<?php

namespace Tests\Unit;

use App\Models\LeaveType;
use PHPUnit\Framework\TestCase;

class LeaveTypeTest extends TestCase
{
    public function test_only_explicit_paid_leave_name_is_paid(): void
    {
        $this->assertTrue(LeaveType::isPaidName('Paid Leave'));
        $this->assertTrue(LeaveType::isPaidName(' paid_leave '));
        $this->assertTrue(LeaveType::isPaidName('PAID-LEAVE'));

        $this->assertFalse(LeaveType::isPaidName('Unpaid Leave'));
        $this->assertFalse(LeaveType::isPaidName('Sick Leave'));
        $this->assertFalse(LeaveType::isPaidName('Casual Leave'));
        $this->assertFalse(LeaveType::isPaidName(null));
    }
}
