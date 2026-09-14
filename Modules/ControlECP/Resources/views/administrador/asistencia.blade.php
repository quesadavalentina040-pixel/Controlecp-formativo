@extends('controlecp::components.layouts.dashboard')

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
                        Asistencia
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: #001A29; font-family: 'Outfit', sans-serif;">
                <i class="fas fa-user-check me-2" style="color: #39A900;"></i>Control de Asistencia
            </h2>
            <p class="text-muted small mb-0">Control ECP &bull; Flujo vinculado y registrado por el Rol Instructor</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('controlecp.administrador.inicio') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver al Inicio
            </a>
            <button type="button" class="btn text-white rounded-pill px-3 btn-sm" style="background-color: #39A900; border-color: #39A900;" onclick="simularPruebaAsistencia()">
                <i class="fas fa-arrows-rotate me-1" id="syncIconAsistencia"></i> Comprobar Estado
            </button>
        </div>
    </div>

    <!-- Alerta Principal: Aviso de Flujo Instructor -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 18px; background: linear-gradient(135deg, #E8F5E9 0%, #C8E6C9 100%); border-left: 6px solid #2E7D32 !important;">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm"
                     style="width: 60px; height: 60px; background: #FFFFFF; color: #2E7D32; font-size: 1.7rem;">
                    <i class="fas fa-chalkboard-user"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <h4 class="h5 fw-bold mb-0" style="color: #1B5E20;">
                            La asistencia es originada y registrada directamente por los Instructores
                        </h4>
                        <span class="badge rounded-pill" style="background-color: #2E7D32; font-size: 0.75rem;">
                            <i class="fas fa-user-tie me-1"></i> Dependencia de Rol Instructor
                        </span>
                    </div>
                    <p class="mb-0 text-dark" style="font-size: 0.93rem; line-height: 1.5;">
                        En el sistema <strong>Control ECP</strong>, la toma de asistencia se realiza en campo y en aula por parte del <strong>usuario Instructor</strong> asignado a cada ficha formativa y momento de paz. Esta sección se alimentará automáticamente una vez se cree y habilite el panel operativo del instructor.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas Informativas del Flujo de Asistencia -->
    <div class="row g-3 mb-4">
        <!-- Tarjeta 1: Registro en Tiempo Real por Instructor -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; transition: transform .2s ease;">
                <div class="card-body p-3 text-center">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3"
                         style="width: 50px; height: 50px; background: #E8F8F5; color: #16A085; font-size: 1.3rem;">
                        <i class="fas fa-clipboard-user"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-2">Captura por Instructor</h6>
                    <p class="small text-muted mb-0">
                        Los instructores registran el estado de asistencia (asistió, excusa, falta o retraso) durante cada jornada.
                    </p>
                </div>
            </div>
        </div>

        <!-- Tarjeta 2: Cruce con Fichas y Aprendices -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; transition: transform .2s ease;">
                <div class="card-body p-3 text-center">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3"
                         style="width: 50px; height: 50px; background: #EBF5FB; color: #2980B9; font-size: 1.3rem;">
                        <i class="fas fa-list-check"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-2">Listas Automáticas</h6>
                    <p class="small text-muted mb-0">
                        La lista de aprendices se toma directamente de las fichas consultadas a través de la API Central.
                    </p>
                </div>
            </div>
        </div>

        <!-- Tarjeta 3: Asociación a Momentos ECP -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; transition: transform .2s ease;">
                <div class="card-body p-3 text-center">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3"
                         style="width: 50px; height: 50px; background: #FEF9E7; color: #D68910; font-size: 1.3rem;">
                        <i class="fas fa-heart-pulse"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-2">Momentos de Paz</h6>
                    <p class="small text-muted mb-0">
                        Cada registro de asistencia queda vinculado al momento de cultura de paz y actividad programada.
                    </p>
                </div>
            </div>
        </div>

        <!-- Tarjeta 4: Trazabilidad y Consolidado -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; transition: transform .2s ease;">
                <div class="card-body p-3 text-center">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3"
                         style="width: 50px; height: 50px; background: #F4ECF7; color: #8E44AD; font-size: 1.3rem;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-2">Consolidado Central</h6>
                    <p class="small text-muted mb-0">
                        El administrador tendrá acceso a estadísticas, reportes de permanencia y generación de certificados.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de Estado Técnico del Flujo de Asistencia -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0" style="color: #001A29;">
                    <i class="fas fa-diagram-project me-2 text-success"></i>Estado del Módulo de Asistencia
                </h5>
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 rounded-pill">
                    <i class="fas fa-hourglass-half me-1"></i> En Espera de Rol Instructor
                </span>
            </div>
        </div>
        <div class="card-body px-4 pb-4">
            <div class="row g-3 align-items-center">
                <div class="col-lg-8">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0 align-middle">
                            <tbody class="small">
                                <tr>
                                    <th class="bg-light text-muted" style="width: 35%;">Módulo Emisor de Datos:</th>
                                    <td class="fw-semibold text-dark">Panel de Gestión del Instructor (Control ECP)</td>
                                </tr>
                                <tr>
                                    <th class="bg-light text-muted">Requisito Previo:</th>
                                    <td>Creación y asignación de usuarios con rol <code class="text-success fw-bold">controlecp.instructor</code></td>
                                </tr>
                                <tr>
                                    <th class="bg-light text-muted">Integración Relacionada:</th>
                                    <td>Fichas académicas y nóminas de aprendices (API Central)</td>
                                </tr>
                                <tr>
                                    <th class="bg-light text-muted">Estado del Flujo:</th>
                                    <td>
                                        <span class="text-success fw-bold">
                                            <i class="fas fa-check-circle me-1"></i> Estructura y vista administrativa listas para vinculación
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-4 text-center">
                    <div class="p-3 rounded-4" style="background-color: #F8F9FA; border: 2px dashed #C8E6C9;">
                        <i class="fas fa-user-clock fa-2x mb-2" style="color: #2E7D32;"></i>
                        <div class="fw-bold small text-dark">Monitoreo de Asistencia</div>
                        <p class="text-muted mb-2" style="font-size: 0.78rem;">
                            Cuando los instructores inicien la toma de asistencia diaria, este panel consolidará las métricas y listados en tiempo real.
                        </p>
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="simularPruebaAsistencia()">
                            <i class="fas fa-plug me-1"></i> Probar Conexión
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerta Informativa Flotante -->
    <div id="apiAsistenciaStatusMessage" class="alert alert-success alert-dismissible fade d-none shadow-sm mb-3" role="alert" style="border-radius: 12px;">
        <div class="d-flex align-items-center gap-2">
            <i class="fas fa-circle-check fa-lg text-success"></i>
            <div>
                <strong>Flujo preparado:</strong> La vista administrativa de asistencia está sincronizada con la arquitectura del sistema y lista para recibir las tomas de asistencia del usuario instructor.
            </div>
        </div>
        <button type="button" class="btn-close" onclick="cerrarAlertaAsistencia()"></button>
    </div>

</div>

<script>
    function simularPruebaAsistencia() {
        const syncIcon = document.getElementById('syncIconAsistencia');
        const alertBox = document.getElementById('apiAsistenciaStatusMessage');
        
        if (syncIcon) {
            syncIcon.classList.add('fa-spin');
        }

        setTimeout(() => {
            if (syncIcon) {
                syncIcon.classList.remove('fa-spin');
            }
            if (alertBox) {
                alertBox.classList.remove('d-none');
                alertBox.classList.add('show');
            }
        }, 600);
    }

    function cerrarAlertaAsistencia() {
        const alertBox = document.getElementById('apiAsistenciaStatusMessage');
        if (alertBox) {
            alertBox.classList.remove('show');
            alertBox.classList.add('d-none');
        }
    }
</script>
@endsection
