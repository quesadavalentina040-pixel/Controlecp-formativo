{{-- Vista: Navegación lateral disponible para el perfil instructor. --}}
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
    .ecp-submenu-checkbox {
        display: none;
    }
    .ecp-submenu-body {
        max-height: 0;
        overflow: hidden;
        transition: max-height .25s ease;
    }
    .ecp-submenu-checkbox:checked ~ .ecp-submenu-body {
        max-height: 300px;
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
            <span class="d-block badge text-wrap mt-1" style="font-size: 0.72rem; background-color: #39A900 !important; color: #ffffff;">Panel Instructor</span>
        </div>
    </div>

    <ul class="nav flex-column gap-1">
        @php
            $items = [
                ['icon' => 'fa-house', 'label' => 'Inicio', 'route' => 'controlecp.instructor.inicio'],
                ['icon' => 'fa-users-rectangle', 'label' => 'Fichas', 'route' => 'controlecp.instructor.fichas'],
                ['icon' => 'fa-star', 'label' => 'Momentos', 'route' => 'controlecp.instructor.momentos'],
                ['icon' => 'fa-list-check', 'label' => 'Actividad', 'route' => 'controlecp.instructor.actividades'],
                ['icon' => 'fa-calendar-days', 'label' => 'Cronograma', 'route' => 'controlecp.instructor.cronograma'],
                ['icon' => 'fa-user-check', 'label' => 'Asistencias', 'route' => 'controlecp.instructor.asistencia'],
                [
                    'icon' => 'fa-folder-open',
                    'label' => 'Repositorio',
                    'route' => null,
                    'children' => [
                        ['icon' => 'fa-book-open', 'label' => 'Historia', 'route' => 'controlecp.instructor.repositorio.historia'],
                        ['icon' => 'fa-square-poll-vertical', 'label' => 'Encuestas', 'route' => 'controlecp.instructor.repositorio.encuestas'],
                        ['icon' => 'fa-table', 'label' => 'Tabulación', 'route' => 'controlecp.instructor.repositorio.tabulacion.index'],
                        ['icon' => 'fa-file-contract', 'label' => 'POE', 'route' => 'controlecp.instructor.poe'],
                    ],
                ],
                ['icon' => 'fa-comments', 'label' => 'Asesorías', 'route' => 'controlecp.instructor.asesorias'],
                ['icon' => 'fa-certificate', 'label' => 'Certificados', 'route' => 'controlecp.instructor.certificados'],
                ['icon' => 'fa-boxes-stacked', 'label' => 'Inventario', 'route' => 'controlecp.instructor.inventario'],
                ['icon' => 'fa-comment-dots', 'label' => 'PQRS', 'route' => 'controlecp.instructor.pqr'],
                ['icon' => 'fa-chart-column', 'label' => 'Reportes', 'route' => 'controlecp.instructor.reportes'],
                ['icon' => 'fa-hand-holding-hand', 'label' => 'Acompañamiento', 'route' => 'controlecp.instructor.acompaniamiento'],
                ['icon' => 'fa-bell', 'label' => 'Notificaciones', 'route' => 'controlecp.notificaciones.index'],
            ];
        @endphp

        {{-- Iteración que presenta los registros de items. --}}
        @foreach ($items as $index => $item)
            @php
                $hasChildren = !empty($item['children']);
                $childActive = $hasChildren && collect($item['children'])->contains(
                    fn ($child) => $child['route'] && request()->routeIs($child['route'])
                );
                $active = ($item['route'] && request()->routeIs($item['route'])) || $childActive;
            @endphp
            @if ($hasChildren)
                <li class="nav-item">
                    <input type="checkbox" id="ecp-instructor-submenu-{{ $index }}" class="ecp-submenu-checkbox" {{ $childActive ? 'checked' : '' }}>
                    <label for="ecp-instructor-submenu-{{ $index }}"
                           class="nav-link ecp-sidebar-link ecp-submenu-toggle d-flex align-items-center gap-2 px-3 py-2 rounded-3 {{ $active ? 'ecp-active' : '' }}"
                           style="font-weight: {{ $active ? '700' : '500' }}; transition: all .2s ease;">
                        <i class="fas {{ $item['icon'] }}"></i> {{ $item['label'] }}
                        <i class="fas fa-chevron-right ecp-chevron" style="font-size: 0.7rem;"></i>
                    </label>
                    <div class="ecp-submenu-body">
                        <ul class="nav flex-column gap-1 mt-1">
                            @foreach ($item['children'] as $child)
                                @php $childIsActive = $child['route'] && request()->routeIs($child['route']); @endphp
                                <li class="nav-item">
                                    <a href="{{ Route::has($child['route']) ? route($child['route']) : '#' }}"
                                       class="nav-link ecp-sidebar-link ecp-sublink d-flex align-items-center gap-2 py-2 rounded-3 {{ $childIsActive ? 'ecp-active' : '' }}"
                                       style="font-weight: {{ $childIsActive ? '700' : '500' }};">
                                        <i class="fas {{ $child['icon'] }}" style="font-size: 0.85rem;"></i> {{ $child['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </li>
            @else
                <li class="nav-item">
                    <a href="{{ $item['route'] && Route::has($item['route']) ? route($item['route']) : '#' }}"
                       class="nav-link ecp-sidebar-link d-flex align-items-center gap-2 px-3 py-2 rounded-3 {{ $active ? 'ecp-active' : '' }}"
                       style="font-weight: {{ $active ? '700' : '500' }}; transition: all .2s ease;">
                        <i class="fas {{ $item['icon'] }}"></i> {{ $item['label'] }}
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
</div>
