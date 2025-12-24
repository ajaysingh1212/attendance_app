<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayrollAdjustment;
use App\Models\Employee;
use Illuminate\Http\Request;

class PayrollAdjustmentController extends Controller
{
    public function index()
    {
        $adjustments = PayrollAdjustment::with('employee')->latest()->get();
        return view('admin.payroll_adjustments.index', compact('adjustments'));
    }

    public function create()
    {
        $employees = Employee::all();
        return view('admin.payroll_adjustments.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type' => 'required|in:advance,penalty',
            'amount' => 'required|numeric',
            'reason' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'adjustment_date' => 'required|date',
        ]);

        PayrollAdjustment::create($request->all());

        return redirect()->route('admin.payroll-adjustments.index')->with('success', 'Adjustment created successfully');
    }

    public function show(PayrollAdjustment $payrollAdjustment)
    {
        return view('admin.payroll_adjustments.show', compact('payrollAdjustment'));
    }

    public function edit(PayrollAdjustment $payrollAdjustment)
    {
        $employees = Employee::all();
        return view('admin.payroll_adjustments.edit', compact('payrollAdjustment', 'employees'));
    }

    public function update(Request $request, PayrollAdjustment $payrollAdjustment)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type' => 'required|in:advance,penalty',
            'amount' => 'required|numeric',
            'reason' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'adjustment_date' => 'required|date',
        ]);

        $payrollAdjustment->update($request->all());

        return redirect()->route('admin.payroll-adjustments.index')->with('success', 'Adjustment updated successfully');
    }

    public function destroy(PayrollAdjustment $payrollAdjustment)
    {
        $payrollAdjustment->delete();
        return back()->with('success', 'Adjustment deleted');
    }


}
