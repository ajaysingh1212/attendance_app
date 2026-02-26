<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExperienceLetterIncrement extends Model
{
    protected $fillable = [
        'experience_letter_id',
        'increment_date',
        'old_salary',
        'new_salary',
        'old_position',
        'new_position'
    ];

    public function letter()
    {
        return $this->belongsTo(ExperienceLetter::class,'experience_letter_id');
    }
}
