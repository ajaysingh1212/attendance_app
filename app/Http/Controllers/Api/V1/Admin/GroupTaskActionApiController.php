<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupTask;
use App\Models\GroupTaskNotification;
use App\Models\GroupTaskPoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupTaskActionApiController extends Controller
{
    /**
     * ============================================================
     * ACCEPT TASK
     * ============================================================
     */
    public function accept(Request $request, $taskId): JsonResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'estimate_type' => [
                'required',
                'in:hours,date',
            ],

            'estimated_hours' => [
                'nullable',
                'numeric',
                'min:0.1',
            ],

            'estimated_date' => [
                'nullable',
                'date',
            ],

            'accept_narration' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Get Task
        |--------------------------------------------------------------------------
        */

        $task = GroupTask::with([
            'group',
            'assignees',
            'createdBy',
        ])->find($taskId);


        if (!$task) {

            return response()->json([
                'success' => false,
                'message' => 'Task not found.',
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | Check Task Status
        |--------------------------------------------------------------------------
        */

        if ($task->status !== 'pending') {

            return response()->json([
                'success' => false,
                'message' => 'This task cannot be accepted because its current status is ' . $task->status . '.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Check User Is Assignee
        |--------------------------------------------------------------------------
        */

        $assignee = $task->assignees()
            ->where('users.id', $validated['user_id'])
            ->first();


        if (!$assignee) {

            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this task.',
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Estimate
        |--------------------------------------------------------------------------
        */

        if (
            $validated['estimate_type'] === 'hours'
            && empty($validated['estimated_hours'])
        ) {

            return response()->json([
                'success' => false,
                'message' => 'Estimated hours are required when estimate type is hours.',
            ], 422);
        }


        if (
            $validated['estimate_type'] === 'date'
            && empty($validated['estimated_date'])
        ) {

            return response()->json([
                'success' => false,
                'message' => 'Estimated date is required when estimate type is date.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Calculate Requested Minutes
        |--------------------------------------------------------------------------
        */

        $requestedMinutes = null;

        if ($validated['estimate_type'] === 'hours') {

            $requestedMinutes = (int) round(
                ((float) $validated['estimated_hours']) * 60
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Accept Task
        |--------------------------------------------------------------------------
        */

        DB::beginTransaction();

        try {

            $task->update([

                'status' => 'accepted',

                'accepted_by_id' => $validated['user_id'],

                'accepted_at' => now(),

                'accept_role' => $assignee->pivot->member_role ?? 'Member',

                'estimate_type' => $validated['estimate_type'],

                'estimated_hours' =>
                    $validated['estimate_type'] === 'hours'
                        ? $validated['estimated_hours']
                        : null,

                'estimated_date' =>
                    $validated['estimate_type'] === 'date'
                        ? $validated['estimated_date']
                        : null,

                'accept_narration' =>
                    $validated['accept_narration'] ?? null,

                'requested_minutes' => $requestedMinutes,
            ]);


            /*
            |--------------------------------------------------------------------------
            | Update Pivot Status
            |--------------------------------------------------------------------------
            */

            $task->assignees()->updateExistingPivot(
                $validated['user_id'],
                [
                    'status' => 'accepted',
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | Notify Task Creator
            |--------------------------------------------------------------------------
            */

            if ($task->created_by_id) {

                GroupTaskNotification::create([

                    'group_task_id' => $task->id,

                    'user_id' => $task->created_by_id,

                    'type' => 'accepted',

                    'message' =>
                        $assignee->name
                        . ' accepted your task: '
                        . $task->title,
                ]);
            }


            DB::commit();


            /*
            |--------------------------------------------------------------------------
            | Reload
            |--------------------------------------------------------------------------
            */

            $task->refresh();

            $task->load([
                'group',
                'createdBy',
                'assignees',
                'acceptedBy',
            ]);


            return response()->json([

                'success' => true,

                'message' => 'Task accepted successfully.',

                'data' => $this->taskResponse($task),

            ], 200);


        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return response()->json([

                'success' => false,

                'message' => 'Unable to accept task.',

                'error' => config('app.debug')
                    ? $e->getMessage()
                    : null,

            ], 500);
        }
    }


    /**
     * ============================================================
     * COMPLETE TASK
     * ============================================================
     */
    public function complete(Request $request, $taskId): JsonResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'completion_narration' => [
                'nullable',
                'string',
                'max:5000',
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
        | Get Task
        |--------------------------------------------------------------------------
        */

        $task = GroupTask::with([
            'group',
            'assignees',
            'createdBy',
        ])->find($taskId);


        if (!$task) {

            return response()->json([
                'success' => false,
                'message' => 'Task not found.',
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | Task Must Be Accepted
        |--------------------------------------------------------------------------
        */

        if ($task->status !== 'accepted') {

            return response()->json([
                'success' => false,
                'message' => 'Only accepted tasks can be completed.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Check User Is Assignee
        |--------------------------------------------------------------------------
        */

        $assignee = $task->assignees()
            ->where('users.id', $validated['user_id'])
            ->first();


        if (!$assignee) {

            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this task.',
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | Calculate Actual Minutes
        |--------------------------------------------------------------------------
        */

        $actualMinutes = null;

        if ($task->accepted_at) {

            $actualMinutes = $task->accepted_at
                ->diffInMinutes(now());
        }


        /*
        |--------------------------------------------------------------------------
        | Calculate Deadline
        |--------------------------------------------------------------------------
        */

        $deadlineAt = null;

        if (
            $task->estimate_type === 'hours'
            && $task->accepted_at
            && $task->estimated_hours
        ) {

            $deadlineAt = $task->accepted_at
                ->copy()
                ->addHours($task->estimated_hours);
        }

        elseif (
            $task->estimate_type === 'date'
            && $task->estimated_date
        ) {

            $deadlineAt = $task->estimated_date
                ->copy()
                ->endOfDay();
        }


        /*
        |--------------------------------------------------------------------------
        | Calculate Delay
        |--------------------------------------------------------------------------
        */

        $delayMinutes = 0;

        $completedWithinDeadline = true;

        if ($deadlineAt && now()->greaterThan($deadlineAt)) {

            $delayMinutes = $deadlineAt->diffInMinutes(now());

            $completedWithinDeadline = false;
        }


        /*
        |--------------------------------------------------------------------------
        | Start Transaction
        |--------------------------------------------------------------------------
        */

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Update Task
            |--------------------------------------------------------------------------
            */

            $task->update([

                'status' => 'completed',

                'completed_by_id' => $validated['user_id'],

                'completed_at' => now(),

                'completion_narration' =>
                    $validated['completion_narration'] ?? null,

                'actual_minutes' => $actualMinutes,

                'delay_minutes' => $delayMinutes,
            ]);


            /*
            |--------------------------------------------------------------------------
            | Update Pivot Status
            |--------------------------------------------------------------------------
            */

            $task->assignees()->updateExistingPivot(
                $validated['user_id'],
                [
                    'status' => 'completed',
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | Calculate Points
            |--------------------------------------------------------------------------
            */

            $wasAssigned = true;

            if ($wasAssigned && $completedWithinDeadline) {

                $points = 1.00;

                $reason = 'Assigned task completed within deadline';

            } elseif ($wasAssigned && !$completedWithinDeadline) {

                $points = 0.50;

                $reason = 'Assigned task completed after deadline';

            } else {

                $points = 1.50;

                $reason = 'Unassigned task completed within deadline';
            }


            /*
            |--------------------------------------------------------------------------
            | Save Points
            |--------------------------------------------------------------------------
            */

            GroupTaskPoint::updateOrCreate(

                [
                    'group_task_id' => $task->id,

                    'user_id' => $validated['user_id'],
                ],

                [
                    'task_group_id' => $task->task_group_id,

                    'points' => $points,

                    'reason' => $reason,

                    'was_assigned' => true,

                    'completed_within_deadline' =>
                        $completedWithinDeadline,
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | Completion Attachments
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('attachments')) {

                foreach ($request->file('attachments') as $file) {

                    $task
                        ->addMedia($file)
                        ->toMediaCollection('completion_attachments');
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Completion Voice
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('voice_note')) {

                $task
                    ->addMedia($request->file('voice_note'))
                    ->toMediaCollection('completion_voice_notes');
            }


            /*
            |--------------------------------------------------------------------------
            | Notify Creator
            |--------------------------------------------------------------------------
            */

            if ($task->created_by_id) {

                GroupTaskNotification::create([

                    'group_task_id' => $task->id,

                    'user_id' => $task->created_by_id,

                    'type' => 'completed',

                    'message' =>
                        $assignee->name
                        . ' completed your task: '
                        . $task->title,
                ]);
            }


            DB::commit();


            /*
            |--------------------------------------------------------------------------
            | Reload
            |--------------------------------------------------------------------------
            */

            $task->refresh();

            $task->load([
                'group',
                'createdBy',
                'assignees',
                'acceptedBy',
                'completedBy',
                'pointLogs',
            ]);


            return response()->json([

                'success' => true,

                'message' => 'Task completed successfully.',

                'data' => $this->taskResponse($task),

            ], 200);


        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return response()->json([

                'success' => false,

                'message' => 'Unable to complete task.',

                'error' => config('app.debug')
                    ? $e->getMessage()
                    : null,

            ], 500);
        }
    }


    /**
     * ============================================================
     * Common Task Response
     * ============================================================
     */
    private function taskResponse(GroupTask $task): array
    {
        return [

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

            'accepted_by' => $task->acceptedBy
                ? [
                    'id' => $task->acceptedBy->id,
                    'name' => $task->acceptedBy->name,
                ]
                : null,

            'accepted_at' => $task->accepted_at
                ? $task->accepted_at->format('Y-m-d H:i:s')
                : null,

            'estimate_type' => $task->estimate_type,

            'estimated_hours' => $task->estimated_hours,

            'estimated_date' => $task->estimated_date
                ? $task->estimated_date->format('Y-m-d')
                : null,

            'accept_narration' => $task->accept_narration,

            'requested_minutes' => $task->requested_minutes,

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

            'points' => $task->pointLogs
                ? $task->pointLogs->map(function ($point) {

                    return [

                        'user_id' => $point->user_id,

                        'points' => $point->points,

                        'reason' => $point->reason,

                        'was_assigned' => $point->was_assigned,

                        'completed_within_deadline' =>
                            $point->completed_within_deadline,
                    ];
                })->values()
                : [],
        ];
    }
}