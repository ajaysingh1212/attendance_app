@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.makeCustomer.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.make-customers.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.id') }}
                        </th>
                        <td>
                            {{ $makeCustomer->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.customer_code') }}
                        </th>
                        <td>
                            {{ $makeCustomer->customer_code }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.shop_name') }}
                        </th>
                        <td>
                            {{ $makeCustomer->shop_name }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.owner_name') }}
                        </th>
                        <td>
                            {{ $makeCustomer->owner_name }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.phone_number') }}
                        </th>
                        <td>
                            {{ $makeCustomer->phone_number }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.email') }}
                        </th>
                        <td>
                            {{ $makeCustomer->email }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.pincode') }}
                        </th>
                        <td>
                            {{ $makeCustomer->pincode }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.address_line_1') }}
                        </th>
                        <td>
                            {!! $makeCustomer->address_line_1 !!}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.address_line_2') }}
                        </th>
                        <td>
                            {!! $makeCustomer->address_line_2 !!}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.area') }}
                        </th>
                        <td>
                            {{ $makeCustomer->area }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.city') }}
                        </th>
                        <td>
                            {{ $makeCustomer->city }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.state') }}
                        </th>
                        <td>
                            {{ $makeCustomer->state }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.country') }}
                        </th>
                        <td>
                            {{ $makeCustomer->country }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.latitude') }}
                        </th>
                        <td>
                            {{ $makeCustomer->latitude }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.longitude') }}
                        </th>
                        <td>
                            {{ $makeCustomer->longitude }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.business_type') }}
                        </th>
                        <td>
                            {{ App\Models\MakeCustomer::BUSINESS_TYPE_SELECT[$makeCustomer->business_type] ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.shop_category') }}
                        </th>
                        <td>
                            {{ $makeCustomer->shop_category->name ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.gst_number') }}
                        </th>
                        <td>
                            {{ $makeCustomer->gst_number }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.license_no') }}
                        </th>
                        <td>
                            {{ $makeCustomer->license_no }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.payment_terms') }}
                        </th>
                        <td>
                            {{ $makeCustomer->payment_terms }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.preferred_payment_method') }}
                        </th>
                        <td>
                            {{ App\Models\MakeCustomer::PREFERRED_PAYMENT_METHOD_SELECT[$makeCustomer->preferred_payment_method] ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.bank_name') }}
                        </th>
                        <td>
                            {{ $makeCustomer->bank_name }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.ifsc_code') }}
                        </th>
                        <td>
                            {{ $makeCustomer->ifsc_code }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.account_no') }}
                        </th>
                        <td>
                            {{ $makeCustomer->account_no }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.shop_image') }}
                        </th>
                        <td>
                            @if($makeCustomer->shop_image)
                                <a href="{{ $makeCustomer->shop_image->getUrl() }}" target="_blank" style="display: inline-block">
                                    <img src="{{ $makeCustomer->shop_image->getUrl('thumb') }}">
                                </a>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.id_proof') }}
                        </th>
                        <td>
                            @if($makeCustomer->id_proof)
                                <a href="{{ $makeCustomer->id_proof->getUrl() }}" target="_blank">
                                    {{ trans('global.view_file') }}
                                </a>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.gst_certificate') }}
                        </th>
                        <td>
                            @if($makeCustomer->gst_certificate)
                                <a href="{{ $makeCustomer->gst_certificate->getUrl() }}" target="_blank">
                                    {{ trans('global.view_file') }}
                                </a>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.notes') }}
                        </th>
                        <td>
                            {!! $makeCustomer->notes !!}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.status') }}
                        </th>
                        <td>
                            {{ App\Models\MakeCustomer::STATUS_SELECT[$makeCustomer->status] ?? '' }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.make-customers.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection