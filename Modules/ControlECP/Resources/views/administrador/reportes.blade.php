@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .reporte-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 20px;
        padding: 1.5rem;
        height: 100%;
        box-shadow: 0 2px 12px rgba(0,26,41,.04);
        transition: transform .18s ease, box-shadow .18s ease;
        text-decoration: none;
        display: flex;
        flex-direction: column;
    }
    .reporte-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 28px rgba(0,26,41,.10);
    }
    .reporte-icon-wrap {
        width: 56px; height: 56px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.45rem;
        margin-bottom: 1rem;
    }
    .kpi-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 1.1rem 1.4rem;
        box-shadow: 0 1px 6px rgba(0,26,41,.04);
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">

    {{-- Cabecera --}}
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.administrador.inicio') }}" class="text-decoration-none" style="color:#39A900;">
                            <i class="fas fa-house me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page" style="color:#001A29;font-weight:600;">Reportes</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color:#001A29;font-family:'Outfit',sans-serif;">
                <i class="fas fa-chart-bar me-2" style="color:#39A900;"></i>Módulo de Reportes
            </h2>
            <p class="text-muted small mb-0 mt-1">
                Consulta, analiza y exporta la información de asistencias, encuestas, asesorías, PQRS, certificación, inventario y POE de la Escuela Cultura de Paz.
                Integración preparada para la API REST Central ERP SENA Empresa / SICEFA.
            </p>
        </div>
    </div>

    {{-- KPIs globales --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="small text-muted fw-semibold mb-1"><i class="fas fa-id-card me-1" style="color:#39A900;"></i>Fichas activas</div>
                <div class="h3 fw-bold mb-0" style="color:#001A29;">{{ $indicadores['fichas_activas'] }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="small text-muted fw-semibold mb-1"><i class="fas fa-user-check me-1" style="color:#0284C7;"></i>Registros asistencia</div>
                <div class="h3 fw-bold mb-0" style="color:#001A29;">{{ number_format($indicadores['total_asistencias']) }}</div>
                <div class="small text-muted">{{ $indicadores['pct_asistencia'] }}% de asistencia global</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="small text-muted fw-semibold mb-1"><i class="fas fa-certificate me-1" style="color:#8B5CF6;"></i>Certificados emitidos</div>
                <div class="h3 fw-bold mb-0" style="color:#001A29;">{{ $indicadores['total_certificados'] }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="small text-muted fw-semibold mb-1"><i class="fas fa-comment-dots me-1" style="color:#F59E0B;"></i>PQRS radicadas</div>
                <div class="h3 fw-bold mb-0" style="color:#001A29;">{{ $indicadores['total_pqrs'] }}</div>
                @if($indicadores['pqrs_pendientes'] > 0)
                    <div class="small text-warning fw-semibold">{{ $indicadores['pqrs_pendientes'] }} pendientes</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Tarjetas de reportes --}}
    <div class="row g-3">

        {{-- Asistencias --}}
        <div class="col-12 col-sm-6 col-lg-4">
            <a href="{{ route('controlecp.administrador.reportes.asistencias') }}" class="reporte-card text-dark">
                <div class="reporte-icon-wrap" style="background:#EFF6FF;">
                    <i class="fas fa-user-check" style="color:#0284C7;"></i>
                </div>
                <div class="fw-bold mb-1" style="color:#001A29;font-size:1.05rem;">Reporte de Asistencias</div>
                <p class="small text-muted mb-3">Listado por ficha, actividad y momento. Sin gráficas. Muestra asistencias, inasistencias y quién registró cada una.</p>
                <div class="mt-auto d-flex justify-content-between align-items-center">
                    <span class="badge rounded-pill px-3 py-1 fw-semibold" style="background:#EFF6FF;color:#0284C7;">{{ number_format($indicadores['total_asistencias']) }} registros</span>
                    <i class="fas fa-arrow-right small" style="color:#0284C7;"></i>
                </div>
            </a>
        </div>

        {{-- Encuestas --}}
        <div class="col-12 col-sm-6 col-lg-4">
            <a href="{{ route('controlecp.administrador.reportes.encuestas') }}" class="reporte-card text-dark">
                <div class="reporte-icon-wrap" style="background:#F0FDF4;">
                    <i class="fas fa-star" style="color:#39A900;"></i>
                </div>
                <div class="fw-bold mb-1" style="color:#001A29;font-size:1.05rem;">Reporte de Encuestas</div>
                <p class="small text-muted mb-3">Tabulación consolidada con gráficas. Respuestas, tasa de participación, promedio y promedio de calificación por actividad.</p>
                <div class="mt-auto d-flex justify-content-between align-items-center">
                    <span class="badge rounded-pill px-3 py-1 fw-semibold" style="background:#F0FDF4;color:#39A900;">Con gráficas</span>
                    <i class="fas fa-arrow-right small" style="color:#39A900;"></i>
                </div>
            </a>
        </div>

        {{-- Asesorías --}}
        <div class="col-12 col-sm-6 col-lg-4">
            <a href="{{ route('controlecp.administrador.reportes.asesorias') }}" class="reporte-card text-dark">
                <div class="reporte-icon-wrap" style="background:#FEF3C7;">
                    <i class="fas fa-comments" style="color:#D97706;"></i>
                </div>
                <div class="fw-bold mb-1" style="color:#001A29;font-size:1.05rem;">Reporte de Asesorías</div>
                <p class="small text-muted mb-3">Por trimestre, categoría, instructor, estado y fechas. Solicitadas, realizadas, canceladas y sin asignar. Gráficas de atención.</p>
                <div class="mt-auto d-flex justify-content-between align-items-center">
                    <span class="badge rounded-pill px-3 py-1 fw-semibold" style="background:#FEF3C7;color:#D97706;">Con totales y gráficas</span>
                    <i class="fas fa-arrow-right small" style="color:#D97706;"></i>
                </div>
            </a>
        </div>

        {{-- PQRS --}}
        <div class="col-12 col-sm-6 col-lg-4">
            <a href="{{ route('controlecp.administrador.reportes.pqrs') }}" class="reporte-card text-dark">
                <div class="reporte-icon-wrap" style="background:#FEF9C3;">
                    <i class="fas fa-comment-dots" style="color:#CA8A04;"></i>
                </div>
                <div class="fw-bold mb-1" style="color:#001A29;font-size:1.05rem;">Reporte de PQRS</div>
                <p class="small text-muted mb-3">Por tipo, estado, instructor y fechas. Listado consolidado con totales de pendientes, en trámite y resueltas.</p>
                <div class="mt-auto d-flex justify-content-between align-items-center">
                    <span class="badge rounded-pill px-3 py-1 fw-semibold" style="background:#FEF9C3;color:#CA8A04;">
                        {{ $indicadores['pqrs_pendientes'] }} pendientes
                    </span>
                    <i class="fas fa-arrow-right small" style="color:#CA8A04;"></i>
                </div>
            </a>
        </div>

        {{-- Certificación --}}
        <div class="col-12 col-sm-6 col-lg-4">
            <a href="{{ route('controlecp.administrador.reportes.certificacion') }}" class="reporte-card text-dark">
                <div class="reporte-icon-wrap" style="background:#EDE9FE;">
                    <i class="fas fa-certificate" style="color:#7C3AED;"></i>
                </div>
                <div class="fw-bold mb-1" style="color:#001A29;font-size:1.05rem;">Reporte de Certificación</div>
                <p class="small text-muted mb-3">Por ficha, momento, responsable y estado. Momentos completos, pendientes y certificados. Preparado para SOFIA Plus.</p>
                <div class="mt-auto d-flex justify-content-between align-items-center">
                    <span class="badge rounded-pill px-3 py-1 fw-semibold" style="background:#EDE9FE;color:#7C3AED;">{{ $indicadores['total_certificados'] }} emitidos</span>
                    <i class="fas fa-arrow-right small" style="color:#7C3AED;"></i>
                </div>
            </a>
        </div>

        {{-- Inventario --}}
        <div class="col-12 col-sm-6 col-lg-4">
            <a href="{{ route('controlecp.administrador.reportes.inventario') }}" class="reporte-card text-dark">
                <div class="reporte-icon-wrap" style="background:#FFF1F2;">
                    <i class="fas fa-boxes-stacked" style="color:#E11D48;"></i>
                </div>
                <div class="fw-bold mb-1" style="color:#001A29;font-size:1.05rem;">Reporte de Inventario</div>
                <p class="small text-muted mb-3">Por categoría, estado y fechas. Semáforo de disponibilidad, movimientos, novedades, alertas de devoluciones incompletas.</p>
                <div class="mt-auto d-flex justify-content-between align-items-center">
                    @if($indicadores['inv_criticos'] > 0)
                        <span class="badge rounded-pill px-3 py-1 fw-semibold" style="background:#FFF1F2;color:#E11D48;">{{ $indicadores['inv_criticos'] }} críticos</span>
                    @else
                        <span class="badge rounded-pill px-3 py-1 fw-semibold" style="background:#FFF1F2;color:#E11D48;">{{ $indicadores['total_inventario'] }} recursos</span>
                    @endif
                    <i class="fas fa-arrow-right small" style="color:#E11D48;"></i>
                </div>
            </a>
        </div>

        {{-- POE --}}
        <div class="col-12 col-sm-6 col-lg-4">
            <a href="{{ route('controlecp.administrador.reportes.poe') }}" class="reporte-card text-dark">
                <div class="reporte-icon-wrap" style="background:#ECFDF5;">
                    <i class="fas fa-file-contract" style="color:#059669;"></i>
                </div>
                <div class="fw-bold mb-1" style="color:#001A29;font-size:1.05rem;">Reporte de POE</div>
                <p class="small text-muted mb-3">Por estado de programación y líder. Muestra quién subió cada versión, descripción del cambio y cumplimiento de subidas programadas.</p>
                <div class="mt-auto d-flex justify-content-between align-items-center">
                    <span class="badge rounded-pill px-3 py-1 fw-semibold" style="background:#ECFDF5;color:#059669;">Versiones y cumplimiento</span>
                    <i class="fas fa-arrow-right small" style="color:#059669;"></i>
                </div>
            </a>
        </div>

    </div>

    {{-- Nota de integración --}}
    <div class="mt-4 p-3 rounded-3 d-flex align-items-start gap-3" style="background:#F0FDF4;border:1px solid #BBF7D0;">
        <i class="fas fa-plug-circle-check mt-1" style="color:#39A900;font-size:1.2rem;"></i>
        <div>
            <div class="fw-semibold small mb-0" style="color:#166534;">Preparado para integración con API REST Central ERP SENA Empresa / SICEFA</div>
            <div class="small text-muted">Los datos mostrados son reales de la base local. Las secciones de Asesorías y POE usan datos estructurados que serán reemplazados por los endpoints de la API Central cuando esté disponible la conexión con SICEFA.</div>
        </div>
    </div>

</div>
@endsection
