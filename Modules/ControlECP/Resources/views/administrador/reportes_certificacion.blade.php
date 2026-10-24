@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .report-card { background:#FFF; border:1px solid #E2E8F0; border-radius:16px; padding:1.4rem; height:100%; box-shadow:0 2px 10px rgba(0,26,41,.03); }
    .report-filter-bar { background:#FFF; border:1px solid #E2E8F0; border-radius:16px; padding:1rem 1.25rem; }
    .nav-pills-custom .nav-link { color:#64748B; font-weight:600; border-radius:9999px; padding:.5rem 1.25rem; font-size:.9rem; }
    .nav-pills-custom .nav-link.active { background-color:#7C3AED; color:#FFF; box-shadow:0 2px 8px rgba(124,58,237,.25); }
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
                    <li class="breadcrumb-item active" style="color:#001A29;font-weight:600;">Certificación</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color:#001A29;font-family:'Outfit',sans-serif;">Reporte de Certificación</h2>
            <p class="text-muted small mb-0">Por ficha, momento, responsable y estado. Momentos completos, pendientes y certificados. Preparado para integración SOFIA Plus / SICEFA.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('controlecp.administrador.reportes') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm"><i class="fas fa-arrow-left me-1"></i>Volver</a>
            <button onclick="window.print()" class="btn text-white rounded-pill px-4 btn-sm" style="background-color:#7C3AED;border-color:#7C3AED;"><i class="fas fa-file-pdf me-1"></i>Exportar PDF</button>
        </div>
    </div>

    {{-- Encabezado institucional PDF --}}
    <div class="d-none d-print-block text-center mb-4 pb-3 border-bottom">
        <h4 class="fw-bold mb-1" style="color:#001A29;">SERVICIO NACIONAL DE APRENDIZAJE — SENA</h4>
        <h5 class="fw-semibold text-secondary mb-1">CENTRO DE FORMACIÓN AGROINDUSTRIAL LA ANGOSTURA · REGIONAL HUILA</h5>
        <h6 class="text-muted mb-2">Escuela Cultura de Paz · Reporte de Certificación de Momentos</h6>
        <div class="small text-muted">Fecha: {{ now()->format('d/m/Y H:i:s') }} · Generado por: {{ Auth::user()->nickname ?? Auth::user()->name }}</div>
    </div>

    @if($errors->has('fecha'))
        <div class="alert alert-warning rounded-3 mb-3 no-print"><i class="fas fa-triangle-exclamation me-2"></i>{{ $errors->first('fecha') }}</div>
    @endif

    {{-- Filtros --}}
    <div class="report-filter-bar mb-4 shadow-sm no-print">
        <form method="GET" action="{{ route('controlecp.administrador.reportes.certificacion') }}" class="row g-2 align-items-end">
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
                <label class="form-label small text-muted mb-1 fw-semibold">Momento</label>
                <select name="momento_id" class="form-select form-select-sm rounded-pill">
                    <option value="">Todos</option>
                    <option value="1" {{ ($filtros['momento_id'] ?? '') == 1 ? 'selected' : '' }}>Momento 1 — Autoconciencia</option>
                    <option value="2" {{ ($filtros['momento_id'] ?? '') == 2 ? 'selected' : '' }}>Momento 2 — Convivencia</option>
                    <option value="3" {{ ($filtros['momento_id'] ?? '') == 3 ? 'selected' : '' }}>Momento 3 — Resolución</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Estado</label>
                <select name="estado" class="form-select form-select-sm rounded-pill">
                    <option value="">Todos</option>
                    <option value="CERTIFICADA" {{ ($filtros['estado'] ?? '') === 'CERTIFICADA' ? 'selected' : '' }}>Certificada</option>
                    <option value="ANULADA"     {{ ($filtros['estado'] ?? '') === 'ANULADA'     ? 'selected' : '' }}>Anulada</option>
                </select>
            </div>
            {{-- HU-011: Filtrar por responsable --}}
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Responsable</label>
                <select name="responsable_id" class="form-select form-select-sm rounded-pill">
                    <option value="">Todos</option>
                    @foreach($responsables as $r)
                        <option value="{{ $r->id }}" {{ ($filtros['responsable_id'] ?? '') == $r->id ? 'selected' : '' }}>{{ $r->nickname ?? $r->name }}</option>
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
                    <a href="{{ route('controlecp.administrador.reportes.certificacion') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3"><i class="fas fa-times me-1"></i>Limpiar</a>
                @endif
                <button type="submit" class="btn btn-sm text-white rounded-pill px-4 shadow-sm" style="background-color:#7C3AED;border-color:#7C3AED;">
                    <i class="fas fa-filter me-1"></i>Generar
                </button>
            </div>
        </form>
    </div>

    {{-- HU-012: Indicadores --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-2">
            <div class="report-card text-center">
                <div class="small text-muted fw-semibold mb-1">Total</div>
                <div class="h2 fw-bold mb-0" style="color:#001A29;">{{ $total }}</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="report-card text-center">
                <div class="small text-muted fw-semibold mb-1">Certificadas</div>
                <div class="h2 fw-bold mb-0 text-success">{{ $completadas }}</div>
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
                <div class="small text-muted fw-semibold mb-1">Anuladas</div>
                <div class="h2 fw-bold mb-0 text-danger">{{ $anuladas }}</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="report-card text-center">
                <div class="small text-muted fw-semibold mb-1">Fichas completas</div>
                <div class="h2 fw-bold mb-0" style="color:#7C3AED;">{{ $fichasCompletas }}</div>
                <div class="small text-muted">(3 momentos)</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="report-card text-center">
                <div class="small text-muted fw-semibold mb-1">% Certificación</div>
                <div class="h2 fw-bold mb-0" style="color:#7C3AED;">
                    {{ $total > 0 ? round(($completadas / $total) * 100, 1) : 0 }}%
                </div>
            </div>
        </div>
    </div>

    {{-- HU-014: Sin datos --}}
    @if($total === 0)
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
            <i class="fas fa-inbox fa-3x mb-3 text-secondary opacity-50"></i>
            <h5 class="fw-bold text-dark mb-1">No se encontraron registros</h5>
            <p class="small mb-3">No hay certificaciones que coincidan con los filtros seleccionados.</p>
            <a href="{{ route('controlecp.administrador.reportes.certificacion') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-4">Limpiar filtros</a>
        </div>
    @else

    {{-- Pestañas --}}
    <ul class="nav nav-pills nav-pills-custom gap-2 mb-4 no-print" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-gf-cert" type="button"><i class="fas fa-chart-pie me-1"></i>Gráficas</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-fichas-cert" type="button"><i class="fas fa-id-card me-1"></i>Por Ficha ({{ $porFicha->count() }})</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-list-cert" type="button"><i class="fas fa-table-list me-1"></i>Detalle ({{ $total }})</button></li>
    </ul>

    <div class="tab-content">

        {{-- GRÁFICAS --}}
        <div class="tab-pane fade show active" id="tab-gf-cert">
            <div class="row g-4">
                <div class="col-12 col-lg-4">
                    <div class="report-card">
                        <h5 class="fw-bold mb-3" style="color:#001A29;font-size:1rem;">Certificados por Momento</h5>
                        <div style="height:240px;"><canvas id="chartMomentoCert"></canvas></div>
                        <div class="mt-3 pt-3 border-top small">
                            @foreach([['Momento 1','#39A900'],['Momento 2','#0070B8'],['Momento 3','#6A1B9A']] as $i => $m)
                                <div class="d-flex justify-content-between py-1">
                                    <span><i class="fas fa-circle me-1" style="color:{{ $m[1] }};"></i>{{ $m[0] }}</span>
                                    <span class="fw-bold">{{ $chartMomentosCounts[$i] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="report-card">
                        <h5 class="fw-bold mb-3" style="color:#001A29;font-size:1rem;">Estado General</h5>
                        <div class="position-relative" style="height:240px;">
                            <canvas id="chartEstadoCert"></canvas>
                            <div class="chart-center-label">
                                <div class="text-muted small fw-semibold" style="font-size:.75rem;">Total</div>
                                <div class="h3 fw-bold mb-0" style="color:#001A29;">{{ $total }}</div>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top small">
                            <div class="d-flex justify-content-between py-1">
                                <span><i class="fas fa-circle me-1 text-success"></i>Certificadas</span>
                                <span class="fw-bold">{{ $completadas }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-1">
                                <span><i class="fas fa-circle me-1 text-warning"></i>Pendientes</span>
                                <span class="fw-bold">{{ $pendientes }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-1">
                                <span><i class="fas fa-circle me-1 text-danger"></i>Anuladas</span>
                                <span class="fw-bold">{{ $anuladas }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="report-card d-flex flex-column justify-content-center align-items-center text-center" style="gap:1rem;">
                        <div style="width:80px;height:80px;border-radius:50%;background:#EDE9FE;display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-certificate fa-2x" style="color:#7C3AED;"></i>
                        </div>
                        <div>
                            <div class="h1 fw-bold mb-0" style="color:#7C3AED;">{{ $fichasCompletas }}</div>
                            <div class="fw-semibold" style="color:#001A29;">Fichas con 3 momentos certificados</div>
                            <div class="small text-muted mt-1">Preparadas para emisión en SOFIA Plus via SICEFA</div>
                        </div>
                        <div class="w-100 pt-3 border-top small">
                            @php $totalFichasConCerts = $porFicha->count(); @endphp
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted">Fichas con al menos 1 momento</span>
                                <span class="fw-bold">{{ $totalFichasConCerts }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted">Fichas 100% completas</span>
                                <span class="fw-bold text-success">{{ $fichasCompletas }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- POR FICHA --}}
        <div class="tab-pane fade" id="tab-fichas-cert">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Ficha</th>
                                <th>Programa</th>
                                <th class="text-center">Momentos cert.</th>
                                <th class="text-center">Estado</th>
                                <th>Responsable</th>
                                <th class="pe-4">Última cert.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($porFicha as $row)
                                <tr>
                                    <td class="ps-4 font-monospace fw-bold" style="color:#001A29;">{{ $row['ficha'] }}</td>
                                    <td class="text-muted text-truncate" style="max-width:220px;">{{ $row['programa'] }}</td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            @foreach([1,2,3] as $m)
                                                @php $colors = ['#39A900','#0070B8','#6A1B9A']; @endphp
                                                <span class="badge rounded-pill px-2"
                                                      style="{{ $row['momentos_cert'] >= $m ? 'background:'.$colors[$m-1].'20;color:'.$colors[$m-1].';border:1px solid '.$colors[$m-1].'40;' : 'background:#F1F5F9;color:#94A3B8;' }}">
                                                    M{{ $m }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($row['completa'])
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2"><i class="fas fa-check me-1"></i>Completa</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2">{{ $row['momentos_cert'] }}/3</span>
                                        @endif
                                    </td>
                                    <td class="text-muted">{{ $row['responsable'] }}</td>
                                    <td class="pe-4 text-muted text-nowrap">
                                        {{ $row['ultima_fecha'] ? \Carbon\Carbon::parse($row['ultima_fecha'])->format('d/m/Y') : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center py-4 text-muted">Sin resultados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- DETALLE --}}
        <div class="tab-pane fade" id="tab-list-cert">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Ficha</th>
                                <th>Momento</th>
                                <th class="text-center">% Asistencia</th>
                                <th class="text-center">Aprendices</th>
                                <th>Responsable</th>
                                <th>Fecha cert.</th>
                                <th class="text-center">Estado</th>
                                <th class="pe-4 text-center">SOFIA Plus</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($certificados as $cert)
                                @php
                                    $mColors = [1=>'#39A900', 2=>'#0070B8', 3=>'#6A1B9A'];
                                    $mc = $mColors[$cert->momento_id] ?? '#6c757d';
                                @endphp
                                <tr>
                                    <td class="ps-4 font-monospace fw-bold" style="color:#001A29;">{{ $cert->course?->code ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge rounded-pill px-2" style="background:{{ $mc }}20;color:{{ $mc }};border:1px solid {{ $mc }}40;">
                                            {{ $cert->momento_nombre }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-semibold {{ ($cert->porcentaje_asistencia ?? 0) >= 80 ? 'text-success' : 'text-warning' }}">
                                            {{ $cert->porcentaje_asistencia ?? '—' }}%
                                        </span>
                                    </td>
                                    <td class="text-center fw-semibold">{{ $cert->total_aprendices ?? '—' }}</td>
                                    <td class="text-muted">
                                        {{ $cert->responsable?->person?->first_name
                                            ? $cert->responsable->person->first_name.' '.$cert->responsable->person->first_last_name
                                            : ($cert->responsable?->nickname ?? 'N/A') }}
                                    </td>
                                    <td class="text-muted text-nowrap">{{ $cert->fecha_certificacion?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill px-2 bg-{{ $cert->estado_badge }}-subtle text-{{ $cert->estado_badge }}-emphasis border border-{{ $cert->estado_badge }}-subtle">
                                            {{ $cert->estado }}
                                        </span>
                                    </td>
                                    <td class="pe-4 text-center">
                                        @if($cert->emitido_sofia)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2"><i class="fas fa-check me-1"></i>Emitido</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2">Pendiente</span>
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
    const ctx1 = document.getElementById('chartMomentoCert');
    if (ctx1) {
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartMomentosLabels) !!},
                datasets: [{ label: 'Certificados', data: {!! json_encode($chartMomentosCounts) !!}, backgroundColor: ['#39A900', '#0070B8', '#6A1B9A'], borderRadius: 10 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
    }
    const ctx2 = document.getElementById('chartEstadoCert');
    if (ctx2) {
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($chartEstadosLabels) !!},
                datasets: [{ data: {!! json_encode($chartEstadosCounts) !!}, backgroundColor: ['#10B981', '#F59E0B', '#EF4444'], borderWidth: 2, borderColor: '#FFF' }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } } } }
        });
    }
});
</script>
@endpush
