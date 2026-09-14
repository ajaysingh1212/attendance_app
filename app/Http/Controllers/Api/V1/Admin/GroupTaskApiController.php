<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupTask;
use App\Models\GroupTaskNotification;
use App\Models\TaskGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GroupTaskApiController extends Controller
{
    /**
     * Add new task
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $validated = $request->validate([
            'task_group_id' => [
                'required',
                'integer',
                'exists:task_groups,id',
            ],

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

            'assignees' => [
                'required',
                'array',
                'min:1',
            ],

            'assignees.*' => [
                'integer',
                'exists:users,id',
            ],

            'attachments' => [
                'nullable',
                'array',
            ],

            'attachments.*' => [
                'file',
                'max:10240',
            ],

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
        | Check Group Access
        |--------------------------------------------------------------------------
        */

        if (!$user->is_admin) {

            $isMember = $group->members()
                ->where('users.id', $user->id)
                ->exists();

            if (!$isMember) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a member of this task group.',
                ], 403);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Check Assignees Are Group Members
        |--------------------------------------------------------------------------
        */

        $assigneeIds = array_values(
            array_unique(
                array_map('intval', $validated['assignees'])
            )
        );

        $groupMemberIds = $group->members()
            ->pluck('users.id')
            ->map(fn ($id) => (int) $id)
            ->all();

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
                'created_by_id'  => $user->id,

                'title'       => $validated['title'],
                'description' => $validated['description'] ?? null,
                'priority'    => $validated['priority'],

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
            | Notifications
            |--------------------------------------------------------------------------
            */

            foreach ($assigneeIds as $assigneeId) {

                GroupTaskNotification::create([
                    'group_task_id' => $task->id,
                    'user_id'       => $assigneeId,
                    'type'          => 'assigned',
                    'message'       => 'You have been assigned a new task: ' . $task->title,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Attachments
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
            | Voice Note
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('voice_note')) {

                $task
                    ->addMedia($request->file('voice_note'))
                    ->toMediaCollection('voice_notes');
            }

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | Reload Task
            |--------------------------------------------------------------------------
            */

            $task->load([
                'group',
                'createdBy',
                'assignees',
            ]);

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
                        ]
                        : null,

                    'assignees' => $task->assignees
                        ->map(function ($member) {
                            return [
                                'id' => $member->id,
                                'name' => $member->name,
                                'email' => $member->email,
                                'status' => $member->pivot->status ?? 'pending',
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