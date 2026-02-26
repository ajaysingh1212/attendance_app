<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Experience Letter</title>

<style>
body{
    font-family: Arial, sans-serif;
    line-height:1.8;
    margin:40px;
    color:#333;
}
.company-header{
    text-align:center;
    margin-bottom:40px;
}
.company-name{
    font-size:26px;
    font-weight:bold;
    letter-spacing:1px;
}
.company-address{
    font-size:13px;
}
.letter-title{
    text-align:center;
    font-size:18px;
    font-weight:bold;
    text-decoration:underline;
    margin:30px 0;
}
.content{
    font-size:15px;
    text-align:justify;
}
.info-table{
    margin-top:20px;
}
.info-table td{
    padding:6px 0;
}
.increment-section{
    margin-top:25px;
}
.increment-box{
    border:1px solid #ccc;
    padding:10px;
    margin-bottom:8px;
}
.signature{
    margin-top:70px;
}
.print-btn{
    position:fixed;
    top:20px;
    right:20px;
}
@media print {
    .print-btn{
        display:none;
    }
}
</style>

</head>

<body>

<button onclick="window.print()" class="print-btn">
    Print
</button>

<div class="company-header">
    <div class="company-name">ABC TECHNOLOGIES PRIVATE LIMITED</div>
    <div class="company-address">
        Corporate Office: Mumbai, India<br>
        Email: hr@abctech.com | Phone: +91-9876543210
    </div>
</div>

<div class="letter-title">EXPERIENCE LETTER</div>

<div class="content">

<p>
This is to certify that <strong>{{ $employee->full_name }}</strong>
was employed with <strong>ABC Technologies Private Limited</strong>
from <strong>{{ $letter->date_of_joining }}</strong>
to <strong>{{ $letter->last_working_date }}</strong>.
</p>

<p>
During this tenure, the employee worked as
<strong>{{ $letter->designation }}</strong>
in the <strong>{{ $letter->department }}</strong> department.
Throughout the employment period, the employee demonstrated a
high level of professionalism, dedication, and technical expertise.
Their contributions significantly supported various organizational
goals and strategic initiatives.
</p>

<p>
The employee maintained a strong work ethic, adhered to company
policies, and consistently delivered quality results within
timelines. They worked collaboratively with cross-functional teams,
handled responsibilities efficiently, and maintained excellent
interpersonal relationships with colleagues and management.
</p>

<p>
Over the course of employment, the employee showed remarkable
professional growth and adaptability. Their performance reflected
commitment, reliability, and continuous improvement in both
technical and organizational competencies.
</p>

<p>
The last drawn salary of the employee was
<strong>₹ {{ number_format($letter->last_drawn_salary,2) }}</strong>
per annum at the time of separation.
</p>

@if($increments->count() > 0)

<div class="increment-section">
<strong>Increment History:</strong>

@foreach($increments as $inc)
<div class="increment-box">
    Date: {{ $inc->increment_month }} <br>
    Previous Salary: ₹ {{ number_format($inc->old_gross_salary,2) }} <br>
    Revised Salary: ₹ {{ number_format($inc->new_gross_salary,2) }} <br>
    Position: {{ $inc->new_position ?? $letter->designation }}
</div>
@endforeach

</div>

@endif

<p>
We found the employee to be sincere, responsible, and capable of
handling professional challenges with confidence and competence.
We appreciate their valuable contribution to the organization and
wish them continued success and prosperity in all future endeavors.
</p>

</div>

<div class="signature">
For ABC Technologies Private Limited<br><br><br>

_____________________________<br>
Authorized Signatory
</div>

</body>
</html>
