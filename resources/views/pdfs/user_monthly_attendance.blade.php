<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <style>
        @page {
            size: A4 landscape;
            margin: 15px 15px 35px 15px;
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
            table-layout: fixed;
        }

        th, td {
            border: 1px solid #2c3e50;
            padding: 5px;
            text-align: center;
            word-wrap: break-word;
        }

        th {
            background: #34495e;
            color: #ffffff;
            font-weight: bold;
        }

        /* STATUS COLORS */
        .present { background:#e8f8f5; color:#1e8449; }
        .absent  { background:#fdecea; color:#c0392b; }
        .half    { background:#fff9db; color:#b7950b; }
        .weekoff { background:#f0f0f0; color:#555; }

        tr { page-break-inside: avoid; }

        /* FOOTER */
        .footer {
            position: fixed;
            bottom: -20px;
            right: 0;
            font-size: 10px;
            color: #555;
        }

        .pagenum:before { content: counter(page); }
        .pagecount:before { content: counter(pages); }
    </style>
</head>

<body>

<h2>Monthly Attendance Report</h2>
<p>
    <strong>Name:</strong> {{ $user->name }} <br>
    <strong>Email:</strong> {{ $user->email }} <br>
    <strong>Month:</strong> {{ $monthName }} {{ $year }}
</p>

<table>
    <thead>
        <tr>
            <th style="width:10%;">Date</th>
            <th style="width:10%;">Day</th>
            <th style="width:12%;">Status</th>
            <th style="width:10%;">Punch In</th>
            <th style="width:10%;">Punch Out</th>
            <th style="width:10%;">Lat</th>
            <th style="width:10%;">Long</th>
            <th style="width:28%;">Location</th>
        </tr>
    </thead>

    <tbody>
        @foreach($rows as $row)

            @php
                $class =
                    $row['status'] === 'present' ? 'present' :
                    ($row['status'] === 'half_time' ? 'half' :
                    ($row['status'] === 'week_off' ? 'weekoff' : 'absent'));
            @endphp

            <tr class="{{ $class }}">
                <td>{{ $row['date'] }}</td>
                <td>{{ \Carbon\Carbon::createFromFormat('d-m-Y',$row['date'])->format('l') }}</td>
                <td><strong>{{ strtoupper(str_replace('_',' ', $row['status'])) }}</strong></td>
                <td>{{ $row['punch_in'] }}</td>
                <td>{{ $row['punch_out'] }}</td>
                <td>{{ $row['latitude'] }}</td>
                <td>{{ $row['longitude'] }}</td>
                <td style="text-align:left;">{{ $row['location'] }}</td>
            </tr>

        @endforeach
    </tbody>
</table>

<div class="footer">
    Page <span class="pagenum"></span> / <span class="pagecount"></span>
</div>

</body>
</html>
