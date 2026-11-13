@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .apr-crono-header {
        background: linear-gradient(120deg, #001A29, #00324D);
        border-radius: 22px;
        padding: 1.5rem 1.75rem;
        color: #fff;
    }
    .apr-crono-header h1 { color: #fff; }
    .apr-crono-header p { color: #c7d4da; }
    .apr-crono-eyebrow {
        color: #62E31D;
        font-size: .7rem;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }
    .apr-kpi {
        height: 100%;
        min-height: 124px;
        padding: 1.15rem 1.25rem;
        border: 2px solid #e1e9ed;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 3px 12px rgba(0,26,41,.025);
    }
    .apr-kpi-top { display: flex; justify-content: space-between; align-items: flex-start; gap: .75rem; }
    .apr-kpi-label { color: #64748b; font-size: .92rem; }
    .apr-kpi-icon {
        display: inline-flex;
        flex: 0 0 38px;
        width: 38px;
        height: 38px;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: .95rem;
    }
    .apr-kpi-value { margin-top: .35rem; font-size: 1.7rem; font-weight: 800; line-height: 1; }
    .apr-kpi-hint { margin-top: .35rem; color: #94a3b8; font-size: .82rem; }
    .apr-kpi--green { border-color: #a9dfbf; }
    .apr-kpi--green .apr-kpi-value { color: #39A900; }
    .apr-kpi--green .apr-kpi-icon { background: #dcfce7; color: #16a34a; }
    .apr-kpi--blue { border-color: #a7d7f0; }
    .apr-kpi--blue .apr-kpi-value { color: #0878c9; }
    .apr-kpi--blue .apr-kpi-icon { background: #dbeafe; color: #0284c7; }
    .apr-kpi--amber { border-color: #f3d09e; }
    .apr-kpi--amber .apr-kpi-value { color: #d97706; }
    .apr-kpi--amber .apr-kpi-icon { background: #fef3c7; color: #d97706; }
    .apr-kpi--purple { border-color: #d7c3ff; }
    .apr-kpi--purple .apr-kpi-value { color: #7c3aed; }
    .apr-kpi--purple .apr-kpi-icon { background: #ede9fe; color: #7c3aed; }
    .apr-crono-filters {
        padding: 1rem 1.15rem;
        border-radius: 16px;
        background: #001A29;
        box-shadow: 0 5px 18px rgba(0,26,41,.08);
    }
    .apr-crono-filters label {
        display: block;
        margin-bottom: .3rem;
        color: #fff;
        font-size: .75rem;
        font-weight: 700;
    }
    .apr-crono-filters .form-control,
    .apr-crono-filters .form-select {
        min-width: 0;
        height: 42px;
        border: 1px solid #dce5eb;
        border-radius: 9px;
        color: #001A29;
    }
    .apr-crono-filter-submit {
        height: 42px;
        border: 1px solid #39A900;
        border-radius: 9px;
        background: #39A900;
        color: #fff;
        font-weight: 700;
    }
    .apr-crono-filter-submit:hover { background: #2e8800; color: #fff; }
    .apr-crono-today,
    .apr-crono-today:hover,
    .apr-crono-today:focus,
    .apr-crono-today:active {
        border-color: #39A900 !important;
        background-color: #39A900 !important;
        color: #fff !important;
    }
    .apr-crono-filter-reset {
        width: 42px;
        height: 42px;
        border: 1px solid #dce5eb;
        border-radius: 9px;
        background: #fff;
        color: #001A29;
    }
    .apr-crono-tabs .nav-link {
        border-radius: 999px;
        padding: .7rem 1.25rem;
        color: #001A29;
        font-size: .95rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .apr-crono-tabs .nav-link.active {
        background: #39B500;
        color: #fff;
    }
    .apr-crono-tabs {
        display: inline-flex;
        align-items: center;
        padding: .3rem;
        border: 1px solid #dce5eb;
        border-radius: 999px;
        background: #f8fafc;
        gap: .25rem !important;
    }
    .apr-crono-card {
        background: #fff;
        border: 1px solid #e0ece4;
        border-radius: 16px;
        box-shadow: 0 2px 10px rgba(0, 26, 41, .05);
        overflow: hidden;
    }
    .apr-crono-card-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #edf1f4;
    }
    .apr-calendar {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 1px;
        background: #e2e8f0;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
    }
    .apr-calendar-wrap { padding: .7rem; }
    .apr-calendar-weekday {
        background: #001A29;
        color: #fff;
        text-align: center;
        padding: .65rem .25rem;
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .apr-calendar-day {
        min-height: 122px;
        padding: .45rem;
        background: #fff;
    }
    .apr-calendar-day.is-outside { background: #f8fafc; color: #94a3b8; }
    .apr-calendar-day.is-non-working-day { background: #e8eef3; color: #718397; }
    .apr-calendar-day.is-today { box-shadow: none; }
    .apr-calendar-number { font-size: .8rem; font-weight: 700; }
    .apr-calendar-day.is-today .apr-calendar-number {
        display: inline-flex;
        width: 24px;
        height: 24px;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #39A900;
        color: #fff;
        box-shadow: 0 2px 6px rgba(57,169,0,.28);
    }
    .apr-calendar-today-label {
        float: right;
        padding: .12rem .45rem;
        border-radius: 999px;
        background: #168b52;
        color: #fff;
        font-size: .62rem;
        font-weight: 700;
        line-height: 1.4;
    }
    .apr-calendar-event {
        display: flex;
        width: 100%;
        margin-top: .3rem;
        padding: .28rem .4rem;
        border: 0;
        border-radius: 999px;
        text-align: left;
        font-size: .68rem;
        line-height: 1.25;
        font-weight: 700;
        overflow: hidden;
        align-items: center;
        gap: .3rem;
        white-space: nowrap;
    }
    .apr-calendar-event.semaforo-rojo { background: #fee2e2; color: #991b1b; }
    .apr-calendar-event.semaforo-amarillo { background: #fef3c7; color: #92400e; }
    .apr-calendar-event.semaforo-verde { background: #dcfce7; color: #166534; }
    .apr-calendar-event.semaforo-neutro { background: #e2e8f0; color: #475569; }
    .apr-calendar-event-dot {
        flex: 0 0 7px;
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
    }
    .apr-calendar-event-label { overflow: hidden; text-overflow: ellipsis; }
    .apr-semaforo {
        display: inline-block;
        width: 12px;
        height: 12px;
        margin-right: .4rem;
        border-radius: 50%;
        vertical-align: middle;
    }
    .apr-semaforo.semaforo-rojo { background: #dc2626; }
    .apr-semaforo.semaforo-amarillo { background: #f59e0b; }
    .apr-semaforo.semaforo-verde { background: #39A900; }
    .apr-semaforo.semaforo-neutro { background: #94a3b8; }
    .apr-crono-table thead th {
        background: #fff;
        color: #001A29;
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .35px;
        white-space: nowrap;
        border-bottom: 1px solid #dce5eb;
    }
    .apr-crono-table tbody td { vertical-align: middle; border-color: #e2e8f0; }
    .apr-crono-table tbody tr:last-child td { border-bottom: 0; }
    .apr-crono-date, .apr-crono-time { display: flex; align-items: center; gap: .4rem; }
    .apr-crono-date { color: #1e293b; font-weight: 700; }
    .apr-crono-time { color: #64748b; font-size: .84rem; }
    .apr-crono-date i, .apr-crono-time i { width: 15px; color: #64748b; text-align: center; }
    .apr-crono-activity-title { color: #0878e8; font-weight: 800; }
    .apr-crono-activity-name { color: #64748b; font-size: .84rem; }
    .apr-crono-location { display: flex; align-items: center; gap: .35rem; }
    .apr-crono-location i { color: #e5484d; }
    .apr-semaforo-label {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        font-size: .78rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .apr-semaforo-label.semaforo-rojo { color: #dc2626; }
    .apr-semaforo-label.semaforo-amarillo { color: #b77900; }
    .apr-semaforo-label.semaforo-verde { color: #16803c; }
    .apr-semaforo-label.semaforo-neutro { color: #64748b; }
    .apr-crono-status {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .3rem .65rem;
        border: 1px solid transparent;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 750;
        white-space: nowrap;
    }
    .apr-crono-status--pendiente { border-color: #a7ccff; background: #e5f0ff; color: #1263c6; }
    .apr-crono-status--proceso { border-color: #8bd8e8; background: #e2f7fb; color: #087b91; }
    .apr-crono-status--finalizada { border-color: #9bd8b5; background: #e1f4e9; color: #16804a; }
    .apr-crono-status--pospuesta { border-color: #ffd675; background: #fff5d8; color: #a66a00; }
    .apr-crono-status--cancelada { border-color: #d7dce2; background: #f1f3f5; color: #64748b; }
    .apr-crono-actions { display: flex; justify-content: flex-end; gap: .4rem; }
    .apr-crono-action {
        display: inline-flex;
        width: 38px;
        height: 38px;
        align-items: center;
        justify-content: center;
        border: 1px solid transparent;
        border-radius: 11px;
        font-size: .95rem;
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .apr-crono-action:hover { transform: translateY(-1px); box-shadow: 0 3px 8px rgba(0,26,41,.12); }
    .apr-crono-action--view { border-color: #b7dcff; background: #edf7ff; color: #0878c9; }
    .apr-crono-action--history { border-color: #d9c6ff; background: #f5f0ff; color: #7c3aed; }
    .apr-crono-detail {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
        padding: .8rem 1rem;
    }
    .apr-crono-page .modal-content { border: 0; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,26,41,.22); }
    .apr-crono-page .modal-header { color: #fff; background: #001A29; border-bottom: 2px solid #39A900; }
    .apr-crono-page .modal-body { padding: 1.4rem; }
    .apr-crono-page .modal-footer { padding: 0 1.4rem 1.3rem; }
    .apr-crono-page .modal-footer .btn-secondary { border: 0; background: #eef2f5; color: #334155; font-weight: 700; }
    .apr-crono-detail { border-color: #e1e9ed; border-radius: 14px; background: #fff; padding: 1rem 1.1rem; }
    .apr-crono-detail .row { padding: .42rem 0; border-bottom: 1px solid #edf1f4; }
    .apr-crono-detail .row:last-child { border-bottom: 0; }
    .apr-change-item { padding: .75rem .85rem; border: 1px solid #e1e9ed; border-left: 3px solid #39A900; border-radius: 10px; background: #fbfdfb; }
    .apr-day-activity {
        width: 100%;
        padding: .9rem 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
        color: #001A29;
        text-align: left;
        transition: border-color .15s ease, background .15s ease;
    }
    .apr-day-activity:hover { border-color: #39A900; background: #f7fcf4; }
    @media (max-width: 767px) {
        .apr-calendar-day { min-height: 78px; padding: .25rem; }
        .apr-calendar-event { font-size: .6rem; padding: .2rem; }
        .apr-calendar-weekday { font-size: .65rem; }
        .apr-crono-header { padding: 1.15rem; }
        .apr-calendar-wrap { padding: .35rem; }
    }
</style>
@endpush

@section('dashboard-content')
@php
    $fechaHoyCronograma = now()->toDateString();
    $actividadesHoy = $actividades->filter(fn ($actividad) => $actividad->fecha?->toDateString() === $fechaHoyCronograma)->count();
    $actividadesPendientes = $actividades->whereIn('estado', ['Pendiente', 'En proceso'])->count();
    $actividadesFinalizadas = $actividades->where('estado', 'Finalizada')->count();
@endphp
<div class="container-fluid px-3 py-3 apr-crono-page">
    <div class="apr-crono-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="apr-crono-eyebrow mb-1">Control ECP · Aprendiz</div>
            <h1 class="h3 fw-bold mb-1"><i class="fas fa-calendar-days me-2" style="color:#62E31D"></i>Mi cronograma de actividades</h1>
            <p class="mb-0 small">Consulta cuándo te corresponde cada actividad de tu ficha.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="apr-kpi apr-kpi--green">
                <div class="apr-kpi-top">
                    <div class="apr-kpi-label">Actividades programadas</div>
                    <span class="apr-kpi-icon" aria-hidden="true"><i class="fas fa-calendar-check"></i></span>
                </div>
                <div class="apr-kpi-value">{{ $actividades->count() }}</div>
                <div class="apr-kpi-hint">En el cronograma de tu ficha</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="apr-kpi apr-kpi--blue">
                <div class="apr-kpi-top">
                    <div class="apr-kpi-label">Programadas para hoy</div>
                    <span class="apr-kpi-icon" aria-hidden="true"><i class="fas fa-calendar-day"></i></span>
                </div>
                <div class="apr-kpi-value">{{ $actividadesHoy }}</div>
                <div class="apr-kpi-hint">Actividades con fecha de hoy</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="apr-kpi apr-kpi--amber">
                <div class="apr-kpi-top">
                    <div class="apr-kpi-label">Por realizar</div>
                    <span class="apr-kpi-icon" aria-hidden="true"><i class="fas fa-hourglass-half"></i></span>
                </div>
                <div class="apr-kpi-value">{{ $actividadesPendientes }}</div>
                <div class="apr-kpi-hint">Pendientes o en proceso</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="apr-kpi apr-kpi--purple">
                <div class="apr-kpi-top">
                    <div class="apr-kpi-label">Finalizadas</div>
                    <span class="apr-kpi-icon" aria-hidden="true"><i class="fas fa-circle-check"></i></span>
                </div>
                <div class="apr-kpi-value">{{ $actividadesFinalizadas }}</div>
                <div class="apr-kpi-hint">Actividades completadas</div>
            </div>
        </div>
    </div>

    <form class="apr-crono-filters mb-4" id="filtrosCronograma">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label for="filtroFecha">Fecha</label>
                <input class="form-control" type="date" id="filtroFecha">
            </div>
            <div class="col-12 col-md-4">
                <label for="filtroActividad">Buscar actividad</label>
                <input class="form-control" type="search" id="filtroActividad" placeholder="Nombre, instructor o lugar">
            </div>
            <div class="col-8 col-md-3">
                <label for="filtroEstado">Estado</label>
                <select class="form-select" id="filtroEstado">
                    <option value="">Todos los estados</option>
                    <option value="Pendiente">Pendiente</option>
                    <option value="En proceso">En proceso</option>
                    <option value="Finalizada">Finalizada</option>
                    <option value="Pospuesta">Pospuesta</option>
                    <option value="Cancelada">Cancelada</option>
                </select>
            </div>
            <div class="col-4 col-md-2 d-flex gap-2">
                <button class="btn apr-crono-filter-submit flex-grow-1" type="submit" aria-label="Aplicar filtros">
                    <i class="fas fa-search me-md-1"></i><span class="d-none d-md-inline">Buscar</span>
                </button>
                <button class="btn apr-crono-filter-reset" type="reset" id="limpiarFiltrosCronograma" aria-label="Limpiar filtros" title="Limpiar filtros">
                    <i class="fas fa-rotate-left"></i>
                </button>
            </div>
        </div>
    </form>

    <ul class="nav nav-pills apr-crono-tabs gap-2 mb-3" id="pestanasCronograma" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pestana-calendario" data-bs-toggle="pill" data-bs-target="#panel-calendario" type="button" role="tab" aria-controls="panel-calendario" aria-selected="true">
                <i class="fas fa-calendar-days me-1"></i>Calendario
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pestana-actividades" data-bs-toggle="pill" data-bs-target="#panel-actividades" type="button" role="tab" aria-controls="panel-actividades" aria-selected="false">
                <i class="fas fa-list-check me-1"></i>Mis actividades
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <section class="tab-pane fade show active" id="panel-calendario" role="tabpanel" aria-labelledby="pestana-calendario" tabindex="0">
            <div class="apr-crono-card mb-4">
                <div class="apr-crono-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h2 class="h5 fw-bold mb-1">Calendario de actividades</h2>
                    </div>
                </div>
                <div class="apr-calendar-wrap">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-3" aria-label="Navegación por mes">
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-sm btn-outline-secondary" type="button" id="mesAnterior" aria-label="Mes anterior"><i class="fas fa-chevron-left"></i></button>
                            <strong id="mesCalendario" class="text-center text-capitalize" style="min-width:160px"></strong>
                            <button class="btn btn-sm btn-outline-secondary" type="button" id="mesSiguiente" aria-label="Mes siguiente"><i class="fas fa-chevron-right"></i></button>
                        </div>
                        <button class="btn btn-sm apr-crono-today" type="button" id="mesActual">Hoy</button>
                    </div>
                    <div class="apr-calendar" id="calendarioAprendiz" aria-label="Calendario mensual"></div>
                </div>
            </div>
        </section>
        <section class="tab-pane fade" id="panel-actividades" role="tabpanel" aria-labelledby="pestana-actividades" tabindex="0">
            <div class="apr-crono-card">
                <div class="apr-crono-card-header">
                    <h2 class="h5 fw-bold mb-0">Mis actividades</h2>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 apr-crono-table">
                        <thead>
                            <tr>
                                <th class="ps-4">Semáforo</th>
                                <th>Fecha y hora</th>
                                <th>Actividad</th>
                                <th>Instructor</th>
                                <th>Lugar</th>
                                <th>Estado</th>
                                <th class="text-end pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaActividadesCronograma">
                            @forelse($actividades as $actividad)
                                @php
                                    $personaInstructor = $actividad->instructor?->person;
                                    $nombreInstructor = $personaInstructor
                                        ? trim($personaInstructor->first_name . ' ' . $personaInstructor->first_last_name)
                                        : ($actividad->instructor?->nickname ?? 'Sin instructor asignado');
                                    $numeroActividad = $actividad->actividad?->numero
                                        ? 'Actividad ' . $actividad->actividad->numero
                                        : 'Actividad ' . ($actividad->actividad?->id ?? '');
                                    $nombreMomento = $actividad->actividad?->momento?->nombre ?? 'Sin momento';
                                    $nombreActividad = $actividad->actividad?->nombre ?? 'Actividad sin nombre';
                                    $semaforo = $actividad->semaforo ?? 'neutro';
                                    $textoFiltro = mb_strtolower($numeroActividad . ' ' . $nombreMomento . ' ' . $nombreActividad . ' ' . $nombreInstructor . ' ' . ($actividad->lugar ?: '') . ' ' . $actividad->estado);
                                @endphp
                                <tr data-fecha="{{ $actividad->fecha?->format('Y-m-d') }}" data-estado="{{ $actividad->estado }}" data-filtro="{{ $textoFiltro }}">
                                    <td class="ps-4">
                                        @if($actividad->semaforo)
                                            <span class="apr-semaforo-label semaforo-{{ $semaforo }}" title="{{ $actividad->semaforo_texto }}">
                                                <span class="apr-semaforo semaforo-{{ $semaforo }}"></span>
                                                {{ $actividad->dias_restantes <= 0 ? ($actividad->dias_restantes === 0 ? 'Hoy' : abs($actividad->dias_restantes) . ' d atrasada') : $actividad->dias_restantes . ' d' }}
                                            </span>
                                        @else
                                            <span class="badge text-bg-light border text-secondary">Sin semáforo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="apr-crono-date"><i class="far fa-calendar"></i>{{ $actividad->fecha?->translatedFormat('d M Y') ?? 'Sin fecha' }}</div>
                                        <div class="apr-crono-time"><i class="far fa-clock"></i>{{ substr($actividad->hora_inicio, 0, 5) }} – {{ substr($actividad->hora_fin, 0, 5) }}</div>
                                        @if($actividad->estado === 'Pospuesta' && $actividad->fecha_original)
                                            <div class="small text-warning-emphasis mt-1">Antes: {{ $actividad->fecha_original->format('d/m/Y') }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="apr-crono-activity-title">{{ $numeroActividad }} – {{ $nombreMomento }}</div>
                                        <div class="apr-crono-activity-name">{{ $nombreActividad }}</div>
                                    </td>
                                    <td>{{ $nombreInstructor }}</td>
                                    <td><span class="apr-crono-location"><i class="fas fa-location-dot"></i>{{ $actividad->lugar ?: 'Sin lugar registrado' }}</span></td>
                                    <td><span class="apr-crono-status @if($actividad->estado === 'Finalizada') apr-crono-status--finalizada @elseif($actividad->estado === 'Pospuesta') apr-crono-status--pospuesta @elseif($actividad->estado === 'Cancelada') apr-crono-status--cancelada @elseif($actividad->estado === 'En proceso') apr-crono-status--proceso @else apr-crono-status--pendiente @endif">
                                        @if($actividad->estado === 'Finalizada')<i class="fas fa-circle-check"></i>@elseif($actividad->estado === 'Pospuesta')<i class="fas fa-clock-rotate-left"></i>@elseif($actividad->estado === 'Cancelada')<i class="fas fa-circle-minus"></i>@elseif($actividad->estado === 'En proceso')<i class="fas fa-spinner"></i>@else<i class="fas fa-clock"></i>@endif
                                        {{ $actividad->estado }}
                                    </span></td>
                                    <td class="text-end pe-4">
                                        <div class="apr-crono-actions">
                                            <button class="apr-crono-action apr-crono-action--view ver-detalle" type="button" data-actividad-id="{{ $actividad->id_cronograma }}" aria-label="Ver detalle" title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="apr-crono-action apr-crono-action--history ver-historial" type="button" data-actividad-id="{{ $actividad->id_cronograma }}" aria-label="Ver historial" title="Ver historial">
                                                <i class="fas fa-clock-rotate-left"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-5">No hay actividades programadas para tu ficha.</td></tr>
                            @endforelse
                            @if($actividades->isNotEmpty())
                                <tr id="sinResultadosCronograma" hidden><td colspan="7" class="text-center text-muted py-5">No hay actividades que coincidan con los filtros.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="detalleActividad" tabindex="-1" aria-labelledby="detalleActividadTitulo" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <div class="small text-uppercase fw-semibold mb-1" style="color:#62E31D">Detalle de actividad</div>
                        <h2 class="modal-title h5 fw-bold mb-0" id="detalleActividadTitulo"></h2>
                    </div>
                    <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="apr-crono-detail mb-4" id="detalleActividadDatos"></div>
                    <h3 class="h6 fw-bold mb-3" id="detalleActividadHistorialTitulo"><i class="fas fa-clock-rotate-left me-2" style="color:#39A900"></i>Historial de programación</h3>
                    <div id="detalleActividadCambios" class="small"></div>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-secondary rounded-pill px-4" type="button" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="actividadesDelDia" tabindex="-1" aria-labelledby="actividadesDelDiaTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <div class="small text-uppercase fw-semibold mb-1" style="color:#62E31D">Cronograma de tu ficha</div>
                        <h2 class="modal-title h5 fw-bold mb-0" id="actividadesDelDiaTitulo">Actividades del día</h2>
                    </div>
                    <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div id="listaActividadesDelDia" class="d-grid gap-2"></div>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-secondary rounded-pill px-4" type="button" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const actividades = @json($actividadesCalendario);

    const calendar = document.getElementById('calendarioAprendiz');
    const monthTitle = document.getElementById('mesCalendario');
    const today = new Date();
    let displayedMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    const eventsByDate = new Map();
    const activityById = new Map(actividades.map(activity => [String(activity.id), activity]));
    const filterForm = document.getElementById('filtrosCronograma');
    const activityRows = [...document.querySelectorAll('#tablaActividadesCronograma tr[data-filtro]')];
    const noResults = document.getElementById('sinResultadosCronograma');
    let activeFilters = {date: '', text: '', state: ''};

    const normalize = value => String(value || '')
        .toLocaleLowerCase('es-CO')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
    const matchesFilters = activity => {
        const searchableText = normalize([
            activity.titulo,
            activity.nombre,
            activity.momento,
            activity.instructor,
            activity.lugar,
            activity.estado,
        ].join(' '));
        return (!activeFilters.date || activity.fecha === activeFilters.date)
            && (!activeFilters.text || searchableText.includes(activeFilters.text))
            && (!activeFilters.state || normalize(activity.estado) === normalize(activeFilters.state));
    };

    const applyFilters = () => {
        activeFilters = {
            date: document.getElementById('filtroFecha').value,
            text: normalize(document.getElementById('filtroActividad').value.trim()),
            state: document.getElementById('filtroEstado').value,
        };
        activityRows.forEach(row => {
            const textMatches = !activeFilters.text || normalize(row.dataset.filtro).includes(activeFilters.text);
            const matches = (!activeFilters.date || row.dataset.fecha === activeFilters.date)
                && textMatches
                && (!activeFilters.state || normalize(row.dataset.estado) === normalize(activeFilters.state));
            row.hidden = !matches;
        });
        if (noResults) noResults.hidden = activityRows.some(row => !row.hidden);
        if (activeFilters.date) {
            const [year, month] = activeFilters.date.split('-').map(Number);
            displayedMonth = new Date(year, month - 1, 1);
        }
        renderCalendar();
    };

    actividades.forEach(activity => {
        if (!activity.fecha) return;
        const events = eventsByDate.get(activity.fecha) || [];
        events.push(activity);
        eventsByDate.set(activity.fecha, events);
    });

    const openDetails = (activity, focusHistory = false) => {
        if (!activity) return;
        document.getElementById('detalleActividadTitulo').textContent = activity.titulo;
        const details = document.getElementById('detalleActividadDatos');
        details.replaceChildren();
        const rows = [
            ['Actividad', activity.nombre],
            ['Momento', activity.momento],
            ['Fecha', activity.fecha ? new Date(`${activity.fecha}T00:00:00`).toLocaleDateString('es-CO', {day:'2-digit', month:'long', year:'numeric'}) : 'Sin fecha'],
            ['Hora', `${activity.hora_inicio} – ${activity.hora_fin}`],
            ['Lugar', activity.lugar],
            ['Instructor', activity.instructor],
            ['Estado', activity.estado],
        ];
        if (activity.estado === 'Pospuesta' && activity.fecha_original) {
            rows.push(['Fecha anterior', new Date(`${activity.fecha_original}T00:00:00`).toLocaleDateString('es-CO')]);
        }
        rows.forEach(([label, value]) => {
            const row = document.createElement('div');
            row.className = 'row g-1 mb-2';
            const name = document.createElement('div');
            name.className = 'col-sm-3 fw-semibold';
            name.textContent = label;
            const content = document.createElement('div');
            content.className = 'col-sm-9';
            content.textContent = value || '—';
            row.append(name, content);
            details.appendChild(row);
        });
        if (activity.motivo_posposicion) {
            const reason = document.createElement('p');
            reason.className = 'small text-muted mb-0 mt-2';
            reason.textContent = `Motivo de posposición: ${activity.motivo_posposicion}`;
            details.appendChild(reason);
        }

        const changes = document.getElementById('detalleActividadCambios');
        changes.replaceChildren();
        if (!activity.cambios.length) {
            changes.textContent = 'No se han registrado cambios de fecha o instructor.';
        } else {
            activity.cambios.forEach(change => {
                const item = document.createElement('div');
                item.className = 'apr-change-item mb-3';
                const action = document.createElement('div');
                action.className = 'fw-semibold text-capitalize';
                action.textContent = change.accion;
                item.appendChild(action);
                if (change.fecha_anterior || change.fecha_nueva) {
                    const dates = document.createElement('div');
                    dates.textContent = `Fecha: ${change.fecha_anterior || '—'} → ${change.fecha_nueva || '—'}`;
                    item.appendChild(dates);
                }
                if (change.instructor_anterior || change.instructor_nuevo) {
                    const instructors = document.createElement('div');
                    instructors.textContent = `Instructor: ${change.instructor_anterior || '—'} → ${change.instructor_nuevo || '—'}`;
                    item.appendChild(instructors);
                }
                if (change.motivo) {
                    const reason = document.createElement('div');
                    reason.textContent = `Motivo: ${change.motivo}`;
                    item.appendChild(reason);
                }
                changes.appendChild(item);
            });
        }
        const modalElement = document.getElementById('detalleActividad');
        if (focusHistory) {
            modalElement.addEventListener('shown.bs.modal', () => {
                document.getElementById('detalleActividadHistorialTitulo').scrollIntoView({behavior: 'smooth', block: 'start'});
            }, {once: true});
        }
        bootstrap.Modal.getOrCreateInstance(modalElement).show();
    };

    const openDayActivities = (dateKey, dayActivities) => {
        const list = document.getElementById('listaActividadesDelDia');
        const title = document.getElementById('actividadesDelDiaTitulo');
        const formattedDate = new Date(`${dateKey}T00:00:00`).toLocaleDateString('es-CO', {
            day: '2-digit',
            month: 'long',
            year: 'numeric',
        });
        title.textContent = `Actividades · ${formattedDate}`;
        list.replaceChildren();
        dayActivities.forEach(activity => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'apr-day-activity';
            const heading = document.createElement('div');
            heading.className = 'fw-bold';
            heading.textContent = activity.titulo;
            const subtitle = document.createElement('div');
            subtitle.className = 'small text-muted mt-1';
            subtitle.textContent = `${activity.hora_inicio} – ${activity.hora_fin} · ${activity.estado}`;
            item.append(heading, subtitle);
            item.addEventListener('click', () => {
                const dayModal = document.getElementById('actividadesDelDia');
                dayModal.addEventListener('hidden.bs.modal', () => openDetails(activity), {once: true});
                bootstrap.Modal.getOrCreateInstance(dayModal).hide();
            });
            list.appendChild(item);
        });
        bootstrap.Modal.getOrCreateInstance(document.getElementById('actividadesDelDia')).show();
    };

    const renderCalendar = () => {
        calendar.replaceChildren();
        monthTitle.textContent = new Intl.DateTimeFormat('es-CO', {month:'long', year:'numeric'}).format(displayedMonth);
        const visibleActivities = actividades.filter(matchesFilters);
        ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'].forEach(day => {
            const header = document.createElement('div');
            header.className = 'apr-calendar-weekday';
            header.textContent = day;
            calendar.appendChild(header);
        });

        const first = new Date(displayedMonth.getFullYear(), displayedMonth.getMonth(), 1);
        const mondayOffset = (first.getDay() + 6) % 7;
        const gridStart = new Date(first);
        gridStart.setDate(first.getDate() - mondayOffset);
        for (let index = 0; index < 42; index++) {
            const day = new Date(gridStart);
            day.setDate(gridStart.getDate() + index);
            const dateKey = `${day.getFullYear()}-${String(day.getMonth() + 1).padStart(2, '0')}-${String(day.getDate()).padStart(2, '0')}`;
            const cell = document.createElement('div');
            cell.className = 'apr-calendar-day';
            cell.dataset.calendarioFecha = dateKey;
            if (day.getMonth() !== displayedMonth.getMonth()) cell.classList.add('is-outside');
            const isToday = dateKey === `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
            if (isToday) cell.classList.add('is-today');
            const number = document.createElement('div');
            number.className = 'apr-calendar-number';
            number.textContent = day.getDate();
            cell.appendChild(number);
            if (isToday) {
                const todayLabel = document.createElement('span');
                todayLabel.className = 'apr-calendar-today-label';
                todayLabel.textContent = 'Hoy';
                cell.appendChild(todayLabel);
            }

            const dayActivities = (eventsByDate.get(dateKey) || []).filter(matchesFilters);
            if (dayActivities.length) {
                const colorPriority = {rojo: 0, amarillo: 1, verde: 2, neutro: 3};
                const representativeActivity = [...dayActivities].sort(
                    (firstActivity, secondActivity) =>
                        (colorPriority[firstActivity.semaforo] ?? 3) - (colorPriority[secondActivity.semaforo] ?? 3)
                )[0];
                const event = document.createElement('button');
                event.type = 'button';
                event.className = `apr-calendar-event semaforo-${representativeActivity.semaforo || 'neutro'}`;
                const dot = document.createElement('span');
                dot.className = 'apr-calendar-event-dot';
                dot.setAttribute('aria-hidden', 'true');
                const label = document.createElement('span');
                label.className = 'apr-calendar-event-label';
                label.textContent = `${dayActivities.length} ${dayActivities.length === 1 ? 'actividad' : 'actividades'}`;
                event.append(dot, label);
                event.title = dayActivities.map(activity => `${activity.titulo} · ${activity.hora_inicio}–${activity.hora_fin}`).join('\n');
                event.setAttribute('aria-label', `${label.textContent}. ${event.title}`);
                event.addEventListener('click', () => {
                    if (dayActivities.length === 1) {
                        openDetails(dayActivities[0]);
                        return;
                    }
                    openDayActivities(dateKey, dayActivities);
                });
                cell.appendChild(event);
            }
            calendar.appendChild(cell);
        }
    };

    document.getElementById('mesAnterior').addEventListener('click', () => {
        displayedMonth = new Date(displayedMonth.getFullYear(), displayedMonth.getMonth() - 1, 1);
        renderCalendar();
    });
    document.getElementById('mesSiguiente').addEventListener('click', () => {
        displayedMonth = new Date(displayedMonth.getFullYear(), displayedMonth.getMonth() + 1, 1);
        renderCalendar();
    });
    document.getElementById('mesActual').addEventListener('click', () => {
        displayedMonth = new Date(today.getFullYear(), today.getMonth(), 1);
        renderCalendar();
    });
    filterForm.addEventListener('submit', event => {
        event.preventDefault();
        applyFilters();
    });
    filterForm.addEventListener('reset', () => {
        window.setTimeout(() => {
            activeFilters = {date: '', text: '', state: ''};
            displayedMonth = new Date(today.getFullYear(), today.getMonth(), 1);
            activityRows.forEach(row => { row.hidden = false; });
            if (noResults) noResults.hidden = true;
            renderCalendar();
        });
    });
    document.querySelectorAll('.ver-detalle').forEach(button => {
        button.addEventListener('click', () => openDetails(activityById.get(button.dataset.actividadId)));
    });
    document.querySelectorAll('.ver-historial').forEach(button => {
        button.addEventListener('click', () => openDetails(activityById.get(button.dataset.actividadId), true));
    });
    renderCalendar();
})();
</script>
@endpush
