<?php

use App\Models\Company;
use App\Models\Document;
use App\Models\User;

test('users can send pending documents to SUNAT', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $document = Document::factory()->pending()->create(['company_id' => $company->id]);

    $this->actingAs($user);

    expect($user->can('sendToSunat', $document))->toBeTrue();
});

test('users cannot send non-pending documents to SUNAT', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $document = Document::factory()->accepted()->create(['company_id' => $company->id]);

    $this->actingAs($user);

    expect($user->can('sendToSunat', $document))->toBeFalse();
});

test('users can update pending documents', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $document = Document::factory()->pending()->create(['company_id' => $company->id]);

    $this->actingAs($user);

    expect($user->can('update', $document))->toBeTrue();
});

test('users cannot update non-pending documents', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $document = Document::factory()->accepted()->create(['company_id' => $company->id]);

    $this->actingAs($user);

    expect($user->can('update', $document))->toBeFalse();
});

test('only admins can cancel documents', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id]);
    $regularUser = User::factory()->create(['company_id' => $company->id]);
    $document = Document::factory()->accepted()->create(['company_id' => $company->id]);

    $admin->assignRole('admin');
    $this->actingAs($admin);
    expect($admin->can('cancel', $document))->toBeTrue();

    $this->actingAs($regularUser);
    expect($regularUser->can('cancel', $document))->toBeFalse();
});

test('users cannot cancel rejected documents', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id]);
    $document = Document::factory()->rejected()->create(['company_id' => $company->id]);

    $admin->assignRole('admin');
    $this->actingAs($admin);

    expect($admin->can('cancel', $document))->toBeFalse();
});
