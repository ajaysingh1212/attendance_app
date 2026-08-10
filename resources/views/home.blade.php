@extends('layouts.admin')

@section('content')

{{-- ════════════════════════════════════════════════════════
     CELEBRATION MODAL
════════════════════════════════════════════════════════ --}}
@if(
    (isset($birthdayEmployees)    && $birthdayEmployees->count())   ||
    (isset($anniversaryEmployees) && $anniversaryEmployees->count())
)
<div class="modal fade" id="celebrationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content celebration-box text-center">
            <div class="modal-body p-4">
                <button id="enableSoundBtn" class="btn btn-sm btn-success d-none">🔊 Enable Celebration Sound</button>
                <div class="party-icons">🎉 🎈 🎊 🎆 💐</div>
                @if($birthdayEmployees->count())
                    <h4 class="mt-3">🎂 Happy Birthday 🎂</h4>
                    <ul class="list-unstyled">
                        @foreach($birthdayEmployees as $emp)
                            <li class="celebration-item">🎉 <strong>{{ $emp->full_name }}</strong>
                                <small class="text-muted">({{ $emp->department ?? 'Team Member' }})</small></li>
                        @endforeach
                    </ul>
                @endif
                @if($anniversaryEmployees->count())
                    <h4 class="mt-4">💍 Happy Work Anniversary 💍</h4>
                    <ul class="list-unstyled">
                        @foreach($anniversaryEmployees as $emp)
                            <li class="celebration-item">🎊 <strong>{{ $emp->full_name }}</strong>
                                <small class="text-muted">({{ $emp->department ?? 'Team Member' }})</small></li>
                        @endforeach
                    </ul>
                @endif
                <button class="btn btn-primary mt-3" data-bs-dismiss="modal">🎉 Celebrate Together 🎉</button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Firecracker Sound --}}
<audio id="fireSound" src="{{ asset('song/bd.mp3') }}" preload="auto"></audio>

@if(isset($message))
<div class="alert-banner">
    <i class="fas fa-exclamation-triangle"></i> {{ $message }}
</div>
@else

{{-- ════════════════════════════════════════════════════════
     INACTIVE EMPLOYEES RIGHT DRAWER
════════════════════════════════════════════════════════ --}}
<div id="inactiveOverlay" class="drawer-overlay" onclick="closeInactiveDrawer()"></div>
<div id="inactiveDrawer" class="inactive-drawer">
    <div class="drawer-header">
        <div>
            <h5 class="drawer-title">👤 Inactive Employees</h5>
            <p class="drawer-subtitle">Resigned · Terminated · Suspended</p>
        </div>
        <button class="drawer-close" onclick="closeInactiveDrawer()">✕</button>
    </div>
    <div class="drawer-search">
        <input type="text" id="drawerSearch" placeholder="🔍 Search employee..." oninput="filterDrawer(this.value)">
    </div>
    <div id="drawerContent" class="drawer-body">
        <div class="drawer-loading">
            <div class="spinner"></div>
            <span>Loading...</span>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════
     MAIN DASHBOARD
════════════════════════════════════════════════════════ --}}
<div class="hrdash">

    @include('admin.groupTasks.home-dashboard')

    {{-- ── Top action bar ── --}}
    <div class="hrdash-topbar">
        <div class="topbar-left">
            <h4 class="dash-title">
                <span class="dash-dot"></span>
                Attendance Dashboard
            </h4>
            <span class="dash-date">{{ \Carbon\Carbon::today()->format('l, d M Y') }}</span>
        </div>
        <div class="topbar-right">
            @can('audit_log_access')
            <button type="button" class="btn-audit-trigger" onclick="openAuditLogModal()">
                <i class="fas fa-history"></i>
                Activity Logs
            </button>
            @endcan
            {{-- Inactive employees trigger --}}
            @php $inactiveCount = $inactiveEmployees->count() ?? 0; @endphp
            @if($inactiveCount > 0)
            <button class="btn-inactive-trigger" onclick="openInactiveDrawer()">
                <span class="inactive-badge">{{ $inactiveCount }}</span>
                👥 Inactive Staff
            </button>
            @endif
        </div>
    </div>

    {{-- ── Filter Form ── --}}
    <form method="GET" class="filter-form">
        <div class="filter-group">
            <label>Period</label>
            <select name="filter" class="filter-select" onchange="toggleCustom(this.value)">
                @foreach(['today'=>'Today','yesterday'=>'Yesterday','week'=>'This Week','halfmonth'=>'Half Month','month'=>'This Month','custom'=>'Custom'] as $f => $label)
                    <option value="{{ $f }}" {{ $filter==$f?'selected':'' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-group custom-range" id="customRange" style="display:{{ $filter=='custom'?'flex':'none' }}">
            <div>
                <label>From</label>
                <input type="date" name="from" value="{{ $customFrom }}" class="filter-input">
            </div>
            <div>
                <label>To</label>
                <input type="date" name="to" value="{{ $customTo }}" class="filter-input">
            </div>
        </div>
        <div class="filter-group">
            <label>Status</label>
            <select name="status" class="filter-select">
                <option value="">All Status</option>
                @foreach(\App\Models\AttendanceDetail::STATUS_SELECT as $key => $value)
                    <option value="{{ $key }}" {{ ($statusFilter ?? '')==$key ? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-group">
            <label>Office</label>
            <select name="office_branch_id" class="filter-select">
                <option value="">All Offices</option>
                @foreach($officeBranches ?? [] as $office)
                    <option value="{{ $office->id }}" {{ (string)($officeFilter ?? '') === (string)$office->id ? 'selected' : '' }}>
                        {{ $office->branch_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-apply">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
            Apply Filter
        </button>
    </form>

    {{-- ── Stats Row 1 ── --}}
    @php
        $cards1 = [
            ['label'=>'Total Staff',   'value'=>$totalEmployees, 'icon'=>'👥', 'cls'=>'card-blue'],
            ['label'=>'Punched In',    'value'=>$totalPunchIn,   'icon'=>'⏺',  'cls'=>'card-green'],
            ['label'=>'Punched Out',   'value'=>$totalPunchOut,  'icon'=>'⏏',  'cls'=>'card-red'],
        ];
        $cards2 = [
            ['label'=>'Present',  'value'=>$totalPresent, 'icon'=>'✅', 'cls'=>'card-emerald', 'status'=>'present'],
            ['label'=>'Absent',   'value'=>$totalAbsent,  'icon'=>'❌', 'cls'=>'card-rose',    'status'=>'absent'],
            ['label'=>'Half Day', 'value'=>$totalHalf,    'icon'=>'🔸', 'cls'=>'card-amber',   'status'=>'half_time'],
            ['label'=>'On Leave', 'value'=>$totalLeave,   'icon'=>'📋', 'cls'=>'card-sky',     'status'=>'leave'],
        ];
        $total2 = $totalPresent + $totalAbsent + $totalHalf + $totalLeave;
    @endphp

    <div class="stats-row">
        @foreach($cards1 as $c)
        <div class="stat-card {{ $c['cls'] }}">
            <div class="stat-icon">{{ $c['icon'] }}</div>
            <div class="stat-info">
                <span class="stat-label">{{ $c['label'] }}</span>
                <span class="stat-value">{{ $c['value'] }}</span>
            </div>
        </div>
        @endforeach
    </div>

    <div class="stats-row mt-3">
        @foreach($cards2 as $c)
        <div class="stat-card {{ $c['cls'] }} clickable" onclick="filterCards('{{ $c['status'] }}')">
            <div class="stat-icon">{{ $c['icon'] }}</div>
            <div class="stat-info">
                <span class="stat-label">{{ $c['label'] }}</span>
                <span class="stat-value">{{ $c['value'] }}</span>
            </div>
            <div class="stat-bar">
                @if($total2 > 0)
                    <div class="stat-bar-fill" style="width:{{ round($c['value']/$total2*100) }}%"></div>
                @endif
            </div>
        </div>
        @endforeach
        <div class="stat-card card-gray">
            <div class="stat-icon">🔢</div>
            <div class="stat-info">
                <span class="stat-label">Total</span>
                <span class="stat-value">{{ $total2 }}</span>
            </div>
        </div>
    </div>

    {{-- ── Attendance Cards ── --}}
    <div class="section-header mt-4">
        <h5>📋 Employee Attendance</h5>
        <button class="btn-reset-filter" onclick="filterCards('')">Show All</button>
    </div>

    @if($attendanceDetails->count() > 0)
    <div id="attendanceGrid" class="attendance-grid">
        @foreach($attendanceDetails as $att)
        @php
            $statusCls = match($att->status) {
                'present'   => 'att-present',
                'absent'    => 'att-absent',
                'half_time' => 'att-half',
                'leave'     => 'att-leave',
                default     => 'att-default',
            };
            $statusLabel = match($att->status) {
                'present'   => '✅ Present',
                'absent'    => '❌ Absent',
                'half_time' => '🔸 Half Day',
                'leave'     => '📋 On Leave',
                default     => ucfirst(str_replace('_',' ',$att->status)),
            };
        @endphp
        <div class="att-card {{ $statusCls }}" data-status="{{ $att->status }}">
            <div class="att-card-header">
                <div class="att-avatar">{{ strtoupper(substr($att->user->full_name ?? 'E', 0, 2)) }}</div>
                <div class="att-name-block">
                    <span class="att-name">{{ $att->user->full_name ?? $att->user->name ?? 'N/A' }}</span>
                    <span class="att-dept">
                        {{ $att->user->officeBranch->branch_name ?? 'No Office' }}
                        @if($att->user->department || $att->user->position)
                            · {{ $att->user->department ?? $att->user->position }}
                        @endif
                    </span>
                </div>
                <span class="att-badge">{{ $statusLabel }}</span>
            </div>
            @if($att->status !== 'absent')
            <div class="att-times">
                <div class="att-time-block">
                    <span class="time-label">IN</span>
                    <span class="time-val">{{ $att->punch_in_time ? \Carbon\Carbon::parse($att->punch_in_time)->format('h:i A') : '—' }}</span>
                    @if(!empty($att->punch_in_location))
                        <span class="time-loc">📍 {{ Str::limit($att->punch_in_location, 30) }}</span>
                    @endif
                </div>
                <div class="att-divider">⟶</div>
                <div class="att-time-block">
                    <span class="time-label">OUT</span>
                    <span class="time-val">{{ $att->punch_out_time ? \Carbon\Carbon::parse($att->punch_out_time)->format('h:i A') : '—' }}</span>
                    @if(!empty($att->punch_out_location))
                        <span class="time-loc">📍 {{ Str::limit($att->punch_out_location, 30) }}</span>
                    @endif
                </div>
            </div>
            @elseif($att->status === 'leave' && isset($att->leave_detail))
            <div class="att-leave-note">
                Leave: {{ $att->leave_detail->date_from }} — {{ $att->leave_detail->date_to }}
            </div>
            @endif
        </div>
        @endforeach
    </div>
    <div id="noAttMsg" class="no-records" style="display:none">
        <div class="no-records-icon">📭</div>
        <p>No records match the selected filter.</p>
    </div>
    @else
    <div class="no-records">
        <div class="no-records-icon">📭</div>
        <p>No attendance records found for the selected period.</p>
    </div>
    @endif

    {{-- ── Absent List ── --}}
    @if(isset($absentEmployees) && $absentEmployees->count())
    <div class="absent-panel mt-4">
        <div class="absent-panel-header">
            <span>❌ Absent Today ({{ $absentEmployees->count() }})</span>
        </div>
        <div class="absent-list">
            @foreach($absentEmployees as $name)
            <span class="absent-chip">{{ $name }}</span>
            @endforeach
        </div>
    </div>
    @endif

</div>{{-- end .hrdash --}}
@endif

@can('audit_log_access')
<div class="modal fade" id="auditLogModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content audit-modal">
            <div class="audit-modal-head">
                <div>
                    <h5>Activity Audit Logs</h5>
                    <p>User, role, menu, action and exact changed record in one place.</p>
                </div>
                <button type="button" class="audit-close" data-bs-dismiss="modal">x</button>
            </div>
            <div class="audit-filter-bar">
                <input type="date" id="auditFrom" class="audit-input" value="{{ now()->toDateString() }}">
                <input type="date" id="auditTo" class="audit-input" value="{{ now()->toDateString() }}">
                <select id="auditRole" class="audit-input">
                    <option value="">All Roles</option>
                    @foreach($auditLogRoles ?? [] as $role)
                        <option value="{{ $role }}">{{ $role }}</option>
                    @endforeach
                </select>
                <select id="auditModule" class="audit-input">
                    <option value="">All Menus</option>
                    @foreach($auditLogModules ?? [] as $module)
                        <option value="{{ $module }}">{{ $module }}</option>
                    @endforeach
                </select>
                <select id="auditAction" class="audit-input">
                    <option value="">All Actions</option>
                    <option value="created">Created</option>
                    <option value="updated">Updated</option>
                    <option value="deleted">Deleted</option>
                </select>
                <input type="search" id="auditSearch" class="audit-input audit-search" placeholder="Search user, menu, record...">
                <button type="button" class="audit-apply" onclick="loadAuditLogs()">Apply</button>
                <button type="button" class="audit-reset" onclick="resetAuditFilters()">Reset</button>
            </div>
            <div id="auditLogList" class="audit-log-list">
                <div class="audit-empty">Open filters and click Apply to load logs.</div>
            </div>
        </div>
    </div>
</div>
@endcan

{{-- ════════════════════════════════════════════════════════
     REACTIVATE CONFIRMATION MODAL
════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="reactivateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header" style="background:#1e3a5f;color:#fff;">
                <h5 class="modal-title">♻️ Reactivate Employee</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Reactivate <strong id="reactName"></strong>? They will appear on the dashboard and payroll will be generated.</p>
                <input type="hidden" id="reactEmployeeId">
                <textarea id="reactRemarks" class="form-control mt-2" rows="2" placeholder="Reason for reactivation (optional)"></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-success" onclick="confirmReactivate()">♻️ Reactivate</button>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════
     STYLES
════════════════════════════════════════════════════════ --}}
<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;600;700&display=swap');

* { box-sizing: border-box; }

.hrdash {
    font-family: 'DM Sans', sans-serif;
    padding: 24px;
    background: #f0f4f9;
    min-height: 100vh;
}

.home-task-dashboard {
    background: #fff;
    border: 1px solid #dfe5ef;
    border-radius: 12px;
    padding: 18px;
    margin-bottom: 22px;
    box-shadow: 0 8px 24px rgba(31,45,61,.06);
}
.home-task-ring {
    display: none;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    background: #fff1f2;
    border: 1px solid #fecdd3;
    color: #9f1239;
    border-radius: 8px;
    padding: 10px 12px;
    margin-bottom: 14px;
}
.home-task-ring.visible { display: flex; }
.home-task-open {
    background: #fff;
    color: #9f1239;
    border-radius: 6px;
    padding: 6px 10px;
    font-weight: 700;
}
.home-task-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 14px;
}
.home-task-head h4 {
    margin: 0;
    color: #172b4d;
    font-size: 1.1rem;
    font-weight: 800;
}
.home-task-head p {
    margin: 3px 0 0;
    color: #6b778c;
    font-size: .82rem;
}
.home-task-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.home-task-actions a {
    border: 1px solid #d8dee9;
    color: #253858;
    background: #fff;
    border-radius: 6px;
    padding: 8px 12px;
    font-weight: 700;
    text-decoration: none;
}
.home-task-actions a.primary {
    background: #2563eb;
    color: #fff;
    border-color: #2563eb;
}
.home-task-metrics {
    display: grid;
    grid-template-columns: repeat(5, minmax(120px, 1fr));
    gap: 12px;
    margin-bottom: 16px;
}
.home-task-metric {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 14px;
    border-left: 4px solid var(--accent);
}
.home-task-metric span {
    color: #64748b;
    font-size: .76rem;
    font-weight: 700;
    text-transform: uppercase;
}
.home-task-metric strong {
    display: block;
    font-size: 1.6rem;
    color: #172b4d;
    line-height: 1;
    margin-top: 8px;
}
.home-task-table-wrap {
    border: 1px solid #edf1f7;
    border-radius: 8px;
    overflow: hidden;
}
.home-task-table-title {
    padding: 11px 14px;
    background: #f7f9fc;
    color: #253858;
    font-weight: 800;
    border-bottom: 1px solid #edf1f7;
}
.home-task-table {
    margin-bottom: 0;
}
.home-task-table thead th {
    background: #fff;
    color: #42526e;
    font-size: .74rem;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.home-task-table td strong,
.home-task-table td small {
    display: block;
}
.home-task-table td small {
    color: #6b778c;
}

/* ── Top Bar ── */
.hrdash-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.dash-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.3rem;
    font-weight: 700;
    color: #1e3a5f;
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
}
.dash-dot {
    width: 10px; height: 10px;
    background: #3b82f6;
    border-radius: 50%;
    display: inline-block;
    box-shadow: 0 0 0 4px rgba(59,130,246,.2);
}
.dash-date { font-size: .82rem; color: #64748b; margin-left: 20px; }

.btn-inactive-trigger {
    position: relative;
    background: linear-gradient(135deg, #1e3a5f, #2563eb);
    color: #fff;
    border: none;
    border-radius: 50px;
    padding: 10px 22px;
    font-size: .85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all .25s;
    letter-spacing: .3px;
}
.btn-inactive-trigger:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(37,99,235,.3); }
.inactive-badge {
    background: #ef4444;
    color: #fff;
    border-radius: 50%;
    width: 20px; height: 20px;
    font-size: .72rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 6px;
}

/* ── Filter ── */
.filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    align-items: flex-end;
    background: #fff;
    padding: 18px 22px;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    margin-bottom: 24px;
}
.filter-group { display: flex; flex-direction: column; gap: 4px; }
.filter-group label { font-size: .75rem; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: .5px; }
.filter-select, .filter-input {
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 8px 14px;
    font-size: .88rem;
    color: #1e293b;
    outline: none;
    transition: border-color .2s;
    background: #f8fafc;
    min-width: 140px;
}
.filter-select:focus, .filter-input:focus { border-color: #3b82f6; background: #fff; }
.custom-range { display: flex; gap: 10px; }
.btn-apply {
    background: #1e3a5f;
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 10px 22px;
    font-size: .88rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 7px;
    transition: all .2s;
}
.btn-apply:hover { background: #2563eb; }

/* ── Stats ── */
.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px,1fr));
    gap: 14px;
}
.stat-card {
    background: #fff;
    border-radius: 16px;
    padding: 18px 16px 14px;
    display: flex;
    gap: 14px;
    align-items: flex-start;
    box-shadow: 0 2px 10px rgba(0,0,0,.05);
    border-top: 3px solid transparent;
    transition: transform .2s, box-shadow .2s;
    position: relative;
    overflow: hidden;
}
.stat-card.clickable { cursor: pointer; }
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 22px rgba(0,0,0,.1); }
.stat-icon { font-size: 1.6rem; }
.stat-info { display: flex; flex-direction: column; gap: 2px; flex: 1; }
.stat-label { font-size: .75rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }
.stat-value { font-family: 'Space Grotesk', sans-serif; font-size: 1.9rem; font-weight: 700; line-height: 1; }
.stat-bar { position: absolute; bottom: 0; left: 0; right: 0; height: 4px; background: #f1f5f9; }
.stat-bar-fill { height: 100%; border-radius: 0 2px 2px 0; transition: width .6s ease; }
.card-blue    { border-top-color: #3b82f6; } .card-blue    .stat-value { color: #1d4ed8; } .card-blue    .stat-bar-fill { background:#3b82f6; }
.card-green   { border-top-color: #10b981; } .card-green   .stat-value { color: #059669; } .card-green   .stat-bar-fill { background:#10b981; }
.card-red     { border-top-color: #ef4444; } .card-red     .stat-value { color: #dc2626; } .card-red     .stat-bar-fill { background:#ef4444; }
.card-emerald { border-top-color: #34d399; } .card-emerald .stat-value { color: #065f46; } .card-emerald .stat-bar-fill { background:#34d399; }
.card-rose    { border-top-color: #fb7185; } .card-rose    .stat-value { color: #be123c; } .card-rose    .stat-bar-fill { background:#fb7185; }
.card-amber   { border-top-color: #fbbf24; } .card-amber   .stat-value { color: #92400e; } .card-amber   .stat-bar-fill { background:#fbbf24; }
.card-sky     { border-top-color: #38bdf8; } .card-sky     .stat-value { color: #0369a1; } .card-sky     .stat-bar-fill { background:#38bdf8; }
.card-gray    { border-top-color: #94a3b8; } .card-gray    .stat-value { color: #1e293b; } .card-gray    .stat-bar-fill { background:#94a3b8; }

/* ── Section Header ── */
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
}
.section-header h5 {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1rem;
    font-weight: 700;
    color: #1e3a5f;
    margin: 0;
}
.btn-reset-filter {
    background: #f1f5f9;
    border: none;
    border-radius: 8px;
    padding: 6px 14px;
    font-size: .8rem;
    color: #475569;
    cursor: pointer;
}

.btn-audit-trigger {
    border: 1px solid #bfd0e8;
    background: #0f766e;
    color: #fff;
    border-radius: 8px;
    padding: 9px 14px;
    font-size: .84rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 8px 18px rgba(15,118,110,.18);
}
.audit-modal {
    border: 0;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 24px 70px rgba(15,23,42,.22);
}
.audit-modal-head {
    background: #102a43;
    color: #fff;
    padding: 20px 22px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
}
.audit-modal-head h5 { margin: 0; font-weight: 800; letter-spacing: 0; }
.audit-modal-head p { margin: 4px 0 0; color: #bcccdc; font-size: .84rem; }
.audit-close {
    width: 34px;
    height: 34px;
    border: 0;
    border-radius: 8px;
    background: rgba(255,255,255,.12);
    color: #fff;
}
.audit-filter-bar {
    display: grid;
    grid-template-columns: repeat(5, minmax(120px, 1fr)) minmax(190px, 1.4fr) auto auto;
    gap: 10px;
    padding: 14px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}
.audit-input {
    height: 38px;
    border: 1px solid #d8e2ef;
    border-radius: 8px;
    padding: 0 10px;
    font-size: .82rem;
    background: #fff;
    color: #1e293b;
}
.audit-apply, .audit-reset {
    border: 0;
    border-radius: 8px;
    padding: 0 14px;
    font-weight: 800;
    height: 38px;
}
.audit-apply { background: #2563eb; color: #fff; }
.audit-reset { background: #e2e8f0; color: #334155; }
.audit-log-list {
    max-height: 62vh;
    overflow: auto;
    padding: 14px;
    background: #fff;
}
.audit-item {
    display: grid;
    grid-template-columns: 44px 1fr auto;
    gap: 12px;
    padding: 13px;
    border: 1px solid #e6edf5;
    border-radius: 8px;
    margin-bottom: 10px;
}
.audit-action-mark {
    width: 44px;
    height: 44px;
    border-radius: 8px;
    display: grid;
    place-items: center;
    background: #e0f2fe;
    color: #075985;
}
.audit-title {
    font-weight: 800;
    color: #172b4d;
    margin-bottom: 5px;
}
.audit-meta {
    color: #64748b;
    font-size: .78rem;
    line-height: 1.7;
}
.audit-chip {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 6px;
    background: #eef2ff;
    color: #3730a3;
    font-weight: 700;
    margin-right: 5px;
}
.audit-time {
    white-space: nowrap;
    color: #475569;
    font-size: .78rem;
    font-weight: 700;
}
.audit-empty {
    text-align: center;
    padding: 44px 16px;
    color: #64748b;
}

/* ── Attendance Grid ── */
.attendance-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 14px;
}
.att-card {
    background: #fff;
    border-radius: 14px;
    padding: 16px;
    box-shadow: 0 2px 10px rgba(0,0,0,.05);
    border-left: 4px solid #e2e8f0;
    transition: transform .2s;
}
.att-card:hover { transform: translateY(-2px); }
.att-present   { border-left-color: #10b981; }
.att-absent    { border-left-color: #ef4444; background: #fff9f9; }
.att-half      { border-left-color: #f59e0b; }
.att-leave     { border-left-color: #3b82f6; }
.att-card-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
}
.att-avatar {
    width: 38px; height: 38px;
    border-radius: 10px;
    background: linear-gradient(135deg, #1e3a5f, #2563eb);
    color: #fff;
    font-size: .8rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.att-name-block { flex: 1; min-width: 0; }
.att-name { display: block; font-weight: 600; font-size: .9rem; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.att-dept { font-size: .73rem; color: #94a3b8; }
.att-badge {
    font-size: .72rem;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 50px;
    white-space: nowrap;
    background: #f1f5f9;
    color: #475569;
}
.att-present  .att-badge { background: #d1fae5; color: #065f46; }
.att-absent   .att-badge { background: #fee2e2; color: #991b1b; }
.att-half     .att-badge { background: #fef3c7; color: #92400e; }
.att-leave    .att-badge { background: #dbeafe; color: #1e40af; }

.att-times {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    gap: 6px;
    align-items: center;
    background: #f8fafc;
    border-radius: 10px;
    padding: 10px;
}
.att-time-block { display: flex; flex-direction: column; gap: 2px; }
.time-label { font-size: .68rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; }
.time-val { font-family: 'Space Grotesk', sans-serif; font-size: .9rem; font-weight: 600; color: #1e293b; }
.time-loc { font-size: .68rem; color: #94a3b8; }
.att-divider { color: #cbd5e1; font-size: 1rem; text-align: center; }
.att-leave-note { font-size: .78rem; color: #1e40af; background: #dbeafe; padding: 6px 10px; border-radius: 8px; }

/* ── Absent panel ── */
.absent-panel {
    background: #fff;
    border-radius: 14px;
    padding: 16px 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,.05);
    border-left: 4px solid #ef4444;
}
.absent-panel-header {
    font-weight: 700;
    font-size: .9rem;
    color: #991b1b;
    margin-bottom: 12px;
}
.absent-list { display: flex; flex-wrap: wrap; gap: 8px; }
.absent-chip {
    background: #fee2e2;
    color: #991b1b;
    border-radius: 50px;
    padding: 5px 14px;
    font-size: .78rem;
    font-weight: 500;
}

/* ── No records ── */
.no-records {
    text-align: center;
    padding: 50px 20px;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(0,0,0,.05);
}
.no-records-icon { font-size: 3rem; margin-bottom: 10px; }
.no-records p { color: #64748b; font-size: .95rem; }

/* ── Alert Banner ── */
.alert-banner {
    background: #fef9c3;
    border: 1.5px solid #fde047;
    color: #713f12;
    padding: 14px 20px;
    border-radius: 12px;
    margin: 20px;
    font-weight: 500;
}

/* ══ INACTIVE DRAWER ══════════════════════════════════ */
.drawer-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.35);
    z-index: 1040;
    opacity: 0;
    pointer-events: none;
    transition: opacity .3s;
}
.drawer-overlay.open { opacity: 1; pointer-events: all; }

.inactive-drawer {
    position: fixed;
    top: 0; right: 0;
    width: min(420px, 95vw);
    height: 100vh;
    background: #fff;
    z-index: 1041;
    transform: translateX(100%);
    transition: transform .35s cubic-bezier(.4,0,.2,1);
    display: flex;
    flex-direction: column;
    box-shadow: -8px 0 40px rgba(0,0,0,.12);
}
.inactive-drawer.open { transform: translateX(0); }

.drawer-header {
    background: linear-gradient(135deg, #1e3a5f, #2563eb);
    color: #fff;
    padding: 22px 20px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-shrink: 0;
}
.drawer-title { font-family: 'Space Grotesk',sans-serif; font-size: 1.1rem; font-weight: 700; margin: 0; }
.drawer-subtitle { font-size: .75rem; opacity: .75; margin: 4px 0 0; }
.drawer-close {
    background: rgba(255,255,255,.15);
    border: none;
    color: #fff;
    width: 32px; height: 32px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1rem;
    transition: background .2s;
}
.drawer-close:hover { background: rgba(255,255,255,.3); }

.drawer-search { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; flex-shrink: 0; }
.drawer-search input {
    width: 100%;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 9px 14px;
    font-size: .87rem;
    outline: none;
    transition: border-color .2s;
}
.drawer-search input:focus { border-color: #3b82f6; }

.drawer-body { flex: 1; overflow-y: auto; padding: 14px 16px; }

.drawer-loading {
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; gap: 12px; height: 160px;
    color: #94a3b8; font-size: .87rem;
}
.spinner {
    width: 32px; height: 32px;
    border: 3px solid #e2e8f0;
    border-top-color: #3b82f6;
    border-radius: 50%;
    animation: spin .8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.inactive-card {
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 14px;
    padding: 14px;
    margin-bottom: 12px;
    transition: box-shadow .2s;
}
.inactive-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,.08); }
.inactive-card-top {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}
.inactive-avatar {
    width: 40px; height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: #fff;
    font-size: .85rem;
    font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.inactive-name { font-weight: 600; font-size: .9rem; color: #1e293b; }
.inactive-dept { font-size: .73rem; color: #94a3b8; }
.inactive-status {
    margin-left: auto;
    font-size: .72rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 50px;
}
.status-Resigned   { background: #fef3c7; color: #92400e; }
.status-Terminated { background: #fee2e2; color: #991b1b; }
.status-Suspended  { background: #f3f4f6; color: #374151; }

.inactive-meta {
    font-size: .75rem;
    color: #64748b;
    background: #fff;
    border-radius: 8px;
    padding: 8px 10px;
    margin-bottom: 10px;
    line-height: 1.7;
    border: 1px solid #f1f5f9;
}
.inactive-meta strong { color: #1e293b; }
.inactive-meta .meta-row { display: flex; gap: 6px; }

.btn-reactivate {
    width: 100%;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
    border: none;
    border-radius: 9px;
    padding: 9px;
    font-size: .83rem;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
}
.btn-reactivate:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16,185,129,.3); }

/* ── Celebration ── */
.celebration-box { background: linear-gradient(135deg,#fde68a,#fca5a5,#93c5fd); border-radius:16px; animation:popIn .6s ease; }
.party-icons { font-size:30px; animation:float 2s infinite alternate; }
.celebration-item { font-size:15px; margin:6px 0; }
@keyframes popIn { from{transform:scale(.7);opacity:0} to{transform:scale(1);opacity:1} }
@keyframes float { from{transform:translateY(0)} to{transform:translateY(-10px)} }

/* ── Responsive ── */
@media(max-width:640px) {
    .hrdash { padding: 14px; }
    .hrdash-topbar { flex-direction: column; align-items: flex-start; gap: 10px; }
    .home-task-head { flex-direction: column; align-items: flex-start; }
    .home-task-metrics { grid-template-columns: 1fr 1fr; }
    .stats-row { grid-template-columns: 1fr 1fr; }
    .attendance-grid { grid-template-columns: 1fr; }
    .audit-filter-bar { grid-template-columns: 1fr; }
    .audit-item { grid-template-columns: 38px 1fr; }
    .audit-time { grid-column: 2; }
}
</style>

{{-- ════════════════════════════════════════════════════════
     SCRIPTS
════════════════════════════════════════════════════════ --}}
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<script>
/* ── Filter Cards by Status ── */
function filterCards(status) {
    const cards = document.querySelectorAll('#attendanceGrid .att-card');
    const noMsg = document.getElementById('noAttMsg');
    let found = 0;

    cards.forEach(card => {
        const show = !status || card.dataset.status === status;
        card.style.display = show ? '' : 'none';
        if (show) found++;
    });

    if (noMsg) noMsg.style.display = found === 0 ? 'block' : 'none';
}

/* ── Custom Date Range Toggle ── */
function toggleCustom(val) {
    document.getElementById('customRange').style.display = val === 'custom' ? 'flex' : 'none';
}

@can('audit_log_access')
function openAuditLogModal() {
    const modal = new bootstrap.Modal(document.getElementById('auditLogModal'));
    modal.show();
    loadAuditLogs();
}

function loadAuditLogs() {
    const params = new URLSearchParams({
        from: document.getElementById('auditFrom').value,
        to: document.getElementById('auditTo').value,
        role: document.getElementById('auditRole').value,
        module: document.getElementById('auditModule').value,
        action: document.getElementById('auditAction').value,
        q: document.getElementById('auditSearch').value
    });
    const list = document.getElementById('auditLogList');
    list.innerHTML = '<div class="audit-empty">Loading activity...</div>';

    fetch(`{{ route('admin.audit-logs.feed') }}?${params.toString()}`)
        .then(r => r.json())
        .then(logs => {
            if (!logs.length) {
                list.innerHTML = '<div class="audit-empty">No audit logs found for selected filters.</div>';
                return;
            }

            list.innerHTML = logs.map(log => `
                <div class="audit-item">
                    <div class="audit-action-mark"><i class="fas fa-history"></i></div>
                    <div>
                        <div class="audit-title">${escapeHtml(log.sentence)}</div>
                        <div class="audit-meta">
                            <span class="audit-chip">${escapeHtml(log.module)}</span>
                            Date: <strong>${escapeHtml(log.date)}</strong>
                            Time: <strong>${escapeHtml(log.time)}</strong>
                        </div>
                    </div>
                    <div class="audit-time">${escapeHtml(log.created_at)}</div>
                </div>
            `).join('');
        })
        .catch(() => {
            list.innerHTML = '<div class="audit-empty text-danger">Unable to load audit logs.</div>';
        });
}

function resetAuditFilters() {
    ['auditRole', 'auditModule', 'auditAction', 'auditSearch'].forEach(id => {
        document.getElementById(id).value = '';
    });
    document.getElementById('auditFrom').value = '{{ now()->toDateString() }}';
    document.getElementById('auditTo').value = '{{ now()->toDateString() }}';
    loadAuditLogs();
}

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, function (char) {
        return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]);
    });
}
@endcan

/* ══ INACTIVE DRAWER ══════════════════════════════════ */
let drawerLoaded = false;
let allInactive  = [];

function openInactiveDrawer() {
    document.getElementById('inactiveDrawer').classList.add('open');
    document.getElementById('inactiveOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
    if (!drawerLoaded) loadInactiveEmployees();
}

function closeInactiveDrawer() {
    document.getElementById('inactiveDrawer').classList.remove('open');
    document.getElementById('inactiveOverlay').classList.remove('open');
    document.body.style.overflow = '';
}

function loadInactiveEmployees() {
    fetch('{{ route("admin.home.inactiveList") }}')
        .then(r => r.json())
        .then(data => {
            allInactive  = data;
            drawerLoaded = true;
            renderDrawer(data);
        })
        .catch(() => {
            document.getElementById('drawerContent').innerHTML =
                '<p class="text-danger text-center">Failed to load data.</p>';
        });
}

function renderDrawer(employees) {

    const container = document.getElementById('drawerContent');

    if (!employees.length) {

        container.innerHTML = `
            <div style="text-align:center;padding:50px 20px;color:#94a3b8;">
                <div style="font-size:3rem;margin-bottom:10px;">🎉</div>
                <p>No inactive employees!</p>
            </div>
        `;

        return;
    }

    container.innerHTML = employees.map(emp => {

        const initials = (emp.full_name || 'E')
            .slice(0,2)
            .toUpperCase();

        const log = emp.status_logs?.[0];

        const changedBy = log?.changed_by_name || 'Unknown';

        const approvedBy = log?.approved_by_name ?? null;

        const changedAt = log?.changed_at
            ? log.changed_at.substring(0,10)
            : '—';

        // ✅ FIXED LOGIC
        const isPending = approvedBy === null;

        return `

        <div class="inactive-card">

            <div class="inactive-card-top">

                <div class="inactive-avatar">
                    ${initials}
                </div>

                <div>
                    <div class="inactive-name">
                        ${emp.full_name || 'N/A'}
                    </div>

                    <div class="inactive-dept">
                        ${emp.department || ''}
                        ${emp.position ? ' · ' + emp.position : ''}
                    </div>
                </div>

                <span class="inactive-status status-${emp.status}">
                    ${emp.status}
                </span>

            </div>

            <div class="inactive-meta">

                <div class="meta-row">
                    <span>📝 Changed By:</span>
                    <strong>${changedBy}</strong>
                </div>

                <div class="meta-row">
                    <span>📅 Changed Date:</span>
                    <strong>${changedAt}</strong>
                </div>

                <div class="meta-row">
                    <span>✅ Approved By:</span>

                    <strong>
                        ${approvedBy ?? 'Pending'}
                    </strong>
                </div>

                <div class="meta-row">
                    <span>📌 Status:</span>

                    <strong style="
                        color:${isPending ? '#d97706' : '#16a34a'};
                        font-weight:700;
                    ">
                        ${isPending ? 'Pending Approval' : 'Approved'}
                    </strong>

                </div>

            </div>

            ${
                isPending
                ? `
                    <button
                        class="btn btn-warning w-100 mb-2"
                        onclick="approveStatus(${emp.id})"
                    >
                        ✅ Approve Status
                    </button>
                `
                : ''
            }

            <button
                class="btn-reactivate"
                onclick="openReactivate(${emp.id}, '${emp.full_name}')"
            >
                ♻️ Reactivate Employee
            </button>

        </div>

        `;

    }).join('');
}
function approveStatus(employeeId)
{
    if (!confirm('Approve this status change?')) {
        return;
    }

    fetch(`/admin/employees/${employeeId}/approve-status`, {

        method: 'POST',

        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }

    })
    .then(async res => {

        const data = await res.json();

        if (!res.ok) {
            throw new Error(data.message || 'Approval failed');
        }

        return data;
    })

    .then(res => {

        alert(res.message);

        drawerLoaded = false;

        allInactive = [];

        loadInactiveEmployees();
    })

    .catch(err => {

        alert(err.message || 'Something went wrong');

    });
}
function filterDrawer(query) {
    const q = query.toLowerCase().trim();
    const filtered = q ? allInactive.filter(e => (e.full_name||'').toLowerCase().includes(q)) : allInactive;
    renderDrawer(filtered);
}

/* ── Reactivate ── */
function openReactivate(id, name) {
    document.getElementById('reactEmployeeId').value = id;
    document.getElementById('reactName').textContent  = name;
    const modal = new bootstrap.Modal(document.getElementById('reactivateModal'));
    modal.show();
}

function confirmReactivate() {
    const id      = document.getElementById('reactEmployeeId').value;
    const remarks = document.getElementById('reactRemarks').value;

    fetch(`/admin/employees/${id}/reactivate`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ remarks })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            bootstrap.Modal.getInstance(document.getElementById('reactivateModal')).hide();
            drawerLoaded = false;
            allInactive  = [];
            document.getElementById('drawerContent').innerHTML =
                '<div class="drawer-loading"><div class="spinner"></div><span>Refreshing...</span></div>';
            loadInactiveEmployees();
            // Optional: reload page after 1.5s to update stats
            setTimeout(() => location.reload(), 1500);
        } else {
            alert(res.message || 'Error reactivating.');
        }
    });
}

/* ── Celebrations ── */
function fireCrackers() {
    const end = Date.now() + 5000;
    (function frame() {
        confetti({ particleCount:10, angle:60,  spread:80, origin:{x:0} });
        confetti({ particleCount:10, angle:120, spread:80, origin:{x:1} });
        confetti({ particleCount:12, spread:360, origin:{x:.5,y:.3} });
        if (Date.now() < end) requestAnimationFrame(frame);
    })();
}

function launchBalloons() {
    const colors = ['#ef4444','#22c55e','#3b82f6','#eab308','#ec4899'];
    for (let i = 0; i < 15; i++) {
        const b = document.createElement('div');
        b.style.cssText = `
            position:fixed; bottom:-120px;
            left:${Math.random()*100}vw;
            width:60px; height:80px;
            border-radius:50%;
            background:${colors[Math.floor(Math.random()*colors.length)]};
            animation:flyUp ${4+Math.random()*3}s linear forwards;
            z-index:2000; opacity:.9;`;
        document.body.appendChild(b);
        setTimeout(() => b.remove(), 8000);
    }
}
</script>

<style>
@keyframes flyUp {
    0%   { transform:translateY(0) translateX(0);    opacity:1; }
    100% { transform:translateY(-120vh) translateX(-30px); opacity:0; }
}
</style>

@section('scripts')
@parent
@include('admin.groupTasks.live-script')
@if(($birthdayEmployees->count() ?? 0) || ($anniversaryEmployees->count() ?? 0))
<script>
document.addEventListener('DOMContentLoaded', function () {
    setTimeout(() => {
        const modal = new bootstrap.Modal(document.getElementById('celebrationModal'));
        modal.show();
        const sound = document.getElementById('fireSound');
        sound.volume = 1;
        sound.play().catch(()=>{});
        fireCrackers();
        launchBalloons();
    }, 800);
});
</script>
@endif
@endsection
@endsection
