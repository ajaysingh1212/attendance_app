@if(auth()->user()?->is_admin)
<section class="tracking-board mt-4" id="attendanceReviewBoard">
    <div class="tracking-heading">
        <div>
            <span class="tracking-kicker">ATTENDANCE INTELLIGENCE</span>
            <h4>Employee attendance journey</h4>
            <p>Attendance result, office verification aur punch-in distance ek hi timeline me.</p>
        </div>
        <select id="journeyFilter" class="form-control" style="max-width:200px" aria-label="Filter attendance journeys">
            <option value="">All journeys</option>
            <option value="in_review">Arrival pending</option>
            <option value="holding">Holding active</option>
            <option value="suspicious">Suspicious</option>
            <option value="approved">Completed</option>
            <option value="corrected">Admin/HR corrected</option>
        </select>
    </div>
    <p id="journeySummary" class="text-muted" role="status">Attendance journeys load ho rahi hain...</p>
    <div id="reviewCards" class="journey-grid"></div>
</section>

<div id="journeyToasts" class="journey-toasts" aria-live="polite"></div>

<style>
/* ===== Board shell ===== */
.tracking-board {
    --ink:#10283f; --ink-soft:#5a7088; --line:rgba(16,40,63,.08);
    --teal:#0e7c86; --teal-soft:#e3f4f4;
    --amber:#b7791f; --amber-soft:#fdf3dc;
    --rose:#b4233c; --rose-soft:#fde8ec;
    --green:#1f7a57; --green-soft:#e1f5ec;
    --gold:#9a6b12; --gold-soft:#fff4d6;
    position:relative; isolation:isolate; padding:32px;
    border:1px solid rgba(255,255,255,.92); border-radius:28px;
    background:
        radial-gradient(ellipse at 3% 0%, rgba(177,221,255,.55), transparent 48%),
        radial-gradient(ellipse at 98% 25%, rgba(209,201,250,.38), transparent 43%),
        radial-gradient(ellipse at 40% 100%, rgba(185,238,221,.4), transparent 55%),
        linear-gradient(135deg,#f2f9ff,#f9f8ff 54%,#f0faf6);
    box-shadow: inset 0 1px 0 #fff, 0 18px 50px rgba(79,113,149,.09), 0 2px 6px rgba(79,113,149,.04);
}
.tracking-heading { display:flex; justify-content:space-between; align-items:center; gap:24px; }
.tracking-kicker { display:inline-flex; align-items:center; gap:8px; color:#456b85; font-size:10px; font-weight:800; letter-spacing:2px; }
.tracking-kicker::before { content:''; width:7px; height:7px; border-radius:50%; background:#5ba99b; box-shadow:0 0 0 4px rgba(91,169,155,.12); animation:jPulseDot 2.2s ease-in-out infinite; }
.tracking-heading h4 { font-weight:800; color:#253e57; margin:12px 0 8px; letter-spacing:-.6px; }
.tracking-heading p { color:#60758c; font-size:13px; line-height:1.7; margin-bottom:0; }
.tracking-board #journeyFilter {
    background:rgba(255,255,255,.7); border:1px solid rgba(255,255,255,.95); border-radius:14px;
    min-height:46px; color:#3e5871; font-size:13px; box-shadow:inset 0 1px 0 #fff, 0 5px 18px rgba(74,109,144,.06);
}
.tracking-board #journeyFilter:focus { outline:2px solid #87b5d2; outline-offset:3px; }
.tracking-board #journeySummary {
    display:inline-flex; margin:22px 0; padding:9px 15px; border:1px solid rgba(255,255,255,.9);
    border-radius:999px; background:rgba(255,255,255,.55); color:#536b82!important; font-size:12px;
}

/* ===== Compact grid ===== */
.journey-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(min(100%,300px),1fr)); gap:18px; align-items:start; }

/* ===== Card ===== */
.journey-card {
    --accent:var(--teal);
    position:relative; min-width:0; padding:18px 18px 14px; overflow:hidden;
    background:rgba(255,255,255,.78); border:1px solid rgba(255,255,255,.96); border-radius:20px;
    box-shadow:inset 0 1px 0 #fff, 0 8px 24px rgba(74,109,144,.08);
    transition:transform .35s cubic-bezier(.2,.8,.2,1), box-shadow .35s ease;
}
.journey-card.is-new { animation:jRise .6s cubic-bezier(.2,.8,.2,1) both; animation-delay:calc(var(--i,0) * 55ms); }
.journey-card:hover { transform:translateY(-4px); box-shadow:inset 0 1px 0 #fff, 0 18px 38px rgba(74,109,144,.15); }
/* upar ki animated accent line */
.journey-card::before {
    content:''; position:absolute; top:0; left:0; right:0; height:3px;
    background:linear-gradient(90deg, var(--accent), #8fc5d6, var(--accent)); background-size:200% 100%;
    animation:jFlow 5s linear infinite;
}
.journey-card.in_review { --accent:var(--amber); }
.journey-card.holding { --accent:#c2851f; }
.journey-card.suspicious { --accent:var(--rose); }
.journey-card.approved, .journey-card.verified { --accent:var(--green); }
.journey-card.suspicious::after {
    content:''; position:absolute; inset:0; border-radius:20px; pointer-events:none;
    box-shadow:inset 0 0 0 1px rgba(180,35,60,.22); animation:jAlert 2.4s ease-in-out infinite;
}

/* admin/HR ne dubara sahi kiya - special marker */
.journey-card.corrected {
    background:linear-gradient(160deg,rgba(255,250,235,.95),rgba(255,255,255,.8) 55%);
    border-color:rgba(214,170,76,.55);
    box-shadow:inset 0 1px 0 #fff, 0 10px 28px rgba(183,121,31,.16);
}
.journey-card.corrected::before { background:linear-gradient(90deg,#b7791f,#f3d38a,#b7791f); background-size:200% 100%; height:4px; }
.journey-card.corrected .journey-shine {
    position:absolute; top:0; left:-60%; width:40%; height:100%; pointer-events:none;
    background:linear-gradient(100deg,transparent,rgba(255,236,180,.55),transparent); transform:skewX(-18deg);
    animation:jShine 4.5s ease-in-out infinite;
}
.journey-corrected-tag {
    display:inline-flex; align-items:center; gap:6px; margin:0 0 12px; padding:5px 11px;
    border-radius:999px; font-size:10px; font-weight:800; letter-spacing:.8px; text-transform:uppercase;
    color:var(--gold); background:var(--gold-soft); border:1px solid rgba(214,170,76,.55);
}
.journey-corrected-tag svg { width:12px; height:12px; }

/* ===== Header ===== */
.journey-top { display:flex; align-items:center; gap:12px; }
.journey-avatar {
    flex:0 0 auto; width:40px; height:40px; border-radius:13px; display:grid; place-items:center;
    font-size:14px; font-weight:800; color:#fff; letter-spacing:.5px;
    background:linear-gradient(135deg,var(--accent),#17405f); box-shadow:0 6px 14px rgba(16,40,63,.18);
}
.journey-id { min-width:0; flex:1; }
.journey-id h5 { font-size:15px; font-weight:750; margin:0 0 2px; color:var(--ink); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.journey-id small { display:block; font-size:11px; color:var(--ink-soft)!important; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.journey-badge {
    flex:0 0 auto; display:inline-flex; align-items:center; gap:6px; padding:5px 10px; border-radius:30px;
    font-size:9.5px; font-weight:800; text-transform:uppercase; letter-spacing:.7px;
    background:var(--green-soft); color:var(--green); border:1px solid rgba(31,122,87,.15);
}
.journey-badge::before { content:''; width:6px; height:6px; border-radius:50%; background:currentColor; }
.journey-card.suspicious .journey-badge { background:var(--rose-soft); color:var(--rose); }
.journey-card.suspicious .journey-badge::before, .journey-card.holding .journey-badge::before, .journey-card.in_review .journey-badge::before { animation:jPulseDot 1.6s ease-in-out infinite; }
.journey-card.holding .journey-badge, .journey-card.in_review .journey-badge { background:var(--amber-soft); color:var(--amber); }

/* ===== Compact summary ===== */
.journey-timer {
    margin:14px 0 10px; padding:10px 12px; border-radius:13px; font-size:12px; font-weight:650; color:#2f4d69;
    background:linear-gradient(120deg,rgba(229,241,253,.85),rgba(238,235,252,.6)); border:1px solid rgba(255,255,255,.95);
}
.journey-timer small { display:block; margin-top:3px; font-size:11px; font-weight:500; color:var(--ink-soft); }
.journey-chips { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:6px; }
.journey-chip {
    flex:1 1 0; min-width:110px; padding:8px 10px; border-radius:12px; background:rgba(255,255,255,.75); border:1px solid var(--line);
}
.journey-chip span { display:block; font-size:9.5px; letter-spacing:.8px; text-transform:uppercase; color:var(--ink-soft); }
.journey-chip strong { display:block; margin-top:2px; font-size:13.5px; color:var(--ink); }

/* ===== See more (smooth expand) ===== */
.journey-more { display:grid; grid-template-rows:0fr; transition:grid-template-rows .45s cubic-bezier(.2,.8,.2,1); }
.journey-card.open .journey-more { grid-template-rows:1fr; }
.journey-more-inner { overflow:hidden; min-height:0; opacity:0; transition:opacity .35s ease .05s; }
.journey-card.open .journey-more-inner { opacity:1; }
.journey-section { margin-top:12px; padding:12px 14px; border-radius:14px; background:rgba(255,255,255,.6); border:1px solid var(--line); }
.journey-label { display:block; font-size:9.5px; letter-spacing:1px; text-transform:uppercase; color:var(--ink-soft); margin-bottom:5px; font-weight:700; }
.journey-section p, .journey-section div.t { margin:0; font-size:12px; line-height:1.65; color:#3f5b76; overflow-wrap:anywhere; }
.journey-section.review { background:var(--gold-soft); border-color:rgba(214,170,76,.5); }
.journey-section.review .journey-label { color:var(--gold); }

/* ===== Timeline (naya design) ===== */
.jt-head-row { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
.jt-count { font-size:10px; font-weight:800; letter-spacing:.6px; padding:3px 9px; border-radius:999px; color:var(--teal); background:var(--teal-soft); }
.journey-timeline { list-style:none; margin:0; padding:2px 4px 2px 0; max-height:300px; overflow:auto; scrollbar-width:thin; }
.jt-item {
    --tone:#4a7c9b; --tone-soft:#e6f0f7; --tone-ring:rgba(74,124,155,.35);
    position:relative; display:grid; grid-template-columns:30px minmax(0,1fr); column-gap:12px; padding-bottom:14px;
}
.jt-item:last-child { padding-bottom:0; }
.jt-item.tone-teal  { --tone:#0e7c86; --tone-soft:#e3f4f4; --tone-ring:rgba(14,124,134,.35); }
.jt-item.tone-green { --tone:#1f7a57; --tone-soft:#e1f5ec; --tone-ring:rgba(31,122,87,.35); }
.jt-item.tone-amber { --tone:#b7791f; --tone-soft:#fdf3dc; --tone-ring:rgba(183,121,31,.4); }
.jt-item.tone-rose  { --tone:#b4233c; --tone-soft:#fde8ec; --tone-ring:rgba(180,35,60,.35); }
.jt-item.tone-gold  { --tone:#9a6b12; --tone-soft:#fff4d6; --tone-ring:rgba(154,107,18,.4); }

/* left rail: node + connecting line */
.jt-rail { position:relative; display:flex; flex-direction:column; align-items:center; }
.jt-node {
    position:relative; z-index:1; flex:0 0 auto; width:30px; height:30px; border-radius:50%; display:grid; place-items:center;
    color:var(--tone); background:var(--tone-soft); box-shadow:0 0 0 2px #fff, 0 0 0 3px var(--tone-ring), 0 5px 12px rgba(16,40,63,.12);
}
.jt-node svg { width:14px; height:14px; }
.jt-line { flex:1 1 auto; width:2px; margin:4px 0 -10px; border-radius:2px; background:linear-gradient(to bottom, var(--tone-ring), rgba(16,40,63,.08)); }
.jt-item:last-child .jt-line { display:none; }
.jt-item.is-latest .jt-node::after {
    content:''; position:absolute; inset:-5px; border-radius:50%; border:2px solid var(--tone); opacity:.5; animation:jRing 2s ease-out infinite;
}

/* right: event card */
.jt-body {
    min-width:0; padding:9px 12px; border-radius:12px; background:rgba(255,255,255,.85); border:1px solid var(--line);
    border-left:3px solid var(--tone); box-shadow:0 3px 10px rgba(16,40,63,.04);
    transition:transform .25s ease, box-shadow .25s ease;
}
.jt-body:hover { transform:translateX(3px); box-shadow:0 8px 18px rgba(16,40,63,.09); }
.jt-top { display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; }
.jt-title { font-size:12.5px; font-weight:750; color:var(--ink); text-transform:capitalize; display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
.jt-latest { font-size:8.5px; font-weight:800; letter-spacing:.8px; text-transform:uppercase; padding:2px 6px; border-radius:999px; color:#fff; background:var(--tone); }
.jt-status { font-size:9.5px; font-weight:800; letter-spacing:.6px; text-transform:uppercase; padding:3px 8px; border-radius:999px; color:var(--tone); background:var(--tone-soft); }
.jt-time { display:flex; align-items:center; gap:6px; flex-wrap:wrap; margin-top:4px; font-size:10.5px; color:var(--ink-soft); }
.jt-gap { padding:1px 7px; border-radius:999px; background:rgba(16,40,63,.06); font-weight:700; }
.jt-note { margin:6px 0 0; font-size:11.5px; line-height:1.6; color:#3f5b76; overflow-wrap:anywhere; }
.jt-map {
    display:inline-flex; align-items:center; gap:5px; margin-top:7px; padding:4px 9px; border-radius:8px; font-size:10.5px; font-weight:700;
    color:var(--tone)!important; background:var(--tone-soft); text-decoration:none!important; transition:filter .2s ease;
}
.jt-map:hover { filter:brightness(.95); }
.jt-map svg { width:11px; height:11px; }

/* card khulne par events ek ke baad ek aate hain */
.journey-card.open .jt-item { animation:jTlIn .5s cubic-bezier(.2,.8,.2,1) both; animation-delay:calc(var(--k,0) * 70ms + 120ms); }

/* ===== Footer ===== */
.journey-foot { display:grid; grid-template-columns:max-content minmax(0,1fr); align-items:start; gap:12px; margin-top:12px; padding-top:10px; border-top:1px solid var(--line); }
.journey-punch-location { min-width:0; padding:9px 11px; border-radius:12px; background:rgba(230,242,249,.7); border:1px solid rgba(138,180,203,.22); }
.journey-punch-location .journey-label { margin-bottom:5px; display:flex; align-items:center; gap:5px; }
.journey-punch-location svg { width:12px; height:12px; flex-shrink:0; }
.journey-punch-address { margin:0; color:#344f68; font-size:11.5px; line-height:1.65; overflow-wrap:anywhere; white-space:pre-line; }
.journey-punch-distance { display:block; margin-top:7px; font-size:11px; font-weight:750; color:#236779; }
.journey-punch-source { display:block; margin-top:3px; font-size:10px; color:var(--ink-soft); line-height:1.5; }
.journey-punch-location a { display:inline-flex; margin-top:6px; font-size:10.5px; font-weight:700; color:var(--teal); }
.journey-foot .journey-actions { grid-column:1 / -1; justify-content:flex-end; }
.journey-foot .journey-toggle { white-space:nowrap; }
.journey-toggle {
    display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:10px; cursor:pointer;
    font-size:12px; font-weight:700; color:var(--teal); background:var(--teal-soft); border:1px solid rgba(14,124,134,.18);
    transition:background .25s ease, transform .2s ease;
}
.journey-toggle:hover { background:#cdeaec; transform:translateY(-1px); }
.journey-toggle svg { width:12px; height:12px; transition:transform .35s ease; }
.journey-card.open .journey-toggle svg { transform:rotate(180deg); }
.journey-actions { display:flex; gap:6px; flex-wrap:wrap; }
.journey-actions button { border-radius:10px; font-size:11.5px; padding:6px 11px; font-weight:650; transition:transform .2s ease, background .25s ease; }
.journey-actions button:hover { transform:translateY(-1px); }
.tracking-board .journey-actions .btn-success { background:var(--green-soft); color:var(--green); border:1px solid #b9decd; }
.tracking-board .journey-actions .btn-success:hover { background:#cbe7d9; }
.tracking-board .journey-actions .btn-outline-danger { color:var(--rose); border:1px solid #ebc5cd; background:rgba(255,243,245,.7); }
.tracking-board .journey-actions .btn-outline-danger:hover { background:#f9e1e7; }
.tracking-board button:focus-visible { outline:2px solid #87b5d2; outline-offset:3px; }

/* ===== Toasts ===== */
.journey-toasts { position:fixed; right:20px; bottom:20px; z-index:2000; display:flex; flex-direction:column; gap:10px; }
.journey-toast {
    min-width:240px; max-width:340px; padding:12px 16px; border-radius:14px; font-size:13px; font-weight:600; color:#fff;
    background:linear-gradient(135deg,#10283f,#17405f); box-shadow:0 14px 34px rgba(16,40,63,.3); animation:jToastIn .4s cubic-bezier(.2,.8,.2,1) both;
}
.journey-toast.error { background:linear-gradient(135deg,#8f1d33,#b4233c); }
.journey-toast.out { animation:jToastOut .3s ease forwards; }

/* ===== Animations ===== */
@keyframes jRise { from { opacity:0; transform:translateY(18px) scale(.97); } to { opacity:1; transform:none; } }
@keyframes jFlow { to { background-position:200% 0; } }
@keyframes jPulseDot { 0%,100% { opacity:1; transform:scale(1); } 50% { opacity:.45; transform:scale(1.35); } }
@keyframes jAlert { 0%,100% { opacity:.35; } 50% { opacity:1; } }
@keyframes jShine { 0%,55% { left:-60%; } 100% { left:140%; } }
@keyframes jToastIn { from { opacity:0; transform:translateX(30px); } to { opacity:1; transform:none; } }
@keyframes jToastOut { to { opacity:0; transform:translateX(30px); } }
@keyframes jRing { 0% { transform:scale(.8); opacity:.6; } 100% { transform:scale(1.5); opacity:0; } }
@keyframes jTlIn { from { opacity:0; transform:translateX(-14px); } to { opacity:1; transform:none; } }
@media (prefers-reduced-motion:reduce) {
    .jt-item, .jt-node::after, .jt-body,
    .journey-card, .journey-card::before, .journey-card::after, .journey-shine, .journey-badge::before, .tracking-kicker::before, .journey-more, .journey-more-inner { animation:none!important; transition:none!important; }
}

.attendance-grid { grid-template-columns:repeat(auto-fit,minmax(min(100%,390px),1fr))!important; gap:22px!important; }
.att-card { min-height:230px; padding:22px!important; }
@media(max-width:700px) {
    .tracking-board { padding:19px; border-radius:22px; }
    .tracking-heading { align-items:flex-start; flex-direction:column; gap:16px; }
    .tracking-board #journeyFilter { max-width:100%!important; width:100%; }
    .tracking-heading h4 { font-size:22px; }
    .tracking-board #journeySummary { border-radius:14px; line-height:1.7; }
    .journey-grid { grid-template-columns:1fr; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    let items = [];
    let firstRender = true;               // entrance animation sirf pehli baar
    const expanded = new Set();           // 15s refresh ke baad bhi open cards open rahein
    const root = document.getElementById('reviewCards');
    const filter = document.getElementById('journeyFilter');
    const toastBox = document.getElementById('journeyToasts');

    const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const time = v => v ? new Date(String(v).replace(' ', 'T')).toLocaleString('en-IN') : 'Pending';
    const initials = n => String(n || '?').trim().split(/\s+/).slice(0, 2).map(p => p[0]?.toUpperCase() || '').join('') || '?';
    const meters = v => (v === null || v === undefined) ? 'Not available' : Number(v).toLocaleString('en-IN', {maximumFractionDigits: 2}) + ' m';

    // Use explicit edit evidence; automatic review notes are not admin corrections.
    function correction(item) {
        const viaEvent = (item.events || []).filter(e => ['admin_edit', 'admin_review', 'admin_recorded'].includes(e.type)).slice(-1)[0];
        const isCorrected = item.is_corrected === true || (item.is_corrected === undefined && !!viaEvent);
        return {
            isCorrected,
            by: isCorrected ? (item.reviewed_by_name || '') : '',
            role: isCorrected ? (item.reviewed_by_role || 'Admin') : '',
            note: isCorrected ? (item.review_note || viaEvent?.note || '') : '',
            at: isCorrected ? (item.reviewed_at || viaEvent?.occurred_at || '') : '',
        };
    }

    function matches(item) {
        if (!filter.value) return true;
        if (filter.value === 'corrected') return correction(item).isCorrected;
        return item.status === filter.value || (filter.value === 'approved' && item.status === 'verified');
    }

    // ===== Timeline helpers =====
    const svg = p => `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">${p}</svg>`;
    const ICONS = {
        pin:   svg('<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>'),
        exit:  svg('<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>'),
        enter: svg('<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="m10 17 5-5-5-5"/><path d="M15 12H3"/>'),
        clock: svg('<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>'),
        shield:svg('<path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6l-8-3Z"/><path d="m9 12 2 2 4-4"/>'),
        alert: svg('<path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>'),
        check: svg('<path d="m20 6-11 11-5-5"/>'),
        dot:   svg('<circle cx="12" cy="12" r="3"/>')
    };
    // event type + status se icon aur color decide karte hain
    function eventStyle(e) {
        const t = String(e.type || '').toLowerCase(), s = String(e.status || '').toLowerCase();
        if (/suspicious|reject|absent/.test(t + s)) return {tone:'rose',  icon:ICONS.alert};
        if (/admin|hr|review|correct/.test(t))      return {tone:'gold',  icon:ICONS.shield};
        if (/punch/.test(t))                        return {tone:'teal',  icon:ICONS.pin};
        if (/exit|left|leave|out/.test(t))          return {tone:'amber', icon:ICONS.exit};
        if (/return|enter|arriv|reach|inside/.test(t)) return {tone:'green', icon:ICONS.enter};
        if (/hold/.test(t))                         return {tone:'amber', icon:ICONS.clock};
        if (/verif|approv|complete/.test(t + s))    return {tone:'green', icon:ICONS.check};
        return {tone:'blue', icon:ICONS.dot};
    }
    const shortTime = v => {
        if (!v) return 'Pending';
        const d = new Date(String(v).replace(' ', 'T'));
        return isNaN(d) ? String(v) : d.toLocaleString('en-IN', {day:'2-digit', month:'short', hour:'numeric', minute:'2-digit', hour12:true});
    };
    // pichhle event se kitna time baad hua
    function gapText(prev, cur) {
        if (!prev?.occurred_at || !cur?.occurred_at) return '';
        const diff = Math.round((Date.parse(String(cur.occurred_at).replace(' ', 'T')) - Date.parse(String(prev.occurred_at).replace(' ', 'T'))) / 60000);
        if (isNaN(diff) || diff < 0) return '';
        if (diff < 1) return '1 min se kam baad';
        if (diff < 60) return diff + ' min baad';
        const h = Math.floor(diff / 60), m = diff % 60;
        return h + 'h' + (m ? ' ' + m + 'm' : '') + ' baad';
    }
    function timelineHtml(events) {
        // purane se naye order me sort (time na ho to original order rakho)
        const sorted = events.map((e, i) => ({e, i}))
            .sort((a, b) => {
                const ta = Date.parse(String(a.e.occurred_at || '').replace(' ', 'T')), tb = Date.parse(String(b.e.occurred_at || '').replace(' ', 'T'));
                return (isNaN(ta) || isNaN(tb)) ? a.i - b.i : (ta - tb || a.i - b.i);
            }).map(x => x.e);
        return sorted.map((e, k) => {
            const st = eventStyle(e), gap = k ? gapText(sorted[k - 1], e) : '';
            const latest = k === sorted.length - 1;
            return `<li class="jt-item tone-${st.tone}${latest ? ' is-latest' : ''}" style="--k:${k}">
                <div class="jt-rail"><span class="jt-node">${st.icon}</span><span class="jt-line"></span></div>
                <div class="jt-body">
                    <div class="jt-top">
                        <span class="jt-title">${esc(String(e.type || 'event').replaceAll('_', ' '))}${latest ? '<span class="jt-latest">Latest</span>' : ''}</span>
                        ${e.status ? `<span class="jt-status">${esc(String(e.status).replaceAll('_', ' '))}</span>` : ''}
                    </div>
                    <div class="jt-time"><span>${esc(shortTime(e.occurred_at))}</span>${gap ? `<span class="jt-gap">${esc(gap)}</span>` : ''}</div>
                    ${e.note ? `<p class="jt-note">${esc(e.note)}</p>` : ''}
                    ${e.latitude ? `<a class="jt-map" target="_blank" rel="noopener" href="https://maps.google.com/?q=${encodeURIComponent(e.latitude + ',' + e.longitude)}">${ICONS.pin}Exit location${e.direction ? ' &middot; ' + esc(e.direction) : ''}</a>` : ''}
                </div>
            </li>`;
        }).join('');
    }

    function cardHtml(item, index) {
        const deadline = item.status === 'holding' ? item.holding_deadline : item.deadline;
        const remaining = deadline ? Math.max(0, Math.ceil((Date.parse(deadline) - Date.now()) / 1000)) : 0;
        const duration = Math.floor(remaining / 3600) + 'h ' + Math.floor(remaining % 3600 / 60) + 'm';
        const events = item.events?.length ? item.events : [{type:'punch_in', status:item.status, occurred_at:item.punch_time, note:'Punch-in recorded'}];
        const ev = item.punch_evidence || {};
        const latest = events[events.length - 1];
        const latestLabel = latest?.type ? latest.type.replaceAll('_', ' ') : 'Journey update';
        const latestAt = latest?.occurred_at ? time(latest.occurred_at) : 'No activity recorded';
        const distanceSource = ev.distance_source === 'recorded' ? 'Recorded at punch-in'
            : ev.distance_source === 'recalculated' ? 'Recalculated from saved GPS using current pin' : 'Distance evidence unavailable';
        const fix = correction(item);
        const isOpen = expanded.has(String(item.id));

        const timerText = item.status === 'holding' ? 'Holding remaining: ' + duration
            : item.tracking_completed_at ? 'Completed: ' + time(item.tracking_completed_at)
            : item.status === 'in_review' ? 'Office arrival remaining: ' + duration
            : 'Attendance: ' + (item.attendance_status ?? '');

        const reviewBlock = fix.isCorrected ? `
            <div class="journey-section review">
                <span class="journey-label">Admin/HR correction</span>
                <div class="t"><b>${esc(fix.role || 'Admin/HR')}${fix.by ? ' - ' + esc(fix.by) : ''}</b>${fix.at ? '<br>' + esc(time(fix.at)) : ''}${fix.note ? '<br>Note: ' + esc(fix.note) : ''}</div>
            </div>` : '';

        const unverified = !['approved', 'verified', 'holding'].includes(String(item.status));
        const hasPunchGps = item.punch_latitude !== null && item.punch_latitude !== undefined && String(item.punch_latitude).trim() !== ''
            && item.punch_longitude !== null && item.punch_longitude !== undefined && String(item.punch_longitude).trim() !== ''
            && Number.isFinite(Number(item.punch_latitude)) && Math.abs(Number(item.punch_latitude)) <= 90
            && Number.isFinite(Number(item.punch_longitude)) && Math.abs(Number(item.punch_longitude)) <= 180;
        const punchAddress = String(item.punch_location || '').trim();
        const locationBlock = unverified ? `<div class="journey-punch-location">
            <span class="journey-label">${ICONS.pin}Punch-in location</span>
            <p class="journey-punch-address">${esc(punchAddress || (hasPunchGps ? 'Address save nahi hua. GPS: '+item.punch_latitude+', '+item.punch_longitude : 'Punch-in address / GPS record available nahi hai.'))}</p>
            <span class="journey-punch-distance">Pin se distance: ${esc(meters(ev.distance_meters))}</span>
            <span class="journey-punch-source">${esc(distanceSource)}</span>
            ${hasPunchGps ? `<a href="https://maps.google.com/?q=${encodeURIComponent(item.punch_latitude+','+item.punch_longitude)}" target="_blank" rel="noopener">Map par dekhein &nearr;</a>` : ''}
        </div>` : '';

        const steps = timelineHtml(events);

        const actions = item.status === 'suspicious'
            ? `<div class="journey-actions"><button class="btn btn-success" data-action="approve" data-id="${esc(item.id)}">Approve</button><button class="btn btn-outline-danger" data-action="reject" data-id="${esc(item.id)}">Mark absent</button></div>` : '';

        return `<article class="journey-card ${esc(item.status)}${fix.isCorrected ? ' corrected' : ''}${isOpen ? ' open' : ''}${firstRender ? ' is-new' : ''}" data-card="${esc(item.id)}" style="--i:${index}">
            ${fix.isCorrected ? '<span class="journey-shine"></span>' : ''}
            ${fix.isCorrected ? `<div class="journey-corrected-tag"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>${esc(fix.role || 'Admin')} ne update kiya</div>` : ''}
            <div class="journey-top">
                <div class="journey-avatar">${esc(initials(item.employee))}</div>
                <div class="journey-id"><h5>${esc(item.employee)}</h5><small>${esc(item.employee_code)} | ${esc(item.office || 'Assigned office')}</small></div>
                <span class="journey-badge">${esc(String(item.status).replaceAll('_', ' '))}</span>
            </div>
            <div class="journey-timer">${esc(timerText)}<small>${esc(latestLabel)} &middot; ${esc(latestAt)}</small></div>
            <div class="journey-chips">
                <div class="journey-chip"><span>Punch-in distance</span><strong>${esc(meters(ev.distance_meters))}</strong></div>
                <div class="journey-chip"><span>Allowed radius</span><strong>${esc(meters(ev.radius_meters))}</strong></div>
            </div>
            <div class="journey-more"><div class="journey-more-inner">
                <div class="journey-section">
                    <span class="journey-label">Evidence</span>
                    <div class="t">${esc(distanceSource)}${ev.area_name ? ' &middot; ' + esc(ev.area_name) : ''}<br>Location verification: ${esc(ev.location_verification || 'Not recorded')} &middot; Current policy: ${esc(ev.current_location_policy || 'Unknown')}${(ev.inside_current_radius === null || ev.inside_current_radius === undefined) ? '' : '<br>Saved punch distance current radius ke ' + (ev.inside_current_radius ? 'andar' : 'bahar') + ' hai.'}</div>
                </div>
                <div class="journey-section"><span class="journey-label">Why this status?</span><p>${esc(ev.reason || 'Status explanation is unavailable for this record.')}</p></div>
                ${reviewBlock}
                <div class="journey-section">
                    <div class="jt-head-row"><span class="journey-label" style="margin:0">Timeline</span><span class="jt-count">${events.length} event${events.length > 1 ? 's' : ''}</span></div>
                    <ol class="journey-timeline">${steps}</ol>
                </div>
            </div></div>
            <div class="journey-foot">
                <button type="button" class="journey-toggle" data-toggle="${esc(item.id)}" aria-expanded="${isOpen}">
                    <span>${isOpen ? 'Kam dikhayein' : 'Aur dekhein'}</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                ${locationBlock}
                ${actions}
            </div>
        </article>`;
    }

    function render() {
        const visible = items.filter(matches);
        const count = s => items.filter(i => i.status === s).length;
        const correctedCount = items.filter(i => correction(i).isCorrected).length;
        document.getElementById('journeySummary').textContent =
            `${items.length} journeys | ${count('holding')} holding | ${count('suspicious')} suspicious | ${correctedCount} corrected`;
        root.innerHTML = visible.map(cardHtml).join('') || '<p class="text-muted p-4">Is filter me koi attendance journey nahi mili.</p>';
        firstRender = false;
    }

    function toast(message, type) {
        const el = document.createElement('div');
        el.className = 'journey-toast' + (type === 'error' ? ' error' : '');
        el.textContent = message;
        toastBox.appendChild(el);
        setTimeout(() => { el.classList.add('out'); setTimeout(() => el.remove(), 320); }, 3200);
    }

    async function load() {
        try {
            const response = await fetch(@json(route('admin.attendance-reviews.feed')), {headers:{Accept:'application/json'}});
            if (!response.ok) throw new Error('Unable to load attendance');
            items = (await response.json()).items;
            render();
        } catch (error) {
            document.getElementById('journeySummary').textContent = 'Attendance feed abhi available nahi hai. Thodi der me dobara try hoga.';
        }
    }

    // See more / See less - poora re-render kiye bina smooth animation ke liye class toggle
    root.addEventListener('click', async event => {
        const toggle = event.target.closest('[data-toggle]');
        if (toggle) {
            const id = toggle.dataset.toggle, card = toggle.closest('.journey-card');
            const open = card.classList.toggle('open');
            open ? expanded.add(id) : expanded.delete(id);
            toggle.setAttribute('aria-expanded', open);
            toggle.querySelector('span').textContent = open ? 'Kam dikhayein' : 'Aur dekhein';
            return;
        }
        const button = event.target.closest('[data-action]');
        if (!button) return;
        const note = prompt('Admin review note (zaroori hai):');
        if (!note?.trim()) return;
        button.disabled = true;
        try {
            const response = await fetch(@json(url('admin/attendance-reviews')) + '/' + button.dataset.id, {
                method:'PATCH',
                headers:{'Content-Type':'application/json', Accept:'application/json', 'X-CSRF-TOKEN':@json(csrf_token())},
                body:JSON.stringify({status:button.dataset.action, review_note:note.trim()})
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Update failed');
            toast(button.dataset.action === 'approve' ? 'Attendance approve ho gayi.' : 'Attendance absent mark kar di gayi.');
            await load();
        } catch (error) {
            toast(error.message || 'Kuch galat ho gaya, dobara try karein.', 'error');
        } finally { button.disabled = false; }
    });

    filter.addEventListener('change', render);
    load();
    setInterval(load, 15000);
});
</script>
@endif