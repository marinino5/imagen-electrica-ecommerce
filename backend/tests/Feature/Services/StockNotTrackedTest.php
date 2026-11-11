<?php

namespace Tests\Feature\Services;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Customer;
use App\Models\Product;
use App\Services\OrderService;
use Database\Seeders\BrandSeeder;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Antes de conectar Siigo el stock es 0 y no se usa: todo producto con precio se compra y nada dice «agotado». */
class StockNotTrackedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['shop.track_stock' => false]);
    }

    public function test_a_product_with_price_and_zero_stock_is_a_purchase_and_payment_does_not_need_units(): void
    {
        $product = Product::factory()->create(['price' => 119000, 'stock' => 0]);
        $orders = app(OrderService::class)->createFromCart(Customer::factory()->create(['address' => 'Calle 1']), [
            'delivery_method' => 'pickup',
            'items' => [['product_id' => $product->id, 'quantity' => 3]],
        ]);

        $this->assertSame(OrderType::Purchase, $orders[0]->type);

        $paid = app(OrderService::class)->changeStatus($orders[0], OrderStatus::Paid);
        $this->assertSame(OrderStatus::Paid, $paid->status);
        $this->assertSame(0, $product->fresh()->stock, 'sin stock confiable no se descuenta nada (ni queda negativo)');
    }

    public function test_the_api_never_reports_a_priced_line_as_unavailable(): void
    {
        // Como en producción antes de Siigo: catálogo real con todo el stock en 0
        $this->seed([BrandSeeder::class, CatalogSeeder::class]);
        Product::query()->update(['stock' => 0]);

        $lines = collect($this->getJson('/api/v1/lines')->assertOk()->json('data'));
        $priced = $lines->filter(fn ($l) => $l['price_from'] !== null);

        $this->assertNotEmpty($priced);
        $this->assertTrue($priced->every(fn ($l) => $l['buyable'] === true), 'toda línea con precio se puede comprar');

        $detail = $this->getJson('/api/v1/lines/w22-iec-carcasa-hierro')->assertOk()->json('data.variants');
        $this->assertTrue(collect($detail)->every(fn ($v) => $v['in_stock'] === true));
    }

    public function test_with_stock_tracking_on_the_old_rules_come_back(): void
    {
        config(['shop.track_stock' => true]);
        $product = Product::factory()->create(['price' => 119000, 'stock' => 0]);

        $orders = app(OrderService::class)->createFromCart(Customer::factory()->create(['address' => 'Calle 1']), [
            'delivery_method' => 'pickup',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);

        $this->assertSame(OrderType::Quote, $orders[0]->type);
    }
}
