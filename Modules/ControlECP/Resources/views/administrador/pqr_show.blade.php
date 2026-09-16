@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">

    {{-- Cabecera --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.administrador.pqr') }}" class="text-decoration-none" style="color:#2E7D32;">
                            <i class="fas fa-comment-dots me-1"></i>PQR
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page" style="color:#1B5E20; font-weight:600;">
                        {{ $pqr->radicado }}
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color:#001A29; font-family:'Outfit', sans-serif;">
                <i class="fas {{ $pqr->iconoTipo() }} me-2" style="color:#39A900;"></i>{{ $pqr->asunto }}
            </h2>
            <p class="text-muted small mb-0">
                Radicado <strong>{{ $pqr->radicado }}</strong> &bull; {{ $pqr->tipo }} &bull;
                {{ $pqr->created_at->format('d/m/Y h:i A') }}
            </p>
        </div>

        <a href="{{ route('controlecp.administrador.pqr') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Volver al listado
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3">

        {{-- Columna izquierda: detalle --}}
        <div class="col-lg-7">

            {{-- Solicitud --}}
            <div class="card border-0 shadow-sm mb-3" style="border-radius:16px;">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0" style="color:#001A29;">
                        <i class="fas fa-file-lines me-2" style="color:#39A900;"></i>Solicitud
                    </h5>
                    <span class="badge rounded-pill px-3 py-2 {{ $pqr->colorEstado() }}">{{ $pqr->estado }}</span>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="p-3 rounded-3 mb-3" style="background:#f8f9fa; white-space:pre-line; line-height:1.7;">{{ $pqr->mensaje }}</div>

                    @if($pqr->anexo_url)
                        <a href="{{ $pqr->anexo_url }}" target="_blank"
                           class="btn btn-sm btn-outline-success rounded-pill px-3">
                            <i class="fas fa-paperclip me-1"></i> Ver documento anexo
                        </a>
                    @else
                        <span class="text-muted small"><i class="fas fa-paperclip me-1"></i>Sin documento anexo</span>
                    @endif
                </div>
            </div>

            {{-- Respuesta registrada --}}
            @if($pqr->estaRespondida())
                <div class="card border-0 shadow-sm mb-3" style="border-radius:16px; border-left:5px solid #39A900 !important;">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                        <h5 class="fw-bold mb-0" style="color:#001A29;">
                            <i class="fas fa-reply me-2" style="color:#39A900;"></i>Respuesta institucional
                        </h5>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="p-3 rounded-3 mb-3" style="background:#eaf9ee; white-space:pre-line; line-height:1.7;">{{ $pqr->respuesta }}</div>
                        <div class="d-flex flex-wrap gap-3 small text-muted">
                            <span><i class="fas fa-user-tie me-1"></i>
                                {{ $pqr->respondidoPor->full_name ?? $pqr->respondidoPor->name ?? 'Sistema' }}
                            </span>
                            <span><i class="fas fa-calendar-check me-1"></i>
                                {{ $pqr->fecha_respuesta?->format('d/m/Y h:i A') }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Formulario de respuesta --}}
            <div class="card border-0 shadow-sm" style="border-radius:16px;">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold mb-0" style="color:#001A29;">
                        <i class="fas fa-pen-to-square me-2" style="color:#39A900;"></i>
                        {{ $pqr->estaRespondida() ? 'Actualizar respuesta' : 'Responder solicitud' }}
                    </h5>
                </div>
                <div class="card-body px-4 pb-4">

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('controlecp.administrador.pqr.responder', $pqr->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Respuesta <span class="text-danger">*</span></label>
                            <textarea name="respuesta" rows="5"
                                      class="form-control @error('respuesta') is-invalid @enderror"
                                      placeholder="Redacta la respuesta institucional a esta solicitud...">{{ old('respuesta', $pqr->respuesta) }}</textarea>
                            @error('respuesta')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-2 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Estado de la PQR <span class="text-danger">*</span></label>
                                <select name="estado" class="form-select @error('estado') is-invalid @enderror">
                                    @foreach($estados as $est)
                                        <option value="{{ $est }}" {{ old('estado', $pqr->estado) == $est ? 'selected' : '' }}>{{ $est }}</option>
                                    @endforeach
                                </select>
                                @error('estado')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 text-md-end">
                                <button type="submit" class="btn text-white rounded-pill px-4 shadow-sm w-100 w-md-auto"
                                        style="background-color:#39A900;">
                                    <i class="fas fa-paper-plane me-1"></i> Guardar respuesta
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        {{-- Columna derecha: información --}}
        <div class="col-lg-5">

            {{-- Solicitante --}}
            <div class="card border-0 shadow-sm mb-3" style="border-radius:16px;">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h6 class="fw-bold mb-0" style="color:#001A29;">
                        <i class="fas fa-user me-2" style="color:#39A900;"></i>Solicitante
                    </h6>
                </div>
                <div class="card-body px-4 pb-4">
                    <table class="table table-sm mb-0 align-middle">
                        <tbody class="small">
                            <tr>
                                <th class="text-muted ps-0" style="width:38%;">Nombre</th>
                                <td class="fw-semibold">{{ $pqr->nombre_solicitante }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted ps-0">Perfil</th>
                                <td><span class="badge" style="background:#d8f3dc; color:#1B5E20;">{{ $pqr->perfil }}</span></td>
                            </tr>
                            <tr>
                                <th class="text-muted ps-0">Documento</th>
                                <td>{{ $pqr->documento ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted ps-0">Correo</th>
                                <td><a href="mailto:{{ $pqr->email }}" class="text-decoration-none" style="color:#2E7D32;">{{ $pqr->email }}</a></td>
                            </tr>
                            <tr>
                                <th class="text-muted ps-0">Teléfono</th>
                                <td>{{ $pqr->telefono ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Trazabilidad --}}
            <div class="card border-0 shadow-sm mb-3" style="border-radius:16px;">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h6 class="fw-bold mb-0" style="color:#001A29;">
                        <i class="fas fa-clock-rotate-left me-2" style="color:#39A900;"></i>Trazabilidad
                    </h6>
                </div>
                <div class="card-body px-4 pb-4">
                    <table class="table table-sm mb-0 align-middle">
                        <tbody class="small">
                            <tr>
                                <th class="text-muted ps-0" style="width:38%;">Radicado</th>
                                <td><span class="badge bg-light text-dark border fw-bold">{{ $pqr->radicado }}</span></td>
                            </tr>
                            <tr>
                                <th class="text-muted ps-0">Tipo</th>
                                <td><i class="fas {{ $pqr->iconoTipo() }} me-1" style="color:#39A900;"></i>{{ $pqr->tipo }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted ps-0">Prioridad</th>
                                <td>
                                    @php
                                        $pr = match($pqr->prioridad) {
                                            'Alta'  => 'bg-danger',
                                            'Media' => 'bg-warning text-dark',
                                            default => 'bg-light text-dark border',
                                        };
                                    @endphp
                                    <span class="badge rounded-pill px-3 {{ $pr }}">{{ $pqr->prioridad }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted ps-0">Radicada</th>
                                <td>{{ $pqr->created_at->format('d/m/Y h:i A') }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted ps-0">Antigüedad</th>
                                <td>{{ $pqr->diasTranscurridos() }} día(s)</td>
                            </tr>
                            <tr>
                                <th class="text-muted ps-0">Estado</th>
                                <td><span class="badge rounded-pill px-3 py-2 {{ $pqr->colorEstado() }}">{{ $pqr->estado }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Eliminar --}}
            <div class="card border-0 shadow-sm" style="border-radius:16px;">
                <div class="card-body p-4 text-center">
                    <p class="small text-muted mb-3">
                        Esta acción elimina la PQR y su documento anexo del sistema.
                    </p>
                    <form action="{{ route('controlecp.administrador.pqr.destroy', $pqr->id) }}" method="POST"
                          onsubmit="return confirm('¿Eliminar definitivamente la PQR {{ $pqr->radicado }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger rounded-pill px-4">
                            <i class="fas fa-trash me-1"></i> Eliminar PQR
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
