@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header"><h4>Adjustment Details</h4></div>

    <div class="card-body">
        <p><strong>Employee:</strong> {{ $payrollAdjustment->employee->name }}</p>
        <p><strong>Type:</strong> {{ ucfirst($payrollAdjustment->type) }}</p>
        <p><strong>Amount:</strong> ₹{{ number_format($payrollAdjustment->amount, 2) }}</p>
        <p><strong>Reason:</strong> {{ $payrollAdjustment->reason }}</p>
        <p><strong>Remarks:</strong> {{ $payrollAdjustment->remarks }}</p>
        <p><strong>Date:</strong> {{ $payrollAdjustment->adjustment_date }}</p>
    </div>
</div>
@endsection
