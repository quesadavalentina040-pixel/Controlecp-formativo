@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    /* Estilos visuales acordes a la identidad SENA Control ECP */
    .stat-card-clean {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 1.25rem 1.4rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        box-shadow: 0 2px 12px rgba(0, 26, 41, 0.03);
    }
    .stat-card-clean:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 26, 41, 0.08);
    }
    .stat-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .badge-semaforo-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }
    .btn-action-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 0.85rem;
        transition: all 0.15s ease;
        border: 1px solid #E2E8F0;
        background: #FFFFFF;
    }
    .btn-action-icon:hover {
        transform: scale(1.08);
        box-shadow: 0 2px 8px rgba(0, 26, 41, 0.1);
    }
    .drag-drop-box {
        border: 2px dashed #CBD5E1;
        border-radius: 14px;
        background-color: #F8FAFC;
        padding: 1.5rem 1rem;
        text-align: center;
        transition: all 0.2s ease-in-out;
        cursor: pointer;
    }
    .drag-drop-box:hover {
        border-color: #39A900;
        background-color: #F0FDF4;
    }
    .recurso-detalle-img {
        width: 100%;
        height: 220px;
        object-fit: cover;
        border-radius: 12px;
        border: 1px solid #E2E8F0;
    }
    .thumb-mini-slot {
        width: 58px;
        height: 58px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid transparent;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .thumb-mini-slot:hover, .thumb-mini-slot.active-thumb {
        border-color: #39A900;
        transform: scale(1.05);
    }
    .nav-pills-report .nav-link {
        color: #64748B;
        font-weight: 600;
        border-radius: 9999px;
        padding: 0.45rem 1.2rem;
        font-size: 0.88rem;
    }
    .nav-pills-report .nav-link.active {
        background-color: #39A900;
        color: #FFFFFF;
    }
    .offcanvas-custom-right {
        width: 520px !important;
        max-width: 95vw;
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">

    <!-- Cabecera Institucional (Mockup Rol Administrador - Image 2) -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge px-2 py-1 small fw-bold" style="background-color: #E2F5D7; color: #39A900; font-size: 0.72rem; letter-spacing: 0.5px;">
                    ROL ADMINISTRADOR
                </span>
                <span class="text-muted small">&bull; Escuela Cultura de Paz</span>
            </div>
            <h2 class="h3 fw-bold mb-0" style="color: #001A29; font-family: 'Outfit', sans-serif;">
                Gestionar Recursos e Inventario
            </h2>
            <p class="text-muted small mb-0">Administra espacios, materiales y equipos para las actividades de la Escuela</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <!-- Botón Verificar Disponibilidad (Modal) -->
            <button type="button" class="btn btn-outline-primary rounded-pill px-3 shadow-sm btn-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalVerificarDisponibilidad">
                <i class="fas fa-calendar-check"></i>
                <span>Verificar disponibilidad</span>
            </button>

            <!-- Botón Categorías (Abre Modal de Categorías sin salir) -->
            <button type="button" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm btn-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalGestionCategorias">
                <i class="fas fa-folder-open text-primary"></i>
                <span>Categorías</span>
            </button>

            <!-- Botón Generar Reporte (Abre Modal de Reporte con Gráficas sin salir) -->
            <button type="button" class="btn btn-outline-dark rounded-pill px-3 shadow-sm btn-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalReporteInventario" onclick="inicializarGraficasReporte()">
                <i class="fas fa-chart-pie text-success"></i>
                <span>Generar reporte</span>
            </button>

            <!-- HU 16: Botón Historial de Movimientos -->
            <a href="{{ route('controlecp.administrador.inventario.movimientos') }}" class="btn btn-outline-info rounded-pill px-3 shadow-sm btn-sm d-flex align-items-center gap-2">
                <i class="fas fa-clock-rotate-left"></i>
                <span>Historial de movimientos</span>
            </a>

            <!-- Botón Movimiento Rápido (Modal) -->
            <button type="button" class="btn btn-outline-success rounded-pill px-3 shadow-sm btn-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalRegistrarMovimiento">
                <i class="fas fa-right-left"></i>
                <span>Registrar movimiento</span>
            </button>

            <!-- Botón Registrar Recurso (Abre Cajón / Drawer Lateral sin salir) -->
            <button type="button" class="btn text-white rounded-pill px-3 shadow-sm btn-sm d-flex align-items-center gap-2" style="background-color: #39A900; border-color: #39A900;" data-bs-toggle="offcanvas" data-bs-target="#drawerRegistrarRecurso">
                <i class="fas fa-plus"></i>
                <span class="fw-semibold">Registrar recurso</span>
            </button>
        </div>
    </div>

    <!-- Alertas Flash -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert" style="background-color: #f0fbf4; color: #001A29;">
            <i class="fas fa-check-circle me-2" style="color: #39A900;"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="fas fa-triangle-exclamation me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <div class="fw-bold mb-1"><i class="fas fa-circle-exclamation me-2"></i>Por favor corrige los siguientes campos:</div>
            <ul class="mb-0 small ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- 1. Tarjetas de Resumen (HU 15: Cantidad total de recursos disponibles y prestados) -->
    <div class="row g-3 mb-4">
        <!-- Tarjeta 1: Recursos Totales -->
        <div class="col-6 col-md-3">
            <div class="stat-card-clean h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-box" style="background-color: #E0F2FE; color: #0288D1;">
                        <i class="fas fa-cubes"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold" style="font-size: 0.8rem;">Recursos totales</div>
                        <div class="h3 fw-bold mb-0" style="color: #001A29;">{{ $totalRecursos }}</div>
                        <small class="text-muted" style="font-size: 0.72rem;">{{ $totalUnidades ?? 0 }} unidades en catálogo</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta 2: Unidades Disponibles (HU 15) -->
        <div class="col-6 col-md-3">
            <div class="stat-card-clean h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-box" style="background-color: #DCFCE7; color: #16A34A;">
                        <i class="fas fa-circle-check"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold" style="font-size: 0.8rem;">Recursos disponibles</div>
                        <div class="h3 fw-bold mb-0 text-success">{{ $totalDisponibles ?? $activosCount }}</div>
                        <small class="text-muted" style="font-size: 0.72rem;">Libres para actividades / asesorías</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta 3: Unidades Prestadas (HU 15) -->
        <div class="col-6 col-md-3">
            <div class="stat-card-clean h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-box" style="background-color: #E0F2FE; color: #0288D1;">
                        <i class="fas fa-hand-holding-box"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold" style="font-size: 0.8rem;">Recursos prestados</div>
                        <div class="h3 fw-bold mb-0" style="color: #0288D1;">{{ $totalPrestados ?? $asignadosHoyCount }}</div>
                        <small class="text-muted" style="font-size: 0.72rem;">En uso / pendientes de devolución</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta 4: Stock bajo / crítico -->
        <div class="col-6 col-md-3">
            <div class="stat-card-clean h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-box" style="background-color: #FFEDD5; color: #EA580C;">
                        <i class="fas fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold" style="font-size: 0.8rem;">Stock bajo / crítico</div>
                        <div class="h3 fw-bold mb-0 text-warning-emphasis">{{ $stockBajoCount }}</div>
                        <small class="text-muted" style="font-size: 0.72rem;">{{ $bajasCount ?? 0 }} dado(s) de baja</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Alerta Prominente de Devolución Incompleta (Mockup Image 1 & 2) -->
    @if(isset($alertasLista) && $alertasLista->count() > 0)
        @foreach($alertasLista as $alertaItem)
            <div class="card border-0 shadow-sm mb-3 border-start border-4 border-danger" style="background: #FFF5F5; border-radius: 12px;">
                <div class="card-body p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-danger fs-3">
                            <i class="fas fa-circle-exclamation"></i>
                        </div>
                        <div>
                            <span class="badge bg-danger text-white mb-1">Devolución incompleta</span>
                            <div class="fw-semibold text-danger small">
                                {{ $alertaItem->observacion }}
                            </div>
                            <div class="text-muted small" style="font-size: 0.75rem;">
                                Recurso: <strong>{{ $alertaItem->inventario ? $alertaItem->inventario->nombre : 'Recurso' }}</strong> &bull;
                                Fecha: {{ $alertaItem->fecha ? $alertaItem->fecha->format('d/m/Y H:i') : '' }}
                            </div>
                        </div>
                    </div>
                    @if($alertaItem->inventario)
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm" onclick="abrirDetalleModal({{ $alertaItem->inventario->id_inventario }})">
                            Ver detalle
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    @endif

    <!-- 3. Buscador y Filtros (Mockup Image 1 & 2) -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-body p-3">
            <form action="{{ route('controlecp.administrador.inventario') }}" method="GET" class="row g-2 align-items-center">
                <!-- Buscador por texto -->
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3 text-muted">
                            <i class="fas fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="buscar" value="{{ request('buscar') }}" class="form-control border-start-0 rounded-end-pill" placeholder="Buscar recurso por nombre...">
                    </div>
                </div>

                <!-- Categorías -->
                <div class="col-6 col-md-2">
                    <select name="categoria_id" class="form-select rounded-pill" onchange="this.form.submit()">
                        <option value="">Todas las categorías</option>
                        @foreach ($categorias as $cat)
                            <option value="{{ $cat->id_categoria_inventario }}" {{ request('categoria_id') == $cat->id_categoria_inventario ? 'selected' : '' }}>
                                {{ $cat->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Estados físicos -->
                <div class="col-6 col-md-2">
                    <select name="estado_fisico" class="form-select rounded-pill" onchange="this.form.submit()">
                        <option value="">Todos los estados físicos</option>
                        <option value="Bueno" {{ request('estado_fisico') == 'Bueno' ? 'selected' : '' }}>Bueno</option>
                        <option value="Regular" {{ request('estado_fisico') == 'Regular' ? 'selected' : '' }}>Regular</option>
                        <option value="Crítico" {{ request('estado_fisico') == 'Crítico' ? 'selected' : '' }}>Crítico</option>
                    </select>
                </div>

                <!-- Semáforo -->
                <div class="col-6 col-md-2">
                    <select name="semaforo" class="form-select rounded-pill" onchange="this.form.submit()">
                        <option value="">Todos los semáforos</option>
                        <option value="verde" {{ request('semaforo') == 'verde' ? 'selected' : '' }}>🟢 Disponible</option>
                        <option value="amarillo" {{ request('semaforo') == 'amarillo' ? 'selected' : '' }}>🟡 Moderado</option>
                        <option value="rojo" {{ request('semaforo') == 'rojo' ? 'selected' : '' }}>🔴 Crítico</option>
                    </select>
                </div>

                <!-- Botón Limpiar Filtros -->
                <div class="col-6 col-md-2 d-flex gap-2 justify-content-end">
                    <button type="submit" class="btn text-white rounded-pill px-3 flex-grow-1" style="background-color: #39A900;">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                    @if(request()->hasAny(['buscar', 'categoria_id', 'estado_fisico', 'semaforo', 'ver_bajas']))
                        <a href="{{ route('controlecp.administrador.inventario') }}" class="btn btn-outline-secondary rounded-pill" title="Limpiar filtros">
                            <i class="fas fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- HU 18: Pestañas de Navegación: Recursos Activos vs Recursos Dados de Baja -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <ul class="nav nav-pills gap-2" role="tablist">
            <li class="nav-item">
                <a href="{{ route('controlecp.administrador.inventario') }}" class="nav-link rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-2 {{ request('ver_bajas') != '1' ? 'active' : '' }}" style="{{ request('ver_bajas') != '1' ? 'background-color: #39A900; color: #FFFFFF;' : 'background-color: #F1F5F9; color: #475569;' }}">
                    <i class="fas fa-boxes-stacked"></i>
                    <span>Recursos activos</span>
                    <span class="badge {{ request('ver_bajas') != '1' ? 'bg-white text-success' : 'bg-secondary text-white' }} rounded-pill">{{ $activosCount }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('controlecp.administrador.inventario', ['ver_bajas' => 1]) }}" class="nav-link rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-2 {{ request('ver_bajas') == '1' ? 'active' : '' }}" style="{{ request('ver_bajas') == '1' ? 'background-color: #DC2626; color: #FFFFFF;' : 'background-color: #F1F5F9; color: #475569;' }}">
                    <i class="fas fa-ban"></i>
                    <span>Recursos dados de baja</span>
                    <span class="badge {{ request('ver_bajas') == '1' ? 'bg-white text-danger' : 'bg-danger text-white' }} rounded-pill">{{ $bajasCount }}</span>
                </a>
            </li>
        </ul>

        @if(request('ver_bajas') == '1')
            <div class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill border border-danger-subtle small fw-semibold">
                <i class="fas fa-circle-exclamation me-1"></i> Visualizando recursos dados de baja (Inactivos). El historial de movimientos permanece conservado.
            </div>
        @else
            <div class="text-muted small">
                Mostrando <strong class="text-dark">{{ $recursos->total() }}</strong> recurso(s) activo(s)
            </div>
        @endif
    </div>

    <!-- 4. Tabla de Recursos (Exacta del Mockup Admin - Image 1 & 2) -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color: #F8FAFC; color: #001A29;" class="small fw-bold border-bottom">
                        <tr>
                            <th class="ps-4 py-3" style="width: 70px;">Foto</th>
                            <th class="py-3">Nombre</th>
                            <th class="py-3">Categoría</th>
                            <th class="py-3">Tipo</th>
                            @if(request('ver_bajas') == '1')
                                <th class="py-3">Motivo de baja</th>
                                <th class="py-3 text-center">Fecha de baja</th>
                            @else
                                <th class="py-3 text-center">Stock / Cantidad</th>
                                <th class="py-3 text-center">Estado físico</th>
                                <th class="py-3 text-center">Semáforo</th>
                            @endif
                            <th class="text-end pe-4 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($recursos as $recurso)
                            @php
                                $foto = $recurso->fotos->first();
                                $semaforoInfo = $recurso->semaforo_badge;
                                $estadoFisicoInfo = $recurso->estado_fisico_badge;
                                $tipoInfo = $recurso->tipo_recurso_badge;
                            @endphp
                            <tr class="{{ $recurso->estado === 'Inactivo' ? 'bg-light opacity-90' : '' }}">
                                <!-- 1. Foto -->
                                <td class="ps-4 py-2">
                                    @if ($foto && $foto->ruta)
                                        <img src="{{ asset($foto->ruta) }}" alt="{{ $recurso->nombre }}" class="rounded-3 shadow-sm border" style="width: 44px; height: 44px; object-fit: cover;">
                                    @else
                                        <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-muted border" style="width: 44px; height: 44px;">
                                            <i class="fas fa-box text-secondary"></i>
                                        </div>
                                    @endif
                                </td>

                                <!-- 2. Nombre del Recurso -->
                                <td>
                                    <div class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                                        {{ $recurso->nombre }}
                                        @if($recurso->estado === 'Inactivo')
                                            <span class="badge bg-danger rounded-pill small" style="font-size: 0.65rem;">De Baja</span>
                                        @endif
                                    </div>
                                    <div class="text-muted small text-truncate" style="max-width: 240px; font-size: 0.75rem;">
                                        {{ $recurso->observaciones ?: 'Sin observaciones adicionales' }}
                                    </div>
                                </td>

                                <!-- 3. Categoría -->
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1 small">
                                        {{ $recurso->categoria ? $recurso->categoria->nombre : 'Sin categoría' }}
                                    </span>
                                </td>

                                <!-- 4. Tipo (Espacio, Material, Equipo) -->
                                <td>
                                    <span class="badge px-2 py-1 rounded-pill small border" style="background-color: {{ $tipoInfo['bg'] }}; color: {{ $tipoInfo['text'] }}; border-color: {{ $tipoInfo['border'] }} !important;">
                                        <i class="{{ $tipoInfo['icon'] }} me-1"></i>{{ $recurso->tipo_recurso }}
                                    </span>
                                </td>

                                @if(request('ver_bajas') == '1')
                                    <!-- HU 18: Motivo de baja -->
                                    <td>
                                        <div class="small fw-semibold text-danger">
                                            <i class="fas fa-ban me-1"></i>{{ $recurso->motivo_baja ?: 'Inactivado' }}
                                        </div>
                                    </td>

                                    <!-- HU 18: Fecha de baja -->
                                    <td class="text-center text-muted small text-nowrap">
                                        {{ $recurso->fecha_baja ? $recurso->fecha_baja->format('d/m/Y H:i') : ($recurso->updated_at ? $recurso->updated_at->format('d/m/Y H:i') : '-') }}
                                    </td>
                                @else
                                    <!-- 5. Stock / Cantidad -->
                                    <td class="text-center">
                                        <span class="fw-bold fs-6 {{ $recurso->cantidad_disponible <= $recurso->umbral_critico ? 'text-danger' : 'text-dark' }}">
                                            {{ $recurso->cantidad_disponible }}
                                        </span>
                                        <span class="text-muted small">de {{ $recurso->cantidad_total }}</span>
                                    </td>

                                    <!-- 6. Estado Físico -->
                                    <td class="text-center">
                                        <span class="badge rounded-pill px-2 py-1 small" style="background-color: {{ $estadoFisicoInfo['bg'] }}; color: {{ $estadoFisicoInfo['text'] }};">
                                            <i class="{{ $estadoFisicoInfo['icon'] }} me-1"></i>{{ $recurso->estado_fisico ?? 'Bueno' }}
                                        </span>
                                    </td>

                                    <!-- 7. Semáforo (Dot + Label) -->
                                    <td class="text-center">
                                        @if($recurso->semaforo === 'verde')
                                            <span class="badge rounded-pill px-2 py-1 border small" style="background-color: #ECFDF5; color: #065F46; border-color: #A7F3D0 !important;">
                                                <span class="badge-semaforo-dot" style="background-color: #10B981;"></span>Disponible
                                            </span>
                                        @elseif($recurso->semaforo === 'amarillo')
                                            <span class="badge rounded-pill px-2 py-1 border small" style="background-color: #FFFBEB; color: #92400E; border-color: #FDE68A !important;">
                                                <span class="badge-semaforo-dot" style="background-color: #F59E0B;"></span>Moderado
                                            </span>
                                        @else
                                            <span class="badge rounded-pill px-2 py-1 border small" style="background-color: #FEF2F2; color: #991B1B; border-color: #FECACA !important;">
                                                <span class="badge-semaforo-dot" style="background-color: #EF4444;"></span>Crítico
                                            </span>
                                        @endif
                                    </td>
                                @endif

                                <!-- 8. Acciones -->
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1">
                                        <!-- Ver Detalle (Abre Modal Detalle sin salir) -->
                                        <button type="button" class="btn btn-action-icon" title="Ver Detalle" onclick="abrirDetalleModal({{ $recurso->id_inventario }})">
                                            <i class="fas fa-eye text-primary"></i>
                                        </button>

                                        @if($recurso->estado !== 'Inactivo')
                                            <!-- Editar (Abre Modal Editar sin salir) -->
                                            <button type="button" class="btn btn-action-icon" title="Editar recurso" onclick="abrirEditarModal({{ $recurso->id_inventario }})">
                                                <i class="fas fa-pen text-success"></i>
                                            </button>

                                            <!-- Registrar Movimiento Rápido -->
                                            <button type="button" class="btn btn-action-icon" title="Registrar Entrega / Devolución" onclick="abrirMovimientoRapido({{ $recurso->id_inventario }}, '{{ addslashes($recurso->nombre) }}', {{ $recurso->cantidad_disponible }})">
                                                <i class="fas fa-right-left text-info"></i>
                                            </button>

                                            <!-- Dar de Baja -->
                                            <button type="button" class="btn btn-action-icon" title="Dar de baja" onclick="abrirModalBaja({{ $recurso->id_inventario }}, '{{ addslashes($recurso->nombre) }}', {{ $recurso->cantidad_disponible }})">
                                                <i class="fas fa-ban text-danger"></i>
                                            </button>
                                        @else
                                            <!-- Historial de movimientos del recurso dado de baja -->
                                            <a href="{{ route('controlecp.administrador.inventario.movimientos', ['buscar' => $recurso->nombre]) }}" class="btn btn-action-icon" title="Ver historial de movimientos">
                                                <i class="fas fa-clock-rotate-left text-info"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-boxes-stacked fa-3x mb-3 text-muted opacity-50"></i>
                                    @if(request('ver_bajas') == '1')
                                        <p class="mb-1 fw-bold fs-5" style="color: #001A29;">No hay recursos dados de baja en el inventario</p>
                                        <small class="text-muted d-block mb-3">Todos los recursos registrados se encuentran activos.</small>
                                        <a href="{{ route('controlecp.administrador.inventario') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-sm">
                                            <i class="fas fa-arrow-left me-1"></i> Ver recursos activos
                                        </a>
                                    @else
                                        <p class="mb-1 fw-bold fs-5" style="color: #001A29;">No se encontraron recursos en el inventario</p>
                                        <small class="text-muted d-block mb-3">Registra los recursos pedagógicos para las actividades y asesorías de Cultura de Paz.</small>
                                        <button type="button" class="btn btn-sm text-white rounded-pill px-3 shadow-sm" style="background-color: #39A900;" data-bs-toggle="offcanvas" data-bs-target="#drawerRegistrarRecurso">
                                            <i class="fas fa-plus me-1"></i> Registrar Primer Recurso
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación con texto 'Mostrando X - Y de Z' -->
            <div class="p-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="small text-muted">
                    Mostrando {{ $recursos->firstItem() ?? 0 }} - {{ $recursos->lastItem() ?? 0 }} de {{ $recursos->total() }} recursos
                </div>
                <div>
                    {{ $recursos->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- =================================================================== -->
<!-- 1. DRAWER LATERAL: REGISTRAR RECURSO                                -->
<!-- =================================================================== -->
<div class="offcanvas offcanvas-end offcanvas-custom-right shadow-lg border-0" tabindex="-1" id="drawerRegistrarRecurso" aria-labelledby="drawerRegistrarRecursoLabel">
    <div class="offcanvas-header border-bottom py-3 px-4" style="background-color: #F8FAFC;">
        <h5 class="offcanvas-title fw-bold" id="drawerRegistrarRecursoLabel" style="color: #001A29;">
            <i class="fas fa-plus-circle me-2" style="color: #39A900;"></i>Registrar recurso
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form action="{{ route('controlecp.administrador.inventario.store') }}" method="POST" enctype="multipart/form-data" id="formDrawerRecurso">
            @csrf
            <div class="mb-3">
                <label for="drawerNombre" class="form-label small fw-semibold text-dark">Nombre del recurso <span class="text-danger">*</span></label>
                <input type="text" name="nombre" id="drawerNombre" class="form-control rounded-3" placeholder="Nombre del recurso" required maxlength="255">
            </div>
            <div class="mb-3">
                <label for="drawerCategoria" class="form-label small fw-semibold text-dark">Categoría <span class="text-danger">*</span></label>
                <select name="id_categoria_inventario" id="drawerCategoria" class="form-select rounded-3" required>
                    <option value="">Selecciona una categoría</option>
                    @foreach ($categorias as $cat)
                        <option value="{{ $cat->id_categoria_inventario }}">{{ $cat->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label for="drawerCantidad" class="form-label small fw-semibold text-dark">Cantidad o capacidad <span class="text-danger">*</span></label>
                    <input type="number" name="cantidad_total" id="drawerCantidad" class="form-control rounded-3" min="1" value="1" required>
                </div>
                <div class="col-6">
                    <label for="drawerEstadoFisico" class="form-label small fw-semibold text-dark">Estado físico <span class="text-danger">*</span></label>
                    <select name="estado_fisico" id="drawerEstadoFisico" class="form-select rounded-3" required>
                        <option value="Bueno" selected>Bueno</option>
                        <option value="Regular">Regular</option>
                        <option value="Crítico">Crítico</option>
                    </select>
                </div>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label for="drawerUmbralReg" class="form-label small fw-semibold text-muted">Stock moderado (Amarillo)</label>
                    <input type="number" name="umbral_regular" id="drawerUmbralReg" class="form-control rounded-3" placeholder="Ej: 3">
                </div>
                <div class="col-6">
                    <label for="drawerUmbralCrit" class="form-label small fw-semibold text-muted">Stock mínimo (Rojo)</label>
                    <input type="number" name="umbral_critico" id="drawerUmbralCrit" class="form-control rounded-3" placeholder="Ej: 1">
                </div>
            </div>
            <div class="mb-3">
                <label for="drawerObs" class="form-label small fw-semibold text-dark">Observación</label>
                <textarea name="observaciones" id="drawerObs" rows="2" class="form-control rounded-3" placeholder="Observaciones adicionales"></textarea>
            </div>
            <div class="mb-4">
                <label class="form-label small fw-semibold text-dark">Fotografías (JPG o PNG) <span class="text-danger">*</span></label>
                <div class="drag-drop-box" id="dropAreaDrawer" onclick="document.getElementById('fotosDrawerInput').click()">
                    <i class="fas fa-cloud-arrow-up fa-2x mb-1" style="color: #39A900;"></i>
                    <div class="small fw-semibold text-dark">Haz clic para subir una imagen o arrástrala aquí</div>
                    <div class="text-muted" style="font-size: 0.72rem;">Formatos: JPG o PNG (máx. 5MB)</div>
                    <input type="file" name="fotos[]" id="fotosDrawerInput" class="d-none" multiple accept="image/jpeg,image/png" required onchange="previewDrawerFotos(this.files)">
                </div>
                <div id="previewDrawerContainer" class="row g-2 mt-2"></div>
            </div>
            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="offcanvas">Cancelar</button>
                <button type="submit" class="btn text-white rounded-pill px-4 shadow-sm" style="background-color: #39A900; border-color: #39A900;">
                    Guardar recurso
                </button>
            </div>
        </form>
    </div>
</div>

<!-- =================================================================== -->
<!-- 2. MODAL: DETALLE DEL RECURSO                                       -->
<!-- =================================================================== -->
<div class="modal fade" id="modalDetalleRecurso" tabindex="-1" aria-labelledby="modalDetalleTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom py-3 px-4" style="background-color: #F8FAFC;">
                <h5 class="modal-title fw-bold" id="modalDetalleTitle" style="color: #001A29;">Detalle del recurso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-12 col-lg-5">
                        <div class="card border-0 shadow-sm p-3 rounded-4 bg-light">
                            <img src="" id="detFotoPrincipal" class="recurso-detalle-img mb-3 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="h5 fw-bold mb-0 text-dark" id="detNombre">Cargando...</h4>
                                <span class="badge rounded-pill px-3 py-2 small" id="detBadgeSemaforo">Disponible</span>
                            </div>
                            <div class="small border-top border-bottom py-2 mb-3">
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted">Categoría:</span>
                                    <strong class="text-dark" id="detCategoria">-</strong>
                                </div>
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted">Cantidad:</span>
                                    <strong class="text-dark" id="detCantidad">-</strong>
                                </div>
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted">Estado físico:</span>
                                    <span id="detEstadoFisico" class="badge rounded-pill px-2 py-1">-</span>
                                </div>
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted">Semáforo:</span>
                                    <span id="detSemaforoTexto" class="fw-semibold">-</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <span class="text-muted small d-block mb-1">Observación:</span>
                                <div class="p-2 rounded bg-white border small text-muted" id="detObservacion">Sin observaciones.</div>
                            </div>
                            <div class="mb-3">
                                <span class="text-muted small d-block mb-2">Fotos:</span>
                                <div class="d-flex flex-wrap gap-2" id="detFotosMiniaturas"></div>
                            </div>
                            <div class="d-flex flex-wrap gap-2 pt-2 border-top" id="detAccionesBar">
                                <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 flex-grow-1" id="detBtnEditar">
                                    <i class="fas fa-pen me-1"></i> Editar
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 flex-grow-1" id="detBtnEntrega">
                                    <i class="fas fa-arrow-up me-1"></i> Entrega
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 flex-grow-1" id="detBtnDevolucion">
                                    <i class="fas fa-arrow-rotate-left me-1"></i> Devolución
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 flex-grow-1" id="detBtnBaja">
                                    <i class="fas fa-ban me-1"></i> Dar de baja
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-7">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-header bg-white border-0 pt-3 px-4 pb-0">
                                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                                    <li class="nav-item">
                                        <button class="nav-link active fw-semibold" id="tab-det-movs" data-bs-toggle="tab" data-bs-target="#pane-det-movs" type="button" role="tab">
                                            <i class="fas fa-clock-rotate-left me-1 text-primary"></i> Historial de movimientos
                                        </button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link fw-semibold" id="tab-det-novs" data-bs-toggle="tab" data-bs-target="#pane-det-novs" type="button" role="tab">
                                            <i class="fas fa-flag me-1 text-warning"></i> Novedades
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body p-4 tab-content">
                                <div class="tab-pane fade show active" id="pane-det-movs" role="tabpanel">
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="fw-bold text-danger mb-0 small text-uppercase">
                                                <i class="fas fa-hand-holding-box me-1"></i> Préstamos pendientes
                                            </h6>
                                            <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1 small">Pendientes</span>
                                        </div>
                                        <div class="table-responsive border rounded-3 bg-white">
                                            <table class="table table-hover align-middle mb-0 small">
                                                <thead class="small fw-bold" style="background:#FFF5F5;">
                                                    <tr>
                                                        <th>Fecha</th><th>Destino</th>
                                                        <th class="text-center">Prestado</th>
                                                        <th class="text-center">Devuelto</th>
                                                        <th class="text-center">Saldo</th>
                                                        <th>Estado</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="detPendientesTbody">
                                                    <tr><td colspan="6" class="text-center py-3 text-muted">Cargando...</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="fw-bold text-success mb-0 small text-uppercase">
                                                <i class="fas fa-circle-check me-1"></i> Entradas y Devoluciones
                                            </h6>
                                            <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 small">En inventario</span>
                                        </div>
                                        <div class="table-responsive border rounded-3 bg-white">
                                            <table class="table table-hover align-middle mb-0 small">
                                                <thead class="small fw-bold" style="background:#F0FDF4;">
                                                    <tr>
                                                        <th>Fecha</th><th>Tipo</th>
                                                        <th class="text-center">Cantidad</th>
                                                        <th>Observación / Autor</th>
                                                        <th class="text-center">Evidencia</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="detDevueltosTbody">
                                                    <tr><td colspan="5" class="text-center py-3 text-muted">Cargando...</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="pane-det-novs" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="fw-bold mb-0 text-dark">Novedades registradas</h6>
                                        <button type="button" class="btn btn-sm text-white rounded-pill px-3" style="background-color: #39A900;" onclick="mostrarModalNovedadDesdeDetalle()">
                                            <i class="fas fa-plus me-1"></i> Agregar
                                        </button>
                                    </div>
                                    <div id="detNovedadesList"><div class="text-center py-3 text-muted">Cargando...</div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- =================================================================== -->
<!-- 3. MODAL: EDITAR RECURSO                                            -->
<!-- =================================================================== -->
<div class="modal fade" id="modalEditarRecurso" tabindex="-1" aria-labelledby="modalEditarRecursoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <form id="formEditarRecursoModal" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header border-bottom py-3 px-4" style="background-color: #F8FAFC;">
                    <h5 class="modal-title fw-bold" id="modalEditarRecursoLabel" style="color: #001A29;">
                        <i class="fas fa-pen-to-square me-2" style="color: #39A900;"></i>Editar recurso
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <label for="editNombre" class="form-label small fw-semibold text-dark">Nombre del recurso <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="editNombre" class="form-control rounded-3" required maxlength="255">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="editCategoria" class="form-label small fw-semibold text-dark">Categoría <span class="text-danger">*</span></label>
                            <select name="id_categoria_inventario" id="editCategoria" class="form-select rounded-3" required>
                                @foreach ($categorias as $cat)
                                    <option value="{{ $cat->id_categoria_inventario }}">{{ $cat->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="editCantidadTotal" class="form-label small fw-semibold text-dark">Cantidad Total <span class="text-danger">*</span></label>
                            <input type="number" name="cantidad_total" id="editCantidadTotal" class="form-control rounded-3" min="1" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="editEstadoFisico" class="form-label small fw-semibold text-dark">Estado físico <span class="text-danger">*</span></label>
                            <select name="estado_fisico" id="editEstadoFisico" class="form-select rounded-3" required>
                                <option value="Bueno">Bueno</option>
                                <option value="Regular">Regular</option>
                                <option value="Crítico">Crítico</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="editUmbralReg" class="form-label small fw-semibold text-muted">Stock moderado (Amarillo)</label>
                            <input type="number" name="umbral_regular" id="editUmbralReg" class="form-control rounded-3">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="editUmbralCrit" class="form-label small fw-semibold text-muted">Stock mínimo (Rojo)</label>
                            <input type="number" name="umbral_critico" id="editUmbralCrit" class="form-control rounded-3">
                        </div>
                        <div class="col-12">
                            <label for="editObservaciones" class="form-label small fw-semibold text-dark">Observación</label>
                            <textarea name="observaciones" id="editObservaciones" rows="2" class="form-control rounded-3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-dark">Reemplazar fotografía(s) (opcional)</label>
                            <input type="file" name="fotos[]" id="editFotosInput" class="form-control rounded-3" multiple accept="image/jpeg,image/png">
                            <div class="form-text small text-success fw-semibold mt-1">
                                <i class="fas fa-circle-info me-1"></i> Si seleccionas una nueva foto, reemplazará la actual. Solo JPG o PNG.
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted mb-1">Foto actual:</label>
                            <div class="d-flex flex-wrap gap-2" id="editFotosExistentes"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white rounded-pill px-4 shadow-sm" style="background-color: #39A900; border-color: #39A900;">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- =================================================================== -->
<!-- 4. MODAL: REPORTES DE INVENTARIO CON GRÁFICAS                       -->
<!-- =================================================================== -->
<div class="modal fade" id="modalReporteInventario" tabindex="-1" aria-labelledby="modalReporteTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom py-3 px-4" style="background-color: #F8FAFC;">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="modalReporteTitle" style="color: #001A29;">
                        <i class="fas fa-chart-pie me-2 text-success"></i>Reportes de inventario
                    </h5>
                    <small class="text-muted">Escuela Cultura de Paz &bull; Estadísticas de disponibilidad, movimientos y distribución</small>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <button type="button" class="btn btn-outline-secondary rounded-pill btn-sm px-3" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Imprimir PDF
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-4">
                <ul class="nav nav-pills nav-pills-report gap-2 mb-4" id="reporteModalTabs" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#pane-rep-resumen" type="button">Resumen</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#pane-rep-recursos" type="button">Recursos ({{ $totalRecursos }})</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#pane-rep-movs" type="button">Movimientos ({{ $reporteMovimientos->count() }})</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#pane-rep-alertas" type="button">Alertas ({{ $alertasIncompletas }})</button></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="pane-rep-resumen" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-12 col-lg-4">
                                <div class="border rounded-4 p-3 bg-white h-100 shadow-sm">
                                    <h6 class="fw-bold text-dark mb-2">Estado de recursos</h6>
                                    <div class="position-relative" style="height: 190px;"><canvas id="chartModalEstadoRecursos"></canvas></div>
                                    <div class="mt-2 pt-2 border-top small">
                                        <div class="d-flex justify-content-between py-1"><span><i class="fas fa-circle me-1" style="color: #10B981;"></i> Disponibles</span><strong>{{ $verdeCount }}</strong></div>
                                        <div class="d-flex justify-content-between py-1"><span><i class="fas fa-circle me-1" style="color: #F59E0B;"></i> Estado regular</span><strong>{{ $amarilloCount }}</strong></div>
                                        <div class="d-flex justify-content-between py-1"><span><i class="fas fa-circle me-1" style="color: #EF4444;"></i> Estado crítico</span><strong>{{ $rojoCount }}</strong></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-4">
                                <div class="border rounded-4 p-3 bg-white h-100 shadow-sm">
                                    <h6 class="fw-bold text-dark mb-2">Movimientos por tipo</h6>
                                    <div style="height: 220px;"><canvas id="chartModalMovimientosTipo"></canvas></div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-4">
                                <div class="border rounded-4 p-3 bg-white h-100 shadow-sm">
                                    <h6 class="fw-bold text-dark mb-2">Recursos por categoría</h6>
                                    <div style="height: 220px;"><canvas id="chartModalRecursosCategoria"></canvas></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pane-rep-recursos" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead style="background: #F8FAFC;"><tr><th>Nombre</th><th>Categoría</th><th class="text-center">Disponible</th><th class="text-center">Total</th><th class="text-center">Semáforo</th></tr></thead>
                                <tbody>
                                    @foreach($todosRecursos as $tr)
                                    <tr>
                                        <td class="fw-bold text-dark">{{ $tr->nombre }}</td>
                                        <td>{{ $tr->categoria ? $tr->categoria->nombre : '-' }}</td>
                                        <td class="text-center fw-bold text-success">{{ $tr->cantidad_disponible }}</td>
                                        <td class="text-center text-muted">{{ $tr->cantidad_total }}</td>
                                        <td class="text-center"><span class="badge rounded-pill px-2 py-1" style="background: {{ $tr->semaforo_badge['bg'] }}; color: {{ $tr->semaforo_badge['text'] }};">{{ $tr->semaforo_badge['label'] }}</span></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pane-rep-movs" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead style="background: #F8FAFC;"><tr><th>Fecha</th><th>Tipo</th><th>Recurso</th><th class="text-center">Cantidad</th><th>Autor</th></tr></thead>
                                <tbody>
                                    @foreach($reporteMovimientos as $rm)
                                    <tr>
                                        <td class="text-muted">{{ $rm->fecha ? $rm->fecha->format('d/m/Y H:i') : '' }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $rm->tipo }}</span></td>
                                        <td class="fw-semibold text-dark">{{ $rm->inventario ? $rm->inventario->nombre : 'Recurso' }}</td>
                                        <td class="text-center fw-bold">{{ $rm->cantidad }}</td>
                                        <td class="text-muted">{{ $rm->usuario ? $rm->usuario->nickname : 'Sistema' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pane-rep-alertas" role="tabpanel">
                        @forelse($alertasLista as $al)
                            <div class="p-3 mb-2 rounded-3 border-start border-4 border-danger bg-light">
                                <strong class="text-danger d-block">Devolución incompleta</strong>
                                <div class="small text-dark">{{ $al->observacion }}</div>
                                <div class="small text-muted mt-1">{{ $al->fecha ? $al->fecha->format('d/m/Y H:i') : '' }}</div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted small">No hay alertas pendientes.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- =================================================================== -->
<!-- 5. MODAL: GESTIÓN DE CATEGORÍAS                                      -->
<!-- =================================================================== -->
<div class="modal fade" id="modalGestionCategorias" tabindex="-1" aria-labelledby="modalCatLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom py-3 px-4" style="background-color: #F8FAFC;">
                <h5 class="modal-title fw-bold" id="modalCatLabel" style="color: #001A29;">
                    <i class="fas fa-folder-open me-2 text-primary"></i>Gestión de categorías
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('controlecp.administrador.inventario.categorias.store') }}" method="POST" class="mb-4 p-3 bg-light rounded-4 border">
                    @csrf
                    <label class="form-label small fw-semibold text-dark">Nueva categoría</label>
                    <div class="input-group">
                        <input type="text" name="nombre" class="form-control" placeholder="Nombre de la nueva categoría" required maxlength="100">
                        <button type="submit" class="btn text-white px-4" style="background-color: #39A900;">
                            <i class="fas fa-plus me-1"></i> Agregar
                        </button>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="border-bottom" style="background: #F8FAFC;"><tr><th>Categoría</th><th class="text-center">Recursos</th><th class="text-center">Estado</th><th class="text-end">Acciones</th></tr></thead>
                        <tbody>
                            @foreach($categoriasConConteo as $catItem)
                            <tr>
                                <td class="fw-bold text-dark">{{ $catItem->nombre }}</td>
                                <td class="text-center"><span class="badge bg-light text-dark border px-2 py-1">{{ $catItem->inventarios_count }} un.</span></td>
                                <td class="text-center">
                                    @if($catItem->estado === 'Activo')
                                        <span class="badge rounded-pill px-3 py-1" style="background:#DEF7EC; color:#03543F;">Activa</span>
                                    @else
                                        <span class="badge rounded-pill px-3 py-1" style="background:#FEE2E2; color:#991B1B;">Inactiva</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary p-1 px-2 rounded-circle" onclick="editarCategoriaModal({{ $catItem->id_categoria_inventario }}, '{{ addslashes($catItem->nombre) }}', '{{ $catItem->estado }}')">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <form action="{{ route('controlecp.administrador.inventario.categorias.toggle', $catItem->id_categoria_inventario) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            @if($catItem->estado === 'Activo')
                                                <button type="submit" class="btn btn-sm btn-outline-danger p-1 px-2 rounded-circle" title="Inactivar"><i class="fas fa-power-off"></i></button>
                                            @else
                                                <button type="submit" class="btn btn-sm btn-outline-success p-1 px-2 rounded-circle" title="Activar"><i class="fas fa-check"></i></button>
                                            @endif
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Categoría -->
<div class="modal fade" id="modalEditarCategoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form id="formEditarCategoria" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header border-bottom py-3 px-4" style="background-color: #F8FAFC;">
                    <h5 class="modal-title fw-bold" style="color: #001A29;"><i class="fas fa-pen me-2 text-primary"></i>Editar categoría</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="editCatNombre" class="form-control rounded-3" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Estado <span class="text-danger">*</span></label>
                        <select name="estado" id="editCatEstado" class="form-select rounded-3" required>
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white rounded-pill px-4 shadow-sm" style="background-color: #39A900;">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- =================================================================== -->
<!-- 6. MODAL: REGISTRAR MOVIMIENTO RÁPIDO                               -->
<!-- =================================================================== -->
<div class="modal fade" id="modalRegistrarMovimiento" tabindex="-1" aria-labelledby="modalMovimientoTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form action="{{ route('controlecp.administrador.inventario.movimiento-rapido') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalMovimientoTitle" style="color: #001A29;">Registrar movimiento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="btn-group w-100 mb-3" role="group">
                        <input type="radio" class="btn-check" name="tipo" id="tipoEntrega" value="Entrega" checked autocomplete="off" onchange="actualizarTipoMovimiento()">
                        <label class="btn btn-outline-success fw-bold rounded-start-pill py-2" for="tipoEntrega">
                            <i class="fas fa-arrow-up me-1"></i> Entrega (Salida)
                        </label>
                        <input type="radio" class="btn-check" name="tipo" id="tipoDevolucion" value="Devolución" autocomplete="off" onchange="actualizarTipoMovimiento()">
                        <label class="btn btn-outline-primary fw-bold rounded-end-pill py-2" for="tipoDevolucion">
                            <i class="fas fa-arrow-rotate-left me-1"></i> Devolución (Entrada)
                        </label>
                    </div>
                    <div class="mb-3">
                        <label for="movRecursoSelect" class="form-label small fw-semibold text-dark">Recurso <span class="text-danger">*</span></label>
                        <select name="id_inventario" id="movRecursoSelect" class="form-select rounded-3" required onchange="actualizarMaxCantidad()">
                            <option value="">Selecciona un recurso...</option>
                            @foreach($todosRecursos as $tr)
                                <option value="{{ $tr->id_inventario }}" data-disponible="{{ $tr->cantidad_disponible }}">
                                    {{ $tr->nombre }} (Disponibles: {{ $tr->cantidad_disponible }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="movCantidad" class="form-label small fw-semibold text-dark">Cantidad <span class="text-danger">*</span></label>
                            <input type="number" name="cantidad" id="movCantidad" class="form-control rounded-3" min="1" value="1" required>
                        </div>
                        <div class="col-6">
                            <label for="movFecha" class="form-label small fw-semibold text-dark">Fecha <span class="text-danger">*</span></label>
                            <input type="date" name="fecha" id="movFecha" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="mb-3" id="campoDestinoWrapper">
                        <label for="movDestino" class="form-label small fw-semibold text-dark">Destino / Responsable</label>
                        <input type="text" name="destino" id="movDestino" class="form-control rounded-3" placeholder="Ej: Actividad, asesoría, persona...">
                    </div>
                    <div class="mb-3">
                        <label for="movObservacion" class="form-label small fw-semibold text-dark">Observación</label>
                        <textarea name="observacion" id="movObservacion" class="form-control rounded-3" rows="2" placeholder="Observaciones del movimiento"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="movFoto" class="form-label small fw-semibold text-dark">Adjuntar foto <span class="text-muted fw-normal">(opcional)</span></label>
                        <input type="file" name="foto" id="movFoto" class="form-control rounded-3" accept="image/jpeg,image/png">
                    </div>
                    <div class="alert alert-warning py-2 small mb-0 d-flex align-items-center gap-2">
                        <i class="fas fa-triangle-exclamation text-danger"></i>
                        <span>El stock no puede quedar en negativo.</span>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white rounded-pill px-4 shadow-sm" style="background-color: #39A900;">Registrar movimiento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- =================================================================== -->
<!-- 7. MODAL: VERIFICAR DISPONIBILIDAD                                   -->
<!-- =================================================================== -->
<div class="modal fade" id="modalVerificarDisponibilidad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" style="color: #001A29;">
                    <i class="fas fa-calendar-check text-primary me-2"></i>Verificar disponibilidad
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label for="checkRecursoSelect" class="form-label small fw-semibold text-dark">Recurso <span class="text-danger">*</span></label>
                    <select id="checkRecursoSelect" class="form-select rounded-3">
                        <option value="">Selecciona un recurso...</option>
                        @foreach($todosRecursos as $tr)
                            <option value="{{ $tr->id_inventario }}">{{ $tr->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-7">
                        <label for="checkFecha" class="form-label small fw-semibold text-dark">Fecha <span class="text-danger">*</span></label>
                        <input type="date" id="checkFecha" class="form-control rounded-3" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-5">
                        <label for="checkHora" class="form-label small fw-semibold text-dark">Hora <span class="text-danger">*</span></label>
                        <input type="time" id="checkHora" class="form-control rounded-3" value="08:00">
                    </div>
                </div>
                <div id="resultadoDisponibilidad" class="d-none">
                    <div class="card border-0 rounded-4 p-3 mb-0" id="boxDisponibilidad" style="background-color: #ECFDF5; border: 1px solid #A7F3D0 !important;">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fas fa-circle-check fs-5 text-success mt-1" id="iconDisponibilidad"></i>
                            <div>
                                <div class="fw-bold fs-6 text-success" id="titleDisponibilidad">Disponible para asignar</div>
                                <div class="small text-muted mt-1" id="msgDisponibilidad">No hay cruces en el horario seleccionado.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" onclick="ejecutarVerificacion()">
                    <i class="fas fa-magnifying-glass me-1"></i> Verificar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- =================================================================== -->
<!-- 8. MODAL: DAR DE BAJA UN RECURSO                                     -->
<!-- =================================================================== -->
<div class="modal fade" id="modalDarDeBaja" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form id="formDarDeBaja" method="POST">
                @csrf
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-danger"><i class="fas fa-ban me-2"></i>Dar de baja a un recurso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="fw-bold text-dark fs-6" id="bajaRecursoNombre">Nombre Recurso</div>
                        <div class="small text-muted">Cantidad actual: <strong id="bajaRecursoCantidad">0</strong> &bull; Estado: <span class="badge bg-success">Activo</span></div>
                    </div>
                    <div class="mb-3">
                        <label for="bajaMotivoSelect" class="form-label small fw-semibold text-dark">Motivo de baja <span class="text-danger">*</span></label>
                        <select name="motivo" id="bajaMotivoSelect" class="form-select rounded-3" required>
                            <option value="">Selecciona un motivo...</option>
                            <option value="Dañado">Dañado</option>
                            <option value="Perdido">Perdido</option>
                            <option value="Fuera de servicio">Fuera de servicio</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="bajaObsText" class="form-label small fw-semibold text-dark">Observación / Detalle <span class="text-danger">*</span></label>
                        <textarea name="descripcion" id="bajaObsText" class="form-control rounded-3" rows="3" placeholder="Detalle del motivo" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm" onclick="return confirm('¿Seguro de dar de baja este recurso? Esta acción lo inactivará.');">Dar de baja</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- =================================================================== -->
<!-- 9. MODAL: AGREGAR NOVEDAD                                            -->
<!-- =================================================================== -->
<div class="modal fade" id="modalAgregarNovedadRapida" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form id="formAgregarNovedadRapida" method="POST">
                @csrf
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" style="color: #001A29;">
                        <i class="fas fa-comment-dots me-2 text-warning"></i>Registrar novedad
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="novRapidaDesc" class="form-label small fw-semibold text-dark">Descripción de la novedad <span class="text-danger">*</span></label>
                        <textarea name="descripcion" id="novRapidaDesc" class="form-control rounded-3" rows="4" placeholder="Describa el incidente, deterioro, cambio de estado..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white rounded-pill px-4 shadow-sm" style="background-color: #39A900;">Guardar Novedad</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let recursoActualDetalleId = null;

function abrirDetalleModal(id) {
    recursoActualDetalleId = id;
    const modal = new bootstrap.Modal(document.getElementById('modalDetalleRecurso'));
    modal.show();

    document.getElementById('detNombre').innerText = 'Cargando información...';
    document.getElementById('detCategoria').innerText = '-';
    document.getElementById('detCantidad').innerText = '-';
    document.getElementById('detObservacion').innerText = '-';
    document.getElementById('detPendientesTbody').innerHTML = '<tr><td colspan="6" class="text-center py-3 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Cargando...</td></tr>';
    document.getElementById('detDevueltosTbody').innerHTML = '<tr><td colspan="5" class="text-center py-3 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Cargando...</td></tr>';
    document.getElementById('detNovedadesList').innerHTML = '<div class="text-center py-3 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Cargando...</div>';

    fetch(`/control-ecp/administrador/inventario/${id}/detalle-json`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('detNombre').innerText = data.nombre;
            document.getElementById('detCategoria').innerText = data.categoria;
            document.getElementById('detCantidad').innerText = `${data.cantidad_disponible} de ${data.cantidad_total} unidades`;
            document.getElementById('detObservacion').innerText = data.observaciones || 'Sin observaciones adicionales.';

            const imgEl = document.getElementById('detFotoPrincipal');
            if (data.fotos.length > 0) {
                imgEl.src = data.fotos[0].ruta;
                imgEl.classList.remove('d-none');
            } else {
                imgEl.classList.add('d-none');
            }

            const semBadge = document.getElementById('detBadgeSemaforo');
            semBadge.style.backgroundColor = data.semaforo_badge.bg;
            semBadge.style.color = data.semaforo_badge.text;
            semBadge.innerText = data.semaforo_badge.label;

            const estFis = document.getElementById('detEstadoFisico');
            estFis.style.backgroundColor = data.estado_fisico_badge.bg;
            estFis.style.color = data.estado_fisico_badge.text;
            estFis.innerText = data.estado_fisico;

            document.getElementById('detSemaforoTexto').innerText = data.semaforo === 'verde' ? 'Verde (Buen nivel)' : (data.semaforo === 'amarillo' ? 'Amarillo (Moderado)' : 'Rojo (Crítico)');

            const fotosCont = document.getElementById('detFotosMiniaturas');
            fotosCont.innerHTML = '';
            data.fotos.forEach((f, idx) => {
                const img = document.createElement('img');
                img.src = f.ruta;
                img.className = `thumb-mini-slot ${idx === 0 ? 'active-thumb' : ''}`;
                img.onclick = () => {
                    document.getElementById('detFotoPrincipal').src = f.ruta;
                    document.querySelectorAll('.thumb-mini-slot').forEach(el => el.classList.remove('active-thumb'));
                    img.classList.add('active-thumb');
                };
                fotosCont.appendChild(img);
            });

            document.getElementById('detBtnEditar').onclick = () => {
                bootstrap.Modal.getInstance(document.getElementById('modalDetalleRecurso')).hide();
                abrirEditarModal(data.id_inventario);
            };
            document.getElementById('detBtnEntrega').onclick = () => {
                bootstrap.Modal.getInstance(document.getElementById('modalDetalleRecurso')).hide();
                abrirMovimientoRapido(data.id_inventario, data.nombre, data.cantidad_disponible);
            };
            document.getElementById('detBtnDevolucion').onclick = () => {
                bootstrap.Modal.getInstance(document.getElementById('modalDetalleRecurso')).hide();
                abrirMovimientoRapido(data.id_inventario, data.nombre, data.cantidad_disponible);
                document.getElementById('tipoDevolucion').checked = true;
                actualizarTipoMovimiento();
            };
            document.getElementById('detBtnBaja').onclick = () => {
                bootstrap.Modal.getInstance(document.getElementById('modalDetalleRecurso')).hide();
                abrirModalBaja(data.id_inventario, data.nombre, data.cantidad_disponible);
            };

            const tbodyPendientes = document.getElementById('detPendientesTbody');
            tbodyPendientes.innerHTML = '';
            if (!data.movimientos_pendientes || data.movimientos_pendientes.length === 0) {
                tbodyPendientes.innerHTML = '<tr><td colspan="6" class="text-center py-3 text-success small"><i class="fas fa-check-circle me-1"></i>No hay préstamos pendientes.</td></tr>';
            } else {
                data.movimientos_pendientes.forEach(p => {
                    tbodyPendientes.innerHTML += `<tr class="table-warning bg-opacity-25"><td class="text-muted small">${p.fecha}</td><td class="fw-semibold text-dark small">${p.destino}</td><td class="text-center small">${p.cantidad_prestada} un.</td><td class="text-center small text-success">${p.cantidad_devuelta} un.</td><td class="text-center fw-bold text-danger small">${p.saldo_pendiente} un.</td><td><span class="badge bg-danger rounded-pill small">${p.estado}</span></td></tr>`;
                });
            }

            const tbodyDevueltos = document.getElementById('detDevueltosTbody');
            tbodyDevueltos.innerHTML = '';
            if (!data.movimientos_devueltos || data.movimientos_devueltos.length === 0) {
                tbodyDevueltos.innerHTML = '<tr><td colspan="5" class="text-center py-3 text-muted small">No hay registros de devoluciones o entradas.</td></tr>';
            } else {
                data.movimientos_devueltos.forEach(d => {
                    const fotoBadge = d.foto ? `<a href="${d.foto}" target="_blank" class="badge bg-light text-primary border"><i class="fas fa-image me-1"></i>Ver foto</a>` : '<span class="text-muted small">-</span>';
                    tbodyDevueltos.innerHTML += `<tr><td class="text-muted small">${d.fecha}</td><td><span class="badge ${d.tipo === 'Devolución' ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary'} rounded-pill small">${d.tipo}</span></td><td class="text-center fw-bold text-dark small">${d.cantidad} un.</td><td class="small text-muted">${d.observacion || '-'} <span class="d-block text-secondary" style="font-size:0.7rem;">Por: ${d.autor}</span></td><td class="text-center">${fotoBadge}</td></tr>`;
                });
            }

            const novList = document.getElementById('detNovedadesList');
            novList.innerHTML = '';
            if (data.novedades.length === 0) {
                novList.innerHTML = '<div class="text-center py-4 text-muted small">No hay novedades registradas.</div>';
            } else {
                data.novedades.forEach(n => {
                    novList.innerHTML += `<div class="p-2 mb-2 rounded bg-light border small"><div class="d-flex justify-content-between mb-1"><strong class="text-dark"><i class="fas fa-flag text-warning me-1"></i>Novedad</strong><span class="text-muted" style="font-size:0.75rem;">${n.fecha}</span></div><div class="text-muted">${n.descripcion}</div><div class="text-secondary pt-1 mt-1 border-top" style="font-size:0.72rem;">Por: ${n.autor}</div></div>`;
                });
            }
        })
        .catch(err => { console.error(err); alert('Error al cargar la información del recurso.'); });
}

function abrirEditarModal(id) {
    const modal = new bootstrap.Modal(document.getElementById('modalEditarRecurso'));
    const form = document.getElementById('formEditarRecursoModal');
    form.action = `/control-ecp/administrador/inventario/${id}`;

    fetch(`/control-ecp/administrador/inventario/${id}/detalle-json`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('editNombre').value = data.nombre;
            document.getElementById('editCategoria').value = data.id_categoria;
            document.getElementById('editCantidadTotal').value = data.cantidad_total;
            document.getElementById('editEstadoFisico').value = data.estado_fisico;
            document.getElementById('editUmbralReg').value = data.umbral_regular || '';
            document.getElementById('editUmbralCrit').value = data.umbral_critico || '';
            document.getElementById('editObservaciones').value = data.observaciones || '';
            const contFotos = document.getElementById('editFotosExistentes');
            contFotos.innerHTML = '';
            data.fotos.forEach(f => {
                contFotos.innerHTML += `<div class="position-relative border rounded p-1 bg-white"><img src="${f.ruta}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px;"></div>`;
            });
            modal.show();
        })
        .catch(err => { console.error(err); alert('Error al cargar el recurso para edición.'); });
}

function mostrarModalNovedadDesdeDetalle() {
    if (!recursoActualDetalleId) return;
    const formNov = document.getElementById('formAgregarNovedadRapida');
    formNov.action = `/control-ecp/administrador/inventario/${recursoActualDetalleId}/novedades`;
    new bootstrap.Modal(document.getElementById('modalAgregarNovedadRapida')).show();
}

function editarCategoriaModal(id, nombre, estado) {
    document.getElementById('editCatNombre').value = nombre;
    document.getElementById('editCatEstado').value = estado;
    document.getElementById('formEditarCategoria').action = `/control-ecp/administrador/inventario/categorias/${id}`;
    new bootstrap.Modal(document.getElementById('modalEditarCategoria')).show();
}

function previewDrawerFotos(files) {
    const cont = document.getElementById('previewDrawerContainer');
    cont.innerHTML = '';
    if (!files || files.length === 0) return;
    Array.from(files).forEach((file, idx) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const col = document.createElement('div');
            col.className = 'col-4';
            col.innerHTML = `<div class="border rounded p-1 bg-white text-center"><img src="${e.target.result}" style="width:100%; height:60px; object-fit:cover; border-radius:4px;"><span class="badge ${idx === 0 ? 'bg-success' : 'bg-secondary'} mt-1" style="font-size:0.65rem;">${idx === 0 ? 'Principal' : '#' + (idx + 1)}</span></div>`;
            cont.appendChild(col);
        };
        reader.readAsDataURL(file);
    });
}

function abrirMovimientoRapido(idRecurso, nombre, cantDisponible) {
    const select = document.getElementById('movRecursoSelect');
    select.value = idRecurso;
    actualizarMaxCantidad();
    new bootstrap.Modal(document.getElementById('modalRegistrarMovimiento')).show();
}

function actualizarMaxCantidad() {
    const select = document.getElementById('movRecursoSelect');
    const selectedOption = select.options[select.selectedIndex];
    const cantInput = document.getElementById('movCantidad');
    const esEntrega = document.getElementById('tipoEntrega').checked;
    if (selectedOption && selectedOption.dataset.disponible) {
        const disponible = parseInt(selectedOption.dataset.disponible);
        if (esEntrega) cantInput.max = disponible;
        else cantInput.removeAttribute('max');
    }
}

function actualizarTipoMovimiento() { actualizarMaxCantidad(); }

function abrirModalBaja(idRecurso, nombre, cant) {
    document.getElementById('bajaRecursoNombre').innerText = nombre;
    document.getElementById('bajaRecursoCantidad').innerText = cant;
    const form = document.getElementById('formDarDeBaja');
    form.action = `/control-ecp/administrador/inventario/${idRecurso}/dar-baja`;
    new bootstrap.Modal(document.getElementById('modalDarDeBaja')).show();
}

function ejecutarVerificacion() {
    const idRecurso = document.getElementById('checkRecursoSelect').value;
    const fecha = document.getElementById('checkFecha').value;
    const hora = document.getElementById('checkHora').value;
    if (!idRecurso) { alert('Por favor selecciona un recurso.'); return; }
    fetch("{{ route('controlecp.administrador.inventario.verificar-disponibilidad') }}", {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
        body: JSON.stringify({ id_inventario: idRecurso, fecha: fecha, hora: hora })
    })
    .then(res => res.json())
    .then(data => {
        const resBox = document.getElementById('resultadoDisponibilidad');
        const box = document.getElementById('boxDisponibilidad');
        const title = document.getElementById('titleDisponibilidad');
        const msg = document.getElementById('msgDisponibilidad');
        const icon = document.getElementById('iconDisponibilidad');
        resBox.classList.remove('d-none');
        if (data.disponible) {
            box.style.backgroundColor = '#ECFDF5'; box.style.borderColor = '#A7F3D0';
            title.className = 'fw-bold fs-6 text-success'; title.innerText = 'Disponible para asignar';
            icon.className = 'fas fa-circle-check fs-5 text-success mt-1'; msg.innerText = data.mensaje;
        } else {
            box.style.backgroundColor = '#FEF2F2'; box.style.borderColor = '#FECACA';
            title.className = 'fw-bold fs-6 text-danger'; title.innerText = 'No disponible';
            icon.className = 'fas fa-circle-xmark fs-5 text-danger mt-1'; msg.innerText = data.mensaje;
        }
    })
    .catch(err => { console.error(err); alert('Ocurrió un error al verificar la disponibilidad.'); });
}

let graficasInicializadas = false;
function inicializarGraficasReporte() {
    if (graficasInicializadas) return;
    setTimeout(() => {
        const ctxEst = document.getElementById('chartModalEstadoRecursos');
        if (ctxEst) {
            new Chart(ctxEst, {
                type: 'doughnut',
                data: { labels: ['Disponibles', 'Estado regular', 'Estado crítico'], datasets: [{ data: [{{ $verdeCount }}, {{ $amarilloCount }}, {{ $rojoCount }}], backgroundColor: ['#10B981', '#F59E0B', '#EF4444'], borderWidth: 2, borderColor: '#FFFFFF' }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, cutout: '70%' }
            });
        }
        const ctxMov = document.getElementById('chartModalMovimientosTipo');
        if (ctxMov) {
            new Chart(ctxMov, {
                type: 'bar',
                data: { labels: ['Entregas', 'Devoluciones', 'Cambios', 'Bajas'], datasets: [{ data: [{{ $entregasCount }}, {{ $devolucionesCount }}, {{ $cambioEstadoCount }}, {{ $bajasCount }}], backgroundColor: ['#0284C7', '#10B981', '#38BDF8', '#F87171'], borderRadius: 6 }] },
                options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });
        }
        const ctxCat = document.getElementById('chartModalRecursosCategoria');
        if (ctxCat) {
            new Chart(ctxCat, {
                type: 'pie',
                data: { labels: {!! json_encode($reporteCategoriasLabels) !!}, datasets: [{ data: {!! json_encode($reporteCategoriasCounts) !!}, backgroundColor: ['#3B82F6', '#F97316', '#EC4899', '#06B6D4', '#EAB308', '#8B5CF6', '#10B981'], borderWidth: 2, borderColor: '#FFFFFF' }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 9 } } } } }
            });
        }
        graficasInicializadas = true;
    }, 250);
}
</script>
@endpush
@endsection
