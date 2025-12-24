

@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
           <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Employees List</h4>
        <a href="{{ route('admin.employees.create') }}" class="btn btn-success">+ Create Employee</a>
    </div>

    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover datatable datatable-Employee">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Employee Code</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Branches</th>
                        <th>Position</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th width="160px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $index => $employee)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $employee->employee_code }}</td>
                            <td>{{ $employee->full_name }}</td>
                            <td>{{ $employee->email }}</td>
                            <td>{{ $employee->phone }}</td>
                            <td>{{ $employee->branch->title ?? 'Anywhere'}}</td>
                            <td>{{ $employee->position }}</td>
                            <td>{{ $employee->department }}</td>
                            <td>
                                <span class="badge badge-{{ $employee->status == 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($employee->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.employees.show', $employee->id) }}" class="btn btn-sm btn-secondary">View</a>
                                <a href="{{ route('admin.payroll.edit', $employee->id) }}" class="btn btn-sm btn-info">Edit</a>
                                <form action="{{ route('admin.employees.destroy', $employee->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
    $(function () {
        $('.datatable-Employee').DataTable({
            pageLength: 25,
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: false, targets: -1 }
            ]
        });
    });
</script>
@endsection
