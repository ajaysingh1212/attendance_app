<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class PayrollGenerator
{
    public function __construct(private PayrollCalculator $calculator)
    {
    }

    public function assertCompletedMonth(int $month, int $year): void
    {
        if (Carbon::create($year, $month, 1)->startOfMonth()->gte(now()->startOfMonth())) {
            throw ValidationException::withMessages([
                'month' => 'Final payroll can only be generated after the month ends. Use Attendance Details for the current salary preview.',
            ]);
        }
    }

    public function canRecalculate(Payroll $payroll): bool
    {
        return strtolower(trim((string) $payroll->status)) === 'pending'
            && !$payroll->partPayments()->exists()
            && (float) $payroll->manual_adjustment === 0.0
            && (float) $payroll->bonus === 0.0
            && !$payroll->message
            && ($payroll->remaining_salary === null
                || abs((float) $payroll->remaining_salary - (float) $payroll->net_salary) < 0.01);
    }

    // Caller holds a transaction and locks existing payroll rows before updating.
    public function save(Employee $employee, int $month, int $year, ?Payroll $payroll = null): Payroll
    {
        $this->assertCompletedMonth($month, $year);
        if ($payroll && !$this->canRecalculate($payroll)) {
            throw ValidationException::withMessages([
                'month' => 'Paid, partially paid or manually adjusted payroll cannot be regenerated automatically.',
            ]);
        }

        $calculation = $this->calculator->calculate($employee, $month, $year);
        $payroll ??= new Payroll();
        $payroll->fill(array_intersect_key($calculation, array_flip([
            'working_days', 'present_days', 'half_days', 'paid_leaves', 'leave_days',
            'holidays', 'absent_days', 'final_paid_days', 'valid_sundays', 'basic',
            'hra', 'allowance', 'gross_salary', 'deductions', 'net_salary',
            'salary_increment_id', 'remarks',
        ])));
        $payroll->fill([
            'employee_id' => $employee->id,
            'office_branch_id' => $employee->office_branch_id,
            'month' => $month,
            'year' => $year,
            'remaining_salary' => $calculation['net_salary'],
            'status' => 'Pending',
            'generated_at' => now(),
        ]);
        if (auth()->check()) {
            $payroll->salary_generated_by = auth()->id();
            $payroll->salary_generated_role = auth()->user()->role ?? null;
        }
        $payroll->save();

        return $payroll;
    }
}
