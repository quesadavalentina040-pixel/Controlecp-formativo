@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .momento-card {
        border: none;
        border-radius: 20px;
        background: #ffffff;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        overflow: hidden;
    }
    .momento-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 50, 77, 0.12);
    }
    .momento-header-1 {
        background: linear-gradient(135deg, #1B5E20 0%, #2E7D32 100%);
        color: #ffffff;
    }
    .momento-header-2 {
        background: linear-gradient(135deg, #0277BD 0%, #0288D1 100%);
        color: #ffffff;
    }
    .momento-header-3 {
        background: linear-gradient(135deg, #6A1B9A 0%, #8E24AA 100%);
        color: #ffffff;
    }
    .badge-actividades {
        font-size: 0.82rem;
        font-weight: 700;
        padding: 0.45rem 0.85rem;
        border-radius: 50rem;
    }
    .actividad-item {
        border-left: 4px solid #e0ece4;
        background-color: #f8faf9;
        transition: all 0.2s ease;
    }
    .actividad-item:hover {
        background-color: #f0f7f3;
        border-left-color: #39A900;
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">

    <!-- Breadcrumb & Cabecera -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.administrador.inicio') }}" class="text-decoration-none" style="color: #2E7D32;">
                            <i class="fas fa-house me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page" style="color: #1B5E20; font-weight: 600;">
                        Momentos
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: #001A29; font-family: 'Outfit', sans-serif;">
                <i class="fas fa-star me-2" style="color: #39A900;"></i>Momentos Pedagógicos ECP
            </h2>
            <p class="text-muted small mb-0">Estructura de formación, talleres y experiencias pedagógicas de Escuela Cultura de Paz</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('controlecp.administrador.actividades') }}" class="btn text-white rounded-pill px-3 shadow-sm" style="background-color: #2E7D32; border-color: #2E7D32;">
                <i class="fas fa-list-check me-1"></i> Ver Actividades
            </a>
            <a href="{{ route('controlecp.administrador.inicio') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="fas fa-arrow-left me-1"></i> Volver al Inicio
            </a>
        </div>
    </div>

    <!-- Banner Informativo -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 18px; background: linear-gradient(135deg, #E8F5E9 0%, #C8E6C9 100%);">
        <div class="card-body p-4 d-flex align-items-center gap-3">
            <div class="rounded-circle bg-white text-success d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 56px; height: 56px; font-size: 1.5rem;">
                <i class="fas fa-shapes"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-1" style="color: #1B5E20;">Estructura del Programa ECP</h5>
                <p class="mb-0 text-secondary small">
                    El proceso formativo comprende <strong>3 Momentos Clave</strong> con un total de <strong>18 actividades pedagógicas</strong> orientadas al desarrollo personal, la convivencia armónica y la resolución pacífica de conflictos.
                </p>
            </div>
        </div>
    </div>

    <!-- Grilla de los 3 Momentos -->
    <div class="row g-4 mb-4">

        <!-- MOMENTO 1: AUTOCONCIENCIA -->
        <div class="col-12 col-lg-4">
            <div class="card momento-card h-100">
                <div class="card-header momento-header-1 p-4 border-0">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-white text-success rounded-pill px-3 py-1 fw-bold text-uppercase small shadow-sm">
                            Momento 01
                        </span>
                        <span class="badge badge-actividades bg-white text-dark shadow-sm">
                            <i class="fas fa-tasks text-success me-1"></i> 10 Actividades
                        </span>
                    </div>
                    <h3 class="h4 fw-bold mb-2">Autoconciencia</h3>
                    <p class="mb-0 small text-white-50">
                        Introspección, reconocimiento de emociones, autorreflexión y construcción del proyecto de vida.
                    </p>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <h6 class="fw-bold text-muted text-uppercase mb-3 small" style="letter-spacing: 1px;">
                            <i class="fas fa-list-ol me-1 text-success"></i> Lista de Actividades (10)
                        </h6>
                        <div class="d-flex flex-column gap-2 mb-3">
                            <div class="actividad-item p-2 px-3 rounded-3 small">
                                <span class="fw-bold text-success me-1">01.</span> Reconocimiento y Gestión Emocional
                            </div>
                            <div class="actividad-item p-2 px-3 rounded-3 small">
                                <span class="fw-bold text-success me-1">02.</span> Taller de Autorreflexión y Autoconcepto
                            </div>
                            <div class="actividad-item p-2 px-3 rounded-3 small">
                                <span class="fw-bold text-success me-1">03.</span> Proyecto de Vida y Sueños Integrales
                            </div>
                            <div class="actividad-item p-2 px-3 rounded-3 small">
                                <span class="fw-bold text-success me-1">04.</span> Identificación de Fortalezas y Oportunidades
                            </div>
                            <div class="actividad-item p-2 px-3 rounded-3 small">
                                <span class="fw-bold text-success me-1">05.</span> Expresión Asertiva de Sentimientos
                            </div>
                            <div class="actividad-item p-2 px-3 rounded-3 small">
                                <span class="fw-bold text-success me-1">06.</span> Mapeo Corporal y Autocuidado
                            </div>
                            <div class="actividad-item p-2 px-3 rounded-3 small">
                                <span class="fw-bold text-success me-1">07.</span> Control del Estrés e Impulsos
                            </div>
                            <div class="actividad-item p-2 px-3 rounded-3 small">
                                <span class="fw-bold text-success me-1">08.</span> Historia Personal y Raíces Culturales
                            </div>
                            <div class="actividad-item p-2 px-3 rounded-3 small">
                                <span class="fw-bold text-success me-1">09.</span> Principios, Valores y Ética Personal
                            </div>
                            <div class="actividad-item p-2 px-3 rounded-3 small">
                                <span class="fw-bold text-success me-1">10.</span> Compromiso Conmigo Mismo
                            </div>
                        </div>
                    </div>
                    <div class="border-top pt-3 mt-2 text-center">
                        <span class="small text-muted fw-semibold">
                            <i class="fas fa-bullseye text-success me-1"></i> Fase inicial del programa
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- MOMENTO 2: CONVIVENCIA -->
        <div class="col-12 col-lg-4">
            <div class="card momento-card h-100">
                <div class="card-header momento-header-2 p-4 border-0">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-white text-primary rounded-pill px-3 py-1 fw-bold text-uppercase small shadow-sm">
                            Momento 02
                        </span>
                        <span class="badge badge-actividades bg-white text-dark shadow-sm">
                            <i class="fas fa-tasks text-primary me-1"></i> 4 Actividades
                        </span>
                    </div>
                    <h3 class="h4 fw-bold mb-2">Convivencia</h3>
                    <p class="mb-0 small text-white-50">
                        Fomento de la empatía, trabajo en equipo, escucha activa y construcción comunitaria.
                    </p>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <h6 class="fw-bold text-muted text-uppercase mb-3 small" style="letter-spacing: 1px;">
                            <i class="fas fa-list-ol me-1 text-primary"></i> Lista de Actividades (4)
                        </h6>
                        <div class="d-flex flex-column gap-2 mb-3">
                            <div class="actividad-item p-2 px-3 rounded-3 small" style="border-left-color: #0288D1;">
                                <span class="fw-bold text-primary me-1">01.</span> Círculos de Diálogo y Empatía
                            </div>
                            <div class="actividad-item p-2 px-3 rounded-3 small" style="border-left-color: #0288D1;">
                                <span class="fw-bold text-primary me-1">02.</span> Dinámicas de Trabajo Colaborativo
                            </div>
                            <div class="actividad-item p-2 px-3 rounded-3 small" style="border-left-color: #0288D1;">
                                <span class="fw-bold text-primary me-1">03.</span> Comunicación No Violenta (CNV)
                            </div>
                            <div class="actividad-item p-2 px-3 rounded-3 small" style="border-left-color: #0288D1;">
                                <span class="fw-bold text-primary me-1">04.</span> Acuerdos de Convivencia y Respeto
                            </div>
                        </div>
                    </div>
                    <div class="border-top pt-3 mt-2 text-center">
                        <span class="small text-muted fw-semibold">
                            <i class="fas fa-users text-primary me-1"></i> Interacción y grupo
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- MOMENTO 3: RESOLUCIÓN -->
        <div class="col-12 col-lg-4">
            <div class="card momento-card h-100">
                <div class="card-header momento-header-3 p-4 border-0">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-white text-purple rounded-pill px-3 py-1 fw-bold text-uppercase small shadow-sm" style="color: #6A1B9A !important;">
                            Momento 03
                        </span>
                        <span class="badge badge-actividades bg-white text-dark shadow-sm">
                            <i class="fas fa-tasks text-purple me-1" style="color: #6A1B9A;"></i> 4 Actividades
                        </span>
                    </div>
                    <h3 class="h4 fw-bold mb-2">Resolución</h3>
                    <p class="mb-0 small text-white-50">
                        Estrategias, herramientas y mediación pacífica para la gestión y transformación de conflictos.
                    </p>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <h6 class="fw-bold text-muted text-uppercase mb-3 small" style="letter-spacing: 1px;">
                            <i class="fas fa-list-ol me-1" style="color: #6A1B9A;"></i> Lista de Actividades (4)
                        </h6>
                        <div class="d-flex flex-column gap-2 mb-3">
                            <div class="actividad-item p-2 px-3 rounded-3 small" style="border-left-color: #8E24AA;">
                                <span class="fw-bold me-1" style="color: #6A1B9A;">01.</span> Taller de Mediación Pacífica
                            </div>
                            <div class="actividad-item p-2 px-3 rounded-3 small" style="border-left-color: #8E24AA;">
                                <span class="fw-bold me-1" style="color: #6A1B9A;">02.</span> Negociación y Estrategias Ganar-Ganar
                            </div>
                            <div class="actividad-item p-2 px-3 rounded-3 small" style="border-left-color: #8E24AA;">
                                <span class="fw-bold me-1" style="color: #6A1B9A;">03.</span> Estudio de Casos y Juego de Roles
                            </div>
                            <div class="actividad-item p-2 px-3 rounded-3 small" style="border-left-color: #8E24AA;">
                                <span class="fw-bold me-1" style="color: #6A1B9A;">04.</span> Plan de Acción y Pactos de Paz
                            </div>
                        </div>
                    </div>
                    <div class="border-top pt-3 mt-2 text-center">
                        <span class="small text-muted fw-semibold">
                            <i class="fas fa-scale-balanced me-1" style="color: #6A1B9A;"></i> Transformación del entorno
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
