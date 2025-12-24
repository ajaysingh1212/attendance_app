<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_increments', function (Blueprint $table) {
            $table->id();

            /* BASIC RELATIONS */
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('user_id')->nullable(); // linked user (optional)

            /* OLD SALARY SNAPSHOT */
            $table->decimal('old_basic', 12, 2)->default(0);
            $table->decimal('old_hra', 12, 2)->default(0);
            $table->decimal('old_allowance', 12, 2)->default(0);
            $table->decimal('old_gross_salary', 12, 2)->default(0);

            /* NEW SALARY */
            $table->decimal('new_basic', 12, 2)->default(0);
            $table->decimal('new_hra', 12, 2)->default(0);
            $table->decimal('new_allowance', 12, 2)->default(0);
            $table->decimal('new_gross_salary', 12, 2)->default(0);

            /* JSON FIELDS */
            $table->json('other_allowances_json')->nullable();
            $table->json('older_allowances_json')->nullable();

            /* MONTH APPLICABLE */
            $table->string('increment_month')->nullable();

            /* REMARKS */
            $table->text('remarks')->nullable();

            /* STATUS */
            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending');

            /* AUDIT FIELDS */
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            /* APPROVAL WORKFLOW */
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();

            $table->timestamps();

            /* FOREIGN KEYS */
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_increments');
    }
};
