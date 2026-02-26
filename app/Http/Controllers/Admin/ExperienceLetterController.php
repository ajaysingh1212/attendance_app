<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\ExperienceLetter;
use App\Models\SalaryIncrement;
use Illuminate\Http\Request;

class ExperienceLetterController extends Controller
{
public function index()
{
    $letters = ExperienceLetter::with('employee')
        ->latest()
        ->get();

    return view('admin.experience_letters.index', compact('letters'));
}

    public function create()
    {
        $employees = Employee::with('user')->get();
        return view('admin.experience_letters.create', compact('employees'));
    }

public function store(Request $request)
{
    $data = $request->all();

    $data['created_by'] = auth()->id();
    $data['status'] = 'pending';   // 👈 yaha fix hai

    ExperienceLetter::create($data);

    return redirect()
        ->route('admin.experience-letters.index')
        ->with('success', 'Experience Letter Created Successfully');
}

    public function show($id)
    {
        $letter = ExperienceLetter::with('employee.user')->findOrFail($id);
        return view('admin.experience_letters.show', compact('letter'));
    }

    public function edit($id)
    {
        $letter = ExperienceLetter::findOrFail($id);
        $employees = Employee::with('user')->get();
        return view('admin.experience_letters.edit', compact('letter','employees'));
    }

    public function update(Request $request, $id)
    {
        $letter = ExperienceLetter::findOrFail($id);
        $letter->update($request->all());

        return redirect()->route('admin.experience-letters.index')
            ->with('success', 'Updated Successfully');
    }
public function getEmployeeDetails($id)
{
    $employee = Employee::with('salaryStructure')->find($id);

    $allIncrements = SalaryIncrement::where('employee_id', $id)
        ->where('status', 'approved')
        ->orderBy('increment_month', 'asc')
        ->get();

    return response()->json([
        'employee' => $employee,
        'all_increments' => $allIncrements
    ]);
}
public function updateStatus(Request $request, $id)
{
    $letter = ExperienceLetter::findOrFail($id);
    $letter->status = $request->status;
    $letter->save();

    return redirect()
        ->route('admin.experience-letters.index')
        ->with('success','Status Updated Successfully');
}
public function printLetter($id)
{
    $letter = ExperienceLetter::with('employee')
        ->findOrFail($id);

    $employee = $letter->employee;

    $increments = \App\Models\SalaryIncrement::where('employee_id', $employee->id)
        ->where('status','approved')
        ->orderBy('increment_month','asc')
        ->get();

    return view('admin.experience_letters.print', compact(
        'letter','employee','increments'
    ));
}
}
