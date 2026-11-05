@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .asistencia-hero {
        background: linear-gradient(120deg, #001A29, #00324D);
        border-radius: 24px;
        padding: 1.5rem 2rem;
        color: #fff;
    }
    .asistencia-hero .hero-eyebrow {
        color: #62E31D;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
    }
    .asistencia-hero h2 {
        color: #fff;
        font-family: 'Outfit', sans-serif;
        font-size: 2rem;
        font-weight: 800;
        margin: .35rem 0 .25rem;
    }
    .asistencia-hero p {
        color: #c7d4da;
    }
    .asistencia-hero a {
        color: #39A900;
    }
    .kpi-asistencia {
        border-radius: 16px;
        background: #fff;
        border: 1px solid rgba(0, 0, 0, .05);
        padding: 1rem 1.25rem;
        box-shadow: 0 4px 16px rgba(0, 26, 41, .06);
        transition: transform .18s;
    }
    .kpi-asistencia:hover {
        transform: translateY(-2px);
    }
    .asistencia-filters {
        display: grid;
        grid-template-columns: minmax(110px, 1fr) minmax(170px, 1.45fr) minmax(190px, 1.6fr) repeat(2, minmax(145px, 1fr)) minmax(160px, 1.15fr) 48px;
        gap: .6rem;
        align-items: end;
    }
    .asistencia-filters .form-label {
        display: block;
        margin-bottom: .35rem;
    }
    .asistencia-filters .form-select,
    .asistencia-filters .form-control {
        min-width: 0;
        width: 100%;
    }
    .ficha-search-results {
        position: absolute;
        top: calc(100% + .25rem);
        left: 0;
        right: 0;
        z-index: 20;
        display: none;
        max-height: 180px;
        overflow-y: auto;
        padding: .25rem;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: .65rem;
        box-shadow: 0 .35rem 1rem rgba(0, 26, 41, .12);
    }
    .ficha-search-results.is-visible {
        display: block;
    }
    .ficha-search-option {
        display: block;
        width: 100%;
        padding: .4rem .55rem;
        border: 0;
        border-radius: .4rem;
        background: transparent;
        color: #001A29;
        text-align: left;
        font-size: .9rem;
    }
    .ficha-search-option:hover,
    .ficha-search-option:focus {
        background: #f0fbf4;
        outline: none;
    }
    .ficha-search-empty {
        padding: .4rem .55rem;
        color: #6c757d;
        font-size: .8rem;
    }
    @media (max-width: 1199.98px) {
        .asistencia-filters {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }
    @media (max-width: 767.98px) {
        .asistencia-filters {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-3 py-3">
    <div class="asistencia-hero mb-4">
        <div>
            <div class="hero-eyebrow mt-3"><i class="fas fa-chart-line me-2"></i>Control ECP · Panel administrativo</div>
            <h2><i class="fas fa-user-check me-2" style="color: #39A900;"></i>Gestión de Asistencias</h2>
            <p class="small mb-0">Registra, controla y audita la asistencia de las actividades programadas.</p>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="kpi-asistencia" style="border: 2px solid rgba(57,169,0,.35);">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted fw-semibold">Actividades</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background: #e8f8e8; color: #39A900;"><i class="fas fa-calendar-check"></i></div>
                </div>
                <h3 class="fw-bold mb-0" style="color: #001A29;">{{ number_format($stats['total']) }}</h3>
                <small class="text-muted">Programadas</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-asistencia" style="border: 2px solid rgba(30,112,0,.35);">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted fw-semibold">Registradas</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background: #e8f8e8; color: #1e7000;"><i class="fas fa-circle-check"></i></div>
                </div>
                <h3 class="fw-bold mb-0 text-success">{{ number_format($stats['registradas']) }}</h3>
                <small class="text-muted">Asistencias guardadas</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-asistencia" style="border: 2px solid rgba(192,57,43,.35);">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted fw-semibold">Pendientes</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background: #fdecea; color: #c0392b;"><i class="fas fa-clock"></i></div>
                </div>
                <h3 class="fw-bold mb-0 text-danger">{{ number_format($stats['pendientes']) }}</h3>
                <small class="text-muted">Sin registro</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-asistencia" style="border: 2px solid rgba(106,27,154,.35);">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted fw-semibold">Ausencias</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background: #f3e8f8; color: #6a1b9a;"><i class="fas fa-user-xmark"></i></div>
                </div>
                <h3 class="fw-bold mb-0" style="color: #6a1b9a;">{{ number_format($stats['ausencias']) }}</h3>
                <small class="text-muted">Aprendices ausentes</small>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <form method="GET" class="asistencia-filters">
                <div class="position-relative"><label class="form-label small" for="filtroFicha">Ficha</label><input id="filtroFicha" type="search" name="ficha" class="form-control" value="{{ request('ficha') }}" placeholder="Buscar..." autocomplete="off" aria-autocomplete="list" aria-controls="fichaSearchResults" aria-expanded="false"><input id="filtroFichaId" type="hidden" name="ficha_id" value="{{ request('ficha_id') }}"><div id="fichaSearchResults" class="ficha-search-results" role="listbox"></div></div>
                <div><label class="form-label small">Instructor</label><select name="instructor_id" class="form-select"><option value="">Todos</option>@foreach($instructores as $instructor)<option value="{{ $instructor->id }}" @selected(request('instructor_id') == $instructor->id)>{{ $instructor->person?->full_name ?: $instructor->nickname }}</option>@endforeach</select></div>
                <div><label class="form-label small">Actividad</label><select name="actividad_id" class="form-select"><option value="">Todas</option>@foreach($actividades as $actividad)<option value="{{ $actividad->id }}" @selected(request('actividad_id') == $actividad->id)>{{ $actividad->numero }} - {{ $actividad->nombre }}</option>@endforeach</select></div>
                <div><label class="form-label small">Desde</label><input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="form-control"></div>
                <div><label class="form-label small">Hasta</label><input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="form-control"></div>
                <div><label class="form-label small">Estado</label><select name="estado" class="form-select"><option value="">Todos</option>@foreach(['Registrada','Anulada','Sin asistencia registrada'] as $estado)<option @selected(request('estado') === $estado)>{{ $estado }}</option>@endforeach</select></div>
                <div><button class="btn btn-success w-100" title="Buscar"><i class="fas fa-search"></i></button></div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 pt-3"><h5 class="fw-bold mb-0">Actividades y asistencia</h5></div>
        <div class="table-responsive"><table class="table align-middle mb-0">
            <thead class="table-light"><tr><th>Fecha</th><th>Ficha</th><th>Actividad</th><th>Instructor</th><th>Registró</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
            @forelse($cronogramas as $sesion)
                @php $registro = $sesion->asistencia; $nombreInstructor = $sesion->instructor?->person?->full_name ?: $sesion->instructor?->nickname; $vencida = $sesion->fecha && $sesion->fecha->isPast(); @endphp
                <tr>
                    <td>{{ optional($sesion->fecha)->format('d/m/Y') }}</td><td>{{ $sesion->ficha?->code ?: 'Sin ficha' }}</td><td>{{ $sesion->actividad?->nombre ?: 'Actividad eliminada' }}</td><td>{{ $nombreInstructor ?: 'Sin instructor' }}</td><td>{{ $registro?->registradoPor?->nickname ?: '—' }}</td>
                    <td>@if($registro?->estado === 'Registrada')<span class="badge text-bg-success">Registrada</span>@elseif($registro?->estado === 'Anulada')<span class="badge text-bg-danger">Anulada</span>@elseif($vencida)<span class="badge text-bg-warning">Sin asistencia registrada</span>@else<span class="badge text-bg-secondary">Pendiente</span>@endif</td>
                    <td class="text-end">
                        @if($registro?->estado === 'Registrada')
                            <button class="btn btn-sm btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#detalleAsistencia{{ $registro->id }}" title="Ver detalle"><i class="fas fa-eye"></i></button>
                            <button class="btn btn-sm btn-outline-secondary rounded-pill" data-bs-toggle="modal" data-bs-target="#formAsistencia{{ $sesion->id_cronograma }}" title="Editar asistencia"><i class="fas fa-pen"></i></button>
                            <button class="btn btn-sm btn-outline-dark rounded-pill" data-bs-toggle="modal" data-bs-target="#historialAsistencia{{ $registro->id }}" title="Historial"><i class="fas fa-clock-rotate-left"></i></button>
                            <form method="POST" action="{{ route('controlecp.administrador.asistencia.annul', $registro->id) }}" class="d-inline" onsubmit="return solicitarMotivo(this, 'Anular asistencia')">@csrf<input type="hidden" name="motivo"><button class="btn btn-sm btn-outline-danger rounded-pill" title="Anular"><i class="fas fa-ban"></i></button></form>
                        @elseif($sesion->actividad)
                            <button class="btn btn-sm btn-success rounded-pill" data-bs-toggle="modal" data-bs-target="#formAsistencia{{ $sesion->id_cronograma }}"><i class="fas fa-clipboard-check me-1"></i>Registrar</button>
                        @endif
                    </td>
                </tr>
            @empty<tr><td colspan="7" class="text-center text-muted py-4">No hay actividades programadas.</td></tr>@endforelse
            </tbody>
        </table></div>
        <div class="card-footer bg-white border-0">{{ $cronogramas->links() }}</div>
    </div>
</div>

@foreach($cronogramas as $sesion)
    @php
        $registro = $sesion->asistencia;
        $editando = $registro?->estado === 'Registrada';
        $detalles = $registro?->detalles?->keyBy('aprendiz_id') ?: collect();
        $aprendices = $aprendicesPorFicha->get($sesion->id_ficha, collect());
    @endphp
    @if($sesion->actividad)
        <div class="modal fade" id="formAsistencia{{ $sesion->id_cronograma }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content">
                <div class="modal-header"><h5 class="modal-title"><i class="fas fa-clipboard-check text-success me-2"></i>{{ $editando ? 'Editar asistencia' : 'Registrar asistencia' }} · {{ $sesion->actividad->nombre }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <form method="POST" action="{{ $editando ? route('controlecp.administrador.asistencia.update', $registro->id) : route('controlecp.administrador.asistencia.store', $sesion->id_cronograma) }}" enctype="multipart/form-data">
                    @csrf @if($editando) @method('PUT') @endif
                    <div class="modal-body">
                        <p class="text-muted small">Ficha {{ $sesion->ficha?->code }} · {{ optional($sesion->fecha)->format('d/m/Y') }}. Todos están presentes por defecto.</p>
                        <div class="table-responsive"><table class="table align-middle"><thead class="table-light"><tr><th>Documento</th><th>Aprendiz</th><th class="text-center">Presente</th><th>Observación</th></tr></thead><tbody>
                        @forelse($aprendices as $aprendiz)
                            @php $detalle = $detalles->get($aprendiz->id); @endphp
                            <tr><td class="font-monospace">{{ $aprendiz->person?->document_number }}</td><td>{{ $aprendiz->person?->full_name }}</td><td class="text-center"><input type="hidden" name="asistencia[{{ $aprendiz->id }}][presente]" value="0"><input type="checkbox" class="form-check-input" name="asistencia[{{ $aprendiz->id }}][presente]" value="1" @checked($detalle?->presente ?? true)></td><td><input class="form-control" name="asistencia[{{ $aprendiz->id }}][observacion]" value="{{ $detalle?->observacion }}" maxlength="1000" placeholder="Opcional"></td></tr>
                        @empty<tr><td colspan="4" class="text-center text-muted">La ficha no tiene aprendices.</td></tr>@endforelse
                        </tbody></table></div>
                        <div class="row g-3 mt-2"><div class="col-md-6"><label class="form-label">Foto opcional</label><input type="file" name="evidencia_foto" class="form-control" accept="image/jpeg,image/png"></div><div class="col-md-6"><label class="form-label">Enlace de video opcional</label><input type="url" name="evidencia_enlace" class="form-control" placeholder="https://..."></div></div>
                        @if($editando)<div class="mt-3"><label class="form-label">Motivo de la edición *</label><textarea class="form-control" name="motivo" maxlength="1000" required></textarea></div>@endif
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-success rounded-pill"><i class="fas fa-save me-1"></i>Guardar asistencia</button></div>
                </form>
            </div></div>
        </div>
    @endif
    @if($registro)
        <div class="modal fade" id="detalleAsistencia{{ $registro->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Detalle de asistencia · {{ $sesion->actividad?->nombre }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body"><p class="text-muted small">Registró: {{ $registro->registradoPor?->nickname ?: '—' }} · {{ optional($sesion->fecha)->format('d/m/Y') }}</p><div class="table-responsive"><table class="table align-middle"><thead class="table-light"><tr><th>Documento</th><th>Aprendiz</th><th>Estado</th><th>Observación</th></tr></thead><tbody>
                @foreach($detalles as $aprendizId => $detalle)
                    @php $aprendiz = $aprendices->firstWhere('id', $aprendizId); @endphp
                    <tr><td>{{ $aprendiz?->person?->document_number }}</td><td>{{ $aprendiz?->person?->full_name }}</td><td>{!! $detalle->presente ? '<span class="badge text-bg-success">Presente</span>' : '<span class="badge text-bg-danger">Ausente</span>' !!}</td><td>{{ $detalle->observacion ?: '—' }}</td></tr>
                @endforeach
                </tbody></table></div></div>
            </div></div>
        </div>
        <div class="modal fade" id="historialAsistencia{{ $registro->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Historial de cambios</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body"><div class="table-responsive"><table class="table align-middle"><thead class="table-light"><tr><th>Fecha</th><th>Acción</th><th>Usuario</th><th>Motivo</th></tr></thead><tbody>
                @forelse($historialPorAsistencia->get($registro->id, collect()) as $item)<tr><td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}</td><td>{{ $item->accion }}</td><td>{{ $item->nickname }}</td><td>{{ $item->motivo ?: '—' }}</td></tr>@empty<tr><td colspan="4" class="text-center text-muted">Sin historial.</td></tr>@endforelse
                </tbody></table></div></div>
            </div></div>
        </div>
    @endif
@endforeach
@endsection

@push('scripts')
<script>
(() => {
    const input = document.getElementById('filtroFicha');
    const fichaId = document.getElementById('filtroFichaId');
    const results = document.getElementById('fichaSearchResults');
    const fichas = @json($fichas->map(fn ($ficha) => ['id' => $ficha->id, 'code' => $ficha->code])->values());
    if (!input || !fichaId || !results) return;

    const hideResults = () => {
        results.classList.remove('is-visible');
        input.setAttribute('aria-expanded', 'false');
    };

    const showResults = () => {
        const term = input.value.trim().toLocaleLowerCase();
        results.replaceChildren();
        if (!term) {
            hideResults();
            return;
        }

        const matches = fichas
            .filter(ficha => String(ficha.code).toLocaleLowerCase().includes(term))
            .slice(0, 5);

        if (!matches.length) {
            const empty = document.createElement('div');
            empty.className = 'ficha-search-empty';
            empty.textContent = 'Sin coincidencias';
            results.append(empty);
        } else {
            matches.forEach(ficha => {
                const option = document.createElement('button');
                option.type = 'button';
                option.className = 'ficha-search-option';
                option.setAttribute('role', 'option');
                option.textContent = ficha.code;
                option.addEventListener('click', () => {
                    input.value = ficha.code;
                    fichaId.value = ficha.id;
                    hideResults();
                    input.focus();
                });
                results.append(option);
            });
        }

        results.classList.add('is-visible');
        input.setAttribute('aria-expanded', 'true');
    };

    input.addEventListener('input', () => {
        fichaId.value = '';
        showResults();
    });
    input.addEventListener('focus', showResults);
    input.addEventListener('keydown', event => {
        if (event.key === 'Escape') hideResults();
    });
    document.addEventListener('click', event => {
        if (!results.parentElement.contains(event.target)) hideResults();
    });
})();

function solicitarMotivo(form, titulo) {
    const motivo = window.prompt(titulo + '. Indica el motivo:');
    if (!motivo || !motivo.trim()) return false;
    form.querySelector('[name="motivo"]').value = motivo.trim();
    return true;
}
</script>
@endpush
