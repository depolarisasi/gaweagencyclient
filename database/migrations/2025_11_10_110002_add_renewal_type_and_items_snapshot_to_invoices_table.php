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
        Schema::table('invoices', function (Blueprint $table) {
            // Jenis renewal: subscription atau addons (null untuk invoice awal)
            if (Schema::hasColumn('invoices', 'is_renewal')) {
                $table->string('renewal_type', 20)->nullable()->after('is_renewal');
            } else {
                $table->string('renewal_type', 20)->nullable();
            }

            // Snapshot item untuk audit cepat (selain tabel invoice_items)
            if (Schema::hasColumn('invoices', 'tripay_data')) {
                $table->json('items_snapshot')->nullable()->after('tripay_data');
            } else {
                $table->json('items_snapshot')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['renewal_type', 'items_snapshot']);
        });
    }
};