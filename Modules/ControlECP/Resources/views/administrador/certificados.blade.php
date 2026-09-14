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
                        Certificados
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: #001A29; font-family: 'Outfit', sans-serif;">
                <i class="fas fa-certificate me-2" style="color: #39A900;"></i>Emisión de Certificados
            </h2>
            <p class="text-muted small mb-0">Control ECP &bull; Acreditación, verificación y constancias de participación</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('controlecp.administrador.inicio') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver al Inicio
            </a>
            <button type="button" class="btn text-white rounded-pill px-3 btn-sm" style="background-color: #39A900; border-color: #39A900;" onclick="simularPruebaCertificados()">
                <i class="fas fa-arrows-rotate me-1" id="syncIconCertificados"></i> Comprobar Motor
            </button>
        </div>
    </div>

    <!-- Alerta Principal: Aviso de Motor de Certificados -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 18px; background: linear-gradient(135deg, #E8F5E9 0%, #C8E6C9 100%); border-left: 6px solid #2E7D32 !important;">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm"
                     style="width: 60px; height: 60px; background: #FFFFFF; color: #2E7D32; font-size: 1.7rem;">
                    <i class="fas fa-award"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <h4 class="h5 fw-bold mb-0" style="color: #1B5E20;">
                            La certificación es generada tras el cumplimiento de asistencia y actividades
                        </h4>
                        <span class="badge rounded-pill" style="background-color: #2E7D32; font-size: 0.75rem;">
                            <i class="fas fa-stamp me-1"></i> Acreditación Automatizada
                        </span>
                    </div>
                    <p class="mb-0 text-dark" style="font-size: 0.93rem; line-height: 1.5;">
                        En <strong>Control ECP</strong>, la emisión de certificados de Escuela Cultura de Paz se calcula en base al registro consolidado de <strong>asistencias mínimas</strong> y la culminación de los <strong>momentos de paz y actividades</strong> dictados por los instructores. Los certificados contarán con código QR de verificación institucional.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas Informativas del Proceso de Certificación -->
    <div class="row g-3 mb-4">
        <!-- Tarjeta 1: Cumplimiento de Asistencia -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; transition: transform .2s ease;">
                <div class="card-body p-3 text-center">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3"
                         style="width: 50px; height: 50px; background: #E8F8F5; color: #16A085; font-size: 1.3rem;">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-2">Porcentaje de Asistencia</h6>
                    <p class="small text-muted mb-0">
                        Validación automática del umbral mínimo de asistencia exigido para cada ficha formativa.
                    </p>
                </div>
            </div>
        </div>

        <!-- Tarjeta 2: Acreditación Digital con QR -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; transition: transform .2s ease;">
                <div class="card-body p-3 text-center">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3"
                         style="width: 50px; height: 50px; background: #EBF5FB; color: #2980B9; font-size: 1.3rem;">
                        <i class="fas fa-qrcode"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-2">Validación con QR</h6>
                    <p class="small text-muted mb-0">
                        Cada constancia emitida cuenta con un identificador único y código QR de verificación pública.
                    </p>
                </div>
            </div>
        </div>

        <!-- Tarjeta 3: Descarga por Aprendices -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; transition: transform .2s ease;">
                <div class="card-body p-3 text-center">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3"
                         style="width: 50px; height: 50px; background: #FEF9E7; color: #D68910; font-size: 1.3rem;">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-2">Descarga en PDF</h6>
                    <p class="small text-muted mb-0">
                        Los aprendices aprobados podrán descargar su certificado oficial directamente desde su portal.
                    </p>
                </div>
            </div>
        </div>

        <!-- Tarjeta 4: Auditoría y Reportes Centrales -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; transition: transform .2s ease;">
                <div class="card-body p-3 text-center">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3"
                         style="width: 50px; height: 50px; background: #F4ECF7; color: #8E44AD; font-size: 1.3rem;">
                        <i class="fas fa-boxes-packing"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-2">Auditoría Central</h6>
                    <p class="small text-muted mb-0">
                        Control total para el administrador de los certificados emitidos, anulados o en proceso.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de Estado Técnico del Motor de Certificados -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0" style="color: #001A29;">
                    <i class="fas fa-server me-2 text-success"></i>Estado del Servicio de Certificación
                </h5>
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 rounded-pill">
                    <i class="fas fa-hourglass-half me-1"></i> En Espera de Datos de Asistencia
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
                                    <th class="bg-light text-muted" style="width: 35%;">Motor de Emisión:</th>
                                    <td class="fw-semibold text-dark">Generador Digital ECP (PDF / QR institucional)</td>
                                </tr>
                                <tr>
                                    <th class="bg-light text-muted">Condición de Aprobación:</th>
                                    <td>Cumplimiento de asistencias y actividades registradas por los instructores</td>
                                </tr>
                                <tr>
                                    <th class="bg-light text-muted">Vínculo de Identidad:</th>
                                    <td>Documentos y nombres homologados desde la API Central</td>
                                </tr>
                                <tr>
                                    <th class="bg-light text-muted">Estado del Módulo:</th>
                                    <td>
                                        <span class="text-success fw-bold">
                                            <i class="fas fa-check-circle me-1"></i> Plantilla y flujo administrativo preparados para expedición
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-4 text-center">
                    <div class="p-3 rounded-4" style="background-color: #F8F9FA; border: 2px dashed #C8E6C9;">
                        <i class="fas fa-stamp fa-2x mb-2" style="color: #2E7D32;"></i>
                        <div class="fw-bold small text-dark">Generador de Constancias</div>
                        <p class="text-muted mb-2" style="font-size: 0.78rem;">
                            Las constancias oficiales se activarán automáticamente para cada ficha conforme finalicen los momentos de paz.
                        </p>
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="simularPruebaCertificados()">
                            <i class="fas fa-plug me-1"></i> Probar Motor
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerta Informativa Flotante -->
    <div id="apiCertificadosStatusMessage" class="alert alert-success alert-dismissible fade d-none shadow-sm mb-3" role="alert" style="border-radius: 12px;">
        <div class="d-flex align-items-center gap-2">
            <i class="fas fa-circle-check fa-lg text-success"></i>
            <div>
                <strong>Motor preparado:</strong> El sistema de certificación está configurado y a la espera de consolidar los registros de asistencia de los instructores.
            </div>
        </div>
        <button type="button" class="btn-close" onclick="cerrarAlertaCertificados()"></button>
    </div>

</div>

<script>
    function simularPruebaCertificados() {
        const syncIcon = document.getElementById('syncIconCertificados');
        const alertBox = document.getElementById('apiCertificadosStatusMessage');
        
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

    function cerrarAlertaCertificados() {
        const alertBox = document.getElementById('apiCertificadosStatusMessage');
        if (alertBox) {
            alertBox.classList.remove('show');
            alertBox.classList.add('d-none');
        }
    }
</script>
@endsection
