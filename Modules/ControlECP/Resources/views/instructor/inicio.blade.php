@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
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
.ai-orange { background: #ffedd5; color: #ea580c; }
.ai-blue   { background: #e0f2fe; color: #0288d1; }

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

.sec-divider {
    height: 2px;
    background: linear-gradient(to right, #39A900, transparent);
    border-radius: 4px;
    margin-bottom: 1.1rem;
}
</style>
@endpush

@section('dashboard-content')

<div class="page-title-bar">
    <h5>
        <i class="fas fa-house me-2 text-success"></i>
        Panel de Inicio - Instructor
    </h5>
</div>

<div class="dash-welcome mb-4">
    <div class="welcome-title">
        Bienvenido, {{ Auth::user()->full_name ?? Auth::user()->name }} 👨‍🏫
    </div>
    <div class="welcome-sub">
        Gestión de sesiones, asistencias y seguimiento pedagógico ECP.
    </div>
    <div class="welcome-section-label">Resumen de Gestión</div>
</div>

<!-- TARJETAS DE RESUMEN -->
<div class="row g-3 mb-4">

    <!-- Fichas Asignadas -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon si-green">
                <i class="fas fa-id-card"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">3</div>
                <div class="stat-label">Fichas Asignadas</div>
                <div class="stat-hint">Grupos a cargo</div>
            </div>
        </div>
    </div>

    <!-- Sesiones Programadas Hoy -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon si-blue">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">1</div>
                <div class="stat-label">Sesiones Hoy</div>
                <div class="stat-hint">Cronograma del día</div>
            </div>
        </div>
    </div>

    <!-- Momentos en Desarrollo -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon si-amber">
                <i class="fas fa-star"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">2</div>
                <div class="stat-label">Momentos Activos</div>
                <div class="stat-hint">Autoconciencia & Convivencia</div>
            </div>
        </div>
    </div>

    <!-- Asesorías Solicitadas -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon si-purple">
                <i class="fas fa-comments"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">0</div>
                <div class="stat-label">Asesorías Solicitadas</div>
                <div class="stat-hint">Pendientes por atender</div>
            </div>
        </div>
    </div>

</div>

<!-- SECCIÓN ALERTAS Y ACTIVIDADES -->
<div class="welcome-section-label mb-2">Acciones Pendientes</div>
<div class="sec-divider"></div>

<div class="row g-3">

    <!-- Registro de Asistencias Pendientes -->
    <div class="col-12 col-md-6">
        <div class="alert-card">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="alert-card-icon ai-orange">
                    <i class="fas fa-user-check"></i>
                </div>
                <div>
                    <p class="mb-0 fw-bold" style="color: #001A29;">Asistencias Pendientes por Reportar</p>
                    <span class="fs-5 fw-bold text-warning-emphasis">1</span>
                    <span class="small text-muted ms-1">sesión finalizada sin lista enviada</span>
                </div>
            </div>
            <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-bold small text-dark">Ficha 2670192 - ADSO</div>
                    <div class="small text-muted">Momento 1: Autoconciencia</div>
                </div>
                <a href="{{ route('controlecp.instructor.asistencia') }}" class="btn btn-sm text-white rounded-pill px-3" style="background-color: #2E7D32;">Registrar</a>
            </div>
        </div>
    </div>

    <!-- Acompañamiento Pedagógico -->
    <div class="col-12 col-md-6">
        <div class="alert-card">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="alert-card-icon ai-blue">
                    <i class="fas fa-hand-holding-hand"></i>
                </div>
                <div>
                    <p class="mb-0 fw-bold" style="color: #001A29;">Acompañamientos Programados</p>
                    <span class="fs-5 fw-bold text-primary">0</span>
                    <span class="small text-muted ms-1">casos activos esta semana</span>
                </div>
            </div>
            <div class="empty-state">
                <i class="fas fa-circle-check text-success"></i>
                Sin solicitudes de acompañamiento pendientes
            </div>
        </div>
    </div>

</div>
@endsection