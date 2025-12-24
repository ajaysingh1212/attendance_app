@extends('layouts.admin')

@section('styles')
<style>
    body {
        background: #f8f9fa;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .custom-card {
        border-radius: 12px;
        border: 1px solid #ddd;
        background: #fff;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        animation: fadeIn 0.6s ease-in-out;
    }
    .custom-card-header {
        background: #2c3e50;
        color: #fff;
        padding: 18px;
        font-size: 18px;
        font-weight: 600;
        text-align: center;
        border-radius: 12px 12px 0 0;
    }
    .form-group {
        position: relative;
        margin-bottom: 1.5rem;
    }
    .form-group input, 
    .form-group textarea, 
    .form-group select {
        border: 1px solid #ccc;
        border-radius: 6px;
        width: 100%;
        padding: 12px 12px 12px 12px;
        transition: border-color 0.3s, box-shadow 0.3s;
        background: transparent;
    }
    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        border-color: #2c3e50;
        box-shadow: 0 0 6px rgba(44,62,80,0.3);
        outline: none;
    }
    .form-group label {
        position: absolute;
        top: 12px;
        left: 12px;
        background: #fff;
        padding: 0 5px;
        color: #777;
        font-size: 14px;
        pointer-events: none;
        transition: 0.3s;
    }
    .form-group input:focus + label,
    .form-group input:not(:placeholder-shown) + label,
    .form-group textarea:focus + label,
    .form-group textarea:not(:placeholder-shown) + label,
    .form-group select:focus + label,
    .form-group select:not([value=""]) + label {
        top: -8px;
        left: 10px;
        font-size: 12px;
        color: #2c3e50;
    }
    .btn-generate {
        background: #2c3e50;
        color: #fff;
        font-weight: 500;
        border: none;
        border-radius: 6px;
        padding: 10px 15px;
        margin-left: 8px;
        transition: all 0.3s;
    }
    .btn-generate:hover {
        background: #1a252f;
    }
    .btn-submit {
        background: #27ae60;
        border: none;
        padding: 12px 30px;
        border-radius: 6px;
        font-weight: 600;
        color: #fff;
        transition: 0.3s;
    }
    .btn-submit:hover {
        background: #1e8449;
        transform: translateY(-2px);
    }
    @keyframes fadeIn {
        from {opacity:0; transform: translateY(15px);}
        to {opacity:1; transform: translateY(0);}
    }
</style>
@endsection

@section('content')

<div class="container mt-4">
    <div class="card custom-card">
        <div class="custom-card-header">
            ➕ Add New Product
        </div>

        <div class="card-body p-4">
            <form method="POST" action="{{ route("admin.products.store") }}" enctype="multipart/form-data">
                @csrf
                <div class="row">

                    {{-- Company --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <input type="text" hidden placeholder=" " />
                            <select name="company_id" id="company_id" required value="{{ old('company_id') }}">
                                <option value="">Select Company</option>
                                @foreach($companies as $id => $company)
                                    <option value="{{ $id }}" {{ old('company_id') == $id ? 'selected' : '' }}>
                                        {{ $company }}
                                    </option>
                                @endforeach
                            </select>
                            <label for="company_id">Company</label>
                        </div>
                    </div>

                    {{-- Category --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <select class="form-control select2" name="categories[]" id="categories" multiple>
                                @foreach($categories as $id => $name)
                                    <option value="{{ $id }}" {{ (in_array($id, old('categories', [])) || (isset($product) && $product->categories->contains($id))) ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            <label for="category_id">Category</label>
                        </div>
                    </div>

                    {{-- Tags --}}
                    <div class="col-md-12">
                        <div class="form-group">
                            <select class="select2" name="tags[]" id="tags" multiple>
                                @foreach($tags as $id => $tag)
                                    <option value="{{ $id }}" {{ in_array($id, old('tags', [])) ? 'selected' : '' }}>
                                        {{ $tag }}
                                    </option>
                                @endforeach
                            </select>
                            <label for="tags">Tags</label>
                        </div>
                    </div>

                    {{-- Name --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder=" " required>
                            <label for="name">Product Name</label>
                        </div>
                    </div>

                    {{-- Slug --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <input type="text" name="slug" id="slug" value="{{ old('slug') }}" placeholder=" " required>
                            <label for="slug">Slug</label>
                        </div>
                    </div>

                    {{-- SKU + Generate --}}
                    <div class="col-md-6 d-flex">
                        <div class="form-group flex-grow-1">
                            <input type="text" name="sku" id="sku" value="{{ old('sku') }}" placeholder=" " required>
                            <label for="sku">SKU</label>
                        </div>
                        <button type="button" class="btn-generate align-self-center" onclick="generateCode('sku')">Generate</button>
                    </div>

                    {{-- HSN Code + Generate --}}
                    <div class="col-md-6 d-flex">
                        <div class="form-group flex-grow-1">
                            <input type="text" name="hsn_code" id="hsn_code" value="{{ old('hsn_code') }}" placeholder=" ">
                            <label for="hsn_code">HSN Code</label>
                        </div>
                        <button type="button" class="btn-generate align-self-center" onclick="generateCode('hsn_code')">Generate</button>
                    </div>

                    {{-- Price --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <input type="number" step="0.01" name="price" id="price" value="{{ old('price') }}" placeholder=" " required>
                            <label for="price">Price</label>
                        </div>
                    </div>

                    {{-- Discount --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <input type="number" step="0.01" name="discount" id="discount" value="{{ old('discount') }}" placeholder=" ">
                            <label for="discount">Discount</label>
                        </div>
                    </div>

                    {{-- Quantity --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <input type="number" name="quantity" id="quantity" value="{{ old('quantity', 0) }}" placeholder=" ">
                            <label for="quantity">Quantity</label>
                        </div>
                    </div>

                    {{-- Item Code --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <input type="text" name="item_code" id="item_code" value="{{ old('item_code') }}" placeholder=" ">
                            <label for="item_code">Item Code</label>
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <select name="status" id="status">
                                <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                            <label for="status">Status</label>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="col-md-12">
                        <div class="form-group">
                            <textarea name="description" id="description" rows="3" placeholder=" ">{{ old('description') }}</textarea>
                            <label for="description">Description</label>
                        </div>
                    </div>

                    {{-- Image --}}
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="photo">Product Image</label>
                            <div class="needsclick dropzone" id="photo-dropzone"></div>
                        </div>
                    </div>

                </div>

                <div class="mt-4 text-center">
                    <button class="btn btn-submit" type="submit">
                        <i class="fas fa-save"></i> Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // 🔹 Auto Generate Code (ET-XXXX1234)
    function generateCode(fieldId) {
        const letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        let randomLetters = "";
        for (let i = 0; i < 4; i++) {
            randomLetters += letters.charAt(Math.floor(Math.random() * letters.length));
        }
        let randomNumbers = Math.floor(1000 + Math.random() * 9000); 
        let code = "ET-" + randomLetters + randomNumbers;
        document.getElementById(fieldId).value = code;
    }

    Dropzone.options.photoDropzone = {
        url: '{{ route('admin.products.storeMedia') }}',
        maxFilesize: 2,
        acceptedFiles: '.jpeg,.jpg,.png,.gif',
        maxFiles: 1,
        addRemoveLinks: true,
        headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
        params: { size: 2, width: 4096, height: 4096 },
        success: function (file, response) {
            $('form').find('input[name="photo"]').remove()
            $('form').append('<input type="hidden" name="photo" value="' + response.name + '">')
        },
        removedfile: function (file) {
            file.previewElement.remove()
            if (file.status !== 'error') {
                $('form').find('input[name="photo"]').remove()
                this.options.maxFiles = this.options.maxFiles + 1
            }
        }
    }
</script>
@endsection
