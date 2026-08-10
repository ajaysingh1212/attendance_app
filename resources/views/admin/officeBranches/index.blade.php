@extends('layouts.admin')

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@can('branch_create')
<div class="mb-3">
    <a class="btn btn-success" href="{{ route('admin.office-branches.create') }}">Add Office</a>
</div>
@endcan

<div class="card">
    <div class="card-header">Office Name List</div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Office Name</th>
                    <th>Address</th>
                    <th>City</th>
                    <th>State</th>
                    <th>Pincode</th>
                    <th>Incharge</th>
                    <th>Details</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($offices as $office)
                    <tr>
                        <td>{{ $office->id }}</td>
                        <td>{{ $office->branch_name }}</td>
                        <td>{{ $office->address_line ?? '-' }}</td>
                        <td>{{ $office->city ?? '-' }}</td>
                        <td>{{ $office->state ?? '-' }}</td>
                        <td>{{ $office->pincode ?? '-' }}</td>
                        <td>{{ $office->incharge_name ?? '-' }}<br><small>{{ $office->incharge_phone ?? '' }}</small></td>
                        <td>{{ \Illuminate\Support\Str::limit($office->registration_detail, 80) }}</td>
                        <td>
                            @can('branch_show')
                                <a class="btn btn-sm btn-primary" href="{{ route('admin.office-branches.show', $office->id) }}">View</a>
                            @endcan
                            @can('branch_edit')
                                <a class="btn btn-sm btn-info" href="{{ route('admin.office-branches.edit', $office->id) }}">Edit</a>
                            @endcan
                            @can('branch_delete')
                                <form action="{{ route('admin.office-branches.destroy', $office->id) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Delete this office?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center">No offices found.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $offices->links() }}
    </div>
</div>
@endsection
