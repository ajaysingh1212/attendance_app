<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        $columns = [
            'employees' => [
                'tracking_enabled' => fn (Blueprint $table) => $table->boolean('tracking_enabled')->default(false),
                'tracking_hours' => fn (Blueprint $table) => $table->decimal('tracking_hours', 5, 2)->default(1),
            ],
            'attendance_details' => [
                'tracking_enabled' => fn (Blueprint $table) => $table->boolean('tracking_enabled')->default(false),
                'holding_seconds' => fn (Blueprint $table) => $table->unsignedInteger('holding_seconds')->default(3600),
                'holding_started_at' => fn (Blueprint $table) => $table->timestamp('holding_started_at')->nullable(),
                'holding_deadline_at' => fn (Blueprint $table) => $table->timestamp('holding_deadline_at')->nullable()->index(),
                'tracking_completed_at' => fn (Blueprint $table) => $table->timestamp('tracking_completed_at')->nullable(),
                'last_tracking_at' => fn (Blueprint $table) => $table->timestamp('last_tracking_at')->nullable(),
                'last_inside_area' => fn (Blueprint $table) => $table->boolean('last_inside_area')->nullable(),
            ],
        ];
        foreach ($columns as $table => $definitions) {
            foreach ($definitions as $column => $definition) {
                if (!Schema::hasColumn($table, $column)) Schema::table($table, $definition);
            }
        }
        if (!Schema::hasTable('attendance_tracking_events')) {
        Schema::create('attendance_tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_detail_id')->constrained('attendance_details')->cascadeOnDelete();
            $table->string('type', 60);
            $table->string('status', 40)->nullable();
            $table->text('note')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('direction', 10)->nullable();
            $table->dateTime('occurred_at');
            $table->dateTime('received_at');
            $table->string('client_event_id', 80)->nullable();
            $table->unique(['attendance_detail_id', 'client_event_id'], 'attendance_client_event_unique');
        });
        }
    }
    public function down(): void {
        Schema::dropIfExists('attendance_tracking_events');
        Schema::table('attendance_details', fn (Blueprint $table) => $table->dropColumn(['tracking_enabled', 'holding_seconds', 'holding_started_at', 'holding_deadline_at', 'tracking_completed_at', 'last_tracking_at', 'last_inside_area']));
        Schema::table('employees', fn (Blueprint $table) => $table->dropColumn(['tracking_enabled', 'tracking_hours']));
    }
};
