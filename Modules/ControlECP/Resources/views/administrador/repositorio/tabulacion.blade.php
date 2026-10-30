
@extends('controlecp::components.layouts.dashboard')

@push('styles')

<style>
    :root {
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

    .tabulacion-card {
        background: #ffffff;
        border: 1px solid var(--gris-ecp-borde);
        border-radius: 18px;
        box-shadow: 0 4px 20px rgba(0, 26, 41, 0.05);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    /* Botones primarios y secundarios */
    .btn-ecp-primary {
        background-color: var(--verde-ecp);
        color: #ffffff !important;
        border: 1px solid var(--verde-ecp);
        font-weight: 600;
        border-radius: 50rem;
        padding: 0.55rem 1.35rem;
        transition: all .2s ease;
    }
    .btn-ecp-primary:hover {
        background-color: var(--verde-ecp-hover);
        border-color: var(--verde-ecp-hover);
        box-shadow: 0 4px 14px rgba(57, 169, 0, 0.35);
        transform: translateY(-1px);
    }

    .btn-ecp-navy {
        background-color: var(--petroleo-ecp);
        color: #ffffff !important;
        border: 1px solid var(--petroleo-ecp);
        font-weight: 600;
        border-radius: 50rem;
        padding: 0.55rem 1.35rem;
        transition: all .2s ease;
    }
    .btn-ecp-navy:hover {
        background-color: var(--petroleo-ecp-medio);
        border-color: var(--petroleo-ecp-medio);
        box-shadow: 0 4px 14px rgba(0, 26, 41, 0.25);
        transform: translateY(-1px);
    }

    /* Tarjetas de Indicadores */
    .indicator-card {
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
    .indicator-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 26, 41, 0.08);
    }
    .indicator-icon-circle {
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
    .prog-bar-container {
        width: 100%;
        height: 18px;
        background-color: #f1f5f9;
        border-radius: 50rem;
        overflow: hidden;
        position: relative;
    }
    .prog-bar-fill-green {
        height: 100%;
        background: linear-gradient(90deg, #39A900 0%, #48c800 100%);
        border-radius: 50rem;
        transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .prog-bar-fill-blue {
        height: 100%;
        background: linear-gradient(90deg, #0284c7 0%, #38bdf8 100%);
        border-radius: 50rem;
        transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .prog-bar-fill-amber {
        height: 100%;
        background: linear-gradient(90deg, #d97706 0%, #fbbf24 100%);
        border-radius: 50rem;
        transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Pestañas de Navegación por Actividad */
    .nav-tabs-ecp {
        border-bottom: 2px solid #e2e8f0;
        gap: 0.5rem;
    }
    .nav-tabs-ecp .nav-link {
        border: none;
        color: #64748b;
        font-weight: 600;
        padding: 0.75rem 1.25rem;
        border-radius: 12px 12px 0 0;
        transition: all .2s ease;
    }
    .nav-tabs-ecp .nav-link:hover {
        color: var(--petroleo-ecp);
        background-color: #f8fafc;
    }
    .nav-tabs-ecp .nav-link.active {
        color: var(--petroleo-ecp);
        background-color: #ffffff;
        border-bottom: 3px solid var(--verde-ecp);
    }

    /* Item de Respuesta de Texto */
    .item-respuesta-texto {
        background-color: #f8fafc;
        border-left: 3.5px solid var(--verde-ecp);
        border-radius: 0 10px 10px 0;
        padding: 0.85rem 1.15rem;
        margin-bottom: 0.75rem;
        transition: background-color .15s ease;
    }
    .item-respuesta-texto:hover {
        background-color: #f1f5f9;
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-3 py-3">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-muted" style="font-size: 0.85rem;">
                    <li class="breadcrumb-item"><a href="{{ route('controlecp.administrador.inicio') }}" class="text-decoration-none text-muted">Control ECP</a></li>
                    <li class="breadcrumb-item">Repositorio</li>
                    <li class="breadcrumb-item active text-dark fw-bold" aria-current="page">Tabulación</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-1" style="color: var(--petroleo-ecp);">
                Tabulación y Estadísticas de Encuestas
            </h2>
            <p class="text-muted mb-0" style="font-size: 0.92rem;">
                Consulta analítica en tiempo real de respuestas, tasa de participación, satisfacción y distribución por pregunta.
            </p>
        </div>

        <div>
            @php // Inicia un bloque PHP para preparar datos que usará la vista.
                $hayRespuestas = ($resultados && ($resultados['consolidado']['total_respuestas'] ?? 0) > 0);
            @endphp
            @if($hayRespuestas)

                <form action="{{ route('controlecp.administrador.repositorio.tabulacion.exportar') }}" method="GET" target="_blank" class="d-inline">
                    <input type="hidden" name="id_encuesta" value="{{ $encuestaActual?->id_encuesta }}">
                    <input type="hidden" name="id_actividad" value="{{ request('id_actividad') }}">
                    <input type="hidden" name="id_ficha" value="{{ request('id_ficha') }}">
                    <input type="hidden" name="fecha_desde" value="{{ request('fecha_desde') }}">
                    <input type="hidden" name="fecha_hasta" value="{{ request('fecha_hasta') }}">
                    <button type="submit" class="btn btn-ecp-navy d-inline-flex align-items-center gap-2">
                        <i class="fa-solid fa-file-pdf text-danger"></i>
                        <span>Exportar PDF</span>
                    </button>
                </form>
            @else
                <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2" disabled title="No hay respuestas disponibles para exportar">
                    <i class="fa-solid fa-file-pdf"></i>
                    <span>Exportar PDF</span>
                </button>
            @endif
        </div>
    </div>

    <div class="tabulacion-card p-3 mb-4">

        <form method="GET" action="{{ route('controlecp.administrador.repositorio.tabulacion.index') }}" id="formFiltrosTabulacion">
            <div class="row g-3 align-items-end">

                <div class="col-12 col-md-4 col-lg-3">
                    <label for="selectEncuesta" class="form-label fw-bold text-dark mb-1" style="font-size: 0.88rem;">
                        <i class="fa-solid fa-square-poll-vertical me-1 text-success"></i> Seleccionar Encuesta:
                    </label>
                    <select name="id_encuesta" id="selectEncuesta" class="form-select border-primary" onchange="cambiarEncuesta(this.form)">

                        @forelse($encuestas as $enc)
                            <option value="{{ $enc->id_encuesta }}" {{ ($encuestaActual?->id_encuesta == $enc->id_encuesta) ? 'selected' : '' }}>
                                {{ $enc->nombre }} ({{ $enc->estado }})
                            </option>
                        @empty
                            <option value="" disabled>No hay encuestas disponibles</option>
                        @endforelse
                    </select>
                </div>

                <div class="col-12 col-md-4 col-lg-3">
                    <label for="filtroActividad" class="form-label fw-semibold text-muted mb-1" style="font-size: 0.85rem;">
                        Actividad Formativa:
                    </label>
                    <select name="id_actividad" id="filtroActividad" class="form-select">
                        <option value="">-- Todas las actividades --</option>
                        @foreach($filtrosDisponibles['actividades'] as $act)
                            <option value="{{ $act->id }}" {{ (request('id_actividad') == $act->id) ? 'selected' : '' }}>
                                {{ $act->numero ? "{$act->numero}. " : '' }}{{ $act->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4 col-lg-2">
                    <label for="filtroFicha" class="form-label fw-semibold text-muted mb-1" style="font-size: 0.85rem;">
                        Ficha (Grupo):
                    </label>
                    <select name="id_ficha" id="filtroFicha" class="form-select">
                        <option value="">-- Todas --</option>
                        @foreach($filtrosDisponibles['fichas'] as $f)
                            <option value="{{ $f->id }}" {{ (request('id_ficha') == $f->id) ? 'selected' : '' }}>
                                {{ $f->code }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3 col-lg-1">
                    <label for="filtroFechaDesde" class="form-label fw-semibold text-muted mb-1" style="font-size: 0.85rem;">
                        Desde:
                    </label>
                    <input type="date"
                           name="fecha_desde"
                           id="filtroFechaDesde"
                           class="form-control"
                           value="{{ request('fecha_desde') }}">

                </div>

                <div class="col-6 col-md-3 col-lg-1">
                    <label for="filtroFechaHasta" class="form-label fw-semibold text-muted mb-1" style="font-size: 0.85rem;">
                        Hasta:
                    </label>
                    <input type="date"
                           name="fecha_hasta"
                           id="filtroFechaHasta"
                           class="form-control"
                           value="{{ request('fecha_hasta') }}">

                </div>

                <div class="col-12 col-md-6 col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-ecp-primary flex-grow-1 px-2 py-2" title="Aplicar filtros">
                        <i class="fa-solid fa-filter me-1"></i> Aplicar
                    </button>
                    <a href="{{ route('controlecp.administrador.repositorio.tabulacion.index', ['id_encuesta' => $encuestaActual?->id_encuesta]) }}"
                       class="btn btn-outline-secondary px-2 py-2 text-decoration-none"
                       title="Limpiar filtros">
                        <i class="fa-solid fa-rotate-left me-1"></i> Limpiar
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
            <h5 class="text-dark">No hay ninguna encuesta seleccionada</h5>
            <p class="text-muted small">Selecciona una encuesta de la lista superior para visualizar su tabulación.</p>
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
                    <a href="{{ route('controlecp.administrador.repositorio.tabulacion.index', ['id_encuesta' => $encuestaActual->id_encuesta]) }}" class="btn btn-ecp-primary btn-sm mt-2">
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
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
    ['id_actividad', 'id_ficha', 'fecha_desde', 'fecha_hasta'].forEach(function (name) {
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
