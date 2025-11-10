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
            // Status lifecycle untuk add-on: default aktif untuk data historis
            $table->string('status', 20)->default('active')->after('addon_details');
            // Tanggal mulai periode add-on (saat order aktif/terbentuk)
            $table->dateTime('started_at')->nullable()->after('status');
            // Jatuh tempo periode berikutnya per add-on
            $table->date('next_due_date')->nullable()->after('started_at');
            // Permintaan cancel oleh client yang efektif di akhir periode berjalan
            $table->boolean('cancel_at_period_end')->default(false)->after('next_due_date');
            // Tanggal eksekusi cancel (auto-cancel H+14 atau saat period-end)
            $table->dateTime('cancelled_at')->nullable()->after('cancel_at_period_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_addons', function (Blueprint $table) {
            $table->dropColumn(['status', 'started_at', 'next_due_date', 'cancel_at_period_end', 'cancelled_at']);
        });
    }
};