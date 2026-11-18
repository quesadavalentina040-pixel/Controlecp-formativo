@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
<style>
    .cronograma-header-card {
        background-color: #001A29;
        border-radius: 24px;
        padding: 1.25rem 1.5rem;
    }
    .cronograma-header-card h2 {
        color: #fff !important;
    }
    .cronograma-header-card .text-muted {
        color: #c7d4da !important;
    }
    .cronograma-today-button {
        border-color: #39A900;
        background: #39A900;
        color: #fff;
    }
    .cronograma-today-button:hover,
    .cronograma-today-button:focus-visible {
        border-color: #2e8b00;
        background: #2e8b00;
        color: #fff;
    }

    #pills-tab .nav-link {
        color: #001A29;
    }
    #pills-tab .nav-link.active,
    #pills-tab .show > .nav-link {
        background-color: #39A900;
        color: #fff;
    }
    #pills-tab .nav-link:not(.active):hover {
        color: #2e8800;
    }

    .cronograma-filter-card {
        border-radius: 18px;
        background: #001A29;
    }
    .cronograma-filter-grid {
        display: grid;
        grid-template-columns:
            minmax(150px, 1.8fr)
            minmax(120px, 1.35fr)
            minmax(100px, .9fr)
            minmax(130px, 1.3fr)
            minmax(120px, 1fr)
            minmax(120px, 1fr)
            auto;
        gap: .6rem;
        align-items: end;
    }
    .cronograma-filter-grid .form-label {
        display: block;
        margin-bottom: .35rem;
        color: #fff;
    }
    .cronograma-filter-grid .form-control,
    .cronograma-filter-grid .form-select,
    .cronograma-filter-grid .input-group-text {
        min-width: 0;
        min-height: 43px;
        border-color: #dce5eb;
        background-color: #fff;
        color: #001A29;
        box-shadow: none;
    }
    .cronograma-filter-grid .form-control:focus,
    .cronograma-filter-grid .form-select:focus {
        border-color: #39A900;
        box-shadow: 0 0 0 .2rem rgba(57, 169, 0, .25);
    }
    .cronograma-filter-grid .input-group-text {
        border-radius: 9px 0 0 9px;
    }
    .cronograma-filter-grid .search-field {
        border-radius: 0 9px 9px 0 !important;
    }
    .cronograma-filter-grid .ficha-search-results {
        position: absolute;
        top: calc(100% + .25rem);
        left: 0;
        right: 0;
        z-index: 20;
        display: none;
        max-height: 180px;
        overflow-y: auto;
        padding: .25rem;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: .65rem;
        box-shadow: 0 .35rem 1rem rgba(0, 26, 41, .12);
    }
    .cronograma-filter-grid .ficha-search-results.is-visible {
        display: block;
    }
    .cronograma-filter-grid .ficha-search-option {
        display: block;
        width: 100%;
        padding: .4rem .55rem;
        border: 0;
        border-radius: .4rem;
        background: transparent;
        color: #001A29;
        text-align: left;
        font-size: .9rem;
    }
    .cronograma-filter-grid .ficha-search-option:hover,
    .cronograma-filter-grid .ficha-search-option:focus {
        background: #f0fbf4;
        outline: none;
    }
    .cronograma-filter-grid .ficha-search-empty {
        padding: .4rem .55rem;
        color: #6c757d;
        font-size: .8rem;
    }
    .cronograma-filter-grid .form-select,
    .cronograma-filter-grid input[type="date"] {
        border-radius: 9px;
    }
    .cronograma-filter-grid .filter-actions {
        display: flex;
        gap: .5rem;
    }
    .cronograma-filter-grid .filter-submit,
    .cronograma-filter-grid .filter-reset {
        min-height: 43px;
        height: 43px;
        white-space: nowrap;
    }
    .cronograma-filter-grid .filter-submit {
        flex: 1;
        min-width: 112px;
        padding: 0 .85rem;
        background: #39A900;
        border-color: #39A900;
        color: #fff;
        font-weight: 700;
    }
    .cronograma-filter-grid .filter-submit:hover,
    .cronograma-filter-grid .filter-submit:focus {
        background: #2e8800;
        border-color: #2e8800;
        color: #fff;
    }
    .cronograma-filter-grid .filter-reset {
        flex: 0 0 48px;
        display: grid;
        place-items: center;
        padding: 0;
        border-color: #dce5eb;
        background: #fff;
        color: #001A29;
    }
    .cronograma-filter-grid .filter-reset:hover {
        background: #f1f5f9;
        color: #001A29;
    }
    @media (max-width: 1199.98px) {
        .cronograma-filter-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
        .cronograma-filter-grid .search-filter {
            grid-column: span 2;
        }
        .cronograma-filter-grid .filter-actions {
            grid-column: span 2;
            justify-content: flex-end;
        }
        .cronograma-filter-grid .filter-submit {
            flex: 0 0 112px;
        }
    }
    @media (max-width: 767.98px) {
        .cronograma-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .cronograma-filter-grid .search-filter,
        .cronograma-filter-grid .filter-actions {
            grid-column: 1 / -1;
        }
    }

    .semaforo-indicator {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 8px rgba(0,0,0,0.15);
        flex-shrink: 0;
    }
    .semaforo-indicator-large {
        width: 20px;
        height: 20px;
    }
    .semaforo-verde {
        background-color: #39A900;
        box-shadow: 0 0 10px rgba(57, 169, 0, 0.6);
    }
    .semaforo-amarillo {
        background-color: #f57f17;
        box-shadow: 0 0 10px rgba(245, 127, 23, 0.6);
    }
    .semaforo-rojo {
        background-color: #d32f2f;
        box-shadow: 0 0 10px rgba(211, 47, 47, 0.6);
    }
    .semaforo-neutro {
        background-color: #94a3b8;
        box-shadow: none;
    }

    .card-semaforo-box {
        height: 100%;
        border: 2px solid transparent;
        border-radius: 14px;
        background: #fff;
        cursor: pointer;
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .card-semaforo-box .card-body {
        padding: 1rem !important;
    }
    .card-semaforo-rojo {
        border-color: rgba(220, 38, 38, .35);
    }
    .card-semaforo-amarillo {
        border-color: rgba(245, 127, 23, .4);
    }
    .card-semaforo-verde {
        border-color: rgba(22, 163, 74, .35);
    }
    .card-semaforo-azul {
        border-color: rgba(0, 112, 184, .35);
    }
    .card-semaforo-neutro {
        border-color: rgba(100, 116, 139, .35);
    }
    .card-semaforo-icon {
        display: inline-flex;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: .95rem;
    }
    .card-semaforo-rojo .card-semaforo-icon {
        background: #fee2e2;
        color: #d32f2f;
    }
    .card-semaforo-amarillo .card-semaforo-icon {
        background: #ffedd5;
        color: #ea580c;
    }
    .card-semaforo-verde .card-semaforo-icon {
        background: #dcfce7;
        color: #16a34a;
    }
    .card-semaforo-azul .card-semaforo-icon {
        background: #eaf5ff;
        color: #0070b8;
    }
    .card-semaforo-neutro .card-semaforo-icon {
        background: #e2e8f0;
        color: #64748b;
    }
    .card-semaforo-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 26, 41, .08);
    }
    .card-semaforo-label {
        color: #475569;
        font-size: .95rem;
        font-weight: 500;
    }
    .card-semaforo-value {
        margin-top: .15rem;
        font-size: 1.7rem;
        font-weight: 800;
        line-height: 1;
    }
    .card-semaforo-subtitle {
        color: #64748b;
        font-size: .9rem;
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 2px;
        background-color: #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
    }
    .calendar-day-header {
        background-color: #001A29;
        color: #ffffff;
        font-weight: 700;
        text-align: center;
        padding: 12px 6px;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .calendar-day-cell {
        background-color: #ffffff;
        min-height: 120px;
        padding: 8px;
        position: relative;
        transition: background-color 0.15s ease;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .calendar-day-cell.has-events {
        cursor: pointer;
    }
    .calendar-day-cell.has-events:hover {
        background-color: #f0fbf4;
    }
    .calendar-day-header-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 4px;
    }
    .calendar-day-number {
        font-weight: 700;
        font-size: 0.88rem;
        color: #001A29;
        display: inline-block;
        width: 26px;
        height: 26px;
        line-height: 26px;
        text-align: center;
        border-radius: 50%;
    }
    .calendar-day-cell.today .calendar-day-number {
        background-color: #39A900;
        color: #ffffff;
        box-shadow: 0 0 10px rgba(57, 169, 0, 0.4);
    }
    .calendar-day-activity-indicator {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        margin-top: .35rem;
        padding: .3rem .5rem;
        border-radius: 999px;
        background: #e8f8e8;
        color: #267800;
        font-size: .72rem;
        font-weight: 700;
    }
    .calendar-day-activity-indicator .activity-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #39A900;
    }
    .calendar-day-activity-indicator.semaforo-rojo {
        background: #fee2e2;
        color: #b91c1c;
        box-shadow: none;
    }
    .calendar-day-activity-indicator.semaforo-rojo .activity-dot {
        background: #dc2626;
    }
    .calendar-day-activity-indicator.semaforo-amarillo {
        background: #ffedd5;
        color: #c2410c;
    }
    .calendar-day-activity-indicator.semaforo-amarillo .activity-dot {
        background: #f57f17;
    }
    .calendar-day-activity-indicator.semaforo-verde {
        background: #e8f8e8;
        color: #267800;
    }
    .calendar-day-activity-indicator.semaforo-neutro {
        background: #e2e8f0;
        color: #475569;
    }
    .calendar-day-activity-indicator.semaforo-neutro .activity-dot {
        background: #94a3b8;
    }
    .calendar-day-activity {
        padding: .75rem;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #39A900;
        border-radius: 12px;
        background: #fff;
    }
    .calendar-day-activity + .calendar-day-activity {
        margin-top: .6rem;
    }
    .calendar-day-activity .activity-meta {
        color: #64748b;
        font-size: .85rem;
    }
    .calendar-day-cell.other-month {
        background-color: #eaf1f5;
        opacity: 1;
    }
    .calendar-day-cell.other-month .calendar-day-number {
        color: #82909b;
    }
    .cronograma-instructor-page .calendar-day-cell {
        background-color: #fff !important;
    }
    .cronograma-instructor-page .calendar-day-cell.other-month {
        background-color: #eaf1f5 !important;
        opacity: 1;
    }
    .calendar-day-events {
        display: flex;
        flex-direction: column;
        gap: 4px;
        overflow-y: auto;
        max-height: 110px;
    }
    .calendar-event-pill {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 4px 6px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        gap: 2px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        text-decoration: none;
        border-left: 3px solid #cbd5e1;
        background-color: #f8fafc;
        color: #0f172a;
    }
    .calendar-event-pill:hover {
        transform: scale(1.02);
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }
    .event-pill-verde {
        background-color: #f0fdf4;
        border-left-color: #39A900;
        color: #14532d;
    }
    .event-pill-amarillo {
        background-color: #fffbeb;
        border-left-color: #f59e0b;
        color: #78350f;
    }
    .event-pill-rojo {
        background-color: #fef2f2;
        border-left-color: #ef4444;
        color: #7f1d1d;
    }
    .event-pill-neutro {
        background-color: #f1f5f9;
        border-left-color: #94a3b8;
        color: #334155;
    }
    .cronograma-card-activity {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 10px 24px rgba(15,23,42,.05);
    }
    .cronograma-card-activity .card-body {
        padding: 1rem 1.1rem;
    }
    .cronograma-estado-badge {
        font-size: .8rem;
    }
    .cronograma-row-actions {
        display: flex;
        justify-content: flex-end;
        gap: .4rem;
        white-space: nowrap;
    }
    .cronograma-row-action {
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
    .cronograma-row-action:hover,
    .cronograma-row-action:focus-visible {
        transform: translateY(-2px);
        box-shadow: 0 3px 8px rgba(0, 26, 41, .14);
    }
    .cronograma-row-action--postpone {
        background: #fff7e6;
        border-color: #f8e3b2;
        color: #c47a00;
    }
    .cronograma-row-action--postpone:hover {
        background: #c47a00;
        border-color: #c47a00;
        color: #fff;
    }
    .cronograma-postpone-modal .modal-content {
        overflow: hidden;
        border: 0;
        border-radius: 20px;
        background: #fff;
        box-shadow: 0 24px 60px rgba(0, 26, 41, .24);
    }
    .cronograma-postpone-modal .modal-header {
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid #e8eef2;
        background: #fff;
    }
    .cronograma-postpone-modal .postpone-title {
        display: flex;
        align-items: center;
        gap: .75rem;
        margin: 0;
        color: #00324d;
        font-weight: 800;
    }
    .cronograma-postpone-modal .postpone-title-icon {
        display: inline-flex;
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
        background: #e8f7df;
        color: #278344;
    }
    .cronograma-postpone-modal .modal-body { padding: 1.15rem 1.25rem; background: #f5f9fb; }
    .cronograma-postpone-modal .postpone-activity-summary {
        display: flex;
        align-items: flex-start;
        gap: .7rem;
        margin-bottom: 1rem;
        padding: .8rem .9rem;
        border: 1px solid #dce8ee;
        border-radius: 12px;
        background: #fff;
    }
    .cronograma-postpone-modal .postpone-activity-summary i { margin-top: .15rem; color: #39a900; }
    .cronograma-postpone-modal .postpone-activity-summary span { color: #36566a; font-size: .88rem; font-weight: 650; }
    .cronograma-postpone-modal .form-label { margin-bottom: .4rem; color: #23465d; font-weight: 700; }
    .cronograma-postpone-modal .form-control {
        min-height: 44px;
        border-color: #d7e2e9;
        border-radius: 10px;
        background: #fff;
    }
    .cronograma-postpone-modal textarea.form-control { min-height: 104px; resize: vertical; }
    .cronograma-postpone-modal .form-control:focus {
        border-color: #39a900;
        box-shadow: 0 0 0 .2rem rgba(57, 169, 0, .14);
    }
    .cronograma-postpone-modal .modal-footer {
        justify-content: flex-end;
        gap: .65rem;
        padding: .9rem 1.25rem 1.1rem;
        border-top: 1px solid #e8eef2;
        background: #fff;
    }
    .cronograma-postpone-modal .modal-footer .btn { min-height: 42px; border-radius: 9px; font-weight: 700; }
    .cronograma-postpone-modal .postpone-submit {
        border: 1px solid #39a900;
        background: #39a900;
        color: #fff;
    }
    .cronograma-postpone-modal .postpone-submit:hover,
    .cronograma-postpone-modal .postpone-submit:focus-visible {
        border-color: #2e8b00;
        background: #2e8b00;
        color: #fff;
    }
    @media (max-width: 575.98px) {
        .cronograma-postpone-modal .modal-header,
        .cronograma-postpone-modal .modal-body { padding: 1rem; }
        .cronograma-postpone-modal .modal-footer { padding: .8rem 1rem 1rem; }
        .cronograma-postpone-modal .modal-footer .btn { flex: 1; padding-inline: .5rem; }
    }
</style>

@php
    $fichas = $fichas ?? collect();
    $momentos = $momentos ?? collect();
    $calendarEvents = $calendarEvents ?? collect();
    $currentMes = request('mes', now()->format('Y-m'));
    $mesActual = \Carbon\Carbon::createFromFormat('Y-m', $currentMes);
    $inicioCalendario = $mesActual->copy()->startOfMonth()->startOfWeek(\Carbon\Carbon::MONDAY);
    $finCalendario = $mesActual->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SUNDAY);
    $diasDelMes = [];
    for ($dia = $inicioCalendario->copy(); $dia->lte($finCalendario); $dia->addDay()) {
        $diasDelMes[] = $dia->copy();
    }
    $eventosInstructor = $calendarEvents->flatten(1)->map(function ($cronograma) {
        $instructor = $cronograma->instructor;
        $persona = $instructor?->person;

        return [
            'id' => $cronograma->id_cronograma,
            'fecha' => $cronograma->fecha?->toDateString(),
            'titulo' => $cronograma->actividad
                ? 'Actividad ' . ($cronograma->actividad->numero ?: $cronograma->actividad->id) . ' – ' . ($cronograma->actividad->momento?->nombre ?? 'Sin momento')
                : 'Actividad programada',
            'nombre' => $cronograma->actividad?->nombre ?? 'Actividad sin nombre',
            'numero' => $cronograma->actividad?->numero,
            'momento' => $cronograma->actividad?->momento?->nombre ?? 'Sin momento',
            'ficha' => $cronograma->ficha?->code ?? '—',
            'programa' => $cronograma->ficha?->program?->name ?? '—',
            'horario' => $cronograma->horario_formateado,
            'hora_inicio' => $cronograma->hora_inicio,
            'instructor' => $persona
                ? trim($persona->first_name . ' ' . $persona->first_last_name)
                : ($instructor?->nickname ?? 'Sin asignar'),
            'lugar' => $cronograma->lugar ?: 'Sin definir',
            'estado' => $cronograma->estado,
            'motivo_posposicion' => $cronograma->motivo_posposicion,
            'recursos' => $cronograma->recursos->map(fn ($recurso) => [
                'nombre' => $recurso->inventario?->nombre ?? 'Recurso',
                'cantidad' => $recurso->cantidad,
            ])->values(),
        ];
    })->values();
@endphp

<div class="container-fluid px-3 py-3 cronograma-instructor-page">
    <div class="cronograma-header-card d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small fw-semibold mb-1" style="color: #39A900;">
                <i class="fas fa-calendar-days me-2"></i>Control ECP · Gestión de Cronograma
            </div>
            <h2 class="h3 fw-bold mb-0" style="font-family: 'Outfit', sans-serif;">
                <i class="fas fa-calendar-days me-2" style="color: #39A900;"></i>Cronograma de Actividades
            </h2>
            <p class="text-muted small mb-0">Tus actividades asignadas, fechas, semaforización y recursos.</p>
        </div>
    </div>

    <!-- Panel de semáforos con la misma organización del administrador -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl">
            <div class="card card-semaforo-box card-semaforo-rojo shadow-sm" onclick="filtrarPorSemaforoInstructor('rojo')">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="card-semaforo-label">Crítico / Muy próximo</span>
                        <span class="card-semaforo-icon"><i class="fas fa-triangle-exclamation"></i></span>
                    </div>
                    <div class="card-semaforo-value" style="color:#dc2626;">{{ $stats['rojo'] ?? 0 }}</div>
                    <div class="card-semaforo-subtitle">Hoy o en ≤ 2 días</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl">
            <div class="card card-semaforo-box card-semaforo-amarillo shadow-sm" onclick="filtrarPorSemaforoInstructor('amarillo')">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="card-semaforo-label">Próximo (~1 semana)</span>
                        <span class="card-semaforo-icon"><i class="fas fa-clock"></i></span>
                    </div>
                    <div class="card-semaforo-value" style="color:#ea580c;">{{ $stats['amarillo'] ?? 0 }}</div>
                    <div class="card-semaforo-subtitle">Entre 3 y 7 días</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl">
            <div class="card card-semaforo-box card-semaforo-verde shadow-sm" onclick="filtrarPorSemaforoInstructor('verde')">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="card-semaforo-label">En tiempo (≥ 2 semanas)</span>
                        <span class="card-semaforo-icon"><i class="fas fa-circle-check"></i></span>
                    </div>
                    <div class="card-semaforo-value" style="color:#16a34a;">{{ $stats['verde'] ?? 0 }}</div>
                    <div class="card-semaforo-subtitle">≥ 8 días de anticipación</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl">
            <div class="card card-semaforo-box card-semaforo-neutro shadow-sm" onclick="filtrarPorSemaforoInstructor('sin_color')">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="card-semaforo-label d-inline-flex align-items-center gap-2">
                            <span class="semaforo-indicator semaforo-neutro" aria-hidden="true"></span>
                            Pospuestas
                        </span>
                        <span class="card-semaforo-icon"><i class="fas fa-calendar-minus"></i></span>
                    </div>
                    <div class="card-semaforo-value text-dark">{{ $stats['pospuestas'] ?? 0 }}</div>
                    <div class="card-semaforo-subtitle">Estado gris</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl">
            <div class="card card-semaforo-box card-semaforo-azul shadow-sm" onclick="filtrarPorSemaforoInstructor('finalizadas')">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="card-semaforo-label">Finalizadas</span>
                        <span class="card-semaforo-icon"><i class="fas fa-circle-check"></i></span>
                    </div>
                    <div class="card-semaforo-value" style="color:#0070b8;">{{ $stats['finalizadas'] ?? 0 }}</div>
                    <div class="card-semaforo-subtitle">Actividades completadas</div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <ul class="nav nav-pills p-1 bg-light rounded-pill border" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ request('vista', 'calendario') !== 'lista' ? 'active' : '' }} rounded-pill px-4 fw-bold" id="tab-calendario" data-bs-toggle="pill" data-bs-target="#content-calendario" type="button" role="tab">
                    <i class="fas fa-calendar-alt me-2"></i>Vista Calendario
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ request('vista') === 'lista' ? 'active' : '' }} rounded-pill px-4 fw-bold" id="tab-lista" data-bs-toggle="pill" data-bs-target="#content-lista" type="button" role="tab">
                    <i class="fas fa-list-ul me-2"></i>Vista Lista & Semáforo
                </button>
            </li>
        </ul>
    </div>

    <!-- Filtros en la misma disposición del cronograma del administrador -->
    <div class="mb-4">
        <div class="card cronograma-filter-card border-0 shadow-sm">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('controlecp.instructor.cronograma') }}" id="formFiltrosCronograma">
                    <input type="hidden" name="vista" id="filtroVista" value="{{ request('vista', 'calendario') }}">
                    <input type="hidden" name="mes" value="{{ $currentMes }}">
                    <input type="hidden" name="semaforo" id="filtroSemaforo" value="{{ request('semaforo') }}">
                    <div class="cronograma-filter-grid">
                        <div class="search-filter">
                            <label for="filtroSearch" class="form-label small fw-semibold">Buscar</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 ps-3"><i class="fas fa-magnifying-glass"></i></span>
                                <input type="text" name="search" id="filtroSearch" value="{{ request('search') }}" class="form-control border-start-0 search-field" placeholder="Actividad, ficha, lugar...">
                            </div>
                        </div>

                        <div class="position-relative">
                            <label for="filtroFicha" class="form-label small fw-semibold">Ficha</label>
                            <input id="filtroFicha" type="search" name="ficha" class="form-control"
                                   value="{{ $fichas->firstWhere('id', request('ficha_id'))?->code ?? request('ficha') }}"
                                   placeholder="Buscar..." autocomplete="off" aria-autocomplete="list"
                                   aria-controls="cronogramaFichaResults" aria-expanded="false">
                            <input id="filtroFichaId" type="hidden" name="ficha_id" value="{{ request('ficha_id') }}">
                            <div id="cronogramaFichaResults" class="ficha-search-results" role="listbox"></div>
                        </div>

                        <div>
                            <label for="filtroMomento" class="form-label small fw-semibold">Momento</label>
                            <select id="filtroMomento" name="momento_id" class="form-select">
                                <option value="">Todos</option>
                                @foreach($momentos as $momento)
                                    <option value="{{ $momento->id }}" @selected(request('momento_id') == $momento->id)>{{ $momento->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="filtroEstado" class="form-label small fw-semibold">Estado</label>
                            <select id="filtroEstado" name="estado" class="form-select">
                                <option value="">Todos</option>
                                @foreach(['Pendiente','Pospuesta','Finalizada','Cancelada'] as $estado)
                                    <option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ $estado }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="filtroFechaDesde" class="form-label small fw-semibold">Desde</label>
                            <input id="filtroFechaDesde" type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
                        </div>

                        <div>
                            <label for="filtroFechaHasta" class="form-label small fw-semibold">Hasta</label>
                            <input id="filtroFechaHasta" type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
                        </div>
                        <div class="filter-actions">
                            <button type="submit" class="btn filter-submit rounded-3" title="Buscar actividades">
                                <i class="fas fa-search me-1"></i>Buscar
                            </button>
                            <a href="{{ route('controlecp.instructor.cronograma', ['vista' => request('vista', 'calendario')]) }}" class="btn filter-reset rounded-3" title="Restablecer filtros" aria-label="Restablecer filtros">
                                <i class="fas fa-rotate-left"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade {{ request('vista', 'calendario') !== 'lista' ? 'show active' : '' }}" id="content-calendario" role="tabpanel">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-header bg-white p-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <a href="{{ route('controlecp.instructor.cronograma', array_merge(request()->query(), ['mes' => $mesActual->copy()->subMonth()->format('Y-m')])) }}" class="btn btn-sm btn-outline-secondary rounded-circle" title="Mes Anterior" style="width: 34px; height: 34px;">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <h4 class="h5 fw-bold mb-0 text-dark" style="font-family: 'Outfit', sans-serif; min-width: 170px; text-align: center;">
                            {{ $mesActual->translatedFormat('F Y') }}
                        </h4>
                        <a href="{{ route('controlecp.instructor.cronograma', array_merge(request()->query(), ['mes' => $mesActual->copy()->addMonth()->format('Y-m')])) }}" class="btn btn-sm btn-outline-secondary rounded-circle" title="Mes Siguiente" style="width: 34px; height: 34px;">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('controlecp.instructor.cronograma', array_merge(request()->query(), ['mes' => now()->format('Y-m')])) }}" class="btn btn-sm cronograma-today-button rounded-2 px-3">
                            <i class="fas fa-calendar-day me-1"></i>Hoy
                        </a>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="calendar-grid">
                        @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $diaNombre)
                            <div class="calendar-day-header">{{ $diaNombre }}</div>
                        @endforeach

                        @foreach($diasDelMes as $dia)
                            @php
                                $eventosDia = $calendarEvents->get($dia->toDateString(), collect());
                                $esHoy = $dia->isToday();
                                $semaforoDia = collect(['rojo', 'amarillo', 'verde', 'neutro'])->first(fn ($semaforo) =>
                                    $eventosDia->contains(fn ($evento) =>
                                        $evento->estado === 'Pendiente'
                                            ? $evento->semaforo === $semaforo
                                            : $semaforo === 'neutro'
                                    )
                                ) ?? 'neutro';
                            @endphp
                            <div class="calendar-day-cell {{ $esHoy ? 'today' : '' }} {{ $dia->month !== $mesActual->month ? 'other-month' : '' }} {{ $eventosDia->isNotEmpty() ? 'has-events' : '' }}"
                                 data-calendario-fecha="{{ $dia->toDateString() }}"
                                 @if($eventosDia->isNotEmpty()) role="button" tabindex="0" aria-label="{{ $dia->translatedFormat('d F Y') }}, {{ $eventosDia->count() }} actividades" @endif>
                                <div class="calendar-day-header-top">
                                    <span class="calendar-day-number">{{ $dia->day }}</span>
                                    @if($esHoy)
                                        <span class="badge bg-success small" style="font-size: 0.6rem;">Hoy</span>
                                    @endif
                                </div>
                                @if($eventosDia->isNotEmpty())
                                    <span class="calendar-day-activity-indicator semaforo-{{ $semaforoDia }}">
                                        <span class="activity-dot"></span>
                                        {{ $eventosDia->count() }} {{ $eventosDia->count() === 1 ? 'actividad' : 'actividades' }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="modal fade" id="modalActividadesDiaInstructor" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                        <div class="modal-header border-0 pb-0">
                            <div>
                                <h5 class="modal-title fw-bold" id="actividadesDiaTituloInstructor" style="color: #001A29;">Actividades del día</h5>
                                <p class="text-muted small mb-0" id="actividadesDiaResumenInstructor"></p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body" id="actividadesDiaListaInstructor"></div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="modalDetalleActividadInstructor" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                        <div class="modal-header border-0 pb-0 pt-4 px-4">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span id="detalleActividadEstadoInstructor" class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle"></span>
                                    <span id="detalleActividadFichaInstructor" class="badge bg-dark-subtle text-dark border font-monospace"></span>
                                </div>
                                <h5 class="modal-title fw-bold text-dark mb-0" id="detalleActividadTituloInstructor"></h5>
                                <p class="text-muted small mb-0" id="detalleActividadNombreInstructor"></p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body p-4" id="detalleActividadCuerpoInstructor"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade {{ request('vista') === 'lista' ? 'show active' : '' }}" id="content-lista" role="tabpanel">
            <div class="card border-0 shadow-sm" style="border-radius: 18px; overflow: hidden;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tablaCronograma">
                            <thead style="background-color: #001A29; color: #ffffff;" class="small fw-bold">
                                <tr>
                                    <th class="ps-4 py-3 text-center" style="width: 90px;">Semáforo</th>
                                    <th class="py-3">Fecha y Horario</th>
                                    <th class="py-3">Ficha / Programa</th>
                                    <th class="py-3">Actividad &amp; Momento</th>
                                    <th class="py-3">Lugar</th>
                                    <th class="py-3 text-center">Estado</th>
                                    <th class="text-end pe-4 py-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cronogramas as $cronograma)
                                    @php
                                        $sem = $cronograma->semaforo;
                                        $actividad = $cronograma->actividad;
                                        $numeroActividad = $actividad ? ($actividad->numero ? "Actividad {$actividad->numero}" : "Actividad {$actividad->id}") : 'Actividad';
                                        $nombreMomento = $actividad?->momento?->nombre ?? 'Sin momento';
                                        $estadoSemaforoGris = $cronograma->estado ?: 'Otro estado';
                                        $diasRestantes = $cronograma->fecha
                                            ? \Carbon\Carbon::today()->diffInDays($cronograma->fecha, false)
                                            : null;
                                    @endphp
                                    <tr>
                                        <td class="ps-4 text-center">
                                            @if($sem === 'rojo')
                                                <span class="semaforo-indicator semaforo-rojo" title="Semáforo rojo: hoy, vencida o en menos de 2 días"></span>
                                                <div class="small text-danger fw-bold" style="font-size: 0.68rem;">
                                                    {{ $diasRestantes < 0 ? abs($diasRestantes).'d atrasada' : ($diasRestantes == 0 ? '¡Hoy!' : $diasRestantes.'d') }}
                                                </div>
                                            @elseif($sem === 'amarillo')
                                                <span class="semaforo-indicator semaforo-amarillo" title="Semáforo amarillo: entre 3 y 7 días"></span>
                                                <div class="small text-warning-emphasis fw-bold" style="font-size: 0.68rem;">{{ $diasRestantes }}d</div>
                                            @elseif($sem === 'verde')
                                                <span class="semaforo-indicator semaforo-verde" title="Semáforo verde: 8 días o más"></span>
                                                <div class="small text-success fw-bold" style="font-size: 0.68rem;">{{ $diasRestantes }}d</div>
                                            @else
                                                <div class="d-flex flex-column align-items-center gap-1">
                                                    <span class="semaforo-indicator semaforo-neutro semaforo-indicator-large" title="{{ $estadoSemaforoGris }}" aria-label="{{ $estadoSemaforoGris }}"></span>
                                                    <span class="small text-muted fw-semibold" style="font-size: 0.68rem;">{{ $estadoSemaforoGris }}</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">
                                                <i class="far fa-calendar text-muted me-1"></i>
                                                {{ $cronograma->fecha?->translatedFormat('d M Y') ?? '--' }}
                                            </div>
                                            <div class="small text-muted">
                                                <i class="far fa-clock text-muted me-1"></i>{{ $cronograma->horario_formateado }}
                                            </div>
                                            @if($cronograma->fecha_original)
                                                <div class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size: 0.65rem;" title="Fecha antes de posponer">
                                                    Orig: {{ $cronograma->fecha_original->format('d/m/Y') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-dark-subtle text-dark border font-monospace">
                                                Ficha {{ $cronograma->ficha?->code ?? '--' }}
                                            </span>
                                            <div class="small text-muted" style="max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $cronograma->ficha?->program?->name }}">
                                                {{ $cronograma->ficha?->program?->name ?? 'Programa no registrado' }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">
                                                <span class="text-primary">{{ $numeroActividad }}</span> – {{ $nombreMomento }}
                                            </div>
                                            <div class="small text-muted" style="max-width: 240px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $actividad?->nombre }}">
                                                {{ $actividad?->nombre ?? 'Actividad sin nombre' }}
                                            </div>
                                            @if($cronograma->recursos->isNotEmpty())
                                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.68rem;">
                                                    <i class="fas fa-boxes-stacked me-1"></i>{{ $cronograma->recursos->count() }} recurso(s)
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="small text-muted">
                                                <i class="fas fa-location-dot text-danger me-1"></i>{{ $cronograma->lugar ?: 'Sin lugar' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($cronograma->estado === 'Pendiente')
                                                <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">Pendiente</span>
                                            @elseif($cronograma->estado === 'Pospuesta')
                                                <span class="badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle px-2 py-1" title="{{ $cronograma->motivo_posposicion }}">
                                                    <i class="fas fa-clock-rotate-left me-1"></i>Pospuesta
                                                </span>
                                            @elseif($cronograma->estado === 'Finalizada')
                                                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                    <i class="fas fa-check-circle me-1"></i>Finalizada
                                                </span>
                                            @else
                                                <span class="badge rounded-pill bg-secondary px-2 py-1">{{ $cronograma->estado }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            @if($cronograma->estado !== 'Finalizada' && $cronograma->estado !== 'Cancelada')
                                                <div class="cronograma-row-actions">
                                                    <button type="button" class="btn cronograma-row-action cronograma-row-action--postpone" title="Posponer actividad" aria-label="Posponer actividad" data-bs-toggle="modal" data-bs-target="#posponer-{{ $cronograma->id_cronograma }}">
                                                        <i class="fas fa-calendar-plus"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="fas fa-calendar-xmark fa-3x mb-3 text-secondary opacity-50"></i>
                                            <h5 class="fw-bold">No se encontraron actividades programadas</h5>
                                            <p class="small mb-0">No hay actividades asignadas que coincidan con los filtros.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if(method_exists($cronogramas, 'links'))
                        <div class="p-3">
                            @if ($cronogramas->hasPages())
                                <nav aria-label="Paginación del cronograma">
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
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@foreach($cronogramas as $cronograma)
    @if($cronograma->estado !== 'Finalizada' && $cronograma->estado !== 'Cancelada')
        <div class="modal fade cronograma-postpone-modal" id="posponer-{{ $cronograma->id_cronograma }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="{{ route('controlecp.instructor.cronograma.posponer', $cronograma->id_cronograma) }}" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title postpone-title">
                            <span class="postpone-title-icon"><i class="fas fa-calendar-plus" aria-hidden="true"></i></span>
                            Posponer actividad
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="postpone-activity-summary">
                            <i class="fas fa-list-check" aria-hidden="true"></i>
                            <span>Actividad {{ $cronograma->actividad?->numero ?? $cronograma->id_actividad }} · {{ $cronograma->actividad?->momento?->nombre ?? 'Sin momento' }}</span>
                        </div>
                        <label class="form-label small" for="nueva-fecha-{{ $cronograma->id_cronograma }}">Nueva fecha</label>
                        <input id="nueva-fecha-{{ $cronograma->id_cronograma }}" type="date" name="nueva_fecha" class="form-control" min="{{ now()->format('Y-m-d') }}" value="{{ $cronograma->fecha?->format('Y-m-d') }}" required>
                        <label class="form-label small mt-3" for="motivo-{{ $cronograma->id_cronograma }}">Motivo</label>
                        <textarea id="motivo-{{ $cronograma->id_cronograma }}" name="motivo" class="form-control" minlength="5" rows="3" required placeholder="Indica el motivo de la posposición"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn postpone-submit px-4">
                            <i class="fas fa-floppy-disk me-1"></i>Guardar posposición
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endforeach

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fichas = @json($fichas->map(fn ($ficha) => ['id' => $ficha->id, 'code' => $ficha->code])->values());
        const eventosInstructor = @json($eventosInstructor);
        const fichaInput = document.getElementById('filtroFicha');
        const fichaId = document.getElementById('filtroFichaId');
        const fichaResults = document.getElementById('cronogramaFichaResults');

        const hideResults = () => {
            fichaResults.classList.remove('is-visible');
            fichaInput.setAttribute('aria-expanded', 'false');
        };
        const showResults = () => {
            const term = fichaInput.value.trim().toLocaleLowerCase();
            fichaResults.replaceChildren();
            if (!term) {
                hideResults();
                return;
            }

            const matches = fichas.filter(ficha => String(ficha.code).toLocaleLowerCase().includes(term)).slice(0, 5);
            if (!matches.length) {
                const empty = document.createElement('div');
                empty.className = 'ficha-search-empty';
                empty.textContent = 'Sin coincidencias';
                fichaResults.append(empty);
            } else {
                matches.forEach(ficha => {
                    const option = document.createElement('button');
                    option.type = 'button';
                    option.className = 'ficha-search-option';
                    option.setAttribute('role', 'option');
                    option.textContent = ficha.code;
                    option.addEventListener('click', () => {
                        fichaInput.value = ficha.code;
                        fichaId.value = ficha.id;
                        hideResults();
                    });
                    fichaResults.append(option);
                });
            }
            fichaResults.classList.add('is-visible');
            fichaInput.setAttribute('aria-expanded', 'true');
        };

        fichaInput.addEventListener('input', () => {
            fichaId.value = '';
            showResults();
        });
        fichaInput.addEventListener('focus', showResults);
        fichaInput.addEventListener('keydown', event => {
            if (event.key === 'Escape') hideResults();
            if (event.key === 'Enter' && fichaResults.querySelector('.ficha-search-option')) {
                event.preventDefault();
                fichaResults.querySelector('.ficha-search-option').click();
            }
        });
        document.addEventListener('click', event => {
            if (!fichaResults.parentElement.contains(event.target)) hideResults();
        });

        const filtroVista = document.getElementById('filtroVista');
        document.querySelectorAll('#pills-tab [data-bs-toggle="pill"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', event => {
                filtroVista.value = event.target.id === 'tab-lista' ? 'lista' : 'calendario';
            });
        });

        const modalDia = document.getElementById('modalActividadesDiaInstructor');
        const modalDetalle = document.getElementById('modalDetalleActividadInstructor');
        const tituloDia = document.getElementById('actividadesDiaTituloInstructor');
        const resumenDia = document.getElementById('actividadesDiaResumenInstructor');
        const listaDia = document.getElementById('actividadesDiaListaInstructor');
        const crearDato = (etiqueta, valor) => {
            const contenedor = document.createElement('div');
            contenedor.className = 'p-3 bg-light rounded-4 h-100';
            const nombre = document.createElement('div');
            nombre.className = 'small text-muted fw-semibold mb-1';
            nombre.textContent = etiqueta;
            const contenido = document.createElement('div');
            contenido.className = 'fw-semibold text-dark';
            contenido.textContent = valor || '—';
            contenedor.append(nombre, contenido);
            return contenedor;
        };
        const mostrarDetalle = evento => {
            const estado = document.getElementById('detalleActividadEstadoInstructor');
            estado.textContent = evento.estado || 'Sin estado';
            estado.className = 'badge rounded-pill ' + (evento.estado === 'Finalizada'
                ? 'bg-success-subtle text-success border border-success-subtle'
                : evento.estado === 'Pospuesta'
                    ? 'bg-warning-subtle text-warning border border-warning-subtle'
                    : 'bg-primary-subtle text-primary border border-primary-subtle');
            document.getElementById('detalleActividadFichaInstructor').textContent = `Ficha ${evento.ficha}`;
            document.getElementById('detalleActividadTituloInstructor').textContent = evento.titulo;
            document.getElementById('detalleActividadNombreInstructor').textContent = evento.nombre;

            const cuerpo = document.getElementById('detalleActividadCuerpoInstructor');
            cuerpo.replaceChildren();
            const datos = document.createElement('div');
            datos.className = 'row g-3';
            const fecha = evento.fecha
                ? new Date(`${evento.fecha}T00:00:00`).toLocaleDateString('es-CO', { day: 'numeric', month: 'long', year: 'numeric' })
                : '—';
            [
                ['Fecha y horario', `${fecha} · ${evento.horario}`],
                ['Instructor', evento.instructor],
                ['Lugar', evento.lugar],
                ['Programa', evento.programa],
            ].forEach(([etiqueta, valor]) => {
                const columna = document.createElement('div');
                columna.className = 'col-12 col-md-6';
                columna.append(crearDato(etiqueta, valor));
                datos.append(columna);
            });
            cuerpo.append(datos);

            if (evento.motivo_posposicion) {
                const motivo = crearDato('Motivo de la posposición', evento.motivo_posposicion);
                motivo.classList.add('mt-3');
                cuerpo.append(motivo);
            }
            if (evento.recursos?.length) {
                const recursos = crearDato('Recursos', evento.recursos.map(recurso => `${recurso.nombre} (${recurso.cantidad})`).join(', '));
                recursos.classList.add('mt-3');
                cuerpo.append(recursos);
            }
            bootstrap.Modal.getOrCreateInstance(modalDetalle).show();
        };
        const mostrarActividadesDia = fecha => {
            const eventos = eventosInstructor
                .filter(evento => evento.fecha === fecha)
                .sort((a, b) => String(a.hora_inicio || '').localeCompare(String(b.hora_inicio || '')));
            const [year, month, day] = fecha.split('-').map(Number);
            const fechaTexto = new Date(year, month - 1, day).toLocaleDateString('es-CO', {
                weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
            });
            tituloDia.textContent = `Actividades del ${fechaTexto}`;
            resumenDia.textContent = `${eventos.length} ${eventos.length === 1 ? 'actividad programada' : 'actividades programadas'}`;
            listaDia.replaceChildren();

            eventos.forEach(evento => {
                const tarjeta = document.createElement('div');
                tarjeta.className = 'calendar-day-activity';
                const encabezado = document.createElement('div');
                encabezado.className = 'd-flex flex-wrap justify-content-between align-items-start gap-2';
                const nombre = document.createElement('h6');
                nombre.className = 'fw-bold mb-1';
                nombre.textContent = evento.titulo;
                const estado = document.createElement('span');
                estado.className = 'badge rounded-pill bg-light text-dark border';
                estado.textContent = evento.estado || 'Sin estado';
                encabezado.append(nombre, estado);

                const horario = document.createElement('div');
                horario.className = 'activity-meta';
                horario.textContent = `Ficha ${evento.ficha} · ${evento.horario}`;
                const ubicacion = document.createElement('div');
                ubicacion.className = 'activity-meta';
                ubicacion.textContent = `Instructor: ${evento.instructor} · Lugar: ${evento.lugar}`;
                const acciones = document.createElement('div');
                acciones.className = 'text-end mt-2';
                const botonDetalle = document.createElement('button');
                botonDetalle.type = 'button';
                botonDetalle.className = 'btn btn-sm btn-outline-success rounded-pill px-3';
                botonDetalle.innerHTML = '<i class="fas fa-circle-info me-1" aria-hidden="true"></i>Ver detalle';
                botonDetalle.addEventListener('click', () => {
                    modalDia.addEventListener('hidden.bs.modal', () => mostrarDetalle(evento), { once: true });
                    bootstrap.Modal.getOrCreateInstance(modalDia).hide();
                });
                acciones.append(botonDetalle);
                tarjeta.append(encabezado, horario, ubicacion, acciones);
                listaDia.append(tarjeta);
            });

            bootstrap.Modal.getOrCreateInstance(modalDia).show();
        };

        document.querySelectorAll('.calendar-day-cell[data-fecha]').forEach(celda => {
            const abrir = () => mostrarActividadesDia(celda.dataset.fecha);
            celda.addEventListener('click', abrir);
            celda.addEventListener('keydown', event => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    abrir();
                }
            });
        });
    });

    function filtrarPorSemaforoInstructor(semaforo) {
        document.getElementById('filtroSemaforo').value = semaforo;
        document.getElementById('formFiltrosCronograma').submit();
    }
</script>
@endpush
@endsection