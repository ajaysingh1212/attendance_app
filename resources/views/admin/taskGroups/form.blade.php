@php
    $selectedMembers = $taskGroup ? $taskGroup->members->pluck('id')->toArray() : [auth()->id()];
    $memberRoles = $taskGroup ? $taskGroup->members->pluck('pivot.member_role', 'id')->toArray() : [auth()->id() => 'Member'];
@endphp
<div class="row">
    <div class="form-group col-lg-6">
        <label class="required" for="name">Group Name</label>
        <input class="form-control" type="text" name="name" id="name" value="{{ old('name', optional($taskGroup)->name) }}" required>
    </div>
    <div class="form-group col-lg-3">
        <label>Status</label>
        <select class="form-control" name="is_active">
            <option value="1" {{ old('is_active', optional($taskGroup)->is_active ?? 1) == 1 ? 'selected' : '' }}>Active</option>
            <option value="0" {{ old('is_active', optional($taskGroup)->is_active ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>
    <div class="form-group col-lg-12">
        <label for="description">Description</label>
        <textarea class="form-control" name="description" id="description" rows="3">{{ old('description', optional($taskGroup)->description) }}</textarea>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header bg-light"><i class="fas fa-users"></i> Group Members</div>
    <div class="card-body">
        <div class="row">
            @foreach($users as $user)
                <div class="col-lg-4 col-md-6 mb-2">
                    <div class="border rounded p-2 h-100">
                        <label class="mb-1 d-block">
                            <input type="checkbox" name="members[]" value="{{ $user->id }}" {{ in_array($user->id, old('members', $selectedMembers)) ? 'checked' : '' }}>
                            <strong>{{ $user->name }}</strong>
                        </label>
                        <input class="form-control form-control-sm" name="member_roles[{{ $user->id }}]" value="{{ old('member_roles.' . $user->id, $memberRoles[$user->id] ?? 'Member') }}" placeholder="Role in group">
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
