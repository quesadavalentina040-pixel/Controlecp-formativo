@extends('senaempresa::components.layouts.master')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card card-orange card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-clipboard-check"></i> Sesiones de Asistencia</h3>
                    <div class="card-tools">
                        <a href="{{ route('senaempresa.asistencia.create') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Nueva Sesión
                        </a>
                    </div>
                </div>
                <div class="card-body">

                    {{-- Filtros --}}
                    <form method="GET" action="{{ route('senaempresa.asistencia.index') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" name="buscar" value="{{ request('buscar') }}"
                                       class="form-control" placeholder="Buscar por nombre...">
                            </div>
                            <div class="col-md-3">
                                <select name="estado" class="form-control">
                                    <option value="">Todos los estados</option>
                                    <option value="ACTIVA" {{ request('estado') == 'ACTIVA' ? 'selected' : '' }}>ACTIVA</option>
                                    <option value="CERRADA" {{ request('estado') == 'CERRADA' ? 'selected' : '' }}>CERRADA</option>
                                    <option value="CANCELADA" {{ request('estado') == 'CANCELADA' ? 'selected' : '' }}>CANCELADA</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="date" name="fecha" value="{{ request('fecha') }}" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> Filtrar</button>
                            </div>
                        </div>
                    </form>

                    {{-- Tabla --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">ID</th>
                                    <th>Nombre</th>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Aprendices</th>
                                    <th>SICEFA</th>
                                    <th>Estado</th>
                                    <th width="15%">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($asistencias as $a)
                                    <tr>
                                        <td>{{ $a->id }}</td>
                                        <td>{{ $a->nombre }}</td>
                                        <td>{{ $a->fecha ? $a->fecha->format('d/m/Y') : '--' }}</td>
                                        <td>
                                            <span class="badge badge-{{
                                                $a->tipo == 'PRESENCIAL' ? 'primary' :
                                                ($a->tipo == 'VIRTUAL' ? 'info' : 'warning')
                                                }}">{{ $a->tipo }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $a->total_apprentices }}</span>
                                        </td>
                                        <td>
                                            @if ($a->is_synced)
                                                <span class="badge badge-success"><i class="fas fa-check-circle"></i> Sincronizado</span>
                                            @else
                                                <span class="badge badge-warning"><i class="fas fa-clock"></i> Pendiente</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($a->estado == 'ACTIVA')
                                                <span class="badge badge-success">{{ $a->estado }}</span>
                                            @elseif ($a->estado == 'CERRADA')
                                                <span class="badge badge-secondary">{{ $a->estado }}</span>
                                            @else
                                                <span class="badge badge-danger">{{ $a->estado }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('senaempresa.asistencia.show', $a) }}"
                                               class="btn btn-info btn-sm" title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if ($a->estado == 'ACTIVA')
                                                <a href="{{ route('senaempresa.asistencia.send-sicefa', $a) }}"
                                                   class="btn btn-warning btn-sm" title="Enviar a SICEFA"
                                                   onclick="return confirm('¿Enviar esta sesión a SICEFA?')">
                                                    <i class="fas fa-cloud-upload-alt"></i>
                                                </a>
                                            @endif
                                            <form action="{{ route('senaempresa.asistencia.destroy', $a) }}" method="POST"
                                                  style="display:inline" onsubmit="return confirm('¿Eliminar esta sesión?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-3x mb-2 d-block"></i>
                                            No hay sesiones de asistencia registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginación --}}
                    <div class="d-flex justify-content-center">
                        {{ $asistencias->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection
