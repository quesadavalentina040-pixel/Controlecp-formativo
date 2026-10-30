@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    :root {
        --ecp-green: #39A900;
        --ecp-navy: #001A29;
    }
    .stat-badge {
        border-radius: 14px;
        padding: 14px 18px;
        background: #ffffff;
        border: 1px solid rgba(0,0,0,0.05);
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .stat-badge:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.07) !important;
    }
    .section-title {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #52796f;
    }
    .aprendiz-row:hover { background: #f6fcf8; }
</style>
@endpush

@section('dashboard-content')
@php
    $isActive = ($ficha->status === 'Activo' || empty($ficha->status));
    $program  = $ficha->program;
@endphp

<div class="container-fluid px-2 px-md-3 py-2">

    {{-- Breadcrumb --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.administrador.inicio') }}" class="text-decoration-none" style="color: var(--ecp-green);">
                            <i class="fas fa-house me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.administrador.fichas') }}" class="text-decoration-none" style="color: var(--ecp-green);">
                            Fichas
                        </a>
                    </li>
                    <li class="breadcrumb-item active fw-bold" style="color: var(--ecp-navy);">
                        Ficha {{ $ficha->code }}
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: var(--ecp-navy); font-family: 'Outfit', sans-serif;">
                <i class="fas fa-id-card-clip me-2" style="color: var(--ecp-green);"></i>
                Ficha <span style="color: var(--ecp-green);">{{ $ficha->code }}</span>
            </h2>
            <p class="text-muted small mb-0">Control ECP &bull; {{ $program ? $program->name : 'Programa no registrado' }}</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            {{-- HU5: Solo lectura — no hay botón "Editar" --}}
            <button type="button" class="btn btn-outline-info rounded-pill px-3 btn-sm"
                    onclick="sincronizarAprendicesFicha({{ $ficha->id }}, '{{ $ficha->code }}')">
                <i class="fas fa-arrows-rotate me-1"></i> Sincronizar Aprendices
            </button>
            @if($isActive)
                <button type="button" class="btn btn-outline-danger rounded-pill px-3 btn-sm"
                        onclick="confirmarCambioEstadoFicha({{ $ficha->id }}, 'desactivar', '{{ $ficha->code }}')">
                    <i class="fas fa-ban me-1"></i> Desactivar Ficha
                </button>
            @else
                <button type="button" class="btn btn-outline-success rounded-pill px-3 btn-sm"
                        onclick="confirmarCambioEstadoFicha({{ $ficha->id }}, 'activar', '{{ $ficha->code }}')">
                    <i class="fas fa-circle-check me-1"></i> Activar Ficha
                </button>
            @endif
            <a href="{{ route('controlecp.administrador.fichas') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    {{-- Alertas Flash --}}
    <div id="liveAlertFichasBox"></div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" style="border-radius: 14px;" role="alert">
            <i class="fas fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">

        {{-- Columna izquierda: Info de la ficha --}}
        <div class="col-lg-4">

            {{-- Card info principal --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 18px;">
                <div class="card-body p-4">
                    <p class="section-title mb-3"><i class="fas fa-id-card me-2"></i>Información de la Ficha</p>

                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0"
                             style="width: 60px; height: 60px; background: #e8f8e8; color: var(--ecp-green); font-size: 1.5rem;">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark" style="font-size: 1.4rem; font-family: 'Outfit', sans-serif;">{{ $ficha->code }}</div>
                            <div>
                                @if($isActive)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                        <i class="fas fa-circle-check me-1"></i> Activa
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1">
                                        <i class="fas fa-ban me-1"></i> Inactiva
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <table class="table table-sm mb-0" style="font-size: 0.85rem;">
                        <tr>
                            <td class="text-muted fw-semibold py-2" style="width: 42%;">Programa</td>
                            <td class="fw-bold text-dark py-2">{{ $program ? $program->name : 'Sin programa' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold py-2">Tipo</td>
                            <td class="py-2">{{ $program ? ($program->program_type ?? 'Tecnólogo') : '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold py-2">Modalidad</td>
                            <td class="py-2">{{ $program ? ($program->training_type ?: 'Presencial') : '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold py-2">Inicio Lectiva</td>
                            <td class="py-2">{{ $ficha->start_date ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold py-2">Fin Lectiva</td>
                            <td class="py-2">{{ $ficha->end_date ?? '—' }}</td>
                        </tr>
                        @if($ficha->start_production_date)
                        <tr>
                            <td class="text-muted fw-semibold py-2">Inicio Productiva</td>
                            <td class="py-2">{{ $ficha->start_production_date }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Card estadísticas de aprendices --}}
            <div class="card border-0 shadow-sm" style="border-radius: 18px;">
                <div class="card-body p-4">
                    <p class="section-title mb-3"><i class="fas fa-users me-2"></i>Resumen de Aprendices</p>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="stat-badge shadow-sm text-center">
                                <div class="fw-bold" style="font-size: 1.6rem; color: var(--ecp-navy);">{{ $statsAprendices['total'] }}</div>
                                <div class="small text-muted">Total</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-badge shadow-sm text-center">
                                <div class="fw-bold text-primary" style="font-size: 1.6rem;">{{ $statsAprendices['en_formacion'] }}</div>
                                <div class="small text-muted">En Formación</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-badge shadow-sm text-center">
                                <div class="fw-bold text-success" style="font-size: 1.6rem;">{{ $statsAprendices['certificados'] }}</div>
                                <div class="small text-muted">Certificados</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-badge shadow-sm text-center">
                                <div class="fw-bold text-danger" style="font-size: 1.6rem;">{{ $statsAprendices['retirados'] }}</div>
                                <div class="small text-muted">Retirados</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Columna derecha: Aprendices --}}
        <div class="col-lg-8">

            {{-- HU2: Nómina de aprendices --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 18px;">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0" style="color: var(--ecp-navy); font-size: 1rem;">
                        <i class="fas fa-users-viewfinder me-2" style="color: var(--ecp-green);"></i>
                        Nómina de Aprendices Matriculados
                    </h5>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                        {{ $statsAprendices['total'] }} aprendices
                    </span>
                </div>
                <div class="card-body p-0">
                    @if($ficha->apprentices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0" style="font-size: 0.85rem;">
                                <thead class="table-light" style="font-size: 0.78rem; text-transform: uppercase; color: #52796f;">
                                    <tr>
                                        <th class="ps-4 py-2">Documento</th>
                                        <th>Nombre</th>
                                        <th>Correo</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ficha->apprentices as $a)
                                        @php
                                            $p = $a->person;
                                            $status = $a->apprentice_status ?? 'EN FORMACIÓN';
                                            $statusColor = match($status) {
                                                'CERTIFICADO'          => 'success',
                                                'EN FORMACIÓN'         => 'primary',
                                                'RETIRO VOLUNTARIO',
                                                'CANCELADO'            => 'danger',
                                                'APLAZADO',
                                                'CONDICIONADO'         => 'warning',
                                                default                => 'secondary',
                                            };
                                        @endphp
                                        <tr class="aprendiz-row">
                                            <td class="ps-4">
                                                @if($p)
                                                    <span class="badge bg-light text-dark border" style="font-size: 0.72rem;">{{ $p->document_type }}</span>
                                                    <strong>{{ $p->document_number }}</strong>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="fw-semibold text-dark">
                                                {{ $p ? "{$p->first_name} {$p->first_last_name} {$p->second_last_name}" : 'Sin datos' }}
                                            </td>
                                            <td>
                                                @if($p && ($p->misena_email || $p->personal_email))
                                                    <a href="mailto:{{ $p->misena_email ?: $p->personal_email }}" class="text-decoration-none text-muted small">
                                                        <i class="far fa-envelope me-1 text-success"></i>{{ $p->misena_email ?: $p->personal_email }}
                                                    </a>
                                                @else
                                                    <span class="text-muted small">No registra</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }} border border-{{ $statusColor }}-subtle rounded-pill px-2 py-1" style="font-size: 0.72rem;">
                                                    {{ $status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-users-slash fa-2x mb-2"></i>
                            <p class="mb-2 small">Esta ficha no tiene aprendices vinculados aún.</p>
                            <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3"
                                    onclick="sincronizarAprendicesFicha({{ $ficha->id }}, '{{ $ficha->code }}')">
                                <i class="fas fa-arrows-rotate me-1"></i> Sincronizar desde SICEFA
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            {{-- HU2: Momentos certificados y actividades finalizadas (indicadores) --}}
            <div class="card border-0 shadow-sm" style="border-radius: 18px;">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
                    <h5 class="fw-bold mb-0" style="color: var(--ecp-navy); font-size: 1rem;">
                        <i class="fas fa-dove me-2" style="color: var(--ecp-green);"></i>
                        Progreso en Control ECP
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        {{-- Momentos certificados --}}
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 border" style="background: #f8faf9;">
                                <p class="section-title mb-2"><i class="fas fa-award me-1"></i>Momentos Certificados</p>
                                <div class="d-flex align-items-end gap-2">
                                    <span class="fw-bold" style="font-size: 2rem; color: var(--ecp-navy);">
                                        {{ $statsAprendices['certificados'] }}
                                    </span>
                                    <span class="text-muted small mb-1">/ {{ $statsAprendices['total'] }} aprendices</span>
                                </div>
                                <div class="progress mt-2" style="height: 6px; border-radius: 6px;">
                                    @php
                                        $pctCert = $statsAprendices['total'] > 0
                                            ? round(($statsAprendices['certificados'] / $statsAprendices['total']) * 100)
                                            : 0;
                                    @endphp
                                    <div class="progress-bar bg-success" style="width: {{ $pctCert }}%;"></div>
                                </div>
                                <small class="text-muted mt-1 d-block">{{ $pctCert }}% certificados</small>
                            </div>
                        </div>

                        {{-- Actividades finalizadas --}}
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 border" style="background: #f8faf9;">
                                <p class="section-title mb-2"><i class="fas fa-list-check me-1"></i>Actividades Finalizadas</p>
                                <div class="d-flex align-items-end gap-2">
                                    <span class="fw-bold" style="font-size: 2rem; color: var(--ecp-navy);">
                                        {{ $statsAprendices['en_formacion'] }}
                                    </span>
                                    <span class="text-muted small mb-1">aprendices activos</span>
                                </div>
                                <div class="progress mt-2" style="height: 6px; border-radius: 6px;">
                                    @php
                                        $pctForm = $statsAprendices['total'] > 0
                                            ? round(($statsAprendices['en_formacion'] / $statsAprendices['total']) * 100)
                                            : 0;
                                    @endphp
                                    <div class="progress-bar bg-primary" style="width: {{ $pctForm }}%;"></div>
                                </div>
                                <small class="text-muted mt-1 d-block">{{ $pctForm }}% en formación activa</small>
                            </div>
                        </div>

                        {{-- Nota de solo lectura HU5 --}}
                        <div class="col-12">
                            <div class="d-flex align-items-start gap-2 p-3 rounded-3" style="background: #fef9e8; border: 1px dashed #f0c040;">
                                <i class="fas fa-lock text-warning mt-1 flex-shrink-0"></i>
                                <div class="small text-dark">
                                    <strong>Módulo de solo lectura.</strong> La información de esta ficha proviene exclusivamente de <strong>SICEFA / SOFIA Plus</strong>. No es posible crear ni editar fichas manualmente. Para actualizar la nómina de aprendices, usa el botón <em>"Sincronizar Aprendices"</em>.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

{{-- Reutilizar modales de fichas.blade.php vía JS inline --}}
<!-- Modal: Confirmación Activar / Desactivar Ficha (HU6 / HU7 / HU8) -->
<div class="modal fade" id="modalCambioEstadoFicha" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4 text-center justify-content-center position-relative">
                <button type="button" class="btn-close position-absolute end-0 top-0 mt-3 me-3" data-bs-dismiss="modal"></button>
                <div id="fichaEstadoModalIcon" class="rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                     style="width: 65px; height: 65px; font-size: 1.8rem;">
                    <i class="fas fa-ban"></i>
                </div>
            </div>
            <div class="modal-body p-4 text-center">
                <h5 class="fw-bold mb-2" id="fichaEstadoModalTitle" style="color: var(--ecp-navy);">Desactivar Ficha</h5>
                <p class="text-muted small mb-4" id="fichaEstadoModalDesc"></p>
                <div class="d-flex justify-content-center gap-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn rounded-pill px-4 text-white" id="btnConfirmarEstadoFicha" onclick="ejecutarCambioEstadoFicha()">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Sincronizar Aprendices (HU4 / HU8 / HU9) -->
<div class="modal fade" id="modalSyncAprendices" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4 text-center justify-content-center position-relative">
                <button type="button" class="btn-close position-absolute end-0 top-0 mt-3 me-3" data-bs-dismiss="modal"></button>
                <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                     style="width: 65px; height: 65px; background: #ebf5fb; color: #2980b9; font-size: 1.8rem;">
                    <i class="fas fa-arrows-rotate" id="syncAprendicezIcon"></i>
                </div>
            </div>
            <div class="modal-body p-4 text-center">
                <h5 class="fw-bold mb-1" style="color: var(--ecp-navy);">Sincronizar Aprendices</h5>
                <p class="text-muted small mb-3">Ficha: <strong id="syncAprendicesCodigoLabel">--</strong></p>
                <p class="text-muted small mb-3">Se obtendrá la nómina desde SICEFA, agregando nuevos aprendices y actualizando los existentes por documento, sin duplicar.</p>

                <div id="syncAprendicesProgress" class="d-none text-start mb-3">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span id="syncAprendicesLabel">Consultando SICEFA...</span>
                        <span id="syncAprendicesPct">0%</span>
                    </div>
                    <div class="progress" style="height: 8px; border-radius: 8px;">
                        <div id="syncAprendicesBar" class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width: 0%;"></div>
                    </div>
                </div>

                <div id="syncAprendicesResult" class="d-none small text-start p-3 rounded-3 border mb-3"></div>

                <div class="d-flex justify-content-center gap-3" id="syncAprendicesBtns">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn rounded-pill px-4 text-white" id="btnEjecutarSyncAprendices"
                            style="background-color: #2980b9;" onclick="ejecutarSyncAprendices()">
                        <i class="fas fa-arrows-rotate me-1"></i> Sincronizar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const fichaShowCsrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // ── Confirmación activar / desactivar (HU6 / HU7 / HU8) ─────────────────────────
    let _fichaEstadoId    = null;
    let _fichaEstadoAccion = null;

    function confirmarCambioEstadoFicha(fichaId, accion, codigo) {
        _fichaEstadoId    = fichaId;
        _fichaEstadoAccion = accion;

        const icon  = document.getElementById('fichaEstadoModalIcon');
        const title = document.getElementById('fichaEstadoModalTitle');
        const desc  = document.getElementById('fichaEstadoModalDesc');
        const btn   = document.getElementById('btnConfirmarEstadoFicha');

        if (accion === 'desactivar') {
            icon.style.background = '#fdecea';
            icon.style.color      = '#c0392b';
            icon.innerHTML        = '<i class="fas fa-ban"></i>';
            title.innerText       = 'Desactivar Ficha';
            desc.innerText        = `¿Desactivar la ficha ${codigo}? Se retirará de las fichas activas. No se puede desactivar si tiene aprendices EN FORMACIÓN.`;
            btn.style.background  = '#dc3545';
            btn.style.borderColor = '#dc3545';
        } else {
            icon.style.background = '#e8f8e8';
            icon.style.color      = '#1e7000';
            icon.innerHTML        = '<i class="fas fa-circle-check"></i>';
            title.innerText       = 'Activar Ficha';
            desc.innerText        = `¿Reactivar la ficha ${codigo}? Volverá a aparecer entre las fichas activas.`;
            btn.style.background  = '#39A900';
            btn.style.borderColor = '#39A900';
        }

        new bootstrap.Modal(document.getElementById('modalCambioEstadoFicha')).show();
    }

    async function ejecutarCambioEstadoFicha() {
        const accion = _fichaEstadoAccion === 'desactivar' ? 'desactivar' : 'activar';
        const url    = "{{ url('/control-ecp/administrador/fichas') }}/" + _fichaEstadoId + '/' + accion;

        try {
            const resp = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': fichaShowCsrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({})
            });
            const data = await resp.json();

            bootstrap.Modal.getInstance(document.getElementById('modalCambioEstadoFicha')).hide();

            if (data.success) {
                mostrarAlertaFichas(data.message, 'success');
                setTimeout(() => { window.location.reload(); }, 1000);
            } else {
                mostrarAlertaFichas(data.message, 'warning');
            }
        } catch (e) {
            mostrarAlertaFichas('Error al cambiar el estado: ' + e.message, 'danger');
        }
    }

    // ── Sincronizar aprendices (HU4 / HU8 / HU9) ─────────────────────────────────────
    let _syncAprendicesFichaId = null;

    function sincronizarAprendicesFicha(fichaId, codigo) {
        _syncAprendicesFichaId = fichaId;
        document.getElementById('syncAprendicesCodigoLabel').innerText = codigo;

        document.getElementById('syncAprendicesProgress').classList.add('d-none');
        document.getElementById('syncAprendicesResult').classList.add('d-none');
        document.getElementById('syncAprendicesBtns').classList.remove('d-none');
        document.getElementById('btnEjecutarSyncAprendices').disabled = false;
        document.getElementById('syncAprendicezIcon').className = 'fas fa-arrows-rotate';

        new bootstrap.Modal(document.getElementById('modalSyncAprendices')).show();
    }

    async function ejecutarSyncAprendices() {
        const progressDiv = document.getElementById('syncAprendicesProgress');
        const progressBar = document.getElementById('syncAprendicesBar');
        const progressLbl = document.getElementById('syncAprendicesLabel');
        const progressPct = document.getElementById('syncAprendicesPct');
        const resultDiv   = document.getElementById('syncAprendicesResult');
        const btns        = document.getElementById('syncAprendicesBtns');
        const icon        = document.getElementById('syncAprendicezIcon');
        const btn         = document.getElementById('btnEjecutarSyncAprendices');

        progressDiv.classList.remove('d-none');
        resultDiv.classList.add('d-none');
        btns.classList.add('d-none');
        btn.disabled = true;
        icon.className = 'fas fa-arrows-rotate fa-spin';

        let pct = 10;
        progressBar.style.width = pct + '%';
        progressPct.innerText   = pct + '%';
        progressLbl.innerText   = 'Consultando SICEFA...';

        const timer = setInterval(() => {
            if (pct < 80) {
                pct += 15;
                progressBar.style.width = pct + '%';
                progressPct.innerText   = pct + '%';
                if (pct === 40) progressLbl.innerText = 'Validando documentos...';
                if (pct === 70) progressLbl.innerText = 'Guardando aprendices...';
            }
        }, 350);

        try {
            const url = "{{ url('/control-ecp/administrador/fichas') }}/" + _syncAprendicesFichaId + "/sync-aprendices";
            const resp = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': fichaShowCsrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({})
            });

            clearInterval(timer);
            const data = await resp.json();

            progressBar.style.width = '100%';
            progressPct.innerText   = '100%';
            progressLbl.innerText   = data.success ? '¡Completado!' : 'Error';

            resultDiv.classList.remove('d-none');
            btns.classList.remove('d-none');
            icon.className = data.success ? 'fas fa-circle-check' : 'fas fa-triangle-exclamation';

            if (data.success) {
                progressBar.classList.remove('bg-info');
                progressBar.classList.add('bg-success');
                resultDiv.className = 'd-block small text-start p-3 rounded-3 border mb-3 bg-success-subtle border-success-subtle';
                resultDiv.innerHTML = `
                    <div class="fw-bold text-success mb-1"><i class="fas fa-circle-check me-1"></i> ${data.message}</div>
                    <div class="text-muted">Nuevos: <strong>${data.created}</strong> &bull; Actualizados: <strong>${data.updated}</strong></div>
                `;
                setTimeout(() => { window.location.reload(); }, 2000);
            } else {
                progressBar.classList.remove('bg-info');
                progressBar.classList.add('bg-danger');
                resultDiv.className = 'd-block small text-start p-3 rounded-3 border mb-3 bg-danger-subtle border-danger-subtle';
                resultDiv.innerHTML = `<div class="fw-bold text-danger"><i class="fas fa-triangle-exclamation me-1"></i> ${data.message}</div>`;
            }

        } catch (e) {
            clearInterval(timer);
            progressBar.classList.add('bg-danger');
            progressLbl.innerText = 'Sin conexión con SICEFA';
            resultDiv.classList.remove('d-none');
            btns.classList.remove('d-none');
            resultDiv.className = 'd-block small text-start p-3 rounded-3 border mb-3 bg-danger-subtle border-danger-subtle';
            resultDiv.innerHTML = `<div class="fw-bold text-danger"><i class="fas fa-wifi me-1"></i> No fue posible consultar SICEFA: ${e.message}. La información existente se conserva sin cambios.</div>`;
        }
    }

    // ── Alertas ───────────────────────────────────────────────────────────────────────
    function mostrarAlertaFichas(mensaje, tipo = 'success') {
        const container = document.getElementById('liveAlertFichasBox');
        if (!container) return;
        const icon = tipo === 'success' ? 'fa-circle-check' : (tipo === 'info' ? 'fa-circle-info' : 'fa-triangle-exclamation');
        container.innerHTML = `
            <div class="alert alert-${tipo} alert-dismissible fade show border-0 shadow-sm mb-3" role="alert" style="border-radius: 14px;">
                <i class="fas ${icon} me-2"></i> ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        setTimeout(() => {
            const alert = container.querySelector('.alert');
            if (alert) { alert.classList.remove('show'); setTimeout(() => { container.innerHTML = ''; }, 300); }
        }, 5000);
    }
</script>
@endpush
