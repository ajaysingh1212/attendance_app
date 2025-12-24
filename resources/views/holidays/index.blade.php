@extends('layouts.admin')

@section('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">

<style>
    /* Page Heading */
    h2 {
        font-weight: bold;
        color: #0d6efd;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Table Container */
    .table-wrapper {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.08);
    }

    /* DataTable Styling */
    #holidayTable {
        border-radius: 10px;
        overflow: hidden;
        width: 100%;
    }

    #holidayTable thead {
        background: #007bff;
        color: #fff;
        text-align: center;
    }

    #holidayTable thead th {
        padding: 12px;
        font-size: 15px;
    }

    #holidayTable tbody tr {
        transition: all 0.2s ease-in-out;
    }

    #holidayTable tbody tr:hover {
        background-color: #f1f8ff;
        transform: scale(1.01);
    }

    /* Button Styling */
    .btn {
        border-radius: 6px;
        padding: 5px 12px;
    }

    .btn-info {
        background-color: #17a2b8;
        border-color: #17a2b8;
    }

    .btn-warning {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #000;
    }

    .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
    }

    /* Datatable Controls */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 10px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 5px 12px;
        border-radius: 5px;
        background: #f1f1f1;
        margin: 2px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #007bff !important;
        color: #fff !important;
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>🎉 Holiday List</h2>
    <a href="{{ route('admin.holidays.create') }}" class="btn btn-primary">
        ➕ Add New Holiday
    </a>
</div>

<div class="table-wrapper">
    <table id="holidayTable" class="table table-bordered table-striped table-hover nowrap">
        <thead>
            <tr>
                <th>Title</th>
                <th>Date(s)</th>
                <th>Type</th>
                <th>Optional</th>
                <th>National</th>
                <th style="width: 160px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($holidays as $holiday)
                <tr>
                    <td>{{ $holiday->title }}</td>
                    <td>{{ $holiday->start_date }}{{ $holiday->end_date ? ' to ' . $holiday->end_date : '' }}</td>
                    <td>{{ $holiday->holiday_type }}</td>
                    <td>
                        <span class="badge bg-{{ $holiday->is_optional ? 'success' : 'secondary' }}">
                            {{ $holiday->is_optional ? 'Yes' : 'No' }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-{{ $holiday->is_national ? 'info' : 'dark' }}">
                            {{ $holiday->is_national ? 'Yes' : 'No' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.holidays.show', $holiday) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('admin.holidays.edit', $holiday) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('admin.holidays.destroy', $holiday) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Delete this holiday?')" class="btn btn-danger btn-sm">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@section('scripts')
<!-- DataTables JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    $('#holidayTable').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, 200], [5, 10, 25, 50, 100, 200]],
        language: {
            search: "🔍 Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ holidays",
            infoEmpty: "No holidays available",
            zeroRecords: "No matching holidays found",
        },
        columnDefs: [
            { orderable: false, targets: [5] } // Disable sorting on Actions column
        ]
    });
});
</script>
@endsection
