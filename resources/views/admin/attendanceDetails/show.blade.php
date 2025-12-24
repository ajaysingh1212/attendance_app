@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.attendanceDetail.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.attendance-details.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.attendanceDetail.fields.id') }}
                        </th>
                        <td>
                            {{ $attendanceDetail->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.attendanceDetail.fields.user') }}
                        </th>
                        <td>
                            {{ $attendanceDetail->user->name ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.attendanceDetail.fields.punch_in_time') }}
                        </th>
                        <td>
                            {{ $attendanceDetail->punch_in_time }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.attendanceDetail.fields.punch_in_latitude') }}
                        </th>
                        <td>
                            {{ $attendanceDetail->punch_in_latitude }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.attendanceDetail.fields.punch_in_longitude') }}
                        </th>
                        <td>
                            {{ $attendanceDetail->punch_in_longitude }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.attendanceDetail.fields.punch_in_location') }}
                        </th>
                        <td>
                            {{ $attendanceDetail->punch_in_location }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.attendanceDetail.fields.punch_in_image') }}
                        </th>
                        <td>
                            @if($attendanceDetail->punch_in_image)
                                <a href="{{ $attendanceDetail->punch_in_image->getUrl() }}" target="_blank" style="display: inline-block">
                                    <img src="{{ $attendanceDetail->punch_in_image->getUrl('thumb') }}">
                                </a>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.attendanceDetail.fields.punch_out_time') }}
                        </th>
                        <td>
                            {{ $attendanceDetail->punch_out_time }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.attendanceDetail.fields.punch_out_latitude') }}
                        </th>
                        <td>
                            {{ $attendanceDetail->punch_out_latitude }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.attendanceDetail.fields.punch_out_longitude') }}
                        </th>
                        <td>
                            {{ $attendanceDetail->punch_out_longitude }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.attendanceDetail.fields.punch_out_location') }}
                        </th>
                        <td>
                            {{ $attendanceDetail->punch_out_location }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.attendanceDetail.fields.punch_out_image') }}
                        </th>
                        <td>
                            @if($attendanceDetail->punch_out_image)
                                <a href="{{ $attendanceDetail->punch_out_image->getUrl() }}" target="_blank" style="display: inline-block">
                                    <img src="{{ $attendanceDetail->punch_out_image->getUrl('thumb') }}">
                                </a>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.attendanceDetail.fields.status') }}
                        </th>
                        <td>
                            {{ App\Models\AttendanceDetail::STATUS_SELECT[$attendanceDetail->status] ?? '' }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.attendance-details.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection