@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card shadow-lg mb-4 border-0">
        <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">✍️ Manual Adjustment - {{ $payroll->employee->full_name ?? 'N/A' }}</h4>
        </div>
        <div class="card-body">

            <div class="row">
                <!-- Employee Details -->
                <div class="col-md-6 mb-3">
                    <div class="card border-info shadow-sm h-100">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">👤 Employee Details</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item text-uppercase"><strong>Name:</strong> {{ $payroll->employee->full_name ?? 'N/A' }}</li>
                                <li class="list-group-item text-uppercase"><strong>Email:</strong> {{ $payroll->employee->email ?? 'N/A' }}</li>
                                <li class="list-group-item text-uppercase"><strong>Phone:</strong> {{ $payroll->employee->phone ?? 'N/A' }}</li>
                                <li class="list-group-item text-uppercase"><strong>Position:</strong> {{ $payroll->employee->position ?? 'N/A' }}</li>
                                <li class="list-group-item text-uppercase"><strong>Department:</strong> {{ $payroll->employee->department ?? 'N/A' }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Payroll Details -->
                <div class="col-md-6 mb-3">
                    <div class="card border-success shadow-sm h-100">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">💰 Payroll Details</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><strong>Month:</strong> {{ $payroll->month }}/{{ $payroll->year }}</li>
                                <li class="list-group-item"><strong>Working Days:</strong> {{ $payroll->working_days }}</li>
                                <li class="list-group-item"><strong>Absent Days:</strong> {{ $payroll->absent_days }}</li>
                                <li class="list-group-item"><strong>Sundays:</strong> {{ $payroll->sundays }}</li>
                                <li class="list-group-item"><strong>Present Days:</strong> {{ $payroll->present_days }}</li>
                                <li class="list-group-item"><strong>Net Salary:</strong> ₹ {{ number_format($payroll->net_salary, 2) }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manual Adjustment Form -->
            <div class="card border-primary shadow-sm mt-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">⚙️ Manual Adjustment Form</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.payrolls.manualAdjust', $payroll->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="employee_id" value="{{ $payroll->employee_id }}">

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">💵 Gross Salary</label>
                                <input type="number" step="0.01" id="grossSalary" name="gross_salary" class="form-control" 
                                    value="{{ old('gross_salary', $payroll->remaining_salary ?? $payroll->net_salary) }}" readonly>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">✍️ Adjustment Amount</label>
                                <input type="number" step="0.01" id="manualAdjustment" name="manual_adjustment" class="form-control" value="{{ old('manual_adjustment', 0) }}">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">➕ Other Adjustments</label>
                                <input type="number" step="0.01" id="otherAdjustments" name="other_adjustments" class="form-control" value="{{ old('other_adjustments', 0) }}">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">💳 Remaining Salary</label>
                                <input type="number" step="0.01" id="remainingSalary" name="remaining_salary" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3 mt-3">
                            <label class="form-label">📩 Message</label>
                            <textarea name="message" class="form-control">{{ old('message', $payroll->message) }}</textarea>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">📌 Status</label>
                            <select name="status" class="form-control">
                                <option value="pending" {{ $payroll->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ $payroll->status == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="processing" {{ $payroll->status == 'processing' ? 'selected' : '' }}>Processing</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-lg btn-primary mt-3">
                            <i class="fas fa-save"></i> Update Payroll
                        </button>

                        <!-- 🔸 Button for Part Payment Page -->
                        <a href="{{ route('admin.payrolls.partPaymentPage', $payroll->id) }}" class="btn btn-warning btn-lg mt-3 ms-3">
                            <i class="fas fa-credit-card"></i> Go to Part Payment
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let grossSalary = parseFloat(document.getElementById('grossSalary').value) || 0;
    let adjustmentInput = document.getElementById('manualAdjustment');
    let otherAdjustmentsInput = document.getElementById('otherAdjustments');
    let remainingField = document.getElementById('remainingSalary');

    function updateRemaining() {
        let adj = parseFloat(adjustmentInput.value) || 0;
        let other = parseFloat(otherAdjustmentsInput.value) || 0;
        remainingField.value = (grossSalary - adj - other).toFixed(2);
    }

    adjustmentInput.addEventListener('input', updateRemaining);
    otherAdjustmentsInput.addEventListener('input', updateRemaining);
    updateRemaining();
});
</script>
@endsection
