@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
<div class="container-fluid px-3 py-4">
    <a href="{{ route('controlecp.instructor.pqr') }}" class="btn btn-sm btn-outline-secondary mb-3"><i class="fas fa-arrow-left me-1"></i>Volver a mis PQRS</a>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    <div class="row g-4">
        <div class="col-lg-7"><div class="card border-0 shadow-sm"><div class="card-body p-4">
            <div class="d-flex justify-content-between"><div><span class="text-muted small">{{ $pqr->radicado }}</span><h2 class="h4 fw-bold">{{ $pqr->asunto }}</h2></div><span class="badge text-bg-light border align-self-start">{{ $pqr->estado }}</span></div>
            <p class="small text-muted">Solicitante: <strong>{{ $pqr->aprendiz?->nickname ?? 'Sin nombre' }}</strong> · {{ $pqr->tipo?->nombre ?? 'Petición' }}</p>
            <div class="border rounded p-3 bg-light mb-4" style="white-space:pre-wrap">{{ $pqr->descripcion }}</div>
            <h5 class="fw-bold">Responder</h5>
            <form method="POST" action="{{ route('controlecp.instructor.pqr.responder', $pqr->id_pqrs) }}">@csrf
                <textarea name="respuesta" required minlength="10" class="form-control mb-2" rows="4" placeholder="Escriba una respuesta clara"></textarea>
                <div class="d-flex gap-2"><select name="estado" class="form-select"><option value="Pendiente">Pendiente</option><option value="En trámite">En trámite</option><option value="Resuelto">Resuelta</option></select><button class="btn btn-success">Enviar respuesta</button></div>
            </form>
            @if($pqr->respuestas->isNotEmpty())<hr><h5 class="fw-bold">Respuestas</h5>@foreach($pqr->respuestas->sortByDesc('fecha') as $respuesta)<div class="border-start border-success border-3 ps-3 mb-3"><strong>{{ $respuesta->usuario?->nickname ?? 'Sistema' }}</strong><small class="text-muted ms-2">{{ $respuesta->fecha?->format('d/m/Y H:i') }}</small><p class="mb-0 mt-1">{{ $respuesta->respuesta }}</p></div>@endforeach@endif
        </div></div></div>
        <div class="col-lg-5"><div class="card border-0 shadow-sm mb-4"><div class="card-body p-4"><h5 class="fw-bold">Cambiar estado</h5><form method="POST" action="{{ route('controlecp.instructor.pqr.estado', $pqr->id_pqrs) }}">@csrf @method('PATCH')<select name="estado" class="form-select mb-2" required>@foreach(['Pendiente','En trámite','Resuelto'] as $estado)<option @selected($pqr->estado === $estado)>{{ $estado }}</option>@endforeach</select><textarea name="observacion" class="form-control mb-2" placeholder="Observación (opcional)"></textarea><button class="btn btn-outline-success w-100">Guardar estado</button></form></div></div>
        <div class="card border-0 shadow-sm"><div class="card-body p-4"><h5 class="fw-bold">Historial</h5>@forelse($pqr->historial->sortByDesc('fecha') as $item)<div class="border-start border-secondary border-2 ps-3 mb-3"><strong>{{ $item->estado_nuevo }}</strong><small class="d-block text-muted">{{ $item->fecha?->format('d/m/Y H:i') }} · {{ $item->usuario?->nickname ?? 'Sistema' }}</small><small>{{ $item->observacion }}</small></div>@empty<p class="text-muted mb-0">Sin cambios registrados.</p>@endforelse</div></div></div>
    </div>
</div>
@endsection
