@extends('layouts.admin')

@section('content')
<style>
.area-shell{display:grid;grid-template-columns:minmax(320px,390px) 1fr;gap:18px}.area-panel{background:#fff;border:1px solid #dfe4ea;border-radius:8px;padding:18px}.area-title{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}.area-title h4{margin:0;color:#172b4d}.map-wrap{position:relative;min-height:620px;border-radius:8px;overflow:hidden;border:1px solid #dfe4ea}#officeAreaMap{height:620px}.area-row{border-left:4px solid var(--area-color);padding:12px;margin-top:10px;background:#f8fafc;border-radius:4px}.area-meta{font-size:12px;color:#64748b}.area-actions{display:flex;gap:6px;margin-top:8px}.location-btn{position:absolute;right:12px;top:12px;z-index:2;box-shadow:0 2px 8px rgba(0,0,0,.2)}@media(max-width:900px){.area-shell{grid-template-columns:1fr}.map-wrap,#officeAreaMap{min-height:440px;height:440px}}
</style>

<div class="area-title">
    <h4><i class="fas fa-draw-polygon mr-2"></i>Office Area</h4>
    <span class="badge badge-primary">{{ $areas->count() }} active radius zones</span>
</div>

<div class="area-shell">
    <div>
        <div class="area-panel">
            <h6 class="font-weight-bold">Add Radius</h6>
            <form method="POST" action="{{ route('admin.office-areas.store') }}" id="areaForm">
                @csrf
                <div class="form-group"><label>Area name</label><input class="form-control" name="name" required placeholder="Main office radius"></div>
                <div class="form-group"><label>Branch</label><select class="form-control" name="branch_id" id="officeBranchSelect" required><option value="">Select branch</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" data-latitude="{{ $branch->latitude }}" data-longitude="{{ $branch->longitude }}" data-address="{{ $branch->address }}">{{ $branch->title }}{{ $branch->address ? ' - '.$branch->address : '' }}</option>@endforeach</select><small class="text-muted">Branches are loaded from Master Data &gt; Branch.</small></div>
                <div class="form-row"><div class="form-group col-6"><label>Latitude</label><input class="form-control" id="latitude" name="latitude" required readonly></div><div class="form-group col-6"><label>Longitude</label><input class="form-control" id="longitude" name="longitude" required readonly></div></div>
                <div class="form-row"><div class="form-group col-6"><label>Radius (meters)</label><input type="number" class="form-control" id="radius" name="radius_meters" value="100" min="1" required></div><div class="form-group col-6"><label>Review time (minutes)</label><input type="number" class="form-control" name="review_minutes" value="10" min="1" required></div></div>
                <div class="form-row align-items-end"><div class="form-group col-6"><label>Circle color</label><input type="color" class="form-control" id="color" name="color" value="#2563eb"></div><div class="form-group col-6"><label><input type="checkbox" name="is_active" value="1" checked> Active</label></div></div>
                <button class="btn btn-primary btn-block"><i class="fas fa-plus mr-1"></i>Add Office Area</button>
            </form>
        </div>
        <div class="area-panel mt-3">
            <h6 class="font-weight-bold">Configured Areas</h6>
            @forelse($areas as $area)
                <div class="area-row" style="--area-color:{{ $area->color }}">
                    <strong>{{ $area->name }}</strong> @if(!$area->is_active)<span class="badge badge-secondary">Disabled</span>@endif
                    <div class="area-meta">{{ $area->branch->title ?? $area->officeBranch->branch_name ?? 'Legacy area' }} | {{ $area->radius_meters }} m | {{ $area->review_minutes }} min review</div>
                    <div class="area-actions">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="focusArea({{ $area->latitude }},{{ $area->longitude }},{{ $area->radius_meters }})"><i class="fas fa-crosshairs"></i></button>
                        <form method="POST" action="{{ route('admin.office-areas.destroy', $area) }}" onsubmit="return confirm('Remove this office area?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>
                    </div>
                </div>
            @empty
                <p class="text-muted mb-0">No office area configured yet.</p>
            @endforelse
        </div>
    </div>
    <div class="map-wrap">
        <button type="button" class="btn btn-light location-btn" id="liveLocation"><i class="fas fa-location-arrow mr-1"></i>Use live location</button>
        <div id="officeAreaMap"></div>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
let areaMap, draftMarker, draftCircle;
const savedAreas = {{ Illuminate\Support\Js::from($mapAreas) }};
function initOfficeAreaMap(){
    const fallback={lat:25.5941,lng:85.1376};
    const first=savedAreas[0];
    const center=first?{lat:Number(first.latitude),lng:Number(first.longitude)}:fallback;
    areaMap=new google.maps.Map(document.getElementById('officeAreaMap'),{center,zoom:17,mapTypeControl:false,streetViewControl:false});
    savedAreas.forEach(a=>{const p={lat:Number(a.latitude),lng:Number(a.longitude)};new google.maps.Marker({map:areaMap,position:p,title:a.name});new google.maps.Circle({map:areaMap,center:p,radius:Number(a.radius_meters),strokeColor:a.color,strokeOpacity:.9,strokeWeight:2,fillColor:a.color,fillOpacity:.15});});
    areaMap.addListener('click',e=>setDraft(e.latLng.lat(),e.latLng.lng()));
    document.getElementById('radius').addEventListener('input',e=>{if(draftCircle){draftCircle.setRadius(Number(e.target.value)||1);fitDraftCircle();}});
    document.getElementById('officeBranchSelect').addEventListener('change',function(){const option=this.options[this.selectedIndex];const lat=Number(option.dataset.latitude);const lng=Number(option.dataset.longitude);if(!option.value)return;if(!Number.isFinite(lat)||!Number.isFinite(lng)||(!lat&&!lng)){alert('Selected office does not have a saved map location. Please update its latitude and longitude first.');return}setDraft(lat,lng);areaMap.setCenter({lat,lng});fitDraftCircle();});
    document.getElementById('color').addEventListener('input',e=>draftCircle&&draftCircle.setOptions({strokeColor:e.target.value,fillColor:e.target.value}));
    document.getElementById('liveLocation').addEventListener('click',()=>requestLiveLocation(true));
    requestLiveLocation(false);
}
function requestLiveLocation(showError){if(!navigator.geolocation){if(showError)alert('Geolocation is not supported in this browser.');return}navigator.geolocation.getCurrentPosition(p=>{setDraft(p.coords.latitude,p.coords.longitude);areaMap.setCenter({lat:p.coords.latitude,lng:p.coords.longitude});},()=>{if(showError)alert('Live location permission is required.');},{enableHighAccuracy:true,timeout:10000,maximumAge:0});}
function setDraft(lat,lng){document.getElementById('latitude').value=lat.toFixed(7);document.getElementById('longitude').value=lng.toFixed(7);const p={lat,lng};if(!draftMarker){draftMarker=new google.maps.Marker({map:areaMap,position:p,draggable:true});draftMarker.addListener('dragend',e=>setDraft(e.latLng.lat(),e.latLng.lng()));draftCircle=new google.maps.Circle({map:areaMap,center:p,radius:Number(document.getElementById('radius').value),strokeWeight:2,fillOpacity:.18});}else{draftMarker.setPosition(p);draftCircle.setCenter(p)}draftCircle.setOptions({strokeColor:document.getElementById('color').value,fillColor:document.getElementById('color').value});}
function fitDraftCircle(){if(draftCircle)areaMap.fitBounds(draftCircle.getBounds(),40);}
function focusArea(lat,lng,radius){areaMap.setCenter({lat:Number(lat),lng:Number(lng)});areaMap.fitBounds(new google.maps.Circle({center:{lat:Number(lat),lng:Number(lng)},radius:Number(radius)}).getBounds());}
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ $mapsKey }}&libraries=places&callback=initOfficeAreaMap"></script>
@endsection
