@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">Add New Branch</div>

    <div class="card-body">
        <form action="{{ route('admin.branches.store') }}" method="POST">
            @csrf

            {{-- Google Map --}}
            <div class="form-group mb-3">
                <label>Choose Location on Map</label>
                <div id="map" style="height: 400px; width: 100%; border: 1px solid #ccc;"></div>
            </div>

            <div class="row">
                {{-- Latitude --}}
                <div class="form-group col-md-4">
                    <label for="latitude">Latitude</label>
                    <input type="text" name="latitude" id="latitude" class="form-control" readonly>
                </div>

                {{-- Longitude --}}
                <div class="form-group col-md-4">
                    <label for="longitude">Longitude</label>
                    <input type="text" name="longitude" id="longitude" class="form-control" readonly>
                </div>

                {{-- Pincode --}}
                <div class="form-group col-md-4">
                    <label for="pincode">Pincode</label>
                    <input type="text" name="pincode" id="pincode" class="form-control" readonly>
                </div>
            </div>

            <div class="row mt-3">
                {{-- State --}}
                <div class="form-group col-md-4">
                    <label for="state">State</label>
                    <input type="text" name="state" id="state" class="form-control" readonly>
                </div>

                {{-- City --}}
                <div class="form-group col-md-4">
                    <label for="city">City</label>
                    <input type="text" name="city" id="city" class="form-control" readonly>
                </div>

                {{-- Address --}}
                <div class="form-group col-md-4">
                    <label for="address">Full Address</label>
                    <input type="text" name="address" id="address" class="form-control" readonly>
                </div>
            </div>

            <hr>

            {{-- Other Company Details --}}
            <div class="row mt-3">
                <div class="form-group col-md-4">
                    <label for="branch_name">Branch Name</label>
                    <input type="text" name="title" id="branch_name" class="form-control">
                </div>

                <div class="form-group col-md-4">
                    <label for="legal_name">Legal Name</label>
                    <input type="text" name="legal_name" id="legal_name" class="form-control">
                </div>

                <div class="form-group col-md-4">
                    <label for="incharge_name">Incharge Name</label>
                    <input type="text" name="incharge_name" id="incharge_name" class="form-control">
                </div>
            </div>

            <div class="row mt-3">
                <div class="form-group col-md-4">
                    <label for="gst">GST Number</label>
                    <input type="text" name="gst" id="gst" class="form-control">
                </div>

                <div class="form-group col-md-4">
                    <label for="pan">PAN Number</label>
                    <input type="text" name="pan" id="pan" class="form-control">
                </div>

                <div class="form-group col-md-4">
                    <label for="registration_number">Registration Number</label>
                    <input type="text" name="registration_number" id="registration_number" class="form-control">
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-success" type="submit">Save Branch</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    let map, marker, geocoder;
    const apiKey = "AIzaSyBgRXfXiK8KHfSnKtunSIpGpKNmLNGNUzM";

    function initMap() {
        const defaultLocation = { lat: 28.6139, lng: 77.2090 }; // Delhi

        map = new google.maps.Map(document.getElementById("map"), {
            center: defaultLocation,
            zoom: 13,
        });

        marker = new google.maps.Marker({
            position: defaultLocation,
            map: map,
            draggable: true,
        });

        geocoder = new google.maps.Geocoder();

        updateLocationInputs(defaultLocation);

        marker.addListener('dragend', function () {
            const newPos = marker.getPosition();
            updateLocationInputs({
                lat: newPos.lat(),
                lng: newPos.lng(),
            });
        });
    }

    function updateLocationInputs(latlng) {
        document.getElementById("latitude").value = latlng.lat;
        document.getElementById("longitude").value = latlng.lng;

        geocoder.geocode({ location: latlng }, function (results, status) {
            if (status === "OK") {
                if (results[0]) {
                    const components = results[0].address_components;
                    document.getElementById("address").value = results[0].formatted_address;

                    let pincode = "", city = "", state = "";
                    for (let comp of components) {
                        if (comp.types.includes("postal_code")) pincode = comp.long_name;
                        if (comp.types.includes("administrative_area_level_1")) state = comp.long_name;
                        if (comp.types.includes("locality")) city = comp.long_name;
                        if (comp.types.includes("administrative_area_level_2") && !city) city = comp.long_name;
                    }

                    document.getElementById("pincode").value = pincode;
                    document.getElementById("city").value = city;
                    document.getElementById("state").value = state;
                }
            } else {
                alert("Geocoder failed: " + status);
            }
        });
    }
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBgRXfXiK8KHfSnKtunSIpGpKNmLNGNUzM&callback=initMap"></script>
@endsection
