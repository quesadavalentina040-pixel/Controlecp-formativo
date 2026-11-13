@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .apr-notif-header { background:#001A29; border-radius:20px; padding:1.25rem 1.5rem; }
    .apr-notif-header h1 { color:#fff; }
    .apr-notif-header p { color:#c7d4da; }
    .apr-notif-header .btn-outline-success { border-color:rgba(255,255,255,.75); color:#fff; }
    .apr-notif-header .btn-outline-success:hover { border-color:#fff; background:#fff; color:#001A29; }
    .apr-notif-kpi, .apr-notif-table-card { background:#fff; border:1px solid #e2e8f0; border-radius:14px; box-shadow:0 2px 10px rgba(0,26,41,.04); }
    .apr-notif-kpi { min-height:124px; padding:1rem 1.15rem; }
    .apr-notif-kpi-label { color:#64748b; font-size:.9rem; }
    .apr-notif-kpi-value { margin-top:.2rem; font-size:1.65rem; font-weight:800; line-height:1; }
    .apr-notif-kpi-subtitle { color:#94a3b8; font-size:.8rem; }
    .apr-notif-kpi-icon { display:flex; width:38px; height:38px; align-items:center; justify-content:center; border-radius:50%; }
    .apr-notif-filter { padding:1rem; border-radius:16px; background:#001A29; }
    .apr-notif-filter .form-label { color:#fff; font-size:.76rem; font-weight:700; }
    .apr-notif-filter .form-select, .apr-notif-filter .form-control { min-height:40px; border:1px solid #dce5eb; border-radius:9px; color:#001A29; }
    .apr-notif-filter .btn-search { min-height:40px; border:1px solid #39A900; border-radius:9px; background:#39A900; color:#fff; font-weight:700; }
    .apr-notif-filter .btn-reset { width:42px; min-height:40px; border:1px solid #dce5eb; border-radius:9px; background:#fff; color:#001A29; }
    .apr-notif-table { min-width:900px; }
    .apr-notif-table thead th { padding:.85rem .75rem; background:#fff; color:#001A29; border-bottom:1px solid #dce5eb; font-size:.7rem; letter-spacing:.03em; text-transform:uppercase; white-space:nowrap; }
    .apr-notif-table tbody td { padding:.75rem; border-color:#e7edf1; vertical-align:middle; }
    .apr-notif-table tbody tr:last-child td { border-bottom:0; }
    .apr-notif-row.unread { background:#f7fcf8; }
    .apr-notif-module { display:inline-flex; align-items:center; gap:.3rem; padding:.22rem .5rem; border:1px solid #e2e8f0; border-radius:999px; background:#f8fafc; color:#475569; font-size:.7rem; white-space:nowrap; }
    .apr-notif-title { color:#1e293b; font-size:.8rem; font-weight:800; }
    .apr-notif-message { max-width:440px; color:#64748b; font-size:.72rem; }
    .apr-notif-date { color:#334155; font-size:.74rem; font-weight:700; white-space:nowrap; }
    .apr-notif-date-detail { color:#64748b; font-size:.68rem; white-space:nowrap; }
    .apr-notif-status { display:inline-flex; padding:.2rem .55rem; border:1px solid transparent; border-radius:999px; font-size:.68rem; font-weight:750; }
    .apr-notif-status.is-read { border-color:#a9dfbf; background:#e1f4e9; color:#16804a; }
    .apr-notif-status.is-unread { border-color:#ffd675; background:#fff5d8; color:#a66a00; }
    .apr-notif-actions { display:flex; justify-content:flex-end; gap:.3rem; white-space:nowrap; }
    .apr-notif-action { display:inline-flex; width:32px; height:32px; align-items:center; justify-content:center; padding:0; border:1px solid transparent; border-radius:9px; text-decoration:none; transition:transform .15s ease, box-shadow .15s ease; }
    .apr-notif-action:hover { transform:translateY(-1px); box-shadow:0 3px 8px rgba(0,26,41,.12); }
    .apr-notif-action--open { border-color:#c9e6fb; background:#eaf5ff; color:#0876bd; }
    .apr-notif-action--toggle { border-color:#dbe3eb; background:#f1f5f9; color:#475569; }
    .apr-notif-action--delete { border-color:#f7cccc; background:#fff0f0; color:#d63939; }
    .apr-notif-pagination .pagination { margin:0; }
    .apr-notif-empty { min-height:220px; color:#64748b; }
    @media (max-width:767px) {
        .apr-notif-header { padding:1rem; }
        .apr-notif-filter { padding:.8rem; }
        .apr-notif-kpi { min-height:112px; }
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-3 py-3" id="aprendizNotificaciones">
    <div class="apr-notif-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small fw-semibold mb-1" style="color:#62E31D">Control ECP · Aprendiz</div>
            <h1 class="h3 fw-bold mb-1"><i class="fas fa-bell me-2" style="color:#62E31D"></i>Historial de Notificaciones</h1>
            <p class="mb-0 small">Consulta todas las novedades, avisos del sistema y eventos de tu cuenta.</p>
        </div>
        <div>
            <button type="button" class="btn btn-outline-success rounded-pill px-3" id="marcarTodasNotificaciones" @disabled($totales['no_leidas'] === 0)>
                <i class="fas fa-check-double me-1"></i>Marcar todas como leídas
            </button>
        </div>
    </div>

    <div class="alert alert-danger d-none" id="errorNotificaciones" role="alert"></div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4"><div class="apr-notif-kpi h-100" style="border-color:rgba(2,132,199,.35)"><div class="d-flex justify-content-between align-items-center"><span class="apr-notif-kpi-label">Total notificaciones</span><span class="apr-notif-kpi-icon" style="background:#e0f2fe;color:#0284c7"><i class="fas fa-bell"></i></span></div><div class="apr-notif-kpi-value" style="color:#0284c7" id="totalNotificaciones">{{ $totales['total'] }}</div><div class="apr-notif-kpi-subtitle">Registradas en tu cuenta</div></div></div>
        <div class="col-12 col-md-4"><div class="apr-notif-kpi h-100" style="border-color:rgba(220,38,38,.35)"><div class="d-flex justify-content-between align-items-center"><span class="apr-notif-kpi-label">No leídas</span><span class="apr-notif-kpi-icon" style="background:#fee2e2;color:#dc2626"><i class="fas fa-envelope"></i></span></div><div class="apr-notif-kpi-value" style="color:#dc2626" id="totalNoLeidas">{{ $totales['no_leidas'] }}</div><div class="apr-notif-kpi-subtitle">Pendientes por revisar</div></div></div>
        <div class="col-12 col-md-4"><div class="apr-notif-kpi h-100" style="border-color:rgba(22,163,74,.35)"><div class="d-flex justify-content-between align-items-center"><span class="apr-notif-kpi-label">Leídas</span><span class="apr-notif-kpi-icon" style="background:#dcfce7;color:#16a34a"><i class="fas fa-envelope-open"></i></span></div><div class="apr-notif-kpi-value" style="color:#16a34a" id="totalLeidas">{{ $totales['leidas'] }}</div><div class="apr-notif-kpi-subtitle">Atendidas en el sistema</div></div></div>
    </div>

    <form method="GET" action="{{ route('controlecp.aprendiz.notificaciones') }}" class="apr-notif-filter mb-3">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label mb-1" for="filtroModuloNotif"><i class="fas fa-cubes me-1"></i>Módulo</label>
                <select class="form-select" name="modulo" id="filtroModuloNotif">
                    <option value="">Todos</option>
                    @foreach($modulos as $valor => $nombre)<option value="{{ $valor }}" @selected(($filtros['modulo'] ?? '') === $valor)>{{ $nombre }}</option>@endforeach
                </select>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label mb-1" for="filtroEstadoNotif">Estado</label>
                <select class="form-select" name="estado" id="filtroEstadoNotif">
                    <option value="">Todas</option>
                    <option value="no_leidas" @selected(($filtros['estado'] ?? '') === 'no_leidas')>No leídas</option>
                    <option value="leidas" @selected(($filtros['estado'] ?? '') === 'leidas')>Leídas</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-1" for="filtroFechaDesde">Desde</label>
                <input class="form-control" type="date" name="fecha_desde" id="filtroFechaDesde" value="{{ $filtros['fecha_desde'] ?? '' }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-1" for="filtroFechaHasta">Hasta</label>
                <input class="form-control" type="date" name="fecha_hasta" id="filtroFechaHasta" value="{{ $filtros['fecha_hasta'] ?? '' }}">
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button class="btn btn-search flex-grow-1" type="submit"><i class="fas fa-search me-1"></i>Buscar</button>
                <a class="btn btn-reset" href="{{ route('controlecp.aprendiz.notificaciones') }}" aria-label="Limpiar filtros" title="Limpiar filtros"><i class="fas fa-rotate-left"></i></a>
            </div>
        </div>
    </form>

    <div class="apr-notif-table-card overflow-hidden" id="listaHistorialNotificaciones">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 apr-notif-table">
                <thead>
                    <tr>
                        <th class="ps-3">Módulo</th>
                        <th>Notificación</th>
                        <th>Fecha generación</th>
                        <th>Fecha lectura</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
        @forelse($notificaciones as $notificacion)
            <tr class="apr-notif-row {{ !$notificacion->leida ? 'unread' : '' }}" id="historial-notif-{{ $notificacion->id_notificacion }}" data-leida="{{ $notificacion->leida ? '1' : '0' }}">
                <td class="ps-3"><span class="apr-notif-module"><i class="fas fa-folder"></i>{{ $notificacion->modulo_mostrado }}</span></td>
                <td>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="apr-notif-title">{{ $notificacion->titulo }}</span>
                        @if($notificacion->cantidad > 1)<span class="badge rounded-pill text-bg-primary-subtle text-primary border">x{{ $notificacion->cantidad }}</span>@endif
                    </div>
                    @if($notificacion->mensaje)<div class="apr-notif-message">{{ $notificacion->mensaje }}</div>@endif
                </td>
                <td>
                    <div class="apr-notif-date"><i class="far fa-calendar-alt me-1"></i>{{ $notificacion->fecha_generacion?->copy()->timezone('America/Bogota')->format('d/m/Y') }}</div>
                    <div class="apr-notif-date-detail"><i class="far fa-clock me-1"></i>{{ $notificacion->fecha_generacion?->copy()->timezone('America/Bogota')->format('g:i a') }} · {{ $notificacion->fecha_generacion?->copy()->locale('es')->diffForHumans() }}</div>
                </td>
                <td>
                    <div class="apr-notif-date-read" id="fechaLectura{{ $notificacion->id_notificacion }}">
                        @if($notificacion->fecha_lectura)
                            <span class="apr-notif-date-detail"><i class="fas fa-check text-success me-1"></i>{{ $notificacion->fecha_lectura->copy()->timezone('America/Bogota')->format('d/m/Y g:i a') }}</span>
                        @else
                            <span class="apr-notif-date-detail fst-italic">Sin leer aún</span>
                        @endif
                    </div>
                </td>
                <td class="text-center"><span class="apr-notif-status {{ $notificacion->leida ? 'is-read' : 'is-unread' }} js-estado-notif">{{ $notificacion->leida ? 'Leída' : 'No leída' }}</span></td>
                <td class="pe-3">
                    <div class="apr-notif-actions">
                        <a href="{{ route('controlecp.notificaciones.ir', $notificacion->id_notificacion) }}" class="apr-notif-action apr-notif-action--open" title="Abrir notificación" aria-label="Abrir notificación"><i class="fas fa-arrow-up-right-from-square"></i></a>
                        <button class="apr-notif-action apr-notif-action--toggle js-toggle-notificacion" type="button" data-url="{{ route('controlecp.notificaciones.toggle', $notificacion->id_notificacion) }}" data-leida="{{ $notificacion->leida ? '1' : '0' }}" title="{{ $notificacion->leida ? 'Marcar no leída' : 'Marcar leída' }}" aria-label="{{ $notificacion->leida ? 'Marcar no leída' : 'Marcar leída' }}"><i class="fas {{ $notificacion->leida ? 'fa-envelope' : 'fa-envelope-open' }}"></i></button>
                        <button class="apr-notif-action apr-notif-action--delete js-delete-notificacion" type="button" data-url="{{ route('controlecp.notificaciones.destroy', $notificacion->id_notificacion) }}" title="Eliminar notificación" aria-label="Eliminar notificación"><i class="fas fa-trash-can"></i></button>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="apr-notif-empty text-center py-5"><i class="fas fa-bell-slash fs-2 mb-3 d-block opacity-50"></i><strong>No tienes notificaciones</strong><div class="small mt-1">Los avisos dirigidos a ti aparecerán aquí.</div></td></tr>
        @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($notificaciones->hasPages())
        <nav class="apr-notif-pagination mt-3" aria-label="Paginación de notificaciones">
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
@endsection

@push('scripts')
<script>
(() => {
    const root = document.getElementById('aprendizNotificaciones');
    if (!root) return;

    const errorBox = document.getElementById('errorNotificaciones');
    const csrf = @json(csrf_token());
    const countTotal = document.getElementById('totalNotificaciones');
    const countUnread = document.getElementById('totalNoLeidas');
    const countRead = document.getElementById('totalLeidas');
    const markAllButton = document.getElementById('marcarTodasNotificaciones');

    const showError = (message) => {
        errorBox.textContent = message;
        errorBox.classList.remove('d-none');
    };

    const updateCounts = (unreadDelta, readDelta = -unreadDelta, totalDelta = 0) => {
        const unread = Math.max(0, Number(countUnread.textContent) + unreadDelta);
        const read = Math.max(0, Number(countRead.textContent) + readDelta);
        countTotal.textContent = Math.max(0, Number(countTotal.textContent) + totalDelta);
        countUnread.textContent = unread;
        countRead.textContent = read;
        markAllButton.disabled = unread === 0;
        const badge = document.getElementById('badgeContadorCampana');
        badge?.replaceChildren(document.createTextNode(unread > 99 ? '99+' : String(unread)));
        badge?.classList.toggle('d-none', unread === 0);
    };

    const updateRowReadState = (row, button, isRead, readAt) => {
        button.dataset.leida = isRead ? '1' : '0';
        button.title = isRead ? 'Marcar no leída' : 'Marcar leída';
        button.setAttribute('aria-label', button.title);
        button.innerHTML = `<i class="fas ${isRead ? 'fa-envelope' : 'fa-envelope-open'}"></i>`;
        row.dataset.leida = isRead ? '1' : '0';
        row.classList.toggle('unread', !isRead);
        const status = row.querySelector('.js-estado-notif');
        status.textContent = isRead ? 'Leída' : 'No leída';
        status.className = `apr-notif-status ${isRead ? 'is-read' : 'is-unread'} js-estado-notif`;
        const readDate = row.querySelector('.apr-notif-date-read');
        const dateLabel = document.createElement('span');
        dateLabel.className = `apr-notif-date-detail${readAt ? '' : ' fst-italic'}`;
        if (readAt) {
            const check = document.createElement('i');
            check.className = 'fas fa-check text-success me-1';
            dateLabel.appendChild(check);
        }
        dateLabel.appendChild(document.createTextNode(readAt || 'Sin leer aún'));
        readDate.replaceChildren(dateLabel);
    };

    root.querySelectorAll('.js-toggle-notificacion').forEach(button => {
        button.addEventListener('click', async () => {
            errorBox.classList.add('d-none');
            const wasRead = button.dataset.leida === '1';
            try {
                const response = await fetch(button.dataset.url, {
                    method: 'PATCH',
                    headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'}
                });
                const result = await response.json();
                if (!response.ok || !result.success) throw new Error(result.message || 'No se pudo actualizar la notificación.');

                const row = button.closest('tr');
                updateRowReadState(row, button, result.leida, result.fecha_lectura);
                if (wasRead !== result.leida) updateCounts(result.leida ? -1 : 1);
            } catch (error) {
                showError(error.message);
            }
        });
    });

    markAllButton?.addEventListener('click', async () => {
        errorBox.classList.add('d-none');
        try {
            const response = await fetch(@json(route('controlecp.notificaciones.marcar-todas')), {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'}
            });
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'No se pudieron marcar las notificaciones.');

            const changed = Number(countUnread.textContent);
            countUnread.textContent = '0';
            countRead.textContent = Number(countRead.textContent) + changed;
            markAllButton.disabled = true;
            document.getElementById('badgeContadorCampana')?.classList.add('d-none');
            const readAt = new Intl.DateTimeFormat('es-CO', {
                timeZone: 'America/Bogota',
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
            }).format(new Date());
            root.querySelectorAll('.js-toggle-notificacion[data-leida="0"]').forEach(button => {
                updateRowReadState(button.closest('tr'), button, true, readAt);
            });
        } catch (error) {
            showError(error.message);
        }
    });

    root.querySelectorAll('.js-delete-notificacion').forEach(button => {
        button.addEventListener('click', async () => {
            if (!window.confirm('¿Quieres eliminar esta notificación?')) return;
            errorBox.classList.add('d-none');
            try {
                const response = await fetch(button.dataset.url, {
                    method: 'DELETE',
                    headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'}
                });
                const result = await response.json();
                if (!response.ok || !result.success) throw new Error(result.message || 'No se pudo eliminar la notificación.');
                const row = button.closest('tr');
                const wasRead = row.dataset.leida === '1';
                row.remove();
                updateCounts(wasRead ? 0 : -1, wasRead ? -1 : 0, -1);
                const tableBody = root.querySelector('.apr-notif-table tbody');
                if (!tableBody.querySelector('.apr-notif-row')) {
                    tableBody.innerHTML = '<tr><td colspan="6" class="apr-notif-empty text-center py-5"><i class="fas fa-bell-slash fs-2 mb-3 d-block opacity-50"></i><strong>No tienes notificaciones</strong><div class="small mt-1">Los avisos dirigidos a ti aparecerán aquí.</div></td></tr>';
                }
            } catch (error) {
                showError(error.message);
            }
        });
    });
})();
</script>
@endpush