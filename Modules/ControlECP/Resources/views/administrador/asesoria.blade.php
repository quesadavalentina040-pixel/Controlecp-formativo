
@extends('controlecp::components.layouts.dashboard')

@push('styles')

<style>
    :root {
        --verde: #39A900;
        --verde-claro: #d8f3dc;
        --petroleo: #001A29;
        --petroleo-medio: #00324D;
        --azul-claro: #d0e8f5;
        --gris-texto: #52796f;
        --gris-claro: #94a3b8;
        --borde: #e0ece4;
    }

    body {
        font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    }

    .font-outfit {
        font-family: 'Outfit', sans-serif;
    }

    /* Tarjetas con elevación suave */
    .ecp-card {
        background: #ffffff;
        border: 1px solid var(--borde);
        border-radius: 14px;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .ecp-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 26, 41, 0.08);
    }

    /* Botones temáticos */
    .btn-ecp-primary {
        background-color: var(--verde);
        color: #ffffff;
        border: 1px solid var(--verde);
        font-weight: 600;
        border-radius: 50rem;
        transition: all .2s ease;
    }

    .btn-ecp-primary:hover {
        background-color: #2e8b00;
        border-color: #2e8b00;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(57, 169, 0, 0.3);
    }

    .btn-ecp-outline {
        background-color: transparent;
        color: var(--petroleo);
        border: 1px solid var(--petroleo);
        font-weight: 500;
        border-radius: 50rem;
        transition: all .2s ease;
    }

    .btn-ecp-outline:hover {
        background-color: var(--petroleo);
        color: #ffffff;
    }

    /* Badges de estado */
    .badge-estado-solicitada {
        background-color: #fef3c7;
        color: #b45309;
        border: 1px solid #fde68a;
    }

    .badge-estado-programada {
        background-color: var(--azul-claro);
        color: #0369a1;
        border: 1px solid #bae6fd;
    }

    .badge-estado-realizada {
        background-color: var(--verde-claro);
        color: #15803d;
        border: 1px solid #bbf7d0;
    }

    .badge-estado-cancelada {
        background-color: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    .badge-estado-sin-asignar {
        background-color: #ffedd5;
        color: #c2410c;
        border: 1px solid #fed7aa;
    }

    /* Acciones en tabla */
    .btn-action-icon {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        border: 1px solid var(--borde);
        background: #ffffff;
        color: var(--petroleo);
        transition: all .2s ease;
    }

    .btn-action-icon:hover {
        background: var(--petroleo);
        color: #ffffff;
        border-color: var(--petroleo);
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-3 py-3">

    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
        @if(session('success'))
            <div class="toast align-items-center text-white border-0 show shadow" style="background-color: var(--verde);" role="alert" aria-live="assertive" aria-atomic="true">
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

        @if(session('info'))
            <div class="toast align-items-center text-white border-0 show shadow" style="background-color: var(--petroleo-medio);" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i class="fas fa-info-circle fs-5"></i>
                        <span>{{ session('info') }}</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
                </div>
            </div>
        @endif

        <div id="toastDinamico" class="toast align-items-center text-white border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true" style="display: none;">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i id="toastDinamicoIcon" class="fas fa-info-circle fs-5"></i>
                    <span id="toastDinamicoMensaje">Mensaje</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <h1 class="h3 fw-bold mb-1 font-outfit" style="color: var(--petroleo);">
                <i class="fas fa-comments me-2" style="color: var(--verde);"></i>Gestión de Asesorías
            </h1>
            <p class="mb-0 small" style="color: var(--gris-texto);">
                Asigna responsables y controla los encuentros de acompañamiento
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-ecp-primary px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevaSolicitud">
                <i class="fas fa-plus me-1"></i> Nueva solicitud
            </button>
            <button type="button" class="btn btn-ecp-outline px-3" data-bs-toggle="modal" data-bs-target="#modalDisponibilidad">
                <i class="fas fa-clock me-1"></i> Disponibilidad
            </button>
            <button type="button" class="btn btn-ecp-outline px-3" data-bs-toggle="modal" data-bs-target="#modalCategorias">
                <i class="fas fa-tags me-1"></i> Categorías
            </button>
            <button type="button" class="btn btn-ecp-outline px-3" data-bs-toggle="modal" data-bs-target="#modalReporte">
                <i class="fas fa-file-export me-1"></i> Generar reporte
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">

        <div class="col-6 col-md-4 col-lg">
            <div class="ecp-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold text-muted">Solicitadas</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background: #fef3c7; color: #b45309;">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 font-outfit" style="color: var(--petroleo);">{{ $indicadores['solicitadas'] }}</h3>
                <small class="text-muted" style="font-size: 0.75rem;">Pendientes por programar</small>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg">
            <div class="ecp-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold text-muted">Programadas</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background: var(--azul-claro); color: #0284c7;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 font-outfit" style="color: var(--petroleo);">{{ $indicadores['programadas'] }}</h3>
                <small class="text-muted" style="font-size: 0.75rem;">Con fecha e instructor</small>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg">
            <div class="ecp-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold text-muted">Realizadas</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background: var(--verde-claro); color: #15803d;">
                        <i class="fas fa-circle-check"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 font-outfit" style="color: var(--petroleo);">{{ $indicadores['realizadas'] }}</h3>
                <small class="text-muted" style="font-size: 0.75rem;">Con seguimiento final</small>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg">
            <div class="ecp-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold text-muted">Sin Asignar</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background: #ffedd5; color: #c2410c;">
                        <i class="fas fa-user-xmark"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 font-outfit" style="color: var(--petroleo);">{{ $indicadores['sin_asignar'] }}</h3>
                <small class="text-muted" style="font-size: 0.75rem;">Requieren instructor</small>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg">
            <div class="ecp-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold text-muted">Canceladas</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background: #fee2e2; color: #b91c1c;">
                        <i class="fas fa-ban"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 font-outfit" style="color: var(--petroleo);">{{ $indicadores['canceladas'] }}</h3>
                <small class="text-muted" style="font-size: 0.75rem;">Anuladas con motivo</small>
            </div>
        </div>
    </div>

    <div class="ecp-card p-3 mb-4">

        <form method="GET" action="{{ route('controlecp.administrador.asesorias.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">
                    <i class="fas fa-search me-1"></i> Buscar Aprendiz
                </label>
                <input type="text" name="aprendiz" value="{{ request('aprendiz') }}" class="form-control form-control-sm rounded-pill" placeholder="Nombre, correo o documento...">
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Categoría</label>
                <select name="categoria_id" class="form-select form-select-sm rounded-pill">
                    <option value="">Todas</option>

                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id_categoria_asesoria }}" {{ request('categoria_id') == $cat->id_categoria_asesoria ? 'selected' : '' }}>
                            {{ $cat->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Instructor</label>
                <select name="instructor_id" class="form-select form-select-sm rounded-pill">
                    <option value="">Todos</option>
                    <option value="sin_asignar" {{ request('instructor_id') === 'sin_asignar' ? 'selected' : '' }}>-- Sin Asignar --</option>
                    @foreach($instructores as $inst)
                        <option value="{{ $inst->id }}" {{ request('instructor_id') == $inst->id ? 'selected' : '' }}>
                            {{ $inst->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Estado</label>
                <select name="estado" class="form-select form-select-sm rounded-pill">
                    <option value="">Todos</option>
                    <option value="Solicitada" {{ request('estado') === 'Solicitada' ? 'selected' : '' }}>Solicitada</option>
                    <option value="Programada" {{ request('estado') === 'Programada' ? 'selected' : '' }}>Programada</option>
                    <option value="Realizada" {{ request('estado') === 'Realizada' ? 'selected' : '' }}>Realizada</option>
                    <option value="Cancelada" {{ request('estado') === 'Cancelada' ? 'selected' : '' }}>Cancelada</option>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Fecha</label>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="form-control form-control-sm rounded-pill mb-1" title="Desde">
            </div>

            <div class="col-12 col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-ecp-primary btn-sm flex-fill rounded-pill" title="Aplicar filtros">
                    <i class="fas fa-filter"></i>
                </button>
                <a href="{{ route('controlecp.administrador.asesorias.index') }}" class="btn btn-outline-secondary btn-sm flex-fill rounded-pill" title="Limpiar filtros">
                    <i class="fas fa-rotate"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="ecp-card mb-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background-color: var(--petroleo); color: #ffffff;">
                    <tr class="small text-uppercase" style="letter-spacing: 0.5px;">
                        <th class="ps-3 py-3">Aprendiz</th>
                        <th class="py-3">Categoría</th>
                        <th class="py-3">Instructor</th>
                        <th class="py-3">Fecha y Hora</th>
                        <th class="py-3 text-center">Estado</th>
                        <th class="pe-3 py-3 text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($asesorias as $item)
                        @php // Inicia un bloque PHP para preparar datos que usará la vista.
                            $badgeClass = match($item->estado) {
                                'Solicitada' => 'badge-estado-solicitada',
                                'Programada' => 'badge-estado-programada',
                                'Realizada'  => 'badge-estado-realizada',
                                'Cancelada'  => 'badge-estado-cancelada',
                                default      => 'badge-estado-sin-asignar',
                            };
                            $sinAsignar = is_null($item->id_usuario_instructor) && $item->estado !== 'Cancelada';
                        @endphp
                        <tr>

                            <td class="ps-3 py-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width: 34px; height: 34px; background-color: var(--petroleo-medio); font-size: 0.75rem;">
                                        {{ $item->aprendiz ? $item->aprendiz->initials : 'AP' }}
                                    </div>
                                    <div>
                                        <span class="d-block fw-semibold text-truncate" style="max-width: 200px; color: var(--petroleo);">
                                            {{ $item->aprendiz ? $item->aprendiz->full_name : 'No registrado' }}
                                        </span>
                                        <small class="text-muted d-block" style="font-size: 0.72rem;">
                                            {{ $item->aprendiz ? $item->aprendiz->email : '' }}
                                        </small>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="badge rounded-pill bg-light text-dark border px-2 py-1 small">
                                    {{ $item->categoria ? $item->categoria->nombre : 'Sin categoría' }}
                                </span>
                            </td>

                            <td>
                                @if($item->instructor)
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-chalkboard-user text-muted" style="font-size: 0.85rem;"></i>
                                        <span class="small fw-medium" style="color: var(--petroleo);">
                                            {{ $item->instructor->full_name }}
                                        </span>
                                    </div>
                                @else
                                    <span class="badge badge-estado-sin-asignar px-2 py-1 rounded-pill small">
                                        <i class="fas fa-user-xmark me-1"></i> Sin asignar
                                    </span>
                                @endif
                            </td>

                            <td>
                                <div class="small">
                                    <span class="d-block fw-semibold" style="color: var(--petroleo);">
                                        <i class="fas fa-calendar-day me-1 text-muted"></i>{{ $item->fecha ? $item->fecha->format('d/m/Y') : '-' }}
                                    </span>
                                    <span class="text-muted" style="font-size: 0.72rem;">
                                        <i class="fas fa-clock me-1 text-muted"></i>{{ substr($item->hora_inicio, 0, 5) }} - {{ substr($item->hora_fin, 0, 5) }}
                                    </span>
                                </div>
                            </td>

                            <td class="text-center">
                                <span class="badge {{ $badgeClass }} rounded-pill px-3 py-1 small">
                                    {{ $item->estado }}
                                </span>
                            </td>

                            <td class="pe-3 py-3 text-end">
                                <div class="d-inline-flex align-items-center gap-1">

                                    <button type="button" class="btn-action-icon" title="Ver detalle completo" onclick="verDetalle({{ $item->id_asesoria }})">
                                        <i class="fas fa-eye text-primary"></i>
                                    </button>

                                    @if($item->estado !== 'Cancelada')
                                        <button type="button" class="btn-action-icon" title="Editar asesoría" onclick="abrirEditar({{ $item->id_asesoria }}, {{ $item->id_usuario_aprendiz }}, {{ $item->id_categoria_asesoria }}, '{{ $item->fecha ? $item->fecha->format('Y-m-d') : '' }}', '{{ substr($item->hora_inicio, 0, 5) }}', '{{ substr($item->hora_fin, 0, 5) }}', {{ $item->id_usuario_instructor ?? 'null' }}, '{{ addslashes(preg_replace('/\r|\n/', ' ', $item->motivo)) }}')">
                                            <i class="fas fa-edit text-warning"></i>
                                        </button>
                                    @endif

                                    @if($item->estado === 'Solicitada')
                                        <button type="button" class="btn-action-icon" title="Asignar y Programar" onclick="abrirProgramar({{ $item->id_asesoria }}, '{{ addslashes($item->aprendiz ? $item->aprendiz->full_name : '') }}', {{ $item->id_categoria_asesoria }}, '{{ $item->fecha ? $item->fecha->format('Y-m-d') : '' }}', '{{ substr($item->hora_inicio, 0, 5) }}', '{{ substr($item->hora_fin, 0, 5) }}', {{ $item->id_usuario_instructor ?? 'null' }}, '{{ addslashes($item->categoria ? $item->categoria->nombre : '') }}')">
                                            <i class="fas fa-calendar-check" style="color: var(--verde);"></i>
                                        </button>
                                    @endif

                                    @if($item->estado === 'Programada')
                                        <button type="button" class="btn-action-icon" title="Finalizar asesoría" onclick="abrirFinalizar({{ $item->id_asesoria }}, '{{ addslashes($item->aprendiz ? $item->aprendiz->full_name : '') }}', '{{ addslashes($item->categoria ? $item->categoria->nombre : '') }}', '{{ $item->fecha ? $item->fecha->format('Y-m-d') : '' }}', '{{ substr($item->hora_inicio, 0, 5) }} - {{ substr($item->hora_fin, 0, 5) }}', '{{ addslashes($item->instructor ? $item->instructor->full_name : 'Sin asignar') }}')">
                                            <i class="fas fa-circle-check text-success"></i>
                                        </button>
                                    @endif

                                    <div class="dropdown d-inline-block">
                                        <button class="btn-action-icon dropdown-toggle dropdown-toggle-split" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Más opciones">
                                            <i class="fas fa-ellipsis-vertical text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 small" style="border-radius: 10px;">
                                            @if($item->estado !== 'Cancelada' && $item->estado !== 'Realizada')
                                                <li>
                                                    <a class="dropdown-item py-2" href="javascript:void(0)" onclick="abrirFinalizar({{ $item->id_asesoria }}, '{{ addslashes($item->aprendiz ? $item->aprendiz->full_name : '') }}', '{{ addslashes($item->categoria ? $item->categoria->nombre : '') }}', '{{ $item->fecha ? $item->fecha->format('Y-m-d') : '' }}', '{{ substr($item->hora_inicio, 0, 5) }} - {{ substr($item->hora_fin, 0, 5) }}', '{{ addslashes($item->instructor ? $item->instructor->full_name : 'Sin asignar') }}')">
                                                        <i class="fas fa-circle-check me-2 text-success"></i> Finalizar Asesoría
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item py-2" href="javascript:void(0)" onclick="abrirReprogramar({{ $item->id_asesoria }}, '{{ $item->fecha ? $item->fecha->format('Y-m-d') : '' }}', '{{ substr($item->hora_inicio, 0, 5) }}', '{{ substr($item->hora_fin, 0, 5) }}')">
                                                        <i class="fas fa-calendar-days me-2 text-warning"></i> Reprogramar
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item py-2" href="javascript:void(0)" onclick="abrirCancelar({{ $item->id_asesoria }})">
                                                        <i class="fas fa-ban me-2 text-danger"></i> Cancelar
                                                    </a>
                                                </li>
                                            @endif

                                            @if($item->estado === 'Programada' || $item->estado === 'Realizada')
                                                <li>
                                                    <a class="dropdown-item py-2" href="javascript:void(0)" onclick="abrirSeguimiento({{ $item->id_asesoria }})">
                                                        <i class="fas fa-clipboard-check me-2" style="color: var(--verde);"></i> Registrar Seguimiento
                                                    </a>
                                                </li>
                                            @endif

                                            @if($item->estado !== 'Cancelada')
                                                <li>
                                                    <a class="dropdown-item py-2" href="javascript:void(0)" onclick="abrirRecursos({{ $item->id_asesoria }})">
                                                        <i class="fas fa-boxes-stacked me-2 text-info"></i> Vincular Recursos
                                                    </a>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3 text-secondary opacity-50"></i>
                                    <h6 class="fw-bold text-secondary mb-1">No se encontraron asesorías</h6>
                                    <p class="small mb-3">No hay registros con los criterios de búsqueda actuales o aún no has creado una solicitud.</p>
                                    <button type="button" class="btn btn-ecp-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalNuevaSolicitud">
                                        <i class="fas fa-plus me-1"></i> Crear primera solicitud
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center p-3 border-top gap-2 bg-light">
            <span class="small text-muted">
                Mostrando {{ $asesorias->firstItem() ?? 0 }} a {{ $asesorias->lastItem() ?? 0 }} de {{ $asesorias->total() }} asesorías
            </span>
            <div>
                {{ $asesorias->links() }}
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="modalDetalle" tabindex="-1" aria-labelledby="modalDetalleLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--petroleo); border-bottom: 2px solid var(--verde);">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: rgba(57, 169, 0, 0.2);">
                        <i class="fas fa-info-circle text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 font-outfit" id="modalDetalleLabel">
                            Ficha de Asesoría #<span id="det-id">-</span>
                        </h5>
                        <small class="text-white-50" style="font-size: 0.75rem;">Trazabilidad, seguimientos y recursos vinculados</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span id="det-badge-estado" class="badge rounded-pill px-3 py-1">-</span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
            </div>

            <div class="modal-body p-4" style="background-color: #f8fafc;">
                <div id="det-cargando" class="text-center py-5">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="text-muted small mt-2">Consultando información...</p>
                </div>

                <div id="det-contenido" style="display: none;">

                    <div class="card border-0 shadow-sm mb-3" style="border-radius: 14px; border: 1px solid var(--borde) !important;">
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="rounded-circle bg-light p-2 text-muted">
                                            <i class="fas fa-user-graduate" style="color: var(--verde);"></i>
                                        </div>
                                        <div>
                                            <span class="text-muted small d-block">Aprendiz</span>
                                            <strong id="det-aprendiz-nombre" style="color: var(--petroleo);">-</strong>
                                            <span id="det-aprendiz-email" class="small text-muted d-block">-</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="rounded-circle bg-light p-2 text-muted">
                                            <i class="fas fa-chalkboard-user" style="color: #0284c7;"></i>
                                        </div>
                                        <div>
                                            <span class="text-muted small d-block">Instructor Asignado</span>
                                            <strong id="det-instructor-nombre" style="color: var(--petroleo);">-</strong>
                                            <span id="det-instructor-email" class="small text-muted d-block">-</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12"><hr class="my-1 text-muted opacity-25"></div>
                                <div class="col-md-4">
                                    <span class="text-muted small d-block">Categoría</span>
                                    <span id="det-categoria" class="badge rounded-pill bg-light text-dark border">-</span>
                                </div>
                                <div class="col-md-4">
                                    <span class="text-muted small d-block">Fecha y Horario</span>
                                    <strong id="det-fecha-hora" style="color: var(--petroleo);">-</strong>
                                </div>
                                <div class="col-md-4">
                                    <span class="text-muted small d-block">Fecha de Solicitud</span>
                                    <span id="det-fecha-solicitud" class="small text-muted">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-3" style="border-radius: 14px; border: 1px solid var(--borde) !important;">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-1" style="color: var(--petroleo); font-size: 0.9rem;">
                                <i class="fas fa-align-left me-1" style="color: var(--verde);"></i> Motivo de la Solicitud
                            </h6>
                            <p id="det-motivo" class="text-muted small mb-0 p-2 rounded bg-light" style="white-space: pre-line;">-</p>
                        </div>
                    </div>

                    <div id="det-contenedor-cancelacion" class="card border-0 shadow-sm mb-3" style="display: none; border-radius: 14px; background-color: #fef2f2; border: 1px solid #fecaca !important;">
                        <div class="card-body p-3">
                            <h6 class="fw-bold text-danger mb-1" style="font-size: 0.9rem;">
                                <i class="fas fa-ban me-1"></i> Motivo de Cancelación
                            </h6>
                            <p id="det-motivo-cancelacion" class="text-danger small mb-1" style="white-space: pre-line;">-</p>
                            <small class="text-muted">Cancelado por: <span id="det-cancelado-por">-</span></small>
                        </div>
                    </div>

                    <ul class="nav nav-pills nav-fill gap-2 mb-2 p-1 bg-white rounded-3 border" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active py-2 small fw-semibold" id="tab-seg" data-bs-toggle="pill" data-bs-target="#panel-seg" type="button" role="tab">
                                <i class="fas fa-clipboard-check me-1"></i> Seguimientos (<span id="det-count-seguimientos">0</span>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-2 small fw-semibold" id="tab-hist" data-bs-toggle="pill" data-bs-target="#panel-hist" type="button" role="tab">
                                <i class="fas fa-history me-1"></i> Historial (<span id="det-count-historial">0</span>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-2 small fw-semibold" id="tab-rec" data-bs-toggle="pill" data-bs-target="#panel-rec" type="button" role="tab">
                                <i class="fas fa-boxes-stacked me-1"></i> Recursos (<span id="det-count-recursos">0</span>)
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="panel-seg" role="tabpanel">
                            <div class="card border-0 shadow-sm p-3" style="border-radius: 14px;">
                                <div id="det-lista-seguimientos" class="d-flex flex-column gap-2"></div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="panel-hist" role="tabpanel">
                            <div class="card border-0 shadow-sm p-3" style="border-radius: 14px;">
                                <div id="det-lista-historial" class="d-flex flex-column gap-2"></div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="panel-rec" role="tabpanel">
                            <div class="card border-0 shadow-sm p-3" style="border-radius: 14px;">
                                <div id="det-lista-recursos"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-white border-top-0 px-4 py-3 d-flex justify-content-between align-items-center">
                <div>
                    <button type="button" class="btn btn-sm btn-success px-3 shadow-sm" id="det-btn-finalizar" style="display: none; background-color: #16a34a; border-color: #16a34a;" onclick="finalizarDesdeDetalle()">
                        <i class="fas fa-circle-check me-1"></i> Finalizar Asesoría
                    </button>
                </div>
                <button type="button" class="btn btn-ecp-outline px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNuevaSolicitud" tabindex="-1" aria-labelledby="modalNuevaSolicitudLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--petroleo); border-bottom: 2px solid var(--verde);">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: rgba(57, 169, 0, 0.2);">
                        <i class="fas fa-calendar-plus text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 font-outfit" id="modalNuevaSolicitudLabel">Nueva Solicitud de Asesoría</h5>
                        <small class="text-white-50" style="font-size: 0.75rem;">Registra el requerimiento de acompañamiento del aprendiz</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form method="POST" action="{{ route('controlecp.administrador.asesorias.store') }}" id="formNuevaSolicitud">
                @csrf
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    @if($errors->any() && session('modal_abrir') === 'modalNuevaSolicitud')
                        <div class="alert alert-danger alert-dismissible fade show rounded-3 small p-3 mb-3 border-0 shadow-sm" role="alert">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Corrige los siguientes errores:</strong>
                            </div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    @endif

                    <div class="row g-3">

                        <div class="col-12 col-md-6">
                            <label for="sol_aprendiz" class="form-label small fw-semibold text-muted mb-1">
                                <i class="fas fa-user-graduate me-1" style="color: var(--verde);"></i> Aprendiz <span class="text-danger">*</span>
                            </label>
                            <select name="id_usuario_aprendiz" id="sol_aprendiz" class="form-select form-select-sm rounded-pill" required>
                                <option value="">-- Selecciona el aprendiz --</option>
                                @foreach($aprendices as $ap)
                                    <option value="{{ $ap->id }}" {{ old('id_usuario_aprendiz') == $ap->id ? 'selected' : '' }}>
                                        {{ $ap->full_name }} ({{ $ap->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="sol_categoria" class="form-label small fw-semibold text-muted mb-1">
                                <i class="fas fa-tag me-1" style="color: var(--verde);"></i> Categoría Temática <span class="text-danger">*</span>
                            </label>
                            <select name="id_categoria_asesoria" id="sol_categoria" class="form-select form-select-sm rounded-pill" required onchange="consultarInstructoresDisponibles('sol')">
                                <option value="">-- Selecciona la categoría --</option>
                                @foreach($categorias->where('estado', 'Activa') as $cat)
                                    <option value="{{ $cat->id_categoria_asesoria }}" {{ old('id_categoria_asesoria') == $cat->id_categoria_asesoria ? 'selected' : '' }}>
                                        {{ $cat->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="sol_fecha" class="form-label small fw-semibold text-muted mb-1">
                                <i class="fas fa-calendar-day me-1" style="color: var(--verde);"></i> Fecha <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="fecha" id="sol_fecha" value="{{ old('fecha', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" class="form-control form-control-sm rounded-pill" required onchange="consultarInstructoresDisponibles('sol')">
                        </div>

                        <div class="col-6 col-md-4">
                            <label for="sol_hora_inicio" class="form-label small fw-semibold text-muted mb-1">
                                <i class="fas fa-clock me-1" style="color: var(--verde);"></i> Hora Inicio <span class="text-danger">*</span>
                            </label>
                            <input type="time" name="hora_inicio" id="sol_hora_inicio" value="{{ old('hora_inicio', '08:00') }}" class="form-control form-control-sm rounded-pill" required onchange="consultarInstructoresDisponibles('sol')">
                        </div>

                        <div class="col-6 col-md-4">
                            <label for="sol_hora_fin" class="form-label small fw-semibold text-muted mb-1">
                                <i class="fas fa-clock me-1" style="color: var(--verde);"></i> Hora Fin <span class="text-danger">*</span>
                            </label>
                            <input type="time" name="hora_fin" id="sol_hora_fin" value="{{ old('hora_fin', '09:00') }}" class="form-control form-control-sm rounded-pill" required onchange="consultarInstructoresDisponibles('sol')">
                        </div>

                        <div class="col-12">
                            <div class="p-3 border rounded-3 bg-white shadow-sm">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label for="sol_instructor" class="form-label small fw-semibold mb-0" style="color: var(--petroleo);">
                                        <i class="fas fa-chalkboard-user me-1" style="color: #0284c7;"></i> Instructor Disponible (Opcional)
                                    </label>
                                    <span id="sol_badge_disponibles" class="badge rounded-pill bg-light text-muted border small">
                                        Selecciona categoría y horario
                                    </span>
                                </div>
                                <select name="id_usuario_instructor" id="sol_instructor" class="form-select form-select-sm rounded-pill">
                                    <option value="">-- Dejar sin asignar (Solicitada) --</option>
                                </select>
                                <div id="sol_alerta_disponibilidad" class="small mt-2" style="display: none;"></div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="sol_motivo" class="form-label small fw-semibold text-muted mb-1">
                                <i class="fas fa-align-left me-1" style="color: var(--verde);"></i> Motivo de la Solicitud <span class="text-danger">*</span>
                            </label>
                            <textarea name="motivo" id="sol_motivo" rows="3" class="form-control form-control-sm rounded-3" placeholder="Describe la necesidad o temática a tratar..." required minlength="5">{{ old('motivo') }}</textarea>
                            <small class="text-muted" style="font-size: 0.72rem;">Mínimo 5 caracteres.</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-white border-top-0 px-4 py-3 gap-2">
                    <button type="button" class="btn btn-ecp-outline px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-ecp-primary px-4 shadow-sm">
                        <i class="fas fa-save me-1"></i> Guardar Solicitud
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarAsesoria" tabindex="-1" aria-labelledby="modalEditarAsesoriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--petroleo); border-bottom: 2px solid var(--verde);">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: rgba(57, 169, 0, 0.2);">
                        <i class="fas fa-edit text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 font-outfit" id="modalEditarAsesoriaLabel">
                            Editar Asesoría #<span id="edit_asesoria_numero">-</span>
                        </h5>
                        <small class="text-white-50" style="font-size: 0.75rem;">Modifica los datos del requerimiento o reasigna instructor</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form method="POST" id="formEditarAsesoria" action="">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_asesoria_id" name="asesoria_id" value="">
                <input type="hidden" id="edit_categoria_id" value="">

                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    @if($errors->any() && session('modal_abrir') === 'modalEditarAsesoria')
                        <div class="alert alert-danger alert-dismissible fade show rounded-3 small p-3 mb-3 border-0 shadow-sm" role="alert">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Corrige los siguientes errores:</strong>
                            </div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    @endif

                    <div class="row g-3">

                        <div class="col-12 col-md-6">
                            <label for="edit_aprendiz" class="form-label small fw-semibold text-muted mb-1">
                                <i class="fas fa-user-graduate me-1" style="color: var(--verde);"></i> Aprendiz <span class="text-danger">*</span>
                            </label>
                            <select name="id_usuario_aprendiz" id="edit_aprendiz" class="form-select form-select-sm rounded-pill" required>
                                <option value="">-- Selecciona el aprendiz --</option>
                                @foreach($aprendices as $ap)
                                    <option value="{{ $ap->id }}" {{ old('id_usuario_aprendiz') == $ap->id ? 'selected' : '' }}>
                                        {{ $ap->full_name }} ({{ $ap->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="edit_categoria" class="form-label small fw-semibold text-muted mb-1">
                                <i class="fas fa-tag me-1" style="color: var(--verde);"></i> Categoría Temática <span class="text-danger">*</span>
                            </label>
                            <select name="id_categoria_asesoria" id="edit_categoria" class="form-select form-select-sm rounded-pill" required onchange="consultarInstructoresDisponibles('edit')">
                                <option value="">-- Selecciona la categoría --</option>
                                @foreach($categorias->where('estado', 'Activa') as $cat)
                                    <option value="{{ $cat->id_categoria_asesoria }}" {{ old('id_categoria_asesoria') == $cat->id_categoria_asesoria ? 'selected' : '' }}>
                                        {{ $cat->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="edit_fecha" class="form-label small fw-semibold text-muted mb-1">
                                <i class="fas fa-calendar-day me-1" style="color: var(--verde);"></i> Fecha <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="fecha" id="edit_fecha" value="{{ old('fecha') }}" class="form-control form-control-sm rounded-pill" required onchange="consultarInstructoresDisponibles('edit')">
                        </div>

                        <div class="col-6 col-md-4">
                            <label for="edit_hora_inicio" class="form-label small fw-semibold text-muted mb-1">
                                <i class="fas fa-clock me-1" style="color: var(--verde);"></i> Hora Inicio <span class="text-danger">*</span>
                            </label>
                            <input type="time" name="hora_inicio" id="edit_hora_inicio" value="{{ old('hora_inicio') }}" class="form-control form-control-sm rounded-pill" required onchange="consultarInstructoresDisponibles('edit')">
                        </div>

                        <div class="col-6 col-md-4">
                            <label for="edit_hora_fin" class="form-label small fw-semibold text-muted mb-1">
                                <i class="fas fa-clock me-1" style="color: var(--verde);"></i> Hora Fin <span class="text-danger">*</span>
                            </label>
                            <input type="time" name="hora_fin" id="edit_hora_fin" value="{{ old('hora_fin') }}" class="form-control form-control-sm rounded-pill" required onchange="consultarInstructoresDisponibles('edit')">
                        </div>

                        <div class="col-12">
                            <div class="p-3 border rounded-3 bg-white shadow-sm">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label for="edit_instructor" class="form-label small fw-semibold mb-0" style="color: var(--petroleo);">
                                        <i class="fas fa-chalkboard-user me-1" style="color: #0284c7;"></i> Instructor Disponible (Opcional)
                                    </label>
                                    <span id="edit_badge_disponibles" class="badge rounded-pill bg-light text-muted border small">
                                        Selecciona categoría y horario
                                    </span>
                                </div>
                                <select name="id_usuario_instructor" id="edit_instructor" class="form-select form-select-sm rounded-pill">
                                    <option value="">-- Dejar sin asignar (Solicitada) --</option>
                                </select>
                                <div id="edit_alerta_disponibilidad" class="small mt-2" style="display: none;"></div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="edit_motivo" class="form-label small fw-semibold text-muted mb-1">
                                <i class="fas fa-align-left me-1" style="color: var(--verde);"></i> Motivo de la Solicitud <span class="text-danger">*</span>
                            </label>
                            <textarea name="motivo" id="edit_motivo" rows="3" class="form-control form-control-sm rounded-3" placeholder="Describe la necesidad o temática a tratar..." required minlength="5">{{ old('motivo') }}</textarea>
                            <small class="text-muted" style="font-size: 0.72rem;">Mínimo 5 caracteres.</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-white border-top-0 px-4 py-3 gap-2">
                    <button type="button" class="btn btn-ecp-outline px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-ecp-primary px-4 shadow-sm">
                        <i class="fas fa-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalProgramar" tabindex="-1" aria-labelledby="modalProgramarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--petroleo); border-bottom: 2px solid var(--verde);">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: rgba(57, 169, 0, 0.2);">
                        <i class="fas fa-calendar-check text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 font-outfit" id="modalProgramarLabel">Asignar y Programar Asesoría</h5>
                        <small class="text-white-50" style="font-size: 0.75rem;">Asigna un instructor libre y reserva la franja horaria</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form method="POST" id="formProgramar" action="">
                @csrf
                @method('PUT')
                <input type="hidden" id="prog_asesoria_id" name="asesoria_id" value="">
                <input type="hidden" id="prog_categoria_id" value="">

                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    @if($errors->any() && session('modal_abrir') === 'modalProgramar')
                        <div class="alert alert-danger alert-dismissible fade show rounded-3 small p-3 mb-3 border-0 shadow-sm" role="alert">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Error al programar:</strong>
                            </div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    @endif

                    <div class="p-3 border rounded-3 bg-white mb-3 shadow-sm">
                        <span class="small text-muted d-block">Aprendiz</span>
                        <strong id="prog_aprendiz_nombre" class="d-block mb-1" style="color: var(--petroleo);">-</strong>
                        <span class="small text-muted d-block">Categoría</span>
                        <span id="prog_categoria_nombre" class="badge rounded-pill bg-light text-dark border small">-</span>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-12 col-md-4">
                            <label for="prog_fecha" class="form-label small fw-semibold text-muted mb-1">Fecha</label>
                            <input type="date" name="fecha" id="prog_fecha" class="form-control form-control-sm rounded-pill" required onchange="consultarInstructoresDisponibles('prog')">
                        </div>
                        <div class="col-6 col-md-4">
                            <label for="prog_hora_inicio" class="form-label small fw-semibold text-muted mb-1">Hora Inicio</label>
                            <input type="time" name="hora_inicio" id="prog_hora_inicio" class="form-control form-control-sm rounded-pill" required onchange="consultarInstructoresDisponibles('prog')">
                        </div>
                        <div class="col-6 col-md-4">
                            <label for="prog_hora_fin" class="form-label small fw-semibold text-muted mb-1">Hora Fin</label>
                            <input type="time" name="hora_fin" id="prog_hora_fin" class="form-control form-control-sm rounded-pill" required onchange="consultarInstructoresDisponibles('prog')">
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="prog_instructor" class="form-label small fw-semibold text-muted mb-0">
                                Instructor Disponible <span class="text-danger">*</span>
                            </label>
                            <span id="prog_badge_disponibles" class="badge rounded-pill bg-light text-muted border small">Consultando...</span>
                        </div>
                        <select name="id_usuario_instructor" id="prog_instructor" class="form-select form-select-sm rounded-pill" required>
                            <option value="">-- Selecciona el instructor --</option>
                        </select>
                        <div id="prog_alerta_disponibilidad" class="small mt-2" style="display: none;"></div>
                    </div>
                </div>

                <div class="modal-footer bg-white border-top-0 px-4 py-3 gap-2">
                    <button type="button" class="btn btn-ecp-outline px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-ecp-primary px-4 shadow-sm">
                        <i class="fas fa-check-circle me-1"></i> Confirmar y Programar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCategorias" tabindex="-1" aria-labelledby="modalCategoriasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--petroleo); border-bottom: 2px solid var(--verde);">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: rgba(57, 169, 0, 0.2);">
                        <i class="fas fa-tags text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 font-outfit" id="modalCategoriasLabel">Categorías de Asesoría</h5>
                        <small class="text-white-50" style="font-size: 0.75rem;">Creación de áreas temáticas y asignación de instructores</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-4" style="background-color: #f8fafc;">
                @if($errors->any() && session('modal_abrir') === 'modalCategorias')
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 small p-3 mb-3 border-0 shadow-sm" role="alert">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Error al guardar categoría:</strong>
                        </div>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('controlecp.administrador.asesorias.categorias.store') }}" id="formCrearCategoria" class="mb-4">
                    @csrf
                    <div class="card border-0 shadow-sm p-3 rounded-3 bg-white">
                        <h6 class="fw-bold mb-3" style="color: var(--petroleo);">
                            <i class="fas fa-plus-circle me-1" style="color: var(--verde);"></i> Nueva Categoría Temática
                        </h6>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="cat_nombre" class="form-label small fw-semibold text-muted mb-1">
                                    Nombre de la Categoría <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="nombre" id="cat_nombre" class="form-control form-control-sm rounded-pill" placeholder="Ej: Paz y Convivencia, Liderazgo..." required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    Instructores Asignados a esta Área
                                </label>
                                <div class="p-2 border rounded-3 bg-light overflow-auto" style="max-height: 120px;">
                                    @forelse($instructores as $inst)
                                        <div class="form-check form-check-sm mb-1">
                                            <input class="form-check-input" type="checkbox" name="instructores[]" value="{{ $inst->id }}" id="cat_inst_{{ $inst->id }}">
                                            <label class="form-check-label small" for="cat_inst_{{ $inst->id }}">
                                                {{ $inst->full_name }} ({{ $inst->email }})
                                            </label>
                                        </div>
                                    @empty
                                        <span class="text-muted small">No hay instructores disponibles.</span>
                                    @endforelse
                                </div>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-ecp-primary btn-sm px-4 shadow-sm">
                                    <i class="fas fa-save me-1"></i> Guardar Categoría
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <div id="cat_contenedor_edicion" class="card border-0 shadow-sm p-3 rounded-3 bg-white mb-3" style="display: none; border-left: 4px solid var(--verde) !important;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0" style="color: var(--petroleo);"><i class="fas fa-edit me-1 text-primary"></i> Editar Categoría</h6>
                        <button type="button" class="btn-close btn-sm" onclick="cancelarEdicionCategoria()"></button>
                    </div>

                    <form method="POST" id="formEditarCategoria" action="">
                        @csrf
                        @method('PUT')
                        <div class="row g-2">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">Nombre de la Categoría <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" id="edit_cat_nombre" class="form-control form-control-sm rounded-pill" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">Instructores Asignados</label>
                                <div class="p-2 border rounded-3 bg-light overflow-auto" style="max-height: 110px;">
                                    @foreach($instructores as $inst)
                                        <div class="form-check form-check-sm mb-1">
                                            <input class="form-check-input edit-cat-inst" type="checkbox" name="instructores[]" value="{{ $inst->id }}" id="edit_cat_inst_{{ $inst->id }}">
                                            <label class="form-check-label small" for="edit_cat_inst_{{ $inst->id }}">{{ $inst->full_name }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-12 text-end">
                                <button type="button" class="btn btn-ecp-outline btn-sm px-3 me-1" onclick="cancelarEdicionCategoria()">Cancelar</button>
                                <button type="submit" class="btn btn-ecp-primary btn-sm px-4 shadow-sm">Guardar Cambios</button>
                            </div>
                        </div>
                    </form>
                </div>

                <h6 class="fw-bold mb-2 font-outfit" style="color: var(--petroleo);">
                    <i class="fas fa-list me-1" style="color: #0284c7;"></i> Categorías Registradas ({{ $categorias->count() }})
                </h6>
                <div class="table-responsive rounded-3 border bg-white shadow-sm">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead class="table-light small">
                            <tr>
                                <th class="ps-3 py-2">Categoría</th>
                                <th class="py-2">Instructores Vinculados</th>
                                <th class="py-2 text-center">Estado</th>
                                <th class="pe-3 py-2 text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($categorias as $c)
                                <tr>
                                    <td class="ps-3 py-2 fw-semibold" style="color: var(--petroleo);">
                                        <i class="fas fa-tag me-1 text-muted"></i>{{ $c->nombre }}
                                    </td>
                                    <td class="py-2">
                                        <span class="badge bg-light text-dark border">{{ $c->instructores_count }} instructor(es)</span>
                                    </td>
                                    <td class="py-2 text-center">
                                        <span class="badge {{ $c->estado === 'Activa' ? 'badge-estado-realizada' : 'badge-estado-cancelada' }} rounded-pill" style="font-size: 0.7rem;">
                                            {{ $c->estado }}
                                        </span>
                                    </td>
                                    <td class="pe-3 py-2 text-end">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <button type="button" class="btn-action-icon" title="Editar categoría" onclick="editarCategoria({{ $c->id_categoria_asesoria }}, '{{ addslashes($c->nombre) }}', {{ json_encode($c->instructores->pluck('id')) }})">
                                                <i class="fas fa-edit text-primary"></i>
                                            </button>

                                            <form method="POST" action="{{ route('controlecp.administrador.asesorias.categorias.estado', $c->id_categoria_asesoria) }}" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn-action-icon" title="{{ $c->estado === 'Activa' ? 'Desactivar categoría' : 'Activar categoría' }}">
                                                    <i class="fas {{ $c->estado === 'Activa' ? 'fa-toggle-on text-success' : 'fa-toggle-off text-muted' }}"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">No hay categorías registradas aún.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer bg-white border-top-0 px-4 py-3">
                <button type="button" class="btn btn-ecp-outline px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDisponibilidad" tabindex="-1" aria-labelledby="modalDisponibilidadLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--petroleo); border-bottom: 2px solid var(--verde);">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: rgba(57, 169, 0, 0.2);">
                        <i class="fas fa-clock text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 font-outfit" id="modalDisponibilidadLabel">Disponibilidad de Instructores</h5>
                        <small class="text-white-50" style="font-size: 0.75rem;">Define franjas horarias semanales por año y trimestre</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-4" style="background-color: #f8fafc;">
                @if($errors->any() && session('modal_abrir') === 'modalDisponibilidad')
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 small p-3 mb-3 border-0 shadow-sm" role="alert">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Error al guardar franja:</strong>
                        </div>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('controlecp.administrador.asesorias.disponibilidad.guardar') }}" id="formGuardarDisponibilidad" class="mb-4">
                    @csrf
                    <div class="card border-0 shadow-sm p-3 rounded-3 bg-white">
                        <h6 class="fw-bold mb-3" style="color: var(--petroleo);">
                            <i class="fas fa-calendar-plus me-1" style="color: var(--verde);"></i> Agregar Franja Horaria
                        </h6>
                        <div class="row g-2">

                            <div class="col-12 col-md-6">
                                <label for="disp_usuario" class="form-label small fw-semibold text-muted mb-1">Instructor <span class="text-danger">*</span></label>
                                <select name="id_usuario" id="disp_usuario" class="form-select form-select-sm rounded-pill" required onchange="cargarDisponibilidadesInstructor(this.value)">
                                    <option value="">-- Selecciona el instructor --</option>
                                    @foreach($instructores as $inst)
                                        <option value="{{ $inst->id }}">{{ $inst->full_name }} ({{ $inst->email }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="disp_dia" class="form-label small fw-semibold text-muted mb-1">Día de la Semana <span class="text-danger">*</span></label>
                                <select name="dia_semana" id="disp_dia" class="form-select form-select-sm rounded-pill" required>
                                    <option value="1">Lunes</option>
                                    <option value="2">Martes</option>
                                    <option value="3">Miércoles</option>
                                    <option value="4">Jueves</option>
                                    <option value="5">Viernes</option>
                                    <option value="6">Sábado</option>
                                    <option value="7">Domingo</option>
                                </select>
                            </div>

                            <div class="col-6 col-md-3">
                                <label for="disp_anio" class="form-label small fw-semibold text-muted mb-1">Año <span class="text-danger">*</span></label>
                                <input type="number" name="anio" id="disp_anio" value="{{ date('Y') }}" class="form-control form-control-sm rounded-pill" required min="2024" max="2035">
                            </div>
                            <div class="col-6 col-md-3">
                                <label for="disp_trimestre" class="form-label small fw-semibold text-muted mb-1">Trimestre <span class="text-danger">*</span></label>
                                <select name="trimestre" id="disp_trimestre" class="form-select form-select-sm rounded-pill" required>
                                    @php $trimActual = ceil(date('n') / 3); @endphp
                                    <option value="1" {{ $trimActual == 1 ? 'selected' : '' }}>T1 (Ene - Mar)</option>
                                    <option value="2" {{ $trimActual == 2 ? 'selected' : '' }}>T2 (Abr - Jun)</option>
                                    <option value="3" {{ $trimActual == 3 ? 'selected' : '' }}>T3 (Jul - Sep)</option>
                                    <option value="4" {{ $trimActual == 4 ? 'selected' : '' }}>T4 (Oct - Dic)</option>
                                </select>
                            </div>

                            <div class="col-6 col-md-3">
                                <label for="disp_hora_inicio" class="form-label small fw-semibold text-muted mb-1">Hora Inicio <span class="text-danger">*</span></label>
                                <input type="time" name="hora_inicio" id="disp_hora_inicio" value="08:00" class="form-control form-control-sm rounded-pill" required>
                            </div>
                            <div class="col-6 col-md-3">
                                <label for="disp_hora_fin" class="form-label small fw-semibold text-muted mb-1">Hora Fin <span class="text-danger">*</span></label>
                                <input type="time" name="hora_fin" id="disp_hora_fin" value="12:00" class="form-control form-control-sm rounded-pill" required>
                            </div>

                            <div class="col-12 text-end mt-2">
                                <button type="submit" class="btn btn-ecp-primary btn-sm px-4 shadow-sm">
                                    <i class="fas fa-save me-1"></i> Guardar Franja
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <h6 class="fw-bold mb-2 font-outfit" style="color: var(--petroleo);">
                    <i class="fas fa-calendar-check me-1" style="color: #0284c7;"></i> Franjas Registradas para el Instructor
                </h6>
                <div id="disp_lista_franjas" class="p-3 border rounded-3 bg-white shadow-sm">
                    <p class="text-muted small text-center my-2">Selecciona un instructor para ver sus franjas.</p>
                </div>
            </div>

            <div class="modal-footer bg-white border-top-0 px-4 py-3">
                <button type="button" class="btn btn-ecp-outline px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalReprogramar" tabindex="-1" aria-labelledby="modalReprogramarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--petroleo); border-bottom: 2px solid var(--verde);">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: rgba(57, 169, 0, 0.2);">
                        <i class="fas fa-calendar-days text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 font-outfit" id="modalReprogramarLabel">Reprogramar Asesoría</h5>
                        <small class="text-white-50" style="font-size: 0.75rem;">Modifica la fecha y horario conservando la trazabilidad</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form method="POST" id="formReprogramar" action="">
                @csrf
                @method('PUT')
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    @if($errors->any() && session('modal_abrir') === 'modalReprogramar')
                        <div class="alert alert-danger alert-dismissible fade show rounded-3 small p-3 mb-3 border-0 shadow-sm" role="alert">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Error al reprogramar:</strong>
                            </div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    @endif

                    <div class="row g-2 mb-3">
                        <div class="col-12 col-md-4">
                            <label for="reprog_fecha" class="form-label small fw-semibold text-muted mb-1">Nueva Fecha <span class="text-danger">*</span></label>
                            <input type="date" name="fecha" id="reprog_fecha" class="form-control form-control-sm rounded-pill" required min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-6 col-md-4">
                            <label for="reprog_hora_inicio" class="form-label small fw-semibold text-muted mb-1">Hora Inicio <span class="text-danger">*</span></label>
                            <input type="time" name="hora_inicio" id="reprog_hora_inicio" class="form-control form-control-sm rounded-pill" required>
                        </div>
                        <div class="col-6 col-md-4">
                            <label for="reprog_hora_fin" class="form-label small fw-semibold text-muted mb-1">Hora Fin <span class="text-danger">*</span></label>
                            <input type="time" name="hora_fin" id="reprog_hora_fin" class="form-control form-control-sm rounded-pill" required>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="reprog_motivo" class="form-label small fw-semibold text-muted mb-1">Motivo de Reprogramación <span class="text-danger">*</span></label>
                        <textarea name="motivo" id="reprog_motivo" rows="3" class="form-control form-control-sm rounded-3" placeholder="Explica la causa del cambio de fecha/horario..." required minlength="5"></textarea>
                        <small class="text-muted" style="font-size:0.72rem;">Quedará guardado en el historial de trazabilidad.</small>
                    </div>
                </div>

                <div class="modal-footer bg-white border-top-0 px-4 py-3 gap-2">
                    <button type="button" class="btn btn-ecp-outline px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-ecp-primary px-4 shadow-sm">
                        <i class="fas fa-calendar-check me-1"></i> Guardar Reprogramación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCancelar" tabindex="-1" aria-labelledby="modalCancelarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: #b91c1c; border-bottom: 2px solid #ef4444;">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: rgba(255, 255, 255, 0.2);">
                        <i class="fas fa-ban text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 font-outfit" id="modalCancelarLabel">Cancelar Asesoría</h5>
                        <small class="text-white-50" style="font-size: 0.75rem;">Anulación formal del encuentro de acompañamiento</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form method="POST" id="formCancelar" action="">
                @csrf
                @method('PUT')
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    @if($errors->any() && session('modal_abrir') === 'modalCancelar')
                        <div class="alert alert-danger alert-dismissible fade show rounded-3 small p-3 mb-3 border-0 shadow-sm" role="alert">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Error al cancelar:</strong>
                            </div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    @endif

                    <div class="alert alert-warning border-0 shadow-sm small p-3 mb-3 rounded-3">
                        <i class="fas fa-triangle-exclamation me-1"></i>
                        Esta acción anulará el encuentro y liberará el horario del instructor. El motivo quedará registrado con tu usuario.
                    </div>

                    <div class="mb-2">
                        <label for="canc_motivo" class="form-label small fw-semibold text-muted mb-1">Motivo de Cancelación <span class="text-danger">*</span></label>
                        <textarea name="motivo" id="canc_motivo" rows="3" class="form-control form-control-sm rounded-3" placeholder="Indica la razón detallada de la cancelación..." required minlength="5"></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-white border-top-0 px-4 py-3 gap-2">
                    <button type="button" class="btn btn-ecp-outline px-4" data-bs-dismiss="modal">Volver</button>
                    <button type="submit" class="btn btn-danger px-4 shadow-sm">
                        <i class="fas fa-ban me-1"></i> Confirmar Cancelación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalFinalizar" tabindex="-1" aria-labelledby="modalFinalizarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: #15803d; border-bottom: 2px solid #22c55e;">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: rgba(255, 255, 255, 0.2);">
                        <i class="fas fa-circle-check text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 font-outfit" id="modalFinalizarLabel">Finalizar Asesoría</h5>
                        <small class="text-white-50" style="font-size: 0.75rem;">Marcar la asesoría como completada / realizada</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form method="POST" id="formFinalizar" action="">
                @csrf
                @method('PUT')
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    @if($errors->any() && session('modal_abrir') === 'modalFinalizar')
                        <div class="alert alert-danger alert-dismissible fade show rounded-3 small p-3 mb-3 border-0 shadow-sm" role="alert">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Error al finalizar:</strong>
                            </div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    @endif

                    <div class="alert alert-success border-0 shadow-sm small p-3 mb-3 rounded-3" style="background-color: #dcfce7; color: #166534;">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="fas fa-circle-check fs-5"></i>
                            <span class="fw-bold">¿Confirmas que esta asesoría fue realizada y deseas finalizarla?</span>
                        </div>
                        <div class="small">
                            El estado pasará a <span class="badge badge-estado-realizada rounded-pill px-2 py-1">Realizada</span> y se notificará a los participantes correspondientes.
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm p-3 mb-3 rounded-3 bg-white">
                        <div class="row g-2 small">
                            <div class="col-6">
                                <span class="text-muted d-block" style="font-size: 0.72rem;">N° ASESORÍA:</span>
                                <span class="fw-bold text-dark" id="fin_asesoria_numero">#</span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block" style="font-size: 0.72rem;">FECHA Y HORARIO:</span>
                                <span class="fw-bold text-dark" id="fin_asesoria_horario">-</span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block" style="font-size: 0.72rem;">APRENDIZ:</span>
                                <span class="fw-semibold text-dark" id="fin_asesoria_aprendiz">-</span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block" style="font-size: 0.72rem;">INSTRUCTOR:</span>
                                <span class="fw-semibold text-dark" id="fin_asesoria_instructor">-</span>
                            </div>
                            <div class="col-12">
                                <span class="text-muted d-block" style="font-size: 0.72rem;">CATEGORÍA:</span>
                                <span class="badge bg-light text-dark border px-2 py-1" id="fin_asesoria_categoria">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="fin_observaciones" class="form-label small fw-semibold text-muted mb-1">
                            Observaciones o conclusiones de la sesión <span class="text-muted fw-normal">(opcional)</span>
                        </label>
                        <textarea name="observaciones" id="fin_observaciones" rows="3" class="form-control form-control-sm rounded-3" placeholder="Ingresa notas o conclusiones sobre el desarrollo de la asesoría..."></textarea>
                        <small class="text-muted" style="font-size: 0.72rem;">Si agregas observaciones, se guardarán en el seguimiento de la asesoría.</small>
                    </div>
                </div>

                <div class="modal-footer bg-white border-top-0 px-4 py-3 gap-2">
                    <button type="button" class="btn btn-ecp-outline px-4" data-bs-dismiss="modal">Volver</button>
                    <button type="submit" class="btn btn-success px-4 shadow-sm" style="background-color: #16a34a; border-color: #16a34a;">
                        <i class="fas fa-circle-check me-1"></i> Confirmar y Finalizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSeguimiento" tabindex="-1" aria-labelledby="modalSeguimientoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--petroleo); border-bottom: 2px solid var(--verde);">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: rgba(57, 169, 0, 0.2);">
                        <i class="fas fa-clipboard-check text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 font-outfit" id="modalSeguimientoLabel">Registrar Seguimiento</h5>
                        <small class="text-white-50" style="font-size: 0.75rem;">Consigna los avances y compromisos (pasa a Realizada)</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form method="POST" id="formSeguimiento" action="">
                @csrf
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    @if($errors->any() && session('modal_abrir') === 'modalSeguimiento')
                        <div class="alert alert-danger alert-dismissible fade show rounded-3 small p-3 mb-3 border-0 shadow-sm" role="alert">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Error al registrar seguimiento:</strong>
                            </div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="seg_avances" class="form-label small fw-semibold text-muted mb-1">Avances del Aprendiz <span class="text-danger">*</span></label>
                        <textarea name="avances" id="seg_avances" rows="3" class="form-control form-control-sm rounded-3" placeholder="Describe los logros, temas revisados y competencias trabajadas..." required minlength="5"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="seg_compromisos" class="form-label small fw-semibold text-muted mb-1">Compromisos y Tareas <span class="text-danger">*</span></label>
                        <textarea name="compromisos" id="seg_compromisos" rows="3" class="form-control form-control-sm rounded-3" placeholder="Puntos acordados para la siguiente sesión o entrega..." required minlength="5"></textarea>
                    </div>

                    <div class="mb-2">
                        <label for="seg_observaciones" class="form-label small fw-semibold text-muted mb-1">Observaciones Adicionales (Opcional)</label>
                        <textarea name="observaciones" id="seg_observaciones" rows="2" class="form-control form-control-sm rounded-3" placeholder="Comentarios generales o recomendaciones..."></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-white border-top-0 px-4 py-3 gap-2">
                    <button type="button" class="btn btn-ecp-outline px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-ecp-primary px-4 shadow-sm">
                        <i class="fas fa-save me-1"></i> Guardar Seguimiento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCorregirSeguimiento" tabindex="-1" aria-labelledby="modalCorregirSeguimientoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--petroleo); border-bottom: 2px solid #eab308;">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: rgba(234, 179, 8, 0.2);">
                        <i class="fas fa-pen-to-square text-warning"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 font-outfit" id="modalCorregirSeguimientoLabel">Corregir Seguimiento</h5>
                        <small class="text-white-50" style="font-size: 0.75rem;">Crea una nueva versión enlazada conservando la auditoría original</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form method="POST" id="formCorregirSeguimiento" action="">
                @csrf
                @method('PUT')
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    @if($errors->any() && session('modal_abrir') === 'modalCorregirSeguimiento')
                        <div class="alert alert-danger alert-dismissible fade show rounded-3 small p-3 mb-3 border-0 shadow-sm" role="alert">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Error al corregir:</strong>
                            </div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    @endif

                    <div class="alert alert-info border-0 shadow-sm small p-3 mb-3 rounded-3">
                        <i class="fas fa-info-circle me-1"></i>
                        Esta acción <strong>nunca borra ni sobrescribe</strong> el seguimiento anterior; genera un nuevo registro que apunta al original.
                    </div>

                    <div class="mb-3">
                        <label for="corr_motivo" class="form-label small fw-semibold text-danger mb-1">Motivo de la Corrección <span class="text-danger">*</span></label>
                        <input type="text" name="motivo_correccion" id="corr_motivo" class="form-control form-control-sm rounded-pill" placeholder="Ej: Ajuste en compromisos solicitados por el aprendiz..." required minlength="5">
                    </div>

                    <div class="mb-3">
                        <label for="corr_avances" class="form-label small fw-semibold text-muted mb-1">Avances Corregidos <span class="text-danger">*</span></label>
                        <textarea name="avances" id="corr_avances" rows="3" class="form-control form-control-sm rounded-3" required minlength="5"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="corr_compromisos" class="form-label small fw-semibold text-muted mb-1">Compromisos Corregidos <span class="text-danger">*</span></label>
                        <textarea name="compromisos" id="corr_compromisos" rows="3" class="form-control form-control-sm rounded-3" required minlength="5"></textarea>
                    </div>

                    <div class="mb-2">
                        <label for="corr_observaciones" class="form-label small fw-semibold text-muted mb-1">Observaciones</label>
                        <textarea name="observaciones" id="corr_observaciones" rows="2" class="form-control form-control-sm rounded-3"></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-white border-top-0 px-4 py-3 gap-2">
                    <button type="button" class="btn btn-ecp-outline px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning px-4 shadow-sm text-dark fw-bold">
                        <i class="fas fa-check-circle me-1"></i> Guardar Corrección
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRecursos" tabindex="-1" aria-labelledby="modalRecursosLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--petroleo); border-bottom: 2px solid var(--verde);">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: rgba(57, 169, 0, 0.2);">
                        <i class="fas fa-boxes-stacked text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 font-outfit" id="modalRecursosLabel">Vincular Recurso de Inventario</h5>
                        <small class="text-white-50" style="font-size: 0.75rem;">Asigna materiales y descuenta el stock en tiempo real</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form method="POST" id="formRecursos" action="">
                @csrf
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    @if($errors->any() && session('modal_abrir') === 'modalRecursos')
                        <div class="alert alert-danger alert-dismissible fade show rounded-3 small p-3 mb-3 border-0 shadow-sm" role="alert">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Error al vincular recurso:</strong>
                            </div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="rec_inventario" class="form-label small fw-semibold text-muted mb-1">Elemento del Inventario <span class="text-danger">*</span></label>
                        <select name="id_inventario" id="rec_inventario" class="form-select form-select-sm rounded-pill" required>
                            <option value="">-- Selecciona el recurso --</option>
                            @foreach($inventarios as $inv)
                                <option value="{{ $inv->id }}">
                                    {{ $inv->element ? $inv->element->name : 'Elemento #'.$inv->id }} (Código: {{ $inv->inventory_code ?? 'S/C' }} | Stock: {{ $inv->stock }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-2">
                        <label for="rec_cantidad" class="form-label small fw-semibold text-muted mb-1">Cantidad a Utilizar <span class="text-danger">*</span></label>
                        <input type="number" name="cantidad" id="rec_cantidad" value="1" min="1" class="form-control form-control-sm rounded-pill" required>
                        <small class="text-muted" style="font-size: 0.72rem;">Se descontará automáticamente del stock de inventario.</small>
                    </div>
                </div>

                <div class="modal-footer bg-white border-top-0 px-4 py-3 gap-2">
                    <button type="button" class="btn btn-ecp-outline px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-ecp-primary px-4 shadow-sm">
                        <i class="fas fa-boxes-stacked me-1"></i> Asignar y Descontar Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalReporte" tabindex="-1" aria-labelledby="modalReporteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--petroleo); border-bottom: 2px solid var(--verde);">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: rgba(57, 169, 0, 0.2);">
                        <i class="fas fa-file-export text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 font-outfit" id="modalReporteLabel">Generar Reporte de Asesorías</h5>
                        <small class="text-white-50" style="font-size: 0.75rem;">Exporta a Excel (CSV) o genera vista de impresión para PDF</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form method="GET" action="{{ route('controlecp.administrador.asesorias.reporte') }}" id="formReporte">
                <input type="hidden" name="formato" id="rep_formato" value="csv">
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    <div class="card border-0 shadow-sm p-3 rounded-3 bg-white mb-3">
                        <h6 class="fw-bold mb-3" style="color: var(--petroleo);">
                            <i class="fas fa-filter me-1" style="color: var(--verde);"></i> Criterios del Reporte
                        </h6>
                        <div class="row g-2">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">Categoría Temática</label>
                                <select name="categoria_id" class="form-select form-select-sm rounded-pill">
                                    <option value="">Todas las categorías</option>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->id_categoria_asesoria }}">{{ $cat->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">Instructor</label>
                                <select name="instructor_id" class="form-select form-select-sm rounded-pill">
                                    <option value="">Todos los instructores</option>
                                    <option value="sin_asignar">-- Sin asignar --</option>
                                    @foreach($instructores as $inst)
                                        <option value="{{ $inst->id }}">{{ $inst->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold text-muted mb-1">Estado</label>
                                <select name="estado" class="form-select form-select-sm rounded-pill">
                                    <option value="">Todos los estados</option>
                                    <option value="Solicitada">Solicitada</option>
                                    <option value="Programada">Programada</option>
                                    <option value="Realizada">Realizada</option>
                                    <option value="Cancelada">Cancelada</option>
                                </select>
                            </div>

                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold text-muted mb-1">Trimestre</label>
                                <select name="trimestre" class="form-select form-select-sm rounded-pill">
                                    <option value="">Todo el año</option>
                                    <option value="1">T1 (Ene - Mar)</option>
                                    <option value="2">T2 (Abr - Jun)</option>
                                    <option value="3">T3 (Jul - Sep)</option>
                                    <option value="4">T4 (Oct - Dic)</option>
                                </select>
                            </div>

                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold text-muted mb-1">Fecha Desde</label>
                                <input type="date" name="fecha_desde" class="form-control form-control-sm rounded-pill">
                            </div>

                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold text-muted mb-1">Fecha Hasta</label>
                                <input type="date" name="fecha_hasta" class="form-control form-control-sm rounded-pill">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-white border-top-0 px-4 py-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-ecp-outline px-4" data-bs-dismiss="modal">Cerrar</button>
                    <div class="d-flex gap-2">
                        <button type="submit" onclick="document.getElementById('rep_formato').value='csv'; this.form.target='_self';" class="btn btn-ecp-primary px-3 shadow-sm">
                            <i class="fas fa-file-csv me-1"></i> Descargar CSV (Excel)
                        </button>
                        <button type="submit" onclick="document.getElementById('rep_formato').value='print'; this.form.target='_blank';" class="btn btn-outline-primary px-3">
                            <i class="fas fa-print me-1"></i> Imprimir / Guardar PDF
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')

<script>
    // Muestra toast dinámico flotante
    function mostrarToast(mensaje, tipo = 'info') {
        const toastEl = document.getElementById('toastDinamico');
        const iconEl = document.getElementById('toastDinamicoIcon');
        const msgEl = document.getElementById('toastDinamicoMensaje');

        if (!toastEl) return;

        toastEl.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info');

        if (tipo === 'success') {
            toastEl.style.backgroundColor = 'var(--verde)';
            iconEl.className = 'fas fa-check-circle fs-5';
        } else if (tipo === 'error') {
            toastEl.style.backgroundColor = '#dc3545';
            iconEl.className = 'fas fa-exclamation-triangle fs-5';
        } else if (tipo === 'warning') {
            toastEl.style.backgroundColor = '#d97706';
            iconEl.className = 'fas fa-triangle-exclamation fs-5';
        } else {
            toastEl.style.backgroundColor = 'var(--petroleo-medio)';
            iconEl.className = 'fas fa-info-circle fs-5';
        }

        msgEl.innerText = mensaje;
        toastEl.style.display = 'block';

        const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
        toast.show();
    }

    // Consulta y muestra el detalle de una asesoría en el modal
    function verDetalle(id) {
        const modalEl = document.getElementById('modalDetalle');
        const bsModal = new bootstrap.Modal(modalEl);

        document.getElementById('det-cargando').style.display = 'block';
        document.getElementById('det-contenido').style.display = 'none';
        document.getElementById('det-contenedor-cancelacion').style.display = 'none';

        bsModal.show();

        fetch(`{{ url('control-ecp/administrador/asesorias') }}/${id}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                mostrarToast('No se pudo cargar la información de la asesoría.', 'error');
                return;
            }

            const a = data.asesoria;
            document.getElementById('det-id').innerText = a.id_asesoria;
            document.getElementById('det-aprendiz-nombre').innerText = a.aprendiz.nombre;
            document.getElementById('det-aprendiz-email').innerText = a.aprendiz.email || '';
            document.getElementById('det-instructor-nombre').innerText = a.instructor.nombre;
            document.getElementById('det-instructor-email').innerText = a.instructor.email || '';
            document.getElementById('det-categoria').innerText = a.categoria;
            document.getElementById('det-fecha-hora').innerText = `${a.fecha_formateada || '-'} | ${a.hora_inicio} - ${a.hora_fin}`;
            document.getElementById('det-fecha-solicitud').innerText = a.fecha_solicitud || '-';
            document.getElementById('det-motivo').innerText = a.motivo || 'Sin motivo especificado.';

            // Estado y badge
            const badgeEl = document.getElementById('det-badge-estado');
            badgeEl.innerText = a.estado;
            badgeEl.className = 'badge rounded-pill px-3 py-1 ';
            if (a.estado === 'Solicitada') badgeEl.className += 'badge-estado-solicitada';
            else if (a.estado === 'Programada') badgeEl.className += 'badge-estado-programada';
            else if (a.estado === 'Realizada') badgeEl.className += 'badge-estado-realizada';
            else if (a.estado === 'Cancelada') badgeEl.className += 'badge-estado-cancelada';
            else badgeEl.className += 'badge-estado-sin-asignar';

            // Cancelación
            if (a.estado === 'Cancelada' && a.motivo_cancelacion) {
                document.getElementById('det-contenedor-cancelacion').style.display = 'block';
                document.getElementById('det-motivo-cancelacion').innerText = a.motivo_cancelacion;
                document.getElementById('det-cancelado-por').innerText = a.cancelado_por || 'Administrador';
            }

            // Seguimientos
            document.getElementById('det-count-seguimientos').innerText = a.seguimientos.length;
            const listaSeg = document.getElementById('det-lista-seguimientos');
            listaSeg.innerHTML = '';
            window._currentSeguimientos = {};
            if (a.seguimientos.length === 0) {
                listaSeg.innerHTML = '<p class="text-muted small text-center my-3">No hay seguimientos registrados para esta asesoría.</p>';
            } else {
                a.seguimientos.forEach(s => {
                    window._currentSeguimientos[s.id_seguimiento] = s;
                    listaSeg.innerHTML += `
                        <div class="p-3 border rounded-3 bg-white mb-2 shadow-sm ${s.es_correccion ? 'border-warning' : ''}">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold small" style="color: var(--petroleo);">${s.usuario}</span>
                                <small class="text-muted">${s.fecha || ''}</small>
                            </div>
                            ${s.es_correccion ? '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle mb-2 small"><i class="fas fa-pen-to-square me-1"></i>Corrección de seguimiento previo</span>' : ''}
                            ${s.motivo_correccion ? `<p class="small text-danger mb-2"><strong>Motivo corrección:</strong> ${s.motivo_correccion}</p>` : ''}
                            <p class="small mb-1"><strong>Avances:</strong> ${s.avances}</p>
                            <p class="small mb-1"><strong>Compromisos:</strong> ${s.compromisos}</p>
                            ${s.observaciones ? `<p class="small text-muted mb-0"><strong>Observaciones:</strong> ${s.observaciones}</p>` : ''}
                            <div class="text-end mt-2 pt-2 border-top">
                                <button type="button" class="btn btn-outline-warning btn-sm text-dark py-0 px-2 rounded-pill shadow-xs" style="font-size: 0.72rem;" onclick="prepararCorreccionSeguimiento(${s.id_seguimiento})">
                                    <i class="fas fa-pen-to-square me-1"></i> Corregir seguimiento
                                </button>
                            </div>
                        </div>
                    `;
                });
            }

            // Historial
            document.getElementById('det-count-historial').innerText = a.historial.length;
            const listaHist = document.getElementById('det-lista-historial');
            listaHist.innerHTML = '';
            if (a.historial.length === 0) {
                listaHist.innerHTML = '<p class="text-muted small text-center my-3">Sin reprogramaciones ni cancelaciones previas.</p>';
            } else {
                a.historial.forEach(h => {
                    const badgeAccion = h.accion === 'Reprogramo' ? 'bg-primary' : 'bg-danger';
                    listaHist.innerHTML += `
                        <div class="p-3 border rounded-3 bg-white mb-2 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge ${badgeAccion} rounded-pill small">${h.accion}</span>
                                <small class="text-muted">${h.fecha || ''}</small>
                            </div>
                            <p class="small mb-1"><strong>Motivo:</strong> ${h.motivo}</p>
                            ${h.fecha_anterior ? `<small class="text-muted d-block">Anterior: ${h.fecha_anterior} (${h.hora_anterior}) &rarr; Nueva: ${h.fecha_nueva} (${h.hora_nueva})</small>` : ''}
                            <small class="text-muted">Por: ${h.usuario}</small>
                        </div>
                    `;
                });
            }

            // Recursos
            document.getElementById('det-count-recursos').innerText = a.recursos.length;
            const listaRec = document.getElementById('det-lista-recursos');
            listaRec.innerHTML = '';
            if (a.recursos.length === 0) {
                listaRec.innerHTML = '<p class="text-muted small text-center my-3">No se han asociado recursos de inventario.</p>';
            } else {
                let htmlRec = `
                    <table class="table table-sm table-bordered small mb-0">
                        <thead class="table-light"><tr><th>Recurso de Inventario</th><th class="text-center" style="width: 100px;">Cantidad</th></tr></thead>
                        <tbody>
                `;
                a.recursos.forEach(r => {
                    htmlRec += `<tr><td>${r.nombre}</td><td class="text-center fw-bold">${r.cantidad}</td></tr>`;
                });
                htmlRec += `</tbody></table>`;
                listaRec.innerHTML = htmlRec;
            }

            // Botón finalizar desde el detalle
            const btnFin = document.getElementById('det-btn-finalizar');
            if (btnFin) {
                if (a.estado === 'Programada' || (a.estado !== 'Cancelada' && a.estado !== 'Realizada')) {
                    btnFin.style.display = 'inline-block';
                    window._currentDetalleAsesoria = a;
                } else {
                    btnFin.style.display = 'none';
                    window._currentDetalleAsesoria = null;
                }
            }

            document.getElementById('det-cargando').style.display = 'none';
            document.getElementById('det-contenido').style.display = 'block';
        })
        .catch(err => {
            document.getElementById('det-cargando').style.display = 'none';
            mostrarToast('Error de conexión al consultar el detalle.', 'error');
        });
    }

    // Consulta instructores disponibles vía AJAX para Solicitud o Programación
    function consultarInstructoresDisponibles(prefix, seleccionado = null) {
        const catEl = document.getElementById(`${prefix}_categoria_id`) || document.getElementById(`${prefix}_categoria`);
        const fechaEl = document.getElementById(`${prefix}_fecha`);
        const hInicioEl = document.getElementById(`${prefix}_hora_inicio`);
        const hFinEl = document.getElementById(`${prefix}_hora_fin`);
        const instSelect = document.getElementById(`${prefix}_instructor`);
        const badgeEl = document.getElementById(`${prefix}_badge_disponibles`);
        const alertaEl = document.getElementById(`${prefix}_alerta_disponibilidad`);
        const asesoriaIdEl = document.getElementById(`${prefix}_asesoria_id`);

        if (!catEl || !fechaEl || !hInicioEl || !hFinEl || !instSelect) return;

        const categoriaId = catEl.value;
        const fecha = fechaEl.value;
        const horaInicio = hInicioEl.value;
        const horaFin = hFinEl.value;
        const asesoriaId = asesoriaIdEl ? asesoriaIdEl.value : '';

        if (!categoriaId || !fecha || !horaInicio || !horaFin) {
            if (badgeEl) {
                badgeEl.className = 'badge rounded-pill bg-light text-muted border small';
                badgeEl.innerText = 'Selecciona categoría y horario';
            }
            if (alertaEl) alertaEl.style.display = 'none';
            return;
        }

        if (horaInicio >= horaFin) {
            if (badgeEl) {
                badgeEl.className = 'badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle small';
                badgeEl.innerText = 'Hora inicio debe ser menor a fin';
            }
            if (alertaEl) {
                alertaEl.className = 'alert alert-warning py-1 px-2 small mb-0 mt-2';
                alertaEl.innerText = 'La hora de inicio debe ser anterior a la hora de fin.';
                alertaEl.style.display = 'block';
            }
            return;
        }

        if (badgeEl) {
            badgeEl.className = 'badge rounded-pill bg-light text-primary border small';
            badgeEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Consultando...';
        }

        const params = new URLSearchParams({
            categoria_id: categoriaId,
            fecha: fecha,
            hora_inicio: horaInicio,
            hora_fin: horaFin,
            asesoria_id: asesoriaId
        });

        fetch(`{{ route('controlecp.administrador.asesorias.instructores-disponibles') }}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                if (badgeEl) {
                    badgeEl.className = 'badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle small';
                    badgeEl.innerText = data.mensaje || 'Error al consultar';
                }
                return;
            }

            // Reconstruir opciones del select
            instSelect.innerHTML = '';
            if (prefix === 'sol') {
                instSelect.innerHTML = '<option value="">-- Dejar sin asignar (Solicitada) --</option>';
            } else {
                instSelect.innerHTML = '<option value="">-- Selecciona el instructor --</option>';
            }

            if (data.instructores.length === 0) {
                if (badgeEl) {
                    badgeEl.className = 'badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle small';
                    badgeEl.innerText = '0 disponibles en esta franja';
                }
                if (alertaEl) {
                    alertaEl.className = 'alert alert-warning py-2 px-3 small mb-0 mt-2 rounded-3';
                    if (prefix === 'sol') {
                        alertaEl.innerHTML = '<i class="fas fa-circle-info me-1"></i> No hay instructores disponibles para esta temática y horario. Puedes guardar la solicitud como <strong>Solicitada</strong> para asignarla después, o definir una nueva disponibilidad.';
                    } else {
                        alertaEl.innerHTML = '<i class="fas fa-triangle-exclamation me-1"></i> Ningún instructor habilitado para esta temática tiene disponibilidad libre en este horario (o hay cruce con otra asesoría).';
                    }
                    alertaEl.style.display = 'block';
                }
            } else {
                if (badgeEl) {
                    badgeEl.className = 'badge rounded-pill bg-success-subtle text-success-emphasis border border-success-subtle small';
                    badgeEl.innerText = `${data.instructores.length} instructor(es) libre(s)`;
                }
                if (alertaEl) {
                    alertaEl.className = 'alert alert-success py-1 px-2 small mb-0 mt-2 rounded-3';
                    alertaEl.innerHTML = `<i class="fas fa-check-circle me-1"></i> Se encontraron ${data.instructores.length} instructor(es) disponibles sin cruces de horario.`;
                    alertaEl.style.display = 'block';
                }

                data.instructores.forEach(inst => {
                    const opt = document.createElement('option');
                    opt.value = inst.id;
                    opt.textContent = `${inst.nombre} (${inst.email})`;
                    if (seleccionado && String(seleccionado) === String(inst.id)) {
                        opt.selected = true;
                    }
                    instSelect.appendChild(opt);
                });
            }
        })
        .catch(err => {
            if (badgeEl) {
                badgeEl.className = 'badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle small';
                badgeEl.innerText = 'Error de conexión';
            }
        });
    }

    // Abre el modal para asignar y programar una asesoría
    function abrirProgramar(id, aprendiz, categoriaId, fecha, hInicio, hFin, instructorId, categoriaNombre) {
        const form = document.getElementById('formProgramar');
        form.action = `{{ url('control-ecp/administrador/asesorias') }}/${id}/programar`;

        document.getElementById('prog_asesoria_id').value = id;
        document.getElementById('prog_categoria_id').value = categoriaId;
        document.getElementById('prog_aprendiz_nombre').innerText = aprendiz || 'Aprendiz';
        document.getElementById('prog_categoria_nombre').innerText = categoriaNombre || 'Categoría';

        if (fecha) document.getElementById('prog_fecha').value = fecha;
        if (hInicio) document.getElementById('prog_hora_inicio').value = hInicio;
        if (hFin) document.getElementById('prog_hora_fin').value = hFin;

        // Ejecutar búsqueda AJAX de instructores disponibles
        consultarInstructoresDisponibles('prog', instructorId);

        const bsModal = new bootstrap.Modal(document.getElementById('modalProgramar'));
        bsModal.show();
    }

    // Consulta franjas registradas para el instructor en el modal de disponibilidad
    function cargarDisponibilidadesInstructor(usuarioId) {
        const cont = document.getElementById('disp_lista_franjas');
        if (!cont) return;
        if (!usuarioId) {
            cont.innerHTML = '<p class="text-muted small text-center my-2">Selecciona un instructor para ver sus franjas.</p>';
            return;
        }
        cont.innerHTML = '<div class="text-center py-2"><span class="spinner-border spinner-border-sm text-success"></span> Consultando franjas...</div>';

        fetch(`{{ url('control-ecp/administrador/asesorias/disponibilidad') }}/${usuarioId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success || data.disponibilidades.length === 0) {
                cont.innerHTML = '<p class="text-muted small text-center my-2">Este instructor aún no tiene franjas de disponibilidad registradas.</p>';
                return;
            }
            let html = '<div class="table-responsive"><table class="table table-sm table-bordered small mb-0 align-middle"><thead class="table-light"><tr><th>Año</th><th>Trim.</th><th>Día</th><th>Horario</th><th>Estado</th></tr></thead><tbody>';
            data.disponibilidades.forEach(d => {
                html += `<tr><td>${d.anio}</td><td>${d.trimestre}</td><td>${d.dia_nombre}</td><td>${d.hora_inicio} - ${d.hora_fin}</td><td><span class="badge ${d.habilitada ? 'badge-estado-realizada' : 'badge-estado-cancelada'} rounded-pill" style="font-size:0.7rem;">${d.habilitada ? 'Activa' : 'Inactiva'}</span></td></tr>`;
            });
            html += '</tbody></table></div>';
            cont.innerHTML = html;
        })
        .catch(() => {
            cont.innerHTML = '<p class="text-danger small text-center my-2">No se pudieron consultar las franjas.</p>';
        });
    }

    // Reabrir modales automáticamente en caso de error de validación
    @if(session('modal_abrir'))
        document.addEventListener('DOMContentLoaded', function() {
            const modalId = "{{ session('modal_abrir') }}";
            const targetId = "{{ session('modal_id') ?? session('modal_programar_id') }}";
            const modalEl = document.getElementById(modalId);
            if (modalEl) {
                if (modalId === 'modalProgramar' && targetId) {
                    const formProg = document.getElementById('formProgramar');
                    if (formProg) formProg.action = `{{ url('control-ecp/administrador/asesorias') }}/${targetId}/programar`;
                } else if (modalId === 'modalReprogramar' && targetId) {
                    const formReprog = document.getElementById('formReprogramar');
                    if (formReprog) formReprog.action = `{{ url('control-ecp/administrador/asesorias') }}/${targetId}/reprogramar`;
                } else if (modalId === 'modalCancelar' && targetId) {
                    const formCanc = document.getElementById('formCancelar');
                    if (formCanc) formCanc.action = `{{ url('control-ecp/administrador/asesorias') }}/${targetId}/cancelar`;
                } else if (modalId === 'modalFinalizar' && targetId) {
                    const formFin = document.getElementById('formFinalizar');
                    if (formFin) formFin.action = `{{ url('control-ecp/administrador/asesorias') }}/${targetId}/finalizar`;
                } else if (modalId === 'modalSeguimiento' && targetId) {
                    const formSeg = document.getElementById('formSeguimiento');
                    if (formSeg) formSeg.action = `{{ url('control-ecp/administrador/asesorias') }}/${targetId}/seguimiento`;
                } else if (modalId === 'modalCorregirSeguimiento' && targetId) {
                    const formCorr = document.getElementById('formCorregirSeguimiento');
                    if (formCorr) formCorr.action = `{{ url('control-ecp/administrador/asesorias/seguimiento') }}/${targetId}`;
                } else if (modalId === 'modalEditarAsesoria' && targetId) {
                    const formEdit = document.getElementById('formEditarAsesoria');
                    if (formEdit) formEdit.action = `{{ url('control-ecp/administrador/asesorias') }}/${targetId}`;
                } else if (modalId === 'modalRecursos' && targetId) {
                    const formRec = document.getElementById('formRecursos');
                    if (formRec) formRec.action = `{{ url('control-ecp/administrador/asesorias') }}/${targetId}/recursos`;
                }
                const bsModal = new bootstrap.Modal(modalEl);
                bsModal.show();
            }
        });
    @endif

    // Abre el modal para editar una asesoría existente
    function abrirEditar(id, aprendizId, categoriaId, fecha, hInicio, hFin, instructorId, motivo) {
        const form = document.getElementById('formEditarAsesoria');
        form.action = `{{ url('control-ecp/administrador/asesorias') }}/${id}`;

        document.getElementById('edit_asesoria_id').value = id;
        document.getElementById('edit_categoria_id').value = categoriaId || '';
        document.getElementById('edit_asesoria_numero').innerText = id;

        if (aprendizId) document.getElementById('edit_aprendiz').value = aprendizId;
        if (categoriaId) document.getElementById('edit_categoria').value = categoriaId;
        if (fecha) document.getElementById('edit_fecha').value = fecha;
        if (hInicio) document.getElementById('edit_hora_inicio').value = hInicio;
        if (hFin) document.getElementById('edit_hora_fin').value = hFin;
        if (motivo) document.getElementById('edit_motivo').value = motivo;

        // Consultar instructores disponibles para este horario y categoría
        consultarInstructoresDisponibles('edit', instructorId);

        const bsModal = new bootstrap.Modal(document.getElementById('modalEditarAsesoria'));
        bsModal.show();
    }

    // Prepara la corrección de un seguimiento desde la ficha de detalle
    function prepararCorreccionSeguimiento(id) {
        if (!window._currentSeguimientos || !window._currentSeguimientos[id]) return;
        const s = window._currentSeguimientos[id];
        abrirCorregirSeguimiento(s.id_seguimiento, s.avances, s.compromisos, s.observaciones);
    }

    // Modal de reprogramación
    function abrirReprogramar(id, fecha, hInicio, hFin) {
        const form = document.getElementById('formReprogramar');
        form.action = `{{ url('control-ecp/administrador/asesorias') }}/${id}/reprogramar`;
        if (fecha) document.getElementById('reprog_fecha').value = fecha;
        if (hInicio) document.getElementById('reprog_hora_inicio').value = hInicio;
        if (hFin) document.getElementById('reprog_hora_fin').value = hFin;
        document.getElementById('reprog_motivo').value = '';
        new bootstrap.Modal(document.getElementById('modalReprogramar')).show();
    }

    // Modal de cancelación
    function abrirCancelar(id) {
        const form = document.getElementById('formCancelar');
        form.action = `{{ url('control-ecp/administrador/asesorias') }}/${id}/cancelar`;
        document.getElementById('canc_motivo').value = '';
        new bootstrap.Modal(document.getElementById('modalCancelar')).show();
    }

    // Modal de finalización
    function abrirFinalizar(id, aprendiz, categoria, fecha, horario, instructor) {
        const form = document.getElementById('formFinalizar');
        form.action = `{{ url('control-ecp/administrador/asesorias') }}/${id}/finalizar`;
        document.getElementById('fin_asesoria_numero').innerText = '#' + id;
        document.getElementById('fin_asesoria_aprendiz').innerText = aprendiz || 'No especificado';
        document.getElementById('fin_asesoria_categoria').innerText = categoria || 'Sin categoría';
        document.getElementById('fin_asesoria_horario').innerText = (fecha || '-') + (horario ? ' (' + horario + ')' : '');
        document.getElementById('fin_asesoria_instructor').innerText = instructor || 'Sin asignar';
        document.getElementById('fin_observaciones').value = '';
        new bootstrap.Modal(document.getElementById('modalFinalizar')).show();
    }

    // Permite disparar la finalización directamente desde el modal de detalle
    function finalizarDesdeDetalle() {
        if (!window._currentDetalleAsesoria) return;
        const a = window._currentDetalleAsesoria;
        const mDet = bootstrap.Modal.getInstance(document.getElementById('modalDetalle'));
        if (mDet) mDet.hide();
        abrirFinalizar(
            a.id_asesoria,
            a.aprendiz ? a.aprendiz.nombre : '',
            a.categoria || '',
            a.fecha_formateada || '',
            `${a.hora_inicio || ''} - ${a.hora_fin || ''}`,
            a.instructor ? a.instructor.nombre : ''
        );
    }

    // Modal de seguimiento
    function abrirSeguimiento(id) {
        const form = document.getElementById('formSeguimiento');
        form.action = `{{ url('control-ecp/administrador/asesorias') }}/${id}/seguimiento`;
        document.getElementById('seg_avances').value = '';
        document.getElementById('seg_compromisos').value = '';
        document.getElementById('seg_observaciones').value = '';
        new bootstrap.Modal(document.getElementById('modalSeguimiento')).show();
    }

    // Modal de corregir seguimiento (conserva histórico creando nuevo registro enlazado)
    function abrirCorregirSeguimiento(idSeguimiento, avances, compromisos, observaciones) {
        const mDet = bootstrap.Modal.getInstance(document.getElementById('modalDetalle'));
        if (mDet) mDet.hide();

        const form = document.getElementById('formCorregirSeguimiento');
        form.action = `{{ url('control-ecp/administrador/asesorias/seguimiento') }}/${idSeguimiento}`;
        document.getElementById('corr_motivo').value = '';
        document.getElementById('corr_avances').value = avances || '';
        document.getElementById('corr_compromisos').value = compromisos || '';
        document.getElementById('corr_observaciones').value = observaciones || '';
        new bootstrap.Modal(document.getElementById('modalCorregirSeguimiento')).show();
    }

    // Modal de asignación de recursos desde inventario
    function abrirRecursos(id) {
        const form = document.getElementById('formRecursos');
        form.action = `{{ url('control-ecp/administrador/asesorias') }}/${id}/recursos`;
        document.getElementById('rec_inventario').value = '';
        document.getElementById('rec_cantidad').value = 1;
        new bootstrap.Modal(document.getElementById('modalRecursos')).show();
    }

    // Edición de categorías en línea dentro del modal de categorías
    function editarCategoria(id, nombre, instructoresIds) {
        const cont = document.getElementById('cat_contenedor_edicion');
        if (!cont) return;
        cont.style.display = 'block';
        const form = document.getElementById('formEditarCategoria');
        form.action = `{{ url('control-ecp/administrador/asesorias/categorias') }}/${id}`;
        document.getElementById('edit_cat_nombre').value = nombre;
        document.querySelectorAll('.edit-cat-inst').forEach(chk => {
            chk.checked = instructoresIds && instructoresIds.includes(parseInt(chk.value));
        });
        cont.scrollIntoView({ behavior: 'smooth' });
    }

    // Cancelar la edición de categoría
    function cancelarEdicionCategoria() {
        const cont = document.getElementById('cat_contenedor_edicion');
        if (cont) cont.style.display = 'none';
    }
</script>
@endpush
@endsection
