<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceDetail;
use Illuminate\Http\Request;

class AttendanceReviewController extends Controller
{
    public function feed()
    {
        AttendanceDetail::where('verification_status', 'in_review')
            ->whereNotNull('review_deadline_at')
            ->where('review_deadline_at', '<=', now())
            ->update(['status' => 'suspicious', 'verification_status' => 'suspicious']);

        $items = AttendanceDetail::with(['user', 'employee.officeBranch', 'officeArea'])
            ->whereIn('verification_status', ['in_review', 'suspicious'])
            ->latest('punch_in_time')
            ->limit(100)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'employee' => $item->employee->full_name ?? $item->user->name ?? 'Employee',
                'employee_code' => $item->employee->employee_code ?? null,
                'office' => $item->employee->officeBranch->branch_name ?? null,
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
            'status' => 'required|in:present,half_time,suspicious,absent',
            'review_note' => 'nullable|string|max:1000',
        ]);

        $attendanceDetail->update([
            'status' => $data['status'],
            'verification_status' => in_array($data['status'], ['present', 'half_time']) ? 'approved' : $data['status'],
            'review_note' => $data['review_note'] ?? $attendanceDetail->review_note,
            'changed_by' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Attendance status updated.']);
    }
}
