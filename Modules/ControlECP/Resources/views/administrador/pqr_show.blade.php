@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .timeline-item {
        position: relative;
        padding-left: 2rem;
        padding-bottom: 1.4rem;
        border-left: 2px solid #E2E8F0;
    }
    .timeline-item:last-child { border-left-color: transparent; padding-bottom: 0; }
    .timeline-dot {
        position: absolute; left: -7px; top: 3px;
        width: 12px; height: 12px;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px #E2E8F0;
    }
    .av-badge {
        width: 42px; height: 42px;
        border-radius: 50%;
        background: #E2F5D7; color: #39A900;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 1rem;
        border: 1.5px solid #A3E070;
        flex-shrink: 0;
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-2 px-md-4 py-3">

    {{-- ══ CABECERA ══ --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.administrador.pqr') }}"
                           class="text-decoration-none fw-semibold" style="color:#39A900;">
                            <i class="fas fa-arrow-left me-1"></i>Bandeja PQRS
                        </a>
                    </li>
                    <li class="breadcrumb-item active fw-semibold" style="color:#001A29;">
                        {{ $pqr->radicado }}
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color:#001A29; font-family:'Outfit',sans-serif;">
                {{ $pqr->asunto }}
            </h2>
            <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                <span class="badge rounded-pill border px-3 py-1 fw-semibold"
                      style="background:{{ $pqr->estado_badge['bg'] }};
                             color:{{ $pqr->estado_badge['text'] }};
                             border-color:{{ $pqr->estado_badge['border'] }} !important;
                             font-size:.78rem;">
                    <i class="{{ $pqr->estado_badge['icon'] }} me-1"></i>{{ $pqr->estado }}
                </span>
                @if($pqr->tipo)
                <span class="badge rounded-pill border px-3 py-1"
                      style="background:{{ $pqr->tipo_badge['bg'] }};
                             color:{{ $pqr->tipo_badge['text'] }};
                             border-color:{{ $pqr->tipo_badge['border'] }} !important;
                             font-size:.78rem;">
                    <i class="{{ $pqr->tipo_badge['icon'] }} me-1"></i>{{ $pqr->tipo->nombre }}
                </span>
                @endif
                <span class="text-muted small">
                    Radicado: <strong>{{ $pqr->radicado }}</strong>
                    &bull; {{ $pqr->fecha_radicacion?->format('d/m/Y H:i') }}
                </span>
            </div>
        </div>
        <a href="{{ route('controlecp.administrador.pqr') }}"
           class="btn btn-outline-secondary rounded-pill px-4 btn-sm shadow-sm">
            <i class="fas fa-arrow-left me-1"></i>Volver al listado
        </a>
    </div>

    {{-- ══ ALERTAS ══ --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4"
             style="background:#f0fbf4; color:#001A29;">
            <i class="fas fa-check-circle me-2" style="color:#39A900;"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4">
            <ul class="mb-0 small ps-3">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">

        {{-- ══ COLUMNA IZQUIERDA ══ --}}
        <div class="col-lg-7">

            {{-- Contenido de la solicitud --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold mb-0" style="color:#001A29;">
                        <i class="fas fa-file-lines me-2" style="color:#39A900;"></i>Solicitud
                    </h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="p-3 rounded-3 border text-dark"
                         style="background:#F8FAFC; white-space:pre-wrap; line-height:1.8; font-size:.93rem;">{{ $pqr->descripcion }}</div>

                    @if($pqr->anexo_url ?? false)
                        <a href="{{ $pqr->anexo_url }}" target="_blank"
                           class="btn btn-sm btn-outline-success rounded-pill px-3 mt-3">
                            <i class="fas fa-paperclip me-1"></i>Ver documento anexo
                        </a>
                    @endif
                </div>
            </div>

            {{-- Hilo de respuestas existentes (HU 7) --}}
            @if($pqr->respuestas->isNotEmpty())
            <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold mb-0" style="color:#001A29;">
                        <i class="fas fa-comments me-2" style="color:#39A900;"></i>
                        Respuestas registradas ({{ $pqr->respuestas->count() }})
                    </h5>
                </div>
                <div class="card-body px-4 pb-4">
                    @foreach($pqr->respuestas->sortByDesc('fecha') as $resp)
                    @php
                        $esAdmin = $resp->usuario?->hasRole('controlecp.admin');
                        $esInst  = $resp->usuario?->hasRole('controlecp.instructor');
                        $rol = $esAdmin ? 'Administrador' : ($esInst ? 'Instructor' : 'Usuario');
                        $colorBorder = $esAdmin ? '#39A900' : '#0284C7';
                        $colorBg     = $esAdmin ? '#F0FDF4' : '#EFF6FF';
                    @endphp
                    <div class="p-3 mb-3 rounded-3 border-start border-4"
                         style="border-color:{{ $colorBorder }} !important; background:{{ $colorBg }};">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <div class="av-badge" style="width:34px;height:34px;font-size:.85rem;">
                                    {{ strtoupper(substr($resp->usuario?->nickname ?? 'A', 0, 1)) }}
                                </div>
                                <div>
                                    <span class="fw-bold text-dark small">
                                        {{ $resp->usuario?->nickname ?? 'Sistema' }}
                                    </span>
                                    <span class="badge rounded-pill ms-1 px-2"
                                          style="font-size:.68rem; background:{{ $esAdmin ? '#E2F5D7' : '#DBEAFE' }}; color:{{ $esAdmin ? '#166534' : '#1E40AF' }};">
                                        {{ $rol }}
                                    </span>
                                </div>
                            </div>
                            <small class="text-muted" style="font-size:.72rem;">
                                {{ $resp->fecha?->format('d/m/Y H:i') }}
                            </small>
                        </div>
                        <div class="text-dark small" style="white-space:pre-wrap; line-height:1.7;">{{ $resp->respuesta }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- ══ FORMULARIO DE RESPUESTA (HU 7) ══ --}}
            <div class="card border-0 shadow-sm" style="border-radius:16px; border-top:3px solid #39A900 !important;">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold mb-0" style="color:#001A29;">
                        <i class="fas fa-pen-to-square me-2" style="color:#39A900;"></i>
                        {{ $pqr->respuestas->isNotEmpty() ? 'Agregar nueva respuesta' : 'Responder solicitud' }}
                    </h5>
                    <p class="text-muted small mb-0 mt-1">
                        Como administrador puedes responder y actualizar el estado de esta PQRS.
                    </p>
                </div>
                <div class="card-body px-4 pb-4">
                    <form action="{{ route('controlecp.administrador.pqr.responder', $pqr->id_pqrs) }}"
                          method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">
                                Respuesta oficial <span class="text-danger">*</span>
                            </label>
                            <textarea name="respuesta" rows="5"
                                      class="form-control rounded-3 @error('respuesta') is-invalid @enderror"
                                      placeholder="Redacta la respuesta institucional clara y formal…"
                                      minlength="10" required>{{ old('respuesta') }}</textarea>
                            @error('respuesta')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">
                                    Actualizar estado <span class="text-danger">*</span>
                                </label>
                                <select name="estado" class="form-select rounded-3 @error('estado') is-invalid @enderror">
                                    @foreach($estados as $est)
                                        <option value="{{ $est }}"
                                            {{ old('estado', $pqr->estado) === $est ? 'selected' : '' }}>
                                            {{ $est }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('estado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text small text-muted">
                                    Estado actual:
                                    <strong>{{ $pqr->estado }}</strong>
                                </div>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <button type="submit"
                                        class="btn text-white rounded-pill px-5 shadow-sm fw-semibold w-100 w-md-auto"
                                        style="background:#39A900; border-color:#39A900;">
                                    <i class="fas fa-paper-plane me-2"></i>Guardar respuesta
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>

        {{-- ══ COLUMNA DERECHA ══ --}}
        <div class="col-lg-5">

            {{-- Información del solicitante --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h6 class="fw-bold mb-0" style="color:#001A29;">
                        <i class="fas fa-user me-2" style="color:#39A900;"></i>Solicitante
                    </h6>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                        <div class="av-badge" style="width:46px;height:46px;font-size:1.1rem;">
                            {{ strtoupper(substr($pqr->aprendiz?->nickname ?? 'A', 0, 1)) }}
                        </div>
                        <div>
                            <div class="fw-bold text-dark">
                                {{ $pqr->aprendiz?->nickname ?? $pqr->aprendiz?->name ?? 'Aprendiz' }}
                            </div>
                            <small class="text-muted d-block">
                                {{ $pqr->aprendiz?->email ?? 'Sin correo registrado' }}
                            </small>
                        </div>
                    </div>
                    <div class="small">
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Destino / Asignado a:</span>
                            <strong class="text-dark">
                                {{ $pqr->instructorDestino?->nickname ?? 'Administración General' }}
                            </strong>
                        </div>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">Radicación:</span>
                            <strong class="text-dark">
                                {{ $pqr->fecha_radicacion?->format('d/m/Y H:i') ?? '—' }}
                            </strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Trazabilidad / Historial --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h6 class="fw-bold mb-0" style="color:#001A29;">
                        <i class="fas fa-clock-rotate-left me-2" style="color:#39A900;"></i>Historial de trazabilidad
                    </h6>
                </div>
                <div class="card-body px-4 pb-4">
                    @php
                        $todosEventos = collect();

                        // Radicación
                        $todosEventos->push([
                            'color' => '#0288D1',
                            'titulo' => 'Solicitud radicada',
                            'fecha' => $pqr->fecha_radicacion?->format('d/m/Y H:i') ?? '—',
                            'desc' => 'Estado inicial: Pendiente',
                            'autor' => $pqr->aprendiz?->nickname ?? 'Aprendiz',
                            'ts' => $pqr->fecha_radicacion?->timestamp ?? 0,
                        ]);

                        // Cambios de estado
                        foreach($pqr->historial as $h) {
                            $todosEventos->push([
                                'color' => '#F59E0B',
                                'titulo' => 'Estado: ' . ($h->estado_anterior ? $h->estado_anterior.' → ' : '') . $h->estado_nuevo,
                                'fecha' => $h->fecha?->format('d/m/Y H:i') ?? '—',
                                'desc' => $h->observacion ?? 'Sin observaciones',
                                'autor' => $h->usuario?->nickname ?? 'Sistema',
                                'ts' => $h->fecha?->timestamp ?? 0,
                            ]);
                        }

                        // Respuestas
                        foreach($pqr->respuestas as $r) {
                            $esAdmin = $r->usuario?->hasRole('controlecp.admin');
                            $todosEventos->push([
                                'color' => '#16A34A',
                                'titulo' => 'Respuesta ' . ($esAdmin ? 'del Administrador' : 'del Instructor'),
                                'fecha' => $r->fecha?->format('d/m/Y H:i') ?? '—',
                                'desc' => \Str::limit($r->respuesta, 80),
                                'autor' => $r->usuario?->nickname ?? 'Sistema',
                                'ts' => $r->fecha?->timestamp ?? 0,
                            ]);
                        }

                        $todosEventos = $todosEventos->sortByDesc('ts')->values();
                    @endphp

                    @if($todosEventos->isEmpty())
                        <div class="text-center py-3 text-muted small">Sin eventos registrados.</div>
                    @else
                        @foreach($todosEventos as $ev)
                        <div class="timeline-item">
                            <span class="timeline-dot"
                                  style="background:{{ $ev['color'] }};
                                         box-shadow:0 0 0 2px {{ $ev['color'] }}33;"></span>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-bold text-dark small">{{ $ev['titulo'] }}</span>
                                <small class="text-muted" style="font-size:.71rem;">{{ $ev['fecha'] }}</small>
                            </div>
                            <div class="text-muted small">{{ $ev['desc'] }}</div>
                            <div class="text-secondary mt-1" style="font-size:.71rem;">
                                Por: <strong>{{ $ev['autor'] }}</strong>
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- Acción: Eliminar --}}
            <div class="card border-0 shadow-sm" style="border-radius:16px;">
                <div class="card-body p-4 text-center">
                    <p class="small text-muted mb-3">
                        Esta acción elimina permanentemente la PQRS del sistema.
                    </p>
                    <form action="{{ route('controlecp.administrador.pqr.destroy', $pqr->id_pqrs) }}"
                          method="POST"
                          onsubmit="return confirm('¿Eliminar definitivamente la PQRS {{ $pqr->radicado }}? Esta acción no se puede deshacer.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger rounded-pill px-4">
                            <i class="fas fa-trash me-1"></i>Eliminar PQRS
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
