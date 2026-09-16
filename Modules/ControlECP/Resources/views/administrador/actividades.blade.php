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
                        Actividades
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: #001A29; font-family: 'Outfit', sans-serif;">
                <i class="fas fa-list-check me-2" style="color: #39A900;"></i>Catálogo de Actividades
            </h2>
            <p class="text-muted small mb-0">Control ECP &bull; 18 actividades estructuradas en los 3 Momentos Pedagógicos</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('controlecp.administrador.momentos') }}" class="btn btn-outline-success rounded-pill px-3">
                <i class="fas fa-star me-1"></i> Ver Momentos
            </a>
            <a href="{{ route('controlecp.administrador.inicio') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="fas fa-arrow-left me-1"></i> Volver al Inicio
            </a>
        </div>
    </div>

    <!-- Acordeón / Pestañas por Momento -->
    <div class="row g-4">

        <!-- Momento 1: Autoconciencia -->
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 18px;">
                <div class="card-header bg-success text-white p-3 px-4 d-flex justify-content-between align-items-center" style="border-top-left-radius: 18px; border-top-right-radius: 18px; background: linear-gradient(135deg, #1B5E20 0%, #2E7D32 100%) !important;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-brain fa-lg"></i>
                        <h5 class="fw-bold mb-0">Momento 1: Autoconciencia</h5>
                    </div>
                    <span class="badge bg-white text-success rounded-pill px-3 py-1 fw-bold">10 Actividades</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Nombre de la Actividad</th>
                                    <th>Objetivo Pedagógico</th>
                                    <th class="text-center">Duración Aprox.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-4 fw-bold text-success">01</td>
                                    <td class="fw-semibold">Reconocimiento y Gestión Emocional</td>
                                    <td class="small text-muted">Identificar la rueda de emociones y tecnicas de autorregulación.</td>
                                    <td class="text-center"><span class="badge bg-light text-dark border">2 Horas</span></td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-bold text-success">02</td>
                                    <td class="fw-semibold">Taller de Autorreflexión y Autoconcepto</td>
                                    <td class="small text-muted">Explorar la percepción personal y fortalezas individuales.</td>
                                    <td class="text-center"><span class="badge bg-light text-dark border">2 Horas</span></td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-bold text-success">03</td>
                                    <td class="fw-semibold">Proyecto de Vida y Sueños Integrales</td>
                                    <td class="small text-muted">Diseñar metas a corto, mediano y largo plazo.</td>
                                    <td class="text-center"><span class="badge bg-light text-dark border">3 Horas</span></td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-bold text-success">04</td>
                                    <td class="fw-semibold">Identificación de Fortalezas y Oportunidades</td>
                                    <td class="small text-muted">Matriz DOFA personal aplicada al contexto SENA.</td>
                                    <td class="text-center"><span class="badge bg-light text-dark border">2 Horas</span></td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-bold text-success">05</td>
                                    <td class="fw-semibold">Expresón Asertiva de Sentimientos</td>
                                    <td class="small text-muted">Comunicar emociones de manera clara y respetuosa.</td>
                                    <td class="text-center"><span class="badge bg-light text-dark border">2 Horas</span></td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-bold text-success">06</td>
                                    <td class="fw-semibold">Mapeo Corporal y Autocuidado</td>
                                    <td class="small text-muted">Reconocer somatizaciones y promover la salud mental.</td>
                                    <td class="text-center"><span class="badge bg-light text-dark border">2 Horas</span></td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-bold text-success">07</td>
                                    <td class="fw-semibold">Control del Estrés e Impulsos</td>
                                    <td class="small text-muted">Técnicas de respiración y mindfulness básico.</td>
                                    <td class="text-center"><span class="badge bg-light text-dark border">1.5 Horas</span></td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-bold text-success">08</td>
                                    <td class="fw-semibold">Historia Personal y Raíces Culturales</td>
                                    <td class="small text-muted">Valorar el origen cultural e historia familiar.</td>
                                    <td class="text-center"><span class="badge bg-light text-dark border">2 Horas</span></td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-bold text-success">09</td>
                                    <td class="fw-semibold">Principios, Valores y Ética Personal</td>
                                    <td class="small text-muted">Alineación de valores con el entorno laboral e institucional.</td>
                                    <td class="text-center"><span class="badge bg-light text-dark border">2 Horas</span></td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-bold text-success">10</td>
                                    <td class="fw-semibold">Compromiso Conmigo Mismo</td>
                                    <td class="small text-muted">Firma de carta de compromiso personal y plan de acción.</td>
                                    <td class="text-center"><span class="badge bg-light text-dark border">1 Hora</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Momento 2: Convivencia -->
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 18px;">
                <div class="card-header bg-primary text-white p-3 px-4 d-flex justify-content-between align-items-center" style="border-top-left-radius: 18px; border-top-right-radius: 18px; background: linear-gradient(135deg, #0277BD 0%, #0288D1 100%) !important;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-people-holding-hands fa-lg"></i>
                        <h5 class="fw-bold mb-0">Momento 2: Convivencia</h5>
                    </div>
                    <span class="badge bg-white text-primary rounded-pill px-3 py-1 fw-bold">4 Actividades</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Actividad</th>
                                    <th>Objetivo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-3 fw-bold text-primary">01</td>
                                    <td class="fw-semibold">Círculos de Diálogo y Empatía</td>
                                    <td class="small text-muted">Desarrollar la escucha activa.</td>
                                </tr>
                                <tr>
                                    <td class="ps-3 fw-bold text-primary">02</td>
                                    <td class="fw-semibold">Dinámicas de Trabajo Colaborativo</td>
                                    <td class="small text-muted">Fortalecer la confianza grupal.</td>
                                </tr>
                                <tr>
                                    <td class="ps-3 fw-bold text-primary">03</td>
                                    <td class="fw-semibold">Comunicación No Violenta (CNV)</td>
                                    <td class="small text-muted">Práctica de lenguaje integrador.</td>
                                </tr>
                                <tr>
                                    <td class="ps-3 fw-bold text-primary">04</td>
                                    <td class="fw-semibold">Acuerdos de Convivencia y Respeto</td>
                                    <td class="small text-muted">Construcción de normas grupales.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Momento 3: Resolución -->
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 18px;">
                <div class="card-header text-white p-3 px-4 d-flex justify-content-between align-items-center" style="border-top-left-radius: 18px; border-top-right-radius: 18px; background: linear-gradient(135deg, #6A1B9A 0%, #8E24AA 100%) !important;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-scale-balanced fa-lg"></i>
                        <h5 class="fw-bold mb-0">Momento 3: Resolución</h5>
                    </div>
                    <span class="badge bg-white text-purple rounded-pill px-3 py-1 fw-bold" style="color: #6A1B9A !important;">4 Actividades</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Actividad</th>
                                    <th>Objetivo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-3 fw-bold" style="color: #6A1B9A;">01</td>
                                    <td class="fw-semibold">Taller de Mediación Pacífica</td>
                                    <td class="small text-muted">Técnicas de mediación de pares.</td>
                                </tr>
                                <tr>
                                    <td class="ps-3 fw-bold" style="color: #6A1B9A;">02</td>
                                    <td class="fw-semibold">Negociación Ganar-Ganar</td>
                                    <td class="small text-muted">Estrategias de consenso.</td>
                                </tr>
                                <tr>
                                    <td class="ps-3 fw-bold" style="color: #6A1B9A;">03</td>
                                    <td class="fw-semibold">Estudio de Casos y Role-Playing</td>
                                    <td class="small text-muted">Simulación de resolución pacífica.</td>
                                </tr>
                                <tr>
                                    <td class="ps-3 fw-bold" style="color: #6A1B9A;">04</td>
                                    <td class="fw-semibold">Plan de Acción y Pactos de Paz</td>
                                    <td class="small text-muted">Compromisos de convivencia pacífica.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
