<?php

namespace App\Models;

use App\Traits\Auditable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AttendanceDetail extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, Auditable, HasFactory;

    public $table = 'attendance_details';

    protected $appends = [
        'punch_in_image',
        'punch_out_image',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const STATUS_SELECT = [
        'absent'   => 'Absent',
        'present'  => 'Present',
        'half_time' => 'Half Day',
        'leave'    => 'Leave',
        'week_off' => 'Week Off',
        'holiday'  => 'Holiday',
        'late'     => 'Late',
        'paid_leave' => 'Paid Leave',
    ];


    protected $fillable = [
        'user_id',
        'punch_in_time',
        'punch_in_latitude',
        'punch_in_longitude',
        'punch_in_location',
        'punch_out_time',
        'punch_out_latitude',
        'punch_out_longitude',
        'punch_out_location',
        'punch_type',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
        'date',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')->fit('crop', 50, 50);
        $this->addMediaConversion('preview')->fit('crop', 120, 120);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getPunchInImageAttribute()
    {
        $file = $this->getMedia('punch_in_image')->last();
        if ($file) {
            $file->url       = $file->getUrl();
            $file->thumbnail = $file->getUrl('thumb');
            $file->preview   = $file->getUrl('preview');
        }

        return $file;
    }

    public function getPunchOutImageAttribute()
    {
        $file = $this->getMedia('punch_out_image')->last();
        if ($file) {
            $file->url       = $file->getUrl();
            $file->thumbnail = $file->getUrl('thumb');
            $file->preview   = $file->getUrl('preview');
        }

        return $file;
    }
}
