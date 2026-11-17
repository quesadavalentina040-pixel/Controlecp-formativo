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
        display: flex;
        align-items: center;
        gap: 1rem;
        height: 100%;
        min-height: 120px;
        padding: 1rem 1.2rem;
        background: #fff;
        border: 1px solid var(--summary-border);
        border-radius: 16px;
        color: var(--repo-petroleo);
        text-decoration: none;
        box-shadow: 0 3px 12px rgba(0, 26, 41, .035);
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .repo-evidencias .summary-card:hover,
    .repo-evidencias .summary-card:focus-visible {
        transform: translateY(-2px);
        color: var(--repo-petroleo);
        box-shadow: 0 7px 20px rgba(0, 26, 41, .08);
    }

    .repo-evidencias .summary-card.is-active {
        border-color: var(--summary-color);
        box-shadow: 0 0 0 2px var(--summary-border);
    }
    .repo-evidencias .summary-card--activities { --summary-color: #0070b8; --summary-border: #a7d8f5; --summary-icon-bg: #eaf5ff; }
    .repo-evidencias .summary-card--attendance { --summary-color: #278000; --summary-border: #b6e4a8; --summary-icon-bg: #eff8e9; }
    .repo-evidencias .summary-card--excuses { --summary-color: #e04444; --summary-border: #ffb6b6; --summary-icon-bg: #fff0f0; }
    .repo-evidencias .summary-card--accompaniment { --summary-color: #805ad5; --summary-border: #d6c7ff; --summary-icon-bg: #f3efff; }
    .repo-evidencias .summary-copy { min-width: 0; }
    .repo-evidencias .summary-label { display: block; color: var(--repo-petroleo); font-size: .95rem; font-weight: 750; }
    .repo-evidencias .summary-description { margin-top: .25rem; color: #64748b; font-size: .85rem; }
    .repo-evidencias .summary-count {
        display: inline-flex;
        width: 48px;
        height: 48px;
        flex: 0 0 48px;
        align-items: center;
        justify-content: center;
        margin-left: auto;
        border-radius: 14px;
        background: var(--summary-icon-bg);
        color: var(--summary-color);
        font-size: 1.3rem;
        font-weight: 800;
    }
    .repo-evidencias .summary-icon {
        display: inline-flex;
        width: 48px;
        height: 48px;
        flex: 0 0 48px;
        align-items: center;
        justify-content: center;
        color: var(--summary-color);
        background: var(--summary-icon-bg);
        border-radius: 14px;
        font-size: 1.05rem;
    }

    .repo-evidencias .welcome-state {
        position: relative;
        overflow: hidden;
        margin-bottom: 1.25rem;
        padding: 1.5rem;
        border-radius: 20px;
        background: linear-gradient(145deg, #fff 0%, #f8fbfc 100%);
        text-align: left;
    }

    .repo-evidencias .welcome-state::before {
        position: absolute;
        inset: 0 auto 0 0;
        width: 5px;
        background: linear-gradient(180deg, #39a900, #0070b8);
        content: "";
    }

    .repo-evidencias .welcome-header {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 1rem;
        margin-bottom: 1.25rem;
        text-align: left;
    }

    .repo-evidencias .welcome-icon {
        display: grid;
        width: 48px;
        height: 48px;
        flex: 0 0 48px;
        place-items: center;
        border-radius: 14px;
        background: var(--repo-petroleo);
        color: #62e31d;
        font-size: 1.15rem;
        box-shadow: 0 5px 14px rgba(0, 26, 41, .15);
    }

    .repo-evidencias .welcome-eyebrow {
        display: block;
        margin-bottom: .2rem;
        color: #39a900;
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    .repo-evidencias .welcome-title {
        margin: 0 0 .35rem;
        color: var(--repo-petroleo);
        font-size: 1.25rem;
        font-weight: 800;
    }

    .repo-evidencias .welcome-intro {
        max-width: 920px;
        margin: 0;
        color: #64748b;
        line-height: 1.65;
    }

    .repo-evidencias .welcome-guide {
        margin: 0 0 1rem;
        text-align: left;
    }

    .repo-evidencias .welcome-guide-item {
        --welcome-color: #0070b8;
        --welcome-bg: #eaf5ff;
        height: 100%;
        padding: 1rem;
        border: 1px solid var(--repo-borde);
        border-top: 3px solid var(--welcome-color);
        border-radius: 14px;
        background: #fff;
        color: #64748b;
        box-shadow: 0 3px 12px rgba(0, 26, 41, .04);
        line-height: 1.55;
    }

    .repo-evidencias .welcome-guide-item--excuses {
        --welcome-color: #e04444;
        --welcome-bg: #fff0f0;
    }

    .repo-evidencias .welcome-guide-item--attendance {
        --welcome-color: #278000;
        --welcome-bg: #eff8e9;
    }

    .repo-evidencias .welcome-guide-item--accompaniment {
        --welcome-color: #805ad5;
        --welcome-bg: #f3efff;
    }

    .repo-evidencias .welcome-guide-title {
        display: flex;
        align-items: center;
        gap: .65rem;
        margin-bottom: .6rem;
    }

    .repo-evidencias .welcome-guide-title strong {
        color: var(--repo-petroleo);
        font-size: .95rem;
        font-weight: 800;
    }

    .repo-evidencias .welcome-guide-icon {
        display: grid;
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        place-items: center;
        border-radius: 10px;
        background: var(--welcome-bg);
        color: var(--welcome-color);
    }

    .repo-evidencias .welcome-guide-item p { margin: 0; }

    .repo-evidencias .welcome-outro {
        display: flex;
        align-items: flex-start;
        gap: .65rem;
        margin: 0;
        padding: .85rem 1rem;
        border: 1px solid #d8edcc;
        border-radius: 12px;
        background: #f3faef;
        color: #4b6350;
        line-height: 1.55;
    }

    .repo-evidencias .welcome-outro i { margin-top: .18rem; color: #39a900; }

    @media (max-width: 575.98px) {
        .repo-evidencias .welcome-state { padding: 1rem; }
        .repo-evidencias .welcome-header { gap: .75rem; }
        .repo-evidencias .welcome-icon { width: 42px; height: 42px; flex-basis: 42px; }
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
    .repo-evidencias .ficha-choice-card { min-height: 94px; }
    .repo-evidencias .ficha-group { padding: 1rem; }
    .repo-evidencias .ficha-group--activities { --ficha-color: #0070b8; --ficha-border: #a7d8f5; }
    .repo-evidencias .ficha-group--attendance { --ficha-color: #278000; --ficha-border: #b6e4a8; }
    .repo-evidencias .ficha-group--excuses { --ficha-color: #e04444; --ficha-border: #ffb6b6; }
    .repo-evidencias .ficha-group--accompaniment { --ficha-color: #805ad5; --ficha-border: #d6c7ff; }
    .repo-evidencias .ficha-group { border-color: var(--ficha-border); }
    .repo-evidencias .ficha-group-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
        padding-bottom: .75rem;
        border-bottom: 1px solid var(--ficha-border);
        color: var(--ficha-color);
    }
    .repo-evidencias .ficha-group-heading { margin: 0; font-size: 1rem; font-weight: 800; }
    .repo-evidencias .ficha-group-header a { color: var(--ficha-color); }
    .repo-evidencias .ficha-pagination--activities { --ficha-color: #0070b8; }
    .repo-evidencias .ficha-pagination--attendance { --ficha-color: #278000; }
    .repo-evidencias .ficha-pagination--excuses { --ficha-color: #e04444; }
    .repo-evidencias .ficha-pagination--accompaniment { --ficha-color: #805ad5; }
    .repo-evidencias .ficha-pagination .page-item.active .page-link {
        border-color: var(--ficha-color);
        background: var(--ficha-color);
    }
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
            <a href="{{ route('controlecp.instructor.repositorio.evidencias', ['categoria' => 'Actividades']) }}"
               class="summary-card summary-card--activities {{ $filtroCategoria === 'Actividades' ? 'is-active' : '' }}"
               @if($filtroCategoria === 'Actividades') aria-current="page" @endif>
                <span class="summary-icon"><i class="fas fa-list-check"></i></span>
                <span class="summary-copy">
                    <span class="summary-label">Evidencias de actividades</span>
                    <span class="summary-description">Fotos y videos registrados</span>
                </span>
                <span class="summary-count" aria-label="{{ $totalActividades }} registros">{{ $totalActividades }}</span>
            </a>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('controlecp.instructor.repositorio.evidencias', ['categoria' => 'Asistencias']) }}"
               class="summary-card summary-card--attendance {{ $filtroCategoria === 'Asistencias' ? 'is-active' : '' }}"
               @if($filtroCategoria === 'Asistencias') aria-current="page" @endif>
                <span class="summary-icon"><i class="fas fa-user-check"></i></span>
                <span class="summary-copy">
                    <span class="summary-label">Evidencias de asistencias</span>
                    <span class="summary-description">Fotos y videos registrados</span>
                </span>
                <span class="summary-count" aria-label="{{ $totalAsistencias }} registros">{{ $totalAsistencias }}</span>
            </a>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('controlecp.instructor.repositorio.evidencias', ['categoria' => 'Excusas']) }}"
               class="summary-card summary-card--excuses {{ $filtroCategoria === 'Excusas' ? 'is-active' : '' }}"
               @if($filtroCategoria === 'Excusas') aria-current="page" @endif>
                <span class="summary-icon"><i class="fas fa-file-circle-check"></i></span>
                <span class="summary-copy">
                    <span class="summary-label">Soportes de excusas</span>
                    <span class="summary-description">Adjuntos de asistencia</span>
                </span>
                <span class="summary-count" aria-label="{{ $totalExcusas }} registros">{{ $totalExcusas }}</span>
            </a>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('controlecp.instructor.repositorio.evidencias', ['categoria' => 'Acompañamientos']) }}"
               class="summary-card summary-card--accompaniment {{ $filtroCategoria === 'Acompañamientos' ? 'is-active' : '' }}"
               @if($filtroCategoria === 'Acompañamientos') aria-current="page" @endif>
                <span class="summary-icon"><i class="fas fa-hand-holding-heart"></i></span>
                <span class="summary-copy">
                    <span class="summary-label">Evidencias de acompañamientos</span>
                    <span class="summary-description">Fotos y videos de sesiones</span>
                </span>
                <span class="summary-count" aria-label="{{ $totalAcompanamientos }} registros">{{ $totalAcompanamientos }}</span>
            </a>
        </div>
    </div>

    @if(empty($busqueda) && empty($filtroCategoria))
        <section class="panel welcome-state">
            <div class="welcome-header">
                <span class="welcome-icon"><i class="fas fa-hand-pointer"></i></span>
                <div>
                    <span class="welcome-eyebrow">Guía rápida del repositorio</span>
                    <h2 class="welcome-title">Explora las evidencias del instructor</h2>
                    <p class="welcome-intro">
                        Consulta fotos y videos de actividades, asistencias y acompañamientos, además de los soportes de excusas.
                        Usa los filtros para encontrar los registros que necesitas.
                    </p>
                </div>
            </div>
            <div class="row g-3 welcome-guide">
                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="welcome-guide-item">
                        <div class="welcome-guide-title">
                            <span class="welcome-guide-icon"><i class="fas fa-list-check"></i></span>
                            <strong>Actividades</strong>
                        </div>
                        <p>Revisa fotografías y videos que documentan las actividades realizadas.</p>
                    </article>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="welcome-guide-item welcome-guide-item--attendance">
                        <div class="welcome-guide-title">
                            <span class="welcome-guide-icon"><i class="fas fa-user-check"></i></span>
                            <strong>Asistencias</strong>
                        </div>
                        <p>Consulta los soportes visuales asociados a los registros de asistencia.</p>
                    </article>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="welcome-guide-item welcome-guide-item--excuses">
                        <div class="welcome-guide-title">
                            <span class="welcome-guide-icon"><i class="fas fa-file-circle-check"></i></span>
                            <strong>Excusas</strong>
                        </div>
                        <p>Encuentra los documentos adjuntos para justificar novedades de asistencia.</p>
                    </article>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="welcome-guide-item welcome-guide-item--accompaniment">
                        <div class="welcome-guide-title">
                            <span class="welcome-guide-icon"><i class="fas fa-hand-holding-heart"></i></span>
                            <strong>Acompañamientos</strong>
                        </div>
                        <p>Explora las evidencias fotográficas y de video de las sesiones.</p>
                    </article>
                </div>
            </div>
            <div class="welcome-outro">
                <i class="fas fa-lightbulb"></i>
                <p class="mb-0">Puedes buscar por nombre, ficha, descripción o responsable, y filtrar por tipo de evidencia para acotar los resultados.</p>
            </div>
        </section>
    @endif

    @if($filtroCategoria)
        <form method="GET" action="{{ route('controlecp.instructor.repositorio.evidencias') }}" class="panel filter-panel">
            <input type="hidden" name="categoria" value="{{ $filtroCategoria }}">
            @if($filtroFichaId !== '')
                <input type="hidden" name="ficha_id" value="{{ $filtroFichaId }}">
            @endif
            <div class="filter-form-grid">
                <div>
                    <label for="buscar" class="form-label">Buscar</label>
                    <input id="buscar" type="search" name="buscar" value="{{ $busqueda }}" class="form-control"
                        placeholder="Nombre, ficha, descripción o responsable">
                </div>
                <div>
                    <label class="form-label">Tipo de evidencia</label>
                    <div class="form-control bg-white">{{ $filtroCategoria }}</div>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-verde"><i class="fas fa-search me-1"></i>Buscar</button>
                    <a href="{{ route('controlecp.instructor.repositorio.evidencias', ['categoria' => $filtroCategoria]) }}"
                       class="btn btn-outline-secondary btn-limpiar" aria-label="Limpiar búsqueda" title="Limpiar búsqueda">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                </div>
            </div>
        </form>

        @if($filtroFichaId === '')
            @if($fichas->isEmpty())
                <div class="panel empty-state">
                    <i class="fas fa-folder-open"></i>
                    <h2 class="h5">No hay fichas para mostrar</h2>
                    <p class="mb-0">No se encontraron fichas para esta categoría y búsqueda.</p>
                </div>
            @else
                <div class="row g-3">
                    @foreach($fichasPaginadas as $ficha)
                        <div class="col-12 col-sm-6 col-xl-3">
                            <a href="{{ route('controlecp.instructor.repositorio.evidencias', [
                                'categoria' => $filtroCategoria,
                                'ficha_id' => $ficha['id'],
                                'buscar' => $busqueda,
                            ]) }}"
                               class="summary-card ficha-choice-card {{ $filtroCategoria === 'Actividades' ? 'summary-card--activities' : ($filtroCategoria === 'Asistencias' ? 'summary-card--attendance' : ($filtroCategoria === 'Excusas' ? 'summary-card--excuses' : 'summary-card--accompaniment')) }}"
                               aria-label="Ver evidencias de la ficha {{ $ficha['codigo'] }}">
                                <span class="summary-icon"><i class="fas fa-folder-open"></i></span>
                                <span class="summary-copy">
                                    <span class="summary-label">Ficha: {{ $ficha['codigo'] }}</span>
                                </span>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        @else
            @if($registros->isEmpty())
                <div class="panel empty-state">
                    <i class="fas fa-folder-open"></i>
                    <h2 class="h5">No hay evidencias para mostrar</h2>
                    <p class="mb-0">No se encontraron registros para esta ficha con la búsqueda seleccionada.</p>
                    <a href="{{ route('controlecp.instructor.repositorio.evidencias', [
                        'categoria' => $filtroCategoria,
                        'buscar' => $busqueda,
                    ]) }}" class="btn btn-outline-secondary mt-3">
                        <i class="fas fa-arrow-left me-1"></i>Volver a fichas
                    </a>
                </div>
            @else
                <section class="panel ficha-group ficha-group--{{ $filtroCategoria === 'Actividades' ? 'activities' : ($filtroCategoria === 'Asistencias' ? 'attendance' : ($filtroCategoria === 'Excusas' ? 'excuses' : 'accompaniment')) }}">
                    <div class="ficha-group-header">
                        <h2 class="ficha-group-heading">Ficha: {{ $fichaSeleccionada['codigo'] }}</h2>
                        <a href="{{ route('controlecp.instructor.repositorio.evidencias', [
                            'categoria' => $filtroCategoria,
                            'buscar' => $busqueda,
                        ]) }}" class="small fw-semibold text-decoration-none">
                            <i class="fas fa-arrow-left me-1"></i>Volver a fichas
                        </a>
                    </div>
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
                </section>
            @endif
        @endif

        @if($paginacion->hasPages())
            <div class="panel mt-4 p-3 ficha-pagination ficha-pagination--{{ $filtroCategoria === 'Actividades' ? 'activities' : ($filtroCategoria === 'Asistencias' ? 'attendance' : ($filtroCategoria === 'Excusas' ? 'excuses' : 'accompaniment')) }}">
                <nav aria-label="Paginación de resultados">
                    <ul class="pagination justify-content-center mb-0">
                        @if($paginacion->onFirstPage())
                            <li class="page-item disabled"><span class="page-link" aria-hidden="true">&lsaquo;</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $paginacion->previousPageUrl() }}" aria-label="Página anterior">&lsaquo;</a></li>
                        @endif
                        @php
                            $ultimaPaginaMostrada = 0;
                        @endphp
                        @for($pagina = 1; $pagina <= $paginacion->lastPage(); $pagina++)
                            @if($pagina === 1 || $pagina === $paginacion->lastPage() || abs($pagina - $paginacion->currentPage()) <= 1)
                                @if($ultimaPaginaMostrada > 0 && $pagina > $ultimaPaginaMostrada + 1)
                                    <li class="page-item disabled"><span class="page-link">…</span></li>
                                @endif
                                <li class="page-item {{ $pagina === $paginacion->currentPage() ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $paginacion->url($pagina) }}" @if($pagina === $paginacion->currentPage()) aria-current="page" @endif>{{ $pagina }}</a>
                                </li>
                                @php
                                    $ultimaPaginaMostrada = $pagina;
                                @endphp
                            @endif
                        @endfor
                        @if($paginacion->hasMorePages())
                            <li class="page-item"><a class="page-link" href="{{ $paginacion->nextPageUrl() }}" aria-label="Página siguiente">&rsaquo;</a></li>
                        @else
                            <li class="page-item disabled"><span class="page-link" aria-hidden="true">&rsaquo;</span></li>
                        @endif
                    </ul>
                </nav>
            </div>
        @endif
    @endif
</div>
@endsection
