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
                        Repositorio &bull; POE
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: #001A29;">
                <i class="fas fa-file-contract me-2" style="color: #39A900;"></i>Procedimientos Operativos Estándar (POE)
            </h2>
            <p class="text-muted small mb-0">Control ECP &bull; Documentos oficiales de los procesos de la escuela</p>
        </div>

        <button type="button" class="btn text-white rounded-pill px-3 btn-sm" style="background-color: #39A900; border-color: #39A900;" onclick="toggleSubir()">
            <i class="fas fa-upload me-1"></i> Subir POE
        </button>
    </div>

    <div class="alert alert-warning small mb-4" style="border-radius: 12px;">
        <i class="fas fa-triangle-exclamation me-1"></i>
        Vista de interfaz con datos de ejemplo — falta crear la tabla <code>poes</code>, el almacenamiento de archivos y la ruta de carga real.
    </div>

    {{-- ===================== FILTROS ===================== --}}
    <div id="vistaListado">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
            <div class="card-body p-3">
                <div class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" class="form-control border-start-0" placeholder="Buscar POE por nombre o código...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm">
                            <option value="">Todas las categorías</option>
                            <option>Bienestar</option>
                            <option>Convivencia</option>
                            <option>Asesorías</option>
                            <option>Administrativo</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm">
                            <option value="">Todos los estados</option>
                            <option>Vigente</option>
                            <option>En revisión</option>
                            <option>Obsoleto</option>
                        </select>
                    </div>
                    <div class="col-md-1 text-end">
                        <button class="btn btn-sm btn-outline-secondary" title="Limpiar filtros"><i class="fas fa-rotate-left"></i></button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===================== LISTADO DE DOCUMENTOS ===================== --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
            <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
                <h5 class="fw-bold mb-0" style="color: #001A29;">
                    <i class="fas fa-folder-tree me-2 text-success"></i>Documentos registrados
                </h5>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="small text-muted">
                                <th>Código</th>
                                <th>Nombre del procedimiento</th>
                                <th>Categoría</th>
                                <th>Líder actual</th>
                                <th>Trimestre vigente</th>
                                <th>Versión</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-light text-dark border">POE-001</span></td>
                                <td class="fw-semibold">Atención de conflictos entre aprendices</td>
                                <td>Convivencia</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                             style="width: 28px; height: 28px; background:#E8F5E9; color:#2E7D32; font-size:0.75rem; font-weight:700;">
                                            LM
                                        </div>
                                        <span class="small">Laura Martínez</span>
                                    </div>
                                </td>
                                <td><span class="badge rounded-pill bg-light text-dark border">2026-III</span></td>
                                <td>v2.1</td>
                                <td><span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle">Vigente</span></td>
                                <td class="text-end">
                                    <a href="#" class="btn btn-sm btn-outline-secondary" title="Ver"><i class="fas fa-eye"></i></a>
                                    <button type="button" class="btn btn-sm btn-outline-success" title="Historial de líderes" onclick="toggleHistorial(1)"><i class="fas fa-clock-rotate-left"></i></button>
                                    <a href="#" class="btn btn-sm btn-outline-primary" title="Editar"><i class="fas fa-pen"></i></a>
                                </td>
                            </tr>
                            <tr id="historial-1" class="d-none">
                                <td colspan="8" class="bg-light">
                                    <div class="p-3">
                                        <h6 class="fw-bold small text-muted mb-3"><i class="fas fa-clock-rotate-left me-1"></i> Historial de líderes &bull; POE-001</h6>
                                        <div class="d-flex flex-wrap gap-3">
                                            <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-white border">
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">2026-III</span>
                                                <span class="small fw-semibold">Laura Martínez</span>
                                                <span class="small text-muted">&bull; v2.1</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-white border">
                                                <span class="badge bg-secondary-subtle text-secondary border">2026-II</span>
                                                <span class="small fw-semibold">Carlos Rojas</span>
                                                <span class="small text-muted">&bull; v2.0</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-white border">
                                                <span class="badge bg-secondary-subtle text-secondary border">2026-I</span>
                                                <span class="small fw-semibold">Carlos Rojas</span>
                                                <span class="small text-muted">&bull; v1.5</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-light text-dark border">POE-002</span></td>
                                <td class="fw-semibold">Registro y seguimiento de asesorías</td>
                                <td>Asesorías</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                             style="width: 28px; height: 28px; background:#EBF5FB; color:#2980B9; font-size:0.75rem; font-weight:700;">
                                            JG
                                        </div>
                                        <span class="small">Julián Gómez</span>
                                    </div>
                                </td>
                                <td><span class="badge rounded-pill bg-light text-dark border">2026-III</span></td>
                                <td>v1.3</td>
                                <td><span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle">En revisión</span></td>
                                <td class="text-end">
                                    <a href="#" class="btn btn-sm btn-outline-secondary" title="Ver"><i class="fas fa-eye"></i></a>
                                    <button type="button" class="btn btn-sm btn-outline-success" title="Historial de líderes" onclick="toggleHistorial(2)"><i class="fas fa-clock-rotate-left"></i></button>
                                    <a href="#" class="btn btn-sm btn-outline-primary" title="Editar"><i class="fas fa-pen"></i></a>
                                </td>
                            </tr>
                            <tr id="historial-2" class="d-none">
                                <td colspan="8" class="bg-light">
                                    <div class="p-3">
                                        <h6 class="fw-bold small text-muted mb-3"><i class="fas fa-clock-rotate-left me-1"></i> Historial de líderes &bull; POE-002</h6>
                                        <div class="d-flex flex-wrap gap-3">
                                            <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-white border">
                                                <span class="badge bg-warning-subtle text-warning-emphasis border">2026-III</span>
                                                <span class="small fw-semibold">Julián Gómez</span>
                                                <span class="small text-muted">&bull; v1.3</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-white border">
                                                <span class="badge bg-secondary-subtle text-secondary border">2026-II</span>
                                                <span class="small fw-semibold">Daniela Pérez</span>
                                                <span class="small text-muted">&bull; v1.0</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-light text-dark border">POE-003</span></td>
                                <td class="fw-semibold">Organización de jornadas de bienestar</td>
                                <td>Bienestar</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                             style="width: 28px; height: 28px; background:#F4ECF7; color:#8E44AD; font-size:0.75rem; font-weight:700;">
                                            AT
                                        </div>
                                        <span class="small">Andrea Torres</span>
                                    </div>
                                </td>
                                <td><span class="badge rounded-pill bg-light text-dark border">2025-IV</span></td>
                                <td>v1.0</td>
                                <td><span class="badge rounded-pill bg-secondary-subtle text-secondary border">Obsoleto</span></td>
                                <td class="text-end">
                                    <a href="#" class="btn btn-sm btn-outline-secondary" title="Ver"><i class="fas fa-eye"></i></a>
                                    <button type="button" class="btn btn-sm btn-outline-success" title="Historial de líderes" onclick="toggleHistorial(3)"><i class="fas fa-clock-rotate-left"></i></button>
                                    <a href="#" class="btn btn-sm btn-outline-primary" title="Editar"><i class="fas fa-pen"></i></a>
                                </td>
                            </tr>
                            <tr id="historial-3" class="d-none">
                                <td colspan="8" class="bg-light">
                                    <div class="p-3">
                                        <h6 class="fw-bold small text-muted mb-3"><i class="fas fa-clock-rotate-left me-1"></i> Historial de líderes &bull; POE-003</h6>
                                        <div class="d-flex flex-wrap gap-3">
                                            <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-white border">
                                                <span class="badge bg-secondary-subtle text-secondary border">2025-IV</span>
                                                <span class="small fw-semibold">Andrea Torres</span>
                                                <span class="small text-muted">&bull; v1.0</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="text-muted small mt-3 mb-0">
                        <i class="fas fa-circle-info me-1"></i> Estos registros son de ejemplo. Se reemplazarán por documentos reales cuando exista la tabla <code>poes</code> y el almacenamiento de archivos.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== FORMULARIO SUBIR POE ===================== --}}
    <div id="vistaSubir" class="d-none">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color: #001A29;">
                    <i class="fas fa-file-circle-plus me-2 text-success"></i>Subir nuevo POE
                </h5>

                <form id="formPoe">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold" style="color:#1B5E20;">Código</label>
                            <input type="text" class="form-control" name="codigo" placeholder="Ej: POE-004">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold" style="color:#1B5E20;">Nombre del procedimiento</label>
                            <input type="text" class="form-control" name="nombre" placeholder="Ej: Protocolo de bienvenida a nuevos aprendices" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold" style="color:#1B5E20;">Categoría</label>
                            <select class="form-select" name="categoria">
                                <option>Bienestar</option>
                                <option>Convivencia</option>
                                <option>Asesorías</option>
                                <option>Administrativo</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold" style="color:#1B5E20;">Versión</label>
                            <input type="text" class="form-control" name="version" placeholder="Ej: v1.0" value="v1.0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold" style="color:#1B5E20;">Estado</label>
                            <select class="form-select" name="estado">
                                <option>Vigente</option>
                                <option>En revisión</option>
                                <option>Obsoleto</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="color:#1B5E20;">Líder responsable</label>
                            <select class="form-select" name="lider_id">
                                <option value="">Selecciona el líder actual...</option>
                                <option>Laura Martínez</option>
                                <option>Carlos Rojas</option>
                                <option>Julián Gómez</option>
                                <option>Andrea Torres</option>
                            </select>
                            <div class="form-text">Este listado se llenará desde el módulo de Usuarios/Instructores cuando esté conectado.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="color:#1B5E20;">Trimestre vigente</label>
                            <select class="form-select" name="trimestre">
                                <option>2026-III</option>
                                <option>2026-IV</option>
                                <option>2027-I</option>
                                <option>2027-II</option>
                            </select>
                            <div class="form-text">Al cambiar de líder cada trimestre, se guarda como una nueva versión en el historial.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold" style="color:#1B5E20;">Descripción breve</label>
                            <textarea class="form-control" rows="2" name="descripcion" placeholder="¿Qué proceso cubre este documento?"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold" style="color:#1B5E20;">Archivo (PDF)</label>
                            <input type="file" class="form-control" name="archivo" accept="application/pdf">
                            <div class="form-text">Aún no se guarda el archivo — falta configurar el almacenamiento (storage) en el backend.</div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn text-white rounded-pill px-4" style="background-color: #2E7D32;">
                            <i class="fas fa-floppy-disk me-1"></i> Guardar POE
                        </button>
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="toggleSubir()">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
    function toggleHistorial(id) {
        document.getElementById('historial-' + id).classList.toggle('d-none');
    }

    function toggleSubir() {
        document.getElementById('vistaListado').classList.toggle('d-none');
        document.getElementById('vistaSubir').classList.toggle('d-none');
    }

    document.getElementById('formPoe')?.addEventListener('submit', function (e) {
        e.preventDefault();
        alert('Falta conectar este formulario al backend (ruta + controlador + tabla poes + storage de archivos) para guardarlo de verdad.');
    });
</script>
@endsection