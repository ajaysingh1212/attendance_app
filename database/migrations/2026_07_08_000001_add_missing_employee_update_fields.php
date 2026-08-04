<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'bank_address')) {
                $table->string('bank_address')->nullable()->after('bank_name');
            }

            if (!Schema::hasColumn('employees', 'status_change_pending')) {
                $table->boolean('status_change_pending')->default(false)->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('employees', 'bank_address')) {
                $columns[] = 'bank_address';
            }

            if (Schema::hasColumn('employees', 'status_change_pending')) {
                $columns[] = 'status_change_pending';
            }

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
