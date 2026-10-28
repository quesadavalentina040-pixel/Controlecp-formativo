
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

    .poe-card {
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

    .btn-ecp-outline-navy {
        background-color: transparent;
        color: var(--petroleo-ecp) !important;
        border: 1.5px solid var(--petroleo-ecp);
        font-weight: 600;
        border-radius: 50rem;
        padding: 0.55rem 1.35rem;
        transition: all .2s ease;
    }
    .btn-ecp-outline-navy:hover {
        background-color: var(--petroleo-ecp);
        color: #ffffff !important;
        transform: translateY(-1px);
    }

    .btn-ecp-outline-green {
        background-color: transparent;
        color: var(--verde-ecp) !important;
        border: 1.5px solid var(--verde-ecp);
        font-weight: 600;
        border-radius: 50rem;
        padding: 0.55rem 1.35rem;
        transition: all .2s ease;
    }
    .btn-ecp-outline-green:hover {
        background-color: var(--verde-ecp);
        color: #ffffff !important;
        transform: translateY(-1px);
    }

    /* Badges de estado */
    .badge-estado-pendiente {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }
    .badge-estado-cumplida {
        background-color: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    .badge-estado-atrasada {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    .badge-estado-cancelada {
        background-color: #f1f5f9;
        color: #64748b;
        border: 1px solid #cbd5e1;
    }

    /* Botones de acción en tabla */
    .btn-action-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        background-color: #f1f5f9;
        color: var(--petroleo-ecp);
        transition: all .2s ease;
        text-decoration: none;
        cursor: pointer;
    }
    .btn-action-icon:hover {
        transform: scale(1.1);
    }
    .btn-action-view:hover {
        background-color: #e0f2fe;
        color: #0284c7;
    }
    .btn-action-download:hover {
        background-color: #dcfce7;
        color: #16a34a;
    }
    .btn-action-version:hover {
        background-color: #fef3c7;
        color: #d97706;
    }
    .btn-action-history:hover {
        background-color: #ede9fe;
        color: #7c3aed;
    }
    .btn-action-program:hover {
        background-color: #fee2e2;
        color: #dc2626;
    }

    /* Avatar circular de líderes */
    .avatar-lider {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background-color: var(--petroleo-ecp);
        color: #fff;
        font-weight: 700;
        font-size: 0.8rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* Tabla estilizada */
    .table-poe th {
        background-color: #f8fafc;
        color: var(--petroleo-ecp);
        font-weight: 700;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 1rem 0.85rem;
        border-bottom: 2px solid #e2e8f0;
    }
    .table-poe td {
        padding: 1rem 0.85rem;
        vertical-align: middle;
        font-size: 0.92rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .table-poe tr:hover td {
        background-color: #f8fafc;
    }

    /* Modal Styling */
    .modal-content {
        border-radius: 20px;
        border: none;
        box-shadow: 0 20px 40px rgba(0, 26, 41, 0.2);
    }
    .modal-header {
        border-bottom: 1px solid #e2e8f0;
        padding: 1.25rem 1.75rem;
    }
    .modal-body {
        padding: 1.5rem 1.75rem;
    }
    .modal-footer {
        border-top: 1px solid #e2e8f0;
        padding: 1.25rem 1.75rem;
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
                    <li class="breadcrumb-item active text-dark fw-bold" aria-current="page">POE</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-1" style="color: var(--petroleo-ecp);">
                Procedimiento Operativo Estandarizado (POE)
            </h2>
            <p class="text-muted mb-0" style="font-size: 0.92rem;">
                Gestión, versiones, programación de entregas e historial de líderes de los POE del cargo de Líder (ASIG-SGC-M-001).
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <button type="button" class="btn btn-ecp-primary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalSubirPoe">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <span>Subir POE</span>
            </button>

            <button type="button" class="btn btn-ecp-navy d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalProgramaciones">
                <i class="fa-solid fa-calendar-days"></i>
                <span>Programaciones</span>
                @if($programacionesAtrasadas > 0)
                    <span class="badge bg-danger rounded-pill">{{ $programacionesAtrasadas }}</span>
                @elseif($programacionesVigentes > 0)
                    <span class="badge bg-warning text-dark rounded-pill">{{ $programacionesVigentes }}</span>
                @endif
            </button>

            <button type="button" class="btn btn-ecp-outline-navy d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalHistorialLideres">
                <i class="fa-solid fa-users-gear"></i>
                <span>Historial de Líderes</span>
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="poe-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background-color: var(--verde-ecp-claro); color: var(--verde-ecp);">
                    <i class="fa-solid fa-file-contract fs-4"></i>
                </div>
                <div>
                    <span class="text-muted d-block" style="font-size: 0.82rem; font-weight: 600;">POE REGISTRADOS</span>
                    <span class="h4 fw-bold mb-0" style="color: var(--petroleo-ecp);">{{ $totalPoes }}</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="poe-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background-color: #fef3c7; color: #d97706;">
                    <i class="fa-solid fa-clock-rotate-left fs-4"></i>
                </div>
                <div>
                    <span class="text-muted d-block" style="font-size: 0.82rem; font-weight: 600;">ENTREGAS VIGENTES</span>
                    <span class="h4 fw-bold mb-0" style="color: var(--petroleo-ecp);">{{ $programacionesVigentes }}</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="poe-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background-color: #fee2e2; color: #dc2626;">
                    <i class="fa-solid fa-triangle-exclamation fs-4"></i>
                </div>
                <div>
                    <span class="text-muted d-block" style="font-size: 0.82rem; font-weight: 600;">ENTREGAS ATRASADAS</span>
                    <span class="h4 fw-bold mb-0" style="color: #dc2626;">{{ $programacionesAtrasadas }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="poe-card p-3 mb-4">

        <form method="GET" action="{{ route('controlecp.administrador.repositorio.poe.index') }}" class="row g-2 align-items-center" id="formBuscarPoe">
            <div class="col-12 col-md-8 col-lg-9">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text"
                           name="buscar"
                           id="inputBuscarPoe"
                           value="{{ $buscar ?? '' }}"
                           class="form-control border-start-0 ps-0"
                           placeholder="Buscar por código (ej: ASIG-SGC-M-001) o por nombre del POE..."
                           autocomplete="off">

                </div>
            </div>
            <div class="col-6 col-md-2 col-lg-1.5 d-grid">
                <button type="submit" class="btn btn-ecp-navy py-2">
                    Buscar
                </button>
            </div>
            <div class="col-6 col-md-2 col-lg-1.5 d-grid">
                <a href="{{ route('controlecp.administrador.repositorio.poe.index') }}" class="btn btn-outline-secondary py-2" id="btnLimpiarBuscar">
                    <i class="fa-solid fa-rotate-left me-1"></i> Limpiar
                </a>
            </div>
        </form>
    </div>

    <div class="poe-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-poe mb-0 align-middle">
                <thead>
                    <tr>
                        <th style="min-width: 140px;">Código</th>
                        <th style="min-width: 220px;">Nombre del POE</th>
                        <th style="min-width: 200px;">Líder Actual</th>
                        <th style="min-width: 130px;" class="text-center">Versión Actual</th>
                        <th style="min-width: 200px;">Subido Por / Fecha</th>
                        <th style="min-width: 150px;" class="text-center">Programación</th>
                        <th style="min-width: 190px;" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyPoes">

                    @forelse($poes as $poe)
                        @php // Inicia un bloque PHP para preparar datos que usará la vista.
                            $vActual = $poe->versionActual;
                            $lider = $poe->liderActual;
                            $nombreLider = $lider?->person ? ($lider->person->first_name . ' ' . $lider->person->first_last_name) : ($lider?->nickname ?: 'Sin asignar');
                            $inicialesLider = strtoupper(substr($nombreLider, 0, 2));

                            $prog = $poe->programacionVigente;
                        @endphp
                        <tr id="fila-poe-{{ $poe->id_poe }}">

                            <td>
                                <span class="badge bg-light text-dark fw-bold border px-2 py-1" style="font-family: monospace; font-size: 0.9rem;">
                                    {{ $poe->codigo }}
                                </span>
                            </td>

                            <td>
                                <div class="fw-bold text-dark">{{ $poe->nombre }}</div>
                                <span class="text-muted small">Procedimiento Operativo</span>
                            </td>

                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-lider" title="{{ $nombreLider }}">
                                        {{ $inicialesLider }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark" style="font-size: 0.9rem;">{{ $nombreLider }}</div>
                                        <div class="text-muted small" style="font-size: 0.75rem;">{{ $lider?->email }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="text-center">
                                @if($vActual)
                                    <div class="d-inline-flex flex-column align-items-center gap-1">
                                        <span class="badge rounded-pill bg-success px-2 py-1 fw-bold" style="font-size: 0.85rem;">
                                            v{{ $vActual->numero_version }}
                                        </span>
                                        @if($vActual->formato === 'PDF')
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-0" style="font-size: 0.72rem;">
                                                <i class="fa-solid fa-file-pdf me-1"></i> PDF
                                            </span>
                                        @else
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0" style="font-size: 0.72rem;">
                                                <i class="fa-solid fa-file-word me-1"></i> Word
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="badge bg-secondary">Sin versión</span>
                                @endif
                            </td>

                            <td>
                                @if($vActual)
                                    @php // Inicia un bloque PHP para preparar datos que usará la vista.
                                        $subio = $vActual->usuario;
                                        $nombreSubio = $subio?->person ? ($subio->person->first_name . ' ' . $subio->person->first_last_name) : ($subio?->nickname ?: 'Sistema');
                                    @endphp
                                    <div class="small fw-semibold text-dark">{{ $nombreSubio }}</div>
                                    <div class="text-muted small" style="font-size: 0.78rem;">
                                        <i class="fa-regular fa-clock me-1"></i>
                                        {{ $vActual->fecha ? $vActual->fecha->format('d/m/Y H:i') : 'N/A' }}
                                    </div>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>

                            <td class="text-center">
                                @if($prog)
                                    @php // Inicia un bloque PHP para preparar datos que usará la vista.
                                        $nuevoLiderNom = $prog->nuevoLider?->person ? ($prog->nuevoLider->person->first_name . ' ' . $prog->nuevoLider->person->first_last_name) : ($prog->nuevoLider?->nickname ?: 'N/A');
                                    @endphp
                                    @if($prog->estado === 'Atrasada')
                                        <span class="badge badge-estado-atrasada rounded-pill px-2 py-1"
                                              data-bs-toggle="tooltip"
                                              title="Venció el {{ $prog->fecha_limite->format('d/m/Y') }}. Entrega por: {{ $nuevoLiderNom }}">

                                            <i class="fa-solid fa-circle-exclamation me-1"></i> Atrasada
                                        </span>
                                    @else
                                        <span class="badge badge-estado-pendiente rounded-pill px-2 py-1"
                                              data-bs-toggle="tooltip"
                                              title="Límite: {{ $prog->fecha_limite->format('d/m/Y') }}. Asignado a: {{ $nuevoLiderNom }}">

                                            <i class="fa-regular fa-clock me-1"></i> Pendiente
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted small">Al día</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="d-inline-flex align-items-center gap-1">

                                    @if($vActual)
                                        @if($vActual->formato === 'PDF')
                                            <button type="button"
                                                    class="btn-action-icon btn-action-view"
                                                    onclick="abrirPreviewPdf('{{ route('controlecp.administrador.repositorio.poe.ver', $vActual->id_poe_version) }}', '{{ $poe->codigo }} - v{{ $vActual->numero_version }}')"

                                                    data-bs-toggle="tooltip"
                                                    title="Previsualizar PDF en el navegador">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                        @else
                                            <button type="button"
                                                    class="btn-action-icon btn-action-view"
                                                    onclick="avisoWordDescarga('{{ route('controlecp.administrador.repositorio.poe.descargar', $vActual->id_poe_version) }}')"

                                                    data-bs-toggle="tooltip"
                                                    title="Archivo Word (solo descarga)">
                                                <i class="fa-solid fa-eye-slash"></i>
                                            </button>
                                        @endif

                                        <a href="{{ route('controlecp.administrador.repositorio.poe.descargar', $vActual->id_poe_version) }}"
                                           class="btn-action-icon btn-action-download"
                                           data-bs-toggle="tooltip"
                                           title="Descargar archivo ({{ $poe->codigo }}_v{{ $vActual->numero_version }})">
                                            <i class="fa-solid fa-cloud-arrow-down"></i>
                                        </a>
                                    @endif

                                    <button type="button"
                                            class="btn-action-icon btn-action-version"
                                            onclick="abrirModalNuevaVersion({{ $poe->id_poe }}, '{{ $poe->codigo }}', '{{ addslashes($poe->nombre) }}', '{{ $prog ? '1' : '0' }}')"

                                            data-bs-toggle="tooltip"
                                            title="Subir nueva versión">
                                        <i class="fa-solid fa-file-circle-plus"></i>
                                    </button>

                                    <button type="button"
                                            class="btn-action-icon btn-action-history"
                                            onclick="cargarHistorialVersiones({{ $poe->id_poe }}, '{{ $poe->codigo }}')"

                                            data-bs-toggle="tooltip"
                                            title="Historial de versiones">
                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                    </button>

                                    <button type="button"
                                            class="btn-action-icon btn-action-program"
                                            onclick="abrirModalProgramar({{ $poe->id_poe }}, '{{ $poe->codigo }}', '{{ addslashes($poe->nombre) }}', {{ $poe->id_usuario }}, '{{ addslashes($nombreLider) }}', {{ $prog ? 'true' : 'false' }})"

                                            data-bs-toggle="tooltip"
                                            title="Programar fecha de subida">
                                        <i class="fa-regular fa-calendar-plus"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fa-solid fa-folder-open fs-1 d-block mb-3 opacity-50"></i>
                                    <h5>No se encontraron POE registrados</h5>
                                    <p class="small text-muted mb-3">Sube el primer Procedimiento Operativo Estandarizado de la Escuela Cultura de Paz.</p>
                                    <button type="button" class="btn btn-ecp-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalSubirPoe">
                                        <i class="fa-solid fa-cloud-arrow-up me-1"></i> Subir POE Inicial
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

<div class="modal fade" id="modalSubirPoe" tabindex="-1" aria-labelledby="modalSubirPoeLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form action="{{ route('controlecp.administrador.repositorio.poe.store') }}" method="POST" enctype="multipart/form-data" id="formSubirPoe">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalSubirPoeLabel">
                        <i class="fa-solid fa-cloud-arrow-up" style="color: var(--verde-ecp);"></i>
                        <span>Subir Nuevo POE</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">

                    <div class="mb-3">
                        <label for="codigoPoe" class="form-label fw-semibold text-dark">Código del Documento <span class="text-danger">*</span></label>
                        <input type="text"
                               name="codigo"
                               id="codigoPoe"
                               class="form-control"
                               placeholder="Ej: ASIG-SGC-M-001"
                               required
                               style="font-family: monospace; text-transform: uppercase;">

                        <div class="form-text">Código único de identificación de calidad del POE.</div>
                    </div>

                    <div class="mb-3">
                        <label for="nombrePoe" class="form-label fw-semibold text-dark">Nombre del Procedimiento <span class="text-danger">*</span></label>
                        <input type="text"
                               name="nombre"
                               id="nombrePoe"
                               class="form-control"
                               placeholder="Ej: Procedimiento Operativo Estandarizado del Cargo de Líder"
                               required>

                    </div>

                    <div class="mb-3">
                        <label for="idUsuarioPoe" class="form-label fw-semibold text-dark">Líder Asignado <span class="text-danger">*</span></label>
                        <select name="id_usuario" id="idUsuarioPoe" class="form-select" required>
                            <option value="" selected disabled>-- Seleccione el usuario líder --</option>
                            @foreach($lideres as $lid)
                                @php // Inicia un bloque PHP para preparar datos que usará la vista.
                                    $nomb = $lid->person ? ($lid->person->first_name . ' ' . $lid->person->first_last_name) : ($lid->nickname ?: $lid->email);
                                @endphp
                                <option value="{{ $lid->id }}">{{ $nomb }} ({{ $lid->email }})</option>
                            @endforeach
                        </select>
                        <div class="form-text">Usuario con rol Líder a cargo del POE.</div>
                    </div>

                    <div class="mb-3">
                        <label for="archivoPoe" class="form-label fw-semibold text-dark">Archivo del POE (v1.0 Inicial) <span class="text-danger">*</span></label>
                        <input type="file"
                               name="archivo"
                               id="archivoPoe"
                               class="form-control"
                               accept=".pdf,.docx"
                               required>

                        <div class="form-text">Solo archivos PDF o Word (.docx), tamaño máximo 10 MB.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-ecp-primary" id="btnGuardarPoe">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Guardar POE
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNuevaVersion" tabindex="-1" aria-labelledby="modalNuevaVersionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form action="" method="POST" enctype="multipart/form-data" id="formNuevaVersion">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalNuevaVersionLabel">
                        <i class="fa-solid fa-file-circle-plus" style="color: #d97706;"></i>
                        <span>Nueva Versión del POE</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">

                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-secondary" id="nvCodigoPoe">CODIGO</span>
                            <span class="badge bg-warning text-dark" id="nvSiguienteVersionBadge">Nueva Versión</span>
                        </div>
                        <div class="fw-bold mt-2" id="nvNombrePoe">Nombre del POE</div>
                    </div>

                    <div id="nvAvisoProgramacion" class="alert alert-warning d-none py-2 px-3 small" role="alert">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        Este POE tiene una entrega programada vigente. Al registrar esta versión, se marcará automáticamente como <strong>Cumplida</strong> y se actualizará el cargo al nuevo líder.
                        <input type="hidden" name="cumplir_programacion" value="1">
                    </div>

                    <div class="mb-3">
                        <label for="nvArchivo" class="form-label fw-semibold text-dark">Archivo de la Nueva Versión <span class="text-danger">*</span></label>
                        <input type="file"
                               name="archivo"
                               id="nvArchivo"
                               class="form-control"
                               accept=".pdf,.docx"
                               required>

                        <div class="form-text">Solo archivos PDF o Word (.docx), tamaño máximo 10 MB.</div>
                    </div>

                    <div class="mb-3">
                        <label for="nvCambio" class="form-label fw-semibold text-dark">¿Qué se modificó? <span class="text-danger">*</span></label>
                        <textarea name="cambio"
                                  id="nvCambio"
                                  rows="3"
                                  class="form-control"
                                  placeholder="Describe detalladamente los cambios o mejoras introducidas en esta versión..."
                                  required></textarea>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-ecp-primary" id="btnGuardarNuevaVersion">
                        <i class="fa-solid fa-cloud-arrow-up me-1"></i> Registrar Nueva Versión
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalHistorialVersiones" tabindex="-1" aria-labelledby="modalHistorialVersionesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalHistorialVersionesLabel">
                        <i class="fa-solid fa-clock-rotate-left" style="color: #7c3aed;"></i>
                        <span>Historial de Versiones</span>
                    </h5>
                    <div class="text-muted small" id="hvSubtitulo">POE</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0">
                <div id="hvLoading" class="text-center py-5">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="text-muted small mt-2">Cargando versiones...</p>
                </div>

                <div id="hvContenedor" class="p-3 d-none">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr style="font-size: 0.82rem;">
                                    <th>Versión</th>
                                    <th>Fecha</th>
                                    <th>Subido Por</th>
                                    <th>Cambios Realizados</th>
                                    <th>Formato</th>
                                    <th class="text-center">Descarga</th>
                                </tr>
                            </thead>
                            <tbody id="hvTablaCuerpo">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalProgramarSubida" tabindex="-1" aria-labelledby="modalProgramarSubidaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form action="" method="POST" id="formProgramarSubida">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalProgramarSubidaLabel">
                        <i class="fa-regular fa-calendar-plus" style="color: #dc2626;"></i>
                        <span>Programar Fecha de Subida</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">

                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="fw-bold text-dark mb-1" id="progPoeCodigo">POE-000</div>
                        <div class="text-muted small" id="progPoeNombre">Nombre</div>
                        <div class="mt-2 text-muted small">
                            <strong>Líder actual (saliente):</strong> <span id="progLiderActualNombre" class="text-dark">Líder</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="progNuevoLider" class="form-label fw-semibold text-dark">Nuevo Líder Asignado <span class="text-danger">*</span></label>
                        <select name="id_usuario_nuevo_lider" id="progNuevoLider" class="form-select" required>
                            <option value="" selected disabled>-- Seleccione el nuevo líder --</option>
                            @foreach($lideres as $lid)
                                @php // Inicia un bloque PHP para preparar datos que usará la vista.
                                    $nomb = $lid->person ? ($lid->person->first_name . ' ' . $lid->person->first_last_name) : ($lid->nickname ?: $lid->email);
                                @endphp
                                <option value="{{ $lid->id }}" data-id="{{ $lid->id }}">{{ $nomb }} ({{ $lid->email }})</option>
                            @endforeach
                        </select>
                        <div class="form-text">El nuevo líder debe ser diferente del líder actual.</div>
                    </div>

                    <div class="mb-3">
                        <label for="progFechaLimite" class="form-label fw-semibold text-dark">Fecha Límite de Entrega <span class="text-danger">*</span></label>
                        <input type="date"
                               name="fecha_limite"
                               id="progFechaLimite"
                               class="form-control"
                               min="{{ date('Y-m-d') }}"
                               required>

                        <div class="form-text">La fecha límite no puede ser anterior a hoy.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-ecp-primary" id="btnGuardarProgramacion">
                        <i class="fa-solid fa-calendar-check me-1"></i> Confirmar Programación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalProgramaciones" tabindex="-1" aria-labelledby="modalProgramacionesLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalProgramacionesLabel">
                        <i class="fa-solid fa-calendar-days" style="color: var(--petroleo-ecp);"></i>
                        <span>Programaciones de Entrega de POE</span>
                    </h5>
                    <div class="text-muted small">Seguimiento de fechas límites, entregas cumplidas, reprogramaciones y alertas por atraso.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0">

                <div class="p-3 bg-light border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="small fw-semibold text-muted">Filtrar por estado:</span>
                        <div class="btn-group btn-group-sm" role="group" id="btnGroupFiltroProg">
                            <button type="button" class="btn btn-outline-secondary active" onclick="filtrarProgramaciones('todos', this)">Todas</button>
                            <button type="button" class="btn btn-outline-warning text-dark" onclick="filtrarProgramaciones('Pendiente', this)">Pendientes</button>
                            <button type="button" class="btn btn-outline-danger" onclick="filtrarProgramaciones('Atrasada', this)">Atrasadas</button>
                            <button type="button" class="btn btn-outline-success" onclick="filtrarProgramaciones('Cumplida', this)">Cumplidas</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="filtrarProgramaciones('Cancelada', this)">Canceladas</button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive p-3">
                    <table class="table table-hover align-middle mb-0" id="tablaModalProgramaciones">
                        <thead class="table-light" style="font-size: 0.85rem;">
                            <tr>
                                <th>POE</th>
                                <th>Sale (Líder Actual)</th>
                                <th>Entra (Nuevo Líder)</th>
                                <th>Fecha Límite</th>
                                <th class="text-center">Estado</th>
                                <th>Novedades / Motivo</th>
                                <th class="text-center" style="min-width: 130px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($todasProgramaciones as $p)
                                @php // Inicia un bloque PHP para preparar datos que usará la vista.
                                    $nomSaliente = $p->liderActual?->person ? ($p->liderActual->person->first_name . ' ' . $p->liderActual->person->first_last_name) : ($p->liderActual?->nickname ?: 'N/A');
                                    $nomEntrante = $p->nuevoLider?->person ? ($p->nuevoLider->person->first_name . ' ' . $p->nuevoLider->person->first_last_name) : ($p->nuevoLider?->nickname ?: 'N/A');
                                    $estadoVis = $p->cancelada ? 'Cancelada' : $p->estado;
                                @endphp
                                <tr class="fila-prog" data-estado="{{ $estadoVis }}">
                                    <td>
                                        <div class="fw-bold" style="font-family: monospace;">{{ $p->poe?->codigo ?? 'N/A' }}</div>
                                        <div class="text-muted small text-truncate" style="max-width: 170px;">{{ $p->poe?->nombre }}</div>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-dark">{{ $nomSaliente }}</div>
                                        <div class="text-muted small" style="font-size: 0.75rem;">{{ $p->liderActual?->email }}</div>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-primary">{{ $nomEntrante }}</div>
                                        <div class="text-muted small" style="font-size: 0.75rem;">{{ $p->nuevoLider?->email }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $p->fecha_limite ? $p->fecha_limite->format('d/m/Y') : 'N/A' }}</div>
                                        @if($p->fecha_original)
                                            <div class="text-muted small" style="font-size: 0.75rem;">
                                                <del title="Fecha previa">{{ $p->fecha_original->format('d/m/Y') }}</del>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($p->cancelada)
                                            <span class="badge badge-estado-cancelada rounded-pill px-2 py-1">Cancelada</span>
                                        @elseif($p->estado === 'Cumplida')
                                            <span class="badge badge-estado-cumplida rounded-pill px-2 py-1">Cumplida</span>
                                        @elseif($p->estado === 'Atrasada')
                                            <span class="badge badge-estado-atrasada rounded-pill px-2 py-1">Atrasada</span>
                                        @else
                                            <span class="badge badge-estado-pendiente rounded-pill px-2 py-1">Pendiente</span>
                                        @endif
                                    </td>
                                    <td style="max-width: 220px;">
                                        @if($p->motivo_cambio)
                                            <div class="small text-muted text-truncate" title="{{ $p->motivo_cambio }}">
                                                <em>"{{ $p->motivo_cambio }}"</em>
                                            </div>
                                        @endif
                                        @if($p->fecha_cumplimiento)
                                            <div class="text-success small" style="font-size: 0.75rem;">
                                                <i class="fa-solid fa-check me-1"></i> Subido: {{ $p->fecha_cumplimiento->format('d/m/Y H:i') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(!$p->cancelada && $p->estado !== 'Cumplida')
                                            <div class="btn-group btn-group-sm" role="group">

                                                <button type="button"
                                                        class="btn btn-outline-warning text-dark"
                                                        onclick="abrirModalReprogramar({{ $p->id_poe_programacion }}, '{{ $p->poe?->codigo }}', '{{ $p->fecha_limite ? $p->fecha_limite->format('Y-m-d') : '' }}', {{ $p->id_usuario_nuevo_lider }})"

                                                        title="Reprogramar entrega">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>

                                                <button type="button"
                                                        class="btn btn-outline-danger"
                                                        onclick="confirmarCancelarProgramacion({{ $p->id_poe_programacion }}, '{{ $p->poe?->codigo }}')"

                                                        title="Cancelar programación">
                                                    <i class="fa-solid fa-ban"></i>
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        No hay programaciones registradas en el sistema.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalReprogramar" tabindex="-1" aria-labelledby="modalReprogramarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form action="" method="POST" id="formReprogramar">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalReprogramarLabel">
                        <i class="fa-solid fa-pen-to-square" style="color: #d97706;"></i>
                        <span>Reprogramar Subida de POE</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="fw-bold" id="reprogPoeCodigo">POE</div>
                        <div class="text-muted small">Modifica la fecha límite o el líder asignado. Se registrará la fecha previa y el motivo del cambio.</div>
                    </div>

                    <div class="mb-3">
                        <label for="reprogNuevoLider" class="form-label fw-semibold text-dark">Nuevo Líder Asignado <span class="text-danger">*</span></label>
                        <select name="id_usuario_nuevo_lider" id="reprogNuevoLider" class="form-select" required>
                            @foreach($lideres as $lid)
                                @php // Inicia un bloque PHP para preparar datos que usará la vista.
                                    $nomb = $lid->person ? ($lid->person->first_name . ' ' . $lid->person->first_last_name) : ($lid->nickname ?: $lid->email);
                                @endphp
                                <option value="{{ $lid->id }}">{{ $nomb }} ({{ $lid->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="reprogFechaLimite" class="form-label fw-semibold text-dark">Nueva Fecha Límite <span class="text-danger">*</span></label>
                        <input type="date"
                               name="fecha_limite"
                               id="reprogFechaLimite"
                               class="form-control"
                               min="{{ date('Y-m-d') }}"
                               required>

                    </div>

                    <div class="mb-3">
                        <label for="reprogMotivo" class="form-label fw-semibold text-dark">Motivo de Reprogramación <span class="text-danger">*</span></label>
                        <textarea name="motivo_cambio"
                                  id="reprogMotivo"
                                  rows="3"
                                  class="form-control"
                                  placeholder="Explica detalladamente por qué se reprograma la entrega del POE..."
                                  required></textarea>

                        <div class="form-text">Campo obligatorio para auditoría y trazabilidad.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold" id="btnGuardarReprogramacion">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Reprogramación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalHistorialLideres" tabindex="-1" aria-labelledby="modalHistorialLideresLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalHistorialLideresLabel">
                        <i class="fa-solid fa-users-gear" style="color: var(--petroleo-ecp);"></i>
                        <span>Historial de Líderes por POE</span>
                    </h5>
                    <div class="text-muted small">Líderes históricos del cargo, periodos de vigencia y versiones subidas por cada uno.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">

                <div class="mb-4">
                    <label for="selectPoeLideres" class="form-label fw-semibold text-dark">Selecciona un POE para consultar su historial:</label>
                    <select id="selectPoeLideres" class="form-select" onchange="consultarLideresPoe(this.value)">
                        <option value="" selected disabled>-- Selecciona un POE --</option>
                        @foreach($poes as $p)
                            <option value="{{ $p->id_poe }}">{{ $p->codigo }} - {{ $p->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="hlLoading" class="text-center py-4 d-none">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="text-muted small mt-2">Cargando historial de liderazgo...</p>
                </div>

                <div id="hlResultados" class="d-none">
                    <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-user-clock me-1 text-success"></i> Periodos de Liderazgo</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm align-middle">
                            <thead class="table-light small">
                                <tr>
                                    <th>Líder</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Fin</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody id="hlTablaPeriodos"></tbody>
                        </table>
                    </div>

                    <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-file-lines me-1 text-primary"></i> Versiones Subidas por Líder</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle">
                            <thead class="table-light small">
                                <tr>
                                    <th>Versión</th>
                                    <th>Fecha Subida</th>
                                    <th>Líder / Autor</th>
                                    <th>Cambio Realizado</th>
                                </tr>
                            </thead>
                            <tbody id="hlTablaVersiones"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPreviewPdf" tabindex="-1" aria-labelledby="modalPreviewPdfLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="height: 90vh;">
        <div class="modal-content h-100">
            <div class="modal-header py-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-file-pdf text-danger fs-5"></i>
                    <h5 class="modal-title fw-bold text-dark mb-0" id="modalPreviewPdfLabel">Previsualización de Documento</h5>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="btnDescargarDesdePreview" class="btn btn-sm btn-ecp-primary" download>
                        <i class="fa-solid fa-download me-1"></i> Descargar
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
            </div>
            <div class="modal-body p-0 h-100 position-relative">
                <div id="pdfLoading" class="position-absolute top-50 start-50 translate-middle text-center">
                    <div class="spinner-border text-success" role="status"></div>
                    <p class="text-muted small mt-2">Cargando documento PDF...</p>
                </div>
                <iframe id="iframePreviewPdf" src="" class="w-100 h-100 border-0" onload="document.getElementById('pdfLoading').classList.add('d-none')"></iframe>
            </div>
        </div>
    </div>
</div>

<form id="formCancelarProgramacion" method="POST" action="" class="d-none">
    @csrf
    @method('PUT')
    <input type="hidden" name="motivo_cambio" id="cancelarMotivoInput">
</form>

@endsection

@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Inicializar tooltips de Bootstrap 5
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Mostrar alertas flash de sesión si existen
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: '¡Operación Exitosa!',
            text: "{{ session('success') }}",
            confirmButtonColor: '#39A900',
            timer: 4000
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Atención',
            text: "{{ session('error') }}",
            confirmButtonColor: '#001A29'
        });
    @endif

    @if(session('warning'))
        Swal.fire({
            icon: 'info',
            title: 'Aviso de Previsualización',
            text: "{{ session('warning') }}",
            confirmButtonColor: '#001A29'
        });
    @endif

    @if(isset($errors) && $errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Errores de Validación',
            html: '<ul class="text-start mb-0">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>',
            confirmButtonColor: '#001A29'
        });
    @endif

    // Búsqueda en vivo (filtra la tabla del DOM si hay contenido)
    const inputBuscar = document.getElementById('inputBuscarPoe');
    if (inputBuscar) {
        inputBuscar.addEventListener('keyup', function () {
            const query = this.value.toLowerCase().trim();
            const filas = document.querySelectorAll('#tbodyPoes tr[id^="fila-poe-"]');
            filas.forEach(fila => {
                const texto = fila.textContent.toLowerCase();
                fila.style.display = texto.includes(query) ? '' : 'none';
            });
        });
    }
});

// Función para abrir previsualización de PDF en Modal
function abrirPreviewPdf(url, titulo) {
    document.getElementById('modalPreviewPdfLabel').textContent = titulo;
    document.getElementById('pdfLoading').classList.remove('d-none');
    document.getElementById('iframePreviewPdf').src = url;
    document.getElementById('btnDescargarDesdePreview').href = url.replace('/ver', '/descargar');

    const modal = new bootstrap.Modal(document.getElementById('modalPreviewPdf'));
    modal.show();
}

// Alerta para archivos Word que no admiten previsualización
function avisoWordDescarga(urlDescarga) {
    Swal.fire({
        icon: 'info',
        title: 'Documento Word (.docx)',
        text: 'Los documentos en formato Word no se pueden previsualizar directamente en el navegador. ¿Deseas descargar el archivo?',
        showCancelButton: true,
        confirmButtonColor: '#39A900',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fa-solid fa-download me-1"></i> Descargar Ahora',
        cancelButtonText: 'Cerrar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = urlDescarga;
        }
    });
}

// Abrir modal de Nueva Versión
function abrirModalNuevaVersion(idPoe, codigo, nombre, tieneProgramacion) {
    const form = document.getElementById('formNuevaVersion');
    form.action = "{{ url('control-ecp/administrador/repositorio/poe') }}/" + idPoe + "/nueva-version";

    document.getElementById('nvCodigoPoe').textContent = codigo;
    document.getElementById('nvNombrePoe').textContent = nombre;
    document.getElementById('nvCambio').value = '';
    document.getElementById('nvArchivo').value = '';

    const aviso = document.getElementById('nvAvisoProgramacion');
    if (tieneProgramacion === '1') {
        aviso.classList.remove('d-none');
    } else {
        aviso.classList.add('d-none');
    }

    const modal = new bootstrap.Modal(document.getElementById('modalNuevaVersion'));
    modal.show();
}

// Cargar Historial de Versiones vía AJAX
function cargarHistorialVersiones(idPoe, codigo) {
    document.getElementById('hvSubtitulo').textContent = 'POE: ' + codigo;
    document.getElementById('hvLoading').classList.remove('d-none');
    document.getElementById('hvContenedor').classList.add('d-none');

    const modal = new bootstrap.Modal(document.getElementById('modalHistorialVersiones'));
    modal.show();

    fetch("{{ url('control-ecp/administrador/repositorio/poe') }}/" + idPoe + "/historial")
        .then(res => res.json())
        .then(data => {
            const tabla = document.getElementById('hvTablaCuerpo');
            tabla.innerHTML = '';

            if (data.versiones && data.versiones.length > 0) {
                data.versiones.forEach(v => {
                    const badgeActual = v.es_actual ? '<span class="badge bg-success ms-1">Actual</span>' : '';
                    const badgeFormato = v.formato === 'PDF' ? '<span class="badge bg-danger-subtle text-danger border">PDF</span>' : '<span class="badge bg-primary-subtle text-primary border">Word</span>';

                    const fila = document.createElement('tr');
                    fila.innerHTML = `
                        <td><strong>v${v.numero_version}</strong> ${badgeActual}</td>
                        <td class="text-muted small">${v.fecha}</td>
                        <td class="small fw-semibold">${v.usuario}</td>
                        <td class="small">${v.cambio}</td>
                        <td>${badgeFormato}</td>
                        <td class="text-center">
                            <a href="${v.url_descargar}" class="btn btn-sm btn-outline-success" title="Descargar versión ${v.numero_version}">
                                <i class="fa-solid fa-download"></i>
                            </a>
                        </td>
                    `;
                    tabla.appendChild(fila);
                });
            } else {
                tabla.innerHTML = '<tr><td colspan="6" class="text-center py-3 text-muted">No se registran versiones.</td></tr>';
            }

            document.getElementById('hvLoading').classList.add('d-none');
            document.getElementById('hvContenedor').classList.remove('d-none');
        })
        .catch(err => {
            document.getElementById('hvLoading').classList.add('d-none');
            Swal.fire('Error', 'No se pudo cargar el historial de versiones: ' + err.message, 'error');
        });
}

// Abrir modal de Programación
function abrirModalProgramar(idPoe, codigo, nombre, idLiderActual, nombreLiderActual, tieneVigente) {
    if (tieneVigente) {
        Swal.fire({
            icon: 'warning',
            title: 'Programación Vigente',
            text: `El POE ${codigo} ya cuenta con una entrega programada activa. Puedes gestionarla, reprogramarla o cancelarla desde el botón "Programaciones".`,
            confirmButtonColor: '#001A29',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    const form = document.getElementById('formProgramarSubida');
    form.action = "{{ url('control-ecp/administrador/repositorio/poe') }}/" + idPoe + "/programar";

    document.getElementById('progPoeCodigo').textContent = codigo;
    document.getElementById('progPoeNombre').textContent = nombre;
    document.getElementById('progLiderActualNombre').textContent = nombreLiderActual;

    // Deshabilitar la opción del líder actual en el select de nuevo líder
    const select = document.getElementById('progNuevoLider');
    select.value = '';
    Array.from(select.options).forEach(opt => {
        if (opt.value == idLiderActual) {
            opt.disabled = true;
            opt.textContent += ' (Líder Actual - No seleccionable)';
        } else {
            opt.disabled = false;
            opt.textContent = opt.textContent.replace(' (Líder Actual - No seleccionable)', '');
        }
    });

    const modal = new bootstrap.Modal(document.getElementById('modalProgramarSubida'));
    modal.show();
}

// Filtrar filas del modal de programaciones
function filtrarProgramaciones(estado, btn) {
    document.querySelectorAll('#btnGroupFiltroProg button').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const filas = document.querySelectorAll('.fila-prog');
    filas.forEach(f => {
        if (estado === 'todos') {
            f.style.display = '';
        } else {
            f.style.display = (f.getAttribute('data-estado') === estado) ? '' : 'none';
        }
    });
}

// Abrir modal de Reprogramación
function abrirModalReprogramar(idProgramacion, codigoPoe, fechaLimite, idNuevoLider) {
    const form = document.getElementById('formReprogramar');
    form.action = "{{ url('control-ecp/administrador/repositorio/poe/programaciones') }}/" + idProgramacion + "/reprogramar";

    document.getElementById('reprogPoeCodigo').textContent = 'POE: ' + codigoPoe;
    document.getElementById('reprogFechaLimite').value = fechaLimite;
    document.getElementById('reprogNuevoLider').value = idNuevoLider;
    document.getElementById('reprogMotivo').value = '';

    const modal = new bootstrap.Modal(document.getElementById('modalReprogramar'));
    modal.show();
}

// Confirmar Cancelación de Programación con SweetAlert2
function confirmarCancelarProgramacion(idProgramacion, codigoPoe) {
    Swal.fire({
        title: '¿Cancelar Programación?',
        text: `Indica el motivo obligatorio de cancelación para el POE ${codigoPoe}:`,
        input: 'textarea',
        inputPlaceholder: 'Escribe aquí el motivo obligatorio de cancelación...',
        inputAttributes: {
            'aria-label': 'Motivo de cancelación'
        },
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, Cancelar Programación',
        cancelButtonText: 'Volver',
        inputValidator: (value) => {
            if (!value || value.trim().length < 5) {
                return 'Debes escribir un motivo de al menos 5 caracteres para cancelar la programación.';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('formCancelarProgramacion');
            form.action = "{{ url('control-ecp/administrador/repositorio/poe/programaciones') }}/" + idProgramacion + "/cancelar";
            document.getElementById('cancelarMotivoInput').value = result.value.trim();
            form.submit();
        }
    });
}

// Consultar Historial de Líderes por POE
function consultarLideresPoe(idPoe) {
    if (!idPoe) return;

    document.getElementById('hlLoading').classList.remove('d-none');
    document.getElementById('hlResultados').classList.add('d-none');

    fetch("{{ url('control-ecp/administrador/repositorio/poe') }}/" + idPoe + "/lideres")
        .then(res => res.json())
        .then(data => {
            // Llenar tabla de periodos
            const tablaPeriodos = document.getElementById('hlTablaPeriodos');
            tablaPeriodos.innerHTML = '';
            if (data.lideres && data.lideres.length > 0) {
                data.lideres.forEach(p => {
                    const badgeActual = p.es_actual ? '<span class="badge bg-success">Actual</span>' : '<span class="badge bg-secondary">Finalizado</span>';
                    const fila = document.createElement('tr');
                    fila.innerHTML = `
                        <td class="fw-semibold">${p.nombre_lider}</td>
                        <td>${p.fecha_inicio}</td>
                        <td>${p.fecha_fin}</td>
                        <td class="text-center">${badgeActual}</td>
                    `;
                    tablaPeriodos.appendChild(fila);
                });
            } else {
                tablaPeriodos.innerHTML = '<tr><td colspan="4" class="text-center py-2 text-muted">No se registran periodos.</td></tr>';
            }

            // Llenar tabla de versiones
            const tablaVersiones = document.getElementById('hlTablaVersiones');
            tablaVersiones.innerHTML = '';
            if (data.versiones && data.versiones.length > 0) {
                data.versiones.forEach(v => {
                    const fila = document.createElement('tr');
                    fila.innerHTML = `
                        <td><strong>v${v.numero_version}</strong></td>
                        <td class="text-muted small">${v.fecha}</td>
                        <td class="small fw-semibold">${v.usuario}</td>
                        <td class="small">${v.cambio}</td>
                    `;
                    tablaVersiones.appendChild(fila);
                });
            } else {
                tablaVersiones.innerHTML = '<tr><td colspan="4" class="text-center py-2 text-muted">No se registran versiones.</td></tr>';
            }

            document.getElementById('hlLoading').classList.add('d-none');
            document.getElementById('hlResultados').classList.remove('d-none');
        })
        .catch(err => {
            document.getElementById('hlLoading').classList.add('d-none');
            Swal.fire('Error', 'No se pudo obtener el historial de líderes: ' + err.message, 'error');
        });
}
</script>
@endpush
