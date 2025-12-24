@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.leaveRequest.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.leave-requests.update", [$leaveRequest->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf

            {{-- User --}}
            <div class="form-group">
                <label for="user_id">{{ trans('cruds.leaveRequest.fields.user') }}</label>
                <select class="form-control select2 {{ $errors->has('user_id') ? 'is-invalid' : '' }}" name="user_id" id="user_id">
                    @foreach($users as $id => $entry)
                        <option value="{{ $id }}" {{ (old('user_id', $leaveRequest->user_id) == $id) ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Leave Type --}}
            <div class="form-group">
                <label for="leave_type_id">{{ trans('cruds.leaveRequest.fields.leave_type') }}</label>
                <select class="form-control select2 {{ $errors->has('leave_type_id') ? 'is-invalid' : '' }}" name="leave_type_id" id="leave_type_id">
                    @foreach($leaveTypes as $id => $entry)
                        <option value="{{ $id }}" {{ (old('leave_type_id', $leaveRequest->leave_type_id) == $id) ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @error('leave_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Title --}}
            <div class="form-group">
                <label for="title">{{ trans('cruds.leaveRequest.fields.title') }}</label>
                <input class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" type="text" name="title" id="title" value="{{ old('title', $leaveRequest->title) }}">
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Description --}}
            <div class="form-group">
                <label for="description">{{ trans('cruds.leaveRequest.fields.description') }}</label>
                <textarea class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}" name="description" id="description">{!! old('description', $leaveRequest->description) !!}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Date From --}}
            <div class="form-group">
                <label for="date_from">{{ trans('cruds.leaveRequest.fields.date_from') }}</label>
                <input class="form-control {{ $errors->has('date_from') ? 'is-invalid' : '' }}" type="date" name="date_from" id="date_from" value="{{ old('date_from', $leaveRequest->date_from) }}">
                @error('date_from')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Date To --}}
            <div class="form-group">
                <label for="date_to">{{ trans('cruds.leaveRequest.fields.date_to') }}</label>
                <input class="form-control {{ $errors->has('date_to') ? 'is-invalid' : '' }}" type="date" name="date_to" id="date_to" value="{{ old('date_to', $leaveRequest->date_to) }}">
                @error('date_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Attachment --}}
            <div class="form-group">
                <label>{{ trans('cruds.leaveRequest.fields.attachment') }}</label>
                <div class="needsclick dropzone {{ $errors->has('attachment') ? 'is-invalid' : '' }}" id="attachment-dropzone"></div>
                @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status" class="form-control" required>
                    <option value="pending" {{ $leaveRequest->status == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ $leaveRequest->status == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ $leaveRequest->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>


            {{-- Remark --}}
            <div class="form-group">
                <label for="remark">{{ trans('cruds.leaveRequest.fields.remark') }}</label>
                <textarea class="form-control ckeditor {{ $errors->has('remark') ? 'is-invalid' : '' }}" name="remark" id="remark">{!! old('remark', $leaveRequest->remark) !!}</textarea>
                @error('remark')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Submit --}}
            <div class="form-group text-end">
                <button class="btn btn-primary" type="submit">
                    {{ trans('global.save') }}
                </button>
            </div>

        </form>
    </div>
</div>
@endsection
