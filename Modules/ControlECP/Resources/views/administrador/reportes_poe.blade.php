@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .report-card { background:#FFF; border:1px solid #E2E8F0; border-radius:16px; padding:1.4rem; height:100%; box-shadow:0 2px 10px rgba(0,26,41,.03); }
    .report-filter-bar { background:#FFF; border:1px solid #E2E8F0; border-radius:16px; padding:1rem 1.25rem; }
    .nav-pills-custom .nav-link { color:#64748B; font-weight:600; border-radius:9999px; padding:.5rem 1.25rem; font-size:.9rem; }
    .nav-pills-custom .nav-link.active { background-color:#059669; color:#FFF; box-shadow:0 2px 8px rgba(5,150,105,.25); }
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
                    <li class="breadcrumb-item active" style="color:#001A29;font-weight:600;">POE</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color:#001A29;font-family:'Outfit',sans-serif;">Reporte de POE</h2>
            <p class="text-muted small mb-0">Plan Operativo de Ejecución. Por estado y líder. Versiones, quién subió cada cambio y cumplimiento de subidas programadas.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('controlecp.administrador.reportes') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm"><i class="fas fa-arrow-left me-1"></i>Volver</a>
            <button onclick="window.print()" class="btn text-white rounded-pill px-4 btn-sm" style="background-color:#059669;border-color:#059669;"><i class="fas fa-file-pdf me-1"></i>Exportar PDF</button>
        </div>
    </div>

    {{-- Encabezado institucional PDF --}}
    <div class="d-none d-print-block text-center mb-4 pb-3 border-bottom">
        <h4 class="fw-bold mb-1" style="color:#001A29;">SERVICIO NACIONAL DE APRENDIZAJE — SENA</h4>
        <h5 class="fw-semibold text-secondary mb-1">CENTRO DE FORMACIÓN AGROINDUSTRIAL LA ANGOSTURA · REGIONAL HUILA</h5>
        <h6 class="text-muted mb-2">Escuela Cultura de Paz · Reporte del Plan Operativo de Ejecución (POE)</h6>
        <div class="small text-muted">Fecha: {{ now()->format('d/m/Y H:i:s') }} · Generado por: {{ Auth::user()->nickname ?? Auth::user()->name }}</div>
    </div>

    {{-- Nota API --}}
    <div class="alert d-flex align-items-start gap-2 rounded-3 mb-4 no-print" style="background:#ECFDF5;border:1px solid #A7F3D0;">
        <i class="fas fa-plug-circle-bolt mt-1" style="color:#059669;"></i>
        <div class="small"><strong style="color:#065F46;">Integración SICEFA pendiente:</strong> Los datos del POE provienen del dataset estructurado local con la misma estructura de la API REST Central del ERP SENA Empresa. Al conectar el endpoint <code>/poe</code>, este reporte se actualizará automáticamente con los datos reales.</div>
    </div>

    @if($errors->has('fecha'))
        <div class="alert alert-warning rounded-3 mb-3 no-print"><i class="fas fa-triangle-exclamation me-2"></i>{{ $errors->first('fecha') }}</div>
    @endif

    {{-- Filtros --}}
    <div class="report-filter-bar mb-4 shadow-sm no-print">
        <form method="GET" action="{{ route('controlecp.administrador.reportes.poe') }}" class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label small text-muted mb-1 fw-semibold">Ficha</label>
                <select name="ficha" class="form-select form-select-sm rounded-pill">
                    <option value="">Todas las fichas</option>
                    @foreach($fichas as $f)
                        <option value="{{ $f->code }}" {{ ($filtros['ficha'] ?? '') == $f->code ? 'selected' : '' }}>{{ $f->code }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Estado</label>
                <select name="estado" class="form-select form-select-sm rounded-pill">
                    <option value="">Todos</option>
                    <option value="Cumplida"  {{ ($filtros['estado'] ?? '') === 'Cumplida'  ? 'selected' : '' }}>Cumplida</option>
                    <option value="Pendiente" {{ ($filtros['estado'] ?? '') === 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="Atrasada"  {{ ($filtros['estado'] ?? '') === 'Atrasada'  ? 'selected' : '' }}>Atrasada</option>
                </select>
            </div>
            {{-- HU-011: Filtrar por líder/responsable --}}
            <div class="col-6 col-md-3">
                <label class="form-label small text-muted mb-1 fw-semibold">Líder responsable</label>
                <select name="lider_id" class="form-select form-select-sm rounded-pill">
                    <option value="">Todos</option>
                    @foreach($lideres as $l)
                        <option value="{{ $l->id }}" {{ ($filtros['lider_id'] ?? '') == $l->id ? 'selected' : '' }}>{{ $l->nickname ?? $l->name }}</option>
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
                    <a href="{{ route('controlecp.administrador.reportes.poe') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3"><i class="fas fa-times me-1"></i>Limpiar</a>
                @endif
                <button type="submit" class="btn btn-sm text-white rounded-pill px-4 shadow-sm" style="background-color:#059669;border-color:#059669;">
                    <i class="fas fa-filter me-1"></i>Generar
                </button>
            </div>
        </form>
    </div>

    {{-- HU-012: Indicadores --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-2">
            <div class="report-card text-center">
                <div class="small text-muted fw-semibold mb-1">Total subidas</div>
                <div class="h2 fw-bold mb-0" style="color:#001A29;">{{ $total }}</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="report-card text-center">
                <div class="small text-muted fw-semibold mb-1">Cumplidas</div>
                <div class="h2 fw-bold mb-0 text-success">{{ $cumplidas }}</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="report-card text-center">
                <div class="small text-muted fw-semibold mb-1">Pendientes</div>
                <div class="h2 fw-bold mb-0 text-warning">{{ $pendientes }}</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="report-card text-center">
                <div class="small text-muted fw-semibold mb-1">Atrasadas</div>
                <div class="h2 fw-bold mb-0 text-danger">{{ $atrasadas }}</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="report-card text-center">
                <div class="small text-muted fw-semibold mb-1">% Cumplimiento</div>
                <div class="h2 fw-bold mb-0 {{ $pctCumplimiento >= 80 ? 'text-success' : ($pctCumplimiento >= 50 ? 'text-warning' : 'text-danger') }}">
                    {{ $pctCumplimiento }}%
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="report-card text-center">
                <div class="small text-muted fw-semibold mb-1">Fichas con POE</div>
                <div class="h2 fw-bold mb-0" style="color:#059669;">{{ $porFicha->count() }}</div>
            </div>
        </div>
    </div>

    {{-- HU-014: Sin datos --}}
    @if($total === 0)
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
            <i class="fas fa-inbox fa-3x mb-3 text-secondary opacity-50"></i>
            <h5 class="fw-bold text-dark mb-1">No se encontraron registros</h5>
            <p class="small mb-3">No hay datos del POE que coincidan con los filtros seleccionados.</p>
            <a href="{{ route('controlecp.administrador.reportes.poe') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-4">Limpiar filtros</a>
        </div>
    @else

    {{-- Pestañas --}}
    <ul class="nav nav-pills nav-pills-custom gap-2 mb-4 no-print" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-gf-poe" type="button"><i class="fas fa-chart-pie me-1"></i>Gráficas</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-fichas-poe" type="button"><i class="fas fa-id-card me-1"></i>Versiones por Ficha ({{ $porFicha->count() }})</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-list-poe" type="button"><i class="fas fa-table-list me-1"></i>Bitácora completa ({{ $total }})</button></li>
    </ul>

    <div class="tab-content">

        {{-- GRÁFICAS --}}
        <div class="tab-pane fade show active" id="tab-gf-poe">
            <div class="row g-4">
                <div class="col-12 col-lg-5">
                    <div class="report-card">
                        <h5 class="fw-bold mb-3" style="color:#001A29;font-size:1rem;">Cumplimiento general</h5>
                        <div class="position-relative" style="height:240px;">
                            <canvas id="chartEstadoPoe"></canvas>
                            <div class="chart-center-label">
                                <div class="text-muted small fw-semibold" style="font-size:.75rem;">Cumplimiento</div>
                                <div class="h3 fw-bold mb-0" style="color:#059669;">{{ $pctCumplimiento }}%</div>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top small">
                            <div class="d-flex justify-content-between py-1"><span><i class="fas fa-circle me-1 text-success"></i>Cumplidas</span><span class="fw-bold">{{ $cumplidas }}</span></div>
                            <div class="d-flex justify-content-between py-1"><span><i class="fas fa-circle me-1 text-warning"></i>Pendientes</span><span class="fw-bold">{{ $pendientes }}</span></div>
                            <div class="d-flex justify-content-between py-1"><span><i class="fas fa-circle me-1 text-danger"></i>Atrasadas</span><span class="fw-bold">{{ $atrasadas }}</span></div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-7">
                    <div class="report-card">
                        <h5 class="fw-bold mb-3" style="color:#001A29;font-size:1rem;">Subidas por ficha (Top 8)</h5>
                        <div style="height:240px;"><canvas id="chartFichasPoe"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- VERSIONES POR FICHA --}}
        <div class="tab-pane fade" id="tab-fichas-poe">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Ficha</th>
                                <th>Programa</th>
                                <th class="text-center">Total subidas</th>
                                <th class="text-center">Última versión</th>
                                <th>Líder</th>
                                <th class="text-center">Cumplidas</th>
                                <th class="text-center">Pendientes</th>
                                <th class="text-center pe-4">Atrasadas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($porFicha as $row)
                                <tr>
                                    <td class="ps-4 font-monospace fw-bold" style="color:#001A29;">{{ $row['ficha'] }}</td>
                                    <td class="text-muted text-truncate" style="max-width:180px;">{{ $row['programa'] }}</td>
                                    <td class="text-center fw-bold">{{ $row['total_subidas'] }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border font-monospace">v{{ $row['ultima_version'] }}</span>
                                    </td>
                                    <td class="text-muted">{{ $row['lider'] }}</td>
                                    <td class="text-center text-success fw-semibold">{{ $row['cumplidas'] }}</td>
                                    <td class="text-center text-warning fw-semibold">{{ $row['pendientes'] }}</td>
                                    <td class="text-center pe-4 text-danger fw-semibold">{{ $row['atrasadas'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center py-4 text-muted">Sin resultados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- BITÁCORA COMPLETA --}}
        <div class="tab-pane fade" id="tab-list-poe">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">ID SICEFA</th>
                                <th>Ficha</th>
                                <th class="text-center">Versión</th>
                                <th>Líder</th>
                                <th>Fecha subida</th>
                                <th>Descripción del cambio</th>
                                <th class="text-center">% Cumplimiento</th>
                                <th class="pe-4 text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registrosPoe as $reg)
                                @php
                                    $eColor = match($reg['estado']) {
                                        'Cumplida'  => ['#DCFCE7','#15803D'],
                                        'Pendiente' => ['#FEF3C7','#92400E'],
                                        'Atrasada'  => ['#FEE2E2','#B91C1C'],
                                        default     => ['#F1F5F9','#475569'],
                                    };
                                @endphp
                                <tr>
                                    <td class="ps-4 font-monospace text-muted small">{{ $reg['sicefa_id'] }}</td>
                                    <td class="fw-semibold" style="color:#001A29;">{{ $reg['ficha_code'] }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border font-monospace">v{{ $reg['version'] }}</span>
                                    </td>
                                    <td class="text-muted">{{ $reg['lider'] }}</td>
                                    <td class="text-nowrap text-muted">{{ \Carbon\Carbon::parse($reg['fecha_subida'])->format('d/m/Y') }}</td>
                                    <td class="text-muted text-truncate" style="max-width:280px;" title="{{ $reg['descripcion'] }}">{{ $reg['descripcion'] }}</td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center gap-1 justify-content-center">
                                            <div class="progress flex-grow-1" style="height:6px;max-width:60px;">
                                                <div class="progress-bar {{ $reg['pct_cumplimiento'] >= 80 ? 'bg-success' : ($reg['pct_cumplimiento'] >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                                     style="width:{{ $reg['pct_cumplimiento'] }}%;"></div>
                                            </div>
                                            <span class="fw-bold small" style="min-width:32px;">{{ $reg['pct_cumplimiento'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="pe-4 text-center">
                                        <span class="badge rounded-pill px-2 py-1 small" style="background:{{ $eColor[0] }};color:{{ $eColor[1] }};">{{ $reg['estado'] }}</span>
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
    const ctx1 = document.getElementById('chartEstadoPoe');
    if (ctx1) new Chart(ctx1, { type:'doughnut', data:{ labels:{!! json_encode($chartEstadosLabels) !!}, datasets:[{ data:{!! json_encode($chartEstadosCounts) !!}, backgroundColor:['#10B981','#F59E0B','#EF4444'], borderWidth:2, borderColor:'#FFF' }] }, options:{ responsive:true, maintainAspectRatio:false, cutout:'70%', plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10, font:{size:10} } } } } });
    const ctx2 = document.getElementById('chartFichasPoe');
    if (ctx2) new Chart(ctx2, { type:'bar', data:{ labels:{!! json_encode($chartFichasLabels) !!}, datasets:[{ label:'Subidas', data:{!! json_encode($chartFichasCounts) !!}, backgroundColor:'#059669', borderRadius:8 }] }, options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false} }, scales:{ y:{ beginAtZero:true, ticks:{ precision:0 } } } } });
});
</script>
@endpush
