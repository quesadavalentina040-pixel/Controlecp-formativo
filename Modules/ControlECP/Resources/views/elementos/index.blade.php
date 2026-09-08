@extends('controlecp::components.layouts.master')

@section('content')

    <div class="container py-4">
        <!-- Navegación interna -->
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <div>
                <a href="{{ route('controlecp.index') }}" class="btn btn-outline-success btn-sm rounded-pill mb-2">
                    <i class="fas fa-arrow-left me-1"></i> Volver a Escuela Cultura de Paz
                </a>
                <h2 class="fw-bold text-success mb-0">
                    <i class="fas fa-layer-group me-2"></i>Gestión de Elementos
                </h2>
                <p class="text-muted mb-0 small">Administración y control de registros del módulo Control ECP</p>
            </div>

            <a href="{{ route('controlecp.create') }}" class="btn btn-sena-green rounded-pill px-4 shadow-sm">
                <i class="fas fa-plus me-1"></i> Nuevo Registro
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color: #f0f7f2; color: #00324D;">
                        <tr>
                            <th class="ps-4">Código</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($elementos as $item)
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-light text-dark border fw-bold">{{ $item->codigo }}</span>
                                </td>

                                <td class="fw-semibold">
                                    {{ $item->nombre }}
                                </td>

                                <td class="text-muted small">
                                    {{ Str::limit($item->descripcion ?? 'Sin descripción', 50) }}
                                </td>

                                <td>
                                    <span class="badge rounded-pill px-3 py-2 {{ $item->estado == 'Activo' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $item->estado }}
                                    </span>
                                </td>

                                <td class="text-end pe-4">
                                    <a href="{{ route('controlecp.edit', $item->id) }}"
                                       class="btn btn-sm btn-outline-primary rounded-circle me-1" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form action="{{ route('controlecp.destroy', $item->id) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('¿Está seguro de eliminar este registro?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-outline-danger rounded-circle" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x text-secondary mb-3 d-block"></i>
                                    <p class="mb-2 fw-semibold">No hay registros disponibles en este momento.</p>
                                    <a href="{{ route('controlecp.create') }}" class="btn btn-sm btn-success rounded-pill px-3">
                                        <i class="fas fa-plus me-1"></i> Crear primer registro
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($elementos, 'links'))
                <div class="card-footer bg-white border-0 py-3">
                    {{ $elementos->links() }}
                </div>
            @endif
        </div>
    </div>

@endsection
