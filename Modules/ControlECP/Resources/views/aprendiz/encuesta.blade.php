@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .apr-encuesta-header {
        background: #001A29;
        border-radius: 20px;
        padding: 1.25rem 1.5rem;
    }
    .apr-encuesta-header h1 { color: #fff; }
    .apr-encuesta-header p { color: #c7d4da; }
    .apr-encuesta-card {
        overflow:hidden;
        background: #fff;
        border: 1px solid #e0ece4;
        border-radius: 16px;
        box-shadow: 0 2px 10px rgba(0, 26, 41, .05);
        transition:transform .18s ease, box-shadow .18s ease;
    }
    .apr-encuesta-card:hover { transform:translateY(-2px); box-shadow:0 10px 24px rgba(0,26,41,.1); }
    .apr-encuesta-card .card-body { padding: 1.25rem; }
    .apr-encuesta-icon { display:flex; width:48px; height:48px; align-items:center; justify-content:center; border-radius:15px; font-size:1.15rem; }
    .apr-encuesta-kpi { min-height:125px; padding:1.1rem 1.25rem; border:1px solid; border-radius:16px; background:#fff; box-shadow:0 2px 10px rgba(0,26,41,.05); transition:transform .18s ease, box-shadow .18s ease; }
    .apr-encuesta-kpi:hover { transform:translateY(-2px); box-shadow:0 8px 22px rgba(0,26,41,.1); }
    .apr-encuesta-kpi-label { color:#475569; font-size:.9rem; font-weight:600; }
    .apr-encuesta-kpi-value { margin-top:.4rem; font-size:2rem; font-weight:800; line-height:1; }
    .apr-encuesta-kpi-icon { display:flex; width:42px; height:42px; align-items:center; justify-content:center; border-radius:50%; font-size:1rem; }
    .apr-encuesta-status { display:inline-flex; min-width:112px; align-items:center; justify-content:center; gap:.4rem; padding:.22rem .75rem; border:1px solid transparent; border-radius:999px; font-size:.75rem; font-weight:800; white-space:nowrap; }
    .apr-encuesta-status--done { border-color:#9bd8b7; background:#e9f8ef; color:#168653; }
    .apr-encuesta-status--pending { border-color:#ffc533; background:#fff4d6; color:#974500; }
    .apr-encuesta-status--disabled { border-color:#cbd5e1; background:#f1f5f9; color:#64748b; }
    .apr-encuesta-open { display:inline-flex; min-height:40px; align-items:center; justify-content:center; gap:.45rem; border-radius:999px; font-weight:700; }
    .apr-encuesta-modal .modal-content { overflow:hidden; border:0; border-radius:20px; box-shadow:0 24px 70px rgba(0,26,41,.24); }
    .apr-encuesta-modal .modal-header { gap:.85rem; padding:1.15rem 1.35rem; border:0; border-bottom:3px solid #39A900; color:#fff; background:linear-gradient(120deg,#001A29,#00324D); }
    .apr-encuesta-modal .modal-title { color:#fff; }
    .apr-encuesta-modal .modal-header p { color:#c7d4da; }
    .apr-encuesta-modal .modal-header .btn-close { filter:brightness(0) invert(1); }
    .apr-encuesta-modal .modal-body { max-height:70vh; overflow-y:auto; padding:1.25rem; background:#f7faf8; }
    .apr-encuesta-meta { display:flex; flex-wrap:wrap; gap:.5rem; }
    .apr-encuesta-meta span { display:inline-flex; align-items:center; gap:.4rem; padding:.4rem .65rem; border:1px solid #e2e8f0; border-radius:9px; background:#fff; color:#475569; font-size:.78rem; }
    .apr-encuesta-question { padding:1rem; border:1px solid #e0ece4; border-radius:14px; background:#fff; }
    .apr-encuesta-answer { padding:.75rem 1rem; border-radius:10px; background:#eef9e9; color:#287a13; }
    .apr-encuesta-choice { display:flex; gap:.65rem; align-items:flex-start; padding:.65rem .75rem; border:1px solid #e2e8f0; border-radius:10px; background:#fff; cursor:pointer; }
    .apr-encuesta-choice:has(input:checked) { border-color:#39A900; background:#f2faee; }
    .apr-encuesta-choice input { margin-top:.2rem; }
    .apr-encuesta-empty-icon { display:grid; width:72px; height:72px; place-items:center; margin:0 auto 1rem; border-radius:50%; background:#f0fbf4; color:#39A900; font-size:1.8rem; }
    @media (max-width:575px) {
        .apr-encuesta-modal .modal-body { padding:1rem; }
        .apr-encuesta-card .card-body { padding:1rem; }
        .apr-encuesta-kpi { min-height:110px; }
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-3 py-3">
    <div class="apr-encuesta-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small fw-semibold mb-1" style="color:#62E31D">Control ECP · Aprendiz</div>
            <h1 class="h3 fw-bold mb-1"><i class="fas fa-square-poll-vertical me-2" style="color:#62E31D"></i>Mis encuestas</h1>
            <p class="mb-0 small">Comparte tu opinión sobre las actividades a las que asististe.</p>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('info'))<div class="alert alert-info">{{ session('info') }}</div>@endif
    @if(session('error'))<div class="alert alert-warning">{{ session('error') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Revisa tus respuestas:</strong>
            <ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @php
        $totalEncuestas = $encuestas->count();
        $encuestasRespondidas = $encuestas->filter(fn ($actividad) => $actividad->respondida)->count();
        $encuestasPendientes = $encuestas->filter(fn ($actividad) => $actividad->asistio && !$actividad->respondida)->count();
    @endphp
    <div class="row g-3 mb-4" aria-label="Resumen de encuestas">
        <div class="col-12 col-md-4">
            <div class="apr-encuesta-kpi h-100" style="border-color:rgba(2,132,199,.35)">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <span class="apr-encuesta-kpi-label">Total de encuestas</span>
                    <span class="apr-encuesta-kpi-icon" style="background:#e0f2fe;color:#0284c7"><i class="fas fa-square-poll-vertical" aria-hidden="true"></i></span>
                </div>
                <div class="apr-encuesta-kpi-value" style="color:#0284c7">{{ $totalEncuestas }}</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="apr-encuesta-kpi h-100" style="border-color:rgba(245,158,11,.4)">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <span class="apr-encuesta-kpi-label">Pendientes por responder</span>
                    <span class="apr-encuesta-kpi-icon" style="background:#fff7e6;color:#c47a00"><i class="fas fa-clock" aria-hidden="true"></i></span>
                </div>
                <div class="apr-encuesta-kpi-value" style="color:#c47a00">{{ $encuestasPendientes }}</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="apr-encuesta-kpi h-100" style="border-color:rgba(22,163,74,.35)">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <span class="apr-encuesta-kpi-label">Respondidas</span>
                    <span class="apr-encuesta-kpi-icon" style="background:#dcfce7;color:#16a34a"><i class="fas fa-circle-check" aria-hidden="true"></i></span>
                </div>
                <div class="apr-encuesta-kpi-value" style="color:#16a34a">{{ $encuestasRespondidas }}</div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column gap-3">
        @forelse($encuestas as $actividad)
            @php
                $asistio = $actividad->asistio;
                $respondida = $actividad->respondida;
                $nombreMomento = $actividad->actividad?->momento?->nombre ?? 'Sin momento';
                $numeroActividad = $actividad->actividad?->numero
                    ? 'Actividad ' . $actividad->actividad->numero
                    : 'Actividad ' . ($actividad->actividad?->id ?? '');
            @endphp
            <article class="apr-encuesta-card">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-start gap-3">
                        <div class="apr-encuesta-icon flex-shrink-0"
                             style="background:{{ $respondida ? '#d8f3dc' : ($asistio ? '#fff3cd' : '#f1f5f9') }};color:{{ $respondida ? '#15803d' : ($asistio ? '#a16207' : '#64748b') }}">
                            <i class="fas {{ $respondida ? 'fa-circle-check' : ($asistio ? 'fa-clipboard-question' : 'fa-lock') }}" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h2 class="h5 fw-bold mb-1">{{ $actividad->encuesta->nombre }}</h2>
                            <div class="text-muted">{{ $numeroActividad }} · {{ $nombreMomento }}</div>
                            <div class="small text-muted mt-1 d-flex flex-wrap gap-3">
                                <span><i class="far fa-calendar me-1"></i>{{ $actividad->fecha?->translatedFormat('d M Y') ?? 'Sin fecha' }}</span>
                                @if($actividad->encuesta->descripcion)
                                    <span><i class="fas fa-circle-info me-1"></i>{{ \Illuminate\Support\Str::limit($actividad->encuesta->descripcion, 110) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        @if($respondida)
                            <span class="apr-encuesta-status apr-encuesta-status--done"><i class="fas fa-check" aria-hidden="true"></i>Respondida</span>
                            <button class="btn btn-outline-success apr-encuesta-open" type="button" data-bs-toggle="modal" data-bs-target="#modalEncuesta{{ $actividad->id_cronograma }}">
                                <i class="fas fa-eye" aria-hidden="true"></i>Ver respuestas
                            </button>
                        @elseif($asistio)
                            <span class="apr-encuesta-status apr-encuesta-status--pending"><i class="fas fa-clock" aria-hidden="true"></i>Pendiente</span>
                            <button class="btn btn-success apr-encuesta-open px-3" type="button" data-bs-toggle="modal" data-bs-target="#modalEncuesta{{ $actividad->id_cronograma }}">
                                <i class="fas fa-pen-to-square" aria-hidden="true"></i>Responder
                            </button>
                        @else
                            <span class="apr-encuesta-status apr-encuesta-status--disabled"><i class="fas fa-lock" aria-hidden="true"></i>No habilitada · sin asistencia</span>
                        @endif
                    </div>
                </div>
            </article>
            <div class="modal fade apr-encuesta-modal" id="modalEncuesta{{ $actividad->id_cronograma }}" tabindex="-1" aria-labelledby="modalEncuestaTitulo{{ $actividad->id_cronograma }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <span class="apr-encuesta-icon flex-shrink-0" style="background:rgba(98,227,29,.14);color:#8df05d">
                                <i class="fas {{ $respondida ? 'fa-square-check' : 'fa-clipboard-list' }}" aria-hidden="true"></i>
                            </span>
                            <div class="flex-grow-1">
                                <h2 class="modal-title h5 fw-bold mb-0" id="modalEncuestaTitulo{{ $actividad->id_cronograma }}">{{ $actividad->encuesta->nombre }}</h2>
                                <p class="mb-0 small">{{ $numeroActividad }} · {{ $nombreMomento }}</p>
                            </div>
                            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="apr-encuesta-meta mb-3">
                                <span><i class="far fa-calendar" aria-hidden="true"></i>{{ $actividad->fecha?->translatedFormat('d M Y') ?? 'Sin fecha' }}</span>
                                <span><i class="far fa-clock" aria-hidden="true"></i>{{ substr($actividad->hora_inicio, 0, 5) }}–{{ substr($actividad->hora_fin, 0, 5) }}</span>
                                @if($actividad->lugar)<span><i class="fas fa-location-dot" aria-hidden="true"></i>{{ $actividad->lugar }}</span>@endif
                            </div>

                            @if($respondida)
                                <div class="alert alert-success"><i class="fas fa-circle-check me-2" aria-hidden="true"></i>Respondiste esta encuesta el {{ $actividad->respuestaAprendiz->fecha?->format('d/m/Y H:i') ?? '—' }}.</div>
                                @php($respuestasGuardadas = $actividad->respuestaAprendiz->detalles->keyBy('id_pregunta'))
                                @foreach($actividad->encuesta->preguntas as $index => $pregunta)
                                    @php($detalle = $respuestasGuardadas->get($pregunta->id_pregunta))
                                    <article class="apr-encuesta-question mb-3">
                                        <div class="small text-muted mb-1">Pregunta {{ $index + 1 }}</div>
                                        <h3 class="h6 fw-bold">{{ $pregunta->texto }}</h3>
                                        <div class="apr-encuesta-answer">
                                            @if($pregunta->tipo === 'Opcion_multiple')
                                                {{ $detalle?->opcion?->texto ?? 'Sin respuesta' }}
                                            @else
                                                {{ $detalle?->valor ?? 'Sin respuesta' }}
                                            @endif
                                        </div>
                                    </article>
                                @endforeach
                            @else
                                @if($actividad->encuesta->descripcion)<p class="text-muted mb-3">{{ $actividad->encuesta->descripcion }}</p>@endif
                                <form method="POST" action="{{ route('controlecp.aprendiz.encuesta.store', $actividad->id_cronograma) }}">
                                    @csrf
                                    <input type="hidden" name="cronograma_id" value="{{ $actividad->id_cronograma }}">
                                    @forelse($actividad->encuesta->preguntas as $index => $pregunta)
                                        @php($answerName = 'respuestas[' . $pregunta->id_pregunta . ']')
                                        <article class="apr-encuesta-question mb-3">
                                            <div class="d-flex align-items-start gap-2 mb-3">
                                                <span class="badge rounded-pill bg-light text-dark border">Pregunta {{ $index + 1 }}</span>
                                                @if($pregunta->obligatoria)<span class="badge rounded-pill text-bg-danger">Obligatoria</span>@else<span class="badge rounded-pill text-bg-secondary">Opcional</span>@endif
                                            </div>
                                            <h3 class="h6 fw-bold mb-3">{{ $pregunta->texto }}</h3>
                                            @if($pregunta->tipo === 'Opcion_multiple')
                                                @foreach($pregunta->opciones as $opcion)
                                                    <label class="apr-encuesta-choice mb-2">
                                                        <input class="form-check-input" type="radio" name="{{ $answerName }}" value="{{ $opcion->id }}"
                                                               @checked((string) old('respuestas.' . $pregunta->id_pregunta) === (string) $opcion->id)
                                                               @required($pregunta->obligatoria)>
                                                        <span>{{ $opcion->texto }}</span>
                                                    </label>
                                                @endforeach
                                            @elseif($pregunta->tipo === 'Escala')
                                                <fieldset>
                                                    <legend class="small text-muted">Selecciona una calificación de 1 a 5</legend>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @for($scale = 1; $scale <= 5; $scale++)
                                                            <label class="apr-encuesta-choice">
                                                                <input class="form-check-input" type="radio" name="{{ $answerName }}" value="{{ $scale }}"
                                                                       @checked((string) old('respuestas.' . $pregunta->id_pregunta) === (string) $scale)
                                                                       @required($pregunta->obligatoria)>
                                                                <span>{{ $scale }} <i class="fas fa-star text-warning" aria-hidden="true"></i></span>
                                                            </label>
                                                        @endfor
                                                    </div>
                                                </fieldset>
                                            @else
                                                <textarea class="form-control" name="{{ $answerName }}" rows="3" maxlength="5000"
                                                          @required($pregunta->obligatoria)>{{ old('respuestas.' . $pregunta->id_pregunta) }}</textarea>
                                            @endif
                                        </article>
                                    @empty
                                        <div class="alert alert-warning">Esta encuesta no tiene preguntas configuradas.</div>
                                    @endforelse
                                    @if($actividad->encuesta->preguntas->isNotEmpty())
                                        <div class="d-flex justify-content-end">
                                            <button class="btn btn-success rounded-pill px-4" type="submit"><i class="fas fa-paper-plane me-2" aria-hidden="true"></i>Enviar respuestas</button>
                                        </div>
                                    @endif
                                </form>
                            @endif
                        </div>
                        @if($respondida)
                            <div class="modal-footer">
                                <button class="btn btn-outline-secondary rounded-pill px-4" type="button" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="apr-encuesta-card text-center p-5">
                <div class="apr-encuesta-empty-icon"><i class="fas fa-square-poll-vertical" aria-hidden="true"></i></div>
                @if($sinFichaActiva)
                    <h2 class="h5 fw-bold">No encontramos una ficha activa asociada a tu usuario</h2>
                    <p class="text-muted mb-0">Solicita al administrador que revise tu inscripción para habilitar las encuestas.</p>
                @else
                    <h2 class="h5 fw-bold">No tienes encuestas disponibles</h2>
                    <p class="text-muted mb-0">Cuando finalice una actividad con encuesta habilitada, aparecerá aquí.</p>
                @endif
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
@if(old('cronograma_id'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById(@json('modalEncuesta' . (int) old('cronograma_id')));
            if (modal) bootstrap.Modal.getOrCreateInstance(modal).show();
        });
    </script>
@endif
@endpush
