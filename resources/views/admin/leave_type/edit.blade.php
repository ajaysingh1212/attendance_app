@extends('layouts.admin')

@section('content')
<div class="container">
    <h2 class="mb-4">Edit Leave Type</h2>

    <form action="{{ route('admin.leave-types.update', $leaveType->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group mb-3">
            <label for="name">Leave Type Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $leaveType->name) }}" required>
        </div>

        <div class="form-group mb-3">
            <label for="description">Description (optional)</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description', $leaveType->description) }}</textarea>
        </div>

        <div class="form-group mb-3">
            <label for="status">Status</label>
            <select name="status" class="form-control" required>
                <option value="active" {{ $leaveType->status == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ $leaveType->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Update Leave Type</button>
        <a href="{{ route('admin.leave-types.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
