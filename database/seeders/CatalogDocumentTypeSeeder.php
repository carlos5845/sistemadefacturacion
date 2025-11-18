<?php

namespace Database\Seeders;

use App\Models\CatalogDocumentType;
use Illuminate\Database\Seeder;

class CatalogDocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documentTypes = [
            ['code' => '01', 'name' => 'Factura'],
            ['code' => '03', 'name' => 'Boleta'],
            ['code' => '07', 'name' => 'Nota de crédito'],
            ['code' => '08', 'name' => 'Nota de débito'],
        ];

        foreach ($documentTypes as $type) {
            CatalogDocumentType::updateOrCreate(
                ['code' => $type['code']],
                ['name' => $type['name']]
            );
        }
    }
}
