<!doctype html>
<html lang="{{ app()->getLocale() }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Sistemas de Ventas - AUDITA">
    <meta name="author" content="Jorge Francelli Saldaña Reyes">
    <link rel="icon" href="{{ asset('public/img/icono/favicon_audita.png') }}">

    <title>AUDITA - Selección de Acceso</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/lib/material-design-icons/css/material-design-iconic-font.min.css') }} "/>
    
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
        overflow-y: auto;
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

      .access-container {
        position: relative;
        z-index: 2;
        width: 100%;
        max-width: 450px;
        padding: 20px;
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
        transition: transform 0.3s ease;
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
        transition: transform 0.3s ease;
      }

      .welcome-text p {
        color: var(--text-main);
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 32px;
      }

      .company-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
        padding-right: 0;
      }

      .company-item {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        padding: 18px 20px;
        color: var(--text-main);
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        justify-content: space-between;
      }

      .company-item:hover {
        background: rgba(255, 255, 255, 0.15);
        border-color: var(--primary-color);
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
      }

      /* Company Category Colors as full box backgrounds */
      .color-iin { background-color: #cc0000 !important; color: white !important; }
      .color-ico { background-color: #34a853 !important; color: white !important; }
      .color-itr { background-color: #7f8c8d !important; color: white !important; }
      .color-ich { background-color: #0ea5e9 !important; color: white !important; }
      .color-iaa { background-color: #6366f1 !important; color: white !important; }

      .company-item i {
        font-size: 18px;
        opacity: 0;
        transition: all 0.2s ease;
        transform: translateX(-10px);
      }

      .company-item:hover i {
        opacity: 1;
        transform: translateX(0);
        color: white;
      }

      .footer-section {
        margin-top: 40px;
      }

      .brand-accent {
        font-weight: 700;
        color: var(--primary-color);
        letter-spacing: 0.15em;
        font-size: 12px;
      }

      /* Animations */
      @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
      }

      .glass-card {
        animation: fadeIn 0.6s ease-out;
      }

      .company-item {
        animation: fadeIn 0.6s ease-out both;
      }
    </style>
  </head>
  <body>

    <div class="access-container">
      <div class="glass-card">

      <div class="welcome-text">
        <p>Seleccione la empresa para continuar</p>
      </div>

      <div class="company-list">
        @foreach($accesos as $index => $item)
          @php 
            $colorClass = $funcion->funciones->color_empresa($item->empresa_id);
          @endphp
          <div class="company-item empresa-centro {{ $colorClass }}" 
               data-empresa="{{$item->empresa_id}}"
               style="animation-delay: {{ 0.2 + ($index * 0.1) }}s">
            <span>{{$item->empresa->NOM_EMPR}}</span>
            <i class="zmdi zmdi-chevron-right"></i>
          </div>
        @endforeach
      </div>

      <div class="footer-section">
        <div class="brand-accent">AUDITA</div>
      </div>
    </div>
  </div>

    <input type='hidden' id='carpeta' value="{{$capeta}}"/>

    <script src="{{ asset('public/lib/jquery/jquery.min.js') }}" type="text/javascript"></script>
    <script type="text/javascript">
      $(document).ready(function(){
        var carpeta = $("#carpeta").val();

        $(".company-list").on('click', '.empresa-centro', function(e) {
          var empresa_id = $(this).attr('data-empresa');
          
          // Add a small delay for the ripple effect/click feel
          $(this).css('transform', 'scale(0.98)');
          
          setTimeout(function() {
            window.location = carpeta + "/accesobienvenido/" + empresa_id;
          }, 150);
        });
      });
    </script>
  </body>
</html>