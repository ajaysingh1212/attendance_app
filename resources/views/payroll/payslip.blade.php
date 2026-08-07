<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip</title>

    <style>

        @page {
            size: A4;
            margin: 30px 35px;
        }

        *{
            font-family: DejaVu Sans, sans-serif;
            box-sizing: border-box;
        }

        body{
            font-size:12px;
            color:#333333;
            margin:0;
        }

        /* ===== Header ===== */

        .header-table{
            width:100%;
            border-collapse:collapse;
        }

        .header-table td{
            border:none;
            padding:0;
            vertical-align:top;
        }

        .logo-wrap{
            width:64px;
            position:relative;
        }

        .logo-grid{
            width:56px;
        }

        .logo-row{
            width:100%;
        }

        .logo-cell{
            width:26px;
            height:26px;
            color:#ffffff;
            font-size:13px;
            font-weight:bold;
            text-align:center;
            vertical-align:middle;
            border-radius:6px;
        }

        .logo-teal{ background:#14b8a6; }
        .logo-orange{ background:#f7941d; }

        .logo-t{
            width:22px;
            height:22px;
            background:#ef4136;
            color:#ffffff;
            font-size:11px;
            font-weight:bold;
            text-align:center;
            vertical-align:middle;
            border-radius:50%;
            position:absolute;
            right:-8px;
            bottom:-8px;
            border:2px solid #ffffff;
        }

        .company-name{
            font-size:19px;
            font-weight:bold;
            color:#222222;
        }

        .company-address{
            font-size:10.5px;
            color:#888888;
            margin-top:2px;
        }

        .header-right{
            text-align:right;
        }

        .payslip-for{
            font-size:10.5px;
            color:#999999;
        }

        .payslip-month{
            font-size:15px;
            font-weight:bold;
            color:#222222;
            margin-top:2px;
        }

        .header-rule{
            border-bottom:1px solid #e2e2e2;
            margin:14px 0 18px 0;
        }

        /* ===== Summary + Net pay ===== */

        .summary-table{
            width:100%;
            border-collapse:collapse;
        }

        .summary-table > tr > td{
            border:none;
            padding:0;
            vertical-align:top;
        }

        .summary-left{
            width:60%;
        }

        .section-label{
            font-size:10px;
            font-weight:bold;
            color:#999999;
            letter-spacing:0.5px;
            margin-bottom:10px;
        }

        .info-table{
            width:100%;
            border-collapse:collapse;
        }

        .info-table td{
            border:none;
            padding:4px 0;
            font-size:12px;
        }

        .info-label{
            width:110px;
            color:#888888;
        }

        .info-colon{
            width:12px;
            color:#888888;
        }

        .info-value{
            color:#222222;
            font-weight:bold;
        }

        .netpay-box{
            width:100%;
            background:#eafaf0;
            border:1px solid #cdeed9;
            border-left:4px solid #22a34a;
            border-radius:4px;
            padding:14px 16px;
        }

        .netpay-amount{
            font-size:24px;
            font-weight:bold;
            color:#1a9b40;
        }

        .netpay-caption{
            font-size:10.5px;
            color:#888888;
            margin-top:2px;
        }

        .netpay-rule{
            border-bottom:1px dashed #cdeed9;
            margin:10px 0;
        }

        .netpay-sub-table{
            width:100%;
            border-collapse:collapse;
        }

        .netpay-sub-table td{
            border:none;
            padding:2px 0;
            font-size:11.5px;
            color:#444444;
        }

        .netpay-sub-label{
            width:70px;
            color:#888888;
        }

        .netpay-sub-colon{
            width:12px;
            color:#888888;
        }

        .netpay-sub-value{
            font-weight:bold;
            color:#222222;
        }

        /* ===== Earnings / Deductions ===== */

        .pay-box{
            border:1px solid #e2e2e2;
            border-radius:5px;
            padding:14px 16px;
            margin-top:22px;
        }

        .pay-table{
            width:100%;
            border-collapse:collapse;
        }

        .pay-table th{
            border:none;
            border-bottom:1px solid #e2e2e2;
            text-align:left;
            font-size:10px;
            color:#999999;
            font-weight:bold;
            letter-spacing:0.5px;
            padding:0 0 8px 0;
        }

        .pay-table th.amt{
            text-align:right;
        }

        .pay-table td{
            border:none;
            padding:8px 0;
            font-size:12px;
            color:#333333;
        }

        .pay-table td.amt{
            text-align:right;
            font-weight:bold;
        }

        .pay-table tr.total-row td{
            border-top:1px solid #e2e2e2;
            background:#f7f7f7;
            font-weight:bold;
            padding:8px 6px;
        }

        .pay-table tr.total-row td.amt{
            text-align:right;
        }

        .col-gap{
            width:24px;
        }

        /* ===== Total Net Payable ===== */

        .total-payable{
            width:100%;
            border:1px solid #e2e2e2;
            border-radius:5px;
            margin-top:18px;
            border-collapse:collapse;
        }

        .total-payable td{
            border:none;
            padding:14px 16px;
            vertical-align:middle;
        }

        .total-payable-label{
            font-size:13px;
            font-weight:bold;
            color:#222222;
        }

        .total-payable-sub{
            font-size:10.5px;
            color:#999999;
            margin-top:2px;
        }

        .total-payable-value{
            background:#eafaf0;
            text-align:right;
            font-size:18px;
            font-weight:bold;
            color:#1a9b40;
            width:180px;
        }

        /* ===== Amount in words ===== */

        .amount-words{
            text-align:right;
            font-size:11px;
            color:#888888;
            margin-top:12px;
        }

        .amount-words strong{
            color:#333333;
        }

        /* ===== Attendance summary ===== */

        .box{
            border:1px solid #e2e2e2;
            border-radius:5px;
            padding:14px 16px;
            margin-top:18px;
        }

        .box h3{
            font-size:12px;
            color:#222222;
            margin:0 0 10px 0;
        }

        .box table{
            width:100%;
            border-collapse:collapse;
        }

        .box table td{
            border:none;
            padding:5px 0;
            font-size:11.5px;
        }

        .box table td.label{
            color:#888888;
            width:32%;
        }

        .box table td.value{
            color:#222222;
            font-weight:bold;
            width:18%;
        }

        /* ===== Payment history ===== */

        .history-table{
            width:100%;
            border-collapse:collapse;
        }

        .history-table th{
            background:#f7f7f7;
            border:1px solid #e2e2e2;
            padding:6px 8px;
            font-size:10.5px;
            color:#666666;
            text-align:left;
        }

        .history-table td{
            border:1px solid #e2e2e2;
            padding:6px 8px;
            font-size:11.5px;
        }

        .text-right{
            text-align:right;
        }

        .history-total td{
            font-weight:bold;
            background:#f7f7f7;
        }

        /* ===== Remarks ===== */

        .remarks-text{
            font-size:11.5px;
            color:#444444;
            line-height:1.5;
        }

        /* ===== Footer ===== */

        .footer{
            margin-top:36px;
            padding-top:14px;
            border-top:1px solid #e2e2e2;
            text-align:center;
            color:#aaaaaa;
            font-size:10.5px;
        }

    </style>

</head>
<body>

<!-- Header -->

<table class="header-table">
    <tr>
        <td style="width:64px;">
            <div class="logo-wrap">
                <table class="logo-grid" cellpadding="0" cellspacing="2">
                    <tr>
                        <td class="logo-cell logo-teal">E</td>
                        <td class="logo-cell logo-teal">E</td>
                    </tr>
                    <tr>
                        <td class="logo-cell logo-orange">M</td>
                        <td class="logo-cell logo-orange">O</td>
                    </tr>
                </table>
                <div class="logo-t">T</div>
            </div>
        </td>
        <td>
            <div class="company-name">Eemotrack India</div>
            <div class="company-address">L B Palace, Salimpur Ahra, Kadamkuan, Patna 800001 India</div>
        </td>
        <td class="header-right">
            <div class="payslip-for">Payslip For the Month</div>
            <div class="payslip-month">{{ $payroll->month }} {{ $payroll->year }}</div>
        </td>
    </tr>
</table>

<div class="header-rule"></div>

<!-- Employee summary + Net pay -->

<table class="summary-table">
    <tr>
        <td class="summary-left">

            <div class="section-label">EMPLOYEE SUMMARY</div>

            <table class="info-table">
                <tr>
                    <td class="info-label">Employee Name</td>
                    <td class="info-colon">:</td>
                    <td class="info-value">{{ $employee->full_name ?? $employee->name }}</td>
                </tr>
                <tr>
                    <td class="info-label">Employee ID</td>
                    <td class="info-colon">:</td>
                    <td class="info-value">{{ $employee->employee_id }}</td>
                </tr>
                <tr>
                    <td class="info-label">Department</td>
                    <td class="info-colon">:</td>
                    <td class="info-value">{{ optional($employee->department)->name }}</td>
                </tr>
                <tr>
                    <td class="info-label">Designation</td>
                    <td class="info-colon">:</td>
                    <td class="info-value">{{ optional($employee->designation)->name }}</td>
                </tr>
                <tr>
                    <td class="info-label">Pay Period</td>
                    <td class="info-colon">:</td>
                    <td class="info-value">{{ $payroll->month }} {{ $payroll->year }}</td>
                </tr>
                <tr>
                    <td class="info-label">Pay Date</td>
                    <td class="info-colon">:</td>
                    <td class="info-value">{{ $payroll->generated_at ? date('d/m/Y', strtotime($payroll->generated_at)) : '-' }}</td>
                </tr>
            </table>

        </td>
        <td style="width:4%;"></td>
        <td>

            <div class="netpay-box">
                <div class="netpay-amount">₹ {{ number_format($payroll->net_salary, 2) }}</div>
                <div class="netpay-caption">Total Net Pay</div>

                <div class="netpay-rule"></div>

                <table class="netpay-sub-table">
                    <tr>
                        <td class="netpay-sub-label">Paid Days</td>
                        <td class="netpay-sub-colon">:</td>
                        <td class="netpay-sub-value">{{ $payroll->final_paid_days }}</td>
                    </tr>
                    <tr>
                        <td class="netpay-sub-label">LOP Days</td>
                        <td class="netpay-sub-colon">:</td>
                        <td class="netpay-sub-value">{{ $payroll->absent_days }}</td>
                    </tr>
                </table>
            </div>

        </td>
    </tr>
</table>

<!-- Earnings / Deductions -->

<div class="pay-box">

    <table class="pay-table">
        <tr>
            <th style="width:34%;">EARNINGS</th>
            <th class="amt" style="width:16%;">AMOUNT</th>
            <th class="col-gap"></th>
            <th style="width:34%;">DEDUCTIONS</th>
            <th class="amt" style="width:16%;">AMOUNT</th>
        </tr>

        <tr>
            <td>Basic</td>
            <td class="amt">{{ number_format($payroll->basic, 2) }}</td>
            <td></td>
            <td>Deductions</td>
            <td class="amt">{{ number_format($payroll->deductions, 2) }}</td>
        </tr>

        <tr>
            <td>House Rent Allowance</td>
            <td class="amt">{{ number_format($payroll->hra, 2) }}</td>
            <td></td>
            <td>Manual Adjustment</td>
            <td class="amt">{{ number_format($payroll->manual_adjustment, 2) }}</td>
        </tr>

        <tr>
            <td>Allowance</td>
            <td class="amt">{{ number_format($payroll->allowance, 2) }}</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>

        <tr>
            <td>Bonus</td>
            <td class="amt">{{ number_format($payroll->bonus, 2) }}</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>

        <tr class="total-row">
            <td>Gross Earnings</td>
            <td class="amt">{{ number_format($payroll->gross_salary, 2) }}</td>
            <td></td>
            <td>Total Deductions</td>
            <td class="amt">{{ number_format($payroll->deductions + $payroll->manual_adjustment, 2) }}</td>
        </tr>
    </table>

</div>

<!-- Total net payable -->

<table class="total-payable">
    <tr>
        <td>
            <div class="total-payable-label">TOTAL NET PAYABLE</div>
            <div class="total-payable-sub">Gross Earnings - Total Deductions</div>
        </td>
        <td class="total-payable-value">₹ {{ number_format($payroll->net_salary, 2) }}</td>
    </tr>
</table>

@php
    $amountInWords = null;

    if (class_exists('NumberFormatter')) {
        try {
            $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
            $amountInWords = ucwords($formatter->format((int) round($payroll->net_salary)));
        } catch (\Throwable $e) {
            $amountInWords = null;
        }
    }
@endphp

@if($amountInWords)
<div class="amount-words">
    Amount In Words : <strong>Indian Rupee {{ $amountInWords }} Only</strong>
</div>
@endif

<!-- Attendance summary -->

<div class="box">
    <h3>Attendance Summary</h3>
    <table>
        <tr>
            <td class="label">Working Days</td>
            <td class="value">{{ $payroll->working_days }}</td>
            <td class="label">Present</td>
            <td class="value">{{ $payroll->present_days }}</td>
        </tr>
        <tr>
            <td class="label">Absent</td>
            <td class="value">{{ $payroll->absent_days }}</td>
            <td class="label">Leave</td>
            <td class="value">{{ $payroll->leave_days }}</td>
        </tr>
        <tr>
            <td class="label">Half Day</td>
            <td class="value">{{ $payroll->half_days }}</td>
            <td class="label">Paid Leaves</td>
            <td class="value">{{ $payroll->paid_leaves }}</td>
        </tr>
        <tr>
            <td class="label">Holidays</td>
            <td class="value">{{ $payroll->holidays }}</td>
            <td class="label">Final Paid Days</td>
            <td class="value">{{ $payroll->final_paid_days }}</td>
        </tr>
    </table>
</div>

@if($payroll->partPayments->count())

<div class="box">
    <h3>Payment History</h3>

    <table class="history-table">
        <tr>
            <th>Date</th>
            <th>Mode</th>
            <th class="text-right">Amount</th>
        </tr>

        @foreach($payroll->partPayments as $payment)
        <tr>
            <td>{{ $payment->payment_date }}</td>
            <td>{{ $payment->payment_mode }}</td>
            <td class="text-right">₹ {{ number_format($payment->part_amount, 2) }}</td>
        </tr>
        @endforeach

        <tr class="history-total">
            <td colspan="2">Total Paid</td>
            <td class="text-right">₹ {{ number_format($payroll->total_paid, 2) }}</td>
        </tr>
        <tr class="history-total">
            <td colspan="2">Remaining Salary</td>
            <td class="text-right">₹ {{ number_format($payroll->remaining_amount, 2) }}</td>
        </tr>
    </table>
</div>

@endif

@if($payroll->remarks)

<div class="box">
    <h3>Remarks</h3>
    <div class="remarks-text">{{ $payroll->remarks }}</div>
</div>

@endif

<div class="footer">
    -- This is a system generated document. --<br>
    Generated by EemotClocking
</div>

</body>
</html>