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
        border: 1px solid rgba(0, 0, 0, 0.04);
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
                        Usuarios
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: var(--ecp-navy); font-family: 'Outfit', sans-serif;">
                <i class="fas fa-users-gear me-2" style="color: var(--ecp-green);"></i>Gestión de Usuarios
            </h2>
            <p class="text-muted small mb-0">Control ECP &bull; Sincronización e integración con la API REST Central del ERP SENA Empresa / SICEFA</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('controlecp.administrador.usuarios.create') }}"
               class="btn text-white rounded-pill px-3 btn-sm shadow-sm"
               style="background-color: var(--ecp-green); border-color: var(--ecp-green);">
                <i class="fas fa-user-plus me-1"></i> Nuevo Usuario
            </a>
            <button type="button" class="btn btn-outline-secondary rounded-pill px-3 btn-sm" data-bs-toggle="modal" data-bs-target="#modalConfigApi">
                <i class="fas fa-sliders me-1"></i> Configurar API
            </button>
            <button type="button" class="btn btn-outline-primary rounded-pill px-3 btn-sm" data-bs-toggle="modal" data-bs-target="#modalBuscarCentral">
                <i class="fas fa-magnifying-glass me-1"></i> Buscar en SICEFA
            </button>
            <button type="button" class="btn btn-outline-success rounded-pill px-3 btn-sm" onclick="probarConexionApi()">
                <i class="fas fa-arrows-rotate me-1" id="btnSyncSpinIcon"></i> Comprobar Conexión
            </button>
            <button type="button" class="btn text-white rounded-pill px-3 btn-sm shadow-sm" style="background-color: var(--ecp-navy); border-color: var(--ecp-navy);" data-bs-toggle="modal" data-bs-target="#modalSyncConfirm">
                <i class="fas fa-cloud-arrow-down me-1"></i> Sincronizar y Registrar Usuarios
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

    <!-- Tarjeta Principal: Estado del Servicio API SICEFA -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 18px; background: linear-gradient(135deg, #f0fbf4 0%, #d8f3dc 100%); border-left: 6px solid var(--ecp-green) !important;">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm"
                         style="width: 58px; height: 58px; background: #FFFFFF; color: var(--ecp-green); font-size: 1.6rem;">
                        <i class="fas fa-network-wired"></i>
                    </div>
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <h4 class="h5 fw-bold mb-0" style="color: var(--ecp-navy);">
                                Integración Directa con la API REST Central SICEFA
                            </h4>
                            <span class="badge rounded-pill d-inline-flex align-items-center gap-1" style="background-color: var(--ecp-green); font-size: 0.75rem;">
                                <span class="pulse-indicator bg-white" style="width: 6px; height: 6px;"></span>
                                Conexión Activa
                            </span>
                        </div>
                        <p class="mb-0 text-dark" style="font-size: 0.9rem; line-height: 1.45;">
                            Este módulo está preparado para consumir y registrar en tiempo real a los <strong>aprendices, instructores y directivos</strong> directamente desde el ERP SENA Empresa / SICEFA, vinculando automáticamente sus roles y credenciales para Control ECP.
                        </p>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
                    <button type="button" class="btn btn-sm text-white rounded-pill px-3 shadow-sm" style="background-color: var(--ecp-navy);" data-bs-toggle="modal" data-bs-target="#modalConfigApi">
                        <i class="fas fa-gear me-1"></i> Ver Endpoint
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success bg-white rounded-pill px-3 shadow-sm" onclick="probarConexionApi()">
                        <i class="fas fa-plug me-1"></i> Test Ping API
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas de Estadísticas (KPIs) -->
    <div class="row g-3 mb-4">
        <!-- KPI 1: Total Usuarios Control ECP -->
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm h-100 bg-white">
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
                        <i class="fas fa-circle-check" style="font-size: 0.7rem;"></i> Sincronizados
                    </small>
                </div>
            </div>
        </div>

        <!-- KPI 2: Administradores -->
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm h-100 bg-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">Administradores</span>
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width: 36px; height: 36px; background: #f0fbf4; color: #1e7000;">
                            <i class="fas fa-user-shield"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark" id="kpiAdminUsers">{{ $stats['admins'] }}</h3>
                    <small class="text-muted mt-1 d-block">Rol ECP Admin</small>
                </div>
            </div>
        </div>

        <!-- KPI 3: Instructores -->
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm h-100 bg-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">Instructores</span>
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width: 36px; height: 36px; background: #ebf5fb; color: #2980b9;">
                            <i class="fas fa-chalkboard-user"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark" id="kpiInstructorUsers">{{ $stats['instructors'] }}</h3>
                    <small class="text-muted mt-1 d-block">Formadores & Mediadores</small>
                </div>
            </div>
        </div>

        <!-- KPI 4: Aprendices -->
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm h-100 bg-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">Aprendices</span>
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width: 36px; height: 36px; background: #fef9e7; color: #d68910;">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark" id="kpiApprenticeUsers">{{ $stats['apprentices'] }}</h3>
                    <small class="text-muted mt-1 d-block">Fichas de Formación</small>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI Estado: Activos / Inactivos (HU1) --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm h-100 bg-white">
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
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm h-100 bg-white">
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

    <!-- Panel de Detalles Técnicos del Endpoint y Última Sincronización -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-header bg-white border-0 pt-3 pb-2 px-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="fw-bold mb-0" style="color: var(--ecp-navy); font-size: 1.05rem;">
                    <i class="fas fa-server me-2 text-success"></i>Parámetros de Integración API REST
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <span id="apiConnectionBadge" class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill">
                        <i class="fas fa-circle-check me-1"></i> Servicio Listo
                    </span>
                    <span class="small text-muted" id="apiLatencyText">Latencia: -- ms</span>
                </div>
            </div>
        </div>
        <div class="card-body px-4 pb-3 pt-1">
            <div class="row g-3">
                <div class="col-md-7">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0" style="font-size: 0.85rem;">
                            <tbody>
                                <tr>
                                    <th class="bg-light text-muted" style="width: 35%;">Endpoint API Central:</th>
                                    <td>
                                        <code class="text-primary fw-bold text-break" id="lblEndpointUrl">{{ $apiConfig['url'] ?? route('controlecp.api.usuarios.mock') }}</code>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light text-muted">Protocolo y Formato:</th>
                                    <td>RESTful JSON &bull; HTTP GET &bull; Estandarizado SICEFA</td>
                                </tr>
                                <tr>
                                    <th class="bg-light text-muted">Última Sincronización:</th>
                                    <td id="lblLastSyncText">
                                        @if(!empty($lastSync['date']))
                                            <span class="text-dark fw-semibold">{{ $lastSync['date'] }}</span> &bull;
                                            <span class="badge bg-light text-success border border-success-subtle">
                                                {{ $lastSync['total_synced'] }} usuarios sincronizados
                                            </span>
                                        @else
                                            <span class="text-muted fst-italic">Pendiente de sincronizar</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="p-3 rounded-3 h-100 d-flex flex-column justify-content-between" style="background-color: #f8fbf9; border: 1px dashed #bce5c6;">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-bolt text-warning"></i>
                                <strong class="small text-dark">Sincronización Automatizada</strong>
                            </div>
                            <p class="text-muted mb-2" style="font-size: 0.78rem; line-height: 1.35;">
                                Al ejecutar la sincronización, se actualizan las tablas maestras de personas, usuarios y roles de Control ECP sin duplicar información.
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 flex-grow-1" onclick="probarConexionApi()">
                                <i class="fas fa-arrows-rotate me-1"></i> Probar Enlace
                            </button>
                            <button type="button" class="btn btn-sm text-white rounded-pill px-3 flex-grow-1" style="background-color: var(--ecp-green);" data-bs-toggle="modal" data-bs-target="#modalSyncConfirm">
                                <i class="fas fa-download me-1"></i> Sincronizar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de Filtros y Búsqueda -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center justify-content-between">
                <!-- Filtro por Rol (Pills) -->
                <div class="col-lg-7">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="small text-muted fw-semibold me-1"><i class="fas fa-filter me-1"></i>Rol:</span>
                        <a href="{{ route('controlecp.administrador.usuarios', ['search' => $searchTerm, 'estado' => $estadoFilter]) }}"
                           class="filter-pill {{ empty($roleFilter) ? 'active' : '' }}">
                            Todos ({{ $stats['total'] }})
                        </a>
                        <a href="{{ route('controlecp.administrador.usuarios', ['rol' => 'admin', 'search' => $searchTerm, 'estado' => $estadoFilter]) }}"
                           class="filter-pill {{ $roleFilter === 'admin' ? 'active' : '' }}">
                            <i class="fas fa-shield-halved me-1"></i> Admin ({{ $stats['admins'] }})
                        </a>
                        <a href="{{ route('controlecp.administrador.usuarios', ['rol' => 'instructor', 'search' => $searchTerm, 'estado' => $estadoFilter]) }}"
                           class="filter-pill {{ $roleFilter === 'instructor' ? 'active' : '' }}">
                            <i class="fas fa-chalkboard-user me-1"></i> Instructor ({{ $stats['instructors'] }})
                        </a>
                        <a href="{{ route('controlecp.administrador.usuarios', ['rol' => 'apprentice', 'search' => $searchTerm, 'estado' => $estadoFilter]) }}"
                           class="filter-pill {{ $roleFilter === 'apprentice' ? 'active' : '' }}">
                            <i class="fas fa-user-graduate me-1"></i> Aprendiz ({{ $stats['apprentices'] }})
                        </a>
                    </div>
                    {{-- Filtro por Estado (HU2) --}}
                    <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                        <span class="small text-muted fw-semibold me-1"><i class="fas fa-toggle-on me-1"></i>Estado:</span>
                        <a href="{{ route('controlecp.administrador.usuarios', ['rol' => $roleFilter, 'search' => $searchTerm]) }}"
                           class="filter-pill {{ empty($estadoFilter) ? 'active' : '' }}">
                            Todos
                        </a>
                        <a href="{{ route('controlecp.administrador.usuarios', ['rol' => $roleFilter, 'search' => $searchTerm, 'estado' => 'Activo']) }}"
                           class="filter-pill {{ $estadoFilter === 'Activo' ? 'active' : '' }}">
                            <i class="fas fa-circle-check me-1"></i> Activos ({{ $stats['activos'] }})
                        </a>
                        <a href="{{ route('controlecp.administrador.usuarios', ['rol' => $roleFilter, 'search' => $searchTerm, 'estado' => 'Inactivo']) }}"
                           class="filter-pill {{ $estadoFilter === 'Inactivo' ? 'active' : '' }}"
                           style="{{ $estadoFilter === 'Inactivo' ? 'background-color: #dc3545 !important;' : '' }}">
                            <i class="fas fa-ban me-1"></i> Inactivos ({{ $stats['inactivos'] }})
                        </a>
                    </div>
                </div>

                <!-- Buscador en tiempo real -->
                <div class="col-lg-5">
                    <form method="GET" action="{{ route('controlecp.administrador.usuarios') }}" class="d-flex gap-2">
                        @if($roleFilter)
                            <input type="hidden" name="rol" value="{{ $roleFilter }}">
                        @endif
                        @if($estadoFilter)
                            <input type="hidden" name="estado" value="{{ $estadoFilter }}">
                        @endif
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill text-muted">
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
                            <button class="btn btn-outline-secondary rounded-end-pill px-3" type="submit">Filtrar</button>
                        </div>
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-3 flex-shrink-0" onclick="exportarUsuariosCSV()" title="Exportar a CSV">
                            <i class="fas fa-file-csv"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla Principal de Usuarios Sincronizados -->
    <div class="card border-0 shadow-sm" style="border-radius: 18px;">
        <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0" style="color: var(--ecp-navy);">
                <i class="fas fa-id-card-clip me-2" style="color: var(--ecp-green);"></i>Nómina de Usuarios SICEFA en Control ECP
            </h5>
            <span class="small text-muted">
                Mostrando {{ $usuarios->count() }} de {{ $usuarios->total() }} registros
            </span>
        </div>

        <div class="card-body p-0">
            @if($usuarios->count() > 0)
                <div class="table-responsive">
                    <table class="table table-users align-middle mb-0" id="tablaUsuariosEcp">
                        <thead class="table-light" style="font-size: 0.82rem; text-transform: uppercase; color: #52796f;">
                            <tr>
                                <th class="ps-4 py-3" style="width: 5%;">#</th>
                                <th style="width: 28%;">Persona / Documento</th>
                                <th style="width: 22%;">Usuario & Contacto</th>
                                <th style="width: 17%;">Rol Asignado en Control ECP</th>
                                <th style="width: 10%;">Estado</th>
                                <th style="width: 10%;">Sincronización</th>
                                <th class="text-end pe-4" style="width: 8%;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 0.88rem;">
                            @foreach($usuarios as $index => $u)
                                @php
                                    $p = $u->person;
                                    $hasAdmin = $u->hasRole('controlecp.admin');
                                    $hasInstructor = $u->hasRole('controlecp.instructor');
                                    $hasApprentice = $u->hasRole('controlecp.apprentice');

                                    $avatarBg = $hasAdmin ? '#e8f8e8' : ($hasInstructor ? '#ebf5fb' : '#fef9e7');
                                    $avatarColor = $hasAdmin ? '#1e7000' : ($hasInstructor ? '#0b5e94' : '#9c6c03');
                                @endphp
                                <tr>
                                    <!-- Avatar & Número -->
                                    <td class="ps-4">
                                        <div class="avatar-circle shadow-sm" style="background-color: {{ $avatarBg }}; color: {{ $avatarColor }};">
                                            {{ $u->initials }}
                                        </div>
                                    </td>

                                    <!-- Datos de Persona -->
                                    <td>
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

                                    <!-- Sincronización -->
                                    <td>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1" style="font-size: 0.75rem;">
                                            <i class="fas fa-link me-1"></i> API SICEFA
                                        </span>
                                        <div class="small text-muted mt-1" style="font-size: 0.72rem;">
                                            {{ $u->updated_at ? $u->updated_at->format('d/m/Y H:i') : 'Activo' }}
                                        </div>
                                    </td>

                                    <!-- Acciones -->
                                    <td class="text-end pe-4">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light rounded-pill px-2" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-vertical text-muted"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="border-radius: 12px; font-size: 0.85rem;">
                                                <li>
                                                    <button class="dropdown-item py-2" type="button"
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
                                                        <i class="fas fa-id-card me-2 text-primary"></i> Ver Ficha SICEFA
                                                    </button>
                                                </li>
                                                {{-- HU5: Editar usuario --}}
                                                <li>
                                                    <a class="dropdown-item py-2"
                                                       href="{{ route('controlecp.administrador.usuarios.edit', $u->id) }}">
                                                        <i class="fas fa-user-pen me-2 text-secondary"></i> Editar Usuario
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                {{-- HU7 / HU8: Desactivar / Activar --}}
                                                @if(($u->estado ?? 'Activo') === 'Activo')
                                                    <li>
                                                        <button class="dropdown-item py-2 text-danger" type="button"
                                                                onclick="confirmarCambioEstado({{ $u->id }}, 'desactivar', '{{ addslashes($u->full_name) }}')">
                                                            <i class="fas fa-ban me-2"></i> Desactivar Usuario
                                                        </button>
                                                    </li>
                                                @else
                                                    <li>
                                                        <button class="dropdown-item py-2 text-success" type="button"
                                                                onclick="confirmarCambioEstado({{ $u->id }}, 'activar', '{{ addslashes($u->full_name) }}')">
                                                            <i class="fas fa-circle-check me-2"></i> Activar Usuario
                                                        </button>
                                                    </li>
                                                @endif
                                                <li><hr class="dropdown-divider"></li>
                                                <li class="dropdown-header small text-muted">Cambiar Rol Control ECP:</li>
                                                <li>
                                                    <button class="dropdown-item py-1" type="button" onclick="cambiarRolUsuario({{ $u->id }}, 'controlecp.admin')">
                                                        <i class="fas fa-user-shield me-2 text-success"></i> Asignar Administrador
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item py-1" type="button" onclick="cambiarRolUsuario({{ $u->id }}, 'controlecp.instructor')">
                                                        <i class="fas fa-chalkboard-user me-2 text-info"></i> Asignar Instructor
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item py-1" type="button" onclick="cambiarRolUsuario({{ $u->id }}, 'controlecp.apprentice')">
                                                        <i class="fas fa-user-graduate me-2 text-warning"></i> Asignar Aprendiz
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="p-3 border-top d-flex justify-content-between align-items-center">
                    <span class="small text-muted">
                        Página {{ $usuarios->currentPage() }} de {{ $usuarios->lastPage() }}
                    </span>
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
                        <a href="{{ route('controlecp.administrador.usuarios') }}" class="btn btn-outline-secondary rounded-pill px-4">
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
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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
                    'X-CSRF-TOKEN': csrfToken,
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
                    'X-CSRF-TOKEN': csrfToken,
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
                    'X-CSRF-TOKEN': csrfToken,
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
                    'X-CSRF-TOKEN': csrfToken,
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
                    'X-CSRF-TOKEN': csrfToken,
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
                    'X-CSRF-TOKEN': csrfToken,
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

        try {
            const resp = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({})
            });
            const data = await resp.json();

            bootstrap.Modal.getInstance(document.getElementById('modalCambioEstado')).hide();

            if (data.success) {
                mostrarAlerta(data.message, 'success');
                setTimeout(() => { window.location.reload(); }, 900);
            } else {
                mostrarAlerta(data.message, 'warning');
            }
        } catch (e) {
            mostrarAlerta('Error al cambiar el estado: ' + e.message, 'danger');
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
                    'X-CSRF-TOKEN': csrfToken,
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

    // ── 9. Utilidades (Copiar, Alertas, Exportar) ───────────────────────────────────
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

    function exportarUsuariosCSV() {
        const table = document.getElementById('tablaUsuariosEcp');
        if (!table) return;

        let csv = [];
        const rows = table.querySelectorAll('tr');

        for (let i = 0; i < rows.length; i++) {
            let row = [], cols = rows[i].querySelectorAll('td, th');
            for (let j = 0; j < cols.length - 1; j++) {
                let text = cols[j].innerText.replace(/"/g, '""').trim();
                row.push('"' + text + '"');
            }
            csv.push(row.join(','));
        }

        const csvContent = 'data:text/csv;charset=utf-8,\uFEFF' + encodeURIComponent(csv.join('\n'));
        const link = document.createElement('a');
        link.setAttribute('href', csvContent);
        link.setAttribute('download', 'usuarios_controlecp_sicefa_' + new Date().toISOString().slice(0, 10) + '.csv');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>
@endpush
@endsection
