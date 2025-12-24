<?php

namespace App\Models;

use App\Traits\MultiTenantModelTrait;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerformanceReport extends Model
{
    use SoftDeletes, MultiTenantModelTrait, HasFactory;

    public $table = 'performance_reports';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'employee_id',
        'date', // isme month-year hi store hoga (YYYY-MM)
        'sales',
        'salaries',
        'metrial_cost',
        'tour_travel',
        'other_cost',
        'cost_of_sell',
        'net_profit',
        'profit_percentage',
        'profit_points',
        'half_profit_percentage',
        'half_profit_points',
        'unpaid_amount',
        'unpaid_percentage',
        'unpaid_points',
        'half_unpaid_percentage',
        'half_unpaid_points',
        'final_points',
        'performance_status',
        'status',
        'created_by_id',
        'attachment',  
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    // 👇 Getter: DB se "2025-09-01" aaye to sirf "2025-09" return karega
    public function getDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m') : null;
    }

    // 👇 Setter: Form se "2025-09" aaye to DB me "2025-09-01" store karega
    public function setDateAttribute($value)
    {
        $this->attributes['date'] = $value ? Carbon::createFromFormat('Y-m', $value)->startOfMonth()->format('Y-m-d') : null;
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function employee()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'employee_id');
    }
}
