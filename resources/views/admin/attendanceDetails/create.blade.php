@extends('layouts.admin')

@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    use Carbon\Carbon;
    use App\Models\Employee;
    use App\Models\AttendanceDetail;
    use App\Models\Branch;
    use App\Models\Role;

    $authUser = auth()->user();
    $adminTitle = Role::where('title', 'admin')->pluck('title')->first();
    $isAdmin = $authUser->roles()->pluck('title')->contains($adminTitle);
    $punch = null; // Initialize $punch globally to avoid undefined errors
@endphp

@if($isAdmin)

    @php
        $employees = Employee::with('user')->get();
        $statuses = ['present', 'absent', 'half_time', 'leave', 'weekoff', 'holiday', 'late', 'paid_leave'];
    @endphp

    <div class="card">
        <div class="card-header">📋 Manual Attendance Entry (Admin)</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.attendance-details.store') }}" enctype="multipart/form-data" id="manual_attendance_form">
                @csrf
                <input type="hidden" name="type" value="manual">

                <div class="form-group">
                    <label for="user_id">👤 Select Employee</label>
                    <select name="user_id" id="user_id" class="form-control" required>
                        <option value="">-- Select Employee --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->user_id }}">{{ $emp->full_name }} ({{ $emp->employee_code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="attendance_date">📅 Attendance Date</label>
                    <input type="date" name="attendance_date" id="attendance_date" class="form-control" required>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label>Punch In Time</label>
                        <input type="datetime-local" name="punch_in_time" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label>Punch Out Time</label>
                        <input type="datetime-local" name="punch_out_time" class="form-control">
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12 mb-3">
                        <div id="adminMap" style="height: 400px; width: 100%; border: 1px solid #ccc;"></div>
                        <small>🗺️ Click on map to set Punch In and Punch Out location.</small>
                    </div>

                    <div class="col-md-6">
                        <label>Punch In Location</label>
                        <input type="text" name="punch_in_location" id="punch_in_location" class="form-control mb-1" readonly>
                        <input type="hidden" name="punch_in_latitude" id="punch_in_latitude" readonly>
                        <input type="hidden" name="punch_in_longitude" id="punch_in_longitude" readonly>
                    </div>

                    <div class="col-md-6">
                        <label>Punch Out Location</label>
                        <input type="text" name="punch_out_location" id="punch_out_location" class="form-control mb-1" readonly>
                        <input type="hidden" name="punch_out_latitude" id="punch_out_latitude" readonly>
                        <input type="hidden" name="punch_out_longitude" id="punch_out_longitude" readonly>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <label for="status">📌 Attendance Status</label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="">-- Select Status --</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group text-center mt-4">
                    <button type="submit" class="btn btn-primary px-4">💾 Submit Attendance</button>
                </div>
            </form>
        </div>
    </div>

@else
    @php
        $employee = Employee::where('user_id', $authUser->id)->first();
        $branch = $employee && $employee->branch_id !== 'anywhere' ? Branch::find($employee->branch_id) : null;
        $today = now()->format('Y-m-d');
        $punch = AttendanceDetail::whereDate('punch_in_time', $today)
                    ->where('user_id', $authUser->id)
                    ->latest()->first();
        $hasPunchedIn = $punch && $punch->punch_in_time;
        $hasPunchedOut = $punch && $punch->punch_out_time;
        $isAnywhere = $employee && $employee->branch_id === 'anywhere';
    @endphp

    <div class="card">
        <div class="card-header">🕒 Attendance Punch</div>
        <div class="card-body">
            @if($employee)
                <div class="mb-3">
                    <strong>👤 Employee Code:</strong> {{ $employee->employee_code }} <br>
                    <strong>🏢 Branch:</strong> {{ $branch->title ?? 'Anywhere' }} <br>
                    <strong>📍 Radius Allowed:</strong> {{ $employee->attendance_radius_meter ?? 100 }} meters
                </div>
            @endif

            @if(!$hasPunchedIn)
                {{-- Punch In Form --}}
                <form method="POST" action="{{ route('admin.attendance-details.store') }}" enctype="multipart/form-data" id="punch_in_form">
                    @csrf
                    <input type="hidden" name="type" value="self">
                    <input type="hidden" name="user_id" value="{{ auth()->id() }}">

                    <h5 class="mb-3">🟢 Step 1: Punch In</h5>

                    <div class="form-group">
                        <label>Punch In Time</label>
                        <input type="datetime-local" name="punch_in_time" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" readonly required>
                    </div>

                    <div class="form-group">
                        <label>Punch In Image</label>
                        <input type="file" name="punch_in_image" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Punch In Location</label>
                        <input type="text" name="punch_in_location" id="punch_in_location" class="form-control" readonly>
                        <input type="hidden" name="punch_in_latitude" id="punch_in_latitude">
                        <input type="hidden" name="punch_in_longitude" id="punch_in_longitude">
                    </div>

                    <div class="form-group text-center">
                        <button type="submit" class="btn btn-success px-4" id="punch_in_submit">✅ Punch In</button>
                    </div>
                </form>

            @elseif($hasPunchedIn && !$hasPunchedOut)
                {{-- Punch Out Form --}}
                <form method="POST" action="{{ route('admin.attendance-details.store') }}" enctype="multipart/form-data" id="punch_out_form">
                    @csrf
                    <input type="hidden" name="type" value="punch_out">
                    <input type="hidden" name="user_id" value="{{ auth()->id() }}">

                    <h5 class="mb-3">🔴 Step 2: Punch Out</h5>

                    <div class="form-group">
                        <label>Punch Out Time</label>
                        <input type="datetime-local" name="punch_out_time" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" readonly required>
                    </div>

                    <div class="form-group">
                        <label>Punch Out Image</label>
                        <input type="file" name="punch_out_image" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Punch Out Location</label>
                        <input type="text" name="punch_out_location" id="punch_out_location" class="form-control" readonly>
                        <input type="hidden" name="punch_out_latitude" id="punch_out_latitude">
                        <input type="hidden" name="punch_out_longitude" id="punch_out_longitude">
                    </div>

                    <div class="form-group text-center">
                        <button type="submit" class="btn btn-danger px-4" id="punch_out_submit">🔚 Punch Out</button>
                    </div>
                </form>

                {{-- Live Timer --}}
                <div class="card mt-3">
                    <div class="card-body text-center">
                        <h5>🕒 Working Since: {{ \Carbon\Carbon::parse($punch->punch_in_time)->format('h:i A') }}</h5>
                        
                        <!-- Analog Clock -->
                        <div id="analogClock" class="d-flex justify-content-center align-items-center">
                            <svg width="200" height="200" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="48" stroke="#333" stroke-width="2" fill="#f5f5f5" />
                                
                                <!-- Hour hand -->
                                <line id="hour" x1="50" y1="50" x2="50" y2="30" stroke="#333" stroke-width="3" stroke-linecap="round" />
                                
                                <!-- Minute hand -->
                                <line id="minute" x1="50" y1="50" x2="50" y2="20" stroke="#666" stroke-width="2" stroke-linecap="round" />
                                
                                <!-- Second hand -->
                                <line id="second" x1="50" y1="50" x2="50" y2="15" stroke="red" stroke-width="1" stroke-linecap="round" />
                                
                                <!-- Center circle -->
                                <circle cx="50" cy="50" r="1.5" fill="#000" />
                            </svg>
                        </div>
                    </div>
                </div>
                <script>
                    function updateAnalogClock() {
                        const now = new Date();

                        const sec = now.getSeconds();
                        const min = now.getMinutes();
                        const hr = now.getHours() % 12;

                        const secDeg = sec * 6;
                        const minDeg = min * 6 + sec * 0.1;
                        const hrDeg = hr * 30 + min * 0.5;

                        document.getElementById("second").setAttribute("transform", `rotate(${secDeg} 50 50)`);
                        document.getElementById("minute").setAttribute("transform", `rotate(${minDeg} 50 50)`);
                        document.getElementById("hour").setAttribute("transform", `rotate(${hrDeg} 50 50)`);
                    }

                    setInterval(updateAnalogClock, 1000);
                    updateAnalogClock();
                </script>
                <style>
                    #analogClock svg {
                        background: #ffffff;
                        border-radius: 50%;
                        box-shadow: 0 0 10px rgba(0,0,0,0.1);
                    }
                </style>

                

            @elseif($hasPunchedOut)
                {{-- Total Worked Duration --}}
                <div class="container text-center mt-4">
                    <div class="alert alert-info">
                        <h5>✅ Today's Work Completed</h5>
                        <p>
                            <strong>Punch In:</strong> {{ \Carbon\Carbon::parse($punch->punch_in_time)->format('h:i A') }} <br>
                            <strong>Punch Out:</strong> {{ \Carbon\Carbon::parse($punch->punch_out_time)->format('h:i A') }} <br>
                            <strong>Total Duration:</strong>
                            {{ \Carbon\Carbon::parse($punch->punch_in_time)->diff(\Carbon\Carbon::parse($punch->punch_out_time))->format('%H:%I:%S') }}
                        </p>
                    </div>

                    {{-- Live Analog Clock --}}
                    <div class="analog-clock-wrapper d-flex justify-content-center mt-4">
                        <svg class="analog-clock" viewBox="0 0 100 100">
                            <!-- Clock face -->
                            <circle cx="50" cy="50" r="48" fill="#fff" stroke="#333" stroke-width="2"/>
                            
                            <!-- Hour marks -->
                            @for ($i = 0; $i < 12; $i++)
                                <line x1="50" y1="5" x2="50" y2="10" stroke="#000" stroke-width="2"
                                    transform="rotate({{ $i * 30 }} 50 50)" />
                            @endfor

                            <!-- Hour hand -->
                            <line id="hour" x1="50" y1="50" x2="50" y2="28" stroke="#000" stroke-width="3" stroke-linecap="round"/>

                            <!-- Minute hand -->
                            <line id="minute" x1="50" y1="50" x2="50" y2="18" stroke="#000" stroke-width="2" stroke-linecap="round"/>

                            <!-- Second hand -->
                            <line id="second" x1="50" y1="50" x2="50" y2="12" stroke="red" stroke-width="1" stroke-linecap="round"/>

                            <!-- Center dot -->
                            <circle cx="50" cy="50" r="2" fill="#000"/>
                        </svg>
                    </div>
                </div>

                <script>
                    function updateClock() {
                        const now = new Date();
                        const second = now.getSeconds();
                        const minute = now.getMinutes();
                        const hour = now.getHours() % 12;

                        const secondDeg = second * 6; // 360 / 60
                        const minuteDeg = (minute + second / 60) * 6;
                        const hourDeg = (hour + minute / 60) * 30;

                        document.getElementById('second').setAttribute('transform', `rotate(${secondDeg} 50 50)`);
                        document.getElementById('minute').setAttribute('transform', `rotate(${minuteDeg} 50 50)`);
                        document.getElementById('hour').setAttribute('transform', `rotate(${hourDeg} 50 50)`);
                    }

                    setInterval(updateClock, 1000);
                    updateClock(); // initial call
                </script>

                <style>
                    .analog-clock {
                        width: 200px;
                        height: 200px;
                    }
                </style>

            @endif
        </div>
    </div>
@endif
@endsection

@section('scripts')
@if($isAdmin)
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBgRXfXiK8KHfSnKtunSIpGpKNmLNGNUzM&libraries=places"></script>
<script>
    let map;
    let punchInMarker = null;
    let punchOutMarker = null;

    function initAdminMap() {
        map = new google.maps.Map(document.getElementById("adminMap"), {
            center: { lat: 22.9734, lng: 78.6569 },
            zoom: 5,
        });

        map.addListener("click", function (event) {
            const lat = event.latLng.lat();
            const lng = event.latLng.lng();

            const geocoder = new google.maps.Geocoder();

            geocoder.geocode({ location: { lat, lng } }, function (results, status) {
                if (status === "OK" && results[0]) {
                    const address = results[0].formatted_address;

                    // Alternate punches
                    if (!punchInMarker) {
                        punchInMarker = new google.maps.Marker({
                            position: { lat, lng },
                            map: map,
                            label: "In",
                        });

                        document.getElementById("punch_in_latitude").value = lat;
                        document.getElementById("punch_in_longitude").value = lng;
                        document.getElementById("punch_in_location").value = address;
                    } else if (!punchOutMarker) {
                        punchOutMarker = new google.maps.Marker({
                            position: { lat, lng },
                            map: map,
                            label: "Out",
                        });

                        document.getElementById("punch_out_latitude").value = lat;
                        document.getElementById("punch_out_longitude").value = lng;
                        document.getElementById("punch_out_location").value = address;
                    }
                } else {
                    alert("Geocoder failed due to: " + status);
                }
            });
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        initAdminMap();
    });
</script>
@else
<script>
    const isAnywhere = @json($isAnywhere);
    const radius = {{ $employee->attendance_radius_meter ?? 100 }};
    const branchLat = {{ $branch->latitude ?? 'null' }};
    const branchLng = {{ $branch->longitude ?? 'null' }};

    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371e3;
        const φ1 = lat1 * Math.PI / 180;
        const φ2 = lat2 * Math.PI / 180;
        const Δφ = (lat2 - lat1) * Math.PI / 180;
        const Δλ = (lon2 - lon1) * Math.PI / 180;

        const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
            Math.cos(φ1) * Math.cos(φ2) *
            Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

        return R * c;
    }

    function getLocation(type) {
        const prefix = type === 'punch_in' ? 'punch_in' : 'punch_out';
        const submitBtn = document.getElementById(`${prefix}_submit`);
        submitBtn.disabled = true;

        if (!navigator.geolocation) {
            alert("❌ Geolocation is not supported by this browser.");
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function (position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                document.getElementById(`${prefix}_latitude`).value = lat;
                document.getElementById(`${prefix}_longitude`).value = lng;

                if (!isAnywhere && branchLat !== null && branchLng !== null) {
                    const dist = calculateDistance(lat, lng, branchLat, branchLng);
                    if (dist > radius) {
                        alert(`⚠️ You are outside the allowed radius (${dist.toFixed(2)}m > ${radius}m).`);
                        submitBtn.disabled = true;
                        return;
                    }
                }

                fetch(`https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&key=AIzaSyBgRXfXiK8KHfSnKtunSIpGpKNmLNGNUzM`)
                    .then(res => res.json())
                    .then(data => {
                        const location = data.results?.[0]?.formatted_address || 'Location not found';
                        document.getElementById(`${prefix}_location`).value = location;
                        submitBtn.disabled = false;
                    })
                    .catch(() => {
                        document.getElementById(`${prefix}_location`).value = 'Location fetch failed';
                        submitBtn.disabled = false;
                    });
            },
            function () {
                alert("❌ Location access denied.");
                submitBtn.disabled = false;
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }

    document.addEventListener('DOMContentLoaded', () => {
        @if(!$hasPunchedIn)
            getLocation('punch_in');
        @else
            getLocation('punch_out');
        @endif
    });
</script>

@if($punch && !$punch->punch_out_time)
<script>
    const punchInTime = new Date("{{ \Carbon\Carbon::parse($punch->punch_in_time)->format('Y-m-d H:i:s') }}");

    function updateTimer() {
        const now = new Date();
        const diff = now - punchInTime;

        const hours = String(Math.floor(diff / 3600000)).padStart(2, '0');
        const minutes = String(Math.floor((diff % 3600000) / 60000)).padStart(2, '0');
        const seconds = String(Math.floor((diff % 60000) / 1000)).padStart(2, '0');

        document.getElementById('work_timer').innerText = `${hours}:${minutes}:${seconds}`;
    }

    setInterval(updateTimer, 1000);
</script>
@endif
@endif
@endsection
