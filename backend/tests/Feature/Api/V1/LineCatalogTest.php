<?php

namespace Tests\Feature\Api\V1;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductLine;
use Database\Seeders\BrandSeeder;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CatalogData;
use Tests\TestCase;

class LineCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([BrandSeeder::class, CatalogSeeder::class]);
    }

    public function test_lista_las_lineas_sin_token(): void
    {
        $response = $this->getJson('/api/v1/lines')->assertOk();

        $this->assertCount(CatalogData::lines(), $response->json('data'));
        $response->assertJsonStructure(['data' => [['slug', 'name', 'family', 'category', 'refs_count', 'price_from', 'hp_min', 'hp_max', 'sale_mode']]]);
    }

    public function test_la_linea_resume_potencia_precio_y_stock(): void
    {
        $linea = collect($this->getJson('/api/v1/lines')->json('data'))->firstWhere('slug', 'w22-iec-carcasa-hierro');

        $this->assertSame(23, $linea['refs_count']);
        $this->assertEquals(1, $linea['hp_min']);
        $this->assertEquals(50, $linea['hp_max']);
        $this->assertSame([1200, 1800, 3600], $linea['rpm_values']);
        $this->assertEquals(1150000, $linea['price_from']);
        $this->assertGreaterThan(0, $linea['stock_total']);
        $this->assertSame('Motores Eléctricos', $linea['family']['name']);
        $this->assertSame('direct', $linea['sale_mode']);
    }

    public function test_filtra_por_familia_y_categoria(): void
    {
        $familia = $this->getJson('/api/v1/lines?family=motores-electricos')->assertOk()->json('data');
        $this->assertCount(CatalogData::linesInFamily('Motores Eléctricos'), $familia);

        $categoria = $this->getJson('/api/v1/lines?category=motores-monofasicos')->assertOk()->json('data');
        $this->assertCount(4, $categoria); // Match Drive está oculta hasta tener foto oficial
    }

    public function test_filtra_por_forma_de_compra_marca_y_bodega(): void
    {
        $bajoPedido = $this->getJson('/api/v1/lines?mode=quote')->json('data');
        $this->assertTrue(collect($bajoPedido)->every(fn ($l) => in_array($l['sale_mode'], ['quote', 'on_request'], true)));
        $this->assertGreaterThanOrEqual(7, count($bajoPedido));

        // Siemens está oculta hasta tener foto oficial: la marca se prueba con Autonics
        $autonics = $this->getJson('/api/v1/lines?brand=Autonics')->json('data');
        $this->assertNotEmpty($autonics);
        $this->assertTrue(collect($autonics)->every(fn ($l) => str_contains($l['brand_label'], 'Autonics')));
        $this->assertSame([], $this->getJson('/api/v1/lines?brand=Siemens')->json('data'));

        $conBodega = $this->getJson('/api/v1/lines?in_stock=1')->json('data');
        $this->assertTrue(collect($conBodega)->every(fn ($l) => $l['stock_total'] > 0));
        $this->assertLessThan(CatalogData::lines(), count($conBodega));
    }

    public function test_filtra_por_rango_de_potencia(): void
    {
        $lineas = $this->getJson('/api/v1/lines?hp_min=5&hp_max=10')->assertOk()->json('data');
        $nombres = collect($lineas)->pluck('name');

        $this->assertTrue($nombres->contains('Motor trifásico WEG W22 IEC · carcasa hierro'));
        $this->assertFalse($nombres->contains('Motor monofásico WEG W12')); // llega hasta 0,75 HP
        $this->assertFalse($nombres->contains('Contactor WEG CWB')); // sin potencia en HP
    }

    public function test_busca_por_sku_nombre_o_variante(): void
    {
        $porSku = $this->getJson('/api/v1/lines?q=15331270')->json('data');
        $this->assertSame(['Motor trifásico WEG W22 IEC · carcasa hierro'], collect($porSku)->pluck('name')->all());

        $porNombre = $this->getJson('/api/v1/lines?q=CFW300')->json('data');
        $this->assertSame('Variador de frecuencia WEG CFW300', $porNombre[0]['name']);
    }

    public function test_rechaza_filtros_invalidos(): void
    {
        $this->getJson('/api/v1/lines?mode=otro')->assertStatus(422);
        $this->getJson('/api/v1/lines?hp_min=10&hp_max=5')->assertStatus(422);
    }

    public function test_no_muestra_lineas_inactivas(): void
    {
        ProductLine::where('slug', 'variador-cfw300')->update(['is_active' => false]);

        $this->assertCount(CatalogData::lines() - 1, $this->getJson('/api/v1/lines')->json('data'));
        $this->getJson('/api/v1/lines/variador-cfw300')->assertNotFound();
    }

    public function test_la_ficha_trae_todas_las_referencias_y_lineas_relacionadas(): void
    {
        $response = $this->getJson('/api/v1/lines/w22-iec-carcasa-hierro')->assertOk();

        $this->assertCount(23, $response->json('data.variants'));
        $response->assertJsonStructure(['data' => ['variants' => [['id', 'sku', 'label', 'hp', 'rpm', 'volt', 'ip', 'stock', 'price', 'sale_mode', 'tax_rate', 'unit']]], 'related']);
        $this->assertNotEmpty($response->json('related'));
        $this->assertNotContains('w22-iec-carcasa-hierro', collect($response->json('related'))->pluck('slug'));
        // El costo es interno y nunca sale
        $this->assertArrayNotHasKey('cost_price', $response->json('data.variants.0'));
    }

    public function test_la_ficha_de_una_linea_bajo_pedido_no_tiene_referencias(): void
    {
        // WCG01 recibió referencias en la Fase 1B; el PLC sigue sin inventario
        $response = $this->getJson('/api/v1/lines/plc-weg-plc200-y-plc500')->assertOk();

        $this->assertSame('on_request', $response->json('data.sale_mode'));
        $this->assertSame([], $response->json('data.variants'));
    }

    public function test_wcg01_con_referencias_en_stock_se_cotiza(): void
    {
        $response = $this->getJson('/api/v1/lines/motorreductor-sinfin-wcg01')->assertOk();

        $this->assertSame('quote', $response->json('data.sale_mode'));
        $this->assertNotEmpty($response->json('data.variants'));
        $this->assertContains('0,25–15 HP', $response->json('data.spec_chips'));
    }

    public function test_una_linea_con_una_sola_referencia_comprable_trae_la_compra_rapida(): void
    {
        $categoria = Category::where('slug', 'motores-trifasicos')->firstOrFail();
        $linea = ProductLine::create(['category_id' => $categoria->id, 'name' => 'Línea de prueba', 'slug' => 'linea-de-prueba', 'sale_mode' => 'direct']);
        Product::factory()->create(['category_id' => $categoria->id, 'brand_id' => Brand::first()->id, 'line_id' => $linea->id, 'sale_mode' => 'direct', 'price' => 100000, 'stock' => 3]);

        $item = collect($this->getJson('/api/v1/lines')->json('data'))->firstWhere('slug', 'linea-de-prueba');

        $this->assertNotNull($item['quick_variant']);
        $this->assertEquals(100000, $item['quick_variant']['price']);
    }

    public function test_familias_con_conteo_de_lineas_y_totales(): void
    {
        $response = $this->getJson('/api/v1/families')->assertOk();

        $motores = collect($response->json('data'))->firstWhere('slug', 'motores-electricos');
        $this->assertSame(CatalogData::linesInFamily('Motores Eléctricos'), $motores['lines_count']);
        $this->assertSame(CatalogData::lines(), $response->json('meta.lines'));
        $this->assertSame(CatalogData::references(), $response->json('meta.references'));
        $this->assertSame('WEG', $response->json('meta.brands.0.name'));
    }

    public function test_el_numero_de_familias_sale_de_los_datos_y_coincide_con_la_lista(): void
    {
        $response = $this->getJson('/api/v1/families')->assertOk();

        $this->assertCount(CatalogData::families(), $response->json('data'));
        $this->assertSame(count($response->json('data')), $response->json('meta.families'));

        $medicion = collect($response->json('data'))->firstWhere('slug', 'medicion-y-testeo');
        $this->assertSame(CatalogData::linesInFamily('Medición y Testeo'), $medicion['lines_count']);
    }
}
