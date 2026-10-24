@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .report-card { background:#FFF; border:1px solid #E2E8F0; border-radius:16px; padding:1.4rem; height:100%; box-shadow:0 2px 10px rgba(0,26,41,.03); }
    .report-filter-bar { background:#FFF; border:1px solid #E2E8F0; border-radius:16px; padding:1rem 1.25rem; }
    .nav-pills-custom .nav-link { color:#64748B; font-weight:600; border-radius:9999px; padding:.5rem 1.25rem; font-size:.9rem; transition:all .2s; }
    .nav-pills-custom .nav-link.active { background-color:#0284C7; color:#FFF; box-shadow:0 2px 8px rgba(2,132,199,.25); }
    @media print {
        .no-print { display:none !important; }
        body { background:#FFF !important; font-size:10.5pt; }
        .tab-pane { display:block !important; opacity:1 !important; visibility:visible !important; }
        .table { font-size:9pt; }
        .card { border:1px solid #CBD5E1 !important; box-shadow:none !important; break-inside:avoid; }
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
                    <li class="breadcrumb-item active" style="color:#001A29;font-weight:600;">Asistencias</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color:#001A29;font-family:'Outfit',sans-serif;">Reporte de Asistencias</h2>
            <p class="text-muted small mb-0">Listado por ficha, actividad y momento. Asistencias, inasistencias y quién registró cada una.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('controlecp.administrador.reportes') }}" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm btn-sm"><i class="fas fa-arrow-left me-1"></i>Volver</a>
            <button onclick="window.print()" class="btn text-white rounded-pill px-4 shadow-sm btn-sm" style="background-color:#0284C7;border-color:#0284C7;">
                <i class="fas fa-file-pdf me-1"></i>Exportar PDF
            </button>
        </div>
    </div>

    {{-- Encabezado institucional para impresión --}}
    <div class="d-none d-print-block text-center mb-4 pb-3 border-bottom">
        <h4 class="fw-bold mb-1" style="color:#001A29;">SERVICIO NACIONAL DE APRENDIZAJE — SENA</h4>
        <h5 class="fw-semibold text-secondary mb-1">CENTRO DE FORMACIÓN AGROINDUSTRIAL LA ANGOSTURA · REGIONAL HUILA</h5>
        <h6 class="text-muted mb-2">Escuela Cultura de Paz · Reporte de Asistencias</h6>
        <div class="small text-muted">Fecha: {{ now()->format('d/m/Y H:i:s') }} · Generado por: {{ Auth::user()->nickname ?? Auth::user()->name }}</div>
    </div>

    @if($errors->has('fecha'))
        <div class="alert alert-warning rounded-3 mb-3 no-print">
            <i class="fas fa-triangle-exclamation me-2"></i>{{ $errors->first('fecha') }}
        </div>
    @endif

    {{-- Filtros --}}
    <div class="report-filter-bar mb-4 shadow-sm no-print">
        <form method="GET" action="{{ route('controlecp.administrador.reportes.asistencias') }}" class="row g-2 align-items-end">
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Ficha</label>
                <select name="ficha" class="form-select form-select-sm rounded-pill">
                    <option value="">Todas las fichas</option>
                    @foreach($fichas as $f)
                        <option value="{{ $f->code }}" {{ ($filtros['ficha'] ?? '') == $f->code ? 'selected' : '' }}>{{ $f->code }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Momento</label>
                <select name="momento_id" class="form-select form-select-sm rounded-pill">
                    <option value="">Todos</option>
                    @foreach($momentos as $m)
                        <option value="{{ $m->id }}" {{ ($filtros['momento_id'] ?? '') == $m->id ? 'selected' : '' }}>{{ $m->nombre }}</option>
                    @endforeach
                    <option value="1" {{ ($filtros['momento_id'] ?? '') == 1 ? 'selected' : '' }}>Momento 1</option>
                    <option value="2" {{ ($filtros['momento_id'] ?? '') == 2 ? 'selected' : '' }}>Momento 2</option>
                    <option value="3" {{ ($filtros['momento_id'] ?? '') == 3 ? 'selected' : '' }}>Momento 3</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Estado</label>
                <select name="estado" class="form-select form-select-sm rounded-pill">
                    <option value="">Todos</option>
                    <option value="ASISTIO" {{ ($filtros['estado'] ?? '') === 'ASISTIO' ? 'selected' : '' }}>Asistió</option>
                    <option value="FALTO"   {{ ($filtros['estado'] ?? '') === 'FALTO'   ? 'selected' : '' }}>Faltó</option>
                    <option value="EXCUSA"  {{ ($filtros['estado'] ?? '') === 'EXCUSA'  ? 'selected' : '' }}>Excusa</option>
                    <option value="RETARDO" {{ ($filtros['estado'] ?? '') === 'RETARDO' ? 'selected' : '' }}>Retardo</option>
                </select>
            </div>
            {{-- HU-011: Filtrar por responsable (instructor) --}}
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Instructor</label>
                <select name="instructor_id" class="form-select form-select-sm rounded-pill">
                    <option value="">Todos</option>
                    @foreach($instructores as $inst)
                        <option value="{{ $inst->id }}" {{ ($filtros['instructor_id'] ?? '') == $inst->id ? 'selected' : '' }}>
                            {{ $inst->person->first_name ?? $inst->nickname ?? $inst->name }}
                        </option>
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
                    <a href="{{ route('controlecp.administrador.reportes.asistencias') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3"><i class="fas fa-times me-1"></i>Limpiar</a>
                @endif
                <button type="submit" class="btn btn-sm text-white rounded-pill px-4 shadow-sm" style="background-color:#0284C7;border-color:#0284C7;">
                    <i class="fas fa-filter me-1"></i>Generar Reporte
                </button>
            </div>
        </form>
    </div>

    {{-- HU-012: Totales e indicadores --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="report-card text-center">
                <div class="small text-muted fw-semibold mb-1">Total registros</div>
                <div class="h2 fw-bold mb-0" style="color:#001A29;">{{ $total }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="report-card text-center">
                <div class="small text-muted fw-semibold mb-1">Asistió</div>
                <div class="h2 fw-bold mb-0 text-success">{{ $asistio }}</div>
                <div class="small text-success">{{ $pctAsistencia }}%</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="report-card text-center">
                <div class="small text-muted fw-semibold mb-1">Faltó</div>
                <div class="h2 fw-bold mb-0 text-danger">{{ $falto }}</div>
                <div class="small text-danger">{{ $pctFalta }}%</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="report-card text-center">
                <div class="small text-muted fw-semibold mb-1">Excusa / Retardo</div>
                <div class="h2 fw-bold mb-0 text-warning">{{ $excusa + $retardo }}</div>
            </div>
        </div>
    </div>

    {{-- HU-014: Manejar reporte sin datos --}}
    @if($total === 0)
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
            <i class="fas fa-inbox fa-3x mb-3 text-secondary opacity-50"></i>
            <h5 class="fw-bold text-dark mb-1">No se encontraron registros</h5>
            <p class="small mb-3">No hay información que coincida con los filtros seleccionados.</p>
            <a href="{{ route('controlecp.administrador.reportes.asistencias') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-4">Limpiar filtros</a>
        </div>
    @else

    {{-- Pestañas --}}
    <ul class="nav nav-pills nav-pills-custom gap-2 mb-4 no-print" id="tabsAsistencia" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-listado" type="button"><i class="fas fa-table-list me-1"></i>Listado ({{ $total }})</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-ficha" type="button"><i class="fas fa-id-card me-1"></i>Por Ficha</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-graficas" type="button"><i class="fas fa-chart-pie me-1"></i>Gráficas</button></li>
    </ul>

    <div class="tab-content">

        {{-- LISTADO SIN GRÁFICAS (HU-002: el sistema muestra el listado en tabla, sin gráficas) --}}
        <div class="tab-pane fade show active" id="tab-listado">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between">
                    <h5 class="fw-bold mb-0" style="color:#001A29;">Registros de asistencia</h5>
                    <small class="text-muted">{{ $total }} registros · Filtros: {{ array_filter($filtros) ? 'Aplicados' : 'Ninguno' }}</small>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Ficha</th>
                                <th>Fecha</th>
                                <th>Actividad</th>
                                <th>Momento</th>
                                <th>Aprendiz</th>
                                <th>Documento</th>
                                <th>Estado</th>
                                <th>Instructor (Registró)</th>
                                <th>Observación</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($registros->take(500) as $reg)
                                <tr>
                                    <td class="ps-4 font-monospace fw-semibold" style="color:#001A29;">{{ $reg->course?->code ?? 'N/A' }}</td>
                                    <td class="text-nowrap text-muted">{{ $reg->fecha?->format('d/m/Y') ?? '-' }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark text-truncate" style="max-width:200px;" title="{{ $reg->actividad_nombre }}">
                                            Act.{{ $reg->actividad_id }} — {{ $reg->actividad_nombre }}
                                        </div>
                                    </td>
                                    <td>
                                        @php $mc = ['#39A900','#0070B8','#6A1B9A'][$reg->momento_id - 1] ?? '#6c757d'; @endphp
                                        <span class="badge rounded-pill px-2" style="background:{{ $mc }}20;color:{{ $mc }};border:1px solid {{ $mc }}40;">M{{ $reg->momento_id }}</span>
                                    </td>
                                    <td class="fw-semibold text-dark">{{ $reg->full_name }}</td>
                                    <td class="text-muted font-monospace">{{ $reg->document_number }}</td>
                                    <td>
                                        <span class="badge rounded-pill px-2 py-1 bg-{{ $reg->estado_badge }}-subtle text-{{ $reg->estado_badge }}-emphasis border border-{{ $reg->estado_badge }}-subtle">
                                            <i class="fas {{ $reg->estado_icon }} me-1"></i>{{ $reg->estado_label }}
                                        </span>
                                    </td>
                                    <td class="text-muted">
                                        {{ $reg->instructor?->person?->first_name
                                            ? $reg->instructor->person->first_name.' '.$reg->instructor->person->first_last_name
                                            : ($reg->instructor?->nickname ?? 'N/A') }}
                                    </td>
                                    <td class="text-muted text-truncate" style="max-width:180px;">{{ $reg->observacion ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($total > 500)
                    <div class="p-3 bg-light text-center small text-muted border-top">
                        Mostrando los primeros 500 de {{ $total }} registros. Aplica filtros para reducir el conjunto.
                    </div>
                @endif
            </div>
        </div>

        {{-- POR FICHA --}}
        <div class="tab-pane fade" id="tab-ficha">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Ficha</th>
                                <th>Programa</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Asistió</th>
                                <th class="text-center">Faltó</th>
                                <th class="text-center">Excusa</th>
                                <th class="text-center">Retardo</th>
                                <th class="text-center pe-4">% Asistencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($porFicha as $row)
                                <tr>
                                    <td class="ps-4 font-monospace fw-bold" style="color:#001A29;">{{ $row['ficha'] }}</td>
                                    <td class="text-muted text-truncate" style="max-width:220px;">{{ $row['programa'] }}</td>
                                    <td class="text-center fw-bold">{{ $row['total'] }}</td>
                                    <td class="text-center text-success fw-semibold">{{ $row['asistio'] }}</td>
                                    <td class="text-center text-danger fw-semibold">{{ $row['falto'] }}</td>
                                    <td class="text-center text-info fw-semibold">{{ $row['excusa'] }}</td>
                                    <td class="text-center text-warning fw-semibold">{{ $row['retardo'] }}</td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height:8px;">
                                                <div class="progress-bar {{ $row['pct'] >= 80 ? 'bg-success' : ($row['pct'] >= 60 ? 'bg-warning' : 'bg-danger') }}"
                                                     style="width:{{ $row['pct'] }}%;"></div>
                                            </div>
                                            <span class="fw-bold small" style="min-width:36px;">{{ $row['pct'] }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center py-4 text-muted">Sin datos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- GRÁFICAS --}}
        <div class="tab-pane fade" id="tab-graficas">
            <div class="row g-4">
                <div class="col-12 col-lg-4">
                    <div class="report-card">
                        <h5 class="fw-bold mb-3" style="color:#001A29;font-size:1rem;">Distribución por Estado</h5>
                        <div style="height:220px;"><canvas id="chartEstados"></canvas></div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="report-card">
                        <h5 class="fw-bold mb-3" style="color:#001A29;font-size:1rem;">% Asistencia por Momento</h5>
                        <div style="height:220px;"><canvas id="chartMomentos"></canvas></div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="report-card">
                        <h5 class="fw-bold mb-3" style="color:#001A29;font-size:1rem;">Por Instructor (top 10)</h5>
                        <div style="height:220px;"><canvas id="chartInstructor"></canvas></div>
                    </div>
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
    const ctx1 = document.getElementById('chartEstados');
    if (ctx1) {
        new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($chartEstadosLabels) !!},
                datasets: [{ data: {!! json_encode($chartEstadosCounts) !!}, backgroundColor: ['#10B981','#EF4444','#38BDF8','#F59E0B'], borderWidth: 2, borderColor: '#FFF' }]
            },
            options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10, font:{size:10} } } } }
        });
    }
    const ctx2 = document.getElementById('chartMomentos');
    if (ctx2) {
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartMomentosLabels) !!},
                datasets: [{ label:'% Asistencia', data: {!! json_encode($chartMomentosPct) !!}, backgroundColor: ['#39A900','#0070B8','#6A1B9A'], borderRadius:8 }]
            },
            options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false} }, scales:{ y:{ beginAtZero:true, max:100, ticks:{ callback: v => v+'%' } } } }
        });
    }
    const ctx3 = document.getElementById('chartInstructor');
    if (ctx3) {
        new Chart(ctx3, {
            type: 'bar',
            data: {
                labels: {!! json_encode($porInstructor->pluck('nombre')->toArray()) !!},
                datasets: [{ label:'% Asistencia', data: {!! json_encode($porInstructor->pluck('pct')->toArray()) !!}, backgroundColor:'#0284C7', borderRadius:6 }]
            },
            options: { indexAxis:'y', responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false} }, scales:{ x:{ beginAtZero:true, max:100 } } }
        });
    }
});
</script>
@endpush
