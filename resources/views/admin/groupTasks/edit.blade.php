@extends('layouts.admin')

@section('styles')
    @include('admin.groupTasks.ui-style')
@endsection

@section('content')
<div class="gt-page">

    <div class="gt-hero">
        <div class="gt-hero-text">
            <h3 class="gt-title">✏️ Edit Task</h3>
            <div class="gt-subtitle">Update assignment, priority, due date, and supporting files.</div>
        </div>
        <div class="gt-actions">
            <a class="btn btn-light gt-btn" href="{{ route('admin.group-tasks.show', $groupTask) }}">
                <i class="fas fa-eye"></i> View Task
            </a>
            <a class="btn btn-light gt-btn" href="{{ route('admin.group-tasks.index') }}">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger" style="border-radius:10px;">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="gt-panel">
        <div class="gt-panel-header">
            <span><i class="fas fa-edit"></i> Task Information</span>
            <span class="gt-badge gt-badge-{{ $groupTask->status }}">{{ ucfirst($groupTask->status) }}</span>
        </div>
        <div class="gt-panel-body">
            <form method="POST" action="{{ route('admin.group-tasks.update', $groupTask) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.groupTasks.form')
                <hr>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <button class="btn btn-primary gt-btn" type="submit">
                        <i class="fas fa-save"></i> Update Task
                    </button>
                    <a class="btn btn-light gt-btn" href="{{ route('admin.group-tasks.show', $groupTask) }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
