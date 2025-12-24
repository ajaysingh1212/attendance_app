<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'employee_code', 'full_name', 'email', 'phone',
        'bank_name', 'account_number', 'ifsc_code', 'pan_number', 'aadhaar_number', 'payment_mode',
        'work_start_time', 'work_end_time', 'working_hours', 'weekly_off_day',
        'attendance_source', 'attendance_radius_meter',
        'basic_salary', 'hra', 'other_allowances', 'deductions', 'net_salary',
        'date_of_joining', 'position', 'department', 'reporting_to', 'status',
        'profile_photo', 'other_allowances_json','branch_id','delay_time','blood_group',

        // Document Fields
        'cv', 'offer_letter', 'aadhaar_front', 'aadhaar_back',
        'pan_card', 'marksheet', 'certificate', 'passbook',
        'photo', 'other_document','signature','exprience_letter','document_verified'
    ];

     protected $casts = [
        'other_allowances' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'reporting_to');
    }
    public function branch()
{
    return $this->belongsTo(Branch::class);
}
public function salaryStructure()
{
    return $this->hasOne(SalaryStructure::class);
}
public function payrollAdjustments()
{
    return $this->hasMany(PayrollAdjustment::class);
}
    public function attendances() {
        return $this->hasMany(AttendanceDetail::class, 'employee_id', 'user_id');
    }
    public function reportingUser()
    {
        return $this->belongsTo(User::class, 'reporting_to');
    }

}
