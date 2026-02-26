@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Employees List</h4>
            <a href="{{ route('admin.employees.create') }}" class="btn btn-success">+ Create Employee</a>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover datatable datatable-Employee">
                <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Employee Code</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Branch</th>
                    <th>Position</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th width="180">Actions</th>
                </tr>
                </thead>

                <tbody>
                @foreach($employees as $index => $employee)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $employee->employee_code }}</td>
                        <td>{{ $employee->full_name }}</td>
                        <td>{{ $employee->email }}</td>
                        <td>{{ $employee->phone }}</td>
                        <td>{{ $employee->branch->title ?? 'Anywhere' }}</td>
                        <td>{{ $employee->position }}</td>
                        <td>{{ $employee->department }}</td>
                        <td>
                            <span class="badge badge-{{ $employee->status == 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($employee->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.employees.show', $employee->id) }}" class="btn btn-sm btn-secondary">View</a>
                            <a href="{{ route('admin.payroll.edit', $employee->id) }}" class="btn btn-sm btn-info">Edit</a>

                            <button
                                class="btn btn-sm btn-warning offer-letter-btn"
                                data-employee-id="{{ $employee->id }}">
                                <i class="fas fa-file-alt"></i>
                            </button>

                            <form action="{{ route('admin.employees.destroy', $employee->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>

            </table>
        </div>
    </div>
</div>

{{-- ================= TERMS MODAL ================= --}}
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Terms & Conditions</h5>
            </div>
            <div class="modal-body">
                <iframe src="{{ asset('terms/policy.pdf') }}" width="100%" height="400"></iframe>

                <div class="alert alert-info mt-3">
                    📍 Stay still • Clear background • Good lighting  
                    <br>Verification works like SIM-card activation
                </div>

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

{{-- ================= CAMERA MODAL ================= --}}
<div class="modal fade" id="cameraModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Live Camera Verification</h5>
            </div>
            <div class="modal-body text-center">
                <div style="position:relative;display:inline-block;">
                    <video id="video" autoplay playsinline muted width="420" style="border-radius:10px;border:2px solid #0d6efd;"></video>
                    <div style="position:absolute;bottom:10px;left:0;right:0;color:#fff;background:rgba(0,0,0,0.6);padding:5px;">
                        Keep face centered • Do not move
                    </div>
                </div>

                <canvas id="canvas" class="d-none"></canvas>
                <p id="cameraMsg" class="text-danger mt-2"></p>
            </div>
        </div>
    </div>
</div>

{{-- ================= SIGNATURE MODAL ================= --}}
<div class="modal fade" id="signModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Digital Signature</h5>
            </div>
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
@endsection

@section('scripts')
@parent
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

<script>
$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
});

let selectedEmployeeId = null;
let signaturePad = null;
let videoStream = null;
let lat = null, lng = null, address = null;

/* ================= OFFER CLICK ================= */
$('.offer-letter-btn').on('click', function () {
    selectedEmployeeId = $(this).data('employee-id');

    $.get(`/admin/employees/${selectedEmployeeId}/terms-status`, function (res) {
        if (res.accepted) {
            window.location.href = `/admin/employees/offer-letter/${selectedEmployeeId}`;
        } else {
            $('#termsModal').modal('show');
        }
    });
});

/* ================= TERMS ================= */
$('#acceptTerms').on('change', function () {
    $('#acceptTermsBtn').prop('disabled', !this.checked);
});

$('#acceptTermsBtn').on('click', function () {
    getLocationAndStartCamera();
});

/* ================= LOCATION ================= */
function getLocationAndStartCamera() {
    navigator.geolocation.getCurrentPosition(pos => {
        lat = pos.coords.latitude;
        lng = pos.coords.longitude;

        fetch(`https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&key=AIzaSyBgRXfXiK8KHfSnKtunSIpGpKNmLNGNUzM`)
            .then(res => res.json())
            .then(data => {
                address = data.results[0]?.formatted_address || 'Unknown location';
            });

        $('#termsModal').modal('hide');
        startCamera();

    }, () => {
        alert('Location permission required');
    }, { enableHighAccuracy: true });
}

/* ================= CAMERA ================= */
function startCamera() {
    $('#cameraModal').modal('show');

    navigator.mediaDevices.getUserMedia({
        video: { facingMode: 'user' }
    }).then(stream => {
        videoStream = stream;
        const video = document.getElementById('video');
        video.srcObject = stream;

        video.onloadedmetadata = () => {
            video.play();
            setTimeout(capturePhoto, 2500); // ✅ SAFE CAPTURE
        };
    });
}

function capturePhoto() {
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    videoStream.getTracks().forEach(track => track.stop());

    canvas.toBlob(blob => {
        let fd = new FormData();
        fd.append('photo', blob);
        fd.append('employee_id', selectedEmployeeId);
        fd.append('lat', lat);
        fd.append('lng', lng);
        fd.append('address', address);

        $.ajax({
            url: '/admin/employees/save-photo',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function () {
                $('#cameraModal').modal('hide');
                openSignature();
            }
        });
    }, 'image/jpeg', 0.95);
}

/* ================= SIGNATURE ================= */
function openSignature() {
    $('#signModal').modal({ backdrop: 'static', keyboard: false });
    signaturePad = new SignaturePad(document.getElementById('signaturePad'));
    $('#locationInfo').text(`📍 ${address}`);
}

$('#saveSignature').on('click', function () {
    if (!signaturePad || signaturePad.isEmpty()) {
        alert('Signature required');
        return;
    }

    $.post('/admin/employees/save-signature', {
        employee_id: selectedEmployeeId,
        signature: signaturePad.toDataURL(),
        lat, lng, address
    }, function () {
        alert('✅ Verification completed successfully');
        location.reload();
    });
});
</script>
@endsection
