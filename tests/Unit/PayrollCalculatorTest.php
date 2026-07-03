<?php

namespace Tests\Unit;

use App\Services\PayrollCalculator;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class PayrollCalculatorTest extends TestCase
{
    public function test_separate_one_day_paid_leave_requests_count_as_two_dates(): void
    {
        $paidLeaveType = (object) ['name' => 'Paid Leave'];
        $requests = [
            (object) [
                'date_from' => '2026-06-01',
                'date_to' => '2026-06-01',
                'leaveType' => $paidLeaveType,
            ],
            (object) [
                'date_from' => '2026-06-02',
                'date_to' => '2026-06-02',
                'leaveType' => $paidLeaveType,
            ],
        ];

        $dates = (new PayrollCalculator())->expandApprovedLeaves(
            $requests,
            Carbon::parse('2026-06-01'),
            Carbon::parse('2026-06-30')
        );

        $this->assertCount(2, $dates);
        $this->assertTrue($dates['2026-06-01']);
        $this->assertTrue($dates['2026-06-02']);
    }
}
