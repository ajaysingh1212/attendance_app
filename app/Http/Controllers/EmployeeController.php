<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeStatusLog;
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
    /* ══════════════════════════════════════════════════════
       INDEX
    ══════════════════════════════════════════════════════ */
    public function index()
    {
        $user    = Auth::user();
        $isAdmin = $user && method_exists($user, 'roles')
                   && $user->roles()->where('title', 'Admin')->exists();

        if ($isAdmin) {
            $employees = Employee::with('user', 'branch')->get();
        } else {
            $employees = Employee::with('user', 'branch')
                ->where('user_id', $user->id)
                ->get();
        }

        // Stats for header cards
        $stats = [
            'total'      => $employees->count(),
            'active'     => $employees->where('status', 'Active')->count(),
            'inactive'   => $employees->whereIn('status', EmployeeStatusLog::inactiveStatuses())->count(),
            'pending'    => $employees->where('status_change_pending', true)->count(),
        ];

        return view('admin.payroll.index', compact('employees', 'isAdmin', 'stats'));
    }

    /* ══════════════════════════════════════════════════════
       ID CARD
    ══════════════════════════════════════════════════════ */
    public function id()
    {
        $employees = Employee::with('user')->get();
        return view('admin.payroll.idcard', compact('employees'));
    }

    /* ══════════════════════════════════════════════════════
       CREATE
    ══════════════════════════════════════════════════════ */
    public function create()
    {
        $users    = User::all();
        $branches = Branch::all();
        return view('admin.payroll.create', compact('users', 'branches'));
    }

    /* ══════════════════════════════════════════════════════
       STORE
    ══════════════════════════════════════════════════════ */
    public function store(Request $request)
    {
        $isAdmin = auth()->user()->roles()->where('title', 'Admin')->exists();

        $validated = $request->validate([
            'user_id'                    => 'nullable|exists:users,id',
            'branch_id'                  => 'required',
            'employee_code'              => 'required|unique:employees,employee_code',
            'full_name'                  => 'required|string|max:255',
            'email'                      => 'nullable|email',
            'phone'                      => 'nullable|string|max:20',
            'employee_type'              => 'nullable|string|max:50',
            'employee_duration_months'   => 'nullable|integer|min:1',
            'date_of_birth'              => 'nullable|date',
            'anniversary_date'           => 'nullable|date',
            'special_terms'              => 'nullable|string',
            'ifsc_code'                  => 'nullable|string|max:20',
            'bank_name'                  => 'nullable|string|max:100',
            'bank_address'               => 'nullable|string|max:255',
            'account_number'             => 'nullable|string|max:50',
            'pan_number'                 => 'nullable|string|max:20',
            'aadhaar_number'             => 'nullable|string|max:20',
            'payment_mode'               => 'nullable|string|max:20',
            'work_start_time'            => 'nullable',
            'work_end_time'              => 'nullable',
            'working_hours'              => 'nullable|string|max:10',
            'weekly_off_day'             => 'nullable|string|max:20',
            'attendance_source'          => 'nullable|string|max:20',
            'attendance_radius_meter'    => 'nullable|numeric',
            'basic_salary'               => 'nullable|numeric',
            'hra'                        => 'nullable|numeric',
            'deductions'                 => 'nullable|numeric',
            'net_salary'                 => 'nullable|numeric',
            'other_allowances_json'      => 'nullable|json',
            'date_of_joining'            => 'nullable|date',
            'position'                   => 'nullable|string|max:100',
            'department'                 => 'nullable|string|max:100',
            'reporting_to'               => 'nullable|exists:users,id',
            'status'                     => 'nullable|string|max:20',
            'profile_photo'              => 'nullable|file|max:5120',
            'cv'                         => 'nullable|file|max:5120',
            'offer_letter'               => 'nullable|file|max:5120',
            'aadhaar_front'              => 'nullable|file|max:5120',
            'aadhaar_back'               => 'nullable|file|max:5120',
            'pan_card'                   => 'nullable|file|max:5120',
            'marksheet'                  => 'nullable|file|max:5120',
            'certificate'                => 'nullable|file|max:5120',
            'passbook'                   => 'nullable|file|max:5120',
            'photo'                      => 'nullable|file|max:5120',
            'other_document'             => 'nullable|file|max:5120',
            'signature'                  => 'nullable|file|max:5120',
            'experience_letter'          => 'nullable|file|max:5120',
        ]);

        if ($validated['employee_type'] === 'Permanent') {
            $validated['employee_duration_months'] = null;
        }

        $allowanceFields = [
            'travel_allowance','meal_allowance','uniform_allowance',
            'medical_allowance','housing_allowance','transport_allowance','special_allowance',
        ];
        $allowances = [];
        foreach ($allowanceFields as $field) {
            $allowances[$field] = $request->input($field, 0);
        }
        $validated['other_allowances_json'] = json_encode($allowances);
        $validated['other_allowances']      = array_sum($allowances);

        if (!$isAdmin) {
            unset($validated['special_terms']);
        }

        $validated['company_id']        = $request->branch_id;
        $validated['document_verified'] = 'pending';

        $employee = Employee::create($validated);

        $fileFields = [
            'profile_photo','signature_image','cv','offer_letter','aadhaar_front',
            'aadhaar_back','pan_card','marksheet','certificate','passbook',
            'photo','other_document','signature','experience_letter',
        ];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $file     = $request->file($field);
                $filename = $field.'_'.time().'_'.$file->getClientOriginalName();
                $path     = $file->storeAs("uploads/employees/{$employee->id}", $filename, 'public');
                $employee->{$field} = $path;
            }
        }
        $employee->save();

        // Log initial status
        if (!empty($validated['status'])) {
            EmployeeStatusLog::create([
                'employee_id' => $employee->id,
                'old_status'  => null,
                'new_status'  => $validated['status'],
                'changed_by'  => auth()->id(),
                'changed_at'  => now(),
                'remarks'     => 'Initial status on creation.',
            ]);
        }

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee created successfully.');
    }

    /* ══════════════════════════════════════════════════════
       UPDATE  — with status change logging & approval flow
    ══════════════════════════════════════════════════════ */
    public function update(Request $request, Employee $employee)
    {
        $isAdmin = auth()->user()->roles()->where('title', 'Admin')->exists();

        $validated = $request->validate([
            'user_id'                  => 'required|exists:users,id',
            'full_name'                => 'required|string|max:255',
            'email'                    => 'nullable|email',
            'phone'                    => 'nullable|string|max:20',
            'branch_id'                => 'required',
            'employee_type'            => 'nullable|string|max:50',
            'employee_duration_months' => 'nullable|integer|min:1',
            'date_of_birth'            => 'nullable|date',
            'anniversary_date'         => 'nullable|date',
            'special_terms'            => 'nullable|string',
            'basic_salary'             => 'nullable|numeric',
            'hra'                      => 'nullable|numeric',
            'deductions'               => 'nullable|numeric',
            'net_salary'               => 'nullable|numeric',
            'other_allowances_json'    => 'nullable|json',
            'date_of_joining'          => 'nullable|date',
            'position'                 => 'nullable|string|max:100',
            'department'               => 'nullable|string|max:100',
            'reporting_to'             => 'nullable|exists:users,id',
            'status'                   => 'nullable|string|max:50',
            'profile_photo'            => 'nullable|file|max:5120',
            'cv'                       => 'nullable|file|max:5120',
            'offer_letter'             => 'nullable|file|max:5120',
            'aadhaar_front'            => 'nullable|file|max:5120',
            'aadhaar_back'             => 'nullable|file|max:5120',
            'pan_card'                 => 'nullable|file|max:5120',
            'marksheet'                => 'nullable|file|max:5120',
            'certificate'              => 'nullable|file|max:5120',
            'passbook'                 => 'nullable|file|max:5120',
            'photo'                    => 'nullable|file|max:5120',
            'other_document'           => 'nullable|file|max:5120',
            'signature'                => 'nullable|file|max:5120',
            'experience_letter'        => 'nullable|file|max:5120',
        ]);

        if ($validated['employee_type'] === 'Permanent') {
            $validated['employee_duration_months'] = null;
        }

        if ($request->filled('other_allowances_json')) {
            $json = json_decode($request->other_allowances_json, true);
            $validated['other_allowances']      = array_sum($json);
            $validated['other_allowances_json'] = json_encode($json);
        }

        if (!$isAdmin) {
            unset($validated['special_terms']);
        }

        /* ── 📋 Capture OLD status BEFORE update ── */
        $oldStatus = $employee->status;
        $newStatus = $request->input('status');

        /* ── File uploads ─────────────────────── */
        $fileFields = [
            'profile_photo','signature_image','cv','offer_letter','aadhaar_front',
            'aadhaar_back','pan_card','marksheet','certificate','passbook',
            'photo','other_document','signature','experience_letter',
        ];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                if ($employee->{$field}) {
                    Storage::disk('public')->delete($employee->{$field});
                }
                $file     = $request->file($field);
                $filename = $field.'_'.time().'_'.$file->getClientOriginalName();
                $validated[$field] = $file->storeAs(
                    "uploads/employees/{$employee->id}", $filename, 'public'
                );
            }
        }

        $validated['company_id'] = $request->branch_id;

        /* ── 🔄 Detect status change ──────────────────── */
        $statusChanged = $newStatus && $oldStatus !== $newStatus;

        if ($statusChanged) {
            // Mark as pending approval (another admin must approve)
            $validated['status_change_pending'] = true;
        }

        $employee->update($validated);

        /* ── 📝 Log the status change ─────────────────── */
        if ($statusChanged) {
            $inactiveStatuses = EmployeeStatusLog::inactiveStatuses();
            $isReactivation   = in_array($oldStatus, $inactiveStatuses) && $newStatus === 'Active';

            EmployeeStatusLog::create([
                'employee_id'    => $employee->id,
                'old_status'     => $oldStatus,
                'new_status'     => $newStatus,
                'changed_by'     => auth()->id(),
                'reactivated_by' => $isReactivation ? auth()->id() : null,
                'remarks'        => $request->input('status_change_remarks', null),
                'changed_at'     => now(),
                'reactivated_at' => $isReactivation ? now() : null,
            ]);
        }

        return redirect()->back()
            ->with('success', 'Employee updated successfully.'
                . ($statusChanged ? " Status changed from '{$oldStatus}' to '{$newStatus}' — pending approval." : ''));
    }

    /* ══════════════════════════════════════════════════════
       QUICK STATUS CHANGE  (AJAX from employee index)
    ══════════════════════════════════════════════════════ */
    public function changeStatus(Request $request, Employee $employee)
    {
        $request->validate([
            'status'  => 'required|string|in:Active,Resigned,Terminated,Suspended',
            'remarks' => 'nullable|string|max:255',
        ]);

        $oldStatus = $employee->status;
        $newStatus = $request->status;

        if ($oldStatus === $newStatus) {
            return response()->json(['success' => false, 'message' => 'Status is already '.$newStatus]);
        }

        $isReactivation = in_array($oldStatus, EmployeeStatusLog::inactiveStatuses())
                          && $newStatus === 'Active';

        $employee->update([
            'status'                => $newStatus,
            'status_change_pending' => !$isReactivation, // reactivation needs no further approval
        ]);

        EmployeeStatusLog::create([
            'employee_id'    => $employee->id,
            'old_status'     => $oldStatus,
            'new_status'     => $newStatus,
            'changed_by'     => auth()->id(),
            'approved_by'    => $isReactivation ? auth()->id() : null,
            'reactivated_by' => $isReactivation ? auth()->id() : null,
            'remarks'        => $request->input('remarks', 'Status changed via dashboard.'),
            'changed_at'     => now(),
            'approved_at'    => $isReactivation ? now() : null,
            'reactivated_at' => $isReactivation ? now() : null,
        ]);

        return response()->json([
            'success'    => true,
            'message'    => "Status changed from '{$oldStatus}' to '{$newStatus}'.",
            'new_status' => $newStatus,
        ]);
    }

    /* ══════════════════════════════════════════════════════
       APPROVE STATUS CHANGE  (AJAX — second admin)
    ══════════════════════════════════════════════════════ */
    public function approveStatus(Employee $employee)
    {
        $log = EmployeeStatusLog::where('employee_id', $employee->id)
                                ->whereNull('approved_by')
                                ->latest()
                                ->first();

        if (!$log) {
            return response()->json(['success' => false, 'message' => 'Nothing to approve.']);
        }

        $log->update([
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $employee->update(['status_change_pending' => false]);

        return response()->json([
            'success' => true,
            'message' => "Status change approved.",
        ]);
    }

    /* ══════════════════════════════════════════════════════
       OFFER LETTER VIEW
    ══════════════════════════════════════════════════════ */
    public function offerLetterView(Employee $employee)
    {
        $user          = User::findOrFail($employee->user_id);
        $termsAccepted = (bool) $user->terms_accepted;
        $acceptImage   = $user->getFirstMedia('accept_image');
        $signImage     = $user->getFirstMedia('sign_image');
        $userImage     = $user->getFirstMedia('image');
        $company       = Branch::find($employee->company_id);

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
            'employee','user','termsAccepted','acceptImage',
            'signImage','userImage','company','documents'
        ));
    }

    /* ══════════════════════════════════════════════════════
       SHOW
    ══════════════════════════════════════════════════════ */
    public function show(Employee $employee)
    {
        $employeeFolder = "uploads/employees/{$employee->id}";
        $documents = Storage::disk('public')->exists($employeeFolder)
            ? Storage::disk('public')->allFiles($employeeFolder)
            : [];

        return view('admin.payroll.show', compact('employee', 'documents'));
    }

    /* ══════════════════════════════════════════════════════
       EDIT
    ══════════════════════════════════════════════════════ */
    public function edit(Employee $employee)
    {
        $user    = Auth::user();
        $isAdmin = $user->roles()->where('title', 'Admin')->exists();

        if (!$isAdmin && $employee->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        if (!$isAdmin && $employee->document_verified == 'verified') {
            return redirect()->route('admin.employees.index')
                ->with('error', 'Documents verified. You cannot update now.');
        }

        $users    = User::all();
        $branches = Branch::all();

        // Status logs for audit trail display
        $statusLogs = $employee->statusLogs()
                               ->with('changedBy', 'approvedBy', 'reactivatedBy')
                               ->latest()
                               ->get();

        return view('admin.payroll.edit', compact(
            'employee','users','branches','isAdmin','statusLogs'
        ));
    }

    /* ══════════════════════════════════════════════════════
       DESTROY
    ══════════════════════════════════════════════════════ */
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee deleted successfully.');
    }

    /* ══════════════════════════════════════════════════════
       DOWNLOAD PDF
    ══════════════════════════════════════════════════════ */
    public function downloadPdf(Employee $employee)
    {
        $logoPath   = public_path('logo.jpg');
        $logoBase64 = base64_encode(file_get_contents($logoPath));
        $logoMime   = mime_content_type($logoPath);
        $logoSrc    = 'data:'.$logoMime.';base64,'.$logoBase64;

        $documents = [];
        $basePath  = 'storage/uploads/employees/'.$employee->id;
        $folderPath = public_path($basePath);

        if (File::exists($folderPath)) {
            foreach (File::files($folderPath) as $file) {
                $filename  = $file->getFilename();
                $extension = pathinfo($filename, PATHINFO_EXTENSION);
                $labelKey  = preg_replace('/[_-]?\d{8,}$/', '', pathinfo($filename, PATHINFO_FILENAME));
                $label     = ucwords(str_replace(['_','-'], ' ', $labelKey));

                $documents[] = [
                    'label'         => $label,
                    'path'          => $basePath.'/'.$filename,
                    'extension'     => strtolower($extension),
                    'absolute_path' => $file->getPathname(),
                ];
            }
        }

        $pdf = PDF::loadView('admin.payroll.profile-pdf', compact('employee','documents','logoSrc'));
        return $pdf->download('Employee_Profile_'.$employee->id.'.pdf');
    }
}
