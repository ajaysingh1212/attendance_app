<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\StoreAttendanceDetailRequest;
use App\Http\Requests\UpdateAttendanceDetailRequest;
use App\Http\Resources\Admin\AttendanceDetailResource;
use App\Models\AttendanceDetail;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Carbon;

use App\Mail\AdminDailyAttendanceMail;

use App\Mail\AttendancePunchMail;
use App\Mail\UserMonthlyAttendanceMail;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

use Illuminate\Support\Facades\Log;

use Barryvdh\DomPDF\Facade\Pdf;




class AttendanceDetailApiController extends Controller
{
    use MediaUploadingTrait;

    public function index()
    {
        abort_if(Gate::denies('attendance_detail_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new AttendanceDetailResource(AttendanceDetail::with(['user'])->get());
    }

    public function store(StoreAttendanceDetailRequest $request)
    {
        $attendanceDetail = AttendanceDetail::create($request->all());

        if ($request->input('punch_in_image', false)) {
            $attendanceDetail->addMedia(storage_path('tmp/uploads/' . basename($request->input('punch_in_image'))))->toMediaCollection('punch_in_image');
        }

        if ($request->input('punch_out_image', false)) {
            $attendanceDetail->addMedia(storage_path('tmp/uploads/' . basename($request->input('punch_out_image'))))->toMediaCollection('punch_out_image');
        }

        return (new AttendanceDetailResource($attendanceDetail))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(AttendanceDetail $attendanceDetail)
    {
        abort_if(Gate::denies('attendance_detail_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new AttendanceDetailResource($attendanceDetail->load(['user']));
    }

    public function update(UpdateAttendanceDetailRequest $request, AttendanceDetail $attendanceDetail)
    {
        $attendanceDetail->update($request->all());

        if ($request->input('punch_in_image', false)) {
            if (! $attendanceDetail->punch_in_image || $request->input('punch_in_image') !== $attendanceDetail->punch_in_image->file_name) {
                if ($attendanceDetail->punch_in_image) {
                    $attendanceDetail->punch_in_image->delete();
                }
                $attendanceDetail->addMedia(storage_path('tmp/uploads/' . basename($request->input('punch_in_image'))))->toMediaCollection('punch_in_image');
            }
        } elseif ($attendanceDetail->punch_in_image) {
            $attendanceDetail->punch_in_image->delete();
        }

        if ($request->input('punch_out_image', false)) {
            if (! $attendanceDetail->punch_out_image || $request->input('punch_out_image') !== $attendanceDetail->punch_out_image->file_name) {
                if ($attendanceDetail->punch_out_image) {
                    $attendanceDetail->punch_out_image->delete();
                }
                $attendanceDetail->addMedia(storage_path('tmp/uploads/' . basename($request->input('punch_out_image'))))->toMediaCollection('punch_out_image');
            }
        } elseif ($attendanceDetail->punch_out_image) {
            $attendanceDetail->punch_out_image->delete();
        }

        return (new AttendanceDetailResource($attendanceDetail))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function destroy(AttendanceDetail $attendanceDetail)
    {
        abort_if(Gate::denies('attendance_detail_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $attendanceDetail->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
    
    public function punchAttendance(Request $request)
    {
        $request->validate([
            'user_id'    => 'required|exists:users,id',
            'latitude'   => 'nullable|string',
            'longitude'  => 'nullable|string',
            'location'   => 'nullable|string',
            'punch_image'=> 'nullable|file|image',
        ]);
    
        try {
            $user = User::find($request->user_id);
            $employee = \App\Models\Employee::where('user_id', $request->user_id)->first();
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ], 404);
            }
    
            $employee_id = $employee->id;
            $todayDate = now()->format('Y-m-d');
    
            // Check today's attendance
            $attendance = AttendanceDetail::where('user_id', $request->user_id)
                ->where('date', $todayDate)
                ->first();
    
            // CASE 1: No record yet → Punch In
            // Punch-In
            if (!$attendance) {
                $expectedStart = \Carbon\Carbon::parse($employee->work_start_time);
                $now = now();
                $lateMinutes = $now->gt($expectedStart) ? $expectedStart->diffInMinutes($now) : 0;
                $status = ($lateMinutes > $employee->delay_time) ? 'half_time' : 'present';
            
                $attendance = AttendanceDetail::create([
                    'user_id'            => $request->user_id,
                    'employee_id'        => $employee_id,
                    'punch_in_time'      => $now,
                    'punch_in_latitude'  => $request->latitude,
                    'punch_in_longitude' => $request->longitude,
                    'punch_in_location'  => $request->location,
                    'status'             => $status,
                    'type'               => 'self',
                    'date'               => $todayDate,
                ]);
            
                // Save punch_in image
                if ($request->hasFile('punch_image')) {
                    $attendance->addMedia($request->file('punch_image'))
                        ->toMediaCollection('punch_in_image');
                }
            
                // Attendance log with late_by_minutes also
                \App\Models\AttendanceLog::create([
                    'user_id'             => $request->user_id,
                    'employee_id'         => $employee_id,
                    'date'                => $todayDate,
                    'expected_in'         => $employee->work_start_time,
                    'expected_out'        => $employee->work_end_time,
                    'actual_in'           => $now->format('H:i:s'),
                    'late_by_minutes'     => $lateMinutes,  // <-- नया field डाल दिया
                    'total_work_minutes'  => 0,
                ]);
                
                // ✅ FAIL-SAFE MAIL AFTER PUNCH IN
                if ($user && $user->email) {
                    try {
                        Mail::to($user->email)->send(
                            new AttendancePunchMail(
                                $user,
                                'punch_in',
                                [
                                    'time'              => $now->format('d-m-Y H:i:s'),
                                    'ip'                => request()->ip(),
                                    'user_agent'        => request()->userAgent(),
                
                                    'latitude'          => $request->latitude,
                                    'longitude'         => $request->longitude,
                                    'location'          => $request->location,
                
                                    'expected_in'       => $employee->work_start_time,
                                    'actual_in'         => $now->format('H:i:s'),
                                    'late_by_minutes'   => $lateMinutes,
                
                                    'status'            => $status,
                                    'type'              => 'self',
                                ]
                            )
                        );
                    } catch (\Exception $e) {
                        \Log::error('Punch-in mail failed', [
                            'user_id' => $user->id,
                            'email'   => $user->email,
                            'error'   => $e->getMessage(),
                        ]);
                    }
                }
            
                return response()->json([
                    'success'    => true,
                    'message'    => 'Punch-in recorded successfully',
                    'attendance' => new AttendanceDetailResource($attendance)
                ], 200);
            }
    
            // CASE 2: Record exists but punch_out not done → Punch Out
            if ($attendance && !$attendance->punch_out_time) {
            
                // ✅ DEFAULT VALUES (VERY IMPORTANT)
                $lateBy = 0;
                $leftEarlyBy = 0;
                $overtime = 0;
            
                $attendance->update([
                    'punch_out_time'      => now(),
                    'punch_out_latitude'  => $request->latitude,
                    'punch_out_longitude' => $request->longitude,
                    'punch_out_location'  => $request->location,
                ]);
            
                // Save punch_out image
                if ($request->hasFile('punch_image')) {
                    $attendance->addMedia($request->file('punch_image'))
                        ->toMediaCollection('punch_out_image');
                }
            
                // ✅ FETCH LOG FIRST
                $attendanceLog = \App\Models\AttendanceLog::where('user_id', $request->user_id)
                    ->where('date', $todayDate)
                    ->first();
            
                // ✅ CALCULATIONS
                if ($attendanceLog && !$attendanceLog->actual_out) {
            
                    $actualIn   = \Carbon\Carbon::parse($attendanceLog->actual_in);
                    $actualOut  = now();
                    $expectedIn = \Carbon\Carbon::parse($attendanceLog->expected_in);
                    $expectedOut= \Carbon\Carbon::parse($attendanceLog->expected_out);
            
                    $lateBy = $actualIn->gt($expectedIn)
                        ? $actualIn->diffInMinutes($expectedIn)
                        : 0;
            
                    $leftEarlyBy = $actualOut->lt($expectedOut)
                        ? $expectedOut->diffInMinutes($actualOut)
                        : 0;
            
                    $totalWork = $actualIn->diffInMinutes($actualOut);
                    $expectedWorkMinutes = $expectedIn->diffInMinutes($expectedOut);
            
                    $overtime = $totalWork > $expectedWorkMinutes
                        ? $totalWork - $expectedWorkMinutes
                        : 0;
            
                    $attendanceLog->update([
                        'actual_out'            => $actualOut->format('H:i:s'),
                        'late_by_minutes'       => $lateBy,
                        'left_early_by_minutes' => $leftEarlyBy,
                        'overtime_by_minutes'   => $overtime,
                        'total_work_minutes'    => $totalWork,
                    ]);
                }
            
                // ✅ MAIL AFTER EVERYTHING IS READY
                if ($user && $user->email) {
                    try {
                        Mail::to($user->email)->send(
                            new AttendancePunchMail(
                                $user,
                                'punch_out',
                                [
                                    'time'              => now()->format('d-m-Y H:i:s'),
                                    'ip'                => request()->ip(),
                                    'user_agent'        => request()->userAgent(),
            
                                    'latitude'          => $request->latitude,
                                    'longitude'         => $request->longitude,
                                    'location'          => $request->location,
            
                                    'expected_out'      => $attendanceLog->expected_out ?? null,
                                    'actual_out'        => now()->format('H:i:s'),
            
                                    'late_by_minutes'   => $lateBy,
                                    'left_early_by'     => $leftEarlyBy,
                                    'overtime'          => $overtime,
            
                                    'status'            => $attendance->status,
                                    'type'              => 'self',
                                ]
                            )
                        );
                    } catch (\Exception $e) {
                        \Log::error('Punch-out mail failed', [
                            'user_id' => $user->id,
                            'email'   => $user->email,
                            'error'   => $e->getMessage(),
                        ]);
                    }
                }
            
                return response()->json([
                    'success'    => true,
                    'message'    => 'Punch-out recorded successfully',
                    'attendance' => new AttendanceDetailResource($attendance)
                ], 200);
            }

    
            // CASE 3: Already punched in and out → No more punches allowed
            return response()->json([
                'success' => false,
                'message' => 'You have already completed today\'s attendance'
            ], 400);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error while saving attendance',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    
    
    
public function manualAttendance(Request $request)
{
    $request->validate([
        'user_id'   => 'required|exists:users,id',
        'date'      => 'required|date',
        'action'    => 'required|in:in,out',
        'time'      => 'nullable|date_format:H:i',
        'latitude'  => 'nullable|string',
        'longitude' => 'nullable|string',
        'location'  => 'nullable|string',
        'image'     => 'nullable|file|image',
    ]);

    // ✅ Only user 1 and 19 allowed
    $allowedUserIds = [47, 19, 10, 9];

    if (!in_array((int)$request->user_id, $allowedUserIds)) {
        return response()->json([
            'success' => false,
            'message' => 'Manual attendance is allowed only for user IDs 10 and 19.'
        ], 403);
    }

    try {

        $employee = \App\Models\Employee::where('user_id', $request->user_id)->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found'
            ], 404);
        }

        $date = \Carbon\Carbon::parse($request->date)->format('Y-m-d');

        // ✅ Use requested time, otherwise current time
        $timeToUse = $request->filled('time')
            ? $request->time
            : \Carbon\Carbon::now()->format('H:i');

        // Existing attendance
        $attendance = \App\Models\AttendanceDetail::where('user_id', $request->user_id)
            ->where('date', $date)
            ->first();

        // Punch In/Out
        $punchIn = $attendance && $attendance->punch_in_time
            ? \Carbon\Carbon::parse($attendance->punch_in_time)
            : null;

        $punchOut = $attendance && $attendance->punch_out_time
            ? \Carbon\Carbon::parse($attendance->punch_out_time)
            : null;

        if ($request->action === 'in') {
            $punchIn = \Carbon\Carbon::parse($date . ' ' . $timeToUse);
        }

        if ($request->action === 'out') {
            $punchOut = \Carbon\Carbon::parse($date . ' ' . $timeToUse);
        }

        // Expected times
        $expectedIn = \Carbon\Carbon::parse($date . ' ' . $employee->work_start_time);
        $expectedOut = \Carbon\Carbon::parse($date . ' ' . $employee->work_end_time);

        // Status
        $status = 'absent';

        if ($punchIn) {
            $lateMinutes = $punchIn->gt($expectedIn)
                ? $expectedIn->diffInMinutes($punchIn)
                : 0;

            $status = ($lateMinutes > $employee->delay_time)
                ? 'half_time'
                : 'present';
        }

        // Calculations
        $lateBy = ($punchIn && $punchIn->gt($expectedIn))
            ? $punchIn->diffInMinutes($expectedIn)
            : 0;

        $leftEarly = ($punchOut && $punchOut->lt($expectedOut))
            ? $expectedOut->diffInMinutes($punchOut)
            : 0;

        $totalWork = ($punchIn && $punchOut)
            ? $punchIn->diffInMinutes($punchOut)
            : 0;

        $expectedWork = $expectedIn->diffInMinutes($expectedOut);

        $overtime = ($totalWork > $expectedWork)
            ? $totalWork - $expectedWork
            : 0;

        // Save Attendance
        if (!$attendance) {

            $data = [
                'user_id'     => $request->user_id,
                'employee_id' => $employee->id,
                'status'      => $status,
                'type'        => 'self',
                'date'        => $date,
            ];

            if ($request->action === 'in') {
                $data['punch_in_time']      = $punchIn;
                $data['punch_in_latitude']  = $request->latitude;
                $data['punch_in_longitude'] = $request->longitude;
                $data['punch_in_location']  = $request->location;
            }

            if ($request->action === 'out') {
                $data['punch_out_time']      = $punchOut;
                $data['punch_out_latitude']  = $request->latitude;
                $data['punch_out_longitude'] = $request->longitude;
                $data['punch_out_location']  = $request->location;
            }

            $attendance = \App\Models\AttendanceDetail::create($data);

        } else {

            $data = [
                'status' => $status,
                'type'   => 'self',
            ];

            if ($request->action === 'in') {
                $data['punch_in_time']      = $punchIn;
                $data['punch_in_latitude']  = $request->latitude;
                $data['punch_in_longitude'] = $request->longitude;
                $data['punch_in_location']  = $request->location;
            }

            if ($request->action === 'out') {
                $data['punch_out_time']      = $punchOut;
                $data['punch_out_latitude']  = $request->latitude;
                $data['punch_out_longitude'] = $request->longitude;
                $data['punch_out_location']  = $request->location;
            }

            $attendance->update($data);
        }

        // Image
        if ($request->hasFile('image')) {

            if ($request->action === 'in') {
                $attendance->clearMediaCollection('punch_in_image');

                $attendance->addMedia($request->file('image'))
                    ->toMediaCollection('punch_in_image');
            }

            if ($request->action === 'out') {
                $attendance->clearMediaCollection('punch_out_image');

                $attendance->addMedia($request->file('image'))
                    ->toMediaCollection('punch_out_image');
            }
        }

        // Attendance Log
        \App\Models\AttendanceLog::updateOrCreate(
            [
                'user_id' => $request->user_id,
                'date'    => $date
            ],
            [
                'employee_id'           => $employee->id,
                'expected_in'           => $employee->work_start_time,
                'expected_out'          => $employee->work_end_time,
                'actual_in'             => $punchIn ? $punchIn->format('H:i:s') : null,
                'actual_out'            => $punchOut ? $punchOut->format('H:i:s') : null,
                'late_by_minutes'       => $lateBy,
                'left_early_by_minutes' => $leftEarly,
                'overtime_by_minutes'   => $overtime,
                'total_work_minutes'    => $totalWork,
            ]
        );

        return response()->json([
            'success'    => true,
            'message'    => 'Manual attendance saved successfully',
            'attendance' => new \App\Http\Resources\Admin\AttendanceDetailResource($attendance)
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => 'Error while saving manual attendance',
            'error'   => $e->getMessage()
        ], 500);
    }
}






    public function todayAttendance($userId)
    {
        try {
            $todayDate = now()->format('Y-m-d');
    
            // ✅ AttendanceDetail fetch karo
            $attendance = AttendanceDetail::with(['user'])
                ->where('user_id', $userId)
                ->where('date', $todayDate)
                ->first();
    
            // ✅ AttendanceLog fetch karo
            $attendanceLog = \App\Models\AttendanceLog::where('user_id', $userId)
                ->where('date', $todayDate)
                ->first();
    
            if (!$attendance && !$attendanceLog) {
                return response()->json([
                    'success' => false,
                    'message' => 'No attendance record found for today',
                ], 404);
            }
    
            return response()->json([
                'success'        => true,
                'message'        => 'Today\'s attendance fetched successfully',
                'attendance'     => $attendance ? new AttendanceDetailResource($attendance) : null,
                'attendance_log' => $attendanceLog ? $attendanceLog : null,
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching attendance',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    public function attendanceReport($userId)
    {
        try {
            // Attendance Details
            $attendanceDetails = \App\Models\AttendanceDetail::where('user_id', $userId)
                ->orderBy('date', 'desc')
                ->get();
    
            // Attendance Logs
            $attendanceLogs = \App\Models\AttendanceLog::where('user_id', $userId)
                ->orderBy('date', 'desc')
                ->get();
    
            // Merge details + logs into clean response
            $report = $attendanceDetails->map(function ($detail) use ($attendanceLogs) {
                $log = $attendanceLogs->firstWhere('date', $detail->date);
    
                return [
                    'date'       => $detail->date,
                    'status'     => $detail->status,
                    'type'       => $detail->type,
    
                    // Punch times
                    'punch_in_time'  => $detail->punch_in_time 
                        ? Carbon::parse($detail->punch_in_time)->format('Y-m-d H:i:s') 
                        : null,
                    'punch_out_time' => $detail->punch_out_time 
                        ? Carbon::parse($detail->punch_out_time)->format('Y-m-d H:i:s') 
                        : null,
    
                    // Punch locations
                    'punch_in_location'  => $detail->punch_in_location,
                    'punch_out_location' => $detail->punch_out_location,
                    'punch_in_latitude'  => $detail->punch_in_latitude,
                    'punch_in_longitude' => $detail->punch_in_longitude,
                    'punch_out_latitude' => $detail->punch_out_latitude,
                    'punch_out_longitude'=> $detail->punch_out_longitude,
    
                    // Expected / actual from logs
                    'expected_in'  => $log->expected_in ?? null,
                    'actual_in'    => $log && $log->actual_in
                        ? Carbon::parse($log->actual_in)->format('Y-m-d H:i:s')
                        : null,
                    'expected_out' => $log->expected_out ?? null,
                    'actual_out'   => $log && $log->actual_out
                        ? Carbon::parse($log->actual_out)->format('Y-m-d H:i:s')
                        : null,
    
                    // Other calculated fields
                    'late_by_minutes'       => $log->late_by_minutes ?? null,
                    'left_early_by_minutes' => $log->left_early_by_minutes ?? null,
                    'overtime_by_minutes'   => $log->overtime_by_minutes ?? null,
                    'total_work_minutes'    => $log->total_work_minutes ?? null,
                ];
            });
    
            return response()->json([
                'success' => true,
                'message' => 'Attendance report fetched successfully',
                'report'  => $report,
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching attendance report',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    
    
    public function fixEmployeeIds(Request $request)
{
    try {

        $records = \App\Models\AttendanceDetail::get();

        $updated = 0;
        $skipped = 0;

        foreach($records as $row){

            $employee = \App\Models\Employee::where('user_id', $row->user_id)->first();

            if($employee){
                // update only if not same already
                if($row->employee_id != $employee->id){
                    $row->employee_id = $employee->id;
                    $row->save();
                    $updated++;
                } else {
                    $skipped++;
                }
            } else {
                $skipped++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Fix complete",
            'updated_records' => $updated,
            'skipped_records' => $skipped,
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => 'Failed',
            'error' => $e->getMessage()
        ], 500);
    }
}


public function sendDailyAttendanceReport(Request $request)
{
    Log::info('sendDailyAttendanceReport API HIT', [
        'payload' => $request->all(),
        'ip'      => $request->ip(),
    ]);

    $request->validate([
        'date'     => 'required|date',
        'email'    => 'required|email',
        'password' => 'required|string',
    ]);

    if ($request->password !== 'ADMIN@EEMOT#2026') {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized request',
        ], 401);
    }

    try {
        $date = Carbon::parse($request->date)->format('Y-m-d');

        $users = User::whereNull('deleted_at')->get();

        $rows = [];

        // 🔢 Counters
        $totalEmployees = $users->count();
        $present = $absent = $half = $punchIn = $punchOut = 0;

        foreach ($users as $user) {

            $attendance = AttendanceDetail::where('user_id', $user->id)
                ->where('date', $date)
                ->first();

            if ($attendance) {

                $status = $attendance->status;

                if ($status === 'present') $present++;
                if ($status === 'half_time') $half++;

                if ($attendance->punch_in_time) $punchIn++;
                if ($attendance->punch_out_time) $punchOut++;

                $rows[] = [
                    'name'      => $user->name,
                    'email'     => $user->email,
                    'number'    => $user->number ?? '-',
                    'status'    => $status,
                    'punch_in'  => $attendance->punch_in_time
                        ? Carbon::parse($attendance->punch_in_time)->format('H:i')
                        : '-',
                    'punch_out' => $attendance->punch_out_time
                        ? Carbon::parse($attendance->punch_out_time)->format('H:i')
                        : '-',
                    'latitude'  => $attendance->punch_in_latitude ?? '-',
                    'longitude' => $attendance->punch_in_longitude ?? '-',
                    'location'  => $attendance->punch_in_location ?? '-',
                ];
            } else {
                $absent++;

                $rows[] = [
                    'name'      => $user->name,
                    'email'     => $user->email,
                    'number'    => $user->number ?? '-',
                    'status'    => 'absent',
                    'punch_in'  => '-',
                    'punch_out' => '-',
                    'latitude'  => '-',
                    'longitude' => '-',
                    'location'  => '-',
                ];
            }
        }

        $summary = [
            'total'     => $totalEmployees,
            'present'   => $present,
            'absent'    => $absent,
            'half'      => $half,
            'punch_in'  => $punchIn,
            'punch_out' => $punchOut,
        ];

        Mail::to($request->email)->send(
            new AdminDailyAttendanceMail($date, $rows, $summary)
        );

        return response()->json([
            'success' => true,
            'message' => 'Daily attendance report sent successfully',
        ]);

    } catch (\Exception $e) {

        Log::error('Daily attendance report failed', [
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to send report',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

public function sendMonthlyAttendanceReport(Request $request)
{
    $request->validate([
        'user_id'  => 'required|integer',
        'month'    => 'required|integer|min:1|max:12',
        'year'     => 'required|integer',
        'email'    => 'required|email',
        'password' => 'required|string',
    ]);

    if ($request->password !== 'ADMIN@EEMOT#2026') {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $user = User::findOrFail($request->user_id);

    $month = $request->month;
    $year  = $request->year;

    $monthName = Carbon::create($year, $month)->format('F');

    // 📅 Month start & end
    $startDate = Carbon::create($year, $month, 1);
    $endDate   = $startDate->copy()->endOfMonth();

    // 📌 Fetch all attendance of user for that month (indexed by date)
    $attendanceMap = AttendanceDetail::where('user_id', $user->id)
        ->whereMonth('date', $month)
        ->whereYear('date', $year)
        ->get()
        ->keyBy('date');

    $rows = [];

    // 🔁 LOOP THROUGH EVERY DAY OF MONTH
    for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {

        $dateStr = $date->format('Y-m-d');

        // 🟡 SUNDAY = WEEK OFF
        if ($date->isSunday()) {

            $rows[] = [
                'date' => $date->format('d-m-Y'),
                'status' => 'week_off',
                'status_class' => 'half', // yellow shade
                'punch_in' => '-',
                'punch_out' => '-',
                'latitude' => '-',
                'longitude' => '-',
                'location' => 'Sunday',
            ];

            continue;
        }

        // ✅ Attendance exists
        if ($attendanceMap->has($dateStr)) {

            $att = $attendanceMap[$dateStr];

            $rows[] = [
                'date' => $date->format('d-m-Y'),
                'status' => $att->status,
                'status_class' =>
                    $att->status === 'present'
                        ? 'present'
                        : ($att->status === 'half_time' ? 'half' : 'absent'),
                'punch_in' => $att->punch_in_time
                    ? Carbon::parse($att->punch_in_time)->format('H:i')
                    : '-',
                'punch_out' => $att->punch_out_time
                    ? Carbon::parse($att->punch_out_time)->format('H:i')
                    : '-',
                'latitude' => $att->punch_in_latitude ?? '-',
                'longitude' => $att->punch_in_longitude ?? '-',
                'location' => $att->punch_in_location ?? '-',
            ];

        } else {
            // ❌ ABSENT
            $rows[] = [
                'date' => $date->format('d-m-Y'),
                'status' => 'absent',
                'status_class' => 'absent',
                'punch_in' => '-',
                'punch_out' => '-',
                'latitude' => '-',
                'longitude' => '-',
                'location' => '-',
            ];
        }
    }

    // 📧 SEND MAIL + PDF
    Mail::to($request->email)->send(
        new UserMonthlyAttendanceMail($user, $rows, $monthName, $year)
    );

    return response()->json([
        'success' => true,
        'message' => 'Monthly attendance report sent successfully',
    ]);
}



public function downloadDailyAttendancePdf(Request $request)
{
    $request->validate([
        'date' => 'required|date',
        'password' => 'required|string',
    ]);

    if ($request->password !== 'ADMIN@EEMOT#2026') {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $date = Carbon::parse($request->date)->format('Y-m-d');

    $users = User::whereNull('deleted_at')->get();

    $rows = [];
    $present = $absent = $half = $punchIn = $punchOut = 0;

    foreach ($users as $user) {
        $attendance = AttendanceDetail::where('user_id', $user->id)
            ->where('date', $date)
            ->first();

        if ($attendance) {
            if ($attendance->status === 'present') $present++;
            if ($attendance->status === 'half_time') $half++;
            if ($attendance->punch_in_time) $punchIn++;
            if ($attendance->punch_out_time) $punchOut++;

            $rows[] = [
                'name' => $user->name,
                'email' => $user->email,
                'number' => $user->number ?? '-',
                'status' => $attendance->status,
                'punch_in' => $attendance->punch_in_time ? Carbon::parse($attendance->punch_in_time)->format('H:i') : '-',
                'punch_out' => $attendance->punch_out_time ? Carbon::parse($attendance->punch_out_time)->format('H:i') : '-',
                'latitude' => $attendance->punch_in_latitude ?? '-',
                'longitude' => $attendance->punch_in_longitude ?? '-',
                'location' => $attendance->punch_in_location ?? '-',
            ];
        } else {
            $absent++;
            $rows[] = [
                'name' => $user->name,
                'email' => $user->email,
                'number' => $user->number ?? '-',
                'status' => 'absent',
                'punch_in' => '-',
                'punch_out' => '-',
                'latitude' => '-',
                'longitude' => '-',
                'location' => '-',
            ];
        }
    }

    $summary = [
        'total' => $users->count(),
        'present' => $present,
        'absent' => $absent,
        'half' => $half,
        'punch_in' => $punchIn,
        'punch_out' => $punchOut,
    ];

    $pdf = Pdf::loadView('pdfs.admin_daily_attendance', compact('date', 'rows', 'summary'))
              ->setPaper('A4', 'landscape');

    return $pdf->download("attendance-report-{$date}.pdf");
}


public function attendanceImages($userId)
{
    try {
        $user = \App\Models\User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $images = [];

        $records = \App\Models\AttendanceDetail::where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->get();

        foreach ($records as $row) {
            $inImage  = $row->getMedia('punch_in_image')->first();
            $outImage = $row->getMedia('punch_out_image')->first();

            $images[] = [
                'date'      => $row->date,
                'punch_in'  => $row->punch_in_time,
                'in_image'  => $inImage ? $inImage->getUrl() : null,
                'punch_out' => $row->punch_out_time,
                'out_image' => $outImage ? $outImage->getUrl() : null,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Attendance images fetched successfully',
            'user' => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'number' => $user->number ?? '-',
            ],
            'data'    => $images,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fetching images',
            'error'   => $e->getMessage()
        ], 500);
    }
}

  


}
