@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">
    <div class="mb-4 pb-2 border-bottom">
        <a href="{{ route('controlecp.aprendiz.pqr') }}" class="small text-success text-decoration-none">
            <i class="fas fa-arrow-left me-1"></i>Volver a mis PQRS
        </a>
        <h2 class="h3 fw-bold mt-2 mb-1" style="color:#001A29;">Radicar PQRS</h2>
        <p class="text-muted small mb-0">Completa los datos de tu petición, queja, reclamo, sugerencia o felicitación.</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('controlecp.aprendiz.pqr.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tipo de PQRS *</label>
                        <select name="id_tipo_pqrs" class="form-select" required>
                            <option value="">Selecciona un tipo</option>
                            @foreach($tipos as $tipo)
                                <option value="{{ $tipo->id_tipo_pqrs }}" @selected(old('id_tipo_pqrs') == $tipo->id_tipo_pqrs)>{{ $tipo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Instructor destinatario (opcional)</label>
                        <select name="id_usuario_instructor_destino" class="form-select">
                            <option value="">Administración de Control ECP</option>
                            @foreach($instructores as $instructor)
                                <option value="{{ $instructor->id }}" @selected(old('id_usuario_instructor_destino') == $instructor->id)>
                                    {{ $instructor->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Asunto *</label>
                        <input type="text" name="asunto" class="form-control" value="{{ old('asunto') }}" maxlength="255" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Descripción *</label>
                        <textarea name="descripcion" class="form-control" rows="7" minlength="10" maxlength="10000" required>{{ old('descripcion') }}</textarea>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('controlecp.aprendiz.pqr') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancelar</a>
                    <button type="submit" class="btn text-white rounded-pill px-4" style="background:#39A900;">
                        <i class="fas fa-paper-plane me-1"></i>Guardar PQRS
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
