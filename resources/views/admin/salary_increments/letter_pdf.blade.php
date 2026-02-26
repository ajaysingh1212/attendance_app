<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Salary Increment Letter</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
@page {
    size: A4;
    margin: 0mm;
}

body {
    font-family: Arial, sans-serif;
    background: #f2f6fb;
    margin: 0;
    padding: 20px 0;
    color: #000;
}

.container-box {
    max-width: 760px;
    margin: auto;
}

.card-custom {
    border: 2px solid #d1d9e6;
    border-radius: 10px;
    background: #fff;
    overflow: hidden;
    position: relative;
}

.content-area {
    padding: 26px 32px 120px; /* bottom space for footer */
    font-size: 14px;
    line-height: 1.45;
}

.bold { font-weight: bold; }

.header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
}

.profile-pic {
    width: 85px;
    height: 85px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid #d1d9e6;
}
.profile-pic img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Footer */
.footer-img {
margin-top: -130px;
width: 100%;

}

/* Print */
@media print {
    body { background: #fff; padding: 0; }
    .print-btn { display: none; }
}
</style>
</head>

<body>

<div class="container-box">
<div class="card-custom" id="printArea">

<!-- HEADER IMAGE -->
<img src="{{ asset('header2.png') }}" style="width:100%; margin-top: -20px;">

<div class="content-area" style="margin-top: -10px;">

<div class="header-row " >
    <div>
        <p class="text-muted mb-1">
            {{ \Carbon\Carbon::parse(($increment->increment_month ?? now()->format('Y-m')).'-01')->format('F d, Y') }}
        </p>

        <p class="bold mb-1">To,</p>
        <p class="mb-0">
            {{ $employee->user->name }}<br>
            Employee Code: {{ $employee->employee_code }}<br>
            Department: {{ $increment->new_department ?? $employee->department }}<br>
            Position: {{ $increment->new_position ?? $employee->position }}
        </p>
    </div>

    <div class="profile-pic">
        @if(!empty($employeeImageUrl))
            <img src="{{ $employeeImageUrl }}">
        @elseif(!empty($companyLogoUrl))
            <img src="{{ $companyLogoUrl }}">
        @endif
    </div>

</div>

<p class="bold mb-2">Subject: Salary Increment Notification</p>

<p class="bold">Dear {{ $employee->user->name }},</p>

<p>
We are pleased to inform you that, following a review of your performance and contribution,
the management of <strong>{{ $companyName ?? 'the Company' }}</strong> has approved a revision
to your monthly compensation.
</p>

<p>
Your efforts, professionalism, and consistent performance have been appreciated. Based on
this evaluation, your salary has been revised as outlined below:
</p>

<ul style="margin-top:0">
<li>Previous Gross Salary: ₹ {{ number_format($increment->old_gross_salary, 2) }} per month</li>
<li>Revised Gross Salary: ₹ {{ number_format($increment->new_gross_salary, 2) }} per month</li>
<li>Effective From: {{ \Carbon\Carbon::parse(($increment->increment_month).'-01')->format('F d, Y') }}</li>
</ul>

<p>
The revised salary will be reflected in your monthly payroll, subject to applicable statutory
deductions and company policies.
</p>

<p>
We appreciate your continued commitment and look forward to your ongoing contribution to the
organization.
</p>

<p class="bold">Yours sincerely,</p>

<p class="bold" style="z-index: 10;">
For {{ $companyName ?? 'Company Name' }}<br>
Human Resources Department
</p>

</div>

<!-- FOOTER IMAGE (ALWAYS BOTTOM) -->
<img src="{{ asset('footer2.png') }}" class="footer-img">

</div>

<!-- PRINT BUTTON -->
<div class="text-center mt-3 print-btn">
    <button class="btn btn-primary" onclick="window.print()">Print / Save as PDF</button>
</div>

</div>

</body>
</html>
