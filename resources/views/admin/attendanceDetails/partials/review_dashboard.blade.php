@if(auth()->user()?->is_admin)
<section class="tracking-board mt-4" id="attendanceReviewBoard">
    <div class="tracking-heading">
        <div><span class="tracking-kicker">ATTENDANCE INTELLIGENCE</span><h4>Employee attendance journey</h4><p>Punch-in, office arrival, holding verification and exceptions in one timeline.</p></div>
        <select id="journeyFilter" class="form-control" style="max-width:190px" aria-label="Filter attendance journeys">
            <option value="">All journeys</option><option value="in_review">Arrival pending</option><option value="holding">Holding active</option><option value="suspicious">Suspicious</option><option value="approved">Completed</option>
        </select>
    </div>
    <p id="journeySummary" class="text-muted" role="status">Loading attendance journeys...</p>
    <div id="reviewCards" class="journey-grid"></div>
</section>
<style>
.tracking-board {
    position: relative;
    isolation: isolate;
    padding: 32px;
    border: 1px solid rgba(255,255,255,.92);
    border-radius: 28px;
    background:
        radial-gradient(ellipse at 3% 0%, rgba(177,221,255,.55), transparent 48%),
        radial-gradient(ellipse at 98% 25%, rgba(209,201,250,.38), transparent 43%),
        radial-gradient(ellipse at 40% 100%, rgba(185,238,221,.4), transparent 55%),
        linear-gradient(135deg, #f2f9ff, #f9f8ff 54%, #f0faf6);
    box-shadow: inset 0 1px 0 #fff, 0 18px 50px rgba(79,113,149,.09), 0 2px 6px rgba(79,113,149,.04);
}
.tracking-heading { display:flex; justify-content:space-between; align-items:center; gap:24px; }
.tracking-kicker {
    display:inline-flex; align-items:center; gap:8px; color:#456b85;
    font-size:10px; font-weight:800; letter-spacing:2px;
}
.tracking-kicker::before {
    content:''; width:7px; height:7px; border-radius:50%; background:#5ba99b;
    box-shadow:0 0 0 4px rgba(91,169,155,.12);
}
.tracking-heading h4 { font-weight:800; color:#253e57; margin:12px 0 8px; letter-spacing:-.6px; }
.tracking-heading p { color:#60758c; font-size:13px; line-height:1.7; margin-bottom:0; }
.tracking-board #journeyFilter {
    background:rgba(255,255,255,.67); border:1px solid rgba(255,255,255,.95);
    border-radius:14px; min-height:46px; color:#3e5871; font-size:13px;
    box-shadow:inset 0 1px 0 #fff, 0 5px 18px rgba(74,109,144,.06);
}
.tracking-board #journeyFilter:focus { outline:2px solid #87b5d2; outline-offset:3px; }
.tracking-board #journeySummary {
    display:inline-flex; margin:22px 0; padding:9px 15px;
    border:1px solid rgba(255,255,255,.9); border-radius:999px;
    background:rgba(255,255,255,.5); color:#536b82!important; font-size:12px;
}
.journey-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(min(100%,420px),1fr)); gap:22px; }
.journey-card {
    position:relative; min-width:0; padding:25px; min-height:300px;
    background:rgba(255,255,255,.66); border:1px solid rgba(255,255,255,.95);
    border-radius:22px; box-shadow:inset 0 1px 0 #fff, 0 10px 28px rgba(74,109,144,.07);
}
.journey-card::before {
    content:''; position:absolute; top:0; left:24px; right:24px; height:1px;
    background:linear-gradient(90deg, transparent, #fff, transparent); pointer-events:none;
}
@supports ((backdrop-filter:blur(20px)) or (-webkit-backdrop-filter:blur(20px))) {
    .tracking-board .journey-card, .tracking-board #journeyFilter, .tracking-board #journeySummary {
        -webkit-backdrop-filter:blur(20px) saturate(130%);
        backdrop-filter:blur(20px) saturate(130%);
    }
}
.journey-top { display:flex; justify-content:space-between; align-items:flex-start; gap:14px; }
.journey-top > div { min-width:0; }
.journey-top h5 { font-size:17px; font-weight:750; margin:0 0 5px; color:#29425b; overflow-wrap:anywhere; }
.journey-top small { font-size:11px; color:#647a8f!important; }
.journey-badge {
    background:rgba(220,244,234,.85); color:#256952; border:1px solid rgba(255,255,255,.85);
    border-radius:30px; padding:7px 11px; font-size:10px; font-weight:700;
    height:fit-content; text-transform:uppercase; letter-spacing:.7px; flex-shrink:0;
}
.journey-card.suspicious .journey-badge { background:rgba(255,229,231,.9); color:#a04150; }
.journey-card.holding .journey-badge, .journey-card.in_review .journey-badge { background:rgba(255,242,211,.9); color:#89611e; }
.journey-timer {
    background:linear-gradient(120deg,rgba(229,241,253,.8),rgba(238,235,252,.62));
    border:1px solid rgba(255,255,255,.95); border-radius:16px;
    padding:16px 18px; margin:20px 0; font-size:13px; font-weight:600; color:#3d5a78;
}
.journey-update {
    display:block; margin-top:11px; padding-top:11px;
    border-top:1px solid rgba(255,255,255,.95); font-weight:400;
}
.journey-update-label { display:block; font-size:10px; letter-spacing:1px; text-transform:uppercase; color:#637b92; margin-bottom:4px; }
.journey-update-value { font-size:12px; color:#3f5b76; line-height:1.6; }
.journey-steps { padding-left:18px; margin:18px 0; border-left:2px solid #dce7f1; list-style:none; max-height:420px; overflow:auto; }
.journey-steps li { position:relative; padding:0 0 22px 10px; font-size:12px; color:#54667c; }
.journey-steps li:before { content:''; position:absolute; left:-24px; top:4px; width:10px; height:10px; background:#76a8b4; border:2px solid white; border-radius:50%; }
.journey-steps strong { display:block; color:#314c66; font-size:13px; }
.journey-steps time { font-size:11px; color:#687e94; }
.journey-steps p { margin:5px 0; line-height:1.6; overflow-wrap:anywhere; }
.journey-actions { display:flex; gap:8px; flex-wrap:wrap; padding-top:10px; border-top:1px solid rgba(255,255,255,.9); }
.journey-actions button { border-radius:11px; font-size:12px; padding:9px 14px; }
.tracking-board .journey-actions .btn-success { background:#e0f2ea; color:#24654f; border:1px solid #b9decd; }
.tracking-board .journey-actions .btn-success:hover { background:#cbe7d9; }
.tracking-board .journey-actions .btn-outline-danger { color:#a04150; border:1px solid #ebc5cd; background:rgba(255,243,245,.65); }
.tracking-board .journey-actions .btn-outline-danger:hover { background:#f9e1e7; }
.tracking-board button:focus-visible { outline:2px solid #87b5d2; outline-offset:3px; }
.attendance-grid { grid-template-columns:repeat(auto-fit,minmax(min(100%,390px),1fr))!important; gap:22px!important; }
.att-card { min-height:230px; padding:22px!important; }
@media(max-width:700px) {
    .tracking-board { padding:19px; border-radius:22px; }
    .tracking-heading { align-items:flex-start; flex-direction:column; gap:16px; }
    .tracking-board #journeyFilter { max-width:100%!important; width:100%; }
    .tracking-heading h4 { font-size:22px; }
    .journey-card { padding:18px; }
    .journey-top { flex-wrap:wrap; }
    .tracking-board #journeySummary { border-radius:14px; line-height:1.7; }
}
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
    let items=[];
    const root=document.getElementById('reviewCards'), filter=document.getElementById('journeyFilter');
    const escape=value=>String(value??'').replace(/[&<>"']/g,char=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
    const time=value=>value?new Date(String(value).replace(' ','T')).toLocaleString('en-IN'):'Pending';
    function render() {
        const visible=items.filter(item=>!filter.value || item.status===filter.value || (filter.value==='approved' && item.status==='verified'));
        document.getElementById('journeySummary').textContent=`${items.length} journeys | ${items.filter(i=>i.status==='holding').length} holding | ${items.filter(i=>i.status==='suspicious').length} suspicious`;
        root.innerHTML=visible.map(item=>{
            const deadline=item.status==='holding'?item.holding_deadline:item.deadline;
            const remaining=deadline?Math.max(0,Math.ceil((Date.parse(deadline)-Date.now())/1000)):0;
            const duration=Math.floor(remaining/3600)+'h '+Math.floor(remaining%3600/60)+'m';
            const events=item.events?.length?item.events:[{type:'punch_in',status:item.status,occurred_at:item.punch_time,note:'Punch-in recorded'}];
            const latestEvent=events[events.length-1];
            const latestActivity=latestEvent?.occurred_at ? time(latestEvent.occurred_at) : 'No activity recorded';
            const latestLabel=latestEvent?.type ? latestEvent.type.replaceAll('_',' ') : 'Journey update';
            return `<article class="journey-card ${escape(item.status)}"><div class="journey-top"><div><h5>${escape(item.employee)}</h5><small class="text-muted">${escape(item.employee_code)} | ${escape(item.office||'Assigned office')}</small></div><span class="journey-badge">${escape(item.status.replaceAll('_',' '))}</span></div>
            <div class="journey-timer">${item.status==='holding'?'Holding remaining: '+duration:item.tracking_completed_at?'Completed: '+escape(time(item.tracking_completed_at)):item.status==='in_review'?'Office arrival remaining: '+duration:'Attendance: '+escape(item.attendance_status)}<span class="journey-update"><span class="journey-update-label">Latest activity</span><span class="journey-update-value">${escape(latestLabel)} &middot; ${escape(latestActivity)}</span></span></div>
            <ol class="journey-steps">${events.map(event=>`<li><time>${escape(time(event.occurred_at))}</time><strong>${escape(event.type.replaceAll('_',' '))} | ${escape(event.status)}</strong><p>${escape(event.note)}</p>${event.latitude?`<span>Exit direction: ${escape(event.direction||'Unknown')} | <a target="_blank" rel="noopener" href="https://maps.google.com/?q=${encodeURIComponent(event.latitude+','+event.longitude)}">Exit location</a></span>`:''}</li>`).join('')}</ol>
            ${item.status==='suspicious'?`<div class="journey-actions"><button class="btn btn-success" data-action="approve" data-id="${item.id}">Approve attendance</button><button class="btn btn-outline-danger" data-action="reject" data-id="${item.id}">Mark absent</button></div>`:''}</article>`;
        }).join('')||'<p class="text-muted p-4">No attendance journeys match this filter.</p>';
    }
    async function load() {
        try {
            const response=await fetch(@json(route('admin.attendance-reviews.feed')),{headers:{Accept:'application/json'}});
            if(!response.ok)throw new Error('Unable to load attendance');
            items=(await response.json()).items; render();
        } catch(error) {document.getElementById('journeySummary').textContent='Attendance feed unavailable. Retrying shortly.';}
    }
    root.addEventListener('click',async event=>{
        const button=event.target.closest('[data-action]');if(!button)return;
        const note=prompt('Admin review note (required):');if(!note?.trim())return;
        button.disabled=true;
        try {
            const response=await fetch(@json(url('admin/attendance-reviews'))+'/'+button.dataset.id,{
                method:'PATCH',headers:{'Content-Type':'application/json',Accept:'application/json','X-CSRF-TOKEN':@json(csrf_token())},
                body:JSON.stringify({status:button.dataset.action,review_note:note.trim()})
            });
            const result=await response.json();if(!response.ok)throw new Error(result.message||'Update failed');await load();
        }catch(error){alert(error.message);}finally{button.disabled=false;}
    });
    filter.addEventListener('change',render);load();setInterval(load,15000);
});
</script>
@endif
