<?php

use App\Models\CatalogDocumentType;
use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentItem;
use App\Models\User;

test('authenticated users can view documents index', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Document::factory()->count(3)->create(['company_id' => $user->company_id]);

    $this->get(route('documents.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Documents/Index')
            ->has('documents.data', 3)
        );
});

test('authenticated users can create a document', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['company_id' => $user->company_id]);
    $documentType = CatalogDocumentType::first() ?? CatalogDocumentType::factory()->create();

    $this->actingAs($user);

    $documentData = [
        'document_type' => $documentType->code,
        'customer_id' => $customer->id,
        'series' => 'F001',
        'number' => 1,
        'issue_date' => now()->format('Y-m-d'),
        'currency' => 'PEN',
        'total_taxed' => 100.00,
        'total_igv' => 18.00,
        'total' => 118.00,
        'items' => [
            [
                'description' => 'Test Item',
                'quantity' => 1,
                'unit_price' => 100.00,
                'total' => 118.00,
                'tax_type' => '10',
                'igv' => 18.00,
            ],
        ],
    ];

    $this->post(route('documents.store'), $documentData)
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('documents', [
        'series' => 'F001',
        'number' => 1,
        'company_id' => $user->company_id,
    ]);

    $document = Document::where('series', 'F001')->where('number', 1)->first();
    $this->assertDatabaseHas('document_items', [
        'document_id' => $document->id,
        'description' => 'Test Item',
    ]);
});

test('document creation requires at least one item', function () {
    $user = User::factory()->create();
    $documentType = CatalogDocumentType::first() ?? CatalogDocumentType::factory()->create();
    $this->actingAs($user);

    $this->post(route('documents.store'), [
        'document_type' => $documentType->code,
        'series' => 'F001',
        'number' => 1,
        'issue_date' => now()->format('Y-m-d'),
        'currency' => 'PEN',
        'total' => 100,
        'items' => [],
    ])->assertSessionHasErrors('items');
});

test('authenticated users can view a document', function () {
    $user = User::factory()->create();
    $document = Document::factory()->create(['company_id' => $user->company_id]);
    $this->actingAs($user);

    $this->get(route('documents.show', $document))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Documents/Show')
            ->has('document')
        );
});

test('users cannot view documents from other companies', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $document = Document::factory()->create(['company_id' => $user1->company_id]);
    $this->actingAs($user2);

    $this->get(route('documents.show', $document))
        ->assertForbidden();
});

test('users can only edit pending documents', function () {
    $user = User::factory()->create();
    $pendingDocument = Document::factory()->pending()->create(['company_id' => $user->company_id]);
    $acceptedDocument = Document::factory()->accepted()->create(['company_id' => $user->company_id]);
    $this->actingAs($user);

    $this->get(route('documents.edit', $pendingDocument))
        ->assertOk();

    $this->get(route('documents.edit', $acceptedDocument))
        ->assertRedirect(route('documents.show', $acceptedDocument))
        ->assertSessionHas('error');
});

test('users can send pending documents to SUNAT', function () {
    $user = User::factory()->create();
    $document = Document::factory()->pending()->create(['company_id' => $user->company_id]);
    $this->actingAs($user);

    $this->post(route('documents.send-to-sunat', $document))
        ->assertRedirect(route('documents.show', $document))
        ->assertSessionHas('success');

    $document->refresh();
    expect($document->status)->toBe('SENT');
});

test('users cannot send non-pending documents to SUNAT', function () {
    $user = User::factory()->create();
    $document = Document::factory()->accepted()->create(['company_id' => $user->company_id]);
    $this->actingAs($user);

    $this->post(route('documents.send-to-sunat', $document))
        ->assertRedirect(route('documents.show', $document))
        ->assertSessionHas('error');
});
