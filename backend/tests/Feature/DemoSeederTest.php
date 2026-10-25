<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Las contraseñas de los seeders vienen del .env, que no existe en todos los entornos
        config(['app.seed.admin_password' => 'clave-admin', 'app.seed.comercial_password' => 'clave-comercial', 'app.seed.customer_password' => 'clave-cliente']);
    }

    public function test_database_seeder_does_not_load_demo_data(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(3, Order::count()); // solo los 3 ejemplos de OrderSeeder
        $this->assertSame(0, Product::whereNotNull('cost_price')->count());
    }

    public function test_demo_seeder_loads_two_years_of_orders_and_quotes_with_costs(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DemoSeeder::class);

        $this->assertGreaterThan(300, Order::where('type', 'purchase')->count());
        $this->assertGreaterThan(150, Order::where('type', 'quote')->count());
        $this->assertTrue(Order::where('created_at', '<', now()->subMonths(13))->exists(), 'falta el año anterior para comparar');
        $this->assertTrue(Order::where('created_at', '>', now()->subDays(7))->exists());

        // Nada con fecha futura y todo pedido pagado tiene fecha de pago
        $this->assertSame(0, Order::where('created_at', '>', now())->count());
        $this->assertSame(0, OrderStatusHistory::where('created_at', '>', now())->count());
        $this->assertSame(0, Order::whereIn('status', ['paid', 'shipped', 'delivered'])->whereNull('paid_at')->count());

        // Cotizaciones aceptadas generan su pedido, y las enviadas tienen precios completos
        $this->assertTrue(Order::whereNotNull('quote_id')->exists());
        $this->assertSame(0, Order::where('type', 'quote')->whereIn('status', ['sent', 'accepted', 'rejected'])->whereNull('total')->count());

        // Costos de ejemplo, dejando algunos productos sin costo
        $this->assertSame(2, Product::whereNull('cost_price')->count());
    }

    public function test_demo_seeder_runs_only_once(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DemoSeeder::class);
        $orders = Order::count();

        $this->seed(DemoSeeder::class);

        $this->assertSame($orders, Order::count());
    }

    public function test_reports_page_renders_with_demo_data(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DemoSeeder::class);
        $admin = User::where('email', config('app.seed.admin_email'))->firstOrFail();

        foreach (['this_month', 'last_month', 'this_year', 'last_year'] as $period) {
            $this->actingAs($admin)->get(route('admin.reports.index', ['period' => $period]))
                ->assertOk()
                ->assertSee('Ventas totales');
        }

        $this->actingAs($admin)->get(route('admin.reports.export', ['period' => 'last_year']))->assertOk();
    }
}
