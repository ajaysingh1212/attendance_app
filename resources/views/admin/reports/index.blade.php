@extends('layouts.admin')
@section('content')
@can('performence_report_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.performance-reports.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.performancereport.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.performancereport.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-Report">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        
                        <th>
                            {{ trans('cruds.performancereport.fields.id') }}
                        </th>
                        <th>Employee</th>
                        <th>
                            {{ trans('cruds.performancereport.fields.date') }}
                        </th>
                        <th>
                            {{ trans('cruds.performancereport.fields.sales') }}
                        </th>
                        <th>
                            {{ trans('cruds.performancereport.fields.cost_of_sell') }}
                        </th>
                        <th>
                            {{ trans('cruds.performancereport.fields.metrial_cost') }}
                        </th>
                        <th>
                            {{ trans('cruds.performancereport.fields.salaries') }}
                        </th>
                        <th>
                            {{ trans('cruds.performancereport.fields.tour_travel') }}
                        </th>
                        <th>
                            {{ trans('cruds.performancereport.fields.other_cost') }}
                        </th>
                        <th>
                            {{ trans('cruds.performancereport.fields.unpaid_amount') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $key => $performanceReport)
                        <tr data-entry-id="{{ $performanceReport->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $performanceReport->id ?? '' }}
                            </td>
                            <td>
                                {{ $performanceReport->employee->full_name ?? '' }}
                            </td>
                            <td>
                                {{ $performanceReport->date ?? '' }}
                            </td>
                            <td>
                                {{ $performanceReport->sales ?? '' }}
                            </td>
                            <td>
                                {{ $performanceReport->cost_of_sell ?? '' }}
                            </td>
                            <td>
                                {{ $performanceReport->metrial_cost ?? '' }}
                            </td>
                            <td>
                                {{ $performanceReport->salaries ?? '' }}
                            </td>
                            <td>
                                {{ $performanceReport->tour_travel ?? '' }}
                            </td>
                            <td>
                                {{ $performanceReport->other_cost ?? '' }}
                            </td>
                            <td>
                                {{ $performanceReport->unpaid_amount ?? '' }}
                            </td>
                            <td>
                                @can('performence_report_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.performance-reports.show', $performanceReport->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can('performence_report_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.performance-reports.edit', $performanceReport->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan

                                @can('performence_report_delete')
                                    <form action="{{ route('admin.performance-reports.destroy', $performanceReport->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
@can('report_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.reports.massDestroy') }}",
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
  let table = $('.datatable-Report:not(.ajaxTable)').DataTable({ buttons: dtButtons })
  $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
      $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
  });
  
})

</script>
@endsection