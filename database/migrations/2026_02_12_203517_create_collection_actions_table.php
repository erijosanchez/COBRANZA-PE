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
        Schema::create('collection_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('debt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('debtor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // gestor
            $table->enum('type', [
                'phone_call',
                'whatsapp',
                'email',
                'visit',
                'letter',
                'legal_notice',
                'promise_to_pay',
                'other'
            ]);
            $table->enum('result', [
                'contacted',
                'no_answer',
                'promise_to_pay',
                'refused',
                'wrong_number',
                'scheduled',
                'other'
            ]);
            $table->date('action_date');
            $table->time('action_time')->nullable();
            $table->date('promise_date')->nullable(); // si prometió pagar
            $table->decimal('promise_amount', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['debt_id', 'action_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_actions');
    }
};
