<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Payroll;
use App\Services\PayrollCalculator;
use App\Services\PayrollGenerator;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PayrollGenerationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Carbon::setTestNow('2026-10-07 12:00:00');

        Schema::create('employees', function (Blueprint $t) {
            $t->id(); $t->integer('user_id')->nullable(); $t->integer('office_branch_id')->nullable();
            $t->date('date_of_joining')->nullable();
            foreach (['basic_salary', 'hra', 'other_allowances', 'deductions'] as $field) $t->decimal($field, 12, 2)->default(0);
            $t->timestamps();
        });
        Schema::create('attendance_details', function (Blueprint $t) {
            $t->id(); $t->integer('employee_id')->nullable(); $t->integer('user_id')->nullable();
            $t->date('date'); $t->string('status'); $t->softDeletes();
        });
        Schema::create('salary_increments', function (Blueprint $t) {
            $t->id(); $t->integer('employee_id'); $t->string('status'); $t->softDeletes();
        });
        Schema::create('holidays', function (Blueprint $t) {
            $t->id(); $t->date('start_date'); $t->date('end_date'); $t->softDeletes();
        });
        Schema::create('payrolls', function (Blueprint $t) {
            $t->id(); $t->integer('employee_id'); $t->integer('office_branch_id')->nullable();
            $t->integer('month'); $t->integer('year'); $t->string('status');
            foreach (['working_days', 'present_days', 'half_days', 'paid_leaves', 'leave_days', 'holidays', 'absent_days', 'final_paid_days', 'valid_sundays', 'basic', 'hra', 'allowance', 'gross_salary', 'deductions', 'net_salary', 'remaining_salary', 'manual_adjustment', 'bonus'] as $field) $t->decimal($field, 12, 2)->nullable();
            $t->integer('salary_increment_id')->nullable(); $t->text('remarks')->nullable();
            $t->text('message')->nullable(); $t->dateTime('generated_at')->nullable(); $t->timestamps();
        });
        Schema::create('payroll_part_payments', function (Blueprint $t) {
            $t->id(); $t->integer('payroll_id'); $t->decimal('part_amount', 12, 2);
        });
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        DB::disconnect('sqlite');
        parent::tearDown();
    }

    public function test_september_snapshot_refreshes_from_full_attendance_without_changing_id(): void
    {
        $employee = Employee::create(['basic_salary' => 30000]);
        $payroll = Payroll::create(['employee_id' => $employee->id, 'month' => 9, 'year' => 2026,
            'status' => 'Pending', 'present_days' => 4, 'absent_days' => 21, 'net_salary' => 9000, 'remaining_salary' => 9000]);
        // Legacy attendance may link by employee ID without a user account.
        for ($day = 1; $day <= 30; $day++) {
            $date = Carbon::create(2026, 9, $day);
            if (!$date->isSunday()) DB::table('attendance_details')->insert([
                'employee_id' => $employee->id, 'date' => $date->toDateString(), 'status' => $day === 15 ? 'half_time' : ' Present ',
            ]);
        }
        // Duplicate daily records must not inflate payable days.
        DB::table('attendance_details')->insert(['employee_id' => $employee->id, 'date' => '2026-09-01', 'status' => 'absent']);
        $calculator = app(PayrollCalculator::class);
        $expected = $calculator->calculate($employee, 9, 2026);
        DB::transaction(fn () => app(PayrollGenerator::class)->save($employee, 9, 2026, $payroll));
        $payroll->refresh();

        $this->assertSame(1, Payroll::count());
        $this->assertSame($expected['net_salary'], (float) $payroll->net_salary);
        $this->assertSame(29500.0, (float) $payroll->net_salary);
        $this->assertSame(0.0, (float) $payroll->absent_days);
        $this->assertSame(25.0, (float) $payroll->present_days);
        $this->assertSame(0.5, (float) $payroll->half_days);
        $this->assertSame((float) $payroll->net_salary, (float) $payroll->remaining_salary);
    }

    public function test_current_and_future_months_cannot_be_finalized(): void
    {
        $generator = app(PayrollGenerator::class);
        foreach ([[10, 2026], [11, 2026], [1, 2027]] as [$month, $year]) {
            try {
                $generator->assertCompletedMonth($month, $year);
                $this->fail('Incomplete month was allowed.');
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('month', $e->errors());
            }
        }
        Carbon::setTestNow('2026-10-31 23:59:59');
        $this->expectException(ValidationException::class);
        $generator->assertCompletedMonth(10, 2026);
    }

    public function test_payroll_is_available_on_first_day_of_next_month(): void
    {
        Carbon::setTestNow('2026-11-01 00:00:00');
        app(PayrollGenerator::class)->assertCompletedMonth(10, 2026);
        $this->assertTrue(true);
    }

    public function test_paid_or_adjusted_payroll_and_payment_history_are_protected(): void
    {
        $employee = Employee::create(['basic_salary' => 30000]);
        $generator = app(PayrollGenerator::class);
        foreach ([['status' => 'Paid'], ['status' => 'Partially Paid'], ['manual_adjustment' => 500], ['remaining_salary' => 5000], ['bonus' => 100], ['message' => 'Manual correction']] as $overrides) {
            $payroll = Payroll::create(array_merge(['employee_id' => $employee->id, 'month' => 9, 'year' => 2026, 'status' => 'Pending', 'net_salary' => 9000, 'remaining_salary' => 9000], $overrides));
            $this->assertFalse($generator->canRecalculate($payroll));
        }
        $payroll = Payroll::create(['employee_id' => $employee->id, 'month' => 9, 'year' => 2026, 'status' => 'Pending', 'net_salary' => 9000]);
        DB::table('payroll_part_payments')->insert(['payroll_id' => $payroll->id, 'part_amount' => 1000]);
        try {
            $generator->save($employee, 9, 2026, $payroll);
            $this->fail('Payroll with payments was overwritten.');
        } catch (ValidationException $e) {
            $this->assertSame(9000.0, (float) $payroll->fresh()->net_salary);
            $this->assertSame(1, DB::table('payroll_part_payments')->count());
        }
    }
}
