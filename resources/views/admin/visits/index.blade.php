@extends('layouts.admin')
@section('content')
@can('visit_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.visits.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.visit.title_singular') }}
            </a>
            <button class="btn btn-warning" data-toggle="modal" data-target="#csvImportModal">
                {{ trans('global.app_csvImport') }}
            </button>
            @include('csvImport.modal', ['model' => 'Visit', 'route' => 'admin.visits.parseCsvImport'])
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.visit.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <table class=" table table-bordered table-striped table-hover ajaxTable datatable datatable-Visit">
            <thead>
                <tr>
                    <th width="10">

                    </th>
                    <th>
                        {{ trans('cruds.visit.fields.id') }}
                    </th>
                    <th>
                        {{ trans('cruds.visit.fields.user') }}
                    </th>
                    <th>
                        {{ trans('cruds.visit.fields.latitude') }}
                    </th>
                    <th>
                        {{ trans('cruds.visit.fields.longitude') }}
                    </th>
                    <th>
                        {{ trans('cruds.visit.fields.location') }}
                    </th>
                    <th>
                        {{ trans('cruds.visit.fields.visited_time') }}
                    </th>
                    <th>
                        {{ trans('cruds.visit.fields.visited_counter_image') }}
                    </th>
                    <th>
                        {{ trans('cruds.visit.fields.visit_self_image') }}
                    </th>
                    <th>
                        {{ trans('cruds.visit.fields.visited_out_latitude') }}
                    </th>
                    <th>
                        {{ trans('cruds.visit.fields.visited_out_longitude') }}
                    </th>
                    <th>
                        {{ trans('cruds.visit.fields.visited_out_location') }}
                    </th>
                    <th>
                        {{ trans('cruds.visit.fields.visited_out_time') }}
                    </th>
                    <th>
                        {{ trans('cruds.visit.fields.visited_out_counter_image') }}
                    </th>
                    <th>
                        {{ trans('cruds.visit.fields.visited_out_self_image') }}
                    </th>
                    <th>
                        {{ trans('cruds.visit.fields.visited_duration') }}
                    </th>
                    <th>
                        &nbsp;
                    </th>
                </tr>
            </thead>
        </table>
    </div>
</div>



@endsection
@section('scripts')
@parent
<script>
    $(function () {
  let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
@can('visit_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}';
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.visits.massDestroy') }}",
    className: 'btn-danger',
    action: function (e, dt, node, config) {
      var ids = $.map(dt.rows({ selected: true }).data(), function (entry) {
          return entry.id
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

  let dtOverrideGlobals = {
    buttons: dtButtons,
    processing: true,
    serverSide: true,
    retrieve: true,
    aaSorting: [],
    ajax: "{{ route('admin.visits.index') }}",
    columns: [
      { data: 'placeholder', name: 'placeholder' },
{ data: 'id', name: 'id' },
{ data: 'user', name: 'user' },
{ data: 'latitude', name: 'latitude' },
{ data: 'longitude', name: 'longitude' },
{ data: 'location', name: 'location' },
{ data: 'visited_time', name: 'visited_time' },
{ data: 'visited_counter_image', name: 'visited_counter_image', sortable: false, searchable: false },
{ data: 'visit_self_image', name: 'visit_self_image', sortable: false, searchable: false },
{ data: 'visited_out_latitude', name: 'visited_out_latitude' },
{ data: 'visited_out_longitude', name: 'visited_out_longitude' },
{ data: 'visited_out_location', name: 'visited_out_location' },
{ data: 'visited_out_time', name: 'visited_out_time' },
{ data: 'visited_out_counter_image', name: 'visited_out_counter_image', sortable: false, searchable: false },
{ data: 'visited_out_self_image', name: 'visited_out_self_image', sortable: false, searchable: false },
{ data: 'visited_duration', name: 'visited_duration' },
{ data: 'actions', name: '{{ trans('global.actions') }}' }
    ],
    orderCellsTop: true,
    order: [[ 1, 'desc' ]],
    pageLength: 100,
  };
  let table = $('.datatable-Visit').DataTable(dtOverrideGlobals);
  $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
      $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
  });
  
});

</script>
@endsection