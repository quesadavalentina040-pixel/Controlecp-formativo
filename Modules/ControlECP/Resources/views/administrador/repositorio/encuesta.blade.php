@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">

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
                        Repositorio &bull; Encuestas
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: #001A29;">
                <i class="fas fa-square-poll-vertical me-2" style="color: #39A900;"></i>Encuestas
            </h2>
            <p class="text-muted small mb-0">Control ECP &bull; Crea y gestiona encuestas para las actividades</p>
        </div>

        <button type="button" class="btn text-white rounded-pill px-3 btn-sm" style="background-color: #39A900; border-color: #39A900;" onclick="toggleCrear()">
            <i class="fas fa-plus me-1"></i> Nueva encuesta
        </button>
    </div>

    <div class="alert alert-warning small mb-4" style="border-radius: 12px;">
        <i class="fas fa-triangle-exclamation me-1"></i>
        Vista de interfaz — aún falta crear las tablas en base de datos y conectar el guardado real.
    </div>

    {{-- ===================== LISTADO DE ENCUESTAS ===================== --}}
    <div id="vistaListado">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
            <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
                <h5 class="fw-bold mb-0" style="color: #001A29;">
                    <i class="fas fa-list-check me-2 text-success"></i>Encuestas creadas
                </h5>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="small text-muted">
                                <th>#</th>
                                <th>Nombre de la encuesta</th>
                                <th>Actividad asociada</th>
                                <th>Preguntas</th>
                                <th>Estado</th>
                                <th>Fecha creación</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td class="fw-semibold">Percepción del taller de convivencia</td>
                                <td>Momento de bienestar &bull; Marzo</td>
                                <td><span class="badge rounded-pill bg-light text-dark border">6 preguntas</span></td>
                                <td><span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle">Activa</span></td>
                                <td>{{ now()->subDays(3)->format('d/m/Y') }}</td>
                                <td class="text-end">
                                    <a href="#" class="btn btn-sm btn-outline-secondary" title="Ver"><i class="fas fa-eye"></i></a>
                                    <a href="#" class="btn btn-sm btn-outline-primary" title="Editar"><i class="fas fa-pen"></i></a>
                                </td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td class="fw-semibold">Evaluación jornada de asesorías</td>
                                <td>Asesorías &bull; Abril</td>
                                <td><span class="badge rounded-pill bg-light text-dark border">4 preguntas</span></td>
                                <td><span class="badge rounded-pill bg-secondary-subtle text-secondary border">Borrador</span></td>
                                <td>{{ now()->subDays(1)->format('d/m/Y') }}</td>
                                <td class="text-end">
                                    <a href="#" class="btn btn-sm btn-outline-secondary" title="Ver"><i class="fas fa-eye"></i></a>
                                    <a href="#" class="btn btn-sm btn-outline-primary" title="Editar"><i class="fas fa-pen"></i></a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="text-muted small mt-3 mb-0">
                        <i class="fas fa-circle-info me-1"></i> Estos registros son de ejemplo. Se reemplazarán por datos reales cuando exista la tabla <code>encuestas</code>.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== CONSTRUCTOR DE ENCUESTA ===================== --}}
    <div id="vistaCrear" class="d-none">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
            <div class="card-body p-4">

                <h5 class="fw-bold mb-3" style="color: #001A29;">
                    <i class="fas fa-file-circle-plus me-2 text-success"></i>Datos generales de la encuesta
                </h5>

                <form id="formEncuesta">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="color:#1B5E20;">Nombre de la encuesta</label>
                            <input type="text" class="form-control" name="nombre" placeholder="Ej: Percepción del taller de convivencia" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="color:#1B5E20;">Actividad asociada</label>
                            <select class="form-select" name="actividad_id">
                                <option value="">Selecciona una actividad...</option>
                                <option>Momento de bienestar &bull; Marzo</option>
                                <option>Asesorías &bull; Abril</option>
                                <option>Jornada de asistencia &bull; Mayo</option>
                            </select>
                            <div class="form-text">Este listado se llenará desde el módulo de Actividades cuando esté conectado.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold" style="color:#1B5E20;">Descripción / objetivo</label>
                            <textarea class="form-control" rows="2" name="descripcion" placeholder="Breve descripción del propósito de esta encuesta"></textarea>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0" style="color: #001A29;">
                            <i class="fas fa-list-ol me-2 text-success"></i>Preguntas
                        </h5>
                        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3" onclick="agregarPregunta()">
                            <i class="fas fa-plus me-1"></i> Agregar pregunta
                        </button>
                    </div>

                    <div id="contenedorPreguntas">
                        {{-- Las preguntas se agregan aquí dinámicamente con JS --}}
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn text-white rounded-pill px-4" style="background-color: #2E7D32;">
                            <i class="fas fa-floppy-disk me-1"></i> Guardar encuesta
                        </button>
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="toggleCrear()">
                            Cancelar
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>

<template id="plantillaPregunta">
    <div class="card border-0 bg-light mb-3 pregunta-item" style="border-radius: 14px;">
        <div class="card-body p-3">
            <div class="row g-3 align-items-start">
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted">Texto de la pregunta</label>
                    <input type="text" class="form-control form-control-sm" name="preguntas[][texto]" placeholder="Escribe la pregunta...">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">Tipo de respuesta</label>
                    <select class="form-select form-select-sm" name="preguntas[][tipo]">
                        <option value="texto_corto">Texto corto</option>
                        <option value="opcion_multiple">Opción múltiple</option>
                        <option value="escala">Escala (1 a 5)</option>
                        <option value="si_no">Sí / No</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end justify-content-end">
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill w-100" onclick="this.closest('.pregunta-item').remove()">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    function toggleCrear() {
        document.getElementById('vistaListado').classList.toggle('d-none');
        document.getElementById('vistaCrear').classList.toggle('d-none');

        // Si se abre el formulario y no tiene preguntas, agrega una inicial
        const contenedor = document.getElementById('contenedorPreguntas');
        if (!document.getElementById('vistaCrear').classList.contains('d-none') && contenedor.children.length === 0) {
            agregarPregunta();
        }
    }

    function agregarPregunta() {
        const plantilla = document.getElementById('plantillaPregunta');
        const clon = plantilla.content.cloneNode(true);
        document.getElementById('contenedorPreguntas').appendChild(clon);
    }

    document.getElementById('formEncuesta')?.addEventListener('submit', function (e) {
        e.preventDefault();
        alert('Falta conectar este formulario al backend (ruta + controlador + tablas encuestas/preguntas) para guardarlo de verdad.');
    });
</script>
@endsection