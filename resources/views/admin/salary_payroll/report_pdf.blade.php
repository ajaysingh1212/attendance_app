<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payroll Slip - {{ $payroll->employee->full_name }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            margin: 20px;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        th, td {
            border: 1px solid #444;
            padding: 8px 10px;
            text-align: left;
        }
        th { background: #f7f7f7; }

        .section-title {
            background: #004085;
            color: #fff;
            font-weight: bold;
            text-transform: uppercase;
            text-align: left;
        }

        tr:nth-child(even):not(.section-title) { background: #f9f9f9; }
        .highlight { font-weight: bold; background: #e6f3ff !important; }

        /* ✅ Watermark Logo */
        .watermark img {
            position: fixed;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.05;
            width: 300px;
            z-index: -1;
        }

        .note {
            text-align: center;
            font-size: 12px;
            margin-top: 20px;
            color: #555;
        }

        .signature img {
            max-height: 60px;
            display: block;
            margin: 10px auto 5px auto;
        }
    </style>
</head>
<body>

<!-- ✅ Watermark -->
<div class="watermark">
    <img src="{{ public_path('logo.jpg') }}" alt="Watermark Logo">
</div>

<!-- ✅ Header Table -->
<table style=" border:none;">
    <tr>
        <td style="width: 20%; text-align:center; border:none;">
            <img src="{{ public_path('logo.jpg') }}" alt="Company Logo" style="height:60px;">
        </td>
        <td style="width: 60%; text-align:center; border:none;">
            <h2>EEMOTRACK INDIA PRIVATE LIMITED</h2>
            <p>HQ-2: GPS House, Kamla Market, R.K Bhattacharya Road, Patna, Bihar - 800001</p>
            <p><strong>GST NO:</strong> 10AQFPK9218DM1ZI</p>
            <h3>Salary Slip - {{ DateTime::createFromFormat('!m', $payroll->month)->format('F') }} {{ $payroll->year }}</h3>
        </td>
        <td style="width: 20%; text-align:right; border:none;">
            <p><strong>Date:</strong> {{ now()->format('d-m-Y') }}</p>
        </td>
    </tr>
</table>

<!-- Employee Details -->
<table>
    <tr class="section-title" style="color:black;"><th colspan="4">Employee Details</th></tr>
    <tr><th>Name</th><td>{{ $payroll->employee->full_name ?? '-' }}</td><th>Employee ID</th><td>{{ $payroll->employee->employee_code ?? '-' }}</td></tr>
    <tr><th>Designation</th><td>{{ $payroll->employee->position ?? '-' }}</td><th>Department</th><td>{{ $payroll->employee->department ?? '-' }}</td></tr>
    <tr><th>Date of Joining</th><td>{{ $payroll->employee->date_of_joining ? \Carbon\Carbon::parse($payroll->employee->date_of_joining)->format('d-m-Y') : '-' }}</td><th>Branch</th><td>{{ $payroll->employee->branch->name ?? '-' }}</td></tr>
    <tr><th>Phone</th><td>{{ $payroll->employee->phone ?? '-' }}</td><th>Email</th><td>{{ $payroll->employee->email ?? '-' }}</td></tr>
    <tr><th>Bank Name</th><td>{{ $payroll->employee->bank_name ?? '-' }}</td><th>Account No.</th><td>{{ $payroll->employee->account_number ?? '-' }}</td></tr>
    <tr><th>IFSC Code</th><td>{{ $payroll->employee->ifsc_code ?? '-' }}</td><th>PAN</th><td>{{ $payroll->employee->pan_number ?? '-' }}</td></tr>
</table>

<!-- Salary Breakdown -->
<table>
    <tr class="section-title" style="color:black;"><th colspan="2">Earnings</th><th colspan="2">Deductions</th></tr>
    <tr><th>Basic Salary</th><td>₹{{ number_format($payroll->basic, 2) }}</td><th>Professional Tax</th><td>₹{{ number_format($payroll->deductions, 2) }}</td></tr>
    <tr><th>HRA</th><td>₹{{ number_format($payroll->hra, 2) }}</td><td></td><td></td></tr>
    @php $allowances = json_decode($payroll->employee->other_allowances_json ?? '{}', true) ?? []; @endphp
    @foreach($allowances as $type => $amount)
        <tr><th>{{ ucwords(str_replace('_', ' ', $type)) }}</th><td>₹{{ number_format($amount, 2) }}</td><td></td><td></td></tr>
    @endforeach
    <tr><th>Bonus</th><td>₹{{ number_format($payroll->bonus, 2) }}</td><td></td><td></td></tr>
    <tr class="highlight"><th>Total Earnings</th><td>₹{{ number_format($payroll->basic + $payroll->hra + array_sum($allowances) + $payroll->bonus, 2) }}</td><th>Total Deductions</th><td>₹{{ number_format($payroll->deductions, 2) }}</td></tr>
    <tr class="highlight"><th colspan="2">Net Salary</th><td colspan="2"><strong>₹{{ number_format($payroll->net_salary, 2) }}</strong></td></tr>
</table>

<!-- Attendance Summary -->
<table>
    <tr class="section-title"><th colspan="6" style="color:black;">Attendance Summary</th></tr>
    <tr><th>Present</th><td>{{ $payroll->present_days ?? 0 }}</td><th>Absent</th><td>{{ $payroll->leave_days ?? 0 }}</td><th>Half Days</th><td>{{ $payroll->half_days ?? 0 }}</td></tr>
    <tr><th>Paid Leaves</th><td>{{ $payroll->paid_leaves ?? 0 }}</td><th>Unpaid Leaves</th><td>{{ $payroll->unpaid_leaves ?? 0 }}</td><th>Holidays</th><td>{{ $payroll->holidays ?? 0 }}</td></tr>
    <tr><th>Week Off</th><td colspan="5">{{ $payroll->valid_sundays ?? '-' }}</th></tr>
    <tr class="highlight"><th colspan="2">Total Working Hours</th><td colspan="4">{{ $totalHoursFormatted }}</td></tr>
</table>

<!-- Payroll Info -->
<table>
    <tr class="section-title" style="color:black;"><th colspan="4">Payroll Generation Details</th></tr>
    <tr><th>Generated By</th><td>{{ $payroll->generatedBy->name ?? 'System' }}</td><th>Role</th><td>{{ $payroll->salary_generated_role ?? '-' }}</td></tr>
    <tr><th>Generated At</th><td>{{ $payroll->generated_at ? \Carbon\Carbon::parse($payroll->generated_at)->format('d-m-Y H:i') : '-' }}</td><th>Status</th><td>{{ $payroll->status ?? '-' }}</td></tr>
    <tr><th>Remarks</th><td colspan="3">{{ $payroll->remarks ?? '-' }}</td></tr>
</table>

<!-- ✅ Signature Block in Table -->
<table>
    <tr>
        <td style="text-align:right; border:none;">
            <div class="signature">
                <img src="{{ public_path('signature.jpg') }}" alt="Authorized Sign">
                <strong>Authorized Signatory</strong><br>
                EEMOTRACK INDIA PVT. LTD.
            </div>
        </td>
    </tr>
</table>

<p class="note">*** This is a system-generated payroll slip ***</p>

</body>
</html>
