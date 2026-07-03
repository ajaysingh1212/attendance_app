<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupTaskNotification;
use App\Models\TaskGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskGroupController extends Controller
{
    public function index()
    {
        $this->adminOnly();

        $groups = TaskGroup::with(['createdBy', 'members'])
            ->withCount(['members', 'tasks'])
            ->latest()
            ->get();

        return view('admin.taskGroups.index', compact('groups'));
    }

    public function create()
    {
        $this->adminOnly();

        $users = User::activeEmployees()->orderBy('name')->get(['id', 'name', 'email']);
        $taskGroup = null;

        return view('admin.taskGroups.create', compact('users', 'taskGroup'));
    }

    public function store(Request $request)
    {
        $this->adminOnly();

        $data = $this->validatedData($request);

        $group = TaskGroup::create([
            'name'          => $data['name'],
            'description'   => $data['description'] ?? null,
            'created_by_id' => Auth::id(),
            'is_active'     => $request->boolean('is_active', true),
        ]);

        $group->members()->sync($this->buildMemberPayload(
            $data['members'],
            $request->input('member_roles', [])
        ));

        $this->notifyMembers($group, $data['members'], 'group_created');

        return redirect()
            ->route('admin.task-groups.show', $group)
            ->with('success', 'Group created and members notified.');
    }

    public function show(TaskGroup $taskGroup)
    {
        $this->adminOnly();

        $taskGroup->load([
            'createdBy',
            'members',
            'tasks.createdBy',
            'tasks.assignees',
            'tasks.acceptedBy',
            'tasks.completedBy',
        ]);

        return view('admin.taskGroups.show', compact('taskGroup'));
    }

    public function edit(TaskGroup $taskGroup)
    {
        $this->adminOnly();

        $taskGroup->load('members');
        $users = User::activeEmployees()->orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.taskGroups.edit', compact('taskGroup', 'users'));
    }

    public function update(Request $request, TaskGroup $taskGroup)
    {
        $this->adminOnly();

        $data = $this->validatedData($request);
        $oldMemberIds = $taskGroup->members()->pluck('users.id')->all();

        $taskGroup->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active'   => $request->boolean('is_active'),
        ]);

        $taskGroup->members()->sync($this->buildMemberPayload(
            $data['members'],
            $request->input('member_roles', [])
        ));

        $newMemberIds = array_values(array_diff($data['members'], $oldMemberIds));
        $this->notifyMembers($taskGroup, $newMemberIds, 'group_created');

        return redirect()
            ->route('admin.task-groups.show', $taskGroup)
            ->with('success', 'Group updated successfully.');
    }

    public function destroy(TaskGroup $taskGroup)
    {
        $this->adminOnly();

        $taskGroup->delete();

        return redirect()
            ->route('admin.task-groups.index')
            ->with('success', 'Group deleted.');
    }

    public function liveGroups()
    {
        $this->adminOnly();

        $groups = TaskGroup::withCount(['members', 'tasks'])
            ->with('members:id,name')
            ->where('is_active', true)
            ->latest()
            ->get()
            ->map(fn ($group) => [
                'id'            => $group->id,
                'name'          => $group->name,
                'members_count' => $group->members_count,
                'tasks_count'   => $group->tasks_count,
                'members'       => $group->members->pluck('name')->values(),
            ]);

        return response()->json($groups);
    }

    public function members(TaskGroup $taskGroup)
    {
        $this->authorizeGroup($taskGroup);

        return response()->json(
            $taskGroup->members()->orderBy('name')->get(['users.id', 'users.name'])
        );
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string|max:5000',
            'is_active'    => 'nullable|boolean',
            'members'      => 'required|array|min:1',
            'members.*'    => 'exists:users,id',
            'member_roles' => 'nullable|array',
        ]);

        $activeUserIds = User::activeEmployees()
            ->whereIn('id', $data['members'])
            ->pluck('id')
            ->all();

        abort_if(
            count(array_diff($data['members'], $activeUserIds)) > 0,
            422,
            'Only active employees can be added to a task group.'
        );

        return $data;
    }

    private function buildMemberPayload(array $members, array $roles): array
    {
        $payload = [];

        foreach ($members as $id) {
            $payload[$id] = [
                'member_role' => $roles[$id] ?? 'Member',
            ];
        }

        return $payload;
    }

    private function notifyMembers(TaskGroup $group, array $userIds, string $type): void
    {
        foreach (array_unique($userIds) as $userId) {
            GroupTaskNotification::create([
                'group_task_id' => null,
                'user_id'       => $userId,
                'type'          => $type,
                'message'       => 'You have been added to the group ' . $group->name . '.',
            ]);
        }
    }

    private function adminOnly(): void
    {
        abort_unless(Auth::user() && Auth::user()->is_admin, 403, 'Only admins can manage task groups.');
    }

    private function authorizeGroup(TaskGroup $group): void
    {
        if (Auth::user()->is_admin) {
            return;
        }

        abort_unless($group->members()->where('users.id', Auth::id())->exists(), 403);
    }
}
