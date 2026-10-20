@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">

    {{-- Breadcrumb & Cabecera --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.instructor.inicio') }}" class="text-decoration-none" style="color:#39A900;">
                            <i class="fas fa-house me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item active" style="color:#001A29;font-weight:600;">Asistencias</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color:#001A29;font-family:'Outfit',sans-serif;">
                <i class="fas fa-user-check me-2" style="color:#39A900;"></i>Registro de Asistencias
            </h2>
            <p class="text-muted small mb-0">
                Control ECP &bull; Los aprendices se cargan desde la ficha vía
                <span class="badge" style="background:#39A900;font-size:.7rem;">API Central SICEFA</span>
                con BD local como respaldo automático.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('controlecp.instructor.inicio') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    {{-- ─── PASO 1: Ficha + Actividad + Fecha ─── --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius:18px;">
        <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
            <h5 class="fw-bold mb-0" style="color:#001A29;">
                <i class="fas fa-graduation-cap me-2" style="color:#39A900;"></i>
                Paso 1 — Selecciona Ficha, Actividad y Fecha
            </h5>
        </div>
        <div class="card-body px-4 pb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Ficha a cargo <span class="text-danger">*</span></label>
                    <select id="selectFicha" class="form-select rounded-pill">
                        <option value="">— Selecciona una ficha —</option>
                        @foreach ($fichas as $ficha)
                            <option value="{{ $ficha->id }}"
                                    data-code="{{ $ficha->code }}"
                                    data-program="{{ $ficha->program?->name ?? 'Sin programa' }}"
                                    data-total="{{ $ficha->apprentices_count }}">
                                {{ $ficha->code }} — {{ Str::limit($ficha->program?->name ?? 'Sin programa', 35) }}
                                ({{ $ficha->apprentices_count }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Actividad ECP <span class="text-danger">*</span></label>
                    <select id="selectActividad" class="form-select rounded-pill">
                        <option value="">— Selecciona una actividad —</option>
                        @php $momentoActual = null; @endphp
                        @foreach ($actividades as $act)
                            @if ($momentoActual !== $act['momento'])
                                @php $momentoActual = $act['momento']; @endphp
                                <option disabled style="color:{{ $act['momento_color'] }};font-weight:700;">
                                    ── {{ $act['momento'] }} ──
                                </option>
                            @endif
                            <option value="{{ $act['id'] }}"
                                    data-nombre="{{ $act['nombre'] }}"
                                    data-momento="{{ $act['momento_id'] }}"
                                    data-color="{{ $act['momento_color'] }}">
                                {{ $act['numero'] }}. {{ $act['nombre'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Fecha <span class="text-danger">*</span></label>
                    <input type="date" id="fechaAsistencia" class="form-control rounded-pill"
                           value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-2 d-grid">
                    <button id="btnCargarLista" class="btn rounded-pill text-white fw-semibold" style="background:#39A900;" disabled>
                        <i class="fas fa-list-check me-1" id="iconCargar"></i> Cargar Lista
                    </button>
                </div>
            </div>
            {{-- Info ficha --}}
            <div id="fichaInfo" class="mt-3 p-3 rounded-3 d-none small" style="background:#f0fbf4;border-left:4px solid #39A900;">
                <div class="row g-2">
                    <div class="col-sm-3"><strong>Ficha:</strong> <span id="infoCode">--</span></div>
                    <div class="col-sm-5"><strong>Programa:</strong> <span id="infoProgram">--</span></div>
                    <div class="col-sm-2"><strong>Aprendices:</strong> <span id="infoTotal">--</span></div>
                    <div class="col-sm-2"><strong>Fuente:</strong> <span id="infoFuente">--</span></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── PASO 2: Lista de aprendices ─── --}}
    <div id="seccionLista" class="d-none">
        <div class="card border-0 shadow-sm mb-4" style="border-radius:18px;">
            <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="fw-bold mb-0" style="color:#001A29;">
                    <i class="fas fa-users me-2" style="color:#39A900;"></i>
                    Paso 2 — Lista de Aprendices
                    <span id="totalAprendices" class="badge rounded-pill ms-2" style="background:#39A900;">0</span>
                </h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm rounded-pill btn-outline-success" onclick="marcarTodos('ASISTIO')">
                        <i class="fas fa-check-double"></i> Todos presentes
                    </button>
                    <button class="btn btn-sm rounded-pill btn-outline-danger" onclick="marcarTodos('FALTO')">
                        <i class="fas fa-times"></i> Todos ausentes
                    </button>
                </div>
            </div>

            {{-- Buscador rápido --}}
            <div class="px-4 pb-2">
                <input type="text" id="buscadorAprendiz" class="form-control form-control-sm rounded-pill"
                       placeholder="Buscar aprendiz por nombre o documento..."
                       oninput="filtrarTabla(this.value)">
            </div>

            <div class="card-body px-4 pb-2">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tablaAprendices">
                        <thead style="background:#f0fbf4;color:#001A29;" class="small">
                            <tr>
                                <th class="ps-3" style="width:4%">#</th>
                                <th style="width:13%">Documento</th>
                                <th>Nombre</th>
                                <th style="width:12%">Estado</th>
                                <th class="text-center" style="width:30%">Asistencia</th>
                                <th style="width:15%">Observación</th>
                                <th style="width:6%">Evidencia</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyAprendices">
                            <tr><td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-circle-notch fa-spin fa-2x mb-2 d-block"></i>Cargando...
                            </td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-white border-0 px-4 pb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex gap-2" id="contadores">
                    <span class="badge bg-success px-3 py-2 fs-6" id="cntAsistio">✓ 0</span>
                    <span class="badge bg-danger px-3 py-2 fs-6" id="cntFalto">✗ 0</span>
                    <span class="badge bg-info px-3 py-2 fs-6" id="cntExcusa">E 0</span>
                    <span class="badge bg-warning text-dark px-3 py-2 fs-6" id="cntRetardo">R 0</span>
                </div>
                <button id="btnGuardar" class="btn rounded-pill px-4 text-white fw-semibold"
                        style="background:#39A900;" onclick="guardarAsistencia()">
                    <i class="fas fa-floppy-disk me-1" id="iconGuardar"></i> Guardar Asistencia
                </button>
            </div>
        </div>
    </div>

    {{-- ─── Historial reciente ─── --}}
    @if ($historial->count() > 0)
    <div class="card border-0 shadow-sm mb-4" style="border-radius:18px;">
        <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0" style="color:#001A29;">
                <i class="fas fa-clock-rotate-left me-2" style="color:#39A900;"></i>Mis Últimas Asistencias Registradas
            </h5>
        </div>
        <div class="card-body px-4 pb-4">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle small">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th><th>Ficha</th><th>Actividad</th>
                            <th>Aprendiz</th><th class="text-center">Estado</th><th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($historial as $reg)
                        <tr id="fila-{{ $reg->id }}">
                            <td>{{ $reg->fecha?->format('d/m/Y') }}</td>
                            <td><span class="fw-bold font-monospace">{{ $reg->course?->code ?? '--' }}</span></td>
                            <td>{{ Str::limit($reg->actividad_nombre, 35) }}</td>
                            <td>{{ $reg->full_name }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $reg->estado_badge }}" id="badge-{{ $reg->id }}">
                                    {{ $reg->estado_label }}
                                </span>
                            </td>
                            <td>
                                @if (!$reg->is_anulado)
                                <button class="btn btn-xs btn-outline-primary rounded-pill me-1"
                                        onclick="abrirEditar({{ $reg->id }}, '{{ $reg->estado }}', '{{ addslashes($reg->observacion ?? '') }}')"
                                        title="HU-004 Editar">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button class="btn btn-xs btn-outline-danger rounded-pill me-1"
                                        onclick="abrirAnular({{ $reg->id }})"
                                        title="HU-005 Anular">
                                    <i class="fas fa-ban"></i>
                                </button>
                                <button class="btn btn-xs btn-outline-secondary rounded-pill"
                                        onclick="abrirEvidencia({{ $reg->id }}, '{{ $reg->course_id }}', {{ $reg->actividad_id }}, '{{ $reg->fecha?->format('Y-m-d') }}', '{{ $reg->document_number }}')"
                                        title="HU-002 Evidencia">
                                    <i class="fas fa-camera{{ $reg->tiene_evidencia ? ' text-success' : '' }}"></i>
                                </button>
                                @else
                                <span class="text-muted small">Anulado</span>
                                @endif
                                <button class="btn btn-xs btn-outline-info rounded-pill ms-1"
                                        onclick="verHistorial({{ $reg->id }})"
                                        title="HU-010 Historial">
                                    <i class="fas fa-history"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- ─── MODAL: Editar registro (HU-004) ─── --}}
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" style="color:#001A29;">
                    <i class="fas fa-pen me-2" style="color:#39A900;"></i>Editar Asistencia
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editarId">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Estado</label>
                    <select id="editarEstado" class="form-select rounded-pill">
                        <option value="ASISTIO">Asistió</option>
                        <option value="FALTO">Faltó</option>
                        <option value="EXCUSA">Excusa</option>
                        <option value="RETARDO">Retardo</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Observación</label>
                    <textarea id="editarObservacion" class="form-control rounded-3" rows="2"></textarea>
                </div>
                <div class="mb-1">
                    <label class="form-label small fw-semibold">
                        Motivo de edición <span class="text-danger">*</span>
                        <small class="text-muted">(obligatorio para auditoría)</small>
                    </label>
                    <textarea id="editarMotivo" class="form-control rounded-3" rows="2"
                              placeholder="Describe el motivo del cambio..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn rounded-pill text-white" style="background:#39A900;" onclick="confirmarEdicion()">
                    <i class="fas fa-save me-1"></i> Guardar Cambio
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ─── MODAL: Anular registro (HU-005) ─── --}}
<div class="modal fade" id="modalAnular" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="fas fa-ban me-2"></i>Anular Registro
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="anularId">
                <div class="alert alert-warning rounded-3 small">
                    El registro cambiará a estado <strong>Anulada</strong> y no podrá editarse.
                    El historial conservará el cambio para auditoría.
                </div>
                <div class="mb-1">
                    <label class="form-label small fw-semibold">
                        Motivo de anulación <span class="text-danger">*</span>
                    </label>
                    <textarea id="anularMotivo" class="form-control rounded-3" rows="3"
                              placeholder="Describe el motivo de la anulación..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-danger rounded-pill" onclick="confirmarAnulacion()">
                    <i class="fas fa-ban me-1"></i> Anular Registro
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ─── MODAL: Subir evidencia (HU-002) ─── --}}
<div class="modal fade" id="modalEvidencia" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" style="color:#001A29;">
                    <i class="fas fa-camera me-2" style="color:#39A900;"></i>Registrar Evidencia
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="evFichaId">
                <input type="hidden" id="evActividadId">
                <input type="hidden" id="evFecha">
                <input type="hidden" id="evDocumento">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Foto de la sesión (jpg/png, max 5MB)</label>
                    <input type="file" id="evArchivo" class="form-control rounded-pill" accept="image/*,video/*">
                </div>
                <div class="text-center text-muted small my-2">— ó —</div>
                <div class="mb-1">
                    <label class="form-label small fw-semibold">Enlace de video / foto en línea</label>
                    <input type="url" id="evEnlace" class="form-control rounded-pill"
                           placeholder="https://...">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn rounded-pill text-white" style="background:#39A900;" onclick="confirmarEvidencia()">
                    <i class="fas fa-upload me-1"></i> Guardar Evidencia
                </button>
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
                    <i class="fas fa-history me-2" style="color:#39A900;"></i>Historial de Cambios (Auditoría)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="historialContent">
                <div class="text-center py-3 text-muted">Cargando...</div>
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
    aprendices : '{{ url("control-ecp/instructor/asistencia/aprendices") }}',
    guardar    : '{{ route("controlecp.instructor.asistencia.store") }}',
    evidencia  : '{{ route("controlecp.instructor.asistencia.evidencia") }}',
    editar     : '{{ url("control-ecp/asistencia") }}',   // /{id}/editar
    anular     : '{{ url("control-ecp/asistencia") }}',   // /{id}/anular
    historial  : '{{ url("control-ecp/asistencia") }}',   // /{id}/historial
    csrf       : '{{ csrf_token() }}'
};

let aprendicesData = [];
let fichaId        = null;
let actividad      = { id: null, nombre: '', momentoId: 1 };

// ─── Activar botón Cargar ────────────────────────────────────────────────────
['selectFicha','selectActividad','fechaAsistencia'].forEach(id =>
    document.getElementById(id).addEventListener('change', () => {
        const f = document.getElementById('selectFicha').value;
        const a = document.getElementById('selectActividad').value;
        const d = document.getElementById('fechaAsistencia').value;
        document.getElementById('btnCargarLista').disabled = !(f && a && d);
    })
);
document.getElementById('btnCargarLista').addEventListener('click', cargarLista);

// ─── Cargar Lista (HU-001) ───────────────────────────────────────────────────
function cargarLista() {
    fichaId = document.getElementById('selectFicha').value;
    const selAct = document.getElementById('selectActividad');
    const opt    = selAct.options[selAct.selectedIndex];
    actividad    = { id: opt.value, nombre: opt.dataset.nombre, momentoId: opt.dataset.momento };
    const fecha  = document.getElementById('fechaAsistencia').value;

    const selFicha = document.getElementById('selectFicha');
    const fo       = selFicha.options[selFicha.selectedIndex];
    document.getElementById('infoCode').textContent    = fo.dataset.code;
    document.getElementById('infoProgram').textContent = fo.dataset.program;
    document.getElementById('infoTotal').textContent   = fo.dataset.total;
    document.getElementById('fichaInfo').classList.remove('d-none');

    const iconCargar = document.getElementById('iconCargar');
    const btn        = document.getElementById('btnCargarLista');
    iconCargar.className = 'fas fa-spinner fa-spin me-1';
    btn.disabled = true;

    document.getElementById('tbodyAprendices').innerHTML =
        '<tr><td colspan="7" class="text-center py-4 text-muted"><i class="fas fa-circle-notch fa-spin fa-2x mb-2 d-block"></i>Cargando aprendices...</td></tr>';
    document.getElementById('seccionLista').classList.remove('d-none');

    fetch(`${ROUTES.aprendices}/${fichaId}?actividad_id=${actividad.id}&fecha=${fecha}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) throw new Error(data.message || 'Error');
        aprendicesData = data.aprendices || [];
        const fuente   = data.fuente === 'api_central'
            ? '<span class="badge bg-success">API SICEFA</span>'
            : '<span class="badge bg-secondary">BD Local</span>';
        document.getElementById('infoFuente').outerHTML = `<span id="infoFuente">${fuente}</span>`;
        document.getElementById('totalAprendices').textContent = aprendicesData.length;
        renderTabla(aprendicesData, data.registros_guardados || {});
        actualizarContadores();
    })
    .catch(err => {
        document.getElementById('tbodyAprendices').innerHTML =
            `<tr><td colspan="7" class="text-center text-danger py-4"><i class="fas fa-exclamation-circle fa-2x mb-2 d-block"></i>${err.message}</td></tr>`;
        toast('Error al cargar: ' + err.message, 'danger');
    })
    .finally(() => {
        iconCargar.className = 'fas fa-list-check me-1';
        btn.disabled = false;
    });
}

// ─── Renderizar tabla ─────────────────────────────────────────────────────────
function renderTabla(aprendices, guardados) {
    if (!aprendices.length) {
        document.getElementById('tbodyAprendices').innerHTML =
            '<tr><td colspan="7" class="text-center py-4 text-muted">No hay aprendices en esta ficha.</td></tr>';
        return;
    }
    const rows = aprendices.map((a, i) => {
        const g = guardados[a.document_number] || {};
        const estado = g.estado || 'ASISTIO';
        const opciones = ['ASISTIO','FALTO','EXCUSA','RETARDO'].map(e => {
            const labels = {ASISTIO:'Presente',FALTO:'Faltó',EXCUSA:'Excusa',RETARDO:'Retardo'};
            const clss   = {ASISTIO:'outline-success',FALTO:'outline-danger',EXCUSA:'outline-info',RETARDO:'outline-warning'};
            const sel    = estado === e ? 'checked' : '';
            return `<input type="radio" class="btn-check estado-radio" name="est_${i}" id="est_${i}_${e}"
                        value="${e}" data-idx="${i}" ${sel} autocomplete="off">
                    <label class="btn btn-${clss[e]} btn-sm" for="est_${i}_${e}">${labels[e]}</label>`;
        }).join('');
        const evIcon = g.evidencia_url
            ? `<i class="fas fa-camera-retro text-success" title="Con evidencia"></i>`
            : `<i class="fas fa-camera text-muted" title="Sin evidencia"></i>`;
        return `<tr data-name="${a.full_name.toLowerCase()}" data-doc="${a.document_number}">
            <td class="ps-3 text-muted small">${i+1}</td>
            <td><span class="fw-bold font-monospace small">${a.document_number}</span></td>
            <td class="fw-semibold" style="color:#001A29;">${a.full_name}</td>
            <td><span class="badge bg-light text-dark border small">${a.status||'En formación'}</span></td>
            <td class="text-center"><div class="btn-group btn-group-sm" role="group">${opciones}</div></td>
            <td><input type="text" class="form-control form-control-sm rounded-pill obs-input" data-idx="${i}"
                       placeholder="Nota..." style="font-size:.8rem;" value="${g.observacion||''}"></td>
            <td class="text-center">${evIcon}</td>
        </tr>`;
    }).join('');
    document.getElementById('tbodyAprendices').innerHTML = rows;
    document.querySelectorAll('.estado-radio').forEach(r => r.addEventListener('change', actualizarContadores));
}

// ─── Contadores en tiempo real ────────────────────────────────────────────────
function actualizarContadores() {
    const c = {ASISTIO:0,FALTO:0,EXCUSA:0,RETARDO:0};
    document.querySelectorAll('.estado-radio:checked').forEach(r => { if (c[r.value]!==undefined) c[r.value]++; });
    document.getElementById('cntAsistio').textContent = '✓ '+c.ASISTIO;
    document.getElementById('cntFalto').textContent   = '✗ '+c.FALTO;
    document.getElementById('cntExcusa').textContent  = 'E '+c.EXCUSA;
    document.getElementById('cntRetardo').textContent = 'R '+c.RETARDO;
}

// ─── Marcar todos ─────────────────────────────────────────────────────────────
function marcarTodos(estado) {
    document.querySelectorAll(`.estado-radio[value="${estado}"]`).forEach(r => { r.checked = true; });
    actualizarContadores();
}

// ─── Filtrar tabla (buscador) ─────────────────────────────────────────────────
function filtrarTabla(term) {
    const t = term.toLowerCase();
    document.querySelectorAll('#tbodyAprendices tr[data-name]').forEach(row => {
        const match = row.dataset.name.includes(t) || row.dataset.doc.includes(t);
        row.style.display = match ? '' : 'none';
    });
}

// ─── Guardar asistencia (HU-001/003) ─────────────────────────────────────────
function guardarAsistencia() {
    if (!fichaId || !actividad.id) { toast('Selecciona ficha y actividad.','warning'); return; }
    if (!aprendicesData.length)    { toast('No hay aprendices cargados.','warning'); return; }
    const fecha = document.getElementById('fechaAsistencia').value;
    if (!fecha) { toast('Selecciona una fecha.','warning'); return; }

    const registros = aprendicesData.map((a, i) => {
        const radio = document.querySelector(`.estado-radio[name="est_${i}"]:checked`);
        const obs   = document.querySelector(`.obs-input[data-idx="${i}"]`);
        return { apprentice_id: a.id, document_number: a.document_number, full_name: a.full_name,
                 estado: radio ? radio.value : 'ASISTIO', observacion: obs ? obs.value : '' };
    });

    const icon = document.getElementById('iconGuardar');
    const btn  = document.getElementById('btnGuardar');
    icon.className = 'fas fa-spinner fa-spin me-1';
    btn.disabled   = true;

    fetch(ROUTES.guardar, {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':ROUTES.csrf,'Accept':'application/json'},
        body: JSON.stringify({ ficha_id: fichaId, actividad_id: actividad.id,
            actividad_nombre: actividad.nombre, momento_id: actividad.momentoId,
            fecha, registros, fuente: document.getElementById('infoFuente')?.textContent?.includes('SICEFA') ? 'api_central' : 'bd_local' })
    })
    .then(r => r.json())
    .then(data => toast(data.message, data.success ? 'success' : 'danger'))
    .catch(() => toast('Error de conexión.','danger'))
    .finally(() => { icon.className = 'fas fa-floppy-disk me-1'; btn.disabled = false; });
}

// ─── HU-004: Editar ──────────────────────────────────────────────────────────
function abrirEditar(id, estado, obs) {
    document.getElementById('editarId').value = id;
    document.getElementById('editarEstado').value = estado;
    document.getElementById('editarObservacion').value = obs;
    document.getElementById('editarMotivo').value = '';
    new bootstrap.Modal('#modalEditar').show();
}
function confirmarEdicion() {
    const id     = document.getElementById('editarId').value;
    const estado = document.getElementById('editarEstado').value;
    const obs    = document.getElementById('editarObservacion').value;
    const motivo = document.getElementById('editarMotivo').value.trim();
    if (!motivo) { toast('El motivo de edición es obligatorio.','warning'); return; }

    fetch(`${ROUTES.editar}/${id}/editar`, {
        method: 'PUT',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':ROUTES.csrf,'Accept':'application/json'},
        body: JSON.stringify({estado, observacion: obs, motivo_edicion: motivo})
    })
    .then(r => r.json())
    .then(data => {
        toast(data.message, data.success ? 'success' : 'danger');
        if (data.success) {
            bootstrap.Modal.getInstance('#modalEditar').hide();
            setTimeout(() => location.reload(), 1200);
        }
    })
    .catch(() => toast('Error.','danger'));
}

// ─── HU-005: Anular ──────────────────────────────────────────────────────────
function abrirAnular(id) {
    document.getElementById('anularId').value = id;
    document.getElementById('anularMotivo').value = '';
    new bootstrap.Modal('#modalAnular').show();
}
function confirmarAnulacion() {
    const id     = document.getElementById('anularId').value;
    const motivo = document.getElementById('anularMotivo').value.trim();
    if (!motivo) { toast('El motivo de anulación es obligatorio.','warning'); return; }

    fetch(`${ROUTES.anular}/${id}/anular`, {
        method: 'PUT',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':ROUTES.csrf,'Accept':'application/json'},
        body: JSON.stringify({motivo_anulacion: motivo})
    })
    .then(r => r.json())
    .then(data => {
        toast(data.message, data.success ? 'success' : 'danger');
        if (data.success) {
            bootstrap.Modal.getInstance('#modalAnular').hide();
            setTimeout(() => location.reload(), 1200);
        }
    })
    .catch(() => toast('Error.','danger'));
}

// ─── HU-002: Evidencia ───────────────────────────────────────────────────────
function abrirEvidencia(regId, fichaId, actId, fecha, doc) {
    document.getElementById('evFichaId').value    = fichaId;
    document.getElementById('evActividadId').value = actId;
    document.getElementById('evFecha').value      = fecha;
    document.getElementById('evDocumento').value  = doc;
    document.getElementById('evArchivo').value    = '';
    document.getElementById('evEnlace').value     = '';
    new bootstrap.Modal('#modalEvidencia').show();
}
function confirmarEvidencia() {
    const fd = new FormData();
    fd.append('_token',          ROUTES.csrf);
    fd.append('ficha_id',        document.getElementById('evFichaId').value);
    fd.append('actividad_id',    document.getElementById('evActividadId').value);
    fd.append('fecha',           document.getElementById('evFecha').value);
    fd.append('document_number', document.getElementById('evDocumento').value);
    const archivo  = document.getElementById('evArchivo').files[0];
    const enlace   = document.getElementById('evEnlace').value;
    if (archivo) fd.append('evidencia', archivo);
    if (enlace)  fd.append('enlace_video', enlace);
    if (!archivo && !enlace) { toast('Adjunta un archivo o un enlace.','warning'); return; }

    fetch(ROUTES.evidencia, { method:'POST', headers:{'Accept':'application/json'}, body: fd })
    .then(r => r.json())
    .then(data => {
        toast(data.message, data.success ? 'success' : 'danger');
        if (data.success) { bootstrap.Modal.getInstance('#modalEvidencia').hide(); setTimeout(() => location.reload(), 1200); }
    })
    .catch(() => toast('Error.','danger'));
}

// ─── HU-010: Historial de cambios ────────────────────────────────────────────
function verHistorial(id) {
    document.getElementById('historialContent').innerHTML = '<div class="text-center py-3 text-muted"><i class="fas fa-spinner fa-spin"></i></div>';
    new bootstrap.Modal('#modalHistorial').show();

    fetch(`${ROUTES.historial}/${id}/historial`, {
        headers: {'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) { document.getElementById('historialContent').innerHTML = '<p class="text-danger">'+data.message+'</p>'; return; }
        const h = data.historial || [];
        const rows = h.length === 0 ? '<tr><td colspan="3" class="text-center text-muted">Sin cambios registrados.</td></tr>'
            : h.map(c => `<tr>
                <td>${new Date(c.timestamp).toLocaleString('es-CO')}</td>
                <td><span class="badge bg-secondary">${c.accion}</span></td>
                <td><pre class="mb-0 small">${JSON.stringify(c.datos, null, 2)}</pre></td>
            </tr>`).join('');

        document.getElementById('historialContent').innerHTML = `
            <p class="small"><strong>Aprendiz:</strong> ${data.registro.full_name} (${data.registro.document_number})<br>
            <strong>Actividad:</strong> ${data.registro.actividad} — ${data.registro.fecha}</p>
            <div class="table-responsive"><table class="table table-sm table-bordered small">
                <thead class="table-light"><tr><th>Fecha/Hora</th><th>Acción</th><th>Detalle</th></tr></thead>
                <tbody>${rows}</tbody>
            </table></div>`;
    })
    .catch(() => { document.getElementById('historialContent').innerHTML = '<p class="text-danger">Error al cargar.</p>'; });
}

// ─── Toast helper ─────────────────────────────────────────────────────────────
function toast(msg, tipo = 'success') {
    const t = document.getElementById('toastFeedback');
    const c = {success:'#39A900',danger:'#dc3545',warning:'#e65c00',info:'#0dcaf0'};
    t.style.backgroundColor = c[tipo] || c.success;
    document.getElementById('toastMsg').textContent = msg;
    new bootstrap.Toast(t, {delay:3500}).show();
}
</script>
@endsection
