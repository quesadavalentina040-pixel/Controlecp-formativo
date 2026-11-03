@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .ficha-page { font-family: 'Outfit', sans-serif; }
    .ficha-card, .kpi-card { background:#fff; border:1px solid rgba(0,0,0,.04); box-shadow:0 4px 18px rgba(0,26,41,.07); border-radius:18px; }
    .ficha-card { transition:transform .2s, box-shadow .2s; }
    .ficha-card:hover { transform:translateY(-3px); box-shadow:0 10px 28px rgba(0,26,41,.12); }
    .kpi-card { padding:1rem; }
    .kpi-value { font-size:1.6rem; font-weight:800; color:#001A29; }
    .kpi-label { color:#7a8394; font-size:.78rem; }
</style>
@endpush

@section('dashboard-content')
<div class="ficha-page container-fluid px-2 px-md-3 py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1 small text-muted">
                <li class="breadcrumb-item"><a href="{{ route('controlecp.instructor.inicio') }}" class="text-decoration-none text-success">Inicio</a></li>
                <li class="breadcrumb-item active">Fichas</li>
            </ol></nav>
            <h2 class="h3 fw-bold mb-0" style="color:#001A29;"><i class="fas fa-users-rectangle me-2 text-success"></i>Mis fichas</h2>
            <p class="text-muted small mb-0">Fichas con actividades asignadas para seguimiento.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3"><div class="kpi-card"><div class="kpi-value text-success">{{ $fichas->count() }}</div><div class="kpi-label">Fichas asignadas</div></div></div>
        <div class="col-6 col-md-3"><div class="kpi-card"><div class="kpi-value text-primary">{{ $fichas->sum('actividades_asignadas') }}</div><div class="kpi-label">Actividades asignadas</div></div></div>
        <div class="col-6 col-md-3"><div class="kpi-card"><div class="kpi-value text-info">{{ $fichas->sum('actividades_finalizadas') }}</div><div class="kpi-label">Actividades finalizadas</div></div></div>
        <div class="col-6 col-md-3"><div class="kpi-card"><div class="kpi-value" style="color:#6A1B9A">{{ $fichas->sum('momentos_certificados') }}</div><div class="kpi-label">Momentos certificados</div></div></div>
    </div>

    <div class="input-group mb-4">
        <span class="input-group-text bg-white"><i class="fas fa-search text-success"></i></span>
        <input id="buscarFicha" type="search" class="form-control" placeholder="Buscar por número de ficha o programa...">
    </div>

    <div id="fichasGrid" class="row g-3">
        @forelse($fichas as $ficha)
            <div class="col-12 col-md-6 col-xl-4 ficha-item" data-search="{{ strtolower($ficha->code.' '.($ficha->program->name ?? '')) }}">
                <div class="ficha-card h-100 p-3">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div><span class="text-muted small">Ficha</span><h4 class="fw-bold mb-0" style="color:#001A29">{{ $ficha->code }}</h4></div>
                        <span class="badge rounded-pill {{ $ficha->status === 'Activo' ? 'bg-success' : 'bg-secondary' }}">{{ $ficha->status ?? 'Sin estado' }}</span>
                    </div>
                    <p class="mb-3"><strong>Programa:</strong><br>{{ $ficha->program->name ?? 'Sin programa' }}</p>
                    <div class="row text-center border-top pt-3 mb-3">
                        <div class="col-4"><strong>{{ $ficha->actividades_asignadas }}</strong><small class="d-block text-muted">Asignadas</small></div>
                        <div class="col-4"><strong>{{ $ficha->actividades_finalizadas }}</strong><small class="d-block text-muted">Finalizadas</small></div>
                        <div class="col-4"><strong>{{ $ficha->porcentaje_asistencia }}%</strong><small class="d-block text-muted">Asistencia</small></div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-success btn-sm flex-fill" data-bs-toggle="modal" data-bs-target="#aprendices-{{ $ficha->id }}"><i class="fas fa-users me-1"></i>Aprendices ({{ $ficha->apprentices->count() }})</button>
                        <button class="btn btn-success btn-sm flex-fill" data-bs-toggle="modal" data-bs-target="#avance-{{ $ficha->id }}"><i class="fas fa-chart-line me-1"></i>Avance</button>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="aprendices-{{ $ficha->id }}" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Aprendices - ficha {{ $ficha->code }}</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body"><div class="list-group">
                    @forelse($ficha->apprentices as $aprendiz)
                        <div class="list-group-item"><strong>{{ $aprendiz->person->full_name ?? 'Sin nombre' }}</strong><br><small class="text-muted">{{ $aprendiz->person->personal_email ?? $aprendiz->person->sena_email ?? 'Sin correo' }} · {{ $aprendiz->person->telephone1 ?? 'Sin teléfono' }}</small></div>
                    @empty <p class="text-muted mb-0">No hay aprendices registrados.</p> @endforelse
                </div></div>
            </div></div></div>
            <div class="modal fade" id="avance-{{ $ficha->id }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Avance de ficha {{ $ficha->code }}</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body"><p><strong>Asistencia:</strong> {{ $ficha->porcentaje_asistencia }}%</p><p><strong>Actividades finalizadas:</strong> {{ $ficha->actividades_finalizadas }} de {{ $ficha->actividades_asignadas }}</p><p><strong>Momentos certificados:</strong> {{ $ficha->momentos_certificados }}</p></div>
            </div></div></div>
        @empty
            <div class="col-12"><div class="alert alert-info">No tienes fichas con actividades asignadas.</div></div>
        @endforelse
    </div>
</div>
@push('scripts')
<script>
document.getElementById('buscarFicha').addEventListener('input', function () {
    const term = this.value.toLowerCase().trim();
    document.querySelectorAll('.ficha-item').forEach(item => { item.classList.toggle('d-none', !item.dataset.search.includes(term)); });
});
</script>
@endpush
@endsection
