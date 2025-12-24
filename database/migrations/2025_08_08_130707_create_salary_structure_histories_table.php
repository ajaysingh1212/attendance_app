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
        Schema::create('salary_structure_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('salary_structure_id');
            $table->unsignedBigInteger('employee_id');
            $table->json('structure_snapshot'); // save full salary structure as JSON
            $table->timestamps();

            $table->foreign('salary_structure_id')->references('id')->on('salary_structures')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_structure_histories');
    }
};
