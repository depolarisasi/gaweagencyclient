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
        Schema::table('orders', function (Blueprint $table) {
            // Add subscription plan reference
            $table->foreignId('subscription_plan_id')->nullable()->after('product_id')->constrained('subscription_plans')->onDelete('cascade');
            
            // Add domain information
            $table->string('domain_name')->nullable()->after('order_details');
            $table->enum('domain_type', ['existing', 'register_new'])->nullable()->after('domain_name');
            $table->json('domain_details')->nullable()->after('domain_type'); // For domain registration info
            
            // Add template reference
            $table->foreignId('template_id')->nullable()->after('subscription_plan_id')->constrained('templates')->onDelete('set null');
            
            // Make product_id nullable since we're moving to subscription model
            $table->foreignId('product_id')->nullable()->change();
            
            // Add subscription specific fields
            $table->enum('order_type', ['subscription', 'addon'])->default('subscription')->after('order_number');
            $table->decimal('subscription_amount', 15, 2)->nullable()->after('amount');
            $table->decimal('addons_amount', 15, 2)->default(0)->after('subscription_amount');
            
            $table->index(['order_type', 'status']);
            $table->index(['subscription_plan_id', 'status']);
            $table->index(['template_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['subscription_plan_id']);
            $table->dropForeign(['template_id']);
            $table->dropColumn([
                'subscription_plan_id',
                'domain_name',
                'domain_type', 
                'domain_details',
                'template_id',
                'order_type',
                'subscription_amount',
                'addons_amount'
            ]);
            
            // Restore product_id as not nullable if needed
            // $table->foreignId('product_id')->nullable(false)->change();
        });
    }
};