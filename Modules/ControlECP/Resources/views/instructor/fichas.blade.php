@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
<style>
    .fichas-kpi-card {
        background: #fff;
        border: 2px solid transparent;
        border-radius: 14px;
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .fichas-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 26, 41, .08);
    }
    .fichas-kpi-card .card-body {
        padding: .8rem 1rem;
    }
    .fichas-kpi-label {
        color: #475569;
        font-size: .95rem;
        font-weight: 500;
    }
    .fichas-kpi-value {
        margin-top: .15rem;
        font-size: 1.7rem;
        font-weight: 800;
        line-height: 1;
    }
    .fichas-kpi-subtitle {
        color: #64748b;
        font-size: .9rem;
    }
    .fichas-kpi-icon {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
    .fichas-filter-card {
        border: 0;
        border-radius: 18px;
        background: #001a29;
        box-shadow: 0 4px 12px rgba(0, 26, 41, .12);
    }
    .fichas-filter-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr)) auto auto;
        align-items: end;
        gap: .6rem;
    }
    .fichas-filter-grid .form-label {
        display: block;
        margin-bottom: .35rem;
        color: #fff;
        font-size: .82rem;
        font-weight: 700;
    }
    .fichas-filter-grid .form-control,
    .fichas-filter-grid .form-select {
        min-height: 44px;
        border: 1px solid #dce5eb;
        border-radius: 10px;
        background: #fff;
        color: #001a29;
        box-shadow: none;
    }
    .fichas-filter-grid .form-control:focus,
    .fichas-filter-grid .form-select:focus {
        border-color: #39a900;
        box-shadow: 0 0 0 .2rem rgba(57, 169, 0, .2);
    }
    .fichas-filter-submit,
    .fichas-filter-reset {
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-weight: 700;
        white-space: nowrap;
    }
    .fichas-filter-submit {
        min-width: 108px;
        padding: 0 1rem;
        border: 1px solid #39a900;
        background: #39a900;
        color: #fff;
    }
    .fichas-filter-submit:hover {
        border-color: #2e8800;
        background: #2e8800;
        color: #fff;
    }
    .fichas-filter-reset {
        width: 44px;
        border: 1px solid #fff;
        background: #fff;
        color: #001a29;
        text-decoration: none;
    }
    .fichas-filter-reset:hover {
        background: #edf2f4;
        color: #001a29;
    }
    .fichas-row-actions {
        display: flex;
        justify-content: flex-end;
        gap: .4rem;
        white-space: nowrap;
    }
    .fichas-row-action {
        display: inline-flex;
        width: 34px;
        height: 34px;
        align-items: center;
        justify-content: center;
        padding: 0;
        border: 1px solid transparent;
        border-radius: 10px !important;
        transition: background-color .15s ease, color .15s ease, transform .15s ease, box-shadow .15s ease;
    }
    .fichas-row-action:hover,
    .fichas-row-action:focus-visible {
        transform: translateY(-2px);
        box-shadow: 0 3px 8px rgba(0, 26, 41, .14);
    }
    .fichas-row-action--view {
        background: #eaf5ff;
        border-color: #c9e6fb;
        color: #0876bd;
    }
    .fichas-row-action--view:hover {
        background: #0876bd;
        border-color: #0876bd;
        color: #fff;
    }
    .fichas-row-action--roster {
        background: #eaf8ef;
        border-color: #ccebd6;
        color: #278344;
    }
    .fichas-row-action--roster:hover {
        background: #278344;
        border-color: #278344;
        color: #fff;
    }
    .fichas-apprentices-count {
        display: inline-flex;
        min-width: 68px;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        padding: .35rem .7rem;
        border: 1px solid #dce3e8;
        border-radius: 999px;
        background: #f8fafb;
        color: #001a29;
        font-weight: 700;
    }
    .fichas-apprentices-count i {
        color: #0876ff;
    }
    .fichas-number-badge {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .45rem .8rem;
        border: 1px solid #dce3e8;
        border-radius: 7px;
        background: #f8fafb;
        color: #1f2937;
        font-family: 'Outfit', sans-serif;
        font-size: .9rem;
        font-weight: 700;
        letter-spacing: .3px;
        white-space: nowrap;
    }
    .fichas-number-badge i {
        color: #16834f;
    }
    .fichas-status-badge {
        display: inline-flex;
        min-width: 86px;
        align-items: center;
        justify-content: center;
        gap: .35rem;
        padding: .25rem .7rem;
        border: 1px solid transparent;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .fichas-status-badge--active {
        background: #d7eee4;
        border-color: #a4d5bd;
        color: #16834f;
    }
    .fichas-status-badge--inactive {
        background: #edf0f2;
        border-color: #d9dfe3;
        color: #64748b;
    }
    @media (max-width: 575.98px) {
        .fichas-filter-grid {
            grid-template-columns: minmax(0, 1fr) auto;
        }
        .fichas-filter-search,
        .fichas-filter-program,
        .fichas-filter-status {
            grid-column: 1 / -1;
        }
    }
</style>
<div class="container-fluid px-3 py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 p-4 mb-4 text-white" style="background:linear-gradient(120deg,#001A29,#00324D);border-radius:20px">
        <div>
            <div class="small fw-bold text-uppercase mb-2" style="color:#62E31D;letter-spacing:.1em">Control ECP · Instructor</div>
            <h1 class="h3 fw-bold mb-1"><i class="fas fa-users me-2" style="color:#62E31D"></i>Mis fichas</h1>
            <p class="mb-0 text-white-50">Consulta tus fichas asignadas, sus aprendices y el avance por momento.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card fichas-kpi-card h-100" style="border-color:rgba(2,132,199,.35);">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="fichas-kpi-label">Fichas asignadas</div>
                        <span class="fichas-kpi-icon" style="background:#e0f2fe;color:#0284c7;"><i class="fas fa-users"></i></span>
                    </div>
                    <div class="fichas-kpi-value" style="color:#0284c7;">{{ $stats['fichas'] }}</div>
                    <div class="fichas-kpi-subtitle">Programadas a tu nombre</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card fichas-kpi-card h-100" style="border-color:rgba(22,163,74,.35);">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="fichas-kpi-label">Fichas activas</div>
                        <span class="fichas-kpi-icon" style="background:#dcfce7;color:#16a34a;"><i class="fas fa-circle-check"></i></span>
                    </div>
                    <div class="fichas-kpi-value" style="color:#16a34a;">{{ $stats['fichas_activas'] }}</div>
                    <div class="fichas-kpi-subtitle">Disponibles para programar</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card fichas-kpi-card h-100" style="border-color:rgba(220,38,38,.35);">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="fichas-kpi-label">Fichas inactivas</div>
                        <span class="fichas-kpi-icon" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-circle-pause"></i></span>
                    </div>
                    <div class="fichas-kpi-value" style="color:#dc2626;">{{ $stats['fichas_inactivas'] }}</div>
                    <div class="fichas-kpi-subtitle">No disponibles para programar</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card fichas-kpi-card h-100" style="border-color:rgba(2,132,199,.35);">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="fichas-kpi-label">Total de aprendices</div>
                        <span class="fichas-kpi-icon" style="background:#e0f2fe;color:#0284c7;"><i class="fas fa-user-graduate"></i></span>
                    </div>
                    <div class="fichas-kpi-value" style="color:#0284c7;">{{ $stats['aprendices'] }}</div>
                    <div class="fichas-kpi-subtitle">En tus fichas asignadas</div>
                </div>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('controlecp.instructor.fichas') }}" class="card fichas-filter-card mb-4">
        <div class="card-body p-3">
            <div class="fichas-filter-grid">
                <div class="fichas-filter-search">
                    <label class="form-label" for="buscarFicha">Buscar ficha</label>
                    <input id="buscarFicha" class="form-control" name="buscar_ficha" value="{{ request('buscar_ficha') }}" placeholder="Número de ficha">
                </div>
                <div class="fichas-filter-program">
                    <label class="form-label" for="buscarPrograma">Buscar programa</label>
                    <input id="buscarPrograma" class="form-control" name="buscar_programa" value="{{ request('buscar_programa') }}" placeholder="Nombre del programa">
                </div>
                <div class="fichas-filter-status">
                    <label class="form-label" for="estadoFicha">Estado</label>
                    <select id="estadoFicha" class="form-select" name="estado">
                        <option value="">Todos los estados</option>
                        <option value="Activo" @selected(request('estado') === 'Activo')>Activo</option>
                        <option value="Inactivo" @selected(request('estado') === 'Inactivo')>Inactivo</option>
                    </select>
                </div>
                <button class="btn fichas-filter-submit" type="submit"><i class="fas fa-search me-1"></i>Buscar</button>
                <a class="fichas-filter-reset" href="{{ route('controlecp.instructor.fichas') }}" aria-label="Limpiar filtros" title="Limpiar filtros">
                    <i class="fas fa-rotate-left"></i>
                </a>
            </div>
        </div>
    </form>

    <section class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th class="ps-4">Ficha</th><th>Programa</th><th>Modalidad</th><th>Aprendices</th><th>Estado</th><th class="text-end pe-4">Acciones</th></tr>
                </thead>
                <tbody>
                @forelse($fichas as $ficha)
                    @php
                        $fichaActiva = $ficha->status === 'Activo' || empty($ficha->status);
                    @endphp
                    <tr>
                        <td class="ps-4">
                            <span class="fichas-number-badge">
                                <i class="fas fa-id-card"></i>{{ $ficha->code ?? '—' }}
                            </span>
                        </td>
                        <td>{{ optional($ficha->program)->name ?? '—' }}</td>
                        <td>
                            <span class="small text-dark fw-semibold">
                                <i class="fas fa-location-dot text-muted me-1"></i>{{ $ficha->program ? ($ficha->program->training_type ?: 'Presencial') : 'Presencial' }}
                            </span>
                        </td>
                        <td>
                            <span class="fichas-apprentices-count">
                                <i class="fas fa-users"></i>{{ $ficha->apprentices_count }}
                            </span>
                        </td>
                        <td>
                            <span class="fichas-status-badge {{ $fichaActiva ? 'fichas-status-badge--active' : 'fichas-status-badge--inactive' }}">
                                <i class="fas {{ $fichaActiva ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                                {{ $fichaActiva ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-inline-flex gap-2">
                                <button type="button" class="btn fichas-row-action fichas-row-action--view"
                                        data-url="{{ route('controlecp.instructor.fichas.detalle', $ficha->id) }}"
                                        onclick="abrirModalFicha(this, 'modalDetalleFichaInstructor', 'detalleFichaInstructorContenido')"
                                        title="Ver ficha" aria-label="Ver ficha">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn fichas-row-action fichas-row-action--roster"
                                        data-url="{{ route('controlecp.instructor.fichas.nomina', $ficha->id) }}"
                                        onclick="abrirModalFicha(this, 'modalNominaFichaInstructor', 'nominaFichaInstructorContenido')"
                                        title="Ver nómina de aprendices" aria-label="Ver nómina de aprendices">
                                    <i class="fas fa-users"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">
                        {{ request()->filled('buscar_ficha') || request()->filled('buscar_programa') || request()->filled('estado') ? 'No se encontraron fichas para esos filtros.' : 'No tienes fichas asignadas.' }}
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($fichas->hasPages())
            <div class="card-footer bg-white border-0 px-4">
                @if ($fichas->hasPages())
                    <nav aria-label="Paginación de fichas">
                        <ul class="pagination justify-content-center">
                            @if ($fichas->onFirstPage())
                                <li class="page-item disabled"><span class="page-link" aria-hidden="true">&lsaquo;</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $fichas->previousPageUrl() }}" aria-label="Página anterior">&lsaquo;</a></li>
                            @endif
                            @php
                                $ultimaPaginaMostrada = 0;
                            @endphp
                            @for ($pagina = 1; $pagina <= $fichas->lastPage(); $pagina++)
                                @if ($pagina === 1 || $pagina === $fichas->lastPage() || abs($pagina - $fichas->currentPage()) <= 1)
                                    @if ($ultimaPaginaMostrada > 0 && $pagina > $ultimaPaginaMostrada + 1)
                                        <li class="page-item disabled"><span class="page-link">…</span></li>
                                    @endif
                                    <li class="page-item {{ $pagina === $fichas->currentPage() ? 'active' : '' }}">
                                        <a class="page-link" href="{{ $fichas->url($pagina) }}" @if($pagina === $fichas->currentPage()) aria-current="page" @endif>{{ $pagina }}</a>
                                    </li>
                                    @php
                                    $ultimaPaginaMostrada = $pagina;
                                @endphp
                                @endif
                            @endfor
                            @if ($fichas->hasMorePages())
                                <li class="page-item"><a class="page-link" href="{{ $fichas->nextPageUrl() }}" aria-label="Página siguiente">&rsaquo;</a></li>
                            @else
                                <li class="page-item disabled"><span class="page-link" aria-hidden="true">&rsaquo;</span></li>
                            @endif
                        </ul>
                    </nav>
                @endif
            </div>
        @endif
    </section>
</div>

<div class="modal fade" id="modalDetalleFichaInstructor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:18px;">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5 fw-bold"><i class="fas fa-id-card me-2 text-primary"></i>Detalle de ficha</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4" id="detalleFichaInstructorContenido"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNominaFichaInstructor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius:18px;">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5 fw-bold"><i class="fas fa-users me-2 text-success"></i>Nómina de aprendices</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4" id="nominaFichaInstructorContenido"></div>
        </div>
    </div>
</div>

<script>
    async function abrirModalFicha(button, modalId, contentId) {
        const modalElement = document.getElementById(modalId);
        const content = document.getElementById(contentId);
        const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

        content.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x text-success mb-2"></i>
                <div class="small text-muted">Cargando información...</div>
            </div>
        `;
        modal.show();

        try {
            const response = await fetch(button.dataset.url, {
                headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!response.ok) {
                throw new Error(`No fue posible cargar la información (HTTP ${response.status}).`);
            }
            content.innerHTML = await response.text();
        } catch (error) {
            content.innerHTML = `
                <div class="alert alert-danger mb-0" role="alert">
                    <i class="fas fa-triangle-exclamation me-2"></i>${error.message}
                </div>
            `;
        }
    }
</script>
@endsection
