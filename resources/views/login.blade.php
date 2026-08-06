<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión • SENA Empresa ERP</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('general/assets/img/cefaempresa.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('general/assets/img/cefaempresa.png') }}">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --sena-green: #39A900;
            --sena-green-hover: #2b8000;
            --sena-neon: #62E31D;
            --sena-navy-dark: #00131E;
            --sena-navy-mid: #001A29;
            --sena-navy-light: #002336;
        }

        body {
            font-family: 'Nunito', 'Open Sans', sans-serif;
            background: radial-gradient(circle at 10% 20%, rgba(57, 169, 0, 0.15), transparent 40%),
                        radial-gradient(circle at 90% 80%, rgba(0, 50, 77, 0.3), transparent 50%),
                        linear-gradient(135deg, var(--sena-navy-dark) 0%, var(--sena-navy-mid) 50%, var(--sena-navy-light) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #333;
        }

        .login-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.45);
            overflow: hidden;
            width: 100%;
            max-width: 960px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .login-banner {
            background: linear-gradient(135deg, rgba(0, 26, 41, 0.95) 0%, rgba(0, 35, 54, 0.95) 100%),
                        url('{{ asset("general/assets/img/cefaempresa.png") }}') center/cover no-repeat;
            color: #ffffff;
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            border-right: 4px solid var(--sena-green);
        }

        .login-banner::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at 30% 30%, rgba(98, 227, 29, 0.12), transparent 60%);
            pointer-events: none;
        }

        .login-form-container {
            padding: 50px 40px;
            background: #ffffff;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px 16px 12px 45px;
            border: 1.5px solid #e0e0e0;
            font-size: 15px;
            transition: all 0.25s ease;
        }

        .form-control:focus {
            border-color: var(--sena-green);
            box-shadow: 0 0 0 0.25rem rgba(57, 169, 0, 0.18);
        }

        .input-group-text-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            color: #888;
            font-size: 16px;
            transition: color 0.25s ease;
        }

        .form-group-custom:focus-within .input-group-text-icon {
            color: var(--sena-green);
        }

        .btn-sena {
            background: linear-gradient(135deg, #001A29 0%, #002D44 100%);
            color: #ffffff;
            font-weight: 700;
            border-radius: 12px;
            padding: 14px 24px;
            font-size: 15px;
            border: 2px solid #39A900;
            box-shadow: 0 6px 20px rgba(0, 26, 41, 0.35);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-sena:hover {
            background: linear-gradient(135deg, #39A900 0%, #2b8000 100%);
            color: #ffffff;
            border-color: #62E31D;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(57, 169, 0, 0.45);
        }

        .btn-sena:active {
            transform: translateY(1px);
            box-shadow: 0 4px 12px rgba(57, 169, 0, 0.3);
        }

        .btn-icon-navy {
            color: #62E31D;
            font-size: 16px;
            transition: color 0.3s ease, transform 0.3s ease;
        }

        .btn-sena:hover .btn-icon-navy {
            color: #ffffff;
            transform: scale(1.15);
        }

        .back-home-btn {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-home-btn:hover {
            color: var(--sena-neon);
            transform: translateX(-4px);
        }

        .toggle-password {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #888;
            z-index: 10;
        }

        .toggle-password:hover {
            color: var(--sena-green);
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="row g-0">
            
            <!-- Left Banner: SENA Empresa Institutional Identity -->
            <div class="col-lg-5 login-banner">
                <div>
                    <a href="/" class="back-home-btn mb-4">
                        <i class="fas fa-arrow-left"></i> Volver al Portal Principal
                    </a>

                    <div class="d-flex align-items-center mb-3 mt-2">
                        <img src="{{ asset('general/assets/img/cefaempresa.png') }}" alt="Logo SENA Empresa" class="bg-white rounded-circle p-2 shadow-sm me-3" style="width: 58px; height: 58px; object-fit: contain;">
                        <div>
                            <h3 class="fw-bold mb-0 text-white fs-4">SENA EMPRESA</h3>
                            <span class="fs-7 fw-semibold" style="color: var(--sena-neon);">Plataforma ERP Integrada</span>
                        </div>
                    </div>

                    <p class="text-white-50 fs-6 leading-relaxed mb-4">
                        Modelo didáctico de formación profesional para la vivencia real del entorno empresarial en el Centro Agroindustrial <strong>"La Angostura"</strong> (Campoalegre - Huila).
                    </p>
                </div>

                <div class="border-top border-secondary border-opacity-50 pt-4">
                    <ul class="list-unstyled text-white-50 fs-7 mb-0">
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2" style="color: var(--sena-neon) !important;"></i> Procesos Estratégicos, Misionales y de Apoyo</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2" style="color: var(--sena-neon) !important;"></i> Control de Inventarios, Compras y Ventas</li>
                        <li><i class="fas fa-check-circle text-success me-2" style="color: var(--sena-neon) !important;"></i> Gestión de Talento Humano y Turnos</li>
                    </ul>
                </div>
            </div>

            <!-- Right Form: Login Authentication -->
            <div class="col-lg-7 login-form-container d-flex flex-column justify-content-between">
                <div>
                    <div class="text-center text-lg-start mb-4">
                        <h2 class="fw-bold text-dark mb-1 fs-3">Acceso al Sistema ERP</h2>
                        <p class="text-muted fs-6">Ingresa tus credenciales para administrar los procesos de SENA Empresa</p>
                    </div>

                    <form action="#" method="POST" onsubmit="event.preventDefault(); alert('Bienvenido a SENA Empresa ERP');">
                        @csrf

                        <!-- User / Email Input -->
                        <div class="mb-3 position-relative form-group-custom">
                            <label for="email" class="form-label fw-semibold text-secondary fs-7">Correo Electrónico / Usuario</label>
                            <div class="position-relative">
                                <i class="fas fa-envelope input-group-text-icon"></i>
                                <input type="email" id="email" name="email" class="form-control" placeholder="ejemplo@sena.edu.co" required autofocus>
                            </div>
                        </div>

                        <!-- Password Input -->
                        <div class="mb-3 position-relative form-group-custom">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="password" class="form-label fw-semibold text-secondary fs-7 mb-0">Contraseña</label>
                                <a href="#" class="fs-7 text-decoration-none fw-semibold" style="color: var(--sena-green);">¿Olvidaste tu contraseña?</a>
                            </div>
                            <div class="position-relative">
                                <i class="fas fa-lock input-group-text-icon"></i>
                                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                                <i class="far fa-eye toggle-password" id="togglePassword"></i>
                            </div>
                        </div>

                        <!-- Remember Me Checkbox -->
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label fs-7 text-muted" for="remember">
                                Recordar mi sesión en este dispositivo
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-sena w-100 mb-3">
                            <i class="fas fa-right-to-bracket btn-icon-navy me-2"></i>
                            <span>Iniciar Sesión en SENA Empresa</span>
                        </button>
                    </form>
                </div>

                <div class="text-center pt-3 border-top mt-4">
                    <p class="text-muted fs-7 mb-0">
                        Centro de Formación Agroindustrial <strong>"La Angostura"</strong><br>
                        SENA Empresa &copy; {{ date('Y') }} • Todos los derechos reservados.
                    </p>
                </div>
            </div>

        </div>
    </div>

    <!-- Toggle Password Script -->
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
