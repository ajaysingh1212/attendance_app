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
        AttendanceDetail::where('verification_status', 'in_review')
            ->whereNotNull('review_deadline_at')
            ->where('review_deadline_at', '<=', now())
            ->update(['status' => 'suspicious', 'verification_status' => 'suspicious']);

        $items = AttendanceDetail::with(['user', 'employee.branch', 'employee.officeBranch', 'officeArea'])
            ->whereIn('verification_status', ['in_review', 'suspicious'])
            ->latest('punch_in_time')
            ->limit(100)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'employee' => $item->employee->full_name ?? $item->user->name ?? 'Employee',
                'employee_code' => $item->employee->employee_code ?? null,
                'office' => $item->employee->branch->title ?? $item->employee->officeBranch->branch_name ?? null,
                'status' => $item->verification_status,
                'punch_time' => $item->punch_in_time,
                'punch_location' => $item->punch_in_location,
                'punch_latitude' => $item->punch_in_latitude,
                'punch_longitude' => $item->punch_in_longitude,
                'distance' => $item->punch_distance_meters,
                'area' => $item->officeArea->name ?? null,
                'radius' => $item->officeArea->radius_meters ?? null,
                'deadline' => $item->review_deadline_at,
                'entered_at' => $item->entered_office_area_at,
                'latest_distance' => $item->latest_distance_meters,
                'reason' => $item->review_note,
            ]);

        return response()->json(['items' => $items, 'server_time' => now()->toIso8601String()]);
    }

    public function update(Request $request, AttendanceDetail $attendanceDetail)
    {
        $data = $request->validate([
            'status' => 'required|in:approve,reject,suspicious',
            'review_note' => 'nullable|string|max:1000',
        ]);

        $finalStatus = $data['status'];
        $verificationStatus = $data['status'];
        if ($data['status'] === 'approve') {
            $attendanceDetail->loadMissing('employee');
            $finalStatus = $attendanceDetail->employee && $attendanceDetail->punch_in_time
                ? app(AttendanceStatusService::class)->forPunchIn($attendanceDetail->employee, $attendanceDetail->punch_in_time)
                : ($attendanceDetail->verified_attendance_status ?: 'present');
            $verificationStatus = 'approved';
        } elseif ($data['status'] === 'reject') {
            $finalStatus = 'absent';
            $verificationStatus = 'rejected';
        }

        $attendanceDetail->update([
            'status' => $finalStatus,
            'verified_attendance_status' => $finalStatus,
            'verification_status' => $verificationStatus,
            'review_note' => $data['review_note'] ?? $attendanceDetail->review_note,
            'changed_by' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Attendance status updated.', 'status' => $finalStatus]);
    }
}
