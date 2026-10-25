<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function summary(): array
    {
        return app(DashboardService::class)->getSummary();
    }

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

        $summary = $this->summary();

        $this->assertSame(2, $summary['pending_payment_orders']['value']);
        $this->assertSame(2, $summary['new_quotes']['value']);
        $this->assertSame(3, $summary['active_products']['value']);
        $this->assertSame(2, $summary['overdue_quotes']['value']);
        $this->assertSame(48, $summary['overdue_quotes']['oldest_hours']);

        // Dona: solo estados con solicitudes; "Nueva" suma 2 cotizaciones
        $newIndex = array_search(OrderStatus::New->label(), $summary['status_chart']['labels'], true);
        $this->assertSame(2, $summary['status_chart']['data'][$newIndex]);
        $this->assertNotContains(OrderStatus::Delivered->label(), $summary['status_chart']['labels']);
    }

    public function test_weekly_comparison_counts_last_seven_days_against_the_previous_seven(): void
    {
        Order::factory()->count(3)->create(); // esta semana
        Order::factory()->create(['created_at' => now()->subDays(10)]); // semana anterior
        Order::factory()->create(['created_at' => now()->subDays(20)]); // fuera de las dos ventanas

        $orders = $this->summary()['pending_payment_orders'];

        $this->assertSame(3, $orders['this_week']);
        $this->assertSame(1, $orders['last_week']);
    }

    public function test_attention_list_shows_unanswered_quotes_oldest_first(): void
    {
        $recent = Order::factory()->quote()->create(['created_at' => now()->subHours(3)]);
        $old = Order::factory()->quote()->create(['status' => OrderStatus::InReview, 'created_at' => now()->subHours(30)]);
        Order::factory()->quote()->create(['status' => OrderStatus::Sent, 'created_at' => now()->subDays(5)]);

        $quotes = $this->summary()['attention_quotes'];

        $this->assertCount(2, $quotes);
        $this->assertTrue($quotes[0]['order']->is($old));
        $this->assertTrue($quotes[0]['is_overdue']);
        $this->assertTrue($quotes[1]['order']->is($recent));
        $this->assertFalse($quotes[1]['is_overdue']);
    }

    public function test_daily_chart_has_seven_days_split_by_type(): void
    {
        Order::factory()->count(2)->create();
        Order::factory()->quote()->create(['created_at' => now()->subDays(2)]);
        Order::factory()->create(['created_at' => now()->subDays(9)]); // fuera del gráfico

        $chart = $this->summary()['daily_chart'];
        [$purchases, $quotes] = $chart['datasets'];

        $this->assertCount(7, $chart['labels']);
        $this->assertSame(2, $purchases['data'][6]); // hoy es el último día
        $this->assertSame(1, $quotes['data'][4]);
        $this->assertSame(2, array_sum($purchases['data']));
    }

    public function test_low_stock_lists_active_direct_products_with_five_units_or_fewer(): void
    {
        $out = Product::factory()->create(['name' => 'Agotado', 'stock' => 0]);
        $low = Product::factory()->create(['name' => 'Casi agotado', 'stock' => 5]);
        Product::factory()->create(['stock' => 6]); // stock suficiente
        Product::factory()->create(['stock' => 1, 'is_active' => false]); // inactivo
        Product::factory()->quoteOnly()->create(['stock' => 0]); // por cotización: sin stock es normal
        Product::factory()->onRequest()->create(); // bajo pedido

        $lowStock = $this->summary()['low_stock'];

        $this->assertSame(2, $lowStock['total']);
        // Los más escasos primero
        $this->assertSame([$out->id, $low->id], $lowStock['products']->pluck('id')->all());
    }

    public function test_dashboard_page_shows_cards_charts_and_new_quotes_bell(): void
    {
        Order::factory()->quote()->count(2)->create();

        $this->actingAs(User::factory()->admin()->create(['name' => 'Mariana Niño']))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Pedidos pendientes de pago')
            ->assertSee('data-chart="status"', false)
            ->assertSee('data-chart="daily"', false)
            ->assertSee('Requieren atención')
            ->assertSee('Stock bajo')
            ->assertSee('Mariana')
            ->assertSee('Buscar productos')
            ->assertSee('2 cotizaciones nuevas');
    }
}
