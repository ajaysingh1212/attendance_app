@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('cruds.report.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.reports.store") }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="required" for="date">{{ trans('cruds.report.fields.date') }}</label>
                <input class="form-control date {{ $errors->has('date') ? 'is-invalid' : '' }}" type="text" name="date" id="date" value="{{ old('date') }}" required>
                @if($errors->has('date'))
                    <div class="invalid-feedback">
                        {{ $errors->first('date') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.report.fields.date_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="sales">{{ trans('cruds.report.fields.sales') }}</label>
                <input class="form-control {{ $errors->has('sales') ? 'is-invalid' : '' }}" type="number" name="sales" id="sales" value="{{ old('sales', '') }}" step="0.01" required>
                @if($errors->has('sales'))
                    <div class="invalid-feedback">
                        {{ $errors->first('sales') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.report.fields.sales_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="cost_of_sell">{{ trans('cruds.report.fields.cost_of_sell') }}</label>
                <input class="form-control {{ $errors->has('cost_of_sell') ? 'is-invalid' : '' }}" type="text" name="cost_of_sell" id="cost_of_sell" value="{{ old('cost_of_sell', '') }}">
                @if($errors->has('cost_of_sell'))
                    <div class="invalid-feedback">
                        {{ $errors->first('cost_of_sell') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.report.fields.cost_of_sell_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="metrial_cost">{{ trans('cruds.report.fields.metrial_cost') }}</label>
                <input class="form-control {{ $errors->has('metrial_cost') ? 'is-invalid' : '' }}" type="text" name="metrial_cost" id="metrial_cost" value="{{ old('metrial_cost', '') }}" required>
                @if($errors->has('metrial_cost'))
                    <div class="invalid-feedback">
                        {{ $errors->first('metrial_cost') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.report.fields.metrial_cost_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="salaries">{{ trans('cruds.report.fields.salaries') }}</label>
                <input class="form-control {{ $errors->has('salaries') ? 'is-invalid' : '' }}" type="text" name="salaries" id="salaries" value="{{ old('salaries', '') }}" required>
                @if($errors->has('salaries'))
                    <div class="invalid-feedback">
                        {{ $errors->first('salaries') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.report.fields.salaries_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="tour_travel">{{ trans('cruds.report.fields.tour_travel') }}</label>
                <input class="form-control {{ $errors->has('tour_travel') ? 'is-invalid' : '' }}" type="text" name="tour_travel" id="tour_travel" value="{{ old('tour_travel', '') }}">
                @if($errors->has('tour_travel'))
                    <div class="invalid-feedback">
                        {{ $errors->first('tour_travel') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.report.fields.tour_travel_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="other_cost">{{ trans('cruds.report.fields.other_cost') }}</label>
                <input class="form-control {{ $errors->has('other_cost') ? 'is-invalid' : '' }}" type="text" name="other_cost" id="other_cost" value="{{ old('other_cost', '') }}">
                @if($errors->has('other_cost'))
                    <div class="invalid-feedback">
                        {{ $errors->first('other_cost') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.report.fields.other_cost_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="unpaid_amount">{{ trans('cruds.report.fields.unpaid_amount') }}</label>
                <input class="form-control {{ $errors->has('unpaid_amount') ? 'is-invalid' : '' }}" type="text" name="unpaid_amount" id="unpaid_amount" value="{{ old('unpaid_amount', '') }}">
                @if($errors->has('unpaid_amount'))
                    <div class="invalid-feedback">
                        {{ $errors->first('unpaid_amount') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.report.fields.unpaid_amount_helper') }}</span>
            </div>
            <div class="form-group">
                <button class="btn btn-danger" type="submit">
                    {{ trans('global.save') }}
                </button>
            </div>
        </form>
    </div>
</div>



@endsection