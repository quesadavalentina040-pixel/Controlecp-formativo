@php
    $nombreUsuario = Auth::user()->full_name ?? Auth::user()->name;
    $iniciales = collect(explode(' ', $nombreUsuario))->map(fn($p) => strtoupper(substr($p, 0, 1)))->take(2)->implode('');
    $rolActual = Auth::user()->hasRole('controlecp.admin') ? 'ADMINISTRADOR'
        : (Auth::user()->hasRole('controlecp.instructor') ? 'INSTRUCTOR' : 'APRENDIZ');
@endphp

<nav class="d-flex align-items-center justify-content-between px-4 py-3 bg-white shadow-sm" style="border-bottom: 1px solid rgba(57,169,0,0.15);">
    <h5 class="fw-bold mb-0" style="color: var(--sena-pastel-text-title);">
        Panel de Inicio - {{ ucfirst(strtolower($rolActual)) }}
    </h5>

    <div class="dropdown">
        <button class="btn d-flex align-items-center gap-2 border-0" type="button" data-bs-toggle="dropdown"
                style="background-color: var(--sena-pastel-xlight); border-radius: 50px; padding: 0.35rem 1rem 0.35rem 0.35rem; transition: all 0.2s ease;">
            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                 style="width: 38px; height: 38px; background-color: #164A19;">
                {{ $iniciales }}
            </div>
            <div class="text-start d-none d-sm-block">
                <div class="fw-bold small" style="color: var(--sena-pastel-text-title);">{{ $nombreUsuario }}</div>
                <div class="text-muted" style="font-size: 0.72rem;">{{ $rolActual }}</div>
            </div>
            <i class="fas fa-chevron-down small text-muted"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2 mt-2">
            <li>
                <a class="dropdown-item rounded-3 py-2 my-1 d-flex align-items-center gap-2" href="#">
                    <i class="fas fa-user" style="color: var(--sena-pastel-primary);"></i> Mi perfil
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item rounded-3 py-2 text-danger d-flex align-items-center gap-2"
                   href="{{ route('logout', ['redirect' => route('login')]) }}">
                    <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                </a>
            </li>
        </ul>
    </div>
</nav>