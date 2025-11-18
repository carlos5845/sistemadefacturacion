<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
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
            $table->timestamps();

            $table->foreign('document_type')->references('code')->on('catalog_document_types');
            $table->unique(['company_id', 'document_type', 'series', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
