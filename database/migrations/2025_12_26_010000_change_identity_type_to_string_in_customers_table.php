<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // SQLite: Recrear tabla para cambiar tipo de columna y convertir datos
        Schema::dropIfExists('customers_temp');

        Schema::create('customers_temp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->char('identity_type', 2); // Cambiado a char(2) para códigos SUNAT (1, 6, etc)
            $table->string('identity_number', 15);
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'identity_type', 'identity_number']);
        });

        // Copiar y convertir datos
        // CASE: 'RUC' -> '6', 'DNI' -> '1', 'CE' -> '4', 'PAS' -> '7'
        // Si ya es número, lo deja igual.
        DB::statement("INSERT INTO customers_temp (
            id, company_id, identity_type, identity_number, name, address, email, phone, created_at, updated_at
        ) SELECT 
            id, company_id, 
            CASE identity_type 
                WHEN 'RUC' THEN '6'
                WHEN 'DNI' THEN '1'
                WHEN 'CE'  THEN '4'
                WHEN 'PAS' THEN '7'
                ELSE identity_type
            END,
            identity_number, name, address, email, phone, created_at, updated_at 
        FROM customers");

        Schema::dropIfExists('customers');
        Schema::rename('customers_temp', 'customers');

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir a ENUM es complicado sin perder datos si hay códigos nuevos desconocidos
        // Se deja como string
    }
};
