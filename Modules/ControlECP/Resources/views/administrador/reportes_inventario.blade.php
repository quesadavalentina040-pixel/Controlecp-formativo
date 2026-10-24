@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .report-card { background:#FFF; border:1px solid #E2E8F0; border-radius:16px; padding:1.4rem; height:100%; box-shadow:0 2px 10px rgba(0,26,41,.03); }
    .report-filter-bar { background:#FFF; border:1px solid #E2E8F0; border-radius:16px; padding:1rem 1.25rem; }
    .nav-pills-custom .nav-link { color:#64748B; font-weight:600; border-radius:9999px; padding:.5rem 1.25rem; font-size:.9rem; }
    .nav-pills-custom .nav-link.active { background-color:#E11D48; color:#FFF; box-shadow:0 2px 8px rgba(225,29,72,.2); }
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
                    <li class="breadcrumb-item active" style="color:#001A29;font-weight:600;">Inventario</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color:#001A29;font-family:'Outfit',sans-serif;">Reporte de Inventario</h2>
            <p class="text-muted small mb-0">Semáforo de disponibilidad, movimientos, novedades, alertas de devoluciones incompletas y quién registró cada movimiento.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('controlecp.administrador.reportes') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm"><i class="fas fa-arrow-left me-1"></i>Volver</a>
            <button onclick="window.print()" class="btn text-white rounded-pill px-4 btn-sm" style="background-color:#E11D48;border-color:#E11D48;"><i class="fas fa-file-pdf me-1"></i>Exportar PDF</button>
        </div>
    </div>

    {{-- Encabezado institucional PDF --}}
    <div class="d-none d-print-block text-center mb-4 pb-3 border-bottom">
        <h4 class="fw-bold mb-1" style="color:#001A29;">SERVICIO NACIONAL DE APRENDIZAJE — SENA</h4>
        <h5 class="fw-semibold text-secondary mb-1">CENTRO DE FORMACIÓN AGROINDUSTRIAL LA ANGOSTURA · REGIONAL HUILA</h5>
        <h6 class="text-muted mb-2">Escuela Cultura de Paz · Reporte de Inventario</h6>
        <div class="small text-muted">Fecha: {{ now()->format('d/m/Y H:i:s') }} · Generado por: {{ Auth::user()->nickname ?? Auth::user()->name }}</div>
        <div class="row g-2 mt-3 text-start">
            <div class="col-3"><div class="border p-2 rounded text-center"><small class="text-muted d-block">Total recursos</small><strong class="fs-5">{{ $total }}</strong></div></div>
            <div class="col-3"><div class="border p-2 rounded text-center" style="background:#F0FDF4;"><small class="text-success d-block">Disponibles</small><strong class="fs-5 text-success">{{ $verdeCount }}</strong></div></div>
            <div class="col-3"><div class="border p-2 rounded text-center" style="background:#FFFBEB;"><small class="text-warning-emphasis d-block">Regular</small><strong class="fs-5 text-warning-emphasis">{{ $amarilloCount }}</strong></div></div>
            <div class="col-3"><div class="border p-2 rounded text-center" style="background:#FEF2F2;"><small class="text-danger d-block">Crítico</small><strong class="fs-5 text-danger">{{ $rojoCount }}</strong></div></div>
        </div>
    </div>

    @if($errors->has('fecha'))
        <div class="alert alert-warning rounded-3 mb-3 no-print"><i class="fas fa-triangle-exclamation me-2"></i>{{ $errors->first('fecha') }}</div>
    @endif

    {{-- Filtros --}}
    <div class="report-filter-bar mb-4 shadow-sm no-print">
        <form method="GET" action="{{ route('controlecp.administrador.reportes.inventario') }}" class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label small text-muted mb-1 fw-semibold">Categoría</label>
                <select name="categoria_id" class="form-select form-select-sm rounded-pill">
                    <option value="">Todas las categorías</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id_categoria_inventario }}" {{ ($filtros['categoria_id'] ?? '') == $cat->id_categoria_inventario ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Estado físico</label>
                <select name="estado_fisico" class="form-select form-select-sm rounded-pill">
                    <option value="">Todos</option>
                    <option value="Bueno"    {{ ($filtros['estado_fisico'] ?? '') === 'Bueno'    ? 'selected' : '' }}>Bueno</option>
                    <option value="Regular"  {{ ($filtros['estado_fisico'] ?? '') === 'Regular'  ? 'selected' : '' }}>Regular</option>
                    <option value="Crítico"  {{ ($filtros['estado_fisico'] ?? '') === 'Crítico'  ? 'selected' : '' }}>Crítico</option>
                </select>
            </div>
            {{-- HU-011: Filtrar por responsable (usuario) --}}
            <div class="col-6 col-md-3">
                <label class="form-label small text-muted mb-1 fw-semibold">Usuario (movimientos)</label>
                <select name="id_usuario" class="form-select form-select-sm rounded-pill">
                    <option value="">Todos</option>
                    @foreach($usuarios as $u)
                        <option value="{{ $u->id }}" {{ ($filtros['id_usuario'] ?? '') == $u->id ? 'selected' : '' }}>{{ $u->nickname ?? $u->name }}</option>
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
                    <a href="{{ route('controlecp.administrador.reportes.inventario') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3"><i class="fas fa-times me-1"></i>Limpiar</a>
                @endif
                <button type="submit" class="btn btn-sm text-white rounded-pill px-4 shadow-sm" style="background-color:#E11D48;border-color:#E11D48;">
                    <i class="fas fa-filter me-1"></i>Generar
                </button>
            </div>
        </form>
    </div>

    {{-- HU-012: Indicadores --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-2">
            <div class="report-card text-center">
                <div class="small text-muted fw-semibold mb-1">Total recursos</div>
                <div class="h2 fw-bold mb-0" style="color:#001A29;">{{ $total }}</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="report-card text-center">
                <div class="small text-muted fw-semibold mb-1">🟢 Disponibles</div>
                <div class="h2 fw-bold mb-0 text-success">{{ $verdeCount }}</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="report-card text-center">
                <div class="small text-muted fw-semibold mb-1">🟡 Regular</div>
                <div class="h2 fw-bold mb-0 text-warning">{{ $amarilloCount }}</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="report-card text-center">
                <div class="small text-muted fw-semibold mb-1">🔴 Crítico</div>
                <div class="h2 fw-bold mb-0 text-danger">{{ $rojoCount }}</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="report-card text-center">
                <div class="small text-muted fw-semibold mb-1">Movimientos</div>
                <div class="h2 fw-bold mb-0" style="color:#001A29;">{{ $movimientos->count() }}</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="report-card text-center">
                <div class="small text-muted fw-semibold mb-1">Alertas devolución</div>
                <div class="h2 fw-bold mb-0 {{ $alertas->count() > 0 ? 'text-danger' : 'text-success' }}">{{ $alertas->count() }}</div>
            </div>
        </div>
    </div>

    {{-- HU-014: Sin datos --}}
    @if($total === 0)
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
            <i class="fas fa-inbox fa-3x mb-3 text-secondary opacity-50"></i>
            <h5 class="fw-bold text-dark mb-1">No se encontraron registros</h5>
            <p class="small mb-3">No hay recursos de inventario que coincidan con los filtros seleccionados.</p>
            <a href="{{ route('controlecp.administrador.reportes.inventario') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-4">Limpiar filtros</a>
        </div>
    @else

    {{-- Pestañas --}}
    <ul class="nav nav-pills nav-pills-custom gap-2 mb-4 no-print" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-resumen-inv" type="button"><i class="fas fa-chart-pie me-1"></i>Resumen</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-recursos-inv" type="button"><i class="fas fa-boxes-stacked me-1"></i>Recursos ({{ $total }})</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-mov-inv" type="button"><i class="fas fa-arrows-rotate me-1"></i>Movimientos ({{ $movimientos->count() }})</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-nov-inv" type="button"><i class="fas fa-file-lines me-1"></i>Novedades ({{ $novedades->count() }})</button></li>
        <li class="nav-item">
            <button class="nav-link position-relative" data-bs-toggle="pill" data-bs-target="#tab-alertas-inv" type="button">
                <i class="fas fa-triangle-exclamation me-1"></i>Alertas
                @if($alertas->count() > 0)
                    <span class="badge rounded-pill bg-danger ms-1">{{ $alertas->count() }}</span>
                @endif
            </button>
        </li>
    </ul>

    <div class="tab-content">

        {{-- RESUMEN --}}
        <div class="tab-pane fade show active" id="tab-resumen-inv">
            <div class="row g-4">
                <div class="col-12 col-lg-4">
                    <div class="report-card">
                        <h5 class="fw-bold mb-3" style="color:#001A29;font-size:1rem;">Estado de recursos</h5>
                        <div class="position-relative" style="height:220px;">
                            <canvas id="chartEstadoInv"></canvas>
                            <div class="chart-center-label">
                                <div class="text-muted small fw-semibold" style="font-size:.75rem;">Total</div>
                                <div class="h3 fw-bold mb-0" style="color:#001A29;">{{ $total }}</div>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top small">
                            <div class="d-flex justify-content-between py-1"><span><i class="fas fa-circle me-1" style="color:#10B981;"></i>Disponibles</span><span class="fw-bold">{{ $verdeCount }}</span></div>
                            <div class="d-flex justify-content-between py-1"><span><i class="fas fa-circle me-1" style="color:#F59E0B;"></i>En estado regular</span><span class="fw-bold">{{ $amarilloCount }}</span></div>
                            <div class="d-flex justify-content-between py-1"><span><i class="fas fa-circle me-1" style="color:#EF4444;"></i>En estado crítico</span><span class="fw-bold">{{ $rojoCount }}</span></div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="report-card">
                        <h5 class="fw-bold mb-3" style="color:#001A29;font-size:1rem;">Movimientos por tipo</h5>
                        <div style="height:250px;"><canvas id="chartMovInv"></canvas></div>
                        <div class="mt-2 pt-2 border-top small text-muted text-center">
                            Total: <strong>{{ $entregasCount + $devolucionesCount + $cambioEstadoCount + $bajasCount }}</strong>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="report-card">
                        <h5 class="fw-bold mb-3" style="color:#001A29;font-size:1rem;">Recursos por categoría</h5>
                        <div style="height:250px;"><canvas id="chartCatInv"></canvas></div>
                        <div class="mt-2 pt-2 border-top small text-muted text-center">
                            <strong>{{ $categorias->count() }}</strong> categorías registradas
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RECURSOS --}}
        <div class="tab-pane fade" id="tab-recursos-inv">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Nombre</th>
                                <th>Categoría</th>
                                <th class="text-center">Disponible</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Estado físico</th>
                                <th class="text-center pe-4">Semáforo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recursos as $r)
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">{{ $r->nombre }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $r->categoria?->nombre ?? 'Sin categoría' }}</span></td>
                                    <td class="text-center fw-bold {{ $r->cantidad_disponible <= $r->umbral_critico ? 'text-danger' : 'text-success' }}">{{ $r->cantidad_disponible }}</td>
                                    <td class="text-center text-muted">{{ $r->cantidad_total }}</td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill px-2 py-1 small" style="background:{{ $r->estado_fisico_badge['bg'] }};color:{{ $r->estado_fisico_badge['text'] }};">{{ $r->estado_fisico ?? 'Bueno' }}</span>
                                    </td>
                                    <td class="text-center pe-4">
                                        @if($r->semaforo === 'verde')
                                            <span class="badge rounded-pill px-2 border" style="background:#ECFDF5;color:#065F46;border-color:#A7F3D0 !important;">🟢 Disponible</span>
                                        @elseif($r->semaforo === 'amarillo')
                                            <span class="badge rounded-pill px-2 border" style="background:#FFFBEB;color:#92400E;border-color:#FDE68A !important;">🟡 Moderado</span>
                                        @else
                                            <span class="badge rounded-pill px-2 border" style="background:#FEF2F2;color:#991B1B;border-color:#FECACA !important;">🔴 Crítico</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center py-4 text-muted">Sin recursos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- MOVIMIENTOS --}}
        <div class="tab-pane fade" id="tab-mov-inv">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Fecha</th>
                                <th>Tipo</th>
                                <th>Recurso</th>
                                <th class="text-center">Cantidad</th>
                                <th>Observación</th>
                                <th class="pe-4 text-end">Autor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movimientos as $m)
                                <tr>
                                    <td class="ps-4 text-nowrap text-muted">{{ $m->fecha?->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td>
                                        @php
                                            $tColors = ['Entrega'=>['#E0F2FE','#0369A1'],'Devolución'=>['#DCFCE7','#15803D'],'Baja'=>['#FEE2E2','#B91C1C'],'CambioEstado'=>['#F3E8FF','#7E22CE']];
                                            $tc = $tColors[$m->tipo] ?? ['#F1F5F9','#475569'];
                                        @endphp
                                        <span class="badge rounded-pill px-2" style="background:{{ $tc[0] }};color:{{ $tc[1] }};">{{ $m->tipo }}</span>
                                    </td>
                                    <td class="fw-semibold text-dark">{{ $m->inventario?->nombre ?? 'Recurso' }}</td>
                                    <td class="text-center fw-bold">{{ $m->cantidad }}</td>
                                    <td class="text-truncate text-muted" style="max-width:250px;">{{ $m->observacion ?? '—' }}</td>
                                    <td class="pe-4 text-end text-muted">{{ $m->usuario?->nickname ?? $m->usuario?->name ?? 'Sistema' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center py-4 text-muted">Sin movimientos en este periodo.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- NOVEDADES --}}
        <div class="tab-pane fade" id="tab-nov-inv">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <div class="row g-3">
                    @forelse($novedades as $nov)
                        <div class="col-12 col-md-6">
                            <div class="p-3 rounded-3 border bg-white shadow-sm">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="text-dark">{{ $nov->inventario?->nombre ?? 'Recurso' }}</strong>
                                    <small class="text-muted">{{ $nov->fecha?->format('d/m/Y H:i') ?? '' }}</small>
                                </div>
                                <p class="small text-muted mb-2">{{ $nov->descripcion }}</p>
                                <div class="small text-secondary pt-2 border-top"><i class="fas fa-user me-1"></i>{{ $nov->usuario?->nickname ?? $nov->usuario?->name ?? 'Sistema' }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5 text-muted">Sin novedades registradas.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ALERTAS --}}
        <div class="tab-pane fade" id="tab-alertas-inv">
            @forelse($alertas as $al)
                <div class="card border-0 shadow-sm mb-3 border-start border-4 border-danger" style="background:#FFF5F5;border-radius:12px;">
                    <div class="card-body p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="text-danger fs-3"><i class="fas fa-triangle-exclamation"></i></div>
                            <div>
                                <span class="badge bg-danger text-white mb-1">Devolución incompleta</span>
                                <div class="fw-semibold text-danger small">{{ $al->observacion }}</div>
                                <div class="text-muted small">
                                    Recurso: <strong>{{ $al->inventario?->nombre ?? 'N/A' }}</strong> ·
                                    Fecha: {{ $al->fecha?->format('d/m/Y H:i') ?? '' }} ·
                                    Responsable: {{ $al->usuario?->nickname ?? $al->usuario?->name ?? 'Sistema' }}
                                </div>
                            </div>
                        </div>
                        @if($al->inventario)
                            <a href="{{ route('controlecp.administrador.inventario.show', $al->inventario->id_inventario) }}" class="btn btn-sm btn-outline-danger rounded-pill px-3">Ver detalle</a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="card border-0 shadow-sm p-5 text-center text-muted" style="border-radius:16px;">
                    <i class="fas fa-circle-check fa-3x text-success mb-2"></i>
                    <h5 class="fw-bold text-dark mb-1">Sin alertas de devolución pendientes</h5>
                    <p class="small mb-0">Todas las entregas han sido devueltas correctamente.</p>
                </div>
            @endforelse
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx1 = document.getElementById('chartEstadoInv');
    if (ctx1) new Chart(ctx1, { type:'doughnut', data:{ labels:['Disponibles','En estado regular','En estado crítico'], datasets:[{ data:[{{ $verdeCount }},{{ $amarilloCount }},{{ $rojoCount }}], backgroundColor:['#10B981','#F59E0B','#EF4444'], borderWidth:3, borderColor:'#FFF' }] }, options:{ responsive:true, maintainAspectRatio:false, cutout:'72%', plugins:{ legend:{display:false} } } });
    const ctx2 = document.getElementById('chartMovInv');
    if (ctx2) new Chart(ctx2, { type:'bar', data:{ labels:['Entregas','Devoluciones','Cambios estado','Bajas'], datasets:[{ label:'Movimientos', data:[{{ $entregasCount }},{{ $devolucionesCount }},{{ $cambioEstadoCount }},{{ $bajasCount }}], backgroundColor:['#0284C7','#10B981','#38BDF8','#F87171'], borderRadius:8 }] }, options:{ indexAxis:'y', responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false} }, scales:{ x:{ beginAtZero:true, ticks:{ precision:0 } } } } });
    const ctx3 = document.getElementById('chartCatInv');
    if (ctx3) new Chart(ctx3, { type:'pie', data:{ labels:{!! json_encode($categoriasLabels) !!}, datasets:[{ data:{!! json_encode($categoriasCounts) !!}, backgroundColor:['#3B82F6','#F97316','#EC4899','#06B6D4','#EAB308','#8B5CF6','#10B981'], borderWidth:2, borderColor:'#FFF' }] }, options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10, font:{size:10} } } } } });
});
</script>
@endpush
