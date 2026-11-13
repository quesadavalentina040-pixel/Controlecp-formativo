
@extends('controlecp::components.layouts.dashboard')

@push('styles')

<style>
    .notif-card {
        background: #ffffff;
        border: 2px solid transparent;
        border-radius: 14px;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .notif-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 26, 41, 0.08);
    }

    .notif-kpi-label { font-size: .95rem; color: #475569; font-weight: 500; }
    .notif-kpi-value { font-size: 1.7rem; font-weight: 800; line-height: 1; margin-top: .15rem; }
    .notif-kpi-subtitle { font-size: .9rem; color: #64748b; }

    .notif-row-actions {
        display: flex;
        justify-content: flex-end;
        gap: .4rem;
        white-space: nowrap;
    }

    .notif-pagination nav .small.text-muted {
        display: none !important;
    }

    .notif-pagination {
        justify-content: center !important;
    }

    .notif-pagination nav {
        justify-content: center !important;
    }

    .btn-notif-action {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border: 1px solid transparent;
        transition: background-color .15s ease, color .15s ease, transform .15s ease, box-shadow .15s ease;
        text-decoration: none;
    }

    .btn-notif-action:hover,
    .btn-notif-action:focus-visible {
        transform: translateY(-2px);
        box-shadow: 0 3px 8px rgba(0, 26, 41, .14);
    }

    .btn-notif-open {
        background: #eaf5ff;
        border-color: #c9e6fb;
        color: #0876bd;
    }
    .btn-notif-open:hover {
        background: #0876bd;
        border-color: #0876bd;
        color: #ffffff;
    }
    .btn-notif-toggle {
        background: #f1f5f9;
        border-color: #dbe3eb;
        color: #475569;
    }
    .btn-notif-toggle:hover {
        background: #475569;
        border-color: #475569;
        color: #ffffff;
    }
    .btn-notif-delete {
        background: #fff0f0;
        border-color: #f7cccc;
        color: #d63939;
    }
    .btn-notif-delete:hover {
        background: #d63939;
        border-color: #d63939;
        color: #ffffff;
    }

    .row-no-leida {
        background-color: #f0fdf4 !important;
        font-weight: 500;
    }

    .notificaciones-header-card {
        background-color: #001A29;
        border-radius: 24px;
        padding: 1.25rem 1.5rem;
    }

    .notificaciones-header-card h1 {
        color: #ffffff !important;
    }

    .notificaciones-header-card p {
        color: #c7d4da !important;
    }

    .notificaciones-header-card .btn-outline-success {
        color: #ffffff;
        border-color: rgba(255, 255, 255, .75);
    }

    .notificaciones-header-card .btn-outline-success:hover {
        background-color: #ffffff;
        color: #001A29;
        border-color: #ffffff;
    }

    .notif-filter-card {
        padding: 20px !important;
        background: linear-gradient(135deg, #001a29 0%, #052b3d 100%);
        border: 0;
        border-radius: 18px;
    }

    .notif-filter-card:hover {
        transform: none;
        box-shadow: none;
    }

    .notif-filter-form {
        display: grid;
        grid-template-columns: 1.6fr 1fr 1fr 1fr 2.2fr;
        align-items: end;
        gap: 14px;
    }

    .notif-filter-form .form-select,
    .notif-filter-form .form-control {
        min-height: 52px;
        padding: .65rem .9rem;
        background-color: #ffffff;
        border: 1px solid #dce6eb;
        border-radius: 11px !important;
        color: #001A29;
        box-shadow: 0 2px 5px rgba(0, 15, 25, .08);
    }

    .notif-filter-form .form-label {
        color: #ffffff !important;
        font-size: .82rem;
        letter-spacing: .01em;
    }

    .notif-filter-actions {
        display: flex;
        gap: 10px;
    }

    .notif-filter-actions .btn-success {
        flex: 1;
        min-height: 52px;
        padding: 0 16px;
        border: 1px solid #39A900;
        border-radius: 11px !important;
        background: #39A900;
        color: #ffffff;
        font-weight: 700;
        box-shadow: 0 4px 10px rgba(57, 169, 0, .2);
        transition: background-color .18s ease, box-shadow .18s ease;
    }

    .notif-filter-actions .btn-success:hover {
        background: #318f00;
        border-color: #318f00;
        box-shadow: 0 6px 14px rgba(57, 169, 0, .3);
    }

    .notif-filter-actions .btn-outline-secondary {
        flex: 0 0 60px;
        min-height: 52px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border: 1px solid #ffffff;
        border-radius: 12px !important;
        background: #ffffff;
        color: #001A29;
        text-decoration: none;
        transition: background-color .18s ease, box-shadow .18s ease;
    }

    .notif-filter-actions .btn-outline-secondary:hover {
        color: #001A29;
        background: #edf2f4;
        border-color: #edf2f4;
        box-shadow: 0 4px 10px rgba(0, 0, 0, .16);
    }

    .notif-filter-actions .btn-outline-secondary i {
        color: #001A29;
    }

    @media (max-width: 991.98px) {
        .notif-filter-form {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .notif-filter-actions {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 767.98px) {
        .notif-filter-card {
            padding: 16px !important;
        }

        .notif-filter-form {
            gap: 12px;
        }
    }

    @media (max-width: 420px) {
        .notif-filter-form {
            grid-template-columns: minmax(0, 1fr);
        }

        .notif-filter-actions {
            grid-column: auto;
        }
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-3 py-3">

    <div class="notificaciones-header-card d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small fw-semibold mb-1" style="color: #39A900;">
                <i class="fas fa-bell me-2"></i>Control ECP · Panel administrativo
            </div>
            <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">
                <i class="fas fa-bell me-2" style="color: #39A900;"></i>Historial de Notificaciones
            </h1>
            <p class="mb-0 small text-muted">
                Consulta todas las novedades, avisos del sistema y eventos de tu cuenta
            </p>
        </div>

        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-success rounded-pill px-3 btn-sm" onclick="marcarTodasEnHistorial()">
                <i class="fas fa-check-double me-1"></i> Marcar todas como leídas
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="notif-card p-3 h-100" style="border-color:rgba(2,132,199,.35);">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="notif-kpi-label">Total notificaciones</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: #e0f2fe; color: #0284c7;">
                        <i class="fas fa-bell"></i>
                    </div>
                </div>
                <div class="notif-kpi-value" id="stat-total" style="color:#0284c7;">{{ $totales['total'] }}</div>
                <div class="notif-kpi-subtitle">Registradas en tu cuenta</div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="notif-card p-3 h-100" style="border-color:rgba(220,38,38,.35);">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="notif-kpi-label">No leídas</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: #fee2e2; color: #dc2626;">
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>
                <div class="notif-kpi-value" id="stat-no-leidas" style="color:#dc2626;">{{ $totales['no_leidas'] }}</div>
                <div class="notif-kpi-subtitle">Pendientes por revisar</div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="notif-card p-3 h-100" style="border-color:rgba(22,163,74,.35);">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="notif-kpi-label">Leídas</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: #dcfce7; color: #16a34a;">
                        <i class="fas fa-envelope-open"></i>
                    </div>
                </div>
                <div class="notif-kpi-value" id="stat-leidas" style="color:#16a34a;">{{ $totales['leidas'] }}</div>
                <div class="notif-kpi-subtitle">Atendidas en el sistema</div>
            </div>
        </div>
    </div>

    <div class="notif-card notif-filter-card mb-4">

        <form method="GET" action="{{ route('controlecp.notificaciones.index') }}" class="notif-filter-form">

            <div>
                <label class="form-label small fw-semibold mb-1">
                    <i class="fas fa-cubes me-1"></i> Módulo
                </label>
                <select name="modulo" class="form-select">
                    <option value="">Todos los módulos</option>

                    @foreach($modulos as $valor => $nombre)
                        <option value="{{ $valor }}" {{ request('modulo') === $valor ? 'selected' : '' }}>
                            {{ $nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label small fw-semibold mb-1">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todas</option>
                    <option value="no_leidas" {{ request('estado') === 'no_leidas' ? 'selected' : '' }}>No leídas</option>
                    <option value="leidas" {{ request('estado') === 'leidas' ? 'selected' : '' }}>Leídas</option>
                </select>
            </div>

            <div>
                <label class="form-label small fw-semibold mb-1">Desde</label>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="form-control">
            </div>

            <div>
                <label class="form-label small fw-semibold mb-1">Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="form-control">
            </div>

            <div class="notif-filter-actions">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-search me-1"></i> Buscar
                </button>
                <a href="{{ route('controlecp.notificaciones.index') }}" class="btn btn-outline-secondary" title="Restablecer filtros" aria-label="Restablecer filtros">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="notif-card mb-4 overflow-hidden shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background-color: #001A29; color: #ffffff;">
                    <tr class="small text-uppercase" style="letter-spacing: 0.5px;">
                        <th class="ps-3 py-3" style="width: 14%;">Módulo</th>
                        <th class="py-3" style="width: 38%;">Notificación</th>
                        <th class="py-3" style="width: 18%;">Fecha Generación</th>
                        <th class="py-3" style="width: 14%;">Fecha Lectura</th>
                        <th class="py-3 text-center" style="width: 8%;">Estado</th>
                        <th class="pe-3 py-3 text-end" style="width: 8%;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notificaciones as $item)
                        <tr class="{{ !$item->leida ? 'row-no-leida' : '' }}" id="fila-notif-{{ $item->id_notificacion }}">

                            <td class="ps-3 py-3">
                                <span class="badge rounded-pill bg-light text-dark border px-2 py-1 small fw-normal">
                                    <i class="fas fa-folder me-1 text-muted"></i>{{ $item->modulo_mostrado }}
                                </span>
                            </td>

                            <td>
                                <div>
                                    <div class="d-flex align-items-center gap-1 mb-1">
                                        <strong class="d-inline-block text-dark small" style="font-size: 0.85rem;">
                                            {{ $item->titulo }}
                                        </strong>
                                        @if($item->cantidad > 1)
                                            <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.65rem;">
                                                x{{ $item->cantidad }} agrupadas
                                            </span>
                                        @endif
                                    </div>
                                    @if($item->mensaje)
                                        <p class="text-muted small mb-0" style="font-size: 0.78rem;">
                                            {{ $item->mensaje }}
                                        </p>
                                    @endif
                                </div>
                            </td>

                            <td>
                                <div class="small">
                                    <span class="d-block fw-semibold" style="color: #001A29;">
                                        <i class="far fa-calendar-alt text-muted me-1"></i>{{ $item->fecha_generacion->copy()->timezone('America/Bogota')->format('d/m/Y') }}
                                    </span>
                                    <span class="text-muted" style="font-size: 0.72rem;">
                                        <i class="far fa-clock text-muted me-1"></i>{{ $item->fecha_generacion->copy()->timezone('America/Bogota')->format('g:i a') }}
                                        (<time class="notif-relative-time" data-timestamp="{{ $item->fecha_generacion->timestamp }}">{{ $item->fecha_generacion->copy()->locale('es')->diffForHumans() }}</time>)
                                    </span>
                                </div>
                            </td>

                            <td>
                                <div class="small" id="fecha-lectura-{{ $item->id_notificacion }}">
                                    @if($item->fecha_lectura)
                                        <span class="d-block text-muted">
                                            <i class="fas fa-check text-success me-1"></i>{{ $item->fecha_lectura->copy()->timezone('America/Bogota')->format('d/m/Y g:i a') }}
                                        </span>
                                    @else
                                        <span class="text-muted fst-italic" style="font-size: 0.75rem;">Sin leer aún</span>
                                    @endif
                                </div>
                            </td>

                            <td class="text-center">
                                <span class="badge rounded-pill px-3 py-1 small {{ $item->leida ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' }}" id="badge-estado-{{ $item->id_notificacion }}">
                                    {{ $item->leida ? 'Leída' : 'No leída' }}
                                </span>
                            </td>

                            <td class="pe-3 py-3 text-end">
                                <div class="notif-row-actions">

                                    <a href="{{ route('controlecp.notificaciones.ir', $item->id_notificacion) }}" class="btn-notif-action btn-notif-open" title="Abrir registro y marcar como leída" aria-label="Abrir registro y marcar como leída">
                                        <i class="fas fa-arrow-up-right-from-square"></i>
                                    </a>

                                    <button type="button" class="btn-notif-action btn-notif-toggle" title="Marcar como leída / no leída" aria-label="Marcar como leída o no leída" onclick="toggleEstadoLeida({{ $item->id_notificacion }})">
                                        <i class="fas {{ $item->leida ? 'fa-envelope' : 'fa-envelope-open' }}" id="icono-toggle-{{ $item->id_notificacion }}"></i>
                                    </button>

                                    <button type="button" class="btn-notif-action btn-notif-delete" title="Eliminar notificación" aria-label="Eliminar notificación" onclick="eliminarNotificacion({{ $item->id_notificacion }})">
                                        <i class="fas fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-bell-slash fa-3x mb-3 text-secondary opacity-50"></i>
                                    <h6 class="fw-bold text-secondary mb-1">No tienes notificaciones</h6>
                                    <p class="small mb-0">No se encontraron notificaciones registradas o coincidentes con tus filtros.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($notificaciones->hasPages())
            <div class="notif-pagination d-flex flex-wrap justify-content-between align-items-center p-3 border-top gap-2 bg-light">
                @if ($notificaciones->hasPages())
                    <nav aria-label="Paginación de notificaciones">
                        <ul class="pagination justify-content-center">
                            @if ($notificaciones->onFirstPage())
                                <li class="page-item disabled"><span class="page-link" aria-hidden="true">&lsaquo;</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $notificaciones->previousPageUrl() }}" aria-label="Página anterior">&lsaquo;</a></li>
                            @endif
                            @php
                                $ultimaPaginaMostrada = 0;
                            @endphp
                            @for ($pagina = 1; $pagina <= $notificaciones->lastPage(); $pagina++)
                                @if ($pagina === 1 || $pagina === $notificaciones->lastPage() || abs($pagina - $notificaciones->currentPage()) <= 1)
                                    @if ($ultimaPaginaMostrada > 0 && $pagina > $ultimaPaginaMostrada + 1)
                                        <li class="page-item disabled"><span class="page-link">…</span></li>
                                    @endif
                                    <li class="page-item {{ $pagina === $notificaciones->currentPage() ? 'active' : '' }}">
                                        <a class="page-link" href="{{ $notificaciones->url($pagina) }}" @if($pagina === $notificaciones->currentPage()) aria-current="page" @endif>{{ $pagina }}</a>
                                    </li>
                                    @php
                                    $ultimaPaginaMostrada = $pagina;
                                @endphp
                                @endif
                            @endfor
                            @if ($notificaciones->hasMorePages())
                                <li class="page-item"><a class="page-link" href="{{ $notificaciones->nextPageUrl() }}" aria-label="Página siguiente">&rsaquo;</a></li>
                            @else
                                <li class="page-item disabled"><span class="page-link" aria-hidden="true">&rsaquo;</span></li>
                            @endif
                        </ul>
                    </nav>
                @endif
            </div>
        @endif
    </div>

</div>

@push('scripts')

<script>
    const urlToggleBase = "{{ url('control-ecp/notificaciones') }}";
    const urlMarcarTodasHistorial = "{{ route('controlecp.notificaciones.marcar-todas') }}";
    const csrfHistorial = "{{ csrf_token() }}";

    function actualizarTiemposRelativos() {
        const ahora = Date.now() / 1000;
        const formatoRelativo = new Intl.RelativeTimeFormat('es', { numeric: 'auto' });

        document.querySelectorAll('.notif-relative-time[data-timestamp]').forEach(elemento => {
            const diferencia = Number(elemento.dataset.timestamp) - ahora;
            const segundos = Math.abs(diferencia);
            let unidad = 'second';
            let cantidad = diferencia;

            if (segundos >= 86400) {
                unidad = 'day';
                cantidad = diferencia / 86400;
            } else if (segundos >= 3600) {
                unidad = 'hour';
                cantidad = diferencia / 3600;
            } else if (segundos >= 60) {
                unidad = 'minute';
                cantidad = diferencia / 60;
            }

            elemento.textContent = formatoRelativo.format(Math.round(cantidad), unidad);
        });
    }

    actualizarTiemposRelativos();
    window.setInterval(actualizarTiemposRelativos, 30000);

    // Alterna estado de lectura de una fila
    function toggleEstadoLeida(id) {
        fetch(`${urlToggleBase}/${id}/toggle`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': csrfHistorial,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;

            const fila = document.getElementById(`fila-notif-${id}`);
            const badge = document.getElementById(`badge-estado-${id}`);
            const fechaCol = document.getElementById(`fecha-lectura-${id}`);
            const icono = document.getElementById(`icono-toggle-${id}`);

            if (data.leida) {
                if (fila) fila.classList.remove('row-no-leida');
                if (badge) {
                    badge.className = 'badge rounded-pill px-3 py-1 small bg-success-subtle text-success border border-success-subtle';
                    badge.textContent = 'Leída';
                }
                if (fechaCol) {
                    fechaCol.innerHTML = `<span class="d-block text-muted"><i class="fas fa-check text-success me-1"></i>${data.fecha_lectura || 'Ahora'}</span>`;
                }
                if (icono) {
                    icono.className = 'fas fa-envelope text-secondary';
                }
            } else {
                if (fila) fila.classList.add('row-no-leida');
                if (badge) {
                    badge.className = 'badge rounded-pill px-3 py-1 small bg-warning-subtle text-warning-emphasis border border-warning-subtle';
                    badge.textContent = 'No leída';
                }
                if (fechaCol) {
                    fechaCol.innerHTML = `<span class="text-muted fst-italic" style="font-size: 0.75rem;">Sin leer aún</span>`;
                }
                if (icono) {
                    icono.className = 'fas fa-envelope-open text-secondary';
                }
            }

            // Actualiza contadores globales
            if (typeof actualizarContadoresVisuales === 'function') {
                actualizarContadoresVisuales(data.total_no_leidas);
            }
            const statNoLeidas = document.getElementById('stat-no-leidas');
            const statLeidas = document.getElementById('stat-leidas');
            if (statNoLeidas) statNoLeidas.textContent = data.total_no_leidas;
            if (statLeidas) {
                const total = parseInt(document.getElementById('stat-total')?.textContent || '0', 10);
                statLeidas.textContent = Math.max(0, total - data.total_no_leidas);
            }
        })
        .catch(err => console.error('Error al alternar estado:', err));
    }

    // Marca todas las notificaciones como leídas desde el historial
    function marcarTodasEnHistorial() {
        fetch(urlMarcarTodasHistorial, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfHistorial,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;

            document.querySelectorAll('tr[id^="fila-notif-"]').forEach(fila => {
                fila.classList.remove('row-no-leida');
            });
            document.querySelectorAll('span[id^="badge-estado-"]').forEach(badge => {
                badge.className = 'badge rounded-pill px-3 py-1 small bg-success-subtle text-success border border-success-subtle';
                badge.textContent = 'Leída';
            });
            document.querySelectorAll('i[id^="icono-toggle-"]').forEach(ico => {
                ico.className = 'fas fa-envelope text-secondary';
            });

            const statNoLeidas = document.getElementById('stat-no-leidas');
            const statLeidas = document.getElementById('stat-leidas');
            const total = parseInt(document.getElementById('stat-total')?.textContent || '0', 10);

            if (statNoLeidas) statNoLeidas.textContent = '0';
            if (statLeidas) statLeidas.textContent = total;

            if (typeof actualizarContadoresVisuales === 'function') {
                actualizarContadoresVisuales(0);
            }
        })
        .catch(err => console.error('Error al marcar todas en historial:', err));
    }

    // Elimina una notificación de la lista
    function eliminarNotificacion(id) {
        if (!confirm('¿Deseas eliminar esta notificación?')) return;

        fetch(`${urlToggleBase}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfHistorial,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;

            const fila = document.getElementById(`fila-notif-${id}`);
            if (fila) fila.remove();

            if (typeof actualizarContadoresVisuales === 'function') {
                actualizarContadoresVisuales(data.total_no_leidas);
            }
        })
        .catch(err => console.error('Error al eliminar notificación:', err));
    }
</script>
@endpush
@endsection
