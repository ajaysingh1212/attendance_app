@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.trackMember.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.track-members.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.trackMember.fields.id') }}
                        </th>
                        <td>
                            {{ $trackMember->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.trackMember.fields.select_member') }}
                        </th>
                        <td>
                            {{ $trackMember->select_member->member ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.trackMember.fields.latitude') }}
                        </th>
                        <td>
                            {{ $trackMember->latitude }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.trackMember.fields.longitude') }}
                        </th>
                        <td>
                            {{ $trackMember->longitude }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.trackMember.fields.location') }}
                        </th>
                        <td>
                            {{ $trackMember->location }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.trackMember.fields.time') }}
                        </th>
                        <td>
                            {{ $trackMember->time }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.track-members.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        {{ trans('global.relatedData') }}
    </div>
    <ul class="nav nav-tabs" role="tablist" id="relationship-tabs">
        <li class="nav-item">
            <a class="nav-link" href="#select_member_reports" role="tab" data-toggle="tab">
                {{ trans('cruds.report.title') }}
            </a>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane" role="tabpanel" id="select_member_reports">
            @includeIf('admin.trackMembers.relationships.selectMemberReports', ['reports' => $trackMember->selectMemberReports])
        </div>
    </div>
</div>

@endsection