<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            if (!Schema::hasColumn('payrolls', 'remaining_salary')) {
                $table->decimal('remaining_salary', 12, 2)->nullable()->after('net_salary');
            }

            if (!Schema::hasColumn('payrolls', 'salary_increment_id')) {
                $table->unsignedBigInteger('salary_increment_id')->nullable()->after('remaining_salary');
            }

            if (!Schema::hasColumn('payrolls', 'valid_sundays')) {
                $table->integer('valid_sundays')->nullable()->after('final_paid_days');
            }

            if (!Schema::hasColumn('payrolls', 'absent_days')) {
                $table->integer('absent_days')->nullable()->after('holidays');
            }

            if (!Schema::hasColumn('payrolls', 'salary_generated_role')) {
                $table->string('salary_generated_role')->nullable()->after('salary_generated_by');
            }

            if (!Schema::hasColumn('payrolls', 'message')) {
                $table->text('message')->nullable()->after('salary_generated_role');
            }

            if (!Schema::hasColumn('payrolls', 'total_days')) {
                $table->integer('total_days')->nullable()->after('valid_sundays');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            foreach ([
                'total_days',
                'message',
                'salary_generated_role',
                'absent_days',
                'valid_sundays',
                'salary_increment_id',
                'remaining_salary',
            ] as $column) {
                if (Schema::hasColumn('payrolls', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
