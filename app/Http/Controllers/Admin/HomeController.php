<?php

namespace App\Http\Controllers\Admin;

use App\Models\Employee;
use App\Models\AttendanceDetail;
use App\Models\LeaveRequest;
use App\Models\EmployeeStatusLog;
use App\Models\GroupTask;
use App\Models\TaskGroup;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HomeController
{
    /** Status values that hide an employee from the main dashboard */
    private const INACTIVE = ['Resigned', 'Terminated', 'Suspended'];

    public function index(Request $request)
    {
        /* ══════════════════════════════════════════════
           🎉 CELEBRATIONS
        ══════════════════════════════════════════════ */

        $today = Carbon::today();

        $birthdayEmployees = Employee::whereMonth(
                'date_of_birth',
                $today->month
            )
            ->whereDay(
                'date_of_birth',
                $today->day
            )
            ->get();

        $anniversaryEmployees = Employee::whereMonth(
                'anniversary_date',
                $today->month
            )
            ->whereDay(
                'anniversary_date',
                $today->day
            )
            ->get();

        $user = Auth::user();

        $isAdmin = $user && $user->is_admin == 1;

        /* ══════════════════════════════════════════════
           FILTERS
        ══════════════════════════════════════════════ */

        $filter       = $request->input('filter', 'today');
        $customFrom   = $request->input('from');
        $customTo     = $request->input('to');
        $statusFilter = $request->input('status');

        /* ══════════════════════════════════════════════
           DATE RANGE
        ══════════════════════════════════════════════ */

        switch ($filter) {

            case 'yesterday':

                $startDate = Carbon::yesterday()->startOfDay();
                $endDate   = Carbon::yesterday()->endOfDay();

                break;

            case 'week':

                $startDate = Carbon::now()->startOfWeek();
                $endDate   = Carbon::now()->endOfWeek();

                break;

            case 'halfmonth':

                $day        = Carbon::now()->day;
                $monthStart = Carbon::now()->startOfMonth();
                $monthMid   = Carbon::now()->startOfMonth()->addDays(14);
                $monthEnd   = Carbon::now()->endOfMonth();

                if ($day <= 15) {

                    $startDate = $monthStart;
                    $endDate   = $monthMid;

                } else {

                    $startDate = $monthMid->addDay();
                    $endDate   = $monthEnd;
                }

                break;

            case 'month':

                $startDate = Carbon::now()->startOfMonth();
                $endDate   = Carbon::now()->endOfMonth();

                break;

            case 'custom':

                $startDate = $customFrom
                    ? Carbon::parse($customFrom)->startOfDay()
                    : Carbon::today()->startOfDay();

                $endDate = $customTo
                    ? Carbon::parse($customTo)->endOfDay()
                    : Carbon::today()->endOfDay();

                break;

            default:

                $startDate = Carbon::today()->startOfDay();
                $endDate   = Carbon::today()->endOfDay();
        }

        /* ══════════════════════════════════════════════
           EMPLOYEES
        ══════════════════════════════════════════════ */

        $employeesQuery = Employee::query();

        // Non-admin → only own employee data
        if (!$isAdmin && $user) {

            $employeesQuery->where('user_id', $user->id);
        }

        $employees = $employeesQuery
            ->where(function ($q) {

                $q->whereNotIn('status', self::INACTIVE)
                  ->orWhereNull('status');
            })
            ->get();

        /* ══════════════════════════════════════════════
           INACTIVE EMPLOYEES
        ══════════════════════════════════════════════ */

        $inactiveEmployees = collect();

        if ($isAdmin) {

            $inactiveEmployees = Employee::with([
                    'statusLogs' => function ($q) {

                        $q->with(
                            'changedBy',
                            'approvedBy',
                            'reactivatedBy'
                        )->latest();
                    }
                ])
                ->whereIn('status', self::INACTIVE)
                ->get();
        }

        /* ══════════════════════════════════════════════
           ATTENDANCE
        ══════════════════════════════════════════════ */

        $attendances = AttendanceDetail::whereBetween(
                'punch_in_time',
                [$startDate, $endDate]
            )
            ->get();

        /* ══════════════════════════════════════════════
           LEAVES
        ══════════════════════════════════════════════ */

        $leaves = LeaveRequest::where('status', 'approved')
            ->where(function ($q) use ($startDate, $endDate) {

                $q->whereBetween('date_from', [$startDate, $endDate])

                  ->orWhereBetween('date_to', [$startDate, $endDate])

                  ->orWhere(function ($q2) use ($startDate, $endDate) {

                      $q2->where('date_from', '<', $startDate)
                         ->where('date_to', '>', $endDate);
                  });
            })
            ->get();

        /* ══════════════════════════════════════════════
           ATTENDANCE DETAILS
        ══════════════════════════════════════════════ */

        $attendanceDetails = $employees->map(function (
            $employee
        ) use (
            $attendances,
            $leaves,
            $statusFilter
        ) {

            $attendance = $attendances->firstWhere(
                'user_id',
                $employee->user_id
            );

            $leave = $leaves->firstWhere(
                'user_id',
                $employee->user_id
            );

            /* PRESENT */

            if ($attendance) {

                if (
                    $statusFilter &&
                    $attendance->status != $statusFilter
                ) {
                    return null;
                }

                return (object) [

                    'user'               => $employee,

                    'punch_in_time'      => $attendance->punch_in_time,

                    'punch_out_time'     => $attendance->punch_out_time,

                    'punch_in_image'     => $attendance->punch_in_image,

                    'punch_out_image'    => $attendance->punch_out_image,

                    'punch_in_location'  => $attendance->punch_in_location,

                    'punch_out_location' => $attendance->punch_out_location,

                    'status'             => $attendance->status,
                ];
            }

            /* LEAVE */

            if ($leave) {

                if (
                    $statusFilter &&
                    $statusFilter != 'leave'
                ) {
                    return null;
                }

                return (object) [

                    'user'         => $employee,

                    'status'       => 'leave',

                    'leave_detail' => $leave,
                ];
            }

            /* ABSENT */

            if (
                $statusFilter &&
                $statusFilter != 'absent'
            ) {
                return null;
            }

            return (object) [

                'user'   => $employee,

                'status' => 'absent',
            ];

        })->filter();

        /* ══════════════════════════════════════════════
           TOTALS
        ══════════════════════════════════════════════ */

        $totalEmployees = $employees->count();

        $totalPunchIn = $attendanceDetails
            ->whereNotNull('punch_in_time')
            ->count();

        $totalPunchOut = $attendanceDetails
            ->whereNotNull('punch_out_time')
            ->count();

        $totalPresent = $attendanceDetails
            ->where('status', 'present')
            ->count();

        $totalAbsent = $attendanceDetails
            ->where('status', 'absent')
            ->count();

        $totalHalf = $attendanceDetails
            ->where('status', 'half_time')
            ->count();

        $totalLeave = $attendanceDetails
            ->where('status', 'leave')
            ->count();

        $absentEmployees = $attendanceDetails
            ->where('status', 'absent')
            ->map(function ($att) {

                return $att->user->full_name
                    ?? $att->user->name;
            });

        /* ══════════════════════════════════════════════
           GROUP TASK DASHBOARD
        ══════════════════════════════════════════════ */

        $taskDashboardQuery = GroupTask::query()
            ->with([
                'group',
                'createdBy',
                'assignees',
                'acceptedBy',
                'completedBy'
            ]);

        // NON ADMIN → only related tasks
        if (!$isAdmin && $user) {

            $taskDashboardQuery->where(function ($q) use ($user) {

                $q->where('created_by_id', $user->id)

                  ->orWhere('accepted_by_id', $user->id)

                  ->orWhere('completed_by_id', $user->id)

                  ->orWhereHas('assignees', function ($a) use ($user) {

                      $a->where('users.id', $user->id);
                  })

                  ->orWhereHas('group.members', function ($g) use ($user) {

                      $g->where('users.id', $user->id);
                  });
            });
        }

        $taskDashboardTasks = (clone $taskDashboardQuery)
            ->latest()
            ->take(20)
            ->get();

        $taskDashboardGroupsQuery = TaskGroup::withCount(['members', 'tasks'])
            ->with(['members' => function ($q) {
                $q->activeEmployees()->select('users.id', 'users.name');
            }])
            ->where('is_active', true);

        if (!$isAdmin && $user) {
            $taskDashboardGroupsQuery->whereHas('members', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        $taskDashboardGroups = $taskDashboardGroupsQuery
            ->latest()
            ->take(12)
            ->get();

        $taskSummary = [

            'total' => (clone $taskDashboardQuery)
                ->count(),

            'pending' => (clone $taskDashboardQuery)
                ->where('status', 'pending')
                ->count(),

            'accepted' => (clone $taskDashboardQuery)
                ->where('status', 'accepted')
                ->count(),

            'completed' => (clone $taskDashboardQuery)
                ->where('status', 'completed')
                ->count(),

            'delayed' => (clone $taskDashboardQuery)
                ->where('status', 'accepted')
                ->get()
                ->filter
                ->is_delayed
                ->count(),
        ];

        /* ══════════════════════════════════════════════
           RETURN VIEW
        ══════════════════════════════════════════════ */

        return view('home', compact(

            'totalEmployees',

            'totalPunchIn',

            'totalPunchOut',

            'attendanceDetails',

            'filter',

            'customFrom',

            'customTo',

            'statusFilter',

            'totalPresent',

            'totalAbsent',

            'totalHalf',

            'totalLeave',

            'absentEmployees',

            'inactiveEmployees',

            'birthdayEmployees',

            'anniversaryEmployees',

            'taskDashboardTasks',

            'taskSummary',

            'taskDashboardGroups'
        ));
    }

    /* ══════════════════════════════════════════════
       AJAX — Approve a status change
    ══════════════════════════════════════════════ */

    public function approveStatus(
        Request $request,
        Employee $employee
    ) {

        $user = auth()->user();

        // Permission check
        if (!$user->can('approve employee status')) {

            return response()->json([

                'success' => false,

                'message' =>
                    'You do not have permission to approve.'

            ], 403);
        }

        $log = EmployeeStatusLog::where(
                'employee_id',
                $employee->id
            )
            ->whereNull('approved_by')
            ->latest()
            ->first();

        if (!$log) {

            return response()->json([

                'success' => false,

                'message' => 'No pending status found.'
            ]);
        }

        $log->update([

            'approved_by' => $user->id,

            'approved_at' => now(),
        ]);

        $employee->update([

            'status_change_pending' => false
        ]);

        return response()->json([

            'success' => true,

            'message' =>
                'Status approved successfully.'
        ]);
    }

    /* ══════════════════════════════════════════════
       AJAX — Reactivate an employee
    ══════════════════════════════════════════════ */

    public function reactivateEmployee(
        Request $request,
        Employee $employee
    ) {

        $oldStatus = $employee->status;

        $employee->update([

            'status' => 'Active',

            'status_change_pending' => false,
        ]);

        EmployeeStatusLog::create([

            'employee_id' => $employee->id,

            'old_status' => $oldStatus,

            'new_status' => 'Active',

            'changed_by' => auth()->id(),

            'reactivated_by' => auth()->id(),

            'remarks' => $request->input(
                'remarks',
                'Reactivated by admin.'
            ),

            'changed_at' => now(),

            'reactivated_at' => now(),
        ]);

        return response()->json([

            'success' => true,

            'message' =>
                "Employee {$employee->full_name} reactivated successfully.",
        ]);
    }

    /* ══════════════════════════════════════════════
       AJAX — Inactive employees list
    ══════════════════════════════════════════════ */

    public function inactiveList()
    {
        $employees = Employee::with([

                'statusLogs' => function ($q) {

                    $q->with(
                        'changedBy',
                        'approvedBy',
                        'reactivatedBy'
                    )->latest();
                }
            ])
            ->whereIn('status', self::INACTIVE)
            ->get()

            ->map(function ($emp) {

                $latestLog =
                    $emp->statusLogs->first();

                return [

                    'id' => $emp->id,

                    'full_name' => $emp->full_name,

                    'department' => $emp->department,

                    'position' => $emp->position,

                    'status' => $emp->status,

                    'status_change_pending' =>
                        $emp->status_change_pending,

                    'status_logs' => $latestLog ? [[

                        'changed_by_name' =>
                            optional(
                                $latestLog->changedBy
                            )->name,

                        'approved_by_name' =>
                            optional(
                                $latestLog->approvedBy
                            )->name,

                        'changed_at' =>
                            $latestLog->changed_at,

                        'approved_at' =>
                            $latestLog->approved_at,

                    ]] : [],
                ];
            });

        return response()->json($employees);
    }
}
