<?php
namespace Tests\Feature;

use App\Models\AttendanceDetail;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Services\AttendanceLocationReviewService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AttendanceHoldingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->travelTo(\Carbon\Carbon::parse('2026-10-22 09:00:00'));
        Schema::create('employees', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id')->nullable();
            foreach (['full_name', 'branch_id', 'attendance_source', 'work_start_time', 'delay_time'] as $name) $t->string($name)->nullable();
            $t->timestamps();
        });
        Schema::create('attendance_details', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('employee_id')->nullable(); $t->unsignedBigInteger('user_id')->nullable();
            $t->unsignedBigInteger('changed_by')->nullable(); $t->unsignedBigInteger('office_area_id')->nullable(); $t->date('date')->nullable();
            foreach (['status', 'verification_status', 'verified_attendance_status', 'review_note'] as $name) $t->string($name)->nullable();
            foreach (['punch_in_time', 'punch_out_time', 'review_started_at', 'review_deadline_at', 'entered_office_area_at'] as $name) $t->timestamp($name)->nullable();
            foreach (['latest_latitude', 'latest_longitude', 'latest_distance_meters'] as $name) $t->double($name)->nullable();
            $t->integer('arrival_delay_seconds')->nullable(); $t->timestamps(); $t->softDeletes();
        });
        (require database_path('migrations/2026_10_22_000001_add_attendance_holding_tracking.php'))->up();
        Schema::create('office_areas', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->unsignedBigInteger('branch_id')->nullable(); $t->unsignedBigInteger('office_branch_id')->nullable();
            $t->double('latitude'); $t->double('longitude'); $t->integer('radius_meters'); $t->boolean('is_active'); $t->integer('review_minutes');
        });
        DB::table('office_areas')->insert(['id' => 1, 'name' => 'Office', 'branch_id' => 1, 'latitude' => 28.6, 'longitude' => 77.2, 'radius_meters' => 100, 'is_active' => true, 'review_minutes' => 10]);
        Schema::create('leave_requests', function (Blueprint $t) {
            $t->id(); $t->date('date_from'); $t->date('date_to'); $t->string('status')->nullable(); $t->timestamps(); $t->softDeletes();
        });
    }

    private function punch(bool $tracked = true, bool $inside = true): array
    {
        $employee = Employee::create(['full_name' => 'Test', 'branch_id' => '1', 'tracking_enabled' => $tracked, 'tracking_hours' => 1]);
        $attendance = AttendanceDetail::create([
            'employee_id' => $employee->id, 'date' => today()->toDateString(), 'punch_in_time' => now(),
            'status' => $inside ? 'present' : 'in_review', 'verification_status' => $inside ? 'approved' : 'in_review',
            'office_area_id' => 1, 'entered_office_area_at' => $inside ? now() : null,
            'review_deadline_at' => $inside ? null : now()->addMinutes(10),
        ]);
        return [$employee, $attendance];
    }

    public function test_holding_starts_and_uses_a_snapshot_of_employee_policy(): void
    {
        [$employee, $attendance] = $this->punch();
        $this->assertSame('holding', $attendance->verification_status);
        $this->assertSame('in_review', $attendance->status);
        $this->assertSame(3600, $attendance->holding_deadline_at->timestamp - $attendance->holding_started_at->timestamp);
        $employee->update(['tracking_hours' => 2]);
        $this->assertEquals(3600, $attendance->fresh()->holding_seconds);
    }

    public function test_early_exit_is_sparse_logged_with_direction_and_is_sticky(): void
    {
        [$employee, $attendance] = $this->punch();
        $service = app(AttendanceLocationReviewService::class);
        $service->update($attendance, $employee, 28.6, 77.2);
        $this->assertSame(0, $attendance->trackingEvents()->where('type', 'area_exit')->count());
        $this->travel(5)->minutes();
        $service->update($attendance, $employee, 28.61, 77.2);
        $this->assertSame('suspicious', $attendance->fresh()->status);
        $exit = $attendance->trackingEvents()->where('type', 'area_exit')->first();
        $this->assertSame('N', $exit->direction);
        $service->update($attendance, $employee, 28.62, 77.2);
        $service->update($attendance, $employee, 28.6, 77.2);
        $this->assertSame(1, $attendance->trackingEvents()->where('type', 'area_exit')->count());
        $this->assertSame('suspicious', $attendance->fresh()->status);
    }

    public function test_successful_holding_is_final_even_after_leaving(): void
    {
        [$employee, $attendance] = $this->punch();
        $service = app(AttendanceLocationReviewService::class);
        for ($i = 0; $i < 12; $i++) {
            $this->travel(5)->minutes(); $service->update($attendance, $employee, 28.6, 77.2);
        }
        $this->assertSame('present', $attendance->fresh()->status);
        $this->assertNotNull($attendance->fresh()->tracking_completed_at);
        $this->travel(5)->minutes();
        $service->update($attendance, $employee, 28.61, 77.2);
        $this->assertSame('present', $attendance->fresh()->status);
        $this->assertSame(0, $attendance->trackingEvents()->where('type', 'area_exit')->count());
    }

    public function test_missing_heartbeat_does_not_silently_approve(): void
    {
        [$employee, $attendance] = $this->punch();
        $this->travel(7)->minutes();
        app(AttendanceLocationReviewService::class)->update($attendance, $employee, 28.6, 77.2);
        $this->assertSame('suspicious', $attendance->fresh()->status);
        $this->assertSame(1, $attendance->trackingEvents()->where('type', 'signal_missing')->count());
    }

    public function test_arrival_before_deadline_starts_holding_but_late_arrival_stays_suspicious(): void
    {
        [$employee, $attendance] = $this->punch(true, false);
        $this->travel(5)->minutes();
        app(AttendanceLocationReviewService::class)->update($attendance, $employee, 28.6, 77.2);
        $this->assertSame('holding', $attendance->fresh()->verification_status);
        [$lateEmployee, $lateAttendance] = $this->punch(true, false);
        $this->travel(11)->minutes();
        app(AttendanceLocationReviewService::class)->update($lateAttendance, $lateEmployee, 28.6, 77.2);
        $this->assertSame('suspicious', $lateAttendance->fresh()->status);
        $this->assertNotNull($lateAttendance->fresh()->entered_office_area_at);
    }

    public function test_disabled_tracking_keeps_existing_present_behavior(): void
    {
        [$employee, $attendance] = $this->punch(false);
        $this->assertSame('present', $attendance->status);
        $this->assertNull($attendance->holding_started_at);
        app(AttendanceLocationReviewService::class)->update($attendance, $employee, 28.61, 77.2);
        $this->assertSame('present', $attendance->fresh()->status);
    }

    public function test_non_admin_cannot_reset_suspicious_attendance(): void
    {
        [$employee, $attendance] = $this->punch();
        app(AttendanceLocationReviewService::class)->update($attendance, $employee, 28.61, 77.2);
        $this->expectException(ValidationException::class);
        $attendance->fresh()->update(['status' => 'present', 'verification_status' => 'approved']);
    }

    public function test_device_events_are_idempotent_and_keep_device_and_received_times(): void
    {
        [$employee, $attendance] = $this->punch();
        $events = ['events' => [['id' => 'offline-1', 'type' => 'network_offline', 'occurred_at' => now()->toIso8601String()]]];
        $service = app(AttendanceLocationReviewService::class);
        $service->update($attendance, $employee, null, null, $events);
        $service->update($attendance, $employee, null, null, $events);
        $this->assertSame(1, $attendance->trackingEvents()->where('client_event_id', 'offline-1')->count());
    }

    public function test_same_day_leave_is_rejected_and_tomorrow_is_allowed(): void
    {
        try {
            LeaveRequest::create(['date_from' => today()->toDateString(), 'date_to' => today()->toDateString()]);
            $this->fail('Same-day leave must fail');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('date_from', $error->errors());
        }
        $leave = LeaveRequest::create(['date_from' => today()->addDay()->toDateString(), 'date_to' => today()->addDay()->toDateString()]);
        $this->assertNotNull($leave->created_at);
        $this->travel(2)->days();
        $leave->update(['status' => 'approved']);
        $this->assertSame('approved', $leave->fresh()->status);
    }

    public function test_location_api_uses_authenticated_employee_and_returns_holding_state(): void
    {
        [$employee, $attendance] = $this->punch();
        DB::table('employees')->where('id', $employee->id)->update(['user_id' => 91]);
        DB::table('attendance_details')->where('id', $attendance->id)->update(['user_id' => 91]);
        $user = new \App\Models\User;
        $user->id = 91;
        $this->actingAs($user)->postJson('/api/v1/attendance/location', [
            'attendance_id' => $attendance->id, 'latitude' => 28.6, 'longitude' => 77.2,
        ])->assertOk()->assertJsonPath('status', 'holding')->assertJsonPath('tracking_active', true);
    }

    public function test_admin_approval_stops_tracking_permanently(): void
    {
        [$employee, $attendance] = $this->punch();
        app(AttendanceLocationReviewService::class)->update($attendance, $employee, 28.61, 77.2);
        $admin = \Mockery::mock(\App\Models\User::class)->makePartial();
        $admin->shouldReceive('getIsAdminAttribute')->andReturn(true);
        \Illuminate\Support\Facades\Auth::shouldReceive('user')->andReturn($admin);
        \Illuminate\Support\Facades\Auth::shouldReceive('check')->andReturn(false);
        \Illuminate\Support\Facades\Auth::shouldReceive('id')->andReturn(7);
        $request = \Illuminate\Http\Request::create('/', 'PATCH', ['status' => 'approve', 'review_note' => 'Approved after review.']);
        app(\App\Http\Controllers\Admin\AttendanceReviewController::class)->update($request, $attendance->fresh());
        $this->assertNotNull($attendance->fresh()->tracking_completed_at);
        $this->assertSame('present', $attendance->fresh()->status);
        app(AttendanceLocationReviewService::class)->update($attendance, $employee, 28.62, 77.2);
        $this->assertSame('present', $attendance->fresh()->status);
    }

    public function test_editing_an_unchanged_historical_leave_date_passes_request_rules(): void
    {
        $leave = new LeaveRequest(['date_from' => '2026-10-20', 'date_to' => '2026-10-20']);
        $request = \App\Http\Requests\UpdateLeaveRequestRequest::create('/leave-requests/1', 'PUT', ['date_from' => '2026-10-20', 'date_to' => '2026-10-20']);
        $route = new \Illuminate\Routing\Route('PUT', 'leave-requests/{leave_request}', fn () => null);
        $route->bind($request); $route->setParameter('leave_request', $leave);
        $request->setRouteResolver(fn () => $route);
        $this->assertFalse(validator($request->all(), $request->rules())->fails());
        $request->merge(['date_from' => '2026-10-22', 'date_to' => '2026-10-22']);
        $this->assertTrue(validator($request->all(), $request->rules())->fails());
    }

    public function test_exit_after_deadline_does_not_reopen_a_completed_holding_period(): void
    {
        [$employee, $attendance] = $this->punch();
        $service = app(AttendanceLocationReviewService::class);
        for ($i = 0; $i < 11; $i++) {
            $this->travel(5)->minutes(); $service->update($attendance, $employee, 28.6, 77.2);
        }
        $this->travel(6)->minutes();
        $service->update($attendance, $employee, 28.61, 77.2);
        $this->assertSame('present', $attendance->fresh()->status);
        $this->assertSame(0, $attendance->trackingEvents()->where('type', 'area_exit')->count());
    }

    public function test_migration_can_resume_after_partially_applied_columns(): void
    {
        (require database_path('migrations/2026_10_22_000001_add_attendance_holding_tracking.php'))->up();
        $this->assertTrue(Schema::hasColumn('employees', 'tracking_hours'));
        $this->assertTrue(Schema::hasTable('attendance_tracking_events'));
    }

    protected function tearDown(): void
    {
        $this->travelBack();
        parent::tearDown();
    }
}
