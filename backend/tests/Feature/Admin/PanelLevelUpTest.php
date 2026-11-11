<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Panel «otro nivel»: filtro de período global, buscador global, documento real del cliente, embudo y exportaciones. */
class PanelLevelUpTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_the_period_chosen_is_remembered_for_home_and_analysis(): void
    {
        $this->actingAs($this->admin());

        $this->get(route('admin.dashboard', ['period' => 'last_7']))->assertOk()->assertSee('Últimos 7 días');
        // Sin escribir el período en la URL, Análisis usa el recordado
        $this->get(route('admin.reports.index'))->assertOk()->assertViewHas('report', fn (array $r) => $r['period']->preset->value === 'last_7');
        $this->get(route('admin.dashboard'))->assertViewHas('overview', fn (array $o) => $o['period']->preset->value === 'last_7');
    }

    public function test_an_invalid_period_is_not_remembered(): void
    {
        $this->actingAs($this->admin());

        $this->get(route('admin.reports.index', ['period' => 'custom', 'from' => '2026-03-10', 'to' => '2026-03-01']))->assertRedirect();
        $this->get(route('admin.reports.index'))->assertViewHas('report', fn (array $r) => $r['period']->preset->value === 'last_30');
    }

    public function test_overview_has_a_change_a_series_and_a_funnel_for_the_period(): void
    {
        Order::factory()->create(['created_at' => now()->subDays(2)]);
        Order::factory()->quote()->create(['created_at' => now()->subDay()]);

        $overview = app(DashboardService::class)->overview();

        $this->assertSame('last_30', $overview['period']->preset->value);
        $this->assertCount(30, $overview['kpis']['orders']['series']);
        $this->assertSame(1, $overview['kpis']['orders']['value']);
        $this->assertSame(1, $overview['funnel'][0]['value'], 'una cotización recibida');
        $this->assertSame(['Cotizaciones recibidas', 'Respondidas con precio', 'Aceptadas por el cliente', 'Pedidos pagados'], array_column($overview['funnel'], 'label'));
    }

    public function test_a_long_period_groups_the_chart_by_month(): void
    {
        $overview = app(DashboardService::class)->overview(\App\Support\ReportPeriod::resolve(\App\Enums\ReportPreset::ThisYear));

        $this->assertTrue($overview['chart']['monthly']);
        $this->assertLessThanOrEqual(12, count($overview['chart']['labels']));
    }

    public function test_global_search_finds_orders_and_customers_too(): void
    {
        $customer = Customer::factory()->create(['company_name' => 'Inversiones Quimbaya', 'nit' => '900123456-7']);
        Order::factory()->create(['company_name' => 'Inversiones Quimbaya', 'code' => 'PED-7777']);

        $this->actingAs(User::factory()->comercial()->create())
            ->get(route('admin.search', ['q' => 'Quimbaya']))
            ->assertOk()
            ->assertSee('PED-7777')
            ->assertSee('Inversiones Quimbaya')
            ->assertSee(route('admin.customers.show', $customer), false);
    }

    public function test_customers_show_the_real_document_type(): void
    {
        $company = Customer::factory()->create(['company_name' => 'Acme Ltda', 'nit' => '860005224']);
        Customer::factory()->create(['type' => 'natural', 'company_name' => null, 'contact_name' => 'Laura Gómez', 'nit' => '1097490864']);

        $this->actingAs($this->admin())->get(route('admin.customers.index'))
            ->assertOk()
            ->assertSee('NIT 860005224-6')
            ->assertSee('CC 1097490864')
            ->assertSee('Persona natural');
        $this->get(route('admin.customers.show', $company))->assertSee('NIT 860005224-6');
    }

    public function test_analysis_exports_csv_and_pdf_for_both_tabs(): void
    {
        $this->actingAs($this->admin());

        foreach (['admin.reports.export', 'admin.reports.commercial.export'] as $route) {
            $this->get(route($route))->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        }
        foreach (['admin.reports.pdf', 'admin.reports.commercial.pdf'] as $route) {
            $this->get(route($route))->assertOk()->assertHeader('content-type', 'application/pdf');
        }
    }

    public function test_the_two_analysis_tabs_look_different(): void
    {
        $this->actingAs($this->admin());

        $this->get(route('admin.reports.index'))->assertSee('analysis-financial', false)->assertSee('Análisis financiero');
        $this->get(route('admin.reports.commercial'))->assertSee('analysis-commercial', false)->assertSee('Análisis comercial');
    }

    public function test_status_chips_filter_the_orders_list(): void
    {
        Order::factory()->create(['code' => 'PED-1111']);
        Order::factory()->create(['code' => 'PED-2222', 'status' => OrderStatus::Paid]);

        $this->actingAs($this->admin())->get(route('admin.orders.index', ['status' => 'paid']))
            ->assertSee('PED-2222')->assertDontSee('PED-1111')->assertSee('chip', false);
    }
}
