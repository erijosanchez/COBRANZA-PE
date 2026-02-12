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
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('debtor_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique(); // código único de deuda
            $table->string('concept'); // concepto de la deuda
            $table->text('description')->nullable();
            $table->decimal('original_amount', 12, 2); // monto original
            $table->decimal('total_amount', 12, 2); // monto total con intereses
            $table->decimal('paid_amount', 12, 2)->default(0); // monto pagado
            $table->decimal('pending_amount', 12, 2); // monto pendiente
            $table->string('currency', 3)->default('PEN'); // PEN, USD
            $table->integer('installments_count')->default(1); // número de cuotas
            $table->date('issue_date'); // fecha de emisión
            $table->date('due_date'); // fecha de vencimiento general
            $table->enum('interest_type', ['none', 'fixed', 'daily', 'monthly'])->default('none');
            $table->decimal('interest_rate', 8, 4)->default(0); // tasa de interés
            $table->enum('status', ['active', 'paid', 'partial', 'overdue', 'cancelled', 'refinanced'])->default('active');
            $table->integer('days_overdue')->default(0);
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['debtor_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
