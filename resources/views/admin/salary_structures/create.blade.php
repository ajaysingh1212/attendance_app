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
@if(session('error'))
    <script>
        alert("{{ session('error') }}");
    </script>
@endif

@if(session('success'))
    <script>
        alert("{{ session('success') }}");
    </script>
@endif

<div class="container">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">{{ isset($salaryStructure) ? 'Edit' : 'Add' }} Salary Structure</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ isset($salaryStructure) ? route('admin.salary-structures.update', $salaryStructure) : route('admin.salary-structures.store') }}">
                @csrf
                @if(isset($salaryStructure))
                    @method('PUT')
                @endif

                <div class="form-group mb-3">
                    <label for="employee_id">Employee</label>
                    <select name="employee_id" id="employee_id" class="form-select" {{ isset($salaryStructure) ? 'disabled' : '' }} required>
                        <option value="">Select Employee</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}"
                                {{ isset($salaryStructure) && $salaryStructure->employee_id == $employee->id ? 'selected' : '' }}>
                                {{ $employee->full_name ?? $employee->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-section">
                    <h5>Earnings</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Basic Salary</label>
                            <input type="number" name="basic" id="basic" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>HRA</label>
                            <input type="number" name="hra" id="hra" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Other Allowances</label>
                            <input type="number" name="allowance" id="allowance" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Bonus</label>
                            <input type="number" name="bonus" id="bonus" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h5>Deductions</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>PF</label>
                            <input type="number" name="pf" id="pf" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>ESI</label>
                            <input type="number" name="esi" id="esi" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>TDS</label>
                            <input type="number" name="tds" id="tds" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Other Deductions</label>
                            <input type="number" name="other_deductions" id="other_deductions" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Advance</label>
                            <input type="number" name="advance" id="advance" class="form-control" >
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Penalty</label>
                            <input type="number" name="penalty" id="penalty" class="form-control" >
                        </div>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label>Net Salary</label>
                    <input type="number" name="net_salary" id="net_salary" class="form-control fw-bold" readonly>
                    <small class="text-muted">Calculated dynamically</small>
                </div>

                <button type="submit" class="btn btn-success w-100">{{ isset($salaryStructure) ? 'Update' : 'Save' }}</button>
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

    fields.forEach(field => {
        const input = document.getElementById(field);
        if (input) {
            input.addEventListener('input', calculateNetSalary);
        }
    });

    document.getElementById('employee_id').addEventListener('change', function () {
        const employeeId = this.value;
        if (!employeeId) return;

        fetch(`/admin/payroll/${employeeId}/salary-details`)
            .then(response => response.json())
            .then(data => {
                console.log("Salary details fetched:", data);

                document.getElementById('basic').value = parseNumber(data.basic_salary);
                document.getElementById('hra').value = parseNumber(data.hra);
                document.getElementById('allowance').value = parseNumber(data.other_allowances);
                document.getElementById('other_deductions').value = parseNumber(data.deductions);

                document.getElementById('bonus').value = 0;
                document.getElementById('pf').value = 0;
                document.getElementById('esi').value = 0;
                document.getElementById('tds').value = 0;

                document.getElementById('advance').value = parseNumber(data.advance);
                document.getElementById('penalty').value = parseNumber(data.penalty);

                calculateNetSalary();
            })
            .catch(error => {
                console.error("Error fetching salary details:", error);
            });
    });

    window.addEventListener('load', calculateNetSalary);
</script>
@endsection
