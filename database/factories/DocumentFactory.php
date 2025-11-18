<?php

namespace Database\Factories;

use App\Models\CatalogDocumentType;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalTaxed = fake()->randomFloat(2, 100, 10000);
        $totalIgv = $totalTaxed * 0.18;
        $total = $totalTaxed + $totalIgv;

        return [
            'company_id' => Company::factory(),
            'customer_id' => Customer::factory(),
            'document_type' => CatalogDocumentType::inRandomOrder()->first()?->code ?? '01',
            'series' => fake()->bothify('F###'),
            'number' => fake()->unique()->numberBetween(1, 999999),
            'issue_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'currency' => 'PEN',
            'total_taxed' => $totalTaxed,
            'total_igv' => $totalIgv,
            'total' => $total,
            'xml' => null,
            'xml_signed' => null,
            'hash' => null,
            'status' => fake()->randomElement(['PENDING', 'SENT', 'ACCEPTED', 'REJECTED', 'CANCELED']),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'PENDING',
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ACCEPTED',
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'REJECTED',
        ]);
    }
}
