@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
<style>
    .certificados-kpi {
        height: 100%;
        border: 2px solid transparent;
        border-radius: 14px;
        background: #fff;
    }
    .certificados-kpi .card-body {
        padding: .8rem 1rem;
    }
    .certificados-kpi-label {
        color: #475569;
        font-size: .95rem;
        font-weight: 500;
    }
    .certificados-kpi-value {
        margin-top: .15rem;
        font-size: 1.7rem;
        font-weight: 800;
        line-height: 1;
    }
    .certificados-kpi-description {
        color: #64748b;
        font-size: .9rem;
    }
    .certificados-kpi-icon {
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    .certificados-filter {
        border: 0;
        border-radius: 18px;
        background: #001a29;
        box-shadow: 0 4px 12px rgba(0, 26, 41, .12);
    }
    .certificados-filter-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr)) auto auto;
        align-items: end;
        gap: .6rem;
    }
    .certificados-filter-grid .form-label {
        display: block;
        margin-bottom: .35rem;
        color: #fff;
        font-size: .78rem;
        font-weight: 700;
    }
    .certificados-filter-grid .form-control,
    .certificados-filter-grid .form-select {
        min-height: 44px;
        border: 1px solid #dce5eb;
        border-radius: 10px;
        background-color: #fff;
        color: #001a29;
        box-shadow: none;
    }
    .certificados-filter-grid .form-control:focus,
    .certificados-filter-grid .form-select:focus {
        border-color: #39a900;
        box-shadow: 0 0 0 .2rem rgba(57, 169, 0, .2);
    }
    .certificados-filter-submit,
    .certificados-filter-reset {
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-weight: 700;
        white-space: nowrap;
    }
    .certificados-filter-submit {
        min-width: 126px;
        padding: 0 1rem;
        border: 1px solid #39a900;
        background: #39a900;
        color: #fff;
    }
    .certificados-filter-submit:hover {
        border-color: #2e8800;
        background: #2e8800;
        color: #fff;
    }
    .certificados-filter-reset {
        width: 44px;
        border: 1px solid #fff;
        background: #fff;
        color: #001a29;
        text-decoration: none;
    }
    .certificados-moment-table {
        overflow: hidden;
        border: 0;
        border-radius: 16px;
        box-shadow: 0 4px 14px rgba(0, 26, 41, .07);
    }
    .certificados-moment-table .card-header {
        color: #001a29;
        background: #fff;
    }
    #certificadosMomentosTab {
        display: inline-flex;
        flex-wrap: wrap;
        max-width: 100%;
    }
    #certificadosMomentosTab .nav-link {
        color: #001a29;
    }
    #certificadosMomentosTab .nav-link.active,
    #certificadosMomentosTab .show > .nav-link {
        background-color: #39a900;
        color: #fff;
    }
    #certificadosMomentosTab .nav-link:not(.active):hover {
        color: #2e8800;
    }
    .certificados-moment-table thead {
        font-size: .82rem;
        font-weight: 700;
    }
    .certificados-progress-content {
        width: 100%;
        max-width: 260px;
        margin-inline: auto;
    }
    .certificados-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .42rem .75rem;
        border: 1px solid transparent;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .certificados-status--certified {
        color: #16834f;
        background: #d7eee4;
        border-color: #a4d5bd;
    }
    .certificados-status--ready {
        color: #a16207;
        background: #fef3c7;
        border-color: #fde68a;
    }
    .certificados-status--progress {
        color: #2563eb;
        background: #dbeafe;
        border-color: #bfdbfe;
    }
    .certificados-row-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: .4rem;
        white-space: nowrap;
    }
    .certificados-certify-form {
        display: inline-flex;
        flex: 0 0 auto;
        margin: 0;
    }
    .certificados-row-action {
        display: inline-flex;
        width: 34px;
        height: 34px;
        align-items: center;
        justify-content: center;
        padding: 0;
        border: 1px solid transparent;
        border-radius: 10px;
    }
    .certificados-row-action--view {
        color: #0876bd;
        background: #eaf5ff;
        border-color: #c9e6fb;
    }
    .certificados-row-action--history {
        color: #7c3aed;
        background: #f3e8ff;
        border-color: #e9d5ff;
    }
    .certificados-row-action--certify {
        width: auto;
        color: #2e8800;
        background: #e8f7df;
        border-color: #c9eabb;
    }
    .certificados-info-modal .modal-content {
        overflow: hidden;
        border: 0;
        border-radius: 20px;
        box-shadow: 0 22px 55px rgba(0, 26, 41, .2);
    }
    .certificados-info-modal .modal-header {
        padding: 1.1rem 1.35rem;
        border-bottom: 3px solid #39a900;
        background: linear-gradient(120deg, #001a29, #00324d);
        color: #fff;
    }
    .certificados-info-modal .modal-title { color: #fff; font-size: 1.05rem; font-weight: 800; }
    .certificados-info-modal .modal-header .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }
    .certificados-info-modal .modal-body { padding: 1.35rem; background: #f5f9fb; }
    .certificados-info-modal .modal-footer { padding: .9rem 1.35rem 1.1rem; border-top: 1px solid #e8eef2; background: #fff; }
    .certificados-info-modal .activities-group {
        height: 100%;
        padding: 1rem;
        border: 1px solid #e2eaf0;
        border-radius: 14px;
        background: #fff;
    }
    .certificados-info-modal .activities-group--done { border-top: 3px solid #39a900; }
    .certificados-info-modal .activities-group--pending { border-top: 3px solid #94a3b8; }
    .certificados-info-modal .activities-group-title {
        display: flex;
        align-items: center;
        gap: .55rem;
        margin: 0 0 .85rem;
        color: #123047;
        font-size: .92rem;
        font-weight: 800;
    }
    .certificados-info-modal .activities-count {
        display: inline-flex;
        min-width: 25px;
        height: 25px;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #e8f7df;
        color: #278344;
        font-size: .75rem;
    }
    .certificados-info-modal .activities-group--pending .activities-count { background: #eef2f6; color: #596879; }
    .certificados-info-modal .activities-list { display: grid; gap: .5rem; margin: 0; padding: 0; list-style: none; }
    .certificados-info-modal .activities-list li {
        display: flex;
        align-items: flex-start;
        gap: .55rem;
        padding: .65rem .7rem;
        border: 1px solid #edf1f4;
        border-radius: 10px;
        background: #fbfdfe;
        color: #334155;
        font-size: .86rem;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }
    .certificados-info-modal .activities-group--done .activities-list li > i { margin-top: .15rem; color: #39a900; }
    .certificados-info-modal .activities-group--pending .activities-list li > i { margin-top: .15rem; color: #94a3b8; }
    .certificados-info-modal .empty-activities {
        margin: 0;
        padding: .8rem;
        border-radius: 10px;
        background: #f8fafc;
        color: #64748b;
        font-size: .84rem;
    }
    .certificados-info-modal .history-summary {
        display: flex;
        align-items: flex-start;
        gap: .85rem;
        padding: 1rem;
        border: 1px solid #ccebd6;
        border-radius: 14px;
        background: #effaf2;
        color: #23465d;
        line-height: 1.55;
    }
    .certificados-info-modal .history-summary-icon {
        display: inline-flex;
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: #d8f3dc;
        color: #278344;
        font-size: 1rem;
    }
    .certificados-info-modal .history-summary strong { display: block; margin-bottom: .15rem; color: #123047; }
    .certificados-info-modal .btn-close-modal {
        min-width: 100px;
        border: 1px solid #39a900;
        border-radius: 9px;
        background: #39a900;
        color: #fff;
        font-weight: 700;
    }
    .certificados-info-modal .btn-close-modal:hover { border-color: #2e8b00; background: #2e8b00; color: #fff; }
    @media (max-width: 575.98px) {
        .certificados-info-modal .modal-header,
        .certificados-info-modal .modal-body { padding: 1rem; }
        .certificados-info-modal .modal-footer { padding: .8rem 1rem 1rem; }
    }
    @media (max-width: 1199.98px) {
        .certificados-filter-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr)) auto auto;
        }
    }
    @media (max-width: 575.98px) {
        .certificados-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) auto;
        }
    }
</style>
<div class="container-fluid px-3 py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 p-4 mb-4 text-white" style="background:linear-gradient(120deg,#001A29,#00324D);border-radius:20px">
        <div>
            <div class="small fw-bold text-uppercase mb-2" style="color:#62E31D;letter-spacing:.1em">Control ECP · Instructor</div>
            <h1 class="h3 fw-bold mb-1"><i class="fas fa-certificate me-2" style="color:#62E31D"></i>Certificación por momento</h1>
            <p class="mb-0 text-white-50">Certifica los momentos completados en las fichas donde has trabajado.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card certificados-kpi" style="border-color:rgba(14,165,233,.4);">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="certificados-kpi-label">Fichas para certificar</div>
                        <span class="certificados-kpi-icon" style="background:#e0f2fe;color:#0284c7;"><i class="fas fa-folder-open"></i></span>
                    </div>
                    <div class="certificados-kpi-value" style="color:#0284c7;">{{ $stats['para_certificar'] }}</div>
                    <div class="certificados-kpi-description">Con actividades listas</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card certificados-kpi" style="border-color:rgba(34,197,94,.4);">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="certificados-kpi-label">Fichas certificadas</div>
                        <span class="certificados-kpi-icon" style="background:#dcfce7;color:#16a34a;"><i class="fas fa-certificate"></i></span>
                    </div>
                    <div class="certificados-kpi-value" style="color:#16a34a;">{{ $stats['certificadas'] }}</div>
                    <div class="certificados-kpi-description">Con momentos certificados</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card certificados-kpi" style="border-color:rgba(248,113,113,.5);">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="certificados-kpi-label">Fichas pendientes por certificar</div>
                        <span class="certificados-kpi-icon" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-clock"></i></span>
                    </div>
                    <div class="certificados-kpi-value" style="color:#dc2626;">{{ $stats['pendientes'] }}</div>
                    <div class="certificados-kpi-description">Con actividades en progreso</div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <form method="GET" action="{{ route('controlecp.instructor.certificados') }}" class="card certificados-filter mb-4">
        <div class="card-body p-3">
            <div class="certificados-filter-grid">
                <div>
                    <label class="form-label" for="filtroFicha">Ficha</label>
                    <input id="filtroFicha" class="form-control" name="ficha" value="{{ request('ficha') }}" placeholder="Número de ficha">
                </div>
                <div>
                    <label class="form-label" for="filtroActividad">Actividad</label>
                    <input id="filtroActividad" class="form-control" name="actividad" value="{{ request('actividad') }}" placeholder="Escribe una actividad">
                </div>
                <div>
                    <label class="form-label" for="filtroEstado">Estado</label>
                    <select id="filtroEstado" class="form-select" name="estado">
                        <option value="">Todos</option>
                        <option value="Certificada" @selected(request('estado') === 'Certificada')>Certificada</option>
                        <option value="Pendiente de certificar" @selected(request('estado') === 'Pendiente de certificar')>Pendiente de certificar</option>
                        <option value="En progreso" @selected(request('estado') === 'En progreso')>En progreso</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" for="filtroDesde">Desde</label>
                    <input id="filtroDesde" class="form-control" type="date" name="desde" value="{{ request('desde') }}">
                </div>
                <div>
                    <label class="form-label" for="filtroHasta">Hasta</label>
                    <input id="filtroHasta" class="form-control" type="date" name="hasta" value="{{ request('hasta') }}">
                </div>
                <button class="btn certificados-filter-submit" type="submit"><i class="fas fa-search me-1"></i>Buscar</button>
                <a class="certificados-filter-reset" href="{{ route('controlecp.instructor.certificados') }}" aria-label="Limpiar filtros" title="Limpiar filtros">
                    <i class="fas fa-rotate-left"></i>
                </a>
            </div>
        </div>
    </form>

    @if($moments->count() < 3)
        <div class="alert alert-info">
            Hay {{ $moments->count() }} momentos configurados. Cuando el administrador agregue el tercero, aparecerá aquí y deberá certificarse después de los anteriores. La constancia PDF se habilitará al completar los tres momentos.
        </div>
    @endif

    <ul class="nav nav-pills p-1 bg-light rounded-pill border mb-3" id="certificadosMomentosTab" role="tablist">
        @foreach($moments as $momentoTab)
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $loop->first ? 'active' : '' }} rounded-pill px-4 fw-bold"
                        id="tab-momento-{{ $momentoTab->id }}"
                        data-bs-toggle="pill"
                        data-bs-target="#contenido-momento-{{ $momentoTab->id }}"
                        type="button" role="tab"
                        aria-controls="contenido-momento-{{ $momentoTab->id }}"
                        aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                    Momento {{ $momentoTab->orden }}
                </button>
            </li>
        @endforeach
    </ul>

    <div class="tab-content" id="certificadosMomentosContenido">
    @foreach($moments as $momentoTabla)
        @php
            $fichasMomento = $rows->filter(
                fn ($row) => $row['momentos']->contains('id', $momentoTabla->id)
            );
        @endphp
        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
             id="contenido-momento-{{ $momentoTabla->id }}"
             role="tabpanel"
             aria-labelledby="tab-momento-{{ $momentoTabla->id }}">
            <section class="card certificados-moment-table mb-4">
                <div class="card-header border-0 p-4">
                    <h2 class="h5 fw-bold mb-0">Momento {{ $momentoTabla->orden }} · {{ $momentoTabla->nombre }}</h2>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Ficha</th>
                                <th>Nombre del programa</th>
                                <th>Progreso</th>
                                <th>Estado</th>
                                <th class="text-end pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($fichasMomento as $row)
                                @php
                                    $momento = $row['momentos']->firstWhere('id', $momentoTabla->id);
                                    $detalleId = 'detalle-momento-' . $row['course']->id . '-' . $momento['id'];
                                    $historialId = 'historial-momento-' . $row['course']->id . '-' . $momento['id'];
                                    $estadoClase = $momento['estado'] === 'Certificada'
                                        ? 'certificados-status--certified'
                                        : ($momento['estado'] === 'Pendiente de certificar' ? 'certificados-status--ready' : 'certificados-status--progress');
                                @endphp
                                <tr>
                                    <td class="ps-4 fw-bold">
                                        {{ $row['course']->code }}
                                    </td>
                                    <td>{{ $row['course']->program?->name ?? '—' }}</td>
                                    <td style="min-width:180px">
                                        <div class="certificados-progress-content">
                                            <div class="d-flex justify-content-between small">
                                                <span>{{ $momento['completadas'] }}/{{ $momento['total'] }} actividades</span>
                                                <span>{{ $momento['porcentaje'] }}%</span>
                                            </div>
                                            <div class="progress mt-1" style="height:7px">
                                                <div class="progress-bar bg-success" style="width:{{ $momento['porcentaje'] }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="certificados-status {{ $estadoClase }}">{{ $momento['estado'] }}</span>
                                        @if($momento['bloqueado_por_secuencia'])
                                            <div class="small text-muted mt-1">Primero certifica el momento anterior.</div>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="certificados-row-actions">
                                            @if($momento['estado'] === 'Pendiente de certificar')
                                                <form class="certificados-certify-form" method="POST" action="{{ route('controlecp.instructor.certificados.certificar', [$row['course']->id, $momento['id']]) }}" onsubmit="return confirm('¿Confirmas certificar el momento {{ $momento['nombre'] }} de la ficha {{ $row['course']->code }}?')">
                                                    @csrf
                                                    <button type="submit" class="btn certificados-row-action certificados-row-action--certify px-3"
                                                            title="Certificar momento">
                                                        <i class="fas fa-certificate me-2"></i>Certificar
                                                    </button>
                                                </form>
                                            @else
                                                <button type="button" class="btn certificados-row-action certificados-row-action--view"
                                                        data-bs-toggle="modal" data-bs-target="#{{ $detalleId }}"
                                                        title="Ver actividades" aria-label="Ver actividades">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn certificados-row-action certificados-row-action--history"
                                                        data-bs-toggle="modal" data-bs-target="#{{ $historialId }}"
                                                        title="Historial de certificación" aria-label="Historial de certificación">
                                                    <i class="fas fa-clock-rotate-left"></i>
                                                </button>
                                            @endif
                                            @if($row['certificacion_completa'] && $momentoTabla->id === $moments->last()->id)
                                                <a class="btn certificados-row-action certificados-row-action--certify"
                                                   href="{{ route('controlecp.instructor.certificados.constancia', $row['course']->id) }}"
                                                   title="Descargar constancia PDF" aria-label="Descargar constancia PDF">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        No hay fichas que coincidan con los filtros de este momento.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @foreach($fichasMomento as $row)
                    @php
                        $momento = $row['momentos']->firstWhere('id', $momentoTabla->id);
                        $detalleId = 'detalle-momento-' . $row['course']->id . '-' . $momento['id'];
                        $historialId = 'historial-momento-' . $row['course']->id . '-' . $momento['id'];
                    @endphp
                    <div class="modal fade certificados-info-modal" id="{{ $detalleId }}" tabindex="-1" aria-labelledby="{{ $detalleId }}-label" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <div>
                                        <span class="small text-white-50 fw-semibold">CERTIFICACIÓN POR MOMENTO</span>
                                        <h2 class="modal-title fs-5 fw-bold mt-1 mb-0" id="{{ $detalleId }}-label">
                                            Actividades · Ficha {{ $row['course']->code }} · Momento {{ $momento['orden'] }}
                                        </h2>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="activities-group activities-group--done">
                                            <h3 class="activities-group-title"><i class="fas fa-circle-check text-success" aria-hidden="true"></i>Actividades completadas <span class="activities-count">{{ $momento['finalizadas']->count() }}</span></h3>
                                            @if($momento['finalizadas']->isEmpty())
                                                <p class="empty-activities">Aún no hay actividades completadas.</p>
                                            @else
                                                <ul class="activities-list">
                                                    @foreach($momento['finalizadas'] as $actividad)
                                                        <li><i class="fas fa-check" aria-hidden="true"></i><span>{{ $actividad->numero }}. {{ $actividad->nombre }}</span></li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="activities-group activities-group--pending">
                                            <h3 class="activities-group-title"><i class="fas fa-list-check text-secondary" aria-hidden="true"></i>Actividades pendientes <span class="activities-count">{{ $momento['pendientes']->count() }}</span></h3>
                                            @if($momento['pendientes']->isEmpty())
                                                <p class="empty-activities">No hay actividades pendientes.</p>
                                            @else
                                                <ul class="activities-list">
                                                    @foreach($momento['pendientes'] as $actividad)
                                                        <li><i class="far fa-circle" aria-hidden="true"></i><span>{{ $actividad->numero }}. {{ $actividad->nombre }}</span></li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if($momento['estado'] === 'Pendiente de certificar')
                                    <div class="modal-footer">
                                        <form method="POST" action="{{ route('controlecp.instructor.certificados.certificar', [$row['course']->id, $momento['id']]) }}" onsubmit="return confirm('¿Confirmas certificar el momento {{ $momento['nombre'] }} de la ficha {{ $row['course']->code }}?')">
                                            @csrf
                                            <button class="btn btn-sm btn-success rounded-2 px-3" type="submit">
                                                <i class="fas fa-certificate me-1"></i>Certificar momento
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="modal fade certificados-info-modal" id="{{ $historialId }}" tabindex="-1" aria-labelledby="{{ $historialId }}-label" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-sm">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <div>
                                        <span class="small text-white-50 fw-semibold">HISTORIAL DE CERTIFICACIÓN</span>
                                        <h2 class="modal-title fs-5 fw-bold mt-1 mb-0" id="{{ $historialId }}-label">
                                            Ficha {{ $row['course']->code }} · Momento {{ $momento['orden'] }}
                                        </h2>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    @if($momento['certificacion'])
                                        <div class="history-summary">
                                            <span class="history-summary-icon"><i class="fas fa-certificate" aria-hidden="true"></i></span>
                                            <div>
                                                <strong>Momento certificado</strong>
                                                <span>{{ $momento['certificacion']->responsable_tipo === 'instructor' ? 'Un instructor' : 'Un administrador' }} certificó este momento el {{ \Carbon\Carbon::parse($momento['certificacion']->fecha_certificacion, 'UTC')->timezone('America/Bogota')->format('d/m/Y g:i A') }}.</span>
                                            </div>
                                        </div>
                                    @else
                                        <div class="history-summary">
                                            <span class="history-summary-icon"><i class="fas fa-clock" aria-hidden="true"></i></span>
                                            <div>
                                                <strong>Pendiente de certificación</strong>
                                                <span>Este momento aún no ha sido certificado por un instructor.</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-close-modal px-3" data-bs-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </section>
        </div>
    @endforeach
    </div>
    @if($rows->hasPages())
        <div class="d-flex justify-content-center mb-4">
            @if ($rows->hasPages())
                <nav aria-label="Paginación de certificados">
                    <ul class="pagination justify-content-center">
                        @if ($rows->onFirstPage())
                            <li class="page-item disabled"><span class="page-link" aria-hidden="true">&lsaquo;</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $rows->previousPageUrl() }}" aria-label="Página anterior">&lsaquo;</a></li>
                        @endif
                        @php
                                $ultimaPaginaMostrada = 0;
                            @endphp
                        @for ($pagina = 1; $pagina <= $rows->lastPage(); $pagina++)
                            @if ($pagina === 1 || $pagina === $rows->lastPage() || abs($pagina - $rows->currentPage()) <= 1)
                                @if ($ultimaPaginaMostrada > 0 && $pagina > $ultimaPaginaMostrada + 1)
                                    <li class="page-item disabled"><span class="page-link">…</span></li>
                                @endif
                                <li class="page-item {{ $pagina === $rows->currentPage() ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $rows->url($pagina) }}" @if($pagina === $rows->currentPage()) aria-current="page" @endif>{{ $pagina }}</a>
                                </li>
                                @php
                                    $ultimaPaginaMostrada = $pagina;
                                @endphp
                            @endif
                        @endfor
                        @if ($rows->hasMorePages())
                            <li class="page-item"><a class="page-link" href="{{ $rows->nextPageUrl() }}" aria-label="Página siguiente">&rsaquo;</a></li>
                        @else
                            <li class="page-item disabled"><span class="page-link" aria-hidden="true">&rsaquo;</span></li>
                        @endif
                    </ul>
                </nav>
            @endif
        </div>
    @endif

</div>
@endsection
