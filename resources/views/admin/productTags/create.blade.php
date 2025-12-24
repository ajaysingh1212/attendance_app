@extends('layouts.admin')

@section('content')
<div class="container py-5">
    <div class="card shadow-lg border-0 rounded-4 animate__animated animate__fadeInDown">
        <div class="card-header bg-gradient text-white text-center fw-bold rounded-top-4" style="background: linear-gradient(90deg, #3b82f6, #9333ea);">
            Create Product Tag
        </div>
        <div class="card-body">
            <form action="{{ route('admin.product-tags.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Tag Name</label>
                    <input type="text" name="name" class="form-control rounded-pill" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-control rounded-pill" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control rounded-4"></textarea>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="status" value="1" checked class="form-check-input">
                    <label class="form-check-label">Active</label>
                </div>
                <button type="submit" class="btn btn-gradient w-100 py-2 rounded-pill fw-bold">Save</button>
            </form>
        </div>
    </div>
</div>

<style>
.btn-gradient {
    background: linear-gradient(90deg, #9333ea, #3b82f6);
    color: white;
    transition: all 0.3s ease-in-out;
}
.btn-gradient:hover {
    transform: scale(1.05);
    box-shadow: 0px 4px 15px rgba(0,0,0,0.3);
}
</style>
@endsection
