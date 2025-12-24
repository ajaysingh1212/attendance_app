<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\SalaryIncrement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalaryIncrementController extends Controller
{
    public function index(Request $request)
    {
        $q = SalaryIncrement::with(['employee.user','approver'])->latest();

        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }

        if ($request->filled('employee_id')) {
            $q->where('employee_id', $request->employee_id);
        }

        return view('admin.salary_increments.index', [
            'increments' => $q->paginate(20),
            'employees'  => Employee::with('user')->get(),
        ]);
    }

    public function create()
    {
        $employees = Employee::with('user')->get();
        return view('admin.salary_increments.create', compact('employees'));
    }

    public function getEmployeeSalary(Request $request)
    {
        $request->validate(['employee_id' => 'required|integer']);

        $employee = Employee::with(['user','branch','reportingUser'])->find($request->employee_id);

        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        $otherJson = $employee->other_allowances_json
            ? json_decode($employee->other_allowances_json, true)
            : [];

        $totalOther = 0;
        if (is_array($otherJson)) {
            foreach ($otherJson as $v) {
                $totalOther += floatval($v);
            }
        }

        $basic      = $employee->basic_salary ?? 0;
        $hra        = $employee->hra ?? 0;
        $allowance  = $employee->other_allowances ?? 0;
        $deductions = $employee->deductions ?? 0;

        $gross = ($basic + $hra + $allowance + $totalOther) - $deductions;

        $history = SalaryIncrement::where('employee_id', $employee->id)
            ->latest()
            ->limit(8)
            ->get([
                'created_at',
                'old_gross_salary',
                'new_gross_salary',
                'increment_month'
            ]);

        return response()->json([
            'employee' => [
                'id'            => $employee->id,
                'name'          => $employee->user->name ?? null,
                'employee_code' => $employee->employee_code,
                'department'    => $employee->department,
                'position'      => $employee->position,
                'reporting_to'  => [
                    'id'   => $employee->reporting_to,
                    'name' => optional($employee->reportingUser)->name
                ],
                'branch'        => $employee->branch->title ?? null,
                'status'        => $employee->status,
                'other_allowances_json' => $otherJson,
            ],
            'salary' => [
                'basic'      => $basic,
                'hra'        => $hra,
                'allowance'  => $allowance,
                'deductions' => $deductions,
                'gross'      => $gross,
            ],
            'history' => $history,
        ]);
    }

    public function store(Request $request)
    {
       
        $request->validate([
            'employee_id'     => 'required|exists:employees,id',
            'increment_month' => 'required',
            'new_basic'       => 'required|numeric',
        ]);

        $emp = Employee::findOrFail($request->employee_id);

        $old_basic  = $emp->basic_salary ?? 0;
        $old_hra    = $emp->hra ?? 0;
        $old_allow  = $emp->other_allowances ?? 0;
        $old_gross  = $emp->net_salary ?? ($old_basic + $old_hra + $old_allow);

        $new_basic = $request->new_basic;
        $new_hra   = $request->new_hra ?? 0;
        $new_allow = $request->new_allowance ?? 0;

        $other_json = $request->other_allowances_json ?? "{}";
        $decoded_other = json_decode($other_json, true);

        $extra = 0;
        if (is_array($decoded_other)) {
            foreach ($decoded_other as $v) {
                $extra += floatval($v);
            }
        }

        $new_gross = $new_basic + $new_hra + $new_allow + $extra;

        SalaryIncrement::create([
            'employee_id'        => $emp->id,
            'user_id'            => $emp->user_id,
            'increment_month'    => $request->increment_month,
            'remarks'            => $request->remarks,

            'old_basic'          => $old_basic,
            'old_hra'            => $old_hra,
            'old_allowance'      => $old_allow,
            'old_gross_salary'   => $old_gross,

            'old_department'     => $emp->department,
            'old_position'       => $emp->position,
            'old_reporting_to'   => $emp->reporting_to,

            'new_basic'          => $new_basic,
            'new_hra'            => $new_hra,
            'new_allowance'      => $new_allow,
            'new_gross_salary'   => $new_gross,

            'new_department'     => $request->new_department ?? $emp->department,
            'new_position'       => $request->new_position ?? $emp->position,
            'new_reporting_to'   => $request->new_reporting_to ?? $emp->reporting_to,
            'old_reporting_to'   => $emp->reporting_to,
            'old_department'     => $emp->department,
            'old_position'       => $emp->position,

            'other_allowances_json' => $other_json,
            'older_allowances_json' => $emp->other_allowances_json,

            'status'             => 'pending',
            'created_by'         => Auth::id(),
            'updated_by'         => Auth::id(),
        ]);

        return redirect()->route('admin.salary-increments.index')
            ->with('success','Increment request submitted.');
    }

    public function edit($id)
    {
        $increment = SalaryIncrement::with([
            'employee.user',
            'oldReportingUser',
            'newReportingUser'
        ])->findOrFail($id);

        $managers = User::orderBy('name')->get();

        return view('admin.salary_increments.edit', [
            'increment' => $increment,
            'employee'  => $increment->employee,
            'managers'  => $managers
        ]);
    }

    public function update(Request $request, $id)
    {
        $inc = SalaryIncrement::findOrFail($id);

        $request->validate([
            'new_basic' => 'required|numeric'
        ]);

        $new_basic = $request->new_basic;
        $new_hra   = $request->new_hra ?? 0;
        $new_allow = $request->new_allowance ?? 0;

        $other_json = $request->other_allowances_json ?? "{}";
        $decoded_other = json_decode($other_json, true);

        $extra = 0;
        if (is_array($decoded_other)) {
            foreach ($decoded_other as $v) {
                $extra += floatval($v);
            }
        }

        $new_gross = $new_basic + $new_hra + $new_allow + $extra;

        $inc->update([
            'new_basic'          => $new_basic,
            'new_hra'            => $new_hra,
            'new_allowance'      => $new_allow,
            'new_gross_salary'   => $new_gross,
            'new_department'     => $request->new_department,
            'new_position'       => $request->new_position,
            'new_reporting_to'   => $request->new_reporting_to,
            'increment_month'    => $request->increment_month,
            'remarks'            => $request->remarks,
            'other_allowances_json' => $other_json,
            'updated_by'         => Auth::id(),
        ]);

        return redirect()->route('admin.salary-increments.index')
            ->with('success','Increment updated.');
    }

    public function approve($id)
    {
        $inc = SalaryIncrement::with('employee')->findOrFail($id);

        $inc->update([
            'status'       => 'approved',
            'approved_by'  => Auth::id(),
            'approved_at'  => now(),
        ]);

        $emp = $inc->employee;

        $emp->update([
            'basic_salary' => $inc->new_basic,
            'hra' => $inc->new_hra,
            'other_allowances' => $inc->new_allowance,
            'net_salary' => $inc->new_gross_salary,
            'department' => $inc->new_department,
            'position' => $inc->new_position,
            'reporting_to' => $inc->new_reporting_to,
            'other_allowances_json' => $inc->other_allowances_json,
        ]);

        return back()->with('success','Increment Approved & Employee updated.');
    }





    /* ============================================================
       REJECT INCREMENT
    ============================================================ */
    public function reject($id)
    {
        $inc = SalaryIncrement::findOrFail($id);

        $inc->status = 'rejected';
        $inc->approved_by = Auth::id();
        $inc->approved_at = now();
        $inc->save();

        return back()->with('success','Increment Rejected.');
    }



    /* ============================================================
       DOWNLOAD PDF LETTER
    ============================================================ */
public function downloadLetter($id)
{
    $inc = SalaryIncrement::with('employee.user')->findOrFail($id);
    $employee = $inc->employee;
    $user = $employee->user ?? null;

    /* ------------------------------
       Company Logo
    ------------------------------ */
    $companyLogoPath = public_path('logo.png');
    $companyLogoUrl = file_exists($companyLogoPath) ? asset('logo.png') : null;


    /* ------------------------------
       EMPLOYEE PROFILE IMAGE
       Priority:
       1) employee->photo (uploads/)
       2) user media library
    ------------------------------ */
    $employeeImageUrl = null;

    // 1️⃣ From employee table (file)
    if (!empty($employee->photo)) {
        $path = public_path('uploads/employee_photos/' . $employee->photo);
        if (file_exists($path)) {
            $employeeImageUrl = asset('uploads/employee_photos/' . $employee->photo);
        }
    }

    // 2️⃣ From user media library (Spatie)
    if (!$employeeImageUrl && $user && $user->image) {
        $employeeImageUrl = $user->image->preview ?? $user->image->url;
    }


    /* ------------------------------
       SIGNATURE
       Priority:
       1) employee->signature (file)
       2) user media library ('signature')
    ------------------------------ */
    $signatureUrl = null;

    // 1️⃣ Signature stored in Employee table
    if (!empty($employee->signature)) {
        $sigPath = public_path('uploads/employee_signatures/' . $employee->signature);
        if (file_exists($sigPath)) {
            $signatureUrl = asset('uploads/employee_signatures/' . $employee->signature);
        }
    }

    // 2️⃣ Signature from User (Spatie Media)
    if (!$signatureUrl && $user) {
        $sigMedia = $user->getMedia('signature')->last();
        if ($sigMedia) {
            $signatureUrl = $sigMedia->getUrl();
        }
    }


    /* ------------------------------
       RETURN LETTER VIEW (HTML)
       PDF will be generated via JS
    ------------------------------ */
    return view('admin.salary_increments.letter_pdf', [
        'increment'         => $inc,
        'employee'          => $employee,
        'user'              => $user,
        'companyLogoUrl'    => $companyLogoUrl,
        'employeeImageUrl'  => $employeeImageUrl,
        'signatureUrl'      => $signatureUrl,
        'companyName'       => 'Eemotrack India',
    ]);
}

}
