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
                        Momentos
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: #001A29; font-family: 'Outfit', sans-serif;">
                <i class="fas fa-star me-2" style="color: #39A900;"></i>Momentos ECP - Guía Instructor
            </h2>
            <p class="text-muted small mb-0">Control ECP &bull; Orientación pedagógica de los 3 Momentos del programa</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('controlecp.instructor.inicio') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver al Inicio
            </a>
        </div>
    </div>

    <!-- Grilla de los 3 Momentos para Instructor -->
    <div class="row g-4">
        <!-- Momento 1: Autoconciencia -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 18px;">
                <div class="card-header p-4 text-white border-0" style="background: linear-gradient(135deg, #1B5E20 0%, #2E7D32 100%); border-top-left-radius: 18px; border-top-right-radius: 18px;">
                    <span class="badge bg-white text-success rounded-pill px-3 py-1 fw-bold text-uppercase small mb-2">Momento 01</span>
                    <h4 class="fw-bold mb-1">Autoconciencia</h4>
                    <p class="small mb-0 text-white-50">10 Actividades pedagógicas de introspección</p>
                </div>
                <div class="card-body p-4">
                    <p class="small text-muted">Guía para acompañar al aprendiz en el reconocimiento de sus emociones, autoestima y proyecto de vida.</p>
                    <a href="{{ route('controlecp.instructor.actividades') }}" class="btn btn-outline-success btn-sm rounded-pill w-100">Ver Actividades (10)</a>
                </div>
            </div>
        </div>

        <!-- Momento 2: Convivencia -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 18px;">
                <div class="card-header p-4 text-white border-0" style="background: linear-gradient(135deg, #0277BD 0%, #0288D1 100%); border-top-left-radius: 18px; border-top-right-radius: 18px;">
                    <span class="badge bg-white text-primary rounded-pill px-3 py-1 fw-bold text-uppercase small mb-2">Momento 02</span>
                    <h4 class="fw-bold mb-1">Convivencia</h4>
                    <p class="small mb-0 text-white-50">4 Actividades de trabajo grupal</p>
                </div>
                <div class="card-body p-4">
                    <p class="small text-muted">Talleres prácticos de empatía, círculos de diálogo y construcción de acuerdos comunitarios.</p>
                    <a href="{{ route('controlecp.instructor.actividades') }}" class="btn btn-outline-primary btn-sm rounded-pill w-100">Ver Actividades (4)</a>
                </div>
            </div>
        </div>

        <!-- Momento 3: Resolución -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 18px;">
                <div class="card-header p-4 text-white border-0" style="background: linear-gradient(135deg, #6A1B9A 0%, #8E24AA 100%); border-top-left-radius: 18px; border-top-right-radius: 18px;">
                    <span class="badge bg-white rounded-pill px-3 py-1 fw-bold text-uppercase small mb-2" style="color: #6A1B9A;">Momento 03</span>
                    <h4 class="fw-bold mb-1">Resolución</h4>
                    <p class="small mb-0 text-white-50">4 Actividades de mediación de conflictos</p>
                </div>
                <div class="card-body p-4">
                    <p class="small text-muted">Herramientas de mediación pacífica, negociación ganar-ganar y pactos de paz institucional.</p>
                    <a href="{{ route('controlecp.instructor.actividades') }}" class="btn btn-outline-secondary btn-sm rounded-pill w-100">Ver Actividades (4)</a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
