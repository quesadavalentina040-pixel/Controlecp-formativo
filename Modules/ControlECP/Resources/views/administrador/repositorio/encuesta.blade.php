
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

    .encuesta-card {
        background: #ffffff;
        border: 1px solid var(--gris-ecp-borde);
        border-radius: 18px;
        box-shadow: 0 4px 20px rgba(0, 26, 41, 0.05);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    /* Botones primarios SENA */
    .btn-ecp-primary {
        background-color: var(--verde-ecp);
        color: #ffffff !important;
        border: 1px solid var(--verde-ecp);
        font-weight: 600;
        border-radius: 50rem;
        padding: 0.55rem 1.4rem;
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
        padding: 0.55rem 1.4rem;
        transition: all .2s ease;
    }

    .btn-ecp-navy:hover {
        background-color: var(--petroleo-ecp-medio);
        border-color: var(--petroleo-ecp-medio);
        box-shadow: 0 4px 14px rgba(0, 26, 41, 0.25);
        transform: translateY(-1px);
    }

    /* Botones circulares de acción en tabla */
    .btn-action-icon {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        border: 1px solid var(--gris-ecp-borde);
        background: #ffffff;
        color: var(--petroleo-ecp);
        transition: all .2s ease;
        text-decoration: none;
        cursor: pointer;
    }

    .btn-action-icon:hover {
        background: var(--petroleo-ecp);
        color: #ffffff;
        border-color: var(--petroleo-ecp);
        transform: translateY(-1px);
    }

    .btn-action-icon.btn-action-success:hover {
        background: var(--verde-ecp);
        color: #ffffff;
        border-color: var(--verde-ecp);
    }

    .btn-action-icon.btn-action-danger:hover {
        background: #dc2626;
        color: #ffffff;
        border-color: #dc2626;
    }

    .btn-action-icon.btn-action-info:hover {
        background: #0284c7;
        color: #ffffff;
        border-color: #0284c7;
    }

    /* Badges de estado de encuesta */
    .badge-estado-activa {
        background-color: var(--verde-ecp-claro);
        color: #1b5e20;
        border: 1px solid #b7e4c7;
        font-weight: 600;
        padding: 0.35rem 0.75rem;
        border-radius: 50rem;
        font-size: 0.78rem;
    }

    .badge-estado-borrador {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
        font-weight: 600;
        padding: 0.35rem 0.75rem;
        border-radius: 50rem;
        font-size: 0.78rem;
    }

    .badge-estado-inactiva {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
        font-weight: 600;
        padding: 0.35rem 0.75rem;
        border-radius: 50rem;
        font-size: 0.78rem;
    }

    /* Selector visual interactivo para tipos de pregunta */
    .tipo-pregunta-card {
        cursor: pointer;
        border: 2px solid var(--gris-ecp-borde);
        border-radius: 14px;
        padding: 0.85rem 0.75rem;
        text-align: center;
        transition: all .2s ease;
        background-color: #ffffff;
    }

    .tipo-pregunta-card:hover {
        border-color: var(--verde-ecp);
        background-color: #f9fdfa;
    }

    .tipo-pregunta-radio:checked + .tipo-pregunta-card {
        border-color: var(--verde-ecp);
        background-color: #f0fbf4;
        box-shadow: 0 4px 12px rgba(57, 169, 0, 0.15);
    }

    .tipo-pregunta-radio:checked + .tipo-pregunta-card i {
        color: var(--verde-ecp) !important;
    }

    /* Tarjetas de preguntas dentro del constructor */
    .pregunta-item-card {
        background: #ffffff;
        border: 1px solid var(--gris-ecp-borde);
        border-radius: 14px;
        padding: 1.1rem;
        margin-bottom: 0.85rem;
        transition: all .2s ease;
        position: relative;
    }

    .pregunta-item-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0, 26, 41, 0.05);
    }

    .pregunta-drag-handle {
        cursor: grab;
        color: #94a3b8;
        padding: 4px;
    }

    .pregunta-drag-handle:active {
        cursor: grabbing;
    }

    /* Escala gráfica simulada de 1 a 5 */
    .escala-preview-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: #f1f5f9;
        color: var(--petroleo-ecp);
        font-weight: 600;
        font-size: 0.82rem;
        border: 1px solid #cbd5e1;
    }

    /* Estilo de la tabla */
    .table-encuestas th {
        background-color: #f8fafc;
        color: var(--petroleo-ecp);
        font-weight: 600;
        font-size: 0.85rem;
        border-bottom: 2px solid var(--gris-ecp-borde);
        white-space: nowrap;
    }

    .table-encuestas td {
        vertical-align: middle;
        font-size: 0.9rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .table-encuestas tbody tr:hover {
        background-color: #fafbfc;
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-3 py-3" style="max-width: 1300px; margin: 0 auto;">

    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090;">
        @if(session('success'))
            <div class="toast align-items-center text-white border-0 show shadow" style="background-color: var(--verde-ecp);" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i class="fas fa-check-circle fs-5"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="toast align-items-center text-white bg-danger border-0 show shadow" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i class="fas fa-exclamation-triangle fs-5"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
                </div>
            </div>
        @endif

        <div id="toastAjaxEncuesta" class="toast align-items-center text-white border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true" style="display: none;">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i id="toastAjaxEncuestaIcon" class="fas fa-check-circle fs-5"></i>
                    <span id="toastAjaxEncuestaMsg">Operación completada</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        </div>
    </div>

    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="fas fa-circle-exclamation fs-5"></i>
                <strong class="font-outfit">Por favor corrige los siguientes datos:</strong>
            </div>
            <ul class="mb-0 ps-4 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="encuesta-card p-4 mb-4">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge rounded-pill" style="background-color: var(--verde-ecp-claro); color: #1b5e20; font-size: 0.75rem;">
                        <i class="fas fa-landmark me-1"></i> Repositorio Institucional
                    </span>
                    <span class="badge rounded-pill bg-light text-muted border" style="font-size: 0.75rem;">
                        HU-009: Gestión de Encuestas
                    </span>
                </div>
                <h1 class="h3 font-outfit fw-bold text-dark mb-1">Encuestas - Escuela Cultura de Paz</h1>
                <p class="text-muted small mb-0">
                    Crea, estructura y administra los instrumentos de evaluación formativa y satisfacción vinculados a las actividades.
                </p>
            </div>

            <div>
                <button type="button" class="btn btn-ecp-primary d-flex align-items-center gap-2 shadow-sm" onclick="abrirModalCrearEncuesta()">
                    <i class="fas fa-plus-circle"></i>
                    <span>Nueva Encuesta</span>
                </button>
            </div>
        </div>

        <hr class="my-3" style="border-color: var(--gris-ecp-borde);">
        <div class="row g-3 text-center text-md-start">
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #e0f2fe; color: #0284c7;">
                        <i class="fas fa-square-poll-vertical fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-5 text-dark">{{ $indicadores['total'] ?? 0 }}</div>
                        <small class="text-muted">Total encuestas</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: var(--verde-ecp-claro); color: #15803d;">
                        <i class="fas fa-circle-check fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-5 text-dark">{{ $indicadores['activas'] ?? 0 }}</div>
                        <small class="text-muted">Activas (disponibles)</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #fef3c7; color: #b45309;">
                        <i class="fas fa-pen-ruler fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-5 text-dark">{{ $indicadores['borradores'] ?? 0 }}</div>
                        <small class="text-muted">Borradores en diseño</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #fee2e2; color: #b91c1c;">
                        <i class="fas fa-ban fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-5 text-dark">{{ $indicadores['inactivas'] ?? 0 }}</div>
                        <small class="text-muted">Inactivas</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="encuesta-card p-3 mb-4">

        <form method="GET" action="{{ route('controlecp.administrador.repositorio.encuesta.index') }}" class="row g-2 align-items-center" id="formFiltros">
            <div class="col-12 col-md-6 col-lg-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3 text-muted">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text"
                           name="buscar"
                           id="filtroBuscarInput"
                           class="form-control border-start-0 rounded-end-pill pe-3"
                           placeholder="Buscar por nombre o descripción de la encuesta..."
                           value="{{ request('buscar') }}"
                           onkeyup="filtrarTablaEnVivo()">

                </div>
            </div>

            <div class="col-6 col-md-4 col-lg-3">
                <select name="estado" class="form-select rounded-pill px-3" onchange="document.getElementById('formFiltros').submit()">
                    <option value="">Todos los estados</option>
                    <option value="Borrador" {{ request('estado') === 'Borrador' ? 'selected' : '' }}>Borrador</option>
                    <option value="Activa" {{ request('estado') === 'Activa' ? 'selected' : '' }}>Activa</option>
                    <option value="Inactiva" {{ request('estado') === 'Inactiva' ? 'selected' : '' }}>Inactiva</option>
                </select>
            </div>

            <div class="col-6 col-md-2 col-lg-4 text-end">
                @if(request('buscar') || request('estado'))
                    <a href="{{ route('controlecp.administrador.repositorio.encuesta.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="fas fa-rotate-left me-1"></i> Limpiar filtros
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="encuesta-card p-0 overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover table-encuestas mb-0" id="tablaEncuestas">
                <thead>
                    <tr>
                        <th class="ps-4">Nombre de la Encuesta</th>
                        <th>Actividades Asociadas</th>
                        <th class="text-center">Preguntas</th>
                        <th class="text-center">Estado</th>
                        <th>Fecha de Creación</th>
                        <th class="text-center pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>

                    @forelse($encuestas as $encuesta)
                        @php // Inicia un bloque PHP para preparar datos que usará la vista.
                            $encId = $encuesta->id_encuesta ?? $encuesta->id;
                            $tieneResp = $encuesta->tieneRespuestas();
                            $totalResp = $encuesta->totalRespuestas();
                        @endphp
                        <tr class="fila-encuesta" data-nombre="{{ strtolower($encuesta->nombre) }}" data-descripcion="{{ strtolower($encuesta->descripcion) }}">

                            <td class="ps-4 py-3" style="max-width: 320px;">
                                <div class="fw-bold text-dark font-outfit" style="font-size: 1.02rem;">
                                    {{ $encuesta->nombre }}
                                </div>
                                @if($encuesta->descripcion)
                                    <p class="text-muted small mb-0 text-truncate" title="{{ $encuesta->descripcion }}" style="max-width: 300px;">
                                        {{ $encuesta->descripcion }}
                                    </p>
                                @else
                                    <span class="text-muted small fst-italic">Sin descripción</span>
                                @endif

                                @if($tieneResp)
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle mt-1 rounded-pill" style="font-size: 0.7rem;">
                                        <i class="fas fa-comments me-1"></i> {{ $totalResp }} respuestas registradas
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if($encuesta->actividades->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-1 align-items-center">
                                        <span class="badge rounded-pill bg-light text-dark border fw-semibold px-2 py-1">
                                            <i class="fas fa-link text-primary me-1"></i> {{ $encuesta->actividades->count() }}
                                            {{ $encuesta->actividades->count() == 1 ? 'actividad' : 'actividades' }}
                                        </span>
                                        @foreach($encuesta->actividades->take(2) as $act)
                                            <span class="badge bg-light text-muted border text-truncate" style="max-width: 150px;" title="{{ $act->nombre }}">
                                                {{ $act->numero }}. {{ $act->nombre }}
                                            </span>
                                        @endforeach
                                        @if($encuesta->actividades->count() > 2)
                                            <span class="badge bg-light text-muted border" title="{{ $encuesta->actividades->pluck('nombre')->implode(', ') }}">
                                                +{{ $encuesta->actividades->count() - 2 }} más
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted small">
                                        <i class="fas fa-circle-minus text-muted me-1"></i> Sin actividades asignadas
                                    </span>
                                @endif
                            </td>

                            <td class="text-center">
                                <span class="badge rounded-pill bg-light text-dark border px-3 py-1 font-outfit fw-bold" style="font-size: 0.85rem;" id="badge-contador-{{ $encId }}">
                                    <i class="fas fa-list-ol text-success me-1"></i> {{ $encuesta->preguntas_count }}
                                </span>
                            </td>

                            <td class="text-center">
                                @if($encuesta->estado === 'Activa')
                                    <span class="badge-estado-activa">
                                        <i class="fas fa-circle-check me-1"></i> Activa
                                    </span>
                                @elseif($encuesta->estado === 'Borrador')
                                    <span class="badge-estado-borrador">
                                        <i class="fas fa-pen-ruler me-1"></i> Borrador
                                    </span>
                                @else
                                    <span class="badge-estado-inactiva">
                                        <i class="fas fa-ban me-1"></i> Inactiva
                                    </span>
                                @endif
                            </td>

                            <td>
                                @php // Inicia un bloque PHP para preparar datos que usará la vista.
                                    $fechaObj = $encuesta->fecha_creacion ?? $encuesta->created_at;
                                @endphp
                                <div class="text-dark small fw-semibold">
                                    {{ $fechaObj ? $fechaObj->format('d/m/Y') : 'N/A' }}
                                </div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    {{ $fechaObj ? $fechaObj->format('h:i A') : '' }}
                                </div>
                            </td>

                            <td class="text-center pe-4">
                                <div class="d-inline-flex align-items-center gap-1">

                                    <button type="button" class="btn-action-icon btn-action-info"
                                            title="Ver estructura de la encuesta"
                                            onclick="abrirModalVerEncuesta({{ $encId }})">

                                        <i class="fas fa-eye"></i>
                                    </button>

                                    <button type="button" class="btn-action-icon"
                                            title="Editar datos y gestionar preguntas"
                                            onclick="abrirModalGestionarEncuesta({{ $encId }})">

                                        <i class="fas fa-pen-to-square"></i>
                                    </button>

                                    @if($encuesta->estado === 'Activa')
                                        <button type="button" class="btn-action-icon btn-action-danger"
                                                title="Desactivar encuesta"
                                                onclick="confirmarDesactivarEncuesta({{ $encId }}, @json($encuesta->nombre))">

                                            <i class="fas fa-toggle-on text-success"></i>
                                        </button>
                                    @elseif($encuesta->estado === 'Borrador')
                                        <button type="button" class="btn-action-icon btn-action-success"
                                                title="Activar encuesta (requiere preguntas)"
                                                onclick="confirmarActivarEncuesta({{ $encId }}, @json($encuesta->nombre), {{ $encuesta->preguntas_count }})">

                                            <i class="fas fa-toggle-off text-muted"></i>
                                        </button>
                                        <button type="button" class="btn-action-icon btn-action-danger"
                                                title="Desactivar encuesta (pasar a Inactiva)"
                                                onclick="confirmarDesactivarEncuesta({{ $encId }}, @json($encuesta->nombre))">

                                            <i class="fas fa-ban text-muted"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn-action-icon btn-action-success"
                                                title="Reactivar encuesta"
                                                onclick="confirmarActivarEncuesta({{ $encId }}, @json($encuesta->nombre), {{ $encuesta->preguntas_count }})">

                                            <i class="fas fa-toggle-off text-muted"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="py-4">
                                    <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px; background-color: var(--verde-ecp-claro); color: var(--verde-ecp);">
                                        <i class="fas fa-square-poll-vertical fs-2"></i>
                                    </div>
                                    <h5 class="fw-bold text-dark font-outfit">No se encontraron encuestas registradas</h5>
                                    <p class="text-muted small mx-auto" style="max-width: 400px;">
                                        Comienza creando tu primera encuesta de satisfacción o evaluación para las actividades del programa.
                                    </p>
                                    <button type="button" class="btn btn-ecp-primary btn-sm px-4" onclick="abrirModalCrearEncuesta()">
                                        <i class="fas fa-plus me-1"></i> Crear primera encuesta
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<div class="modal fade" id="modalCrearEncuesta" tabindex="-1" aria-labelledby="modalCrearEncuestaTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--petroleo-ecp); border-bottom: 3px solid var(--verde-ecp);">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-plus-circle fs-5" style="color: #62E31D;"></i>
                    <h5 class="modal-title font-outfit fw-bold" id="modalCrearEncuestaTitle">Nueva Encuesta</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formCrearEncuesta" method="POST" action="{{ route('controlecp.administrador.repositorio.encuesta.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-light border rounded-3 p-3 mb-4 d-flex align-items-center gap-2 small">
                        <i class="fas fa-info-circle text-primary fs-5"></i>
                        <span>La encuesta se creará en estado <strong>Borrador</strong> con fecha de creación automática y asociada a tu usuario administrador. Luego podrás agregar preguntas y activarla.</span>
                    </div>

                    <div class="mb-3">
                        <label for="crear_nombre" class="form-label fw-bold font-outfit text-dark small mb-1">
                            Nombre de la Encuesta <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="nombre"
                               id="crear_nombre"
                               class="form-control rounded-pill px-3 py-2"
                               placeholder="Ej: Encuesta de Evaluación Formativa - Momento 1"
                               maxlength="255"
                               required>

                    </div>

                    <div class="mb-4">
                        <label for="crear_descripcion" class="form-label fw-bold font-outfit text-dark small mb-1">
                            Descripción / Propósito de la Encuesta
                        </label>
                        <textarea name="descripcion"
                                  id="crear_descripcion"
                                  rows="3"
                                  class="form-control rounded-4 p-3"
                                  placeholder="Indica el propósito del instrumento, instrucciones breves para los aprendices, etc..."></textarea>

                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold font-outfit text-dark small mb-1 d-flex align-items-center justify-content-between">
                            <span>Vincular a Actividades Formativas (Opcional)</span>
                            <span class="text-muted fw-normal" style="font-size: 0.75rem;">Puedes asignarla ahora o más tarde</span>
                        </label>
                        <div class="border rounded-4 p-3 bg-light" style="max-height: 180px; overflow-y: auto;">
                            @forelse($actividadesDisponibles as $act)
                                <div class="form-check py-1">
                                    <input class="form-check-input" type="checkbox" name="actividades_ids[]" value="{{ $act->id }}" id="check_act_{{ $act->id }}">
                                    <label class="form-check-label small text-dark" for="check_act_{{ $act->id }}">
                                        <strong>{{ $act->numero }}.</strong> {{ $act->nombre }}
                                        @if($act->id_encuesta || $act->encuesta_id)
                                            <span class="badge bg-secondary-subtle text-muted" style="font-size: 0.68rem;">Reemplazará encuesta previa</span>
                                        @endif
                                    </label>
                                </div>
                            @empty
                                <div class="text-muted small fst-italic">No hay actividades registradas en el catálogo.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-ecp-primary px-4 d-flex align-items-center gap-2">
                        <i class="fas fa-save"></i>
                        <span>Guardar y Configurar Preguntas</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalVerEncuesta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--petroleo-ecp); border-bottom: 3px solid var(--verde-ecp);">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-eye fs-5" style="color: #62E31D;"></i>
                    <h5 class="modal-title font-outfit fw-bold" id="verModalTitle">Detalle de la Encuesta</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-4">

                <div class="d-flex align-items-start justify-content-between gap-3 mb-3 pb-3 border-bottom">
                    <div>
                        <h4 class="font-outfit fw-bold text-dark mb-1" id="verEncuestaNombre">-</h4>
                        <p class="text-muted small mb-0" id="verEncuestaDescripcion">-</p>
                    </div>
                    <div id="verEncuestaEstadoBadge">
                        <!-- Badge estado -->
                    </div>
                </div>

                <div class="row g-2 mb-4 small">
                    <div class="col-sm-6">
                        <span class="text-muted">Fecha de creación:</span>
                        <strong class="text-dark ms-1" id="verEncuestaFecha">-</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted">Total de preguntas:</span>
                        <strong class="text-dark ms-1" id="verEncuestaTotalPreguntas">0</strong>
                    </div>
                    <div class="col-12">
                        <span class="text-muted">Actividades asociadas:</span>
                        <div class="mt-1" id="verEncuestaActividadesContainer">
                            <span class="text-muted fst-italic">Ninguna</span>
                        </div>
                    </div>
                </div>

                <h5 class="font-outfit fw-bold text-dark mb-3">
                    <i class="fas fa-list-check text-success me-2"></i>Cuestionario de Preguntas
                </h5>
                <div id="verEncuestaPreguntasListado">
                    <!-- Cargado dinámicamente -->
                </div>
            </div>

            <div class="modal-footer bg-light px-4 py-3">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalGestionarEncuesta" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--petroleo-ecp); border-bottom: 3px solid var(--verde-ecp);">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <i class="fas fa-sliders fs-5" style="color: #62E31D;"></i>
                    <h5 class="modal-title font-outfit fw-bold mb-0" id="gestionarModalTitle">Gestionar Encuesta</h5>
                    <div id="gestionarModalEstadoBadge" class="ms-1"></div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div id="gestionarModalEstadoBtn"></div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
            </div>

            <div class="modal-body p-4">

                <div id="alertaEncuestaConRespuestas" class="alert alert-warning border border-warning rounded-4 p-3 mb-4 d-none">
                    <div class="d-flex gap-2">
                        <i class="fas fa-lock text-warning fs-4 mt-1"></i>
                        <div>
                            <strong class="d-block font-outfit">Encuesta con respuestas registradas</strong>
                            <span class="small">
                                Esta encuesta ya cuenta con respuestas completadas por aprendices en cronogramas ejecutados. Para proteger la validez y consistencia estadística, <strong>no es posible agregar, editar, reordenar ni eliminar preguntas u opciones</strong>. Solo puedes modificar el nombre o la descripción.
                            </span>
                        </div>
                    </div>
                </div>

                <form id="formActualizarEncuesta" method="POST" action="" class="mb-4 pb-4 border-bottom">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="gestionarEncuestaId" value="">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_nombre" class="form-label fw-bold font-outfit text-dark small mb-1">
                                Nombre de la Encuesta <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nombre" id="edit_nombre" class="form-control rounded-pill px-3" required maxlength="255">
                        </div>

                        <div class="col-md-6">
                            <label for="edit_descripcion" class="form-label fw-bold font-outfit text-dark small mb-1">
                                Descripción
                            </label>
                            <input type="text" name="descripcion" id="edit_descripcion" class="form-control rounded-pill px-3" maxlength="2000">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold font-outfit text-dark small mb-1">
                                Actividades Asociadas (Reutilización por actividad)
                            </label>
                            <div class="border rounded-4 p-3 bg-light" style="max-height: 140px; overflow-y: auto;">
                                <div class="row g-2">
                                    @foreach($actividadesDisponibles as $act)
                                        <div class="col-md-6">
                                            <div class="form-check py-1">
                                                <input class="form-check-input edit-check-actividad" type="checkbox" name="actividades_ids[]" value="{{ $act->id }}" id="edit_act_{{ $act->id }}">
                                                <label class="form-check-label small text-dark" for="edit_act_{{ $act->id }}">
                                                    <strong>{{ $act->numero }}.</strong> {{ $act->nombre }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-sm btn-ecp-navy px-4">
                                <i class="fas fa-check me-1"></i> Guardar Cambios de la Encuesta
                            </button>
                        </div>
                    </div>
                </form>

                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h5 class="font-outfit fw-bold text-dark mb-0">
                            <i class="fas fa-circle-question text-success me-2"></i>Preguntas de la Encuesta
                        </h5>
                        <small class="text-muted">Total: <strong id="contadorPreguntasModal">0</strong> preguntas registradas</small>
                    </div>

                    <div id="contenedorBotonNuevaPregunta">
                        <button type="button" class="btn btn-sm btn-ecp-primary d-flex align-items-center gap-1 shadow-sm" onclick="toggleFormularioNuevaPregunta()">
                            <i class="fas fa-plus"></i>
                            <span>Agregar Pregunta</span>
                        </button>
                    </div>
                </div>

                <div id="contenedorFormularioNuevaPregunta" class="encuesta-card p-4 mb-4 bg-light border-2 d-none" style="border-color: var(--verde-ecp) !important;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-bold font-outfit text-dark mb-0" id="tituloFormularioPregunta">
                            <i class="fas fa-pen-to-square text-success me-1"></i> Diseñar Nueva Pregunta
                        </h6>
                        <button type="button" class="btn-close" onclick="cancelarEdicionPregunta()"></button>
                    </div>

                    <form id="formNuevaPreguntaAjax" novalidate onsubmit="guardarPreguntaAjax(event); return false;">
                        <input type="hidden" id="editar_pregunta_id" value="">
                        <input type="hidden" id="pregunta_encuesta_id" value="">

                        <div class="mb-3">
                            <label for="nueva_pregunta_texto" class="form-label fw-bold font-outfit text-dark small mb-1">
                                Texto de la Pregunta <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="nueva_pregunta_texto" class="form-control rounded-pill px-3" placeholder="Ej: ¿Cómo calificaría la claridad y metodología del facilitador durante la actividad?">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold font-outfit text-dark small mb-1">
                                Tipo de Respuesta <span class="text-danger">*</span>
                            </label>
                            <div class="row g-2">
                                <div class="col-4">
                                    <input type="radio" class="d-none tipo-pregunta-radio" name="nuevo_tipo" id="tipo_opcion_multiple" value="Opcion_multiple" checked onchange="cambiarTipoPregunta('Opcion_multiple')">
                                    <label for="tipo_opcion_multiple" class="tipo-pregunta-card w-100">
                                        <i class="fas fa-list-ul fs-4 mb-1 d-block text-muted"></i>
                                        <span class="d-block fw-semibold small text-dark">Opción Múltiple</span>
                                        <span class="d-block text-muted" style="font-size: 0.7rem;">Mínimo 2 opciones</span>
                                    </label>
                                </div>

                                <div class="col-4">
                                    <input type="radio" class="d-none tipo-pregunta-radio" name="nuevo_tipo" id="tipo_escala" value="Escala" onchange="cambiarTipoPregunta('Escala')">
                                    <label for="tipo_escala" class="tipo-pregunta-card w-100">
                                        <i class="fas fa-star-half-stroke fs-4 mb-1 d-block text-muted"></i>
                                        <span class="d-block fw-semibold small text-dark">Escala Numérica</span>
                                        <span class="d-block text-muted" style="font-size: 0.7rem;">Fija de 1 a 5</span>
                                    </label>
                                </div>

                                <div class="col-4">
                                    <input type="radio" class="d-none tipo-pregunta-radio" name="nuevo_tipo" id="tipo_texto" value="Texto" onchange="cambiarTipoPregunta('Texto')">
                                    <label for="tipo_texto" class="tipo-pregunta-card w-100">
                                        <i class="fas fa-align-left fs-4 mb-1 d-block text-muted"></i>
                                        <span class="d-block fw-semibold small text-dark">Texto Abierto</span>
                                        <span class="d-block text-muted" style="font-size: 0.7rem;">Respuesta libre</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="nueva_pregunta_obligatoria" checked>
                            <label class="form-check-label fw-semibold small text-dark" for="nueva_pregunta_obligatoria">
                                <i class="fas fa-asterisk text-danger me-1"></i> Pregunta obligatoria (el aprendiz debe responderla para completar la encuesta)
                            </label>
                        </div>

                        <div id="seccionOpcionesMultiples" class="p-3 bg-white rounded-4 border mb-3">
                            <label class="form-label fw-bold font-outfit text-dark small mb-1 d-flex justify-content-between align-items-center">
                                <span>Opciones de Respuesta <span class="text-danger">*</span> (Mínimo 2)</span>
                                <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-2 py-0" style="font-size: 0.75rem;" onclick="agregarFilaOpcion()">
                                    <i class="fas fa-plus me-1"></i> Añadir opción
                                </button>
                            </label>
                            <div id="contenedorFilasOpciones" class="d-flex flex-column gap-2 mt-2">
                                <div class="input-group input-group-sm fila-opcion">
                                    <span class="input-group-text bg-light text-muted">1</span>
                                    <input type="text" class="form-control opcion-input" placeholder="Opción 1">
                                    <button class="btn btn-outline-danger" type="button" onclick="eliminarFilaOpcion(this)"><i class="fas fa-trash"></i></button>
                                </div>
                                <div class="input-group input-group-sm fila-opcion">
                                    <span class="input-group-text bg-light text-muted">2</span>
                                    <input type="text" class="form-control opcion-input" placeholder="Opción 2">
                                    <button class="btn btn-outline-danger" type="button" onclick="eliminarFilaOpcion(this)"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                        </div>

                        <div id="seccionEscala" class="p-3 bg-white rounded-4 border mb-3 d-none">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="fas fa-star text-warning fs-5"></i>
                                <strong class="font-outfit text-dark small">Escala fija de calificación (1 a 5)</strong>
                            </div>
                            <p class="text-muted small mb-2">El aprendiz responderá seleccionando un valor dentro de la escala fija:</p>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="escala-preview-pill">1</span>
                                <span class="escala-preview-pill">2</span>
                                <span class="escala-preview-pill">3</span>
                                <span class="escala-preview-pill">4</span>
                                <span class="escala-preview-pill">5</span>
                                <span class="text-muted small ms-2">(1 = Muy deficiente &bull; 5 = Excelente)</span>
                            </div>
                        </div>

                        <div id="seccionTextoAbierto" class="p-3 bg-white rounded-4 border mb-3 d-none text-muted small">
                            <i class="fas fa-circle-info text-primary me-1"></i> Respuesta abierta: el aprendiz dispondrá de un campo libre para registrar observaciones, reflexiones o comentarios formativos.
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="cancelarEdicionPregunta()">Cancelar</button>
                            <button type="button" id="btnGuardarPreguntaAjax" class="btn btn-sm btn-ecp-primary px-4 d-inline-flex align-items-center" onclick="guardarPreguntaAjax(event)">
                                <span id="spinnerGuardarPregunta" class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                                <i id="iconoGuardarPregunta" class="fas fa-save me-1"></i>
                                <span id="textoBotonGuardarPregunta">Guardar Pregunta</span>
                            </button>
                        </div>
                    </form>
                </div>

                <div id="listaPreguntasContainer" class="d-flex flex-column gap-2">
                    <!-- Cargado dinámicamente -->
                </div>
            </div>

            <div class="modal-footer bg-light px-4 py-3">
                <button type="button" class="btn btn-ecp-navy rounded-pill px-4" data-bs-dismiss="modal">
                    Listo / Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<script>
    const RUTA_BASE_ENCUESTAS = (function() {
        try {
            const rutaCompleta = "{{ route('controlecp.administrador.repositorio.encuesta.index') }}";
            const parsed = new URL(rutaCompleta, window.location.href);
            return parsed.pathname;
        } catch (e) {
            return "{{ url('control-ecp/administrador/repositorio/encuestas') }}";
        }
    })();
    const CSRF_TOKEN = "{{ csrf_token() }}";
    let sortablePreguntasInstance = null;
    let isGuardandoPregunta = false;

    // Al cargar la página, comprobar si viene el parámetro 'abrir' para desplegar modal inmediatamente
    document.addEventListener('DOMContentLoaded', function () {
        const urlParams = new URLSearchParams(window.location.search);
        const encuestaIdAbrir = urlParams.get('abrir');
        if (encuestaIdAbrir) {
            abrirModalGestionarEncuesta(encuestaIdAbrir);
        }
    });

    /* =========================================================================
       1. BUSCADOR EN VIVO DE LA TABLA
       ========================================================================= */
    function filtrarTablaEnVivo() {
        const input = document.getElementById('filtroBuscarInput').value.toLowerCase().trim();
        const filas = document.querySelectorAll('.fila-encuesta');

        filas.forEach(fila => {
            const nombre = fila.getAttribute('data-nombre') || '';
            const desc = fila.getAttribute('data-descripcion') || '';
            if (nombre.includes(input) || desc.includes(input)) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });
    }

    /* =========================================================================
       2. MODAL CREAR ENCUESTA
       ========================================================================= */
    const modalCrearInstancia = new bootstrap.Modal(document.getElementById('modalCrearEncuesta'));

    function abrirModalCrearEncuesta() {
        document.getElementById('formCrearEncuesta').reset();
        modalCrearInstancia.show();
    }

    /* =========================================================================
       3. MODAL VER ENCUESTA (SOLO LECTURA)
       ========================================================================= */
    const modalVerInstancia = new bootstrap.Modal(document.getElementById('modalVerEncuesta'));

    function abrirModalVerEncuesta(id) {
        fetch(`${RUTA_BASE_ENCUESTAS}/${id}`)
            .then(res => res.json())
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Error al obtener datos');

                const enc = data.encuesta;
                const elNom = document.getElementById('verEncuestaNombre'); if (elNom) elNom.textContent = enc.nombre;
                const elDesc = document.getElementById('verEncuestaDescripcion'); if (elDesc) elDesc.textContent = enc.descripcion || 'Sin descripción';
                const fechaCreacionVal = enc.fecha_creacion || enc.created_at;
                const elFecha = document.getElementById('verEncuestaFecha'); if (elFecha) elFecha.textContent = fechaCreacionVal ? new Date(fechaCreacionVal).toLocaleDateString('es-CO') : 'N/A';
                const elTotal = document.getElementById('verEncuestaTotalPreguntas'); if (elTotal) elTotal.textContent = enc.preguntas ? enc.preguntas.length : 0;

                // Badge de estado
                let badgeEstadoHtml = '';
                if (enc.estado === 'Activa') {
                    badgeEstadoHtml = `<span class="badge-estado-activa"><i class="fas fa-circle-check me-1"></i> Activa</span>`;
                } else if (enc.estado === 'Borrador') {
                    badgeEstadoHtml = `<span class="badge-estado-borrador"><i class="fas fa-pen-ruler me-1"></i> Borrador</span>`;
                } else {
                    badgeEstadoHtml = `<span class="badge-estado-inactiva"><i class="fas fa-ban me-1"></i> Inactiva</span>`;
                }
                document.getElementById('verEncuestaEstadoBadge').innerHTML = badgeEstadoHtml;

                // Actividades
                const actContainer = document.getElementById('verEncuestaActividadesContainer');
                if (enc.actividades && enc.actividades.length > 0) {
                    actContainer.innerHTML = enc.actividades.map(a => `<span class="badge bg-light text-dark border me-1 mb-1"><strong>${a.numero}.</strong> ${a.nombre}</span>`).join('');
                } else {
                    actContainer.innerHTML = `<span class="text-muted small fst-italic">Ninguna actividad asociada actualmente</span>`;
                }

                // Listado de preguntas
                const preguntasContainer = document.getElementById('verEncuestaPreguntasListado');
                if (!enc.preguntas || enc.preguntas.length === 0) {
                    preguntasContainer.innerHTML = `<div class="alert alert-light border text-muted small p-4 text-center">Esta encuesta aún no contiene preguntas registradas.</div>`;
                } else {
                    preguntasContainer.innerHTML = enc.preguntas.map((p, index) => {
                        let renderOpciones = '';
                        const tipoNorm = (p.tipo || '').toLowerCase();

                        if (tipoNorm === 'opcion_multiple' && p.opciones) {
                            renderOpciones = `
                                <div class="mt-2 ps-3 border-start">
                                    ${p.opciones.map(o => `
                                        <div class="d-flex align-items-center gap-2 small text-dark py-1">
                                            <i class="far fa-circle text-muted" style="font-size: 0.75rem;"></i>
                                            <span>${o.texto}</span>
                                        </div>
                                    `).join('')}
                                </div>
                            `;
                        } else if (tipoNorm === 'escala') {
                            let pills = '';
                            for (let i = 1; i <= 5; i++) {
                                pills += `<span class="escala-preview-pill me-1">${i}</span>`;
                            }
                            renderOpciones = `
                                <div class="mt-2">
                                    <div class="d-flex align-items-center gap-1 mb-1">${pills}</div>
                                    <div class="d-flex justify-content-between text-muted" style="font-size: 0.75rem; max-width: 250px;">
                                        <span>1 (Deficiente)</span>
                                        <span>5 (Excelente)</span>
                                    </div>
                                </div>
                            `;
                        } else if (tipoNorm === 'texto') {
                            renderOpciones = `
                                <div class="mt-2">
                                    <input type="text" class="form-control form-control-sm bg-light text-muted" placeholder="Respuesta abierta del aprendiz..." disabled>
                                </div>
                            `;
                        }

                        let tipoLabel = tipoNorm === 'opcion_multiple' ? 'Opción Múltiple' : (tipoNorm === 'escala' ? 'Escala 1 a 5' : 'Texto Abierto');
                        let obligatoriaBadge = p.obligatoria ? `<span class="badge bg-danger-subtle text-danger border" style="font-size: 0.68rem;"><i class="fas fa-asterisk me-1"></i>Obligatoria</span>` : `<span class="badge bg-light text-muted border" style="font-size: 0.68rem;">Opcional</span>`;

                        return `
                            <div class="p-3 mb-2 rounded-3 border bg-light">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <strong class="text-dark small">${index + 1}. ${p.texto}</strong>
                                    <div class="d-flex align-items-center gap-1">
                                        ${obligatoriaBadge}
                                        <span class="badge bg-white text-muted border" style="font-size: 0.7rem;">${tipoLabel}</span>
                                    </div>
                                </div>
                                ${renderOpciones}
                            </div>
                        `;
                    }).join('');
                }

                modalVerInstancia.show();
            })
            .catch(err => {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar la información de la encuesta.',
                    confirmButtonColor: 'var(--petroleo-ecp)'
                });
            });
    }

    /* =========================================================================
       4. MODAL GESTIONAR ENCUESTA (CONSTRUCTOR DE PREGUNTAS Y EDICIÓN)
       ========================================================================= */
    const modalGestionarInstancia = new bootstrap.Modal(document.getElementById('modalGestionarEncuesta'));
    let encuestaActualData = null;

    function abrirModalGestionarEncuesta(id) {
        fetch(`${RUTA_BASE_ENCUESTAS}/${id}`)
            .then(res => res.json())
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Error al obtener datos');

                encuestaActualData = data.encuesta;
                const enc = data.encuesta;
                const encId = enc.id_encuesta || enc.id;

                document.getElementById('gestionarEncuestaId').value = encId;
                const hiddenPregId = document.getElementById('pregunta_encuesta_id');
                if (hiddenPregId) hiddenPregId.value = encId;
                const modalTitleEl = document.getElementById('gestionarModalTitle');
                if (modalTitleEl) modalTitleEl.textContent = `Gestionar: ${enc.nombre}`;
                document.getElementById('formActualizarEncuesta').action = `${RUTA_BASE_ENCUESTAS}/${encId}`;

                // Cargar campos generales
                document.getElementById('edit_nombre').value = enc.nombre;
                document.getElementById('edit_descripcion').value = enc.descripcion || '';

                // Actividades asociadas
                const checks = document.querySelectorAll('.edit-check-actividad');
                const activasIds = (enc.actividades || []).map(a => a.id);
                checks.forEach(chk => {
                    chk.checked = activasIds.includes(parseInt(chk.value));
                });

                // Alerta de respuestas
                const alertaBloqueo = document.getElementById('alertaEncuestaConRespuestas');
                const btnNuevaPreg = document.getElementById('contenedorBotonNuevaPregunta');

                if (data.tiene_respuestas) {
                    alertaBloqueo.classList.remove('d-none');
                    btnNuevaPreg.classList.add('d-none');
                } else {
                    alertaBloqueo.classList.add('d-none');
                    btnNuevaPreg.classList.remove('d-none');
                }

                // Ocultar formulario de nueva pregunta por defecto
                cancelarEdicionPregunta();

                // Renderizar preguntas
                renderizarPreguntasEnModal(enc.preguntas || [], data.puede_modificar_preguntas);

                // Renderizar estado y botones rápidos en la cabecera
                actualizarCabeceraEstadoModal(enc, (enc.preguntas || []).length);

                modalGestionarInstancia.show();
            })
            .catch(err => {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar la encuesta seleccionada.',
                    confirmButtonColor: 'var(--petroleo-ecp)'
                });
            });
    }

    function recargarPreguntasModal(id) {
        return fetch(`${RUTA_BASE_ENCUESTAS}/${id}`)
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;
                encuestaActualData = data.encuesta;
                const enc = data.encuesta;
                renderizarPreguntasEnModal(enc.preguntas || [], data.puede_modificar_preguntas);
                actualizarCabeceraEstadoModal(enc, (enc.preguntas || []).length);
            })
            .catch(err => console.error('Error al recargar preguntas:', err));
    }

    function actualizarCabeceraEstadoModal(enc, totalPreg) {
        const estadoBadgeEl = document.getElementById('gestionarModalEstadoBadge');
        const estadoBtnEl = document.getElementById('gestionarModalEstadoBtn');
        if (!estadoBadgeEl || !estadoBtnEl) return;

        const encId = enc.id_encuesta || enc.id;
        const safeNombre = (enc.nombre || '').replace(/'/g, "\\'");

        if (enc.estado === 'Activa') {
            estadoBadgeEl.innerHTML = `<span class="badge-estado-activa"><i class="fas fa-circle-check me-1"></i> Activa</span>`;
            estadoBtnEl.innerHTML = `
                <button type="button" class="btn btn-sm btn-outline-danger text-white border-white-50" onclick="confirmarDesactivarEncuesta(${encId}, '${safeNombre}')">
                    <i class="fas fa-ban me-1"></i> Desactivar
                </button>
            `;
        } else if (enc.estado === 'Borrador') {
            estadoBadgeEl.innerHTML = `<span class="badge-estado-borrador"><i class="fas fa-pen-ruler me-1"></i> Borrador</span>`;
            estadoBtnEl.innerHTML = `
                <button type="button" class="btn btn-sm btn-outline-success text-white border-white-50 me-1" onclick="confirmarActivarEncuesta(${encId}, '${safeNombre}', ${totalPreg})">
                    <i class="fas fa-check me-1"></i> Activar
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger text-white border-white-50" onclick="confirmarDesactivarEncuesta(${encId}, '${safeNombre}')">
                    <i class="fas fa-ban me-1"></i> Pasar a Inactiva
                </button>
            `;
        } else {
            estadoBadgeEl.innerHTML = `<span class="badge-estado-inactiva"><i class="fas fa-ban me-1"></i> Inactiva</span>`;
            estadoBtnEl.innerHTML = `
                <button type="button" class="btn btn-sm btn-outline-success text-white border-white-50" onclick="confirmarActivarEncuesta(${encId}, '${safeNombre}', ${totalPreg})">
                    <i class="fas fa-check me-1"></i> Reactivar
                </button>
            `;
        }
    }

    function renderizarPreguntasEnModal(preguntas, puedeModificar) {
        const contModal = document.getElementById('contadorPreguntasModal');
        if (contModal) contModal.textContent = preguntas.length;

        const contenedor = document.getElementById('listaPreguntasContainer');
        if (preguntas.length === 0) {
            contenedor.innerHTML = `
                <div class="text-center p-4 border rounded-4 bg-light text-muted small">
                    <i class="fas fa-question-circle fs-3 mb-2 d-block text-secondary"></i>
                    Aún no hay preguntas añadidas a esta encuesta. Usa el botón "<strong>Agregar Pregunta</strong>" para comenzar.
                </div>
            `;
            return;
        }

        const encId = (encuestaActualData ? (encuestaActualData.id_encuesta || encuestaActualData.id) : null)
            || document.getElementById('pregunta_encuesta_id')?.value
            || document.getElementById('gestionarEncuestaId')?.value;

        contenedor.innerHTML = preguntas.map((p, index) => {
            const pId = p.id_pregunta || p.id;
            let tipoLabel = '';
            let detalleTipo = '';
            const tipoNorm = (p.tipo || '').toLowerCase();

            if (tipoNorm === 'opcion_multiple') {
                tipoLabel = `<span class="badge bg-light text-primary border" style="font-size: 0.72rem;"><i class="fas fa-list-ul me-1"></i> Opción Múltiple</span>`;
                if (p.opciones && p.opciones.length > 0) {
                    detalleTipo = `<div class="mt-1 ps-2 small text-muted">${p.opciones.map(o => `<span class="badge bg-white text-dark border me-1">${o.texto}</span>`).join('')}</div>`;
                }
            } else if (tipoNorm === 'escala') {
                tipoLabel = `<span class="badge bg-light text-success border" style="font-size: 0.72rem;"><i class="fas fa-star-half-stroke me-1"></i> Escala (1 a 5)</span>`;
                detalleTipo = `<div class="mt-1 small text-muted">Escala fija: 1 a 5</div>`;
            } else {
                tipoLabel = `<span class="badge bg-light text-secondary border" style="font-size: 0.72rem;"><i class="fas fa-align-left me-1"></i> Texto Abierto</span>`;
            }

            const obligatoriaBadge = p.obligatoria ? `<span class="badge bg-danger-subtle text-danger border" style="font-size: 0.68rem;"><i class="fas fa-asterisk me-1"></i>Obligatoria</span>` : `<span class="badge bg-light text-muted border" style="font-size: 0.68rem;">Opcional</span>`;

            const accionesPregunta = puedeModificar ? `
                <div class="d-inline-flex align-items-center gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary border-0 rounded-circle" title="Editar pregunta" onclick="editarPreguntaClick(${pId})">
                        <i class="fas fa-pen-to-square"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle" title="Eliminar pregunta" onclick="eliminarPreguntaAjax(${encId}, ${pId})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            ` : '';

            const dragHandle = puedeModificar ? `
                <span class="pregunta-drag-handle me-2" title="Arrastrar para reordenar"><i class="fas fa-grip-vertical"></i></span>
            ` : '';

            return `
                <div class="pregunta-item-card d-flex align-items-center justify-content-between" data-id="${pId}">
                    <div class="d-flex align-items-center flex-grow-1">
                        ${dragHandle}
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                <span class="badge bg-light text-dark border rounded-pill" style="font-size: 0.7rem;">#${index + 1}</span>
                                <strong class="text-dark small">${p.texto}</strong>
                                ${tipoLabel}
                                ${obligatoriaBadge}
                            </div>
                            ${detalleTipo}
                        </div>
                    </div>
                    <div>
                        ${accionesPregunta}
                    </div>
                </div>
            `;
        }).join('');

        // Iniciar Sortable para preguntas si se pueden modificar
        if (puedeModificar && preguntas.length > 1) {
            iniciarSortablePreguntas();
        }
    }

    function iniciarSortablePreguntas() {
        const contenedor = document.getElementById('listaPreguntasContainer');
        if (!contenedor) return;

        if (sortablePreguntasInstance) sortablePreguntasInstance.destroy();

        sortablePreguntasInstance = new Sortable(contenedor, {
            handle: '.pregunta-drag-handle',
            animation: 200,
            ghostClass: 'bg-light',
            onEnd: function () {
                guardarReordenPreguntasAjax();
            }
        });
    }

    function guardarReordenPreguntasAjax() {
        const items = document.querySelectorAll('#listaPreguntasContainer .pregunta-item-card');
        const ids = Array.from(items).map(el => parseInt(el.getAttribute('data-id')));
        const encuestaId = document.getElementById('gestionarEncuestaId').value;

        fetch(`${RUTA_BASE_ENCUESTAS}/${encuestaId}/preguntas/reordenar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: ids })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                mostrarToastEncuesta('Orden de preguntas guardado correctamente.', 'success');
            }
        })
        .catch(err => console.error(err));
    }

    /* =========================================================================
       5. CONSTRUCTOR: AGREGAR Y EDITAR PREGUNTAS (AJAX)
       ========================================================================= */
    function toggleFormularioNuevaPregunta() {
        const cont = document.getElementById('contenedorFormularioNuevaPregunta');
        const estabaOculto = cont.classList.contains('d-none');
        if (estabaOculto) {
            limpiarFormularioPregunta();
            cont.classList.remove('d-none');
            document.getElementById('nueva_pregunta_texto').focus();
        } else {
            cancelarEdicionPregunta();
        }
    }

    function limpiarFormularioPregunta() {
        document.getElementById('editar_pregunta_id').value = '';
        document.getElementById('nueva_pregunta_texto').value = '';
        document.getElementById('nueva_pregunta_obligatoria').checked = true;
        const titForm = document.getElementById('tituloFormularioPregunta');
        if (titForm) titForm.innerHTML = '<i class="fas fa-pen-to-square text-success me-1"></i> Diseñar Nueva Pregunta';
        const txtBtn = document.getElementById('textoBotonGuardarPregunta');
        if (txtBtn) txtBtn.textContent = 'Guardar Pregunta';
        document.getElementById('spinnerGuardarPregunta')?.classList.add('d-none');
        document.getElementById('iconoGuardarPregunta')?.classList.remove('d-none');

        // Tipo por defecto: Opcion_multiple
        const radOpcion = document.getElementById('tipo_opcion_multiple');
        if (radOpcion) radOpcion.checked = true;
        cambiarTipoPregunta('Opcion_multiple');

        // Restaurar 2 filas de opciones limpias
        const contOpc = document.getElementById('contenedorFilasOpciones');
        contOpc.innerHTML = `
            <div class="input-group input-group-sm fila-opcion">
                <span class="input-group-text bg-light text-muted">1</span>
                <input type="text" class="form-control opcion-input" placeholder="Opción 1">
                <button class="btn btn-outline-danger" type="button" onclick="eliminarFilaOpcion(this)"><i class="fas fa-trash"></i></button>
            </div>
            <div class="input-group input-group-sm fila-opcion">
                <span class="input-group-text bg-light text-muted">2</span>
                <input type="text" class="form-control opcion-input" placeholder="Opción 2">
                <button class="btn btn-outline-danger" type="button" onclick="eliminarFilaOpcion(this)"><i class="fas fa-trash"></i></button>
            </div>
        `;
    }

    function cancelarEdicionPregunta() {
        document.getElementById('contenedorFormularioNuevaPregunta').classList.add('d-none');
        document.getElementById('editar_pregunta_id').value = '';
    }

    function cambiarTipoPregunta(tipo) {
        document.getElementById('seccionOpcionesMultiples').classList.add('d-none');
        document.getElementById('seccionEscala').classList.add('d-none');
        document.getElementById('seccionTextoAbierto').classList.add('d-none');

        const tipoNorm = (tipo || '').toLowerCase();

        if (tipoNorm === 'opcion_multiple') {
            document.getElementById('seccionOpcionesMultiples').classList.remove('d-none');
        } else if (tipoNorm === 'escala') {
            document.getElementById('seccionEscala').classList.remove('d-none');
        } else if (tipoNorm === 'texto') {
            document.getElementById('seccionTextoAbierto').classList.remove('d-none');
        }
    }

    function agregarFilaOpcion(valor = '') {
        const cont = document.getElementById('contenedorFilasOpciones');
        const count = cont.querySelectorAll('.fila-opcion').length + 1;
        const div = document.createElement('div');
        div.className = 'input-group input-group-sm fila-opcion';
        div.innerHTML = `
            <span class="input-group-text bg-light text-muted">${count}</span>
            <input type="text" class="form-control opcion-input" placeholder="Opción ${count}" value="${valor}">
            <button class="btn btn-outline-danger" type="button" onclick="eliminarFilaOpcion(this)"><i class="fas fa-trash"></i></button>
        `;
        cont.appendChild(div);
    }

    function eliminarFilaOpcion(btn) {
        const cont = document.getElementById('contenedorFilasOpciones');
        if (cont.querySelectorAll('.fila-opcion').length <= 2) {
            Swal.fire({
                icon: 'warning',
                title: 'Mínimo de opciones',
                text: 'Las preguntas de opción múltiple deben tener al menos 2 opciones.',
                confirmButtonColor: 'var(--petroleo-ecp)'
            });
            return;
        }
        btn.closest('.fila-opcion').remove();

        // Renumerar índices
        cont.querySelectorAll('.fila-opcion').forEach((fila, i) => {
            const idxBadge = fila.querySelector('.input-group-text');
            if (idxBadge) idxBadge.textContent = i + 1;
        });
    }

    function editarPreguntaClick(preguntaId) {
        if (!encuestaActualData || !encuestaActualData.preguntas) return;

        const p = encuestaActualData.preguntas.find(item => (item.id_pregunta || item.id) === preguntaId);
        if (!p) return;

        document.getElementById('editar_pregunta_id').value = preguntaId;
        document.getElementById('nueva_pregunta_texto').value = p.texto;
        document.getElementById('nueva_pregunta_obligatoria').checked = p.obligatoria !== false && p.obligatoria !== 0;

        const titForm = document.getElementById('tituloFormularioPregunta');
        if (titForm) titForm.innerHTML = `<i class="fas fa-pen-to-square text-success me-1"></i> Editar Pregunta`;
        const txtBtn = document.getElementById('textoBotonGuardarPregunta');
        if (txtBtn) txtBtn.textContent = 'Actualizar Pregunta';

        const tipoNorm = (p.tipo || '').toLowerCase();
        if (tipoNorm === 'opcion_multiple') {
            document.getElementById('tipo_opcion_multiple').checked = true;
            cambiarTipoPregunta('Opcion_multiple');

            const contOpc = document.getElementById('contenedorFilasOpciones');
            contOpc.innerHTML = '';
            if (p.opciones && p.opciones.length > 0) {
                p.opciones.forEach(o => agregarFilaOpcion(o.texto));
            } else {
                agregarFilaOpcion();
                agregarFilaOpcion();
            }
        } else if (tipoNorm === 'escala') {
            document.getElementById('tipo_escala').checked = true;
            cambiarTipoPregunta('Escala');
        } else {
            document.getElementById('tipo_texto').checked = true;
            cambiarTipoPregunta('Texto');
        }

        const contForm = document.getElementById('contenedorFormularioNuevaPregunta');
        contForm.classList.remove('d-none');
        contForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        document.getElementById('nueva_pregunta_texto').focus();
    }

    function guardarPreguntaAjax(e) {
        if (e && e.preventDefault) e.preventDefault();
        if (isGuardandoPregunta) return;

        const encuestaId = document.getElementById('pregunta_encuesta_id')?.value
            || document.getElementById('gestionarEncuestaId')?.value
            || (typeof encuestaActualData !== 'undefined' && encuestaActualData ? (encuestaActualData.id_encuesta || encuestaActualData.id) : null);

        if (!encuestaId) {
            Swal.fire({
                icon: 'error',
                title: 'Error de identificación',
                text: 'No se pudo identificar la encuesta seleccionada. Por favor recarga la página.',
                confirmButtonColor: 'var(--petroleo-ecp)'
            });
            return;
        }

        const preguntaIdEditar = document.getElementById('editar_pregunta_id')?.value || '';
        const inputTexto = document.getElementById('nueva_pregunta_texto');
        const texto = inputTexto ? inputTexto.value.trim() : '';
        const tipoRadio = document.querySelector('input[name="nuevo_tipo"]:checked');
        const tipoSeleccionado = tipoRadio ? tipoRadio.value : 'Texto';
        const checkOblig = document.getElementById('nueva_pregunta_obligatoria');
        const obligatoria = checkOblig ? checkOblig.checked : true;

        if (!texto) {
            Swal.fire({
                icon: 'warning',
                title: 'Campo obligatorio',
                text: 'Por favor escribe el texto de la pregunta.',
                confirmButtonColor: 'var(--petroleo-ecp)'
            });
            if (inputTexto) inputTexto.focus();
            return;
        }

        const payload = {
            texto: texto,
            tipo: tipoSeleccionado,
            obligatoria: obligatoria
        };

        if (tipoSeleccionado === 'Opcion_multiple' || tipoSeleccionado.toLowerCase() === 'opcion_multiple') {
            const inputs = document.querySelectorAll('#contenedorFilasOpciones .opcion-input');
            const opciones = Array.from(inputs).map(inp => inp.value.trim()).filter(val => val.length > 0);

            if (opciones.length < 2) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Opciones insuficientes',
                    text: 'Debes completar al menos 2 opciones de respuesta para las preguntas de opción múltiple.',
                    confirmButtonColor: 'var(--petroleo-ecp)'
                });
                return;
            }
            payload.opciones = opciones;
        }

        isGuardandoPregunta = true;
        const btn = document.getElementById('btnGuardarPreguntaAjax');
        if (btn) btn.disabled = true;
        document.getElementById('spinnerGuardarPregunta')?.classList.remove('d-none');
        document.getElementById('iconoGuardarPregunta')?.classList.add('d-none');
        const txtBtn = document.getElementById('textoBotonGuardarPregunta');
        if (txtBtn) txtBtn.textContent = 'Guardando...';

        const esEdicion = Boolean(preguntaIdEditar);
        const urlEndpoint = esEdicion
            ? `${RUTA_BASE_ENCUESTAS}/${encuestaId}/preguntas/${preguntaIdEditar}`
            : `${RUTA_BASE_ENCUESTAS}/${encuestaId}/preguntas`;
        const methodHttp = esEdicion ? 'PUT' : 'POST';

        fetch(urlEndpoint, {
            method: methodHttp,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(async res => {
            const data = await res.json().catch(() => ({}));
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Error en el servidor (' + res.status + ')');
            }
            return data;
        })
        .then(data => {
            if (esEdicion) {
                cancelarEdicionPregunta();
                mostrarToastEncuesta('Pregunta actualizada exitosamente.', 'success');
            } else {
                limpiarFormularioPregunta();
                document.getElementById('contenedorFormularioNuevaPregunta').classList.remove('d-none');
                document.getElementById('nueva_pregunta_texto').focus();
                mostrarToastEncuesta('¡Pregunta guardada! Puedes agregar otra a continuación.', 'success');
            }

            // Recargar listado de preguntas sin llamar a modal.show()
            recargarPreguntasModal(encuestaId);

            // Actualizar badge en la tabla principal
            const badgeTabla = document.getElementById(`badge-contador-${encuestaId}`);
            if (badgeTabla && data.total_preguntas !== undefined) {
                badgeTabla.innerHTML = `<i class="fas fa-list-ol text-success me-1"></i> ${data.total_preguntas}`;
            }
        })
        .catch(err => {
            Swal.fire({
                icon: 'error',
                title: 'No se pudo guardar la pregunta',
                text: err.message,
                confirmButtonColor: 'var(--petroleo-ecp)'
            });
        })
        .finally(() => {
            isGuardandoPregunta = false;
            if (btn) btn.disabled = false;
            document.getElementById('spinnerGuardarPregunta')?.classList.add('d-none');
            document.getElementById('iconoGuardarPregunta')?.classList.remove('d-none');
            const txtBtn = document.getElementById('textoBotonGuardarPregunta');
            if (txtBtn) {
                const editId = document.getElementById('editar_pregunta_id')?.value;
                txtBtn.textContent = editId ? 'Actualizar Pregunta' : 'Guardar Pregunta';
            }
        });
    }

    function eliminarPreguntaAjax(encuestaId, preguntaId) {
        Swal.fire({
            title: '¿Eliminar esta pregunta?',
            text: 'Esta acción quitará la pregunta de la encuesta.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: 'var(--petroleo-ecp)',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`${RUTA_BASE_ENCUESTAS}/${encuestaId}/preguntas/${preguntaId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) throw new Error(data.message || 'Error al eliminar');

                    // Recargar preguntas sin reabrir modal
                    recargarPreguntasModal(encuestaId);

                    const badgeTabla = document.getElementById(`badge-contador-${encuestaId}`);
                    if (badgeTabla && data.total_preguntas !== undefined) {
                        badgeTabla.innerHTML = `<i class="fas fa-list-ol text-success me-1"></i> ${data.total_preguntas}`;
                    }

                    mostrarToastEncuesta('Pregunta eliminada correctamente.', 'success');
                })
                .catch(err => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: err.message,
                        confirmButtonColor: 'var(--petroleo-ecp)'
                    });
                });
            }
        });
    }

    /* =========================================================================
       6. ACTIVAR Y DESACTIVAR CON SWEETALERT (AJAX)
       ========================================================================= */
    function confirmarActivarEncuesta(id, nombre, totalPreguntas) {
        if (totalPreguntas === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Preguntas requeridas',
                text: `La encuesta "${nombre}" no tiene ninguna pregunta registrada. Agrega al menos una pregunta antes de poder activarla.`,
                confirmButtonColor: 'var(--petroleo-ecp)',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        Swal.fire({
            title: '¿Activar encuesta?',
            html: `La encuesta <strong>"${nombre}"</strong> pasará a estado <strong>Activa</strong> y quedará disponible para vincularse a las actividades formativas del programa.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: 'var(--verde-ecp)',
            cancelButtonColor: 'var(--petroleo-ecp)',
            confirmButtonText: '<i class="fas fa-check me-1"></i> Sí, activar',
            cancelButtonText: 'Cancelar'
        }).then((res) => {
            if (res.isConfirmed) {
                Swal.fire({
                    title: 'Activando encuesta...',
                    text: 'Por favor espera un momento.',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                fetch(`${RUTA_BASE_ENCUESTAS}/${id}/activar`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(async res => {
                    const data = await res.json().catch(() => ({}));
                    return { ok: res.ok, status: res.status, data };
                })
                .then(({ ok, data }) => {
                    if (!ok || !data.success) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No se pudo activar la encuesta',
                            text: data.message || 'Ocurrió un error al intentar activar la encuesta.',
                            confirmButtonColor: 'var(--petroleo-ecp)'
                        });
                        return;
                    }

                    Swal.fire({
                        icon: 'success',
                        title: '¡Encuesta activada!',
                        text: data.message || 'La encuesta ha sido activada con éxito.',
                        confirmButtonColor: 'var(--verde-ecp)',
                        timer: 1800,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                })
                .catch(err => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo conectar con el servidor: ' + err.message,
                        confirmButtonColor: 'var(--petroleo-ecp)'
                    });
                });
            }
        });
    }

    function confirmarDesactivarEncuesta(id, nombre) {
        Swal.fire({
            title: '¿Desactivar encuesta?',
            html: `La encuesta <strong>"${nombre}"</strong> pasará a estado <strong>Inactiva</strong>.<br><br><small class="text-muted">Sus preguntas y respuestas históricas se mantendrán intactas (no se eliminarán).</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: 'var(--petroleo-ecp)',
            confirmButtonText: '<i class="fas fa-ban me-1"></i> Sí, desactivar',
            cancelButtonText: 'Cancelar'
        }).then((res) => {
            if (res.isConfirmed) {
                Swal.fire({
                    title: 'Desactivando encuesta...',
                    text: 'Comprobando actividades y sesiones del cronograma...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                fetch(`${RUTA_BASE_ENCUESTAS}/${id}/desactivar`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(async res => {
                    const data = await res.json().catch(() => ({}));
                    return { ok: res.ok, status: res.status, data };
                })
                .then(({ ok, data }) => {
                    if (!ok || !data.success) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No se puede desactivar la encuesta',
                            html: `
                                <div class="text-start p-2">
                                    <p class="mb-2 text-dark">${data.message || 'No fue posible desactivar la encuesta.'}</p>
                                    <div class="alert alert-light border small text-muted mb-0">
                                        <i class="fas fa-circle-info text-primary me-1"></i>
                                        <strong>¿Cómo resolverlo?</strong> Puedes ingresar a "Gestionar" (icono de lápiz) en esta encuesta y desmarcar la(s) actividad(es) asociada(s) para desvincularla(s), o esperar a que sus sesiones en el cronograma finalicen.
                                    </div>
                                </div>
                            `,
                            confirmButtonColor: 'var(--petroleo-ecp)',
                            confirmButtonText: 'Entendido'
                        });
                        return;
                    }

                    Swal.fire({
                        icon: 'success',
                        title: '¡Encuesta desactivada!',
                        text: data.message || 'La encuesta ha pasado a estado Inactiva.',
                        confirmButtonColor: 'var(--verde-ecp)',
                        timer: 1800,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                })
                .catch(err => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo conectar con el servidor: ' + err.message,
                        confirmButtonColor: 'var(--petroleo-ecp)'
                    });
                });
            }
        });
    }

    /* =========================================================================
       7. UTILIDADES (TOASTS)
       ========================================================================= */
    const ToastEncuesta = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3200,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    function mostrarToastEncuesta(msg, tipo = 'success') {
        ToastEncuesta.fire({
            icon: tipo,
            title: msg
        });
    }
</script>
@endpush
@endsection
