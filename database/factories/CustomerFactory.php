<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $identityType = fake()->randomElement(['DNI', 'RUC', 'CE', 'PAS']);

        return [
            'company_id' => Company::factory(),
            'identity_type' => $identityType,
            'identity_number' => match ($identityType) {
                'DNI' => fake()->unique()->numerify('########'),
                'RUC' => fake()->unique()->numerify('20#########'),
                'CE' => fake()->unique()->bothify('??########'),
                'PAS' => fake()->unique()->bothify('??########'),
                default => fake()->unique()->numerify('########'),
            },
            'name' => fake()->name(),
            'address' => fake()->address(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('9########'),
        ];
    }
}
