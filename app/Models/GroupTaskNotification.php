<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupTaskNotification extends Model
{
    protected $fillable = [
        'group_task_id',
        'user_id',
        'type',
        'message',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(GroupTask::class, 'group_task_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getIsReadAttribute(): bool
    {
        return $this->read_at !== null;
    }
}
