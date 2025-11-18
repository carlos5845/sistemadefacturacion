<?php

namespace Database\Factories;

use App\Models\CatalogTaxType;
use App\Models\CatalogUnit;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $salePrice = fake()->randomFloat(2, 10, 1000);
        $purchasePrice = $salePrice * fake()->randomFloat(2, 0.5, 0.8);

        return [
            'company_id' => Company::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'category_id' => ProductCategory::factory(),
            'unit_type' => CatalogUnit::inRandomOrder()->first()?->code ?? 'NIU',
            'sale_price' => $salePrice,
            'purchase_price' => $purchasePrice,
            'tax_type' => CatalogTaxType::inRandomOrder()->first()?->code ?? '10',
            'has_igv' => fake()->boolean(80),
            'active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
}
