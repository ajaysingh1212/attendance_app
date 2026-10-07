<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollAdjustment;
use App\Models\AttendanceLog;
use App\Models\PayrollPartPayment;
use App\Services\PayrollGenerator;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
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
        'year' => 'required|integer|min:2000|max:2100',
    ]);

    $month = (int) $request->month;
    $year = (int) $request->year;
    $generator = app(PayrollGenerator::class);
    $generator->assertCompletedMonth($month, $year);

    try {
        DB::transaction(function () use ($request, $month, $year, $generator) {
            $existing = Payroll::where('month', $month)->where('year', $year)
                ->lockForUpdate()->get();

            if (!$request->boolean('force') && $existing->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'month' => 'Payroll already generated for this month. Please use Regenerate Payroll.',
                ]);
            }

            foreach ($existing as $payroll) {
                if (!$generator->canRecalculate($payroll)) {
                    throw ValidationException::withMessages([
                        'month' => 'This month includes paid or manually adjusted payroll. Automatic regeneration is blocked to preserve payments.',
                    ]);
                }
            }

            $existing = $existing->keyBy('employee_id');
            // Include existing employees even if their status changed since generation.
            $employees = Employee::where(function ($query) use ($existing) {
                $query->whereNotIn('status', ['Resigned', 'Terminated', 'Suspended'])
                    ->orWhereNull('status')
                    ->orWhereIn('id', $existing->keys());
            })->get();

            foreach ($employees as $employee) {
                $generator->save($employee, $month, $year, $existing->get($employee->id));
            }
        });

        return redirect()
            ->route('admin.payroll.index', ['month' => $month, 'year' => $year])
            ->with('success', 'Payroll updated using the full month attendance.');
    } catch (ValidationException $e) {
        throw $e;
    } catch (Throwable $e) {
        report($e);

        return back()->withInput()
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

        $payrolls = Payroll::with(['employee.officeBranch', 'officeBranch'])
            ->where('month', $month)
            ->where('year', $year)
            ->get();

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

public function downloadPayrollPdf(Payroll $payroll)
{
    $payroll->load(['employee.branch', 'generatedBy']);

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
    $payroll = Payroll::with(['employee.officeBranch', 'generatedBy', 'branch', 'officeBranch'])->findOrFail($request->id);

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
