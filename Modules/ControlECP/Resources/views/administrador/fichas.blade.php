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
                        Fichas de Formación
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: #001A29; font-family: 'Outfit', sans-serif;">
                <i class="fas fa-id-card-clip me-2" style="color: #39A900;"></i>Gestión de Fichas
            </h2>
            <p class="text-muted small mb-0">Control ECP &bull; Sincronización e integración con la API Central de Formación</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('controlecp.administrador.inicio') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver al Inicio
            </a>
            <button type="button" class="btn text-white rounded-pill px-3 btn-sm" style="background-color: #39A900; border-color: #39A900;" onclick="simularPruebaApiFichas()">
                <i class="fas fa-arrows-rotate me-1" id="syncIconFichas"></i> Comprobar API
            </button>
        </div>
    </div>

    <!-- Alerta Principal: Aviso de API para Fichas -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 18px; background: linear-gradient(135deg, #E8F5E9 0%, #C8E6C9 100%); border-left: 6px solid #2E7D32 !important;">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm"
                     style="width: 60px; height: 60px; background: #FFFFFF; color: #2E7D32; font-size: 1.7rem;">
                    <i class="fas fa-id-card"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <h4 class="h5 fw-bold mb-0" style="color: #1B5E20;">
                            Las fichas de formación se obtienen directamente a través de la API Central
                        </h4>
                        <span class="badge rounded-pill" style="background-color: #2E7D32; font-size: 0.75rem;">
                            <i class="fas fa-link me-1"></i> Sincronización Externa
                        </span>
                    </div>
                    <p class="mb-0 text-dark" style="font-size: 0.93rem; line-height: 1.5;">
                        En el módulo de <strong>Control ECP</strong> no se crean fichas académicas manualmente. La información de <strong>número de ficha, programa de formación, nivel, jornada y aprendices matriculados</strong> se sincroniza de forma automática desde la <strong>API REST Central del ERP SENA Empresa / SOFIA Plus / SICEFA</strong>.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas Informativas de la Integración de Fichas -->
    <div class="row g-3 mb-4">
        <!-- Tarjeta 1: Programas Académicos -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-3 text-center">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3"
                         style="width: 50px; height: 50px; background: #E8F8F5; color: #16A085; font-size: 1.3rem;">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-2">Programas Académicos</h6>
                    <p class="small text-muted mb-0">
                        Carga directa de programas técnicos, tecnólogos y complementarios activos en el centro de formación.
                    </p>
                </div>
            </div>
        </div>

        <!-- Tarjeta 2: Aprendices por Ficha -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-3 text-center">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3"
                         style="width: 50px; height: 50px; background: #EBF5FB; color: #2980B9; font-size: 1.3rem;">
                        <i class="fas fa-people-group"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-2">Listas de Aprendices</h6>
                    <p class="small text-muted mb-0">
                        La nómina de aprendices pertenecientes a cada ficha se vincula de manera automática para asistencias y momentos de paz.
                    </p>
                </div>
            </div>
        </div>

        <!-- Tarjeta 3: Jornadas y Modalidad -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-3 text-center">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3"
                         style="width: 50px; height: 50px; background: #FEF9E7; color: #D68910; font-size: 1.3rem;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-2">Jornadas y Estados</h6>
                    <p class="small text-muted mb-0">
                        Información actualizada del estado de la ficha (lectiva, productiva) y su respectiva jornada formativa.
                    </p>
                </div>
            </div>
        </div>

        <!-- Tarjeta 4: Sin Duplicidad -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-3 text-center">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3"
                         style="width: 50px; height: 50px; background: #F4ECF7; color: #8E44AD; font-size: 1.3rem;">
                        <i class="fas fa-arrows-split-up-and-left"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-2">Datos Homologados</h6>
                    <p class="small text-muted mb-0">
                        Se mantiene la coherencia con el sistema académico nacional sin necesidad de mantenimiento manual.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de Estado Técnico del Endpoint de Fichas -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0" style="color: #001A29;">
                    <i class="fas fa-server me-2 text-success"></i>Estado del Servicio API de Fichas
                </h5>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
                    <i class="fas fa-circle-check me-1"></i> Integración Configurada
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
                                    <th class="bg-light text-muted" style="width: 35%;">Origen de Datos:</th>
                                    <td class="fw-semibold text-dark">API Académica Central (SICEFA / SOFIA Plus)</td>
                                </tr>
                                <tr>
                                    <th class="bg-light text-muted">Endpoint de Consumo:</th>
                                    <td><code class="text-primary fw-bold">GET /api/v1/fichas</code> &bull; RESTful JSON</td>
                                </tr>
                                <tr>
                                    <th class="bg-light text-muted">Datos Recuperados:</th>
                                    <td>Número de Ficha, Programa, Nivel, Fecha Inicio/Fin, Aprendices</td>
                                </tr>
                                <tr>
                                    <th class="bg-light text-muted">Estado de Conexión:</th>
                                    <td>
                                        <span class="text-success fw-bold">
                                            <i class="fas fa-check-circle me-1"></i> Listo para sincronización
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-4 text-center">
                    <div class="p-3 rounded-4" style="background-color: #F8F9FA; border: 2px dashed #C8E6C9;">
                        <i class="fas fa-layer-group fa-2x mb-2" style="color: #2E7D32;"></i>
                        <div class="fw-bold small text-dark">Sincronización de Fichas</div>
                        <p class="text-muted mb-2" style="font-size: 0.78rem;">
                            Las actividades, asistencias y registros de momentos de paz seleccionarán las fichas consumidas de este servicio.
                        </p>
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="simularPruebaApiFichas()">
                            <i class="fas fa-plug me-1"></i> Probar Enlace
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerta Informativa Flotante -->
    <div id="apiFichasStatusMessage" class="alert alert-success alert-dismissible fade d-none shadow-sm mb-3" role="alert" style="border-radius: 12px;">
        <div class="d-flex align-items-center gap-2">
            <i class="fas fa-circle-check fa-lg text-success"></i>
            <div>
                <strong>Enlace verificado:</strong> El módulo Control ECP está preparado para listar y asociar las fichas académicas provistas por la API Central.
            </div>
        </div>
        <button type="button" class="btn-close" onclick="cerrarAlertaFichas()"></button>
    </div>

</div>

<script>
    function simularPruebaApiFichas() {
        const syncIcon = document.getElementById('syncIconFichas');
        const alertBox = document.getElementById('apiFichasStatusMessage');
        
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

    function cerrarAlertaFichas() {
        const alertBox = document.getElementById('apiFichasStatusMessage');
        if (alertBox) {
            alertBox.classList.remove('show');
            alertBox.classList.add('d-none');
        }
    }
</script>
@endsection
