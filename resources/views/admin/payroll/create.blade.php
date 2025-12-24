@extends('layouts.admin')

@section('content')
<div class="container">
    @php
        use Illuminate\Support\Facades\Auth;

        $user = Auth::user();
        $isAdmin = false;

        if ($user && method_exists($user, 'roles')) {
            $isAdmin = $user->roles()->where('title', 'Admin')->exists();
        }
    @endphp
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.employees.store') }}" enctype="multipart/form-data">
        @csrf
        @if($isAdmin)
        <div class="row">
        <!-- Personal & Contact -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">Personal & Contact</div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Linked User (Optional)</label>
                        <select class="form-control" name="user_id" id="linked_user">
                            <option value="">Select User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}"
                                    data-name="{{ $user->name }}"
                                    data-email="{{ $user->email }}"
                                    data-phone="{{ $user->number ?? $user->phone ?? '' }}"
                                >
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <label>Employee Code</label>
                    <div class="input-group mb-2">
                        <input class="form-control" id="employee_code" name="employee_code" placeholder="Employee Code" required>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-secondary" onclick="generateEmployeeCode()">Generate</button>
                        </div>
                    </div>

                    <input class="form-control mb-2" id="full_name" name="full_name" placeholder="Full Name" required readonly>
                    <input class="form-control mb-2" id="email" name="email" placeholder="Email" readonly>
                    <input class="form-control mb-2" id="phone" name="phone" placeholder="Phone" readonly>
                        <select class="form-control mb-2" name="company_id" id="attendance_source">
                            <option value="anywhere">Anywhere</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->title }}</option>
                            @endforeach
                        </select>
                </div>
            </div>
        </div>


            <!-- Bank Info -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">Bank Details</div>
                    <div class="card-body">
                        <input class="form-control mb-2" name="ifsc_code" id="ifsc_code" placeholder="IFSC Code">
                        
                        <input class="form-control mb-2" name="bank_name" id="bank_name" placeholder="Bank Name" readonly>
                        <input class="form-control mb-2" name="bank_address" id="bank_address" placeholder="Bank Address" readonly>

                        <input class="form-control mb-2" name="account_number" placeholder="Account Number">
                        <input class="form-control mb-2" name="pan_number" placeholder="PAN Number">
                        <input class="form-control mb-2" name="aadhaar_number" placeholder="Aadhaar Number">
                        
                        <select class="form-control mb-2" name="payment_mode">
                            <option value="Bank">Bank</option>
                            <option value="Cash">Cash</option>
                            <option value="UPI">UPI</option>
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
                        <input class="form-control mb-2" type="time" name="work_start_time">
                        <label>End Time</label>
                        <input class="form-control mb-2" type="time" name="work_end_time">
                        <input class="form-control mb-2" name="working_hours" placeholder="Total Working Hours">
                        <input class="form-control mb-2" name="weekly_off_day" placeholder="Weekly Off (e.g., Sunday)">
                        <label>Attendance Source</label>
                       <div class="row">
                        <div class="col-lg-6">
                            <input class="form-control mb-2" name="attendance_radius_meter" placeholder="Radius in meters">
                        </div>
                        <div class="col-lg-6">
                            <input class="form-control mb-2" name="delay_time" placeholder="Delay Time">
                            
                        </div>
                        </div>
                        <select class="form-control mb-2" name="branch_id" id="attendance_source">
                            <option value="anywhere">Anywhere</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->title }}</option>
                            @endforeach
                        </select>


                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">Salary Info</div>
                    <div class="card-body">
                        <input type="number" class="form-control mb-2" id="basic_salary" name="basic_salary" placeholder="Basic Salary">
                        <input type="number" class="form-control mb-2" id="hra" name="hra" placeholder="HRA">

                        <!-- Hidden extra allowance fields -->
                        <div id="other_allowances_extra" style="display: none; margin-top: 10px;">
                            <input type="number" class="form-control mb-2 allowance-field" name="travel_allowance" placeholder="Travel Allowance">
                            <input type="number" class="form-control mb-2 allowance-field" name="meal_allowance" placeholder="Meal Allowance">
                            <input type="number" class="form-control mb-2 allowance-field" name="uniform_allowance" placeholder="Uniform Allowance">
                            <input type="number" class="form-control mb-2 allowance-field" name="medical_allowance" placeholder="Medical Allowance">
                            <input type="number" class="form-control mb-2 allowance-field" name="housing_allowance" placeholder="Housing Allowance">
                            <input type="number" class="form-control mb-2 allowance-field" name="transport_allowance" placeholder="Transport Allowance">
                            <input type="number" class="form-control mb-2 allowance-field" name="special_allowance" placeholder="Special Allowance">
                        </div>

                        <!-- Main Other Allowances (Read-only) -->
                        <input 
                            type="number"
                            class="form-control mb-2" 
                            name="other_allowances" 
                            placeholder="Other Allowances" 
                            id="other_allowances_main"
                            readonly
                        >

                        <!-- Hidden field for JSON data -->
                        <input type="hidden" name="other_allowances_json" id="other_allowances_json">

                        <input type="number" class="form-control mb-2" id="deductions" name="deductions" placeholder="Deductions">
                        <input type="number" class="form-control mb-2" id="net_salary" name="net_salary" placeholder="Net Salary" readonly>
                    </div>
                </div>
            </div>

            <script>
            // Show/hide allowance breakdown on click
            document.getElementById("other_allowances_main").addEventListener("click", function() {
                const extraFields = document.getElementById("other_allowances_extra");
                extraFields.style.display = (extraFields.style.display === "none" || extraFields.style.display === "") 
                    ? "block" 
                    : "none";
            });

            // Elements
            const basicInput = document.getElementById("basic_salary");
            const hraInput = document.getElementById("hra");
            const deductionInput = document.getElementById("deductions");
            const netSalaryInput = document.getElementById("net_salary");
            const allowanceInputs = document.querySelectorAll(".allowance-field");
            const otherAllowancesMain = document.getElementById("other_allowances_main");
            const otherAllowancesJson = document.getElementById("other_allowances_json");

            // Function to calculate total salary
            function calculateSalary() {
                let basic = parseFloat(basicInput.value) || 0;
                let hra = parseFloat(hraInput.value) || 0;
                let deductions = parseFloat(deductionInput.value) || 0;

                let totalAllowances = 0;
                let allowanceData = {};

                allowanceInputs.forEach(field => {
                    let value = parseFloat(field.value) || 0;
                    totalAllowances += value;
                    allowanceData[field.name] = value;
                });

                // Set main allowance total and hidden JSON
                otherAllowancesMain.value = totalAllowances;
                otherAllowancesJson.value = JSON.stringify(allowanceData);

                // Calculate net salary
                let netSalary = basic + hra + totalAllowances - deductions;
                netSalaryInput.value = netSalary >= 0 ? netSalary : 0;
            }

            // Event listeners
            basicInput.addEventListener("input", calculateSalary);
            hraInput.addEventListener("input", calculateSalary);
            deductionInput.addEventListener("input", calculateSalary);

            allowanceInputs.forEach(input => {
                input.addEventListener("input", calculateSalary);
            });
            </script>

            <!-- Employment Info -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">Employment Info</div>
                    <div class="card-body">
                        <label>Date of Joining</label>
                        <input class="form-control mb-2" type="date" name="date_of_joining">
                        <input class="form-control mb-2" name="position" placeholder="Position">
                        <input class="form-control mb-2" name="department" placeholder="Department">
                        <label>Reporting Manager</label>
                        <select class="form-control mb-2" name="reporting_to">
                            <option value="">Select</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        <select class="form-control mb-2" name="status">
                            <option value="Active">Active</option>
                            <option value="Resigned">Resigned</option>
                            <option value="Terminated">Terminated</option>
                        </select>
                    </div>
                </div>
            </div>
            <style>
                .file-upload {
                    position: relative;
                    margin-bottom: 1rem;
                }

                .file-upload input[type="file"] {
                    position: absolute;
                    opacity: 0;
                    z-index: 2;
                    width: 100%;
                    height: 100%;
                    cursor: pointer;
                }

                .file-upload .file-label {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    background: #f8f9fa;
                    border: 1px solid #ced4da;
                    padding: 0.5rem 1rem;
                    border-radius: 6px;
                    font-size: 0.95rem;
                    color: #6c757d;
                }

                .file-upload img,
                .file-upload iframe {
                    max-height: 80px;
                    margin-top: 5px;
                    border: 1px solid #ccc;
                    border-radius: 5px;
                }

                .file-upload .preview {
                    margin-top: 0.5rem;
                }

                .file-label i {
                    margin-right: 8px;
                }
            </style>
             @endif
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
                                <div class="preview" id="preview_{{ $name }}"></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <script>
                function previewFile(input, id) {
                    const previewDiv = document.getElementById('preview_' + id);
                    previewDiv.innerHTML = '';

                    if (input.files && input.files[0]) {
                        const file = input.files[0];
                        const reader = new FileReader();

                        reader.onload = function (e) {
                            if (file.type.includes('image')) {
                                previewDiv.innerHTML = '<img src="' + e.target.result + '" class="img-fluid rounded">';
                            } else if (file.type === 'application/pdf') {
                                previewDiv.innerHTML = '<iframe src="' + e.target.result + '" width="100%" height="80"></iframe>';
                            } else {
                                previewDiv.innerHTML = '<span class="text-muted">📄 ' + file.name + '</span>';
                            }
                        };

                        reader.readAsDataURL(file);
                    }
                }
            </script>

        </div>

        <div class="text-right">
            <button class="btn btn-primary">Submit</button>
        </div>
    </form>
</div>

<script>
    const attendanceSource = document.getElementById('attendance_source');
    const radiusSection = document.getElementById('radius_section');

    attendanceSource.addEventListener('change', function () {
        radiusSection.style.display = this.value === 'Office' ? 'block' : 'none';
    });

    radiusSection.style.display = attendanceSource.value === 'Office' ? 'block' : 'none';
</script>
<script>
    const userDropdown = document.getElementById('linked_user');

    userDropdown.addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];

        document.getElementById('full_name').value = selected.getAttribute('data-name') || '';
        document.getElementById('email').value = selected.getAttribute('data-email') || '';
        document.getElementById('phone').value = selected.getAttribute('data-phone') || '';
    });

    function generateEmployeeCode() {
        const prefix = "ET";
        const letters = Array.from({ length: 3 }, () => String.fromCharCode(65 + Math.floor(Math.random() * 26))).join('');
        const numbers = Math.floor(100 + Math.random() * 900); // 3 digit
        const code = `${prefix}-${letters}${numbers}`;

        document.getElementById('employee_code').value = code;
    }
</script>
<script>
    document.getElementById('ifsc_code').addEventListener('blur', function () {
        let ifsc = this.value.trim();
        if (ifsc !== '') {
            fetch(`https://ifsc.razorpay.com/${ifsc}`)
                .then(response => {
                    if (!response.ok) throw new Error("Invalid IFSC code");
                    return response.json();
                })
                .then(data => {
                    document.getElementById('bank_name').value = data.BANK;
                    document.getElementById('bank_address').value = data.ADDRESS;
                })
                .catch(err => {
                    alert("Invalid IFSC code or not found.");
                    document.getElementById('bank_name').value = '';
                    document.getElementById('bank_address').value = '';
                });
        }
    });
</script>

@endsection
