<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollAdjustment extends Model
{
    protected $fillable = [
        'employee_id', 'type', 'amount', 'reason', 'remarks', 'adjustment_date','status',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}


