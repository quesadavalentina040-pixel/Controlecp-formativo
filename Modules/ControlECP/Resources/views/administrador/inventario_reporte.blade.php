@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    /* Estilos de gráficas y tarjetas de reporte */
    .report-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 1.4rem;
        height: 100%;
        box-shadow: 0 2px 10px rgba(0, 26, 41, 0.03);
    }
    .report-filter-bar {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 1rem 1.25rem;
    }
    .chart-center-label {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        pointer-events: none;
    }
    .nav-pills-custom .nav-link {
        color: #64748B;
        font-weight: 600;
        border-radius: 9999px;
        padding: 0.5rem 1.25rem;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }
    .nav-pills-custom .nav-link.active {
        background-color: #39A900;
        color: #FFFFFF;
        box-shadow: 0 2px 8px rgba(57, 169, 0, 0.25);
    }
    @media print {
        .no-print { display: none !important; }
        .d-print-block { display: block !important; }
        body { background: #FFFFFF !important; font-size: 11pt; }
        .printable-area { width: 100% !important; padding: 0 !important; margin: 0 !important; }
        .card { border: 1px solid #CBD5E1 !important; box-shadow: none !important; break-inside: avoid; }
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2 printable-area">

    <!-- Breadcrumb & Cabecera (Mockup Image 1) -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom no-print">
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
                        Reportes de inventario
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: #001A29; font-family: 'Outfit', sans-serif;">
                Reportes de inventario
            </h2>
            <p class="text-muted small mb-0">HU-014: Criterio 19 &bull; Métricas de disponibilidad, trazabilidad de movimientos y reporte ejecutivo para exportación PDF</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('controlecp.administrador.inventario') }}" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver al inventario
            </a>
            <button type="button" class="btn text-white rounded-pill px-4 shadow-sm btn-sm" style="background-color: #39A900; border-color: #39A900;" onclick="window.print()">
                <i class="fas fa-file-pdf me-1"></i> Exportar PDF
            </button>
        </div>
    </div>

    <!-- Encabezado Institucional para Impresión / PDF (Mockup Image 1 Row 4 Middle) -->
    <div class="d-none d-print-block text-center mb-4 pb-3 border-bottom">
        <h4 class="fw-bold mb-1" style="color: #001A29;">CONTROL ECP &bull; REPORTE DE INVENTARIO</h4>
        <h6 class="text-muted mb-2">Centro de Formación Agroindustrial La Angostura &bull; SENA Regional Huila</h6>
        <div class="small text-muted">
            Fecha de emisión: {{ now()->format('d/m/Y H:i:s') }} &bull; Generado por: {{ Auth::user()->nickname ?? 'Administrador' }}
        </div>

        <!-- Tarjetas Resumen en Impresión -->
        <div class="row g-2 mt-3 text-start">
            <div class="col-3">
                <div class="border p-2 rounded text-center">
                    <small class="text-muted d-block">Total de recursos</small>
                    <strong class="fs-5">{{ $total }}</strong>
                </div>
            </div>
            <div class="col-3">
                <div class="border p-2 rounded text-center" style="background: #F0FDF4;">
                    <small class="text-success d-block">Disponibles</small>
                    <strong class="fs-5 text-success">{{ $verdeCount }}</strong>
                </div>
            </div>
            <div class="col-3">
                <div class="border p-2 rounded text-center" style="background: #FFFBEB;">
                    <small class="text-warning-emphasis d-block">En estado regular</small>
                    <strong class="fs-5 text-warning-emphasis">{{ $amarilloCount }}</strong>
                </div>
            </div>
            <div class="col-3">
                <div class="border p-2 rounded text-center" style="background: #FEF2F2;">
                    <small class="text-danger d-block">En estado crítico</small>
                    <strong class="fs-5 text-danger">{{ $rojoCount }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de Filtros Superior (Exacta del Mockup 1 Row 3 Right) -->
    <div class="report-filter-bar mb-4 shadow-sm no-print">
        <form action="{{ route('controlecp.administrador.inventario.reporte') }}" method="GET" class="row g-2 align-items-center">
            <!-- Selector Ficha -->
            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label small text-muted mb-1 fw-semibold">Ficha</label>
                <select name="ficha" class="form-select form-select-sm rounded-pill">
                    <option value="">Todas las fichas</option>
                    <option value="todas" {{ request('ficha') ? 'selected' : '' }}>Todas (Formación Agroindustrial)</option>
                </select>
            </div>

            <!-- Rango de fechas: Desde y Hasta -->
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Desde</label>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="form-control form-select-sm rounded-pill">
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="form-control form-select-sm rounded-pill">
            </div>

            <!-- Selector Usuario -->
            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label small text-muted mb-1 fw-semibold">Usuario</label>
                <select name="id_usuario" class="form-select form-select-sm rounded-pill">
                    <option value="">Todos los usuarios ({{ $usuarios->count() }})</option>
                    @foreach($usuarios as $u)
                        <option value="{{ $u->id }}" {{ request('id_usuario') == $u->id ? 'selected' : '' }}>
                            {{ $u->nickname ?? $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Botones Generar Reporte y Exportar PDF -->
            <div class="col-12 col-md-2 d-flex gap-1 align-items-end mt-3 mt-md-0">
                <button type="submit" class="btn btn-sm text-white rounded-pill px-3 flex-grow-1 shadow-sm" style="background-color: #39A900; border-color: #39A900;">
                    <i class="fas fa-filter me-1"></i> Generar reporte
                </button>
            </div>
        </form>
    </div>

    <!-- Pestañas de Navegación del Reporte (Mockup 1 Row 3 Right) -->
    <div class="mb-4 no-print">
        <ul class="nav nav-pills nav-pills-custom gap-2" id="reportTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pills-resumen-tab" data-bs-toggle="pill" data-bs-target="#pills-resumen" type="button" role="tab">
                    Resumen
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-recursos-tab" data-bs-toggle="pill" data-bs-target="#pills-recursos" type="button" role="tab">
                    Recursos ({{ $total }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-movimientos-tab" data-bs-toggle="pill" data-bs-target="#pills-movimientos" type="button" role="tab">
                    Movimientos ({{ $movimientos->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-novedades-tab" data-bs-toggle="pill" data-bs-target="#pills-novedades" type="button" role="tab">
                    Novedades ({{ $novedades->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link position-relative" id="pills-alertas-tab" data-bs-toggle="pill" data-bs-target="#pills-alertas" type="button" role="tab">
                    Alertas
                    @if($alertas->count() > 0)
                        <span class="badge rounded-pill bg-danger ms-1">{{ $alertas->count() }}</span>
                    @endif
                </button>
            </li>
        </ul>
    </div>

    <!-- Contenido de las Pestañas -->
    <div class="tab-content" id="reportTabsContent">

        <!-- ========================================== -->
        <!-- PESTAÑA 1: RESUMEN (3 Gráficas Exactas)    -->
        <!-- ========================================== -->
        <div class="tab-pane fade show active" id="pills-resumen" role="tabpanel">
            <div class="row g-4 mb-4">
                <!-- Gráfica 1: Estado de recursos (Doughnut con Total en el centro) -->
                <div class="col-12 col-lg-4">
                    <div class="report-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0" style="color: #001A29; font-size: 1rem;">
                                Estado de recursos
                            </h5>
                        </div>

                        <div class="position-relative" style="height: 220px;">
                            <canvas id="chartEstadoRecursos"></canvas>
                            <div class="chart-center-label">
                                <div class="text-muted small fw-semibold" style="font-size: 0.75rem;">Total</div>
                                <div class="h3 fw-bold mb-0" style="color: #001A29;">{{ $total }}</div>
                            </div>
                        </div>

                        <!-- Leyendas personalizadas con viñetas de color (Mockup 1) -->
                        <div class="mt-3 pt-3 border-top small">
                            <div class="d-flex justify-content-between align-items-center py-1">
                                <span><i class="fas fa-circle me-1" style="color: #10B981;"></i> Disponibles</span>
                                <span class="fw-bold text-dark">{{ $verdeCount }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-1">
                                <span><i class="fas fa-circle me-1" style="color: #F59E0B;"></i> En estado regular</span>
                                <span class="fw-bold text-dark">{{ $amarilloCount }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-1">
                                <span><i class="fas fa-circle me-1" style="color: #EF4444;"></i> En estado crítico</span>
                                <span class="fw-bold text-dark">{{ $rojoCount }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráfica 2: Movimientos por tipo (Horizontal Bar Chart) -->
                <div class="col-12 col-lg-4">
                    <div class="report-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0" style="color: #001A29; font-size: 1rem;">
                                Movimientos por tipo
                            </h5>
                        </div>

                        <div style="height: 250px;">
                            <canvas id="chartMovimientosTipo"></canvas>
                        </div>

                        <div class="mt-3 pt-2 border-top small text-muted text-center" style="font-size: 0.75rem;">
                            Total de movimientos registrados en el periodo: <strong>{{ $entregasCount + $devolucionesCount + $cambioEstadoCount + $bajasCount }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Gráfica 3: Recursos por categoría (Pie Chart) -->
                <div class="col-12 col-lg-4">
                    <div class="report-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0" style="color: #001A29; font-size: 1rem;">
                                Recursos por categoría
                            </h5>
                        </div>

                        <div style="height: 250px;">
                            <canvas id="chartRecursosCategoria"></canvas>
                        </div>

                        <div class="mt-3 pt-2 border-top small text-muted text-center" style="font-size: 0.75rem;">
                            Distribución en <strong>{{ $categorias->count() }}</strong> categorías registradas
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- PESTAÑA 2: RECURSOS (Listado Completo)     -->
        <!-- ========================================== -->
        <div class="tab-pane fade" id="pills-recursos" role="tabpanel">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background-color: #F8FAFC; color: #001A29;" class="small fw-bold border-bottom">
                                <tr>
                                    <th class="ps-4 py-3">Nombre</th>
                                    <th class="py-3">Categoría</th>
                                    <th class="py-3 text-center">Disponible</th>
                                    <th class="py-3 text-center">Total</th>
                                    <th class="py-3 text-center">Estado físico</th>
                                    <th class="py-3 text-center">Semáforo</th>
                                    <th class="text-end pe-4 py-3 no-print">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y small">
                                @forelse($recursos as $r)
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark">{{ $r->nombre }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $r->categoria ? $r->categoria->nombre : 'Sin categoría' }}</span></td>
                                        <td class="text-center fw-bold {{ $r->cantidad_disponible <= $r->umbral_critico ? 'text-danger' : 'text-success' }}">{{ $r->cantidad_disponible }}</td>
                                        <td class="text-center text-muted">{{ $r->cantidad_total }}</td>
                                        <td class="text-center">
                                            <span class="badge rounded-pill px-2 py-1 small" style="background-color: {{ $r->estado_fisico_badge['bg'] }}; color: {{ $r->estado_fisico_badge['text'] }};">
                                                {{ $r->estado_fisico ?? 'Bueno' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($r->semaforo === 'verde')
                                                <span class="badge rounded-pill px-2 py-1 border" style="background-color: #ECFDF5; color: #065F46; border-color: #A7F3D0 !important;">
                                                    🟢 Disponible
                                                </span>
                                            @elseif($r->semaforo === 'amarillo')
                                                <span class="badge rounded-pill px-2 py-1 border" style="background-color: #FFFBEB; color: #92400E; border-color: #FDE68A !important;">
                                                    🟡 Moderado
                                                </span>
                                            @else
                                                <span class="badge rounded-pill px-2 py-1 border" style="background-color: #FEF2F2; color: #991B1B; border-color: #FECACA !important;">
                                                    🔴 Crítico
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4 no-print">
                                            <a href="{{ route('controlecp.administrador.inventario.show', $r->id_inventario) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2" title="Ver detalle">
                                                <i class="fas fa-eye text-primary"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center py-4 text-muted">No se encontraron recursos.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- PESTAÑA 3: MOVIMIENTOS                     -->
        <!-- ========================================== -->
        <div class="tab-pane fade" id="pills-movimientos" role="tabpanel">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background-color: #F8FAFC; color: #001A29;" class="small fw-bold border-bottom">
                                <tr>
                                    <th class="ps-4 py-3">Fecha</th>
                                    <th class="py-3">Tipo</th>
                                    <th class="py-3">Recurso</th>
                                    <th class="py-3 text-center">Cantidad</th>
                                    <th class="py-3">Observación</th>
                                    <th class="pe-4 py-3 text-end">Autor</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y small">
                                @forelse($movimientos as $m)
                                    <tr>
                                        <td class="ps-4 text-nowrap text-muted">{{ $m->fecha ? $m->fecha->format('d/m/Y H:i') : '' }}</td>
                                        <td>
                                            @if($m->tipo === 'Entrega')
                                                <span class="badge rounded-pill px-2 py-1" style="background-color: #E0F2FE; color: #0369A1;">Entrega</span>
                                            @elseif($m->tipo === 'Devolución')
                                                <span class="badge rounded-pill px-2 py-1" style="background-color: #DCFCE7; color: #15803D;">Devolución</span>
                                            @elseif($m->tipo === 'Baja')
                                                <span class="badge rounded-pill px-2 py-1" style="background-color: #FEE2E2; color: #B91C1C;">Baja</span>
                                            @else
                                                <span class="badge rounded-pill px-2 py-1 bg-light text-dark border">{{ $m->tipo }}</span>
                                            @endif
                                        </td>
                                        <td class="fw-semibold text-dark">{{ $m->inventario ? $m->inventario->nombre : 'Recurso' }}</td>
                                        <td class="text-center fw-bold">{{ $m->cantidad }}</td>
                                        <td class="text-truncate" style="max-width: 250px;">{{ $m->observacion }}</td>
                                        <td class="pe-4 text-end text-muted">{{ $m->usuario ? $m->usuario->nickname : 'Sistema' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-4 text-muted">No hay movimientos en este periodo.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- PESTAÑA 4: NOVEDADES                       -->
        <!-- ========================================== -->
        <div class="tab-pane fade" id="pills-novedades" role="tabpanel">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <div class="row g-3">
                        @forelse($novedades as $nov)
                            <div class="col-12 col-md-6">
                                <div class="p-3 rounded-3 border bg-white shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="text-dark">{{ $nov->inventario ? $nov->inventario->nombre : 'Recurso' }}</strong>
                                        <small class="text-muted">{{ $nov->fecha ? $nov->fecha->format('d/m/Y H:i') : '' }}</small>
                                    </div>
                                    <p class="small text-muted mb-2">{{ $nov->descripcion }}</p>
                                    <div class="small text-secondary pt-2 border-top">
                                        <i class="fas fa-user me-1"></i> Autor: {{ $nov->usuario ? $nov->usuario->nickname : 'Sistema' }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-5 text-muted">No se registran novedades.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- PESTAÑA 5: ALERTAS (Devoluciones Incompletas) -->
        <!-- ========================================== -->
        <div class="tab-pane fade" id="pills-alertas" role="tabpanel">
            @forelse($alertas as $al)
                <div class="card border-0 shadow-sm mb-3 border-start border-4 border-danger" style="background: #FFF5F5; border-radius: 12px;">
                    <div class="card-body p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="text-danger fs-3"><i class="fas fa-triangle-exclamation"></i></div>
                            <div>
                                <span class="badge bg-danger text-white mb-1">Devolución incompleta</span>
                                <div class="fw-semibold text-danger small">{{ $al->observacion }}</div>
                                <div class="text-muted small">
                                    Recurso: <strong>{{ $al->inventario ? $al->inventario->nombre : 'N/A' }}</strong> &bull;
                                    Fecha: {{ $al->fecha ? $al->fecha->format('d/m/Y H:i') : '' }} &bull;
                                    Responsable: {{ $al->usuario ? $al->usuario->nickname : 'Sistema' }}
                                </div>
                            </div>
                        </div>
                        @if($al->inventario)
                            <a href="{{ route('controlecp.administrador.inventario.show', $al->inventario->id_inventario) }}" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                Ver detalle
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="card border-0 shadow-sm p-5 text-center text-muted" style="border-radius: 16px;">
                    <i class="fas fa-circle-check fa-3x text-success mb-2"></i>
                    <h5 class="fw-bold text-dark">No hay alertas de devolución pendientes</h5>
                    <p class="small text-muted mb-0">Todas las entregas han sido devueltas con sus cantidades completas.</p>
                </div>
            @endforelse
        </div>

    </div>
</div>

@push('scripts')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- 1. Gráfica Doughnut: Estado de recursos (Mockup 1) ---
        const ctxEstado = document.getElementById('chartEstadoRecursos');
        if (ctxEstado) {
            new Chart(ctxEstado, {
                type: 'doughnut',
                data: {
                    labels: ['Disponibles', 'En estado regular', 'En estado crítico'],
                    datasets: [{
                        data: [{{ $verdeCount }}, {{ $amarilloCount }}, {{ $rojoCount }}],
                        backgroundColor: ['#10B981', '#F59E0B', '#EF4444'],
                        borderWidth: 3,
                        borderColor: '#FFFFFF'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }

        // --- 2. Gráfica Barras Horizontales: Movimientos por tipo (Mockup 1) ---
        const ctxMov = document.getElementById('chartMovimientosTipo');
        if (ctxMov) {
            new Chart(ctxMov, {
                type: 'bar',
                data: {
                    labels: ['Entregas', 'Devoluciones', 'Cambios estado', 'Bajas'],
                    datasets: [{
                        label: 'Movimientos',
                        data: [{{ $entregasCount }}, {{ $devolucionesCount }}, {{ $cambioEstadoCount }}, {{ $bajasCount }}],
                        backgroundColor: ['#0284C7', '#10B981', '#38BDF8', '#F87171'],
                        borderRadius: 8
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    }
                }
            });
        }

        // --- 3. Gráfica Circular: Recursos por categoría (Mockup 1) ---
        const ctxCat = document.getElementById('chartRecursosCategoria');
        if (ctxCat) {
            new Chart(ctxCat, {
                type: 'pie',
                data: {
                    labels: {!! json_encode($categoriasLabels) !!},
                    datasets: [{
                        data: {!! json_encode($categoriasCounts) !!},
                        backgroundColor: [
                            '#3B82F6', '#F97316', '#EC4899', '#06B6D4', '#EAB308', '#8B5CF6', '#10B981'
                        ],
                        borderWidth: 2,
                        borderColor: '#FFFFFF'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                font: { size: 10 }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection
