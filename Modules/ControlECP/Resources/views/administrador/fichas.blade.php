@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    :root {
        --ecp-green: #39A900;
        --ecp-green-hover: #2e8800;
        --ecp-green-light: #e8f8e8;
        --ecp-navy: #001A29;
        --ecp-navy-light: #00324D;
    }

    .stat-card-ficha {
        border-radius: 16px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 1px solid rgba(0, 0, 0, 0.04);
        background: #ffffff;
    }
    .stat-card-ficha:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08) !important;
    }

    .pulse-dot-fichas {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: var(--ecp-green);
        display: inline-block;
        box-shadow: 0 0 0 0 rgba(57, 169, 0, 0.7);
        animation: pulse-dot-fichas 1.8s infinite;
    }
    @keyframes pulse-dot-fichas {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(57, 169, 0, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(57, 169, 0, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(57, 169, 0, 0); }
    }

    .filter-pill-ficha {
        border-radius: 20px;
        font-size: 0.82rem;
        font-weight: 600;
        padding: 6px 14px;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .filter-pill-ficha.active {
        background-color: var(--ecp-green);
        color: #ffffff !important;
        box-shadow: 0 4px 10px rgba(57, 169, 0, 0.3);
    }
    .filter-pill-ficha:not(.active) {
        background-color: #f1f4f3;
        color: var(--ecp-navy);
    }
    .filter-pill-ficha:not(.active):hover {
        background-color: #e5ece9;
    }

    .table-fichas tbody tr {
        transition: background-color 0.15s ease;
    }
    .table-fichas tbody tr:hover {
        background-color: #f6fcf8;
    }

    .ficha-badge {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        letter-spacing: 0.5px;
        font-size: 0.9rem;
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
                        Fichas de Formación
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: var(--ecp-navy); font-family: 'Outfit', sans-serif;">
                <i class="fas fa-id-card-clip me-2" style="color: var(--ecp-green);"></i>Gestión de Fichas de Formación
            </h2>
            <p class="text-muted small mb-0">Control ECP &bull; Sincronización e integración con la API Académica Central (SICEFA / SOFIA Plus)</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('controlecp.administrador.inicio') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver al Inicio
            </a>
            <button type="button" class="btn btn-outline-secondary rounded-pill px-3 btn-sm" data-bs-toggle="modal" data-bs-target="#modalConfigApiFichas">
                <i class="fas fa-sliders me-1"></i> Configurar API Fichas
            </button>
            <button type="button" class="btn btn-outline-success rounded-pill px-3 btn-sm" onclick="probarConexionApiFichas()">
                <i class="fas fa-arrows-rotate me-1" id="syncIconFichas"></i> Comprobar Conexión
            </button>
            <button type="button" class="btn text-white rounded-pill px-3 btn-sm shadow-sm" style="background-color: var(--ecp-green); border-color: var(--ecp-green);" data-bs-toggle="modal" data-bs-target="#modalSyncFichasConfirm">
                <i class="fas fa-cloud-arrow-down me-1"></i> Sincronizar Fichas
            </button>
        </div>
    </div>

    <!-- Alertas Flash -->
    <div id="liveAlertFichasBox"></div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 14px;">
            <i class="fas fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Alerta Principal: Estado de la API Central de Fichas -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 18px; background: linear-gradient(135deg, #f0fbf4 0%, #d8f3dc 100%); border-left: 6px solid var(--ecp-green) !important;">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm"
                         style="width: 58px; height: 58px; background: #FFFFFF; color: var(--ecp-green); font-size: 1.6rem;">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <h4 class="h5 fw-bold mb-0" style="color: var(--ecp-navy);">
                                Sincronización Automática con la API Académica Central
                            </h4>
                            <span class="badge rounded-pill bg-white text-success border border-success-subtle d-inline-flex align-items-center gap-1 py-1 px-2" style="font-size: 0.75rem;">
                                <span class="pulse-dot-fichas"></span> Servicio Activo &bull; SICEFA
                            </span>
                        </div>
                        <p class="mb-0 text-dark" style="font-size: 0.9rem; line-height: 1.45;">
                            La información de <strong>número de ficha, programa de formación, fechas lectivas y aprendices matriculados</strong> se consume y homologa directamente desde la <strong>API REST Central del ERP SENA Empresa / SOFIA Plus / SICEFA</strong> para asignación de asistencias y momentos de paz.
                        </p>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
                    <button type="button" class="btn btn-sm text-white rounded-pill px-3 shadow-sm" style="background-color: var(--ecp-navy);" data-bs-toggle="modal" data-bs-target="#modalConfigApiFichas">
                        <i class="fas fa-gear me-1"></i> Ver Endpoint
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success bg-white rounded-pill px-3 shadow-sm" onclick="probarConexionApiFichas()">
                        <i class="fas fa-plug me-1"></i> Test Ping Fichas
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas de Estadísticas (KPIs) -->
    <div class="row g-3 mb-4">
        <!-- KPI 1: Total Fichas -->
        <div class="col-6 col-md-3">
            <div class="card stat-card-ficha shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">Total Fichas</span>
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width: 36px; height: 36px; background: #e8f8e8; color: var(--ecp-green);">
                            <i class="fas fa-id-card"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark">{{ $stats['total'] }}</h3>
                    <small class="text-success d-flex align-items-center gap-1 mt-1">
                        <i class="fas fa-circle-check" style="font-size: 0.7rem;"></i> Registradas en SICEFA
                    </small>
                </div>
            </div>
        </div>

        <!-- KPI 2: Fichas Activas -->
        <div class="col-6 col-md-3">
            <div class="card stat-card-ficha shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">Fichas Activas</span>
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width: 36px; height: 36px; background: #ebf5fb; color: #2980b9;">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-primary">{{ $stats['activas'] }}</h3>
                    <small class="text-muted mt-1 d-block">En etapa formativa lectiva</small>
                </div>
            </div>
        </div>

        <!-- KPI 3: Aprendices Vinculados -->
        <div class="col-6 col-md-3">
            <div class="card stat-card-ficha shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">Total Aprendices</span>
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width: 36px; height: 36px; background: #fef9e7; color: #d68910;">
                            <i class="fas fa-users-viewfinder"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($stats['aprendices'], 0, ',', '.') }}</h3>
                    <small class="text-muted mt-1 d-block">Matriculados en el centro</small>
                </div>
            </div>
        </div>

        <!-- KPI 4: Programas de Formación -->
        <div class="col-6 col-md-3">
            <div class="card stat-card-ficha shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">Programas Académicos</span>
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width: 36px; height: 36px; background: #f4ecf7; color: #8e44ad;">
                            <i class="fas fa-book-bookmark"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark">{{ $stats['programas'] }}</h3>
                    <small class="text-muted mt-1 d-block">Técnicos & Tecnólogos</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de Estado Técnico del Servicio API de Fichas -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-header bg-white border-0 pt-3 pb-2 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="fw-bold mb-0" style="color: var(--ecp-navy); font-size: 1.05rem;">
                <i class="fas fa-server me-2 text-success"></i>Estado del Servicio API de Fichas
            </h5>
            <div class="d-flex align-items-center gap-2">
                <span id="apiFichasConnectionBadge" class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill">
                    <i class="fas fa-circle-check me-1"></i> Servicio Listo
                </span>
                <span class="small text-muted" id="apiFichasLatencyText">Latencia: -- ms</span>
            </div>
        </div>
        <div class="card-body px-4 pb-3 pt-1">
            <div class="row g-3">
                <div class="col-md-7">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0" style="font-size: 0.85rem;">
                            <tbody>
                                <tr>
                                    <th class="bg-light text-muted" style="width: 35%;">Endpoint de Consumo:</th>
                                    <td>
                                        <code class="text-primary fw-bold text-break" id="lblFichasEndpointUrl">{{ $apiConfig['url'] ?? route('controlecp.api.fichas.mock') }}</code>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light text-muted">Datos Homologados:</th>
                                    <td>Número de Ficha, Programa, Nivel, Fechas Lectivas, Aprendices Matriculados</td>
                                </tr>
                                <tr>
                                    <th class="bg-light text-muted">Última Sincronización:</th>
                                    <td id="lblFichasLastSyncText">
                                        <span class="text-dark fw-semibold">{{ $lastSync['date'] ?? now()->format('d/m/Y H:i') }}</span> &bull;
                                        <span class="badge bg-light text-success border border-success-subtle">
                                            {{ $lastSync['total_synced'] ?? $stats['total'] }} fichas sincronizadas
                                        </span>
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
                                Al sincronizar fichas, las actividades, asistencias y sesiones de los tres momentos de Control ECP se vincularán de manera automática a la nómina de aprendices.
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 flex-grow-1" onclick="probarConexionApiFichas()">
                                <i class="fas fa-arrows-rotate me-1"></i> Probar Enlace
                            </button>
                            <button type="button" class="btn btn-sm text-white rounded-pill px-3 flex-grow-1" style="background-color: var(--ecp-green);" data-bs-toggle="modal" data-bs-target="#modalSyncFichasConfirm">
                                <i class="fas fa-download me-1"></i> Sincronizar Fichas
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
                <!-- Filtro por Estado (Pills) -->
                <div class="col-lg-5">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="small text-muted fw-semibold me-1"><i class="fas fa-filter me-1"></i>Estado:</span>
                        <a href="{{ route('controlecp.administrador.fichas', ['search' => $searchTerm]) }}"
                           class="filter-pill-ficha {{ empty($statusFilter) ? 'active' : '' }}">
                            Todas ({{ $stats['total'] }})
                        </a>
                        <a href="{{ route('controlecp.administrador.fichas', ['estado' => 'activo', 'search' => $searchTerm]) }}"
                           class="filter-pill-ficha {{ $statusFilter === 'activo' ? 'active' : '' }}">
                            <i class="fas fa-circle-check me-1"></i> Activas ({{ $stats['activas'] }})
                        </a>
                        <a href="{{ route('controlecp.administrador.fichas', ['estado' => 'inactivo', 'search' => $searchTerm]) }}"
                           class="filter-pill-ficha {{ $statusFilter === 'inactivo' ? 'active' : '' }}">
                            <i class="fas fa-circle-pause me-1"></i> Inactivas ({{ $stats['inactivas'] }})
                        </a>
                    </div>
                </div>

                <!-- Buscador de Fichas -->
                <div class="col-lg-7">
                    <form method="GET" action="{{ route('controlecp.administrador.fichas') }}" class="d-flex gap-2">
                        @if($statusFilter)
                            <input type="hidden" name="estado" value="{{ $statusFilter }}">
                        @endif
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill text-muted">
                                <i class="fas fa-magnifying-glass"></i>
                            </span>
                            <input type="text" name="search" id="inputSearchFicha" class="form-control border-start-0 ps-0"
                                   placeholder="Buscar por número de ficha o nombre del programa académico..."
                                   value="{{ $searchTerm }}">
                            @if(!empty($searchTerm))
                                <a href="{{ route('controlecp.administrador.fichas', ['estado' => $statusFilter]) }}" class="input-group-text bg-white text-muted text-decoration-none">
                                    <i class="fas fa-xmark"></i>
                                </a>
                            @endif
                            <button class="btn btn-outline-secondary rounded-end-pill px-3" type="submit">Buscar</button>
                        </div>
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-3 flex-shrink-0" onclick="exportarFichasCSV()" title="Exportar Fichas a CSV">
                            <i class="fas fa-file-csv"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla Principal de Fichas Académicas -->
    <div class="card border-0 shadow-sm" style="border-radius: 18px;">
        <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0" style="color: var(--ecp-navy);">
                <i class="fas fa-graduation-cap me-2 text-success"></i>Directorio de Fichas Académicas SICEFA
            </h5>
            <span class="small text-muted">
                Mostrando {{ $fichas->count() }} de {{ $fichas->total() }} fichas registradas
            </span>
        </div>

        <div class="card-body p-0">
            @if($fichas->count() > 0)
                <div class="table-responsive">
                    <table class="table table-fichas align-middle mb-0" id="tablaFichasEcp">
                        <thead class="table-light" style="font-size: 0.82rem; text-transform: uppercase; color: #52796f;">
                            <tr>
                                <th class="ps-4 py-3" style="width: 14%;">Ficha</th>
                                <th style="width: 34%;">Programa de Formación</th>
                                <th style="width: 14%;">Modalidad</th>
                                <th style="width: 12%;">Aprendices</th>
                                <th style="width: 14%;">Estado</th>
                                <th class="text-end pe-4" style="width: 12%;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 0.88rem;">
                            @foreach($fichas as $f)
                                @php
                                    $isActive = ($f->status === 'Activo' || empty($f->status));
                                @endphp
                                <tr>
                                    <!-- Ficha Badge -->
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-light text-dark border px-3 py-2 ficha-badge shadow-xs">
                                                <i class="fas fa-id-card text-success me-1"></i>{{ $f->code }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Programa -->
                                    <td>
                                        <div class="fw-bold text-dark">
                                            {{ $f->program ? $f->program->name : 'PROGRAMA EN FORMACIÓN' }}
                                        </div>
                                        <div class="small text-muted mt-1">
                                            <span class="badge bg-light text-muted border">
                                                {{ $f->program ? ($f->program->program_type ?? 'Tecnólogo') : 'Formación SENA' }}
                                            </span>
                                            @if($f->start_date)
                                                <span class="ms-1" style="font-size: 0.75rem;">
                                                    <i class="far fa-calendar me-1"></i>{{ $f->start_date }} a {{ $f->end_date ?? 'En curso' }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Modalidad -->
                                    <td>
                                        <span class="small text-dark fw-semibold">
                                            <i class="fas fa-location-dot text-muted me-1"></i>{{ $f->program ? ($f->program->training_type ?: 'Presencial') : 'Presencial' }}
                                        </span>
                                    </td>

                                    <!-- Aprendices Matriculados -->
                                    <td>
                                        <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 py-1"
                                                onclick="cargarAprendicesFicha({{ $f->id }})" title="Ver nómina de aprendices">
                                            <i class="fas fa-users text-primary me-1"></i>
                                            <strong>{{ $f->apprentices_count }}</strong>
                                        </button>
                                    </td>

                                    <!-- Estado -->
                                    <td>
                                        @if($isActive)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                                <i class="fas fa-circle-check me-1"></i> Activo
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1">
                                                <i class="fas fa-circle-xmark me-1"></i> Inactivo
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Acciones -->
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-sm text-white rounded-pill px-3 shadow-xs"
                                                style="background-color: var(--ecp-green);"
                                                onclick="cargarAprendicesFicha({{ $f->id }})">
                                            <i class="fas fa-users-viewfinder me-1"></i> Aprendices
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="p-3 border-top d-flex justify-content-between align-items-center">
                    <span class="small text-muted">
                        Página {{ $fichas->currentPage() }} de {{ $fichas->lastPage() }}
                    </span>
                    <div>
                        {{ $fichas->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @else
                <div class="text-center py-5 px-3">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3"
                         style="width: 80px; height: 80px; background: #f0fbf4; color: var(--ecp-green); font-size: 2.2rem;">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">No se encontraron fichas con los filtros aplicados</h5>
                    <p class="text-muted small mb-4" style="max-width: 480px; margin: 0 auto;">
                        Puede sincronizar la nómina de cursos desde la API Académica Central o restablecer la búsqueda.
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn text-white rounded-pill px-4 shadow-sm" style="background-color: var(--ecp-green);" data-bs-toggle="modal" data-bs-target="#modalSyncFichasConfirm">
                            <i class="fas fa-cloud-arrow-down me-1"></i> Sincronizar Fichas Ahora
                        </button>
                        <a href="{{ route('controlecp.administrador.fichas') }}" class="btn btn-outline-secondary rounded-pill px-4">
                            Limpiar Filtros
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>

<!-- ========================================================================================= -->
<!-- MODALES DEL SISTEMA DE GESTIÓN Y SINCRONIZACIÓN DE FICHAS                                  -->
<!-- ========================================================================================= -->

<!-- 1. Modal: Configuración de la API REST Central de Fichas -->
<div class="modal fade" id="modalConfigApiFichas" tabindex="-1" aria-hidden="true">
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
                            Configuración de Conexión API Central de Fichas
                        </h5>
                        <p class="text-muted small mb-0">Endpoint Académico Central (SICEFA / SOFIA Plus)</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formApiFichasConfig" onsubmit="guardarConfigApiFichas(event)">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">URL del Endpoint REST de Fichas:</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="fas fa-link"></i></span>
                            <input type="url" class="form-control" id="cfgApiFichasUrl" required
                                   value="{{ $apiConfig['url'] ?? route('controlecp.api.fichas.mock') }}"
                                   placeholder="https://sicefa.senaempresa.edu.co/api/v1/fichas">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="restablecerUrlMockFichas()" title="Restablecer al endpoint integrado">
                                <i class="fas fa-rotate-left me-1"></i> Local Mock
                            </button>
                        </div>
                        <div class="form-text">
                            Endpoint que entrega la lista de cursos, programas académicos, jornadas y aprendices en formato JSON.
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small text-dark">Bearer Token (Opcional):</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="fas fa-key"></i></span>
                                <input type="password" class="form-control" id="cfgApiFichasToken"
                                       value="{{ $apiConfig['token'] ?? '' }}"
                                       placeholder="Token de autorización Bearer si es requerido...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-dark">Timeout de Respuesta:</label>
                            <select class="form-select" id="cfgApiFichasTimeout">
                                <option value="5" {{ ($apiConfig['timeout'] ?? 5) == 5 ? 'selected' : '' }}>5 segundos</option>
                                <option value="10" {{ ($apiConfig['timeout'] ?? 5) == 10 ? 'selected' : '' }}>10 segundos</option>
                                <option value="15" {{ ($apiConfig['timeout'] ?? 5) == 15 ? 'selected' : '' }}>15 segundos</option>
                            </select>
                        </div>
                    </div>

                    <!-- Panel de Prueba en Vivo -->
                    <div class="p-3 rounded-3 mb-3" style="background-color: #f8faf9; border: 1px solid #e1ece5;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold small text-dark"><i class="fas fa-terminal me-1 text-success"></i>Prueba de Conexión en Vivo:</span>
                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="probarConexionModalFichas()">
                                <i class="fas fa-play me-1"></i> Probar Conexión Ahora
                            </button>
                        </div>
                        <div id="modalTestResultBoxFichas" class="small p-2 rounded bg-white border text-muted" style="min-height: 48px; font-family: monospace;">
                            Presione "Probar Conexión Ahora" para validar la disponibilidad del servicio.
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

<!-- 2. Modal: Confirmación y Sincronización Masiva de Fichas -->
<div class="modal fade" id="modalSyncFichasConfirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4 text-center justify-content-center position-relative">
                <button type="button" class="btn-close position-absolute end-0 top-0 mt-3 me-3" data-bs-dismiss="modal"></button>
                <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                     style="width: 65px; height: 65px; background: #e8f8e8; color: var(--ecp-green); font-size: 1.8rem;">
                    <i class="fas fa-layer-group" id="modalSyncFichasIcon"></i>
                </div>
            </div>
            <div class="modal-body p-4 text-center">
                <h5 class="fw-bold mb-2" style="color: var(--ecp-navy);">
                    Sincronizar Fichas Académicas desde API Central
                </h5>
                <p class="text-muted small mb-3">
                    Se conectará a la <strong>API REST Académica Central SICEFA / SOFIA Plus</strong> para actualizar el catálogo de programas y fichas formativas.
                </p>

                <!-- Beneficios -->
                <div class="text-start bg-light p-3 rounded-3 mb-4" style="font-size: 0.84rem;">
                    <div class="d-flex align-items-center gap-2 mb-2 text-dark">
                        <i class="fas fa-circle-check text-success"></i>
                        <span>Crea automáticamente los programas y números de ficha nuevos.</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-2 text-dark">
                        <i class="fas fa-circle-check text-success"></i>
                        <span>Actualiza fechas de inicio, fin lectivo y etapa productiva.</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 text-dark">
                        <i class="fas fa-circle-check text-success"></i>
                        <span>Prepara las nóminas de aprendices para registro de asistencia y momentos ECP.</span>
                    </div>
                </div>

                <!-- Barra de Progreso -->
                <div id="syncFichasProgressContainer" class="d-none text-start mb-3">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span id="syncFichasProgressLabel">Conectando con la API...</span>
                        <span id="syncFichasProgressPercent">0%</span>
                    </div>
                    <div class="progress" style="height: 10px; border-radius: 8px;">
                        <div id="syncFichasProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 0%;"></div>
                    </div>
                </div>

                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal" id="btnCancelSyncFichas">
                        Cancelar
                    </button>
                    <button type="button" class="btn text-white rounded-pill px-4 shadow-sm" style="background-color: var(--ecp-green);" onclick="ejecutarSincronizacionFichas()" id="btnStartSyncFichas">
                        <i class="fas fa-arrows-rotate me-1"></i> Iniciar Sincronización
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 3. Modal: Nómina de Aprendices por Ficha -->
<div class="modal fade" id="modalAprendicesFicha" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="modal-title fw-bold" style="color: var(--ecp-navy);">
                        <i class="fas fa-users-viewfinder me-2 text-success"></i>Nómina de Aprendices Matriculados
                    </h5>
                    <p class="text-muted small mb-0" id="modalFichaSubTitle">Ficha: -- &bull; Programa: --</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="modalAprendicesLoading" class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-success mb-2"></i>
                    <div class="small text-muted">Cargando aprendices matriculados desde la base central...</div>
                </div>

                <div id="modalAprendicesContent" class="d-none">
                    <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="ps-3 py-2">Documento</th>
                                    <th>Nombre del Aprendiz</th>
                                    <th>Correo Institucional</th>
                                    <th>Teléfono</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="modalAprendicesTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="modalAprendicesEmpty" class="d-none text-center py-4 text-muted small">
                    <i class="fas fa-users-slash fa-2x mb-2 text-muted"></i>
                    <p class="mb-0">Esta ficha aún no cuenta con aprendices vinculados en la base de datos.</p>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 btn-sm" data-bs-dismiss="modal">
                    Cerrar
                </button>
                <button type="button" class="btn text-white rounded-pill px-4 btn-sm" style="background-color: var(--ecp-navy);" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Imprimir Nómina
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // ── 1. Probar Conexión con la API Central de Fichas ─────────────────────────────
    async function probarConexionApiFichas() {
        const syncIcon = document.getElementById('syncIconFichas');
        const badge = document.getElementById('apiFichasConnectionBadge');
        const latencyText = document.getElementById('apiFichasLatencyText');

        if (syncIcon) syncIcon.classList.add('fa-spin');
        mostrarAlertaFichas('Probando enlace con la API Central de Fichas...', 'info');

        try {
            const resp = await fetch("{{ route('controlecp.administrador.fichas.test_api') }}", {
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
                    badge.innerHTML = '<i class="fas fa-circle-check me-1"></i> En Línea (' + data.count + ' fichas)';
                }
                if (latencyText) latencyText.innerText = 'Latencia: ' + data.latency_ms + ' ms';
                mostrarAlertaFichas('¡Enlace verificado! ' + data.count + ' fichas disponibles en la API Central.', 'success');
            } else {
                if (badge) {
                    badge.className = 'badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1 rounded-pill';
                    badge.innerHTML = '<i class="fas fa-triangle-exclamation me-1"></i> Modo Fallback Activo';
                }
                mostrarAlertaFichas(data.message, 'warning');
            }
        } catch (e) {
            if (syncIcon) syncIcon.classList.remove('fa-spin');
            mostrarAlertaFichas('Error de red: ' + e.message, 'danger');
        }
    }

    // ── 2. Prueba desde el Modal de Configuración ────────────────────────────────────
    async function probarConexionModalFichas() {
        const url = document.getElementById('cfgApiFichasUrl').value.trim();
        const token = document.getElementById('cfgApiFichasToken').value.trim();
        const timeout = document.getElementById('cfgApiFichasTimeout').value;
        const resultBox = document.getElementById('modalTestResultBoxFichas');

        resultBox.innerHTML = '<span class="text-primary"><i class="fas fa-spinner fa-spin me-1"></i> Consultando ' + url + '...</span>';

        try {
            const resp = await fetch("{{ route('controlecp.administrador.fichas.test_api') }}", {
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
                    <div>Latencia: <strong>${data.latency_ms} ms</strong> | Fichas encontradas: <strong>${data.count}</strong></div>
                    <div class="text-muted mt-1" style="font-size: 0.75rem;">Muestra: ${JSON.stringify(data.sample)}</div>
                `;
            } else {
                resultBox.innerHTML = `
                    <div class="text-danger fw-bold"><i class="fas fa-triangle-exclamation me-1"></i> ${data.message}</div>
                `;
            }
        } catch (e) {
            resultBox.innerHTML = '<div class="text-danger"><i class="fas fa-circle-xmark me-1"></i> Error: ' + e.message + '</div>';
        }
    }

    // ── 3. Guardar Configuración de Fichas ──────────────────────────────────────────
    async function guardarConfigApiFichas(e) {
        e.preventDefault();
        const url = document.getElementById('cfgApiFichasUrl').value.trim();
        const token = document.getElementById('cfgApiFichasToken').value.trim();
        const timeout = document.getElementById('cfgApiFichasTimeout').value;

        try {
            const resp = await fetch("{{ route('controlecp.administrador.fichas.save_config') }}", {
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
                document.getElementById('lblFichasEndpointUrl').innerText = data.config.url;
                bootstrap.Modal.getInstance(document.getElementById('modalConfigApiFichas')).hide();
                mostrarAlertaFichas('Configuración de la API de Fichas guardada con éxito.', 'success');
            }
        } catch (e) {
            mostrarAlertaFichas('Error: ' + e.message, 'danger');
        }
    }

    function restablecerUrlMockFichas() {
        document.getElementById('cfgApiFichasUrl').value = "{{ route('controlecp.api.fichas.mock') }}";
    }

    // ── 4. Sincronización Masiva de Fichas ────────────────────────────────────────────
    async function ejecutarSincronizacionFichas() {
        const progressContainer = document.getElementById('syncFichasProgressContainer');
        const progressBar = document.getElementById('syncFichasProgressBar');
        const progressLabel = document.getElementById('syncFichasProgressLabel');
        const progressPercent = document.getElementById('syncFichasProgressPercent');
        const btnStart = document.getElementById('btnStartSyncFichas');
        const btnCancel = document.getElementById('btnCancelSyncFichas');
        const icon = document.getElementById('modalSyncFichasIcon');

        progressContainer.classList.remove('d-none');
        btnStart.disabled = true;
        btnCancel.disabled = true;
        if (icon) icon.className = 'fas fa-arrows-rotate fa-spin text-success';

        let progress = 10;
        progressBar.style.width = progress + '%';
        progressPercent.innerText = progress + '%';
        progressLabel.innerText = 'Consultando endpoint académico central de fichas...';

        const timer = setInterval(() => {
            if (progress < 85) {
                progress += 15;
                progressBar.style.width = progress + '%';
                progressPercent.innerText = progress + '%';
                if (progress === 40) progressLabel.innerText = 'Validando programas de formación académica...';
                if (progress === 70) progressLabel.innerText = 'Actualizando fechas lectivas y estados formativos...';
            }
        }, 300);

        try {
            const resp = await fetch("{{ route('controlecp.administrador.fichas.sync') }}", {
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
            progressLabel.innerText = '¡Fichas Sincronizadas!';

            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('modalSyncFichasConfirm')).hide();
                if (data.success) {
                    mostrarAlertaFichas(data.message, 'success');
                    setTimeout(() => { window.location.reload(); }, 1200);
                } else {
                    mostrarAlertaFichas(data.message, 'danger');
                    btnStart.disabled = false;
                    btnCancel.disabled = false;
                }
            }, 600);
        } catch (e) {
            clearInterval(timer);
            progressBar.classList.add('bg-danger');
            progressLabel.innerText = 'Error: ' + e.message;
            btnStart.disabled = false;
            btnCancel.disabled = false;
        }
    }

    // ── 5. Cargar Aprendices de una Ficha ─────────────────────────────────────────────
    async function cargarAprendicesFicha(courseId) {
        const modal = new bootstrap.Modal(document.getElementById('modalAprendicesFicha'));
        const loading = document.getElementById('modalAprendicesLoading');
        const content = document.getElementById('modalAprendicesContent');
        const empty = document.getElementById('modalAprendicesEmpty');
        const subTitle = document.getElementById('modalFichaSubTitle');
        const tableBody = document.getElementById('modalAprendicesTableBody');

        loading.classList.remove('d-none');
        content.classList.add('d-none');
        empty.classList.add('d-none');
        tableBody.innerHTML = '';
        modal.show();

        try {
            const url = "{{ url('/control-ecp/administrador/fichas') }}/" + courseId + "/aprendices";
            const resp = await fetch(url, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await resp.json();

            loading.classList.add('d-none');

            if (data.success) {
                subTitle.innerHTML = `Ficha: <strong>${data.ficha_code}</strong> &bull; Programa: <strong>${data.program_name}</strong> &bull; (${data.total_aprendices} aprendices)`;

                if (data.aprendices.length > 0) {
                    let rows = '';
                    data.aprendices.forEach(a => {
                        rows += `
                            <tr>
                                <td class="ps-3">
                                    <span class="badge bg-light text-dark border">${a.document_type}</span>
                                    <strong>${a.document_number}</strong>
                                </td>
                                <td class="fw-bold text-dark">${a.full_name}</td>
                                <td>
                                    <a href="mailto:${a.email}" class="text-decoration-none text-muted">
                                        <i class="far fa-envelope me-1 text-success"></i>${a.email}
                                    </a>
                                </td>
                                <td>${a.telephone}</td>
                                <td>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                        ${a.status}
                                    </span>
                                </td>
                            </tr>
                        `;
                    });
                    tableBody.innerHTML = rows;
                    content.classList.remove('d-none');
                } else {
                    empty.classList.remove('d-none');
                }
            }
        } catch (e) {
            loading.classList.add('d-none');
            empty.classList.remove('d-none');
            empty.innerHTML = '<span class="text-danger">Error al cargar la nómina de aprendices: ' + e.message + '</span>';
        }
    }

    // ── 6. Utilidades (Alertas y CSV) ───────────────────────────────────────────────
    function mostrarAlertaFichas(mensaje, tipo = 'success') {
        const container = document.getElementById('liveAlertFichasBox');
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

    function exportarFichasCSV() {
        const table = document.getElementById('tablaFichasEcp');
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
        link.setAttribute('download', 'fichas_controlecp_sicefa_' + new Date().toISOString().slice(0, 10) + '.csv');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>
@endpush
@endsection
