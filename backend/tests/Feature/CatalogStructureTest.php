<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductLine;
use App\Services\CatalogLayoutService;
use Database\Seeders\BrandSeeder;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Revisión producto por producto: una línea es un solo producto, sin repetidos ni mezclas, y cada referencia trae tipo y marca. */
class CatalogStructureTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<int, array<string, mixed>> */
    private function lines(): array
    {
        return json_decode((string) file_get_contents(database_path('data/catalogo.json')), true, 512, JSON_THROW_ON_ERROR)['lines'];
    }

    public function test_una_linea_no_mezcla_marcas_ni_repite_slug(): void
    {
        $slugs = [];

        foreach ($this->lines() as $line) {
            $brands = array_unique(array_column($line['variants'], 'brand'));
            $this->assertLessThanOrEqual(1, count($brands), "«{$line['name']}» mezcla marcas: ".implode(', ', $brands));

            $slug = $line['slug'] ?? \Illuminate\Support\Str::slug($line['name']);
            $this->assertArrayNotHasKey($slug, $slugs, "slug repetido: {$slug}");
            $slugs[$slug] = true;
        }
    }

    public function test_ningun_sku_esta_en_dos_lineas(): void
    {
        $skus = [];
        foreach ($this->lines() as $line) {
            foreach ($line['variants'] as $v) {
                $this->assertArrayNotHasKey($v['sku'], $skus, "SKU repetido: {$v['sku']}");
                $skus[$v['sku']] = true;
            }
        }
    }

    public function test_el_nombre_de_la_linea_dice_que_es_y_la_marca_en_las_lineas_de_control(): void
    {
        foreach ($this->lines() as $line) {
            if (! in_array($line['family'], ['Control y Automatización'], true) || $line['variants'] === []) {
                continue;
            }

            $this->assertNotContains($line['name'], ['Accesorios de maniobra', 'Contactores', 'Guardamotores', 'Pulsadores, pilotos y selectores']);
            $brand = $line['variants'][0]['brand'];
            if ($brand !== 'Sin marca') {
                $this->assertStringContainsString($brand, $line['name'], "«{$line['name']}» debe nombrar la marca {$brand}");
            }
        }
    }

    public function test_cada_referencia_trae_su_tipo_en_las_especificaciones(): void
    {
        foreach ($this->lines() as $line) {
            foreach ($line['variants'] as $v) {
                $this->assertNotEmpty($v['specs']['tipo'] ?? null, "{$v['sku']} no trae «tipo»");
                $this->assertNotSame('', trim($v['label']), "{$v['sku']} sin etiqueta");
            }
        }
    }

    public function test_el_sync_deja_cada_producto_en_su_linea_sin_tocar_precio_ni_stock(): void
    {
        $this->seed([BrandSeeder::class, CatalogSeeder::class]);
        $product = Product::where('sku', 'CWB9')->first() ?? Product::where('sku', '12220434')->firstOrFail();
        // Estado «viejo»: todo en una línea mezclada, con precio y stock ajustados a mano
        $old = ProductLine::create(['category_id' => $product->category_id, 'name' => 'Contactores', 'slug' => 'contactores-viejo', 'sale_mode' => 'direct', 'sort_order' => 1]);
        $product->update(['line_id' => $old->id, 'price' => 99999, 'stock' => 3, 'variant_label' => 'viejo']);

        app(CatalogLayoutService::class)->sync();
        app(CatalogLayoutService::class)->sync(); // idempotente

        $product->refresh();
        $this->assertSame('contactores', $product->line->slug);
        $this->assertNotSame('viejo', $product->variant_label);
        $this->assertSame('99999.00', $product->price);
        $this->assertSame(3, $product->stock);
        $this->assertDatabaseMissing('product_lines', ['slug' => 'contactores-viejo']);
    }

    public function test_las_lineas_sin_foto_oficial_quedan_ocultas(): void
    {
        $this->seed([BrandSeeder::class, CatalogSeeder::class]);

        foreach (CatalogSeeder::HIDDEN_UNTIL_PHOTO as $name) {
            $line = ProductLine::where('name', $name)->firstOrFail();
            $this->assertFalse($line->is_active, $name);
            $this->assertSame(0, Product::where('line_id', $line->id)->where('is_active', true)->count(), $name);
        }
    }
}
