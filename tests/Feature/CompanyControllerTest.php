<?php

use App\Models\Company;
use App\Models\User;

test('guests cannot access companies index', function () {
    $this->get(route('companies.index'))->assertRedirect(route('login'));
});

test('authenticated users can view companies index', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Company::factory()->count(3)->create();

    $this->get(route('companies.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Companies/Index')
            ->has('companies.data', 3)
        );
});

test('authenticated users can create a company', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $companyData = [
        'ruc' => '20123456789',
        'business_name' => 'Test Company S.A.C.',
        'trade_name' => 'Test Company',
        'address' => 'Av. Test 123',
        'ubigeo' => '150101',
    ];

    $this->post(route('companies.store'), $companyData)
        ->assertRedirect(route('companies.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('companies', [
        'ruc' => '20123456789',
        'business_name' => 'Test Company S.A.C.',
    ]);
});

test('company creation requires valid RUC', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->post(route('companies.store'), [
        'ruc' => '123', // Invalid RUC
        'business_name' => 'Test Company',
    ])->assertSessionHasErrors('ruc');
});

test('authenticated users can view a company', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $this->actingAs($user);

    $this->get(route('companies.show', $company))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Companies/Show')
            ->has('company')
        );
});

test('authenticated users can update a company', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $this->actingAs($user);

    $this->put(route('companies.update', $company), [
        'ruc' => $company->ruc,
        'business_name' => 'Updated Company Name',
        'trade_name' => 'Updated Trade Name',
    ])->assertRedirect(route('companies.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('companies', [
        'id' => $company->id,
        'business_name' => 'Updated Company Name',
    ]);
});

test('authenticated users can delete a company', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $this->actingAs($user);

    $this->delete(route('companies.destroy', $company))
        ->assertRedirect(route('companies.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('companies', ['id' => $company->id]);
});
