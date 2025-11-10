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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('order_addon_id')->nullable()->constrained('order_addons')->onDelete('set null');
            $table->foreignId('product_addon_id')->nullable()->constrained('product_addons')->onDelete('set null');
            $table->string('item_type', 20); // subscription, addon, domain
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->integer('quantity')->default(1);
            $table->string('billing_type', 20)->nullable(); // one_time, recurring
            $table->string('billing_cycle', 50)->nullable();
            $table->timestamps();

            $table->index(['invoice_id']);
            $table->index(['order_id']);
            $table->index(['order_addon_id']);
            $table->index(['product_addon_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};