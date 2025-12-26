<?php

use App\Models\Company;
use App\Models\Document;
use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

beforeEach(function () {
    // Crear usuario con empresa para las pruebas
    $this->company = Company::factory()->create([
        'ruc' => '20123456786',
        'business_name' => 'Test Company',
    ]);

    $this->user = User::factory()->create([
        'company_id' => $this->company->id,
    ]);

    actingAs($this->user);
});

describe('DocumentApiController - getNextNumber', function () {
    it('devuelve el próximo número para una serie nueva', function () {
        $response = getJson('/api/documents/next-number/F001');

        $response->assertStatus(200)
            ->assertJson([
                'series' => 'F001',
                'document_type' => '01',
                'last_number' => 0,
                'next_number' => 1,
                'suggested_full_number' => 'F001-00000001',
            ]);
    });

    it('devuelve el próximo número incrementado cuando existen documentos', function () {
        // Crear algunos documentos previos
        Document::factory()->create([
            'company_id' => $this->company->id,
            'document_type' => '01',
            'series' => 'F001',
            'number' => 5,
        ]);

        Document::factory()->create([
            'company_id' => $this->company->id,
            'document_type' => '01',
            'series' => 'F001',
            'number' => 10,
        ]);

        $response = getJson('/api/documents/next-number/F001');

        $response->assertStatus(200)
            ->assertJson([
                'series' => 'F001',
                'document_type' => '01',
                'last_number' => 10,
                'next_number' => 11,
            ]);
    });

    it('distingue entre diferentes series', function () {
        // Crear documentos en F001
        Document::factory()->create([
            'company_id' => $this->company->id,
            'document_type' => '01',
            'series' => 'F001',
            'number' => 5,
        ]);

        // Crear documentos en F002
        Document::factory()->create([
            'company_id' => $this->company->id,
            'document_type' => '01',
            'series' => 'F002',
            'number' => 15,
        ]);

        // Verificar F001
        $response1 = getJson('/api/documents/next-number/F001');
        $response1->assertJson([
            'series' => 'F001',
            'next_number' => 6,
        ]);

        // Verificar F002
        $response2 = getJson('/api/documents/next-number/F002');
        $response2->assertJson([
            'series' => 'F002',
            'next_number' => 16,
        ]);
    });

    it('funciona con series de boletas (B001)', function () {
        Document::factory()->create([
            'company_id' => $this->company->id,
            'document_type' => '03',
            'series' => 'B001',
            'number' => 20,
        ]);

        $response = getJson('/api/documents/next-number/B001');

        $response->assertStatus(200)
            ->assertJson([
                'series' => 'B001',
                'document_type' => '03',
                'last_number' => 20,
                'next_number' => 21,
            ]);
    });

    it('devuelve error para series inválidas', function () {
        $response = getJson('/api/documents/next-number/INVALID');

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Serie inválida. Use formato F001-F999 para facturas o B001-B999 para boletas',
            ]);
    });

    it('convierte serie a mayúsculas', function () {
        $response = getJson('/api/documents/next-number/f001');

        $response->assertStatus(200)
            ->assertJson([
                'series' => 'F001',
                'document_type' => '01',
            ]);
    });

    it('solo cuenta documentos de la empresa del usuario', function () {
        // Crear otra empresa
        $otherCompany = Company::factory()->create();

        // Crear documentos en la otra empresa
        Document::factory()->create([
            'company_id' => $otherCompany->id,
            'document_type' => '01',
            'series' => 'F001',
            'number' => 100,
        ]);

        // Crear documento en la empresa del usuario
        Document::factory()->create([
            'company_id' => $this->company->id,
            'document_type' => '01',
            'series' => 'F001',
            'number' => 5,
        ]);

        $response = getJson('/api/documents/next-number/F001');

        // Debe devolver 6 (siguiente del 5), no 101 (siguiente del 100)
        $response->assertStatus(200)
            ->assertJson([
                'next_number' => 6,
            ]);
    });
});

describe('DocumentApiController - getNextNumbers', function () {
    it('devuelve próximos números para múltiples series', function () {
        // Crear documentos en diferentes series
        Document::factory()->create([
            'company_id' => $this->company->id,
            'document_type' => '01',
            'series' => 'F001',
            'number' => 10,
        ]);

        Document::factory()->create([
            'company_id' => $this->company->id,
            'document_type' => '03',
            'series' => 'B001',
            'number' => 25,
        ]);

        $response = getJson('/api/documents/next-numbers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'F001' => ['last_number', 'next_number'],
                'B001' => ['last_number', 'next_number'],
                'FC01',
                'BC01',
                'FD01',
                'BD01',
            ])
            ->assertJson([
                'F001' => [
                    'last_number' => 10,
                    'next_number' => 11,
                ],
                'B001' => [
                    'last_number' => 25,
                    'next_number' => 26,
                ],
            ]);
    });

    it('devuelve 1 para series sin documentos', function () {
        $response = getJson('/api/documents/next-numbers');

        $response->assertStatus(200)
            ->assertJson([
                'F001' => [
                    'last_number' => 0,
                    'next_number' => 1,
                ],
                'B001' => [
                    'last_number' => 0,
                    'next_number' => 1,
                ],
            ]);
    });
});

it('requiere autenticación', function () {
    // Cerrar sesión
    auth()->logout();

    $response = getJson('/api/documents/next-number/F001');

    $response->assertStatus(302); // Redirect a login
});

it('devuelve error si el usuario no tiene empresa', function () {
    // Crear usuario sin empresa
    $userWithoutCompany = User::factory()->create([
        'company_id' => null,
    ]);

    actingAs($userWithoutCompany);

    $response = getJson('/api/documents/next-number/F001');

    $response->assertStatus(400)
        ->assertJson([
            'error' => 'Usuario no asociado a ninguna empresa',
        ]);
});
