@extends('controlecp::components.layouts.master')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Listado de Elementos</h2>

        <a href="{{ route('controlecp.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Nuevo Registro
        </a>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($elementos as $item)

                        <tr>
                            <td>
                                <strong>{{ $item->codigo }}</strong>
                            </td>

                            <td>
                                {{ $item->nombre }}
                            </td>

                            <td>
                                <span class="badge bg-{{ $item->estado == 'Activo' ? 'success' : 'secondary' }}">
                                    {{ $item->estado }}
                                </span>
                            </td>

                            <td class="text-end">

                                <a href="{{ route('controlecp.edit', $item->id) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <form action="{{ route('controlecp.destroy', $item->id) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('¿Eliminar registro?');">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                </form>

                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                No hay registros disponibles.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>
    </div>

@endsection