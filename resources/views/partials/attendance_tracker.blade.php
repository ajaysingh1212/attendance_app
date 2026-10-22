@if(auth()->check() && !auth()->user()->is_admin)
@php
$activeTrackingAttendance = \App\Models\AttendanceDetail::where('user_id', auth()->id())
    ->whereNotNull('punch_in_time')->where('punch_in_time', '>=', now()->subDays(2))
    ->where(function ($q) { $q->where('tracking_enabled', true)->orWhere('verification_status', 'in_review'); })
    ->latest()->first();
@endphp
@if($activeTrackingAttendance)
<script>
(() => {
    const key = 'attendance-events-{{ auth()->id() }}-{{ $activeTrackingAttendance->id }}';
    let queue = [];
    try { queue = JSON.parse(localStorage.getItem(key) || '[]'); } catch (_) {}
    let active = @json(!$activeTrackingAttendance->tracking_completed_at && !$activeTrackingAttendance->punch_out_time), busy = false, locationFailed = false;
    function persist() { try { localStorage.setItem(key, JSON.stringify(queue)); } catch (_) {} }
    function record(type) {
        queue.push({id: window.crypto?.randomUUID?.() || Date.now()+'-'+Math.random().toString(36).slice(2), type, occurred_at:new Date().toISOString()});
        persist();
    }
    async function send(coords = null) {
        if ((!active && !queue.length) || busy || !navigator.onLine) return;
        busy = true;
        const batch = queue.slice(0,100);
        const payload = {attendance_id:{{ $activeTrackingAttendance->id }}, events:batch};
        if (coords) { payload.latitude=coords.latitude; payload.longitude=coords.longitude; }
        try {
            const response = await fetch(@json(route('admin.attendance-details.liveLocation')), {
                method:'POST', headers:{'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN':@json(csrf_token())}, body:JSON.stringify(payload)
            });
            if (!response.ok) return;
            const data = await response.json();
            const ids = new Set(batch.map(e=>e.id)); queue=queue.filter(e=>!ids.has(e.id)); persist();
            active=data.tracking_active;
            window.dispatchEvent(new CustomEvent('attendance-tracking-state', {detail:data}));
        } catch (_) {} finally { busy=false; }
    }
    function check() {
        if (!navigator.onLine) return;
        if (!active) { if(queue.length)send(); return; }
        if (!navigator.geolocation) { if(!locationFailed)record('location_unavailable'); locationFailed=true; send(); return; }
        navigator.geolocation.getCurrentPosition(position => {
            if(locationFailed)record('location_available'); locationFailed=false;
            send(position.coords);
        }, () => {
            if(!locationFailed)record('location_unavailable'); locationFailed=true; send();
        }, {enableHighAccuracy:true, maximumAge:0, timeout:20000});
    }
    window.addEventListener('offline', () => { if(active)record('network_offline'); });
    window.addEventListener('online', () => { if(active)record('network_online'); check(); });
    document.addEventListener('visibilitychange', () => { if(!document.hidden)check(); });
    if(!navigator.onLine)record('network_offline');
    check(); setInterval(check, 300000);
})();
</script>
@endif
@endif
