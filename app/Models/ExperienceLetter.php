<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExperienceLetter extends Model
{
    use Auditable;

    protected $fillable = [
        'employee_id',
        'date_of_joining',
        'date_of_resignation',
        'last_working_date',
        'designation',
        'department',
        'last_drawn_salary',
        'notice_period_days',
        'notice_served',
        'notice_served_days',
        'had_increment',
        'last_increment_date',
        'increment_amount',
        'additional_remark',
        'created_by'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function increments()
{
    return $this->hasMany(ExperienceLetterIncrement::class);
}
}
