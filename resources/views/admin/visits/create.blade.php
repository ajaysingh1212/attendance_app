@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0">Visit Management</h4>
    </div>
    <div class="card-body">

        {{-- Flash --}}
        @if(session('flash'))
            <div class="alert alert-success">{{ session('flash') }}</div>
        @endif

      @php
    $activeVisit = \App\Models\Visit::where('user', auth()->id())
                    ->whereNull('visited_out_time')
                    ->latest()
                    ->first();
@endphp


        {{-- ================= CHECK-IN FORM ================= --}}
        @if(!$activeVisit)
        <form id="checkin-form" method="POST" action="{{ route('admin.visits.start') }}">
            @csrf
            <input type="hidden" name="latitude" id="checkin_lat">
            <input type="hidden" name="longitude" id="checkin_lng">
            <input type="hidden" name="location" id="checkin_loc">
            <input type="hidden" name="visited_counter_image" id="counterImageData">
            <input type="hidden" name="visit_self_image" id="selfieImageData">
         <div class="form-group">
                <label for="latitude">{{ trans('cruds.visit.fields.title') }}</label>
                <input class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" type="text" name="title" id="title" value="">
                @if($errors->has('title'))
                    <div class="invalid-feedback">
                        {{ $errors->first('title') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.visit.fields.title_helper') }}</span>
            </div>
            <h5 class="mb-4">Check-in</h5>

            <div class="row">
                {{-- Counter --}}
                <div class="col-md-6 mb-3">
                    <label>Counter Photo</label>
                    <video id="counterPreview" class="w-100 rounded d-none" autoplay playsinline></video>
                    <img id="counterCaptured" class="w-100 rounded d-none"/>
                    <canvas id="counterCanvas" class="d-none"></canvas>
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-secondary" id="startCounterCamera">Open Camera</button>
                        <button type="button" class="btn btn-sm btn-success d-none" id="captureCounter">Capture</button>
                        <button type="button" class="btn btn-sm btn-warning d-none" id="retakeCounter">Retake</button>
                    </div>
                </div>
                {{-- Selfie --}}
                <div class="col-md-6 mb-3">
                    <label>Selfie Photo</label>
                    <video id="selfiePreview" class="w-100 rounded d-none" autoplay playsinline></video>
                    <img id="selfieCaptured" class="w-100 rounded d-none"/>
                    <canvas id="selfieCanvas" class="d-none"></canvas>
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-secondary" id="startSelfieCamera">Open Camera</button>
                        <button type="button" class="btn btn-sm btn-success d-none" id="captureSelfie">Capture</button>
                        <button type="button" class="btn btn-sm btn-warning d-none" id="retakeSelfie">Retake</button>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3" id="checkin-btn" disabled>Check-in</button>
        </form>
        @endif

        {{-- ================= CHECK-OUT FORM ================= --}}
        @if($activeVisit)
        <form id="checkout-form" method="POST" action="{{ route('admin.visits.out',$activeVisit->id) }}">
            @csrf
            <input type="hidden" name="visited_out_latitude" id="checkout_lat">
            <input type="hidden" name="visited_out_longitude" id="checkout_lng">
            <input type="hidden" name="visited_out_location" id="checkout_loc">
            <input type="hidden" name="visited_out_counter_image" id="outCounterImageData">
            <input type="hidden" name="visited_out_self_image" id="outSelfieImageData">

            <h5 class="mb-4">Check-out</h5>

            <div class="row">
                {{-- Counter --}}
                <div class="col-md-6 mb-3">
                    <label>Out Counter Photo</label>
                    <video id="outCounterPreview" class="w-100 rounded d-none" autoplay playsinline></video>
                    <img id="outCounterCaptured" class="w-100 rounded d-none"/>
                    <canvas id="outCounterCanvas" class="d-none"></canvas>
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-secondary" id="startOutCounterCamera">Open Camera</button>
                        <button type="button" class="btn btn-sm btn-success d-none" id="captureOutCounter">Capture</button>
                        <button type="button" class="btn btn-sm btn-warning d-none" id="retakeOutCounter">Retake</button>
                    </div>
                </div>
                {{-- Selfie --}}
                <div class="col-md-6 mb-3">
                    <label>Out Selfie Photo</label>
                    <video id="outSelfiePreview" class="w-100 rounded d-none" autoplay playsinline></video>
                    <img id="outSelfieCaptured" class="w-100 rounded d-none"/>
                    <canvas id="outSelfieCanvas" class="d-none"></canvas>
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-secondary" id="startOutSelfieCamera">Open Camera</button>
                        <button type="button" class="btn btn-sm btn-success d-none" id="captureOutSelfie">Capture</button>
                        <button type="button" class="btn btn-sm btn-warning d-none" id="retakeOutSelfie">Retake</button>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-danger mt-3" id="checkout-btn" disabled>Check-out</button>
        </form>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function(){
    let currentStream = null;

    // ---------- STOP CAMERA ----------
    function stopStream(){
        if(currentStream){
            currentStream.getTracks().forEach(t=>t.stop());
            currentStream=null;
        }
    }

    // ---------- START CAMERA ----------
    async function startCamera(previewId, captureBtnId){
        stopStream();
        try{
            const constraints = { video:true };
            const stream = await navigator.mediaDevices.getUserMedia(constraints);
            currentStream = stream;
            const video = document.getElementById(previewId);
            video.srcObject = stream;
            video.classList.remove('d-none');
            document.getElementById(captureBtnId).classList.remove('d-none');
        }catch(e){ alert("Camera error: "+e.message); }
    }

    // ---------- CAPTURE IMAGE ----------
    function capture(previewId, canvasId, imgId, hiddenId, captureBtnId, retakeBtnId){
        const video=document.getElementById(previewId);
        const canvas=document.getElementById(canvasId);
        const img=document.getElementById(imgId);
        const hidden=document.getElementById(hiddenId);

        canvas.width=video.videoWidth;
        canvas.height=video.videoHeight;
        canvas.getContext('2d').drawImage(video,0,0);
        const data=canvas.toDataURL('image/jpeg');
        hidden.value=data;

        img.src=data;
        img.classList.remove('d-none');
        video.classList.add('d-none');
        stopStream();

        document.getElementById(captureBtnId).classList.add('d-none');
        document.getElementById(retakeBtnId).classList.remove('d-none');

        updateButtons();
    }

    // ---------- RETAKE ----------
    function retake(previewId, imgId, hiddenId, captureBtnId, retakeBtnId){
        document.getElementById(hiddenId).value="";
        document.getElementById(imgId).classList.add('d-none');
        document.getElementById(captureBtnId).classList.add('d-none');
        document.getElementById(retakeBtnId).classList.add('d-none');
        updateButtons();
    }

    // ---------- ENABLE/DISABLE BUTTONS ----------
    function updateButtons(){
        const checkinOk = document.getElementById('counterImageData')?.value && document.getElementById('selfieImageData')?.value;
        const checkoutOk = document.getElementById('outCounterImageData')?.value && document.getElementById('outSelfieImageData')?.value;

        if(document.getElementById('checkin-btn')) document.getElementById('checkin-btn').disabled = !checkinOk;
        if(document.getElementById('checkout-btn')) document.getElementById('checkout-btn').disabled = !checkoutOk;
    }

    // ---------- GEOLOCATION ----------
    function fetchLocation(prefixLat, prefixLng, prefixLoc){
        if(!navigator.geolocation){
            alert("Please enable GPS / Location services.");
            return;
        }
        navigator.geolocation.getCurrentPosition(function(pos){
            document.getElementById(prefixLat).value = pos.coords.latitude;
            document.getElementById(prefixLng).value = pos.coords.longitude;
            document.getElementById(prefixLoc).value = "";
        }, function(err){
            alert("Location error: " + err.message);
        }, { enableHighAccuracy:true, timeout:10000, maximumAge:0 });
    }

    // Run on load
    if(document.getElementById('checkin-form')){
        fetchLocation('checkin_lat','checkin_lng','checkin_loc');
    }
    if(document.getElementById('checkout-form')){
        fetchLocation('checkout_lat','checkout_lng','checkout_loc');
    }

    // ---------- EVENTS ----------
    document.getElementById('startCounterCamera')?.addEventListener('click', ()=>startCamera('counterPreview','captureCounter'));
    document.getElementById('captureCounter')?.addEventListener('click', ()=>capture('counterPreview','counterCanvas','counterCaptured','counterImageData','captureCounter','retakeCounter'));
    document.getElementById('retakeCounter')?.addEventListener('click', ()=>retake('counterPreview','counterCaptured','counterImageData','captureCounter','retakeCounter'));

    document.getElementById('startSelfieCamera')?.addEventListener('click', ()=>startCamera('selfiePreview','captureSelfie'));
    document.getElementById('captureSelfie')?.addEventListener('click', ()=>capture('selfiePreview','selfieCanvas','selfieCaptured','selfieImageData','captureSelfie','retakeSelfie'));
    document.getElementById('retakeSelfie')?.addEventListener('click', ()=>retake('selfiePreview','selfieCaptured','selfieImageData','captureSelfie','retakeSelfie'));

    document.getElementById('startOutCounterCamera')?.addEventListener('click', ()=>startCamera('outCounterPreview','captureOutCounter'));
    document.getElementById('captureOutCounter')?.addEventListener('click', ()=>capture('outCounterPreview','outCounterCanvas','outCounterCaptured','outCounterImageData','captureOutCounter','retakeOutCounter'));
    document.getElementById('retakeOutCounter')?.addEventListener('click', ()=>retake('outCounterPreview','outCounterCaptured','outCounterImageData','captureOutCounter','retakeOutCounter'));

    document.getElementById('startOutSelfieCamera')?.addEventListener('click', ()=>startCamera('outSelfiePreview','captureOutSelfie'));
    document.getElementById('captureOutSelfie')?.addEventListener('click', ()=>capture('outSelfiePreview','outSelfieCanvas','outSelfieCaptured','outSelfieImageData','captureOutSelfie','retakeOutSelfie'));
    document.getElementById('retakeOutSelfie')?.addEventListener('click', ()=>retake('outSelfiePreview','outSelfieCaptured','outSelfieImageData','captureOutSelfie','retakeOutSelfie'));
});
</script>
@endsection
