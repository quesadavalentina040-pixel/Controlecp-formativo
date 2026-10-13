@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">

    {{-- ══════════════════════════════════════════════
         CABECERA
    ══════════════════════════════════════════════ --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.administrador.inicio') }}" class="text-decoration-none" style="color:#2E7D32;">
                            <i class="fas fa-house me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item active" style="color:#1B5E20;font-weight:600;">Usuarios</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color:#001A29;font-family:'Outfit',sans-serif;">
                <i class="fas fa-users-gear me-2" style="color:#39A900;"></i>Gestión de Usuarios
            </h2>
            <p class="text-muted small mb-0">Control ECP &bull; CRUD local + Sincronización SICEFA</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            {{-- Botón historial → abre modal --}}
            <button type="button" class="btn btn-outline-secondary rounded-pill px-3 btn-sm"
                    onclick="abrirHistorial()">
                <i class="fas fa-clock-rotate-left me-1"></i> Historial inactivos
            </button>
            {{-- Botón sincronizar → abre modal --}}
            <button type="button" class="btn btn-outline-primary rounded-pill px-3 btn-sm"
                    onclick="abrirSincronizar()">
                <i class="fas fa-arrows-rotate me-1"></i> Sincronizar SICEFA
            </button>
            {{-- Nuevo usuario: sigue siendo página separada (formulario largo) --}}
            <a href="{{ route('controlecp.usuarios.create') }}"
               class="btn rounded-pill px-3 btn-sm text-white" style="background-color:#39A900;border-color:#39A900;">
                <i class="fas fa-user-plus me-1"></i> Nuevo usuario
            </a>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         ALERTAS DE SESIÓN
    ══════════════════════════════════════════════ --}}
    <div id="flashContainer">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                <i class="fas fa-triangle-exclamation me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                <i class="fas fa-circle-info me-2"></i>{{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>
    {{-- Alerta dinámica (para respuestas AJAX) --}}
    <div id="ajaxAlert" class="d-none alert alert-dismissible fade show rounded-3 shadow-sm" role="alert">
        <span id="ajaxAlertMsg"></span>
        <button type="button" class="btn-close" onclick="cerrarAjaxAlert()"></button>
    </div>

    {{-- ══════════════════════════════════════════════
         TARJETAS RESUMEN
    ══════════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center" style="border-radius:14px;">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold" style="color:#39A900;">{{ $totalActivos }}</div>
                    <div class="small text-muted">Activos</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center" style="border-radius:14px;">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold text-secondary">{{ $totalInactivos }}</div>
                    <div class="small text-muted">Inactivos</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center" style="border-radius:14px;">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold" style="color:#1565C0;">{{ $usuarios->total() }}</div>
                    <div class="small text-muted">Resultado filtro</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center" style="border-radius:14px;">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold" style="color:#6A1B9A;">{{ $totalActivos + $totalInactivos }}</div>
                    <div class="small text-muted">Total registros</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         FILTROS
    ══════════════════════════════════════════════ --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('controlecp.usuarios.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold mb-1">Buscar</label>
                    <input type="text" name="buscar" value="{{ request('buscar') }}"
                           class="form-control form-control-sm rounded-pill"
                           placeholder="Nombre, documento o correo…">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold mb-1">Rol</label>
                    <select name="rol" class="form-select form-select-sm rounded-pill">
                        <option value="">Todos los roles</option>
                        @foreach($rolesEcp as $rolOpt)
                            <option value="{{ $rolOpt->slug }}" {{ request('rol') === $rolOpt->slug ? 'selected' : '' }}>
                                {{ $rolOpt->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Estado</label>
                    <select name="estado" class="form-select form-select-sm rounded-pill">
                        <option value="">Todos</option>
                        <option value="activo"   {{ request('estado') === 'activo'   ? 'selected' : '' }}>Activo</option>
                        <option value="inactivo" {{ request('estado') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm rounded-pill text-white flex-grow-1"
                            style="background-color:#39A900;border-color:#39A900;">
                        <i class="fas fa-magnifying-glass me-1"></i> Filtrar
                    </button>
                    <a href="{{ route('controlecp.usuarios.index') }}"
                       class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="fas fa-xmark"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         TABLA PRINCIPAL
    ══════════════════════════════════════════════ --}}
    <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:0.88rem;">
                    <thead style="background-color:#E8F5E9;">
                        <tr>
                            <th class="px-3 py-3 text-muted fw-semibold">#</th>
                            <th class="py-3 text-muted fw-semibold">Usuario</th>
                            <th class="py-3 text-muted fw-semibold d-none d-md-table-cell">Documento</th>
                            <th class="py-3 text-muted fw-semibold d-none d-lg-table-cell">Correo</th>
                            <th class="py-3 text-muted fw-semibold">Rol ECP</th>
                            <th class="py-3 text-muted fw-semibold">Estado</th>
                            <th class="py-3 text-muted fw-semibold d-none d-md-table-cell">Origen</th>
                            <th class="py-3 text-muted fw-semibold text-end pe-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaUsuariosBody">
                        @forelse($usuarios as $usuario)
                            @php
                                $inactivo = $usuario->trashed();
                                $rolEcp   = $usuario->roles->whereIn('slug', ['controlecp.admin','controlecp.instructor','controlecp.apprentice'])->first();
                                $esSync   = $usuario->person && $usuario->person->sena_email;
                                $rolColor = match($rolEcp?->slug) {
                                    'controlecp.admin'      => '#1565C0',
                                    'controlecp.instructor' => '#E65100',
                                    'controlecp.apprentice' => '#2E7D32',
                                    default                 => '#555',
                                };
                            @endphp
                            <tr class="{{ $inactivo ? 'table-secondary text-muted' : '' }}"
                                data-id="{{ $usuario->id }}">
                                <td class="px-3">
                                    {{ $loop->iteration + ($usuarios->currentPage() - 1) * $usuarios->perPage() }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 fw-bold text-white"
                                             style="width:36px;height:36px;font-size:0.78rem;background-color:{{ $inactivo ? '#9E9E9E' : '#39A900' }};">
                                            {{ $usuario->initials }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $usuario->full_name }}</div>
                                            @if($usuario->nickname)
                                                <div class="small text-muted">&#64;{{ $usuario->nickname }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    @if($usuario->person)
                                        <span class="badge bg-light text-dark border">
                                            {{ $usuario->person->document_type ? substr($usuario->person->document_type, 0, 2) : '' }}
                                        </span>
                                        {{ $usuario->person->document_number }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="d-none d-lg-table-cell">{{ $usuario->email }}</td>
                                <td>
                                    @if($rolEcp)
                                        <span class="badge rounded-pill px-3"
                                              style="background-color:{{ $rolColor }};font-size:0.78rem;">
                                            {{ $rolEcp->name }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border">Sin rol ECP</span>
                                    @endif
                                </td>
                                <td>
                                    @if($inactivo)
                                        <span class="badge bg-secondary rounded-pill px-3">
                                            <i class="fas fa-ban me-1"></i>Inactivo
                                        </span>
                                        <div class="small text-muted mt-1" style="font-size:0.73rem;">
                                            Desde {{ $usuario->deleted_at->format('d/m/Y') }}
                                        </div>
                                    @else
                                        <span class="badge rounded-pill px-3" style="background-color:#39A900;">
                                            <i class="fas fa-circle-check me-1"></i>Activo
                                        </span>
                                    @endif
                                </td>
                                <td class="d-none d-md-table-cell">
                                    @if($esSync)
                                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2" style="font-size:0.73rem;">
                                            <i class="fas fa-cloud-arrow-down me-1"></i>SICEFA
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border rounded-pill px-2" style="font-size:0.73rem;">
                                            <i class="fas fa-hand me-1"></i>Manual
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-flex justify-content-end gap-1 flex-wrap">
                                        {{-- Editar → abre modal --}}
                                        <button type="button"
                                                class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                                title="Editar"
                                                onclick="abrirEditar({{ $usuario->id }})">
                                            <i class="fas fa-pen-to-square"></i>
                                        </button>

                                        @if($inactivo)
                                            {{-- Reactivar → AJAX --}}
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-success rounded-pill px-3"
                                                    title="Reactivar"
                                                    onclick="cambiarEstado({{ $usuario->id }}, 'activar', '{{ addslashes($usuario->full_name) }}')">
                                                <i class="fas fa-rotate-right"></i>
                                            </button>
                                        @else
                                            {{-- Desactivar → AJAX --}}
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                                    title="Desactivar"
                                                    onclick="cambiarEstado({{ $usuario->id }}, 'desactivar', '{{ addslashes($usuario->full_name) }}')">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-users-slash fa-2x mb-2 d-block"></i>
                                    No se encontraron usuarios con los filtros aplicados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($usuarios->hasPages())
                <div class="px-3 py-3 border-top">{{ $usuarios->links() }}</div>
            @endif
        </div>
    </div>

</div>{{-- /container --}}


{{-- ══════════════════════════════════════════════════════════════════
     MODAL 1 — EDITAR USUARIO
══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:18px;border:none;">

            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="modalEditarLabel" style="color:#001A29;">
                        <i class="fas fa-pen-to-square me-2" style="color:#39A900;"></i>Editar Usuario
                    </h5>
                    <p class="text-muted small mb-0" id="editarSubtitulo">Cargando datos…</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body px-4 py-3">

                {{-- Spinner de carga --}}
                <div id="editarSpinner" class="text-center py-5">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Cargando…</span>
                    </div>
                </div>

                {{-- Alerta de usuario inactivo --}}
                <div id="editarAlertaInactivo" class="alert alert-warning rounded-3 d-none small">
                    <i class="fas fa-triangle-exclamation me-1"></i>
                    Este usuario está <strong>inactivo</strong>. Puedes editarlo, pero no podrá iniciar sesión hasta reactivarlo.
                </div>

                {{-- Errores de validación (respuesta del servidor) --}}
                <div id="editarErrores" class="alert alert-danger rounded-3 d-none small">
                    <ul id="editarErroresList" class="mb-0 ps-3"></ul>
                </div>

                <form id="formEditar" autocomplete="off" class="d-none">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="usuario_id" id="editarUserId">

                    <div class="row g-3">
                        {{-- Columna izquierda: datos personales --}}
                        <div class="col-lg-6">
                            <p class="fw-semibold small mb-2" style="color:#1B5E20;">
                                <i class="fas fa-id-card me-1"></i>Datos personales
                            </p>

                            <div class="mb-2">
                                <label class="form-label small fw-semibold mb-1">Documento</label>
                                <input type="text" id="editarDocumento" class="form-control form-control-sm bg-light" readonly>
                                <div class="form-text">No editable desde aquí.</div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label small fw-semibold mb-1">Primer nombre <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" id="editarFirstName"
                                       class="form-control form-control-sm" required maxlength="100">
                            </div>

                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold mb-1">Primer apellido <span class="text-danger">*</span></label>
                                    <input type="text" name="first_last_name" id="editarFirstLastName"
                                           class="form-control form-control-sm" required maxlength="100">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold mb-1">Segundo apellido</label>
                                    <input type="text" name="second_last_name" id="editarSecondLastName"
                                           class="form-control form-control-sm" maxlength="100">
                                </div>
                            </div>

                            <div class="mt-2">
                                <label class="form-label small fw-semibold mb-1">Teléfono</label>
                                <input type="text" name="telephone1" id="editarTelephone"
                                       class="form-control form-control-sm" maxlength="20">
                            </div>
                        </div>

                        {{-- Columna derecha: credenciales --}}
                        <div class="col-lg-6">
                            <p class="fw-semibold small mb-2" style="color:#1B5E20;">
                                <i class="fas fa-lock me-1"></i>Credenciales y Rol
                            </p>

                            <div class="mb-2">
                                <label class="form-label small fw-semibold mb-1">Correo <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="editarEmail"
                                       class="form-control form-control-sm" required maxlength="191">
                            </div>

                            <div class="mb-2">
                                <label class="form-label small fw-semibold mb-1">Nickname</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">&#64;</span>
                                    <input type="text" name="nickname" id="editarNickname"
                                           class="form-control" maxlength="60">
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label small fw-semibold mb-1">Nueva contraseña</label>
                                <input type="password" name="password" id="editarPassword"
                                       class="form-control form-control-sm"
                                       placeholder="Dejar vacío para no cambiar" minlength="8">
                            </div>

                            <div class="mb-2">
                                <label class="form-label small fw-semibold mb-1">Confirmar contraseña</label>
                                <input type="password" name="password_confirmation"
                                       class="form-control form-control-sm"
                                       placeholder="Solo si cambias la contraseña" minlength="8">
                            </div>

                            <div class="mb-0">
                                <label class="form-label small fw-semibold mb-1">Rol ECP <span class="text-danger">*</span></label>
                                <select name="rol_id" id="editarRolId" class="form-select form-select-sm" required>
                                    <option value="">— Seleccionar —</option>
                                    @foreach($rolesEcp as $rolOpt)
                                        <option value="{{ $rolOpt->id }}">{{ $rolOpt->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer border-0 px-4 pb-4 pt-0">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                        data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn rounded-pill px-5 text-white fw-semibold"
                        style="background-color:#39A900;border-color:#39A900;"
                        id="btnGuardarEditar" onclick="guardarEditar()">
                    <i class="fas fa-floppy-disk me-2"></i>Guardar cambios
                </button>
            </div>
        </div>
    </div>
</div>


{{-- ══════════════════════════════════════════════════════════════════
     MODAL 2 — HISTORIAL DE INACTIVOS
══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalHistorial" tabindex="-1" aria-labelledby="modalHistorialLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:18px;border:none;">

            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="modalHistorialLabel" style="color:#001A29;">
                        <i class="fas fa-clock-rotate-left me-2" style="color:#39A900;"></i>Historial — Usuarios Desactivados
                    </h5>
                    <p class="text-muted small mb-0">
                        Los registros se conservan íntegros. Solo se bloquea el acceso al sistema.
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body px-4 py-3">

                <div id="historialSpinner" class="text-center py-5">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Cargando…</span>
                    </div>
                </div>

                <div id="historialContenido" class="d-none">
                    <div class="alert alert-info rounded-3 small mb-3 d-flex gap-2 align-items-start"
                         style="border-left:4px solid #0288D1;">
                        <i class="fas fa-circle-info mt-1 text-info flex-shrink-0"></i>
                        <span>
                            Al desactivar un usuario se marca <code>deleted_at</code> en <code>users</code>,
                            preservando su historial completo. Puedes reactivarlo en cualquier momento.
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size:0.86rem;">
                            <thead style="background-color:#FFF3E0;">
                                <tr>
                                    <th class="px-3 py-2 text-muted fw-semibold">#</th>
                                    <th class="py-2 text-muted fw-semibold">Usuario</th>
                                    <th class="py-2 text-muted fw-semibold">Documento</th>
                                    <th class="py-2 text-muted fw-semibold d-none d-md-table-cell">Correo</th>
                                    <th class="py-2 text-muted fw-semibold">Rol</th>
                                    <th class="py-2 text-muted fw-semibold">Desactivado el</th>
                                    <th class="py-2 text-muted fw-semibold text-end pe-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="historialTbody">
                                {{-- Se llena con JS --}}
                            </tbody>
                        </table>
                    </div>

                    <div id="historialVacio" class="text-center py-4 text-muted d-none">
                        <i class="fas fa-circle-check fa-2x mb-2 d-block text-success"></i>
                        No hay usuarios desactivados. ¡Todos los registros están activos!
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 px-4 pb-4 pt-0">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                        data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


{{-- ══════════════════════════════════════════════════════════════════
     MODAL 3 — SINCRONIZAR SICEFA
══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalSincronizar" tabindex="-1" aria-labelledby="modalSincronizarLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:18px;border:none;">

            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="modalSincronizarLabel" style="color:#001A29;">
                        <i class="fas fa-arrows-rotate me-2" style="color:#39A900;"></i>Sincronización con SICEFA
                    </h5>
                    <p class="text-muted small mb-0">Vista previa — ningún cambio se aplica hasta que confirmes</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body px-4 py-3">

                {{-- Paso 1: selector de tipo --}}
                <div id="syncPaso1">
                    <p class="fw-semibold mb-3">¿Qué personas traer de SICEFA?</p>
                    <div class="d-flex gap-3 mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="syncTipo"
                                   id="syncInst" value="instructores" checked>
                            <label class="form-check-label" for="syncInst">
                                <i class="fas fa-chalkboard-teacher me-1"></i>Instructores
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="syncTipo"
                                   id="syncApren" value="aprendices">
                            <label class="form-check-label" for="syncApren">
                                <i class="fas fa-user-graduate me-1"></i>Aprendices
                            </label>
                        </div>
                    </div>
                    <button type="button" class="btn rounded-pill text-white px-5"
                            style="background-color:#1565C0;border-color:#1565C0;"
                            onclick="consultarSicefaModal()">
                        <i class="fas fa-cloud-arrow-down me-1"></i> Consultar API SICEFA
                    </button>
                </div>

                {{-- Spinner de consulta --}}
                <div id="syncSpinner" class="text-center py-5 d-none">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Consultando SICEFA…</span>
                    </div>
                    <p class="text-muted small mt-2">Consultando API SICEFA…</p>
                </div>

                {{-- Paso 2: tabla de preview --}}
                <div id="syncPaso2" class="d-none">

                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="badge rounded-pill px-3" style="background-color:#2E7D32;">Nuevo</span>
                            <span class="badge rounded-pill px-3" style="background-color:#E65100;">Con cambios</span>
                            <span class="badge bg-light text-muted border rounded-pill px-3">Sin cambios</span>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                                    onclick="seleccionarTodosSyncModal(true)">
                                <i class="fas fa-check-double me-1"></i>Todos
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                                    onclick="seleccionarTodosSyncModal(false)">
                                <i class="fas fa-square me-1"></i>Ninguno
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3"
                                    onclick="seleccionarSoloNuevosSync()">
                                <i class="fas fa-star me-1"></i>Solo nuevos
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height:380px;overflow-y:auto;">
                        <table class="table table-hover align-middle mb-0" style="font-size:0.84rem;">
                            <thead class="sticky-top" style="background-color:#E8F5E9;">
                                <tr>
                                    <th class="px-2 py-2">
                                        <input type="checkbox" id="syncCheckAll" class="form-check-input"
                                               onchange="seleccionarTodosSyncModal(this.checked)">
                                    </th>
                                    <th class="py-2 text-muted fw-semibold">Persona</th>
                                    <th class="py-2 text-muted fw-semibold">Documento</th>
                                    <th class="py-2 text-muted fw-semibold d-none d-lg-table-cell">Correo</th>
                                    <th class="py-2 text-muted fw-semibold">Estado BD</th>
                                    <th class="py-2 text-muted fw-semibold">Rol a asignar</th>
                                    <th class="py-2 text-muted fw-semibold d-none d-xl-table-cell">Cambios</th>
                                </tr>
                            </thead>
                            <tbody id="syncTbody">
                                {{-- Se llena con JS --}}
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-warning rounded-3 mt-3 small">
                        <i class="fas fa-triangle-exclamation me-1"></i>
                        Los datos personales de filas marcadas se escribirán en <code>people</code>.
                        La API <strong>nunca</strong> escribe contraseñas ni roles — eso se gestiona aquí.
                        Personas sin rol asignado solo actualizan <code>people</code>, sin crear cuenta.
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-2">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                                onclick="volverSyncPaso1()">
                            <i class="fas fa-arrow-left me-1"></i>Consultar de nuevo
                        </button>
                        <button type="button" class="btn rounded-pill px-5 text-white fw-semibold"
                                style="background-color:#39A900;border-color:#39A900;"
                                onclick="aplicarSincronizacion()">
                            <i class="fas fa-check me-2"></i>Aplicar sincronización
                        </button>
                    </div>
                </div>

            </div>

            <div class="modal-footer border-0 px-4 pb-4 pt-0">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                        data-bs-dismiss="modal">Cancelar — no aplicar nada</button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     JAVASCRIPT
══════════════════════════════════════════════════════════════════ --}}
<script>
const CSRF = '{{ csrf_token() }}';

// Roles ECP disponibles (para el modal de sincronizar)
const ROLES_ECP_OPCIONES = @json($rolesEcp->map(fn($r) => ['id' => $r->id, 'name' => $r->name, 'slug' => $r->slug]));

// Datos de preview guardados para el envío
let syncPreviewData = [];
let syncTipoActual  = 'instructores';

// ─── Helpers de alerta dinámica ──────────────────────────────────────────────

function mostrarAjaxAlert(tipo, msg) {
    const el = document.getElementById('ajaxAlert');
    el.className = `alert alert-${tipo} alert-dismissible fade show rounded-3 shadow-sm`;
    document.getElementById('ajaxAlertMsg').innerHTML = msg;
    el.classList.remove('d-none');
    window.scrollTo({ top: 0, behavior: 'smooth' });
    setTimeout(() => { if (!el.classList.contains('d-none')) cerrarAjaxAlert(); }, 6000);
}

function cerrarAjaxAlert() {
    const el = document.getElementById('ajaxAlert');
    el.classList.add('d-none');
}

// ─── MODAL 1: EDITAR ─────────────────────────────────────────────────────────

function abrirEditar(id) {
    // Limpiar estado anterior
    document.getElementById('editarSpinner').classList.remove('d-none');
    document.getElementById('formEditar').classList.add('d-none');
    document.getElementById('editarAlertaInactivo').classList.add('d-none');
    document.getElementById('editarErrores').classList.add('d-none');
    document.getElementById('editarSubtitulo').textContent = 'Cargando datos…';

    const modal = new bootstrap.Modal(document.getElementById('modalEditar'));
    modal.show();

    fetch(`{{ url('control-ecp/administrador/usuarios') }}/${id}/datos`, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) { mostrarAjaxAlert('danger', data.error); modal.hide(); return; }

        document.getElementById('editarUserId').value        = data.id;
        document.getElementById('editarDocumento').value     = `${data.document_type} ${data.document_number}`;
        document.getElementById('editarFirstName').value     = data.first_name;
        document.getElementById('editarFirstLastName').value = data.first_last_name;
        document.getElementById('editarSecondLastName').value = data.second_last_name;
        document.getElementById('editarTelephone').value     = data.telephone1;
        document.getElementById('editarEmail').value         = data.email;
        document.getElementById('editarNickname').value      = data.nickname ?? '';
        document.getElementById('editarPassword').value      = '';
        document.getElementById('editarRolId').value         = data.rol_id ?? '';
        document.getElementById('editarSubtitulo').textContent = data.first_name + ' ' + data.first_last_name;

        if (data.trashed) {
            document.getElementById('editarAlertaInactivo').classList.remove('d-none');
        }

        document.getElementById('editarSpinner').classList.add('d-none');
        document.getElementById('formEditar').classList.remove('d-none');
    })
    .catch(() => {
        mostrarAjaxAlert('danger', 'Error al cargar los datos del usuario.');
        modal.hide();
    });
}

function guardarEditar() {
    const id  = document.getElementById('editarUserId').value;
    const btn = document.getElementById('btnGuardarEditar');
    const errContainer = document.getElementById('editarErrores');
    const errList      = document.getElementById('editarErroresList');

    errContainer.classList.add('d-none');
    errList.innerHTML = '';
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando…';

    const form    = document.getElementById('formEditar');
    const formData = new FormData(form);
    formData.append('_method', 'PUT');

    fetch(`{{ url('control-ecp/administrador/usuarios') }}/${id}`, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: formData,
    })
    .then(async r => {
        const json = await r.json();
        if (r.ok && json.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalEditar')).hide();
            mostrarAjaxAlert('success', '<i class="fas fa-check-circle me-2"></i>' + json.message);
            setTimeout(() => location.reload(), 1200);
        } else if (r.status === 422) {
            // Errores de validación Laravel
            const errors = json.errors || {};
            const msgs = Object.values(errors).flat();
            msgs.forEach(m => {
                const li = document.createElement('li');
                li.textContent = m;
                errList.appendChild(li);
            });
            errContainer.classList.remove('d-none');
        } else {
            mostrarAjaxAlert('danger', json.message || 'Error al guardar los cambios.');
        }
    })
    .catch(() => mostrarAjaxAlert('danger', 'Error de red al guardar.'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-floppy-disk me-2"></i>Guardar cambios';
    });
}

// ─── MODAL 2: HISTORIAL ──────────────────────────────────────────────────────

function abrirHistorial() {
    document.getElementById('historialSpinner').classList.remove('d-none');
    document.getElementById('historialContenido').classList.add('d-none');

    const modal = new bootstrap.Modal(document.getElementById('modalHistorial'));
    modal.show();

    fetch('{{ route('controlecp.usuarios.historial') }}', {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
    })
    .then(r => r.json())
    .then(data => {
        const tbody = document.getElementById('historialTbody');
        const vacio = document.getElementById('historialVacio');
        tbody.innerHTML = '';

        if (!data.inactivos || data.inactivos.length === 0) {
            vacio.classList.remove('d-none');
        } else {
            vacio.classList.add('d-none');
            data.inactivos.forEach((u, i) => {
                tbody.innerHTML += `
                <tr>
                    <td class="px-3">${i + 1}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                                 style="width:34px;height:34px;font-size:0.76rem;background-color:#9E9E9E;">
                                ${u.initials}
                            </div>
                            <div>
                                <div class="fw-semibold text-muted">${u.full_name}</div>
                                ${u.nickname ? `<div class="small text-muted">@${u.nickname}</div>` : ''}
                            </div>
                        </div>
                    </td>
                    <td class="text-muted">${u.document_number}</td>
                    <td class="text-muted d-none d-md-table-cell">${u.email}</td>
                    <td><span class="badge bg-secondary rounded-pill px-2" style="font-size:0.75rem;">${u.rol_name}</span></td>
                    <td>
                        <span class="text-danger fw-semibold">${u.deleted_date}</span>
                    </td>
                    <td class="text-end pe-3">
                        <button type="button"
                                class="btn btn-sm btn-outline-success rounded-pill px-3"
                                onclick="reactivarDesdeHistorial(${u.id}, '${u.full_name.replace(/'/g,"\\'")}')">
                            <i class="fas fa-rotate-right me-1"></i>Reactivar
                        </button>
                    </td>
                </tr>`;
            });
        }

        document.getElementById('historialSpinner').classList.add('d-none');
        document.getElementById('historialContenido').classList.remove('d-none');
    })
    .catch(() => {
        mostrarAjaxAlert('danger', 'Error al cargar el historial.');
        bootstrap.Modal.getInstance(document.getElementById('modalHistorial')).hide();
    });
}

function reactivarDesdeHistorial(id, nombre) {
    if (!confirm(`¿Reactivar a ${nombre}?`)) return;

    fetch(`{{ url('control-ecp/administrador/usuarios') }}/${id}/activar`, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF,
                   'Content-Type': 'application/x-www-form-urlencoded' },
        body: '_method=PATCH',
    })
    .then(r => r.json())
    .then(data => {
        bootstrap.Modal.getInstance(document.getElementById('modalHistorial')).hide();
        mostrarAjaxAlert(data.success ? 'success' : 'danger',
            '<i class="fas fa-' + (data.success ? 'check-circle' : 'triangle-exclamation') + ' me-2"></i>' + data.message);
        if (data.success) setTimeout(() => location.reload(), 1200);
    })
    .catch(() => mostrarAjaxAlert('danger', 'Error de red al reactivar.'));
}

// ─── MODAL 3: SINCRONIZAR ────────────────────────────────────────────────────

function abrirSincronizar() {
    volverSyncPaso1();
    new bootstrap.Modal(document.getElementById('modalSincronizar')).show();
}

function volverSyncPaso1() {
    document.getElementById('syncPaso1').classList.remove('d-none');
    document.getElementById('syncPaso2').classList.add('d-none');
    document.getElementById('syncSpinner').classList.add('d-none');
}

function consultarSicefaModal() {
    syncTipoActual = document.querySelector('input[name="syncTipo"]:checked').value;

    document.getElementById('syncPaso1').classList.add('d-none');
    document.getElementById('syncPaso2').classList.add('d-none');
    document.getElementById('syncSpinner').classList.remove('d-none');

    fetch(`{{ route('controlecp.usuarios.sincronizar.preview') }}?tipo=${syncTipoActual}`, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) {
            mostrarAjaxAlert('warning', data.error);
            volverSyncPaso1();
            return;
        }

        syncPreviewData = data.preview || [];
        renderizarTablaSync(syncPreviewData, data.rolesEcp || []);

        document.getElementById('syncSpinner').classList.add('d-none');
        document.getElementById('syncPaso2').classList.remove('d-none');
    })
    .catch(() => {
        mostrarAjaxAlert('danger', 'Error al consultar la API SICEFA.');
        volverSyncPaso1();
    });
}

function renderizarTablaSync(preview, rolesDisponibles) {
    const tbody = document.getElementById('syncTbody');
    tbody.innerHTML = '';

    if (preview.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-muted">
            La API no devolvió registros.</td></tr>`;
        return;
    }

    const opcionesRol = rolesDisponibles.map(r =>
        `<option value="${r.id}" data-slug="${r.slug}">${r.name}</option>`
    ).join('');

    preview.forEach((item, i) => {
        const d       = item.datos || {};
        const esNuevo = item.es_nuevo;
        const cambios = item.cambios || {};
        const tieneUser = item.tiene_user;

        let badgeColor, badgeLabel;
        if (esNuevo)                      { badgeColor = '#2E7D32'; badgeLabel = 'Nuevo'; }
        else if (Object.keys(cambios).length > 0) { badgeColor = '#E65100'; badgeLabel = 'Con cambios'; }
        else                              { badgeColor = '#9E9E9E'; badgeLabel = 'Sin cambios'; }

        const preChecked = esNuevo || Object.keys(cambios).length > 0;

        // Construir HTML de cambios
        let htmlCambios = '—';
        if (Object.keys(cambios).length > 0) {
            htmlCambios = '<ul class="mb-0 ps-3" style="font-size:0.78rem;">';
            for (const [campo, vals] of Object.entries(cambios)) {
                htmlCambios += `<li><strong>${campo}:</strong> <span class="text-muted">${vals.actual || '(vacío)'}</span> → <strong>${vals.nuevo || '(vacío)'}</strong></li>`;
            }
            htmlCambios += '</ul>';
        }

        // Select de rol
        let rolSelect = tieneUser && !esNuevo
            ? `<span class="text-success small"><i class="fas fa-check me-1"></i>Ya asignado</span>
               <input type="hidden" name="registros[${i}][rol_id]" value="">`
            : `<select name="registros[${i}][rol_id]" class="form-select form-select-sm rounded-pill sync-rol-select"
                       style="min-width:150px;" data-tipo="${syncTipoActual}" data-index="${i}">
                   <option value="">— Sin cuenta —</option>
                   ${opcionesRol}
               </select>`;

        tbody.innerHTML += `
        <tr data-nuevo="${esNuevo ? 1 : 0}">
            <td class="px-2">
                <input type="checkbox" name="registros[${i}][aplicar]" value="1"
                       class="form-check-input sync-chk" ${preChecked ? 'checked' : ''}>
                <input type="hidden" name="registros[${i}][doc_number]"     value="${d.document_number || ''}">
                <input type="hidden" name="registros[${i}][document_type]"  value="${d.document_type || 'Cédula de ciudadanía'}">
                <input type="hidden" name="registros[${i}][first_name]"     value="${d.first_name || ''}">
                <input type="hidden" name="registros[${i}][first_last_name]"  value="${d.first_last_name || ''}">
                <input type="hidden" name="registros[${i}][second_last_name]" value="${d.second_last_name || ''}">
                <input type="hidden" name="registros[${i}][email]"          value="${d.email || ''}">
                <input type="hidden" name="registros[${i}][telephone1]"     value="${d.telephone1 || ''}">
            </td>
            <td>
                <span class="fw-semibold">
                    ${((d.first_name || '') + ' ' + (d.first_last_name || '') + ' ' + (d.second_last_name || '')).trim().toUpperCase()}
                </span>
            </td>
            <td class="text-muted">${d.document_number || '—'}</td>
            <td class="text-muted d-none d-lg-table-cell">${d.email || '—'}</td>
            <td>
                <span class="badge rounded-pill px-2" style="background-color:${badgeColor};font-size:0.74rem;">
                    ${badgeLabel}
                </span>
                ${!tieneUser && !esNuevo ? '<div class="small text-warning mt-1">Sin cuenta</div>' : ''}
            </td>
            <td>${rolSelect}</td>
            <td class="d-none d-xl-table-cell">${htmlCambios}</td>
        </tr>`;
    });

    // Pre-seleccionar rol según tipo
    document.querySelectorAll('.sync-rol-select').forEach(sel => {
        const tipo = sel.dataset.tipo;
        for (const opt of sel.options) {
            if (tipo === 'instructores' && opt.dataset.slug && opt.dataset.slug.includes('instructor')) {
                sel.value = opt.value;
                break;
            }
            if (tipo === 'aprendices' && opt.dataset.slug && opt.dataset.slug.includes('apprentice')) {
                sel.value = opt.value;
                break;
            }
        }
    });
}

function seleccionarTodosSyncModal(val) {
    document.querySelectorAll('.sync-chk').forEach(c => c.checked = val);
    const master = document.getElementById('syncCheckAll');
    if (master) master.checked = val;
}

function seleccionarSoloNuevosSync() {
    document.querySelectorAll('.sync-chk').forEach(c => {
        const fila = c.closest('tr');
        c.checked = fila && fila.dataset.nuevo === '1';
    });
}

function aplicarSincronizacion() {
    const checks  = document.querySelectorAll('.sync-chk');
    const marcados = [...checks].filter(c => c.checked).length;
    if (marcados === 0) {
        alert('Selecciona al menos un registro para sincronizar.');
        return;
    }

    if (!confirm(`¿Aplicar la sincronización de ${marcados} registro(s)?`)) return;

    // Recopilar todos los inputs dentro de syncTbody
    const tbody    = document.getElementById('syncTbody');
    const allInputs = tbody.querySelectorAll('input, select');
    const formData  = new FormData();
    formData.append('_token', CSRF);
    allInputs.forEach(inp => {
        if (inp.name) {
            if (inp.type === 'checkbox') {
                if (inp.checked) formData.append(inp.name, inp.value);
            } else {
                formData.append(inp.name, inp.value);
            }
        }
    });

    fetch('{{ route('controlecp.usuarios.sincronizar.aplicar') }}', {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: formData,
    })
    .then(async r => {
        const json = await r.json();
        bootstrap.Modal.getInstance(document.getElementById('modalSincronizar')).hide();
        if (r.ok) {
            mostrarAjaxAlert('success', '<i class="fas fa-check-circle me-2"></i>' + (json.message || '¡Sincronización aplicada!'));
            setTimeout(() => location.reload(), 1500);
        } else {
            mostrarAjaxAlert('danger', json.message || 'Error al aplicar la sincronización.');
        }
    })
    .catch(() => mostrarAjaxAlert('danger', 'Error de red al sincronizar.'));
}

// ─── Activar / Desactivar desde tabla principal (AJAX) ───────────────────────

function cambiarEstado(id, accion, nombre) {
    const msg = accion === 'desactivar'
        ? `¿Desactivar a ${nombre}? El historial se conservará íntegro.`
        : `¿Reactivar a ${nombre}?`;
    if (!confirm(msg)) return;

    fetch(`{{ url('control-ecp/administrador/usuarios') }}/${id}/${accion}`, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF,
                   'Content-Type': 'application/x-www-form-urlencoded' },
        body: '_method=PATCH',
    })
    .then(r => r.json())
    .then(data => {
        mostrarAjaxAlert(data.success ? 'success' : 'danger',
            '<i class="fas fa-' + (data.success ? 'check-circle' : 'triangle-exclamation') + ' me-2"></i>' + data.message);
        if (data.success) setTimeout(() => location.reload(), 1200);
    })
    .catch(() => mostrarAjaxAlert('danger', 'Error de red.'));
}

// ─── Abrir modal automáticamente si viene ?modal= ────────────────────────────
window.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    if (params.get('modal') === 'historial')    abrirHistorial();
    if (params.get('modal') === 'sincronizar')  abrirSincronizar();
});
</script>
@endsection
