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

    .fichas-hero {
        background: linear-gradient(120deg, #001A29, #00324D);
        border-radius: 24px;
        padding: 2rem 2.25rem;
        color: #fff;
    }
    .fichas-hero .breadcrumb {
        --bs-breadcrumb-divider-color: #c7d4da;
    }
    .fichas-hero .breadcrumb-item,
    .fichas-hero .breadcrumb-item.active {
        color: #c7d4da !important;
    }
    .fichas-hero .breadcrumb-item a {
        color: #62E31D !important;
    }
    .fichas-hero h2 {
        color: #fff !important;
    }
    .fichas-hero .hero-eyebrow {
        color: #62E31D;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
    }
    .fichas-hero .hero-subtitle {
        color: #c7d4da;
    }
    .fichas-hero .btn-outline-secondary {
        color: #fff;
        border-color: rgba(255, 255, 255, .65);
    }

    .stat-card-ficha {
        border-radius: 16px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 2px solid transparent;
        background: #ffffff;
    }
    .stat-card-ficha-total {
        border-color: rgba(57, 169, 0, .35);
    }
    .stat-card-ficha-active {
        border-color: rgba(41, 128, 185, .35);
    }
    .stat-card-ficha-apprentices {
        border-color: rgba(214, 137, 16, .35);
    }
    .stat-card-ficha-programs {
        border-color: rgba(142, 68, 173, .35);
    }
    .stat-card-ficha:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08) !important;
    }

    .fichas-status-select {
        min-width: 220px;
        border-color: #dce5df;
        color: var(--ecp-navy);
        font-weight: 600;
    }
    .fichas-status-select:focus {
        border-color: var(--ecp-green);
        box-shadow: 0 0 0 0.2rem rgba(57, 169, 0, 0.15);
    }
    .fichas-filter-card {
        background-color: var(--ecp-navy);
        border-radius: 24px !important;
    }
    .fichas-filter-card .text-muted {
        color: #ffffff !important;
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

    .fichas-pagination {
        display: flex;
        justify-content: center;
        margin: 1rem auto;
        padding: 0;
        border-top: 1px solid #eef1f2;
        width: fit-content;
    }
    .fichas-pagination nav {
        display: flex;
        justify-content: center;
    }
    .fichas-pagination nav > div:first-child {
        display: none !important;
    }
    .fichas-pagination nav > div:last-child > div:first-child {
        display: none !important;
    }
    .fichas-pagination nav > div:last-child {
        justify-content: center !important;
    }
    .fichas-pagination .pagination {
        margin-bottom: 0;
        gap: 0;
    }
    .fichas-pagination .page-link {
        min-width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #b8dfc4;
        border-radius: 0 !important;
        color: var(--ecp-green);
        text-align: center;
        margin-left: -1px;
    }
    .fichas-pagination .page-link:hover {
        background-color: #e8f8e8;
        border-color: var(--ecp-green);
        color: var(--ecp-green-hover);
    }
    .fichas-pagination .page-item.active .page-link {
        background-color: var(--ecp-green);
        border-color: var(--ecp-green);
        color: #fff;
    }
    .fichas-pagination .page-item.disabled .page-link {
        color: #9ab5a1;
        background-color: #f7fbf8;
        border-color: #dceee0;
    }

    .fichas-search-icon {
        color: var(--ecp-green);
    }
    .btn-search-ficha {
        background-color: var(--ecp-green);
        border-color: var(--ecp-green);
        color: #fff;
    }
    .btn-search-ficha:hover,
    .btn-search-ficha:focus {
        background-color: var(--ecp-green-hover);
        border-color: var(--ecp-green-hover);
        color: #fff;
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-3 py-3">

    <!-- Breadcrumb & Cabecera -->
    <div class="fichas-hero d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="hero-eyebrow mb-2">
                <i class="fas fa-id-card-clip me-2"></i>Control ECP · Gestión académica
            </div>
            <h2 class="h3 fw-bold mb-0" style="font-family: 'Outfit', sans-serif;">
                Gestión de Fichas de Formación
            </h2>
            <p class="hero-subtitle small mb-0">Consulta y administra las fichas de formación registradas en SICEFA.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 hero-actions">
            <button type="button" class="btn btn-outline-secondary rounded-pill px-3 btn-sm" data-bs-toggle="modal" data-bs-target="#modalConfigApiFichas">
                <i class="fas fa-sliders me-1"></i> Configurar API Fichas
            </button>
            <button type="button" class="btn btn-outline-secondary rounded-pill px-3 btn-sm" onclick="probarConexionApiFichas()">
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

    <!-- Tarjetas de Estadísticas (KPIs) -->
    <div class="row g-3 mb-4">
        <!-- KPI 1: Total Fichas -->
        <div class="col-6 col-md-3">
            <div class="card stat-card-ficha stat-card-ficha-total shadow-sm h-100">
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
            <div class="card stat-card-ficha stat-card-ficha-active shadow-sm h-100">
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
            <div class="card stat-card-ficha stat-card-ficha-apprentices shadow-sm h-100">
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
            <div class="card stat-card-ficha stat-card-ficha-programs shadow-sm h-100">
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

    <!-- Barra de Filtros y Búsqueda -->
    <div class="card border-0 shadow-sm mb-4 fichas-filter-card">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center justify-content-between">
                <!-- Filtro por Estado -->
                <div class="col-lg-5">
                    <form method="GET" action="{{ route('controlecp.administrador.fichas') }}" class="d-flex align-items-center gap-2">
                        @if(!empty($searchTerm))
                            <input type="hidden" name="search" value="{{ $searchTerm }}">
                        @endif
                        <label for="filtroEstadoFicha" class="small text-muted fw-semibold text-nowrap mb-0">
                            <i class="fas fa-filter me-1"></i>Estados de las fichas:
                        </label>
                        <select name="estado" id="filtroEstadoFicha" class="form-select form-select-sm fichas-status-select"
                                onchange="this.form.submit()">
                            <option value="" {{ empty($statusFilter) ? 'selected' : '' }}>Todos ({{ $stats['total'] }})</option>
                            <option value="activo" {{ $statusFilter === 'activo' ? 'selected' : '' }}>Activos ({{ $stats['activas'] }})</option>
                            <option value="inactivo" {{ $statusFilter === 'inactivo' ? 'selected' : '' }}>Inactivos ({{ $stats['inactivas'] }})</option>
                        </select>
                    </form>
                </div>

                <!-- Buscador de Fichas -->
                <div class="col-lg-7">
                    <form method="GET" action="{{ route('controlecp.administrador.fichas') }}" class="d-flex gap-2">
                        @if($statusFilter)
                            <input type="hidden" name="estado" value="{{ $statusFilter }}">
                        @endif
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill text-muted">
                                <i class="fas fa-magnifying-glass fichas-search-icon"></i>
                            </span>
                            <input type="text" name="search" id="inputSearchFicha" class="form-control border-start-0 ps-0"
                                   placeholder="Buscar por número de ficha o nombre del programa académico..."
                                   value="{{ $searchTerm }}">
                            @if(!empty($searchTerm))
                                <a href="{{ route('controlecp.administrador.fichas', ['estado' => $statusFilter]) }}" class="input-group-text bg-white text-muted text-decoration-none">
                                    <i class="fas fa-xmark"></i>
                                </a>
                            @endif
                            <button class="btn btn-search-ficha rounded-end-pill px-3" type="submit">Buscar</button>
                        </div>
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
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light rounded-pill px-2" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-vertical text-muted"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="border-radius: 12px; font-size: 0.85rem;">
                                                {{-- HU2: Ver detalle --}}
                                                <li>
                                                    <a class="dropdown-item py-2"
                                                       href="{{ route('controlecp.administrador.fichas.show', $f->id) }}">
                                                        <i class="fas fa-eye me-2 text-primary"></i> Ver Detalle
                                                    </a>
                                                </li>
                                                {{-- HU2: Ver aprendices (modal rápido) --}}
                                                <li>
                                                    <button type="button" class="dropdown-item py-2"
                                                            onclick="cargarAprendicesFicha({{ $f->id }})">
                                                        <i class="fas fa-users-viewfinder me-2 text-success"></i> Nómina de Aprendices
                                                    </button>
                                                </li>
                                                {{-- HU4: Sincronizar aprendices de esta ficha --}}
                                                <li>
                                                    <button type="button" class="dropdown-item py-2"
                                                            onclick="sincronizarAprendicesFicha({{ $f->id }}, '{{ $f->code }}')">
                                                        <i class="fas fa-arrows-rotate me-2 text-info"></i> Sincronizar Aprendices
                                                    </button>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                {{-- HU5: No hay opción de crear/editar manualmente --}}
                                                {{-- HU6 / HU7: Desactivar / Activar ficha --}}
                                                @if($isActive)
                                                    <li>
                                                        <button class="dropdown-item py-2 text-danger" type="button"
                                                                onclick="confirmarCambioEstadoFicha({{ $f->id }}, 'desactivar', '{{ $f->code }}')">
                                                            <i class="fas fa-ban me-2"></i> Desactivar Ficha
                                                        </button>
                                                    </li>
                                                @else
                                                    <li>
                                                        <button class="dropdown-item py-2 text-success" type="button"
                                                                onclick="confirmarCambioEstadoFicha({{ $f->id }}, 'activar', '{{ $f->code }}')">
                                                            <i class="fas fa-circle-check me-2"></i> Activar Ficha
                                                        </button>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="fichas-pagination">
                    {{ $fichas->onEachSide(1)->links('pagination::bootstrap-5') }}
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

<!-- Modal: Confirmación Activar / Desactivar Ficha (HU6 / HU7 / HU8) -->
<div class="modal fade" id="modalCambioEstadoFicha" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4 text-center justify-content-center position-relative">
                <button type="button" class="btn-close position-absolute end-0 top-0 mt-3 me-3" data-bs-dismiss="modal"></button>
                <div id="fichaEstadoModalIcon" class="rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                     style="width: 65px; height: 65px; font-size: 1.8rem;">
                    <i class="fas fa-ban"></i>
                </div>
            </div>
            <div class="modal-body p-4 text-center">
                <h5 class="fw-bold mb-2" id="fichaEstadoModalTitle" style="color: var(--ecp-navy);">Desactivar Ficha</h5>
                <p class="text-muted small mb-4" id="fichaEstadoModalDesc"></p>
                <div class="d-flex justify-content-center gap-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn rounded-pill px-4 text-white" id="btnConfirmarEstadoFicha" onclick="ejecutarCambioEstadoFicha()">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Sincronizar Aprendices de una Ficha (HU4 / HU8 / HU9) -->
<div class="modal fade" id="modalSyncAprendices" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4 text-center justify-content-center position-relative">
                <button type="button" class="btn-close position-absolute end-0 top-0 mt-3 me-3" data-bs-dismiss="modal"></button>
                <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                     style="width: 65px; height: 65px; background: #ebf5fb; color: #2980b9; font-size: 1.8rem;">
                    <i class="fas fa-arrows-rotate" id="syncAprendicezIcon"></i>
                </div>
            </div>
            <div class="modal-body p-4 text-center">
                <h5 class="fw-bold mb-1" style="color: var(--ecp-navy);">Sincronizar Aprendices</h5>
                <p class="text-muted small mb-3">Ficha: <strong id="syncAprendicesCodigoLabel">--</strong></p>
                <p class="text-muted small mb-3">Se obtendrá la nómina desde SICEFA, se agregarán los aprendices nuevos y se actualizarán los existentes por número de documento, sin duplicar.</p>

                <div id="syncAprendicesProgress" class="d-none text-start mb-3">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span id="syncAprendicesLabel">Consultando SICEFA...</span>
                        <span id="syncAprendicesPct">0%</span>
                    </div>
                    <div class="progress" style="height: 8px; border-radius: 8px;">
                        <div id="syncAprendicesBar" class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width: 0%;"></div>
                    </div>
                </div>

                <div id="syncAprendicesResult" class="d-none small text-start p-3 rounded-3 border mb-3"></div>

                <div class="d-flex justify-content-center gap-3" id="syncAprendicesBtns">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn rounded-pill px-4 text-white" id="btnEjecutarSyncAprendices"
                            style="background-color: #2980b9;" onclick="ejecutarSyncAprendices()">
                        <i class="fas fa-arrows-rotate me-1"></i> Sincronizar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

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
                <button type="button" class="btn btn-outline-info rounded-pill px-4 btn-sm" id="btnSyncAprendicesDesdeModal">
                    <i class="fas fa-arrows-rotate me-1"></i> Sincronizar Aprendices
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
    const fichasCsrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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
                    'X-CSRF-TOKEN': fichasCsrfToken,
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
                    'X-CSRF-TOKEN': fichasCsrfToken,
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
                    'X-CSRF-TOKEN': fichasCsrfToken,
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
                    'X-CSRF-TOKEN': fichasCsrfToken,
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

    // ── 4A. Confirmar cambio de estado de ficha (HU6 / HU7 / HU8) ──────────────────
    let _fichaEstadoId   = null;
    let _fichaEstadoAccion = null;

    function confirmarCambioEstadoFicha(fichaId, accion, codigo) {
        _fichaEstadoId    = fichaId;
        _fichaEstadoAccion = accion;

        const icon  = document.getElementById('fichaEstadoModalIcon');
        const title = document.getElementById('fichaEstadoModalTitle');
        const desc  = document.getElementById('fichaEstadoModalDesc');
        const btn   = document.getElementById('btnConfirmarEstadoFicha');

        if (accion === 'desactivar') {
            icon.style.background = '#fdecea';
            icon.style.color      = '#c0392b';
            icon.innerHTML        = '<i class="fas fa-ban"></i>';
            title.innerText       = 'Desactivar Ficha';
            desc.innerText        = `¿Desactivar la ficha ${codigo}? Se retirará de las fichas activas. No se puede desactivar si tiene aprendices EN FORMACIÓN.`;
            btn.style.background  = '#dc3545';
            btn.style.borderColor = '#dc3545';
        } else {
            icon.style.background = '#e8f8e8';
            icon.style.color      = '#1e7000';
            icon.innerHTML        = '<i class="fas fa-circle-check"></i>';
            title.innerText       = 'Activar Ficha';
            desc.innerText        = `¿Reactivar la ficha ${codigo}? Volverá a aparecer entre las fichas activas.`;
            btn.style.background  = '#39A900';
            btn.style.borderColor = '#39A900';
        }

        new bootstrap.Modal(document.getElementById('modalCambioEstadoFicha')).show();
    }

    async function ejecutarCambioEstadoFicha() {
        const accion = _fichaEstadoAccion === 'desactivar' ? 'desactivar' : 'activar';
        const url    = "{{ url('/control-ecp/administrador/fichas') }}/" + _fichaEstadoId + '/' + accion;

        try {
            const resp = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': fichasCsrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({})
            });
            const data = await resp.json();

            bootstrap.Modal.getInstance(document.getElementById('modalCambioEstadoFicha')).hide();

            if (data.success) {
                // HU8 — Confirmación de operación exitosa
                mostrarAlertaFichas(data.message, 'success');
                setTimeout(() => { window.location.reload(); }, 1000);
            } else {
                // HU8 / HU9 — Mensaje de error claro
                mostrarAlertaFichas(data.message, 'warning');
            }
        } catch (e) {
            // HU9 — Error de conexión
            mostrarAlertaFichas('Error al cambiar el estado de la ficha: ' + e.message, 'danger');
        }
    }

    // ── 4B. Sincronizar aprendices de una ficha específica (HU4 / HU8 / HU9) ────────
    let _syncAprendicesFichaId = null;

    function sincronizarAprendicesFicha(fichaId, codigo) {
        _syncAprendicesFichaId = fichaId;
        document.getElementById('syncAprendicesCodigoLabel').innerText = codigo;

        // Reset modal
        document.getElementById('syncAprendicesProgress').classList.add('d-none');
        document.getElementById('syncAprendicesResult').classList.add('d-none');
        document.getElementById('syncAprendicesBtns').classList.remove('d-none');
        document.getElementById('btnEjecutarSyncAprendices').disabled = false;
        document.getElementById('syncAprendicezIcon').className = 'fas fa-arrows-rotate';

        new bootstrap.Modal(document.getElementById('modalSyncAprendices')).show();
    }

    async function ejecutarSyncAprendices() {
        const progressDiv = document.getElementById('syncAprendicesProgress');
        const progressBar = document.getElementById('syncAprendicesBar');
        const progressLbl = document.getElementById('syncAprendicesLabel');
        const progressPct = document.getElementById('syncAprendicesPct');
        const resultDiv   = document.getElementById('syncAprendicesResult');
        const btns        = document.getElementById('syncAprendicesBtns');
        const icon        = document.getElementById('syncAprendicezIcon');
        const btn         = document.getElementById('btnEjecutarSyncAprendices');

        progressDiv.classList.remove('d-none');
        resultDiv.classList.add('d-none');
        btns.classList.add('d-none');
        btn.disabled = true;
        icon.className = 'fas fa-arrows-rotate fa-spin';

        let pct = 10;
        progressBar.style.width = pct + '%';
        progressPct.innerText   = pct + '%';
        progressLbl.innerText   = 'Consultando SICEFA...';

        const timer = setInterval(() => {
            if (pct < 80) {
                pct += 15;
                progressBar.style.width = pct + '%';
                progressPct.innerText   = pct + '%';
                if (pct === 40) progressLbl.innerText = 'Validando documentos...';
                if (pct === 70) progressLbl.innerText = 'Guardando aprendices...';
            }
        }, 350);

        try {
            const url = "{{ url('/control-ecp/administrador/fichas') }}/" + _syncAprendicesFichaId + "/sync-aprendices";
            const resp = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': fichasCsrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({})
            });

            clearInterval(timer);
            const data = await resp.json();

            progressBar.style.width = '100%';
            progressPct.innerText   = '100%';
            progressLbl.innerText   = data.success ? '¡Completado!' : 'Error';

            resultDiv.classList.remove('d-none');
            btns.classList.remove('d-none');
            icon.className = data.success ? 'fas fa-circle-check' : 'fas fa-triangle-exclamation';

            if (data.success) {
                // HU8 — Confirmación de operación
                progressBar.classList.remove('bg-info');
                progressBar.classList.add('bg-success');
                resultDiv.className = 'd-block small text-start p-3 rounded-3 border mb-3 bg-success-subtle border-success-subtle';
                resultDiv.innerHTML = `
                    <div class="fw-bold text-success mb-1"><i class="fas fa-circle-check me-1"></i> ${data.message}</div>
                    <div class="text-muted">Nuevos: <strong>${data.created}</strong> &bull; Actualizados: <strong>${data.updated}</strong> &bull; Total: <strong>${data.total}</strong></div>
                `;
                setTimeout(() => { window.location.reload(); }, 2000);
            } else {
                // HU9 — Error, información conservada
                progressBar.classList.remove('bg-info');
                progressBar.classList.add('bg-danger');
                resultDiv.className = 'd-block small text-start p-3 rounded-3 border mb-3 bg-danger-subtle border-danger-subtle';
                resultDiv.innerHTML = `<div class="fw-bold text-danger"><i class="fas fa-triangle-exclamation me-1"></i> ${data.message}</div>`;
            }

        } catch (e) {
            clearInterval(timer);
            // HU9 — Error de conexión con SICEFA
            progressBar.classList.add('bg-danger');
            progressLbl.innerText = 'Sin conexión con SICEFA';
            resultDiv.classList.remove('d-none');
            btns.classList.remove('d-none');
            resultDiv.className = 'd-block small text-start p-3 rounded-3 border mb-3 bg-danger-subtle border-danger-subtle';
            resultDiv.innerHTML = `<div class="fw-bold text-danger"><i class="fas fa-wifi me-1"></i> No fue posible consultar SICEFA: ${e.message}. La información existente se conserva sin cambios.</div>`;
        }
    }

    // ── 5. Cargar Aprendices de una Ficha ─────────────────────────────────────────────
    let _aprendicesFichaId = null;

    async function cargarAprendicesFicha(courseId) {
        _aprendicesFichaId = courseId;

        // Configurar botón de sync en el footer del modal
        const btnSync = document.getElementById('btnSyncAprendicesDesdeModal');
        if (btnSync) {
            btnSync.onclick = () => {
                bootstrap.Modal.getInstance(document.getElementById('modalAprendicesFicha')).hide();
                // Buscar el código de la ficha en el subtítulo del modal
                const subTitle = document.getElementById('modalFichaSubTitle').innerText;
                const codigoMatch = subTitle.match(/Ficha: (\S+)/);
                const codigo = codigoMatch ? codigoMatch[1] : courseId;
                sincronizarAprendicesFicha(courseId, codigo);
            };
        }
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

    // ── 6. Utilidades (Alertas) ─────────────────────────────────────────────────────
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

</script>
@endpush
@endsection
