<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaskGroup;
use Illuminate\Http\JsonResponse;

class TaskGroupApiController extends Controller
{
    /**
     * Get all active task groups with members
     */
    public function index(): JsonResponse
    {
        $groups = TaskGroup::with([
            'members' => function ($query) {
                $query
                    ->select(
                        'users.id',
                        'users.name',
                        'users.email',
                        'users.number',
                        'users.status'
                    )
                    ->orderBy('users.name');
            },
            'createdBy:id,name,email'
        ])
            ->where('is_active', true)
            ->latest()
            ->get();

        $data = $groups->map(function ($group) {

            return [
                'id' => $group->id,

                'name' => $group->name,

                'description' => $group->description,

                'is_active' => (bool) $group->is_active,

                'created_by' => $group->createdBy
                    ? [
                        'id' => $group->createdBy->id,
                        'name' => $group->createdBy->name,
                        'email' => $group->createdBy->email,
                    ]
                    : null,

                'members_count' => $group->members->count(),

                'members' => $group->members->map(function ($member) {

                    return [
                        'id' => $member->id,
                        'name' => $member->name,
                        'email' => $member->email,
                        'number' => $member->number,
                        'status' => $member->status,

                        // task_group_user pivot se
                        // member_role milega
                        'member_role' => $member->pivot->member_role ?? 'Member',
                    ];

                })->values(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'message' => 'Task groups fetched successfully.',
            'data' => $data,
        ]);
    }
}