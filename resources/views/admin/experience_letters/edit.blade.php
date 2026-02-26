@extends('layouts.admin')
@section('content')

<div class="container">
<div class="card p-4 card-box">

<h4>Edit Experience Letter</h4>

<form action="{{ route('admin.experience-letters.update',$letter->id) }}" method="POST">
@csrf
@method('PUT')

<input type="hidden" name="employee_id" value="{{ $letter->employee_id }}">

<div class="row">

<div class="col-md-3 mb-3">
<label>Date of Joining</label>
<input type="date" name="date_of_joining" value="{{ $letter->date_of_joining }}" class="form-control">
</div>

<div class="col-md-3 mb-3">
<label>Last Working Date</label>
<input type="date" name="last_working_date" value="{{ $letter->last_working_date }}" class="form-control">
</div>

<div class="col-md-3 mb-3">
<label>Designation</label>
<input type="text" name="designation" value="{{ $letter->designation }}" class="form-control">
</div>

<div class="col-md-3 mb-3">
<label>Department</label>
<input type="text" name="department" value="{{ $letter->department }}" class="form-control">
</div>

<div class="col-md-3 mb-3">
<label>Salary</label>
<input type="number" step="0.01" name="last_drawn_salary" value="{{ $letter->last_drawn_salary }}" class="form-control">
</div>

</div>

<button type="submit" class="btn btn-warning">Update</button>

</form>
</div>
</div>

@endsection
