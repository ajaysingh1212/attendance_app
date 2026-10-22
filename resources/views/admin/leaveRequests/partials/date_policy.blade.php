<dialog class="leave-date-modal" id="leaveDatePolicy" aria-labelledby="leaveDatePolicyTitle">
    <small class="text-warning font-weight-bold">PLEASE CHOOSE A FUTURE DATE</small>
    <h4 id="leaveDatePolicyTitle" class="mt-3">Current date ka leave apply nahi kar sakte</h4>
    <p>Aap aaj ka leave apply nahi kar sakte. Please next day (kal) ya uske baad ki date select karein.</p>
    <p class="small">Earliest available date: <strong>{{ now()->addDay()->format('d M Y') }}</strong></p>
    <button type="button" class="btn btn-primary" id="closeLeaveDatePolicy">Choose another date</button>
</dialog>
<script>
document.addEventListener('DOMContentLoaded',()=>{
    const today=@json(now()->toDateString()), start=document.getElementById('date_from'), end=document.getElementById('date_to'), modal=document.getElementById('leaveDatePolicy');
    const original=@json(isset($leaveRequest) ? $leaveRequest->date_from : null);
    function validateDate(event){
        if(start?.value && start.value<=today && start.value!==original){
            event?.preventDefault();start.value='';modal.showModal();return false;
        }
        if(end)end.min=start?.value || @json(now()->addDay()->toDateString());
        return true;
    }
    start?.addEventListener('change',validateDate);
    end?.addEventListener('change',event=>{
        if(end.value && end.value<=today && start.value!==original){event.preventDefault();end.value='';modal.showModal();}
    });
    start?.form?.addEventListener('submit',validateDate);
    document.getElementById('closeLeaveDatePolicy').addEventListener('click',()=>{modal.close();start?.focus();});
});
</script>
