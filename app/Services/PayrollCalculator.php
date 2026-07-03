<?php

namespace App\Services;

use App\Models\AttendanceDetail;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\SalaryIncrement;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class PayrollCalculator
{
    public function calculate(Employee $employee, int $month, int $year): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $joining = $employee->date_of_joining
            ? Carbon::parse($employee->date_of_joining)->startOfDay()
            : null;

        $salary = $this->resolveSalary($employee, $start);
        $leaveRequests = collect();

        if ($employee->user_id) {
            $leaveRequests = LeaveRequest::with('leaveType')
                ->where('user_id', $employee->user_id)
                ->whereRaw("LOWER(TRIM(status)) = 'approved'")
                ->where('date_from', '<=', $end->toDateString())
                ->where('date_to', '>=', $start->toDateString())
                ->get();
        }

        $approvedLeaves = $this->expandApprovedLeaves($leaveRequests, $start, $end, $joining);

        $attendances = AttendanceDetail::where(function ($query) use ($employee) {
                $query->where('employee_id', $employee->id);
                if ($employee->user_id) {
                    $query->orWhere('user_id', $employee->user_id);
                }
            })
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy(fn ($record) => Carbon::parse($record->getRawOriginal('date'))->toDateString());

        $holidayDates = [];
        $holidays = Holiday::where('start_date', '<=', $end->toDateString())
            ->where('end_date', '>=', $start->toDateString())
            ->get();

        foreach ($holidays as $holiday) {
            foreach (CarbonPeriod::create($holiday->start_date, $holiday->end_date) as $date) {
                if ($date->between($start, $end)) {
                    $holidayDates[$date->toDateString()] = true;
                }
            }
        }

        $presentDates = [];
        $halfDayDates = [];
        $paidLeaveDates = [];
        $unpaidLeaveDates = [];
        $absentDates = [];
        $paidHolidayDates = [];
        $weekOffDates = [];
        $paidDayValues = [];

        foreach (CarbonPeriod::create($start, $end) as $date) {
            $dateString = $date->toDateString();

            if ($joining && $date->lt($joining)) {
                continue;
            }

            if (array_key_exists($dateString, $approvedLeaves)) {
                if ($approvedLeaves[$dateString]) {
                    $paidLeaveDates[$dateString] = true;
                    $paidDayValues[$dateString] = 1.0;
                } else {
                    $unpaidLeaveDates[$dateString] = true;
                }
                continue;
            }

            if ($attendances->has($dateString)) {
                $statuses = $attendances[$dateString]
                    ->pluck('status')
                    ->map(fn ($status) => strtolower(trim((string) $status)))
                    ->all();

                if (array_intersect(['present', 'late', 'week_off'], $statuses)) {
                    $presentDates[$dateString] = true;
                    $paidDayValues[$dateString] = 1.0;
                } elseif (array_intersect(['half_day', 'half_time'], $statuses)) {
                    $halfDayDates[$dateString] = true;
                    $paidDayValues[$dateString] = 0.5;
                } elseif (array_intersect(['paid leave', 'paid_leave'], $statuses)) {
                    $paidLeaveDates[$dateString] = true;
                    $paidDayValues[$dateString] = 1.0;
                } elseif (collect($statuses)->contains(fn ($status) => str_contains($status, 'holiday'))) {
                    $paidHolidayDates[$dateString] = true;
                    $paidDayValues[$dateString] = 1.0;
                } elseif (in_array('absent', $statuses, true)) {
                    $absentDates[$dateString] = true;
                } else {
                    $unpaidLeaveDates[$dateString] = true;
                }
                continue;
            }

            if (isset($holidayDates[$dateString])) {
                $paidHolidayDates[$dateString] = true;
                $paidDayValues[$dateString] = 1.0;
            } elseif ($date->isSunday()) {
                $weekOffDates[$dateString] = true;
                $paidDayValues[$dateString] = 1.0;
            } else {
                $absentDates[$dateString] = true;
            }
        }

        $finalPaidDays = array_sum($paidDayValues);
        $perDaySalary = $start->daysInMonth > 0
            ? $salary['monthly_salary'] / $start->daysInMonth
            : 0;

        return array_merge($salary, [
            'working_days' => $start->daysInMonth,
            'present_days' => count($presentDates),
            'half_days' => count($halfDayDates) * 0.5,
            'paid_leaves' => count($paidLeaveDates),
            'leave_days' => count($unpaidLeaveDates),
            'absent_days' => count($absentDates),
            'holidays' => count($paidHolidayDates),
            'valid_sundays' => count($weekOffDates),
            'final_paid_days' => $finalPaidDays,
            'per_day_salary' => round($perDaySalary, 2),
            'net_salary' => round($perDaySalary * $finalPaidDays, 2),
            'period' => $start->format('F Y'),
        ]);
    }

    /**
     * Returns a date => is-paid map. Separate one-day requests remain separate
     * dates, while overlapping requests can never count the same date twice.
     */
    public function expandApprovedLeaves(
        iterable $leaveRequests,
        Carbon $start,
        Carbon $end,
        ?Carbon $joining = null
    ): array {
        $dates = [];

        foreach ($leaveRequests as $leave) {
            $isPaid = LeaveType::isPaidName($leave->leaveType->name ?? null);

            foreach (CarbonPeriod::create($leave->date_from, $leave->date_to) as $date) {
                if (!$date->between($start, $end) || ($joining && $date->lt($joining))) {
                    continue;
                }

                $key = $date->toDateString();
                $dates[$key] = array_key_exists($key, $dates)
                    ? ($dates[$key] && $isPaid)
                    : $isPaid;
            }
        }

        return $dates;
    }

    private function resolveSalary(Employee $employee, Carbon $payrollMonth): array
    {
        $basic = (float) $employee->basic_salary;
        $hra = (float) $employee->hra;
        $allowance = is_array($employee->other_allowances)
            ? (float) array_sum($employee->other_allowances)
            : (float) $employee->other_allowances;
        $deductions = (float) ($employee->deductions ?? 0);

        $increment = SalaryIncrement::where('employee_id', $employee->id)
            ->whereRaw("LOWER(TRIM(status)) = 'approved'")
            ->get()
            ->filter(function ($item) use ($payrollMonth) {
                $effectiveMonth = substr(trim((string) $item->increment_month), 0, 7);

                return preg_match('/^\d{4}-\d{2}$/', $effectiveMonth)
                    && $effectiveMonth <= $payrollMonth->format('Y-m');
            })
            ->sortByDesc(fn ($item) => substr(trim((string) $item->increment_month), 0, 7))
            ->first();

        if ($increment) {
            $basic = (float) $increment->new_basic;
            $hra = (float) $increment->new_hra;
            $allowance = (float) $increment->new_allowance;
        }

        $gross = $increment
            ? (float) $increment->new_gross_salary
            : $basic + $hra + $allowance;

        return [
            'basic' => $basic,
            'hra' => $hra,
            'allowance' => $allowance,
            'gross_salary' => $gross,
            'deductions' => $deductions,
            'monthly_salary' => $gross - $deductions,
            'salary_increment_id' => $increment?->id,
            'salary_source' => $increment ? 'Approved Increment' : 'Employee Master',
            'increment_month' => $increment?->increment_month,
            'old_gross_salary' => $increment ? (float) $increment->old_gross_salary : null,
            'remarks' => $increment
                ? "Salary increment effective from {$increment->increment_month}."
                : 'Salary calculated from employee master.',
        ];
    }
}
