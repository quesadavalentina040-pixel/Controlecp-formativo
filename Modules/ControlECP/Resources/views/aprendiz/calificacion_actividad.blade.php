@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .apr-calif-header { background:#001A29; border-radius:20px; padding:1.25rem 1.5rem; }
    .apr-calif-header h1 { color:#fff; }
    .apr-calif-header p { color:#c7d4da; }
    .apr-calif-kpi { min-height:125px; padding:1.1rem 1.25rem; border:1px solid; border-radius:16px; background:#fff; box-shadow:0 2px 10px rgba(0,26,41,.05); transition:transform .18s ease, box-shadow .18s ease; }
    .apr-calif-kpi:hover { transform:translateY(-2px); box-shadow:0 8px 22px rgba(0,26,41,.1); }
    .apr-calif-kpi-label { color:#475569; font-size:.9rem; font-weight:600; }
    .apr-calif-kpi-value { margin-top:.4rem; font-size:2rem; font-weight:800; line-height:1; }
    .apr-calif-kpi-icon { display:flex; width:42px; height:42px; align-items:center; justify-content:center; border-radius:50%; font-size:1rem; }
    .apr-calif-filter { display:grid; grid-template-columns:minmax(180px,1fr) minmax(160px,.55fr) auto auto; align-items:end; gap:.75rem; padding:1rem; border-radius:16px; background:#001A29; }
    .apr-calif-filter label { display:block; margin-bottom:.3rem; color:#fff; font-size:.76rem; font-weight:700; }
    .apr-calif-filter .form-control, .apr-calif-filter .form-select { min-height:42px; border-radius:9px; }
    .apr-calif-filter .btn-search { min-height:42px; border:1px solid #39A900; border-radius:9px; background:#39A900; color:#fff; font-weight:700; }
    .apr-calif-filter .btn-reset { display:grid; width:42px; min-height:42px; place-items:center; border-radius:9px; background:#fff; color:#001A29; }
    .apr-calif-table-card { overflow:hidden; border:1px solid #e0ece4; border-radius:16px; background:#fff; box-shadow:0 2px 10px rgba(0,26,41,.05); }
    .apr-calif-table { min-width:760px; }
    .apr-calif-table thead th { padding:.85rem 1rem; border:0; background:#001A29; color:#fff; font-size:.72rem; font-weight:800; letter-spacing:.03em; text-transform:uppercase; white-space:nowrap; }
    .apr-calif-table tbody td { padding:.9rem 1rem; border-color:#edf1f3; vertical-align:middle; }
    .apr-calif-status { display:inline-flex; align-items:center; gap:.35rem; padding:.3rem .65rem; border:1px solid transparent; border-radius:999px; font-size:.72rem; font-weight:800; white-space:nowrap; }
    .apr-calif-status--pending { border-color:#f8e3b2; background:#fff7e6; color:#c47a00; }
    .apr-calif-status--rated { border-color:#a7e4c5; background:#d1fae5; color:#168653; }
    .apr-calif-rating-display { display:inline-flex; align-items:center; gap:.12rem; color:#f59e0b; white-space:nowrap; }
    .apr-calif-rating-display .is-empty { color:#cbd5e1; }
    .apr-calif-action { display:inline-flex; width:36px; height:36px; align-items:center; justify-content:center; border:1px solid #f8e3b2; border-radius:10px; background:#fff7e6; color:#e8a000; transition:background .18s ease, color .18s ease, transform .18s ease; }
    .apr-calif-action:hover { transform:translateY(-1px); background:#f59e0b; color:#fff; }
    .apr-calif-pagination { display:flex; justify-content:center; padding:1rem; border-top:1px solid #edf1f3; }
    .apr-calif-pagination-list { display:flex; align-items:center; gap:0; margin:0; padding:0; list-style:none; }
    .apr-calif-pagination-item { display:flex; min-width:42px; height:42px; align-items:center; justify-content:center; border:1px solid #cfe2d2; border-right:0; background:#fff; color:#39A900; font-size:.95rem; font-weight:700; text-decoration:none; }
    .apr-calif-pagination-item:first-child { border-radius:8px 0 0 8px; }
    .apr-calif-pagination-item:last-child { border-right:1px solid #cfe2d2; border-radius:0 8px 8px 0; }
    .apr-calif-pagination-item:hover { background:#f0f8ed; color:#278000; }
    .apr-calif-pagination-item.is-current { background:#39A900; color:#fff; }
    .apr-calif-pagination-item.is-disabled { color:#9bb59b; cursor:not-allowed; }
    .apr-calif-pagination-item.is-ellipsis { min-width:32px; border-left:0; border-right:0; }
    .apr-calif-modal .modal-content { overflow:hidden; border:0; border-radius:20px; box-shadow:0 24px 70px rgba(0,26,41,.24); }
    .apr-calif-modal .modal-header { gap:.85rem; padding:1.2rem 1.4rem; border:0; border-bottom:3px solid #39A900; color:#fff; background:linear-gradient(120deg,#001A29,#00324D); }
    .apr-calif-modal .modal-title { color:#fff; }
    .apr-calif-modal .modal-header p { color:#c7d4da; }
    .apr-calif-modal .modal-header .btn-close { filter:brightness(0) invert(1); }
    .apr-calif-modal .modal-body { padding:1.4rem; background:#f7faf8; }
    .apr-calif-modal .form-control { border-radius:10px; }
    .apr-calif-stars { display:flex; flex-direction:row-reverse; justify-content:flex-end; gap:.2rem; }
    .apr-calif-stars input { position:absolute; width:1px; height:1px; overflow:hidden; clip:rect(0,0,0,0); clip-path:inset(50%); white-space:nowrap; }
    .apr-calif-stars label { color:#cbd5e1; cursor:pointer; font-size:1.8rem; line-height:1; transition:color .15s ease; }
    .apr-calif-stars label:hover,
    .apr-calif-stars label:hover ~ label,
    .apr-calif-stars input:checked ~ label { color:#f59e0b; }
    .apr-calif-stars input:focus-visible + label { outline:2px solid #001A29; outline-offset:3px; border-radius:3px; }
    @media (max-width:767px) {
        .apr-calif-filter { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .apr-calif-filter .btn-search { width:100%; }
        .apr-calif-filter .filter-reset-wrap { justify-self:start; }
    }
    @media (max-width:480px) {
        .apr-calif-filter { grid-template-columns:minmax(0,1fr); }
        .apr-calif-filter .filter-reset-wrap { justify-self:stretch; }
        .apr-calif-filter .btn-reset { width:100%; }
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-3 py-3">
    <div class="apr-calif-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small fw-semibold mb-1" style="color:#62E31D">Control ECP · Aprendiz</div>
            <h1 class="h3 fw-bold mb-1"><i class="fas fa-star me-2" style="color:#62E31D"></i>Calificación de actividad</h1>
            <p class="mb-0 small">Valora las actividades finalizadas a las que asististe.</p>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('info'))<div class="alert alert-info">{{ session('info') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="apr-calif-kpi h-100" style="border-color:#cfe2d2">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="apr-calif-kpi-label">Actividades en las que participaste</span>
                    <span class="apr-calif-kpi-icon" style="background:#edf8e8;color:#39A900"><i class="fas fa-list-check"></i></span>
                </div>
                <div class="apr-calif-kpi-value" style="color:#287a13">{{ $totales['total'] }}</div>
                <div class="small text-muted mt-1">Actividades finalizadas y asistidas</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="apr-calif-kpi h-100" style="border-color:#f8e3b2">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="apr-calif-kpi-label">Pendientes por calificar</span>
                    <span class="apr-calif-kpi-icon" style="background:#fff7e6;color:#c47a00"><i class="fas fa-star"></i></span>
                </div>
                <div class="apr-calif-kpi-value text-warning">{{ $totales['pendientes'] }}</div>
                <div class="small text-muted mt-1">Comparte tu experiencia</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="apr-calif-kpi h-100" style="border-color:#a7e4c5">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="apr-calif-kpi-label">Actividades calificadas</span>
                    <span class="apr-calif-kpi-icon" style="background:#d1fae5;color:#168653"><i class="fas fa-check"></i></span>
                </div>
                <div class="apr-calif-kpi-value text-success">{{ $totales['calificadas'] }}</div>
                <div class="small text-muted mt-1">Opiniones enviadas</div>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('controlecp.aprendiz.calificacion_actividad') }}" class="apr-calif-filter mb-4">
        <div>
            <label for="buscarCalificacionActividad"><i class="fas fa-search me-1"></i>Buscar actividad</label>
            <input class="form-control" id="buscarCalificacionActividad" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nombre, número o momento">
        </div>
        <div>
            <label for="estadoCalificacionActividad">Estado</label>
            <select class="form-select" id="estadoCalificacionActividad" name="estado">
                <option value="">Todos</option>
                <option value="pendiente" @selected(($filters['estado'] ?? '') === 'pendiente')>Pendiente</option>
                <option value="calificada" @selected(($filters['estado'] ?? '') === 'calificada')>Calificada</option>
            </select>
        </div>
        <button class="btn btn-search" type="submit"><i class="fas fa-magnifying-glass me-1"></i>Buscar</button>
        <div class="filter-reset-wrap">
            <a class="btn btn-reset" href="{{ route('controlecp.aprendiz.calificacion_actividad') }}" title="Limpiar filtros" aria-label="Limpiar filtros"><i class="fas fa-rotate-left"></i></a>
        </div>
    </form>

    <section class="apr-calif-table-card">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 border-bottom">
            <h2 class="h5 fw-bold mb-0">Actividades en las que participé</h2>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 apr-calif-table">
                <thead>
                    <tr>
                        <th>Fecha y hora</th>
                        <th>Actividad</th>
                        <th>Momento</th>
                        <th>Estado</th>
                        <th>Calificación</th>
                        <th class="text-end">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cronogramas as $cronograma)
                        @php
                            $calificacion = $cronograma->calificacionAprendiz;
                            $numeroActividad = $cronograma->actividad?->numero
                                ? 'Actividad ' . $cronograma->actividad->numero
                                : 'Actividad ' . ($cronograma->actividad?->id ?? '');
                        @endphp
                        <tr>
                            <td class="text-nowrap">
                                <span class="fw-semibold">{{ $cronograma->fecha?->format('d/m/Y') ?? '—' }}</span>
                                <div class="small text-muted">@if($cronograma->hora_inicio){{ \Illuminate\Support\Carbon::parse($cronograma->hora_inicio)->format('g:i a') }}@else—@endif</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $numeroActividad }}</div>
                                <div class="small text-muted">{{ $cronograma->actividad?->nombre ?? 'Sin nombre' }}</div>
                            </td>
                            <td>{{ $cronograma->actividad?->momento?->nombre ?? 'Sin momento' }}</td>
                            <td>
                                @if($calificacion)
                                    <span class="apr-calif-status apr-calif-status--rated"><i class="fas fa-check-circle" aria-hidden="true"></i>Calificada</span>
                                @else
                                    <span class="apr-calif-status apr-calif-status--pending"><i class="fas fa-star" aria-hidden="true"></i>Por calificar</span>
                                @endif
                            </td>
                            <td>
                                @if($calificacion)
                                    <span class="apr-calif-rating-display" aria-label="{{ $calificacion->estrellas }} de 5 estrellas">
                                        @for($estrella = 1; $estrella <= 5; $estrella++)
                                            <i class="fas fa-star {{ $estrella <= $calificacion->estrellas ? '' : 'is-empty' }}" aria-hidden="true"></i>
                                        @endfor
                                    </span>
                                    <div class="small text-muted">{{ $calificacion->estrellas }}/5</div>
                                @else
                                    <span class="small text-muted">Pendiente</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button class="apr-calif-action" type="button" data-bs-toggle="modal" data-bs-target="#calificarActividad{{ $cronograma->id_cronograma }}"
                                        title="{{ $calificacion ? 'Ver calificación' : 'Calificar actividad' }}"
                                        aria-label="{{ $calificacion ? 'Ver calificación' : 'Calificar actividad' }}">
                                    <i class="fas {{ $calificacion ? 'fa-eye' : 'fa-star' }}" aria-hidden="true"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-5 text-center text-muted">
                                @if($sinFichaActiva)
                                    <i class="fas fa-id-card fs-2 d-block mb-3"></i>
                                    <span class="fw-semibold d-block">No encontramos una ficha activa asociada a tu usuario</span>
                                    <span>Solicita al administrador que revise tu inscripción para habilitar las calificaciones.</span>
                                @else
                                    <i class="fas fa-star fs-2 d-block mb-3"></i>
                                    <span class="fw-semibold d-block">No hay actividades que coincidan con la búsqueda</span>
                                    <span>Aquí aparecen las actividades finalizadas en las que estuviste presente.</span>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($cronogramas->hasPages())
            <nav class="apr-calif-pagination" aria-label="Paginación de actividades">
                <ul class="apr-calif-pagination-list">
                    @if($cronogramas->onFirstPage())
                        <li><span class="apr-calif-pagination-item is-disabled" aria-hidden="true">&lsaquo;</span></li>
                    @else
                        <li><a class="apr-calif-pagination-item" href="{{ $cronogramas->previousPageUrl() }}" aria-label="Página anterior">&lsaquo;</a></li>
                    @endif

                    @if($cronogramas->currentPage() > 2)
                        <li><a class="apr-calif-pagination-item" href="{{ $cronogramas->url(1) }}">1</a></li>
                        @if($cronogramas->currentPage() > 3)
                            <li><span class="apr-calif-pagination-item is-ellipsis" aria-hidden="true">…</span></li>
                        @endif
                    @endif

                    @foreach($cronogramas->getUrlRange(max(1, $cronogramas->currentPage() - 1), min($cronogramas->lastPage(), $cronogramas->currentPage() + 1)) as $pagina => $url)
                        @if((int) $pagina === (int) $cronogramas->currentPage())
                            <li><span class="apr-calif-pagination-item is-current" aria-current="page">{{ $pagina }}</span></li>
                        @else
                            <li><a class="apr-calif-pagination-item" href="{{ $url }}" aria-label="Página {{ $pagina }}">{{ $pagina }}</a></li>
                        @endif
                    @endforeach

                    @if($cronogramas->currentPage() < $cronogramas->lastPage() - 1)
                        @if($cronogramas->currentPage() < $cronogramas->lastPage() - 2)
                            <li><span class="apr-calif-pagination-item is-ellipsis" aria-hidden="true">…</span></li>
                        @endif
                        <li><a class="apr-calif-pagination-item" href="{{ $cronogramas->url($cronogramas->lastPage()) }}">{{ $cronogramas->lastPage() }}</a></li>
                    @endif

                    @if($cronogramas->hasMorePages())
                        <li><a class="apr-calif-pagination-item" href="{{ $cronogramas->nextPageUrl() }}" aria-label="Página siguiente">&rsaquo;</a></li>
                    @else
                        <li><span class="apr-calif-pagination-item is-disabled" aria-hidden="true">&rsaquo;</span></li>
                    @endif
                </ul>
            </nav>
        @endif
    </section>

    @foreach($cronogramas as $cronograma)
        @php
            $calificacion = $cronograma->calificacionAprendiz;
            $numeroActividad = $cronograma->actividad?->numero
                ? 'Actividad ' . $cronograma->actividad->numero
                : 'Actividad ' . ($cronograma->actividad?->id ?? '');
        @endphp
        <div class="modal fade apr-calif-modal" id="calificarActividad{{ $cronograma->id_cronograma }}" tabindex="-1" aria-labelledby="calificarActividadTitulo{{ $cronograma->id_cronograma }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <span class="apr-calif-kpi-icon" style="background:rgba(245,158,11,.16);color:#fcd34d"><i class="fas fa-star"></i></span>
                        <div class="flex-grow-1">
                            <h2 class="modal-title h5 fw-bold mb-0" id="calificarActividadTitulo{{ $cronograma->id_cronograma }}">{{ $numeroActividad }}</h2>
                            <p class="mb-0 small">{{ $cronograma->actividad?->momento?->nombre ?? 'Sin momento' }} · {{ $cronograma->fecha?->format('d/m/Y') ?? 'Sin fecha' }}</p>
                        </div>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        @if($calificacion)
                            <div class="text-center">
                                <div class="apr-calif-rating-display justify-content-center fs-3 mb-2" role="img" aria-label="{{ $calificacion->estrellas }} de 5 estrellas">
                                    @for($estrella = 1; $estrella <= 5; $estrella++)
                                        <i class="fas fa-star {{ $estrella <= $calificacion->estrellas ? '' : 'is-empty' }}" aria-hidden="true"></i>
                                    @endfor
                                </div>
                                <div class="fw-bold">{{ $calificacion->estrellas }} de 5 estrellas</div>
                                <div class="small text-muted">Tu calificación · {{ $calificacion->created_at?->format('d/m/Y') ?? '—' }}</div>
                                <hr>
                                <h3 class="h6 fw-bold">Tu comentario</h3>
                                <p class="text-muted mb-0">{{ $calificacion->comentario ?: 'No agregaste un comentario.' }}</p>
                            </div>
                        @else
                            <p class="text-muted mb-3">Comparte cómo fue tu experiencia en esta actividad.</p>
                            <form method="POST" action="{{ route('controlecp.aprendiz.calificacion_actividad.store', $cronograma->id_cronograma) }}">
                                @csrf
                                <input type="hidden" name="cronograma_id" value="{{ $cronograma->id_cronograma }}">
                                <fieldset class="mb-3">
                                    <legend class="form-label fw-semibold mb-2">Tu calificación <span class="text-danger">*</span></legend>
                                    <div class="apr-calif-stars" role="radiogroup" aria-label="Selecciona de 1 a 5 estrellas">
                                        @for($estrella = 1; $estrella <= 5; $estrella++)
                                            <input id="estrella-{{ $cronograma->id_cronograma }}-{{ $estrella }}" type="radio" name="estrellas" value="{{ $estrella }}" required
                                                @checked((int) old('cronograma_id') === (int) $cronograma->id_cronograma && old('estrellas') == $estrella)>
                                            <label for="estrella-{{ $cronograma->id_cronograma }}-{{ $estrella }}" title="{{ $estrella }} {{ $estrella === 1 ? 'estrella' : 'estrellas' }}">
                                                <i class="fas fa-star" aria-hidden="true"></i>
                                                <span class="visually-hidden">{{ $estrella }} {{ $estrella === 1 ? 'estrella' : 'estrellas' }}</span>
                                            </label>
                                        @endfor
                                    </div>
                                </fieldset>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold" for="comentario-{{ $cronograma->id_cronograma }}">Comentario <span class="text-muted fw-normal">(opcional)</span></label>
                                    <textarea class="form-control" id="comentario-{{ $cronograma->id_cronograma }}" name="comentario" rows="3" maxlength="2000" placeholder="Comparte brevemente tu opinión">{{ (int) old('cronograma_id') === (int) $cronograma->id_cronograma ? old('comentario') : '' }}</textarea>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button class="btn btn-success rounded-pill px-4" type="submit"><i class="fas fa-paper-plane me-1"></i>Enviar calificación</button>
                                </div>
                            </form>
                        @endif
                    </div>
                    @if($calificacion)
                        <div class="modal-footer">
                            <button class="btn btn-outline-secondary rounded-pill px-4" type="button" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection

@push('scripts')
@if(old('cronograma_id'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById(@json('calificarActividad' . (int) old('cronograma_id')));
            if (modal) bootstrap.Modal.getOrCreateInstance(modal).show();
        });
    </script>
@endif
@endpush