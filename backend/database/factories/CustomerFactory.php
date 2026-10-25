<?php

namespace Database\Factories;

use App\Enums\CustomerType;
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
            'type' => CustomerType::Company,
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

    /** Persona natural: sin razón social y con la cédula (opcional) en nit. */
    public function natural(bool $withDocument = true): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CustomerType::Natural,
            'company_name' => null,
            'nit' => $withDocument ? fake()->unique()->numerify('1#########') : null,
        ]);
    }

    public function withMarketing(): static
    {
        return $this->state(fn (array $attributes) => [
            'marketing_opt_in' => true,
            'marketing_opt_in_at' => now(),
        ]);
    }
}
