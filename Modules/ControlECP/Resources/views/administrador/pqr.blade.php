@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">

    <!-- Breadcrumb & Cabecera -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.administrador.inicio') }}" class="text-decoration-none" style="color: #2E7D32;">
                            <i class="fas fa-house me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page" style="color: #1B5E20; font-weight: 600;">
                        PQR
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: #001A29; font-family: 'Outfit', sans-serif;">
                <i class="fas fa-comment-dots me-2" style="color: #39A900;"></i>Gestión de PQR
            </h2>
            <p class="text-muted small mb-0">Control ECP &bull; Peticiones, Quejas, Reclamos, Sugerencias y Felicitaciones</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('controlecp.administrador.pqr.create') }}" class="btn text-white rounded-pill px-3 shadow-sm" style="background-color: #2E7D32; border-color: #2E7D32;">
                <i class="fas fa-plus me-1"></i> Radicar PQR
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert" style="background-color: #E8F5E9; color: #1B5E20;">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Tarjetas de Estadísticas -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; background: #F8F9FA;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 48px; height: 48px; background-color: #2E7D32;">
                        <i class="fas fa-inbox fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Radicados</div>
                        <div class="h4 fw-bold mb-0" style="color: #001A29;">{{ $total ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; background: #FFF3E0;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 48px; height: 48px; background-color: #EF6C00;">
                        <i class="fas fa-clock fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Pendientes</div>
                        <div class="h4 fw-bold mb-0 text-warning-emphasis">{{ $pendientes ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; background: #E3F2FD;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 48px; height: 48px; background-color: #1976D2;">
                        <i class="fas fa-spinner fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">En Trámite</div>
                        <div class="h4 fw-bold mb-0 text-primary">{{ $enTramite ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; background: #E8F5E9;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 48px; height: 48px; background-color: #388E3C;">
                        <i class="fas fa-circle-check fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Resueltas</div>
                        <div class="h4 fw-bold mb-0 text-success">{{ $resueltas ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Buscador y Filtros -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-body p-3">
            <form action="{{ route('controlecp.administrador.pqr') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3">
                            <i class="fas fa-magnifying-glass text-muted"></i>
                        </span>
                        <input type="text" name="buscar" value="{{ request('buscar') }}" class="form-control border-start-0 rounded-end-pill" placeholder="Buscar por radicado, solicitante o asunto...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="tipo" class="form-select rounded-pill" onchange="this.form.submit()">
                        <option value="">Todos los tipos</option>
                        @foreach (\Modules\ControlECP\Entities\Pqr::TIPOS as $tipo)
                            <option value="{{ $tipo }}" {{ request('tipo') == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="estado" class="form-select rounded-pill" onchange="this.form.submit()">
                        <option value="">Todos los estados</option>
                        @foreach (\Modules\ControlECP\Entities\Pqr::ESTADOS as $est)
                            <option value="{{ $est }}" {{ request('estado') == $est ? 'selected' : '' }}>{{ $est }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-outline-success rounded-pill">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de PQR -->
    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color: #E8F5E9; color: #1B5E20;" class="small fw-bold">
                        <tr>
                            <th class="ps-4 py-3">Radicado</th>
                            <th class="py-3">Solicitante</th>
                            <th class="py-3">Tipo</th>
                            <th class="py-3">Asunto</th>
                            <th class="py-3 text-center">Prioridad</th>
                            <th class="py-3 text-center">Estado</th>
                            <th class="text-end pe-4 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($pqrs as $item)
                            <tr>
                                <td class="ps-4 fw-bold font-monospace text-success">{{ $item->radicado }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $item->nombre_solicitante }}</div>
                                    <div class="small text-muted">{{ $item->email }}</div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $item->tipo }}</span></td>
                                <td class="small text-dark" style="max-width: 250px;">
                                    <span class="d-inline-block text-truncate" style="max-width: 240px;">{{ $item->asunto }}</span>
                                </td>
                                <td class="text-center">
                                    @if ($item->prioridad === 'Alta')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">Alta</span>
                                    @elseif ($item->prioridad === 'Media')
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">Media</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">Baja</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($item->estado === 'Pendiente')
                                        <span class="badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle px-3">Pendiente</span>
                                    @elseif ($item->estado === 'En trámite')
                                        <span class="badge rounded-pill bg-info-subtle text-info border border-info-subtle px-3">En trámite</span>
                                    @elseif ($item->estado === 'Resuelto')
                                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3">Resuelto</span>
                                    @else
                                        <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle px-3">Cerrado</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('controlecp.administrador.pqr.show', $item->id) }}" class="btn btn-sm btn-outline-success rounded-circle" title="Ver detalle / Responder">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="{{ route('controlecp.administrador.pqr.destroy', $item->id) }}" method="POST" onsubmit="return confirm('¿Seguro de eliminar este radicado de PQR?');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Eliminar">
                                                <i class="fas fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-comment-slash fa-3x mb-3 text-muted opacity-50"></i>
                                    <p class="mb-0 fw-semibold">No se encontraron solicitudes PQR.</p>
                                    <small class="text-muted">Prueba radicando una nueva PQR con el botón superior.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if (isset($pqrs) && method_exists($pqrs, 'hasPages') && $pqrs->hasPages())
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $pqrs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
