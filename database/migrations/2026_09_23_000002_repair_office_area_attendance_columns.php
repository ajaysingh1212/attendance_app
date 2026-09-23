<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('office_areas')) {
            Schema::create('office_areas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('office_branch_id')->nullable()->constrained('office_branches')->nullOnDelete();
                $table->string('name');
                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->unsignedInteger('radius_meters');
                $table->unsignedInteger('review_minutes')->default(10);
                $table->string('color', 7)->default('#2563eb');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        $columns = [
            'office_area_id' => fn (Blueprint $table) => $table->unsignedBigInteger('office_area_id')->nullable()->after('employee_id'),
            'verification_status' => fn (Blueprint $table) => $table->string('verification_status')->nullable()->after('status'),
            'verified_attendance_status' => fn (Blueprint $table) => $table->string('verified_attendance_status')->nullable()->after('verification_status'),
            'review_started_at' => fn (Blueprint $table) => $table->timestamp('review_started_at')->nullable()->after('verified_attendance_status'),
            'review_deadline_at' => fn (Blueprint $table) => $table->timestamp('review_deadline_at')->nullable()->after('review_started_at'),
            'entered_office_area_at' => fn (Blueprint $table) => $table->timestamp('entered_office_area_at')->nullable()->after('review_deadline_at'),
            'latest_latitude' => fn (Blueprint $table) => $table->decimal('latest_latitude', 10, 7)->nullable()->after('entered_office_area_at'),
            'latest_longitude' => fn (Blueprint $table) => $table->decimal('latest_longitude', 10, 7)->nullable()->after('latest_latitude'),
            'punch_distance_meters' => fn (Blueprint $table) => $table->decimal('punch_distance_meters', 10, 2)->nullable()->after('latest_longitude'),
            'latest_distance_meters' => fn (Blueprint $table) => $table->decimal('latest_distance_meters', 10, 2)->nullable()->after('punch_distance_meters'),
            'review_note' => fn (Blueprint $table) => $table->text('review_note')->nullable()->after('latest_distance_meters'),
        ];

        foreach ($columns as $name => $definition) {
            if (!Schema::hasColumn('attendance_details', $name)) {
                Schema::table('attendance_details', $definition);
            }
        }
    }

    public function down(): void
    {
        // This migration repairs a partial production deployment. Rollback must not
        // remove columns that may have been created by the primary migration.
    }
};
