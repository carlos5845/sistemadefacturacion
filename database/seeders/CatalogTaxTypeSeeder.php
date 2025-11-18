<?php

namespace Database\Seeders;

use App\Models\CatalogTaxType;
use Illuminate\Database\Seeder;

class CatalogTaxTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxTypes = [
            ['code' => '10', 'name' => 'Gravado 18%', 'affects_igv' => true],
            ['code' => '20', 'name' => 'Exonerado', 'affects_igv' => false],
            ['code' => '30', 'name' => 'Inafecto', 'affects_igv' => false],
            ['code' => '40', 'name' => 'Exportación', 'affects_igv' => false],
        ];

        foreach ($taxTypes as $type) {
            CatalogTaxType::updateOrCreate(
                ['code' => $type['code']],
                [
                    'name' => $type['name'],
                    'affects_igv' => $type['affects_igv'],
                ]
            );
        }
    }
}
