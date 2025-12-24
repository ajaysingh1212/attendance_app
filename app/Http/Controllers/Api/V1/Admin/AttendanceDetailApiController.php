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
            
                return response()->json([
                    'success'    => true,
                    'message'    => 'Punch-in recorded successfully',
                    'attendance' => new AttendanceDetailResource($attendance)
                ], 200);
            }
    
    
            // CASE 2: Record exists but punch_out not done → Punch Out
            if ($attendance && !$attendance->punch_out_time) {
                $attendance->update([
                    'punch_out_time'     => now(),
                    'punch_out_latitude' => $request->latitude,
                    'punch_out_longitude'=> $request->longitude,
                    'punch_out_location' => $request->location,
                ]);
    
                // Save punch_out image
                if ($request->hasFile('punch_image')) {
                    $attendance->addMedia($request->file('punch_image'))
                        ->toMediaCollection('punch_out_image');
                }
    
                $attendanceLog = \App\Models\AttendanceLog::where('user_id', $request->user_id)
                    ->where('date', $todayDate)
                    ->first();
    
                if ($attendanceLog && !$attendanceLog->actual_out) {
                    $actualIn  = \Carbon\Carbon::parse($attendanceLog->actual_in);
                    $actualOut = now();
                    $expectedIn = \Carbon\Carbon::parse($attendanceLog->expected_in);
                    $expectedOut = \Carbon\Carbon::parse($attendanceLog->expected_out);
    
                    $lateBy = $actualIn->gt($expectedIn) ? $actualIn->diffInMinutes($expectedIn) : 0;
                    $leftEarlyBy = $actualOut->lt($expectedOut) ? $expectedOut->diffInMinutes($actualOut) : 0;
                    $totalWork = $actualIn->diffInMinutes($actualOut);
                    $expectedWorkMinutes = $expectedIn->diffInMinutes($expectedOut);
                    $overtime = $totalWork > $expectedWorkMinutes ? $totalWork - $expectedWorkMinutes : 0;
    
                    $attendanceLog->update([
                        'actual_out'            => $actualOut->format('H:i:s'),
                        'late_by_minutes'       => $lateBy,
                        'left_early_by_minutes' => $leftEarlyBy,
                        'overtime_by_minutes'   => $overtime,
                        'total_work_minutes'    => $totalWork,
                    ]);
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














    
    
  


}
