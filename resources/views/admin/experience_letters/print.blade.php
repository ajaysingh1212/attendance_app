<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Header Only</title>
  <link rel="stylesheet" href="style.css">
</head>
<style>
    *{
  box-sizing:border-box;
 
}

body{

  background:#eee;
  padding:30px 0;   /* top-bottom only */
  font-family: 'Times New Roman', Times, serif;
}



/* HEADER WRAP */
.header-wrap{
  width:794px;
  background:#fff;
  margin:auto;
  padding:0 50px 120px;
  border-radius:10px 10px 10px 10px;
  position:relative;
  overflow:hidden;

}

/* TOP BAR */
.top-bar{
  height:42px;
  background:#0b6fae;
  position:relative;

  width:calc(100% + 50px);  /* body padding = 30px + 30px */
  margin-left:-50px;        /* body padding ke bahar */
  margin-right:-45px;
}



.top-right{
  position:absolute;
  right:0;
  top:0;
  height:42px;
}

.top-right .blue{
  position:absolute;
  left:-20px;
  width:28px;
  height:182px;
 background: #0a3069;
 background: linear-gradient(90deg, rgba(10, 48, 105, 1) 40%, rgba(54, 173, 191, 1) 100%);
  transform:skewX(45deg);
}

.top-right .orange{
  position:absolute;
  left:20px;
  width:28px;
  height:192px;
  background:#f7941d;
  transform:skewX(45deg);
}

.top-right .last{
  position:absolute;
  right:-135px;
  width:90px;
  height:182px;
  background:#0b6fae;
  transform:skewX(45deg);
}
/* HEADER CONTENT */
.header-content{
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin-top:30px;
}

.left{
  display:flex;
  align-items:center;
  gap:15px;
}

.logo-icon img{
  width:60px;
  height:60px;
  object-fit:contain;
}

.brand h3{
  margin:0;
  color:#0b6fae;
  font-size:20px;
  font-weight:700;
}

.brand p{
  margin:3px 0 0;
  font-size:13px;
}


/* RIGHT */
.right p{
  margin:2px 0;
  font-size:13px;
  text-align:right;
  margin-right: 35px;

}

/* DOUBLE LINE */
.double-line{
  margin:18px 0 30px;
  height:3px;
  border-top:5px solid #0b6fae;
  border-bottom:3px solid #0b6fae;
  padding: 5px;
}

/* TITLE */
.title{
  text-align:center;
  color:#0b6fae;
  letter-spacing:3px;
  font-size:24px;
}
.footer-bar{
  height:42px;
  background:#0b6fae;

  position:absolute;        /* 🔴 relative ❌ → absolute ✅ */
  bottom:0;                 /* 👈 ab bilkul niche touch karega */
  left:-50px;               /* left se bahar */
  width:calc(100% + 90px); /* full stretch */
  margin-top: 100px;

}


.letter-content{
  padding:40px 50px;
  font-size:14px;
  line-height:1.7;
  color:#222;
}

.row{
  display:flex;
  justify-content:space-between;
  margin-bottom:25px;
}

.date{
  font-size:13px;
}

.letter-title{
  text-align:center;
  color:#0b6fae;
  letter-spacing:2px;
  margin:25px 0;
}

.offer-list{
  margin:10px 0 20px 20px;
}

.offer-list li{
  margin-bottom:6px;
}

.signature{
  margin-top:50px;
  margin-left: 500px;
}

.signature .name{
  margin-top:40px;
  font-weight:bold;
}

.signature span{
  font-weight:normal;
  font-size:13px;
}

.footer-bar .bottom-last{
  position:absolute;
  left:35px;
  width:90px;
  height:182px;
  background:#0b6fae;
  transform:skewX(90deg);
}
.footer-bar .bottom-orange{
  position:absolute;
  right:705px;          /* SAME as your code */
  width:28px;
  height:280px;         /* 🔥 192px → 280px */
  background:#f7941d;
  transform:skewX(47deg);
  bottom:-140px;        /* 🔥 thoda aur bahar */
}


.footer-bar .bottom-blue{
  position:absolute;
  right:660px;          /* SAME as your code */
  width:5px;
  height:233px;         /* 🔥 192px → 260px */
  background: linear-gradient(90deg,

    rgba(54, 173, 191, 1) 100%);
  transform:skewX(47deg);
  bottom:-130px;        /* 🔥 thoda aur bahar */
}
.footer-bar .bottom-blue2{
  position:absolute;
  right:650px;          /* SAME as your code */
  width:5px;
  height:233px;         /* 🔥 192px → 260px */
  background: linear-gradient(90deg,

    rgba(54, 173, 191, 1) 100%);
  transform:skewX(47deg);
  bottom:-130px;        /* 🔥 thoda aur bahar */
}

.footer-bar .bottom-orange2{
  position:absolute;
  right:608px;          /* SAME as your code */
  width:28px;
  height:315px;         /* 🔥 192px → 280px */
  background:#f7941d;
  transform:skewX(47deg);
  bottom:-175px;        /* 🔥 thoda aur bahar */

}

.footer-bar .last-blue{
  /* position:absolute;
  right:760px;
  width:90px;
  height:100px;
  background:#0b6fae;
  transform:skewX(47deg);
  bottom: 90px; */
/*
  position:absolute;
  right:790px;
  width:80px;
  height:120px;
  background:#0b6fae;
  bottom: 102px;
  transform:skewX(-135deg); */


   position:absolute;
  right:790px;
  bottom:90px;

  width:60px;
  height:125px;

  background:linear-gradient(
    to bottom,
    #2aa8e0,
    #0b6fae
  );

  /* 🔥 IMAGE JAISE ANGLE KE LIYE */
  transform: rotate(-48deg) skewY(-40deg);

}

.footer-bar .traingle-blue{
  position:absolute;
  right:820px;
  width:60px;
  height:70px;
  background:#0b6fae;
  bottom: 40px;
  transform:skewX(47deg);
}

.right-traingle-blue{
  position:absolute;
  right:0;
  width:140px;
  height:164px;
  background:#0b6fae;
  transform:skewX(130deg);
  bottom:-5px;
  opacity: 0.2;
}

.right-blue{
  position:absolute;
  right:93px;
  width:60px;
  height:250px;
  background:#0b6fae;
  transform:skewX(130deg);
  bottom:-5px;
  opacity: 0.2;

}

.right-orange{
   position:absolute;
  right:215px;
  width:130px;
  height:65px;
  background:#f7941d;
  transform:skewX(130deg);
  bottom:42px;
  opacity: 0.2;
}

.right-blue1{
  position:absolute;
  right:65px;
  width:6px;
  height:220px;
  background:#0a3069;
  transform:skewX(130deg);
  bottom:108px;
 opacity: 0.2;
}
.right-blue2{
  position:absolute;
  right:80px;
  width:6px;
  height:220px;
  background:#0a3069;
  transform:skewX(130deg);
  bottom:108px;
 opacity: 0.2;
}
.right-blue3{
  position:absolute;
  right:100px;
  width:40px;
  height:220px;
  background:#0a3069;
  transform:skewX(130deg);
  bottom:108px;
 opacity: 0.2;
}


</style>
<body>


<div class="header-wrap">

  <!-- TOP BLUE BAR -->
  <div class="top-bar">
    <div class="top-right">
      <span class="blue"></span>
      <span class="orange"></span>
      <span class="last"></span>
    </div>
  </div>

  <!-- HEADER CONTENT -->
  <div class="header-content">
    <div class="left">
      <div class="logo-icon">
        <img src="{{ asset($company->logo ?? 'eemot.png') }}" alt="Company Logo">
      </div>

      <div class="brand">
        <h3>{{ $company->name ?? 'EEMOTRACK INDIA' }}</h3>
        <p>{{ $company->website ?? 'www.eemotrack.com' }}</p>
      </div>
    </div>

    <div class="right">
      <p>{{ $company->email ?? 'info@eemot.com' }}</p>
      <p>{{ $company->phone ?? '+91 78578 68055' }}</p>
      <p>
        {{ $company->address_line1 ?? 'LB Palace, Kadamkuan Road' }}<br>
        {{ $company->address_line2 ?? 'NH-30, Salimpur Ahra, Patna' }}
      </p>
    </div>
  </div>

  <!-- DOUBLE LINE -->
  <div class="double-line"></div>

  <!-- TITLE ROW -->
  <div class="row">
    <div class="left">
      <strong>To:</strong><br>
      {{ $employee->full_name }}<br>
      {{ $employee->address ?? 'Employee Address' }}
    </div>

    <div class="right date">
      {{ \Carbon\Carbon::now()->format('F d, Y') }}
    </div>
  </div>

  <h2 class="letter-title">EXPERIENCE LETTER</h2>

  <p>
      This is to certify that <strong>{{ $employee->full_name }}</strong>
      was employed with
      <strong>{{ $company->name ?? 'EEMOTRACK INDIA' }}</strong>
      as a <strong>{{ $letter->designation }}</strong>
      from <strong>{{ \Carbon\Carbon::parse($letter->date_of_joining)->format('d M Y') }}</strong>
      to <strong>{{ \Carbon\Carbon::parse($letter->last_working_date)->format('d M Y') }}</strong>.
  </p>

  <p>
      During the period of employment, the employee was responsible for managing
      assigned roles and responsibilities related to the
      <strong>{{ $letter->department }}</strong> department. Their key duties included
      handling daily operational tasks, coordinating with team members,
      maintaining professional communication with clients/vendors,
      and ensuring timely completion of assigned projects.
  </p>

  <p>
      Throughout their tenure, <strong>{{ $employee->full_name }}</strong> demonstrated strong
      dedication, sincerity, and a professional attitude towards work.
      They showed the ability to work both independently and as part of a team.
      Their performance met our expectations and contributed positively
      to the growth and productivity of the organization.
  </p>

  <p>
      The employee maintained good conduct and discipline during their employment
      and adhered to company policies and guidelines.
      We found them to be reliable, punctual, and committed to delivering quality work.
  </p>

  @if(isset($letter->last_drawn_salary))
  <p>
      The last drawn salary of the employee was
      <strong>₹ {{ number_format($letter->last_drawn_salary,2) }}</strong> per annum.
  </p>
  @endif

  @if(isset($increments) && $increments->count() > 0)
  <div style="margin-top:15px;">
      <strong>Increment History:</strong><br><br>

      @foreach($increments as $inc)
          Date: {{ \Carbon\Carbon::parse($inc->increment_month)->format('M Y') }} <br>
          Previous Salary: ₹ {{ number_format($inc->old_gross_salary,2) }} <br>
          Revised Salary: ₹ {{ number_format($inc->new_gross_salary,2) }} <br>
          Position: {{ $inc->new_position ?? $letter->designation }}
          <br><br>
      @endforeach
  </div>
  @endif

  <p>
      We sincerely appreciate their contribution to
      <strong>{{ $company->name ?? 'the organization' }}</strong>
      and wish them continued success in all their future professional endeavors.
  </p>

  <div class="signature">
    <p>Sincerely,</p>

    <p class="name">
      {{ $company->authorized_person ?? 'HR Manager' }}<br>
      <span>{{ $company->authorized_designation ?? 'Human Resource Department' }}</span>
    </p>
  </div>

  <!-- FOOTER BAR -->
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

</div>

</body>
</html>


