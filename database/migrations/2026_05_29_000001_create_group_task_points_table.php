<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_task_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('points', 4, 2)->default(0);
            $table->string('reason')->nullable();
            $table->boolean('was_assigned')->default(false);
            $table->boolean('completed_within_deadline')->default(true);
            $table->timestamps();

            $table->unique(['group_task_id', 'user_id']);
            $table->index(['task_group_id', 'user_id']);
        });

        Schema::table('group_tasks', function (Blueprint $table) {
            $table->decimal('completion_points', 4, 2)->nullable()->after('delay_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('group_tasks', function (Blueprint $table) {
            $table->dropColumn('completion_points');
        });

        Schema::dropIfExists('group_task_points');
    }
};
