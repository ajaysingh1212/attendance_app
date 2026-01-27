@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">Edit Branch</div>

    <div class="card-body">
        <form action="{{ route('admin.branches.update', $branch->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- 🔍 ADDRESS SEARCH --}}
            <div class="form-group mb-3">
                <label>Search Address</label>
                <input type="text"
                       id="search_address"
                       class="form-control"
                       placeholder="Search area / landmark / city"
                       value="{{ old('address', $branch->address) }}">
            </div>

            {{-- 🗺 GOOGLE MAP --}}
            <div class="form-group mb-3">
                <label>Choose Location on Map</label>
                <div id="map" style="height: 400px; width: 100%; border: 1px solid #ccc;"></div>
            </div>

            {{-- LOCATION DETAILS --}}
            <div class="row">
                <div class="form-group col-md-4">
                    <label>Latitude</label>
                    <input type="text" name="latitude" id="latitude" class="form-control"
                           value="{{ old('latitude', $branch->latitude) }}" readonly>
                </div>

                <div class="form-group col-md-4">
                    <label>Longitude</label>
                    <input type="text" name="longitude" id="longitude" class="form-control"
                           value="{{ old('longitude', $branch->longitude) }}" readonly>
                </div>

                <div class="form-group col-md-4">
                    <label>Pincode</label>
                    <input type="text" name="pincode" id="pincode" class="form-control"
                           value="{{ old('pincode', $branch->pincode) }}" readonly>
                </div>
            </div>

            <div class="row mt-3">
                <div class="form-group col-md-4">
                    <label>State</label>
                    <input type="text" name="state" id="state" class="form-control"
                           value="{{ old('state', $branch->state) }}" readonly>
                </div>

                <div class="form-group col-md-4">
                    <label>City</label>
                    <input type="text" name="city" id="city" class="form-control"
                           value="{{ old('city', $branch->city) }}" readonly>
                </div>

                <div class="form-group col-md-4">
                    <label>Full Address</label>
                    <input type="text" name="address" id="address" class="form-control"
                           value="{{ old('address', $branch->address) }}" readonly>
                </div>
            </div>

            <hr>

            {{-- BRANCH DETAILS --}}
            <div class="row mt-3">
                <div class="form-group col-md-4">
                    <label>Branch Name</label>
                    <input type="text" name="title" class="form-control"
                           value="{{ old('title', $branch->title) }}">
                </div>

                <div class="form-group col-md-4">
                    <label>Legal Name</label>
                    <input type="text" name="legal_name" class="form-control"
                           value="{{ old('legal_name', $branch->legal_name) }}">
                </div>

                <div class="form-group col-md-4">
                    <label>Incharge Name</label>
                    <input type="text" name="incharge_name" class="form-control"
                           value="{{ old('incharge_name', $branch->incharge_name) }}">
                </div>
            </div>

            <div class="row mt-3">
                <div class="form-group col-md-6">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control"
                           value="{{ old('email', $branch->email) }}">
                </div>
                <div class="form-group col-md-6">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control"
                           value="{{ old('phone', $branch->phone) }}">
                </div>
            </div>

            <div class="row mt-3">
                <div class="form-group col-md-4">
                    <label>GST Number</label>
                    <input type="text" name="gst" class="form-control"
                           value="{{ old('gst', $branch->gst) }}">
                </div>

                <div class="form-group col-md-4">
                    <label>PAN Number</label>
                    <input type="text" name="pan" class="form-control"
                           value="{{ old('pan', $branch->pan) }}">
                </div>

                <div class="form-group col-md-4">
                    <label>Registration Number</label>
                    <input type="text" name="registration_number" class="form-control"
                           value="{{ old('registration_number', $branch->registration_number) }}">
                </div>
            </div>

            <hr>

            {{-- 🎨 CREATIVE FILE UPLOADS --}}
            <div class="row mt-4">

                {{-- LOGO --}}
                <div class="col-md-4">
                    <div class="card shadow-sm p-3 text-center">
                        <label class="fw-bold">Branch Logo</label>
                        <div class="mb-2">
                            @if($branch->branch_image)
                                <img src="{{ $branch->branch_image->preview ?? $branch->branch_image->url }}"
                                     style="height:80px;border:1px solid #ddd;padding:4px;">
                            @else
                                <p class="text-muted">No Logo</p>
                            @endif
                        </div>
                        <input type="file" name="branch_image" class="form-control">
                    </div>
                </div>

                {{-- SIGNATURE --}}
                <div class="col-md-4">
                    <div class="card shadow-sm p-3 text-center">
                        <label class="fw-bold">Authorized Signature</label>
                        <div class="mb-2">
                            @if($branch->signature_image)
                                <img src="{{ $branch->signature_image->preview ?? $branch->signature_image->url }}"
                                     style="height:80px;border:1px solid #ddd;padding:4px;">
                            @else
                                <p class="text-muted">No Signature</p>
                            @endif
                        </div>
                        <input type="file" name="signature" class="form-control">
                    </div>
                </div>

                {{-- STAMP --}}
                <div class="col-md-4">
                    <div class="card shadow-sm p-3 text-center">
                        <label class="fw-bold">Company Stamp</label>
                        <div class="mb-2">
                            @if($branch->stamp_image)
                                <img src="{{ $branch->stamp_image->preview ?? $branch->stamp_image->url }}"
                                     style="height:80px;border:1px solid #ddd;padding:4px;">
                            @else
                                <p class="text-muted">No Stamp</p>
                            @endif
                        </div>
                        <input type="file" name="stamp" class="form-control">
                    </div>
                </div>

            </div>

            <div class="mt-4 text-end">
                <button class="btn btn-primary px-4">Update Branch</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let map, marker, geocoder, autocomplete;

function initMap() {
    const lat = parseFloat(document.getElementById("latitude").value) || 25.5941;
    const lng = parseFloat(document.getElementById("longitude").value) || 85.1376;

    const location = { lat, lng };

    map = new google.maps.Map(document.getElementById("map"), {
        center: location,
        zoom: 13,
    });

    marker = new google.maps.Marker({
        position: location,
        map: map,
        draggable: true,
    });

    geocoder = new google.maps.Geocoder();

    // 🔍 AUTOCOMPLETE
    autocomplete = new google.maps.places.Autocomplete(
        document.getElementById('search_address')
    );

    autocomplete.addListener('place_changed', function () {
        const place = autocomplete.getPlace();
        if (!place.geometry) return;

        const newLocation = {
            lat: place.geometry.location.lat(),
            lng: place.geometry.location.lng()
        };

        map.setCenter(newLocation);
        marker.setPosition(newLocation);
        updateLocationInputs(newLocation);
    });

    marker.addListener('dragend', function () {
        const pos = marker.getPosition();
        updateLocationInputs({ lat: pos.lat(), lng: pos.lng() });
    });
}

function updateLocationInputs(latlng) {
    document.getElementById("latitude").value = latlng.lat;
    document.getElementById("longitude").value = latlng.lng;

    geocoder.geocode({ location: latlng }, function (results, status) {
        if (status === "OK" && results[0]) {
            const comps = results[0].address_components;
            document.getElementById("address").value = results[0].formatted_address;

            let pincode="", city="", state="";
            comps.forEach(c => {
                if (c.types.includes("postal_code")) pincode = c.long_name;
                if (c.types.includes("locality")) city = c.long_name;
                if (c.types.includes("administrative_area_level_1")) state = c.long_name;
            });

            document.getElementById("pincode").value = pincode;
            document.getElementById("city").value = city;
            document.getElementById("state").value = state;
        }
    });
}
</script>

<script async defer
src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBgRXfXiK8KHfSnKtunSIpGpKNmLNGNUzM&libraries=places&callback=initMap"></script>
@endsection
