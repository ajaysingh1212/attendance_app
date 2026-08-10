@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">Edit Office</div>
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif
        <form action="{{ route('admin.office-branches.update', $officeBranch->id) }}" method="POST">
            @method('PUT')
            @include('admin.officeBranches._form')
        </form>
    </div>
</div>
@endsection
