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
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('channel', ['whatsapp', 'email', 'sms']);
            $table->enum('type', [
                'payment_reminder',
                'overdue_notice',
                'payment_confirmation',
                'promise_reminder',
                'legal_warning',
                'custom'
            ]);
            $table->string('subject')->nullable(); // para email
            $table->text('body'); // con variables {nombre}, {monto}, {fecha}
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_templates');
    }
};
