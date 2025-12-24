@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.visit.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.visits.update", [$visit->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="form-group">
                <label for="user">{{ trans('cruds.visit.fields.user') }}</label>
                <input class="form-control {{ $errors->has('user') ? 'is-invalid' : '' }}" type="text" name="user" id="user" value="{{ old('user', $visit->user) }}">
                @if($errors->has('user'))
                    <div class="invalid-feedback">
                        {{ $errors->first('user') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.visit.fields.user_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="latitude">{{ trans('cruds.visit.fields.latitude') }}</label>
                <input class="form-control {{ $errors->has('latitude') ? 'is-invalid' : '' }}" type="text" name="latitude" id="latitude" value="{{ old('latitude', $visit->latitude) }}">
                @if($errors->has('latitude'))
                    <div class="invalid-feedback">
                        {{ $errors->first('latitude') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.visit.fields.latitude_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="longitude">{{ trans('cruds.visit.fields.longitude') }}</label>
                <input class="form-control {{ $errors->has('longitude') ? 'is-invalid' : '' }}" type="text" name="longitude" id="longitude" value="{{ old('longitude', $visit->longitude) }}">
                @if($errors->has('longitude'))
                    <div class="invalid-feedback">
                        {{ $errors->first('longitude') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.visit.fields.longitude_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="location">{{ trans('cruds.visit.fields.location') }}</label>
                <input class="form-control {{ $errors->has('location') ? 'is-invalid' : '' }}" type="text" name="location" id="location" value="{{ old('location', $visit->location) }}">
                @if($errors->has('location'))
                    <div class="invalid-feedback">
                        {{ $errors->first('location') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.visit.fields.location_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="visited_time">{{ trans('cruds.visit.fields.visited_time') }}</label>
                <input class="form-control {{ $errors->has('visited_time') ? 'is-invalid' : '' }}" type="text" name="visited_time" id="visited_time" value="{{ old('visited_time', $visit->visited_time) }}">
                @if($errors->has('visited_time'))
                    <div class="invalid-feedback">
                        {{ $errors->first('visited_time') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.visit.fields.visited_time_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="visited_counter_image">{{ trans('cruds.visit.fields.visited_counter_image') }}</label>
                <div class="needsclick dropzone {{ $errors->has('visited_counter_image') ? 'is-invalid' : '' }}" id="visited_counter_image-dropzone">
                </div>
                @if($errors->has('visited_counter_image'))
                    <div class="invalid-feedback">
                        {{ $errors->first('visited_counter_image') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.visit.fields.visited_counter_image_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="visit_self_image">{{ trans('cruds.visit.fields.visit_self_image') }}</label>
                <div class="needsclick dropzone {{ $errors->has('visit_self_image') ? 'is-invalid' : '' }}" id="visit_self_image-dropzone">
                </div>
                @if($errors->has('visit_self_image'))
                    <div class="invalid-feedback">
                        {{ $errors->first('visit_self_image') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.visit.fields.visit_self_image_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="visited_out_latitude">{{ trans('cruds.visit.fields.visited_out_latitude') }}</label>
                <input class="form-control {{ $errors->has('visited_out_latitude') ? 'is-invalid' : '' }}" type="text" name="visited_out_latitude" id="visited_out_latitude" value="{{ old('visited_out_latitude', $visit->visited_out_latitude) }}">
                @if($errors->has('visited_out_latitude'))
                    <div class="invalid-feedback">
                        {{ $errors->first('visited_out_latitude') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.visit.fields.visited_out_latitude_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="visited_out_longitude">{{ trans('cruds.visit.fields.visited_out_longitude') }}</label>
                <input class="form-control {{ $errors->has('visited_out_longitude') ? 'is-invalid' : '' }}" type="text" name="visited_out_longitude" id="visited_out_longitude" value="{{ old('visited_out_longitude', $visit->visited_out_longitude) }}">
                @if($errors->has('visited_out_longitude'))
                    <div class="invalid-feedback">
                        {{ $errors->first('visited_out_longitude') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.visit.fields.visited_out_longitude_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="visited_out_location">{{ trans('cruds.visit.fields.visited_out_location') }}</label>
                <input class="form-control {{ $errors->has('visited_out_location') ? 'is-invalid' : '' }}" type="text" name="visited_out_location" id="visited_out_location" value="{{ old('visited_out_location', $visit->visited_out_location) }}">
                @if($errors->has('visited_out_location'))
                    <div class="invalid-feedback">
                        {{ $errors->first('visited_out_location') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.visit.fields.visited_out_location_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="visited_out_time">{{ trans('cruds.visit.fields.visited_out_time') }}</label>
                <input class="form-control {{ $errors->has('visited_out_time') ? 'is-invalid' : '' }}" type="text" name="visited_out_time" id="visited_out_time" value="{{ old('visited_out_time', $visit->visited_out_time) }}">
                @if($errors->has('visited_out_time'))
                    <div class="invalid-feedback">
                        {{ $errors->first('visited_out_time') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.visit.fields.visited_out_time_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="visited_out_counter_image">{{ trans('cruds.visit.fields.visited_out_counter_image') }}</label>
                <div class="needsclick dropzone {{ $errors->has('visited_out_counter_image') ? 'is-invalid' : '' }}" id="visited_out_counter_image-dropzone">
                </div>
                @if($errors->has('visited_out_counter_image'))
                    <div class="invalid-feedback">
                        {{ $errors->first('visited_out_counter_image') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.visit.fields.visited_out_counter_image_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="visited_out_self_image">{{ trans('cruds.visit.fields.visited_out_self_image') }}</label>
                <div class="needsclick dropzone {{ $errors->has('visited_out_self_image') ? 'is-invalid' : '' }}" id="visited_out_self_image-dropzone">
                </div>
                @if($errors->has('visited_out_self_image'))
                    <div class="invalid-feedback">
                        {{ $errors->first('visited_out_self_image') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.visit.fields.visited_out_self_image_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="visited_duration">{{ trans('cruds.visit.fields.visited_duration') }}</label>
                <input class="form-control {{ $errors->has('visited_duration') ? 'is-invalid' : '' }}" type="text" name="visited_duration" id="visited_duration" value="{{ old('visited_duration', $visit->visited_duration) }}">
                @if($errors->has('visited_duration'))
                    <div class="invalid-feedback">
                        {{ $errors->first('visited_duration') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.visit.fields.visited_duration_helper') }}</span>
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
    Dropzone.options.visitedCounterImageDropzone = {
    url: '{{ route('admin.visits.storeMedia') }}',
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
      $('form').find('input[name="visited_counter_image"]').remove()
      $('form').append('<input type="hidden" name="visited_counter_image" value="' + response.name + '">')
    },
    removedfile: function (file) {
      file.previewElement.remove()
      if (file.status !== 'error') {
        $('form').find('input[name="visited_counter_image"]').remove()
        this.options.maxFiles = this.options.maxFiles + 1
      }
    },
    init: function () {
@if(isset($visit) && $visit->visited_counter_image)
      var file = {!! json_encode($visit->visited_counter_image) !!}
          this.options.addedfile.call(this, file)
      this.options.thumbnail.call(this, file, file.preview ?? file.preview_url)
      file.previewElement.classList.add('dz-complete')
      $('form').append('<input type="hidden" name="visited_counter_image" value="' + file.file_name + '">')
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
    Dropzone.options.visitSelfImageDropzone = {
    url: '{{ route('admin.visits.storeMedia') }}',
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
      $('form').find('input[name="visit_self_image"]').remove()
      $('form').append('<input type="hidden" name="visit_self_image" value="' + response.name + '">')
    },
    removedfile: function (file) {
      file.previewElement.remove()
      if (file.status !== 'error') {
        $('form').find('input[name="visit_self_image"]').remove()
        this.options.maxFiles = this.options.maxFiles + 1
      }
    },
    init: function () {
@if(isset($visit) && $visit->visit_self_image)
      var file = {!! json_encode($visit->visit_self_image) !!}
          this.options.addedfile.call(this, file)
      this.options.thumbnail.call(this, file, file.preview ?? file.preview_url)
      file.previewElement.classList.add('dz-complete')
      $('form').append('<input type="hidden" name="visit_self_image" value="' + file.file_name + '">')
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
    Dropzone.options.visitedOutCounterImageDropzone = {
    url: '{{ route('admin.visits.storeMedia') }}',
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
      $('form').find('input[name="visited_out_counter_image"]').remove()
      $('form').append('<input type="hidden" name="visited_out_counter_image" value="' + response.name + '">')
    },
    removedfile: function (file) {
      file.previewElement.remove()
      if (file.status !== 'error') {
        $('form').find('input[name="visited_out_counter_image"]').remove()
        this.options.maxFiles = this.options.maxFiles + 1
      }
    },
    init: function () {
@if(isset($visit) && $visit->visited_out_counter_image)
      var file = {!! json_encode($visit->visited_out_counter_image) !!}
          this.options.addedfile.call(this, file)
      this.options.thumbnail.call(this, file, file.preview ?? file.preview_url)
      file.previewElement.classList.add('dz-complete')
      $('form').append('<input type="hidden" name="visited_out_counter_image" value="' + file.file_name + '">')
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
    Dropzone.options.visitedOutSelfImageDropzone = {
    url: '{{ route('admin.visits.storeMedia') }}',
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
      $('form').find('input[name="visited_out_self_image"]').remove()
      $('form').append('<input type="hidden" name="visited_out_self_image" value="' + response.name + '">')
    },
    removedfile: function (file) {
      file.previewElement.remove()
      if (file.status !== 'error') {
        $('form').find('input[name="visited_out_self_image"]').remove()
        this.options.maxFiles = this.options.maxFiles + 1
      }
    },
    init: function () {
@if(isset($visit) && $visit->visited_out_self_image)
      var file = {!! json_encode($visit->visited_out_self_image) !!}
          this.options.addedfile.call(this, file)
      this.options.thumbnail.call(this, file, file.preview ?? file.preview_url)
      file.previewElement.classList.add('dz-complete')
      $('form').append('<input type="hidden" name="visited_out_self_image" value="' + file.file_name + '">')
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