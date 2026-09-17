@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">

    <!-- Breadcrumb & Cabecera -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.aprendiz.inicio') }}" class="text-decoration-none" style="color: #2E7D32;">
                            <i class="fas fa-house me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page" style="color: #1B5E20; font-weight: 600;">
                        Encuesta
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: #001A29; font-family: 'Outfit', sans-serif;">
                <i class="fas fa-square-poll-vertical me-2" style="color: #39A900;"></i>Encuestas y Evaluaciones ECP
            </h2>
            <p class="text-muted small mb-0">Control ECP &bull; Evaluación de satisfacción y retroalimentación de talleres</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('controlecp.aprendiz.inicio') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver al Inicio
            </a>
        </div>
    </div>

    <!-- Contenido Encuestas -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 18px;">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-4 mb-3 border">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-warning flex-shrink-0" style="width: 50px; height: 50px; background-color: #FFF8E1; font-size: 1.4rem;">
                    <i class="fas fa-star"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="fw-bold mb-1 text-dark">Encuesta Momento 1: Autoconciencia</h6>
                    <p class="small text-muted mb-0">Por favor responde esta breve encuesta de 5 preguntas sobre tu experiencia en el Momento 1.</p>
                </div>
                <button class="btn text-white rounded-pill px-4" style="background-color: #2E7D32;">Responder Encuesta</button>
            </div>
        </div>
    </div>

</div>
@endsection
