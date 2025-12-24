@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.addRequestAmount.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.add-request-amounts.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.addRequestAmount.fields.id') }}
                        </th>
                        <td>
                            {{ $addRequestAmount->id }}
                        </td>
                    </tr>
                   <tr>
                        <th>Employee Name</th>
                        <td>{{ $addRequestAmount->employee ? $addRequestAmount->employee->full_name : '' }}</td>
                   </tr>

                    <tr>
                        <th>
                            {{ trans('cruds.addRequestAmount.fields.amount') }}
                        </th>
                        <td>
                            {{ $addRequestAmount->amount }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.addRequestAmount.fields.description') }}
                        </th>
                        <td>
                            {{ $addRequestAmount->description }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.addRequestAmount.fields.status') }}
                        </th>
                        <td>
                            {{ App\Models\AddRequestAmount::STATUS_SELECT[$addRequestAmount->status] ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.addRequestAmount.fields.remark') }}
                        </th>
                        <td>
                            {{ $addRequestAmount->remark }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.add-request-amounts.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection