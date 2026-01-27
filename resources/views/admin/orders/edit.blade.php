@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.order.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.orders.update", [$order->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="form-group">
                <label class="required" for="select_products">{{ trans('cruds.order.fields.select_product') }}</label>
                <div style="padding-bottom: 4px">
                    <span class="btn btn-info btn-xs select-all" style="border-radius: 0">{{ trans('global.select_all') }}</span>
                    <span class="btn btn-info btn-xs deselect-all" style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                </div>
                <select class="form-control select2 {{ $errors->has('select_products') ? 'is-invalid' : '' }}" name="select_products[]" id="select_products" multiple required>
                    @foreach($select_products as $id => $select_product)
                        <option value="{{ $id }}" {{ (in_array($id, old('select_products', [])) || $order->select_products->contains($id)) ? 'selected' : '' }}>{{ $select_product }}</option>
                    @endforeach
                </select>
                @if($errors->has('select_products'))
                    <div class="invalid-feedback">
                        {{ $errors->first('select_products') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.order.fields.select_product_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="select_customer_id">{{ trans('cruds.order.fields.select_customer') }}</label>
                <select class="form-control select2 {{ $errors->has('select_customer') ? 'is-invalid' : '' }}" name="select_customer_id" id="select_customer_id" required>
                    @foreach($select_customers as $id => $entry)
                        <option value="{{ $id }}" {{ (old('select_customer_id') ? old('select_customer_id') : $order->select_customer->id ?? '') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                    @endforeach
                </select>
                @if($errors->has('select_customer'))
                    <div class="invalid-feedback">
                        {{ $errors->first('select_customer') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.order.fields.select_customer_helper') }}</span>
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