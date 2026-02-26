<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\PayrollAdjustment;
use App\Models\User;
use Illuminate\Support\Str;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use PDF;
use Illuminate\Support\Facades\File;

class EmployeeController extends Controller
{
public function index()
{
    $user = Auth::user();

    // Check Admin role
    $isAdmin = false;
    if ($user && method_exists($user, 'roles')) {
        $isAdmin = $user->roles()->where('title', 'Admin')->exists();
    }

    if ($isAdmin) {
        // Admin → all employees
        $employees = Employee::with('user', 'branch')->get();
    } else {
        // Non-admin → only own employee record
        $employees = Employee::with('user', 'branch')
            ->where('user_id', $user->id)
            ->get();
    }

    return view('admin.payroll.index', compact('employees', 'isAdmin'));
}
   public function id()
{
    $employees = Employee::with('user')->get(); // or any specific query
    return view('admin.payroll.idcard', compact('employees'));
}
public function create()
{
    $users = User::all();
    $branches = Branch::all(); // fetch all branches

    return view('admin.payroll.create', compact('users', 'branches'));
}

public function store(Request $request)
{
    $isAdmin = auth()->user()->roles()->where('title', 'Admin')->exists();

    $validated = $request->validate([
        'user_id' => 'nullable|exists:users,id',
        'branch_id' => 'required',
        'employee_code' => 'required|unique:employees,employee_code',
        'full_name' => 'required|string|max:255',
        'email' => 'nullable|email',
        'phone' => 'nullable|string|max:20',

        // Employee Type
        'employee_type' => 'nullable|string|max:50',
        'employee_duration_months' => 'nullable|integer|min:1',

        // Additional Details
        'date_of_birth' => 'nullable|date',
        'anniversary_date' => 'nullable|date',
        'special_terms' => 'nullable|string',

        // Bank
        'ifsc_code' => 'nullable|string|max:20',
        'bank_name' => 'nullable|string|max:100',
        'bank_address' => 'nullable|string|max:255',
        'account_number' => 'nullable|string|max:50',
        'pan_number' => 'nullable|string|max:20',
        'aadhaar_number' => 'nullable|string|max:20',
        'payment_mode' => 'nullable|string|max:20',

        // Work
        'work_start_time' => 'nullable',
        'work_end_time' => 'nullable',
        'working_hours' => 'nullable|string|max:10',
        'weekly_off_day' => 'nullable|string|max:20',
        'attendance_source' => 'nullable|string|max:20',
        'attendance_radius_meter' => 'nullable|numeric',

        // Salary
        'basic_salary' => 'nullable|numeric',
        'hra' => 'nullable|numeric',
        'deductions' => 'nullable|numeric',
        'net_salary' => 'nullable|numeric',
        'other_allowances_json' => 'nullable|json',

        // Employment
        'date_of_joining' => 'nullable|date',
        'position' => 'nullable|string|max:100',
        'department' => 'nullable|string|max:100',
        'reporting_to' => 'nullable|exists:users,id',
        'status' => 'nullable|string|max:20',

        // Files
        'profile_photo' => 'nullable|file|max:5120',
        'signature_image' => 'nullable|file|max:5120',
        'cv' => 'nullable|file|max:5120',
        'offer_letter' => 'nullable|file|max:5120',
        'aadhaar_front' => 'nullable|file|max:5120',
        'aadhaar_back' => 'nullable|file|max:5120',
        'pan_card' => 'nullable|file|max:5120',
        'marksheet' => 'nullable|file|max:5120',
        'certificate' => 'nullable|file|max:5120',
        'passbook' => 'nullable|file|max:5120',
        'photo' => 'nullable|file|max:5120',
        'other_document' => 'nullable|file|max:5120',
        'signature' => 'nullable|file|max:5120',
        'experience_letter' => 'nullable|file|max:5120',
    ]);

    /* ✅ Employee Type Logic */
    if ($validated['employee_type'] === 'Permanent') {
        $validated['employee_duration_months'] = null;
    }

    /* ✅ Allowances */
    $allowanceFields = [
        'travel_allowance', 'meal_allowance', 'uniform_allowance',
        'medical_allowance', 'housing_allowance', 'transport_allowance', 'special_allowance'
    ];

    $allowances = [];
    foreach ($allowanceFields as $field) {
        $allowances[$field] = $request->input($field, 0);
    }

    $validated['other_allowances_json'] = json_encode($allowances);
    $validated['other_allowances'] = array_sum($allowances);

    /* ✅ Admin-only field */
    if (!$isAdmin) {
        unset($validated['special_terms']);
    }

    $validated['company_id'] = $request->branch_id;
    $validated['document_verified'] = 'pending';

    $employee = Employee::create($validated);

    /* ✅ File Uploads */
    $fileFields = [
        'profile_photo','signature_image','cv','offer_letter','aadhaar_front',
        'aadhaar_back','pan_card','marksheet','certificate','passbook',
        'photo','other_document','signature','experience_letter'
    ];

    foreach ($fileFields as $field) {
        if ($request->hasFile($field)) {
            $file = $request->file($field);
            $filename = $field.'_'.time().'_'.$file->getClientOriginalName();
            $path = $file->storeAs("uploads/employees/{$employee->id}", $filename, 'public');
            $employee->{$field} = $path;
        }
    }

    $employee->save();

    return redirect()->route('admin.employees.index')
        ->with('success', 'Employee created successfully.');
}
public function update(Request $request, Employee $employee)
{
    $isAdmin = auth()->user()->roles()->where('title', 'Admin')->exists();

    $validated = $request->validate([
        'user_id' => 'required|exists:users,id',
        'full_name' => 'required|string|max:255',
        'email' => 'nullable|email',
        'phone' => 'nullable|string|max:20',
        'branch_id' => 'required',

        // Employee Type
        'employee_type' => 'nullable|string|max:50',
        'employee_duration_months' => 'nullable|integer|min:1',

        // Additional
        'date_of_birth' => 'nullable|date',
        'anniversary_date' => 'nullable|date',
        'special_terms' => 'nullable|string',

        // Salary
        'basic_salary' => 'nullable|numeric',
        'hra' => 'nullable|numeric',
        'deductions' => 'nullable|numeric',
        'net_salary' => 'nullable|numeric',
        'other_allowances_json' => 'nullable|json',

        // Employment
        'date_of_joining' => 'nullable|date',
        'position' => 'nullable|string|max:100',
        'department' => 'nullable|string|max:100',
        'reporting_to' => 'nullable|exists:users,id',
        'status' => 'nullable|string|max:50',

        // Files
        'profile_photo' => 'nullable|file|max:5120',
        'signature_image' => 'nullable|file|max:5120',
        'cv' => 'nullable|file|max:5120',
        'offer_letter' => 'nullable|file|max:5120',
        'aadhaar_front' => 'nullable|file|max:5120',
        'aadhaar_back' => 'nullable|file|max:5120',
        'pan_card' => 'nullable|file|max:5120',
        'marksheet' => 'nullable|file|max:5120',
        'certificate' => 'nullable|file|max:5120',
        'passbook' => 'nullable|file|max:5120',
        'photo' => 'nullable|file|max:5120',
        'other_document' => 'nullable|file|max:5120',
        'signature' => 'nullable|file|max:5120',
        'experience_letter' => 'nullable|file|max:5120',
    ]);

    /* ✅ Employee Type Logic */
    if ($validated['employee_type'] === 'Permanent') {
        $validated['employee_duration_months'] = null;
    }

    /* ✅ Allowances */
    if ($request->filled('other_allowances_json')) {
        $json = json_decode($request->other_allowances_json, true);
        $validated['other_allowances'] = array_sum($json);
        $validated['other_allowances_json'] = json_encode($json);
    }

    /* ✅ Admin-only */
    if (!$isAdmin) {
        unset($validated['special_terms']);
    }

    /* ✅ Files */
    $fileFields = [
        'profile_photo','signature_image','cv','offer_letter','aadhaar_front',
        'aadhaar_back','pan_card','marksheet','certificate','passbook',
        'photo','other_document','signature','experience_letter'
    ];

    foreach ($fileFields as $field) {
        if ($request->hasFile($field)) {
            if ($employee->{$field}) {
                Storage::disk('public')->delete($employee->{$field});
            }

            $file = $request->file($field);
            $filename = $field.'_'.time().'_'.$file->getClientOriginalName();
            $validated[$field] = $file->storeAs(
                "uploads/employees/{$employee->id}", 
                $filename, 
                'public'
            );
        }
    }

    $validated['company_id'] = $request->branch_id;

    $employee->update($validated);

    return redirect()->back()
        ->with('success', 'Employee updated successfully.');
}


public function offerLetterView(Employee $employee)
{
    /* =========================
       USER FETCH
    ========================= */
    $user = User::findOrFail($employee->user_id);

    /* =========================
       TERMS STATUS
    ========================= */
    $termsAccepted = (bool) $user->terms_accepted;

    /* =========================
       USER MEDIA (Spatie)
    ========================= */
    $acceptImage = $user->getFirstMedia('accept_image'); // camera photo
    $signImage   = $user->getFirstMedia('sign_image');   // signature

    /* =========================
       USER PROFILE IMAGE (if any)
    ========================= */
    $userImage = $user->getFirstMedia('image');

    /* =========================
       COMPANY / BRANCH
    ========================= */
    $company = Branch::find($employee->company_id);

    /* =========================
       EMPLOYEE DOCUMENTS
    ========================= */
    $documents = [
        'profile_photo'     => $employee->profile_photo,
        'signature_image'   => $employee->signature_image,
        'cv'                => $employee->cv,
        'offer_letter'      => $employee->offer_letter,
        'aadhaar_front'     => $employee->aadhaar_front,
        'aadhaar_back'      => $employee->aadhaar_back,
        'pan_card'          => $employee->pan_card,
        'marksheet'         => $employee->marksheet,
        'certificate'       => $employee->certificate,
        'passbook'          => $employee->passbook,
        'photo'             => $employee->photo,
        'other_document'    => $employee->other_document,
        'signature'         => $employee->signature,
        'experience_letter' => $employee->experience_letter,
    ];

    return view('admin.offerletters.document', compact(
        'employee',
        'user',
        'termsAccepted',
        'acceptImage',
        'signImage',
        'userImage',
        'company',
        'documents'
    ));
}



public function show(Employee $employee)
{
    $employeeFolder = "uploads/employees/{$employee->id}";
    $documents = Storage::disk('public')->exists($employeeFolder)
        ? Storage::disk('public')->allFiles($employeeFolder)
        : [];

    return view('admin.payroll.show', compact('employee', 'documents'));
}
    
public function edit(Employee $employee)
{
    $user = Auth::user();

    // Check Admin
    $isAdmin = $user->roles()->where('title', 'Admin')->exists();

    // Non-admin can edit only own record
    if (!$isAdmin && $employee->user_id !== $user->id) {
        abort(403, 'Unauthorized');
    }

    // If non-admin & document verified → block edit
    if (!$isAdmin && $employee->document_verified == 'verified') {
        return redirect()->route('admin.employees.index')
            ->with('error', 'Documents verified. You cannot update now.');
    }

    $users = \App\Models\User::all();
    $branches = \App\Models\Branch::all();

    return view('admin.payroll.edit', compact(
        'employee',
        'users',
        'branches',
        'isAdmin'
    ));
}



public function destroy($id)
{
    $employee = Employee::findOrFail($id);
    $employee->delete();

    return redirect()->route('admin.employees.index')->with('success', 'Employee deleted successfully.');
}
public function downloadPdf(Employee $employee)
{
    $logoPath = public_path('logo.jpg');
$logoBase64 = base64_encode(file_get_contents($logoPath));
$logoMime = mime_content_type($logoPath);
$logoSrc = 'data:' . $logoMime . ';base64,' . $logoBase64;

    $documents = [];
    $basePath = 'storage/uploads/employees/' . $employee->id;
    $folderPath = public_path($basePath);

    if (File::exists($folderPath)) {
        $files = File::files($folderPath);

        foreach ($files as $file) {
            $filename = $file->getFilename();
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $labelKey = preg_replace('/[_-]?\d{8,}$/', '', pathinfo($filename, PATHINFO_FILENAME));
            $label = ucwords(str_replace(['_', '-'], ' ', $labelKey));

            $documents[] = [
                'label' => $label,
                'path' => $basePath . '/' . $filename,
                'extension' => strtolower($extension),
                'absolute_path' => $file->getPathname() // Add absolute path
            ];
        }
    }

    $pdf = Pdf::loadView('admin.payroll.profile-pdf', [
        'employee' => $employee,
        'documents' => $documents
        , 'logoSrc' => $logoSrc
    ]);

    return $pdf->download('Employee_Profile_' . $employee->id . '.pdf');
}


public function print(Employee $employee)
    {
        // Agar tum PDF banana chahte ho to yahan logic laga sakte ho
        // Filhal simple view render kar rahe hain
        return view('admin.payroll.print', compact('employee'));
    }




}
