@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .btn-action-cat {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 0.85rem;
        transition: all 0.15s ease;
    }
    .btn-action-cat:hover {
        transform: scale(1.05);
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">

    <!-- Breadcrumb & Cabecera (Mockup Image 1 Row 1 Right) -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.administrador.inicio') }}" class="text-decoration-none" style="color: #39A900;">
                            <i class="fas fa-house me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.administrador.inventario') }}" class="text-decoration-none" style="color: #39A900;">
                            Inventario
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page" style="color: #001A29; font-weight: 600;">
                        Gestión de categorías
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: #001A29; font-family: 'Outfit', sans-serif;">
                Gestión de categorías
            </h2>
            <p class="text-muted small mb-0">Administra las categorías de recursos conservando los recursos asociados</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('controlecp.administrador.inventario') }}" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver al inventario
            </a>
            <button type="button" class="btn text-white rounded-pill px-3 shadow-sm btn-sm" style="background-color: #39A900; border-color: #39A900;" data-bs-toggle="modal" data-bs-target="#modalCrearCategoria">
                <i class="fas fa-plus me-1"></i> Nueva categoría
            </button>
        </div>
    </div>

    <!-- Alertas Flash -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert" style="background-color: #f0fbf4; color: #001A29;">
            <i class="fas fa-check-circle me-2" style="color: #39A900;"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <ul class="mb-0 small ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Tabla de Categorías (Exacta de Mockup Image 1) -->
    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color: #F8FAFC; color: #001A29;" class="small fw-bold border-bottom">
                        <tr>
                            <th class="ps-4 py-3">Categoría</th>
                            <th class="py-3 text-center">Recursos</th>
                            <th class="py-3 text-center">Estado</th>
                            <th class="text-end pe-4 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($categorias as $cat)
                            <tr>
                                <!-- Columna 1: Nombre de la Categoría -->
                                <td class="ps-4">
                                    <div class="fw-bold text-dark fs-6">{{ $cat->nombre }}</div>
                                    <small class="text-muted" style="font-size: 0.75rem;">Creada el {{ $cat->created_at ? $cat->created_at->format('d/m/Y') : 'N/A' }}</small>
                                </td>

                                <!-- Columna 2: Conteo de Recursos Asociados -->
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border px-2 py-1 small">
                                        {{ $cat->inventarios_count }} recurso(s)
                                    </span>
                                </td>

                                <!-- Columna 3: Estado (Pills Activa / Inactiva como en el Mockup) -->
                                <td class="text-center">
                                    @if($cat->estado === 'Activo')
                                        <span class="badge rounded-pill px-3 py-1 border small" style="background-color: #DEF7EC; color: #03543F; border-color: #BCF0DA !important;">
                                            Activa
                                        </span>
                                    @else
                                        <span class="badge rounded-pill px-3 py-1 border small" style="background-color: #FEE2E2; color: #991B1B; border-color: #F87171 !important;">
                                            Inactiva
                                        </span>
                                    @endif
                                </td>

                                <!-- Columna 4: Acciones (Lapiz editar y botón power toggle) -->
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1">
                                        <!-- Editar Categoría -->
                                        <button type="button" class="btn btn-outline-secondary btn-action-cat" title="Editar categoría" onclick="editarCategoria({{ $cat->id_categoria_inventario }}, '{{ addslashes($cat->nombre) }}', '{{ $cat->estado }}')">
                                            <i class="fas fa-pen text-primary"></i>
                                        </button>

                                        <!-- Activar / Desactivar (Toggle) -->
                                        <form action="{{ route('controlecp.administrador.inventario.categorias.toggle', $cat->id_categoria_inventario) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            @if($cat->estado === 'Activo')
                                                <button type="submit" class="btn btn-outline-secondary btn-action-cat" title="Desactivar categoría (Conservará los recursos)">
                                                    <i class="fas fa-power-off text-danger"></i>
                                                </button>
                                            @else
                                                <button type="submit" class="btn btn-outline-secondary btn-action-cat" title="Activar categoría">
                                                    <i class="fas fa-check text-success"></i>
                                                </button>
                                            @endif
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fas fa-tags fa-3x mb-2 text-secondary opacity-50"></i>
                                    <p class="mb-0 fw-semibold">No hay categorías registradas.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Crear Categoría -->
<div class="modal fade" id="modalCrearCategoria" tabindex="-1" aria-labelledby="modalCrearCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form action="{{ route('controlecp.administrador.inventario.categorias.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalCrearCategoriaLabel" style="color: #001A29;">
                        Nueva categoría
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="nombreCat" class="form-label small fw-semibold text-dark">Nombre de la categoría <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="nombreCat" class="form-control rounded-3" placeholder="Ej: Tecnología, Mobiliario, Salud, Logística..." required maxlength="100">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white rounded-pill px-4 shadow-sm" style="background-color: #39A900; border-color: #39A900;">
                        Guardar categoría
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Editar Categoría -->
<div class="modal fade" id="modalEditarCategoria" tabindex="-1" aria-labelledby="modalEditarCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form id="formEditarCategoria" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalEditarCategoriaLabel" style="color: #001A29;">
                        Editar categoría
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="editNombreCat" class="form-label small fw-semibold text-dark">Nombre de la categoría <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="editNombreCat" class="form-control rounded-3" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="editEstadoCat" class="form-label small fw-semibold text-dark">Estado <span class="text-danger">*</span></label>
                        <select name="estado" id="editEstadoCat" class="form-select rounded-3" required>
                            <option value="Activo">Activa</option>
                            <option value="Inactivo">Inactiva</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white rounded-pill px-4 shadow-sm" style="background-color: #39A900; border-color: #39A900;">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function editarCategoria(id, nombre, estado) {
        document.getElementById('editNombreCat').value = nombre;
        document.getElementById('editEstadoCat').value = estado;
        document.getElementById('formEditarCategoria').action = `/control-ecp/administrador/inventario/categorias/${id}`;
        new bootstrap.Modal(document.getElementById('modalEditarCategoria')).show();
    }
</script>
@endpush
@endsection
