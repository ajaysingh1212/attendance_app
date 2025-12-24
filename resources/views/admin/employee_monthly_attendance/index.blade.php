@extends('layouts.admin')

@section('styles')
<style>
    .calendar {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 5px;
    }
    .day {
        border: 1px solid #ddd;
        padding: 10px;
        min-height: 80px;
        position: relative;
        border-radius: 6px;
    }
    .day-header {
        text-align: center;
        font-weight: bold;
        background: #f1f1f1;
        padding: 5px;
    }
    .today { border: 2px solid #007bff; }
    .status-present { background: #d4edda; }   /* Green */
    .status-absent  { background: #f8d7da; }   /* Red */
    .status-half    { background: #fff3cd; }   /* Yellow */
    .status-leave   { background: #d1ecf1; }   /* Light Blue */
    .status-holiday { background: #e2e3e5; }   /* Gray */
    .sunday { background: #f5c6cb !important; } /* Highlight Sundays */
    small { display: block; font-size: 12px; }

    .summary-flex { display:flex; flex-wrap:wrap; gap:8px; }
    .summary-item { padding:6px 10px; border:1px solid #ddd; border-radius:6px; display:flex; align-items:center; }
    .summary-item strong { margin-right:8px; }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h4>Employee Monthly Attendance</h4>
    </div>
    <div class="card-body">
        {{-- Employee & Month Form --}}
        <form method="GET" action="{{ route('admin.employee_monthly_attendance.index') }}" class="form-inline mb-4">
            <select name="employee_id" class="form-control mr-2" required>
                <option value="">Select Employee</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ (isset($employeeId) && $employeeId == $emp->id) ? 'selected' : '' }}>
                        {{ $emp->full_name }} ({{ $emp->employee_code }})
                    </option>
                @endforeach
            </select>

            <input type="month" name="month" class="form-control mr-2" value="{{ $month ?? '' }}" required>
            <button type="submit" class="btn btn-primary">Check</button>
        </form>

        @if($attendanceSummary)
            @php
                $startOfMonth   = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                $endOfMonth     = \Carbon\Carbon::createFromFormat('Y-m', $month)->endOfMonth();
                $firstDayOfWeek = $startOfMonth->dayOfWeek;

                $attendanceMap = $attendanceSummary['attendanceMap'] ?? [];
                $leaveMap      = $attendanceSummary['leaveMap'] ?? [];
                $holidayMap    = $attendanceSummary['holidayMap'] ?? [];
            @endphp

            {{-- Calendar Title --}}
            <h5 class="mb-3">
                Attendance Calendar for {{ $attendanceSummary['employee']->full_name }}
                ({{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }})
            </h5>

            {{-- Status Summary --}}
            <div class="mb-4">
                <h5>Status Summary</h5>
                <div class="summary-flex mt-2">
                    <div class="summary-item" style="background:#d4edda;">
                        <strong>Present:</strong>
                        <span class="badge badge-success">{{ $attendanceSummary['present'] ?? 0 }}</span>
                    </div>
                    <div class="summary-item" style="background:#f8d7da;">
                        <strong>Absent:</strong>
                        <span class="badge badge-danger">{{ $attendanceSummary['absent'] ?? 0 }}</span>
                    </div>
                    <div class="summary-item" style="background:#fff3cd;">
                        <strong>Half Day:</strong>
                        <span class="badge badge-warning">{{ $attendanceSummary['halfDay'] ?? 0 }}</span>
                    </div>
                    <div class="summary-item" style="background:#d1ecf1;">
                        <strong>Leave:</strong>
                        <span class="badge badge-info">{{ count($leaveMap) }}</span>
                    </div>
                    <div class="summary-item" style="background:#e2e3e5;">
                        <strong>Holiday:</strong>
                        <span class="badge badge-secondary">{{ count($holidayMap) }}</span>
                    </div>
                </div>
            </div>

            {{-- Calendar Headers --}}
            <div class="calendar mb-2">
                @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $dayName)
                    <div class="day-header">{{ $dayName }}</div>
                @endforeach
            </div>

            {{-- Calendar Days --}}
            <div class="calendar">
                {{-- Empty cells for first week offset --}}
                @for($i = 0; $i < $firstDayOfWeek; $i++)
                    <div class="day"></div>
                @endfor

                {{-- Days of Month --}}
                @for($date = $startOfMonth->copy(); $date <= $endOfMonth; $date->addDay())
                    @php
                        $classes = 'day';
                        $content = '';
                        $dateStr = $date->toDateString();

                        if ($date->isSunday()) $classes .= ' sunday';

                        if (isset($holidayMap[$dateStr])) {
                            $classes .= ' status-holiday';
                            $content = 'Holiday: ' . $holidayMap[$dateStr];
                        } elseif (isset($leaveMap[$dateStr])) {
                            $classes .= ' status-leave';
                            $content = $leaveMap[$dateStr];
                        } elseif (isset($attendanceMap[$dateStr])) {
                            $status = $attendanceMap[$dateStr];
                            if ($status === 'present') {
                                $classes .= ' status-present'; $content = 'Present';
                            } elseif ($status === 'absent') {
                                $classes .= ' status-absent';  $content = 'Absent';
                            } elseif ($status === 'half_time') {
                                $classes .= ' status-half';    $content = 'Half Day';
                            }
                        }

                        if ($date->isToday()) $classes .= ' today';
                    @endphp

                    <div class="{{ $classes }}">
                        <strong>{{ $date->day }}</strong>
                        <small>{{ $content }}</small>
                    </div>
                @endfor
            </div>
        @endif
    </div>
</div>
@endsection
