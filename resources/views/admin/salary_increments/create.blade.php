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
<h3 class="mb-4">📈 Create Salary Increment (With Deductions)</h3>

<form action="{{ route('admin.salary-increments.store') }}" method="POST">
@csrf

<div class="row">

<!-- ================= LEFT PANEL ================= -->
<div class="col-lg-4">

<!-- Employee Select -->
<div class="card shadow-sm mb-3">
<div class="card-header bg-primary text-white card-header-custom">
Select Employee
</div>
<div class="card-body">
<select class="form-control" id="employee_id" name="employee_id" required>
<option value="">Choose Employee</option>
@foreach($employees as $emp)
<option value="{{ $emp->id }}">
{{ $emp->employee_code }} — {{ $emp->user->name }}
</option>
@endforeach
</select>
</div>
</div>

<!-- 📌 Previous Job Details -->
<div class="card shadow-sm mb-3 d-none" id="oldJobCard">
<div class="card-header bg-secondary text-white card-header-custom">
📌 Previous Job Details
</div>
<div class="card-body">

<div class="small-label">Department</div>
<div class="big-value" id="old_department">—</div>

<div class="small-label mt-2">Position</div>
<div id="old_position">—</div>

<div class="small-label mt-2">Reporting To</div>
<div id="old_reporting">—</div>

</div>
</div>

<!-- Previous Salary -->
<div class="card shadow-sm mb-3 d-none" id="oldSalaryCard">
<div class="card-header bg-info text-white card-header-custom">
💼 Previous Salary
</div>
<div class="card-body">

<div class="small-label">Basic</div>
<div class="big-value" id="old_basic">0</div>

<div class="small-label mt-2">HRA</div>
<div id="old_hra">0</div>

<div class="small-label mt-2">Allowance</div>
<div id="old_allowance">0</div>

<div class="small-label mt-2 text-danger">Deductions</div>
<div class="big-value text-danger" id="old_deductions">0</div>

<hr>
<div class="small-label">Net Gross (After Deduction)</div>
<div class="big-value text-success" id="old_gross">0</div>

<hr>
<h6 class="mt-3 mb-2">🧾 Allowance Breakdown</h6>
<div id="old_allowances_list"></div>

</div>
</div>

<!-- Pie Chart -->
<div class="card shadow-sm mb-3">
<div class="card-header bg-dark text-white card-header-custom">
📊 Salary Comparison Chart
</div>
<div class="card-body text-center">
<canvas id="salaryPieChart" style="max-height:260px;"></canvas>
<small class="text-muted d-block mt-2">Old vs New Salary</small>
</div>
</div>

</div>

<!-- ================= RIGHT PANEL ================= -->
<div class="col-lg-8">

<!-- Comparison -->
<div class="card shadow-sm mb-3">
<div class="card-header bg-success text-white card-header-custom">
🔍 Salary Comparison (Auto Calculated)
</div>
<div class="card-body">

<div class="compare-box">
<div class="row text-center">

<div class="col-md-4">
<div class="small-label">Old Gross</div>
<div class="big-value" id="cmp_old_gross">0</div>
</div>

<div class="col-md-4">
<div class="small-label">New Gross</div>
<div class="big-value text-primary" id="cmp_new_gross">0</div>
</div>

<div class="col-md-4">
<div class="small-label">Increase</div>
<div class="big-value text-success" id="cmp_diff">0</div>
</div>

</div>

<hr>

<div class="text-center">
<div class="small-label">Increment %</div>
<div class="big-value text-warning" id="cmp_percent">0%</div>
</div>

</div>

</div>
</div>

<!-- New Salary & Job Structure -->
<div class="card shadow-sm mb-3">
<div class="card-header bg-warning card-header-custom">
🆕 Enter New Salary Structure
</div>
<div class="card-body">

<div class="row">

<div class="col-md-4">
<label class="small-label">New Department</label>
<input type="text" class="form-control" name="new_department" id="new_department">
</div>

<div class="col-md-4">
<label class="small-label">New Position</label>
<input type="text" class="form-control" name="new_position" id="new_position">
</div>

<div class="col-md-4">
<label class="small-label">New Reporting To</label>
<select name="new_reporting_to" id="new_reporting_to" class="form-control">
<option value="">-- Select --</option>
@foreach(\App\Models\User::orderBy('name')->get() as $u)
<option value="{{ $u->id }}">{{ $u->name }}</option>
@endforeach
</select>
</div>

<div class="col-md-3 mt-3">
<label class="small-label">New Basic</label>
<input type="number" class="form-control" id="new_basic" name="new_basic" required>
</div>

<div class="col-md-3 mt-3">
<label class="small-label">New HRA</label>
<input type="number" class="form-control" id="new_hra" name="new_hra">
</div>

<div class="col-md-3 mt-3">
<label class="small-label">New Allowance</label>
<input type="number" class="form-control" id="new_allowance" name="new_allowance">
</div>

<div class="col-md-3 mt-3">
<label class="small-label text-danger">Deduction</label>
<input type="number" class="form-control" id="new_deductions" name="new_deductions" value="0">
</div>

<div class="col-md-12 mt-3">
<label class="small-label">Other Allowances (JSON – Info Only)</label>
<textarea class="form-control" id="other_allowances_json" name="other_allowances_json"
placeholder='{"travel":1000,"meal":500}'></textarea>
</div>

<div class="col-md-6 mt-3">
<label>Increment Month</label>
<input type="month" class="form-control" name="increment_month" required>
</div>

<div class="col-md-6 mt-3">
<label>Remarks</label>
<textarea class="form-control" name="remarks"></textarea>
</div>

</div>

</div>
</div>

<div class="text-end mt-2">
<button class="btn btn-primary btn-lg">Save Increment</button>
</div>

</div>

</div>
</form>
</div>

<script>
let salaryPie;

/* ================= PIE CHART ================= */
function renderPieChart(oldGross, newGross) {
    const ctx = document.getElementById("salaryPieChart").getContext("2d");
    if (salaryPie) salaryPie.destroy();

    salaryPie = new Chart(ctx, {
        type: "doughnut",
        data: {
            labels: ["Old Gross", "New Gross"],
            datasets: [{
                data: [oldGross, newGross],
                backgroundColor: ["#007bff", "#28a745"],
                borderWidth: 2
            }]
        },
        options: {
            cutout: "55%",
            plugins: { legend: { position: "bottom" } }
        }
    });
}

/* ================= CALC (UNCHANGED) ================= */
function updateSalaryComparison() {
    const basic = parseFloat(new_basic.value) || 0;
    const hra = parseFloat(new_hra.value) || 0;
    const allow = parseFloat(new_allowance.value) || 0;
    const ded = parseFloat(new_deductions.value) || 0;

    const newGross = (basic + hra + allow) - ded;
    const oldGross = parseFloat(old_gross.innerText) || 0;

    cmp_new_gross.innerText = newGross.toFixed(2);
    cmp_old_gross.innerText = oldGross.toFixed(2);
    cmp_diff.innerText = (newGross - oldGross).toFixed(2);
    cmp_percent.innerText =
        oldGross > 0 ? (((newGross - oldGross) / oldGross) * 100).toFixed(2) + "%" : "0%";

    renderPieChart(oldGross, newGross);
}

["new_basic","new_hra","new_allowance","new_deductions"]
.forEach(id => document.getElementById(id).addEventListener("input", updateSalaryComparison));

/* ================= EMPLOYEE FETCH ================= */
employee_id.addEventListener("change", function () {

fetch("{{ route('admin.get.employee.salary') }}", {
method: "POST",
headers: {
"X-CSRF-TOKEN": "{{ csrf_token() }}",
"Content-Type": "application/json"
},
body: JSON.stringify({ employee_id: this.value })
})
.then(r => r.json())
.then(res => {

const s = res.salary;
const emp = res.employee;

oldJobCard.classList.remove("d-none");
oldSalaryCard.classList.remove("d-none");

old_department.innerText = emp.department ?? "—";
old_position.innerText = emp.position ?? "—";
old_reporting.innerText = emp.reporting_to?.name ?? "—";

/* AUTO FILL NEW INPUTS */
new_department.value = emp.department ?? "";
new_position.value = emp.position ?? "";
new_reporting_to.value = emp.reporting_to?.id ?? "";

/* OLD SALARY */
const basic = parseFloat(s.basic) || 0;
const hra = parseFloat(s.hra) || 0;
const allow = parseFloat(s.allowance) || 0;
const ded = parseFloat(s.deductions) || 0;

old_basic.innerText = basic.toFixed(2);
old_hra.innerText = hra.toFixed(2);
old_allowance.innerText = allow.toFixed(2);
old_deductions.innerText = ded.toFixed(2);

const gross = (basic + hra + allow) - ded;
old_gross.innerText = gross.toFixed(2);

let html = "";
Object.entries(emp.other_allowances_json || {}).forEach(([k,v])=>{
html += `<div class="allowance-box"><b>${k}:</b> ${v}</div>`;
});
old_allowances_list.innerHTML = html || "<em>No Allowances</em>";

renderPieChart(gross, gross);
updateSalaryComparison();
});
});
</script>

@endsection
