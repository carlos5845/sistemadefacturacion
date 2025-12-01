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
        Schema::table('documents', function (Blueprint $table) {
            $table->string('sunat_code')->nullable()->after('status');
            $table->text('sunat_message')->nullable()->after('sunat_code');
            $table->longText('cdr_zip')->nullable()->after('sunat_message');
            $table->longText('cdr_xml')->nullable()->after('cdr_zip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['sunat_code', 'sunat_message', 'cdr_zip', 'cdr_xml']);
        });
    }
};
