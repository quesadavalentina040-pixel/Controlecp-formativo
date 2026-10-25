@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    /* ── Estilos del Semáforo ── */
    .semaforo-indicator {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 8px rgba(0,0,0,0.15);
        flex-shrink: 0;
    }
    .semaforo-verde {
        background-color: #39A900;
        box-shadow: 0 0 10px rgba(57, 169, 0, 0.6);
        animation: pulse-green 2s infinite;
    }
    .semaforo-amarillo {
        background-color: #f57f17;
        box-shadow: 0 0 10px rgba(245, 127, 23, 0.6);
        animation: pulse-yellow 2s infinite;
    }
    .semaforo-rojo {
        background-color: #d32f2f;
        box-shadow: 0 0 10px rgba(211, 47, 47, 0.6);
        animation: pulse-red 2s infinite;
    }
    .semaforo-neutro {
        background-color: #94a3b8;
        box-shadow: none;
    }

    @keyframes pulse-green {
        0% { box-shadow: 0 0 0 0 rgba(57, 169, 0, 0.7); }
        70% { box-shadow: 0 0 0 8px rgba(57, 169, 0, 0); }
        100% { box-shadow: 0 0 0 0 rgba(57, 169, 0, 0); }
    }
    @keyframes pulse-yellow {
        0% { box-shadow: 0 0 0 0 rgba(245, 127, 23, 0.7); }
        70% { box-shadow: 0 0 0 8px rgba(245, 127, 23, 0); }
        100% { box-shadow: 0 0 0 0 rgba(245, 127, 23, 0); }
    }
    @keyframes pulse-red {
        0% { box-shadow: 0 0 0 0 rgba(211, 47, 47, 0.7); }
        70% { box-shadow: 0 0 0 8px rgba(211, 47, 47, 0); }
        100% { box-shadow: 0 0 0 0 rgba(211, 47, 47, 0); }
    }

    .card-semaforo-box {
        border-radius: 18px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: none;
        cursor: pointer;
    }
    .card-semaforo-box:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    /* ── Estilos del Calendario Mensual ── */
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
    .calendar-day-cell:hover {
        background-color: #f8fafc;
    }
    .calendar-day-cell.other-month {
        background-color: #f1f5f9;
        opacity: 0.55;
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

    .badge-ficha {
        font-size: 0.68rem;
        background-color: rgba(0, 26, 41, 0.08);
        color: #001A29;
        font-family: monospace;
        padding: 1px 4px;
        border-radius: 4px;
        display: inline-block;
        width: fit-content;
    }

    .timeline-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: #39A900;
        border: 2px solid #ffffff;
        box-shadow: 0 0 0 2px #39A900;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">

    <!-- ── Breadcrumb & Cabecera Principal ── -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.administrador.inicio') }}" class="text-decoration-none" style="color: #39A900;">
                            <i class="fas fa-house me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page" style="color: #001A29; font-weight: 600;">
                        Gestión de Cronograma (HU-006)
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: #001A29; font-family: 'Outfit', sans-serif;">
                <i class="fas fa-calendar-days me-2" style="color: #39A900;"></i>Cronograma de Actividades
            </h2>
            <p class="text-muted small mb-0">Programación, control temporal, semaforización de cercanía y asignación de recursos</p>
        </div>

        <div class="d-flex gap-2">
            <button class="btn text-white rounded-pill px-4 shadow-sm fw-semibold" style="background-color: #39A900; border-color: #39A900;" data-bs-toggle="modal" data-bs-target="#modalProgramarActividad">
                <i class="fas fa-plus-circle me-2"></i>Programar Actividad
            </button>
            <a href="{{ route('controlecp.administrador.inicio') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="fas fa-arrow-left me-1"></i>Inicio
            </a>
        </div>
    </div>

    <!-- ── Panel de Semáforos (Criterio 6) ── -->
    <div class="row g-3 mb-4">
        <!-- Rojo: Crítico / Muy próximo (<= 2 días o vencida pendiente) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-semaforo-box shadow-sm" style="background: linear-gradient(135deg, #FEF2F2 0%, #FEE2E2 100%); border-left: 5px solid #d32f2f;" onclick="filtrarPorSemaforoCard('rojo')">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <span class="semaforo-indicator semaforo-rojo" style="width: 22px; height: 22px;"></span>
                        <div>
                            <span class="d-block fw-bold text-uppercase small text-danger" style="letter-spacing: 0.5px; font-size: 0.75rem;">Crítico / Muy Próximo</span>
                            <span class="h3 fw-bold mb-0 text-danger">{{ $stats['rojo'] }}</span>
                            <span class="small text-muted d-block" style="font-size: 0.72rem;">Hoy o en ≤ 2 días</span>
                        </div>
                    </div>
                    <i class="fas fa-triangle-exclamation fa-2x text-danger opacity-50"></i>
                </div>
            </div>
        </div>

        <!-- Amarillo: Aproximadamente una semana (3 a 7 días) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-semaforo-box shadow-sm" style="background: linear-gradient(135deg, #FFFBEB 0%, #FEF3C7 100%); border-left: 5px solid #f57f17;" onclick="filtrarPorSemaforoCard('amarillo')">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <span class="semaforo-indicator semaforo-amarillo" style="width: 22px; height: 22px;"></span>
                        <div>
                            <span class="d-block fw-bold text-uppercase small" style="color: #B45309; letter-spacing: 0.5px; font-size: 0.75rem;">Próximo (~1 Semana)</span>
                            <span class="h3 fw-bold mb-0" style="color: #F57F17;">{{ $stats['amarillo'] }}</span>
                            <span class="small text-muted d-block" style="font-size: 0.72rem;">Entre 3 y 7 días</span>
                        </div>
                    </div>
                    <i class="fas fa-clock fa-2x text-warning opacity-50"></i>
                </div>
            </div>
        </div>

        <!-- Verde: Dos semanas o más (>= 8 días) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-semaforo-box shadow-sm" style="background: linear-gradient(135deg, #F0FDF4 0%, #DCFCE7 100%); border-left: 5px solid #39A900;" onclick="filtrarPorSemaforoCard('verde')">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <span class="semaforo-indicator semaforo-verde" style="width: 22px; height: 22px;"></span>
                        <div>
                            <span class="d-block fw-bold text-uppercase small" style="color: #166534; letter-spacing: 0.5px; font-size: 0.75rem;">En Tiempo (≥ 2 Semanas)</span>
                            <span class="h3 fw-bold mb-0" style="color: #39A900;">{{ $stats['verde'] }}</span>
                            <span class="small text-muted d-block" style="font-size: 0.72rem;">≥ 8 días de anticipación</span>
                        </div>
                    </div>
                    <i class="fas fa-circle-check fa-2x text-success opacity-50"></i>
                </div>
            </div>
        </div>

        <!-- Pospuestas y Finalizadas (Sin Semáforo - Criterio 6) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-semaforo-box shadow-sm" style="background: linear-gradient(135deg, #F8FAFC 0%, #E2E8F0 100%); border-left: 5px solid #64748b;" onclick="filtrarPorSemaforoCard('sin_color')">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <span class="semaforo-indicator semaforo-neutro" style="width: 22px; height: 22px;"></span>
                        <div>
                            <span class="d-block fw-bold text-uppercase small text-secondary" style="letter-spacing: 0.5px; font-size: 0.75rem;">Sin Color (Otras)</span>
                            <div class="d-flex align-items-baseline gap-2">
                                <span class="h4 fw-bold mb-0 text-dark">{{ $stats['pospuestas'] }}</span>
                                <span class="small text-muted">posp.</span>
                                <span class="text-muted">/</span>
                                <span class="h4 fw-bold mb-0 text-dark">{{ $stats['finalizadas'] }}</span>
                                <span class="small text-muted">fin.</span>
                            </div>
                            <span class="small text-muted d-block" style="font-size: 0.72rem;">Pospuestas y finalizadas</span>
                        </div>
                    </div>
                    <i class="fas fa-calendar-check fa-2x text-secondary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Barra de Pestañas y Acciones de Vista (Criterio 5) ── -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <ul class="nav nav-pills p-1 bg-light rounded-pill border" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill px-4 fw-bold" id="tab-calendario" data-bs-toggle="pill" data-bs-target="#content-calendario" type="button" role="tab">
                    <i class="fas fa-calendar-alt me-2"></i>Vista Calendario
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4 fw-bold" id="tab-lista" data-bs-toggle="pill" data-bs-target="#content-lista" type="button" role="tab">
                    <i class="fas fa-list-ul me-2"></i>Vista Lista & Semáforo
                    <span class="badge bg-secondary rounded-pill ms-1">{{ $todasSesiones->count() }}</span>
                </button>
            </li>
        </ul>

        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" type="button" data-bs-toggle="collapse" data-bs-target="#panelFiltrosAvanzados">
                <i class="fas fa-filter me-1"></i>Filtros Avanzados
            </button>
            <a href="{{ route('controlecp.administrador.cronograma') }}" class="btn btn-sm btn-link text-muted text-decoration-none">
                <i class="fas fa-rotate-left me-1"></i>Limpiar
            </a>
        </div>
    </div>

    <!-- ── Panel Colapsable de Filtros Avanzados (Criterio 7) ── -->
    <div class="collapse show mb-4" id="panelFiltrosAvanzados">
        <div class="card border-0 shadow-sm" style="border-radius: 18px;">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('controlecp.administrador.cronograma') }}" id="formFiltrosCronograma">
                    <div class="row g-2 align-items-end">
                        <!-- Filtro Buscar Texto -->
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-semibold text-muted mb-1">Buscar</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3">
                                    <i class="fas fa-magnifying-glass text-muted"></i>
                                </span>
                                <input type="text" name="search" id="filtroSearch" value="{{ request('search') }}" class="form-control border-start-0 rounded-end-pill" placeholder="Actividad, ficha, lugar...">
                            </div>
                        </div>

                        <!-- Filtro Ficha -->
                        <div class="col-12 col-md-2">
                            <label class="form-label small fw-semibold text-muted mb-1">Ficha</label>
                            <select name="ficha_id" id="filtroFicha" class="form-select form-select-sm rounded-pill">
                                <option value="">Todas las Fichas</option>
                                @foreach($fichas as $f)
                                    <option value="{{ $f->id }}" {{ request('ficha_id') == $f->id ? 'selected' : '' }}>
                                        {{ $f->code }} - {{ Str::limit($f->program?->name ?? 'S/P', 18) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filtro Instructor -->
                        <div class="col-12 col-md-2">
                            <label class="form-label small fw-semibold text-muted mb-1">Instructor</label>
                            <select name="instructor_id" id="filtroInstructor" class="form-select form-select-sm rounded-pill">
                                <option value="">Todos los Instructores</option>
                                @foreach($instructores as $inst)
                                    @php $p = $inst->person; @endphp
                                    <option value="{{ $inst->id }}" {{ request('instructor_id') == $inst->id ? 'selected' : '' }}>
                                        {{ $p ? "{$p->first_name} {$p->first_last_name}" : $inst->nickname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filtro Momento -->
                        <div class="col-12 col-md-2">
                            <label class="form-label small fw-semibold text-muted mb-1">Momento</label>
                            <select name="momento_id" id="filtroMomento" class="form-select form-select-sm rounded-pill">
                                <option value="">Todos los Momentos</option>
                                @foreach($momentos as $m)
                                    <option value="{{ $m->id }}" {{ request('momento_id') == $m->id ? 'selected' : '' }}>
                                        {{ $m->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filtro Estado -->
                        <div class="col-6 col-md-1">
                            <label class="form-label small fw-semibold text-muted mb-1">Estado</label>
                            <select name="estado" id="filtroEstado" class="form-select form-select-sm rounded-pill">
                                <option value="">Todos</option>
                                <option value="Pendiente" {{ request('estado') === 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="Pospuesta" {{ request('estado') === 'Pospuesta' ? 'selected' : '' }}>Pospuesta</option>
                                <option value="Finalizada" {{ request('estado') === 'Finalizada' ? 'selected' : '' }}>Finalizada</option>
                            </select>
                        </div>

                        <!-- Filtro Semáforo -->
                        <div class="col-6 col-md-2">
                            <label class="form-label small fw-semibold text-muted mb-1">Semáforo</label>
                            <select name="semaforo" id="filtroSemaforo" class="form-select form-select-sm rounded-pill">
                                <option value="todos">Todos</option>
                                <option value="rojo" {{ request('semaforo') === 'rojo' ? 'selected' : '' }}>🔴 Rojo (≤ 2 días)</option>
                                <option value="amarillo" {{ request('semaforo') === 'amarillo' ? 'selected' : '' }}>🟡 Amarillo (~1 sem)</option>
                                <option value="verde" {{ request('semaforo') === 'verde' ? 'selected' : '' }}>🟢 Verde (≥ 2 sem)</option>
                                <option value="sin_color" {{ request('semaforo') === 'sin_color' ? 'selected' : '' }}>⚪ Sin color (Posp/Fin)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Fila 2 de Filtros: Rango de Fechas y Botones -->
                    <div class="row g-2 align-items-end mt-2 pt-2 border-top">
                        <div class="col-6 col-md-2">
                            <label class="form-label small fw-semibold text-muted mb-1">Fecha Desde</label>
                            <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="form-control form-control-sm rounded-pill">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small fw-semibold text-muted mb-1">Fecha Hasta</label>
                            <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="form-control form-control-sm rounded-pill">
                        </div>
                        <div class="col-12 col-md-8 text-md-end">
                            <button type="submit" class="btn btn-sm text-white rounded-pill px-4" style="background-color: #001A29;">
                                <i class="fas fa-filter me-1"></i>Aplicar Filtros
                            </button>
                            <a href="{{ route('controlecp.administrador.cronograma') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 ms-1">
                                Restablecer
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ── Contenedor de Pestañas ── -->
    <div class="tab-content" id="pills-tabContent">

        <!-- ════════════════════════════════════════════════
             PESTAÑA 1: VISTA CALENDARIO MENSUAL (Criterio 1, 5, 6)
        ════════════════════════════════════════════════ -->
        <div class="tab-pane fade show active" id="content-calendario" role="tabpanel">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-header bg-white p-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <button class="btn btn-sm btn-outline-secondary rounded-circle" id="prevMonthBtn" title="Mes Anterior" style="width: 34px; height: 34px;">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <h4 class="h5 fw-bold mb-0 text-dark" id="calendarMonthTitle" style="font-family: 'Outfit', sans-serif; min-width: 170px; text-align: center;">
                            --
                        </h4>
                        <button class="btn btn-sm btn-outline-secondary rounded-circle" id="nextMonthBtn" title="Mes Siguiente" style="width: 34px; height: 34px;">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-outline-success rounded-pill px-3" id="todayBtn">
                            <i class="fas fa-calendar-day me-1"></i>Hoy
                        </button>
                        <div class="d-none d-md-flex align-items-center gap-2 ms-3 ps-3 border-start text-muted small">
                            <span><span class="semaforo-indicator semaforo-rojo me-1" style="width: 10px; height: 10px;"></span>≤ 2 días</span>
                            <span><span class="semaforo-indicator semaforo-amarillo me-1" style="width: 10px; height: 10px;"></span>~1 sem</span>
                            <span><span class="semaforo-indicator semaforo-verde me-1" style="width: 10px; height: 10px;"></span>≥ 2 sem</span>
                            <span><span class="semaforo-indicator semaforo-neutro me-1" style="width: 10px; height: 10px;"></span>Sin semáforo</span>
                        </div>
                    </div>
                </div>

                <div class="card-body p-3">
                    <div class="calendar-grid" id="calendarGrid">
                        <!-- Cabecera de Días de la Semana -->
                        <div class="calendar-day-header">Lun</div>
                        <div class="calendar-day-header">Mar</div>
                        <div class="calendar-day-header">Mié</div>
                        <div class="calendar-day-header">Jue</div>
                        <div class="calendar-day-header">Vie</div>
                        <div class="calendar-day-header">Sáb</div>
                        <div class="calendar-day-header">Dom</div>
                        <!-- Celdas renderizadas dinámicamente con JavaScript -->
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════
             PESTAÑA 2: VISTA LISTA Y TABLA DE SEMÁFOROS (Criterio 5, 6, 7)
        ════════════════════════════════════════════════ -->
        <div class="tab-pane fade" id="content-lista" role="tabpanel">
            <div class="card border-0 shadow-sm" style="border-radius: 18px; overflow: hidden;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tablaCronograma">
                            <thead style="background-color: #001A29; color: #ffffff;" class="small fw-bold">
                                <tr>
                                    <th class="ps-4 py-3 text-center" style="width: 90px;">Semáforo</th>
                                    <th class="py-3">Fecha y Horario</th>
                                    <th class="py-3">Ficha / Programa</th>
                                    <th class="py-3">Actividad & Momento</th>
                                    <th class="py-3">Instructor</th>
                                    <th class="py-3">Lugar</th>
                                    <th class="py-3 text-center">Estado</th>
                                    <th class="text-end pe-4 py-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @forelse($todasSesiones as $sesion)
                                    @php
                                        $sem = $sesion->semaforo;
                                        $pInst = $sesion->instructor?->person;
                                        $nomInst = $pInst ? "{$pInst->first_name} {$pInst->first_last_name}" : ($sesion->instructor?->nickname ?? 'Sin asignar');
                                        $act = $sesion->actividad;
                                        $numAct = $act ? ($act->numero ? "Actividad {$act->numero}" : "Actividad {$act->id}") : 'Actividad';
                                        $nomMom = $act && $act->momento ? $act->momento->nombre : 'Sin Momento';
                                    @endphp
                                    <tr>
                                        <!-- Semáforo (Criterio 6: Solo a pendientes) -->
                                        <td class="ps-4 text-center">
                                            @if($sem === 'rojo')
                                                <span class="semaforo-indicator semaforo-rojo" title="Semáforo Rojo: Hoy o en menos de 2 días (Crítico)"></span>
                                                <div class="small text-danger fw-bold" style="font-size: 0.68rem;">
                                                    {{ $sesion->dias_restantes < 0 ? abs($sesion->dias_restantes).'d atrasada' : ($sesion->dias_restantes == 0 ? '¡Hoy!' : $sesion->dias_restantes.'d') }}
                                                </div>
                                            @elseif($sem === 'amarillo')
                                                <span class="semaforo-indicator semaforo-amarillo" title="Semáforo Amarillo: Aproximadamente 1 semana (3 a 7 días)"></span>
                                                <div class="small text-warning-emphasis fw-bold" style="font-size: 0.68rem;">{{ $sesion->dias_restantes }}d</div>
                                            @elseif($sem === 'verde')
                                                <span class="semaforo-indicator semaforo-verde" title="Semáforo Verde: En tiempo (2 semanas o más)"></span>
                                                <div class="small text-success fw-bold" style="font-size: 0.68rem;">{{ $sesion->dias_restantes }}d</div>
                                            @else
                                                <span class="badge bg-light text-secondary border small" style="font-size: 0.68rem;">Sin semáforo</span>
                                            @endif
                                        </td>

                                        <!-- Fecha y Horario -->
                                        <td>
                                            <div class="fw-bold text-dark">
                                                <i class="far fa-calendar text-muted me-1"></i>
                                                {{ $sesion->fecha ? \Carbon\Carbon::parse($sesion->fecha)->translatedFormat('d M Y') : '--' }}
                                            </div>
                                            <div class="small text-muted">
                                                <i class="far fa-clock text-muted me-1"></i>
                                                {{ $sesion->horario_formateado }}
                                            </div>
                                            @if($sesion->fecha_original)
                                                <div class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size: 0.65rem;" title="Fecha antes de posponer">
                                                    Orig: {{ \Carbon\Carbon::parse($sesion->fecha_original)->format('d/m/Y') }}
                                                </div>
                                            @endif
                                        </td>

                                        <!-- Ficha / Programa -->
                                        <td>
                                            <span class="badge bg-dark-subtle text-dark border font-monospace">
                                                Ficha {{ $sesion->ficha?->code ?? '--' }}
                                            </span>
                                            <div class="small text-muted" style="max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $sesion->ficha?->program?->name }}">
                                                {{ $sesion->ficha?->program?->name ?? 'Programa no registrado' }}
                                            </div>
                                        </td>

                                        <!-- Momento & Actividad (Criterio 1: Actividad X – Momento Y) -->
                                        <td>
                                            <div class="fw-bold text-dark">
                                                <span class="text-primary">{{ $numAct }}</span> – {{ $nomMom }}
                                            </div>
                                            <div class="small text-muted" style="max-width: 240px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $act?->nombre }}">
                                                {{ $act?->nombre ?? 'Actividad sin nombre' }}
                                            </div>
                                            @if($sesion->recursos->count() > 0)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.68rem;">
                                                    <i class="fas fa-boxes-stacked me-1"></i>{{ $sesion->recursos->count() }} recurso(s)
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Instructor -->
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center fw-bold small" style="width: 32px; height: 32px;">
                                                    {{ strtoupper(substr($nomInst, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div class="small fw-bold text-dark">{{ $nomInst }}</div>
                                                    <div class="text-muted" style="font-size: 0.70rem;">Instructor Responsable</div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Lugar -->
                                        <td>
                                            <span class="small text-muted">
                                                <i class="fas fa-location-dot text-danger me-1"></i>{{ $sesion->lugar }}
                                            </span>
                                        </td>

                                        <!-- Estado -->
                                        <td class="text-center">
                                            @if($sesion->estado === 'Pendiente')
                                                <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                                    Pendiente
                                                </span>
                                            @elseif($sesion->estado === 'Pospuesta')
                                                <span class="badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle px-2 py-1" title="{{ $sesion->motivo_posposicion }}">
                                                    <i class="fas fa-clock-rotate-left me-1"></i>Pospuesta
                                                </span>
                                            @elseif($sesion->estado === 'Finalizada')
                                                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                    <i class="fas fa-check-circle me-1"></i>Finalizada
                                                </span>
                                            @else
                                                <span class="badge rounded-pill bg-secondary px-2 py-1">{{ $sesion->estado }}</span>
                                            @endif
                                        </td>

                                        <!-- Acciones (Criterios 8, 9, 10, 11) -->
                                        <td class="text-end pe-4">
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-secondary rounded-start-pill" title="Ver Detalle y Auditoría" onclick="verDetalleSesion({{ $sesion->id_cronograma }})">
                                                    <i class="fas fa-eye text-primary"></i>
                                                </button>
                                                <button class="btn btn-outline-secondary" title="Editar Programación" onclick="abrirModalEditar({{ $sesion->id_cronograma }})">
                                                    <i class="fas fa-pen text-dark"></i>
                                                </button>
                                                <button class="btn btn-outline-secondary" title="Reasignar Instructor" onclick="abrirModalReasignar({{ $sesion->id_cronograma }})">
                                                    <i class="fas fa-user-pen text-success"></i>
                                                </button>
                                                <button class="btn btn-outline-secondary" title="Posponer Actividad" onclick="abrirModalPosponer({{ $sesion->id_cronograma }})">
                                                    <i class="fas fa-calendar-plus text-warning"></i>
                                                </button>
                                                <button class="btn btn-outline-danger rounded-end-pill" title="Eliminar Programación" onclick="confirmarEliminarSesion({{ $sesion->id_cronograma }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="fas fa-calendar-xmark fa-3x mb-3 text-secondary opacity-50"></i>
                                            <h5 class="fw-bold">No se encontraron actividades programadas</h5>
                                            <p class="small mb-3">No hay sesiones que coincidan con los filtros aplicados.</p>
                                            <button class="btn btn-sm btn-outline-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalProgramarActividad">
                                                <i class="fas fa-plus me-1"></i>Programar Actividad Ahora
                                            </button>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- ════════════════════════════════════════════════
     MODAL 1: PROGRAMAR ACTIVIDAD (Criterios 1, 2, 3, 4, 12)
════════════════════════════════════════════════ -->
<div class="modal fade" id="modalProgramarActividad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div>
                    <h5 class="modal-title fw-bold" style="color: #001A29;">
                        <i class="fas fa-calendar-plus me-2" style="color: #39A900;"></i>Programar Actividad en Cronograma
                    </h5>
                    <p class="text-muted small mb-0">Asigna ficha, horario, instructor y asocia recursos del inventario (HU-006)</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formProgramarActividad">
                    @csrf
                    <div class="row g-3">
                        <!-- Actividad Pedagógica (Criterio 1 & 2) -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                Actividad Pedagógica <span class="text-danger">*</span>
                            </label>
                            <select name="id_actividad" id="progActividad" class="form-select rounded-pill" required>
                                <option value="">— Seleccionar Actividad —</option>
                                @foreach($momentos as $mom)
                                    <optgroup label="{{ $mom->nombre }}">
                                        @foreach($mom->actividades as $actItem)
                                            <option value="{{ $actItem->id }}" data-duracion="{{ $actItem->duracion_min ?? 120 }}">
                                                {{ $actItem->numero ? "Actividad {$actItem->numero}: " : '' }}{{ $actItem->nombre }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                        <!-- Ficha de Formación (Criterio 1 & 2) -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                Ficha de Formación SENA <span class="text-danger">*</span>
                            </label>
                            <select name="id_ficha" id="progFicha" class="form-select rounded-pill" required>
                                <option value="">— Seleccionar Ficha —</option>
                                @foreach($fichas as $f)
                                    <option value="{{ $f->id }}">
                                        Ficha {{ $f->code }} — {{ $f->program?->name ?? 'Sin programa' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Fecha Programada -->
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-dark">
                                Fecha de Ejecución <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="fecha" id="progFecha" class="form-control rounded-pill" min="{{ date('Y-m-d') }}" required onchange="verificarDisponibilidadEnVivo()">
                        </div>

                        <!-- Hora Inicio -->
                        <div class="col-6 col-md-4">
                            <label class="form-label small fw-bold text-dark">
                                Hora Inicio <span class="text-danger">*</span>
                            </label>
                            <input type="time" name="hora_inicio" id="progHoraInicio" class="form-control rounded-pill" value="08:00" required onchange="verificarDisponibilidadEnVivo()">
                        </div>

                        <!-- Hora Fin -->
                        <div class="col-6 col-md-4">
                            <label class="form-label small fw-bold text-dark">
                                Hora Fin <span class="text-danger">*</span>
                            </label>
                            <input type="time" name="hora_fin" id="progHoraFin" class="form-control rounded-pill" value="10:00" required onchange="verificarDisponibilidadEnVivo()">
                        </div>

                        <!-- Instructor Responsable (Criterio 3) -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                Instructor Responsable <span class="text-danger">*</span>
                            </label>
                            <select name="id_instructor" id="progInstructor" class="form-select rounded-pill" required onchange="verificarDisponibilidadEnVivo()">
                                <option value="">— Seleccionar Instructor —</option>
                                @foreach($instructores as $inst)
                                    @php $p = $inst->person; @endphp
                                    <option value="{{ $inst->id }}">
                                        {{ $p ? "{$p->first_name} {$p->first_last_name}" : $inst->nickname }}
                                    </option>
                                @endforeach
                            </select>
                            <!-- Feedback de disponibilidad del instructor -->
                            <div id="progInstructorFeedback" class="small mt-1 d-none"></div>
                        </div>

                        <!-- Lugar o Ambiente -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                Lugar / Ambiente <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="lugar" id="progLugar" class="form-control rounded-pill" placeholder="Ej: Aula 102, Auditorio Central, Cancha ECP" required>
                        </div>

                        <!-- ── Sección Asociar Recursos (Criterio 4) ── -->
                        <div class="col-12 mt-3 pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <label class="form-label small fw-bold text-dark mb-0">
                                        <i class="fas fa-boxes-stacked text-success me-1"></i>Asociar Recursos de Inventario (Opcional)
                                    </label>
                                    <div class="text-muted" style="font-size: 0.72rem;">Verifica disponibilidad de inventario para la sesión</div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="agregarFilaRecurso()">
                                    <i class="fas fa-plus me-1"></i>Agregar Recurso
                                </button>
                            </div>

                            <div id="contenedorRecursos" class="d-flex flex-column gap-2">
                                <!-- Filas de recursos añadidas dinámicamente -->
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn text-white rounded-pill px-4 fw-semibold shadow-sm" style="background-color: #39A900;" id="btnGuardarProgramacion" onclick="ejecutarGuardarProgramacion()">
                    <i class="fas fa-floppy-disk me-1"></i>Guardar Programación
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════
     MODAL 2: EDITAR PROGRAMACIÓN (Criterio 8)
════════════════════════════════════════════════ -->
<div class="modal fade" id="modalEditarProgramacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div>
                    <h5 class="modal-title fw-bold" style="color: #001A29;">
                        <i class="fas fa-pen-to-square me-2" style="color: #001A29;"></i>Editar Programación
                    </h5>
                    <p class="text-muted small mb-0">Modifica fecha, horario, lugar, ficha o recursos asociados (Criterio 8)</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formEditarProgramacion">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editCronogramaId" name="id_cronograma">

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">Actividad Pedagógica</label>
                            <select name="id_actividad" id="editActividad" class="form-select rounded-pill" required>
                                @foreach($momentos as $mom)
                                    <optgroup label="{{ $mom->nombre }}">
                                        @foreach($mom->actividades as $actItem)
                                            <option value="{{ $actItem->id }}">
                                                {{ $actItem->numero ? "Actividad {$actItem->numero}: " : '' }}{{ $actItem->nombre }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">Ficha de Formación</label>
                            <select name="id_ficha" id="editFicha" class="form-select rounded-pill" required>
                                @foreach($fichas as $f)
                                    <option value="{{ $f->id }}">
                                        Ficha {{ $f->code }} — {{ $f->program?->name ?? 'Sin programa' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-dark">Fecha de Ejecución</label>
                            <input type="date" name="fecha" id="editFecha" class="form-control rounded-pill" required>
                        </div>

                        <div class="col-6 col-md-4">
                            <label class="form-label small fw-bold text-dark">Hora Inicio</label>
                            <input type="time" name="hora_inicio" id="editHoraInicio" class="form-control rounded-pill" required>
                        </div>

                        <div class="col-6 col-md-4">
                            <label class="form-label small fw-bold text-dark">Hora Fin</label>
                            <input type="time" name="hora_fin" id="editHoraFin" class="form-control rounded-pill" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Lugar / Ambiente</label>
                            <input type="text" name="lugar" id="editLugar" class="form-control rounded-pill" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Motivo del Ajuste (Opcional)</label>
                            <input type="text" name="motivo_edicion" id="editMotivo" class="form-control rounded-pill" placeholder="Explica brevemente el motivo del cambio...">
                        </div>

                        <!-- Recursos en Edición -->
                        <div class="col-12 mt-3 pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label small fw-bold text-dark mb-0">
                                    <i class="fas fa-boxes-stacked text-success me-1"></i>Recursos de Inventario
                                </label>
                                <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="agregarFilaRecurso('contenedorRecursosEdit')">
                                    <i class="fas fa-plus me-1"></i>Agregar Recurso
                                </button>
                            </div>
                            <div id="contenedorRecursosEdit" class="d-flex flex-column gap-2"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn text-white rounded-pill px-4 fw-semibold" style="background-color: #001A29;" onclick="ejecutarActualizarProgramacion()">
                    <i class="fas fa-check me-1"></i>Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════
     MODAL 3: REASIGNAR INSTRUCTOR (Criterio 10)
════════════════════════════════════════════════ -->
<div class="modal fade" id="modalReasignarInstructor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div>
                    <h5 class="modal-title fw-bold" style="color: #001A29;">
                        <i class="fas fa-user-pen me-2" style="color: #39A900;"></i>Reasignar Instructor
                    </h5>
                    <p class="text-muted small mb-0">Valida disponibilidad del nuevo instructor y notifica a ambos (Criterio 10)</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formReasignarInstructor">
                    <input type="hidden" id="reasignarCronogramaId">

                    <!-- Tarjeta con resumen de la sesión actual -->
                    <div class="p-3 mb-3 bg-light rounded-4 border">
                        <div class="small text-muted fw-semibold">Actividad Seleccionada:</div>
                        <div class="fw-bold text-dark" id="reasignarActividadNombre">—</div>
                        <div class="small text-muted mt-1" id="reasignarHorarioTexto">—</div>
                        <div class="small text-danger fw-semibold mt-1">
                            Instructor actual: <span id="reasignarInstructorActual">—</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">
                            Nuevo Instructor Responsable <span class="text-danger">*</span>
                        </label>
                        <select name="nuevo_instructor_id" id="nuevoInstructorId" class="form-select rounded-pill" required>
                            <option value="">— Seleccionar Nuevo Instructor —</option>
                            @foreach($instructores as $inst)
                                @php $p = $inst->person; @endphp
                                <option value="{{ $inst->id }}">
                                    {{ $p ? "{$p->first_name} {$p->first_last_name}" : $inst->nickname }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">
                            Motivo de la Reasignación <span class="text-danger">*</span>
                        </label>
                        <textarea name="motivo_reasignacion" id="motivoReasignacion" class="form-control" rows="3" placeholder="Explica la causa del cambio de instructor (cruce pedagógico, permiso, etc.)..." required style="border-radius: 14px;"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn text-white rounded-pill px-4 fw-semibold" style="background-color: #39A900;" onclick="ejecutarReasignarInstructor()">
                    <i class="fas fa-arrows-rotate me-1"></i>Reasignar y Notificar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════
     MODAL 4: POSPONER ACTIVIDAD (Criterio 11)
════════════════════════════════════════════════ -->
<div class="modal fade" id="modalPosponerActividad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div>
                    <h5 class="modal-title fw-bold text-warning-emphasis">
                        <i class="fas fa-calendar-plus me-2 text-warning"></i>Posponer Actividad
                    </h5>
                    <p class="text-muted small mb-0">Conserva la fecha original, registra el motivo y la reubica (Criterio 11)</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formPosponerActividad">
                    <input type="hidden" id="posponerCronogramaId">

                    <!-- Resumen fecha actual -->
                    <div class="p-3 mb-3 bg-light rounded-4 border">
                        <div class="small text-muted fw-semibold">Sesión a Posponer:</div>
                        <div class="fw-bold text-dark" id="posponerActividadNombre">—</div>
                        <div class="small text-muted mt-1">
                            Fecha Programada Actual: <span class="fw-bold text-danger" id="posponerFechaActual">—</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">
                            Nueva Fecha de Ejecución <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="nueva_fecha" id="posponerNuevaFecha" class="form-control rounded-pill" min="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">Nueva Hora Inicio</label>
                            <input type="time" name="hora_inicio" id="posponerHoraInicio" class="form-control rounded-pill">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">Nueva Hora Fin</label>
                            <input type="time" name="hora_fin" id="posponerHoraFin" class="form-control rounded-pill">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">
                            Motivo de Posposición <span class="text-danger">*</span>
                        </label>
                        <textarea name="motivo" id="posponerMotivo" class="form-control" rows="3" placeholder="Explica obligatoriamente el motivo para posponer la sesión..." required style="border-radius: 14px;"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning text-dark fw-bold rounded-pill px-4" onclick="ejecutarPosponerActividad()">
                    <i class="fas fa-clock-rotate-left me-1"></i>Confirmar Posposición
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════
     MODAL 5: VER DETALLE Y AUDITORÍA (Criterios 1, 5, 6, 8, 10, 11)
════════════════════════════════════════════════ -->
<div class="modal fade" id="modalDetalleSesion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span id="detSemaforoDot" class="semaforo-indicator semaforo-verde"></span>
                        <span id="detEstadoBadge" class="badge rounded-pill bg-primary px-3">Pendiente</span>
                        <span id="detFichaBadge" class="badge bg-dark-subtle text-dark border font-monospace">Ficha 000000</span>
                    </div>
                    <h5 class="modal-title fw-bold text-dark mb-0" id="detTituloCalendario">
                        Actividad X – Momento Y
                    </h5>
                    <p class="text-muted small mb-0" id="detActividadNombre">—</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <!-- Datos Principales -->
                    <div class="col-12 col-md-6">
                        <div class="p-3 bg-light rounded-4 h-100">
                            <h6 class="fw-bold text-dark mb-2 small text-uppercase" style="letter-spacing: 0.5px;">
                                <i class="fas fa-circle-info text-success me-1"></i>Detalles de Sesión
                            </h6>
                            <div class="mb-2 small">
                                <span class="text-muted d-block">Fecha y Horario:</span>
                                <span class="fw-bold text-dark" id="detFechaHorario">—</span>
                            </div>
                            <div class="mb-2 small">
                                <span class="text-muted d-block">Lugar / Ambiente:</span>
                                <span class="fw-bold text-dark" id="detLugar">—</span>
                            </div>
                            <div class="mb-2 small">
                                <span class="text-muted d-block">Instructor a Cargo:</span>
                                <span class="fw-bold text-success" id="detInstructor">—</span>
                            </div>
                            <div class="small">
                                <span class="text-muted d-block">Programado por:</span>
                                <span class="fw-bold text-muted" id="detCreador">—</span>
                            </div>
                        </div>
                    </div>

                    <!-- Recursos Asociados (Criterio 4) -->
                    <div class="col-12 col-md-6">
                        <div class="p-3 bg-light rounded-4 h-100">
                            <h6 class="fw-bold text-dark mb-2 small text-uppercase" style="letter-spacing: 0.5px;">
                                <i class="fas fa-boxes-stacked text-primary me-1"></i>Recursos Asociados
                            </h6>
                            <div id="detListaRecursos" class="d-flex flex-column gap-2 small">
                                <span class="text-muted">Sin recursos asignados a esta sesión.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Trazabilidad / Historial de Cambios (Criterios 8, 10, 11) -->
                    <div class="col-12">
                        <div class="p-3 bg-white rounded-4 border">
                            <h6 class="fw-bold text-dark mb-3 small text-uppercase" style="letter-spacing: 0.5px;">
                                <i class="fas fa-timeline text-secondary me-1"></i>Historial de Auditoría & Trazabilidad
                            </h6>
                            <div id="detTimelineHistorial" class="d-flex flex-column gap-3 small">
                                <!-- Elementos del historial -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4 d-flex justify-content-between">
                <div>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 me-1" id="btnDetEliminar">
                        <i class="fas fa-trash me-1"></i>Eliminar
                    </button>
                    <button type="button" class="btn btn-outline-warning btn-sm rounded-pill px-3 me-1" id="btnDetPosponer">
                        <i class="fas fa-clock-rotate-left me-1"></i>Posponer
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3" id="btnDetReasignar">
                        <i class="fas fa-user-pen me-1"></i>Reasignar
                    </button>
                </div>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // ── Variables Globales y Catálogo de Inventario ──
    const CATALOGO_INVENTARIO = @json($recursosInventario);
    let mesActual = new Date().getMonth() + 1; // 1-12
    let anioActual = new Date().getFullYear();
    let eventosCalendario = [];

    document.addEventListener('DOMContentLoaded', function () {
        cargarEventosCalendario(anioActual, mesActual);

        // Controles de navegación de mes
        document.getElementById('prevMonthBtn').addEventListener('click', function () {
            mesActual--;
            if (mesActual < 1) {
                mesActual = 12;
                anioActual--;
            }
            cargarEventosCalendario(anioActual, mesActual);
        });

        document.getElementById('nextMonthBtn').addEventListener('click', function () {
            mesActual++;
            if (mesActual > 12) {
                mesActual = 1;
                anioActual++;
            }
            cargarEventosCalendario(anioActual, mesActual);
        });

        document.getElementById('todayBtn').addEventListener('click', function () {
            const hoy = new Date();
            anioActual = hoy.getFullYear();
            mesActual = hoy.getMonth() + 1;
            cargarEventosCalendario(anioActual, mesActual);
        });
    });

    // ── 1. Cargar Eventos y Renderizar Calendario (Criterio 1, 5, 6) ──
    function cargarEventosCalendario(year, month) {
        const url = `{{ route('controlecp.administrador.cronograma.eventos') }}?year=${year}&month=${month}`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    eventosCalendario = data.data;
                    renderizarCalendario(year, month, eventosCalendario);
                }
            })
            .catch(err => console.error('Error cargando eventos:', err));
    }

    function renderizarCalendario(year, month, eventos) {
        const grid = document.getElementById('calendarGrid');
        const titleEl = document.getElementById('calendarMonthTitle');

        const mesesNombres = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];
        titleEl.textContent = `${mesesNombres[month - 1]} ${year}`;

        // Limpiar días previos (mantener los 7 encabezados)
        while (grid.children.length > 7) {
            grid.removeChild(grid.lastChild);
        }

        const primerDia = new Date(year, month - 1, 1);
        let diaSemanaInicio = primerDia.getDay() - 1; // Lunes = 0
        if (diaSemanaInicio === -1) diaSemanaInicio = 6; // Domingo = 6

        const diasEnMes = new Date(year, month, 0).getDate();
        const diasEnMesAnterior = new Date(year, month - 1, 0).getDate();

        const hoy = new Date();
        const esMismoMesYHoy = hoy.getFullYear() === year && (hoy.getMonth() + 1) === month;
        const diaHoyNumero = hoy.getDate();

        // 1. Días del mes anterior
        for (let i = diaSemanaInicio - 1; i >= 0; i--) {
            const numDia = diasEnMesAnterior - i;
            const celda = document.createElement('div');
            celda.className = 'calendar-day-cell other-month';
            celda.innerHTML = `
                <div class="calendar-day-header-top">
                    <span class="calendar-day-number">${numDia}</span>
                </div>
            `;
            grid.appendChild(celda);
        }

        // 2. Días del mes actual
        for (let dia = 1; dia <= diasEnMes; dia++) {
            const fechaStr = `${year}-${String(month).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
            const esHoy = esMismoMesYHoy && dia === diaHoyNumero;

            const celda = document.createElement('div');
            celda.className = `calendar-day-cell ${esHoy ? 'today' : ''}`;

            // Filtrar eventos de este día
            const eventosDelDia = eventos.filter(e => e.fecha === fechaStr);

            let htmlEventos = '';
            eventosDelDia.forEach(ev => {
                // Criterio 6: Semáforo solo a pendientes
                let clasePill = 'event-pill-neutro';
                let dotColor = '#94a3b8';

                if (ev.estado === 'Pendiente') {
                    if (ev.semaforo === 'rojo') {
                        clasePill = 'event-pill-rojo';
                        dotColor = '#d32f2f';
                    } else if (ev.semaforo === 'amarillo') {
                        clasePill = 'event-pill-amarillo';
                        dotColor = '#f57f17';
                    } else if (ev.semaforo === 'verde') {
                        clasePill = 'event-pill-verde';
                        dotColor = '#39A900';
                    }
                }

                // Criterio 1: Nombre en el calendario "Actividad X – Momento Y"
                htmlEventos += `
                    <div class="calendar-event-pill ${clasePill}" onclick="verDetalleSesion(${ev.id})" title="${ev.titulo} | Ficha ${ev.ficha_codigo} | ${ev.horario}">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="badge-ficha">Ficha ${ev.ficha_codigo}</span>
                            <span class="semaforo-indicator" style="background-color: ${dotColor}; width: 8px; height: 8px;"></span>
                        </div>
                        <div class="fw-bold" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            ${ev.titulo}
                        </div>
                        <div class="small text-muted" style="font-size: 0.65rem;">
                            <i class="far fa-clock me-1"></i>${ev.horario}
                        </div>
                    </div>
                `;
            });

            celda.innerHTML = `
                <div class="calendar-day-header-top">
                    <span class="calendar-day-number">${dia}</span>
                    ${esHoy ? '<span class="badge bg-success small" style="font-size: 0.6rem;">Hoy</span>' : ''}
                </div>
                <div class="calendar-day-events">
                    ${htmlEventos}
                </div>
            `;
            grid.appendChild(celda);
        }

        // 3. Días del mes siguiente para completar la cuadrícula de 35 o 42 celdas
        const totalCeldasActuales = diaSemanaInicio + diasEnMes;
        const celdasRestantes = (totalCeldasActuales <= 35 ? 35 : 42) - totalCeldasActuales;
        for (let diaSig = 1; diaSig <= celdasRestantes; diaSig++) {
            const celda = document.createElement('div');
            celda.className = 'calendar-day-cell other-month';
            celda.innerHTML = `
                <div class="calendar-day-header-top">
                    <span class="calendar-day-number">${diaSig}</span>
                </div>
            `;
            grid.appendChild(celda);
        }
    }

    // ── 2. Filtro Rápido desde las Tarjetas de Semáforo ──
    function filtrarPorSemaforoCard(tipo) {
        const selectSem = document.getElementById('filtroSemaforo');
        if (selectSem) {
            selectSem.value = tipo;
            document.getElementById('formFiltrosCronograma').submit();
        }
    }

    // ── 3. Manejo de Filas Dinámicas de Recursos (Criterio 4) ──
    function agregarFilaRecurso(contenedorId = 'contenedorRecursos') {
        const contenedor = document.getElementById(contenedorId);
        const index = contenedor.children.length;

        let opcionesRecursos = '<option value="">— Seleccionar Recurso de Inventario —</option>';
        CATALOGO_INVENTARIO.forEach(rec => {
            opcionesRecursos += `<option value="${rec.id_inventario}" data-disponible="${rec.cantidad_disponible}">
                ${rec.nombre} (Disponibles: ${rec.cantidad_disponible})
            </option>`;
        });

        const fila = document.createElement('div');
        fila.className = 'row g-2 align-items-center bg-white p-2 rounded-pill border shadow-xs';
        fila.innerHTML = `
            <div class="col-7">
                <select name="recursos[${index}][id_inventario]" class="form-select form-select-sm border-0 bg-transparent rounded-pill" required onchange="validarStockFila(this)">
                    ${opcionesRecursos}
                </select>
            </div>
            <div class="col-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent border-0 small text-muted">Cant:</span>
                    <input type="number" name="recursos[${index}][cantidad]" class="form-control form-control-sm rounded-pill" value="1" min="1" required oninput="validarStockFila(this)">
                </div>
            </div>
            <div class="col-2 text-end pe-2">
                <button type="button" class="btn btn-sm btn-outline-danger rounded-circle" style="width: 28px; height: 28px; padding: 0;" onclick="this.closest('.row').remove()" title="Quitar recurso">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        contenedor.appendChild(fila);
    }

    function validarStockFila(element) {
        const fila = element.closest('.row');
        const select = fila.querySelector('select');
        const inputCant = fila.querySelector('input[type="number"]');

        const selectedOption = select.options[select.selectedIndex];
        const disponible = parseInt(selectedOption?.getAttribute('data-disponible') || 0);
        const cantidad = parseInt(inputCant.value || 0);

        if (cantidad > disponible && disponible > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Disponibilidad insuficiente',
                text: `Solo hay ${disponible} unidades disponibles de este recurso.`,
                confirmButtonColor: '#39A900'
            });
            inputCant.value = disponible;
        }
    }

    // ── 4. Validación en Vivo de Disponibilidad de Instructor (Criterio 3) ──
    let timeoutDisponibilidad = null;
    function verificarDisponibilidadEnVivo() {
        clearTimeout(timeoutDisponibilidad);
        timeoutDisponibilidad = setTimeout(() => {
            const instructorId = document.getElementById('progInstructor').value;
            const fecha = document.getElementById('progFecha').value;
            const horaInicio = document.getElementById('progHoraInicio').value;
            const horaFin = document.getElementById('progHoraFin').value;
            const feedback = document.getElementById('progInstructorFeedback');

            if (!instructorId || !fecha || !horaInicio || !horaFin) {
                feedback.className = 'd-none';
                return;
            }

            fetch('{{ route('controlecp.administrador.cronograma.verificar_disponibilidad') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    id_instructor: instructorId,
                    fecha: fecha,
                    hora_inicio: horaInicio,
                    hora_fin: horaFin
                })
            })
            .then(res => res.json())
            .then(data => {
                feedback.classList.remove('d-none');
                if (data.disponible) {
                    feedback.className = 'small mt-1 text-success fw-bold';
                    feedback.innerHTML = '<i class="fas fa-check-circle me-1"></i>Instructor libre en este horario.';
                } else {
                    feedback.className = 'small mt-1 text-danger fw-bold';
                    feedback.innerHTML = `<i class="fas fa-circle-exclamation me-1"></i>${data.mensaje}`;
                }
            })
            .catch(err => console.error(err));
        }, 400);
    }

    // ── 5. Guardar Nueva Programación (Criterios 1, 2, 3, 4, 12) ──
    function ejecutarGuardarProgramacion() {
        const form = document.getElementById('formProgramarActividad');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const btn = document.getElementById('btnGuardarProgramacion');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Guardando...';

        const formData = new FormData(form);

        fetch('{{ route('controlecp.administrador.cronograma.store') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(({ status, body }) => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-floppy-disk me-1"></i>Guardar Programación';

            if (status === 200 && body.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalProgramarActividad')).hide();
                Swal.fire({
                    icon: 'success',
                    title: '¡Programada con éxito!',
                    text: body.message,
                    confirmButtonColor: '#39A900'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo guardar',
                    text: body.message || 'Por favor verifica los campos obligatorios o la disponibilidad.',
                    confirmButtonColor: '#001A29'
                });
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-floppy-disk me-1"></i>Guardar Programación';
            Swal.fire({
                icon: 'error',
                title: 'Error de servidor',
                text: 'Ocurrió un error inesperado al procesar la solicitud.'
            });
        });
    }

    // ── 6. Ver Detalle y Trazabilidad (Modal 5) ──
    function verDetalleSesion(idCronograma) {
        fetch(`{{ url('/control-ecp/administrador/cronograma') }}/${idCronograma}`)
            .then(res => res.json())
            .then(res => {
                if (!res.success) return;
                const d = res.data;

                // Cabecera modal
                document.getElementById('detTituloCalendario').textContent = `${d.actividad_numero ? 'Actividad ' + d.actividad_numero : 'Actividad'} – ${d.momento_nombre}`;
                document.getElementById('detActividadNombre').textContent = d.actividad_nombre;
                document.getElementById('detFichaBadge').textContent = `Ficha ${d.ficha_code} - ${d.ficha_programa || ''}`;

                const badgeEstado = document.getElementById('detEstadoBadge');
                badgeEstado.textContent = d.estado;
                badgeEstado.className = d.estado === 'Finalizada' ? 'badge rounded-pill bg-success px-3' :
                                       (d.estado === 'Pospuesta' ? 'badge rounded-pill bg-warning text-dark px-3' : 'badge rounded-pill bg-primary px-3');

                const dot = document.getElementById('detSemaforoDot');
                dot.className = 'semaforo-indicator ' + (d.semaforo === 'rojo' ? 'semaforo-rojo' : (d.semaforo === 'amarillo' ? 'semaforo-amarillo' : (d.semaforo === 'verde' ? 'semaforo-verde' : 'semaforo-neutro')));

                // Detalles
                document.getElementById('detFechaHorario').textContent = `${d.fecha_formateada} (${d.horario})`;
                document.getElementById('detLugar').textContent = d.lugar;
                document.getElementById('detInstructor').textContent = d.instructor_nombre;
                document.getElementById('detCreador').textContent = d.creador_nombre;

                // Recursos
                const contenedorRec = document.getElementById('detListaRecursos');
                if (d.recursos && d.recursos.length > 0) {
                    let htmlRec = '';
                    d.recursos.forEach(r => {
                        htmlRec += `
                            <div class="d-flex justify-content-between align-items-center bg-white p-2 rounded-3 border">
                                <span class="fw-semibold text-dark"><i class="fas fa-box text-success me-1"></i>${r.nombre}</span>
                                <span class="badge bg-success-subtle text-success border">Cant: ${r.cantidad}</span>
                            </div>
                        `;
                    });
                    contenedorRec.innerHTML = htmlRec;
                } else {
                    contenedorRec.innerHTML = '<span class="text-muted">Sin recursos asignados.</span>';
                }

                // Trazabilidad / Historial
                const contHist = document.getElementById('detTimelineHistorial');
                if (d.historial && d.historial.length > 0) {
                    let htmlH = '';
                    d.historial.forEach(h => {
                        htmlH += `
                            <div class="d-flex gap-2 align-items-start border-bottom pb-2">
                                <span class="timeline-dot mt-1"></span>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold text-dark text-capitalize">${h.accion}</span>
                                        <span class="text-muted" style="font-size: 0.7rem;">${h.fecha_registro}</span>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.75rem;">Por: <strong>${h.autor}</strong></div>
                                    ${h.motivo ? `<div class="p-1 px-2 mt-1 bg-light rounded text-dark" style="font-size: 0.72rem;">"${h.motivo}"</div>` : ''}
                                </div>
                            </div>
                        `;
                    });
                    contHist.innerHTML = htmlH;
                } else {
                    contHist.innerHTML = '<span class="text-muted">No hay registros de historial aún.</span>';
                }

                // Configurar botones de acción del modal detalle
                document.getElementById('btnDetEliminar').onclick = () => {
                    bootstrap.Modal.getInstance(document.getElementById('modalDetalleSesion')).hide();
                    confirmarEliminarSesion(d.id_cronograma);
                };
                document.getElementById('btnDetPosponer').onclick = () => {
                    bootstrap.Modal.getInstance(document.getElementById('modalDetalleSesion')).hide();
                    abrirModalPosponer(d.id_cronograma);
                };
                document.getElementById('btnDetReasignar').onclick = () => {
                    bootstrap.Modal.getInstance(document.getElementById('modalDetalleSesion')).hide();
                    abrirModalReasignar(d.id_cronograma);
                };

                new bootstrap.Modal(document.getElementById('modalDetalleSesion')).show();
            });
    }

    // ── 7. Abrir y Ejecutar Edición (Criterio 8) ──
    function abrirModalEditar(idCronograma) {
        fetch(`{{ url('/control-ecp/administrador/cronograma') }}/${idCronograma}`)
            .then(res => res.json())
            .then(res => {
                if (!res.success) return;
                const d = res.data;

                document.getElementById('editCronogramaId').value = d.id_cronograma;
                document.getElementById('editActividad').value = d.id_actividad;
                document.getElementById('editFicha').value = d.id_ficha;
                document.getElementById('editFecha').value = d.fecha;
                document.getElementById('editHoraInicio').value = d.hora_inicio;
                document.getElementById('editHoraFin').value = d.hora_fin;
                document.getElementById('editLugar').value = d.lugar;
                document.getElementById('editMotivo').value = '';

                // Cargar recursos existentes
                const contEditRec = document.getElementById('contenedorRecursosEdit');
                contEditRec.innerHTML = '';
                if (d.recursos && d.recursos.length > 0) {
                    d.recursos.forEach(r => {
                        agregarFilaRecurso('contenedorRecursosEdit');
                        const ultFila = contEditRec.lastElementChild;
                        ultFila.querySelector('select').value = r.id_inventario;
                        ultFila.querySelector('input[type="number"]').value = r.cantidad;
                    });
                }

                new bootstrap.Modal(document.getElementById('modalEditarProgramacion')).show();
            });
    }

    function ejecutarActualizarProgramacion() {
        const id = document.getElementById('editCronogramaId').value;
        const form = document.getElementById('formEditarProgramacion');
        const formData = new FormData(form);

        fetch(`{{ url('/control-ecp/administrador/cronograma') }}/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalEditarProgramacion')).hide();
                Swal.fire({
                    icon: 'success',
                    title: '¡Actualizado!',
                    text: body.message,
                    confirmButtonColor: '#39A900'
                }).then(() => location.reload());
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error al actualizar',
                    text: body.message || 'Verifica los campos obligatorios.',
                    confirmButtonColor: '#001A29'
                });
            }
        });
    }

    // ── 8. Reasignar Instructor (Criterio 10) ──
    function abrirModalReasignar(idCronograma) {
        fetch(`{{ url('/control-ecp/administrador/cronograma') }}/${idCronograma}`)
            .then(res => res.json())
            .then(res => {
                if (!res.success) return;
                const d = res.data;
                document.getElementById('reasignarCronogramaId').value = d.id_cronograma;
                document.getElementById('reasignarActividadNombre').textContent = `${d.actividad_numero ? 'Actividad ' + d.actividad_numero + ': ' : ''}${d.actividad_nombre}`;
                document.getElementById('reasignarHorarioTexto').textContent = `${d.fecha_formateada} (${d.horario}) — Lugar: ${d.lugar}`;
                document.getElementById('reasignarInstructorActual').textContent = d.instructor_nombre;
                document.getElementById('nuevoInstructorId').value = '';
                document.getElementById('motivoReasignacion').value = '';

                new bootstrap.Modal(document.getElementById('modalReasignarInstructor')).show();
            });
    }

    function ejecutarReasignarInstructor() {
        const id = document.getElementById('reasignarCronogramaId').value;
        const nuevoInstructor = document.getElementById('nuevoInstructorId').value;
        const motivo = document.getElementById('motivoReasignacion').value.trim();

        if (!nuevoInstructor || !motivo) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos requeridos',
                text: 'Debes seleccionar el nuevo instructor e ingresar el motivo de la reasignación.'
            });
            return;
        }

        fetch(`{{ url('/control-ecp/administrador/cronograma') }}/${id}/reasignar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                nuevo_instructor_id: nuevoInstructor,
                motivo_reasignacion: motivo
            })
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalReasignarInstructor')).hide();
                Swal.fire({
                    icon: 'success',
                    title: '¡Instructor Reasignado!',
                    text: body.message,
                    confirmButtonColor: '#39A900'
                }).then(() => location.reload());
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Conflicto de Horario',
                    text: body.message,
                    confirmButtonColor: '#001A29'
                });
            }
        });
    }

    // ── 9. Posponer Actividad (Criterio 11) ──
    function abrirModalPosponer(idCronograma) {
        fetch(`{{ url('/control-ecp/administrador/cronograma') }}/${idCronograma}`)
            .then(res => res.json())
            .then(res => {
                if (!res.success) return;
                const d = res.data;
                document.getElementById('posponerCronogramaId').value = d.id_cronograma;
                document.getElementById('posponerActividadNombre').textContent = `${d.actividad_numero ? 'Actividad ' + d.actividad_numero + ': ' : ''}${d.actividad_nombre}`;
                document.getElementById('posponerFechaActual').textContent = `${d.fecha_formateada} (${d.horario})`;
                document.getElementById('posponerNuevaFecha').value = '';
                document.getElementById('posponerHoraInicio').value = d.hora_inicio;
                document.getElementById('posponerHoraFin').value = d.hora_fin;
                document.getElementById('posponerMotivo').value = '';

                new bootstrap.Modal(document.getElementById('modalPosponerActividad')).show();
            });
    }

    function ejecutarPosponerActividad() {
        const id = document.getElementById('posponerCronogramaId').value;
        const nuevaFecha = document.getElementById('posponerNuevaFecha').value;
        const motivo = document.getElementById('posponerMotivo').value.trim();
        const horaInicio = document.getElementById('posponerHoraInicio').value;
        const horaFin = document.getElementById('posponerHoraFin').value;

        if (!nuevaFecha || !motivo) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos obligatorios',
                text: 'La nueva fecha y el motivo de posposición son obligatorios.'
            });
            return;
        }

        fetch(`{{ url('/control-ecp/administrador/cronograma') }}/${id}/posponer`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                nueva_fecha: nuevaFecha,
                hora_inicio: horaInicio,
                hora_fin: horaFin,
                motivo: motivo
            })
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalPosponerActividad')).hide();
                Swal.fire({
                    icon: 'success',
                    title: '¡Actividad Pospuesta!',
                    text: body.message,
                    confirmButtonColor: '#39A900'
                }).then(() => location.reload());
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo posponer',
                    text: body.message,
                    confirmButtonColor: '#001A29'
                });
            }
        });
    }

    // ── 10. Eliminar Programación (Criterio 9) ──
    function confirmarEliminarSesion(idCronograma) {
        Swal.fire({
            title: '¿Eliminar programación?',
            text: 'Esta acción cancelará la sesión y liberará los recursos asignados. No se permite si ya tiene asistencia.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d32f2f',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="fas fa-trash me-1"></i>Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('/control-ecp/administrador/cronograma') }}/${idCronograma}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json().then(data => ({ status: res.status, body: data })))
                .then(({ status, body }) => {
                    if (status === 200 && body.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Eliminada!',
                            text: body.message,
                            confirmButtonColor: '#39A900'
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'No se puede eliminar',
                            text: body.message,
                            confirmButtonColor: '#001A29'
                        });
                    }
                });
            }
        });
    }
</script>
@endpush
@endsection
