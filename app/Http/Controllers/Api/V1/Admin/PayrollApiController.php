<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Http\Request;

class PayrollApiController extends Controller
{
    public function getSalaryDetails($userId, $month, $year)
    {
        // Find employee by user_id
        $employee = Employee::where('user_id', $userId)->first();

        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => 'Employee not found.'
            ], 404);
        }

        // Get payroll
        $payroll = Payroll::with('partPayments')
            ->where('employee_id', $employee->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if (!$payroll) {
            return response()->json([
                'status' => false,
                'message' => 'Salary record not found.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Salary details fetched successfully.',
            'data' => [

                'employee_id' => $employee->id,
                'employee_name' => $employee->full_name ?? $employee->name,
                'month' => $payroll->month,
                'year' => $payroll->year,

                // Attendance Summary
                'working_days' => $payroll->working_days,
                'present_days' => $payroll->present_days,
                'paid_leaves' => $payroll->paid_leaves,
                'holidays' => $payroll->holidays,
                'absent_days' => $payroll->absent_days,
                'half_days' => $payroll->half_days,
                'leave_days' => $payroll->leave_days,
                'final_paid_days' => $payroll->final_paid_days,
                'valid_sundays' => $payroll->valid_sundays,
                'sundays' => $payroll->sundays,
                'total_days' => $payroll->total_days,

                // Salary
                'basic' => $payroll->basic,
                'hra' => $payroll->hra,
                'allowance' => $payroll->allowance,
                'bonus' => $payroll->bonus,
                'gross_salary' => $payroll->gross_salary,
                'deductions' => $payroll->deductions,
                'manual_adjustment' => $payroll->manual_adjustment,
                'net_salary' => $payroll->net_salary,

                // Payment
                'total_paid' => $payroll->total_paid,
                'remaining_salary' => $payroll->remaining_amount,

                // Other
                'remarks' => $payroll->remarks,
                'status' => $payroll->status,
                'message' => $payroll->message,
                'generated_at' => $payroll->generated_at,

                // Part Payments
                'part_payments' => $payroll->partPayments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => $payment->part_amount,
                        'payment_date' => $payment->payment_date,
                        'payment_mode' => $payment->payment_mode,
                        'remarks' => $payment->remarks,
                    ];
                }),
            ]
        ]);
    }
}