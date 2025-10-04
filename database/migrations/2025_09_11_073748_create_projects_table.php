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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_name');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // client
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null'); // staff
            $table->enum('status', ['pending', 'in_progress', 'review', 'completed', 'on_hold', 'cancelled'])->default('pending');
            $table->text('description')->nullable();
            $table->json('requirements')->nullable(); // JSON for project requirements
            $table->json('deliverables')->nullable(); // JSON for project deliverables
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->integer('progress_percentage')->default(0);
            $table->text('notes')->nullable();
            $table->json('files')->nullable(); // JSON for uploaded files
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index(['status', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
