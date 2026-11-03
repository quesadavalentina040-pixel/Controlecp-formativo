@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <h2 class="h3 fw-bold mb-1" style="color:#001A29;">
                <i class="fas fa-comments me-2" style="color:#39A900;"></i>Mis asesorías
            </h2>
            <p class="text-muted small mb-0">Solicita, consulta o cancela tus asesorías.</p>
        </div>
        <button class="btn text-white rounded-pill px-3" style="background:#39A900;" data-bs-toggle="modal" data-bs-target="#modalAsesoria">
            <i class="fas fa-plus me-1"></i>Solicitar asesoría
        </button>
    </div>

    @if(session('success') || session('error'))
        <div class="alert alert-{{ session('success') ? 'success' : 'danger' }} border-0 shadow-sm">
            {{ session('success') ?: session('error') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Categoría</th><th>Instructor</th><th>Fecha</th><th>Hora</th><th>Estado</th><th class="text-end">Acción</th></tr>
                </thead>
                <tbody>
                    @forelse($asesorias as $asesoria)
                        @php
                            $estado = $asesoria->estado;
                            $clase = $estado === 'Realizada' ? 'success' : ($estado === 'Cancelada' ? 'secondary' : 'warning');
                            $cambios = $asesoria->historial->whereIn('accion', ['reprogramar', 'cancelar']);
                        @endphp
                        <tr>
                            <td class="fw-semibold">{{ $asesoria->categoria?->nombre ?? 'Sin categoría' }}</td>
                            <td>{{ $asesoria->instructor?->full_name ?? 'Sin instructor' }}</td>
                            <td>{{ $asesoria->fecha?->format('d/m/Y') }}</td>
                            <td>{{ substr((string) $asesoria->hora_inicio, 0, 5) }} - {{ substr((string) $asesoria->hora_fin, 0, 5) }}</td>
                            <td>
                                <span class="badge bg-{{ $clase }}-subtle text-{{ $clase }} border rounded-pill">{{ $estado }}</span>
                                @if($cambios->isNotEmpty())
                                    <div class="small text-muted mt-1">Tiene cambios registrados</div>
                                @endif
                            </td>
                            <td class="text-end">
                                @if(in_array($estado, ['Solicitada', 'Programada'], true))
                                    <form method="POST" action="{{ route('controlecp.aprendiz.asesorias.cancelar', $asesoria->id_asesoria) }}" class="d-inline"
                                          onsubmit="return confirm('¿Deseas cancelar esta asesoría?');">
                                        @csrf
                                        <input type="hidden" name="motivo" value="Cancelada por solicitud del aprendiz.">
                                        <button class="btn btn-sm btn-outline-danger rounded-pill">Cancelar</button>
                                    </form>
                                @else
                                    <span class="text-muted small">Solo lectura</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">Aún no tienes asesorías registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAsesoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius:16px;">
            <form method="POST" action="{{ route('controlecp.aprendiz.asesorias.store') }}" id="formAsesoria">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Solicitar asesoría</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small">Selecciona categoría, fecha y horario para consultar instructores disponibles.</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Categoría *</label>
                            <select name="id_categoria_asesoria" id="categoriaAsesoria" class="form-select" required>
                                <option value="">Selecciona...</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->id_categoria_asesoria }}">{{ $categoria->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha *</label>
                            <input type="date" name="fecha" id="fechaAsesoria" min="{{ now()->format('Y-m-d') }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Hora inicial *</label>
                            <input type="time" name="hora_inicio" id="horaInicioAsesoria" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Hora final *</label>
                            <input type="time" name="hora_fin" id="horaFinAsesoria" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <button type="button" id="buscarInstructores" class="btn btn-outline-success rounded-pill">
                                <i class="fas fa-search me-1"></i>Consultar disponibilidad
                            </button>
                            <div id="mensajeDisponibilidad" class="small mt-2"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Instructor *</label>
                            <select name="id_usuario_instructor" id="instructorAsesoria" class="form-select" required disabled>
                                <option value="">Consulta primero la disponibilidad...</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Motivo *</label>
                            <textarea name="motivo" rows="4" class="form-control" minlength="5" maxlength="5000" required>{{ old('motivo') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn text-white rounded-pill" style="background:#39A900;">Confirmar reserva</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('buscarInstructores').addEventListener('click', async () => {
        const categoria = document.getElementById('categoriaAsesoria').value;
        const fecha = document.getElementById('fechaAsesoria').value;
        const inicio = document.getElementById('horaInicioAsesoria').value;
        const fin = document.getElementById('horaFinAsesoria').value;
        const mensaje = document.getElementById('mensajeDisponibilidad');
        const select = document.getElementById('instructorAsesoria');
        if (!categoria || !fecha || !inicio || !fin || inicio >= fin) {
            mensaje.className = 'small mt-2 text-danger';
            mensaje.textContent = 'Completa categoría, fecha y un horario válido.';
            return;
        }
        mensaje.className = 'small mt-2 text-muted';
        mensaje.textContent = 'Consultando disponibilidad...';
        select.disabled = true;
        select.innerHTML = '<option>Consultando...</option>';
        const params = new URLSearchParams({ id_categoria_asesoria: categoria, fecha, hora_inicio: inicio, hora_fin: fin });
        const response = await fetch('{{ route('controlecp.aprendiz.asesorias.instructores-disponibles') }}?' + params);
        const data = await response.json();
        select.innerHTML = '<option value="">Selecciona un instructor...</option>';
        data.instructores.forEach((instructor) => {
            select.add(new Option(instructor.nombre, instructor.id));
        });
        select.disabled = data.instructores.length === 0;
        mensaje.className = 'small mt-2 ' + (data.instructores.length ? 'text-success' : 'text-danger');
        mensaje.textContent = data.mensaje || 'Selecciona el instructor que prefieras.';
    });
</script>
@endpush
