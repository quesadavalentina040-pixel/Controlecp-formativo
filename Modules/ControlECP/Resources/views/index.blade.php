@extends('controlecp::components.layouts.master')

@section('content')

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
                    <a href="{{ route('login', ['redirect' => route('controlecp.elementos')]) }}" class="btn btn-sena-green d-inline-flex align-items-center gap-2">
                        <i class="fas fa-right-to-bracket"></i> Iniciar Sesión
                    </a>
                @else
                    <a href="{{ route('controlecp.elementos') }}" class="btn btn-sena-green d-inline-flex align-items-center gap-2" title="Entrar al Aplicativo Control ECP">
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
                                <a class="dropdown-item rounded-3 py-2 my-1" href="{{ route('controlecp.elementos') }}">
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
            </div>

            <!-- Diapositivas del Carrusel con Fotos Reales y Cajita Compacta -->
            <div class="carousel-inner">
                <!-- Slide 1: Principal - Kiosco / Casa de Paz en el Lago -->
                <div class="carousel-item active ecp-carousel-item" style="background-image: url('{{ asset('general/assets/img/carrusel-1.png') }}'); background-size: cover; background-position: center;">
                    <div class="ecp-carousel-overlay"></div>
                    <div class="container ecp-carousel-content py-4">
                        <div class="row justify-content-center">
                            <div class="col-xl-7 col-lg-8 col-md-10">
                                <div class="ecp-hero-card text-center">
                                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2" style="background: rgba(255, 255, 255, 0.9); border: 1px solid rgba(57, 169, 0, 0.45); box-shadow: 0 2px 8px rgba(57, 169, 0, 0.15);">
                                        <i class="fas fa-dove" style="color: var(--sena-pastel-primary); font-size: 0.8rem;"></i>
                                        <span class="fw-bold" style="color: var(--sena-pastel-text-title); font-size: 0.72rem;">Centro de Formación Agroindustrial "La Angostura"</span>
                                    </div>

                                    <h2 class="fw-bold mb-2 fs-2" style="color: var(--sena-pastel-text-title); line-height: 1.2;">
                                        Escuela Cultura <span style="color: var(--sena-pastel-primary);">de Paz</span>
                                    </h2>

                                    <p class="mb-3 mx-auto fw-medium" style="color: var(--sena-pastel-text-body); max-width: 460px; font-size: 0.92rem; line-height: 1.5;">
                                        Espacio pedagógico de formación integral, convivencia armónica, diálogo constructivo y valores humanos para la comunidad educativa SENA.
                                    </p>

                                    <div class="d-flex flex-wrap justify-content-center gap-2">
                                        @guest
                                            <a href="{{ route('login', ['redirect' => route('controlecp.elementos')]) }}" class="btn btn-sena-green px-4 py-2 fs-6 d-inline-flex align-items-center gap-2 shadow-sm">
                                                <span>Ingresar</span>
                                                <i class="fas fa-arrow-right"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('controlecp.elementos') }}" class="btn btn-sena-green px-4 py-2 fs-6 d-inline-flex align-items-center gap-2 shadow-sm">
                                                <span>Ingresar al Sistema</span>
                                                <i class="fas fa-arrow-right"></i>
                                            </a>
                                        @endguest

                                        <a href="#quienes-somos" class="btn btn-sena-outline-white px-3 py-2 fs-6 d-inline-flex align-items-center gap-2">
                                            <i class="fas fa-circle-info"></i> Conocer Más
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 2: Convivencia y Actividades al Aire Libre -->
                <div class="carousel-item ecp-carousel-item" style="background-image: url('{{ asset('general/assets/img/carrusel-2.png') }}'); background-size: cover; background-position: center;">
                    <div class="ecp-carousel-overlay"></div>
                    <div class="container ecp-carousel-content py-4">
                        <div class="row justify-content-center">
                            <div class="col-xl-7 col-lg-8 col-md-10">
                                <div class="ecp-hero-card text-center">
                                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2" style="background: rgba(255, 255, 255, 0.9); border: 1px solid rgba(57, 169, 0, 0.45); box-shadow: 0 2px 8px rgba(57, 169, 0, 0.15);">
                                        <i class="fas fa-handshake" style="color: var(--sena-pastel-primary); font-size: 0.8rem;"></i>
                                        <span class="fw-bold" style="color: var(--sena-pastel-text-title); font-size: 0.72rem;">Mediación y Diálogo Asertivo</span>
                                    </div>

                                    <h2 class="fw-bold mb-2 fs-2" style="color: var(--sena-pastel-text-title); line-height: 1.2;">
                                        Transformando Conflictos en <span style="color: var(--sena-pastel-primary);">Oportunidades de Aprendizaje</span>
                                    </h2>

                                    <p class="mb-3 mx-auto fw-medium" style="color: var(--sena-pastel-text-body); max-width: 460px; font-size: 0.92rem; line-height: 1.5;">
                                        Promovemos la resolución pacífica de controversias mediante la escucha activa, la empatía y la construcción de acuerdos colectivos sostenibles.
                                    </p>

                                    <div class="d-flex flex-wrap justify-content-center gap-2">
                                        @guest
                                            <a href="{{ route('login', ['redirect' => route('controlecp.elementos')]) }}" class="btn btn-sena-green px-4 py-2 fs-6 d-inline-flex align-items-center gap-2 shadow-sm">
                                                <span>Ingresar</span>
                                                <i class="fas fa-arrow-right"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('controlecp.elementos') }}" class="btn btn-sena-green px-4 py-2 fs-6 d-inline-flex align-items-center gap-2 shadow-sm">
                                                <span>Ingresar al Sistema</span>
                                                <i class="fas fa-arrow-right"></i>
                                            </a>
                                        @endguest
                                        <a href="#momentos" class="btn btn-sena-outline-white px-3 py-2 fs-6 d-inline-flex align-items-center gap-2">
                                            <i class="fas fa-shapes"></i> Ver Momentos ECP
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 3: Talleres Vivenciales y Formación Ciudadana -->
                <div class="carousel-item ecp-carousel-item" style="background-image: url('{{ asset('general/assets/img/carrusel-3.png') }}'); background-size: cover; background-position: center;">
                    <div class="ecp-carousel-overlay"></div>
                    <div class="container ecp-carousel-content py-4">
                        <div class="row justify-content-center">
                            <div class="col-xl-7 col-lg-8 col-md-10">
                                <div class="ecp-hero-card text-center">
                                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2" style="background: rgba(255, 255, 255, 0.9); border: 1px solid rgba(57, 169, 0, 0.45); box-shadow: 0 2px 8px rgba(57, 169, 0, 0.15);">
                                        <i class="fas fa-seedling" style="color: var(--sena-pastel-primary); font-size: 0.8rem;"></i>
                                        <span class="fw-bold" style="color: var(--sena-pastel-text-title); font-size: 0.72rem;">Formación Ética y Ciudadana</span>
                                    </div>

                                    <h2 class="fw-bold mb-2 fs-2" style="color: var(--sena-pastel-text-title); line-height: 1.2;">
                                        Liderazgo Consciente y <span style="color: var(--sena-pastel-primary);">Convivencia</span>
                                    </h2>

                                    <p class="mb-3 mx-auto fw-medium" style="color: var(--sena-pastel-text-body); max-width: 460px; font-size: 0.92rem; line-height: 1.5;">
                                        Fortalecemos las habilidades socioemocionales de aprendices e instructores para forjar líderes integrales y constructores de paz en el Huila.
                                    </p>

                                    <div class="d-flex flex-wrap justify-content-center gap-2">
                                        @guest
                                            <a href="{{ route('login', ['redirect' => route('controlecp.elementos')]) }}" class="btn btn-sena-green px-4 py-2 fs-6 d-inline-flex align-items-center gap-2 shadow-lg">
                                                <span>Ingresar</span>
                                                <i class="fas fa-arrow-right"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('controlecp.elementos') }}" class="btn btn-sena-green px-4 py-2 fs-6 d-inline-flex align-items-center gap-2 shadow-lg">
                                                <span>Ingresar al Sistema</span>
                                                <i class="fas fa-arrow-right"></i>
                                            </a>
                                        @endguest
                                        <a href="#que-hacemos" class="btn btn-sena-outline-white px-3 py-2 fs-6 d-inline-flex align-items-center gap-2">
                                            <i class="fas fa-hands-holding-circle"></i> Nuestras Acciones
                                        </a>
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
    <!-- 3. SECCIÓN: "¿QUIÉNES SOMOS?" (Imagen a la izquierda, Texto a la derecha) -->
    <!-- ========================================================================= -->
    <section id="quienes-somos" class="py-5" style="background-color: #ffffff;">
        <div class="container py-4">
            <div class="row align-items-center g-5">
                <!-- Columna Izquierda: Imagen / Ilustración con badge flotante -->
                <div class="col-lg-6">
                    <div class="ecp-feature-img-box position-relative">
                        <!-- Banner con degradado institucional SENA -->
                        <div class="p-5 text-center d-flex flex-column justify-content-center align-items-center" 
                             style="min-height: 420px; background: linear-gradient(135deg, #001A29 0%, #00324D 60%, #004b73 100%);">
                            <div class="rounded-circle p-4 mb-4 shadow-sm" style="background: rgba(255, 255, 255, 0.12); border: 2px solid rgba(98, 227, 29, 0.4);">
                                <i class="fas fa-users-rays fa-4x" style="color: #62E31D;"></i>
                            </div>
                            <h3 class="fw-bold mb-2 text-white">Comunidad Educativa Unida</h3>
                            <p class="small mb-0 px-3 fw-medium text-white-50">
                                Aprendices, Instructores y Personal Administrativo construyendo entornos seguros y colaborativos.
                            </p>
                        </div>

                        <!-- Badge Flotante -->
                        <div class="ecp-feature-badge-floating shadow-lg">
                            <div class="rounded-circle p-2 text-white d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background-color: var(--sena-pastel-primary);">
                                <i class="fas fa-dove fs-5"></i>
                            </div>
                            <div>
                                <span class="d-block fw-bold fs-6" style="color: var(--sena-pastel-text-title);">Cultura de Paz SENA</span>
                                <small style="color: var(--sena-pastel-text-muted);">Regional Huila • La Angostura</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Contenido y Misión -->
                <div class="col-lg-6">
                    <div class="ps-lg-3">
                        <span class="ecp-section-tag">
                            <i class="fas fa-shield-halved me-1"></i> ¿Quiénes Somos?
                        </span>
                        
                        <h2 class="ecp-section-title text-start mb-3">
                            Una iniciativa pedagógica para el bienestar y la transformación social
                        </h2>

                        <p class="fs-6 mb-4" style="color: var(--sena-pastel-text-body); line-height: 1.8;">
                            La <strong>Escuela Cultura de Paz (ECP)</strong> es una estrategia institucional desarrollada en el 
                            <strong>Centro de Formación Agroindustrial "La Angostura"</strong> para sembrar y fortalecer habilidades socioemocionales, 
                            la resolución pacífica de controversias y la sana convivencia en todos los ambientes formativos.
                        </p>

                        <p class="fs-6 mb-4" style="color: var(--sena-pastel-text-body); line-height: 1.8;">
                            A través de metodologías participativas y vivenciales, fomentamos la empatía, el liderazgo solidario y el pensamiento crítico, 
                            permitiendo que cada aprendiz se convierta en un agente activo de armonía tanto en el SENA como en su entorno laboral y comunitario.
                        </p>

                        <!-- Puntos Clave / Destacados -->
                        <div class="row g-3 pt-2">
                            <div class="col-sm-6">
                                <div class="d-flex align-items-start gap-3 p-3 rounded-4" style="background-color: var(--sena-pastel-white-green); border-left: 4px solid var(--sena-pastel-primary); border: 1px solid rgba(57, 169, 0, 0.25);">
                                    <i class="fas fa-check-circle fs-5 mt-1" style="color: var(--sena-pastel-primary);"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1" style="color: var(--sena-pastel-text-title);">Formación en Valores</h6>
                                        <small style="color: var(--sena-pastel-text-muted);">Respeto, tolerancia, responsabilidad y solidaridad.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="d-flex align-items-start gap-3 p-3 rounded-4" style="background-color: var(--sena-pastel-white-green); border-left: 4px solid var(--sena-pastel-primary); border: 1px solid rgba(57, 169, 0, 0.25);">
                                    <i class="fas fa-check-circle fs-5 mt-1" style="color: var(--sena-pastel-primary);"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1" style="color: var(--sena-pastel-text-title);">Diálogo y Mediación</h6>
                                        <small style="color: var(--sena-pastel-text-muted);">Espacios seguros para concertar y solucionar diferencias.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 4. SECCIÓN: "¿QUÉ HACEMOS?" (Texto a la izquierda, Imagen a la derecha) -->
    <!-- ========================================================================= -->
    <section id="que-hacemos" class="py-5" style="background-color: var(--sena-pastel-xlight);">
        <div class="container py-4">
            <div class="row align-items-center g-5">
                <!-- Columna Izquierda: Texto de Acciones y Metodología -->
                <div class="col-lg-6 order-2 order-lg-1">
                    <div class="pe-lg-3">
                        <span class="ecp-section-tag" style="background-color: #ffffff;">
                            <i class="fas fa-lightbulb me-1"></i> ¿Qué Hacemos?
                        </span>

                        <h2 class="ecp-section-title text-start mb-3">
                            Espacios dinámicos, talleres vivenciales y acompañamiento formativo
                        </h2>

                        <p class="fs-6 mb-4" style="color: var(--sena-pastel-text-body); line-height: 1.8;">
                            Desarrollamos actividades pedagógicas orientadas a la vivencia de la paz en el día a día. 
                            Integramos dinámicas grupales, círculos de diálogo y herramientas prácticas para el manejo de emociones y la comunicación asertiva.
                        </p>

                        <!-- Lista de Acciones Principales -->
                        <div class="d-flex flex-column gap-3 mb-4">
                            <div class="d-flex align-items-center gap-3 bg-white p-3 rounded-4 shadow-sm border" style="border-color: rgba(57, 169, 0, 0.2) !important;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 44px; height: 44px; background-color: var(--sena-pastel-primary); flex-shrink: 0;">
                                    <i class="fas fa-comments"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1" style="color: var(--sena-pastel-text-title);">Talleres de Habilidades Socioemocionales</h6>
                                    <p class="small mb-0" style="color: var(--sena-pastel-text-muted);">Dinámicas de autoconocimiento, regulación emocional y trabajo colaborativo.</p>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-3 bg-white p-3 rounded-4 shadow-sm border" style="border-color: rgba(57, 169, 0, 0.2) !important;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 44px; height: 44px; background-color: #00324D; flex-shrink: 0;">
                                    <i class="fas fa-scale-balanced" style="color: #62E31D;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1" style="color: var(--sena-pastel-text-title);">Círculos de Mediación y Acuerdos de Paz</h6>
                                    <p class="small mb-0" style="color: var(--sena-pastel-text-muted);">Mecanismos pedagógicos para prevenir y atender situaciones de conflicto de forma concertada.</p>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-3 bg-white p-3 rounded-4 shadow-sm border" style="border-color: rgba(57, 169, 0, 0.2) !important;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 44px; height: 44px; background-color: #2d6a4f; flex-shrink: 0;">
                                    <i class="fas fa-bullhorn"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1" style="color: var(--sena-pastel-text-title);">Campañas de Buen Trato y Ciudadanía</h6>
                                    <p class="small mb-0" style="color: var(--sena-pastel-text-muted);">Promoción de ambientes de formación libres de discriminación, acoso o violencia.</p>
                                </div>
                            </div>
                        </div>

                        @guest
                            <a href="{{ route('login', ['redirect' => route('controlecp.elementos')]) }}" class="btn btn-sena-green rounded-pill px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                                <i class="fas fa-arrow-right"></i> Participar en la Escuela
                            </a>
                        @else
                            <a href="{{ route('controlecp.elementos') }}" class="btn btn-sena-green rounded-pill px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                                <i class="fas fa-arrow-right"></i> Participar en la Escuela
                            </a>
                        @endguest
                    </div>
                </div>

                <!-- Columna Derecha: Imagen / Tarjeta Visual -->
                <div class="col-lg-6 order-1 order-lg-2">
                    <div class="ecp-feature-img-box position-relative">
                        <div class="p-5 text-center d-flex flex-column justify-content-center align-items-center" 
                             style="min-height: 420px; background: linear-gradient(135deg, #001A29 0%, #00324D 60%, #004b73 100%);">
                            <div class="rounded-circle p-4 mb-4 shadow-sm" style="background: rgba(255, 255, 255, 0.12); border: 2px solid rgba(98, 227, 29, 0.4);">
                                <i class="fas fa-hands-holding-child fa-4x" style="color: #62E31D;"></i>
                            </div>
                            <h3 class="fw-bold mb-2 text-white">Aprender Haciendo en Paz</h3>
                            <p class="small mb-0 px-3 fw-medium text-white-50">
                                Fomentamos el liderazgo juvenil y la capacidad reflexiva para afrontar los retos productivos y sociales.
                            </p>
                        </div>

                        <!-- Badge Flotante Estadístico -->
                        <div class="ecp-feature-badge-floating shadow-lg">
                            <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background-color: var(--sena-pastel-light); color: var(--sena-pastel-hover);">
                                <i class="fas fa-star fs-5" style="color: var(--sena-pastel-primary);"></i>
                            </div>
                            <div>
                                <span class="d-block fw-bold fs-6" style="color: var(--sena-pastel-text-title);">100% Espacio Didáctico</span>
                                <small style="color: var(--sena-pastel-text-muted);">Impacto en todas las fichas formativas</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 5. SECCIÓN: "MOMENTOS" (3 Tarjetas: Autoconciencia, Convivencia, Resolución) -->
    <!-- ========================================================================= -->
    <section id="momentos" class="py-5" style="background-color: #ffffff;">
        <div class="container py-4">
            <!-- Encabezado de la Sección -->
            <div class="ecp-section-header">
                <span class="ecp-section-tag">
                    <i class="fas fa-compass me-1"></i> Ejes Pedagógicos
                </span>
                <h2 class="ecp-section-title">Momentos de Cultura de Paz</h2>
                <p class="ecp-section-subtitle">
                    Estructura vivencial en 3 dimensiones para la reflexión, la interacción constructiva y la mediación pacífica en nuestra comunidad educativa.
                </p>
            </div>

            <!-- Grilla de las 3 Tarjetas (Autoconciencia, Convivencia, Resolución) -->
            <div class="row g-4">
                <!-- Momento 1: Autoconciencia -->
                <div class="col-lg-4 col-md-6">
                    <div class="ecp-card-feature text-center d-flex flex-column h-100">
                        <div class="ecp-moment-icon mx-auto">
                            <i class="fas fa-brain"></i>
                        </div>

                        <span class="badge rounded-pill px-3 py-1 mb-2 fw-bold align-self-center" style="font-size: 0.75rem; background-color: var(--sena-pastel-light); color: var(--sena-pastel-hover); border: 1px solid rgba(57, 169, 0, 0.35);">
                            MOMENTO 01
                        </span>

                        <h4 class="fw-bold mb-3" style="color: var(--sena-pastel-text-title);">Autoconciencia</h4>

                        <p class="fs-6 mb-4 flex-grow-1" style="color: var(--sena-pastel-text-body); line-height: 1.7;">
                            Reconocimiento personal de las propias emociones, fortalezas y pensamientos. 
                            Espacio para la autorreflexión, el autocontrol y el fortalecimiento de la autoestima ética.
                        </p>

                        <div class="border-top pt-3 text-start" style="border-color: rgba(57, 169, 0, 0.2) !important;">
                            <ul class="list-unstyled mb-0 small" style="color: var(--sena-pastel-text-muted);">
                                <li class="mb-2"><i class="fas fa-check me-2" style="color: var(--sena-pastel-primary);"></i> Identificación y gestión emocional</li>
                                <li class="mb-2"><i class="fas fa-check me-2" style="color: var(--sena-pastel-primary);"></i> Reflexión sobre acciones individuales</li>
                                <li><i class="fas fa-check me-2" style="color: var(--sena-pastel-primary);"></i> Responsabilidad personal y ética</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Momento 2: Convivencia -->
                <div class="col-lg-4 col-md-6">
                    <div class="ecp-card-feature text-center d-flex flex-column h-100" style="border-top: 4px solid var(--sena-pastel-primary);">
                        <div class="ecp-moment-icon mx-auto" style="background-color: #c7ecc8; color: #00324D;">
                            <i class="fas fa-hand-holding-heart"></i>
                        </div>

                        <span class="badge rounded-pill px-3 py-1 mb-2 fw-bold align-self-center" style="font-size: 0.75rem; background-color: #b7e4c7; color: var(--sena-pastel-hover); border: 1px solid rgba(57, 169, 0, 0.35);">
                            MOMENTO 02
                        </span>

                        <h4 class="fw-bold mb-3" style="color: var(--sena-pastel-text-title);">Convivencia</h4>

                        <p class="fs-6 mb-4 flex-grow-1" style="color: var(--sena-pastel-text-body); line-height: 1.7;">
                            Construcción de relaciones interpersonales basadas en el respeto mutuo, la solidaridad y la empatía. 
                            Fomento del trabajo en equipo, la inclusión y la valoración de la diversidad.
                        </p>

                        <div class="border-top pt-3 text-start" style="border-color: rgba(57, 169, 0, 0.2) !important;">
                            <ul class="list-unstyled mb-0 small" style="color: var(--sena-pastel-text-muted);">
                                <li class="mb-2"><i class="fas fa-check me-2" style="color: var(--sena-pastel-primary);"></i> Comunicación asertiva y respeto</li>
                                <li class="mb-2"><i class="fas fa-check me-2" style="color: var(--sena-pastel-primary);"></i> Inclusión y empatía comunitaria</li>
                                <li><i class="fas fa-check me-2" style="color: var(--sena-pastel-primary);"></i> Cooperación en ambientes de aprendizaje</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Momento 3: Resolución -->
                <div class="col-lg-4 col-md-12">
                    <div class="ecp-card-feature text-center d-flex flex-column h-100">
                        <div class="ecp-moment-icon mx-auto" style="background-color: #d8f3dc; color: #00324D;">
                            <i class="fas fa-scale-balanced"></i>
                        </div>

                        <span class="badge rounded-pill px-3 py-1 mb-2 fw-bold align-self-center" style="font-size: 0.75rem; background-color: var(--sena-pastel-light); color: var(--sena-pastel-hover); border: 1px solid rgba(57, 169, 0, 0.35);">
                            MOMENTO 03
                        </span>

                        <h4 class="fw-bold mb-3" style="color: var(--sena-pastel-text-title);">Resolución</h4>

                        <p class="fs-6 mb-4 flex-grow-1" style="color: var(--sena-pastel-text-body); line-height: 1.7;">
                            Herramientas y técnicas para transformar desacuerdos y controversias a través de la mediación pedagógica, 
                            el diálogo constructivo y la búsqueda de acuerdos justos gana-gana.
                        </p>

                        <div class="border-top pt-3 text-start" style="border-color: rgba(57, 169, 0, 0.2) !important;">
                            <ul class="list-unstyled mb-0 small" style="color: var(--sena-pastel-text-muted);">
                                <li class="mb-2"><i class="fas fa-check me-2" style="color: var(--sena-pastel-primary);"></i> Mediación formativa de conflictos</li>
                                <li class="mb-2"><i class="fas fa-check me-2" style="color: var(--sena-pastel-primary);"></i> Acuerdos de convivencia compartidos</li>
                                <li><i class="fas fa-check me-2" style="color: var(--sena-pastel-primary);"></i> Cultura de no violencia y reconciliación</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón de acción debajo de momentos -->
            <div class="text-center mt-5">
                @guest
                    <a href="{{ route('login', ['redirect' => route('controlecp.elementos')]) }}" class="btn btn-sena-green btn-lg px-5 py-3 rounded-pill shadow-lg d-inline-flex align-items-center gap-3">
                        <i class="fas fa-door-open"></i>
                        <span>Ingresar y Explorar Módulo ECP</span>
                    </a>
                @else
                    <a href="{{ route('controlecp.elementos') }}" class="btn btn-sena-green btn-lg px-5 py-3 rounded-pill shadow-lg d-inline-flex align-items-center gap-3">
                        <i class="fas fa-door-open"></i>
                        <span>Ingresar y Explorar Módulo ECP</span>
                    </a>
                @endguest
            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 6. FOOTER (Dark Navy SENA con texto blanco y verde luminoso) -->
    <!-- ========================================================================= -->
    <footer id="contacto" class="ecp-footer">
        <div class="container pb-5">
            <div class="row g-4 justify-content-between">
                <!-- Columna 1: Integrantes del Equipo -->
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="{{ asset('general/assets/img/logo-ecp.png') }}" alt="Logo Escuela Cultura de Paz" style="width: 50px; height: 50px; object-fit: contain; background: white; border-radius: 50%; padding: 2px; border: 2px solid var(--sena-pastel-primary); box-shadow: 0 2px 8px rgba(57, 169, 0, 0.35);">
                        <div>
                            <h4 class="fw-bold mb-0 fs-5 text-white">Control ECP</h4>
                            <span class="fs-7 fw-semibold" style="color: #62E31D;">Escuela Cultura de Paz</span>
                        </div>
                    </div>

                    <h5 class="mt-4">Integrantes del Equipo</h5>
                    <p class="small mb-3 text-white-50">
                        Desarrollado y coordinado por aprendices e instructores del Centro de Formación Agroindustrial La Angostura:
                    </p>
                    <ul>
                        <li><i class="fas fa-user-check"></i> <span><strong class="text-white">Equipo Pedagógico:</strong> Cultura de Paz ECP</span></li>
                        <li><i class="fas fa-laptop-code"></i> <span><strong class="text-white">Desarrollo de Software:</strong> Aprendices ADSO / SENA Empresa</span></li>
                        <li><i class="fas fa-chalkboard-user"></i> <span><strong class="text-white">Instructores Lideres:</strong> Formación y Bienestar</span></li>
                    </ul>
                </div>

                <!-- Columna 2: SENA (Datos Institucionales) -->
                <div class="col-lg-4 col-md-6">
                    <h5>SENA - Datos Institucionales</h5>
                    <p class="mb-3 text-white-50">
                        <i class="fas fa-landmark me-2" style="color: #62E31D;"></i> <strong class="text-white">Centro de Formación Agroindustrial "La Angostura"</strong><br>
                        <i class="fas fa-map-location-dot me-2 mt-2" style="color: #62E31D;"></i> Kilómetro 38 Vía al Sur del Huila, Neiva - Campoalegre<br>
                        <i class="fas fa-globe me-2 mt-2" style="color: #62E31D;"></i> Regional Huila • Colombia
                    </p>
                    <p class="small text-white-50">
                        Ecosistema ERP SENA Empresa • Integrando la formación profesional integral y la innovación tecnológica.
                    </p>
                </div>

                <!-- Columna 3: Teléfonos y Canales de Contacto -->
                <div class="col-lg-3 col-md-6">
                    <h5>Teléfonos y Contacto</h5>
                    <ul>
                        <li><i class="fas fa-phone"></i> <span><strong class="text-white">Atención SENA:</strong> 01 8000 910270</span></li>
                        <li><i class="fas fa-mobile-screen"></i> <span><strong class="text-white">Centro La Angostura:</strong> (608) 870 0000</span></li>
                        <li><i class="fas fa-envelope"></i> <span><strong class="text-white">Correo:</strong> ecp.angostura@sena.edu.co</span></li>
                    </ul>

                    <div class="mt-4">
                        <span class="d-block small mb-2 fw-semibold text-white">Redes Institucionales:</span>
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

        <!-- Barra Inferior de Derechos -->
        <div class="ecp-footer-bottom">
            <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="text-white-50">
                    &copy; {{ date('Y') }} <strong class="text-white">Escuela Cultura de Paz (Control ECP)</strong> • SENA La Angostura. Todos los derechos reservados.
                </div>
                <div>
                    <a href="{{ url('/') }}" class="me-3 text-white-50 text-decoration-none">
                        <i class="fas fa-house me-1 text-success"></i> Portal ERP SENA Empresa
                    </a>
                    @guest
                        <a href="{{ route('login', ['redirect' => route('controlecp.elementos')]) }}" class="text-decoration-none fw-bold" style="color: #62E31D;">
                            <i class="fas fa-lock me-1"></i> Acceso Administrativo
                        </a>
                    @else
                        <a href="{{ route('controlecp.elementos') }}" class="text-decoration-none fw-bold" style="color: #62E31D;">
                            <i class="fas fa-lock me-1"></i> Acceso Administrativo
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </footer>

@endsection