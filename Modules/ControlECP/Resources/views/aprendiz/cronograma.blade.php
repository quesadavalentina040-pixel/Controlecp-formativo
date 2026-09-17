@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .semaforo-indicator {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        display: inline-block;
    }
    .semaforo-verde { background-color: #2e7d32; box-shadow: 0 0 8px rgba(46, 125, 50, 0.6); }
    .semaforo-amarillo { background-color: #f57f17; box-shadow: 0 0 8px rgba(245, 127, 23, 0.6); }
    .semaforo-rojo { background-color: #d32f2f; box-shadow: 0 0 8px rgba(211, 47, 47, 0.6); }
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
                        <a href="{{ route('controlecp.aprendiz.inicio') }}" class="text-decoration-none" style="color: #2E7D32;">
                            <i class="fas fa-house me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page" style="color: #1B5E20; font-weight: 600;">
                        Mi Cronograma
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: #001A29; font-family: 'Outfit', sans-serif;">
                <i class="fas fa-calendar-days me-2" style="color: #39A900;"></i>Mi Cronograma de Sesiones ECP
            </h2>
            <p class="text-muted small mb-0">Control ECP &bull; Calendario y programación de talleres de tu ficha de formación</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('controlecp.aprendiz.inicio') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver al Inicio
            </a>
        </div>
    </div>

    <!-- Tabla de Cronograma para Aprendiz -->
    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color: #E8F5E9; color: #1B5E20;" class="small fw-bold">
                        <tr>
                            <th class="ps-4 py-3 text-center">Estado</th>
                            <th class="py-3">Fecha y Hora</th>
                            <th class="py-3">Momento & Actividad</th>
                            <th class="py-3">Instructor</th>
                            <th class="py-3 text-center">Asistencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-4 text-center"><span class="semaforo-indicator semaforo-amarillo" title="Próxima Sesión"></span></td>
                            <td><div class="fw-bold">18 Sep 2026</div><div class="small text-muted">10:00 AM - 12:00 PM</div></td>
                            <td><div class="fw-bold">Momento 2: Convivencia</div><div class="small text-muted">01. Círculos de Diálogo y Empatía</div></td>
                            <td><div class="small fw-bold">María Andrea López</div></td>
                            <td class="text-center"><span class="badge bg-warning-subtle text-warning border px-3">Próxima</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4 text-center"><span class="semaforo-indicator semaforo-verde" title="Asistió"></span></td>
                            <td><div class="fw-bold">08 Sep 2026</div><div class="small text-muted">09:00 AM - 11:00 AM</div></td>
                            <td><div class="fw-bold">Momento 1: Autoconciencia</div><div class="small text-muted">03. Proyecto de Vida e Integralidad</div></td>
                            <td><div class="small fw-bold">María Andrea López</div></td>
                            <td class="text-center"><span class="badge bg-success-subtle text-success border px-3"><i class="fas fa-check me-1"></i> Asistió</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
