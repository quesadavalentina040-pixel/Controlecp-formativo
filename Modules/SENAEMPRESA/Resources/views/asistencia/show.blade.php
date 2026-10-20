@extends('senaempresa::components.layouts.master')

@section('content')

    {{-- Resumen de la sesión --}}
    <div class="row">
        <div class="col-12">
            <div class="card card-orange card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clipboard-check"></i>
                        {{ $asistencia->nombre }}
                        @if ($asistencia->estado == 'ACTIVA')
                            <span class="badge badge-success ml-2">ACTIVA</span>
                        @elseif ($asistencia->estado == 'CERRADA')
                            <span class="badge badge-secondary ml-2">CERRADA</span>
                        @else
                            <span class="badge badge-danger ml-2">CANCELADA</span>
                        @endif
                        @if ($asistencia->is_synced)
                            <span class="badge badge-info ml-2"><i class="fas fa-cloud"></i> SICEFA ID: {{ $asistencia->sicefa_id }}</span>
                        @endif
                    </h3>
                    <div class="card-tools">
                        @if ($asistencia->estado == 'ACTIVA')
                            <a href="{{ route('senaempresa.asistencia.send-sicefa', $asistencia) }}"
                               class="btn btn-warning btn-sm" onclick="return confirm('¿Enviar a SICEFA?')">
                                <i class="fas fa-cloud-upload-alt"></i> Enviar a SICEFA
                            </a>
                            <form action="{{ route('senaempresa.asistencia.close', $asistencia) }}" method="POST" style="display:inline"
                                  onsubmit="return confirm('¿Cerrar la sesión? No se podrán agregar más aprendices.')">
                                @csrf
                                <button type="submit" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-door-closed"></i> Cerrar
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-calendar"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Fecha</span>
                                    <span class="info-box-number">{{ $asistencia->fecha ? $asistencia->fecha->format('d/m/Y') : '--' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-user-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Asistieron</span>
                                    <span class="info-box-number">{{ $asistencia->total_asistio }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-danger">
                                <span class="info-box-icon"><i class="fas fa-user-times"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Faltaron</span>
                                    <span class="info-box-number">{{ $asistencia->total_falto }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Retardo / Excusa</span>
                                    <span class="info-box-number">{{ $asistencia->total_retardo + $asistencia->total_excusa }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($asistencia->descripcion)
                        <p class="text-muted"><strong>Descripción:</strong> {{ $asistencia->descripcion }}</p>
                    @endif
                    <p><strong>Tipo:</strong> {{ $asistencia->tipo }}
                       <strong class="ml-3">Hora:</strong> {{ $asistencia->hora_inicio ?? '--' }} - {{ $asistencia->hora_fin ?? '--' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Aprendices registrados --}}
    <div class="row mt-2">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-graduate"></i> Aprendices Registrados ({{ $asistencia->total_apprentices }})</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th>Documento</th>
                                    <th>Nombre</th>
                                    <th>Curso</th>
                                    <th>Estado</th>
                                    <th>Observación</th>
                                    @if ($asistencia->estado == 'ACTIVA')
                                        <th width="10%">Cambiar</th>
                                        <th width="5%"></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($asistencia->apprentices as $apprentice)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $apprentice->person?->document_type }} {{ $apprentice->person?->document_number }}</td>
                                        <td>{{ $apprentice->person?->full_name }}</td>
                                        <td>{{ $apprentice->course?->code }}</td>
                                        <td>
                                            @php
                                                $estado = $apprentice->pivot->asistencia;
                                                $badgeClass = [
                                                    'ASISTIO' => 'success',
                                                    'FALTO'   => 'danger',
                                                    'EXCUSA'  => 'info',
                                                    'RETARDO' => 'warning',
                                                ][$estado] ?? 'secondary';
                                            @endphp
                                            <span class="badge badge-{{ $badgeClass }}">{{ $estado }}</span>
                                        </td>
                                        <td>{{ $apprentice->pivot->observacion ?? '--' }}</td>
                                        @if ($asistencia->estado == 'ACTIVA')
                                            <td>
                                                <select class="form-control form-control-sm cambio-asistencia"
                                                        data-apprentice-id="{{ $apprentice->id }}">
                                                    @foreach ($attendanceTypes as $value => $label)
                                                        <option value="{{ $value }}" {{ $apprentice->pivot->asistencia == $value ? 'selected' : '' }}>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <form action="{{ route('senaempresa.asistencia.apprentices.remove', $asistencia) }}" method="POST">
                                                    @csrf @method('DELETE')
                                                    <input type="hidden" name="apprentice_id" value="{{ $apprentice->id }}">
                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                            onclick="return confirm('¿Remover este aprendiz?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $asistencia->estado == 'ACTIVA' ? 8 : 6 }}" class="text-center text-muted py-3">
                                            No hay aprendices registrados en esta sesión.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Agregar aprendices --}}
    @if ($asistencia->estado == 'ACTIVA' && $availableApprentices->count() > 0)
        <div class="row mt-2">
            <div class="col-12">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-plus-circle"></i> Agregar Aprendices Disponibles</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('senaempresa.asistencia.apprentices.register', $asistencia) }}" method="POST">
                            @csrf
                            <div class="row mb-2">
                                <div class="col-md-3">
                                    <label>Estado de asistencia por defecto:</label>
                                    <select name="asistencia_default" class="form-control" required>
                                        @foreach ($attendanceTypes as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th width="5%"><input type="checkbox" id="select-all" checked></th>
                                            <th>Documento</th>
                                            <th>Nombre</th>
                                            <th>Curso</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($availableApprentices as $apprentice)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="apprentice_ids[]" value="{{ $apprentice->id }}" class="apprentice-check" checked>
                                                </td>
                                                <td>{{ $apprentice->person?->document_type }} {{ $apprentice->person?->document_number }}</td>
                                                <td>{{ $apprentice->person?->full_name }}</td>
                                                <td>{{ $apprentice->course?->code }}</td>
                                                <td>{{ $apprentice->apprentice_status }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-user-plus"></i> Registrar Aprendices Seleccionados
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection

@section('script')
    <script>
        // Seleccionar/deseleccionar todos
        $('#select-all').on('click', function() {
            $('.apprentice-check').prop('checked', this.checked);
        });

        // Cambiar estado de asistencia vía AJAX
        $('.cambio-asistencia').on('change', function() {
            const apprenticeId = $(this).data('apprentice-id');
            const asistenciaValue = $(this).val();

            $.ajax({
                url: '{{ route("senaempresa.asistencia.apprentices.update", $asistencia) }}',
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'PUT',
                    apprentice_id: apprenticeId,
                    asistencia: asistenciaValue
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    // Recargar para actualizar badges
                    setTimeout(() => location.reload(), 1000);
                },
                error: function() {
                    Swal.fire({ icon: 'error', title: 'Error al actualizar' });
                }
            });
        });
    </script>
@endsection
