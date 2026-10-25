<?php

namespace Tests\Feature\Api\V1;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::factory()->create(['address' => 'Calle 1 # 2-3']);
        Sanctum::actingAs($this->customer->user);
    }

    public function test_post_orders_creates_a_purchase(): void
    {
        $product = Product::factory()->create(['price' => 119000, 'stock' => 10]);

        $this->postJson('/api/v1/orders', [
            'delivery_method' => 'pickup',
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ])
            ->assertCreated()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', 'purchase')
            ->assertJsonPath('data.0.status', 'pending_payment')
            ->assertJsonPath('data.0.total', 238000)
            ->assertJsonPath('data.0.items.0.sku', $product->sku)
            ->assertJsonPath('data.0.items.0.line_total', 238000);

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_status_histories', 1);
    }

    public function test_post_orders_with_a_mixed_cart_creates_a_purchase_and_a_quote(): void
    {
        $direct = Product::factory()->create(['stock' => 5]);
        $quote = Product::factory()->quoteOnly()->create();
        $outOfStock = Product::factory()->outOfStock()->create();

        $this->postJson('/api/v1/orders', [
            'delivery_method' => 'local',
            'items' => [
                ['product_id' => $direct->id, 'quantity' => 1],
                ['product_id' => $quote->id, 'quantity' => 2],
                ['product_id' => $outOfStock->id, 'quantity' => 1, 'notes' => 'Urgente'],
            ],
        ])
            ->assertCreated()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.type', 'purchase')
            ->assertJsonPath('data.1.type', 'quote')
            ->assertJsonPath('data.1.status', 'new')
            ->assertJsonPath('data.1.total', null)
            ->assertJsonCount(2, 'data.1.items')
            ->assertJsonPath('data.1.items.1.notes', 'Urgente');
    }

    public function test_post_orders_only_quote_products_creates_only_a_quote(): void
    {
        $quote = Product::factory()->quoteOnly()->create();

        $this->postJson('/api/v1/orders', ['delivery_method' => 'pickup', 'items' => [['product_id' => $quote->id, 'quantity' => 1]]])
            ->assertCreated()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', 'quote');
    }

    public function test_post_orders_validates_the_cart(): void
    {
        $product = Product::factory()->create();

        $this->postJson('/api/v1/orders', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['items', 'delivery_method']);

        $this->postJson('/api/v1/orders', ['delivery_method' => 'pickup', 'items' => []])
            ->assertUnprocessable()
            ->assertJsonPath('errors.items.0', 'El carrito está vacío.');

        $this->postJson('/api/v1/orders', ['delivery_method' => 'drone', 'items' => [['product_id' => $product->id, 'quantity' => 1]]])
            ->assertUnprocessable()->assertJsonValidationErrors('delivery_method');

        foreach ([0, -1, 'mucho', 1.234] as $quantity) {
            $this->postJson('/api/v1/orders', ['delivery_method' => 'pickup', 'items' => [['product_id' => $product->id, 'quantity' => $quantity]]])
                ->assertUnprocessable()->assertJsonValidationErrors('items.0.quantity');
        }

        $this->postJson('/api/v1/orders', ['delivery_method' => 'pickup', 'items' => [
            ['product_id' => $product->id, 'quantity' => 1], ['product_id' => $product->id, 'quantity' => 2],
        ]])->assertUnprocessable()->assertJsonValidationErrors('items.1.product_id');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_post_orders_rejects_missing_inactive_or_deleted_products(): void
    {
        $inactive = Product::factory()->create(['is_active' => false]);
        $deleted = Product::factory()->create();
        $deleted->delete();

        foreach ([$inactive->id, $deleted->id, 99999] as $id) {
            $this->postJson('/api/v1/orders', ['delivery_method' => 'pickup', 'items' => [['product_id' => $id, 'quantity' => 1]]])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('items.0.product_id');
        }
    }

    public function test_decimal_quantity_is_only_allowed_for_cable_sold_by_meter(): void
    {
        $cable = Product::factory()->create(['unit' => 'metro', 'stock' => 100]);
        $unit = Product::factory()->create(['stock' => 10]);

        $this->postJson('/api/v1/orders', ['delivery_method' => 'pickup', 'items' => [['product_id' => $cable->id, 'quantity' => 50.5]]])
            ->assertCreated()
            ->assertJsonPath('data.0.items.0.quantity', 50.5);

        $this->postJson('/api/v1/orders', ['delivery_method' => 'pickup', 'items' => [['product_id' => $unit->id, 'quantity' => 1.5]]])
            ->assertUnprocessable()
            ->assertJsonPath('message', "«{$unit->name}» se vende por unidad: la cantidad debe ser un número entero.");
    }

    public function test_delivery_needs_an_address_when_the_customer_has_none(): void
    {
        $this->customer->update(['address' => null]);
        $product = Product::factory()->create(['stock' => 5]);
        $cart = ['items' => [['product_id' => $product->id, 'quantity' => 1]]];

        $this->postJson('/api/v1/orders', $cart + ['delivery_method' => 'national'])
            ->assertUnprocessable()->assertJsonValidationErrors('address');

        $this->postJson('/api/v1/orders', $cart + ['delivery_method' => 'pickup'])->assertCreated();
        $this->postJson('/api/v1/orders', $cart + ['delivery_method' => 'national', 'address' => 'Vereda La Esperanza'])
            ->assertCreated()->assertJsonPath('data.0.address', 'Vereda La Esperanza');
    }

    public function test_get_orders_lists_only_my_requests_newest_first(): void
    {
        $older = Order::factory()->create(['customer_id' => $this->customer->id, 'created_at' => now()->subDay()]);
        $newer = Order::factory()->quote()->create(['customer_id' => $this->customer->id]);
        Order::factory()->create(); // de otro cliente

        $this->getJson('/api/v1/orders')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.code', $newer->code)
            ->assertJsonPath('data.1.code', $older->code)
            ->assertJsonPath('meta.total', 2);

        $this->getJson('/api/v1/orders?type=quote')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.code', $newer->code);
        $this->getJson('/api/v1/orders?status=cancelled')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/orders?status=inventado')->assertUnprocessable();
    }

    public function test_get_order_by_code_returns_details_and_history(): void
    {
        $order = Order::factory()->create(['customer_id' => $this->customer->id]);
        OrderItem::factory()->create(['order_id' => $order->id]);

        $this->getJson("/api/v1/orders/{$order->code}")
            ->assertOk()
            ->assertJsonPath('data.code', $order->code)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonStructure(['data' => ['status_history', 'items' => [['sku', 'product_name', 'quantity', 'unit_price', 'line_total']]]])
            ->assertJsonMissingPath('data.assigned_to');
    }

    public function test_another_customers_order_is_a_404(): void
    {
        $foreign = Order::factory()->create();

        $this->getJson("/api/v1/orders/{$foreign->code}")->assertNotFound();
        $this->patchJson("/api/v1/orders/{$foreign->code}/cancel")->assertNotFound();
        $this->assertSame(OrderStatus::PendingPayment, $foreign->fresh()->status);
    }

    public function test_cancel_is_a_status_change_not_a_delete(): void
    {
        $quote = Order::factory()->quote()->create(['customer_id' => $this->customer->id]);

        $this->patchJson("/api/v1/orders/{$quote->code}/cancel")
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled')
            ->assertJsonPath('data.status_history.0.to_status', 'cancelled');

        $this->assertDatabaseHas('orders', ['id' => $quote->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('order_status_histories', ['order_id' => $quote->id, 'to_status' => 'cancelled', 'user_id' => $this->customer->user_id]);
    }

    public function test_cancel_in_a_state_that_does_not_allow_it_returns_422(): void
    {
        $order = Order::factory()->create(['customer_id' => $this->customer->id, 'status' => OrderStatus::Shipped]);

        $this->patchJson("/api/v1/orders/{$order->code}/cancel")->assertUnprocessable();
        $this->assertSame(OrderStatus::Shipped, $order->fresh()->status);

        // Y no se puede cancelar dos veces
        $pending = Order::factory()->create(['customer_id' => $this->customer->id]);
        $this->patchJson("/api/v1/orders/{$pending->code}/cancel")->assertOk();
        $this->patchJson("/api/v1/orders/{$pending->code}/cancel")->assertUnprocessable();
    }

    public function test_there_is_no_delete_endpoint_for_orders(): void
    {
        $order = Order::factory()->create(['customer_id' => $this->customer->id]);

        $this->deleteJson("/api/v1/orders/{$order->code}")->assertStatus(405);
        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }

    public function test_orders_need_a_client_token(): void
    {
        $this->app['auth']->forgetGuards();

        $this->getJson('/api/v1/orders')->assertUnauthorized();
        $this->postJson('/api/v1/orders', [])->assertUnauthorized();

        Sanctum::actingAs(User::factory()->admin()->create());
        $this->getJson('/api/v1/orders')->assertForbidden();
        $this->postJson('/api/v1/orders', [])->assertForbidden();

        Sanctum::actingAs(User::factory()->comercial()->create());
        $this->getJson('/api/v1/orders')->assertForbidden();
    }
}
