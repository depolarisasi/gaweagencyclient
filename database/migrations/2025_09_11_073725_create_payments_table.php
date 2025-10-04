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
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('reference')->unique();
            $table->string('payment_method');
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'completed', 'failed', 'expired', 'cancelled'])->default('pending');
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('callback_data')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'status']);
            $table->index(['invoice_id', 'status']);
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