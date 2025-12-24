@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white">
        <strong>Branch Details</strong>
    </div>

    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <label><strong>ID:</strong></label>
                <p>{{ $branch->id }}</p>
            </div>
            <div class="col-md-6">
                <label><strong>Branch Title:</strong></label>
                <p>{{ $branch->title }}</p>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label><strong>Address:</strong></label>
                <p>{{ $branch->address }}</p>
            </div>
            <div class="col-md-6">
                <label><strong>Legal Name:</strong></label>
                <p>{{ $branch->legal_name }}</p>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label><strong>Incharge Name:</strong></label>
                <p>{{ $branch->incharge_name }}</p>
            </div>
            <div class="col-md-6">
                <label><strong>PAN Number:</strong></label>
                <p>{{ $branch->pan }}</p>
            </div>
        </div>

        @if($branch->branch_image)
        <div class="row mb-3">
            <div class="col-md-12">
                <label><strong>Branch Image:</strong></label><br>
                <img src="{{ $branch->branch_image->getUrl() }}" class="img-thumbnail" style="max-height: 200px;" alt="Branch Image">
            </div>
        </div>
        @endif

        <div class="form-group mt-4">
            <a class="btn btn-secondary" href="{{ route('admin.branches.index') }}">
                {{ trans('global.back_to_list') }}
            </a>
        </div>
    </div>
</div>
@endsection
