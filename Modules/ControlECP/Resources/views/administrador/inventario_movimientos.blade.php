@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .alert-card-clean {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 1.2rem 1.4rem;
        box-shadow: 0 2px 10px rgba(0, 26, 41, 0.03);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .alert-card-clean:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 26, 41, 0.08);
    }
    .drag-drop-box-sm {
        border: 2px dashed #CBD5E1;
        border-radius: 12px;
        background-color: #F8FAFC;
        padding: 1.5rem 1rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .drag-drop-box-sm:hover {
        border-color: #39A900;
        background-color: #F0FDF4;
    }
    .nav-pills-movs .nav-link {
        color: #475569;
        font-weight: 600;
        border-radius: 9999px;
        padding: 0.5rem 1.25rem;
        font-size: 0.88rem;
        background-color: #F1F5F9;
        transition: all 0.2s ease;
    }
    .nav-pills-movs .nav-link.active {
        background-color: #39A900;
        color: #FFFFFF;
        box-shadow: 0 2px 8px rgba(57, 169, 0, 0.25);
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">

    <!-- Breadcrumb & Cabecera (HU 16: Historial de Movimientos) -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.administrador.inicio') }}" class="text-decoration-none" style="color: #39A900;">
                            <i class="fas fa-house me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.administrador.inventario') }}" class="text-decoration-none" style="color: #39A900;">
                            Inventario
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page" style="color: #001A29; font-weight: 600;">
                        Historial de movimientos
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: #001A29; font-family: 'Outfit', sans-serif;">
                Historial de Movimientos
            </h2>
            <p class="text-muted small mb-0">Trazabilidad de préstamos, entregas, devoluciones y alertas de inventario de Cultura de Paz</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('controlecp.administrador.inventario') }}" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm btn-sm d-flex align-items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <span>Volver al inventario</span>
            </a>
            <button type="button" class="btn text-white rounded-pill px-4 shadow-sm btn-sm d-flex align-items-center gap-2" style="background-color: #39A900; border-color: #39A900;" data-bs-toggle="modal" data-bs-target="#modalRegistrarMovimiento">
                <i class="fas fa-plus"></i>
                <span class="fw-semibold">Registrar movimiento</span>
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

    <!-- Banner Superior de Devolución Incompleta (HU 12) -->
    @if($alertaIncompletaPrincipal)
        <div class="card border-0 shadow-sm mb-4 border-start border-4 border-danger" style="background: #FFF5F5; border-radius: 14px;">
            <div class="card-body p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="text-danger fs-3">
                        <i class="fas fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="fw-bold text-danger mb-0">Alerta: Devolución incompleta pendiente</h6>
                            <span class="badge bg-danger text-white rounded-pill small" style="font-size: 0.7rem;">Automática</span>
                        </div>
                        <div class="text-danger small fw-semibold mt-1">
                            {{ $alertaIncompletaPrincipal->observacion }}
                        </div>
                        <div class="text-muted small mt-1" style="font-size: 0.75rem;">
                            Recurso: <strong>{{ $alertaIncompletaPrincipal->inventario ? $alertaIncompletaPrincipal->inventario->nombre : 'Recurso' }}</strong> &bull;
                            Saldo pendiente: <strong class="text-danger">{{ $alertaIncompletaPrincipal->saldo_pendiente }} unidad(es)</strong> &bull;
                            <span class="text-success"><i class="fas fa-circle-check me-1"></i>Esta alerta se quitará automáticamente al saldar las unidades pendientes.</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm text-white rounded-pill px-3 shadow-sm" style="background-color: #39A900;" onclick="prepararDevolucionRapida({{ $alertaIncompletaPrincipal->id_inventario }}, {{ $alertaIncompletaPrincipal->id }}, {{ $alertaIncompletaPrincipal->saldo_pendiente }})">
                        <i class="fas fa-arrow-rotate-left me-1"></i> Saldar devolución
                    </button>
                    @if($alertaIncompletaPrincipal->inventario)
                        <a href="{{ route('controlecp.administrador.inventario.show', $alertaIncompletaPrincipal->inventario->id_inventario) }}" class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm">
                            Ver detalle
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Resumen de Indicadores (HU 15: Disponibles vs Prestados) -->
    <div class="row g-3 mb-4">
        <!-- 1. Recursos Disponibles (HU 15) -->
        <div class="col-6 col-md-3">
            <div class="alert-card-clean">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted small fw-semibold">Recursos disponibles</span>
                    <i class="fas fa-circle-check text-success fs-5"></i>
                </div>
                <div class="h2 fw-bold mb-0 text-success">{{ $totalDisponibles }}</div>
                <small class="text-muted" style="font-size: 0.75rem;">Unidades listas para asignar</small>
            </div>
        </div>

        <!-- 2. Recursos Prestados (HU 15) -->
        <div class="col-6 col-md-3">
            <div class="alert-card-clean">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted small fw-semibold">Recursos prestados</span>
                    <i class="fas fa-hand-holding-box text-primary fs-5"></i>
                </div>
                <div class="h2 fw-bold mb-0 text-primary">{{ $totalPrestados }}</div>
                <small class="text-muted" style="font-size: 0.75rem;">Unidades actualmente en préstamo</small>
            </div>
        </div>

        <!-- 3. Préstamos pendientes de devolver -->
        <div class="col-6 col-md-3">
            <div class="alert-card-clean">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted small fw-semibold">Préstamos pendientes</span>
                    <i class="fas fa-clock text-warning fs-5"></i>
                </div>
                <div class="h2 fw-bold mb-0 text-warning-emphasis">{{ $prestamosPendientes->count() }}</div>
                <small class="text-muted" style="font-size: 0.75rem;">Entregas con saldo por devolver</small>
            </div>
        </div>

        <!-- 4. Devoluciones incompletas (HU 12) -->
        <div class="col-6 col-md-3">
            <div class="alert-card-clean">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted small fw-semibold">Devoluciones incompletas</span>
                    <i class="fas fa-circle-exclamation text-danger fs-5"></i>
                </div>
                <div class="h2 fw-bold mb-0 text-danger">{{ $devolucionesIncompletasCount }}</div>
                <small class="text-muted" style="font-size: 0.75rem;">Alertas automáticas activas</small>
            </div>
        </div>
    </div>

    <!-- Buscador y Filtros del Historial (HU 16: Con buscador por texto) -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-body p-3">
            <form action="{{ route('controlecp.administrador.inventario.movimientos') }}" method="GET" class="row g-2 align-items-center">
                <!-- Buscador por texto (HU 16) -->
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3 text-muted">
                            <i class="fas fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="buscar" value="{{ request('buscar') }}" class="form-control border-start-0 rounded-end-pill" placeholder="Buscar por recurso, destino u observación...">
                    </div>
                </div>

                <!-- Filtro por Tipo -->
                <div class="col-6 col-md-2">
                    <select name="tipo" class="form-select rounded-pill" onchange="this.form.submit()">
                        <option value="">Todos los tipos</option>
                        <option value="Entrega" {{ request('tipo') == 'Entrega' ? 'selected' : '' }}>Entregas / Préstamos</option>
                        <option value="Devolución" {{ request('tipo') == 'Devolución' ? 'selected' : '' }}>Devoluciones</option>
                        <option value="Cambio de estado" {{ request('tipo') == 'Cambio de estado' ? 'selected' : '' }}>Cambios de estado</option>
                        <option value="Baja" {{ request('tipo') == 'Baja' ? 'selected' : '' }}>Bajas</option>
                    </select>
                </div>

                <!-- Fecha Desde -->
                <div class="col-6 col-md-2">
                    <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="form-control rounded-pill" title="Fecha desde" onchange="this.form.submit()">
                </div>

                <!-- Fecha Hasta -->
                <div class="col-6 col-md-2">
                    <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="form-control rounded-pill" title="Fecha hasta" onchange="this.form.submit()">
                </div>

                <!-- Botones Acción -->
                <div class="col-6 col-md-2 d-flex gap-2 justify-content-end">
                    <button type="submit" class="btn text-white rounded-pill px-3 flex-grow-1" style="background-color: #39A900;">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                    @if(request()->hasAny(['buscar', 'tipo', 'fecha_desde', 'fecha_hasta']))
                        <a href="{{ route('controlecp.administrador.inventario.movimientos') }}" class="btn btn-outline-secondary rounded-pill" title="Limpiar filtros">
                            <i class="fas fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Pestañas de Navegación del Historial (HU 16: Préstamos pendientes vs Historial general) -->
    <ul class="nav nav-pills nav-pills-movs gap-2 mb-3" id="movimientosTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active d-flex align-items-center gap-2" id="tab-completo" data-bs-toggle="pill" data-bs-target="#pane-completo" type="button" role="tab">
                <i class="fas fa-clock-rotate-left"></i>
                <span>Historial completo</span>
                <span class="badge bg-white text-dark rounded-pill">{{ $movimientos->total() }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link d-flex align-items-center gap-2" id="tab-pendientes" data-bs-toggle="pill" data-bs-target="#pane-pendientes" type="button" role="tab">
                <i class="fas fa-hand-holding-box text-warning"></i>
                <span>Préstamos pendientes de devolución</span>
                <span class="badge bg-danger text-white rounded-pill">{{ $prestamosPendientes->count() }}</span>
            </button>
        </li>
    </ul>

    <!-- Contenedor de Pestañas -->
    <div class="tab-content" id="movimientosTabsContent">

        <!-- 1. PESTAÑA: HISTORIAL COMPLETO DE MOVIMIENTOS -->
        <div class="tab-pane fade show active" id="pane-completo" role="tabpanel">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background-color: #F8FAFC; color: #001A29;" class="small fw-bold border-bottom">
                                <tr>
                                    <th class="ps-4 py-3">Fecha y hora</th>
                                    <th class="py-3">Recurso</th>
                                    <th class="py-3">Tipo de movimiento</th>
                                    <th class="py-3 text-center">Cantidad</th>
                                    <th class="py-3">Detalle / Destino</th>
                                    <th class="py-3">Autor</th>
                                    <th class="text-end pe-4 py-3">Evidencia (Foto)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y small">
                                @forelse($movimientos as $mov)
                                    @php
                                        $esIncompleto = str_contains($mov->observacion, '[ALERTA DEVOLUCIÓN INCOMPLETA]');
                                    @endphp
                                    <tr class="{{ $esIncompleto ? 'table-warning bg-opacity-25' : '' }}">
                                        <!-- Fecha y Hora -->
                                        <td class="ps-4 text-nowrap text-muted">
                                            {{ $mov->fecha ? $mov->fecha->format('d/m/Y H:i') : 'N/A' }}
                                        </td>

                                        <!-- Recurso -->
                                        <td>
                                            <div class="fw-bold text-dark">
                                                {{ $mov->inventario ? $mov->inventario->nombre : 'Recurso eliminado' }}
                                            </div>
                                            <div class="text-muted small" style="font-size: 0.72rem;">
                                                Categoría: {{ $mov->inventario && $mov->inventario->categoria ? $mov->inventario->categoria->nombre : '-' }}
                                            </div>
                                        </td>

                                        <!-- Tipo de Movimiento -->
                                        <td>
                                            @if($mov->tipo === 'Entrega')
                                                <span class="badge rounded-pill px-3 py-1 fw-semibold" style="background-color: #E0F2FE; color: #0369A1;">
                                                    <i class="fas fa-arrow-up me-1"></i>Entrega / Salida
                                                </span>
                                            @elseif($mov->tipo === 'Devolución')
                                                <span class="badge rounded-pill px-3 py-1 fw-semibold" style="background-color: #DCFCE7; color: #15803D;">
                                                    <i class="fas fa-arrow-rotate-left me-1"></i>Devolución / Entrada
                                                </span>
                                            @elseif($mov->tipo === 'Baja')
                                                <span class="badge rounded-pill px-3 py-1 fw-semibold" style="background-color: #FEE2E2; color: #B91C1C;">
                                                    <i class="fas fa-ban me-1"></i>Dado de baja
                                                </span>
                                            @else
                                                <span class="badge rounded-pill px-3 py-1 bg-light text-dark border">
                                                    {{ $mov->tipo }}
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Cantidad -->
                                        <td class="text-center fw-bold fs-6">
                                            @if($mov->tipo === 'Entrega' || $mov->tipo === 'Baja')
                                                <span class="text-danger">-{{ $mov->cantidad }}</span>
                                            @elseif($mov->tipo === 'Devolución' || $mov->tipo === 'Entrada')
                                                <span class="text-success">+{{ $mov->cantidad }}</span>
                                            @else
                                                <span class="text-muted">{{ $mov->cantidad }}</span>
                                            @endif
                                        </td>

                                        <!-- Observación / Destino -->
                                        <td>
                                            <div class="text-dark small text-truncate" style="max-width: 280px;" title="{{ $mov->observacion }}">
                                                {{ $mov->observacion ?: 'Sin observaciones' }}
                                            </div>
                                        </td>

                                        <!-- Autor -->
                                        <td class="text-muted small">
                                            <i class="fas fa-user-circle me-1 text-secondary"></i>{{ $mov->usuario ? $mov->usuario->nickname : 'Sistema' }}
                                        </td>

                                        <!-- Evidencia Fotográfica (HU 14) -->
                                        <td class="text-end pe-4">
                                            @if($mov->foto)
                                                <a href="{{ asset($mov->foto) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1 small" title="Ver fotografía adjunta">
                                                    <i class="fas fa-camera me-1"></i>Foto
                                                </a>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="fas fa-clock-rotate-left fa-3x mb-3 text-secondary opacity-50"></i>
                                            <p class="mb-1 fw-bold fs-6 text-dark">No se encontraron movimientos en el historial</p>
                                            <small class="text-muted">Intenta cambiar los filtros de búsqueda o registra una nueva entrega.</small>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="p-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="small text-muted">
                            Mostrando {{ $movimientos->firstItem() ?? 0 }} - {{ $movimientos->lastItem() ?? 0 }} de {{ $movimientos->total() }} movimientos
                        </div>
                        <div>
                            {{ $movimientos->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. PESTAÑA: PRÉSTAMOS PENDIENTES DE DEVOLUCIÓN (HU 16) -->
        <div class="tab-pane fade" id="pane-pendientes" role="tabpanel">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-white border-0 pt-3 px-4 pb-2 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark" style="font-size: 1rem;">
                            <i class="fas fa-hand-holding-box text-warning me-2"></i>Recursos prestados pendientes por devolver
                        </h5>
                        <small class="text-muted">Listado de entregas activas que no han sido saldadas en su totalidad</small>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background-color: #FFF5F5; color: #001A29;" class="small fw-bold border-bottom">
                                <tr>
                                    <th class="ps-4 py-3">Fecha de entrega</th>
                                    <th class="py-3">Recurso</th>
                                    <th class="py-3 text-center">Cant. Prestada</th>
                                    <th class="py-3 text-center">Cant. Devuelta</th>
                                    <th class="py-3 text-center">Saldo por devolver</th>
                                    <th class="py-3">Destino / Observación</th>
                                    <th class="py-3">Autor</th>
                                    <th class="text-end pe-4 py-3">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y small">
                                @forelse($prestamosPendientes as $p)
                                    <tr>
                                        <!-- Fecha -->
                                        <td class="ps-4 text-nowrap text-muted">
                                            {{ $p->fecha ? $p->fecha->format('d/m/Y H:i') : 'N/A' }}
                                        </td>

                                        <!-- Recurso -->
                                        <td>
                                            <div class="fw-bold text-dark">
                                                {{ $p->inventario ? $p->inventario->nombre : 'Recurso' }}
                                            </div>
                                            <div class="text-muted small" style="font-size: 0.72rem;">
                                                Stock actual disponible: {{ $p->inventario ? $p->inventario->cantidad_disponible : '-' }} un.
                                            </div>
                                        </td>

                                        <!-- Cant. Prestada -->
                                        <td class="text-center fw-semibold text-primary">
                                            {{ $p->cantidad_prestada }} un.
                                        </td>

                                        <!-- Cant. Devuelta -->
                                        <td class="text-center fw-semibold text-success">
                                            {{ $p->cantidad_devuelta }} un.
                                        </td>

                                        <!-- Saldo Pendiente -->
                                        <td class="text-center fw-bold text-danger fs-6">
                                            {{ $p->saldo_pendiente }} un.
                                        </td>

                                        <!-- Destino -->
                                        <td>
                                            <div class="text-dark small text-truncate" style="max-width: 250px;">
                                                {{ $p->observacion ?: 'Sin destino especificado' }}
                                            </div>
                                        </td>

                                        <!-- Autor -->
                                        <td class="text-muted small">
                                            {{ $p->autor }}
                                        </td>

                                        <!-- Acción Rápida Devolver -->
                                        <td class="text-end pe-4">
                                            <button type="button" class="btn btn-sm text-white rounded-pill px-3 shadow-sm" style="background-color: #39A900;" onclick="prepararDevolucionRapida({{ $p->id_inventario }}, {{ $p->id }}, {{ $p->saldo_pendiente }})">
                                                <i class="fas fa-arrow-rotate-left me-1"></i> Devolver
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="fas fa-circle-check fa-3x text-success mb-3 opacity-50"></i>
                                            <p class="mb-1 fw-bold fs-6 text-dark">¡Todos los préstamos se encuentran al día!</p>
                                            <small class="text-muted">No existen recursos pendientes de devolución en la Escuela Cultura de Paz.</small>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: REGISTRAR MOVIMIENTO (Con soporte para Saldo)    -->
<!-- ======================================================== -->
<div class="modal fade" id="modalRegistrarMovimiento" tabindex="-1" aria-labelledby="modalRegistrarMovimientoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <form action="{{ route('controlecp.administrador.inventario.movimiento-rapido') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <!-- ID de entrega de origen para devoluciones directas -->
                <input type="hidden" name="id_movimiento_entrega" id="modalEntregaOrigenId" value="">

                <div class="modal-header border-bottom py-3 px-4" style="background-color: #F8FAFC;">
                    <h5 class="modal-title fw-bold" id="modalRegistrarMovimientoLabel" style="color: #001A29;">
                        <i class="fas fa-right-left me-2 text-primary"></i>Registrar movimiento de inventario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Toggle Pills: Entrega / Devolución -->
                    <div class="btn-group w-100 mb-3" role="group">
                        <input type="radio" class="btn-check" name="tipo" id="tipoEntrega" value="Entrega" checked autocomplete="off" onchange="cambiarTipoModal()">
                        <label class="btn btn-outline-success fw-bold rounded-start-pill py-2" for="tipoEntrega">
                            <i class="fas fa-arrow-up me-1"></i> Entrega (Salida de inventario)
                        </label>

                        <input type="radio" class="btn-check" name="tipo" id="tipoDevolucion" value="Devolución" autocomplete="off" onchange="cambiarTipoModal()">
                        <label class="btn btn-outline-primary fw-bold rounded-end-pill py-2" for="tipoDevolucion">
                            <i class="fas fa-arrow-rotate-left me-1"></i> Devolución (Entrada a inventario)
                        </label>
                    </div>

                    <div class="row g-3">
                        <!-- Recurso * -->
                        <div class="col-12 col-md-8">
                            <label for="id_inventario_mov" class="form-label small fw-semibold text-dark">Recurso <span class="text-danger">*</span></label>
                            <select name="id_inventario" id="id_inventario_mov" class="form-select rounded-3" required onchange="actualizarLimitesModal()">
                                <option value="">Selecciona un recurso...</option>
                                @foreach($recursosDisponibles as $rec)
                                    <option value="{{ $rec->id_inventario }}" data-disponible="{{ $rec->cantidad_disponible }}">
                                        {{ $rec->nombre }} (Disponibles: {{ $rec->cantidad_disponible }} un.)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Cantidad * -->
                        <div class="col-12 col-md-4">
                            <label for="cantidad_mov" class="form-label small fw-semibold text-dark">Cantidad <span class="text-danger">*</span></label>
                            <input type="number" name="cantidad" id="cantidad_mov" class="form-control rounded-3" min="1" value="1" required>
                            <div class="form-text small text-muted" id="infoMaxCantidad"></div>
                        </div>

                        <!-- Fecha * -->
                        <div class="col-12 col-md-6">
                            <label for="fecha_mov" class="form-label small fw-semibold text-dark">Fecha <span class="text-danger">*</span></label>
                            <input type="date" name="fecha" id="fecha_mov" value="{{ date('Y-m-d') }}" class="form-control rounded-3" required>
                        </div>

                        <!-- Destino / Responsable -->
                        <div class="col-12 col-md-6" id="wrapperDestino">
                            <label for="destino_mov" class="form-label small fw-semibold text-dark">Destino / Responsable</label>
                            <input type="text" name="destino" id="destino_mov" class="form-control rounded-3" placeholder="Ej. Actividad, taller, asesoría...">
                        </div>

                        <!-- Observación -->
                        <div class="col-12">
                            <label for="observacion_mov" class="form-label small fw-semibold text-dark">Observación o novedad del movimiento</label>
                            <textarea name="observacion" id="observacion_mov" rows="2" class="form-control rounded-3" placeholder="Detalles del préstamo o estado de devolución..."></textarea>
                        </div>

                        <!-- Adjuntar Foto (HU 14) -->
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-dark">Adjuntar evidencia fotográfica (JPG o PNG)</label>
                            <div class="drag-drop-box-sm" onclick="document.getElementById('fotoMovInput').click()">
                                <i class="fas fa-camera fa-2x mb-1" style="color: #39A900;"></i>
                                <div class="small fw-semibold text-dark">
                                    Haz clic para subir foto del estado del recurso
                                </div>
                                <div class="text-muted" style="font-size: 0.72rem;">Formatos aceptados: JPG o PNG (máx. 4MB)</div>
                                <input type="file" name="foto" id="fotoMovInput" class="d-none" accept="image/jpeg,image/png" onchange="mostrarPreviewFoto(this)">
                            </div>
                            <div id="previewFotoTexto" class="small text-success mt-1 fw-semibold"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white rounded-pill px-4 shadow-sm" style="background-color: #39A900; border-color: #39A900;">
                        Guardar movimiento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function cambiarTipoModal() {
    actualizarLimitesModal();
}

function actualizarLimitesModal() {
    const sel = document.getElementById('id_inventario_mov');
    const cantInput = document.getElementById('cantidad_mov');
    const info = document.getElementById('infoMaxCantidad');
    const esEntrega = document.getElementById('tipoEntrega').checked;

    if (sel.selectedIndex > 0) {
        const disp = parseInt(sel.options[sel.selectedIndex].dataset.disponible || 0);
        if (esEntrega) {
            cantInput.max = disp;
            info.innerText = `Máximo disponible para entregar: ${disp} un.`;
        } else {
            cantInput.removeAttribute('max');
            info.innerText = `Las unidades devueltas sumarán al inventario.`;
        }
    } else {
        info.innerText = '';
    }
}

function prepararDevolucionRapida(idRecurso, idEntrega, saldoPendiente) {
    document.getElementById('tipoDevolucion').checked = true;
    cambiarTipoModal();
    document.getElementById('id_inventario_mov').value = idRecurso;
    document.getElementById('modalEntregaOrigenId').value = idEntrega;
    document.getElementById('cantidad_mov').value = saldoPendiente;
    document.getElementById('infoMaxCantidad').innerText = `Saldo pendiente de la entrega: ${saldoPendiente} un.`;
    new bootstrap.Modal(document.getElementById('modalRegistrarMovimiento')).show();
}

function mostrarPreviewFoto(input) {
    if (input.files && input.files[0]) {
        document.getElementById('previewFotoTexto').innerHTML = `<i class="fas fa-check me-1"></i> Foto seleccionada: ${input.files[0].name}`;
    }
}
</script>
@endpush
@endsection
