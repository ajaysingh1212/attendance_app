<!-- resources/views/idcard/template.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ID Card</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
    }

    .card-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
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
      margin: 10px;
    }

    .front, .back {
      position: relative;
    }

    .wave, .back-wave {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 55%;
      background: radial-gradient(circle at top right, #7b0000 0%, #000000 80%);
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

    .circle {
      position: absolute;
      top: 100px;
      left: 50%;
      transform: translateX(-50%);
      width: 120px;
      height: 120px;
      border-radius: 50%;
      background: #eee;
      border: 4px solid red;
      z-index: 2;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow: hidden;
    }

    .circle img {
      width: 100%;
      height: 100%;
      object-fit: cover;
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

    .barcode {
      position: absolute;
      bottom: 10px;
      left: 20px;
      right: 20px;
      text-align: center;
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
      font-family: 'Pacifico', cursive;
      font-size: 12px;
      color: #111;
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
  <div class="card-container">

    <!-- FRONT SIDE -->
    <div class="card front">
      <div class="wave"></div>
      <div class="logo">
          <img src="{{ public_path('logo.jpg') }}" alt="Company Logo">
      </div>
      <div class="company-text">
        <h2>EEMOTRACK INDIA</h2>
        <p>{{ $employee->employee_code ?? 'N/A' }}</p>
      </div>
      <div class="circle">
          <img src="{{ $employee->profile_photo ? public_path($employee->profile_photo) : ($user->media->first()->getPath() ?? public_path('default.png')) }}" alt="Employee Photo">
      </div>
      <div class="info">
        <h3>{{ $employee->full_name ?? $user->name }}</h3>
        <p>{{ $employee->position ?? $employee->degination ?? 'Position' }}</p>
        <p>Phone: {{ $employee->phone ?? $user->number ?? 'N/A' }}</p>
        <p>Dept: {{ $employee->department ?? 'N/A' }}</p>
      </div>
      <div class="barcode">
        <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($employee->employee_code ?? 'N/A', 'C128') }}" alt="barcode" />
      </div>
    </div>

    <!-- BACK SIDE -->
    <div class="card back">
      <div class="back-wave"></div>
      <div class="back-logo">
          <img src="{{ public_path('logo.jpg') }}" alt="Company Logo">
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
          <strong>EEMOT Private Limited (EEMOTRACK)</strong><br>
          Kamala Market, R.K. Bhattacharya Road,<br>
          Pirmuhani, Salimpur Ahra, Golambar,<br>
          Patna – 800001, Bihar, India<br>
          <strong>Contact:</strong> +91 78578 68055 <br>
          <strong>Email:</strong> info@eemotrack.com
        </p>
      </div>
      <div class="footer-container">
        <div class="signature">
          Neetu Sahani
          <br>
          <span style="font-family: 'Segoe UI', sans-serif; font-size: 11px; color:#000;">Signature</span>
        </div>
        <div class="dates">
          <p>Join Date:<br> {{ $employee->date_of_joining ?? 'N/A' }}</p>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
