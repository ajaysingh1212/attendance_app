@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="card shadow-sm">
        <div class="card-header bg-info text-white">
            <h4 class="mb-0">Holiday Details</h4>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Title:</dt>
                <dd class="col-sm-9">{{ $holiday->title }}</dd>

                <dt class="col-sm-3">Start Date:</dt>
                <dd class="col-sm-9">{{ $holiday->start_date }}</dd>

                <dt class="col-sm-3">End Date:</dt>
                <dd class="col-sm-9">{{ $holiday->end_date ?? 'N/A' }}</dd>

                <dt class="col-sm-3">Type:</dt>
                <dd class="col-sm-9">{{ $holiday->holiday_type }}</dd>

                <dt class="col-sm-3">Optional:</dt>
                <dd class="col-sm-9">{{ $holiday->is_optional ? 'Yes' : 'No' }}</dd>

                <dt class="col-sm-3">National Holiday:</dt>
                <dd class="col-sm-9">{{ $holiday->is_national ? 'Yes' : 'No' }}</dd>

                <dt class="col-sm-3">Description:</dt>
                <dd class="col-sm-9">{{ $holiday->description ?? 'N/A' }}</dd>
            </dl>
        </div>
    </div>
</div>
@endsection
