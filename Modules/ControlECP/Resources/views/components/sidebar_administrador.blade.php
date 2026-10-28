{{-- Vista: Navegación lateral disponible para el perfil administrador. --}}
<style>
    .ecp-sidebar-link {
        color: rgba(255, 255, 255, 0.85);
        background-color: transparent;
        text-decoration: none;
    }
    .ecp-sidebar-link:hover {
        background-color: rgba(57, 169, 0, 0.2) !important;
        color: #62E31D !important;
    }
    .ecp-sidebar-link.ecp-active {
        background-color: #39A900 !important;
        color: #FFFFFF !important;
        box-shadow: 0 4px 14px rgba(57, 169, 0, 0.4);
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

<div style="width: 260px; min-height: 100%; background-color: #001A29; padding: 1.5rem 0.9rem; border-right: 1px solid rgba(57, 169, 0, 0.2);">

    <div class="d-flex flex-column align-items-center text-center gap-2 px-2 mb-4">
        <img src="{{ asset('general/assets/img/logo-ecp.png') }}" alt="Logo ECP"
             style="height: 64px; width: 64px; object-fit: contain; background: #fff; border-radius: 50%; padding: 4px; box-shadow: 0 2px 10px rgba(57, 169, 0, 0.3); border: 2px solid #39A900;">
        <div>
            <span class="d-block fw-bold text-white" style="font-size: 1rem; line-height: 1.1;">Control ECP</span>
            <span class="d-block" style="font-size: 0.72rem; color: #62E31D;">Escuela Cultura de Paz</span>
        </div>
    </div>

    <ul class="nav flex-column gap-1">
        @php
            $items = [
                ['icon' => 'fa-house', 'label' => 'Inicio', 'route' => 'controlecp.administrador.inicio'],
                ['icon' => 'fa-users', 'label' => 'Usuarios', 'route' => null],
                ['icon' => 'fa-id-card', 'label' => 'Fichas', 'route' => null],
                ['icon' => 'fa-star', 'label' => 'Momentos', 'route' => null],
                ['icon' => 'fa-list-check', 'label' => 'Actividades', 'route' => null],
                ['icon' => 'fa-calendar-days', 'label' => 'Cronograma', 'route' => null],
                ['icon' => 'fa-user-check', 'label' => 'Asistencia', 'route' => null],
                [
                    'icon' => 'fa-folder-open',
                    'label' => 'Repositorio',
                    'route' => null,
                    'children' => [
                        ['icon' => 'fa-file-medical', 'label' => 'Historias', 'route' => 'controlecp.administrador.repositorio.historia.index'],
                        ['icon' => 'fa-square-poll-vertical', 'label' => 'Encuestas', 'route' => 'controlecp.administrador.repositorio.encuesta.index'],
                        ['icon' => 'fa-table', 'label' => 'Tabulación', 'route' => 'controlecp.administrador.repositorio.tabulacion.index'],
                        ['icon' => 'fa-file-contract', 'label' => 'POE', 'route' => 'controlecp.administrador.repositorio.poe.index'],
                    ],
                ],
                ['icon' => 'fa-comments', 'label' => 'Asesorías', 'route' => 'controlecp.administrador.asesorias.index'],
                ['icon' => 'fa-certificate', 'label' => 'Certificados', 'route' => null],
                ['icon' => 'fa-boxes-stacked', 'label' => 'Inventario', 'route' => null],
                ['icon' => 'fa-comment-dots', 'label' => 'PQRS', 'route' => null],
                ['icon' => 'fa-chart-line', 'label' => 'Reportes', 'route' => null],
                ['icon' => 'fa-bell', 'label' => 'Notificaciones', 'route' => 'controlecp.notificaciones.index'],
            ];
        @endphp

        {{-- Iteración que presenta los registros de items. --}}
        @foreach ($items as $index => $item)
            @php
                $hasChildren = !empty($item['children']);
                $childActive = $hasChildren
                    ? collect($item['children'])->contains(fn($c) => !empty($c['route']) && (request()->routeIs($c['route']) || request()->routeIs(str_replace('.index', '.*', $c['route']))))
                    : false;

                $itemActive = !empty($item['route']) && (
                    request()->routeIs($item['route']) ||
                    ($item['route'] === 'controlecp.administrador.asesorias.index' && request()->routeIs('controlecp.administrador.asesorias.*'))
                );
                $active = $itemActive || $childActive;
            @endphp

            @if ($hasChildren)
                <li class="nav-item">
                    <input type="checkbox" id="ecp-submenu-{{ $index }}" class="ecp-submenu-checkbox" {{ $childActive ? 'checked' : '' }}>
                    <label for="ecp-submenu-{{ $index }}"
                           class="nav-link ecp-sidebar-link ecp-submenu-toggle d-flex align-items-center gap-2 px-3 py-2 rounded-3 mb-0 {{ $active ? 'ecp-active' : '' }}"
                           style="font-weight: {{ $active ? '700' : '500' }}; transition: all .2s ease;">
                        <i class="fas {{ $item['icon'] }}"></i> {{ $item['label'] }}
                        <i class="fas fa-chevron-right ecp-chevron" style="font-size: 0.7rem;"></i>
                    </label>
                    <div class="ecp-submenu-body">
                        <ul class="nav flex-column gap-1 mt-1">
                            @foreach ($item['children'] as $child)
                                @php $childIsActive = !empty($child['route']) && (request()->routeIs($child['route']) || request()->routeIs(str_replace('.index', '.*', $child['route']))); @endphp
                                <li class="nav-item">
                                    <a href="{{ !empty($child['route']) && \Illuminate\Support\Facades\Route::has($child['route']) ? route($child['route']) : '#' }}"
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
                    <a href="{{ !empty($item['route']) && \Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : '#' }}"
                       class="nav-link ecp-sidebar-link d-flex align-items-center gap-2 px-3 py-2 rounded-3 {{ $active ? 'ecp-active' : '' }}"
                       style="font-weight: {{ $active ? '700' : '500' }}; transition: all .2s ease;">
                        <i class="fas {{ $item['icon'] }}"></i>
                        <span>{{ $item['label'] }}</span>
                        @if($item['label'] === 'Notificaciones')
                            <span id="ecp-notif-sidebar-badge" class="badge rounded-pill bg-danger ms-auto d-none" style="font-size: 0.7rem; font-weight: 700;">0</span>
                        @endif
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
</div>