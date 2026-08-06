<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceDetail;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Payroll;
use App\Models\PayrollAdjustment;
use App\Models\SalaryStructure;
use App\Models\AttendanceLog;
use App\Models\PayrollPartPayment;
use App\Models\SalaryIncrement;
use App\Models\SalaryStructureHistory;
use App\Services\PayrollCalculator;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Throwable;


class PayrollController extends Controller
{
public function index(Request $request)
{
    $month = $request->month ?? now()->month;
    $year  = $request->year ?? now()->year;

    $alreadyGenerated = Payroll::where('month', $month)
        ->where('year', $year)
        ->exists();

    return view('admin.salary_payroll.index', compact(
        'month',
        'year',
        'alreadyGenerated'
    ));
}


public function verifyMasterPassword(Request $request)
{
    $request->validate([
        'master_password' => 'required',
        'month' => 'required',
        'year'  => 'required',
    ]);

    $user = auth()->user();

    if (!Hash::check($request->master_password, $user->master_password)) {
        return back()->with('error', '❌ Master password incorrect!');
    }

    // ✅ DIRECTLY CALL generate()
    $request->merge(['force' => true]);

    return $this->generate($request);
}







public function generate(Request $request)
{
    $request->validate([
        'month' => 'required|integer|min:1|max:12',
        'year'  => 'required|integer|min:2000|max:2100',
    ]);

    try {
    $month = (int) $request->month;
    $year  = (int) $request->year;

    if (
        !$request->boolean('force') &&
        Payroll::where('month', $month)->where('year', $year)->exists()
    ) {
        return redirect()
            ->route('admin.payroll.index', ['month' => $month, 'year' => $year])
            ->with('error', 'Payroll already generated for this month. Please use Regenerate Payroll.');
    }

    Payroll::where('month', $month)
        ->where('year', $year)
        ->delete();

    $start = Carbon::create($year, $month, 1)->startOfMonth();
    $end   = Carbon::create($year, $month, 1)->endOfMonth();
    $daysInMonth = $start->daysInMonth;

    $payrollDate = $start->copy();

    /* ============================
       HOLIDAYS
    ============================ */
    $holidayDates = [];

    $holidays = Holiday::where('start_date', '<=', $end)
        ->where('end_date', '>=', $start)
        ->get();

    foreach ($holidays as $h) {
        foreach (CarbonPeriod::create($h->start_date, $h->end_date) as $d) {
            if ($d->between($start, $end)) {
                $holidayDates[] = $d->format('Y-m-d');
            }
        }
    }

    $holidayDates = array_unique($holidayDates);

    /* ============================
       SUNDAYS
    ============================ */
    $sundayDates = [];
    for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
        if ($d->isSunday()) {
            $sundayDates[] = $d->format('Y-m-d');
        }
    }

    $employees = Employee::where(function ($q) {
    $q->whereNotIn('status', ['Resigned', 'Terminated', 'Suspended'])
      ->orWhereNull('status');
    })->get();

    foreach ($employees as $employee) {

        /* ============================
           DEFAULT SALARY
        ============================ */
        $basic     = (float)$employee->basic_salary;
        $hra       = (float)$employee->hra;
        $allowance = (float)$employee->other_allowances;

        $gross = $basic + $hra + $allowance;
        $deductions = (float)($employee->deductions ?? 0);
        $monthlySalary = $gross - $deductions;

        $incrementId = null;
        $remarks = 'Salary processed successfully.';

        /* ============================
           🔥 INCREMENT (ADDED BACK)
        ============================ */
        $increment = SalaryIncrement::where('employee_id', $employee->id)
            ->whereRaw("LOWER(TRIM(status)) = 'approved'")
            ->whereRaw("
                STR_TO_DATE(CONCAT(increment_month,'-01'),'%Y-%m-%d') <= ?
            ", [$payrollDate->format('Y-m-d')])
            ->orderByRaw("
                STR_TO_DATE(CONCAT(increment_month,'-01'),'%Y-%m-%d') DESC
            ")
            ->first();

        if ($increment) {

            $basic     = (float)$increment->new_basic;
            $hra       = (float)$increment->new_hra;
            $allowance = (float)$increment->new_allowance;

            $gross = (float)$increment->new_gross_salary;
            $monthlySalary = $gross - $deductions;

            $incrementId = $increment->id;

            $remarks = "🎉 Salary updated from ₹{$increment->old_gross_salary} to ₹{$increment->new_gross_salary}";
        }

        /* ============================
           JOINING DATE
        ============================ */
        $joining = $employee->date_of_joining
            ? Carbon::parse($employee->date_of_joining)->startOfDay()
            : null;

        /* ============================
           LEAVE REQUEST
        ============================ */
        $approvedLeaveMap = [];
        $paidLeaveDates = [];
        $unpaidLeaveDates = [];

        if ($employee->user_id) {

            $leaveRequests = LeaveRequest::with('leaveType')
                ->where('user_id', $employee->user_id)
                ->whereRaw("LOWER(TRIM(status)) = 'approved'")
                ->where('date_from', '<=', $end)
                ->where('date_to', '>=', $start)
                ->get();

            foreach ($leaveRequests as $leave) {

                $isPaidLeave = LeaveType::isPaidName($leave->leaveType->name ?? null);

                foreach (CarbonPeriod::create($leave->date_from, $leave->date_to) as $d) {

                    if (!$d->between($start, $end)) continue;

                    $date = $d->format('Y-m-d');

                    if ($joining && $date < $joining->format('Y-m-d')) continue;

                    // For overlapping approved requests, an unpaid type wins.
                    $approvedLeaveMap[$date] = isset($approvedLeaveMap[$date])
                        ? ($approvedLeaveMap[$date] && $isPaidLeave)
                        : $isPaidLeave;
                }
            }

            foreach ($approvedLeaveMap as $date => $isPaidLeave) {
                if ($isPaidLeave) {
                    $paidLeaveDates[] = $date;
                } else {
                    $unpaidLeaveDates[] = $date;
                }
            }
        }

        /* ============================
           ATTENDANCE
        ============================ */
        $attendance = AttendanceDetail::where(function ($query) use ($employee) {
                $query->where('employee_id', $employee->id);
                if ($employee->user_id) {
                    $query->orWhere('user_id', $employee->user_id);
                }
            })
            ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->select('date', DB::raw('LOWER(TRIM(status)) as status'))
            ->get()
            ->groupBy('date');

        $presentDays = 0;
        $halfDays = 0;
        $presentDates = [];
        $halfDayDates = [];
        $absentDates = [];
        $attendanceHolidayDates = [];

        foreach ($attendance as $date => $records) {

            $date = Carbon::parse($date)->format('Y-m-d');

            // ✅ IMPORTANT: approved leave priority
            if (isset($approvedLeaveMap[$date])) {
                continue;
            }

            if ($joining && $date < $joining->format('Y-m-d')) continue;

            $statuses = collect($records)->pluck('status')->toArray();

            if (in_array('present', $statuses) || in_array('week_off', $statuses)) {
                $presentDays++;
                $presentDates[] = $date;
                continue;
            }

            if (array_intersect(['half_day','half_time'], $statuses)) {
                $halfDays += 0.5;
                $halfDayDates[] = $date;
                continue;
            }

            if (in_array('late', $statuses)) {
                $presentDays++;
                $presentDates[] = $date;
                continue;
            }

            if (in_array('absent', $statuses)) {
                $absentDates[] = $date;
                continue;
            }

            if (array_intersect(['paid leave','paid_leave'], $statuses)) {
                $paidLeaveDates[] = $date;
                continue;
            }

            if (collect($statuses)->contains(fn($s) => str_contains($s, 'holiday'))) {
                $attendanceHolidayDates[] = $date;
                continue;
            }

            $unpaidLeaveDates[] = $date;
        }

        /* ============================
           UNIQUE
        ============================ */
        $paidLeaveDates = array_unique($paidLeaveDates);
        $unpaidLeaveDates = array_unique($unpaidLeaveDates);
        $paidLeaveDates = array_values(array_diff($paidLeaveDates, $unpaidLeaveDates));
        $presentDates = array_unique($presentDates);
        $halfDayDates = array_unique($halfDayDates);
        $attendanceHolidayDates = array_unique($attendanceHolidayDates);

        /* ============================
           HOLIDAYS FINAL
        ============================ */
        $finalHolidayDates = count($holidayDates)
            ? $holidayDates
            : $attendanceHolidayDates;

        $paidHolidays = array_diff($finalHolidayDates, $absentDates, $unpaidLeaveDates);
        $validSundays = array_diff($sundayDates, $absentDates, $unpaidLeaveDates);

        /* ============================
           WORKING DAYS (FULL MONTH)
        ============================ */
        $workingDays = $daysInMonth;

        /* ============================
           FINAL PAID DAYS
        ============================ */
        // One date-wise ledger prevents leave/Sunday/holiday double payment.
        $paidDayValues = [];
        foreach ($presentDates as $date) {
            $paidDayValues[$date] = 1.0;
        }
        foreach ($halfDayDates as $date) {
            $paidDayValues[$date] = 0.5;
        }
        foreach ($paidLeaveDates as $date) {
            $paidDayValues[$date] = 1.0;
        }
        foreach (array_merge($validSundays, $paidHolidays) as $date) {
            if (!$joining || $date >= $joining->format('Y-m-d')) {
                $paidDayValues[$date] = max($paidDayValues[$date] ?? 0, 1.0);
            }
        }

        $finalPaidDays = array_sum($paidDayValues);

        // Keep approved unpaid leave separate from ordinary absence in reports.
        $totalUnpaidDays = max(0, $workingDays - $finalPaidDays);
        $absentDays = max(0, $totalUnpaidDays - count($unpaidLeaveDates));

        /* ============================
           SALARY
        ============================ */
        $perDaySalary = $monthlySalary / $daysInMonth;
        $finalSalary = round($perDaySalary * $finalPaidDays, 2);

        // Use the shared calculator used by the live attendance salary panel.
        // This keeps generated payroll and AJAX preview on exactly the same rules.
        $calculation = app(PayrollCalculator::class)->calculate($employee, (int) $month, (int) $year);

        Payroll::create([
            'employee_id' => $employee->id,
            'month' => $month,
            'year' => $year,
            'working_days' => $calculation['working_days'],
            'present_days' => $calculation['present_days'],
            'half_days' => $calculation['half_days'],
            'paid_leaves' => $calculation['paid_leaves'],
            'leave_days' => $calculation['leave_days'],
            'holidays' => $calculation['holidays'],
            'absent_days' => $calculation['absent_days'],
            'final_paid_days' => $calculation['final_paid_days'],
            'valid_sundays' => $calculation['valid_sundays'],
            'basic' => $calculation['basic'],
            'hra' => $calculation['hra'],
            'allowance' => $calculation['allowance'],
            'gross_salary' => $calculation['gross_salary'],
            'deductions' => $calculation['deductions'],
            'net_salary' => $calculation['net_salary'],
            'remaining_salary' => $calculation['net_salary'],
            'salary_increment_id' => $calculation['salary_increment_id'],
            'remarks' => $calculation['remarks'],
            'status' => 'Pending',
            'salary_generated_by' => auth()->id(),
            'salary_generated_role' => auth()->user()->role ?? null,
            'generated_at' => now(),
        ]);
    }

    return redirect()
        ->route('admin.payroll.index', ['month' => $month, 'year' => $year])
        ->with('success', '🔥 PERFECT: Increment + Leave + Salary all fixed');
    } catch (Throwable $e) {
        report($e);

        return back()
            ->withInput()
            ->with('error', 'Payroll generate nahi hua: ' . $e->getMessage());
    }
}


public function manualAdjustmentUpdate(Request $request, $payrollId)
{
    $payroll = Payroll::findOrFail($payrollId);
    $user = auth()->user();

    // Get role name
    $roleName = $user->roles()->pluck('title')->first();

    // Validation
    $request->validate([
        'gross_salary'      => 'required|numeric',
        'manual_adjustment' => 'nullable|numeric',
        'remaining_salary'  => 'required|numeric',
        'adjustment_note'   => $request->manual_adjustment > 0 ? 'required|string|max:255' : 'nullable|string|max:255',
    ]);

    // 🔹 Fetch payroll adjustment
    $payrollAdjustment = null;
    if ($request->payroll_adjustment_id) {
        $payrollAdjustment = \App\Models\PayrollAdjustment::find($request->payroll_adjustment_id);
    }

    $manualAmount = $request->manual_adjustment ?? 0;

    // 🔹 Apply adjustment to PayrollAdjustment amount
    if ($payrollAdjustment) {

        if ($payrollAdjustment->type === 'advance') {
            $manualAmount = abs($manualAmount);
            $newAmount = $payrollAdjustment->amount - $manualAmount;

        } elseif ($payrollAdjustment->type === 'bonus') {
            $manualAmount = abs($manualAmount);
            $newAmount = $payrollAdjustment->amount - $manualAmount;

        } else {
            $newAmount = $payrollAdjustment->amount - $manualAmount;
        }

        // Prevent negative amount
        $newAmount = max($newAmount, 0);

        // Update status
        $status = $newAmount == 0 ? 'paid' : 'due';

        // Update PayrollAdjustment record
        $payrollAdjustment->update([
            'amount' => $newAmount,
            'status' => $status,
            'remarks' => $request->adjustment_note ?? $payrollAdjustment->remarks,
            'adjustment_date' => now(),
        ]);
    }

    // 🔹 If payroll status paid then remaining salary = 0
    $remainingSalary = $request->status === 'paid' ? 0 : $request->remaining_salary;

    // 🔹 Payroll Update
    $payroll->update([
        'gross_salary'          => $request->gross_salary,
        'manual_adjustment'     => $manualAmount,
        'remaining_salary'      => $remainingSalary,
        'status'                => $request->status,
        'salary_generated_by'   => $user->id,
        'salary_generated_role' => $roleName,
        'message'               => $request->message,
        'generated_at'          => now(),
    ]);

    // 🔹 Salary Structure History Save
    \App\Models\SalaryStructureHistory::create([
        'payroll_adjustment_id' => $request->payroll_adjustment_id,
        'employee_id'           => $payroll->employee_id,
        'structure_snapshot'    => [
            'gross_salary'      => $request->gross_salary,
            'manual_adjustment' => $manualAmount,
            'adjustment_note'   => $request->adjustment_note,
            'other_adjustments' => $request->other_adjustments,
            'remaining_salary'  => $remainingSalary,
            'reason'            => $request->message,
            'status'            => $request->status,
            'recorded_by'       => $user->id,
            'role'              => $roleName,
            'created_at'        => now()->toDateTimeString(),
        ],
    ]);

    return redirect()->route('admin.payroll.list')
        ->with('success', 'Payroll updated successfully with manual adjustment.');
}


    public function list(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $payrolls = Payroll::with('employee')->where('month', $month)->where('year', $year)->get();

        return view('admin.salary_payroll.list', compact('payrolls', 'month', 'year'));
    }

    public function getSalaryDetails($id)
    {
        $employee = Employee::findOrFail($id);

        $basic_salary = (float)$employee->basic_salary ?? 0;
        $hra = (float)$employee->hra ?? 0;
        $other_allowances = (float)$employee->other_allowances ?? 0;
        $deductions = (float)$employee->deductions ?? 0;

        $advance = (float)PayrollAdjustment::where('employee_id', $id)
            ->where('type', 'advance')
            ->whereNotNull('amount')
            ->sum('amount');

        $penalty = (float)PayrollAdjustment::where('employee_id', $id)
            ->where('type', 'penalty')
            ->whereNotNull('amount')
            ->sum('amount');

        $gross_salary = $basic_salary + $hra + $other_allowances;
        $total_deductions = $deductions + $advance + $penalty;
        $net_salary = $gross_salary - $total_deductions;

        return response()->json([
            'basic_salary'     => number_format($basic_salary, 2),
            'hra'              => number_format($hra, 2),
            'other_allowances' => number_format($other_allowances, 2),
            'deductions'       => number_format($deductions, 2),
            'advance'          => number_format($advance, 2),
            'penalty'          => number_format($penalty, 2),
            'net_salary'       => number_format($net_salary, 2),
        ]);
    }
public function manualAdjustmentForm($payrollId)
{
    $payroll = Payroll::with('employee')->findOrFail($payrollId); // Payroll + Employee detail
    $employee = $payroll->employee_id;

    // Fetch all adjustments for this employee, newest first
    $adjustments = PayrollAdjustment::where('employee_id', $employee)
        ->orderBy('created_at', 'desc')
        ->get();

    return view('admin.salary_payroll.manual_adjustment', compact('payroll','adjustments'));
}

public function downloadPayrollPdf($payrollId)
{
    $payroll = Payroll::with('employee.branch')->findOrFail($payrollId);

    $startDate = Carbon::create($payroll->year, $payroll->month, 1)->startOfMonth();
    $endDate   = Carbon::create($payroll->year, $payroll->month, 1)->endOfMonth();

    // Total seconds calculate
    $totalSeconds = AttendanceLog::where('employee_id', $payroll->employee_id)
        ->whereBetween('date', [$startDate, $endDate])
        ->get()
        ->reduce(function ($carry, $log) {
            if ($log->actual_in && $log->actual_out) {
                $in  = Carbon::parse($log->actual_in);
                $out = Carbon::parse($log->actual_out);
                $carry += $out->diffInSeconds($in);
            }
            return $carry;
        }, 0);

    // Convert to HH:MM:SS
    $hours   = floor($totalSeconds / 3600);
    $minutes = floor(($totalSeconds % 3600) / 60);
    $seconds = $totalSeconds % 60;

    $totalHoursFormatted = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

    $pdf = Pdf::loadView('admin.salary_payroll.report_pdf', compact('payroll', 'totalHoursFormatted'));
    return $pdf->download('payroll_'.$payroll->id.'.pdf');
}
public function details(Request $request)
{
    $payroll = Payroll::with(['employee', 'generatedBy', 'branch'])->findOrFail($request->id);

    $adjustments = PayrollAdjustment::where('employee_id', $payroll->employee_id)->get();
    $attendance = \App\Models\AttendanceDetail::where('user_id', $payroll->employee->user_id ?? null)
                    ->whereMonth('date', $payroll->month)
                    ->whereYear('date', $payroll->year)
                    ->get();

    $html = view('admin.salary_payroll.partials.payroll_details', compact('payroll', 'adjustments', 'attendance'))->render();

    return response()->json(['html' => $html]);
}
public function manualAdjustPage($id)
{
    $payroll = Payroll::with('employee')->findOrFail($id);
    $adjustments = PayrollPartPayment::where('payroll_id', $id)->get();
    return view('admin.salary_payroll.manual_adjustment', compact('payroll', 'adjustments'));
}

public function partPaymentPage($id)
{
    $payroll = Payroll::with('employee')->findOrFail($id);
    return view('admin.salary_payroll.part_payment', compact('payroll'));
}

public function savePartPayment(Request $request, $id)
{
    $request->validate([
        'payment_date' => 'required|date',
        'part_amount' => 'required|numeric|min:0.01',
    ]);

    $payroll = Payroll::findOrFail($id);

    // 🔹 Get already paid total
    $totalPaid = PayrollPartPayment::where('payroll_id', $id)->sum('part_amount');

    // 🔹 Calculate new totals
    $newTotalPaid = $totalPaid + $request->part_amount;
    $remaining = max($payroll->net_salary - $newTotalPaid, 0); // Prevent negative values

    // 🔹 Save new part payment
    PayrollPartPayment::create([
        'payroll_id' => $id,
        'payment_date' => $request->payment_date,
        'part_amount' => $request->part_amount,
        'remaining_amount' => $remaining,
        'additional_data' => ['note' => $request->note],
        'created_by_id' => auth()->id(),
    ]);

    // 🔹 Update Payroll main table
    $payroll->update([
        'remaining_salary' => $remaining,
        'status' => $remaining <= 0 ? 'Paid' : 'Partially Paid',
    ]);

    return redirect()->back()->with('success', 'Part payment added successfully!');
}
public function partPaymentsList(Request $request)
{
    $payroll = Payroll::with('partPayments')->find($request->id);
    if (!$payroll) {
        return response()->json(['html' => '<p class="text-danger">Payroll not found.</p>']);
    }

    $html = view('admin.salary_payroll.part_payments_modal', compact('payroll'))->render();
    return response()->json(['html' => $html]);
}



}
