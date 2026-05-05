@extends('layouts.admin')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<style>
/* ─────────────────────────────────────────────
   LAYOUT
───────────────────────────────────────────── */
.calendar-wrapper {
    background: #fff;
    border-radius: 1rem;
    box-shadow: 0 0 20px rgba(0,0,0,.08);
    padding: 20px;
}

/* ─────────────────────────────────────────────
   SUMMARY CARDS
───────────────────────────────────────────── */
#attendanceSummary {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 1.5rem;
}

.summary-box {
    position: relative;
    border-width: 3px;
    border-style: solid;
    border-radius: 15px;
    padding: 16px 20px;
    min-width: 120px;
    text-align: center;
    background: rgba(255,255,255,.8);
    backdrop-filter: blur(4px);
    overflow: hidden;
    cursor: pointer;
    transition: transform .25s, box-shadow .25s;
    animation: fadeInUp .5s ease both;
}
.summary-box::before {
    content: attr(data-icon);
    position: absolute;
    font-size: 64px;
    opacity: .07;
    right: 8px;
    bottom: -8px;
    pointer-events: none;
}
.summary-box:hover { transform: scale(1.06); box-shadow: 0 8px 20px rgba(0,0,0,.14); }
.summary-box h5    { font-size: 15px; font-weight: 600; margin-bottom: 4px; }
.summary-box .counter { font-size: 26px; font-weight: 800; }
.border-purple { border-color: #6610f2 !important; }
#attendanceModalBody {
    overflow-y: auto;
    max-height: calc(85vh - 70px);
}
@keyframes fadeInUp {
    from { opacity:0; transform:translateY(12px); }
    to   { opacity:1; transform:translateY(0);    }
}

/* ─────────────────────────────────────────────
   FULLCALENDAR EVENTS
───────────────────────────────────────────── */
.fc-toolbar-title  { font-size: 1.4rem; font-weight: 700; color: #333; }
.fc-daygrid-day-number { font-weight: 600; }
.fc-event {
    border: none !important;
    border-radius: 5px !important;
    padding: 2px 6px !important;
    font-weight: 500;
    font-size: .82rem;
    cursor: pointer;
}
.fc-event.present    { background-color: #28a745 !important; color:#fff !important; }
.fc-event.absent     { background-color: #dc3545 !important; color:#fff !important; }
.fc-event.leave      { background-color: #ffc107 !important; color:#000 !important; }
.fc-event.half_time  { background-color: #17a2b8 !important; color:#fff !important; }
.fc-event.week_off   { background-color: #6c757d !important; color:#fff !important; }
.fc-event.week_off_s { background-color: #6c757d !important; color:#fff !important; }
.fc-event.holiday    { background-color: #6610f2 !important; color:#fff !important; }
.fc-event.late       { background-color: #fd7e14 !important; color:#fff !important; }
.fc-event.paid_leave { background-color: #20c997 !important; color:#fff !important; }

/* ─────────────────────────────────────────────
   MODAL TWEAKS
───────────────────────────────────────────── */
.modal-xl { max-width: 960px; }
.card-header { font-size: .92rem; }
.badge-status {
    display:inline-block; padding:4px 10px;
    border-radius:50px; font-size:.8rem; font-weight:600;
}
.badge-present    { background:#d4edda; color:#155724; }
.badge-absent     { background:#f8d7da; color:#721c24; }
.badge-half_time  { background:#d1ecf1; color:#0c5460; }
.badge-leave      { background:#fff3cd; color:#856404; }
.badge-week_off   { background:#e2e3e5; color:#383d41; }
.badge-holiday    { background:#e2d9f3; color:#3d0f8a; }
.badge-late       { background:#ffe5d0; color:#7b3304; }
.badge-paid_leave { background:#d2f4ea; color:#0a5740; }

#loadingSpinner {
    display:none;
    text-align:center;
    padding: 40px 0;
}
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <h2 class="mb-4 text-center fw-bold">📅 Attendance Calendar</h2>

    {{-- Action buttons --}}
    <div class="mb-3 d-flex gap-2">
        <a class="btn btn-success" href="{{ route('admin.attendance-details.create') }}">
            {{ trans('global.add') }} {{ trans('cruds.attendanceDetail.title_singular') }}
        </a>
        <button class="btn btn-warning" data-toggle="modal" data-target="#csvImportModal">
            {{ trans('global.app_csvImport') }}
        </button>
        @include('csvImport.modal', ['model'=>'AttendanceDetail','route'=>'admin.attendance-details.parseCsvImport'])
    </div>

    {{-- Employee filter (admin only) --}}
    @if(auth()->user()->is_admin)
    <div class="mb-3 text-center">
        <select class="form-control d-inline-block" style="max-width:320px;" id="userSelect">
            <option value="">🔽 Select Employee</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
            @endforeach
        </select>
    </div>
    @endif

    {{-- Summary cards --}}
    <div id="attendanceSummary">
        <div class="summary-box border-success"   data-icon="✔" data-status="present">
            <h5>Present</h5>
            <div id="count-present" class="counter text-success">0</div>
        </div>
        <div class="summary-box border-danger"    data-icon="✖" data-status="absent">
            <h5>Absent</h5>
            <div id="count-absent"  class="counter text-danger">0</div>
        </div>
        <div class="summary-box border-info"      data-icon="½" data-status="half_time">
            <h5>Half Day</h5>
            <div id="count-half_time" class="counter text-info">0</div>
        </div>
        <div class="summary-box border-warning"   data-icon="☕" data-status="leave">
            <h5>Leave</h5>
            <div id="count-leave"   class="counter text-warning">0</div>
        </div>
        <div class="summary-box border-secondary" data-icon="🛏" data-status="week_off">
            <h5>Week Off</h5>
            <div id="count-week_off" class="counter text-secondary">0</div>
        </div>
        <div class="summary-box border-purple"    data-icon="🎉" data-status="holiday">
            <h5>Holiday</h5>
            <div id="count-holiday" class="counter" style="color:#6610f2">0</div>
        </div>
        <div class="summary-box border-dark flex-grow-1" data-icon="Σ" data-status="total">
            <h5>Total</h5>
            <div id="count-total"   class="counter">0</div>
        </div>
    </div>

    {{-- Calendar --}}
    <div class="calendar-wrapper">
        <div id="calendar"></div>
    </div>
</div>

{{-- ═══════════════════ HOLIDAY MODAL ═══════════════════ --}}
<div class="modal fade" id="holidayModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="holidayModalTitle">Holiday Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p><strong>Type:</strong>       <span id="modalHolidayType">-</span></p>
                <p><strong>Optional:</strong>   <span id="modalIsOptional">-</span></p>
                <p><strong>National:</strong>   <span id="modalIsNational">-</span></p>
                <p><strong>Description:</strong><span id="modalDescription">-</span></p>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════ ATTENDANCE MODAL ═══════════════════ --}}
<div class="modal fade" id="attendanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0" style="border-radius:1rem;">

            <div class="modal-header bg-info text-white" style="border-radius:1rem 1rem 0 0;">
                <h5 class="modal-title fw-bold">
                    📅 Attendance — <span id="modalDateLabel"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>

            {{-- Spinner --}}
            <div id="loadingSpinner">
                <div class="spinner-border text-info" role="status"></div>
                <p class="mt-2 text-muted">Loading…</p>
            </div>

            {{-- Dynamic body (filled by fetchDetail) --}}
            <div id="attendanceModalBody" style="overflow-y:auto; max-height:calc(85vh - 68px);"></div>

        </div>
    </div>
</div>

{{-- ═══════════════════ MASTER PASSWORD MODAL ═══════════════════ --}}
<div class="modal fade" id="masterPasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">🔐 Confirm Master Password</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <label class="fw-bold mb-1">Master Password</label>
                <input type="password" id="masterPassword" class="form-control"
                       placeholder="Enter master password">
                <div id="pwdError" class="text-danger mt-1" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button id="confirmSaveAttendance" class="btn btn-success px-4">
                    ✔ Confirm &amp; Save
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBgRXfXiK8KHfSnKtunSIpGpKNmLNGNUzM&libraries=places"></script>

<script>
/* ══════════════════════════════════════════════════════════
   UTILITIES
══════════════════════════════════════════════════════════ */
function getDeviceUID() {
    let uid = localStorage.getItem('device_uid');
    if (!uid) { uid = crypto.randomUUID(); localStorage.setItem('device_uid', uid); }
    return uid;
}

function getLocationWithAddress(callback) {
    if (!navigator.geolocation) { callback(null); return; }
    navigator.geolocation.getCurrentPosition(pos => {
        const lat = pos.coords.latitude, lng = pos.coords.longitude;
        const geocoder = new google.maps.Geocoder();
        geocoder.geocode({ location: { lat, lng } }, (results, status) => {
            callback({
                lat, lng,
                address: (status === 'OK' && results[0]) ? results[0].formatted_address : ''
            });
        });
    }, () => callback(null), { enableHighAccuracy: true });
}

/* ══════════════════════════════════════════════════════════
   KEY HELPER — execute <script> tags injected via innerHTML
   (browsers don't auto-run scripts inserted this way)
══════════════════════════════════════════════════════════ */
function runInjectedScripts(container) {
    container.querySelectorAll('script').forEach(oldScript => {
        const newScript = document.createElement('script');
        // Copy attributes (e.g. src, type)
        Array.from(oldScript.attributes).forEach(attr =>
            newScript.setAttribute(attr.name, attr.value)
        );
        newScript.textContent = oldScript.textContent;
        // Append to document body so it actually executes
        document.body.appendChild(newScript);
        // Remove the dead copy from the modal so it isn't run twice
        oldScript.parentNode.removeChild(oldScript);
    });
}

/* ══════════════════════════════════════════════════════════
   MAIN
══════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', function () {

    /* ── State ── */
    let selectedUserId = '{{ auth()->id() }}';
    let selectedDate   = null;
    window.currentLoc  = undefined;   // shared with modal partial

    /* ── Elements ── */
    const modalBody      = document.getElementById('attendanceModalBody');
    const spinner        = document.getElementById('loadingSpinner');
    const modalDateLabel = document.getElementById('modalDateLabel');

    /* ── Pre-fetch geolocation once page loads ── */
    getLocationWithAddress(loc => {
        window.currentLoc = loc;
        // If modal is already open and loc chip updater exists, update it
        if (typeof window._atmUpdateLocChip === 'function') {
            window._atmUpdateLocChip(loc);
        }
    });

    /* ══════════════════════════════════════════
       FULLCALENDAR
    ══════════════════════════════════════════ */
    const calendarEl = document.getElementById('calendar');
    const calendar   = new FullCalendar.Calendar(calendarEl, {
        initialView : 'dayGridMonth',
        height      : 680,
        showNonCurrentDates: false,   // ✅ ye line add karo
        fixedWeekCount: false,        // (optional: extra empty rows hata dega)
        headerToolbar: {
            left  : 'prev,next today',
            center: 'title',
            right : 'dayGridMonth,timeGridWeek'
        },

        events: function (fetchInfo, successCallback, failureCallback) {
            const url = `{{ route('admin.attendance-details.calendarData', ['user' => '__UID__']) }}`
                          .replace('__UID__', selectedUserId)
                        + `?start=${fetchInfo.startStr}&end=${fetchInfo.endStr}`;

            fetch(url)
                .then(r => { if (!r.ok) throw new Error('Network error'); return r.json(); })
                .then(data => { successCallback(data); updateSummary(data); })
                .catch(failureCallback);
        },

        eventClick(info) {
            const props = info.event.extendedProps;

            if (info.event.classNames.includes('holiday')) {
                document.getElementById('holidayModalTitle').textContent = info.event.title;
                document.getElementById('modalHolidayType').textContent  = props.holiday_type  ?? '-';
                document.getElementById('modalIsOptional').textContent   = props.is_optional   ? 'Yes' : 'No';
                document.getElementById('modalIsNational').textContent   = props.is_national   ? 'Yes' : 'No';
                document.getElementById('modalDescription').textContent  = props.description   ?? '-';
                $('#holidayModal').modal('show');
                return;
            }

            selectedDate = info.event.startStr;
            openAttendanceModal();
        }
    });

    calendar.render();

    /* ══════════════════════════════════════════
       SUMMARY CARDS
    ══════════════════════════════════════════ */
    function updateSummary(events) {
        const counts = { present:0, absent:0, half_time:0, leave:0, week_off:0, holiday:0 };
        events.forEach(ev => {
            const cls = (ev.classNames || [])[0];
            if (cls && counts.hasOwnProperty(cls)) counts[cls]++;
            if (cls === 'week_off_s') counts.week_off++;
        });
        const total = Object.values(counts).reduce((a,b)=>a+b, 0);
        Object.keys(counts).forEach(k => {
            const el = document.getElementById('count-' + k);
            if (el) el.textContent = counts[k];
        });
        const totalEl = document.getElementById('count-total');
        if (totalEl) totalEl.textContent = total;
    }

    /* ══════════════════════════════════════════
       OPEN ATTENDANCE MODAL
    ══════════════════════════════════════════ */
    function openAttendanceModal() {
        modalDateLabel.textContent = selectedDate;
        modalBody.innerHTML = '';
        spinner.style.display = 'block';
        // Reset any previous modal-scope globals
        window.__initAdminPanel = null;
        window.__initAtmMap     = null;
        window._atmUpdateLocChip = null;
        $('#attendanceModal').modal('show');

        fetch(`{{ route('admin.attendance-details.fetchDetail') }}?user_id=${selectedUserId}&date=${selectedDate}`)
            .then(r => r.text())
            .then(html => {
                spinner.style.display = 'none';
                modalBody.innerHTML   = html;

                /* ── CRITICAL: execute the injected <script> blocks ── */
                runInjectedScripts(modalBody);

                /* ── Init admin panel logic (defined in partial script) ── */
                if (typeof window.__initAdminPanel === 'function') {
                    window.__initAdminPanel();
                }

                /* ── Init map (defined in partial script) ── */
                if (typeof window.__initAtmMap === 'function') {
                    window.__initAtmMap();
                }

                /* ── Inject location ── */
                if (window.currentLoc) {
                    injectLocation(window.currentLoc);
                } else {
                    getLocationWithAddress(loc => {
                        window.currentLoc = loc;
                        if (loc) injectLocation(loc);
                    });
                }

                /* ── Wire "Save Attendance" button ── */
                const openPwdBtn = modalBody.querySelector('#openPasswordModal');
                if (openPwdBtn) {
                    openPwdBtn.addEventListener('click', () => {
                        document.getElementById('masterPassword').value = '';
                        document.getElementById('pwdError').style.display = 'none';
                        $('#masterPasswordModal').modal('show');
                    });
                }
            })
            .catch(() => {
                spinner.style.display = 'none';
                modalBody.innerHTML = '<div class="p-4 text-danger">Failed to load attendance data.</div>';
            });
    }

    function injectLocation(loc) {
        const latEl  = modalBody.querySelector('#latitude');
        const lngEl  = modalBody.querySelector('#longitude');
        const addrEl = modalBody.querySelector('#fullAddress');
        if (latEl)  latEl.value  = loc.lat;
        if (lngEl)  lngEl.value  = loc.lng;
        if (addrEl) addrEl.value = loc.address;
        // Also update the chip in the admin panel if it exists
        if (typeof window._atmUpdateLocChip === 'function') {
            window._atmUpdateLocChip(loc);
        }
    }

    /* ══════════════════════════════════════════
       SAVE ATTENDANCE (after password confirm)
    ══════════════════════════════════════════ */
    document.getElementById('confirmSaveAttendance').addEventListener('click', function () {
        const pwd = document.getElementById('masterPassword').value.trim();
        if (!pwd) { showPwdError('Please enter master password.'); return; }

        /* ── Read form fields from modal ── */
        const statusEl       = modalBody.querySelector('#attendanceStatusSelect');
        const punchTypeEl    = modalBody.querySelector('#punchTypeHidden');   // computed hidden input
        const changedByEl    = modalBody.querySelector('#changedBy');
        const deviceUIDEl    = modalBody.querySelector('#deviceUID');
        const latEl          = modalBody.querySelector('#latitude');
        const lngEl          = modalBody.querySelector('#longitude');
        const addrEl         = modalBody.querySelector('#fullAddress');
        const piImg          = modalBody.querySelector('#punchInImage');
        const poImg          = modalBody.querySelector('#punchOutImage');
        const punchInTimeEl  = modalBody.querySelector('#punchInTimeInput');
        const punchOutTimeEl = modalBody.querySelector('#punchOutTimeInput');

        const punchType = punchTypeEl?.value ?? 'in';

        const fd = new FormData();
        fd.append('user_id',         selectedUserId);
        fd.append('date',            selectedDate);
        fd.append('status',          statusEl?.value ?? 'present');
        fd.append('punch_type',      punchType);
        fd.append('changed_by',      changedByEl?.value ?? '{{ auth()->user()->name }}');
        fd.append('device_uid',      deviceUIDEl?.value ?? getDeviceUID());
        fd.append('device_name',     navigator.userAgent);
        fd.append('master_password', pwd);

        /* ── Include selected punch times ── */
        if (punchType === 'in' || punchType === 'both') {
            if (punchInTimeEl?.value) fd.append('punch_in_time', punchInTimeEl.value);
            fd.append('punch_in_latitude',  latEl?.value  ?? '');
            fd.append('punch_in_longitude', lngEl?.value  ?? '');
            fd.append('punch_in_location',  addrEl?.value ?? '');
        }
        if (punchType === 'out' || punchType === 'both') {
            if (punchOutTimeEl?.value) fd.append('punch_out_time', punchOutTimeEl.value);
            fd.append('punch_out_latitude',  latEl?.value  ?? '');
            fd.append('punch_out_longitude', lngEl?.value  ?? '');
            fd.append('punch_out_location',  addrEl?.value ?? '');
        }

        if (piImg?.files[0]) fd.append('punch_in_image',  piImg.files[0]);
        if (poImg?.files[0]) fd.append('punch_out_image', poImg.files[0]);

        const btn = document.getElementById('confirmSaveAttendance');
        btn.disabled    = true;
        btn.textContent = 'Saving…';

        fetch(`{{ route('admin.attendance-details.updateStatus') }}`, {
            method : 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body   : fd,
        })
        .then(r => r.json())
        .then(res => {
            btn.disabled    = false;
            btn.textContent = '✔ Confirm & Save';

            if (res.success) {
                $('#masterPasswordModal').modal('hide');
                $('#attendanceModal').modal('hide');
                calendar.refetchEvents();
                showToast('Attendance saved successfully!', 'success');
            } else {
                const msg = typeof res.message === 'object'
                    ? Object.values(res.message).flat().join('\n')
                    : res.message;
                showPwdError(msg);
            }
        })
        .catch(() => {
            btn.disabled    = false;
            btn.textContent = '✔ Confirm & Save';
            showPwdError('Server error. Please try again.');
        });
    });

    function showPwdError(msg) {
        const el = document.getElementById('pwdError');
        el.textContent   = msg;
        el.style.display = 'block';
    }

    /* ══════════════════════════════════════════
       TOAST NOTIFICATION
    ══════════════════════════════════════════ */
    function showToast(msg, type = 'success') {
        const t = document.createElement('div');
        t.className     = `alert alert-${type} position-fixed shadow`;
        t.style.cssText = 'bottom:24px;right:24px;z-index:9999;min-width:260px;animation:fadeInUp .3s ease';
        t.textContent   = msg;
        document.body.appendChild(t);
        setTimeout(() => t.remove(), 3500);
    }

    /* ══════════════════════════════════════════
       USER SELECT (admin)
    ══════════════════════════════════════════ */
    document.getElementById('userSelect')?.addEventListener('change', function () {
        selectedUserId = this.value || '{{ auth()->id() }}';
        calendar.refetchEvents();
    });

    /* ══════════════════════════════════════════
       DEVICE UID (set on page load)
    ══════════════════════════════════════════ */
    document.querySelectorAll('#deviceUID').forEach(el => el.value = getDeviceUID());
    document.querySelectorAll('#ipAddress').forEach(el => el.value = '{{ request()->ip() }}');
    document.querySelectorAll('#deviceName').forEach(el => el.value = navigator.userAgent);
});
</script>
@endsection
