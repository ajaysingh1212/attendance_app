<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Profile PDF</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .watermark {
            position: fixed;
            top: 35%;
            left: 25%;
            width: 400px;
            opacity: 0.05;
            z-index: -1;
        }
        h2 {
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 5px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        td, th {
            padding: 6px 8px;
            border: 1px solid #ccc;
            vertical-align: top;
        }
        th {
            background-color: #f1f1f1;
            text-align: left;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
            padding: 3px 6px;
            border-radius: 4px;
        }
        .badge-danger {
            background-color: #dc3545;
            color: white;
            padding: 3px 6px;
            border-radius: 4px;
        }
        .doc-img {
            width: 100%;
            max-height: 400px;
            object-fit: contain;
            margin-bottom: 35px;
            border: 1px solid #999;
            page-break-inside: avoid;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #888;
            margin-top: 30px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header img {
            height: 50px;
        }
        .company-info {
            font-size: 11px;
            text-align: right;
        }
        .img-label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .doc-section {
            page-break-before: always;
        }
    </style>
</head>
<body>

    <!-- Watermark -->
<img src="{{ $logoSrc }}" class="watermark" alt="Watermark">
    <!-- Company Header -->
    <div class="header">
        <img src="{{ $logoSrc }}" alt="EemoTrack Logo" style="height: 50px;">
        <div class="company-info">
            <strong>EEMOT Private Limited</strong><br>
            Kamla Market, Bhattacharya Road, Patna – 800001, Bihar, India<br>
            www.eemotrack.com | info@eemotrack.com | +91 78578 68055
        </div>
    </div>

    <!-- Title -->
    <h2>👤 Employee Profile: {{ $employee->full_name }}</h2>

    <!-- Profile Information -->
    <table>
        <tr><th>Full Name</th><td>{{ $employee->full_name }}</td><th>Email</th><td>{{ $employee->email }}</td></tr>
        <tr><th>Phone</th><td>{{ $employee->phone }}</td><th>Employee Code</th><td>{{ $employee->employee_code }}</td></tr>
        <tr><th>Position</th><td>{{ $employee->position }}</td><th>Department</th><td>{{ $employee->department }}</td></tr>
        <tr><th>Joining Date</th><td>{{ $employee->date_of_joining }}</td><th>Status</th>
            <td>
                @if($employee->status === 'Active')
                    <span class="badge-success">Active</span>
                @else
                    <span class="badge-danger">Inactive</span>
                @endif
            </td>
        </tr>
        <tr><th>Reporting To</th><td colspan="3">{{ optional($employee->manager)->name ?? 'N/A' }}</td></tr>
    </table>
@php
    // Breakdown JSON decode
    $allowanceBreakdown = json_decode($employee->other_allowances_json ?? '{}', true);
    $allowanceBreakdown = is_array($allowanceBreakdown) ? $allowanceBreakdown : [];

    // Total calculate (agar DB me na ho to)
    $totalAllowances = $employee->other_allowances ?? array_sum($allowanceBreakdown);
@endphp
    <!-- Bank Info -->
    <table>
        <tr><th>Bank Name</th><td>{{ $employee->bank_name }}</td><th>Account Number</th><td>{{ $employee->account_number }}</td></tr>
        <tr><th>IFSC Code</th><td>{{ $employee->ifsc_code }}</td><th>PAN Number</th><td>{{ $employee->pan_number }}</td></tr>
        <tr><th>Aadhaar Number</th><td>{{ $employee->aadhaar_number }}</td><th>Payment Mode</th><td>{{ $employee->payment_mode }}</td></tr>
        <tr><th>Basic Salary</th><td>₹{{ $employee->basic_salary }}</td><th>HRA</th><td>₹{{ $employee->hra }}</td></tr>
    <tr>
        <th>Other Allowances</th>
        <td colspan="3">
            @foreach($allowanceBreakdown as $type => $amount)
                {{ ucwords(str_replace('_', ' ', $type)) }}: ₹{{ $amount }}<br>
            @endforeach
            <strong>Total: ₹{{ $totalAllowances }}</strong>
        </td>
    </tr>
        <tr><th colspan="2">Net Salary</th><td colspan="2">₹{{ $employee->net_salary }}</td></tr>
    </table>

    <!-- Work Schedule -->
    <table>
        <tr><th>Start Time</th><td>{{ $employee->work_start_time }}</td><th>End Time</th><td>{{ $employee->work_end_time }}</td></tr>
        <tr><th>Working Hours</th><td>{{ $employee->working_hours }}</td><th>Weekly Off</th><td>{{ ucfirst($employee->weekly_off_day) }}</td></tr>
        <tr><th>Attendance Source</th><td>{{ $employee->attendance_source }}</td><th>Radius (m)</th><td>{{ $employee->attendance_radius_meter ?? 'N/A' }}</td></tr>
    </table>

    <!-- Document Images Section -->
    @if(count($documents) > 0)
    <div class="doc-section">
        <h2>📎 Uploaded Documents</h2>
        @foreach ($documents as $doc)
            @if(in_array($doc['extension'], ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                <div class="img-label">{{ $doc['label'] }} ({{ strtoupper($doc['extension']) }})</div>
                @if(file_exists($doc['absolute_path']))
                    <img src="data:image/{{ $doc['extension'] }};base64,{{ base64_encode(file_get_contents($doc['absolute_path'])) }}" class="doc-img" alt="{{ $doc['label'] }}">
                @else
                    <span style="color: red;">Image not found</span>
                @endif
            @endif
        @endforeach
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        Generated by EemoTrack · {{ now()->format('Y-m-d H:i:s') }}
    </div>

</body>
</html>
