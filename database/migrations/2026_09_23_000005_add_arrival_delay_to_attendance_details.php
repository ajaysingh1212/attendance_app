<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_details', function (Blueprint $table) {
            $table->unsignedInteger('arrival_delay_seconds')->nullable()->after('entered_office_area_at');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_details', function (Blueprint $table) {
            $table->dropColumn('arrival_delay_seconds');
        });
    }
};
