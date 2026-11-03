{{-- Cronograma de actividades de la ficha del aprendiz. Vista de solo lectura. --}}
@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .semaforo-indicator { width: 14px; height: 14px; border-radius: 50%; display: inline-block; }
    .semaforo-verde { background-color: #39A900; box-shadow: 0 0 8px rgba(57,169,0,.6); }
    .semaforo-amarillo { background-color: #f57f17; box-shadow: 0 0 8px rgba(245,127,23,.6); }
    .semaforo-rojo { background-color: #d32f2f; box-shadow: 0 0 8px rgba(211,47,47,.6); }
    .calendar-day { min-height: 82px; border: 1px solid #e8eee9; background: #fff; }
    .calendar-day.has-event { background: #f0fbf4; border-color: #bce5c6; }
</style>
@endpush

@section('dashboard-content')
@php
    $hoy = now()->startOfDay();
    $inicioMes = $hoy->copy()->startOfMonth();
    $finMes = $hoy->copy()->endOfMonth();
    $inicioCalendario = $inicioMes->copy()->startOfWeek();
    $finCalendario = $finMes->copy()->endOfWeek();
    $sesionesPorFecha = $sesiones->groupBy(fn($sesion) => $sesion->fecha?->format('Y-m-d'));
@endphp

<div class="container-fluid px-2 px-md-3 py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.aprendiz.inicio') }}" class="text-decoration-none" style="color:#39A900;">
                            <i class="fas fa-house me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Mi Cronograma</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color:#001A29;font-family:'Outfit',sans-serif;">
                <i class="fas fa-calendar-days me-2" style="color:#39A900;"></i>Mi Cronograma
            </h2>
            <p class="text-muted small mb-0">Actividades programadas únicamente para tu ficha de formación</p>
        </div>
        <a href="{{ route('controlecp.aprendiz.inicio') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Volver al Inicio
        </a>
    </div>

    @if($fichas->isNotEmpty())
        <div class="alert alert-light border shadow-sm mb-4">
            <i class="fas fa-layer-group me-2 text-success"></i>
            <strong>Ficha{{ $fichas->count() > 1 ? 's' : '' }}:</strong>
            {{ $fichas->map(fn($ficha) => $ficha->code . ($ficha->program ? ' - ' . $ficha->program->name : ''))->implode(' | ') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
        <div class="card-header bg-white border-0 pt-3 px-4">
            <h5 class="fw-bold mb-0" style="color:#001A29;">
                <i class="fas fa-calendar-week me-2 text-success"></i>Calendario de actividades
            </h5>
            <div class="small text-muted mt-1">{{ $inicioMes->translatedFormat('F Y') }}</div>
        </div>
        <div class="card-body p-3">
            <div class="row row-cols-7 g-1 text-center small fw-bold text-muted mb-1">
                @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $dia)
                    <div class="col">{{ $dia }}</div>
                @endforeach
            </div>
            <div class="row row-cols-7 g-1">
                @for($fecha = $inicioCalendario->copy(); $fecha->lte($finCalendario); $fecha->addDay())
                    @php
                        $clave = $fecha->format('Y-m-d');
                        $eventos = $sesionesPorFecha->get($clave, collect());
                    @endphp
                    <div class="col">
                        <div class="calendar-day rounded p-2 text-start {{ $eventos->isNotEmpty() ? 'has-event' : '' }}">
                            <div class="fw-bold {{ $fecha->month === $inicioMes->month ? 'text-dark' : 'text-muted' }}">{{ $fecha->day }}</div>
                            @foreach($eventos->take(2) as $evento)
                                <button type="button" class="btn btn-link p-0 text-start small text-success text-decoration-none d-block text-truncate w-100"
                                        data-bs-toggle="modal" data-bs-target="#detalleActividad{{ $evento->id_cronograma }}">
                                    {{ $evento->titulo_calendario }}
                                </button>
                            @endforeach
                            @if($eventos->count() > 2)
                                <span class="text-muted" style="font-size:.7rem;">+{{ $eventos->count() - 2 }} más</span>
                            @endif
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-header bg-white border-0 pt-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0" style="color:#001A29;">
                <i class="fas fa-list-check me-2 text-success"></i>Próximas actividades y novedades
            </h5>
            <span class="badge bg-success-subtle text-success border">{{ $sesiones->count() }} actividades</span>
        </div>
        <div class="card-body p-0">
            @forelse($sesiones as $sesion)
                @php
                    $sem = $sesion->semaforo;
                    $estadoClase = $sesion->estado === 'Finalizada' ? 'success' : ($sesion->estado === 'Pospuesta' ? 'secondary' : 'warning');
                @endphp
                <button type="button" class="w-100 text-start border-0 border-bottom bg-white px-4 py-3"
                        data-bs-toggle="modal" data-bs-target="#detalleActividad{{ $sesion->id_cronograma }}">
                    <div class="row align-items-center g-3">
                        <div class="col-auto">
                            @if($sem)
                                <span class="semaforo-indicator semaforo-{{ $sem }}" title="{{ $sesion->semaforo_texto }}"></span>
                            @else
                                <span class="semaforo-indicator bg-secondary-subtle"></span>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <div class="fw-bold text-dark">{{ $sesion->titulo_calendario }}</div>
                            <div class="small text-muted">{{ $sesion->fecha?->format('d/m/Y') }} · {{ $sesion->horario_formateado }}</div>
                        </div>
                        <div class="col-md-3 small text-muted">
                            <i class="fas fa-location-dot me-1"></i>{{ $sesion->lugar ?: 'Lugar por confirmar' }}
                        </div>
                        <div class="col-md-2 small text-muted">
                            <i class="fas fa-user-tie me-1"></i>{{ $sesion->instructor?->full_name ?? 'Sin instructor' }}
                        </div>
                        <div class="col-md-2">
                            <span class="badge bg-{{ $estadoClase }}-subtle text-{{ $estadoClase }} border">{{ $sesion->estado }}</span>
                            @if($sesion->estado === 'Pospuesta' && $sesion->fecha_original)
                                <div class="small text-muted mt-1">Nueva fecha: {{ $sesion->fecha?->format('d/m/Y') }}</div>
                            @endif
                        </div>
                        <div class="col text-end"><i class="fas fa-chevron-right text-muted"></i></div>
                    </div>
                </button>
            @empty
                <div class="text-center text-muted py-5">
                    <i class="fas fa-calendar-xmark fa-2x mb-3"></i>
                    <p class="mb-0">No hay actividades programadas para tu ficha.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@foreach($sesiones as $sesion)
    <div class="modal fade" id="detalleActividad{{ $sesion->id_cronograma }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius:18px;">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" style="color:#001A29;">{{ $sesion->titulo_calendario }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5">Momento</dt><dd class="col-7">{{ $sesion->actividad?->momento?->nombre ?? 'Sin momento' }}</dd>
                        <dt class="col-5">Fecha</dt><dd class="col-7">{{ $sesion->fecha?->format('d/m/Y') ?? 'Sin fecha' }}</dd>
                        <dt class="col-5">Hora</dt><dd class="col-7">{{ $sesion->horario_formateado }}</dd>
                        <dt class="col-5">Lugar</dt><dd class="col-7">{{ $sesion->lugar ?: 'Por confirmar' }}</dd>
                        <dt class="col-5">Instructor</dt><dd class="col-7">{{ $sesion->instructor?->full_name ?? 'Sin instructor' }}</dd>
                        <dt class="col-5">Estado</dt><dd class="col-7">{{ $sesion->estado }}</dd>
                        @if($sesion->estado === 'Pospuesta')
                            <dt class="col-5">Fecha anterior</dt><dd class="col-7">{{ $sesion->fecha_original?->format('d/m/Y') ?? 'No registrada' }}</dd>
                            <dt class="col-5">Motivo</dt><dd class="col-7">{{ $sesion->motivo_posposicion ?: 'No especificado' }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection
