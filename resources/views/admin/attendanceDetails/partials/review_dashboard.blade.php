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

.journey-steps { padding-left:16px; margin:6px 0 0; border-left:2px solid #dce7f1; list-style:none; max-height:260px; overflow:auto; }
.journey-steps li { position:relative; padding:0 0 14px 10px; font-size:12px; color:#54667c; }
.journey-steps li:before { content:''; position:absolute; left:-22px; top:4px; width:10px; height:10px; background:#76a8b4; border:2px solid #fff; border-radius:50%; }
.journey-steps strong { display:block; color:#314c66; font-size:12.5px; text-transform:capitalize; }
.journey-steps time { font-size:10.5px; color:#687e94; }
.journey-steps p { margin:3px 0; line-height:1.55; overflow-wrap:anywhere; }

/* ===== Footer ===== */
.journey-foot { display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; margin-top:12px; padding-top:10px; border-top:1px solid var(--line); }
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
@media (prefers-reduced-motion:reduce) {
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

    // Admin/HR ne manually sahi kiya ya nahi - backend ke alag-alag fields ko defensively check karte hain
    function correction(item) {
        const by = item.reviewed_by_name || item.reviewed_by || item.corrected_by || '';
        const role = String(item.reviewed_by_role || item.reviewer_role || item.corrected_by_role || '').toUpperCase();
        const note = item.review_note || '';
        const at = item.reviewed_at || item.corrected_at || '';
        const viaEvent = (item.events || []).find(e => /(admin|hr|review|correct|approve|reject)/i.test(e.type || '') && !/punch/i.test(e.type || ''));
        const isCorrected = !!(item.is_corrected || by || note || at || viaEvent);
        return { isCorrected, by: typeof by === 'object' ? (by?.name || '') : by, role, note, at: at || viaEvent?.occurred_at || '' };
    }

    function matches(item) {
        if (!filter.value) return true;
        if (filter.value === 'corrected') return correction(item).isCorrected;
        return item.status === filter.value || (filter.value === 'approved' && item.status === 'verified');
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

        const steps = events.map(e => `<li><time>${esc(time(e.occurred_at))}</time><strong>${esc((e.type || '').replaceAll('_', ' '))} | ${esc(e.status)}</strong><p>${esc(e.note)}</p>${e.latitude ? `<span>Exit direction: ${esc(e.direction || 'Unknown')} | <a target="_blank" rel="noopener" href="https://maps.google.com/?q=${encodeURIComponent(e.latitude + ',' + e.longitude)}">Exit location</a></span>` : ''}</li>`).join('');

        const actions = item.status === 'suspicious'
            ? `<div class="journey-actions"><button class="btn btn-success" data-action="approve" data-id="${esc(item.id)}">Approve</button><button class="btn btn-outline-danger" data-action="reject" data-id="${esc(item.id)}">Mark absent</button></div>` : '';

        return `<article class="journey-card ${esc(item.status)}${fix.isCorrected ? ' corrected' : ''}${isOpen ? ' open' : ''}${firstRender ? ' is-new' : ''}" data-card="${esc(item.id)}" style="--i:${index}">
            ${fix.isCorrected ? '<span class="journey-shine"></span>' : ''}
            ${fix.isCorrected ? `<div class="journey-corrected-tag"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>${esc(fix.role || 'Admin/HR')} ne sahi kiya</div>` : ''}
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
                <div class="journey-section"><span class="journey-label">Timeline</span><ol class="journey-steps">${steps}</ol></div>
            </div></div>
            <div class="journey-foot">
                <button type="button" class="journey-toggle" data-toggle="${esc(item.id)}" aria-expanded="${isOpen}">
                    <span>${isOpen ? 'Kam dikhayein' : 'Aur dekhein'}</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </button>
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