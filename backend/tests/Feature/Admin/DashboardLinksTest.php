<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Los indicadores y la lista de atención del inicio abren su detalle. */
class DashboardLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_indicators_link_to_their_lists(): void
    {
        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.dashboard'))->assertOk();

        foreach ([
            route('admin.reports.index'),
            route('admin.orders.index', ['status' => 'pending_payment']),
            route('admin.quotes.index'),
        ] as $url) {
            $response->assertSee('href="'.e($url).'"', false);
        }
    }

    public function test_attention_list_links_to_each_order_and_quote(): void
    {
        $quote = Order::factory()->quote()->create();
        $purchase = Order::factory()->create();

        $this->actingAs(User::factory()->comercial()->create())->get(route('admin.dashboard'))
            ->assertSee('href="'.e(route('admin.orders.show', $quote)).'"', false)
            ->assertSee('href="'.e(route('admin.orders.show', $purchase)).'"', false);
    }

    public function test_comercial_sees_no_links_to_the_catalog_or_the_analysis(): void
    {
        $this->actingAs(User::factory()->comercial()->create())->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee(route('admin.products.index'), false)
            ->assertDontSee(route('admin.reports.index'), false);
    }

    public function test_bell_links_to_new_quotes(): void
    {
        Order::factory()->quote()->create(['status' => OrderStatus::New]);

        $this->actingAs(User::factory()->comercial()->create())->get(route('admin.dashboard'))
            ->assertSee('href="'.e(route('admin.quotes.index')).'"', false)
            ->assertSee('1 cotización nueva');
    }
}
