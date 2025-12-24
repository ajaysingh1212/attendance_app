<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\AttendanceDetail;
use App\Models\Holiday;
use App\Models\LeaveRequest;

class AttendanceCalendarApiController extends Controller
{
    public function getCalendarReport(Request $request, $userId)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        $month = Carbon::createFromFormat('Y-m', $request->month);
        $fromDate = $month->copy()->startOfMonth();
        $toDate   = $month->copy()->endOfMonth();
        $today = Carbon::today();

        $counts = [
            'present'  => 0,
            'half_time' => 0,
            'leave'    => 0,
            'absent'   => 0,
            'holiday'  => 0,
            'week_off' => 0,
        ];

        // Attendance details keyed by date
        $attendanceDetails = AttendanceDetail::where('user_id', $userId)
            ->whereDate('date', '>=', $fromDate)
            ->whereDate('date', '<=', $toDate)
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            });

        $holidays = Holiday::where(function ($q) use ($fromDate, $toDate) {
            $q->whereBetween('start_date', [$fromDate, $toDate])
              ->orWhereBetween('end_date', [$fromDate, $toDate]);
        })->get();

        $leaves = LeaveRequest::where('user_id', $userId)
            ->where('status', 'approved')
            ->where(function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('date_from', [$fromDate, $toDate])
                  ->orWhereBetween('date_to', [$fromDate, $toDate]);
            })->get();

        $report = [];
        $daysInMonth = $month->daysInMonth;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $carbonDate = Carbon::create($month->year, $month->month, $day);
            $dayStr = $carbonDate->format('Y-m-d');

            $dayReport = [
                'date' => $dayStr,
                'status' => 'absent', // default
            ];

            // 1️⃣ Attendance exists → override everything
            if (isset($attendanceDetails[$dayStr])) {
                $att = $attendanceDetails[$dayStr];
                $dayReport['status'] = $att->status; // present / half_time / absent
                $dayReport['type'] = $att->type;
                $dayReport['punch_in_time'] = $att->punch_in_time;
                $dayReport['punch_out_time'] = $att->punch_out_time;
                $dayReport['punch_in_location'] = $att->punch_in_location;
                $dayReport['punch_out_location'] = $att->punch_out_location;

                // Count based on status
                if ($att->status === 'present') {
                    $counts['present']++;
                } elseif ($att->status === 'half_time') {
                    $counts['half_time']++;
                } elseif ($att->status === 'absent') {
                    $counts['absent']++; // Absent overrides week_off/holiday
                }
            }
            // 2️⃣ Leave check
            elseif ($leave = $leaves->first(function ($l) use ($dayStr) {
                return $dayStr >= $l->date_from && $dayStr <= $l->date_to;
            })) {
                $dayReport['status'] = 'leave';
                $dayReport['leave_type'] = $leave->title;
                $counts['leave']++;
            }
            // 3️⃣ Week off check (Sunday) → priority over holiday
            elseif ($carbonDate->isSunday()) {
                $dayReport['status'] = 'week_off';
                $counts['week_off']++;
            }
            // 4️⃣ Holiday check (only if not Sunday)
            elseif ($holiday = $holidays->first(function ($h) use ($dayStr) {
                return $dayStr >= $h->start_date && $dayStr <= $h->end_date;
            })) {
                $dayReport['status'] = 'holiday';
                $dayReport['holiday_title'] = $holiday->title;
                $counts['holiday']++;
            }
            // 5️⃣ Default absent → only for today or past dates
            else {
                if ($carbonDate->lte($today)) {
                    $dayReport['status'] = 'absent';
                    $counts['absent']++;
                } else {
                    // Future date, no data → skip counting
                    $dayReport['status'] = null; // optional, can keep empty
                }
            }

            $report[] = $dayReport;
        }

        return response()->json([
            'success' => true,
            'message' => 'Calendar report fetched successfully',
            'month'   => $month->format('Y-m'),
            'counts'  => $counts,
            'days'    => $report
        ]);
    }
}
