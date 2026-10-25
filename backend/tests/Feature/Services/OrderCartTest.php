<?php

namespace Tests\Feature\Services;

use App\Enums\DeliveryMethod;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Exceptions\BusinessRuleException;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Regla de clasificación del carrito, totales y envío de OrderService::createFromCart. */
class OrderCartTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $service;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(OrderService::class);
        $this->customer = Customer::factory()->create(['address' => 'Calle 1 # 2-3']);
        config(['shop.shipping.free_shipping_from' => 500000, 'shop.shipping.cost.local' => 15000, 'shop.shipping.cost.national' => 35000]);
    }

    /** @param  array<int, array{0: Product, 1: float|int}>  $lines */
    private function cart(array $lines, string $delivery = 'pickup', array $extra = []): array
    {
        return $extra + [
            'delivery_method' => $delivery,
            'items' => array_map(fn (array $line) => ['product_id' => $line[0]->id, 'quantity' => $line[1]], $lines),
        ];
    }

    public function test_direct_products_with_stock_form_a_purchase(): void
    {
        $product = Product::factory()->create(['price' => 119000, 'stock' => 10]);

        $orders = $this->service->createFromCart($this->customer, $this->cart([[$product, 2]]));

        $this->assertCount(1, $orders);
        $this->assertSame(OrderType::Purchase, $orders[0]->type);
        $this->assertSame(OrderStatus::PendingPayment, $orders[0]->status);
        $this->assertMatchesRegularExpression('/^PED-\d{4}$/', $orders[0]->code);
    }

    public function test_quote_and_on_request_products_form_a_quote(): void
    {
        $quote = Product::factory()->quoteOnly()->create();
        $onRequest = Product::factory()->onRequest()->create();

        $orders = $this->service->createFromCart($this->customer, $this->cart([[$quote, 1], [$onRequest, 3]]));

        $this->assertCount(1, $orders);
        $this->assertSame(OrderType::Quote, $orders[0]->type);
        $this->assertSame(OrderStatus::New, $orders[0]->status);
        $this->assertMatchesRegularExpression('/^COT-\d{4}$/', $orders[0]->code);
        $this->assertCount(2, $orders[0]->items);
    }

    public function test_direct_product_without_enough_stock_goes_to_the_quote(): void
    {
        $out = Product::factory()->outOfStock()->create();
        $short = Product::factory()->create(['stock' => 3]);

        $orders = $this->service->createFromCart($this->customer, $this->cart([[$out, 1], [$short, 4]]));

        $this->assertCount(1, $orders);
        $this->assertSame(OrderType::Quote, $orders[0]->type);
        // Su sale_mode no cambia por quedarse sin stock
        $this->assertSame('direct', $out->fresh()->sale_mode->value);
    }

    public function test_exact_stock_is_enough_but_decimals_round_up(): void
    {
        $exact = Product::factory()->create(['stock' => 4]);
        $cable = Product::factory()->create(['unit' => 'metro', 'stock' => 50]);

        // 4 de 4: alcanza. 50,5 m necesita 51 unidades de stock: no alcanza, va a cotización.
        $orders = $this->service->createFromCart($this->customer, $this->cart([[$exact, 4], [$cable, 50.5]]));

        $this->assertCount(2, $orders);
        $this->assertSame([OrderType::Purchase, OrderType::Quote], $orders->pluck('type')->all());
        $this->assertSame('50.50', $orders[1]->items[0]->quantity);
    }

    public function test_mixed_cart_generates_both_with_their_own_lines(): void
    {
        $direct = Product::factory()->create(['stock' => 5]);
        $quote = Product::factory()->quoteOnly()->create();

        $orders = $this->service->createFromCart($this->customer, $this->cart([[$direct, 1], [$quote, 2]]));

        $this->assertCount(2, $orders);
        $this->assertSame([$direct->sku], $orders[0]->items->pluck('sku')->all());
        $this->assertSame([$quote->sku], $orders[1]->items->pluck('sku')->all());
        $this->assertNotNull($orders[0]->items[0]->unit_price);
        $this->assertNull($orders[1]->items[0]->unit_price);
    }

    public function test_purchase_totals_split_tax_from_included_price(): void
    {
        $product = Product::factory()->create(['price' => 119000, 'tax_rate' => 19, 'stock' => 10]);

        $order = $this->service->createFromCart($this->customer, $this->cart([[$product, 2]]))[0];

        $this->assertSame('238000.00', $order->items[0]->line_total);
        $this->assertSame('200000.00', $order->subtotal);
        $this->assertSame('38000.00', $order->tax);
        $this->assertSame('0.00', $order->shipping);
        $this->assertSame('238000.00', $order->total);
    }

    public function test_quote_has_no_prices_nor_totals(): void
    {
        $quote = Product::factory()->quoteOnly()->create();

        $order = $this->service->createFromCart($this->customer, $this->cart([[$quote, 1]]))[0];

        $this->assertNull($order->subtotal);
        $this->assertNull($order->tax);
        $this->assertNull($order->shipping);
        $this->assertNull($order->total);
        $this->assertNull($order->items[0]->unit_price);
        $this->assertNull($order->items[0]->line_total);
    }

    public function test_shipping_is_charged_below_the_minimum_and_free_from_it(): void
    {
        $cheap = Product::factory()->create(['price' => 100000, 'stock' => 20]);

        $local = $this->service->createFromCart($this->customer, $this->cart([[$cheap, 1]], 'local'))[0];
        $national = $this->service->createFromCart($this->customer, $this->cart([[$cheap, 1]], 'national'))[0];
        $pickup = $this->service->createFromCart($this->customer, $this->cart([[$cheap, 1]], 'pickup'))[0];
        $free = $this->service->createFromCart($this->customer, $this->cart([[$cheap, 5]], 'national'))[0];

        $this->assertSame('15000.00', $local->shipping);
        $this->assertSame('115000.00', $local->total);
        $this->assertSame('35000.00', $national->shipping);
        $this->assertSame('0.00', $pickup->shipping);
        // 5 x 100.000 = 500.000: llega a la compra mínima
        $this->assertSame('0.00', $free->shipping);
        $this->assertSame('500000.00', $free->total);
    }

    public function test_it_copies_customer_and_product_data_and_records_history(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $orders = $this->service->createFromCart($this->customer, $this->cart([[$product, 1]], 'local', ['address' => 'Bodega 9', 'notes' => 'Portería']));
        $order = $orders[0];

        $this->assertSame($this->customer->company_name, $order->company_name);
        $this->assertSame($this->customer->nit, $order->nit);
        $this->assertSame($this->customer->user->email, $order->email);
        $this->assertSame('Bodega 9', $order->address);
        $this->assertSame('Portería', $order->notes);
        $this->assertSame(DeliveryMethod::Local, $order->delivery_method);
        $this->assertSame($product->name, $order->items[0]->product_name);

        $this->assertCount(1, $order->statusHistories);
        $this->assertNull($order->statusHistories[0]->from_status);
        $this->assertSame(OrderStatus::PendingPayment, $order->statusHistories[0]->to_status);
        $this->assertSame($this->customer->user_id, $order->statusHistories[0]->user_id);

        // Si el producto cambia después, el pedido no
        $product->update(['name' => 'Otro nombre', 'price' => 1]);
        $this->assertSame($orders[0]->items[0]->product_name, $order->fresh()->items[0]->product_name);
    }

    public function test_address_falls_back_to_the_customers(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $order = $this->service->createFromCart($this->customer, $this->cart([[$product, 1]], 'local'))[0];

        $this->assertSame('Calle 1 # 2-3', $order->address);
    }

    public function test_creating_a_cart_does_not_touch_stock(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $this->service->createFromCart($this->customer, $this->cart([[$product, 5]]));

        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_codes_are_consecutive_per_type(): void
    {
        $direct = Product::factory()->create(['stock' => 50]);

        $first = $this->service->createFromCart($this->customer, $this->cart([[$direct, 1]]))[0];
        $second = $this->service->createFromCart($this->customer, $this->cart([[$direct, 1]]))[0];

        $this->assertSame('PED-0001', $first->code);
        $this->assertSame('PED-0002', $second->code);
    }

    public function test_only_metro_products_accept_decimal_quantities(): void
    {
        $unit = Product::factory()->create(['stock' => 10]);

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('la cantidad debe ser un número entero');

        $this->service->createFromCart($this->customer, $this->cart([[$unit, 1.5]]));
    }

    public function test_a_failure_creates_nothing(): void
    {
        $ok = Product::factory()->create(['stock' => 5]);
        $deleted = Product::factory()->create();
        $deleted->delete();

        try {
            $this->service->createFromCart($this->customer, $this->cart([[$ok, 1], [$deleted, 1]]));
            $this->fail('Debió rechazar el producto eliminado.');
        } catch (BusinessRuleException) {
            $this->assertSame(0, Order::count());
        }
    }

    public function test_customer_can_cancel_only_what_is_still_unmanaged(): void
    {
        $purchase = Order::factory()->create(['customer_id' => $this->customer->id]);
        $shipped = Order::factory()->create(['customer_id' => $this->customer->id, 'status' => OrderStatus::Shipped]);

        $cancelled = $this->service->cancelForCustomer($this->customer, $purchase->code);

        $this->assertSame(OrderStatus::Cancelled, $cancelled->status);
        $this->assertSame('Cancelado por el cliente', $cancelled->statusHistories->last()->note);
        $this->assertDatabaseHas('orders', ['id' => $purchase->id]);

        $this->expectException(BusinessRuleException::class);
        $this->service->cancelForCustomer($this->customer, $shipped->code);
    }
}
