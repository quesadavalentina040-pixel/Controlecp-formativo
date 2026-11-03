@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .rating-option { color: #f0ad00; transition: transform .15s ease; }
    .rating-option:hover { transform: scale(1.12); }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">
    <div class="mb-4 pb-2 border-bottom">
        <a href="{{ route('controlecp.aprendiz.calificaciones') }}" class="text-decoration-none small" style="color:#39A900;">
            <i class="fas fa-arrow-left me-1"></i>Volver a mis calificaciones
        </a>
        <h2 class="h3 fw-bold mt-2 mb-1" style="color:#001A29;">
            <i class="fas fa-comments me-2" style="color:#39A900;"></i>Calificar asesoría
        </h2>
        <p class="text-muted small mb-0">
            {{ $asesoria->categoria?->nombre ?? 'Asesoría' }} · {{ $asesoria->fecha?->format('d/m/Y') }}
        </p>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius:16px; max-width:760px;">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('controlecp.aprendiz.calificaciones.asesoria.guardar', $asesoria->id_asesoria) }}">
                @csrf
                <label class="form-label fw-semibold">¿Cómo valoras esta asesoría? <span class="text-danger">*</span></label>
                <div class="d-flex gap-2 mb-1" aria-label="Selecciona de 1 a 5 estrellas">
                    @for($estrella = 1; $estrella <= 5; $estrella++)
                        <label class="rating-option fs-2" style="cursor:pointer;" title="{{ $estrella }} estrella{{ $estrella > 1 ? 's' : '' }}">
                            <input type="radio" name="estrellas" value="{{ $estrella }}" class="visually-hidden" {{ old('estrellas') == $estrella ? 'checked' : '' }}>
                            <i class="{{ old('estrellas') >= $estrella ? 'fas' : 'far' }} fa-star"></i>
                        </label>
                    @endfor
                </div>
                @error('estrellas')<div class="text-danger small mb-3">{{ $message }}</div>@enderror

                <div class="mb-3">
                    <label for="comentario" class="form-label fw-semibold">Comentario <span class="text-muted fw-normal">(opcional)</span></label>
                    <textarea id="comentario" name="comentario" rows="5" maxlength="2000" class="form-control @error('comentario') is-invalid @enderror"
                              placeholder="Comparte un comentario sobre la asesoría...">{{ old('comentario') }}</textarea>
                    @error('comentario')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn text-white rounded-pill px-4" style="background:#39A900;">
                    <i class="fas fa-paper-plane me-1"></i>Enviar calificación
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('input[name="estrellas"]').forEach((input) => {
        input.addEventListener('change', () => {
            document.querySelectorAll('input[name="estrellas"]').forEach((option) => {
                const icon = option.nextElementSibling;
                icon.className = `${option.value <= input.value ? 'fas' : 'far'} fa-star`;
            });
        });
    });
</script>
@endpush
