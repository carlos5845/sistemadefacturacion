<?php

use App\Models\Company;
use App\Models\Document;
use App\Models\User;

test('it returns default series and number 1 for new document type', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $this->actingAs($user);

    $response = $this->getJson(route('documents.next-number', ['document_type' => '01']));

    $response->assertOk()
        ->assertJson([
            'series' => 'F001',
            'number' => 1,
        ]);
});

test('it increments number for existing series', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $customer = \App\Models\Customer::factory()->create(['company_id' => $company->id]);
    $this->actingAs($user);

    // Create a document with series F001 and number 10
    Document::factory()->create([
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'document_type' => '01',
        'series' => 'F001',
        'number' => 10,
    ]);

    $response = $this->getJson(route('documents.next-number', ['document_type' => '01']));

    $response->assertOk()
        ->assertJson([
            'series' => 'F001',
            'number' => 11,
        ]);
});

test('it respects requested series and returns next number', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $customer = \App\Models\Customer::factory()->create(['company_id' => $company->id]);
    $this->actingAs($user);

    // Create a document with series F002 and number 5
    Document::factory()->create([
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'document_type' => '01',
        'series' => 'F002',
        'number' => 5,
    ]);

    $response = $this->getJson(route('documents.next-number', [
        'document_type' => '01',
        'series' => 'F002'
    ]));

    $response->assertOk()
        ->assertJson([
            'series' => 'F002',
            'number' => 6,
        ]);
});

test('it defaults to B001 for boletas', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $this->actingAs($user);

    $response = $this->getJson(route('documents.next-number', ['document_type' => '03']));

    $response->assertOk()
        ->assertJson([
            'series' => 'B001',
            'number' => 1,
        ]);
});
