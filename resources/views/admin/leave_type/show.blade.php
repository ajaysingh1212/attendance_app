{{-- resources/views/admin/leave_type/show.blade.php --}}

@extends('layouts.admin')
@section('content')

<div class="container py-4">
    <h4>Leave Type Details</h4>

    <div class="card mt-3">
        <div class="card-body">
            <h5 class="card-title">{{ $leaveType->name }}</h5>
            <p class="card-text"><strong>Description:</strong><br> {{ $leaveType->description ?? 'N/A' }}</p>
            <p class="card-text"><strong>Created At:</strong> {{ $leaveType->created_at->format('d M Y, h:i A') }}</p>
        </div>
        <div class="card-footer text-end">
            <a href="{{ route('admin.leave-types.edit', $leaveType->id) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('admin.leave-types.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>

@endsection
