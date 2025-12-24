@extends('layouts.admin')

@section('styles')
<style>
    .form-section {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        background-color: #f8f9fa;
    }

    .form-section h5 {
        margin-bottom: 1rem;
        font-weight: 600;
        color: #343a40;
    }

    .form-control:read-only {
        background-color: #e9ecef;
        font-weight: bold;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Edit Salary Structure</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.salary-structures.update', $salaryStructure) }}">
                @csrf
                @method('PUT')

                <div class="form-group mb-3">
                    <label for="employee_id">Employee</label>
                    <select name="employee_id" id="employee_id" class="form-select" disabled>
                        <option value="{{ $salaryStructure->employee->id }}">
                            {{ $salaryStructure->employee->full_name ?? $salaryStructure->employee->name }}
                        </option>
                    </select>
                </div>

                <div class="form-section">
                    <h5>Earnings</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Basic Salary</label>
                            <input type="number" name="basic" id="basic" class="form-control" value="{{ $salaryStructure->basic }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>HRA</label>
                            <input type="number" name="hra" id="hra" class="form-control" value="{{ $salaryStructure->hra }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Other Allowances</label>
                            <input type="number" name="allowance" id="allowance" class="form-control" value="{{ $salaryStructure->allowance }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Bonus</label>
                            <input type="number" name="bonus" id="bonus" class="form-control" value="{{ $salaryStructure->bonus }}">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h5>Deductions</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>PF</label>
                            <input type="number" name="pf" id="pf" class="form-control" value="{{ $salaryStructure->pf }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>ESI</label>
                            <input type="number" name="esi" id="esi" class="form-control" value="{{ $salaryStructure->esi }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>TDS</label>
                            <input type="number" name="tds" id="tds" class="form-control" value="{{ $salaryStructure->tds }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Other Deductions</label>
                            <input type="number" name="other_deductions" id="other_deductions" class="form-control" value="{{ $salaryStructure->other_deductions }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Advance</label>
                            <input type="number" name="advance" id="advance" class="form-control" value="{{ old('advance', 0) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Penalty</label>
                            <input type="number" name="penalty" id="penalty" class="form-control" value="{{ old('penalty', 0) }}">
                        </div>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label>Net Salary</label>
                    <input type="number" name="net_salary" id="net_salary" class="form-control fw-bold" value="{{ $salaryStructure->net_salary }}" readonly>
                    <small class="text-muted">Calculated dynamically</small>
                </div>

                <button type="submit" class="btn btn-success w-100">Update</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const fields = ['basic', 'hra', 'allowance', 'bonus', 'pf', 'esi', 'tds', 'other_deductions', 'advance', 'penalty'];

    function parseNumber(value) {
        return parseFloat((value || '0').toString().replace(/,/g, '')) || 0;
    }

    function calculateNetSalary() {
        let basic = parseNumber(document.getElementById('basic').value);
        let hra = parseNumber(document.getElementById('hra').value);
        let allowance = parseNumber(document.getElementById('allowance').value);
        let bonus = parseNumber(document.getElementById('bonus').value);

        let pf = parseNumber(document.getElementById('pf').value);
        let esi = parseNumber(document.getElementById('esi').value);
        let tds = parseNumber(document.getElementById('tds').value);
        let other_deductions = parseNumber(document.getElementById('other_deductions').value);
        let advance = parseNumber(document.getElementById('advance').value);
        let penalty = parseNumber(document.getElementById('penalty').value);

        let earnings = basic + hra + allowance + bonus;
        let deductions = pf + esi + tds + other_deductions + advance + penalty;

        let net = earnings - deductions;
        document.getElementById('net_salary').value = net.toFixed(2);
    }

    // Fix here — don't insert commas into input values
    function loadSalaryDetails(employeeId) {
        fetch(`/admin/payroll/${employeeId}/salary-details`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('basic').value = parseNumber(data.basic_salary).toFixed(2);
                document.getElementById('hra').value = parseNumber(data.hra).toFixed(2);
                document.getElementById('allowance').value = parseNumber(data.other_allowances).toFixed(2);
                document.getElementById('advance').value = parseNumber(data.advance).toFixed(2);
                document.getElementById('penalty').value = parseNumber(data.penalty).toFixed(2);
                calculateNetSalary();
            })
            .catch(error => console.error('Error loading salary details:', error));
    }

    // Add input listener to all fields
    fields.forEach(field => {
        const input = document.getElementById(field);
        if (input) {
            input.addEventListener('input', calculateNetSalary);
        }
    });

    // On page load
    window.addEventListener('load', function () {
        const employeeId = "{{ $salaryStructure->employee->id ?? '' }}";
        if (employeeId) {
            loadSalaryDetails(employeeId);
        } else {
            calculateNetSalary();
        }
    });
</script>
@endsection

