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
                        Repositorio &bull; Historias
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: #001A29;">
                <i class="fas fa-file-medical me-2" style="color: #39A900;"></i>Historia de la Escuela Cultura de Paz
            </h2>
            <p class="text-muted small mb-0">Control ECP &bull; Origen y propósito de la escuela</p>
        </div>

        <button type="button" class="btn text-white rounded-pill px-3 btn-sm" style="background-color: #39A900; border-color: #39A900;" onclick="toggleEdicion()">
            <i class="fas fa-pen me-1"></i> Editar información
        </button>
    </div>

    {{-- ===================== MODO LECTURA ===================== --}}
    <div id="vistaLectura">

        {{-- 1. Por qué surge la idea --}}
        <div class="row align-items-center g-4 mb-5">
            <div class="col-md-7">
                <h4 class="fw-bold mb-2" style="color: #1B5E20;">
                    <i class="fas fa-lightbulb me-2" style="color: #39A900;"></i>¿Por qué surge la idea de crear la escuela?
                </h4>
                <p class="text-dark" style="font-size: 0.95rem; line-height: 1.6;">
                    La idea nace de identificar la necesidad de un espacio dedicado a la formación en convivencia,
                    resolución pacífica de conflictos y construcción de comunidad dentro del entorno educativo.
                    Se buscaba ofrecer a los aprendices herramientas prácticas para promover el respeto,
                    la tolerancia y el diálogo como base de una cultura de paz duradera.
                </p>
            </div>
            <div class="col-md-5 text-center">
                <img src="https://picsum.photos/seed/ecp1/500/320" alt="Origen de la escuela"
                     class="img-fluid rounded-4 shadow-sm" style="max-height: 260px; object-fit: cover; width: 100%;">
            </div>
        </div>

        {{-- 2. Cómo se creó (imagen a la izquierda) --}}
        <div class="row align-items-center g-4 mb-5 flex-md-row-reverse">
            <div class="col-md-7">
                <h4 class="fw-bold mb-2" style="color: #1B5E20;">
                    <i class="fas fa-people-group me-2" style="color: #39A900;"></i>¿Cómo se creó?
                </h4>
                <p class="text-dark" style="font-size: 0.95rem; line-height: 1.6;">
                    Se creó a partir de un trabajo conjunto entre instructores, personal administrativo
                    y aprendices interesados en el bienestar y la convivencia institucional, articulando
                    talleres, jornadas de sensibilización y actividades formativas que poco a poco
                    consolidaron un espacio permanente dentro de la institución.
                </p>
            </div>
            <div class="col-md-5 text-center">
                <img src="https://picsum.photos/seed/ecp2/500/320" alt="Cómo se creó"
                     class="img-fluid rounded-4 shadow-sm" style="max-height: 260px; object-fit: cover; width: 100%;">
            </div>
        </div>

        {{-- 3. Dónde queda ubicada --}}
        <div class="row align-items-center g-4 mb-5">
            <div class="col-md-7">
                <h4 class="fw-bold mb-2" style="color: #1B5E20;">
                    <i class="fas fa-location-dot me-2" style="color: #39A900;"></i>¿Dónde queda ubicada?
                </h4>
                <p class="text-dark" style="font-size: 0.95rem; line-height: 1.6;">
                    La Escuela Cultura de Paz funciona en la <strong>Casa del Lago</strong>, un espacio
                    pensado para el encuentro, el diálogo y las actividades formativas en un ambiente
                    tranquilo y propicio para la reflexión.
                </p>
            </div>
            <div class="col-md-5 text-center">
                <img src="https://picsum.photos/seed/ecp3/500/320" alt="Casa del Lago"
                     class="img-fluid rounded-4 shadow-sm" style="max-height: 260px; object-fit: cover; width: 100%;">
            </div>
        </div>

        {{-- 4. Personas encargadas (imagen a la izquierda) --}}
        <div class="row align-items-center g-4 mb-5 flex-md-row-reverse">
            <div class="col-md-7">
                <h4 class="fw-bold mb-2" style="color: #1B5E20;">
                    <i class="fas fa-user-tie me-2" style="color: #39A900;"></i>Personas encargadas
                </h4>
                <p class="text-dark" style="font-size: 0.95rem; line-height: 1.6;">
                    Un equipo de instructores y personal administrativo comprometido con la convivencia
                    y el bienestar institucional lidera la escuela, acompañando el diseño y ejecución de
                    sus actividades. <em>(Actualiza aquí los nombres reales del equipo cuando estén definidos.)</em>
                </p>
            </div>
            <div class="col-md-5 text-center">
                <img src="https://picsum.photos/seed/ecp4/500/320" alt="Personas encargadas"
                     class="img-fluid rounded-4 shadow-sm" style="max-height: 260px; object-fit: cover; width: 100%;">
            </div>
        </div>

        {{-- 5. Qué quiere lograr --}}
        <div class="row align-items-center g-4 mb-5">
            <div class="col-md-7">
                <h4 class="fw-bold mb-2" style="color: #1B5E20;">
                    <i class="fas fa-bullseye me-2" style="color: #39A900;"></i>¿Qué quiere lograr la Escuela Cultura de Paz?
                </h4>
                <p class="text-dark" style="font-size: 0.95rem; line-height: 1.6;">
                    Busca formar personas capaces de resolver conflictos de manera pacífica, fortalecer
                    los valores de respeto y tolerancia, y consolidar una comunidad educativa donde la
                    convivencia sana sea parte natural del día a día.
                </p>
            </div>
            <div class="col-md-5 text-center">
                <img src="https://picsum.photos/seed/ecp5/500/320" alt="Objetivo de la escuela"
                     class="img-fluid rounded-4 shadow-sm" style="max-height: 260px; object-fit: cover; width: 100%;">
            </div>
        </div>

    </div>

    {{-- ===================== MODO EDICIÓN ===================== --}}
    <div id="vistaEdicion" class="d-none">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
            <div class="card-body p-4">
                <div class="alert alert-warning small mb-4" style="border-radius: 12px;">
                    <i class="fas fa-triangle-exclamation me-1"></i>
                    Esta información aún no se guarda en base de datos — falta conectar el formulario al backend.
                </div>

                <form id="formHistoria">
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="color:#1B5E20;">¿Por qué surge la idea de crear la escuela?</label>
                        <textarea class="form-control" rows="3" name="por_que_surge"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="color:#1B5E20;">¿Cómo se creó?</label>
                        <textarea class="form-control" rows="3" name="como_se_creo"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="color:#1B5E20;">¿Dónde queda ubicada?</label>
                        <input type="text" class="form-control" name="ubicacion" value="Casa del Lago">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="color:#1B5E20;">Personas encargadas</label>
                        <textarea class="form-control" rows="2" name="personas_encargadas"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold" style="color:#1B5E20;">¿Qué quiere lograr la Escuela Cultura de Paz?</label>
                        <textarea class="form-control" rows="3" name="que_quiere_lograr"></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn text-white rounded-pill px-4" style="background-color: #2E7D32;">
                            <i class="fas fa-floppy-disk me-1"></i> Guardar cambios
                        </button>
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="toggleEdicion()">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
    function toggleEdicion() {
        document.getElementById('vistaLectura').classList.toggle('d-none');
        document.getElementById('vistaEdicion').classList.toggle('d-none');
    }

    document.getElementById('formHistoria')?.addEventListener('submit', function (e) {
        e.preventDefault();
        alert('Falta conectar este formulario al backend (ruta + controlador + tabla) para guardar los cambios.');
    });
</script>
@endsection