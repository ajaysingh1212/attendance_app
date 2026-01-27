<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CsvImportTrait;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\MassDestroyAttendanceDetailRequest;
use App\Http\Requests\StoreAttendanceDetailRequest;
use App\Http\Requests\UpdateAttendanceDetailRequest;
use App\Models\AttendanceDetail;
use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class AttendanceDetailController extends Controller
{
    use MediaUploadingTrait, CsvImportTrait;

    public function index(Request $request)
    {
        abort_if(Gate::denies('attendance_detail_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $users = auth()->user()->is_admin ? User::all() : null;

        return view('admin.attendanceDetails.index', compact('users'));
    }

public function calendarData(Request $request, User $user)
{
    if (!auth()->user()->is_admin && auth()->id() !== $user->id) {
        abort(403, 'Unauthorized access.');
    }

    $start = Carbon::parse($request->start ?? now()->copy()->startOfMonth()->subMonth());
    $end = Carbon::parse($request->end ?? now()->copy()->endOfMonth()->addMonth());
    $today = now()->toDateString();

    $attendances = AttendanceDetail::where('user_id', $user->id)
        ->whereBetween('punch_in_time', [$start, $end])
        ->get()
        ->keyBy(fn($item) => Carbon::parse($item->punch_in_time ?: $item->created_at)->toDateString());

    $holidays = Holiday::where(function ($query) use ($start, $end) {
        $query->whereBetween('start_date', [$start, $end])
              ->orWhereBetween('end_date', [$start, $end])
              ->orWhere(function ($q) use ($start, $end) {
                  $q->where('start_date', '<=', $start)->where('end_date', '>=', $end);
              });
    })->get();

    $leaveRequests = LeaveRequest::where('user_id', $user->id)
        ->where(function ($query) use ($start, $end) {
            $query->whereBetween('date_from', [$start, $end])
                ->orWhereBetween('date_to', [$start, $end])
                ->orWhere(function ($q) use ($start, $end) {
                    $q->where('date_from', '<=', $start)->where('date_to', '>=', $end);
                });
        })->get();

    $leaveDates = [];
    foreach ($leaveRequests as $leave) {
        $from = Carbon::parse($leave->date_from);
        $to = Carbon::parse($leave->date_to);
        for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
            $leaveDates[$date->toDateString()] = $leave->title . ' (' . ucfirst($leave->status) . ')';
        }
    }

    $events = [];

    // Loop through each day
    for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
        $dateStr = $date->toDateString();

   if ($attendances->has($dateStr)) {
    $status = strtolower($attendances[$dateStr]->status ?? 'present');

    // ✅ Agar Sunday hai aur attendance bhi hai -> dual label
    $title = $date->isSunday() 
        ? ucfirst($status) . ' (Week Off)' 
        : ucfirst($status);

    $events[] = [
        'title' => $title,
        'start' => $dateStr,
        'classNames' => [$status],
    ];
} elseif (isset($leaveDates[$dateStr])) {
    $events[] = [
        'title' => $leaveDates[$dateStr],
        'start' => $dateStr,
        'classNames' => ['leave'],
    ];
} elseif ($date->isSunday()) {
    $events[] = [
        'title' => 'Week Off',
        'start' => $dateStr,
        'classNames' => ['week_off'],
    ];
} elseif ($dateStr <= $today) {
    $events[] = [
        'title' => 'Absent',
        'start' => $dateStr,
        'classNames' => ['absent'],
    ];
}

    }

    // Add full holiday ranges
    foreach ($holidays as $holiday) {
        $from = Carbon::parse($holiday->start_date);
        $to = Carbon::parse($holiday->end_date);
        $title = $holiday->title;

        for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
            $events[] = [
                'title' => $title,
                'start' => $date->toDateString(),
                'classNames' => ['holiday'],
                'extendedProps' => [
                    'description' => $holiday->description,
                    'holiday_type' => $holiday->holiday_type,
                    'is_optional' => $holiday->is_optional,
                    'is_national' => $holiday->is_national,
                ],
            ];
        }
    }

    return response()->json($events);
}
// AttendanceDetailController.php

public function fetchDetail(Request $request)
{
    $userId = $request->get('user_id');
    $date   = $request->get('date');

    /* ================= ATTENDANCE ================= */
    $attendanceDetail = AttendanceDetail::where('user_id', $userId)
        ->whereDate('date', $date)
        ->first();

    /* ================= ATTENDANCE LOG ================= */
    $attendanceLog = \App\Models\AttendanceLog::where('user_id', $userId)
        ->whereDate('date', $date)
        ->first();

    /* ================= EMPLOYEE (WORK TIME) ================= */
    $employee = \App\Models\Employee::where('user_id', $userId)
        ->select('work_start_time', 'work_end_time')
        ->first();

    /* ================= SAFE DATA ================= */
    $data = [
        // attendance
        'attendanceDetail' => $attendanceDetail,
        'attendanceLog'    => $attendanceLog,

        // work time (always from employee table)
        'work_start_time'  => $employee?->work_start_time,
        'work_end_time'    => $employee?->work_end_time,

        // punch in (only if attendance exists)
        'punchInLatitude'  => $attendanceDetail?->punch_in_latitude,
        'punchInLongitude' => $attendanceDetail?->punch_in_longitude,
        'punchInLocation'  => $attendanceDetail?->punch_in_location,

        // punch out
        'punchOutLatitude' => $attendanceDetail?->punch_out_latitude,
        'punchOutLongitude'=> $attendanceDetail?->punch_out_longitude,
        'punchOutLocation' => $attendanceDetail?->punch_out_location,

        // flags for frontend logic
        'hasPunchIn'       => (bool) $attendanceDetail?->punch_in_time,
        'hasPunchOut'      => (bool) $attendanceDetail?->punch_out_time,
    ];

    return view('admin.attendanceDetails.partials.attendance_modal', $data);
}


public function create()
{
    abort_if(Gate::denies('attendance_detail_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

    $authUser = auth()->user();
    $isAdmin = $authUser->roles->contains('title', 'Admin');

    $users = $isAdmin
        ? User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '')
        : collect([$authUser->id => $authUser->name]);

    $today = now()->format('Y-m-d');

    // ✅ Custom logic function to fetch any data you want (e.g., today's attendance)
    $todayAttendance = $this->getTodayAttendanceDetails($authUser->id);
    
    return view('admin.attendanceDetails.create', compact('users', 'today', 'todayAttendance'));
}
private function getTodayAttendanceDetails($userId)
{
    return AttendanceDetail::where('user_id', $userId)
        ->whereDate('date', now()->toDateString())
        ->latest()
        ->first();
}

public function store(StoreAttendanceDetailRequest $request)
{
    // dd($request->all());
    // Step 1: Get target user ID from form
    $userId = $request->input('user_id');

    // Step 2: Get employee linked with that user
    $employee = \App\Models\Employee::where('user_id', $userId)->firstOrFail();

    // Step 3: Get branch info
    $branch = \App\Models\Branch::find($employee->branch_id);

    // Step 4: Location verification based on branch & employee radius
    if ($branch && strtolower($branch->name) !== 'anywhere') {

        // Agar employee ke attendance radius set hai, tabhi check karega
        $allowedRadius = $employee->attendance_radius_meter ?? 0;

        if ($allowedRadius > 0 && !empty($request->punch_in_latitude) && !empty($request->punch_in_longitude)) {

            $branchLat = $branch->latitude;
            $branchLng = $branch->longitude;
            $userLat   = $request->punch_in_latitude;
            $userLng   = $request->punch_in_longitude;

            // Distance calculate
            $distance = $this->calculateDistance($userLat, $userLng, $branchLat, $branchLng);

            // Validate radius
            if ($distance > $allowedRadius) {
                return back()->withErrors([
                    'location' => 'Punch-in location is out of allowed radius. 
                        You are ' . round($distance, 2) . ' meters away. 
                        Allowed: ' . $allowedRadius . 'm.'
                ])->withInput();
            }
        }
    }

    // ✅ Late logic for self punch-ins
    if ($request->input('type') === 'self' && $request->filled('punch_in_time')) {
        $workStart = Carbon::parse($employee->work_start_time); // e.g. 09:00:00
        $punchIn = Carbon::parse($request->punch_in_time);
        $delayAllowed = (int) $employee->delay_time;

        if ($punchIn->gt($workStart->copy()->addMinutes($delayAllowed))) {
            $request->merge(['status' => 'half_time']);
        } else {
            $request->merge(['status' => 'present']);
        }
    }

    // Step 5: Store punch-in or punch-out
    $attendance = AttendanceDetail::firstOrNew([
        'user_id' => $userId,
        'date' => $request->input('attendance_date', now()->format('Y-m-d')),
    ]);

    if ($request->has('punch_in_time')) {
        $attendance->fill($request->only([
            'punch_in_time',
            'punch_in_latitude',
            'punch_in_longitude',
            'punch_in_location',
        ]));

       if ($request->hasFile('punch_in_image')) {
    $attendance->addMedia($request->file('punch_in_image'))
        ->toMediaCollection('punch_in_image');
}


        // ✅ Update status on punch-in
        $attendance->status = $request->input('status');
    }
    // dd($attendance);
//     dd([
//     'attributes' => $attendance->toArray(),
//     'media' => $attendance->getMedia('punch_in_image')->pluck('file_name'),
// ]);

    if ($request->has('punch_out_time')) {
        $attendance->fill($request->only([
            'punch_out_time',
            'punch_out_latitude',
            'punch_out_longitude',
            'punch_out_location',
        ]));

      if ($request->hasFile('punch_out_image')) {
    $attendance->addMedia($request->file('punch_out_image'))
        ->toMediaCollection('punch_out_image');
}

    }

    $attendance->user_id = $userId;
    $attendance->employee_id = $employee->id;
    $attendance->date = $request->input('attendance_date', now()->format('Y-m-d'));
    $attendance->type = $request->input('type', 'manual');
    $attendance->save();

    // Step 6: AttendanceLog update only if punch out
    if ($attendance->punch_in_time && $attendance->punch_out_time) {
        $workStart = Carbon::parse($employee->work_start_time);
        $workEnd = Carbon::parse($employee->work_end_time);
        $actualIn = Carbon::parse($attendance->punch_in_time);
        $actualOut = Carbon::parse($attendance->punch_out_time);

        // Calculate lateness, early leave, overtime
        $lateBy = $actualIn->gt($workStart) ? $actualIn->diffInMinutes($workStart) : 0;
        $earlyBy = $actualOut->lt($workEnd) ? $workEnd->diffInMinutes($actualOut) : 0;
        $expectedWorkMinutes = $workEnd->diffInMinutes($workStart);
        $actualWorkMinutes = $actualOut->diffInMinutes($actualIn);
        $overtime = $actualWorkMinutes > $expectedWorkMinutes
            ? $actualWorkMinutes - $expectedWorkMinutes
            : 0;

        \App\Models\AttendanceLog::updateOrCreate([
            'user_id' => $userId,
            'employee_id' => $employee->id,
            'date' => $attendance->date,
        ], [
            'expected_in' => $workStart->format('H:i:s'),
            'actual_in' => $actualIn->format('H:i:s'),
            'late_by_minutes' => $lateBy,
            'expected_out' => $workEnd->format('H:i:s'),
            'actual_out' => $actualOut->format('H:i:s'),
            'left_early_by_minutes' => $earlyBy,
            'overtime_by_minutes' => $overtime,
            'total_work_minutes' => $actualWorkMinutes,
        ]);
    }

    return redirect()->route('admin.attendance-details.index')->with('success', 'Attendance recorded successfully.');
}




    public function edit(AttendanceDetail $attendanceDetail)
    {
        abort_if(Gate::denies('attendance_detail_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $users = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $attendanceDetail->load('user');

        return view('admin.attendanceDetails.edit', compact('attendanceDetail', 'users'));
    }

    public function update(UpdateAttendanceDetailRequest $request, AttendanceDetail $attendanceDetail)
    {
        $attendanceDetail->update($request->all());

        if ($request->input('punch_in_image')) {
            $attendanceDetail->addMedia(storage_path('tmp/uploads/' . basename($request->input('punch_in_image'))))->toMediaCollection('punch_in_image');
        }

        if ($request->input('punch_out_image')) {
            $attendanceDetail->addMedia(storage_path('tmp/uploads/' . basename($request->input('punch_out_image'))))->toMediaCollection('punch_out_image');
        }

        $employee = Employee::where('user_id', Auth::id())->first();
        if ($employee) {
            $this->updateAttendanceLog($attendanceDetail, $employee);
        }

        return redirect()->route('attendance-details.index');
    }

    public function show(AttendanceDetail $attendanceDetail)
    {
        abort_if(Gate::denies('attendance_detail_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $attendanceDetail->load('user');
        return view('admin.attendanceDetails.show', compact('attendanceDetail'));
    }

    public function destroy(AttendanceDetail $attendanceDetail)
    {
        abort_if(Gate::denies('attendance_detail_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $attendanceDetail->delete();
        return back();
    }

    public function massDestroy(MassDestroyAttendanceDetailRequest $request)
    {
        AttendanceDetail::whereIn('id', request('ids'))->delete();
        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function storeCKEditorImages(Request $request)
    {
        abort_if(Gate::denies('attendance_detail_create') && Gate::denies('attendance_detail_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $model = new AttendanceDetail();
        $model->id = $request->input('crud_id', 0);
        $model->exists = true;
        $media = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');

        return response()->json(['id' => $media->id, 'url' => $media->getUrl()], Response::HTTP_CREATED);
    }

    // 🔁 Shared utility methods
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2 +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    private function updateAttendanceLog($attendance, $employee)
    {
        $workStart = Carbon::parse($employee->work_start_time);
        $workEnd = Carbon::parse($employee->work_end_time);
        $actualIn = Carbon::parse($attendance->punch_in_time);
        $actualOut = Carbon::parse($attendance->punch_out_time);

        AttendanceLog::updateOrCreate([
            'user_id' => $attendance->user_id,
            'employee_id' => $employee->id,
            'date' => $attendance->date,
        ], [
            'expected_in' => $workStart->format('H:i:s'),
            'actual_in' => $actualIn->format('H:i:s'),
            'late_by_minutes' => $actualIn->gt($workStart) ? $actualIn->diffInMinutes($workStart) : 0,
            'expected_out' => $workEnd->format('H:i:s'),
            'actual_out' => $actualOut->format('H:i:s'),
            'left_early_by_minutes' => $actualOut->lt($workEnd) ? $workEnd->diffInMinutes($actualOut) : 0,
            'overtime_by_minutes' => max(0, $actualOut->diffInMinutes($actualIn) - $workEnd->diffInMinutes($workStart)),
            'total_work_minutes' => $actualOut->diffInMinutes($actualIn),
        ]);
    }



public function updateStatus(Request $request)
{
    try {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,half_time,leave,week_off,holiday,paid_leave,late',

            'punch_type' => 'required|in:in,out,both',

            'punch_in_latitude' => 'nullable|numeric',
            'punch_in_longitude' => 'nullable|numeric',
            'punch_out_latitude' => 'nullable|numeric',
            'punch_out_longitude' => 'nullable|numeric',

            'punch_in_location' => 'nullable|string',
            'punch_out_location' => 'nullable|string',

            'master_password' => 'required|string',
            'changed_by' => 'required|string',
            'device_name' => 'nullable|string',
            'device_uid' => 'nullable|string',

            'punch_in_image' => 'nullable|image',
            'punch_out_image' => 'nullable|image',
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json(['success' => false, 'message' => $e->errors()], 422);
    }

    $admin = auth()->user();
    if (!Hash::check($validated['master_password'], $admin->master_password)) {
        return response()->json(['success' => false, 'message' => 'Invalid master password'], 403);
    }

    $employee = Employee::where('user_id', $validated['user_id'])->firstOrFail();

    $attendance = AttendanceDetail::firstOrNew([
        'user_id' => $validated['user_id'],
        'date' => $validated['date'],
    ]);

    $attendance->status = $validated['status'];
    $attendance->punch_type = $validated['punch_type'];
    $attendance->changed_by = $validated['changed_by'];
    $attendance->ip_address = $request->ip();
    $attendance->device_name = $validated['device_name'] ?? $request->userAgent();

    /* ---------- Punch In ---------- */
    if (in_array($validated['punch_type'], ['in', 'both'])) {
        $attendance->punch_in_time = $attendance->punch_in_time ?? now();
        $attendance->punch_in_latitude = $validated['punch_in_latitude'];
        $attendance->punch_in_longitude = $validated['punch_in_longitude'];
        $attendance->punch_in_location = $validated['punch_in_location'];
    }

    /* ---------- Punch Out ---------- */
    if (in_array($validated['punch_type'], ['out', 'both'])) {

        if (!$attendance->punch_in_time) {
            return response()->json([
                'success' => false,
                'message' => 'Punch In required before Punch Out'
            ], 422);
        }

        $attendance->punch_out_time = $attendance->punch_out_time ?? now();
        $attendance->punch_out_latitude = $validated['punch_out_latitude'];
        $attendance->punch_out_longitude = $validated['punch_out_longitude'];
        $attendance->punch_out_location = $validated['punch_out_location'];
    }

    $attendance->save();

    /* ---------- MEDIA ---------- */
    if ($request->hasFile('punch_in_image')) {
        $attendance->clearMediaCollection('punch_in_image');
        $attendance->addMediaFromRequest('punch_in_image')->toMediaCollection('punch_in_image');
    }

    if ($request->hasFile('punch_out_image')) {
        $attendance->clearMediaCollection('punch_out_image');
        $attendance->addMediaFromRequest('punch_out_image')->toMediaCollection('punch_out_image');
    }

    return response()->json([
        'success' => true,
        'message' => 'Attendance saved successfully'
    ]);
}



}
