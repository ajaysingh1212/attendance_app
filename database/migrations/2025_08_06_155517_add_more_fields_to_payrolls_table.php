<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->integer('paid_leaves')->nullable();
            $table->integer('holidays')->nullable();
            $table->integer('half_days')->nullable();
            $table->integer('leave_days')->nullable();
            $table->integer('final_paid_days')->nullable();
            $table->decimal('gross_salary', 10, 2)->nullable();
            $table->decimal('manual_adjustment', 10, 2)->nullable();
            $table->unsignedBigInteger('salary_generated_by')->nullable();
            $table->timestamp('generated_at')->nullable();

            $table->foreign('salary_generated_by')->references('id')->on('users')->onDelete('set null');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            //
        });
    }
};
