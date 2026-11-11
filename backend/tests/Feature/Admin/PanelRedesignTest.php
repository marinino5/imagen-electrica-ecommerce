<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Customer;
use App\Models\NewsletterSubscription;
use App\Models\Order;
use App\Models\Product;
use App\Models\QuoteRequest;
use App\Models\User;
use App\Services\CatalogLayoutService;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Panel rediseñado: cotizaciones unificadas, clientes, suscriptores, análisis comercial, inicio y categorías antiguas. */
class PanelRedesignTest extends TestCase
{
    use RefreshDatabase;

    private function freeQuote(array $attributes = []): QuoteRequest
    {
        $quote = QuoteRequest::create(array_diff_key($attributes, ['created_at' => 1]) + [
            'name' => 'Visitante', 'phone' => '3001234567', 'email' => 'visita@empresa.co', 'city' => 'Bucaramanga',
            'items' => [], 'privacy_accepted_at' => now(),
        ]);

        // created_at no es asignable en masa
        if (isset($attributes['created_at'])) {
            $quote->forceFill(['created_at' => $attributes['created_at']])->save();
        }

        return $quote;
    }

    public function test_quotes_tab_mixes_quotes_with_and_without_account(): void
    {
        $withAccount = Order::factory()->quote()->create();
        $free = $this->freeQuote(['name' => 'Sin cuenta SA']);

        $this->actingAs(User::factory()->comercial()->create())->get(route('admin.quotes.index'))
            ->assertOk()
            ->assertSee($withAccount->code)
            ->assertSee('SOL-'.str_pad((string) $free->id, 4, '0', STR_PAD_LEFT))
            ->assertSee('Con cuenta')
            ->assertSee('Sin cuenta');
    }

    public function test_orders_tab_lists_only_purchases(): void
    {
        $quote = Order::factory()->quote()->create();
        $purchase = Order::factory()->create();

        $this->actingAs(User::factory()->admin()->create())->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee($purchase->code)
            ->assertDontSee($quote->code);
    }

    public function test_customers_list_and_history(): void
    {
        $customer = Customer::factory()->create(['company_name' => 'Metalúrgica Andina']);
        Order::factory()->create(['customer_id' => $customer->id]);

        $user = User::factory()->comercial()->create();
        $this->actingAs($user)->get(route('admin.customers.index'))->assertOk()->assertSee('Metalúrgica Andina');
        $this->actingAs($user)->get(route('admin.customers.index', ['q' => 'zzz-no-existe']))->assertOk()->assertDontSee('Metalúrgica Andina');
        $this->actingAs($user)->get(route('admin.customers.show', $customer))->assertOk()->assertSee('Metalúrgica Andina');
        $this->actingAs(User::factory()->create())->get(route('admin.customers.index'))->assertForbidden();
    }

    public function test_newsletter_export_and_unsubscribe_keep_the_consent_proof(): void
    {
        $active = NewsletterSubscription::create(['email' => 'activo@empresa.co', 'consent' => true, 'consented_at' => now(), 'ip' => '127.0.0.1']);
        $gone = NewsletterSubscription::create(['email' => 'baja@empresa.co', 'consent' => true, 'consented_at' => now(), 'ip' => '127.0.0.1']);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->patch(route('admin.newsletter.unsubscribe', $gone))->assertRedirect();
        $this->assertNotNull($gone->fresh()->unsubscribed_at);
        $this->assertTrue($gone->fresh()->consent, 'la baja no borra la prueba del consentimiento');
        $this->assertNull($active->fresh()->unsubscribed_at);

        $csv = $this->actingAs($admin)->get(route('admin.newsletter.export'))->assertOk()->getContent();
        $this->assertStringContainsString('activo@empresa.co,activo', $csv);
        $this->assertStringContainsString('baja@empresa.co,baja', $csv);
        $this->assertStringContainsString(config('shop.newsletter_consent_version'), $csv);
    }

    public function test_commercial_analysis_is_admin_only_and_shows_the_vat(): void
    {
        $this->actingAs(User::factory()->comercial()->create())->get(route('admin.reports.commercial'))->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())->get(route('admin.reports.commercial'))->assertOk();
        $this->get(route('admin.reports.index'))->assertOk()->assertSee('IVA de las ventas');
    }

    public function test_overview_counts_pending_quotes_of_both_origins_and_lists_the_overdue_first(): void
    {
        Order::factory()->create(); // pedido pendiente de pago
        Order::factory()->quote()->create(['created_at' => now()->subHours(2)]);
        $this->freeQuote(['created_at' => now()->subHours(40)]);

        $overview = app(DashboardService::class)->overview();

        $this->assertSame(2, $overview['kpis']['quotes']['value']);
        $this->assertSame(1, $overview['kpis']['orders']['value']);
        $this->assertTrue($overview['attention']->first()->overdue, 'lo atrasado va primero');
        $this->assertCount(DashboardService::CHART_DAYS, $overview['chart']['labels']);
    }

    public function test_legacy_categories_are_merged_into_the_store_ones(): void
    {
        $old = Category::factory()->create(['slug' => 'extractores', 'parent_id' => null]);
        $new = Category::factory()->create(['slug' => 'ventilacion-industrial', 'parent_id' => null]);
        $product = Product::factory()->create(['category_id' => $old->id]);

        $removed = app(CatalogLayoutService::class)->migrateLegacyCategories();

        $this->assertSame(1, $removed);
        $this->assertSame($new->id, $product->fresh()->category_id);
        $this->assertNull(Category::where('slug', 'extractores')->first());
        $this->assertSame(0, app(CatalogLayoutService::class)->migrateLegacyCategories(), 'idempotente');
    }

    public function test_product_photos_stored_as_paths_point_to_the_storefront(): void
    {
        config(['shop.frontend_url' => 'https://tienda.test']);

        $own = Product::factory()->create(['image_url' => '/img/catalogo/foto.webp']);
        $external = Product::factory()->create(['image_url' => 'https://cdn.test/foto.jpg']);

        $this->assertSame('https://tienda.test/img/catalogo/foto.webp', $own->image_src);
        $this->assertSame('https://cdn.test/foto.jpg', $external->image_src);
        $this->assertNull(Product::factory()->create(['image_url' => null])->image_src);
    }
}
