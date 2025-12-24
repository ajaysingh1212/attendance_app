<?php

namespace App\Models;

use App\Traits\Auditable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrackMember extends Model
{
    use SoftDeletes,  Auditable, HasFactory;

    public $table = 'track_members';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'latitude',
        'longitude',
        'location',
        'time',
        'created_at',
        'updated_at',
        'deleted_at',
        'user_id',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }


    public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}



}
