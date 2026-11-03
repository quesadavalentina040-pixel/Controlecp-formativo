{{-- Historia de la Escuela Cultura de Paz. Vista de solo lectura para aprendices. --}}
@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .historia-block {
        border-left: 4px solid #39A900;
        border-radius: 14px;
        background: #fff;
    }
    .historia-media {
        border-radius: 12px;
        overflow: hidden;
        background: #f0fbf4;
    }
    .historia-media img {
        width: 100%;
        max-height: 360px;
        object-fit: cover;
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.aprendiz.inicio') }}" class="text-decoration-none" style="color:#39A900;">
                            <i class="fas fa-house me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Historia</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color:#001A29;font-family:'Outfit',sans-serif;">
                <i class="fas fa-book-open me-2" style="color:#39A900;"></i>Historia de la Escuela Cultura de Paz
            </h2>
            <p class="text-muted small mb-0">Conoce el origen y propósito de la Escuela Cultura de Paz</p>
        </div>
        <a href="{{ route('controlecp.aprendiz.inicio') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Volver al Inicio
        </a>
    </div>

    @forelse($bloques as $bloque)
        <article class="historia-block shadow-sm p-4 mb-4">
            <div class="row g-4 align-items-center">
                <div class="{{ $bloque->tipo_contenido === 'texto' ? 'col-12' : 'col-lg-6' }}">
                    <div class="small text-success fw-semibold mb-2">Bloque {{ $loop->iteration }}</div>
                    <h3 class="h4 fw-bold mb-3" style="color:#001A29;">{{ $bloque->titulo }}</h3>
                    @if($bloque->texto)
                        <div class="text-muted" style="white-space:pre-line; line-height:1.75;">{{ $bloque->texto }}</div>
                    @endif
                </div>

                @if($bloque->tipo_contenido !== 'texto')
                    <div class="col-lg-6">
                        <div class="historia-media p-2">
                            @if($bloque->isImagen() && $bloque->archivo_url)
                                <img src="{{ $bloque->archivo_url }}" alt="{{ $bloque->titulo }}" loading="lazy">
                            @elseif($bloque->isAudio() && $bloque->archivo_url)
                                <div class="p-4 text-center">
                                    <i class="fas fa-volume-high fa-2x text-success mb-3"></i>
                                    <audio class="w-100" controls preload="metadata">
                                        <source src="{{ $bloque->archivo_url }}">
                                        Tu navegador no permite reproducir este audio.
                                    </audio>
                                </div>
                            @elseif($bloque->isVideo() && $bloque->video_embed_url)
                                <div class="p-4 text-center">
                                    <i class="fas fa-video fa-2x text-success mb-3"></i>
                                    <p class="small text-muted mb-3">Video relacionado con este bloque</p>
                                    <a href="{{ $bloque->url_video }}" target="_blank" rel="noopener noreferrer"
                                       class="btn btn-success rounded-pill px-4">
                                        <i class="fas fa-arrow-up-right-from-square me-1"></i>Abrir video
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </article>
    @empty
        <div class="card border-0 shadow-sm text-center py-5">
            <i class="fas fa-book-open fa-2x text-muted mb-3"></i>
            <p class="text-muted mb-0">La historia aún no tiene contenido publicado.</p>
        </div>
    @endforelse
</div>
@endsection
