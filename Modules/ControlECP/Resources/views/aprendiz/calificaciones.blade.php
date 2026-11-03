@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <h2 class="h3 fw-bold mb-1" style="color:#001A29;">
                <i class="fas fa-star me-2" style="color:#39A900;"></i>Mis calificaciones
            </h2>
            <p class="text-muted small mb-0">Califica las actividades finalizadas a las que asististe.</p>
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

    <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Actividad</th><th>Momento</th><th>Fecha</th><th>Estado</th><th class="text-end">Acción</th></tr>
                </thead>
                <tbody>
                    @forelse($sesiones as $sesion)
                        @php($calificacion = $sesion->calificacion_aprendiz)
                        <tr>
                            <td class="fw-semibold">{{ $sesion->actividad?->nombre ?? $sesion->titulo_calendario }}</td>
                            <td>{{ $sesion->actividad?->momento?->nombre ?? 'Sin momento' }}</td>
                            <td>{{ $sesion->fecha?->format('d/m/Y') }}</td>
                            <td>
                                @if($calificacion)
                                    <span class="badge bg-success-subtle text-success border rounded-pill">
                                        <i class="fas fa-check me-1"></i>Calificada · {{ $calificacion->estrellas }}/5
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis border rounded-pill">Pendiente</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($calificacion)
                                    <span class="text-warning" title="Calificación registrada">
                                        @for($estrella = 1; $estrella <= 5; $estrella++)
                                            <i class="fas fa-star{{ $estrella <= $calificacion->estrellas ? '' : '-o' }}"></i>
                                        @endfor
                                    </span>
                                @else
                                    <a href="{{ route('controlecp.aprendiz.calificaciones.calificar', $sesion->id_cronograma) }}"
                                       class="btn text-white rounded-pill btn-sm px-3" style="background:#39A900;">
                                        Calificar
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-5">No tienes actividades disponibles para calificar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4" style="border-radius:16px;">
        <div class="card-header bg-white border-0 pt-3 px-4">
            <h5 class="fw-bold mb-0" style="color:#001A29;">
                <i class="fas fa-comments me-2" style="color:#39A900;"></i>Mis asesorías
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Asesoría</th><th>Instructor</th><th>Fecha</th><th>Estado</th><th class="text-end">Acción</th></tr>
                </thead>
                <tbody>
                    @forelse($asesorias as $asesoria)
                        @php($calificacion = $asesoria->calificacion_aprendiz)
                        <tr>
                            <td class="fw-semibold">{{ $asesoria->categoria?->nombre ?? 'Asesoría' }}</td>
                            <td>{{ $asesoria->instructor?->full_name ?? 'Sin instructor' }}</td>
                            <td>{{ $asesoria->fecha?->format('d/m/Y') }}</td>
                            <td>
                                @if($calificacion)
                                    <span class="badge bg-success-subtle text-success border rounded-pill">
                                        <i class="fas fa-check me-1"></i>Calificada · {{ $calificacion->estrellas }}/5
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis border rounded-pill">Pendiente</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($calificacion)
                                    <span class="text-warning">{{ $calificacion->estrellas }}/5 <i class="fas fa-star"></i></span>
                                @else
                                    <a href="{{ route('controlecp.aprendiz.calificaciones.asesoria.calificar', $asesoria->id_asesoria) }}"
                                       class="btn text-white rounded-pill btn-sm px-3" style="background:#39A900;">
                                        Calificar
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No tienes asesorías finalizadas para calificar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
