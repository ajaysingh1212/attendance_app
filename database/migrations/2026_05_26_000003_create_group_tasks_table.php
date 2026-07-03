<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_id')->constrained('users')->cascadeOnDelete();

            // Core fields
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['pending', 'accepted', 'completed'])->default('pending');
            $table->timestamp('due_at')->nullable();

            // Accept fields
            $table->foreignId('accepted_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('accepted_at')->nullable();
            $table->string('accept_role')->nullable();
            $table->enum('estimate_type', ['hours', 'date'])->nullable();
            $table->unsignedInteger('estimated_hours')->nullable();
            $table->date('estimated_date')->nullable();
            $table->text('accept_narration')->nullable();
            $table->unsignedInteger('requested_minutes')->nullable();

            // Completion fields
            $table->foreignId('completed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->text('completion_narration')->nullable();
            $table->unsignedInteger('actual_minutes')->nullable();
            $table->integer('delay_minutes')->nullable(); // negative = early

            $table->timestamps();
            $table->softDeletes();
        });

        // Pivot: task assignees
        Schema::create('group_task_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('assigned'); // assigned, accepted, completed
            $table->timestamps();

            $table->unique(['group_task_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_task_user');
        Schema::dropIfExists('group_tasks');
    }
};
