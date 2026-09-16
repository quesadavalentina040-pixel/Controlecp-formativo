@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">

    {{-- Cabecera --}}
    <div class="mb-4 pb-2 border-bottom">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small text-muted">
                <li class="breadcrumb-item">
                    <a href="{{ route('controlecp.administrador.inventario') }}" class="text-decoration-none" style="color: #2E7D32;">
                        <i class="fas fa-boxes-stacked me-1"></i>Inventario
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page" style="color: #1B5E20; font-weight: 600;">
                    Nuevo Material
                </li>
            </ol>
        </nav>
        <h2 class="h3 fw-bold mb-0" style="color: #001A29; font-family: 'Outfit', sans-serif;">
            <i class="fas fa-plus-circle me-2" style="color: #39A900;"></i>Registrar Material
        </h2>
        <p class="text-muted small mb-0">Agrega un nuevo material escolar al inventario de Control ECP</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card shadow-sm border-0" style="border-radius: 16px;">
                <div class="card-body p-4">

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <strong><i class="fas fa-exclamation-triangle me-2"></i>Corrige los siguientes errores:</strong>
                            <ul class="mb-0 mt-2 ms-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('controlecp.administrador.inventario.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- Recuadro de imagen --}}
                        <div class="mb-4 text-center">
                            <label class="form-label fw-semibold d-block text-start">Imagen del material</label>
                            <label for="imagenInput" class="img-drop" id="imgDrop">
                                <img id="imgPreview" class="img-preview d-none" alt="Vista previa">
                                <div class="img-placeholder" id="imgPlaceholder">
                                    <i class="fas fa-cloud-arrow-up"></i>
                                    <span class="fw-semibold">Subir imagen</span>
                                    <small class="text-muted">JPG, PNG o WEBP · máx 2 MB</small>
                                </div>
                            </label>
                            <input type="file" name="imagen" id="imagenInput"
                                   accept="image/jpeg,image/png,image/webp" class="d-none">
                            @error('imagen')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Código <span class="text-danger">*</span></label>
                                <input type="text" name="codigo" value="{{ old('codigo') }}"
                                       class="form-control @error('codigo') is-invalid @enderror" placeholder="Ej: MAT-001" required>
                                @error('codigo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Nombre del material <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" value="{{ old('nombre') }}"
                                       class="form-control @error('nombre') is-invalid @enderror" placeholder="Ej: Cuaderno cuadriculado" required>
                                @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Descripción</label>
                                <textarea name="descripcion" rows="2"
                                          class="form-control @error('descripcion') is-invalid @enderror"
                                          placeholder="Detalles adicionales del material...">{{ old('descripcion') }}</textarea>
                                @error('descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Categoría <span class="text-danger">*</span></label>
                                <select name="categoria" class="form-select @error('categoria') is-invalid @enderror">
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat }}" {{ old('categoria') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                    @endforeach
                                </select>
                                @error('categoria')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Ubicación</label>
                                <input type="text" name="ubicacion" value="{{ old('ubicacion') }}"
                                       class="form-control @error('ubicacion') is-invalid @enderror" placeholder="Ej: Bodega 1 - Estante A">
                                @error('ubicacion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Cantidad <span class="text-danger">*</span></label>
                                <input type="number" name="cantidad" value="{{ old('cantidad', 0) }}" min="0"
                                       class="form-control @error('cantidad') is-invalid @enderror" required>
                                @error('cantidad')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Unidad <span class="text-danger">*</span></label>
                                <select name="unidad" class="form-select @error('unidad') is-invalid @enderror">
                                    @foreach($unidades as $u)
                                        <option value="{{ $u }}" {{ old('unidad') == $u ? 'selected' : '' }}>{{ $u }}</option>
                                    @endforeach
                                </select>
                                @error('unidad')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Stock mínimo <span class="text-danger">*</span></label>
                                <input type="number" name="stock_minimo" value="{{ old('stock_minimo', 0) }}" min="0"
                                       class="form-control @error('stock_minimo') is-invalid @enderror" required>
                                @error('stock_minimo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                                <select name="estado" class="form-select @error('estado') is-invalid @enderror">
                                    @foreach($estados as $e)
                                        <option value="{{ $e }}" {{ old('estado') == $e ? 'selected' : '' }}>{{ $e }}</option>
                                    @endforeach
                                </select>
                                @error('estado')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('controlecp.administrador.inventario') }}" class="btn btn-secondary rounded-pill px-4">
                                Cancelar
                            </a>
                            <button type="submit" class="btn text-white rounded-pill px-4 shadow-sm" style="background-color: #39A900;">
                                <i class="fas fa-save me-1"></i> Guardar material
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

</div>

@push('styles')
<style>
    .img-drop {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 220px;
        height: 220px;
        margin: 0 auto;
        background: #efece7;
        border: 2px dashed #cfc9c0;
        border-radius: 14px;
        cursor: pointer;
        overflow: hidden;
        position: relative;
        transition: border-color .2s ease, background .2s ease;
    }
    .img-drop:hover { border-color: #39A900; background: #f3f8f3; }
    .img-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        color: #a8a29a;
        text-align: center;
        padding: 12px;
    }
    .img-placeholder i { font-size: 2.2rem; }
    .img-preview {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>
@endpush

@push('scripts')
<script>
    const imagenInput   = document.getElementById('imagenInput');
    const imgPreview    = document.getElementById('imgPreview');
    const imgPlaceholder= document.getElementById('imgPlaceholder');

    imagenInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            imgPreview.src = e.target.result;
            imgPreview.classList.remove('d-none');
            imgPlaceholder.classList.add('d-none');
        };
        reader.readAsDataURL(file);
    });
</script>
@endpush
@endsection
