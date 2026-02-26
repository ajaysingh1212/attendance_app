<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\AttendanceDetail;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class HomeController
{
public function index(Request $request)
{
    /* ================= BASIC DATA ================= */
    $users = User::all();
    $selectedUserId = $request->get('user_id');
    $selectedDate = $request->get('date');
    $monthInput = $request->get('month') ?? Carbon::now()->format('Y-m');

    $attendances = collect();
    $monthlyData = [];

    /* ================= ATTENDANCE LOGIC ================= */
    if ($selectedUserId) {
        $month = Carbon::createFromFormat('Y-m', $monthInput);
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();
        $period = CarbonPeriod::create($start, $end);

        $allAttendances = AttendanceDetail::where('user_id', $selectedUserId)
            ->whereBetween('punch_in_time', [
                $start->copy()->startOfDay(),
                $end->copy()->endOfDay()
            ])
            ->get()
            ->groupBy(fn ($item) =>
                Carbon::parse($item->punch_in_time)->format('Y-m-d')
            );

        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            $records = $allAttendances->get($dateString);

            $statuses = $records
                ? $records->pluck('status')->unique()->implode(', ')
                : null;

            $monthlyData[$dateString] = $statuses ?? 'no data';
        }

        if ($selectedDate) {
            $attendances = AttendanceDetail::where('user_id', $selectedUserId)
                ->whereDate('punch_in_time', $selectedDate)
                ->get();
        }
    }

    /* ================= 🎉 MULTI CELEBRATION LOGIC ================= */
    $today = Carbon::today();

    $birthdayEmployees = Employee::whereMonth('date_of_birth', $today->month)
        ->whereDay('date_of_birth', $today->day)
        ->get();

    $anniversaryEmployees = Employee::whereMonth('anniversary_date', $today->month)
        ->whereDay('anniversary_date', $today->day)
        ->get();

    /* ================= RETURN VIEW ================= */
    return view('home', compact(
        'users',
        'attendances',
        'selectedUserId',
        'monthlyData',
        'selectedDate',
        'monthInput',
        'birthdayEmployees',
        'anniversaryEmployees'
    ));
}

    public function saveAttendance(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'date' => 'required|date',
        'status' => 'required|string',
        'time' => 'nullable'
    ]);

    $date = Carbon::parse($request->date);
    $punchIn = $request->time 
        ? Carbon::parse($request->date . ' ' . $request->time)
        : $date->copy()->startOfDay();

    // Find existing attendance record for this user and date
    $attendance = AttendanceDetail::where('user_id', $request->user_id)
        ->whereDate('punch_in_time', $date->toDateString())
        ->first();

    if ($attendance) {
        // Update only the status for existing record
        $attendance->status = $request->status;
        $attendance->save();

        return response()->json([
            'success' => true,
            'message' => 'Attendance status updated successfully!'
        ]);
    }

    // Create new record if none exists
    $attendance = new AttendanceDetail([
        'user_id' => $request->user_id,
        'punch_in_time' => $punchIn,
        'status' => $request->status,
        'punch_in_latitude' => null,
        'punch_in_longitude' => null,
        'punch_in_location' => 'Manual Entry'
    ]);

    $attendance->save();

    return response()->json([
        'success' => true,
        'message' => 'New attendance record created successfully!'
    ]);
}
}