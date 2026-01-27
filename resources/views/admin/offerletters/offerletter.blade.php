<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Job Offer Letter</title>

    <link href="{{ asset('css/style.css') }}" rel="stylesheet" />

    <style>
        /* ================= PRINT RESET ================= */
        @page {
            margin: 0 5px; /* LEFT & RIGHT 5px */
        }

        body {
            margin: 0;
            padding: 0 5px; /* LEFT & RIGHT 5px */
            font-family: Arial, Helvetica, sans-serif;
        }

        /* MAIN WRAPPER SAFETY */
        .header-wrap {
            max-width: 100%;
            box-sizing: border-box;
        }

        /* ================= PRINT BUTTON ================= */
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #1d4ed8;
            color: #fff;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            z-index: 9999;
        }

        @media print {
            .print-btn {
                display: none;
            }
        }

        /* ================= HEADER ================= */
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .company-logo img {
            height: 60px;
        }

        .company-address {
            max-width: 380px;
            line-height: 16px;
            word-wrap: break-word;
        }

        .user-photo img {
            height: 80px;
            width: 80px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #ddd;
            margin-bottom: 5px;
        }

        /* ================= TITLE ================= */
        .letter-title {
            text-align: center;
            margin: 25px 0 10px;
            font-size: 22px;
            letter-spacing: 1px;
        }

        .letter-underline {
            width: 220px;
            margin: 0 auto 25px;
            border-bottom: 2px solid #000;
        }

        /* ================= CONTENT ================= */
        p, li {
            font-size: 14px;
            line-height: 22px;
        }

        /* ================= FOOTER ================= */
        .signature {
            margin-top: -23px;
        }
    </style>
</head>
<body>

<!-- PRINT BUTTON -->
<div class="print-btn" onclick="window.print()">🖨 Print</div>

<div class="header-wrap">

    <!-- TOP BAR -->
    <div class="top-bar">
        <div class="top-right">
            <span class="blue"></span>
            <span class="orange"></span>
            <span class="last"></span>
        </div>
    </div>

    <!-- HEADER CONTENT -->
    <div class="header-content">

        <!-- LEFT -->
        <div class="left">
            <div class="logo-circle company-logo">
                @if($company && $company->branch_image)
                    <img src="{{ $company->branch_image->url }}" alt="Company Logo">
                @else
                    <div class="logo-icon"></div>
                @endif
            </div>

            <div class="brand">
                <h3 style="color: orange;">{{ $company->title ?? config('app.name') }}</h3>
                <p class="company-address">
                    {{ $company->address ?? url('/') }}
                </p>
            </div>
        </div>

        <!-- RIGHT -->
        <div class="right" style="text-align:right;">
            <p>{{ $company->email ?? 'hr@company.com' }}</p>
            <p>{{ $employee->phone ?? '+91-XXXXXXXXXX' }}</p>
            <p>{{ $company->title ?? 'Head Office' }}</p>
        </div>
    </div>

    <div class="double-line"></div>

    <!-- TO & DATE -->
    <div class="row">
        <div class="left">
            <strong>To:</strong><br>
            {{ $employee->full_name }}<br>
            {{ $employee->email ?? 'N/A' }}
        </div>

        <div class="right date" style="text-align:right;">
            <div class="user-photo">
                @if($userImage && $userImage->file_name)
                    <img src="{{ asset('storage/'.$userImage->id.'/'.$userImage->file_name) }}">
                @else
                    <img src="{{ asset('images/default-user.png') }}">
                @endif
            </div>
            <strong>{{ \Carbon\Carbon::now()->format('d F Y') }}</strong>
        </div>
    </div>

    <!-- TITLE -->
    <h2 class="letter-title">JOB OFFER LETTER</h2>
    <div class="letter-underline"></div>

    <!-- CONTENT -->
    <p>
        We are pleased to formally offer you the position of
        <strong>{{ $employee->position ?? 'Employee' }}</strong>
        in the <strong style="text-transform:uppercase;">{{ $employee->department ?? 'Department' }}</strong>
        department at <strong>{{ $company->title ?? 'Head Office' }}</strong>.
        After careful evaluation of your qualifications, experience,
        and professional background, we believe that your skills and
        expertise will be a valuable asset to our organization.
    </p>

    <p>
        Your employment with us is scheduled to commence on
        <strong>{{ $employee->date_of_joining ? \Carbon\Carbon::parse($employee->date_of_joining)->format('d F Y') : 'TBD' }}</strong>.
        You will be based at our
        <strong>{{ $employee->branch->title ?? 'designated office location' }}</strong>.
    </p>

    <p><strong>Details of the Offer:</strong></p>

    <ul class="offer-list">
        <li><strong>Position:</strong> {{ $employee->position ?? 'N/A' }}</li>
        <li><strong>Department:</strong> {{ strtoupper($employee->department ?? 'N/A') }}</li>
        <li><strong>Work Location:</strong> {{ $employee->branch->title ?? 'N/A' }}</li>
        <li><strong>Employment Status:</strong> {{ ucfirst($employee->employee_type ?? 'Active') }}</li>
        <li><strong>Net Salary:</strong> ₹{{ number_format($employee->net_salary ?? 0, 2) }}</li>

        {{-- ✅ Show duration only if months exist --}}
        @if(!empty($employee->employee_duration_months) && $employee->date_of_joining)
            @php
                $joiningDate = \Carbon\Carbon::parse($employee->date_of_joining);
                $endDate = (clone $joiningDate)->addMonths($employee->employee_duration_months);
            @endphp

            <li>
                <strong>Employment Duration:</strong><br>
                {{ $joiningDate->format('F Y') }}
                → {{ $employee->employee_duration_months }} month(s)
                → {{ $endDate->format('F Y') }}
            </li>
        @endif
    </ul>



    <p>
        Kindly confirm your acceptance of this offer by signing and returning a copy of this
        letter. Your acceptance will indicate your agreement to comply with all company
        policies, rules, and regulations applicable to your employment.
    </p>

    <p>
        This offer is subject to completion of pre-employment formalities, including
        document verification and statutory requirements. The company reserves the right
        to withdraw this offer if any information provided is found to be incorrect.
    </p>

    <!-- SIGNATURE -->
    <div class="signature">
        <p>Sincerely,</p>
        <p class="name">
            {{ $company->incharge_name ?? 'HR Manager' }}<br>
            <span>Human Resources Department</span><br>
            <span>{{ $company->title ?? config('app.name') }}</span>
        </p>
    </div>

    <!-- FOOTER -->
    <div class="footer-bar">
        <span class="bottom-last"></span>
        <span class="bottom-orange"></span>
        <span class="bottom-blue"></span>
        <span class="bottom-blue2"></span>
        <span class="bottom-orange2"></span>
        <span class="last-blue"></span>
        <span class="traingle-blue"></span>
        <span class="right-traingle-blue"></span>
        <span class="right-blue"></span>
        <span class="right-orange"></span>
        <span class="right-blue1"></span>
        <span class="right-blue2"></span>
        <span class="right-blue3"></span>
    </div>
    <div class="page-label">
    PAGE 1 OF 2 — OFFER LETTER
</div>

</div>
</body>
</html>
