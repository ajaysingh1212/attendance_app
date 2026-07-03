{{--
    home-dashboard.blade.php
    Included on the attendance home page.
    Shows: task summary metrics, ring alert for pending assigned tasks,
    my active tasks with countdown, recent pending tasks list.
    Realtime via polling (live-script handles it).
--}}

<div class="home-task-dashboard" id="homeTaskDashboard">

    {{-- Ring alert (JS shows/hides) --}}
    <div class="home-task-ring" id="homeTaskRing">
        <div style="display:flex;align-items:center;gap:8px;flex:1;min-width:0;">
            <span style="font-size:1.3rem;">🔔</span>
            <div>
                <div style="font-weight:800;font-size:.85rem;">New Task Assigned!</div>
                <div style="font-size:.78rem;opacity:.85;" id="homeRingText">Loading…</div>
            </div>
        </div>
        <a href="#" id="homeRingLink" class="home-task-open">Open →</a>
        <button onclick="document.getElementById('homeTaskRing').classList.remove('visible');document.getElementById('homeRingAudio').pause();"
                style="background:none;border:none;color:inherit;font-size:1rem;cursor:pointer;padding:0 4px;">✕</button>
    </div>

    {{-- Header row --}}
    <div class="home-task-head">
        <div>
            <h4>📋 Task Dashboard</h4>
            <p>Realtime group task overview &amp; your active assignments</p>
        </div>
        <div class="home-task-actions">
            @if(Auth::user()->is_admin)
            <a href="{{ route('admin.task-groups.index') }}">👥 Groups</a>
            @endif
            <a href="{{ route('admin.group-tasks.index') }}">All Tasks</a>
            <a href="{{ route('admin.group-tasks.create') }}" class="primary">+ New Task</a>
        </div>
    </div>

    {{-- Metrics row --}}
    <div class="home-task-metrics">
        <div class="home-task-metric" style="--accent:#3b82f6">
            <span>Total</span>
            <strong data-summary="total">{{ $taskSummary['total'] ?? 0 }}</strong>
        </div>
        <div class="home-task-metric" style="--accent:#f59e0b">
            <span>Pending</span>
            <strong data-summary="pending">{{ $taskSummary['pending'] ?? 0 }}</strong>
        </div>
        <div class="home-task-metric" style="--accent:#06b6d4">
            <span>Accepted</span>
            <strong data-summary="accepted">{{ $taskSummary['accepted'] ?? 0 }}</strong>
        </div>
        <div class="home-task-metric" style="--accent:#10b981">
            <span>Completed</span>
            <strong data-summary="completed">{{ $taskSummary['completed'] ?? 0 }}</strong>
        </div>
        <div class="home-task-metric" style="--accent:#ef4444">
            <span>Delayed</span>
            <strong data-summary="delayed">{{ $taskSummary['delayed'] ?? 0 }}</strong>
        </div>
    </div>

    {{-- My active tasks countdown table --}}
    <div id="activeTasksWrap" style="display:none;" class="home-task-table-wrap mb-3">
        <div class="home-task-table-title">⏱ My Active Tasks</div>
        <table class="table home-task-table">
            <thead>
                <tr>
                    <th>Task</th>
                    <th>Deadline</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="activeTasksTbody"></tbody>
        </table>
    </div>

    {{-- Recent pending tasks --}}
    <div id="pendingTasksWrap" style="display:none;" class="home-task-table-wrap">
        <div class="home-task-table-title">🔔 Pending Tasks Assigned To You</div>
        <table class="table home-task-table">
            <thead>
                <tr>
                    <th>Task</th>
                    <th>Group</th>
                    <th>Priority</th>
                    <th>Given By</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="pendingTasksTbody"></tbody>
        </table>
    </div>

    @if(isset($taskDashboardTasks) && $taskDashboardTasks->count())
    <div class="home-task-table-wrap mt-3">
        <div class="home-task-table-title">Recent Group Tasks</div>
        <table class="table home-task-table">
            <thead>
                <tr>
                    <th>Task</th>
                    <th>Group</th>
                    <th>Assigned</th>
                    <th>Accepted</th>
                    <th>Completed</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($taskDashboardTasks->take(8) as $task)
                <tr>
                    <td><strong>{{ $task->title }}</strong></td>
                    <td>{{ optional($task->group)->name ?? '-' }}</td>
                    <td>
                        {{ $task->assignees->pluck('name')->take(2)->implode(', ') }}
                        {{ $task->assignees->count() > 2 ? '+' . ($task->assignees->count() - 2) : '' }}
                    </td>
                    <td>{{ optional($task->acceptedBy)->name ?? 'Pending' }}</td>
                    <td>{{ optional($task->completedBy)->name ?? '-' }}</td>
                    <td><span class="gt-badge gt-badge-{{ $task->status }}">{{ ucfirst($task->status) }}</span></td>
                    <td><a href="{{ route('admin.group-tasks.show', $task) }}" class="btn btn-xs btn-primary">Open</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Groups panel --}}
    <div id="adminGroupsWrap" style="{{ isset($taskDashboardGroups) && $taskDashboardGroups->count() ? '' : 'display:none;' }}" class="mt-3">
        <div class="home-task-table-title" style="margin-bottom:10px;">👥 Active Groups</div>
        <div id="adminGroupsGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;">
            @if(isset($taskDashboardGroups))
                @foreach($taskDashboardGroups as $group)
                <div class="home-task-metric" style="--accent:#3b82f6;text-align:left;">
                    <span style="font-weight:700;color:#0f172a;">{{ $group->name }}</span>
                    <div style="display:flex;gap:8px;margin-top:6px;flex-wrap:wrap;">
                        <span style="font-size:.7rem;color:#64748b;">{{ $group->members_count }} members</span>
                        <span style="font-size:.7rem;color:#64748b;">{{ $group->tasks_count }} tasks</span>
                    </div>
                    <div style="font-size:.72rem;color:#64748b;margin-top:6px;">
                        {{ $group->members->pluck('name')->take(4)->implode(', ') }}
                    </div>
                </div>
                @endforeach
            @endif
        </div>
    </div>

</div>

{{-- Ring sound --}}
<audio id="homeRingAudio" src="{{ asset('song/bd.mp3') }}" loop preload="auto"></audio>
