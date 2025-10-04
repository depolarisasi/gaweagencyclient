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
        Schema::create('product_addons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->enum('billing_type', ['one_time', 'recurring'])->default('one_time');
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'semi_annually', 'annually'])->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('category')->default('general'); // hosting, domain, ssl, maintenance, marketing
            $table->timestamps();
            
            $table->index(['is_active', 'sort_order']);
            $table->index(['category', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_addons');
    }
};