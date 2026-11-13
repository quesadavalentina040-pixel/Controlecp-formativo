@extends('controlecp::components.layouts.dashboard')

@section('title', 'Repositorio de evidencias')

@push('styles')
<style>
    .repo-evidencias {
        --repo-verde: #39a900;
        --repo-petroleo: #001a29;
        --repo-borde: #e2e8f0;
        color: var(--repo-petroleo);
    }

    .repo-evidencias .panel {
        background: #fff;
        border: 1px solid var(--repo-borde);
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(0, 26, 41, .05);
    }

    .repo-evidencias .page-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.25rem;
        border-radius: 24px;
        background: var(--repo-petroleo);
    }
    .repo-evidencias .page-heading h1 { margin: 0 0 .35rem; color: #fff; font-size: 1.65rem; font-weight: 750; }
    .repo-evidencias .page-heading p { margin: 0; color: #64748b; }

    .repo-evidencias .summary-card {
        --summary-color: #0070b8;
        --summary-border: #a7d8f5;
        --summary-icon-bg: #eaf5ff;
        height: 100%;
        min-height: 140px;
        padding: 1rem 1.2rem;
        background: #fff;
        border: 2px solid var(--summary-border);
        border-radius: 16px;
        box-shadow: 0 3px 12px rgba(0, 26, 41, .035);
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .repo-evidencias .summary-card:hover { transform: translateY(-2px); box-shadow: 0 7px 20px rgba(0, 26, 41, .08); }
    .repo-evidencias .summary-card--activities { --summary-color: #0070b8; --summary-border: #a7d8f5; --summary-icon-bg: #eaf5ff; }
    .repo-evidencias .summary-card--attendance { --summary-color: #278000; --summary-border: #b6e4a8; --summary-icon-bg: #eff8e9; }
    .repo-evidencias .summary-card--excuses { --summary-color: #e04444; --summary-border: #ffb6b6; --summary-icon-bg: #fff0f0; }
    .repo-evidencias .summary-label { color: #64748b; font-size: .95rem; font-weight: 650; }
    .repo-evidencias .summary-value { margin-top: .55rem; color: var(--summary-color); font-size: 1.8rem; font-weight: 800; line-height: 1.1; }
    .repo-evidencias .summary-description { margin-top: .15rem; color: #64748b; font-size: .88rem; }
    .repo-evidencias .summary-icon {
        display: inline-flex;
        width: 42px;
        height: 42px;
        align-items: center;
        justify-content: center;
        color: var(--summary-color);
        background: var(--summary-icon-bg);
        border-radius: 50%;
        font-size: 1.05rem;
    }

    .repo-evidencias .filter-panel {
        padding: 1rem;
        margin-bottom: 1.25rem;
        background: #001a29;
        border: 0;
        border-radius: 18px;
        box-shadow: 0 5px 16px rgba(0, 26, 41, .12);
    }

    .repo-evidencias .filter-form-grid {
        display: grid;
        grid-template-columns: minmax(250px, 1.7fr) minmax(220px, 1.3fr) minmax(245px, 1fr);
        align-items: end;
        gap: 1rem;
    }

    .repo-evidencias .filter-form-grid > * { min-width: 0; }
    .repo-evidencias .filter-panel label { margin-bottom: .4rem; color: #fff; font-size: .85rem; font-weight: 600; }
    .repo-evidencias .filter-panel .form-control,
    .repo-evidencias .filter-panel .form-select {
        min-height: 46px;
        color: #263746;
        background: #fff;
        border: 1px solid #dce5eb;
        border-radius: 11px;
        box-shadow: none;
    }

    .repo-evidencias .filter-panel .form-control:focus,
    .repo-evidencias .filter-panel .form-select:focus {
        border-color: #62b8e8;
        box-shadow: 0 0 0 3px rgba(98, 184, 232, .2);
    }

    .repo-evidencias .btn-verde {
        flex: 1;
        min-height: 46px;
        padding: .55rem 1rem;
        color: #fff;
        background: var(--repo-verde);
        border: 0;
        border-radius: 11px;
        font-weight: 650;
        transition: background-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .repo-evidencias .btn-verde:hover {
        color: #fff;
        background: #2e8b00;
        box-shadow: 0 4px 12px rgba(57, 169, 0, .25);
        transform: translateY(-1px);
    }
    .repo-evidencias .filter-actions { display: flex; align-items: center; gap: .75rem; }
    .repo-evidencias .btn-limpiar {
        display: inline-flex;
        width: 54px;
        height: 46px;
        align-items: center;
        justify-content: center;
        padding: 0;
        color: #001a29 !important;
        background: #fff !important;
        border: 1px solid rgba(255, 255, 255, .9) !important;
        border-radius: 11px;
        box-shadow: 0 3px 8px rgba(0, 0, 0, .12);
        transition: background-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .repo-evidencias .btn-limpiar i { font-size: 1rem; transition: transform .25s ease; }
    .repo-evidencias .btn-limpiar:hover {
        background: #f0fbf4 !important;
        box-shadow: 0 4px 12px rgba(255, 255, 255, .16);
        transform: translateY(-1px);
    }
    .repo-evidencias .btn-limpiar:hover i { transform: rotate(-35deg); }

    .repo-evidencias .results-caption { margin: 0 0 .85rem; color: #64748b; }

    @media (max-width: 767.98px) {
        .repo-evidencias .filter-form-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .85rem; }
        .repo-evidencias .filter-actions { grid-column: 1 / -1; }
    }

    @media (max-width: 480px) {
        .repo-evidencias .filter-form-grid { grid-template-columns: minmax(0, 1fr); }
        .repo-evidencias .filter-actions { grid-column: auto; }
    }

    .repo-evidencias .evidence-card { height: 100%; overflow: hidden; transition: transform .18s ease, box-shadow .18s ease; }
    .repo-evidencias .evidence-card:hover { transform: translateY(-2px); box-shadow: 0 9px 24px rgba(0, 26, 41, .1); }
    .repo-evidencias .evidence-preview {
        display: flex;
        height: 190px;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        color: #64748b;
        background: #f1f5f9;
    }

    .repo-evidencias .evidence-preview--video { position: relative; height: auto; aspect-ratio: 16 / 9; background: #0f172a; }
    .repo-evidencias .evidence-preview--video iframe { width: 100%; height: 100%; border: 0; }
    .repo-evidencias .video-link-preview {
        display: flex;
        width: 100%;
        height: 100%;
        min-height: 190px;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: .6rem;
        color: #fff;
        background: linear-gradient(135deg, #0f172a, #1e3a5f);
        text-decoration: none;
        font-weight: 650;
    }

    .repo-evidencias .video-link-preview i { color: #a3e635; font-size: 2.8rem; }
    .repo-evidencias .video-link-preview:hover {
        color: #fff;
        background: linear-gradient(135deg, #172554, #075985);
    }
    .repo-evidencias .evidence-preview img { width: 100%; height: 100%; object-fit: cover; }
    .repo-evidencias .evidence-preview .preview-icon { font-size: 2.5rem; }
    .repo-evidencias .evidence-body { padding: 1rem; }
    .repo-evidencias .evidence-category {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .25rem .55rem;
        color: #277000;
        background: #eff8e9;
        border-radius: 999px;
        font-size: .75rem;
        font-weight: 700;
    }

    .repo-evidencias .evidence-title { margin: .65rem 0 .35rem; font-size: 1rem; font-weight: 700; overflow-wrap: anywhere; }
    .repo-evidencias .evidence-detail { min-height: 2.8em; margin: 0 0 .75rem; color: #64748b; font-size: .85rem; overflow-wrap: anywhere; }
    .repo-evidencias .evidence-meta { display: flex; justify-content: space-between; gap: .75rem; color: #64748b; font-size: .78rem; }
    .repo-evidencias .evidence-meta span { overflow-wrap: anywhere; }
    .repo-evidencias .empty-state { padding: 3rem 1rem; color: #64748b; text-align: center; }
    .repo-evidencias .empty-state i { display: block; margin-bottom: .75rem; color: #94a3b8; font-size: 2.4rem; }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid repo-evidencias py-3">
    <div class="page-heading">
        <div>
            <h1><i class="fas fa-images me-2" style="color: var(--repo-verde)"></i>Repositorio de evidencias</h1>
            <p>Fotos y videos organizados por origen: actividades, asistencias, acompañamientos y excusas.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="summary-card summary-card--activities">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="summary-label">Evidencias de actividades</span>
                    <span class="summary-icon"><i class="fas fa-list-check"></i></span>
                </div>
                <div class="summary-value">{{ $totalActividades }}</div>
                <div class="summary-description">Fotos y videos registrados</div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="summary-card summary-card--attendance">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="summary-label">Evidencias de asistencias</span>
                    <span class="summary-icon"><i class="fas fa-user-check"></i></span>
                </div>
                <div class="summary-value">{{ $totalAsistencias }}</div>
                <div class="summary-description">Fotos y videos registrados</div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="summary-card summary-card--excuses">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="summary-label">Soportes de excusas</span>
                    <span class="summary-icon"><i class="fas fa-file-circle-check"></i></span>
                </div>
                <div class="summary-value">{{ $totalExcusas }}</div>
                <div class="summary-description">Adjuntos de asistencia</div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="summary-card summary-card--attendance">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="summary-label">Evidencias de acompañamientos</span>
                    <span class="summary-icon"><i class="fas fa-hand-holding-heart"></i></span>
                </div>
                <div class="summary-value">{{ $totalAcompanamientos }}</div>
                <div class="summary-description">Fotos y videos de sesiones</div>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('controlecp.instructor.repositorio.evidencias') }}" class="panel filter-panel">
        <div class="filter-form-grid">
            <div>
                <label for="buscar" class="form-label">Buscar</label>
                <input id="buscar" type="search" name="buscar" value="{{ $busqueda }}" class="form-control"
                    placeholder="Nombre, ficha, descripción o responsable">
            </div>
            <div>
                <label for="categoria" class="form-label">Tipo de evidencia</label>
                <select id="categoria" name="categoria" class="form-select">
                    <option value="">Todas las categorías</option>
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria }}" @selected($filtroCategoria === $categoria)>{{ $categoria }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-verde"><i class="fas fa-search me-1"></i>Buscar</button>
                <a href="{{ route('controlecp.instructor.repositorio.evidencias') }}" class="btn btn-outline-secondary btn-limpiar"
                   aria-label="Limpiar filtros" title="Limpiar filtros"><i class="fas fa-rotate-left"></i></a>
            </div>
        </div>
    </form>

    @if($registros->isEmpty())
        <div class="panel empty-state">
            <i class="fas fa-folder-open"></i>
            <h2 class="h5">No hay evidencias para mostrar</h2>
            <p class="mb-0">Las fotos y videos de actividades, asistencias y acompañamientos, junto con los soportes de excusas, aparecerán aquí por separado.</p>
        </div>
    @else
        <div class="row g-3">
            @foreach($registros as $registro)
                <div class="col-12 col-sm-6 col-xl-4 col-xxl-3">
                    <article class="panel evidence-card">
                        @if($registro['es_video'])
                            <div class="evidence-preview evidence-preview--video">
                                @if($registro['url_video_embed'])
                                    <iframe src="{{ $registro['url_video_embed'] }}"
                                            title="Video: {{ $registro['titulo'] }}" loading="lazy"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                            referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                                @else
                                    <a class="video-link-preview" href="{{ $registro['url'] }}" target="_blank" rel="noopener noreferrer">
                                        <i class="fas fa-circle-play"></i><span>Ver video</span>
                                    </a>
                                @endif
                            </div>
                        @else
                            <a class="evidence-preview text-decoration-none" href="{{ $registro['url'] }}" target="_blank"
                               rel="noopener noreferrer" aria-label="Abrir {{ $registro['titulo'] }}">
                                <img src="{{ $registro['url'] }}" alt="{{ $registro['titulo'] }}" loading="lazy">
                            </a>
                        @endif
                        <div class="evidence-body">
                            <span class="evidence-category"><i class="fas fa-folder"></i>{{ $registro['categoria'] }}</span>
                            <h2 class="evidence-title">{{ $registro['titulo'] }}</h2>
                            <p class="evidence-detail">{{ $registro['detalle'] ?: $registro['tipo'] }}</p>
                            <div class="evidence-meta">
                                <span><i class="fas fa-user me-1"></i>{{ $registro['usuario'] ?: 'Sin responsable' }}</span>
                                <span>
                                    @if($registro['fecha'])
                                        <i class="fas fa-calendar me-1"></i>{{ \Illuminate\Support\Carbon::parse($registro['fecha'], 'UTC')->timezone('America/Bogota')->format('d/m/Y g:i A') }}
                                    @else
                                        Sin fecha
                                    @endif
                                </span>
                            </div>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>

        @if($registros->hasPages())
            <div class="panel mt-4 p-3">
                @if ($registros->hasPages())
                    <nav aria-label="Paginación de evidencias">
                        <ul class="pagination justify-content-center">
                            @if ($registros->onFirstPage())
                                <li class="page-item disabled"><span class="page-link" aria-hidden="true">&lsaquo;</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $registros->previousPageUrl() }}" aria-label="Página anterior">&lsaquo;</a></li>
                            @endif
                            @php
                                $ultimaPaginaMostrada = 0;
                            @endphp
                            @for ($pagina = 1; $pagina <= $registros->lastPage(); $pagina++)
                                @if ($pagina === 1 || $pagina === $registros->lastPage() || abs($pagina - $registros->currentPage()) <= 1)
                                    @if ($ultimaPaginaMostrada > 0 && $pagina > $ultimaPaginaMostrada + 1)
                                        <li class="page-item disabled"><span class="page-link">…</span></li>
                                    @endif
                                    <li class="page-item {{ $pagina === $registros->currentPage() ? 'active' : '' }}">
                                        <a class="page-link" href="{{ $registros->url($pagina) }}" @if($pagina === $registros->currentPage()) aria-current="page" @endif>{{ $pagina }}</a>
                                    </li>
                                    @php
                                    $ultimaPaginaMostrada = $pagina;
                                @endphp
                                @endif
                            @endfor
                            @if ($registros->hasMorePages())
                                <li class="page-item"><a class="page-link" href="{{ $registros->nextPageUrl() }}" aria-label="Página siguiente">&rsaquo;</a></li>
                            @else
                                <li class="page-item disabled"><span class="page-link" aria-hidden="true">&rsaquo;</span></li>
                            @endif
                        </ul>
                    </nav>
                @endif
            </div>
        @endif
    @endif
</div>
@endsection
