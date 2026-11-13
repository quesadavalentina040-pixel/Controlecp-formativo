{{-- Vista: Vista del aprendiz para enviar peticiones, quejas o sugerencias. --}}
@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .apr-pqr-header { background:#001A29; border-radius:20px; padding:1.25rem 1.5rem; }
    .apr-pqr-header h1 { color:#fff; }
    .apr-pqr-header p { color:#c7d4da; }
    .apr-pqr-new-request { border-color:#39A900 !important; background:#39A900 !important; color:#fff !important; }
    .apr-pqr-new-request:hover, .apr-pqr-new-request:focus { border-color:#318f00 !important; background:#318f00 !important; color:#fff !important; }
    .apr-pqr-card { background:#fff; border:1px solid #e0ece4; border-radius:16px; box-shadow:0 2px 10px rgba(0,26,41,.05); }
    .apr-pqr-kpi {
        height:100%;
        padding:1rem 1.15rem;
        border:1px solid var(--apr-pqr-kpi-border);
        border-radius:16px;
        background:#fff;
        box-shadow:0 2px 10px rgba(0,26,41,.04);
        transition:transform .2s ease, box-shadow .2s ease;
    }
    .apr-pqr-kpi:hover {
        transform:translateY(-4px);
        box-shadow:0 10px 24px rgba(0,26,41,.12);
    }
    .apr-pqr-kpi-icon {
        display:flex;
        width:36px;
        height:36px;
        align-items:center;
        justify-content:center;
        border-radius:50%;
        background:var(--apr-pqr-kpi-bg);
        color:var(--apr-pqr-kpi-color);
    }
    .apr-pqr-kpi-label { color:#64748b; font-size:.88rem; }
    .apr-pqr-kpi-value { color:var(--apr-pqr-kpi-color); font-size:1.65rem; font-weight:800; line-height:1.1; }
    .apr-pqr-kpi-caption { color:#94a3b8; font-size:.78rem; }
    .apr-pqr-filters {
        display:grid;
        grid-template-columns:minmax(170px,1.15fr) minmax(160px,1fr) repeat(2,minmax(150px,.8fr)) auto;
        align-items:end;
        gap:.75rem;
        padding:1rem 1.15rem;
        border-radius:16px;
        background:#001A29;
        box-shadow:0 5px 20px rgba(0,26,41,.08);
    }
    .apr-pqr-filter-field { min-width:0; }
    .apr-pqr-filter-field label {
        display:block;
        margin-bottom:.3rem;
        color:#fff;
        font-size:.75rem;
        font-weight:700;
    }
    .apr-pqr-filter-field .form-control,
    .apr-pqr-filter-field .form-select {
        height:42px;
        min-width:0;
        border:1px solid #dbe3eb;
        border-radius:10px;
        background:#fff;
        color:#001A29;
        font-size:.88rem;
        box-shadow:none;
    }
    .apr-pqr-filter-actions { display:flex; gap:.55rem; }
    .apr-pqr-filter-actions .btn-search {
        height:42px;
        min-width:110px;
        padding:0 1rem;
        border:0;
        border-radius:10px;
        background:#39A900;
        color:#fff;
        font-weight:700;
        white-space:nowrap;
    }
    .apr-pqr-filter-actions .btn-search:hover { background:#318f00; color:#fff; }
    .apr-pqr-filter-actions .btn-reset {
        display:grid;
        width:44px;
        height:42px;
        flex:0 0 44px;
        place-items:center;
        border:1px solid #dbe3eb;
        border-radius:10px;
        background:#fff;
        color:#001A29;
    }
    .apr-pqr-filter-actions .btn-reset:hover { background:#f1f5f9; }
    .apr-pqr-table thead th {
        padding:.85rem 1rem;
        border-bottom:1px solid #e2e8f0;
        background:#f8fafc;
        color:#001A29;
        font-size:.82rem;
        font-weight:750;
        white-space:nowrap;
    }
    .apr-pqr-table tbody td { padding:.9rem 1rem; border-bottom:1px solid #eef2f5; }
    .apr-pqr-table tbody tr:last-child td { border-bottom:0; }
    .apr-pqr-table tbody tr:hover td { background:#fafbfd; }
    .apr-pqr-subject { display:block; max-width:280px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .apr-pqr-pagination { padding:1rem; border-top:1px solid #eef2f5; background:#fff; }
    .apr-pqr-pagination .pagination { margin:0; }
    .apr-pqr-pagination .page-link {
        display:flex;
        min-width:42px;
        height:42px;
        align-items:center;
        justify-content:center;
        border:1px solid #d5e7db;
        border-right:0;
        background:#fff;
        color:#39A900;
        font-weight:700;
        text-decoration:none;
        box-shadow:none;
    }
    .apr-pqr-pagination .page-item:first-child .page-link { border-radius:8px 0 0 8px; }
    .apr-pqr-pagination .page-item:last-child .page-link { border-right:1px solid #d5e7db; border-radius:0 8px 8px 0; }
    .apr-pqr-pagination .page-item.active .page-link { border-color:#39A900; background:#39A900; color:#fff; }
    .apr-pqr-pagination .page-item:not(.active):not(.disabled) .page-link:hover { background:#f0fdf4; color:#318f00; }
    .apr-pqr-pagination .page-item.disabled .page-link { background:#f8fafc; color:#94a3b8; }
    .apr-pqr-modal .modal-content { border:0; border-radius:16px; overflow:hidden; }
    .apr-pqr-modal .modal-header { color:#fff; background:#001A29; border-bottom:2px solid #39A900; }
    .apr-pqr-detail-modal .modal-content { overflow:hidden; border:0; border-radius:22px; box-shadow:0 24px 70px rgba(0,26,41,.24); }
    .apr-pqr-detail-modal .modal-header {
        padding:1.35rem 1.5rem;
        border:0;
        color:#fff;
        background:linear-gradient(120deg,#001A29 0%,#00324D 100%);
    }
    .apr-pqr-detail-icon {
        display:flex;
        width:46px;
        height:46px;
        flex:0 0 46px;
        align-items:center;
        justify-content:center;
        border:1px solid rgba(147,197,253,.45);
        border-radius:15px;
        background:rgba(37,99,235,.18);
        color:#93c5fd;
        font-size:1.15rem;
    }
    .apr-pqr-detail-kicker { color:#93c5fd; font-size:.75rem; font-weight:700; }
    .apr-pqr-detail-modal .modal-body { padding:1.5rem; background:#f8fafc; }
    .apr-pqr-detail-meta {
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:.8rem;
        margin-bottom:1.1rem;
    }
    .apr-pqr-detail-meta-item { min-width:0; padding:.85rem 1rem; border:1px solid #e5edf2; border-radius:12px; background:#fff; }
    .apr-pqr-detail-meta-label { display:block; margin-bottom:.2rem; color:#64748b; font-size:.7rem; font-weight:700; text-transform:uppercase; }
    .apr-pqr-detail-meta-value { color:#00324d; font-size:.9rem; font-weight:700; overflow-wrap:anywhere; }
    .apr-pqr-detail-section { padding:1rem 1.1rem; border:1px solid #e5edf2; border-radius:14px; background:#fff; }
    .apr-pqr-detail-section + .apr-pqr-detail-section { margin-top:.85rem; }
    .apr-pqr-detail-section-title { display:flex; align-items:center; gap:.55rem; margin-bottom:.55rem; color:#001A29; font-size:.9rem; font-weight:800; }
    .apr-pqr-detail-section-title i { color:#39A900; }
    .apr-pqr-detail-description { margin:0; color:#475569; font-size:.9rem; line-height:1.65; white-space:pre-wrap; overflow-wrap:anywhere; }
    .apr-pqr-detail-response { padding:.9rem 1rem; border:1px solid #dcfce7; border-left:4px solid #39A900; border-radius:12px; background:#f7fdf5; }
    .apr-pqr-detail-response + .apr-pqr-detail-response { margin-top:.65rem; }
    .apr-pqr-detail-empty { margin:0; padding:.8rem; border-radius:10px; background:#f8fafc; color:#64748b; font-size:.85rem; }
    .apr-pqr-detail-modal .modal-footer { padding:.9rem 1.5rem; border-top:1px solid #eef2f5; background:#fff; }
    .apr-pqr-detail-close { border:0; background:#39A900; color:#fff; font-weight:700; }
    .apr-pqr-detail-close:hover { background:#318f00; color:#fff; }
    @media (max-width:520px) {
        .apr-pqr-detail-meta { grid-template-columns:minmax(0,1fr); }
        .apr-pqr-detail-modal .modal-body { padding:1rem; }
    }
    @media (max-width: 900px) {
        .apr-pqr-filters { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .apr-pqr-filter-actions { grid-column:span 2; justify-content:flex-end; }
    }
    @media (max-width: 520px) {
        .apr-pqr-filters { grid-template-columns:minmax(0,1fr); }
        .apr-pqr-filter-actions { grid-column:auto; }
        .apr-pqr-filter-actions .btn-search { flex:1; }
    }
</style>
@endpush

{{-- Contenido principal que organiza la interfaz específica de esta vista. --}}
@section('dashboard-content')
<div class="container-fluid px-3 py-3">

    <div class="apr-pqr-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small fw-semibold mb-1" style="color:#62E31D">Control ECP · Aprendiz</div>
            <h1 class="h3 fw-bold mb-1"><i class="fas fa-comment-dots me-2" style="color:#62E31D"></i>Mis PQRS</h1>
            <p class="small mb-0">Radica solicitudes y consulta su estado y las respuestas.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn apr-pqr-new-request rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#formRadicarPqr">
                <i class="fas fa-plus me-1"></i>Radicar PQRS
            </button>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="apr-pqr-kpi" style="--apr-pqr-kpi-border:#bfdbfe;--apr-pqr-kpi-bg:#e0f2fe;--apr-pqr-kpi-color:#0284c7;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="apr-pqr-kpi-label">Total enviadas</span>
                    <span class="apr-pqr-kpi-icon"><i class="fas fa-inbox" aria-hidden="true"></i></span>
                </div>
                <div class="apr-pqr-kpi-value">{{ $totalPqrs }}</div>
                <div class="apr-pqr-kpi-caption">Solicitudes radicadas por ti</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="apr-pqr-kpi" style="--apr-pqr-kpi-border:#bbf7d0;--apr-pqr-kpi-bg:#dcfce7;--apr-pqr-kpi-color:#16a34a;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="apr-pqr-kpi-label">Respondidas</span>
                    <span class="apr-pqr-kpi-icon"><i class="fas fa-envelope-open-text" aria-hidden="true"></i></span>
                </div>
                <div class="apr-pqr-kpi-value">{{ $pqrsRespondidas }}</div>
                <div class="apr-pqr-kpi-caption">Con respuesta registrada</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="apr-pqr-kpi" style="--apr-pqr-kpi-border:#fecaca;--apr-pqr-kpi-bg:#fee2e2;--apr-pqr-kpi-color:#dc2626;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="apr-pqr-kpi-label">Sin respuesta</span>
                    <span class="apr-pqr-kpi-icon"><i class="fas fa-envelope" aria-hidden="true"></i></span>
                </div>
                <div class="apr-pqr-kpi-value">{{ $pqrsSinRespuesta }}</div>
                <div class="apr-pqr-kpi-caption">Pendientes de respuesta</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="apr-pqr-kpi" style="--apr-pqr-kpi-border:#fde68a;--apr-pqr-kpi-bg:#fef3c7;--apr-pqr-kpi-color:#d97706;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="apr-pqr-kpi-label">En trámite</span>
                    <span class="apr-pqr-kpi-icon"><i class="fas fa-arrows-rotate" aria-hidden="true"></i></span>
                </div>
                <div class="apr-pqr-kpi-value">{{ $pqrsEnTramite }}</div>
                <div class="apr-pqr-kpi-caption">En revisión actualmente</div>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('controlecp.aprendiz.pqr') }}" class="apr-pqr-filters mb-4">
        <div class="apr-pqr-filter-field">
            <label for="filtroTipoPqr"><i class="fas fa-lightbulb me-1" aria-hidden="true"></i>Tipo</label>
            <select class="form-select" id="filtroTipoPqr" name="id_tipo_pqrs">
                <option value="">Todos los tipos</option>
                @foreach($tiposPqrs as $tipo)
                    <option value="{{ $tipo->id_tipo_pqrs }}" @selected(request('id_tipo_pqrs') == $tipo->id_tipo_pqrs)>
                        {{ $tipo->nombre }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="apr-pqr-filter-field">
            <label for="filtroEstadoPqr"><i class="fas fa-filter me-1" aria-hidden="true"></i>Estado</label>
            <select class="form-select" id="filtroEstadoPqr" name="estado">
                <option value="">Todos los estados</option>
                @foreach(['Pendiente', 'En trámite', 'Resuelto', 'Resuelta', 'Cerrado', 'Cerrada'] as $estado)
                    <option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ $estado }}</option>
                @endforeach
            </select>
        </div>
        <div class="apr-pqr-filter-field">
            <label for="filtroFechaDesde"><i class="fas fa-calendar-day me-1" aria-hidden="true"></i>Desde</label>
            <input class="form-control" type="date" id="filtroFechaDesde" name="fecha_desde" value="{{ request('fecha_desde') }}">
        </div>
        <div class="apr-pqr-filter-field">
            <label for="filtroFechaHasta"><i class="fas fa-calendar-check me-1" aria-hidden="true"></i>Hasta</label>
            <input class="form-control" type="date" id="filtroFechaHasta" name="fecha_hasta" value="{{ request('fecha_hasta') }}">
        </div>
        <div class="apr-pqr-filter-actions">
            <button type="submit" class="btn btn-search"><i class="fas fa-search me-1" aria-hidden="true"></i>Buscar</button>
            <a href="{{ route('controlecp.aprendiz.pqr') }}" class="btn btn-reset" title="Limpiar filtros" aria-label="Limpiar filtros">
                <i class="fas fa-rotate-left" aria-hidden="true"></i>
            </a>
        </div>
    </form>

    <div class="modal fade apr-pqr-modal" id="formRadicarPqr" tabindex="-1" aria-labelledby="formRadicarPqrLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
        <form method="POST" action="{{ route('controlecp.aprendiz.pqr.store') }}">
            @csrf
            <div class="modal-header">
                <div><h2 class="modal-title h5 fw-bold mb-1" id="formRadicarPqrLabel">Radicar PQRS</h2><p class="small mb-0" style="color:#c7d4da">Completa los datos de tu solicitud.</p></div>
                <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body row g-3 p-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="tipoPqr">Tipo de PQRS *</label>
                    <select id="tipoPqr" name="id_tipo_pqrs" class="form-select" required>
                        <option value="">Selecciona el tipo</option>
                        @foreach($tiposPqrs as $tipo)<option value="{{ $tipo->id_tipo_pqrs }}" @selected(old('id_tipo_pqrs') == $tipo->id_tipo_pqrs)>{{ $tipo->nombre }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="instructorDestino">Dirigir a (opcional)</label>
                    <select id="instructorDestino" name="id_usuario_instructor_destino" class="form-select">
                        <option value="">Administración general</option>
                        @foreach($instructores as $instructor)<option value="{{ $instructor->id }}" @selected(old('id_usuario_instructor_destino') == $instructor->id)>{{ $instructor->nickname ?: $instructor->email }}</option>@endforeach
                    </select>
                    <div class="form-text">Opcional. Si no eliges instructor, la solicitud se envía a administración.</div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold" for="asuntoPqr">Asunto *</label>
                    <input id="asuntoPqr" name="asunto" class="form-control" minlength="10" maxlength="200" value="{{ old('asunto') }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold" for="descripcionPqr">Descripción * <span class="text-muted fw-normal">(mínimo 10 caracteres)</span></label>
                    <textarea id="descripcionPqr" name="descripcion" class="form-control" rows="5" minlength="10" maxlength="200" required>{{ old('descripcion') }}</textarea>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button class="btn btn-outline-secondary rounded-pill" type="button" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-success rounded-pill px-4" type="submit"><i class="fas fa-paper-plane me-1"></i>Guardar PQRS</button>
            </div>
        </form>
        </div></div>
    </div>

    <div class="apr-pqr-card overflow-hidden">
        <div class="p-4 border-bottom"><h2 class="h5 fw-bold mb-0">Historial de solicitudes</h2></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 apr-pqr-table">
                <thead class="table-light"><tr><th class="ps-4">Radicado</th><th>Tipo</th><th>Asunto</th><th>Fecha</th><th>Estado</th><th class="text-end pe-4">Detalle</th></tr></thead>
                <tbody>
                @forelse($pqrs as $pqr)
                    @php
                        $estadoBadge = $pqr->estado_badge;
                        $tipoBadge = $pqr->tipo_badge;
                    @endphp
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $pqr->radicado }}</td>
                        <td>
                            <span class="badge rounded-pill px-2 py-1 border fw-semibold"
                                  style="background:{{ $tipoBadge['bg'] }};color:{{ $tipoBadge['text'] }};border-color:{{ $tipoBadge['border'] }} !important;font-size:.74rem;">
                                <i class="{{ $tipoBadge['icon'] }} me-1" aria-hidden="true"></i>{{ $pqr->tipo_nombre }}
                            </span>
                        </td>
                        <td><span class="apr-pqr-subject" title="{{ $pqr->asunto }}">{{ $pqr->asunto }}</span></td>
                        <td>{{ $pqr->fecha_radicacion?->copy()->timezone('America/Bogota')->format('d/m/Y g:i a') ?? '—' }}</td>
                        <td>
                            <span class="badge rounded-pill px-3 py-1 border fw-semibold"
                                  style="background:{{ $estadoBadge['bg'] }};color:{{ $estadoBadge['text'] }};border-color:{{ $estadoBadge['border'] }} !important;font-size:.76rem;">
                                <i class="{{ $estadoBadge['icon'] }} me-1" aria-hidden="true"></i>{{ $estadoBadge['label'] }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-outline-primary rounded-circle" type="button" data-bs-toggle="modal" data-bs-target="#detallePqr{{ $pqr->id }}" aria-label="Ver detalle de {{ $pqr->radicado }}" title="Ver detalle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5">Aún no has radicado solicitudes PQRS.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($pqrs->hasPages())
            <div class="apr-pqr-pagination">
                <nav aria-label="Paginación de PQRS">
                    <ul class="pagination justify-content-center">
                        @if ($pqrs->onFirstPage())
                            <li class="page-item disabled"><span class="page-link" aria-hidden="true">&lsaquo;</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $pqrs->previousPageUrl() }}" aria-label="Página anterior">&lsaquo;</a></li>
                        @endif
                        @php
                                $ultimaPaginaMostrada = 0;
                            @endphp
                        @for ($pagina = 1; $pagina <= $pqrs->lastPage(); $pagina++)
                            @if ($pagina === 1 || $pagina === $pqrs->lastPage() || abs($pagina - $pqrs->currentPage()) <= 1)
                                @if ($ultimaPaginaMostrada > 0 && $pagina > $ultimaPaginaMostrada + 1)
                                    <li class="page-item disabled"><span class="page-link">…</span></li>
                                @endif
                                <li class="page-item {{ $pagina === $pqrs->currentPage() ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $pqrs->url($pagina) }}" @if($pagina === $pqrs->currentPage()) aria-current="page" @endif>{{ $pagina }}</a>
                                </li>
                                @php
                                    $ultimaPaginaMostrada = $pagina;
                                @endphp
                            @endif
                        @endfor
                        @if ($pqrs->hasMorePages())
                            <li class="page-item"><a class="page-link" href="{{ $pqrs->nextPageUrl() }}" aria-label="Página siguiente">&rsaquo;</a></li>
                        @else
                            <li class="page-item disabled"><span class="page-link" aria-hidden="true">&rsaquo;</span></li>
                        @endif
                    </ul>
                </nav>
            </div>
        @endif
    </div>

    @foreach($pqrs as $pqr)
        @php
            $badge = $pqr->estado_badge;
        @endphp
        <div class="modal fade apr-pqr-detail-modal" id="detallePqr{{ $pqr->id }}" tabindex="-1"
             aria-labelledby="detallePqrLabel{{ $pqr->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="d-flex align-items-center gap-3">
                            <span class="apr-pqr-detail-icon"><i class="fas fa-eye" aria-hidden="true"></i></span>
                            <div class="min-w-0">
                                <div class="apr-pqr-detail-kicker mb-1">Detalle de solicitud · {{ $pqr->radicado }}</div>
                                <h2 class="modal-title h5 fw-bold mb-0 text-break" id="detallePqrLabel{{ $pqr->id }}">{{ $pqr->asunto }}</h2>
                            </div>
                        </div>
                        <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="apr-pqr-detail-meta">
                            <div class="apr-pqr-detail-meta-item">
                                <span class="apr-pqr-detail-meta-label">Tipo</span>
                                <span class="badge rounded-pill px-2 py-1 border fw-semibold"
                                      style="background:{{ $pqr->tipo_badge['bg'] }};color:{{ $pqr->tipo_badge['text'] }};border-color:{{ $pqr->tipo_badge['border'] }} !important;font-size:.76rem;">
                                    <i class="{{ $pqr->tipo_badge['icon'] }} me-1" aria-hidden="true"></i>{{ $pqr->tipo_nombre }}
                                </span>
                            </div>
                            <div class="apr-pqr-detail-meta-item">
                                <span class="apr-pqr-detail-meta-label">Fecha de radicación</span>
                                <span class="apr-pqr-detail-meta-value">{{ $pqr->fecha_radicacion?->copy()->timezone('America/Bogota')->format('d/m/Y g:i a') ?? '—' }}</span>
                            </div>
                            <div class="apr-pqr-detail-meta-item">
                                <span class="apr-pqr-detail-meta-label">Estado</span>
                                <span class="badge rounded-pill px-3 py-1 border fw-semibold"
                                      style="background:{{ $badge['bg'] }};color:{{ $badge['text'] }};border-color:{{ $badge['border'] }} !important;font-size:.76rem;">
                                    <i class="{{ $badge['icon'] }} me-1" aria-hidden="true"></i>{{ $badge['label'] }}
                                </span>
                            </div>
                            <div class="apr-pqr-detail-meta-item">
                                <span class="apr-pqr-detail-meta-label">Dirigida a</span>
                                <span class="apr-pqr-detail-meta-value">{{ $pqr->instructorDestino?->full_name ?? $pqr->instructorDestino?->nickname ?? 'Administración general' }}</span>
                            </div>
                        </div>
                        <section class="apr-pqr-detail-section">
                            <h3 class="apr-pqr-detail-section-title"><i class="fas fa-align-left" aria-hidden="true"></i>Descripción</h3>
                            <p class="apr-pqr-detail-description">{{ $pqr->descripcion }}</p>
                        </section>
                        <section class="apr-pqr-detail-section">
                            <h3 class="apr-pqr-detail-section-title"><i class="fas fa-comments" aria-hidden="true"></i>Respuestas</h3>
                            @forelse($pqr->respuestas as $respuesta)
                                <article class="apr-pqr-detail-response">
                                    <div class="d-flex flex-wrap justify-content-between gap-2 small text-muted mb-2">
                                        <strong class="text-dark">{{ $respuesta->usuario?->full_name ?? $respuesta->usuario?->nickname ?? 'Equipo ECP' }}</strong>
                                        <time>{{ $respuesta->fecha?->copy()->timezone('America/Bogota')->format('d/m/Y g:i a') ?? '—' }}</time>
                                    </div>
                                    <div class="apr-pqr-detail-description">{{ $respuesta->respuesta }}</div>
                                </article>
                            @empty
                                <p class="apr-pqr-detail-empty">Aún no hay respuesta. Te avisaremos cuando haya una actualización.</p>
                            @endforelse
                        </section>
                    </div>
                    <div class="modal-footer">
                        <button class="btn apr-pqr-detail-close rounded-pill px-4" type="button" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection

@if($errors->any())
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('formRadicarPqr')).show();
        });
    </script>
    @endpush
@endif
