@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    :root {
        --ecp-green: #39A900;
        --ecp-navy:  #001A29;
    }

    /* ── Tarjeta de Momento ── */
    .momento-card {
        border-radius: 20px;
        background: #fff;
        box-shadow: 0 6px 24px rgba(0,26,41,.07);
        border: 1px solid rgba(0,0,0,.04);
        transition: transform .22s ease, box-shadow .22s ease;
        overflow: hidden;
    }
    .momento-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 34px rgba(0,26,41,.12);
    }
    .momento-card.inactivo {
        opacity: .65;
        filter: grayscale(.4);
    }

    /* ── KPI strip ── */
    .kpi-m {
        border-radius: 16px;
        background: #fff;
        border: 1px solid rgba(0,0,0,.05);
        padding: 1rem 1.25rem;
        box-shadow: 0 4px 16px rgba(0,26,41,.06);
        transition: transform .18s;
    }
    .kpi-m:hover { transform: translateY(-2px); }

    /* ── Actividad item en el listado del modal ── */
    .act-list-item {
        border-left: 4px solid #e0ece4;
        background: #f8faf9;
        border-radius: 8px;
        padding: .55rem .85rem;
        transition: all .18s;
        cursor: default;
    }
    .act-list-item:hover {
        background: #f0fbf4;
        border-left-color: var(--ecp-green);
    }
    .act-list-item.inactiva {
        opacity: .6;
        background: #f5f5f5;
        border-left-color: #ccc;
    }

    /* ── Badge estado ── */
    .badge-activo   { background: #e8f8e8; color: #1e7000; border: 1px solid #c2eec2; }
    .badge-inactivo { background: #f5f5f5; color: #666;    border: 1px solid #ddd; }

    /* ── Colores del formulario ── */
    .color-swatch {
        width: 28px; height: 28px; border-radius: 8px; cursor: pointer;
        border: 2px solid transparent; transition: border-color .15s, transform .15s;
        flex-shrink: 0;
    }
    .color-swatch:hover, .color-swatch.selected { border-color: var(--ecp-navy); transform: scale(1.12); }

    /* ── Icon picker ── */
    .icon-btn {
        width: 38px; height: 38px; border-radius: 10px; font-size: .95rem;
        border: 2px solid #e0e5ec; background: #f8fafc; color: #536070;
        cursor: pointer; transition: all .15s; display: flex; align-items: center; justify-content: center;
    }
    .icon-btn:hover, .icon-btn.selected { border-color: var(--ecp-green); background: #f0fbf4; color: var(--ecp-green); }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">

    {{-- Breadcrumb & Cabecera --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.administrador.inicio') }}" class="text-decoration-none" style="color: var(--ecp-green);">
                            <i class="fas fa-house me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item active fw-bold" style="color: var(--ecp-navy);">Momentos</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: var(--ecp-navy); font-family: 'Outfit', sans-serif;">
                <i class="fas fa-star me-2" style="color: var(--ecp-green);"></i>Gestión de Momentos Pedagógicos
            </h2>
            <p class="text-muted small mb-0">Control ECP &bull; Escuela Cultura de Paz &bull; {{ $stats['total_momentos'] }} momentos registrados</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            {{-- HU1: Botón crear momento --}}
            <button type="button" class="btn text-white rounded-pill px-3 btn-sm shadow-sm"
                    style="background-color: var(--ecp-green);"
                    onclick="abrirModalCrear()">
                <i class="fas fa-plus me-1"></i> Crear Momento
            </button>
            <a href="{{ route('controlecp.administrador.actividades') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
                <i class="fas fa-list-check me-1"></i> Catálogo de Actividades
            </a>
            <a href="{{ route('controlecp.administrador.inicio') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    {{-- Alertas Flash --}}
    <div id="alertMomentos"></div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" style="border-radius: 14px;" role="alert">
            <i class="fas fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- KPIs --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="kpi-m">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted fw-semibold">Total Momentos</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width: 34px; height: 34px; background: #e8f8e8; color: var(--ecp-green);">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0" style="color: var(--ecp-navy);">{{ $stats['total_momentos'] }}</h3>
                <small class="text-muted">Pedagógicos ECP</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-m">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted fw-semibold">Activos</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width: 34px; height: 34px; background: #e8f8e8; color: #1e7000;">
                        <i class="fas fa-circle-check"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-success">{{ $stats['activos'] }}</h3>
                <small class="text-muted">Disponibles</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-m">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted fw-semibold">Total Fichas</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width: 34px; height: 34px; background: #ebf5fb; color: #2980b9;">
                        <i class="fas fa-id-card"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-primary">{{ $stats['total_fichas'] }}</h3>
                <small class="text-muted">Fichas activas</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-m">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted fw-semibold">Aprendices</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width: 34px; height: 34px; background: #fef9e7; color: #d68910;">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-dark">{{ number_format($stats['total_aprendices']) }}</h3>
                <small class="text-muted">En el centro</small>
            </div>
        </div>
    </div>

    {{-- HU5: Grilla de tarjetas de momentos --}}
    @if($momentos->count() > 0)
        <div class="row g-4" id="gridMomentos">
            @foreach($momentos as $m)
                @php
                    $isActivo = $m->estado === 'Activo';
                    // Gradiente de cabecera basado en el color del momento
                    $gradient = "linear-gradient(135deg, var(--ecp-navy) 0%, {$m->color} 100%)";
                @endphp
                <div class="col-12 col-md-6 col-lg-4" id="card-momento-{{ $m->id }}">
                    <div class="momento-card h-100 {{ !$isActivo ? 'inactivo' : '' }}">

                        {{-- Header con gradiente del color del momento --}}
                        <div class="p-4" style="background: {{ $gradient }}; color: #fff;">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 42px; height: 42px; background: rgba(255,255,255,.2); font-size: 1.1rem;">
                                        <i class="fas {{ $m->icono }}"></i>
                                    </div>
                                    <span class="badge rounded-pill fw-bold px-3 py-1 small"
                                          style="background: rgba(255,255,255,.2);">
                                        Momento {{ str_pad($m->orden, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                </div>
                                {{-- Badge de estado --}}
                                @if($isActivo)
                                    <span class="badge badge-activo rounded-pill px-2 py-1" style="font-size: .72rem;">
                                        <i class="fas fa-circle-check me-1"></i> Activo
                                    </span>
                                @else
                                    <span class="badge badge-inactivo rounded-pill px-2 py-1" style="font-size: .72rem;">
                                        <i class="fas fa-ban me-1"></i> Inactivo
                                    </span>
                                @endif
                            </div>
                            <h4 class="fw-bold mb-1 fs-5">{{ $m->nombre }}</h4>
                            <p class="mb-0 small" style="opacity: .8; line-height: 1.4;">
                                {{ Str::limit($m->descripcion, 100) }}
                            </p>
                        </div>

                        {{-- Cuerpo: cantidad de actividades --}}
                        <div class="p-4 d-flex flex-column justify-content-between flex-fill">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <span class="small text-muted fw-semibold text-uppercase" style="letter-spacing: .05em;">
                                        <i class="fas fa-list-check me-1" style="color: {{ $m->color }};"></i>
                                        Actividades
                                    </span>
                                    <div class="d-flex gap-2">
                                        <span class="badge rounded-pill px-3 py-1 fw-bold"
                                              style="background: {{ $m->color }}22; color: {{ $m->color }}; font-size: .82rem; border: 1px solid {{ $m->color }}44;">
                                            {{ $m->actividades_activas_count }} activas
                                        </span>
                                        <span class="badge bg-light text-muted border rounded-pill px-2 py-1 fw-bold" style="font-size: .82rem;">
                                            {{ $m->actividades_count }} total
                                        </span>
                                    </div>
                                </div>

                                {{-- HU6: Botón para ver actividades del momento --}}
                                <button type="button"
                                        class="btn w-100 rounded-pill btn-sm mb-3 fw-semibold"
                                        style="background: {{ $m->color }}15; color: {{ $m->color }}; border: 1.5px solid {{ $m->color }}44;"
                                        onclick="verActividadesMomento({{ $m->id }}, '{{ addslashes($m->nombre) }}', '{{ $m->color }}', '{{ $m->icono }}')">
                                    <i class="fas fa-eye me-1"></i>
                                    Ver {{ $m->actividades_count }} actividades
                                </button>
                            </div>

                            {{-- Acciones --}}
                            <div class="d-flex gap-2 pt-3 border-top">
                                {{-- HU2: Editar --}}
                                <button type="button" class="btn btn-outline-secondary rounded-pill btn-sm flex-fill"
                                        onclick="abrirModalEditar({{ json_encode(['id'=>$m->id,'nombre'=>$m->nombre,'descripcion'=>$m->descripcion,'icono'=>$m->icono,'color'=>$m->color,'orden'=>$m->orden]) }})">
                                    <i class="fas fa-pen me-1"></i> Editar
                                </button>

                                {{-- HU3 / HU4: Desactivar / Activar --}}
                                @if($isActivo)
                                    <button type="button" class="btn btn-outline-danger rounded-pill btn-sm flex-fill"
                                            onclick="confirmarEstadoMomento({{ $m->id }}, 'desactivar', '{{ addslashes($m->nombre) }}')">
                                        <i class="fas fa-ban me-1"></i> Desactivar
                                    </button>
                                @else
                                    <button type="button" class="btn btn-outline-success rounded-pill btn-sm flex-fill"
                                            onclick="confirmarEstadoMomento({{ $m->id }}, 'activar', '{{ addslashes($m->nombre) }}')">
                                        <i class="fas fa-circle-check me-1"></i> Activar
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        {{-- Estado vacío --}}
        <div class="text-center py-5">
            <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3"
                 style="width: 80px; height: 80px; background: #f0fbf4; color: var(--ecp-green); font-size: 2.2rem;">
                <i class="fas fa-star-half-stroke"></i>
            </div>
            <h5 class="fw-bold text-dark mb-2">Aún no hay momentos registrados</h5>
            <p class="text-muted small mb-4">Crea el primer momento pedagógico o ejecuta el seeder para cargar los 3 momentos base de la Escuela Cultura de Paz.</p>
            <button type="button" class="btn text-white rounded-pill px-4 shadow-sm" style="background-color: var(--ecp-green);" onclick="abrirModalCrear()">
                <i class="fas fa-plus me-1"></i> Crear Primer Momento
            </button>
        </div>
    @endif

</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- MODALES                                                         --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}

{{-- Modal: Crear / Editar Momento (HU1, HU2, HU7) --}}
<div class="modal fade" id="modalFormMomento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width: 42px; height: 42px; background: #e8f8e8; color: var(--ecp-green);">
                        <i class="fas fa-star" id="formModalIcon"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="formModalTitle" style="color: var(--ecp-navy);">Crear Momento</h5>
                        <p class="text-muted small mb-0">Escuela Cultura de Paz &bull; Control ECP</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formMomento" novalidate>
                    <input type="hidden" id="formMomentoId" value="">

                    {{-- HU7: Validación visual --}}
                    <div id="formAlertValidacion" class="alert alert-danger border-0 d-none" style="border-radius: 12px; font-size: .88rem;">
                        <i class="fas fa-triangle-exclamation me-2"></i>
                        <span id="formAlertMsg"></span>
                    </div>

                    <div class="row g-3">
                        {{-- Nombre --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold small" style="color: var(--ecp-navy);">
                                Nombre del Momento <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="formNombre" class="form-control"
                                   placeholder="Ej: Autoconciencia, Convivencia, Resolución..."
                                   maxlength="120" required>
                            <div class="invalid-feedback">El nombre es obligatorio.</div>
                        </div>

                        {{-- Descripción --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold small" style="color: var(--ecp-navy);">Descripción</label>
                            <textarea id="formDescripcion" class="form-control" rows="3" maxlength="1000"
                                      placeholder="Describe el propósito pedagógico de este momento..."></textarea>
                        </div>

                        {{-- Color e Ícono --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small" style="color: var(--ecp-navy);">Color del Momento</label>
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                @php
                                    $colores = ['#39A900','#0070B8','#6A1B9A','#E65100','#00838F','#c0392b','#8e44ad','#2c3e50'];
                                @endphp
                                @foreach($colores as $hex)
                                    <div class="color-swatch" data-color="{{ $hex }}"
                                         style="background: {{ $hex }};"
                                         onclick="seleccionarColor('{{ $hex }}')"></div>
                                @endforeach
                                <input type="color" id="colorCustom" value="#39A900"
                                       class="form-control form-control-color border-0 rounded-3"
                                       style="width: 32px; height: 28px; padding: 0; cursor: pointer;"
                                       oninput="seleccionarColor(this.value)" title="Color personalizado">
                                <input type="hidden" id="formColor" value="#39A900">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small" style="color: var(--ecp-navy);">Ícono</label>
                            <div class="d-flex flex-wrap gap-2">
                                @php
                                    $iconos = [
                                        'fa-star','fa-brain','fa-people-holding-hands','fa-scale-balanced',
                                        'fa-dove','fa-heart','fa-leaf','fa-sun','fa-hands-holding-heart',
                                        'fa-shield-halved','fa-lightbulb','fa-seedling',
                                    ];
                                @endphp
                                @foreach($iconos as $ic)
                                    <button type="button" class="icon-btn" data-icon="{{ $ic }}"
                                            onclick="seleccionarIcono('{{ $ic }}')" title="{{ $ic }}">
                                        <i class="fas {{ $ic }}"></i>
                                    </button>
                                @endforeach
                                <input type="hidden" id="formIcono" value="fa-star">
                            </div>
                        </div>

                        {{-- Orden --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small" style="color: var(--ecp-navy);">Orden de presentación</label>
                            <input type="number" id="formOrden" class="form-control" min="0" max="99" value="0"
                                   placeholder="0">
                        </div>

                        {{-- Preview en vivo --}}
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small" style="color: var(--ecp-navy);">Vista previa</label>
                            <div id="previewMomento" class="rounded-3 p-3 text-white d-flex align-items-center gap-3"
                                 style="background: linear-gradient(135deg, var(--ecp-navy) 0%, #39A900 100%); min-height: 64px;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                     style="width: 40px; height: 40px; background: rgba(255,255,255,.2); font-size: 1.1rem; flex-shrink: 0;">
                                    <i class="fas fa-star" id="previewIcono"></i>
                                </div>
                                <div>
                                    <div class="fw-bold" id="previewNombre">Nombre del Momento</div>
                                    <div class="small" style="opacity: .8;" id="previewDesc">Descripción...</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 mt-3 border-top">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="button" class="btn text-white rounded-pill px-4" id="btnGuardarMomento"
                                style="background-color: var(--ecp-green);" onclick="guardarMomento()">
                            <i class="fas fa-floppy-disk me-1"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Confirmar activar / desactivar (HU3 / HU4 / HU8) --}}
<div class="modal fade" id="modalConfirmEstado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4 text-center justify-content-center position-relative">
                <button type="button" class="btn-close position-absolute end-0 top-0 mt-3 me-3" data-bs-dismiss="modal"></button>
                <div id="confirmEstadoIcon" class="rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                     style="width: 65px; height: 65px; font-size: 1.8rem;"></div>
            </div>
            <div class="modal-body p-4 text-center">
                <h5 class="fw-bold mb-2" id="confirmEstadoTitle" style="color: var(--ecp-navy);"></h5>
                <p class="text-muted small mb-4" id="confirmEstadoDesc"></p>
                <div class="d-flex justify-content-center gap-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn rounded-pill px-4 text-white" id="btnConfirmEstado" onclick="ejecutarCambioEstado()">Confirmar</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Ver actividades del momento (HU6) --}}
<div class="modal fade" id="modalActividades" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-3">
                    <div id="actModalIconWrap" class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width: 42px; height: 42px; background: #e8f8e8; color: var(--ecp-green); font-size: 1.1rem;">
                        <i class="fas fa-star" id="actModalIcono"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="actModalTitle" style="color: var(--ecp-navy);">Actividades del Momento</h5>
                        <p class="text-muted small mb-0" id="actModalSubtitle">Cargando...</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="actModalLoading" class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-success mb-2"></i>
                    <div class="small text-muted">Cargando actividades...</div>
                </div>
                <div id="actModalContent" class="d-none">
                    <div id="actModalList" class="d-flex flex-column gap-2"></div>
                </div>
                <div id="actModalEmpty" class="d-none text-center py-4 text-muted">
                    <i class="fas fa-list-check fa-2x mb-2"></i>
                    <p class="small mb-0">Este momento no tiene actividades registradas aún.</p>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 btn-sm" data-bs-dismiss="modal">Cerrar</button>
                <a href="{{ route('controlecp.administrador.actividades') }}"
                   class="btn text-white rounded-pill px-4 btn-sm" style="background-color: var(--ecp-navy);">
                    <i class="fas fa-list-check me-1"></i> Ver Catálogo Completo
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // ── Estado global ─────────────────────────────────────────────────────────────
    let _estadoMomentoId     = null;
    let _estadoMomentoAccion = null;

    // ── HU1: Abrir modal para crear momento ───────────────────────────────────────
    function abrirModalCrear() {
        document.getElementById('formMomentoId').value    = '';
        document.getElementById('formNombre').value       = '';
        document.getElementById('formDescripcion').value  = '';
        document.getElementById('formOrden').value        = {{ $stats['total_momentos'] + 1 }};

        seleccionarColor('#39A900');
        seleccionarIcono('fa-star');
        actualizarPreview();

        document.getElementById('formModalTitle').textContent = 'Crear Momento';
        document.getElementById('formAlertValidacion').classList.add('d-none');
        document.querySelectorAll('#formMomento .is-invalid').forEach(e => e.classList.remove('is-invalid'));

        new bootstrap.Modal(document.getElementById('modalFormMomento')).show();
    }

    // ── HU2: Abrir modal para editar momento ──────────────────────────────────────
    function abrirModalEditar(data) {
        document.getElementById('formMomentoId').value    = data.id;
        document.getElementById('formNombre').value       = data.nombre;
        document.getElementById('formDescripcion').value  = data.descripcion ?? '';
        document.getElementById('formOrden').value        = data.orden ?? 0;

        seleccionarColor(data.color ?? '#39A900');
        seleccionarIcono(data.icono ?? 'fa-star');
        actualizarPreview();

        document.getElementById('formModalTitle').textContent = 'Editar Momento';
        document.getElementById('formAlertValidacion').classList.add('d-none');
        document.querySelectorAll('#formMomento .is-invalid').forEach(e => e.classList.remove('is-invalid'));

        new bootstrap.Modal(document.getElementById('modalFormMomento')).show();
    }

    // ── HU1 / HU2: Guardar momento (crear o actualizar) ───────────────────────────
    async function guardarMomento() {
        const id     = document.getElementById('formMomentoId').value.trim();
        const nombre = document.getElementById('formNombre').value.trim();
        const desc   = document.getElementById('formDescripcion').value.trim();
        const icono  = document.getElementById('formIcono').value;
        const color  = document.getElementById('formColor').value;
        const orden  = parseInt(document.getElementById('formOrden').value) || 0;

        // HU7 — Validar campos obligatorios en el front
        if (!nombre) {
            const input = document.getElementById('formNombre');
            input.classList.add('is-invalid');
            mostrarAlertaForm('El nombre del momento es obligatorio. Por favor completa este campo.');
            input.focus();
            return;
        }
        document.getElementById('formNombre').classList.remove('is-invalid');
        document.getElementById('formAlertValidacion').classList.add('d-none');

        const btn = document.getElementById('btnGuardarMomento');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';

        const isEdit = id !== '';
        const url    = isEdit
            ? `{{ url('/control-ecp/administrador/momentos') }}/${id}`
            : "{{ route('controlecp.administrador.momentos.store') }}";
        const method = isEdit ? 'PUT' : 'POST';

        try {
            const resp = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ nombre, descripcion: desc, icono, color, orden }),
            });

            const data = await resp.json();

            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-floppy-disk me-1"></i> Guardar';

            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalFormMomento')).hide();
                // HU8 — Mensaje de confirmación
                mostrarAlerta(data.message, 'success');
                setTimeout(() => window.location.reload(), 900);
            } else {
                // HU7 — Alerta de error de validación del servidor
                mostrarAlertaForm(data.message ?? 'Error al guardar. Verifica los campos.');
            }
        } catch (e) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-floppy-disk me-1"></i> Guardar';
            mostrarAlertaForm('Error de conexión: ' + e.message);
        }
    }

    // ── HU3 / HU4: Confirmar cambio de estado ────────────────────────────────────
    function confirmarEstadoMomento(id, accion, nombre) {
        _estadoMomentoId    = id;
        _estadoMomentoAccion = accion;

        const icon  = document.getElementById('confirmEstadoIcon');
        const title = document.getElementById('confirmEstadoTitle');
        const desc  = document.getElementById('confirmEstadoDesc');
        const btn   = document.getElementById('btnConfirmEstado');

        if (accion === 'desactivar') {
            icon.style.background = '#fdecea';
            icon.style.color      = '#c0392b';
            icon.innerHTML        = '<i class="fas fa-ban"></i>';
            title.innerText       = 'Desactivar Momento';
            desc.innerText        = `¿Desactivar "${nombre}"? El momento dejará de aparecer en las opciones activas, pero se conserva su información y registros históricos.`;
            btn.style.background  = '#dc3545';
            btn.style.borderColor = '#dc3545';
        } else {
            icon.style.background = '#e8f8e8';
            icon.style.color      = '#1e7000';
            icon.innerHTML        = '<i class="fas fa-circle-check"></i>';
            title.innerText       = 'Activar Momento';
            desc.innerText        = `¿Reactivar "${nombre}"? El momento volverá a estar disponible.`;
            btn.style.background  = '#39A900';
            btn.style.borderColor = '#39A900';
        }

        new bootstrap.Modal(document.getElementById('modalConfirmEstado')).show();
    }

    async function ejecutarCambioEstado() {
        const accion = _estadoMomentoAccion === 'desactivar' ? 'desactivar' : 'activar';
        const url    = `{{ url('/control-ecp/administrador/momentos') }}/${_estadoMomentoId}/${accion}`;

        try {
            const resp = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({}),
            });
            const data = await resp.json();

            bootstrap.Modal.getInstance(document.getElementById('modalConfirmEstado')).hide();

            if (data.success) {
                // HU8 — Confirmación de operación
                mostrarAlerta(data.message, 'success');
                setTimeout(() => window.location.reload(), 900);
            } else {
                mostrarAlerta(data.message, 'warning');
            }
        } catch (e) {
            mostrarAlerta('Error al cambiar el estado: ' + e.message, 'danger');
        }
    }

    // ── HU6: Ver actividades del momento ─────────────────────────────────────────
    async function verActividadesMomento(id, nombre, color, icono) {
        // Configurar modal
        document.getElementById('actModalTitle').innerText   = nombre;
        document.getElementById('actModalSubtitle').innerText = 'Cargando actividades...';
        document.getElementById('actModalIcono').className   = 'fas ' + icono;
        document.getElementById('actModalIconWrap').style.background = color + '22';
        document.getElementById('actModalIconWrap').style.color      = color;

        document.getElementById('actModalLoading').classList.remove('d-none');
        document.getElementById('actModalContent').classList.add('d-none');
        document.getElementById('actModalEmpty').classList.add('d-none');

        new bootstrap.Modal(document.getElementById('modalActividades')).show();

        try {
            const url  = `{{ url('/control-ecp/administrador/momentos') }}/${id}/actividades`;
            const resp = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const data = await resp.json();

            document.getElementById('actModalLoading').classList.add('d-none');

            if (data.success) {
                document.getElementById('actModalSubtitle').innerText =
                    `${data.total_actividades} actividades pedagógicas`;

                if (data.actividades.length > 0) {
                    const list = document.getElementById('actModalList');
                    list.innerHTML = data.actividades.map(a => `
                        <div class="act-list-item ${a.estado === 'Inactivo' ? 'inactiva' : ''}">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div style="min-width:0;">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="fw-bold small rounded-2 px-2 py-0"
                                              style="background: ${color}22; color: ${color}; font-size: .72rem;">
                                            ${a.numero}
                                        </span>
                                        <span class="fw-semibold text-dark small">${a.nombre}</span>
                                    </div>
                                    ${a.objetivo ? `<p class="text-muted mb-0" style="font-size:.8rem;line-height:1.4;">${a.objetivo}</p>` : ''}
                                </div>
                                <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0">
                                    <span class="badge bg-light text-muted border rounded-pill" style="font-size:.72rem;">
                                        <i class="far fa-clock me-1"></i>${a.duracion}
                                    </span>
                                    <span class="badge rounded-pill" style="font-size:.68rem;
                                          background: ${a.estado==='Activo'?'#e8f8e8':'#f5f5f5'};
                                          color: ${a.estado==='Activo'?'#1e7000':'#666'};
                                          border: 1px solid ${a.estado==='Activo'?'#c2eec2':'#ddd'};">
                                        ${a.estado === 'Activo' ? '<i class="fas fa-circle-check me-1"></i>' : '<i class="fas fa-ban me-1"></i>'}
                                        ${a.estado}
                                    </span>
                                </div>
                            </div>
                        </div>
                    `).join('');

                    document.getElementById('actModalContent').classList.remove('d-none');
                } else {
                    document.getElementById('actModalEmpty').classList.remove('d-none');
                }
            }
        } catch (e) {
            document.getElementById('actModalLoading').classList.add('d-none');
            document.getElementById('actModalEmpty').classList.remove('d-none');
            document.getElementById('actModalEmpty').innerHTML =
                `<i class="fas fa-triangle-exclamation fa-2x mb-2 text-danger"></i><p class="small text-danger mb-0">Error al cargar: ${e.message}</p>`;
        }
    }

    // ── Utilidades de UI ──────────────────────────────────────────────────────────
    function seleccionarColor(hex) {
        document.getElementById('formColor').value = hex;
        document.getElementById('colorCustom').value = hex;
        document.querySelectorAll('.color-swatch').forEach(s => {
            s.classList.toggle('selected', s.dataset.color === hex);
        });
        actualizarPreview();
    }

    function seleccionarIcono(ic) {
        document.getElementById('formIcono').value = ic;
        document.querySelectorAll('.icon-btn').forEach(b => {
            b.classList.toggle('selected', b.dataset.icon === ic);
        });
        actualizarPreview();
    }

    function actualizarPreview() {
        const nombre = document.getElementById('formNombre').value || 'Nombre del Momento';
        const desc   = document.getElementById('formDescripcion').value || 'Descripción...';
        const color  = document.getElementById('formColor').value || '#39A900';
        const icono  = document.getElementById('formIcono').value || 'fa-star';

        const preview = document.getElementById('previewMomento');
        preview.style.background = `linear-gradient(135deg, var(--ecp-navy) 0%, ${color} 100%)`;

        document.getElementById('previewIcono').className = 'fas ' + icono;
        document.getElementById('previewNombre').textContent = nombre;
        document.getElementById('previewDesc').textContent   = desc.substring(0, 80) + (desc.length > 80 ? '...' : '');
    }

    // Actualizar preview en tiempo real al escribir
    document.getElementById('formNombre')?.addEventListener('input', actualizarPreview);
    document.getElementById('formDescripcion')?.addEventListener('input', actualizarPreview);

    function mostrarAlertaForm(msg) {
        const el = document.getElementById('formAlertValidacion');
        document.getElementById('formAlertMsg').textContent = msg;
        el.classList.remove('d-none');
    }

    function mostrarAlerta(msg, tipo = 'success') {
        const el   = document.getElementById('alertMomentos');
        if (!el) return;
        const icon = tipo === 'success' ? 'fa-circle-check' : (tipo === 'info' ? 'fa-circle-info' : 'fa-triangle-exclamation');
        el.innerHTML = `
            <div class="alert alert-${tipo} alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 14px;">
                <i class="fas ${icon} me-2"></i> ${msg}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        setTimeout(() => {
            const a = el.querySelector('.alert');
            if (a) { a.classList.remove('show'); setTimeout(() => { el.innerHTML = ''; }, 300); }
        }, 5000);
    }

    // Inicializar primera selección de color e ícono al cargar
    document.addEventListener('DOMContentLoaded', () => {
        seleccionarColor('#39A900');
        seleccionarIcono('fa-star');
    });
</script>
@endpush
