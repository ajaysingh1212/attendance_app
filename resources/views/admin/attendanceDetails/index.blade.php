@extends('layouts.admin')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<style>
    .calendar-wrapper {
        background: #ffffff;
        border-radius: 1rem;
        box-shadow: 0 0 20px rgba(0,0,0,0.08);
        padding: 20px;
    }
    .fc-toolbar-title { font-size: 1.5rem; font-weight: 600; color: #333; }
    .fc-daygrid-day-number { font-weight: bold; }
    .fc-event { border: none !important; border-radius: 5px !important; padding: 3px 6px !important; font-weight: 500; }
    .user-select { max-width: 300px; margin: 0 auto 20px; }
    .fc-event.present       { background-color: #28a745 !important; color: #fff !important; }
    .fc-event.absent        { background-color: #dc3545 !important; color: #fff !important; }
    .fc-event.leave         { background-color: #ffc107 !important; color: #000 !important; }
    .fc-event.half_time     { background-color: #17a2b8 !important; color: #fff !important; }
    .fc-event.week_off      { background-color: #6c757d !important; color: #fff !important; }
    .fc-event.holiday       { background-color: #6610f2 !important; color: #fff !important; }
    .fc-event.late          { background-color: #fd7e14 !important; color: #fff !important; }
    .fc-event.paid_leave    { background-color: #20c997 !important; color: #fff !important; }
    .fc-event.default       { background-color: #007bff !important; color: #fff !important; }
    .fc-daygrid-day.muted-day { background-color: #f8f9fa !important; opacity: 0.5; }

    .counter { font-size: 2rem; font-weight: 800; }
    .summary-box {
    position: relative;
    border-width: 3px;
    border-style: solid;
    border-radius: 15px;
    padding: 20px;
    width: 150px;
    text-align: center;
    background: rgba(255,255,255,0.7);
    backdrop-filter: blur(4px);
    overflow: hidden;
    cursor: pointer;
    transition: 0.3s ease;
    animation: fadeIn 0.8s ease;
}

/* Background Icon */
.summary-box::before {
    content: attr(data-icon);
    position: absolute;
    font-size: 70px;
    opacity: 0.08;
    right: 10px;
    bottom: -10px;
    pointer-events: none;
}

/* Hover Animation */
.summary-box:hover {
    transform: scale(1.06);
    box-shadow: 0 8px 18px rgba(0,0,0,0.15);
}

/* Text */
.summary-box h5 {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 5px;
}

.summary-box .counter {
    font-size: 26px;
    font-weight: bold;
}

/* Custom Purple Border */
.border-purple {
    border-color: #6610f2 !important;
}

/* Smooth Entry Animation */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}

</style>
@endsection

@section('content')
<div class="container py-4">
    <h2 class="mb-4 text-center">Attendance Calendar</h2>

    <div class="col-lg-12 mb-3">
        <a class="btn btn-success" href="{{ route('admin.attendance-details.create') }}">
            {{ trans('global.add') }} {{ trans('cruds.attendanceDetail.title_singular') }}
        </a>
        <button class="btn btn-warning" data-toggle="modal" data-target="#csvImportModal">
            {{ trans('global.app_csvImport') }}
        </button>
        @include('csvImport.modal', ['model' => 'AttendanceDetail', 'route' => 'admin.attendance-details.parseCsvImport'])
    </div>

    @if(auth()->user()->is_admin)
    <div class="user-select mb-3 text-center">
        <select class="form-select form-control" id="userSelect">
            <option value="">🔽 Select Employee</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
            @endforeach
        </select>
    </div>
    @endif
<!-- ================= SUMMARY CARDS ================= -->
<div id="attendanceSummary" class="d-flex gap-3 mb-4 flex-wrap" style="display:none;">

    <div class="summary-box border-success" data-icon="✔">
        <h5>Present</h5>
        <div id="count-present" class="counter">0</div>
    </div>

    <div class="summary-box border-danger" data-icon="✖">
        <h5>Absent</h5>
        <div id="count-absent" class="counter">0</div>
    </div>

    <div class="summary-box border-info" data-icon="½">
        <h5>Half Time</h5>
        <div id="count-half_time" class="counter">0</div>
    </div>

    <div class="summary-box border-warning" data-icon="☕">
        <h5>Leave</h5>
        <div id="count-leave" class="counter">0</div>
    </div>

    <div class="summary-box border-secondary" data-icon="🛏">
        <h5>Week Off</h5>
        <div id="count-week_off" class="counter">0</div>
    </div>

    <div class="summary-box border-purple" data-icon="🎉">
        <h5>Holiday</h5>
        <div id="count-holiday" class="counter">0</div>
    </div>

    <div class="summary-box border-dark flex-grow-1" data-icon="Σ">
        <h5>Total</h5>
        <div id="count-total" class="counter">0</div>
    </div>

</div>


<!-- ================= CALENDAR AREA ================= -->
<div class="calendar-wrapper mt-4">
    <div id="calendar"></div>
</div>

<!-- Holiday Modal -->
<div class="modal fade" id="holidayModal" tabindex="-1" role="dialog" aria-labelledby="holidayModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="holidayModalLabel">Holiday Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>Type:</strong> <span id="modalHolidayType">-</span></p>
                <p><strong>Optional:</strong> <span id="modalIsOptional">-</span></p>
                <p><strong>National:</strong> <span id="modalIsNational">-</span></p>
                <p><strong>Description:</strong> <span id="modalDescription">-</span></p>
            </div>
        </div>
    </div>
</div>

<!-- ================= ATTENDANCE MODAL ================= -->
<div class="modal fade" id="attendanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg rounded-4 border-0">

            <!-- ================= HEADER ================= -->
            <div class="modal-header bg-info text-white rounded-top-4">
                <h5 class="modal-title fw-bold">📅 Attendance Management</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            @if(auth()->user()->is_admin)
            <div class="modal-body">

                <!-- ================= AUDIT INFO ================= -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header fw-bold bg-light">🔐 Audit Information</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label>Changed By</label>
                                <input class="form-control" id="changedBy"
                                    value="{{ auth()->user()->name }}" readonly>
                            </div>
                            <div class="col-md-3">
                                <label>IP Address</label>
                                <input class="form-control" id="ipAddress"
                                    value="{{ request()->ip() }}" readonly>
                            </div>
                            <div class="col-md-3">
                                <label>Device</label>
                                <input class="form-control" id="deviceName" readonly>
                            </div>
                            <div class="col-md-3">
                                <label>Device UUID</label>
                                <input class="form-control" id="deviceUID" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= WORK TIME ================= -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header fw-bold bg-light">⏱ Work Timing</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label>Work Start Time</label>
                                <input class="form-control bg-light"
                                    id="workStartTime"
                                    value="{{ $work_start_time ?? '-' }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label>Work End Time</label>
                                <input class="form-control bg-light"
                                    id="workEndTime"
                                    value="{{ $work_end_time ?? '-' }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= LOCATION ================= -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header fw-bold bg-light">📍 Location Details</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label>Latitude</label>
                                <input class="form-control" id="latitude"
                                    value="{{ $punchInLatitude ?? '' }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label>Longitude</label>
                                <input class="form-control" id="longitude"
                                    value="{{ $punchInLongitude ?? '' }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label>Full Address</label>
                                <input class="form-control" id="fullAddress"
                                    value="{{ $punchInLocation ?? '' }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= ATTENDANCE ACTION ================= -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header fw-bold bg-light">✅ Attendance Action</div>
                    <div class="card-body">

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label>Attendance Status</label>
                                <select id="attendanceStatusSelect" class="form-control">
                                    @foreach(App\Models\AttendanceDetail::STATUS_SELECT as $k => $v)
                                        <option value="{{ $k }}"
                                            @selected(($attendanceDetail->status ?? null) === $k)
                                        >
                                            {{ $v }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label>Punch Type</label>
                                <select id="punchTypeSelect" class="form-control">
                                    <option value="in">Punch In</option>
                                    <option value="out">Punch Out</option>
                                    <option value="both">Punch In & Punch Out</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label>Punch In Image</label>
                                <input type="file" id="punchInImage"
                                    class="form-control" accept="image/*">
                            </div>
                            <div class="col-md-6">
                                <label>Punch Out Image</label>
                                <input type="file" id="punchOutImage"
                                    class="form-control" accept="image/*">
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ================= ACTION BUTTON ================= -->
                <div class="text-end">
                    <button id="openPasswordModal" class="btn btn-success px-4 py-2">
                        💾 Save Attendance
                    </button>
                </div>
                @php
                    $hasPunchIn  = $hasPunchIn  ?? false;
                    $hasPunchOut = $hasPunchOut ?? false;
                @endphp

                <!-- ================= FLAGS FOR JS LOGIC ================= -->
                <input type="hidden" id="hasPunchIn"
                    value="{{ $hasPunchIn ? 1 : 0 }}">
                <input type="hidden" id="hasPunchOut"
                    value="{{ $hasPunchOut ? 1 : 0 }}">

            </div>
            @endif

        </div>
    </div>
</div>



<!-- Master Password Modal -->
<div class="modal fade" id="masterPasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Confirm Master Password</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <label><strong>Master Password</strong></label>
                <input type="password" id="masterPassword" class="form-control" placeholder="Enter master password">
            </div>

            <div class="modal-footer">
                <button id="confirmSaveAttendance" class="btn btn-success">
                    Confirm & Save
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
/* ================= DEVICE UID ================= */
function getDeviceUID() {
    let uid = localStorage.getItem('device_uid');
    if (!uid) {
        uid = crypto.randomUUID();
        localStorage.setItem('device_uid', uid);
    }
    return uid;
}

/* ================= LOCATION + ADDRESS ================= */
function getLocationWithAddress(callback) {
    if (!navigator.geolocation) {
        callback(null);
        return;
    }

    navigator.geolocation.getCurrentPosition(
        pos => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;

            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ location: { lat, lng } }, (results, status) => {
                callback({
                    lat,
                    lng,
                    address: (status === 'OK' && results[0]) ? results[0].formatted_address : ''
                });
            });
        },
        () => callback(null),
        { enableHighAccuracy: true }
    );
}

document.addEventListener('DOMContentLoaded', function () {

    /* ================= ELEMENTS ================= */
    const attendanceModalBody = document.getElementById('attendanceModalBody');

    const deviceUIDEl = document.getElementById('deviceUID');
    const latitudeEl = document.getElementById('latitude');
    const longitudeEl = document.getElementById('longitude');
    const fullAddressEl = document.getElementById('fullAddress');

    const workStartEl = document.getElementById('workStartTime');
    const workEndEl = document.getElementById('workEndTime');

    const punchTypeEl = document.getElementById('punchTypeSelect');
    const statusEl = document.getElementById('attendanceStatusSelect');

    /* ================= STATIC INFO ================= */
    document.getElementById('ipAddress').value = '{{ request()->ip() }}';
    document.getElementById('deviceName').value = navigator.userAgent;
    deviceUIDEl.value = getDeviceUID();

    /* ================= CALENDAR ================= */
    let selectedUserId = '{{ auth()->id() }}';
    let selectedDate = null;

    const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'dayGridMonth',
        height: 650,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        events: function (fetchInfo, successCallback) {
            fetch(`{{ route('admin.attendance-details.calendarData', ['user' => '__USER_ID__']) }}`
                .replace('__USER_ID__', selectedUserId))
                .then(res => res.json())
                .then(successCallback);
        },
        eventClick(info) {
            selectedDate = info.event.startStr;
            loadAttendanceDetail();
        }
    });

    calendar.render();

    /* ================= LOAD MODAL DATA ================= */
    function loadAttendanceDetail() {

        // 1️⃣ Fetch attendance partial (HTML)
        fetch(`admin/attendance-details/fetch-detail?user_id=${selectedUserId}&date=${selectedDate}`)
            .then(res => res.text())
            .then(html => {
                attendanceModalBody.innerHTML = html;
                $('#attendanceModal').modal('show');
            });

        // 2️⃣ Fetch employee work timing
        fetch(`admin/employees/by-user/${selectedUserId}`)
            .then(res => res.json())
            .then(emp => {
                workStartEl.value = emp.work_start_time ?? '-';
                workEndEl.value = emp.work_end_time ?? '-';
            });

        // 3️⃣ Get current location
        getLocationWithAddress(loc => {
            if (!loc) return;
            latitudeEl.value = loc.lat;
            longitudeEl.value = loc.lng;
            fullAddressEl.value = loc.address;
        });

        // 4️⃣ Reset punch logic
        punchTypeEl.value = 'in';
    }

    /* ================= PUNCH LOGIC ================= */
    punchTypeEl.addEventListener('change', () => {

        const hasPunchIn = attendanceModalBody.querySelector('[data-has-punch-in]')?.value === '1';

        if (punchTypeEl.value === 'out' && !hasPunchIn) {
            alert('Punch In required before Punch Out');
            punchTypeEl.value = 'in';
        }
    });

    /* ================= SAVE ATTENDANCE ================= */
    document.getElementById('confirmSaveAttendance').addEventListener('click', () => {

        const masterPwd = document.getElementById('masterPassword').value;
        if (!masterPwd) return alert('Master password required');

        const fd = new FormData();
        fd.append('user_id', selectedUserId);
        fd.append('date', selectedDate);
        fd.append('status', statusEl.value);
        fd.append('punch_type', punchTypeEl.value);
        fd.append('changed_by', document.getElementById('changedBy').value);
        fd.append('device_uid', deviceUIDEl.value);
        fd.append('master_password', masterPwd);

        fd.append('latitude', latitudeEl.value);
        fd.append('longitude', longitudeEl.value);
        fd.append('full_address', fullAddressEl.value);

        if (document.getElementById('punchInImage').files[0]) {
            fd.append('punch_in_image', document.getElementById('punchInImage').files[0]);
        }

        if (document.getElementById('punchOutImage').files[0]) {
            fd.append('punch_out_image', document.getElementById('punchOutImage').files[0]);
        }

        fetch(`{{ route('admin.attendance-details.updateStatus') }}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: fd
        })
        .then(res => res.json())
        .then(r => {
            alert(r.message);
            if (r.success) {
                $('#masterPasswordModal').modal('hide');
                $('#attendanceModal').modal('hide');
                calendar.refetchEvents();
            }
        });
    });

    /* ================= USER FILTER ================= */
    document.getElementById('userSelect')?.addEventListener('change', function () {
        selectedUserId = this.value;
        calendar.refetchEvents();
    });

});
</script>


@endsection
