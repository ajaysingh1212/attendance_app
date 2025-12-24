@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('cruds.trackMember.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.track-members.store") }}" enctype="multipart/form-data">
            @csrf
            <h4 class= "text-center mt-2 py-2 ">Add Track Member</h4>
            <div class="row">
            <div class="form-group col-lg-4">
                <label class="required" for="select_member_id">{{ trans('cruds.trackMember.fields.select_member') }}</label>
                <select class="form-control select2 {{ $errors->has('select_member') ? 'is-invalid' : '' }}" name="select_member_id" id="select_member_id" required>
                    @foreach($select_members as $id => $entry)
                        <option value="{{ $id }}" {{ old('select_member_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('select_member'))
                    <span class="text-danger">{{ $errors->first('select_member') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.trackMember.fields.select_member_helper') }}</span>
            </div>
            <div class="form-group col-lg-4">
                <label class="required" for="latitude">{{ trans('cruds.trackMember.fields.latitude') }}</label>
                <input class="form-control {{ $errors->has('latitude') ? 'is-invalid' : '' }}" type="text" name="latitude" id="latitude" value="{{ old('latitude', '') }}" required>
                @if($errors->has('latitude'))
                    <span class="text-danger">{{ $errors->first('latitude') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.trackMember.fields.latitude_helper') }}</span>
            </div>
            <div class="form-group col-lg-4">
                <label class="required" for="longitude">{{ trans('cruds.trackMember.fields.longitude') }}</label>
                <input class="form-control {{ $errors->has('longitude') ? 'is-invalid' : '' }}" type="text" name="longitude" id="longitude" value="{{ old('longitude', '') }}" required>
                @if($errors->has('longitude'))
                    <span class="text-danger">{{ $errors->first('longitude') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.trackMember.fields.longitude_helper') }}</span>
            </div>
            <div class="form-group col-lg-5">
                <label class="required" for="location">{{ trans('cruds.trackMember.fields.location') }}</label>
                <input class="form-control {{ $errors->has('location') ? 'is-invalid' : '' }}" type="text" name="location" id="location" value="{{ old('location', '') }}" required>
                @if($errors->has('location'))
                    <span class="text-danger">{{ $errors->first('location') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.trackMember.fields.location_helper') }}</span>
            </div>
            <div class="form-group col-lg-5">
                <label for="time">{{ trans('cruds.trackMember.fields.time') }}</label>
                <input class="form-control {{ $errors->has('time') ? 'is-invalid' : '' }}" type="text" name="time" id="time" value="{{ old('time', '') }}">
                @if($errors->has('time'))
                    <span class="text-danger">{{ $errors->first('time') }}</span>
                @endif
                <span class="help-block">{{ trans('cruds.trackMember.fields.time_helper') }}</span>
            </div>
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