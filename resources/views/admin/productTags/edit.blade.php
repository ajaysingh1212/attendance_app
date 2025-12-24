@extends('layouts.admin')

@section('content')
<div class="container py-5">
    <div class="card shadow-lg border-0 rounded-4 animate__animated animate__fadeInUp">
        <div class="card-header bg-gradient text-white text-center fw-bold rounded-top-4" style="background: linear-gradient(90deg, #10b981, #3b82f6);">
            Edit Product Tag
        </div>
        <div class="card-body">
            <form action="{{ route('admin.product-tags.update', $productTag->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Tag Name</label>
                    <input type="text" name="name" value="{{ $productTag->name }}" class="form-control rounded-pill" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" value="{{ $productTag->slug }}" class="form-control rounded-pill" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control rounded-4">{{ $productTag->description }}</textarea>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="status" value="1" {{ $productTag->status ? 'checked' : '' }} class="form-check-input">
                    <label class="form-check-label">Active</label>
                </div>
                <button type="submit" class="btn btn-success w-100 py-2 rounded-pill fw-bold">Update</button>
            </form>
        </div>
    </div>
</div>
@endsection
