<?php

namespace Tests\Feature\Services;

use App\Enums\SaleMode;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductLine;
use App\Services\CatalogPriceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogPriceServiceTest extends TestCase
{
    use RefreshDatabase;

    private function line(): ProductLine
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $line = ProductLine::create([
            'category_id' => $category->id, 'brand_id' => $brand->id, 'name' => 'Reflectores LED', 'slug' => 'reflectores-led',
            'sale_mode' => SaleMode::Quote, 'sort_order' => 1,
        ]);

        foreach (['ELT33' => 1, 'ELT34' => 3] as $sku => $stock) {
            Product::factory()->create([
                'category_id' => $category->id, 'brand_id' => $brand->id, 'line_id' => $line->id, 'sku' => $sku,
                'price' => null, 'price_is_simulated' => false, 'sale_mode' => SaleMode::Quote, 'stock' => $stock,
            ]);
        }

        return $line;
    }

    public function test_un_sku_con_precio_pasa_a_compra_en_linea_y_la_linea_a_directa(): void
    {
        $line = $this->line();

        $result = app(CatalogPriceService::class)->load([['sku' => 'ELT33', 'price' => '125.000'], ['sku' => 'ELT34', 'price' => '']]);

        $this->assertSame(1, $result['updated']);
        $this->assertSame(1, $result['skipped']);
        $direct = Product::where('sku', 'ELT33')->firstOrFail();
        $this->assertEquals(125000, $direct->price);
        $this->assertSame(SaleMode::Direct, $direct->sale_mode);
        $this->assertFalse($direct->price_is_simulated);
        // El que no trae precio sigue en cotización
        $this->assertNull(Product::where('sku', 'ELT34')->value('price'));
        $this->assertSame(SaleMode::Quote, Product::where('sku', 'ELT34')->firstOrFail()->sale_mode);
        $this->assertSame(SaleMode::Direct, $line->fresh()->sale_mode);
    }

    public function test_acepta_formato_colombiano_y_reporta_sku_desconocidos_y_precios_invalidos(): void
    {
        $this->line();

        $result = app(CatalogPriceService::class)->load([
            ['sku' => 'ELT33', 'price' => '1.250.000,50'],
            ['sku' => 'NO-EXISTE', 'price' => '100'],
            ['sku' => 'ELT34', 'price' => 'abc'],
        ]);

        $this->assertEquals(1250000.50, Product::where('sku', 'ELT33')->value('price'));
        $this->assertSame(['NO-EXISTE'], $result['unknown']);
        $this->assertSame(['ELT34'], $result['invalid']);
    }

    public function test_dry_run_no_guarda_nada(): void
    {
        $this->line();

        $result = app(CatalogPriceService::class)->load([['sku' => 'ELT33', 'price' => '99000']], dryRun: true);

        $this->assertSame(1, $result['updated']);
        $this->assertNull(Product::where('sku', 'ELT33')->value('price'));
    }

    public function test_el_comando_lee_el_csv(): void
    {
        $this->line();
        $path = tempnam(sys_get_temp_dir(), 'precios');
        file_put_contents($path, "sku,nombre,precio_con_iva\nELT33,Reflector,89900\nELT34,Reflector,\n");

        $this->artisan('catalog:load-prices', ['file' => $path])->expectsOutputToContain('Con precio: 1')->assertSuccessful();

        $this->assertEquals(89900, Product::where('sku', 'ELT33')->value('price'));
        unlink($path);
    }

    public function test_el_comando_falla_si_faltan_columnas(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'precios');
        file_put_contents($path, "codigo,valor\nELT33,100\n");

        $this->artisan('catalog:load-prices', ['file' => $path])->assertFailed();
        unlink($path);
    }
}
