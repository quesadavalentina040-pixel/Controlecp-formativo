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
    }
    .semaforo-verde {
        background-color: #2e7d32;
        box-shadow: 0 0 10px rgba(46, 125, 50, 0.6);
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

    @keyframes pulse-green {
        0% { box-shadow: 0 0 0 0 rgba(46, 125, 50, 0.7); }
        70% { box-shadow: 0 0 0 8px rgba(46, 125, 50, 0); }
        100% { box-shadow: 0 0 0 0 rgba(46, 125, 50, 0); }
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
        border-radius: 16px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: none;
    }
    .card-semaforo-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
    }

    /* ── Estilos del Calendario ── */
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 1px;
        background-color: #e4eceb;
        border-radius: 16px;
        overflow: hidden;
    }
    .calendar-day-header {
        background-color: #E8F5E9;
        color: #1B5E20;
        font-weight: 700;
        text-align: center;
        padding: 12px 6px;
        font-size: 0.85rem;
        text-transform: uppercase;
    }
    .calendar-day-cell {
        background-color: #ffffff;
        min-height: 110px;
        padding: 8px;
        position: relative;
        transition: background-color 0.15s ease;
    }
    .calendar-day-cell:hover {
        background-color: #f7fbf8;
    }
    .calendar-day-cell.other-month {
        background-color: #f8faf9;
        color: #b0bec5;
    }
    .calendar-day-number {
        font-weight: 700;
        font-size: 0.9rem;
        color: #001A29;
        margin-bottom: 6px;
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
    }

    .calendar-event-pill {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 3px 6px;
        border-radius: 6px;
        margin-bottom: 4px;
        cursor: pointer;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: flex;
        align-items: center;
        gap: 4px;
        transition: transform 0.15s ease;
    }
    .calendar-event-pill:hover {
        transform: scale(1.02);
    }
    .event-verde {
        background-color: #E8F5E9;
        color: #1B5E20;
        border-left: 3px solid #2E7D32;
    }
    .event-amarillo {
        background-color: #FFF8E1;
        color: #F57F17;
        border-left: 3px solid #F57F17;
    }
    .event-rojo {
        background-color: #FFEBEE;
        color: #D32F2F;
        border-left: 3px solid #D32F2F;
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">

    <!-- Breadcrumb & Cabecera -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.administrador.inicio') }}" class="text-decoration-none" style="color: #2E7D32;">
                            <i class="fas fa-house me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page" style="color: #1B5E20; font-weight: 600;">
                        Cronograma & Calendario
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: #001A29; font-family: 'Outfit', sans-serif;">
                <i class="fas fa-calendar-days me-2" style="color: #39A900;"></i>Cronograma con Semáforo
            </h2>
            <p class="text-muted small mb-0">Control ECP &bull; Calendario interactivo y semaforización de actividades por ficha</p>
        </div>

        <div class="d-flex gap-2">
            <button class="btn text-white rounded-pill px-3 shadow-sm" style="background-color: #2E7D32; border-color: #2E7D32;" data-bs-toggle="modal" data-bs-target="#modalNuevaSesion">
                <i class="fas fa-plus me-1"></i> Programar Sesión
            </button>
            <a href="{{ route('controlecp.administrador.inicio') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="fas fa-arrow-left me-1"></i> Volver al Inicio
            </a>
        </div>
    </div>

    <!-- Panel de Control de Semáforos -->
    <div class="row g-3 mb-4">
        <!-- Verde: Ejecutado / Al día -->
        <div class="col-12 col-md-4">
            <div class="card card-semaforo-box shadow-sm" style="background: linear-gradient(135deg, #E8F5E9 0%, #C8E6C9 100%);">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <span class="semaforo-indicator semaforo-verde" style="width: 22px; height: 22px;"></span>
                        <div>
                            <span class="d-block fw-bold text-uppercase small" style="color: #1B5E20; letter-spacing: 0.5px;">Al Día / Completado</span>
                            <span class="h3 fw-bold mb-0" style="color: #1B5E20;">12</span>
                            <span class="small text-muted d-block">Sesiones ejecutadas a tiempo</span>
                        </div>
                    </div>
                    <i class="fas fa-circle-check fa-2x text-success opacity-50"></i>
                </div>
            </div>
        </div>

        <!-- Amarillo: Próximo / En proceso -->
        <div class="col-12 col-md-4">
            <div class="card card-semaforo-box shadow-sm" style="background: linear-gradient(135deg, #FFF8E1 0%, #FFE082 100%);">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <span class="semaforo-indicator semaforo-amarillo" style="width: 22px; height: 22px;"></span>
                        <div>
                            <span class="d-block fw-bold text-uppercase small" style="color: #F57F17; letter-spacing: 0.5px;">Próximo (7 Días)</span>
                            <span class="h3 fw-bold mb-0" style="color: #F57F17;">5</span>
                            <span class="small text-muted d-block">Programadas esta semana</span>
                        </div>
                    </div>
                    <i class="fas fa-clock fa-2x text-warning opacity-50"></i>
                </div>
            </div>
        </div>

        <!-- Rojo: Vencido / Retrasado -->
        <div class="col-12 col-md-4">
            <div class="card card-semaforo-box shadow-sm" style="background: linear-gradient(135deg, #FFEBEE 0%, #FFCDD2 100%);">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <span class="semaforo-indicator semaforo-rojo" style="width: 22px; height: 22px;"></span>
                        <div>
                            <span class="d-block fw-bold text-uppercase small text-danger" style="letter-spacing: 0.5px;">Atrasado / Alerta</span>
                            <span class="h3 fw-bold mb-0 text-danger">2</span>
                            <span class="small text-muted d-block">Requiere atención inmediata</span>
                        </div>
                    </div>
                    <i class="fas fa-triangle-exclamation fa-2x text-danger opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Pestañas de Vista: Calendario vs Lista -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <ul class="nav nav-pills p-1 bg-light rounded-pill border" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill px-4 fw-bold" id="tab-calendario" data-bs-toggle="pill" data-bs-target="#content-calendario" type="button" role="tab">
                    <i class="fas fa-calendar-alt me-1"></i> Vista Calendario
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4 fw-bold" id="tab-lista" data-bs-toggle="pill" data-bs-target="#content-lista" type="button" role="tab">
                    <i class="fas fa-list-ul me-1"></i> Vista Lista & Semáforo
                </button>
            </li>
        </ul>

        <div class="d-flex align-items-center gap-2">
            <span class="small text-muted fw-semibold me-2">Leyenda Semáforo:</span>
            <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="fas fa-circle me-1"></i> Completado</span>
            <span class="badge bg-warning-subtle text-warning border border-warning-subtle"><i class="fas fa-circle me-1"></i> Próximo</span>
            <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="fas fa-circle me-1"></i> Atrasado</span>
        </div>
    </div>

    <div class="tab-content" id="pills-tabContent">

        <!-- ════════════════════════════════════════════════
             PESTAÑA 1: CALENDARIO MENSUAL INTERACTIVO
        ════════════════════════════════════════════════ -->
        <div class="tab-pane fade show active" id="content-calendario" role="tabpanel">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-header bg-white p-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <button class="btn btn-sm btn-outline-secondary rounded-circle" id="prevMonthBtn" title="Mes Anterior">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <h4 class="h5 fw-bold mb-0 text-dark" id="calendarMonthTitle" style="font-family: 'Outfit', sans-serif;">
                            Septiembre 2026
                        </h4>
                        <button class="btn btn-sm btn-outline-secondary rounded-circle" id="nextMonthBtn" title="Mes Siguiente">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    <button class="btn btn-sm btn-outline-success rounded-pill px-3" id="todayBtn">
                        <i class="fas fa-calendar-day me-1"></i> Hoy
                    </button>
                </div>

                <div class="card-body p-3">
                    <div class="calendar-grid">
                        <!-- Cabecera Días -->
                        <div class="calendar-day-header">Lun</div>
                        <div class="calendar-day-header">Mar</div>
                        <div class="calendar-day-header">Mié</div>
                        <div class="calendar-day-header">Jue</div>
                        <div class="calendar-day-header">Vie</div>
                        <div class="calendar-day-header">Sáb</div>
                        <div class="calendar-day-header">Dom</div>

                        <!-- Semana 1 -->
                        <div class="calendar-day-cell other-month"><span class="calendar-day-number">31</span></div>
                        <div class="calendar-day-cell"><span class="calendar-day-number">1</span></div>
                        <div class="calendar-day-cell"><span class="calendar-day-number">2</span></div>
                        <div class="calendar-day-cell"><span class="calendar-day-number">3</span></div>
                        <div class="calendar-day-cell"><span class="calendar-day-number">4</span></div>
                        <div class="calendar-day-cell"><span class="calendar-day-number">5</span></div>
                        <div class="calendar-day-cell"><span class="calendar-day-number">6</span></div>

                        <!-- Semana 2 -->
                        <div class="calendar-day-cell"><span class="calendar-day-number">7</span></div>
                        <div class="calendar-day-cell">
                            <span class="calendar-day-number">8</span>
                            <div class="calendar-event-pill event-verde" title="Ficha 2670192 - Momento 1 (Autoconciencia)">
                                <span class="semaforo-indicator semaforo-verde" style="width: 8px; height: 8px;"></span> Ficha 2670192
                            </div>
                        </div>
                        <div class="calendar-day-cell"><span class="calendar-day-number">9</span></div>
                        <div class="calendar-day-cell">
                            <span class="calendar-day-number">10</span>
                            <div class="calendar-event-pill event-verde" title="Ficha 2558910 - Momento 3 (Resolución)">
                                <span class="semaforo-indicator semaforo-verde" style="width: 8px; height: 8px;"></span> Ficha 2558910
                            </div>
                        </div>
                        <div class="calendar-day-cell"><span class="calendar-day-number">11</span></div>
                        <div class="calendar-day-cell">
                            <span class="calendar-day-number">12</span>
                            <div class="calendar-event-pill event-rojo" title="Ficha 2670192 - Atrasado">
                                <span class="semaforo-indicator semaforo-rojo" style="width: 8px; height: 8px;"></span> Ficha 2670192 (Atrasado)
                            </div>
                        </div>
                        <div class="calendar-day-cell"><span class="calendar-day-number">13</span></div>

                        <!-- Semana 3 -->
                        <div class="calendar-day-cell"><span class="calendar-day-number">14</span></div>
                        <div class="calendar-day-cell"><span class="calendar-day-number">15</span></div>
                        <div class="calendar-day-cell today">
                            <span class="calendar-day-number">16</span>
                            <div class="small fw-bold text-success mt-1" style="font-size: 0.68rem;">Hoy</div>
                        </div>
                        <div class="calendar-day-cell"><span class="calendar-day-number">17</span></div>
                        <div class="calendar-day-cell">
                            <span class="calendar-day-number">18</span>
                            <div class="calendar-event-pill event-amarillo" title="Ficha 2670195 - Momento 2 (Convivencia)">
                                <span class="semaforo-indicator semaforo-amarillo" style="width: 8px; height: 8px;"></span> Ficha 2670195
                            </div>
                        </div>
                        <div class="calendar-day-cell"><span class="calendar-day-number">19</span></div>
                        <div class="calendar-day-cell"><span class="calendar-day-number">20</span></div>

                        <!-- Semana 4 -->
                        <div class="calendar-day-cell"><span class="calendar-day-number">21</span></div>
                        <div class="calendar-day-cell">
                            <span class="calendar-day-number">22</span>
                            <div class="calendar-event-pill event-amarillo" title="Ficha 2670192 - Momento 2">
                                <span class="semaforo-indicator semaforo-amarillo" style="width: 8px; height: 8px;"></span> Ficha 2670192
                            </div>
                        </div>
                        <div class="calendar-day-cell"><span class="calendar-day-number">23</span></div>
                        <div class="calendar-day-cell"><span class="calendar-day-number">24</span></div>
                        <div class="calendar-day-cell"><span class="calendar-day-number">25</span></div>
                        <div class="calendar-day-cell"><span class="calendar-day-number">26</span></div>
                        <div class="calendar-day-cell"><span class="calendar-day-number">27</span></div>

                        <!-- Semana 5 -->
                        <div class="calendar-day-cell"><span class="calendar-day-number">28</span></div>
                        <div class="calendar-day-cell"><span class="calendar-day-number">29</span></div>
                        <div class="calendar-day-cell"><span class="calendar-day-number">30</span></div>
                        <div class="calendar-day-cell other-month"><span class="calendar-day-number">1</span></div>
                        <div class="calendar-day-cell other-month"><span class="calendar-day-number">2</span></div>
                        <div class="calendar-day-cell other-month"><span class="calendar-day-number">3</span></div>
                        <div class="calendar-day-cell other-month"><span class="calendar-day-number">4</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════
             PESTAÑA 2: VISTA LISTA Y TABLA DE SEMÁFOROS
        ════════════════════════════════════════════════ -->
        <div class="tab-pane fade" id="content-lista" role="tabpanel">
            <!-- Barra de Filtro y Búsqueda -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-body p-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3">
                                    <i class="fas fa-magnifying-glass text-muted"></i>
                                </span>
                                <input type="text" id="filterBuscar" class="form-control border-start-0 rounded-end-pill" placeholder="Buscar por ficha, actividad o instructor...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select id="filterSemaforo" class="form-select rounded-pill">
                                <option value="todos">Todos los semáforos</option>
                                <option value="verde">🟢 Verde (Completado / Al día)</option>
                                <option value="amarillo">🟡 Amarillo (Próximo / En ejecución)</option>
                                <option value="rojo">🔴 Rojo (Atrasado / Pendiente crítico)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="filterMomento" class="form-select rounded-pill">
                                <option value="todos">Todos los Momentos</option>
                                <option value="Momento 1">Momento 1: Autoconciencia</option>
                                <option value="Momento 2">Momento 2: Convivencia</option>
                                <option value="Momento 3">Momento 3: Resolución</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla del Cronograma -->
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tablaCronograma">
                            <thead style="background-color: #E8F5E9; color: #1B5E20;" class="small fw-bold">
                                <tr>
                                    <th class="ps-4 py-3 text-center">Semáforo</th>
                                    <th class="py-3">Fecha y Hora</th>
                                    <th class="py-3">Ficha / Programa</th>
                                    <th class="py-3">Momento & Actividad</th>
                                    <th class="py-3">Instructor Cargo</th>
                                    <th class="py-3 text-center">Estado</th>
                                    <th class="text-end pe-4 py-3">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <!-- Fila 1: Rojo (Atrasado) -->
                                <tr class="row-rojo">
                                    <td class="ps-4 text-center">
                                        <span class="semaforo-indicator semaforo-rojo" title="Semáforo Rojo: Sesión atrasada"></span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">12 Sep 2026</div>
                                        <div class="small text-muted">08:00 AM - 10:00 AM</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-dark border font-monospace">Ficha 2670192</span>
                                        <div class="small text-muted">ADSO - Mañana</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">Momento 1: Autoconciencia</div>
                                        <div class="small text-muted">01. Reconocimiento y Gestión Emocional</div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center fw-bold small" style="width: 32px; height: 32px;">
                                                CR
                                            </div>
                                            <div>
                                                <div class="small fw-bold">Carlos Rodríguez</div>
                                                <div class="small text-muted" style="font-size: 0.72rem;">Instructor ECP</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle px-3 py-1">
                                            <i class="fas fa-exclamation-circle me-1"></i> Atrasado
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-success rounded-pill px-3">
                                            <i class="fas fa-check me-1"></i> Marcar Ejecutado
                                        </button>
                                    </td>
                                </tr>

                                <!-- Fila 2: Amarillo (Próximo) -->
                                <tr class="row-amarillo">
                                    <td class="ps-4 text-center">
                                        <span class="semaforo-indicator semaforo-amarillo" title="Semáforo Amarillo: Programado próximamente"></span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">18 Sep 2026</div>
                                        <div class="small text-muted">10:00 AM - 12:00 PM</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-dark border font-monospace">Ficha 2670195</span>
                                        <div class="small text-muted">Gestión de Mercados</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">Momento 2: Convivencia</div>
                                        <div class="small text-muted">01. Círculos de Diálogo y Empatía</div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold small" style="width: 32px; height: 32px;">
                                                MA
                                            </div>
                                            <div>
                                                <div class="small fw-bold">María Andrea López</div>
                                                <div class="small text-muted" style="font-size: 0.72rem;">Instructora ECP</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle px-3 py-1">
                                            <i class="fas fa-clock me-1"></i> Próximo (2 días)
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <i class="fas fa-pen me-1"></i> Reprogramar
                                        </button>
                                    </td>
                                </tr>

                                <!-- Fila 3: Verde (Completado) -->
                                <tr class="row-verde">
                                    <td class="ps-4 text-center">
                                        <span class="semaforo-indicator semaforo-verde" title="Semáforo Verde: Ejecutado exitosamente"></span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">10 Sep 2026</div>
                                        <div class="small text-muted">02:00 PM - 04:00 PM</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-dark border font-monospace">Ficha 2558910</span>
                                        <div class="small text-muted">Agroindustria - Tarde</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">Momento 3: Resolución</div>
                                        <div class="small text-muted">01. Taller de Mediación Pacífica</div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center fw-bold small" style="width: 32px; height: 32px;">
                                                CR
                                            </div>
                                            <div>
                                                <div class="small fw-bold">Carlos Rodríguez</div>
                                                <div class="small text-muted" style="font-size: 0.72rem;">Instructor ECP</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-1">
                                            <i class="fas fa-circle-check me-1"></i> Completado
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" disabled>
                                            <i class="fas fa-lock me-1"></i> Finalizado
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Modal Programar Nueva Sesión -->
<div class="modal fade" id="modalNuevaSesion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" style="color: #001A29;">
                    <i class="fas fa-calendar-plus me-2" style="color: #39A900;"></i>Programar Sesión ECP
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Ficha de Formación</label>
                        <select class="form-select rounded-pill">
                            <option value="">Seleccione la Ficha SENA...</option>
                            <option>2670192 - ADSO</option>
                            <option>2670195 - Gestión de Mercados</option>
                            <option>2558910 - Agroindustria</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Momento Pedagógico</label>
                        <select class="form-select rounded-pill">
                            <option value="">Seleccione Momento...</option>
                            <option>Momento 1: Autoconciencia (10 Actividades)</option>
                            <option>Momento 2: Convivencia (4 Actividades)</option>
                            <option>Momento 3: Resolución (4 Actividades)</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Fecha Programada</label>
                            <input type="date" class="form-control rounded-pill">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Hora Inicio</label>
                            <input type="time" class="form-control rounded-pill">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Instructor Asignado</label>
                        <input type="text" class="form-control rounded-pill" placeholder="Nombre del instructor">
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn text-white rounded-pill px-4" style="background-color: #2E7D32;" data-bs-dismiss="modal">Guardar Sesión</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterSemaforo = document.getElementById('filterSemaforo');
        const filterMomento = document.getElementById('filterMomento');
        const filterBuscar = document.getElementById('filterBuscar');
        const rows = document.querySelectorAll('#tablaCronograma tbody tr');

        if (filterSemaforo && filterMomento && filterBuscar) {
            function filtrarTabla() {
                const semVal = filterSemaforo.value;
                const momVal = filterMomento.value.toLowerCase();
                const busVal = filterBuscar.value.toLowerCase();

                rows.forEach(row => {
                    let matchSem = true;
                    let matchMom = true;
                    let matchBus = true;

                    if (semVal !== 'todos') {
                        matchSem = row.classList.contains('row-' + semVal);
                    }

                    if (momVal !== 'todos') {
                        matchMom = row.innerText.toLowerCase().includes(momVal);
                    }

                    if (busVal !== '') {
                        matchBus = row.innerText.toLowerCase().includes(busVal);
                    }

                    if (matchSem && matchMom && matchBus) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            filterSemaforo.addEventListener('change', filtrarTabla);
            filterMomento.addEventListener('change', filtrarTabla);
            filterBuscar.addEventListener('input', filtrarTabla);
        }
    });
</script>
@endpush
@endsection
