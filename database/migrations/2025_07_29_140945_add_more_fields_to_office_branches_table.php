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
        // database/migrations/xxxx_add_more_fields_to_office_branches_table.php
        Schema::table('office_branches', function (Blueprint $table) {
            $table->string('gst_number')->nullable();
            $table->string('pan_number')->nullable();
            $table->string('legal_entity_name')->nullable();
            $table->string('incharge_name');
            $table->string('incharge_phone');
            $table->string('incharge_email')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('office_branches', function (Blueprint $table) {
            //
        });
    }
};
