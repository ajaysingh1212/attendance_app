@extends('layouts.admin')

@section('styles')
    @include('admin.groupTasks.ui-style')
@endsection

@section('content')
<div class="gt-page">

    <div class="gt-hero">
        <div class="gt-hero-text">
            <h3 class="gt-title">➕ Create Group Task</h3>
            <div class="gt-subtitle">Select a group, assign members, set priority and attach files or voice notes.</div>
        </div>
        <div class="gt-actions">
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
            <span><i class="fas fa-paper-plane"></i> Task Information</span>
        </div>
        <div class="gt-panel-body">
            <form method="POST" action="{{ route('admin.group-tasks.store') }}" enctype="multipart/form-data">
                @csrf
                @include('admin.groupTasks.form', ['groupTask' => null])
                <hr>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <button class="btn btn-success gt-btn" type="submit">
                        <i class="fas fa-paper-plane"></i> Create &amp; Notify Members
                    </button>
                    <a class="btn btn-light gt-btn" href="{{ route('admin.group-tasks.index') }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
