@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    /* ── Tarjetas métricas ── */
    .pqr-stat-card {
        background: #fff;
        border: 1px solid #E2E8F0;
        border-radius: 18px;
        padding: 1.35rem 1.5rem;
        transition: transform .2s, box-shadow .2s;
        box-shadow: 0 2px 10px rgba(0,26,41,.04);
        position: relative; overflow: hidden;
    }
    .pqr-stat-card::before {
        content: ''; position: absolute; top: 0; left: 0;
        width: 4px; height: 100%; border-radius: 18px 0 0 18px;
    }
    .pqr-stat-card.accent-blue::before   { background: #0288D1; }
    .pqr-stat-card.accent-amber::before  { background: #D97706; }
    .pqr-stat-card.accent-indigo::before { background: #4F46E5; }
    .pqr-stat-card.accent-green::before  { background: #16A34A; }
    .pqr-stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,26,41,.1); }

    .pqr-stat-icon {
        width: 50px; height: 50px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem; flex-shrink: 0;
    }

    /* ── Botones de acción ── */
    .btn-tbl-action {
        width: 34px; height: 34px; padding: 0;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 9px; font-size: 0.82rem;
        border: 1px solid #E2E8F0; background: #FFFFFF;
        transition: all .15s ease; text-decoration: none; cursor: pointer;
    }
    .btn-tbl-action:hover { transform: scale(1.1); box-shadow: 0 3px 10px rgba(0,26,41,.12); }
    .btn-tbl-action:disabled, .btn-tbl-action[disabled] {
        opacity: .4; cursor: not-allowed; pointer-events: none;
    }

    /* ── Avatar ── */
    .av-badge {
        width: 38px; height: 38px; border-radius: 50%;
        background: #E2F5D7; color: #39A900;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: .88rem;
        border: 1.5px solid #A3E070; flex-shrink: 0;
    }

    /* ── Sección instructores (solo lectura) ── */
    .section-readonly {
        background: #FAFBFD;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        overflow: hidden;
    }
    .section-readonly .tbl-body-row td { color: #64748B; }

    /* ── Tabla ── */
    .tbl-head-row th {
        background: #F8FAFC; color: #475569;
        font-size: .76rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .4px;
        border-bottom: 2px solid #E2E8F0;
        padding: .85rem 1rem;
    }
    .tbl-head-row th:first-child { padding-left: 1.5rem; }
    .tbl-head-row th:last-child  { padding-right: 1.5rem; }
    .tbl-body-row td {
        padding: .85rem 1rem; border-bottom: 1px solid #F1F5F9;
        vertical-align: middle;
    }
    .tbl-body-row td:first-child { padding-left: 1.5rem; }
    .tbl-body-row td:last-child  { padding-right: 1.5rem; }
    .tbl-body-row:last-child td  { border-bottom: none; }
    .tbl-body-row:hover td       { background: #FAFBFF; }

    /* ── Buscador ── */
    .inv-search .form-control    { border-radius: 0 50px 50px 0; border-left: 0; box-shadow: none; }
    .inv-search .input-group-text{ border-radius: 50px 0 0 50px; border-right: 0; background: #fff; }

    /* ── Badge solo lectura ── */
    .badge-readonly {
        background: #F1F5F9; color: #94A3B8;
        border: 1px dashed #CBD5E1;
        border-radius: 8px;
        font-size: .7rem; padding: .3rem .6rem;
        display: inline-flex; align-items: center; gap: .3rem;
    }

    /* ── Timeline ── */
    .timeline-item {
        position: relative; padding-left: 2rem; padding-bottom: 1.4rem;
        border-left: 2px solid #E2E8F0;
    }
    .timeline-item:last-child { border-left-color: transparent; padding-bottom: 0; }
    .timeline-dot {
        position: absolute; left: -7px; top: 3px;
        width: 12px; height: 12px; border-radius: 50%;
        border: 2px solid #fff; box-shadow: 0 0 0 2px #E2E8F0;
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-2 px-md-4 py-3">

    {{-- ══ CABECERA ══ --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge px-2 py-1 small fw-bold rounded-pill"
                      style="background:#E2F5D7; color:#39A900; font-size:.7rem;">
                    ROL ADMINISTRADOR
                </span>
                <span class="text-muted small">&bull; Escuela Cultura de Paz</span>
            </div>
            <h2 class="h3 fw-bold mb-0" style="color:#001A29; font-family:'Outfit',sans-serif;">
                <i class="fas fa-comment-dots me-2" style="color:#39A900;"></i>Gestión de PQRS
            </h2>
            <p class="text-muted small mb-0 mt-1">
                Supervisión y respuesta a solicitudes de la Escuela Cultura de Paz
            </p>
        </div>
        <button type="button"
                class="btn btn-outline-dark rounded-pill px-4 btn-sm shadow-sm d-flex align-items-center gap-2"
                data-bs-toggle="modal" data-bs-target="#modalReportePqrs"
                onclick="inicializarGraficasPqrs()">
            <i class="fas fa-chart-pie text-success"></i>
            <span class="fw-semibold">Generar reporte</span>
        </button>
    </div>

    {{-- ══ ALERTAS FLASH ══ --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4"
             style="background:#f0fbf4; color:#001A29;">
            <i class="fas fa-check-circle me-2" style="color:#39A900;"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ══ MÉTRICAS ══ --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="pqr-stat-card accent-blue h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="pqr-stat-icon" style="background:#E0F2FE; color:#0288D1;">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <div>
                        <div class="text-muted fw-semibold" style="font-size:.78rem;">Total recibidas</div>
                        <div class="h3 fw-bold mb-0" style="color:#001A29;">{{ $total }}</div>
                        <small class="text-muted" style="font-size:.7rem;">
                            <span class="text-success fw-semibold">{{ $pqrsAdmin->total() }}</span> admin ·
                            <span style="color:#4F46E5;" class="fw-semibold">{{ $pqrsInstructor->total() }}</span> instructores
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="pqr-stat-card accent-amber h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="pqr-stat-icon" style="background:#FEF3C7; color:#D97706;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <div class="text-muted fw-semibold" style="font-size:.78rem;">Pendientes</div>
                        <div class="h3 fw-bold mb-0 text-warning-emphasis">{{ $pendientes }}</div>
                        <small class="text-muted" style="font-size:.7rem;">sin respuesta aún</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="pqr-stat-card accent-indigo h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="pqr-stat-icon" style="background:#EEF2FF; color:#4F46E5;">
                        <i class="fas fa-spinner"></i>
                    </div>
                    <div>
                        <div class="text-muted fw-semibold" style="font-size:.78rem;">En trámite</div>
                        <div class="h3 fw-bold mb-0" style="color:#4F46E5;">{{ $enTramite }}</div>
                        <small class="text-muted" style="font-size:.7rem;">en revisión activa</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="pqr-stat-card accent-green h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="pqr-stat-icon" style="background:#DCFCE7; color:#16A34A;">
                        <i class="fas fa-circle-check"></i>
                    </div>
                    <div>
                        <div class="text-muted fw-semibold" style="font-size:.78rem;">Resueltos</div>
                        <div class="h3 fw-bold mb-0 text-success">{{ $resueltas }}</div>
                        <small class="text-muted" style="font-size:.7rem;">casos finalizados</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ FILTROS ══ --}}
    <div class="card border-0 shadow-sm mb-5" style="border-radius:16px;">
        <div class="card-body p-3">
            <form action="{{ route($soloInstructores ? 'controlecp.administrador.pqr.instructores' : 'controlecp.administrador.pqr') }}" method="GET"
                  class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <div class="input-group inv-search">
                        <span class="input-group-text text-muted"><i class="fas fa-magnifying-glass"></i></span>
                        <input type="text" name="buscar" value="{{ request('buscar') }}"
                               class="form-control" placeholder="Radicado, aprendiz, asunto…">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <select name="id_tipo_pqrs" class="form-select rounded-pill" onchange="this.form.submit()">
                        <option value="">Todos los tipos</option>
                        @foreach($tipos as $t)
                            <option value="{{ $t->id_tipo_pqrs }}"
                                {{ request('id_tipo_pqrs') == $t->id_tipo_pqrs ? 'selected' : '' }}>
                                {{ $t->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="estado" class="form-select rounded-pill" onchange="this.form.submit()">
                        <option value="">Todos los estados</option>
                        <option value="Pendiente"  {{ request('estado')=='Pendiente'  ?'selected':'' }}>🟡 Pendiente</option>
                        <option value="En trámite" {{ request('estado')=='En trámite' ?'selected':'' }}>🔵 En trámite</option>
                        <option value="Resuelto"   {{ request('estado')=='Resuelto'   ?'selected':'' }}>🟢 Resuelto</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <select name="id_instructor" class="form-select rounded-pill" onchange="this.form.submit()">
                        <option value="">Todos los instructores</option>
                        @foreach($instructores as $inst)
                            <option value="{{ $inst->id }}"
                                {{ request('id_instructor') == $inst->id ? 'selected' : '' }}>
                                👨‍🏫 {{ $inst->nickname }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn text-white rounded-pill px-3 flex-grow-1 fw-semibold"
                            style="background:#39A900;">
                        <i class="fas fa-filter me-1"></i>Filtrar
                    </button>
                    @if(request()->hasAny(['buscar','id_tipo_pqrs','estado','id_instructor']))
                        <a href="{{ route($soloInstructores ? 'controlecp.administrador.pqr.instructores' : 'controlecp.administrador.pqr') }}"
                           class="btn btn-outline-secondary rounded-pill" title="Limpiar">
                            <i class="fas fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if(!$soloInstructores)
    {{-- ══ TABS: ADMINISTRACIÓN / INSTRUCTORES ══ --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div class="d-flex gap-2">
            {{-- Tab Administración --}}
            <div class="d-flex align-items-center gap-2 rounded-pill px-4 py-2 fw-bold shadow-sm"
                 style="background:#39A900; color:#fff; font-size:.88rem; cursor:default;">
                <i class="fas fa-building-columns"></i>
                <span>Administración</span>
                <span class="badge rounded-pill fw-bold"
                      style="background:#fff; color:#39A900; font-size:.75rem; padding:.2rem .55rem;">
                    {{ $pqrsAdmin->total() }}
                </span>
            </div>
            {{-- Tab Instructores --}}
            <div class="d-flex align-items-center gap-2 rounded-pill border px-4 py-2 fw-bold"
                 style="background:#F1F5F9; color:#475569; font-size:.88rem; cursor:default;">
                <i class="fas fa-chalkboard-user"></i>
                <span>Instructores</span>
                <span class="badge rounded-pill fw-bold"
                      style="background:#DC2626; color:#fff; font-size:.75rem; padding:.2rem .55rem;">
                    {{ $pqrsInstructor->total() }}
                </span>
            </div>
        </div>
        <div class="text-muted small">
            Total: <strong class="text-dark">{{ $total }}</strong> PQRS en el sistema
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════
         SECCIÓN 1 — PQRS DE ADMINISTRACIÓN (el admin puede responder)
         ══════════════════════════════════════════════════════════════ --}}
    @if(!$soloInstructores)
    <div class="mb-5">
        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="d-flex align-items-center justify-content-center rounded-circle"
                  style="width:10px;height:10px;background:#39A900;flex-shrink:0;"></span>
            <h6 class="fw-bold mb-0" style="color:#001A29;">
                PQRS de Administración
                <span class="text-muted fw-normal ms-1" style="font-size:.8rem;">— Puedes ver, responder y cambiar estado</span>
            </h6>
        </div>
        <div class="card border-0 shadow-sm" style="border-radius:16px; overflow:hidden;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr class="tbl-head-row">
                                <th>Radicado / Fecha</th>
                                <th>Aprendiz</th>
                                <th>Tipo</th>
                                <th>Asunto</th>
                                <th class="text-center">Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pqrsAdmin as $pqr)
                            @php $ei = $pqr->estado_badge; $ti = $pqr->tipo_badge; @endphp
                            <tr class="tbl-body-row">
                                <td>
                                    <div class="fw-bold font-monospace" style="font-size:.84rem; color:#001A29;">
                                        {{ $pqr->radicado }}
                                    </div>
                                    <small class="text-muted" style="font-size:.7rem;">
                                        <i class="fas fa-calendar-day me-1"></i>
                                        {{ $pqr->fecha_radicacion?->format('d/m/Y H:i') }}
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="av-badge">
                                            {{ strtoupper(substr($pqr->aprendiz?->nickname ?? 'A', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark" style="font-size:.88rem;">
                                                {{ $pqr->aprendiz?->nickname ?? 'Aprendiz' }}
                                            </div>
                                            <small class="text-muted" style="font-size:.7rem;">
                                                {{ $pqr->aprendiz?->email ?? '' }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge rounded-pill px-2 py-1 border fw-semibold"
                                          style="background:{{ $ti['bg'] }};color:{{ $ti['text'] }};border-color:{{ $ti['border'] }} !important;font-size:.74rem;">
                                        <i class="{{ $ti['icon'] }} me-1"></i>{{ $pqr->tipo?->nombre ?? 'Petición' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark text-truncate" style="max-width:220px;font-size:.88rem;">
                                        {{ $pqr->asunto }}
                                    </div>
                                    <div class="text-muted text-truncate" style="max-width:220px;font-size:.74rem;">
                                        {{ $pqr->descripcion }}
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill px-3 py-1 border fw-semibold"
                                          style="background:{{ $ei['bg'] }};color:{{ $ei['text'] }};border-color:{{ $ei['border'] }} !important;font-size:.76rem;">
                                        <i class="{{ $ei['icon'] }} me-1"></i>{{ $pqr->estado }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    {{-- Acciones completas: ver, responder, cambiar estado, historial --}}
                                    <div class="d-inline-flex gap-1">
                                        <button type="button" class="btn-tbl-action" title="Ver detalle"
                                                onclick="abrirDetallePqrsModal({{ $pqr->id_pqrs }})">
                                            <i class="fas fa-eye text-primary"></i>
                                        </button>
                                        <button type="button" class="btn-tbl-action" title="Responder"
                                                onclick="abrirModalResponder({{ $pqr->id_pqrs }}, '{{ $pqr->radicado }}', '{{ addslashes($pqr->asunto) }}')">
                                            <i class="fas fa-reply text-success"></i>
                                        </button>
                                        <button type="button" class="btn-tbl-action" title="Cambiar estado"
                                                onclick="abrirModalEstado({{ $pqr->id_pqrs }}, '{{ $pqr->radicado }}', '{{ $pqr->estado }}')">
                                            <i class="fas fa-arrows-rotate text-warning"></i>
                                        </button>
                                        <button type="button" class="btn-tbl-action" title="Ver historial"
                                                onclick="abrirModalHistorial({{ $pqr->id_pqrs }}, '{{ $pqr->radicado }}')">
                                            <i class="fas fa-clock-rotate-left" style="color:#7C3AED;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block opacity-30"></i>
                                    <p class="fw-bold mb-1" style="color:#001A29;">No hay PQRS para administración</p>
                                    <small>Las solicitudes sin instructor asignado aparecerán aquí.</small>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($pqrsAdmin->hasPages())
                <div class="px-4 py-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="small text-muted">
                        Mostrando {{ $pqrsAdmin->firstItem() }}–{{ $pqrsAdmin->lastItem() }}
                        de {{ $pqrsAdmin->total() }}
                    </div>
                    {{ $pqrsAdmin->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════
         SECCIÓN 2 — PQRS DE INSTRUCTORES (solo lectura para el admin)
         ══════════════════════════════════════════════════════════════ --}}
    @if($soloInstructores)
    <div class="mb-4">
        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="d-flex align-items-center justify-content-center rounded-circle"
                  style="width:10px;height:10px;background:#4F46E5;flex-shrink:0;"></span>
            <h6 class="fw-bold mb-0" style="color:#001A29;">
                PQRS de Instructores
                <span class="text-muted fw-normal ms-1" style="font-size:.8rem;">— Solo lectura para el administrador</span>
            </h6>
        </div>

        <div class="section-readonly">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr class="tbl-head-row" style="background:#F1F5F9;">
                            <th>Radicado / Fecha</th>
                            <th>Aprendiz</th>
                            <th>Tipo</th>
                            <th>Asunto</th>
                            <th>Instructor asignado</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end">Ver</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pqrsInstructor as $pqr)
                        @php $ei = $pqr->estado_badge; $ti = $pqr->tipo_badge; @endphp
                        <tr class="tbl-body-row">
                            <td>
                                <div class="fw-semibold font-monospace" style="font-size:.84rem; color:#475569;">
                                    {{ $pqr->radicado }}
                                </div>
                                <small class="text-muted" style="font-size:.7rem;">
                                    {{ $pqr->fecha_radicacion?->format('d/m/Y H:i') }}
                                </small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="av-badge" style="background:#F1F5F9;color:#94A3B8;border-color:#CBD5E1;">
                                        {{ strtoupper(substr($pqr->aprendiz?->nickname ?? 'A', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold" style="font-size:.88rem;color:#475569;">
                                            {{ $pqr->aprendiz?->nickname ?? 'Aprendiz' }}
                                        </div>
                                        <small class="text-muted" style="font-size:.7rem;">
                                            {{ $pqr->aprendiz?->email ?? '' }}
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill px-2 py-1 border"
                                      style="background:{{ $ti['bg'] }};color:{{ $ti['text'] }};border-color:{{ $ti['border'] }} !important;font-size:.74rem;opacity:.85;">
                                    <i class="{{ $ti['icon'] }} me-1"></i>{{ $pqr->tipo?->nombre ?? 'Petición' }}
                                </span>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width:200px;font-size:.88rem;color:#475569;">
                                    {{ $pqr->asunto }}
                                </div>
                                <div class="text-muted text-truncate" style="max-width:200px;font-size:.74rem;">
                                    {{ $pqr->descripcion }}
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-chalkboard-user" style="color:#4F46E5;font-size:.85rem;"></i>
                                    <span class="fw-semibold small" style="color:#4F46E5;">
                                        {{ $pqr->instructorDestino?->nickname ?? '—' }}
                                    </span>
                                </div>
                                @if($pqr->respuestas->isNotEmpty())
                                <small class="text-success d-block mt-1" style="font-size:.7rem;">
                                    <i class="fas fa-check-circle me-1"></i>
                                    {{ $pqr->respuestas->count() }} respuesta(s) del instructor
                                </small>
                                @else
                                <small class="text-muted d-block mt-1" style="font-size:.7rem;">
                                    Sin respuesta aún
                                </small>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill px-2 py-1 border"
                                      style="background:{{ $ei['bg'] }};color:{{ $ei['text'] }};border-color:{{ $ei['border'] }} !important;font-size:.76rem;">
                                    <i class="{{ $ei['icon'] }} me-1"></i>{{ $pqr->estado }}
                                </span>
                            </td>
                            <td class="text-end">
                                {{-- Solo ver detalle e historial; sin responder ni cambiar estado --}}
                                <div class="d-inline-flex gap-1">
                                    <button type="button" class="btn-tbl-action"
                                            title="Ver detalle (solo lectura)"
                                            onclick="abrirDetallePqrsModal({{ $pqr->id_pqrs }}, true)">
                                        <i class="fas fa-eye" style="color:#4F46E5;"></i>
                                    </button>
                                    <button type="button" class="btn-tbl-action"
                                            title="Ver historial"
                                            onclick="abrirModalHistorial({{ $pqr->id_pqrs }}, '{{ $pqr->radicado }}')">
                                        <i class="fas fa-clock-rotate-left" style="color:#7C3AED;"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-chalkboard-user fa-3x mb-3 d-block opacity-30"></i>
                                <p class="fw-bold mb-1" style="color:#001A29;">No hay PQRS asignadas a instructores</p>
                                <small>Las solicitudes dirigidas a instructores aparecerán aquí.</small>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($pqrsInstructor->hasPages())
            <div class="px-4 py-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2"
                 style="background:#FAFBFD;">
                <div class="small text-muted">
                    Mostrando {{ $pqrsInstructor->firstItem() }}–{{ $pqrsInstructor->lastItem() }}
                    de {{ $pqrsInstructor->total() }}
                </div>
                {{ $pqrsInstructor->links() }}
            </div>
            @endif
        </div>
    </div>
    @endif
</div>

{{-- ════════════════════════════════════════════════════════
     MODAL 1 – DETALLE PQRS
     ════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalDetallePqrs" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header py-3 px-4 border-bottom" style="background:#F8FAFC;">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-dark font-monospace fs-6 px-3 py-1" id="detRadicado">…</span>
                    <span class="badge rounded-pill px-2 py-1" id="detTipoBadge">—</span>
                    <span class="badge rounded-pill px-2 py-1" id="detEstadoBadge">—</span>
                    {{-- Indicador solo lectura (visible solo para sección instructores) --}}
                    <span id="detReadonlyBadge" class="badge-readonly d-none">
                        <i class="fas fa-eye"></i> Solo lectura
                    </span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-12 col-lg-5">
                        <div class="card border-0 shadow-sm rounded-4 p-3 mb-3" style="background:#FAFBFD;">
                            <h6 class="fw-bold mb-3 pb-2 border-bottom text-dark">
                                <i class="fas fa-user me-1 text-success"></i>Solicitante
                            </h6>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="av-badge" style="width:46px;height:46px;font-size:1.1rem;" id="detAvatar">A</div>
                                <div>
                                    <div class="fw-bold text-dark" id="detAprendizNombre">—</div>
                                    <small class="text-muted d-block" id="detAprendizEmail">—</small>
                                </div>
                            </div>
                            <div class="small">
                                <div class="d-flex justify-content-between py-1 border-bottom">
                                    <span class="text-muted">Radicación:</span>
                                    <strong id="detFechaRad">—</strong>
                                </div>
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted">Asignado a:</span>
                                    <strong id="detInstructor">—</strong>
                                </div>
                            </div>
                        </div>
                        <div class="card border-0 shadow-sm rounded-4 p-3">
                            <h6 class="fw-bold text-dark mb-1" id="detAsunto">—</h6>
                            <div class="p-3 rounded-3 border small text-dark mt-2"
                                 style="background:#F8FAFC; white-space:pre-wrap; line-height:1.7; font-size:.88rem;"
                                 id="detDescripcion">Cargando…</div>
                            {{-- Botones de acción (ocultos en modo solo lectura) --}}
                            <div class="d-flex gap-2 mt-3 pt-3 border-top" id="detAccionesBar">
                                <button type="button" id="detBtnCambiarEstado"
                                        class="btn btn-sm btn-outline-warning rounded-pill px-3 flex-grow-1">
                                    <i class="fas fa-arrows-rotate me-1"></i>Estado
                                </button>
                                <button type="button" id="detBtnResponder"
                                        class="btn btn-sm text-white rounded-pill px-3 flex-grow-1 fw-semibold"
                                        style="background:#39A900;">
                                    <i class="fas fa-reply me-1"></i>Responder
                                </button>
                                <button type="button" id="detBtnHistorial"
                                        class="btn btn-sm btn-outline-secondary rounded-pill px-3 flex-grow-1"
                                        style="color:#7C3AED; border-color:#7C3AED;">
                                    <i class="fas fa-clock-rotate-left me-1"></i>Historial
                                </button>
                            </div>
                            {{-- Aviso solo lectura (visible en modo instructor) --}}
                            <div id="detReadonlyBar" class="d-none mt-3 pt-3 border-top">
                                <div class="alert mb-0 py-2 small border-0 d-flex align-items-center gap-2"
                                     style="background:#FFF7ED; border-radius:10px;">
                                    <i class="fas fa-lock" style="color:#D97706;"></i>
                                    <span class="text-dark">Esta PQRS es gestionada por el instructor asignado.
                                    El administrador solo puede supervisar.</span>
                                </div>
                                <button type="button" id="detBtnHistorialRO"
                                        class="btn btn-sm btn-outline-secondary rounded-pill px-3 w-100 mt-2"
                                        style="color:#7C3AED; border-color:#7C3AED;">
                                    <i class="fas fa-clock-rotate-left me-1"></i>Ver historial completo
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-7">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-header bg-white border-0 pt-3 px-4 pb-0">
                                <ul class="nav nav-tabs card-header-tabs">
                                    <li class="nav-item">
                                        <button class="nav-link active fw-semibold" data-bs-toggle="tab"
                                                data-bs-target="#pane-timeline-det">
                                            <i class="fas fa-clock-rotate-left me-1 text-primary"></i>Trazabilidad
                                        </button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link fw-semibold" data-bs-toggle="tab"
                                                data-bs-target="#pane-respuestas-det">
                                            <i class="fas fa-comment-dots me-1 text-success"></i>Respuestas
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body p-4 tab-content" style="min-height:280px;">
                                <div class="tab-pane fade show active" id="pane-timeline-det">
                                    <div id="detTimelineContainer">
                                        <div class="text-center py-5 text-muted">
                                            <i class="fas fa-spinner fa-spin me-2"></i>Cargando…
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="pane-respuestas-det">
                                    <div id="detRespuestasContainer">
                                        <div class="text-center py-5 text-muted">
                                            <i class="fas fa-spinner fa-spin me-2"></i>Cargando…
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════
     MODAL 2 – RESPONDER (solo para PQRS de administración)
     ════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalResponderPqrs" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <form id="formResponderPqrs" method="POST">
                @csrf
                <div class="modal-header py-3 px-4 border-bottom" style="background:#F8FAFC;">
                    <h5 class="modal-title fw-bold" style="color:#001A29;">
                        <i class="fas fa-reply me-2" style="color:#39A900;"></i>Escribir respuesta oficial
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 py-2 small mb-3 rounded-3" style="background:#EFF6FF;">
                        <i class="fas fa-circle-info me-1"></i>
                        Respondiendo a <strong id="respModalRadicado">—</strong>:
                        <span id="respModalAsunto" class="text-dark fw-semibold">—</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">
                            Respuesta formal <span class="text-danger">*</span>
                        </label>
                        <textarea name="respuesta" id="respTextarea" rows="6"
                                  class="form-control rounded-3"
                                  placeholder="Redacte la respuesta clara y formal que recibirá el aprendiz…"
                                  required minlength="10"></textarea>
                    </div>
                    <div>
                        <label class="form-label small fw-bold text-dark">Actualizar estado</label>
                        <select name="cambiar_estado" id="respCambiarEstado" class="form-select rounded-3">
                            <option value="Resuelto" selected>🟢 Marcar como Resuelto (caso finalizado)</option>
                            <option value="En trámite">🔵 Mantener En trámite</option>
                            <option value="Mantener">🔘 Mantener estado actual</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                            data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white rounded-pill px-4 shadow-sm fw-semibold"
                            style="background:#39A900;">
                        <i class="fas fa-paper-plane me-1"></i>Enviar respuesta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════
     MODAL 3 – CAMBIAR ESTADO
     ════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalCambiarEstadoPqrs" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <form id="formCambiarEstadoPqrs" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header py-3 px-4 border-bottom" style="background:#F8FAFC;">
                    <h5 class="modal-title fw-bold" style="color:#001A29;">
                        <i class="fas fa-arrows-rotate me-2 text-warning"></i>Cambiar estado
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <div class="text-muted small">Solicitud:</div>
                        <div class="fw-bold font-monospace fs-6 text-dark" id="estadoModalRadicado">—</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Nuevo estado <span class="text-danger">*</span></label>
                        <select name="estado" id="nuevoEstadoSelect" class="form-select rounded-3" required>
                            <option value="Pendiente">🟡 Pendiente</option>
                            <option value="En trámite">🔵 En trámite</option>
                            <option value="Resuelto">🟢 Resuelto</option>
                            <option value="Cerrado">🔒 Cerrado</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label small fw-bold text-dark">Observación</label>
                        <textarea name="observacion" id="obsEstadoText" rows="3"
                                  class="form-control rounded-3"
                                  placeholder="Motivo del cambio…"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                            data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white rounded-pill px-4 shadow-sm fw-semibold"
                            style="background:#39A900;">Guardar estado</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════
     MODAL 4 – HISTORIAL PERSONAL
     ════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalHistorialPqrs" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header py-3 px-4 border-bottom"
                 style="background:linear-gradient(135deg,#F5F3FF,#EDE9FE);">
                <div>
                    <h5 class="modal-title fw-bold mb-0" style="color:#4C1D95;">
                        <i class="fas fa-clock-rotate-left me-2" style="color:#7C3AED;"></i>
                        Historial completo
                    </h5>
                    <div class="small text-muted mt-1">
                        Radicado: <span class="fw-bold font-monospace" id="historialModalRadicado">—</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="historialTimelineContainer">
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-spinner fa-spin fa-2x mb-3 d-block" style="color:#7C3AED;"></i>
                        Cargando historial…
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top py-3">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                        data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════
     MODAL 5 – REPORTE ESTADÍSTICO
     ════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalReportePqrs" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header py-3 px-4 border-bottom" style="background:#F8FAFC;">
                <div>
                    <h5 class="modal-title fw-bold mb-0" style="color:#001A29;">
                        <i class="fas fa-chart-pie me-2 text-success"></i>Reporte estadístico de PQRS
                    </h5>
                    <small class="text-muted">Escuela Cultura de Paz</small>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <a href="{{ route('controlecp.administrador.pqr.reporte') }}"
                       class="btn btn-outline-secondary rounded-pill btn-sm px-3" target="_blank">
                        <i class="fas fa-print me-1"></i>Imprimir
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-12 col-lg-4">
                        <div class="border rounded-4 p-3 bg-white shadow-sm">
                            <h6 class="fw-bold text-dark mb-2">Por tipo</h6>
                            <div style="height:220px;"><canvas id="chartPqrsTipo"></canvas></div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="border rounded-4 p-3 bg-white shadow-sm">
                            <h6 class="fw-bold text-dark mb-2">Por estado</h6>
                            <div style="height:220px;"><canvas id="chartPqrsEstado"></canvas></div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="border rounded-4 p-3 bg-white shadow-sm">
                            <h6 class="fw-bold text-dark mb-2">Por responsable</h6>
                            <div style="height:220px;"><canvas id="chartPqrsInstructor"></canvas></div>
                        </div>
                    </div>
                </div>
                <div class="row g-2 mt-4 pt-3 border-top text-center">
                    <div class="col-3">
                        <div class="p-2 border rounded-3 bg-light">
                            <small class="text-muted d-block">Total</small>
                            <strong class="fs-5">{{ $total }}</strong>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 border rounded-3" style="background:#FEF3C7;">
                            <small class="text-warning-emphasis d-block">Pendientes</small>
                            <strong class="fs-5 text-warning-emphasis">{{ $pendientes }}</strong>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 border rounded-3" style="background:#EEF2FF;">
                            <small style="color:#4F46E5;" class="d-block">En trámite</small>
                            <strong class="fs-5" style="color:#4F46E5;">{{ $enTramite }}</strong>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 border rounded-3" style="background:#DEF7EC;">
                            <small class="text-success d-block">Resueltos</small>
                            <strong class="fs-5 text-success">{{ $resueltas }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// ── 1. MODAL DETALLE ──────────────────────────────────────────────────────
function abrirDetallePqrsModal(id, soloLectura = false) {
    const modal = new bootstrap.Modal(document.getElementById('modalDetallePqrs'));
    modal.show();

    // Mostrar/ocultar controles según modo
    const accBar  = document.getElementById('detAccionesBar');
    const roBar   = document.getElementById('detReadonlyBar');
    const roBadge = document.getElementById('detReadonlyBadge');
    if (soloLectura) {
        accBar.classList.add('d-none');
        roBar.classList.remove('d-none');
        roBadge.classList.remove('d-none');
    } else {
        accBar.classList.remove('d-none');
        roBar.classList.add('d-none');
        roBadge.classList.add('d-none');
    }

    // Reset
    document.getElementById('detRadicado').textContent    = 'Cargando…';
    document.getElementById('detAsunto').textContent      = '—';
    document.getElementById('detDescripcion').textContent = 'Cargando…';
    document.getElementById('detTimelineContainer').innerHTML =
        '<div class="text-center py-5 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Cargando…</div>';
    document.getElementById('detRespuestasContainer').innerHTML =
        '<div class="text-center py-5 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Cargando…</div>';

    fetch(`/control-ecp/administrador/pqr/${id}/detalle-json`)
        .then(r => r.json())
        .then(d => {
            document.getElementById('detRadicado').textContent      = d.radicado;
            document.getElementById('detAsunto').textContent         = d.asunto;
            document.getElementById('detDescripcion').textContent    = d.descripcion;
            document.getElementById('detAprendizNombre').textContent = d.aprendiz.nickname;
            document.getElementById('detAprendizEmail').textContent  = d.aprendiz.email;
            document.getElementById('detAvatar').textContent         = d.aprendiz.nickname.substring(0,1).toUpperCase();
            document.getElementById('detFechaRad').textContent       = d.fecha_radicacion;
            document.getElementById('detInstructor').textContent     = d.instructor.nickname;

            const tb = document.getElementById('detTipoBadge');
            tb.style.backgroundColor = d.tipo_badge.bg;
            tb.style.color           = d.tipo_badge.text;
            tb.textContent           = d.tipo_nombre;

            const eb = document.getElementById('detEstadoBadge');
            eb.style.backgroundColor = d.estado_badge.bg;
            eb.style.color           = d.estado_badge.text;
            eb.textContent           = d.estado;

            if (!soloLectura) {
                document.getElementById('detBtnResponder').onclick = () => {
                    bootstrap.Modal.getInstance(document.getElementById('modalDetallePqrs')).hide();
                    abrirModalResponder(d.id_pqrs, d.radicado, d.asunto);
                };
                document.getElementById('detBtnCambiarEstado').onclick = () => {
                    bootstrap.Modal.getInstance(document.getElementById('modalDetallePqrs')).hide();
                    abrirModalEstado(d.id_pqrs, d.radicado, d.estado);
                };
                document.getElementById('detBtnHistorial').onclick = () => {
                    bootstrap.Modal.getInstance(document.getElementById('modalDetallePqrs')).hide();
                    abrirModalHistorial(d.id_pqrs, d.radicado);
                };
            } else {
                document.getElementById('detBtnHistorialRO').onclick = () => {
                    bootstrap.Modal.getInstance(document.getElementById('modalDetallePqrs')).hide();
                    abrirModalHistorial(d.id_pqrs, d.radicado);
                };
            }

            // Timeline
            const tc = document.getElementById('detTimelineContainer');
            tc.innerHTML = '';
            if (!d.historial || d.historial.length === 0) {
                tc.innerHTML = '<div class="text-center py-3 text-muted small">Sin eventos adicionales.</div>';
            } else {
                d.historial.forEach(h => {
                    tc.innerHTML += `
                    <div class="timeline-item">
                        <span class="timeline-dot" style="background:#39A900;"></span>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-bold text-dark small">${h.estado_nuevo ? 'Estado: '+h.estado_nuevo : 'Actuación'}</span>
                            <small class="text-muted" style="font-size:.72rem;">${h.fecha}</small>
                        </div>
                        <div class="text-muted small">${h.observacion || 'Sin observaciones'}</div>
                        <div class="text-secondary mt-1" style="font-size:.71rem;">Por: <strong>${h.autor}</strong></div>
                    </div>`;
                });
            }

            // Respuestas
            const rc = document.getElementById('detRespuestasContainer');
            rc.innerHTML = '';
            if (!d.respuestas || d.respuestas.length === 0) {
                const msgBtn = soloLectura
                    ? '<div class="text-muted small mt-2">El instructor aún no ha respondido esta solicitud.</div>'
                    : `<button type="button" class="btn btn-sm text-white rounded-pill px-3 mt-2 d-block mx-auto"
                               style="background:#39A900;"
                               onclick="document.getElementById('detBtnResponder').click()">
                           <i class="fas fa-reply me-1"></i>Escribir primera respuesta
                       </button>`;
                rc.innerHTML = `
                <div class="text-center py-5 text-muted small">
                    <i class="fas fa-comment-slash fa-2x mb-2 d-block opacity-40"></i>
                    Esta solicitud aún no tiene respuestas.
                    ${msgBtn}
                </div>`;
            } else {
                d.respuestas.forEach(r => {
                    rc.innerHTML += `
                    <div class="p-3 mb-3 rounded-3 border-start border-4 border-success"
                         style="background:#F0FDF4;">
                        <div class="d-flex justify-content-between mb-1">
                            <strong class="text-dark small">
                                <i class="fas fa-user-shield text-success me-1"></i>${r.autor}
                            </strong>
                            <small class="text-muted" style="font-size:.72rem;">${r.fecha}</small>
                        </div>
                        <div class="text-dark small mt-2" style="white-space:pre-wrap;">${r.respuesta}</div>
                    </div>`;
                });
            }
        })
        .catch(() => alert('Error al cargar el detalle de la PQRS.'));
}

// ── 2. MODAL RESPONDER ─────────────────────────────────────────────────────
function abrirModalResponder(id, radicado, asunto) {
    document.getElementById('respModalRadicado').textContent = radicado;
    document.getElementById('respModalAsunto').textContent   = asunto;
    document.getElementById('formResponderPqrs').action      = `/control-ecp/administrador/pqr/${id}/responder`;
    document.getElementById('respTextarea').value            = '';
    document.getElementById('respCambiarEstado').value       = 'Resuelto';
    new bootstrap.Modal(document.getElementById('modalResponderPqrs')).show();
}

// ── 3. MODAL CAMBIAR ESTADO ────────────────────────────────────────────────
function abrirModalEstado(id, radicado, estadoActual) {
    document.getElementById('estadoModalRadicado').textContent = radicado;
    document.getElementById('nuevoEstadoSelect').value          = estadoActual;
    document.getElementById('obsEstadoText').value              = '';
    document.getElementById('formCambiarEstadoPqrs').action     = `/control-ecp/administrador/pqr/${id}/estado`;
    new bootstrap.Modal(document.getElementById('modalCambiarEstadoPqrs')).show();
}

// ── 4. MODAL HISTORIAL ─────────────────────────────────────────────────────
function abrirModalHistorial(id, radicado) {
    document.getElementById('historialModalRadicado').textContent = radicado;
    document.getElementById('historialTimelineContainer').innerHTML =
        '<div class="text-center py-5 text-muted"><i class="fas fa-spinner fa-spin fa-2x mb-3 d-block" style="color:#7C3AED;"></i>Cargando historial…</div>';

    new bootstrap.Modal(document.getElementById('modalHistorialPqrs')).show();

    fetch(`/control-ecp/administrador/pqr/${id}/historial-json`)
        .then(r => r.json())
        .then(d => {
            const cont = document.getElementById('historialTimelineContainer');
            cont.innerHTML = '';

            if (!d.eventos || d.eventos.length === 0) {
                cont.innerHTML = '<div class="text-center py-4 text-muted small">No hay eventos registrados.</div>';
                return;
            }

            cont.innerHTML += `
            <div class="d-flex flex-wrap gap-2 mb-4 p-3 rounded-3 border" style="background:#F5F3FF;">
                <span class="badge bg-secondary font-monospace px-2 py-1">${d.radicado}</span>
                <span class="fw-semibold text-dark small">${d.asunto}</span>
                <span class="badge rounded-pill ms-auto px-3"
                      style="background:${d.estado_badge.bg}; color:${d.estado_badge.text};">
                    ${d.estado}
                </span>
            </div>
            <div id="timelineEventos"></div>`;

            const evCont = document.getElementById('timelineEventos');
            const colorMap = {
                radicacion: '#0288D1',
                estado:     '#D97706',
                respuesta:  '#16A34A',
            };
            d.eventos.forEach(ev => {
                const color = colorMap[ev.tipo] || '#64748B';
                evCont.innerHTML += `
                <div class="timeline-item">
                    <span class="timeline-dot" style="background:${color};box-shadow:0 0 0 2px ${color}33;"></span>
                    <div class="d-flex flex-wrap justify-content-between gap-1 mb-1">
                        <span class="fw-bold text-dark small">${ev.titulo}</span>
                        <small class="text-muted" style="font-size:.71rem;white-space:nowrap;">${ev.fecha}</small>
                    </div>
                    <div class="p-2 rounded-3 border small text-dark mb-1"
                         style="background:#FAFBFD;white-space:pre-wrap;font-size:.85rem;">${ev.descripcion}</div>
                    <div class="text-secondary" style="font-size:.71rem;">
                        <i class="fas fa-user me-1"></i>Por: <strong>${ev.autor}</strong>
                    </div>
                </div>`;
            });
        })
        .catch(() => {
            document.getElementById('historialTimelineContainer').innerHTML =
                '<div class="text-center py-4 text-danger small"><i class="fas fa-circle-xmark me-1"></i>Error al cargar el historial.</div>';
        });
}

// ── 5. GRÁFICAS REPORTE ────────────────────────────────────────────────────
let graficasPqrsInicializadas = false;
function inicializarGraficasPqrs() {
    if (graficasPqrsInicializadas) return;
    setTimeout(() => {
        const ctxT = document.getElementById('chartPqrsTipo');
        if (ctxT) new Chart(ctxT, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($reporteTiposLabels) !!},
                datasets: [{ data: {!! json_encode($reporteTiposCounts) !!},
                    backgroundColor: ['#0284C7','#EF4444','#F97316','#8B5CF6','#10B981'],
                    borderWidth: 2, borderColor: '#fff' }]
            },
            options: { responsive:true, maintainAspectRatio:false,
                plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10, font:{ size:9 } } } } }
        });

        const ctxE = document.getElementById('chartPqrsEstado');
        if (ctxE) new Chart(ctxE, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($reporteEstadosLabels) !!},
                datasets: [{ data: {!! json_encode($reporteEstadosCounts) !!},
                    backgroundColor: ['#F59E0B','#4F46E5','#10B981'],
                    borderWidth: 2, borderColor: '#fff' }]
            },
            options: { responsive:true, maintainAspectRatio:false, cutout:'65%',
                plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10, font:{ size:9 } } } } }
        });

        const ctxI = document.getElementById('chartPqrsInstructor');
        if (ctxI) new Chart(ctxI, {
            type: 'bar',
            data: {
                labels: {!! json_encode($reporteInstructoresLabels) !!},
                datasets: [{ label:'PQRS Asignadas',
                    data: {!! json_encode($reporteInstructoresCounts) !!},
                    backgroundColor: '#39A900', borderRadius: 6 }]
            },
            options: { indexAxis:'y', responsive:true, maintainAspectRatio:false,
                plugins:{ legend:{ display:false } }, scales:{ x:{ beginAtZero:true, ticks:{ precision:0 } } } }
        });

        graficasPqrsInicializadas = true;
    }, 250);
}
</script>
@endpush
@endsection
