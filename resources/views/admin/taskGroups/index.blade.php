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
            <h3 class="gt-title">Task Groups</h3>
            <div class="gt-subtitle">Create teams, add members, and keep task assignment limited to the selected group.</div>
        </div>
        <div class="gt-actions">
            <a class="btn btn-success gt-btn" href="{{ route('admin.task-groups.create') }}"><i class="fas fa-plus"></i> New Group</a>
        </div>
    </div>

    <div class="gt-panel">
        <div class="gt-panel-header">
            <span><i class="fas fa-users-cog"></i> Groups</span>
        </div>
        <div class="gt-panel-body table-responsive">
            <table class="table table-hover datatable gt-table">
                <thead>
                    <tr>
                        <th>Group</th>
                        <th>Members</th>
                        <th>Tasks</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groups as $group)
                        <tr>
                            <td>
                                <div class="gt-task-row-title">{{ $group->name }}</div>
                                <div class="gt-task-row-meta">{{ \Illuminate\Support\Str::limit($group->description, 80) }}</div>
                            </td>
                            <td><span class="badge badge-light gt-badge">{{ $group->members_count }} Members</span></td>
                            <td><span class="badge badge-light gt-badge">{{ $group->tasks_count }} Tasks</span></td>
                            <td><span class="badge gt-badge badge-{{ $group->is_active ? 'success' : 'secondary' }}">{{ $group->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td>{{ optional($group->createdBy)->name }}</td>
                            <td>
                                <a class="btn btn-xs btn-primary" href="{{ route('admin.task-groups.show', $group) }}"><i class="fas fa-eye"></i></a>
                                <a class="btn btn-xs btn-info" href="{{ route('admin.task-groups.edit', $group) }}"><i class="fas fa-edit"></i></a>
                                <a class="btn btn-xs btn-success" href="{{ route('admin.group-tasks.create', ['group_id' => $group->id]) }}"><i class="fas fa-plus"></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
