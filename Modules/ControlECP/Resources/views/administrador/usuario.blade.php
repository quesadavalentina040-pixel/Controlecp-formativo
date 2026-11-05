@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    :root {
        --ecp-green: #39A900;
        --ecp-green-hover: #2e8800;
        --ecp-green-light: #e8f8e8;
        --ecp-navy: #001A29;
        --ecp-navy-light: #00324D;
        --ecp-bg-soft: #f8faf9;
    }

    .stat-card {
        border-radius: 18px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 2px solid transparent;
    }
    .stat-card-total {
        border-color: rgba(57, 169, 0, .35);
    }
    .stat-card-active {
        border-color: rgba(30, 112, 0, .35);
    }
    .stat-card-inactive {
        border-color: rgba(192, 57, 43, .35);
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08) !important;
    }

    .badge-role-admin {
        background: linear-gradient(135deg, #e8f8e8 0%, #d4f3d4 100%);
        color: #1e7000;
        border: 1px solid #c2eec2;
    }
    .badge-role-instructor {
        background: linear-gradient(135deg, #e8f4fc 0%, #d3ebfa 100%);
        color: #0b5e94;
        border: 1px solid #bce1f7;
    }
    .badge-role-apprentice {
        background: linear-gradient(135deg, #fef9e8 0%, #fdf2cd 100%);
        color: #9c6c03;
        border: 1px solid #fce8a9;
    }

    .avatar-circle {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.95rem;
        flex-shrink: 0;
    }

    .table-users tbody tr {
        transition: background-color 0.15s ease;
    }
    .table-users tbody tr:hover {
        background-color: #f6fcf8;
    }

    .pulse-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 0 0 rgba(57, 169, 0, 0.7);
        animation: pulse-green-dot 1.8s infinite;
    }
    @keyframes pulse-green-dot {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(57, 169, 0, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(57, 169, 0, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(57, 169, 0, 0); }
    }

    .credential-card {
        background: linear-gradient(145deg, #ffffff 0%, #f4fbf6 100%);
        border-radius: 20px;
        border: 2px solid #e1f3e6;
        position: relative;
        overflow: hidden;
    }
    .credential-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 8px;
        background: linear-gradient(90deg, #39A900 0%, #62E31D 50%, #001A29 100%);
    }

    .filter-pill {
        border-radius: 20px;
        font-size: 0.82rem;
        font-weight: 600;
        padding: 6px 14px;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .filter-pill.active {
        background-color: var(--ecp-green);
        color: #ffffff !important;
        box-shadow: 0 4px 10px rgba(57, 169, 0, 0.3);
    }
    .filter-pill:not(.active) {
        background-color: #f1f4f3;
        color: #001A29;
    }
    .filter-pill:not(.active):hover {
        background-color: #e5ece9;
    }

    .btn-action-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        background-color: #f1f5f9;
        color: var(--ecp-navy);
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
    .btn-action-edit:hover {
        background-color: #e2e8f0;
        color: #475569;
    }
    .btn-action-enable:hover {
        background-color: #dcfce7;
        color: #16a34a;
    }
    .btn-action-disable:hover {
        background-color: #fee2e2;
        color: #dc2626;
    }

    .usuarios-header-card {
        background-color: var(--ecp-navy);
        border-radius: 24px;
        padding: 1.25rem 1.5rem;
    }

    .usuarios-header-card h2 {
        color: #ffffff !important;
    }

    .usuarios-header-card p {
        color: #c7d4da !important;
    }

    .usuarios-header-card .btn-outline-secondary,
    .usuarios-header-card .btn-outline-primary,
    .usuarios-header-card .btn-outline-success {
        color: #ffffff;
        border-color: rgba(255, 255, 255, .75);
    }

    .usuarios-filter-bar {
        background-color: var(--ecp-navy) !important;
    }

    .usuarios-filter-bar .form-select,
    .usuarios-filter-bar .form-control,
    .usuarios-filter-bar .input-group-text {
        border-color: #ffffff;
        color: var(--ecp-navy);
    }

    .usuarios-filter-bar .form-select {
        background-color: #ffffff;
    }

    .usuarios-filter-bar .form-select:focus,
    .usuarios-filter-bar .form-control:focus {
        border-color: var(--ecp-green);
        box-shadow: 0 0 0 0.2rem rgba(57, 169, 0, 0.25);
    }

    .usuarios-filter-bar .input-group-text {
        background-color: #ffffff;
    }

    .usuarios-filter-bar .input-group-text.text-success {
        color: var(--ecp-green) !important;
    }

    .usuarios-filter-bar .btn-filter {
        background-color: var(--ecp-green);
        border-color: var(--ecp-green);
    }

    .usuarios-filter-bar .btn-filter:hover,
    .usuarios-filter-bar .btn-filter:focus {
        background-color: var(--ecp-green-hover);
        border-color: var(--ecp-green-hover);
    }

    .btn-clear-filters {
        color: var(--ecp-navy);
        border-color: var(--ecp-navy-light);
    }

    .btn-clear-filters:hover {
        color: #ffffff;
        background-color: var(--ecp-navy);
        border-color: var(--ecp-navy);
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-3 py-3">

    <div class="usuarios-header-card d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small fw-semibold mb-1" style="color: var(--ecp-green);">
                <i class="fas fa-users-gear me-2"></i>Control ECP · Panel administrativo
            </div>
            <h2 class="h3 fw-bold mb-0" style="font-family: 'Outfit', sans-serif;">
                <i class="fas fa-users-gear me-2" style="color: var(--ecp-green);"></i>Gestión de Usuarios
            </h2>
            <p class="text-muted small mb-0">Control ECP &bull; Gestiona y supervisa los usuarios del sistema</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <button type="button"
               class="btn text-white rounded-pill px-3 btn-sm shadow-sm"
               data-bs-toggle="modal"
               data-bs-target="#nuevoUsuarioModal"
               style="background-color: var(--ecp-green); border-color: var(--ecp-green);">
                <i class="fas fa-user-plus me-1"></i> Nuevo Usuario
            </button>
        </div>
    </div>

    <!-- Alertas Flash -->
    <div id="liveAlertBox"></div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 14px;">
            <i class="fas fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tarjetas de Estadísticas (KPIs) -->
    <div class="row g-3 mb-4">
        <!-- KPI 1: Total Usuarios Control ECP -->
        <div class="col-12 col-md-4">
            <div class="card stat-card stat-card-total shadow-sm h-100 bg-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">Total Usuarios ECP</span>
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width: 36px; height: 36px; background: #e8f8e8; color: var(--ecp-green);">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0" style="color: var(--ecp-navy);" id="kpiTotalUsers">{{ $stats['total'] }}</h3>
                    <small class="text-success d-flex align-items-center gap-1 mt-1">
                        <i class="fas fa-circle-check" style="font-size: 0.7rem;"></i> Registrados en la base de datos
                    </small>
                </div>
            </div>
        </div>

        {{-- KPI Estado: Activos / Inactivos (HU1) --}}
        <div class="col-12 col-md-4">
            <div class="card stat-card stat-card-active shadow-sm h-100 bg-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">Activos</span>
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width: 36px; height: 36px; background: #e8f8e8; color: #1e7000;">
                            <i class="fas fa-circle-check"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0" style="color: #1e7000;">{{ $stats['activos'] }}</h3>
                    <small class="text-success mt-1 d-block">Pueden iniciar sesión</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card stat-card stat-card-inactive shadow-sm h-100 bg-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">Inactivos</span>
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width: 36px; height: 36px; background: #fdecea; color: #c0392b;">
                            <i class="fas fa-ban"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0" style="color: #c0392b;">{{ $stats['inactivos'] }}</h3>
                    <small class="text-danger mt-1 d-block">Sin acceso al sistema</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de Filtros y Búsqueda -->
    <div class="card usuarios-filter-bar border-0 shadow-sm mb-4" style="border-radius: 24px;">
        <div class="card-body p-3 p-lg-4">
            <div class="row g-3 align-items-center">
                <!-- Filtro por Rol -->
                <div class="col-lg-auto">
                    <div class="d-flex align-items-center gap-2">
                        <label for="filtroRolUsuarios" class="small text-white fw-semibold mb-0 text-nowrap">
                            <i class="fas fa-filter me-1"></i>Rol:
                        </label>
                        <select id="filtroRolUsuarios"
                                class="form-select form-select-sm rounded-pill fw-semibold border-0"
                                style="width: 240px;"
                                onchange="window.location.href = this.value;">
                            <option value="{{ route('controlecp.administrador.usuarios', ['search' => $searchTerm, 'estado' => $estadoFilter]) }}"
                                {{ empty($roleFilter) ? 'selected' : '' }}>
                                Todos
                            </option>
                            <option value="{{ route('controlecp.administrador.usuarios', ['rol' => 'admin', 'search' => $searchTerm, 'estado' => $estadoFilter]) }}"
                                {{ $roleFilter === 'admin' ? 'selected' : '' }}>
                                Administradores
                            </option>
                            <option value="{{ route('controlecp.administrador.usuarios', ['rol' => 'instructor', 'search' => $searchTerm, 'estado' => $estadoFilter]) }}"
                                {{ $roleFilter === 'instructor' ? 'selected' : '' }}>
                                Instructores
                            </option>
                            <option value="{{ route('controlecp.administrador.usuarios', ['rol' => 'apprentice', 'search' => $searchTerm, 'estado' => $estadoFilter]) }}"
                                {{ $roleFilter === 'apprentice' ? 'selected' : '' }}>
                                Aprendices
                            </option>
                        </select>
                    </div>
                </div>

                {{-- Filtro por Estado (HU2) --}}
                <div class="col-lg-auto">
                    <div class="d-flex align-items-center gap-2">
                        <label for="filtroEstadoUsuarios" class="small text-white fw-semibold mb-0 text-nowrap">
                            <i class="fas fa-toggle-on me-1"></i>Estado:
                        </label>
                        <select id="filtroEstadoUsuarios"
                                class="form-select form-select-sm rounded-pill fw-semibold border-0"
                                style="width: 210px;"
                                onchange="window.location.href = this.value;">
                            <option value="{{ route('controlecp.administrador.usuarios', ['rol' => $roleFilter, 'search' => $searchTerm]) }}"
                                {{ empty($estadoFilter) ? 'selected' : '' }}>
                                Todos
                            </option>
                            <option value="{{ route('controlecp.administrador.usuarios', ['rol' => $roleFilter, 'search' => $searchTerm, 'estado' => 'Activo']) }}"
                                {{ $estadoFilter === 'Activo' ? 'selected' : '' }}>
                                Activos
                            </option>
                            <option value="{{ route('controlecp.administrador.usuarios', ['rol' => $roleFilter, 'search' => $searchTerm, 'estado' => 'Inactivo']) }}"
                                {{ $estadoFilter === 'Inactivo' ? 'selected' : '' }}>
                                Inactivos
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Buscador en tiempo real -->
                <div class="col-lg flex-grow-1">
                    <form method="GET" action="{{ route('controlecp.administrador.usuarios') }}" class="d-flex gap-2">
                        @if($roleFilter)
                            <input type="hidden" name="rol" value="{{ $roleFilter }}">
                        @endif
                        @if($estadoFilter)
                            <input type="hidden" name="estado" value="{{ $estadoFilter }}">
                        @endif
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill text-success">
                                <i class="fas fa-magnifying-glass"></i>
                            </span>
                            <input type="text" name="search" id="inputSearchUser" class="form-control border-start-0 ps-0"
                                   placeholder="Buscar por nombre, correo o rol..."
                                   value="{{ $searchTerm }}">
                            @if(!empty($searchTerm))
                                <a href="{{ route('controlecp.administrador.usuarios', ['rol' => $roleFilter, 'estado' => $estadoFilter]) }}" class="input-group-text bg-white text-muted text-decoration-none">
                                    <i class="fas fa-xmark"></i>
                                </a>
                            @endif
                            <button class="btn btn-filter rounded-end-pill px-4 text-white fw-semibold" type="submit">Filtrar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla Principal de Usuarios Locales -->
    <div class="card border-0 shadow-sm" style="border-radius: 18px;">
        <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0" style="color: var(--ecp-navy);">
                <i class="fas fa-id-card-clip me-2" style="color: var(--ecp-green);"></i>Usuarios registrados en Control ECP
            </h5>
        </div>

        <div class="card-body p-0">
            @if($usuarios->count() > 0)
                <div class="table-responsive">
                    <table class="table table-users align-middle mb-0" id="tablaUsuariosEcp">
                        <thead class="table-light" style="font-size: 0.82rem; text-transform: uppercase; color: #52796f;">
                            <tr>
                                <th style="width: 33%;">Persona / Documento</th>
                                <th style="width: 26%;">Usuario & Contacto</th>
                                <th style="width: 20%;">Rol</th>
                                <th style="width: 12%;">Estado</th>
                                <th class="text-end pe-4" style="width: 9%;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 0.88rem;">
                            @foreach($usuarios as $u)
                                @php
                                    $p = $u->person;
                                    $hasAdmin = $u->hasRole('controlecp.admin');
                                    $hasInstructor = $u->hasRole('controlecp.instructor');
                                    $hasApprentice = $u->hasRole('controlecp.apprentice');

                                    $avatarBg = $hasAdmin ? '#e8f8e8' : ($hasInstructor ? '#ebf5fb' : '#fef9e7');
                                    $avatarColor = $hasAdmin ? '#1e7000' : ($hasInstructor ? '#0b5e94' : '#9c6c03');
                                @endphp
                                <tr>
                                    <!-- Datos de Persona -->
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-circle shadow-sm flex-shrink-0" style="background-color: {{ $avatarBg }}; color: {{ $avatarColor }};">
                                                {{ $u->initials }}
                                            </div>
                                            <div>
                                        <div class="fw-bold text-dark mb-0">
                                            {{ $u->full_name }}
                                        </div>
                                        <div class="small text-muted d-flex align-items-center gap-1 mt-1">
                                            <span class="badge bg-light text-dark border">
                                                {{ $p ? $p->document_type : 'Doc' }}
                                            </span>
                                            <span class="fw-semibold text-dark">{{ $p ? $p->document_number : 'Sin documento' }}</span>
                                            @if($p)
                                                <button class="btn btn-link p-0 text-muted" onclick="copiarAlPortapapeles('{{ $p->document_number }}')" title="Copiar documento">
                                                    <i class="far fa-copy" style="font-size: 0.75rem;"></i>
                                                </button>
                                            @endif
                                        </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Usuario & Contacto -->
                                    <td>
                                        <div class="small text-dark fw-semibold">
                                            <i class="fas fa-at me-1 text-muted"></i>{{ $u->nickname }}
                                        </div>
                                        <div class="small text-muted mt-1 text-truncate" style="max-width: 240px;">
                                            <a href="mailto:{{ $u->email }}" class="text-decoration-none text-muted">
                                                <i class="far fa-envelope me-1 text-success"></i>{{ $u->email }}
                                            </a>
                                        </div>
                                        @if($p && !empty($p->telephone1))
                                            <div class="small text-muted mt-1">
                                                <i class="fas fa-phone me-1" style="font-size: 0.75rem;"></i>{{ $p->telephone1 }}
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Roles Control ECP -->
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @if($hasAdmin)
                                                <span class="badge badge-role-admin rounded-pill px-3 py-1">
                                                    <i class="fas fa-user-shield me-1"></i> Administrador
                                                </span>
                                            @endif
                                            @if($hasInstructor)
                                                <span class="badge badge-role-instructor rounded-pill px-3 py-1">
                                                    <i class="fas fa-chalkboard-user me-1"></i> Instructor
                                                </span>
                                            @endif
                                            @if($hasApprentice)
                                                <span class="badge badge-role-apprentice rounded-pill px-3 py-1">
                                                    <i class="fas fa-user-graduate me-1"></i> Aprendiz
                                                </span>
                                            @endif
                                            @if(!$hasAdmin && !$hasInstructor && !$hasApprentice)
                                                <span class="badge bg-light text-muted border rounded-pill px-3 py-1">
                                                    Sin rol ECP
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Estado (HU1) -->
                                    <td>
                                        @if(($u->estado ?? 'Activo') === 'Activo')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1" style="font-size: 0.75rem;">
                                                <i class="fas fa-circle-check me-1"></i> Activo
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1" style="font-size: 0.75rem;">
                                                <i class="fas fa-ban me-1"></i> Inactivo
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Acciones -->
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button class="btn-action-icon btn-action-view" type="button"
                                                    title="Ver ficha"
                                                    onclick="verCredencialUsuario({{ json_encode([
                                                        'id' => $u->id,
                                                        'nickname' => $u->nickname,
                                                        'email' => $u->email,
                                                        'full_name' => $u->full_name,
                                                        'initials' => $u->initials,
                                                        'document_type' => $p ? $p->document_type : 'Cédula de ciudadanía',
                                                        'document_number' => $p ? $p->document_number : '',
                                                        'telephone' => $p ? $p->telephone1 : '',
                                                        'misena_email' => $p ? $p->misena_email : '',
                                                        'role' => $hasAdmin ? 'Administrador' : ($hasInstructor ? 'Instructor' : ($hasApprentice ? 'Aprendiz' : 'Usuario')),
                                                        'updated_at' => $u->updated_at ? $u->updated_at->format('d/m/Y H:i') : 'Activo'
                                                    ]) }})">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn-action-icon btn-action-edit" type="button"
                                                    title="Editar usuario"
                                                    onclick="abrirEditarUsuario('{{ route('controlecp.administrador.usuarios.edit', $u->id, false) }}')">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                                @if(($u->estado ?? 'Activo') === 'Activo')
                                                    <button class="btn-action-icon btn-action-disable" type="button"
                                                            title="Desactivar usuario"
                                                            onclick="confirmarCambioEstado({{ $u->id }}, 'desactivar', '{{ addslashes($u->full_name) }}')">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                @else
                                                    <button class="btn-action-icon btn-action-enable" type="button"
                                                            title="Activar usuario"
                                                            onclick="confirmarCambioEstado({{ $u->id }}, 'activar', '{{ addslashes($u->full_name) }}')">
                                                        <i class="fas fa-circle-check"></i>
                                                    </button>
                                                @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="p-3 border-top d-flex justify-content-end align-items-center">
                    <div>
                        {{ $usuarios->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @else
                <!-- Estado Vacío -->
                <div class="text-center py-5 px-3">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3"
                         style="width: 80px; height: 80px; background: #f0fbf4; color: var(--ecp-green); font-size: 2.2rem;">
                        <i class="fas fa-users-slash"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">No se encontraron usuarios registrados con los filtros aplicados</h5>
                    <p class="text-muted small mb-4" style="max-width: 480px; margin: 0 auto;">
                        El módulo está listo para recibir y registrar toda la nómina institucional al conectarse a la API REST Central de SICEFA.
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn text-white rounded-pill px-4 shadow-sm" style="background-color: var(--ecp-green);" data-bs-toggle="modal" data-bs-target="#modalSyncConfirm">
                            <i class="fas fa-cloud-arrow-down me-1"></i> Sincronizar Usuarios Ahora
                        </button>
                        <a href="{{ route('controlecp.administrador.usuarios') }}" class="btn btn-clear-filters rounded-pill px-4">
                            Limpiar Filtros
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>

<!-- ========================================================================================= -->
<!-- MODALES DEL SISTEMA DE INTEGRACIÓN Y SINCRONIZACIÓN API                                   -->
<!-- ========================================================================================= -->

<!-- Modal: Confirmación Activar / Desactivar Usuario (HU7 / HU8) -->
<div class="modal fade" id="modalCambioEstado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4 text-center justify-content-center position-relative">
                <button type="button" class="btn-close position-absolute end-0 top-0 mt-3 me-3" data-bs-dismiss="modal"></button>
                <div id="estadoModalIcon" class="rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                     style="width: 65px; height: 65px; font-size: 1.8rem;">
                    <i class="fas fa-ban"></i>
                </div>
            </div>
            <div class="modal-body p-4 text-center">
                <h5 class="fw-bold mb-2" id="estadoModalTitle" style="color: var(--ecp-navy);">
                    Desactivar Usuario
                </h5>
                <p class="text-muted small mb-4" id="estadoModalDesc">
                    El usuario no podrá iniciar sesión, pero sus datos e historial se conservan.
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn rounded-pill px-4 text-white" id="btnConfirmarEstado"
                            onclick="ejecutarCambioEstado()">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 1. Modal: Configuración de la API REST Central -->
<div class="modal fade" id="modalConfigApi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width: 42px; height: 42px; background: #e8f8e8; color: var(--ecp-green);">
                        <i class="fas fa-sliders"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold" style="color: var(--ecp-navy);">
                            Configuración de Conexión API REST Central
                        </h5>
                        <p class="text-muted small mb-0">ERP SENA Empresa / SICEFA La Angostura</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formApiConfig" onsubmit="guardarConfigApi(event)">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">URL del Endpoint REST Central:</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="fas fa-link"></i></span>
                            <input type="url" class="form-control" id="cfgApiUrl" required
                                   value="{{ $apiConfig['url'] ?? route('controlecp.api.usuarios.mock') }}"
                                   placeholder="https://sicefa.senaempresa.edu.co/api/v1/usuarios">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="restablecerUrlMock()" title="Restablecer al endpoint integrado">
                                <i class="fas fa-rotate-left me-1"></i> Local Mock
                            </button>
                        </div>
                        <div class="form-text">
                            Endpoint que entrega la lista de aprendices, instructores y directivos en formato JSON estandarizado.
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small text-dark">Bearer Token / API Key (Opcional):</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="fas fa-key"></i></span>
                                <input type="password" class="form-control" id="cfgApiToken"
                                       value="{{ $apiConfig['token'] ?? '' }}"
                                       placeholder="Token de autorización Bearer si es requerido...">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('cfgApiToken')">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-dark">Timeout de Respuesta:</label>
                            <select class="form-select" id="cfgApiTimeout">
                                <option value="5" {{ ($apiConfig['timeout'] ?? 5) == 5 ? 'selected' : '' }}>5 segundos</option>
                                <option value="10" {{ ($apiConfig['timeout'] ?? 5) == 10 ? 'selected' : '' }}>10 segundos</option>
                                <option value="15" {{ ($apiConfig['timeout'] ?? 5) == 15 ? 'selected' : '' }}>15 segundos</option>
                            </select>
                        </div>
                    </div>

                    <!-- Panel de Prueba en Vivo de la Conexión -->
                    <div class="p-3 rounded-3 mb-3" style="background-color: #f8faf9; border: 1px solid #e1ece5;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold small text-dark"><i class="fas fa-terminal me-1 text-success"></i>Prueba de Conexión en Tiempo Real:</span>
                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="probarConexionModal()">
                                <i class="fas fa-play me-1"></i> Probar Conexión Ahora
                            </button>
                        </div>
                        <div id="modalTestResultBox" class="small p-2 rounded bg-white border text-muted" style="min-height: 48px; font-family: monospace;">
                            Presione "Probar Conexión Ahora" para validar el estado del enlace HTTP y la respuesta del servidor.
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn text-white rounded-pill px-4" style="background-color: var(--ecp-green);">
                            <i class="fas fa-floppy-disk me-1"></i> Guardar Configuración
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 2. Modal: Confirmación y Sincronización Masiva -->
<div class="modal fade" id="modalSyncConfirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4 text-center justify-content-center position-relative">
                <button type="button" class="btn-close position-absolute end-0 top-0 mt-3 me-3" data-bs-dismiss="modal"></button>
                <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                     style="width: 65px; height: 65px; background: #e8f8e8; color: var(--ecp-green); font-size: 1.8rem;">
                    <i class="fas fa-cloud-arrow-down" id="modalSyncIcon"></i>
                </div>
            </div>
            <div class="modal-body p-4 text-center">
                <h5 class="fw-bold mb-2" style="color: var(--ecp-navy);">
                    Sincronizar y Registrar Usuarios desde API Central
                </h5>
                <p class="text-muted small mb-3">
                    Se conectará a la <strong>API REST Central del ERP SENA Empresa / SICEFA</strong> para descargar la lista de usuarios y registrarlos de forma segura en Control ECP.
                </p>

                <!-- Beneficios de la sincronización -->
                <div class="text-start bg-light p-3 rounded-3 mb-4" style="font-size: 0.84rem;">
                    <div class="d-flex align-items-center gap-2 mb-2 text-dark">
                        <i class="fas fa-circle-check text-success"></i>
                        <span>Crea automáticamente las personas y usuarios inexistentes.</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-2 text-dark">
                        <i class="fas fa-circle-check text-success"></i>
                        <span>Actualiza correos, teléfonos y datos personales.</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-2 text-dark">
                        <i class="fas fa-circle-check text-success"></i>
                        <span>Homologa y vincula los roles (Admin, Instructor, Aprendiz).</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 text-dark">
                        <i class="fas fa-circle-check text-success"></i>
                        <span>Garantiza contraseñas institucionales seguras y sin duplicados.</span>
                    </div>
                </div>

                <!-- Barra de Progreso (Oculta al inicio) -->
                <div id="syncProgressContainer" class="d-none text-start mb-3">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span id="syncProgressLabel">Conectando con la API...</span>
                        <span id="syncProgressPercent">0%</span>
                    </div>
                    <div class="progress" style="height: 10px; border-radius: 8px;">
                        <div id="syncProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 0%;"></div>
                    </div>
                </div>

                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal" id="btnCancelSync">
                        Cancelar
                    </button>
                    <button type="button" class="btn text-white rounded-pill px-4 shadow-sm" style="background-color: var(--ecp-green);" onclick="ejecutarSincronizacion()" id="btnStartSync">
                        <i class="fas fa-arrows-rotate me-1"></i> Iniciar Registro
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 3. Modal: Ver Credencial / Ficha SICEFA -->
<div class="modal fade" id="modalCredencialUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header border-0 pb-0 pt-3 px-4 d-flex justify-content-between align-items-center">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill">
                    <i class="fas fa-shield-halved me-1"></i> Credencial Digital SICEFA
                </span>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="credential-card p-4 shadow-sm text-center">
                    <!-- Avatar -->
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center shadow-sm mb-3"
                         id="credAvatar"
                         style="width: 72px; height: 72px; background: #e8f8e8; color: var(--ecp-green); font-size: 1.6rem; font-weight: 700; border: 3px solid #ffffff;">
                        SE
                    </div>

                    <!-- Nombre y Documento -->
                    <h5 class="fw-bold mb-1 text-dark" id="credFullName">Nombre de Usuario</h5>
                    <div class="small text-muted mb-3" id="credDoc">Cédula: 00000000</div>

                    <!-- Badge Rol -->
                    <div class="mb-4">
                        <span class="badge rounded-pill px-4 py-2 fs-6 shadow-sm" id="credRoleBadge" style="background-color: var(--ecp-green); color: white;">
                            Rol ECP
                        </span>
                    </div>

                    <!-- Datos de Contacto -->
                    <div class="text-start bg-white p-3 rounded-3 shadow-xs mb-3 border">
                        <div class="row g-2 small">
                            <div class="col-4 text-muted">Usuario:</div>
                            <div class="col-8 fw-semibold text-dark" id="credNickname">--</div>

                            <div class="col-4 text-muted">Correo:</div>
                            <div class="col-8 fw-semibold text-dark text-break" id="credEmail">--</div>

                            <div class="col-4 text-muted">Teléfono:</div>
                            <div class="col-8 fw-semibold text-dark" id="credPhone">--</div>

                            <div class="col-4 text-muted">Centro:</div>
                            <div class="col-8 fw-semibold text-dark">La Angostura (Huila)</div>

                            <div class="col-4 text-muted">Sincronizado:</div>
                            <div class="col-8 fw-semibold text-success" id="credUpdated">--</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">
                            Cerrar
                        </button>
                        <button type="button" class="btn btn-sm text-white rounded-pill px-3" style="background-color: var(--ecp-navy);" onclick="imprimirCredencial()">
                            <i class="fas fa-print me-1"></i> Imprimir Ficha
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 4. Modal: Buscar y Vincular Persona de la BD Central SICEFA -->
<div class="modal fade" id="modalBuscarCentral" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width: 42px; height: 42px; background: #ebf5fb; color: #2980b9;">
                        <i class="fas fa-magnifying-glass"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold" style="color: var(--ecp-navy);">
                            Consultar Directorio Central SICEFA
                        </h5>
                        <p class="text-muted small mb-0">Búsqueda directa entre los registros del centro para vincular a Control ECP</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="input-group mb-3">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" class="form-control" id="inputSearchCentralTerm"
                           placeholder="Ingrese número de documento o nombre para buscar..."
                           onkeyup="if(event.key === 'Enter') buscarEnCentral()">
                    <button class="btn text-white px-4" style="background-color: var(--ecp-green);" type="button" onclick="buscarEnCentral()">
                        Consultar
                    </button>
                </div>

                <!-- Resultados de la Búsqueda -->
                <div id="centralSearchResults" style="max-height: 380px; overflow-y: auto;">
                    <div class="text-center py-4 text-muted small">
                        <i class="fas fa-database fa-2x mb-2 text-muted"></i>
                        <p class="mb-0">Ingrese un documento o nombre para consultar la base de datos central de SICEFA.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Token CSRF de Laravel
    const usuarioCsrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // ── 1. Probar Conexión con la API REST Central ──────────────────────────────────
    async function probarConexionApi() {
        const syncIcon = document.getElementById('btnSyncSpinIcon');
        const badge = document.getElementById('apiConnectionBadge');
        const latencyText = document.getElementById('apiLatencyText');

        if (syncIcon) syncIcon.classList.add('fa-spin');
        mostrarAlerta('Probando enlace con la API REST Central...', 'info');

        try {
            const resp = await fetch("{{ route('controlecp.administrador.usuarios.test_api') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': usuarioCsrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            });

            const data = await resp.json();

            if (syncIcon) syncIcon.classList.remove('fa-spin');

            if (data.success) {
                if (badge) {
                    badge.className = 'badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill';
                    badge.innerHTML = '<i class="fas fa-circle-check me-1"></i> En Línea (' + data.count + ' usuarios)';
                }
                if (latencyText) latencyText.innerText = 'Latencia: ' + data.latency_ms + ' ms';
                mostrarAlerta('¡Conexión establecida con éxito! ' + data.count + ' usuarios disponibles en la API Central.', 'success');
            } else {
                if (badge) {
                    badge.className = 'badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1 rounded-pill';
                    badge.innerHTML = '<i class="fas fa-triangle-exclamation me-1"></i> Modo Fallback Activo';
                }
                if (latencyText) latencyText.innerText = 'Latencia: ' + (data.latency_ms || '--') + ' ms';
                mostrarAlerta(data.message, 'warning');
            }
        } catch (e) {
            if (syncIcon) syncIcon.classList.remove('fa-spin');
            mostrarAlerta('Error de red al comprobar el servicio: ' + e.message, 'danger');
        }
    }

    // ── 2. Prueba desde el Modal de Configuración ────────────────────────────────────
    async function probarConexionModal() {
        const url = document.getElementById('cfgApiUrl').value.trim();
        const token = document.getElementById('cfgApiToken').value.trim();
        const timeout = document.getElementById('cfgApiTimeout').value;
        const resultBox = document.getElementById('modalTestResultBox');

        resultBox.innerHTML = '<span class="text-primary"><i class="fas fa-spinner fa-spin me-1"></i> Enviando petición GET a ' + url + '...</span>';

        try {
            const resp = await fetch("{{ route('controlecp.administrador.usuarios.test_api') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': usuarioCsrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ url, token, timeout })
            });

            const data = await resp.json();

            if (data.success) {
                resultBox.innerHTML = `
                    <div class="text-success fw-bold"><i class="fas fa-check-circle me-1"></i> Conexión Exitosa (HTTP ${data.http_code})</div>
                    <div>Latencia: <strong>${data.latency_ms} ms</strong> | Registros encontrados: <strong>${data.count}</strong></div>
                    <div class="text-muted mt-1" style="font-size: 0.75rem;">Muestra: ${JSON.stringify(data.sample)}</div>
                `;
            } else {
                resultBox.innerHTML = `
                    <div class="text-danger fw-bold"><i class="fas fa-triangle-exclamation me-1"></i> ${data.message}</div>
                    <div class="text-muted mt-1">Sugerencia: Si el servidor remoto está fuera de línea, puede utilizar el endpoint integrado para pruebas.</div>
                `;
            }
        } catch (e) {
            resultBox.innerHTML = '<div class="text-danger"><i class="fas fa-circle-xmark me-1"></i> Error: ' + e.message + '</div>';
        }
    }

    // ── 3. Guardar Configuración de la API ──────────────────────────────────────────
    async function guardarConfigApi(e) {
        e.preventDefault();
        const url = document.getElementById('cfgApiUrl').value.trim();
        const token = document.getElementById('cfgApiToken').value.trim();
        const timeout = document.getElementById('cfgApiTimeout').value;

        try {
            const resp = await fetch("{{ route('controlecp.administrador.usuarios.save_config') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': usuarioCsrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ url, token, timeout })
            });

            const data = await resp.json();
            if (data.success) {
                document.getElementById('lblEndpointUrl').innerText = data.config.url;
                bootstrap.Modal.getInstance(document.getElementById('modalConfigApi')).hide();
                mostrarAlerta('Configuración de API guardada correctamente.', 'success');
            }
        } catch (e) {
            mostrarAlerta('Error al guardar configuración: ' + e.message, 'danger');
        }
    }

    function restablecerUrlMock() {
        document.getElementById('cfgApiUrl').value = "{{ route('controlecp.api.usuarios.mock') }}";
    }

    // ── 4. Ejecución del Proceso de Sincronización y Registro ─────────────────────────
    async function ejecutarSincronizacion() {
        const progressContainer = document.getElementById('syncProgressContainer');
        const progressBar = document.getElementById('syncProgressBar');
        const progressLabel = document.getElementById('syncProgressLabel');
        const progressPercent = document.getElementById('syncProgressPercent');
        const btnStart = document.getElementById('btnStartSync');
        const btnCancel = document.getElementById('btnCancelSync');
        const icon = document.getElementById('modalSyncIcon');

        progressContainer.classList.remove('d-none');
        btnStart.disabled = true;
        btnCancel.disabled = true;
        if (icon) icon.className = 'fas fa-arrows-rotate fa-spin text-success';

        // Animación de pasos
        let progress = 10;
        progressBar.style.width = progress + '%';
        progressPercent.innerText = progress + '%';
        progressLabel.innerText = 'Consultando endpoint de API Central SICEFA...';

        const timer = setInterval(() => {
            if (progress < 85) {
                progress += 15;
                progressBar.style.width = progress + '%';
                progressPercent.innerText = progress + '%';
                if (progress === 40) progressLabel.innerText = 'Validando nómina y documentos de aprendices e instructores...';
                if (progress === 70) progressLabel.innerText = 'Registrando usuarios y asignando credenciales en Control ECP...';
            }
        }, 300);

        try {
            const resp = await fetch("{{ route('controlecp.administrador.usuarios.sync') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': usuarioCsrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            });

            clearInterval(timer);
            const data = await resp.json();

            progressBar.style.width = '100%';
            progressPercent.innerText = '100%';
            progressLabel.innerText = '¡Completado!';

            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('modalSyncConfirm')).hide();
                if (data.success) {
                    mostrarAlerta(data.message, 'success');
                    // Actualizar KPIs si vienen
                    if (data.stats) {
                        const lbl = document.getElementById('lblLastSyncText');
                        if (lbl) {
                            lbl.innerHTML = `<span class="text-dark fw-semibold">${data.stats.date}</span> &bull; <span class="badge bg-light text-success border border-success-subtle">${data.stats.total_synced} usuarios sincronizados</span>`;
                        }
                    }
                    // Recargar suavemente la página tras 1.2 segundos para reflejar la tabla actualizada
                    setTimeout(() => { window.location.reload(); }, 1200);
                } else {
                    mostrarAlerta(data.message, 'danger');
                    btnStart.disabled = false;
                    btnCancel.disabled = false;
                }
            }, 600);

        } catch (e) {
            clearInterval(timer);
            progressBar.classList.remove('bg-success');
            progressBar.classList.add('bg-danger');
            progressLabel.innerText = 'Error durante la sincronización: ' + e.message;
            btnStart.disabled = false;
            btnCancel.disabled = false;
        }
    }

    // ── 5. Buscar en Directorio Central SICEFA ───────────────────────────────────────
    async function buscarEnCentral() {
        const term = document.getElementById('inputSearchCentralTerm').value.trim();
        const container = document.getElementById('centralSearchResults');

        if (!term) {
            container.innerHTML = '<div class="alert alert-warning small mb-0">Debe ingresar un término para buscar.</div>';
            return;
        }

        container.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-success mb-2"></i><div class="small text-muted">Consultando en más de 150.000 registros de SICEFA...</div></div>';

        try {
            const resp = await fetch("{{ route('controlecp.administrador.usuarios.search_central') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': usuarioCsrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ term })
            });

            const data = await resp.json();

            if (data.success && data.results.length > 0) {
                let html = '<div class="list-group list-group-flush">';
                data.results.forEach(p => {
                    const hasEcp = Object.keys(p.assigned_roles).length > 0;
                    const rolesText = hasEcp ? Object.values(p.assigned_roles).join(', ') : 'Sin rol en Control ECP';

                    html += `
                        <div class="list-group-item px-3 py-3 rounded-3 mb-2 border shadow-xs bg-white">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div>
                                    <div class="fw-bold text-dark">${p.full_name}</div>
                                    <div class="small text-muted">
                                        <span class="badge bg-light text-dark border me-1">${p.document_type}</span>
                                        <strong>${p.document_number}</strong> &bull; Correo: ${p.email}
                                    </div>
                                    <div class="small mt-1 text-muted">
                                        <i class="fas fa-graduation-cap me-1 text-success"></i> ${p.ficha}
                                    </div>
                                    <div class="small mt-1">
                                        <span class="badge ${hasEcp ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'} rounded-pill">
                                            ${rolesText}
                                        </span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <button class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="vincularPersonaDirecto(${p.person_id}, 'controlecp.apprentice')">
                                        + Aprendiz
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="vincularPersonaDirecto(${p.person_id}, 'controlecp.instructor')">
                                        + Instructor
                                    </button>
                                    <button class="btn btn-sm btn-outline-dark rounded-pill px-3" onclick="vincularPersonaDirecto(${p.person_id}, 'controlecp.admin')">
                                        + Admin
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="alert alert-info small mb-0 text-center">No se encontraron personas con el criterio especificado.</div>';
            }
        } catch (e) {
            container.innerHTML = '<div class="alert alert-danger small mb-0">Error en la consulta: ' + e.message + '</div>';
        }
    }

    // ── 6. Vincular persona directa con rol ──────────────────────────────────────────
    async function vincularPersonaDirecto(personId, roleSlug) {
        try {
            const resp = await fetch("{{ route('controlecp.administrador.usuarios.link_user') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': usuarioCsrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ person_id: personId, role_slug: roleSlug })
            });

            const data = await resp.json();
            if (data.success) {
                mostrarAlerta(data.message, 'success');
                setTimeout(() => { window.location.reload(); }, 1000);
            } else {
                mostrarAlerta(data.message, 'danger');
            }
        } catch (e) {
            mostrarAlerta('Error: ' + e.message, 'danger');
        }
    }

    // ── 7A. Confirmar cambio de estado (HU7 / HU8) ─────────────────────────────────
    let _estadoUserId   = null;
    let _estadoAccion   = null;

    function confirmarCambioEstado(userId, accion, fullName) {
        _estadoUserId = userId;
        _estadoAccion = accion;

        const icon  = document.getElementById('estadoModalIcon');
        const title = document.getElementById('estadoModalTitle');
        const desc  = document.getElementById('estadoModalDesc');
        const btn   = document.getElementById('btnConfirmarEstado');

        if (accion === 'desactivar') {
            icon.style.background = '#fdecea';
            icon.style.color      = '#c0392b';
            icon.innerHTML        = '<i class="fas fa-ban"></i>';
            title.innerText       = 'Desactivar usuario';
            desc.innerText        = `¿Desactivar a "${fullName}"? El usuario no podrá iniciar sesión, pero sus datos e historial se conservan.`;
            btn.style.background  = '#dc3545';
            btn.style.borderColor = '#dc3545';
        } else {
            icon.style.background = '#e8f8e8';
            icon.style.color      = '#1e7000';
            icon.innerHTML        = '<i class="fas fa-circle-check"></i>';
            title.innerText       = 'Activar usuario';
            desc.innerText        = `¿Reactivar a "${fullName}"? El usuario podrá volver a iniciar sesión.`;
            btn.style.background  = '#39A900';
            btn.style.borderColor = '#39A900';
        }

        new bootstrap.Modal(document.getElementById('modalCambioEstado')).show();
    }

    async function ejecutarCambioEstado() {
        const accion = _estadoAccion === 'desactivar' ? 'desactivar' : 'activar';
        const url    = "{{ url('/control-ecp/administrador/usuarios') }}/" + _estadoUserId + '/' + accion;
        const modalElement = document.getElementById('modalCambioEstado');
        const confirmButton = document.getElementById('btnConfirmarEstado');

        if (!_estadoUserId || !_estadoAccion || confirmButton.disabled) {
            return;
        }

        confirmButton.disabled = true;
        confirmButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Procesando...';

        try {
            const resp = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': usuarioCsrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({})
            });
            const data = await resp.json().catch(() => ({}));

            if (!resp.ok) {
                throw new Error(data.message || 'No se pudo actualizar el estado del usuario.');
            }

            if (data.success) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                modalElement.addEventListener('hidden.bs.modal', function mostrarResultado() {
                    modalElement.removeEventListener('hidden.bs.modal', mostrarResultado);
                    mostrarAlerta(data.message, 'success');
                });
                modal.hide();
                setTimeout(() => { window.location.reload(); }, 1500);
            } else {
                mostrarAlerta(data.message, 'warning');
            }
        } catch (e) {
            mostrarAlerta('Error al cambiar el estado: ' + e.message, 'danger');
        } finally {
            confirmButton.disabled = false;
            confirmButton.innerHTML = 'Confirmar';
        }
    }

    // ── 7B. Cambiar Rol de Usuario ───────────────────────────────────────────────────
    async function cambiarRolUsuario(userId, roleSlug) {
        const url = "{{ url('/control-ecp/administrador/usuarios') }}/" + userId + "/change-role";
        try {
            const resp = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': usuarioCsrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ role_slug: roleSlug })
            });

            const data = await resp.json();
            if (data.success) {
                mostrarAlerta(data.message, 'success');
                setTimeout(() => { window.location.reload(); }, 800);
            } else {
                mostrarAlerta(data.message, 'danger');
            }
        } catch (e) {
            mostrarAlerta('Error al cambiar rol: ' + e.message, 'danger');
        }
    }

    // ── 8. Ver Credencial / Ficha de Usuario ────────────────────────────────────────
    function verCredencialUsuario(data) {
        document.getElementById('credFullName').innerText = data.full_name;
        document.getElementById('credDoc').innerText = data.document_type + ': ' + data.document_number;
        document.getElementById('credAvatar').innerText = data.initials;
        document.getElementById('credNickname').innerText = data.nickname;
        document.getElementById('credEmail').innerText = data.email;
        document.getElementById('credPhone').innerText = data.telephone || 'No registra';
        document.getElementById('credUpdated').innerText = data.updated_at;

        const roleBadge = document.getElementById('credRoleBadge');
        roleBadge.innerText = data.role;
        if (data.role === 'Administrador') {
            roleBadge.style.backgroundColor = '#1e7000';
        } else if (data.role === 'Instructor') {
            roleBadge.style.backgroundColor = '#0b5e94';
        } else {
            roleBadge.style.backgroundColor = '#9c6c03';
        }

        const modal = new bootstrap.Modal(document.getElementById('modalCredencialUsuario'));
        modal.show();
    }

    function imprimirCredencial() {
        window.print();
    }

    // ── 9. Utilidades (Copiar, Alertas) ────────────────────────────────────────────
    function copiarAlPortapapeles(texto) {
        navigator.clipboard.writeText(texto).then(() => {
            mostrarAlerta('Documento copiado al portapapeles: ' + texto, 'success');
        });
    }

    function togglePasswordVisibility(id) {
        const input = document.getElementById(id);
        if (input.type === 'password') {
            input.type = 'text';
        } else {
            input.type = 'password';
        }
    }

    function mostrarAlerta(mensaje, tipo = 'success') {
        const container = document.getElementById('liveAlertBox');
        if (!container) return;

        const icon = tipo === 'success' ? 'fa-circle-check' : (tipo === 'info' ? 'fa-circle-info' : 'fa-triangle-exclamation');
        container.innerHTML = `
            <div class="alert alert-${tipo} alert-dismissible fade show border-0 shadow-sm mb-3" role="alert" style="border-radius: 14px;">
                <i class="fas ${icon} me-2"></i> ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        setTimeout(() => {
            const alert = container.querySelector('.alert');
            if (alert) {
                alert.classList.remove('show');
                setTimeout(() => { container.innerHTML = ''; }, 300);
            }
        }, 5000);
    }

</script>
@endpush
<div class="modal fade" id="nuevoUsuarioModal" tabindex="-1" aria-labelledby="nuevoUsuarioModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--ecp-navy);">
                <h5 class="modal-title" id="nuevoUsuarioModalLabel">
                    <i class="fas fa-user-plus me-2" style="color: var(--ecp-green);"></i>Registrar Usuario
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0">
                <iframe
                    id="nuevoUsuarioFrame"
                    src="{{ route('controlecp.administrador.usuarios.create', ['modal' => 1]) }}"
                    title="Formulario para registrar usuario"
                    style="display: block; width: 100%; height: 75vh; border: 0;">
                </iframe>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editarUsuarioModal" tabindex="-1" aria-labelledby="editarUsuarioModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--ecp-navy);">
                <h5 class="modal-title" id="editarUsuarioModalLabel">
                    <i class="fas fa-user-pen me-2" style="color: var(--ecp-green);"></i>Editar Usuario
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0">
                <iframe
                    id="editarUsuarioFrame"
                    title="Formulario para editar usuario"
                    style="display: block; width: 100%; height: 75vh; border: 0;">
                </iframe>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        window.addEventListener('message', function (event) {
            if (event.origin !== window.location.origin || !event.data || event.data.type !== 'user-saved') {
                return;
            }

            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('nuevoUsuarioModal'));
            const editModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('editarUsuarioModal'));
            const openModal = document.querySelector('.modal.show');
            const modalElement = openModal || document.getElementById('nuevoUsuarioModal');
            const modalInstance = openModal === document.getElementById('editarUsuarioModal') ? editModal : modal;

            modalElement.addEventListener('hidden.bs.modal', function mostrarResultado() {
                modalElement.removeEventListener('hidden.bs.modal', mostrarResultado);
                mostrarAlerta(event.data.message, 'success');
            });
            modalInstance.hide();
            setTimeout(() => { window.location.reload(); }, 1800);
        });

        const listPath = @json(parse_url(route('controlecp.administrador.usuarios'), PHP_URL_PATH));
        const frames = [
            { modal: document.getElementById('nuevoUsuarioModal'), frame: document.getElementById('nuevoUsuarioFrame') },
            { modal: document.getElementById('editarUsuarioModal'), frame: document.getElementById('editarUsuarioFrame') }
        ];

        frames.forEach(function (item) {
            item.frame.addEventListener('load', function () {
                try {
                    if (item.frame.contentWindow.location.pathname === listPath) {
                        bootstrap.Modal.getOrCreateInstance(item.modal).hide();
                        window.location.reload();
                    }
                } catch (error) {
                    console.error('No fue posible verificar el resultado del formulario:', error);
                }
            });
        });
    });

    function abrirEditarUsuario(url) {
        const frame = document.getElementById('editarUsuarioFrame');
        frame.src = url + (url.includes('?') ? '&' : '?') + 'modal=1';
        bootstrap.Modal.getOrCreateInstance(document.getElementById('editarUsuarioModal')).show();
    }
</script>
@endpush
