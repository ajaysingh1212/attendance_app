<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'holiday_type',
        'is_optional',
        'is_national',
        'created_by',
    ];
}
