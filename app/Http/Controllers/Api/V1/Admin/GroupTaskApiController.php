<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupTask;
use App\Models\GroupTaskNotification;
use App\Models\TaskGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupTaskApiController extends Controller
{
    

    /**
     * Get tasks for user
     *
     * Tasks will be returned when:
     * 1. User is directly assigned to task
     * OR
     * 2. Task belongs to a group in which user is a member
     */
    public function index(Request $request, $userId): JsonResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Check User
        |--------------------------------------------------------------------------
        */

        $user = \App\Models\User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | Get Tasks
        |--------------------------------------------------------------------------
        */

        $tasks = GroupTask::with([
            'group',
            'createdBy',
            'assignees',
            'acceptedBy',
            'completedBy',
            'pointLogs',
        ])
            ->where(function ($query) use ($userId) {

                /*
                |--------------------------------------------------------------------------
                | 1. Directly Assigned Tasks
                |--------------------------------------------------------------------------
                */

                $query->whereHas('assignees', function ($assigneeQuery) use ($userId) {

                    $assigneeQuery->where('users.id', $userId);

                })


                /*
                |--------------------------------------------------------------------------
                | OR
                |--------------------------------------------------------------------------
                */

                ->orWhereHas('group.members', function ($memberQuery) use ($userId) {

                    $memberQuery->where('users.id', $userId);

                });

            })
            ->latest()
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Format Response
        |--------------------------------------------------------------------------
        */

        $data = $tasks->map(function ($task) use ($userId) {

            /*
            |--------------------------------------------------------------------------
            | Check Direct Assignment
            |--------------------------------------------------------------------------
            */

            $isAssigned = $task->assignees
                ->contains(function ($member) use ($userId) {
                    return (int) $member->id === (int) $userId;
                });


            /*
            |--------------------------------------------------------------------------
            | User's Pivot Status
            |--------------------------------------------------------------------------
            */

            $userAssignment = $task->assignees
                ->first(function ($member) use ($userId) {
                    return (int) $member->id === (int) $userId;
                });


            /*
            |--------------------------------------------------------------------------
            | Return Task
            |--------------------------------------------------------------------------
            */

            return [

                'id' => $task->id,

                'task_group_id' => $task->task_group_id,

                'group' => $task->group
                    ? [
                        'id' => $task->group->id,
                        'name' => $task->group->name,
                        'description' => $task->group->description,
                    ]
                    : null,


                'title' => $task->title,

                'description' => $task->description,

                'priority' => $task->priority,

                'priority_color' => $task->priority_color,

                'status' => $task->status,

                'due_at' => $task->due_at
                    ? $task->due_at->format('Y-m-d H:i:s')
                    : null,


                /*
                |--------------------------------------------------------------------------
                | Deadline
                |--------------------------------------------------------------------------
                */

                'deadline_at' => $task->deadline_at
                    ? $task->deadline_at->format('Y-m-d H:i:s')
                    : null,

                'is_delayed' => (bool) $task->is_delayed,


                /*
                |--------------------------------------------------------------------------
                | Created By
                |--------------------------------------------------------------------------
                */

                'created_by' => $task->createdBy
                    ? [
                        'id' => $task->createdBy->id,
                        'name' => $task->createdBy->name,
                        'email' => $task->createdBy->email,
                    ]
                    : null,


                /*
                |--------------------------------------------------------------------------
                | Current User Assignment
                |--------------------------------------------------------------------------
                */

                'is_assigned' => $isAssigned,

                'my_status' => $userAssignment
                    ? ($userAssignment->pivot->status ?? 'pending')
                    : null,


                /*
                |--------------------------------------------------------------------------
                | Accepted By
                |--------------------------------------------------------------------------
                */

                'accepted_by' => $task->acceptedBy
                    ? [
                        'id' => $task->acceptedBy->id,
                        'name' => $task->acceptedBy->name,
                    ]
                    : null,

                'accepted_at' => $task->accepted_at
                    ? $task->accepted_at->format('Y-m-d H:i:s')
                    : null,


                /*
                |--------------------------------------------------------------------------
                | Estimate
                |--------------------------------------------------------------------------
                */

                'estimate_type' => $task->estimate_type,

                'estimated_hours' => $task->estimated_hours,

                'estimated_date' => $task->estimated_date
                    ? $task->estimated_date->format('Y-m-d')
                    : null,

                'accept_narration' => $task->accept_narration,

                'requested_minutes' => $task->requested_minutes,


                /*
                |--------------------------------------------------------------------------
                | Completed
                |--------------------------------------------------------------------------
                */

                'completed_by' => $task->completedBy
                    ? [
                        'id' => $task->completedBy->id,
                        'name' => $task->completedBy->name,
                    ]
                    : null,

                'completed_at' => $task->completed_at
                    ? $task->completed_at->format('Y-m-d H:i:s')
                    : null,

                'completion_narration' => $task->completion_narration,

                'actual_minutes' => $task->actual_minutes,

                'delay_minutes' => $task->delay_minutes,

                'completion_points' => $task->completion_points,


                /*
                |--------------------------------------------------------------------------
                | All Assignees
                |--------------------------------------------------------------------------
                */

                'assignees' => $task->assignees
                    ->map(function ($member) {

                        return [

                            'id' => $member->id,

                            'name' => $member->name,

                            'email' => $member->email,

                            'number' => $member->number ?? null,

                            'status' =>
                                $member->pivot->status
                                ?? 'pending',
                        ];

                    })
                    ->values(),


                /*
                |--------------------------------------------------------------------------
                | Points
                |--------------------------------------------------------------------------
                */

                'points' => $task->pointLogs
                    ->map(function ($point) {

                        return [

                            'id' => $point->id,

                            'user_id' => $point->user_id,

                            'points' => $point->points,

                            'reason' => $point->reason,

                            'was_assigned' =>
                                (bool) $point->was_assigned,

                            'completed_within_deadline' =>
                                (bool) $point->completed_within_deadline,
                        ];

                    })
                    ->values(),

            ];
        });


        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $summary = [

            'total' => $tasks->count(),

            'pending' => $tasks
                ->where('status', 'pending')
                ->count(),

            'accepted' => $tasks
                ->where('status', 'accepted')
                ->count(),

            'completed' => $tasks
                ->where('status', 'completed')
                ->count(),

            'delayed' => $tasks
                ->filter(function ($task) {
                    return $task->is_delayed;
                })
                ->count(),

        ];


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'success' => true,

            'message' => 'Tasks fetched successfully.',

            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],

            'summary' => $summary,

            'data' => $data->values(),

        ]);
    }

    /**
     * Create New Task
     */
    public function store(Request $request): JsonResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Validate Request
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            // Task creator
            'created_by_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            // Group
            'task_group_id' => [
                'required',
                'integer',
                'exists:task_groups,id',
            ],

            // Task details
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'priority' => [
                'required',
                'in:urgent,high,medium,low',
            ],

            'due_at' => [
                'nullable',
                'date',
            ],

            // Assignees
            'assignees' => [
                'required',
                'array',
                'min:1',
            ],

            'assignees.*' => [
                'integer',
                'exists:users,id',
            ],

            // Attachments
            'attachments' => [
                'nullable',
                'array',
            ],

            'attachments.*' => [
                'file',
                'max:10240',
            ],

            // Voice
            'voice_note' => [
                'nullable',
                'file',
                'max:20480',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Get Group
        |--------------------------------------------------------------------------
        */

        $group = TaskGroup::with('members')
            ->find($validated['task_group_id']);


        if (!$group) {

            return response()->json([
                'success' => false,
                'message' => 'Task group not found.',
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | Check Created By User
        |--------------------------------------------------------------------------
        */

        $creatorExists = $group->members()
            ->where('users.id', $validated['created_by_id'])
            ->exists();


        /*
        | Admin can create task even if not group member
        |--------------------------------------------------------------------------
        */

        $creator = \App\Models\User::find($validated['created_by_id']);


        if (!$creator) {

            return response()->json([
                'success' => false,
                'message' => 'Task creator not found.',
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | Non Admin Creator Must Be Group Member
        |--------------------------------------------------------------------------
        */

        if (!$creator->is_admin && !$creatorExists) {

            return response()->json([
                'success' => false,
                'message' => 'Task creator is not a member of this group.',
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | Prepare Assignee IDs
        |--------------------------------------------------------------------------
        */

        $assigneeIds = array_values(
            array_unique(
                array_map(
                    'intval',
                    $validated['assignees']
                )
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Get Group Member IDs
        |--------------------------------------------------------------------------
        */

        $groupMemberIds = $group->members()
            ->pluck('users.id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->all();


        /*
        |--------------------------------------------------------------------------
        | Check Assignees Are Group Members
        |--------------------------------------------------------------------------
        */

        $invalidAssignees = array_diff(
            $assigneeIds,
            $groupMemberIds
        );


        if (!empty($invalidAssignees)) {

            return response()->json([
                'success' => false,
                'message' => 'One or more selected assignees are not members of this group.',
                'invalid_assignees' => array_values($invalidAssignees),
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Create Task
        |--------------------------------------------------------------------------
        */

        DB::beginTransaction();

        try {

            $task = GroupTask::create([

                'task_group_id' => $group->id,

                'created_by_id' => $validated['created_by_id'],

                'title' => $validated['title'],

                'description' => $validated['description'] ?? null,

                'priority' => $validated['priority'],

                'status' => 'pending',

                'due_at' => $validated['due_at'] ?? null,
            ]);


            /*
            |--------------------------------------------------------------------------
            | Assign Users
            |--------------------------------------------------------------------------
            */

            $pivotData = [];

            foreach ($assigneeIds as $assigneeId) {

                $pivotData[$assigneeId] = [
                    'status' => 'pending',
                ];
            }


            $task->assignees()->sync($pivotData);


            /*
            |--------------------------------------------------------------------------
            | Create Notifications
            |--------------------------------------------------------------------------
            */

            foreach ($assigneeIds as $assigneeId) {

                GroupTaskNotification::create([

                    'group_task_id' => $task->id,

                    'user_id' => $assigneeId,

                    'type' => 'assigned',

                    'message' =>
                        'You have been assigned a new task: '
                        . $task->title,
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Save Attachments
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('attachments')) {

                foreach ($request->file('attachments') as $file) {

                    $task
                        ->addMedia($file)
                        ->toMediaCollection('attachments');
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Save Voice Note
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('voice_note')) {

                $task
                    ->addMedia($request->file('voice_note'))
                    ->toMediaCollection('voice_notes');
            }


            /*
            |--------------------------------------------------------------------------
            | Commit
            |--------------------------------------------------------------------------
            */

            DB::commit();


            /*
            |--------------------------------------------------------------------------
            | Load Relations
            |--------------------------------------------------------------------------
            */

            $task->load([
                'group',
                'createdBy',
                'assignees',
            ]);


            /*
            |--------------------------------------------------------------------------
            | Success Response
            |--------------------------------------------------------------------------
            */

            return response()->json([

                'success' => true,

                'message' => 'Task created successfully.',

                'data' => [

                    'id' => $task->id,

                    'task_group_id' => $task->task_group_id,

                    'group' => $task->group
                        ? [
                            'id' => $task->group->id,
                            'name' => $task->group->name,
                        ]
                        : null,

                    'title' => $task->title,

                    'description' => $task->description,

                    'priority' => $task->priority,

                    'status' => $task->status,

                    'due_at' => $task->due_at
                        ? $task->due_at->format('Y-m-d H:i:s')
                        : null,

                    'created_by' => $task->createdBy
                        ? [
                            'id' => $task->createdBy->id,
                            'name' => $task->createdBy->name,
                            'email' => $task->createdBy->email,
                        ]
                        : null,

                    'assignees' => $task->assignees
                        ->map(function ($member) {

                            return [

                                'id' => $member->id,

                                'name' => $member->name,

                                'email' => $member->email,

                                'status' =>
                                    $member->pivot->status
                                    ?? 'pending',
                            ];
                        })
                        ->values(),
                ],

            ], 201);


        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return response()->json([

                'success' => false,

                'message' => 'Unable to create task.',

                'error' => config('app.debug')
                    ? $e->getMessage()
                    : null,

            ], 500);
        }
    }
}