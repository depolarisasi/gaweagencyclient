<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Normalisasi data lama agar kompatibel dengan enum baru
        DB::statement("UPDATE subscription_plans SET billing_cycle = 'annually' WHERE billing_cycle = 'annual'");
        DB::statement("UPDATE subscription_plans SET billing_cycle = '6_months' WHERE billing_cycle = 'semi_annual'");

        // Alter enum untuk konsistensi dengan UI dan controller
        // Sertakan 'quarterly' untuk menjaga kompatibilitas data lama
        DB::statement("ALTER TABLE subscription_plans MODIFY COLUMN billing_cycle ENUM('monthly','quarterly','6_months','annually','2_years','3_years') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan enum ke set sebelumnya
        DB::statement("ALTER TABLE subscription_plans MODIFY COLUMN billing_cycle ENUM('monthly','quarterly','semi_annual','annual') NOT NULL");
    }
};