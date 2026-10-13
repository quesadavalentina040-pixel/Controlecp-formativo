@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">

    {{-- Breadcrumb --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.administrador.inicio') }}" class="text-decoration-none" style="color:#2E7D32;">
                            <i class="fas fa-house me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.usuarios.index') }}" class="text-decoration-none" style="color:#2E7D32;">Usuarios</a>
                    </li>
                    <li class="breadcrumb-item active" style="color:#1B5E20;font-weight:600;">Nuevo usuario</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color:#001A29;font-family:'Outfit',sans-serif;">
                <i class="fas fa-user-plus me-2" style="color:#39A900;"></i>Crear Usuario Manual
            </h2>
            <p class="text-muted small mb-0">Completa los datos del nuevo usuario de Control ECP</p>
        </div>
        <a href="{{ route('controlecp.usuarios.index') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger rounded-3 shadow-sm mb-4">
            <i class="fas fa-triangle-exclamation me-2"></i>
            <strong>Corrige los siguientes errores:</strong>
            <ul class="mb-0 mt-2 ps-3">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('controlecp.usuarios.store') }}" autocomplete="off">
        @csrf

        <div class="row g-4">

            {{-- ── Datos Personales ── --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold mb-0" style="color:#1B5E20;">
                            <i class="fas fa-id-card me-2"></i>Datos Personales
                        </h5>
                        <p class="text-muted small mb-3">Se guardarán en la tabla <code>people</code></p>
                    </div>
                    <div class="card-body px-4 pb-4">

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Tipo de documento <span class="text-danger">*</span></label>
                            <select name="document_type" class="form-select @error('document_type') is-invalid @enderror" required>
                                <option value="">— Seleccionar —</option>
                                @foreach(['Cédula de ciudadanía','Tarjeta de identidad','Cédula de extranjería','Pasaporte','Documento nacional de identidad','Registro civil','Número de Identificación Tributaria'] as $tipo)
                                    <option value="{{ $tipo }}" {{ old('document_type') === $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                                @endforeach
                            </select>
                            @error('document_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Número de documento <span class="text-danger">*</span></label>
                            <input type="text" name="document_number"
                                   value="{{ old('document_number') }}"
                                   class="form-control @error('document_number') is-invalid @enderror"
                                   placeholder="Ej: 1012345678" required maxlength="30">
                            @error('document_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Primer nombre <span class="text-danger">*</span></label>
                                <input type="text" name="first_name"
                                       value="{{ old('first_name') }}"
                                       class="form-control @error('first_name') is-invalid @enderror"
                                       placeholder="Ej: JUAN" required maxlength="100">
                                @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Primer apellido <span class="text-danger">*</span></label>
                                <input type="text" name="first_last_name"
                                       value="{{ old('first_last_name') }}"
                                       class="form-control @error('first_last_name') is-invalid @enderror"
                                       placeholder="Ej: PÉREZ" required maxlength="100">
                                @error('first_last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Segundo apellido</label>
                                <input type="text" name="second_last_name"
                                       value="{{ old('second_last_name') }}"
                                       class="form-control @error('second_last_name') is-invalid @enderror"
                                       placeholder="Ej: GÓMEZ" maxlength="100">
                                @error('second_last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label fw-semibold small">Teléfono</label>
                            <input type="text" name="telephone1"
                                   value="{{ old('telephone1') }}"
                                   class="form-control @error('telephone1') is-invalid @enderror"
                                   placeholder="Ej: 3001234567" maxlength="20">
                            @error('telephone1')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </div>
                </div>
            </div>

            {{-- ── Credenciales y Rol ── --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold mb-0" style="color:#1B5E20;">
                            <i class="fas fa-lock me-2"></i>Credenciales y Rol
                        </h5>
                        <p class="text-muted small mb-3">Se guardarán en <code>users</code> y <code>role_user</code></p>
                    </div>
                    <div class="card-body px-4 pb-4">

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Correo electrónico <span class="text-danger">*</span></label>
                            <input type="email" name="email"
                                   value="{{ old('email') }}"
                                   class="form-control @error('email') is-invalid @enderror"
                                   placeholder="usuario@sena.edu.co" required maxlength="191">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Nombre de usuario (nickname)</label>
                            <div class="input-group">
                                <span class="input-group-text text-muted">&#64;</span>
                                <input type="text" name="nickname"
                                       value="{{ old('nickname') }}"
                                       class="form-control @error('nickname') is-invalid @enderror"
                                       placeholder="Opcional — ej: jperez" maxlength="60">
                            </div>
                            @error('nickname')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div class="form-text">Si se deja vacío, el usuario inicia sesión solo con correo.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Contraseña inicial <span class="text-danger">*</span></label>
                            <input type="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Mínimo 8 caracteres" required minlength="8">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold small">Confirmar contraseña <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation"
                                   class="form-control"
                                   placeholder="Repite la contraseña" required minlength="8">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Rol en Control ECP <span class="text-danger">*</span></label>
                            <select name="rol_id" class="form-select @error('rol_id') is-invalid @enderror" required>
                                <option value="">— Seleccionar rol —</option>
                                @foreach($rolesEcp as $rol)
                                    <option value="{{ $rol->id }}" {{ old('rol_id') == $rol->id ? 'selected' : '' }}>
                                        {{ $rol->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('rol_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="alert alert-warning rounded-3 small py-2 px-3 mb-0">
                            <i class="fas fa-circle-info me-1"></i>
                            La contraseña se almacena <strong>hasheada</strong> (bcrypt). Nunca se guarda en texto plano.
                        </div>

                    </div>
                </div>
            </div>

        </div>

        {{-- Botones --}}
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('controlecp.usuarios.index') }}"
               class="btn btn-outline-secondary rounded-pill px-4">
                Cancelar
            </a>
            <button type="submit" class="btn rounded-pill px-5 text-white fw-semibold"
                    style="background-color:#39A900;border-color:#39A900;">
                <i class="fas fa-floppy-disk me-2"></i>Guardar Usuario
            </button>
        </div>

    </form>

</div>
@endsection
