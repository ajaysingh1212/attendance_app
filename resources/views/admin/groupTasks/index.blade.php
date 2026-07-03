@extends('layouts.admin')

@section('styles')
    @include('admin.groupTasks.ui-style')
    <style>
        .ring-alert { display: none; position: sticky; top: 0; z-index: 10; border-radius: 10px; margin-bottom: 14px; }
        .ring-alert.visible { display: flex; align-items: center; justify-content: space-between; }
    </style>
@endsection

@section('content')
<div class="gt-page">

    {{-- Sticky ring alert --}}
    <div id="taskRingAlert" class="alert alert-danger ring-alert">
        <div style="display:flex;align-items:center;gap:10px;flex:1;">
            <span style="font-size:1.3rem;">🔔</span>
            <div>
                <strong>New task assigned!</strong>
                <span id="taskRingText" style="margin-left:6px;"></span>
            </div>
        </div>
        <a id="taskRingLink" class="btn btn-sm btn-light ml-2" href="#">Open</a>
    </div>

    {{-- Hero --}}
    <div class="gt-hero">
        <div class="gt-hero-text">
            <h3 class="gt-title">📋 Group Tasks</h3>
            <div class="gt-subtitle">Manage, assign, accept and complete tasks across all your groups.</div>
        </div>
        <div class="gt-actions">
            <a class="btn btn-light gt-btn" href="{{ route('admin.group-tasks.report') }}">
                <i class="fas fa-chart-bar"></i> Report
            </a>
            @if(Auth::user()->is_admin)
            <a class="btn btn-light gt-btn" href="{{ route('admin.task-groups.index') }}">
                <i class="fas fa-layer-group"></i> Groups
            </a>
            @endif
            <a class="btn btn-success gt-btn" href="{{ route('admin.group-tasks.create') }}">
                <i class="fas fa-plus"></i> New Task
            </a>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="gt-summary-grid">
        <div class="gt-summary-card" style="--accent:#3b82f6">
            <div class="card-icon">📋</div>
            <small>Total</small>
            <strong data-summary="total">{{ $summary['total'] }}</strong>
        </div>
        <div class="gt-summary-card" style="--accent:#f59e0b">
            <div class="card-icon">⏳</div>
            <small>Pending</small>
            <strong data-summary="pending">{{ $summary['pending'] }}</strong>
        </div>
        <div class="gt-summary-card" style="--accent:#06b6d4">
            <div class="card-icon">✅</div>
            <small>Accepted</small>
            <strong data-summary="accepted">{{ $summary['accepted'] }}</strong>
        </div>
        <div class="gt-summary-card" style="--accent:#10b981">
            <div class="card-icon">🏁</div>
            <small>Completed</small>
            <strong data-summary="completed">{{ $summary['completed'] }}</strong>
        </div>
        <div class="gt-summary-card" style="--accent:#ef4444">
            <div class="card-icon">⚠️</div>
            <small>Delayed</small>
            <strong data-summary="delayed">{{ $summary['delayed'] }}</strong>
        </div>
    </div>

    {{-- Filter tabs + group filter --}}
    <div class="gt-panel">
        <div class="gt-panel-header">
            <span><i class="fas fa-filter"></i> Filter Tasks</span>
        </div>
        <div class="gt-panel-body" style="padding:14px 20px;">
            <div style="display:flex;flex-wrap:wrap;align-items:center;gap:10px;">
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                    <a class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-secondary' }}"
                       href="{{ route('admin.group-tasks.index', array_merge(request()->except('status'), [])) }}">All</a>
                    @foreach(['pending'=>'⏳ Pending','accepted'=>'✅ Accepted','completed'=>'🏁 Completed'] as $s => $l)
                    <a class="btn btn-sm {{ request('status')===$s ? 'btn-primary' : 'btn-outline-secondary' }}"
                       href="{{ route('admin.group-tasks.index', array_merge(request()->all(), ['status'=>$s])) }}">{{ $l }}</a>
                    @endforeach
                </div>
                @if($myGroups->count())
                <select class="form-control form-control-sm" style="width:200px;"
                        onchange="location.href='{{ route('admin.group-tasks.index') }}?group_id='+this.value+'&status={{ request('status') }}'">
                    <option value="">— All Groups —</option>
                    @foreach($myGroups as $g)
                    <option value="{{ $g->id }}" {{ request('group_id')==$g->id?'selected':'' }}>{{ $g->name }}</option>
                    @endforeach
                </select>
                @endif
            </div>
        </div>
    </div>

    {{-- Task list --}}
    <div class="gt-panel">
        <div class="gt-panel-header">
            <span><i class="fas fa-clipboard-list"></i> Task List</span>
            <span style="font-size:.78rem;color:#64748b;">{{ $tasks->count() }} task(s)</span>
        </div>
        <div class="gt-panel-body" style="padding:0;">
            <div class="table-responsive">
                <table class="table table-hover datatable gt-table">
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Group</th>
                            <th>Priority</th>
                            <th>Assigned To</th>
                            <th>Given By</th>
                            <th>Accepted By</th>
                            <th>Status</th>
                            <th>Timer</th>
                            <th style="width:90px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                        <tr>
                            <td>
                                <div class="gt-task-row-title">{{ $task->title }}</div>
                                <div class="gt-task-row-meta">{{ \Illuminate\Support\Str::limit($task->description, 70) }}</div>
                            </td>
                            <td>{{ optional($task->group)->name ?? '—' }}</td>
                            <td>
                                <span class="gt-badge gt-priority-{{ $task->priority }}">
                                    {{ ucfirst($task->priority) }}
                                </span>
                            </td>
                            <td>
                                @foreach($task->assignees->take(3) as $a)
                                    <span class="member-chip">
                                        <span class="mc-avatar">{{ strtoupper(substr($a->name,0,2)) }}</span>
                                        {{ $a->name }}
                                    </span>
                                @endforeach
                                @if($task->assignees->count() > 3)
                                    <span class="gt-badge" style="background:#f1f5fb;color:#64748b;">+{{ $task->assignees->count()-3 }}</span>
                                @endif
                            </td>
                            <td>{{ optional($task->createdBy)->name ?? '—' }}</td>
                            <td>
                                @if($task->acceptedBy)
                                    <span class="member-chip">
                                        <span class="mc-avatar">{{ strtoupper(substr($task->acceptedBy->name,0,2)) }}</span>
                                        {{ $task->acceptedBy->name }}
                                    </span>
                                @else
                                    <span style="color:#94a3b8;font-size:.8rem;">Waiting…</span>
                                @endif
                            </td>
                            <td>
                                @if($task->status === 'completed')
                                    <span class="gt-badge gt-badge-completed">🏁 Completed</span>
                                @elseif($task->status === 'accepted')
                                    <span class="gt-badge gt-badge-accepted">✅ Accepted</span>
                                @else
                                    <span class="gt-badge gt-badge-pending">⏳ Pending</span>
                                @endif
                            </td>
                            <td>
                                @if($task->status === 'accepted' && $task->deadline_at)
                                    <span class="countdown-chip ok" data-deadline="{{ $task->deadline_at->toIso8601String() }}">
                                        <span class="cd-dot"></span>
                                        <span>—</span>
                                    </span>
                                @elseif($task->delay_minutes !== null)
                                    @if($task->delay_minutes > 0)
                                        <span class="gt-badge gt-badge-delayed">{{ round($task->delay_minutes/60,1) }}h late</span>
                                    @else
                                        <span class="gt-badge gt-badge-completed">{{ abs(round($task->delay_minutes/60,1)) }}h early</span>
                                    @endif
                                @else
                                    <span style="color:#94a3b8;">—</span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex;gap:4px;">
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.group-tasks.show', $task) }}" title="View">
                                        <i class="fas fa-folder-open"></i>
                                    </a>
                                    @if($task->status !== 'completed')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.group-tasks.edit', $task) }}" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5" style="color:#94a3b8;">
                                <div style="font-size:2.5rem;margin-bottom:8px;">📭</div>
                                No tasks found.
                                <a href="{{ route('admin.group-tasks.create') }}" class="btn btn-sm btn-primary ml-2">Create one</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<audio id="taskRingAudio" src="{{ asset('song/bd.mp3') }}" loop preload="auto"></audio>
@endsection

@section('scripts')
    @parent
    @include('admin.groupTasks.datatable-script')
    @include('admin.groupTasks.live-script')
@endsection
