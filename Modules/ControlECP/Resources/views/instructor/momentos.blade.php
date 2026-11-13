@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    :root {
        --ecp-green: #39A900;
        --ecp-navy: #001A29;
    }

    .momentos-header-card {
        padding: 1.25rem 1.5rem;
        border-radius: 24px;
        background: var(--ecp-navy);
    }
    .momentos-header-card h1 { color: #fff; }
    .momentos-header-card p { color: #c7d4da; }

    .momento-card {
        overflow: hidden;
        border: 1px solid rgba(0, 0, 0, .04);
        border-radius: 20px;
        background: #fff;
        box-shadow: 0 6px 24px rgba(0, 26, 41, .07);
        transition: transform .22s ease, box-shadow .22s ease;
    }
    .momento-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 34px rgba(0, 26, 41, .12);
    }
    .momento-card.inactivo { opacity: .7; filter: grayscale(.25); }
    .momento-card-toggle {
        display: block;
        width: 100%;
        border: 0;
        padding: 0;
        background: transparent;
        text-align: left;
    }
    .momento-card-toggle:focus-visible {
        outline: 3px solid var(--ecp-green);
        outline-offset: -3px;
    }
    .momento-card-activity {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) minmax(0, 1.5fr) auto;
        align-items: center;
        gap: .5rem .75rem;
        padding: .65rem .85rem;
        border-left: 4px solid #e0ece4;
        border-radius: 8px;
        background: #f8faf9;
    }
    .momento-card-activity-name,
    .momento-card-activity-description {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .momento-card-activity-name { font-weight: 600; color: #212529; }
    .momento-card-activity-description { color: #64748b; font-size: .875rem; }
    .momento-card-activity-status {
        display: inline-flex;
        align-items: center;
        padding: .2rem .55rem;
        border: 1px solid #a7f3d0;
        border-radius: 999px;
        background: #d1fae5;
        color: #047857;
        font-size: .75rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .momento-card-activity-status--inactive {
        border-color: #fecaca;
        background: #fee2e2;
        color: #b91c1c;
    }
    .momento-card-activity + .momento-card-activity { margin-top: .55rem; }
    .momento-card-activity.inactiva {
        border-left-color: #ccc;
        background: #f5f5f5;
        opacity: .7;
    }
    .momento-card-activity-number {
        color: var(--ecp-navy);
        font-size: .75rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .momento-activities-modal {
        overflow: hidden;
        border: 0;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 26, 41, .24);
    }
    .momento-activities-modal .modal-header {
        align-items: center;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e8eef2;
        background: #fff;
    }
    .momento-activities-modal .modal-title {
        display: flex;
        align-items: center;
        gap: .7rem;
        color: #00324d;
    }
    .momento-activities-title-icon {
        display: inline-flex;
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        align-items: center;
        justify-content: center;
        border-radius: 13px;
        background: #edf8e8;
        color: #318b16;
    }
    .momento-activities-heading { min-width: 0; }
    .momento-activities-heading small {
        display: block;
        margin-top: .12rem;
        color: #718096;
        font-size: .76rem;
        font-weight: 500;
    }
    .momento-activities-modal .modal-body {
        display: flex;
        flex-direction: column;
        gap: .65rem;
        padding: 1.1rem 1.25rem 1.25rem;
        background: #f7fafb;
    }
    .momento-activities-modal .momento-card-activity {
        min-height: 54px;
        grid-template-columns: auto minmax(0, 1fr) minmax(0, 1.25fr) auto;
        gap: .6rem .8rem;
        padding: .7rem .85rem;
        border: 1px solid #e7eef0;
        border-left: 4px solid #b9dfc5;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0, 26, 41, .035);
        transition: border-color .16s ease, box-shadow .16s ease, transform .16s ease;
    }
    .momento-activities-modal .momento-card-activity:hover {
        transform: translateY(-1px);
        border-color: #d6e7dc;
        box-shadow: 0 5px 14px rgba(0, 26, 41, .07);
    }
    .momento-activities-modal .momento-card-activity.inactiva {
        border-left-color: #e4b7b7;
        background: #fff;
        opacity: .8;
    }
    .momento-activities-modal .momento-card-activity-number {
        display: inline-flex;
        min-width: 34px;
        height: 30px;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        background: #f0f6f3;
        color: #31566b;
    }
    .momento-activities-modal .momento-card-activity-name {
        color: #243746;
        font-weight: 700;
    }
    .momento-activities-modal .momento-card-activity-description {
        color: #647b8a;
        line-height: 1.4;
    }
    .momento-activities-modal .momento-card-activity-status {
        padding: .3rem .65rem;
        border-color: #b7ebd2;
        background: #e7f8ef;
        color: #087c59;
    }
    .momento-activities-modal .momento-card-activity-status--inactive {
        border-color: #f3c7c7;
        background: #fff0f0;
        color: #b42332;
    }
    .momento-activities-modal .btn-close {
        padding: .65rem;
        border-radius: 50%;
        background-color: #f1f5f7;
        opacity: 1;
    }
    @media (max-width: 575.98px) {
        .momento-activities-modal .modal-header { padding: .9rem 1rem; }
        .momento-activities-modal .modal-body { gap: .55rem; padding: .85rem 1rem 1rem; }
        .momento-activities-modal .momento-card-activity {
            grid-template-columns: auto minmax(0, 1fr) auto;
            gap: .4rem .6rem;
            padding: .65rem;
        }
        .momento-activities-modal .momento-card-activity-description {
            grid-column: 2;
            white-space: normal;
        }
        .momento-activities-modal .momento-card-activity-status {
            grid-column: 3;
            grid-row: 1 / span 2;
        }
    }
    @media (max-width: 420px) {
        .momento-card-activity {
            grid-template-columns: auto minmax(0, 1fr) auto;
            gap: .35rem .65rem;
        }
        .momento-card-activity-number { grid-column: 1; grid-row: 1; }
        .momento-card-activity-name,
        .momento-card-activity-description { grid-column: 2; }
        .momento-card-activity-status { grid-column: 3; grid-row: 1 / span 2; }
    }
    .kpi-m {
        height: 100%;
        padding: 1rem 1.25rem;
        border: 1px solid rgba(0, 0, 0, .05);
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 4px 16px rgba(0, 26, 41, .06);
        transition: transform .22s ease, box-shadow .22s ease;
    }
    .kpi-m:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 26px rgba(0, 26, 41, .12);
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-3 py-3">
    @php
        $totalMomentos = $momentos->count();
        $momentosActivos = $momentos->where('estado', 'Activo')->count();
        $momentosInactivos = $totalMomentos - $momentosActivos;
        $totalActividades = $momentos->sum(fn ($momento) => $momento->actividades->count());
    @endphp

    <header class="momentos-header-card d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small fw-semibold mb-1" style="color:var(--ecp-green);">
                <i class="fas fa-star me-2"></i>Control ECP · Momentos pedagógicos
            </div>
            <h1 class="h3 fw-bold mb-1" style="font-family:'Outfit',sans-serif;">
                <i class="fas fa-star me-2" style="color:var(--ecp-green);"></i>Momentos Pedagógicos
            </h1>
            <p class="small mb-0">Consulta la organización del programa y las actividades de cada momento.</p>
        </div>
        <a href="{{ route('controlecp.instructor.actividades') }}"
           class="btn btn-outline-light rounded-pill px-3 btn-sm">
            <i class="fas fa-list-check me-1"></i>Catálogo de actividades
        </a>
    </header>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="kpi-m" style="border:2px solid rgba(57,169,0,.35);">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted fw-semibold">Total Momentos</span>
                    <span class="rounded-circle d-flex align-items-center justify-content-center"
                          style="width:34px;height:34px;background:#e8f8e8;color:var(--ecp-green);">
                        <i class="fas fa-star"></i>
                    </span>
                </div>
                <div class="h3 fw-bold mb-0" style="color:var(--ecp-navy);">{{ $totalMomentos }}</div>
                <small class="text-muted">Pedagógicos ECP</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-m" style="border:2px solid rgba(30,112,0,.35);">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted fw-semibold">Activos</span>
                    <span class="rounded-circle d-flex align-items-center justify-content-center"
                          style="width:34px;height:34px;background:#e8f8e8;color:#1e7000;">
                        <i class="fas fa-circle-check"></i>
                    </span>
                </div>
                <div class="h3 fw-bold mb-0 text-success">{{ $momentosActivos }}</div>
                <small class="text-muted">Disponibles</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-m" style="border:2px solid rgba(192,57,43,.35);">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted fw-semibold">Inactivos</span>
                    <span class="rounded-circle d-flex align-items-center justify-content-center"
                          style="width:34px;height:34px;background:#fdecea;color:#c0392b;">
                        <i class="fas fa-ban"></i>
                    </span>
                </div>
                <div class="h3 fw-bold mb-0 text-danger">{{ $momentosInactivos }}</div>
                <small class="text-muted">Momentos inactivos</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-m" style="border:2px solid rgba(106,27,154,.35);">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted fw-semibold">Actividades totales</span>
                    <span class="rounded-circle d-flex align-items-center justify-content-center"
                          style="width:34px;height:34px;background:#f4ecf7;color:#6a1b9a;">
                        <i class="fas fa-list-ol"></i>
                    </span>
                </div>
                <div class="h3 fw-bold mb-0" style="color:#6a1b9a;">{{ $totalActividades }}</div>
                <small class="text-muted">En todos los momentos</small>
            </div>
        </div>
    </div>

    @if($momentos->isNotEmpty())
        <div class="row g-4">
            @foreach($momentos as $momento)
                @php
                    $color = $momento->color ?: '#39A900';
                    $icono = $momento->icono ?: 'fa-star';
                    $actividades = $momento->actividades;
                    $modalId = 'modal-actividades-momento-' . $momento->id;
                    $isActivo = $momento->estado === 'Activo';
                @endphp
                <div class="col-12 col-md-6 col-lg-4">
                    <article class="momento-card h-100 {{ $isActivo ? '' : 'inactivo' }}">
                        <button class="momento-card-toggle" type="button"
                            data-bs-toggle="modal" data-bs-target="#{{ $modalId }}"
                            aria-haspopup="dialog" aria-controls="{{ $modalId }}">
                            <div class="p-4" style="background:linear-gradient(135deg,var(--ecp-navy) 0%,{{ $color }} 100%);color:#fff;">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="rounded-circle d-flex align-items-center justify-content-center"
                                              style="width:42px;height:42px;background:rgba(255,255,255,.2);font-size:1.1rem;">
                                            <i class="fas {{ $icono }}"></i>
                                        </span>
                                        <span class="badge rounded-pill fw-bold px-3 py-1 small" style="background:rgba(255,255,255,.2);">
                                            Momento {{ str_pad((string)$momento->orden, 2, '0', STR_PAD_LEFT) }}
                                        </span>
                                    </div>
                                    <span class="badge rounded-pill px-2 py-1 {{ $isActivo ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        <i class="fas {{ $isActivo ? 'fa-circle-check' : 'fa-ban' }} me-1"></i>{{ $momento->estado }}
                                    </span>
                                </div>
                                <h2 class="h5 fw-bold mb-1" style="color:#fff;">{{ $momento->nombre }}</h2>
                                <p class="mb-0 small" style="color:#fff;opacity:.92;line-height:1.5;">
                                    {{ $momento->descripcion ?: 'Este momento no tiene descripción registrada.' }}
                                </p>
                            </div>
                        </button>

                        <div class="p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="small text-muted fw-semibold text-uppercase" style="letter-spacing:.05em;">
                                    <i class="fas fa-list-check me-1" style="color:{{ $color }};"></i>Actividades
                                </span>
                                <span class="badge bg-light text-muted border rounded-pill px-3 py-2 fw-bold">
                                    {{ $actividades->count() }} {{ $actividades->count() === 1 ? 'actividad' : 'actividades' }}
                                </span>
                            </div>
                        </div>
                    </article>
                    <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}-label" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content momento-activities-modal">
                                <div class="modal-header">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <h2 class="modal-title fs-5 fw-bold" id="{{ $modalId }}-label">
                                            <span class="momento-activities-title-icon"><i class="fas {{ $momento->icono ?: 'fa-star' }}" aria-hidden="true"></i></span>
                                            <span class="momento-activities-heading">
                                                Momento {{ str_pad((string)$momento->orden, 2, '0', STR_PAD_LEFT) }} · {{ $momento->nombre }}
                                                <small>Actividades de este momento</small>
                                            </span>
                                        </h2>
                                        <span class="momento-card-activity-status {{ $isActivo ? '' : 'momento-card-activity-status--inactive' }}">{{ $momento->estado }}</span>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    @forelse($actividades as $actividad)
                                        @php
                                            $descripcionActividad = $actividad->objetivo ?: $actividad->descripcion ?: 'Sin descripción registrada.';
                                            $actividadEsExitosa = in_array($actividad->estado, ['Activo', 'Finalizada'], true);
                                            $actividadEstaInactiva = $actividad->estado === 'Inactivo';
                                        @endphp
                                        <div class="momento-card-activity {{ $actividadEsExitosa ? '' : 'inactiva' }}">
                                            @if($actividad->numero)
                                                <span class="momento-card-activity-number">#{{ $actividad->numero }}</span>
                                            @endif
                                            <span class="momento-card-activity-name" title="{{ $actividad->nombre }}">{{ $actividad->nombre }}</span>
                                            <span class="momento-card-activity-description" title="{{ $descripcionActividad }}">{{ $descripcionActividad }}</span>
                                            <span class="momento-card-activity-status {{ $actividadEsExitosa ? '' : 'momento-card-activity-status--inactive' }}">
                                                {{ $actividadEstaInactiva ? 'Inactivo' : $actividad->estado }}
                                            </span>
                                        </div>
                                    @empty
                                        <p class="small text-muted mb-0">Este momento aún no tiene actividades.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-5">
            <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3"
                 style="width:80px;height:80px;background:#f0fbf4;color:var(--ecp-green);font-size:2rem;">
                <i class="fas fa-star-half-stroke"></i>
            </div>
            <h2 class="h5 fw-bold text-dark mb-2">No hay momentos registrados</h2>
            <p class="text-muted small mb-0">Los momentos del programa aparecerán aquí cuando estén disponibles.</p>
        </div>
    @endif
</div>
@endsection
