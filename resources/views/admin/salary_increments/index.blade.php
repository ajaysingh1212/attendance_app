@extends('layouts.admin')

@section('content')
<!-- DataTables & SweetAlert CDN -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
.table-avatar {
  display:flex;
  align-items:center;
  gap:10px;
}
.table-avatar img {
  width:40px;
  height:40px;
  border-radius:50%;
  object-fit:cover;
  border:1px solid #e6e6e6;
}
.badge-pending{ background:#ffc107; color:#111; padding:6px 8px; border-radius:6px; }
.badge-approved{ background:#28a745; color:#fff; padding:6px 8px; border-radius:6px; }
.badge-rejected{ background:#dc3545; color:#fff; padding:6px 8px; border-radius:6px; }
.action-btn { margin-right:6px; }
</style>

<div class="container">
  

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif



    <div class="table-responsive shadow-sm rounded bg-white p-2">
    <div class="mb-3 d-flex  align-items-center">
        <div>
              <h3 class="mb-3">Salary Increments</h3>
        </div>
        <div style="margin-left: 20px;">
            <a href="{{ route('admin.salary-increments.create') }}" class="btn btn-primary">New Increment</a>
        </div>

        <div>
            <!-- optional filters could go here -->
        </div>
    </div>
        <table id="incrementsTable" class="display table table-hover" style="width:100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Employee</th>
                    <th>Old Gross</th>
                    <th>New Gross</th>
                    <th>Month</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($increments as $inc)
                    <tr data-id="{{ $inc->id }}">
                        <td>{{ $inc->id }}</td>
                        <td>
                            <div class="table-avatar">
                                @php
                                    $user = $inc->employee->user ?? null;
                                    $avatarUrl = null;

                                    if ($user) {
                                        $media = $user->getFirstMedia('image'); // 👈 collection_name = image
                                        if ($media) {
                                            $avatarUrl = $media->getUrl(); // original image
                                        }
                                    }
                                @endphp


                                @if($avatarUrl)
                                    <img src="{{ $avatarUrl }}" alt="avatar">
                                @else
                                    <img src="{{ asset('logo.png') }}" alt="avatar">
                                @endif

                                <div>
                                    <div><strong>{{ $inc->employee->employee_code ?? 'EMP-'.$inc->employee_id }}</strong></div>
                                    <div class="small text-muted">{{ $user->name ?? $inc->employee->full_name ?? 'No Name' }}</div>
                                </div>
                            </div>
                        </td>

                        <td>{{ number_format($inc->old_gross_salary ?? 0, 2) }}</td>
                        <td>{{ number_format($inc->new_gross_salary ?? 0, 2) }}</td>
                        <td>{{ $inc->increment_month }}</td>
                        <td>
                            @if($inc->status == 'pending')
                                <span class="badge-pending">Pending</span>
                            @elseif($inc->status == 'approved')
                                <span class="badge-approved">Approved</span>
                            @else
                                <span class="badge-rejected">Rejected</span>
                            @endif
                        </td>

                        <td>
                            <a href="{{ route('admin.salary-increments.edit', $inc->id) }}" class="btn btn-sm btn-info action-btn">Edit</a>

                            @if($inc->status == 'pending')
                                <button class="btn btn-sm btn-success action-btn btn-approve" data-id="{{ $inc->id }}">Approve</button>
                                <button class="btn btn-sm btn-danger action-btn btn-reject" data-id="{{ $inc->id }}">Reject</button>
                            @endif

                            <a href="{{ route('admin.salary-increments.letter', $inc->id) }}" target="_blank" class="btn btn-sm btn-secondary action-btn">Letter</a>

                            <!-- hidden forms for actions (approved/reject) -->
                            <form method="POST" action="{{ route('admin.salary-increments.approve', $inc->id) }}" id="approve-form-{{ $inc->id }}" style="display:none">
                                @csrf
                            </form>
                            <form method="POST" action="{{ route('admin.salary-increments.reject', $inc->id) }}" id="reject-form-{{ $inc->id }}" style="display:none">
                                @csrf
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-3">
            {{ $increments->links() }}
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    $('#incrementsTable').DataTable({
        paging: false, // server-side pagination used via Laravel links; DataTables still provides sorting/search
        info: false,
        ordering: true,
        columnDefs: [
            { orderable: false, targets: [1,6] }
        ],
        // keep default search box
    });

    // Approve button
    $(document).on('click', '.btn-approve', function(e){
        e.preventDefault();
        const id = $(this).data('id');
        Swal.fire({
            title: 'Approve increment?',
            icon: 'question',
            text: 'Approving will update employee salary. Are you sure?',
            showCancelButton: true,
            confirmButtonText: 'Yes, Approve',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((res) => {
            if(res.isConfirmed){
                $('#approve-form-' + id).submit();
            }
        });
    });

    // Reject button
    $(document).on('click', '.btn-reject', function(e){
        e.preventDefault();
        const id = $(this).data('id');
        Swal.fire({
            title: 'Reject increment?',
            icon: 'warning',
            text: 'This will mark the request as rejected.',
            showCancelButton: true,
            confirmButtonText: 'Yes, Reject',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((res) => {
            if(res.isConfirmed){
                $('#reject-form-' + id).submit();
            }
        });
    });

});
</script>
@endsection
