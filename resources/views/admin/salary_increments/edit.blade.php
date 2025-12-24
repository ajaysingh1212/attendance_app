@extends('layouts.admin')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
.card-header-custom { font-weight:700; }
.small-label { font-size:0.85rem; color:#666; }
.big-value { font-size:1.4rem; font-weight:700; }
.compare-box { background:#f0f5ff; padding:15px; border-radius:10px; }
.allowance-box { background:#eef6ff; padding:8px; border-radius:6px; margin-bottom:6px; }
</style>

<div class="container">
<h3 class="mb-4">✏️ Edit Salary Increment</h3>

<form action="{{ route('admin.salary-increments.update',$increment->id) }}" method="POST">
@csrf
@method('PUT')

<div class="row">

<!-- ================= LEFT PANEL ================= -->
<div class="col-lg-4">

<!-- Employee (Readonly) -->
<div class="card shadow-sm mb-3">
<div class="card-header bg-primary text-white card-header-custom">
Employee
</div>
<div class="card-body">
<input type="text" class="form-control" disabled
value="{{ $employee->employee_code }} — {{ $employee->user->name }}">
</div>
</div>

<!-- 📌 Previous Job Details -->
<div class="card shadow-sm mb-3">
<div class="card-header bg-secondary text-white card-header-custom">
📌 Previous Job Details
</div>
<div class="card-body">

<div class="small-label">Department</div>
<div class="big-value">{{ $increment->old_department }}</div>

<div class="small-label mt-2">Position</div>
<div>{{ $increment->old_position }}</div>

<div class="small-label mt-2">Reporting To</div>
<div>{{ optional($increment->oldReportingUser)->name ?? '—' }}</div>

</div>
</div>

<!-- Previous Salary -->
<div class="card shadow-sm mb-3">
<div class="card-header bg-info text-white card-header-custom">
💼 Previous Salary
</div>
<div class="card-body">

<div class="small-label">Basic</div>
<div class="big-value" id="old_basic">{{ $increment->old_basic }}</div>

<div class="small-label mt-2">HRA</div>
<div id="old_hra">{{ $increment->old_hra }}</div>

<div class="small-label mt-2">Allowance</div>
<div id="old_allowance">{{ $increment->old_allowance }}</div>

<div class="small-label mt-2 text-danger">Deductions</div>
<div class="big-value text-danger" id="old_deductions">{{ $increment->deductions ?? 0 }}</div>

<hr>
<div class="small-label">Net Gross</div>
<div class="big-value text-success" id="old_gross">
{{ $increment->old_gross_salary }}
</div>

<hr>
<h6>🧾 Allowance Breakdown</h6>
@foreach(json_decode($increment->older_allowances_json ?? '{}', true) as $k=>$v)
<div class="allowance-box"><b>{{ $k }}:</b> {{ $v }}</div>
@endforeach

</div>
</div>

<!-- Pie Chart -->
<div class="card shadow-sm mb-3">
<div class="card-header bg-dark text-white card-header-custom">
📊 Salary Comparison Chart
</div>
<div class="card-body text-center">
<canvas id="salaryPieChart" style="max-height:260px;"></canvas>
</div>
</div>

</div>

<!-- ================= RIGHT PANEL ================= -->
<div class="col-lg-8">

<!-- Comparison -->
<div class="card shadow-sm mb-3">
<div class="card-header bg-success text-white card-header-custom">
🔍 Salary Comparison
</div>
<div class="card-body">

<div class="compare-box">
<div class="row text-center">

<div class="col-md-4">
<div class="small-label">Old Gross</div>
<div class="big-value" id="cmp_old_gross">
{{ $increment->old_gross_salary }}
</div>
</div>

<div class="col-md-4">
<div class="small-label">New Gross</div>
<div class="big-value text-primary" id="cmp_new_gross">
{{ $increment->new_gross_salary }}
</div>
</div>

<div class="col-md-4">
<div class="small-label">Increase</div>
<div class="big-value text-success" id="cmp_diff">
{{ $increment->new_gross_salary - $increment->old_gross_salary }}
</div>
</div>

</div>

<hr>

<div class="text-center">
<div class="small-label">Increment %</div>
<div class="big-value text-warning" id="cmp_percent">
{{ $increment->old_gross_salary > 0
? number_format((($increment->new_gross_salary - $increment->old_gross_salary) / $increment->old_gross_salary) * 100,2)
: 0 }}%
</div>
</div>

</div>

</div>
</div>

<!-- New Salary & Job Structure -->
<div class="card shadow-sm mb-3">
<div class="card-header bg-warning card-header-custom">
🆕 Updated Salary Structure
</div>
<div class="card-body">

<div class="row">

<div class="col-md-4">
<label class="small-label">New Department</label>
<input type="text" class="form-control" name="new_department"
id="new_department" value="{{ $increment->new_department }}">
</div>

<div class="col-md-4">
<label class="small-label">New Position</label>
<input type="text" class="form-control" name="new_position"
id="new_position" value="{{ $increment->new_position }}">
</div>

<div class="col-md-4">
<label class="small-label">New Reporting To</label>
<select name="new_reporting_to" id="new_reporting_to" class="form-control">
<option value="">-- Select --</option>
@foreach($managers as $m)
<option value="{{ $m->id }}"
{{ $increment->new_reporting_to == $m->id ? 'selected' : '' }}>
{{ $m->name }}
</option>
@endforeach
</select>
</div>

<div class="col-md-3 mt-3">
<label class="small-label">New Basic</label>
<input type="number" class="form-control" id="new_basic"
name="new_basic" value="{{ $increment->new_basic }}" required>
</div>

<div class="col-md-3 mt-3">
<label class="small-label">New HRA</label>
<input type="number" class="form-control" id="new_hra"
name="new_hra" value="{{ $increment->new_hra }}">
</div>

<div class="col-md-3 mt-3">
<label class="small-label">New Allowance</label>
<input type="number" class="form-control" id="new_allowance"
name="new_allowance" value="{{ $increment->new_allowance }}">
</div>

<div class="col-md-3 mt-3">
<label class="small-label text-danger">Deduction</label>
<input type="number" class="form-control" id="new_deductions"
value="{{ $increment->deductions ?? 0 }}">
</div>

<div class="col-md-12 mt-3">
<label class="small-label">Other Allowances (JSON)</label>
<textarea class="form-control" id="other_allowances_json"
name="other_allowances_json">{{ $increment->other_allowances_json }}</textarea>
</div>

<div class="col-md-6 mt-3">
<label>Increment Month</label>
<input type="month" class="form-control"
name="increment_month" value="{{ $increment->increment_month }}" required>
</div>

<div class="col-md-6 mt-3">
<label>Remarks</label>
<textarea class="form-control" name="remarks">{{ $increment->remarks }}</textarea>
</div>

</div>

</div>
</div>

<div class="text-end">
<button class="btn btn-primary btn-lg">Update Increment</button>
</div>

</div>

</div>
</form>
</div>

<script>
let salaryPie;

function renderPieChart(o,n){
const ctx=document.getElementById("salaryPieChart").getContext("2d");
if(salaryPie) salaryPie.destroy();
salaryPie=new Chart(ctx,{
type:"doughnut",
data:{labels:["Old","New"],datasets:[{data:[o,n],backgroundColor:["#007bff","#28a745"]}]},
options:{cutout:"55%"}
});
}

function updateSalaryComparison(){
const b=+new_basic.value||0;
const h=+new_hra.value||0;
const a=+new_allowance.value||0;
const d=+new_deductions.value||0;

const newG=(b+h+a)-d;
const oldG=+old_gross.innerText||0;

cmp_new_gross.innerText=newG.toFixed(2);
cmp_old_gross.innerText=oldG.toFixed(2);
cmp_diff.innerText=(newG-oldG).toFixed(2);
cmp_percent.innerText=oldG>0?(((newG-oldG)/oldG)*100).toFixed(2)+"%":"0%";

renderPieChart(oldG,newG);
}

["new_basic","new_hra","new_allowance","new_deductions"]
.forEach(i=>document.getElementById(i).addEventListener("input",updateSalaryComparison));

document.addEventListener("DOMContentLoaded",()=>{
renderPieChart(
parseFloat("{{ $increment->old_gross_salary }}"),
parseFloat("{{ $increment->new_gross_salary }}")
);
});
</script>

@endsection
