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
        $identityType = fake()->randomElement(['1', '6', '4', '7']);

        return [
            'company_id' => Company::factory(),
            'identity_type' => $identityType,
            'identity_number' => match ($identityType) {
                '1' => fake()->unique()->numerify('########'),
                '6' => fake()->unique()->numerify('20#########'),
                '4' => fake()->unique()->bothify('??########'),
                '7' => fake()->unique()->bothify('??########'),
                default => fake()->unique()->numerify('########'),
            },
            'name' => fake()->name(),
            'address' => fake()->address(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('9########'),
        ];
    }
}
