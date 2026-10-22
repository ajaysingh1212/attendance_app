<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceDetail;
use App\Services\AttendanceStatusService;
use Illuminate\Http\Request;

class AttendanceReviewController extends Controller
{
    public function feed()
    {
        abort_unless(auth()->user()?->is_admin, 403);
        AttendanceDetail::whereIn('verification_status', ['in_review', 'holding'])->each(function ($item) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($item) {
                $locked = AttendanceDetail::whereKey($item->id)->lockForUpdate()->first();
                app(\App\Services\AttendanceLocationReviewService::class)->expire($locked);
            });
        });

        $items = AttendanceDetail::with(['user', 'employee.branch', 'employee.officeBranch', 'officeArea', 'trackingEvents'])
            ->whereDate('date', now()->toDateString())
            ->latest('punch_in_time')
            ->limit(100)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'employee' => $item->employee->full_name ?? $item->user->name ?? 'Employee',
                'employee_code' => $item->employee->employee_code ?? null,
                'office' => $item->employee->branch->title ?? $item->employee->officeBranch->branch_name ?? null,
                'status' => $item->verification_status ?? $item->status ?? 'in_review',
                'punch_time' => $item->punch_in_time,
                'punch_location' => $item->punch_in_location,
                'punch_latitude' => $item->punch_in_latitude,
                'punch_longitude' => $item->punch_in_longitude,
                'distance' => $item->punch_distance_meters,
                'area' => $item->officeArea->name ?? null,
                'radius' => $item->officeArea->radius_meters ?? null,
                'deadline' => $item->review_deadline_at,
                'entered_at' => $item->entered_office_area_at,
                'arrival_delay_seconds' => $item->arrival_delay_seconds,
                'latest_distance' => $item->latest_distance_meters,
                'reason' => $item->review_note,
                'attendance_status' => $item->status,
                'holding_deadline' => $item->holding_deadline_at,
                'holding_seconds' => $item->holding_seconds,
                'tracking_completed_at' => $item->tracking_completed_at,
                'events' => $item->trackingEvents,
            ]);

        return response()->json(['items' => $items, 'server_time' => now()->toIso8601String()]);
    }

    public function update(Request $request, AttendanceDetail $attendanceDetail)
    {
        abort_unless(auth()->user()?->is_admin, 403);
        return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $attendanceDetail) {
            $attendanceDetail = AttendanceDetail::whereKey($attendanceDetail->id)->lockForUpdate()->firstOrFail();
        $data = $request->validate([
            'status' => 'required|in:approve,reject,suspicious',
            'review_note' => 'required_if:status,approve|nullable|string|max:1000',
        ]);

        if ($data['status'] === 'approve' && $attendanceDetail->verification_status !== 'suspicious') {
            return response()->json([
                'success' => false,
                'message' => 'Attendance can only be approved after the review timer expires and it becomes suspicious.',
            ], 422);
        }

        $finalStatus = $data['status'];
        $verificationStatus = $data['status'];
        if ($data['status'] === 'approve') {
            $attendanceDetail->loadMissing('employee');
            $finalStatus = $attendanceDetail->employee && $attendanceDetail->punch_in_time
                ? app(AttendanceStatusService::class)->forPunchIn($attendanceDetail->employee, $attendanceDetail->punch_in_time)
                : ($attendanceDetail->verified_attendance_status ?: 'present');
            $verificationStatus = 'verified';
        } elseif ($data['status'] === 'reject') {
            $finalStatus = 'absent';
            $verificationStatus = 'rejected';
        }

        $attendanceDetail->tracking_completed_at = now();
        $attendanceDetail->update([
            'status' => $finalStatus,
            'verified_attendance_status' => $finalStatus,
            'verification_status' => $verificationStatus,
            'review_note' => isset($data['review_note']) ? trim($data['review_note']) : $attendanceDetail->review_note,
            'changed_by' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Attendance status updated.', 'status' => $finalStatus]);
        });
    }
}
