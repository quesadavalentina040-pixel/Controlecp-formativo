@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .drag-drop-box {
        border: 2px dashed #CBD5E1;
        border-radius: 16px;
        background-color: #F8FAFC;
        padding: 1.8rem 1.2rem;
        text-align: center;
        transition: all 0.2s ease-in-out;
        cursor: pointer;
    }
    .drag-drop-box:hover, .drag-drop-box.dragover {
        border-color: #39A900;
        background-color: #F0FDF4;
    }
    .photo-thumb-card {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #E2E8F0;
        box-shadow: 0 2px 6px rgba(0, 26, 41, 0.05);
        position: relative;
    }
    .photo-thumb-img {
        width: 100%;
        height: 100px;
        object-fit: cover;
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">

    <!-- Breadcrumb & Cabecera -->
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
                        Editar recurso: {{ $recurso->nombre }}
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: #001A29; font-family: 'Outfit', sans-serif;">
                Editar recurso
            </h2>
            <p class="text-muted small mb-0">HU-014 &bull; Modifica la información, cantidades, estado físico y fotografías del recurso</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('controlecp.administrador.inventario.show', $recurso->id_inventario) }}" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm btn-sm">
                <i class="fas fa-eye me-1"></i> Ver detalle
            </a>
            <a href="{{ route('controlecp.administrador.inventario') }}" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver al listado
            </a>
        </div>
    </div>

    <!-- Alertas Flash -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert" style="background-color: #f0fbf4; color: #001A29;">
            <i class="fas fa-check-circle me-2" style="color: #39A900;"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="fas fa-triangle-exclamation me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

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

    <!-- Formulario 2 Columnas -->
    <div class="row g-4">
        <!-- Columna Izquierda: Formulario Principal -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm p-3 p-md-4" style="border-radius: 16px;">
                <form action="{{ route('controlecp.administrador.inventario.update', $recurso->id_inventario) }}" method="POST" enctype="multipart/form-data" id="formEditarRecurso">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <!-- Categoría * -->
                        <div class="col-12">
                            <label for="id_categoria_inventario" class="form-label fw-semibold small text-dark">
                                Categoría <span class="text-danger">*</span>
                            </label>
                            <select name="id_categoria_inventario" id="id_categoria_inventario" class="form-select rounded-3 @error('id_categoria_inventario') is-invalid @enderror" required>
                                @foreach ($categorias as $cat)
                                    <option value="{{ $cat->id_categoria_inventario }}" {{ old('id_categoria_inventario', $recurso->id_categoria_inventario) == $cat->id_categoria_inventario ? 'selected' : '' }}>
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
                            <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $recurso->nombre) }}" class="form-control rounded-3 @error('nombre') is-invalid @enderror" required maxlength="255">
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Cantidad Total * y Estado Físico * -->
                        <div class="col-12 col-md-6">
                            <label for="cantidad_total" class="form-label fw-semibold small text-dark">
                                Cantidad Total <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="cantidad_total" id="cantidad_total" value="{{ old('cantidad_total', $recurso->cantidad_total) }}" min="1" class="form-control rounded-3" required>
                            <div class="form-text small" style="font-size: 0.72rem;">Disponible actual: <strong>{{ $recurso->cantidad_disponible }}</strong> unidad(es).</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="estado_fisico" class="form-label fw-semibold small text-dark">
                                Estado físico <span class="text-danger">*</span>
                            </label>
                            <select name="estado_fisico" id="estado_fisico" class="form-select rounded-3" required>
                                <option value="Bueno" {{ old('estado_fisico', $recurso->estado_fisico) == 'Bueno' ? 'selected' : '' }}>Bueno</option>
                                <option value="Regular" {{ old('estado_fisico', $recurso->estado_fisico) == 'Regular' ? 'selected' : '' }}>Regular</option>
                                <option value="Crítico" {{ old('estado_fisico', $recurso->estado_fisico) == 'Crítico' ? 'selected' : '' }}>Crítico</option>
                            </select>
                        </div>

                        <!-- Umbrales de Semáforo -->
                        <div class="col-12 col-md-6">
                            <label for="umbral_regular" class="form-label fw-semibold small text-muted">
                                Cantidad Moderada (Semáforo Amarillo)
                            </label>
                            <input type="number" name="umbral_regular" id="umbral_regular" value="{{ old('umbral_regular', $recurso->umbral_regular) }}" min="0" class="form-control rounded-3">
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="umbral_critico" class="form-label fw-semibold small text-muted">
                                Cantidad Mínima / Crítica (Semáforo Rojo)
                            </label>
                            <input type="number" name="umbral_critico" id="umbral_critico" value="{{ old('umbral_critico', $recurso->umbral_critico) }}" min="0" class="form-control rounded-3">
                        </div>

                        <!-- Observaciones -->
                        <div class="col-12">
                            <label for="observaciones" class="form-label fw-semibold small text-dark">
                                Observación
                            </label>
                            <textarea name="observaciones" id="observaciones" rows="3" class="form-control rounded-3">{{ old('observaciones', $recurso->observaciones) }}</textarea>
                        </div>

                        <!-- Agregar Nuevas Fotos -->
                        <div class="col-12">
                            <label class="form-label fw-semibold small text-dark">
                                Agregar más fotografías (opcional)
                            </label>
                            <div class="drag-drop-box" id="dropAreaEdit" onclick="document.getElementById('fotosEditInput').click()">
                                <i class="fas fa-cloud-arrow-up fa-2x mb-2" style="color: #39A900;"></i>
                                <div class="fw-semibold text-dark mb-1">
                                    Arrastra nuevas fotos aquí o <span style="color: #39A900; text-decoration: underline;">selecciona</span>
                                </div>
                                <div class="text-muted small">Formatos: JPG o PNG (máx. 4MB)</div>
                                <input type="file" name="fotos[]" id="fotosEditInput" class="d-none" multiple accept="image/jpeg,image/png" onchange="mostrarNuevasPreviews(this.files)">
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="col-12 mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                            <a href="{{ route('controlecp.administrador.inventario.show', $recurso->id_inventario) }}" class="btn btn-outline-secondary rounded-pill px-4">
                                Cancelar
                            </a>
                            <button type="submit" class="btn text-white rounded-pill px-4 shadow-sm" style="background-color: #39A900; border-color: #39A900;">
                                <i class="fas fa-save me-1"></i> Guardar cambios
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Columna Derecha: Fotos Actuales y Nuevas Previews -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm p-3 p-md-4 mb-4" style="border-radius: 16px;">
                <h5 class="fw-bold mb-3" style="color: #001A29; font-size: 1rem;">
                    Fotografías actuales ({{ $recurso->fotos->count() }})
                </h5>

                <div class="row g-2">
                    @forelse($recurso->fotos as $index => $foto)
                        <div class="col-6">
                            <div class="photo-thumb-card bg-white">
                                <img src="{{ asset($foto->ruta) }}" alt="Foto" class="photo-thumb-img">
                                <div class="p-1 px-2 d-flex justify-content-between align-items-center bg-white border-top">
                                    <span class="badge {{ $index === 0 ? 'bg-success' : 'bg-light text-dark' }}" style="font-size: 0.65rem;">
                                        {{ $index === 0 ? 'Principal' : '#' . ($index + 1) }}
                                    </span>

                                    @if($recurso->fotos->count() > 1)
                                        <form action="{{ route('controlecp.administrador.inventario.foto.eliminar', $foto->id_recurso_foto) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta fotografía?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger p-0 px-1 border-0" title="Eliminar foto">
                                                <i class="fas fa-trash-can" style="font-size: 0.75rem;"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-4 text-muted small">
                            No hay fotografías registradas.
                        </div>
                    @endforelse
                </div>

                <div class="mt-3 text-muted small" style="font-size: 0.73rem;">
                    <i class="fas fa-info-circle me-1 text-primary"></i> El recurso debe conservar al menos 1 fotografía mientras esté activo.
                </div>
            </div>

            <!-- Previews de nuevas fotos agregadas -->
            <div class="card border-0 shadow-sm p-3 p-md-4" style="border-radius: 16px;" id="cardNuevasPreviews" style="display: none;">
                <h5 class="fw-bold mb-3" style="color: #001A29; font-size: 0.95rem;">
                    Nuevas fotos por subir
                </h5>
                <div id="contenedorNuevasFotos" class="row g-2"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const dropAreaEdit = document.getElementById('dropAreaEdit');
    const fotosEditInput = document.getElementById('fotosEditInput');
    const contenedorNuevas = document.getElementById('contenedorNuevasFotos');
    const cardNuevas = document.getElementById('cardNuevasPreviews');

    ['dragenter', 'dragover'].forEach(eventName => {
        dropAreaEdit.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropAreaEdit.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropAreaEdit.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropAreaEdit.classList.remove('dragover');
        }, false);
    });

    dropAreaEdit.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        fotosEditInput.files = files;
        mostrarNuevasPreviews(files);
    }, false);

    function mostrarNuevasPreviews(files) {
        contenedorNuevas.innerHTML = '';
        if (!files || files.length === 0) return;

        Array.from(files).forEach((file, index) => {
            const ext = file.name.split('.').pop().toLowerCase();
            if (!['jpg', 'jpeg', 'png'].includes(ext)) {
                alert(`El archivo "${file.name}" no es válido. Solo JPG o PNG.`);
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const col = document.createElement('div');
                col.className = 'col-6';
                col.innerHTML = `
                    <div class="photo-thumb-card bg-white">
                        <img src="${e.target.result}" class="photo-thumb-img">
                        <div class="p-1 px-2 small text-truncate bg-white border-top text-muted" style="font-size: 0.68rem;">
                            ${file.name}
                        </div>
                    </div>
                `;
                contenedorNuevas.appendChild(col);
            };
            reader.readAsDataURL(file);
        });
    }
</script>
@endpush
@endsection
