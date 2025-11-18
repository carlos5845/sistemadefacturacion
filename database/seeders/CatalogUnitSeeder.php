<?php

namespace Database\Seeders;

use App\Models\CatalogUnit;
use Illuminate\Database\Seeder;

class CatalogUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['code' => 'NIU', 'name' => 'Unidad', 'description' => 'Unidad de medida estándar'],
            ['code' => 'ZZ', 'name' => 'Servicio', 'description' => 'Unidad para servicios'],
            ['code' => 'KG', 'name' => 'Kilogramo', 'description' => 'Unidad de masa'],
            ['code' => 'MTR', 'name' => 'Metro', 'description' => 'Unidad de longitud'],
            ['code' => 'LTR', 'name' => 'Litro', 'description' => 'Unidad de volumen'],
            ['code' => 'GRM', 'name' => 'Gramo', 'description' => 'Unidad de masa'],
            ['code' => 'SET', 'name' => 'Juego', 'description' => 'Conjunto de artículos'],
            ['code' => 'PK', 'name' => 'Paquete', 'description' => 'Paquete de artículos'],
        ];

        foreach ($units as $unit) {
            CatalogUnit::updateOrCreate(
                ['code' => $unit['code']],
                [
                    'name' => $unit['name'],
                    'description' => $unit['description'],
                ]
            );
        }
    }
}
