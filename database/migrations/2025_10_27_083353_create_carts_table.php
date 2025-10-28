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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable(); // For anonymous users
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // For authenticated users
            $table->foreignId('template_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('subscription_plan_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('billing_cycle')->nullable(); // monthly, yearly
            $table->json('configuration')->nullable(); // Store template configuration
            $table->json('domain_data')->nullable(); // Store domain information
            $table->decimal('template_amount', 10, 2)->default(0);
            $table->decimal('addons_amount', 10, 2)->default(0);
            $table->decimal('domain_amount', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('customer_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->timestamp('expires_at')->nullable(); // Cart expiration
            $table->timestamps();
            
            // Indexes
            $table->index('session_id');
            $table->index('user_id');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
