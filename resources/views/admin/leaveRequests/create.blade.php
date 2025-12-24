@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">{{ trans('global.create') }} {{ trans('cruds.leaveRequest.title_singular') }}</h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route("admin.leave-requests.store") }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label" for="user_id">{{ trans('cruds.leaveRequest.fields.user') }}</label>
                            <select class="form-select select2 {{ $errors->has('user') ? 'is-invalid' : '' }}" name="user_id" id="user_id">
                                @foreach($users as $id => $entry)
                                    <option value="{{ $id }}" {{ old('user_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                                @endforeach
                            </select>
                            @include('partials.error', ['field' => 'user'])
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="leave_type_id">{{ trans('cruds.leaveRequest.fields.leave_type') }}</label>
                            <select class="form-select select2 {{ $errors->has('leave_type_id') ? 'is-invalid' : '' }}" name="leave_type_id" id="leave_type_id">
                                @foreach($leaveTypes as $id => $entry)
                                    <option value="{{ $id }}" {{ old('leave_type_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                                @endforeach
                            </select>
                            @include('partials.error', ['field' => 'leave_type_id'])
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="title">{{ trans('cruds.leaveRequest.fields.title') }}</label>
                            <input class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" type="text" name="title" id="title" value="{{ old('title') }}">
                            @include('partials.error', ['field' => 'title'])
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="description">{{ trans('cruds.leaveRequest.fields.description') }}</label>
                            <textarea class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}" name="description" id="description">{!! old('description') !!}</textarea>
                            @include('partials.error', ['field' => 'description'])
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="date_from">{{ trans('cruds.leaveRequest.fields.date_from') }}</label>
                                <input class="form-control {{ $errors->has('date_from') ? 'is-invalid' : '' }}" type="date" name="date_from" id="date_from" value="{{ old('date_from') }}">
                                @include('partials.error', ['field' => 'date_from'])
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="date_to">{{ trans('cruds.leaveRequest.fields.date_to') }}</label>
                                <input class="form-control {{ $errors->has('date_to') ? 'is-invalid' : '' }}" type="date" name="date_to" id="date_to" value="{{ old('date_to') }}">
                                @include('partials.error', ['field' => 'date_to'])
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ trans('cruds.leaveRequest.fields.attachment') }}</label>
                            <div class="needsclick dropzone {{ $errors->has('attachment') ? 'is-invalid' : '' }}" id="attachment-dropzone"></div>
                            @include('partials.error', ['field' => 'attachment'])
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ trans('cruds.leaveRequest.fields.status') }}</label>
                            <select class="form-select {{ $errors->has('status') ? 'is-invalid' : '' }}" name="status" id="status">
                                <option value="" disabled {{ old('status') === null ? 'selected' : '' }}>{{ trans('global.pleaseSelect') }}</option>
                                @foreach(App\Models\LeaveRequest::STATUS_SELECT as $key => $label)
                                    <option value="{{ $key }}" {{ old('status', 'Pending') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @include('partials.error', ['field' => 'status'])
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="remark">{{ trans('cruds.leaveRequest.fields.remark') }}</label>
                            <textarea class="form-control ckeditor {{ $errors->has('remark') ? 'is-invalid' : '' }}" name="remark" id="remark">{!! old('remark') !!}</textarea>
                            @include('partials.error', ['field' => 'remark'])
                        </div>

                        <div class="text-end">
                            <button class="btn btn-success" type="submit">
                                <i class="fas fa-save me-1"></i> {{ trans('global.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
