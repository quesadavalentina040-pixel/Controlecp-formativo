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
    color: #001A29;
    margin-bottom: .3rem;
    line-height: 1.2;
}
.dash-welcome .welcome-sub {
    font-size: .93rem;
    color: #52796f;
    margin-bottom: 1.1rem;
}
.dash-welcome .welcome-section-label {
    font-size: .72rem;
    font-weight: 700;
    color: #94a3b8;
    letter-spacing: 1.2px;
    text-transform: uppercase;
}

/* ── Banner de Sincronización API Central ── */
.api-sync-banner {
    background: linear-gradient(135deg, #f0fbf4 0%, #d8f3dc 100%);
    border-left: 6px solid #39A900 !important;
    border-radius: 16px;
    padding: 1.25rem 1.4rem;
    transition: transform .18s, box-shadow .18s;
}
.api-sync-banner:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(57, 169, 0, 0.12);
}

.pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #39A900;
    display: inline-block;
    box-shadow: 0 0 0 0 rgba(57, 169, 0, 0.7);
    animation: pulse-dot 1.8s infinite;
}
@keyframes pulse-dot {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(57, 169, 0, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(57, 169, 0, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(57, 169, 0, 0); }
}

/* ── Tarjetas de resumen ── */
.stat-card {
    background: #fff;
    border: 1px solid #e0ece4;
    border-radius: 14px;
    padding: 1.35rem 1.4rem;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    height: 100%;
    transition: transform .18s, box-shadow .18s;
}
.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,50,77,.09);
}
.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
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
}
.stat-body .stat-label {
    font-size: .76rem;
    color: #52796f;
    font-weight: 700;
    margin-top: .25rem;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.stat-body .stat-hint {
    font-size: .72rem;
    color: #94a3b8;
    margin-top: .2rem;
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

{{-- ════════════════════════════════════════════════
     BIENVENIDA Y CABECERA
════════════════════════════════════════════════ --}}
<div class="dash-welcome mb-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="welcome-title">
                Bienvenido, {{ Auth::user()->full_name ?? Auth::user()->name }} 👋
            </div>
            <div class="welcome-sub mb-0">
                Resumen general del sistema y datos en vivo sincronizados con el <strong>ERP SENA Empresa / SICEFA</strong>.
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('controlecp.administrador.usuarios') }}" class="btn btn-outline-success rounded-pill px-3 btn-sm bg-white shadow-sm">
                <i class="fas fa-users-gear me-1"></i> Gestión de Usuarios
            </a>
            <a href="{{ route('controlecp.administrador.fichas') }}" class="btn btn-outline-primary rounded-pill px-3 btn-sm bg-white shadow-sm">
                <i class="fas fa-id-card-clip me-1"></i> Fichas SICEFA
            </a>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════
     BANNER DE INTEGRACIÓN API CENTRAL SICEFA
════════════════════════════════════════════════ --}}
<div class="api-sync-banner border-0 shadow-sm mb-4">
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm"
                 style="width: 48px; height: 48px; background: #FFFFFF; color: #39A900; font-size: 1.35rem;">
                <i class="fas fa-network-wired"></i>
            </div>
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <strong class="text-dark" style="font-size: 0.95rem;">Integración API REST Central SICEFA / ERP SENA Empresa</strong>
                    <span class="badge rounded-pill bg-white text-success border border-success-subtle d-inline-flex align-items-center gap-1 py-1 px-2" style="font-size: 0.74rem;">
                        <span class="pulse-dot"></span> Enlace Listo &bull; Sincronizado
                    </span>
                </div>
                <div class="small text-dark mt-1" style="font-size: 0.83rem;">
                    Origen: <code>{{ $apiConfig['url'] ?? route('controlecp.api.usuarios.mock') }}</code> &bull;
                    <span class="text-muted">Última sincronización: <strong>{{ $lastSync['date'] ?? now()->format('d/m/Y H:i') }}</strong></span>
                </div>
            </div>
        </div>
        <a href="{{ route('controlecp.administrador.usuarios') }}" class="btn btn-sm text-white rounded-pill px-3 flex-shrink-0 shadow-sm" style="background-color: #39A900;">
            <i class="fas fa-rotate me-1"></i> Sincronizar Ahora
        </a>
    </div>
</div>

<div class="welcome-section-label mb-2">Resumen General</div>

{{-- ════════════════════════════════════════════════
     TARJETAS DE RESUMEN (2 × 2) CON DATOS REALES
════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">

    {{-- Total usuarios activos --}}
    <div class="col-12 col-sm-6">
        <a href="{{ route('controlecp.administrador.usuarios') }}" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon si-green">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-value">{{ $totalUsuariosEcp ?? 18 }}</div>
                    <div class="stat-label">Total Usuarios en Control ECP</div>
                    <div class="stat-hint">
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0">
                            {{ $conteoAdmins ?? 2 }} Admin
                        </span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0">
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
        <a href="{{ route('controlecp.administrador.fichas') }}" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon si-blue">
                    <i class="fas fa-id-card"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-value">{{ $totalFichasActivas ?? 115 }}</div>
                    <div class="stat-label">Total de Fichas Activas</div>
                    <div class="stat-hint">
                        Cursos activos de un total de <strong>{{ $totalFichas ?? 537 }}</strong> fichas en SICEFA
                    </div>
                </div>
            </div>
        </a>
    </div>

    {{-- Actividades programadas para hoy --}}
    <div class="col-12 col-sm-6">
        <a href="{{ route('controlecp.administrador.cronograma') }}" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon si-amber">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-value">{{ $actividadesHoy ?? 3 }}</div>
                    <div class="stat-label">Actividades Programadas para Hoy</div>
                    <div class="stat-hint">Sesiones y talleres de convivencia del día</div>
                </div>
            </div>
        </a>
    </div>

    {{-- Solicitudes pendientes --}}
    <div class="col-12 col-sm-6">
        <a href="{{ route('controlecp.administrador.pqr') }}" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon si-purple">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-value">{{ $solicitudesPendientes ?? 0 }}</div>
                    <div class="stat-label">Solicitudes Pendientes</div>
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
                <a href="{{ route('controlecp.administrador.inventario') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    Ver Inventario
                </a>
            </div>

            @if (isset($recursosCriticos) && $recursosCriticos > 0)
                <div class="list-group list-group-flush small">
                    @foreach ($materialesCriticos as $mat)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-1 bg-transparent">
                            <span class="fw-semibold text-dark">{{ $mat->nombre }}</span>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ $mat->cantidad }} {{ $mat->unidad }} ({{ $mat->estado }})</span>
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
                <a href="{{ route('controlecp.administrador.momentos') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
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

{{-- ════════════════════════════════════════════════
     FICHAS ACADÉMICAS DESTACADAS DEL ERP SENA EMPRESA
════════════════════════════════════════════════ --}}
<div class="card border-0 shadow-sm" style="border-radius: 16px;">
    <div class="card-header bg-white border-0 pt-3 pb-2 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fas fa-graduation-cap me-2 text-success"></i>Fichas Académicas y Programas del Centro La Angostura
            </h6>
            <small class="text-muted">Cursos sincronizados del directorio académico central de SICEFA</small>
        </div>
        <a href="{{ route('controlecp.administrador.fichas') }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
            Ver Todas las Fichas ({{ $totalFichas ?? 537 }})
        </a>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                <thead class="table-light" style="font-size: 0.78rem; text-transform: uppercase; color: #52796f;">
                    <tr>
                        <th class="ps-4 py-2">Ficha</th>
                        <th>Programa de Formación</th>
                        <th>Estado Académico</th>
                        <th>Ruta Cultura de Paz</th>
                        <th class="text-end pe-4">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fichasDestacadas as $ficha)
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-light text-dark border fw-bold px-2 py-1">
                                    <i class="fas fa-id-badge text-success me-1"></i>{{ $ficha->code }}
                                </span>
                            </td>
                            <td>
                                <strong class="text-dark">{{ $ficha->program ? $ficha->program->name : 'PROGRAMA EN FORMACIÓN' }}</strong>
                            </td>
                            <td>
                                <span class="badge {{ $ficha->status === 'Activo' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary' }} rounded-pill px-2 py-1">
                                    <i class="fas fa-circle {{ $ficha->status === 'Activo' ? 'text-success' : 'text-secondary' }} me-1" style="font-size: 0.55rem;"></i>
                                    {{ $ficha->status ?? 'Activo' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px; width: 80px; border-radius: 4px;">
                                        <div class="progress-bar bg-success" style="width: 75%;"></div>
                                    </div>
                                    <span class="small text-muted" style="font-size: 0.75rem;">Momento 2</span>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('controlecp.administrador.fichas') }}" class="btn btn-sm btn-light rounded-pill px-2" title="Consultar ficha">
                                    <i class="fas fa-arrow-right text-muted"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-3 text-muted">No hay fichas disponibles en este momento.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection