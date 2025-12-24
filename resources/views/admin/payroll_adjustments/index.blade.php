@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
          <a href="{{ route('admin.payroll-adjustments.create') }}" class="btn btn-primary mb-3">Add Adjustment</a>
          <a href="{{ route('admin.salary-structures.history') }}" class="btn btn-primary mb-3 float-right">History </a>
    </div>

    <div class="card-body">
      <h2 class="p-2">Payroll Adjustments</h2>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Reason</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Remarks</th>
                    <th>Paid date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($adjustments as $adj)
                    <tr>
                        <td>{{ $adj->employee->full_name ?? 'N/A' }} ({{  $adj->employee->employee_code ?? 'N/A' }})</td>
                        <td>{{ ucfirst($adj->type) }}</td>
                        <td>₹{{ $adj->amount }}</td>
                        <td>{{ $adj->reason }}</td>
                        <td>{{ $adj->created_at }}</td>
                        <td>{{ $adj->status }}</td>
                        <td>{{ $adj->updated_at }}</td>
                        <td>{{ $adj->remarks }}</td>
                        <td>
                            <a href="{{ route('admin.payroll-adjustments.edit', $adj->id) }}" class="btn btn-sm btn-info">Edit</a>
                            <form method="POST" action="{{ route('admin.payroll-adjustments.destroy', $adj->id) }}" style="display:inline;">
                                @csrf @method('DELETE')
                                <button onclick="return confirm('Are you sure?')" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
