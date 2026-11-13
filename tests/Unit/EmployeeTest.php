<?php

namespace Tests\Unit;

use App\Models\Employee;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class EmployeeTest extends TestCase
{
    public function test_employee_duration_months_is_cast_to_integer_for_carbon(): void
    {
        $employee = new Employee();
        $employee->setRawAttributes(['employee_duration_months' => '12']);

        $endDate = Carbon::parse('2026-01-15')->addMonths($employee->employee_duration_months);

        $this->assertSame(12, $employee->employee_duration_months);
        $this->assertSame('2027-01-15', $endDate->toDateString());
    }
}
