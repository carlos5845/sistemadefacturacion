<?php

use App\Models\CatalogTaxType;
use App\Models\CatalogUnit;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;

test('authenticated users can view products index', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Product::factory()->count(3)->create(['company_id' => $user->company_id]);

    $this->get(route('products.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Products/Index')
            ->has('products.data', 3)
        );
});

test('authenticated users can create a product', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = ProductCategory::factory()->create(['company_id' => $user->company_id]);
    $unit = CatalogUnit::first() ?? CatalogUnit::factory()->create();
    $taxType = CatalogTaxType::first() ?? CatalogTaxType::factory()->create();

    $productData = [
        'name' => 'Test Product',
        'description' => 'Test Description',
        'category_id' => $category->id,
        'unit_type' => $unit->code,
        'sale_price' => 100.50,
        'purchase_price' => 80.00,
        'tax_type' => $taxType->code,
        'has_igv' => true,
        'active' => true,
    ];

    $this->post(route('products.store'), $productData)
        ->assertRedirect(route('products.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('products', [
        'name' => 'Test Product',
        'company_id' => $user->company_id,
    ]);
});

test('product creation requires valid unit type', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->post(route('products.store'), [
        'name' => 'Test Product',
        'unit_type' => 'INVALID',
        'sale_price' => 100,
        'tax_type' => '10',
    ])->assertSessionHasErrors('unit_type');
});

test('authenticated users can view a product', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['company_id' => $user->company_id]);
    $this->actingAs($user);

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Products/Show')
            ->has('product')
        );
});

test('users cannot view products from other companies', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $product = Product::factory()->create(['company_id' => $user1->company_id]);
    $this->actingAs($user2);

    $this->get(route('products.show', $product))
        ->assertForbidden();
});

test('authenticated users can update a product', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['company_id' => $user->company_id]);
    $this->actingAs($user);

    $this->put(route('products.update', $product), [
        'name' => 'Updated Product',
        'unit_type' => $product->unit_type,
        'sale_price' => 150.00,
        'tax_type' => $product->tax_type,
    ])->assertRedirect(route('products.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Updated Product',
    ]);
});

test('authenticated users can delete a product', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['company_id' => $user->company_id]);
    $this->actingAs($user);

    $this->delete(route('products.destroy', $product))
        ->assertRedirect(route('products.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});
