@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">Add Office</div>
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif
        <form action="{{ route('admin.office-branches.store') }}" method="POST">
            @include('admin.officeBranches._form')
        </form>
    </div>
</div>
@endsection
