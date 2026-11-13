@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .apr-asesoria-rating-header { background:#001A29; border-radius:20px; padding:1.25rem 1.5rem; }
    .apr-asesoria-rating-header h1 { color:#fff; }
    .apr-asesoria-rating-header p { color:#c7d4da; }
    .apr-asesoria-rating-kpi { min-height:132px; padding:1.1rem 1.25rem; border:1px solid; border-radius:16px; background:#fff; box-shadow:0 2px 10px rgba(0,26,41,.05); transition:transform .18s ease, box-shadow .18s ease; }
    .apr-asesoria-rating-kpi:hover { transform:translateY(-2px); box-shadow:0 8px 22px rgba(0,26,41,.1); }
    .apr-asesoria-rating-kpi-label { color:#475569; font-size:.9rem; font-weight:600; }
    .apr-asesoria-rating-kpi-value { margin-top:.4rem; font-size:2rem; font-weight:800; line-height:1; }
    .apr-asesoria-rating-kpi-icon { display:flex; width:42px; height:42px; align-items:center; justify-content:center; border-radius:50%; font-size:1rem; }
    .apr-asesoria-rating-filter { display:grid; grid-template-columns:minmax(180px,1fr) minmax(160px,.55fr) auto auto; align-items:end; gap:.75rem; padding:1rem; border-radius:16px; background:#001A29; }
    .apr-asesoria-rating-filter label { display:block; margin-bottom:.3rem; color:#fff; font-size:.76rem; font-weight:700; }
    .apr-asesoria-rating-filter .form-control, .apr-asesoria-rating-filter .form-select { min-height:42px; border-radius:9px; }
    .apr-asesoria-rating-filter .btn-search { min-height:42px; border:1px solid #39A900; border-radius:9px; background:#39A900; color:#fff; font-weight:700; }
    .apr-asesoria-rating-filter .btn-reset { display:grid; width:42px; min-height:42px; place-items:center; border-radius:9px; background:#fff; color:#001A29; }
    .apr-asesoria-rating-table-card { overflow:hidden; border:1px solid #e0ece4; border-radius:16px; background:#fff; box-shadow:0 2px 10px rgba(0,26,41,.05); }
    .apr-asesoria-rating-pagination { display:flex; justify-content:center; padding:1rem; border-top:1px solid #edf1f3; }
    .apr-asesoria-rating-pagination-list { display:flex; align-items:center; gap:0; margin:0; padding:0; list-style:none; }
    .apr-asesoria-rating-pagination-item { display:flex; min-width:42px; height:42px; align-items:center; justify-content:center; border:1px solid #cfe2d2; border-right:0; background:#fff; color:#39A900; font-size:.95rem; font-weight:700; text-decoration:none; }
    .apr-asesoria-rating-pagination-item:first-child { border-radius:8px 0 0 8px; }
    .apr-asesoria-rating-pagination-item:last-child { border-right:1px solid #cfe2d2; border-radius:0 8px 8px 0; }
    .apr-asesoria-rating-pagination-item:hover { background:#f0f8ed; color:#278000; }
    .apr-asesoria-rating-pagination-item.is-current { background:#39A900; color:#fff; }
    .apr-asesoria-rating-pagination-item.is-disabled { color:#9bb59b; cursor:not-allowed; }
    .apr-asesoria-rating-pagination-item.is-ellipsis { min-width:32px; border-left:0; border-right:0; }
    .apr-asesoria-rating-pagination-item:focus-visible { position:relative; z-index:1; outline:2px solid #001A29; outline-offset:2px; }
    .apr-asesoria-rating-table { min-width:850px; }
    .apr-asesoria-rating-table thead th { padding:.85rem 1rem; border:0; background:#001A29; color:#fff; font-size:.72rem; font-weight:800; letter-spacing:.03em; text-transform:uppercase; white-space:nowrap; }
    .apr-asesoria-rating-table tbody td { padding:.9rem 1rem; border-color:#edf1f3; vertical-align:middle; }
    .apr-asesoria-rating-status { display:inline-flex; align-items:center; gap:.35rem; padding:.3rem .65rem; border:1px solid transparent; border-radius:999px; font-size:.72rem; font-weight:800; white-space:nowrap; }
    .apr-asesoria-rating-status--ready { border-color:#a7e4c5; background:#d1fae5; color:#168653; }
    .apr-asesoria-rating-status--waiting { border-color:#93c5fd; background:#dbeafe; color:#1671e8; }
    .apr-asesoria-rating-status--rated { border-color:#a7e4c5; background:#d1fae5; color:#168653; }
    .apr-asesoria-rating-status--closed { border-color:#fecaca; background:#fee2e2; color:#c62828; }
    .apr-asesoria-rating-stars { display:flex; flex-direction:row-reverse; justify-content:flex-end; gap:.2rem; }
    .apr-asesoria-rating-stars input { position:absolute; width:1px; height:1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; clip-path:inset(50%); }
    .apr-asesoria-rating-stars label { color:#cbd5e1; cursor:pointer; font-size:1.8rem; line-height:1; transition:color .15s ease; }
    .apr-asesoria-rating-stars label:hover,
    .apr-asesoria-rating-stars label:hover ~ label,
    .apr-asesoria-rating-stars input:checked ~ label { color:#f59e0b; }
    .apr-asesoria-rating-stars input:focus-visible + label { outline:2px solid #001A29; outline-offset:3px; border-radius:3px; }
    .apr-asesoria-rating-display { display:inline-flex; align-items:center; gap:.12rem; color:#f59e0b; white-space:nowrap; }
    .apr-asesoria-rating-display .is-empty { color:#cbd5e1; }
    .apr-asesoria-rating-action { display:inline-flex; width:36px; height:36px; align-items:center; justify-content:center; border:1px solid #f8e3b2; border-radius:10px; background:#fff7e6; color:#e8a000; }
    .apr-asesoria-rating-modal .modal-content { overflow:hidden; border:0; border-radius:20px; box-shadow:0 24px 70px rgba(0,26,41,.24); }
    .apr-asesoria-rating-modal .modal-header { position:relative; gap:.85rem; padding:1.2rem 1.4rem; border:0; border-bottom:3px solid #39A900; color:#fff; background:linear-gradient(120deg,#001A29,#00324D); }
    .apr-asesoria-rating-modal .modal-title { color:#fff; }
    .apr-asesoria-rating-modal .modal-header p { color:#c7d4da; }
    .apr-asesoria-rating-modal .modal-header .btn-close { filter:brightness(0) invert(1); }
    .apr-asesoria-rating-modal .modal-body { padding:1.4rem; background:#f7faf8; }
    .apr-asesoria-rating-modal .form-control { border-radius:10px; }
    @media (max-width:767px) {
        .apr-asesoria-rating-filter { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .apr-asesoria-rating-filter .btn-search { width:100%; }
        .apr-asesoria-rating-filter .filter-reset-wrap { justify-self:start; }
    }
    @media (max-width:480px) {
        .apr-asesoria-rating-filter { grid-template-columns:minmax(0,1fr); }
        .apr-asesoria-rating-filter .filter-reset-wrap { justify-self:stretch; }
        .apr-asesoria-rating-filter .btn-reset { width:100%; }
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-3 py-3">
    <div class="apr-asesoria-rating-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small fw-semibold mb-1" style="color:#62E31D">Control ECP · Aprendiz</div>
            <h1 class="h3 fw-bold mb-1"><i class="fas fa-star-half-stroke me-2" style="color:#62E31D"></i>Calificación de asesorías</h1>
            <p class="mb-0 small">Califica con estrellas las asesorías realizadas; las demás estarán disponibles al finalizar.</p>
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
            <div class="apr-asesoria-rating-kpi h-100" style="border-color:#f8e3b2">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="apr-asesoria-rating-kpi-label">Pendientes por calificar</span>
                    <span class="apr-asesoria-rating-kpi-icon" style="background:#fff7e6;color:#c47a00"><i class="fas fa-star"></i></span>
                </div>
                <div class="apr-asesoria-rating-kpi-value text-warning">{{ $totales['pendientes'] }}</div>
                <div class="small text-muted mt-1">Asesorías realizadas sin calificación</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="apr-asesoria-rating-kpi h-100" style="border-color:#a7e4c5">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="apr-asesoria-rating-kpi-label">Asesorías calificadas</span>
                    <span class="apr-asesoria-rating-kpi-icon" style="background:#d1fae5;color:#168653"><i class="fas fa-check"></i></span>
                </div>
                <div class="apr-asesoria-rating-kpi-value text-success">{{ $totales['calificadas'] }}</div>
                <div class="small text-muted mt-1">Opiniones enviadas</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="apr-asesoria-rating-kpi h-100" style="border-color:#93c5fd">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="apr-asesoria-rating-kpi-label">Esperando finalización</span>
                    <span class="apr-asesoria-rating-kpi-icon" style="background:#dbeafe;color:#1671e8"><i class="fas fa-clock"></i></span>
                </div>
                <div class="apr-asesoria-rating-kpi-value" style="color:#1671e8">{{ $totales['por_finalizar'] }}</div>
                <div class="small text-muted mt-1">Se podrán calificar al terminar</div>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('controlecp.aprendiz.calificacion_asesoria') }}" class="apr-asesoria-rating-filter mb-4">
        <div>
            <label for="buscarCalificacionAsesoria"><i class="fas fa-search me-1"></i>Buscar asesoría</label>
            <input class="form-control" id="buscarCalificacionAsesoria" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Motivo, categoría o instructor">
        </div>
        <div>
            <label for="estadoCalificacionAsesoria">Estado</label>
            <select class="form-select" id="estadoCalificacionAsesoria" name="estado">
                <option value="">Todos</option>
                @foreach(['Programada', 'Realizada'] as $estado)
                    <option value="{{ $estado }}" @selected(($filters['estado'] ?? '') === $estado)>{{ $estado }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-search" type="submit"><i class="fas fa-magnifying-glass me-1"></i>Buscar</button>
        <div class="filter-reset-wrap">
            <a class="btn btn-reset" href="{{ route('controlecp.aprendiz.calificacion_asesoria') }}" title="Limpiar filtros" aria-label="Limpiar filtros"><i class="fas fa-rotate-left"></i></a>
        </div>
    </form>

    <section class="apr-asesoria-rating-table-card">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 border-bottom">
            <h2 class="h5 fw-bold mb-0">Mis asesorías</h2>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 apr-asesoria-rating-table">
                <thead>
                    <tr>
                        <th>Fecha y hora</th>
                        <th>Categoría y motivo</th>
                        <th>Instructor</th>
                        <th>Estado</th>
                        <th>Calificación</th>
                        <th class="text-end">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($asesorias as $asesoria)
                        @php
                            $calificacion = $asesoria->calificacion;
                            $puedeCalificar = $asesoria->estado === 'Realizada' && !$calificacion;
                            $esperaFinalizacion = $asesoria->estado === 'Programada';
                        @endphp
                        <tr>
                            <td class="text-nowrap">
                                <span class="fw-semibold">{{ $asesoria->fecha?->format('d/m/Y') ?? '—' }}</span>
                                <div class="small text-muted">{{ \Illuminate\Support\Carbon::parse($asesoria->hora_inicio)->format('g:i a') }}–{{ \Illuminate\Support\Carbon::parse($asesoria->hora_fin)->format('g:i a') }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $asesoria->categoria?->nombre ?? 'Asesoría' }}</div>
                                <div class="small text-muted">{{ \Illuminate\Support\Str::limit($asesoria->motivo, 70) }}</div>
                            </td>
                            <td>{{ $asesoria->instructor?->person?->full_name ?? $asesoria->instructor?->nickname ?? 'Pendiente de asignación' }}</td>
                            <td>
                                <span class="apr-asesoria-rating-status @if($calificacion) apr-asesoria-rating-status--rated @elseif($asesoria->estado === 'Realizada') apr-asesoria-rating-status--ready @elseif($esperaFinalizacion) apr-asesoria-rating-status--waiting @else apr-asesoria-rating-status--closed @endif">
                                    @if($calificacion)<i class="fas fa-check-circle" aria-hidden="true"></i>Calificada
                                    @elseif($asesoria->estado === 'Realizada')<i class="fas fa-star" aria-hidden="true"></i>Por calificar
                                    @elseif($esperaFinalizacion)<i class="fas fa-clock" aria-hidden="true"></i>{{ $asesoria->estado }}
                                    @else<i class="fas fa-ban" aria-hidden="true"></i>{{ $asesoria->estado }}
                                    @endif
                                </span>
                            </td>
                            <td>
                                @if($calificacion)
                                    <span class="apr-asesoria-rating-display" aria-label="{{ $calificacion->estrellas }} de 5 estrellas">
                                        @for($estrella = 1; $estrella <= 5; $estrella++)
                                            <i class="fas fa-star {{ $estrella <= $calificacion->estrellas ? '' : 'is-empty' }}" aria-hidden="true"></i>
                                        @endfor
                                    </span>
                                    <div class="small text-muted">{{ $calificacion->estrellas }}/5</div>
                                @elseif($puedeCalificar)
                                    <span class="small text-warning fw-semibold">Pendiente</span>
                                @else
                                    <span class="small text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button class="apr-asesoria-rating-action" type="button" data-bs-toggle="modal" data-bs-target="#calificarAsesoria{{ $asesoria->id_asesoria }}"
                                        title="{{ $calificacion ? 'Ver calificación' : ($puedeCalificar ? 'Calificar asesoría' : 'Calificación pendiente') }}"
                                        aria-label="{{ $calificacion ? 'Ver calificación' : ($puedeCalificar ? 'Calificar asesoría' : 'Calificación pendiente') }}">
                                    <i class="fas fa-star" aria-hidden="true"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-5 text-center text-muted">No tienes asesorías que coincidan con la búsqueda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($asesorias->hasPages())
            <nav class="apr-asesoria-rating-pagination" aria-label="Paginación de asesorías">
                <ul class="apr-asesoria-rating-pagination-list">
                    @if($asesorias->onFirstPage())
                        <li><span class="apr-asesoria-rating-pagination-item is-disabled" aria-hidden="true">&lsaquo;</span></li>
                    @else
                        <li><a class="apr-asesoria-rating-pagination-item" href="{{ $asesorias->previousPageUrl() }}" aria-label="Página anterior">&lsaquo;</a></li>
                    @endif

                    @if($asesorias->currentPage() > 2)
                        <li><a class="apr-asesoria-rating-pagination-item" href="{{ $asesorias->url(1) }}">1</a></li>
                        @if($asesorias->currentPage() > 3)
                            <li><span class="apr-asesoria-rating-pagination-item is-ellipsis" aria-hidden="true">…</span></li>
                        @endif
                    @endif

                    @foreach($asesorias->getUrlRange(max(1, $asesorias->currentPage() - 1), min($asesorias->lastPage(), $asesorias->currentPage() + 1)) as $pagina => $url)
                        @if((int) $pagina === (int) $asesorias->currentPage())
                            <li><span class="apr-asesoria-rating-pagination-item is-current" aria-current="page">{{ $pagina }}</span></li>
                        @else
                            <li><a class="apr-asesoria-rating-pagination-item" href="{{ $url }}" aria-label="Página {{ $pagina }}">{{ $pagina }}</a></li>
                        @endif
                    @endforeach

                    @if($asesorias->currentPage() < $asesorias->lastPage() - 1)
                        @if($asesorias->currentPage() < $asesorias->lastPage() - 2)
                            <li><span class="apr-asesoria-rating-pagination-item is-ellipsis" aria-hidden="true">…</span></li>
                        @endif
                        <li><a class="apr-asesoria-rating-pagination-item" href="{{ $asesorias->url($asesorias->lastPage()) }}">{{ $asesorias->lastPage() }}</a></li>
                    @endif

                    @if($asesorias->hasMorePages())
                        <li><a class="apr-asesoria-rating-pagination-item" href="{{ $asesorias->nextPageUrl() }}" aria-label="Página siguiente">&rsaquo;</a></li>
                    @else
                        <li><span class="apr-asesoria-rating-pagination-item is-disabled" aria-hidden="true">&rsaquo;</span></li>
                    @endif
                </ul>
            </nav>
        @endif
    </section>

    @foreach($asesorias as $asesoria)
        @php
            $calificacion = $asesoria->calificacion;
            $puedeCalificar = $asesoria->estado === 'Realizada' && !$calificacion;
            $instructorNombre = $asesoria->instructor?->person?->full_name ?? $asesoria->instructor?->nickname ?? 'Pendiente de asignación';
        @endphp
        <div class="modal fade apr-asesoria-rating-modal" id="calificarAsesoria{{ $asesoria->id_asesoria }}" tabindex="-1" aria-labelledby="calificarAsesoriaTitulo{{ $asesoria->id_asesoria }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <span class="apr-asesoria-rating-kpi-icon" style="background:rgba(245,158,11,.16);color:#fcd34d"><i class="fas fa-star"></i></span>
                        <div class="flex-grow-1">
                            <h2 class="modal-title h5 fw-bold mb-0" id="calificarAsesoriaTitulo{{ $asesoria->id_asesoria }}">Calificación de la asesoría</h2>
                            <p class="mb-0 small">{{ $asesoria->categoria?->nombre ?? 'Asesoría' }} · {{ $instructorNombre }}</p>
                        </div>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        @if($calificacion)
                            <div class="text-center">
                                <div class="apr-asesoria-rating-display justify-content-center fs-3 mb-2" role="img" aria-label="{{ $calificacion->estrellas }} de 5 estrellas">
                                    @for($estrella = 1; $estrella <= 5; $estrella++)
                                        <i class="fas fa-star {{ $estrella <= $calificacion->estrellas ? '' : 'is-empty' }}" aria-hidden="true"></i>
                                    @endfor
                                </div>
                                <div class="fw-bold">{{ $calificacion->estrellas }} de 5 estrellas</div>
                                <div class="small text-muted">Tu calificación · {{ $calificacion->created_at?->copy()->timezone('America/Bogota')->format('d/m/Y g:i a') ?? '—' }}</div>
                                <hr>
                                <h3 class="h6 fw-bold">Tu comentario</h3>
                                <p class="text-muted mb-0">{{ $calificacion->comentario ?: 'No agregaste un comentario.' }}</p>
                            </div>
                        @elseif($puedeCalificar)
                            <p class="text-muted mb-3">La asesoría terminó. Comparte cómo fue tu experiencia.</p>
                            <form method="POST" action="{{ route('controlecp.aprendiz.calificacion_asesoria.store', $asesoria->id_asesoria) }}">
                                @csrf
                                <input type="hidden" name="asesoria_id" value="{{ $asesoria->id_asesoria }}">
                                <fieldset class="mb-3">
                                    <legend class="form-label fw-semibold mb-2">Tu calificación <span class="text-danger">*</span></legend>
                                    <div class="apr-asesoria-rating-stars" role="radiogroup" aria-label="Selecciona de 1 a 5 estrellas">
                                        @for($estrella = 1; $estrella <= 5; $estrella++)
                                            <input id="asesoria-estrella-{{ $asesoria->id_asesoria }}-{{ $estrella }}" type="radio" name="estrellas" value="{{ $estrella }}" required
                                                @checked((int) old('asesoria_id') === (int) $asesoria->id_asesoria && old('estrellas') == $estrella)>
                                            <label for="asesoria-estrella-{{ $asesoria->id_asesoria }}-{{ $estrella }}" title="{{ $estrella }} {{ $estrella === 1 ? 'estrella' : 'estrellas' }}">
                                                <i class="fas fa-star" aria-hidden="true"></i>
                                                <span class="visually-hidden">{{ $estrella }} {{ $estrella === 1 ? 'estrella' : 'estrellas' }}</span>
                                            </label>
                                        @endfor
                                    </div>
                                </fieldset>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold" for="asesoria-comentario-{{ $asesoria->id_asesoria }}">Comentario <span class="text-muted fw-normal">(opcional)</span></label>
                                    <textarea class="form-control" id="asesoria-comentario-{{ $asesoria->id_asesoria }}" name="comentario" rows="3" maxlength="2000" placeholder="Comparte brevemente tu opinión">{{ old('asesoria_id') == $asesoria->id_asesoria ? old('comentario') : '' }}</textarea>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button class="btn btn-success rounded-pill px-4" type="submit"><i class="fas fa-paper-plane me-1"></i>Enviar calificación</button>
                                </div>
                            </form>
                        @elseif($asesoria->estado === 'Programada')
                            <div class="text-center py-3">
                                <div class="apr-asesoria-rating-display justify-content-center fs-3 mb-3" aria-hidden="true">
                                    @for($estrella = 1; $estrella <= 5; $estrella++)<i class="fas fa-star is-empty"></i>@endfor
                                </div>
                                <h3 class="h6 fw-bold">Calificación pendiente</h3>
                                <p class="text-muted mb-0">Podrás calificar esta asesoría cuando haya finalizado.</p>
                            </div>
                        @else
                            <div class="text-center py-3">
                                <i class="fas fa-circle-info fs-2 text-muted mb-3" aria-hidden="true"></i>
                                <h3 class="h6 fw-bold">No disponible para calificar</h3>
                                <p class="text-muted mb-0">Esta asesoría no se realizó, por lo que no se puede calificar.</p>
                            </div>
                        @endif
                    </div>
                    @if(!$puedeCalificar || $calificacion)
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
@if(old('asesoria_id'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById(@json('calificarAsesoria' . (int) old('asesoria_id')));
            if (modal) bootstrap.Modal.getOrCreateInstance(modal).show();
        });
    </script>
@endif
@endpush
