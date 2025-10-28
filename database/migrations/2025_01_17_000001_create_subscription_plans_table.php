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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Monthly Plan, 6 Months Plan, etc.
            $table->text('description');
            $table->decimal('price', 15, 2); // Harga dalam IDR
            $table->enum('billing_cycle', ['monthly', '6_months', 'annually', '2_years', '3_years']);
            $table->integer('cycle_months'); // 1, 6, 12, 24, 36
            $table->decimal('discount_percentage', 5, 2)->default(0); // Persentase diskon
            $table->json('features')->nullable(); // JSON array of features included
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_popular')->default(false); // Untuk highlight plan populer
            $table->timestamps();
            
            $table->index(['is_active', 'sort_order']);
            $table->index(['billing_cycle', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};