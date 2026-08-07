<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip</title>

    <style>
        *{
            font-family: DejaVu Sans, sans-serif;
        }

        body{
            font-size:12px;
            color:#333;
            margin:20px;
        }

        .header{
            width:100%;
            border-bottom:2px solid #ddd;
            padding-bottom:10px;
            margin-bottom:20px;
        }

        .left{
            float:left;
            width:60%;
        }

        .right{
            float:right;
            width:35%;
            text-align:right;
        }

        .clear{
            clear:both;
        }

        .box{
            border:1px solid #ddd;
            border-radius:5px;
            padding:10px;
            margin-top:15px;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        th{
            background:#f2f2f2;
            padding:8px;
            border:1px solid #ddd;
            text-align:left;
        }

        td{
            padding:8px;
            border:1px solid #ddd;
        }

        .netpay{
            background:#eaf9ea;
            font-size:22px;
            font-weight:bold;
            color:#0a8d28;
            padding:15px;
            text-align:center;
            border:1px solid #7dd67d;
            margin-top:10px;
        }

        .title{
            font-size:18px;
            font-weight:bold;
        }

        .footer{
            margin-top:40px;
            text-align:center;
            color:#777;
            font-size:11px;
        }

        .text-right{
            text-align:right;
        }

    </style>

</head>
<body>

<div class="header">

    <div class="left">
        <div class="title">Eemotrack India</div>

        <div>
            L B Palace, Salimpur Ahra,<br>
            Kadamkuan, Patna - 800001
        </div>
    </div>

    <div class="right">
        <h2>Payslip</h2>
        <strong>{{ $payroll->month }} {{ $payroll->year }}</strong>
    </div>

    <div class="clear"></div>

</div>

<div class="box">

<table>

<tr>
    <td width="25%"><strong>Employee Name</strong></td>
    <td>{{ $employee->full_name ?? $employee->name }}</td>

    <td width="20%"><strong>Employee ID</strong></td>
    <td>{{ $employee->employee_id }}</td>
</tr>

<tr>
    <td><strong>Department</strong></td>
    <td>{{ optional($employee->department)->name }}</td>

    <td><strong>Designation</strong></td>
    <td>{{ optional($employee->designation)->name }}</td>
</tr>

<tr>
    <td><strong>Pay Period</strong></td>
    <td>{{ $payroll->month }} {{ $payroll->year }}</td>

    <td><strong>Generated On</strong></td>
    <td>{{ date('d-m-Y',strtotime($payroll->generated_at)) }}</td>
</tr>

</table>

</div>


<div class="netpay">

₹ {{ number_format($payroll->net_salary,2) }}

<br>

<span style="font-size:13px;">
Total Net Pay
</span>

</div>

<br>

<table>

<tr>

<th>Earnings</th>
<th class="text-right">Amount</th>

<th>Deductions</th>
<th class="text-right">Amount</th>

</tr>

<tr>

<td>Basic</td>
<td class="text-right">{{ number_format($payroll->basic,2) }}</td>

<td>Deductions</td>
<td class="text-right">{{ number_format($payroll->deductions,2) }}</td>

</tr>

<tr>

<td>HRA</td>
<td class="text-right">{{ number_format($payroll->hra,2) }}</td>

<td>Manual Adjustment</td>
<td class="text-right">{{ number_format($payroll->manual_adjustment,2) }}</td>

</tr>

<tr>

<td>Allowance</td>
<td class="text-right">{{ number_format($payroll->allowance,2) }}</td>

<td></td>
<td></td>

</tr>

<tr>

<td>Bonus</td>
<td class="text-right">{{ number_format($payroll->bonus,2) }}</td>

<td></td>
<td></td>

</tr>

<tr style="font-weight:bold;">

<td>Gross Salary</td>
<td class="text-right">{{ number_format($payroll->gross_salary,2) }}</td>

<td>Total Deduction</td>
<td class="text-right">{{ number_format($payroll->deductions,2) }}</td>

</tr>

</table>

<br>

<div class="box">

<h3>Attendance Summary</h3>

<table>

<tr>
<td>Working Days</td>
<td>{{ $payroll->working_days }}</td>

<td>Present</td>
<td>{{ $payroll->present_days }}</td>
</tr>

<tr>
<td>Absent</td>
<td>{{ $payroll->absent_days }}</td>

<td>Leave</td>
<td>{{ $payroll->leave_days }}</td>
</tr>

<tr>
<td>Half Day</td>
<td>{{ $payroll->half_days }}</td>

<td>Paid Leaves</td>
<td>{{ $payroll->paid_leaves }}</td>
</tr>

<tr>
<td>Holidays</td>
<td>{{ $payroll->holidays }}</td>

<td>Final Paid Days</td>
<td>{{ $payroll->final_paid_days }}</td>
</tr>

</table>

</div>

@if($payroll->partPayments->count())

<br>

<div class="box">

<h3>Payment History</h3>

<table>

<tr>
<th>Date</th>
<th>Mode</th>
<th class="text-right">Amount</th>
</tr>

@foreach($payroll->partPayments as $payment)

<tr>

<td>{{ $payment->payment_date }}</td>

<td>{{ $payment->payment_mode }}</td>

<td class="text-right">
₹ {{ number_format($payment->part_amount,2) }}
</td>

</tr>

@endforeach

<tr style="font-weight:bold">

<td colspan="2">Total Paid</td>

<td class="text-right">

₹ {{ number_format($payroll->total_paid,2) }}

</td>

</tr>

<tr style="font-weight:bold">

<td colspan="2">Remaining Salary</td>

<td class="text-right">

₹ {{ number_format($payroll->remaining_amount,2) }}

</td>

</tr>

</table>

</div>

@endif

@if($payroll->remarks)

<br>

<div class="box">

<strong>Remarks</strong>

<br><br>

{{ $payroll->remarks }}

</div>

@endif

<div class="footer">

This is a system generated salary slip.<br>

Generated by EemotClocking

</div>

</body>
</html>