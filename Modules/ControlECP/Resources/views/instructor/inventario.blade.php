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
</style>
@endpush

@section('dashboard-content')
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

    <div class="d-flex flex-wrap gap-2 mb-3">
        <span class="badge rounded-pill px-3 py-2 text-white" style="background:#39A900">
            <i class="fas fa-boxes-stacked me-1"></i>Recursos activos
        </span>
    </div>

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
                                <a class="btn btn-sm btn-outline-primary rounded-pill" href="{{ route('controlecp.instructor.inventario.show', $recurso->id_inventario) }}">
                                    <i class="fas fa-eye me-1"></i>Ver / usar
                                </a>
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
@endsection
