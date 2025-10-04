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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->enum('type', ['website', 'hosting', 'domain', 'maintenance']);
            $table->decimal('price', 10, 2);
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'semi_annually', 'annually']);
            $table->json('features')->nullable(); // JSON array of features
            $table->integer('setup_time_days')->default(7);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
