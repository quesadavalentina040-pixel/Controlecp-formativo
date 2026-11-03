@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <h2 class="h3 fw-bold mb-1" style="color:#001A29;">
                <i class="fas fa-comment-dots me-2" style="color:#39A900;"></i>Mis PQRS
            </h2>
            <p class="text-muted small mb-0">Radica solicitudes y consulta sus respuestas.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('controlecp.aprendiz.pqr.create') }}" class="btn text-white rounded-pill px-3" style="background:#39A900;">
                <i class="fas fa-plus me-1"></i>Radicar PQRS
            </a>
            <a href="{{ route('controlecp.aprendiz.inicio') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Volver
            </a>
        </div>
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
                    <tr><th>Radicado</th><th>Tipo</th><th>Asunto</th><th>Fecha</th><th>Estado</th><th class="text-end">Ver</th></tr>
                </thead>
                <tbody>
                    @forelse($pqrs as $pqr)
                        @php($badge = $pqr->estado_badge)
                        <tr>
                            <td class="fw-semibold">{{ $pqr->radicado }}</td>
                            <td>{{ $pqr->tipo?->nombre ?? 'Sin tipo' }}</td>
                            <td>{{ $pqr->asunto }}</td>
                            <td>{{ $pqr->fecha_radicacion?->format('d/m/Y H:i') }}</td>
                            <td><span class="badge rounded-pill" style="background:{{ $badge['bg'] }};color:{{ $badge['text'] }};">{{ $badge['label'] }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('controlecp.aprendiz.pqr.show', $pqr->id_pqrs) }}" class="btn btn-sm btn-outline-secondary rounded-circle" title="Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">Aún no has radicado PQRS.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
