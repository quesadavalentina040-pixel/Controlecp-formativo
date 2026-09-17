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
                        Asistencias
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: #001A29; font-family: 'Outfit', sans-serif;">
                <i class="fas fa-user-check me-2" style="color: #39A900;"></i>Registro de Asistencias
            </h2>
            <p class="text-muted small mb-0">Control ECP &bull; Control y reporte de asistencia a sesiones de talleres ECP</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('controlecp.instructor.inicio') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver al Inicio
            </a>
        </div>
    </div>

    <!-- Formulario de Registro de Asistencia -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 18px;">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3" style="color: #001A29;"><i class="fas fa-clipboard-user me-2" style="color: #39A900;"></i>Seleccionar Ficha y Sesión</h5>
            <div class="row g-3 align-items-end mb-4">
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Ficha a Cargo</label>
                    <select class="form-select rounded-pill">
                        <option value="">Ficha 2670192 - ADSO (Mañana)</option>
                        <option value="">Ficha 2670195 - Gestión de Mercados</option>
                        <option value="">Ficha 2558910 - Agroindustria</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Actividad / Sesión</label>
                    <select class="form-select rounded-pill">
                        <option value="">01. Reconocimiento y Gestión Emocional</option>
                        <option value="">02. Taller de Autorreflexión</option>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn text-white rounded-pill" style="background-color: #2E7D32;">Cargar Lista</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color: #E8F5E9; color: #1B5E20;" class="small">
                        <tr>
                            <th class="ps-3">Documento</th>
                            <th>Aprendiz</th>
                            <th class="text-center">Asistencia</th>
                            <th class="text-end pe-3">Observación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-3 fw-bold font-monospace">1012345678</td>
                            <td>Ana Sofía Martínez</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <input type="radio" class="btn-check" name="asist_1" id="a1_p" checked>
                                    <label class="btn btn-outline-success" for="a1_p">Presente</label>
                                    <input type="radio" class="btn-check" name="asist_1" id="a1_a">
                                    <label class="btn btn-outline-danger" for="a1_a">Ausente</label>
                                </div>
                            </td>
                            <td class="text-end pe-3"><input type="text" class="form-control form-control-sm rounded-pill d-inline-block" style="max-width: 200px;" placeholder="Nota opcional..."></td>
                        </tr>
                        <tr>
                            <td class="ps-3 fw-bold font-monospace">1098765432</td>
                            <td>Juan David Gómez</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <input type="radio" class="btn-check" name="asist_2" id="a2_p" checked>
                                    <label class="btn btn-outline-success" for="a2_p">Presente</label>
                                    <input type="radio" class="btn-check" name="asist_2" id="a2_a">
                                    <label class="btn btn-outline-danger" for="a2_a">Ausente</label>
                                </div>
                            </td>
                            <td class="text-end pe-3"><input type="text" class="form-control form-control-sm rounded-pill d-inline-block" style="max-width: 200px;" placeholder="Nota opcional..."></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-end">
                <button class="btn text-white rounded-pill px-4" style="background-color: #2E7D32;"><i class="fas fa-floppy-disk me-1"></i> Guardar Asistencia</button>
            </div>
        </div>
    </div>

</div>
@endsection
