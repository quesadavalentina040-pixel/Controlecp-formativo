{{-- Vista: Panel de inicio y resumen de información del aprendiz. --}}
@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .dash-welcome {
        padding: 1.35rem 1.75rem;
        margin-bottom: 1rem !important;
        border-radius: 24px;
        background: linear-gradient(120deg, #001a29, #00324d);
        color: #fff;
    }

    .dash-welcome .welcome-eyebrow {
        margin-bottom: .35rem;
        color: #62e31d;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .dash-welcome .welcome-title {
        margin-bottom: .3rem;
        color: #fff;
        font-family: 'Outfit', sans-serif;
        font-size: 2rem;
        font-weight: 800;
        line-height: 1.2;
    }

    .dash-welcome .welcome-sub {
        margin-bottom: 0;
        color: #c7d4da;
        font-size: .93rem;
    }

    .welcome-section-label {
        color: #94a3b8;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: 1.2px;
        text-transform: uppercase;
    }

    .stat-card {
        display: flex;
        height: 100%;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.35rem 1.4rem;
        border: 2px solid #e0ece4;
        border-radius: 14px;
        background: #fff;
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .stat-card:hover,
    .alert-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 50, 77, .09);
    }

    .stat-card-green { border-color: rgba(57, 169, 0, .45); }
    .stat-card-blue { border-color: rgba(0, 112, 184, .35); }
    .stat-card-amber { border-color: rgba(217, 119, 6, .35); }
    .stat-card-purple { border-color: rgba(124, 58, 237, .35); }

    .stat-icon,
    .alert-card-icon {
        display: flex;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        border-radius: 13px;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        font-size: 1.2rem;
    }

    .si-green { background: #d8f3dc; color: #39a900; }
    .si-blue { background: #d0e8f5; color: #0070b8; }
    .si-amber { background: #fff3cd; color: #d97706; }
    .si-purple { background: #ede9fe; color: #7c3aed; }

    .stat-value {
        color: #001a29;
        font-family: 'Outfit', sans-serif;
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
    }

    .stat-label {
        margin-top: .25rem;
        color: #52796f;
        font-size: .76rem;
        font-weight: 700;
        letter-spacing: .5px;
        text-transform: uppercase;
    }

    .stat-hint {
        margin-top: .2rem;
        color: #94a3b8;
        font-size: .72rem;
    }

    .sec-divider {
        height: 2px;
        margin-bottom: 1.1rem;
        border-radius: 4px;
        background: linear-gradient(to right, #39a900, transparent);
    }

    .learner-quick-access {
        padding: 1.25rem 1.4rem;
        border: 2px solid #dcebe1;
        border-radius: 14px;
        background: #fff;
    }

    .learner-quick-access--blue { border-color: rgba(0, 112, 184, .35); }
    .learner-quick-access--green { border-color: rgba(57, 169, 0, .4); }

    .learner-quick-access-icon {
        display: flex;
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #e0f2fe;
        color: #0878ed;
        font-size: 1.15rem;
    }

    .learner-quick-access-icon--green {
        background: #d8f3dc;
        color: #39a900;
    }

    .learner-quick-access .btn {
        border: 1px solid #0878ed;
        border-radius: 999px;
        background: #fff;
        color: #0878ed;
    }

    .learner-quick-access .btn:hover,
    .learner-quick-access .btn:focus {
        background: #0878ed;
        color: #fff;
    }

    .learner-quick-access .learner-quick-access-submit {
        border-color: #39a900;
        background: #39a900;
        color: #fff;
    }

    .learner-quick-access .learner-quick-access-submit:hover,
    .learner-quick-access .learner-quick-access-submit:focus {
        border-color: #2b8000;
        background: #2b8000;
        color: #fff;
    }

    @media (max-width: 575.98px) {
        .dash-welcome { padding: 1.1rem 1.2rem; }
        .dash-welcome .welcome-title { font-size: 1.55rem; }
        .stat-card { padding: 1rem; }
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-3 py-3">
    <div class="dash-welcome mb-4">
        <div class="welcome-eyebrow"><i class="fas fa-chart-line me-2"></i>Control ECP · Panel Aprendiz</div>
        <div class="welcome-title">
            Bienvenido, {{ Auth::user()->full_name ?? Auth::user()->name }} 🎓
        </div>
        <div class="welcome-sub">Gestión de sesiones, asistencias y seguimiento pedagógico ECP.</div>
    </div>
    <div class="welcome-section-label mb-3">Mi Progreso</div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <a href="{{ route('controlecp.aprendiz.cronograma') }}" class="text-decoration-none">
                <div class="stat-card stat-card-green">
                    <div class="stat-icon si-green"><i class="fas fa-calendar-day"></i></div>
                    <div class="stat-body">
                        <div class="stat-value">18 Sep</div>
                        <div class="stat-label">Próximo Taller</div>
                        <div class="stat-hint">Convivencia - 10:00 AM</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card stat-card-blue">
                <div class="stat-icon si-blue"><i class="fas fa-user-check"></i></div>
                <div class="stat-body">
                    <div class="stat-value">100%</div>
                    <div class="stat-label">Mi Asistencia</div>
                    <div class="stat-hint">3 de 3 sesiones asistidas</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <a href="{{ route('controlecp.aprendiz.encuesta') }}" class="text-decoration-none">
                <div class="stat-card stat-card-amber">
                    <div class="stat-icon si-amber"><i class="fas fa-square-poll-vertical"></i></div>
                    <div class="stat-body">
                        <div class="stat-value">1</div>
                        <div class="stat-label">Encuestas Pendientes</div>
                        <div class="stat-hint">Evaluación Momento 1</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <a href="{{ route('controlecp.aprendiz.pqr') }}" class="text-decoration-none">
                <div class="stat-card stat-card-purple">
                    <div class="stat-icon si-purple"><i class="fas fa-comment-dots"></i></div>
                    <div class="stat-body">
                        <div class="stat-value">0</div>
                        <div class="stat-label">Mis PQRS</div>
                        <div class="stat-hint">Solicitudes enviadas</div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="welcome-section-label mb-2">Servicios para el Aprendiz</div>
    <div class="sec-divider"></div>

    <div class="row g-3">
        <div class="col-12 col-md-6">
            <div class="learner-quick-access learner-quick-access--blue h-100">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="learner-quick-access-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Solicitar Asesoría ECP</h6>
                        <span class="small text-muted">Atención individual con tus instructores</span>
                    </div>
                </div>
                <p class="small text-muted mb-3">
                    ¿Necesitas orientación o acompañamiento personal en temas de Cultura de Paz? Agenda una cita directa.
                </p>
                <a href="{{ route('controlecp.aprendiz.asesorias') }}" class="btn btn-sm px-4">Pedir Asesoría</a>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="learner-quick-access learner-quick-access--green h-100">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="learner-quick-access-icon learner-quick-access-icon--green">
                        <i class="fas fa-comment-dots"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Radicar Petición o Sugerencia</h6>
                        <span class="small text-muted">Sistema de PQRS Control ECP</span>
                    </div>
                </div>
                <p class="small text-muted mb-3">
                    Envía tus comentarios, inquietudes, felicitaciones o reclamos sobre los talleres e instalaciones.
                </p>
                <a href="{{ route('controlecp.aprendiz.pqr') }}" class="btn btn-sm px-4 learner-quick-access-submit">Radicar PQRS</a>
            </div>
        </div>
    </div>
</div>
@endsection
