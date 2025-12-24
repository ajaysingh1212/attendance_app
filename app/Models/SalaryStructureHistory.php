<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalaryStructureHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_adjustment_id',
        'employee_id',
        'structure_snapshot',
    ];

    protected $casts = [
        'structure_snapshot' => 'array',
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function salaryStructure()
    {
        return $this->belongsTo(Payroll::class);
    }
}
