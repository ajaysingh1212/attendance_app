<?php

namespace App\Models;

use App\Traits\Auditable;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Expense extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, Auditable, HasFactory;

    public $table = 'expenses';

    protected $appends = [
        'upload_image',
    ];

    protected $dates = [
        'entry_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const STATUS_SELECT = [
        'pending' => 'Pending',
        'accept'  => 'Accept',
        'reject'  => 'Reject',
    ];

    protected $fillable = [
        'employee_id',            // 👈 नया field
        'user_id',
        'expense_category_id',
        'entry_date',
        'amount',
        'status',
        'description',
        'remark',
        'created_at',
        'updated_at',
        'deleted_at',
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

    // 🔹 Relations
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function expense_category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    // 🔹 Mutators
    public function getEntryDateAttribute($value)
{
    return $value ? Carbon::parse($value)->format(config('panel.date_format')) : null;
}


    public function setEntryDateAttribute($value)
{
    if ($value) {
        // Try parsing ISO date first
        try {
            $this->attributes['entry_date'] = Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            $this->attributes['entry_date'] = null;
        }
    } else {
        $this->attributes['entry_date'] = null;
    }
}


    // 🔹 File accessor
    public function getUploadImageAttribute()
    {
        $file = $this->getMedia('upload_image')->last();
        if ($file) {
            $file->url       = $file->getUrl();
            $file->thumbnail = $file->getUrl('thumb');
            $file->preview   = $file->getUrl('preview');
        }

        return $file;
    }
}
