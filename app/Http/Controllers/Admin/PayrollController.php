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
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;


class PayrollController extends Controller
{
    public function index()
    {
        return view('admin.salary_payroll.index');
    }

public function generate(Request $request)
{
    $request->validate([
        'month' => 'required|integer|min:1|max:12',
        'year'  => 'required|integer|min:2000|max:2100',
    ]);

    $month = $request->month;
    $year  = $request->year;

    $start = Carbon::create($year, $month, 1)->startOfMonth();
    $end   = Carbon::create($year, $month, 1)->endOfMonth();
    $daysInMonth = $start->daysInMonth;

    /* -------------------- Sundays -------------------- */
    $sundayDates = [];
    for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
        if ($d->isSunday()) {
            $sundayDates[] = $d->toDateString();
        }
    }

    /* -------------------- Holidays -------------------- */
    $holidays = Holiday::where('start_date', '<=', $end)
        ->where('end_date', '>=', $start)
        ->get();

    $holidayDates = [];
    foreach ($holidays as $h) {
        $from = Carbon::parse($h->start_date)->lt($start) ? $start : Carbon::parse($h->start_date);
        $to   = Carbon::parse($h->end_date)->gt($end) ? $end : Carbon::parse($h->end_date);

        foreach (CarbonPeriod::create($from, $to) as $d) {
            $holidayDates[] = $d->toDateString();
        }
    }
    $holidayDates = array_values(array_unique($holidayDates));

    $employees = Employee::all();
    $payrollMonth = Carbon::create($year, $month, 1)->format('Y-m');

    foreach ($employees as $employee) {

        /* =================================================
           DEFAULT SALARY (Employee table)
        ================================================= */
        $basic     = $employee->basic_salary;
        $hra       = $employee->hra;
        $allowance = $employee->other_allowances;

        $gross     = $basic + $hra + $allowance;
        $netSalary = $employee->net_salary;

        $incrementId = null;
        $remarks = 'Salary processed successfully for this month.';

        /* =================================================
           SALARY INCREMENT (if applicable)
        ================================================= */
        $increment = SalaryIncrement::where('employee_id', $employee->id)
            ->whereRaw("LOWER(TRIM(status)) = 'approved'")
            ->whereRaw("TRIM(increment_month) <= ?", [$payrollMonth])
            ->orderByRaw("TRIM(increment_month) DESC")
            ->first();

        if ($increment) {
            // 🔥 overwrite salary parts ONLY if increment exists
            $oldGross = $increment->old_gross_salary;
            $newGross = $increment->new_gross_salary;

            $basic     = $increment->new_basic;
            $hra       = $increment->new_hra;
            $allowance = $increment->new_allowance;

            $gross     = $newGross;
            $netSalary = $gross - ($employee->deductions ?? 0);

            $percent = $oldGross > 0
                ? round((($newGross - $oldGross) / $oldGross) * 100, 2)
                : 0;

            $incrementId = $increment->id;

            $remarks =
                "🎉 Congratulations! Your salary has been increased successfully. " .
                "Earlier Gross was ₹{$oldGross}, now it is ₹{$newGross}. " .
                "That’s a {$percent}% increment — keep growing! 🚀";
        }

        if (!$netSalary) continue;

        /* -------------------- Joining Date -------------------- */
        $joining = $employee->date_of_joining
            ? Carbon::parse($employee->date_of_joining)->startOfDay()
            : null;

        /* -------------------- Attendance -------------------- */
        $attendanceQuery = AttendanceDetail::query();
        $employee->user_id
            ? $attendanceQuery->where('user_id', $employee->user_id)
            : $attendanceQuery->where('employee_id', $employee->id);

        $attendanceRecords = $attendanceQuery
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $end)
            ->get();

        $empSundayDates  = $joining
            ? array_filter($sundayDates, fn($d) => Carbon::parse($d)->gte($joining))
            : $sundayDates;

        $empHolidayDates = $joining
            ? array_filter($holidayDates, fn($d) => Carbon::parse($d)->gte($joining))
            : $holidayDates;

        $presentDays = 0;
        $halfDays = 0;
        $absentDates = [];

        foreach ($attendanceRecords as $att) {
            $date = Carbon::parse($att->date)->toDateString();
            if ($joining && $date < $joining->toDateString()) continue;

            $status = strtolower(trim($att->status));
            if ($status === 'present') $presentDays++;
            elseif (in_array($status, ['half_day', 'half_time'])) $halfDays += 0.5;
            elseif ($status === 'absent') $absentDates[] = $date;
        }

        /* -------------------- Paid Leaves -------------------- */
        $leaveDates = [];
        $approvedLeaves = LeaveRequest::where('user_id', $employee->user_id)
            ->where('status', 'approved')
            ->where('date_from', '<=', $end)
            ->where('date_to', '>=', $start)
            ->get();

        foreach ($approvedLeaves as $leave) {
            $leaveType = LeaveType::find($leave->leave_type_id);
            if (!$leaveType || strtolower($leaveType->name) !== 'paid leave') continue;

            $ls = max(Carbon::parse($leave->date_from), $start);
            $le = min(Carbon::parse($leave->date_to), $end);

            if ($joining && $le < $joining) continue;
            if ($joining && $ls < $joining) $ls = $joining;

            foreach (CarbonPeriod::create($ls, $le) as $d) {
                $leaveDates[] = $d->toDateString();
            }
        }

        $leaveDates = array_unique($leaveDates);
        $paidLeaveDays = count($leaveDates);

        /* -------------------- Working Days -------------------- */
        $empStart = $joining && $joining > $start ? $joining : $start;
        $totalDays = $empStart->diffInDays($end) + 1;

        $empSundays = array_filter($empSundayDates, fn($d) =>
            Carbon::parse($d)->between($empStart, $end)
        );

        $workingDays = $totalDays - count($empSundays);
        $validSundays = array_diff($empSundays, $empHolidayDates, $absentDates);
        $paidHolidays = array_diff($empHolidayDates, $absentDates);

        $finalPaidDays = $presentDays + $halfDays + $paidLeaveDays
            + count($validSundays) + count($paidHolidays);

        $absentDays = max(0, $workingDays - $finalPaidDays);

        /* -------------------- Salary -------------------- */
        $perDay = $netSalary / $daysInMonth;
        $netPay = $perDay * $finalPaidDays;

        /* -------------------- SAVE PAYROLL -------------------- */
        Payroll::updateOrCreate(
            ['employee_id' => $employee->id, 'month' => $month, 'year' => $year],
            [
                'working_days' => $workingDays,
                'sundays' => count($empSundays),
                'valid_sundays' => count($validSundays),
                'present_days' => $presentDays,
                'half_days' => $halfDays,
                'paid_leaves' => $paidLeaveDays,
                'leave_days' => count($leaveDates),
                'holidays' => count($paidHolidays),
                'absent_days' => $absentDays,
                'final_paid_days' => $finalPaidDays,
                'total_days' => $totalDays,

                // 🔥 salary parts (incremented OR default)
                'basic' => $basic,
                'hra' => $hra,
                'allowance' => $allowance,
                'gross_salary' => $gross,
                'deductions' => $employee->deductions,
                'net_salary' => $netPay,
                'remaining_salary' => $netPay,

                'salary_increment_id' => $incrementId,
                'remarks' => $remarks,
                'status' => 'Pending',

                'salary_generated_by' => auth()->id(),
                'salary_generated_role' => auth()->user()->role ?? null,
                'generated_at' => now(),
            ]
        );
    }

    return redirect()->route('admin.payroll.index')
        ->with('success', 'Payroll generated successfully with salary increments applied 🎉');
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
        // 'status'            => 'required|string',
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
        // Handle type: advance = subtract, bonus = add
        if ($payrollAdjustment->type === 'advance') {
            $manualAmount = abs($manualAmount); // always positive
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

    // 🔹 Payroll Update
    $payroll->update([
        'gross_salary'          => $request->gross_salary,
        'manual_adjustment'     => $manualAmount,
        'remaining_salary'      => $request->remaining_salary,
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
            'remaining_salary'  => $request->remaining_salary,
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
