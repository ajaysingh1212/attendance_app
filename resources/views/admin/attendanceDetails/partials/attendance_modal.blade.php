<div class="container p-3">

    {{-- Leave Request Section --}}
    @if($leaveRequest)
        <div class="card mb-4 border-warning shadow-sm">
            <div class="card-header bg-warning text-dark fw-semibold">
                <i class="fas fa-calendar-times me-1"></i> Leave Request
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-sm-4"><strong>Title:</strong></div>
                    <div class="col-sm-8">{{ $leaveRequest->title }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-4"><strong>Leave Type:</strong></div>
                    <div class="col-sm-8">
                        <span class="badge bg-secondary">{{ $leaveRequest->leaveType->name ?? 'N/A' }}</span>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-4"><strong>Description:</strong></div>
                    <div class="col-sm-8">{{ $leaveRequest->description }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-4"><strong>Date From:</strong></div>
                    <div class="col-sm-8">{{ $leaveRequest->date_from }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-4"><strong>Date To:</strong></div>
                    <div class="col-sm-8">{{ $leaveRequest->date_to }}</div>
                </div>
                <div class="row">
                    <div class="col-sm-4"><strong>Remark:</strong></div>
                    <div class="col-sm-8">{{ $leaveRequest->remark }}</div>
                </div>
            </div>
        </div>
    @endif

  {{-- Attendance Record Section --}}
@if($attendanceDetail)
    <div class="card mb-4 border-info shadow-sm">
        <div class="card-header bg-info text-white fw-semibold">
            <i class="fas fa-user-check me-1"></i> Attendance Record
        </div>
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-sm-4"><strong>Status:</strong></div>
                <div class="col-sm-8">{{ ucfirst($attendanceDetail->status) }}</div>
            </div>

            <div class="row mb-2">
                <div class="col-sm-4"><strong>Note:</strong></div>
                <div class="col-sm-8">{{ $attendanceDetail->note ?? '-' }}</div>
            </div>

            <div class="row mb-2">
                <div class="col-sm-4"><strong>Date:</strong></div>
                <div class="col-sm-8">{{ $attendanceDetail->date }}</div>
            </div>

            <div class="row mb-2">
                <div class="col-sm-4"><strong>Punch In Image:</strong></div>
                <div class="col-sm-8">
                   @if($attendanceDetail->punch_in_image)
    @php
        $mime = $attendanceDetail->punch_in_image->mime_type ?? '';
    @endphp

    @if(Str::startsWith($mime, 'image/'))
        {{-- Agar image hai to thumbnail dikhao --}}
        <a href="{{ $attendanceDetail->punch_in_image->url }}" target="_blank">
            <img src="{{ $attendanceDetail->punch_in_image->preview }}" 
                 alt="Punch In" class="img-thumbnail" style="max-width:150px;" />
        </a>
    @else
        {{-- Agar PDF ya koi aur file hai to icon + link dikhao --}}
        <a href="{{ $attendanceDetail->punch_in_image->url }}" target="_blank" class="btn btn-sm btn-info">
            📄 View File
        </a>
    @endif
@else
    -
@endif

                </div>
            </div>

            <div class="row mb-2">
                <div class="col-sm-4"><strong>Punch Out Image:</strong></div>
                <div class="col-sm-8">
                   @if($attendanceDetail->punch_out_image)
    @php
        $mime = $attendanceDetail->punch_out_image->mime_type ?? '';
    @endphp

    @if(Str::startsWith($mime, 'image/'))
        <a href="{{ $attendanceDetail->punch_out_image->url }}" target="_blank">
            <img src="{{ $attendanceDetail->punch_out_image->preview }}" 
                 alt="Punch Out" class="img-thumbnail" style="max-width:150px;" />
        </a>
    @else
        <a href="{{ $attendanceDetail->punch_out_image->url }}" target="_blank" class="btn btn-sm btn-info">
            📄 View File
        </a>
    @endif
@else
    -
@endif

                </div>
            </div>
        </div>
    </div>
@endif


    {{-- Attendance Log Section --}}
    @if($attendanceLog)
        <div class="card border-success shadow-sm">
            <div class="card-header bg-success text-white fw-semibold">
                <i class="fas fa-clock me-1"></i> Log Details
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-sm-4"><strong>In Time:</strong></div>
                    <div class="col-sm-8">
                        <span class="text-muted">Expected:</span> {{ $attendanceLog->expected_in }} |
                        <span class="text-muted">Actual:</span> {{ $attendanceLog->actual_in }}
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-4"><strong>Out Time:</strong></div>
                    <div class="col-sm-8">
                        <span class="text-muted">Expected:</span> {{ $attendanceLog->expected_out }} |
                        <span class="text-muted">Actual:</span> {{ $attendanceLog->actual_out }}
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-4"><strong>Late By:</strong></div>
                    <div class="col-sm-8">{{ $attendanceLog->late_by_minutes }} minutes</div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-4"><strong>Left Early:</strong></div>
                    <div class="col-sm-8">{{ $attendanceLog->left_early_by_minutes }} minutes</div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-4"><strong>Overtime:</strong></div>
                    <div class="col-sm-8">{{ $attendanceLog->overtime_by_minutes }} minutes</div>
                </div>
                <div class="row">
                    <div class="col-sm-4"><strong>Total Work:</strong></div>
                    <div class="col-sm-8">{{ $attendanceLog->total_work_minutes }} minutes</div>
                </div>
            </div>
        </div>
    @endif

    {{-- Location Map --}}
  <div class="card mt-3">
    <div class="card-header">
        <h5 class="card-title">Location Map</h5>
    </div>
    <div class="card-body">
        {{-- Map --}}
        @if($punch_in_location || $punch_out_location)
            <div id="attendance-map" style="height: 400px; width: 100%;"></div>

            <div class="mt-3">
                <h6>Punch In Location:</h6>
                <p>
                    {{ $punch_in_location ?? 'N/A' }}
                </p>

                <h6>Punch Out Location:</h6>
                <p>
                    {{ $punch_out_location ?? 'N/A' }}
                </p>
            </div>
        @else
            <p class="text-danger text-center fw-bold">Location Not Found</p>
        @endif
    </div>
</div>


<script>
    function initMap() {
        var punchIn = { lat: {{ $punchInLatitude ?? 0 }}, lng: {{ $punchInLongitude ?? 0 }} };
        var punchOut = { lat: {{ $punchOutLatitude ?? 0 }}, lng: {{ $punchOutLongitude ?? 0 }} };

        var map = new google.maps.Map(document.getElementById("attendance-map"), {
            zoom: 14,
            center: punchIn.lat !== 0 ? punchIn : punchOut
        });

        // Punch In marker
        if (punchIn.lat !== 0 && punchIn.lng !== 0) {
            new google.maps.Marker({
                position: punchIn,
                map: map,
                title: "Punch In Location",
                label: "IN"
            });
        }

        // Punch Out marker
        if (punchOut.lat !== 0 && punchOut.lng !== 0) {
            new google.maps.Marker({
                position: punchOut,
                map: map,
                title: "Punch Out Location",
                label: "OUT"
            });
        }

        // Auto fit bounds if both locations exist
        var bounds = new google.maps.LatLngBounds();
        if (punchIn.lat !== 0) bounds.extend(punchIn);
        if (punchOut.lat !== 0) bounds.extend(punchOut);
        if (!bounds.isEmpty()) map.fitBounds(bounds);
    }
</script>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBgRXfXiK8KHfSnKtunSIpGpKNmLNGNUzM&callback=initMap" async defer></script>


    {{-- Empty State --}}
    @if(!$attendanceDetail && !$attendanceLog && !$leaveRequest)
        <div class="alert alert-secondary mt-4" role="alert">
            No attendance or leave data found for this day.
        </div>
    @endif

</div>


