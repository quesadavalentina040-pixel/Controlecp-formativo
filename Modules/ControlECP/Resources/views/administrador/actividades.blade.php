@extends('controlecp::components.layouts.dashboard')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --ecp-green:   #39A900;
        --ecp-navy:    #001A29;
        --ecp-blue:    #0070B8;
        --ecp-purple:  #6A1B9A;
    }
    .act-page { font-family: 'Outfit', sans-serif; min-width: 0; }
    .actividades-header-card {
        background-color: var(--ecp-navy);
        border-radius: 24px;
        padding: 1.25rem 1.5rem;
    }
    .actividades-header-card h2 {
        color: #fff !important;
    }
    .actividades-header-card .text-muted {
        color: #c7d4da !important;
    }
    .actividades-header-card .btn-outline-secondary {
        color: #fff;
        border-color: rgba(255, 255, 255, .65);
    }
    .act-detail-icon {
        width: 34px;
        height: 34px;
        padding: 0;
        color: #d63384;
        border: 1px solid #d63384;
        transition: transform .18s ease, box-shadow .18s ease, background-color .18s ease, color .18s ease;
    }
    .act-detail-icon:hover,
    .act-detail-icon:focus-visible {
        transform: translateY(-1px);
        color: #fff;
        background: #d63384;
        box-shadow: 0 4px 12px rgba(214, 51, 132, .3);
    }
    .act-detail-icon:active { transform: scale(.96); }
    .act-rating-icon {
        width: 34px;
        height: 34px;
        padding: 0;
        color: #d28a00;
        border: 1px solid #f4b400;
        background: #fff;
        transition: transform .18s ease, box-shadow .18s ease, background-color .18s ease, color .18s ease;
    }
    .act-rating-icon:hover,
    .act-rating-icon:focus-visible {
        transform: translateY(-1px);
        border-color: #d28a00;
        color: #fff;
        background: #f4b400;
        box-shadow: 0 4px 12px rgba(210, 138, 0, .35);
    }
    .act-rating-icon:active { transform: scale(.96); }

    /* KPI cards */
    .kpi-card {
        background: #fff; border-radius: 18px;
        padding: 1rem 1.25rem;
        box-shadow: 0 4px 18px rgba(0,26,41,.07);
        border: 2px solid transparent;
        display: flex; flex-direction: column; gap: .3rem;
        transition: transform .2s, box-shadow .2s;
    }
    .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 8px 28px rgba(0,26,41,.12); }
    .kpi-icon-wrap {
        width: 38px; height: 38px; border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem;
    }
    .kpi-value { font-size: 1.7rem; font-weight: 800; line-height: 1; color: var(--ecp-navy); margin-top: .15rem; }
    .kpi-label { font-size: .95rem; color: #475569; font-weight: 500; }
    .kpi-subtitle { font-size: .9rem; color: #64748b; }

    /* Tarjetas de actividad */
    .act-card {
        background: #fff; border-radius: 16px;
        box-shadow: 0 4px 16px rgba(0,26,41,.06);
        border: 1px solid rgba(0,0,0,.04);
        transition: transform .2s, box-shadow .2s;
        overflow: hidden;
    }
    .act-card:hover { transform: translateY(-3px); box-shadow: 0 10px 28px rgba(0,26,41,.11); }
    .act-card.estado-inactivo { border-style: dashed; }
    .act-card.estado-finalizado { border-top: 3px solid #6c757d; }

    /* Badge estado */
    .badge-activo     { background:#e8f8e8; color:#1e7000; border:1px solid #c2eec2; }
    .badge-inactivo   { background:#f5f5f5; color:#666; border:1px solid #ddd; }
    .badge-finalizado { background:#e8f0fe; color:#3949ab; border:1px solid #c5cae9; }

    .comp-badge {
        font-size: .72rem; padding: .25rem .65rem; border-radius: 50px; font-weight: 600;
        border: 1.5px solid; display: inline-flex; align-items: center; gap: .3rem;
    }
    .comp-on  { background: #f0fbf4; }
    .comp-off { background: #ffffff; }
    .comp-encuesta {
        background: #eaf5ff;
        color: #0070b8;
        border-color: #bce1f7;
    }
    .comp-encuesta:hover { background: #d9edff; }
    button.comp-badge { cursor: pointer; }

    /* Sección de momento */
    .momentos-list {
        display: flex;
        flex-direction: column;
        width: 100%;
        min-width: 0;
        gap: 0;
    }
    .momento-section {
        display: block;
        flex: 0 0 100%;
        width: 100%;
        min-width: 0;
        border-radius: 18px;
        overflow: hidden;
        margin-bottom: 2rem;
    }
    .momento-header  {
        width: 100%;
        padding: 1rem 1.25rem;
        color: #fff;
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: .5rem;
    }
    .act-grid-sm {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .75rem;
        padding: .85rem 1rem;
        background: #f8faf9;
    }
    .act-grid-sm > .act-card {
        width: 100%;
        min-width: 0;
        grid-column: span 1;
    }
    .act-pagination {
        display: flex;
        justify-content: center;
        padding: .8rem 1rem;
        --momento-color: var(--ecp-green);
    }
    .act-pagination nav { justify-content: center !important; }
    .act-pagination nav > div { flex: 0 0 auto !important; }
    .act-pagination .small.text-muted { display: none !important; }
    .act-pagination .pagination { margin-bottom: 0; }
    .act-pagination .page-link { color: var(--momento-color); }
    .act-pagination .page-item.active .page-link {
        border-color: var(--momento-color);
        background-color: var(--momento-color);
        color: #fff;
    }
    .act-pagination .page-link:hover {
        color: var(--momento-color);
        background: #f8faf9;
    }
    @media (max-width: 1200px) {
        .act-grid-sm { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (max-width: 768px) {
        .act-grid-sm { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 520px) {
        .act-grid-sm { grid-template-columns: 1fr; }
    }

    /* Filtro pill */
    .pill-act {
        border-radius: 20px; font-size: .81rem; font-weight: 600; padding: 5px 14px;
        text-decoration: none; transition: all .18s;
    }
    .pill-act.active { color: #fff; box-shadow: 0 4px 10px rgba(0,0,0,.15); }
    .pill-act:not(.active) { background: #f1f4f3; color: var(--ecp-navy); }
    .pill-act:not(.active):hover { background: #e5ece9; }

    .act-modal {
        --act-modal-accent: var(--ecp-green);
        --act-modal-tint: #f1faed;
    }
    .act-modal .modal-dialog {
        margin: 1rem auto;
    }
    .act-modal .modal-content {
        overflow: hidden;
        border: 1px solid #e4eaed !important;
        border-top: 5px solid var(--act-modal-accent) !important;
        border-radius: 22px !important;
        background: #fff;
        box-shadow: 0 24px 70px rgba(0, 26, 41, .24) !important;
    }
    .act-modal .modal-header {
        align-items: flex-start;
        padding: 1.35rem 1.5rem 1.1rem !important;
        border-bottom: 1px solid #edf1f3 !important;
        background: linear-gradient(115deg, var(--act-modal-tint), #fff 72%);
    }
    .act-modal .modal-header .btn-close {
        margin-top: .2rem;
        box-shadow: none;
    }
    .act-modal .modal-title {
        color: var(--ecp-navy);
        font-weight: 800;
        letter-spacing: -.02em;
    }
    .act-modal .modal-body {
        padding: 1.35rem 1.5rem !important;
    }
    .act-modal .modal-footer {
        gap: .55rem;
        padding: .9rem 1.5rem 1.35rem !important;
        border-top: 1px solid #edf1f3 !important;
        background: #fbfcfd;
    }
    .act-modal .form-label {
        margin-bottom: .45rem;
        color: var(--ecp-navy);
        font-weight: 700;
    }
    .act-modal .form-control,
    .act-modal .form-select {
        min-height: 44px;
        border-color: #d9e1e6;
        border-radius: 10px;
    }
    .act-modal textarea.form-control {
        min-height: auto;
    }
    .act-modal .form-control:focus,
    .act-modal .form-select:focus {
        border-color: var(--act-modal-accent);
        box-shadow: 0 0 0 .2rem rgba(57, 169, 0, .12);
    }
    .act-modal .btn {
        min-height: 40px;
        border-radius: 999px;
        font-weight: 700;
        transition: transform .16s ease, box-shadow .16s ease, background-color .16s ease;
    }
    .act-modal .btn:not(:disabled):hover {
        transform: translateY(-1px);
        box-shadow: 0 5px 14px rgba(0, 26, 41, .12);
    }
    .act-modal-icon {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: var(--act-modal-tint);
        color: var(--act-modal-accent);
        font-size: 1.05rem;
    }
    #modalCambiarMomento {
        --act-modal-accent: #0070b8;
        --act-modal-tint: #eaf5ff;
    }
    #modalComponentes {
        --act-modal-accent: var(--ecp-green);
        --act-modal-tint: #f1faed;
    }
    #modalAccionComponente {
        --act-modal-accent: #0070b8;
        --act-modal-tint: #eaf5ff;
    }
    #modalConfirmActEstado {
        --act-modal-accent: #d28a00;
        --act-modal-tint: #fff7e6;
    }
    #modalEvidencia,
    #modalVerEvidencias {
        --act-modal-accent: var(--ecp-green);
        --act-modal-tint: #f1faed;
    }
    #modalCalificaciones {
        --act-modal-accent: #d28a00;
        --act-modal-tint: #fff7e6;
    }
    #modalAccionComponente .modal-dialog {
        max-width: 520px;
    }
    #modalAccionComponente .modal-body {
        padding-top: 1.1rem !important;
    }
    #modalAccionComponente .modal-body > .d-flex.justify-content-end {
        gap: .55rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #edf1f3;
    }
    #modalAccionComponente #accionComponenteDescription {
        line-height: 1.6;
    }
    #modalAccionComponente #encuestaComponenteSelector {
        padding: 1rem;
        border: 1px solid #cfe7f7;
        border-radius: 12px;
        background: #f4faff;
    }
    #modalAccionComponente #accionEncuestaGuardar {
        background: #0070b8;
        border-color: #0070b8;
    }
    #modalComponentes #compModalBody {
        min-height: 120px;
    }
    #modalComponentes #compModalBody .component-card,
    #modalComponentes #compModalBody .act-list-item {
        border: 1px solid #e4eaed;
        border-radius: 12px;
        background: #fff;
        transition: border-color .16s ease, box-shadow .16s ease;
    }
    #modalComponentes #compModalBody .component-card:hover,
    #modalComponentes #compModalBody .act-list-item:hover {
        border-color: #b7dca8;
        box-shadow: 0 5px 16px rgba(0, 26, 41, .07);
    }
    #modalEvidencia .modal-dialog {
        max-width: 820px;
    }
    #modalEvidencia .modal-body > .d-flex label {
        border-radius: 12px !important;
        border-color: #d9e1e6 !important;
        transition: border-color .16s ease, background-color .16s ease, box-shadow .16s ease;
    }
    #modalEvidencia .modal-body > .d-flex label:has(input:checked) {
        border-color: var(--ecp-green) !important;
        background: #f4fbf1;
        box-shadow: 0 0 0 2px rgba(57, 169, 0, .08);
    }
    #modalEvidencia input[type="file"] {
        overflow: hidden;
    }
    #modalEvidencia input[type="file"]::file-selector-button {
        min-height: 42px;
        margin: -.375rem .75rem -.375rem -.75rem;
        border: 0;
        border-right: 1px solid #d9e1e6;
        background: #f4fbf1;
        color: #287c00;
        font-weight: 600;
    }
    #modalVerEvidencias .modal-body,
    #modalCalificaciones .modal-body {
        min-height: 150px;
    }
    @media (max-width: 575.98px) {
        .act-modal .modal-header {
            padding: 1.1rem 1rem .9rem !important;
        }
        .act-modal .modal-body {
            padding: 1rem !important;
        }
        .act-modal .modal-footer {
            display: grid;
            grid-template-columns: 1fr;
            padding: .8rem 1rem 1rem !important;
        }
        .act-modal .modal-footer .btn,
        .act-modal .modal-body > .d-flex.justify-content-end .btn {
            width: 100%;
        }
        #modalEvidencia .modal-body > .d-flex.justify-content-end {
            display: grid !important;
            grid-template-columns: 1fr;
        }
        #modalEvidencia .modal-body > .d-flex {
            flex-direction: column;
        }
    }
</style>
@endpush

@section('dashboard-content')
<div class="act-page container-fluid px-3 py-3">

    {{-- Breadcrumb & Cabecera --}}
    <div class="actividades-header-card d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small fw-semibold mb-1" style="color: var(--ecp-green);">
                <i class="fas fa-list-check me-2"></i>Control ECP · Gestión de Actividades
            </div>
            <h2 class="h3 fw-bold mb-0">
                <i class="fas fa-list-check me-2" style="color:var(--ecp-green);"></i>Gestión de Actividades
            </h2>
            <p class="text-muted small mb-0">
                Control ECP &bull;
                <strong>{{ $stats['total'] }}</strong> actividades en total &bull;
                <strong>{{ $stats['activas'] }}</strong> activas
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            {{-- HU1: Registrar actividad --}}
            <button type="button" class="btn text-white rounded-pill px-3 btn-sm shadow-sm"
                    style="background-color: var(--ecp-green);" onclick="abrirModalCrear()">
                <i class="fas fa-plus me-1"></i> Registrar Actividad
            </button>
            <a href="{{ route('controlecp.administrador.momentos') }}"
               class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
                <i class="fas fa-star me-1"></i> Ver Momentos
            </a>
        </div>
    </div>

    {{-- Alertas Flash --}}
    <div id="alertActividades"></div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4"
             style="border-radius: 14px;" role="alert">
            <i class="fas fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- KPIs --}}
    <div class="row g-3 mb-4 justify-content-center">
        <div class="col-6 col-md-3">
            <div class="kpi-card" style="border-color:rgba(57,169,0,.35);">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="kpi-label">Total Actividades</span>
                    <div class="kpi-icon-wrap" style="background:rgba(57,169,0,.12);">
                        <i class="fas fa-list-check" style="color:var(--ecp-green);"></i>
                    </div>
                </div>
                <div class="kpi-value" style="color:var(--ecp-green);">{{ $stats['total'] }}</div>
                <div class="kpi-subtitle">Registradas en ECP</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card" style="border-color:rgba(30,112,0,.35);">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="kpi-label">Activas</span>
                    <div class="kpi-icon-wrap" style="background:#e8f8e8;">
                        <i class="fas fa-circle-check" style="color:#1e7000;"></i>
                    </div>
                </div>
                <div class="kpi-value text-success">{{ $stats['activas'] }}</div>
                <div class="kpi-subtitle">Disponibles</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card" style="border-color:rgba(192,57,43,.35);">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="kpi-label">Inactivas</span>
                    <div class="kpi-icon-wrap" style="background:#fdecea;">
                        <i class="fas fa-ban" style="color:#c0392b;"></i>
                    </div>
                </div>
                <div class="kpi-value text-danger">{{ $stats['inactivas'] }}</div>
                <div class="kpi-subtitle">No disponibles</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card" style="border-color:rgba(106,27,154,.35);">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="kpi-label">Momentos</span>
                    <div class="kpi-icon-wrap" style="background:#f4ecf7;">
                        <i class="fas fa-star" style="color:var(--ecp-purple);"></i>
                    </div>
                </div>
                <div class="kpi-value" style="color:var(--ecp-purple);">{{ $stats['momentos'] }}</div>
                <div class="kpi-subtitle">Pedagógicos ECP</div>
            </div>
        </div>
    </div>

    {{-- HU3: Listado agrupado por momento --}}
    @if($momentos->count() > 0)
        <div class="momentos-list" id="momentosActividades">
            @foreach($momentos as $momento)
                <section class="card border-0 shadow-sm momento-section">
                {{-- Encabezado del momento --}}
                <div class="momento-header"
                     style="background: linear-gradient(135deg, var(--ecp-navy) 0%, {{ $momento->color }} 100%);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width: 40px; height: 40px; background: rgba(255,255,255,.2); font-size: 1rem;">
                            <i class="fas {{ $momento->icono }}"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-6">{{ $momento->nombre }}</div>
                            <div class="small" style="opacity:.8;">{{ $momento->descripcion }}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill px-3 py-1 fw-bold"
                              style="background:rgba(255,255,255,.2); font-size:.82rem;">
                            {{ $momento->actividades->count() }} actividades
                        </span>
                        <button type="button" class="btn btn-sm rounded-pill px-3 fw-semibold"
                                style="background:rgba(255,255,255,.2); color:#fff; border:1.5px solid rgba(255,255,255,.4);"
                                onclick="abrirModalCrear({{ $momento->id }}, @js($momento->nombre))">
                            <i class="fas fa-plus me-1"></i> Agregar
                        </button>
                    </div>
                </div>

                {{-- Grid de tarjetas del momento --}}
                @if($momento->actividades->count() > 0)
                    <div class="act-grid-sm">
                        @foreach($momento->actividades_paginadas as $a)
                            @php
                                $estadoBadge = match($a->estado) {
                                    'Activo'    => 'badge-activo',
                                    'Inactivo'  => 'badge-inactivo',
                                    'Finalizado'=> 'badge-finalizado',
                                    default     => 'badge-inactivo',
                                };
                                $estadoIcon = match($a->estado) {
                                    'Activo'    => 'fa-circle-check',
                                    'Inactivo'  => 'fa-ban',
                                    'Finalizado'=> 'fa-flag-checkered',
                                    default     => 'fa-ban',
                                };
                                $datosActividad = [
                                    'id'               => $a->id,
                                    'nombre'           => $a->nombre,
                                    'descripcion'      => $a->descripcion,
                                    'objetivo'         => $a->objetivo,
                                    'duracion'         => $a->duracion,
                                    'duracion_min'     => $a->duracion_min,
                                    'metodologia'      => $a->metodologia,
                                    'momento_id'       => $a->momento_id,
                                    'tiene_asistencia' => $a->tiene_asistencia,
                                    'encuesta_id'      => $a->id_encuesta,
                                    'tiene_evidencia'  => $a->tiene_evidencia,
                                ];
                                $datosDetalleActividad = array_merge($datosActividad, [
                                    'numero'           => $a->numero,
                                    'estado'           => $a->estado,
                                    'momento'          => $momento->nombre,
                                    'tiene_asistencia' => (bool) $a->tiene_asistencia,
                                    'tiene_encuesta'   => (bool) $a->id_encuesta,
                                    'tiene_evidencia'  => (bool) $a->tiene_evidencia,
                                ]);
                            @endphp
                            <div class="act-card estado-{{ strtolower($a->estado) }}"
                                 style="border: 2px solid {{ $momento->color }}44; border-top: 4px solid {{ $momento->color }}; border-radius: 20px;"
                                 id="act-card-{{ $a->id }}">
                                <div class="p-3">
                                    {{-- Número + estado --}}
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="fw-bold rounded-2 px-2 py-1 small"
                                              style="background:{{ $momento->color }}22; color:{{ $momento->color }}; font-size:.72rem;">
                                            #{{ $a->numero }}
                                        </span>
                                        <span class="badge {{ $estadoBadge }} rounded-pill px-2 py-1" style="font-size:.7rem;">
                                            <i class="fas {{ $estadoIcon }} me-1"></i>{{ $a->estado }}
                                        </span>
                                    </div>

                                    {{-- Nombre y objetivo --}}
                                    <h6 class="fw-bold text-dark mb-1" style="font-size:.9rem; line-height:1.3;">
                                        {{ $a->nombre }}
                                    </h6>
                                    <p class="text-muted mb-2" style="font-size:.8rem; line-height:1.4;">
                                        {{ Str::limit($a->objetivo, 90) }}
                                    </p>

                                    {{-- Acciones rápidas de la actividad --}}
                                    <div class="d-flex flex-wrap gap-1 mb-3">
                                        <button type="button" class="comp-badge comp-{{ $a->tiene_asistencia ? 'on' : 'off' }}"
                                              onclick="abrirModalComponente('asistencia', {{ $a->id }}, @js($a->nombre))"
                                              style="color:#6A1B9A; border-color:#e1bee7;">
                                            <i class="fas fa-clipboard-list"></i> Asistencia
                                        </button>
                                        <button type="button" class="comp-badge comp-{{ $a->encuesta_id ? 'on' : 'off' }} comp-encuesta"
                                              onclick="abrirModalComponente('encuesta', {{ $a->id }}, @js($a->nombre), @js($a->id_encuesta))"
                                              >
                                            <i class="fas fa-clipboard-question"></i> Encuesta
                                        </button>
                                        <button type="button" class="comp-badge comp-{{ $a->tiene_evidencia ? 'on' : 'off' }}"
                                              onclick="abrirModalComponente('evidencia', {{ $a->id }}, @js($a->nombre))"
                                              style="color:#39A900; border-color:#c2eec2;">
                                            <i class="fas fa-photo-film"></i> Evidencia
                                        </button>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-2">
                                        <button class="btn btn-sm rounded-circle d-inline-flex align-items-center justify-content-center act-detail-icon"
                                                type="button" title="Ver detalle de actividad" aria-label="Ver detalle de actividad"
                                                onclick="abrirModalDetalle(@js($datosDetalleActividad))">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm rounded-circle d-inline-flex align-items-center justify-content-center act-rating-icon"
                                                type="button" title="Ver calificaciones" aria-label="Ver calificaciones"
                                                onclick="verCalificaciones({{ $a->id }}, @js($a->nombre))">
                                            <i class="fas fa-star" aria-hidden="true"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary rounded-circle d-inline-flex align-items-center justify-content-center"
                                                style="width:34px;height:34px;"
                                                type="button" title="Editar actividad" aria-label="Editar actividad"
                                                onclick="abrirModalEditar(@js($datosActividad))">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @if($momento->actividades_paginadas->hasPages())
                        <div class="act-pagination" style="--momento-color:{{ $momento->color ?: '#39A900' }};">
                            @php
                                $paginadorActividades = $momento->actividades_paginadas;
                            @endphp
                            @if ($paginadorActividades->hasPages())
                                <nav aria-label="Paginación de actividades">
                                    <ul class="pagination justify-content-center">
                                        @if ($paginadorActividades->onFirstPage())
                                            <li class="page-item disabled"><span class="page-link" aria-hidden="true">&lsaquo;</span></li>
                                        @else
                                            <li class="page-item"><a class="page-link" href="{{ $paginadorActividades->previousPageUrl() }}" aria-label="Página anterior">&lsaquo;</a></li>
                                        @endif
                                        @php
                                $ultimaPaginaMostrada = 0;
                            @endphp
                                        @for ($pagina = 1; $pagina <= $paginadorActividades->lastPage(); $pagina++)
                                            @if ($pagina === 1 || $pagina === $paginadorActividades->lastPage() || abs($pagina - $paginadorActividades->currentPage()) <= 1)
                                                @if ($ultimaPaginaMostrada > 0 && $pagina > $ultimaPaginaMostrada + 1)
                                                    <li class="page-item disabled"><span class="page-link">…</span></li>
                                                @endif
                                                <li class="page-item {{ $pagina === $paginadorActividades->currentPage() ? 'active' : '' }}">
                                                    <a class="page-link" href="{{ $paginadorActividades->url($pagina) }}" @if($pagina === $paginadorActividades->currentPage()) aria-current="page" @endif>{{ $pagina }}</a>
                                                </li>
                                                @php
                                    $ultimaPaginaMostrada = $pagina;
                                @endphp
                                            @endif
                                        @endfor
                                        @if ($paginadorActividades->hasMorePages())
                                            <li class="page-item"><a class="page-link" href="{{ $paginadorActividades->nextPageUrl() }}" aria-label="Página siguiente">&rsaquo;</a></li>
                                        @else
                                            <li class="page-item disabled"><span class="page-link" aria-hidden="true">&rsaquo;</span></li>
                                        @endif
                                    </ul>
                                </nav>
                            @endif
                        </div>
                    @endif
                @else
                    <div class="text-center py-4 text-muted small" style="background:#f8faf9;">
                        <i class="fas fa-list-check fa-2x mb-2 text-muted" style="opacity:.4;"></i>
                        <p class="mb-2">No hay actividades en este momento.</p>
                        <button type="button" class="btn btn-sm rounded-pill px-3 text-white"
                                style="background:{{ $momento->color }};"
                                onclick="abrirModalCrear({{ $momento->id }}, @js($momento->nombre))">
                            <i class="fas fa-plus me-1"></i> Agregar primera actividad
                        </button>
                    </div>
                @endif
                </section>
        @endforeach
        </div>
    @else
        <div class="text-center py-5">
            <i class="fas fa-list-check fa-3x mb-3" style="color:#d5dae2;"></i>
            <h5 class="fw-bold text-dark mb-2">No hay momentos ni actividades</h5>
            <p class="text-muted small mb-3">Primero crea los momentos pedagógicos, luego agrega actividades.</p>
            <a href="{{ route('controlecp.administrador.momentos') }}"
               class="btn text-white rounded-pill px-4" style="background:var(--ecp-green);">
                <i class="fas fa-star me-1"></i> Ir a Momentos
            </a>
        </div>
    @endif

</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- MODALES                                                         --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}

{{-- Modal: Detalle de actividad --}}
<div class="modal fade act-modal" id="modalDetalleActividad" tabindex="-1" aria-labelledby="modalDetalleActividadTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="modalDetalleActividadTitulo" style="color:#d63384;">
                        <i class="fas fa-eye me-2"></i>Detalle de actividad
                    </h5>
                    <p class="text-muted small mb-0" id="detalleActMomento">—</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                    <h6 class="fw-bold mb-0" id="detalleActNombre">—</h6>
                    <span class="badge rounded-pill" id="detalleActEstado">—</span>
                </div>
                <div class="mb-3">
                    <div class="small fw-semibold text-muted mb-1">Objetivo pedagógico</div>
                    <p class="mb-0" id="detalleActObjetivo">—</p>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-3" id="detalleActComponentes"></div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-0">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Crear / Editar actividad (HU1 / HU2 / HU4) --}}
<div class="modal fade act-modal" id="modalFormActividad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="act-modal-icon">
                        <i class="fas fa-list-check"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="formActModalTitle" style="color:var(--ecp-navy);">
                            Registrar Actividad
                        </h5>
                        <p class="text-muted small mb-0">Escuela Cultura de Paz &bull; Control ECP</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                {{-- HU2: Alerta de validación --}}
                <div id="formActAlert" class="alert alert-danger border-0 d-none" style="border-radius:12px; font-size:.87rem;">
                    <i class="fas fa-triangle-exclamation me-2"></i>
                    <span id="formActAlertMsg"></span>
                </div>

                <input type="hidden" id="formActId" value="">
                <div class="row g-3">

                    {{-- Nombre --}}
                    <div class="col-md-8">
                        <label class="form-label fw-semibold small" style="color:var(--ecp-navy);">
                            Nombre <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="formActNombre" class="form-control"
                               placeholder="Ej: Reconocimiento y Gestión Emocional" maxlength="200" required>
                    </div>

                    {{-- Momento --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small" style="color:var(--ecp-navy);">
                            Momento <span class="text-danger">*</span>
                        </label>
                        <select id="formActMomento" class="form-select" required>
                            <option value="">— Seleccionar —</option>
                            @foreach($momentos as $m)
                                <option value="{{ $m->id }}" data-color="{{ $m->color }}"
                                        @disabled($m->estado !== 'Activo')>
                                    {{ $m->nombre }}{{ $m->estado !== 'Activo' ? ' (Inactivo)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Objetivo --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small" style="color:var(--ecp-navy);">
                            Objetivo Pedagógico <span class="text-danger">*</span>
                        </label>
                        <textarea id="formActObjetivo" class="form-control" rows="2" maxlength="1000"
                                  placeholder="¿Qué logrará el aprendiz con esta actividad?"></textarea>
                    </div>

                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 mt-2 border-top">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn text-white rounded-pill px-4" id="btnGuardarActividad"
                            style="background-color: var(--ecp-green);" onclick="guardarActividad()">
                        <i class="fas fa-floppy-disk me-1"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Cambiar momento (HU5) --}}
<div class="modal fade act-modal" id="modalCambiarMomento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" style="color:var(--ecp-navy);">
                    <i class="fas fa-right-left me-2 text-info"></i>Cambiar Momento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-3">
                    Actividad: <strong id="cambiarMomentoNombre">—</strong>
                </p>
                <label class="form-label fw-semibold small" style="color:var(--ecp-navy);">
                    Selecciona el nuevo momento:
                </label>
                <select id="cambiarMomentoSelect" class="form-select">
                    @foreach($momentos as $m)
                        <option value="{{ $m->id }}" @disabled($m->estado !== 'Activo')>
                            {{ $m->nombre }}{{ $m->estado !== 'Activo' ? ' (Inactivo)' : '' }}
                        </option>
                    @endforeach
                </select>
                <input type="hidden" id="cambiarMomentoActividadId" value="">
                <div id="cambiarMomentoAlert" class="alert alert-danger border-0 d-none mt-3 small" style="border-radius:10px;"></div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-0">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn text-white rounded-pill px-4" style="background:var(--ecp-blue);"
                        onclick="ejecutarCambiarMomento()">
                    <i class="fas fa-right-left me-1"></i> Mover Actividad
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Ver componentes (HU10) --}}
<div class="modal fade act-modal" id="modalComponentes" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div>
                    <h5 class="modal-title fw-bold mb-0" style="color:var(--ecp-navy);">
                        <i class="fas fa-puzzle-piece me-2" style="color:var(--ecp-green);"></i>Componentes de la Actividad
                    </h5>
                    <p class="text-muted small mb-0" id="compModalSubtitle">—</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="compModalBody">
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-success"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Acciones rápidas de componentes --}}
<div class="modal fade act-modal" id="modalAccionComponente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div>
                    <div class="d-flex align-items-center gap-3">
                        <span class="act-modal-icon" id="accionComponenteIconWrap">
                            <i class="fas fa-clipboard-question" id="accionComponenteIcon"></i>
                        </span>
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="accionComponenteTitle"></h5>
                            <p class="text-muted small mb-0" id="accionComponenteSubtitle"></p>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-4" id="accionComponenteDescription"></p>
                <div id="encuestaComponenteSelector" class="d-none mb-4">
                    <label class="form-label fw-semibold small">Encuesta activa</label>
                    <select id="accionEncuestaSelect" class="form-select">
                        <option value="">— Seleccionar encuesta —</option>
                        @foreach($encuestasActivas as $encuesta)
                            <option value="{{ $encuesta->id_encuesta }}">{{ $encuesta->nombre }}</option>
                        @endforeach
                    </select>
                    <div id="accionEncuestaAlert" class="text-danger small mt-2 d-none"></div>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
                    <a id="accionComponenteLink" href="#" class="btn text-white rounded-pill px-4">
                        <i class="fas fa-arrow-up-right-from-square me-1"></i>Ir al módulo
                    </a>
                    <button id="accionEncuestaGuardar" type="button" class="btn text-white rounded-pill px-4 d-none"
                            onclick="guardarEncuestaActividad()">
                        <i class="fas fa-save me-1"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Confirmar estado / finalizar (HU11/HU12/HU13) --}}
<div class="modal fade act-modal" id="modalConfirmActEstado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4 text-center justify-content-center position-relative">
                <button type="button" class="btn-close position-absolute end-0 top-0 mt-3 me-3" data-bs-dismiss="modal"></button>
                <div id="confirmActIcon" class="rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                     style="width:65px;height:65px;font-size:1.8rem;"></div>
            </div>
            <div class="modal-body p-4 text-center">
                <h5 class="fw-bold mb-2" id="confirmActTitle" style="color:var(--ecp-navy);"></h5>
                <p class="text-muted small mb-4" id="confirmActDesc"></p>
                <div class="d-flex justify-content-center gap-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn rounded-pill px-4 text-white" id="btnConfirmActEstado"
                            onclick="ejecutarAccionActividad()">Confirmar</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Subir evidencia (HU15/HU16) --}}
<div class="modal fade act-modal" id="modalEvidencia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius:20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div>
                    <h5 class="modal-title fw-bold mb-0" style="color:var(--ecp-navy);">
                        <i class="fas fa-upload me-2" style="color:var(--ecp-green);"></i>Subir Evidencia
                    </h5>
                    <p class="text-muted small mb-0" id="evidModalSubtitle">—</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="evidAlert" class="alert alert-danger border-0 d-none small" style="border-radius:10px;"></div>
                <input type="hidden" id="evidActividadId" value="">

                {{-- Tipo de evidencia --}}
                <div class="d-flex gap-3 mb-4">
                    <label class="evid-option d-flex align-items-center gap-2 p-3 rounded-3 border flex-fill" style="cursor:pointer;">
                        <input type="radio" name="evidTipo" value="foto" class="form-check-input mt-0" checked
                               onchange="toggleEvidTipo()">
                        <div>
                            <div class="fw-semibold small"><i class="fas fa-image me-1 text-success"></i> Foto</div>
                            <div class="text-muted" style="font-size:.75rem;">JPG o PNG, máx. 5 MB</div>
                        </div>
                    </label>
                    <label class="evid-option d-flex align-items-center gap-2 p-3 rounded-3 border flex-fill" style="cursor:pointer;">
                        <input type="radio" name="evidTipo" value="video" class="form-check-input mt-0"
                               onchange="toggleEvidTipo()">
                        <div>
                            <div class="fw-semibold small"><i class="fas fa-video me-1 text-info"></i> Enlace de Video</div>
                            <div class="text-muted" style="font-size:.75rem;">YouTube, Drive, etc.</div>
                        </div>
                    </label>
                </div>

                <div id="evidFotoWrap">
                    <label class="form-label fw-semibold small">Archivo de foto (JPG/PNG) <span class="text-danger">*</span></label>
                    <input type="file" id="evidArchivo" class="form-control" accept=".jpg,.jpeg,.png">
                </div>
                <div id="evidVideoWrap" class="d-none">
                    <label class="form-label fw-semibold small">URL del video <span class="text-danger">*</span></label>
                    <input type="url" id="evidEnlace" class="form-control"
                           placeholder="https://www.youtube.com/watch?v=...">
                </div>

                <div class="mt-3">
                    <label class="form-label fw-semibold small">Ficha asociada <span class="text-danger">*</span></label>
                    <select id="evidFichaId" class="form-select">
                        <option value="">— Seleccionar ficha —</option>
                        @php
                            try {
                                $fichasEvidencia = \Modules\SICA\Entities\Course::where('status','Activo')
                                    ->with('program')->orderBy('code')->get();
                            } catch(\Exception $e) { $fichasEvidencia = collect(); }
                        @endphp
                        @foreach($fichasEvidencia as $f)
                            <option value="{{ $f->id }}">{{ $f->code }} — {{ $f->program?->name ?? 'Sin programa' }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-3">
                    <label class="form-label fw-semibold small">Descripción (opcional)</label>
                    <textarea id="evidDescripcion" class="form-control form-control-sm" rows="2"
                              placeholder="Describe brevemente esta evidencia..."></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 mt-2 border-top">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn text-white rounded-pill px-4" id="btnSubirEvidencia"
                            style="background:var(--ecp-green);" onclick="ejecutarSubirEvidencia()">
                        <i class="fas fa-upload me-1"></i> Subir Evidencia
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Ver evidencias (HU17) --}}
<div class="modal fade act-modal" id="modalVerEvidencias" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius:20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div>
                    <h5 class="modal-title fw-bold mb-0" style="color:var(--ecp-navy);">
                        <i class="fas fa-photo-film me-2" style="color:var(--ecp-green);"></i>Evidencias de la Actividad
                    </h5>
                    <p class="text-muted small mb-0" id="verEvidSubtitle">—</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="verEvidBody">
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-success"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Calificaciones (HU18) --}}
<div class="modal fade act-modal" id="modalCalificaciones" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius:20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div>
                    <h5 class="modal-title fw-bold mb-0" style="color:var(--ecp-navy);">
                        <i class="fas fa-star me-2 text-warning"></i>Calificaciones
                    </h5>
                    <p class="text-muted small mb-0" id="califSubtitle">—</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="califBody">
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-warning"></i>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const actividadesCsrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const BASE_URL  = "{{ url('/control-ecp/administrador/actividades') }}";
const ENCUESTA_URL = `${BASE_URL}/__ID__/encuesta`;

function abrirModalDetalle(data) {
    document.getElementById('detalleActNombre').textContent = data.nombre || 'Sin nombre';
    document.getElementById('detalleActMomento').textContent = `Actividad #${data.numero} · ${data.momento}`;
    document.getElementById('detalleActEstado').textContent = data.estado || 'Sin estado';
    document.getElementById('detalleActObjetivo').textContent = data.objetivo || 'Sin objetivo registrado.';
    const componentes = document.getElementById('detalleActComponentes');
    componentes.replaceChildren();
    [
        ['Asistencia', data.tiene_asistencia],
        ['Encuesta', data.tiene_encuesta],
        ['Evidencia', data.tiene_evidencia],
    ].forEach(([nombre, activo]) => {
        const badge = document.createElement('span');
        badge.className = `badge rounded-pill ${activo ? 'bg-success-subtle text-success' : 'bg-light text-secondary border'}`;
        badge.textContent = `${nombre}: ${activo ? 'Activa' : 'No configurada'}`;
        componentes.appendChild(badge);
    });

    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetalleActividad')).show();
}

// ── HU1: Abrir modal crear ────────────────────────────────────────────────────
function abrirModalCrear(momentoId = null, momentoNombre = null) {
    document.getElementById('formActId').value = '';
    document.getElementById('formActNombre').value = '';
    document.getElementById('formActObjetivo').value = '';
    document.getElementById('formActAlert').classList.add('d-none');
    document.getElementById('formActModalTitle').textContent = 'Registrar Actividad';

    if (momentoId) {
        document.getElementById('formActMomento').value = momentoId;
    }

    new bootstrap.Modal(document.getElementById('modalFormActividad')).show();
}

// ── HU4: Abrir modal editar ───────────────────────────────────────────────────
function abrirModalEditar(data) {
    document.getElementById('formActId').value              = data.id;
    document.getElementById('formActNombre').value          = data.nombre;
    document.getElementById('formActObjetivo').value        = data.objetivo ?? '';
    document.getElementById('formActMomento').value         = data.momento_id;
    document.getElementById('formActAlert').classList.add('d-none');
    document.getElementById('formActModalTitle').textContent = 'Editar Actividad';
    new bootstrap.Modal(document.getElementById('modalFormActividad')).show();
}

// ── HU1 / HU4: Guardar ───────────────────────────────────────────────────────
async function guardarActividad() {
    const id           = document.getElementById('formActId').value.trim();
    const nombre       = document.getElementById('formActNombre').value.trim();
    const momentoId    = document.getElementById('formActMomento').value;
    const objetivo     = document.getElementById('formActObjetivo').value.trim();
    // HU2 — Validar campos obligatorios
    if (!nombre) { mostrarAlertaForm('El nombre de la actividad es obligatorio.'); return; }
    if (!momentoId) { mostrarAlertaForm('Debes seleccionar un momento.'); return; }
    if (!objetivo) { mostrarAlertaForm('El objetivo pedagógico es obligatorio.'); return; }
    const btn = document.getElementById('btnGuardarActividad');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';

    const isEdit = id !== '';
    const url    = isEdit ? `${BASE_URL}/${id}` : BASE_URL;
    const method = isEdit ? 'PUT' : 'POST';

    try {
        const resp = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': actividadesCsrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({
                nombre, momento_id: momentoId, objetivo,
                duracion: '2 Horas', duracion_min: 120,
                tiene_evidencia: true,
                id_encuesta: null,
            }),
        });

        const data = await resp.json();
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-floppy-disk me-1"></i> Guardar';

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalFormActividad')).hide();
            mostrarAlerta(data.message, 'success');
            setTimeout(() => window.location.reload(), 800);
        } else {
            mostrarAlertaForm(data.message ?? 'Error al guardar.');
        }
    } catch (e) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-floppy-disk me-1"></i> Guardar';
        mostrarAlertaForm('Error de conexión: ' + e.message);
    }
}

// ── HU5: Cambiar momento ──────────────────────────────────────────────────────
function abrirModalCambiarMomento(actId, momentoActualId, nombre) {
    document.getElementById('cambiarMomentoActividadId').value = actId;
    document.getElementById('cambiarMomentoNombre').textContent = nombre;
    document.getElementById('cambiarMomentoSelect').value = momentoActualId;
    document.getElementById('cambiarMomentoAlert').classList.add('d-none');
    new bootstrap.Modal(document.getElementById('modalCambiarMomento')).show();
}

async function ejecutarCambiarMomento() {
    const actId     = document.getElementById('cambiarMomentoActividadId').value;
    const momentoId = document.getElementById('cambiarMomentoSelect').value;
    const alertEl   = document.getElementById('cambiarMomentoAlert');
    alertEl.classList.add('d-none');

    try {
        const resp = await fetch(`${BASE_URL}/${actId}/cambiar-momento`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': actividadesCsrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ momento_id: momentoId }),
        });
        const data = await resp.json();
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalCambiarMomento')).hide();
            mostrarAlerta(data.message, 'success');
            setTimeout(() => window.location.reload(), 800);
        } else {
            alertEl.textContent = data.message;
            alertEl.classList.remove('d-none');
        }
    } catch (e) {
        alertEl.textContent = 'Error: ' + e.message;
        alertEl.classList.remove('d-none');
    }
}

// ── HU10: Ver componentes ─────────────────────────────────────────────────────
let encuestaActividadId = null;

function abrirModalComponente(tipo, actId, nombre, encuestaId = null) {
    if (tipo === 'evidencia') {
        abrirModalEvidencia(actId, nombre);
        return;
    }

    const config = tipo === 'asistencia'
        ? {
            title: 'Registrar asistencia',
            description: 'Desde el módulo de asistencia podrás consultar y gestionar el registro de los aprendices.',
            color: '#6A1B9A',
            icon: 'fa-clipboard-list',
            url: "{{ route('controlecp.administrador.asistencia') }}",
        }
        : {
            title: 'Seleccionar encuesta',
            description: 'Elige la encuesta activa que deseas asociar a esta actividad.',
            color: 'var(--ecp-blue)',
            icon: 'fa-clipboard-question',
            url: '#',
        };

    document.getElementById('accionComponenteTitle').textContent = config.title;
    document.getElementById('accionComponenteSubtitle').textContent = nombre;
    document.getElementById('accionComponenteDescription').textContent = config.description;
    const modal = document.getElementById('modalAccionComponente');
    modal.style.setProperty('--act-modal-accent', config.color);
    modal.style.setProperty('--act-modal-tint', tipo === 'asistencia' ? '#f6effa' : '#eaf5ff');
    document.getElementById('accionComponenteIcon').className = 'fas ' + config.icon;
    document.getElementById('accionComponenteIconWrap').style.color = config.color;
    const link = document.getElementById('accionComponenteLink');
    link.href = config.url;
    link.style.backgroundColor = config.color;
    const selector = document.getElementById('encuestaComponenteSelector');
    const guardar = document.getElementById('accionEncuestaGuardar');
    encuestaActividadId = tipo === 'encuesta' ? actId : null;
    selector.classList.toggle('d-none', tipo !== 'encuesta');
    guardar.classList.toggle('d-none', tipo !== 'encuesta');
    link.classList.toggle('d-none', tipo === 'encuesta');
    if (tipo === 'encuesta') {
        document.getElementById('accionEncuestaSelect').value = encuestaId ?? '';
        document.getElementById('accionEncuestaAlert').classList.add('d-none');
    }
    new bootstrap.Modal(document.getElementById('modalAccionComponente')).show();
}

async function guardarEncuestaActividad() {
    const encuestaId = document.getElementById('accionEncuestaSelect').value;
    const alertEl = document.getElementById('accionEncuestaAlert');
    if (!encuestaId) {
        alertEl.textContent = 'Selecciona una encuesta.';
        alertEl.classList.remove('d-none');
        return;
    }

    try {
        const resp = await fetch(ENCUESTA_URL.replace('__ID__', encuestaActividadId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': actividadesCsrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ id_encuesta: encuestaId }),
        });
        const data = await resp.json();
        if (!resp.ok || !data.success) {
            alertEl.textContent = data.message ?? 'No se pudo asociar la encuesta.';
            alertEl.classList.remove('d-none');
            return;
        }
        bootstrap.Modal.getInstance(document.getElementById('modalAccionComponente')).hide();
        mostrarAlerta(data.message, 'success');
        setTimeout(() => window.location.reload(), 800);
    } catch (e) {
        alertEl.textContent = 'Error de conexión: ' + e.message;
        alertEl.classList.remove('d-none');
    }
}

async function verComponentes(actId) {
    document.getElementById('compModalSubtitle').textContent = 'Cargando...';
    document.getElementById('compModalBody').innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-success"></i></div>';
    new bootstrap.Modal(document.getElementById('modalComponentes')).show();

    try {
        const resp = await fetch(`${BASE_URL}/${actId}/componentes`, { headers: { 'Accept': 'application/json' } });
        const data = await resp.json();

        if (data.success) {
            document.getElementById('compModalSubtitle').textContent = data.actividad_nombre;
            const comps = data.componentes;
            document.getElementById('compModalBody').innerHTML = `
                <div class="row g-3">
                    ${Object.entries(comps).map(([key, c]) => `
                        <div class="col-12">
                            <div class="p-3 rounded-3 border d-flex align-items-start gap-3"
                                 style="background:${c.activo ? '#f8faf9' : '#f5f5f5'}; opacity:${c.activo ? 1 : .6}">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width:42px;height:42px;background:${c.color}22;color:${c.color};font-size:1.1rem;">
                                    <i class="fas ${c.icono}"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark text-capitalize">${key}
                                        <span class="badge rounded-pill ms-2 px-2 py-1" style="font-size:.7rem;
                                              background:${c.activo ? '#e8f8e8' : '#f5f5f5'};
                                              color:${c.activo ? '#1e7000' : '#666'};
                                              border:1px solid ${c.activo ? '#c2eec2' : '#ddd'};">
                                            ${c.activo ? 'Activo' : 'Inactivo'}
                                        </span>
                                        ${c.encuesta ? `<span class="text-muted small fw-normal"> — ${c.encuesta}</span>` : ''}
                                    </div>
                                    <div class="text-muted small mt-1">${c.descripcion}</div>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }
    } catch (e) {
        document.getElementById('compModalBody').innerHTML = `<div class="text-danger small">Error: ${e.message}</div>`;
    }
}

// ── HU11 / HU12 / HU13: Confirmar estado / finalizar ─────────────────────────
let _actAccionId   = null;
let _actAccionTipo = null;

function confirmarEstado(id, accion, nombre) {
    _actAccionId   = id;
    _actAccionTipo = accion;
    const icon  = document.getElementById('confirmActIcon');
    const title = document.getElementById('confirmActTitle');
    const desc  = document.getElementById('confirmActDesc');
    const btn   = document.getElementById('btnConfirmActEstado');

    if (accion === 'desactivar') {
        icon.style.cssText = 'width:65px;height:65px;font-size:1.8rem;background:#fdecea;color:#c0392b;';
        icon.innerHTML = '<i class="fas fa-ban"></i>';
        title.textContent = 'Desactivar Actividad';
        desc.textContent = `¿Desactivar "${nombre}"? Se retirará de las opciones activas pero su historial se conserva.`;
        btn.style.cssText = 'background:#dc3545;border-color:#dc3545;';
    } else {
        icon.style.cssText = 'width:65px;height:65px;font-size:1.8rem;background:#e8f8e8;color:#1e7000;';
        icon.innerHTML = '<i class="fas fa-circle-check"></i>';
        title.textContent = 'Activar Actividad';
        desc.textContent = `¿Reactivar "${nombre}"? Quedará disponible para programar.`;
        btn.style.cssText = 'background:#39A900;border-color:#39A900;';
    }
    new bootstrap.Modal(document.getElementById('modalConfirmActEstado')).show();
}

function confirmarFinalizar(id, nombre) {
    _actAccionId   = id;
    _actAccionTipo = 'finalizar';
    const icon  = document.getElementById('confirmActIcon');
    icon.style.cssText = 'width:65px;height:65px;font-size:1.8rem;background:#e8f0fe;color:#3949ab;';
    icon.innerHTML = '<i class="fas fa-flag-checkered"></i>';
    document.getElementById('confirmActTitle').textContent = 'Finalizar Actividad';
    document.getElementById('confirmActDesc').textContent  =
        `¿Finalizar "${nombre}"? Se validará que tenga asistencia registrada. Una vez finalizada se habilita la encuesta para los aprendices.`;
    document.getElementById('btnConfirmActEstado').style.cssText = 'background:#3949ab;border-color:#3949ab;';
    new bootstrap.Modal(document.getElementById('modalConfirmActEstado')).show();
}

async function ejecutarAccionActividad() {
    const accionMap = { desactivar:'desactivar', activar:'activar', finalizar:'finalizar' };
    const url = `${BASE_URL}/${_actAccionId}/${accionMap[_actAccionTipo]}`;

    try {
        const resp = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': actividadesCsrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({}),
        });
        const data = await resp.json();
        bootstrap.Modal.getInstance(document.getElementById('modalConfirmActEstado')).hide();
        if (data.success) {
            mostrarAlerta(data.message, 'success');
            setTimeout(() => window.location.reload(), 900);
        } else {
            mostrarAlerta(data.message, 'warning');
        }
    } catch (e) {
        mostrarAlerta('Error: ' + e.message, 'danger');
    }
}

// ── HU15: Subir evidencia ─────────────────────────────────────────────────────
function abrirModalEvidencia(actId, nombre) {
    document.getElementById('evidActividadId').value = actId;
    document.getElementById('evidModalSubtitle').textContent = nombre;
    document.getElementById('evidAlert').classList.add('d-none');
    document.getElementById('evidArchivo').value  = '';
    document.getElementById('evidEnlace').value   = '';
    document.getElementById('evidDescripcion').value = '';
    document.querySelector('input[name="evidTipo"][value="foto"]').checked = true;
    toggleEvidTipo();
    new bootstrap.Modal(document.getElementById('modalEvidencia')).show();
}

function toggleEvidTipo() {
    const tipo = document.querySelector('input[name="evidTipo"]:checked')?.value;
    document.getElementById('evidFotoWrap').classList.toggle('d-none', tipo !== 'foto');
    document.getElementById('evidVideoWrap').classList.toggle('d-none', tipo !== 'video');
}

async function ejecutarSubirEvidencia() {
    const actId     = document.getElementById('evidActividadId').value;
    const tipo      = document.querySelector('input[name="evidTipo"]:checked')?.value;
    const fichaId   = document.getElementById('evidFichaId').value;
    const desc      = document.getElementById('evidDescripcion').value.trim();
    const alertEl   = document.getElementById('evidAlert');
    alertEl.classList.add('d-none');

    if (!fichaId) { alertEl.textContent = 'Debes seleccionar una ficha.'; alertEl.classList.remove('d-none'); return; }

    const btn = document.getElementById('btnSubirEvidencia');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Subiendo...';

    const formData = new FormData();
    formData.append('_method', 'POST');
    formData.append('tipo', tipo);
    formData.append('course_id', fichaId);
    formData.append('descripcion', desc);

    if (tipo === 'foto') {
        const archivo = document.getElementById('evidArchivo').files[0];
        if (!archivo) {
            alertEl.textContent = 'Selecciona una imagen JPG o PNG.';
            alertEl.classList.remove('d-none');
            btn.disabled = false; btn.innerHTML = '<i class="fas fa-upload me-1"></i> Subir Evidencia';
            return;
        }
        // HU16 — Validar tamaño y tipo en el front
        if (!['image/jpeg','image/jpg','image/png'].includes(archivo.type)) {
            alertEl.textContent = 'Solo se aceptan fotos JPG o PNG.'; alertEl.classList.remove('d-none');
            btn.disabled = false; btn.innerHTML = '<i class="fas fa-upload me-1"></i> Subir Evidencia';
            return;
        }
        if (archivo.size > 5 * 1024 * 1024) {
            alertEl.textContent = 'La foto no puede superar los 5 MB.'; alertEl.classList.remove('d-none');
            btn.disabled = false; btn.innerHTML = '<i class="fas fa-upload me-1"></i> Subir Evidencia';
            return;
        }
        formData.append('archivo', archivo);
    } else {
        const enlace = document.getElementById('evidEnlace').value.trim();
        if (!enlace) {
            alertEl.textContent = 'Ingresa el enlace del video.'; alertEl.classList.remove('d-none');
            btn.disabled = false; btn.innerHTML = '<i class="fas fa-upload me-1"></i> Subir Evidencia';
            return;
        }
        formData.append('enlace', enlace);
    }

    try {
        const resp = await fetch(`${BASE_URL}/${actId}/evidencias`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': actividadesCsrfToken, 'Accept': 'application/json' },
            body: formData,
        });
        const data = await resp.json();
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-upload me-1"></i> Subir Evidencia';

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalEvidencia')).hide();
            mostrarAlerta(data.message, 'success');
        } else {
            alertEl.textContent = data.message ?? 'Error al subir.'; alertEl.classList.remove('d-none');
        }
    } catch (e) {
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-upload me-1"></i> Subir Evidencia';
        alertEl.textContent = 'Error: ' + e.message; alertEl.classList.remove('d-none');
    }
}

// ── HU17: Ver evidencias ──────────────────────────────────────────────────────
async function verEvidencias(actId, nombre) {
    document.getElementById('verEvidSubtitle').textContent = nombre;
    document.getElementById('verEvidBody').innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x" style="color:var(--ecp-purple);"></i></div>';
    new bootstrap.Modal(document.getElementById('modalVerEvidencias')).show();

    try {
        const resp = await fetch(`${BASE_URL}/${actId}/evidencias`, { headers: { 'Accept': 'application/json' } });
        const data = await resp.json();

        if (!data.success || data.evidencias.length === 0) {
            document.getElementById('verEvidBody').innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-photo-film fa-2x mb-2" style="opacity:.4;"></i>
                    <p class="small mb-0">No hay evidencias registradas para esta actividad.</p>
                </div>`;
            return;
        }

        document.getElementById('verEvidBody').innerHTML = `
            <p class="small text-muted mb-3"><strong>${data.total_evidencias}</strong> evidencia(s) registrada(s).</p>
            <div class="row g-3">
                ${data.evidencias.map(e => `
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm" style="border-radius:14px;overflow:hidden;">
                            ${e.tipo === 'foto'
                                ? `<img src="${e.url}" class="w-100" style="max-height:180px;object-fit:cover;" alt="Evidencia">`
                                : `<div class="d-flex align-items-center justify-content-center p-3" style="background:#f0f8ff;min-height:80px;">
                                       <a href="${e.url}" target="_blank" class="btn btn-outline-primary rounded-pill btn-sm">
                                           <i class="fas fa-play me-1"></i> Ver Video
                                       </a>
                                   </div>`
                            }
                            <div class="p-2">
                                <div class="small text-muted">${e.descripcion || 'Sin descripción'}</div>
                                <div class="d-flex justify-content-between mt-1" style="font-size:.72rem;color:#888;">
                                    <span><i class="fas fa-user me-1"></i>${e.usuario}</span>
                                    <span><i class="far fa-calendar me-1"></i>${e.fecha_registro}</span>
                                </div>
                                <div style="font-size:.72rem;color:#888;"><i class="fas fa-id-card me-1"></i>${e.ficha}</div>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    } catch (e) {
        document.getElementById('verEvidBody').innerHTML = `<div class="text-danger small">Error: ${e.message}</div>`;
    }
}

// ── HU18: Calificaciones ──────────────────────────────────────────────────────
async function verCalificaciones(actId, nombre) {
    document.getElementById('califSubtitle').textContent = nombre;
    document.getElementById('califBody').innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-warning"></i></div>';
    new bootstrap.Modal(document.getElementById('modalCalificaciones')).show();

    try {
        const resp = await fetch(`${BASE_URL}/${actId}/calificaciones`, { headers: { 'Accept': 'application/json' } });
        const data = await resp.json();

        if (!data.success || data.total === 0) {
            document.getElementById('califBody').innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-star fa-2x mb-2" style="opacity:.3;"></i>
                    <p class="small mb-0">Aún no hay calificaciones para esta actividad.</p>
                </div>`;
            return;
        }

        const estrellas = (n) => Array.from({length:5}, (_,i) =>
            `<i class="fas fa-star" style="color:${i < n ? '#f59e0b' : '#e5e7eb'};font-size:.8rem;"></i>`
        ).join('');

        document.getElementById('califBody').innerHTML = `
            <div class="d-flex align-items-center gap-4 mb-4 p-3 rounded-3" style="background:#fffbeb;border:1px solid #fde68a;">
                <div class="text-center">
                    <div style="font-size:3rem;font-weight:800;color:#d97706;line-height:1;">${data.promedio}</div>
                    <div class="mt-1">${estrellas(Math.round(data.promedio))}</div>
                    <div class="small fw-semibold text-dark mt-1">Promedio grupal</div>
                    <div class="small text-muted">${data.total} aprendices calificaron</div>
                </div>
                <div class="flex-fill">
                    ${data.distribucion.map(d => `
                        <div class="d-flex align-items-center gap-2 mb-1" style="font-size:.82rem;">
                            <span style="width:15px;text-align:right;">${d.estrellas}</span>
                            <i class="fas fa-star text-warning"></i>
                            <div class="flex-fill" style="height:8px;background:#f3f4f6;border-radius:4px;overflow:hidden;">
                                <div style="height:100%;width:${d.pct}%;background:#f59e0b;border-radius:4px;"></div>
                            </div>
                            <span class="text-muted" style="width:30px;">${d.cantidad}</span>
                        </div>
                    `).join('')}
                </div>
            </div>
            <div class="d-flex flex-column gap-2 mt-2">
                ${data.comentarios.map(c => `
                    <div class="p-3 rounded-3 border" style="background:#fafafa;">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div class="fw-semibold small text-dark">${c.aprendiz}</div>
                            <div>${estrellas(c.estrellas)}</div>
                        </div>
                        ${c.comentario ? `<div class="small text-muted">${c.comentario}</div>` : ''}
                        <div style="font-size:.72rem;color:#aaa;" class="mt-1">
                            Ficha ${c.ficha} &bull; ${c.fecha}
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    } catch (e) {
        document.getElementById('califBody').innerHTML = `<div class="text-danger small">Error: ${e.message}</div>`;
    }
}

// ── Utilidades ────────────────────────────────────────────────────────────────
function mostrarAlertaForm(msg) {
    const el = document.getElementById('formActAlert');
    document.getElementById('formActAlertMsg').textContent = msg;
    el.classList.remove('d-none');
}

function mostrarAlerta(msg, tipo = 'success') {
    const el   = document.getElementById('alertActividades');
    if (!el) return;
    const icon = tipo === 'success' ? 'fa-circle-check' : (tipo === 'info' ? 'fa-circle-info' : 'fa-triangle-exclamation');
    el.innerHTML = `
        <div class="alert alert-${tipo} alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius:14px;">
            <i class="fas ${icon} me-2"></i> ${msg}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
    setTimeout(() => {
        const a = el.querySelector('.alert');
        if (a) { a.classList.remove('show'); setTimeout(() => { el.innerHTML = ''; }, 300); }
    }, 5000);
}
</script>
@endpush
