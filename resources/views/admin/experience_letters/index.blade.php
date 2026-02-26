@extends('layouts.admin')

@section('content')

<div class="card">
    <div class="card-header">
        Experience Letters List
    </div>

    <div class="card-body">

        <div class="mb-3">
            <a href="{{ route('admin.experience-letters.create') }}" class="btn btn-success">
                Create Experience Letter
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover datatable-ExperienceLetter text-center" style="width:100%">
                <thead class="thead-light">
                    <tr>
                        <th width="40">ID</th>
                        <th>Employee</th>
                        <th width="130">Joining Date</th>
                        <th width="130">Last Working</th>
                        <th width="120">Salary</th>
                        <th width="120">Status</th>
                        <th width="220">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($letters as $letter)
                    <tr>
                        <td>{{ $letter->id }}</td>
                        <td class="text-left">{{ $letter->employee->full_name ?? '' }}</td>
                        <td>{{ $letter->date_of_joining }}</td>
                        <td>{{ $letter->last_working_date }}</td>
                        <td>₹ {{ number_format($letter->last_drawn_salary,2) }}</td>

                        <td>
                            @if($letter->status == 'pending')
                                <span class="badge badge-warning px-3 py-1">Pending</span>
                            @elseif($letter->status == 'approved')
                                <span class="badge badge-success px-3 py-1">Approved</span>
                            @elseif($letter->status == 'issued')
                                <span class="badge badge-primary px-3 py-1">Issued</span>
                            @else
                                <span class="badge badge-danger px-3 py-1">Rejected</span>
                            @endif

                            <br>

                            <button class="btn btn-sm btn-outline-dark mt-1"
                                data-toggle="modal"
                                data-target="#statusModal"
                                onclick="setStatus({{ $letter->id }}, '{{ $letter->status }}')">
                                Change
                            </button>
                        </td>

                        <td>
                            <a href="{{ route('admin.experience-letters.show',$letter->id) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('admin.experience-letters.edit',$letter->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <a href="{{ route('admin.experience-letters.print',$letter->id) }}"
   class="btn btn-sm btn-success" target="_blank">
   Print
</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>

    </div>
</div>


<!-- STATUS MODAL -->
<div class="modal fade" id="statusModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <form method="POST" id="statusForm">
        @csrf

        <div class="modal-header">
          <h5 class="modal-title">Update Status</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">
            <input type="hidden" name="letter_id" id="modal_letter_id">

            <div class="form-group">
                <label>Select Status</label>
                <select name="status" id="modal_status" class="form-control">
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="issued">Issued</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Update</button>
        </div>

      </form>

    </div>
  </div>
</div>

@endsection


@section('scripts')
@parent

<script>
$(document).ready(function(){

    $('.datatable-ExperienceLetter').DataTable({
        pageLength: 25,
        order: [[0, 'desc']],
        autoWidth: false,
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });

});

function setStatus(id, status)
{
    document.getElementById('modal_letter_id').value = id;
    document.getElementById('modal_status').value = status;
    document.getElementById('statusForm').action =
        '/admin/experience-letters/' + id + '/update-status';
}
</script>

@endsection
