@extends('layouts.admin')

@section('content')

@can('user_create')
<div style="margin-bottom: 10px;" class="row">
    <div class="col-lg-12">
        <a class="btn btn-success" href="{{ route('admin.users.create') }}">
            {{ trans('global.add') }} {{ trans('cruds.user.title_singular') }}
        </a>

        <button class="btn btn-warning" data-toggle="modal" data-target="#csvImportModal">
            {{ trans('global.app_csvImport') }}
        </button>

        @include('csvImport.modal', [
            'model' => 'User',
            'route' => 'admin.users.parseCsvImport'
        ])
    </div>
</div>
@endcan

<div class="card">
    <div class="card-header">
        {{ trans('cruds.user.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover datatable datatable-User">
                <thead>
                    <tr>
                        <th>{{ trans('cruds.user.fields.id') }}</th>
                        <th>{{ trans('cruds.user.fields.name') }}</th>
                        <th>{{ trans('cruds.user.fields.email') }}</th>
                        <th>{{ trans('cruds.user.fields.email_verified_at') }}</th>
                        <th>{{ trans('cruds.user.fields.roles') }}</th>
                        <th>{{ trans('cruds.user.fields.number') }}</th>
                        <th>{{ trans('cruds.user.fields.address') }}</th>
                        <th>{{ trans('cruds.user.fields.degination') }}</th>
                        <th>{{ trans('cruds.user.fields.company') }}</th>
                        <th>{{ trans('cruds.user.fields.branch') }}</th>
                        <th>{{ trans('cruds.user.fields.emergency_number') }}</th>
                        <th>Master Password</th>
                        <th>{{ trans('cruds.user.fields.image') }}</th>
                        <th>{{ trans('global.actions') }}</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->email_verified_at }}</td>

                        <td>
                            @foreach($user->roles as $role)
                                <span class="badge badge-info">{{ $role->title }}</span>
                            @endforeach
                        </td>

                        <td>{{ $user->number }}</td>
                        <td>{{ $user->address }}</td>
                        <td>{{ $user->degination }}</td>

                        <td>
                            @foreach($user->companies as $company)
                                <span class="badge badge-primary">{{ $company->title }}</span>
                            @endforeach
                        </td>

                        <td>
                            @foreach($user->branches as $branch)
                                <span class="badge badge-warning">{{ $branch->title }}</span>
                            @endforeach
                        </td>

                        <td>{{ $user->emergency_number }}</td>

                        <td>********</td>

                        <td>
                            @if($user->image)
                                <a href="{{ $user->image->url }}" target="_blank">
                                    <img src="{{ $user->image->getUrl('thumb') }}" width="50" height="50">
                                </a>
                            @else
                                <img src="{{ asset('images/default-avatar.png') }}" width="50" height="50">
                            @endif

                        </td>

                        <td>
                            @can('user_show')
                                <a class="btn btn-xs btn-primary"
                                   href="{{ route('admin.users.show', $user->id) }}">
                                    {{ trans('global.view') }}
                                </a>
                            @endcan

                            @can('user_edit')
                                <a class="btn btn-xs btn-info"
                                   href="{{ route('admin.users.edit', $user->id) }}">
                                    {{ trans('global.edit') }}
                                </a>
                            @endcan

                            @can('user_delete')
                                <form action="{{ route('admin.users.destroy', $user->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('{{ trans('global.areYouSure') }}');"
                                      style="display:inline-block;">
                                    @method('DELETE')
                                    @csrf
                                    <button class="btn btn-xs btn-danger">
                                        {{ trans('global.delete') }}
                                    </button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>
</div>

@endsection

{{-- ================== DATATABLE SCRIPT ================== --}}
@section('scripts')
@parent
<script>
    $(function () {
        $('.datatable-User').DataTable({
            pageLength: 25,
            order: [[0, 'desc']],
            responsive: true,
            dom: 'lBfrtip',
            buttons: [
                {
                    extend: 'copy',
                    className: 'btn-default'
                },
                {
                    extend: 'excel',
                    className: 'btn-default'
                },
                {
                    extend: 'print',
                    className: 'btn-default'
                }
            ]
        });
    });
</script>
@endsection
