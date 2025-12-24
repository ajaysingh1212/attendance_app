@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.leaveRequest.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.leave-requests.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.leaveRequest.fields.id') }}
                        </th>
                        <td>
                            {{ $leaveRequest->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.leaveRequest.fields.user') }}
                        </th>
                        <td>
                            {{ $leaveRequest->user->name ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.leaveRequest.fields.title') }}
                        </th>
                        <td>
                            {{ $leaveRequest->title }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.leaveRequest.fields.description') }}
                        </th>
                        <td>
                            {!! $leaveRequest->description !!}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.leaveRequest.fields.date_from') }}
                        </th>
                        <td>
                            {{ $leaveRequest->date_from }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.leaveRequest.fields.date_to') }}
                        </th>
                        <td>
                            {{ $leaveRequest->date_to }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.leaveRequest.fields.attachment') }}
                        </th>
                        <td>
                            @if($leaveRequest->attachment)
                                <a href="{{ $leaveRequest->attachment->getUrl() }}" target="_blank" style="display: inline-block">
                                    <img src="{{ $leaveRequest->attachment->getUrl('thumb') }}">
                                </a>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.leaveRequest.fields.status') }}
                        </th>
                        <td>
                            {{ App\Models\LeaveRequest::STATUS_SELECT[$leaveRequest->status] ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.leaveRequest.fields.remark') }}
                        </th>
                        <td>
                            {!! $leaveRequest->remark !!}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.leave-requests.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection