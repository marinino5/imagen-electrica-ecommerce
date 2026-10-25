<?php

namespace Tests\Feature\Services;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Exceptions\BusinessRuleException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(OrderService::class);
    }

    /** Cotización con una línea por producto indicado; precios opcionales. */
    private function quote(OrderStatus $status = OrderStatus::New, array $prices = []): Order
    {
        $quote = Order::factory()->quote()->create(['status' => $status]);

        foreach ($prices ?: [null] as $price) {
            OrderItem::factory()->create([
                'order_id' => $quote->id,
                'product_id' => Product::factory()->quoteOnly()->create()->id,
                'quantity' => 2,
                'unit_price' => $price,
                'line_total' => $price === null ? null : $price * 2,
            ]);
        }

        return $quote;
    }

    // ---------- Transiciones válidas ----------

    public function test_purchase_follows_the_full_valid_flow_and_records_history(): void
    {
        $user = User::factory()->comercial()->create();
        $order = Order::factory()->create();
        OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => Product::factory()->create(['stock' => 10])->id, 'quantity' => 1]);

        foreach ([OrderStatus::Paid, OrderStatus::Shipped, OrderStatus::Delivered] as $status) {
            $this->service->changeStatus($order, $status, $user);
        }

        $this->assertSame(OrderStatus::Delivered, $order->fresh()->status);
        $this->assertSame(
            [OrderStatus::PendingPayment, OrderStatus::Paid, OrderStatus::Shipped],
            $order->statusHistories()->pluck('from_status')->all(),
        );
        $this->assertSame($user->id, $order->statusHistories()->first()->user_id);
    }

    public function test_pending_purchase_can_be_cancelled(): void
    {
        $order = Order::factory()->create();

        $this->service->changeStatus($order, OrderStatus::Cancelled, null, 'El cliente se arrepintió');

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'from_status' => 'pending_payment',
            'to_status' => 'cancelled',
            'note' => 'El cliente se arrepintió',
        ]);
    }

    public function test_quote_follows_the_valid_flow_when_priced(): void
    {
        $quote = $this->quote(prices: [100000]);

        $this->service->changeStatus($quote, OrderStatus::InReview);
        $this->service->changeStatus($quote, OrderStatus::Sent);
        $this->service->changeStatus($quote, OrderStatus::Rejected);

        $this->assertSame(OrderStatus::Rejected, $quote->fresh()->status);
        $this->assertSame(3, $quote->statusHistories()->count());
    }

    public function test_new_quote_can_be_cancelled(): void
    {
        $quote = $this->quote();

        $this->service->changeStatus($quote, OrderStatus::Cancelled);

        $this->assertSame(OrderStatus::Cancelled, $quote->fresh()->status);
    }

    // ---------- Transiciones inválidas ----------

    /** @return array<string, array{OrderType, OrderStatus, OrderStatus}> */
    public static function invalidTransitions(): array
    {
        return [
            'pedido: saltar de pendiente a enviado' => [OrderType::Purchase, OrderStatus::PendingPayment, OrderStatus::Shipped],
            'pedido: cancelar uno ya pagado' => [OrderType::Purchase, OrderStatus::Paid, OrderStatus::Cancelled],
            'pedido: retroceder de enviado a pagado' => [OrderType::Purchase, OrderStatus::Shipped, OrderStatus::Paid],
            'pedido: entregado es final' => [OrderType::Purchase, OrderStatus::Delivered, OrderStatus::Shipped],
            'pedido: cancelado es final' => [OrderType::Purchase, OrderStatus::Cancelled, OrderStatus::PendingPayment],
            'pedido: usar un estado de cotización' => [OrderType::Purchase, OrderStatus::PendingPayment, OrderStatus::InReview],
            'cotización: saltar de nueva a enviada' => [OrderType::Quote, OrderStatus::New, OrderStatus::Sent],
            'cotización: cancelar una en revisión' => [OrderType::Quote, OrderStatus::InReview, OrderStatus::Cancelled],
            'cotización: aceptar sin haberla enviado' => [OrderType::Quote, OrderStatus::InReview, OrderStatus::Accepted],
            'cotización: aceptada es final' => [OrderType::Quote, OrderStatus::Accepted, OrderStatus::Rejected],
            'cotización: usar un estado de pedido' => [OrderType::Quote, OrderStatus::New, OrderStatus::Paid],
        ];
    }

    #[DataProvider('invalidTransitions')]
    public function test_invalid_transition_is_rejected_without_changes(OrderType $type, OrderStatus $from, OrderStatus $to): void
    {
        $order = $type === OrderType::Quote
            ? Order::factory()->quote()->create(['status' => $from])
            : Order::factory()->create(['status' => $from]);

        try {
            $this->service->changeStatus($order, $to);
            $this->fail('Debió lanzar BusinessRuleException');
        } catch (BusinessRuleException $e) {
            $this->assertStringContainsString('No se puede pasar', $e->getMessage());
        }

        $this->assertSame($from, $order->fresh()->status);
        $this->assertSame(0, $order->statusHistories()->count());
    }

    public function test_invalid_transition_answers_422_for_json_requests(): void
    {
        $exception = new BusinessRuleException('No se puede.');

        $response = $exception->render(\Illuminate\Http\Request::create('/api/v1/x', 'PATCH', server: ['HTTP_ACCEPT' => 'application/json']));

        $this->assertSame(422, $response->getStatusCode());
    }

    // ---------- Cotización sin precios ----------

    public function test_quote_without_prices_cannot_be_sent(): void
    {
        $quote = $this->quote(OrderStatus::InReview);

        try {
            $this->service->changeStatus($quote, OrderStatus::Sent);
            $this->fail('Debió lanzar BusinessRuleException');
        } catch (BusinessRuleException $e) {
            $this->assertStringContainsString('faltan precios en 1 producto', $e->getMessage());
        }

        $this->assertSame(OrderStatus::InReview, $quote->fresh()->status);
        $this->assertSame(0, $quote->statusHistories()->count());
    }

    public function test_quote_with_only_some_prices_cannot_be_sent(): void
    {
        $quote = $this->quote(OrderStatus::InReview, [100000, null, 50000]);

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('faltan precios en 1 producto');

        $this->service->changeStatus($quote, OrderStatus::Sent);
    }

    public function test_pricing_every_line_calculates_totals_with_included_vat(): void
    {
        $quote = $this->quote(OrderStatus::InReview);
        $item = $quote->items()->first();

        $this->service->priceQuote($quote, [$item->id => 119000], shipping: 10000);

        $quote->refresh();
        // 2 unidades x 119.000 = 238.000 con IVA 19%: base 200.000 + IVA 38.000
        $this->assertEquals(200000.00, $quote->subtotal);
        $this->assertEquals(38000.00, $quote->tax);
        $this->assertEquals(10000.00, $quote->shipping);
        $this->assertEquals(248000.00, $quote->total);

        $this->service->changeStatus($quote, OrderStatus::Sent);
        $this->assertSame(OrderStatus::Sent, $quote->fresh()->status);
    }

    public function test_totals_stay_empty_while_a_line_has_no_price(): void
    {
        $quote = $this->quote(prices: [null, null]);
        $first = $quote->items()->first();

        $this->service->priceQuote($quote, [$first->id => 50000]);

        $quote->refresh();
        $this->assertNull($quote->total);
        $this->assertNull($quote->subtotal);
        $this->assertSame(1, $this->service->missingPrices($quote->load('items')));
    }

    public function test_cannot_price_a_quote_already_sent_or_a_purchase(): void
    {
        $sent = $this->quote(OrderStatus::Sent, [100000]);
        $purchase = Order::factory()->create();

        foreach ([$sent, $purchase] as $order) {
            try {
                $this->service->priceQuote($order, []);
                $this->fail('Debió lanzar BusinessRuleException');
            } catch (BusinessRuleException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    // ---------- Cotización aceptada genera pedido ----------

    public function test_accepting_a_quote_creates_a_purchase_linked_to_it(): void
    {
        $quote = $this->quote(OrderStatus::InReview);
        $this->service->priceQuote($quote, [$quote->items()->first()->id => 119000], shipping: 0);
        $this->service->changeStatus($quote, OrderStatus::Sent);

        $this->service->changeStatus($quote, OrderStatus::Accepted, User::factory()->comercial()->create());

        $purchase = Order::where('quote_id', $quote->id)->firstOrFail();

        $this->assertSame(OrderType::Purchase, $purchase->type);
        $this->assertSame(OrderStatus::PendingPayment, $purchase->status);
        $this->assertSame('PED-0001', $purchase->code);
        $this->assertSame($quote->company_name, $purchase->company_name);
        $this->assertSame($quote->nit, $purchase->nit);
        $this->assertEquals(238000.00, $purchase->total);
        $this->assertSame($quote->customer_id, $purchase->customer_id);

        // Líneas copiadas con precio
        $this->assertSame(1, $purchase->items()->count());
        $this->assertEquals(119000.00, $purchase->items()->first()->unit_price);
        $this->assertSame($quote->items()->first()->product_name, $purchase->items()->first()->product_name);

        // La cotización se conserva aceptada y el pedido nace con historial
        $this->assertSame(OrderStatus::Accepted, $quote->fresh()->status);
        $this->assertDatabaseHas('order_status_histories', ['order_id' => $purchase->id, 'from_status' => null, 'to_status' => 'pending_payment']);
    }

    public function test_generated_purchase_codes_continue_the_sequence(): void
    {
        Order::factory()->create(['code' => 'PED-0007']);
        $quote = $this->quote(OrderStatus::Sent, [100000]);

        $this->service->changeStatus($quote, OrderStatus::Accepted);

        $this->assertDatabaseHas('orders', ['quote_id' => $quote->id, 'code' => 'PED-0008']);
    }

    public function test_rejecting_a_quote_does_not_create_a_purchase(): void
    {
        $quote = $this->quote(OrderStatus::Sent, [100000]);

        $this->service->changeStatus($quote, OrderStatus::Rejected);

        $this->assertDatabaseMissing('orders', ['quote_id' => $quote->id]);
    }

    // ---------- Inventario al confirmar el pago ----------

    public function test_confirming_payment_deducts_stock_and_sets_paid_at(): void
    {
        $product = Product::factory()->create(['stock' => 10]);
        $order = Order::factory()->create();
        OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 4]);

        $this->service->changeStatus($order, OrderStatus::Paid);

        $this->assertSame(6, $product->fresh()->stock);
        $this->assertNotNull($order->fresh()->paid_at);
    }

    public function test_payment_fails_without_enough_stock_and_changes_nothing(): void
    {
        $enough = Product::factory()->create(['stock' => 10]);
        $short = Product::factory()->create(['stock' => 1]);
        $order = Order::factory()->create();
        OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $enough->id, 'quantity' => 3]);
        OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $short->id, 'quantity' => 2]);

        try {
            $this->service->changeStatus($order, OrderStatus::Paid);
            $this->fail('Debió lanzar BusinessRuleException');
        } catch (BusinessRuleException $e) {
            $this->assertStringContainsString('Stock insuficiente', $e->getMessage());
        }

        $this->assertSame(10, $enough->fresh()->stock);
        $this->assertSame(OrderStatus::PendingPayment, $order->fresh()->status);
        $this->assertSame(0, $order->statusHistories()->count());
    }

    // ---------- Asignación ----------

    public function test_assign_accepts_active_commercial_only(): void
    {
        $order = Order::factory()->create();
        $comercial = User::factory()->comercial()->create();

        $this->service->assign($order, $comercial->id);
        $this->assertSame($comercial->id, $order->fresh()->assigned_to);

        $this->service->assign($order, null);
        $this->assertNull($order->fresh()->assigned_to);

        $this->expectException(BusinessRuleException::class);
        $this->service->assign($order, User::factory()->create()->id);
    }
}
