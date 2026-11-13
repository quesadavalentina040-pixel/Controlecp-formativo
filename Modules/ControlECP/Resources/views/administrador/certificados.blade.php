@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .certificaciones-hero {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1.5rem 2rem;
        color: #fff;
        background: linear-gradient(120deg, #001A29, #00324D);
        border-radius: 22px;
    }
    .certificaciones-hero h2 { color: #fff; font-weight: 800; }
    .certificaciones-hero p { color: #c7d4da; }
    .certificacion-kpi {
        height: 100%;
        background: #fff;
        border: 2px solid transparent;
        border-radius: 14px;
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .certificacion-kpi:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0, 26, 41, .08); }
    .certificacion-kpi-label { font-size: .95rem; color: #475569; font-weight: 500; }
    .certificacion-kpi-value { font-size: 1.7rem; font-weight: 800; line-height: 1; margin-top: .15rem; }
    .certificacion-kpi-subtitle { font-size: .9rem; color: #64748b; }
    .certificacion-filtros-card {
        padding: 1rem 1.15rem;
        background: #001A29;
        border-radius: 18px;
        box-shadow: 0 5px 20px rgba(0, 26, 41, .08);
    }
    .certificacion-filtros {
        display: grid;
        grid-template-columns: minmax(150px, 1fr) repeat(3, minmax(145px, 1fr)) repeat(2, minmax(150px, 1fr)) 220px;
        gap: .65rem;
        align-items: end;
    }
    .certificacion-filtro-campo { min-width: 0; }
    .certificacion-filtros label {
        display: block;
        margin: 0 0 .3rem;
        color: #fff;
        font-size: .75rem;
        font-weight: 700;
    }
    .certificacion-filtros input,
    .certificacion-filtros select {
        width: 100%;
        min-width: 0;
        height: 44px;
        padding: .55rem .8rem;
        border: 1px solid #fff;
        border-radius: 10px;
        background: #fff;
        color: #001A29;
        font-size: .88rem;
    }
    .certificacion-filtro-acciones {
        display: flex;
        gap: .55rem;
        min-width: 0;
    }
    .certificacion-buscar {
        flex: 1;
        min-width: 0;
        height: 44px;
        border: 0;
        border-radius: 10px;
        background: #39A900;
        color: #fff;
        font-weight: 700;
        white-space: nowrap;
    }
    .certificacion-buscar:hover,
    .certificacion-buscar:focus { background: #2f8f00; color: #fff; }
    .certificacion-restablecer {
        flex: 0 0 48px;
        height: 44px;
        display: grid;
        place-items: center;
        padding: 0;
        border: 1px solid #fff;
        border-radius: 10px;
        background: #fff;
        color: #001A29;
    }
    .certificacion-restablecer:hover { background: #f1f5f9; color: #001A29; }
    .certificacion-momento-tabs {
        display: inline-flex;
        flex-wrap: wrap;
        max-width: 100%;
    }
    .certificacion-momento-tabs .nav-link { color: #001A29; font-weight: 700; }
    .certificacion-momento-tabs .nav-link.active {
        background: #39A900;
        color: #fff;
    }
    .certificacion-momento-tabs .nav-link:not(.active):hover { color: #2f8f00; }
    .certificacion-progreso {
        min-width: 110px;
        height: 8px;
        background: #e9ecef;
        border-radius: 8px;
        overflow: hidden;
    }
    .certificacion-progreso span {
        display: block;
        height: 100%;
        background: var(--sena-green);
    }
    .certificacion-estado {
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
        padding: .35rem .75rem;
        border: 1px solid transparent;
        border-radius: 999px;
        font-size: .8rem;
        font-weight: 600;
    }
    .certificacion-estado--certificada {
        background: #d1fae5;
        border-color: #a7f3d0;
        color: #047857;
    }
    .certificacion-estado--pendiente {
        background: #fef3c7;
        border-color: #fde68a;
        color: #92400e;
    }
    .certificacion-estado--progreso {
        background: #dbeafe;
        border-color: #bfdbfe;
        color: #1d4ed8;
    }
    .certificacion-row-actions {
        display: flex;
        justify-content: flex-end;
        gap: .4rem;
        white-space: nowrap;
    }
    .certificacion-row-action {
        display: inline-flex;
        width: 34px;
        height: 34px;
        align-items: center;
        justify-content: center;
        padding: 0;
        border: 1px solid transparent;
        border-radius: 10px !important;
        transition: background-color .15s ease, color .15s ease, transform .15s ease, box-shadow .15s ease;
    }
    .certificacion-row-action:hover,
    .certificacion-row-action:focus-visible {
        transform: translateY(-2px);
        box-shadow: 0 3px 8px rgba(0, 26, 41, .14);
    }
    .certificacion-row-action--view {
        background: #eaf5ff;
        border-color: #c9e6fb;
        color: #0876bd;
    }
    .certificacion-row-action--view:hover {
        background: #0876bd;
        border-color: #0876bd;
        color: #fff;
    }
    .certificacion-row-action--history {
        background: #f3edff;
        border-color: #ddccff;
        color: #7541c8;
    }
    .certificacion-row-action--history:hover {
        background: #7541c8;
        border-color: #7541c8;
        color: #fff;
    }
    .certificacion-row-action--certify {
        background: #eaf8ef;
        border-color: #ccebd6;
        color: #278344;
    }
    .certificacion-row-action--certify:hover {
        background: #278344;
        border-color: #278344;
        color: #fff;
    }
    @media (max-width: 1199px) {
        .certificacion-filtros { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .certificacion-filtro-acciones { grid-column: span 3; justify-content: flex-end; }
        .certificacion-buscar { flex: 0 0 200px; }
    }
    @media (max-width: 767px) {
        .certificacion-filtros { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .certificacion-filtro-acciones { grid-column: span 2; }
    }
    @media (max-width: 480px) {
        .certificacion-filtros { grid-template-columns: minmax(0, 1fr); }
        .certificacion-filtro-acciones { grid-column: auto; }
        .certificacion-buscar { flex: 1; }
    }
    @media print {
        body * { visibility: hidden !important; }
        #reporteCertificaciones, #reporteCertificaciones * { visibility: visible !important; }
        #reporteCertificaciones {
            position: absolute;
            inset: 0;
            width: 100%;
            padding: 0 !important;
        }
        .no-imprimir { display: none !important; }
        .certificacion-momento-tabs { display: none !important; }
        .certificacion-momento-tab-content > .tab-pane {
            display: block !important;
            opacity: 1 !important;
        }
        .table { font-size: 10px; }
        .table th, .table td { padding: .3rem; }
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-3 py-3" id="reporteCertificaciones">
    <div class="certificaciones-hero mb-4">
        <div>
            <div class="small fw-bold text-uppercase mb-2" style="color:#62E31D;letter-spacing:.12em">Control ECP · Panel administrativo</div>
            <h2 class="mb-1"><i class="fas fa-certificate me-2" style="color:#39A900"></i>Gestión de certificaciones</h2>
            <p class="mb-0">Consulta el avance de las fichas y registra los momentos completados.</p>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success no-imprimir">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger no-imprimir">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger no-imprimir">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="certificacion-kpi p-3 h-100" style="border-color:rgba(2,132,199,.35);">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="certificacion-kpi-label">Fichas para certificar</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:38px;height:38px;background:#e0f2fe;color:#0284c7;"><i class="fas fa-folder-open"></i></div>
                </div>
                <div class="certificacion-kpi-value" style="color:#0284c7;">{{ number_format($stats['para_certificar']) }}</div>
                <div class="certificacion-kpi-subtitle">Con actividades listas</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="certificacion-kpi p-3 h-100" style="border-color:rgba(22,163,74,.35);">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="certificacion-kpi-label">Fichas certificadas</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:38px;height:38px;background:#dcfce7;color:#16a34a;"><i class="fas fa-certificate"></i></div>
                </div>
                <div class="certificacion-kpi-value" style="color:#16a34a;">{{ number_format($stats['certificadas']) }}</div>
                <div class="certificacion-kpi-subtitle">Con momentos certificados</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="certificacion-kpi p-3 h-100" style="border-color:rgba(220,38,38,.35);">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="certificacion-kpi-label">Fichas pendientes por certificar</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:38px;height:38px;background:#fee2e2;color:#dc2626;"><i class="fas fa-clock"></i></div>
                </div>
                <div class="certificacion-kpi-value" style="color:#dc2626;">{{ number_format($stats['pendientes']) }}</div>
                <div class="certificacion-kpi-subtitle">Con actividades en progreso</div>
            </div>
        </div>
    </div>

    <div class="certificacion-filtros-card mb-4 no-imprimir">
            <form method="GET" action="{{ route('controlecp.administrador.certificados') }}" class="certificacion-filtros">
                <div class="certificacion-filtro-campo"><label for="filtroFicha">Ficha</label><input id="filtroFicha" name="ficha" value="{{ request('ficha') }}" list="fichasCertificacion" placeholder="Número de ficha"><datalist id="fichasCertificacion">@foreach($fichas as $ficha)<option value="{{ $ficha->code }}">@endforeach</datalist></div>
                <div class="certificacion-filtro-campo"><label for="filtroMomento">Momento</label><select id="filtroMomento" name="momento_id"><option value="">Todos</option>@foreach($momentos as $momento)<option value="{{ $momento->id }}" @selected(request('momento_id') == $momento->id)>{{ $momento->nombre }}</option>@endforeach</select></div>
                <div class="certificacion-filtro-campo"><label for="filtroResponsable">Responsable</label><select id="filtroResponsable" name="responsable_id"><option value="">Todos</option>@foreach($responsables as $responsable)<option value="{{ $responsable->id }}" @selected(request('responsable_id') == $responsable->id)>{{ $responsable->nickname ?: $responsable->name }}</option>@endforeach</select></div>
                <div class="certificacion-filtro-campo"><label for="filtroEstado">Estado</label><select id="filtroEstado" name="estado"><option value="">Todos</option>@foreach(['Pendiente de certificar','Certificada','En progreso'] as $estado)<option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ $estado }}</option>@endforeach</select></div>
                <div class="certificacion-filtro-campo"><label for="fechaDesde">Desde</label><input id="fechaDesde" type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"></div>
                <div class="certificacion-filtro-campo"><label for="fechaHasta">Hasta</label><input id="fechaHasta" type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"></div>
                <div class="certificacion-filtro-acciones">
                    <button type="submit" class="btn certificacion-buscar"><i class="fas fa-magnifying-glass me-1"></i>Buscar</button>
                    <a href="{{ route('controlecp.administrador.certificados') }}" class="btn certificacion-restablecer" title="Restablecer filtros" aria-label="Restablecer filtros"><i class="fas fa-rotate-left"></i></a>
                </div>
            </form>
    </div>

    @if($momentos->count() < 3)
        <div class="alert alert-info">
            Hay {{ $momentos->count() }} momentos configurados. Cuando agregues el tercero, aparecerá aquí y deberá certificarse después de los anteriores. La constancia PDF se habilitará al completar los tres momentos.
        </div>
    @endif

    @if(request()->boolean('reporte'))
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0">Reporte de certificaciones y progreso</h4>
            <button type="button" class="btn btn-success rounded-pill no-imprimir" onclick="window.print()"><i class="fas fa-file-pdf me-1"></i>Guardar como PDF</button>
        </div>
        <p>Resumen de fichas: 1 momento certificado: {{ $stats['uno'] }} · 2 momentos: {{ $stats['dos'] }} · 3 momentos: {{ $stats['tres'] }}.</p>
    @else
        <h5 class="fw-bold mb-3">Fichas y progreso</h5>
    @endif

    <ul class="nav nav-pills p-1 bg-light rounded-pill border mb-3 certificacion-momento-tabs" id="certificadosAdminMomentosTab" role="tablist">
        @foreach($momentos as $momentoTab)
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $loop->first ? 'active' : '' }} rounded-pill px-4"
                        id="tab-admin-momento-{{ $momentoTab->id }}"
                        data-bs-toggle="pill"
                        data-bs-target="#contenido-admin-momento-{{ $momentoTab->id }}"
                        type="button" role="tab"
                        aria-controls="contenido-admin-momento-{{ $momentoTab->id }}"
                        aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                    Momento {{ $momentoTab->orden }}
                </button>
            </li>
        @endforeach
    </ul>

    <div class="tab-content certificacion-momento-tab-content" id="certificadosAdminMomentosContenido">
        @foreach($momentos as $momentoTabla)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                 id="contenido-admin-momento-{{ $momentoTabla->id }}"
                 role="tabpanel"
                 aria-labelledby="tab-admin-momento-{{ $momentoTabla->id }}">
                <section class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header border-0 bg-white p-4">
                        <h5 class="fw-bold mb-0">Momento {{ $momentoTabla->orden }} · {{ $momentoTabla->nombre }}</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Ficha</th><th>Nombre del programa</th><th>Progreso</th><th>Quién certificó</th><th>Estado</th><th class="text-end no-imprimir">Acciones</th></tr>
                            </thead>
                            <tbody>
                            @forelse($registros as $fila)
                                @php
                                    $momentoFila = $fila['momentos']->firstWhere('momento_id', $momentoTabla->id);
                                    $responsablesMomento = $momentoFila['certificaciones']->map(
                                        fn ($certificacion) => $certificacion->nickname . ' (' . ($certificacion->responsable_tipo === 'instructor' ? 'Instructor' : 'Administrador') . ')'
                                    )->unique()->implode(', ');
                                    $estadoClase = in_array($momentoFila['estado'], ['Certificada', 'Certificada por instructor'], true)
                                        ? 'certificada'
                                        : ($momentoFila['estado'] === 'Pendiente de certificar' ? 'pendiente' : 'progreso');
                                @endphp
                                <tr>
                                    <td class="fw-semibold">{{ $fila['ficha'] }}</td>
                                    <td>{{ $fila['programa'] }}</td>
                                    <td style="min-width:180px">
                                        <div class="d-flex justify-content-between small"><span>{{ $momentoFila['completadas'] }}/{{ $momentoFila['total'] }} actividades</span><span>{{ $momentoFila['porcentaje'] }}%</span></div>
                                        <div class="certificacion-progreso mt-1"><span style="width:{{ $momentoFila['porcentaje'] }}%"></span></div>
                                    </td>
                                    <td>{{ $responsablesMomento ?: '—' }}</td>
                                    <td>
                                        <span class="certificacion-estado certificacion-estado--{{ $estadoClase }}">{{ $momentoFila['estado'] }}</span>
                                        @if($momentoFila['bloqueado_por_secuencia'])
                                            <div class="small text-muted mt-1">Primero certifica el momento anterior.</div>
                                        @endif
                                    </td>
                                    <td class="text-end no-imprimir">
                                        <div class="certificacion-row-actions">
                                            <button type="button" class="btn certificacion-row-action certificacion-row-action--view" data-bs-toggle="modal" data-bs-target="#detalleFicha{{ $fila['course_id'] }}" title="Ver progreso" aria-label="Ver progreso"><i class="fas fa-eye"></i></button>
                                            <button type="button" class="btn certificacion-row-action certificacion-row-action--history" data-bs-toggle="modal" data-bs-target="#historialFicha{{ $fila['course_id'] }}" title="Historial" aria-label="Historial"><i class="fas fa-clock-rotate-left"></i></button>
                                            @if($momentoFila['puede_certificar'])
                                                <form method="POST" action="{{ route('controlecp.administrador.certificados.certificar', [$fila['course_id'], $momentoFila['momento_id']]) }}" class="d-inline" onsubmit="return confirm('¿Confirmas certificar el momento {{ $momentoFila['momento'] }}?')">
                                                    @csrf<button class="btn certificacion-row-action certificacion-row-action--certify" title="Certificar {{ $momentoFila['momento'] }}" aria-label="Certificar {{ $momentoFila['momento'] }}"><i class="fas fa-certificate" aria-hidden="true"></i></button>
                                                </form>
                                            @endif
                                            @if($fila['certificado_completo'] && $momentoTabla->id === $momentos->last()->id)
                                                <a class="btn certificacion-row-action certificacion-row-action--certify" href="{{ route('controlecp.administrador.certificados.constancia', $fila['course_id']) }}" title="Descargar constancia PDF" aria-label="Descargar constancia PDF"><i class="fas fa-file-pdf" aria-hidden="true"></i></a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">No hay fichas que coincidan con los filtros.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        @endforeach
    </div>

    @foreach($registros as $fila)
            <div class="modal fade no-imprimir" id="detalleFicha{{ $fila['course_id'] }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title">Progreso · Ficha {{ $fila['ficha'] }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <p><strong>Programa:</strong> {{ $fila['programa'] }} · <strong>Avance general:</strong> {{ $fila['completadas'] }}/{{ $fila['total'] }} ({{ $fila['porcentaje'] }}%)</p>
                        @foreach($fila['momentos'] as $momentoFila)
                            <hr>
                            <h6 class="fw-bold">{{ $momentoFila['momento'] }} · {{ $momentoFila['estado'] }}</h6>
                            <p class="small text-muted">{{ $momentoFila['completadas'] }}/{{ $momentoFila['total'] }} actividades ({{ $momentoFila['porcentaje'] }}%)</p>
                            <h6 class="text-success">Actividades finalizadas</h6>
                            <ul>@forelse($momentoFila['finalizadas'] as $actividad)<li>{{ $actividad->numero }} - {{ $actividad->nombre }}</li>@empty<li>No hay actividades finalizadas.</li>@endforelse</ul>
                            <h6 class="text-warning">Actividades pendientes</h6>
                            <ul>@forelse($momentoFila['pendientes'] as $actividad)<li>{{ $actividad->numero }} - {{ $actividad->nombre }}</li>@empty<li>No hay actividades pendientes.</li>@endforelse</ul>
                        @endforeach
                    </div>
                </div></div>
            </div>
            <div class="modal fade no-imprimir" id="historialFicha{{ $fila['course_id'] }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg"><div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title">Historial · Ficha {{ $fila['ficha'] }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        @php $momentosCertificados = $fila['momentos']->filter(fn ($momentoFila) => $momentoFila['certificaciones']->isNotEmpty()); @endphp
                        @forelse($momentosCertificados as $momentoFila)
                            @foreach($momentoFila['certificaciones'] as $certificacion)
                                <p><strong>{{ $momentoFila['momento'] }} · {{ $certificacion->responsable_tipo === 'instructor' ? 'Instructor' : 'Administrador' }}:</strong> {{ $certificacion->nickname }} certificó el {{ \Carbon\Carbon::parse($certificacion->fecha_certificacion)->format('d/m/Y H:i') }}.</p>
                            @endforeach
                        @empty
                            <p class="text-muted mb-0">Esta ficha aún no tiene momentos certificados.</p>
                        @endforelse
                    </div>
                </div></div>
            </div>
    @endforeach
    @unless(request()->boolean('reporte'))
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-footer bg-white border-0">
                @if ($registros->hasPages())
                    <nav aria-label="Paginación de certificados">
                        <ul class="pagination justify-content-center">
                            @if ($registros->onFirstPage())
                                <li class="page-item disabled"><span class="page-link" aria-hidden="true">&lsaquo;</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $registros->previousPageUrl() }}" aria-label="Página anterior">&lsaquo;</a></li>
                            @endif
                            @php
                                $ultimaPaginaMostrada = 0;
                            @endphp
                            @for ($pagina = 1; $pagina <= $registros->lastPage(); $pagina++)
                                @if ($pagina === 1 || $pagina === $registros->lastPage() || abs($pagina - $registros->currentPage()) <= 1)
                                    @if ($ultimaPaginaMostrada > 0 && $pagina > $ultimaPaginaMostrada + 1)
                                        <li class="page-item disabled"><span class="page-link">…</span></li>
                                    @endif
                                    <li class="page-item {{ $pagina === $registros->currentPage() ? 'active' : '' }}">
                                        <a class="page-link" href="{{ $registros->url($pagina) }}" @if($pagina === $registros->currentPage()) aria-current="page" @endif>{{ $pagina }}</a>
                                    </li>
                                    @php
                                    $ultimaPaginaMostrada = $pagina;
                                @endphp
                                @endif
                            @endfor
                            @if ($registros->hasMorePages())
                                <li class="page-item"><a class="page-link" href="{{ $registros->nextPageUrl() }}" aria-label="Página siguiente">&rsaquo;</a></li>
                            @else
                                <li class="page-item disabled"><span class="page-link" aria-hidden="true">&rsaquo;</span></li>
                            @endif
                        </ul>
                    </nav>
                @endif
            </div>
        </div>
    @endunless
</div>
@endsection
