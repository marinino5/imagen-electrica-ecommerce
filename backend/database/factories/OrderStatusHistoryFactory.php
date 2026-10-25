<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Por defecto registra la creación del pedido (sin estado anterior).
 *
 * @extends Factory<OrderStatusHistory>
 */
class OrderStatusHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'user_id' => null,
            'from_status' => null,
            'to_status' => OrderStatus::PendingPayment,
            'note' => null,
        ];
    }
}
