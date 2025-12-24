{{-- resources/views/admin/leave_type/create.blade.php --}}

@extends('layouts.admin')
@section('content')

<div class="container py-4">
    <h4>Add Leave Type</h4>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.leave-types.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Leave Type Name</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="e.g. Sick Leave" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description (Optional)</label>
            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-success">Create</button>
        <a href="{{ route('admin.leave-types.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>

@endsection
