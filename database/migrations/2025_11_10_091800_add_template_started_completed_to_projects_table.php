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
            // Add template_id for linking project to selected template
            if (!Schema::hasColumn('projects', 'template_id')) {
                $table->foreignId('template_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('templates')
                    ->onDelete('set null');
            }

            // Add started_at to track precise datetime a project started
            if (!Schema::hasColumn('projects', 'started_at')) {
                $table->dateTime('started_at')
                    ->nullable()
                    ->after('description');
            }

            // Add completed_at to track precise datetime a project completed
            if (!Schema::hasColumn('projects', 'completed_at')) {
                $table->dateTime('completed_at')
                    ->nullable()
                    ->after('started_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Drop foreign key first before column
            if (Schema::hasColumn('projects', 'template_id')) {
                $table->dropForeign(['template_id']);
                $table->dropColumn('template_id');
            }

            if (Schema::hasColumn('projects', 'started_at')) {
                $table->dropColumn('started_at');
            }

            if (Schema::hasColumn('projects', 'completed_at')) {
                $table->dropColumn('completed_at');
            }
        });
    }
};