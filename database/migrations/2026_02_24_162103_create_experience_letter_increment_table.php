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
    Schema::create('experience_letter_increment', function (Blueprint $table) {
        $table->id();

        $table->foreignId('experience_letter_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->date('increment_date');
        $table->decimal('old_salary', 12,2)->nullable();
        $table->decimal('new_salary', 12,2)->nullable();
        $table->string('old_position')->nullable();
        $table->string('new_position')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experience_letter_increment');
    }
};
