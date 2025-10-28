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
        Schema::create('order_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('product_addon_id')->constrained('product_addons')->onDelete('cascade');
            $table->decimal('price', 15, 2); // Price at time of order
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'semi_annually', 'annually'])->nullable();
            $table->integer('quantity')->default(1);
            $table->json('addon_details')->nullable(); // Additional configuration
            $table->timestamps();
            
            $table->unique(['order_id', 'product_addon_id']);
            $table->index(['order_id']);
            $table->index(['product_addon_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_addons');
    }
};