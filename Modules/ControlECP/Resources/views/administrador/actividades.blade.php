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
    .act-page { font-family: 'Outfit', sans-serif; }

    /* KPI cards */
    .kpi-card {
        background: #fff; border-radius: 18px;
        padding: 1.1rem 1rem;
        box-shadow: 0 4px 18px rgba(0,26,41,.07);
        border: 1px solid rgba(0,0,0,.04);
        display: flex; flex-direction: column; gap: .3rem;
        transition: transform .2s, box-shadow .2s;
    }
    .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 8px 28px rgba(0,26,41,.12); }
    .kpi-icon-wrap {
        width: 38px; height: 38px; border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; margin-bottom: .2rem;
    }
    .kpi-value { font-size: 1.7rem; font-weight: 800; line-height: 1; color: var(--ecp-navy); }
    .kpi-label { font-size: .77rem; color: #7a8394; font-weight: 500; }

    /* Tarjetas de actividad */
    .act-card {
        background: #fff; border-radius: 16px;
        box-shadow: 0 4px 16px rgba(0,26,41,.06);
        border: 1px solid rgba(0,0,0,.04);
        transition: transform .2s, box-shadow .2s;
        overflow: hidden;
    }
    .act-card:hover { transform: translateY(-3px); box-shadow: 0 10px 28px rgba(0,26,41,.11); }
    .act-card.estado-inactivo { opacity: .65; filter: grayscale(.4); }
    .act-card.estado-finalizado { border-top: 3px solid #6c757d; }

    /* Badge estado */
    .badge-activo     { background:#e8f8e8; color:#1e7000; border:1px solid #c2eec2; }
    .badge-inactivo   { background:#f5f5f5; color:#666; border:1px solid #ddd; }
    .badge-finalizado { background:#e8f0fe; color:#3949ab; border:1px solid #c5cae9; }

    /* Componentes de la actividad */
    .comp-badge {
        font-size: .72rem; padding: .25rem .65rem; border-radius: 50px; font-weight: 600;
        border: 1.5px solid; display: inline-flex; align-items: center; gap: .3rem;
    }
    .comp-on  { background: #f0fbf4; }
    .comp-off { background: #f5f5f5; opacity: .6; }

    /* Sección de momento */
    .momento-section { border-radius: 18px; overflow: hidden; margin-bottom: 2rem; }
    .momento-header  {
        padding: 1rem 1.25rem;
        color: #fff;
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: .5rem;
    }
    .act-grid-sm {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1rem;
        padding: 1rem 1.25rem;
        background: #f8faf9;
    }

    /* Filtro pill */
    .pill-act {
        border-radius: 20px; font-size: .81rem; font-weight: 600; padding: 5px 14px;
        text-decoration: none; transition: all .18s;
    }
    .pill-act.active { color: #fff; box-shadow: 0 4px 10px rgba(0,0,0,.15); }
    .pill-act:not(.active) { background: #f1f4f3; color: var(--ecp-navy); }
    .pill-act:not(.active):hover { background: #e5ece9; }
</style>
@endpush

@section('dashboard-content')
<div class="act-page container-fluid px-2 px-md-3 py-2">

    {{-- Breadcrumb & Cabecera --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.administrador.inicio') }}" class="text-decoration-none" style="color:var(--ecp-green);">
                            <i class="fas fa-house me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item active" style="color:var(--ecp-navy);font-weight:600;">Actividades</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color:var(--ecp-navy);">
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
            <a href="{{ route('controlecp.administrador.inicio') }}"
               class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Inicio
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
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-2">
            <div class="kpi-card">
                <div class="kpi-icon-wrap" style="background:rgba(57,169,0,.12);">
                    <i class="fas fa-list-check" style="color:var(--ecp-green);"></i>
                </div>
                <div class="kpi-value" style="color:var(--ecp-green);">{{ $stats['total'] }}</div>
                <div class="kpi-label">Total</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card">
                <div class="kpi-icon-wrap" style="background:#e8f8e8;">
                    <i class="fas fa-circle-check" style="color:#1e7000;"></i>
                </div>
                <div class="kpi-value text-success">{{ $stats['activas'] }}</div>
                <div class="kpi-label">Activas</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card">
                <div class="kpi-icon-wrap" style="background:#fdecea;">
                    <i class="fas fa-ban" style="color:#c0392b;"></i>
                </div>
                <div class="kpi-value text-danger">{{ $stats['inactivas'] }}</div>
                <div class="kpi-label">Inactivas</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card">
                <div class="kpi-icon-wrap" style="background:#e8f0fe;">
                    <i class="fas fa-flag-checkered" style="color:#3949ab;"></i>
                </div>
                <div class="kpi-value" style="color:#3949ab;">{{ $stats['finalizadas'] }}</div>
                <div class="kpi-label">Finalizadas</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card">
                <div class="kpi-icon-wrap" style="background:#ebf5fb;">
                    <i class="fas fa-id-card" style="color:var(--ecp-blue);"></i>
                </div>
                <div class="kpi-value" style="color:var(--ecp-blue);">{{ $stats['fichas'] }}</div>
                <div class="kpi-label">Fichas activas</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card">
                <div class="kpi-icon-wrap" style="background:#f4ecf7;">
                    <i class="fas fa-star" style="color:var(--ecp-purple);"></i>
                </div>
                <div class="kpi-value" style="color:var(--ecp-purple);">{{ $stats['momentos'] }}</div>
                <div class="kpi-label">Momentos</div>
            </div>
        </div>
    </div>

    {{-- HU3: Listado agrupado por momento --}}
    @if($momentos->count() > 0)
        @foreach($momentos as $momento)
            <div class="card border-0 shadow-sm momento-section">
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
                                onclick="abrirModalCrear({{ $momento->id }}, '{{ addslashes($momento->nombre) }}')">
                            <i class="fas fa-plus me-1"></i> Agregar
                        </button>
                    </div>
                </div>

                {{-- Grid de tarjetas del momento --}}
                @if($momento->actividades->count() > 0)
                    <div class="act-grid-sm">
                        @foreach($momento->actividades as $a)
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
                            @endphp
                            <div class="act-card estado-{{ strtolower($a->estado) }}"
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

                                    {{-- Componentes (HU6/HU9) --}}
                                    <div class="d-flex flex-wrap gap-1 mb-3">
                                        <span class="comp-badge comp-{{ $a->tiene_asistencia ? 'on' : 'off' }}"
                                              style="color:{{ $a->tiene_asistencia ? '#39A900' : '#999' }}; border-color:{{ $a->tiene_asistencia ? '#c2eec2' : '#ddd' }};">
                                            <i class="fas fa-clipboard-list"></i> Asistencia
                                        </span>
                                        <span class="comp-badge comp-{{ $a->encuesta_id ? 'on' : 'off' }}"
                                              style="color:{{ $a->encuesta_id ? '#0070B8' : '#999' }}; border-color:{{ $a->encuesta_id ? '#bce1f7' : '#ddd' }};">
                                            <i class="fas fa-clipboard-question"></i> Encuesta
                                        </span>
                                        <span class="comp-badge comp-{{ $a->tiene_evidencia ? 'on' : 'off' }}"
                                              style="color:{{ $a->tiene_evidencia ? '#6A1B9A' : '#999' }}; border-color:{{ $a->tiene_evidencia ? '#e1bee7' : '#ddd' }};">
                                            <i class="fas fa-photo-film"></i> Evidencia
                                        </span>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between border-top pt-2">
                                        <span class="small text-muted">
                                            <i class="far fa-clock me-1"></i>{{ $a->duracion }}
                                        </span>

                                        {{-- Menú de acciones --}}
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light rounded-pill px-2" type="button"
                                                    data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-vertical text-muted"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0"
                                                style="border-radius:12px; font-size:.84rem;">
                                                {{-- HU4: Editar --}}
                                                <li>
                                                    <button class="dropdown-item py-2" type="button"
                                                            onclick="abrirModalEditar({{ json_encode([
                                                                'id'               => $a->id,
                                                                'nombre'           => $a->nombre,
                                                                'descripcion'      => $a->descripcion,
                                                                'objetivo'         => $a->objetivo,
                                                                'duracion'         => $a->duracion,
                                                                'duracion_min'     => $a->duracion_min,
                                                                'metodologia'      => $a->metodologia,
                                                                'momento_id'       => $a->momento_id,
                                                                'tiene_asistencia' => $a->tiene_asistencia,
                                                                'encuesta_id'      => $a->encuesta_id,
                                                                'tiene_evidencia'  => $a->tiene_evidencia,
                                                            ]) }})">
                                                        <i class="fas fa-pen me-2 text-secondary"></i> Editar
                                                    </button>
                                                </li>
                                                {{-- HU5: Cambiar momento --}}
                                                <li>
                                                    <button class="dropdown-item py-2" type="button"
                                                            onclick="abrirModalCambiarMomento({{ $a->id }}, {{ $a->momento_id }}, '{{ addslashes($a->nombre) }}')">
                                                        <i class="fas fa-right-left me-2 text-info"></i> Cambiar Momento
                                                    </button>
                                                </li>
                                                {{-- HU10: Ver componentes --}}
                                                <li>
                                                    <button class="dropdown-item py-2" type="button"
                                                            onclick="verComponentes({{ $a->id }})">
                                                        <i class="fas fa-puzzle-piece me-2" style="color:var(--ecp-green);"></i> Ver Componentes
                                                    </button>
                                                </li>
                                                {{-- HU17: Ver evidencias --}}
                                                <li>
                                                    <button class="dropdown-item py-2" type="button"
                                                            onclick="verEvidencias({{ $a->id }}, '{{ addslashes($a->nombre) }}')">
                                                        <i class="fas fa-photo-film me-2" style="color:var(--ecp-purple);"></i> Ver Evidencias
                                                    </button>
                                                </li>
                                                {{-- HU18: Calificaciones --}}
                                                <li>
                                                    <button class="dropdown-item py-2" type="button"
                                                            onclick="verCalificaciones({{ $a->id }}, '{{ addslashes($a->nombre) }}')">
                                                        <i class="fas fa-star me-2 text-warning"></i> Calificaciones
                                                    </button>
                                                </li>
                                                {{-- HU15: Subir evidencia --}}
                                                @if($a->tiene_evidencia)
                                                <li>
                                                    <button class="dropdown-item py-2" type="button"
                                                            onclick="abrirModalEvidencia({{ $a->id }}, '{{ addslashes($a->nombre) }}')">
                                                        <i class="fas fa-upload me-2" style="color:var(--ecp-blue);"></i> Subir Evidencia
                                                    </button>
                                                </li>
                                                @endif
                                                <li><hr class="dropdown-divider"></li>
                                                {{-- HU13: Finalizar --}}
                                                @if($a->estado === 'Activo')
                                                <li>
                                                    <button class="dropdown-item py-2 text-primary" type="button"
                                                            onclick="confirmarFinalizar({{ $a->id }}, '{{ addslashes($a->nombre) }}')">
                                                        <i class="fas fa-flag-checkered me-2"></i> Finalizar
                                                    </button>
                                                </li>
                                                @endif
                                                {{-- HU11/HU12: Desactivar / Activar --}}
                                                @if($a->estado === 'Activo')
                                                <li>
                                                    <button class="dropdown-item py-2 text-danger" type="button"
                                                            onclick="confirmarEstado({{ $a->id }}, 'desactivar', '{{ addslashes($a->nombre) }}')">
                                                        <i class="fas fa-ban me-2"></i> Desactivar
                                                    </button>
                                                </li>
                                                @elseif($a->estado === 'Inactivo')
                                                <li>
                                                    <button class="dropdown-item py-2 text-success" type="button"
                                                            onclick="confirmarEstado({{ $a->id }}, 'activar', '{{ addslashes($a->nombre) }}')">
                                                        <i class="fas fa-circle-check me-2"></i> Activar
                                                    </button>
                                                </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-muted small" style="background:#f8faf9;">
                        <i class="fas fa-list-check fa-2x mb-2 text-muted" style="opacity:.4;"></i>
                        <p class="mb-2">No hay actividades en este momento.</p>
                        <button type="button" class="btn btn-sm rounded-pill px-3 text-white"
                                style="background:{{ $momento->color }};"
                                onclick="abrirModalCrear({{ $momento->id }}, '{{ addslashes($momento->nombre) }}')">
                            <i class="fas fa-plus me-1"></i> Agregar primera actividad
                        </button>
                    </div>
                @endif
            </div>
        @endforeach
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

{{-- Modal: Crear / Editar actividad (HU1 / HU2 / HU4) --}}
<div class="modal fade" id="modalFormActividad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width: 42px; height: 42px; background: #e8f8e8; color: var(--ecp-green);">
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
                                <option value="{{ $m->id }}" data-color="{{ $m->color }}">{{ $m->nombre }}</option>
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

                    {{-- Descripción --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small" style="color:var(--ecp-navy);">Descripción</label>
                        <textarea id="formActDescripcion" class="form-control" rows="2" maxlength="1000"
                                  placeholder="Descripción general de la actividad..."></textarea>
                    </div>

                    {{-- Metodología --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small" style="color:var(--ecp-navy);">Metodología</label>
                        <textarea id="formActMetodologia" class="form-control" rows="2" maxlength="1000"
                                  placeholder="Dinámicas, técnicas y recursos pedagógicos..."></textarea>
                    </div>

                    {{-- Duración --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small" style="color:var(--ecp-navy);">
                            Duración <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="formActDuracion" class="form-control"
                               placeholder="Ej: 2 Horas" maxlength="30" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small" style="color:var(--ecp-navy);">
                            Duración (minutos) <span class="text-danger">*</span>
                        </label>
                        <input type="number" id="formActDuracionMin" class="form-control"
                               placeholder="120" min="15" max="600" required>
                    </div>

                    {{-- Componentes --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small" style="color:var(--ecp-navy);">Componentes activos</label>
                        <div class="d-flex flex-column gap-2 mt-1">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="formActAsistencia" checked>
                                <label class="form-check-label small" for="formActAsistencia">
                                    <i class="fas fa-clipboard-list text-success me-1"></i> Asistencia
                                </label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="formActEvidencia" checked>
                                <label class="form-check-label small" for="formActEvidencia">
                                    <i class="fas fa-photo-film me-1" style="color:var(--ecp-purple);"></i> Evidencia
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Encuesta (HU7/HU8) --}}
                    <div class="col-12">
                        <div class="p-3 rounded-3 border" style="background:#f8faf9;">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="formActTieneEncuesta"
                                       onchange="toggleEncuestaSelector()">
                                <label class="form-check-label fw-semibold small" for="formActTieneEncuesta"
                                       style="color:var(--ecp-blue);">
                                    <i class="fas fa-clipboard-question me-1"></i> Asociar encuesta del repositorio
                                </label>
                            </div>
                            <div id="encuestaSelectorWrap" class="d-none">
                                @if($encuestasActivas->count() > 0)
                                    <select id="formActEncuesta" class="form-select form-select-sm">
                                        <option value="">— Seleccionar encuesta activa —</option>
                                        @foreach($encuestasActivas as $enc)
                                            <option value="{{ $enc->id }}">{{ $enc->titulo }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    {{-- HU8: Sin encuestas disponibles --}}
                                    <div class="alert alert-warning border-0 small py-2 mb-0" style="border-radius:10px;">
                                        <i class="fas fa-triangle-exclamation me-1"></i>
                                        <strong>No hay encuestas activas disponibles.</strong>
                                        Primero cree y active una encuesta en el repositorio antes de asociarla.
                                    </div>
                                @endif
                            </div>
                        </div>
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
<div class="modal fade" id="modalCambiarMomento" tabindex="-1" aria-hidden="true">
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
                        <option value="{{ $m->id }}">{{ $m->nombre }}</option>
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
<div class="modal fade" id="modalComponentes" tabindex="-1" aria-hidden="true">
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

{{-- Modal: Confirmar estado / finalizar (HU11/HU12/HU13) --}}
<div class="modal fade" id="modalConfirmActEstado" tabindex="-1" aria-hidden="true">
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
<div class="modal fade" id="modalEvidencia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius:20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div>
                    <h5 class="modal-title fw-bold mb-0" style="color:var(--ecp-navy);">
                        <i class="fas fa-upload me-2" style="color:var(--ecp-blue);"></i>Subir Evidencia
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
                    <label class="d-flex align-items-center gap-2 p-3 rounded-3 border flex-fill" style="cursor:pointer;">
                        <input type="radio" name="evidTipo" value="foto" class="form-check-input mt-0" checked
                               onchange="toggleEvidTipo()">
                        <div>
                            <div class="fw-semibold small"><i class="fas fa-image me-1 text-success"></i> Foto</div>
                            <div class="text-muted" style="font-size:.75rem;">JPG o PNG, máx. 5 MB</div>
                        </div>
                    </label>
                    <label class="d-flex align-items-center gap-2 p-3 rounded-3 border flex-fill" style="cursor:pointer;">
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
                            style="background:var(--ecp-blue);" onclick="ejecutarSubirEvidencia()">
                        <i class="fas fa-upload me-1"></i> Subir Evidencia
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Ver evidencias (HU17) --}}
<div class="modal fade" id="modalVerEvidencias" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius:20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div>
                    <h5 class="modal-title fw-bold mb-0" style="color:var(--ecp-navy);">
                        <i class="fas fa-photo-film me-2" style="color:var(--ecp-purple);"></i>Evidencias de la Actividad
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
<div class="modal fade" id="modalCalificaciones" tabindex="-1" aria-hidden="true">
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
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const BASE_URL  = "{{ url('/control-ecp/administrador/actividades') }}";

// ── HU1: Abrir modal crear ────────────────────────────────────────────────────
function abrirModalCrear(momentoId = null, momentoNombre = null) {
    document.getElementById('formActId').value = '';
    document.getElementById('formActNombre').value = '';
    document.getElementById('formActDescripcion').value = '';
    document.getElementById('formActObjetivo').value = '';
    document.getElementById('formActDuracion').value = '';
    document.getElementById('formActDuracionMin').value = '120';
    document.getElementById('formActMetodologia').value = '';
    document.getElementById('formActAsistencia').checked = true;
    document.getElementById('formActEvidencia').checked  = true;
    document.getElementById('formActTieneEncuesta').checked = false;
    toggleEncuestaSelector();
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
    document.getElementById('formActDescripcion').value     = data.descripcion ?? '';
    document.getElementById('formActObjetivo').value        = data.objetivo ?? '';
    document.getElementById('formActDuracion').value        = data.duracion;
    document.getElementById('formActDuracionMin').value     = data.duracion_min;
    document.getElementById('formActMetodologia').value     = data.metodologia ?? '';
    document.getElementById('formActMomento').value         = data.momento_id;
    document.getElementById('formActAsistencia').checked    = !!data.tiene_asistencia;
    document.getElementById('formActEvidencia').checked     = !!data.tiene_evidencia;
    document.getElementById('formActTieneEncuesta').checked = !!data.encuesta_id;

    if (data.encuesta_id) {
        const sel = document.getElementById('formActEncuesta');
        if (sel) sel.value = data.encuesta_id;
    }
    toggleEncuestaSelector();

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
    const duracion     = document.getElementById('formActDuracion').value.trim();
    const duracionMin  = document.getElementById('formActDuracionMin').value.trim();
    const desc         = document.getElementById('formActDescripcion').value.trim();
    const metodologia  = document.getElementById('formActMetodologia').value.trim();
    const tieneAsist   = document.getElementById('formActAsistencia').checked;
    const tieneEvid    = document.getElementById('formActEvidencia').checked;
    const tieneEnc     = document.getElementById('formActTieneEncuesta').checked;
    const encuestaId   = document.getElementById('formActEncuesta')?.value ?? '';

    // HU2 — Validar campos obligatorios
    if (!nombre) { mostrarAlertaForm('El nombre de la actividad es obligatorio.'); return; }
    if (!momentoId) { mostrarAlertaForm('Debes seleccionar un momento.'); return; }
    if (!objetivo) { mostrarAlertaForm('El objetivo pedagógico es obligatorio.'); return; }
    if (!duracion) { mostrarAlertaForm('La duración es obligatoria.'); return; }
    if (!duracionMin || parseInt(duracionMin) < 15) { mostrarAlertaForm('La duración en minutos debe ser al menos 15.'); return; }
    if (tieneEnc && !encuestaId) {
        mostrarAlertaForm('Debes seleccionar una encuesta activa del repositorio, o desactiva el componente encuesta.');
        return;
    }

    const btn = document.getElementById('btnGuardarActividad');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';

    const isEdit = id !== '';
    const url    = isEdit ? `${BASE_URL}/${id}` : BASE_URL;
    const method = isEdit ? 'PUT' : 'POST';

    try {
        const resp = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({
                nombre, momento_id: momentoId, objetivo, descripcion: desc,
                duracion, duracion_min: parseInt(duracionMin), metodologia,
                tiene_asistencia: tieneAsist, tiene_evidencia: tieneEvid,
                tiene_encuesta: tieneEnc, encuesta_id: tieneEnc ? encuestaId : null,
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
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
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
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
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
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
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
                    <div class="small text-muted mt-1">${data.total} calificaciones</div>
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
function toggleEncuestaSelector() {
    const wrap = document.getElementById('encuestaSelectorWrap');
    if (wrap) wrap.classList.toggle('d-none', !document.getElementById('formActTieneEncuesta').checked);
}

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
