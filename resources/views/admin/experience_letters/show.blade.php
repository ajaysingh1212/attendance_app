@extends('layouts.admin')
@section('content')

<style>
.letter-box{
    background:#fff;
    padding:50px;
    border-radius:14px;
    box-shadow:0 5px 30px rgba(0,0,0,0.08);
    line-height:1.8;
}
.letter-header{
    text-align:center;
    margin-bottom:40px;
}
.signature{
    margin-top:60px;
}
</style>

<div class="container">
<div class="letter-box">

<div class="letter-header">
<h2>Experience Letter</h2>
<p>Date: {{ now()->format('d M Y') }}</p>
</div>

<p>
This is to certify that <strong>{{ $letter->employee->full_name }}</strong>
was employed with our organization from
<strong>{{ \Carbon\Carbon::parse($letter->date_of_joining)->format('d M Y') }}</strong>
to
<strong>{{ \Carbon\Carbon::parse($letter->last_working_date)->format('d M Y') }}</strong>.
</p>

<p>
During this period, the employee worked as
<strong>{{ $letter->designation }}</strong>
in the <strong>{{ $letter->department }}</strong> department.
</p>

<p>
The last drawn salary was ₹
<strong>{{ number_format($letter->last_drawn_salary,2) }}</strong>.
</p>

@if($letter->had_increment)
<p>
The employee received a salary increment of ₹
<strong>{{ number_format($letter->increment_amount,2) }}</strong>
on {{ \Carbon\Carbon::parse($letter->last_increment_date)->format('d M Y') }}.
</p>
@endif

<p>
We found the employee sincere and hardworking.
We wish them all the best for their future endeavors.
</p>

@if($letter->additional_remark)
<p><strong>Remark:</strong> {{ $letter->additional_remark }}</p>
@endif

<div class="signature">
<p>For Company Name</p>
<br><br>
<p>Authorized Signatory</p>
</div>

<hr>
<a href="{{ route('admin.experience-letters.pdf',$letter->id) }}" class="btn btn-success">
Download PDF
</a>

</div>
</div>

@endsection
