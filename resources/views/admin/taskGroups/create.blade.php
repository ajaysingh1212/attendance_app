@extends('layouts.admin')
@section('styles')
@include('admin.groupTasks.ui-style')
@endsection
@section('content')
<div class="gt-page">
    <div class="gt-hero">
        <div>
            <h3 class="gt-title">Create Task Group</h3>
            <div class="gt-subtitle">Build a team and set each member's role for task collaboration.</div>
        </div>
    </div>
    <div class="gt-panel">
        <div class="gt-panel-header"><span><i class="fas fa-users-cog"></i> Group Information</span></div>
        <div class="gt-panel-body">
        <form method="POST" action="{{ route('admin.task-groups.store') }}">
            @csrf
            @include('admin.taskGroups.form', ['taskGroup' => null])
            <button class="btn btn-danger gt-btn" type="submit"><i class="fas fa-save"></i> Save Group</button>
            <a class="btn btn-default" href="{{ route('admin.task-groups.index') }}">Back</a>
        </form>
        </div>
    </div>
</div>
@endsection
