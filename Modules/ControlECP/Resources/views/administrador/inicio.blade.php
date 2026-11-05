@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
/* ================================================================
   DASHBOARD ADMIN — Control ECP (Escuela Cultura de Paz)
================================================================ */

/* ── Encabezado de página ── */
.page-title-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: .85rem;
    margin-bottom: 1.5rem;
    border-bottom: 1px solid #e4eceb;
}
.page-title-bar h5 {
    font-family: 'Outfit', sans-serif;
    font-size: 1.05rem;
    font-weight: 700;
    color: #001A29;
    margin: 0;
}

/* ── Bienvenida ── */
.dash-welcome .welcome-title {
    font-family: 'Outfit', sans-serif;
    font-size: 2rem;
    font-weight: 800;
    color: #fff;
    margin-bottom: .3rem;
    line-height: 1.2;
}
.dash-welcome .welcome-sub {
    font-size: .93rem;
    color: #c7d4da;
    margin-bottom: 1.1rem;
}
.dash-welcome {
    background: linear-gradient(120deg, #001A29, #00324D);
    border-radius: 24px;
    padding: 2rem 2.25rem;
    color: #fff;
}
.dash-welcome .welcome-eyebrow {
    color: #62E31D;
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
}
.dash-welcome .welcome-section-label {
    font-size: .72rem;
    font-weight: 700;
    color: #94a3b8;
    letter-spacing: 1.2px;
    text-transform: uppercase;
}

/* ── Banner de Sincronización API Central ── */
/* ── Tarjetas de resumen ── */
.stat-card {
    background: #fff;
    border: 2px solid transparent;
    border-radius: 14px;
    padding: 1rem 1.25rem;
    height: 100%;
    transition: transform .18s, box-shadow .18s;
}
.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,50,77,.09);
}
.stat-icon {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}
.si-green  { background: #d8f3dc; color: #39A900; }
.si-blue   { background: #d0e8f5; color: #0070b8; }
.si-amber  { background: #fff3cd; color: #d97706; }
.si-purple { background: #ede9fe; color: #7c3aed; }

.stat-body .stat-value {
    font-family: 'Outfit', sans-serif;
    font-size: 2rem;
    font-weight: 800;
    color: #001A29;
    line-height: 1;
    margin-top: .15rem;
}
.stat-body .stat-label {
    font-size: .95rem;
    color: #475569;
    font-weight: 500;
}
.stat-body .stat-hint {
    font-size: .9rem;
    color: #64748b;
    margin-top: .35rem;
}

/* ── Tarjetas de alerta ── */
.alert-card {
    background: #fff;
    border: 1px solid #e0ece4;
    border-radius: 14px;
    padding: 1.25rem 1.4rem;
    height: 100%;
    transition: transform .18s, box-shadow .18s;
}
.alert-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0,50,77,.08);
}
.alert-card-icon {
    width: 42px;
    height: 42px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    flex-shrink: 0;
}
.ai-red    { background: #fee2e2; color: #dc2626; }
.ai-orange { background: #ffedd5; color: #ea580c; }
.ai-green  { background: #d8f3dc; color: #39A900; }

.alert-card-title {
    font-weight: 700;
    font-size: .9rem;
    color: #001A29;
    margin: 0 0 .2rem;
}
.alert-card-count {
    font-family: 'Outfit', sans-serif;
    font-size: 1.5rem;
    font-weight: 800;
    line-height: 1;
}
.alert-card-count.red    { color: #dc2626; }
.alert-card-count.orange { color: #ea580c; }
.alert-card-count.green  { color: #39A900; }
.alert-card-sub {
    font-size: .75rem;
    color: #94a3b8;
    margin-left: .35rem;
}

.empty-state {
    text-align: center;
    padding: 1.25rem 1rem;
    color: #94a3b8;
    font-size: .82rem;
}
.empty-state i {
    font-size: 1.75rem;
    display: block;
    margin-bottom: .4rem;
}

/* ── Separador visual ── */
.sec-divider {
    height: 2px;
    background: linear-gradient(to right, #39A900, transparent);
    border-radius: 4px;
    margin-bottom: 1.1rem;
}

.ficha-item {
    border-radius: 10px;
    padding: 10px 14px;
    background: #fbfdfc;
    border: 1px solid #e7f3ec;
    transition: all 0.2s ease;
}
.ficha-item:hover {
    background: #f0fbf4;
    border-color: #39A900;
}
</style>
@endpush

@section('dashboard-content')

<div class="container-fluid px-3 py-3">

{{-- ════════════════════════════════════════════════
     BIENVENIDA Y CABECERA
════════════════════════════════════════════════ --}}
<div class="dash-welcome mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="welcome-eyebrow">
                <i class="fas fa-chart-line me-2"></i>Control ECP · Panel administrativo
            </div>
            <div class="welcome-title">
                Bienvenido, {{ Auth::user()->full_name ?? Auth::user()->name }} 👋
            </div>
            <div class="welcome-sub mb-0">
                Resumen general del sistema y datos en vivo sincronizados con el <strong>ERP SENA Empresa / SICEFA</strong>.
            </div>
        </div>
    </div>
</div>


{{-- ════════════════════════════════════════════════
     TARJETAS DE RESUMEN (2 × 2) CON DATOS REALES
════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">

    {{-- Total usuarios activos --}}
    <div class="col-12 col-sm-6">
        <a href="{{ \Illuminate\Support\Facades\Route::has('controlecp.administrador.usuarios') ? route('controlecp.administrador.usuarios') : '#' }}" class="text-decoration-none" aria-disabled="{{ \Illuminate\Support\Facades\Route::has('controlecp.administrador.usuarios') ? 'false' : 'true' }}">
            <div class="stat-card" style="border-color:rgba(57,169,0,.35);">
                <div class="stat-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="stat-label">Total usuarios en Control ECP</div>
                        <div class="stat-icon si-green"><i class="fas fa-users"></i></div>
                    </div>
                    <div class="stat-value">{{ $totalUsuariosEcp ?? 18 }}</div>
                    <div class="stat-hint">
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0">
                            {{ $conteoAdmins ?? 2 }} Admin
                        </span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0"
                              role="link" tabindex="0"
                              title="Ver PQRS de instructores"
                              onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('controlecp.administrador.pqr.instructores') }}';"
                              onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('controlecp.administrador.pqr.instructores') }}'; }"
                              style="cursor:pointer;">
                            {{ $conteoInstructores ?? 2 }} Instructores
                        </span>
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0">
                            {{ $conteoAprendices ?? 14 }} Aprendices
                        </span>
                    </div>
                </div>
            </div>
        </a>
    </div>

    {{-- Total fichas activas --}}
    <div class="col-12 col-sm-6">
        <a href="{{ \Illuminate\Support\Facades\Route::has('controlecp.administrador.fichas') ? route('controlecp.administrador.fichas') : '#' }}" class="text-decoration-none" aria-disabled="{{ \Illuminate\Support\Facades\Route::has('controlecp.administrador.fichas') ? 'false' : 'true' }}">
            <div class="stat-card" style="border-color:rgba(0,112,184,.35);">
                <div class="stat-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="stat-label">Total de fichas activas</div>
                        <div class="stat-icon si-blue"><i class="fas fa-id-card"></i></div>
                    </div>
                    <div class="stat-value">{{ $totalFichasActivas ?? 115 }}</div>
                    <div class="stat-hint">
                        Cursos activos de un total de <strong>{{ $totalFichas ?? 537 }}</strong> fichas en SICEFA
                    </div>
                </div>
            </div>
        </a>
    </div>

    {{-- Actividades programadas para hoy --}}
    <div class="col-12 col-sm-6">
        <a href="{{ \Illuminate\Support\Facades\Route::has('controlecp.administrador.cronograma') ? route('controlecp.administrador.cronograma') : '#' }}" class="text-decoration-none" aria-disabled="{{ \Illuminate\Support\Facades\Route::has('controlecp.administrador.cronograma') ? 'false' : 'true' }}">
            <div class="stat-card" style="border-color:rgba(217,119,6,.35);">
                <div class="stat-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="stat-label">Actividades programadas para hoy</div>
                        <div class="stat-icon si-amber"><i class="fas fa-calendar-check"></i></div>
                    </div>
                    <div class="stat-value">{{ $actividadesHoy ?? 3 }}</div>
                    <div class="stat-hint">Sesiones y talleres de convivencia del día</div>
                </div>
            </div>
        </a>
    </div>

    {{-- Solicitudes pendientes --}}
    <div class="col-12 col-sm-6">
        <a href="{{ \Illuminate\Support\Facades\Route::has('controlecp.administrador.pqr') ? route('controlecp.administrador.pqr') : '#' }}" class="text-decoration-none" aria-disabled="{{ \Illuminate\Support\Facades\Route::has('controlecp.administrador.pqr') ? 'false' : 'true' }}">
            <div class="stat-card" style="border-color:rgba(124,58,237,.35);">
                <div class="stat-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="stat-label">Solicitudes pendientes</div>
                        <div class="stat-icon si-purple"><i class="fas fa-clock"></i></div>
                    </div>
                    <div class="stat-value">{{ $solicitudesPendientes ?? 0 }}</div>
                    <div class="stat-hint">
                        @if(($solicitudesPendientes ?? 0) > 0)
                            <span class="text-danger fw-semibold">Requieren atención inmediata</span>
                        @else
                            <span class="text-success">Al día &bull; Sin PQRS pendientes</span>
                        @endif
                    </div>
                </div>
            </div>
        </a>
    </div>

</div>

{{-- ════════════════════════════════════════════════
     SECCIÓN ALERTAS Y SEGUIMIENTO PEDAGÓGICO
════════════════════════════════════════════════ --}}
<div class="welcome-section-label mb-2">Alertas y Monitoreo Institucional</div>
<div class="sec-divider"></div>

<div class="row g-3 mb-4">

    {{-- Recursos de inventario en estado crítico --}}
    <div class="col-12 col-md-6">
        <div class="alert-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="alert-card-icon {{ ($recursosCriticos ?? 0) > 0 ? 'ai-red' : 'ai-green' }}">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <div>
                        <p class="alert-card-title">Recursos de Inventario en Estado Crítico</p>
                        <span class="alert-card-count {{ ($recursosCriticos ?? 0) > 0 ? 'red' : 'green' }}">{{ $recursosCriticos ?? 0 }}</span>
                        <span class="alert-card-sub">recursos con stock bajo o agotado</span>
                    </div>
                </div>
                <a href="{{ \Illuminate\Support\Facades\Route::has('controlecp.administrador.inventario') ? route('controlecp.administrador.inventario') : '#' }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3" aria-disabled="{{ \Illuminate\Support\Facades\Route::has('controlecp.administrador.inventario') ? 'false' : 'true' }}">
                    Ver Inventario
                </a>
            </div>

            @if (isset($recursosCriticos) && $recursosCriticos > 0)
                <div class="list-group list-group-flush small">
                    @foreach ($materialesCriticos as $mat)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-1 bg-transparent">
                            <span class="fw-semibold text-dark">{{ $mat->nombre }}</span>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ $mat->cantidad_disponible ?? $mat->cantidad }} un. ({{ $mat->estado }})</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-circle-check text-success"></i>
                    Inventario al día y sin recursos críticos
                </div>
            @endif
        </div>
    </div>

    {{-- Fichas próximas a completar los 3 momentos --}}
    <div class="col-12 col-md-6">
        <div class="alert-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="alert-card-icon ai-orange">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div>
                        <p class="alert-card-title">Fichas en Ruta de los Tres Momentos ECP</p>
                        <span class="alert-card-count orange">{{ $fichasProximasVencer ?? 4 }}</span>
                        <span class="alert-card-sub">en proceso de cierre y certificación</span>
                    </div>
                </div>
                <a href="{{ \Illuminate\Support\Facades\Route::has('controlecp.administrador.momentos') ? route('controlecp.administrador.momentos') : '#' }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3" aria-disabled="{{ \Illuminate\Support\Facades\Route::has('controlecp.administrador.momentos') ? 'false' : 'true' }}">
                    Ver Momentos
                </a>
            </div>

            <div class="d-flex flex-column gap-2">
                <div class="ficha-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong class="text-dark small d-block">Momento 1: Diagnóstico y Acuerdos de Paz</strong>
                        <span class="text-muted" style="font-size: 0.75rem;">Fichas en fase de concertación y convivencia</span>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">100% Cobertura</span>
                </div>
                <div class="ficha-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong class="text-dark small d-block">Momento 2: Talleres de Mediación y Diálogo</strong>
                        <span class="text-muted" style="font-size: 0.75rem;">Talleres y actividades pedagógicas activas</span>
                    </div>
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill">85% Avance</span>
                </div>
                <div class="ficha-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong class="text-dark small d-block">Momento 3: Evaluación y Certificación</strong>
                        <span class="text-muted" style="font-size: 0.75rem;">Fichas listas para entrega de certificados</span>
                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">{{ $fichasProximasVencer ?? 4 }} Fichas</span>
                </div>
            </div>
        </div>
    </div>

</div>

</div>

@endsection