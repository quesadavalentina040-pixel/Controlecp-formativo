@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .report-card { background:#FFF; border:1px solid #E2E8F0; border-radius:16px; padding:1.4rem; height:100%; box-shadow:0 2px 10px rgba(0,26,41,.03); }
    .report-filter-bar { background:#FFF; border:1px solid #E2E8F0; border-radius:16px; padding:1rem 1.25rem; }
    .nav-pills-custom .nav-link { color:#64748B; font-weight:600; border-radius:9999px; padding:.5rem 1.25rem; font-size:.9rem; }
    .nav-pills-custom .nav-link.active { background-color:#D97706; color:#FFF; }
    @media print {
        .no-print { display:none !important; }
        body { background:#FFF !important; font-size:10.5pt; }
        .tab-pane { display:block !important; opacity:1 !important; visibility:visible !important; }
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">

    {{-- Cabecera --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom no-print">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item"><a href="{{ route('controlecp.administrador.inicio') }}" class="text-decoration-none" style="color:#39A900;"><i class="fas fa-house me-1"></i>Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('controlecp.administrador.reportes') }}" class="text-decoration-none" style="color:#39A900;">Reportes</a></li>
                    <li class="breadcrumb-item active" style="color:#001A29;font-weight:600;">Asesorías</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color:#001A29;font-family:'Outfit',sans-serif;">Reporte de Asesorías</h2>
            <p class="text-muted small mb-0">Por trimestre, categoría, instructor y estado. Totales y gráficas de atención. Datos listos para API REST SICEFA.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('controlecp.administrador.reportes') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm"><i class="fas fa-arrow-left me-1"></i>Volver</a>
            <button onclick="window.print()" class="btn text-white rounded-pill px-4 btn-sm" style="background-color:#D97706;border-color:#D97706;"><i class="fas fa-file-pdf me-1"></i>Exportar PDF</button>
        </div>
    </div>

    {{-- Encabezado institucional PDF --}}
    <div class="d-none d-print-block text-center mb-4 pb-3 border-bottom">
        <h4 class="fw-bold mb-1" style="color:#001A29;">SERVICIO NACIONAL DE APRENDIZAJE — SENA</h4>
        <h5 class="fw-semibold text-secondary mb-1">CENTRO DE FORMACIÓN AGROINDUSTRIAL LA ANGOSTURA · REGIONAL HUILA</h5>
        <h6 class="text-muted mb-2">Escuela Cultura de Paz · Reporte de Asesorías</h6>
        <div class="small text-muted">Fecha: {{ now()->format('d/m/Y H:i:s') }} · Generado por: {{ Auth::user()->nickname ?? Auth::user()->name }}</div>
    </div>

    {{-- Nota API --}}
    <div class="alert d-flex align-items-start gap-2 rounded-3 mb-4 no-print" style="background:#FFFBEB;border:1px solid #FDE68A;">
        <i class="fas fa-plug-circle-bolt mt-1" style="color:#D97706;"></i>
        <div class="small"><strong style="color:#92400E;">Integración SICEFA pendiente:</strong> Los datos mostrados provienen del dataset estructurado local con la misma estructura de la API REST Central del ERP SENA Empresa. Al conectar el endpoint de asesorías, este reporte se actualizará automáticamente.</div>
    </div>

    @if($errors->has('fecha'))
        <div class="alert alert-warning rounded-3 mb-3 no-print"><i class="fas fa-triangle-exclamation me-2"></i>{{ $errors->first('fecha') }}</div>
    @endif

    {{-- Filtros --}}
    <div class="report-filter-bar mb-4 shadow-sm no-print">
        <form method="GET" action="{{ route('controlecp.administrador.reportes.asesorias') }}" class="row g-2 align-items-end">
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Ficha</label>
                <select name="ficha" class="form-select form-select-sm rounded-pill">
                    <option value="">Todas</option>
                    @foreach($fichas as $f)
                        <option value="{{ $f->code }}" {{ ($filtros['ficha'] ?? '') == $f->code ? 'selected' : '' }}>{{ $f->code }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Estado</label>
                <select name="estado" class="form-select form-select-sm rounded-pill">
                    <option value="">Todos</option>
                    <option value="Solicitada" {{ ($filtros['estado'] ?? '') === 'Solicitada' ? 'selected' : '' }}>Solicitada</option>
                    <option value="Programada" {{ ($filtros['estado'] ?? '') === 'Programada' ? 'selected' : '' }}>Programada</option>
                    <option value="Realizada"  {{ ($filtros['estado'] ?? '') === 'Realizada'  ? 'selected' : '' }}>Realizada</option>
                    <option value="Cancelada"  {{ ($filtros['estado'] ?? '') === 'Cancelada'  ? 'selected' : '' }}>Cancelada</option>
                </select>
            </div>
            {{-- HU-011: Filtrar por instructor --}}
            <div class="col-6 col-md-3">
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
                    <a href="{{ route('controlecp.administrador.reportes.asesorias') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3"><i class="fas fa-times me-1"></i>Limpiar</a>
                @endif
                <button type="submit" class="btn btn-sm text-white rounded-pill px-4 shadow-sm" style="background-color:#D97706;border-color:#D97706;">
                    <i class="fas fa-filter me-1"></i>Generar
                </button>
            </div>
        </form>
    </div>

    {{-- HU-012: Indicadores --}}
    <div class="row g-3 mb-4">
        @php $kpis = [['label'=>'Total','value'=>$total,'color'=>'#001A29'],['label'=>'Solicitadas','value'=>$solicitadas,'color'=>'#D97706'],['label'=>'Programadas','value'=>$programadas,'color'=>'#0284C7'],['label'=>'Realizadas','value'=>$realizadas,'color'=>'#39A900'],['label'=>'Canceladas','value'=>$canceladas,'color'=>'#EF4444'],['label'=>'Sin asignar','value'=>$sinAsignar,'color'=>'#6c757d']]; @endphp
        @foreach($kpis as $kpi)
            <div class="col-6 col-md-2">
                <div class="report-card text-center">
                    <div class="small text-muted fw-semibold mb-1">{{ $kpi['label'] }}</div>
                    <div class="h2 fw-bold mb-0" style="color:{{ $kpi['color'] }};">{{ $kpi['value'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- HU-014: Sin datos --}}
    @if($total === 0)
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
            <i class="fas fa-inbox fa-3x mb-3 text-secondary opacity-50"></i>
            <h5 class="fw-bold text-dark mb-1">No se encontraron registros</h5>
            <p class="small mb-0">No hay asesorías que coincidan con los filtros seleccionados.</p>
        </div>
    @else

    {{-- Pestañas --}}
    <ul class="nav nav-pills nav-pills-custom gap-2 mb-4 no-print" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-gf-as" type="button"><i class="fas fa-chart-bar me-1"></i>Gráficas</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-list-as" type="button"><i class="fas fa-table-list me-1"></i>Listado ({{ $total }})</button></li>
    </ul>

    <div class="tab-content">
        {{-- GRÁFICAS --}}
        <div class="tab-pane fade show active" id="tab-gf-as">
            <div class="row g-4">
                <div class="col-12 col-lg-4">
                    <div class="report-card">
                        <h5 class="fw-bold mb-3" style="color:#001A29;font-size:1rem;">Estado de Asesorías</h5>
                        <div style="height:220px;"><canvas id="chartEstadosAs"></canvas></div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="report-card">
                        <h5 class="fw-bold mb-3" style="color:#001A29;font-size:1rem;">Por Categoría</h5>
                        <div style="height:220px;"><canvas id="chartCatAs"></canvas></div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="report-card">
                        <h5 class="fw-bold mb-3" style="color:#001A29;font-size:1rem;">Por Instructor</h5>
                        <div style="height:220px;"><canvas id="chartInstAs"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- LISTADO --}}
        <div class="tab-pane fade" id="tab-list-as">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">ID SICEFA</th>
                                <th>Ficha</th>
                                <th>Fecha</th>
                                <th>Categoría</th>
                                <th>Instructor</th>
                                <th class="text-center">Asistentes</th>
                                <th class="text-center">Satisfacción</th>
                                <th class="text-center">Duración</th>
                                <th class="pe-4">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($asesoriasMock as $ases)
                                <tr>
                                    <td class="ps-4 font-monospace text-muted small">{{ $ases['sicefa_id'] }}</td>
                                    <td class="fw-semibold" style="color:#001A29;">{{ $ases['ficha_code'] }}</td>
                                    <td class="text-muted text-nowrap">{{ \Carbon\Carbon::parse($ases['fecha'])->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $ases['categoria'] }}</span>
                                    </td>
                                    <td class="text-muted">{{ $ases['instructor_nombre'] }}</td>
                                    <td class="text-center fw-semibold">{{ $ases['total_asistentes'] }}</td>
                                    <td class="text-center">
                                        <span class="{{ $ases['satisfaccion'] >= 4 ? 'text-success' : ($ases['satisfaccion'] >= 3 ? 'text-warning' : 'text-danger') }} fw-semibold">
                                            {{ $ases['satisfaccion'] }} ⭐
                                        </span>
                                    </td>
                                    <td class="text-center text-muted">{{ $ases['duracion_min'] }}min</td>
                                    <td class="pe-4">
                                        @php
                                            $eColor = match($ases['estado']) {
                                                'Realizada'  => ['#DCFCE7','#15803D'],
                                                'Programada' => ['#EFF6FF','#1D4ED8'],
                                                'Cancelada'  => ['#FEE2E2','#B91C1C'],
                                                default      => ['#FEF3C7','#92400E'],
                                            };
                                        @endphp
                                        <span class="badge rounded-pill px-2 py-1 small" style="background:{{ $eColor[0] }};color:{{ $eColor[1] }};">{{ $ases['estado'] }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center py-4 text-muted">Sin resultados.</td></tr>
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
    const ctx1 = document.getElementById('chartEstadosAs');
    if (ctx1) new Chart(ctx1, { type:'doughnut', data:{ labels:{!! json_encode($chartEstadosLabels) !!}, datasets:[{ data:{!! json_encode($chartEstadosCounts) !!}, backgroundColor:['#F59E0B','#0284C7','#39A900','#EF4444'], borderWidth:2, borderColor:'#FFF' }] }, options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10, font:{size:10} } } } } });
    const ctx2 = document.getElementById('chartCatAs');
    if (ctx2) new Chart(ctx2, { type:'pie', data:{ labels:{!! json_encode($chartCatLabels) !!}, datasets:[{ data:{!! json_encode($chartCatCounts) !!}, backgroundColor:['#39A900','#0284C7','#F59E0B','#EF4444','#8B5CF6','#EC4899'], borderWidth:2, borderColor:'#FFF' }] }, options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10, font:{size:10} } } } } });
    const ctx3 = document.getElementById('chartInstAs');
    if (ctx3) new Chart(ctx3, { type:'bar', data:{ labels:{!! json_encode($chartInstLabels) !!}, datasets:[{ label:'Asesorías', data:{!! json_encode($chartInstCounts) !!}, backgroundColor:'#D97706', borderRadius:6 }] }, options:{ indexAxis:'y', responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false} }, scales:{ x:{ beginAtZero:true, ticks:{ precision:0 } } } } });
});
</script>
@endpush
