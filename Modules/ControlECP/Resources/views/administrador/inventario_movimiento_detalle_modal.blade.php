<div class="modal fade" id="modalDetalleRecursoDesdeMovimientos" tabindex="-1"
     aria-labelledby="modalDetalleRecursoDesdeMovimientosTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header text-white py-3 px-4" style="background:#001A29;border-bottom:2px solid #39A900;">
                <h2 class="modal-title h5 fw-bold" id="modalDetalleRecursoDesdeMovimientosTitulo">Detalle del recurso</h2>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <div id="detalleRecursoMovimientosCargando" class="text-center text-muted py-5">
                    <span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
                    Cargando información del recurso...
                </div>
                <div id="detalleRecursoMovimientosError" class="alert alert-danger d-none" role="alert"></div>
                <div id="detalleRecursoMovimientosContenido" class="d-none">
                    <div class="row g-4 mb-4">
                        <div class="col-12 col-md-4">
                            <img id="detalleRecursoMovimientosFoto" class="w-100 rounded-3 border bg-light"
                                 style="height:220px;object-fit:contain;" alt="">
                            <div id="detalleRecursoMovimientosSinFoto"
                                 class="w-100 rounded-3 border bg-light text-muted d-none align-items-center justify-content-center"
                                 style="height:220px;">
                                <i class="fas fa-box-open me-2"></i>Sin fotografía
                            </div>
                        </div>
                        <div class="col-12 col-md-8">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                                <div>
                                    <h3 class="h4 fw-bold mb-1" id="detalleRecursoMovimientosNombre"></h3>
                                    <div class="text-muted" id="detalleRecursoMovimientosCategoria"></div>
                                </div>
                                <span class="badge rounded-pill px-3 py-2" id="detalleRecursoMovimientosSemaforo"></span>
                            </div>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="small text-muted">Disponibles</div>
                                    <div class="fw-bold" id="detalleRecursoMovimientosDisponibles"></div>
                                </div>
                                <div class="col-6">
                                    <div class="small text-muted">Cantidad total</div>
                                    <div class="fw-bold" id="detalleRecursoMovimientosTotal"></div>
                                </div>
                                <div class="col-6">
                                    <div class="small text-muted">Estado físico</div>
                                    <span class="badge rounded-pill" id="detalleRecursoMovimientosEstadoFisico"></span>
                                </div>
                                <div class="col-6">
                                    <div class="small text-muted">Estado</div>
                                    <div class="fw-semibold" id="detalleRecursoMovimientosEstado"></div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="small text-muted mb-1">Observaciones</div>
                                <div class="rounded-3 border bg-light p-3 small" id="detalleRecursoMovimientosObservacion"></div>
                            </div>
                        </div>
                    </div>

                    <h3 class="h6 fw-bold mb-2">Entregas pendientes de devolución</h3>
                    <div class="table-responsive border rounded-3 mb-4">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th><th>Destino</th>
                                    <th class="text-center">Prestado</th><th class="text-center">Devuelto</th>
                                    <th class="text-center">Pendiente</th><th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="detalleRecursoMovimientosPendientes"></tbody>
                        </table>
                    </div>

                    <h3 class="h6 fw-bold mb-2">Historial de movimientos</h3>
                    <div class="table-responsive border rounded-3 mb-4">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th><th>Tipo</th><th class="text-center">Cantidad</th>
                                    <th>Detalle</th><th>Autor</th><th>Evidencia</th>
                                </tr>
                            </thead>
                            <tbody id="detalleRecursoMovimientosHistorial"></tbody>
                        </table>
                    </div>

                    <h3 class="h6 fw-bold mb-2">Novedades</h3>
                    <div id="detalleRecursoMovimientosNovedades"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(() => {
    const modalElement = document.getElementById('modalDetalleRecursoDesdeMovimientos');
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    const loading = document.getElementById('detalleRecursoMovimientosCargando');
    const error = document.getElementById('detalleRecursoMovimientosError');
    const content = document.getElementById('detalleRecursoMovimientosContenido');

    const addCell = (row, value, className = '') => {
        const cell = document.createElement('td');
        cell.className = className;
        cell.textContent = value ?? '—';
        row.appendChild(cell);
        return cell;
    };

    const addEmptyRow = (tbody, columns, message) => {
        const row = document.createElement('tr');
        const cell = addCell(row, message, 'text-center text-muted py-3');
        cell.colSpan = columns;
        tbody.replaceChildren(row);
    };

    const fillTable = (tbody, items, columns, fillRow, emptyMessage) => {
        tbody.replaceChildren();
        if (!items?.length) {
            addEmptyRow(tbody, columns, emptyMessage);
            return;
        }
        items.forEach(item => {
            const row = document.createElement('tr');
            fillRow(row, item);
            tbody.appendChild(row);
        });
    };

    const showDetail = data => {
        document.getElementById('modalDetalleRecursoDesdeMovimientosTitulo').textContent = `Detalle: ${data.nombre}`;
        document.getElementById('detalleRecursoMovimientosNombre').textContent = data.nombre;
        document.getElementById('detalleRecursoMovimientosCategoria').textContent = data.categoria || 'Sin categoría';
        document.getElementById('detalleRecursoMovimientosDisponibles').textContent = `${data.cantidad_disponible} unidades`;
        document.getElementById('detalleRecursoMovimientosTotal').textContent = `${data.cantidad_total} unidades`;
        document.getElementById('detalleRecursoMovimientosEstado').textContent = data.estado || '—';
        document.getElementById('detalleRecursoMovimientosObservacion').textContent = data.observaciones || 'Sin observaciones adicionales.';

        const semaforo = document.getElementById('detalleRecursoMovimientosSemaforo');
        semaforo.textContent = data.semaforo_badge?.label || data.semaforo || '—';
        semaforo.style.backgroundColor = data.semaforo_badge?.bg || '#f1f5f9';
        semaforo.style.color = data.semaforo_badge?.text || '#475569';

        const estadoFisico = document.getElementById('detalleRecursoMovimientosEstadoFisico');
        estadoFisico.textContent = data.estado_fisico_badge?.label || data.estado_fisico || '—';
        estadoFisico.style.backgroundColor = data.estado_fisico_badge?.bg || '#f1f5f9';
        estadoFisico.style.color = data.estado_fisico_badge?.text || '#475569';

        const foto = document.getElementById('detalleRecursoMovimientosFoto');
        const sinFoto = document.getElementById('detalleRecursoMovimientosSinFoto');
        if (data.fotos?.length) {
            foto.src = data.fotos[0].ruta;
            foto.alt = `Foto de ${data.nombre}`;
            foto.classList.remove('d-none');
            sinFoto.classList.add('d-none');
            sinFoto.classList.remove('d-flex');
        } else {
            foto.removeAttribute('src');
            foto.classList.add('d-none');
            sinFoto.classList.remove('d-none');
            sinFoto.classList.add('d-flex');
        }

        fillTable(
            document.getElementById('detalleRecursoMovimientosPendientes'),
            data.movimientos_pendientes,
            6,
            (row, item) => {
                addCell(row, item.fecha);
                addCell(row, item.destino || item.observacion);
                addCell(row, `${item.cantidad_prestada} un.`, 'text-center');
                addCell(row, `${item.cantidad_devuelta} un.`, 'text-center text-success');
                addCell(row, `${item.saldo_pendiente} un.`, 'text-center fw-bold text-danger');
                addCell(row, item.estado);
            },
            'No hay entregas pendientes de devolución.'
        );

        fillTable(
            document.getElementById('detalleRecursoMovimientosHistorial'),
            data.movimientos_devueltos,
            6,
            (row, item) => {
                addCell(row, item.fecha);
                addCell(row, item.tipo);
                addCell(row, `${item.cantidad} un.`, 'text-center');
                addCell(row, item.observacion);
                addCell(row, item.autor);
                const evidenceCell = document.createElement('td');
                if (item.foto) {
                    const link = document.createElement('a');
                    link.href = item.foto;
                    link.target = '_blank';
                    link.rel = 'noopener';
                    link.className = 'btn inv-movement-action';
                    link.title = 'Ver fotografía adjunta';
                    link.setAttribute('aria-label', 'Ver fotografía adjunta');
                    link.innerHTML = '<i class="fas fa-camera" aria-hidden="true"></i>';
                    evidenceCell.appendChild(link);
                } else {
                    evidenceCell.textContent = '—';
                }
                row.appendChild(evidenceCell);
            },
            'No hay movimientos registrados.'
        );

        const novedades = document.getElementById('detalleRecursoMovimientosNovedades');
        novedades.replaceChildren();
        if (!data.novedades?.length) {
            const empty = document.createElement('div');
            empty.className = 'text-center text-muted py-3';
            empty.textContent = 'No hay novedades registradas.';
            novedades.appendChild(empty);
        } else {
            data.novedades.forEach(item => {
                const article = document.createElement('article');
                article.className = 'p-3 mb-2 rounded-3 border-start border-4 border-warning bg-light';
                const header = document.createElement('div');
                header.className = 'd-flex justify-content-between gap-2';
                const author = document.createElement('strong');
                author.textContent = item.autor;
                const date = document.createElement('span');
                date.className = 'small text-muted';
                date.textContent = item.fecha;
                const description = document.createElement('div');
                description.className = 'small mt-2';
                description.textContent = item.descripcion;
                header.append(author, date);
                article.append(header, description);
                novedades.appendChild(article);
            });
        }
        content.classList.remove('d-none');
    };

    document.addEventListener('click', event => {
        const button = event.target.closest('[data-inventario-detalle-url]');
        if (!button) return;

        loading.classList.remove('d-none');
        error.classList.add('d-none');
        content.classList.add('d-none');
        modal.show();

        fetch(button.dataset.inventarioDetalleUrl, {
            headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}
        })
            .then(response => {
                if (!response.ok) throw new Error(`No se pudo cargar el detalle (HTTP ${response.status}).`);
                return response.json();
            })
            .then(showDetail)
            .catch(exception => {
                error.textContent = exception.message || 'Ocurrió un error al cargar el detalle.';
                error.classList.remove('d-none');
            })
            .finally(() => loading.classList.add('d-none'));
    });
})();
</script>
@endpush
