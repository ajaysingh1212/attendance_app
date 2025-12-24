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

<!-- Attendance Modal -->
<div class="modal fade" id="attendanceModal" tabindex="-1" role="dialog" aria-labelledby="attendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="attendanceModalLabel">Attendance Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            @if(auth()->user()->is_admin)
            <div class="modal-footer">
                <div class="form-inline w-100 justify-content-between">
                    <select id="attendanceStatus" class="form-control" style="width: 200px;">
                        <option value="">-- Change Status --</option>
                        <option value="present">Present</option>
                        <option value="absent">Absent</option>
                        <option value="half_time">Half Time</option>
                        <option value="leave">Leave</option>
                        <option value="week_off">Week Off</option>
                        <option value="holiday">Holiday</option>
                        <option value="paid_leave">Paid Leave</option>
                        <option value="late">Late</option>
                    </select>
                    <button id="saveStatusBtn" class="btn btn-success">Save</button>
                </div>
            </div>
            @endif

            <div class="modal-body" id="attendanceModalBody">
                <p>Loading...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/google-calendar@5.11.3/main.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBgRXfXiK8KHfSnKtunSIpGpKNmLNGNUzM&libraries=places"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    let selectedUserId = '{{ auth()->id() }}';
    let selectedDate = null;

    // animate counter helper
    function animateCounter(el, val) {
        let c = 0;
        let end = parseInt(val) || 0;
        el.textContent = '0';
        if (end <= 0) return;
        let st = setInterval(() => {
            c++;
            if (c >= end) { c = end; clearInterval(st); }
            el.textContent = c;
        }, 20);
    }

    // summary calculation
  function updateSummary(events) {

    if (typeof calendar === 'undefined' || !calendar.view) return;

    let view = calendar.view;
    let monthStart = view.currentStart;
    let monthEnd = view.currentEnd;

    // summary counters
    let s = { present:0, absent:0, half_time:0, leave:0, week_off:0, holiday:0 };

    // map of holiday dates to avoid absent on holiday
    let holidayDates = {};

    events.forEach(e => {
        const start = e.start || e.startStr;
        if (!start) return;
        let d = new Date(start);
        if (d >= monthStart && d < monthEnd) {
            
            let type = (e.classNames && e.classNames[0]) ? e.classNames[0] : 'default';

            // mark holiday date
            if (type === 'holiday') {
                holidayDates[start] = true;
            }
        }
    });

    // second pass — count events but skip absent when holiday exists
    events.forEach(e => {
        const start = e.start || e.startStr;
        if (!start) return;
        let d = new Date(start);

        if (d >= monthStart && d < monthEnd) {

            let type = (e.classNames && e.classNames[0]) ? e.classNames[0] : 'default';

            // holiday exists → skip absent
            if (holidayDates[start] && type === 'absent') {
                return;
            }

            if (s[type] !== undefined) s[type]++;
        }
    });

    const total = s.present + s.absent + s.half_time + s.leave + s.week_off + s.holiday;

    animateCounter(document.getElementById("count-present"), s.present);
    animateCounter(document.getElementById("count-absent"), s.absent);
    animateCounter(document.getElementById("count-half_time"), s.half_time);
    animateCounter(document.getElementById("count-leave"), s.leave);
    animateCounter(document.getElementById("count-week_off"), s.week_off);
    animateCounter(document.getElementById("count-holiday"), s.holiday);
    animateCounter(document.getElementById("count-total"), total);

    const summaryEl = document.getElementById("attendanceSummary");
    if (summaryEl) summaryEl.style.display = "flex";
}


    // initialize calendar
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 650,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        displayOtherMonths: false,
        showNonCurrentDates: false,
        events: function(fetchInfo, successCallback, failureCallback) {
            const url = `{{ route('admin.attendance-details.calendarData', ['user' => '__USER_ID__']) }}`.replace('__USER_ID__', selectedUserId);
            fetch(url)
                .then(res => res.json())
                .then(events => {
                    successCallback(events);
                    // update summary whenever events load
                    try { updateSummary(events); } catch (e) { console.error("updateSummary error", e); }
                })
                .catch(err => {
                    console.error("Events fetch error", err);
                    failureCallback(err);
                });
        },
        datesSet: function() {
            // when month changes, refetch events (and summary will update on load)
            calendar.refetchEvents();
        },
        eventClick: function(info) {
            selectedDate = info.event.startStr;
            if (info.event.classNames.includes('holiday')) {
                const props = info.event.extendedProps;
                document.getElementById('modalHolidayType').textContent = props.holiday_type || 'N/A';
                document.getElementById('modalIsOptional').textContent = props.is_optional ? 'Yes' : 'No';
                document.getElementById('modalIsNational').textContent = props.is_national ? 'Yes' : 'No';
                document.getElementById('modalDescription').textContent = props.description || 'N/A';
                $('#holidayModal').modal('show');
            } else {
                const url = `admin/attendance-details/fetch-detail?user_id=${selectedUserId}&date=${selectedDate}`;
                fetch(url)
                    .then(res => res.text())
                    .then(data => {
                        $('#attendanceModalBody').html(data);
                        $('#attendanceModal').modal('show');
                    })
                    .catch(err => console.error("Attendance detail fetch error", err));
            }
        }
    });

    calendar.render();

    // save status button
    document.getElementById('saveStatusBtn')?.addEventListener('click', function () {
        const newStatus = document.getElementById('attendanceStatus').value;
        if (!newStatus) { alert("⚠ Please select a status first!"); return; }
        if (!selectedDate) { alert("⚠ Please select a date first!"); return; }

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                // reverse geocode if google available
                function sendPayload(address) {
                    const payload = {
                        user_id: selectedUserId,
                        date: selectedDate,
                        status: newStatus,
                        punch_in_latitude: lat,
                        punch_in_longitude: lng,
                        punch_out_latitude: lat,
                        punch_out_longitude: lng,
                        punch_in_location: address,
                        punch_out_location: address
                    };

                    fetch(`{{ route('admin.attendance-details.updateStatus') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(async res => {
                        const text = await res.text();
                        try {
                            const response = JSON.parse(text);
                            if (response.success) {
                                alert('✅ Attendance status updated successfully!');
                                $('#attendanceModal').modal('hide');
                                calendar.refetchEvents();
                            } else {
                                alert('❌ Failed to update attendance: ' + (response.message || 'Unknown error'));
                            }
                        } catch (e) {
                            console.error("JSON parse error:", e, text);
                            alert("⚠ Server did not return valid JSON. Check console/logs.");
                        }
                    })
                    .catch(err => { console.error("Fetch error:", err); alert('⚠ Something went wrong! Check console.'); });
                }

                if (window.google && google.maps && google.maps.Geocoder) {
                    const geocoder = new google.maps.Geocoder();
                    geocoder.geocode({ location: { lat: lat, lng: lng } }, (results, status) => {
                        if (status === "OK" && results[0]) {
                            sendPayload(results[0].formatted_address);
                        } else {
                            sendPayload("Unknown Location");
                        }
                    });
                } else {
                    // google maps not available — send without resolved address
                    sendPayload("Unknown Location");
                }

            }, function(err) {
                console.error("Geolocation error:", err);
                alert('⚠ Unable to get your location. Attendance update failed.');
            });
        } else {
            alert('⚠ Geolocation is not supported by your browser.');
        }
    });

    // user filter
    const userSelect = document.getElementById('userSelect');
    if (userSelect) {
        userSelect.addEventListener('change', function () {
            selectedUserId = this.value;
            calendar.refetchEvents();
        });
    }

});
</script>
@endsection
