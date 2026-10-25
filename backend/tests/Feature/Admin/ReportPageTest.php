<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportPageTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->get(route('admin.reports.index'))->assertRedirect(route('admin.login'));
    }

    public function test_comercial_cannot_open_reports_or_export_them(): void
    {
        $comercial = User::factory()->comercial()->create();

        $this->actingAs($comercial)->get(route('admin.reports.index'))->assertForbidden();
        $this->actingAs($comercial)->get(route('admin.reports.export'))->assertForbidden();
    }

    public function test_admin_sees_the_page_with_this_month_by_default(): void
    {
        $this->actingAs($this->admin())->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Reportes')
            ->assertSee('Ventas totales')
            ->assertSee('Exportar CSV')
            ->assertViewHas('report', fn (array $report) => $report['period']->preset->value === 'this_month');
    }

    public function test_sidebar_shows_the_analysis_section_only_to_admin(): void
    {
        $this->actingAs($this->admin())->get(route('admin.dashboard'))->assertSee('Análisis')->assertSee('Reportes');
        $this->actingAs(User::factory()->comercial()->create())->get(route('admin.dashboard'))->assertDontSee('Análisis');
    }

    public function test_every_preset_renders(): void
    {
        foreach (['this_month', 'last_month', 'this_year', 'last_year'] as $period) {
            $this->actingAs($this->admin())->get(route('admin.reports.index', ['period' => $period]))->assertOk();
        }
    }

    public function test_custom_range_is_applied(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.reports.index', ['period' => 'custom', 'from' => '2026-03-01', 'to' => '2026-03-31']))
            ->assertOk()
            ->assertSee('01/03/2026 – 31/03/2026');
    }

    public function test_invalid_filters_go_back_to_the_page_with_errors(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.reports.index', ['period' => 'custom']))
            ->assertRedirect(route('admin.reports.index'))
            ->assertSessionHasErrors(['from', 'to']);

        $this->actingAs($admin)->get(route('admin.reports.index', ['period' => 'custom', 'from' => '2026-03-10', 'to' => '2026-03-01']))
            ->assertSessionHasErrors('to');

        $this->actingAs($admin)->get(route('admin.reports.index', ['period' => 'custom', 'from' => '2010-01-01', 'to' => '2026-03-01']))
            ->assertSessionHasErrors('to');

        $this->actingAs($admin)->get(route('admin.reports.index', ['period' => 'forever']))
            ->assertSessionHasErrors('period');
    }

    public function test_page_shows_products_and_customers_ranked_without_exposing_cost(): void
    {
        $product = Product::factory()->create(['name' => 'Motor WEG 5 HP', 'price' => 119000, 'cost_price' => 61234]);
        $order = Order::factory()->paid()->create(['company_name' => 'Metalmecánica Prueba S.A.S.', 'total' => 119000]);
        OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 1, 'unit_price' => 119000, 'line_total' => 119000]);

        $this->actingAs($this->admin())->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Motor WEG 5 HP')
            ->assertSee('Metalmecánica Prueba S.A.S.')
            ->assertSee('$ 119.000');
    }

    public function test_csv_export_downloads_the_period_report(): void
    {
        $product = Product::factory()->create(['name' => 'Motor WEG 5 HP', 'price' => 119000, 'cost_price' => 60000]);
        $order = Order::factory()->paid()->create(['total' => 119000]);
        OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 1, 'unit_price' => 119000, 'line_total' => 119000]);

        $response = $this->actingAs($this->admin())->get(route('admin.reports.export', ['period' => 'this_month']));

        $response->assertOk()->assertDownload();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        $csv = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('"Ventas totales";119000;', $csv);
        $this->assertStringContainsString('Motor WEG 5 HP', $csv);
    }

    public function test_csv_export_rejects_invalid_range(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.reports.export', ['period' => 'custom', 'from' => '2026-03-10', 'to' => '2026-03-01']))
            ->assertSessionHasErrors('to');
    }
}
