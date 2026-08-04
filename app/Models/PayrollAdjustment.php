<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class PayrollAdjustment extends Model
{
    use Auditable;

    protected $fillable = [
        'employee_id', 'type', 'amount', 'reason', 'remarks', 'adjustment_date','status',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}

