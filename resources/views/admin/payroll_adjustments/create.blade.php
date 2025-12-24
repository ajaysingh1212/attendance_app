@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        <h4>{{ isset($payrollAdjustment) ? 'Edit' : 'Add' }} Payroll Adjustment</h4>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ isset($payrollAdjustment) ? route('admin.payroll-adjustments.update', $payrollAdjustment->id) : route('admin.payroll-adjustments.store') }}">
            @csrf
            @if(isset($payrollAdjustment)) @method('PUT') @endif

            <div class="form-group">
                <label>Employee</label>
                <select name="employee_id" class="form-control" required>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" {{ (isset($payrollAdjustment) && $payrollAdjustment->employee_id == $employee->id) ? 'selected' : '' }}>
                            {{ $employee->full_name }}- {{ $employee->employee_code }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Type</label>
                <select name="type" class="form-control" required>
                    <option value="advance" {{ (isset($payrollAdjustment) && $payrollAdjustment->type == 'Advance') ? 'selected' : '' }}>Advance</option>
                    <option value="penalty" {{ (isset($payrollAdjustment) && $payrollAdjustment->type == 'Penalty') ? 'selected' : '' }}>Penalty</option>
                </select>
            </div>

            <div class="form-group">
                <label>Amount</label>
                <input type="number" step="0.01" name="amount" value="{{ $payrollAdjustment->amount ?? '' }}" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Reason</label>
                <input type="text" name="reason" value="{{ $payrollAdjustment->reason ?? '' }}" class="form-control">
            </div>

            <div class="form-group">
                <label>Remarks</label>
                <textarea name="remarks" class="form-control">{{ $payrollAdjustment->remarks ?? '' }}</textarea>
            </div>

            <div class="form-group">
                <label>Adjustment Date</label>
                <input type="date" name="adjustment_date" value="{{ isset($payrollAdjustment) ? $payrollAdjustment->adjustment_date->format('Y-m-d') : now()->toDateString() }}" class="form-control" required>
            </div>

            <button class="btn btn-success">{{ isset($payrollAdjustment) ? 'Update' : 'Save' }}</button>
        </form>
    </div>
</div>
@endsection
