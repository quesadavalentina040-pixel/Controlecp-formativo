@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <a href="{{ route('controlecp.administrador.inicio') }}" class="small text-decoration-none" style="color: #39A900;">
                <i class="fas fa-house me-1"></i>Inicio
            </a>
            <h2 class="h3 fw-bold mt-2 mb-0" style="color: #001A29;">
                <i class="fas fa-certificate me-2" style="color: #39A900;"></i>Certificados
            </h2>
        </div>
        <a href="{{ route('controlecp.administrador.asistencia') }}" class="btn btn-outline-success rounded-pill">
            <i class="fas fa-user-check me-1"></i>Ver asistencia
        </a>
    </div>

    <div class="alert alert-info border-0 shadow-sm" role="status">
        <h5 class="alert-heading"><i class="fas fa-circle-info me-2"></i>Emisión pendiente de integración</h5>
        <p class="mb-0">
            La emisión de certificados requiere los registros de asistencia y actividades de los instructores.
            El sistema todavía no dispone de datos para generar o consultar certificados.
        </p>
    </div>
</div>
@endsection
