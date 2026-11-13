@extends('controlecp::components.layouts.dashboard')

@section('title', 'Gestionar encuesta')

@push('styles')
<style>
    .encuesta-gestion {
        --enc-verde: #39A900;
        --enc-petroleo: #001A29;
        --enc-borde: #e2e8f0;
        color: var(--enc-petroleo);
    }

    .encuesta-gestion .header-card,
    .encuesta-gestion .section-card {
        background: #fff;
        border: 1px solid var(--enc-borde);
        border-radius: 18px;
        box-shadow: 0 4px 20px rgba(0, 26, 41, .05);
    }

    .encuesta-gestion .header-card {
        padding: 1.25rem 1.5rem;
        color: #fff;
        background: var(--enc-petroleo);
        border: 0;
        border-radius: 24px;
    }

    .encuesta-gestion .question-card { border: 1px solid var(--enc-borde); border-radius: 14px; }
    .encuesta-gestion .status-active { color: #1b5e20; background: #d8f3dc; border: 1px solid #b7e4c7; }
    .encuesta-gestion .status-draft { color: #92400e; background: #fef3c7; border: 1px solid #fde68a; }
    .encuesta-gestion .status-inactive { color: #991b1b; background: #fee2e2; border: 1px solid #fecaca; }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid encuesta-gestion px-3 py-3">
    <div class="header-card d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small fw-semibold mb-1" style="color:var(--enc-verde)">
                <i class="fas fa-square-poll-vertical me-2"></i>Control ECP · Panel de instructor
            </div>
            <h1 class="h3 fw-bold mb-1">{{ $encuesta->nombre }}</h1>
            <p class="mb-0 small" style="color:#c7d4da">
                @if($esPropietario)
                    Gestiona los datos y las preguntas de tu encuesta.
                @else
                    Edita las preguntas de esta encuesta. El administrador que la creó recibirá una notificación por cada cambio.
                @endif
            </p>
        </div>
        <a class="btn btn-outline-light rounded-pill px-3" href="{{ route('controlecp.instructor.repositorio.encuestas') }}">
            <i class="fas fa-arrow-left me-1"></i>Volver a mis encuestas
        </a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    @php
        $claseEstado = ['Activa' => 'status-active', 'Borrador' => 'status-draft', 'Inactiva' => 'status-inactive'][$encuesta->estado] ?? 'status-inactive';
    @endphp
    <section class="section-card p-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h2 class="h5 fw-bold mb-1">Datos de la encuesta</h2>
                <span class="badge rounded-pill {{ $claseEstado }}">{{ $encuesta->estado }}</span>
                @if($tieneRespuestas)
                    <span class="badge rounded-pill text-bg-primary"><i class="fas fa-comments me-1"></i>{{ $encuesta->totalRespuestas() }} respuestas</span>
                @endif
            </div>
            @if($esPropietario)
                <div class="d-flex gap-2">
                    @if($encuesta->estado === 'Activa')
                        <form method="POST" action="{{ route('controlecp.instructor.encuestas.desactivar', $encuesta->id_encuesta) }}">
                            @csrf @method('PATCH')
                            <button class="btn btn-outline-danger rounded-pill" type="submit"><i class="fas fa-ban me-1"></i>Desactivar</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('controlecp.instructor.encuestas.activar', $encuesta->id_encuesta) }}">
                            @csrf @method('PATCH')
                            <button class="btn btn-success rounded-pill" type="submit"><i class="fas fa-circle-check me-1"></i>Activar</button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
        @if($esPropietario)
            <form method="POST" action="{{ route('controlecp.instructor.encuestas.update', $encuesta->id_encuesta) }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold" for="nombreEncuesta">Nombre <span class="text-danger">*</span></label>
                        <input id="nombreEncuesta" name="nombre" class="form-control" maxlength="255" value="{{ old('nombre', $encuesta->nombre) }}" required>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label fw-semibold" for="descripcionEncuesta">Descripción</label>
                        <textarea id="descripcionEncuesta" name="descripcion" class="form-control" rows="2" maxlength="2000">{{ old('descripcion', $encuesta->descripcion) }}</textarea>
                    </div>
                    <div class="col-12 text-end">
                        <button class="btn btn-primary rounded-pill px-4" type="submit"><i class="fas fa-save me-1"></i>Guardar datos</button>
                    </div>
                </div>
            </form>
        @else
            <div class="row g-3">
                <div class="col-md-5"><span class="form-label fw-semibold d-block">Nombre</span>{{ $encuesta->nombre }}</div>
                <div class="col-md-7"><span class="form-label fw-semibold d-block">Descripción</span>{{ $encuesta->descripcion ?: 'Sin descripción' }}</div>
            </div>
        @endif
    </section>

    @if($tieneRespuestas)
        <div class="alert alert-warning">
            <i class="fas fa-lock me-1"></i>Esta encuesta ya tiene respuestas registradas. Sus preguntas se muestran en solo lectura para proteger la consistencia de los resultados.
        </div>
    @else
        <section class="section-card p-4 mb-4">
            <h2 class="h5 fw-bold mb-3"><i class="fas fa-circle-plus text-success me-2"></i>Agregar pregunta</h2>
            <form method="POST" action="{{ route('controlecp.instructor.encuestas.preguntas.store', $encuesta->id_encuesta) }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold" for="textoPregunta">Texto de la pregunta <span class="text-danger">*</span></label>
                        <input id="textoPregunta" name="texto" class="form-control" maxlength="1000" value="{{ old('texto') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold" for="tipoPregunta">Tipo de respuesta</label>
                        <select id="tipoPregunta" name="tipo" class="form-select" required>
                            <option value="Opcion_multiple" @selected(old('tipo', 'Opcion_multiple') === 'Opcion_multiple')>Opción múltiple</option>
                            <option value="Escala" @selected(old('tipo') === 'Escala')>Escala de 1 a 5</option>
                            <option value="Texto" @selected(old('tipo') === 'Texto')>Texto abierto</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold" for="opcionesPregunta">Opciones (una por línea; mínimo dos para opción múltiple)</label>
                        <textarea id="opcionesPregunta" name="opciones_texto" class="form-control" rows="3" maxlength="2000"
                                  placeholder="Opción 1&#10;Opción 2">{{ old('opciones_texto') }}</textarea>
                    </div>
                    <div class="col-md-8 d-flex align-items-center">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="obligatoria" id="obligatoria" value="1" @checked(old('obligatoria', true))>
                            <label class="form-check-label fw-semibold" for="obligatoria">Pregunta obligatoria</label>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <button class="btn btn-success rounded-pill px-4" type="submit"><i class="fas fa-plus me-1"></i>Agregar pregunta</button>
                    </div>
                </div>
            </form>
        </section>
    @endif

    <section class="section-card p-4">
        <h2 class="h5 fw-bold mb-3"><i class="fas fa-list-check text-success me-2"></i>Preguntas de la encuesta <span class="text-muted">({{ $encuesta->preguntas->count() }})</span></h2>
        @forelse($encuesta->preguntas as $index => $pregunta)
            <article class="question-card p-3 mb-3">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                    <div class="flex-grow-1">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <span class="badge rounded-pill bg-light text-dark border">Pregunta {{ $index + 1 }}</span>
                            <span class="badge rounded-pill bg-light text-primary border">{{ str_replace('_', ' ', $pregunta->tipo) }}</span>
                            <span class="badge rounded-pill {{ $pregunta->obligatoria ? 'text-bg-danger' : 'text-bg-secondary' }}">{{ $pregunta->obligatoria ? 'Obligatoria' : 'Opcional' }}</span>
                        </div>
                        <h3 class="h6 fw-bold mb-2">{{ $pregunta->texto }}</h3>
                        @if($pregunta->tipo === 'Opcion_multiple')
                            <ul class="small text-muted mb-0">
                                @foreach($pregunta->opciones as $opcion)<li>{{ $opcion->texto }}</li>@endforeach
                            </ul>
                        @elseif($pregunta->tipo === 'Escala')
                            <div class="small text-muted">Escala fija: 1 a 5</div>
                        @else
                            <div class="small text-muted">Respuesta abierta</div>
                        @endif
                    </div>
                    @unless($tieneRespuestas)
                        <form method="POST" action="{{ route('controlecp.instructor.encuestas.preguntas.destroy', [$encuesta->id_encuesta, $pregunta->id_pregunta]) }}"
                              onsubmit="return confirm('¿Eliminar esta pregunta y sus opciones?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger rounded-pill" type="submit"><i class="fas fa-trash me-1"></i>Eliminar</button>
                        </form>
                    @endunless
                </div>

                @unless($tieneRespuestas)
                    <details class="mt-3 pt-3 border-top">
                        <summary class="small fw-semibold text-primary">Editar pregunta</summary>
                        <form method="POST" action="{{ route('controlecp.instructor.encuestas.preguntas.update', [$encuesta->id_encuesta, $pregunta->id_pregunta]) }}" class="row g-3 mt-1">
                            @csrf @method('PUT')
                            <div class="col-md-8">
                                <label class="form-label small fw-semibold">Texto</label>
                                <input name="texto" class="form-control" maxlength="1000" value="{{ $pregunta->texto }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Tipo</label>
                                <select name="tipo" class="form-select" required>
                                    <option value="Opcion_multiple" @selected($pregunta->tipo === 'Opcion_multiple')>Opción múltiple</option>
                                    <option value="Escala" @selected($pregunta->tipo === 'Escala')>Escala de 1 a 5</option>
                                    <option value="Texto" @selected($pregunta->tipo === 'Texto')>Texto abierto</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Opciones (una por línea)</label>
                                <textarea name="opciones_texto" class="form-control" rows="3" maxlength="2000">@foreach($pregunta->opciones as $opcion){{ $opcion->texto }}@if(!$loop->last){{ "\n" }}@endif @endforeach</textarea>
                            </div>
                            <div class="col-md-8 d-flex align-items-center">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="obligatoria" value="1" id="obligatoria-{{ $pregunta->id_pregunta }}" @checked($pregunta->obligatoria)>
                                    <label class="form-check-label fw-semibold" for="obligatoria-{{ $pregunta->id_pregunta }}">Pregunta obligatoria</label>
                                </div>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <button class="btn btn-sm btn-primary rounded-pill px-3" type="submit"><i class="fas fa-save me-1"></i>Guardar pregunta</button>
                            </div>
                        </form>
                    </details>
                @endunless
            </article>
        @empty
            <div class="text-center text-muted py-5">
                <i class="fas fa-circle-question fa-2x mb-3 d-block opacity-50"></i>
                <p class="mb-0">Todavía no hay preguntas. Agrega la primera para configurar la encuesta.</p>
            </div>
        @endforelse
    </section>
</div>
@endsection
