@extends('layouts.admin')
@section('content')
@can('make_customer_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.make-customers.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.makeCustomer.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.makeCustomer.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-MakeCustomer">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.id') }}
                        </th>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.customer_code') }}
                        </th>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.shop_name') }}
                        </th>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.owner_name') }}
                        </th>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.phone_number') }}
                        </th>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.email') }}
                        </th>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.pincode') }}
                        </th>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.area') }}
                        </th>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.city') }}
                        </th>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.state') }}
                        </th>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.country') }}
                        </th>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.latitude') }}
                        </th>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.longitude') }}
                        </th>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.business_type') }}
                        </th>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.shop_category') }}
                        </th>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.gst_number') }}
                        </th>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.license_no') }}
                        </th>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.payment_terms') }}
                        </th>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.preferred_payment_method') }}
                        </th>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.bank_name') }}
                        </th>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.ifsc_code') }}
                        </th>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.account_no') }}
                        </th>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.shop_image') }}
                        </th>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.id_proof') }}
                        </th>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.gst_certificate') }}
                        </th>
                        <th>
                            {{ trans('cruds.makeCustomer.fields.status') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($makeCustomers as $key => $makeCustomer)
                        <tr data-entry-id="{{ $makeCustomer->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $makeCustomer->id ?? '' }}
                            </td>
                            <td>
                                {{ $makeCustomer->customer_code ?? '' }}
                            </td>
                            <td>
                                {{ $makeCustomer->shop_name ?? '' }}
                            </td>
                            <td>
                                {{ $makeCustomer->owner_name ?? '' }}
                            </td>
                            <td>
                                {{ $makeCustomer->phone_number ?? '' }}
                            </td>
                            <td>
                                {{ $makeCustomer->email ?? '' }}
                            </td>
                            <td>
                                {{ $makeCustomer->pincode ?? '' }}
                            </td>
                            <td>
                                {{ $makeCustomer->area ?? '' }}
                            </td>
                            <td>
                                {{ $makeCustomer->city ?? '' }}
                            </td>
                            <td>
                                {{ $makeCustomer->state ?? '' }}
                            </td>
                            <td>
                                {{ $makeCustomer->country ?? '' }}
                            </td>
                            <td>
                                {{ $makeCustomer->latitude ?? '' }}
                            </td>
                            <td>
                                {{ $makeCustomer->longitude ?? '' }}
                            </td>
                            <td>
                                {{ App\Models\MakeCustomer::BUSINESS_TYPE_SELECT[$makeCustomer->business_type] ?? '' }}
                            </td>
                            <td>
                                {{ $makeCustomer->shop_category->name ?? '' }}
                            </td>
                            <td>
                                {{ $makeCustomer->gst_number ?? '' }}
                            </td>
                            <td>
                                {{ $makeCustomer->license_no ?? '' }}
                            </td>
                            <td>
                                {{ $makeCustomer->payment_terms ?? '' }}
                            </td>
                            <td>
                                {{ App\Models\MakeCustomer::PREFERRED_PAYMENT_METHOD_SELECT[$makeCustomer->preferred_payment_method] ?? '' }}
                            </td>
                            <td>
                                {{ $makeCustomer->bank_name ?? '' }}
                            </td>
                            <td>
                                {{ $makeCustomer->ifsc_code ?? '' }}
                            </td>
                            <td>
                                {{ $makeCustomer->account_no ?? '' }}
                            </td>
                            <td>
                                @if($makeCustomer->shop_image)
                                    <a href="{{ $makeCustomer->shop_image->getUrl() }}" target="_blank" style="display: inline-block">
                                        <img src="{{ $makeCustomer->shop_image->getUrl('thumb') }}">
                                    </a>
                                @endif
                            </td>
                            <td>
                                @if($makeCustomer->id_proof)
                                    <a href="{{ $makeCustomer->id_proof->getUrl() }}" target="_blank">
                                        {{ trans('global.view_file') }}
                                    </a>
                                @endif
                            </td>
                            <td>
                                @if($makeCustomer->gst_certificate)
                                    <a href="{{ $makeCustomer->gst_certificate->getUrl() }}" target="_blank">
                                        {{ trans('global.view_file') }}
                                    </a>
                                @endif
                            </td>
                            <td>
                                {{ App\Models\MakeCustomer::STATUS_SELECT[$makeCustomer->status] ?? '' }}
                            </td>
                            <td>
                                @can('make_customer_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.make-customers.show', $makeCustomer->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can('make_customer_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.make-customers.edit', $makeCustomer->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan

                                @can('make_customer_delete')
                                    <form action="{{ route('admin.make-customers.destroy', $makeCustomer->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
                                    </form>
                                @endcan

                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>



@endsection
@section('scripts')
@parent
<script>
    $(function () {
  let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
@can('make_customer_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.make-customers.massDestroy') }}",
    className: 'btn-danger',
    action: function (e, dt, node, config) {
      var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
          return $(entry).data('entry-id')
      });

      if (ids.length === 0) {
        alert('{{ trans('global.datatables.zero_selected') }}')

        return
      }

      if (confirm('{{ trans('global.areYouSure') }}')) {
        $.ajax({
          headers: {'x-csrf-token': _token},
          method: 'POST',
          url: config.url,
          data: { ids: ids, _method: 'DELETE' }})
          .done(function () { location.reload() })
      }
    }
  }
  dtButtons.push(deleteButton)
@endcan

  $.extend(true, $.fn.dataTable.defaults, {
    orderCellsTop: true,
    order: [[ 1, 'desc' ]],
    pageLength: 100,
  });
  let table = $('.datatable-MakeCustomer:not(.ajaxTable)').DataTable({ buttons: dtButtons })
  $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
      $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
  });
  
})

</script>
@endsection