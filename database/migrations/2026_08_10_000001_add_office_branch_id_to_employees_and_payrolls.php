<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'office_branch_id')) {
                $table->foreignId('office_branch_id')
                    ->nullable()
                    ->after('branch_id')
                    ->constrained('office_branches')
                    ->nullOnDelete();
            }
        });

        Schema::table('payrolls', function (Blueprint $table) {
            if (!Schema::hasColumn('payrolls', 'office_branch_id')) {
                $table->foreignId('office_branch_id')
                    ->nullable()
                    ->after('employee_id')
                    ->constrained('office_branches')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            if (Schema::hasColumn('payrolls', 'office_branch_id')) {
                $table->dropConstrainedForeignId('office_branch_id');
            }
        });

        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'office_branch_id')) {
                $table->dropConstrainedForeignId('office_branch_id');
            }
        });
    }
};
