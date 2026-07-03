@extends('layouts.admin')

@section('content')

{{-- ════════════════════════════════════════════════════════
     STATUS CHANGE MODAL
════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content emp-modal">
            <div class="modal-header emp-modal-header">
                <h5 class="modal-title">🔄 Change Employee Status</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p style="color:#475569;font-size:.9rem;">
                    Changing <strong id="statusEmpName" style="color:#1e293b;"></strong>'s status will be logged
                    and will require approval.
                </p>
                <input type="hidden" id="statusEmpId">
                <div class="mb-3">
                    <label class="emp-label">New Status</label>
                    <select id="newStatusSelect" class="emp-select">
                        <option value="Active">✅ Active</option>
                        <option value="Resigned">📤 Resigned</option>
                        <option value="Terminated">🚫 Terminated</option>
                        <option value="Suspended">⏸ Suspended</option>
                    </select>
                </div>
                <div>
                    <label class="emp-label">Remarks</label>
                    <textarea id="statusRemarks" class="emp-textarea" rows="2"
                        placeholder="Reason for status change..."></textarea>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #f1f5f9;padding:12px 20px;">
                <button class="emp-btn-outline" data-bs-dismiss="modal">Cancel</button>
                <button class="emp-btn-primary" onclick="submitStatusChange()">Save Status</button>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════
     APPROVE STATUS MODAL
════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content emp-modal">
            <div class="modal-header" style="background:#059669;color:#fff;border-radius:16px 16px 0 0;">
                <h5 class="modal-title">✅ Approve Status Change</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p style="color:#475569;font-size:.9rem;">
                    Approve the pending status change for <strong id="approveEmpName"></strong>?
                </p>
                <input type="hidden" id="approveEmpId">
            </div>
            <div class="modal-footer" style="border-top:1px solid #f1f5f9;padding:12px 20px;">
                <button class="emp-btn-outline" data-bs-dismiss="modal">Cancel</button>
                <button class="emp-btn-success" onclick="submitApprove()">✅ Approve</button>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════
     MAIN CONTENT
════════════════════════════════════════════════════════ --}}
<div class="emp-page">

    {{-- ── Page Header ── --}}
    <div class="emp-page-header">
        <div>
            <h3 class="emp-page-title">Employee Directory</h3>
            <p class="emp-page-sub">Manage your workforce — {{ $stats['total'] ?? 0 }} total employees</p>
        </div>
        @if($isAdmin)
        <a href="{{ route('admin.employees.create') }}" class="emp-btn-primary">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Employee
        </a>
        @endif
    </div>

    {{-- ── Stats Chips ── --}}
    @if($isAdmin)
    <div class="emp-stats-bar">
        <div class="emp-stat-chip chip-blue">
            <span class="chip-val">{{ $stats['total'] ?? 0 }}</span>
            <span class="chip-label">Total</span>
        </div>
        <div class="emp-stat-chip chip-green">
            <span class="chip-val">{{ $stats['active'] ?? 0 }}</span>
            <span class="chip-label">Active</span>
        </div>
        <div class="emp-stat-chip chip-red">
            <span class="chip-val">{{ $stats['inactive'] ?? 0 }}</span>
            <span class="chip-label">Inactive</span>
        </div>
        <div class="emp-stat-chip chip-amber">
            <span class="chip-val">{{ $stats['pending'] ?? 0 }}</span>
            <span class="chip-label">Pending Approval</span>
        </div>
    </div>
    @endif

    {{-- ── Flash Messages ── --}}
    @if(session('success'))
        <div class="emp-alert emp-alert-success">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="emp-alert emp-alert-danger">❌ {{ session('error') }}</div>
    @endif

    {{-- ── Search + Table ── --}}
    <div class="emp-card">
        <div class="emp-card-toolbar">
            <div class="emp-search-box">
                <svg class="search-icon" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input id="empSearch" type="text" placeholder="Search by name, code, department...">
            </div>
            <div class="emp-filter-tabs" id="filterTabs">
                <button class="tab-btn active" onclick="filterTable('all', this)">All</button>
                <button class="tab-btn" onclick="filterTable('Active', this)">Active</button>
                <button class="tab-btn" onclick="filterTable('Resigned', this)">Resigned</button>
                <button class="tab-btn" onclick="filterTable('Terminated', this)">Terminated</button>
                <button class="tab-btn" onclick="filterTable('Suspended', this)">Suspended</button>
            </div>
        </div>

        <div class="emp-table-wrapper">
            <table class="emp-table" id="empTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Employee</th>
                        <th>Code</th>
                        <th>Contact</th>
                        <th>Branch</th>
                        <th>Role</th>
                        <th>Status</th>
                        @if($isAdmin)<th>Actions</th>@endif
                    </tr>
                </thead>
                <tbody>
                @foreach($employees as $index => $employee)
                @php
                    $status    = $employee->status ?? 'Active';
                    $isPending = (bool)($employee->status_change_pending ?? false);
                    $inactive  = in_array($status, ['Resigned','Terminated','Suspended']);
                @endphp
                <tr class="emp-row {{ $inactive ? 'row-inactive' : '' }}"
                    data-status="{{ $status }}"
                    data-search="{{ strtolower($employee->full_name.' '.$employee->employee_code.' '.($employee->department??'').' '.($employee->position??'')) }}">
                    <td class="col-num">{{ $index + 1 }}</td>

                    <td>
                        <div class="emp-identity">
                            <div class="emp-thumb {{ $inactive ? 'thumb-inactive' : '' }}">
                                {{ strtoupper(substr($employee->full_name ?? 'E', 0, 2)) }}
                            </div>
                            <div>
                                <div class="emp-fullname">{{ $employee->full_name }}</div>
                                <div class="emp-email">{{ $employee->email ?? '—' }}</div>
                            </div>
                        </div>
                    </td>

                    <td><span class="emp-code">{{ $employee->employee_code }}</span></td>

                    <td>
                        <div class="emp-contact">{{ $employee->phone ?? '—' }}</div>
                    </td>

                    <td>
                        <div class="emp-branch">
                            {{ $employee->branch->title ?? 'Anywhere' }}
                        </div>
                    </td>

                    <td>
                        <div class="emp-role">
                            <span class="role-pos">{{ $employee->position ?? '—' }}</span>
                            <span class="role-dept">{{ $employee->department ?? '' }}</span>
                        </div>
                    </td>

                    <td>
                        <div class="status-cell">
                            <span class="emp-status status-{{ strtolower($status) }}">
                                {{ $status }}
                            </span>
                            @if($isPending)
                                <span class="pending-dot" title="Status change pending approval">⏳</span>
                            @endif
                        </div>
                    </td>

                    @if($isAdmin)
                    <td>
                        <div class="emp-actions">
                            <a href="{{ route('admin.employees.show', $employee->id) }}"
                               class="action-btn btn-view" title="View">
                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ route('admin.payroll.edit', $employee->id) }}"
                               class="action-btn btn-edit" title="Edit">
                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <button class="action-btn btn-status" title="Change Status"
                                onclick="openStatusModal({{ $employee->id }}, '{{ addslashes($employee->full_name) }}', '{{ $status }}')">
                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                            @if($isPending)
                            <button class="action-btn btn-approve" title="Approve Status Change"
                                onclick="openApproveModal({{ $employee->id }}, '{{ addslashes($employee->full_name) }}')">
                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </button>
                            @endif
                            <button class="action-btn btn-doc" title="Offer Letter"
                                onclick="checkOfferLetter({{ $employee->id }})">
                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </button>
                            <form action="{{ route('admin.employees.destroy', $employee->id) }}" method="POST"
                                  style="display:inline"
                                  onsubmit="return confirm('Delete {{ addslashes($employee->full_name) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-btn btn-delete" title="Delete">
                                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                    @endif
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div id="noEmpMsg" class="emp-no-data" style="display:none;">
            <div style="font-size:3rem;margin-bottom:10px;">🔍</div>
            <p>No employees match your search.</p>
        </div>
    </div>
</div>

{{-- ── Terms / Camera / Signature modals (unchanged from original) ── --}}
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5>Terms & Conditions</h5></div>
            <div class="modal-body">
                <iframe src="{{ asset('terms/policy.pdf') }}" width="100%" height="400"></iframe>
                <div class="alert alert-info mt-3">📍 Stay still • Clear background • Good lighting</div>
                <div class="form-check mt-3">
                    <input type="checkbox" id="acceptTerms" class="form-check-input">
                    <label class="form-check-label">I accept Terms & Conditions</label>
                </div>
            </div>
            <div class="modal-footer">
                <button id="acceptTermsBtn" class="btn btn-primary" disabled>Continue</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="cameraModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5>Live Camera Verification</h5></div>
            <div class="modal-body text-center">
                <video id="video" autoplay playsinline muted width="420" style="border-radius:10px;border:2px solid #0d6efd;"></video>
                <canvas id="canvas" class="d-none"></canvas>
                <p id="cameraMsg" class="text-danger mt-2"></p>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="signModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5>Digital Signature</h5></div>
            <div class="modal-body">
                <canvas id="signaturePad" style="border:1px solid #ccc;width:100%;height:200px;"></canvas>
                <p id="locationInfo" class="text-muted mt-2"></p>
            </div>
            <div class="modal-footer">
                <button id="saveSignature" class="btn btn-success">Save</button>
            </div>
        </div>
    </div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;600;700&display=swap');

.emp-page {
    font-family: 'DM Sans', sans-serif;
    padding: 24px;
    background: #f0f4f9;
    min-height: 100vh;
}

/* ── Header ── */
.emp-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
}
.emp-page-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e3a5f;
    margin: 0;
}
.emp-page-sub { color: #64748b; font-size: .85rem; margin: 4px 0 0; }

/* ── Stat chips ── */
.emp-stats-bar { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
.emp-stat-chip {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #fff;
    border-radius: 12px;
    padding: 12px 18px;
    box-shadow: 0 2px 8px rgba(0,0,0,.06);
    border-left: 3px solid;
}
.chip-val {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.4rem;
    font-weight: 700;
}
.chip-label { font-size: .75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .4px; }
.chip-blue   { border-left-color: #3b82f6; } .chip-blue   .chip-val { color: #1d4ed8; }
.chip-green  { border-left-color: #10b981; } .chip-green  .chip-val { color: #059669; }
.chip-red    { border-left-color: #ef4444; } .chip-red    .chip-val { color: #dc2626; }
.chip-amber  { border-left-color: #f59e0b; } .chip-amber  .chip-val { color: #92400e; }

/* ── Alerts ── */
.emp-alert {
    border-radius: 10px;
    padding: 12px 18px;
    font-size: .88rem;
    font-weight: 500;
    margin-bottom: 16px;
}
.emp-alert-success { background: #d1fae5; color: #065f46; border-left: 3px solid #10b981; }
.emp-alert-danger  { background: #fee2e2; color: #991b1b; border-left: 3px solid #ef4444; }

/* ── Card ── */
.emp-card {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 2px 16px rgba(0,0,0,.06);
    overflow: hidden;
}

/* ── Toolbar ── */
.emp-card-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    padding: 18px 22px;
    border-bottom: 1px solid #f1f5f9;
}
.emp-search-box {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 9px 14px;
    min-width: 240px;
}
.search-icon { color: #94a3b8; flex-shrink: 0; }
.emp-search-box input {
    border: none;
    background: transparent;
    outline: none;
    font-size: .87rem;
    color: #1e293b;
    width: 100%;
}

.emp-filter-tabs { display: flex; gap: 6px; flex-wrap: wrap; }
.tab-btn {
    border: 1.5px solid #e2e8f0;
    background: #f8fafc;
    border-radius: 8px;
    padding: 6px 14px;
    font-size: .78rem;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
    transition: all .2s;
}
.tab-btn:hover, .tab-btn.active {
    background: #1e3a5f;
    color: #fff;
    border-color: #1e3a5f;
}

/* ── Table ── */
.emp-table-wrapper { overflow-x: auto; }
.emp-table { width: 100%; border-collapse: collapse; }
.emp-table thead tr {
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
}
.emp-table thead th {
    padding: 13px 16px;
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: #64748b;
    white-space: nowrap;
}
.emp-table tbody tr {
    border-bottom: 1px solid #f1f5f9;
    transition: background .15s;
}
.emp-table tbody tr:hover { background: #f8fafc; }
.emp-table tbody tr.row-inactive { opacity: .65; }
.emp-table td { padding: 14px 16px; vertical-align: middle; }

.col-num { font-size: .8rem; color: #94a3b8; font-weight: 600; width: 40px; }

/* ── Identity cell ── */
.emp-identity { display: flex; align-items: center; gap: 10px; }
.emp-thumb {
    width: 36px; height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, #1e3a5f, #2563eb);
    color: #fff;
    font-size: .75rem;
    font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.emp-thumb.thumb-inactive { background: linear-gradient(135deg, #94a3b8, #64748b); }
.emp-fullname { font-weight: 600; font-size: .88rem; color: #1e293b; }
.emp-email { font-size: .72rem; color: #94a3b8; }

.emp-code {
    font-family: 'Space Grotesk', monospace;
    font-size: .78rem;
    font-weight: 600;
    background: #f1f5f9;
    color: #475569;
    padding: 3px 9px;
    border-radius: 6px;
}

.emp-contact { font-size: .85rem; color: #475569; }
.emp-branch  { font-size: .82rem; color: #475569; font-weight: 500; }
.emp-role .role-pos  { display: block; font-size: .83rem; color: #1e293b; font-weight: 500; }
.emp-role .role-dept { font-size: .72rem; color: #94a3b8; }

/* ── Status ── */
.status-cell { display: flex; align-items: center; gap: 6px; }
.emp-status {
    font-size: .72rem;
    font-weight: 700;
    padding: 4px 11px;
    border-radius: 50px;
    text-transform: uppercase;
    letter-spacing: .4px;
}
.status-active     { background: #d1fae5; color: #065f46; }
.status-resigned   { background: #fef3c7; color: #92400e; }
.status-terminated { background: #fee2e2; color: #991b1b; }
.status-suspended  { background: #f3f4f6; color: #374151; }
.pending-dot { font-size: .9rem; cursor: help; }

/* ── Actions ── */
.emp-actions { display: flex; gap: 5px; flex-wrap: wrap; }
.action-btn {
    width: 30px; height: 30px;
    border: none;
    border-radius: 7px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all .2s;
    text-decoration: none;
}
.action-btn:hover { transform: translateY(-1px); }
.btn-view   { background: #e0f2fe; color: #0369a1; }
.btn-edit   { background: #e0e7ff; color: #3730a3; }
.btn-status { background: #fef3c7; color: #92400e; }
.btn-approve{ background: #d1fae5; color: #065f46; }
.btn-doc    { background: #f3e8ff; color: #7e22ce; }
.btn-delete { background: #fee2e2; color: #991b1b; }

.emp-no-data { text-align: center; padding: 50px 20px; color: #64748b; }

/* ── Modal ── */
.emp-modal { border-radius: 18px; overflow: hidden; border: none; box-shadow: 0 20px 60px rgba(0,0,0,.15); }
.emp-modal-header { background: linear-gradient(135deg, #1e3a5f, #2563eb); color: #fff; border-radius: 0; }

.emp-label { display: block; font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #475569; margin-bottom: 6px; }
.emp-select, .emp-textarea {
    width: 100%;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: .88rem;
    color: #1e293b;
    outline: none;
    font-family: 'DM Sans', sans-serif;
    transition: border-color .2s;
}
.emp-select:focus, .emp-textarea:focus { border-color: #3b82f6; }
.emp-textarea { resize: none; }

/* ── Buttons ── */
.emp-btn-primary {
    background: linear-gradient(135deg, #1e3a5f, #2563eb);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 10px 22px;
    font-size: .87rem;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    text-decoration: none;
    transition: all .2s;
}
.emp-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(37,99,235,.3); color:#fff; }
.emp-btn-outline {
    background: #f8fafc;
    color: #475569;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 8px 18px;
    font-size: .87rem;
    font-weight: 600;
    cursor: pointer;
}
.emp-btn-success {
    background: linear-gradient(135deg, #059669, #10b981);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 8px 18px;
    font-size: .87rem;
    font-weight: 600;
    cursor: pointer;
}

@media(max-width:640px) {
    .emp-page { padding: 14px; }
    .emp-page-header { flex-direction: column; gap: 12px; }
    .emp-card-toolbar { flex-direction: column; align-items: stretch; }
    .emp-search-box { min-width: auto; }
}
</style>

@section('scripts')
@parent
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

<script>
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });

/* ── Live Search ── */
document.getElementById('empSearch').addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    applyFilter(q, currentFilter);
});

let currentFilter = 'all';
function filterTable(status, btn) {
    currentFilter = status;
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    applyFilter(document.getElementById('empSearch').value.toLowerCase().trim(), status);
}

function applyFilter(search, status) {
    const rows = document.querySelectorAll('#empTable tbody .emp-row');
    let found = 0;
    rows.forEach(row => {
        const matchSearch = !search || row.dataset.search.includes(search);
        const matchStatus = status === 'all' || row.dataset.status === status;
        const show = matchSearch && matchStatus;
        row.style.display = show ? '' : 'none';
        if (show) found++;
    });
    document.getElementById('noEmpMsg').style.display = found === 0 ? 'block' : 'none';
}

/* ── Status Modal ── */
function openStatusModal(id, name, currentStatus) {
    document.getElementById('statusEmpId').value    = id;
    document.getElementById('statusEmpName').textContent = name;
    document.getElementById('newStatusSelect').value = currentStatus;
    new bootstrap.Modal(document.getElementById('statusModal')).show();
}
function submitStatusChange() {
    const id      = document.getElementById('statusEmpId').value;
    const status  = document.getElementById('newStatusSelect').value;
    const remarks = document.getElementById('statusRemarks').value;
    $.post(`/admin/employees/${id}/change-status`, { status, remarks }, function (res) {
        if (res.success) { location.reload(); }
        else { alert(res.message); }
    }).fail(() => alert('Error changing status.'));
}

/* ── Approve Modal ── */
function openApproveModal(id, name) {
    document.getElementById('approveEmpId').value       = id;
    document.getElementById('approveEmpName').textContent = name;
    new bootstrap.Modal(document.getElementById('approveModal')).show();
}
function submitApprove() {
    const id = document.getElementById('approveEmpId').value;
    $.post(`/admin/employees/${id}/approve-status`, {}, function (res) {
        if (res.success) { location.reload(); }
        else { alert(res.message); }
    }).fail(() => alert('Error approving.'));
}

/* ── Offer Letter ── */
let selectedEmployeeId = null, signaturePad = null, videoStream = null;
let lat = null, lng = null, address = null;

function checkOfferLetter(id) {
    selectedEmployeeId = id;
    $.get(`/admin/employees/${id}/terms-status`, function (res) {
        if (res.accepted) {
            window.location.href = `/admin/employees/offer-letter/${id}`;
        } else {
            $('#termsModal').modal('show');
        }
    });
}
$('#acceptTerms').on('change', function () { $('#acceptTermsBtn').prop('disabled', !this.checked); });
$('#acceptTermsBtn').on('click', function () {
    navigator.geolocation.getCurrentPosition(pos => {
        lat = pos.coords.latitude; lng = pos.coords.longitude;
        fetch(`https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&key=AIzaSyBgRXfXiK8KHfSnKtunSIpGpKNmLNGNUzM`)
            .then(r => r.json()).then(d => { address = d.results[0]?.formatted_address || ''; });
        $('#termsModal').modal('hide');
        startCamera();
    }, () => alert('Location permission required'), { enableHighAccuracy: true });
});
function startCamera() {
    $('#cameraModal').modal('show');
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } }).then(stream => {
        videoStream = stream;
        const v = document.getElementById('video');
        v.srcObject = stream;
        v.onloadedmetadata = () => { v.play(); setTimeout(capturePhoto, 2500); };
    });
}
function capturePhoto() {
    const v = document.getElementById('video'), c = document.getElementById('canvas');
    c.width = v.videoWidth; c.height = v.videoHeight;
    c.getContext('2d').drawImage(v, 0, 0, c.width, c.height);
    videoStream.getTracks().forEach(t => t.stop());
    c.toBlob(blob => {
        let fd = new FormData();
        fd.append('photo', blob); fd.append('employee_id', selectedEmployeeId);
        fd.append('lat', lat); fd.append('lng', lng); fd.append('address', address);
        $.ajax({ url: '/admin/employees/save-photo', method: 'POST', data: fd,
            processData: false, contentType: false,
            success: () => { $('#cameraModal').modal('hide'); openSignature(); }
        });
    }, 'image/jpeg', 0.95);
}
function openSignature() {
    $('#signModal').modal({ backdrop: 'static', keyboard: false });
    signaturePad = new SignaturePad(document.getElementById('signaturePad'));
    $('#locationInfo').text(`📍 ${address}`);
}
$('#saveSignature').on('click', function () {
    if (!signaturePad || signaturePad.isEmpty()) { alert('Signature required'); return; }
    $.post('/admin/employees/save-signature', {
        employee_id: selectedEmployeeId, signature: signaturePad.toDataURL(), lat, lng, address
    }, () => { alert('✅ Verification completed'); location.reload(); });
});
</script>
@endsection
@endsection
