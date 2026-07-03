@extends('layouts.admin')
@section('styles')
@include('admin.groupTasks.ui-style')
@endsection
@section('scripts')
@parent
@include('admin.groupTasks.datatable-script')
@endsection
@section('content')
<div class="gt-page">
<div class="gt-hero">
    <div>
        <h3 class="gt-title">{{ $taskGroup->name }}</h3>
        <div class="gt-subtitle">{{ $taskGroup->description }}</div>
    </div>
    <div class="gt-actions">
        <a class="btn btn-success gt-btn" href="{{ route('admin.group-tasks.create', ['group_id' => $taskGroup->id]) }}"><i class="fas fa-plus"></i> New Task</a>
        <a class="btn btn-info gt-btn" href="{{ route('admin.task-groups.edit', $taskGroup) }}"><i class="fas fa-edit"></i> Edit</a>
        <a class="btn btn-default gt-btn" href="{{ route('admin.task-groups.index') }}">Back</a>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="gt-panel">
            <div class="gt-panel-header">Members</div>
            <div class="gt-panel-body">
                @foreach($taskGroup->members as $member)
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span>{{ $member->name }}</span>
                        <span class="badge badge-light">{{ $member->pivot->member_role }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="gt-panel">
            <div class="gt-panel-header">Group Tasks</div>
            <div class="gt-panel-body table-responsive">
                <table class="table table-hover datatable gt-table">
                    <thead><tr><th>Task</th><th>Status</th><th>Accepted By</th><th>Completed By</th><th>&nbsp;</th></tr></thead>
                    <tbody>
                        @foreach($taskGroup->tasks as $task)
                            <tr>
                                <td>{{ $task->title }}</td>
                                <td><span class="badge badge-info">{{ ucfirst($task->status) }}</span></td>
                                <td>{{ optional($task->acceptedBy)->name ?: '-' }}</td>
                                <td>{{ optional($task->completedBy)->name ?: '-' }}</td>
                                <td><a class="btn btn-xs btn-primary" href="{{ route('admin.group-tasks.show', $task) }}">Open</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
