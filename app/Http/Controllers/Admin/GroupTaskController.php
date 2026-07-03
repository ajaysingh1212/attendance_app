<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupTask;
use App\Models\GroupTaskNotification;
use App\Models\GroupTaskPoint;
use App\Models\TaskGroup;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupTaskController extends Controller
{
    /* ════════════════════════════════════════════
     |  INDEX
     ════════════════════════════════════════════ */
    public function index(Request $request)
    {
        $tasks = $this->visibleTasks()
            ->with(['group', 'createdBy', 'assignees', 'acceptedBy', 'completedBy'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->group_id, fn ($q) => $q->where('task_group_id', $request->group_id))
            ->latest()
            ->get();

        $summary  = $this->summary();
        $myGroups = $this->availableGroups()->get(['id', 'name']);

        return view('admin.groupTasks.index', compact('tasks', 'summary', 'myGroups'));
    }

    /* ════════════════════════════════════════════
     |  CREATE / STORE
     ════════════════════════════════════════════ */
    public function create()
    {
        $user = Auth::user();

        $groups = $this->availableGroups()
            ->with(['members' => fn ($q) => $q->activeEmployees()->orderBy('name')])
            ->orderBy('name')
            ->get();
       
        // Non-admin and no group
        if (!$user->is_admin && $groups->count() == 0) {
            return redirect()
                ->back()
                ->with('error', 'Aap kisi group me nahi ho.');
        }

        return view('admin.groupTasks.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'task_group_id'  => 'required|exists:task_groups,id',
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'priority'       => 'required|in:low,medium,high,urgent',
            'due_at'         => 'nullable|date',
            'assignees'      => 'required|array|min:1',
            'assignees.*'    => 'exists:users,id',
            'attachments.*'  => 'nullable|file|max:20480',
            'voice_note'     => 'nullable|file|mimes:audio/webm,webm,mp3,wav,ogg,m4a|max:20480',
        ]);

        $group = TaskGroup::with(['members' => fn ($q) => $q->activeEmployees()])->findOrFail($data['task_group_id']);
        $this->authorizeGroup($group);

        $memberIds = $group->members->pluck('id')->all();
        abort_if(
            count(array_diff($data['assignees'], $memberIds)) > 0,
            422,
            'All assignees must be members of the selected group.'
        );

        $task = GroupTask::create([
            'task_group_id' => $group->id,
            'created_by_id' => Auth::id(),
            'title'         => $data['title'],
            'description'   => $data['description'] ?? null,
            'priority'      => $data['priority'],
            'due_at'        => $data['due_at'] ?? null,
            'status'        => 'pending',
        ]);

        $task->assignees()->sync($data['assignees']);

        // Notify every assignee
        foreach ($data['assignees'] as $userId) {
            GroupTaskNotification::create([
                'group_task_id' => $task->id,
                'user_id'       => $userId,
                'type'          => 'assigned',
                'message'       => "You have been assigned task: {$task->title}.",
            ]);
        }

        $this->storeTaskMedia($request, $task);

        return redirect()
            ->route('admin.group-tasks.show', $task)
            ->with('success', 'Task created and assignees notified.');
    }

    /* ════════════════════════════════════════════
     |  SHOW
     ════════════════════════════════════════════ */
    public function show(GroupTask $groupTask)
    {
        $this->authorizeTask($groupTask);

        $groupTask->load([
            'group.members',
            'createdBy',
            'assignees',
            'acceptedBy',
            'completedBy',
        ]);

        // Mark notifications as read for current user
        GroupTaskNotification::where('group_task_id', $groupTask->id)
            ->where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('admin.groupTasks.show', compact('groupTask'));
    }

    /* ════════════════════════════════════════════
     |  EDIT / UPDATE
     ════════════════════════════════════════════ */
    public function edit(GroupTask $groupTask)
    {
        $this->authorizeTask($groupTask);
        abort_if($groupTask->status === 'completed', 403, 'Completed tasks cannot be edited.');

        $groups = $this->availableGroups()->with('members')->orderBy('name')->get();
        $groupTask->load('assignees');

        return view('admin.groupTasks.edit', compact('groupTask', 'groups'));
    }

    public function update(Request $request, GroupTask $groupTask)
    {
        $this->authorizeTask($groupTask);
        abort_if($groupTask->status === 'completed', 403, 'Completed tasks cannot be edited.');

        $data = $request->validate([
            'task_group_id'  => 'required|exists:task_groups,id',
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'priority'       => 'required|in:low,medium,high,urgent',
            'due_at'         => 'nullable|date',
            'assignees'      => 'required|array|min:1',
            'assignees.*'    => 'exists:users,id',
            'attachments.*'  => 'nullable|file|max:20480',
            'voice_note'     => 'nullable|file|max:20480',
        ]);

        $group = TaskGroup::with(['members' => fn ($q) => $q->activeEmployees()])->findOrFail($data['task_group_id']);
        $this->authorizeGroup($group);

        abort_if(
            count(array_diff($data['assignees'], $group->members->pluck('id')->all())) > 0,
            422,
            'All assignees must be members of the selected group.'
        );

        $groupTask->update([
            'task_group_id' => $group->id,
            'title'         => $data['title'],
            'description'   => $data['description'] ?? null,
            'priority'      => $data['priority'],
            'due_at'        => $data['due_at'] ?? null,
        ]);

        $oldIds = $groupTask->assignees()->pluck('users.id')->all();
        $groupTask->assignees()->sync($data['assignees']);

        foreach (array_diff($data['assignees'], $oldIds) as $userId) {
            GroupTaskNotification::create([
                'group_task_id' => $groupTask->id,
                'user_id'       => $userId,
                'type'          => 'assigned',
                'message'       => "You have been assigned task: {$groupTask->title}.",
            ]);
        }

        $this->storeTaskMedia($request, $groupTask);

        return redirect()
            ->route('admin.group-tasks.show', $groupTask)
            ->with('success', 'Task updated.');
    }

    /* ════════════════════════════════════════════
     |  DESTROY
     ════════════════════════════════════════════ */
    public function destroy(GroupTask $groupTask)
    {
        $this->authorizeTask($groupTask);
        abort_if($groupTask->status === 'completed' && ! Auth::user()->is_admin, 403);

        $groupTask->delete();

        return redirect()
            ->route('admin.group-tasks.index')
            ->with('success', 'Task deleted.');
    }

    /* ════════════════════════════════════════════
     |  ACCEPT
     ════════════════════════════════════════════ */
    public function accept(Request $request, GroupTask $groupTask)
    {
        $this->authorizeTask($groupTask);
        abort_unless($groupTask->status === 'pending', 422, 'Task is already accepted or completed.');

        $data = $request->validate([
            'estimate_type'   => 'required|in:hours,date',
            'estimated_hours' => 'required_if:estimate_type,hours|nullable|integer|min:1|max:10000',
            'estimated_date'  => 'required_if:estimate_type,date|nullable|date|after:today',
            'accept_narration'=> 'nullable|string|max:2000',
        ]);

        $acceptedAt = now();
        $requestedMinutes = $data['estimate_type'] === 'hours'
            ? ((int) $data['estimated_hours']) * 60
            : max(0, $acceptedAt->diffInMinutes(
                Carbon::parse($data['estimated_date'])->endOfDay(),
                false
            ));

        $groupTask->update([
            'status'            => 'accepted',
            'accepted_by_id'    => Auth::id(),
            'accepted_at'       => $acceptedAt,
            'accept_role'       => Auth::user()->roles->pluck('title')->implode(', ') ?: 'Employee',
            'estimate_type'     => $data['estimate_type'],
            'estimated_hours'   => $data['estimated_hours'] ?? null,
            'estimated_date'    => $data['estimated_date'] ?? null,
            'accept_narration'  => $data['accept_narration'] ?? null,
            'requested_minutes' => $requestedMinutes,
        ]);

        $groupTask->assignees()->syncWithoutDetaching([Auth::id() => ['status' => 'accepted']]);

        // Notify task creator
        GroupTaskNotification::create([
            'group_task_id' => $groupTask->id,
            'user_id'       => $groupTask->created_by_id,
            'type'          => 'accepted',
            'message'       => Auth::user()->name . " accepted the task: {$groupTask->title}.",
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->route('admin.group-tasks.show', $groupTask)
            ->with('success', 'Task accepted successfully.');
    }

    /* ════════════════════════════════════════════
     |  COMPLETE
     ════════════════════════════════════════════ */
    public function complete(Request $request, GroupTask $groupTask)
    {
        $this->authorizeTask($groupTask);
        abort_unless($groupTask->status === 'accepted', 422, 'Task must be accepted before completion.');

        $data = $request->validate([
            'completion_narration'   => 'required|string|max:5000',
            'completion_attachments.*'=> 'nullable|file|max:20480',
            'completion_voice_note'  => 'nullable|file|max:20480',
        ]);

        $completedAt    = now();
        $acceptedAt     = $groupTask->accepted_at
            ? Carbon::parse($groupTask->accepted_at)
            : Carbon::parse($groupTask->created_at);
        $actualMinutes  = $acceptedAt->diffInMinutes($completedAt);
        $delayMinutes   = $groupTask->requested_minutes
            ? $actualMinutes - $groupTask->requested_minutes
            : null;

        $groupTask->update([
            'status'               => 'completed',
            'completed_by_id'      => Auth::id(),
            'completed_at'         => $completedAt,
            'completion_narration' => $data['completion_narration'],
            'actual_minutes'       => $actualMinutes,
            'delay_minutes'        => $delayMinutes,
            'completion_points'    => $this->calculateCompletionPoints($groupTask, Auth::id(), $delayMinutes),
        ]);

        $groupTask->assignees()->syncWithoutDetaching([Auth::id() => ['status' => 'completed']]);
        $this->storeCompletionPoints($groupTask->fresh('assignees'), Auth::id(), $delayMinutes);
        $this->storeCompletionMedia($request, $groupTask);

        // Notify task creator
        GroupTaskNotification::create([
            'group_task_id' => $groupTask->id,
            'user_id'       => $groupTask->created_by_id,
            'type'          => 'completed',
            'message'       => Auth::user()->name . " completed the task: {$groupTask->title}.",
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->route('admin.group-tasks.show', $groupTask)
            ->with('success', 'Task completed successfully.');
    }

    /* ════════════════════════════════════════════
     |  LIVE POLL (SSE-like polling endpoint)
     ════════════════════════════════════════════ */
    public function live()
    {
        $userId = Auth::id();
        $isAdmin = Auth::user()->is_admin;

        // Pending tasks assigned to me — the "ring" tasks
        $ringTasks = GroupTask::with(['group', 'createdBy'])
            ->where('status', 'pending')
            ->whereHas('assignees', fn ($q) => $q->where('users.id', $userId))
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($t) => [
                'id'         => $t->id,
                'title'      => $t->title,
                'priority'   => $t->priority,
                'group'      => optional($t->group)->name,
                'created_by' => optional($t->createdBy)->name,
                'url'        => route('admin.group-tasks.show', $t),
            ]);

        // Unread notifications for current user
        $notifications = GroupTaskNotification::with('task')
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->latest()
            ->take(10)
            ->get()
            ->filter(fn ($n) => $n->group_task_id !== null)
            ->map(fn ($n) => [
                'id'      => $n->id,
                'message' => $n->message,
                'type'    => $n->type,
                'url'     => $n->group_task_id ? route('admin.group-tasks.show', $n->group_task_id) : '#',
            ]);

        // Group notifications (group_created type) for this user
        $groupNotifs = GroupTaskNotification::where('user_id', $userId)
            ->whereNull('group_task_id')
            ->whereNull('read_at')
            ->latest()
            ->get()
            ->map(fn ($n) => [
                'id'      => $n->id,
                'message' => $n->message,
                'type'    => 'group_created',
                'url'     => $isAdmin ? route('admin.task-groups.index') : route('admin.home'),
            ]);

        // My active tasks with deadlines (for countdown)
        $myActiveTasks = GroupTask::where('status', 'accepted')
            ->where(function ($q) use ($userId) {
                $q->where('accepted_by_id', $userId)
                    ->orWhereHas('assignees', fn ($aq) => $aq->where('users.id', $userId));
            })
            ->get(['id', 'title', 'accepted_at', 'estimate_type', 'estimated_hours', 'estimated_date', 'requested_minutes'])
            ->map(function ($t) {
                $deadline = $t->deadline_at;
                return [
                    'id'           => $t->id,
                    'title'        => $t->title,
                    'deadline_iso' => $deadline ? $deadline->toIso8601String() : null,
                    'is_delayed'   => $t->is_delayed,
                ];
            });

        // Summary counts
        $summary = $this->summary();

        // Admin groups summary
        $groupsQuery = TaskGroup::withCount(['members', 'tasks'])
            ->with('members:id,name')
            ->where('is_active', true);

        if (! $isAdmin) {
            $groupsQuery->whereHas('members', fn ($q) => $q->where('users.id', $userId));
        }

        $groupsSummary = $groupsQuery
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($g) => [
                'id'            => $g->id,
                'name'          => $g->name,
                'members_count' => $g->members_count,
                'tasks_count'   => $g->tasks_count,
                'members'       => $g->members->pluck('name')->values(),
            ]);

        return response()->json([
            'ring'          => $ringTasks,
            'notifications' => $notifications->merge($groupNotifs)->values(),
            'active_tasks'  => $myActiveTasks,
            'summary'       => $summary,
            'groups'        => $groupsSummary,
        ]);
    }

    /* ════════════════════════════════════════════
     |  MARK NOTIFICATION READ
     ════════════════════════════════════════════ */
    public function markRead(Request $request)
    {
        GroupTaskNotification::where('user_id', Auth::id())
            ->when($request->id, fn ($q) => $q->where('id', $request->id))
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /* ════════════════════════════════════════════
     |  REPORT
     ════════════════════════════════════════════ */
    public function report(Request $request)
    {
        $isAdmin = Auth::user()->is_admin;
        $groups  = $this->availableGroups()
            ->with(['members' => fn ($q) => $q->activeEmployees()->select('users.id', 'users.name')])
            ->orderBy('name')
            ->get();

        $selectedGroup  = $request->group_id
            ? TaskGroup::with(['members' => fn ($q) => $q->activeEmployees()->select('users.id', 'users.name')])->find($request->group_id)
            : null;
        $members        = $selectedGroup ? $selectedGroup->members : collect();

        if (! $isAdmin) {
            $members = collect([Auth::user()]);
        }

        $selectedMember = $isAdmin
            ? ($request->member_id ? User::find($request->member_id) : null)
            : Auth::user();

        $report = null;

        if ($selectedMember) {
            $base = $this->visibleTasks()
                ->when($selectedGroup, fn ($q) => $q->where('task_group_id', $selectedGroup->id))
                ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
                ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to));

            $taskQuery = (clone $base)->with(['group', 'createdBy', 'assignees', 'acceptedBy', 'completedBy'])
                ->where(function ($q) use ($selectedMember) {
                    $q->where('created_by_id', $selectedMember->id)
                        ->orWhere('accepted_by_id', $selectedMember->id)
                        ->orWhere('completed_by_id', $selectedMember->id)
                        ->orWhereHas('assignees', fn ($aq) => $aq->where('users.id', $selectedMember->id));
                })
                ->latest()
                ->get();

            $report = [
                'created'      => (clone $base)->where('created_by_id', $selectedMember->id)->count(),
                'assigned'     => (clone $base)->whereHas('assignees', fn ($q) => $q->where('users.id', $selectedMember->id))->count(),
                'accepted'     => (clone $base)->where('accepted_by_id', $selectedMember->id)->count(),
                'completed'    => (clone $base)->where('completed_by_id', $selectedMember->id)->count(),
                'others_solved'=> (clone $base)->where('completed_by_id', $selectedMember->id)
                    ->whereDoesntHave('assignees', fn ($q) => $q->where('users.id', $selectedMember->id))
                    ->count(),
                'on_time'      => (clone $base)->where('completed_by_id', $selectedMember->id)
                    ->whereNotNull('completed_at')
                    ->where(function ($q) {
                        $q->whereNull('delay_minutes')->orWhere('delay_minutes', '<=', 0);
                    })
                    ->count(),
                'late'         => (clone $base)->where('completed_by_id', $selectedMember->id)
                    ->where('delay_minutes', '>', 0)
                    ->count(),
                'points'       => GroupTaskPoint::where('user_id', $selectedMember->id)
                    ->when($selectedGroup, fn ($q) => $q->where('task_group_id', $selectedGroup->id))
                    ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
                    ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
                    ->sum('points'),
                'tasks'        => $taskQuery,
            ];
        }

        return view('admin.groupTasks.report', compact(
            'groups', 'selectedGroup', 'members', 'selectedMember', 'report'
        ));
    }

    /* ════════════════════════════════════════════
     |  MEMBERS JSON for task form
     ════════════════════════════════════════════ */
    public function members(TaskGroup $taskGroup)
    {
        $this->authorizeGroup($taskGroup);

        return response()->json(
            $taskGroup->members()->activeEmployees()->orderBy('name')->get(['users.id', 'users.name'])
        );
    }

    /* ════════════════════════════════════════════
     |  PRIVATE HELPERS
     ════════════════════════════════════════════ */

    private function visibleTasks()
    {
        $q = GroupTask::query();

        if (! Auth::user()->is_admin) {
            $q->where(function ($inner) {
                $inner->where('created_by_id', Auth::id())
                    ->orWhereHas('group.members', fn ($m) => $m->where('users.id', Auth::id()));
            });
        }

        return $q;
    }

    private function availableGroups()
    {
        $query = TaskGroup::where('is_active', true);

        // Agar admin nahi hai
        if (!Auth::user()->is_admin) {

            // Sirf wahi groups jahan user member hai
            $query->whereHas('members', function ($q) {
                $q->where('users.id', Auth::id());
            });
        }

        return $query;
    }

    private function authorizeTask(GroupTask $task): void
    {
        $task->loadMissing('group.members');
        $this->authorizeGroup($task->group);
    }

    private function authorizeGroup(TaskGroup $group): void
    {
        if (Auth::user()->is_admin) {
            return;
        }

        abort_unless($group->members()->where('users.id', Auth::id())->exists(), 403);
    }

    private function summary(): array
    {
        $v = $this->visibleTasks();

        return [
            'total'     => (clone $v)->count(),
            'pending'   => (clone $v)->where('status', 'pending')->count(),
            'accepted'  => (clone $v)->where('status', 'accepted')->count(),
            'completed' => (clone $v)->where('status', 'completed')->count(),
            'delayed'   => (clone $v)->where('status', 'accepted')->get()->filter->is_delayed->count(),
        ];
    }

    private function storeTaskMedia(Request $request, GroupTask $task): void
    {
        foreach ($request->file('attachments', []) as $file) {
            $task->addMedia($file)->toMediaCollection('attachments');
        }

        if ($request->hasFile('voice_note')) {
            $task->addMediaFromRequest('voice_note')->toMediaCollection('voice_notes');
        }
    }

    private function storeCompletionMedia(Request $request, GroupTask $task): void
    {
        foreach ($request->file('completion_attachments', []) as $file) {
            $task->addMedia($file)->toMediaCollection('completion_attachments');
        }

        if ($request->hasFile('completion_voice_note')) {
            $task->addMediaFromRequest('completion_voice_note')->toMediaCollection('completion_voice_notes');
        }
    }

    private function calculateCompletionPoints(GroupTask $task, int $userId, ?int $delayMinutes): float
    {
        $wasAssigned = $task->assignees()->where('users.id', $userId)->exists();
        $withinDeadline = $delayMinutes === null || $delayMinutes <= 0;

        if ($wasAssigned) {
            return $withinDeadline ? 1.0 : 0.5;
        }

        return $withinDeadline ? 1.5 : 1.0;
    }

    private function storeCompletionPoints(GroupTask $task, int $userId, ?int $delayMinutes): void
    {
        $wasAssigned = $task->assignees->contains('id', $userId);
        $withinDeadline = $delayMinutes === null || $delayMinutes <= 0;
        $points = $this->calculateCompletionPoints($task, $userId, $delayMinutes);

        GroupTaskPoint::updateOrCreate(
            [
                'group_task_id' => $task->id,
                'user_id'       => $userId,
            ],
            [
                'task_group_id'             => $task->task_group_id,
                'points'                    => $points,
                'reason'                    => $wasAssigned
                    ? ($withinDeadline ? 'Assigned task completed on time' : 'Assigned task completed late')
                    : ($withinDeadline ? 'Unassigned group task completed on time' : 'Unassigned group task completed late'),
                'was_assigned'              => $wasAssigned,
                'completed_within_deadline' => $withinDeadline,
            ]
        );
    }
}
