@extends('layouts.admin')

@section('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="card">
    <div class="card-header">Salary Structures
        <a href="{{ route('admin.salary-structures.create') }}" class="btn btn-outline-success mb-3 float-right">Add Structure</a>

    </div>
    <div class="card-body">
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="salaryStructuresTable">
                <thead class="table-dark">
                    <tr>
                        <th>Employee</th>
                        <th>Net Pay</th>
                        <th>Basic</th>
                        <th>HRA</th>
                        <th>Allowance</th>
                        <th>Bonus</th>
                        <th>Deductions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($structures as $structure)
                        <tr>
                            <td>{{ $structure->employee->employee_code }} - {{ $structure->employee->full_name }}</td>
                            <td>{{ $structure->net_salary }}</td>
                            <td>{{ $structure->basic }}</td>
                            <td>{{ $structure->hra }}</td>
                            <td>{{ $structure->allowance }}</td>
                            <td>{{ $structure->bonus }}</td>
                            <td>
                                {{ ($structure->pf + $structure->esi + $structure->tds + $structure->other_deductions) ?? 0 }}
                            </td>
                            <td>
                                <a href="{{ route('admin.salary-structures.edit', $structure) }}" class="btn btn-sm btn-info">Edit</a>
                                <form action="{{ route('admin.salary-structures.destroy', $structure) }}" method="POST" style="display:inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this?')">Delete</button>
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
    <!-- jQuery + DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#salaryStructuresTable').DataTable({
                pageLength: 5,
                lengthMenu: [[5, 10, 25, 50, 100, 200], [5, 10, 25, 50, 100, 200]],
                searching: true,
                ordering: false,
                responsive: true
            });
        });
    </script>
@endsection
