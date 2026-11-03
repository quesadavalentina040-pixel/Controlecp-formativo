@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <h2 class="h3 fw-bold mb-1" style="color:#001A29;">
                <i class="fas fa-square-poll-vertical me-2" style="color:#39A900;"></i>Mis encuestas
            </h2>
            <p class="text-muted small mb-0">Responde únicamente las encuestas de actividades a las que asististe.</p>
        </div>
        <a href="{{ route('controlecp.aprendiz.inicio') }}" class="btn btn-outline-secondary rounded-pill btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>

    @if(session('success') || session('error'))
        <div class="alert alert-{{ session('success') ? 'success' : 'danger' }} border-0 shadow-sm">
            {{ session('success') ?: session('error') }}
        </div>
    @endif

    @forelse($sesiones as $sesion)
        @php
            $respondida = $respuestas->has($sesion->id_cronograma);
            $encuesta = $sesion->actividad->encuesta;
        @endphp
        <div class="card border-0 shadow-sm mb-3" style="border-radius:16px;">
            <div class="card-body p-4 d-flex flex-wrap align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:48px;height:48px;background:#f0fbf4;color:#39A900;">
                    <i class="fas fa-star"></i>
                </div>
                <div class="flex-grow-1">
                    <h5 class="fw-bold mb-1" style="color:#001A29;">{{ $encuesta->nombre }}</h5>
                    <div class="small text-muted">
                        {{ $sesion->actividad->nombre }} · {{ $sesion->fecha?->format('d/m/Y') }}
                    </div>
                    @if($encuesta->descripcion)
                        <div class="small text-muted mt-1">{{ $encuesta->descripcion }}</div>
                    @endif
                </div>
                @if($respondida)
                    <span class="badge bg-success-subtle text-success border rounded-pill px-3 py-2">
                        <i class="fas fa-check me-1"></i>Respondida
                    </span>
                @else
                    <a href="{{ route('controlecp.aprendiz.encuesta.responder', $sesion->id_cronograma) }}"
                       class="btn text-white rounded-pill px-4" style="background:#39A900;">
                        Responder
                    </a>
                @endif
            </div>
        </div>
    @empty
        <div class="card border-0 shadow-sm text-center py-5">
            <i class="fas fa-clipboard-check fa-2x text-muted mb-3"></i>
            <p class="text-muted mb-0">No tienes encuestas disponibles por ahora.</p>
        </div>
    @endforelse
</div>
@endsection
