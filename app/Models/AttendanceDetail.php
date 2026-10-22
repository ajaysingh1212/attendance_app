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
        'review_started_at',
        'review_deadline_at',
        'entered_office_area_at',
    ];

    /**
     * Do NOT cast 'date' to Carbon here — it breaks getRawOriginal() and keyBy().
     * The controller uses getRawOriginal('date') to always get the raw Y-m-d string.
     */
    protected $casts = [
        'review_started_at' => 'datetime',
        'review_deadline_at' => 'datetime',
        'entered_office_area_at' => 'datetime',
        'arrival_delay_seconds' => 'integer',
        'tracking_enabled' => 'boolean',
        'holding_seconds' => 'integer',
        'last_inside_area' => 'boolean',
        'holding_started_at' => 'datetime',
        'holding_deadline_at' => 'datetime',
        'tracking_completed_at' => 'datetime',
        'last_tracking_at' => 'datetime',
    ];

    public const STATUS_SELECT = [
        'absent'     => 'Absent',
        'present'    => 'Present',
        'half_time'  => 'Half Day',
        'leave'      => 'Leave',
        'week_off'   => 'Week Off',
        'holiday'    => 'Holiday',
        'late'       => 'Late',
        'paid_leave' => 'Paid Leave',
        'in_review'  => 'In Review',
        'holding'    => 'Holding',
        'suspicious' => 'Suspicious',
        'approved'   => 'Approved',
        'verified'   => 'Verified',
    ];

    protected $fillable = [
        'user_id',
        'employee_id',
        'date',
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
        'type',
        'changed_by',
        'office_area_id',
        'verification_status',
        'verified_attendance_status',
        'review_started_at',
        'review_deadline_at',
        'entered_office_area_at',
        'arrival_delay_seconds',
        'latest_latitude',
        'latest_longitude',
        'punch_distance_meters',
        'latest_distance_meters',
        'review_note',
        'ip_address',
        'device_name',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $attendance) {
            if ($attendance->exists && $attendance->getRawOriginal('verification_status') === 'suspicious'
                && ($attendance->isDirty('status') || $attendance->isDirty('verification_status'))
                && !auth()->user()?->is_admin) {
                throw \Illuminate\Validation\ValidationException::withMessages(['status' => 'Only an admin can change suspicious attendance.']);
            }
            if ($attendance->punch_in_time && (!$attendance->exists || ($attendance->isDirty('punch_in_time') && !$attendance->getRawOriginal('punch_in_time')))) {
                $employee = Employee::find($attendance->employee_id);
                $attendance->tracking_enabled = (bool) $employee?->tracking_enabled;
                $attendance->holding_seconds = (int) round((float) ($employee?->tracking_hours ?? 1) * 3600);
            }
            if ($attendance->isDirty('punch_out_time') && $attendance->punch_out_time && $attendance->verification_status === 'holding') {
                $attendance->status = 'suspicious';
                $attendance->verification_status = 'suspicious';
                $attendance->review_note = 'Punch-out occurred before holding verification completed.';
            }
            if ($attendance->tracking_enabled && $attendance->entered_office_area_at
                && !$attendance->holding_started_at && !$attendance->tracking_completed_at
                && in_array($attendance->verification_status, ['approved', 'verified'], true)) {
                $attendance->holding_started_at = now();
                $attendance->holding_deadline_at = now()->addSeconds($attendance->holding_seconds);
                $attendance->last_tracking_at = now();
                $attendance->last_inside_area = true;
                $attendance->verified_attendance_status = $attendance->status === 'in_review'
                    ? 'present' : $attendance->status;
                $attendance->status = 'in_review';
                $attendance->verification_status = 'holding';
            }
        });
        static::saved(function (self $attendance) {
            if ($attendance->wasRecentlyCreated || $attendance->wasChanged(['status', 'verification_status', 'entered_office_area_at', 'punch_in_time', 'punch_out_time'])) {
                $attendance->trackingEvents()->create([
                    'type' => match (true) {
                        $attendance->wasRecentlyCreated => $attendance->punch_in_time ? 'punch_in' : 'attendance_recorded',
                        $attendance->wasChanged('punch_out_time') => 'punch_out',
                        $attendance->wasChanged('punch_in_time') => 'punch_in',
                        $attendance->wasChanged('entered_office_area_at') => 'office_arrival',
                        $attendance->wasChanged('tracking_completed_at') && $attendance->wasChanged('changed_by') => 'admin_review',
                        $attendance->wasChanged('tracking_completed_at') => 'holding_completed',
                        default => 'status_changed',
                    },
                    'status' => $attendance->verification_status ?: $attendance->status,
                    'note' => $attendance->review_note ?: 'Attendance recorded.',
                    'occurred_at' => now(), 'received_at' => now(),
                ]);
                if ($attendance->wasRecentlyCreated && $attendance->entered_office_area_at) {
                    $attendance->trackingEvents()->create([
                        'type' => 'office_arrival', 'status' => $attendance->verification_status ?: $attendance->status,
                        'note' => 'Employee was inside the office at punch-in.',
                        'occurred_at' => $attendance->entered_office_area_at, 'received_at' => now(),
                    ]);
                }
            }
        });
    }

    public function trackingEvents()
    {
        return $this->hasMany(AttendanceTrackingEvent::class)->orderBy('occurred_at')->orderBy('id');
    }

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')->fit(\Spatie\Image\Enums\Fit::Crop, 50, 50);
        $this->addMediaConversion('preview')->fit(\Spatie\Image\Enums\Fit::Crop, 120, 120);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function officeArea()
    {
        return $this->belongsTo(OfficeArea::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Media Accessors
    |--------------------------------------------------------------------------
    */
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

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForDateRange($query, $start, $end)
    {
        // Uses the 'date' column — works for ALL historical data
        return $query->whereBetween('date', [$start, $end]);
    }
}
