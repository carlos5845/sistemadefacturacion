<?php

use App\Models\Company;
use App\Models\Customer;
use App\Models\User;

test('users can view customers from their own company', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $customer = Customer::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user);

    expect($user->can('view', $customer))->toBeTrue();
});

test('users cannot view customers from other companies', function () {
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company1->id]);
    $customer = Customer::factory()->create(['company_id' => $company2->id]);

    $this->actingAs($user);

    expect($user->can('view', $customer))->toBeFalse();
});

test('users can create customers', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    expect($user->can('create', Customer::class))->toBeTrue();
});

test('users can update customers from their own company', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $customer = Customer::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user);

    expect($user->can('update', $customer))->toBeTrue();
});

test('users cannot update customers from other companies', function () {
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company1->id]);
    $customer = Customer::factory()->create(['company_id' => $company2->id]);

    $this->actingAs($user);

    expect($user->can('update', $customer))->toBeFalse();
});

test('only admins can delete customers', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id]);
    $regularUser = User::factory()->create(['company_id' => $company->id]);
    $customer = Customer::factory()->create(['company_id' => $company->id]);

    $admin->assignRole('admin');
    $this->actingAs($admin);
    expect($admin->can('delete', $customer))->toBeTrue();

    $this->actingAs($regularUser);
    expect($regularUser->can('delete', $customer))->toBeFalse();
});
