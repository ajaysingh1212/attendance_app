@extends('layouts.admin')

@section('content')

@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Str;

    $user    = Auth::user();
    $isAdmin = $user && method_exists($user, 'roles')
               && $user->roles()->where('title', 'Admin')->exists();

    $allowances = json_decode($employee->other_allowances_json, true) ?? [];

    $inactiveStatuses = ['Resigned','Terminated','Suspended'];
    $isInactive = in_array($employee->status, $inactiveStatuses);
@endphp

<div class="eedit-page">

    {{-- ── Page Header ── --}}
    <div class="eedit-header">
        <div class="eedit-header-left">
            <a href="{{ route('admin.employees.index') }}" class="back-btn">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h3 class="eedit-title">Edit Employee</h3>
                <p class="eedit-sub">
                    {{ $employee->employee_code }} · {{ $employee->position ?? 'No position' }}
                    @if($isInactive)
                        <span class="badge-inactive">{{ $employee->status }}</span>
                    @endif
                </p>
            </div>
        </div>
        <div class="eedit-avatar">
            {{ strtoupper(substr($employee->full_name ?? 'E', 0, 2)) }}
        </div>
    </div>

    {{-- ── Alerts ── --}}
    @if(session('success'))
        <div class="eedit-alert eedit-alert-success">✅ {{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="eedit-alert eedit-alert-danger">
            <strong>Please fix the following errors:</strong>
            <ul style="margin:8px 0 0;padding-left:18px;">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- ── Tabs ── --}}
    <div class="eedit-tabs">
        <button class="etab active" onclick="showTab('personal', this)">👤 Personal</button>
        @if($isAdmin)
        <button class="etab" onclick="showTab('bank', this)">🏦 Bank</button>
        <button class="etab" onclick="showTab('work', this)">⏱ Work</button>
        <button class="etab" onclick="showTab('salary', this)">💰 Salary</button>
        <button class="etab" onclick="showTab('employment', this)">💼 Employment</button>
        @endif
        <button class="etab" onclick="showTab('documents', this)">📁 Documents</button>
        @if($isAdmin)
        <button class="etab" onclick="showTab('audit', this)">
            📋 Audit
            @if($employee->status_change_pending)
                <span class="etab-badge">!</span>
            @endif
        </button>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.employees.update', $employee->id) }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        {{-- ══════════════════════════════════════════
             TAB: PERSONAL
        ══════════════════════════════════════════ --}}
        <div id="tab-personal" class="etab-panel active">
            <div class="eedit-grid-2">
                <div class="eedit-card">
                    <div class="eedit-card-head">👤 Personal Information</div>
                    <div class="eedit-card-body">

                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Linked User</label>
                                <select class="field-input" name="user_id" id="user_id">
                                    <option value="">Select User</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}"
                                            data-name="{{ $u->name }}"
                                            data-email="{{ $u->email }}"
                                            data-phone="{{ $u->number ?? '' }}"
                                            {{ $employee->user_id == $u->id ? 'selected' : '' }}>
                                            {{ $u->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field-group">
                                <label class="field-label">Employee Code</label>
                                <div style="display:flex;gap:8px;">
                                    <input class="field-input" id="employee_code" name="employee_code"
                                        value="{{ old('employee_code', $employee->employee_code) }}" required>
                                    <button type="button" class="btn-gen" onclick="generateEmployeeCode()">Gen</button>
                                </div>
                            </div>
                        </div>

                        <div class="field-group">
                            <label class="field-label">Full Name</label>
                            <input class="field-input" id="full_name" name="full_name"
                                value="{{ old('full_name', $employee->full_name) }}" required>
                        </div>

                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Email</label>
                                <input class="field-input" id="email" name="email" type="email"
                                    value="{{ old('email', $employee->email) }}">
                            </div>
                            <div class="field-group">
                                <label class="field-label">Phone</label>
                                <input class="field-input" id="phone" name="phone"
                                    value="{{ old('phone', $employee->phone) }}">
                            </div>
                        </div>

                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Employee Type</label>
                                <select class="field-input" name="employee_type" id="employee_type">
                                    <option value="">Select</option>
                                    @foreach(['Permanent','Provisional','Intern','Contract'] as $type)
                                        <option value="{{ $type }}"
                                            {{ old('employee_type', $employee->employee_type) == $type ? 'selected' : '' }}>
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field-group" id="emp_duration_box" style="display:none;">
                                <label class="field-label">Duration (months)</label>
                                <input class="field-input" type="number" name="employee_duration_months"
                                    value="{{ old('employee_duration_months', $employee->employee_duration_months) }}">
                            </div>
                        </div>

                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Date of Birth</label>
                                <input class="field-input" type="date" name="date_of_birth"
                                    value="{{ old('date_of_birth', $employee->date_of_birth) }}">
                            </div>
                            <div class="field-group">
                                <label class="field-label">Anniversary Date</label>
                                <input class="field-input" type="date" name="anniversary_date"
                                    value="{{ old('anniversary_date', $employee->anniversary_date) }}">
                            </div>
                        </div>

                        @if($isAdmin)
                        <div class="field-group">
                            <label class="field-label">Special Terms <span class="badge-admin">Admin</span></label>
                            <textarea class="field-input" name="special_terms" rows="3">{{ old('special_terms', $employee->special_terms) }}</textarea>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             TAB: BANK
        ══════════════════════════════════════════ --}}
        @if($isAdmin)
        <div id="tab-bank" class="etab-panel">
            <div class="eedit-grid-2">
                <div class="eedit-card">
                    <div class="eedit-card-head">🏦 Bank Details</div>
                    <div class="eedit-card-body">
                        <div class="field-group">
                            <label class="field-label">IFSC Code</label>
                            <input class="field-input" name="ifsc_code" id="ifsc_code"
                                value="{{ old('ifsc_code', $employee->ifsc_code) }}" placeholder="e.g. SBIN0001234">
                        </div>
                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Bank Name</label>
                                <input class="field-input" name="bank_name" id="bank_name"
                                    value="{{ old('bank_name', $employee->bank_name) }}" readonly>
                            </div>
                            <div class="field-group">
                                <label class="field-label">Bank Address</label>
                                <input class="field-input" name="bank_address" id="bank_address"
                                    value="{{ old('bank_address', $employee->bank_address) }}" readonly>
                            </div>
                        </div>
                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Account Number</label>
                                <input class="field-input" name="account_number"
                                    value="{{ old('account_number', $employee->account_number) }}">
                            </div>
                            <div class="field-group">
                                <label class="field-label">PAN Number</label>
                                <input class="field-input" name="pan_number"
                                    value="{{ old('pan_number', $employee->pan_number) }}">
                            </div>
                        </div>
                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Aadhaar Number</label>
                                <input class="field-input" name="aadhaar_number"
                                    value="{{ old('aadhaar_number', $employee->aadhaar_number) }}">
                            </div>
                            <div class="field-group">
                                <label class="field-label">Payment Mode</label>
                                <select class="field-input" name="payment_mode">
                                    @foreach(['Bank','Cash','UPI'] as $pm)
                                        <option value="{{ $pm }}" {{ $employee->payment_mode == $pm ? 'selected' : '' }}>{{ $pm }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             TAB: WORK TIMING
        ══════════════════════════════════════════ --}}
        <div id="tab-work" class="etab-panel">
            <div class="eedit-grid-2">
                <div class="eedit-card">
                    <div class="eedit-card-head">⏱ Work Timing & Attendance</div>
                    <div class="eedit-card-body">
                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Start Time</label>
                                <input class="field-input" type="time" name="work_start_time"
                                    value="{{ old('work_start_time', $employee->work_start_time) }}">
                            </div>
                            <div class="field-group">
                                <label class="field-label">End Time</label>
                                <input class="field-input" type="time" name="work_end_time"
                                    value="{{ old('work_end_time', $employee->work_end_time) }}">
                            </div>
                        </div>
                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Working Hours</label>
                                <input class="field-input" name="working_hours"
                                    value="{{ old('working_hours', $employee->working_hours) }}">
                            </div>
                            <div class="field-group">
                                <label class="field-label">Weekly Off</label>
                                <input class="field-input" name="weekly_off_day"
                                    value="{{ old('weekly_off_day', $employee->weekly_off_day) }}" placeholder="e.g. Sunday">
                            </div>
                        </div>
                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Radius (meters)</label>
                                <input class="field-input" type="number" name="attendance_radius_meter"
                                    value="{{ old('attendance_radius_meter', $employee->attendance_radius_meter) }}">
                            </div>
                            <div class="field-group">
                                <label class="field-label">Delay Time</label>
                                <input class="field-input" name="delay_time"
                                    value="{{ old('delay_time', $employee->delay_time) }}">
                            </div>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Branch / Attendance Location</label>
                            <select class="field-input" name="branch_id">
                                <option value="anywhere" {{ $employee->branch_id == 'anywhere' ? 'selected' : '' }}>Anywhere</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $employee->branch_id == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             TAB: SALARY
        ══════════════════════════════════════════ --}}
        <div id="tab-salary" class="etab-panel">
            <div class="eedit-grid-2">
                <div class="eedit-card">
                    <div class="eedit-card-head">💰 Salary Structure</div>
                    <div class="eedit-card-body">
                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Basic Salary (₹)</label>
                                <input class="field-input" type="number" id="basic_salary" name="basic_salary"
                                    value="{{ old('basic_salary', $employee->basic_salary) }}">
                            </div>
                            <div class="field-group">
                                <label class="field-label">HRA (₹)</label>
                                <input class="field-input" type="number" id="hra" name="hra"
                                    value="{{ old('hra', $employee->hra) }}">
                            </div>
                        </div>

                        <div class="eedit-section-sub">Additional Allowances</div>

                        <div class="allowance-grid">
                            @php
                                $allowanceLabels = [
                                    'travel_allowance'    => 'Travel',
                                    'meal_allowance'      => 'Meal',
                                    'uniform_allowance'   => 'Uniform',
                                    'medical_allowance'   => 'Medical',
                                    'housing_allowance'   => 'Housing',
                                    'transport_allowance' => 'Transport',
                                    'special_allowance'   => 'Special',
                                ];
                            @endphp
                            @foreach($allowanceLabels as $key => $label)
                            <div class="field-group">
                                <label class="field-label">{{ $label }} (₹)</label>
                                <input class="field-input allowance-field" type="number" name="{{ $key }}"
                                    value="{{ $allowances[$key] ?? '' }}" placeholder="0">
                            </div>
                            @endforeach
                        </div>

                        <input type="hidden" name="other_allowances_json" id="other_allowances_json"
                            value="{{ $employee->other_allowances_json }}">

                        <div class="salary-summary">
                            <div class="salary-row">
                                <span>Other Allowances</span>
                                <span id="other_allowances_display">₹{{ number_format($employee->other_allowances ?? 0, 2) }}</span>
                                <input type="hidden" id="other_allowances_main" name="other_allowances"
                                    value="{{ $employee->other_allowances ?? 0 }}">
                            </div>
                            <div class="salary-row">
                                <span>Deductions (₹)</span>
                                <input class="field-input" type="number" id="deductions" name="deductions"
                                    value="{{ old('deductions', $employee->deductions) }}" style="max-width:140px;text-align:right;">
                            </div>
                            <div class="salary-row salary-net">
                                <span>Net Salary</span>
                                <span id="net_salary_display">₹{{ number_format($employee->net_salary ?? 0, 2) }}</span>
                                <input type="hidden" id="net_salary" name="net_salary"
                                    value="{{ $employee->net_salary ?? 0 }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             TAB: EMPLOYMENT
        ══════════════════════════════════════════ --}}
        <div id="tab-employment" class="etab-panel">
            <div class="eedit-grid-2">
                <div class="eedit-card">
                    <div class="eedit-card-head">💼 Employment Details</div>
                    <div class="eedit-card-body">
                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Date of Joining</label>
                                <input class="field-input" type="date" name="date_of_joining"
                                    value="{{ old('date_of_joining', $employee->date_of_joining) }}">
                            </div>
                            <div class="field-group">
                                <label class="field-label">Position</label>
                                <input class="field-input" name="position"
                                    value="{{ old('position', $employee->position) }}">
                            </div>
                        </div>
                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Department</label>
                                <input class="field-input" name="department"
                                    value="{{ old('department', $employee->department) }}">
                            </div>
                            <div class="field-group">
                                <label class="field-label">Reporting Manager</label>
                                <select class="field-input" name="reporting_to">
                                    <option value="">Select Manager</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}" {{ $employee->reporting_to == $u->id ? 'selected' : '' }}>
                                            {{ $u->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">
                                    Employment Status
                                    @if($employee->status_change_pending)
                                        <span class="pending-label">⏳ Change Pending Approval</span>
                                    @endif
                                </label>
                                <select class="field-input" name="status">
                                    @foreach(['Active','Resigned','Terminated','Suspended'] as $s)
                                        <option value="{{ $s }}" {{ old('status', $employee->status) == $s ? 'selected' : '' }}>
                                            {{ $s }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field-group">
                                <label class="field-label">Document Status</label>
                                <select class="field-input" name="document_verified">
                                    @foreach(['pending','processing','verified','rejected'] as $ds)
                                        <option value="{{ $ds }}" {{ $employee->document_verified == $ds ? 'selected' : '' }}>
                                            {{ ucfirst($ds) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Status Change Remarks</label>
                            <textarea class="field-input" name="status_change_remarks" rows="2"
                                placeholder="Reason for status change (if applicable)"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ══════════════════════════════════════════
             TAB: DOCUMENTS
        ══════════════════════════════════════════ --}}
        <div id="tab-documents" class="etab-panel">
            <div class="eedit-card">
                <div class="eedit-card-head">📁 Upload Documents</div>
                <div class="eedit-card-body">
                    <div class="doc-grid">
                        @php
                            $docs = [
                                'cv'               => ['label'=>'CV / Resume',          'icon'=>'📄'],
                                'offer_letter'     => ['label'=>'Offer Letter',          'icon'=>'📬'],
                                'aadhaar_front'    => ['label'=>'Aadhaar Front',         'icon'=>'🪪'],
                                'aadhaar_back'     => ['label'=>'Aadhaar Back',          'icon'=>'🪪'],
                                'pan_card'         => ['label'=>'PAN Card',              'icon'=>'💳'],
                                'marksheet'        => ['label'=>'Marksheet',             'icon'=>'📋'],
                                'certificate'      => ['label'=>'Certificate',           'icon'=>'🏅'],
                                'passbook'         => ['label'=>'Bank Passbook',         'icon'=>'📒'],
                                'photo'            => ['label'=>'Passport Photo',        'icon'=>'🖼'],
                                'other_document'   => ['label'=>'Other Document',        'icon'=>'📎'],
                                'signature'        => ['label'=>'Signature',             'icon'=>'✍️'],
                                'experience_letter'=> ['label'=>'Experience Letter',     'icon'=>'📜'],
                            ];
                        @endphp
                        @foreach($docs as $name => $meta)
                        <div class="doc-item">
                            <label class="doc-label">
                                <div class="doc-icon">{{ $meta['icon'] }}</div>
                                <div class="doc-info">
                                    <span class="doc-name">{{ $meta['label'] }}</span>
                                    <span class="doc-status">
                                        @if($employee->$name)
                                            <span style="color:#10b981;">✓ Uploaded</span>
                                        @else
                                            <span style="color:#94a3b8;">Not uploaded</span>
                                        @endif
                                    </span>
                                </div>
                                <input type="file" name="{{ $name }}" class="doc-file-input"
                                    accept="image/*,.pdf,.doc,.docx"
                                    onchange="previewDoc(this, '{{ $name }}')">
                                <div class="doc-upload-btn">Choose File</div>
                            </label>
                            <div id="doc_preview_{{ $name }}" class="doc-preview">
                                @if($employee->$name)
                                    @php $url = asset('storage/'.$employee->$name); @endphp
                                    @if(Str::endsWith($employee->$name, ['.jpg','.jpeg','.png','.webp']))
                                        <img src="{{ $url }}" style="max-height:60px;border-radius:6px;">
                                    @elseif(Str::endsWith($employee->$name, '.pdf'))
                                        <a href="{{ $url }}" target="_blank" class="doc-link">📄 View PDF</a>
                                    @else
                                        <a href="{{ $url }}" target="_blank" class="doc-link">📎 View File</a>
                                    @endif
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             TAB: AUDIT TRAIL
        ══════════════════════════════════════════ --}}
        @if($isAdmin)
        <div id="tab-audit" class="etab-panel">
            <div class="eedit-card">
                <div class="eedit-card-head">
                    📋 Status Change Audit Trail
                    @if($employee->status_change_pending)
                        <span class="pending-label ml-2">⏳ Approval Pending</span>
                    @endif
                </div>
                <div class="eedit-card-body">
                    @if($statusLogs->count())
                    <div class="audit-timeline">
                        @foreach($statusLogs as $log)
                        <div class="audit-entry {{ is_null($log->approved_by) && !in_array($log->new_status, ['Active']) ? 'audit-pending' : 'audit-done' }}">
                            <div class="audit-line"></div>
                            <div class="audit-dot"></div>
                            <div class="audit-body">
                                <div class="audit-top">
                                    <span class="audit-change">
                                        <span class="audit-from">{{ $log->old_status ?? 'New' }}</span>
                                        <span class="audit-arrow">→</span>
                                        <span class="audit-to {{ strtolower($log->new_status) }}">{{ $log->new_status }}</span>
                                    </span>
                                    <span class="audit-date">{{ $log->changed_at?->format('d M Y, h:i A') }}</span>
                                </div>
                                <div class="audit-meta">
                                    <div class="audit-meta-row">
                                        <span class="meta-key">Changed by</span>
                                        <span class="meta-val">{{ $log->changedBy?->name ?? '—' }}</span>
                                    </div>
                                    <div class="audit-meta-row">
                                        <span class="meta-key">Approved by</span>
                                        <span class="meta-val {{ is_null($log->approved_by) ? 'meta-pending' : '' }}">
                                            {{ $log->approvedBy?->name ?? '⏳ Pending' }}
                                        </span>
                                    </div>
                                    @if($log->reactivated_by)
                                    <div class="audit-meta-row">
                                        <span class="meta-key">Reactivated by</span>
                                        <span class="meta-val">{{ $log->reactivatedBy?->name ?? '—' }}</span>
                                    </div>
                                    @endif
                                    @if($log->remarks)
                                    <div class="audit-meta-row">
                                        <span class="meta-key">Remarks</span>
                                        <span class="meta-val">{{ $log->remarks }}</span>
                                    </div>
                                    @endif
                                </div>
                                @if(is_null($log->approved_by) && $log->new_status !== 'Active')
                                <button type="button" class="btn-approve-status"
                                    onclick="approveLog({{ $log->id }}, {{ $employee->id }})">
                                    ✅ Approve This Change
                                </button>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div style="text-align:center;padding:40px;color:#94a3b8;">
                        <div style="font-size:2.5rem;margin-bottom:10px;">📋</div>
                        <p>No status changes recorded yet.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- ── Submit Bar ── --}}
        <div class="eedit-submit-bar">
            <a href="{{ route('admin.employees.index') }}" class="btn-cancel">Cancel</a>
            <button type="submit" class="btn-save">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Update Employee
            </button>
        </div>

    </form>
</div>{{-- end .eedit-page --}}

<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;600;700&display=swap');

*, *::before, *::after { box-sizing: border-box; }

.eedit-page {
    font-family: 'DM Sans', sans-serif;
    padding: 24px;
    background: #f0f4f9;
    min-height: 100vh;
}

/* ── Header ── */
.eedit-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 22px;
}
.eedit-header-left { display: flex; align-items: center; gap: 14px; }
.back-btn {
    width: 38px; height: 38px;
    border-radius: 10px;
    background: #fff;
    border: 1.5px solid #e2e8f0;
    display: flex; align-items: center; justify-content: center;
    color: #475569;
    text-decoration: none;
    transition: all .2s;
}
.back-btn:hover { background: #1e3a5f; color: #fff; border-color: #1e3a5f; }
.eedit-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.3rem;
    font-weight: 700;
    color: #1e3a5f;
    margin: 0;
}
.eedit-sub { font-size: .82rem; color: #64748b; margin: 3px 0 0; }
.badge-inactive {
    background: #fee2e2; color: #991b1b;
    font-size: .68rem; font-weight: 700;
    padding: 2px 8px; border-radius: 50px;
    margin-left: 8px;
}
.eedit-avatar {
    width: 48px; height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, #1e3a5f, #2563eb);
    color: #fff;
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1rem;
    font-weight: 700;
    display: flex; align-items: center; justify-content: center;
}

/* ── Alerts ── */
.eedit-alert {
    border-radius: 10px;
    padding: 12px 18px;
    font-size: .87rem;
    margin-bottom: 18px;
}
.eedit-alert-success { background: #d1fae5; color: #065f46; border-left: 3px solid #10b981; }
.eedit-alert-danger  { background: #fee2e2; color: #991b1b; border-left: 3px solid #ef4444; }

/* ── Tabs ── */
.eedit-tabs {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-bottom: 18px;
    background: #fff;
    border-radius: 14px;
    padding: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,.05);
}
.etab {
    border: none;
    background: transparent;
    border-radius: 9px;
    padding: 8px 18px;
    font-size: .83rem;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
    transition: all .2s;
    position: relative;
}
.etab:hover { background: #f1f5f9; }
.etab.active { background: #1e3a5f; color: #fff; }
.etab-badge {
    background: #ef4444;
    color: #fff;
    border-radius: 50%;
    width: 16px; height: 16px;
    font-size: .6rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-left: 4px;
    vertical-align: middle;
}

/* ── Tab Panels ── */
.etab-panel { display: none; }
.etab-panel.active { display: block; }

/* ── Card ── */
.eedit-grid-2 { display: grid; grid-template-columns: 1fr; gap: 18px; }
.eedit-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    overflow: hidden;
}
.eedit-card-head {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
    color: #fff;
    padding: 14px 20px;
    font-weight: 700;
    font-size: .92rem;
    display: flex;
    align-items: center;
    gap: 8px;
}
.eedit-card-body { padding: 20px; }

/* ── Fields ── */
.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.field-group { display: flex; flex-direction: column; gap: 5px; margin-bottom: 14px; }
.field-label {
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #475569;
    display: flex;
    align-items: center;
    gap: 6px;
}
.field-input {
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: .88rem;
    color: #1e293b;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
    font-family: 'DM Sans', sans-serif;
    background: #f8fafc;
    width: 100%;
}
.field-input:focus { border-color: #3b82f6; background: #fff; box-shadow: 0 0 0 3px rgba(59,130,246,.08); }
textarea.field-input { resize: vertical; }

.badge-admin {
    background: #dbeafe;
    color: #1d4ed8;
    font-size: .6rem;
    padding: 1px 7px;
    border-radius: 50px;
    font-weight: 700;
}
.pending-label {
    font-size: .72rem;
    color: #92400e;
    background: #fef3c7;
    padding: 2px 8px;
    border-radius: 50px;
    font-weight: 600;
    margin-left: 6px;
}
.eedit-section-sub {
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: #94a3b8;
    margin: 6px 0 14px;
    padding-bottom: 8px;
    border-bottom: 1px dashed #e2e8f0;
}

.btn-gen {
    background: #f1f5f9;
    border: 1.5px solid #e2e8f0;
    border-radius: 9px;
    padding: 10px 14px;
    font-size: .78rem;
    font-weight: 700;
    color: #475569;
    cursor: pointer;
    white-space: nowrap;
    transition: all .2s;
}
.btn-gen:hover { background: #1e3a5f; color: #fff; border-color: #1e3a5f; }

/* ── Allowances ── */
.allowance-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

/* ── Salary Summary ── */
.salary-summary {
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    padding: 14px 16px;
    margin-top: 10px;
}
.salary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 7px 0;
    font-size: .87rem;
    color: #475569;
    border-bottom: 1px solid #f1f5f9;
}
.salary-row:last-child { border-bottom: none; }
.salary-net {
    font-weight: 700;
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1rem;
    color: #1e293b;
    padding-top: 10px;
    margin-top: 4px;
    border-top: 2px solid #e2e8f0 !important;
    border-bottom: none !important;
}

/* ── Documents ── */
.doc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 14px; }
.doc-item {
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    transition: border-color .2s;
}
.doc-item:hover { border-color: #3b82f6; }
.doc-label {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px;
    cursor: pointer;
    background: #f8fafc;
}
.doc-icon { font-size: 1.5rem; flex-shrink: 0; }
.doc-info { flex: 1; min-width: 0; }
.doc-name { display: block; font-size: .82rem; font-weight: 600; color: #1e293b; }
.doc-status { font-size: .7rem; color: #94a3b8; }
.doc-file-input { display: none; }
.doc-upload-btn {
    background: #e2e8f0;
    color: #475569;
    font-size: .7rem;
    font-weight: 600;
    padding: 4px 9px;
    border-radius: 7px;
    white-space: nowrap;
    flex-shrink: 0;
}
.doc-item:hover .doc-upload-btn { background: #3b82f6; color: #fff; }
.doc-preview { padding: 8px 12px; min-height: 20px; background: #fff; }
.doc-link { font-size: .78rem; color: #3b82f6; text-decoration: none; font-weight: 500; }

/* ── Audit Timeline ── */
.audit-timeline { position: relative; padding-left: 30px; }
.audit-entry { position: relative; margin-bottom: 20px; }
.audit-line {
    position: absolute;
    left: -21px; top: 22px; bottom: -20px;
    width: 2px;
    background: #e2e8f0;
}
.audit-entry:last-child .audit-line { display: none; }
.audit-dot {
    position: absolute;
    left: -26px; top: 6px;
    width: 12px; height: 12px;
    border-radius: 50%;
    border: 2px solid;
}
.audit-done .audit-dot    { background: #10b981; border-color: #10b981; }
.audit-pending .audit-dot { background: #f59e0b; border-color: #f59e0b; }

.audit-body {
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    padding: 14px;
}
.audit-pending .audit-body { border-color: #fde68a; background: #fffbeb; }

.audit-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.audit-change { display: flex; align-items: center; gap: 8px; }
.audit-from { font-size: .82rem; color: #64748b; font-weight: 500; }
.audit-arrow { color: #94a3b8; }
.audit-to {
    font-size: .78rem; font-weight: 700;
    padding: 2px 9px; border-radius: 50px;
}
.audit-to.active      { background: #d1fae5; color: #065f46; }
.audit-to.resigned    { background: #fef3c7; color: #92400e; }
.audit-to.terminated  { background: #fee2e2; color: #991b1b; }
.audit-to.suspended   { background: #f3f4f6; color: #374151; }

.audit-date { font-size: .73rem; color: #94a3b8; }

.audit-meta { font-size: .78rem; }
.audit-meta-row { display: flex; gap: 8px; padding: 4px 0; color: #475569; }
.meta-key { font-weight: 600; color: #94a3b8; min-width: 90px; }
.meta-val { color: #1e293b; }
.meta-pending { color: #f59e0b; font-style: italic; }

.btn-approve-status {
    margin-top: 10px;
    background: linear-gradient(135deg, #059669, #10b981);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 7px 16px;
    font-size: .8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
}
.btn-approve-status:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16,185,129,.3); }

/* ── Submit bar ── */
.eedit-submit-bar {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 22px;
    background: #fff;
    border-radius: 14px;
    padding: 16px 20px;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
}
.btn-save {
    background: linear-gradient(135deg, #1e3a5f, #2563eb);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 11px 28px;
    font-size: .88rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 7px;
    transition: all .2s;
}
.btn-save:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(37,99,235,.3); }
.btn-cancel {
    background: #f8fafc;
    color: #475569;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 11px 22px;
    font-size: .88rem;
    font-weight: 600;
    text-decoration: none;
    transition: all .2s;
}
.btn-cancel:hover { background: #e2e8f0; }

@media(max-width:640px) {
    .eedit-page { padding: 14px; }
    .field-row { grid-template-columns: 1fr; }
    .allowance-grid { grid-template-columns: 1fr; }
    .doc-grid { grid-template-columns: 1fr 1fr; }
    .eedit-tabs { gap: 4px; }
    .etab { padding: 7px 12px; font-size: .75rem; }
}
</style>

@section('scripts')
@parent
<script>
/* ── Tab Switching ── */
function showTab(tabId, btn) {
    document.querySelectorAll('.etab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.etab').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tabId).classList.add('active');
    btn.classList.add('active');
}

/* ── Employee Type Toggle ── */
const empType    = document.getElementById('employee_type');
const durBox     = document.getElementById('emp_duration_box');
function toggleDuration() {
    if (empType && durBox) {
        durBox.style.display = (empType.value && empType.value !== 'Permanent') ? 'flex' : 'none';
    }
}
if (empType) { empType.addEventListener('change', toggleDuration); toggleDuration(); }

/* ── IFSC Fetch ── */
const ifscInput = document.getElementById('ifsc_code');
if (ifscInput) {
    ifscInput.addEventListener('blur', function () {
        if (this.value.trim().length === 11) {
            fetch(`https://ifsc.razorpay.com/${this.value.trim()}`)
                .then(r => r.json())
                .then(d => {
                    if (d && d.BANK) {
                        document.getElementById('bank_name').value    = d.BANK    || '';
                        document.getElementById('bank_address').value = d.ADDRESS || '';
                    }
                });
        }
    });
}

/* ── Salary Calculation ── */
function calcSalary() {
    const basic  = parseFloat(document.getElementById('basic_salary')?.value)  || 0;
    const hra    = parseFloat(document.getElementById('hra')?.value)            || 0;
    const other  = parseFloat(document.getElementById('other_allowances_main')?.value) || 0;
    const deduc  = parseFloat(document.getElementById('deductions')?.value)     || 0;
    const net    = basic + hra + other - deduc;

    const netEl  = document.getElementById('net_salary');
    const netDis = document.getElementById('net_salary_display');
    if (netEl)  netEl.value       = net.toFixed(2);
    if (netDis) netDis.textContent = '₹' + net.toLocaleString('en-IN', {minimumFractionDigits:2});
}

/* ── Allowances Sum ── */
function sumAllowances() {
    let total = 0;
    const data  = {};
    document.querySelectorAll('.allowance-field').forEach(input => {
        const val = parseFloat(input.value) || 0;
        total += val;
        data[input.name] = val;
    });
    const mainEl = document.getElementById('other_allowances_main');
    const disEl  = document.getElementById('other_allowances_display');
    const jsonEl = document.getElementById('other_allowances_json');
    if (mainEl) mainEl.value       = total.toFixed(2);
    if (disEl)  disEl.textContent  = '₹' + total.toLocaleString('en-IN', {minimumFractionDigits:2});
    if (jsonEl) jsonEl.value       = JSON.stringify(data);
    calcSalary();
}

document.querySelectorAll('.allowance-field').forEach(f => f.addEventListener('input', sumAllowances));
['basic_salary','hra','deductions'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', calcSalary);
});

/* ── Load saved allowances on edit ── */
try {
    const jsonEl = document.getElementById('other_allowances_json');
    if (jsonEl && jsonEl.value) {
        const saved = JSON.parse(jsonEl.value);
        Object.entries(saved).forEach(([k, v]) => {
            const el = document.querySelector(`input[name='${k}']`);
            if (el) el.value = v;
        });
        sumAllowances();
    }
} catch {}

/* ── Generate Employee Code ── */
function generateEmployeeCode() {
    const y = new Date().getFullYear().toString().slice(-2);
    document.getElementById('employee_code').value = 'EMP' + y + Math.floor(1000 + Math.random() * 9000);
}

/* ── Document Preview ── */
function previewDoc(input, id) {
    const preview = document.getElementById('doc_preview_' + id);
    if (!preview || !input.files.length) return;
    const file = input.files[0];
    const reader = new FileReader();
    reader.onload = function (e) {
        const ext = file.name.split('.').pop().toLowerCase();
        if (['jpg','jpeg','png','webp'].includes(ext)) {
            preview.innerHTML = `<img src="${e.target.result}" style="max-height:60px;border-radius:6px;">`;
        } else if (ext === 'pdf') {
            preview.innerHTML = `<span class="doc-link">📄 ${file.name}</span>`;
        } else {
            preview.innerHTML = `<span class="doc-link">📎 ${file.name}</span>`;
        }
    };
    reader.readAsDataURL(file);
}

/* ── Approve Status Log ── */
function approveLog(logId, empId) {
    if (!confirm('Approve this status change?')) return;
    $.ajax({
        url: `/admin/employees/${empId}/approve-status`,
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        success: () => location.reload(),
        error: () => alert('Error approving status change.')
    });
}

/* ── Non-admin protection ── */
@if(!$isAdmin)
document.addEventListener('DOMContentLoaded', function () {
    const sel = document.getElementById('user_id');
    if (!sel) return;
    const original = sel.value;
    sel.addEventListener('change', function () {
        alert('You do not have permission to change the linked user.');
        sel.value = original;
    });
});
@endif
</script>
@endsection
@endsection
