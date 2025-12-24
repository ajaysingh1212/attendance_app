@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">{{ isset($holiday) ? 'Edit' : 'Add' }} Holiday</h4>
        </div>
        <div class="card-body">
            <form method="POST" 
                  action="{{ isset($holiday) ? route('admin.holidays.update', $holiday) : route('admin.holidays.store') }}">
                @csrf
                @if(isset($holiday)) @method('PUT') @endif

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" id="title" name="title" class="form-control" 
                               value="{{ old('title', $holiday->title ?? '') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" id="start_date" name="start_date" class="form-control" 
                               value="{{ old('start_date', $holiday->start_date ?? '') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" 
                               value="{{ old('end_date', $holiday->end_date ?? '') }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="holiday_type" class="form-label">Holiday Type</label>
                        <select name="holiday_type" class="form-control">
                            @foreach(['General', 'Public', 'Religious', 'Company'] as $type)
                                <option value="{{ $type }}" 
                                    @selected(old('holiday_type', $holiday->holiday_type ?? '') == $type)>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 d-flex align-items-center mt-4">
                        <div class="form-check">
                            <input type="checkbox" name="is_optional" id="is_optional" class="form-check-input" 
                                   value="1" {{ old('is_optional', $holiday->is_optional ?? false) ? 'checked' : '' }}>
                            <label for="is_optional" class="form-check-label">Is Optional?</label>
                        </div>
                    </div>

                    <div class="col-md-4 d-flex align-items-center mt-4">
                        <div class="form-check">
                            <input type="checkbox" name="is_national" id="is_national" class="form-check-input" 
                                   value="1" {{ old('is_national', $holiday->is_national ?? false) ? 'checked' : '' }}>
                            <label for="is_national" class="form-check-label">Is National Holiday?</label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $holiday->description ?? '') }}</textarea>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">{{ isset($holiday) ? 'Update' : 'Save' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
