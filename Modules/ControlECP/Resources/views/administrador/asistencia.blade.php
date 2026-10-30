@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <a href="{{ route('controlecp.administrador.inicio') }}" class="small text-decoration-none" style="color: #39A900;">
                <i class="fas fa-house me-1"></i>Inicio
            </a>
            <h2 class="h3 fw-bold mt-2 mb-0" style="color: #001A29;">
                <i class="fas fa-user-check me-2" style="color: #39A900;"></i>Control de Asistencia
            </h2>
        </div>
        <a href="{{ route('controlecp.administrador.cronograma') }}" class="btn btn-outline-success rounded-pill">
            <i class="fas fa-calendar-days me-1"></i>Ver cronograma
        </a>
    </div>

    <div class="alert alert-info border-0 shadow-sm" role="status">
        <h5 class="alert-heading"><i class="fas fa-circle-info me-2"></i>Módulo en preparación</h5>
        <p class="mb-0">
            El registro y consolidación de asistencia depende del flujo de trabajo del rol Instructor.
            Aún no hay registros disponibles para consultar desde esta pantalla.
        </p>
    </div>
</div>
@endsection
