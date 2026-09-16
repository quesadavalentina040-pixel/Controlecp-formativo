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
                        Inventario
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: #001A29; font-family: 'Outfit', sans-serif;">
                <i class="fas fa-boxes-stacked me-2" style="color: #39A900;"></i>Gestión de Inventario
            </h2>
            <p class="text-muted small mb-0">Control ECP &bull; Catálogo y control de materiales pedagógicos y suministros</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('controlecp.administrador.inventario.create') }}" class="btn text-white rounded-pill px-3 shadow-sm" style="background-color: #2E7D32; border-color: #2E7D32;">
                <i class="fas fa-plus me-1"></i> Registrar Material
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
                        <i class="fas fa-box fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Materiales</div>
                        <div class="h4 fw-bold mb-0" style="color: #001A29;">{{ $totalMateriales ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; background: #F8F9FA;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 48px; height: 48px; background-color: #0288D1;">
                        <i class="fas fa-cubes fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Unidades</div>
                        <div class="h4 fw-bold mb-0" style="color: #001A29;">{{ $totalUnidades ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; background: #FFF8E1;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 48px; height: 48px; background-color: #F57F17;">
                        <i class="fas fa-triangle-exclamation fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Bajo Stock</div>
                        <div class="h4 fw-bold mb-0 text-warning-emphasis">{{ $bajoStock ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; background: #FFEBEE;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 48px; height: 48px; background-color: #D32F2F;">
                        <i class="fas fa-ban fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Agotados</div>
                        <div class="h4 fw-bold mb-0 text-danger">{{ $agotados ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Buscador y Filtros -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-body p-3">
            <form action="{{ route('controlecp.administrador.inventario') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-6 col-lg-7">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3">
                            <i class="fas fa-magnifying-glass text-muted"></i>
                        </span>
                        <input type="text" name="buscar" value="{{ request('buscar') }}" class="form-control border-start-0 rounded-end-pill" placeholder="Buscar por código o nombre del material...">
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <select name="categoria" class="form-select rounded-pill" onchange="this.form.submit()">
                        <option value="">Todas las categorías</option>
                        @foreach (\Modules\ControlECP\Entities\Material::CATEGORIAS as $cat)
                            <option value="{{ $cat }}" {{ request('categoria') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-lg-2 d-grid">
                    <button type="submit" class="btn btn-outline-success rounded-pill">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Materiales -->
    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color: #E8F5E9; color: #1B5E20;" class="small fw-bold">
                        <tr>
                            <th class="ps-4 py-3">Código</th>
                            <th class="py-3">Material</th>
                            <th class="py-3">Categoría</th>
                            <th class="py-3 text-center">Cantidad</th>
                            <th class="py-3 text-center">Estado</th>
                            <th class="py-3">Ubicación</th>
                            <th class="text-end pe-4 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($materiales as $material)
                            <tr>
                                <td class="ps-4 fw-bold font-monospace text-muted small">{{ $material->codigo }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if ($material->imagen)
                                            <img src="{{ asset('storage/' . $material->imagen) }}" alt="{{ $material->nombre }}" class="rounded-3 shadow-sm" style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-muted" style="width: 40px; height: 40px;">
                                                <i class="fas fa-box"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-bold text-dark">{{ $material->nombre }}</div>
                                            <div class="small text-muted text-truncate" style="max-width: 220px;">{{ $material->descripcion ?? 'Sin descripción' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $material->categoria }}</span></td>
                                <td class="text-center">
                                    <span class="fw-bold fs-6">{{ $material->cantidad }}</span>
                                    <span class="small text-muted d-block">{{ $material->unidad }}</span>
                                </td>
                                <td class="text-center">
                                    @if ($material->estado === 'Disponible')
                                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3">Disponible</span>
                                    @elseif ($material->estado === 'Bajo stock')
                                        <span class="badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle px-3">Bajo stock</span>
                                    @else
                                        <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle px-3">Agotado</span>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $material->ubicacion ?? 'N/A' }}</td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('controlecp.administrador.inventario.edit', $material->id) }}" class="btn btn-sm btn-outline-primary rounded-circle" title="Editar">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <form action="{{ route('controlecp.administrador.inventario.destroy', $material->id) }}" method="POST" onsubmit="return confirm('¿Seguro de eliminar este material del inventario?');" class="d-inline">
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
                                    <i class="fas fa-boxes-stacked fa-3x mb-3 text-muted opacity-50"></i>
                                    <p class="mb-0 fw-semibold">No se encontraron materiales en el inventario.</p>
                                    <small class="text-muted">Prueba registrando un nuevo material con el botón superior.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if (isset($materiales) && method_exists($materiales, 'hasPages') && $materiales->hasPages())
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $materiales->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
