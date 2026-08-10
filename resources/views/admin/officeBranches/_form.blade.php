@csrf

<div class="row">
    <div class="form-group col-md-4">
        <label>Office Name</label>
        <input type="text" name="branch_name" class="form-control" value="{{ old('branch_name', $officeBranch->branch_name ?? '') }}" required>
    </div>
    <div class="form-group col-md-4">
        <label>Pincode</label>
        <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $officeBranch->pincode ?? '') }}" required>
    </div>
    <div class="form-group col-md-4">
        <label>Country</label>
        <input type="text" name="country" class="form-control" value="{{ old('country', $officeBranch->country ?? 'India') }}">
    </div>
</div>

<div class="row">
    <div class="form-group col-md-4">
        <label>City</label>
        <input type="text" name="city" class="form-control" value="{{ old('city', $officeBranch->city ?? '') }}">
    </div>
    <div class="form-group col-md-4">
        <label>State</label>
        <input type="text" name="state" class="form-control" value="{{ old('state', $officeBranch->state ?? '') }}">
    </div>
    <div class="form-group col-md-2">
        <label>Latitude</label>
        <input type="text" name="latitude" class="form-control" value="{{ old('latitude', $officeBranch->latitude ?? '') }}">
    </div>
    <div class="form-group col-md-2">
        <label>Longitude</label>
        <input type="text" name="longitude" class="form-control" value="{{ old('longitude', $officeBranch->longitude ?? '') }}">
    </div>
</div>

<div class="form-group">
    <label>Address</label>
    <textarea name="address_line" class="form-control" rows="3">{{ old('address_line', $officeBranch->address_line ?? '') }}</textarea>
</div>

<div class="form-group">
    <label>Registration / Other Details</label>
    <textarea name="registration_detail" class="form-control" rows="4">{{ old('registration_detail', $officeBranch->registration_detail ?? '') }}</textarea>
</div>

<hr>

<div class="row">
    <div class="form-group col-md-4">
        <label>Legal Entity Name</label>
        <input type="text" name="legal_entity_name" class="form-control" value="{{ old('legal_entity_name', $officeBranch->legal_entity_name ?? '') }}">
    </div>
    <div class="form-group col-md-4">
        <label>GST Number</label>
        <input type="text" name="gst_number" class="form-control" value="{{ old('gst_number', $officeBranch->gst_number ?? '') }}">
    </div>
    <div class="form-group col-md-4">
        <label>PAN Number</label>
        <input type="text" name="pan_number" class="form-control" value="{{ old('pan_number', $officeBranch->pan_number ?? '') }}">
    </div>
</div>

<div class="row">
    <div class="form-group col-md-4">
        <label>Incharge Name</label>
        <input type="text" name="incharge_name" class="form-control" value="{{ old('incharge_name', $officeBranch->incharge_name ?? '') }}" required>
    </div>
    <div class="form-group col-md-4">
        <label>Incharge Phone</label>
        <input type="text" name="incharge_phone" class="form-control" value="{{ old('incharge_phone', $officeBranch->incharge_phone ?? '') }}" required>
    </div>
    <div class="form-group col-md-4">
        <label>Incharge Email</label>
        <input type="email" name="incharge_email" class="form-control" value="{{ old('incharge_email', $officeBranch->incharge_email ?? '') }}">
    </div>
</div>

<div class="text-right">
    <a href="{{ route('admin.office-branches.index') }}" class="btn btn-secondary">Cancel</a>
    <button class="btn btn-success">Save Office</button>
</div>
