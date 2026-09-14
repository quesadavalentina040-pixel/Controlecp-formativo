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
                        Asesorías
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: #001A29; font-family: 'Outfit', sans-serif;">
                <i class="fas fa-comments me-2" style="color: #39A900;"></i>Gestión de Asesorías
            </h2>
            <p class="text-muted small mb-0">Control ECP &bull; Orientación, mediación y acompañamiento en cultura de paz</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('controlecp.administrador.inicio') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver al Inicio
            </a>
            <button type="button" class="btn text-white rounded-pill px-3 btn-sm" style="background-color: #39A900; border-color: #39A900;" onclick="simularPruebaAsesorias()">
                <i class="fas fa-arrows-rotate me-1" id="syncIconAsesorias"></i> Comprobar Estado
            </button>
        </div>
    </div>

    <!-- Alerta Principal: Aviso de Flujo de Asesorías -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 18px; background: linear-gradient(135deg, #E8F5E9 0%, #C8E6C9 100%); border-left: 6px solid #2E7D32 !important;">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm"
                     style="width: 60px; height: 60px; background: #FFFFFF; color: #2E7D32; font-size: 1.7rem;">
                    <i class="fas fa-hand-holding-heart"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <h4 class="h5 fw-bold mb-0" style="color: #1B5E20;">
                            Las asesorías son solicitadas por aprendices y atendidas por instructores/orientadores
                        </h4>
                        <span class="badge rounded-pill" style="background-color: #2E7D32; font-size: 0.75rem;">
                            <i class="fas fa-people-arrows me-1"></i> Flujo de Acompañamiento
                        </span>
                    </div>
                    <p class="mb-0 text-dark" style="font-size: 0.93rem; line-height: 1.5;">
                        En el sistema <strong>Control ECP</strong>, las sesiones de asesoría y resolución pacífica de conflictos se gestionan a través de la interacción directa entre los <strong>aprendices solicitantes</strong> y los <strong>instructores orientadores</strong>. Este panel administrativo centralizará el historial y seguimiento una vez se habiliten los módulos operativos correspondientes.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas Informativas del Flujo de Asesorías -->
    <div class="row g-3 mb-4">
        <!-- Tarjeta 1: Solicitud y Agendamiento -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; transition: transform .2s ease;">
                <div class="card-body p-3 text-center">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3"
                         style="width: 50px; height: 50px; background: #E8F8F5; color: #16A085; font-size: 1.3rem;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-2">Solicitud de Orientación</h6>
                    <p class="small text-muted mb-0">
                        Los aprendices pueden agendar espacios de diálogo y mediación de forma voluntaria o remitida.
                    </p>
                </div>
            </div>
        </div>

        <!-- Tarjeta 2: Registro por Instructor/Orientador -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; transition: transform .2s ease;">
                <div class="card-body p-3 text-center">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3"
                         style="width: 50px; height: 50px; background: #EBF5FB; color: #2980B9; font-size: 1.3rem;">
                        <i class="fas fa-user-doctor"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-2">Atención Especializada</h6>
                    <p class="small text-muted mb-0">
                        Los instructores registran bitácoras, acuerdos de convivencia y planes de seguimiento personalizados.
                    </p>
                </div>
            </div>
        </div>

        <!-- Tarjeta 3: Confidencialidad y Ética -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; transition: transform .2s ease;">
                <div class="card-body p-3 text-center">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3"
                         style="width: 50px; height: 50px; background: #FEF9E7; color: #D68910; font-size: 1.3rem;">
                        <i class="fas fa-user-lock"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-2">Confidencialidad</h6>
                    <p class="small text-muted mb-0">
                        Manejo seguro y ético de la información, salvaguardando la privacidad y bienestar de cada aprendiz.
                    </p>
                </div>
            </div>
        </div>

        <!-- Tarjeta 4: Métricas de Convivencia -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; transition: transform .2s ease;">
                <div class="card-body p-3 text-center">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3"
                         style="width: 50px; height: 50px; background: #F4ECF7; color: #8E44AD; font-size: 1.3rem;">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-2">Indicadores ECP</h6>
                    <p class="small text-muted mb-0">
                        Monitoreo del clima de convivencia institucional y efectividad de las mediaciones formativas.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de Estado Técnico del Flujo de Asesorías -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0" style="color: #001A29;">
                    <i class="fas fa-diagram-project me-2 text-success"></i>Estado del Módulo de Asesorías
                </h5>
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 rounded-pill">
                    <i class="fas fa-hourglass-half me-1"></i> En Espera de Interacción de Roles
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
                                    <th class="bg-light text-muted" style="width: 35%;">Origen de Solicitudes:</th>
                                    <td class="fw-semibold text-dark">Portal del Aprendiz &bull; Remisión de Instructores</td>
                                </tr>
                                <tr>
                                    <th class="bg-light text-muted">Gestor de Atención:</th>
                                    <td>Instructores orientadores y equipo psicosocial de la Escuela Cultura de Paz</td>
                                </tr>
                                <tr>
                                    <th class="bg-light text-muted">Vínculo con Datos:</th>
                                    <td>Fichas académicas y perfiles de aprendices (API Central)</td>
                                </tr>
                                <tr>
                                    <th class="bg-light text-muted">Estado del Flujo:</th>
                                    <td>
                                        <span class="text-success fw-bold">
                                            <i class="fas fa-check-circle me-1"></i> Panel administrativo configurado y listo para trazabilidad
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-4 text-center">
                    <div class="p-3 rounded-4" style="background-color: #F8F9FA; border: 2px dashed #C8E6C9;">
                        <i class="fas fa-headset fa-2x mb-2" style="color: #2E7D32;"></i>
                        <div class="fw-bold small text-dark">Monitoreo de Asesorías</div>
                        <p class="text-muted mb-2" style="font-size: 0.78rem;">
                            Permite supervisar los casos atendidos, citas programadas y compromisos de mediación formativa.
                        </p>
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="simularPruebaAsesorias()">
                            <i class="fas fa-plug me-1"></i> Probar Conexión
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerta Informativa Flotante -->
    <div id="apiAsesoriasStatusMessage" class="alert alert-success alert-dismissible fade d-none shadow-sm mb-3" role="alert" style="border-radius: 12px;">
        <div class="d-flex align-items-center gap-2">
            <i class="fas fa-circle-check fa-lg text-success"></i>
            <div>
                <strong>Flujo preparado:</strong> La vista administrativa de asesorías está configurada y lista para recibir solicitudes y registros una vez activos los roles de aprendiz e instructor.
            </div>
        </div>
        <button type="button" class="btn-close" onclick="cerrarAlertaAsesorias()"></button>
    </div>

</div>

<script>
    function simularPruebaAsesorias() {
        const syncIcon = document.getElementById('syncIconAsesorias');
        const alertBox = document.getElementById('apiAsesoriasStatusMessage');
        
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

    function cerrarAlertaAsesorias() {
        const alertBox = document.getElementById('apiAsesoriasStatusMessage');
        if (alertBox) {
            alertBox.classList.remove('show');
            alertBox.classList.add('d-none');
        }
    }
</script>
@endsection
