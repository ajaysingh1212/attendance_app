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

class Branch extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, Auditable, HasFactory;

    public $table = 'branches';

    protected $appends = [
        'branch_image',
        'signature_image',
        'stamp_image',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'title',
        'legal_name',
        'incharge_name',
        'email',
        'phone',
        'gst',
        'pan',
        'registration_number',
        'address',
        'city',
        'state',
        'pincode',
        'country',
        'latitude',
        'longitude',
    ];
        public function getSignatureImageAttribute()
    {
        return $this->getSingleMedia('signature');
    }

    public function getStampImageAttribute()
    {
        return $this->getSingleMedia('stamp');
    }
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')->fit('crop', 50, 50);
        $this->addMediaConversion('preview')->fit('crop', 120, 120);
    }

    public function getBranchImageAttribute()
    {
        $file = $this->getMedia('branch_image')->last();
        if ($file) {
            $file->url       = $file->getUrl();
            $file->thumbnail = $file->getUrl('thumb');
            $file->preview   = $file->getUrl('preview');
        }

        return $file;
    }
    public function employees()
{
    return $this->hasMany(Employee::class);
}
    private function getSingleMedia($collection)
    {
        $file = $this->getMedia($collection)->last();
        if ($file) {
            $file->url = $file->getUrl();
            $file->thumb = $file->getUrl('thumb');
        }
        return $file;
    }

}
