@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">Generate Payroll
        <a href="{{ route('admin.payroll.list') }}" class="btn btn-outline-primary float-right">Check List</a>
        <a href="{{ route('admin.employee_monthly_attendance.index') }}" class="btn btn-outline-success float-right mr-2">Check Attendence</a>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.payroll.generate') }}">
            @csrf
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="month">Month</label>
                    <select name="month" class="form-control" required>
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="year">Year</label>
                    <input type="number" name="year" value="{{ now()->year }}" class="form-control" required>
                </div>
                <div class="form-group col-md-4 mt-4">
                    <button type="submit" class="btn btn-primary mt-2">Generate Payroll</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
