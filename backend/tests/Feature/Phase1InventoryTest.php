<?php

namespace Tests\Feature;

use App\Enums\SaleMode;
use App\Models\Product;
use App\Models\ProductLine;
use Database\Seeders\BrandSeeder;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\LineImageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CatalogData;
use Tests\TestCase;

/** Fase 1B: las referencias nuevas del inventario (solo las «Listo» con foto) y el stock de la hoja «Stock distinto». */
class Phase1InventoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([BrandSeeder::class, CatalogSeeder::class, LineImageSeeder::class]);
    }

    /** @return list<array{sku: string, stock_tienda: int, stock_excel: int}> */
    private function stockFix(): array
    {
        return json_decode((string) file_get_contents(base_path('../docs/inventario/stock-distinto.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_el_total_es_351_mas_las_referencias_publicadas(): void
    {
        $this->assertSame(CatalogData::BASE_REFERENCES + count(CatalogData::phase1Variants()), Product::whereNotNull('line_id')->count());
    }

    public function test_toda_referencia_nueva_tiene_precio_de_referencia_o_se_cotiza_y_trae_su_foto(): void
    {
        foreach (CatalogData::phase1Variants() as $v) {
            $product = Product::where('sku', $v['sku'])->first();
            $this->assertNotNull($product, "falta {$v['sku']}");
            $this->assertFalse($product->price_is_simulated, $v['sku']);

            if ($product->price_source !== null) {
                // Precio de referencia: venta directa, por confirmar con la empresa
                $this->assertSame(SaleMode::Direct, $product->sale_mode, $v['sku']);
                $this->assertGreaterThan(0, (float) $product->price, $v['sku']);
            } else {
                // Industrial o bajo pedido (reductores, motorreductores): solo cotización
                $this->assertSame(SaleMode::Quote, $product->sale_mode, $v['sku']);
                $this->assertNull($product->price, $v['sku']);
            }

            $this->assertNotEmpty($product->image_url, $v['sku']);
            $this->assertFileExists(base_path('../cliente/public'.$product->image_url), $v['sku']);
            $this->assertDoesNotMatchRegularExpression('/^(PEND|PROV|TEMP|SIN)/i', $v['sku'], 'nada de SKU provisionales');
            $this->assertGreaterThan(0, $product->stock);
        }
    }

    public function test_las_referencias_nuevas_no_repiten_sku(): void
    {
        $skus = array_column(CatalogData::phase1Variants(), 'sku');

        $this->assertSame(count($skus), count(array_unique($skus)));
    }

    public function test_las_familias_nuevas_van_al_final_y_otros_productos_es_la_ultima(): void
    {
        $slugs = collect($this->getJson('/api/v1/families')->assertOk()->json('data'))->pluck('slug');

        $this->assertSame('otros-productos', $slugs->last());
        $this->assertSame('material-electrico', $slugs->slice(-2, 1)->first());
        $this->assertContains('medicion-y-testeo', $slugs);
    }

    public function test_medicion_incluye_los_instrumentos_de_panel_y_las_lineas_de_wcg_ya_tienen_referencias(): void
    {
        $this->assertTrue(ProductLine::where('slug', 'amperimetro-analogo-chint')->exists());
        $this->assertGreaterThan(0, ProductLine::where('slug', 'motorreductor-sinfin-wcg01')->firstOrFail()->variants()->count());
        $this->assertSame(SaleMode::Quote, ProductLine::where('slug', 'motorreductor-sinfin-wcg01')->firstOrFail()->sale_mode);
    }

    public function test_sin_marca_no_sale_como_marca_en_la_api(): void
    {
        $product = Product::whereHas('brand', fn ($q) => $q->where('slug', 'sin-marca'))->with('line')->firstOrFail();

        $variants = $this->getJson('/api/v1/lines/'.$product->line->slug)->assertOk()->json('data.variants');
        $variant = collect($variants)->firstWhere('sku', $product->sku);

        $this->assertNull($variant['brand']);
    }

    public function test_las_referencias_traen_sus_datos_propios_en_specs(): void
    {
        $product = Product::whereNotNull('line_id')->get()->first(fn (Product $p) => isset($p->specs['pines']));
        $this->assertNotNull($product);

        $variant = collect($this->getJson('/api/v1/lines/'.$product->line->slug)->json('data.variants'))->firstWhere('sku', $product->sku);

        $this->assertArrayHasKey('pines', $variant['specs']);
    }

    public function test_la_migracion_ajusta_el_stock_solo_si_conserva_el_valor_de_la_tienda(): void
    {
        $fix = $this->stockFix();
        $this->assertCount(12, $fix);
        // Estado publicado antes de la migración: el stock de la tienda, salvo una referencia que el panel ya cambió
        foreach ($fix as $f) {
            Product::where('sku', $f['sku'])->update(['stock' => $f['stock_tienda']]);
        }
        Product::where('sku', $fix[0]['sku'])->update(['stock' => 99]);

        (require database_path('migrations/2026_11_11_100000_add_phase1_inventory.php'))->up();

        foreach (array_slice($fix, 1) as $f) {
            $this->assertSame($f['stock_excel'], Product::where('sku', $f['sku'])->value('stock'), $f['sku']);
        }
        $this->assertSame(99, Product::where('sku', $fix[0]['sku'])->value('stock'), 'un ajuste del panel no se pisa');
    }

    public function test_la_migracion_es_idempotente(): void
    {
        (require database_path('migrations/2026_11_11_100000_add_phase1_inventory.php'))->up();
        (require database_path('migrations/2026_11_11_100000_add_phase1_inventory.php'))->up();

        $this->assertSame(CatalogData::allLines(), ProductLine::count());
        $this->assertSame(CatalogData::allReferences(), Product::whereNotNull('line_id')->count());
    }
}
