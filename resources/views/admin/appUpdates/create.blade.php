@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('cruds.appUpdate.title_singular') }}
    </div>

    <div class="card-body">
        <form id="appUpdateForm" method="POST" action="{{ route('admin.app-updates.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="required" for="version">{{ trans('cruds.appUpdate.fields.version') }}</label>
                <input class="form-control" type="text" name="version" id="version" value="{{ old('version', '') }}" required>
            </div>

            <div class="form-group">
                <label class="required" for="heading">{{ trans('cruds.appUpdate.fields.heading') }}</label>
                <input class="form-control" type="text" name="heading" id="heading" value="{{ old('heading', '') }}" required>
            </div>

            <div class="form-group">
                <label for="content">{{ trans('cruds.appUpdate.fields.content') }}</label>
                <textarea class="form-control ckeditor" name="content" id="content">{!! old('content') !!}</textarea>
            </div>

            <div class="form-group">
                <label class="required" for="appFile">Upload APK (Max 100 MB)</label>
                <input type="file" class="form-control" id="appFile" accept=".apk" name="app_file">
                <progress id="uploadProgress" value="0" max="100" style="width:100%; margin-top:10px; display:none;"></progress>
                <div id="uploadStatus" class="mt-2 text-muted"></div>
            </div>

            <!-- Hidden field to store uploaded file ID -->
            <input type="hidden" name="app" id="appHidden">

            <div class="form-group mt-3">
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
document.addEventListener("DOMContentLoaded", function () {
    const fileInput = document.getElementById("appFile");
    const progressBar = document.getElementById("uploadProgress");
    const statusText = document.getElementById("uploadStatus");
    const hiddenInput = document.getElementById("appHidden");

    fileInput.addEventListener("change", function () {
        const file = fileInput.files[0];
        if (!file) return;

        // Validate size (100 MB)
        if (file.size > 100 * 1024 * 1024) {
            alert("File size exceeds 100 MB limit.");
            fileInput.value = "";
            return;
        }

        const formData = new FormData();
        formData.append("file", file);
        formData.append("_token", "{{ csrf_token() }}");

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "{{ route('admin.app-updates.storeMedia') }}", true);

        xhr.upload.addEventListener("loadstart", function () {
            progressBar.style.display = "block";
            progressBar.value = 0;
            statusText.innerText = "Uploading...";
        });

        xhr.upload.addEventListener("progress", function (e) {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                progressBar.value = percent;
                statusText.innerText = `Uploading... ${percent}%`;
            }
        });

        xhr.onload = function () {
            if (xhr.status === 201) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    hiddenInput.value = response.id; // save media ID
                    statusText.innerText = "Upload complete ✅";
                } catch (err) {
                    statusText.innerText = "Upload failed ❌ (Invalid JSON)";
                }
            } else {
                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    if(errorResponse.errors && errorResponse.errors.file){
                        statusText.innerText = "Upload failed ❌: " + errorResponse.errors.file[0];
                    } else if(errorResponse.message){
                        statusText.innerText = "Upload failed ❌: " + errorResponse.message;
                    } else {
                        statusText.innerText = "Upload failed ❌";
                    }
                } catch (err) {
                    statusText.innerText = "Upload failed ❌";
                }
            }
        };

        xhr.onerror = function () {
            statusText.innerText = "Upload failed ❌";
        };

        xhr.send(formData);
    });
});
</script>
@endsection
