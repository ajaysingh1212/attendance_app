
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Terms and Conditions - Eemot Clocking App</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f6f8;
            color: #222;
            line-height: 1.7;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .card {
            background: #fff;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        h1 {
            margin-top: 0;
            text-align: center;
            color: #111;
        }

        h2 {
            margin-top: 30px;
            color: #222;
        }

        p {
            margin-bottom: 15px;
        }

        ul {
            padding-left: 25px;
        }

        .updated {
            text-align: center;
            color: #777;
            margin-bottom: 30px;
        }

        @media (max-width: 600px) {
            .container {
                margin: 20px auto;
                padding: 0 12px;
            }

            .card {
                padding: 22px;
            }

            h1 {
                font-size: 26px;
            }

            h2 {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>

<div class="container">
    <div class="card">

        <h1>Terms and Conditions</h1>

        <div class="updated">
            Last Updated: {{ date('d F Y') }}
        </div>

        <!-- ========================================= -->
        <!-- ADD YOUR TERMS AND CONDITIONS CONTENT HERE -->
        <!-- ========================================= -->

        <h2>1. Introduction</h2>

        <p>
            Welcome to Eemot Clocking App. These Terms and Conditions
            govern your use of the application and related services.
        </p>

        <h2>2. Use of the Application</h2>

        <p>
            The application is intended for authorized employees and
            users for attendance, employee management and related
            business activities.
        </p>

        <h2>3. User Responsibilities</h2>

        <ul>
            <li>Users must provide accurate information.</li>
            <li>Users must keep their login credentials secure.</li>
            <li>Users must use the application only for authorized purposes.</li>
        </ul>

        <h2>4. Attendance</h2>

        <p>
            Attendance features may use device camera, location and
            verification mechanisms to record and validate attendance.
        </p>

        <h2>5. Changes to These Terms</h2>

        <p>
            We may update these Terms and Conditions from time to time.
            Any changes will be published on this page.
        </p>

        <h2>6. Contact Us</h2>

        <p>
            If you have any questions regarding these Terms and Conditions,
            please contact your organization or the Eemot Clocking App support team.
        </p>

    </div>
</div>

</body>
</html>