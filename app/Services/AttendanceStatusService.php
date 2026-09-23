<?php

namespace App\Services;

use App\Models\Employee;
use Carbon\Carbon;

class AttendanceStatusService
{
    public function forPunchIn(Employee $employee, Carbon|string $punchIn): string
    {
        $punch = $punchIn instanceof Carbon ? $punchIn->copy() : Carbon::parse($punchIn);
        if (!$employee->work_start_time) {
            return 'present';
        }

        $allowedUntil = $punch->copy()
            ->setTimeFromTimeString(Carbon::parse($employee->work_start_time)->format('H:i:s'))
            ->addMinutes((int) ($employee->delay_time ?? 0));

        return $punch->greaterThan($allowedUntil) ? 'half_time' : 'present';
    }
}
