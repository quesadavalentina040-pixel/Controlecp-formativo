@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    :root {
        --ecp-green: #39A900;
        --ecp-green-hover: #2e8800;
        --ecp-navy: #001A29;
    }
    .form-label { font-weight: 600; font-size: 0.88rem; color: var(--ecp-navy); }
    .required-star { color: #dc3545; }
    .card-form { border-radius: 18px; }
    .section-title {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #52796f;
    }
</style>
@endpush

@section('dashboard-content')
@php
    $isEdit   = isset($editUser);
    $pageTitle = $isEdit ? 'Editar Usuario' : 'Registrar Usuario';
    $person   = $isEdit ? $editUser->person : null;

    // Rol ECP actualmente asignado al usuario
    $currentRoleSlug = '';
    if ($isEdit) {
        $ecpRole = $editUser->roles->first(fn($r) => str_starts_with($r->slug, 'controlecp'));
        $currentRoleSlug = $ecpRole ? $ecpRole->slug : '';
    }
@endphp

<div class="container-fluid px-2 px-md-3 py-2">

    {{-- Breadcrumb --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.administrador.inicio') }}" class="text-decoration-none" style="color: var(--ecp-green);">
                            <i class="fas fa-house me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.administrador.usuarios') }}" class="text-decoration-none" style="color: var(--ecp-green);">
                            Usuarios
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page" style="color: var(--ecp-navy); font-weight: 600;">
                        {{ $pageTitle }}
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: var(--ecp-navy); font-family: 'Outfit', sans-serif;">
                <i class="fas {{ $isEdit ? 'fa-user-pen' : 'fa-user-plus' }} me-2" style="color: var(--ecp-green);"></i>{{ $pageTitle }}
            </h2>
            <p class="text-muted small mb-0">Control ECP &bull; {{ $isEdit ? 'Modifica los datos del usuario y su rol' : 'Completa el formulario para registrar un nuevo usuario' }}</p>
        </div>
        <a href="{{ route('controlecp.administrador.usuarios') }}" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fas fa-arrow-left me-1"></i> Volver al listado
        </a>
    </div>

    {{-- Alertas de errores de validación --}}
    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-4" style="border-radius: 14px;">
            <i class="fas fa-triangle-exclamation me-2"></i>
            <strong>Corrige los siguientes errores:</strong>
            <ul class="mb-0 mt-1 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4" style="border-radius: 14px;">
            <i class="fas fa-circle-xmark me-2"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Formulario principal --}}
    @if($isEdit)
        <form method="POST" action="{{ route('controlecp.administrador.usuarios.update', $editUser->id) }}" autocomplete="off">
            @method('PUT')
    @else
        <form method="POST" action="{{ route('controlecp.administrador.usuarios.store') }}" autocomplete="off">
    @endif
    @csrf

    <div class="row g-4">

        {{-- Columna izquierda: Datos personales --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm card-form mb-4">
                <div class="card-body p-4">
                    <p class="section-title mb-3"><i class="fas fa-person me-2"></i>Datos Personales</p>
                    <div class="row g-3">

                        {{-- Documento --}}
                        @if(!$isEdit)
                        <div class="col-md-4">
                            <label class="form-label">
                                Tipo de Documento
                            </label>
                            <select class="form-select" name="document_type">
                                <option value="Cédula de ciudadanía" selected>C.C.</option>
                                <option value="Tarjeta de identidad">T.I.</option>
                                <option value="Pasaporte">Pasaporte</option>
                                <option value="Cédula de extranjería">C.E.</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">
                                Número de Documento <span class="required-star">*</span>
                            </label>
                            <input type="text" class="form-control @error('document_number') is-invalid @enderror"
                                   name="document_number" value="{{ old('document_number') }}"
                                   placeholder="Ej: 1012345678" required>
                            @error('document_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @else
                        <div class="col-12">
                            <label class="form-label">Documento de Identidad</label>
                            <input type="text" class="form-control bg-light" readonly
                                   value="{{ $person ? $person->document_type . ': ' . $person->document_number : 'Sin documento' }}">
                        </div>
                        @endif

                        {{-- Nombres --}}
                        <div class="col-md-4">
                            <label class="form-label">
                                Primer Nombre <span class="required-star">*</span>
                            </label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                   name="first_name"
                                   value="{{ old('first_name', $person ? $person->first_name : '') }}"
                                   placeholder="Ej: JUAN" required>
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">
                                Primer Apellido <span class="required-star">*</span>
                            </label>
                            <input type="text" class="form-control @error('first_last_name') is-invalid @enderror"
                                   name="first_last_name"
                                   value="{{ old('first_last_name', $person ? $person->first_last_name : '') }}"
                                   placeholder="Ej: PÉREZ" required>
                            @error('first_last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Segundo Apellido</label>
                            <input type="text" class="form-control"
                                   name="second_last_name"
                                   value="{{ old('second_last_name', $person ? $person->second_last_name : '') }}"
                                   placeholder="Ej: GÓMEZ">
                        </div>

                        {{-- Teléfono --}}
                        <div class="col-md-6">
                            <label class="form-label">Teléfono / Celular</label>
                            <input type="text" class="form-control"
                                   name="telephone"
                                   value="{{ old('telephone', $person ? $person->telephone1 : '') }}"
                                   placeholder="Ej: 3001234567">
                        </div>

                        {{-- Correo --}}
                        <div class="col-md-6">
                            <label class="form-label">
                                Correo Electrónico <span class="required-star">*</span>
                            </label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   name="email"
                                   value="{{ old('email', $isEdit ? $editUser->email : '') }}"
                                   placeholder="usuario@misena.edu.co" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>

            {{-- Datos de acceso --}}
            <div class="card border-0 shadow-sm card-form">
                <div class="card-body p-4">
                    <p class="section-title mb-3"><i class="fas fa-key me-2"></i>Datos de Acceso al Sistema</p>
                    <div class="row g-3">

                        {{-- Nickname --}}
                        <div class="col-md-6">
                            <label class="form-label">
                                Nombre de Usuario (nickname) <span class="required-star">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="fas fa-at"></i></span>
                                <input type="text" class="form-control @error('nickname') is-invalid @enderror"
                                       name="nickname"
                                       value="{{ old('nickname', $isEdit ? $editUser->nickname : '') }}"
                                       placeholder="Ej: juan.perez" required>
                                @error('nickname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Contraseña --}}
                        <div class="col-md-6">
                            <label class="form-label">
                                Contraseña
                                @if($isEdit)
                                    <span class="text-muted fw-normal">(dejar vacío para no cambiarla)</span>
                                @else
                                    <span class="required-star">*</span>
                                @endif
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                       id="inputPassword" name="password"
                                       placeholder="{{ $isEdit ? 'Nueva contraseña (opcional)' : 'Mínimo 6 caracteres' }}"
                                       {{ !$isEdit ? 'required' : '' }}>
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePass()">
                                    <i class="fas fa-eye" id="iconEye"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- Columna derecha: Rol y estado --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm card-form mb-4">
                <div class="card-body p-4">
                    <p class="section-title mb-3"><i class="fas fa-shield-halved me-2"></i>Rol en Control ECP</p>

                    @foreach($rolesEcp as $role)
                        @php
                            $roleLabel = match(true) {
                                str_contains($role->slug, 'admin')      => ['icon' => 'fa-user-shield',   'color' => '#1e7000',  'bg' => '#e8f8e8'],
                                str_contains($role->slug, 'instructor') => ['icon' => 'fa-chalkboard-user','color' => '#0b5e94', 'bg' => '#ebf5fb'],
                                default                                 => ['icon' => 'fa-user-graduate',  'color' => '#9c6c03',  'bg' => '#fef9e7'],
                            };
                        @endphp
                        <label class="d-flex align-items-center gap-3 p-3 rounded-3 mb-2 border cursor-pointer"
                               style="cursor: pointer; background-color: {{ old('role_slug', $currentRoleSlug) === $role->slug ? $roleLabel['bg'] : 'transparent' }};"
                               id="labelRole_{{ $loop->index }}">
                            <input type="radio" name="role_slug" value="{{ $role->slug }}"
                                   class="form-check-input mt-0 flex-shrink-0"
                                   {{ old('role_slug', $currentRoleSlug) === $role->slug ? 'checked' : '' }}
                                   onchange="highlightRole()"
                                   required>
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width: 34px; height: 34px; background: {{ $roleLabel['bg'] }}; color: {{ $roleLabel['color'] }};">
                                <i class="fas {{ $roleLabel['icon'] }}"></i>
                            </div>
                            <div>
                                <div class="fw-semibold small text-dark">{{ $role->name }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ $role->description }}</div>
                            </div>
                        </label>
                    @endforeach

                    @error('role_slug')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Estado (solo visible en edición) --}}
            @if($isEdit)
            <div class="card border-0 shadow-sm card-form mb-4">
                <div class="card-body p-4">
                    <p class="section-title mb-3"><i class="fas fa-toggle-on me-2"></i>Estado del Usuario</p>
                    <div class="d-flex gap-3">
                        <label class="d-flex align-items-center gap-2 p-3 rounded-3 border flex-fill" style="cursor: pointer;">
                            <input type="radio" name="estado" value="Activo"
                                   class="form-check-input mt-0"
                                   {{ ($editUser->estado ?? 'Activo') === 'Activo' ? 'checked' : '' }}>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                <i class="fas fa-circle-check me-1"></i> Activo
                            </span>
                        </label>
                        <label class="d-flex align-items-center gap-2 p-3 rounded-3 border flex-fill" style="cursor: pointer;">
                            <input type="radio" name="estado" value="Inactivo"
                                   class="form-check-input mt-0"
                                   {{ ($editUser->estado ?? 'Activo') === 'Inactivo' ? 'checked' : '' }}>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1">
                                <i class="fas fa-ban me-1"></i> Inactivo
                            </span>
                        </label>
                    </div>
                    <div class="small text-muted mt-2">
                        <i class="fas fa-info-circle me-1"></i>
                        Un usuario Inactivo no puede iniciar sesión, pero su información y historial se conservan.
                    </div>
                </div>
            </div>
            @endif

            {{-- Botones --}}
            <div class="d-grid gap-2">
                <button type="submit" class="btn text-white rounded-pill py-2 fw-semibold shadow-sm"
                        style="background-color: var(--ecp-green); border-color: var(--ecp-green);">
                    <i class="fas fa-floppy-disk me-2"></i>
                    {{ $isEdit ? 'Guardar Cambios' : 'Registrar Usuario' }}
                </button>
                <a href="{{ route('controlecp.administrador.usuarios') }}"
                   class="btn btn-outline-secondary rounded-pill py-2">
                    <i class="fas fa-xmark me-1"></i> Cancelar
                </a>
            </div>
        </div>

    </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
    function togglePass() {
        const input = document.getElementById('inputPassword');
        const icon  = document.getElementById('iconEye');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    function highlightRole() {
        document.querySelectorAll('[id^="labelRole_"]').forEach(label => {
            const radio = label.querySelector('input[type="radio"]');
            label.style.backgroundColor = radio.checked ? '#f0fbf4' : 'transparent';
            label.style.borderColor     = radio.checked ? '#39A900' : '#dee2e6';
        });
    }

    // Aplicar highlight al cargar si hay un rol preseleccionado
    document.addEventListener('DOMContentLoaded', highlightRole);
</script>
@endpush
