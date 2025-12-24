@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.showReport.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.show-reports.update", [$showReport->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="form-group">
                <label class="required" for="select_employess_id">{{ trans('cruds.showReport.fields.select_employess') }}</label>
                <select class="form-control select2 {{ $errors->has('select_employess') ? 'is-invalid' : '' }}" name="select_employess_id" id="select_employess_id" required>
                    @foreach($select_employesses as $id => $entry)
                        <option value="{{ $id }}" {{ (old('select_employess_id') ? old('select_employess_id') : $showReport->select_employess->id ?? '') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('select_employess'))
                    <div class="invalid-feedback">
                        {{ $errors->first('select_employess') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.showReport.fields.select_employess_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="start_date">{{ trans('cruds.showReport.fields.start_date') }}</label>
                <input class="form-control datetime {{ $errors->has('start_date') ? 'is-invalid' : '' }}" type="text" name="start_date" id="start_date" value="{{ old('start_date', $showReport->start_date) }}">
                @if($errors->has('start_date'))
                    <div class="invalid-feedback">
                        {{ $errors->first('start_date') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.showReport.fields.start_date_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="end_date">{{ trans('cruds.showReport.fields.end_date') }}</label>
                <input class="form-control datetime {{ $errors->has('end_date') ? 'is-invalid' : '' }}" type="text" name="end_date" id="end_date" value="{{ old('end_date', $showReport->end_date) }}">
                @if($errors->has('end_date'))
                    <div class="invalid-feedback">
                        {{ $errors->first('end_date') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.showReport.fields.end_date_helper') }}</span>
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