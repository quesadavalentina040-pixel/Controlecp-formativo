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
    .ecp-submenu-toggle {
        cursor: pointer;
    }
    .ecp-submenu-toggle .ecp-chevron {
        margin-left: auto;
        transition: transform .2s ease;
    }
    .ecp-sublink {
        font-size: 0.85rem;
        padding-left: 2.4rem !important;
    }

    /* Toggle 100% CSS, sin depender de JS de Bootstrap */
    .ecp-submenu-checkbox {
        display: none;
    }
    .ecp-submenu-body {
        max-height: 0;
        overflow: hidden;
        transition: max-height .25s ease;
    }
    .ecp-submenu-checkbox:checked ~ .ecp-submenu-body {
        max-height: 500px;
    }
    .ecp-submenu-checkbox:checked ~ label .ecp-chevron {
        transform: rotate(90deg);
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
                ['icon' => 'fa-users', 'label' => 'Usuarios', 'route' => 'controlecp.administrador.usuarios'],
                ['icon' => 'fa-id-card', 'label' => 'Fichas', 'route' => 'controlecp.administrador.fichas'],
                ['icon' => 'fa-star', 'label' => 'Momentos', 'route' => 'controlecp.administrador.momentos'],
                ['icon' => 'fa-list-check', 'label' => 'Actividades', 'route' => 'controlecp.administrador.actividades'],
                ['icon' => 'fa-calendar-days', 'label' => 'Cronograma', 'route' => 'controlecp.administrador.cronograma'],
                ['icon' => 'fa-user-check', 'label' => 'Asistencia', 'route' => 'controlecp.administrador.asistencia'],
                [
                    'icon' => 'fa-folder-open',
                    'label' => 'Repositorio',
                    // Un padre con "children" NUNCA debe tener 'route' propio,
                    // porque esa ruta no existe (solo existen las 4 rutas hijas).
                    'route' => NULL,
                    'children' => [
                        ['icon' => 'fa-file-medical', 'label' => 'Historias', 'route' => 'controlecp.administrador.repositorio.historia'],
                        ['icon' => 'fa-square-poll-vertical', 'label' => 'Encuestas', 'route' => 'controlecp.administrador.repositorio.encuesta'],
                        ['icon' => 'fa-table', 'label' => 'Tabulación', 'route' => 'controlecp.administrador.repositorio.tabulacion'],
                        ['icon' => 'fa-file-contract', 'label' => 'POE', 'route' => 'controlecp.administrador.repositorio.poe'],
                    ],
                ],
                ['icon' => 'fa-comments', 'label' => 'Asesorías', 'route' => 'controlecp.administrador.asesorias'],
                ['icon' => 'fa-certificate', 'label' => 'Certificados', 'route' => 'controlecp.administrador.certificados'],
                ['icon' => 'fa-boxes-stacked', 'label' => 'Inventario', 'route' => 'controlecp.administrador.inventario'],
                ['icon' => 'fa-comment-dots', 'label' => 'PQR', 'route' => 'controlecp.administrador.pqr'],
            ];
        @endphp

        @foreach ($items as $index => $item)
            @php
                $hasChildren = !empty($item['children']);
                $childActive = $hasChildren
                    ? collect($item['children'])->contains(fn($c) => $c['route'] && (request()->routeIs($c['route']) || request()->is('control-ecp/administrador/repositorio*')))
                    : false;

                $isRepositoryItem = $item['label'] === 'Repositorio';
                $repositoryExpanded = $isRepositoryItem
                    && (request()->routeIs('controlecp.administrador.repositorio.*') || request()->is('control-ecp/administrador/repositorio*'));

                $active = ($item['route'] && request()->routeIs($item['route'])) || ($isRepositoryItem && $repositoryExpanded) || $childActive;
            @endphp

            @if ($hasChildren)
                <li class="nav-item">
                    <input type="checkbox" id="ecp-submenu-{{ $index }}" class="ecp-submenu-checkbox" {{ $childActive || $repositoryExpanded ? 'checked' : '' }}>
                    <label for="ecp-submenu-{{ $index }}"
                           class="nav-link ecp-sidebar-link ecp-submenu-toggle d-flex align-items-center gap-2 px-3 py-2 rounded-3 mb-0 {{ $active ? 'ecp-active' : '' }}"
                           style="font-weight: {{ $active ? '700' : '500' }}; transition: all .2s ease;">
                        <i class="fas {{ $item['icon'] }}"></i> {{ $item['label'] }}
                        <i class="fas fa-chevron-right ecp-chevron" style="font-size: 0.7rem;"></i>
                    </label>
                    <div class="ecp-submenu-body">
                        <ul class="nav flex-column gap-1 mt-1">
                            @foreach ($item['children'] as $child)
                                @php $childIsActive = $child['route'] && request()->routeIs($child['route']); @endphp
                                <li class="nav-item">
                                    <a href="{{ $child['route'] ? route($child['route']) : '#' }}"
                                       class="nav-link ecp-sidebar-link ecp-sublink d-flex align-items-center gap-2 py-2 rounded-3 {{ $childIsActive ? 'ecp-active' : '' }}"
                                       style="font-weight: {{ $childIsActive ? '700' : '500' }};">
                                        <i class="fas {{ $child['icon'] ?? 'fa-circle' }}" style="font-size: {{ isset($child['icon']) ? '0.85rem' : '0.4rem' }};"></i> {{ $child['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </li>
            @else
                <li class="nav-item">
                    <a href="{{ $item['route'] ? route($item['route']) : '#' }}"
                       class="nav-link ecp-sidebar-link d-flex align-items-center gap-2 px-3 py-2 rounded-3 {{ $active ? 'ecp-active' : '' }}"
                       style="font-weight: {{ $active ? '700' : '500' }}; transition: all .2s ease;">
                        <i class="fas {{ $item['icon'] }}"></i> {{ $item['label'] }}
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
</div>