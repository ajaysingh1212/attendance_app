@extends('layouts.admin')

@section('content')
@can('branch_create')
<div style="margin-bottom: 10px;" class="row">
    <div class="col-lg-12">
        <a class="btn btn-success" href="{{ route('admin.branches.create') }}">
            {{ trans('global.add') }} {{ trans('cruds.branch.title_singular') }}
        </a>

        <button class="btn btn-warning" data-toggle="modal" data-target="#csvImportModal">
            {{ trans('global.app_csvImport') }}
        </button>

        @include('csvImport.modal', [
            'model' => 'Branch',
            'route' => 'admin.branches.parseCsvImport'
        ])
    </div>
</div>
@endcan

<div class="card">
    <div class="card-header">
        {{ trans('cruds.branch.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped table-hover ajaxTable datatable datatable-Branch">
            <thead>
                <tr>
                    <th width="10"></th>
                    <th>ID</th>
                    <th>Branch Name</th>
                    <th>Address</th>
                    <th>Legal Name</th>
                    <th>Incharge</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>GST</th>
                    <th>PAN</th>
                    <th>Reg. No</th>
                    <th>Logo</th>
                    <th>Signature</th>
                    <th>Stamp</th>
                    <th>&nbsp;</th>
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

@can('branch_delete')
    let deleteButton = {
        text: '{{ trans('global.datatables.delete') }}',
        url: "{{ route('admin.branches.massDestroy') }}",
        className: 'btn-danger',
        action: function (e, dt, node, config) {
            let ids = $.map(dt.rows({ selected: true }).data(), function (entry) {
                return entry.id
            });

            if (ids.length === 0) {
                alert('{{ trans('global.datatables.zero_selected') }}');
                return;
            }

            if (confirm('{{ trans('global.areYouSure') }}')) {
                $.ajax({
                    headers: {'x-csrf-token': _token},
                    method: 'POST',
                    url: config.url,
                    data: { ids: ids, _method: 'DELETE' }
                }).done(function () { location.reload() })
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
        ajax: "{{ route('admin.branches.index') }}",

        columns: [
            { data: 'placeholder', name: 'placeholder' },
            { data: 'id', name: 'id' },
            { data: 'title', name: 'title' },
            { data: 'address', name: 'address' },
            { data: 'legal_name', name: 'legal_name' },
            { data: 'incharge_name', name: 'incharge_name' },
            { data: 'email', name: 'email' },
            { data: 'phone', name: 'phone' },
            { data: 'gst', name: 'gst' },
            { data: 'pan', name: 'pan' },
            { data: 'registration_number', name: 'registration_number' },

            { data: 'branch_image', name: 'branch_image', sortable: false, searchable: false },
            { data: 'signature_image', name: 'signature_image', sortable: false, searchable: false },
            { data: 'stamp_image', name: 'stamp_image', sortable: false, searchable: false },

            { data: 'actions', name: '{{ trans('global.actions') }}' }
        ],

        orderCellsTop: true,
        order: [[1, 'desc']],
        pageLength: 50,
    };

    $('.datatable-Branch').DataTable(dtOverrideGlobals);

});
</script>
@endsection
