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
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('demo_url')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->string('category')->default('business'); // business, ecommerce, portfolio, blog, landing
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('features')->nullable(); // JSON array of template features
            $table->json('preview_images')->nullable(); // JSON array of preview image URLs
            $table->timestamps();
            
            $table->index(['category', 'is_active']);
            $table->index(['is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};