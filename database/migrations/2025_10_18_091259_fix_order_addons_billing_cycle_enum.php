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
        Schema::table('order_addons', function (Blueprint $table) {
            // Drop the existing enum column and recreate with new values
            $table->dropColumn('billing_cycle');
        });
        
        Schema::table('order_addons', function (Blueprint $table) {
            // Add the column back with updated enum values to match subscription_plans
            $table->enum('billing_cycle', ['monthly', '6_months', 'annually', '2_years', '3_years'])->nullable()->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_addons', function (Blueprint $table) {
            // Drop the updated enum column
            $table->dropColumn('billing_cycle');
        });
        
        Schema::table('order_addons', function (Blueprint $table) {
            // Restore the original enum values
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'semi_annually', 'annually'])->nullable()->after('price');
        });
    }
};
