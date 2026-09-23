<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('office_areas', 'branch_id')) {
            Schema::table('office_areas', function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()->after('office_branch_id')->constrained('branches')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('office_areas', 'branch_id')) {
            Schema::table('office_areas', function (Blueprint $table) {
                $table->dropConstrainedForeignId('branch_id');
            });
        }
    }
};
