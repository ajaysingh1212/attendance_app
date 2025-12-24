<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payroll extends Model
{
    use HasFactory;

        protected $fillable = [
            'employee_id',
            'month',
            'sundays',
            'year',
            'working_days',
            'present_days',
            'paid_leaves',
            'holidays',
            'absent_days',
            'half_days',
            'leave_days',
            'final_paid_days',
            'basic',
            'hra',
            'allowance',
            'bonus',
            'gross_salary',
            'deductions',
            'manual_adjustment',
            'net_salary',
            'remarks',
            'status',
            'salary_generated_by',
            'generated_at',
            'salary_generated_role',
            'message',
            'manual_adjustment',
            'remaining_salary',
            'valid_sundays',
            'total_days',
            'salary_increment_id',
        ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'salary_generated_by');
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    public function partPayments()
    {
        return $this->hasMany(PayrollPartPayment::class, 'payroll_id');
    }

    public function getTotalPaidAttribute()
    {
        return $this->partPayments()->sum('part_amount');
    }

    public function getRemainingAmountAttribute()
    {
        return $this->net_salary - $this->total_paid;
    }

}
