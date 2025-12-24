@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.attendanceDetail.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.attendance-details.update", [$attendanceDetail->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="form-group">
                <label for="user_id">{{ trans('cruds.attendanceDetail.fields.user') }}</label>
                <select class="form-control select2 {{ $errors->has('user') ? 'is-invalid' : '' }}" name="user_id" id="user_id">
                    @foreach($users as $id => $entry)
                        <option value="{{ $id }}" {{ (old('user_id') ? old('user_id') : $attendanceDetail->user->id ?? '') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('user'))
                    <div class="invalid-feedback">
                        {{ $errors->first('user') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.attendanceDetail.fields.user_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="punch_in_time">{{ trans('cruds.attendanceDetail.fields.punch_in_time') }}</label>
                <input class="form-control {{ $errors->has('punch_in_time') ? 'is-invalid' : '' }}" type="text" name="punch_in_time" id="punch_in_time" value="{{ old('punch_in_time', $attendanceDetail->punch_in_time) }}">
                @if($errors->has('punch_in_time'))
                    <div class="invalid-feedback">
                        {{ $errors->first('punch_in_time') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.attendanceDetail.fields.punch_in_time_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="punch_in_latitude">{{ trans('cruds.attendanceDetail.fields.punch_in_latitude') }}</label>
                <input class="form-control {{ $errors->has('punch_in_latitude') ? 'is-invalid' : '' }}" type="text" name="punch_in_latitude" id="punch_in_latitude" value="{{ old('punch_in_latitude', $attendanceDetail->punch_in_latitude) }}">
                @if($errors->has('punch_in_latitude'))
                    <div class="invalid-feedback">
                        {{ $errors->first('punch_in_latitude') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.attendanceDetail.fields.punch_in_latitude_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="punch_in_longitude">{{ trans('cruds.attendanceDetail.fields.punch_in_longitude') }}</label>
                <input class="form-control {{ $errors->has('punch_in_longitude') ? 'is-invalid' : '' }}" type="text" name="punch_in_longitude" id="punch_in_longitude" value="{{ old('punch_in_longitude', $attendanceDetail->punch_in_longitude) }}">
                @if($errors->has('punch_in_longitude'))
                    <div class="invalid-feedback">
                        {{ $errors->first('punch_in_longitude') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.attendanceDetail.fields.punch_in_longitude_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="punch_in_location">{{ trans('cruds.attendanceDetail.fields.punch_in_location') }}</label>
                <input class="form-control {{ $errors->has('punch_in_location') ? 'is-invalid' : '' }}" type="text" name="punch_in_location" id="punch_in_location" value="{{ old('punch_in_location', $attendanceDetail->punch_in_location) }}">
                @if($errors->has('punch_in_location'))
                    <div class="invalid-feedback">
                        {{ $errors->first('punch_in_location') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.attendanceDetail.fields.punch_in_location_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="punch_in_image">{{ trans('cruds.attendanceDetail.fields.punch_in_image') }}</label>
                <div class="needsclick dropzone {{ $errors->has('punch_in_image') ? 'is-invalid' : '' }}" id="punch_in_image-dropzone">
                </div>
                @if($errors->has('punch_in_image'))
                    <div class="invalid-feedback">
                        {{ $errors->first('punch_in_image') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.attendanceDetail.fields.punch_in_image_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="punch_out_time">{{ trans('cruds.attendanceDetail.fields.punch_out_time') }}</label>
                <input class="form-control {{ $errors->has('punch_out_time') ? 'is-invalid' : '' }}" type="text" name="punch_out_time" id="punch_out_time" value="{{ old('punch_out_time', $attendanceDetail->punch_out_time) }}">
                @if($errors->has('punch_out_time'))
                    <div class="invalid-feedback">
                        {{ $errors->first('punch_out_time') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.attendanceDetail.fields.punch_out_time_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="punch_out_latitude">{{ trans('cruds.attendanceDetail.fields.punch_out_latitude') }}</label>
                <input class="form-control {{ $errors->has('punch_out_latitude') ? 'is-invalid' : '' }}" type="text" name="punch_out_latitude" id="punch_out_latitude" value="{{ old('punch_out_latitude', $attendanceDetail->punch_out_latitude) }}">
                @if($errors->has('punch_out_latitude'))
                    <div class="invalid-feedback">
                        {{ $errors->first('punch_out_latitude') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.attendanceDetail.fields.punch_out_latitude_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="punch_out_longitude">{{ trans('cruds.attendanceDetail.fields.punch_out_longitude') }}</label>
                <input class="form-control {{ $errors->has('punch_out_longitude') ? 'is-invalid' : '' }}" type="text" name="punch_out_longitude" id="punch_out_longitude" value="{{ old('punch_out_longitude', $attendanceDetail->punch_out_longitude) }}">
                @if($errors->has('punch_out_longitude'))
                    <div class="invalid-feedback">
                        {{ $errors->first('punch_out_longitude') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.attendanceDetail.fields.punch_out_longitude_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="punch_out_location">{{ trans('cruds.attendanceDetail.fields.punch_out_location') }}</label>
                <input class="form-control {{ $errors->has('punch_out_location') ? 'is-invalid' : '' }}" type="text" name="punch_out_location" id="punch_out_location" value="{{ old('punch_out_location', $attendanceDetail->punch_out_location) }}">
                @if($errors->has('punch_out_location'))
                    <div class="invalid-feedback">
                        {{ $errors->first('punch_out_location') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.attendanceDetail.fields.punch_out_location_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="punch_out_image">{{ trans('cruds.attendanceDetail.fields.punch_out_image') }}</label>
                <div class="needsclick dropzone {{ $errors->has('punch_out_image') ? 'is-invalid' : '' }}" id="punch_out_image-dropzone">
                </div>
                @if($errors->has('punch_out_image'))
                    <div class="invalid-feedback">
                        {{ $errors->first('punch_out_image') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.attendanceDetail.fields.punch_out_image_helper') }}</span>
            </div>
            <div class="form-group">
                <label>{{ trans('cruds.attendanceDetail.fields.status') }}</label>
                <select class="form-control {{ $errors->has('status') ? 'is-invalid' : '' }}" name="status" id="status">
                    <option value disabled {{ old('status', null) === null ? 'selected' : '' }}>{{ trans('global.pleaseSelect') }}</option>
                    @foreach(App\Models\AttendanceDetail::STATUS_SELECT as $key => $label)
                        <option value="{{ $key }}" {{ old('status', $attendanceDetail->status) === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @if($errors->has('status'))
                    <div class="invalid-feedback">
                        {{ $errors->first('status') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.attendanceDetail.fields.status_helper') }}</span>
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

@section('scripts')
<script>
    Dropzone.options.punchInImageDropzone = {
    url: '{{ route('admin.attendance-details.storeMedia') }}',
    maxFilesize: 20, // MB
    acceptedFiles: '.jpeg,.jpg,.png,.gif',
    maxFiles: 1,
    addRemoveLinks: true,
    headers: {
      'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },
    params: {
      size: 20,
      width: 4096,
      height: 4096
    },
    success: function (file, response) {
      $('form').find('input[name="punch_in_image"]').remove()
      $('form').append('<input type="hidden" name="punch_in_image" value="' + response.name + '">')
    },
    removedfile: function (file) {
      file.previewElement.remove()
      if (file.status !== 'error') {
        $('form').find('input[name="punch_in_image"]').remove()
        this.options.maxFiles = this.options.maxFiles + 1
      }
    },
    init: function () {
@if(isset($attendanceDetail) && $attendanceDetail->punch_in_image)
      var file = {!! json_encode($attendanceDetail->punch_in_image) !!}
          this.options.addedfile.call(this, file)
      this.options.thumbnail.call(this, file, file.preview ?? file.preview_url)
      file.previewElement.classList.add('dz-complete')
      $('form').append('<input type="hidden" name="punch_in_image" value="' + file.file_name + '">')
      this.options.maxFiles = this.options.maxFiles - 1
@endif
    },
    error: function (file, response) {
        if ($.type(response) === 'string') {
            var message = response //dropzone sends it's own error messages in string
        } else {
            var message = response.errors.file
        }
        file.previewElement.classList.add('dz-error')
        _ref = file.previewElement.querySelectorAll('[data-dz-errormessage]')
        _results = []
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            node = _ref[_i]
            _results.push(node.textContent = message)
        }

        return _results
    }
}

</script>
<script>
    Dropzone.options.punchOutImageDropzone = {
    url: '{{ route('admin.attendance-details.storeMedia') }}',
    maxFilesize: 20, // MB
    acceptedFiles: '.jpeg,.jpg,.png,.gif',
    maxFiles: 1,
    addRemoveLinks: true,
    headers: {
      'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },
    params: {
      size: 20,
      width: 4096,
      height: 4096
    },
    success: function (file, response) {
      $('form').find('input[name="punch_out_image"]').remove()
      $('form').append('<input type="hidden" name="punch_out_image" value="' + response.name + '">')
    },
    removedfile: function (file) {
      file.previewElement.remove()
      if (file.status !== 'error') {
        $('form').find('input[name="punch_out_image"]').remove()
        this.options.maxFiles = this.options.maxFiles + 1
      }
    },
    init: function () {
@if(isset($attendanceDetail) && $attendanceDetail->punch_out_image)
      var file = {!! json_encode($attendanceDetail->punch_out_image) !!}
          this.options.addedfile.call(this, file)
      this.options.thumbnail.call(this, file, file.preview ?? file.preview_url)
      file.previewElement.classList.add('dz-complete')
      $('form').append('<input type="hidden" name="punch_out_image" value="' + file.file_name + '">')
      this.options.maxFiles = this.options.maxFiles - 1
@endif
    },
    error: function (file, response) {
        if ($.type(response) === 'string') {
            var message = response //dropzone sends it's own error messages in string
        } else {
            var message = response.errors.file
        }
        file.previewElement.classList.add('dz-error')
        _ref = file.previewElement.querySelectorAll('[data-dz-errormessage]')
        _results = []
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            node = _ref[_i]
            _results.push(node.textContent = message)
        }

        return _results
    }
}

</script>
@endsection