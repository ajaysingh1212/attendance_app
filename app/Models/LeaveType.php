<?php

// app/Models/LeaveType.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = ['name', 'description'];

    /** Only the explicitly named "Paid Leave" type is salary-paid. */
    public static function isPaidName(?string $name): bool
    {
        $normalized = preg_replace('/[\s_-]+/', ' ', strtolower(trim((string) $name)));

        return $normalized === 'paid leave';
    }
}
