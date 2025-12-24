@extends('layouts.admin')

@section('content')
@can('track_member_create')
<div class="mb-2">
    <a class="btn btn-success" href="{{ route('admin.track-members.create') }}">
        Add Track Member
    </a>
</div>
@endcan

<div class="card">
    <div class="card-header">Live Tracking</div>

    <div class="card-body">
        <div class="form-group">
            <label><strong>Select Member to Track:</strong></label>
            <select id="memberSelect" class="form-control col-md-4">
                <option value="">-- Select Member --</option>
                @foreach($createdUsers as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
            <div id="statusText" class="mt-2 text-dark"></div>
        </div>

        <div id="mapContainer" style="display:none;">
            <div id="map" style="height: 400px; margin-top: 20px;"></div>
        </div>

        <table id="liveTrackingTable" class="table table-bordered table-striped mt-4">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Member</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <th>Location</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>


@endsection

@section('scripts')
@parent
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBgRXfXiK8KHfSnKtunSIpGpKNmLNGNUzM&libraries=geometry"></script>
<script>
let map, marker, geocoder, dtTable;
let lastLat = null, lastLng = null, lastTime = null;
let intervalId = null;
let mapInitialized = false;
let selectedUserId = null;

function initMap(lat, lng) {
    const myLatLng = { lat: lat, lng: lng };
    map = new google.maps.Map(document.getElementById("map"), {
        zoom: 15,
        center: myLatLng,
    });

    marker = new google.maps.Marker({
        position: myLatLng,
        map,
        title: "Tracking User",
    });

    geocoder = new google.maps.Geocoder();
    mapInitialized = true;
}

function reverseGeocode(lat, lng, callback) {
    const latLng = new google.maps.LatLng(lat, lng);
    if (!geocoder) geocoder = new google.maps.Geocoder();

    geocoder.geocode({ location: latLng }, function (results, status) {
        if (status === "OK") {
            callback(results[0]?.formatted_address || `Lat: ${lat}, Lng: ${lng}`);
        } else {
            callback(`Lat: ${lat}, Lng: ${lng}`);
        }
    });
}

function updateMapAndStatus(lat, lng, createdAt, fullAddress) {
    const newLatLng = new google.maps.LatLng(lat, lng);

    if (!mapInitialized) {
        document.getElementById('mapContainer').style.display = 'block';
        initMap(lat, lng);
    }

    const hasMoved = lastLat !== lat || lastLng !== lng;
    if (hasMoved) {
        marker.setPosition(newLatLng);
        map.setCenter(newLatLng);
    }

    const now = new Date();
    let status = '';

    if (!hasMoved && lastTime) {
        const diffSec = Math.floor((now - new Date(lastTime)) / 1000);
        if (diffSec >= 60) {
            status = `🛑 Stopped at ${fullAddress} for ${Math.floor(diffSec / 60)} minutes`;
        } else {
            status = `⏸ Idle at ${fullAddress}`;
        }
    } else {
        status = `🚶 Moving near ${fullAddress}`;
    }

    document.getElementById('statusText').innerText = status;
    lastLat = lat;
    lastLng = lng;
    lastTime = createdAt;
}

function fetchLiveLocation(userId) {
    fetch(`{{ route('admin.track-members.liveLocation') }}`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ user_id: userId })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.latitude || !data.longitude) return;

        const lat = parseFloat(data.latitude);
        const lng = parseFloat(data.longitude);
        const createdAt = new Date(data.created_at);

        reverseGeocode(lat, lng, function (fullAddress) {
            updateMapAndStatus(lat, lng, createdAt, fullAddress);

            const payload = {
                latitude: lat,
                longitude: lng,
                location: fullAddress
            };

            fetch("{{ route('admin.track.location') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(payload),
            });
        });
    })
    .catch(err => console.error("Fetch Error (live location):", err));
}

function fetchUserTrackTable(userId) {
    fetch(`{{ route('admin.track-members.fetchUserData') }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ user_id: userId })
    })
    .then(res => res.json())
    .then(data => {
        dtTable.clear();
        data.forEach(item => {
            dtTable.row.add([
                item.id,
                item.user?.name || '',
                item.latitude,
                item.longitude,
                item.location,
                item.created_at
            ]);
        });
        dtTable.draw();
    });
}

function updateOwnLocationIfNoSelection() {
    if (selectedUserId) return;

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            reverseGeocode(lat, lng, function (address) {
                updateMapAndStatus(lat, lng, new Date(), address);

                const payload = {
                    latitude: lat,
                    longitude: lng,
                    location: address
                };

                fetch("{{ route('admin.track.location') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload),
                });
            });
        }, function (error) {
            console.warn("❌ Geolocation error:", error.message);
        });
    }
}

document.addEventListener('DOMContentLoaded', function () {
    dtTable = $('#liveTrackingTable').DataTable({
        pageLength: 50,
        order: [[0, 'desc']]
    });

    document.getElementById('mapContainer').style.display = 'none';

    document.getElementById('memberSelect').addEventListener('change', function () {
        const userId = this.value;
        selectedUserId = userId;

        clearInterval(intervalId);
        document.getElementById('statusText').innerText = '';
        document.getElementById('mapContainer').style.display = 'none';
        map = null;
        marker = null;
        mapInitialized = false;
        lastLat = lastLng = lastTime = null;

        if (userId) {
            fetchUserTrackTable(userId);
            fetchLiveLocation(userId);
            intervalId = setInterval(() => fetchLiveLocation(userId), 5000);
        } else {
            selectedUserId = null;
            dtTable.clear().draw();
        }
    });

    // Start tracking own location every 5 seconds
    setInterval(updateOwnLocationIfNoSelection, 5000);
});
</script>
@endsection
