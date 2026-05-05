{{--
  attendance_modal.blade.php
  Injected into #attendanceModalBody via fetchDetail()
  Variables: $attendanceDetail, $attendanceLog, $leaveRequest,
             $work_start_time, $work_end_time,
             $punchInLatitude, $punchInLongitude, $punchInLocation,
             $punchOutLatitude, $punchOutLongitude, $punchOutLocation,
             $hasPunchIn, $hasPunchOut, $selectedDate
--}}

{{-- ══ Hidden Inputs (read by parent JS) ══ --}}
<input type="hidden" id="hasPunchIn"   value="{{ $hasPunchIn  ? '1' : '0' }}">
<input type="hidden" id="hasPunchOut"  value="{{ $hasPunchOut ? '1' : '0' }}">
<input type="hidden" id="latitude"     value="">
<input type="hidden" id="longitude"    value="">
<input type="hidden" id="fullAddress"  value="">
<input type="hidden" id="changedBy"    value="{{ auth()->user()->name }}">
<input type="hidden" id="deviceUID"    value="">
{{-- computed punch type (set by JS, read by parent save) --}}
<input type="hidden" id="punchTypeHidden" value="{{ $hasPunchIn ? ($hasPunchOut ? 'both' : 'out') : 'in' }}">

<style>
/* ── Modal Shell ── */
#attendanceModalBody {
    font-family: 'Segoe UI', system-ui, sans-serif;
    background: #f5f7fb;
}

/* ── Status Hero Bar ── */
.atm-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    padding: 18px 24px 14px;
    border-bottom: 1px solid #e3e8f0;
    background: #fff;
}
.atm-hero .hero-left { display: flex; align-items: center; gap: 14px; }
.atm-hero .date-chip {
    background: #1e3a5f;
    color: #fff;
    padding: 6px 16px;
    border-radius: 30px;
    font-size: .82rem;
    font-weight: 700;
    letter-spacing: .5px;
}
.atm-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 14px;
    border-radius: 30px;
    font-size: .82rem;
    font-weight: 700;
}
.pill-present    { background:#dcfce7; color:#166534; }
.pill-absent     { background:#fee2e2; color:#991b1b; }
.pill-half_time  { background:#dbeafe; color:#1e40af; }
.pill-leave      { background:#fef9c3; color:#92400e; }
.pill-week_off   { background:#f1f5f9; color:#475569; }
.pill-holiday    { background:#ede9fe; color:#5b21b6; }
.pill-late       { background:#ffedd5; color:#9a3412; }
.pill-paid_leave { background:#d1fae5; color:#065f46; }
.pill-default    { background:#f1f5f9; color:#475569; }

/* ── Sections Grid ── */
.atm-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    padding: 16px 24px;
}
@media (max-width: 700px) { .atm-grid { grid-template-columns: 1fr; } }

/* ── Cards ── */
.atm-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(30,58,95,.07);
    overflow: hidden;
    animation: atmFadeUp .35s ease both;
}
.atm-card.full { grid-column: 1 / -1; }
@keyframes atmFadeUp {
    from { opacity:0; transform:translateY(10px); }
    to   { opacity:1; transform:translateY(0); }
}

.atm-card-header {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 18px;
    font-size: .82rem;
    font-weight: 700;
    letter-spacing: .4px;
    text-transform: uppercase;
}
.atm-card-header .icon { font-size: 1rem; }
.hdr-blue   { background: linear-gradient(135deg,#1e3a5f,#2563eb); color:#fff; }
.hdr-green  { background: linear-gradient(135deg,#064e3b,#10b981); color:#fff; }
.hdr-amber  { background: linear-gradient(135deg,#78350f,#f59e0b); color:#fff; }
.hdr-violet { background: linear-gradient(135deg,#4c1d95,#7c3aed); color:#fff; }
.hdr-slate  { background: linear-gradient(135deg,#1e293b,#475569); color:#fff; }

.atm-card-body { padding: 14px 18px; }

/* ── Row pairs ── */
.atm-row {
    display: flex;
    align-items: flex-start;
    padding: 6px 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: .875rem;
    gap: 8px;
}
.atm-row:last-child { border-bottom: none; }
.atm-row .lbl {
    min-width: 110px;
    color: #64748b;
    font-weight: 600;
    font-size: .8rem;
    text-transform: uppercase;
    letter-spacing: .3px;
    padding-top: 2px;
}
.atm-row .val { color: #1e293b; font-weight: 500; flex: 1; }

/* ── Time chip ── */
.time-chip {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #f1f5f9;
    border-radius: 8px;
    padding: 3px 10px;
    font-size: .8rem;
    font-weight: 600;
    color: #1e293b;
    margin: 1px;
}
.time-chip.late-chip { background: #fee2e2; color: #991b1b; }
.time-chip.ok-chip   { background: #dcfce7; color: #166534; }

/* ── Punch image ── */
.punch-img-wrap { display: flex; gap: 10px; flex-wrap: wrap; }
.punch-img-wrap img {
    width: 80px; height: 80px;
    object-fit: cover;
    border-radius: 10px;
    border: 2px solid #e2e8f0;
    transition: transform .2s;
    cursor: pointer;
}
.punch-img-wrap img:hover { transform: scale(1.08); }

/* ── Stat chips (log) ── */
.stat-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 4px; }
.stat-chip {
    display: flex;
    flex-direction: column;
    align-items: center;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 8px 14px;
    min-width: 90px;
    text-align: center;
}
.stat-chip .sc-val { font-size: 1.1rem; font-weight: 800; color: #1e3a5f; }
.stat-chip .sc-lbl { font-size: .7rem; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; margin-top: 2px; }
.stat-chip.danger .sc-val  { color: #dc2626; }
.stat-chip.success .sc-val { color: #16a34a; }
.stat-chip.warning .sc-val { color: #d97706; }

/* ── Map ── */
#atm-map { height: 320px; width: 100%; border-radius: 0 0 14px 14px; }

/* ── Admin Panel ── */
.atm-admin-panel {
    background: #fff;
    margin: 0 24px 16px;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(30,58,95,.07);
    overflow: hidden;
    border: 2px solid #e0e7ff;
}
.atm-admin-header {
    background: linear-gradient(135deg, #1e3a5f, #3b82f6);
    color: #fff;
    padding: 12px 18px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 700;
    font-size: .85rem;
    letter-spacing: .4px;
    text-transform: uppercase;
}
.atm-admin-body { padding: 16px 18px; }

/* Form controls */
.atm-form-group { margin-bottom: 12px; }
.atm-form-group label {
    font-size: .78rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: .4px;
    display: block;
    margin-bottom: 5px;
}
.atm-select, .atm-input {
    width: 100%;
    padding: 9px 12px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: .875rem;
    color: #1e293b;
    background: #f8fafc;
    transition: border-color .2s, box-shadow .2s;
    outline: none;
}
.atm-select:focus, .atm-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59,130,246,.12);
    background: #fff;
}

.punch-time-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
@media (max-width: 480px) { .punch-time-row { grid-template-columns: 1fr; } }

.time-auto-note {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: .78rem;
    color: #2563eb;
    background: #eff6ff;
    border-radius: 8px;
    padding: 5px 10px;
    margin-top: 4px;
}

/* Save btn */
.atm-save-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #1e3a5f, #2563eb);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 10px 28px;
    font-size: .875rem;
    font-weight: 700;
    letter-spacing: .4px;
    cursor: pointer;
    transition: opacity .2s, transform .15s;
    box-shadow: 0 4px 14px rgba(37,99,235,.35);
}
.atm-save-btn:hover  { opacity: .9; transform: translateY(-1px); }
.atm-save-btn:active { transform: translateY(0); }

/* Punch-out toggle */
.punch-out-toggle-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px 14px;
    cursor: pointer;
    user-select: none;
    transition: border-color .2s;
}
.punch-out-toggle-wrap:hover { border-color: #3b82f6; }
.punch-out-toggle-wrap input[type=checkbox] {
    width: 18px; height: 18px; cursor: pointer; accent-color: #2563eb;
}
.punch-out-toggle-wrap .tog-label {
    font-size: .875rem; font-weight: 600; color: #1e293b; text-transform: none; letter-spacing: 0;
}
.punch-out-toggle-wrap .tog-sub {
    font-size: .75rem; color: #64748b; margin-top: 1px;
}

/* Location pill */
.loc-pill {
    display: inline-flex;
    align-items: flex-start;
    gap: 6px;
    background: #f1f5f9;
    border-radius: 8px;
    padding: 5px 10px;
    font-size: .8rem;
    color: #334155;
    line-height: 1.4;
    word-break: break-word;
}

/* Empty state */
.atm-empty {
    text-align: center;
    padding: 28px 16px;
    color: #94a3b8;
    font-size: .9rem;
}
.atm-empty .empty-icon { font-size: 2.5rem; margin-bottom: 8px; }
</style>

{{-- ══ STATUS HERO BAR ══ --}}
<div class="atm-hero">
    <div class="hero-left">
        <div class="date-chip">📅 {{ $selectedDate }}</div>

        @php
            $curStatus = $attendanceDetail?->status ?? 'N/A';
            $pillClass = 'pill-' . $curStatus;
            $statusIcons = [
                'present'    => '✅',
                'absent'     => '❌',
                'half_time'  => '🕐',
                'leave'      => '🏖',
                'week_off'   => '💤',
                'holiday'    => '🎉',
                'late'       => '⏰',
                'paid_leave' => '💳',
            ];
            $icon = $statusIcons[$curStatus] ?? '📋';
        @endphp

        @if($attendanceDetail)
            <span class="atm-status-pill {{ $pillClass }}">
                {{ $icon }} {{ ucfirst(str_replace('_', ' ', $curStatus)) }}
            </span>
        @else
            <span class="atm-status-pill pill-default">📋 No Record</span>
        @endif
    </div>

    @if($attendanceDetail)
        <div class="d-flex gap-2 flex-wrap">
            @if($attendanceDetail->punch_in_time)
                <span class="time-chip">
                    🟢 IN: {{ \Carbon\Carbon::parse($attendanceDetail->punch_in_time)->format('H:i') }}
                </span>
            @endif
            @if($attendanceDetail->punch_out_time)
                <span class="time-chip">
                    🔴 OUT: {{ \Carbon\Carbon::parse($attendanceDetail->punch_out_time)->format('H:i') }}
                </span>
            @endif
        </div>
    @endif
</div>

{{-- ══ MAIN GRID ══ --}}
<div class="atm-grid" style="padding-top:20px;">

    {{-- ── ATTENDANCE RECORD ── --}}
    @if($attendanceDetail)
    <div class="atm-card" style="animation-delay:.05s">
        <div class="atm-card-header hdr-blue">
            <span class="icon">👤</span> Attendance Record
        </div>
        <div class="atm-card-body">
            <div class="atm-row">
                <span class="lbl">Status</span>
                <span class="val">
                    <span class="atm-status-pill {{ $pillClass }}" style="font-size:.75rem; padding:3px 10px;">
                        {{ $icon }} {{ ucfirst(str_replace('_', ' ', $curStatus)) }}
                    </span>
                </span>
            </div>
            <div class="atm-row">
                <span class="lbl">Punch In</span>
                <span class="val">
                    @if($attendanceDetail->punch_in_time)
                        <span class="time-chip">🟢 {{ \Carbon\Carbon::parse($attendanceDetail->punch_in_time)->format('h:i A') }}</span>
                    @else <span class="text-muted">—</span> @endif
                </span>
            </div>
            <div class="atm-row">
                <span class="lbl">Punch Out</span>
                <span class="val">
                    @if($attendanceDetail->punch_out_time)
                        <span class="time-chip">🔴 {{ \Carbon\Carbon::parse($attendanceDetail->punch_out_time)->format('h:i A') }}</span>
                    @else <span class="text-muted">—</span> @endif
                </span>
            </div>
            <div class="atm-row">
                <span class="lbl">Type</span>
                <span class="val">{{ ucfirst($attendanceDetail->type ?? '—') }}</span>
            </div>
            @if($attendanceDetail->changed_by)
            <div class="atm-row">
                <span class="lbl">Changed By</span>
                <span class="val">{{ $attendanceDetail->changed_by }}</span>
            </div>
            @endif
            @if($attendanceDetail->note)
            <div class="atm-row">
                <span class="lbl">Note</span>
                <span class="val">{{ $attendanceDetail->note }}</span>
            </div>
            @endif

            @if($attendanceDetail->punch_in_image || $attendanceDetail->punch_out_image)
            <div class="atm-row" style="flex-direction:column; align-items:flex-start;">
                <span class="lbl" style="margin-bottom:8px;">Photos</span>
                <div class="punch-img-wrap">
                    @if($attendanceDetail->punch_in_image)
                        @php $inMime = $attendanceDetail->punch_in_image->mime_type ?? ''; @endphp
                        @if(\Str::startsWith($inMime, 'image/'))
                            <div style="text-align:center;">
                                <a href="{{ $attendanceDetail->punch_in_image->url }}" target="_blank">
                                    <img src="{{ $attendanceDetail->punch_in_image->preview }}" alt="Punch In">
                                </a>
                                <div style="font-size:.68rem; color:#64748b; margin-top:3px; font-weight:600;">IN</div>
                            </div>
                        @else
                            <a href="{{ $attendanceDetail->punch_in_image->url }}" target="_blank" class="btn btn-sm btn-outline-primary">📄 IN File</a>
                        @endif
                    @endif
                    @if($attendanceDetail->punch_out_image)
                        @php $outMime = $attendanceDetail->punch_out_image->mime_type ?? ''; @endphp
                        @if(\Str::startsWith($outMime, 'image/'))
                            <div style="text-align:center;">
                                <a href="{{ $attendanceDetail->punch_out_image->url }}" target="_blank">
                                    <img src="{{ $attendanceDetail->punch_out_image->preview }}" alt="Punch Out">
                                </a>
                                <div style="font-size:.68rem; color:#64748b; margin-top:3px; font-weight:600;">OUT</div>
                            </div>
                        @else
                            <a href="{{ $attendanceDetail->punch_out_image->url }}" target="_blank" class="btn btn-sm btn-outline-danger">📄 OUT File</a>
                        @endif
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ── LEAVE REQUEST ── --}}
    @if($leaveRequest)
    <div class="atm-card" style="animation-delay:.1s">
        <div class="atm-card-header hdr-amber">
            <span class="icon">🏖</span> Leave Request
        </div>
        <div class="atm-card-body">
            <div class="atm-row"><span class="lbl">Title</span><span class="val fw-bold">{{ $leaveRequest->title }}</span></div>
            <div class="atm-row">
                <span class="lbl">Type</span>
                <span class="val">
                    <span class="badge" style="background:#fef3c7;color:#92400e;padding:3px 8px;border-radius:6px;font-size:.78rem;">
                        {{ $leaveRequest->leaveType->name ?? 'N/A' }}
                    </span>
                </span>
            </div>
            <div class="atm-row">
                <span class="lbl">Status</span>
                <span class="val">
                    @php
                        $lsColors = ['approved'=>'#dcfce7','pending'=>'#fef9c3','rejected'=>'#fee2e2'];
                        $ltColors = ['approved'=>'#166534','pending'=>'#92400e','rejected'=>'#991b1b'];
                        $ls = strtolower($leaveRequest->status ?? 'pending');
                    @endphp
                    <span style="background:{{ $lsColors[$ls]??'#f1f5f9' }};color:{{ $ltColors[$ls]??'#475569' }};padding:3px 10px;border-radius:20px;font-size:.78rem;font-weight:700;">
                        {{ ucfirst($ls) }}
                    </span>
                </span>
            </div>
            <div class="atm-row"><span class="lbl">From</span><span class="val">{{ $leaveRequest->date_from }}</span></div>
            <div class="atm-row"><span class="lbl">To</span><span class="val">{{ $leaveRequest->date_to }}</span></div>
            @if($leaveRequest->description)
            <div class="atm-row"><span class="lbl">Reason</span><span class="val">{{ $leaveRequest->description }}</span></div>
            @endif
            @if($leaveRequest->remark)
            <div class="atm-row"><span class="lbl">Remark</span><span class="val">{{ $leaveRequest->remark }}</span></div>
            @endif
        </div>
    </div>
    @endif

    {{-- ── LOG DETAILS ── --}}
    @if($attendanceLog)
    <div class="atm-card {{ !$leaveRequest && !$attendanceDetail ? 'full' : '' }}" style="animation-delay:.15s">
        <div class="atm-card-header hdr-green">
            <span class="icon">⏱</span> Log Details
        </div>
        <div class="atm-card-body">
            <div class="stat-chips mb-3">
                <div class="stat-chip {{ $attendanceLog->late_by_minutes > 0 ? 'danger' : 'success' }}">
                    <span class="sc-val">{{ $attendanceLog->late_by_minutes }}</span>
                    <span class="sc-lbl">Late (min)</span>
                </div>
                <div class="stat-chip {{ $attendanceLog->left_early_by_minutes > 0 ? 'warning' : 'success' }}">
                    <span class="sc-val">{{ $attendanceLog->left_early_by_minutes }}</span>
                    <span class="sc-lbl">Early Out (min)</span>
                </div>
                <div class="stat-chip {{ $attendanceLog->overtime_by_minutes > 0 ? 'success' : '' }}">
                    <span class="sc-val">{{ $attendanceLog->overtime_by_minutes }}</span>
                    <span class="sc-lbl">Overtime (min)</span>
                </div>
                <div class="stat-chip">
                    <span class="sc-val">{{ $attendanceLog->total_work_minutes }}</span>
                    <span class="sc-lbl">Total Work (min)</span>
                </div>
            </div>
            <div class="atm-row">
                <span class="lbl">In Time</span>
                <span class="val">
                    <span class="time-chip">Expected: {{ $attendanceLog->expected_in }}</span>
                    <span class="time-chip {{ $attendanceLog->late_by_minutes > 0 ? 'late-chip' : 'ok-chip' }}">
                        Actual: {{ $attendanceLog->actual_in }}
                    </span>
                </span>
            </div>
            <div class="atm-row">
                <span class="lbl">Out Time</span>
                <span class="val">
                    <span class="time-chip">Expected: {{ $attendanceLog->expected_out }}</span>
                    @if($attendanceLog->actual_out)
                        <span class="time-chip {{ $attendanceLog->left_early_by_minutes > 0 ? 'late-chip' : 'ok-chip' }}">
                            Actual: {{ $attendanceLog->actual_out }}
                        </span>
                    @else
                        <span class="text-muted" style="font-size:.8rem;">Not yet</span>
                    @endif
                </span>
            </div>
        </div>
    </div>
    @endif

    {{-- ── LOCATION MAP ── --}}
    <div class="atm-card full" style="animation-delay:.2s">
        <div class="atm-card-header hdr-slate">
            <span class="icon">🗺</span> Location Map
        </div>

        @if($punchInLatitude || $punchOutLatitude)
            <div style="padding:12px 18px 0; display:flex; gap:12px; flex-wrap:wrap;">
                @if($punchInLocation)
                <div>
                    <div style="font-size:.7rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px;">🟢 Punch In</div>
                    <div class="loc-pill"><span>📍</span> {{ $punchInLocation }}</div>
                </div>
                @endif
                @if($punchOutLocation)
                <div>
                    <div style="font-size:.7rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px;">🔴 Punch Out</div>
                    <div class="loc-pill"><span>📍</span> {{ $punchOutLocation }}</div>
                </div>
                @endif
            </div>
            <div id="atm-map"></div>
        @else
            <div class="atm-empty">
                <div class="empty-icon">🗺</div>
                <div>No location data available for this day.</div>
            </div>
        @endif
    </div>

</div>{{-- end .atm-grid --}}

{{-- ══ EMPTY STATE ══ --}}
@if(!$attendanceDetail && !$attendanceLog && !$leaveRequest)
<div style="padding: 0 24px 16px;">
    <div class="atm-card">
        <div class="atm-empty">
            <div class="empty-icon">📭</div>
            <div style="font-weight:600; color:#475569;">No attendance, leave, or log data for this day.</div>
        </div>
    </div>
</div>
@endif

{{-- ══ ADMIN: CHANGE STATUS PANEL — always shown to admin, even if record exists ══ --}}
@if(auth()->user()->is_admin)
<div class="atm-admin-panel" id="adminStatusPanel">
    <div class="atm-admin-header">
        <span>🔧</span> Update Attendance
        <span style="margin-left:auto; opacity:.7; font-size:.7rem;">Admin Only</span>
    </div>
    <div class="atm-admin-body">

        {{-- Status Select — full width, always shown --}}
        <div class="atm-form-group">
            <label>Status</label>
            <select class="atm-select" id="attendanceStatusSelect">
                <option value="">-- Select Status --</option>
                <option value="present"    {{ ($attendanceDetail?->status === 'present')    ? 'selected' : '' }}>✅ Present</option>
                <option value="absent"     {{ ($attendanceDetail?->status === 'absent')     ? 'selected' : '' }}>❌ Absent</option>
                <option value="half_time"  {{ ($attendanceDetail?->status === 'half_time')  ? 'selected' : '' }}>🕐 Half Day</option>
                <option value="late"       {{ ($attendanceDetail?->status === 'late')       ? 'selected' : '' }}>⏰ Late</option>
                <option value="leave"      {{ ($attendanceDetail?->status === 'leave')      ? 'selected' : '' }}>🏖 Leave</option>
                <option value="paid_leave" {{ ($attendanceDetail?->status === 'paid_leave') ? 'selected' : '' }}>💳 Paid Leave</option>
                <option value="week_off"   {{ ($attendanceDetail?->status === 'week_off')   ? 'selected' : '' }}>💤 Week Off</option>
                <option value="holiday"    {{ ($attendanceDetail?->status === 'holiday')    ? 'selected' : '' }}>🎉 Holiday</option>
            </select>
        </div>

        {{-- Punch In Time — shown for present / half_time / late --}}
        <div class="atm-form-group" id="punchInTimeGroup" style="display:none;">
            <label>Punch In Time</label>
            <input type="time" class="atm-input" id="punchInTimeInput"
                   value="{{ $attendanceDetail?->punch_in_time
                        ? \Carbon\Carbon::parse($attendanceDetail->punch_in_time)->format('H:i')
                        : ($work_start_time ? \Carbon\Carbon::parse($work_start_time)->format('H:i') : '10:30') }}">
            <div class="time-auto-note" id="autoInNote">
                ⚡ Auto-filled from work start ({{ $work_start_time ? \Carbon\Carbon::parse($work_start_time)->format('h:i A') : '10:30 AM' }})
            </div>
        </div>

        {{-- "Also add Punch Out?" toggle — shown once punch in section is visible --}}
        <div class="atm-form-group" id="addPunchOutGroup" style="display:none;">
            <label style="cursor:default;">Punch Out</label>
            <label class="punch-out-toggle-wrap" for="addPunchOutToggle">
                <input type="checkbox" id="addPunchOutToggle"
                       {{ $hasPunchOut ? 'checked' : '' }}>
                <div>
                    <div class="tog-label">🔴 Also add Punch Out?</div>
                    <div class="tog-sub">
                        Default: {{ $work_end_time ? \Carbon\Carbon::parse($work_end_time)->format('h:i A') : '06:00 PM' }}
                        — Location same as admin's current location
                    </div>
                </div>
            </label>
        </div>

        {{-- Punch Out Time — shown when checkbox is checked --}}
        <div class="atm-form-group" id="punchOutTimeGroup" style="display:none;">
            <label>Punch Out Time</label>
            <input type="time" class="atm-input" id="punchOutTimeInput"
                   value="{{ $attendanceDetail?->punch_out_time
                        ? \Carbon\Carbon::parse($attendanceDetail->punch_out_time)->format('H:i')
                        : ($work_end_time ? \Carbon\Carbon::parse($work_end_time)->format('H:i') : '18:30') }}">
            <div class="time-auto-note" style="background:#f0fdf4; color:#16a34a;">
                🏁 Work end: {{ $work_end_time ? \Carbon\Carbon::parse($work_end_time)->format('h:i A') : '06:30 PM' }}
            </div>
        </div>

        {{-- Location status chip --}}
        <div class="atm-form-group">
            <label>Current Location (will be saved for punch)</label>
            <div id="locStatusChip" style="display:inline-flex;align-items:center;gap:6px;background:#f1f5f9;border-radius:8px;padding:7px 14px;font-size:.82rem;color:#64748b;">
                <span>⏳</span> Fetching location…
            </div>
        </div>

        {{-- Punch Images --}}
        <div class="punch-time-row">
            <div class="atm-form-group" id="piImgGroup" style="display:none;">
                <label>Punch In Photo <span style="color:#94a3b8;">(optional)</span></label>
                <input type="file" class="atm-input" id="punchInImage" accept="image/*" capture="environment"
                       style="padding:5px 10px; cursor:pointer;">
            </div>
            <div class="atm-form-group" id="poImgGroup" style="display:none;">
                <label>Punch Out Photo <span style="color:#94a3b8;">(optional)</span></label>
                <input type="file" class="atm-input" id="punchOutImage" accept="image/*" capture="environment"
                       style="padding:5px 10px; cursor:pointer;">
            </div>
        </div>

        <div style="display:flex; justify-content:flex-end; margin-top:4px;">
            <button class="atm-save-btn" id="openPasswordModal" type="button">
                🔐 Save Attendance
            </button>
        </div>
    </div>
</div>

<div style="height:20px;"></div>

<script>
/* ═══════════════════════════════════════════════════════
   ADMIN PANEL LOGIC
   NOTE: This script is executed manually by the parent
         after innerHTML injection (scripts don't run via innerHTML).
         The parent calls window.__initAdminPanel() below.
═══════════════════════════════════════════════════════ */
window.__initAdminPanel = function() {

    const statusSel       = document.getElementById('attendanceStatusSelect');
    const inGroup         = document.getElementById('punchInTimeGroup');
    const addOutGroup     = document.getElementById('addPunchOutGroup');
    const outGroup        = document.getElementById('punchOutTimeGroup');
    const piImgGroup      = document.getElementById('piImgGroup');
    const poImgGroup      = document.getElementById('poImgGroup');
    const inInput         = document.getElementById('punchInTimeInput');
    const autoInNote      = document.getElementById('autoInNote');
    const addOutToggle    = document.getElementById('addPunchOutToggle');
    const punchTypeHidden = document.getElementById('punchTypeHidden');
    const locChip         = document.getElementById('locStatusChip');

    const workStart = '{{ $work_start_time ? \Carbon\Carbon::parse($work_start_time)->format("H:i") : "10:30" }}';
    const workEnd   = '{{ $work_end_time   ? \Carbon\Carbon::parse($work_end_time)->format("H:i")   : "18:30" }}';

    // Statuses that require punch times
    const needsPunch = ['present', 'half_time', 'late'];

    // ── recompute hidden punch_type from current UI state ──
    function computePunchType() {
        const hasPunchSection = needsPunch.includes(statusSel.value);
        if (!hasPunchSection) { punchTypeHidden.value = 'none'; return; }
        const addOut = addOutToggle.checked;
        punchTypeHidden.value = addOut ? 'both' : 'in';
    }

    // ── show/hide based on status ──
    function applyStatusVisibility() {
        const status    = statusSel.value;
        const showPunch = needsPunch.includes(status);

        inGroup.style.display     = showPunch ? 'block' : 'none';
        addOutGroup.style.display = showPunch ? 'block' : 'none';
        piImgGroup.style.display  = showPunch ? 'block' : 'none';

        if (showPunch) {
            // Auto-fill punch in with work_start_time if empty
            if (!inInput.value) inInput.value = workStart;
            autoInNote.style.display = 'flex';
            applyPunchOutVisibility();
        } else {
            // Hide punch out section too
            outGroup.style.display  = 'none';
            poImgGroup.style.display = 'none';
        }

        computePunchType();
    }

    // ── show/hide punch out based on checkbox ──
    function applyPunchOutVisibility() {
        const checked = addOutToggle.checked;
        outGroup.style.display   = checked ? 'block' : 'none';
        poImgGroup.style.display = checked ? 'block' : 'none';
        computePunchType();
    }

    // ── Event listeners ──
    statusSel.addEventListener('change', applyStatusVisibility);
    addOutToggle.addEventListener('change', applyPunchOutVisibility);

    // ── Init on load ──
    applyStatusVisibility();

    // ── Location chip update ──
    function updateLocChip(loc) {
        if (loc && loc.lat) {
            locChip.innerHTML        = '<span>✅</span> ' + (loc.address || (loc.lat.toFixed(4) + ', ' + loc.lng.toFixed(4)));
            locChip.style.background = '#dcfce7';
            locChip.style.color      = '#166534';
        } else {
            locChip.innerHTML        = '<span>⚠️</span> Location unavailable';
            locChip.style.background = '#fee2e2';
            locChip.style.color      = '#991b1b';
        }
    }

    // Poll parent currentLoc (set by geolocation in index.blade.php)
    let tries = 0;
    const poll = setInterval(() => {
        if (typeof window.currentLoc !== 'undefined') {
            clearInterval(poll);
            updateLocChip(window.currentLoc);
        }
        if (++tries > 20) { clearInterval(poll); updateLocChip(null); }
    }, 500);

    window._atmUpdateLocChip = updateLocChip;
};
</script>
@endif

{{-- ══ MAP INIT — called manually by parent after script execution ══ --}}
@if($punchInLatitude || $punchOutLatitude)
<script>
window.__initAtmMap = function() {
    var punchIn  = {
        lat: {{ (float)($punchInLatitude  ?? 0) }},
        lng: {{ (float)($punchInLongitude ?? 0) }}
    };
    var punchOut = {
        lat: {{ (float)($punchOutLatitude  ?? 0) }},
        lng: {{ (float)($punchOutLongitude ?? 0) }}
    };

    var mapEl = document.getElementById('atm-map');
    if (!mapEl || typeof google === 'undefined') return;

    var center = (punchIn.lat !== 0) ? punchIn : punchOut;

    var map = new google.maps.Map(mapEl, {
        zoom: 15,
        center: center,
        styles: [
            { featureType:'all',   elementType:'geometry', stylers:[{saturation:-20}] },
            { featureType:'road',  elementType:'geometry', stylers:[{color:'#ffffff'}] },
            { featureType:'water', stylers:[{color:'#cce4f7'}] }
        ]
    });

    var bounds = new google.maps.LatLngBounds();

    if (punchIn.lat !== 0 && punchIn.lng !== 0) {
        new google.maps.Marker({
            position : punchIn,
            map      : map,
            title    : 'Punch In',
            icon: {
                path        : google.maps.SymbolPath.CIRCLE,
                scale       : 12,
                fillColor   : '#16a34a',
                fillOpacity : 1,
                strokeColor : '#fff',
                strokeWeight: 2.5
            },
            label: { text:'IN', color:'#fff', fontSize:'9px', fontWeight:'bold' }
        });
        bounds.extend(punchIn);
    }

    if (punchOut.lat !== 0 && punchOut.lng !== 0) {
        new google.maps.Marker({
            position : punchOut,
            map      : map,
            title    : 'Punch Out',
            icon: {
                path        : google.maps.SymbolPath.CIRCLE,
                scale       : 12,
                fillColor   : '#dc2626',
                fillOpacity : 1,
                strokeColor : '#fff',
                strokeWeight: 2.5
            },
            label: { text:'OUT', color:'#fff', fontSize:'8px', fontWeight:'bold' }
        });
        bounds.extend(punchOut);
    }

    if (!bounds.isEmpty()) {
        map.fitBounds(bounds);
        // Don't zoom in too close for single marker
        google.maps.event.addListenerOnce(map, 'idle', function() {
            if (map.getZoom() > 17) map.setZoom(17);
        });
    }
};
</script>
@endif
