@extends('layouts.admin')
@section('content')

<div class="container-fluid">
    <form method="POST" action="{{ route("admin.make-customers.store") }}" enctype="multipart/form-data">
        @csrf
        <div class="row">

            {{-- Card 1: Basic Details --}}
            <div class="col-md-6">
                <div class="card shadow-lg border-0 mb-4">
                    <div class="card-header bg-gradient-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user mr-2"></i> Basic Details</h5>
                    </div>
                    <div class="card-body">
                        {{-- Customer Code --}}
                        <div class="form-group">
                            <label class="required">Customer Code</label>
                            <div class="input-group">
                                <input class="form-control" type="text" name="customer_code" id="customer_code" readonly required>
                                <div class="input-group-append">
                                    <button class="btn btn-success" type="button" onclick="generateCustomerCode()">
                                        <i class="fas fa-random"></i> Generate
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="required">Shop Name</label>
                            <input class="form-control" type="text" name="shop_name" id="shop_name" required>
                        </div>

                        <div class="form-group">
                            <label class="required">Owner Name</label>
                            <input class="form-control" type="text" name="owner_name" id="owner_name" required>
                        </div>

                        <div class="form-group">
                            <label class="required">Phone Number</label>
                            <input class="form-control" type="text" name="phone_number" id="phone_number" required>
                        </div>

                        <div class="form-group">
                            <label class="required">Email</label>
                            <input class="form-control" type="email" name="email" id="email" required>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 2: Address Details --}}
            <div class="col-md-6">
                <div class="card shadow-lg border-0 mb-4">
                    <div class="card-header bg-gradient-info text-white">
                        <h5 class="mb-0"><i class="fas fa-map-marker-alt mr-2"></i> Address Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Pincode</label>
                            <input class="form-control" type="text" name="pincode" id="pincode">
                        </div>

                        <div class="form-group">
                            <label>Address Line 1</label>
                            <textarea class="form-control ckeditor" name="address_line_1" id="address_line_1"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Address Line 2</label>
                            <textarea class="form-control ckeditor" name="address_line_2" id="address_line_2"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Area</label>
                            <input class="form-control" type="text" name="area" id="area">
                        </div>

                        <div class="form-group">
                            <label>District</label>
                            <input class="form-control" type="text" name="district" id="district">
                        </div>

                        <div class="form-group">
                            <label>State</label>
                            <input class="form-control" type="text" name="state" id="state">
                        </div>

                        <div class="form-group">
                            <label>Country</label>
                            <input class="form-control" type="text" name="country" id="country">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 3: Location --}}
            <div class="col-md-12">
                <div class="card shadow-lg border-0 mb-4">
                    <div class="card-header bg-gradient-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-map mr-2"></i> Location</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label>Latitude</label>
                                <input class="form-control" type="text" name="latitude" id="latitude">
                            </div>
                            <div class="col-md-6">
                                <label>Longitude</label>
                                <input class="form-control" type="text" name="longitude" id="longitude">
                            </div>
                        </div>
                        <div id="map" style="height: 400px;" class="mt-3 rounded border"></div>
                    </div>
                </div>
            </div>

            {{-- Card 4: Business & Bank Details --}}
            <div class="col-md-12">
                <div class="card shadow-lg border-0 mb-4">
                    <div class="card-header bg-gradient-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-briefcase mr-2"></i> Business & Bank Details</h5>
                    </div>
                    <div class="card-body row">
                        <div class="form-group col-md-4">
                            <label>Business Type</label>
                            <select class="form-control" name="business_type" id="business_type">
                                <option value disabled selected>--Choose--</option>
                                @foreach(App\Models\MakeCustomer::BUSINESS_TYPE_SELECT as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Shop Category</label>
                            <select class="form-control select2" name="shop_category_id" id="shop_category_id">
                                @foreach($shop_categories as $id => $entry)
                                    <option value="{{ $id }}">{{ $entry }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label>GST Number</label>
                            <input class="form-control" type="text" name="gst_number" id="gst_number">
                        </div>

                        <div class="form-group col-md-4">
                            <label>License No</label>
                            <input class="form-control" type="text" name="license_no" id="license_no">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Payment Terms</label>
                            <input class="form-control" type="text" name="payment_terms" id="payment_terms">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Preferred Payment Method</label>
                            <select class="form-control" name="preferred_payment_method" id="preferred_payment_method">
                                <option value disabled selected>--Choose--</option>
                                @foreach(App\Models\MakeCustomer::PREFERRED_PAYMENT_METHOD_SELECT as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Bank Name</label>
                            <input class="form-control" type="text" name="bank_name" id="bank_name">
                        </div>

                        <div class="form-group col-md-4">
                            <label>IFSC Code</label>
                            <input class="form-control" type="text" name="ifsc_code" id="ifsc_code">
                        </div>



                        <div class="form-group col-md-4">
                            <label>Account No</label>
                            <input class="form-control" type="text" name="account_no" id="account_no">
                        </div>
                        {{-- CKEditor for Bank Address --}}
                        <div class="form-group col-md-12">
                            <label>Bank Full Address</label>
                            <textarea class="form-control ckeditor" name="bank_full_address" id="bank_full_address"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 5: Uploads & Notes --}}
            <div class="col-md-12">
                <div class="card shadow-lg border-0 mb-4">
                    <div class="card-header bg-gradient-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-file-upload mr-2"></i> Uploads & Notes</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Shop Image</label>
                            <div class="dropzone" id="shop_image-dropzone"></div>
                        </div>
                        <div class="form-group">
                            <label>ID Proof</label>
                            <div class="dropzone" id="id_proof-dropzone"></div>
                        </div>
                        <div class="form-group">
                            <label>GST Certificate</label>
                            <div class="dropzone" id="gst_certificate-dropzone"></div>
                        </div>
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea class="form-control ckeditor" name="notes" id="notes"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" name="status" id="status">
                                <option value disabled selected>--Choose--</option>
                                @foreach(App\Models\MakeCustomer::STATUS_SELECT as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="btn btn-lg btn-danger px-5" type="submit">
                            <i class="fas fa-save"></i> Save
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection

@section('scripts')
{{-- CKEditor 5 --}}
<script src="https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js"></script>


<script>
window.editors = {};
document.querySelectorAll('.ckeditor').forEach((el) => {
    ClassicEditor.create(el).then(editor => {
        window.editors[el.id] = editor;
    }).catch(error => { console.error(error); });
});

// Generate Customer Code
function generateCustomerCode() {
    let letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    let code = "";
    for (let i = 0; i < 4; i++) code += letters.charAt(Math.floor(Math.random() * letters.length));
    for (let i = 0; i < 6; i++) code += Math.floor(Math.random() * 10);
    document.getElementById("customer_code").value = code;
}

// Google Map Init
function initMap() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            let lat = position.coords.latitude;
            let lng = position.coords.longitude;
            document.getElementById("latitude").value = lat;
            document.getElementById("longitude").value = lng;
            let map = new google.maps.Map(document.getElementById("map"), { center: { lat, lng }, zoom: 15 });
            new google.maps.Marker({ position: { lat, lng }, map: map });
        });
    }
}

// Pincode Autofill
document.getElementById("pincode").addEventListener("blur", function () {
    let pincode = this.value.trim();
    if (pincode.length === 6) {
        fetch(`https://api.postalpincode.in/pincode/${pincode}`)
            .then(response => response.json())
            .then(data => {
                if (data && data[0].Status === "Success" && data[0].PostOffice.length > 0) {
                    let po = data[0].PostOffice[0];
                    let area = po.Name || "";
                    let district = po.District || "";
                    let state = po.State || "";
                    let country = "India";

                    document.getElementById("area").value = area;
                    document.getElementById("district").value = district;
                    document.getElementById("state").value = state;
                    document.getElementById("country").value = country;

                    let fullAddress = `${area}, ${district}, ${state}, ${country} - ${pincode}`;
                    document.getElementById("address_line_1").value = fullAddress;
                    document.getElementById("address_line_2").value = fullAddress;

                    if (window.editors['address_line_1']) window.editors['address_line_1'].setData(fullAddress);
                    if (window.editors['address_line_2']) window.editors['address_line_2'].setData(fullAddress);
                }
            });
    }
});



// IFSC Autofill
document.getElementById("ifsc_code").addEventListener("blur", function () {
    let ifsc = this.value.trim().toUpperCase();
    if (ifsc.length === 11) {
        fetch(`https://ifsc.razorpay.com/${ifsc}`)
            .then(res => res.json())
            .then(data => {
                if (!data || data === "Not Found") {
                    alert("Invalid IFSC Code");
                } else {
                    let address = `${data.BANK}, ${data.BRANCH}, ${data.ADDRESS}, ${data.CITY}, ${data.STATE}`;
                    if (window.editors['bank_full_address']) {
                        window.editors['bank_full_address'].setData(address);
                    } else {
                        document.getElementById("bank_full_address").value = address;
                    }
                }
            })
            .catch(err => console.error("IFSC API error:", err));
    }
});
</script>
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
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBgRXfXiK8KHfSnKtunSIpGpKNmLNGNUzM&callback=initMap"></script>
@endsection
