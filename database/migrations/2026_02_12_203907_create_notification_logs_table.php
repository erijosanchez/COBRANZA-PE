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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('debtor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('debt_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('channel', ['whatsapp', 'email', 'sms']);
            $table->string('recipient'); // número o email
            $table->string('template')->nullable();
            $table->text('message');
            $table->enum('status', ['sent', 'delivered', 'failed', 'pending'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
