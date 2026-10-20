@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .drag-drop-box {
        border: 2px dashed #CBD5E1;
        border-radius: 16px;
        background-color: #F8FAFC;
        padding: 2.2rem 1.5rem;
        text-align: center;
        transition: all 0.2s ease-in-out;
        cursor: pointer;
    }
    .drag-drop-box:hover, .drag-drop-box.dragover {
        border-color: #39A900;
        background-color: #F0FDF4;
    }
    .preview-card {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #E2E8F0;
        box-shadow: 0 2px 8px rgba(0, 26, 41, 0.05);
        transition: transform 0.15s ease;
        position: relative;
    }
    .preview-card:hover {
        transform: translateY(-2px);
    }
    .preview-thumb {
        width: 100%;
        height: 110px;
        object-fit: cover;
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">

    <!-- Breadcrumb & Cabecera (Mockup Admin) -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.administrador.inicio') }}" class="text-decoration-none" style="color: #39A900;">
                            <i class="fas fa-house me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.administrador.inventario') }}" class="text-decoration-none" style="color: #39A900;">
                            Inventario
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page" style="color: #001A29; font-weight: 600;">
                        Registrar recurso
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: #001A29; font-family: 'Outfit', sans-serif;">
                Registrar recurso
            </h2>
            <p class="text-muted small mb-0">HU-014 &bull; Registra espacios, materiales o equipos para las actividades de la Escuela Cultura de Paz</p>
        </div>

        <a href="{{ route('controlecp.administrador.inventario') }}" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Volver al listado
        </a>
    </div>

    <!-- Errores de Validación (Criterio 2) -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <div class="fw-bold mb-1"><i class="fas fa-circle-exclamation me-2"></i>Por favor verifique los siguientes campos:</div>
            <ul class="mb-0 small ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Formulario 2 Columnas (Exacto Mockup Image 1) -->
    <form action="{{ route('controlecp.administrador.inventario.store') }}" method="POST" enctype="multipart/form-data" id="formRecurso">
        @csrf

        <div class="row g-4">
            <!-- Columna Izquierda: Formulario Principal (col-lg-8) -->
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm p-3 p-md-4" style="border-radius: 16px;">
                    <div class="row g-3">
                        <!-- Categoría * -->
                        <div class="col-12">
                            <label for="id_categoria_inventario" class="form-label fw-semibold small text-dark">
                                Categoría <span class="text-danger">*</span>
                            </label>
                            <select name="id_categoria_inventario" id="id_categoria_inventario" class="form-select rounded-3 @error('id_categoria_inventario') is-invalid @enderror" required>
                                <option value="">Selecciona una categoría</option>
                                @foreach ($categorias as $cat)
                                    <option value="{{ $cat->id_categoria_inventario }}" {{ old('id_categoria_inventario') == $cat->id_categoria_inventario ? 'selected' : '' }}>
                                        {{ $cat->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_categoria_inventario')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Nombre del recurso * -->
                        <div class="col-12">
                            <label for="nombre" class="form-label fw-semibold small text-dark">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" class="form-control rounded-3 @error('nombre') is-invalid @enderror" placeholder="Nombre del recurso" required maxlength="255">
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Cantidad * y Estado físico * (Fila de 2 columnas) -->
                        <div class="col-12 col-md-6">
                            <label for="cantidad_total" class="form-label fw-semibold small text-dark">
                                Cantidad <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="cantidad_total" id="cantidad_total" value="{{ old('cantidad_total', 1) }}" min="1" class="form-control rounded-3 @error('cantidad_total') is-invalid @enderror" placeholder="Cantidad" required>
                            @error('cantidad_total')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="estado_fisico" class="form-label fw-semibold small text-dark">
                                Estado físico <span class="text-danger">*</span>
                            </label>
                            <select name="estado_fisico" id="estado_fisico" class="form-select rounded-3 @error('estado_fisico') is-invalid @enderror" required>
                                <option value="">Selecciona estado físico</option>
                                <option value="Bueno" {{ old('estado_fisico', 'Bueno') == 'Bueno' ? 'selected' : '' }}>Bueno</option>
                                <option value="Regular" {{ old('estado_fisico') == 'Regular' ? 'selected' : '' }}>Regular</option>
                                <option value="Crítico" {{ old('estado_fisico') == 'Crítico' ? 'selected' : '' }}>Crítico</option>
                            </select>
                            @error('estado_fisico')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Umbrales de Semáforo (Criterio 9) -->
                        <div class="col-12 col-md-6">
                            <label for="umbral_regular" class="form-label fw-semibold small text-muted">
                                Cantidad Moderada (Semáforo Amarillo)
                            </label>
                            <input type="number" name="umbral_regular" id="umbral_regular" value="{{ old('umbral_regular') }}" min="0" class="form-control rounded-3" placeholder="Ej: 3 (Nivel regular)">
                            <div class="form-text small" style="font-size: 0.72rem;">Cuando baje de este valor, se pondrá en semáforo Amarillo.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="umbral_critico" class="form-label fw-semibold small text-muted">
                                Cantidad Mínima / Crítica (Semáforo Rojo)
                            </label>
                            <input type="number" name="umbral_critico" id="umbral_critico" value="{{ old('umbral_critico') }}" min="0" class="form-control rounded-3" placeholder="Ej: 1 (Alerta crítica)">
                            <div class="form-text small" style="font-size: 0.72rem;">Cuando baje de este valor, se pondrá en semáforo Rojo.</div>
                        </div>

                        <!-- Observación -->
                        <div class="col-12">
                            <label for="observaciones" class="form-label fw-semibold small text-dark">
                                Observación
                            </label>
                            <textarea name="observaciones" id="observaciones" rows="3" class="form-control rounded-3" placeholder="Observaciones adicionales">{{ old('observaciones') }}</textarea>
                        </div>

                        <!-- Fotos * (Caja Drag & Drop como Mockup 1) -->
                        <div class="col-12">
                            <label class="form-label fw-semibold small text-dark">
                                Fotos <span class="text-danger">*</span>
                            </label>
                            <div class="drag-drop-box" id="dropArea" onclick="document.getElementById('fotosInput').click()">
                                <i class="fas fa-cloud-arrow-up fa-2x mb-2" style="color: #39A900;"></i>
                                <div class="fw-semibold text-dark mb-1">
                                    Arrastra fotos aquí o <span style="color: #39A900; text-decoration: underline;">selecciona</span>
                                </div>
                                <div class="text-muted small">Formatos: JPG o PNG (máx. 4MB)</div>
                                <input type="file" name="fotos[]" id="fotosInput" class="d-none" multiple accept="image/jpeg,image/png" required onchange="manejarSeleccionFotos(this.files)">
                            </div>
                            @error('fotos')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Botones de Acción (Mockup: Cancelar y Guardar recurso) -->
                        <div class="col-12 mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                            <a href="{{ route('controlecp.administrador.inventario') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                Cancelar
                            </a>
                            <button type="submit" class="btn text-white rounded-pill px-4 shadow-sm" style="background-color: #39A900; border-color: #39A900;">
                                Guardar recurso
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Vista previa de fotos (col-lg-4 - Exacto Mockup Image 1) -->
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm p-3 p-md-4 h-100" style="border-radius: 16px;">
                    <h5 class="fw-bold mb-3" style="color: #001A29; font-size: 1rem;">
                        Vista previa de fotos
                    </h5>

                    <!-- Contenedor dinámico de miniaturas -->
                    <div id="contenedorPreviewFotos" class="row g-2">
                        <div class="col-12 text-center py-5 text-muted" id="placeholderVacioFotos">
                            <i class="fas fa-images fa-3x mb-2 text-secondary opacity-50"></i>
                            <div class="small fw-semibold">No hay fotos seleccionadas</div>
                            <div class="small text-muted" style="font-size: 0.75rem;">Las miniaturas de las fotografías aparecerán aquí una vez seleccionadas.</div>
                        </div>
                    </div>

                    <div class="mt-auto pt-3 border-top text-muted small" style="font-size: 0.75rem;">
                        <i class="fas fa-circle-check me-1 text-success"></i> La primera foto será asignada como imagen principal en el listado y reportes.
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const dropArea = document.getElementById('dropArea');
    const fotosInput = document.getElementById('fotosInput');
    const contenedorPreview = document.getElementById('contenedorPreviewFotos');
    const placeholderVacio = document.getElementById('placeholderVacioFotos');

    // Drag and drop events
    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropArea.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropArea.classList.remove('dragover');
        }, false);
    });

    dropArea.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        fotosInput.files = files;
        manejarSeleccionFotos(files);
    }, false);

    function manejarSeleccionFotos(files) {
        contenedorPreview.innerHTML = '';

        if (!files || files.length === 0) {
            contenedorPreview.appendChild(placeholderVacio);
            return;
        }

        Array.from(files).forEach((file, index) => {
            const ext = file.name.split('.').pop().toLowerCase();
            if (!['jpg', 'jpeg', 'png'].includes(ext)) {
                alert(`El archivo "${file.name}" no es válido. Solo se admiten formatos JPG o PNG.`);
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const col = document.createElement('div');
                col.className = 'col-6';
                col.innerHTML = `
                    <div class="preview-card bg-white">
                        <img src="${e.target.result}" alt="Preview" class="preview-thumb">
                        <div class="p-1 px-2 d-flex justify-content-between align-items-center bg-white border-top">
                            <span class="small text-truncate text-muted" style="font-size: 0.7rem; max-width: 80px;">${file.name}</span>
                            <span class="badge ${index === 0 ? 'bg-success' : 'bg-secondary'}" style="font-size: 0.65rem;">
                                ${index === 0 ? 'Principal' : '#' + (index + 1)}
                            </span>
                        </div>
                    </div>
                `;
                contenedorPreview.appendChild(col);
            };
            reader.readAsDataURL(file);
        });
    }
</script>
@endpush
@endsection
