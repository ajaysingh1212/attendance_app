@extends('layouts.admin')

@section('content')
<div class="container">
    <h2 class="mb-4">👤 Employee Profile: {{ $employee->full_name }}</h2>

    <!-- Profile Information -->
    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Basic Information</h5>
        </div>
        <div class="card-body row">
            <div class="col-md-4">
                <p><strong>Email:</strong> {{ $employee->email }}</p>
                <p><strong>Phone:</strong> {{ $employee->phone }}</p>
                <p><strong>Employee Code:</strong> {{ $employee->employee_code }}</p>
            </div>
            <div class="col-md-4">
                <p><strong>Position:</strong> {{ $employee->position }}</p>
                <p><strong>Department:</strong> {{ $employee->department }}</p>
                <p><strong>Joining Date:</strong> {{ $employee->date_of_joining }}</p>
            </div>
            <div class="col-md-4">
                <p><strong>Status:</strong> 
                    @if($employee->status === 'Active')
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-danger">Inactive</span>
                    @endif
                </p>

               @if ($employee->photo && file_exists(public_path('storage/uploads/employees/' . $employee->photo)))
    <div class="mb-3">
        <p><strong>Photo:</strong></p>
        <img src="{{ asset('storage/uploads/employees/' . $employee->photo) }}" 
             alt="Employee Photo" class="img-fluid rounded border" style="max-height: 180px;">
    </div>
@else
    <p><strong>Photo:</strong> Not uploaded</p>
@endif

            </div>
        </div>
    </div>

    <!-- Document Section -->
    <div class="card shadow">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">📎 Uploaded Documents</h5>
        </div>
        <div class="card-body">
           @php
    $documentLabels = [
        'cv' => 'CV',
        'offer_letter' => 'Offer Letter',
        'aadhaar_front' => 'Aadhaar Front',
        'aadhaar_back' => 'Aadhaar Back',
        'pan_card' => 'PAN Card',
        'marksheet' => 'Marksheet',
        'certificate' => 'Certificate',
        'passbook' => 'Passbook',
        'photo' => 'Photo',
        'signature' => 'Signature',
        'other_document' => 'Other Document',
        'exprience_letter' => 'Experience Letter',
    ];
@endphp
<div class="row">
    @foreach ($documents as $doc)
        @php
            $fileName  = basename($doc);
            $fieldKey  = pathinfo($fileName, PATHINFO_FILENAME);
            $extension = strtolower(pathinfo($doc, PATHINFO_EXTENSION));
            $label     = $documentLabels[$fieldKey] ?? ucfirst(str_replace('_', ' ', $fieldKey));
            $relativePath = 'storage/' . ltrim($doc, '/'); // Ensure it starts without /
            $fileUrl  = asset($relativePath);
            $isImage  = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            $isPDF    = $extension === 'pdf';
        @endphp

        <div class="col-md-4 mb-4">
            <label class="fw-bold d-block">{{ $label }}</label>
            <div class="border rounded p-2 text-center bg-light">
                @if ($isImage)
                    <img src="{{ $fileUrl }}" alt="{{ $label }}" class="img-fluid" style="max-height: 150px;">
                @elseif ($isPDF)
                    <iframe src="{{ $fileUrl }}" width="100%" height="200" frameborder="0"></iframe>
                @else
                    <iframe src="https://docs.google.com/gview?url={{ urlencode($fileUrl) }}&embedded=true"
                            width="100%" height="200" frameborder="0"></iframe>
                @endif
            </div>

            <a href="{{ $fileUrl }}" target="_blank" class="btn btn-outline-primary btn-sm mt-2 w-100">
                ⬇️ View Document
            </a>
        </div>
    @endforeach
</div>


            </div>

            <div class="text-center mt-4">
           <form action="{{ route('admin.employees.downloadPdf', $employee->id) }}" method="POST">
    @csrf
    <button type="submit" class="btn btn-danger px-4 py-2">
        🧾 Download Profile as PDF
    </button>
</form>




            </div>
        </div>
    </div>
</div>
@endsection
