@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .instructor-pqr-header { padding:1.25rem 1.5rem; border-radius:20px; background:#001A29; color:#fff; }
    .instructor-pqr-card { border:0; border-radius:16px; box-shadow:0 2px 12px rgba(0,26,41,.07); }
    .instructor-pqr-timeline { position:relative; padding-left:1.5rem; border-left:2px solid #e2e8f0; }
    .instructor-pqr-event { position:relative; padding-bottom:1.25rem; }
    .instructor-pqr-event:last-child { padding-bottom:0; }
    .instructor-pqr-event::before { position:absolute; top:.2rem; left:-1.93rem; width:.7rem; height:.7rem; border:2px solid #fff; border-radius:50%; background:#39A900; box-shadow:0 0 0 2px #a3e070; content:""; }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-3 py-3">
    <div class="instructor-pqr-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small fw-semibold mb-1" style="color:#62E31D">Control ECP · Panel del instructor</div>
            <h1 class="h4 fw-bold mb-1">{{ $pqr->asunto }}</h1>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="badge rounded-pill" style="background:{{ $pqr->estado_badge['bg'] }};color:{{ $pqr->estado_badge['text'] }}">
                    <i class="{{ $pqr->estado_badge['icon'] }} me-1"></i>{{ $pqr->estado_badge['label'] }}
                </span>
                <span class="badge rounded-pill border" style="background:{{ $pqr->tipo_badge['bg'] }};color:{{ $pqr->tipo_badge['text'] }};border-color:{{ $pqr->tipo_badge['border'] }} !important">
                    <i class="{{ $pqr->tipo_badge['icon'] }} me-1"></i>{{ $pqr->tipo_nombre }}
                </span>
                <span class="small text-white-50">{{ $pqr->radicado }} · {{ $pqr->fecha_radicacion?->format('d/m/Y H:i') ?? '—' }}</span>
            </div>
        </div>
        <a href="{{ route('controlecp.instructor.pqr') }}" class="btn btn-outline-light rounded-pill px-4">
            <i class="fas fa-arrow-left me-1"></i>Volver a mis PQRS
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger rounded-3" role="alert">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <section class="card instructor-pqr-card mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3" style="color:#001A29"><i class="fas fa-file-lines me-2" style="color:#39A900"></i>Solicitud</h2>
                    <div class="p-3 rounded-3 border" style="background:#f8fafc;white-space:pre-wrap;line-height:1.7">{{ $pqr->descripcion }}</div>
                </div>
            </section>

            <section class="card instructor-pqr-card mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3" style="color:#001A29"><i class="fas fa-comments me-2" style="color:#39A900"></i>Respuestas ({{ $pqr->respuestas->count() }})</h2>
                    @forelse($pqr->respuestas as $respuesta)
                        <article class="p-3 mb-3 rounded-3 border-start border-4" style="border-color:#39A900 !important;background:#f8fafc">
                            <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                                <strong>{{ $respuesta->usuario?->full_name ?? 'Equipo de atención' }}</strong>
                                <small class="text-muted">{{ $respuesta->fecha?->format('d/m/Y H:i') ?? '—' }}</small>
                            </div>
                            <p class="mb-0" style="white-space:pre-wrap">{{ $respuesta->respuesta }}</p>
                        </article>
                    @empty
                        <p class="text-muted mb-0">Todavía no hay respuestas registradas.</p>
                    @endforelse
                </div>
            </section>

            @if(!in_array($pqr->estado, ['Resuelto', 'Resuelta', 'Cerrado', 'Cerrada'], true))
                <section class="card instructor-pqr-card">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-3" style="color:#001A29"><i class="fas fa-reply me-2" style="color:#39A900"></i>Responder</h2>
                        <form method="POST" action="{{ route('controlecp.instructor.pqr.responder', $pqr->id) }}">
                            @csrf
                            <label class="form-label fw-semibold" for="respuestaPqr">Respuesta para el aprendiz</label>
                            <textarea class="form-control mb-3" id="respuestaPqr" name="respuesta" rows="5" minlength="10" maxlength="200" required>{{ old('respuesta') }}</textarea>
                            <button type="submit" class="btn text-white rounded-pill px-4" style="background:#39A900">
                                <i class="fas fa-paper-plane me-1"></i>Enviar respuesta
                            </button>
                        </form>
                    </div>
                </section>
            @endif
        </div>

        <div class="col-lg-5">
            <section class="card instructor-pqr-card mb-4">
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold mb-3" style="color:#001A29"><i class="fas fa-user me-2" style="color:#39A900"></i>Solicitante</h2>
                    <div class="fw-semibold">{{ $pqr->aprendiz?->full_name ?? 'Aprendiz' }}</div>
                    <div class="small text-muted">{{ $pqr->aprendiz?->email ?? 'Sin correo registrado' }}</div>
                    <hr>
                    <div class="small text-muted">Radicado el</div>
                    <div class="fw-semibold">{{ $pqr->fecha_radicacion?->format('d/m/Y H:i') ?? '—' }}</div>
                </div>
            </section>

            @if(!in_array($pqr->estado, ['Resuelto', 'Resuelta', 'Cerrado', 'Cerrada'], true))
                <section class="card instructor-pqr-card mb-4">
                    <div class="card-body p-4">
                        <h2 class="h6 fw-bold mb-3" style="color:#001A29"><i class="fas fa-arrows-rotate me-2" style="color:#39A900"></i>Actualizar estado</h2>
                        <form method="POST" action="{{ route('controlecp.instructor.pqr.estado', $pqr->id) }}">
                            @csrf
                            @method('PATCH')
                            <label class="form-label fw-semibold" for="estadoPqr">Estado</label>
                            <select class="form-select mb-3" id="estadoPqr" name="estado" required>
                                @foreach(['Pendiente', 'En trámite', 'Resuelto'] as $estado)
                                    <option value="{{ $estado }}" @selected(old('estado', $pqr->estado) === $estado)>{{ $estado }}</option>
                                @endforeach
                            </select>
                            <label class="form-label fw-semibold" for="observacionPqr">Observación del cambio</label>
                            <textarea class="form-control mb-3" id="observacionPqr" name="observacion" rows="3" minlength="10" maxlength="200" required>{{ old('observacion') }}</textarea>
                            <button type="submit" class="btn btn-outline-success rounded-pill px-4">Guardar estado</button>
                        </form>
                    </div>
                </section>
            @endif

            <section class="card instructor-pqr-card">
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold mb-3" style="color:#001A29"><i class="fas fa-clock-rotate-left me-2" style="color:#39A900"></i>Historial</h2>
                    <div class="instructor-pqr-timeline">
                        <div class="instructor-pqr-event">
                            <div class="fw-semibold">Solicitud radicada · {{ $pqr->estado_badge['label'] }}</div>
                            <small class="text-muted">{{ $pqr->fecha_radicacion?->format('d/m/Y H:i') ?? '—' }}</small>
                            <div class="small text-muted">Por {{ $pqr->aprendiz?->full_name ?? 'Aprendiz' }}</div>
                        </div>
                        @foreach($pqr->historial as $evento)
                            <div class="instructor-pqr-event">
                                <div class="fw-semibold">{{ $evento->estado_anterior ? $evento->estado_anterior . ' → ' : '' }}{{ $evento->estado_nuevo }}</div>
                                <small class="text-muted">{{ $evento->fecha?->format('d/m/Y H:i') ?? '—' }}</small>
                                <div class="small">{{ $evento->observacion }}</div>
                                <div class="small text-muted">Por {{ $evento->usuario?->full_name ?? 'Sistema' }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
