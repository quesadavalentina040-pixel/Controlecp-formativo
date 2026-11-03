@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
<div class="container-fluid px-3 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h2 class="fw-bold text-dark mb-1"><i class="fas fa-comment-dots text-success me-2"></i>Mis PQRS</h2><p class="text-muted mb-0">Solicitudes dirigidas a usted</p></div>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    <div class="row g-3 mb-4">
        @foreach(['total'=>'Total radicadas','pendientes'=>'Pendientes','tramite'=>'En trámite','resueltas'=>'Resueltas'] as $key => $label)
            <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm p-3 h-100"><small class="text-muted">{{ $label }}</small><strong class="fs-3 text-dark">{{ $totales[$key] }}</strong></div></div>
        @endforeach
    </div>
    <div class="card border-0 shadow-sm mb-4"><div class="card-body">
        <form class="row g-2" method="GET">
            <div class="col-md-5"><input name="buscar" value="{{ request('buscar') }}" class="form-control" placeholder="Buscar por radicado o asunto"></div>
            <div class="col-md-3"><select name="id_tipo_pqrs" class="form-select"><option value="">Todos los tipos</option>@foreach($tipos as $tipo)<option value="{{ $tipo->id_tipo_pqrs }}" @selected(request('id_tipo_pqrs') == $tipo->id_tipo_pqrs)>{{ $tipo->nombre }}</option>@endforeach</select></div>
            <div class="col-md-2"><select name="estado" class="form-select"><option value="">Todos los estados</option>@foreach(\Modules\ControlECP\Entities\Pqrs::ESTADOS as $estado)<option @selected(request('estado') === $estado)>{{ $estado }}</option>@endforeach</select></div>
            <div class="col-md-2"><button class="btn btn-success w-100">Filtrar</button></div>
        </form>
    </div></div>
    <div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0">
        <thead><tr><th>Radicado</th><th>Solicitante</th><th>Tipo</th><th>Asunto</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        @forelse($pqrs as $pqr)
            <tr><td class="fw-semibold">{{ $pqr->radicado }}</td><td>{{ $pqr->aprendiz?->nickname ?? 'Sin nombre' }}</td><td>{{ $pqr->tipo?->nombre ?? 'Petición' }}</td><td>{{ Str::limit($pqr->asunto, 45) }}</td><td><span class="badge text-bg-light border">{{ $pqr->estado }}</span></td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('controlecp.instructor.pqr.show', $pqr->id_pqrs) }}"><i class="fas fa-eye"></i></a></td></tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-5">No se encontraron solicitudes PQRS</td></tr>
        @endforelse
        </tbody>
    </table></div><div class="p-3">{{ $pqrs->links() }}</div></div>
</div>
@endsection
