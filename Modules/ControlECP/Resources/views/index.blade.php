{{-- Vista: Listado de elementos con sus acciones de consulta y administración. --}}
@extends('controlecp::components.layouts.master')

@push('styles')
<style>
    .historia-timeline {
        padding: 2.5rem 0;
        position: relative;
    }

    .historia-item {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 3.5rem;
        width: 100%;
        margin-bottom: 4rem;
    }

    .historia-item:last-child {
        margin-bottom: 1rem;
    }

    .historia-col-text,
    .historia-col-media {
        flex: 1;
        width: calc(50% - 1.75rem);
    }

    /* REGLA ZIGZAG: Alterna lados en pantallas medianas y grandes */
    @media (min-width: 768px) {
        .historia-item:nth-child(even) {
            flex-direction: row-reverse;
        }
    }

    .historia-narrative {
        position: relative;
        padding: 1.25rem 1.5rem 1.25rem 2rem;
        border-left: 4px solid var(--sena-pastel-primary);
        background: #ffffff;
        border-radius: 0 18px 18px 0;
        box-shadow: 0 4px 18px rgba(0, 26, 41, 0.05);
    }

    .historia-narrative::before {
        position: absolute;
        top: 1.6rem;
        left: -9px;
        width: 14px;
        height: 14px;
        content: "";
        background: var(--sena-pastel-primary);
        border-radius: 50%;
        box-shadow: 0 0 0 4px #ffffff, 0 0 10px rgba(57, 169, 0, 0.4);
    }

    .historia-media-wrapper {
        overflow: hidden;
        background: #ffffff;
        border: 1px solid rgba(57, 169, 0, 0.25);
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 26, 41, 0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .historia-media-wrapper:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 36px rgba(57, 169, 0, 0.18);
    }

    .historia-img {
        display: block;
        width: 100%;
        max-height: 400px;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .historia-img:hover {
        transform: scale(1.02);
    }

    .historia-audio-container {
        padding: 2.2rem;
        background: linear-gradient(135deg, #ffffff 0%, #edf8f0 100%);
        border-radius: 20px;
    }

    .historia-audio-icon-box {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 54px;
        height: 54px;
        color: var(--sena-pastel-primary);
        background: #ffffff;
        border: 1px solid rgba(57, 169, 0, 0.3);
        border-radius: 14px;
        box-shadow: 0 4px 12px rgba(57, 169, 0, 0.15);
    }

    .historia-video-container {
        overflow: hidden;
        background: #000000;
        border-radius: 20px;
    }

    .historia-video-container iframe {
        width: 100%;
        height: 100%;
        border: 0;
    }

    .historia-titulo {
        margin-bottom: 0.85rem;
        color: var(--sena-pastel-text-title);
        font-family: 'Outfit', sans-serif;
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1.25;
    }

    .historia-texto {
        color: var(--sena-pastel-text-body);
        font-size: 1.02rem;
        line-height: 1.8;
        white-space: pre-line;
    }

    @media (max-width: 767.98px) {
        .historia-item {
            flex-direction: column !important;
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .historia-col-text,
        .historia-col-media {
            width: 100% !important;
        }

        .historia-titulo {
            font-size: 1.35rem;
        }
    }

    /* ========================================================================= */
    /* BARRA DE KPIS / ÁREAS INTERACTIVAS (ESTILO UNIDAD PRODUCTIVA SENA) */
    /* ========================================================================= */
    .ecp-kpi-wrapper {
        position: relative;
        z-index: 20;
        margin-top: -3.5rem;
        margin-bottom: 2rem;
    }

    .ecp-kpi-card-group {
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 16px 40px rgba(0, 26, 41, 0.1);
        border: 1px solid rgba(57, 169, 0, 0.2);
        overflow: hidden;
        display: flex;
        flex-wrap: wrap;
    }

    .ecp-kpi-col {
        flex: 1 1 0;
        min-width: 175px;
        padding: 1.5rem 1.15rem;
        border: 0;
        border-right: 1px solid #edf2f7;
        cursor: pointer;
        transition: all 0.25s ease;
        background: #ffffff;
        color: inherit;
        font: inherit;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        text-align: left;
    }

    .ecp-kpi-col:last-child {
        border-right: 0;
    }

    .ecp-kpi-col:hover {
        background-color: #f3fbf5;
        transform: translateY(-2px);
    }

    .ecp-kpi-col:focus-visible {
        outline: 3px solid var(--sena-pastel-primary);
        outline-offset: -3px;
        z-index: 1;
    }

    .ecp-kpi-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: #e8f7ec;
        color: #39A900;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        margin-bottom: 0.9rem;
        transition: all 0.25s ease;
    }

    .ecp-kpi-col:hover .ecp-kpi-icon-box {
        background: #39A900;
        color: #ffffff;
        transform: scale(1.08);
    }

    .ecp-kpi-title {
        font-weight: 700;
        color: #001A29;
        font-size: 0.96rem;
        margin-bottom: 0.35rem;
        line-height: 1.2;
    }

    .ecp-kpi-desc {
        color: #64748b;
        font-size: 0.77rem;
        line-height: 1.45;
        margin-bottom: 0;
    }

    @media (max-width: 991.98px) {
        .ecp-kpi-wrapper {
            margin-top: 1rem;
        }
        .ecp-kpi-col {
            flex: 1 1 33.333%;
            border-bottom: 1px solid #edf2f7;
        }
    }

    @media (max-width: 575.98px) {
        .ecp-kpi-col {
            flex: 1 1 100%;
        }
    }

    .ecp-kpi-col.is-active {
        background-color: #eaf8ee !important;
        border-bottom: 4px solid var(--sena-pastel-primary) !important;
        transform: translateY(-2px);
    }

    .ecp-kpi-col.is-active .ecp-kpi-icon-box {
        background: var(--sena-pastel-primary) !important;
        color: #ffffff !important;
    }

    .ecp-detalle-section {
        background-color: #f8fafc;
        border-top: 1px solid rgba(57, 169, 0, 0.2);
    }

    .ecp-detalle-section .container {
        padding-top: 2rem;
        padding-bottom: 2rem;
    }

    .ecp-detalle-card-wrapper {
        background: #ffffff;
        border: 1px solid rgba(57, 169, 0, 0.25);
        border-radius: 24px;
        box-shadow: 0 16px 45px rgba(0, 26, 41, 0.08);
        padding: 2rem;
        position: relative;
    }

    @media (max-width: 575.98px) {
        .ecp-detalle-card-wrapper {
            padding: 1.25rem;
        }
    }

    .modal-ecp-badge-green {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: #eaf8ee;
        color: #2b8000;
        font-size: 0.78rem;
        font-weight: 700;
        padding: 0.4rem 0.95rem;
        border-radius: 50px;
        border: 1px solid rgba(57, 169, 0, 0.3);
    }

    .modal-ecp-badge-gold {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: #fff8e6;
        color: #b45309;
        font-size: 0.78rem;
        font-weight: 700;
        padding: 0.4rem 0.95rem;
        border-radius: 50px;
        border: 1px solid rgba(217, 119, 6, 0.3);
    }

    .modal-ecp-img-box {
        position: relative;
        border-radius: 18px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        box-shadow: 0 8px 24px rgba(0, 26, 41, 0.08);
        margin-bottom: 1.25rem;
    }

    .modal-ecp-img-box img {
        width: 100%;
        height: 250px;
        object-fit: cover;
        display: block;
    }

    .modal-ecp-img-overlay-tag {
        position: absolute;
        bottom: 12px;
        left: 12px;
        right: 12px;
        background: rgba(0, 26, 41, 0.88);
        backdrop-filter: blur(6px);
        color: #ffffff;
        padding: 9px 15px;
        border-radius: 12px;
        font-size: 0.78rem;
        border-left: 3px solid #39A900;
    }

    .modal-ecp-feature-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 0.95rem 1.1rem;
        margin-bottom: 0.85rem;
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        box-shadow: 0 2px 8px rgba(0, 26, 41, 0.03);
        transition: all 0.2s ease;
    }

    .modal-ecp-feature-card:hover {
        border-color: rgba(57, 169, 0, 0.4);
        transform: translateY(-2px);
        box-shadow: 0 4px 14px rgba(57, 169, 0, 0.1);
    }

    .modal-ecp-feature-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #eaf8ee;
        color: #39A900;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        flex-shrink: 0;
    }

    .modal-ecp-callout {
        background: #eaf8ee;
        border: 1px solid rgba(57, 169, 0, 0.3);
        border-left: 4px solid #39A900;
        border-radius: 14px;
        padding: 1rem 1.2rem;
        font-size: 0.84rem;
        color: #00324D;
    }
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .modal-ecp-callout {
        background: #eaf8ee;
        border: 1px solid rgba(57, 169, 0, 0.3);
        border-left: 4px solid #39A900;
        border-radius: 14px;
        padding: 0.9rem 1.1rem;
        font-size: 0.82rem;
        color: #00324D;
    }
</style>
@endpush

{{-- Contenido principal que organiza la interfaz específica de esta vista. --}}
@section('content')

    @php
        // Calculamos, si hay usuario autenticado, la ruta de "inicio" que le corresponde según su rol.
        // Los roles del módulo (app_id 30 en la tabla apps) ya existen con slug exacto:
        // controlecp.admin, controlecp.instructor, controlecp.apprentice.
        $rutaInicioRol = route('direccion.welcome');

        if (Auth::check()) {
            $usuarioActual = Auth::user();

            if ($usuarioActual->hasRole('controlecp.admin')) {
                $rutaInicioRol = route('controlecp.administrador.inicio');
            } elseif ($usuarioActual->hasRole('controlecp.instructor')) {
                $rutaInicioRol = route('controlecp.instructor.inicio');
            } elseif ($usuarioActual->hasRole('controlecp.apprentice')) {
                $rutaInicioRol = route('controlecp.aprendiz.inicio');
            }
        }
    @endphp

    <!-- ========================================================================= -->
    <!-- 1. HEADER / BARRA DE NAVEGACIÓN (Verde Pastel Claro) -->
    <!-- ========================================================================= -->
    <nav class="navbar navbar-expand-lg ecp-navbar sticky-top py-3">
        <div class="container">
            <!-- Logotipo y Nombre del Módulo -->
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('controlecp.index') }}">
                <img src="{{ asset('general/assets/img/logo-ecp.png') }}" 
                     alt="Logo Escuela Cultura de Paz" 
                     style="height: 46px; width: 46px; object-fit: contain; background: #ffffff; border-radius: 50%; padding: 2px; box-shadow: 0 2px 8px rgba(57, 169, 0, 0.35); border: 2px solid var(--sena-pastel-primary);">
                <div>
                    <span class="d-block fw-bold fs-5" style="color: var(--sena-pastel-text-title); line-height: 1.1;">Escuela Cultura de Paz</span>
                    <span class="badge rounded-pill fw-bold" style="background-color: var(--sena-pastel-light); color: var(--sena-pastel-text-title); font-size: 0.68rem; letter-spacing: 0.5px; border: 1px solid rgba(57, 169, 0, 0.45);">Control ECP • SENA</span>
                </div>
            </a>

            <!-- Acciones / Iniciar Sesión (Alineadas a la derecha) -->
            <div class="d-flex align-items-center gap-2 ms-auto">
                <a href="{{ url('/') }}" class="btn btn-sm btn-sena-outline-white d-none d-sm-inline-flex align-items-center gap-1" title="Volver al Portal Principal ERP">
                    <i class="fas fa-arrow-left"></i> SENA Empresa
                </a>

                @guest
                    <a href="{{ route('login') }}" class="btn btn-sena-green d-inline-flex align-items-center gap-2">
                        <i class="fas fa-right-to-bracket"></i> Iniciar Sesión
                    </a>
                @else
                    <a href="{{ $rutaInicioRol }}" class="btn btn-sena-green d-inline-flex align-items-center gap-2" title="Entrar al Aplicativo Control ECP">
                        <i class="fas fa-right-to-bracket"></i> Iniciar Sesión
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-sena-outline-white dropdown-toggle d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> {{ Str::limit(Auth::user()->full_name ?? Auth::user()->name, 14) }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2 mt-2" style="background-color: #ffffff; border: 1px solid rgba(57, 169, 0, 0.25) !important;">
                            <li class="px-3 py-2 border-bottom">
                                <small class="text-muted d-block">Sesión iniciada como:</small>
                                <strong style="color: var(--sena-pastel-text-title);">{{ Auth::user()->email }}</strong>
                            </li>
                            <li>
                                <a class="dropdown-item rounded-3 py-2 my-1" href="{{ $rutaInicioRol }}">
                                    <i class="fas fa-layer-group me-2" style="color: var(--sena-pastel-primary);"></i> Entrar al Aplicativo
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item rounded-3 py-2 my-1" href="{{ url('/') }}">
                                    <i class="fas fa-cubes me-2" style="color: var(--sena-pastel-primary);"></i> Módulos ERP SENA
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item rounded-3 py-2 text-danger" href="{{ route('logout') }}">
                                    <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                                </a>
                            </li>
                        </ul>
                    </div>
                @endguest
            </div>
        </div>
    </nav>

    <!-- ========================================================================= -->
    <!-- 2. HERO / CARRUSEL PRINCIPAL (Verde Pastel Claro y Luminoso) -->
    <!-- ========================================================================= -->
    <section id="hero-carousel" class="ecp-hero-carousel position-relative">
        <div id="carouselControlECP" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="6000">
            <!-- Indicadores -->
            <div class="carousel-indicators mb-4">
                <button type="button" data-bs-target="#carouselControlECP" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1" style="background-color: var(--sena-pastel-primary);"></button>
                <button type="button" data-bs-target="#carouselControlECP" data-bs-slide-to="1" aria-label="Slide 2" style="background-color: var(--sena-pastel-primary);"></button>
                <button type="button" data-bs-target="#carouselControlECP" data-bs-slide-to="2" aria-label="Slide 3" style="background-color: var(--sena-pastel-primary);"></button>
                <button type="button" data-bs-target="#carouselControlECP" data-bs-slide-to="3" aria-label="Slide 4" style="background-color: var(--sena-pastel-primary);"></button>
                <button type="button" data-bs-target="#carouselControlECP" data-bs-slide-to="4" aria-label="Slide 5" style="background-color: var(--sena-pastel-primary);"></button>
                <button type="button" data-bs-target="#carouselControlECP" data-bs-slide-to="5" aria-label="Slide 6" style="background-color: var(--sena-pastel-primary);"></button>
            </div>

            <!-- Diapositivas del Carrusel con Fotos Reales y Cajita Compacta -->
            <div class="carousel-inner">
                <!-- Slide 1: Quiénes somos -->
                <div class="carousel-item active ecp-carousel-item" style="background-image: url('{{ asset('general/assets/img/carrusel-1.png') }}?v={{ filemtime(public_path('general/assets/img/carrusel-1.png')) }}'); background-size: cover; background-position: center;">
                    <div class="ecp-carousel-overlay"></div>
                    <div class="container ecp-carousel-content py-4">
                        <div class="row justify-content-center">
                            <div class="col-xl-7 col-lg-8 col-md-10">
                                <div class="ecp-hero-card text-center">
                                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2" style="background: rgba(255, 255, 255, 0.9); border: 1px solid rgba(57, 169, 0, 0.45); box-shadow: 0 2px 8px rgba(57, 169, 0, 0.15);">
                                        <i class="fas fa-dove" style="color: var(--sena-pastel-primary); font-size: 0.8rem;"></i>
                                        <span class="fw-bold" style="color: var(--sena-pastel-text-title); font-size: 0.72rem;">Escuela Cultura de Paz • SENA</span>
                                    </div>

                                    <h2 class="fw-bold mb-2 fs-2" style="color: var(--sena-pastel-text-title); line-height: 1.2;">
                                        ¿Quiénes <span style="color: var(--sena-pastel-primary);">somos?</span>
                                    </h2>

                                    <p class="mb-3 mx-auto fw-medium" style="color: var(--sena-pastel-text-body); max-width: 460px; font-size: 0.92rem; line-height: 1.5;">
                                        Somos un espacio pedagógico de formación integral que promueve la convivencia, el diálogo y los valores humanos.
                                    </p>

                                    <div class="d-flex flex-wrap justify-content-center gap-2">
                                        <button type="button" class="btn btn-sena-outline-white px-3 py-2 fs-6 d-inline-flex align-items-center gap-2" onclick="abrirDetalleKpi('quienes-somos')" aria-controls="detalle-kpi-section">
                                            <i class="fas fa-arrow-down"></i> Conócenos
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 2: Logros -->
                <div class="carousel-item ecp-carousel-item" style="background-image: url('{{ asset('general/assets/img/carrusel-2.png') }}?v={{ filemtime(public_path('general/assets/img/carrusel-2.png')) }}'); background-size: cover; background-position: center;">
                    <div class="ecp-carousel-overlay"></div>
                    <div class="container ecp-carousel-content py-4">
                        <div class="row justify-content-center">
                            <div class="col-xl-7 col-lg-8 col-md-10">
                                <div class="ecp-hero-card text-center">
                                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2" style="background: rgba(255, 255, 255, 0.9); border: 1px solid rgba(57, 169, 0, 0.45); box-shadow: 0 2px 8px rgba(57, 169, 0, 0.15);">
                                        <i class="fas fa-award" style="color: var(--sena-pastel-primary); font-size: 0.8rem;"></i>
                                        <span class="fw-bold" style="color: var(--sena-pastel-text-title); font-size: 0.72rem;">Avances y experiencias</span>
                                    </div>

                                    <h2 class="fw-bold mb-2 fs-2" style="color: var(--sena-pastel-text-title); line-height: 1.2;">
                                        Logros de la <span style="color: var(--sena-pastel-primary);">Escuela</span>
                                    </h2>

                                    <p class="mb-3 mx-auto fw-medium" style="color: var(--sena-pastel-text-body); max-width: 460px; font-size: 0.92rem; line-height: 1.5;">
                                        Conoce las experiencias formativas y acciones que promueven la convivencia y la cultura de paz.
                                    </p>

                                    <div class="d-flex flex-wrap justify-content-center gap-2">
                                        <button type="button" class="btn btn-sena-outline-white px-3 py-2 fs-6 d-inline-flex align-items-center gap-2" onclick="abrirDetalleKpi('logros')" aria-controls="detalle-kpi-section">
                                            <i class="fas fa-arrow-down"></i> Ver logros
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 3: Historia -->
                <div class="carousel-item ecp-carousel-item" style="background-image: url('{{ asset('general/assets/img/carrusel-3.png') }}?v={{ filemtime(public_path('general/assets/img/carrusel-3.png')) }}'); background-size: cover; background-position: center;">
                    <div class="ecp-carousel-overlay"></div>
                    <div class="container ecp-carousel-content py-4">
                        <div class="row justify-content-center">
                            <div class="col-xl-7 col-lg-8 col-md-10">
                                <div class="ecp-hero-card text-center">
                                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2" style="background: rgba(255, 255, 255, 0.9); border: 1px solid rgba(57, 169, 0, 0.45); box-shadow: 0 2px 8px rgba(57, 169, 0, 0.15);">
                                        <i class="fas fa-book-open" style="color: var(--sena-pastel-primary); font-size: 0.8rem;"></i>
                                        <span class="fw-bold" style="color: var(--sena-pastel-text-title); font-size: 0.72rem;">Origen y recorrido</span>
                                    </div>

                                    <h2 class="fw-bold mb-2 fs-2" style="color: var(--sena-pastel-text-title); line-height: 1.2;">
                                        Historia de la <span style="color: var(--sena-pastel-primary);">Escuela</span>
                                    </h2>

                                    <p class="mb-3 mx-auto fw-medium" style="color: var(--sena-pastel-text-body); max-width: 460px; font-size: 0.92rem; line-height: 1.5;">
                                        Conoce el recorrido y la evolución de la Escuela Cultura de Paz.
                                    </p>

                                    <div class="d-flex flex-wrap justify-content-center gap-2">
                                        <button type="button" class="btn btn-sena-outline-white px-3 py-2 fs-6 d-inline-flex align-items-center gap-2" onclick="abrirDetalleKpi('historia')" aria-controls="detalle-kpi-section">
                                            <i class="fas fa-arrow-down"></i> Conocer la historia
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 4: Misión y visión -->
                <div class="carousel-item ecp-carousel-item" style="background-image: url('{{ asset('general/assets/img/carrusel-4.png') }}?v={{ filemtime(public_path('general/assets/img/carrusel-4.png')) }}'); background-size: cover; background-position: center;">
                    <div class="ecp-carousel-overlay"></div>
                    <div class="container ecp-carousel-content py-4">
                        <div class="row justify-content-center">
                            <div class="col-xl-7 col-lg-8 col-md-10">
                                <div class="ecp-hero-card text-center">
                                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2" style="background: rgba(255, 255, 255, 0.9); border: 1px solid rgba(57, 169, 0, 0.45); box-shadow: 0 2px 8px rgba(57, 169, 0, 0.15);">
                                        <i class="fas fa-compass" style="color: var(--sena-pastel-primary); font-size: 0.8rem;"></i>
                                        <span class="fw-bold" style="color: var(--sena-pastel-text-title); font-size: 0.72rem;">Nuestro propósito</span>
                                    </div>

                                    <h2 class="fw-bold mb-2 fs-2" style="color: var(--sena-pastel-text-title); line-height: 1.2;">
                                        Misión y <span style="color: var(--sena-pastel-primary);">visión</span>
                                    </h2>

                                    <p class="mb-3 mx-auto fw-medium" style="color: var(--sena-pastel-text-body); max-width: 460px; font-size: 0.92rem; line-height: 1.5;">
                                        Nuestro propósito y horizonte: formar en cultura de paz, respeto y convivencia.
                                    </p>

                                    <div class="d-flex flex-wrap justify-content-center gap-2">
                                        <button type="button" class="btn btn-sena-outline-white px-3 py-2 fs-6 d-inline-flex align-items-center gap-2" onclick="abrirDetalleKpi('mision-vision')" aria-controls="detalle-kpi-section">
                                            <i class="fas fa-arrow-down"></i> Ver misión y visión
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 5: Nuestros servicios -->
                <div class="carousel-item ecp-carousel-item" style="background-image: url('{{ asset('general/assets/img/carrusel-5.png') }}?v={{ filemtime(public_path('general/assets/img/carrusel-5.png')) }}'); background-size: cover; background-position: center;">
                    <div class="ecp-carousel-overlay"></div>
                    <div class="container ecp-carousel-content py-4">
                        <div class="row justify-content-center">
                            <div class="col-xl-7 col-lg-8 col-md-10">
                                <div class="ecp-hero-card text-center">
                                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2" style="background: rgba(255, 255, 255, 0.9); border: 1px solid rgba(57, 169, 0, 0.45); box-shadow: 0 2px 8px rgba(57, 169, 0, 0.15);">
                                        <i class="fas fa-hands-holding-circle" style="color: var(--sena-pastel-primary); font-size: 0.8rem;"></i>
                                        <span class="fw-bold" style="color: var(--sena-pastel-text-title); font-size: 0.72rem;">Acompañamiento y formación</span>
                                    </div>

                                    <h2 class="fw-bold mb-2 fs-2" style="color: var(--sena-pastel-text-title); line-height: 1.2;">
                                        Nuestros <span style="color: var(--sena-pastel-primary);">servicios</span>
                                    </h2>

                                    <p class="mb-3 mx-auto fw-medium" style="color: var(--sena-pastel-text-body); max-width: 460px; font-size: 0.92rem; line-height: 1.5;">
                                        Acompañamiento psicológico, fortalecimiento espiritual y trabajo en equipo.
                                    </p>

                                    <div class="d-flex flex-wrap justify-content-center gap-2">
                                        <button type="button" class="btn btn-sena-outline-white px-3 py-2 fs-6 d-inline-flex align-items-center gap-2" onclick="abrirDetalleKpi('servicios')" aria-controls="detalle-kpi-section">
                                            <i class="fas fa-arrow-down"></i> Conocer los servicios
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 6: Podcast ECP -->
                <div class="carousel-item ecp-carousel-item" style="background-image: url('{{ asset('general/assets/img/carrusel-6.png') }}?v={{ filemtime(public_path('general/assets/img/carrusel-6.png')) }}'); background-size: cover; background-position: center;">
                    <div class="ecp-carousel-overlay"></div>
                    <div class="container ecp-carousel-content py-4">
                        <div class="row justify-content-center">
                            <div class="col-xl-7 col-lg-8 col-md-10">
                                <div class="ecp-hero-card text-center">
                                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2" style="background: rgba(255, 255, 255, 0.9); border: 1px solid rgba(57, 169, 0, 0.45); box-shadow: 0 2px 8px rgba(57, 169, 0, 0.15);">
                                        <i class="fas fa-podcast" style="color: var(--sena-pastel-primary); font-size: 0.8rem;"></i>
                                        <span class="fw-bold" style="color: var(--sena-pastel-text-title); font-size: 0.72rem;">Voces de la cultura de paz</span>
                                    </div>

                                    <h2 class="fw-bold mb-2 fs-2" style="color: var(--sena-pastel-text-title); line-height: 1.2;">
                                        Podcast <span style="color: var(--sena-pastel-primary);">ECP</span>
                                    </h2>

                                    <p class="mb-3 mx-auto fw-medium" style="color: var(--sena-pastel-text-body); max-width: 460px; font-size: 0.92rem; line-height: 1.5;">
                                        Conversaciones y reflexiones sobre cultura de paz. Episodios próximamente.
                                    </p>

                                    <div class="d-flex flex-wrap justify-content-center gap-2">
                                        <button type="button" class="btn btn-sena-outline-white px-3 py-2 fs-6 d-inline-flex align-items-center gap-2" onclick="abrirDetalleKpi('podcast')" aria-controls="detalle-kpi-section">
                                            <i class="fas fa-arrow-down"></i> Ver Podcast ECP
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Controles Anterior / Siguiente -->
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselControlECP" data-bs-slide="prev">
                <span class="carousel-control-prev-icon rounded-circle p-3" style="background-color: rgba(57, 169, 0, 0.75);" aria-hidden="true"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselControlECP" data-bs-slide="next">
                <span class="carousel-control-next-icon rounded-circle p-3" style="background-color: rgba(57, 169, 0, 0.75);" aria-hidden="true"></span>
                <span class="visually-hidden">Siguiente</span>
            </button>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- BARRA DE KPIS / ÁREAS INTERACTIVAS (ESTILO UNIDAD PRODUCTIVA SENA) -->
    <!-- ========================================================================= -->
    <div class="container ecp-kpi-wrapper" id="areas-ecp">
        <div class="ecp-kpi-card-group">
            <button type="button" class="ecp-kpi-col" id="kpi-col-quienes-somos" onclick="abrirDetalleKpi('quienes-somos')" aria-expanded="false" aria-controls="detalle-kpi-section">
                <div>
                    <div class="ecp-kpi-icon-box">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="ecp-kpi-title">Quiénes somos</div>
                    <p class="ecp-kpi-desc">Conoce la Escuela Cultura de Paz.</p>
                </div>
            </button>

            <button type="button" class="ecp-kpi-col" id="kpi-col-logros" onclick="abrirDetalleKpi('logros')" aria-expanded="false" aria-controls="detalle-kpi-section">
                <div>
                    <div class="ecp-kpi-icon-box">
                        <i class="fas fa-award"></i>
                    </div>
                    <div class="ecp-kpi-title">Logros</div>
                    <p class="ecp-kpi-desc">Avances y experiencias de la ECP.</p>
                </div>
            </button>

            <button type="button" class="ecp-kpi-col" id="kpi-col-historia" onclick="abrirDetalleKpi('historia')" aria-expanded="false" aria-controls="detalle-kpi-section">
                <div>
                    <div class="ecp-kpi-icon-box">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="ecp-kpi-title">Historia</div>
                    <p class="ecp-kpi-desc">Origen y recorrido de la Escuela.</p>
                </div>
            </button>

            <button type="button" class="ecp-kpi-col" id="kpi-col-mision-vision" onclick="abrirDetalleKpi('mision-vision')" aria-expanded="false" aria-controls="detalle-kpi-section">
                <div>
                    <div class="ecp-kpi-icon-box">
                        <i class="fas fa-compass"></i>
                    </div>
                    <div class="ecp-kpi-title">Misión y visión</div>
                    <p class="ecp-kpi-desc">Nuestro propósito y horizonte.</p>
                </div>
            </button>

            <button type="button" class="ecp-kpi-col" id="kpi-col-servicios" onclick="abrirDetalleKpi('servicios')" aria-expanded="false" aria-controls="detalle-kpi-section">
                <div>
                    <div class="ecp-kpi-icon-box">
                        <i class="fas fa-hands-holding-circle"></i>
                    </div>
                    <div class="ecp-kpi-title">Nuestros servicios</div>
                    <p class="ecp-kpi-desc">Acompañamiento, formación y trabajo colaborativo.</p>
                </div>
            </button>

            <button type="button" class="ecp-kpi-col" id="kpi-col-podcast" onclick="abrirDetalleKpi('podcast')" aria-expanded="false" aria-controls="detalle-kpi-section">
                <div>
                    <div class="ecp-kpi-icon-box">
                        <i class="fas fa-podcast"></i>
                    </div>
                    <div class="ecp-kpi-title">Podcast ECP</div>
                    <p class="ecp-kpi-desc">Conversaciones sobre cultura de paz.</p>
                </div>
            </button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- DETALLES DE LAS ÁREAS CON DESPLIEGUE HACIA ABAJO -->
    <!-- ========================================================================= -->
    <section class="collapse ecp-detalle-section" id="detalle-kpi-section" aria-labelledby="detalle-tag-area">
        <div class="container">
            <div class="ecp-detalle-card-wrapper position-relative">

                <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="modal-ecp-badge-green"><i class="fas fa-seedling"></i> Escuela Cultura de Paz</span>
                        <span class="modal-ecp-badge-gold" id="detalle-tag-area"><i class="fas fa-circle-info"></i> Información de Área</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1" onclick="cerrarDetalleKpi()" title="Volver al índice">
                        <i class="fas fa-arrow-left"></i> <span>Volver al índice</span>
                    </button>
                </div>

                <div id="detalle-kpi-content"></div>
                <div class="d-none" aria-hidden="true">

                <!-- Panel 1: Momentos ECP -->
                <div id="panel-kpi-momentos" class="ecp-area-panel" style="display: none;">
                    <div class="row g-4 align-items-start">
                        <div class="col-lg-7">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded-3 d-flex align-items-center justify-content-center text-white shadow-sm" style="width: 50px; height: 50px; background: var(--sena-pastel-primary); font-size: 1.4rem;">
                                    <i class="fas fa-compass"></i>
                                </div>
                                <div>
                                    <h3 class="fw-bold mb-0" style="color: var(--sena-pastel-text-title);">¿Cómo se desarrollan los Momentos en el SENA?</h3>
                                    <small class="text-muted">Monitoreo formativo y vivencial en 3 dimensiones de aprendizaje</small>
                                </div>
                            </div>

                            <div class="modal-ecp-img-box">
                                <img src="{{ asset('general/assets/img/carrusel-1.png') }}" alt="Momentos ECP">
                                <div class="modal-ecp-img-overlay-tag">
                                    <div class="fw-bold text-uppercase"><i class="fas fa-camera me-1"></i> Momentos Formativos • 3 Dimensiones</div>
                                    <div class="text-white-50 small">Estructura pedagógica vivencial, dialógica y transformadora</div>
                                </div>
                            </div>

                            <p style="color: var(--sena-pastel-text-body); font-size: 0.95rem; line-height: 1.8; text-align: justify;">
                                La estructura de <strong>Momentos de la Escuela Cultura de Paz</strong> es una estrategia pedagógica integral desarrollada en el Centro Agroindustrial La Angostura. Se divide en tres etapas consecutivas: <strong>Autoconciencia</strong> (inteligencia intrapersonal y valores éticos), <strong>Convivencia</strong> (relaciones asertivas y trabajo colectivo) y <strong>Resolución de Conflictos</strong> (mediación formativa). Este recorrido fortalece a cada aprendiz como ciudadano integral y constructor de paz.
                            </p>

                            <div class="row g-2 pt-2">
                                <div class="col-sm-4">
                                    <div class="p-2 text-center rounded-3 border bg-light shadow-sm">
                                        <div class="badge bg-success mb-1">Momento 01</div>
                                        <div class="fw-bold small" style="color: #001A29;">Autoconciencia</div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="p-2 text-center rounded-3 border bg-light shadow-sm">
                                        <div class="badge bg-success mb-1">Momento 02</div>
                                        <div class="fw-bold small" style="color: #001A29;">Convivencia</div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="p-2 text-center rounded-3 border bg-light shadow-sm">
                                        <div class="badge bg-success mb-1">Momento 03</div>
                                        <div class="fw-bold small" style="color: #001A29;">Resolución</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <h6 class="fw-bold mb-3" style="color: #001A29;">
                                <i class="fas fa-clipboard-check text-success me-2"></i> Aspectos Clave y Buenas Prácticas
                            </h6>

                            <div class="modal-ecp-feature-card">
                                <div class="modal-ecp-feature-icon"><i class="fas fa-brain"></i></div>
                                <div>
                                    <strong class="d-block text-dark small">Autoconocimiento y Ética</strong>
                                    <span class="text-muted small">Identificación de emociones, autorreflexión y responsabilidad sobre los propios actos.</span>
                                </div>
                            </div>

                            <div class="modal-ecp-feature-card">
                                <div class="modal-ecp-feature-icon"><i class="fas fa-handshake"></i></div>
                                <div>
                                    <strong class="d-block text-dark small">Relaciones Basadas en el Respeto</strong>
                                    <span class="text-muted small">Comunicación no violenta, empatía y valoración activa de la diversidad en el aula.</span>
                                </div>
                            </div>

                            <div class="modal-ecp-feature-card">
                                <div class="modal-ecp-feature-icon"><i class="fas fa-scale-balanced"></i></div>
                                <div>
                                    <strong class="d-block text-dark small">Mediación Pedagógica</strong>
                                    <span class="text-muted small">Mecanismos formativos para transformar desacuerdos en oportunidades de mejora.</span>
                                </div>
                            </div>

                            <div class="modal-ecp-feature-card">
                                <div class="modal-ecp-feature-icon"><i class="fas fa-shield-halved"></i></div>
                                <div>
                                    <strong class="d-block text-dark small">Ambientes Seguros y Libres de Acoso</strong>
                                    <span class="text-muted small">Tolerancia cero a la discriminación, garantizando bienestar físico y emocional.</span>
                                </div>
                            </div>

                            <div class="modal-ecp-callout mt-3">
                                <strong><i class="fas fa-seedling me-1"></i> Enfoque SENA La Angostura:</strong>
                                <span class="d-block mt-1">Con el 100% de cobertura en las fichas formativas, los Momentos articulan el aprendizaje técnico agroindustrial con el fortalecimiento del ser.</span>
                            </div>

                            <div class="mt-4">
                                @guest
                                    <a href="{{ route('login') }}" class="btn btn-sena-green w-100 d-inline-flex align-items-center justify-content-center gap-2">
                                        <i class="fas fa-right-to-bracket"></i> Iniciar Sesión para Participar
                                    </a>
                                @else
                                    <a href="{{ $rutaInicioRol }}" class="btn btn-sena-green w-100 d-inline-flex align-items-center justify-content-center gap-2">
                                        <i class="fas fa-arrow-right"></i> Entrar a Momentos ECP
                                    </a>
                                @endguest
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel 2: Asesorías de Paz -->
                <div id="panel-kpi-asesorias" class="ecp-area-panel" style="display: none;">
                    <div class="row g-4 align-items-start">
                        <div class="col-lg-7">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded-3 d-flex align-items-center justify-content-center text-white shadow-sm" style="width: 50px; height: 50px; background: var(--sena-pastel-primary); font-size: 1.4rem;">
                                    <i class="fas fa-comments"></i>
                                </div>
                                <div>
                                    <h3 class="fw-bold mb-0" style="color: var(--sena-pastel-text-title);">¿Cómo funcionan las Asesorías en Control ECP?</h3>
                                    <small class="text-muted">Acompañamiento individual y confidencial para el bienestar integral</small>
                                </div>
                            </div>

                            <div class="modal-ecp-img-box">
                                <img src="{{ asset('general/assets/img/sena-empresa-equipo.jpg') }}" alt="Asesorías ECP">
                                <div class="modal-ecp-img-overlay-tag">
                                    <div class="fw-bold text-uppercase"><i class="fas fa-hands-holding-child me-1"></i> Espacio de Escucha • Seguro y Confidencial</div>
                                    <div class="text-white-50 small">Orientación socioemocional y resolución concertada de dudas</div>
                                </div>
                            </div>

                            <p style="color: var(--sena-pastel-text-body); font-size: 0.95rem; line-height: 1.8; text-align: justify;">
                                Las <strong>Asesorías de Paz</strong> son sesiones personalizadas o grupales guiadas por instructores líderes de bienestar y pedagogía. Brindan un espacio cálido de escucha activa y orientación enfocada a superar dificultades personales, académicas o interpersonales, preservando la dignidad, la privacidad y el proyecto formativo de cada aprendiz.
                            </p>

                            <div class="row g-2 pt-2">
                                <div class="col-sm-4">
                                    <div class="p-2 text-center rounded-3 border bg-light shadow-sm">
                                        <i class="fas fa-user-lock text-success mb-1"></i>
                                        <div class="fw-bold small" style="color: #001A29;">100% Confidencial</div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="p-2 text-center rounded-3 border bg-light shadow-sm">
                                        <i class="fas fa-calendar-check text-success mb-1"></i>
                                        <div class="fw-bold small" style="color: #001A29;">Agendamiento Fácil</div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="p-2 text-center rounded-3 border bg-light shadow-sm">
                                        <i class="fas fa-clipboard-list text-success mb-1"></i>
                                        <div class="fw-bold small" style="color: #001A29;">Plan Concertado</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <h6 class="fw-bold mb-3" style="color: #001A29;">
                                <i class="fas fa-clipboard-check text-success me-2"></i> Aspectos Clave y Buenas Prácticas
                            </h6>

                            <div class="modal-ecp-feature-card">
                                <div class="modal-ecp-feature-icon"><i class="fas fa-ear-listen"></i></div>
                                <div>
                                    <strong class="d-block text-dark small">Escucha Empática y sin Juicios</strong>
                                    <span class="text-muted small">Atención cálida enfocada en comprender las circunstancias particulares de cada persona.</span>
                                </div>
                            </div>

                            <div class="modal-ecp-feature-card">
                                <div class="modal-ecp-feature-icon"><i class="fas fa-calendar-plus"></i></div>
                                <div>
                                    <strong class="d-block text-dark small">Solicitud y Programación Digital</strong>
                                    <span class="text-muted small">El aprendiz solicita asesoría desde su panel y el instructor asigna fecha y lugar idóneo.</span>
                                </div>
                            </div>

                            <div class="modal-ecp-feature-card">
                                <div class="modal-ecp-feature-icon"><i class="fas fa-heart-pulse"></i></div>
                                <div>
                                    <strong class="d-block text-dark small">Prevención de la Deserción</strong>
                                    <span class="text-muted small">Identificación temprana de alertas académicas o emocionales para brindar apoyo oportuno.</span>
                                </div>
                            </div>

                            <div class="modal-ecp-feature-card">
                                <div class="modal-ecp-feature-icon"><i class="fas fa-star-half-stroke"></i></div>
                                <div>
                                    <strong class="d-block text-dark small">Evaluación del Servicio</strong>
                                    <span class="text-muted small">Calificación transparente de la asesoría para garantizar un acompañamiento de excelencia.</span>
                                </div>
                            </div>

                            <div class="modal-ecp-callout mt-3">
                                <strong><i class="fas fa-seedling me-1"></i> Enfoque SENA La Angostura:</strong>
                                <span class="d-block mt-1">Garantiza un canal accesible para que ningún aprendiz se sienta solo ante desafíos que puedan poner en riesgo su proceso formativo.</span>
                            </div>

                            <div class="mt-4">
                                @guest
                                    <a href="{{ route('login') }}" class="btn btn-sena-green w-100 d-inline-flex align-items-center justify-content-center gap-2">
                                        <i class="fas fa-right-to-bracket"></i> Iniciar Sesión para Solicitar Asesoría
                                    </a>
                                @else
                                    <a href="{{ $rutaInicioRol }}" class="btn btn-sena-green w-100 d-inline-flex align-items-center justify-content-center gap-2">
                                        <i class="fas fa-arrow-right"></i> Ir a Mis Asesorías
                                    </a>
                                @endguest
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel 3: Talleres y Actividades -->
                <div id="panel-kpi-actividades" class="ecp-area-panel" style="display: none;">
                    <div class="row g-4 align-items-start">
                        <div class="col-lg-7">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded-3 d-flex align-items-center justify-content-center text-white shadow-sm" style="width: 50px; height: 50px; background: var(--sena-pastel-primary); font-size: 1.4rem;">
                                    <i class="fas fa-people-group"></i>
                                </div>
                                <div>
                                    <h3 class="fw-bold mb-0" style="color: var(--sena-pastel-text-title);">¿Qué actividades y talleres realizamos?</h3>
                                    <small class="text-muted">Aprender haciendo a través de dinámicas grupales y espacios vivenciales</small>
                                </div>
                            </div>

                            <div class="modal-ecp-img-box">
                                <img src="{{ asset('general/assets/img/carrusel-3.png') }}" alt="Talleres ECP">
                                <div class="modal-ecp-img-overlay-tag">
                                    <div class="fw-bold text-uppercase"><i class="fas fa-users-rays me-1"></i> Formación Vivencial • Ambientes Naturales</div>
                                    <div class="text-white-50 small">Integración comunitaria al aire libre en el Centro La Angostura</div>
                                </div>
                            </div>

                            <p style="color: var(--sena-pastel-text-body); font-size: 0.95rem; line-height: 1.8; text-align: justify;">
                                En la Escuela Cultura de Paz las actividades trascienden el aula convencional. Mediante talleres vivenciales, círculos reflexivos, retos colaborativos y representaciones pedagógicas, los aprendices experimentan situaciones de toma de decisiones éticas y aprenden a construir consensos en equipo.
                            </p>

                            <div class="row g-2 pt-2">
                                <div class="col-sm-4">
                                    <div class="p-2 text-center rounded-3 border bg-light shadow-sm">
                                        <i class="fas fa-tree text-success mb-1"></i>
                                        <div class="fw-bold small" style="color: #001A29;">Al Aire Libre</div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="p-2 text-center rounded-3 border bg-light shadow-sm">
                                        <i class="fas fa-puzzle-piece text-success mb-1"></i>
                                        <div class="fw-bold small" style="color: #001A29;">Lúdico-Práctico</div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="p-2 text-center rounded-3 border bg-light shadow-sm">
                                        <i class="fas fa-camera-retro text-success mb-1"></i>
                                        <div class="fw-bold small" style="color: #001A29;">Evidencia Digital</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <h6 class="fw-bold mb-3" style="color: #001A29;">
                                <i class="fas fa-clipboard-check text-success me-2"></i> Aspectos Clave y Buenas Prácticas
                            </h6>

                            <div class="modal-ecp-feature-card">
                                <div class="modal-ecp-feature-icon"><i class="fas fa-users-gear"></i></div>
                                <div>
                                    <strong class="d-block text-dark small">Trabajo en Equipo y Cooperación</strong>
                                    <span class="text-muted small">Dinámicas para resolver retos colectivos donde todos los roles son indispensables.</span>
                                </div>
                            </div>

                            <div class="modal-ecp-feature-card">
                                <div class="modal-ecp-feature-icon"><i class="fas fa-lightbulb"></i></div>
                                <div>
                                    <strong class="d-block text-dark small">Pensamiento Crítico y Dilemas Morales</strong>
                                    <span class="text-muted small">Análisis de casos reales para fomentar juicios éticos y soluciones sostenibles.</span>
                                </div>
                            </div>

                            <div class="modal-ecp-feature-card">
                                <div class="modal-ecp-feature-icon"><i class="fas fa-bullhorn"></i></div>
                                <div>
                                    <strong class="d-block text-dark small">Comunicación Asertiva</strong>
                                    <span class="text-muted small">Prácticas guiadas para expresar desacuerdos con firmeza, respeto y calidez.</span>
                                </div>
                            </div>

                            <div class="modal-ecp-feature-card">
                                <div class="modal-ecp-feature-icon"><i class="fas fa-images"></i></div>
                                <div>
                                    <strong class="d-block text-dark small">Repositorio de Evidencias</strong>
                                    <span class="text-muted small">Registro fotográfico y memorias sistematizadas de cada actividad realizada.</span>
                                </div>
                            </div>

                            <div class="modal-ecp-callout mt-3">
                                <strong><i class="fas fa-seedling me-1"></i> Enfoque SENA La Angostura:</strong>
                                <span class="d-block mt-1">Aprovecha la riqueza natural del centro para crear experiencias formativas memorables y de alto impacto socioemocional.</span>
                            </div>

                            <div class="mt-4">
                                @guest
                                    <a href="{{ route('login') }}" class="btn btn-sena-green w-100 d-inline-flex align-items-center justify-content-center gap-2">
                                        <i class="fas fa-right-to-bracket"></i> Iniciar Sesión para Ver Actividades
                                    </a>
                                @else
                                    <a href="{{ $rutaInicioRol }}" class="btn btn-sena-green w-100 d-inline-flex align-items-center justify-content-center gap-2">
                                        <i class="fas fa-arrow-right"></i> Explorar Mis Actividades
                                    </a>
                                @endguest
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel 4: Mediación y Acuerdos -->
                <div id="panel-kpi-mediacion" class="ecp-area-panel" style="display: none;">
                    <div class="row g-4 align-items-start">
                        <div class="col-lg-7">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded-3 d-flex align-items-center justify-content-center text-white shadow-sm" style="width: 50px; height: 50px; background: var(--sena-pastel-primary); font-size: 1.4rem;">
                                    <i class="fas fa-scale-balanced"></i>
                                </div>
                                <div>
                                    <h3 class="fw-bold mb-0" style="color: var(--sena-pastel-text-title);">¿Cómo opera la Mediación y PQRS formativa?</h3>
                                    <small class="text-muted">Resolución de controversias mediante el diálogo y la justicia restaurativa</small>
                                </div>
                            </div>

                            <div class="modal-ecp-img-box">
                                <img src="{{ asset('general/assets/img/carrusel-2.png') }}" alt="Mediación ECP">
                                <div class="modal-ecp-img-overlay-tag">
                                    <div class="fw-bold text-uppercase"><i class="fas fa-handshake me-1"></i> Círculos de Diálogo • Acuerdos Gana-Gana</div>
                                    <div class="text-white-50 small">Transformación concertada del conflicto en oportunidades de aprendizaje</div>
                                </div>
                            </div>

                            <p style="color: var(--sena-pastel-text-body); font-size: 0.95rem; line-height: 1.8; text-align: justify;">
                                En lugar de sanciones punitivas, el componente de <strong>Mediación y PQRS</strong> de Control ECP promueve la justicia restaurativa y la concertación pacífica. Mediadores neutrales facilitan encuentros guiados donde las partes analizan las causas del conflicto, reparan el daño y firman acuerdos mutuos de convivencia armónica.
                            </p>

                            <div class="row g-2 pt-2">
                                <div class="col-sm-4">
                                    <div class="p-2 text-center rounded-3 border bg-light shadow-sm">
                                        <i class="fas fa-comments text-success mb-1"></i>
                                        <div class="fw-bold small" style="color: #001A29;">Círculos de Paz</div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="p-2 text-center rounded-3 border bg-light shadow-sm">
                                        <i class="fas fa-file-contract text-success mb-1"></i>
                                        <div class="fw-bold small" style="color: #001A29;">Acuerdos Mutuos</div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="p-2 text-center rounded-3 border bg-light shadow-sm">
                                        <i class="fas fa-shield-heart text-success mb-1"></i>
                                        <div class="fw-bold small" style="color: #001A29;">Buen Trato</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <h6 class="fw-bold mb-3" style="color: #001A29;">
                                <i class="fas fa-clipboard-check text-success me-2"></i> Aspectos Clave y Buenas Prácticas
                            </h6>

                            <div class="modal-ecp-feature-card">
                                <div class="modal-ecp-feature-icon"><i class="fas fa-scale-balanced"></i></div>
                                <div>
                                    <strong class="d-block text-dark small">Justicia Restaurativa y Reparación</strong>
                                    <span class="text-muted small">Foco en la restauración de los lazos de confianza entre aprendices e instructores.</span>
                                </div>
                            </div>

                            <div class="modal-ecp-feature-card">
                                <div class="modal-ecp-feature-icon"><i class="fas fa-inbox"></i></div>
                                <div>
                                    <strong class="d-block text-dark small">Módulo PQRS con Trazabilidad</strong>
                                    <span class="text-muted small">Radicación digital transparente de peticiones y quejas con respuesta formativa rápida.</span>
                                </div>
                            </div>

                            <div class="modal-ecp-feature-card">
                                <div class="modal-ecp-feature-icon"><i class="fas fa-hand-holding-heart"></i></div>
                                <div>
                                    <strong class="d-block text-dark small">Imparcialidad y Respeto</strong>
                                    <span class="text-muted small">El mediador actúa como puente sin tomar partido, facilitando la autogestión de la solución.</span>
                                </div>
                            </div>

                            <div class="modal-ecp-feature-card">
                                <div class="modal-ecp-feature-icon"><i class="fas fa-check-to-slot"></i></div>
                                <div>
                                    <strong class="d-block text-dark small">Seguimiento de Acuerdos</strong>
                                    <span class="text-muted small">Verificación periódica del cumplimiento de los compromisos adquiridos en mediación.</span>
                                </div>
                            </div>

                            <div class="modal-ecp-callout mt-3">
                                <strong><i class="fas fa-seedling me-1"></i> Enfoque SENA La Angostura:</strong>
                                <span class="d-block mt-1">Sustituye la confrontación por canales institucionales formales donde cada voz es escuchada y valorada con respeto.</span>
                            </div>

                            <div class="mt-4">
                                @guest
                                    <a href="{{ route('login') }}" class="btn btn-sena-green w-100 d-inline-flex align-items-center justify-content-center gap-2">
                                        <i class="fas fa-right-to-bracket"></i> Iniciar Sesión para Radicar PQRS
                                    </a>
                                @else
                                    <a href="{{ $rutaInicioRol }}" class="btn btn-sena-green w-100 d-inline-flex align-items-center justify-content-center gap-2">
                                        <i class="fas fa-arrow-right"></i> Ir a PQRS y Mediación
                                    </a>
                                @endguest
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel 5: Certificación ECP -->
                <div id="panel-kpi-certificacion" class="ecp-area-panel" style="display: none;">
                    <div class="row g-4 align-items-start">
                        <div class="col-lg-7">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded-3 d-flex align-items-center justify-content-center text-white shadow-sm" style="width: 50px; height: 50px; background: var(--sena-pastel-primary); font-size: 1.4rem;">
                                    <i class="fas fa-award"></i>
                                </div>
                                <div>
                                    <h3 class="fw-bold mb-0" style="color: var(--sena-pastel-text-title);">¿Cómo se obtiene la Certificación ECP?</h3>
                                    <small class="text-muted">Acreditación oficial del ciclo de formación en valores y cultura de paz</small>
                                </div>
                            </div>

                            <div class="modal-ecp-img-box">
                                <img src="{{ asset('general/assets/img/hero-img.png') }}" alt="Certificación ECP">
                                <div class="modal-ecp-img-overlay-tag">
                                    <div class="fw-bold text-uppercase"><i class="fas fa-certificate me-1"></i> Certificado Digital Oficial • SENA</div>
                                    <div class="text-white-50 small">Constancia con valor curricular avalada por el Centro La Angostura</div>
                                </div>
                            </div>

                            <p style="color: var(--sena-pastel-text-body); font-size: 0.95rem; line-height: 1.8; text-align: justify;">
                                El <strong>Certificado de la Escuela Cultura de Paz</strong> acredita formalmente que el aprendiz completó con éxito las horas pedagógicas de los 3 Momentos, mantuvo un porcentaje de asistencia superior al 80% y participó activamente en los talleres vivenciales. Este documento digital incluye código de verificación y enriquece la hoja de vida laboral del aprendiz.
                            </p>

                            <div class="row g-2 pt-2">
                                <div class="col-sm-4">
                                    <div class="p-2 text-center rounded-3 border bg-light shadow-sm">
                                        <i class="fas fa-user-check text-success mb-1"></i>
                                        <div class="fw-bold small" style="color: #001A29;">Asistencia 80%+</div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="p-2 text-center rounded-3 border bg-light shadow-sm">
                                        <i class="fas fa-qrcode text-success mb-1"></i>
                                        <div class="fw-bold small" style="color: #001A29;">Código QR</div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="p-2 text-center rounded-3 border bg-light shadow-sm">
                                        <i class="fas fa-file-pdf text-success mb-1"></i>
                                        <div class="fw-bold small" style="color: #001A29;">Descarga PDF</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <h6 class="fw-bold mb-3" style="color: #001A29;">
                                <i class="fas fa-clipboard-check text-success me-2"></i> Aspectos Clave y Buenas Prácticas
                            </h6>

                            <div class="modal-ecp-feature-card">
                                <div class="modal-ecp-feature-icon"><i class="fas fa-graduation-cap"></i></div>
                                <div>
                                    <strong class="d-block text-dark small">Valor Diferencial en el Perfil Laboral</strong>
                                    <span class="text-muted small">Certifica habilidades socioemocionales altamente cotizadas por las empresas del sector agropecuario.</span>
                                </div>
                            </div>

                            <div class="modal-ecp-feature-card">
                                <div class="modal-ecp-feature-icon"><i class="fas fa-chart-line"></i></div>
                                <div>
                                    <strong class="d-block text-dark small">Seguimiento Sistematizado</strong>
                                    <span class="text-muted small">El sistema valida automáticamente asistencias, evaluaciones y horas cursadas.</span>
                                </div>
                            </div>

                            <div class="modal-ecp-feature-card">
                                <div class="modal-ecp-feature-icon"><i class="fas fa-stamp"></i></div>
                                <div>
                                    <strong class="d-block text-dark small">Respaldo Institucional SENA</strong>
                                    <span class="text-muted small">Firma digital y visto bueno de la coordinación académica y bienestar al aprendiz.</span>
                                </div>
                            </div>

                            <div class="modal-ecp-feature-card">
                                <div class="modal-ecp-feature-icon"><i class="fas fa-mobile-screen"></i></div>
                                <div>
                                    <strong class="d-block text-dark small">Disponible 24/7</strong>
                                    <span class="text-muted small">Descargable en cualquier momento desde el portal de aprendiz para trámites y convocatorias.</span>
                                </div>
                            </div>

                            <div class="modal-ecp-callout mt-3">
                                <strong><i class="fas fa-seedling me-1"></i> Enfoque SENA La Angostura:</strong>
                                <span class="d-block mt-1">Premiamos el compromiso con la paz y la sana convivencia convirtiéndolo en un mérito formativo comprobable.</span>
                            </div>

                            <div class="mt-4">
                                @guest
                                    <a href="{{ route('login') }}" class="btn btn-sena-green w-100 d-inline-flex align-items-center justify-content-center gap-2">
                                        <i class="fas fa-right-to-bracket"></i> Iniciar Sesión para Ver Certificado
                                    </a>
                                @else
                                    <a href="{{ $rutaInicioRol }}" class="btn btn-sena-green w-100 d-inline-flex align-items-center justify-content-center gap-2">
                                        <i class="fas fa-arrow-right"></i> Ir a Mis Certificados
                                    </a>
                                @endguest
                            </div>
                        </div>
                    </div>
                </div>

                </div>
            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- FOOTER -->
    <!-- ========================================================================= -->
    <footer id="contacto" class="ecp-footer">
        <div class="container pb-5">
            <div class="row g-4 justify-content-between">
                <div class="col-lg-4 col-md-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="{{ asset('general/assets/img/logo-ecp.png') }}" alt="Logo Escuela Cultura de Paz" style="width: 50px; height: 50px; object-fit: contain; background: white; border-radius: 50%; padding: 2px; border: 2px solid var(--sena-pastel-primary); box-shadow: 0 2px 8px rgba(57, 169, 0, 0.35);">
                        <div>
                            <h4 class="fw-bold mb-0 fs-5 text-white">Control ECP</h4>
                            <span class="fs-7 fw-semibold" style="color: #62E31D;">Escuela Cultura de Paz</span>
                        </div>
                    </div>
                    <p class="small mb-0 text-white-50">Formación y convivencia para construir paz.</p>
                </div>
                <div class="col-lg-4 col-md-4">
                    <h5>SENA La Angostura</h5>
                    <p class="small mb-0 text-white-50">
                        Centro de Formación Agroindustrial<br>
                        Km 38 vía al sur · Campoalegre, Huila
                    </p>
                </div>
                <div class="col-lg-4 col-md-4">
                    <h5>Contacto</h5>
                    <ul>
                        <li><i class="fas fa-phone"></i> <span><strong class="text-white">Atención SENA:</strong> 01 8000 910270</span></li>
                        <li><i class="fas fa-envelope"></i> <span><strong class="text-white">Correo:</strong> ecp.angostura@sena.edu.co</span></li>
                    </ul>
                    <div class="mt-4">
                        <div class="d-flex align-items-center">
                            <a href="https://www.facebook.com/sena.empresa.angostura" target="_blank" class="social-circle-btn" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="https://www.instagram.com/sena.empresa" target="_blank" class="social-circle-btn" title="Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="https://www.youtube.com/@CEFASCHANNEL" target="_blank" class="social-circle-btn" title="YouTube"><i class="fab fa-youtube"></i></a>
                            <a href="https://www.tiktok.com/@senaempresa_45" target="_blank" class="social-circle-btn" title="TikTok"><i class="fab fa-tiktok"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="ecp-footer-bottom">
            <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="text-white-50">
                    &copy; {{ date('Y') }} <strong class="text-white">Control ECP</strong> · SENA La Angostura
                </div>
                <div>
                    <a href="{{ url('/') }}" class="me-3 text-white-50 text-decoration-none">
                        <i class="fas fa-house me-1 text-success"></i> Portal ERP SENA Empresa
                    </a>
                    @guest
                        <a href="{{ route('login') }}" class="text-decoration-none fw-bold" style="color: #62E31D;">
                            <i class="fas fa-lock me-1"></i> Acceso Administrativo
                        </a>
                    @else
                        <a href="{{ $rutaInicioRol }}" class="text-decoration-none fw-bold" style="color: #62E31D;">
                            <i class="fas fa-lock me-1"></i> Acceso Administrativo
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </footer>

@endsection

@push('scripts')
<script>
    const areasEcp = {
        'quienes-somos': {
            title: 'Quiénes somos',
            icon: 'fas fa-users',
            description: 'La Escuela Cultura de Paz (ECP) es una estrategia pedagógica del Centro Agroindustrial SENA La Angostura, creada para fortalecer la convivencia, el diálogo y los valores humanos en toda la comunidad educativa. Trabajamos desde tres dimensiones: el ser, el convivir y el transformar, acompañando a aprendices, instructores y personal en su desarrollo integral.',
            items: [
                { icon: 'fas fa-graduation-cap', title: 'Formación integral del ser', text: 'Promovemos el autoconocimiento, la responsabilidad personal y el crecimiento ético en cada miembro de la comunidad SENA.' },
                { icon: 'fas fa-handshake', title: 'Convivencia y diálogo', text: 'Fomentamos relaciones basadas en el respeto, la empatía y la comunicación no violenta dentro y fuera del aula.' },
                { icon: 'fas fa-shield-halved', title: 'Ambientes seguros', text: 'Garantizamos espacios libres de violencia, acoso y discriminación, donde cada persona se siente valorada y protegida.' },
                { icon: 'fas fa-users-gear', title: 'Equipo comprometido', text: 'Contamos con instructores líderes, psicólogos y bienestar institucional trabajando en red por la paz del centro.' }
            ]
        },
        'logros': {
            title: 'Logros de la Escuela',
            icon: 'fas fa-award',
            description: 'La Escuela Cultura de Paz ha alcanzado resultados significativos en la transformación de la convivencia y el bienestar del Centro Agroindustrial SENA La Angostura. Cada logro refleja el compromiso colectivo de aprendices, instructores y directivos por construir una comunidad más justa, empática y pacífica.',
            items: [
                { icon: 'fas fa-people-group', title: 'Cobertura total de fichas', text: 'Los Momentos ECP se aplican al 100% de las fichas formativas activas del centro, impactando a cientos de aprendices cada trimestre.' },
                { icon: 'fas fa-heart-pulse', title: 'Reducción de conflictos', text: 'Se ha evidenciado una disminución notable en los reportes de conflictos internos gracias a la mediación pedagógica activa.' },
                { icon: 'fas fa-certificate', title: 'Reconocimiento institucional', text: 'La ECP ha sido reconocida como buena práctica de convivencia a nivel regional dentro de la red de centros SENA.' },
                { icon: 'fas fa-seedling', title: 'Cultura de paz arraigada', text: 'Aprendices que pasaron por la ECP demuestran mayor capacidad para resolver diferencias de forma constructiva y colaborativa.' }
            ]
        },
        'historia': {
            title: 'Historia de la Escuela',
            icon: 'fas fa-book-open',
            description: 'La Escuela Cultura de Paz nació como respuesta a la necesidad de fortalecer la convivencia en el Centro Agroindustrial La Angostura del SENA. Desde sus inicios, ha evolucionado de una iniciativa de bienestar a una estrategia pedagógica estructurada que hoy hace parte del corazón formativo del centro.',
            items: [
                { icon: 'fas fa-flag', title: 'Origen y fundación', text: 'Surgió de la iniciativa de instructores y el equipo de bienestar comprometidos con generar ambientes escolares más humanos y solidarios.' },
                { icon: 'fas fa-route', title: 'Crecimiento y estructura', text: 'Con el tiempo se formalizaron los tres Momentos pedagógicos (Autoconciencia, Convivencia y Resolución de Conflictos) como eje metodológico.' },
                { icon: 'fas fa-landmark', title: 'Consolidación institucional', text: 'La ECP pasó a ser parte oficial del plan de bienestar del centro, integrándose a los procesos de formación de todas las fichas activas.' },
                { icon: 'fas fa-infinity', title: 'Hoy y el futuro', text: 'Actualmente la ECP se gestiona digitalmente a través de Control ECP, garantizando trazabilidad, seguimiento y mejora continua de cada acción.' }
            ]
        },
        'mision-vision': {
            title: 'Misión y Visión',
            icon: 'fas fa-compass',
            description: 'La Escuela Cultura de Paz orienta su quehacer desde un propósito claro y un horizonte ambicioso: ser el motor de transformación humana del Centro Agroindustrial SENA La Angostura, formando ciudadanos íntegros capaces de construir paz desde su cotidianidad.',
            items: [
                { icon: 'fas fa-bullseye', title: 'Misión', text: 'Promover una cultura de paz mediante la formación integral, el acompañamiento socioemocional y el diálogo, fortaleciendo la convivencia y el bienestar de toda la comunidad educativa SENA.' },
                { icon: 'fas fa-eye', title: 'Visión', text: 'Ser un referente nacional en formación para la paz dentro de la red SENA, reconocido por transformar vidas y generar comunidades educativas solidarias, respetuosas y colaborativas.' },
                { icon: 'fas fa-scale-balanced', title: 'Valores', text: 'Respeto, empatía, solidaridad, honestidad y compromiso con la paz son los pilares que guían cada acción de la Escuela Cultura de Paz.' },
                { icon: 'fas fa-star', title: 'Principio rector', text: 'Creemos que la paz no se enseña, se construye juntos. Cada interacción en el centro es una oportunidad para cultivarla.' }
            ]
        },
        'servicios': {
            title: 'Nuestros Servicios',
            icon: 'fas fa-hands-holding-circle',
            description: 'La Escuela Cultura de Paz ofrece un conjunto de servicios orientados al bienestar integral, la convivencia y el desarrollo humano de aprendices e instructores del Centro Agroindustrial SENA La Angostura.',
            items: [
                { icon: 'fas fa-brain', title: 'Acompañamiento psicosocial', text: 'Sesiones de escucha activa y orientación socioemocional para aprendices que enfrentan dificultades personales, académicas o relacionales.' },
                { icon: 'fas fa-people-arrows', title: 'Mediación de conflictos', text: 'Facilitación de diálogos y acuerdos entre las partes involucradas en un conflicto, buscando soluciones justas y duraderas.' },
                { icon: 'fas fa-chalkboard-user', title: 'Talleres formativos', text: 'Actividades grupales vivenciales sobre inteligencia emocional, liderazgo, comunicación asertiva y resolución de conflictos.' },
                { icon: 'fas fa-spa', title: 'Bienestar espiritual y emocional', text: 'Espacios de reflexión, mindfulness y fortalecimiento de valores que nutren el equilibrio interior de cada persona.' },
                { icon: 'fas fa-medal', title: 'Reconocimiento a la convivencia', text: 'Programa de estímulos para aprendices e instructores que demuestran comportamientos ejemplares en cultura de paz.' },
                { icon: 'fas fa-podcast', title: 'Comunicación y divulgación', text: 'Podcast ECP, boletines y contenidos digitales que difunden reflexiones y aprendizajes sobre la cultura de paz.' }
            ]
        },
        'podcast': {
            title: 'Podcast ECP',
            icon: 'fas fa-podcast',
            description: 'El Podcast de la Escuela Cultura de Paz es un espacio de reflexión, conversación y aprendizaje donde voces de la comunidad SENA La Angostura comparten experiencias, historias y herramientas para construir paz desde lo cotidiano. Cada episodio es una invitación a pensar, sentir y actuar diferente.',
            items: [
                { icon: 'fas fa-microphone-lines', title: 'Voces de la comunidad', text: 'Aprendices, instructores y líderes de bienestar comparten sus experiencias y aprendizajes en torno a la cultura de paz.' },
                { icon: 'fas fa-lightbulb', title: 'Reflexiones prácticas', text: 'Cada episodio aborda temas concretos: manejo de emociones, resolución de conflictos, liderazgo y convivencia escolar.' },
                { icon: 'fas fa-headphones', title: 'Acceso libre y gratuito', text: 'El podcast estará disponible en plataformas digitales para que toda la comunidad pueda escucharlo cuando quiera.' },
                { icon: 'fas fa-calendar-plus', title: 'Próximos episodios', text: 'Se están produciendo los primeros episodios. Pronto podrás sintonizarnos y ser parte de la conversación por la paz.' }
            ],
            note: 'El Podcast ECP estará disponible muy pronto. ¡Síguenos en redes sociales para ser el primero en enterarte!'
        }
    };

    function abrirDetalleKpi(area) {
        const info = areasEcp[area];
        const detalleElement = document.getElementById('detalle-kpi-section');
        const contenido = document.getElementById('detalle-kpi-content');
        const tagArea = document.getElementById('detalle-tag-area');
        const btnActivo = document.getElementById('kpi-col-' + area);

        if (!info || !detalleElement || !contenido || !tagArea || !btnActivo) {
            console.error('No se pudo abrir la información seleccionada de la Escuela Cultura de Paz.');
            return;
        }

        document.querySelectorAll('.ecp-kpi-col').forEach(col => {
            const isActive = col === btnActivo;
            col.classList.toggle('is-active', isActive);
            col.setAttribute('aria-expanded', isActive ? 'true' : 'false');
        });
        tagArea.innerHTML = `<i class="${info.icon} me-1" aria-hidden="true"></i> ${info.title}`;

        const items = info.items.map(item => `
            <div class="col-md-6">
                <div class="modal-ecp-feature-card h-100 mb-0">
                    <div class="modal-ecp-feature-icon"><i class="${item.icon}" aria-hidden="true"></i></div>
                    <div>
                        <strong class="d-block text-dark small">${item.title}</strong>
                        <span class="text-muted small">${item.text}</span>
                    </div>
                </div>
            </div>
        `).join('');
        const note = info.note
            ? `<div class="col-12"><div class="modal-ecp-callout"><i class="${info.icon} me-1" aria-hidden="true"></i>${info.note}</div></div>`
            : '';

        contenido.innerHTML = `
            <div class="row g-3">
                <div class="col-12">
                    <h3 class="fw-bold mb-2" style="color: var(--sena-pastel-text-title);">${info.title}</h3>
                    <p class="mb-1" style="color: var(--sena-pastel-text-body); line-height: 1.7;">${info.description}</p>
                </div>
                ${items}
                ${note}
            </div>
        `;

        if (!window.bootstrap || !bootstrap.Collapse) {
            throw new Error('Bootstrap Collapse no está disponible.');
        }

        const detalle = bootstrap.Collapse.getOrCreateInstance(detalleElement, { toggle: false });
        if (detalleElement.classList.contains('show')) {
            // Ya está abierto: solo actualiza contenido y hace scroll al detalle
            setTimeout(() => {
                detalleElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 50);
        } else {
            detalle.show();
        }
    }

    function cerrarDetalleKpi() {
        const detalleElement = document.getElementById('detalle-kpi-section');
        bootstrap.Collapse.getOrCreateInstance(detalleElement, { toggle: false }).hide();
    }

    document.getElementById('detalle-kpi-section').addEventListener('shown.bs.collapse', () => {
        // Scroll directo al panel de detalle una vez que termina de expandirse
        document.getElementById('detalle-kpi-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    document.getElementById('detalle-kpi-section').addEventListener('hidden.bs.collapse', () => {
        document.querySelectorAll('.ecp-kpi-col').forEach(col => {
            col.classList.remove('is-active');
            col.setAttribute('aria-expanded', 'false');
        });
        document.getElementById('areas-ecp').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
</script>
@endpush
