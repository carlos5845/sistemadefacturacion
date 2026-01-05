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

        // SQLite no soporta ALTER COLUMN directamente
        // Necesitamos recrear la tabla
        
        // Asegurar que no exista la tabla temporal de un intento fallido previo
        Schema::dropIfExists('documents_temp');

        // Crear tabla temporal con la nueva estructura
        Schema::create('documents_temp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->char('document_type', 2);
            $table->string('series', 4);
            $table->unsignedInteger('number');
            $table->date('issue_date');
            $table->char('currency', 3)->default('PEN');
            $table->decimal('total_taxed', 12, 2)->default(0);
            $table->decimal('total_igv', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->longText('xml')->nullable();
            $table->longText('xml_signed')->nullable();
            $table->string('hash')->nullable();
            $table->string('status')->default('PENDING');  // Cambio aquí: de enum a string
            $table->text('error_message')->nullable();
            $table->string('sunat_code')->nullable();
            $table->text('sunat_message')->nullable();
            $table->longText('cdr_zip')->nullable();
            $table->longText('cdr_xml')->nullable();
            $table->timestamps();

            $table->foreign('document_type')->references('code')->on('catalog_document_types');
            $table->unique(['company_id', 'document_type', 'series', 'number']);
        });

        // Copiar datos
        // Copiar datos explícitamente para evitar errores de columnas (documents tiene 21 columnas, temp tiene 22 por error_message)
        // Orden físico en SQLite: id...status, created_at, updated_at, sunat_code...
        // Pero mejor listamos explícitamente para seguridad
        DB::statement('INSERT INTO documents_temp (
            id, company_id, customer_id, document_type, series, number, issue_date, 
            currency, total_taxed, total_igv, total, xml, xml_signed, hash, status, 
            sunat_code, sunat_message, cdr_zip, cdr_xml, created_at, updated_at
        ) SELECT 
            id, company_id, customer_id, document_type, series, number, issue_date, 
            currency, total_taxed, total_igv, total, xml, xml_signed, hash, status, 
            sunat_code, sunat_message, cdr_zip, cdr_xml, created_at, updated_at 
        FROM documents');

        // Eliminar tabla original
        Schema::dropIfExists('documents');

        // Renombrar tabla temporal
        Schema::rename('documents_temp', 'documents');

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        // No es fácil revertir, pero podemos intentar
        Schema::create('documents_temp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->char('document_type', 2);
            $table->string('series', 4);
            $table->unsignedInteger('number');
            $table->date('issue_date');
            $table->char('currency', 3)->default('PEN');
            $table->decimal('total_taxed', 12, 2)->default(0);
            $table->decimal('total_igv', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->longText('xml')->nullable();
            $table->longText('xml_signed')->nullable();
            $table->string('hash')->nullable();
            $table->enum('status', ['PENDING', 'SENT', 'ACCEPTED', 'REJECTED', 'CANCELED'])->default('PENDING');
            $table->text('error_message')->nullable();
            $table->string('sunat_code')->nullable();
            $table->text('sunat_message')->nullable();
            $table->longText('cdr_zip')->nullable();
            $table->longText('cdr_xml')->nullable();
            $table->timestamps();

            $table->foreign('document_type')->references('code')->on('catalog_document_types');
            $table->unique(['company_id', 'document_type', 'series', 'number']);
        });

        // Copiar datos explícitamente
        DB::statement('INSERT INTO documents_temp (
            id, company_id, customer_id, document_type, series, number, issue_date, 
            currency, total_taxed, total_igv, total, xml, xml_signed, hash, status, 
            sunat_code, sunat_message, cdr_zip, cdr_xml, created_at, updated_at
        ) SELECT 
            id, company_id, customer_id, document_type, series, number, issue_date, 
            currency, total_taxed, total_igv, total, xml, xml_signed, hash, status, 
            sunat_code, sunat_message, cdr_zip, cdr_xml, created_at, updated_at 
        FROM documents');
        Schema::dropIfExists('documents');
        Schema::rename('documents_temp', 'documents');

        Schema::enableForeignKeyConstraints();
    }
};
