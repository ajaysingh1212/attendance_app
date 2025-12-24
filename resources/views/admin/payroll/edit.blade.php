@extends('layouts.admin')

@section('content')
<div class="container">
    <h2 class="mb-4">Edit Employee</h2>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.employees.update', $employee->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">

            <!-- Personal & Contact -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">Personal & Contact</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Linked User (Optional)</label>
                            <select class="form-control" name="user_id" id="user_id" @readonly(false && !Auth::user()->isAdmin()) >
                                <option value="">Select User</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}"
                                        data-name="{{ $user->name }}"
                                        data-email="{{ $user->email }}"
                                        data-phone="{{ $user->number ?? $user->phone ?? '' }}"
                                        {{ $employee->user_id == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <label>Employee Code</label>
                        <div class="input-group mb-2">
                            <input class="form-control" id="employee_code" name="employee_code"
                                value="{{ old('employee_code', $employee->employee_code) }}" required @readonly(false && !Auth::user()->isAdmin()) >
                            <div class="input-group-append">
                                <button type="button" class="btn btn-secondary" onclick="generateEmployeeCode()">Generate</button>
                            </div>
                        </div>

                        <input class="form-control mb-2" id="full_name" name="full_name"
                            value="{{ old('full_name', $employee->full_name) }}" placeholder="Full Name" required  @readonly(false && !Auth::user()->isAdmin()) >
                        <input class="form-control mb-2" id="email" name="email"
                            value="{{ old('email', $employee->email) }}" placeholder="Email"  @readonly(false && !Auth::user()->isAdmin()) >
                        <input class="form-control mb-2" id="phone" name="phone"
                            value="{{ old('phone', $employee->phone) }}" placeholder="Phone"  @readonly(false && !Auth::user()->isAdmin()) >
                    </div>
                </div>
            </div>
    @php
        use Illuminate\Support\Facades\Auth;

        $user = Auth::user();
        $isAdmin = false;

        if ($user && method_exists($user, 'roles')) {
            $isAdmin = $user->roles()->where('title', 'Admin')->exists();
        }
    @endphp
    @if($isAdmin)
        
    
            <!-- Bank Info -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">Bank Details</div>
                    <div class="card-body">
                        <input class="form-control mb-2" name="ifsc_code" id="ifsc_code"
                            value="{{ old('ifsc_code', $employee->ifsc_code) }}" placeholder="IFSC Code"  @readonly(false && !Auth::user()->isAdmin()) >
                        <input class="form-control mb-2" name="bank_name" id="bank_name"
                            value="{{ old('bank_name', $employee->bank_name) }}" placeholder="Bank Name" readonly  @readonly(false && !Auth::user()->isAdmin()) >
                        <input class="form-control mb-2" name="bank_address" id="bank_address"
                            value="{{ old('bank_address', $employee->bank_address) }}" placeholder="Bank Address" readonly  @readonly(false && !Auth::user()->isAdmin()) >
                        <input class="form-control mb-2" name="account_number"
                            value="{{ old('account_number', $employee->account_number) }}" placeholder="Account Number"  @readonly(false && !Auth::user()->isAdmin()) >
                        <input class="form-control mb-2" name="pan_number"
                            value="{{ old('pan_number', $employee->pan_number) }}" placeholder="PAN Number"  @readonly(false && !Auth::user()->isAdmin()) >
                        <input class="form-control mb-2" name="aadhaar_number"
                            value="{{ old('aadhaar_number', $employee->aadhaar_number) }}" placeholder="Aadhaar Number"  @readonly(false && !Auth::user()->isAdmin()) >

                        <select class="form-control mb-2" name="payment_mode"  @readonly(false && !Auth::user()->isAdmin()) >
                            <option value="Bank" {{ $employee->payment_mode == 'Bank' ? 'selected' : '' }}>Bank</option>
                            <option value="Cash" {{ $employee->payment_mode == 'Cash' ? 'selected' : '' }}>Cash</option>
                            <option value="UPI" {{ $employee->payment_mode == 'UPI' ? 'selected' : '' }}>UPI</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Work Timing -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">Work Timing</div>
                    <div class="card-body">
                        <label>Start Time</label>
                        <input class="form-control mb-2" type="time" name="work_start_time"
                            value="{{ old('work_start_time', $employee->work_start_time) }}"  @readonly(false && !Auth::user()->isAdmin()) >
                        <label>End Time</label>
                        <input class="form-control mb-2" type="time" name="work_end_time"
                            value="{{ old('work_end_time', $employee->work_end_time) }}"  @readonly(false && !Auth::user()->isAdmin()) >
                        <input class="form-control mb-2" name="working_hours"
                            value="{{ old('working_hours', $employee->working_hours) }}" placeholder="Total Working Hours"  @readonly(false && !Auth::user()->isAdmin()) >
                        <input class="form-control mb-2" name="weekly_off_day"
                            value="{{ old('weekly_off_day', $employee->weekly_off_day) }}" placeholder="Weekly Off (e.g., Sunday)"  @readonly(false && !Auth::user()->isAdmin()) >
                        <label>Attendance Source</label>

                        <div class="row">
                            <div class="col-lg-6">
                                <input class="form-control mb-2" name="attendance_radius_meter"
                                    value="{{ old('attendance_radius_meter', $employee->attendance_radius_meter) }}"
                                    placeholder="Radius in meters"  @readonly(false && !Auth::user()->isAdmin()) >
                            </div>
                            <div class="col-lg-6">
                                <input class="form-control mb-2" name="delay_time"
                                    value="{{ old('delay_time', $employee->delay_time) }}" placeholder="Delay Time"  @readonly(false && !Auth::user()->isAdmin()) >
                            </div>
                        </div>

                        <select class="form-control mb-2" name="branch_id" id="attendance_source"  @readonly(false && !Auth::user()->isAdmin()) >
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

            <!-- Salary Info -->
            <div class="col-lg-4 mb-4">
    <div class="card shadow-sm">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <span>Salary Info</span>
            <button type="button" class="btn btn-sm btn-light" id="toggle_allowances">
                <i class="fas fa-plus"></i> Extra
            </button>
        </div>

        <div class="card-body">
            {{-- Basic + HRA --}}
            <input type="number" class="form-control mb-2" id="basic_salary"
                name="basic_salary" value="{{ old('basic_salary', $employee->basic_salary) }}"
                placeholder="Basic Salary" @readonly(false && !Auth::user()->isAdmin()) >

            <input type="number" class="form-control mb-2" id="hra"
                name="hra" value="{{ old('hra', $employee->hra) }}"
                placeholder="HRA" @readonly(false && !Auth::user()->isAdmin()) >

            {{-- Hidden extra allowance fields --}}
            @php
                $allowances = json_decode($employee->other_allowances_json, true) ?? [];
            @endphp
            <div id="other_allowances_extra" style=" margin-top:10px;">
                <input type="number" class="form-control mb-2 allowance-field" name="travel_allowance"
                    value="{{ $allowances['travel_allowance'] ?? '' }}" placeholder="Travel Allowance" @readonly(false && !Auth::user()->isAdmin()) >
                <input type="number" class="form-control mb-2 allowance-field" name="meal_allowance"
                    value="{{ $allowances['meal_allowance'] ?? '' }}" placeholder="Meal Allowance" @readonly(false && !Auth::user()->isAdmin()) >
                <input type="number" class="form-control mb-2 allowance-field" name="uniform_allowance"
                    value="{{ $allowances['uniform_allowance'] ?? '' }}" placeholder="Uniform Allowance" @readonly(false && !Auth::user()->isAdmin()) >
                <input type="number" class="form-control mb-2 allowance-field" name="medical_allowance"
                    value="{{ $allowances['medical_allowance'] ?? '' }}" placeholder="Medical Allowance" @readonly(false && !Auth::user()->isAdmin()) >
                <input type="number" class="form-control mb-2 allowance-field" name="housing_allowance"
                    value="{{ $allowances['housing_allowance'] ?? '' }}" placeholder="Housing Allowance" @readonly(false && !Auth::user()->isAdmin()) >
                <input type="number" class="form-control mb-2 allowance-field" name="transport_allowance"
                    value="{{ $allowances['transport_allowance'] ?? '' }}" placeholder="Transport Allowance" @readonly(false && !Auth::user()->isAdmin()) >
                <input type="number" class="form-control mb-2 allowance-field" name="special_allowance"
                    value="{{ $allowances['special_allowance'] ?? '' }}" placeholder="Special Allowance" @readonly(false && !Auth::user()->isAdmin()) >
            </div>

            {{-- Main Allowances --}}
            <input type="number" class="form-control mb-2" name="other_allowances"
                placeholder="Other Allowances" id="other_allowances_main"
                value="{{ old('other_allowances', $employee->other_allowances) }}" readonly @readonly(false && !Auth::user()->isAdmin()) >

            {{-- Hidden JSON field --}}
            <input type="hidden" name="other_allowances_json" id="other_allowances_json"
                value="{{ $employee->other_allowances_json }}" @readonly(false && !Auth::user()->isAdmin()) >

            {{-- Deductions + Net --}}
            <input type="number" class="form-control mb-2" id="deductions"
                name="deductions" value="{{ old('deductions', $employee->deductions) }}" placeholder="Deductions" @readonly(false && !Auth::user()->isAdmin()) >

            <input type="number" class="form-control mb-2" id="net_salary"
                name="net_salary" value="{{ old('net_salary', $employee->net_salary) }}"
                placeholder="Net Salary" readonly @readonly(false && !Auth::user()->isAdmin()) >
        </div>
    </div>
</div>


            <!-- Employment Info -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">Employment Info</div>
                    <div class="card-body">
                        <label>Date of Joining</label>
                        <input class="form-control mb-2" type="date"
                            name="date_of_joining" value="{{ old('date_of_joining', $employee->date_of_joining) }}" @readonly(false && !Auth::user()->isAdmin()) >
                        <input class="form-control mb-2" name="position"
                            value="{{ old('position', $employee->position) }}" placeholder="Position" @readonly(false && !Auth::user()->isAdmin()) >
                        <input class="form-control mb-2" name="department"
                            value="{{ old('department', $employee->department) }}" placeholder="Department" @readonly(false && !Auth::user()->isAdmin()) >
                        <label>Reporting Manager</label>
                        <select class="form-control mb-2" name="reporting_to" @readonly(false && !Auth::user()->isAdmin()) >
                            <option value="">Select</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $employee->reporting_to == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        <select class="form-control mb-2" name="status" @readonly(false && !Auth::user()->isAdmin()) >
                            <option value="Active" {{ $employee->status == 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Resigned" {{ $employee->status == 'Resigned' ? 'selected' : '' }}>Resigned</option>
                            <option value="Terminated" {{ $employee->status == 'Terminated' ? 'selected' : '' }}>Terminated</option>
                        </select>
                        <label for="document_verified">Document Status</label>
                        <select name="document_verified" id="document_verified" class="form-control">
                            <option value="pending" {{ $employee->document_verified == 'pending' ? 'selected' : '' }}>
                                Pending
                            </option>
                            <option value="processing" {{ $employee->document_verified == 'processing' ? 'selected' : '' }}>
                                Processing
                            </option>
                            <option value="verified" {{ $employee->document_verified == 'verified' ? 'selected' : '' }}>
                                Verified
                            </option>
                            <option value="rejected" {{ $employee->document_verified == 'rejected' ? 'selected' : '' }}>
                                Rejected
                            </option>
                        </select>

                    </div>
                </div>
            </div>
            @endif
            <!-- Documents -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">📁 Upload Documents</div>
                    <div class="card-body">
                        @php
                            $docs = [
                                'cv' => 'CV / Resume',
                                'offer_letter' => 'Offer Letter',
                                'aadhaar_front' => 'Aadhaar Front',
                                'aadhaar_back' => 'Aadhaar Back',
                                'pan_card' => 'PAN Card',
                                'marksheet' => 'Qualification Marksheet',
                                'certificate' => 'Qualification Certificate',
                                'passbook' => 'Bank Passbook',
                                'photo' => 'Passport Size Photo',
                                'other_document' => 'Other Document',
                                'signature' => 'Signature',
                                'experience_letter' => 'Experience Letter',
                            ];
                        @endphp

                        @foreach($docs as $name => $label)
                            <div class="file-upload">
                                <label class="file-label">
                                    <i class="fas fa-upload"></i> <span>{{ $label }}</span>
                                    <input type="file" name="{{ $name }}" accept="image/*,.pdf,.doc,.docx"
                                        onchange="previewFile(this, '{{ $name }}')">
                                </label>
                                <div class="preview" id="preview_{{ $name }}">
                                    @if($employee->$name)
                                        @php $url = asset('storage/'.$employee->$name); @endphp
                                        @if(Str::endsWith($employee->$name, ['.jpg','.jpeg','.png','.webp']))
                                            <img src="{{ $url }}" class="img-fluid rounded" width="100px" height="100px">
                                        @elseif(Str::endsWith($employee->$name, '.pdf'))
                                            <iframe src="{{ $url }}" width="100px" height="100px"></iframe>
                                        @else
                                            <a href="{{ $url }}" target="_blank">{{ basename($employee->$name) }}</a>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="text-right">
            <button class="btn btn-success">Update</button>
            <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

{{-- Scripts copied from create page --}}
@if(!$isAdmin)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const userSelect = document.getElementById('user_id');

        const originalValue = userSelect.value;

        userSelect.addEventListener('change', function () {
            alert(
  "You do not have permission to change the assigned user.\n" +
  "This action is restricted to administrators only.\n" +
  "You may update your documents until they are verified."
);

            userSelect.value = originalValue;
        });
    });
</script>
@endif

<script>
document.addEventListener("DOMContentLoaded", function() {

    /* ------------------ 🧮 Salary Calculation ------------------ */
    function calculateNetSalary() {
        const basic = parseFloat($("#basic_salary").val()) || 0;
        const hra = parseFloat($("#hra").val()) || 0;
        const other = parseFloat($("#other_allowances_main").val()) || 0;
        const deductions = parseFloat($("#deductions").val()) || 0;
        const net = basic + hra + other - deductions;
        $("#net_salary").val(net.toFixed(2));
    }

    ["#basic_salary", "#hra", "#deductions", "#other_allowances_main"].forEach(id => {
        $(id).on("input", calculateNetSalary);
    });

    /* ------------------ 💸 Allowances Auto-Sum + JSON ------------------ */
    const allowanceFields = $(".allowance-field");

    allowanceFields.on("input", function() {
        let total = 0;
        let allowanceData = {};

        allowanceFields.each(function() {
            const val = parseFloat($(this).val()) || 0;
            total += val;
            allowanceData[$(this).attr("name")] = val;
        });

        $("#other_allowances_main").val(total.toFixed(2));
        $("#other_allowances_json").val(JSON.stringify(allowanceData));

        calculateNetSalary();
    });

    /* ------------------ 🔄 Load Allowance JSON (on Edit) ------------------ */
    try {
        const jsonData = $("#other_allowances_json").val();
        if (jsonData) {
            const allowances = JSON.parse(jsonData);
            for (const key in allowances) {
                $(`input[name='${key}']`).val(allowances[key]);
            }
            let total = Object.values(allowances).reduce((a, b) => a + parseFloat(b || 0), 0);
            $("#other_allowances_main").val(total.toFixed(2));
        }
    } catch (err) {
        console.warn("Invalid allowance JSON");
    }

    /* ------------------ 🏦 IFSC Code Auto Fetch ------------------ */
    $("#ifsc_code").on("blur", function() {
        const ifsc = $(this).val().trim();
        if (ifsc.length === 11) {
            fetch(`https://ifsc.razorpay.com/${ifsc}`)
                .then(res => res.json())
                .then(data => {
                    if (data && data.BANK) {
                        $("#bank_name").val(data.BANK || "");
                        $("#bank_address").val(data.ADDRESS || "");
                    } else {
                        alert("Invalid IFSC Code");
                    }
                })
                .catch(() => alert("Unable to fetch IFSC details"));
        }
    });

    /* ------------------ 👤 Linked User Autofill ------------------ */
    $("#linked_user").on("change", function() {
        const selected = $(this).find("option:selected");
        $("#full_name").val(selected.data("name") || "");
        $("#email").val(selected.data("email") || "");
        $("#phone").val(selected.data("phone") || "");
    });

    /* ------------------ 🔢 Generate Employee Code ------------------ */
    window.generateEmployeeCode = function() {
        const randomNum = Math.floor(1000 + Math.random() * 9000);
        const year = new Date().getFullYear().toString().slice(-2);
        $("#employee_code").val(`EMP${year}${randomNum}`);
    };

    /* ------------------ 🖼️ File Preview ------------------ */
    window.previewFile = function(input, id) {
        const preview = document.getElementById("preview_" + id);
        if (!preview || !input.files.length) return;

        const file = input.files[0];
        const reader = new FileReader();

        reader.onload = function(e) {
            const ext = file.name.split(".").pop().toLowerCase();
            if (["jpg", "jpeg", "png", "webp"].includes(ext)) {
                preview.innerHTML = `<img src="${e.target.result}" class="img-fluid rounded">`;
            } else if (ext === "pdf") {
                preview.innerHTML = `<iframe src="${e.target.result}" width="100%" height="80"></iframe>`;
            } else {
                preview.innerHTML = `<p>${file.name}</p>`;
            }
        };

        reader.readAsDataURL(file);
    };

});
</script>


@endsection
