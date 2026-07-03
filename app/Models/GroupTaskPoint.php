<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupTaskPoint extends Model
{
    protected $fillable = [
        'group_task_id',
        'task_group_id',
        'user_id',
        'points',
        'reason',
        'was_assigned',
        'completed_within_deadline',
    ];

    protected $casts = [
        'points'                    => 'decimal:2',
        'was_assigned'              => 'boolean',
        'completed_within_deadline' => 'boolean',
    ];

    public function task()
    {
        return $this->belongsTo(GroupTask::class, 'group_task_id');
    }

    public function group()
    {
        return $this->belongsTo(TaskGroup::class, 'task_group_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
