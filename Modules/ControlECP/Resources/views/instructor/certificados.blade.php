@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">

    {{-- Breadcrumb --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.instructor.inicio') }}" class="text-decoration-none" style="color:#39A900;">
                            <i class="fas fa-house me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item active" style="color:#001A29;font-weight:600;">Certificados</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color:#001A29;">
                <i class="fas fa-certificate me-2" style="color:#39A900;"></i>Estado de Certificaciones ECP
            </h2>
            <p class="text-muted small mb-0">
                Control ECP · Vista de progreso por ficha · Solo lectura
                <span class="badge ms-1" style="background:#39A900;font-size:.7rem;">API Central SICEFA preparada</span>
            </p>
        </div>
        <a href="{{ route('controlecp.instructor.inicio') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    {{-- ─── Indicadores ─── --}}
    <div class="row g-3 mb-4">
        @php
            $ind = $indicadores;
        @endphp
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 shadow-sm" style="border-radius:16px;">
                <div class="card-body text-center p-3">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-2"
                         style="width:44px;height:44px;background:#39A90018;color:#39A900;font-size:1.1rem;">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h4 class="fw-bold mb-0" style="color:#39A900;">{{ $ind['certCount'] }}</h4>
                    <p class="small text-muted mb-0" style="font-size:.78rem;">Momentos certificados</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 shadow-sm" style="border-radius:16px;">
                <div class="card-body text-center p-3">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-2"
                         style="width:44px;height:44px;background:#0070B818;color:#0070B8;font-size:1.1rem;">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h4 class="fw-bold mb-0" style="color:#0070B8;">{{ $ind['fichasCompletas'] }}</h4>
                    <p class="small text-muted mb-0" style="font-size:.78rem;">Fichas 100% certificadas</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 shadow-sm" style="border-radius:16px;">
                <div class="card-body text-center p-3">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-2"
                         style="width:44px;height:44px;background:#E74C3C18;color:#E74C3C;font-size:1.1rem;">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <h4 class="fw-bold mb-0" style="color:#E74C3C;">{{ $fichas->count() - $ind['fichasCompletas'] }}</h4>
                    <p class="small text-muted mb-0" style="font-size:.78rem;">Fichas en progreso</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 shadow-sm" style="border-radius:16px;">
                <div class="card-body text-center p-3">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-2"
                         style="width:44px;height:44px;background:#6c757d18;color:#6c757d;font-size:1.1rem;">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h4 class="fw-bold mb-0" style="color:#6c757d;">{{ $fichas->count() }}</h4>
                    <p class="small text-muted mb-0" style="font-size:.78rem;">Fichas activas</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Avance por ficha ─── --}}
    @if ($fichas->count())
    <div class="row g-4">
        @foreach ($fichas as $ficha)
        @php
            $prog = $ficha->progreso;
            $certCount = $ficha->momentos_certificados;
            $completa  = $certCount === 3;
        @endphp
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card h-100 border-0 shadow-sm"
                 style="border-radius:18px;border-top:4px solid {{ $completa ? '#39A900' : ($certCount > 0 ? '#D68910' : '#dee2e6') }}!important;">
                <div class="card-header bg-white border-0 pt-3 pb-1 px-4 d-flex justify-content-between align-items-start">
                    <div>
                        <span class="fw-bold font-monospace" style="color:#001A29;font-size:1rem;">{{ $ficha->code }}</span>
                        <p class="text-muted small mb-0">{{ Str::limit($ficha->program?->name ?? 'Sin programa', 40) }}</p>
                    </div>
                    <div class="text-end">
                        @if ($completa)
                            <span class="badge bg-success rounded-pill">
                                <i class="fas fa-trophy me-1"></i>Completa
                            </span>
                        @elseif ($certCount > 0)
                            <span class="badge bg-warning text-dark rounded-pill">{{ $certCount }}/3 momentos</span>
                        @else
                            <span class="badge bg-secondary rounded-pill">Sin certificar</span>
                        @endif
                        <div class="text-muted small mt-1">
                            <i class="fas fa-users me-1"></i>{{ $ficha->apprentices_count }} aprendices
                        </div>
                    </div>
                </div>
                <div class="card-body px-4 pb-4">
                    @foreach ([1,2,3] as $m)
                    @php
                        $p = $prog[$m];
                        $colores = [1=>'#39A900', 2=>'#0070B8', 3=>'#6A1B9A'];
                        $col = $colores[$m];
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-semibold" style="color:{{ $col }};">
                                <i class="fas {{ [1=>'fa-brain',2=>'fa-people-holding-hands',3=>'fa-scale-balanced'][$m] }} me-1"></i>
                                {{ $p['nombre'] }}
                            </span>
                            <span class="small">
                                @if ($p['certificado'])
                                    <span class="badge bg-success rounded-pill" style="font-size:.7rem;">
                                        <i class="fas fa-check me-1"></i>{{ $p['cert_fecha'] }}
                                    </span>
                                @elseif ($p['puede_cert'])
                                    <span class="badge rounded-pill text-white" style="background:#D68910;font-size:.7rem;">
                                        <i class="fas fa-bolt me-1"></i>Listo para certificar
                                    </span>
                                @else
                                    <span class="text-muted" style="font-size:.75rem;">
                                        {{ $p['completadas'] }}/{{ $p['total'] }} actividades
                                    </span>
                                @endif
                            </span>
                        </div>
                        <div class="progress" style="height:7px;border-radius:10px;background:#e9ecef;">
                            <div class="progress-bar"
                                 style="width:{{ $p['porcentaje'] }}%;background:{{ $col }};border-radius:10px;transition:width .6s;"
                                 title="{{ $p['porcentaje'] }}%">
                            </div>
                        </div>
                        @if ($p['certificado'] && $p['cert_resp'])
                        <div class="text-muted mt-1" style="font-size:.72rem;">
                            <i class="fas fa-user-tie me-1"></i>{{ $p['cert_resp'] }}
                        </div>
                        @endif
                    </div>
                    @endforeach

                    {{-- Barra resumen total --}}
                    @php $pctTotal = round((collect($prog)->sum('completadas') / max(1, collect($prog)->sum('total'))) * 100, 1); @endphp
                    <div class="pt-2 border-top">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Progreso total de actividades</span>
                            <span class="fw-bold" style="color:#001A29;">{{ $pctTotal }}%</span>
                        </div>
                        <div class="progress" style="height:10px;border-radius:10px;">
                            <div class="progress-bar"
                                 style="width:{{ $pctTotal }}%;background:linear-gradient(90deg,#39A900,#0070B8);border-radius:10px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="card border-0 shadow-sm" style="border-radius:18px;">
        <div class="card-body text-center py-5 text-muted">
            <i class="fas fa-inbox fa-3x mb-3 d-block" style="color:#39A900;opacity:.3;"></i>
            <p class="mb-0">No hay fichas activas registradas.<br>
            <small>El administrador debe sincronizar las fichas desde la API Central SICEFA.</small></p>
        </div>
    </div>
    @endif

    {{-- Nota HU-010 --}}
    <div class="alert border-0 shadow-sm mt-4 small" style="border-radius:12px;background:#f8f9fa;border-left:4px solid #6c757d!important;">
        <i class="fas fa-info-circle me-2 text-muted"></i>
        <strong>Nota:</strong> La emisión oficial en <strong>SOFIA Plus</strong> se activará cuando se conecte la
        <strong>API REST Central del ERP SENA Empresa / SICEFA</strong>.
        El registro local ya está habilitado y el token QR de verificación se genera automáticamente al certificar.
    </div>
</div>
@endsection
