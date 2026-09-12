<nav class="navbar navbar-expand-lg" style="background-color: #00324D; padding: 0.8rem 1.5rem;">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center gap-2 text-white" href="{{ route('controlecp.index') }}">
            <img src="{{ asset('general/assets/img/logo-ecp.png') }}" alt="Logo ECP"
                 style="height: 36px; width: 36px; object-fit: contain; background: #fff; border-radius: 50%; padding: 2px;">
            <span class="fw-bold">Control ECP</span>
        </a>

        <div class="dropdown ms-auto">
            <button class="btn btn-sena-outline-white dropdown-toggle d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-user-circle"></i> {{ Str::limit(Auth::user()->full_name ?? Auth::user()->name, 18) }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2 mt-2">
                <li class="px-3 py-2 border-bottom">
                    <small class="text-muted d-block">Sesión iniciada como:</small>
                    <strong>{{ Auth::user()->email }}</strong>
                </li>
                <li>
                    <a class="dropdown-item rounded-3 py-2 my-1" href="{{ url('/') }}">
                        <i class="fas fa-cubes me-2"></i> Módulos ERP SENA
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item rounded-3 py-2 text-danger" href="{{ route('logout') }}">
                        <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>