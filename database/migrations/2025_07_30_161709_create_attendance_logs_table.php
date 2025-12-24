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
            Schema::create('attendance_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('employee_id');
                $table->date('date');
                $table->time('expected_in')->nullable();   // from work_start_time
                $table->time('actual_in')->nullable();
                $table->integer('late_by_minutes')->nullable();

                $table->time('expected_out')->nullable();  // from work_end_time
                $table->time('actual_out')->nullable();
                $table->integer('left_early_by_minutes')->nullable();
                $table->integer('overtime_by_minutes')->nullable();

                $table->integer('total_work_minutes')->nullable(); // actual time between in and out
                $table->timestamps();
            });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
