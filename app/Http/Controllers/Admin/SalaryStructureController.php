<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SalaryStructure;
use App\Models\Employee;
use App\Models\PayrollAdjustment;
use App\Models\SalaryStructureHistory;
use Illuminate\Support\Facades\DB;

class SalaryStructureController extends Controller
{

public function index()
{
    $structures = SalaryStructure::with('employee')->get();
    return view('admin.salary_structures.index', compact('structures'));
}

public function create()
{
    $employees = Employee::all();
    return view('admin.salary_structures.create', compact('employees'));
}

public function store(Request $request)
{
    $request->validate([
        'employee_id' => 'required|exists:employees,id',
        'basic' => 'required|numeric',
    ]);

    $currentMonth = now()->format('Y-m'); // e.g. 2025-08

    // Check if adjustment already exists for this employee in current month
    if (
        ($request->filled('advance') && $request->advance > 0) ||
        ($request->filled('penalty') && $request->penalty > 0)
    ) {
        $alreadyAdjusted = SalaryStructure::where('employee_id', $request->employee_id)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->exists();

        if ($alreadyAdjusted) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'इस महीने के लिए salary adjustment पहले ही किया जा चुका है।');
        }
    }

    DB::transaction(function () use ($request) {
        // Step 1: Create salary structure
        $salary = SalaryStructure::create($request->all());

        // Step 2: Save adjustments
        $adjustmentType = null;
        $adjustmentAmount = null;

        if ($request->filled('advance') && $request->advance > 0) {
            $adjustmentType = 'advance';
            $adjustmentAmount = $request->advance;

            PayrollAdjustment::create([
                'employee_id' => $request->employee_id,
                'type' => $adjustmentType,
                'amount' => -abs($adjustmentAmount),
                'reason' => 'Advance recorded during salary structure creation',
            ]);
        }

        if ($request->filled('penalty') && $request->penalty > 0) {
            $adjustmentType = 'penalty';
            $adjustmentAmount = $request->penalty;

            PayrollAdjustment::create([
                'employee_id' => $request->employee_id,
                'type' => $adjustmentType,
                'amount' => -abs($adjustmentAmount),
                'reason' => 'Penalty recorded during salary structure creation',
            ]);
        }

        // Step 3: Fetch related employee and user details
        $employee = Employee::with('user')->findOrFail($request->employee_id);
        $user = $employee->user;

        // Prepare snapshot with extra details
        $snapshot = $salary->toArray();
        $snapshot['type'] = $adjustmentType ?? 'initial';
        $snapshot['adjustment_amount'] = $adjustmentAmount ?? 0;
        $snapshot['employee_name'] = $employee->full_name;
        $snapshot['employee_code'] = $employee->employee_code;
        $snapshot['user_id'] = $user?->id;
        $snapshot['user_email'] = $user?->email;

        // Step 4: Save salary structure history
        SalaryStructureHistory::create([
            'salary_structure_id' => $salary->id,
            'employee_id' => $request->employee_id,
            'structure_snapshot' => json_encode($snapshot),
            'type' => $adjustmentType ?? 'initial',
        ]);
    });

    return redirect()->route('admin.salary-structures.index')->with('success', 'Salary structure created.');
}



public function edit(SalaryStructure $salaryStructure)
{
    $employees = Employee::all();
    return view('admin.salary_structures.edit', compact('salaryStructure', 'employees'));
}

public function update(Request $request, SalaryStructure $salaryStructure)
{
    $request->validate([
        'basic' => 'required|numeric',
    ]);

    $salaryStructure->update($request->all());

    return redirect()->route('admin.salary-structures.index')->with('success', 'Salary structure updated.');
}

public function destroy(SalaryStructure $salaryStructure)
{
    $salaryStructure->delete();

    return back()->with('success', 'Salary structure deleted.');
}
public function show()
{
    $histories = SalaryStructureHistory::with('employee')->latest()->paginate(10);
    return view('admin.salary_structures.history', compact('histories'));
}

}
