@extends('layouts.admin')

@section('styles')
    @include('admin.groupTasks.ui-style')
@endsection

@section('content')
<div class="gt-page">

    <div class="gt-hero">
        <div class="gt-hero-text">
            <h3 class="gt-title">📊 Task Report</h3>
            <div class="gt-subtitle">Member-wise performance: tasks given, accepted, solved — with timing metrics.</div>
        </div>
        <div class="gt-actions">
            <a class="btn btn-light gt-btn" href="{{ route('admin.group-tasks.index') }}">
                <i class="fas fa-arrow-left"></i> Task List
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="gt-panel">
        <div class="gt-panel-header">
            <span><i class="fas fa-filter"></i> Filter Report</span>
        </div>
        <div class="gt-panel-body">
            <form method="GET" action="{{ route('admin.group-tasks.report') }}" class="row">
                @if(Auth::user()->is_admin)
                <div class="form-group col-lg-4">
                    <label style="font-weight:600;font-size:.82rem;">Group</label>
                    <select class="form-control" name="group_id" onchange="this.form.submit()">
                        <option value="">— Select Group —</option>
                        @foreach($groups as $g)
                        <option value="{{ $g->id }}" {{ request('group_id') == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-lg-4">
                    <label style="font-weight:600;font-size:.82rem;">Member</label>
                    <select class="form-control" name="member_id">
                        <option value="">— Select Member —</option>
                        @foreach($members as $m)
                        <option value="{{ $m->id }}" {{ request('member_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-lg-4 d-flex align-items-end">
                    <button class="btn btn-primary btn-block gt-btn" type="submit">
                        <i class="fas fa-search"></i> Generate Report
                    </button>
                </div>
                @else
                <div class="col-12">
                    <p style="color:#64748b;margin:0;">Showing your personal task report.</p>
                </div>
                @endif
            </form>
        </div>
    </div>

    @if($report && $selectedMember)

    {{-- Summary cards --}}
    <div class="gt-summary-grid">
        <div class="gt-summary-card" style="--accent:#3b82f6">
            <div class="card-icon">📤</div>
            <small>Tasks Given</small>
            <strong>{{ $report['created'] }}</strong>
        </div>
        <div class="gt-summary-card" style="--accent:#8b5cf6">
            <div class="card-icon">📥</div>
            <small>Assigned To Me</small>
            <strong>{{ $report['assigned'] }}</strong>
        </div>
        <div class="gt-summary-card" style="--accent:#06b6d4">
            <div class="card-icon">✅</div>
            <small>Accepted</small>
            <strong>{{ $report['accepted'] }}</strong>
        </div>
        <div class="gt-summary-card" style="--accent:#10b981">
            <div class="card-icon">🏁</div>
            <small>Completed</small>
            <strong>{{ $report['completed'] }}</strong>
        </div>
        <div class="gt-summary-card" style="--accent:#f59e0b">
            <div class="card-icon">🤝</div>
            <small>Solved for Others</small>
            <strong>{{ $report['others_solved'] }}</strong>
        </div>
    </div>

    {{-- Task history table --}}
    <div class="gt-panel">
        <div class="gt-panel-header">
            <span>
                <i class="fas fa-history"></i>
                {{ $selectedMember->name }}'s Task History
            </span>
            <span class="gt-badge" style="background:#f1f5fb;color:#475569;">{{ count($report['tasks']) }} records</span>
        </div>
        <div class="gt-panel-body" style="padding:0;">
            <div class="table-responsive">
                <table class="table table-hover datatable gt-table">
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Group</th>
                            <th>Given By</th>
                            <th>Assigned To</th>
                            <th>Accepted By</th>
                            <th>Completed By</th>
                            <th>Status</th>
                            <th>Timing</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report['tasks'] as $task)
                        <tr>
                            <td>
                                <a href="{{ route('admin.group-tasks.show', $task) }}" style="font-weight:700;color:#1e3a8a;">
                                    {{ $task->title }}
                                </a>
                            </td>
                            <td>{{ optional($task->group)->name ?? '—' }}</td>
                            <td>
                                <span class="member-chip">
                                    <span class="mc-avatar">{{ strtoupper(substr(optional($task->createdBy)->name ?? 'N', 0, 2)) }}</span>
                                    {{ optional($task->createdBy)->name ?? '—' }}
                                </span>
                            </td>
                            <td>
                                @foreach($task->assignees->take(2) as $a)
                                <span class="member-chip">
                                    <span class="mc-avatar">{{ strtoupper(substr($a->name,0,2)) }}</span>
                                    {{ $a->name }}
                                </span>
                                @endforeach
                                @if($task->assignees->count() > 2)
                                <span class="gt-badge" style="background:#f1f5fb;color:#64748b;">+{{ $task->assignees->count()-2 }}</span>
                                @endif
                            </td>
                            <td>{{ optional($task->acceptedBy)->name ?? '—' }}</td>
                            <td>{{ optional($task->completedBy)->name ?? '—' }}</td>
                            <td>
                                <span class="gt-badge gt-badge-{{ $task->status }}">{{ ucfirst($task->status) }}</span>
                            </td>
                            <td>
                                @if($task->delay_minutes !== null)
                                    @if($task->delay_minutes > 0)
                                        <span class="gt-badge gt-badge-delayed">{{ round($task->delay_minutes/60,1) }}h late</span>
                                    @else
                                        <span class="gt-badge gt-badge-completed">{{ abs(round($task->delay_minutes/60,1)) }}h early</span>
                                    @endif
                                @else
                                    <span style="color:#94a3b8;">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4" style="color:#94a3b8;">No task history found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @elseif(request('group_id') || !Auth::user()->is_admin)
    <div class="gt-panel">
        <div class="gt-panel-body text-center py-5" style="color:#94a3b8;">
            <div style="font-size:2.5rem;margin-bottom:8px;">📊</div>
            <p>{{ Auth::user()->is_admin ? 'Select a member to generate their report.' : 'No report data available.' }}</p>
        </div>
    </div>
    @endif

</div>
@endsection

@section('scripts')
    @parent
    @include('admin.groupTasks.datatable-script')
@endsection
