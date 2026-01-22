<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="Sistemas de Ventas - AUDITA">
  <meta name="author" content="Antigravity Redesign">
  <link rel="icon" href="{{ asset('public/img/icono/merge1.ico') }}">

  <title>AUDITA - Inicio de Sesión</title>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

  <!-- Icons -->
  <link rel="stylesheet" type="text/css"
    href="{{ asset('public/lib/material-design-icons/css/material-design-iconic-font.min.css') }} " />
  <link rel="stylesheet" type="text/css" href="{{ asset('public/css/font-awesome.min.css') }} " />

  <style>
    :root {
      --primary-color: #2563eb;
      --primary-hover: #1d4ed8;
      --accent-color: #FFCC00;
      --bg-overlay: rgba(15, 23, 42, 0.7);
      --glass-bg: rgba(255, 255, 255, 0.1);
      --glass-border: rgba(255, 255, 255, 0.2);
      --text-main: #ffffff;
      --text-muted: #cbd5e1;
      --error-color: #f87171;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: url("{{ asset('public/img/bg-acceso.png') }}") no-repeat center center fixed;
      background-size: cover;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }

    body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: var(--bg-overlay);
      z-index: 1;
    }

    .login-wrapper {
      position: relative;
      z-index: 2;
      width: 100%;
      max-width: 450px;
      padding: 20px;
      animation: fadeIn 0.8s ease-out;
    }

    .glass-card {
      background: var(--glass-bg);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border: 1px solid var(--glass-border);
      border-radius: 28px;
      padding: 48px 40px;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
      text-align: center;
    }

    .logo-container {
      margin-bottom: 32px;
    }

    .logo-img {
      max-width: 160px;
      height: auto;
      filter: drop-shadow(0 0 12px rgba(255, 255, 255, 0.2));
      transition: transform 0.3s ease;
    }

    .logo-img:hover {
      transform: scale(1.05);
    }

    .login-header h1 {
      color: var(--text-main);
      font-size: 26px;
      font-weight: 700;
      margin-bottom: 8px;
      letter-spacing: -0.025em;
    }

    .login-header p {
      color: var(--text-muted);
      font-size: 14px;
      margin-bottom: 40px;
    }

    .form-group {
      position: relative;
      margin-bottom: 24px;
      text-align: left;
    }

    .form-control {
      width: 100%;
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 14px;
      padding: 16px 20px;
      color: white;
      font-size: 15px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      outline: none;
    }

    .form-control:focus {
      background: rgba(255, 255, 255, 0.12);
      border-color: var(--primary-color);
      box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
    }

    .form-control::placeholder {
      color: rgba(255, 255, 255, 0.4);
    }

    .btn-submit {
      width: 100%;
      background: var(--primary-color);
      color: white;
      border: none;
      border-radius: 14px;
      padding: 16px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      margin-top: 12px;
    }

    .btn-submit:hover {
      background: var(--primary-hover);
      transform: translateY(-2px);
      box-shadow: 0 12px 20px -8px rgba(37, 99, 235, 0.5);
    }

    .btn-submit:active {
      transform: translateY(0);
    }

    .footer-section {
      margin-top: 40px;
    }

    .social-links {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin-bottom: 24px;
    }

    .social-links a {
      color: rgba(255, 255, 255, 0.5);
      font-size: 1.4rem;
      transition: all 0.3s ease;
    }

    .social-links a:hover {
      color: var(--text-main);
      transform: translateY(-4px);
    }

    .brand-accent {
      font-weight: 700;
      color: var(--primary-color);
      letter-spacing: 0.15em;
      font-size: 12px;
    }

    .alert-container {
      position: fixed;
      top: 32px;
      right: 32px;
      z-index: 1000;
      max-width: 400px;
    }

    .validate-error {
      color: var(--error-color);
      font-size: 12px;
      margin-top: 6px;
      margin-left: 4px;
      font-weight: 500;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @media (max-width: 480px) {
      .glass-card {
        padding: 40px 24px;
      }
    }
  </style>
</head>

<body>

  <div class="alert-container">
    @include('success.ajax-alert')
    @include('success.bienhecho', ['bien' => Session::get('bienhecho')])
    @include('error.erroresurl', ['error' => Session::get('errorurl')])
    @include('error.erroresbd', ['id' => Session::get('errorbd'), 'error' => Session::get('errorbd'), 'data' => '2'])
  </div>

  <div class="login-wrapper">
    <div class="glass-card">
      <div class="logo-container">
        <img src="{{ asset('public/img/indulogo.png') }}" alt="AUDITA" class="logo-img">
      </div>

      <div class="login-header">
        <h1>Plataforma AUDITA</h1>
        <p>Reportes</p>
      </div>

      <form method="POST" action="{{ url('login') }}" id="loginForm">
        {{ csrf_field() }}
        <input type="hidden" name="device_info" id='device_info'>

        <div class="form-group">
          <input id="name" name='name' type="text" required value="{{ old('name') }}" placeholder="Usuario"
            autocomplete="off" class="form-control" />
          @if($errors->has('name'))
            <div class="validate-error"><i class="zmdi zmdi-alert-circle"></i> {{ $errors->first('name') }}</div>
          @endif
        </div>

        <div class="form-group">
          <input id="password" name='password' type="password" required placeholder="Contraseña" class="form-control" />
          @if($errors->has('password'))
            <div class="validate-error"><i class="zmdi zmdi-alert-circle"></i> {{ $errors->first('password') }}</div>
          @endif
        </div>

        <button type="submit" class="btn-submit">Iniciar Sesión</button>

        <input type='hidden' id='carpeta' value="{{$capeta}}" />
        <input type="hidden" id="token" name="_token" value="{{ csrf_token() }}">
      </form>

      <div class="footer-section">
        <div class="social-links">
          <a href="https://www.facebook.com/induamericasl" target="_blank" title="Facebook"><i
              class="fa fa-facebook"></i></a>
          <a href="https://x.com/induamerica_sac?lang=es" target="_blank" title="Twitter"><i
              class="fa fa-twitter"></i></a>
          <a href="https://www.linkedin.com/company/induamerica-servicios-logisticos/" target="_blank"
            title="LinkedIn"><i class="fa fa-linkedin"></i></a>
          <a href="https://www.instagram.com/induamerica.peru?igsh=bGg0YzdrbnJxdWM=" target="_blank"
            title="Instagram"><i class="fa fa-instagram"></i></a>
        </div>
        <div class="brand-accent">AUDITA</div>
      </div>
    </div>
  </div>

  <script src="{{ asset('public/lib/jquery/jquery.min.js') }}" type="text/javascript"></script>
  <script src="{{ asset('public/lib/bootstrap/dist/js/bootstrap.min.js') }}" type="text/javascript"></script>
  <script src="{{ asset('public/lib/parsley/parsley.js') }}" type="text/javascript"></script>
  <script src="{{ asset('public/js/user/user.js') }}" type="text/javascript"></script>

  <script type="text/javascript">
    $(document).ready(function () {
      $('#loginForm').parsley();
    });
  </script>

</body>

</html>