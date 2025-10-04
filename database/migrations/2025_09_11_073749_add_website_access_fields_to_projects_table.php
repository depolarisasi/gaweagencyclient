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
        Schema::table('projects', function (Blueprint $table) {
            // Website access information
            $table->string('website_url')->nullable()->after('notes');
            $table->string('admin_url')->nullable()->after('website_url');
            $table->string('admin_username')->nullable()->after('admin_url');
            $table->string('admin_password')->nullable()->after('admin_username');
            $table->json('additional_access')->nullable()->after('admin_password'); // For any additional access info
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'website_url',
                'admin_url',
                'admin_username',
                'admin_password',
                'additional_access'
            ]);
        });
    }
};