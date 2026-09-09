<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión • Escuela Cultura de Paz</title>

    <link rel="icon" type="image/png" href="{{ asset('general/assets/img/logo-ecp.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --green:        #2ea04f;
            --green-dark:   #1f7a3a;
            --green-light:  #d6f0dc;
            --green-xlight: #eaf7ee;
            --text-dark:    #1b4332;
            --text-muted:   #52796f;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--green-xlight);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* ── Hojas de fondo (cuerpo) ─────────────────────────────── */
        .bg-leaf {
            position: fixed;
            opacity: 0.35;
            pointer-events: none;
            z-index: 0;
        }
        .bg-leaf-tl { top: -30px;  left: -40px;  width: 260px; transform: rotate(10deg); }
        .bg-leaf-br { bottom: -30px; right: -40px; width: 260px; transform: rotate(190deg); }

        /* ── Card ────────────────────────────────────────────────── */
        .login-card {
            position: relative;
            z-index: 1;
            display: flex;
            width: 100%;
            max-width: 820px;
            min-height: 440px;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(30, 100, 60, 0.22), 0 4px 20px rgba(0,0,0,0.08);
            background: #ffffff;
        }

        /* ── Panel izquierdo verde ───────────────────────────────── */
        .panel-left {
            width: 42%;
            flex-shrink: 0;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 44px 32px 32px;
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        /* hojas decorativas dentro del panel */
        .panel-leaf {
            position: absolute;
            opacity: 0.12;
            pointer-events: none;
        }
        .panel-leaf-bl { bottom: -18px; left: -22px;  width: 140px; transform: rotate(0deg); }
        .panel-leaf-br { bottom: -18px; right: -22px; width: 120px; transform: scaleX(-1); }
        .panel-leaf-tl { top: -18px; left: -18px;  width: 100px; transform: rotate(175deg); opacity: 0.08; }

        /* paloma */
        .dove-img {
            width: 200px;
            height: 200px;
            object-fit: contain;
            margin-bottom: 24px;
            background: #ffffff;
            border-radius: 20px;
            padding: 16px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        }

        .panel-title {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.95rem;
            color: var(--green);
            line-height: 1.1;
            margin-bottom: 6px;
        }

        .panel-sub {
            font-size: 0.92rem;
            font-weight: 400;
            color: var(--green-dark);
        }

        /* ── Panel derecho blanco ────────────────────────────────── */
        .panel-right {
            flex: 1;
            background: #e8f5e9;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 48px 44px;
        }

        /* Logo + título arriba del form */
        .form-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 32px;
        }

        .form-header-dove {
            width: 48px;
            height: 48px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .form-header-text .sub  { font-size: 0.9rem;  font-weight: 400; color: var(--text-muted); line-height: 1; }
        .form-header-text .main { font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1.35rem; color: var(--text-dark); line-height: 1.1; }

        /* Labels */
        .field-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            color: #444;
            margin-bottom: 7px;
        }

        /* Input wrapper */
        .input-wrap {
            position: relative;
            margin-bottom: 20px;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            font-size: 14px;
            pointer-events: none;
            transition: color .2s;
        }

        .field-input {
            width: 100%;
            padding: 13px 44px;
            border: 1.5px solid #c8e6c9;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: #333;
            background: #ffffff;
            outline: none;
            transition: border-color .25s, box-shadow .25s, background .25s;
        }

        .field-input::placeholder { color: #bbb; }

        .field-input:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 4px rgba(46,160,79,0.12);
            background: #fff;
        }

        .input-wrap:focus-within .input-icon { color: var(--green); }

        .toggle-eye {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            cursor: pointer;
            font-size: 14px;
            transition: color .2s;
        }
        .toggle-eye:hover { color: var(--green); }

        /* Botón */
        .btn-ingresar {
            width: 100%;
            padding: 14px;
            background: var(--green);
            color: #fff;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background .25s, transform .2s, box-shadow .25s;
            box-shadow: 0 4px 16px rgba(46,160,79,0.35);
            margin-top: 4px;
        }

        .btn-ingresar:hover {
            background: var(--green-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(46,160,79,0.45);
        }

        /* Divisor con paloma */
        .divider {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 22px;
            color: #ddd;
            font-size: 11px;
        }
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #ebebeb;
        }
        .divider svg { flex-shrink: 0; opacity: 0.45; }

        /* Alertas */
        .alert-box {
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 0.83rem;
            margin-bottom: 16px;
            display: flex;
            gap: 8px;
            align-items: flex-start;
        }
        .alert-box.error   { background: #fde8e8; color: #7f1d1d; }
        .alert-box.success { background: #dcfce7; color: #14532d; }
        .alert-box.info    { background: #e0f2fe; color: #0c4a6e; }

        /* Responsive */
        @media (max-width: 640px) {
            .login-card   { flex-direction: column; }
            .panel-left   { width: 100%; min-height: 240px; padding: 36px 24px 28px; }
            .panel-right  { padding: 36px 28px; }
            .dove-svg     { width: 110px; }
        }
    </style>
</head>
<body>

    {{-- Hojas de fondo --}}
    <svg class="bg-leaf bg-leaf-tl" viewBox="0 0 200 280" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M100 270 C40 220, 0 130, 50 30 C80 80, 120 160, 100 270Z" fill="#2ea04f"/>
        <path d="M100 270 C155 210, 185 110, 145 20 C120 70, 95 160, 100 270Z" fill="#2ea04f" opacity=".55"/>
        <line x1="100" y1="270" x2="100" y2="30" stroke="#2ea04f" stroke-width="2.5" opacity=".4"/>
    </svg>
    <svg class="bg-leaf bg-leaf-br" viewBox="0 0 200 280" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M100 270 C40 220, 0 130, 50 30 C80 80, 120 160, 100 270Z" fill="#2ea04f"/>
        <path d="M100 270 C155 210, 185 110, 145 20 C120 70, 95 160, 100 270Z" fill="#2ea04f" opacity=".55"/>
        <line x1="100" y1="270" x2="100" y2="30" stroke="#2ea04f" stroke-width="2.5" opacity=".4"/>
    </svg>

    <div class="login-card">

        {{-- ── PANEL IZQUIERDO — Verde con paloma ── --}}
        <div class="panel-left">
            <svg class="panel-leaf panel-leaf-tl" viewBox="0 0 160 220" fill="none">
                <path d="M80 215 C30 170, -5 95, 35 20 C58 65, 90 130, 80 215Z" fill="#2ea04f"/>
                <path d="M80 215 C125 165, 150 85, 118 12 C98 58, 78 135, 80 215Z" fill="#2ea04f" opacity=".5"/>
            </svg>
            <svg class="panel-leaf panel-leaf-bl" viewBox="0 0 160 220" fill="none">
                <path d="M80 215 C30 170, -5 95, 35 20 C58 65, 90 130, 80 215Z" fill="#2ea04f"/>
                <path d="M80 215 C125 165, 150 85, 118 12 C98 58, 78 135, 80 215Z" fill="#2ea04f" opacity=".5"/>
                <line x1="80" y1="215" x2="80" y2="20" stroke="#2ea04f" stroke-width="1.8" opacity=".35"/>
            </svg>
            <svg class="panel-leaf panel-leaf-br" viewBox="0 0 160 220" fill="none">
                <path d="M80 215 C30 170, -5 95, 35 20 C58 65, 90 130, 80 215Z" fill="#2ea04f"/>
                <path d="M80 215 C125 165, 150 85, 118 12 C98 58, 78 135, 80 215Z" fill="#2ea04f" opacity=".5"/>
                <line x1="80" y1="215" x2="80" y2="20" stroke="#2ea04f" stroke-width="1.8" opacity=".35"/>
            </svg>

            <div class="banner-ecp-inner">
                <a href="{{ route('controlecp.index') }}"
                   style="position:absolute; top:20px; left:20px; display:inline-flex; align-items:center; gap:7px; font-size:0.85rem; font-weight:600; color:var(--green-dark); text-decoration:none; transition:all .2s ease;"
                   onmouseover="this.style.color='var(--green)'; this.style.transform='translateX(-3px)'"
                   onmouseout="this.style.color='var(--green-dark)'; this.style.transform='translateX(0)'">
                    <i class="fas fa-arrow-left"></i> Volver al Portal Principal
                </a>
                <img src="{{ asset('general/assets/img/paloma-paz.png') }}"
                     alt="Paloma de la Paz"
                     class="dove-img">
                <h2 class="panel-title">Bienvenido</h2>
                <p class="panel-sub">Escuela Cultura de Paz</p>
            </div>
        </div>

        {{-- ── PANEL DERECHO — Formulario blanco ── --}}
        <div class="panel-right">

            {{-- Encabezado --}}
            <div class="form-header">
                <img src="{{ asset('general/assets/img/logo-ecp.png') }}"
                     alt="Logo ECP"
                     class="form-header-dove">
                <div class="form-header-text">
                    <span class="sub">Escuela</span>
                    <span class="main">Cultura de Paz</span>
                </div>
            </div>

            {{-- Alertas --}}
            @if(session('info'))
                <div class="alert-box info">
                    <i class="fas fa-info-circle" style="margin-top:2px;"></i>
                    <span>{{ session('info') }}</span>
                </div>
            @endif
            @if(session('success'))
                <div class="alert-box success">
                    <i class="fas fa-check-circle" style="margin-top:2px;"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if($errors->any())
                <div class="alert-box error">
                    <i class="fas fa-exclamation-circle" style="margin-top:2px; flex-shrink:0;"></i>
                    <div>
                        @foreach($errors->all() as $e)
                            <div>{{ $e }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Formulario --}}
            <form action="{{ route('login.post') }}" method="POST" autocomplete="off">
                @csrf
                <input type="hidden" name="redirect" value="{{ old('redirect', $redirect ?? request('redirect')) }}">

                <label class="field-label" for="email">Correo electrónico</label>
                <div class="input-wrap">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="text" id="email" name="email" value="{{ old('email') }}"
                           class="field-input" placeholder="Ingresa tu correo electrónico" required autofocus>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <label class="field-label" for="password" style="margin-bottom:0;">Contraseña</label>
                    <a href="javascript:void(0)"
                       onclick="alert('Por favor contacte al Administrador del Sistema o Soporte TIC.');"
                       style="font-size:0.83rem; font-weight:600; color:var(--green); text-decoration:none;">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>
                <div class="input-wrap" style="margin-top:7px;">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" id="password" name="password"
                           class="field-input" placeholder="Ingresa tu contraseña" required>
                    <i class="far fa-eye toggle-eye" id="togglePwd"></i>
                </div>

                {{-- Recordar sesión --}}
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:18px; margin-top:12px;">
                    <input type="checkbox" id="remember" name="remember"
                           {{ old('remember') ? 'checked' : '' }}
                           style="width:16px; height:16px; accent-color:var(--green); cursor:pointer;">
                    <label for="remember" style="font-size:0.875rem; color:#666; cursor:pointer; margin:0;">
                        Recordar mi sesión en este dispositivo
                    </label>
                </div>

                <button type="submit" class="btn-ingresar">Ingresar a Control ECP</button>

                {{-- Registrarse --}}
                <p style="text-align:center; margin-top:16px; font-size:0.85rem; color:#666;">
                    ¿No tienes cuenta?
                    <a href="javascript:void(0)"
                       onclick="alert('Contacta al Administrador del Sistema para registrar tu cuenta.');"
                       style="color:var(--green); font-weight:600; text-decoration:none;">
                        Registrarme
                    </a>
                </p>
            </form>

            <div class="divider">
                <svg width="20" height="20" viewBox="0 0 100 90" fill="none">
                    <path d="M50 18 C43 17 35 21 32 29 C29 37 33 45 40 48 C46 51 54 50 60 46 C67 42 70 34 67 26 C64 19 57 17 50 18Z"
                          stroke="#2ea04f" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                    <path d="M40 28 C32 23 22 17 12 14 C18 20 27 26 36 33" stroke="#2ea04f" stroke-width="3" stroke-linecap="round" fill="none"/>
                    <circle cx="63" cy="22" r="6" stroke="#2ea04f" stroke-width="3" fill="none"/>
                    <path d="M73 21 C78 16 84 12 90 9" stroke="#2ea04f" stroke-width="2.2" stroke-linecap="round" fill="none"/>
                    <ellipse cx="79" cy="15" rx="4" ry="2" transform="rotate(-40 79 15)" fill="#2ea04f"/>
                    <ellipse cx="85" cy="11" rx="4" ry="2" transform="rotate(-55 85 11)" fill="#2ea04f"/>
                </svg>
            </div>

            <p style="text-align:center; font-size:0.78rem; color:#52796f; margin-top:16px; line-height:1.6;">
                Centro de Formación Agroindustrial <strong>"La Angostura"</strong><br>
                SENA Empresa &copy; {{ date('Y') }} &bull; Todos los derechos reservados.
            </p>
        </div>

    </div>

    <script>
        document.getElementById('togglePwd').addEventListener('click', function () {
            const inp = document.getElementById('password');
            const hidden = inp.type === 'password';
            inp.type = hidden ? 'text' : 'password';
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>

</body>
</html>
