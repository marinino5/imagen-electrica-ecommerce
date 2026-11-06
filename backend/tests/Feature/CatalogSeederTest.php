<?php

namespace Tests\Feature;

use App\Enums\SaleMode;
use App\Models\Product;
use App\Models\ProductLine;
use Database\Seeders\BrandSeeder;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([BrandSeeder::class, CatalogSeeder::class]);
    }

    public function test_carga_33_lineas_y_351_referencias_del_inventario(): void
    {
        $this->assertSame(33, ProductLine::count());
        $this->assertSame(351, Product::whereNotNull('line_id')->count());
    }

    public function test_las_siete_lineas_sin_inventario_son_bajo_pedido(): void
    {
        $sinReferencias = ProductLine::doesntHave('variants')->get();

        $this->assertCount(7, $sinReferencias);
        $this->assertTrue($sinReferencias->every(fn (ProductLine $l) => $l->sale_mode === SaleMode::OnRequest));
        $this->assertTrue($sinReferencias->contains('name', 'Motorreductor sinfín WCG01'));
        $this->assertTrue($sinReferencias->contains('name', 'PLC WEG PLC200 y PLC500'));
    }

    public function test_los_precios_son_simulados_y_las_cotizaciones_no_publican_precio(): void
    {
        $directas = Product::whereNotNull('line_id')->where('sale_mode', SaleMode::Direct->value)->get();
        $cotizar = Product::whereNotNull('line_id')->where('sale_mode', SaleMode::Quote->value)->get();

        $this->assertTrue($directas->every(fn (Product $p) => $p->price_is_simulated && $p->price !== null));
        $this->assertTrue($cotizar->every(fn (Product $p) => $p->price === null && ! $p->price_is_simulated));
    }

    public function test_las_referencias_guardan_la_placa_en_specs(): void
    {
        $motor = Product::where('sku', '15331270')->firstOrFail();

        $this->assertSame('1 HP · 1800 RPM', $motor->variant_label);
        $this->assertEquals(1, $motor->specs['potencia_hp']);
        $this->assertSame(1800, $motor->specs['velocidad_rpm']);
        $this->assertSame('w22-iec-carcasa-hierro', $motor->line->slug);
    }

    public function test_se_puede_ejecutar_dos_veces_sin_duplicar_ni_pisar_cambios(): void
    {
        Product::where('sku', '15331270')->update(['price' => 999000, 'stock' => 1]);

        $this->seed(CatalogSeeder::class);

        $this->assertSame(33, ProductLine::count());
        $this->assertSame(351, Product::whereNotNull('line_id')->count());
        $this->assertEquals(999000, Product::where('sku', '15331270')->value('price'));
    }

    public function test_el_plc_es_weg_y_no_hay_marca_delta(): void
    {
        $this->assertDatabaseMissing('brands', ['slug' => 'delta']);
        $this->assertSame('WEG', ProductLine::where('name', 'PLC WEG PLC200 y PLC500')->firstOrFail()->brand_label);
    }

    public function test_el_seeder_de_produccion_llena_el_catalogo_solo_una_vez(): void
    {
        ProductLine::query()->delete();
        Product::query()->forceDelete();

        $this->seed(\Database\Seeders\ProductionCatalogSeeder::class);
        $this->assertSame(33, ProductLine::count());

        // Una línea eliminada a propósito no vuelve en el siguiente arranque
        ProductLine::where('slug', 'variador-cfw300')->first()->variants()->forceDelete();
        ProductLine::where('slug', 'variador-cfw300')->delete();
        $this->seed(\Database\Seeders\ProductionCatalogSeeder::class);

        $this->assertSame(32, ProductLine::count());
    }
}
