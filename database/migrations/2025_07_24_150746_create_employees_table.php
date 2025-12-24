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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            // 🔗 User Reference
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('employee_code')->unique();
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            // 🧾 Account Details
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('pan_number')->nullable();
            $table->string('aadhaar_number')->nullable();
            $table->enum('payment_mode', ['Bank', 'Cash', 'UPI'])->default('Bank');

            // 🕐 Timing & Work Schedule
            $table->time('work_start_time')->nullable();
            $table->time('work_end_time')->nullable();
            $table->decimal('working_hours', 5, 2)->nullable();
            $table->string('weekly_off_day')->nullable();
            $table->enum('attendance_source', ['Office', 'Anywhere'])->default('Office');
            $table->integer('attendance_radius_meter')->nullable();

            // 💰 Salary Details
            $table->decimal('basic_salary', 10, 2)->nullable();
            $table->decimal('hra', 10, 2)->nullable();
            $table->decimal('other_allowances', 10, 2)->nullable();
            $table->decimal('deductions', 10, 2)->nullable();
            $table->decimal('net_salary', 10, 2)->nullable();

            // 📆 Employment Info
            $table->date('date_of_joining')->nullable();
            $table->string('position')->nullable();
            $table->string('department')->nullable();
            $table->foreignId('reporting_to')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['Active', 'Resigned', 'Terminated'])->default('Active');

            // 📸 Media
            $table->string('profile_photo')->nullable();
            $table->string('signature_image')->nullable();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
