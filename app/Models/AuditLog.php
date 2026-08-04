<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $table = 'audit_logs';

    protected $fillable = [
        'description',
        'action',
        'module',
        'subject_id',
        'subject_type',
        'user_id',
        'actor_name',
        'actor_role',
        'target_user_id',
        'target_user_name',
        'subject_name',
        'properties',
        'host',
    ];

    protected $casts = [
        'properties' => 'collection',
    ];

    public static function moduleOptions(): array
    {
        return [
            'AddRequestAmount' => 'Add Request Amount',
            'AppUpdate' => 'App Updates',
            'AttendanceDetail' => 'Attendance Detail',
            'Branch' => 'Branch',
            'Company' => 'Company',
            'Employee' => 'Payroll Detail',
            'Expense' => 'Expenses',
            'ExpenseCategory' => 'Expense Categories',
            'ExperienceLetter' => 'Experience Letter',
            'GroupTask' => 'Task Assign',
            'Holiday' => 'Holidays',
            'Income' => 'Income',
            'IncomeCategory' => 'Income Categories',
            'LeaveRequest' => 'Leave Requests',
            'LeaveType' => 'Leave Types',
            'MakeCustomer' => 'Make Customer',
            'Order' => 'Order',
            'Payroll' => 'Salary Payroll',
            'PayrollAdjustment' => 'Payroll Adjustments',
            'Permission' => 'Permissions',
            'Product' => 'Products',
            'ProductCategory' => 'Categories',
            'ProductTag' => 'Tags',
            'Report' => 'Reports',
            'Role' => 'Roles',
            'SalaryIncrement' => 'Salary Increment',
            'ShowReport' => 'Show Report',
            'TaskGroup' => 'Grouping',
            'TrackMember' => 'Track Member',
            'User' => 'Users',
            'Visit' => 'Visit',
            'MenuUserManagement' => 'User management',
            'MenuMasterData' => 'Master Data',
            'MenuPayroll' => 'Payroll',
            'MenuProductManagement' => 'Product Management',
            'MenuCreateOrder' => 'Create Order',
            'MenuCustomer' => 'Customer',
            'MenuAttendance' => 'Attendance',
            'MenuPerformance' => 'Performance',
            'MenuLeaveManagement' => 'Leave Management',
            'MenuTracking' => 'Tracking',
            'MenuCounterVisit' => 'Counter Visit',
            'MenuTaskManagement' => 'Task Management',
            'MenuExpenseManagement' => 'Expense Management',
            'MenuEmployeeId' => 'Employee ID',
            'MenuOfferLetter' => 'Offer Letter',
            'MenuMonthlyReport' => 'Monthly report',
            'MenuExpenseReport' => 'Expense Report',
            'MenuAddRequestAmount' => 'Add Request Amount',
            'MenuSalaryPayroll' => 'Salary Payroll',
        ];
    }

    public static function moduleLabels()
    {
        return collect(self::moduleOptions())->values()->sort()->mapWithKeys(function ($module) {
            return [$module => $module];
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
