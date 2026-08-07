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

class GroupTask extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, Auditable, HasFactory;

    protected $fillable = [
        'task_group_id', 'created_by_id',
        'title', 'description', 'priority', 'status', 'due_at',
        'accepted_by_id', 'accepted_at', 'accept_role',
        'estimate_type', 'estimated_hours', 'estimated_date',
        'accept_narration', 'requested_minutes',
        'completed_by_id', 'completed_at', 'completion_narration',
        'actual_minutes', 'delay_minutes', 'completion_points',
    ];

    protected $casts = [
        'due_at'         => 'datetime',
        'accepted_at'    => 'datetime',
        'estimated_date' => 'date',
        'completed_at'   => 'datetime',
    ];

    /* ── Media ── */

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments');
        $this->addMediaCollection('voice_notes')->singleFile();
        $this->addMediaCollection('completion_attachments');
        $this->addMediaCollection('completion_voice_notes')->singleFile();
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')->fit('crop', 60, 60)->nonQueued();
    }

    /* ── Relationships ── */

    public function group()
    {
        return $this->belongsTo(TaskGroup::class, 'task_group_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function assignees()
    {
        return $this->belongsToMany(User::class, 'group_task_user')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function acceptedBy()
    {
        return $this->belongsTo(User::class, 'accepted_by_id');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by_id');
    }

    public function pointLogs()
    {
        return $this->hasMany(GroupTaskPoint::class);
    }

    public function notifications()
    {
        return $this->hasMany(GroupTaskNotification::class);
    }

    /* ── Computed Attributes ── */

    public function getDeadlineAtAttribute(): ?Carbon
    {
        if ($this->estimate_type === 'hours' && $this->accepted_at && $this->estimated_hours) {
            return Carbon::parse($this->accepted_at)->addHours($this->estimated_hours);
        }

        if ($this->estimate_type === 'date' && $this->estimated_date) {
            return Carbon::parse($this->estimated_date)->endOfDay();
        }

        return null;
    }

    public function getIsDelayedAttribute(): bool
    {
        return $this->deadline_at
            && ! $this->completed_at
            && now()->greaterThan($this->deadline_at);
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => '#ef4444',
            'high'   => '#f97316',
            'medium' => '#eab308',
            'low'    => '#22c55e',
            default  => '#94a3b8',
        };
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
