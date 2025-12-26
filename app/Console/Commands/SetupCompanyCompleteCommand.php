<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Document;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SetupCompanyCompleteCommand extends Command
{
    protected $signature = 'company:setup-complete {--force : Forzar ejecución sin confirmación}';
    protected $description = 'Limpia BD y configura nueva empresa con credenciales actualizadas';

    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('¿ESTÁS SEGURO? Esto borrará TODAS las empresas y documentos.', true)) {
            return;
        }

        $this->info('🗑️ Limpiando base de datos...');
        
        // Desactivar FK checks para truncate
        Schema::disableForeignKeyConstraints();
        DB::table('document_items')->truncate();
        DB::table('documents')->truncate();
        DB::table('companies')->truncate();
        DB::table('customers')->truncate();
        Schema::enableForeignKeyConstraints();

        $this->info('🏢 Creando nueva empresa...');

        // Credenciales proporcinadas por el usuario
        $ruc = '10747773942';
        $usuario = '74777394';
        $clave = '135135Bb';
        
        $company = Company::create([
            'ruc' => $ruc,
            'business_name' => 'EMPRESA DE PRUEBA',
            'address' => 'AV. PRINCIPAL 123',
            'user_sol' => $ruc . $usuario,
            'password_sol' => encrypt($clave),
            'certificate' => 'LLAMA-PE-CERTIFICADO-DEMO-20100066603.pfx', // Reusamos el demo
            'certificate_password' => '12345678',
        ]);

        $this->info('👥 Creando clientes de prueba...');
        
        Customer::create([
            'company_id' => $company->id,
            'identity_type' => 'DNI', // Antes '1'
            'identity_number' => '12345678',
            'name' => 'CLIENTE BOLETA (DNI)',
            'address' => 'Direccion Cliente DNI'
        ]);

        Customer::create([
            'company_id' => $company->id,
            'identity_type' => 'RUC', // Antes '6'
            'identity_number' => '20123456789',
            'name' => 'CLIENTE FACTURA (RUC)',
            'address' => 'Direccion Cliente RUC'
        ]);

        $this->info("✅ Configuración completada exitosamente.");
        $this->line("Empresa: {$company->business_name}");
        $this->line("RUC: {$company->ruc}");
        $this->line("Usuario SOL: {$company->user_sol}");
        
        return 0;
    }
}
