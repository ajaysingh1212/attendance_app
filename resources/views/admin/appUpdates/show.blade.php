@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.appUpdate.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.app-updates.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.appUpdate.fields.id') }}
                        </th>
                        <td>
                            {{ $appUpdate->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.appUpdate.fields.version') }}
                        </th>
                        <td>
                            {{ $appUpdate->version }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.appUpdate.fields.heading') }}
                        </th>
                        <td>
                            {{ $appUpdate->heading }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.appUpdate.fields.content') }}
                        </th>
                        <td>
                            {!! $appUpdate->content !!}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.appUpdate.fields.app') }}
                        </th>
                        <td>
                            @if($appUpdate->app)
                                <a href="{{ $appUpdate->app->getUrl() }}" target="_blank">
                                    {{ trans('global.view_file') }}
                                </a>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.app-updates.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection