<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>Escuela Cultura de Paz | Control ECP - SENA</title>

    <meta name="description" content="Escuela Cultura de Paz - Centro de Formación Agroindustrial La Angostura, SENA Regional Huila. Plataforma de convivencia, mediación y formación ciudadana.">
    <meta name="keywords" content="SENA, Cultura de Paz, Convivencia, Formación, La Angostura, Control ECP, ERP SENA Empresa">
    <meta name="author" content="SENA Empresa - La Angostura">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('general/assets/img/logo-ecp.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('general/assets/img/logo-ecp.png') }}">

    <!-- Google Fonts: Poppins & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Icons: Bootstrap Icons & FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            /* Paleta Verde Pastel Claro / Soft Sage & Mint */
            --sena-pastel-primary: #74c69d;
            --sena-pastel-hover: #52b788;
            --sena-pastel-light: #d8f3dc;
            --sena-pastel-xlight: #eaf7ee;
            --sena-pastel-white-green: #f4fbf6;
            --sena-pastel-accent: #95d5b2;
            --sena-pastel-sage: #80b996;
            
            /* Textos y Contrastes Suaves */
            --sena-pastel-text-title: #1b4332;
            --sena-pastel-text-body: #2d5a46;
            --sena-pastel-text-muted: #52796f;
            --sena-border-radius: 16px;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--sena-pastel-text-body);
            background-color: var(--sena-pastel-white-green);
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            color: var(--sena-pastel-text-title);
        }

        /* Navbar Verde Pastel Claro */
        .ecp-navbar {
            background: rgba(244, 251, 246, 0.94);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-bottom: 3px solid var(--sena-pastel-primary);
            box-shadow: 0 4px 20px rgba(82, 183, 136, 0.12);
            transition: all 0.3s ease;
            z-index: 1030;
        }

        .ecp-navbar .navbar-brand {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            color: var(--sena-pastel-text-title);
            font-size: 1.25rem;
            letter-spacing: -0.5px;
        }

        .ecp-navbar .nav-link {
            color: var(--sena-pastel-text-body);
            font-weight: 600;
            font-size: 0.95rem;
            padding: 0.5rem 1rem;
            transition: all 0.25s ease;
            position: relative;
        }

        .ecp-navbar .nav-link:hover,
        .ecp-navbar .nav-link.active {
            color: #1b4332 !important;
        }

        .ecp-navbar .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 3px;
            background: var(--sena-pastel-primary);
            border-radius: 3px;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .ecp-navbar .nav-link:hover::after,
        .ecp-navbar .nav-link.active::after {
            width: 70%;
        }

        /* Botones Verde Pastel Claro */
        .btn-sena-green {
            background: var(--sena-pastel-primary);
            color: #ffffff !important;
            font-weight: 700;
            border-radius: 50px;
            padding: 0.65rem 1.85rem;
            border: 2px solid transparent;
            box-shadow: 0 4px 15px rgba(116, 198, 157, 0.45);
            transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .btn-sena-green:hover {
            background: var(--sena-pastel-hover);
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(82, 183, 136, 0.55);
        }

        .btn-sena-outline-white {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(6px);
            color: var(--sena-pastel-text-title) !important;
            font-weight: 700;
            border-radius: 50px;
            padding: 0.65rem 1.85rem;
            border: 2px solid var(--sena-pastel-primary);
            box-shadow: 0 4px 12px rgba(82, 183, 136, 0.15);
            transition: all 0.3s ease;
        }

        .btn-sena-outline-white:hover {
            background: var(--sena-pastel-light);
            color: #1b4332 !important;
            border-color: var(--sena-pastel-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(82, 183, 136, 0.25);
        }

        /* Hero Carousel Claro */
        .ecp-hero-carousel {
            position: relative;
            background: var(--sena-pastel-white-green);
            overflow: hidden;
        }

        .ecp-carousel-item {
            min-height: 82vh;
            background-size: cover;
            background-position: center;
            position: relative;
            display: flex;
            align-items: center;
        }

        .ecp-carousel-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(216, 243, 220, 0.88) 0%, rgba(183, 228, 199, 0.8) 50%, rgba(149, 213, 178, 0.85) 100%);
            z-index: 1;
        }

        .ecp-carousel-content {
            position: relative;
            z-index: 2;
        }

        /* Section Titles */
        .ecp-section-header {
            position: relative;
            margin-bottom: 3.5rem;
            text-align: center;
        }

        .ecp-section-tag {
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 0.82rem;
            font-weight: 700;
            color: #1b4332;
            background: var(--sena-pastel-light);
            padding: 0.4rem 1.3rem;
            border-radius: 50px;
            margin-bottom: 0.75rem;
            border: 1px solid rgba(116, 198, 157, 0.4);
        }

        .ecp-section-title {
            font-size: 2.35rem;
            color: var(--sena-pastel-text-title);
            font-weight: 800;
            margin-bottom: 0.75rem;
        }

        .ecp-section-subtitle {
            color: var(--sena-pastel-text-muted);
            font-size: 1.05rem;
            max-width: 680px;
            margin: 0 auto;
        }

        /* Tarjetas Verde Pastel Claro */
        .ecp-card-feature {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid rgba(116, 198, 157, 0.28);
            box-shadow: 0 10px 30px rgba(116, 198, 157, 0.08);
            padding: 2.2rem;
            transition: all 0.35s ease;
            position: relative;
            overflow: hidden;
            height: 100%;
        }

        .ecp-card-feature::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--sena-pastel-primary);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .ecp-card-feature:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(116, 198, 157, 0.22);
            border-color: var(--sena-pastel-primary);
        }

        .ecp-card-feature:hover::before {
            opacity: 1;
        }

        .ecp-moment-icon {
            width: 75px;
            height: 75px;
            border-radius: 20px;
            background: var(--sena-pastel-light);
            color: #1b4332;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 1.5rem;
            transition: all 0.35s ease;
            border: 1px solid rgba(116, 198, 157, 0.3);
        }

        .ecp-card-feature:hover .ecp-moment-icon {
            background: var(--sena-pastel-primary);
            color: #ffffff;
            transform: scale(1.1) rotate(5deg);
        }

        /* Bloques de Imagen con fondo verde pastel claro */
        .ecp-feature-img-box {
            position: relative;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 45px rgba(116, 198, 157, 0.18);
            border: 4px solid #ffffff;
        }

        .ecp-feature-badge-floating {
            position: absolute;
            bottom: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(10px);
            color: var(--sena-pastel-text-title);
            padding: 12px 20px;
            border-radius: 16px;
            border: 1px solid rgba(116, 198, 157, 0.4);
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        /* Footer Verde Pastel Claro */
        .ecp-footer {
            background: #e3f2ea;
            color: var(--sena-pastel-text-body);
            border-top: 4px solid var(--sena-pastel-primary);
            padding-top: 4.5rem;
        }

        .ecp-footer h5 {
            color: var(--sena-pastel-text-title);
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.6rem;
        }

        .ecp-footer h5::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 35px;
            height: 3px;
            background: var(--sena-pastel-primary);
            border-radius: 2px;
        }

        .ecp-footer p, .ecp-footer li {
            color: var(--sena-pastel-text-body);
            font-size: 0.92rem;
            line-height: 1.7;
        }

        .ecp-footer ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .ecp-footer ul li {
            margin-bottom: 0.6rem;
            display: flex;
            align-items: flex-start;
        }

        .ecp-footer ul li i {
            color: #2d6a4f;
            margin-right: 10px;
            margin-top: 4px;
        }

        .ecp-footer a {
            color: var(--sena-pastel-text-title);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .ecp-footer a:hover {
            color: #0b291d;
            padding-left: 4px;
        }

        .ecp-footer-bottom {
            background: #d4ebd8;
            padding: 1.5rem 0;
            border-top: 1px solid rgba(116, 198, 157, 0.3);
            font-size: 0.85rem;
            color: var(--sena-pastel-text-muted);
        }

        .social-circle-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #ffffff;
            color: #1b4332;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 8px;
            border: 1px solid rgba(116, 198, 157, 0.4);
            transition: all 0.3s ease;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(116, 198, 157, 0.15);
        }

        .social-circle-btn:hover {
            background: var(--sena-pastel-primary);
            color: #ffffff;
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(116, 198, 157, 0.35);
        }
    </style>

    @stack('styles')
</head>

<body>

    @yield('content')

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>
