@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    /* Estilos de gráficas y tarjetas de reporte PQRS */
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
        body { background: #FFFFFF !important; font-size: 10.5pt; }
        .printable-area { width: 100% !important; padding: 0 !important; margin: 0 !important; }
        .card { border: 1px solid #CBD5E1 !important; box-shadow: none !important; break-inside: avoid; }
        .tab-pane { display: block !important; opacity: 1 !important; visibility: visible !important; }
        .table { font-size: 9pt; }
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2 printable-area">

    <!-- Breadcrumb & Cabecera (No imprimible en pantalla de edición) -->
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
                        <a href="{{ route('controlecp.administrador.pqr') }}" class="text-decoration-none" style="color: #39A900;">
                            PQRS
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page" style="color: #001A29; font-weight: 600;">
                        Informe y Reporte Ejecutivo
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: #001A29; font-family: 'Outfit', sans-serif;">
                Informe y Reportes de PQRS
            </h2>
            <p class="text-muted small mb-0">Métricas consolidadas, análisis por tipología, estados de atención y exportación ejecutiva en PDF</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('controlecp.administrador.pqr') }}" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver a la bandeja
            </a>
            <button type="button" class="btn text-white rounded-pill px-4 shadow-sm btn-sm" style="background-color: #39A900; border-color: #39A900;" onclick="window.print()">
                <i class="fas fa-file-pdf me-1"></i> Exportar PDF / Imprimir
            </button>
        </div>
    </div>

    <!-- Encabezado Institucional SENA para Impresión / PDF -->
    <div class="d-none d-print-block text-center mb-4 pb-3 border-bottom">
        <h4 class="fw-bold mb-1" style="color: #001A29;">SERVICIO NACIONAL DE APRENDIZAJE - SENA</h4>
        <h5 class="fw-semibold text-secondary mb-1" style="font-size: 1.1rem;">CENTRO DE FORMACIÓN AGROINDUSTRIAL LA ANGOSTURA &bull; REGIONAL HUILA</h5>
        <h6 class="text-muted mb-2">Escuela Cultura de Paz (Control ECP) &bull; Informe Estadístico de PQRS</h6>
        <div class="small text-muted">
            Fecha de emisión: {{ now()->format('d/m/Y H:i:s') }} &bull; Generado por: {{ Auth::user()->nickname ?? 'Administrador General' }}
        </div>

        <!-- Tarjetas Resumen en Impresión -->
        <div class="row g-2 mt-3 text-start">
            <div class="col-3">
                <div class="border p-2 rounded text-center">
                    <small class="text-muted d-block">Total Radicadas</small>
                    <strong class="fs-5">{{ $total }}</strong>
                </div>
            </div>
            <div class="col-3">
                <div class="border p-2 rounded text-center" style="background: #FFFBEB;">
                    <small class="text-warning-emphasis d-block">Pendientes</small>
                    <strong class="fs-5 text-warning-emphasis">{{ $pendientes }}</strong>
                </div>
            </div>
            <div class="col-3">
                <div class="border p-2 rounded text-center" style="background: #EFF6FF;">
                    <small class="text-primary d-block">En trámite</small>
                    <strong class="fs-5 text-primary">{{ $enTramite }}</strong>
                </div>
            </div>
            <div class="col-3">
                <div class="border p-2 rounded text-center" style="background: #F0FDF4;">
                    <small class="text-success d-block">Resueltas ({{ $tasaResolucion }}%)</small>
                    <strong class="fs-5 text-success">{{ $resueltas }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de Filtros Superior (No imprimible) -->
    <div class="report-filter-bar mb-4 shadow-sm no-print">
        <form action="{{ route('controlecp.administrador.pqr.reporte') }}" method="GET" class="row g-2 align-items-center">
            <!-- Tipo de PQRS -->
            <div class="col-6 col-md-3">
                <label class="form-label small text-muted mb-1 fw-semibold">Tipo de Solicitud</label>
                <select name="id_tipo_pqrs" class="form-select form-select-sm rounded-pill">
                    <option value="">Todos los tipos</option>
                    @foreach($tipos as $tipoItem)
                        <option value="{{ $tipoItem->id_tipo_pqrs }}" {{ request('id_tipo_pqrs') == $tipoItem->id_tipo_pqrs ? 'selected' : '' }}>
                            {{ $tipoItem->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Estado -->
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Estado</label>
                <select name="estado" class="form-select form-select-sm rounded-pill">
                    <option value="">Todos los estados</option>
                    <option value="Pendiente" {{ request('estado') == 'Pendiente' ? 'selected' : '' }}>🟡 Pendientes</option>
                    <option value="En trámite" {{ request('estado') == 'En trámite' ? 'selected' : '' }}>🔵 En trámite</option>
                    <option value="Resuelta" {{ request('estado') == 'Resuelta' ? 'selected' : '' }}>🟢 Resueltas</option>
                </select>
            </div>

            <!-- Responsable -->
            <div class="col-6 col-md-3">
                <label class="form-label small text-muted mb-1 fw-semibold">Destino / Responsable</label>
                <select name="id_instructor" class="form-select form-select-sm rounded-pill">
                    <option value="">Todos los responsables</option>
                    <option value="admin" {{ request('id_instructor') === 'admin' ? 'selected' : '' }}>🏛️ Administración General</option>
                    @foreach($instructores as $inst)
                        <option value="{{ $inst->id }}" {{ request('id_instructor') == $inst->id ? 'selected' : '' }}>
                            👨‍🏫 {{ $inst->nickname }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Rango Fechas -->
            <div class="col-3 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Desde</label>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="form-control form-control-sm rounded-pill">
            </div>

            <div class="col-3 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="form-control form-control-sm rounded-pill">
            </div>

            <!-- Botones de Acción -->
            <div class="col-12 d-flex gap-2 justify-content-end mt-2">
                @if(request()->hasAny(['id_tipo_pqrs', 'estado', 'id_instructor', 'fecha_desde', 'fecha_hasta']))
                    <a href="{{ route('controlecp.administrador.pqr.reporte') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="fas fa-times me-1"></i> Limpiar filtros
                    </a>
                @endif
                <button type="submit" class="btn btn-sm text-white rounded-pill px-4 shadow-sm" style="background-color: #39A900; border-color: #39A900;">
                    <i class="fas fa-filter me-1"></i> Aplicar Filtros al Reporte
                </button>
            </div>
        </form>
    </div>

    <!-- Pestañas de Navegación del Reporte (No imprimible) -->
    <div class="mb-4 no-print">
        <ul class="nav nav-pills nav-pills-custom gap-2" id="reportTabsPqrs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pills-resumen-tab" data-bs-toggle="pill" data-bs-target="#pills-resumen" type="button" role="tab">
                    <i class="fas fa-chart-pie me-1"></i> Resumen Gráfico
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-listado-tab" data-bs-toggle="pill" data-bs-target="#pills-listado" type="button" role="tab">
                    <i class="fas fa-table-list me-1"></i> Listado Detallado ({{ $total }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-respuestas-tab" data-bs-toggle="pill" data-bs-target="#pills-respuestas" type="button" role="tab">
                    <i class="fas fa-reply-all me-1"></i> Respuestas Oficiales Emitidas
                </button>
            </li>
        </ul>
    </div>

    <!-- Contenido de las Pestañas -->
    <div class="tab-content" id="reportTabsPqrsContent">

        <!-- ========================================== -->
        <!-- PESTAÑA 1: RESUMEN GRÁFICO (3 Gráficas)     -->
        <!-- ========================================== -->
        <div class="tab-pane fade show active" id="pills-resumen" role="tabpanel">
            <div class="row g-4 mb-4">
                <!-- Gráfica 1: Por Tipo -->
                <div class="col-12 col-lg-4">
                    <div class="report-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0" style="color: #001A29; font-size: 1rem;">
                                Solicitudes por Tipo
                            </h5>
                        </div>

                        <div class="position-relative" style="height: 220px;">
                            <canvas id="chartReporteTipo"></canvas>
                        </div>

                        <div class="mt-3 pt-3 border-top small">
                            @foreach($tipos as $idx => $t)
                                @php
                                    $cnt = $pqrs->where('id_tipo_pqrs', $t->id_tipo_pqrs)->count();
                                @endphp
                                <div class="d-flex justify-content-between align-items-center py-1">
                                    <span>
                                        <i class="fas fa-circle me-1" style="color: {{ ['#0284C7', '#EF4444', '#F97316', '#8B5CF6', '#10B981'][$idx % 5] }};"></i>
                                        {{ $t->nombre }}
                                    </span>
                                    <span class="fw-bold text-dark">{{ $cnt }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Gráfica 2: Por Estado y Tasa -->
                <div class="col-12 col-lg-4">
                    <div class="report-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0" style="color: #001A29; font-size: 1rem;">
                                Estado de Gestión
                            </h5>
                            <span class="badge rounded-pill" style="background-color: #E2F5D7; color: #39A900; font-size: 0.75rem;">
                                {{ $tasaResolucion }}% Resueltas
                            </span>
                        </div>

                        <div class="position-relative" style="height: 220px;">
                            <canvas id="chartReporteEstado"></canvas>
                            <div class="chart-center-label">
                                <div class="text-muted small fw-semibold" style="font-size: 0.75rem;">Total</div>
                                <div class="h3 fw-bold mb-0" style="color: #001A29;">{{ $total }}</div>
                            </div>
                        </div>

                        <div class="mt-3 pt-3 border-top small">
                            <div class="d-flex justify-content-between align-items-center py-1">
                                <span><i class="fas fa-circle me-1" style="color: #F59E0B;"></i> Pendientes</span>
                                <span class="fw-bold text-dark">{{ $pendientes }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-1">
                                <span><i class="fas fa-circle me-1" style="color: #0284C7;"></i> En trámite</span>
                                <span class="fw-bold text-dark">{{ $enTramite }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-1">
                                <span><i class="fas fa-circle me-1" style="color: #10B981;"></i> Resueltas</span>
                                <span class="fw-bold text-dark">{{ $resueltas }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráfica 3: Por Instructor / Destino -->
                <div class="col-12 col-lg-4">
                    <div class="report-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0" style="color: #001A29; font-size: 1rem;">
                                Asignación por Responsable
                            </h5>
                        </div>

                        <div style="height: 220px;">
                            <canvas id="chartReporteInstructor"></canvas>
                        </div>

                        <div class="mt-3 pt-3 border-top small text-muted">
                            Supervisión consolidada de directas a instructores y administración general.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- PESTAÑA 2: LISTADO DETALLADO               -->
        <!-- ========================================== -->
        <div class="tab-pane fade" id="pills-listado" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0" style="color: #001A29;">
                        Listado Consolidado de Solicitudes ({{ $total }})
                    </h5>
                    <small class="text-muted">Mostrando resultados según filtros seleccionados</small>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Radicado</th>
                                <th>Fecha</th>
                                <th>Aprendiz</th>
                                <th>Tipo</th>
                                <th>Asunto</th>
                                <th>Responsable</th>
                                <th>Estado</th>
                                <th class="text-center pe-4">Respuestas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pqrs as $pqr)
                                <tr>
                                    <td class="ps-4 font-monospace fw-bold small" style="color: #001A29;">
                                        {{ $pqr->radicado }}
                                    </td>
                                    <td class="small text-muted">
                                        {{ $pqr->fecha_radicacion ? $pqr->fecha_radicacion->format('d/m/Y') : '-' }}
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark small">{{ $pqr->aprendiz->nickname ?? 'Aprendiz' }}</div>
                                        <small class="text-muted" style="font-size: 0.75rem;">{{ $pqr->aprendiz->email ?? '' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill px-2 py-1 small" style="background-color: {{ $pqr->tipo_badge['bg'] }}; color: {{ $pqr->tipo_badge['text'] }};">
                                            {{ $pqr->tipo->nombre ?? 'PQRS' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark small text-truncate" style="max-width: 260px;" title="{{ $pqr->asunto }}">
                                            {{ $pqr->asunto }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($pqr->instructorDestino)
                                            <span class="badge bg-light text-dark border small">
                                                👨‍🏫 {{ $pqr->instructorDestino->nickname }}
                                            </span>
                                        @else
                                            <span class="badge bg-light text-secondary border small">
                                                🏛️ Administración
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill px-2 py-1 small" style="background-color: {{ $pqr->estado_badge['bg'] }}; color: {{ $pqr->estado_badge['text'] }};">
                                            {{ $pqr->estado }}
                                        </span>
                                    </td>
                                    <td class="text-center pe-4">
                                        @if($pqr->respuestas->count() > 0)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                                <i class="fas fa-check me-1"></i>{{ $pqr->respuestas->count() }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary rounded-pill">0</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 text-secondary opacity-50"></i>
                                        <p class="mb-0">No se encontraron solicitudes con los filtros aplicados.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- PESTAÑA 3: RESPUESTAS OFICIALES EMITIDAS   -->
        <!-- ========================================== -->
        <div class="tab-pane fade" id="pills-respuestas" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h5 class="fw-bold mb-0" style="color: #001A29;">
                        Bitácora de Respuestas Oficiales Notificadas
                    </h5>
                </div>
                <div class="p-4">
                    @php
                        $todasRespuestas = $pqrs->flatMap(function($p) {
                            return $p->respuestas->map(function($r) use ($p) {
                                $r->pqr_parent = $p;
                                return $r;
                            });
                        })->sortByDesc('fecha');
                    @endphp

                    @forelse($todasRespuestas as $resp)
                        <div class="p-3 mb-3 rounded-3 bg-light border border-start border-4 border-success">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                <div>
                                    <span class="badge bg-dark font-monospace me-2">{{ $resp->pqr_parent->radicado }}</span>
                                    <strong class="text-dark">{{ $resp->pqr_parent->asunto }}</strong>
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-calendar-alt me-1"></i>{{ $resp->fecha ? $resp->fecha->format('d/m/Y H:i') : '' }}
                                </small>
                            </div>
                            <div class="small text-muted mb-2">
                                Destinatario: <strong>{{ $resp->pqr_parent->aprendiz->nickname ?? 'Aprendiz' }}</strong> &bull; Emitido por: <strong class="text-success">{{ $resp->usuario->nickname ?? 'Administración' }}</strong>
                            </div>
                            <div class="p-3 bg-white rounded-2 border text-dark small" style="white-space: pre-wrap;">{{ $resp->respuesta }}</div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-comments fa-3x mb-3 text-secondary opacity-50"></i>
                            <p class="mb-0">No hay respuestas registradas para el conjunto de solicitudes actual.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Gráfica de Tipos
    const ctxTipo = document.getElementById('chartReporteTipo');
    if (ctxTipo) {
        new Chart(ctxTipo, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($reporteTiposLabels) !!},
                datasets: [{
                    data: {!! json_encode($reporteTiposCounts) !!},
                    backgroundColor: ['#0284C7', '#EF4444', '#F97316', '#8B5CF6', '#10B981'],
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
                        labels: { boxWidth: 10, font: { size: 10 } }
                    }
                }
            }
        });
    }

    // 2. Gráfica de Estados
    const ctxEstado = document.getElementById('chartReporteEstado');
    if (ctxEstado) {
        new Chart(ctxEstado, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($reporteEstadosLabels) !!},
                datasets: [{
                    data: {!! json_encode($reporteEstadosCounts) !!},
                    backgroundColor: ['#F59E0B', '#0284C7', '#10B981'],
                    borderWidth: 2,
                    borderColor: '#FFFFFF'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, font: { size: 10 } }
                    }
                }
            }
        });
    }

    // 3. Gráfica de Instructores / Responsables
    const ctxInst = document.getElementById('chartReporteInstructor');
    if (ctxInst) {
        new Chart(ctxInst, {
            type: 'bar',
            data: {
                labels: {!! json_encode($reporteInstructoresLabels) !!},
                datasets: [{
                    label: 'Solicitudes Asignadas',
                    data: {!! json_encode($reporteInstructoresCounts) !!},
                    backgroundColor: '#39A900',
                    borderRadius: 6
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
});
</script>
@endpush
