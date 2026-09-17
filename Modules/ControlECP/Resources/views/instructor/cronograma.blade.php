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
                        <a href="{{ route('controlecp.instructor.inicio') }}" class="text-decoration-none" style="color: #2E7D32;">
                            <i class="fas fa-house me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page" style="color: #1B5E20; font-weight: 600;">
                        Cronograma Instructor
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: #001A29; font-family: 'Outfit', sans-serif;">
                <i class="fas fa-calendar-days me-2" style="color: #39A900;"></i>Mi Cronograma de Sesiones
            </h2>
            <p class="text-muted small mb-0">Control ECP &bull; Programación y seguimiento de talleres a tu cargo</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('controlecp.instructor.inicio') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver al Inicio
            </a>
        </div>
    </div>

    <!-- Tabla del Cronograma del Instructor -->
    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color: #E8F5E9; color: #1B5E20;" class="small fw-bold">
                        <tr>
                            <th class="ps-4 py-3 text-center">Semáforo</th>
                            <th class="py-3">Fecha y Hora</th>
                            <th class="py-3">Ficha SENA</th>
                            <th class="py-3">Momento & Actividad</th>
                            <th class="py-3 text-center">Estado</th>
                            <th class="text-end pe-4 py-3">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-4 text-center"><span class="semaforo-indicator semaforo-rojo" title="Atrasado"></span></td>
                            <td><div class="fw-bold">12 Sep 2026</div><div class="small text-muted">08:00 AM - 10:00 AM</div></td>
                            <td><span class="badge bg-secondary-subtle text-dark border">Ficha 2670192</span><div class="small text-muted">ADSO</div></td>
                            <td><div class="fw-bold">Momento 1: Autoconciencia</div><div class="small text-muted">01. Gestión Emocional</div></td>
                            <td class="text-center"><span class="badge bg-danger-subtle text-danger border px-3">Pendiente</span></td>
                            <td class="text-end pe-4"><a href="{{ route('controlecp.instructor.asistencia') }}" class="btn btn-sm btn-outline-success rounded-pill px-3">Reportar Asistencia</a></td>
                        </tr>
                        <tr>
                            <td class="ps-4 text-center"><span class="semaforo-indicator semaforo-amarillo" title="Próximo"></span></td>
                            <td><div class="fw-bold">18 Sep 2026</div><div class="small text-muted">10:00 AM - 12:00 PM</div></td>
                            <td><span class="badge bg-secondary-subtle text-dark border">Ficha 2670195</span><div class="small text-muted">Mercados</div></td>
                            <td><div class="fw-bold">Momento 2: Convivencia</div><div class="small text-muted">01. Círculos de Diálogo</div></td>
                            <td class="text-center"><span class="badge bg-warning-subtle text-warning border px-3">Programado</span></td>
                            <td class="text-end pe-4"><button class="btn btn-sm btn-outline-primary rounded-pill px-3" disabled>Próximamente</button></td>
                        </tr>
                        <tr>
                            <td class="ps-4 text-center"><span class="semaforo-indicator semaforo-verde" title="Completado"></span></td>
                            <td><div class="fw-bold">10 Sep 2026</div><div class="small text-muted">02:00 PM - 04:00 PM</div></td>
                            <td><span class="badge bg-secondary-subtle text-dark border">Ficha 2558910</span><div class="small text-muted">Agroindustria</div></td>
                            <td><div class="fw-bold">Momento 3: Resolución</div><div class="small text-muted">01. Mediación Pacífica</div></td>
                            <td class="text-center"><span class="badge bg-success-subtle text-success border px-3">Completado</span></td>
                            <td class="text-end pe-4"><span class="small text-muted fw-semibold"><i class="fas fa-check-double text-success me-1"></i>Reportado</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
