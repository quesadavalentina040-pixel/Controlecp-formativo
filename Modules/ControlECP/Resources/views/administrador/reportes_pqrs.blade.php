@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .report-card { background:#FFF; border:1px solid #E2E8F0; border-radius:16px; padding:1.4rem; height:100%; box-shadow:0 2px 10px rgba(0,26,41,.03); }
    .report-filter-bar { background:#FFF; border:1px solid #E2E8F0; border-radius:16px; padding:1rem 1.25rem; }
    .nav-pills-custom .nav-link { color:#64748B; font-weight:600; border-radius:9999px; padding:.5rem 1.25rem; font-size:.9rem; }
    .nav-pills-custom .nav-link.active { background-color:#CA8A04; color:#FFF; }
    .chart-center-label { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); text-align:center; pointer-events:none; }
    @media print {
        .no-print { display:none !important; }
        body { background:#FFF !important; font-size:10.5pt; }
        .tab-pane { display:block !important; opacity:1 !important; visibility:visible !important; }
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2 printable-area">

    {{-- Cabecera --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom no-print">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item"><a href="{{ route('controlecp.administrador.inicio') }}" class="text-decoration-none" style="color:#39A900;"><i class="fas fa-house me-1"></i>Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('controlecp.administrador.reportes') }}" class="text-decoration-none" style="color:#39A900;">Reportes</a></li>
                    <li class="breadcrumb-item active" style="color:#001A29;font-weight:600;">PQRS</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color:#001A29;font-family:'Outfit',sans-serif;">Reporte de PQRS</h2>
            <p class="text-muted small mb-0">Por tipo, estado, instructor y fechas. Listado consolidado con totales.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('controlecp.administrador.reportes') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm"><i class="fas fa-arrow-left me-1"></i>Volver</a>
            <button onclick="window.print()" class="btn text-white rounded-pill px-4 btn-sm" style="background-color:#CA8A04;border-color:#CA8A04;"><i class="fas fa-file-pdf me-1"></i>Exportar PDF</button>
        </div>
    </div>

    {{-- Encabezado institucional PDF --}}
    <div class="d-none d-print-block text-center mb-4 pb-3 border-bottom">
        <h4 class="fw-bold mb-1" style="color:#001A29;">SERVICIO NACIONAL DE APRENDIZAJE — SENA</h4>
        <h5 class="fw-semibold text-secondary mb-1">CENTRO DE FORMACIÓN AGROINDUSTRIAL LA ANGOSTURA · REGIONAL HUILA</h5>
        <h6 class="text-muted mb-2">Escuela Cultura de Paz · Informe Estadístico de PQRS</h6>
        <div class="small text-muted">Fecha: {{ now()->format('d/m/Y H:i:s') }} · Generado por: {{ Auth::user()->nickname ?? Auth::user()->name }}</div>
    </div>

    @if($errors->has('fecha'))
        <div class="alert alert-warning rounded-3 mb-3 no-print"><i class="fas fa-triangle-exclamation me-2"></i>{{ $errors->first('fecha') }}</div>
    @endif

    {{-- Filtros --}}
    <div class="report-filter-bar mb-4 shadow-sm no-print">
        <form method="GET" action="{{ route('controlecp.administrador.reportes.pqrs') }}" class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label small text-muted mb-1 fw-semibold">Tipo de Solicitud</label>
                <select name="id_tipo_pqrs" class="form-select form-select-sm rounded-pill">
                    <option value="">Todos</option>
                    @foreach($tipos as $t)
                        <option value="{{ $t->id_tipo_pqrs }}" {{ ($filtros['id_tipo_pqrs'] ?? '') == $t->id_tipo_pqrs ? 'selected' : '' }}>{{ $t->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Estado</label>
                <select name="estado" class="form-select form-select-sm rounded-pill">
                    <option value="">Todos</option>
                    <option value="Pendiente"  {{ ($filtros['estado'] ?? '') === 'Pendiente'  ? 'selected' : '' }}>Pendiente</option>
                    <option value="En trámite" {{ ($filtros['estado'] ?? '') === 'En trámite' ? 'selected' : '' }}>En trámite</option>
                    <option value="Resuelto"   {{ ($filtros['estado'] ?? '') === 'Resuelto'   ? 'selected' : '' }}>Resuelto</option>
                    <option value="Cerrado"    {{ ($filtros['estado'] ?? '') === 'Cerrado'    ? 'selected' : '' }}>Cerrado</option>
                </select>
            </div>
            {{-- HU-011: Filtrar por responsable --}}
            <div class="col-6 col-md-3">
                <label class="form-label small text-muted mb-1 fw-semibold">Destino / Responsable</label>
                <select name="id_instructor" class="form-select form-select-sm rounded-pill">
                    <option value="">Todos</option>
                    <option value="admin" {{ ($filtros['id_instructor'] ?? '') === 'admin' ? 'selected' : '' }}>🏛️ Administración</option>
                    @foreach($instructores as $inst)
                        <option value="{{ $inst->id }}" {{ ($filtros['id_instructor'] ?? '') == $inst->id ? 'selected' : '' }}>👨‍🏫 {{ $inst->nickname ?? $inst->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-4 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Desde</label>
                <input type="date" name="fecha_desde" value="{{ $filtros['fecha_desde'] ?? '' }}" class="form-control form-control-sm rounded-pill">
            </div>
            <div class="col-4 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ $filtros['fecha_hasta'] ?? '' }}" class="form-control form-control-sm rounded-pill">
            </div>
            <div class="col-12 d-flex gap-2 justify-content-end mt-1">
                @if(array_filter($filtros))
                    <a href="{{ route('controlecp.administrador.reportes.pqrs') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3"><i class="fas fa-times me-1"></i>Limpiar</a>
                @endif
                <button type="submit" class="btn btn-sm text-white rounded-pill px-4 shadow-sm" style="background-color:#CA8A04;border-color:#CA8A04;">
                    <i class="fas fa-filter me-1"></i>Generar
                </button>
            </div>
        </form>
    </div>

    {{-- HU-012: Indicadores --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="report-card text-center">
                <div class="small text-muted fw-semibold mb-1">Total radicadas</div>
                <div class="h2 fw-bold mb-0" style="color:#001A29;">{{ $total }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="report-card text-center">
                <div class="small text-muted fw-semibold mb-1">Pendientes</div>
                <div class="h2 fw-bold mb-0 text-warning">{{ $pendientes }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="report-card text-center">
                <div class="small text-muted fw-semibold mb-1">En trámite</div>
                <div class="h2 fw-bold mb-0 text-primary">{{ $enTramite }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="report-card text-center">
                <div class="small text-muted fw-semibold mb-1">Resueltas</div>
                <div class="h2 fw-bold mb-0 text-success">{{ $resueltas }}</div>
                <div class="small text-success">{{ $tasaResolucion }}%</div>
            </div>
        </div>
    </div>

    {{-- HU-014: Sin datos --}}
    @if($total === 0)
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
            <i class="fas fa-inbox fa-3x mb-3 text-secondary opacity-50"></i>
            <h5 class="fw-bold text-dark mb-1">No se encontraron registros</h5>
            <p class="small mb-0">No hay PQRS que coincidan con los filtros seleccionados.</p>
        </div>
    @else

    {{-- Pestañas --}}
    <ul class="nav nav-pills nav-pills-custom gap-2 mb-4 no-print" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-gf-pq" type="button"><i class="fas fa-chart-pie me-1"></i>Resumen Gráfico</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-list-pq" type="button"><i class="fas fa-table-list me-1"></i>Listado ({{ $total }})</button></li>
    </ul>

    <div class="tab-content">

        {{-- GRÁFICAS --}}
        <div class="tab-pane fade show active" id="tab-gf-pq">
            <div class="row g-4">
                <div class="col-12 col-lg-4">
                    <div class="report-card">
                        <h5 class="fw-bold mb-3" style="color:#001A29;font-size:1rem;">Por Tipo de Solicitud</h5>
                        <div style="height:220px;"><canvas id="chartTipoPq"></canvas></div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="report-card">
                        <h5 class="fw-bold mb-3" style="color:#001A29;font-size:1rem;">Estado de Gestión</h5>
                        <div class="position-relative" style="height:220px;">
                            <canvas id="chartEstadoPq"></canvas>
                            <div class="chart-center-label">
                                <div class="text-muted small fw-semibold" style="font-size:.75rem;">Total</div>
                                <div class="h3 fw-bold mb-0" style="color:#001A29;">{{ $total }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="report-card">
                        <h5 class="fw-bold mb-3" style="color:#001A29;font-size:1rem;">Asignación por Responsable</h5>
                        <div style="height:220px;"><canvas id="chartInstPq"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- LISTADO --}}
        <div class="tab-pane fade" id="tab-list-pq">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
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
                                    <td class="ps-4 font-monospace fw-bold small" style="color:#001A29;">{{ $pqr->radicado }}</td>
                                    <td class="text-muted text-nowrap">{{ $pqr->fecha_radicacion?->format('d/m/Y') ?? '-' }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark small">{{ $pqr->aprendiz?->nickname ?? $pqr->aprendiz?->name ?? 'Aprendiz' }}</div>
                                        <small class="text-muted" style="font-size:.75rem;">{{ $pqr->aprendiz?->email ?? '' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill px-2 py-1 small" style="background:{{ $pqr->tipo_badge['bg'] }};color:{{ $pqr->tipo_badge['text'] }};">{{ $pqr->tipo?->nombre ?? 'PQRS' }}</span>
                                    </td>
                                    <td class="text-truncate fw-semibold text-dark small" style="max-width:220px;" title="{{ $pqr->asunto }}">{{ $pqr->asunto }}</td>
                                    <td>
                                        @if($pqr->instructorDestino)
                                            <span class="badge bg-light text-dark border small">👨‍🏫 {{ $pqr->instructorDestino?->nickname ?? $pqr->instructorDestino?->name }}</span>
                                        @else
                                            <span class="badge bg-light text-secondary border small">🏛️ Administración</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill px-2 py-1 small" style="background:{{ $pqr->estado_badge['bg'] }};color:{{ $pqr->estado_badge['text'] }};">{{ $pqr->estado }}</span>
                                    </td>
                                    <td class="text-center pe-4">
                                        @if($pqr->respuestas->count() > 0)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill"><i class="fas fa-check me-1"></i>{{ $pqr->respuestas->count() }}</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary rounded-pill">0</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center py-4 text-muted">Sin resultados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx1 = document.getElementById('chartTipoPq');
    if (ctx1) new Chart(ctx1, { type:'doughnut', data:{ labels:{!! json_encode($reporteTiposLabels) !!}, datasets:[{ data:{!! json_encode($reporteTiposCounts) !!}, backgroundColor:['#0284C7','#EF4444','#F97316','#8B5CF6','#10B981'], borderWidth:2, borderColor:'#FFF' }] }, options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10, font:{size:10} } } } } });
    const ctx2 = document.getElementById('chartEstadoPq');
    if (ctx2) new Chart(ctx2, { type:'doughnut', data:{ labels:{!! json_encode($reporteEstadosLabels) !!}, datasets:[{ data:{!! json_encode($reporteEstadosCounts) !!}, backgroundColor:['#F59E0B','#0284C7','#10B981'], borderWidth:2, borderColor:'#FFF' }] }, options:{ responsive:true, maintainAspectRatio:false, cutout:'70%', plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10, font:{size:10} } } } } });
    const ctx3 = document.getElementById('chartInstPq');
    if (ctx3) new Chart(ctx3, { type:'bar', data:{ labels:{!! json_encode($reporteInstructoresLabels) !!}, datasets:[{ label:'Solicitudes', data:{!! json_encode($reporteInstructoresCounts) !!}, backgroundColor:'#CA8A04', borderRadius:6 }] }, options:{ indexAxis:'y', responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false} }, scales:{ x:{ beginAtZero:true, ticks:{ precision:0 } } } } });
});
</script>
@endpush
