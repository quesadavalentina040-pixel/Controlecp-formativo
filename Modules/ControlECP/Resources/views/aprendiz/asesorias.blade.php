@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .apr-asesorias-kpi { min-height:156px; padding:1.25rem 1.5rem; border:1px solid; border-radius:16px; background:#fff; box-shadow:0 2px 10px rgba(0,26,41,.04); }
    .apr-asesorias-kpi-label { color:#475569; font-size:1.05rem; }
    .apr-asesorias-kpi-value { margin-top:.35rem; font-size:2.2rem; font-weight:800; line-height:1.1; }
    .apr-asesorias-kpi-subtitle { color:#64748b; font-size:.9rem; }
    .apr-asesorias-kpi-icon { display:flex; width:48px; height:48px; align-items:center; justify-content:center; border-radius:50%; font-size:1.15rem; }
    .apr-asesorias-status { display:inline-flex; min-width:96px; align-items:center; justify-content:center; gap:.35rem; padding:.28rem .7rem; border:1px solid transparent; border-radius:999px; font-size:.72rem; font-weight:800; white-space:nowrap; }
    .apr-asesorias-status--done { border-color:#a9dfbf; background:#e1f4e9; color:#16804a; }
    .apr-asesorias-status--cancelled { border-color:#ffc9c9; background:#fff1f1; color:#dc2626; }
    .apr-asesorias-status--requested { border-color:#f5c542; background:#fff4d6; color:#974500; }
    .apr-asesorias-status--scheduled { border-color:#bae6fd; background:#e0f2fe; color:#0369a1; }
    .apr-asesorias-status--rejected { border-color:#ffc9c9; background:#fff1f1; color:#dc2626; }
    .apr-asesorias-cancel-action { border:1px solid #ffc9c9; background:#fff1f1; color:#dc2626; }
    .apr-asesorias-cancel-action:hover,
    .apr-asesorias-cancel-action:focus { border-color:#fca5a5; background:#fee2e2; color:#b91c1c; }
    .apr-asesorias-filter { padding:1rem; border-radius:16px; background:#001A29; }
    .apr-asesorias-filter-grid { display:grid; grid-template-columns:minmax(135px,1.05fr) minmax(180px,1.5fr) minmax(180px,1.5fr) minmax(135px,1fr) minmax(120px,.85fr) 42px; align-items:end; gap:.75rem; }
    .apr-asesorias-filter .form-label { color:#fff; font-size:.76rem; font-weight:700; }
    .apr-asesorias-filter .form-control, .apr-asesorias-filter .form-select { min-height:40px; border:1px solid #dce5eb; border-radius:9px; color:#001A29; }
    .apr-asesorias-filter .btn-search { min-height:40px; border:1px solid #39A900; border-radius:9px; background:#39A900; color:#fff; font-weight:700; }
    .apr-asesorias-filter .btn-search:hover { border-color:#318f00; background:#318f00; color:#fff; }
    .apr-asesorias-filter .btn-reset { width:42px; min-height:40px; border:1px solid #dce5eb; border-radius:9px; background:#fff; color:#001A29; }
    .apr-asesorias-new-request { border-color:#39A900 !important; background:#39A900 !important; color:#fff !important; }
    .apr-asesorias-new-request:hover, .apr-asesorias-new-request:focus { border-color:#318f00 !important; background:#318f00 !important; color:#fff !important; }
    .apr-asesoria-detail-modal .modal-content { overflow:hidden; border:0; border-radius:20px; box-shadow:0 24px 70px rgba(0,26,41,.24); }
    .apr-asesoria-detail-modal .modal-header { position:relative; padding:1.35rem 1.5rem; border:0; color:#fff; background:linear-gradient(120deg,#001A29,#00324D) !important; background-image:linear-gradient(120deg,#001A29,#00324D) !important; }
    .apr-asesoria-detail-modal .modal-header::after { position:absolute; right:0; bottom:0; left:0; height:4px; background:#62E31D; content:""; }
    .apr-asesoria-detail-modal .modal-header .btn-close { filter:brightness(0) invert(1); opacity:.8; }
    .apr-asesoria-detail-modal .modal-header .btn-close:hover { opacity:1; }
    .apr-asesoria-detail-eyebrow { color:#d5f5e5; font-size:.68rem; font-weight:800; letter-spacing:.1em; text-transform:uppercase; }
    .apr-asesoria-detail-modal .modal-body { padding:1.5rem; background:#f8fafc; }
    .apr-asesoria-detail-summary { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.8rem; margin-bottom:1.1rem; }
    .apr-asesoria-detail-meta { padding:.85rem 1rem; border:1px solid #e5edf2; border-radius:12px; background:#fff; }
    .apr-asesoria-detail-meta-label { display:block; margin-bottom:.2rem; color:#64748b; font-size:.7rem; font-weight:700; text-transform:uppercase; }
    .apr-asesoria-detail-meta-value { color:#1e293b; font-size:.88rem; font-weight:700; }
    .apr-asesoria-detail-section { padding:1rem 1.1rem; border:1px solid #e5edf2; border-radius:14px; background:#fff; }
    .apr-asesoria-detail-section + .apr-asesoria-detail-section { margin-top:.85rem; }
    .apr-asesoria-detail-section-title { display:flex; align-items:center; gap:.55rem; margin-bottom:.55rem; color:#001A29; font-size:.9rem; font-weight:800; }
    .apr-asesoria-detail-section-title i { color:#39A900; }
    .apr-asesoria-detail-motive { margin:0; color:#475569; font-size:.88rem; line-height:1.6; overflow-wrap:anywhere; }
    .apr-asesoria-detail-status { display:inline-flex; align-items:center; gap:.4rem; padding:.35rem .7rem; border:1px solid transparent; border-radius:999px; font-size:.76rem; font-weight:800; }
    .apr-asesoria-detail-status--done { border-color:#a9dfbf; background:#e1f4e9; color:#16804a; }
    .apr-asesoria-detail-status--cancelled { border-color:#f7cccc; background:#fff0f0; color:#d63939; }
    .apr-asesoria-detail-status--pending { border-color:#fde68a; background:#fef3c7; color:#a16207; }
    .apr-asesoria-detail-status--scheduled { border-color:#bae6fd; background:#e0f2fe; color:#0369a1; }
    .apr-asesoria-detail-timeline { display:grid; gap:.85rem; }
    .apr-asesoria-detail-event { position:relative; padding:0 0 .85rem 1.65rem; border-bottom:1px solid #eef2f5; }
    .apr-asesoria-detail-event:last-child { padding-bottom:0; border-bottom:0; }
    .apr-asesoria-detail-event::before { position:absolute; top:.2rem; left:.15rem; width:.75rem; height:.75rem; border:2px solid #39A900; border-radius:50%; background:#e9f8e4; content:""; }
    .apr-asesoria-detail-event-title { color:#1e293b; font-size:.84rem; font-weight:800; }
    .apr-asesoria-detail-event-date, .apr-asesoria-detail-event-description { color:#64748b; font-size:.76rem; line-height:1.5; }
    .apr-asesoria-detail-empty { padding:.85rem; border-radius:10px; background:#f8fafc; color:#64748b; font-size:.82rem; }
    .apr-asesoria-detail-modal .modal-footer { padding:.9rem 1.5rem; border-top:1px solid #eef2f5; background:#fff; }
    .apr-asesoria-detail-close { border:0; background:#39A900 !important; color:#fff; font-weight:700; }
    .apr-asesoria-detail-close:hover { background:#318f00 !important; color:#fff; }
    .apr-asesoria-request-modal .modal-content { overflow:hidden; border:0; border-radius:20px; box-shadow:0 24px 70px rgba(0,26,41,.25); }
    .apr-asesoria-request-modal .modal-content > form { display:flex; flex:1 1 auto; flex-direction:column; min-height:0; }
    .apr-asesoria-request-modal .modal-content > form .modal-body { flex:1 1 auto; min-height:0; overflow-y:auto; }
    .apr-asesoria-request-modal .modal-content > form .modal-footer { flex:0 0 auto; }
    .apr-asesoria-request-modal .modal-header { position:relative; padding:1.35rem 1.5rem; border:0; color:#fff; background:linear-gradient(120deg,#001A29,#00324D) !important; }
    .apr-asesoria-request-modal .modal-header::after { position:absolute; right:0; bottom:0; left:0; height:4px; background:#39A900; content:""; }
    .apr-asesoria-request-modal .modal-header .btn-close { filter:brightness(0) invert(1); opacity:.85; }
    .apr-asesoria-request-modal .modal-header .btn-close:hover { opacity:1; }
    .apr-asesoria-request-kicker { color:#c7d4da; font-size:.68rem; font-weight:800; letter-spacing:.1em; text-transform:uppercase; }
    .apr-asesoria-request-modal .modal-body { padding:1.5rem; background:#f7faf8; }
    .apr-asesoria-request-modal .form-label { margin-bottom:.4rem; color:#334155; font-size:.82rem; font-weight:750; }
    .apr-asesoria-request-modal .form-control, .apr-asesoria-request-modal .form-select { min-height:44px; border:1px solid #d8e3dc; border-radius:10px; background-color:#fff; color:#1e293b; }
    .apr-asesoria-request-modal textarea.form-control { min-height:108px; resize:vertical; }
    .apr-asesoria-request-modal .form-control:focus, .apr-asesoria-request-modal .form-select:focus { border-color:#39A900; box-shadow:0 0 0 .2rem rgba(57,169,0,.14); }
    .apr-asesoria-request-availability { display:flex; flex-wrap:wrap; align-items:center; gap:.65rem; }
    .apr-asesoria-request-availability .btn { min-height:42px; border-color:#39A900; border-radius:10px; color:#318f00; font-weight:700; }
    .apr-asesoria-request-availability .btn:hover { background:#39A900; color:#fff; }
    .apr-asesoria-request-search,
    .apr-asesoria-request-search:disabled { border:1px solid #39A900 !important; background:#39A900 !important; color:#fff !important; opacity:1; font-weight:750; }
    .apr-asesoria-request-search:hover:not(:disabled),
    .apr-asesoria-request-search:focus-visible { border-color:#318f00 !important; background:#318f00 !important; color:#fff !important; }
    .apr-asesoria-request-availability-status { color:#64748b; font-size:.78rem; }
    .apr-asesoria-request-availability-status.text-success { color:#278344 !important; }
    .apr-asesoria-request-availability-status.text-danger { color:#dc3545 !important; }
    .apr-asesoria-calendar { padding:1rem; border:1px solid #e1ebe4; border-radius:14px; background:#fff; }
    .apr-asesoria-calendar-toolbar { display:flex; align-items:center; justify-content:space-between; gap:.75rem; margin-bottom:.8rem; }
    .apr-asesoria-calendar-toolbar button { width:36px; height:36px; border:1px solid #d8e3dc; border-radius:9px; background:#fff; color:#001A29; }
    .apr-asesoria-calendar-toolbar button:disabled { opacity:.4; cursor:not-allowed; }
    .apr-asesoria-calendar-grid { display:grid; grid-template-columns:repeat(7,minmax(0,1fr)); gap:.35rem; }
    .apr-asesoria-calendar-weekday { padding:.3rem 0; color:#64748b; font-size:.68rem; font-weight:800; text-align:center; }
    .apr-asesoria-calendar-day { min-height:38px; border:1px solid #e2e5e9; border-radius:9px; background:#eef0f2; color:#8a929b; font-size:.8rem; }
    button.apr-asesoria-calendar-day:not(:disabled) { border-color:#b9dfc2; background:#eef9f0; color:#206b33; font-weight:750; cursor:pointer; }
    button.apr-asesoria-calendar-day:not(:disabled):hover,
    button.apr-asesoria-calendar-day:not(:disabled).is-selected { border-color:#39A900; background:#39A900; color:#fff; }
    .apr-asesoria-calendar-day:disabled { cursor:not-allowed; opacity:1; }
    .apr-asesoria-calendar-day.is-outside { border-color:#d9e3ea; background:#eaf1f5!important; color:#82909b!important; }
    .apr-asesoria-calendar-day.is-nonworking,
    button.apr-asesoria-calendar-day.is-nonworking { border-color:#c8d3dd; background:#e8eef3; color:#718397; }
    .apr-asesoria-calendar-day.is-busy,
    button.apr-asesoria-calendar-day.is-busy { border-color:#f1b8b8; background:#fde8e8; color:#c62828; }
    .apr-asesoria-calendar-day.is-busy:disabled { opacity:1; }
    button.apr-asesoria-calendar-day.is-busy:not(:disabled):hover { border-color:#dc3545; background:#f8d7da; color:#a61e2d; }
    button.apr-asesoria-calendar-day.is-nonworking:disabled { opacity:1; }
    .apr-asesoria-calendar-legend { display:flex; flex-wrap:wrap; gap:.9rem; margin-top:.8rem; color:#64748b; font-size:.73rem; }
    .apr-asesoria-calendar-legend-item { display:inline-flex; align-items:center; gap:.35rem; }
    .apr-asesoria-calendar-legend-color { width:12px; height:12px; border:1px solid; border-radius:4px; }
    .apr-asesoria-calendar-note { margin-top:.8rem; color:#64748b; font-size:.78rem; }
    .apr-asesoria-calendar-slots { display:flex; flex-wrap:wrap; gap:.4rem; margin-top:.6rem; }
    .apr-asesoria-calendar-slot { padding:.3rem .55rem; border:1px solid #d8e3dc; border-radius:999px; background:#f7faf8; color:#334155; font-size:.74rem; font-weight:700; }
    .apr-asesoria-request-modal .modal-footer { padding:1rem 1.5rem; border-top:1px solid #e8eee9; background:#fff; }
    .apr-asesoria-request-cancel { border:1px solid #d5ded8; color:#475569; font-weight:700; }
    .apr-asesoria-request-submit { min-width:170px; border:1px solid #39A900 !important; background:#39A900 !important; color:#fff !important; font-weight:750; }
    .apr-asesoria-request-submit:hover:not(:disabled) { border-color:#318f00 !important; background:#318f00 !important; }
    .apr-asesoria-request-submit:disabled { border-color:#b9d5ad !important; background:#b9d5ad !important; color:#fff !important; opacity:.8; }
    @media (max-width:991px) {
        .apr-asesorias-filter-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .apr-asesorias-filter-reset { justify-self:start; }
    }
    @media (max-width:575px) {
        .apr-asesorias-filter-grid { grid-template-columns:minmax(0,1fr); }
        .apr-asesoria-detail-modal .modal-body { padding:1rem; }
        .apr-asesoria-detail-summary { grid-template-columns:minmax(0,1fr); gap:.55rem; }
        .apr-asesoria-request-modal .modal-body { padding:1rem; }
        .apr-asesoria-request-modal .modal-footer { padding:1rem; }
        .apr-asesoria-request-submit { min-width:0; }
    }
    @media (max-width:767px) {
        .apr-asesorias-kpi { min-height:140px; padding:1.1rem 1.25rem; }
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-3 py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 p-4 mb-4 text-white" style="background:linear-gradient(120deg,#001A29,#00324D);border-radius:20px">
        <div>
            <div class="small fw-bold text-uppercase mb-2" style="color:#62E31D;letter-spacing:.1em">Control ECP · Aprendiz</div>
            <h1 class="h3 fw-bold mb-1"><i class="fas fa-comments me-2" style="color:#62E31D"></i>Mis asesorías</h1>
            <p class="mb-0 text-white-50">Solicita una asesoría, consulta los cambios y cancela las que aún no se hayan realizado.</p>
        </div>
        <button class="btn apr-asesorias-new-request rounded-pill px-4 fw-bold" type="button" data-bs-toggle="modal" data-bs-target="#nuevaSolicitud">
            <i class="fas fa-plus me-1"></i>Nueva solicitud
        </button>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="apr-asesorias-kpi h-100" style="border-color:rgba(2,132,199,.35)">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="apr-asesorias-kpi-label">Total de solicitudes</span>
                    <span class="apr-asesorias-kpi-icon" style="background:#e0f2fe;color:#0284c7"><i class="fas fa-paper-plane"></i></span>
                </div>
                <div class="apr-asesorias-kpi-value" style="color:#0284c7">{{ $totales['total'] }}</div>
                <div class="apr-asesorias-kpi-subtitle">Solicitudes registradas en tu cuenta</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="apr-asesorias-kpi h-100" style="border-color:rgba(22,163,74,.35)">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="apr-asesorias-kpi-label">Aceptadas</span>
                    <span class="apr-asesorias-kpi-icon" style="background:#dcfce7;color:#16a34a"><i class="fas fa-check"></i></span>
                </div>
                <div class="apr-asesorias-kpi-value" style="color:#16a34a">{{ $totales['aceptadas'] }}</div>
                <div class="apr-asesorias-kpi-subtitle">Programadas o realizadas</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="apr-asesorias-kpi h-100" style="border-color:rgba(220,38,38,.35)">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="apr-asesorias-kpi-label">Rechazadas</span>
                    <span class="apr-asesorias-kpi-icon" style="background:#fee2e2;color:#dc2626"><i class="fas fa-times"></i></span>
                </div>
                <div class="apr-asesorias-kpi-value" style="color:#dc2626">{{ $totales['rechazadas'] }}</div>
                <div class="apr-asesorias-kpi-subtitle">No aceptadas por el instructor</div>
            </div>
        </div>
    </div>

    <div>
    <form method="GET" action="{{ route('controlecp.aprendiz.asesorias') }}" class="apr-asesorias-filter mb-4">
        <div class="apr-asesorias-filter-grid">
            <div>
                <label class="form-label mb-1" for="filtroFechaAsesoria">Fecha</label>
                <input class="form-control" type="date" name="fecha" id="filtroFechaAsesoria" value="{{ $filtros['fecha'] ?? '' }}">
            </div>
            <div>
                <label class="form-label mb-1" for="filtroAsesoria">Asesoría</label>
                <input class="form-control" type="search" name="asesoria" id="filtroAsesoria" value="{{ $filtros['asesoria'] ?? '' }}" placeholder="Motivo ">
            </div>
            <div>
                <label class="form-label mb-1" for="filtroCategoriaAsesoria">Categoría</label>
                <select class="form-select" name="categoria_id" id="filtroCategoriaAsesoria">
                    <option value="">Todas</option>
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria->id_categoria_asesoria }}" @selected(($filtros['categoria_id'] ?? '') == $categoria->id_categoria_asesoria)>{{ $categoria->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label mb-1" for="filtroEstadoAsesoria">Estado</label>
                <select class="form-select" name="estado" id="filtroEstadoAsesoria">
                    <option value="">Todos</option>
                    @foreach(['Solicitada', 'Programada', 'Realizada', 'Cancelada', 'Rechazada'] as $estado)
                        <option value="{{ $estado }}" @selected(($filtros['estado'] ?? '') === $estado)>{{ $estado }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-grid">
                <button class="btn btn-search text-nowrap" type="submit">Buscar</button>
            </div>
            <div class="apr-asesorias-filter-reset d-grid">
                <a class="btn btn-reset" href="{{ route('controlecp.aprendiz.asesorias') }}" aria-label="Restablecer filtros" title="Restablecer filtros"><i class="fas fa-rotate-left"></i></a>
            </div>
        </div>
    </form>

    <section class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 p-4"><h2 class="h5 fw-bold mb-0">Historial de solicitudes</h2></div>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th class="ps-4">Fecha y hora</th><th>Categoría</th><th>Instructor</th><th>Estado</th><th>Motivo</th><th class="text-end pe-4">Acciones</th></tr></thead>
            <tbody>
            @forelse($asesorias as $asesoria)
                <tr>
                    <td class="ps-4">{{ $asesoria->fecha?->format('d/m/Y') }}<div class="small text-muted">{{ substr($asesoria->hora_inicio,0,5) }}–{{ substr($asesoria->hora_fin,0,5) }}</div></td>
                    <td>{{ $asesoria->categoria?->nombre ?? '—' }}</td>
                    <td>{{ $asesoria->instructor?->person?->full_name ?? $asesoria->instructor?->nickname ?? 'Pendiente de asignación' }}<div class="small text-muted">{{ $asesoria->instructor?->email }}</div></td>
                    <td>
                        <span class="apr-asesorias-status @if($asesoria->estado === 'Realizada') apr-asesorias-status--done @elseif($asesoria->estado === 'Solicitada') apr-asesorias-status--requested @elseif($asesoria->estado === 'Programada') apr-asesorias-status--scheduled @elseif($asesoria->estado === 'Rechazada') apr-asesorias-status--rejected @else apr-asesorias-status--cancelled @endif">
                            <i class="fas @if($asesoria->estado === 'Realizada') fa-circle-check @elseif($asesoria->estado === 'Solicitada') fa-clock @elseif($asesoria->estado === 'Programada') fa-calendar-check @elseif($asesoria->estado === 'Rechazada') fa-circle-xmark @else fa-ban @endif" aria-hidden="true"></i>
                            {{ $asesoria->estado }}
                        </span>
                    </td>
                    <td>{{ \Illuminate\Support\Str::limit($asesoria->motivo, 70) }}</td>
                    <td class="text-end pe-4 text-nowrap">
                        <button class="btn btn-sm rounded-circle" style="width:36px;height:36px;padding:0;border:1px solid #d8c5f5;background:#f3ebff;color:#7e3fc7" type="button" data-bs-toggle="modal" data-bs-target="#detalle{{ $asesoria->id_asesoria }}" title="Ver cambios" aria-label="Ver cambios"><i class="fas fa-eye"></i></button>
                        @if(in_array($asesoria->estado, ['Solicitada', 'Programada'], true))
                            <form method="POST" action="{{ route('controlecp.aprendiz.asesorias.cancelar', $asesoria->id_asesoria) }}" class="d-inline" onsubmit="return confirm('¿Confirmas que deseas cancelar esta asesoría? El horario quedará disponible para el instructor.');">
                                @csrf
                                @method('PUT')
                                <button class="btn btn-sm apr-asesorias-cancel-action rounded-pill ms-1" type="submit"><i class="fas fa-circle-xmark me-1" aria-hidden="true"></i>Cancelar</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-5">Aún no tienes solicitudes de asesoría.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        @foreach($asesorias as $asesoria)
            <div class="modal fade apr-asesoria-detail-modal" id="detalle{{ $asesoria->id_asesoria }}" tabindex="-1" aria-labelledby="detalleTitulo{{ $asesoria->id_asesoria }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <div class="apr-asesoria-detail-eyebrow mb-1">Control ECP · Aprendiz</div>
                                <h2 class="modal-title h5 fw-bold mb-0" id="detalleTitulo{{ $asesoria->id_asesoria }}">Detalle de asesoría</h2>
                            </div>
                            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="apr-asesoria-detail-summary">
                                <div class="apr-asesoria-detail-meta">
                                    <span class="apr-asesoria-detail-meta-label">Fecha y horario</span>
                                    <span class="apr-asesoria-detail-meta-value"><i class="far fa-calendar me-1 text-primary"></i>{{ $asesoria->fecha?->format('d/m/Y') }} · {{ substr($asesoria->hora_inicio, 0, 5) }}–{{ substr($asesoria->hora_fin, 0, 5) }}</span>
                                </div>
                                <div class="apr-asesoria-detail-meta">
                                    <span class="apr-asesoria-detail-meta-label">Categoría</span>
                                    <span class="apr-asesoria-detail-meta-value">{{ $asesoria->categoria?->nombre ?? 'Sin categoría' }}</span>
                                </div>
                                <div class="apr-asesoria-detail-meta">
                                    <span class="apr-asesoria-detail-meta-label">Instructor</span>
                                    <span class="apr-asesoria-detail-meta-value">{{ $asesoria->instructor?->person?->full_name ?? $asesoria->instructor?->nickname ?? 'Pendiente de asignación' }}</span>
                                </div>
                                <div class="apr-asesoria-detail-meta">
                                    <span class="apr-asesoria-detail-meta-label">Estado actual</span>
                                    <span class="apr-asesoria-detail-status @if($asesoria->estado === 'Realizada') apr-asesoria-detail-status--done @elseif(in_array($asesoria->estado, ['Cancelada', 'Rechazada'], true)) apr-asesoria-detail-status--cancelled @elseif($asesoria->estado === 'Solicitada') apr-asesoria-detail-status--pending @else apr-asesoria-detail-status--scheduled @endif">
                                        <i class="fas @if($asesoria->estado === 'Realizada') fa-check-circle @elseif(in_array($asesoria->estado, ['Cancelada', 'Rechazada'], true)) fa-times-circle @elseif($asesoria->estado === 'Solicitada') fa-clock @else fa-calendar-check @endif"></i>{{ $asesoria->estado }}
                                    </span>
                                </div>
                            </div>
                            <section class="apr-asesoria-detail-section">
                                <h3 class="apr-asesoria-detail-section-title"><i class="fas fa-comment-dots"></i>Motivo de la asesoría</h3>
                                <p class="apr-asesoria-detail-motive">{{ $asesoria->motivo }}</p>
                            </section>
                            <section class="apr-asesoria-detail-section">
                                <h3 class="apr-asesoria-detail-section-title"><i class="fas fa-clock-rotate-left"></i>Historial de cambios</h3>
                                @forelse($asesoria->historial as $evento)
                                    <div class="apr-asesoria-detail-timeline">
                                        <div class="apr-asesoria-detail-event">
                                            <div class="apr-asesoria-detail-event-title">{{ $evento->accion === 'Cancelo' ? 'Asesoría cancelada' : ($evento->accion === 'Acepto' ? 'Solicitud aceptada' : ($evento->accion === 'Rechazo' ? 'Solicitud rechazada' : 'Asesoría reprogramada')) }}</div>
                                            <div class="apr-asesoria-detail-event-date">{{ $evento->fecha?->format('d/m/Y · H:i') }}</div>
                                            @if(in_array($evento->accion, ['Rechazo', 'Cancelo'], true))
                                                <div class="apr-asesoria-detail-event-description mt-1"><strong>Motivo:</strong> {{ $evento->motivo }}</div>
                                            @endif
                                            @if($evento->fecha_anterior)
                                                <div class="apr-asesoria-detail-event-description mt-1"><strong>Fecha anterior:</strong> {{ $evento->fecha_anterior->format('d/m/Y') }}@if($evento->hora_anterior) · {{ substr($evento->hora_anterior, 0, 5) }}@endif</div>
                                            @endif
                                            @if($evento->fecha_nueva)
                                                <div class="apr-asesoria-detail-event-description mt-1"><strong>Nueva fecha:</strong> {{ $evento->fecha_nueva->format('d/m/Y') }}@if($evento->hora_nueva) · {{ substr($evento->hora_nueva, 0, 5) }}@endif</div>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="apr-asesoria-detail-empty"><i class="fas fa-circle-info me-1"></i>Aún no hay cambios registrados para esta asesoría.</div>
                                @endforelse
                            </section>
                        </div>
                        <div class="modal-footer">
                            <button class="btn apr-asesoria-detail-close rounded-pill px-4" type="button" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="card-footer bg-white border-0">
            @if ($asesorias->hasPages())
                <nav aria-label="Paginación de asesorías">
                    <ul class="pagination justify-content-center">
                        @if ($asesorias->onFirstPage())
                            <li class="page-item disabled"><span class="page-link" aria-hidden="true">&lsaquo;</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $asesorias->previousPageUrl() }}" aria-label="Página anterior">&lsaquo;</a></li>
                        @endif
                        @php
                                $ultimaPaginaMostrada = 0;
                            @endphp
                        @for ($pagina = 1; $pagina <= $asesorias->lastPage(); $pagina++)
                            @if ($pagina === 1 || $pagina === $asesorias->lastPage() || abs($pagina - $asesorias->currentPage()) <= 1)
                                @if ($ultimaPaginaMostrada > 0 && $pagina > $ultimaPaginaMostrada + 1)
                                    <li class="page-item disabled"><span class="page-link">…</span></li>
                                @endif
                                <li class="page-item {{ $pagina === $asesorias->currentPage() ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $asesorias->url($pagina) }}" @if($pagina === $asesorias->currentPage()) aria-current="page" @endif>{{ $pagina }}</a>
                                </li>
                                @php
                                    $ultimaPaginaMostrada = $pagina;
                                @endphp
                            @endif
                        @endfor
                        @if ($asesorias->hasMorePages())
                            <li class="page-item"><a class="page-link" href="{{ $asesorias->nextPageUrl() }}" aria-label="Página siguiente">&rsaquo;</a></li>
                        @else
                            <li class="page-item disabled"><span class="page-link" aria-hidden="true">&rsaquo;</span></li>
                        @endif
                    </ul>
                </nav>
            @endif
        </div>
    </section>
    </div>

    <div class="modal fade apr-asesoria-request-modal" id="nuevaSolicitud" tabindex="-1" aria-labelledby="nuevaSolicitudTitulo" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
        <form method="POST" action="{{ route('controlecp.aprendiz.asesorias.store') }}" id="formNuevaSolicitud" novalidate>
            @csrf
            <div class="modal-header">
                <div>
                    <div class="apr-asesoria-request-kicker mb-1">Control ECP · Aprendiz</div>
                    <h2 class="modal-title h5 fw-bold mb-0" id="nuevaSolicitudTitulo"><i class="fas fa-calendar-plus me-2"></i>Solicitar asesoría</h2>
                </div>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="categoriaAsesoria">Categoría <span class="text-danger">*</span></label>
                        <select id="categoriaAsesoria" class="form-select" name="id_categoria_asesoria" required><option value="">Selecciona una categoría</option>@foreach($categorias as $categoria)<option value="{{ $categoria->id_categoria_asesoria }}" @selected(old('id_categoria_asesoria') == $categoria->id_categoria_asesoria)>{{ $categoria->nombre }}</option>@endforeach</select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="responsableAsesoria">Instructor o administrador <span class="text-danger">*</span></label>
                        <select id="responsableAsesoria" class="form-select" name="id_usuario_instructor" required disabled><option value="">Primero selecciona una categoría</option></select>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-center">
                            <button class="btn apr-asesoria-request-search px-4" type="button" id="buscarResponsableAsesoria" disabled>
                                <i class="fas fa-magnifying-glass me-1"></i>Buscar
                            </button>
                        </div>
                    </div>
                    <div class="col-12 d-none" id="seccionCalendarioAsesoria">
                        <label class="form-label">Selecciona una fecha disponible <span class="text-danger">*</span></label>
                        <input id="fechaAsesoria" type="hidden" name="fecha">
                        <div class="apr-asesoria-calendar" id="calendarioAsesoria" aria-live="polite">
                            <div class="apr-asesoria-calendar-toolbar">
                                <button type="button" id="mesAnteriorAsesoria" aria-label="Mes anterior" disabled><i class="fas fa-chevron-left"></i></button>
                                <strong id="mesCalendarioAsesoria" class="text-capitalize">Selecciona un responsable</strong>
                                <button type="button" id="mesSiguienteAsesoria" aria-label="Mes siguiente" disabled><i class="fas fa-chevron-right"></i></button>
                            </div>
                            <div class="apr-asesoria-calendar-grid" id="diasCalendarioAsesoria">
                                @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $dia)
                                    <span class="apr-asesoria-calendar-weekday">{{ $dia }}</span>
                                @endforeach
                            </div>
                            <div class="apr-asesoria-calendar-note" id="notaCalendarioAsesoria">Elige primero una categoría y el responsable.</div>
                            <div class="apr-asesoria-calendar-slots" id="franjasCalendarioAsesoria"></div>
                            <div class="apr-asesoria-calendar-legend" aria-label="Leyenda del calendario">
                                <span class="apr-asesoria-calendar-legend-item"><span class="apr-asesoria-calendar-legend-color" style="border-color:#b9dfc2;background:#eef9f0"></span>Disponible</span>
                                <span class="apr-asesoria-calendar-legend-item"><span class="apr-asesoria-calendar-legend-color" style="border-color:#f1b8b8;background:#fde8e8"></span>Con asesoría</span>
                                <span class="apr-asesoria-calendar-legend-item"><span class="apr-asesoria-calendar-legend-color" style="border-color:#c8d3dd;background:#e8eef3"></span>Fin de semana o festivo</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="horaInicioAsesoria">Desde <span class="text-danger">*</span></label>
                        <input id="horaInicioAsesoria" class="form-control" type="time" name="hora_inicio" min="08:00" max="14:59" step="60" required disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="horaFinAsesoria">Hasta <span class="text-danger">*</span></label>
                        <input id="horaFinAsesoria" class="form-control" type="time" name="hora_fin" min="08:00" max="15:00" step="60" required disabled>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="motivoAsesoria">Motivo de la asesoría <span class="text-muted fw-normal">(opcional)</span></label>
                        <textarea id="motivoAsesoria" class="form-control" name="motivo" rows="3" maxlength="2000" placeholder="Cuéntanos en qué tema necesitas apoyo...">{{ old('motivo') }}</textarea>
                    </div>
                    @if($errors->any())
                        <div class="col-12">
                            <div class="alert alert-danger py-2 mb-0" role="alert">
                                @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-end gap-2">
                <div class="w-100 small text-danger" id="mensajeValidacionSolicitud" role="status" aria-live="polite"></div>
                <button class="btn apr-asesoria-request-cancel rounded-pill px-4" type="button" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn apr-asesoria-request-submit rounded-pill px-4" type="submit" id="guardarSolicitud"><i class="fas fa-paper-plane me-1"></i>Enviar solicitud</button>
            </div>
        </form>
    </div></div></div>
</div>
@endsection

@push('scripts')
@php
    $valoresSolicitudAnterior = [
        'responsable' => old('id_usuario_instructor'),
        'fecha' => old('fecha'),
        'inicio' => old('hora_inicio'),
        'fin' => old('hora_fin'),
    ];
@endphp
<script>
(() => {
    const form = document.getElementById('formNuevaSolicitud');
    const category = document.getElementById('categoriaAsesoria');
    const responsible = document.getElementById('responsableAsesoria');
    const dateField = document.getElementById('fechaAsesoria');
    const startField = document.getElementById('horaInicioAsesoria');
    const endField = document.getElementById('horaFinAsesoria');
    const reasonField = document.getElementById('motivoAsesoria');
    const formMessage = document.getElementById('mensajeValidacionSolicitud');
    const searchButton = document.getElementById('buscarResponsableAsesoria');
    const calendarSection = document.getElementById('seccionCalendarioAsesoria');
    const monthLabel = document.getElementById('mesCalendarioAsesoria');
    const calendarGrid = document.getElementById('diasCalendarioAsesoria');
    const calendarNote = document.getElementById('notaCalendarioAsesoria');
    const slotList = document.getElementById('franjasCalendarioAsesoria');
    const prevMonth = document.getElementById('mesAnteriorAsesoria');
    const nextMonth = document.getElementById('mesSiguienteAsesoria');
    const weekdayLabels = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];
    let calendarDays = [];
    let currentMonth = null;
    let selectedDay = null;
    let responsibleRequest = 0;
    let calendarRequest = 0;
    const previousValues = @json($valoresSolicitudAnterior);
    const dateMap = () => new Map(calendarDays.map(day => [day.fecha, day]));
    const resetCalendar = () => {
        calendarRequest++;
        calendarDays = [];
        currentMonth = null;
        selectedDay = null;
        calendarSection.classList.add('d-none');
        dateField.value = '';
        startField.value = '';
        endField.value = '';
        startField.disabled = true;
        endField.disabled = true;
        monthLabel.textContent = 'Selecciona un responsable';
        calendarNote.textContent = 'Elige primero una categoría y el responsable.';
        slotList.replaceChildren();
        calendarGrid.replaceChildren(...weekdayLabels.map(day => {
            const label = document.createElement('span');
            label.className = 'apr-asesoria-calendar-weekday';
            label.textContent = day;
            return label;
        }));
        prevMonth.disabled = true;
        nextMonth.disabled = true;
        validateForm();
    };
    const validateForm = () => {
        const day = dateMap().get(dateField.value);
        const start = startField.value;
        const end = endField.value;
        let validTimes = Boolean(start && end && end > start);
        if (validTimes && day) {
            validTimes = day.franjas.some(slot => start >= slot.inicio && end <= slot.fin);
        }
        if (category.value && responsible.value && day?.disponible && validTimes) {
            formMessage.textContent = '';
        }
    };
    const renderCalendar = () => {
        if (!currentMonth || !calendarDays.length) return;
        const monthStart = new Date(currentMonth.getFullYear(), currentMonth.getMonth(), 1);
        const monthEnd = new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1, 0);
        monthLabel.textContent = new Intl.DateTimeFormat('es-CO', {month:'long', year:'numeric'}).format(monthStart);
        calendarGrid.replaceChildren(...weekdayLabels.map(day => {
            const label = document.createElement('span');
            label.className = 'apr-asesoria-calendar-weekday';
            label.textContent = day;
            return label;
        }));
        const offset = (monthStart.getDay() + 6) % 7;
        for (let blank = 0; blank < offset; blank++) {
            const outsideDate = new Date(currentMonth.getFullYear(), currentMonth.getMonth(), 1 - (offset - blank));
            const outsideButton = document.createElement('button');
            outsideButton.type = 'button';
            outsideButton.disabled = true;
            outsideButton.className = 'apr-asesoria-calendar-day is-outside';
            outsideButton.textContent = String(outsideDate.getDate());
            calendarGrid.append(outsideButton);
        }
        const days = dateMap();
        for (let dayNumber = 1; dayNumber <= monthEnd.getDate(); dayNumber++) {
            const date = new Date(currentMonth.getFullYear(), currentMonth.getMonth(), dayNumber);
            const key = `${date.getFullYear()}-${String(date.getMonth()+1).padStart(2,'0')}-${String(dayNumber).padStart(2,'0')}`;
            const info = days.get(key);
            const dayButton = document.createElement('button');
            dayButton.type = 'button';
            dayButton.className = 'apr-asesoria-calendar-day';
            dayButton.textContent = String(dayNumber);
            dayButton.disabled = !info?.disponible;
            if (info?.no_laborable) dayButton.classList.add('is-nonworking');
            else if (info?.ocupado) dayButton.classList.add('is-busy');
            else if (!info?.disponible) dayButton.classList.add('is-unavailable');
            dayButton.setAttribute('aria-label', `${dayNumber} ${monthLabel.textContent}${info?.disponible ? ', disponible' : ', no disponible'}`);
            if (key === selectedDay) dayButton.classList.add('is-selected');
            if (info?.disponible) {
                dayButton.addEventListener('click', () => selectDay(info));
            }
            calendarGrid.append(dayButton);
        }
        const renderedCount = offset + monthEnd.getDate();
        const trailingCount = (renderedCount <= 35 ? 35 : 42) - renderedCount;
        for (let trailing = 1; trailing <= trailingCount; trailing++) {
            const outsideDate = new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1, trailing);
            const outsideButton = document.createElement('button');
            outsideButton.type = 'button';
            outsideButton.disabled = true;
            outsideButton.className = 'apr-asesoria-calendar-day is-outside';
            outsideButton.textContent = String(outsideDate.getDate());
            calendarGrid.append(outsideButton);
        }
        const firstMonth = new Date(calendarDays[0].fecha + 'T00:00:00');
        const lastMonth = new Date(calendarDays[calendarDays.length - 1].fecha + 'T00:00:00');
        prevMonth.disabled = currentMonth.getFullYear() === firstMonth.getFullYear() && currentMonth.getMonth() === firstMonth.getMonth();
        nextMonth.disabled = currentMonth.getFullYear() === lastMonth.getFullYear() && currentMonth.getMonth() === lastMonth.getMonth();
    };
    const selectDay = day => {
        selectedDay = day.fecha;
        dateField.value = day.fecha;
        startField.disabled = false;
        endField.disabled = false;
        startField.value = '';
        endField.value = '';
        slotList.replaceChildren();
        calendarNote.textContent = `Horarios libres para ${new Intl.DateTimeFormat('es-CO', {dateStyle:'long'}).format(new Date(day.fecha + 'T00:00:00'))}. Elige Desde/Hasta dentro de una de estas franjas:`;
        day.franjas.forEach(slot => {
            const badge = document.createElement('span');
            badge.className = 'apr-asesoria-calendar-slot';
            badge.textContent = `${slot.inicio} – ${slot.fin}`;
            slotList.append(badge);
        });
        renderCalendar();
        validateForm();
    };
    const loadCalendar = async () => {
        resetCalendar();
        if (!category.value || !responsible.value) return;
        const requestId = calendarRequest;
        calendarNote.textContent = 'Consultando fechas disponibles…';
        const url = new URL(@json(route('controlecp.aprendiz.asesorias.calendario')), window.location.origin);
        url.searchParams.set('categoria_id', category.value);
        url.searchParams.set('responsable_id', responsible.value);
        try {
            const response = await fetch(url, {headers: {'Accept': 'application/json'}});
            const result = await response.json();
            if (requestId !== calendarRequest) return;
            if (!response.ok) throw new Error(result.message || result.mensaje || 'No se pudo consultar el calendario.');
            calendarDays = result.dias;
            currentMonth = new Date(result.desde + 'T00:00:00');
            calendarSection.classList.remove('d-none');
            calendarNote.textContent = result.mensaje || 'Los días verdes tienen horarios libres; elige uno para ver las franjas.';
            renderCalendar();
            if (previousValues.fecha && dateMap().get(previousValues.fecha)?.disponible) {
                selectDay(dateMap().get(previousValues.fecha));
                startField.value = previousValues.inicio || '';
                endField.value = previousValues.fin || '';
                validateForm();
            }
        } catch (error) {
            if (requestId !== calendarRequest) return;
            calendarNote.textContent = error.message;
        }
    };
    const loadResponsables = async () => {
        const requestId = ++responsibleRequest;
        responsible.disabled = true;
        responsible.replaceChildren(new Option('Cargando responsables…', ''));
        resetCalendar();
        searchButton.disabled = true;
        if (!category.value) {
            responsible.replaceChildren(new Option('Primero selecciona una categoría', ''));
            return;
        }
        const url = new URL(@json(route('controlecp.aprendiz.asesorias.responsables')), window.location.origin);
        url.searchParams.set('categoria_id', category.value);
        try {
            const response = await fetch(url, {headers: {'Accept': 'application/json'}});
            const result = await response.json();
            if (requestId !== responsibleRequest) return;
            if (!response.ok) throw new Error(result.message || result.mensaje || 'No se pudieron cargar los responsables.');
            responsible.replaceChildren(new Option(result.responsables.length ? 'Selecciona un responsable' : 'No hay responsables', ''));
            result.responsables.forEach(item => {
                const option = new Option(`${item.tipo === 'administrador' ? 'Administrador' : 'Instructor'} · ${item.nombre} · ${item.email}`, item.id);
                responsible.add(option);
            });
            responsible.disabled = result.responsables.length === 0;
            searchButton.disabled = true;
            if (!result.responsables.length) calendarNote.textContent = result.mensaje;
        } catch (error) {
            if (requestId !== responsibleRequest) return;
            responsible.replaceChildren(new Option(error.message, ''));
        }
    };
    category.addEventListener('change', loadResponsables);
    responsible.addEventListener('change', () => {
        searchButton.disabled = !responsible.value;
        if (responsible.value) {
            loadCalendar();
        } else {
            resetCalendar();
        }
    });
    searchButton.addEventListener('click', loadCalendar);
    startField.addEventListener('input', validateForm);
    endField.addEventListener('input', validateForm);
    reasonField.addEventListener('input', validateForm);
    prevMonth.addEventListener('click', () => {
        currentMonth = new Date(currentMonth.getFullYear(), currentMonth.getMonth() - 1, 1);
        renderCalendar();
    });
    nextMonth.addEventListener('click', () => {
        currentMonth = new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1, 1);
        renderCalendar();
    });
    form.addEventListener('submit', event => {
        validateForm();
        const day = dateMap().get(dateField.value);
        let error = '';
        let fieldToFocus = null;
        if (!category.value || !responsible.value || !day?.disponible) {
            error = 'Selecciona la categoría, el responsable y un día disponible.';
        } else if (!startField.value) {
            error = 'Ingresa la hora de inicio para continuar.';
            fieldToFocus = startField;
        } else if (!endField.value) {
            error = 'Ingresa la hora de finalización para continuar.';
            fieldToFocus = endField;
        } else if (startField.value < '08:00' || endField.value > '15:00') {
            error = 'El horario de la asesoría debe estar entre las 8:00 a. m. y las 3:00 p. m.';
            fieldToFocus = startField.value < '08:00' ? startField : endField;
        } else if (endField.value <= startField.value) {
            error = 'La hora de finalización debe ser posterior a la hora de inicio.';
            fieldToFocus = endField;
        } else if (!day.franjas.some(slot => startField.value >= slot.inicio && endField.value <= slot.fin)) {
            error = 'El horario debe quedar dentro de una de las franjas libres mostradas para este día.';
            fieldToFocus = startField;
        }

        if (error) {
            event.preventDefault();
            formMessage.textContent = error;
            fieldToFocus?.focus();
            fieldToFocus?.scrollIntoView({behavior: 'smooth', block: 'center'});
        } else {
            formMessage.textContent = '';
        }
    });

    @if(old('id_categoria_asesoria'))
        loadResponsables().then(() => {
            if (previousValues.responsable) {
                responsible.value = String(previousValues.responsable);
                searchButton.disabled = !responsible.value;
                if (responsible.value) loadCalendar();
            }
        });
    @endif

    @if($errors->any())
        if (window.bootstrap?.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(document.getElementById('nuevaSolicitud')).show();
        }
    @endif
})();
</script>
@endpush
