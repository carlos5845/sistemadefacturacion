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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->char('ruc', 11)->unique();
            $table->string('business_name');
            $table->string('trade_name')->nullable();
            $table->text('certificate')->nullable();
            $table->string('certificate_password')->nullable();
            $table->string('user_sol', 50)->nullable();
            $table->string('password_sol', 50)->nullable();
            $table->string('address')->nullable();
            $table->char('ubigeo', 6)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
