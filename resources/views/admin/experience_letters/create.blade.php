@extends('layouts.admin')

@section('content')

<style>
.card-box{
    border-radius:14px;
    box-shadow:0 4px 18px rgba(0,0,0,0.06);
    border:none;
}
.section-title{
    font-weight:600;
    font-size:16px;
    color:#444;
    margin-bottom:15px;
}
.info-box{
    background:#f8f9fa;
    padding:15px;
    border-radius:10px;
    margin-bottom:10px;
    border-left:4px solid #0d6efd;
}
.summary-card{
    background:#ffffff;
    border-radius:14px;
    box-shadow:0 5px 20px rgba(0,0,0,0.05);
}
.increment-history-card{
    background:#f8f9fa;
    padding:15px;
    border-radius:10px;
    margin-bottom:10px;
    border-left:4px solid #198754;
}
</style>

<div class="container">

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card card-box p-4">
<h4 class="mb-4">Create Experience Letter</h4>

<form action="{{ route('admin.experience-letters.store') }}" method="POST">
@csrf

<div class="row">

<div class="col-md-6 mb-3">
<label>Select Employee</label>
<select name="employee_id" id="employee_id" class="form-control" required>
<option value="">-- Select Employee --</option>
@foreach($employees as $emp)
<option value="{{ $emp->id }}">
{{ $emp->employee_code }} - {{ $emp->full_name }}
</option>
@endforeach
</select>
</div>

<div class="col-md-3 mb-3">
<label>Date of Joining</label>
<input type="date" name="date_of_joining" id="date_of_joining" class="form-control">
</div>

<div class="col-md-3 mb-3">
<label>Resignation Date</label>
<input type="date" name="date_of_resignation" class="form-control">
</div>

<div class="col-md-3 mb-3">
<label>Last Working Date</label>
<input type="date" name="last_working_date" class="form-control">
</div>

<div class="col-md-3 mb-3">
<label>Designation</label>
<input type="text" name="designation" id="designation" class="form-control">
</div>

<div class="col-md-3 mb-3">
<label>Department</label>
<input type="text" name="department" id="department" class="form-control">
</div>

<div class="col-md-3 mb-3">
<label>Last Drawn Salary</label>
<input type="number" step="0.01" name="last_drawn_salary" id="salary" class="form-control">
</div>

</div>

<!-- Employee Summary -->
<div class="summary-card mt-4 p-4" id="employee_summary_card" style="display:none;">
<h5 class="mb-3">Employee Summary</h5>

<div class="row">

<div class="col-md-3">
<div class="info-box">
<strong>Joining Date</strong>
<div id="summary_joining">-</div>
</div>
</div>

<div class="col-md-3">
<div class="info-box">
<strong>Current Position</strong>
<div id="summary_position">-</div>
</div>
</div>

<div class="col-md-3">
<div class="info-box">
<strong>Current Salary</strong>
<div id="summary_salary">-</div>
</div>
</div>

<div class="col-md-3">
<div class="info-box">
<strong>Increment Status</strong>
<div id="summary_increment">No Increment</div>
</div>
</div>

</div>

<!-- Increment History -->
<div class="row mt-3" id="increment_history_container"></div>

</div>

<hr>

<h5 class="section-title">Notice Period Details</h5>

<div class="row">
<div class="col-md-4 mb-3">
<label>Notice Period (Days)</label>
<input type="number" name="notice_period_days" class="form-control">
</div>

<div class="col-md-4 mb-3">
<label>Notice Served?</label>
<select name="notice_served" class="form-control">
<option value="1">Yes</option>
<option value="0">No</option>
</select>
</div>

<div class="col-md-4 mb-3">
<label>Days Served</label>
<input type="number" name="notice_served_days" class="form-control">
</div>
</div>

<hr>

<h5 class="section-title">Increment Details</h5>

<div class="row">
<div class="col-md-4 mb-3">
<label>Had Increment?</label>
<select name="had_increment" id="had_increment" class="form-control">
<option value="0">No</option>
<option value="1">Yes</option>
</select>
</div>

<div class="col-md-4 mb-3">
<label>Last Increment Date</label>
<input type="date" name="last_increment_date" id="increment_date" class="form-control">
</div>

<div class="col-md-4 mb-3">
<label>Increment Amount</label>
<input type="number" step="0.01" name="increment_amount" id="increment_amount" class="form-control">
</div>
</div>

<hr>

<div class="mb-3">
<label>Additional Remark</label>
<textarea name="additional_remark" rows="3" class="form-control"></textarea>
</div>

<button type="submit" class="btn btn-primary">Save Experience Letter</button>

</form>
</div>
</div>

@endsection


@section('scripts')

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
$(document).ready(function(){

$('#employee_id').on('change', function(){

let id = $(this).val();

if(!id){
$('#employee_summary_card').hide();
return;
}

$.ajax({
url: "/admin/employee-details/" + id,
type: "GET",
success: function(res){

let emp = res.employee;
let increments = res.all_increments ?? [];

$('#increment_history_container').html('');

if(emp){

$('#employee_summary_card').fadeIn();

$('#date_of_joining').val(emp.date_of_joining);
$('#designation').val(emp.position);
$('#department').val(emp.department);

// Default salary from employee
let currentSalary = emp.basic_salary;

// If increments exist → use latest increment salary
if(increments.length > 0){

let latest = increments[increments.length - 1];

currentSalary = latest.new_gross_salary;

$('#summary_increment').html('<span class="text-success">Increment Given</span>');

$('#had_increment').val(1);
$('#increment_date').val(latest.increment_month);
$('#increment_amount').val(
    latest.new_gross_salary - latest.old_gross_salary
);

// Render all increments history
increments.forEach(function(inc, index){

let html = `
<div class="col-md-12">
<div class="increment-history-card">
<h6>Increment ${index + 1}</h6>
<div class="row">

<div class="col-md-3">
<strong>Date:</strong> ${inc.increment_month}
</div>

<div class="col-md-3">
<strong>Old Salary:</strong> ₹ ${inc.old_gross_salary}
</div>

<div class="col-md-3">
<strong>New Salary:</strong> ₹ ${inc.new_gross_salary}
</div>

<div class="col-md-3">
<strong>New Position:</strong> ${inc.new_position ?? '-'}
</div>

</div>
</div>
</div>
`;

$('#increment_history_container').append(html);

});

}else{

$('#summary_increment').html('<span class="text-danger">No Increment</span>');
$('#had_increment').val(0);
$('#increment_date').val('');
$('#increment_amount').val('');

}

$('#salary').val(currentSalary);

$('#summary_joining').text(emp.date_of_joining ?? '-');
$('#summary_position').text(emp.position ?? '-');
$('#summary_salary').text("₹ " + currentSalary);

}

}
});

});

});
</script>

@endsection
