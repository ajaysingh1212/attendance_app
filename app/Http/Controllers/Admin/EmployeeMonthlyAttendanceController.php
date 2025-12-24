<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\AttendanceDetail;
use App\Models\LeaveRequest;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmployeeMonthlyAttendanceController extends Controller
{
public function index(Request $request)
{
    $employees    = Employee::orderBy('full_name')->get();
    $employeeId   = $request->employee_id;
    $month        = $request->month;

    $attendanceSummary = null;

    if ($employeeId && $month) {
        $employee   = Employee::findOrFail($employeeId);

        $startDate  = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $endDate    = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        // Fetch all attendance records for employee
        $attendanceDetails = AttendanceDetail::where('user_id', $employee->user_id)
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->orderBy('date')
            ->get();
        
        // Build attendance map: '2025-09-01' => ['status' => 'present', ...]
        $attendanceMap = [];
        foreach ($attendanceDetails as $att) {
            $dateStr = $att->date;
            $attendanceMap[$dateStr][] = [
                'status' => $att->status,
                'punch_in' => $att->punch_in_time,
                'punch_out' => $att->punch_out_time,
                'punch_in_location' => $att->punch_in_location,
                'punch_out_location' => $att->punch_out_location,
                'hours_worked' => ($att->punch_in_time && $att->punch_out_time)
                    ? Carbon::parse($att->punch_out_time)->diffInHours(Carbon::parse($att->punch_in_time)) . 'h ' .
                      (Carbon::parse($att->punch_out_time)->diffInMinutes(Carbon::parse($att->punch_in_time)) % 60) . 'm'
                    : '-',
            ];
        }

        // Compute daily final status (present > half_time > absent)
        $dailyStatus = [];
        foreach ($attendanceMap as $date => $records) {
            $status = 'absent';
            foreach ($records as $rec) {
                if ($rec['status'] === 'present') { 
                    $status = 'present'; 
                    break; 
                } elseif ($rec['status'] === 'half_time') { 
                    $status = 'half_time'; 
                }
            }
            $dailyStatus[$date] = $status;
        }

        // Summary counts using filter
        $present = collect($dailyStatus)->filter(fn($s) => $s === 'present')->count();
        $absent  = collect($dailyStatus)->filter(fn($s) => $s === 'absent')->count();
        $halfDay = collect($dailyStatus)->filter(fn($s) => $s === 'half_time')->count();
        
        // Approved leaves
        $leaves = LeaveRequest::with('leaveType')
            ->where('user_id', $employee->user_id)
            ->where('status', 'approved')
            ->whereDate('date_from', '<=', $endDate)
            ->whereDate('date_to', '>=', $startDate)
            ->orderBy('date_from')
            ->get();

        $leaveMap = [];
        foreach ($leaves as $leave) {
            $from = Carbon::parse($leave->date_from);
            $to   = Carbon::parse($leave->date_to);
            while ($from <= $to) {
                $leaveMap[$from->toDateString()] = $leave->leaveType ? $leave->leaveType->name : 'Leave';
                $from->addDay();
            }
        }

        // Holidays
        $holidays = Holiday::whereDate('start_date', '<=', $endDate)
            ->whereDate('end_date', '>=', $startDate)
            ->orderBy('start_date')
            ->get();

        $holidayMap = [];
        foreach ($holidays as $holiday) {
            $from = Carbon::parse($holiday->start_date);
            $to   = Carbon::parse($holiday->end_date);
            while ($from <= $to) {
                $holidayMap[$from->toDateString()] = $holiday->title;
                $from->addDay();
            }
        }

        // Prepare summary array
        $attendanceSummary = [
            'employee' => $employee,
            'attendanceMap' => $dailyStatus, // use final status per day
            'leaveMap'      => $leaveMap,
            'holidayMap'    => $holidayMap,
            'present'       => $present,
            'absent'        => $absent,
            'halfDay'       => $halfDay,
        ];
    }

    return view('admin.employee_monthly_attendance.index', compact(
        'employees',
        'employeeId',
        'month',
        'attendanceSummary'
    ));
}


}
