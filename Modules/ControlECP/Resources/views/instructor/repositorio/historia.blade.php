@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    :root {
        --verde-ecp: #39A900;
        --verde-ecp-hover: #2e8b00;
        --verde-ecp-claro: #d8f3dc;
        --petroleo-ecp: #001A29;
        --petroleo-ecp-medio: #00324D;
        --gris-ecp-borde: #e2e8f0;
    }

    .historia-header {
        background: var(--petroleo-ecp);
        border-radius: 24px;
        padding: 1.25rem 1.5rem;
    }

    .historia-timeline { padding: 2.5rem 0; }

    .historia-pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: .5rem 0 2rem;
    }

    .historia-pagination nav { justify-content: center !important; }
    .historia-pagination nav > div:first-child,
    .historia-pagination nav > div:last-child > div:first-child { display: none !important; }
    .historia-pagination nav > div:last-child {
        display: flex !important;
        flex: 0 0 auto !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .historia-pagination .pagination { margin-bottom: 0; }
    .historia-pagination .page-link { color: var(--verde-ecp); border-color: var(--gris-ecp-borde); }
    .historia-pagination .page-link:hover {
        color: #fff;
        background: var(--verde-ecp-hover);
        border-color: var(--verde-ecp-hover);
    }
    .historia-pagination .page-item.active .page-link {
        color: #fff;
        background: var(--verde-ecp);
        border-color: var(--verde-ecp);
    }

    .historia-item {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 4rem;
        width: 100%;
        margin-bottom: 4rem;
    }

    .historia-col-text,
    .historia-col-media { flex: 1; width: calc(50% - 1.75rem); }
    .historia-item:nth-child(even) { flex-direction: row-reverse; }

    .historia-narrative {
        position: relative;
        padding: .6rem 0 .6rem 1.75rem;
        border-left: 3px solid var(--verde-ecp);
    }

    .historia-narrative::before {
        position: absolute;
        top: .95rem;
        left: -7px;
        width: 11px;
        height: 11px;
        content: "";
        background: var(--verde-ecp);
        border-radius: 50%;
        box-shadow: 0 0 0 4px #fff;
    }

    .historia-media-wrapper {
        overflow: hidden;
        background: #fff;
        border: 1px solid var(--gris-ecp-borde);
        border-radius: 18px;
        box-shadow: 0 8px 24px rgba(0, 26, 41, .08);
        transition: transform .3s ease, box-shadow .3s ease;
    }

    .historia-media-wrapper:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(0, 26, 41, .14);
    }

    .historia-img {
        display: block;
        width: 100%;
        max-height: 380px;
        object-fit: cover;
        transition: transform .3s ease;
    }

    .historia-img:hover { transform: scale(1.02); }

    .historia-audio-container {
        padding: 2rem;
        background: linear-gradient(135deg, #f8fafc 0%, #edf8f0 100%);
        border-radius: 18px;
    }

    .historia-audio-icon-box {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 54px;
        height: 54px;
        color: var(--verde-ecp);
        background: #fff;
        border: 1px solid var(--verde-ecp-claro);
        border-radius: 14px;
        box-shadow: 0 4px 12px rgba(57, 169, 0, .18);
    }

    .historia-video-container {
        overflow: hidden;
        background: #000;
        border-radius: 18px;
    }

    .historia-video-container iframe { width: 100%; height: 100%; border: 0; }

    .historia-titulo {
        margin-bottom: .85rem;
        color: var(--petroleo-ecp);
        font-family: 'Outfit', sans-serif;
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1.25;
    }

    .historia-texto {
        margin-bottom: 1.2rem;
        color: #334155;
        font-size: 1.05rem;
        line-height: 1.7;
        white-space: pre-line;
    }

    @media (max-width: 767.98px) {
        .historia-item,
        .historia-item:nth-child(even) {
            flex-direction: column !important;
            gap: 1.5rem;
            margin-bottom: 3rem;
            padding-left: 3.5rem;
        }

        .historia-col-text,
        .historia-col-media { width: 100% !important; }
        .historia-titulo { font-size: 1.35rem; }
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-3 py-3">
    <div class="historia-header mb-4">
        <div>
            <div class="small fw-semibold mb-1" style="color: var(--verde-ecp);">
                <i class="fas fa-feather-pointed me-2"></i>Control ECP · Panel de instructor
            </div>
            <h1 class="h3 fw-bold mb-1 text-white">Historia de la Escuela Cultura de Paz</h1>
            <p class="mb-0" style="color: #c7d4da;">Origen y propósito de la escuela</p>
        </div>
    </div>

    @if($bloques->isEmpty())
        <div class="text-center p-5 my-5 bg-white border rounded-4 shadow-sm">
            <div class="mb-3">
                <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center"
                     style="width: 80px; height: 80px; background: var(--verde-ecp-claro); color: var(--verde-ecp);">
                    <i class="fas fa-feather-pointed fs-1"></i>
                </div>
            </div>
            <h2 class="h4 fw-bold text-dark">Aún no hay bloques de historia publicados</h2>
            <p class="text-muted mb-0">El contenido histórico estará disponible cuando sea publicado.</p>
        </div>
    @else
        <div class="historia-timeline" id="historiaTimeline">
            @foreach($bloques as $bloque)
                <article class="historia-item" id="bloque-item-{{ $bloque->id }}">
                    <div class="historia-col-text">
                        <div class="historia-narrative">
                            <h2 class="historia-titulo">{{ $bloque->titulo }}</h2>
                            <div class="historia-texto">{!! nl2br(e($bloque->texto)) !!}</div>
                        </div>
                    </div>

                    <div class="historia-col-media">
                        @if($bloque->tipo_contenido === 'imagen')
                            <div class="historia-media-wrapper">
                                @if($bloque->archivo_url)
                                    <img src="{{ $bloque->archivo_url }}" alt="{{ $bloque->titulo }}" class="historia-img" loading="lazy">
                                @else
                                    <div class="p-5 text-center text-muted bg-light">
                                        <i class="fas fa-image fs-1 mb-2"></i>
                                        <div>Sin imagen cargada</div>
                                    </div>
                                @endif
                            </div>
                        @elseif($bloque->tipo_contenido === 'audio')
                            <div class="historia-media-wrapper historia-audio-container">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="historia-audio-icon-box"><i class="fas fa-volume-high fs-4"></i></div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 1.1rem;">Contenido sonoro</div>
                                        <small class="text-muted">Registro auditivo del hito histórico</small>
                                    </div>
                                </div>
                                @if($bloque->archivo_url)
                                    <audio controls class="w-100 rounded-3 shadow-sm mt-2" preload="metadata">
                                        <source src="{{ $bloque->archivo_url }}">
                                        Tu navegador no soporta la reproducción de audio HTML5.
                                    </audio>
                                @else
                                    <div class="p-3 text-center text-muted bg-white rounded-3">
                                        <i class="fas fa-file-audio me-1"></i> Archivo de audio no disponible
                                    </div>
                                @endif
                            </div>
                        @elseif($bloque->tipo_contenido === 'video')
                            <div class="historia-media-wrapper historia-video-container ratio ratio-16x9">
                                @if($bloque->video_embed_url)
                                    <iframe src="{{ $bloque->video_embed_url }}"
                                            title="{{ $bloque->titulo }}"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                            allowfullscreen loading="lazy"></iframe>
                                @else
                                    <div class="d-flex flex-column align-items-center justify-content-center text-white bg-dark p-4">
                                        <i class="fas fa-video-slash fs-1 mb-2 text-danger"></i>
                                        <span>Enlace de video no válido o no configurado</span>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        @if($bloques->hasPages())
            <div class="historia-pagination">
                @if ($bloques->hasPages())
                    <nav aria-label="Paginación del historial">
                        <ul class="pagination justify-content-center">
                            @if ($bloques->onFirstPage())
                                <li class="page-item disabled"><span class="page-link" aria-hidden="true">&lsaquo;</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $bloques->previousPageUrl() }}" aria-label="Página anterior">&lsaquo;</a></li>
                            @endif
                            @php
                                $ultimaPaginaMostrada = 0;
                            @endphp
                            @for ($pagina = 1; $pagina <= $bloques->lastPage(); $pagina++)
                                @if ($pagina === 1 || $pagina === $bloques->lastPage() || abs($pagina - $bloques->currentPage()) <= 1)
                                    @if ($ultimaPaginaMostrada > 0 && $pagina > $ultimaPaginaMostrada + 1)
                                        <li class="page-item disabled"><span class="page-link">…</span></li>
                                    @endif
                                    <li class="page-item {{ $pagina === $bloques->currentPage() ? 'active' : '' }}">
                                        <a class="page-link" href="{{ $bloques->url($pagina) }}" @if($pagina === $bloques->currentPage()) aria-current="page" @endif>{{ $pagina }}</a>
                                    </li>
                                    @php
                                    $ultimaPaginaMostrada = $pagina;
                                @endphp
                                @endif
                            @endfor
                            @if ($bloques->hasMorePages())
                                <li class="page-item"><a class="page-link" href="{{ $bloques->nextPageUrl() }}" aria-label="Página siguiente">&rsaquo;</a></li>
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
