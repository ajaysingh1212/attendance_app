@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card shadow-lg border-0">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
            <h4 class="mb-0">💳 Part Payment - {{ $payroll->employee->full_name ?? 'N/A' }}</h4>
            <a href="{{ route('admin.payroll.list') }}" class="btn btn-dark">
                ← Back to Payroll List
            </a>
            <a href="{{ route('admin.payrolls.manualAdjustPage', $payroll->id) }}" class="btn btn-dark">
                ← Back to Payroll Adjustment
            </a>

        </div>

        <div class="card-body">
            <form action="{{ route('admin.payrolls.partPayment', $payroll->id) }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">💵 Net Salary</label>
                        <input type="number" step="0.01" class="form-control" 
                            value="{{ ($payroll->remaining_salary === null || $payroll->remaining_salary <= 0) ? $payroll->net_salary : $payroll->remaining_salary }}" 
                            readonly>
                    </div>
              

                    <div class="col-md-3 mb-3">
                        <label class="form-label">📅 Payment Date</label>
                        <input type="date" name="payment_date" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">💰 Part Payment Amount</label>
                        <input type="number" step="0.01" id="partAmount" name="part_amount" class="form-control" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">💳 Remaining After Payment</label>
                        <input type="number" step="0.01" id="remainingAfterPart" class="form-control" readonly>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">📝 Note (Optional)</label>
                        <textarea name="note" class="form-control" placeholder="Enter note..."></textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-credit-card"></i> Save Part Payment
                </button>
            </form>

            <hr>

            <h5 class="mt-4">📋 Part Payment History</h5>
            @php
                $partPayments = \App\Models\PayrollPartPayment::where('payroll_id', $payroll->id)->latest()->get();
                $totalPaid = $partPayments->sum('part_amount');
                $remainingTotal = $payroll->net_salary - $totalPaid;
            @endphp

            @if($partPayments->count() > 0)
                <table class="table table-bordered table-striped mt-3">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Part Amount</th>
                            <th>Remaining</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($partPayments as $index => $pp)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><span class="text-success">{{ $pp->payment_date->format('d M Y') }}</span></td>
                                <td><span class="text-success">₹ {{ number_format($pp->part_amount, 2) }}</span> </td>
                                <td><span class="text-success">₹ {{ number_format($pp->remaining_amount, 2) }}</span> </td>
                                <td><span class="text-success">{{ $pp->additional_data['note'] ?? '-' }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-light">
                            <th colspan="2" class="text-warning">Total Paid:</th>
                            <th><span class="text-warning">₹ {{ number_format($totalPaid, 2) }}</span> </th>
                            <th colspan="2"><span class="text-warning">Remaining: ₹ {{ number_format($remainingTotal, 2) }}</span> </th>
                        </tr>
                    </tfoot>
                </table>
            @else
                <p class="text-muted">No part payments found.</p>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const netSalary = {{ ($payroll->remaining_salary === null || $payroll->remaining_salary <= 0) ? $payroll->net_salary : $payroll->remaining_salary }};
                            const totalPaid = {{ $partPayments->sum('part_amount') }};
                            const partAmount = document.getElementById('partAmount');
                            const remainingAfterPart = document.getElementById('remainingAfterPart');

                            partAmount.addEventListener('input', function() {
                                const entered = parseFloat(partAmount.value) || 0;
                                const remaining = netSalary - totalPaid - entered;
                                remainingAfterPart.value = remaining.toFixed(2);
                            });
                        });

</script>
@endsection
