<?php

namespace App\Models;

use App\Traits\Auditable;
use Carbon\Carbon;
use DateTimeInterface;
use Hash;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens, SoftDeletes, Notifiable, InteractsWithMedia, Auditable, HasFactory;

    protected $table = 'users';

    /* ===================== */
    /* ATTRIBUTES */
    /* ===================== */

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'number',
        'address',
        'degination',
        'emergency_number',
        'terms_accepted',
        'latitude',
        'longitude',
        'current_address',
        'location_verified_at',
        'master_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $dates = [
        'email_verified_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $appends = [
        'image',
        'accept_image',
        'sign_image',
    ];

    /* ===================== */
    /* MEDIA COLLECTIONS */
    /* ===================== */

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
        $this->addMediaCollection('accept_image')->singleFile();
        $this->addMediaCollection('sign_image')->singleFile();
         $this->addMediaCollection('ck-media');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit('crop', 50, 50);

        $this->addMediaConversion('preview')
            ->fit('crop', 120, 120);
    }

    /* ===================== */
    /* ACCESSORS */
    /* ===================== */

    public function getImageAttribute()
    {
        return $this->getMedia('image')->last();
    }

    public function getAcceptImageAttribute()
    {
        return $this->getMedia('accept_image')->last();
    }

    public function getSignImageAttribute()
    {
        return $this->getMedia('sign_image')->last();
    }

    /* ===================== */
    /* MUTATORS */
    /* ===================== */

    public function setPasswordAttribute($input)
    {
        if ($input) {
            $this->attributes['password'] =
                app('hash')->needsRehash($input)
                    ? Hash::make($input)
                    : $input;
        }
    }

    /* ===================== */
    /* RELATIONSHIPS */
    /* ===================== */

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class);
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'user_id');
    }

    /* ===================== */
    /* HELPERS */
    /* ===================== */

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getEmailVerifiedAtAttribute($value)
    {
        return $value
            ? Carbon::createFromFormat('Y-m-d H:i:s', $value)
                ->format(config('panel.date_format') . ' ' . config('panel.time_format'))
            : null;
    }

    public function setEmailVerifiedAtAttribute($value)
    {
        $this->attributes['email_verified_at'] = $value
            ? Carbon::createFromFormat(
                config('panel.date_format') . ' ' . config('panel.time_format'),
                $value
            )->format('Y-m-d H:i:s')
            : null;
    }

    public function getIsAdminAttribute()
    {
        return $this->roles()->where('id', 1)->exists();
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }
}
