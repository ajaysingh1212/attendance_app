<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>ID Card Design</title>
  <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <!-- Font Awesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    body {
      background: #e2e2e2;
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      align-items: flex-start;
      gap: 30px;
      padding: 20px;
    }

    .card {
      width: 280px;
      height: 440px;
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.2);
      overflow: hidden;
      position: relative;
      background: #fff;
    }

    .front, .back {
      position: relative;
    }

    .wave {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 55%;
      background: radial-gradient(circle at top right, #7b0000 0%, #000000 80%);
      clip-path: path('M0,0 H280 V100 C200,130 120,30 0,180 Z');
      z-index: 0;
    }

    .back-wave {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 55%;
      background: radial-gradient(circle at top right, #7b0000 0%, #000000 80%);
      clip-path: path('M280,0 H0 V100 C80,130 160,30 280,180 Z');
      z-index: 0;
    }

    .logo, .back-logo {
      position: absolute;
      top: 20px;
      left: 20px;
      z-index: 3;
    }

    .logo img, .back-logo img {
      width: 70px;
    }

    .company-text {
      position: absolute;
      top: 20px;
      left: 100px;
      color: white;
      z-index: 3;
    }

    .company-text h2 {
      margin: 0;
      font-size: 14px;
    }

    .company-text p {
      margin: 0;
      font-size: 12px;
    }

    .back .company-text {
     
      color: white;
    }

   .profile-wrapper {
    display: flex;
    justify-content: end;
    align-items: end;
    gap: 0px; /* photo aur blood drop ke beech spacing */
    margin-top: 110px;
    margin-right: 20px;
}

.circle {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: #eee;
    border: 4px solid red;
    display: flex;
    justify-content: space-between;
    align-items: center;
    overflow: hidden;
}

.circle img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

 .blood-icon {
    position: relative;
    font-size: 40px;   /* bigger drop */
    line-height: 1;
    display: inline-block;
}

.blood-icon .emoji {
    color: red !important; /* vivid red */
    filter: drop-shadow(0 4px 8px rgba(0,0,0,0.5)); /* stronger 3D effect */
    display: block;
    text-shadow: 0 0 6px rgba(255,0,0,0.6); /* subtle glow */
}

.blood-text {
    position: absolute;
    top: 60%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 15px;  /* slightly bigger text */
    font-weight: bold;
    color: #fff;
    text-shadow: 1px 1px 4px rgba(0,0,0,0.9);
    font-family: Arial, sans-serif;
    pointer-events: none; /* ensures text doesn't interfere with hover/click */
}

   

   
    .info {
      position: absolute;
      bottom: 80px;
      left: 20px;
      right: 20px;
      z-index: 2;
      text-align: center;
      text-transform: uppercase;
    }

    .info h3 {
      margin: 10px 0 0;
      color: #111;
      font-size: 18px;
    }

    .info p {
      margin: 2px;
      font-size: 12px;
    }
    .info div {
      margin-top: 20px;
    }

  .barcode {
    position: absolute;
    bottom: 10px;         /* thoda upar uth jaye */
    left: 20px;           /* left se space */
    right: 20px;          /* right se space */
    text-align: center;
    padding: 0px 10px;    /* andar ka padding */
}

.barcode svg {
    height: 55px !important;   /* barcode ki height kam */
    width: auto !important;    /* width auto rahe */
}

.barcode p {
    margin-top: 5px;
    font-weight: bold;
    font-size: 14px;
    letter-spacing: 2px;
}


    .terms {
      position: absolute;
      top: 100px;
      left: 20px;
      right: 20px;
      font-size: 11px;
      z-index: 2;
    }

    .terms h4 {
      color: #7b0000;
      margin-bottom: 5px;
    }

    .terms ul {
      padding-left: 20px;
      margin: 0;
    }

    .terms li {
      margin: 5px 0;
    }

    .footer-container {
      position: absolute;
      bottom: 10px;
      left: 0;
      right: 0;
      height: 60px;
      padding: 0 20px;
      font-size: 11px;
    }

    .signature {
      position: absolute;
      bottom: 0;
      left: 20px;
    }

    .signature img {
      width: 100px;
      height: 40px;
      object-fit: contain;
    }

    .dates {
      position: absolute;
      bottom: 0;
      right: 20px;
      text-align: right;
    }
  </style>
</head>
<body>
@foreach($employees as $employee)
  <!-- FRONT SIDE -->
  <div class="card front">
    <div class="wave"></div>
    <div class="logo">
        <img src="{{ asset('logo.jpg') }}" alt="Company Logo">
    </div>
    <div class="company-text">
      <h2>EEMOTRACK INDIA</h2>
      <p>{{ $employee->employee_code ?? 'N/A' }}</p>
    </div>
    <div class="profile-wrapper">
    <div class="circle">
        @if($employee->profile_photo)
            <img src="{{ asset('storage/' . $employee->profile_photo) }}" alt="Employee Photo">
        @else
            <span>No Photo</span>
        @endif
    </div>

     <div class="blood-icon" style="position: relative; top: -140px;">
    <span class="emoji">🩸</span>
    <div class="blood-text">{{ $employee->blood_group ?? 'N/A' }}</div>
  </div>


</div>
    <div class="info">
      <h3 style="">{{ $employee->full_name }}</h3>
      <p style="font-weight:900">Blood Group: {{ $employee->blood_group ?? 'N/A' }}</p>

      <div class="">
      <p style="font-weight:500" >{{ $employee->position ?? 'Position Not Specified' }}</p>
      <p>Phone: {{ $employee->phone ?? 'N/A' }}</p>
      <p>Dept: {{ $employee->department ?? 'N/A' }}</p>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
   <script>
    document.addEventListener("DOMContentLoaded", function () {
        let employeeCode = "{{ $employee->employee_code ?? 'N/A' }}";
        JsBarcode("#barcode-{{ $employee->id }}", employeeCode, {
            format: "CODE128",
            lineColor: "#000",
            width: 2.5,
            height: 60,
            margin: 15,
            displayValue: false
        });
    });
  </script>

   <div class="barcode">
        <svg id="barcode-{{ $employee->id }}"></svg>
    </div>

  </div>

  <!-- BACK SIDE -->
  <div class="card back">
    <div class="back-wave"></div>
    <div class="back-logo">
        <img src="{{ asset('logo.jpg') }}" alt="Company Logo">
    </div>
    <div class="company-text">
      <h2>EEMOTRACK INDIA</h2>
      <p>{{ $employee->employee_code ?? 'N/A' }}</p>
    </div>
   <div class="terms">
  <h4>Terms and Conditions</h4>
  <ul>
    <li>Employees must carry this card while on duty.</li>
    <li>Lost or damaged card will incur a fee.</li>
    <li>If found, return to the company.</li>
  </ul>
  <hr>
  <p>
    <h5 style="margin:0;">Company Information</h5>
    <strong>EEMOT Private Limited (EEMOTRACK)</strong><br>
     Kamala Market, R.K. Bhattacharya Road,<br>
    Pirmuhani, Salimpur Ahra, Golambar, Patna – 800001, Bihar, India<br>
    <strong>Contact:</strong> +91 78578 68055 
    <br> <strong>Email:</strong> info@eemotrack.com
  </p>
</div>


    <div class="footer-container">
     <div class="signature" style="text-align: center; margin-top:20px;">
    <span style="font-family: 'Pacifico', cursive; font-size: 14px; color:#000;font-weight:700">
        Neetu Sahani
    </span>
    <br>
    <span style="font-size: 14px; color:#555;">Signature</span>
</div>

      <div class="dates">
        <p>Join Date:<br> {{ $employee->date_of_joining ? \Carbon\Carbon::parse($employee->date_of_joining)->format('d/m/Y') : 'N/A' }}</p>
      </div>
    </div>
  </div>
@endforeach
</body>
</html>
