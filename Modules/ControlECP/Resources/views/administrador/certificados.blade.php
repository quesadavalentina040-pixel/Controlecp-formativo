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
                    <li class="breadcrumb-item active" style="color:#001A29;font-weight:600;">Certificados</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color:#001A29;">
                <i class="fas fa-certificate me-2" style="color:#39A900;"></i>Gestión de Certificaciones ECP
            </h2>
            <p class="text-muted small mb-0">
                Control ECP · 13 casos de uso · Preparado para API REST Central ERP SENA Empresa / SICEFA
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-outline-info rounded-pill btn-sm" onclick="abrirReporte()">
                <i class="fas fa-file-pdf me-1"></i> Generar Reporte
            </button>
            <button class="btn rounded-pill btn-sm text-white" style="background:#39A900;"
                    onclick="abrirCertificar()">
                <i class="fas fa-stamp me-1"></i> Certificar Ficha
            </button>
        </div>
    </div>

    {{-- ─── HU-012: Indicadores ─── --}}
    <div class="row g-3 mb-4">
        @php
            $ind = $indicadores;
            $cards = [
                ['val'=>$ind['certCount'],   'label'=>'Momentos certificados',       'color'=>'#39A900','icon'=>'fa-certificate'],
                ['val'=>$ind['fichasCompletas'],'label'=>'Fichas 100% certificadas','color'=>'#0070B8','icon'=>'fa-graduation-cap'],
                ['val'=>$ind['m1'],          'label'=>'Momento 1 certificados',      'color'=>'#39A900','icon'=>'fa-brain'],
                ['val'=>$ind['m2'],          'label'=>'Momento 2 certificados',      'color'=>'#0070B8','icon'=>'fa-people-holding-hands'],
                ['val'=>$ind['m3'],          'label'=>'Momento 3 certificados',      'color'=>'#6A1B9A','icon'=>'fa-scale-balanced'],
                ['val'=>$ind['anuladas'],    'label'=>'Anuladas',                   'color'=>'#6c757d','icon'=>'fa-ban'],
                ['val'=>$ind['sinSofia'],    'label'=>'Sin emisión SOFIA Plus',      'color'=>'#D68910','icon'=>'fa-exclamation-triangle'],
                ['val'=>$fichasPendientes->count(),'label'=>'Fichas listas para certificar','color'=>'#E74C3C','icon'=>'fa-hourglass-half'],
            ];
        @endphp
        @foreach ($cards as $c)
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 shadow-sm" style="border-radius:16px;">
                <div class="card-body text-center p-3">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-2"
                         style="width:44px;height:44px;background:{{ $c['color'] }}18;color:{{ $c['color'] }};font-size:1.1rem;">
                        <i class="fas {{ $c['icon'] }}"></i>
                    </div>
                    <h4 class="fw-bold mb-0" style="color:{{ $c['color'] }};">{{ $c['val'] }}</h4>
                    <p class="small text-muted mb-0" style="font-size:.78rem;">{{ $c['label'] }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ─── HU-004: Fichas pendientes de certificar ─── --}}
    @if ($fichasPendientes->count() > 0)
    <div class="alert border-0 shadow-sm mb-4 d-flex align-items-start gap-3" style="border-radius:14px;background:#fff8e1;border-left:5px solid #D68910!important;">
        <i class="fas fa-hourglass-half fa-lg mt-1" style="color:#D68910;"></i>
        <div class="flex-grow-1">
            <strong style="color:#001A29;">{{ $fichasPendientes->count() }} ficha(s) listas para certificar (Pendiente de certificar):</strong>
            <div class="mt-2 d-flex flex-wrap gap-2">
                @foreach ($fichasPendientes->take(6) as $p)
                <button class="btn btn-sm rounded-pill text-white fw-semibold"
                        style="background:{{ $p['color'] }};font-size:.75rem;"
                        onclick="prepararCertificar({{ $p['ficha_id'] }}, {{ $p['momento_id'] }}, '{{ addslashes($p['ficha']) }}')">
                    <i class="fas fa-stamp me-1"></i>
                    Ficha {{ $p['ficha'] }} — {{ $p['momento'] }}
                </button>
                @endforeach
                @if ($fichasPendientes->count() > 6)
                <span class="text-muted small align-self-center">+{{ $fichasPendientes->count() - 6 }} más</span>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- ─── HU-009: Avance de certificación por ficha ─── --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
        <div class="card-header bg-white border-0 pt-3 pb-2 px-4">
            <h6 class="fw-bold mb-0" style="color:#001A29;">
                <i class="fas fa-chart-bar me-2 text-success"></i>
                HU-009 — Avance de Certificación por Ficha
                <small class="text-muted fw-normal ms-2">(verde = certificado, naranja = listo para certificar, gris = pendiente)</small>
            </h6>
        </div>
        <div class="card-body px-4 pb-3">
            @if ($avancePorFicha->count())
            <div class="table-responsive">
                <table class="table table-hover align-middle small">
                    <thead class="table-light">
                        <tr>
                            <th>Ficha</th>
                            <th>Programa</th>
                            <th class="text-center">Aprendices</th>
                            <th class="text-center">M1 Autoconciencia</th>
                            <th class="text-center">M2 Convivencia</th>
                            <th class="text-center">M3 Resolución</th>
                            <th class="text-center">Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($avancePorFicha as $f)
                        <tr>
                            <td><span class="fw-bold font-monospace">{{ $f['code'] }}</span></td>
                            <td class="small">{{ Str::limit($f['programa'], 35) }}</td>
                            <td class="text-center">{{ $f['total_aprendices'] }}</td>
                            @foreach ([1,2,3] as $m)
                            @php $p = $f['progreso'][$m]; @endphp
                            <td class="text-center">
                                @if ($p['certificado'])
                                    <span class="badge bg-success rounded-pill">
                                        <i class="fas fa-check me-1"></i>Certificado
                                    </span>
                                @elseif ($p['puede'])
                                    <button class="btn btn-xs rounded-pill text-white fw-semibold"
                                            style="background:#D68910;font-size:.72rem;"
                                            onclick="prepararCertificar({{ $f['id'] }}, {{ $m }}, '{{ $f['code'] }}')">
                                        <i class="fas fa-stamp me-1"></i>Certificar
                                    </button>
                                @else
                                    <div class="small text-muted">
                                        {{ $p['completadas'] }}/{{ $p['total'] }}
                                        <div class="progress mt-1" style="height:4px;">
                                            <div class="progress-bar" style="width:{{ $p['porcentaje'] }}%;background:{{ [1=>'#39A900',2=>'#0070B8',3=>'#6A1B9A'][$m] }};"></div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                            @endforeach
                            <td class="text-center">
                                @if ($f['completa'])
                                    <span class="badge bg-success">
                                        <i class="fas fa-trophy me-1"></i>Completa
                                    </span>
                                @elseif ($f['certs_count'] > 0)
                                    <span class="badge bg-warning text-dark">{{ $f['certs_count'] }}/3</span>
                                @else
                                    <span class="badge bg-secondary">Sin certificar</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-xs btn-outline-info rounded-pill"
                                        onclick="verProgreso({{ $f['id'] }})"
                                        title="Ver progreso detallado">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginación fichas (máx 10 por página) --}}
            @if ($avancePaginator->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3">
                <p class="small text-muted mb-0">
                    Mostrando {{ $avancePaginator->firstItem() }}–{{ $avancePaginator->lastItem() }}
                    de {{ $avancePaginator->total() }} fichas
                </p>
                <div>
                    {{ $avancePaginator->appends(request()->except('fichas_page'))->links() }}
                </div>
            </div>
            @endif

            @else
            <p class="text-muted text-center py-3">No hay fichas activas registradas.</p>
            @endif
        </div>
    </div>

    {{-- ─── HU-002: Filtros + HU-001: Listado ─── --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
        <div class="card-header bg-white border-0 pt-3 pb-2 px-4">
            <h5 class="fw-bold mb-0" style="color:#001A29;">
                <i class="fas fa-list me-2 text-success"></i>
                HU-001 / HU-002 — Listado y Filtros de Certificaciones
                <span class="badge rounded-pill ms-2" style="background:#39A900;">{{ $certificados->total() }}</span>
            </h5>
        </div>
        <div class="card-body px-4 pb-2">
            {{-- Filtros --}}
            <form method="GET" action="{{ route('controlecp.administrador.certificados') }}" class="mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <input type="text" name="ficha" value="{{ $filtros['ficha'] ?? '' }}"
                               class="form-control form-control-sm rounded-pill" placeholder="Ficha (código)">
                    </div>
                    <div class="col-md-2">
                        <select name="momento_id" class="form-select form-select-sm rounded-pill">
                            <option value="">Momento</option>
                            <option value="1" {{ ($filtros['momento_id'] ?? '') == '1' ? 'selected' : '' }}>M1 Autoconciencia</option>
                            <option value="2" {{ ($filtros['momento_id'] ?? '') == '2' ? 'selected' : '' }}>M2 Convivencia</option>
                            <option value="3" {{ ($filtros['momento_id'] ?? '') == '3' ? 'selected' : '' }}>M3 Resolución</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="responsable_id" class="form-select form-select-sm rounded-pill">
                            <option value="">Responsable</option>
                            @foreach ($responsables as $resp)
                            <option value="{{ $resp->id }}" {{ ($filtros['responsable_id'] ?? '') == $resp->id ? 'selected' : '' }}>
                                {{ $resp->full_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="fecha_desde" value="{{ $filtros['fecha_desde'] ?? '' }}"
                               class="form-control form-control-sm rounded-pill" title="Fecha desde">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="fecha_hasta" value="{{ $filtros['fecha_hasta'] ?? '' }}"
                               class="form-control form-control-sm rounded-pill" title="Fecha hasta">
                    </div>
                    <div class="col-md-1">
                        <select name="estado" class="form-select form-select-sm rounded-pill">
                            <option value="">Estado</option>
                            <option value="CERTIFICADA" {{ ($filtros['estado'] ?? '') == 'CERTIFICADA' ? 'selected' : '' }}>Certificada</option>
                            <option value="ANULADA" {{ ($filtros['estado'] ?? '') == 'ANULADA' ? 'selected' : '' }}>Anulada</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-grid">
                        <button type="submit" class="btn btn-sm rounded-pill text-white" style="background:#39A900;">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>

            {{-- Tabla de certificaciones --}}
            @if ($certificados->count())
            <div class="table-responsive">
                <table class="table table-hover align-middle small">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th><th>Ficha</th><th>Programa</th><th>Momento</th>
                            <th>Fecha</th><th>Responsable</th><th>% Asist.</th>
                            <th class="text-center">Estado</th><th>SOFIA+</th>
                            <th>Noti.</th><th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($certificados as $cert)
                        <tr>
                            <td class="text-muted font-monospace small">#{{ $cert->id }}</td>
                            <td><span class="fw-bold font-monospace">{{ $cert->course?->code ?? '--' }}</span></td>
                            <td>{{ Str::limit($cert->course?->program?->name ?? '--', 30) }}</td>
                            <td>
                                <span class="badge rounded-pill"
                                      style="background:{{ $cert->momento_color }}22;color:{{ $cert->momento_color }};border:1px solid {{ $cert->momento_color }}44;font-size:.7rem;">
                                    {{ $cert->momento_nombre }}
                                </span>
                            </td>
                            <td>{{ $cert->fecha_certificacion?->format('d/m/Y') }}</td>
                            <td class="small">{{ $cert->responsable?->full_name ?? '--' }}</td>
                            <td>
                                <span class="{{ $cert->porcentaje_asistencia >= 80 ? 'text-success' : ($cert->porcentaje_asistencia >= 60 ? 'text-warning' : 'text-danger') }} fw-bold">
                                    {{ $cert->porcentaje_asistencia }}%
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $cert->estado_badge }}">{{ $cert->estado }}</span>
                            </td>
                            <td class="text-center small text-muted">
                                {{-- HU-010: siempre sin emisión oficial --}}
                                <i class="fas fa-times-circle text-secondary" title="HU-010: No emitido en SOFIA Plus"></i>
                                <span style="font-size:.7rem;">No emitido</span>
                            </td>
                            <td class="text-center">
                                @if ($cert->notificacion_generada)
                                    <i class="fas fa-bell text-success" title="Notificación generada"></i>
                                @else
                                    <i class="fas fa-bell-slash text-muted"></i>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-xs btn-outline-info rounded-pill me-1"
                                        onclick="verDetalle({{ $cert->id }})" title="HU-008 Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-xs btn-outline-secondary rounded-pill"
                                        onclick="verHistorial({{ $cert->id }})" title="HU-012 Historial">
                                    <i class="fas fa-history"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-3">
                {{ $certificados->links() }}
            </div>
            @else
            <div class="text-center py-5 text-muted">
                <i class="fas fa-inbox fa-3x mb-3 d-block" style="color:#39A900;opacity:.3;"></i>
                @if (array_filter($filtros ?? []))
                    <p>No hay certificaciones con estos filtros.</p>
                    <a href="{{ route('controlecp.administrador.certificados') }}" class="btn btn-outline-secondary rounded-pill btn-sm">Limpiar filtros</a>
                @else
                    <p>No hay certificaciones registradas aún.<br>
                    <small>Usa el botón <strong>Certificar Ficha</strong> para registrar la primera.</small></p>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- Nota HU-010 --}}
    <div class="alert border-0 shadow-sm small" style="border-radius:12px;background:#f8f9fa;border-left:4px solid #6c757d!important;">
        <i class="fas fa-info-circle me-2 text-muted"></i>
        <strong>HU-010:</strong> Este módulo registra las certificaciones localmente.
        La emisión oficial en <strong>SOFIA Plus</strong> se realizará cuando se conecte con la
        <strong>API REST Central del ERP SENA Empresa / SICEFA</strong>.
        El token de verificación QR ya está generado y listo para la integración.
    </div>
</div>

{{-- ─── MODAL: Certificar ficha (HU-005/006/007) ─── --}}
<div class="modal fade" id="modalCertificar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" style="color:#001A29;">
                    <i class="fas fa-stamp me-2" style="color:#39A900;"></i>Certificar Ficha en un Momento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Ficha formativa <span class="text-danger">*</span></label>
                        <select id="certFichaId" class="form-select rounded-pill" onchange="cargarProgresoCertificar()">
                            <option value="">— Selecciona una ficha —</option>
                            @foreach ($avancePorFicha as $f)
                            <option value="{{ $f['id'] }}" data-code="{{ $f['code'] }}">
                                {{ $f['code'] }} — {{ Str::limit($f['programa'], 40) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Momento a certificar <span class="text-danger">*</span></label>
                        <select id="certMomentoId" class="form-select rounded-pill">
                            <option value="">— Selecciona un momento —</option>
                            <option value="1" style="color:#39A900;">Momento 1: Autoconciencia</option>
                            <option value="2" style="color:#0070B8;">Momento 2: Convivencia</option>
                            <option value="3" style="color:#6A1B9A;">Momento 3: Resolución</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Observación (opcional)</label>
                        <textarea id="certObservacion" class="form-control rounded-3" rows="2"
                                  placeholder="Observación sobre la certificación..."></textarea>
                    </div>
                </div>

                {{-- Estado de validación (HU-006) --}}
                <div id="certValidacion" class="mt-3"></div>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn rounded-pill text-white px-4" style="background:#39A900;"
                        onclick="confirmarCertificacion()">
                    <i class="fas fa-stamp me-1" id="iconCert"></i> Certificar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ─── MODAL: Detalle certificado (HU-008) ─── --}}
<div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" style="color:#001A29;">
                    <i class="fas fa-certificate me-2" style="color:#39A900;"></i>Detalle de Certificación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleContent">
                <div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x" style="color:#39A900;"></i></div>
            </div>
        </div>
    </div>
</div>

{{-- ─── MODAL: Historial (HU-012) ─── --}}
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
                <div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x" style="color:#39A900;"></i></div>
            </div>
        </div>
    </div>
</div>

{{-- ─── MODAL: Progreso por ficha (HU-003) ─── --}}
<div class="modal fade" id="modalProgreso" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" style="color:#001A29;">
                    <i class="fas fa-chart-bar me-2" style="color:#39A900;"></i>Progreso por Momento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="progresoContent">
                <div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x" style="color:#39A900;"></i></div>
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
                    <i class="fas fa-file-pdf me-2" style="color:#E74C3C;"></i>HU-013 — Generar Reporte de Certificaciones
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <input type="text" id="rpFicha" class="form-control form-control-sm rounded-pill" placeholder="Ficha (código)">
                    </div>
                    <div class="col-md-3">
                        <select id="rpMomento" class="form-select form-select-sm rounded-pill">
                            <option value="">Todos los momentos</option>
                            <option value="1">M1 Autoconciencia</option>
                            <option value="2">M2 Convivencia</option>
                            <option value="3">M3 Resolución</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="rpEstado" class="form-select form-select-sm rounded-pill">
                            <option value="">Todos los estados</option>
                            <option value="CERTIFICADA">Certificada</option>
                            <option value="ANULADA">Anulada</option>
                        </select>
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
    certificar : '{{ route("controlecp.certificados.certificar") }}',
    detalle    : '{{ url("control-ecp/certificados") }}', // /{id}/detalle
    historial  : '{{ url("control-ecp/certificados") }}', // /{id}/historial
    progreso   : '{{ url("control-ecp/certificados/ficha") }}', // /{fichaId}/progreso
    reporte    : '{{ route("controlecp.certificados.reporte") }}',
    csrf       : '{{ csrf_token() }}',
};
const momentoColores = { 1:'#39A900', 2:'#0070B8', 3:'#6A1B9A' };
const momentoNombres = { 1:'M1: Autoconciencia', 2:'M2: Convivencia', 3:'M3: Resolución' };

// ─── Abrir modal Certificar ───────────────────────────────────────────────────
function abrirCertificar() {
    document.getElementById('certFichaId').value  = '';
    document.getElementById('certMomentoId').value = '';
    document.getElementById('certObservacion').value = '';
    document.getElementById('certValidacion').innerHTML = '';
    new bootstrap.Modal('#modalCertificar').show();
}
function prepararCertificar(fichaId, momentoId, code) {
    document.getElementById('certFichaId').value   = fichaId;
    document.getElementById('certMomentoId').value  = momentoId;
    document.getElementById('certObservacion').value = '';
    cargarProgresoCertificar();
    new bootstrap.Modal('#modalCertificar').show();
}

// Carga el progreso de la ficha al seleccionarla en el modal Certificar
function cargarProgresoCertificar() {
    const fichaId = document.getElementById('certFichaId').value;
    if (!fichaId) { document.getElementById('certValidacion').innerHTML = ''; return; }

    document.getElementById('certValidacion').innerHTML =
        '<div class="text-center text-muted small"><i class="fas fa-spinner fa-spin me-1"></i> Verificando actividades...</div>';

    fetch(`${ROUTES.progreso}/${fichaId}`, {headers:{Accept:'application/json'}})
    .then(r => r.json())
    .then(data => {
        if (!data.success) { document.getElementById('certValidacion').innerHTML = ''; return; }
        const rows = Object.values(data.progreso).map(m => {
            const cls = m.certificado ? 'success' : (m.puede_cert ? 'warning' : 'secondary');
            const estado = m.certificado ? '✓ Certificado' : (m.puede_cert ? '⚡ Listo' : `${m.completadas}/${m.total}`);
            return `<tr>
                <td><span class="badge rounded-pill" style="background:${m.color}22;color:${m.color};border:1px solid ${m.color}44;">${m.nombre}</span></td>
                <td class="text-center"><span class="badge bg-${cls}">${estado}</span></td>
                <td>
                    <div class="progress" style="height:6px;">
                        <div class="progress-bar" style="width:${m.porcentaje}%;background:${m.color};"></div>
                    </div>
                    <small class="text-muted">${m.porcentaje}%</small>
                </td>
            </tr>`;
        }).join('');
        document.getElementById('certValidacion').innerHTML = `
            <div class="card border-0 rounded-3" style="background:#f0fbf4;">
                <div class="card-body p-3">
                    <p class="fw-semibold small mb-2" style="color:#001A29;">
                        Progreso de <strong>${data.ficha}</strong> — ${data.programa}
                    </p>
                    <table class="table table-sm mb-0 small">
                        <thead class="table-light"><tr><th>Momento</th><th class="text-center">Estado</th><th>Progreso</th></tr></thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            </div>`;
    })
    .catch(() => { document.getElementById('certValidacion').innerHTML = ''; });
}

// ─── HU-005/006/007: Confirmar certificación ──────────────────────────────────
function confirmarCertificacion() {
    const fichaId  = document.getElementById('certFichaId').value;
    const momento  = document.getElementById('certMomentoId').value;
    const obs      = document.getElementById('certObservacion').value;

    if (!fichaId)  { toast('Selecciona una ficha.','warning'); return; }
    if (!momento)  { toast('Selecciona el momento.','warning'); return; }

    const icon = document.getElementById('iconCert');
    icon.className = 'fas fa-spinner fa-spin me-1';

    fetch(ROUTES.certificar, {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':ROUTES.csrf,'Accept':'application/json'},
        body: JSON.stringify({course_id: fichaId, momento_id: momento, observacion: obs})
    })
    .then(r => r.json())
    .then(data => {
        toast(data.message, data.success ? 'success' : 'danger');
        if (data.success) {
            bootstrap.Modal.getInstance('#modalCertificar').hide();
            setTimeout(() => location.reload(), 1500);
        }
    })
    .catch(() => toast('Error de conexión.','danger'))
    .finally(() => { icon.className = 'fas fa-stamp me-1'; });
}

// ─── HU-008: Ver detalle ──────────────────────────────────────────────────────
function verDetalle(id) {
    document.getElementById('detalleContent').innerHTML =
        '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x" style="color:#39A900;"></i></div>';
    new bootstrap.Modal('#modalDetalle').show();

    fetch(`${ROUTES.detalle}/${id}/detalle`, {headers:{Accept:'application/json'}})
    .then(r => r.json())
    .then(d => {
        if (!d.success) { document.getElementById('detalleContent').innerHTML = '<p class="text-danger">'+d.message+'</p>'; return; }
        const sincronizado = d.is_synced ? '<span class="badge bg-success ms-2">Sincronizado SICEFA</span>' : '<span class="badge bg-secondary ms-2">Sin sincronizar</span>';
        const progRows = Object.values(d.progreso_ficha).map(p =>
            `<tr><td><span class="badge rounded-pill" style="background:${p.color}22;color:${p.color};border:1px solid ${p.color}44;font-size:.72rem;">${p.nombre}</span></td>
            <td class="text-center"><span class="badge bg-${p.certificado?'success':'secondary'}">${p.certificado?'✓ Certificado':'Pendiente'}</span></td>
            <td>${p.completadas}/${p.total} act. (${p.porcentaje}%)</td>
            ${p.certificado?`<td class="small">${p.cert_fecha||''} — ${p.cert_resp||''}</td>`:'<td class="text-muted small">—</td>'}
            </tr>`).join('');

        document.getElementById('detalleContent').innerHTML = `
            <div class="row g-3">
                <div class="col-md-6">
                    <table class="table table-sm table-bordered small">
                        <tr><th class="bg-light">Ficha</th><td class="font-monospace fw-bold">${d.ficha}</td></tr>
                        <tr><th class="bg-light">Programa</th><td>${d.programa}</td></tr>
                        <tr><th class="bg-light">Momento</th><td><span class="badge rounded-pill" style="background:${d.momento_color}22;color:${d.momento_color};border:1px solid ${d.momento_color}44;">${d.momento}</span></td></tr>
                        <tr><th class="bg-light">Estado</th><td><span class="badge bg-${d.estado_badge}">${d.estado}</span>${sincronizado}</td></tr>
                        <tr><th class="bg-light">Fecha</th><td>${d.fecha}</td></tr>
                        <tr><th class="bg-light">Responsable</th><td>${d.responsable} <small class="text-muted">(${d.doc_responsable})</small></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-bordered small">
                        <tr><th class="bg-light">% Asistencia</th><td class="fw-bold ${d.porcentaje_asistencia>=80?'text-success':'text-warning'}">${d.porcentaje_asistencia}%</td></tr>
                        <tr><th class="bg-light">Total aprendices</th><td>${d.total_aprendices}</td></tr>
                        <tr><th class="bg-light">Actividades registradas</th><td>${d.actividades_completadas}</td></tr>
                        <tr><th class="bg-light">SOFIA Plus (HU-010)</th><td class="text-muted"><i class="fas fa-times-circle"></i> No emitido</td></tr>
                        <tr><th class="bg-light">Notificación (HU-011)</th><td>${d.notificacion_generada?'<span class="text-success"><i class="fas fa-bell"></i> Generada</span>':'<span class="text-muted">No generada</span>'}</td></tr>
                        <tr><th class="bg-light">Token QR</th><td><code class="small">${(d.token_verificacion||'').slice(0,20)}...</code></td></tr>
                    </table>
                </div>
            </div>
            <h6 class="fw-bold mt-3 mb-2" style="color:#001A29;"><i class="fas fa-chart-bar me-2 text-success"></i>Progreso de la ficha en los 3 momentos</h6>
            <div class="table-responsive"><table class="table table-sm small">
                <thead class="table-light"><tr><th>Momento</th><th class="text-center">Estado</th><th>Avance</th><th>Certificado por</th></tr></thead>
                <tbody>${progRows}</tbody>
            </table></div>`;
    })
    .catch(() => { document.getElementById('detalleContent').innerHTML = '<p class="text-danger">Error.</p>'; });
}

// ─── HU-012: Historial ────────────────────────────────────────────────────────
function verHistorial(id) {
    document.getElementById('historialContent').innerHTML =
        '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x" style="color:#39A900;"></i></div>';
    new bootstrap.Modal('#modalHistorial').show();

    fetch(`${ROUTES.historial}/${id}/historial`, {headers:{Accept:'application/json'}})
    .then(r => r.json())
    .then(data => {
        const h = data.historial || [];
        const rows = h.length === 0
            ? '<tr><td colspan="3" class="text-center text-muted">Sin cambios registrados.</td></tr>'
            : h.map(c => `<tr>
                <td>${new Date(c.timestamp).toLocaleString('es-CO')}</td>
                <td><span class="badge bg-secondary">${c.accion}</span></td>
                <td><pre class="mb-0 small" style="font-size:.72rem;">${JSON.stringify(c.datos,null,2)}</pre></td>
            </tr>`).join('');
        document.getElementById('historialContent').innerHTML = `
            <p class="small"><strong>Ficha:</strong> ${data.ficha} — <strong>Momento:</strong> ${data.momento}
            — <strong>Estado:</strong> <span class="badge bg-${data.estado==='CERTIFICADA'?'success':'secondary'}">${data.estado}</span><br>
            <strong>Responsable:</strong> ${data.responsable||'--'}
            ${data.anulado_por ? ' | <strong>Anulado por:</strong> '+data.anulado_por : ''}</p>
            <div class="table-responsive"><table class="table table-sm table-bordered small">
                <thead class="table-light"><tr><th>Fecha/Hora</th><th>Acción</th><th>Detalle</th></tr></thead>
                <tbody>${rows}</tbody>
            </table></div>`;
    })
    .catch(() => { document.getElementById('historialContent').innerHTML = '<p class="text-danger">Error.</p>'; });
}

// ─── HU-003: Progreso por ficha ───────────────────────────────────────────────
function verProgreso(fichaId) {
    document.getElementById('progresoContent').innerHTML =
        '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x" style="color:#39A900;"></i></div>';
    new bootstrap.Modal('#modalProgreso').show();

    fetch(`${ROUTES.progreso}/${fichaId}`, {headers:{Accept:'application/json'}})
    .then(r => r.json())
    .then(data => {
        if (!data.success) { document.getElementById('progresoContent').innerHTML = '<p class="text-danger">'+data.message+'</p>'; return; }
        const cards = Object.values(data.progreso).map(m => {
            const pct = m.porcentaje;
            const cls = m.certificado ? 'success' : (m.puede_cert ? 'warning' : 'secondary');
            const btnCert = (!m.certificado && m.puede_cert)
                ? `<button class="btn btn-sm rounded-pill text-white mt-2 w-100" style="background:${m.color};"
                       onclick="prepararCertificar(${fichaId}, ${m.momento_id}, '${data.ficha}'); bootstrap.Modal.getInstance('#modalProgreso').hide();">
                       <i class="fas fa-stamp me-1"></i> Certificar este momento
                   </button>` : '';
            const certInfo = m.certificado
                ? `<div class="small mt-2 text-success"><i class="fas fa-check-circle me-1"></i>${m.cert_fecha} — ${m.cert_resp}</div>` : '';
            return `<div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius:14px;border-top:4px solid ${m.color}!important;">
                    <div class="card-body p-3 text-center">
                        <span class="badge rounded-pill fw-semibold mb-2" style="background:${m.color}22;color:${m.color};border:1px solid ${m.color}44;">
                            ${m.nombre}
                        </span>
                        <div class="fw-bold" style="color:${m.color};font-size:1.5rem;">${pct}%</div>
                        <div class="progress my-2" style="height:8px;">
                            <div class="progress-bar" style="width:${pct}%;background:${m.color};"></div>
                        </div>
                        <div class="small text-muted">${m.completadas} / ${m.total} actividades</div>
                        <span class="badge bg-${cls} mt-2">${m.certificado?'✓ Certificado':(m.puede_cert?'⚡ Listo para certificar':'Pendiente')}</span>
                        ${certInfo}${btnCert}
                    </div>
                </div>
            </div>`;
        }).join('');
        document.getElementById('progresoContent').innerHTML = `
            <p class="fw-bold mb-3" style="color:#001A29;">Ficha <span class="font-monospace">${data.ficha}</span> — ${data.programa}</p>
            <div class="row g-3">${cards}</div>`;
    })
    .catch(() => { document.getElementById('progresoContent').innerHTML = '<p class="text-danger">Error.</p>'; });
}

// ─── HU-013: Reporte ──────────────────────────────────────────────────────────
function abrirReporte() {
    document.getElementById('reporteContent').innerHTML = '';
    new bootstrap.Modal('#modalReporte').show();
}
function ejecutarReporte() {
    const params = new URLSearchParams();
    const f = document.getElementById('rpFicha').value;
    const m = document.getElementById('rpMomento').value;
    const e = document.getElementById('rpEstado').value;
    if (f) params.append('ficha', f);
    if (m) params.append('momento_id', m);
    if (e) params.append('estado', e);
    document.getElementById('reporteContent').innerHTML =
        '<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i></div>';

    fetch(`${ROUTES.reporte}?${params}`, {headers:{Accept:'application/json'}})
    .then(r => r.json())
    .then(data => {
        const res = data.resumen;
        const rows = data.registros.map(r => `<tr>
            <td>${r.id}</td><td class="font-monospace fw-bold">${r.ficha||'--'}</td>
            <td>${r.programa||'--'}</td><td>${r.momento}</td><td>${r.estado}</td>
            <td>${r.fecha}</td><td>${r.responsable||'--'}</td>
            <td>${r.porcentaje_asistencia}</td><td>${r.total_aprendices}</td>
            <td>${r.notificacion}</td><td>${r.sofia_plus}</td>
        </tr>`).join('');

        document.getElementById('reporteContent').innerHTML = `
            <div class="row g-2 mb-3 small">
                <div class="col-auto"><span class="badge bg-success px-3 py-2">Certificadas: ${res.certificadas}</span></div>
                <div class="col-auto"><span class="badge bg-secondary px-3 py-2">Anuladas: ${res.anuladas}</span></div>
                <div class="col-auto"><span class="badge bg-primary px-3 py-2">Fichas completas (3M): ${res.fichas_completas}</span></div>
                <div class="col-auto"><span class="badge bg-info text-dark px-3 py-2">Con 1M: ${res.fichas_con_1_momento}</span></div>
                <div class="col-auto"><span class="badge bg-warning text-dark px-3 py-2">Con 2M: ${res.fichas_con_2_momentos}</span></div>
                <div class="col-auto"><span class="badge bg-light text-dark border px-3 py-2">Generado: ${res.generado_en}</span></div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered small" id="tablaReporte">
                    <thead class="table-light"><tr>
                        <th>ID</th><th>Ficha</th><th>Programa</th><th>Momento</th><th>Estado</th>
                        <th>Fecha</th><th>Responsable</th><th>% Asist.</th><th>Aprendices</th>
                        <th>Noti.</th><th>SOFIA+</th>
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
    w.document.write(`<html><head><title>Reporte Certificados — Control ECP</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3/dist/css/bootstrap.min.css">
        </head><body class="p-4"><h4>Reporte de Certificaciones ECP</h4>${tabla}</body></html>`);
    w.document.close(); w.print();
}

// ─── Toast ─────────────────────────────────────────────────────────────────────
function toast(msg, tipo='success') {
    const t = document.getElementById('toastFeedback');
    const c = {success:'#39A900',danger:'#dc3545',warning:'#e65c00',info:'#0dcaf0'};
    t.style.backgroundColor = c[tipo]||c.success;
    document.getElementById('toastMsg').textContent = msg;
    new bootstrap.Toast(t,{delay:4000}).show();
}
</script>
@endsection
