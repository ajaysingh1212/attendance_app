<?php

namespace Tests\Unit;

use App\Models\Employee;
use App\Services\AttendanceStatusService;
use PHPUnit\Framework\TestCase;

class AttendanceStatusServiceTest extends TestCase
{
    public function test_status_uses_work_start_and_delay_time(): void
    {
        $employee = new Employee(['work_start_time' => '10:00:00', 'delay_time' => 15]);
        $service = new AttendanceStatusService();

        $this->assertSame('present', $service->forPunchIn($employee, '2026-09-23 10:15:00'));
        $this->assertSame('half_time', $service->forPunchIn($employee, '2026-09-23 10:15:01'));
    }
}
