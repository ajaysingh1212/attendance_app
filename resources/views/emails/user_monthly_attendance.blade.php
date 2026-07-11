<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; font-size:14px; color:#2c3e50;">

<h3 style="color:#34495e;">Monthly Attendance Report</h3>

<p>
    <strong>Name:</strong> {{ $user->name }} <br>
    <strong>Email:</strong> {{ $user->email }} <br>
    <strong>Month:</strong> {{ $monthName }} {{ $year }}
</p>

<table width="100%" cellpadding="6" cellspacing="0" border="1"
       style="border-collapse:collapse;font-size:13px;">
    <thead style="background:#34495e;color:#ffffff;">
        <tr>
            <th>Date</th>
            <th>Day</th>
            <th>Status</th>
            <th>Punch In</th>
            <th>Punch Out</th>
        </tr>
    </thead>

    <tbody>
        @foreach($rows as $row)

            @php
                $bg = '#ffffff';
                if($row['status'] === 'present') $bg = '#e8f8f5';
                if($row['status'] === 'half_time') $bg = '#fff9db';
                if($row['status'] === 'week_off') $bg = '#f0f0f0';
                if($row['status'] === 'absent') $bg = '#fdecea';
            @endphp

            <tr style="background:{{ $bg }};">
                <td>{{ $row['date'] }}</td>
                <td>{{ \Carbon\Carbon::createFromFormat('d-m-Y',$row['date'])->format('l') }}</td>
                <td>
                    <strong>
                        {{ strtoupper(str_replace('_',' ', $row['status'])) }}
                    </strong>
                </td>
                <td>{{ $row['punch_in'] }}</td>
                <td>{{ $row['punch_out'] }}</td>
            </tr>

        @endforeach
    </tbody>
</table>

<p style="margin-top:10px;">
    📎 <strong>Note:</strong> Complete detailed report is attached as PDF.
</p>

<p>
    Regards,<br>
    <strong>{{ config('app.name') }}</strong>
</p>

</body>
</html>
