@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">

    {{-- Breadcrumb --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.administrador.inicio') }}" class="text-decoration-none" style="color:#39A900;">
                            <i class="fas fa-house me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item active" style="color:#001A29;font-weight:600;">Asistencia</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color:#001A29;">
                <i class="fas fa-user-check me-2" style="color:#39A900;"></i>Control de Asistencia
            </h2>
            <p class="text-muted small mb-0">Control ECP · Consolidado de los 13 casos de uso · Integrado con API REST Central SICEFA</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-outline-info rounded-pill btn-sm" onclick="abrirConsultaAprendiz()">
                <i class="fas fa-user-clock me-1"></i> Consultar Aprendiz
            </button>
            <button class="btn btn-outline-warning rounded-pill btn-sm" onclick="generarReporte()">
                <i class="fas fa-file-pdf me-1"></i> Generar Reporte
            </button>
            <a href="{{ route('controlecp.administrador.inicio') }}" class="btn btn-outline-secondary rounded-pill btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    {{-- ─── HU-012: Indicadores ─── --}}
    <div class="row g-3 mb-4">
        @php
            $ind = $indicadores;
            $cards = [
                ['val'=>$ind['total'],       'label'=>'Registros totales',        'color'=>'#39A900','icon'=>'fa-clipboard-list'],
                ['val'=>$ind['hoy'],          'label'=>'Registros hoy',           'color'=>'#0070B8','icon'=>'fa-calendar-day'],
                ['val'=>$ind['asistio'],      'label'=>'Asistieron',              'color'=>'#27AE60','icon'=>'fa-user-check'],
                ['val'=>$ind['falto'],        'label'=>'Faltaron',                'color'=>'#E74C3C','icon'=>'fa-user-times'],
                ['val'=>$ind['porcentaje_asistencia'].'%', 'label'=>'% Asistencia','color'=>'#8E44AD','icon'=>'fa-percent'],
                ['val'=>$ind['actividades_finalizadas'].'/18','label'=>'Actividades con registro','color'=>'#D68910','icon'=>'fa-tasks'],
                ['val'=>$ind['actividades_pendientes'],'label'=>'Actividades pendientes','color'=>'#6c757d','icon'=>'fa-hourglass-half'],
                ['val'=>$ind['fichas_con_registro'],'label'=>'Fichas con registro','color'=>'#001A29','icon'=>'fa-graduation-cap'],
            ];
        @endphp
        @foreach ($cards as $c)
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 shadow-sm" style="border-radius:16px;">
                <div class="card-body text-center p-3">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-2"
                         style="width:44px;height:44px;background:{{ $c['color'] }}18;color:{{ $c['color'] }};font-size:1.2rem;">
                        <i class="fas {{ $c['icon'] }}"></i>
                    </div>
                    <h4 class="fw-bold mb-0" style="color:{{ $c['color'] }};">{{ $c['val'] }}</h4>
                    <p class="small text-muted mb-0" style="font-size:.78rem;">{{ $c['label'] }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ─── HU-011: Actividades sin asistencia ─── --}}
    @if (count($actividadesSinAsistencia) > 0)
    <div class="alert border-0 shadow-sm mb-4 d-flex align-items-start gap-3" style="border-radius:14px;background:#fff8e1;border-left:5px solid #D68910!important;">
        <i class="fas fa-exclamation-triangle fa-lg mt-1" style="color:#D68910;"></i>
        <div>
            <strong style="color:#001A29;">{{ count($actividadesSinAsistencia) }} actividad(es) sin asistencia registrada:</strong>
            <div class="mt-1 d-flex flex-wrap gap-1">
                @foreach (array_slice($actividadesSinAsistencia, 0, 8) as $a)
                    <span class="badge rounded-pill" style="background:{{ $a['momento_color'] }}22;color:{{ $a['momento_color'] }};border:1px solid {{ $a['momento_color'] }}44;font-size:.75rem;">
                        {{ $a['numero'] }}. {{ $a['nombre'] }}
                    </span>
                @endforeach
                @if (count($actividadesSinAsistencia) > 8)
                    <span class="text-muted small">+{{ count($actividadesSinAsistencia) - 8 }} más</span>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- ─── HU-007: Filtros ─── --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
        <div class="card-header bg-white border-0 pt-3 pb-2 px-4">
            <h6 class="fw-bold mb-0" style="color:#001A29;"><i class="fas fa-filter me-2 text-success"></i>Filtrar Asistencias</h6>
        </div>
        <div class="card-body px-4 pb-3">
            <form method="GET" action="{{ route('controlecp.administrador.asistencia') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <input type="text" name="ficha" value="{{ $filtros['ficha'] ?? '' }}"
                               class="form-control form-control-sm rounded-pill" placeholder="Ficha (código)">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="documento" value="{{ $filtros['documento'] ?? '' }}"
                               class="form-control form-control-sm rounded-pill" placeholder="Doc. aprendiz">
                    </div>
                    <div class="col-md-2">
                        <select name="actividad_id" class="form-select form-select-sm rounded-pill">
                            <option value="">Actividad</option>
                            @for ($i = 1; $i <= 18; $i++)
                                <option value="{{ $i }}" {{ ($filtros['actividad_id'] ?? '') == $i ? 'selected' : '' }}>Act. {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="instructor_id" class="form-select form-select-sm rounded-pill">
                            <option value="">Instructor</option>
                            @foreach ($instructores as $inst)
                                <option value="{{ $inst->id }}" {{ ($filtros['instructor_id'] ?? '') == $inst->id ? 'selected' : '' }}>
                                    {{ $inst->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="fecha" value="{{ $filtros['fecha'] ?? '' }}"
                               class="form-control form-control-sm rounded-pill">
                    </div>
                    <div class="col-md-1">
                        <select name="estado" class="form-select form-select-sm rounded-pill">
                            <option value="">Estado</option>
                            @foreach (['ASISTIO'=>'Asistió','FALTO'=>'Faltó','EXCUSA'=>'Excusa','RETARDO'=>'Retardo','ANULADA'=>'Anulada'] as $v => $l)
                                <option value="{{ $v }}" {{ ($filtros['estado'] ?? '') == $v ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1 d-grid">
                        <button type="submit" class="btn btn-sm rounded-pill text-white" style="background:#39A900;">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ─── HU-006: Listado ─── --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
        <div class="card-header bg-white border-0 pt-3 pb-2 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0" style="color:#001A29;">
                <i class="fas fa-list me-2 text-success"></i>
                Registros de Asistencia
                <span class="badge rounded-pill ms-2" style="background:#39A900;">{{ $registros->total() }}</span>
            </h5>
        </div>
        <div class="card-body px-4 pb-4">
            @if ($registros->count())
            <div class="table-responsive">
                <table class="table table-hover align-middle small">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Ficha</th>
                            <th>Actividad</th>
                            <th>Aprendiz</th>
                            <th>Documento</th>
                            <th>Instructor</th>
                            <th class="text-center">Estado</th>
                            <th>Evidencia</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($registros as $reg)
                        <tr>
                            <td>{{ $reg->fecha?->format('d/m/Y') }}</td>
                            <td><span class="fw-bold font-monospace">{{ $reg->course?->code ?? '--' }}</span></td>
                            <td>
                                <span class="badge rounded-pill"
                                      style="background:{{ $reg->momento_color }}22;color:{{ $reg->momento_color }};border:1px solid {{ $reg->momento_color }}44;font-size:.7rem;">
                                    {{ $reg->momento_nombre }}
                                </span><br>
                                <span class="small">{{ Str::limit($reg->actividad_nombre, 30) }}</span>
                            </td>
                            <td class="fw-semibold">{{ $reg->full_name }}</td>
                            <td class="font-monospace small">{{ $reg->document_number }}</td>
                            <td class="small">{{ $reg->instructor?->full_name ?? '--' }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $reg->estado_badge }}">
                                    <i class="fas {{ $reg->estado_icon }} me-1"></i>{{ $reg->estado_label }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if ($reg->tiene_evidencia)
                                    <a href="{{ $reg->evidencia_url }}" target="_blank" class="text-success" title="Ver evidencia">
                                        <i class="fas fa-camera-retro"></i>
                                    </a>
                                @else
                                    <span class="text-muted" title="Sin evidencia"><i class="fas fa-camera-slash"></i></span>
                                @endif
                            </td>
                            <td>
                                {{-- HU-008 Detalle --}}
                                <button class="btn btn-xs btn-outline-info rounded-pill me-1"
                                        onclick="verDetalle({{ $reg->course_id }}, {{ $reg->actividad_id }}, '{{ $reg->fecha?->format('Y-m-d') }}')"
                                        title="Ver detalle actividad">
                                    <i class="fas fa-eye"></i>
                                </button>
                                {{-- HU-010 Historial --}}
                                <button class="btn btn-xs btn-outline-secondary rounded-pill"
                                        onclick="verHistorial({{ $reg->id }})"
                                        title="Historial de cambios">
                                    <i class="fas fa-history"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-3">
                {{ $registros->links() }}
            </div>
            @else
            <div class="text-center py-5 text-muted">
                <i class="fas fa-inbox fa-3x mb-3 d-block" style="color:#39A900;opacity:.3;"></i>
                @if (array_filter($filtros ?? []))
                    <p>No hay registros que coincidan con los filtros.</p>
                    <a href="{{ route('controlecp.administrador.asistencia') }}" class="btn btn-outline-secondary rounded-pill btn-sm">Limpiar filtros</a>
                @else
                    <p>No hay asistencias registradas aún.<br>
                    <small>Los instructores deben registrarlas desde su panel.</small></p>
                @endif
            </div>
            @endif
        </div>
    </div>

</div>

{{-- ─── MODAL: Detalle actividad (HU-008) ─── --}}
<div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" style="color:#001A29;">
                    <i class="fas fa-clipboard-list me-2" style="color:#39A900;"></i>Detalle de Actividad
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleContent">
                <div class="text-center py-3 text-muted"><i class="fas fa-spinner fa-spin"></i></div>
            </div>
        </div>
    </div>
</div>

{{-- ─── MODAL: Historial de cambios (HU-010) ─── --}}
<div class="modal fade" id="modalHistorial" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" style="color:#001A29;">
                    <i class="fas fa-history me-2" style="color:#39A900;"></i>Historial de Cambios
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="historialContent">
                <div class="text-center py-3 text-muted"><i class="fas fa-spinner fa-spin"></i></div>
            </div>
        </div>
    </div>
</div>

{{-- ─── MODAL: Consultar aprendiz (HU-009) ─── --}}
<div class="modal fade" id="modalAprendiz" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" style="color:#001A29;">
                    <i class="fas fa-user-clock me-2" style="color:#39A900;"></i>Historial por Aprendiz
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex gap-2 mb-3">
                    <input type="text" id="inputDocAprendiz" class="form-control rounded-pill"
                           placeholder="Número de documento del aprendiz">
                    <button class="btn rounded-pill text-white px-4" style="background:#39A900;"
                            onclick="buscarAprendiz()">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
                <div id="aprendizContent"></div>
            </div>
        </div>
    </div>
</div>

{{-- ─── MODAL: Reporte (HU-013) ─── --}}
<div class="modal fade" id="modalReporte" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" style="color:#001A29;">
                    <i class="fas fa-file-pdf me-2" style="color:#E74C3C;"></i>Generar Reporte de Asistencia
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <input type="text" id="rpFicha" class="form-control form-control-sm rounded-pill" placeholder="Ficha (ID)">
                    </div>
                    <div class="col-md-3">
                        <input type="number" id="rpActividad" class="form-control form-control-sm rounded-pill" placeholder="Actividad ID (1-18)">
                    </div>
                    <div class="col-md-3">
                        <input type="date" id="rpFecha" class="form-control form-control-sm rounded-pill">
                    </div>
                    <div class="col-md-3 d-grid">
                        <button class="btn btn-sm rounded-pill text-white" style="background:#E74C3C;" onclick="ejecutarReporte()">
                            <i class="fas fa-play me-1"></i> Generar
                        </button>
                    </div>
                </div>
                <div id="reporteContent"></div>
            </div>
        </div>
    </div>
</div>

{{-- Toast --}}
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999;">
    <div id="toastFeedback" class="toast align-items-center text-white border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body fw-semibold" id="toastMsg">OK</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
const ROUTES = {
    detalle  : '{{ url("control-ecp/asistencia/detalle") }}',
    historial: '{{ url("control-ecp/asistencia") }}',
    aprendiz : '{{ route("controlecp.asistencia.historial_aprendiz") }}',
    reporte  : '{{ route("controlecp.asistencia.reporte") }}',
};

// ─── HU-008: Ver detalle actividad ────────────────────────────────────────────
function verDetalle(fichaId, actividadId, fecha) {
    document.getElementById('detalleContent').innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i></div>';
    new bootstrap.Modal('#modalDetalle').show();

    fetch(`${ROUTES.detalle}/${fichaId}/${actividadId}/${fecha}`, {headers:{Accept:'application/json'}})
    .then(r => r.json())
    .then(data => {
        if (!data.success) { document.getElementById('detalleContent').innerHTML = '<p class="text-danger">'+data.message+'</p>'; return; }
        const r = data.resumen;
        const inst = data.instructor ? `<strong>Instructor:</strong> ${data.instructor.nombre}` : '';
        const rows = data.registros.map((a,i) => `<tr>
            <td>${i+1}</td>
            <td>${a.document_number}</td>
            <td class="fw-semibold">${a.full_name}</td>
            <td class="text-center"><span class="badge bg-${a.estado_badge}">${a.estado_label}</span></td>
            <td>${a.observacion||'--'}</td>
            <td class="text-center">${a.evidencia_url ? '<a href="'+a.evidencia_url+'" target="_blank"><i class="fas fa-camera-retro text-success"></i></a>' : '<i class="fas fa-camera-slash text-muted"></i>'}</td>
        </tr>`).join('');

        document.getElementById('detalleContent').innerHTML = `
            <div class="row g-2 mb-3">
                <div class="col-sm-2"><div class="p-2 rounded text-center" style="background:#f0fbf4;"><div class="fw-bold" style="color:#39A900;">${r.total}</div><div class="small text-muted">Total</div></div></div>
                <div class="col-sm-2"><div class="p-2 rounded text-center" style="background:#EAFAF1;"><div class="fw-bold text-success">${r.asistio}</div><div class="small text-muted">Asistió</div></div></div>
                <div class="col-sm-2"><div class="p-2 rounded text-center" style="background:#FDEDEC;"><div class="fw-bold text-danger">${r.falto}</div><div class="small text-muted">Faltó</div></div></div>
                <div class="col-sm-2"><div class="p-2 rounded text-center" style="background:#EBF5FB;"><div class="fw-bold text-info">${r.excusa}</div><div class="small text-muted">Excusa</div></div></div>
                <div class="col-sm-2"><div class="p-2 rounded text-center" style="background:#FEF9E7;"><div class="fw-bold text-warning">${r.retardo}</div><div class="small text-muted">Retardo</div></div></div>
            </div>
            <p class="small text-muted">${inst}</p>
            <div class="table-responsive"><table class="table table-sm table-hover">
                <thead class="table-light"><tr><th>#</th><th>Doc.</th><th>Nombre</th><th class="text-center">Estado</th><th>Obs.</th><th>Evidencia</th></tr></thead>
                <tbody>${rows}</tbody>
            </table></div>`;
    })
    .catch(() => { document.getElementById('detalleContent').innerHTML = '<p class="text-danger">Error.</p>'; });
}

// ─── HU-010: Historial de cambios ─────────────────────────────────────────────
function verHistorial(id) {
    document.getElementById('historialContent').innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i></div>';
    new bootstrap.Modal('#modalHistorial').show();

    fetch(`${ROUTES.historial}/${id}/historial`, {headers:{Accept:'application/json'}})
    .then(r => r.json())
    .then(data => {
        if (!data.success) { document.getElementById('historialContent').innerHTML = '<p class="text-danger">'+data.message+'</p>'; return; }
        const h = data.historial || [];
        const rows = h.length === 0 ? '<tr><td colspan="3" class="text-center text-muted">Sin cambios.</td></tr>'
            : h.map(c => `<tr><td>${new Date(c.timestamp).toLocaleString('es-CO')}</td>
                <td><span class="badge bg-secondary">${c.accion}</span></td>
                <td><pre class="mb-0 small">${JSON.stringify(c.datos,null,2)}</pre></td></tr>`).join('');
        document.getElementById('historialContent').innerHTML = `
            <p class="small"><strong>${data.registro.full_name}</strong> (${data.registro.document_number}) —
            ${data.registro.actividad} — ${data.registro.fecha}</p>
            <div class="table-responsive"><table class="table table-sm table-bordered small">
                <thead class="table-light"><tr><th>Fecha/Hora</th><th>Acción</th><th>Detalle</th></tr></thead>
                <tbody>${rows}</tbody>
            </table></div>`;
    })
    .catch(() => { document.getElementById('historialContent').innerHTML = '<p class="text-danger">Error.</p>'; });
}

// ─── HU-009: Consultar aprendiz ───────────────────────────────────────────────
function abrirConsultaAprendiz() {
    document.getElementById('aprendizContent').innerHTML = '';
    document.getElementById('inputDocAprendiz').value = '';
    new bootstrap.Modal('#modalAprendiz').show();
}
function buscarAprendiz() {
    const doc = document.getElementById('inputDocAprendiz').value.trim();
    if (!doc) { toast('Ingresa el número de documento.','warning'); return; }
    document.getElementById('aprendizContent').innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i></div>';

    fetch(`${ROUTES.aprendiz}?documento=${doc}`, {headers:{Accept:'application/json'}})
    .then(r => r.json())
    .then(data => {
        if (!data.success) { document.getElementById('aprendizContent').innerHTML = `<div class="alert alert-warning rounded-3">${data.message}</div>`; return; }
        const p = data.porcentaje_asistencia;
        const color = p >= 80 ? '#27AE60' : (p >= 60 ? '#D68910' : '#E74C3C');
        const rows = data.registros.map(r => `<tr>
            <td>${r.fecha}</td><td>${r.ficha||'--'}</td><td>${r.actividad}</td>
            <td>${r.momento}</td><td><span class="badge bg-${r.estado_badge}">${r.estado_label}</span></td>
            <td>${r.observacion||'--'}</td>
        </tr>`).join('');

        document.getElementById('aprendizContent').innerHTML = `
            <div class="d-flex align-items-center gap-3 p-3 rounded-3 mb-3" style="background:#f0fbf4;">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:60px;height:60px;background:${color};color:#fff;font-size:1.1rem;font-weight:700;">${p}%</div>
                <div>
                    <div class="fw-bold">${data.full_name}</div>
                    <div class="small text-muted">Doc: ${data.documento}</div>
                    <div class="small">Actividades: ${data.total} &nbsp;|&nbsp; Asistió: ${data.asistio} &nbsp;|&nbsp; Faltó: ${data.falto} &nbsp;|&nbsp; Excusa: ${data.excusa} &nbsp;|&nbsp; Retardo: ${data.retardo}</div>
                </div>
            </div>
            <div class="table-responsive"><table class="table table-sm table-hover">
                <thead class="table-light"><tr><th>Fecha</th><th>Ficha</th><th>Actividad</th><th>Momento</th><th class="text-center">Estado</th><th>Obs.</th></tr></thead>
                <tbody>${rows}</tbody>
            </table></div>`;
    })
    .catch(() => { document.getElementById('aprendizContent').innerHTML = '<p class="text-danger">Error.</p>'; });
}

// ─── HU-013: Reporte ──────────────────────────────────────────────────────────
function generarReporte() {
    document.getElementById('reporteContent').innerHTML = '';
    new bootstrap.Modal('#modalReporte').show();
}
function ejecutarReporte() {
    const ficha    = document.getElementById('rpFicha').value;
    const actividad = document.getElementById('rpActividad').value;
    const fecha    = document.getElementById('rpFecha').value;
    document.getElementById('reporteContent').innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i></div>';

    const params = new URLSearchParams();
    if (ficha)     params.append('ficha_id', ficha);
    if (actividad) params.append('actividad_id', actividad);
    if (fecha)     params.append('fecha', fecha);

    fetch(`${ROUTES.reporte}?${params}`, {headers:{Accept:'application/json'}})
    .then(r => r.json())
    .then(data => {
        if (!data.success) { document.getElementById('reporteContent').innerHTML = '<p class="text-danger">Sin datos.</p>'; return; }
        const res = data.resumen;
        const rows = data.registros.map(r => `<tr>
            <td>${r.fecha}</td><td>${r.ficha||'--'}</td><td>${r.programa||'--'}</td>
            <td>${r.actividad}</td><td>${r.moment||r.momento}</td>
            <td>${r.document_number}</td><td>${r.full_name}</td>
            <td>${r.estado}</td><td>${r.observacion||'--'}</td>
            <td>${r.evidencia}</td><td>${r.instructor||'--'}</td>
        </tr>`).join('');

        document.getElementById('reporteContent').innerHTML = `
            <div class="d-flex gap-3 mb-3 small">
                <span class="badge bg-secondary px-3 py-2">Total: ${res.total}</span>
                <span class="badge bg-success px-3 py-2">Asistieron: ${res.asistio}</span>
                <span class="badge bg-danger px-3 py-2">Faltaron: ${res.falto}</span>
                <span class="badge bg-warning text-dark px-3 py-2">% Asistencia: ${res.porcentaje_asistencia}%</span>
                <span class="badge bg-light text-dark border px-3 py-2">Generado: ${res.generado_en}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered small" id="tablaReporte">
                    <thead class="table-light"><tr>
                        <th>Fecha</th><th>Ficha</th><th>Programa</th><th>Actividad</th><th>Momento</th>
                        <th>Documento</th><th>Aprendiz</th><th>Estado</th><th>Obs.</th><th>Evidencia</th><th>Instructor</th>
                    </tr></thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
            <button class="btn btn-sm btn-outline-danger rounded-pill mt-2" onclick="imprimirReporte()">
                <i class="fas fa-print me-1"></i> Imprimir / PDF
            </button>`;
    })
    .catch(() => { document.getElementById('reporteContent').innerHTML = '<p class="text-danger">Error.</p>'; });
}
function imprimirReporte() {
    const tabla = document.getElementById('tablaReporte')?.outerHTML;
    if (!tabla) return;
    const w = window.open('', '_blank');
    w.document.write(`<html><head><title>Reporte Asistencia - Control ECP</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3/dist/css/bootstrap.min.css">
        </head><body class="p-4"><h4>Reporte de Asistencia — Control ECP</h4>${tabla}</body></html>`);
    w.document.close(); w.print();
}

// ─── Toast ────────────────────────────────────────────────────────────────────
function toast(msg, tipo='success') {
    const t = document.getElementById('toastFeedback');
    const c = {success:'#39A900',danger:'#dc3545',warning:'#e65c00',info:'#0dcaf0'};
    t.style.backgroundColor = c[tipo]||c.success;
    document.getElementById('toastMsg').textContent = msg;
    new bootstrap.Toast(t,{delay:3500}).show();
}
</script>
@endsection
