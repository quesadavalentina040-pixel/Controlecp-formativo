@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    :root {
        --ecp-green: #39A900;
        --ecp-green-hover: #2e8800;
        --ecp-navy: #001A29;
        --ecp-navy-light: #00324D;
    }

    .momento-card {
        border: none;
        border-radius: 20px;
        background: #ffffff;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        overflow: hidden;
    }
    .momento-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 50, 77, 0.12);
    }

    .momento-header-1 {
        background: linear-gradient(135deg, #001A29 0%, #39A900 100%);
        color: #ffffff;
    }
    .momento-header-2 {
        background: linear-gradient(135deg, #002336 0%, #0070B8 100%);
        color: #ffffff;
    }
    .momento-header-3 {
        background: linear-gradient(135deg, #001A29 0%, #6A1B9A 100%);
        color: #ffffff;
    }

    .badge-actividades {
        font-size: 0.82rem;
        font-weight: 700;
        padding: 0.45rem 0.85rem;
        border-radius: 50rem;
    }

    .actividad-item {
        border-left: 4px solid #e0ece4;
        background-color: #f8faf9;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .actividad-item:hover {
        background-color: #f0fbf4;
        border-left-color: #39A900;
        transform: translateX(3px);
    }

    .stat-kpi-momento {
        border-radius: 16px;
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease;
    }
    .stat-kpi-momento:hover {
        transform: translateY(-2px);
    }

    .progress-momento {
        height: 8px;
        border-radius: 6px;
        background-color: #e8ece9;
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
                        <a href="{{ route('controlecp.administrador.inicio') }}" class="text-decoration-none" style="color: var(--ecp-green);">
                            <i class="fas fa-house me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page" style="color: var(--ecp-navy); font-weight: 600;">
                        Momentos
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: var(--ecp-navy); font-family: 'Outfit', sans-serif;">
                <i class="fas fa-star me-2" style="color: var(--ecp-green);"></i>Momentos Pedagógicos ECP
            </h2>
            <p class="text-muted small mb-0">Estructura de formación, talleres y experiencias pedagógicas de Escuela Cultura de Paz</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-outline-primary rounded-pill px-3 btn-sm" data-bs-toggle="modal" data-bs-target="#modalConsultarAvanceFicha">
                <i class="fas fa-magnifying-glass me-1"></i> Consultar Ficha
            </button>
            <a href="{{ route('controlecp.administrador.actividades') }}" class="btn text-white rounded-pill px-3 btn-sm shadow-sm" style="background-color: var(--ecp-green); border-color: var(--ecp-green);">
                <i class="fas fa-list-check me-1"></i> Ver Catálogo de Actividades
            </a>
            <a href="{{ route('controlecp.administrador.inicio') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver al Inicio
            </a>
        </div>
    </div>

    <!-- Banner Informativo -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 18px; background: linear-gradient(135deg, #f0fbf4 0%, #d8f3dc 100%); border-left: 6px solid var(--ecp-green) !important;">
        <div class="card-body p-4 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-white text-success d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 58px; height: 58px; font-size: 1.6rem; color: var(--ecp-green) !important;">
                    <i class="fas fa-shapes"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1" style="color: var(--ecp-navy);">Estructura del Programa ECP</h5>
                    <p class="mb-0 text-dark small" style="line-height: 1.45;">
                        El proceso formativo comprende <strong>3 Momentos Clave</strong> con un total de <strong>18 actividades pedagógicas</strong> orientadas al desarrollo personal, la convivencia armónica y la resolución pacífica de conflictos.
                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <span class="badge bg-white text-success border border-success-subtle px-3 py-2 rounded-pill shadow-xs" style="font-size: 0.8rem;">
                    <i class="fas fa-graduation-cap me-1"></i> {{ $statsMomentos['total_fichas'] ?? 115 }} Fichas en Ruta
                </span>
            </div>
        </div>
    </div>

    <!-- Tarjetas de Resumen de Impacto por Momento -->
    <div class="row g-3 mb-4">
        <!-- KPI 1: Momento 1 -->
        <div class="col-12 col-md-4">
            <div class="card stat-kpi-momento shadow-sm p-3 border-start border-4" style="border-left-color: #39A900 !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase">Momento 1: Autoconciencia</span>
                        <h4 class="fw-bold mb-0 text-dark mt-1">{{ $statsMomentos['m1_fichas'] ?? 52 }} Fichas</h4>
                        <small class="text-success"><i class="fas fa-users me-1"></i>{{ number_format($statsMomentos['m1_aprendices'] ?? 1200, 0, ',', '.') }} aprendices asignados</small>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: #e8f8e8; color: #39A900;">
                        <i class="fas fa-brain"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI 2: Momento 2 -->
        <div class="col-12 col-md-4">
            <div class="card stat-kpi-momento shadow-sm p-3 border-start border-4" style="border-left-color: #0070B8 !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase">Momento 2: Convivencia</span>
                        <h4 class="fw-bold mb-0 text-dark mt-1">{{ $statsMomentos['m2_fichas'] ?? 41 }} Fichas</h4>
                        <small class="text-primary"><i class="fas fa-users me-1"></i>{{ number_format($statsMomentos['m2_aprendices'] ?? 980, 0, ',', '.') }} aprendices asignados</small>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: #ebf5fb; color: #0070B8;">
                        <i class="fas fa-people-arrows"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI 3: Momento 3 -->
        <div class="col-12 col-md-4">
            <div class="card stat-kpi-momento shadow-sm p-3 border-start border-4" style="border-left-color: #6A1B9A !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase">Momento 3: Resolución</span>
                        <h4 class="fw-bold mb-0 text-dark mt-1">{{ $statsMomentos['m3_fichas'] ?? 22 }} Fichas</h4>
                        <small style="color: #6A1B9A;"><i class="fas fa-users me-1"></i>{{ number_format($statsMomentos['m3_aprendices'] ?? 540, 0, ',', '.') }} aprendices asignados</small>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: #f4ecf7; color: #6A1B9A;">
                        <i class="fas fa-scale-balanced"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grilla Principal de los 3 Momentos Pedagógicos -->
    <div class="row g-4 mb-4">

        <!-- MOMENTO 1: AUTOCONCIENCIA -->
        <div class="col-12 col-lg-4">
            <div class="card momento-card h-100">
                <div class="card-header momento-header-1 p-4 border-0">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-white text-success rounded-pill px-3 py-1 fw-bold text-uppercase small shadow-sm">
                            Momento 01
                        </span>
                        <span class="badge badge-actividades bg-white text-dark shadow-sm">
                            <i class="fas fa-tasks text-success me-1"></i> 10 Actividades
                        </span>
                    </div>
                    <h3 class="h4 fw-bold mb-2">Autoconciencia</h3>
                    <p class="mb-0 small text-white-50">
                        Introspección, reconocimiento de emociones, autorreflexión y construcción del proyecto de vida.
                    </p>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-muted text-uppercase mb-0 small" style="letter-spacing: 1px;">
                                <i class="fas fa-list-ol me-1 text-success"></i> Lista de Actividades (10)
                            </h6>
                            <span class="text-muted" style="font-size: 0.75rem;">Haz clic para ver guía</span>
                        </div>

                        <div class="d-flex flex-column gap-2 mb-3">
                            @php
                                $actividadesM1 = [
                                    ['num' => '01', 'nombre' => 'Reconocimiento y Gestión Emocional', 'duracion' => '2 Horas', 'obj' => 'Identificar la rueda de emociones y técnicas de autorregulación emocional.'],
                                    ['num' => '02', 'nombre' => 'Taller de Autorreflexión y Autoconcepto', 'duracion' => '2 Horas', 'obj' => 'Explorar la percepción personal, autoestima y fortalezas individuales.'],
                                    ['num' => '03', 'nombre' => 'Proyecto de Vida y Sueños Integrales', 'duracion' => '3 Horas', 'obj' => 'Diseñar metas académicas, laborales y personales a corto, mediano y largo plazo.'],
                                    ['num' => '04', 'nombre' => 'Identificación de Fortalezas y Oportunidades', 'duracion' => '2 Horas', 'obj' => 'Matriz DOFA personal aplicada al contexto de formación SENA.'],
                                    ['num' => '05', 'nombre' => 'Expresión Asertiva de Sentimientos', 'duracion' => '2 Horas', 'obj' => 'Comunicar emociones de manera clara, oportuna y sin agresividad.'],
                                    ['num' => '06', 'nombre' => 'Mapeo Corporal y Autocuidado', 'duracion' => '2 Horas', 'obj' => 'Reconocer somatizaciones del estrés y promover hábitos de salud mental.'],
                                    ['num' => '07', 'nombre' => 'Control del Estrés e Impulsos', 'duracion' => '2 Horas', 'obj' => 'Técnicas de respiración diafragmática, mindfulness y pausas activas.'],
                                    ['num' => '08', 'nombre' => 'Historia Personal y Raíces Culturales', 'duracion' => '2 Horas', 'obj' => 'Reconocer el valor de la identidad personal, familiar y cultural.'],
                                    ['num' => '09', 'nombre' => 'Principios, Valores y Ética Personal', 'duracion' => '2 Horas', 'obj' => 'Alinear los valores individuales con el decálogo ético del SENA.'],
                                    ['num' => '10', 'nombre' => 'Compromiso Conmigo Mismo', 'duracion' => '2 Horas', 'obj' => 'Firma del pacto de crecimiento y autorrealización formativa.']
                                ];
                            @endphp

                            @foreach($actividadesM1 as $act)
                                <div class="actividad-item p-2 px-3 rounded-3 small d-flex justify-content-between align-items-center"
                                     onclick="abrirModalGuiaActividad('Momento 1: Autoconciencia', '{{ $act['num'] }}. {{ $act['nombre'] }}', '{{ $act['duracion'] }}', '{{ $act['obj'] }}', 'Introspección guiada y dinámicas individuales', '#39A900')">
                                    <div>
                                        <span class="fw-bold text-success me-1">{{ $act['num'] }}.</span> {{ $act['nombre'] }}
                                    </div>
                                    <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">{{ $act['duracion'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="border-top pt-3 mt-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted fw-semibold">
                                <i class="fas fa-bullseye text-success me-1"></i> Fase inicial del programa
                            </span>
                            <span class="small fw-bold text-success">{{ $statsMomentos['m1_fichas'] ?? 52 }} Fichas</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-success w-100 rounded-pill"
                                onclick="abrirModalFichasMomento('Momento 1: Autoconciencia', 1)">
                            <i class="fas fa-id-card-clip me-1"></i> Ver Fichas en Momento 1
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MOMENTO 2: CONVIVENCIA -->
        <div class="col-12 col-lg-4">
            <div class="card momento-card h-100">
                <div class="card-header momento-header-2 p-4 border-0">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-white text-primary rounded-pill px-3 py-1 fw-bold text-uppercase small shadow-sm">
                            Momento 02
                        </span>
                        <span class="badge badge-actividades bg-white text-dark shadow-sm">
                            <i class="fas fa-tasks text-primary me-1"></i> 4 Actividades
                        </span>
                    </div>
                    <h3 class="h4 fw-bold mb-2">Convivencia</h3>
                    <p class="mb-0 small text-white-50">
                        Fomento de la empatía, trabajo en equipo, escucha activa y construcción comunitaria.
                    </p>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-muted text-uppercase mb-0 small" style="letter-spacing: 1px;">
                                <i class="fas fa-list-ol me-1 text-primary"></i> Lista de Actividades (4)
                            </h6>
                            <span class="text-muted" style="font-size: 0.75rem;">Haz clic para ver guía</span>
                        </div>

                        <div class="d-flex flex-column gap-2 mb-3">
                            @php
                                $actividadesM2 = [
                                    ['num' => '01', 'nombre' => 'Círculos de Diálogo y Empatía', 'duracion' => '3 Horas', 'obj' => 'Generar espacios de escucha activa, vulnerabilidad y comprensión recíproca.'],
                                    ['num' => '02', 'nombre' => 'Dinámicas de Trabajo Colaborativo', 'duracion' => '2 Horas', 'obj' => 'Fortalecer la confianza y sinergia en equipos de proyectos formativos.'],
                                    ['num' => '03', 'nombre' => 'Comunicación No Violenta (CNV)', 'duracion' => '3 Horas', 'obj' => 'Aprender el modelo de observación, sentimientos, necesidades y peticiones.'],
                                    ['num' => '04', 'nombre' => 'Acuerdos de Convivencia y Respeto', 'duracion' => '2 Horas', 'obj' => 'Construir pactos colectivos de paz y respeto para el ambiente de aprendizaje.']
                                ];
                            @endphp

                            @foreach($actividadesM2 as $act)
                                <div class="actividad-item p-2 px-3 rounded-3 small d-flex justify-content-between align-items-center"
                                     style="border-left-color: #0070B8;"
                                     onclick="abrirModalGuiaActividad('Momento 2: Convivencia', '{{ $act['num'] }}. {{ $act['nombre'] }}', '{{ $act['duracion'] }}', '{{ $act['obj'] }}', 'Talleres vivenciales y círculos comunitarios', '#0070B8')">
                                    <div>
                                        <span class="fw-bold text-primary me-1">{{ $act['num'] }}.</span> {{ $act['nombre'] }}
                                    </div>
                                    <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">{{ $act['duracion'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="border-top pt-3 mt-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted fw-semibold">
                                <i class="fas fa-users text-primary me-1"></i> Interacción y grupo
                            </span>
                            <span class="small fw-bold text-primary">{{ $statsMomentos['m2_fichas'] ?? 41 }} Fichas</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary w-100 rounded-pill"
                                onclick="abrirModalFichasMomento('Momento 2: Convivencia', 2)">
                            <i class="fas fa-id-card-clip me-1"></i> Ver Fichas en Momento 2
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MOMENTO 3: RESOLUCIÓN -->
        <div class="col-12 col-lg-4">
            <div class="card momento-card h-100">
                <div class="card-header momento-header-3 p-4 border-0">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-white text-purple rounded-pill px-3 py-1 fw-bold text-uppercase small shadow-sm" style="color: #6A1B9A !important;">
                            Momento 03
                        </span>
                        <span class="badge badge-actividades bg-white text-dark shadow-sm">
                            <i class="fas fa-tasks text-purple me-1" style="color: #6A1B9A;"></i> 4 Actividades
                        </span>
                    </div>
                    <h3 class="h4 fw-bold mb-2">Resolución</h3>
                    <p class="mb-0 small text-white-50">
                        Estrategias, herramientas y mediación pacífica para la gestión y transformación de conflictos.
                    </p>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-muted text-uppercase mb-0 small" style="letter-spacing: 1px;">
                                <i class="fas fa-list-ol me-1" style="color: #6A1B9A;"></i> Lista de Actividades (4)
                            </h6>
                            <span class="text-muted" style="font-size: 0.75rem;">Haz clic para ver guía</span>
                        </div>

                        <div class="d-flex flex-column gap-2 mb-3">
                            @php
                                $actividadesM3 = [
                                    ['num' => '01', 'nombre' => 'Taller de Mediación Pacífica', 'duracion' => '3 Horas', 'obj' => 'Herramientas prácticas para intervenir como mediadores neutrales en conflictos formativos.'],
                                    ['num' => '02', 'nombre' => 'Negociación y Estrategias Ganar-Ganar', 'duracion' => '3 Horas', 'obj' => 'Técnicas de negociación basada en intereses para llegar a acuerdos sostenibles.'],
                                    ['num' => '03', 'nombre' => 'Estudio de Casos y Juego de Roles', 'duracion' => '2 Horas', 'obj' => 'Simulación de situaciones reales de tensión en entornos laborales y comunitarios.'],
                                    ['num' => '04', 'nombre' => 'Plan de Acción y Pactos de Paz', 'duracion' => '3 Horas', 'obj' => 'Cierre del proceso formativo, evaluación integral y certificación de paz.']
                                ];
                            @endphp

                            @foreach($actividadesM3 as $act)
                                <div class="actividad-item p-2 px-3 rounded-3 small d-flex justify-content-between align-items-center"
                                     style="border-left-color: #6A1B9A;"
                                     onclick="abrirModalGuiaActividad('Momento 3: Resolución', '{{ $act['num'] }}. {{ $act['nombre'] }}', '{{ $act['duracion'] }}', '{{ $act['obj'] }}', 'Simulación, mediación y evaluación final', '#6A1B9A')">
                                    <div>
                                        <span class="fw-bold me-1" style="color: #6A1B9A;">{{ $act['num'] }}.</span> {{ $act['nombre'] }}
                                    </div>
                                    <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">{{ $act['duracion'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="border-top pt-3 mt-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted fw-semibold">
                                <i class="fas fa-scale-balanced me-1" style="color: #6A1B9A;"></i> Transformación del entorno
                            </span>
                            <span class="small fw-bold" style="color: #6A1B9A;">{{ $statsMomentos['m3_fichas'] ?? 22 }} Fichas</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 rounded-pill"
                                onclick="abrirModalFichasMomento('Momento 3: Resolución', 3)">
                            <i class="fas fa-id-card-clip me-1"></i> Ver Fichas en Momento 3
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- ========================================================================================= -->
<!-- MODALES INTERACTIVOS DE LOS MOMENTOS PEDAGÓGICOS                                          -->
<!-- ========================================================================================= -->

<!-- 1. Modal: Ficha Metodológica de Actividad -->
<div class="modal fade" id="modalGuiaActividad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <span class="badge rounded-pill px-3 py-1 shadow-xs" id="modalActMomentoBadge" style="background-color: var(--ecp-green); color: white;">
                    Momento
                </span>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <h5 class="fw-bold text-dark mb-1" id="modalActTitle">Nombre de la Actividad</h5>
                <div class="small text-muted mb-3 d-flex align-items-center gap-2">
                    <span><i class="far fa-clock me-1 text-success"></i> Duración: <strong id="modalActDuracion">2 Horas</strong></span>
                    &bull;
                    <span><i class="fas fa-award text-warning me-1"></i> Escuela Cultura de Paz</span>
                </div>

                <div class="p-3 rounded-3 bg-light border mb-3 small">
                    <strong class="text-dark d-block mb-1"><i class="fas fa-bullseye me-1 text-primary"></i> Objetivo Pedagógico:</strong>
                    <p class="mb-0 text-muted" id="modalActObj">Descripción del objetivo...</p>
                </div>

                <div class="p-3 rounded-3 bg-light border mb-3 small">
                    <strong class="text-dark d-block mb-1"><i class="fas fa-chalkboard-user me-1 text-success"></i> Metodología Aplicada:</strong>
                    <p class="mb-0 text-muted" id="modalActMetodo">Dinámicas y talleres...</p>
                </div>

                <div class="small text-muted">
                    <i class="fas fa-check-double text-success me-1"></i> Impacta la competencia transversal de <strong>Promover la interacción idónea consigo mismo, con los demás y con la naturaleza</strong>.
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 btn-sm" data-bs-dismiss="modal">Cerrar</button>
                <a href="{{ route('controlecp.administrador.cronograma') }}" class="btn text-white rounded-pill px-4 btn-sm" style="background-color: var(--ecp-green);">
                    <i class="fas fa-calendar-plus me-1"></i> Programar en Cronograma
                </a>
            </div>
        </div>
    </div>
</div>

<!-- 2. Modal: Fichas en el Momento Pedagógico -->
<div class="modal fade" id="modalFichasMomento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="modal-title fw-bold" style="color: var(--ecp-navy);" id="modalFichasMomentoTitle">
                        Fichas en Momento
                    </h5>
                    <p class="text-muted small mb-0">Cursos académicos activos del Centro La Angostura</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="ps-3 py-2">Ficha</th>
                                <th>Programa de Formación</th>
                                <th>Aprendices</th>
                                <th>Estado</th>
                                <th class="text-end pe-3">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="modalFichasMomentoTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 btn-sm" data-bs-dismiss="modal">Cerrar</button>
                <a href="{{ route('controlecp.administrador.fichas') }}" class="btn text-white rounded-pill px-4 btn-sm" style="background-color: var(--ecp-navy);">
                    <i class="fas fa-id-card me-1"></i> Ir a Gestión de Fichas
                </a>
            </div>
        </div>
    </div>
</div>

<!-- 3. Modal: Consultar Avance por Ficha -->
<div class="modal fade" id="modalConsultarAvanceFicha" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width: 42px; height: 42px; background: #e8f8e8; color: var(--ecp-green);">
                        <i class="fas fa-magnifying-glass"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold" style="color: var(--ecp-navy);">
                            Consultar Ruta de Paz de una Ficha
                        </h5>
                        <p class="text-muted small mb-0">Seleccione una ficha del centro para verificar su progreso</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-dark">Seleccionar Ficha Académica:</label>
                    <select class="form-select" id="selectConsultaFicha" onchange="mostrarProgresoFichaSeleccionada()">
                        <option value="">-- Seleccione una Ficha --</option>
                        @foreach($catalogoFichas as $f)
                            <option value="{{ $f->code }}" data-program="{{ $f->program ? $f->program->name : 'Formación SENA' }}" data-aprendices="{{ $f->apprentices_count }}">
                                {{ $f->code }} &bull; {{ $f->program ? $f->program->name : 'SENA' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="resultadoProgresoFicha" class="p-3 rounded-3 border bg-light d-none">
                    <h6 class="fw-bold text-dark mb-1" id="lblFichaCodigo">Ficha: --</h6>
                    <p class="small text-muted mb-3" id="lblFichaPrograma">Programa: --</p>

                    <!-- Avance en los 3 momentos -->
                    <div class="mb-2 small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-semibold">Momento 1: Autoconciencia</span>
                            <span class="text-success fw-bold">100% Completado</span>
                        </div>
                        <div class="progress progress-momento">
                            <div class="progress-bar bg-success" style="width: 100%;"></div>
                        </div>
                    </div>

                    <div class="mb-2 small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-semibold">Momento 2: Convivencia</span>
                            <span class="text-primary fw-bold">75% En curso</span>
                        </div>
                        <div class="progress progress-momento">
                            <div class="progress-bar bg-primary" style="width: 75%;"></div>
                        </div>
                    </div>

                    <div class="mb-3 small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-semibold">Momento 3: Resolución</span>
                            <span class="text-muted">Pendiente de inicio</span>
                        </div>
                        <div class="progress progress-momento">
                            <div class="progress-bar bg-secondary" style="width: 15%;"></div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <span class="small text-muted" id="lblFichaAprendices">-- Aprendices</span>
                        <a href="{{ route('controlecp.administrador.asistencia') }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
                            Ver Asistencia
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Fichas serializadas de backend
    const fichasM1 = @json($fichasMomento1 ?? []);
    const fichasM2 = @json($fichasMomento2 ?? []);
    const fichasM3 = @json($fichasMomento3 ?? []);

    // ── 1. Abrir Modal de Ficha Metodológica de Actividad ─────────────────────────────
    function abrirModalGuiaActividad(momento, titulo, duracion, objetivo, metodo, color) {
        document.getElementById('modalActMomentoBadge').innerText = momento;
        document.getElementById('modalActMomentoBadge').style.backgroundColor = color;
        document.getElementById('modalActTitle').innerText = titulo;
        document.getElementById('modalActDuracion').innerText = duracion;
        document.getElementById('modalActObj').innerText = objetivo;
        document.getElementById('modalActMetodo').innerText = metodo;

        const modal = new bootstrap.Modal(document.getElementById('modalGuiaActividad'));
        modal.show();
    }

    // ── 2. Abrir Modal de Fichas en un Momento Específico ─────────────────────────────
    function abrirModalFichasMomento(titulo, momentoNum) {
        document.getElementById('modalFichasMomentoTitle').innerText = titulo;
        const tbody = document.getElementById('modalFichasMomentoTableBody');
        tbody.innerHTML = '';

        let fichas = [];
        let badgeColor = 'success';
        if (momentoNum === 1) {
            fichas = fichasM1;
            badgeColor = 'success';
        } else if (momentoNum === 2) {
            fichas = fichasM2;
            badgeColor = 'primary';
        } else {
            fichas = fichasM3;
            badgeColor = 'secondary';
        }

        if (fichas && fichas.length > 0) {
            let rows = '';
            fichas.forEach(f => {
                const progName = f.program ? f.program.name : 'PROGRAMA EN FORMACIÓN';
                const count = f.apprentices_count || 0;
                rows += `
                    <tr>
                        <td class="ps-3">
                            <span class="badge bg-light text-dark border fw-bold px-2 py-1">
                                <i class="fas fa-id-card text-success me-1"></i>${f.code}
                            </span>
                        </td>
                        <td>
                            <strong class="text-dark small">${progName}</strong>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-users me-1 text-primary"></i>${count}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-${badgeColor}-subtle text-${badgeColor} border border-${badgeColor}-subtle rounded-pill">
                                En formación
                            </span>
                        </td>
                        <td class="text-end pe-3">
                            <a href="{{ route('controlecp.administrador.fichas') }}?search=${f.code}" class="btn btn-sm btn-light rounded-pill px-2" title="Consultar ficha">
                                <i class="fas fa-arrow-right text-muted"></i>
                            </a>
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = rows;
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted small">No hay fichas asignadas a este momento en este momento.</td></tr>';
        }

        const modal = new bootstrap.Modal(document.getElementById('modalFichasMomento'));
        modal.show();
    }

    // ── 3. Mostrar Progreso de Ficha Seleccionada ──────────────────────────────────────
    function mostrarProgresoFichaSeleccionada() {
        const select = document.getElementById('selectConsultaFicha');
        const container = document.getElementById('resultadoProgresoFicha');

        if (!select.value) {
            container.classList.add('d-none');
            return;
        }

        const opt = select.selectedOptions[0];
        document.getElementById('lblFichaCodigo').innerText = 'Ficha: ' + select.value;
        document.getElementById('lblFichaPrograma').innerText = 'Programa: ' + opt.dataset.program;
        document.getElementById('lblFichaAprendices').innerText = (opt.dataset.aprendices || 0) + ' Aprendices matriculados';

        container.classList.remove('d-none');
    }
</script>
@endpush
@endsection
