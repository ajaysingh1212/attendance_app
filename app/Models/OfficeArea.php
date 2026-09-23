<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'office_branch_id', 'name', 'latitude', 'longitude', 'radius_meters',
        'review_minutes', 'color', 'is_active',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'radius_meters' => 'integer',
        'review_minutes' => 'integer',
        'is_active' => 'boolean',
    ];

    public function officeBranch()
    {
        return $this->belongsTo(OfficeBranch::class);
    }
}
