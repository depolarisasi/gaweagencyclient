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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->text('description')->nullable();
            $table->json('line_items')->nullable(); // JSON for invoice items
            
            // Tripay integration fields
            $table->string('tripay_reference')->nullable()->unique();
            $table->string('tripay_merchant_ref')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_code')->nullable();
            $table->json('tripay_response')->nullable();
            $table->timestamp('payment_expired_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['status', 'due_date']);
            $table->index('tripay_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
