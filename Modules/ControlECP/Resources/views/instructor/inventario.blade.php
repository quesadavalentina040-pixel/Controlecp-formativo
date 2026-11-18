@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .instructor-inventory-header,
    .instructor-inventory-filters {
        background: #001A29;
        border-radius: 20px;
    }
    .instructor-inventory-stat {
        background: #fff;
        border: 2px solid transparent;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0, 26, 41, .05);
        padding: 1rem 1.25rem;
        transition: transform .2s, box-shadow .2s;
    }
    .instructor-inventory-stat:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(0, 26, 41, .1);
    }
    .instructor-inventory-stat-label,
    .instructor-inventory-stat-subtitle {
        color: #64748b;
        font-size: .9rem;
    }
    .instructor-inventory-stat-value {
        font-size: 1.7rem;
        font-weight: 800;
        line-height: 1.2;
    }
    .instructor-inventory-stat-icon {
        align-items: center;
        border-radius: 12px;
        display: flex;
        height: 38px;
        justify-content: center;
        width: 38px;
    }
    .instructor-inventory-filters label {
        color: #fff;
        font-size: .8rem;
        font-weight: 700;
    }
    .instructor-inventory-search {
        background-color: #39A900;
        border-color: #39A900;
        color: #fff;
    }
    .instructor-inventory-search:hover,
    .instructor-inventory-search:focus-visible {
        background-color: #2f9000;
        border-color: #2f9000;
        color: #fff;
    }
    .instructor-inventory-actions {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: .5rem;
        flex-wrap: wrap;
        white-space: nowrap;
    }
    .instructor-inventory-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .4rem;
        min-height: 38px;
        padding: .45rem .85rem;
        border: 1px solid transparent;
        border-radius: 999px;
        font-size: .82rem;
        font-weight: 700;
        line-height: 1;
        text-decoration: none;
        transition: background-color .18s ease, border-color .18s ease, color .18s ease, box-shadow .18s ease, transform .18s ease;
    }
    .instructor-inventory-action:hover:not(:disabled),
    .instructor-inventory-action:focus-visible:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0, 26, 41, .14);
    }
    .instructor-inventory-action-view {
        color: #0876bd;
        background: #eaf5ff;
        border-color: #c9e6fb;
    }
    .instructor-inventory-action-view:hover,
    .instructor-inventory-action-view:focus-visible {
        color: #fff;
        background: #0876bd;
        border-color: #0876bd;
    }
    .instructor-inventory-action-use {
        color: #187b36;
        background: #eaf8ed;
        border-color: #c5e9cd;
        box-shadow: 0 2px 6px rgba(24, 123, 54, .1);
    }
    .instructor-inventory-action-use:hover,
    .instructor-inventory-action-use:focus-visible {
        color: #fff;
        background: #238b45;
        border-color: #238b45;
    }
    .instructor-inventory-action-return {
        color: #087c91;
        background: #e8f8fb;
        border-color: #b9e6ef;
    }
    .instructor-inventory-action-return:hover,
    .instructor-inventory-action-return:focus-visible {
        color: #fff;
        background: #087c91;
        border-color: #087c91;
    }
    .instructor-inventory-action:disabled {
        cursor: not-allowed;
        opacity: .5;
        box-shadow: none;
    }
</style>
@endpush

@section('dashboard-content')
@if(request()->boolean('modal'))
    @if(request()->input('accion') === 'entrega')
        <form data-inventario-entrega-form method="POST"
              action="{{ route('controlecp.instructor.inventario.entregar', $recurso->id_inventario) }}"
              enctype="multipart/form-data">
            @csrf
            <p class="small text-muted">Recurso: <strong>{{ $recurso->nombre }}</strong></p>
            <div data-inventario-entrega-paso="datos">
                <div class="small fw-semibold text-primary mb-3">Paso 1 de 2 · Selecciona el destino y la cantidad</div>
                <div class="mb-3">
                    <label for="inventarioDestinoEntrega" class="form-label small fw-semibold">Actividad o asesoría <span class="text-danger">*</span></label>
                    <select id="inventarioDestinoEntrega" name="destino" class="form-select rounded-3" required>
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
                        <div class="form-text text-danger">No tienes actividades o asesorías disponibles para asociar el uso.</div>
                    @endif
                </div>
                <div class="mb-3">
                    <label for="inventarioCantidadEntrega" class="form-label small fw-semibold">Cantidad que vas a usar <span class="text-danger">*</span></label>
                    <input id="inventarioCantidadEntrega" type="number" name="cantidad" class="form-control rounded-3"
                           min="1" max="{{ $recurso->cantidad_disponible }}" value="1" required>
                    <div class="form-text">Disponibles ahora: {{ $recurso->cantidad_disponible }} unidades.</div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn text-white rounded-pill px-4" style="background:#39A900;"
                            data-inventario-entrega-continuar
                            @disabled($recurso->cantidad_disponible < 1 || ($actividades->isEmpty() && $asesorias->isEmpty()))>
                        Continuar <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>
            <div class="d-none" data-inventario-entrega-paso="evidencia">
                <div class="small fw-semibold text-primary mb-3">Paso 2 de 2 · Adjunta la evidencia para entregar</div>
                <div class="bg-light rounded-3 p-3 mb-3">
                    <div class="small text-muted">Vas a entregar</div>
                    <strong data-inventario-entrega-resumen></strong>
                </div>
                <div class="mb-3">
                    <label for="inventarioFotoEntrega" class="form-label small fw-semibold">Foto de evidencia <span class="text-danger">*</span></label>
                    <input id="inventarioFotoEntrega" type="file" name="foto" class="form-control rounded-3"
                           accept="image/jpeg,image/png">
                    <div class="form-text">JPG o PNG, máximo 4 MB.</div>
                </div>
                <div class="mb-3">
                    <label for="inventarioObservacionEntrega" class="form-label small fw-semibold">Observación</label>
                    <textarea id="inventarioObservacionEntrega" name="observacion" class="form-control rounded-3" rows="2" maxlength="1000"></textarea>
                </div>
                <div class="d-flex justify-content-between gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-inventario-entrega-volver>
                        <i class="fas fa-arrow-left me-1"></i>Volver
                    </button>
                    <button type="submit" class="btn text-white rounded-pill px-4" style="background:#39A900;">
                        <i class="fas fa-arrow-up me-1"></i>Entregar
                    </button>
                </div>
            </div>
        </form>
    @elseif(request()->input('accion') === 'devolucion')
        <form method="POST" action="{{ route('controlecp.instructor.inventario.devolver', $recurso->id_inventario) }}"
              enctype="multipart/form-data">
            @csrf
            <p class="small text-muted">Recurso: <strong>{{ $recurso->nombre }}</strong></p>
            <div class="mb-3">
                <label for="inventarioEntregaDevolucion" class="form-label small fw-semibold">Entrega pendiente <span class="text-danger">*</span></label>
                <select id="inventarioEntregaDevolucion" name="id_movimiento_entrega" class="form-select rounded-3" required>
                    <option value="">Selecciona la entrega</option>
                    @foreach($entregasPendientes as $entrega)
                        @php
                            $devuelto = $entrega->movimientosHijos->where('tipo', 'Devolución')->sum('cantidad');
                            $saldo = (int) $entrega->cantidad - (int) $devuelto;
                        @endphp
                        <option value="{{ $entrega->id_movimiento_inventario }}" data-pendiente="{{ $saldo }}">
                            {{ $entrega->fecha?->copy()->timezone('America/Bogota')->format('d/m/Y g:i A') ?? 'Sin fecha' }}
                            · {{ $saldo }} un. pendientes · {{ $entrega->observacion ?: 'Entrega' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label for="inventarioCantidadDevolucion" class="form-label small fw-semibold">Cantidad <span class="text-danger">*</span></label>
                    <input id="inventarioCantidadDevolucion" type="number" name="cantidad" class="form-control rounded-3"
                           min="1" value="1" required>
                </div>
                <div class="col-6">
                    <label for="inventarioFotoDevolucion" class="form-label small fw-semibold">Foto de evidencia <span class="text-danger">*</span></label>
                    <input id="inventarioFotoDevolucion" type="file" name="foto" class="form-control rounded-3"
                           accept="image/jpeg,image/png" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="inventarioObservacionDevolucion" class="form-label small fw-semibold">Observación</label>
                <textarea id="inventarioObservacionDevolucion" name="observacion" class="form-control rounded-3" rows="2" maxlength="1000"></textarea>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn text-white rounded-pill px-4" style="background:#39A900;"
                        @disabled($entregasPendientes->isEmpty())>
                    Registrar devolución
                </button>
            </div>
        </form>
    @else
        <div data-inventory-modal-title="{{ $recurso->nombre }}">
            @php
                $semaforo = $recurso->semaforo_badge;
                $estadoFisico = $recurso->estado_fisico_badge;
                $fotoPrincipal = $recurso->fotos->first();
            @endphp
            <div class="row g-4">
                <div class="col-12 col-lg-5">
                    <div class="card border-0 shadow-sm p-3 rounded-4 bg-light h-100">
                        @if($fotoPrincipal)
                            <img src="{{ asset($fotoPrincipal->ruta) }}" alt="Foto de {{ $recurso->nombre }}"
                                 id="inventarioFotoPrincipal" class="rounded-3 border shadow-sm mb-3"
                                 style="width:100%;height:220px;object-fit:contain;background:#fff;">
                        @else
                            <div class="rounded-3 border bg-white d-flex align-items-center justify-content-center text-muted mb-3"
                                 style="width:100%;height:220px;">
                                <i class="fas fa-box-open fa-2x me-2"></i>Sin fotografía
                            </div>
                        @endif
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="h5 fw-bold mb-0 text-dark">{{ $recurso->nombre }}</h3>
                            <span class="badge rounded-pill px-3 py-2"
                                  style="background:{{ $semaforo['bg'] }};color:{{ $semaforo['text'] }};">
                                {{ $semaforo['label'] }}
                            </span>
                        </div>
                        <div class="small border-top border-bottom py-2 mb-3">
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted">Categoría:</span>
                                <strong class="text-dark">{{ $recurso->categoria?->nombre ?? 'Sin categoría' }}</strong>
                            </div>
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted">Cantidad:</span>
                                <strong class="text-dark">{{ $recurso->cantidad_disponible }} de {{ $recurso->cantidad_total }} unidades</strong>
                            </div>
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted">Estado físico:</span>
                                <span class="badge rounded-pill px-2 py-1"
                                      style="background:{{ $estadoFisico['bg'] }};color:{{ $estadoFisico['text'] }};">
                                    {{ $estadoFisico['label'] }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted">Semáforo:</span>
                                <span class="fw-semibold">{{ $semaforo['label'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted">Estado:</span>
                                <strong class="text-dark">{{ $recurso->estado }}</strong>
                            </div>
                        </div>
                        <div class="mb-3">
                            <span class="text-muted small d-block mb-1">Observación:</span>
                            <div class="p-2 rounded bg-white border small text-muted">
                                {{ $recurso->observaciones ?: 'Sin observaciones adicionales.' }}
                            </div>
                        </div>
                        <div class="mb-3">
                            <span class="text-muted small d-block mb-2">Fotos:</span>
                            <div class="d-flex flex-wrap gap-2">
                                @forelse($recurso->fotos as $foto)
                                    <button type="button" class="p-1 bg-white rounded-3 border {{ $fotoPrincipal?->id_recurso_foto === $foto->id_recurso_foto ? 'border-success' : '' }}"
                                            data-inventory-photo="{{ asset($foto->ruta) }}" aria-label="Ver fotografía de {{ $recurso->nombre }}">
                                        <img src="{{ asset($foto->ruta) }}" alt="" style="width:44px;height:44px;object-fit:cover;border-radius:6px;">
                                    </button>
                                @empty
                                    <span class="small text-muted">No hay fotografías registradas.</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-0 pt-3 px-4 pb-0">
                            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active fw-semibold" id="tabInstructorMovimientos"
                                            data-bs-toggle="tab" data-bs-target="#paneInstructorMovimientos"
                                            type="button" role="tab" aria-controls="paneInstructorMovimientos" aria-selected="true">
                                        <i class="fas fa-clock-rotate-left me-1 text-primary"></i>Historial de movimientos
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link fw-semibold" id="tabInstructorNovedades"
                                            data-bs-toggle="tab" data-bs-target="#paneInstructorNovedades"
                                            type="button" role="tab" aria-controls="paneInstructorNovedades" aria-selected="false">
                                        <i class="fas fa-flag me-1 text-warning"></i>Novedades
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body p-4 tab-content">
                            <div class="tab-pane fade show active" id="paneInstructorMovimientos" role="tabpanel"
                                 aria-labelledby="tabInstructorMovimientos">
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h4 class="h6 fw-bold text-danger mb-0 small text-uppercase">
                                            <i class="fas fa-hand-holding-box me-1"></i>Mis préstamos pendientes
                                        </h4>
                                        <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1 small">Pendientes</span>
                                    </div>
                                    <div class="table-responsive border rounded-3 bg-white">
                                        <table class="table table-hover align-middle mb-0 small">
                                            <thead class="small fw-bold" style="background:#FFF5F5;">
                                                <tr>
                                                    <th>Fecha</th><th>Destino</th>
                                                    <th class="text-center">Prestado</th>
                                                    <th class="text-center">Devuelto</th>
                                                    <th class="text-center">Saldo</th>
                                                    <th>Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($entregasPendientes as $entrega)
                                                    @php
                                                        $devuelto = $entrega->movimientosHijos->where('tipo', 'Devolución')->sum('cantidad');
                                                        $saldo = (int) $entrega->cantidad - (int) $devuelto;
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $entrega->fecha?->copy()->timezone('America/Bogota')->format('d/m/Y H:i') ?? '—' }}</td>
                                                        <td>{{ $entrega->observacion ?: 'Entrega de recurso' }}</td>
                                                        <td class="text-center">{{ $entrega->cantidad }}</td>
                                                        <td class="text-center">{{ $devuelto }}</td>
                                                        <td class="text-center fw-bold text-warning">{{ $saldo }}</td>
                                                        <td>{{ $devuelto > 0 ? 'Parcial' : 'Pendiente' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="6" class="text-center py-3 text-success">No hay préstamos pendientes.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h4 class="h6 fw-bold text-success mb-0 small text-uppercase">
                                            <i class="fas fa-circle-check me-1"></i>Mis entradas y devoluciones
                                        </h4>
                                        <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 small">En inventario</span>
                                    </div>
                                    <div class="table-responsive border rounded-3 bg-white">
                                        <table class="table table-hover align-middle mb-0 small">
                                            <thead class="small fw-bold" style="background:#F0FDF4;">
                                                <tr>
                                                    <th>Fecha</th><th>Tipo</th>
                                                    <th class="text-center">Cantidad</th>
                                                    <th>Observación</th><th class="text-center">Evidencia</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($recurso->movimientos->where('id_usuario', auth()->id())->take(20) as $movimiento)
                                                    <tr>
                                                        <td>{{ $movimiento->fecha?->copy()->timezone('America/Bogota')->format('d/m/Y H:i') ?? '—' }}</td>
                                                        <td>
                                                            <span class="badge {{ $movimiento->tipo === 'Devolución' ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' }} rounded-pill">
                                                                {{ $movimiento->tipo }}
                                                            </span>
                                                        </td>
                                                        <td class="text-center fw-bold">{{ $movimiento->cantidad }} un.</td>
                                                        <td>{{ $movimiento->observacion ?: '—' }}</td>
                                                        <td class="text-center">
                                                            @if($movimiento->foto)
                                                                <a href="{{ route('controlecp.instructor.repositorio.evidencias', [
                                                                    'categoria' => 'Inventario',
                                                                    'producto_id' => $movimiento->id_inventario,
                                                                    'movimiento_id' => $movimiento->id_movimiento_inventario,
                                                                ]) }}"
                                                                   class="badge bg-light text-primary border text-decoration-none">Ver</a>
                                                            @else
                                                                <span class="text-muted">—</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="5" class="text-center py-3 text-muted">Aún no registras movimientos para este recurso.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="paneInstructorNovedades" role="tabpanel"
                                 aria-labelledby="tabInstructorNovedades">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4 class="h6 fw-bold mb-0 text-dark">Novedades registradas</h4>
                                    <button type="button" class="btn btn-sm text-white rounded-pill px-3"
                                            style="background-color:#39A900;" data-inventory-action="novedad">
                                        <i class="fas fa-plus me-1"></i>Agregar
                                    </button>
                                </div>
                                @forelse($recurso->novedades as $novedad)
                                    <article class="p-3 mb-2 rounded-3 border-start border-4 border-warning bg-light">
                                        <div class="d-flex flex-wrap justify-content-between gap-2">
                                            <strong>{{ $novedad->usuario?->nickname ?? 'Usuario' }}</strong>
                                            <span class="small text-muted">{{ $novedad->fecha?->copy()->timezone('America/Bogota')->format('d/m/Y g:i A') ?? '—' }}</span>
                                        </div>
                                        <div class="small text-dark mt-1">{{ $novedad->descripcion }}</div>
                                    </article>
                                @empty
                                    <div class="text-center py-4 text-muted small">No hay novedades registradas.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <template id="inventarioFormularioNovedad">
            <form method="POST" action="{{ route('controlecp.instructor.inventario.novedad', $recurso->id_inventario) }}">
                @csrf
                <p class="small text-muted">Recurso: <strong>{{ $recurso->nombre }}</strong></p>
                <div class="mb-3">
                    <label for="inventarioDescripcionNovedad" class="form-label small fw-semibold">Descripción <span class="text-danger">*</span></label>
                    <textarea id="inventarioDescripcionNovedad" name="descripcion" class="form-control rounded-3" rows="4"
                              minlength="10" maxlength="2000" required></textarea>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white rounded-pill px-4" style="background:#39A900;">Guardar novedad</button>
                </div>
            </form>
        </template>
    @endif
@else
<div class="container-fluid px-3 py-3">
    <div class="instructor-inventory-header d-flex flex-wrap justify-content-between align-items-center gap-3 p-4 mb-4 text-white">
        <div>
            <div class="small fw-bold mb-2" style="color:#62E31D">
                <i class="fas fa-star me-1"></i>Control ECP · Gestión de Inventario
            </div>
            <h1 class="h3 fw-bold mb-1">
                <i class="fas fa-boxes-stacked me-2" style="color:#62E31D"></i>Gestión de Inventario
            </h1>
            <p class="mb-0 text-white-50">Consulta disponibilidad y usa recursos en tus actividades o asesorías.</p>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger">
            <div class="fw-semibold mb-1">No se pudo completar la solicitud:</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="instructor-inventory-stat h-100" style="border-color:rgba(2,136,209,.35)">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="instructor-inventory-stat-label">Recursos totales</span>
                    <span class="instructor-inventory-stat-icon" style="background:#E0F2FE;color:#0288D1">
                        <i class="fas fa-cubes"></i>
                    </span>
                </div>
                <div class="instructor-inventory-stat-value mt-1" style="color:#0288D1">{{ $totalRecursos }}</div>
                <div class="instructor-inventory-stat-subtitle">{{ $totalUnidades }} unidades en catálogo</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="instructor-inventory-stat h-100" style="border-color:rgba(22,163,74,.35)">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="instructor-inventory-stat-label">Recursos disponibles</span>
                    <span class="instructor-inventory-stat-icon" style="background:#DCFCE7;color:#16A34A">
                        <i class="fas fa-circle-check"></i>
                    </span>
                </div>
                <div class="instructor-inventory-stat-value mt-1" style="color:#16A34A">{{ $totalDisponibles }}</div>
                <div class="instructor-inventory-stat-subtitle">Libres para actividades / asesorías</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="instructor-inventory-stat h-100" style="border-color:rgba(2,136,209,.35)">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="instructor-inventory-stat-label">Recursos prestados</span>
                    <span class="instructor-inventory-stat-icon" style="background:#E0F2FE;color:#0288D1">
                        <i class="fas fa-hand-holding"></i>
                    </span>
                </div>
                <div class="instructor-inventory-stat-value mt-1" style="color:#0288D1">{{ $totalPrestados }}</div>
                <div class="instructor-inventory-stat-subtitle">En uso / pendientes de devolución</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="instructor-inventory-stat h-100" style="border-color:rgba(234,88,12,.35)">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="instructor-inventory-stat-label">Stock bajo / crítico</span>
                    <span class="instructor-inventory-stat-icon" style="background:#FFEDD5;color:#EA580C">
                        <i class="fas fa-triangle-exclamation"></i>
                    </span>
                </div>
                <div class="instructor-inventory-stat-value mt-1" style="color:#EA580C">{{ $stockBajoCount }}</div>
                <div class="instructor-inventory-stat-subtitle">{{ $bajasCount }} dado(s) de baja</div>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('controlecp.instructor.inventario') }}" class="instructor-inventory-filters shadow-sm mb-4 p-3">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label for="buscarRecurso" class="form-label">Buscar</label>
                <input id="buscarRecurso" name="buscar" class="form-control" value="{{ request('buscar') }}" placeholder="Buscar recurso por nombre...">
            </div>
            <div class="col-12 col-md-2">
                <label for="categoriaRecurso" class="form-label">Categoría</label>
                <select id="categoriaRecurso" name="categoria_id" class="form-select">
                    <option value="">Todas las categorías</option>
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria->id_categoria_inventario }}" @selected((string) request('categoria_id') === (string) $categoria->id_categoria_inventario)>{{ $categoria->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label for="estadoFisico" class="form-label">Estado físico</label>
                <select id="estadoFisico" name="estado_fisico" class="form-select">
                    <option value="">Todos los estados físicos</option>
                    @foreach(\Modules\ControlECP\Entities\Inventario::ESTADOS_FISICOS as $estado)
                        <option value="{{ $estado }}" @selected(request('estado_fisico') === $estado)>{{ $estado }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2">
                <label for="semaforoRecurso" class="form-label">Semáforo</label>
                <select id="semaforoRecurso" name="semaforo" class="form-select">
                    <option value="">Todos los semáforos</option>
                    <option value="verde" @selected(request('semaforo') === 'verde')>Verde</option>
                    <option value="amarillo" @selected(request('semaforo') === 'amarillo')>Amarillo</option>
                    <option value="rojo" @selected(request('semaforo') === 'rojo')>Rojo</option>
                </select>
            </div>
            <div class="col-8 col-md-1 d-grid">
                <button class="btn instructor-inventory-search text-nowrap" aria-label="Buscar recursos"><i class="fas fa-search me-1"></i>Buscar</button>
            </div>
            <div class="col-4 col-md-1 d-grid">
                <a class="btn btn-light" href="{{ route('controlecp.instructor.inventario') }}" aria-label="Limpiar filtros" title="Limpiar filtros">
                    <i class="fas fa-rotate-left"></i>
                </a>
            </div>
        </div>
    </form>

    <section class="card border-0 shadow-sm mb-4" style="border-radius:16px">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="small fw-bold border-bottom" style="background:#F8FAFC;color:#001A29">
                    <tr>
                        <th class="ps-4 py-3">Foto</th>
                        <th class="py-3">Nombre</th>
                        <th class="py-3">Categoría</th>
                        <th class="py-3">Tipo</th>
                        <th class="py-3 text-center">Stock / Cantidad</th>
                        <th class="py-3 text-center">Estado físico</th>
                        <th class="py-3 text-center">Semáforo</th>
                        <th class="text-end pe-4 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recursos as $recurso)
                        @php
                            $semaforo = $recurso->semaforo_badge;
                            $estadoFisico = $recurso->estado_fisico_badge;
                            $tipo = $recurso->tipo_recurso_badge;
                            $puedeDevolver = $entregasPendientes->has($recurso->id_inventario);
                        @endphp
                        <tr>
                            <td class="ps-4">
                                @if($recurso->fotos->isNotEmpty())
                                    <img src="{{ asset($recurso->fotos->first()->ruta) }}" alt="Foto de {{ $recurso->nombre }}" class="rounded-3 border" style="width:48px;height:48px;object-fit:cover">
                                @else
                                    <span class="rounded-3 bg-light text-muted d-inline-flex justify-content-center align-items-center border" style="width:48px;height:48px"><i class="fas fa-box"></i></span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold">{{ $recurso->nombre }}</div>
                                <div class="small text-muted">{{ $recurso->observaciones ?: 'Sin observaciones adicionales' }}</div>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $recurso->categoria?->nombre ?? 'Sin categoría' }}</span></td>
                            <td>
                                <span class="badge rounded-pill border" style="background:{{ $tipo['bg'] }};color:{{ $tipo['text'] }};border-color:{{ $tipo['border'] }}!important">
                                    <i class="{{ $tipo['icon'] }} me-1"></i>{{ $recurso->tipo_recurso }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold">{{ $recurso->cantidad_disponible }}</span>
                                <span class="small text-muted">de {{ $recurso->cantidad_total }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill" style="background:{{ $estadoFisico['bg'] }};color:{{ $estadoFisico['text'] }}">{{ $estadoFisico['label'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill" style="background:{{ $semaforo['bg'] }};color:{{ $semaforo['text'] }}">
                                    <i class="{{ $semaforo['icon'] }} me-1"></i>{{ $semaforo['label'] }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="instructor-inventory-actions">
                                    <button type="button" class="instructor-inventory-action instructor-inventory-action-view"
                                            data-bs-toggle="modal" data-bs-target="#modalDetalleInventario"
                                            data-url="{{ route('controlecp.instructor.inventario.show', $recurso->id_inventario) }}?modal=1"
                                            title="Ver detalles" aria-label="Ver detalles de {{ $recurso->nombre }}">
                                        <i class="fas fa-eye" aria-hidden="true"></i>
                                    </button>
                                    <button type="button" class="instructor-inventory-action instructor-inventory-action-use"
                                            data-bs-toggle="modal" data-bs-target="#modalAccionInventario"
                                            data-action-url="{{ route('controlecp.instructor.inventario.show', $recurso->id_inventario) }}?modal=1&amp;accion=entrega"
                                            data-action-title="Usar recurso"
                                            @disabled($recurso->cantidad_disponible < 1)
                                            title="Usar recurso: registrar entrega" aria-label="Usar {{ $recurso->nombre }}">
                                        <i class="fas fa-arrow-up" aria-hidden="true"></i>
                                    </button>
                                    <button type="button" class="instructor-inventory-action instructor-inventory-action-return"
                                            data-bs-toggle="modal" data-bs-target="#modalAccionInventario"
                                            data-action-url="{{ route('controlecp.instructor.inventario.show', $recurso->id_inventario) }}?modal=1&amp;accion=devolucion"
                                            data-action-title="Registrar devolución"
                                            @disabled(!$puedeDevolver)
                                            title="Registrar devolución" aria-label="Devolver {{ $recurso->nombre }}">
                                        <i class="fas fa-arrow-rotate-left" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-boxes-stacked fs-1 text-secondary opacity-50"></i>
                                <p class="fw-bold mt-3 mb-1">No se encontraron recursos en el inventario</p>
                                <p class="small text-muted mb-0">Prueba a cambiar los filtros para consultar otros recursos.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($recursos->hasPages())
            <div class="card-footer bg-white border-0 px-4">
                @if ($recursos->hasPages())
                    <nav aria-label="Paginación de inventario">
                        <ul class="pagination justify-content-center">
                            @if ($recursos->onFirstPage())
                                <li class="page-item disabled"><span class="page-link" aria-hidden="true">&lsaquo;</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $recursos->previousPageUrl() }}" aria-label="Página anterior">&lsaquo;</a></li>
                            @endif
                            @php
                                $ultimaPaginaMostrada = 0;
                            @endphp
                            @for ($pagina = 1; $pagina <= $recursos->lastPage(); $pagina++)
                                @if ($pagina === 1 || $pagina === $recursos->lastPage() || abs($pagina - $recursos->currentPage()) <= 1)
                                    @if ($ultimaPaginaMostrada > 0 && $pagina > $ultimaPaginaMostrada + 1)
                                        <li class="page-item disabled"><span class="page-link">…</span></li>
                                    @endif
                                    <li class="page-item {{ $pagina === $recursos->currentPage() ? 'active' : '' }}">
                                        <a class="page-link" href="{{ $recursos->url($pagina) }}" @if($pagina === $recursos->currentPage()) aria-current="page" @endif>{{ $pagina }}</a>
                                    </li>
                                    @php
                                    $ultimaPaginaMostrada = $pagina;
                                @endphp
                                @endif
                            @endfor
                            @if ($recursos->hasMorePages())
                                <li class="page-item"><a class="page-link" href="{{ $recursos->nextPageUrl() }}" aria-label="Página siguiente">&rsaquo;</a></li>
                            @else
                                <li class="page-item disabled"><span class="page-link" aria-hidden="true">&rsaquo;</span></li>
                            @endif
                        </ul>
                    </nav>
                @endif
            </div>
        @endif
    </section>
</div>

<div class="modal fade" id="modalDetalleInventario" tabindex="-1" aria-labelledby="modalDetalleInventarioTitulo" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header text-white py-3 px-4" style="background:#001A29;border-bottom:2px solid #39A900;">
                <h2 class="modal-title h5 fw-bold" id="modalDetalleInventarioTitulo">Detalle del recurso</h2>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body pt-0" id="modalDetalleInventarioContenido">
                <div class="text-center text-muted py-5">
                    <span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
                    Cargando información del recurso...
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAccionInventario" tabindex="-1" aria-labelledby="modalAccionInventarioTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header text-white py-3 px-4" style="background:#001A29;border-bottom:2px solid #39A900;">
                <h2 class="modal-title h5 fw-bold" id="modalAccionInventarioTitulo"></h2>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4" id="modalAccionInventarioContenido"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(() => {
    const modal = document.getElementById('modalDetalleInventario');
    const contenido = document.getElementById('modalDetalleInventarioContenido');
    const titulo = document.getElementById('modalDetalleInventarioTitulo');
    const modalAccion = document.getElementById('modalAccionInventario');
    const tituloAccion = document.getElementById('modalAccionInventarioTitulo');
    const contenidoAccion = document.getElementById('modalAccionInventarioContenido');

    const abrirFormularioAccion = action => {
        const templates = {
            novedad: ['inventarioFormularioNovedad', 'Registrar novedad']
        };
        const [templateId, actionTitle] = templates[action] || [];
        const template = templateId ? contenido.querySelector(`#${templateId}`) : null;
        if (!template) {
            contenidoAccion.textContent = 'No fue posible cargar el formulario seleccionado.';
            return;
        }

        modal.addEventListener('hidden.bs.modal', () => {
            tituloAccion.textContent = actionTitle;
            contenidoAccion.replaceChildren(template.content.cloneNode(true));
            bootstrap.Modal.getOrCreateInstance(modalAccion).show();
        }, {once: true});
        bootstrap.Modal.getOrCreateInstance(modal).hide();
    };

    const avanzarEntrega = form => {
        if (!form.reportValidity()) return;

        const destino = form.querySelector('[name="destino"]');
        const cantidad = form.querySelector('[name="cantidad"]');
        const resumen = form.querySelector('[data-inventario-entrega-resumen]');
        const foto = form.querySelector('#inventarioFotoEntrega');
        resumen.textContent = `${cantidad.value} unidad(es) para ${destino.options[destino.selectedIndex].text.trim()}`;
        foto.required = true;
        form.querySelector('[data-inventario-entrega-paso="datos"]').classList.add('d-none');
        form.querySelector('[data-inventario-entrega-paso="evidencia"]').classList.remove('d-none');
    };

    modalAccion.addEventListener('show.bs.modal', event => {
        const boton = event.relatedTarget;
        const url = boton?.getAttribute('data-action-url');
        if (!url) return;

        tituloAccion.textContent = boton.getAttribute('data-action-title') || 'Registrar acción';
        contenidoAccion.innerHTML = `
            <div class="text-center text-muted py-4">
                <span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
                Cargando formulario...
            </div>
        `;

        fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html'}})
            .then(response => {
                if (!response.ok) {
                    throw new Error(`No se pudo cargar el formulario (HTTP ${response.status}).`);
                }
                return response.text();
            })
            .then(html => {
                contenidoAccion.innerHTML = html;
            })
            .catch(error => {
                contenidoAccion.textContent = error.message || 'Ocurrió un error al cargar el formulario.';
            });
    });

    modal.addEventListener('show.bs.modal', event => {
        const boton = event.relatedTarget;
        const url = boton?.getAttribute('data-url');

        titulo.textContent = 'Detalle del recurso';
        contenido.innerHTML = `
            <div class="text-center text-muted py-5">
                <span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
                Cargando información del recurso...
            </div>
        `;

        if (!url) {
            contenido.textContent = 'No fue posible identificar el recurso seleccionado.';
            return;
        }

        fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html'}})
            .then(response => {
                if (!response.ok) {
                    throw new Error(`No se pudo cargar el recurso (HTTP ${response.status}).`);
                }
                return response.text();
            })
            .then(html => {
                contenido.innerHTML = html;
                const detalle = contenido.querySelector('[data-inventory-modal-title]');
                if (detalle) {
                    titulo.textContent = detalle.dataset.inventoryModalTitle;
                }
            })
            .catch(error => {
                contenido.textContent = error.message || 'Ocurrió un error al cargar el recurso.';
            });
    });

    modal.addEventListener('click', event => {
        const botonAccion = event.target.closest('[data-inventory-action]');
        if (botonAccion) {
            abrirFormularioAccion(botonAccion.dataset.inventoryAction);
            return;
        }

        const miniatura = event.target.closest('[data-inventory-photo]');
        if (!miniatura) return;

        const fotoPrincipal = contenido.querySelector('#inventarioFotoPrincipal');
        if (fotoPrincipal) {
            fotoPrincipal.src = miniatura.dataset.inventoryPhoto;
            contenido.querySelectorAll('[data-inventory-photo]').forEach(foto => {
                foto.classList.toggle('border-success', foto === miniatura);
            });
        }
    });

    contenidoAccion.addEventListener('click', event => {
        const continuar = event.target.closest('[data-inventario-entrega-continuar]');
        if (continuar) {
            const form = continuar.closest('form[data-inventario-entrega-form]');
            if (form) avanzarEntrega(form);
            return;
        }

        const volver = event.target.closest('[data-inventario-entrega-volver]');
        if (!volver) return;

        const form = volver.closest('form[data-inventario-entrega-form]');
        if (!form) return;

        form.querySelector('#inventarioFotoEntrega').required = false;
        form.querySelector('[data-inventario-entrega-paso="evidencia"]').classList.add('d-none');
        form.querySelector('[data-inventario-entrega-paso="datos"]').classList.remove('d-none');
    });

    contenidoAccion.addEventListener('submit', event => {
        const form = event.target.closest('form[data-inventario-entrega-form]');
        if (!form || !form.querySelector('[data-inventario-entrega-paso="evidencia"]').classList.contains('d-none')) return;

        event.preventDefault();
        avanzarEntrega(form);
    });

    document.addEventListener('change', event => {
        if (event.target.id !== 'inventarioEntregaDevolucion') return;

        const cantidad = contenidoAccion.querySelector('#inventarioCantidadDevolucion');
        const pendiente = event.target.options[event.target.selectedIndex]?.dataset.pendiente;
        if (!cantidad) return;

        if (pendiente) {
            cantidad.max = pendiente;
            cantidad.value = Math.min(parseInt(cantidad.value || '1', 10), parseInt(pendiente, 10));
        } else {
            cantidad.removeAttribute('max');
        }
    });
})();
</script>
@endpush
@endif
@endsection
