@extends('senaempresa::components.layouts.master')

@section('content')

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-orange card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-plus-circle"></i> Nueva Sesión de Asistencia</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('senaempresa.asistencia.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="nombre">Nombre de la sesión <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror"
                                   value="{{ old('nombre') }}" required autofocus>
                            @error('nombre') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea name="descripcion" id="descripcion" rows="2" class="form-control">{{ old('descripcion') }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha">Fecha <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha" id="fecha" class="form-control @error('fecha') is-invalid @enderror"
                                           value="{{ old('fecha', date('Y-m-d')) }}" required>
                                    @error('fecha') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="hora_inicio">Hora inicio</label>
                                    <input type="time" name="hora_inicio" id="hora_inicio" class="form-control"
                                           value="{{ old('hora_inicio') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="hora_fin">Hora fin</label>
                                    <input type="time" name="hora_fin" id="hora_fin" class="form-control @error('hora_fin') is-invalid @enderror"
                                           value="{{ old('hora_fin') }}">
                                    @error('hora_fin') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="tipo">Tipo de sesión <span class="text-danger">*</span></label>
                            <select name="tipo" id="tipo" class="form-control @error('tipo') is-invalid @enderror" required>
                                <option value="PRESENCIAL" {{ old('tipo') == 'PRESENCIAL' ? 'selected' : '' }}>Presencial</option>
                                <option value="VIRTUAL" {{ old('tipo') == 'VIRTUAL' ? 'selected' : '' }}>Virtual</option>
                                <option value="MIXTA" {{ old('tipo') == 'MIXTA' ? 'selected' : '' }}>Mixta</option>
                            </select>
                            @error('tipo') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Crear Sesión</button>
                            <a href="{{ route('senaempresa.asistencia.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
