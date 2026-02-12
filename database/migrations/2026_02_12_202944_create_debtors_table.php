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
        Schema::create('debtors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->enum('document_type', ['DNI', 'RUC', 'CE', 'PASAPORTE'])->default('DNI');
            $table->string('document_number', 20);
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('phone_secondary', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('district')->nullable();
            $table->string('province')->nullable();
            $table->string('department')->nullable();
            $table->text('reference')->nullable(); // referencia de ubicación
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'document_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debtors');
    }
};
