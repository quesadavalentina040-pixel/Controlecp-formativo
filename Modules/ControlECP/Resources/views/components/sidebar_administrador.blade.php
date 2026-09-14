<style>
    .ecp-sidebar-link {
        color: #1B5E20;
        background-color: transparent;
        text-decoration: none;
    }
    .ecp-sidebar-link:hover {
        background-color: #A8F59B !important;
        color: #1B5E20 !important;
    }
    .ecp-sidebar-link.ecp-active {
        background-color: #2E7D32 !important;
        color: #FFFFFF !important;
    }
</style>

<div style="width: 260px; min-height: 100%; background-color: #C8FFBE; padding: 1.5rem 0.9rem;">

    <div class="d-flex flex-column align-items-center text-center gap-2 px-2 mb-4">
        <img src="{{ asset('general/assets/img/logo-ecp.png') }}" alt="Logo ECP"
             style="height: 64px; width: 64px; object-fit: contain; background: #fff; border-radius: 50%; padding: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
        <div>
            <span class="d-block fw-bold" style="font-size: 1rem; line-height: 1.1; color: #1B5E20;">Control ECP</span>
            <span class="d-block" style="font-size: 0.72rem; color: #1B5E20;">Escuela Cultura de Paz</span>
        </div>
    </div>

    <ul class="nav flex-column gap-1">
        @php
            $items = [
                ['icon' => 'fa-house', 'label' => 'Inicio', 'route' => 'controlecp.administrador.inicio'],
                ['icon' => 'fa-users', 'label' => 'Usuarios', 'route' => NULL],
                ['icon' => 'fa-id-card', 'label' => 'Fichas', 'route' => NULL],
                ['icon' => 'fa-star', 'label' => 'Momentos', 'route' => NULL],
                ['icon' => 'fa-list-check', 'label' => 'Actividades', 'route' => NULL],
                ['icon' => 'fa-calendar-days', 'label' => 'Cronograma', 'route' => NULL],
                ['icon' => 'fa-user-check', 'label' => 'Asistencia', 'route' => NULL],
                ['icon' => 'fa-folder-open', 'label' => 'Repositorio', 'route' => NULL],
                ['icon' => 'fa-comments', 'label' => 'Asesorías', 'route' => NULL],
                ['icon' => 'fa-certificate', 'label' => 'Certificados', 'route' => NULL],
                ['icon' => 'fa-boxes-stacked', 'label' => 'Inventario', 'route' => NULL],
                ['icon' => 'fa-comment-dots', 'label' => 'PQR', 'route' => NULL],
            ];
        @endphp

        @foreach ($items as $item)
            @php $active = $item['route'] && request()->routeIs($item['route']); @endphp
            <li class="nav-item">
                <a href="{{ $item['route'] ? route($item['route']) : '#' }}"
                   class="nav-link ecp-sidebar-link d-flex align-items-center gap-2 px-3 py-2 rounded-3 {{ $active ? 'ecp-active' : '' }}"
                   style="font-weight: {{ $active ? '700' : '500' }}; transition: all .2s ease;">
                    <i class="fas {{ $item['icon'] }}"></i> {{ $item['label'] }}
                </a>
            </li>
        @endforeach
    </ul>
</div>