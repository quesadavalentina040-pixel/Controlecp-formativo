@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .report-card { background:#FFF; border:1px solid #E2E8F0; border-radius:16px; padding:1.4rem; height:100%; box-shadow:0 2px 10px rgba(0,26,41,.03); }
    .report-filter-bar { background:#FFF; border:1px solid #E2E8F0; border-radius:16px; padding:1rem 1.25rem; }
    .nav-pills-custom .nav-link { color:#64748B; font-weight:600; border-radius:9999px; padding:.5rem 1.25rem; font-size:.9rem; }
    .nav-pills-custom .nav-link.active { background-color:#39A900; color:#FFF; box-shadow:0 2px 8px rgba(57,169,0,.25); }
    .star-fill { color:#F59E0B; }
    .star-empty { color:#E2E8F0; }
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
                    <li class="breadcrumb-item active" style="color:#001A29;font-weight:600;">Encuestas</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color:#001A29;font-family:'Outfit',sans-serif;">Reporte de Encuestas</h2>
            <p class="text-muted small mb-0">Tabulación consolidada con gráficas. Respuestas, participación, promedio y calificación por actividad.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('controlecp.administrador.reportes') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm"><i class="fas fa-arrow-left me-1"></i>Volver</a>
            <button onclick="window.print()" class="btn text-white rounded-pill px-4 btn-sm" style="background-color:#39A900;border-color:#39A900;"><i class="fas fa-file-pdf me-1"></i>Exportar PDF</button>
        </div>
    </div>

    {{-- Encabezado institucional para PDF --}}
    <div class="d-none d-print-block text-center mb-4 pb-3 border-bottom">
        <h4 class="fw-bold mb-1" style="color:#001A29;">SERVICIO NACIONAL DE APRENDIZAJE — SENA</h4>
        <h5 class="fw-semibold text-secondary mb-1">CENTRO DE FORMACIÓN AGROINDUSTRIAL LA ANGOSTURA · REGIONAL HUILA</h5>
        <h6 class="text-muted mb-2">Escuela Cultura de Paz · Reporte de Encuestas y Calificaciones</h6>
        <div class="small text-muted">Fecha: {{ now()->format('d/m/Y H:i:s') }} · Generado por: {{ Auth::user()->nickname ?? Auth::user()->name }}</div>
    </div>

    @if($errors->has('fecha'))
        <div class="alert alert-warning rounded-3 mb-3 no-print"><i class="fas fa-triangle-exclamation me-2"></i>{{ $errors->first('fecha') }}</div>
    @endif

    {{-- Filtros --}}
    <div class="report-filter-bar mb-4 shadow-sm no-print">
        <form method="GET" action="{{ route('controlecp.administrador.reportes.encuestas') }}" class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label small text-muted mb-1 fw-semibold">Ficha</label>
                <select name="ficha" class="form-select form-select-sm rounded-pill">
                    <option value="">Todas las fichas</option>
                    @foreach($fichas as $f)
                        <option value="{{ $f->code }}" {{ ($filtros['ficha'] ?? '') == $f->code ? 'selected' : '' }}>{{ $f->code }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small text-muted mb-1 fw-semibold">Actividad</label>
                <select name="actividad_id" class="form-select form-select-sm rounded-pill">
                    <option value="">Todas</option>
                    @foreach($actividades as $a)
                        <option value="{{ $a->id }}" {{ ($filtros['actividad_id'] ?? '') == $a->id ? 'selected' : '' }}>Act.{{ $a->numero }} — {{ Str::limit($a->nombre, 30) }}</option>
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
                    <a href="{{ route('controlecp.administrador.reportes.encuestas') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3"><i class="fas fa-times me-1"></i>Limpiar</a>
                @endif
                <button type="submit" class="btn btn-sm text-white rounded-pill px-4 shadow-sm" style="background-color:#39A900;border-color:#39A900;">
                    <i class="fas fa-filter me-1"></i>Generar
                </button>
            </div>
        </form>
    </div>

    {{-- HU-012: Indicadores --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="report-card text-center">
                <div class="small text-muted fw-semibold mb-1">Total respuestas</div>
                <div class="h2 fw-bold mb-0" style="color:#001A29;">{{ $total }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="report-card text-center">
                <div class="small text-muted fw-semibold mb-1">Promedio global</div>
                <div class="h2 fw-bold mb-0" style="color:#F59E0B;">{{ $promedioGlobal }} ⭐</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="report-card text-center">
                <div class="small text-muted fw-semibold mb-1">Actividades evaluadas</div>
                <div class="h2 fw-bold mb-0" style="color:#001A29;">{{ $tabulacion->count() }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="report-card text-center">
                <div class="small text-muted fw-semibold mb-1">Calificación 5 ⭐</div>
                <div class="h2 fw-bold mb-0 text-success">{{ $distGlobal[5] ?? 0 }}</div>
                <div class="small text-muted">{{ $total > 0 ? round((($distGlobal[5] ?? 0) / $total) * 100, 1) : 0 }}%</div>
            </div>
        </div>
    </div>

    {{-- HU-014: Sin datos --}}
    @if($total === 0)
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
            <i class="fas fa-inbox fa-3x mb-3 text-secondary opacity-50"></i>
            <h5 class="fw-bold text-dark mb-1">No se encontraron registros</h5>
            <p class="small mb-3">No hay calificaciones de encuestas que coincidan con los filtros seleccionados.</p>
            <a href="{{ route('controlecp.administrador.reportes.encuestas') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-4">Limpiar filtros</a>
        </div>
    @else

    {{-- Pestañas --}}
    <ul class="nav nav-pills nav-pills-custom gap-2 mb-4 no-print" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-tabulacion" type="button"><i class="fas fa-table me-1"></i>Tabulación</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-graficas-enc" type="button"><i class="fas fa-chart-bar me-1"></i>Gráficas</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-detalle-enc" type="button"><i class="fas fa-list me-1"></i>Detalle ({{ $total }})</button></li>
    </ul>

    <div class="tab-content">

        {{-- TABULACIÓN CONSOLIDADA --}}
        <div class="tab-pane fade show active" id="tab-tabulacion">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h5 class="fw-bold mb-0" style="color:#001A29;">Tabulación por Actividad</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Actividad</th>
                                <th>Momento</th>
                                <th class="text-center">Respuestas</th>
                                <th class="text-center">Promedio</th>
                                <th class="text-center">1⭐</th>
                                <th class="text-center">2⭐</th>
                                <th class="text-center">3⭐</th>
                                <th class="text-center">4⭐</th>
                                <th class="text-center pe-4">5⭐</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tabulacion as $row)
                                <tr>
                                    <td class="ps-4 fw-semibold text-dark text-truncate" style="max-width:220px;" title="{{ $row['actividad'] }}">{{ $row['actividad'] }}</td>
                                    <td class="text-muted">{{ $row['momento'] }}</td>
                                    <td class="text-center fw-bold">{{ $row['total'] }}</td>
                                    <td class="text-center">
                                        <span class="fw-bold {{ $row['promedio'] >= 4 ? 'text-success' : ($row['promedio'] >= 3 ? 'text-warning' : 'text-danger') }}">
                                            {{ $row['promedio'] }} ⭐
                                        </span>
                                    </td>
                                    @foreach([1,2,3,4,5] as $star)
                                        <td class="text-center text-muted">{{ $row['dist'][$star] }}</td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center py-4 text-muted">Sin datos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- GRÁFICAS --}}
        <div class="tab-pane fade" id="tab-graficas-enc">
            <div class="row g-4">
                <div class="col-12 col-lg-6">
                    <div class="report-card">
                        <h5 class="fw-bold mb-3" style="color:#001A29;font-size:1rem;">Promedio por actividad (Top 10)</h5>
                        <div style="height:280px;"><canvas id="chartActPromedio"></canvas></div>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="report-card">
                        <h5 class="fw-bold mb-3" style="color:#001A29;font-size:1rem;">Distribución global de estrellas</h5>
                        <div style="height:280px;"><canvas id="chartDistEstrellas"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- DETALLE --}}
        <div class="tab-pane fade" id="tab-detalle-enc">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Actividad</th>
                                <th>Ficha</th>
                                <th>Aprendiz</th>
                                <th class="text-center">Estrellas</th>
                                <th class="pe-4">Comentario</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($calificaciones->take(300) as $cal)
                                <tr>
                                    <td class="ps-4 fw-semibold text-dark text-truncate" style="max-width:200px;">
                                        Act.{{ $cal->actividad?->numero }} — {{ $cal->actividad?->nombre }}
                                    </td>
                                    <td class="font-monospace text-muted">{{ $cal->ficha?->code ?? 'N/A' }}</td>
                                    <td class="text-dark">{{ $cal->aprendiz?->person?->first_name ?? $cal->aprendiz?->nickname ?? 'Aprendiz' }}</td>
                                    <td class="text-center">
                                        @for($s = 1; $s <= 5; $s++)
                                            <i class="fas fa-star {{ $s <= $cal->estrellas ? 'star-fill' : 'star-empty' }}" style="font-size:.75rem;"></i>
                                        @endfor
                                    </td>
                                    <td class="pe-4 text-muted text-truncate" style="max-width:220px;">{{ $cal->comentario ?? '—' }}</td>
                                </tr>
                            @endforeach
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
    const ctx1 = document.getElementById('chartActPromedio');
    if (ctx1) {
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartActLabels) !!},
                datasets: [{ label: 'Promedio ⭐', data: {!! json_encode($chartActPromedio) !!}, backgroundColor: '#39A900', borderRadius: 8 }]
            },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, max: 5 } } }
        });
    }
    const ctx2 = document.getElementById('chartDistEstrellas');
    if (ctx2) {
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: ['1 ⭐', '2 ⭐', '3 ⭐', '4 ⭐', '5 ⭐'],
                datasets: [{ label: 'Respuestas', data: [{{ $distGlobal[1] ?? 0 }}, {{ $distGlobal[2] ?? 0 }}, {{ $distGlobal[3] ?? 0 }}, {{ $distGlobal[4] ?? 0 }}, {{ $distGlobal[5] ?? 0 }}], backgroundColor: ['#EF4444','#F97316','#F59E0B','#84CC16','#22C55E'], borderRadius: 8 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
    }
});
</script>
@endpush
