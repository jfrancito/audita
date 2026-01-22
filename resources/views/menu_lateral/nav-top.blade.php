<nav class="navbar navbar-default navbar-fixed-top be-top-header premium-nav">
  <div class="container-fluid">
    <div class="navbar-header">
      <a href="{{ url('/bienvenido') }}" class="navbar-brand-premium">
        <img src="{{ asset('public/img/indulogo_menu.png') }}" alt="Logo">
        <span class="brand-divider"></span>
        <span class="user-id">({{strtoupper(Session::get('usuario')->name)}})</span>
      </a>
    </div>

    <div class="be-right-navbar">
      <ul class="nav navbar-nav navbar-right be-user-nav">
        <li class="dropdown">
          <a href="#" data-toggle="dropdown" role="button" aria-expanded="false" class="dropdown-toggle">
            <div class="user-avatar-wrapper">
              <img src="{{ asset('public/img/avatar1.png') }}" alt="Avatar">
              <span class="status-indicator online"></span>
            </div>
          </a>
          <ul role="menu" class="dropdown-menu">
            <li>
              <div class="user-info">
                <div class="user-name">{{Session::get('usuario')->nombre}}</div>
                <div class="user-position online">disponible</div>
              </div>
            </li>
            <li><a href="{{ url('/cambiarperfil/') }}"><span class="icon mdi mdi-settings"></span> Cambiar de perfil</a>
            </li>
            <li><a href="{{ url('/cerrarsession') }}"><span class="icon mdi mdi-power"></span> Cerrar sesión</a></li>
          </ul>
        </li>

        <li class="nav-user-info">
          <span class="user-display-name">{{Session::get('usuario')->nombre}}</span>
        </li>
      </ul>
    </div>
  </div>
</nav>