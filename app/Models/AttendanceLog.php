<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    protected $fillable = [
        'user_id',
        'employee_id',
        'date',
        'expected_in',
        'actual_in',
        'late_by_minutes',
        'expected_out',
        'actual_out',
        'left_early_by_minutes',
        'overtime_by_minutes',
        'total_work_minutes'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function employee() {
        return $this->belongsTo(Employee::class);
    }
}
