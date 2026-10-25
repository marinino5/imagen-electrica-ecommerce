<?php

namespace Database\Factories;

use App\Enums\ProductUnit;
use App\Enums\SaleMode;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = 'Motor trifásico '.fake()->unique()->numberBetween(1, 9999).' HP';

        return [
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'sku' => fake()->unique()->bothify('REF-####-??'),
            'name' => $name,
            'slug' => Str::slug($name),
            'short_description' => fake()->sentence(),
            'description' => fake()->optional()->paragraph(),
            'image_url' => null,
            'specs' => [
                'potencia_hp' => fake()->randomElement([1, 2, 5, 10]),
                'voltaje_v' => fake()->randomElement([220, 440]),
            ],
            'unit' => ProductUnit::Unidad,
            'price' => fake()->numberBetween(100, 5000) * 1000,
            'tax_rate' => 19.00,
            'stock' => fake()->numberBetween(1, 30),
            'sale_mode' => SaleMode::Direct,
            'is_active' => true,
            'is_featured' => false,
        ];
    }

    public function quoteOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'sale_mode' => SaleMode::Quote,
            'price' => null,
        ]);
    }

    public function onRequest(): static
    {
        return $this->state(fn (array $attributes) => [
            'sale_mode' => SaleMode::OnRequest,
            'price' => null,
            'stock' => 0,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => ['stock' => 0]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => ['is_featured' => true]);
    }
}
