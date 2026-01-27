@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        Generate Payroll
        <a href="{{ route('admin.payroll.list') }}" class="btn btn-outline-primary float-right">
            Check List
        </a>
    </div>

    <div class="card-body">

        {{-- Alerts --}}
        @if(session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Month / Year Check --}}
        <form method="GET" action="{{ route('admin.payroll.index') }}">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Month</label>
                    <select name="month" class="form-control">
                        @for($m=1;$m<=12;$m++)
                            <option value="{{ $m }}" {{ $m==$month?'selected':'' }}>
                                {{ DateTime::createFromFormat('!m',$m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="form-group col-md-4">
                    <label>Year</label>
                    <input type="number" name="year" value="{{ $year }}" class="form-control">
                </div>

                <div class="form-group col-md-4 mt-4">
                    <button class="btn btn-info mt-2">Check</button>
                </div>
            </div>
        </form>

        <hr>

 

        {{-- IF PAYROLL ALREADY GENERATED --}}
        @if($alreadyGenerated)
            <div class="alert alert-danger mt-3">
                ⚠ Payroll already generated for this month.
            </div>

            <button
                class="btn btn-danger"
                data-toggle="modal"
                data-target="#masterPasswordModal">
                Regenerate Payroll 🔐
            </button>
        @endif

    </div>
</div>

{{-- ================= MODAL ================= --}}
<div class="modal fade" id="masterPasswordModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    🔐 Master Password Required
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.payroll.verifyMaster') }}">
                @csrf
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="year" value="{{ $year }}">

                <div class="modal-body">
                    <div class="form-group">
                        <label>Enter Master Password</label>
                        <input
                            type="password"
                            name="master_password"
                            class="form-control"
                            placeholder="********"
                            required>
                    </div>

                    <small class="text-muted">
                        This action will regenerate payroll for the selected month.
                    </small>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        Confirm & Regenerate
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
{{-- ========================================= --}}

@endsection
