<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryStructure extends Model
{
    protected $fillable = [
        'employee_id', 'basic', 'hra', 'allowance', 'bonus',
        'pf', 'esi', 'tds', 'other_deductions','net_salary'
    ];

public function employee()
{
    return $this->belongsTo(Employee::class);
}
}
