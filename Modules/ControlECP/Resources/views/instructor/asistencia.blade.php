@extends('controlecp::components.layouts.dashboard')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    .asistencia-page { font-family: 'Outfit', sans-serif; min-width: 0; }
    .asistencia-hero {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1.5rem 2rem;
        border-radius: 24px;
        background: linear-gradient(120deg, #001A29, #00324D);
        color: #fff;
    }
    .asistencia-hero .hero-eyebrow {
        color: #62E31D;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
    }
    .asistencia-hero h2 {
        margin: .35rem 0 .25rem;
        color: #fff;
        font-family: 'Outfit', sans-serif;
        font-size: 2rem;
        font-weight: 800;
    }
    .asistencia-hero p { color: #c7d4da; }
    .asistencia-hero a { color: #39A900; }
    .kpi-asistencia {
        padding: 1rem 1.25rem;
        border: 1px solid rgba(0, 0, 0, .05);
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 4px 16px rgba(0, 26, 41, .06);
        transition: transform .18s;
    }
    .kpi-asistencia:hover { transform: translateY(-2px); }
    .asistencia-filters {
        display: grid;
        grid-template-columns: minmax(150px, 1fr) minmax(180px, 1.4fr) minmax(150px, 1fr) repeat(2, minmax(140px, 1fr)) 150px;
        gap: .65rem;
        align-items: end;
    }
    .asistencia-filter-card {
        padding: 1rem 1.15rem;
        margin-bottom: 1.5rem;
        border-radius: 18px;
        background: #001A29;
        box-shadow: 0 5px 20px rgba(0, 26, 41, .08);
    }
    .asistencia-filters .form-label {
        display: block;
        margin: 0 0 .3rem;
        color: #fff;
        font-size: .75rem;
        font-weight: 700;
    }
    .asistencia-filters .form-select,
    .asistencia-filters .form-control {
        width: 100%;
        min-width: 0;
        height: 44px;
        padding: .55rem .8rem;
        border: 1px solid #fff;
        border-radius: 10px;
        background-color: #fff;
        color: #001A29;
        font-size: .88rem;
        box-shadow: none;
    }
    .asistencia-filter-actions { display: flex; gap: .5rem; min-width: 0; }
    .asistencia-filter-actions .asistencia-action-button {
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
    .asistencia-filter-actions .asistencia-action-button:hover { background: #2e8b00; color: #fff; }
    .asistencia-filter-reset {
        display: grid;
        flex: 0 0 48px;
        height: 44px;
        place-items: center;
        padding: 0;
        border: 1px solid #fff;
        border-radius: 10px;
        background: #fff;
        color: #001A29;
    }
    .asistencia-filter-reset:hover { background: #f1f5f9; color: #001A29; }
    .asistencia-row-actions {
        display: flex;
        justify-content: flex-end;
        gap: .4rem;
        white-space: nowrap;
    }
    .asistencia-row-action {
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
    .asistencia-row-action:hover,
    .asistencia-row-action:focus-visible { transform: translateY(-2px); box-shadow: 0 3px 8px rgba(0, 26, 41, .14); }
    .asistencia-row-action--view { border-color: #c9e6fb; background: #eaf5ff; color: #0876bd; }
    .asistencia-row-action--view:hover { border-color: #0876bd; background: #0876bd; color: #fff; }
    .asistencia-row-action--edit { border-color: #ffe0a3; background: #fff7e6; color: #b7791f; }
    .asistencia-row-action--edit:hover { border-color: #d99000; background: #d99000; color: #fff; }
    .asistencia-row-action--history { border-color: #ddccff; background: #f3edff; color: #7541c8; }
    .asistencia-row-action--history:hover { border-color: #7541c8; background: #7541c8; color: #fff; }
    .asistencia-row-action--cancel { border-color: #f5c2c7; background: #fff0f1; color: #b02a37; }
    .asistencia-row-action--cancel:hover { border-color: #b02a37; background: #b02a37; color: #fff; }
    .asistencia-row-action--register {
        width: auto;
        padding: 0 .85rem;
        border-color: #ccebd6;
        background: #eaf8ef;
        color: #278344;
        font-weight: 600;
    }
    .asistencia-row-action--register:hover { border-color: #278344; background: #278344; color: #fff; }
    .asistencia-row-action--register:disabled { border-color: #d8e0e5; background: #f1f5f7; color: #8795a0; cursor: not-allowed; }
    .asistencia-table thead th {
        padding-top: .9rem;
        padding-bottom: .9rem;
        color: #475569;
        font-size: .76rem;
        font-weight: 700;
        letter-spacing: .4px;
        text-transform: uppercase;
    }
    .asistencia-table tbody td { padding-top: .85rem; padding-bottom: .85rem; }
    .asistencia-register-modal { overflow: hidden; border: 0; border-radius: 20px; box-shadow: 0 20px 60px rgba(0, 26, 41, .24); }
    .asistencia-register-modal .modal-header { padding: 1rem 1.35rem; border-bottom: 1px solid #e8eef2; background: #fff; }
    .asistencia-register-modal .modal-title { display: flex; align-items: center; gap: .75rem; color: #00324d; }
    .asistencia-register-modal .register-title-icon { display: inline-flex; width: 40px; height: 40px; flex: 0 0 40px; align-items: center; justify-content: center; border-radius: 13px; background: #eaf8ef; color: #278344; }
    .asistencia-register-modal .register-title-copy { min-width: 0; }
    .asistencia-register-modal .register-title-copy small { display: block; margin-top: .12rem; color: #718096; font-size: .78rem; font-weight: 500; }
    .asistencia-register-modal .modal-body { padding: 1.1rem 1.35rem; background: #f7fafb; }
    .asistencia-register-modal .register-hint { display: flex; align-items: flex-start; gap: .6rem; margin-bottom: 1rem; padding: .75rem .9rem; border: 1px solid #d5e9f4; border-radius: 12px; background: #f0f8fc; color: #36566a; font-size: .85rem; line-height: 1.45; }
    .asistencia-register-modal .register-hint i { margin-top: .1rem; color: #1683b5; }
    .asistencia-register-modal .register-roster-wrap { overflow: auto; max-height: min(52vh, 500px); border: 1px solid #e2eaf0; border-radius: 14px; background: #fff; }
    .asistencia-register-modal .register-roster-table { min-width: 700px; margin: 0; }
    .asistencia-register-modal .register-roster-table thead th { position: sticky; z-index: 1; top: 0; padding: .75rem .85rem; border-bottom: 1px solid #dfe7ed; background: #f3f7f9; color: #36566a; font-size: .75rem; font-weight: 750; letter-spacing: .025em; }
    .asistencia-register-modal .register-roster-table tbody td { padding: .55rem .85rem; border-color: #edf1f4; vertical-align: middle; }
    .asistencia-register-modal .register-roster-table tbody tr:last-child td { border-bottom: 0; }
    .asistencia-register-modal .form-control,.asistencia-register-modal .form-select { border-color: #dce5eb; border-radius: 9px; }
    .asistencia-register-modal .form-control:focus,.asistencia-register-modal .form-select:focus { border-color: #9bd0ff; box-shadow: 0 0 0 .2rem rgba(8, 120, 201, .12); }
    .asistencia-register-modal .register-evidence { height: 100%; padding: .85rem; border: 1px solid #e2eaf0; border-radius: 12px; background: #fff; }
    .asistencia-register-modal .register-evidence .form-label { color: #36566a; font-size: .82rem; font-weight: 700; }
    .asistencia-register-modal .modal-footer { padding: .9rem 1.35rem 1.1rem; border-top: 1px solid #edf1f4; background: #fff; }

    .asistencia-detail-modal .modal-content {
        border: 0;
        border-radius: 22px;
        overflow: hidden;
        box-shadow: 0 24px 60px rgba(0, 26, 41, .20);
    }
    .asistencia-detail-modal .modal-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #edf2f5;
        background: linear-gradient(135deg, #001A29 0%, #00324D 100%);
        color: #fff;
    }
    .asistencia-detail-modal .modal-title {
        margin: 0;
        color: #fff;
        font-size: 1.15rem;
        font-weight: 800;
    }
    .asistencia-detail-modal .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
    .asistencia-detail-modal .modal-dialog {
        max-height: 92vh;
    }
    .asistencia-detail-modal .modal-content {
        max-height: 92vh;
    }
    .asistencia-detail-modal .modal-body {
        padding: 0;
        max-height: calc(92vh - 120px);
        overflow-y: auto;
        background: #f6fbfc;
    }
    .asistencia-detail-modal .asistencia-edit-modal-body {
        padding: 1.25rem 1.5rem;
    }
    .asistencia-detail-modal .modal-footer {
        gap: .75rem;
        margin: 0 .85rem .85rem;
        padding: 1rem 1.25rem;
        border-top: 1px solid #e8eef2;
        border: 1px solid #e8eef2;
        border-radius: 14px;
        background: #fff;
    }
    .asistencia-detail-modal .asistencia-edit-modal-footer {
        box-sizing: border-box;
        flex: 0 0 auto;
        align-items: flex-start;
        justify-content: flex-end;
        gap: 1rem;
        min-height: 76px;
        margin: 0 .85rem .85rem !important;
        padding: .75rem 1.75rem 1.25rem !important;
        border: 0;
        border-radius: 0;
        background: transparent;
    }
    .asistencia-detail-modal .asistencia-edit-modal-content {
        display: flex;
        height: 92vh;
        max-height: 92vh;
    }
    .asistencia-detail-modal .asistencia-edit-modal-content > form {
        display: flex;
        min-height: 0;
        flex: 1 1 auto;
        flex-direction: column;
    }
    .asistencia-detail-modal .asistencia-edit-modal-content .asistencia-edit-modal-body {
        min-height: 0;
        flex: 1 1 auto;
        max-height: none;
        overflow-y: auto;
    }
    .asistencia-correction-reason {
        margin-top: 1.25rem !important;
        padding: 1rem 1.1rem;
        border: 1px solid #e2eaf0;
        border-radius: 14px;
        background: #fff;
    }
    .asistencia-correction-reason .form-label {
        display: block;
        margin-bottom: .55rem;
        color: #23465d;
    }
    .asistencia-correction-reason .form-control {
        min-height: 92px;
        border-color: #d7e2e9;
        border-radius: 10px;
        resize: vertical;
    }
    .asistencia-correction-reason .form-control:focus {
        border-color: #9bd0ff;
        box-shadow: 0 0 0 .2rem rgba(8, 120, 201, .12);
    }
    .asistencia-detail-table {
        margin: 0;
        background: #fff;
    }
    .asistencia-detail-table thead th {
        padding: 1rem 1.1rem;
        border-bottom: 1px solid #dfeaf0;
        background: #eef7fa;
        color: #23465d;
        font-size: .76rem;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
    }
    .asistencia-detail-table tbody td {
        padding: 1rem 1.1rem;
        border-bottom: 1px solid #edf2f5;
        vertical-align: middle;
        color: #1f2d3d;
        font-weight: 500;
    }
    .asistencia-detail-table tbody tr:last-child td { border-bottom: 0; }
    .asistencia-support-link {
        display: inline-flex;
        width: 38px;
        height: 38px;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 12px;
        background: linear-gradient(135deg, #1ec7c4 0%, #3aa9d8 100%);
        box-shadow: 0 8px 18px rgba(34, 190, 186, .28);
        color: #fff;
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .asistencia-support-link:hover,
    .asistencia-support-link:focus-visible {
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 12px 20px rgba(34, 190, 186, .35);
    }
    .asistencia-support-link i { font-size: 1.1rem; }

    .asistencia-excusa-editor {
        display: flex;
        flex-direction: column;
        gap: .7rem;
        min-width: 0;
    }
    .asistencia-excusa-input-row {
        display: flex;
        align-items: center;
        gap: .65rem;
        min-width: 0;
    }
    .asistencia-excusa-input-row .asistencia-observacion-editar {
        flex: 1;
        min-width: 0;
        height: 42px;
        border: 1px solid #dfe7ed;
        border-radius: 10px;
        background: #fff;
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, .03);
    }
    .asistencia-upload-trigger {
        display: inline-flex;
        width: 42px;
        height: 42px;
        align-items: center;
        justify-content: center;
        flex: 0 0 42px;
        border: 0;
        border-radius: 12px;
        background: linear-gradient(135deg, #1ec7c4 0%, #2aa6d9 100%);
        box-shadow: 0 10px 22px rgba(28, 188, 188, .28);
        color: #fff;
        cursor: pointer;
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .asistencia-upload-trigger:hover,
    .asistencia-upload-trigger:focus-within {
        transform: translateY(-1px);
        box-shadow: 0 12px 24px rgba(28, 188, 188, .35);
        color: #fff;
    }
    .asistencia-upload-trigger i {
        font-size: 1.1rem;
    }
    .asistencia-excusa-imagen-editar {
        display: none;
    }
    .asistencia-excusa-actions {
        display: flex;
        align-items: center;
        gap: .5rem;
    }
    .asistencia-excusa-modal-trigger {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        min-width: 150px;
        padding: .52rem .9rem;
        border: 1px solid #dfe7ed;
        border-radius: 10px;
        background: #fff;
        color: #1d2b3a;
        font-size: .84rem;
        font-weight: 600;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    }
    .asistencia-excusa-modal-trigger:hover,
    .asistencia-excusa-modal-trigger:focus-visible {
        background: #f4fbff;
        border-color: #9ad7ff;
        color: #0b4569;
    }
    .asistencia-excusa-modal-trigger i {
        color: #0e9fd8;
    }
    .asistencia-detail-modal .asistencia-modal-button {
        min-width: 150px;
        min-height: 44px;
        padding: .55rem 1rem;
        border: 1px solid #39A900;
        border-radius: 10px;
        background: #39A900;
        color: #fff;
        font-weight: 700;
        transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease;
    }
    .asistencia-detail-modal .asistencia-modal-button:hover {
        border-color: #2e8b00;
        background: #2e8b00;
        color: #fff;
        box-shadow: 0 4px 12px rgba(57, 169, 0, .22);
    }
    .asistencia-detail-modal .asistencia-modal-cancel {
        min-width: 120px;
        min-height: 44px;
        padding: .55rem 1rem;
        border-radius: 10px !important;
    }
    @media (max-width: 575.98px) {
        .asistencia-detail-modal .asistencia-edit-modal-footer {
            padding: .75rem .85rem 1.1rem !important;
        }
        .asistencia-detail-modal .asistencia-edit-modal-footer .btn {
            min-width: 0;
            flex: 1;
        }
    }
    .asistencia-register-modal .register-submit { padding: .6rem 1rem; border: 1px solid #278344; border-radius: 10px; background: #278344; color: #fff; font-weight: 700; }
    .asistencia-register-modal .register-submit:hover { border-color: #1d6e36; background: #1d6e36; }
    .asistencia-register-modal .btn-close { padding: .65rem; border-radius: 50%; background-color: #f1f5f7; opacity: 1; }
    @media (max-width: 575.98px) {
        .asistencia-register-modal .modal-header { padding: .9rem 1rem; }
        .asistencia-register-modal .modal-body { padding: .85rem 1rem; }
        .asistencia-register-modal .modal-footer { padding: .8rem 1rem 1rem; }
        .asistencia-register-modal .register-submit { width: 100%; }
    }
    @media (max-width: 1199.98px) {
        .asistencia-filters { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .asistencia-filter-actions { grid-column: span 3; justify-content: flex-end; }
        .asistencia-filter-actions .asistencia-action-button { flex: 0 0 130px; }
    }
    @media (max-width: 767.98px) {
        .asistencia-filters { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .asistencia-filter-actions { grid-column: span 2; }
        .asistencia-hero { padding: 1.25rem; }
        .asistencia-hero h2 { font-size: 1.6rem; }
    }
    @media (max-width: 480px) {
        .asistencia-filters { grid-template-columns: minmax(0, 1fr); }
        .asistencia-filter-actions { grid-column: auto; }
        .asistencia-filter-actions .asistencia-action-button { flex: 1; }
    }
</style>
@endpush

@section('dashboard-content')
<div class="asistencia-page container-fluid px-3 py-3">
    @php
        $hayFiltros = collect($filtros)->contains(fn ($valor) => filled($valor));
    @endphp
    <div class="asistencia-hero mb-4">
        <div>
            <div class="hero-eyebrow"><i class="fas fa-chart-line me-2"></i>Control ECP · Panel del instructor</div>
            <h2><i class="fas fa-user-check me-2" style="color:#39A900;"></i>Gestión de Asistencias</h2>
            <p class="small mb-0">Consulta, registra y controla la asistencia de tus actividades asignadas.</p>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    @unless($hayFiltros)
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="kpi-asistencia h-100" style="border:2px solid rgba(57,169,0,.35);">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted fw-semibold">Actividades</span>
                    <span class="rounded-circle d-flex align-items-center justify-content-center" style="width:34px;height:34px;background:#e8f8e8;color:#39A900;"><i class="fas fa-calendar-check"></i></span>
                </div>
                <h3 class="fw-bold mb-0" style="color:#001A29;">{{ number_format($stats['total']) }}</h3>
                <small class="text-muted">Programadas para ti</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-asistencia h-100" style="border:2px solid rgba(30,112,0,.35);">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted fw-semibold">Registradas</span>
                    <span class="rounded-circle d-flex align-items-center justify-content-center" style="width:34px;height:34px;background:#e8f8e8;color:#1e7000;"><i class="fas fa-circle-check"></i></span>
                </div>
                <h3 class="fw-bold mb-0 text-success">{{ number_format($stats['registradas']) }}</h3>
                <small class="text-muted">Asistencias guardadas</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-asistencia h-100" style="border:2px solid rgba(192,57,43,.35);">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted fw-semibold">Pendientes</span>
                    <span class="rounded-circle d-flex align-items-center justify-content-center" style="width:34px;height:34px;background:#fdecea;color:#c0392b;"><i class="fas fa-clock"></i></span>
                </div>
                <h3 class="fw-bold mb-0 text-danger">{{ number_format($stats['pendientes']) }}</h3>
                <small class="text-muted">Vencidas sin registro</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-asistencia h-100" style="border:2px solid rgba(106,27,154,.35);">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted fw-semibold">Ausencias</span>
                    <span class="rounded-circle d-flex align-items-center justify-content-center" style="width:34px;height:34px;background:#f3e8f8;color:#6a1b9a;"><i class="fas fa-user-xmark"></i></span>
                </div>
                <h3 class="fw-bold mb-0" style="color:#6a1b9a;">{{ number_format($stats['ausencias']) }}</h3>
                <small class="text-muted">Aprendices ausentes</small>
            </div>
        </div>
    </div>
    @endunless

    <div class="asistencia-filter-card">
        <form method="GET" action="{{ route('controlecp.instructor.asistencia') }}" class="asistencia-filters">
                <div>
                    <label class="form-label" for="filtroFicha">Ficha</label>
                    <select class="form-select" id="filtroFicha" name="ficha_id" onchange="this.form.elements['aprendiz_id'].value = ''; this.form.submit()">
                        <option value="">Todas</option>
                        @foreach($fichas as $ficha)
                            <option value="{{ $ficha->id }}" @selected(($filtros['ficha_id'] ?? '') == $ficha->id)>{{ $ficha->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label" for="filtroActividad">Actividad</label>
                    <select class="form-select" id="filtroActividad" name="actividad_id" onchange="this.form.elements['aprendiz_id'].value = ''; this.form.submit()">
                        <option value="">Todas</option>
                        @foreach($actividades as $actividad)
                            <option value="{{ $actividad->id }}" @selected(($filtros['actividad_id'] ?? '') == $actividad->id)>{{ $actividad->numero }} · {{ $actividad->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label" for="filtroEstado">Estado</label>
                    <select class="form-select" id="filtroEstado" name="estado" onchange="this.form.elements['aprendiz_id'].value = ''; this.form.submit()">
                        <option value="">Todos</option>
                        @foreach(['Registrada', 'Anulada', 'Pendiente'] as $estado)
                            <option value="{{ $estado }}" @selected(($filtros['estado'] ?? '') === $estado)>{{ $estado }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                <label class="form-label" for="fechaDesde">Desde</label>
                <input class="form-control" id="fechaDesde" type="date" name="fecha_desde" value="{{ $filtros['fecha_desde'] ?? '' }}">
                </div>
                <div>
                <label class="form-label" for="fechaHasta">Hasta</label>
                <input class="form-control" id="fechaHasta" type="date" name="fecha_hasta" value="{{ $filtros['fecha_hasta'] ?? '' }}">
                </div>
                <input type="hidden" name="aprendiz_id" value="{{ $filtros['aprendiz_id'] ?? '' }}">
                <div class="asistencia-filter-actions">
                <button class="btn asistencia-action-button" type="submit"><i class="fas fa-magnifying-glass me-1"></i>Buscar</button>
                <a class="btn asistencia-filter-reset" href="{{ route('controlecp.instructor.asistencia') }}" title="Limpiar filtros" aria-label="Limpiar filtros"><i class="fas fa-rotate-left"></i></a>
                </div>
        </form>
    </div>

    @if($sesionAprendiz)
        <section class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 p-4">
                <h2 class="h5 fw-bold mb-1">Historial de {{ $sesionAprendiz->person?->full_name ?? 'Aprendiz' }}</h2>
                <p class="small text-muted mb-0">Ficha {{ $fichaSeleccionada->code }} · {{ $sesionAprendiz->person?->document_number ?? '—' }}</p>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 asistencia-table">
                    <thead class="table-light"><tr><th class="ps-4">Fecha</th><th>Actividad</th><th>Estado del registro</th><th>Asistencia</th></tr></thead>
                    <tbody>
                    @php
                        $hayRegistrosAprendiz = false;
                    @endphp
                    @foreach($sesionesFicha as $sesionFicha)
                        @if($sesionFicha->asistencia?->estado === 'Registrada')
                            @php
                                $hayRegistrosAprendiz = true;
                                $detalleAprendiz = $sesionFicha->asistencia->detalles->firstWhere('aprendiz_id', $sesionAprendiz->id);
                            @endphp
                            <tr>
                                <td class="ps-4">{{ $sesionFicha->fecha?->format('d/m/Y') }}</td>
                                <td>{{ $sesionFicha->actividad?->nombre ?? 'Actividad' }}</td>
                                <td><span class="badge text-bg-success">Registrada</span></td>
                                <td>{{ $detalleAprendiz?->presente ? 'Presente' : 'Ausente' }}</td>
                            </tr>
                        @endif
                    @endforeach
                    @unless($hayRegistrosAprendiz)<tr><td colspan="4" class="text-center text-muted py-4">Este aprendiz aún no tiene asistencias registradas.</td></tr>@endunless
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <section class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 p-4">
            <h2 class="h5 fw-bold mb-0">Actividades y asistencia</h2>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th class="ps-4">Fecha</th><th>Ficha</th><th>Programa</th><th>Actividad</th><th>Estado</th><th class="text-end pe-4">Acciones</th></tr></thead>
                <tbody>
                @forelse($cronogramas as $cronograma)
                    @php
                        $registro = $cronograma->asistencia;
                        $vencida = $cronograma->fecha && $cronograma->fecha->isPast();
                        $sinAprendices = $aprendicesPorFicha->get($cronograma->id_ficha, collect())->isEmpty();
                        $puedeRegistrar = $cronograma->actividad
                            && !in_array($cronograma->estado, ['Finalizada', 'Cancelada'], true)
                            && !$cronograma->fecha?->isFuture();
                    @endphp
                    <tr>
                        <td class="ps-4">{{ $cronograma->fecha?->format('d/m/Y') ?? '—' }}</td>
                        <td>{{ $cronograma->ficha?->code ?? '—' }}</td>
                        <td>{{ $cronograma->ficha?->program?->name ?? 'Sin programa registrado' }}</td>
                        <td>{{ $cronograma->actividad?->nombre ?? $cronograma->titulo_calendario }}</td>
                        <td>
                            @if($registro?->estado === 'Registrada')
                                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="fas fa-check-circle me-1"></i>Asistencia registrada
                                </span>
                                @if($cronograma->estado === 'Finalizada')
                                    <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                        <i class="fas fa-check-circle me-1"></i>Actividad finalizada
                                    </span>
                                @endif
                            @elseif($registro?->estado === 'Anulada')
                                <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">Anulada</span>
                            @elseif($vencida)
                                <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">Sin asistencia registrada</span>
                            @else
                                <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">Pendiente</span>
                            @endif
                        </td>
                        <td class="pe-4">
                            <div class="asistencia-row-actions">
                                @if($registro?->estado === 'Registrada')
                                    <button class="btn asistencia-row-action asistencia-row-action--view" type="button" data-bs-toggle="modal" data-bs-target="#verAsistencia{{ $registro->id }}" title="Ver registro" aria-label="Ver registro"><i class="fas fa-eye"></i></button>
                                    <button class="btn asistencia-row-action asistencia-row-action--edit" type="button" data-bs-toggle="modal" data-bs-target="#editarAsistencia{{ $registro->id }}" title="Editar asistencia" aria-label="Editar asistencia"><i class="fas fa-pen"></i></button>
                                    <button class="btn asistencia-row-action asistencia-row-action--history" type="button" data-bs-toggle="modal" data-bs-target="#historial{{ $registro->id }}" title="Ver historial" aria-label="Ver historial"><i class="fas fa-clock-rotate-left"></i></button>
                                @elseif($cronograma->actividad && !in_array($cronograma->estado, ['Finalizada', 'Cancelada'], true))
                                    <button class="btn asistencia-row-action asistencia-row-action--register" type="button"
                                            @if($puedeRegistrar && $sinAprendices) onclick="window.showSystemAlert('No hay aprendices asociados a esta ficha; no es posible guardar la asistencia.', 'warning')"
                                            @elseif($puedeRegistrar) data-bs-toggle="modal" data-bs-target="#registrarAsistencia{{ $cronograma->id_cronograma }}"
                                            @else disabled title="Podrás registrar la asistencia el día de la actividad." @endif>
                                        <i class="fas fa-clipboard-check me-1"></i>{{ $registro?->estado === 'Anulada' ? 'Registrar de nuevo' : 'Registrar' }}
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5">No hay actividades asignadas que coincidan con los filtros.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0">
            @if ($cronogramas->hasPages())
                <nav aria-label="Paginación de asistencias">
                    <ul class="pagination justify-content-center">
                        @if ($cronogramas->onFirstPage())
                            <li class="page-item disabled"><span class="page-link" aria-hidden="true">&lsaquo;</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $cronogramas->previousPageUrl() }}" aria-label="Página anterior">&lsaquo;</a></li>
                        @endif
                        @php
                                $ultimaPaginaMostrada = 0;
                            @endphp
                        @for ($pagina = 1; $pagina <= $cronogramas->lastPage(); $pagina++)
                            @if ($pagina === 1 || $pagina === $cronogramas->lastPage() || abs($pagina - $cronogramas->currentPage()) <= 1)
                                @if ($ultimaPaginaMostrada > 0 && $pagina > $ultimaPaginaMostrada + 1)
                                    <li class="page-item disabled"><span class="page-link">…</span></li>
                                @endif
                                <li class="page-item {{ $pagina === $cronogramas->currentPage() ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $cronogramas->url($pagina) }}" @if($pagina === $cronogramas->currentPage()) aria-current="page" @endif>{{ $pagina }}</a>
                                </li>
                                @php
                                    $ultimaPaginaMostrada = $pagina;
                                @endphp
                            @endif
                        @endfor
                        @if ($cronogramas->hasMorePages())
                            <li class="page-item"><a class="page-link" href="{{ $cronogramas->nextPageUrl() }}" aria-label="Página siguiente">&rsaquo;</a></li>
                        @else
                            <li class="page-item disabled"><span class="page-link" aria-hidden="true">&rsaquo;</span></li>
                        @endif
                    </ul>
                </nav>
            @endif
        </div>
        @foreach($cronogramas as $cronograma)
            @if($cronograma->asistencia?->estado === 'Registrada')
                @php
                    $registro = $cronograma->asistencia;
                @endphp
                <div class="modal fade asistencia-detail-modal" id="verAsistencia{{ $registro->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title">Detalle de asistencia</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table align-middle asistencia-detail-table">
                                    <thead><tr><th>Documento</th><th>Aprendiz</th><th>Estado</th><th>Observación</th><th>Soporte</th></tr></thead>
                                    <tbody>
                                    @foreach($registro->detalles as $detalle)
                                        @php
                                            $aprendiz = $detalle->aprendiz?->person;
                                        @endphp
                                        <tr>
                                            <td class="font-monospace">{{ $aprendiz?->document_number ?? $detalle->aprendiz_id }}</td>
                                            <td>{{ $aprendiz?->full_name ?? 'Aprendiz' }}</td>
                                            <td>
                                                <span class="badge rounded-pill {{ $detalle->presente ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                                    {{ $detalle->presente ? 'Presente' : 'Ausente' }}
                                                </span>
                                            </td>
                                            <td>{{ $detalle->observacion ?: '—' }}</td>
                                            <td>
                                                @if($detalle->excusa_imagen)
                                                    @php
                                                        $soporteBuscador = trim((string) ($aprendiz?->full_name ?? ''));
                                                    @endphp
                                                    <a class="asistencia-support-link" href="{{ route('controlecp.instructor.repositorio.evidencias', ['categoria' => 'Excusas', 'buscar' => $soporteBuscador]) }}" rel="noopener" title="Ver excusa en repositorio"><i class="fas fa-folder-open"></i></a>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div></div>
                </div>
                <div class="modal fade asistencia-detail-modal" id="editarAsistencia{{ $registro->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content asistencia-edit-modal-content">
                        <form id="formEditarAsistencia{{ $registro->id }}" method="POST" action="{{ route('controlecp.instructor.asistencia.checklist.store', $cronograma->id_cronograma) }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="modo" value="editar">
                            <div class="modal-header"><h5 class="modal-title">Editar asistencia</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
                            <div class="modal-body asistencia-edit-modal-body">
                                <div class="table-responsive">
                                    <table class="table align-middle asistencia-detail-table">
                                        <thead><tr><th>Documento</th><th>Aprendiz</th><th class="text-center">Presente</th><th class="text-center">Excusa</th><th>Motivo y soporte</th></tr></thead>
                                        <tbody>
                                        @foreach($aprendicesPorFicha->get($cronograma->id_ficha, collect()) as $aprendiz)
                                            @php
                                                $detalle = $registro->detalles->firstWhere('aprendiz_id', $aprendiz->id);
                                                $presente = old('asistencia.' . $aprendiz->id . '.presente', $detalle?->presente ? '1' : '0');
                                                $justificada = old('asistencia.' . $aprendiz->id . '.justificada', $detalle?->justificada ? '1' : '0');
                                            @endphp
                                            <tr data-aprendiz-editar="{{ $aprendiz->id }}">
                                                <td class="font-monospace">{{ $aprendiz->person?->document_number ?? $aprendiz->person_id }}</td>
                                                <td>{{ $aprendiz->person?->full_name ?? 'Aprendiz' }}</td>
                                                <td class="text-center">
                                                    <input type="hidden" name="asistencia[{{ $aprendiz->id }}][presente]" value="0">
                                                    <input type="checkbox" class="form-check-input asistencia-presente-editar" name="asistencia[{{ $aprendiz->id }}][presente]" value="1" @checked((string)$presente === '1')>
                                                </td>
                                                <td class="text-center">
                                                    <input type="hidden" name="asistencia[{{ $aprendiz->id }}][justificada]" value="0">
                                                    <input type="checkbox" class="form-check-input asistencia-justificada-editar" name="asistencia[{{ $aprendiz->id }}][justificada]" value="1" @checked((string)$justificada === '1')>
                                                </td>
                                                <td>
                                                    <div class="asistencia-excusa-detalles-editar {{ (string)$justificada === '1' ? '' : 'd-none' }}">
                                                        <button type="button" class="btn asistencia-excusa-modal-trigger" data-excusa-modal="excusaModalEditar{{ $registro->id }}_{{ $aprendiz->id }}" data-parent-modal="editarAsistencia{{ $registro->id }}">
                                                            <i class="fas fa-folder-open"></i>
                                                            Motivo y soporte
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="asistencia-correction-reason">
                                    <label class="form-label fw-semibold" for="motivoEdicion{{ $registro->id }}">Motivo de la corrección *</label>
                                    <textarea class="form-control" id="motivoEdicion{{ $registro->id }}" name="motivo" maxlength="1000" required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer asistencia-edit-modal-footer">
                                <button type="button" class="btn btn-outline-secondary asistencia-modal-cancel" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn asistencia-modal-button">Guardar cambios</button>
                            </div>
                        </form>
                    </div></div>
                </div>
                @foreach($aprendicesPorFicha->get($cronograma->id_ficha, collect()) as $aprendiz)
                    @php
                        $detalle = $registro->detalles->firstWhere('aprendiz_id', $aprendiz->id);
                    @endphp
                    <div class="modal fade asistencia-detail-modal asistencia-excusa-modal" id="excusaModalEditar{{ $registro->id }}_{{ $aprendiz->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Motivo y soporte · {{ $aprendiz->person?->full_name ?? 'Aprendiz' }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="asistencia-excusa-editor">
                                        <label class="form-label fw-semibold" for="motivoExcusa{{ $registro->id }}_{{ $aprendiz->id }}">Motivo de la excusa</label>
                                        <div class="asistencia-excusa-input-row">
                                            <input id="motivoExcusa{{ $registro->id }}_{{ $aprendiz->id }}" type="text" class="form-control asistencia-observacion-editar" form="formEditarAsistencia{{ $registro->id }}" name="asistencia[{{ $aprendiz->id }}][observacion]" maxlength="1000" value="{{ old('asistencia.' . $aprendiz->id . '.observacion', $detalle?->observacion ?? '') }}" placeholder="Escribe el motivo de la excusa">
                                            <div class="asistencia-excusa-actions">
                                                <label class="asistencia-upload-trigger" for="excusaFile{{ $registro->id }}_{{ $aprendiz->id }}" title="Subir evidencia" aria-label="Subir evidencia">
                                                    <i class="fas fa-folder-open"></i>
                                                </label>
                                                <input id="excusaFile{{ $registro->id }}_{{ $aprendiz->id }}" type="file" class="asistencia-excusa-imagen-editar" form="formEditarAsistencia{{ $registro->id }}" name="asistencia[{{ $aprendiz->id }}][excusa_imagen]" accept="image/jpeg,image/png">
                                            </div>
                                        </div>
                                        @if($detalle?->excusa_imagen)
                                            <small class="text-primary"><i class="fas fa-circle-check me-1"></i>Ya hay una evidencia guardada; se conservará si vuelves a marcar la excusa.</small>
                                        @endif
                                        <small class="text-muted asistencia-excusa-archivo-estado" data-default-text="Adjunta una imagen JPG o PNG como evidencia de la excusa.">Adjunta una imagen JPG o PNG como evidencia de la excusa.</small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn asistencia-modal-button px-4" data-bs-dismiss="modal">Listo</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="modal fade asistencia-detail-modal" id="historial{{ $registro->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title">Historial de asistencia</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
                        <div class="modal-body">
                            <div class="table-responsive"><table class="table align-middle asistencia-detail-table">
                                <thead><tr><th>Fecha</th><th>Acción</th><th>Usuario</th><th>Motivo</th></tr></thead>
                                <tbody>
                                @forelse($historialPorAsistencia->get($registro->id, collect()) as $item)
                                    <tr><td>{{ $item->created_at ? \Carbon\Carbon::parse($item->created_at, 'UTC')->timezone('America/Bogota')->format('d/m/Y g:i A') : '—' }}</td><td>{{ $item->accion }}</td><td>{{ $item->nickname ?? 'Instructor' }}</td><td>{{ $item->motivo ?: '—' }}</td></tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">Sin historial.</td></tr>
                                @endforelse
                                </tbody>
                            </table></div>
                        </div>
                    </div></div>
                </div>
                <div class="modal fade" id="anular{{ $registro->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog"><div class="modal-content">
                        <form method="POST" action="{{ route('controlecp.instructor.asistencia.anular', $registro->id) }}">
                            @csrf
                            <div class="modal-header"><h5 class="modal-title">Anular asistencia</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
                            <div class="modal-body">
                                <p>Confirma la anulación de la asistencia de <strong>{{ $cronograma->actividad?->nombre }}</strong>.</p>
                                <label class="form-label" for="motivo{{ $registro->id }}">Motivo *</label>
                                <textarea class="form-control" id="motivo{{ $registro->id }}" name="motivo" maxlength="1000" required></textarea>
                            </div>
                            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-danger rounded-pill">Confirmar anulación</button></div>
                        </form>
                    </div></div>
                </div>
            @endif
            @if($cronograma->actividad && !in_array($cronograma->estado, ['Finalizada', 'Cancelada'], true) && !$cronograma->fecha?->isFuture() && $cronograma->asistencia?->estado !== 'Registrada' && $aprendicesPorFicha->get($cronograma->id_ficha, collect())->isNotEmpty())
                @php
                    $aprendicesModal = $aprendicesPorFicha->get($cronograma->id_ficha, collect());
                @endphp
                <div class="modal fade" id="registrarAsistencia{{ $cronograma->id_cronograma }}" tabindex="-1" aria-labelledby="registrarAsistenciaTitulo{{ $cronograma->id_cronograma }}" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"><div class="modal-content asistencia-register-modal">
                        <form method="POST" action="{{ route('controlecp.instructor.asistencia.checklist.store', $cronograma->id_cronograma) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-header">
                                <h2 class="modal-title h5 fw-bold" id="registrarAsistenciaTitulo{{ $cronograma->id_cronograma }}">
                                    <span class="register-title-icon"><i class="fas fa-clipboard-check" aria-hidden="true"></i></span>
                                    <span class="register-title-copy">
                                        Registrar asistencia · {{ $cronograma->actividad?->nombre ?? 'Actividad' }}
                                        <small>Ficha {{ $cronograma->ficha?->code ?? '—' }} · {{ $cronograma->fecha?->format('d/m/Y') ?? '—' }}</small>
                                    </span>
                                </h2>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                @if($aprendicesModal->isNotEmpty())
                                    <div class="register-roster-wrap">
                                        <table class="table table-hover align-middle register-roster-table">
                                            <thead>
                                                <tr>
                                                    <th>Documento</th>
                                                    <th>Aprendiz</th>
                                                    <th style="min-width:110px">Presente</th>
                                                    <th style="min-width:120px">Excusa</th>
                                                    <th style="min-width:220px">Motivo y soporte</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($aprendicesModal as $aprendiz)
                                                    <tr data-aprendiz="{{ $aprendiz->id }}">
                                                        <td class="text-nowrap">{{ $aprendiz->person?->document_number ?? $aprendiz->person_id }}</td>
                                                        <td class="fw-semibold">{{ $aprendiz->person?->full_name ?? 'Aprendiz' }}</td>
                                                        <td class="text-center">
                                                            <input type="hidden" name="asistencia[{{ $aprendiz->id }}][presente]" value="0">
                                                            <input type="checkbox" class="form-check-input asistencia-presente" name="asistencia[{{ $aprendiz->id }}][presente]" value="1" checked>
                                                        </td>
                                                        <td class="text-center">
                                                            <input type="hidden" name="asistencia[{{ $aprendiz->id }}][justificada]" value="0">
                                                            <input type="checkbox" class="form-check-input asistencia-justificada" name="asistencia[{{ $aprendiz->id }}][justificada]" value="1">
                                                        </td>
                                                        <td>
                                                            <div class="asistencia-excusa-detalles d-none">
                                                                <input type="text" class="form-control form-control-sm asistencia-observacion mb-2" name="asistencia[{{ $aprendiz->id }}][observacion]" maxlength="1000" placeholder="Escribe el motivo de la excusa">
                                                                <input type="file" class="form-control form-control-sm asistencia-excusa-imagen" name="asistencia[{{ $aprendiz->id }}][excusa_imagen]" accept="image/jpeg,image/png">
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="row g-3 mt-1">
                                        <div class="col-md-6">
                                            <div class="register-evidence">
                                                <label class="form-label" for="evidenciaFoto{{ $cronograma->id_cronograma }}">Foto opcional (JPG o PNG)</label>
                                                <input class="form-control form-control-sm" id="evidenciaFoto{{ $cronograma->id_cronograma }}" type="file" name="evidencia_foto" accept="image/jpeg,image/png">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="register-evidence">
                                                <label class="form-label" for="evidenciaEnlace{{ $cronograma->id_cronograma }}">Enlace de video opcional</label>
                                                <input class="form-control form-control-sm" id="evidenciaEnlace{{ $cronograma->id_cronograma }}" type="url" name="evidencia_enlace" maxlength="500" placeholder="https://...">
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                                <button class="btn register-submit" type="submit" @disabled($aprendicesModal->isEmpty())><i class="fas fa-floppy-disk me-2" aria-hidden="true"></i>Guardar asistencia</button>
                            </div>
                        </form>
                    </div></div>
                </div>
            @endif
        @endforeach
    </section>
</div>
<script>
    function actualizarFilaAsistencia(fila) {
        if (!fila) return;

        const presente = fila.querySelector('.asistencia-presente') || fila.querySelector('.asistencia-presente-editar');
        const excusa = fila.querySelector('.asistencia-justificada') || fila.querySelector('.asistencia-justificada-editar');
        const detallesExcusa = fila.querySelector('.asistencia-excusa-detalles') || fila.querySelector('.asistencia-excusa-detalles-editar');
        const observacion = fila.querySelector('.asistencia-observacion') || fila.querySelector('.asistencia-observacion-editar');
        const imagen = fila.querySelector('.asistencia-excusa-imagen') || fila.querySelector('.asistencia-excusa-imagen-editar');
        const trigger = fila.querySelector('.asistencia-excusa-modal-trigger');
        if (!presente || !excusa || !detallesExcusa) return;

        detallesExcusa.classList.toggle('d-none', !excusa.checked);
        if (trigger) {
            trigger.style.display = excusa.checked ? 'inline-flex' : 'none';
        }
        if (observacion && !observacion.classList.contains('asistencia-observacion-editar')) {
            observacion.required = excusa.checked;
        }
        if (imagen && !imagen.classList.contains('asistencia-excusa-imagen-editar')) {
            imagen.required = excusa.checked;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('tr[data-aprendiz], tr[data-aprendiz-editar]').forEach((fila) => actualizarFilaAsistencia(fila));
    });

    document.addEventListener('change', (event) => {
        if (event.target.matches('.asistencia-excusa-imagen-editar')) {
            const modal = event.target.closest('.asistencia-excusa-modal');
            const status = modal?.querySelector('.asistencia-excusa-archivo-estado');
            if (status) {
                status.textContent = event.target.files?.[0]
                    ? 'Evidencia de excusa ya seleccionada: ' + event.target.files[0].name
                    : status.dataset.defaultText;
                status.classList.toggle('text-primary', Boolean(event.target.files?.[0]));
                status.classList.toggle('text-muted', !event.target.files?.[0]);
            }
        }

        if (event.target.matches('.asistencia-presente, .asistencia-presente-editar')) {
            const fila = event.target.closest('tr[data-aprendiz], tr[data-aprendiz-editar]');
            if (event.target.checked) {
                const excusa = fila?.querySelector('.asistencia-justificada, .asistencia-justificada-editar');
                if (excusa) excusa.checked = false;
            }
            actualizarFilaAsistencia(fila);
        }
        if (event.target.matches('.asistencia-justificada, .asistencia-justificada-editar')) {
            const fila = event.target.closest('tr[data-aprendiz], tr[data-aprendiz-editar]');
            if (event.target.checked) {
                const presente = fila?.querySelector('.asistencia-presente, .asistencia-presente-editar');
                if (presente) presente.checked = false;
            }
            actualizarFilaAsistencia(fila);
        }
    });

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('.asistencia-excusa-modal-trigger');
        if (!trigger) return;

        const parent = document.getElementById(trigger.dataset.parentModal);
        const excuseModal = document.getElementById(trigger.dataset.excusaModal);
        if (!parent || !excuseModal || !window.bootstrap || !window.bootstrap.Modal) return;

        excuseModal.dataset.parentModal = parent.id;
        parent.addEventListener('hidden.bs.modal', () => {
            window.bootstrap.Modal.getOrCreateInstance(excuseModal).show();
        }, { once: true });
        window.bootstrap.Modal.getOrCreateInstance(parent).hide();
    });

    document.addEventListener('hidden.bs.modal', (event) => {
        const excuseModal = event.target;
        if (!excuseModal.classList.contains('asistencia-excusa-modal') || !excuseModal.dataset.parentModal) return;

        const parent = document.getElementById(excuseModal.dataset.parentModal);
        if (parent) {
            window.bootstrap.Modal.getOrCreateInstance(parent).show();
        }
    });
</script>
@endsection
