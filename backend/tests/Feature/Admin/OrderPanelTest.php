<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPanelTest extends TestCase
{
    use RefreshDatabase;

    private function staff(): User
    {
        return User::factory()->comercial()->create();
    }

    public function test_admin_and_comercial_can_list_orders_but_clients_cannot(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get(route('admin.orders.index'))->assertOk();
        $this->actingAs($this->staff())->get(route('admin.orders.index'))->assertOk();
        $this->actingAs(User::factory()->create())->get(route('admin.orders.index'))->assertForbidden();
    }

    public function test_index_paginates_by_ten(): void
    {
        Order::factory()->count(12)->create();

        $this->actingAs($this->staff())->get(route('admin.orders.index'))
            ->assertViewHas('orders', fn ($page) => $page->perPage() === 10 && $page->total() === 12);
    }

    public function test_index_filters_by_type_and_status_from_the_url(): void
    {
        Order::factory()->create();
        Order::factory()->paid()->create();
        Order::factory()->quote()->create();
        Order::factory()->quote()->create(['status' => OrderStatus::InReview]);

        $total = fn (array $query) => $this->actingAs($this->staff())
            ->get(route('admin.orders.index', $query))
            ->viewData('orders')->total();

        $this->assertSame(2, $total(['type' => 'purchase']));
        $this->assertSame(2, $total(['type' => 'quote']));
        $this->assertSame(1, $total(['status' => 'paid']));
        $this->assertSame(1, $total(['type' => 'quote', 'status' => 'new']));
        $this->assertSame(2, $total(['status' => 'new,in_review']));
        $this->assertSame(4, $total(['type' => 'nope', 'status' => 'nope']));
    }

    public function test_index_filters_overdue_quotes(): void
    {
        $overdue = Order::factory()->quote()->create(['created_at' => now()->subHours(30)]);
        Order::factory()->quote()->create(['created_at' => now()->subHours(2)]);
        Order::factory()->quote()->create(['status' => OrderStatus::Sent, 'created_at' => now()->subDays(5)]);

        $this->actingAs($this->staff())->get(route('admin.orders.index', ['overdue' => 1]))
            ->assertViewHas('orders', fn ($page) => $page->total() === 1 && $page->first()->is($overdue));
    }

    public function test_index_searches_by_code_company_nit_or_contact(): void
    {
        Order::factory()->create(['code' => 'PED-0042', 'company_name' => 'Metalúrgica Andina', 'nit' => '900123456-7']);
        Order::factory()->create(['company_name' => 'Otra empresa']);

        foreach (['PED-0042', 'Andina', '900123456'] as $term) {
            $this->actingAs($this->staff())->get(route('admin.orders.index', ['q' => $term]))
                ->assertViewHas('orders', fn ($page) => $page->total() === 1);
        }
    }

    public function test_show_displays_customer_items_and_history(): void
    {
        $quote = Order::factory()->quote()->create(['company_name' => 'Constructora Norte']);
        OrderItem::factory()->create(['order_id' => $quote->id, 'product_name' => 'Motor WEG 5HP']);
        $quote->statusHistories()->create(['to_status' => OrderStatus::New, 'note' => 'Cotización creada']);

        $this->actingAs($this->staff())->get(route('admin.orders.show', $quote))
            ->assertOk()
            ->assertSee('Constructora Norte')
            ->assertSee('Motor WEG 5HP')
            ->assertSee('Cotización creada');
    }

    public function test_status_change_goes_through_order_service_and_saves_history(): void
    {
        $staff = $this->staff();
        $quote = Order::factory()->quote()->create();

        $this->actingAs($staff)
            ->patch(route('admin.orders.status', $quote), ['status' => 'in_review', 'note' => 'Revisando'])
            ->assertRedirect(route('admin.orders.show', $quote))
            ->assertSessionHas('status');

        $this->assertSame(OrderStatus::InReview, $quote->fresh()->status);
        $this->assertDatabaseHas('order_status_histories', ['order_id' => $quote->id, 'user_id' => $staff->id, 'note' => 'Revisando']);
    }

    public function test_invalid_transition_returns_to_the_page_with_an_error(): void
    {
        $order = Order::factory()->create();

        $this->actingAs($this->staff())->from(route('admin.orders.show', $order))
            ->patch(route('admin.orders.status', $order), ['status' => 'delivered'])
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHas('error');

        $this->assertSame(OrderStatus::PendingPayment, $order->fresh()->status);
    }

    public function test_invalid_transition_answers_422_when_json_is_expected(): void
    {
        $order = Order::factory()->create();

        $this->actingAs($this->staff())
            ->patchJson(route('admin.orders.status', $order), ['status' => 'delivered'])
            ->assertUnprocessable();
    }

    public function test_prices_are_saved_and_unlock_sending(): void
    {
        $quote = Order::factory()->quote()->create(['status' => OrderStatus::InReview]);
        $item = OrderItem::factory()->create([
            'order_id' => $quote->id,
            'product_id' => Product::factory()->quoteOnly()->create()->id,
            'quantity' => 1,
            'unit_price' => null,
        ]);

        $this->actingAs($this->staff())
            ->put(route('admin.orders.prices', $quote), ['prices' => [$item->id => '119000'], 'shipping' => '0'])
            ->assertRedirect(route('admin.orders.show', $quote));

        $this->assertEquals(119000.00, $quote->fresh()->total);

        $this->actingAs($this->staff())
            ->patch(route('admin.orders.status', $quote), ['status' => 'sent'])
            ->assertSessionHas('status');
    }

    public function test_prices_must_be_numeric(): void
    {
        $quote = Order::factory()->quote()->create();
        $item = OrderItem::factory()->create(['order_id' => $quote->id, 'unit_price' => null]);

        $this->actingAs($this->staff())
            ->put(route('admin.orders.prices', $quote), ['prices' => [$item->id => 'abc']])
            ->assertSessionHasErrors('prices.'.$item->id);
    }

    public function test_assigning_a_commercial(): void
    {
        $order = Order::factory()->quote()->create();
        $comercial = User::factory()->comercial()->create();

        $this->actingAs($this->staff())
            ->patch(route('admin.orders.assignment', $order), ['assigned_to' => $comercial->id])
            ->assertSessionHas('status');

        $this->assertSame($comercial->id, $order->fresh()->assigned_to);
    }

    public function test_assigning_a_client_user_is_rejected(): void
    {
        $order = Order::factory()->quote()->create();

        $this->actingAs($this->staff())->from(route('admin.orders.show', $order))
            ->patch(route('admin.orders.assignment', $order), ['assigned_to' => User::factory()->create()->id])
            ->assertSessionHas('error');

        $this->assertNull($order->fresh()->assigned_to);
    }
}
