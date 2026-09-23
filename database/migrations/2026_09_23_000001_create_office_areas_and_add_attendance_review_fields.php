<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

        Schema::table('attendance_details', function (Blueprint $table) {
            $table->foreignId('office_area_id')->nullable()->after('employee_id')->constrained('office_areas')->nullOnDelete();
            $table->string('verification_status')->nullable()->after('status');
            $table->string('verified_attendance_status')->nullable()->after('verification_status');
            $table->timestamp('review_started_at')->nullable()->after('verified_attendance_status');
            $table->timestamp('review_deadline_at')->nullable()->after('review_started_at');
            $table->timestamp('entered_office_area_at')->nullable()->after('review_deadline_at');
            $table->decimal('latest_latitude', 10, 7)->nullable()->after('entered_office_area_at');
            $table->decimal('latest_longitude', 10, 7)->nullable()->after('latest_latitude');
            $table->decimal('punch_distance_meters', 10, 2)->nullable()->after('latest_longitude');
            $table->decimal('latest_distance_meters', 10, 2)->nullable()->after('punch_distance_meters');
            $table->text('review_note')->nullable()->after('latest_distance_meters');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_details', function (Blueprint $table) {
            $table->dropConstrainedForeignId('office_area_id');
            $table->dropColumn([
                'verification_status', 'verified_attendance_status', 'review_started_at',
                'review_deadline_at', 'entered_office_area_at', 'latest_latitude',
                'latest_longitude', 'punch_distance_meters', 'latest_distance_meters', 'review_note',
            ]);
        });
        Schema::dropIfExists('office_areas');
    }
};
