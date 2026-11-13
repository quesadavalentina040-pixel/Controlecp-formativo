@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
@php
    $asistenciaRegistrada = $cronograma->asistencia?->estado === 'Registrada';
    $soloLectura = $asistenciaRegistrada && !$editando;
    $bloqueado = !$editando && ($cronograma->estado === 'Finalizada' || $cronograma->estado === 'Cancelada' || $cronograma->fecha?->isFuture());
@endphp
<div class="container-fluid px-3 py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="small fw-semibold text-success mb-1"><i class="fas fa-clipboard-check me-2"></i>Control ECP · Instructor</div>
            <h1 class="h3 fw-bold mb-1">{{ $editando ? 'Editar asistencia' : ($soloLectura ? 'Detalle de asistencia' : 'Registrar asistencia') }}</h1>
            <p class="text-muted mb-0">{{ $cronograma->actividad?->nombre }} · {{ $cronograma->fecha?->format('d/m/Y') }} · Ficha {{ $cronograma->ficha?->code ?? '—' }}</p>
        </div>
        <a class="btn btn-outline-secondary rounded-pill" href="{{ route('controlecp.instructor.asistencia') }}">Volver a asistencias</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @if($cronograma->fecha?->isFuture() && !$soloLectura)
        <div class="alert alert-info">Podrás registrar la asistencia el día de la actividad.</div>
    @elseif($bloqueado && !$soloLectura)
        <div class="alert alert-warning">No se puede registrar asistencia para una actividad finalizada o cancelada.</div>
    @elseif($soloLectura)
        <div class="alert alert-success"><i class="fas fa-circle-check me-2"></i>La asistencia está registrada. Puedes consultar el checklist o editarlo.</div>
    @endif
    @if($aprendices->isEmpty())
        <div class="alert alert-warning">No hay aprendices asociados a esta ficha; no es posible guardar el checklist vacío.</div>
    @endif

    <form method="POST" action="{{ route('controlecp.instructor.asistencia.checklist.store', $cronograma->id_cronograma) }}" enctype="multipart/form-data">
        @csrf
        @if($editando)
            <input type="hidden" name="modo" value="editar">
        @endif
        <div class="card border-0 shadow-sm" style="border-radius:18px;overflow:hidden">
            <div class="card-header bg-white py-3 px-4">
                <p class="small text-muted mb-0">Todos van presentes por defecto. Desmarca a quien no asistió y agrega la excusa con soporte.</p>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background:#001A29;color:#fff">
                        <tr>
                            <th class="ps-4 py-3">Documento</th>
                            <th class="py-3">Aprendiz</th>
                            <th class="py-3 text-center">Presente</th>
                            <th class="py-3 text-center">Excusa</th>
                            <th class="py-3">Motivo y soporte de excusa</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($aprendices as $aprendiz)
                        @php
                            $detalle = $detalles->get($aprendiz->id);
                            $valorPresente = old("asistencia.{$aprendiz->id}.presente", $editando ? (string) (int) ($detalle?->presente ?? 1) : '1');
                            $valorJustificada = old("asistencia.{$aprendiz->id}.justificada", $editando ? ((int) ($detalle?->justificada ?? 0)) : 0);
                            $observacion = old("asistencia.{$aprendiz->id}.observacion", $editando ? $detalle?->observacion : '');
                        @endphp
                        <tr data-aprendiz="{{ $aprendiz->id }}">
                            <td class="ps-4 fw-semibold font-monospace">{{ $aprendiz->person?->document_number ?? $aprendiz->person_id }}</td>
                            <td class="fw-semibold">{{ $aprendiz->person?->full_name ?? 'Aprendiz' }}</td>
                            <td class="text-center">
                                @if($soloLectura)
                                    <span class="badge rounded-pill {{ $detalle?->presente ? 'text-bg-success' : 'text-bg-danger' }}">{{ $detalle?->presente ? 'Presente' : 'Ausente' }}</span>
                                @else
                                    <input type="hidden" name="asistencia[{{ $aprendiz->id }}][presente]" value="0">
                                    <input type="checkbox" class="form-check-input asistencia-presente" name="asistencia[{{ $aprendiz->id }}][presente]" value="1" @checked((string) $valorPresente === '1') @disabled($bloqueado)>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($soloLectura)
                                    <span class="badge rounded-pill {{ $detalle?->justificada ? 'text-bg-info' : 'text-bg-secondary' }}">{{ $detalle?->justificada ? 'Sí' : 'No' }}</span>
                                @else
                                    <input type="hidden" name="asistencia[{{ $aprendiz->id }}][justificada]" value="0">
                                    <input type="checkbox" class="form-check-input asistencia-justificada" name="asistencia[{{ $aprendiz->id }}][justificada]" value="1" @checked((string) $valorJustificada === '1') @disabled($bloqueado || (string) $valorPresente === '1')>
                                @endif
                            </td>
                            <td>
                                @if($soloLectura)
                                    @if($detalle?->observacion || $detalle?->excusa_imagen)
                                        <div class="small text-muted">
                                            {{ $detalle?->observacion ?: '—' }}
                                            @if($detalle?->excusa_imagen)
                                                <a class="d-inline-block mt-1 small text-primary" href="{{ asset('storage/' . $detalle->excusa_imagen) }}" target="_blank" rel="noopener">Ver soporte</a>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                @else
                                    <div class="asistencia-excusa-detalles {{ (string) $valorJustificada === '1' ? '' : 'd-none' }}">
                                        <input type="text" class="form-control form-control-sm mb-2 asistencia-observacion" name="asistencia[{{ $aprendiz->id }}][observacion]" value="{{ $observacion }}" maxlength="1000" placeholder="Escribe el motivo de la excusa" @required((string) $valorJustificada === '1') @disabled($bloqueado)>
                                        <input type="file" class="form-control form-control-sm asistencia-excusa-imagen" name="asistencia[{{ $aprendiz->id }}][excusa_imagen]" accept="image/jpeg,image/png" @required((string) $valorJustificada === '1' && empty($detalle?->excusa_imagen)) @disabled($bloqueado)>
                                        @if($detalle?->excusa_imagen)
                                            <a class="small d-inline-block mt-1 text-primary" href="{{ asset('storage/' . $detalle->excusa_imagen) }}" target="_blank" rel="noopener">Ver imagen de soporte</a>
                                        @endif
                                    </div>
                                    @if((string) $valorJustificada !== '1')
                                        <div class="small text-muted">Si marca la excusa, aquí aparecerá el motivo y la evidencia.</div>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No hay aprendices asociados a esta ficha.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if(!$soloLectura)
                <div class="card-body border-top">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="evidenciaFoto">Foto opcional (JPG o PNG)</label>
                            <input class="form-control" id="evidenciaFoto" type="file" name="evidencia_foto" accept="image/jpeg,image/png" @disabled($bloqueado)>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="evidenciaEnlace">Enlace de video opcional</label>
                            <input class="form-control" id="evidenciaEnlace" type="url" name="evidencia_enlace" maxlength="500" placeholder="https://..." @disabled($bloqueado)>
                        </div>
                    </div>
                    @if($editando)
                        <div class="mt-3">
                            <label class="form-label fw-semibold" for="motivoEdicion">Motivo de la corrección *</label>
                            <textarea class="form-control" id="motivoEdicion" name="motivo" maxlength="1000" required @disabled($bloqueado)></textarea>
                        </div>
                    @endif
                </div>
                <div class="card-footer bg-white d-flex justify-content-end">
                    <button class="btn text-white rounded-pill px-4" type="submit" style="background:#39A900"
                            @disabled($bloqueado || $aprendices->isEmpty())>
                        <i class="fas fa-floppy-disk me-1"></i>{{ $editando ? 'Guardar corrección' : 'Guardar asistencia' }}
                    </button>
                </div>
            @elseif($soloLectura)
                <div class="card-footer bg-white d-flex justify-content-end gap-2">
                    <a class="btn btn-outline-warning rounded-pill" href="{{ route('controlecp.instructor.asistencia.checklist', ['cronogramaId' => $cronograma->id_cronograma, 'editar' => 1]) }}">
                        <i class="fas fa-pen me-1"></i>Editar asistencia
                    </a>
                </div>
            @endif
        </div>
    </form>
</div>

<script>
    function actualizarFilaAsistencia(fila) {
        if (!fila) return;

        const presente = fila.querySelector('.asistencia-presente');
        const excusa = fila.querySelector('.asistencia-justificada');
        const detallesExcusa = fila.querySelector('.asistencia-excusa-detalles');
        const observacion = fila.querySelector('.asistencia-observacion');
        const imagen = fila.querySelector('.asistencia-excusa-imagen');
        if (!presente || !excusa || !detallesExcusa) return;

        excusa.disabled = presente.checked;
        if (presente.checked) {
            excusa.checked = false;
        }

        detallesExcusa.classList.toggle('d-none', !excusa.checked);
        if (observacion) {
            observacion.required = excusa.checked;
        }
        if (imagen) {
            imagen.required = excusa.checked;
        }
    }

    document.querySelectorAll('tr[data-aprendiz]').forEach((fila) => {
        actualizarFilaAsistencia(fila);
    });

    document.addEventListener('change', (event) => {
        if (event.target.matches('.asistencia-presente')) {
            actualizarFilaAsistencia(event.target.closest('tr[data-aprendiz]'));
        }

        if (event.target.matches('.asistencia-justificada')) {
            actualizarFilaAsistencia(event.target.closest('tr[data-aprendiz]'));
        }
    });
</script>
@endsection
