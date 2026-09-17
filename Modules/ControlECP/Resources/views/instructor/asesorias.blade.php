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
                        Asesorías
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: #001A29; font-family: 'Outfit', sans-serif;">
                <i class="fas fa-comments me-2" style="color: #39A900;"></i>Atención de Asesorías
            </h2>
            <p class="text-muted small mb-0">Control ECP &bull; Solicitudes de asesoría individual o grupal de aprendices</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('controlecp.instructor.inicio') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver al Inicio
            </a>
        </div>
    </div>

    <!-- Lista de Asesorías -->
    <div class="card border-0 shadow-sm" style="border-radius: 18px;">
        <div class="card-body p-4 text-center py-5">
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 70px; height: 70px; background-color: #EDE9FE; color: #7C3AED;">
                <i class="fas fa-comments fa-2x"></i>
            </div>
            <h5 class="fw-bold" style="color: #001A29;">Gestión de Asesorías</h5>
            <p class="text-muted mx-auto small" style="max-width: 500px;">Aquí recibirás y gestionarás las agendas de asesorías solicitadas por tus aprendices.</p>
            <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-2">
                <i class="fas fa-circle-check me-1"></i> Módulo habilitado correctamente
            </span>
        </div>
    </div>

</div>
@endsection
