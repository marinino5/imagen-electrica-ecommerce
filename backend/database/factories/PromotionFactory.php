<?php

namespace Database\Factories;

use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Promotion>
 */
class PromotionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->words(3, true),
            'description' => fake()->sentence(),
            'product_line_id' => null, // se pasa al crear: ->for($line, 'line') o ['product_line_id' => $id]
            'product_id' => null,
            'discount_percent' => 10,
            'special_price' => null,
            'starts_at' => today()->subDay(),
            'ends_at' => today()->addDays(10),
            'is_active' => true,
        ];
    }

    public function expired(): static
    {
        return $this->state(['starts_at' => today()->subDays(20), 'ends_at' => today()->subDay()]);
    }

    public function upcoming(): static
    {
        return $this->state(['starts_at' => today()->addDays(2), 'ends_at' => today()->addDays(9)]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function specialPrice(float $price): static
    {
        return $this->state(['discount_percent' => null, 'special_price' => $price]);
    }
}
