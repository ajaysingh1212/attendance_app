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
use App\Services\PayrollCalculator;
use Carbon\Carbon;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class AttendanceDetailController extends Controller
{
    use MediaUploadingTrait, CsvImportTrait;

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        abort_if(Gate::denies('attendance_detail_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $users = auth()->user()->is_admin
            ? User::whereHas('employee')->orderBy('name')->get()
            : null;
        $defaultUserId = auth()->user()->is_admin
            ? ($users->first()?->id ?? auth()->id())
            : auth()->id();

        return view('admin.attendanceDetails.index', compact('users', 'defaultUserId'));
    }

    /*
    |--------------------------------------------------------------------------
    | CALENDAR DATA
    |--------------------------------------------------------------------------
    */
    public function calendarData(Request $request, User $user)
    {
        if (!auth()->user()->is_admin && auth()->id() !== $user->id) {
            abort(403, 'Unauthorized access.');
        }

        $startStr = substr($request->input('start', now()->startOfMonth()->toDateString()), 0, 10);
        $endStr   = substr($request->input('end',   now()->endOfMonth()->toDateString()),   0, 10);
        $today    = now()->toDateString();

        $start = Carbon::createFromFormat('Y-m-d', $startStr)->startOfDay();
        $end   = Carbon::createFromFormat('Y-m-d', $endStr)->endOfDay();

        $attendances = AttendanceDetail::where('user_id', $user->id)
            ->whereBetween('date', [$startStr, $endStr])
            ->get()
            ->keyBy(fn($a) => $a->getRawOriginal('date'));

        $holidays = Holiday::where(function ($q) use ($startStr, $endStr) {
            $q->whereBetween('start_date', [$startStr, $endStr])
              ->orWhereBetween('end_date',   [$startStr, $endStr])
              ->orWhere(function ($q2) use ($startStr, $endStr) {
                  $q2->where('start_date', '<=', $startStr)->where('end_date', '>=', $endStr);
              });
        })->get();

        $leaveRequests = LeaveRequest::with('leaveType')
            ->where('user_id', $user->id)
            ->whereRaw("LOWER(TRIM(status)) = 'approved'")
            ->where(function ($q) use ($startStr, $endStr) {
                $q->whereBetween('date_from', [$startStr, $endStr])
                  ->orWhereBetween('date_to',   [$startStr, $endStr])
                  ->orWhere(function ($q2) use ($startStr, $endStr) {
                      $q2->where('date_from', '<=', $startStr)->where('date_to', '>=', $endStr);
                  });
            })->get();

        $leaveDates = [];
        foreach ($leaveRequests as $leave) {
            $isPaidLeave = \App\Models\LeaveType::isPaidName($leave->leaveType->name ?? null);
            $from = Carbon::parse($leave->date_from);
            $to   = Carbon::parse($leave->date_to);
            for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
                $date = $d->toDateString();
                $existingIsPaid = isset($leaveDates[$date])
                    && $leaveDates[$date]['class'] === 'paid_leave';
                $dateIsPaid = isset($leaveDates[$date])
                    ? ($existingIsPaid && $isPaidLeave)
                    : $isPaidLeave;

                $leaveDates[$date] = [
                    'class' => $dateIsPaid ? 'paid_leave' : 'unpaid_leave',
                    'title' => $dateIsPaid ? 'Paid Leave' : 'Unpaid Leave',
                    'type'  => $leave->leaveType->name ?? $leave->title,
                ];
            }
        }

        $holidayDates = [];
        foreach ($holidays as $holiday) {
            $from = Carbon::parse($holiday->start_date);
            $to   = Carbon::parse($holiday->end_date);
            for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
                $holidayDates[$d->toDateString()] = $holiday;
            }
        }

        $events = [];

        for ($d = $start->copy()->startOfDay(); $d->toDateString() <= $endStr; $d->addDay()) {
            $dateStr  = $d->toDateString();
            $isSunday = $d->isSunday();

            // Approved leave has priority. Otherwise normal attendance decides.
            if (isset($leaveDates[$dateStr])) {
                $leave = $leaveDates[$dateStr];
                $events[] = [
                    'title'      => $leave['title'],
                    'start'      => $dateStr,
                    'classNames' => [$leave['class']],
                    'extendedProps' => ['leave_type' => $leave['type']],
                ];
            } elseif ($attendances->has($dateStr)) {
                $rec    = $attendances[$dateStr];
                $status = strtolower($rec->status ?? 'present');
                $title  = $isSunday ? ucfirst($status) . ' (Week Off)' : ucfirst($status);
                $events[] = [
                    'title'      => $title,
                    'start'      => $dateStr,
                    'classNames' => [$status],
                ];
            } elseif (isset($holidayDates[$dateStr])) {
                $holiday  = $holidayDates[$dateStr];
                $events[] = [
                    'title'      => $holiday->title,
                    'start'      => $dateStr,
                    'classNames' => ['holiday'],
                    'extendedProps' => [
                        'description'  => $holiday->description,
                        'holiday_type' => $holiday->holiday_type,
                        'is_optional'  => $holiday->is_optional,
                        'is_national'  => $holiday->is_national,
                    ],
                ];
            } elseif ($isSunday) {
                $events[] = [
                    'title'      => 'Week Off',
                    'start'      => $dateStr,
                    'classNames' => ['week_off'],
                ];
            } elseif ($dateStr <= $today) {
                $events[] = [
                    'title'      => 'Absent',
                    'start'      => $dateStr,
                    'classNames' => ['absent'],
                ];
            }
        }

        return response()->json($events);
    }

    public function summary(Request $request, PayrollCalculator $calculator)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        if (!auth()->user()->is_admin && auth()->id() !== (int) $validated['user_id']) {
            abort(403, 'Unauthorized access.');
        }

        $employee = Employee::where('user_id', $validated['user_id'])->firstOrFail();
        $calculation = $calculator->calculate(
            $employee,
            (int) $validated['month'],
            (int) $validated['year']
        );

        return response()->json([
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->full_name,
                'employee_code' => $employee->employee_code,
            ],
            'calculation' => $calculation,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | FETCH DETAIL (modal partial)
    |--------------------------------------------------------------------------
    */
    public function fetchDetail(Request $request)
    {
        $userId = $request->get('user_id');
        $date   = $request->get('date');

        $attendanceDetail = AttendanceDetail::where('user_id', $userId)
            ->whereDate('date', $date)
            ->first();

        $attendanceLog = AttendanceLog::where('user_id', $userId)
            ->whereDate('date', $date)
            ->first();

        $employee = Employee::where('user_id', $userId)
            ->select('work_start_time', 'work_end_time')
            ->first();

        $leaveRequest = LeaveRequest::where('user_id', $userId)
            ->whereDate('date_from', '<=', $date)
            ->whereDate('date_to', '>=', $date)
            ->first();

        $data = [
            'attendanceDetail'  => $attendanceDetail,
            'attendanceLog'     => $attendanceLog,
            'leaveRequest'      => $leaveRequest,
            'work_start_time'   => $employee?->work_start_time,
            'work_end_time'     => $employee?->work_end_time,
            'punchInLatitude'   => $attendanceDetail?->punch_in_latitude,
            'punchInLongitude'  => $attendanceDetail?->punch_in_longitude,
            'punchInLocation'   => $attendanceDetail?->punch_in_location,
            'punchOutLatitude'  => $attendanceDetail?->punch_out_latitude,
            'punchOutLongitude' => $attendanceDetail?->punch_out_longitude,
            'punchOutLocation'  => $attendanceDetail?->punch_out_location,
            'hasPunchIn'        => (bool) $attendanceDetail?->punch_in_time,
            'hasPunchOut'       => (bool) $attendanceDetail?->punch_out_time,
            'selectedDate'      => $date,
        ];

        return view('admin.attendanceDetails.partials.attendance_modal', $data);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        abort_if(Gate::denies('attendance_detail_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $authUser = auth()->user();
        $isAdmin  = $authUser->roles->contains('title', 'Admin');

        $users = $isAdmin
            ? User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '')
            : collect([$authUser->id => $authUser->name]);

        $today           = now()->format('Y-m-d');
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

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */
    public function store(StoreAttendanceDetailRequest $request)
    {
        $userId   = $request->input('user_id');
        $employee = Employee::where('user_id', $userId)->firstOrFail();
        $branch   = Branch::find($employee->branch_id);

        if ($branch && strtolower($branch->name) !== 'anywhere') {
            $allowedRadius = $employee->attendance_radius_meter ?? 0;
            if ($allowedRadius > 0 && $request->filled('punch_in_latitude') && $request->filled('punch_in_longitude')) {
                $distance = $this->calculateDistance(
                    $request->punch_in_latitude, $request->punch_in_longitude,
                    $branch->latitude, $branch->longitude
                );
                if ($distance > $allowedRadius) {
                    return back()->withErrors([
                        'location' => 'Punch-in location is out of allowed radius. You are ' . round($distance, 2) . ' meters away. Allowed: ' . $allowedRadius . 'm.',
                    ])->withInput();
                }
            }
        }

        if ($request->input('type') === 'self' && $request->filled('punch_in_time')) {
            $workStart    = Carbon::parse($employee->work_start_time);
            $punchIn      = Carbon::parse($request->punch_in_time);
            $delayAllowed = (int) $employee->delay_time;
            $status       = $punchIn->gt($workStart->copy()->addMinutes($delayAllowed)) ? 'half_time' : 'present';
            $request->merge(['status' => $status]);
        }

        $attendance = AttendanceDetail::firstOrNew([
            'user_id' => $userId,
            'date'    => $request->input('attendance_date', now()->format('Y-m-d')),
        ]);

        if ($request->has('punch_in_time')) {
            $attendance->fill($request->only([
                'punch_in_time', 'punch_in_latitude', 'punch_in_longitude', 'punch_in_location',
            ]));
            $attendance->status = $request->input('status');
            if ($request->hasFile('punch_in_image')) {
                $attendance->addMedia($request->file('punch_in_image'))->toMediaCollection('punch_in_image');
            }
        }

        if ($request->has('punch_out_time')) {
            $attendance->fill($request->only([
                'punch_out_time', 'punch_out_latitude', 'punch_out_longitude', 'punch_out_location',
            ]));
            if ($request->hasFile('punch_out_image')) {
                $attendance->addMedia($request->file('punch_out_image'))->toMediaCollection('punch_out_image');
            }
        }

        $attendance->user_id     = $userId;
        $attendance->employee_id = $employee->id;
        $attendance->date        = $request->input('attendance_date', now()->format('Y-m-d'));
        $attendance->type        = $request->input('type', 'manual');
        $attendance->save();

        if ($attendance->punch_in_time && $attendance->punch_out_time) {
            $this->updateAttendanceLog($attendance, $employee);
        }

        return redirect()->route('admin.attendance-details.index')->with('success', 'Attendance recorded successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
    public function edit(AttendanceDetail $attendanceDetail)
    {
        abort_if(Gate::denies('attendance_detail_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $users = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $attendanceDetail->load('user');
        return view('admin.attendanceDetails.edit', compact('attendanceDetail', 'users'));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
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

        return redirect()->route('admin.attendance-details.index');
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */
    public function show(AttendanceDetail $attendanceDetail)
    {
        abort_if(Gate::denies('attendance_detail_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $attendanceDetail->load('user');
        return view('admin.attendanceDetails.show', compact('attendanceDetail'));
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */
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

    /*
    |--------------------------------------------------------------------------
    | UPDATE STATUS (Admin modal action)
    |--------------------------------------------------------------------------
    */
    public function updateStatus(Request $request)
    {
        try {
            // ── 1. Validate ────────────────────────────────────────────────
            try {
                $validated = $request->validate([
                    'user_id'             => 'required|exists:users,id',
                    'date'                => 'required|date',
                    'status'              => 'required|in:present,absent,half_time,leave,week_off,holiday,paid_leave,late',
                    'punch_type'          => 'required|in:in,out,both,none',
                    'punch_in_time'       => 'nullable|date_format:H:i',
                    'punch_out_time'      => 'nullable|date_format:H:i',
                    'punch_in_latitude'   => 'nullable|numeric',
                    'punch_in_longitude'  => 'nullable|numeric',
                    'punch_out_latitude'  => 'nullable|numeric',
                    'punch_out_longitude' => 'nullable|numeric',
                    'punch_in_location'   => 'nullable|string|max:500',
                    'punch_out_location'  => 'nullable|string|max:500',
                    'master_password'     => 'required|string',
                    'changed_by'          => 'required|string',
                    'device_name'         => 'nullable|string',
                    'device_uid'          => 'nullable|string',
                    'punch_in_image'      => 'nullable|image',
                    'punch_out_image'     => 'nullable|image',
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json(['success' => false, 'message' => $e->errors()], 422);
            }

            // ── 2. Verify master password ──────────────────────────────────
            // Supports both 'master_password' and 'password' column names
            $admin         = auth()->user();
            $storedHash    = $admin->master_password ?? $admin->password ?? '';
            if (empty($storedHash) || !Hash::check($validated['master_password'], $storedHash)) {
                return response()->json(['success' => false, 'message' => 'Invalid master password'], 403);
            }

            // ── 3. Find employee (nullable — admin may have no employee row) ──
            $employee = Employee::where('user_id', $validated['user_id'])->first();

            // ── 4. Build / update attendance record ────────────────────────
            $attendance = AttendanceDetail::firstOrNew([
                'user_id' => $validated['user_id'],
                'date'    => $validated['date'],
            ]);

            $attendance->status      = $validated['status'];
            $attendance->changed_by  = $validated['changed_by'];
            $attendance->ip_address  = $request->ip();
            $attendance->device_name = $validated['device_name'] ?? $request->userAgent();

            // Only set employee_id when a matching employee row exists
            if ($employee) {
                $attendance->employee_id = $employee->id;
            }

            $punchType = $validated['punch_type']; // 'in' | 'out' | 'both' | 'none'

            // ─── Punch In ─────────────────────────────────────────────────
            if (in_array($punchType, ['in', 'both'])) {
                if (!empty($validated['punch_in_time'])) {
                    // Use admin-chosen time on the selected date
                    $attendance->punch_in_time = $validated['date'] . ' ' . $validated['punch_in_time'] . ':00';
                } elseif (empty($attendance->getOriginal('punch_in_time'))) {
                    $attendance->punch_in_time = now();
                }

                $attendance->punch_in_latitude  = $validated['punch_in_latitude']  ?? null;
                $attendance->punch_in_longitude = $validated['punch_in_longitude'] ?? null;
                $attendance->punch_in_location  = $validated['punch_in_location']  ?? null;
                $attendance->punch_type         = $punchType;
            }

            // ─── Punch Out ────────────────────────────────────────────────
            if (in_array($punchType, ['out', 'both'])) {
                // Require punch-in before punch-out
                $existingPunchIn = $attendance->getOriginal('punch_in_time') ?? $attendance->punch_in_time;
                if (empty($existingPunchIn)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Punch In is required before Punch Out',
                    ], 422);
                }

                if (!empty($validated['punch_out_time'])) {
                    $attendance->punch_out_time = $validated['date'] . ' ' . $validated['punch_out_time'] . ':00';
                } elseif (empty($attendance->getOriginal('punch_out_time'))) {
                    $attendance->punch_out_time = now();
                }

                $attendance->punch_out_latitude  = $validated['punch_out_latitude']  ?? null;
                $attendance->punch_out_longitude = $validated['punch_out_longitude'] ?? null;
                $attendance->punch_out_location  = $validated['punch_out_location']  ?? null;
                $attendance->punch_type          = $punchType;
            }

            // ─── Status-only (absent / leave / week_off / holiday / paid_leave) ─
            // punch_type stays as whatever was already on the record (or null)
            // — no DB constraint issue because we only override when punch data exists.

            $attendance->save();

            // Media
            if ($request->hasFile('punch_in_image')) {
                $attendance->clearMediaCollection('punch_in_image');
                $attendance->addMediaFromRequest('punch_in_image')->toMediaCollection('punch_in_image');
            }
            if ($request->hasFile('punch_out_image')) {
                $attendance->clearMediaCollection('punch_out_image');
                $attendance->addMediaFromRequest('punch_out_image')->toMediaCollection('punch_out_image');
            }

            // Attendance log (only when employee exists and both punches present)
            if ($employee && $attendance->punch_in_time && $attendance->punch_out_time) {
                $this->updateAttendanceLog($attendance, $employee);
            }

            return response()->json(['success' => true, 'message' => 'Attendance saved successfully']);

        } catch (\Exception $e) {
            \Log::error('updateStatus error: ' . $e->getMessage(), [
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'request' => $request->except(['master_password', 'punch_in_image', 'punch_out_image']),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CKEditor Image Upload
    |--------------------------------------------------------------------------
    */
    public function storeCKEditorImages(Request $request)
    {
        abort_if(Gate::denies('attendance_detail_create') && Gate::denies('attendance_detail_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $model         = new AttendanceDetail();
        $model->id     = $request->input('crud_id', 0);
        $model->exists = true;
        $media         = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');
        return response()->json(['id' => $media->id, 'url' => $media->getUrl()], Response::HTTP_CREATED);
    }

    /*
    |--------------------------------------------------------------------------
    | Private Helpers
    |--------------------------------------------------------------------------
    */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a    = sin($dLat / 2) ** 2
              + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function updateAttendanceLog(AttendanceDetail $attendance, Employee $employee): void
    {
        if (!$attendance->punch_in_time || !$attendance->punch_out_time) {
            return;
        }

        $workStart    = Carbon::parse($employee->work_start_time);
        $workEnd      = Carbon::parse($employee->work_end_time);
        $actualIn     = Carbon::parse($attendance->punch_in_time);
        $actualOut    = Carbon::parse($attendance->punch_out_time);
        $expectedMins = $workEnd->diffInMinutes($workStart);
        $actualMins   = $actualOut->diffInMinutes($actualIn);

        AttendanceLog::updateOrCreate(
            [
                'user_id'     => $attendance->user_id,
                'employee_id' => $employee->id,
                'date'        => $attendance->date,
            ],
            [
                'expected_in'           => $workStart->format('H:i:s'),
                'actual_in'             => $actualIn->format('H:i:s'),
                'late_by_minutes'       => $actualIn->gt($workStart) ? $actualIn->diffInMinutes($workStart) : 0,
                'expected_out'          => $workEnd->format('H:i:s'),
                'actual_out'            => $actualOut->format('H:i:s'),
                'left_early_by_minutes' => $actualOut->lt($workEnd) ? $workEnd->diffInMinutes($actualOut) : 0,
                'overtime_by_minutes'   => max(0, $actualMins - $expectedMins),
                'total_work_minutes'    => $actualMins,
            ]
        );
    }
}
