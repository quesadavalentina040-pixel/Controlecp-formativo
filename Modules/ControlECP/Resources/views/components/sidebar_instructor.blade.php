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
                ['icon' => 'fa-calendar-days', 'label' => 'Cronograma', 'route' => 'controlecp.instructor.cronograma'],
                ['icon' => 'fa-star', 'label' => 'Momentos', 'route' => 'controlecp.instructor.momentos'],
                ['icon' => 'fa-list-check', 'label' => 'Actividad', 'route' => 'controlecp.instructor.actividades'],
                ['icon' => 'fa-user-check', 'label' => 'Asistencias', 'route' => 'controlecp.instructor.asistencia'],
                ['icon' => 'fa-comments', 'label' => 'Asesorías', 'route' => 'controlecp.instructor.asesorias'],
                ['icon' => 'fa-certificate', 'label' => 'Certificados', 'route' => 'controlecp.instructor.certificados'],
                ['icon' => 'fa-file-contract', 'label' => 'POE', 'route' => 'controlecp.instructor.poe'],
                ['icon' => 'fa-hand-holding-hand', 'label' => 'Acompañamiento', 'route' => 'controlecp.instructor.acompaniamiento'],
            ];
        @endphp

        @foreach ($items as $item)
            @php
                $active = $item['route'] && request()->routeIs($item['route']);
            @endphp
            <li class="nav-item">
                <a href="{{ $item['route'] && Route::has($item['route']) ? route($item['route']) : '#' }}"
                   class="nav-link ecp-sidebar-link d-flex align-items-center gap-2 px-3 py-2 rounded-3 {{ $active ? 'ecp-active' : '' }}"
                   style="font-weight: {{ $active ? '700' : '500' }}; transition: all .2s ease;">
                    <i class="fas {{ $item['icon'] }}"></i> {{ $item['label'] }}
                </a>
            </li>
        @endforeach
    </ul>
</div>
