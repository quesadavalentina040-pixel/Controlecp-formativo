@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .recurso-main-img {
        width: 100%;
        height: 240px;
        object-fit: cover;
        border-radius: 12px;
        border: 1px solid #E2E8F0;
        transition: transform 0.2s ease;
    }
    .thumb-gallery-item {
        width: 62px;
        height: 62px;
        object-fit: cover;
        border-radius: 10px;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.2s ease;
    }
    .thumb-gallery-item:hover, .thumb-gallery-item.active-thumb {
        border-color: #39A900;
        transform: scale(1.05);
    }
    .thumb-more-badge {
        width: 62px;
        height: 62px;
        border-radius: 10px;
        background: #001A29;
        color: #FFFFFF;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.95rem;
        cursor: pointer;
    }
    .metadata-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.65rem 0;
        border-bottom: 1px solid #F1F5F9;
        font-size: 0.9rem;
    }
    .metadata-row:last-child {
        border-bottom: none;
    }
    .metadata-label {
        color: #64748B;
        font-weight: 500;
    }
    .metadata-value {
        color: #0F172A;
        font-weight: 600;
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">

    <!-- Breadcrumb & Cabecera (Mockup Image 1) -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.administrador.inventario') }}" class="text-decoration-none fw-semibold" style="color: #39A900;">
                            <i class="fas fa-arrow-left me-1"></i> Volver al listado
                        </a>
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: #001A29; font-family: 'Outfit', sans-serif;">
                Detalle del recurso
            </h2>
            <p class="text-muted small mb-0">Control ECP &bull; Información técnica, galería fotográfica, trazabilidad y novedades</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('controlecp.administrador.inventario.edit', $recurso->id_inventario) }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm shadow-sm">
                <i class="fas fa-pen me-1 text-success"></i> Editar
            </a>

            @if($recurso->estado !== 'Inactivo')
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEntrega" {{ $recurso->cantidad_disponible <= 0 ? 'disabled' : '' }}>
                    <i class="fas fa-hand-holding-box me-1"></i> Entrega
                </button>

                <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalDevolucion">
                    <i class="fas fa-arrow-rotate-left me-1"></i> Devolución
                </button>

                <button type="button" class="btn btn-sm text-white rounded-pill px-3 shadow-sm" style="background-color: #39A900;" data-bs-toggle="modal" data-bs-target="#modalNovedad">
                    <i class="fas fa-plus me-1"></i> Novedad
                </button>

                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalBaja">
                    <i class="fas fa-ban me-1"></i> Dar de baja
                </button>
            @endif
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

    @php
        $fotoPrincipal = $recurso->fotos->first();
        $semaforo = $recurso->semaforo_badge;
        $estadoFisico = $recurso->estado_fisico_badge;
    @endphp

    <div class="row g-4">
        <!-- Columna Izquierda: Tarjeta del Recurso (Exacta Mockup Image 1) -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm p-3 p-md-4" style="border-radius: 16px;">
                <!-- Imagen Principal -->
                <div class="mb-3 position-relative">
                    @if($fotoPrincipal && $fotoPrincipal->ruta)
                        <img src="{{ asset($fotoPrincipal->ruta) }}" alt="{{ $recurso->nombre }}" class="recurso-main-img" id="imgPrincipalRecurso">
                    @else
                        <div class="recurso-main-img bg-light d-flex align-items-center justify-content-center text-muted">
                            <i class="fas fa-box-open fa-3x text-secondary opacity-50"></i>
                        </div>
                    @endif
                </div>

                <!-- Título y Estado / Disponibilidad -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="h4 fw-bold mb-0 text-truncate" style="color: #001A29;" title="{{ $recurso->nombre }}">
                        {{ $recurso->nombre }}
                    </h3>

                    @if($recurso->estado === 'Inactivo')
                        <span class="badge rounded-pill px-3 py-2 border small" style="background-color: #FEF2F2; color: #991B1B; border-color: #FECACA !important;">
                            <span class="badge-semaforo-dot" style="background-color: #EF4444;"></span>Inactivo / Baja
                        </span>
                    @elseif($recurso->semaforo === 'verde')
                        <span class="badge rounded-pill px-3 py-2 border small" style="background-color: #ECFDF5; color: #065F46; border-color: #A7F3D0 !important;">
                            <span class="badge-semaforo-dot" style="background-color: #10B981;"></span>Disponible
                        </span>
                    @elseif($recurso->semaforo === 'amarillo')
                        <span class="badge rounded-pill px-3 py-2 border small" style="background-color: #FFFBEB; color: #92400E; border-color: #FDE68A !important;">
                            <span class="badge-semaforo-dot" style="background-color: #F59E0B;"></span>Moderado
                        </span>
                    @else
                        <span class="badge rounded-pill px-3 py-2 border small" style="background-color: #FEF2F2; color: #991B1B; border-color: #FECACA !important;">
                            <span class="badge-semaforo-dot" style="background-color: #EF4444;"></span>Crítico
                        </span>
                    @endif
                </div>

                <!-- Lista de Metadatos (Mockup Image 1) -->
                <div class="mb-3">
                    <div class="metadata-row">
                        <span class="metadata-label">Categoría:</span>
                        <span class="metadata-value">{{ $recurso->categoria ? $recurso->categoria->nombre : 'Sin categoría' }}</span>
                    </div>

                    <div class="metadata-row">
                        <span class="metadata-label">Cantidad:</span>
                        <span class="metadata-value">
                            <span class="{{ $recurso->cantidad_disponible <= $recurso->umbral_critico ? 'text-danger' : 'text-success' }}">
                                {{ $recurso->cantidad_disponible }}
                            </span>
                            <span class="text-muted fw-normal">de {{ $recurso->cantidad_total }} en total</span>
                        </span>
                    </div>

                    <div class="metadata-row">
                        <span class="metadata-label">Estado físico:</span>
                        <span class="metadata-value">
                            <span class="badge rounded-pill px-2 py-1 small" style="background-color: {{ $estadoFisico['bg'] }}; color: {{ $estadoFisico['text'] }};">
                                {{ $recurso->estado_fisico ?? 'Bueno' }}
                            </span>
                        </span>
                    </div>

                    <div class="metadata-row">
                        <span class="metadata-label">Semáforo:</span>
                        <span class="metadata-value">
                            @if($recurso->semaforo === 'verde')
                                <span class="text-success"><i class="fas fa-circle me-1" style="color: #10B981;"></i>Verde (buen nivel)</span>
                            @elseif($recurso->semaforo === 'amarillo')
                                <span class="text-warning-emphasis"><i class="fas fa-circle me-1" style="color: #F59E0B;"></i>Amarillo (nivel regular)</span>
                            @else
                                <span class="text-danger"><i class="fas fa-circle me-1" style="color: #EF4444;"></i>Rojo (nivel crítico)</span>
                            @endif
                        </span>
                    </div>
                </div>

                <!-- Observación -->
                <div class="mb-4">
                    <span class="metadata-label d-block mb-1">Observación:</span>
                    <p class="text-muted small mb-0 p-2 rounded-3 bg-light border">
                        {{ $recurso->observaciones ?: 'Equipo en buen estado, sin observaciones adicionales.' }}
                    </p>
                </div>

                <!-- Fotos en miniatura (Mockup Image 1) -->
                <div>
                    <span class="metadata-label d-block mb-2">Fotos:</span>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        @foreach($recurso->fotos->take(3) as $index => $foto)
                            <img src="{{ asset($foto->ruta) }}" alt="Foto {{ $index + 1 }}" class="thumb-gallery-item {{ $index === 0 ? 'active-thumb' : '' }}" onclick="cambiarFotoPrincipal('{{ asset($foto->ruta) }}', this)">
                        @endforeach

                        @if($recurso->fotos->count() > 3)
                            <div class="thumb-more-badge" data-bs-toggle="modal" data-bs-target="#modalGaleriaCompleta" title="Ver todas las fotos">
                                +{{ $recurso->fotos->count() - 3 }}
                            </div>
                        @endif
                    </div>
                </div>

                @if($recurso->estado === 'Inactivo')
                    <div class="mt-4 p-3 bg-danger-subtle rounded-3 text-danger border border-danger-subtle">
                        <div class="fw-bold"><i class="fas fa-ban me-1"></i> Dado de Baja:</div>
                        <div class="small">{{ $recurso->motivo_baja }}</div>
                        <div class="small text-muted mt-1">Fecha: {{ $recurso->fecha_baja ? $recurso->fecha_baja->format('d/m/Y H:i') : '' }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Columna Derecha: Pestañas de Movimientos y Novedades (Mockup Image 1) -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
                <div class="card-header bg-white border-0 pt-3 px-4 pb-0">
                    <ul class="nav nav-tabs card-header-tabs" id="recursoTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-semibold" id="movimientos-tab" data-bs-toggle="tab" data-bs-target="#movimientos-tab-pane" type="button" role="tab" style="color: #001A29;">
                                <i class="fas fa-clock-rotate-left me-1 text-primary"></i> Historial de movimientos
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold" id="novedades-tab" data-bs-toggle="tab" data-bs-target="#novedades-tab-pane" type="button" role="tab" style="color: #64748B;">
                                <i class="fas fa-flag me-1 text-warning"></i> Novedades ({{ $recurso->novedades->count() }})
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-4 tab-content" id="recursoTabContent">
                    <!-- Tab 1: Historial de Movimientos en 2 Tablas (HU 11-12) -->
                    <div class="tab-pane fade show active" id="movimientos-tab-pane" role="tabpanel">
                        <!-- Sub-Tabla 1: Lo que NO está / Falta por entregar (Préstamos pendientes) -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold text-danger mb-0 small text-uppercase">
                                    <i class="fas fa-hand-holding-box me-1"></i> 1. Préstamos pendientes (Lo que NO está en inventario)
                                </h6>
                                <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1 small">
                                    {{ $movimientosPendientes->count() }} pendiente(s)
                                </span>
                            </div>
                            <div class="table-responsive border rounded-3 bg-white">
                                <table class="table table-hover align-middle mb-0 small">
                                    <thead class="small fw-bold" style="background:#FFF5F5;">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Destino / Asesoría</th>
                                            <th class="text-center">Prestado</th>
                                            <th class="text-center">Devuelto</th>
                                            <th class="text-center">Saldo pendiente</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y">
                                        @forelse($movimientosPendientes as $p)
                                            <tr class="table-warning bg-opacity-25">
                                                <td class="text-muted text-nowrap">{{ $p['fecha'] }}</td>
                                                <td class="fw-semibold text-dark">{{ $p['destino'] }}</td>
                                                <td class="text-center">{{ $p['cantidad_prestada'] }} un.</td>
                                                <td class="text-center text-success">{{ $p['cantidad_devuelta'] }} un.</td>
                                                <td class="text-center fw-bold text-danger">{{ $p['saldo_pendiente'] }} un.</td>
                                                <td><span class="badge bg-danger rounded-pill">{{ $p['estado'] }}</span></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-3 text-success small">
                                                    <i class="fas fa-check-circle me-1"></i> No hay préstamos pendientes. Todo el inventario prestado se encuentra devuelto.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Sub-Tabla 2: Lo que SÍ está / Ya se entregó al inventario (Devoluciones y Entradas) -->
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold text-success mb-0 small text-uppercase">
                                    <i class="fas fa-circle-check me-1"></i> 2. Entradas y Devoluciones (Lo que SÍ está en inventario)
                                </h6>
                                <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 small">
                                    {{ $movimientosDevueltos->count() }} registro(s)
                                </span>
                            </div>
                            <div class="table-responsive border rounded-3 bg-white">
                                <table class="table table-hover align-middle mb-0 small">
                                    <thead class="small fw-bold" style="background:#F0FDF4;">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Tipo</th>
                                            <th class="text-center">Cantidad</th>
                                            <th>Observación / Autor</th>
                                            <th class="text-center">Evidencia</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y">
                                        @forelse($movimientosDevueltos as $d)
                                            <tr>
                                                <td class="text-muted text-nowrap">{{ $d['fecha'] }}</td>
                                                <td>
                                                    <span class="badge {{ $d['tipo'] === 'Devolución' ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' }} rounded-pill">
                                                        {{ $d['tipo'] }}
                                                    </span>
                                                </td>
                                                <td class="text-center fw-bold text-dark">{{ $d['cantidad'] }} un.</td>
                                                <td>
                                                    <div class="text-dark small">{{ $d['observacion'] ?: '-' }}</div>
                                                    <div class="text-muted" style="font-size: 0.72rem;">Por: {{ $d['autor'] }}</div>
                                                </td>
                                                <td class="text-center">
                                                    @if($d['foto'])
                                                        <a href="{{ $d['foto'] }}" target="_blank" class="badge bg-light text-primary border text-decoration-none">
                                                            <i class="fas fa-image me-1"></i>Ver
                                                        </a>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-3 text-muted small">
                                                    No hay devoluciones o entradas registradas para este recurso.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Botón Ver todo el historial (Mockup 1) -->
                        <div class="text-center mt-4 pt-3 border-top">
                            <a href="{{ route('controlecp.administrador.inventario.movimientos') }}" class="btn btn-outline-primary rounded-pill px-4 shadow-sm btn-sm">
                                <i class="fas fa-clock-rotate-left me-1"></i> Ver historial de movimientos general
                            </a>
                        </div>
                    </div>

                    <!-- Tab 2: Novedades -->
                    <div class="tab-pane fade" id="novedades-tab-pane" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0 text-dark">Registro de incidentes y novedades</h6>
                            <button type="button" class="btn text-white rounded-pill px-3 btn-sm shadow-sm" style="background-color: #39A900;" data-bs-toggle="modal" data-bs-target="#modalNovedad">
                                <i class="fas fa-plus me-1"></i> Registrar novedad
                            </button>
                        </div>

                        @forelse($recurso->novedades as $nov)
                            <div class="p-3 mb-3 border rounded-3 bg-white shadow-sm">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge bg-light text-dark border">
                                        <i class="fas fa-flag me-1 text-warning"></i>Novedad
                                    </span>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-day me-1"></i>{{ $nov->fecha ? $nov->fecha->format('d/m/Y H:i') : '' }}
                                    </small>
                                </div>
                                <p class="text-dark small mb-2 mt-2">{{ $nov->descripcion }}</p>
                                <div class="border-top pt-2 small text-muted">
                                    <i class="fas fa-user me-1"></i>Registrado por: <strong>{{ $nov->usuario ? $nov->usuario->nickname : 'Sistema' }}</strong>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted small">
                                <i class="fas fa-comment-dots fa-3x mb-2 text-secondary opacity-50"></i>
                                <div>No se han registrado novedades para este recurso.</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODALES DE OPERACIONES DEL INVENTARIO      -->
<!-- ========================================== -->

<!-- Modal: Registrar Entrega -->
<div class="modal fade" id="modalEntrega" tabindex="-1" aria-labelledby="modalEntregaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form action="{{ route('controlecp.administrador.inventario.entrega', $recurso->id_inventario) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalEntregaLabel" style="color: #001A29;">
                        <i class="fas fa-hand-holding-box me-2 text-primary"></i>Registrar entrega de recurso
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 small mb-3">
                        Existencias disponibles actualmente: <strong>{{ $recurso->cantidad_disponible }}</strong> unidad(es).
                    </div>

                    <div class="mb-3">
                        <label for="cantidadEntrega" class="form-label small fw-semibold text-dark">Cantidad a Entregar <span class="text-danger">*</span></label>
                        <input type="number" name="cantidad" id="cantidadEntrega" class="form-control rounded-3" min="1" max="{{ $recurso->cantidad_disponible }}" value="1" required>
                    </div>

                    <div class="mb-3">
                        <label for="destinoEntrega" class="form-label small fw-semibold text-dark">Destino / Responsable <span class="text-danger">*</span></label>
                        <input type="text" name="destino" id="destinoEntrega" class="form-control rounded-3" placeholder="Ej: Taller Ficha 2856471 - Ambiente 204" required>
                    </div>

                    <div class="mb-3">
                        <label for="observacionEntrega" class="form-label small fw-semibold text-dark">Observaciones</label>
                        <textarea name="observacion" id="observacionEntrega" class="form-control rounded-3" rows="2" placeholder="Estado en que se entrega, compromisos..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="fotoEntrega" class="form-label small fw-semibold text-dark">Adjuntar foto de evidencia (Opcional)</label>
                        <input type="file" name="foto" id="fotoEntrega" class="form-control rounded-3" accept="image/jpeg,image/png">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        Registrar Entrega
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Registrar Devolución -->
<div class="modal fade" id="modalDevolucion" tabindex="-1" aria-labelledby="modalDevolucionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form action="{{ route('controlecp.administrador.inventario.devolucion', $recurso->id_inventario) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalDevolucionLabel" style="color: #001A29;">
                        <i class="fas fa-arrow-rotate-left me-2 text-success"></i>Registrar devolución de recurso
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="entregaAsociada" class="form-label small fw-semibold text-dark">Entrega Relacionada (Opcional para comparar)</label>
                        <select name="id_movimiento_entrega" id="entregaAsociada" class="form-select rounded-3">
                            <option value="">Devolución general / Sin entrega previa</option>
                            @foreach($entregasPendientes as $ep)
                                <option value="{{ $ep->id_movimiento_inventario }}">
                                    {{ $ep->fecha ? $ep->fecha->format('d/m/Y') : '' }} - Entregadas: {{ $ep->cantidad }} un. ({{ Str::limit($ep->observacion, 35) }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text small text-muted">Si la cantidad devuelta es menor que la entregada, se generará una <strong>alerta de devolución incompleta</strong>.</div>
                    </div>

                    <div class="mb-3">
                        <label for="cantidadDevolucion" class="form-label small fw-semibold text-dark">Cantidad Devuelta <span class="text-danger">*</span></label>
                        <input type="number" name="cantidad" id="cantidadDevolucion" class="form-control rounded-3" min="1" value="1" required>
                    </div>

                    <div class="mb-3">
                        <label for="observacionDevolucion" class="form-label small fw-semibold text-dark">Observaciones</label>
                        <textarea name="observacion" id="observacionDevolucion" class="form-control rounded-3" rows="2" placeholder="Estado en que se devuelve, novedades..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="fotoDevolucion" class="form-label small fw-semibold text-dark">Foto de evidencia (Opcional)</label>
                        <input type="file" name="foto" id="fotoDevolucion" class="form-control rounded-3" accept="image/jpeg,image/png">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white rounded-pill px-4 shadow-sm" style="background-color: #39A900; border-color: #39A900;">
                        Registrar Devolución
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Agregar Novedad -->
<div class="modal fade" id="modalNovedad" tabindex="-1" aria-labelledby="modalNovedadLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form action="{{ route('controlecp.administrador.inventario.novedades.store', $recurso->id_inventario) }}" method="POST">
                @csrf
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalNovedadLabel" style="color: #001A29;">
                        <i class="fas fa-comment-dots me-2 text-warning"></i>Registrar novedad
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="descripcionNovedad" class="form-label small fw-semibold text-dark">Descripción de la Novedad <span class="text-danger">*</span></label>
                        <textarea name="descripcion" id="descripcionNovedad" class="form-control rounded-3" rows="4" placeholder="Describa el incidente, deterioro, mantenimiento o novedad ocurrida..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white rounded-pill px-4 shadow-sm" style="background-color: #39A900; border-color: #39A900;">
                        Guardar Novedad
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Dar de Baja (Mockup 1 Row 3 Middle) -->
<div class="modal fade" id="modalBaja" tabindex="-1" aria-labelledby="modalBajaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form action="{{ route('controlecp.administrador.inventario.dar-baja', $recurso->id_inventario) }}" method="POST">
                @csrf
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalBajaLabel" style="color: #001A29;">
                        Dar de baja un recurso
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Resumen del recurso (Exacto Mockup 1) -->
                    <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 mb-3 border">
                        @if($fotoPrincipal && $fotoPrincipal->ruta)
                            <img src="{{ asset($fotoPrincipal->ruta) }}" alt="Foto" class="rounded-3" style="width: 50px; height: 50px; object-fit: cover;">
                        @else
                            <div class="rounded-3 bg-white d-flex align-items-center justify-content-center text-muted border" style="width: 50px; height: 50px;">
                                <i class="fas fa-box"></i>
                            </div>
                        @endif
                        <div>
                            <div class="fw-bold text-dark">{{ $recurso->nombre }}</div>
                            <div class="small text-muted">Cantidad actual: <strong>{{ $recurso->cantidad_disponible }}</strong></div>
                            <div class="small text-success">Estado: Activo</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="motivoBaja" class="form-label small fw-semibold text-dark">Motivo de baja <span class="text-danger">*</span></label>
                        <select name="motivo" id="motivoBaja" class="form-select rounded-3" required>
                            <option value="">Selecciona un motivo</option>
                            <option value="Dañado">Dañado</option>
                            <option value="Perdido">Perdido</option>
                            <option value="Fuera de servicio">Fuera de servicio</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="descripcionBaja" class="form-label small fw-semibold text-dark">Observación <span class="text-danger">*</span></label>
                        <textarea name="descripcion" id="descripcionBaja" class="form-control rounded-3" rows="3" placeholder="Detalle del motivo" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm" onclick="return confirm('¿Está seguro de dar de baja este recurso?');">
                        Dar de baja
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Galería Completa de Fotos -->
<div class="modal fade" id="modalGaleriaCompleta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold" style="color: #001A29;">
                    <i class="fas fa-images me-2" style="color: #39A900;"></i>Fotografías del recurso ({{ $recurso->fotos->count() }})
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    @foreach($recurso->fotos as $f)
                        <div class="col-6 col-md-4">
                            <a href="{{ asset($f->ruta) }}" target="_blank">
                                <img src="{{ asset($f->ruta) }}" class="rounded-3 w-100 border shadow-sm" style="height: 160px; object-fit: cover;">
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function cambiarFotoPrincipal(src, thumbElement) {
        const imgPrincipal = document.getElementById('imgPrincipalRecurso');
        if (imgPrincipal) {
            imgPrincipal.src = src;
        }
        document.querySelectorAll('.thumb-gallery-item').forEach(el => el.classList.remove('active-thumb'));
        if (thumbElement) {
            thumbElement.classList.add('active-thumb');
        }
    }
</script>
@endpush
@endsection
