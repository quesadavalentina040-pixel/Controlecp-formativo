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

    .repo-evidencias .evidence-filters {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) 248px;
        align-items: end;
        gap: .75rem;
        padding: .8rem .9rem;
        border: 1px solid var(--repo-petroleo);
        border-radius: 16px;
        background: var(--repo-petroleo);
        box-shadow: 0 4px 18px rgba(0, 26, 41, .05);
    }

    .repo-evidencias .evidence-filter-field label {
        display: block;
        margin: 0 0 .2rem .1rem;
        color: #fff;
        font-size: .88rem;
        font-weight: 700;
    }

    .repo-evidencias .evidence-filter-field .form-control {
        min-height: 40px;
        border: 1px solid #dce5eb;
        border-radius: 8px;
        background: #fff;
        color: var(--repo-petroleo);
        box-shadow: none;
    }

    .repo-evidencias .evidence-filter-field .form-control:focus {
        border-color: #b7d6a8;
        box-shadow: 0 0 0 .15rem rgba(57, 169, 0, .12);
    }

    .repo-evidencias .evidence-filter-actions {
        display: flex;
        align-items: end;
        gap: .5rem;
    }

    .repo-evidencias .evidence-filter-actions .btn-filter {
        min-height: 38px;
        flex: 1;
        border: 1px solid #39a900;
        border-radius: 6px;
        background: #39a900;
        color: #fff;
        font-weight: 700;
    }

    .repo-evidencias .evidence-filter-actions .btn-filter:hover {
        border-color: #318f00;
        background: #318f00;
        color: #fff;
    }

    .repo-evidencias .evidence-filter-actions .btn-reset {
        display: grid;
        width: 40px;
        min-height: 38px;
        flex: 0 0 40px;
        place-items: center;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        background: #fff;
        color: #64748b;
    }

    .repo-evidencias .evidence-filter-actions .btn-reset:hover {
        background: #f8fafc;
        color: var(--repo-petroleo);
    }

    .repo-evidencias .page-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .repo-evidencias .page-heading h1 {
        margin: 0 0 .35rem;
        font-size: 1.65rem;
        font-weight: 750;
    }

    .repo-evidencias .page-heading p {
        margin: 0;
        color: #64748b;
    }

    .repo-evidencias .summary-card {
        --summary-color: #0070b8;
        --summary-border: #a7d8f5;
        --summary-icon-bg: #eaf5ff;
        display: flex;
        align-items: center;
        gap: 1rem;
        height: 100%;
        min-height: 140px;
        padding: 1rem 1.2rem;
        border: 1px solid var(--summary-border);
        border-radius: 16px;
        background: #fff;
        color: var(--repo-petroleo);
        text-decoration: none;
        box-shadow: 0 3px 12px rgba(0, 26, 41, .035);
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .repo-evidencias .summary-card:hover,
    .repo-evidencias .summary-card:focus-visible {
        transform: translateY(-2px);
        box-shadow: 0 7px 20px rgba(0, 26, 41, .08);
        color: var(--repo-petroleo);
    }

    .repo-evidencias .summary-card.is-active {
        border-color: var(--summary-color);
        box-shadow: 0 0 0 2px var(--summary-border);
    }

    .repo-evidencias .summary-card--activities {
        --summary-color: #0070b8;
        --summary-border: #a7d8f5;
        --summary-icon-bg: #eaf5ff;
    }

    .repo-evidencias .summary-card--attendance {
        --summary-color: #278000;
        --summary-border: #b6e4a8;
        --summary-icon-bg: #eff8e9;
    }

    .repo-evidencias .summary-card--excuses {
        --summary-color: #e04444;
        --summary-border: #ffb6b6;
        --summary-icon-bg: #fff0f0;
    }

    .repo-evidencias .summary-label {
        display: block;
        color: var(--repo-petroleo);
        font-size: .98rem;
        font-weight: 750;
    }

    .repo-evidencias .summary-copy {
        min-width: 0;
    }

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

    .repo-evidencias .summary-action {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        margin-top: .35rem;
        color: var(--summary-color);
        font-size: .82rem;
        font-weight: 750;
    }

    .repo-evidencias .summary-description {
        margin: .25rem 0 0;
        color: #64748b;
        font-size: .85rem;
    }

    .repo-evidencias .summary-icon {
        display: inline-flex;
        width: 52px;
        height: 52px;
        flex: 0 0 52px;
        align-items: center;
        justify-content: center;
        border-radius: 15px;
        background: var(--summary-icon-bg);
        color: var(--summary-color);
        font-size: 1.1rem;
    }

    .repo-evidencias .ficha-choice-card {
        min-height: 100px;
    }

    .repo-evidencias .evidence-card {
        height: 100%;
        overflow: hidden;
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .repo-evidencias .ficha-group {
        margin-bottom: 1rem;
        padding: .85rem;
        border: 1px solid var(--ficha-group-border);
        border-radius: 18px;
        box-shadow: 0 0 0 2px var(--ficha-group-ring), 0 4px 18px rgba(0, 26, 41, .05);
    }

    .repo-evidencias .ficha-group--excuses {
        --ficha-group-color: #e04444;
        --ficha-group-border: #ffb6b6;
        --ficha-group-ring: rgba(224, 68, 68, .1);
    }

    .repo-evidencias .ficha-group--activities {
        --ficha-group-color: #0070b8;
        --ficha-group-border: #a7d8f5;
        --ficha-group-ring: rgba(0, 112, 184, .1);
    }

    .repo-evidencias .ficha-group--attendance {
        --ficha-group-color: #278000;
        --ficha-group-border: #b6e4a8;
        --ficha-group-ring: rgba(39, 128, 0, .1);
    }

    .repo-evidencias .ficha-group-heading {
        margin: 0;
        color: var(--ficha-group-color);
        font-size: 1rem;
        font-weight: 750;
    }

    .repo-evidencias .ficha-group-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: .85rem;
        padding: 0 .1rem .7rem;
        border-bottom: 1px solid var(--ficha-group-border);
    }

    .repo-evidencias .ficha-group-header a {
        color: var(--ficha-group-color);
    }

    .repo-evidencias .evidence-pagination--excuses {
        --pagination-color: #e04444;
        --pagination-border: #ffb6b6;
        --pagination-hover: #fff0f0;
        --pagination-focus: rgba(224, 68, 68, .2);
    }

    .repo-evidencias .evidence-pagination--activities {
        --pagination-color: #0070b8;
        --pagination-border: #a7d8f5;
        --pagination-hover: #eaf5ff;
        --pagination-focus: rgba(0, 112, 184, .2);
    }

    .repo-evidencias .evidence-pagination--attendance {
        --pagination-color: #278000;
        --pagination-border: #b6e4a8;
        --pagination-hover: #eff8e9;
        --pagination-focus: rgba(39, 128, 0, .2);
    }

    .repo-evidencias .evidence-pagination .page-link {
        min-width: 40px;
        color: var(--pagination-color);
        border-color: var(--pagination-border);
        text-align: center;
    }

    .repo-evidencias .evidence-pagination .page-link:hover {
        color: var(--pagination-color);
        background: var(--pagination-hover);
    }

    .repo-evidencias .evidence-pagination .page-link:focus {
        box-shadow: 0 0 0 .2rem var(--pagination-focus);
    }

    .repo-evidencias .evidence-pagination .page-item.active .page-link {
        border-color: var(--pagination-color);
        background: var(--pagination-color);
        color: #fff;
    }

    .repo-evidencias .evidence-pagination .page-item.disabled .page-link {
        color: #94a3b8;
    }

    .repo-evidencias .evidence-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 9px 24px rgba(0, 26, 41, .1);
    }

    .repo-evidencias .evidence-preview {
        display: flex;
        height: 190px;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #f1f5f9;
        color: #64748b;
    }

    .repo-evidencias .evidence-preview--video {
        position: relative;
        height: auto;
        aspect-ratio: 16 / 9;
        background: #0f172a;
    }

    .repo-evidencias .evidence-preview--video iframe {
        width: 100%;
        height: 100%;
        border: 0;
    }

    .repo-evidencias .video-link-preview {
        display: flex;
        width: 100%;
        height: 100%;
        min-height: 190px;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: .6rem;
        background: linear-gradient(135deg, #0f172a, #1e3a5f);
        color: #fff;
        text-decoration: none;
        font-weight: 650;
    }

    .repo-evidencias .video-link-preview i {
        color: #a3e635;
        font-size: 2.8rem;
    }

    .repo-evidencias .video-link-preview:hover {
        color: #fff;
        background: linear-gradient(135deg, #172554, #075985);
    }

    .repo-evidencias .evidence-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .repo-evidencias .evidence-preview .preview-icon {
        font-size: 2.5rem;
    }

    .repo-evidencias .evidence-body {
        padding: 1rem;
    }

    .repo-evidencias .evidence-category {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .25rem .55rem;
        border-radius: 999px;
        background: #eff8e9;
        color: #277000;
        font-size: .75rem;
        font-weight: 700;
    }

    .repo-evidencias .evidence-title {
        margin: .65rem 0 .35rem;
        font-size: 1rem;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .repo-evidencias .evidence-detail {
        min-height: 2.8em;
        margin: 0 0 .75rem;
        color: #64748b;
        font-size: .85rem;
        overflow-wrap: anywhere;
    }

    .repo-evidencias .evidence-meta {
        display: flex;
        justify-content: space-between;
        gap: .75rem;
        color: #64748b;
        font-size: .78rem;
    }

    .repo-evidencias .evidence-meta span {
        overflow-wrap: anywhere;
    }

    .repo-evidencias .empty-state {
        padding: 3rem 1rem;
        color: #64748b;
        text-align: center;
    }

    .repo-evidencias .empty-state i {
        display: block;
        margin-bottom: .75rem;
        color: #94a3b8;
        font-size: 2.4rem;
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
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1.25rem;
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

    .repo-evidencias .welcome-guide-item p {
        margin: 0;
    }

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

    .repo-evidencias .welcome-outro i {
        margin-top: .18rem;
        color: #39a900;
    }

    @media (max-width: 575.98px) {
        .repo-evidencias .page-heading {
            flex-direction: column;
        }

        .repo-evidencias .welcome-state {
            padding: 1rem;
        }

        .repo-evidencias .welcome-header {
            gap: .75rem;
        }

        .repo-evidencias .welcome-icon {
            width: 42px;
            height: 42px;
            flex-basis: 42px;
        }
    }

    @media (max-width: 767.98px) {
        .repo-evidencias .evidence-filters {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .repo-evidencias .evidence-filter-actions {
            grid-column: 1 / -1;
            justify-self: end;
            width: 248px;
        }
    }

    @media (max-width: 480px) {
        .repo-evidencias .evidence-filters {
            grid-template-columns: minmax(0, 1fr);
        }

        .repo-evidencias .evidence-filter-actions {
            grid-column: auto;
            width: 100%;
        }
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid repo-evidencias py-3">
    <div class="page-heading">
        <div>
            <h1><i class="fas fa-images me-2" style="color: var(--repo-verde)"></i>Repositorio de evidencias</h1>
            <p>Fotos y videos organizados por origen: actividades, asistencias y excusas.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-4">
            <a href="{{ route('controlecp.administrador.repositorio.evidencias', ['categoria' => 'Excusas']) }}"
                class="summary-card summary-card--excuses {{ $filtroCategoria === 'Excusas' ? 'is-active' : '' }}"
                @if($filtroCategoria === 'Excusas') aria-current="page" @endif>
                <span class="summary-icon"><i class="fas fa-file-circle-check"></i></span>
                <span class="summary-copy">
                    <span class="summary-label">Excusas</span>
                    <span class="summary-description">Consulta justificaciones y sus soportes adjuntos.</span>
                </span>
                <span class="summary-count" aria-label="{{ $cantidadExcusas }} registros">{{ $cantidadExcusas }}</span>
            </a>
        </div>
        <div class="col-12 col-lg-4">
            <a href="{{ route('controlecp.administrador.repositorio.evidencias', ['categoria' => 'Actividades']) }}"
                class="summary-card summary-card--activities {{ $filtroCategoria === 'Actividades' ? 'is-active' : '' }}"
                @if($filtroCategoria === 'Actividades') aria-current="page" @endif>
                <span class="summary-icon"><i class="fas fa-list-check"></i></span>
                <span class="summary-copy">
                    <span class="summary-label">Evidencias de actividades</span>
                    <span class="summary-description">Explora fotos y videos registrados en actividades.</span>
                </span>
                <span class="summary-count" aria-label="{{ $cantidadActividades }} registros">{{ $cantidadActividades }}</span>
            </a>
        </div>
        <div class="col-12 col-lg-4">
            <a href="{{ route('controlecp.administrador.repositorio.evidencias', ['categoria' => 'Asistencias']) }}"
                class="summary-card summary-card--attendance {{ $filtroCategoria === 'Asistencias' ? 'is-active' : '' }}"
                @if($filtroCategoria === 'Asistencias') aria-current="page" @endif>
                <span class="summary-icon"><i class="fas fa-user-check"></i></span>
                <span class="summary-copy">
                    <span class="summary-label">Evidencias de asistencias</span>
                    <span class="summary-description">Revisa los registros visuales de asistencia.</span>
                </span>
                <span class="summary-count" aria-label="{{ $cantidadAsistencias }} registros">{{ $cantidadAsistencias }}</span>
            </a>
        </div>
    </div>

    @if(!$filtroCategoria)
        <section class="panel welcome-state">
            <div class="welcome-header">
                <span class="welcome-icon"><i class="fas fa-hand-pointer"></i></span>
                <div>
                    <span class="welcome-eyebrow">Guía rápida del repositorio</span>
                    <h2 class="welcome-title">Selecciona una acción para comenzar</h2>
                    <p class="welcome-intro">
                        El repositorio reúne fotografías y videos que respaldan las excusas, las actividades y las asistencias.
                        Elige una de las categorías de arriba para encontrar y revisar los registros que necesitas.
                    </p>
                </div>
            </div>
            <div class="row g-3 welcome-guide">
                <div class="col-12 col-md-4">
                    <article class="welcome-guide-item welcome-guide-item--excuses">
                        <div class="welcome-guide-title">
                            <span class="welcome-guide-icon"><i class="fas fa-file-circle-check"></i></span>
                            <strong>Excusas</strong>
                        </div>
                        <p>Consulta las justificaciones y sus soportes adjuntos para conocer los documentos aportados y quién registró cada caso.</p>
                    </article>
                </div>
                <div class="col-12 col-md-4">
                    <article class="welcome-guide-item">
                        <div class="welcome-guide-title">
                            <span class="welcome-guide-icon"><i class="fas fa-list-check"></i></span>
                            <strong>Actividades</strong>
                        </div>
                        <p>Explora fotos y videos asociados a las actividades. Los registros se organizan por ficha para facilitar su revisión.</p>
                    </article>
                </div>
                <div class="col-12 col-md-4">
                    <article class="welcome-guide-item welcome-guide-item--attendance">
                        <div class="welcome-guide-title">
                            <span class="welcome-guide-icon"><i class="fas fa-user-check"></i></span>
                            <strong>Asistencias</strong>
                        </div>
                        <p>Ubica los soportes visuales de las jornadas, revisa quién los registró y consulta las evidencias de cada ficha.</p>
                    </article>
                </div>
            </div>
            <div class="welcome-outro">
                <i class="fas fa-lightbulb"></i>
                <p class="mb-0">Después de elegir una categoría, selecciona una ficha para ver sus evidencias. Puedes buscar por ficha o por usuario que registró, y usar la paginación para recorrer todos los resultados.</p>
            </div>
        </section>
    @endif

    @if($filtroCategoria)
        <form method="GET" action="{{ route('controlecp.administrador.repositorio.evidencias') }}" class="evidence-filters mb-3">
            <input type="hidden" name="categoria" value="{{ $filtroCategoria }}">
            @if($filtroFichaId !== '')
                <input type="hidden" name="ficha_id" value="{{ $filtroFichaId }}">
            @endif
            <div class="evidence-filter-field">
                <label for="filtroFicha">Ficha</label>
                <input id="filtroFicha" name="ficha" type="search" class="form-control"
                    value="{{ $filtroFicha }}" list="fichasDisponibles" placeholder="Buscar ficha...">
                <datalist id="fichasDisponibles">
                    @foreach($fichas as $ficha)
                        <option value="{{ $ficha['codigo'] }}"></option>
                    @endforeach
                </datalist>
            </div>
            <div class="evidence-filter-field">
                <label for="filtroUsuario">Usuario que registró</label>
                <input id="filtroUsuario" name="usuario" type="search" class="form-control"
                    value="{{ $filtroUsuario }}" list="usuariosDisponibles" placeholder="Buscar usuario...">
                <datalist id="usuariosDisponibles">
                    @foreach($usuarios as $usuario)
                        <option value="{{ $usuario['nombre'] }}"></option>
                    @endforeach
                </datalist>
            </div>
            <div class="evidence-filter-actions">
                <button type="submit" class="btn btn-filter">
                    <i class="fas fa-magnifying-glass me-1"></i>Buscar
                </button>
                <a href="{{ route('controlecp.administrador.repositorio.evidencias', ['categoria' => $filtroCategoria]) }}"
                    class="btn btn-reset" title="Limpiar filtros" aria-label="Limpiar filtros">
                    <i class="fas fa-rotate-left"></i>
                </a>
            </div>
        </form>

        @if($filtroFichaId === '')
            @if($fichas->isEmpty())
                <div class="panel empty-state">
                    <i class="fas fa-folder-open"></i>
                    <h2 class="h5">No hay fichas para mostrar</h2>
                    <p class="mb-0">No se encontraron fichas con esos filtros.</p>
                </div>
            @else
                <div class="row g-3">
                    @foreach($fichasPaginadas as $ficha)
                        <div class="col-12 col-sm-6 col-xl-3">
                            <a href="{{ route('controlecp.administrador.repositorio.evidencias', [
                                'categoria' => $filtroCategoria,
                                'ficha_id' => $ficha['id'],
                                'usuario' => $filtroUsuario,
                            ]) }}"
                                class="summary-card ficha-choice-card {{ $filtroCategoria === 'Excusas' ? 'summary-card--excuses' : ($filtroCategoria === 'Actividades' ? 'summary-card--activities' : 'summary-card--attendance') }}"
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
                <h2 class="h5">
                    @if($filtroCategoria === 'Excusas')
                        No hay excusas para mostrar
                    @elseif($filtroCategoria === 'Actividades')
                        No hay evidencias de actividades para mostrar
                    @elseif($filtroCategoria === 'Asistencias')
                        No hay evidencias de asistencias para mostrar
                    @else
                        No hay evidencias para mostrar
                    @endif
                </h2>
                <p class="mb-0">No se encontraron registros en esta categoría.</p>
                <a href="{{ route('controlecp.administrador.repositorio.evidencias', [
                    'categoria' => $filtroCategoria,
                    'ficha' => $filtroFicha,
                    'usuario' => $filtroUsuario,
                ]) }}" class="btn btn-outline-secondary mt-3">
                    <i class="fas fa-arrow-left me-1"></i>Volver a fichas
                </a>
            </div>
        @else
            <section class="panel ficha-group ficha-group--{{ $filtroCategoria === 'Excusas' ? 'excuses' : ($filtroCategoria === 'Actividades' ? 'activities' : 'attendance') }}">
                        <div class="ficha-group-header">
                            <h2 class="ficha-group-heading">Ficha: {{ $fichaSeleccionada }}</h2>
                            <a href="{{ route('controlecp.administrador.repositorio.evidencias', [
                                'categoria' => $filtroCategoria,
                                'ficha' => $filtroFicha,
                                'usuario' => $filtroUsuario,
                            ]) }}" class="small fw-semibold text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>Volver a fichas
                            </a>
                        </div>
                        <div class="row g-3">
                            @foreach($registros as $registro)
                                <div class="col-12 col-sm-6 col-xl-3">
                                    <article class="panel evidence-card">
                                        @if($registro['es_video'])
                                            <div class="evidence-preview evidence-preview--video">
                                                @if($registro['url_video_embed'])
                                                    <iframe
                                                        src="{{ $registro['url_video_embed'] }}"
                                                        title="Video: {{ $registro['titulo'] }}"
                                                        loading="lazy"
                                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                                        referrerpolicy="strict-origin-when-cross-origin"
                                                        allowfullscreen>
                                                    </iframe>
                                                @else
                                                    <a class="video-link-preview"
                                                        href="{{ $registro['url'] }}"
                                                        target="_blank"
                                                        rel="noopener noreferrer">
                                                        <i class="fas fa-circle-play"></i>
                                                        <span>Ver video</span>
                                                    </a>
                                                @endif
                                            </div>
                                        @else
                                            <a class="evidence-preview text-decoration-none"
                                                href="{{ $registro['url'] }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                aria-label="Abrir {{ $registro['titulo'] }}">
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
                                                        <i class="fas fa-calendar me-1"></i>{{ \Illuminate\Support\Carbon::parse($registro['fecha'])->format('d/m/Y') }}
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
            <div class="panel mt-4 p-3 evidence-pagination evidence-pagination--{{ $filtroCategoria === 'Excusas' ? 'excuses' : ($filtroCategoria === 'Actividades' ? 'activities' : 'attendance') }}">
                <nav aria-label="Paginación de resultados">
                    <ul class="pagination justify-content-center">
                        @if ($paginacion->onFirstPage())
                            <li class="page-item disabled"><span class="page-link" aria-hidden="true">&lsaquo;</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $paginacion->previousPageUrl() }}" aria-label="Página anterior">&lsaquo;</a></li>
                        @endif
                        @php
                            $ultimaPaginaMostrada = 0;
                        @endphp
                        @for ($pagina = 1; $pagina <= $paginacion->lastPage(); $pagina++)
                            @if ($pagina === 1 || $pagina === $paginacion->lastPage() || abs($pagina - $paginacion->currentPage()) <= 1)
                                @if ($ultimaPaginaMostrada > 0 && $pagina > $ultimaPaginaMostrada + 1)
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
                        @if ($paginacion->hasMorePages())
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