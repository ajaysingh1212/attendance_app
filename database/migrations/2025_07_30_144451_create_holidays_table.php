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
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Holiday name
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable(); // In case it's a multi-day holiday
            $table->string('holiday_type')->default('General'); // Optional: e.g., Public, Company, Religious
            $table->boolean('is_optional')->default(false);
            $table->boolean('is_national')->default(false);
            $table->string('created_by')->nullable(); // Name or user_id of person who added it
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
