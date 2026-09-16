@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">

    {{-- Cabecera --}}
    <div class="mb-4 pb-2 border-bottom">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small text-muted">
                <li class="breadcrumb-item">
                    <a href="{{ route('controlecp.administrador.pqr') }}" class="text-decoration-none" style="color:#2E7D32;">
                        <i class="fas fa-comment-dots me-1"></i>PQR
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page" style="color:#1B5E20; font-weight:600;">Radicar</li>
            </ol>
        </nav>
        <h2 class="h3 fw-bold mb-0" style="color:#001A29; font-family:'Outfit', sans-serif;">
            <i class="fas fa-file-circle-plus me-2" style="color:#39A900;"></i>Radicar Nueva PQR
        </h2>
        <p class="text-muted small mb-0">Registra una petición, queja, reclamo, sugerencia o felicitación</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0" style="border-radius:16px;">
                <div class="card-body p-4">

                    {{-- Radicado asignado --}}
                    <div class="alert d-flex align-items-center gap-3 border-0 mb-4"
                         style="background:linear-gradient(135deg,#E8F5E9,#C8E6C9); border-radius:12px;">
                        <i class="fas fa-hashtag fa-lg" style="color:#1B5E20;"></i>
                        <div>
                            <div class="small text-muted">Número de radicado asignado</div>
                            <div class="fw-bold h5 mb-0" style="color:#1B5E20;">{{ $radicado }}</div>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <strong><i class="fas fa-exclamation-triangle me-2"></i>Corrige los siguientes errores:</strong>
                            <ul class="mb-0 mt-2 ms-3">
                                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('controlecp.administrador.pqr.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- Tipo de solicitud (selector visual) --}}
                        <label class="form-label fw-semibold">Tipo de solicitud <span class="text-danger">*</span></label>
                        <div class="row g-2 mb-4">
                            @php
                                $iconos = [
                                    'Petición'     => 'fa-hand-holding-heart',
                                    'Queja'        => 'fa-face-frown',
                                    'Reclamo'      => 'fa-triangle-exclamation',
                                    'Sugerencia'   => 'fa-lightbulb',
                                    'Felicitación' => 'fa-star',
                                ];
                                $tipoSel = old('tipo', 'Petición');
                            @endphp
                            @foreach($tipos as $tipo)
                                <div class="col-6 col-md-4 col-lg">
                                    <input type="radio" name="tipo" value="{{ $tipo }}"
                                           id="tipo-{{ Str::slug($tipo) }}" class="tipo-radio d-none"
                                           {{ $tipoSel == $tipo ? 'checked' : '' }} required>
                                    <label for="tipo-{{ Str::slug($tipo) }}" class="tipo-card">
                                        <i class="fas {{ $iconos[$tipo] }}"></i>
                                        <span>{{ $tipo }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        {{-- Datos del solicitante --}}
                        <h6 class="fw-bold mb-3 pb-2 border-bottom" style="color:#1B5E20;">
                            <i class="fas fa-user me-1"></i> Datos del solicitante
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nombre completo <span class="text-danger">*</span></label>
                                <input type="text" name="nombre_solicitante" value="{{ old('nombre_solicitante') }}"
                                       class="form-control @error('nombre_solicitante') is-invalid @enderror" required>
                                @error('nombre_solicitante')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Documento</label>
                                <input type="text" name="documento" value="{{ old('documento') }}"
                                       class="form-control @error('documento') is-invalid @enderror">
                                @error('documento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Perfil <span class="text-danger">*</span></label>
                                <select name="perfil" class="form-select @error('perfil') is-invalid @enderror">
                                    @foreach($perfiles as $p)
                                        <option value="{{ $p }}" {{ old('perfil') == $p ? 'selected' : '' }}>{{ $p }}</option>
                                    @endforeach
                                </select>
                                @error('perfil')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Correo electrónico <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-envelope text-muted"></i></span>
                                    <input type="email" name="email" value="{{ old('email') }}"
                                           class="form-control @error('email') is-invalid @enderror"
                                           placeholder="ejemplo@sena.edu.co" required>
                                </div>
                                @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Teléfono</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-phone text-muted"></i></span>
                                    <input type="text" name="telefono" value="{{ old('telefono') }}"
                                           class="form-control @error('telefono') is-invalid @enderror">
                                </div>
                                @error('telefono')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Detalle de la solicitud --}}
                        <h6 class="fw-bold mb-3 pb-2 border-bottom" style="color:#1B5E20;">
                            <i class="fas fa-file-lines me-1"></i> Detalle de la solicitud
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-9">
                                <label class="form-label fw-semibold">Asunto <span class="text-danger">*</span></label>
                                <input type="text" name="asunto" value="{{ old('asunto') }}"
                                       class="form-control @error('asunto') is-invalid @enderror"
                                       placeholder="Resumen breve de la solicitud" required>
                                @error('asunto')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Prioridad <span class="text-danger">*</span></label>
                                <select name="prioridad" class="form-select @error('prioridad') is-invalid @enderror">
                                    @foreach($prioridades as $pr)
                                        <option value="{{ $pr }}" {{ old('prioridad', 'Media') == $pr ? 'selected' : '' }}>{{ $pr }}</option>
                                    @endforeach
                                </select>
                                @error('prioridad')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Mensaje <span class="text-danger">*</span></label>
                                <textarea name="mensaje" rows="5"
                                          class="form-control @error('mensaje') is-invalid @enderror"
                                          placeholder="Describe con detalle tu petición, queja, reclamo o sugerencia...">{{ old('mensaje') }}</textarea>
                                @error('mensaje')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Anexo --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">Documento anexo (opcional)</label>
                                <label for="anexoInput" class="anexo-drop" id="anexoDrop">
                                    <div id="anexoPlaceholder" class="d-flex flex-column align-items-center gap-1">
                                        <i class="fas fa-paperclip"></i>
                                        <span class="fw-semibold">Clic para adjuntar archivo</span>
                                        <small class="text-muted">PDF, Word o imagen &bull; máx 4 MB</small>
                                    </div>
                                    <div id="anexoInfo" class="d-none align-items-center gap-2">
                                        <i class="fas fa-file-circle-check" style="color:#39A900;"></i>
                                        <span id="anexoNombre" class="fw-semibold small"></span>
                                    </div>
                                </label>
                                <input type="file" name="anexo" id="anexoInput" class="d-none"
                                       accept=".pdf,.doc,.docx,image/jpeg,image/png,image/webp">
                                @error('anexo')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('controlecp.administrador.pqr') }}" class="btn btn-secondary rounded-pill px-4">
                                Cancelar
                            </a>
                            <button type="submit" class="btn text-white rounded-pill px-4 shadow-sm" style="background-color:#39A900;">
                                <i class="fas fa-paper-plane me-1"></i> Radicar PQR
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

</div>

@push('styles')
<style>
    /* Tarjetas de tipo de solicitud */
    .tipo-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 100%;
        padding: 14px 8px;
        background: #fafafa;
        border: 2px solid #ececec;
        border-radius: 12px;
        cursor: pointer;
        transition: all .2s ease;
        text-align: center;
    }
    .tipo-card i { font-size: 1.5rem; color: #b0b0b0; transition: color .2s ease; }
    .tipo-card span { font-size: .8rem; font-weight: 600; color: #666; }
    .tipo-card:hover { border-color: #a8d5a2; background: #f5faf5; }
    .tipo-radio:checked + .tipo-card {
        border-color: #39A900;
        background: #eaf9ee;
        box-shadow: 0 4px 12px rgba(57,169,0,.18);
    }
    .tipo-radio:checked + .tipo-card i { color: #39A900; }
    .tipo-radio:checked + .tipo-card span { color: #1B5E20; }

    /* Zona de anexo */
    .anexo-drop {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 110px;
        background: #fafafa;
        border: 2px dashed #d5d5d5;
        border-radius: 12px;
        cursor: pointer;
        color: #a8a29a;
        transition: all .2s ease;
    }
    .anexo-drop:hover { border-color: #39A900; background: #f5faf5; }
    .anexo-drop i { font-size: 1.8rem; }
</style>
@endpush

@push('scripts')
<script>
    const anexoInput       = document.getElementById('anexoInput');
    const anexoPlaceholder = document.getElementById('anexoPlaceholder');
    const anexoInfo        = document.getElementById('anexoInfo');
    const anexoNombre      = document.getElementById('anexoNombre');

    anexoInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        anexoNombre.textContent = file.name;
        anexoPlaceholder.classList.add('d-none');
        anexoInfo.classList.remove('d-none');
        anexoInfo.classList.add('d-flex');
    });
</script>
@endpush
@endsection
