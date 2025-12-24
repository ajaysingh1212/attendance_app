@extends('layouts.admin')
@section('content')
@can('show_report_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.show-reports.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.showReport.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.showReport.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-ShowReport">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('cruds.showReport.fields.id') }}
                        </th>
                        <th>
                            {{ trans('cruds.showReport.fields.select_employess') }}
                        </th>
                        <th>
                            {{ trans('cruds.user.fields.email') }}
                        </th>
                        <th>
                            {{ trans('cruds.showReport.fields.start_date') }}
                        </th>
                        <th>
                            {{ trans('cruds.showReport.fields.end_date') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($showReports as $key => $showReport)
                        <tr data-entry-id="{{ $showReport->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $showReport->id ?? '' }}
                            </td>
                            <td>
                                {{ $showReport->select_employess->name ?? '' }}
                            </td>
                            <td>
                                {{ $showReport->select_employess->email ?? '' }}
                            </td>
                            <td>
                                {{ $showReport->start_date ?? '' }}
                            </td>
                            <td>
                                {{ $showReport->end_date ?? '' }}
                            </td>
                            <td>
                                @can('show_report_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.show-reports.show', $showReport->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can('show_report_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.show-reports.edit', $showReport->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan

                                @can('show_report_delete')
                                    <form action="{{ route('admin.show-reports.destroy', $showReport->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
@can('show_report_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.show-reports.massDestroy') }}",
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
  let table = $('.datatable-ShowReport:not(.ajaxTable)').DataTable({ buttons: dtButtons })
  $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
      $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
  });
  
})

</script>
@endsection