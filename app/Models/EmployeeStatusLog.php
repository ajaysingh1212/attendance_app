<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeStatusLog extends Model
{
    use HasFactory;

    protected $table = 'employee_status_logs';

    protected $fillable = [
        'employee_id',
        'old_status',
        'new_status',
        'changed_by',
        'approved_by',
        'reactivated_by',
        'remarks',
        'changed_at',
        'approved_at',
        'reactivated_at',
    ];

    protected $casts = [
        'changed_at'     => 'datetime',
        'approved_at'    => 'datetime',
        'reactivated_at' => 'datetime',
    ];

    /* ── Relationships ──────────────────────────────────── */

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function reactivatedBy()
    {
        return $this->belongsTo(User::class, 'reactivated_by');
    }

    /* ── Helpers ────────────────────────────────────────── */

    /** Human-readable badge color for a status */
    public static function colorFor(string $status): string
    {
        return match (strtolower($status)) {
            'active'     => 'success',
            'resigned'   => 'warning',
            'terminated' => 'danger',
            'suspended'  => 'secondary',
            default      => 'info',
        };
    }

    /** Statuses that make an employee "inactive" (hide from dashboard, skip payroll) */
    public static function inactiveStatuses(): array
    {
        return ['Resigned', 'Terminated', 'Suspended'];
    }
}
