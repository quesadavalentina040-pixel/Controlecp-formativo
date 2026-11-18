
@extends('controlecp::components.layouts.dashboard')

@push('styles')

<style>
    .tabulacion-instructor {
        --verde-ecp: #39A900;
        --verde-ecp-hover: #2e8b00;
        --verde-ecp-claro: #d8f3dc;
        --verde-ecp-suave: #f0fbf4;
        --petroleo-ecp: #001A29;
        --petroleo-ecp-medio: #00324D;
        --azul-ecp-claro: #d0e8f5;
        --gris-ecp-texto: #52796f;
        --gris-ecp-borde: #e2e8f0;
    }

    .tabulacion-instructor .tabulacion-card {
        background: #ffffff;
        border: 1px solid var(--gris-ecp-borde);
        border-radius: 18px;
        box-shadow: 0 4px 20px rgba(0, 26, 41, 0.05);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    /* Botones primarios y secundarios */
    .tabulacion-instructor .btn-ecp-primary {
        background-color: var(--verde-ecp);
        color: #ffffff !important;
        border: 1px solid var(--verde-ecp);
        font-weight: 600;
        border-radius: 50rem;
        padding: 0.55rem 1.35rem;
        transition: all .2s ease;
    }
    .tabulacion-instructor .btn-ecp-primary:hover,
    .tabulacion-instructor .btn-ecp-primary:focus-visible {
        background-color: var(--verde-ecp-hover);
        border-color: var(--verde-ecp-hover);
        box-shadow: 0 4px 14px rgba(57, 169, 0, 0.35);
        transform: translateY(-1px);
    }

    .tabulacion-instructor .btn-ecp-navy {
        background-color: var(--petroleo-ecp);
        color: #ffffff !important;
        border: 1px solid var(--petroleo-ecp);
        font-weight: 600;
        border-radius: 50rem;
        padding: 0.55rem 1.35rem;
        transition: all .2s ease;
    }
    .tabulacion-instructor .btn-ecp-navy:hover {
        background-color: var(--petroleo-ecp-medio);
        border-color: var(--petroleo-ecp-medio);
        box-shadow: 0 4px 14px rgba(0, 26, 41, 0.25);
        transform: translateY(-1px);
    }

    .tabulacion-instructor .tabulacion-card form .btn-outline-secondary {
        background-color: #ffffff;
        color: var(--petroleo-ecp);
        border-color: #ffffff;
    }

    .tabulacion-instructor .tabulacion-card form .btn-outline-secondary:hover {
        background-color: #f1f5f9;
        color: var(--petroleo-ecp);
        border-color: #ffffff;
    }

    /* Tarjetas de Indicadores */
    .tabulacion-instructor .indicator-card {
        background: #ffffff;
        border: 1px solid var(--gris-ecp-borde);
        border-radius: 16px;
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        box-shadow: 0 2px 12px rgba(0, 26, 41, 0.04);
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .tabulacion-instructor .indicator-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 26, 41, 0.08);
    }
    .tabulacion-instructor .indicator-icon-circle {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        flex-shrink: 0;
    }

    /* Barras Horizontales con CSS Puro */
    .tabulacion-instructor .prog-bar-container {
        width: 100%;
        height: 18px;
        background-color: #f1f5f9;
        border-radius: 50rem;
        overflow: hidden;
        position: relative;
    }
    .tabulacion-instructor .prog-bar-fill-green {
        height: 100%;
        background: linear-gradient(90deg, #39A900 0%, #48c800 100%);
        border-radius: 50rem;
        transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .tabulacion-instructor .prog-bar-fill-blue {
        height: 100%;
        background: linear-gradient(90deg, #0284c7 0%, #38bdf8 100%);
        border-radius: 50rem;
        transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .tabulacion-instructor .prog-bar-fill-amber {
        height: 100%;
        background: linear-gradient(90deg, #d97706 0%, #fbbf24 100%);
        border-radius: 50rem;
        transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Pestañas de Navegación por Actividad */
    .tabulacion-instructor .nav-tabs-ecp {
        border-bottom: 2px solid #e2e8f0;
        gap: 0.5rem;
    }
    .tabulacion-instructor .nav-tabs-ecp .nav-link {
        border: none;
        color: #64748b;
        font-weight: 600;
        padding: 0.75rem 1.25rem;
        border-radius: 12px 12px 0 0;
        transition: all .2s ease;
    }
    .tabulacion-instructor .nav-tabs-ecp .nav-link:hover {
        color: var(--petroleo-ecp);
        background-color: #f8fafc;
    }
    .tabulacion-instructor .nav-tabs-ecp .nav-link.active {
        color: var(--petroleo-ecp);
        background-color: #ffffff;
        border-bottom: 3px solid var(--verde-ecp);
    }

    /* Item de Respuesta de Texto */
    .tabulacion-instructor .item-respuesta-texto {
        background-color: #f8fafc;
        border-left: 3.5px solid var(--verde-ecp);
        border-radius: 0 10px 10px 0;
        padding: 0.85rem 1.15rem;
        margin-bottom: 0.75rem;
        transition: background-color .15s ease;
    }
    .tabulacion-instructor .item-respuesta-texto:hover {
        background-color: #f1f5f9;
    }

    .tabulacion-instructor .tabulacion-header-card {
        background-color: var(--petroleo-ecp);
        border-radius: 24px;
        padding: 1.25rem 1.5rem;
    }

    .tabulacion-instructor .tabulacion-header-card h2 {
        color: #ffffff !important;
    }

    .tabulacion-instructor .tabulacion-header-card p {
        color: #c7d4da !important;
    }

    .tabulacion-instructor .tabulacion-header-card .btn-outline-secondary {
        color: #ffffff;
        border-color: rgba(255, 255, 255, .75);
    }

    .tabulacion-instructor .tabulacion-filter-toolbar {
        padding: 12px;
        background: var(--petroleo-ecp);
        border-color: var(--petroleo-ecp);
        border-radius: 18px;
    }

    .tabulacion-instructor .tabulacion-filter-toolbar label {
        color: #ffffff !important;
    }

    .tabulacion-instructor .tabulacion-filter-toolbar .form-select,
    .tabulacion-instructor .tabulacion-filter-toolbar .form-control,
    .tabulacion-instructor .tabulacion-filter-toolbar .btn {
        min-height: 44px;
        border-radius: 10px !important;
    }

    .tabulacion-instructor .tabulacion-filter-toolbar .tabulacion-option-search {
        min-height: 44px;
        font-size: .88rem;
    }

    .tabulacion-instructor .tabulacion-filter-toolbar .btn-ecp-primary {
        flex: 1;
        padding: .55rem .8rem;
    }

    .tabulacion-instructor .tabulacion-filter-toolbar .btn-outline-secondary {
        flex: 0 0 52px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    @media (min-width: 992px) {
        .tabulacion-instructor .tabulacion-filter-toolbar .tabulacion-filter-row {
            display: grid;
            grid-template-columns:
                minmax(190px, 1.5fr)
                minmax(155px, 1.2fr)
                minmax(140px, 1.1fr)
                minmax(105px, .8fr)
                minmax(105px, .8fr)
                minmax(150px, 1.2fr);
            gap: 12px;
            margin: 0;
        }

        .tabulacion-instructor .tabulacion-filter-toolbar .tabulacion-filter-row > [class*="col-"] {
            width: auto;
            padding: 0;
        }
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid tabulacion-instructor px-3 py-3">

    <div class="tabulacion-header-card d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="small fw-semibold mb-1" style="color: var(--verde-ecp);">
                <i class="fas fa-chart-column me-2"></i>Control ECP · Panel de instructor
            </div>
            <h2 class="h3 fw-bold mb-1">
                Tabulación y Estadísticas de Encuestas
            </h2>
            <p class="text-muted mb-0" style="font-size: 0.92rem;">
                Consulta analítica en tiempo real de respuestas, tasa de participación, satisfacción y distribución por pregunta.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('controlecp.instructor.repositorio.encuestas') }}"
               class="btn btn-outline-light rounded-pill px-3">
                <i class="fas fa-arrow-left me-1"></i>Mis encuestas
            </a>
            @if($resultados && $resultados['consolidado']['total_respuestas'] > 0)
                <a href="{{ route('controlecp.instructor.repositorio.tabulacion.exportar', array_merge(request()->query(), ['id_encuesta' => $encuestaActual->id_encuesta])) }}"
                   class="btn btn-ecp-primary text-decoration-none">
                    <i class="fa-solid fa-file-pdf me-2"></i>Exportar PDF
                </a>
            @endif
        </div>
    </div>

    <div class="tabulacion-card tabulacion-filter-toolbar mb-4">

        <form method="GET" action="{{ route('controlecp.instructor.repositorio.tabulacion') }}" id="formFiltrosTabulacion">
            <div class="row g-3 align-items-end tabulacion-filter-row">

                <div class="col-12 col-md-4 col-lg-3">
                    <label for="selectEncuesta" class="form-label fw-bold mb-1" style="font-size: 0.88rem;">
                        <i class="fas fa-square-poll-vertical me-1"></i>Seleccionar encuesta
                    </label>
                    <select name="id_encuesta" id="selectEncuesta" class="form-select border-success" onchange="cambiarEncuesta(this.form)">
                        @forelse($encuestas as $enc)
                            <option value="{{ $enc->id_encuesta }}" {{ $encuestaActual?->id_encuesta == $enc->id_encuesta ? 'selected' : '' }}>
                                {{ $enc->nombre }} ({{ $enc->estado }})
                            </option>
                        @empty
                            <option value="" disabled>No hay encuestas disponibles</option>
                        @endforelse
                    </select>
                </div>

                <div class="col-12 col-md-4 col-lg-2">
                    <label for="filtroActividad" class="form-label fw-semibold mb-1" style="font-size: 0.85rem;">
                        Actividad Formativa:
                    </label>
                    <input type="search"
                           id="buscarActividad"
                           class="form-control tabulacion-option-search"
                           data-filter-options="opcionesActividades"
                           data-filter-value="filtroActividad"
                           aria-label="Buscar actividad formativa"
                           placeholder="Buscar actividad..."
                           list="opcionesActividades"
                           autocomplete="off">
                    <datalist id="opcionesActividades">
                        @foreach($filtrosDisponibles['actividades'] as $act)
                            <option data-id="{{ $act->id }}" value="{{ $act->numero ? "{$act->numero}. " : '' }}{{ $act->nombre }}"></option>
                        @endforeach
                    </datalist>
                    <input type="hidden" name="id_actividad" id="filtroActividad" value="{{ request('id_actividad') }}">
                </div>

                <div class="col-12 col-md-4 col-lg-2">
                    <label for="filtroFicha" class="form-label fw-semibold mb-1" style="font-size: 0.85rem;">
                        Ficha:
                    </label>
                    <input type="search"
                           id="buscarFicha"
                           class="form-control tabulacion-option-search"
                           data-filter-options="opcionesFichas"
                           data-filter-value="filtroFicha"
                           aria-label="Buscar ficha"
                           placeholder="Buscar ficha..."
                           list="opcionesFichas"
                           autocomplete="off">
                    <datalist id="opcionesFichas">
                        @foreach($filtrosDisponibles['fichas'] as $f)
                            <option data-id="{{ $f->id }}" value="{{ $f->code }}"></option>
                        @endforeach
                    </datalist>
                    <input type="hidden" name="id_ficha" id="filtroFicha" value="{{ request('id_ficha') }}">
                </div>

                <div class="col-6 col-md-3 col-lg-1">
                    <label for="filtroFechaDesde" class="form-label fw-semibold mb-1" style="font-size: 0.85rem;">
                        Desde:
                    </label>
                    <input type="date"
                           name="fecha_desde"
                           id="filtroFechaDesde"
                           class="form-control"
                           value="{{ request('fecha_desde') }}">

                </div>

                <div class="col-6 col-md-3 col-lg-1">
                    <label for="filtroFechaHasta" class="form-label fw-semibold mb-1" style="font-size: 0.85rem;">
                        Hasta:
                    </label>
                    <input type="date"
                           name="fecha_hasta"
                           id="filtroFechaHasta"
                           class="form-control"
                           value="{{ request('fecha_hasta') }}">

                </div>

                <div class="col-12 col-md-6 col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-ecp-primary flex-grow-1 px-2 py-2" title="Aplicar filtros">
                        <i class="fas fa-search me-1"></i> Buscar
                    </button>
                    <a href="{{ route('controlecp.instructor.repositorio.tabulacion', ['id_encuesta' => $encuestaActual?->id_encuesta]) }}"
                       class="btn btn-outline-secondary px-2 py-2 text-decoration-none"
                       title="Limpiar filtros"
                       aria-label="Limpiar filtros">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>

            </div>

            @if(request('id_actividad') || request('id_ficha') || request('fecha_desde') || request('fecha_hasta'))
                <div class="d-flex flex-wrap align-items-center gap-2 mt-3 pt-2 border-top" style="font-size: 0.82rem;">
                    <span class="text-muted fw-semibold"><i class="fa-solid fa-filter me-1"></i> Filtros aplicados:</span>
                    @if(request('id_actividad'))
                        @php $actSel = $filtrosDisponibles['actividades']->firstWhere('id', request('id_actividad')); @endphp
                        <span class="badge bg-light text-dark border">
                            Actividad: {{ $actSel?->nombre ?? request('id_actividad') }}
                        </span>
                    @endif
                    @if(request('id_ficha'))
                        @php $fichaSel = $filtrosDisponibles['fichas']->firstWhere('id', request('id_ficha')); @endphp
                        <span class="badge bg-light text-dark border">
                            Ficha: {{ $fichaSel?->code ?? request('id_ficha') }}
                        </span>
                    @endif
                    @if(request('fecha_desde') || request('fecha_hasta'))
                        <span class="badge bg-light text-dark border">
                            Fecha: {{ request('fecha_desde') ?? 'Inicio' }} al {{ request('fecha_hasta') ?? 'Hoy' }}
                        </span>
                    @endif
                </div>
            @endif
        </form>
    </div>

    @if(!$encuestaActual)
        <div class="tabulacion-card text-center py-5">
            <i class="fa-solid fa-square-poll-vertical fs-1 text-muted opacity-50 mb-3 d-block"></i>
            <h5 class="text-dark">No hay encuestas disponibles para tabular</h5>
            <p class="text-muted small">Cuando una encuesta esté vinculada a una actividad de tus cronogramas, podrás consultar aquí sus resultados.</p>
        </div>
    @elseif($resultados['consolidado']['total_respuestas'] === 0)

        <div class="tabulacion-card text-center py-5">
            <div class="py-4">
                <i class="fa-solid fa-clipboard-question fs-1 text-muted opacity-50 mb-3 d-block"></i>
                <h4 class="fw-bold text-dark">Sin respuestas registradas</h4>
                <p class="text-muted small mx-auto" style="max-width: 520px;">
                    La encuesta <strong>"{{ $encuestaActual->nombre }}"</strong> no tiene respuestas almacenadas en el sistema para los filtros seleccionados.
                </p>
                @if(request('id_actividad') || request('id_ficha') || request('fecha_desde') || request('fecha_hasta'))
                    <a href="{{ route('controlecp.instructor.repositorio.tabulacion', ['id_encuesta' => $encuestaActual->id_encuesta]) }}" class="btn btn-ecp-primary btn-sm mt-2">
                        <i class="fa-solid fa-rotate-left me-1"></i> Restablecer Filtros
                    </a>
                @endif
            </div>
        </div>
    @else

        @php // Inicia un bloque PHP para preparar datos que usará la vista.
            $consolidado = $resultados['consolidado'];
            $porActividad = $resultados['por_actividad'];
            $tieneMultiples = $resultados['tiene_multiples_actividades'] && !empty($porActividad);
        @endphp

        @if($tieneMultiples)
            <ul class="nav nav-tabs nav-tabs-ecp mb-4" id="tabsActividades" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-consolidado-tab" data-bs-toggle="tab" data-bs-target="#tab-consolidado" type="button" role="tab">
                        <i class="fa-solid fa-layer-group me-1"></i> Consolidado (Todas)
                    </button>
                </li>
                @foreach($porActividad as $idx => $item)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-act-{{ $item['actividad']->id }}-tab" data-bs-toggle="tab" data-bs-target="#tab-act-{{ $item['actividad']->id }}" type="button" role="tab">
                            <i class="fa-solid fa-cubes me-1"></i> {{ $item['actividad']->nombre }}
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="tab-content" id="tabContentActividades">

            <div class="tab-pane fade show active" id="tab-consolidado" role="tabpanel">

                <div class="row g-3 mb-4">

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="indicator-card">
                            <div class="indicator-icon-circle" style="background-color: #e0f2fe; color: #0284c7;">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <div>
                                <span class="d-block text-muted" style="font-size: 0.8rem; font-weight: 600;">RESPUESTAS RECIBIDAS</span>
                                <h3 class="fw-bold mb-0" style="color: var(--petroleo-ecp);">{{ $consolidado['total_respuestas'] }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="indicator-card">
                            <div class="indicator-icon-circle" style="background-color: #ede9fe; color: #7c3aed;">
                                <i class="fa-solid fa-list-check"></i>
                            </div>
                            <div>
                                <span class="d-block text-muted" style="font-size: 0.8rem; font-weight: 600;">PREGUNTAS EVALUADAS</span>
                                <h3 class="fw-bold mb-0" style="color: var(--petroleo-ecp);">{{ $consolidado['preguntas_evaluadas'] }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="indicator-card">
                            <div class="indicator-icon-circle" style="background-color: var(--verde-ecp-claro); color: var(--verde-ecp);">
                                <i class="fa-solid fa-chart-pie"></i>
                            </div>
                            <div>
                                <span class="d-block text-muted" style="font-size: 0.8rem; font-weight: 600;">TASA DE PARTICIPACIÓN</span>
                                <h3 class="fw-bold mb-0" style="color: var(--verde-ecp);">{{ $consolidado['tasa_participacion'] }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="indicator-card">
                            <div class="indicator-icon-circle" style="background-color: #fef3c7; color: #d97706;">
                                <i class="fa-solid fa-star"></i>
                            </div>
                            <div>
                                <span class="d-block text-muted" style="font-size: 0.8rem; font-weight: 600;">SATISFACCIÓN PROMEDIO</span>
                                <div class="d-flex align-items-baseline gap-2">
                                    <h3 class="fw-bold mb-0" style="color: #d97706;">{{ $consolidado['satisfaccion_promedio'] }}</h3>
                                    @if($consolidado['satisfaccion_porcentaje'] !== null)
                                        <span class="badge bg-warning-subtle text-warning-emphasis border" style="font-size: 0.75rem;">
                                            {{ $consolidado['satisfaccion_porcentaje'] }}%
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="fa-solid fa-chart-simple text-success me-2"></i>Resultados Detallados por Pregunta
                    </h5>
                    <span class="text-muted small">Total preguntas en encuesta: {{ count($consolidado['preguntas']) }}</span>
                </div>

                <div class="row g-4">
                    @foreach($consolidado['preguntas'] as $idx => $preg)
                        <div class="col-12 {{ ($preg['tipo'] === 'Texto') ? 'col-12' : 'col-lg-6' }}">
                            <div class="tabulacion-card p-4 h-100">

                                <div class="d-flex align-items-start justify-content-between gap-2 mb-3 pb-2 border-bottom">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="badge bg-dark rounded-pill px-2.5 py-1" style="font-size: 0.75rem;">
                                                P{{ $preg['orden'] ?? ($idx + 1) }}
                                            </span>
                                            <span class="badge bg-light text-muted border" style="font-size: 0.72rem;">
                                                {{ str_replace('_', ' ', $preg['tipo']) }}
                                            </span>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-0" style="font-size: 1rem; line-height: 1.35;">
                                            {{ $preg['texto'] }}
                                        </h6>
                                    </div>
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1 small" title="Respuestas recibidas en esta pregunta">
                                        {{ $preg['cantidad_respuestas'] }} resp.
                                    </span>
                                </div>

                                @if($preg['tipo'] === 'Opcion_multiple')
                                    <div class="d-flex flex-column gap-3">
                                        @foreach($preg['opciones'] as $opc)
                                            <div>
                                                <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.88rem;">
                                                    <span class="fw-semibold text-dark">{{ $opc['texto'] }}</span>
                                                    <span class="text-muted small">
                                                        <strong class="text-dark">{{ $opc['total'] }}</strong> ({{ $opc['porcentaje'] }}%)
                                                    </span>
                                                </div>
                                                <div class="prog-bar-container">
                                                    <div class="prog-bar-fill-green" style="width: {{ $opc['porcentaje'] }}%;"></div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                @elseif($preg['tipo'] === 'Escala')
                                    @if($preg['promedio_escala'] !== null)
                                        <div class="p-2 mb-3 rounded-3 text-center" style="background-color: var(--verde-ecp-suave); border: 1px solid var(--verde-ecp-claro);">
                                            <span class="text-muted small">Promedio de la pregunta:</span>
                                            <span class="fw-bold fs-5 text-success ms-1">{{ number_format($preg['promedio_escala'], 1) }} / 5</span>
                                        </div>
                                    @endif

                                    <div class="d-flex flex-column gap-2.5">
                                        @for($i = 1; $i <= 5; $i++)
                                            @php $itEscala = $preg['escala'][$i] ?? ['total' => 0, 'porcentaje' => 0]; @endphp

                                            <div>
                                                <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.85rem;">
                                                    <span class="fw-semibold text-dark">
                                                        {{ $i }} ★
                                                    </span>
                                                    <span class="text-muted small">
                                                        <strong class="text-dark">{{ $itEscala['total'] }}</strong> ({{ $itEscala['porcentaje'] }}%)
                                                    </span>
                                                </div>
                                                <div class="prog-bar-container">
                                                    <div class="prog-bar-fill-blue" style="width: {{ $itEscala['porcentaje'] }}%;"></div>
                                                </div>
                                            </div>
                                        @endfor
                                    </div>

                                @elseif($preg['tipo'] === 'Texto')
                                    @if(count($preg['respuestas_texto']) > 0)
                                        <div class="d-flex flex-column" id="contenedorTextosP{{ $preg['id_pregunta'] }}">
                                            @foreach($preg['respuestas_texto'] as $tIdx => $txt)
                                                <div class="item-respuesta-texto {{ $tIdx >= 5 ? 'd-none texto-oculto-p' . $preg['id_pregunta'] : '' }}">
                                                    <div class="text-dark" style="font-size: 0.92rem; line-height: 1.4;">
                                                        "{{ $txt['texto'] }}"
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center mt-2 small text-muted" style="font-size: 0.78rem;">
                                                        <span><i class="fa-regular fa-user me-1"></i> {{ $txt['autor'] }}</span>
                                                        <span><i class="fa-regular fa-clock me-1"></i> {{ $txt['fecha'] }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        @if(count($preg['respuestas_texto']) > 5)
                                            <div class="text-center mt-2">
                                                <button type="button"
                                                        class="btn btn-outline-secondary btn-sm rounded-pill"
                                                        onclick="toggleRespuestasTexto({{ $preg['id_pregunta'] }}, this)"
                                                        data-expandido="false">

                                                    <i class="fa-solid fa-chevron-down me-1"></i> Ver todas las respuestas ({{ count($preg['respuestas_texto']) }})
                                                </button>
                                            </div>
                                        @endif
                                    @else
                                        <div class="text-muted text-center py-4" style="font-style: italic;">
                                            No se han recibido respuestas de texto en esta pregunta.
                                        </div>
                                    @endif
                                @endif

                            </div>
                        </div>
                    @endforeach
                </div>

            </div>

            @if($tieneMultiples)
                @foreach($porActividad as $item)
                    @php // Inicia un bloque PHP para preparar datos que usará la vista.
                        $act = $item['actividad'];
                        $actDatos = $item['datos'];
                    @endphp
                    <div class="tab-pane fade" id="tab-act-{{ $act->id }}" role="tabpanel">

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-sm-6 col-xl-3">
                                <div class="indicator-card">
                                    <div class="indicator-icon-circle" style="background-color: #e0f2fe; color: #0284c7;">
                                        <i class="fa-solid fa-users"></i>
                                    </div>
                                    <div>
                                        <span class="d-block text-muted" style="font-size: 0.8rem; font-weight: 600;">RESPUESTAS (ACTIVIDAD)</span>
                                        <h3 class="fw-bold mb-0" style="color: var(--petroleo-ecp);">{{ $actDatos['total_respuestas'] }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-xl-3">
                                <div class="indicator-card">
                                    <div class="indicator-icon-circle" style="background-color: #ede9fe; color: #7c3aed;">
                                        <i class="fa-solid fa-list-check"></i>
                                    </div>
                                    <div>
                                        <span class="d-block text-muted" style="font-size: 0.8rem; font-weight: 600;">PREGUNTAS EVALUADAS</span>
                                        <h3 class="fw-bold mb-0" style="color: var(--petroleo-ecp);">{{ $actDatos['preguntas_evaluadas'] }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-xl-3">
                                <div class="indicator-card">
                                    <div class="indicator-icon-circle" style="background-color: var(--verde-ecp-claro); color: var(--verde-ecp);">
                                        <i class="fa-solid fa-chart-pie"></i>
                                    </div>
                                    <div>
                                        <span class="d-block text-muted" style="font-size: 0.8rem; font-weight: 600;">TASA DE PARTICIPACIÓN</span>
                                        <h3 class="fw-bold mb-0" style="color: var(--verde-ecp);">{{ $actDatos['tasa_participacion'] }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-xl-3">
                                <div class="indicator-card">
                                    <div class="indicator-icon-circle" style="background-color: #fef3c7; color: #d97706;">
                                        <i class="fa-solid fa-star"></i>
                                    </div>
                                    <div>
                                        <span class="d-block text-muted" style="font-size: 0.8rem; font-weight: 600;">SATISFACCIÓN (ACTIVIDAD)</span>
                                        <div class="d-flex align-items-baseline gap-2">
                                            <h3 class="fw-bold mb-0" style="color: #d97706;">{{ $actDatos['satisfaccion_promedio'] }}</h3>
                                            @if($actDatos['satisfaccion_porcentaje'] !== null)
                                                <span class="badge bg-warning-subtle text-warning-emphasis border" style="font-size: 0.75rem;">
                                                    {{ $actDatos['satisfaccion_porcentaje'] }}%
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($actDatos['total_respuestas'] === 0)
                            <div class="tabulacion-card text-center text-muted py-5">
                                <i class="fa-solid fa-clipboard-question fs-2 opacity-50 mb-2 d-block"></i>
                                Sin respuestas registradas para esta actividad con los filtros seleccionados.
                            </div>
                        @else

                        <div class="row g-4">
                            @foreach($actDatos['preguntas'] as $idxA => $pregA)
                                <div class="col-12 {{ ($pregA['tipo'] === 'Texto') ? 'col-12' : 'col-lg-6' }}">
                                    <div class="tabulacion-card p-4 h-100">
                                        <div class="d-flex align-items-start justify-content-between gap-2 mb-3 pb-2 border-bottom">
                                            <div>
                                                <span class="badge bg-dark rounded-pill px-2 py-0.5" style="font-size: 0.75rem;">
                                                    P{{ $pregA['orden'] ?? ($idxA + 1) }}
                                                </span>
                                                <span class="badge bg-light text-muted border" style="font-size: 0.72rem;">
                                                    {{ str_replace('_', ' ', $pregA['tipo']) }}
                                                </span>
                                                <h6 class="fw-bold text-dark mt-1 mb-0">{{ $pregA['texto'] }}</h6>
                                            </div>
                                            <span class="badge bg-light text-muted border">{{ $pregA['cantidad_respuestas'] }} resp.</span>
                                        </div>

                                        @if($pregA['tipo'] === 'Opcion_multiple')
                                            <div class="d-flex flex-column gap-3">
                                                @foreach($pregA['opciones'] as $opcA)
                                                    <div>
                                                        <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.88rem;">
                                                            <span class="fw-semibold text-dark">{{ $opcA['texto'] }}</span>
                                                            <span class="text-muted small"><strong>{{ $opcA['total'] }}</strong> ({{ $opcA['porcentaje'] }}%)</span>
                                                        </div>
                                                        <div class="prog-bar-container">
                                                            <div class="prog-bar-fill-green" style="width: {{ $opcA['porcentaje'] }}%;"></div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @elseif($pregA['tipo'] === 'Escala')
                                            @if($pregA['promedio_escala'] !== null)
                                                <div class="p-2 mb-3 rounded-3 text-center bg-light border">
                                                    <span class="text-muted small">Promedio:</span>
                                                    <span class="fw-bold fs-5 text-success ms-1">{{ number_format($pregA['promedio_escala'], 1) }} / 5</span>
                                                </div>
                                            @endif
                                            <div class="d-flex flex-column gap-2.5">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @php $itEscalaA = $pregA['escala'][$i] ?? ['total' => 0, 'porcentaje' => 0]; @endphp

                                                    <div>
                                                        <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.85rem;">
                                                            <span class="fw-semibold text-dark">{{ $i }} ★</span>
                                                            <span class="text-muted small"><strong>{{ $itEscalaA['total'] }}</strong> ({{ $itEscalaA['porcentaje'] }}%)</span>
                                                        </div>
                                                        <div class="prog-bar-container">
                                                            <div class="prog-bar-fill-blue" style="width: {{ $itEscalaA['porcentaje'] }}%;"></div>
                                                        </div>
                                                    </div>
                                                @endfor
                                            </div>
                                        @elseif($pregA['tipo'] === 'Texto')
                                            <div class="d-flex flex-column">
                                                @forelse($pregA['respuestas_texto'] as $txtIdxA => $txtA)
                                                    <div class="item-respuesta-texto {{ $txtIdxA >= 5 ? 'd-none texto-oculto-pact' . $act->id . '-' . $pregA['id_pregunta'] : '' }}">
                                                        <div class="text-dark">"{{ $txtA['texto'] }}"</div>
                                                        <div class="small text-muted mt-1">{{ $txtA['autor'] }} &bull; {{ $txtA['fecha'] }}</div>
                                                    </div>
                                                @empty
                                                    <div class="text-muted text-center py-3">Sin respuestas de texto en esta actividad.</div>
                                                @endforelse
                                            </div>
                                            @if(count($pregA['respuestas_texto']) > 5)
                                                <div class="text-center mt-2">
                                                    <button type="button"
                                                            class="btn btn-outline-secondary btn-sm rounded-pill"
                                                            onclick="toggleRespuestasTexto('act{{ $act->id }}-p{{ $pregA['id_pregunta'] }}', this)"

                                                            data-expandido="false">
                                                        <i class="fa-solid fa-chevron-down me-1"></i>
                                                        Ver todas las respuestas ({{ count($pregA['respuestas_texto']) }})
                                                    </button>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @endif

                    </div>
                @endforeach
            @endif

        </div>
    @endif

</div>
@endsection

@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.tabulacion-option-search').forEach(function (searchInput) {
        const options = document.getElementById(searchInput.dataset.filterOptions);
        const valueInput = document.getElementById(searchInput.dataset.filterValue);
        if (!options || !valueInput) {
            return;
        }

        const matchingOption = function (value) {
            return Array.from(options.options).find(function (option) {
                return option.value.toLocaleLowerCase() === value.trim().toLocaleLowerCase();
            });
        };
        const selectedOption = Array.from(options.options).find(function (option) {
            return option.dataset.id === valueInput.value;
        });
        if (selectedOption) {
            searchInput.value = selectedOption.value;
        }

        searchInput.addEventListener('input', function () {
            const option = matchingOption(this.value);
            valueInput.value = option ? option.dataset.id : '';
        });

    });

    @if(session('warning'))
        Swal.fire({
            icon: 'warning',
            title: 'Atención',
            text: "{{ session('warning') }}",
            confirmButtonColor: '#001A29'
        });
    @endif

    @if(isset($errors) && $errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Filtro Inválido',
            html: '<ul class="text-start mb-0">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>',
            confirmButtonColor: '#001A29'
        });
    @endif
});

function cambiarEncuesta(form) {
    ['id_actividad', 'id_ficha', 'fecha_desde', 'fecha_hasta', 'buscarActividad', 'buscarFicha'].forEach(function (name) {
        form.elements[name].value = '';
    });
    form.submit();
}

// Función para colapsar / expandir respuestas de texto abiertas
function toggleRespuestasTexto(idPregunta, btn) {
    const ocultos = document.querySelectorAll('.texto-oculto-p' + idPregunta);
    const expandido = (btn.getAttribute('data-expandido') === 'true');

    if (expandido) {
        ocultos.forEach(el => el.classList.add('d-none'));
        btn.innerHTML = '<i class="fa-solid fa-chevron-down me-1"></i> Ver todas las respuestas';
        btn.setAttribute('data-expandido', 'false');
    } else {
        ocultos.forEach(el => el.classList.remove('d-none'));
        btn.innerHTML = '<i class="fa-solid fa-chevron-up me-1"></i> Ver menos';
        btn.setAttribute('data-expandido', 'true');
    }
}
</script>
@endpush
