@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
<div class="container-fluid px-3 py-3">
    @php
        $semaforo = $recurso->semaforo_badge;
        $estadoFisico = $recurso->estado_fisico_badge;
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 p-4 mb-4 text-white"
         style="background:#001A29;border-radius:20px;">
        <div>
            <a class="small text-white-50 text-decoration-none" href="{{ route('controlecp.instructor.inventario') }}">
                <i class="fas fa-arrow-left me-1"></i>Volver al inventario
            </a>
            <div class="small fw-semibold mt-2 mb-1" style="color:#39A900;">Control ECP · Inventario compartido</div>
            <h1 class="h3 fw-bold mb-1">{{ $recurso->nombre }}</h1>
            <p class="mb-0 text-white-50">{{ $recurso->categoria?->nombre ?? 'Sin categoría' }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <span class="badge rounded-pill px-3 py-2"
                  style="background:{{ $semaforo['bg'] }};color:{{ $semaforo['text'] }};">
                <i class="{{ $semaforo['icon'] }} me-1"></i>{{ $semaforo['label'] }}
            </span>
            <span class="badge rounded-pill px-3 py-2"
                  style="background:{{ $estadoFisico['bg'] }};color:{{ $estadoFisico['text'] }};">
                {{ $estadoFisico['label'] }}
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm" role="alert">
            <i class="fas fa-triangle-exclamation me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger rounded-4 border-0 shadow-sm">
            <div class="fw-semibold mb-1">No se pudo completar el registro:</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <div class="small text-muted">Disponibles</div>
                    <div class="h3 fw-bold mb-0 text-success">{{ $recurso->cantidad_disponible }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <div class="small text-muted">Cantidad total</div>
                    <div class="h3 fw-bold mb-0">{{ $recurso->cantidad_total }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <div class="small text-muted">Estado</div>
                    <div class="h5 fw-bold mb-0">{{ $recurso->estado }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-5">
            <section class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 p-4 pb-0">
                    <h2 class="h5 fw-bold mb-0">Información del recurso</h2>
                </div>
                <div class="card-body p-4">
                    @if($recurso->fotos->isNotEmpty())
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @foreach($recurso->fotos as $foto)
                                <img src="{{ asset($foto->ruta) }}" alt="Foto de {{ $recurso->nombre }}"
                                     class="rounded-3 border" style="width:100px;height:85px;object-fit:cover;">
                            @endforeach
                        </div>
                    @endif
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted">Categoría</dt>
                        <dd class="col-7">{{ $recurso->categoria?->nombre ?? '—' }}</dd>
                        <dt class="col-5 text-muted">Estado físico</dt>
                        <dd class="col-7">{{ $estadoFisico['label'] }}</dd>
                        <dt class="col-5 text-muted">Observaciones</dt>
                        <dd class="col-7">{{ $recurso->observaciones ?: 'Sin observaciones.' }}</dd>
                    </dl>
                    <div class="d-flex flex-wrap gap-2 mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-success rounded-pill px-3"
                                data-bs-toggle="modal" data-bs-target="#modalEntrega">
                            <i class="fas fa-arrow-up me-1"></i>Registrar entrega
                        </button>
                        <button type="button" class="btn btn-outline-primary rounded-pill px-3"
                                data-bs-toggle="modal" data-bs-target="#modalDevolucion"
                                @disabled($entregasPendientes->isEmpty())>
                            <i class="fas fa-arrow-rotate-left me-1"></i>Registrar devolución
                        </button>
                        <button type="button" class="btn btn-outline-warning rounded-pill px-3"
                                data-bs-toggle="modal" data-bs-target="#modalNovedad">
                            <i class="fas fa-flag me-1"></i>Registrar novedad
                        </button>
                    </div>
                </div>
            </section>
        </div>

        <div class="col-lg-7">
            <section class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 p-4">
                    <h2 class="h5 fw-bold mb-0">Mis entregas pendientes</h2>
                    <div class="small text-muted">Solo aparecen los préstamos registrados por tu usuario.</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th class="ps-4">Fecha</th><th>Destino</th><th class="text-center">Pendiente</th></tr>
                        </thead>
                        <tbody>
                            @forelse($entregasPendientes as $entrega)
                                @php
                                    $devuelto = $entrega->movimientosHijos->where('tipo', 'Devolución')->sum('cantidad');
                                    $saldo = (int) $entrega->cantidad - (int) $devuelto;
                                @endphp
                                <tr>
                                    <td class="ps-4">{{ $entrega->fecha?->copy()->timezone('America/Bogota')->format('d/m/Y g:i A') ?? '—' }}</td>
                                    <td>
                                        {{ $entrega->observacion ?: 'Entrega de recurso' }}
                                        <div class="small text-muted">{{ $entrega->tipo }}</div>
                                    </td>
                                    <td class="text-center fw-bold text-warning">{{ $saldo }} un.</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">No tienes entregas pendientes de devolución.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <section class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 p-4">
            <h2 class="h5 fw-bold mb-0">Mis movimientos recientes</h2>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th class="ps-4">Fecha</th><th>Movimiento</th><th class="text-center">Cantidad</th><th>Observación</th></tr>
                </thead>
                <tbody>
                    @forelse($recurso->movimientos->where('id_usuario', auth()->id())->sortByDesc('fecha')->take(20) as $movimiento)
                        <tr>
                            <td class="ps-4">{{ $movimiento->fecha?->copy()->timezone('America/Bogota')->format('d/m/Y g:i A') ?? '—' }}</td>
                            <td>{{ $movimiento->tipo }}</td>
                            <td class="text-center">{{ $movimiento->cantidad }}</td>
                            <td>
                                {{ $movimiento->observacion ?: '—' }}
                                @if($movimiento->foto)
                                    <a class="small d-block" href="{{ asset($movimiento->foto) }}" target="_blank" rel="noopener">Ver evidencia</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Aún no registras movimientos para este recurso.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 p-4">
            <h2 class="h5 fw-bold mb-0">Novedades</h2>
        </div>
        <div class="card-body pt-0">
            @forelse($recurso->novedades as $novedad)
                <article class="border-start border-3 border-warning ps-3 pb-3 mb-3">
                    <div class="d-flex flex-wrap justify-content-between gap-2">
                        <strong>{{ $novedad->usuario?->nickname ?? 'Usuario' }}</strong>
                        <span class="small text-muted">{{ $novedad->fecha?->copy()->timezone('America/Bogota')->format('d/m/Y g:i A') ?? '—' }}</span>
                    </div>
                    <p class="mb-0 mt-1">{{ $novedad->descripcion }}</p>
                </article>
            @empty
                <p class="text-muted mb-0">No hay novedades registradas.</p>
            @endforelse
        </div>
    </section>
</div>

<div class="modal fade" id="modalEntrega" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form method="POST" action="{{ route('controlecp.instructor.inventario.entregar', $recurso->id_inventario) }}"
                  enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-bottom-0 pb-0">
                    <h2 class="modal-title h5 fw-bold">Registrar entrega</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted">Recurso: <strong>{{ $recurso->nombre }}</strong></p>
                    <div class="mb-3">
                        <label for="destinoEntrega" class="form-label fw-semibold">Actividad o asesoría <span class="text-danger">*</span></label>
                        <select id="destinoEntrega" name="destino" class="form-select" required>
                            <option value="">Selecciona el destino</option>
                            <optgroup label="Actividades">
                                @foreach($actividades as $actividad)
                                    <option value="actividad:{{ $actividad->id_cronograma }}">
                                        {{ $actividad->actividad?->nombre ?? 'Actividad' }} ·
                                        {{ $actividad->fecha?->format('d/m/Y') ?? 'Sin fecha' }} ·
                                        {{ substr($actividad->hora_inicio, 0, 5) }}
                                    </option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Asesorías">
                                @foreach($asesorias as $asesoria)
                                    <option value="asesoria:{{ $asesoria->id_asesoria }}">
                                        {{ $asesoria->categoria?->nombre ?? 'Asesoría' }} ·
                                        {{ $asesoria->fecha?->format('d/m/Y') ?? 'Sin fecha' }} ·
                                        {{ substr($asesoria->hora_inicio, 0, 5) }}
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                        @if($actividades->isEmpty() && $asesorias->isEmpty())
                            <div class="form-text text-danger">No tienes actividades o asesorías disponibles para asociar la entrega.</div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="cantidadEntrega" class="form-label fw-semibold">Cantidad <span class="text-danger">*</span></label>
                        <input id="cantidadEntrega" type="number" name="cantidad" class="form-control" min="1"
                               max="{{ $recurso->cantidad_disponible }}" value="1" required>
                        <div class="form-text">Disponibles ahora: {{ $recurso->cantidad_disponible }}.</div>
                    </div>
                    <div class="mb-3">
                        <label for="observacionEntrega" class="form-label fw-semibold">Observación</label>
                        <textarea id="observacionEntrega" name="observacion" class="form-control" rows="2" maxlength="1000"></textarea>
                    </div>
                    <div>
                        <label for="fotoEntrega" class="form-label fw-semibold">Foto de evidencia <span class="text-danger">*</span></label>
                        <input id="fotoEntrega" type="file" name="foto" class="form-control" accept="image/jpeg,image/png" required>
                        <div class="form-text">JPG o PNG, máximo 4 MB.</div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4"
                            @disabled($recurso->cantidad_disponible < 1 || ($actividades->isEmpty() && $asesorias->isEmpty()))>
                        Registrar entrega
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDevolucion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form method="POST" action="{{ route('controlecp.instructor.inventario.devolver', $recurso->id_inventario) }}"
                  enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-bottom-0 pb-0">
                    <h2 class="modal-title h5 fw-bold">Registrar devolución</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="entregaDevolucion" class="form-label fw-semibold">Entrega pendiente <span class="text-danger">*</span></label>
                        <select id="entregaDevolucion" name="id_movimiento_entrega" class="form-select" required>
                            <option value="">Selecciona la entrega</option>
                            @foreach($entregasPendientes as $entrega)
                                @php
                                    $devuelto = $entrega->movimientosHijos->where('tipo', 'Devolución')->sum('cantidad');
                                    $saldo = (int) $entrega->cantidad - (int) $devuelto;
                                @endphp
                                <option value="{{ $entrega->id_movimiento_inventario }}" data-pendiente="{{ $saldo }}">
                                    {{ $entrega->fecha?->copy()->timezone('America/Bogota')->format('d/m/Y g:i A') ?? 'Sin fecha' }} · {{ $saldo }} un. pendientes · {{ $entrega->observacion ?: 'Entrega' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="cantidadDevolucion" class="form-label fw-semibold">Cantidad <span class="text-danger">*</span></label>
                        <input id="cantidadDevolucion" type="number" name="cantidad" class="form-control" min="1"
                               value="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="observacionDevolucion" class="form-label fw-semibold">Observación</label>
                        <textarea id="observacionDevolucion" name="observacion" class="form-control" rows="2" maxlength="1000"></textarea>
                    </div>
                    <div>
                        <label for="fotoDevolucion" class="form-label fw-semibold">Foto de evidencia <span class="text-danger">*</span></label>
                        <input id="fotoDevolucion" type="file" name="foto" class="form-control" accept="image/jpeg,image/png" required>
                        <div class="form-text">JPG o PNG, máximo 4 MB.</div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4" @disabled($entregasPendientes->isEmpty())>
                        Registrar devolución
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNovedad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form method="POST" action="{{ route('controlecp.instructor.inventario.novedad', $recurso->id_inventario) }}">
                @csrf
                <div class="modal-header border-bottom-0 pb-0">
                    <h2 class="modal-title h5 fw-bold">Registrar novedad</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    <label for="descripcionNovedad" class="form-label fw-semibold">Descripción <span class="text-danger">*</span></label>
                    <textarea id="descripcionNovedad" name="descripcion" class="form-control" rows="4"
                              minlength="10" maxlength="2000" required></textarea>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4">Guardar novedad</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('entregaDevolucion')?.addEventListener('change', function () {
    const cantidad = document.getElementById('cantidadDevolucion');
    const pendiente = this.options[this.selectedIndex]?.dataset.pendiente;
    if (pendiente) {
        cantidad.max = pendiente;
        cantidad.value = Math.min(parseInt(cantidad.value || '1', 10), parseInt(pendiente, 10));
    } else {
        cantidad.removeAttribute('max');
    }
});
</script>
@endsection
