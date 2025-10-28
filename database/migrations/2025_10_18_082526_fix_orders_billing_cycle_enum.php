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
        // Update billing_cycle enum to match subscription_plans format
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('billing_cycle', ['monthly', '6_months', 'annually', '2_years', '3_years'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'semi_annually', 'annually'])->change();
        });
    }
};
