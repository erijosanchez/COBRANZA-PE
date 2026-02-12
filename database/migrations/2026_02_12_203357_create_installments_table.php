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
        Schema::create('installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('debt_id')->constrained()->cascadeOnDelete();
            $table->integer('number'); // número de cuota
            $table->decimal('amount', 12, 2); // monto de la cuota
            $table->decimal('interest_amount', 12, 2)->default(0);
            $table->decimal('penalty_amount', 12, 2)->default(0); // mora
            $table->decimal('total_amount', 12, 2); // amount + interest + penalty
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->enum('status', ['pending', 'paid', 'partial', 'overdue', 'cancelled'])->default('pending');
            $table->timestamps();

            $table->index(['debt_id', 'status']);
            $table->unique(['debt_id', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installments');
    }
};
