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
    Schema::create('experience_letters', function (Blueprint $table) {
        $table->id();

        $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

        // Employment Details Snapshot
        $table->date('date_of_joining')->nullable();
        $table->date('date_of_resignation')->nullable();
        $table->date('last_working_date')->nullable();

        $table->string('designation')->nullable();
        $table->string('department')->nullable();

        $table->decimal('last_drawn_salary', 12, 2)->nullable();

        // Notice Period
        $table->integer('notice_period_days')->nullable();
        $table->boolean('notice_served')->default(false);
        $table->integer('notice_served_days')->nullable();

        // Increment Details
        $table->boolean('had_increment')->default(false);
        $table->date('last_increment_date')->nullable();
        $table->decimal('increment_amount', 12, 2)->nullable();

        // Letter Content Customization
        $table->text('additional_remark')->nullable();

        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experience_letters');
    }
};
