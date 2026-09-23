<?php

namespace Tests\Unit;

use App\Models\Employee;
use App\Services\OfficeAreaService;
use PHPUnit\Framework\TestCase;

class OfficeAreaServiceTest extends TestCase
{
    public function test_distance_uses_meters_and_returns_zero_for_same_point(): void
    {
        $service = new OfficeAreaService();

        $this->assertSame(0.0, $service->distance(25.5941, 85.1376, 25.5941, 85.1376));
        $this->assertEqualsWithDelta(111.2, $service->distance(0, 0, 0.001, 0), 1.0);
    }

    public function test_anywhere_employee_has_no_office_area_restrictions(): void
    {
        $employee = new Employee(['branch_id' => 'anywhere']);

        $this->assertTrue((new OfficeAreaService())->areasFor($employee)->isEmpty());
    }
}
