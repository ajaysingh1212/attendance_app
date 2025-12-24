<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalaryIncrement extends Model
{
    use HasFactory;

    protected $table = 'salary_increments';

    protected $fillable = [
        'employee_id',

        /* OLD SALARY SNAPSHOT */
        'old_basic',
        'old_hra',
        'old_allowance',
        'old_gross_salary',

        /* NEW SALARY VALUES */
        'new_basic',
        'new_hra',
        'new_allowance',
        'new_gross_salary',

        /* OTHER ALLOWANCES JSON */
        'other_allowances_json',

        /* META */
        'increment_month',
        'remarks',

        'status',       // pending / approved / rejected

        'created_by',
        'updated_by',

        'approved_by',
        'approved_at',

        'rejected_by',
        'rejected_at',

        'user_id',   // linked user
        'older_allowances_json',
        'old_reporting_to',
        'new_reporting_to',
        'new_department',
        'new_position',
        'old_department',
        'old_position',
    ];

    protected $dates = [
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'other_allowances_json' => 'array',
        'older_allowances_json' => 'array',
    ];

    /* ============================================================
       RELATIONS
    ============================================================ */

    // Which employee increment belongs to
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Who approved the increment
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Who created the request
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Who updated the request
    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Who rejected the increment
    public function rejectedByUser()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }


    /* ============================================================
       ACCESSORS (auto-calculated)
    ============================================================ */

    // Increment amount
    public function getDifferenceAttribute()
    {
        return floatval($this->new_gross_salary) - floatval($this->old_gross_salary);
    }

    // Increment percentage
    public function getIncrementPercentAttribute()
    {
        $old = floatval($this->old_gross_salary);
        if ($old <= 0) return 0;

        return round(($this->difference / $old) * 100, 2);
    }
    public function oldReportingUser()
{
    return $this->belongsTo(User::class, 'old_reporting_to');
}

public function newReportingUser()
{
    return $this->belongsTo(User::class, 'new_reporting_to');
}

}
