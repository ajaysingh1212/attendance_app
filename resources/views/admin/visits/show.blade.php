@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.visit.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.visits.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.visit.fields.id') }}
                        </th>
                        <td>
                            {{ $visit->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.visit.fields.user') }}
                        </th>
                        <td>
                            {{ $visit->user }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.visit.fields.latitude') }}
                        </th>
                        <td>
                            {{ $visit->latitude }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.visit.fields.longitude') }}
                        </th>
                        <td>
                            {{ $visit->longitude }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.visit.fields.location') }}
                        </th>
                        <td>
                            {{ $visit->location }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.visit.fields.visited_time') }}
                        </th>
                        <td>
                            {{ $visit->visited_time }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.visit.fields.visited_counter_image') }}
                        </th>
                        <td>
                            @if($visit->visited_counter_image)
                                <a href="{{ $visit->visited_counter_image->getUrl() }}" target="_blank" style="display: inline-block">
                                    <img src="{{ $visit->visited_counter_image->getUrl('thumb') }}">
                                </a>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.visit.fields.visit_self_image') }}
                        </th>
                        <td>
                            @if($visit->visit_self_image)
                                <a href="{{ $visit->visit_self_image->getUrl() }}" target="_blank" style="display: inline-block">
                                    <img src="{{ $visit->visit_self_image->getUrl('thumb') }}">
                                </a>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.visit.fields.visited_out_latitude') }}
                        </th>
                        <td>
                            {{ $visit->visited_out_latitude }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.visit.fields.visited_out_longitude') }}
                        </th>
                        <td>
                            {{ $visit->visited_out_longitude }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.visit.fields.visited_out_location') }}
                        </th>
                        <td>
                            {{ $visit->visited_out_location }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.visit.fields.visited_out_time') }}
                        </th>
                        <td>
                            {{ $visit->visited_out_time }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.visit.fields.visited_out_counter_image') }}
                        </th>
                        <td>
                            @if($visit->visited_out_counter_image)
                                <a href="{{ $visit->visited_out_counter_image->getUrl() }}" target="_blank" style="display: inline-block">
                                    <img src="{{ $visit->visited_out_counter_image->getUrl('thumb') }}">
                                </a>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.visit.fields.visited_out_self_image') }}
                        </th>
                        <td>
                            @if($visit->visited_out_self_image)
                                <a href="{{ $visit->visited_out_self_image->getUrl() }}" target="_blank" style="display: inline-block">
                                    <img src="{{ $visit->visited_out_self_image->getUrl('thumb') }}">
                                </a>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.visit.fields.visited_duration') }}
                        </th>
                        <td>
                            {{ $visit->visited_duration }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.visits.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection