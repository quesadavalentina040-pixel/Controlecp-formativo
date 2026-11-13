{{-- Vista: Campana compartida para consultar y actualizar notificaciones. --}}
@php
    $idUsuarioAuth = auth()->id();
    $conteoInicial = \Modules\ControlECP\Entities\Notification::where('id_usuario_receptor', $idUsuarioAuth)
        ->where('leida', false)
        ->count();
    $recientesInicial = \Modules\ControlECP\Entities\Notification::where('id_usuario_receptor', $idUsuarioAuth)
        ->orderBy('fecha_generacion', 'desc')
        ->limit(6)
        ->get();
    $ultimoIdInicial = $recientesInicial->first()?->id_notificacion ?? 0;
@endphp

{{-- Estilos para la campana interactiva y el desplegable --}}
<style>
    .ecp-notif-bell-btn {
        position: relative;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background-color: var(--sena-pastel-xlight, #f0fbf4);
        color: var(--sena-navy, #001A29);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(57, 169, 0, 0.2);
        cursor: pointer;
        transition: all .2s ease;
    }

    .ecp-notif-bell-btn:hover {
        background-color: var(--sena-pastel-light, #d8f3dc);
        color: var(--sena-green, #39A900);
        transform: scale(1.05);
    }

    .ecp-notif-badge {
        position: absolute;
        top: -3px;
        right: -3px;
        background-color: #ef4444;
        color: #ffffff;
        font-size: 0.68rem;
        font-weight: 700;
        min-width: 19px;
        height: 19px;
        border-radius: 50rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 4px;
        border: 2px solid #ffffff;
        box-shadow: 0 2px 5px rgba(239, 68, 68, 0.4);
        transition: transform .2s ease;
    }

    .ecp-notif-menu {
        width: 360px;
        max-width: 90vw;
        border-radius: 16px;
        box-shadow: 0 10px 35px rgba(0, 26, 41, 0.15);
        border: 1px solid #e2e8f0;
        padding: 0;
        overflow: hidden;
    }

    .ecp-notif-item {
        padding: 10px 14px;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        transition: background-color .15s ease;
        text-decoration: none;
        display: block;
        color: inherit;
    }

    .ecp-notif-item:hover {
        background-color: #f8fafc;
    }

    .ecp-notif-item.no-leida {
        background-color: #f0fdf4;
    }

    .ecp-notif-item.no-leida:hover {
        background-color: #e6f9ed;
    }

    .ecp-notif-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: var(--sena-green, #39A900);
        flex-shrink: 0;
    }

    @keyframes ecpBellShake {
        0%, 100% { transform: rotate(0); }
        20%, 60% { transform: rotate(15deg); }
        40%, 80% { transform: rotate(-15deg); }
    }

    .ecp-bell-animating {
        transform-origin: top center;
        animation: ecpBellShake .7s ease-in-out 3;
    }
</style>

{{-- Contenedor desplegable de la campana --}}
<div class="dropdown d-inline-block" id="ecpNotifDropdownContainer">
    <button class="ecp-notif-bell-btn border-0" type="button" id="btnCampanaNotificaciones" data-bs-toggle="dropdown" aria-expanded="false" title="Notificaciones">
        <i class="fas fa-bell fs-5" id="iconoCampana"></i>
        <span class="ecp-notif-badge {{ $conteoInicial > 0 ? '' : 'd-none' }}" id="badgeContadorCampana">
            {{ $conteoInicial > 99 ? '99+' : $conteoInicial }}
        </span>
    </button>

    <div class="dropdown-menu dropdown-menu-end ecp-notif-menu mt-2" aria-labelledby="btnCampanaNotificaciones">
        {{-- Cabecera del desplegable --}}
        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom" style="background-color: #001A29; color: #ffffff;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-bell" style="color: #62E31D;"></i>
                <span class="fw-bold small font-outfit">Notificaciones</span>
            </div>
            <button type="button" class="btn btn-link text-white text-decoration-none p-0 small" style="font-size: 0.72rem;" onclick="marcarTodasComoLeidas(event)" title="Marcar todas como leídas">
                <i class="fas fa-check-double me-1"></i> Marcar todas
            </button>
        </div>

        {{-- Lista de notificaciones recientes --}}
        <div id="listaNotificacionesCampana" style="max-height: 360px; overflow-y: auto;">
            {{-- Iteración que presenta los registros de recientesInicial. --}}
            @forelse($recientesInicial as $notif)
                <div class="ecp-notif-item {{ !$notif->leida ? 'no-leida' : '' }}" id="item-notif-{{ $notif->id_notificacion }}" onclick="abrirNotificacion('{{ route('controlecp.notificaciones.ir', $notif->id_notificacion) }}')">
                    <div class="d-flex align-items-start gap-2">
                        @if(!$notif->leida)
                            <span class="ecp-notif-dot mt-1" id="dot-notif-{{ $notif->id_notificacion }}"></span>
                        @endif
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge rounded-pill bg-light text-dark border px-2 py-0 small" style="font-size: 0.65rem;">
                                    {{ $notif->modulo_mostrado }}
                                </span>
                                <small class="text-muted" style="font-size: 0.68rem;">{{ $notif->fecha_generacion->diffForHumans() }}</small>
                            </div>
                            <strong class="d-block small text-dark mb-0 text-truncate" style="max-width: 280px; font-size: 0.8rem;">
                                {{ $notif->titulo }}
                                @if($notif->cantidad > 1)
                                    <span class="badge bg-secondary rounded-pill ms-1" style="font-size: 0.6rem;">x{{ $notif->cantidad }}</span>
                                @endif
                            </strong>
                            @if($notif->mensaje)
                                <p class="text-muted small mb-0 text-truncate" style="max-width: 280px; font-size: 0.73rem;">{{ $notif->mensaje }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 px-3" id="vacioNotificacionesCampana">
                    <i class="fas fa-bell-slash fa-2x text-muted mb-2 opacity-50"></i>
                    <p class="small text-muted mb-0">No tienes notificaciones</p>
                </div>
            @endforelse
        </div>

        {{-- Pie del desplegable con enlace al historial completo --}}
        <div class="p-2 border-top bg-light text-center">
            <a href="{{ auth()->user()->hasRole('controlecp.apprentice') ? route('controlecp.aprendiz.notificaciones') : route('controlecp.notificaciones.index') }}" class="btn btn-link text-decoration-none small fw-semibold p-0" style="color: #39A900; font-size: 0.78rem;">
                Ver todo el historial <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</div>

{{-- Lógica JavaScript nativa (Sondeo en segundo plano, contador y acciones sin recarga) --}}
<script>
    let ultimoIdNotificacion = {{ $ultimoIdInicial }};
    let conteoNoLeidasGlobal = {{ $conteoInicial }};
    const urlResumenNotif = "{{ route('controlecp.notificaciones.resumen') }}";
    const urlMarcarTodas = "{{ route('controlecp.notificaciones.marcar-todas') }}";
    const notificacionesCsrfToken = "{{ csrf_token() }}";
    let contextoAudioNotificaciones = null;
    let audioNotificacionesHabilitado = false;

    function habilitarAudioNotificaciones() {
        const AudioContextClass = window.AudioContext || window.webkitAudioContext;
        if (!AudioContextClass) return;

        contextoAudioNotificaciones ??= new AudioContextClass();
        if (contextoAudioNotificaciones.state === 'suspended') {
            contextoAudioNotificaciones.resume()
                .then(() => { audioNotificacionesHabilitado = true; })
                .catch(error => console.warn('No se pudo habilitar el sonido de notificaciones:', error));
        } else {
            audioNotificacionesHabilitado = true;
        }
    }

    document.addEventListener('pointerdown', habilitarAudioNotificaciones, {once: true});
    document.addEventListener('keydown', habilitarAudioNotificaciones, {once: true});

    function sonarNotificacion() {
        if (!audioNotificacionesHabilitado || !contextoAudioNotificaciones) return;

        const oscillator = contextoAudioNotificaciones.createOscillator();
        const gain = contextoAudioNotificaciones.createGain();
        const ahora = contextoAudioNotificaciones.currentTime;
        oscillator.type = 'sine';
        oscillator.frequency.setValueAtTime(880, ahora);
        oscillator.frequency.setValueAtTime(1174, ahora + 0.12);
        gain.gain.setValueAtTime(0.0001, ahora);
        gain.gain.exponentialRampToValueAtTime(0.12, ahora + 0.02);
        gain.gain.exponentialRampToValueAtTime(0.0001, ahora + 0.32);
        oscillator.connect(gain);
        gain.connect(contextoAudioNotificaciones.destination);
        oscillator.start(ahora);
        oscillator.stop(ahora + 0.34);
    }

    function avisarNuevaNotificacion() {
        const bell = document.getElementById('iconoCampana');
        if (bell) {
            bell.classList.remove('ecp-bell-animating');
            void bell.offsetWidth;
            bell.classList.add('ecp-bell-animating');
            bell.addEventListener('animationend', () => bell.classList.remove('ecp-bell-animating'), {once: true});
        }

        const vibracionDisponible = typeof navigator.vibrate === 'function'
            && navigator.vibrate([180, 90, 180]);
        if (!vibracionDisponible) sonarNotificacion();
    }

    // Actualiza los badges de la campana y del sidebar
    function actualizarContadoresVisuales(total) {
        conteoNoLeidasGlobal = total;
        const badgeCampana = document.getElementById('badgeContadorCampana');
        const badgeSidebar = document.getElementById('ecp-notif-sidebar-badge');

        if (badgeCampana) {
            if (total > 0) {
                badgeCampana.textContent = total > 99 ? '99+' : total;
                badgeCampana.classList.remove('d-none');
            } else {
                badgeCampana.classList.add('d-none');
            }
        }

        if (badgeSidebar) {
            if (total > 0) {
                badgeSidebar.textContent = total > 99 ? '99+' : total;
                badgeSidebar.classList.remove('d-none');
            } else {
                badgeSidebar.classList.add('d-none');
            }
        }
    }

    // Inicializa contador del sidebar si existe
    document.addEventListener('DOMContentLoaded', () => {
        actualizarContadoresVisuales(conteoNoLeidasGlobal);
    });

    // Abre una notificación marcándola como leída en el backend
    function abrirNotificacion(url) {
        window.location.href = url;
    }

    // Marca todas las notificaciones como leídas
    function marcarTodasComoLeidas(e) {
        if (e) e.stopPropagation();

        fetch(urlMarcarTodas, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': notificacionesCsrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                actualizarContadoresVisuales(0);
                document.querySelectorAll('.ecp-notif-item.no-leida').forEach(el => {
                    el.classList.remove('no-leida');
                });
                document.querySelectorAll('.ecp-notif-dot').forEach(el => el.remove());
            }
        })
        .catch(err => console.error('Error al marcar notificaciones:', err));
    }

    // Sondeo en tiempo real cada 12 segundos sin recargar
    function sondearNotificaciones() {
        fetch(`${urlResumenNotif}?ultimo_id=${ultimoIdNotificacion}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;

            actualizarContadoresVisuales(data.total_no_leidas);

            // Si hay notificaciones nuevas registradas en este intervalo
            if (data.nuevas && data.nuevas.length > 0) {
                avisarNuevaNotificacion();
                ultimoIdNotificacion = data.ultimo_id;
            }

            // Actualiza la lista del menú desplegable
            if (data.recientes) {
                renderizarListaCampana(data.recientes);
            }
        })
        .catch(err => console.error('Error en sondeo de notificaciones:', err));
    }

    // Renderiza la lista dentro del desplegable
    function renderizarListaCampana(items) {
        const contenedor = document.getElementById('listaNotificacionesCampana');
        if (!contenedor) return;

        if (items.length === 0) {
            contenedor.innerHTML = `
                <div class="text-center py-4 px-3" id="vacioNotificacionesCampana">
                    <i class="fas fa-bell-slash fa-2x text-muted mb-2 opacity-50"></i>
                    <p class="small text-muted mb-0">No tienes notificaciones</p>
                </div>
            `;
            return;
        }

        contenedor.replaceChildren();
        items.forEach(n => {
            const item = document.createElement('a');
            item.className = `ecp-notif-item ${!n.leida ? 'no-leida' : ''}`;
            item.id = `item-notif-${n.id}`;
            item.href = n.url;

            const row = document.createElement('div');
            row.className = 'd-flex align-items-start gap-2';
            if (!n.leida) {
                const dot = document.createElement('span');
                dot.className = 'ecp-notif-dot mt-1';
                dot.id = `dot-notif-${n.id}`;
                row.append(dot);
            }

            const content = document.createElement('div');
            content.className = 'flex-grow-1';
            const meta = document.createElement('div');
            meta.className = 'd-flex justify-content-between align-items-center mb-1';
            const module = document.createElement('span');
            module.className = 'badge rounded-pill bg-light text-dark border px-2 py-0 small';
            module.style.fontSize = '0.65rem';
            module.textContent = n.modulo || 'Control ECP';
            const date = document.createElement('small');
            date.className = 'text-muted';
            date.style.fontSize = '0.68rem';
            date.textContent = n.fecha || '';
            meta.append(module, date);

            const title = document.createElement('strong');
            title.className = 'd-block small text-dark mb-0 text-truncate';
            title.style.maxWidth = '280px';
            title.style.fontSize = '0.8rem';
            title.textContent = n.titulo || 'Nueva notificación';
            if (n.cantidad > 1) {
                const count = document.createElement('span');
                count.className = 'badge bg-secondary rounded-pill ms-1';
                count.style.fontSize = '0.6rem';
                count.textContent = `x${n.cantidad}`;
                title.append(count);
            }
            content.append(meta, title);

            if (n.mensaje) {
                const message = document.createElement('p');
                message.className = 'text-muted small mb-0 text-truncate';
                message.style.maxWidth = '280px';
                message.style.fontSize = '0.73rem';
                message.textContent = n.mensaje;
                content.append(message);
            }

            row.append(content);
            item.append(row);
            contenedor.append(item);
        });
    }

    // Inicia sondeo periódico cada 12 segundos
    setInterval(sondearNotificaciones, 12000);
</script>
