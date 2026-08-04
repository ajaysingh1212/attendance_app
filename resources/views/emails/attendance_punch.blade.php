<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>

<h2>Hello {{ $user->name }}</h2>

<p>
    Your <strong>{{ strtoupper(str_replace('_',' ', $type)) }}</strong>
    has been recorded successfully.
</p>

<hr>

<p><strong>Date & Time:</strong> {{ $data['time'] ?? '-' }}</p>
<p><strong>IP Address:</strong> {{ $data['ip'] ?? '-' }}</p>

<p><strong>User Agent:</strong><br>
    <small>{{ $data['user_agent'] ?? '-' }}</small>
</p>

@if(!empty($data['location']))
<p><strong>Location:</strong> {{ $data['location'] }}</p>
@endif

@if(!empty($data['latitude']) && !empty($data['longitude']))
<p><strong>Latitude / Longitude:</strong>
    {{ $data['latitude'] }}, {{ $data['longitude'] }}
</p>
@endif

<hr>

@if($type === 'punch_in')
    <p><strong>Expected In:</strong> {{ $data['expected_in'] ?? '-' }}</p>
    <p><strong>Actual In:</strong> {{ $data['actual_in'] ?? '-' }}</p>
@endif

@if($type === 'punch_out')
    <p><strong>Expected Out:</strong> {{ $data['expected_out'] ?? '-' }}</p>
    <p><strong>Actual Out:</strong> {{ $data['actual_out'] ?? '-' }}</p>
@endif

<p><strong>Status:</strong>
    {{ ucfirst(str_replace('_',' ', $data['status'] ?? '-')) }}
</p>

<p><strong>Type:</strong> {{ $data['type'] ?? '-' }}</p>

@if(isset($data['late_by_minutes']))
<p><strong>Late By (minutes):</strong> {{ $data['late_by_minutes'] }}</p>
@endif

@if(isset($data['left_early_by']))
<p><strong>Left Early By (minutes):</strong> {{ $data['left_early_by'] }}</p>
@endif

@if(isset($data['overtime']))
<p><strong>Overtime (minutes):</strong> {{ $data['overtime'] }}</p>
@endif

<hr>

<p>
    Regards,<br>
    {{ config('app.name') }}
</p>

</body>
</html>
