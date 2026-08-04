<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif">

<h2 style="color:#2c3e50;">Daily Attendance Report</h2>
<p><strong>Date:</strong> {{ $date }}</p>

{{-- 🔢 SUMMARY TABLE (EXACT SAME BORDER AS DETAILS TABLE) --}}
<table width="100%"
       cellpadding="10"
       cellspacing="0"
       border="2"
       style="margin-bottom:25px;border-collapse:collapse;font-size:14px;">

    <thead>
        <tr style="background:#34495e;color:#ffffff;font-weight:bold;text-align:center;">
            <th>Total Employees</th>
            <th>Present</th>
            <th>Absent</th>
            <th>Half Day</th>
            <th>Punch In</th>
            <th>Punch Out</th>
        </tr>
    </thead>

    <tbody>
        <tr align="center" style="font-weight:bold;font-size:16px;">
            <td style="background:#f4f6f7;color:#2c3e50;">
                {{ $summary['total'] }}
            </td>

            <td style="background:#e8f8f5;color:#1e8449;">
                {{ $summary['present'] }}
            </td>

            <td style="background:#fdecea;color:#c0392b;">
                {{ $summary['absent'] }}
            </td>

            <td style="background:#fff9db;color:#b7950b;">
                {{ $summary['half'] }}
            </td>

            <td style="background:#ebf5fb;color:#2471a3;">
                {{ $summary['punch_in'] }}
            </td>

            <td style="background:#f4ecf7;color:#6c3483;">
                {{ $summary['punch_out'] }}
            </td>
        </tr>
    </tbody>
</table>

{{-- 📋 DETAILS TABLE --}}
<table width="100%" cellpadding="6" cellspacing="0" border="1">
    <thead style="background:#34495e;color:white;">
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Mobile</th>
            <th>Status</th>
            <th>Punch In</th>
            <th>Punch Out</th>
            <th>Lat</th>
            <th>Long</th>
            <th>Location</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)

        @php
            $bg = '#ffffff';
            if($row['status'] === 'present') $bg = '#e8f8f5';
            if($row['status'] === 'half_time') $bg = '#fff9db';
            if($row['status'] === 'absent') $bg = '#fdecea';
        @endphp

        <tr style="background:{{ $bg }};">
            <td>{{ $row['name'] }}</td>
            <td>{{ $row['email'] }}</td>
            <td>{{ $row['number'] }}</td>
            <td>
                <strong style="
                    color:
                    {{ $row['status'] === 'present' ? 'green' :
                       ($row['status'] === 'half_time' ? '#d4a000' : 'red') }}">
                    {{ strtoupper($row['status']) }}
                </strong>
            </td>
            <td>{{ $row['punch_in'] }}</td>
            <td>{{ $row['punch_out'] }}</td>
            <td>{{ $row['latitude'] }}</td>
            <td>{{ $row['longitude'] }}</td>
            <td>{{ $row['location'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<br>
<p>Regards,<br><strong>{{ config('app.name') }}</strong></p>

</body>
</html>
