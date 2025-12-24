@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Leave Types</h4>
            <a href="{{ route('admin.leave-types.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Leave Type
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="leaveTypeTable" class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th>#</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Created At</th>
                            <th width="180">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaveTypes as $key => $type)
                        <tr>
                            <td><input type="checkbox" class="row-checkbox" value="{{ $type->id }}"></td>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $type->name }}</td>
                            <td>{{ $type->description ?? '-' }}</td>
                            <td>{{ $type->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('admin.leave-types.show', $type->id) }}" class="btn btn-sm btn-info me-1">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.leave-types.edit', $type->id) }}" class="btn btn-sm btn-warning me-1">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.leave-types.destroy', $type->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this leave type?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    .dataTables_filter input {
        border-radius: 20px;
        padding: 6px 12px;
        border: 1px solid #ced4da;
    }
</style>
@endsection

@section('scripts')
<!-- jQuery (required for DataTables) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function () {
        let table = $('#leaveTypeTable').DataTable({
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search leave types..."
            },
            columnDefs: [
                { orderable: false, targets: 0 } // Disable ordering for the checkbox column
            ]
        });

        // "Select All" checkbox functionality
        $('#select-all').on('click', function () {
            let isChecked = $(this).is(':checked');
            $('.row-checkbox').prop('checked', isChecked);
        });

        // Uncheck "Select All" if one is unchecked
        $(document).on('change', '.row-checkbox', function () {
            if (!this.checked) {
                $('#select-all').prop('checked', false);
            } else if ($('.row-checkbox:checked').length === $('.row-checkbox').length) {
                $('#select-all').prop('checked', true);
            }
        });
    });
</script>
@endsection
