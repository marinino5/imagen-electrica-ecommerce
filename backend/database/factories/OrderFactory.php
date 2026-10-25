<?php

namespace Database\Factories;

use App\Enums\DeliveryMethod;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Por defecto crea un pedido (purchase). Los datos del cliente se copian
 * de la ficha del cliente, como hará OrderService.
 *
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->numerify('PED-####'),
            'type' => OrderType::Purchase,
            'status' => OrderStatus::PendingPayment,
            'customer_id' => Customer::factory(),
            'assigned_to' => null,
            'quote_id' => null,
            'company_name' => fn (array $attributes) => Customer::find($attributes['customer_id'])->company_name,
            'nit' => fn (array $attributes) => Customer::find($attributes['customer_id'])->nit,
            'contact_name' => fn (array $attributes) => Customer::find($attributes['customer_id'])->contact_name,
            'email' => fn (array $attributes) => Customer::find($attributes['customer_id'])->user->email,
            'phone' => fn (array $attributes) => Customer::find($attributes['customer_id'])->phone,
            'city' => fn (array $attributes) => Customer::find($attributes['customer_id'])->city,
            'address' => fn (array $attributes) => Customer::find($attributes['customer_id'])->address,
            'delivery_method' => fake()->randomElement(DeliveryMethod::cases()),
            'payment_method' => null,
            'subtotal' => 840336.13,
            'tax' => 159663.87,
            'shipping' => 0,
            'total' => 1000000,
            'notes' => null,
            'expires_at' => null,
            'paid_at' => null,
        ];
    }

    /** Cotización nueva, todavía sin precios. */
    public function quote(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => fake()->unique()->numerify('COT-####'),
            'type' => OrderType::Quote,
            'status' => OrderStatus::New,
            'subtotal' => null,
            'tax' => null,
            'shipping' => null,
            'total' => null,
            'expires_at' => now()->addDays(15),
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Paid,
            'payment_method' => 'transferencia',
            'paid_at' => now(),
        ]);
    }
}
