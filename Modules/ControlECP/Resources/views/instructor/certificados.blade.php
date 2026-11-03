{{-- Vista: Vista del instructor para consultar certificados. --}}
@extends('controlecp::components.layouts.dashboard')

{{-- Contenido principal que organiza la interfaz específica de esta vista. --}}
@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">

    <!-- Breadcrumb & Cabecera -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.instructor.inicio') }}" class="text-decoration-none" style="color: #39A900;">
                            <i class="fas fa-house me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page" style="color: #001A29; font-weight: 600;">
                        Certificados
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: #001A29; font-family: 'Outfit', sans-serif;">
                <i class="fas fa-certificate me-2" style="color: #39A900;"></i>Certificados ECP
            </h2>
            <p class="text-muted small mb-0">Control ECP &bull; Avance de cumplimiento y postulación a certificados de la Escuela Cultura de Paz</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('controlecp.instructor.inicio') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver al Inicio
            </a>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 18px;">
        <div class="card-header bg-white fw-bold">Fichas y momentos disponibles para certificar</div>
        <div class="table-responsive"><table class="table align-middle mb-0">
            <thead><tr><th>Ficha</th><th>Momento</th><th>Progreso</th><th>Estado</th><th></th></tr></thead>
            <tbody>
            @forelse($disponibles as $item)
                <tr><td class="fw-semibold">{{ $item->ficha?->code ?? $item->ficha?->id }}</td><td>{{ $item->momento?->nombre }}</td><td>{{ $item->finalizadas }}/{{ $item->total }} actividades finalizadas @if($item->pendientes)<small class="d-block text-danger">Pendientes: {{ $item->pendientes }}</small>@endif</td><td>@if($item->certificacion)<span class="badge text-bg-success">Certificada</span>@elseif($item->cumplido)<span class="badge text-bg-warning">Lista</span>@else<span class="badge text-bg-secondary">En progreso</span>@endif</td><td>@if($item->certificacion)<small class="text-muted">{{ $item->certificacion->fecha_certificacion?->format('d/m/Y') }}</small>@elseif($item->cumplido)<form method="POST" action="{{ route('controlecp.instructor.certificados.certificar', $item->clave) }}">@csrf<button class="btn btn-success btn-sm" onclick="return confirm('¿Confirmar certificación de este momento?')"><i class="fas fa-certificate me-1"></i>Certificar</button></form>@else<span class="text-muted small">Faltan actividades</span>@endif</td></tr>
            @empty<tr><td colspan="5" class="text-center text-muted py-5">No hay fichas con actividades asignadas para certificar.</td></tr>@endforelse
            </tbody>
        </table></div>
    </div>
    <div class="card border-0 shadow-sm" style="border-radius: 18px;"><div class="card-header bg-white fw-bold">Mis certificaciones</div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Ficha</th><th>Momento</th><th>Fecha</th><th>Estado</th></tr></thead><tbody>@forelse($historial as $certificacion)<tr><td>{{ $certificacion->ficha?->code }}</td><td>{{ $certificacion->momento?->nombre }}</td><td>{{ $certificacion->fecha_certificacion?->format('d/m/Y H:i') }}</td><td><span class="badge text-bg-success">{{ $certificacion->estado }}</span></td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-4">Aún no has certificado momentos.</td></tr>@endforelse</tbody></table></div></div>

</div>
@endsection
