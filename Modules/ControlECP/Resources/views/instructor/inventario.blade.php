@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .stat-card-clean{background:#fff;border:1px solid #E2E8F0;border-radius:16px;padding:1.25rem 1.4rem;box-shadow:0 2px 12px rgba(0,26,41,.03);transition:transform .2s,box-shadow .2s}
    .stat-card-clean:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,26,41,.08)}
    .stat-icon-box{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.25rem}
    .btn-action-icon{width:34px;height:34px;padding:0;display:inline-flex;align-items:center;justify-content:center;border-radius:8px;font-size:.85rem;border:1px solid #E2E8F0;background:#fff}
    .inventory-table thead th{background:#F8FAFC;color:#475569;font-size:.76rem;text-transform:uppercase;letter-spacing:.4px;padding:.9rem 1rem}
    .inventory-table tbody td{padding:.9rem 1rem;vertical-align:middle;border-bottom:1px solid #F1F5F9}
    .inventory-photo{width:54px;height:54px;object-fit:cover;border-radius:10px;border:1px solid #E2E8F0}
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge px-2 py-1 small fw-bold" style="background:#E2F5D7;color:#39A900;font-size:.72rem;">ROL INSTRUCTOR</span>
                <span class="text-muted small">&bull; Escuela Cultura de Paz</span>
            </div>
            <h2 class="h3 fw-bold mb-0" style="color:#001A29;font-family:'Outfit',sans-serif;">Gestionar Recursos e Inventario</h2>
            <p class="text-muted small mb-0">Consulta y utiliza los recursos asignados a tus actividades y asesorías</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <span class="btn btn-outline-secondary rounded-pill px-3 btn-sm"><i class="fas fa-eye me-1"></i>Solo catálogo</span>
            <span class="btn btn-outline-success rounded-pill px-3 btn-sm"><i class="fas fa-hand-holding-box me-1"></i>Mis recursos</span>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success rounded-4 border-0 shadow-sm">{{ session('success') }}</div>@endif
    @if(session('warning'))<div class="alert alert-warning rounded-4 border-0 shadow-sm">{{ session('warning') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger rounded-4 border-0 shadow-sm">{{ session('error') }}</div>@endif

    @php
        $totalRecursos = $recursos->total();
        $disponibles = $recursos->getCollection()->sum('cantidad_disponible');
        $bajo = $recursos->getCollection()->filter(fn($r) => in_array($r->semaforo, ['amarillo', 'rojo']))->count();
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3"><div class="stat-card-clean h-100"><div class="d-flex align-items-center gap-3"><div class="stat-icon-box" style="background:#E0F2FE;color:#0288D1"><i class="fas fa-cubes"></i></div><div><div class="text-muted small fw-semibold">Recursos visibles</div><div class="h3 fw-bold mb-0" style="color:#001A29">{{ $totalRecursos }}</div></div></div></div></div>
        <div class="col-6 col-md-3"><div class="stat-card-clean h-100"><div class="d-flex align-items-center gap-3"><div class="stat-icon-box" style="background:#DCFCE7;color:#16A34A"><i class="fas fa-boxes-stacked"></i></div><div><div class="text-muted small fw-semibold">Unidades disponibles</div><div class="h3 fw-bold mb-0 text-success">{{ $disponibles }}</div></div></div></div></div>
        <div class="col-6 col-md-3"><div class="stat-card-clean h-100"><div class="d-flex align-items-center gap-3"><div class="stat-icon-box" style="background:#FEF3C7;color:#D97706"><i class="fas fa-triangle-exclamation"></i></div><div><div class="text-muted small fw-semibold">Stock bajo</div><div class="h3 fw-bold mb-0 text-warning">{{ $bajo }}</div></div></div></div></div>
        <div class="col-6 col-md-3"><div class="stat-card-clean h-100"><div class="d-flex align-items-center gap-3"><div class="stat-icon-box" style="background:#EDE9FE;color:#6D28D9"><i class="fas fa-calendar-check"></i></div><div><div class="text-muted small fw-semibold">Actividades propias</div><div class="h3 fw-bold mb-0" style="color:#6D28D9">{{ $cronogramas->count() }}</div></div></div></div></div>
    </div>

    <div class="card border-0 shadow-sm mb-4" style="border-radius:16px"><div class="card-body">
        <form action="{{ route('controlecp.instructor.inventario') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-4"><label class="form-label small fw-semibold text-muted">Buscar recurso</label><input name="buscar" value="{{ request('buscar') }}" class="form-control" placeholder="Nombre del recurso"></div>
            <div class="col-12 col-md-3"><label class="form-label small fw-semibold text-muted">Categoría</label><select name="categoria_id" class="form-select"><option value="">Todas</option>@foreach($categorias as $categoria)<option value="{{ $categoria->id_categoria_inventario }}" @selected(request('categoria_id') == $categoria->id_categoria_inventario)>{{ $categoria->nombre }}</option>@endforeach</select></div>
            <div class="col-6 col-md-2"><label class="form-label small fw-semibold text-muted">Estado físico</label><select name="estado_fisico" class="form-select"><option value="">Todos</option>@foreach(\Modules\ControlECP\Entities\Inventario::ESTADOS_FISICOS as $estado)<option @selected(request('estado_fisico') === $estado)>{{ $estado }}</option>@endforeach</select></div>
            <div class="col-6 col-md-2"><label class="form-label small fw-semibold text-muted">Semáforo</label><select name="semaforo" class="form-select"><option value="">Todos</option><option value="verde" @selected(request('semaforo') === 'verde')>Verde</option><option value="amarillo" @selected(request('semaforo') === 'amarillo')>Amarillo</option><option value="rojo" @selected(request('semaforo') === 'rojo')>Rojo</option></select></div>
            <div class="col-12 col-md-1"><button class="btn btn-success w-100" title="Filtrar"><i class="fas fa-filter"></i></button></div>
        </form>
    </div></div>

    <div class="card border-0 shadow-sm" style="border-radius:16px"><div class="table-responsive"><table class="table inventory-table mb-0">
        <thead><tr><th>Recurso</th><th>Categoría</th><th>Cantidad</th><th>Estado físico</th><th>Semáforo</th><th class="text-end">Acciones</th></tr></thead>
        <tbody>
        @forelse($recursos as $recurso)
            @php $badge = $recurso->semaforo_badge; $foto = $recurso->fotos->first(); @endphp
            <tr><td><div class="d-flex align-items-center gap-3">@if($foto)<img src="{{ asset($foto->ruta) }}" class="inventory-photo" alt="{{ $recurso->nombre }}">@else<div class="inventory-photo bg-light d-flex align-items-center justify-content-center"><i class="fas fa-box-open text-muted"></i></div>@endif<div><div class="fw-bold text-dark">{{ $recurso->nombre }}</div><small class="text-muted">{{ $recurso->tipo_recurso }}</small></div></div></td><td>{{ $recurso->categoria?->nombre ?? 'Sin categoría' }}</td><td><strong>{{ $recurso->cantidad_disponible }}</strong> / {{ $recurso->cantidad_total }}</td><td><span class="badge rounded-pill bg-light text-dark border">{{ $recurso->estado_fisico }}</span></td><td><span class="badge rounded-pill" style="background:{{ $badge['bg'] }};color:{{ $badge['text'] }}"><i class="{{ $badge['icon'] }} me-1"></i>{{ $badge['label'] }}</span></td><td class="text-end"><a href="{{ route('controlecp.instructor.inventario.show', $recurso->id_inventario) }}" class="btn-action-icon text-primary" title="Ver detalle"><i class="fas fa-eye"></i></a></td></tr>
        @empty<tr><td colspan="6" class="text-center text-muted py-5"><i class="fas fa-box-open fs-2 d-block mb-2"></i>No se encontraron recursos.</td></tr>@endforelse
        </tbody>
    </table></div><div class="p-3">{{ $recursos->links() }}</div></div>
</div>
@endsection
