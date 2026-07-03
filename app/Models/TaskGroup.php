<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskGroup extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'name',
        'description',
        'created_by_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /* ── Relationships ── */

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'task_group_user')
            ->withPivot('member_role')
            ->withTimestamps();
    }

    public function tasks()
    {
        return $this->hasMany(GroupTask::class);
    }

    /* ── Helpers ── */

    public function hasMember(int $userId): bool
    {
        return $this->members()->where('users.id', $userId)->exists();
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
