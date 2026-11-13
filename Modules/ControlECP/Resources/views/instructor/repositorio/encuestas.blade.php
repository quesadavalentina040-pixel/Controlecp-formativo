@extends('controlecp::components.layouts.dashboard')

@section('title', 'Mis encuestas')

@push('styles')
<style>
    .encuesta-instructor {
        --enc-verde: #39A900;
        --enc-petroleo: #001A29;
        --enc-borde: #e2e8f0;
        color: var(--enc-petroleo);
    }

    .encuesta-instructor .header-card {
        padding: 1.25rem 1.5rem;
        color: #fff;
        background: var(--enc-petroleo);
        border-radius: 24px;
    }

    .encuesta-instructor .btn-ecp-primary {
        color: #fff;
        background-color: var(--enc-verde);
        border-color: var(--enc-verde);
    }

    .encuesta-instructor .btn-ecp-primary:hover,
    .encuesta-instructor .btn-ecp-primary:focus-visible {
        color: #fff;
        background-color: #2e8b00;
        border-color: #2e8b00;
    }

    #modalCrearEncuesta .btn-ecp-primary {
        color: #fff;
        background-color: #39A900;
        border-color: #39A900;
    }

    #modalCrearEncuesta .btn-ecp-primary:hover,
    #modalCrearEncuesta .btn-ecp-primary:focus-visible {
        color: #fff;
        background-color: #2e8b00;
        border-color: #2e8b00;
    }

    .encuesta-instructor .kpi-card,
    .encuesta-instructor .content-card {
        background: #fff;
        border: 1px solid var(--enc-borde);
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(0, 26, 41, .05);
    }

    .encuesta-instructor .kpi-card {
        height: 100%;
        padding: 1rem 1.2rem;
        border: 2px solid var(--kpi-border);
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .encuesta-instructor .kpi-card:hover { transform: translateY(-2px); box-shadow: 0 7px 20px rgba(0, 26, 41, .08); }
    .encuesta-instructor .kpi-label { color: #64748b; font-size: .95rem; font-weight: 650; }
    .encuesta-instructor .kpi-value { margin-top: .5rem; color: var(--kpi-color); font-size: 1.8rem; font-weight: 800; }
    .encuesta-instructor .kpi-icon {
        display: inline-flex;
        width: 42px;
        height: 42px;
        align-items: center;
        justify-content: center;
        color: var(--kpi-color);
        background: var(--kpi-bg);
        border-radius: 50%;
    }

    .encuesta-instructor .filter-toolbar {
        padding: 1rem;
        background: var(--enc-petroleo);
        border-radius: 18px;
        box-shadow: 0 5px 16px rgba(0, 26, 41, .12);
    }

    .encuesta-instructor .filter-grid {
        display: grid;
        grid-template-columns: minmax(250px, 1.6fr) minmax(180px, .9fr) minmax(220px, 1fr);
        align-items: end;
        gap: .75rem;
    }

    .encuesta-instructor .filter-grid > * { min-width: 0; }
    .encuesta-instructor .filter-grid label { display: block; margin-bottom: .3rem; color: #fff; font-size: .78rem; font-weight: 700; }
    .encuesta-instructor .filter-grid .form-control,
    .encuesta-instructor .filter-grid .form-select { min-height: 46px; border-radius: 10px; }
    .encuesta-instructor .filter-actions { display: flex; gap: .6rem; }
    .encuesta-instructor .filter-actions .btn { min-height: 46px; white-space: nowrap; }
    .encuesta-instructor .btn-reset {
        display: inline-flex;
        width: 50px;
        align-items: center;
        justify-content: center;
        padding: 0;
        color: var(--enc-petroleo);
        background: #fff;
    }

    .encuesta-instructor .table thead th {
        color: #475569;
        background: #f8fafc;
        border-bottom: 2px solid var(--enc-borde);
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .encuesta-instructor .table td { vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
    .encuesta-instructor .status-active { color: #1b5e20; background: #d8f3dc; border: 1px solid #b7e4c7; }
    .encuesta-instructor .status-draft { color: #92400e; background: #fef3c7; border: 1px solid #fde68a; }
    .encuesta-instructor .status-inactive { color: #991b1b; background: #fee2e2; border: 1px solid #fecaca; }
    .survey-preview-modal .modal-content { overflow: hidden; border: 0; border-radius: 20px; box-shadow: 0 20px 55px rgba(0, 26, 41, .2); }
    .survey-preview-modal .modal-header { color: #fff; background: linear-gradient(135deg, #001a29, #00324d); border-bottom: 3px solid #39a900; }
    .survey-preview-modal .modal-title { color: #fff; font-weight: 750; }
    .survey-preview-modal .btn-outline-success {
        border-color: #39a900;
        border-radius: 8px;
        color: #278344;
    }
    .survey-preview-modal .btn-outline-success:hover,
    .survey-preview-modal .btn-outline-success:focus-visible {
        border-color: #39a900;
        background: #39a900;
        color: #fff;
    }
    .survey-edit-modal .modal-header { background: #001a29; border-bottom: 3px solid #39a900; }
    .survey-edit-modal .modal-body { background: #fff; }
    .survey-edit-modal .form-control,
    .survey-edit-modal .form-select { border-color: #dce4ec; }
    .survey-edit-modal .activity-picker { max-height: 140px; overflow-y: auto; }
    .survey-edit-modal .question-designer { border: 2px solid #39a900; border-radius: 16px; background: #f8fafc; }
    .survey-edit-modal .question-type-card {
        height: 100%;
        padding: .8rem .5rem;
        text-align: center;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        background: #fff;
        cursor: pointer;
    }
    .survey-edit-modal .question-type-radio:checked + .question-type-card {
        border-color: #39a900;
        background: #f0fbf4;
    }
    .survey-edit-modal .question-type-radio:checked + .question-type-card i { color: #39a900 !important; }
    .survey-edit-modal .option-list { border: 1px solid #dce4ec; border-radius: 14px; background: #fff; }
    .survey-edit-modal .option-row .form-control { min-width: 0; }
    .survey-edit-modal .question-item { border: 1px solid #e2e8f0; border-radius: 14px; background: #fff; }
    .survey-edit-modal .question-item + .question-item { margin-top: .75rem; }
    .survey-edit-modal .btn-ecp-primary,
    .survey-edit-modal .btn-primary {
        border-color: #39a900;
        border-radius: 8px !important;
        background: #39a900;
        color: #fff;
    }
    .survey-edit-modal .btn-ecp-primary:hover,
    .survey-edit-modal .btn-ecp-primary:focus-visible,
    .survey-edit-modal .btn-primary:hover,
    .survey-edit-modal .btn-primary:focus-visible {
        border-color: #2e8b00;
        background: #2e8b00;
        color: #fff;
    }
    .survey-edit-modal .btn-outline-success,
    .survey-edit-modal .btn-outline-secondary[data-bs-dismiss="modal"],
    .survey-edit-modal [data-cancel-survey-question] {
        border-color: #39a900;
        border-radius: 8px !important;
        color: #278344;
    }
    .survey-edit-modal .btn-outline-success:hover,
    .survey-edit-modal .btn-outline-secondary[data-bs-dismiss="modal"]:hover,
    .survey-edit-modal [data-cancel-survey-question]:hover {
        border-color: #39a900;
        background: #39a900;
        color: #fff;
    }
    .survey-preview-modal .status-active { color: #1b5e20; background: #d8f3dc; border: 1px solid #b7e4c7; }
    .survey-preview-modal .status-draft { color: #92400e; background: #fef3c7; border: 1px solid #fde68a; }
    .survey-preview-modal .status-inactive { color: #991b1b; background: #fee2e2; border: 1px solid #fecaca; }
    .survey-preview-modal .survey-question { padding: 1rem; border: 1px solid #e2e8f0; border-radius: 12px; background: #f8fafc; }
    .survey-preview-modal .survey-question + .survey-question { margin-top: .75rem; }
    .encuesta-instructor .survey-action-btn {
        display: inline-flex;
        width: 36px;
        height: 36px;
        align-items: center;
        justify-content: center;
        padding: 0;
        color: #0876c9;
        background: #eff7ff;
        border: 1px solid #cfe5fb;
        border-radius: 9px;
    }
    .encuesta-instructor .survey-action-btn:hover,
    .encuesta-instructor .survey-action-btn:focus-visible {
        color: #075da0;
        background: #e2f0ff;
        border-color: #9dcdf5;
    }
    .encuesta-instructor .survey-action-btn-edit {
        color: #64748b;
        background: #f1f5f9;
        border-color: #d7dee8;
    }
    .encuesta-instructor .survey-action-btn-edit:hover,
    .encuesta-instructor .survey-action-btn-edit:focus-visible {
        color: #475569;
        background: #e2e8f0;
        border-color: #cbd5e1;
    }
    .encuesta-instructor .survey-pagination .pagination { justify-content: center; margin-bottom: 0; }
    .encuesta-instructor .survey-pagination .page-link {
        min-width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #39a900;
        border-color: #d7e8d1;
        text-decoration: none;
    }
    .encuesta-instructor .survey-pagination .page-item.active .page-link {
        color: #fff;
        background: #39a900;
        border-color: #39a900;
    }
    .encuesta-instructor .survey-pagination .page-item.disabled .page-link { color: #9aa8b2; background: #f8fafc; }

    @media (max-width: 767.98px) {
        .encuesta-instructor .filter-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .encuesta-instructor .filter-actions { grid-column: 1 / -1; }
    }

    @media (max-width: 480px) {
        .encuesta-instructor .filter-grid { grid-template-columns: minmax(0, 1fr); }
        .encuesta-instructor .filter-actions { grid-column: auto; }
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid encuesta-instructor px-3 py-3">
    <div class="header-card d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small fw-semibold mb-1" style="color: var(--enc-verde);">
                <i class="fas fa-square-poll-vertical me-2"></i>Control ECP · Panel de instructor
            </div>
            <h1 class="h3 fw-bold mb-1">Gestión de Encuestas</h1>
            <p class="mb-0 small" style="color: #c7d4da;">Crea y administra encuestas para tus actividades formativas.</p>
        </div>
        <button class="btn btn-ecp-primary rounded-pill px-4" type="button" data-bs-toggle="modal" data-bs-target="#modalCrearEncuesta">
            <i class="fas fa-plus-circle me-1"></i>Nueva encuesta
        </button>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="row g-3 mb-4">
        @foreach([
            ['Total encuestas', $indicadores['total'], '#0284c7', '#e0f2fe', 'fa-square-poll-vertical', 'Registradas'],
            ['Activas', $indicadores['activas'], '#15803d', '#d8f3dc', 'fa-circle-check', 'Disponibles'],
            ['Borradores', $indicadores['borradores'], '#b45309', '#fef3c7', 'fa-pen-ruler', 'En diseño'],
            ['Inactivas', $indicadores['inactivas'], '#b91c1c', '#fee2e2', 'fa-ban', 'No disponibles'],
        ] as [$label, $value, $color, $background, $icon, $subtitle])
            <div class="col-6 col-lg-3">
                <div class="kpi-card" style="--kpi-color:{{ $color }};--kpi-bg:{{ $background }};--kpi-border:{{ $color }}55">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="kpi-label">{{ $label }}</span>
                        <span class="kpi-icon"><i class="fas {{ $icon }}"></i></span>
                    </div>
                    <div class="kpi-value">{{ $value }}</div>
                    <div class="small text-muted">{{ $subtitle }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <form method="GET" action="{{ route('controlecp.instructor.repositorio.encuestas') }}" class="filter-toolbar mb-4">
        <div class="filter-grid">
            <div>
                <label for="buscarEncuesta">Buscar encuesta</label>
                <input id="buscarEncuesta" name="buscar" class="form-control" value="{{ request('buscar') }}"
                       placeholder="Nombre o descripción">
            </div>
            <div>
                <label for="estadoEncuesta">Estado</label>
                <select id="estadoEncuesta" name="estado" class="form-select">
                    <option value="">Todos los estados</option>
                    @foreach(['Borrador', 'Activa', 'Inactiva'] as $estado)
                        <option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ $estado }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-actions">
                <button class="btn btn-ecp-primary flex-grow-1" type="submit"><i class="fas fa-search me-1"></i>Buscar</button>
                <a class="btn btn-reset" href="{{ route('controlecp.instructor.repositorio.encuestas') }}"
                   aria-label="Limpiar filtros" title="Limpiar filtros"><i class="fas fa-rotate-left"></i></a>
            </div>
        </div>
    </form>

    <section class="content-card overflow-hidden">
        <div class="p-4">
            <h2 class="h5 fw-bold mb-1">Encuestas del programa</h2>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr>
                    <th class="ps-4">Nombre de la encuesta</th><th>Actividades asociadas</th>
                    <th>Creada por</th><th class="text-center">Preguntas</th><th class="text-center">Estado</th>
                    <th>Fecha de creación</th><th class="text-center pe-4">Acciones</th>
                </tr></thead>
                <tbody>
                @forelse($encuestas as $encuesta)
                    @php
                        $claseEstado = ['Activa' => 'status-active', 'Borrador' => 'status-draft', 'Inactiva' => 'status-inactive'][$encuesta->estado] ?? 'status-inactive';
                    @endphp
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold">{{ $encuesta->nombre }}</div>
                            <div class="small text-muted">{{ \Illuminate\Support\Str::limit($encuesta->descripcion ?: 'Sin descripción', 90) }}</div>
                        </td>
                        <td>
                            @if($encuesta->actividades_count)
                                <span class="badge rounded-pill bg-light text-dark border">{{ $encuesta->actividades_count }} {{ $encuesta->actividades_count === 1 ? 'actividad' : 'actividades' }}</span>
                                <div class="small text-muted mt-1">{{ $encuesta->actividades->pluck('nombre')->take(2)->implode(', ') }}@if($encuesta->actividades_count > 2), …@endif</div>
                            @else
                                <span class="small text-muted">Sin actividades asignadas</span>
                            @endif
                        </td>
                        <td>{{ $encuesta->usuario?->nickname ?? $encuesta->usuario?->full_name ?? 'Usuario' }}</td>
                        <td class="text-center"><span class="badge rounded-pill bg-light text-dark border px-3">{{ $encuesta->preguntas_count }}</span></td>
                        <td class="text-center"><span class="badge rounded-pill {{ $claseEstado }}">{{ $encuesta->estado }}</span></td>
                        <td>
                            <div class="small fw-semibold">{{ $encuesta->fecha_creacion?->copy()->timezone('America/Bogota')->format('d/m/Y') ?? '—' }}</div>
                            <div class="small text-muted">{{ $encuesta->fecha_creacion?->copy()->timezone('America/Bogota')->format('h:i A') ?? '—' }}</div>
                        </td>
                        <td class="text-center pe-4">
                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                <button class="btn survey-action-btn" type="button" title="Ver encuesta" aria-label="Ver encuesta"
                                        data-bs-toggle="modal" data-bs-target="#verEncuesta{{ $encuesta->id_encuesta }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if((int) $encuesta->id_usuario === (int) auth()->id())
                                    <button class="btn survey-action-btn survey-action-btn-edit" type="button" title="Editar encuesta"
                                            aria-label="Editar encuesta" data-bs-toggle="modal"
                                            data-bs-target="#editarEncuesta{{ $encuesta->id_encuesta }}">
                                        <i class="fas fa-pen-to-square"></i>
                                    </button>
                                @elseif($encuesta->usuario?->hasRole('controlecp.admin'))
                                    <button class="btn survey-action-btn survey-action-btn-edit" type="button" title="Editar encuesta"
                                            aria-label="Editar encuesta" data-bs-toggle="modal"
                                            data-bs-target="#editarEncuesta{{ $encuesta->id_encuesta }}">
                                        <i class="fas fa-pen-to-square"></i>
                                    </button>
                                @elseif(in_array((int) $encuesta->id_encuesta, $encuestasTabulables, true))
                                    <a class="btn survey-action-btn" href="{{ route('controlecp.instructor.repositorio.tabulacion', ['id_encuesta' => $encuesta->id_encuesta]) }}"
                                       title="Tabular encuesta" aria-label="Tabular encuesta">
                                        <i class="fas fa-table-cells"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-5">
                        {{ request()->hasAny(['buscar', 'estado']) ? 'No se encontraron encuestas con esos filtros.' : 'Aún no hay encuestas registradas en el programa.' }}
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($encuestas->hasPages())
        <div class="survey-pagination p-3">
                <nav aria-label="Paginación de encuestas">
                    <ul class="pagination">
                        @if($encuestas->onFirstPage())
                            <li class="page-item disabled" aria-disabled="true">
                                <span class="page-link" aria-hidden="true">&lsaquo;</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $encuestas->previousPageUrl() }}" rel="prev" aria-label="Página anterior">&lsaquo;</a>
                            </li>
                        @endif

                        @foreach($encuestas->elements() as $element)
                            @if(is_string($element))
                                <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                            @endif

                            @if(is_array($element))
                                @foreach($element as $page => $url)
                                    @if($page == $encuestas->currentPage())
                                        <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                                    @else
                                        <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach

                        @if($encuestas->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $encuestas->nextPageUrl() }}" rel="next" aria-label="Página siguiente">&rsaquo;</a>
                            </li>
                        @else
                            <li class="page-item disabled" aria-disabled="true">
                                <span class="page-link" aria-hidden="true">&rsaquo;</span>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        @endif
    </section>
</div>

@foreach($encuestas as $encuesta)
    @php
        $claseEstado = ['Activa' => 'status-active', 'Borrador' => 'status-draft', 'Inactiva' => 'status-inactive'][$encuesta->estado] ?? 'status-inactive';
    @endphp
    <div class="modal fade survey-preview-modal" id="verEncuesta{{ $encuesta->id_encuesta }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-eye text-success"></i>
                        <h2 class="modal-title h5 mb-0">Detalle de la encuesta</h2>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3 pb-3 border-bottom">
                        <div>
                            <h3 class="h4 fw-bold mb-1">{{ $encuesta->nombre }}</h3>
                            <p class="text-muted mb-0">{{ $encuesta->descripcion ?: 'Sin descripción' }}</p>
                        </div>
                        <span class="badge rounded-pill {{ $claseEstado }}">{{ $encuesta->estado }}</span>
                    </div>
                    <div class="row g-2 small mb-4">
                        <div class="col-sm-6"><span class="text-muted">Creada:</span> <strong>{{ $encuesta->fecha_creacion?->copy()->timezone('America/Bogota')->format('d/m/Y h:i A') ?? '—' }}</strong></div>
                        <div class="col-sm-6"><span class="text-muted">Preguntas:</span> <strong>{{ $encuesta->preguntas_count }}</strong></div>
                        <div class="col-12">
                            <span class="text-muted">Actividades asociadas:</span>
                            <div class="mt-1">
                                @forelse($encuesta->actividades as $actividad)
                                    <span class="badge bg-light text-dark border me-1 mb-1">{{ $actividad->numero }}. {{ $actividad->nombre }}</span>
                                @empty
                                    <span class="text-muted fst-italic">Ninguna actividad asociada</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <h4 class="h6 fw-bold mb-3"><i class="fas fa-list-check text-success me-2"></i>Preguntas de la encuesta</h4>
                    @forelse($encuesta->preguntas as $pregunta)
                        <div class="survey-question">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                                <strong>{{ $loop->iteration }}. {{ $pregunta->texto }}</strong>
                                <span class="badge bg-white text-secondary border">{{ $pregunta->tipo === 'Opcion_multiple' ? 'Opción múltiple' : ($pregunta->tipo === 'Escala' ? 'Escala 1 a 5' : 'Texto abierto') }}</span>
                            </div>
                            <div class="small text-muted mt-2">{{ $pregunta->obligatoria ? 'Obligatoria' : 'Opcional' }}</div>
                            @if($pregunta->tipo === 'Opcion_multiple')
                                <div class="mt-2 ps-3 border-start">
                                    @forelse($pregunta->opciones as $opcion)
                                        <div class="small py-1"><i class="far fa-circle text-muted me-2"></i>{{ $opcion->texto }}</div>
                                    @empty
                                        <div class="small text-muted">Sin opciones registradas.</div>
                                    @endforelse
                                </div>
                            @elseif($pregunta->tipo === 'Escala')
                                <div class="d-flex gap-2 mt-2">
                                    @for($valor = 1; $valor <= 5; $valor++)
                                        <span class="badge rounded-pill bg-white text-secondary border">{{ $valor }}</span>
                                    @endfor
                                </div>
                            @else
                                <div class="form-control form-control-sm bg-white text-muted mt-2" aria-hidden="true">Respuesta abierta</div>
                            @endif
                        </div>
                    @empty
                        <div class="alert alert-light border text-center text-muted mb-0">Esta encuesta aún no contiene preguntas.</div>
                    @endforelse
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-success px-4" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    @if((int) $encuesta->id_usuario === (int) auth()->id() || $encuesta->usuario?->hasRole('controlecp.admin'))
        @php
            $tieneRespuestasEncuesta = (bool) ($encuesta->tiene_respuestas ?? false);
        @endphp
        <div class="modal fade survey-preview-modal survey-edit-modal" id="editarEncuesta{{ $encuesta->id_encuesta }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-sliders text-success"></i>
                            <h2 class="modal-title h5 mb-0">Gestionar Encuesta</h2>
                        </div>
                        <div class="d-flex align-items-center gap-3 ms-auto">
                            <span class="badge rounded-pill {{ $claseEstado }}">{{ $encuesta->estado }}</span>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                    </div>
                    <div class="modal-body p-4">
                        @if($tieneRespuestasEncuesta)
                            <div class="alert alert-warning border border-warning rounded-4 p-3 mb-4">
                                <div class="d-flex gap-2">
                                    <i class="fas fa-lock text-warning fs-4 mt-1"></i>
                                    <div>
                                        <strong class="d-block">Encuesta con respuestas registradas</strong>
                                        <span class="small">Para proteger los resultados, las preguntas y opciones quedan en solo lectura. Puedes modificar los datos generales de la encuesta.</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <form method="POST" action="{{ route('controlecp.instructor.encuestas.update', $encuesta->id_encuesta) }}" class="mb-4 pb-4 border-bottom">
                            @csrf @method('PUT')
                            <input type="hidden" name="survey_id" value="{{ $encuesta->id_encuesta }}">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Nombre de la Encuesta <span class="text-danger">*</span></label>
                                    <input name="nombre" class="form-control rounded-pill px-3" maxlength="255" value="{{ $encuesta->nombre }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Descripción</label>
                                    <input name="descripcion" class="form-control rounded-pill px-3" maxlength="2000" value="{{ $encuesta->descripcion }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold small">Actividades Asociadas (Reutilización por actividad)</label>
                                    <div class="activity-picker border rounded-4 p-3 bg-light">
                                        <div class="row g-2">
                                            @forelse($actividadesDisponibles as $actividad)
                                                @php
                                                    $actividadEncuestaId = (int) ($actividad->id_encuesta ?? $actividad->encuesta_id ?? 0);
                                                    $actividadAsociada = $actividadEncuestaId === (int) $encuesta->id_encuesta;
                                                    $actividadEditable = $actividadesInstructor->contains((int) $actividad->id)
                                                        && (!$actividadEncuestaId || $actividadAsociada);
                                                    $actividadBloqueada = ($actividadAsociada && !$actividadEditable)
                                                        || ($actividadEncuestaId && !$actividadAsociada);
                                                @endphp
                                                <div class="col-md-6">
                                                    <div class="form-check py-1">
                                                        @if($actividadAsociada && $actividadBloqueada)
                                                            <input type="hidden" name="actividades_ids[]" value="{{ $actividad->id }}">
                                                        @endif
                                                        <input class="form-check-input" type="checkbox" name="actividades_ids[]" value="{{ $actividad->id }}"
                                                               id="editAct{{ $encuesta->id_encuesta }}_{{ $actividad->id }}"
                                                               @checked($actividadAsociada) @disabled($actividadBloqueada)>
                                                        <label class="form-check-label small text-dark" for="editAct{{ $encuesta->id_encuesta }}_{{ $actividad->id }}">
                                                            <strong>{{ $actividad->numero }}.</strong> {{ $actividad->nombre }}
                                                            @if($actividadBloqueada)<span class="text-muted">(asociada a otra encuesta)</span>@endif
                                                        </label>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="col-12 text-muted small fst-italic">No hay actividades asignadas a tu cronograma.</div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-sm btn-ecp-primary px-4">
                                        <i class="fas fa-check me-1"></i>Guardar Cambios de la Encuesta
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                            <div>
                                <h3 class="h5 fw-bold mb-0"><i class="fas fa-circle-question text-success me-2"></i>Preguntas de la Encuesta</h3>
                                <small class="text-muted">Total: <strong>{{ $encuesta->preguntas->count() }}</strong> preguntas registradas</small>
                            </div>
                            @unless($tieneRespuestasEncuesta)
                                <button type="button" class="btn btn-sm btn-ecp-primary px-3"
                                        onclick="toggleSurveyQuestionForm('{{ $encuesta->id_encuesta }}')">
                                    <i class="fas fa-plus me-1"></i>Agregar Pregunta
                                </button>
                            @endunless
                        </div>

                        @unless($tieneRespuestasEncuesta)
                            <section class="question-designer p-4 mb-4" id="questionDesigner{{ $encuesta->id_encuesta }}">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h4 class="h6 fw-bold mb-0"><i class="fas fa-pen-to-square text-success me-1"></i>Diseñar Nueva Pregunta</h4>
                                    <button type="button" class="btn-close" aria-label="Cerrar"
                                            onclick="toggleSurveyQuestionForm('{{ $encuesta->id_encuesta }}')"></button>
                                </div>
                                <form method="POST" action="{{ route('controlecp.instructor.encuestas.preguntas.store', $encuesta->id_encuesta) }}"
                                      class="survey-question-form" data-survey-question-form>
                                    @csrf
                                    <input type="hidden" name="survey_id" value="{{ $encuesta->id_encuesta }}">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small">Texto de la Pregunta <span class="text-danger">*</span></label>
                                        <input type="text" name="texto" class="form-control rounded-pill px-3"
                                               placeholder="Ej: ¿Cómo calificaría la claridad y metodología del facilitador durante la actividad?"
                                               maxlength="1000" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small">Tipo de Respuesta <span class="text-danger">*</span></label>
                                        <div class="row g-2">
                                            @foreach([
                                                ['Opcion_multiple', 'fa-list-ul', 'Opción Múltiple', 'Mínimo 2 opciones'],
                                                ['Escala', 'fa-star', 'Escala Numérica', 'Fija de 1 a 5'],
                                                ['Texto', 'fa-align-left', 'Texto Abierto', 'Respuesta libre'],
                                            ] as [$tipo, $icono, $tituloTipo, $subtituloTipo])
                                                <div class="col-4">
                                                    <input class="d-none question-type-radio" type="radio" name="tipo"
                                                           id="tipo{{ $encuesta->id_encuesta }}{{ $tipo }}"
                                                           value="{{ $tipo }}" @checked($loop->first) data-survey-type>
                                                    <label for="tipo{{ $encuesta->id_encuesta }}{{ $tipo }}" class="question-type-card d-block">
                                                        <i class="fas {{ $icono }} fs-4 mb-1 d-block text-muted"></i>
                                                        <span class="d-block fw-semibold small text-dark">{{ $tituloTipo }}</span>
                                                        <span class="d-block text-muted" style="font-size:.7rem">{{ $subtituloTipo }}</span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" name="obligatoria"
                                               id="obligatoriaNueva{{ $encuesta->id_encuesta }}" value="1" checked>
                                        <label class="form-check-label fw-semibold small" for="obligatoriaNueva{{ $encuesta->id_encuesta }}">
                                            <i class="fas fa-asterisk text-danger me-1"></i>Pregunta obligatoria (el aprendiz debe responderla para completar la encuesta)
                                        </label>
                                    </div>
                                    <div class="option-list p-3 mb-3" data-survey-options>
                                        <label class="form-label fw-bold small d-flex justify-content-between align-items-center">
                                            <span>Opciones de Respuesta <span class="text-danger">*</span> (Mínimo 2)</span>
                                            <button type="button" class="btn btn-sm btn-outline-success px-2 py-1"
                                                    data-add-survey-option><i class="fas fa-plus me-1"></i>Añadir opción</button>
                                        </label>
                                        <div class="d-flex flex-column gap-2 mt-2" data-survey-option-rows>
                                            @foreach([1, 2] as $numeroOpcion)
                                                <div class="input-group input-group-sm option-row">
                                                    <span class="input-group-text bg-light text-muted">{{ $numeroOpcion }}</span>
                                                    <input type="text" class="form-control survey-option-input" placeholder="Opción {{ $numeroOpcion }}">
                                                    <button class="btn btn-outline-danger" type="button" data-remove-survey-option aria-label="Eliminar opción"><i class="fas fa-trash"></i></button>
                                                </div>
                                            @endforeach
                                        </div>
                                        <textarea class="d-none" name="opciones_texto" data-survey-options-value></textarea>
                                        <div class="small text-danger d-none mt-2" data-survey-option-error>
                                            Las preguntas de opción múltiple requieren al menos dos opciones.
                                        </div>
                                    </div>
                                    <div class="d-none mb-3" data-survey-scale>
                                        <div class="d-flex align-items-center gap-2 mb-2"><i class="fas fa-star text-warning"></i><strong class="small">Escala fija de calificación (1 a 5)</strong></div>
                                        <div class="d-flex gap-2">@foreach([1, 2, 3, 4, 5] as $valor)<span class="badge rounded-pill bg-white text-secondary border">{{ $valor }}</span>@endforeach</div>
                                    </div>
                                    <div class="d-none mb-3" data-survey-text><div class="form-control bg-white text-muted">Respuesta abierta del aprendiz...</div></div>
                                    <div class="d-flex justify-content-end gap-2 mt-3">
                                        <button class="btn btn-outline-secondary px-4" type="button" data-cancel-survey-question>Cancelar</button>
                                        <button class="btn btn-ecp-primary px-4" type="submit"><i class="fas fa-floppy-disk me-1"></i>Guardar Pregunta</button>
                                    </div>
                                </form>
                            </section>
                        @endunless

                        @forelse($encuesta->preguntas as $pregunta)
                            <article class="question-item p-3">
                                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                                    <div class="flex-grow-1">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                            <span class="badge rounded-pill bg-white text-secondary border">{{ str_replace('_', ' ', $pregunta->tipo) }}</span>
                                            <span class="badge rounded-pill {{ $pregunta->obligatoria ? 'text-bg-danger' : 'text-bg-secondary' }}">{{ $pregunta->obligatoria ? 'Obligatoria' : 'Opcional' }}</span>
                                        </div>
                                        <strong>{{ $pregunta->texto }}</strong>
                                        @if($pregunta->tipo === 'Opcion_multiple')
                                            <ul class="small text-muted mt-2 mb-0">
                                                @foreach($pregunta->opciones as $opcion)<li>{{ $opcion->texto }}</li>@endforeach
                                            </ul>
                                        @elseif($pregunta->tipo === 'Escala')
                                            <div class="small text-muted mt-2">Escala fija: 1 a 5</div>
                                        @else
                                            <div class="small text-muted mt-2">Respuesta abierta</div>
                                        @endif
                                    </div>
                                    @if(!$tieneRespuestasEncuesta)
                                        <form method="POST" action="{{ route('controlecp.instructor.encuestas.preguntas.destroy', [$encuesta->id_encuesta, $pregunta->id_pregunta]) }}"
                                              onsubmit="return confirm('¿Eliminar esta pregunta y sus opciones?');">
                                            @csrf @method('DELETE')
                                            <input type="hidden" name="survey_id" value="{{ $encuesta->id_encuesta }}">
                                            <button class="btn btn-sm btn-outline-danger rounded-pill" type="submit" title="Eliminar pregunta" aria-label="Eliminar pregunta"><i class="fas fa-trash"></i></button>
                                        </form>
                                    @endif
                                </div>
                                @unless($tieneRespuestasEncuesta)
                                    <details class="mt-3 pt-3 border-top">
                                        <summary class="small fw-semibold text-primary">Editar pregunta</summary>
                                        <form method="POST" action="{{ route('controlecp.instructor.encuestas.preguntas.update', [$encuesta->id_encuesta, $pregunta->id_pregunta]) }}" class="row g-3 mt-1">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="survey_id" value="{{ $encuesta->id_encuesta }}">
                                            <div class="col-md-8">
                                                <label class="form-label small fw-semibold">Texto</label>
                                                <input name="texto" class="form-control" maxlength="1000" value="{{ $pregunta->texto }}" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small fw-semibold">Tipo</label>
                                                <select name="tipo" class="form-select" required>
                                                    <option value="Opcion_multiple" @selected($pregunta->tipo === 'Opcion_multiple')>Opción múltiple</option>
                                                    <option value="Escala" @selected($pregunta->tipo === 'Escala')>Escala de 1 a 5</option>
                                                    <option value="Texto" @selected($pregunta->tipo === 'Texto')>Texto abierto</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label small fw-semibold">Opciones (una por línea)</label>
                                                <textarea name="opciones_texto" class="form-control" rows="2" maxlength="2000">@foreach($pregunta->opciones as $opcion){{ $opcion->texto }}@if(!$loop->last){{ "\n" }}@endif @endforeach</textarea>
                                            </div>
                                            <div class="col-md-7">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="obligatoria" value="1" id="obligatoria-{{ $encuesta->id_encuesta }}-{{ $pregunta->id_pregunta }}" @checked($pregunta->obligatoria)>
                                                    <label class="form-check-label fw-semibold" for="obligatoria-{{ $encuesta->id_encuesta }}-{{ $pregunta->id_pregunta }}">Pregunta obligatoria</label>
                                                </div>
                                            </div>
                                            <div class="col-md-5 text-md-end">
                                                <button class="btn btn-sm btn-primary px-3" type="submit"><i class="fas fa-save me-1"></i>Guardar pregunta</button>
                                            </div>
                                        </form>
                                    </details>
                                @endunless
                            </article>
                        @empty
                            <div class="alert alert-light border text-center text-muted mb-0">Esta encuesta aún no contiene preguntas.</div>
                        @endforelse
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-success px-4" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

<div class="modal fade" id="modalCrearEncuesta" tabindex="-1" aria-labelledby="modalCrearEncuestaTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header text-white" style="background:#001A29;border-bottom:3px solid #39A900">
                <h2 class="modal-title h5 fw-bold" id="modalCrearEncuestaTitle"><i class="fas fa-plus-circle me-2 text-success"></i>Nueva encuesta</h2>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form method="POST" action="{{ route('controlecp.instructor.encuestas.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <label class="form-label fw-semibold" for="nombreEncuesta">Nombre <span class="text-danger">*</span></label>
                    <input id="nombreEncuesta" name="nombre" class="form-control mb-3" maxlength="255" value="{{ old('nombre') }}" required>
                    <label class="form-label fw-semibold" for="descripcionEncuesta">Descripción</label>
                    <textarea id="descripcionEncuesta" name="descripcion" class="form-control" rows="3" maxlength="2000">{{ old('descripcion') }}</textarea>
                    <div class="form-text">Se guardará como borrador para que puedas agregar preguntas antes de activarla.</div>
                </div>
                <div class="modal-footer bg-light">
                    <button class="btn btn-outline-secondary rounded-pill" type="button" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-ecp-primary rounded-pill px-4" type="submit"><i class="fas fa-floppy-disk me-1"></i>Crear encuesta</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleSurveyQuestionForm(surveyId) {
        document.getElementById(`questionDesigner${surveyId}`)?.classList.toggle('d-none');
    }

    function renumberSurveyOptions(container) {
        container.querySelectorAll('[data-survey-option-rows] .option-row').forEach((row, index) => {
            row.querySelector('.input-group-text').textContent = String(index + 1);
            row.querySelector('.survey-option-input').placeholder = `Opción ${index + 1}`;
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-survey-question-form]').forEach((form) => {
            const optionsSection = form.querySelector('[data-survey-options]');
            const scaleSection = form.querySelector('[data-survey-scale]');
            const textSection = form.querySelector('[data-survey-text]');
            const optionsTextarea = form.querySelector('[data-survey-options-value]');
            const optionsError = form.querySelector('[data-survey-option-error]');

            const updateQuestionType = () => {
                const type = form.querySelector('[data-survey-type]:checked')?.value;
                optionsSection.classList.toggle('d-none', type !== 'Opcion_multiple');
                scaleSection.classList.toggle('d-none', type !== 'Escala');
                textSection.classList.toggle('d-none', type !== 'Texto');
            };

            form.querySelectorAll('[data-survey-type]').forEach((radio) => {
                radio.addEventListener('change', updateQuestionType);
            });

            form.querySelector('[data-add-survey-option]').addEventListener('click', () => {
                const rows = form.querySelector('[data-survey-option-rows]');
                const row = document.createElement('div');
                row.className = 'input-group input-group-sm option-row';

                const number = document.createElement('span');
                number.className = 'input-group-text bg-light text-muted';
                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'form-control survey-option-input';
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'btn btn-outline-danger';
                remove.setAttribute('data-remove-survey-option', '');
                remove.setAttribute('aria-label', 'Eliminar opción');
                remove.innerHTML = '<i class="fas fa-trash"></i>';

                row.append(number, input, remove);
                rows.append(row);
                renumberSurveyOptions(form);
                input.focus();
            });

            form.addEventListener('click', (event) => {
                if (event.target.closest('[data-cancel-survey-question]')) {
                    form.reset();
                    form.closest('.question-designer').classList.add('d-none');
                    updateQuestionType();
                    return;
                }

                const removeButton = event.target.closest('[data-remove-survey-option]');
                if (!removeButton) return;

                const rows = form.querySelector('[data-survey-option-rows]');
                if (rows.querySelectorAll('.option-row').length <= 2) {
                    return;
                }

                removeButton.closest('.option-row').remove();
                renumberSurveyOptions(form);
            });

            form.addEventListener('submit', (event) => {
                const type = form.querySelector('[data-survey-type]:checked')?.value;
                const options = Array.from(form.querySelectorAll('.survey-option-input'))
                    .map((input) => input.value.trim())
                    .filter(Boolean);

                if (type === 'Opcion_multiple' && options.length < 2) {
                    event.preventDefault();
                    optionsError.classList.remove('d-none');
                    form.querySelector('[data-survey-options]').scrollIntoView({ behavior: 'smooth', block: 'center' });
                    form.querySelector('.survey-option-input')?.focus();
                    return;
                }

                optionsError.classList.add('d-none');
                optionsTextarea.value = type === 'Opcion_multiple' ? options.join('\n') : '';
            });

            updateQuestionType();
        });

        const surveyId = @json(old('survey_id') ?? session('open_edit_modal'));
        if (surveyId) {
            const editModal = document.getElementById(`editarEncuesta${surveyId}`);
            if (editModal) {
                bootstrap.Modal.getOrCreateInstance(editModal).show();
            }
        }
    });
</script>
@endpush
@endsection
