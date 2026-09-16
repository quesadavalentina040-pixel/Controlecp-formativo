@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
/* ================================================================
   DASHBOARD ADMIN — Control ECP
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

/* ── Sección heading ── */
.sec-heading {
    font-family: 'Outfit', sans-serif;
    font-size: .85rem;
    font-weight: 700;
    color: #94a3b8;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    margin-bottom: 1rem;
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
</style>
@endpush

@section('dashboard-content')

{{-- ════════════════════════════════════════════════
     TÍTULO DE PÁGINA
════════════════════════════════════════════════ --}}
<div class="page-title-bar">
    <h5>
        <i class="fas fa-house me-2 text-success"></i>
        Panel de Inicio - Administrador
    </h5>
</div>

{{-- ════════════════════════════════════════════════
     BIENVENIDA
════════════════════════════════════════════════ --}}
<div class="dash-welcome mb-4">
    <div class="welcome-title">
        Bienvenido, {{ Auth::user()->full_name ?? Auth::user()->name }} 👋
    </div>
    <div class="welcome-sub">
        Aquí tienes el resumen general del sistema, actualizado en tiempo real.
    </div>
    <div class="welcome-section-label">Resumen General</div>
</div>

{{-- ════════════════════════════════════════════════
     TARJETAS DE RESUMEN (2 × 2)
════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">

    {{-- Total usuarios activos --}}
    <div class="col-12 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon si-green">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">—</div>
                <div class="stat-label">Total Usuarios Activos</div>
                <div class="stat-hint">Registrados en el sistema</div>
            </div>
        </div>
    </div>

    {{-- Total fichas activas --}}
    <div class="col-12 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon si-blue">
                <i class="fas fa-id-card"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">—</div>
                <div class="stat-label">Total de Fichas Activas</div>
                <div class="stat-hint">Cursos en estado Activo</div>
            </div>
        </div>
    </div>

    {{-- Actividades programadas para hoy --}}
    <div class="col-12 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon si-amber">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">—</div>
                <div class="stat-label">Actividades Programadas para Hoy</div>
                <div class="stat-hint">Cronograma del día</div>
            </div>
        </div>
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
                    <div class="stat-hint">PQR por atender</div>
                </div>
            </div>
        </a>
    </div>

</div>

{{-- ════════════════════════════════════════════════
     SECCIÓN ALERTAS
════════════════════════════════════════════════ --}}
<div class="welcome-section-label mb-2">Alertas</div>
<div class="sec-divider"></div>

<div class="row g-3">

    {{-- Recursos de inventario en estado crítico --}}
    <div class="col-12 col-md-6">
        <div class="alert-card">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="alert-card-icon ai-red">
                    <i class="fas fa-warehouse"></i>
                </div>
                <div>
                    <p class="alert-card-title">Recursos de Inventario en Estado Crítico</p>
                    <span class="alert-card-count red">{{ $recursosCriticos ?? 0 }}</span>
                    <span class="alert-card-sub">recursos con stock bajo o agotado</span>
                </div>
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
                    Sin recursos críticos en inventario
                </div>
            @endif
        </div>
    </div>

    {{-- Fichas próximas a completar los 3 momentos --}}
    <div class="col-12 col-md-6">
        <div class="alert-card">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="alert-card-icon ai-orange">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div>
                    <p class="alert-card-title">Fichas Próximas a Completar los Tres Momentos</p>
                    <span class="alert-card-count orange">—</span>
                    <span class="alert-card-sub">vencen en los próximos 30 días</span>
                </div>
            </div>
            <div class="empty-state">
                <i class="fas fa-circle-check text-success"></i>
                Sin datos disponibles aún
            </div>
        </div>
    </div>

</div>
@endsection