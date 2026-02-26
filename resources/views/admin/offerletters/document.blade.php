<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Job Offer & Verification</title>

    <style>
        @page { margin: 20px; }

        body {
            font-family: "Segoe UI", Arial, sans-serif;
            color: #111;
            margin: 0;
            padding: 0;
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #0d6efd;
            color: #fff;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            z-index: 9999;
            font-size: 14px;
        }

        @media print {
            .print-btn { display: none; }
            .page-break { page-break-before: always; }
        }

        h2 {
            text-align: center;
            margin: 25px 0 10px;
            letter-spacing: 1px;
            font-size: 22px;
        }

        h3 {
            margin-top: 35px;
            font-size: 18px;
            border-left: 4px solid #0d6efd;
            padding-left: 10px;
        }

        p {
            font-size: 14px;
            line-height: 22px;
        }

        .badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: #fff;
            display: inline-block;
        }

        .badge-success { background: #16a34a; }
        .badge-danger  { background: #dc2626; }

        .card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin-top: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 14px;
        }

        table th {
            background: #f3f4f6;
            text-align: left;
            width: 35%;
            padding: 10px;
            border: 1px solid #d1d5db;
        }

        table td {
            padding: 10px;
            border: 1px solid #d1d5db;
        }

        .image-row {
            display: flex;
            gap: 40px;
            margin-top: 30px;
            justify-content: center;
        }

        .image-box {
            text-align: center;
        }

        .image-box img {
            width: 220px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 6px;
        }

        .image-box p {
            margin-top: 8px;
            font-size: 13px;
            color: #555;
        }

        .footer-note {
            margin-top: 50px;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
            border-top: 1px dashed #d1d5db;
            padding-top: 15px;
        }
        .page-label {
    text-align: center;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 10px;
    letter-spacing: 1px;
    margin-top: -40px;
}

    </style>
</head>

<body>

<div class="print-btn" onclick="window.print()">🖨 Print / Download</div>

<!-- ================= PAGE 1 : OFFER LETTER ================= -->
@include('admin.offerletters.offerletter')

<!-- ================= PAGE BREAK ================= -->
<div class="page-break"></div>

<!-- ================= PAGE 2 : VERIFICATION ================= -->

<h2>EMPLOYEE DOCUMENT VERIFICATION</h2>

<p style="text-align:center; max-width:700px; margin:0 auto;">
    This document confirms that the employee mentioned below has completed
    the mandatory verification process required for employment with
    <strong>{{ $company->title ?? config('app.name') }}</strong>.
</p>

<div class="card">
    <table>
        <tr>
            <th>Employee Name</th>
            <td>{{ $employee->full_name }}</td>
        </tr>

        <tr>
            <th>Employee Code</th>
            <td>{{ $employee->employee_code ?? 'N/A' }}</td>
        </tr>

        <tr>
            <th>Terms & Conditions</th>
            <td>
                @if($termsAccepted)
                    <span class="badge badge-success">ACCEPTED</span>
                @else
                    <span class="badge badge-danger">NOT ACCEPTED</span>
                @endif
            </td>
        </tr>

        <tr>
            <th>Document Verification Status</th>
            <td>
                @if($employee->document_verified)
                    <span class="badge badge-success">VERIFIED</span>
                @else
                    <span class="badge badge-danger">PENDING</span>
                @endif
            </td>
        </tr>

        <tr>
            <th>Documents Uploaded On</th>
            <td>
                {{ $employee->documents_uploaded_at
                    ? \Carbon\Carbon::parse($employee->documents_uploaded_at)->format('d F Y')
                    : 'Not Uploaded Yet' }}
            </td>
        </tr>

        <tr>
            <th>Verification Date</th>
            <td>{{ now()->format('d F Y') }}</td>
        </tr>

        <tr>
            <th>Verification Location</th>
            <td>{{ $user->current_address ?? 'N/A' }}</td>
        </tr>

        <tr>
            <th>Latitude / Longitude</th>
            <td>{{ $user->latitude }}, {{ $user->longitude }}</td>
        </tr>

        <tr>
            <th>Location Verified At</th>
            <td>
                {{ $user->location_verified_at
                    ? \Carbon\Carbon::parse($user->location_verified_at)->format('d F Y, h:i A')
                    : 'Not Verified' }}
            </td>
        </tr>
        <tr>
            <th>Special terms</th>
            <td>{{ $employee->special_terms ?? 'N/A' }}</td>
        </tr>
    </table>
</div>

<h3>Identity Verification Proof</h3>

<div class="image-row">
    <div class="image-box">
        @if($acceptImage)
            <img src="{{ $acceptImage->getUrl() }}">
        @else
            <img src="{{ asset('images/no-image.png') }}">
        @endif
        <p>Live Camera Verification</p>
    </div>

    <div class="image-box">
        @if($signImage)
            <img src="{{ $signImage->getUrl() }}">
        @else
            <img src="{{ asset('images/no-image.png') }}">
        @endif
        <p>Employee Digital Signature</p>
    </div>
</div>

<div class="footer-note">
    This is a system-generated verification document.  
    No physical signature is required. Any misuse or modification
    of this document may lead to disciplinary action as per company policy.
</div>
<div class="page-label">
    PAGE 2 OF 2 — EMPLOYEE VERIFICATION
</div>
</body>
</html>
