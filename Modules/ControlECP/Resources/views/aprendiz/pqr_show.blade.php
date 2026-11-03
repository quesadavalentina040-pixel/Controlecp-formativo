@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">
    <div class="mb-4 pb-2 border-bottom">
        <a href="{{ route('controlecp.aprendiz.pqr') }}" class="small text-success text-decoration-none">
            <i class="fas fa-arrow-left me-1"></i>Volver a mis PQRS
        </a>
        <h2 class="h3 fw-bold mt-2 mb-1" style="color:#001A29;">Detalle de PQRS</h2>
        <p class="text-muted small mb-0">{{ $pqr->radicado }} · {{ $pqr->fecha_radicacion?->format('d/m/Y H:i') }}</p>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body p-4">
            @php($badge = $pqr->estado_badge)
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">{{ $pqr->asunto }}</h5>
                <span class="badge rounded-pill" style="background:{{ $badge['bg'] }};color:{{ $badge['text'] }};">{{ $badge['label'] }}</span>
            </div>
            <dl class="row small">
                <dt class="col-sm-3">Tipo</dt><dd class="col-sm-9">{{ $pqr->tipo?->nombre }}</dd>
                <dt class="col-sm-3">Instructor</dt><dd class="col-sm-9">{{ $pqr->instructorDestino?->full_name ?? 'Administración de Control ECP' }}</dd>
                <dt class="col-sm-3">Descripción</dt><dd class="col-sm-9" style="white-space:pre-line;">{{ $pqr->descripcion }}</dd>
            </dl>
            <hr>
            <h6 class="fw-bold">Respuesta</h6>
            @forelse($pqr->respuestas as $respuesta)
                <div class="alert alert-light border mb-2">
                    <div class="small text-muted mb-1">{{ $respuesta->usuario?->full_name ?? 'Equipo Control ECP' }} · {{ $respuesta->fecha?->format('d/m/Y H:i') }}</div>
                    <div style="white-space:pre-line;">{{ $respuesta->respuesta }}</div>
                </div>
            @empty
                <p class="text-muted small mb-0">Aún no hay una respuesta registrada.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
