@if($payroll->partPayments->count() > 0)
    <table class="table table-bordered table-striped">
        <thead class="bg-warning text-dark">
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Amount (₹)</th>
                <th>Remaining (₹)</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payroll->partPayments as $index => $p)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($p->payment_date)->format('d M Y') }}</td>
                    <td>₹{{ number_format($p->part_amount, 2) }}</td>
                    <td>₹{{ number_format($p->remaining_amount, 2) }}</td>
                    <td>{{ $p->additional_data['note'] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-light">
                <th colspan="2" class="text-end">Total Paid:</th>
                <th>₹{{ number_format($payroll->partPayments->sum('part_amount'), 2) }}</th>
                <th colspan="2">Remaining: ₹{{ number_format($payroll->remaining_amount, 2) }}</th>
            </tr>
        </tfoot>
    </table>
@else
    <p class="text-muted text-center">No part payments found for this payroll.</p>
@endif
