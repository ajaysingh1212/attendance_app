@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.addRequestAmount.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.add-request-amounts.update", [$addRequestAmount->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="form-group">
    <label for="employee_id">{{ trans('cruds.addRequestAmount.fields.employee') }}</label>
    <select class="form-control select2 {{ $errors->has('employee') ? 'is-invalid' : '' }}" 
            name="employee_id" id="employee_id">
        @foreach($employees as $id => $entry)
            <option value="{{ $id }}" {{ (old('employee_id') ? old('employee_id') : $addRequestAmount->employee_id ?? '') == $id ? 'selected' : '' }}>
                {{ $entry }}
            </option>
        @endforeach
    </select>
    @if($errors->has('employee'))
        <div class="invalid-feedback">
            {{ $errors->first('employee') }}
        </div>
    @endif
</div>

            <div class="form-group">
                <label for="amount">{{ trans('cruds.addRequestAmount.fields.amount') }}</label>
                <input class="form-control {{ $errors->has('amount') ? 'is-invalid' : '' }}" type="text" name="amount" id="amount" value="{{ old('amount', $addRequestAmount->amount) }}">
                @if($errors->has('amount'))
                    <div class="invalid-feedback">
                        {{ $errors->first('amount') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.addRequestAmount.fields.amount_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="description">{{ trans('cruds.addRequestAmount.fields.description') }}</label>
                <input class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}" type="text" name="description" id="description" value="{{ old('description', $addRequestAmount->description) }}">
                @if($errors->has('description'))
                    <div class="invalid-feedback">
                        {{ $errors->first('description') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.addRequestAmount.fields.description_helper') }}</span>
            </div>
            <div class="form-group">
                <label>{{ trans('cruds.addRequestAmount.fields.status') }}</label>
                <select class="form-control {{ $errors->has('status') ? 'is-invalid' : '' }}" name="status" id="status">
                    <option value disabled {{ old('status', null) === null ? 'selected' : '' }}>{{ trans('global.pleaseSelect') }}</option>
                    @foreach(App\Models\AddRequestAmount::STATUS_SELECT as $key => $label)
                        <option value="{{ $key }}" {{ old('status', $addRequestAmount->status) === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @if($errors->has('status'))
                    <div class="invalid-feedback">
                        {{ $errors->first('status') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.addRequestAmount.fields.status_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="remark">{{ trans('cruds.addRequestAmount.fields.remark') }}</label>
                <input class="form-control {{ $errors->has('remark') ? 'is-invalid' : '' }}" type="text" name="remark" id="remark" value="{{ old('remark', $addRequestAmount->remark) }}">
                @if($errors->has('remark'))
                    <div class="invalid-feedback">
                        {{ $errors->first('remark') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.addRequestAmount.fields.remark_helper') }}</span>
            </div>
            <div class="form-group">
                <button class="btn btn-danger" type="submit">
                    {{ trans('global.save') }}
                </button>
            </div>
        </form>
    </div>
</div>



@endsection