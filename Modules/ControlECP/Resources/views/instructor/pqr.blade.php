@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .pqr-stat-card {
        height: 100%;
        padding: 1rem 1.25rem;
        background: #fff;
        border: 2px solid transparent;
        border-radius: 18px;
        box-shadow: 0 2px 10px rgba(0, 26, 41, .04);
        transition: transform .2s, box-shadow .2s;
    }
    .pqr-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(0, 26, 41, .1);
    }
    .pqr-stat-icon {
        display: flex;
        width: 38px;
        height: 38px;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 1rem;
    }
    .pqr-stat-label { color: #475569; font-size: .95rem; font-weight: 500; }
    .pqr-stat-value { margin-top: .15rem; font-size: 1.7rem; font-weight: 800; line-height: 1; }
    .pqr-stat-subtitle { color: #64748b; font-size: .9rem; }

    .pqr-header-card {
        padding: 1.25rem 1.5rem;
        background: #001A29;
        border-radius: 24px;
    }
    .pqr-header-card h1 { color: #fff; }
    .pqr-header-card p { color: #c7d4da; }

    .av-badge {
        display: flex;
        width: 38px;
        height: 38px;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border: 1.5px solid #A3E070;
        border-radius: 50%;
        background: #E2F5D7;
        color: #39A900;
        font-size: .88rem;
        font-weight: 700;
    }
    .pqr-manage-icon {
        display:inline-flex;
        width:38px;
        height:38px;
        align-items:center;
        justify-content:center;
        padding:0;
        border:1px solid #93c5fd;
        border-radius:11px;
        background:#eff6ff;
        color:#2563eb;
        transition:transform .18s ease,background-color .18s ease,color .18s ease,box-shadow .18s ease;
    }
    .pqr-manage-icon-history { color:#7c3aed; background:#f5f3ff; border-color:#ddd6fe; }
    .pqr-manage-icon-history:hover,
    .pqr-manage-icon-history:focus-visible { border-color:#7c3aed; background:#7c3aed; color:#fff; }
    .pqr-manage-icon-status { color:#d97706; background:#fffbeb; border-color:#fde68a; }
    .pqr-manage-icon-status:hover,
    .pqr-manage-icon-status:focus-visible { border-color:#d97706; background:#d97706; color:#fff; }
    .pqr-manage-icon-reply { color:#16a34a; background:#f0fdf4; border-color:#bbf7d0; }
    .pqr-manage-icon-reply:hover,
    .pqr-manage-icon-reply:focus-visible { border-color:#16a34a; background:#16a34a; color:#fff; }
    .pqr-manage-icon:not(.pqr-manage-icon-history):not(.pqr-manage-icon-status):not(.pqr-manage-icon-reply):hover,
    .pqr-manage-icon:not(.pqr-manage-icon-history):not(.pqr-manage-icon-status):not(.pqr-manage-icon-reply):focus-visible {
        border-color:#2563eb;
        background:#2563eb;
        color:#fff;
    }
    .pqr-manage-icon:hover,
    .pqr-manage-icon:focus-visible {
        transform:translateY(-2px);
        box-shadow:0 5px 14px rgba(37,99,235,.28);
    }
    .pqr-manage-icon:disabled { opacity:.4; cursor:not-allowed; }
    .pqr-status-modal .modal-content { overflow:hidden; border-radius:22px; }
    .pqr-status-modal .modal-header {
        padding:1.35rem 1.5rem;
        color:#fff;
        background:linear-gradient(135deg,#001A29 0%,#12394d 100%);
        border:0;
    }
    .pqr-status-modal-icon {
        display:flex;
        width:46px;
        height:46px;
        flex:0 0 46px;
        align-items:center;
        justify-content:center;
        border:1px solid rgba(253,230,138,.45);
        border-radius:15px;
        background:rgba(245,158,11,.16);
        color:#fbbf24;
        font-size:1.15rem;
    }
    .pqr-status-modal .modal-body { padding:1.5rem; }
    .pqr-status-modal .form-select,
    .pqr-status-modal .form-control {
        min-height:46px;
        border:1px solid #dbe3eb;
        border-radius:12px;
        box-shadow:none;
    }
    .pqr-status-modal textarea.form-control { min-height:112px; resize:vertical; }
    .pqr-status-modal .form-select:focus,
    .pqr-status-modal .form-control:focus {
        border-color:#39A900;
        box-shadow:0 0 0 .2rem rgba(57,169,0,.13);
    }
    .pqr-status-modal .modal-footer {
        padding:1rem 1.5rem;
        border-top:1px solid #edf1f5;
        background:#fbfcfd;
    }
    .pqr-status-save {
        min-height:42px;
        padding:.55rem 1.15rem;
        border:0;
        border-radius:999px;
        background:#39A900;
        color:#fff;
        font-weight:700;
    }
    .pqr-status-save:hover { background:#2f8f00; color:#fff; }
    .pqr-reply-modal .modal-content { overflow:hidden; border-radius:22px; }
    .pqr-reply-modal .modal-header {
        padding:1.35rem 1.5rem;
        color:#fff;
        background:linear-gradient(135deg,#001A29 0%,#12394d 100%);
        border:0;
    }
    .pqr-reply-modal-icon {
        display:flex;
        width:46px;
        height:46px;
        flex:0 0 46px;
        align-items:center;
        justify-content:center;
        border:1px solid rgba(134,239,172,.45);
        border-radius:15px;
        background:rgba(22,163,74,.18);
        color:#86efac;
        font-size:1.15rem;
    }
    .pqr-reply-modal .modal-body { padding:1.5rem; }
    .pqr-reply-modal textarea.form-control {
        min-height:150px;
        border:1px solid #dbe3eb;
        border-radius:12px;
        box-shadow:none;
        resize:vertical;
    }
    .pqr-reply-modal textarea.form-control:focus {
        border-color:#39A900;
        box-shadow:0 0 0 .2rem rgba(57,169,0,.13);
    }
    .pqr-reply-modal .modal-footer {
        padding:1rem 1.5rem;
        border-top:1px solid #edf1f5;
        background:#fbfcfd;
    }
    .pqr-reply-send {
        min-height:42px;
        padding:.55rem 1.15rem;
        border:0;
        border-radius:999px;
        background:#16a34a;
        color:#fff;
        font-weight:700;
    }
    .pqr-reply-send:hover { background:#15803d; color:#fff; }
    .pqr-history-modal .modal-content { overflow:hidden; border-radius:22px; }
    .pqr-history-modal .modal-header {
        padding:1.35rem 1.5rem;
        color:#fff;
        background:linear-gradient(135deg,#001A29 0%,#12394d 100%);
        border:0;
    }
    .pqr-history-modal-icon {
        display:flex;
        width:46px;
        height:46px;
        flex:0 0 46px;
        align-items:center;
        justify-content:center;
        border:1px solid rgba(196,181,253,.45);
        border-radius:15px;
        background:rgba(124,58,237,.18);
        color:#c4b5fd;
        font-size:1.15rem;
    }
    .pqr-history-modal .modal-body { padding:1.5rem; }
    .pqr-history-timeline { position:relative; padding-left:1.7rem; }
    .pqr-history-timeline::before {
        position:absolute;
        top:.35rem;
        bottom:.35rem;
        left:.42rem;
        width:2px;
        background:#e9d5ff;
        content:"";
    }
    .pqr-history-event { position:relative; padding:0 0 1.4rem; }
    .pqr-history-event:last-child { padding-bottom:0; }
    .pqr-history-event::before {
        position:absolute;
        top:.25rem;
        left:-1.64rem;
        width:13px;
        height:13px;
        border:3px solid #fff;
        border-radius:50%;
        background:#8b5cf6;
        box-shadow:0 0 0 2px #c4b5fd;
        content:"";
    }
    .pqr-history-event-heading {
        display:flex;
        flex-wrap:wrap;
        align-items:baseline;
        gap:.25rem .5rem;
        margin-bottom:.2rem;
    }
    .pqr-history-event-status { color:#1e293b; font-weight:700; }
    .pqr-history-event-date { color:#64748b; font-size:.88rem; }
    .pqr-history-event-note { color:#475569; white-space:pre-wrap; }
    .pqr-history-empty {
        padding:1rem;
        border:1px dashed #c4b5fd;
        border-radius:14px;
        background:#faf8ff;
        color:#64748b;
        text-align:center;
    }
    .pqr-history-modal .modal-footer {
        padding:1rem 1.5rem;
        border-top:1px solid #edf1f5;
        background:#fbfcfd;
    }
    .pqr-detail-modal .modal-content { overflow:hidden; border-radius:22px; }
    .pqr-detail-modal .modal-header {
        padding:1.35rem 1.5rem;
        color:#fff;
        background:linear-gradient(135deg,#001A29 0%,#12394d 100%);
        border:0;
    }
    .pqr-detail-modal-icon {
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
    .pqr-detail-modal .modal-body { padding:1.5rem; }
    .pqr-detail-meta {
        padding:1rem;
        border:1px solid #e8edf2;
        border-radius:15px;
        background:#f8fafc;
    }
    .pqr-detail-label {
        display:block;
        margin-bottom:.2rem;
        color:#64748b;
        font-size:.75rem;
        font-weight:700;
        letter-spacing:.03em;
        text-transform:uppercase;
    }
    .pqr-detail-description {
        padding:1rem;
        border:1px solid #e8edf2;
        border-radius:15px;
        background:#fff;
        color:#334155;
        line-height:1.65;
        white-space:pre-wrap;
    }
    .pqr-detail-response {
        padding:1rem;
        border:1px solid #dcfce7;
        border-left:4px solid #39A900;
        border-radius:14px;
        background:#f7fdf5;
    }
    .pqr-detail-modal .modal-footer {
        padding:1rem 1.5rem;
        border-top:1px solid #edf1f5;
        background:#fbfcfd;
    }
    .tbl-head-row th {
        padding: .85rem 1rem;
        border-bottom: 2px solid #E2E8F0;
        background: #F8FAFC;
        color: #475569;
        font-size: .76rem;
        font-weight: 700;
        letter-spacing: .4px;
        text-transform: uppercase;
    }
    .tbl-head-row th:first-child { padding-left: 1.5rem; }
    .tbl-head-row th:last-child { padding-right: 1.5rem; }
    .tbl-body-row td {
        padding: .85rem 1rem;
        border-bottom: 1px solid #F1F5F9;
        vertical-align: middle;
    }
    .tbl-body-row td:first-child { padding-left: 1.5rem; }
    .tbl-body-row td:last-child { padding-right: 1.5rem; }
    .tbl-body-row:last-child td { border-bottom: 0; }
    .tbl-body-row:hover td { background: #FAFBFF; }
    .pqr-pagination { padding:1rem; border-top:1px solid #eef2f5; background:#fff; }
    .pqr-pagination .pagination { margin:0; }
    .pqr-pagination .page-link {
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
    .pqr-pagination .page-item:first-child .page-link { border-radius:8px 0 0 8px; }
    .pqr-pagination .page-item:last-child .page-link { border-right:1px solid #d5e7db; border-radius:0 8px 8px 0; }
    .pqr-pagination .page-item.active .page-link { border-color:#39A900; background:#39A900; color:#fff; }
    .pqr-pagination .page-item:not(.active):not(.disabled) .page-link:hover { background:#f0fdf4; color:#318f00; }
    .pqr-pagination .page-item.disabled .page-link { background:#f8fafc; color:#94a3b8; }

    .pqr-filter-card {
        padding: 1rem 1.15rem;
        background: #001A29;
        border-radius: 18px;
        box-shadow: 0 5px 20px rgba(0, 26, 41, .08);
    }
    .pqr-filter-form {
        display: grid;
        grid-template-columns: minmax(200px, 1.4fr) minmax(150px, 1fr) minmax(160px, 1fr) 160px;
        align-items: end;
        gap: .75rem;
    }
    .pqr-filter-field { min-width: 0; }
    .pqr-filter-field label {
        display: block;
        margin: 0 0 .3rem;
        color: #fff;
        font-size: .75rem;
        font-weight: 700;
    }
    .pqr-filter-field .form-control,
    .pqr-filter-field .form-select {
        width: 100%;
        min-width: 0;
        height: 44px;
        padding: .55rem .8rem;
        border: 1px solid #fff;
        border-radius: 10px;
        background: #fff;
        color: #001A29;
        font-size: .88rem;
        box-shadow: none;
    }
    .pqr-filter-field .input-group { flex-wrap: nowrap; }
    .pqr-filter-field .input-group-text {
        height: 44px;
        padding: 0 .8rem;
        border: 1px solid #fff;
        border-right: 0;
        border-radius: 10px 0 0 10px;
        background: #fff;
        color: #001A29;
    }
    .pqr-filter-field .input-group .form-control {
        border-left: 0;
        border-radius: 0 10px 10px 0;
    }
    .pqr-filter-actions { display: flex; min-width: 0; gap: .55rem; }
    .pqr-filter-actions .btn-filter {
        height: 44px;
        min-width: 0;
        flex: 1;
        border: 0;
        border-radius: 10px;
        background: #39A900;
        color: #fff;
        font-weight: 700;
        white-space: nowrap;
    }
    .pqr-filter-actions .btn-filter:hover { background: #318f00; color: #fff; }
    .pqr-filter-actions .btn-reset {
        display: grid;
        width: 48px;
        height: 44px;
        flex: 0 0 48px;
        place-items: center;
        padding: 0;
        border: 1px solid #fff;
        border-radius: 10px;
        background: #fff;
        color: #001A29;
    }
    .pqr-filter-actions .btn-reset:hover { background: #f1f5f9; color: #001A29; }

    @media (max-width: 900px) {
        .pqr-filter-form { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .pqr-filter-actions { grid-column: span 2; justify-content: flex-end; }
        .pqr-filter-actions .btn-filter { flex: 0 0 200px; }
    }
    @media (max-width: 520px) {
        .pqr-filter-form { grid-template-columns: minmax(0, 1fr); }
        .pqr-filter-actions { grid-column: auto; }
        .pqr-filter-actions .btn-filter { flex: 1; }
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-3 py-3">
    <div class="pqr-header-card d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small fw-semibold mb-1" style="color:#39A900;">
                <i class="fas fa-comment-dots me-2"></i>Control ECP · Panel del instructor
            </div>
            <h1 class="h3 fw-bold mb-0" style="font-family:'Outfit',sans-serif;">
                <i class="fas fa-comment-dots me-2" style="color:#39A900;"></i>Mis PQRS asignadas
            </h1>
            <p class="small mb-0 mt-1">Consulta, responde y actualiza las solicitudes dirigidas a ti.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4"
             style="background:#f0fbf4;color:#001A29;">
            <i class="fas fa-check-circle me-2" style="color:#39A900;"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="pqr-stat-card h-100" style="border-color:rgba(2,136,209,.35);">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="pqr-stat-label">Total recibidas</span>
                    <span class="pqr-stat-icon" style="background:#E0F2FE;color:#0288D1;"><i class="fas fa-inbox"></i></span>
                </div>
                <div class="pqr-stat-value" style="color:#0288D1;">{{ $totales['radicadas'] }}</div>
                <div class="pqr-stat-subtitle">Solicitudes asignadas</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="pqr-stat-card h-100" style="border-color:rgba(217,119,6,.35);">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="pqr-stat-label">Pendientes</span>
                    <span class="pqr-stat-icon" style="background:#FEF3C7;color:#D97706;"><i class="fas fa-clock"></i></span>
                </div>
                <div class="pqr-stat-value" style="color:#D97706;">{{ $totales['pendientes'] }}</div>
                <div class="pqr-stat-subtitle">Sin respuesta aún</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="pqr-stat-card h-100" style="border-color:rgba(79,70,229,.35);">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="pqr-stat-label">En trámite</span>
                    <span class="pqr-stat-icon" style="background:#EEF2FF;color:#4F46E5;"><i class="fas fa-spinner"></i></span>
                </div>
                <div class="pqr-stat-value" style="color:#4F46E5;">{{ $totales['en_tramite'] }}</div>
                <div class="pqr-stat-subtitle">En revisión activa</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="pqr-stat-card h-100" style="border-color:rgba(22,163,74,.35);">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="pqr-stat-label">Resueltos</span>
                    <span class="pqr-stat-icon" style="background:#DCFCE7;color:#16A34A;"><i class="fas fa-circle-check"></i></span>
                </div>
                <div class="pqr-stat-value" style="color:#16A34A;">{{ $totales['resueltas'] }}</div>
                <div class="pqr-stat-subtitle">Casos finalizados</div>
            </div>
        </div>
    </div>

    <div class="pqr-filter-card mb-4">
        <form method="GET" action="{{ route('controlecp.instructor.pqr') }}" class="pqr-filter-form">
            <div class="pqr-filter-field">
                <label for="buscarPqr">Buscar</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-magnifying-glass"></i></span>
                    <input id="buscarPqr" class="form-control" name="buscar" value="{{ request('buscar') }}"
                           placeholder="Radicado o asunto…">
                </div>
            </div>
            <div class="pqr-filter-field">
                <label for="tipoPqr">Tipo</label>
                <select id="tipoPqr" class="form-select" name="tipo">
                    <option value="">Todos los tipos</option>
                    @foreach($tipos as $tipo)
                        <option value="{{ $tipo->id_tipo_pqrs }}" @selected((string) request('tipo') === (string) $tipo->id_tipo_pqrs)>
                            {{ $tipo->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="pqr-filter-field">
                <label for="estadoPqr">Estado</label>
                <select id="estadoPqr" class="form-select" name="estado">
                    <option value="">Todos los estados</option>
                    <option value="Pendiente" @selected(request('estado') === 'Pendiente')>Pendiente</option>
                    <option value="En trámite" @selected(request('estado') === 'En trámite')>En trámite</option>
                    <option value="Resuelto" @selected(request('estado') === 'Resuelto')>Resuelto</option>
                </select>
            </div>
            <div class="pqr-filter-actions">
                <button type="submit" class="btn btn-filter">
                    <i class="fas fa-search me-1"></i>Buscar
                </button>
                <a href="{{ route('controlecp.instructor.pqr') }}" class="btn btn-reset"
                   title="Restablecer filtros" aria-label="Restablecer filtros">
                    <i class="fas fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="mb-3 d-flex align-items-center gap-2">
        <span class="d-inline-block rounded-circle" style="width:10px;height:10px;background:#39A900;"></span>
        <h2 class="h6 fw-bold mb-0" style="color:#001A29;">
            Solicitudes asignadas
        </h2>
    </div>
    <div class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden;">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr class="tbl-head-row">
                        <th>Radicado / Fecha</th>
                        <th>Aprendiz</th>
                        <th>Tipo</th>
                        <th>Asunto</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pqrs as $pqr)
                        @php
                            $badge = $pqr->estado_badge;
                        @endphp
                        <tr class="tbl-body-row">
                            <td>
                                <div class="fw-bold font-monospace" style="font-size:.84rem;color:#001A29;">
                                    {{ $pqr->radicado }}
                                </div>
                                <small class="text-muted" style="font-size:.7rem;">
                                    <i class="fas fa-calendar-day me-1"></i>{{ $pqr->fecha_radicacion?->copy()->timezone('America/Bogota')->format('d/m/Y g:i A') ?? '—' }}
                                </small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="av-badge">{{ strtoupper(substr($pqr->aprendiz?->nickname ?? $pqr->nombre_solicitante ?? 'A', 0, 1)) }}</div>
                                    <div>
                                        <div class="fw-semibold text-dark" style="font-size:.88rem;">
                                            {{ $pqr->aprendiz?->nickname ?? $pqr->nombre_solicitante ?? 'Aprendiz' }}
                                        </div>
                                        <small class="text-muted" style="font-size:.7rem;">
                                            {{ $pqr->aprendiz?->email ?? $pqr->email ?? '' }}
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill px-2 py-1 border fw-semibold"
                                      style="background:{{ $pqr->tipo_badge['bg'] }};color:{{ $pqr->tipo_badge['text'] }};border-color:{{ $pqr->tipo_badge['border'] }} !important;font-size:.74rem;">
                                    <i class="{{ $pqr->tipo_badge['icon'] }} me-1"></i>{{ $pqr->tipo_nombre }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark text-truncate" style="max-width:220px;font-size:.88rem;">
                                    {{ $pqr->asunto }}
                                </div>
                                <div class="text-muted text-truncate" style="max-width:220px;font-size:.74rem;">
                                    {{ $pqr->descripcion }}
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill px-3 py-1 border fw-semibold"
                                      style="background:{{ $badge['bg'] }};color:{{ $badge['text'] }};border-color:{{ $badge['border'] }} !important;font-size:.76rem;">
                                    <i class="{{ $badge['icon'] }} me-1"></i>{{ $badge['label'] }}
                                </span>
                            </td>
                            <td class="text-end">
                                @php $cerrada = in_array($pqr->estado, ['Resuelto', 'Resuelta', 'Cerrado', 'Cerrada'], true); @endphp
                                <div class="d-inline-flex gap-2">
                                    <button class="btn pqr-manage-icon" title="Ver detalle"
                                            aria-label="Ver detalle de {{ $pqr->radicado }}"
                                            type="button" data-bs-toggle="modal" data-bs-target="#gestionarPqr{{ $pqr->id }}"
                                            data-focus-target="descripcionPqr{{ $pqr->id }}">
                                        <i class="fas fa-eye" aria-hidden="true"></i>
                                    </button>
                                    <button class="btn pqr-manage-icon pqr-manage-icon-history" title="Ver historial"
                                            aria-label="Ver historial de {{ $pqr->radicado }}"
                                            type="button" data-bs-toggle="modal" data-bs-target="#historialPqr{{ $pqr->id }}">
                                        <i class="fas fa-clock-rotate-left" aria-hidden="true"></i>
                                    </button>
                                    <button class="btn pqr-manage-icon pqr-manage-icon-status" title="{{ $cerrada ? 'PQRS cerrada' : 'Cambiar estado' }}"
                                            aria-label="Cambiar estado de {{ $pqr->radicado }}"
                                            type="button" data-bs-toggle="modal" data-bs-target="#cambiarEstadoPqr{{ $pqr->id }}"
                                            @disabled($cerrada)>
                                        <i class="fas fa-arrows-rotate" aria-hidden="true"></i>
                                    </button>
                                    <button class="btn pqr-manage-icon pqr-manage-icon-reply" title="{{ $cerrada ? 'PQRS cerrada' : 'Enviar respuesta' }}"
                                            aria-label="Enviar respuesta para {{ $pqr->radicado }}"
                                            type="button" data-bs-toggle="modal" data-bs-target="#responderPqr{{ $pqr->id }}"
                                            @disabled($cerrada)>
                                        <i class="fas fa-paper-plane" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 d-block opacity-30"></i>
                                <p class="fw-bold mb-1" style="color:#001A29;">No hay PQRS asignadas</p>
                                <small>Las solicitudes dirigidas a tu usuario aparecerán aquí.</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @foreach($pqrs as $pqr)
            @php
                $badge = $pqr->estado_badge;
                $cerrada = in_array($pqr->estado, ['Resuelto', 'Resuelta', 'Cerrado', 'Cerrada'], true);
                $modalErroresPqr = old('modal_pqr_id') == $pqr->id && $errors->has('respuesta');
                $modalErroresEstado = old('modal_pqr_id') == $pqr->id
                    && ($errors->has('estado') || $errors->has('observacion'));
            @endphp
            <div class="modal fade pqr-status-modal" id="cambiarEstadoPqr{{ $pqr->id }}" tabindex="-1"
                 aria-labelledby="cambiarEstadoTitulo{{ $pqr->id }}" aria-hidden="true"
                 @if($modalErroresEstado) data-open-after-validation="true" @endif>
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <form method="POST" action="{{ route('controlecp.instructor.pqr.estado', $pqr->id) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="modal_pqr_id" value="{{ $pqr->id }}">
                            <div class="modal-header">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="pqr-status-modal-icon"><i class="fas fa-arrows-rotate" aria-hidden="true"></i></span>
                                    <div>
                                        <div class="small fw-semibold mb-1" style="color:#fbbf24">{{ $pqr->radicado }}</div>
                                        <h2 class="modal-title h5 fw-bold mb-0" id="cambiarEstadoTitulo{{ $pqr->id }}">Cambiar estado</h2>
                                    </div>
                                </div>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                @if($modalErroresEstado)
                                    <div class="alert alert-danger rounded-3">
                                        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                                    </div>
                                @endif
                                <div class="mb-3">
                                    <label class="form-label fw-semibold" for="estadoPqr{{ $pqr->id }}">Nuevo estado</label>
                                    <select class="form-select" id="estadoPqr{{ $pqr->id }}" name="estado" required>
                                        @foreach(['Pendiente', 'En trámite', 'Resuelto'] as $estado)
                                            <option value="{{ $estado }}" @selected(($modalErroresEstado ? old('estado', $pqr->estado) : $pqr->estado) === $estado)>{{ $estado }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label fw-semibold" for="observacionPqr{{ $pqr->id }}">Observación</label>
                                    <textarea class="form-control" id="observacionPqr{{ $pqr->id }}" name="observacion" rows="4" minlength="10" maxlength="200" placeholder="Escribe el motivo o detalle del cambio..." required>{{ $modalErroresEstado ? old('observacion') : '' }}</textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn pqr-status-save">
                                    <i class="fas fa-check me-1" aria-hidden="true"></i>Guardar cambio
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal fade pqr-history-modal" id="historialPqr{{ $pqr->id }}" tabindex="-1"
                 aria-labelledby="historialPqrTitulo{{ $pqr->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header">
                            <div class="d-flex align-items-center gap-3">
                                <span class="pqr-history-modal-icon"><i class="fas fa-clock-rotate-left" aria-hidden="true"></i></span>
                                <div>
                                    <div class="small fw-semibold mb-1" style="color:#c4b5fd">{{ $pqr->radicado }}</div>
                                    <h2 class="modal-title h5 fw-bold mb-0" id="historialPqrTitulo{{ $pqr->id }}">Historial de cambios</h2>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            @if($pqr->historial->isNotEmpty())
                                <div class="pqr-history-timeline">
                                    @foreach($pqr->historial->sortBy('fecha') as $evento)
                                        <article class="pqr-history-event">
                                            <div class="pqr-history-event-heading">
                                                <span class="pqr-history-event-status">
                                                    {{ $evento->estado_anterior ? $evento->estado_anterior . ' → ' : '' }}{{ $evento->estado_nuevo }}
                                                </span>
                                                <time class="pqr-history-event-date">
                                                    {{ $evento->fecha?->copy()->timezone('America/Bogota')->format('d/m/Y H:i') ?? '—' }}
                                                </time>
                                            </div>
                                            @if($evento->observacion)
                                                <div class="pqr-history-event-note">{{ $evento->observacion }}</div>
                                            @endif
                                        </article>
                                    @endforeach
                                </div>
                            @else
                                <div class="pqr-history-empty">
                                    <i class="fas fa-clock-rotate-left mb-2 d-block" aria-hidden="true"></i>
                                    Aún no hay cambios registrados.
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade pqr-reply-modal" id="responderPqr{{ $pqr->id }}" tabindex="-1"
                 aria-labelledby="responderPqrTitulo{{ $pqr->id }}" aria-hidden="true"
                 @if($modalErroresPqr) data-open-after-validation="true" @endif>
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <form method="POST" action="{{ route('controlecp.instructor.pqr.responder', $pqr->id) }}">
                            @csrf
                            <input type="hidden" name="modal_pqr_id" value="{{ $pqr->id }}">
                            <div class="modal-header">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="pqr-reply-modal-icon"><i class="fas fa-paper-plane" aria-hidden="true"></i></span>
                                    <div>
                                        <div class="small fw-semibold mb-1" style="color:#86efac">{{ $pqr->radicado }}</div>
                                        <h2 class="modal-title h5 fw-bold mb-0" id="responderPqrTitulo{{ $pqr->id }}">Enviar respuesta</h2>
                                    </div>
                                </div>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                @if($modalErroresPqr)
                                    <div class="alert alert-danger rounded-3">
                                        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                                    </div>
                                @endif
                                <label class="form-label fw-semibold" for="respuestaPqr{{ $pqr->id }}">Respuesta para el aprendiz</label>
                                <textarea class="form-control" id="respuestaPqr{{ $pqr->id }}" name="respuesta" rows="5" minlength="10" maxlength="200" placeholder="Escribe aquí tu respuesta..." required>{{ $modalErroresPqr ? old('respuesta') : '' }}</textarea>
                                <div class="form-text text-end">Máximo 5000 caracteres.</div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn pqr-reply-send">
                                    <i class="fas fa-paper-plane me-1" aria-hidden="true"></i>Enviar respuesta
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal fade pqr-detail-modal" id="gestionarPqr{{ $pqr->id }}" tabindex="-1"
                 aria-labelledby="gestionarPqrTitulo{{ $pqr->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header">
                            <div class="d-flex align-items-center gap-3">
                                <span class="pqr-detail-modal-icon"><i class="fas fa-eye" aria-hidden="true"></i></span>
                                <div>
                                    <div class="small fw-semibold mb-1" style="color:#93c5fd">{{ $pqr->radicado }} · {{ $pqr->tipo_nombre }}</div>
                                    <h2 class="modal-title h5 fw-bold mb-0" id="gestionarPqrTitulo{{ $pqr->id }}">Detalle de la solicitud</h2>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <h3 class="h5 fw-bold mb-3" style="color:#001A29;">{{ $pqr->asunto }}</h3>
                            <div class="pqr-detail-meta d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                                <div>
                                    <span class="pqr-detail-label">Solicitante</span>
                                    <strong class="text-dark">{{ $pqr->aprendiz?->full_name ?? $pqr->nombre_solicitante ?? 'Aprendiz' }}</strong>
                                    <div class="small text-muted">{{ $pqr->aprendiz?->email ?? $pqr->email ?? '' }}</div>
                                </div>
                                <div>
                                    <span class="pqr-detail-label">Fecha de radicación</span>
                                    <span class="small fw-semibold text-dark">{{ $pqr->fecha_radicacion?->copy()->timezone('America/Bogota')->format('d/m/Y g:i A') ?? '—' }}</span>
                                </div>
                                <span class="badge rounded-pill px-3 py-2 border fw-semibold" style="background:{{ $badge['bg'] }};color:{{ $badge['text'] }};border-color:{{ $badge['border'] }} !important">
                                    <i class="{{ $badge['icon'] }} me-1" aria-hidden="true"></i>{{ $badge['label'] }}
                                </span>
                            </div>
                            <h3 class="h6 fw-bold mb-2" id="descripcionPqr{{ $pqr->id }}" tabindex="-1">Descripción</h3>
                            <div class="pqr-detail-description mb-4">{{ $pqr->descripcion }}</div>

                            @if($pqr->respuestas->isNotEmpty())
                                <h3 class="h6 fw-bold mb-3">Respuestas</h3>
                                <div class="d-grid gap-2">
                                    @foreach($pqr->respuestas as $respuesta)
                                        <article class="pqr-detail-response">
                                            <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                                                <strong class="text-dark">{{ $respuesta->usuario?->full_name ?? 'Equipo de atención' }}</strong>
                                                <time class="small text-muted">{{ $respuesta->fecha?->copy()->timezone('America/Bogota')->format('d/m/Y H:i') ?? '—' }}</time>
                                            </div>
                                            <div style="white-space:pre-wrap">{{ $respuesta->respuesta }}</div>
                                        </article>
                                    @endforeach
                                </div>
                            @else
                                <div class="small text-muted">
                                    <i class="fas fa-comment-dots me-1" aria-hidden="true"></i>Aún no hay respuestas.
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        @if($pqrs->hasPages())
            <div class="pqr-pagination">
                @if ($pqrs->hasPages())
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
                @endif
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.modal[id^="gestionarPqr"]').forEach((modal) => {
        modal.addEventListener('shown.bs.modal', (event) => {
            const targetId = event.relatedTarget?.dataset.focusTarget;
            const target = targetId ? document.getElementById(targetId) : null;
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                target.focus({ preventScroll: true });
            }
        });
    });

    const modal = document.querySelector('[data-open-after-validation="true"]');
    if (modal && window.bootstrap?.Modal) {
        window.bootstrap.Modal.getOrCreateInstance(modal).show();
    }
});
</script>
@endpush
