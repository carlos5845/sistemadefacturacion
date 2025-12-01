<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::create([
            'ruc' => '20123456789',
            'business_name' => 'EMPRESA DE PRUEBAS S.A.C.',
            'trade_name' => 'EMPRESA DEMO',
            'user_sol' => 'MODDATOS',
            'password_sol' => 'moddatos',
            'certificate' => 'certificates/certificado_pruebas_sunat.pfx',
            'certificate_password' => '123456',
            'address' => 'AV. DE PRUEBA 123',
            'ubigeo' => '150101',
        ]);
    }
}
