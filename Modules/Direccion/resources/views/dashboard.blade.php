@extends('direccion::layouts.master')

@section('title', 'Dashboard Ejecutivo - Dirección')

@section('content')
<div class="row g-4">

    <!-- Welcome Hero Alert -->
    <div class="col-12">
        <div class="card border-0 rounded-4 shadow-sm overflow-hidden text-white" style="background: linear-gradient(135deg, var(--sena-dark) 0%, var(--sena-light-navy) 60%, var(--sena-green) 100%);">
            <div class="card-body p-4 p-md-5">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-white bg-opacity-15 border border-white border-opacity-25 mb-3">
                            <i class="fas fa-shield-alt text-warning"></i>
                            <span class="fs-7 fw-semibold">Panel de Control Estratégico</span>
                        </div>
                        @auth
                            <h2 class="fw-bold text-white mb-2 fs-3">
                                ¡Bienvenido(a), {{ Auth::user()->full_name }}!
                            </h2>
                            <p class="text-white-50 fs-6 mb-3">
                                Estás conectado con el rol <strong class="text-white">{{ Auth::user()->primary_role }}</strong>. Desde aquí puedes supervisar la formulación de directrices corporativas y alineación de las unidades de SENA Empresa.
                            </p>
                        @else
                            <h2 class="fw-bold text-white mb-2 fs-3">
                                Tablero de Control y Gestión Estratégica
                            </h2>
                            <p class="text-white-50 fs-6 mb-3">
                                Supervisión general de directrices corporativas y alineación de las unidades de SENA Empresa.
                            </p>
                        @endauth
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('direccion.politicas.create') }}" class="btn btn-sena">
                                <i class="fas fa-plus-circle me-1"></i> Formular Nueva Política
                            </a>
                            <a href="{{ route('direccion.politicas.index') }}" class="btn btn-outline-light rounded-pill px-3 fw-semibold">
                                <i class="fas fa-list-check me-1"></i> Ver Catálogo Completo
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 text-center text-lg-end mt-4 mt-lg-0">
                        @auth
                            <div class="avatar-circle mx-auto mx-lg-end shadow-lg" style="width: 80px; height: 80px; font-size: 28px;">
                                {{ Auth::user()->initials }}
                            </div>
                            <div class="mt-2 text-white fw-bold">{{ Auth::user()->email }}</div>
                            <span class="badge bg-success bg-opacity-50 border border-light text-white px-3 py-1 rounded-pill mt-1">
                                {{ Auth::user()->primary_role }}
                            </span>
                        @else
                            <div class="avatar-circle mx-auto mx-lg-end shadow-lg" style="width: 80px; height: 80px; font-size: 28px;">
                                SE
                            </div>
                            <div class="mt-2 text-white fw-bold">Modo Consulta</div>
                            <a href="{{ route('login', ['redirect' => route('direccion.dashboard')]) }}" class="btn btn-sm btn-sena mt-2">
                                Iniciar Sesión
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat Cards KPI -->
    <div class="col-xl-3 col-md-6">
        <div class="card-custom p-4 text-center border-start border-4 border-primary">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fs-7 text-uppercase fw-semibold">Total Políticas</span>
                    <h3 class="fw-bold text-dark mb-0 fs-2 mt-1">{{ $totalPoliticas }}</h3>
                </div>
                <div class="rounded-circle p-3 bg-primary bg-opacity-10 text-primary fs-3">
                    <i class="fas fa-file-signature"></i>
                </div>
            </div>
            <div class="mt-3 pt-2 border-top text-start fs-8 text-muted">
                <i class="fas fa-database me-1 text-primary"></i> Decisiones corporativas registradas
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card-custom p-4 text-center border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fs-7 text-uppercase fw-semibold">Políticas Activas</span>
                    <h3 class="fw-bold text-success mb-0 fs-2 mt-1">{{ $activas }}</h3>
                </div>
                <div class="rounded-circle p-3 bg-success bg-opacity-10 text-success fs-3">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="mt-3 pt-2 border-top text-start fs-8 text-muted">
                <i class="fas fa-bolt me-1 text-success"></i> En plena vigencia operativa
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card-custom p-4 text-center border-start border-4 border-warning">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fs-7 text-uppercase fw-semibold">En Revisión</span>
                    <h3 class="fw-bold text-warning mb-0 fs-2 mt-1">{{ $enRevision }}</h3>
                </div>
                <div class="rounded-circle p-3 bg-warning bg-opacity-10 text-warning fs-3">
                    <i class="fas fa-clock-rotate-left"></i>
                </div>
            </div>
            <div class="mt-3 pt-2 border-top text-start fs-8 text-muted">
                <i class="fas fa-sync-alt me-1 text-warning"></i> Pendientes de aprobación final
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card-custom p-4 text-center border-start border-4 border-secondary">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fs-7 text-uppercase fw-semibold">Inactivas / Históricas</span>
                    <h3 class="fw-bold text-secondary mb-0 fs-2 mt-1">{{ $inactivas }}</h3>
                </div>
                <div class="rounded-circle p-3 bg-secondary bg-opacity-10 text-secondary fs-3">
                    <i class="fas fa-archive"></i>
                </div>
            </div>
            <div class="mt-3 pt-2 border-top text-start fs-8 text-muted">
                <i class="fas fa-history me-1 text-secondary"></i> Directrices archivadas
            </div>
        </div>
    </div>

    <!-- Breakdown By Type -->
    <div class="col-lg-5">
        <div class="card-custom p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h5 class="fw-bold text-dark mb-0 fs-5">
                    <i class="fas fa-chart-pie text-success me-2"></i> Distribución por Tipología
                </h5>
                <span class="badge bg-light text-muted border fs-8">Total: {{ $totalPoliticas }}</span>
            </div>

            @foreach($porTipo as $tipo => $cantidad)
                @php
                    $porcentaje = $totalPoliticas > 0 ? round(($cantidad / $totalPoliticas) * 100) : 0;
                    $color = match($tipo) {
                        'Estratégica' => 'success',
                        'Calidad' => 'primary',
                        'Seguridad' => 'danger',
                        'Operativa' => 'warning',
                        default => 'secondary'
                    };
                @endphp
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-semibold fs-7 text-dark">
                            <i class="fas fa-folder-open text-{{ $color }} me-1"></i> {{ $tipo }}
                        </span>
                        <span class="fs-7 text-muted fw-bold">{{ $cantidad }} ({{ $porcentaje }}%)</span>
                    </div>
                    <div class="progress rounded-pill" style="height: 8px;">
                        <div class="progress-bar bg-{{ $color }}" role="progressbar" style="width: {{ $porcentaje }}%;" aria-valuenow="{{ $porcentaje }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            @endforeach

            <div class="alert alert-light border rounded-3 p-3 mt-4 mb-0">
                <div class="d-flex gap-2 align-items-start">
                    <i class="fas fa-info-circle text-success fs-5 mt-1"></i>
                    <div class="fs-8 text-muted">
                        Las políticas de <strong>Calidad</strong> y <strong>Estratégicas</strong> impactan directamente los turnos y la certificación de aprendices en las plantas agroindustriales.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Policies List -->
    <div class="col-lg-7">
        <div class="card-custom p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="fw-bold text-dark mb-0 fs-5">
                    <i class="fas fa-clock text-primary me-2"></i> Últimas Políticas Registradas
                </h5>
                <a href="{{ route('direccion.politicas.index') }}" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold fs-8">
                    Ver todas <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-7">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Título</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ultimasPoliticas as $pol)
                            <tr>
                                <td><code>{{ $pol->codigo }}</code></td>
                                <td class="fw-semibold text-dark">{{ Str::limit($pol->titulo, 32) }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border fs-8">{{ $pol->tipo }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $pol->estado == 'Activa' ? 'bg-success' : ($pol->estado == 'En Revisión' ? 'bg-warning text-dark' : 'bg-secondary') }} rounded-pill px-2 py-1 fs-8">
                                        {{ $pol->estado }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('direccion.politicas.edit', $pol->id) }}" class="btn btn-sm btn-outline-primary rounded-circle p-1" style="width: 28px; height: 28px;" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    No hay políticas registradas recientemente.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
