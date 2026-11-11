<?php

namespace Tests\Feature\Admin;

use App\Enums\PriceSource;
use App\Enums\SaleMode;
use App\Models\Product;
use App\Models\ProductLine;
use App\Models\User;
use App\Services\CatalogPriceService;
use Database\Seeders\BrandSeeder;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Precios de referencia: se marcan en el panel, se filtran para confirmarlos y los reemplaza el precio real. */
class ReferencePriceTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_seeder_asigna_precios_de_referencia_y_deja_los_reductores_en_cotizacion(): void
    {
        $this->seed([BrandSeeder::class, CatalogSeeder::class]);

        $toma = Product::where('sku', 'WID0002')->firstOrFail();
        $this->assertSame(PriceSource::Reference, $toma->price_source);
        $this->assertSame(SaleMode::Direct, $toma->sale_mode);
        $this->assertGreaterThan(0, (float) $toma->price);
        $this->assertSame(SaleMode::Direct, $toma->line->sale_mode);

        $reductor = Product::where('sku', '801846')->firstOrFail();
        $this->assertNull($reductor->price_source);
        $this->assertNull($reductor->price);
        $this->assertSame(SaleMode::Quote, $reductor->sale_mode);
        $this->assertSame(94, Product::whereNotNull('price_source')->count() + Product::whereIn('sku', ['801846', '15891264', '15891341', '4360438', '15889637', '15336424', '15884397', '16696025', '853063', '50102889', '12361705', '15553051'])->count());
    }

    public function test_es_idempotente_y_no_pisa_precios_reales(): void
    {
        $this->seed([BrandSeeder::class, CatalogSeeder::class]);
        Product::where('sku', 'WID0002')->update(['price' => 31000, 'price_source' => null]);

        $this->assertSame(0, app(CatalogPriceService::class)->loadReferencePrices());
        $this->assertSame('31000.00', Product::where('sku', 'WID0002')->value('price'));
    }

    public function test_cargar_el_precio_real_limpia_la_marca_de_referencia(): void
    {
        $this->seed([BrandSeeder::class, CatalogSeeder::class]);

        app(CatalogPriceService::class)->load([['sku' => 'WID0002', 'price' => '29.900']]);

        $product = Product::where('sku', 'WID0002')->firstOrFail();
        $this->assertNull($product->price_source);
        $this->assertSame('29900.00', $product->price);
    }

    public function test_el_panel_filtra_y_marca_los_precios_de_referencia(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        Product::factory()->create(['name' => 'Con referencia', 'price' => 1000, 'price_source' => PriceSource::Reference]);
        Product::factory()->create(['name' => 'Confirmado', 'price' => 2000]);

        $this->get(route('admin.products.index'))->assertOk()->assertSee('Con referencia')->assertSee('Confirmado')->assertSee('Por confirmar');
        $this->get(route('admin.products.index', ['price' => 'reference']))->assertOk()->assertSee('Con referencia')->assertDontSee('Confirmado')->assertSee('Precio de referencia');
    }

    public function test_editar_el_precio_desde_el_panel_lo_da_por_confirmado(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $product = Product::factory()->create(['price' => 1000, 'sale_mode' => SaleMode::Direct, 'price_source' => PriceSource::Reference]);
        $payload = fn (array $o) => array_merge([
            'category_id' => $product->category_id, 'brand_id' => $product->brand_id, 'sku' => $product->sku, 'name' => $product->name,
            'short_description' => 'x', 'unit' => $product->unit->value, 'sale_mode' => 'direct', 'price' => 1000,
            'tax_rate' => 19, 'stock' => 3, 'is_active' => 1,
        ], $o);

        // Mismo precio: sigue siendo de referencia
        $this->put(route('admin.products.update', $product), $payload([]))->assertRedirect();
        $this->assertSame(PriceSource::Reference, $product->fresh()->price_source);

        // Precio distinto: confirmado
        $this->put(route('admin.products.update', $product), $payload(['price' => 1200]))->assertRedirect();
        $this->assertNull($product->fresh()->price_source);
    }
}
