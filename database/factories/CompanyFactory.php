<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ruc' => fake()->unique()->numerify('20#########'),
            'business_name' => fake()->company(),
            'trade_name' => fake()->companySuffix(),
            'certificate' => null,
            'certificate_password' => null,
            'user_sol' => fake()->userName(),
            'password_sol' => fake()->password(),
            'address' => fake()->address(),
            'ubigeo' => fake()->numerify('######'),
        ];
    }
}
