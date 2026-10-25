<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Copia sku, nombre, unidad, precio e IVA del producto, como hará OrderService.
 *
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'sku' => fn (array $attributes) => Product::find($attributes['product_id'])->sku,
            'product_name' => fn (array $attributes) => Product::find($attributes['product_id'])->name,
            'unit' => fn (array $attributes) => Product::find($attributes['product_id'])->unit,
            'quantity' => fake()->numberBetween(1, 5),
            'unit_price' => fn (array $attributes) => Product::find($attributes['product_id'])->price,
            'tax_rate' => fn (array $attributes) => Product::find($attributes['product_id'])->tax_rate,
            'line_total' => fn (array $attributes) => $attributes['unit_price'] === null
                ? null
                : round($attributes['unit_price'] * $attributes['quantity'], 2),
            'notes' => null,
        ];
    }
}
