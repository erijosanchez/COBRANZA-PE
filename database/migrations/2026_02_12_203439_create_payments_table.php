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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('debt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('installment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('debtor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->constrained()->restrictOnDelete();
            $table->foreignId('registered_by')->constrained('users')->restrictOnDelete();
            $table->string('receipt_number', 30)->nullable();
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->string('reference')->nullable(); // nro operación, voucher
            $table->text('notes')->nullable();
            $table->enum('status', ['confirmed', 'pending', 'rejected', 'reversed'])->default('confirmed');
            $table->timestamps();

            $table->index(['debt_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
