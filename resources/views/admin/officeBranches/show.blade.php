@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">Office Details</div>
    <div class="card-body">
        <table class="table table-bordered">
            <tr><th>Office Name</th><td>{{ $officeBranch->branch_name }}</td></tr>
            <tr><th>Address</th><td>{{ $officeBranch->address_line ?? '-' }}</td></tr>
            <tr><th>City</th><td>{{ $officeBranch->city ?? '-' }}</td></tr>
            <tr><th>State</th><td>{{ $officeBranch->state ?? '-' }}</td></tr>
            <tr><th>Country</th><td>{{ $officeBranch->country ?? '-' }}</td></tr>
            <tr><th>Pincode</th><td>{{ $officeBranch->pincode ?? '-' }}</td></tr>
            <tr><th>Latitude</th><td>{{ $officeBranch->latitude ?? '-' }}</td></tr>
            <tr><th>Longitude</th><td>{{ $officeBranch->longitude ?? '-' }}</td></tr>
            <tr><th>Details</th><td>{{ $officeBranch->registration_detail ?? '-' }}</td></tr>
            <tr><th>Legal Entity Name</th><td>{{ $officeBranch->legal_entity_name ?? '-' }}</td></tr>
            <tr><th>GST Number</th><td>{{ $officeBranch->gst_number ?? '-' }}</td></tr>
            <tr><th>PAN Number</th><td>{{ $officeBranch->pan_number ?? '-' }}</td></tr>
            <tr><th>Incharge Name</th><td>{{ $officeBranch->incharge_name ?? '-' }}</td></tr>
            <tr><th>Incharge Phone</th><td>{{ $officeBranch->incharge_phone ?? '-' }}</td></tr>
            <tr><th>Incharge Email</th><td>{{ $officeBranch->incharge_email ?? '-' }}</td></tr>
        </table>
        <a href="{{ route('admin.office-branches.index') }}" class="btn btn-secondary">Back</a>
    </div>
</div>
@endsection
