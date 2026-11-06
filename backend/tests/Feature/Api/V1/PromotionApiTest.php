<?php

namespace Tests\Feature\Api\V1;

use App\Models\ProductLine;
use App\Models\Promotion;
use Database\Seeders\BrandSeeder;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\PromotionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([BrandSeeder::class, CatalogSeeder::class]);
    }

    private function line(string $slug = 'contactores'): ProductLine
    {
        return ProductLine::where('slug', $slug)->firstOrFail();
    }

    public function test_sin_promociones_vigentes_devuelve_lista_vacia(): void
    {
        $this->getJson('/api/v1/promotions')->assertOk()->assertExactJson(['data' => []]);
    }

    public function test_solo_devuelve_las_vigentes_y_activas(): void
    {
        $line = $this->line();
        Promotion::factory()->create(['title' => 'Vigente', 'product_line_id' => $line->id]);
        Promotion::factory()->expired()->create(['title' => 'Vencida', 'product_line_id' => $line->id]);
        Promotion::factory()->upcoming()->create(['title' => 'Futura', 'product_line_id' => $line->id]);
        Promotion::factory()->inactive()->create(['title' => 'Apagada', 'product_line_id' => $line->id]);

        $titles = collect($this->getJson('/api/v1/promotions')->assertOk()->json('data'))->pluck('title');

        $this->assertSame(['Vigente'], $titles->all());
    }

    public function test_el_ultimo_dia_de_vigencia_todavia_cuenta(): void
    {
        Promotion::factory()->create(['title' => 'Termina hoy', 'product_line_id' => $this->line()->id, 'starts_at' => today()->subDays(3), 'ends_at' => today()]);

        $this->getJson('/api/v1/promotions')->assertJsonCount(1, 'data');
    }

    public function test_calcula_el_precio_con_descuento_de_la_linea(): void
    {
        $line = $this->line();
        Promotion::factory()->create(['product_line_id' => $line->id, 'discount_percent' => 10]);
        $from = (float) $line->variants()->where('is_active', true)->whereNotNull('price')->where('sale_mode', 'direct')->min('price');

        $item = $this->getJson('/api/v1/promotions')->json('data.0');

        $this->assertEquals($from, $item['original_price']);
        $this->assertEquals(round($from * 0.9), $item['final_price']);
        $this->assertTrue($item['is_from_price']);
        $this->assertSame('contactores', $item['line']['slug']);
        $this->assertSame('control-y-automatizacion', $item['line']['family']['slug']);
    }

    public function test_precio_especial_de_una_referencia(): void
    {
        $product = $this->line()->variants()->whereNotNull('price')->first();
        Promotion::factory()->specialPrice(99000)->create(['product_line_id' => $product->line_id, 'product_id' => $product->id]);

        $item = $this->getJson('/api/v1/promotions')->json('data.0');

        $this->assertEquals(99000, $item['final_price']);
        $this->assertEquals((float) $product->price, $item['original_price']);
        $this->assertSame($product->sku, $item['sku']);
        $this->assertFalse($item['is_from_price']);
        $this->assertNull($item['discount_percent']);
    }

    public function test_no_incluye_promociones_de_lineas_inactivas(): void
    {
        $line = $this->line();
        Promotion::factory()->create(['product_line_id' => $line->id]);
        $line->update(['is_active' => false]);

        $this->getJson('/api/v1/promotions')->assertExactJson(['data' => []]);
    }

    public function test_el_seeder_de_ejemplo_crea_dos_promociones_vigentes(): void
    {
        $this->seed(PromotionSeeder::class);

        $this->assertCount(2, $this->getJson('/api/v1/promotions')->json('data'));
    }

    public function test_el_seeder_general_solo_siembra_promociones_en_local(): void
    {
        // En las pruebas APP_ENV=testing: no es local, así que DatabaseSeeder no debe crear promociones
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('promotions', 0);
    }
}
