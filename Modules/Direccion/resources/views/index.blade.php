@extends('direccion::layouts.master')

@section('title', 'Gestión de Políticas y Directrices')

@section('content')
    <!-- Page Header Title -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 pb-2 border-bottom">
        <div>
            <span class="badge badge-sena-estrategico px-3 py-1 rounded-pill mb-2">
                <i class="fas fa-chess-king me-1"></i> Proceso Estratégico
            </span>
            <h2 class="fw-bold text-dark mb-0">Gestión de Políticas y Directrices</h2>
            <p class="text-muted fs-6 mb-0">Administración de decisiones corporativas, lineamientos y directivas de la gerencia en SENA Empresa.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('direccion.create') }}" class="btn btn-sena shadow-sm">
                <i class="fas fa-plus-circle me-1"></i> Nueva Política
            </a>
        </div>
    </div>

    <!-- Summary Metrics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card card-custom p-3 border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 fw-semibold text-uppercase">Total Políticas</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1">{{ $totalPoliticas }}</h3>
                    </div>
                    <div class="rounded-circle p-3 bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-folder-open fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card card-custom p-3 border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 fw-semibold text-uppercase">Políticas Activas</span>
                        <h3 class="fw-bold text-success mb-0 mt-1">{{ $activas }}</h3>
                    </div>
                    <div class="rounded-circle p-3 bg-success bg-opacity-10 text-success">
                        <i class="fas fa-check-circle fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card card-custom p-3 border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 fw-semibold text-uppercase">En Revisión</span>
                        <h3 class="fw-bold text-warning mb-0 mt-1">{{ $enRevision }}</h3>
                    </div>
                    <div class="rounded-circle p-3 bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-clock fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card card-custom p-3 border-start border-4 border-secondary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 fw-semibold text-uppercase">Inactivas</span>
                        <h3 class="fw-bold text-secondary mb-0 mt-1">{{ $inactivas }}</h3>
                    </div>
                    <div class="rounded-circle p-3 bg-secondary bg-opacity-10 text-secondary">
                        <i class="fas fa-archive fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="card card-custom p-4 mb-4">
        <form method="GET" action="{{ route('direccion.index') }}" class="row g-3 align-items-end">
            <div class="col-lg-5 col-md-12">
                <label class="form-label fs-7 fw-bold text-muted">Buscador</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="buscar" value="{{ request('buscar') }}" class="form-control border-start-0 bg-light" placeholder="Buscar por código, título, responsable...">
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <label class="form-label fs-7 fw-bold text-muted">Tipo de Política</label>
                <select name="tipo" class="form-select bg-light">
                    <option value="">Todos los tipos</option>
                    <option value="Estratégica" {{ request('tipo') == 'Estratégica' ? 'selected' : '' }}>Estratégica</option>
                    <option value="Calidad" {{ request('tipo') == 'Calidad' ? 'selected' : '' }}>Calidad</option>
                    <option value="Seguridad" {{ request('tipo') == 'Seguridad' ? 'selected' : '' }}>Seguridad</option>
                    <option value="Operativa" {{ request('tipo') == 'Operativa' ? 'selected' : '' }}>Operativa</option>
                </select>
            </div>

            <div class="col-lg-2 col-md-6">
                <label class="form-label fs-7 fw-bold text-muted">Estado</label>
                <select name="estado" class="form-select bg-light">
                    <option value="">Todos</option>
                    <option value="Activa" {{ request('estado') == 'Activa' ? 'selected' : '' }}>Activa</option>
                    <option value="En Revisión" {{ request('estado') == 'En Revisión' ? 'selected' : '' }}>En Revisión</option>
                    <option value="Inactiva" {{ request('estado') == 'Inactiva' ? 'selected' : '' }}>Inactiva</option>
                </select>
            </div>

            <div class="col-lg-2 col-md-12 d-flex gap-2">
                <button type="submit" class="btn btn-dark w-100 fw-semibold rounded-3">
                    <i class="fas fa-filter me-1"></i> Filtrar
                </button>
                @if(request()->hasAny(['buscar', 'tipo', 'estado']))
                    <a href="{{ route('direccion.index') }}" class="btn btn-outline-secondary rounded-3" title="Limpiar filtros">
                        <i class="fas fa-undo"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="card card-custom overflow-hidden shadow-sm">
        <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fas fa-list-check text-success me-2"></i> Listado de Políticas Institucionales
            </h5>
            <span class="fs-7 text-muted">Mostrando {{ $politicas->count() }} de {{ $politicas->total() }} registros</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 130px;">Código</th>
                        <th>Título y Descripción</th>
                        <th style="width: 140px;">Tipo</th>
                        <th style="width: 100px;">Vigencia</th>
                        <th>Responsable</th>
                        <th style="width: 120px;">Estado</th>
                        <th class="text-end pe-4" style="width: 130px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($politicas as $pol)
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-light text-dark border fw-bold font-monospace px-2 py-1">
                                    {{ $pol->codigo }}
                                </span>
                            </td>
                            <td>
                                <strong class="text-dark d-block fs-6 mb-1">{{ $pol->titulo }}</strong>
                                <p class="text-muted fs-7 mb-0 text-truncate" style="max-width: 480px;">{{ $pol->descripcion }}</p>
                            </td>
                            <td>
                                @if($pol->tipo == 'Estratégica')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1 rounded-pill"><i class="fas fa-chess-king me-1"></i> Estratégica</span>
                                @elseif($pol->tipo == 'Calidad')
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2 py-1 rounded-pill"><i class="fas fa-award me-1"></i> Calidad</span>
                                @elseif($pol->tipo == 'Seguridad')
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1 rounded-pill"><i class="fas fa-shield-alt me-1"></i> Seguridad</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary px-2 py-1 rounded-pill"><i class="fas fa-cogs me-1"></i> Operativa</span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold text-dark">{{ $pol->vigencia }}</span>
                            </td>
                            <td>
                                <small class="text-muted"><i class="fas fa-user-tie text-success me-1"></i> {{ $pol->responsable }}</small>
                            </td>
                            <td>
                                @if($pol->estado == 'Activa')
                                    <span class="badge bg-success text-white px-2 py-1 rounded-pill"><i class="fas fa-circle fs-8 me-1"></i> Activa</span>
                                @elseif($pol->estado == 'En Revisión')
                                    <span class="badge bg-warning text-dark px-2 py-1 rounded-pill"><i class="fas fa-clock fs-8 me-1"></i> En Revisión</span>
                                @else
                                    <span class="badge bg-secondary text-white px-2 py-1 rounded-pill"><i class="fas fa-ban fs-8 me-1"></i> Inactiva</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('direccion.edit', $pol->id) }}" class="btn btn-sm btn-outline-primary rounded-3" title="Editar Política">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('direccion.destroy', $pol->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar la política {{ $pol->codigo }}?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="Eliminar Política">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-folder-open display-4 mb-3 text-secondary opacity-50"></i>
                                    <h5>No se encontraron políticas</h5>
                                    <p class="fs-6 mb-3">No hay registros que coincidan con los criterios de búsqueda.</p>
                                    <a href="{{ route('direccion.create') }}" class="btn btn-sm btn-sena">
                                        <i class="fas fa-plus me-1"></i> Registrar Primera Política
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($politicas->hasPages())
            <div class="card-footer bg-white py-3 px-4 border-top">
                {{ $politicas->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection
