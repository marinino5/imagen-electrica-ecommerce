<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_name' => fake()->company().' S.A.S.',
            'nit' => fake()->unique()->numerify('9########-#'),
            'contact_name' => fake()->name(),
            'phone' => fake()->numerify('3## ### ####'),
            'city' => fake()->randomElement(['Bucaramanga', 'Floridablanca', 'Girón', 'Piedecuesta', 'Barrancabermeja']),
            'address' => fake()->streetAddress(),
            'marketing_opt_in' => false,
            'marketing_opt_in_at' => null,
            'privacy_accepted_at' => now(),
        ];
    }

    public function withMarketing(): static
    {
        return $this->state(fn (array $attributes) => [
            'marketing_opt_in' => true,
            'marketing_opt_in_at' => now(),
        ]);
    }
}
