@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">

    <!-- Breadcrumb & Cabecera -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.instructor.inicio') }}" class="text-decoration-none" style="color: #2E7D32;">
                            <i class="fas fa-house me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page" style="color: #1B5E20; font-weight: 600;">
                        Actividades
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: #001A29; font-family: 'Outfit', sans-serif;">
                <i class="fas fa-list-check me-2" style="color: #39A900;"></i>Catálogo de Actividades Instructor
            </h2>
            <p class="text-muted small mb-0">Control ECP &bull; Guía metodológica de los 18 talleres del programa</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('controlecp.instructor.momentos') }}" class="btn btn-outline-success rounded-pill px-3 btn-sm">
                <i class="fas fa-star me-1"></i> Ver Momentos
            </a>
            <a href="{{ route('controlecp.instructor.inicio') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver al Inicio
            </a>
        </div>
    </div>

    <!-- Lista de Actividades -->
    <div class="card border-0 shadow-sm" style="border-radius: 18px;">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3" style="color: #1B5E20;"><i class="fas fa-book-open me-2"></i>Guía de Talleres Formativos</h5>
            <div class="list-group list-group-flush">
                <div class="list-group-item px-0 py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="badge bg-success-subtle text-success mb-1">Momento 1: Autoconciencia</span>
                            <h6 class="fw-bold mb-1">01. Reconocimiento y Gestión Emocional</h6>
                            <p class="small text-muted mb-0">Rueda de emociones, identificación de patrones de reacción y autocuidado básico.</p>
                        </div>
                        <span class="badge bg-light text-dark border">2 Horas</span>
                    </div>
                </div>
                <div class="list-group-item px-0 py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="badge bg-primary-subtle text-primary mb-1">Momento 2: Convivencia</span>
                            <h6 class="fw-bold mb-1">01. Círculos de Diálogo y Empatía</h6>
                            <p class="small text-muted mb-0">Ejercicio de escucha activa sin juicios y construcción de vínculos comunitarios.</p>
                        </div>
                        <span class="badge bg-light text-dark border">2 Horas</span>
                    </div>
                </div>
                <div class="list-group-item px-0 py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="badge bg-purple-subtle text-purple mb-1" style="background-color: #f3e5f5; color: #6a1b9a;">Momento 3: Resolución</span>
                            <h6 class="fw-bold mb-1">01. Taller de Mediación Pacífica de Conflictos</h6>
                            <p class="small text-muted mb-0">Simulación de casos reales con rol de mediadores de pares.</p>
                        </div>
                        <span class="badge bg-light text-dark border">3 Horas</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
