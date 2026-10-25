<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_counts_requests_products_and_overdue_quotes(): void
    {
        Order::factory()->count(2)->create(); // pendientes de pago
        Order::factory()->paid()->create();
        Order::factory()->quote()->create(); // nueva y reciente
        Order::factory()->quote()->create(['created_at' => now()->subHours(25)]); // nueva y atrasada
        Order::factory()->quote()->create(['status' => OrderStatus::InReview, 'created_at' => now()->subDays(2)]); // atrasada
        Order::factory()->quote()->create(['status' => OrderStatus::Sent, 'created_at' => now()->subDays(3)]); // ya respondida
        Product::factory()->count(3)->create(['is_active' => true]);
        Product::factory()->create(['is_active' => false]);

        $summary = app(DashboardService::class)->getSummary();

        $this->assertSame(2, $summary['pending_payment_orders']);
        $this->assertSame(2, $summary['new_quotes']);
        $this->assertSame(3, $summary['active_products']);
        $this->assertSame(2, $summary['overdue_quotes']);

        // Gráfico: la barra "Nueva" tiene 2 cotizaciones y 0 pedidos
        $newIndex = array_search(OrderStatus::New->label(), $summary['chart']['labels'], true);
        [$purchases, $quotes] = $summary['chart']['datasets'];
        $this->assertSame(0, $purchases['data'][$newIndex]);
        $this->assertSame(2, $quotes['data'][$newIndex]);
    }

    public function test_dashboard_page_shows_the_cards_and_chart(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Pedidos pendientes de pago')
            ->assertSee('requests-by-status-chart');
    }
}
