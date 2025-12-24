
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ trans('panel.site_title') }} | {{ trans('global.login') }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap');
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #1f293a;
        }
        .container {
            position: relative;
            width: 256px;
            height: 256px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container span {
            position: absolute;
            left: 0;
            width: 32px;
            height: 6px;
            background: #2c4766;
            border-radius: 8px;
            transform-origin: 128px;
            transform: scale(2.2) rotate(calc(var(--i) * (360deg / 50)));
            animation: animateBlink 3s linear infinite;
            animation-delay: calc(var(--i) * (3s / 50));
        }
        @keyframes animateBlink {
            0% { background: #0ef; }
            25% { background: #2c4766; }
        }
        .login-box {
            position: absolute;
            width: 400px;
            z-index: 10;
        }
        .login-box form {
            width: 100%;
            padding: 0 50px;
        }
        h2 {
            font-size: 2em;
            color: #0ef;
            text-align: center;
        }
        .input-box {
            position: relative;
            margin: 25px 0;
        }
        .input-box input {
            width: 100%;
            height: 50px;
            background: transparent;
            border: 2px solid #2c4766;
            outline: none;
            border-radius: 40px;
            font-size: 1em;
            color: #fff;
            padding: 0 20px;
            transition: .5s ease;
        }
        .input-box input:focus,
        .input-box input:valid {
            border-color: #0ef;
        }
        .input-box label {
            position: absolute;
            top: 50%;
            left: 20px;
            transform: translateY(-50%);
            font-size: 1em;
            color: #fff;
            pointer-events: none;
            transition: .5s ease;
        }
        .input-box input:focus ~ label,
        .input-box input:valid ~ label {
            top: 1px;
            font-size: .8em;
            background: #1f293a;
            padding: 0 6px;
            color: #0ef;
        }
        .forgot-pass {
            margin: -15px 0 10px;
            text-align: center;
        }
        .forgot-pass a {
            font-size: .85em;
            color: #fff;
            text-decoration: none;
        }
        .forgot-pass a:hover {
            text-decoration: underline;
        }
        .btn {
            width: 100%;
            height: 45px;
            background: #0ef;
            border: none;
            outline: none;
            border-radius: 40px;
            cursor: pointer;
            font-size: 1em;
            color: #1f293a;
            font-weight: 600;
        }
        .signup-link {
            margin: 20px 0 10px;
            text-align: center;
        }
        .signup-link a {
            font-size: 1em;
            color: #0ef;
            text-decoration: none;
            font-weight: 600;
        }
        .signup-link a:hover {
            text-decoration: underline;
        }
        .alert {
            margin-bottom: 15px;
            padding: 10px;
            color: #fff;
            border-radius: 6px;
            text-align: center;
        }
        .alert-info { background: #0ef; color: #000; }
        .invalid-feedback {
            color: #ff4c4c;
            font-size: 0.85em;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        @for ($i = 0; $i < 50; $i++)
            <span style="--i:{{ $i }};"></span>
        @endfor

        <div class="login-box">
            <h2>{{ trans('global.login') }}</h2>

            {{-- Success / Info Message --}}
            @if(session('message'))
                <div class="alert alert-info">{{ session('message') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email --}}
                <div class="input-box">
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                    <label>{{ trans('global.login_email') }}</label>
                    @if($errors->has('email'))
                        <div class="invalid-feedback">{{ $errors->first('email') }}</div>
                    @endif
                </div>

                {{-- Password --}}
                <div class="input-box">
                    <input type="password" id="password" name="password" required>
                    <label>{{ trans('global.login_password') }}</label>
                    @if($errors->has('password'))
                        <div class="invalid-feedback">{{ $errors->first('password') }}</div>
                    @endif
                </div>

                {{-- Forgot Password --}}
                <div class="forgot-pass">
                    @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}">
                            {{ trans('global.forgot_password') }}
                        </a>
                    @endif
                </div>

                {{-- Submit Button --}}
                <button type="submit" class="btn">{{ trans('global.login') }}</button>

                {{-- Signup Link --}}
                <div class="signup-link">
                    <a href="{{ route('register') }}">{{ trans('global.register') }}</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

