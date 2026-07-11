<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <style>
        @page {
            size: A4 landscape; /* ✅ FIX 1: Landscape */
            margin: 15px 15px 35px 15px; /* bottom extra for footer */
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #2c3e50;
        }

        h2 {
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; /* ✅ FIX 2: Prevent overflow */
        }

        th, td {
            word-wrap: break-word;
            overflow-wrap: break-word;
            text-align: center;
        }

        th {
            background: #34495e;
            color: #ffffff;
            font-weight: bold;
            padding: 6px;
        }

        td {
            padding: 5px;
        }

        /* Row colors */
        .present { background: #e8f8f5; color: #1e8449; }
        .absent  { background: #fdecea; color: #c0392b; }
        .half    { background: #fff9db; color: #b7950b; }

        /* Footer */
        .footer {
            position: fixed;
            bottom: -20px;
            left: 0;
            right: 0;
            text-align: right;
            font-size: 10px;
            color: #555;
        }

        .pagenum:before {
            content: counter(page);
        }

        .pagecount:before {
            content: counter(pages);
        }

        tr {
            page-break-inside: avoid; /* cleaner page breaks */
        }
    </style>
</head>

<body>

<h2>Daily Attendance Report</h2>
<p><strong>Date:</strong> {{ $date }}</p>

{{-- 🔢 SUMMARY TABLE --}}
<table border="2" cellpadding="8" cellspacing="0" style="margin-bottom:18px;">
    <tr>
        <th>Total Employees</th>
        <th>Present</th>
        <th>Absent</th>
        <th>Half Day</th>
        <th>Punch In</th>
        <th>Punch Out</th>
    </tr>
    <tr style="font-weight:bold;">
        <td>{{ $summary['total'] }}</td>
        <td class="present">{{ $summary['present'] }}</td>
        <td class="absent">{{ $summary['absent'] }}</td>
        <td class="half">{{ $summary['half'] }}</td>
        <td>{{ $summary['punch_in'] }}</td>
        <td>{{ $summary['punch_out'] }}</td>
    </tr>
</table>

{{-- 📋 DETAILS TABLE --}}
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th style="width:10%;">Name</th>
        <th style="width:16%;">Email</th>
        <th style="width:10%;">Mobile</th>
        <th style="width:8%;">Status</th>
        <th style="width:7%;">Punch In</th>
        <th style="width:7%;">Punch Out</th>
        <th style="width:7%;">Lat</th>
        <th style="width:7%;">Long</th>
        <th style="width:28%;">Location</th>
    </tr>

    @foreach($rows as $row)
        @php
            $rowClass = $row['status'] === 'present'
                ? 'present'
                : ($row['status'] === 'half_time' ? 'half' : 'absent');
        @endphp

        <tr class="{{ $rowClass }}">
            <td>{{ $row['name'] }}</td>
            <td>{{ $row['email'] }}</td>
            <td>{{ $row['number'] }}</td>
            <td><strong>{{ strtoupper($row['status']) }}</strong></td>
            <td>{{ $row['punch_in'] }}</td>
            <td>{{ $row['punch_out'] }}</td>
            <td>{{ $row['latitude'] }}</td>
            <td>{{ $row['longitude'] }}</td>
            <td style="text-align:left;">{{ $row['location'] }}</td>
        </tr>
    @endforeach
</table>

{{-- 🔢 FOOTER PAGE NUMBER --}}
<div class="footer">
    Page <span class="pagenum"></span> / <span class="pagecount"></span>
</div>

</body>
</html>
