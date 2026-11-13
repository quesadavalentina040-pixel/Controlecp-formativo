@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .apr-encuesta-header { background:#001A29; border-radius:20px; padding:1.25rem 1.5rem; }
    .apr-encuesta-header h1 { color:#fff; }
    .apr-encuesta-header p { color:#c7d4da; }
    .apr-pregunta-card { background:#fff; border:1px solid #e0ece4; border-radius:14px; }
    .apr-encuesta-form-card { background:#fff; border:1px solid #e0ece4; border-radius:16px; }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-3 py-3">
    <div class="apr-encuesta-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small fw-semibold mb-1" style="color:#62E31D">Control ECP · Aprendiz</div>
            <h1 class="h3 fw-bold mb-1"><i class="fas fa-square-poll-vertical me-2" style="color:#62E31D"></i>{{ $encuesta->nombre }}</h1>
            <p class="mb-0 small">{{ $cronograma->actividad?->nombre }} · {{ $cronograma->actividad?->momento?->nombre }}</p>
        </div>
        <a href="{{ route('controlecp.aprendiz.encuesta') }}" class="btn btn-outline-light rounded-pill px-3">
            <i class="fas fa-arrow-left me-1"></i>Volver a mis encuestas
        </a>
    </div>

    @if(session('info'))<div class="alert alert-info">{{ session('info') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Revisa tus respuestas:</strong>
            <ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="apr-encuesta-form-card p-3 p-md-4">
        <div class="d-flex flex-wrap gap-3 mb-4 pb-3 border-bottom">
            <div><span class="fw-semibold">Fecha:</span> {{ $cronograma->fecha?->translatedFormat('d M Y') }}</div>
            <div><span class="fw-semibold">Hora:</span> {{ substr($cronograma->hora_inicio, 0, 5) }} – {{ substr($cronograma->hora_fin, 0, 5) }}</div>
            <div><span class="fw-semibold">Lugar:</span> {{ $cronograma->lugar }}</div>
            <div><span class="fw-semibold">Estado:</span> {{ $respuesta ? 'Respondida' : 'Pendiente' }}</div>
        </div>

        @if($respuesta)
            <div class="alert alert-success"><i class="fas fa-circle-check me-2"></i>Ya respondiste esta encuesta el {{ $respuesta->fecha?->format('d/m/Y H:i') }}. No se puede responder nuevamente.</div>
            @php
                $respuestasGuardadas = $respuesta->detalles->keyBy('id_pregunta');
            @endphp
            @foreach($encuesta->preguntas as $index => $pregunta)
                @php
                    $detalle = $respuestasGuardadas->get($pregunta->id_pregunta);
                @endphp
                <article class="apr-pregunta-card p-3 mb-3">
                    <div class="small text-muted mb-1">Pregunta {{ $index + 1 }}</div>
                    <h2 class="h6 fw-bold">{{ $pregunta->texto }}</h2>
                    <div class="text-success">
                        @if($pregunta->tipo === 'Opcion_multiple')
                            {{ $detalle?->opcion?->texto ?? 'Sin respuesta' }}
                        @else
                            {{ $detalle?->valor ?? 'Sin respuesta' }}
                        @endif
                    </div>
                </article>
            @endforeach
        @else
            @if($encuesta->descripcion)<p class="text-muted mb-4">{{ $encuesta->descripcion }}</p>@endif
            <form method="POST" action="{{ route('controlecp.aprendiz.encuesta.store', $cronograma->id_cronograma) }}">
                @csrf
                @forelse($encuesta->preguntas as $index => $pregunta)
                    @php
                        $answerName = 'respuestas[' . $pregunta->id_pregunta . ']';
                    @endphp
                    <article class="apr-pregunta-card p-3 mb-3">
                        <div class="d-flex align-items-start gap-2 mb-3">
                            <span class="badge rounded-pill bg-light text-dark border">Pregunta {{ $index + 1 }}</span>
                            @if($pregunta->obligatoria)<span class="badge rounded-pill text-bg-danger">Obligatoria</span>@else<span class="badge rounded-pill text-bg-secondary">Opcional</span>@endif
                        </div>
                        <h2 class="h6 fw-bold mb-3">{{ $pregunta->texto }}</h2>

                        @if($pregunta->tipo === 'Opcion_multiple')
                            @foreach($pregunta->opciones as $opcion)
                                <label class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="{{ $answerName }}" value="{{ $opcion->id }}"
                                           @checked((string) old('respuestas.' . $pregunta->id_pregunta) === (string) $opcion->id)>
                                    <span class="form-check-label">{{ $opcion->texto }}</span>
                                </label>
                            @endforeach
                        @elseif($pregunta->tipo === 'Escala')
                            <fieldset>
                                <legend class="small text-muted">Selecciona una calificación de 1 a 5</legend>
                                <div class="d-flex flex-wrap gap-3">
                                    @for($scale = 1; $scale <= 5; $scale++)
                                        <label class="form-check">
                                            <input class="form-check-input" type="radio" name="{{ $answerName }}" value="{{ $scale }}"
                                                   @checked((string) old('respuestas.' . $pregunta->id_pregunta) === (string) $scale)>
                                            <span class="form-check-label">{{ $scale }}</span>
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
                @if($encuesta->preguntas->isNotEmpty())
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-success rounded-pill px-4" type="submit">
                            <i class="fas fa-paper-plane me-2"></i>Enviar respuestas
                        </button>
                    </div>
                @endif
            </form>
        @endif
    </div>
</div>
@endsection
