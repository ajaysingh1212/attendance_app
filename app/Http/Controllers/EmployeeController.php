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
    
    $validated = $request->validate([
        'user_id' => 'nullable|exists:users,id',
        'branch_id' => 'required', // ✅ Add this
        'employee_code' => 'required|unique:employees,employee_code',
        'full_name' => 'required|string|max:255',
        'email' => 'nullable|email',
        'phone' => 'nullable|string|max:20',

        // Bank Details
        'ifsc_code' => 'nullable|string|max:20',
        'bank_name' => 'nullable|string|max:100',
        'bank_address' => 'nullable|string|max:255',
        'account_number' => 'nullable|string|max:50',
        'pan_number' => 'nullable|string|max:20',
        'aadhaar_number' => 'nullable|string|max:20',
        'payment_mode' => 'nullable|string|max:20',

        // Work Timing
        'work_start_time' => 'nullable',
        'work_end_time' => 'nullable',
        'working_hours' => 'nullable|string|max:10',
        'weekly_off_day' => 'nullable|string|max:20',
        'attendance_source' => 'nullable|string|max:20',
        'attendance_radius_meter' => 'nullable|numeric',

        // Salary Info
        'basic_salary' => 'nullable|numeric',
        'hra' => 'nullable|numeric',
        'other_allowances' => 'nullable|numeric',
        'deductions' => 'nullable|numeric',
        'net_salary' => 'nullable|numeric',

        // Employment Info
        'date_of_joining' => 'nullable|date',
        'position' => 'nullable|string|max:100',
        'department' => 'nullable|string|max:100',
        'reporting_to' => 'nullable|exists:users,id',
        'status' => 'nullable|string|max:20',
        'other_allowances_json' => 'nullable|json',

        // File Uploads
        'profile_photo'    => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp,svg,gif|max:5120',
        'signature_image'  => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp,svg,gif|max:5120',
        'cv'               => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp,svg,gif|max:5120',
        'offer_letter'     => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp,svg,gif|max:5120',
        'aadhaar_front'    => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp,svg,gif|max:5120',
        'aadhaar_back'     => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp,svg,gif|max:5120',
        'pan_card'         => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp,svg,gif|max:5120',
        'marksheet'        => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp,svg,gif|max:5120',
        'certificate'      => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp,svg,gif|max:5120',
        'passbook'         => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp,svg,gif|max:5120',
        'photo'            => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp,svg,gif|max:5120',
        'other_document'   => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp,svg,gif|max:5120',
        'signature'        => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp,svg,gif|max:5120',
        'experience_letter'=> 'nullable|file|mimes:jpg,jpeg,png,pdf,webp,svg,gif|max:5120',
        'document_verified'=> 'pending',
        'company_id'       => 'nullable',
        
    ]);

    // Step 1: Extract only non-file fields and create employee first
    $fileFields = [
        'profile_photo', 'signature_image', 'cv', 'offer_letter', 'aadhaar_front',
        'aadhaar_back', 'pan_card', 'marksheet', 'certificate', 'passbook',
        'photo', 'other_document', 'signature', 'experience_letter','document_verified','company_id'
    ];

    $employeeData = collect($validated)->except($fileFields)->toArray();
   // 🎯 Step 1.1: Handle Allowances
$allowanceFields = [
    'travel_allowance', 'meal_allowance', 'uniform_allowance', 
    'medical_allowance', 'housing_allowance', 'transport_allowance', 'special_allowance'
];

$allowancesData = [];
foreach ($allowanceFields as $field) {
    $allowancesData[$field] = $request->input($field, 0);
}

// Total allowances
$totalAllowances = array_sum($allowancesData);

// JSON & Total add in array
$employeeData['other_allowances_json'] = json_encode($allowancesData);
$employeeData['other_allowances'] = $totalAllowances;

$employeeData['document_verified'] = $request->input('document_verified', null);
$employeeData['company_id'] = $request->input('branch_id', null);
    $employee = Employee::create($employeeData); // ID generated here
    
    // Step 2: Handle file uploads and update employee
    foreach ($fileFields as $field) {
        if ($request->hasFile($field)) {
            $file = $request->file($field);
            $filename = $field . '_' . time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $folder = "uploads/employees/{$employee->id}";
            $path = $file->storeAs($folder, $filename, 'public');
            $employee->{$field} = $path;
        }
    }

    $employee->save();

    return redirect()->route('admin.employees.index')->with('success', 'Employee created successfully.');
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


public function update(Request $request, Employee $employee)
{
    
    $validated = $request->validate([
        'user_id' => 'required|exists:users,id',
        'employee_code' => 'nullable|string|max:255',
        'full_name' => 'required|string|max:255',
        'email' => 'nullable|email',
        'phone' => 'nullable|string|max:20',
        'bank_name' => 'nullable|string|max:255',
        'account_number' => 'nullable|string|max:50',
        'ifsc_code' => 'nullable|string|max:20',
        'pan_number' => 'nullable|string|max:20',
        'aadhaar_number' => 'nullable|string|max:20',
        'payment_mode' => 'nullable|string|max:100',
        'work_start_time' => 'nullable|date_format:H:i:s',
        'work_end_time' => 'nullable|date_format:H:i:s',
        'working_hours' => 'nullable|string|max:50',
        'weekly_off_day' => 'nullable|string|max:50',
        'attendance_source' => 'nullable|string|max:100',
        'attendance_radius_meter' => 'nullable|numeric|min:0',
        'basic_salary' => 'nullable|numeric',
        'hra' => 'nullable|numeric',
        'other_allowances' => 'nullable|numeric',
        'other_allowances_json' => 'nullable|string', // ✅ added
        'deductions' => 'nullable|numeric',
        'net_salary' => 'nullable|numeric',
        'date_of_joining' => 'nullable|date',
        'position' => 'nullable|string|max:100',
        'department' => 'nullable|string|max:100',
        'reporting_to' => 'nullable|exists:users,id',
        'status' => 'nullable|string|max:50',
        'branch_id' => 'required',

        // File validations
        'profile_photo' => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp|max:5120',
        'signature_image' => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp|max:5120',
        'cv' => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp|max:5120',
        'offer_letter' => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp|max:5120',
        'aadhaar_front' => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp|max:5120',
        'aadhaar_back' => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp|max:5120',
        'pan_card' => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp|max:5120',
        'marksheet' => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp|max:5120',
        'certificate' => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp|max:5120',
        'passbook' => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp|max:5120',
        'photo' => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp|max:5120',
        'other_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp|max:5120',
        'signature' => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp|max:5120',
        'exprience_letter' => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp|max:5120',
        'document_verified' => 'nullable',
    ]);

    /* ✅ Handle Allowances JSON */
    if ($request->filled('other_allowances_json')) {
        $json = json_decode($request->other_allowances_json, true);

        if (is_array($json)) {
            // Calculate total of all allowances
            $totalAllowances = array_sum(array_map('floatval', $json));

            $validated['other_allowances_json'] = json_encode($json, JSON_UNESCAPED_UNICODE);
            $validated['other_allowances'] = $totalAllowances;
        }
    }

    /* ✅ Handle file uploads */
    $fileFields = [
        'profile_photo', 'signature_image', 'cv', 'offer_letter', 'aadhaar_front',
        'aadhaar_back', 'pan_card', 'marksheet', 'certificate', 'passbook',
        'photo', 'other_document', 'signature', 'exprience_letter'
    ];

    foreach ($fileFields as $field) {
        if ($request->hasFile($field)) {
            // Delete old file if exists
            if ($employee->{$field} && Storage::disk('public')->exists($employee->{$field})) {
                Storage::disk('public')->delete($employee->{$field});
            }

            $file = $request->file($field);
            $filename = $field . '_' . time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $path = $file->storeAs('uploads/employees/' . $employee->id, $filename, 'public');
            $validated[$field] = $path;
        }
    }
    $validated['branch_id'] = $request->input('branch_id');
    $validated['document_verified'] = $request->input('document_verified', $employee->document_verified);
    /* ✅ Update employee */
    $employee->update($validated);

    return redirect()->back()->with('success', 'Employee updated successfully!');
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

    public function offerLetter()
{
    abort_if(Gate::denies('payroll_offer_letter'), Response::HTTP_FORBIDDEN, '403 Forbidden');

    // Example: get all employees for dropdown
    $employees = Employee::orderByDesc('id')->get();

    return view('admin.payroll.offer', compact('employees'));
}


}
