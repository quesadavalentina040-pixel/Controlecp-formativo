@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .historia-card { border: 0; border-left: 4px solid #39A900; border-radius: 16px; box-shadow: 0 4px 18px rgba(0,26,41,.07); }
    .historia-media { max-height: 260px; width: 100%; object-fit: cover; border-radius: 12px; }
    .historia-timeline { position: relative; }
    .historia-timeline::before { content:""; position:absolute; left:20px; top:0; bottom:0; width:2px; background:#d8f3dc; }
    .historia-item { position:relative; padding-left:56px; }
    .historia-number { position:absolute; left:0; top:1rem; width:42px; height:42px; border-radius:50%; background:#39A900; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; z-index:1; }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1 small text-muted"><li class="breadcrumb-item"><a href="{{ route('controlecp.instructor.inicio') }}" class="text-success text-decoration-none">Inicio</a></li><li class="breadcrumb-item active">Repositorio / Historia</li></ol></nav>
            <h2 class="h3 fw-bold mb-1" style="color:#001A29"><i class="fas fa-book-open me-2 text-success"></i>Historia de la Escuela Cultura de Paz</h2>
            <p class="text-muted small mb-0">Consulta el origen y propósito de la Escuela Cultura de Paz.</p>
        </div>
    </div>
    <div class="historia-timeline">
        @forelse($bloques as $bloque)
            <div class="historia-item mb-4">
                <div class="historia-number">{{ $loop->iteration }}</div>
                <article class="historia-card card">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-3" style="color:#001A29">{{ $bloque->titulo }}</h4>
                        <p class="mb-3" style="white-space:pre-line">{{ $bloque->texto }}</p>
                        @if($bloque->isImagen() && $bloque->archivo_url)
                            <img src="{{ $bloque->archivo_url }}" alt="{{ $bloque->titulo }}" class="historia-media">
                        @elseif($bloque->isAudio() && $bloque->archivo_url)
                            <div class="p-3 rounded-3" style="background:#f0fbf4"><div class="small text-muted mb-2"><i class="fas fa-volume-high me-1 text-success"></i>Audio del bloque</div><audio controls class="w-100"><source src="{{ $bloque->archivo_url }}"></audio></div>
                        @elseif($bloque->isVideo() && $bloque->url_video)
                            <a href="{{ $bloque->url_video }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-success rounded-pill"><i class="fas fa-video me-1"></i>Abrir video</a>
                        @endif
                    </div>
                </article>
            </div>
        @empty
            <div class="alert alert-info">No hay bloques de historia publicados.</div>
        @endforelse
    </div>
</div>
@endsection
