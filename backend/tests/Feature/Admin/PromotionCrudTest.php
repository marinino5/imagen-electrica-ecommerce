<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\ProductLine;
use App\Models\Promotion;
use App\Models\User;
use Database\Seeders\BrandSeeder;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([BrandSeeder::class, CatalogSeeder::class]);
    }

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'title' => 'Contactores con descuento',
            'description' => 'Para tableros',
            'product_line_id' => ProductLine::where('slug', 'contactores')->value('id'),
            'discount_percent' => '10',
            'starts_at' => today()->toDateString(),
            'ends_at' => today()->addDays(7)->toDateString(),
            'is_active' => '1',
        ], $override);
    }

    public function test_solo_el_admin_entra_a_promociones(): void
    {
        $comercial = User::factory()->comercial()->create();

        $this->actingAs($comercial)->get(route('admin.promotions.index'))->assertForbidden();
        $this->actingAs($this->admin())->get(route('admin.promotions.index'))->assertOk()->assertSee('Aún no hay promociones');
    }

    public function test_crea_una_promocion_con_descuento(): void
    {
        $this->actingAs($this->admin())->post(route('admin.promotions.store'), $this->payload())
            ->assertRedirect(route('admin.promotions.index'));

        $this->assertDatabaseHas('promotions', ['title' => 'Contactores con descuento', 'discount_percent' => 10, 'special_price' => null]);
    }

    public function test_crea_una_promocion_de_una_referencia_con_precio_especial(): void
    {
        $line = ProductLine::where('slug', 'contactores')->first();
        $sku = $line->variants()->first()->sku;

        $this->actingAs($this->admin())->post(route('admin.promotions.store'), $this->payload([
            'discount_percent' => '', 'special_price' => '250000', 'sku' => $sku,
        ]))->assertRedirect(route('admin.promotions.index'));

        $promotion = Promotion::firstOrFail();
        $this->assertSame(Product::where('sku', $sku)->value('id'), $promotion->product_id);
        $this->assertNull($promotion->discount_percent);
        $this->assertEquals(250000, $promotion->special_price);
    }

    public function test_exige_descuento_o_precio_pero_no_ambos(): void
    {
        $this->actingAs($this->admin())->post(route('admin.promotions.store'), $this->payload(['discount_percent' => '']))
            ->assertSessionHasErrors('discount_percent');

        $this->actingAs($this->admin())->post(route('admin.promotions.store'), $this->payload(['special_price' => '100000']))
            ->assertSessionHasErrors('discount_percent');

        $this->assertDatabaseCount('promotions', 0);
    }

    public function test_valida_fechas_sku_y_descuento_maximo(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.promotions.store'), $this->payload(['ends_at' => today()->subDay()->toDateString()]))
            ->assertSessionHasErrors('ends_at');

        $this->actingAs($admin)->post(route('admin.promotions.store'), $this->payload(['sku' => 'NO-EXISTE']))
            ->assertSessionHasErrors('sku');

        $this->actingAs($admin)->post(route('admin.promotions.store'), $this->payload(['discount_percent' => '95']))
            ->assertSessionHasErrors('discount_percent');
    }

    public function test_el_sku_debe_ser_de_la_linea_elegida(): void
    {
        $otroSku = ProductLine::where('slug', 'guardamotores')->first()->variants()->first()->sku;

        $this->actingAs($this->admin())->post(route('admin.promotions.store'), $this->payload(['sku' => $otroSku]))
            ->assertSessionHasErrors('sku');
    }

    public function test_edita_y_elimina(): void
    {
        $admin = $this->admin();
        $promotion = Promotion::factory()->create(['product_line_id' => ProductLine::first()->id]);

        $this->actingAs($admin)->put(route('admin.promotions.update', $promotion), $this->payload(['title' => 'Nuevo título']))
            ->assertRedirect(route('admin.promotions.index'));
        $this->assertSame('Nuevo título', $promotion->fresh()->title);

        $this->actingAs($admin)->delete(route('admin.promotions.destroy', $promotion))->assertRedirect(route('admin.promotions.index'));
        $this->assertDatabaseMissing('promotions', ['id' => $promotion->id]);
    }

    public function test_el_listado_busca_y_marca_el_estado(): void
    {
        $line = ProductLine::where('slug', 'contactores')->first();
        Promotion::factory()->create(['title' => 'Oferta vigente', 'product_line_id' => $line->id]);
        Promotion::factory()->expired()->create(['title' => 'Oferta vieja', 'product_line_id' => $line->id]);

        $this->actingAs($this->admin())->get(route('admin.promotions.index'))
            ->assertSee('Vigente')->assertSee('Vencida');

        $this->actingAs($this->admin())->get(route('admin.promotions.index', ['q' => 'vieja']))
            ->assertViewHas('promotions', fn ($page) => $page->total() === 1);
    }
}
