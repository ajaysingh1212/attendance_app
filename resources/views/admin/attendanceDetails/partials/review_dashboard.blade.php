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
.tracking-board{background:#f6f9fd;border:1px solid #dce5f0;border-radius:22px;padding:28px}.tracking-heading{display:flex;justify-content:space-between;align-items:center;gap:20px}.tracking-kicker{color:#397084;font-size:10px;font-weight:800;letter-spacing:2px}.tracking-heading h4{font-weight:800;color:#18334d;margin:7px 0}.tracking-heading p{color:#718096;font-size:13px}.journey-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,420px),1fr));gap:22px}.journey-card{background:white;border:1px solid #e1e8f0;border-radius:18px;padding:24px;min-height:300px;box-shadow:0 6px 24px #18334d06}.journey-top{display:flex;justify-content:space-between;gap:14px}.journey-top h5{font-weight:750;margin:0}.journey-badge{background:#e8f5ef;color:#16634c;border-radius:30px;padding:7px 12px;font-size:11px;height:fit-content;text-transform:uppercase;letter-spacing:.5px}.journey-card.suspicious .journey-badge{background:#fff0ee;color:#b53535}.journey-card.holding .journey-badge,.journey-card.in_review .journey-badge{background:#fff5df;color:#99620b}.journey-timer{background:#f3f7fb;border-radius:12px;padding:12px 16px;margin:18px 0;font-size:13px;color:#40566f}.journey-steps{padding-left:18px;margin:18px 0;border-left:2px solid #dce7f1;list-style:none;max-height:420px;overflow:auto}.journey-steps li{position:relative;padding:0 0 22px 10px;font-size:12px;color:#54667c}.journey-steps li:before{content:'';position:absolute;left:-24px;top:4px;width:10px;height:10px;background:#327886;border:2px solid white;border-radius:50%}.journey-steps strong{display:block;color:#223b56;font-size:13px}.journey-steps time{font-size:11px;color:#7c8ba0}.journey-steps p{margin:5px 0;line-height:1.5}.journey-actions{display:flex;gap:8px;flex-wrap:wrap}.journey-actions button{border-radius:8px;font-size:12px}.attendance-grid{grid-template-columns:repeat(auto-fit,minmax(min(100%,390px),1fr))!important;gap:22px!important}.att-card{min-height:230px;padding:22px!important}@media(max-width:700px){.tracking-board{padding:16px}.tracking-heading{align-items:flex-start;flex-direction:column}.journey-card{padding:18px}}
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
            return `<article class="journey-card ${escape(item.status)}"><div class="journey-top"><div><h5>${escape(item.employee)}</h5><small class="text-muted">${escape(item.employee_code)} | ${escape(item.office||'Assigned office')}</small></div><span class="journey-badge">${escape(item.status.replaceAll('_',' '))}</span></div>
            <div class="journey-timer">${item.status==='holding'?'Holding remaining: '+duration:item.tracking_completed_at?'Completed: '+escape(time(item.tracking_completed_at)):item.status==='in_review'?'Office arrival remaining: '+duration:'Attendance: '+escape(item.attendance_status)}<br><small>Office arrival: ${escape(time(item.entered_at))}</small></div>
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
