@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">
    <div class="mb-4 pb-2 border-bottom">
        <a href="{{ route('controlecp.aprendiz.encuesta') }}" class="small text-success text-decoration-none">
            <i class="fas fa-arrow-left me-1"></i>Volver a mis encuestas
        </a>
        <h2 class="h3 fw-bold mt-2 mb-1" style="color:#001A29;">{{ $sesion->actividad->encuesta->nombre }}</h2>
        <p class="text-muted small mb-0">{{ $sesion->actividad->nombre }} · {{ $sesion->fecha?->format('d/m/Y') }}</p>
    </div>

    <form method="POST" action="{{ route('controlecp.aprendiz.encuesta.guardar', $sesion->id_cronograma) }}">
        @csrf
        @foreach($sesion->actividad->encuesta->preguntas as $pregunta)
            <div class="card border-0 shadow-sm mb-3" style="border-radius:16px;">
                <div class="card-body p-4">
                    <label class="fw-semibold d-block mb-3">
                        {{ $loop->iteration }}. {{ $pregunta->texto }}
                        @if($pregunta->obligatoria)<span class="text-danger">*</span>@endif
                    </label>
                    @if($pregunta->isOpcionMultiple())
                        @foreach($pregunta->opciones as $opcion)
                            <label class="d-block mb-2">
                                <input type="radio" name="respuestas[{{ $pregunta->id_pregunta }}]"
                                       value="{{ $opcion->id_opcion_pregunta }}" class="form-check-input me-2"
                                       {{ $pregunta->obligatoria && $loop->first ? 'required' : '' }}
                                       {{ old('respuestas.' . $pregunta->id_pregunta) == $opcion->id_opcion_pregunta ? 'checked' : '' }}>
                                {{ $opcion->texto }}
                            </label>
                        @endforeach
                    @elseif($pregunta->isEscala())
                        <select name="respuestas[{{ $pregunta->id_pregunta }}]" class="form-select" {{ $pregunta->obligatoria ? 'required' : '' }}>
                            <option value="">Selecciona una calificación</option>
                            @for($valor = 1; $valor <= 5; $valor++)
                                <option value="{{ $valor }}" {{ old('respuestas.' . $pregunta->id_pregunta) == $valor ? 'selected' : '' }}>{{ $valor }}</option>
                            @endfor
                        </select>
                    @else
                        <textarea name="respuestas[{{ $pregunta->id_pregunta }}]" class="form-control" rows="3"
                                  {{ $pregunta->obligatoria ? 'required' : '' }}>{{ old('respuestas.' . $pregunta->id_pregunta) }}</textarea>
                    @endif
                    @error('respuestas.' . $pregunta->id_pregunta)
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        @endforeach
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('controlecp.aprendiz.encuesta') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancelar</a>
            <button type="submit" class="btn text-white rounded-pill px-4" style="background:#39A900;">
                <i class="fas fa-paper-plane me-1"></i>Enviar encuesta
            </button>
        </div>
    </form>
</div>
@endsection
