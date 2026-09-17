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

/* ── Tarjetas de alerta / módulos ── */
.alert-card {
    background: #fff;
    border: 1px solid #e0ece4;
    border-radius: 14px;
    padding: 1.25rem 1.4rem;
    height: 100%;
    transition: transform .18s, box-shadow .18s;
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
        Panel de Inicio - Aprendiz
    </h5>
</div>

<div class="dash-welcome mb-4">
    <div class="welcome-title">
        Bienvenido, {{ Auth::user()->full_name ?? Auth::user()->name }} 🎓
    </div>
    <div class="welcome-sub">
        Tu espacio personal en la Escuela Cultura de Paz (ECP).
    </div>
    <div class="welcome-section-label">Mi Progreso</div>
</div>

<!-- TARJETAS DE RESUMEN APRENDIZ -->
<div class="row g-3 mb-4">

    <!-- Próxima Sesión -->
    <div class="col-12 col-sm-6 col-lg-3">
        <a href="{{ route('controlecp.aprendiz.cronograma') }}" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon si-green">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-value">18 Sep</div>
                    <div class="stat-label">Próximo Taller</div>
                    <div class="stat-hint">Convivencia - 10:00 AM</div>
                </div>
            </div>
        </a>
    </div>

    <!-- Asistencia Acumulada -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon si-blue">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">100%</div>
                <div class="stat-label">Mi Asistencia</div>
                <div class="stat-hint">3 de 3 sesiones asistidas</div>
            </div>
        </div>
    </div>

    <!-- Encuestas Pendientes -->
    <div class="col-12 col-sm-6 col-lg-3">
        <a href="{{ route('controlecp.aprendiz.encuesta') }}" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon si-amber">
                    <i class="fas fa-square-poll-vertical"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-value">1</div>
                    <div class="stat-label">Encuestas Pendientes</div>
                    <div class="stat-hint">Evaluación Momento 1</div>
                </div>
            </div>
        </a>
    </div>

    <!-- Mis Solicitudes PQR -->
    <div class="col-12 col-sm-6 col-lg-3">
        <a href="{{ route('controlecp.aprendiz.pqr') }}" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon si-purple">
                    <i class="fas fa-comment-dots"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-value">0</div>
                    <div class="stat-label">Mis PQR</div>
                    <div class="stat-hint">Solicitudes enviadas</div>
                </div>
            </div>
        </a>
    </div>

</div>

<!-- SECCIÓN ACCESOS RÁPIDOS -->
<div class="welcome-section-label mb-2">Servicios para el Aprendiz</div>
<div class="sec-divider"></div>

<div class="row g-3">

    <!-- Solicitar Asesoría -->
    <div class="col-12 col-md-6">
        <div class="alert-card d-flex flex-column justify-content-between">
            <div>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-primary" style="width: 45px; height: 45px; background-color: #e0f2fe; font-size: 1.2rem;">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Solicitar Asesoría ECP</h6>
                        <span class="small text-muted">Atención individual con tus instructores</span>
                    </div>
                </div>
                <p class="small text-muted">¿Necesitas orientación o acompañamiento personal en temas de Cultura de Paz? Agenda una cita directa.</p>
            </div>
            <a href="{{ route('controlecp.aprendiz.asesorias') }}" class="btn btn-outline-primary rounded-pill btn-sm align-self-start px-4">Pedir Asesoría</a>
        </div>
    </div>

    <!-- Radicar PQR -->
    <div class="col-12 col-md-6">
        <div class="alert-card d-flex flex-column justify-content-between">
            <div>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-success" style="width: 45px; height: 45px; background-color: #e8f5e9; font-size: 1.2rem;">
                        <i class="fas fa-comment-dots"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Radicar Petición o Sugerencia</h6>
                        <span class="small text-muted">Sistema de PQR Control ECP</span>
                    </div>
                </div>
                <p class="small text-muted">Envía tus comentarios, inquietudes, felicidades o reclamos sobre los talleres e instalaciones.</p>
            </div>
            <a href="{{ route('controlecp.aprendiz.pqr') }}" class="btn btn-outline-success rounded-pill btn-sm align-self-start px-4">Radicar PQR</a>
        </div>
    </div>

</div>
@endsection