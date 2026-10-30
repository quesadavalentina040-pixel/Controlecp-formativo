
@extends('controlecp::components.layouts.dashboard')

@push('styles')

<style>
    .notif-card {
        background: #ffffff;
        border: 1px solid #e0ece4;
        border-radius: 14px;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .notif-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 26, 41, 0.08);
    }

    .btn-notif-action {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #001A29;
        transition: all .2s ease;
        text-decoration: none;
    }

    .btn-notif-action:hover {
        background: #001A29;
        color: #ffffff;
        border-color: #001A29;
    }

    .row-no-leida {
        background-color: #f0fdf4 !important;
        font-weight: 500;
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-3 py-3">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <h1 class="h3 fw-bold mb-1" style="color: #001A29; font-family: 'Outfit', sans-serif;">
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
            <div class="notif-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold text-muted">Total Notificaciones</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #e0f2fe; color: #0284c7;">
                        <i class="fas fa-bell"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 font-outfit" id="stat-total" style="color: #001A29;">{{ $totales['total'] }}</h3>
                <small class="text-muted" style="font-size: 0.75rem;">Registradas en tu cuenta</small>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="notif-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold text-muted">No Leídas</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #fee2e2; color: #dc2626;">
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 font-outfit" id="stat-no-leidas" style="color: #dc2626;">{{ $totales['no_leidas'] }}</h3>
                <small class="text-muted" style="font-size: 0.75rem;">Pendientes por revisar</small>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="notif-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-semibold text-muted">Leídas</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #dcfce7; color: #16a34a;">
                        <i class="fas fa-envelope-open"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 font-outfit" id="stat-leidas" style="color: #16a34a;">{{ $totales['leidas'] }}</h3>
                <small class="text-muted" style="font-size: 0.75rem;">Atendidas en el sistema</small>
            </div>
        </div>
    </div>

    <div class="notif-card p-3 mb-4">

        <form method="GET" action="{{ route('controlecp.notificaciones.index') }}" class="row g-2 align-items-end">

            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">
                    <i class="fas fa-cubes me-1"></i> Módulo
                </label>
                <select name="modulo" class="form-select form-select-sm rounded-pill">
                    <option value="">Todos los módulos</option>

                    @foreach($modulos as $mod)
                        <option value="{{ $mod }}" {{ request('modulo') === $mod ? 'selected' : '' }}>
                            {{ $mod }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Estado</label>
                <select name="estado" class="form-select form-select-sm rounded-pill">
                    <option value="">Todas</option>
                    <option value="no_leidas" {{ request('estado') === 'no_leidas' ? 'selected' : '' }}>No leídas</option>
                    <option value="leidas" {{ request('estado') === 'leidas' ? 'selected' : '' }}>Leídas</option>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Desde</label>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="form-control form-control-sm rounded-pill">
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="form-control form-control-sm rounded-pill">
            </div>

            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 flex-fill" style="background-color: #39A900; border-color: #39A900;">
                    <i class="fas fa-filter me-1"></i> Filtrar
                </button>
                <a href="{{ route('controlecp.notificaciones.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3" title="Limpiar filtros">
                    <i class="fas fa-rotate"></i>
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
                                    <i class="fas fa-folder me-1 text-muted"></i>{{ $item->modulo }}
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
                                        <i class="far fa-calendar-alt text-muted me-1"></i>{{ $item->fecha_generacion->format('d/m/Y') }}
                                    </span>
                                    <span class="text-muted" style="font-size: 0.72rem;">
                                        <i class="far fa-clock text-muted me-1"></i>{{ $item->fecha_generacion->format('H:i') }} ({{ $item->fecha_generacion->diffForHumans() }})
                                    </span>
                                </div>
                            </td>

                            <td>
                                <div class="small" id="fecha-lectura-{{ $item->id_notificacion }}">
                                    @if($item->fecha_lectura)
                                        <span class="d-block text-muted">
                                            <i class="fas fa-check text-success me-1"></i>{{ $item->fecha_lectura->format('d/m/Y H:i') }}
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
                                <div class="d-inline-flex align-items-center gap-1">

                                    <a href="{{ route('controlecp.notificaciones.ir', $item->id_notificacion) }}" class="btn-notif-action" title="Abrir registro y marcar como leída">
                                        <i class="fas fa-arrow-up-right-from-square text-primary" style="font-size: 0.8rem;"></i>
                                    </a>

                                    <button type="button" class="btn-notif-action" title="Marcar como leída / no leída" onclick="toggleEstadoLeida({{ $item->id_notificacion }})">
                                        <i class="fas {{ $item->leida ? 'fa-envelope' : 'fa-envelope-open' }} text-secondary" id="icono-toggle-{{ $item->id_notificacion }}" style="font-size: 0.8rem;"></i>
                                    </button>

                                    <button type="button" class="btn-notif-action" title="Eliminar notificación" onclick="eliminarNotificacion({{ $item->id_notificacion }})">
                                        <i class="fas fa-trash-can text-danger" style="font-size: 0.8rem;"></i>
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

        <div class="d-flex flex-wrap justify-content-between align-items-center p-3 border-top gap-2 bg-light">
            <span class="small text-muted">
                Mostrando {{ $notificaciones->firstItem() ?? 0 }} a {{ $notificaciones->lastItem() ?? 0 }} de {{ $notificaciones->total() }} notificaciones
            </span>
            <div>
                {{ $notificaciones->links() }}
            </div>
        </div>
    </div>

</div>

@push('scripts')

<script>
    const urlToggleBase = "{{ url('control-ecp/notificaciones') }}";
    const urlMarcarTodasHistorial = "{{ route('controlecp.notificaciones.marcar-todas') }}";
    const csrfHistorial = "{{ csrf_token() }}";

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
