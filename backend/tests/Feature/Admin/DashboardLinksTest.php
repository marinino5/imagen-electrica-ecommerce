<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Cada tarjeta y sección del dashboard abre su detalle con el filtro ya aplicado. */
class DashboardLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_cards_link_to_filtered_lists(): void
    {
        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.dashboard'))->assertOk();

        foreach ([
            route('admin.orders.index', ['type' => 'purchase', 'status' => 'pending_payment']),
            route('admin.orders.index', ['type' => 'quote', 'status' => 'new']),
            route('admin.orders.index', ['overdue' => 1]),
            route('admin.products.index', ['status' => 'active']),
        ] as $url) {
            $response->assertSee('href="'.e($url).'"', false);
        }
    }

    public function test_low_stock_shows_five_products_and_a_link_that_lists_the_same_total(): void
    {
        Product::factory()->count(7)->create(['stock' => 2]);
        Product::factory()->outOfStock()->create();
        Product::factory()->create(['stock' => 50]);
        // No cuentan: inactivo y de cotización (el stock en cero es lo normal)
        Product::factory()->create(['stock' => 1, 'is_active' => false]);
        Product::factory()->quoteOnly()->create(['stock' => 0]);

        $admin = User::factory()->admin()->create();
        $summary = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->viewData('summary');

        $this->assertCount(5, $summary['low_stock']['products']);
        $this->assertSame(8, $summary['low_stock']['total']);

        $link = route('admin.products.index', ['stock' => 'low', 'sale_mode' => 'direct', 'status' => 'active']);
        $this->get(route('admin.dashboard'))->assertSee('Ver todos (8)')->assertSee('href="'.e($link).'"', false);

        // La lista abierta con ese enlace muestra el mismo total que la tarjeta
        $this->get($link)->assertViewHas('products', fn ($page) => $page->total() === 8);
    }

    public function test_attention_and_latest_orders_link_to_their_detail(): void
    {
        $quote = Order::factory()->quote()->create();
        $purchase = Order::factory()->create();

        $this->actingAs(User::factory()->comercial()->create())->get(route('admin.dashboard'))
            ->assertSee('href="'.e(route('admin.orders.show', $quote)).'"', false)
            ->assertSee('href="'.e(route('admin.orders.show', $purchase)).'"', false)
            ->assertSee('href="'.e(route('admin.orders.index', ['type' => 'quote', 'status' => 'new,in_review'])).'"', false);
    }

    public function test_comercial_sees_no_links_to_the_product_catalog(): void
    {
        Product::factory()->create(['stock' => 1]);

        $this->actingAs(User::factory()->comercial()->create())->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee(route('admin.products.index'), false)
            ->assertDontSee('Ver todos (1)');
    }

    public function test_bell_links_to_new_quotes(): void
    {
        Order::factory()->quote()->create(['status' => OrderStatus::New]);

        $this->actingAs(User::factory()->comercial()->create())->get(route('admin.dashboard'))
            ->assertSee('href="'.e(route('admin.orders.index', ['type' => 'quote', 'status' => 'new'])).'"', false);
    }
}
