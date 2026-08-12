@extends('direccion::layouts.master')

@section('title', 'Nueva Política Directiva')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <!-- Header -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <a href="{{ route('direccion.index') }}" class="text-decoration-none text-muted fs-7 fw-semibold">
                        <i class="fas fa-arrow-left me-1"></i> Volver al listado
                    </a>
                    <h3 class="fw-bold text-dark mb-0 mt-1">Registrar Nueva Política Directiva</h3>
                </div>
            </div>

            <!-- Form Card -->
            <div class="card card-custom p-4 p-md-5">
                <form action="{{ route('direccion.store') }}" method="POST">
                    @csrf

                    <div class="row g-4">
                        <!-- Código y Tipo -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark fs-7">Código Identificador <span class="text-danger">*</span></label>
                            <input type="text" name="codigo" value="{{ old('codigo', $codigoSugerido) }}" class="form-control @error('codigo') is-invalid @enderror" placeholder="Ej: POL-2026-001" required>
                            @error('codigo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted fs-8">Identificador único de la directriz institucional.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark fs-7">Tipo de Proceso / Política <span class="text-danger">*</span></label>
                            <select name="tipo" class="form-select @error('tipo') is-invalid @enderror" required>
                                <option value="" disabled {{ old('tipo') ? '' : 'selected' }}>Seleccione una categoría</option>
                                <option value="Estratégica" {{ old('tipo') == 'Estratégica' ? 'selected' : '' }}>🟢 Estratégica (Direccionamiento Global)</option>
                                <option value="Calidad" {{ old('tipo') == 'Calidad' ? 'selected' : '' }}>🔵 Calidad (Procesos e Inocuidad)</option>
                                <option value="Seguridad" {{ old('tipo') == 'Seguridad' ? 'selected' : '' }}>🔴 Seguridad (SST y Bioseguridad)</option>
                                <option value="Operativa" {{ old('tipo') == 'Operativa' ? 'selected' : '' }}>⚪ Operativa (Funcionamiento Diario)</option>
                            </select>
                            @error('tipo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Título -->
                        <div class="col-12">
                            <label class="form-label fw-bold text-dark fs-7">Título de la Política o Directiva <span class="text-danger">*</span></label>
                            <input type="text" name="titulo" value="{{ old('titulo') }}" class="form-control @error('titulo') is-invalid @enderror" placeholder="Ej: Política de Calidad y Manejo de Insumos Agroindustriales" required>
                            @error('titulo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Descripción -->
                        <div class="col-12">
                            <label class="form-label fw-bold text-dark fs-7">Descripción y Lineamientos <span class="text-danger">*</span></label>
                            <textarea name="descripcion" rows="4" class="form-control @error('descripcion') is-invalid @enderror" placeholder="Describe los alcances, normativas y objetivos de esta política directiva..." required>{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Vigencia, Responsable y Estado -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark fs-7">Vigencia (Año) <span class="text-danger">*</span></label>
                            <input type="number" name="vigencia" value="{{ old('vigencia', date('Y')) }}" class="form-control @error('vigencia') is-invalid @enderror" min="2020" max="2050" required>
                            @error('vigencia')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark fs-7">Responsable / Área <span class="text-danger">*</span></label>
                            <input type="text" name="responsable" value="{{ old('responsable', 'Dirección General SENA Empresa') }}" class="form-control @error('responsable') is-invalid @enderror" required>
                            @error('responsable')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark fs-7">Estado Inicial <span class="text-danger">*</span></label>
                            <select name="estado" class="form-select @error('estado') is-invalid @enderror" required>
                                <option value="Activa" {{ old('estado', 'Activa') == 'Activa' ? 'selected' : '' }}>Activa</option>
                                <option value="En Revisión" {{ old('estado') == 'En Revisión' ? 'selected' : '' }}>En Revisión</option>
                                <option value="Inactiva" {{ old('estado') == 'Inactiva' ? 'selected' : '' }}>Inactiva</option>
                            </select>
                            @error('estado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex align-items-center justify-content-end gap-2 mt-5 pt-4 border-top">
                        <a href="{{ route('direccion.index') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-sena px-5 py-2 shadow-sm">
                            <i class="fas fa-save me-1"></i> Guardar Política
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
