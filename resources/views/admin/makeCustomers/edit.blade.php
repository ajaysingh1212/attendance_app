@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.makeCustomer.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.make-customers.update", [$makeCustomer->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="form-group">
                <label class="required" for="customer_code">{{ trans('cruds.makeCustomer.fields.customer_code') }}</label>
                <input class="form-control {{ $errors->has('customer_code') ? 'is-invalid' : '' }}" type="text" name="customer_code" id="customer_code" value="{{ old('customer_code', $makeCustomer->customer_code) }}" required>
                @if($errors->has('customer_code'))
                    <div class="invalid-feedback">
                        {{ $errors->first('customer_code') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.makeCustomer.fields.customer_code_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="shop_name">{{ trans('cruds.makeCustomer.fields.shop_name') }}</label>
                <input class="form-control {{ $errors->has('shop_name') ? 'is-invalid' : '' }}" type="text" name="shop_name" id="shop_name" value="{{ old('shop_name', $makeCustomer->shop_name) }}" required>
                @if($errors->has('shop_name'))
                    <div class="invalid-feedback">
                        {{ $errors->first('shop_name') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.makeCustomer.fields.shop_name_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="owner_name">{{ trans('cruds.makeCustomer.fields.owner_name') }}</label>
                <input class="form-control {{ $errors->has('owner_name') ? 'is-invalid' : '' }}" type="text" name="owner_name" id="owner_name" value="{{ old('owner_name', $makeCustomer->owner_name) }}" required>
                @if($errors->has('owner_name'))
                    <div class="invalid-feedback">
                        {{ $errors->first('owner_name') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.makeCustomer.fields.owner_name_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="phone_number">{{ trans('cruds.makeCustomer.fields.phone_number') }}</label>
                <input class="form-control {{ $errors->has('phone_number') ? 'is-invalid' : '' }}" type="text" name="phone_number" id="phone_number" value="{{ old('phone_number', $makeCustomer->phone_number) }}" required>
                @if($errors->has('phone_number'))
                    <div class="invalid-feedback">
                        {{ $errors->first('phone_number') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.makeCustomer.fields.phone_number_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="email">{{ trans('cruds.makeCustomer.fields.email') }}</label>
                <input class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" type="text" name="email" id="email" value="{{ old('email', $makeCustomer->email) }}" required>
                @if($errors->has('email'))
                    <div class="invalid-feedback">
                        {{ $errors->first('email') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.makeCustomer.fields.email_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="pincode">{{ trans('cruds.makeCustomer.fields.pincode') }}</label>
                <input class="form-control {{ $errors->has('pincode') ? 'is-invalid' : '' }}" type="text" name="pincode" id="pincode" value="{{ old('pincode', $makeCustomer->pincode) }}">
                @if($errors->has('pincode'))
                    <div class="invalid-feedback">
                        {{ $errors->first('pincode') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.makeCustomer.fields.pincode_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="address_line_1">{{ trans('cruds.makeCustomer.fields.address_line_1') }}</label>
                <textarea class="form-control ckeditor {{ $errors->has('address_line_1') ? 'is-invalid' : '' }}" name="address_line_1" id="address_line_1">{!! old('address_line_1', $makeCustomer->address_line_1) !!}</textarea>
                @if($errors->has('address_line_1'))
                    <div class="invalid-feedback">
                        {{ $errors->first('address_line_1') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.makeCustomer.fields.address_line_1_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="address_line_2">{{ trans('cruds.makeCustomer.fields.address_line_2') }}</label>
                <textarea class="form-control ckeditor {{ $errors->has('address_line_2') ? 'is-invalid' : '' }}" name="address_line_2" id="address_line_2">{!! old('address_line_2', $makeCustomer->address_line_2) !!}</textarea>
                @if($errors->has('address_line_2'))
                    <div class="invalid-feedback">
                        {{ $errors->first('address_line_2') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.makeCustomer.fields.address_line_2_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="area">{{ trans('cruds.makeCustomer.fields.area') }}</label>
                <input class="form-control {{ $errors->has('area') ? 'is-invalid' : '' }}" type="text" name="area" id="area" value="{{ old('area', $makeCustomer->area) }}">
                @if($errors->has('area'))
                    <div class="invalid-feedback">
                        {{ $errors->first('area') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.makeCustomer.fields.area_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="city">{{ trans('cruds.makeCustomer.fields.city') }}</label>
                <input class="form-control {{ $errors->has('city') ? 'is-invalid' : '' }}" type="text" name="city" id="city" value="{{ old('city', $makeCustomer->city) }}">
                @if($errors->has('city'))
                    <div class="invalid-feedback">
                        {{ $errors->first('city') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.makeCustomer.fields.city_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="state">{{ trans('cruds.makeCustomer.fields.state') }}</label>
                <input class="form-control {{ $errors->has('state') ? 'is-invalid' : '' }}" type="text" name="state" id="state" value="{{ old('state', $makeCustomer->state) }}">
                @if($errors->has('state'))
                    <div class="invalid-feedback">
                        {{ $errors->first('state') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.makeCustomer.fields.state_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="country">{{ trans('cruds.makeCustomer.fields.country') }}</label>
                <input class="form-control {{ $errors->has('country') ? 'is-invalid' : '' }}" type="text" name="country" id="country" value="{{ old('country', $makeCustomer->country) }}">
                @if($errors->has('country'))
                    <div class="invalid-feedback">
                        {{ $errors->first('country') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.makeCustomer.fields.country_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="latitude">{{ trans('cruds.makeCustomer.fields.latitude') }}</label>
                <input class="form-control {{ $errors->has('latitude') ? 'is-invalid' : '' }}" type="text" name="latitude" id="latitude" value="{{ old('latitude', $makeCustomer->latitude) }}">
                @if($errors->has('latitude'))
                    <div class="invalid-feedback">
                        {{ $errors->first('latitude') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.makeCustomer.fields.latitude_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="longitude">{{ trans('cruds.makeCustomer.fields.longitude') }}</label>
                <input class="form-control {{ $errors->has('longitude') ? 'is-invalid' : '' }}" type="text" name="longitude" id="longitude" value="{{ old('longitude', $makeCustomer->longitude) }}">
                @if($errors->has('longitude'))
                    <div class="invalid-feedback">
                        {{ $errors->first('longitude') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.makeCustomer.fields.longitude_helper') }}</span>
            </div>
            <div class="form-group">
                <label>{{ trans('cruds.makeCustomer.fields.business_type') }}</label>
                <select class="form-control {{ $errors->has('business_type') ? 'is-invalid' : '' }}" name="business_type" id="business_type">
                    <option value disabled {{ old('business_type', null) === null ? 'selected' : '' }}>{{ trans('global.pleaseSelect') }}</option>
                    @foreach(App\Models\MakeCustomer::BUSINESS_TYPE_SELECT as $key => $label)
                        <option value="{{ $key }}" {{ old('business_type', $makeCustomer->business_type) === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @if($errors->has('business_type'))
                    <div class="invalid-feedback">
                        {{ $errors->first('business_type') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.makeCustomer.fields.business_type_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="shop_category_id">{{ trans('cruds.makeCustomer.fields.shop_category') }}</label>
                <select class="form-control select2 {{ $errors->has('shop_category') ? 'is-invalid' : '' }}" name="shop_category_id" id="shop_category_id">
                    @foreach($shop_categories as $id => $entry)
                        <option value="{{ $id }}" {{ (old('shop_category_id') ? old('shop_category_id') : $makeCustomer->shop_category->id ?? '') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('shop_category'))
                    <div class="invalid-feedback">
                        {{ $errors->first('shop_category') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.makeCustomer.fields.shop_category_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="gst_number">{{ trans('cruds.makeCustomer.fields.gst_number') }}</label>
                <input class="form-control {{ $errors->has('gst_number') ? 'is-invalid' : '' }}" type="text" name="gst_number" id="gst_number" value="{{ old('gst_number', $makeCustomer->gst_number) }}">
                @if($errors->has('gst_number'))
                    <div class="invalid-feedback">
                        {{ $errors->first('gst_number') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.makeCustomer.fields.gst_number_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="license_no">{{ trans('cruds.makeCustomer.fields.license_no') }}</label>
                <input class="form-control {{ $errors->has('license_no') ? 'is-invalid' : '' }}" type="text" name="license_no" id="license_no" value="{{ old('license_no', $makeCustomer->license_no) }}">
                @if($errors->has('license_no'))
                    <div class="invalid-feedback">
                        {{ $errors->first('license_no') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.makeCustomer.fields.license_no_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="payment_terms">{{ trans('cruds.makeCustomer.fields.payment_terms') }}</label>
                <input class="form-control {{ $errors->has('payment_terms') ? 'is-invalid' : '' }}" type="text" name="payment_terms" id="payment_terms" value="{{ old('payment_terms', $makeCustomer->payment_terms) }}">
                @if($errors->has('payment_terms'))
                    <div class="invalid-feedback">
                        {{ $errors->first('payment_terms') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.makeCustomer.fields.payment_terms_helper') }}</span>
            </div>
            <div class="form-group">
                <label>{{ trans('cruds.makeCustomer.fields.preferred_payment_method') }}</label>
                <select class="form-control {{ $errors->has('preferred_payment_method') ? 'is-invalid' : '' }}" name="preferred_payment_method" id="preferred_payment_method">
                    <option value disabled {{ old('preferred_payment_method', null) === null ? 'selected' : '' }}>{{ trans('global.pleaseSelect') }}</option>
                    @foreach(App\Models\MakeCustomer::PREFERRED_PAYMENT_METHOD_SELECT as $key => $label)
                        <option value="{{ $key }}" {{ old('preferred_payment_method', $makeCustomer->preferred_payment_method) === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @if($errors->has('preferred_payment_method'))
                    <div class="invalid-feedback">
                        {{ $errors->first('preferred_payment_method') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.makeCustomer.fields.preferred_payment_method_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="bank_name">{{ trans('cruds.makeCustomer.fields.bank_name') }}</label>
                <input class="form-control {{ $errors->has('bank_name') ? 'is-invalid' : '' }}" type="text" name="bank_name" id="bank_name" value="{{ old('bank_name', $makeCustomer->bank_name) }}">
                @if($errors->has('bank_name'))
                    <div class="invalid-feedback">
                        {{ $errors->first('bank_name') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.makeCustomer.fields.bank_name_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="ifsc_code">{{ trans('cruds.makeCustomer.fields.ifsc_code') }}</label>
                <input class="form-control {{ $errors->has('ifsc_code') ? 'is-invalid' : '' }}" type="text" name="ifsc_code" id="ifsc_code" value="{{ old('ifsc_code', $makeCustomer->ifsc_code) }}">
                @if($errors->has('ifsc_code'))
                    <div class="invalid-feedback">
                        {{ $errors->first('ifsc_code') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.makeCustomer.fields.ifsc_code_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="account_no">{{ trans('cruds.makeCustomer.fields.account_no') }}</label>
                <input class="form-control {{ $errors->has('account_no') ? 'is-invalid' : '' }}" type="text" name="account_no" id="account_no" value="{{ old('account_no', $makeCustomer->account_no) }}">
                @if($errors->has('account_no'))
                    <div class="invalid-feedback">
                        {{ $errors->first('account_no') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.makeCustomer.fields.account_no_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="shop_image">{{ trans('cruds.makeCustomer.fields.shop_image') }}</label>
                <div class="needsclick dropzone {{ $errors->has('shop_image') ? 'is-invalid' : '' }}" id="shop_image-dropzone">
                </div>
                @if($errors->has('shop_image'))
                    <div class="invalid-feedback">
                        {{ $errors->first('shop_image') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.makeCustomer.fields.shop_image_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="id_proof">{{ trans('cruds.makeCustomer.fields.id_proof') }}</label>
                <div class="needsclick dropzone {{ $errors->has('id_proof') ? 'is-invalid' : '' }}" id="id_proof-dropzone">
                </div>
                @if($errors->has('id_proof'))
                    <div class="invalid-feedback">
                        {{ $errors->first('id_proof') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.makeCustomer.fields.id_proof_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="gst_certificate">{{ trans('cruds.makeCustomer.fields.gst_certificate') }}</label>
                <div class="needsclick dropzone {{ $errors->has('gst_certificate') ? 'is-invalid' : '' }}" id="gst_certificate-dropzone">
                </div>
                @if($errors->has('gst_certificate'))
                    <div class="invalid-feedback">
                        {{ $errors->first('gst_certificate') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.makeCustomer.fields.gst_certificate_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="notes">{{ trans('cruds.makeCustomer.fields.notes') }}</label>
                <textarea class="form-control ckeditor {{ $errors->has('notes') ? 'is-invalid' : '' }}" name="notes" id="notes">{!! old('notes', $makeCustomer->notes) !!}</textarea>
                @if($errors->has('notes'))
                    <div class="invalid-feedback">
                        {{ $errors->first('notes') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.makeCustomer.fields.notes_helper') }}</span>
            </div>
            <div class="form-group">
                <label>{{ trans('cruds.makeCustomer.fields.status') }}</label>
                <select class="form-control {{ $errors->has('status') ? 'is-invalid' : '' }}" name="status" id="status">
                    <option value disabled {{ old('status', null) === null ? 'selected' : '' }}>{{ trans('global.pleaseSelect') }}</option>
                    @foreach(App\Models\MakeCustomer::STATUS_SELECT as $key => $label)
                        <option value="{{ $key }}" {{ old('status', $makeCustomer->status) === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @if($errors->has('status'))
                    <div class="invalid-feedback">
                        {{ $errors->first('status') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.makeCustomer.fields.status_helper') }}</span>
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
    $(document).ready(function () {
  function SimpleUploadAdapter(editor) {
    editor.plugins.get('FileRepository').createUploadAdapter = function(loader) {
      return {
        upload: function() {
          return loader.file
            .then(function (file) {
              return new Promise(function(resolve, reject) {
                // Init request
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '{{ route('admin.make-customers.storeCKEditorImages') }}', true);
                xhr.setRequestHeader('x-csrf-token', window._token);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.responseType = 'json';

                // Init listeners
                var genericErrorText = `Couldn't upload file: ${ file.name }.`;
                xhr.addEventListener('error', function() { reject(genericErrorText) });
                xhr.addEventListener('abort', function() { reject() });
                xhr.addEventListener('load', function() {
                  var response = xhr.response;

                  if (!response || xhr.status !== 201) {
                    return reject(response && response.message ? `${genericErrorText}\n${xhr.status} ${response.message}` : `${genericErrorText}\n ${xhr.status} ${xhr.statusText}`);
                  }

                  $('form').append('<input type="hidden" name="ck-media[]" value="' + response.id + '">');

                  resolve({ default: response.url });
                });

                if (xhr.upload) {
                  xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                      loader.uploadTotal = e.total;
                      loader.uploaded = e.loaded;
                    }
                  });
                }

                // Send request
                var data = new FormData();
                data.append('upload', file);
                data.append('crud_id', '{{ $makeCustomer->id ?? 0 }}');
                xhr.send(data);
              });
            })
        }
      };
    }
  }

  var allEditors = document.querySelectorAll('.ckeditor');
  for (var i = 0; i < allEditors.length; ++i) {
    ClassicEditor.create(
      allEditors[i], {
        extraPlugins: [SimpleUploadAdapter]
      }
    );
  }
});
</script>

<script>
    Dropzone.options.shopImageDropzone = {
    url: '{{ route('admin.make-customers.storeMedia') }}',
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
      $('form').find('input[name="shop_image"]').remove()
      $('form').append('<input type="hidden" name="shop_image" value="' + response.name + '">')
    },
    removedfile: function (file) {
      file.previewElement.remove()
      if (file.status !== 'error') {
        $('form').find('input[name="shop_image"]').remove()
        this.options.maxFiles = this.options.maxFiles + 1
      }
    },
    init: function () {
@if(isset($makeCustomer) && $makeCustomer->shop_image)
      var file = {!! json_encode($makeCustomer->shop_image) !!}
          this.options.addedfile.call(this, file)
      this.options.thumbnail.call(this, file, file.preview ?? file.preview_url)
      file.previewElement.classList.add('dz-complete')
      $('form').append('<input type="hidden" name="shop_image" value="' + file.file_name + '">')
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
    Dropzone.options.idProofDropzone = {
    url: '{{ route('admin.make-customers.storeMedia') }}',
    maxFilesize: 20, // MB
    maxFiles: 1,
    addRemoveLinks: true,
    headers: {
      'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },
    params: {
      size: 20
    },
    success: function (file, response) {
      $('form').find('input[name="id_proof"]').remove()
      $('form').append('<input type="hidden" name="id_proof" value="' + response.name + '">')
    },
    removedfile: function (file) {
      file.previewElement.remove()
      if (file.status !== 'error') {
        $('form').find('input[name="id_proof"]').remove()
        this.options.maxFiles = this.options.maxFiles + 1
      }
    },
    init: function () {
@if(isset($makeCustomer) && $makeCustomer->id_proof)
      var file = {!! json_encode($makeCustomer->id_proof) !!}
          this.options.addedfile.call(this, file)
      file.previewElement.classList.add('dz-complete')
      $('form').append('<input type="hidden" name="id_proof" value="' + file.file_name + '">')
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
    Dropzone.options.gstCertificateDropzone = {
    url: '{{ route('admin.make-customers.storeMedia') }}',
    maxFilesize: 20, // MB
    maxFiles: 1,
    addRemoveLinks: true,
    headers: {
      'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },
    params: {
      size: 20
    },
    success: function (file, response) {
      $('form').find('input[name="gst_certificate"]').remove()
      $('form').append('<input type="hidden" name="gst_certificate" value="' + response.name + '">')
    },
    removedfile: function (file) {
      file.previewElement.remove()
      if (file.status !== 'error') {
        $('form').find('input[name="gst_certificate"]').remove()
        this.options.maxFiles = this.options.maxFiles + 1
      }
    },
    init: function () {
@if(isset($makeCustomer) && $makeCustomer->gst_certificate)
      var file = {!! json_encode($makeCustomer->gst_certificate) !!}
          this.options.addedfile.call(this, file)
      file.previewElement.classList.add('dz-complete')
      $('form').append('<input type="hidden" name="gst_certificate" value="' + file.file_name + '">')
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