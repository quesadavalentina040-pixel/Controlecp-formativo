
@extends('controlecp::components.layouts.dashboard')

@push('styles')

<style>
    :root {
        --verde-ecp: #39A900;
        --verde-ecp-hover: #2e8b00;
        --verde-ecp-claro: #d8f3dc;
        --verde-ecp-suave: #f0fbf4;
        --petroleo-ecp: #001A29;
        --petroleo-ecp-medio: #00324D;
        --azul-ecp-claro: #d0e8f5;
        --gris-ecp-texto: #52796f;
        --gris-ecp-borde: #e2e8f0;
    }

    /* Contenedor general */
    .historia-container {
        max-width: 1320px;
        margin: 0 auto;
    }

    .historia-header {
        background: transparent;
        border: none;
        box-shadow: none;
        padding: 0.25rem 0 0.5rem;
        border-bottom: 1px solid rgba(82, 121, 111, 0.18);
    }

    /* Tarjetas con sombra suave y estilo ECP */
    .historia-card {
        background: #ffffff;
        border: 1px solid var(--gris-ecp-borde);
        border-radius: 18px;
        box-shadow: 0 4px 20px rgba(0, 26, 41, 0.05);
        transition: transform .25s ease, box-shadow .25s ease;
    }

    .historia-card:hover {
        box-shadow: 0 10px 28px rgba(0, 26, 41, 0.09);
    }

    /* Estado inactivo: borde sutil y tono atenuado */
    .historia-item.is-inactive .historia-card {
        background: #fafbfc;
        border: 2px dashed #cbd5e1;
        opacity: 0.88;
    }

    .historia-item.is-inactive .historia-card:hover {
        opacity: 1;
    }

    .historia-narrative {
        position: relative;
        padding: 0.6rem 0 0.6rem 1.75rem;
        border-left: 3px solid var(--verde-ecp);
    }

    .historia-narrative::before {
        content: "";
        position: absolute;
        top: 0.95rem;
        left: -7px;
        width: 11px;
        height: 11px;
        border-radius: 50%;
        background: var(--verde-ecp);
        box-shadow: 0 0 0 4px #ffffff;
    }

    .historia-item.is-inactive .historia-narrative {
        border-left-color: #94a3b8;
    }

    .historia-item.is-inactive .historia-narrative::before {
        background: #94a3b8;
    }

    .historia-inactive-note {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
        color: #b42318;
        font-size: 0.875rem;
    }

    /* Botones primarios SENA */
    .btn-ecp-primary {
        background-color: var(--verde-ecp);
        color: #ffffff !important;
        border: 1px solid var(--verde-ecp);
        font-weight: 600;
        border-radius: 50rem;
        padding: 0.55rem 1.4rem;
        transition: all .2s ease;
    }

    .btn-ecp-primary:hover {
        background-color: var(--verde-ecp-hover);
        border-color: var(--verde-ecp-hover);
        box-shadow: 0 4px 14px rgba(57, 169, 0, 0.35);
        transform: translateY(-1px);
    }

    .btn-ecp-navy {
        background-color: var(--petroleo-ecp);
        color: #ffffff !important;
        border: 1px solid var(--petroleo-ecp);
        font-weight: 600;
        border-radius: 50rem;
        padding: 0.55rem 1.4rem;
        transition: all .2s ease;
    }

    .btn-ecp-navy:hover {
        background-color: var(--petroleo-ecp-medio);
        border-color: var(--petroleo-ecp-medio);
        box-shadow: 0 4px 14px rgba(0, 26, 41, 0.25);
        transform: translateY(-1px);
    }

    .btn-ecp-outline {
        background-color: transparent;
        color: var(--petroleo-ecp);
        border: 1px solid var(--petroleo-ecp);
        font-weight: 500;
        border-radius: 50rem;
        transition: all .2s ease;
    }

    .btn-ecp-outline:hover {
        background-color: var(--petroleo-ecp);
        color: #ffffff;
    }

    /* ----------------------------------------------------
       LÍNEA DE TIEMPO EN ZIGZAG (ESTILO SICCSAC)
       Alternancia estrictamente con CSS :nth-child(even)
    ---------------------------------------------------- */
    .historia-timeline {
        position: relative;
        padding: 2.5rem 0;
    }

    .historia-pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0.5rem 0 2rem;
    }

    .historia-pagination nav {
        justify-content: center !important;
    }

    .historia-pagination nav > div:first-child {
        display: none !important;
    }

    .historia-pagination nav > div:last-child {
        display: flex !important;
        flex: 0 0 auto !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .historia-pagination nav > div:last-child > div:first-child {
        display: none !important;
    }

    .historia-pagination .pagination {
        margin-bottom: 0;
    }

    .historia-pagination .page-link {
        color: var(--verde-ecp);
        border-color: var(--gris-ecp-borde);
    }

    .historia-pagination .page-link:hover {
        color: #ffffff;
        background-color: var(--verde-ecp-hover);
        border-color: var(--verde-ecp-hover);
    }

    .historia-pagination .page-item.active .page-link {
        color: #ffffff;
        background-color: var(--verde-ecp);
        border-color: var(--verde-ecp);
    }

    /* Cada bloque en la línea de tiempo */
    .historia-item {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 4rem;
        margin-bottom: 4rem;
        width: 100%;
        z-index: 2;
    }

    /* Columnas de texto y multimedia */
    .historia-col-text,
    .historia-col-media {
        flex: 1;
        width: calc(50% - 1.75rem);
    }

    /* REGLA ZIGZAG: Bloques pares invierten la dirección con CSS */
    .historia-item:nth-child(even) {
        flex-direction: row-reverse;
    }

    /* Contenido multimedia */
    .historia-media-wrapper {
        border-radius: 18px;
        overflow: hidden;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(0, 26, 41, 0.08);
        border: 1px solid var(--gris-ecp-borde);
        transition: transform .3s ease, box-shadow .3s ease;
    }

    .historia-media-wrapper:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(0, 26, 41, 0.14);
    }

    .historia-img {
        width: 100%;
        max-height: 380px;
        object-fit: cover;
        display: block;
        transition: transform .3s ease;
    }

    .historia-img:hover {
        transform: scale(1.02);
    }

    /* Reproductor de Audio */
    .historia-audio-container {
        padding: 2rem;
        background: linear-gradient(135deg, #f8fafc 0%, #edf8f0 100%);
        border-radius: 18px;
    }

    .historia-audio-icon-box {
        width: 54px;
        height: 54px;
        border-radius: 14px;
        background: #ffffff;
        color: var(--verde-ecp);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(57, 169, 0, 0.18);
        border: 1px solid var(--verde-ecp-claro);
    }

    /* Video responsivo */
    .historia-video-container {
        border-radius: 18px;
        overflow: hidden;
        background-color: #000;
    }

    .historia-video-container iframe {
        width: 100%;
        height: 100%;
        border: none;
    }

    /* Tipografía y espaciado de textos */
    .historia-titulo {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 1.75rem;
        color: var(--petroleo-ecp);
        line-height: 1.25;
        margin-bottom: 0.85rem;
    }

    .historia-texto {
        color: #334155;
        font-size: 1.05rem;
        line-height: 1.7;
        margin-bottom: 1.2rem;
        white-space: pre-line;
    }

    /* Selector de tipo interactivo */
    .tipo-card-option {
        cursor: pointer;
        border: 2px solid var(--gris-ecp-borde);
        border-radius: 14px;
        padding: 1rem 0.75rem;
        text-align: center;
        transition: all .2s ease;
        background-color: #ffffff;
    }

    .tipo-card-option:hover {
        border-color: var(--verde-ecp);
        background-color: #f9fdfa;
    }

    .tipo-radio:checked + .tipo-card-option {
        border-color: var(--verde-ecp);
        background-color: #f0fbf4;
        box-shadow: 0 4px 12px rgba(57, 169, 0, 0.15);
    }

    .tipo-radio:checked + .tipo-card-option i {
        color: var(--verde-ecp) !important;
    }

    /* ----------------------------------------------------
       RESPONSIVE DESIGN (< 768px)
       Apilar en 1 sola columna, sin alternancia
    ---------------------------------------------------- */
    @media (max-width: 767.98px) {
        .historia-item,
        .historia-item:nth-child(even) {
            flex-direction: column !important;
            gap: 1.5rem;
            margin-bottom: 3rem;
            padding-left: 3.5rem;
        }

        .historia-col-text,
        .historia-col-media {
            width: 100% !important;
        }

        .historia-titulo {
            font-size: 1.35rem;
        }
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-3 py-3 historia-container">

    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090;">
        @if(session('success'))
            <div class="toast align-items-center text-white border-0 show shadow" style="background-color: var(--verde-ecp);" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i class="fas fa-check-circle fs-5"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="toast align-items-center text-white bg-danger border-0 show shadow" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i class="fas fa-exclamation-triangle fs-5"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
                </div>
            </div>
        @endif

        <div id="toastAjax" class="toast align-items-center text-white border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true" style="display: none;">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i id="toastAjaxIcon" class="fas fa-check-circle fs-5"></i>
                    <span id="toastAjaxMsg">Operación exitosa</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="fas fa-circle-exclamation fs-5"></i>
                <strong class="font-outfit">Por favor corrige los siguientes inconvenientes antes de guardar:</strong>
            </div>
            <ul class="mb-0 ps-4 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="historia-header mb-4">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <h1 class="display-6 fw-bold text-dark mb-1" style="font-size: clamp(2rem, 2.5vw, 3rem); line-height: 1.1; letter-spacing: -0.03em;">
                    Historia de la Escuela Cultura de Paz
                </h1>
                <p class="text-muted mb-0">
                    Control ECP · Origen y propósito de la escuela
                </p>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <button type="button" class="btn btn-ecp-primary d-flex align-items-center gap-2 shadow-sm" onclick="abrirModalCrear()">
                    <i class="fas fa-plus-circle"></i>
                    <span>Agregar Bloque</span>
                </button>
            </div>
        </div>
    </div>

    @if($bloques->isEmpty())
        <div class="historia-card text-center p-5 my-5">
            <div class="mb-3">
                <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background-color: var(--verde-ecp-claro); color: var(--verde-ecp);">
                    <i class="fas fa-feather-pointed fs-1"></i>
                </div>
            </div>
            <h3 class="font-outfit fw-bold text-dark">Aún no hay bloques de historia registrados</h3>
            <p class="text-muted mx-auto" style="max-width: 500px;">
                Comienza a construir la línea de tiempo histórica de la Escuela Cultura de Paz. Agrega hitos con imágenes, audios o enlaces de video.
            </p>
            <button type="button" class="btn btn-ecp-primary px-4 py-2 mt-2" onclick="abrirModalCrear()">
                <i class="fas fa-plus me-1"></i> Agregar el primer bloque
            </button>
        </div>
    @else

        <div class="historia-timeline" id="historiaTimeline">

            @foreach($bloques as $bloque)
                <div class="historia-item {{ !$bloque->activo ? 'is-inactive' : '' }}"
                     id="bloque-item-{{ $bloque->id }}">

                    <div class="historia-col-text">
                        <div class="historia-narrative">

                            <h2 class="historia-titulo">{{ $bloque->titulo }}</h2>
                            <div class="historia-texto">{!! nl2br(e($bloque->texto)) !!}</div>

                            @if(!$bloque->activo)
                                <div class="historia-inactive-note">
                                    <i class="fas fa-eye-slash"></i>
                                    <span><strong>Inactivo:</strong> Este bloque no se visualiza actualmente a los aprendices.</span>
                                </div>
                            @endif

                            <div class="pt-3 mt-3 border-top d-flex justify-content-end">
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary rounded-pill px-3 d-flex align-items-center gap-1"
                                        onclick='abrirModalEditar(@json($bloque))'>
                                    <i class="fas fa-pen-to-square"></i>
                                    <span>Editar</span>
                                </button>
                            </div>

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
                                    <div class="historia-audio-icon-box">
                                        <i class="fas fa-volume-high fs-4"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark font-outfit" style="font-size: 1.1rem;">Contenido Sonoro</div>
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
                                            allowfullscreen>
                                    </iframe>
                                @else
                                    <div class="d-flex flex-column align-items-center justify-content-center text-white bg-dark p-4">
                                        <i class="fas fa-video-slash fs-1 mb-2 text-danger"></i>
                                        <span>Enlace de video no válido o no configurado</span>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                </div>
            @endforeach
        </div>

        @if($bloques->hasPages())
            <div class="historia-pagination">
                {{ $bloques->links('pagination::bootstrap-5') }}
            </div>
        @endif
    @endif

</div>

<div class="modal fade" id="modalBloque" tabindex="-1" aria-labelledby="modalBloqueTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">

            <div class="modal-header text-white" style="background-color: var(--petroleo-ecp); border-bottom: 3px solid var(--verde-ecp);">
                <div class="d-flex align-items-center gap-2">
                    <i id="modalBloqueIcon" class="fas fa-plus-circle fs-5" style="color: #62E31D;"></i>
                    <h5 class="modal-title font-outfit fw-bold" id="modalBloqueTitle">Agregar Nuevo Bloque</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formBloque" method="POST" action="{{ route('controlecp.administrador.repositorio.historia.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="formBloqueMethod" value="POST">
                <input type="hidden" id="bloqueIdEditando" value="">

                <div class="modal-body p-4">

                    <div class="alert alert-light border d-flex align-items-center gap-2 py-2 px-3 mb-4 rounded-3 small">
                        <i class="fas fa-info-circle text-primary"></i>
                        <span>Los bloques se ordenarán cronológicamente en la línea de tiempo en zigzag.</span>
                    </div>

                    <div class="mb-3">
                        <label for="campo_titulo" class="form-label fw-bold font-outfit text-dark small mb-1">
                            Título del Bloque <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="titulo"
                               id="campo_titulo"
                               class="form-control rounded-pill px-3 py-2"
                               placeholder="Ej: Fundación de la Escuela Cultura de Paz"
                               maxlength="255"
                               required>

                        <div class="invalid-feedback">Por favor escribe el título del bloque.</div>
                    </div>

                    <div class="mb-4">
                        <label for="campo_texto" class="form-label fw-bold font-outfit text-dark small mb-1">
                            Descripción / Narración del Hito <span class="text-danger">*</span>
                        </label>
                        <textarea name="texto"
                                  id="campo_texto"
                                  rows="4"
                                  class="form-control rounded-4 p-3"
                                  placeholder="Describe este acontecimiento histórico, fechas clave y contexto..."
                                  required></textarea>

                        <div class="invalid-feedback">Por favor escribe el texto del bloque.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold font-outfit text-dark small mb-2">
                            Tipo de Contenido Multimedia <span class="text-danger">*</span>
                        </label>
                        <div class="row g-2">
                            <div class="col-4">
                                <input type="radio" class="d-none tipo-radio" name="tipo_contenido" id="tipo_imagen" value="imagen" checked onchange="cambiarTipoContenido('imagen')">
                                <label for="tipo_imagen" class="tipo-card-option w-100">
                                    <i class="fas fa-image fs-3 mb-2 d-block text-muted"></i>
                                    <span class="d-block fw-semibold small text-dark">Imagen</span>
                                    <span class="d-block text-muted" style="font-size: 0.72rem;">JPG o PNG</span>
                                </label>
                            </div>

                            <div class="col-4">
                                <input type="radio" class="d-none tipo-radio" name="tipo_contenido" id="tipo_audio" value="audio" onchange="cambiarTipoContenido('audio')">
                                <label for="tipo_audio" class="tipo-card-option w-100">
                                    <i class="fas fa-headphones fs-3 mb-2 d-block text-muted"></i>
                                    <span class="d-block fw-semibold small text-dark">Audio</span>
                                    <span class="d-block text-muted" style="font-size: 0.72rem;">MP3, WAV (máx 10MB)</span>
                                </label>
                            </div>

                            <div class="col-4">
                                <input type="radio" class="d-none tipo-radio" name="tipo_contenido" id="tipo_video" value="video" onchange="cambiarTipoContenido('video')">
                                <label for="tipo_video" class="tipo-card-option w-100">
                                    <i class="fab fa-youtube fs-3 mb-2 d-block text-muted"></i>
                                    <span class="d-block fw-semibold small text-dark">Video</span>
                                    <span class="d-block text-muted" style="font-size: 0.72rem;">YouTube o Vimeo</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="seccion_imagen" class="seccion-multimedia p-3 rounded-4 bg-light border mb-3">
                        <label for="campo_archivo_imagen" class="form-label fw-bold font-outfit text-dark small mb-1">
                            Seleccionar Imagen <span id="req_imagen" class="text-danger">*</span>
                        </label>
                        <input type="file"
                               name="archivo_imagen"
                               id="campo_archivo_imagen"
                               class="form-control rounded-pill"
                               accept=".jpg,.jpeg,.png"
                               onchange="previsualizarImagen(event)">

                        <div class="form-text small">Formatos válidos: solo <strong>JPG, JPEG o PNG</strong>. Tamaño recomendado hasta 5 MB.</div>

                        <div id="contenedor_preview_imagen" class="mt-3 text-center d-none">
                            <span class="d-block text-muted small mb-2 fw-semibold">Vista previa de la imagen:</span>
                            <img id="img_preview" src="" alt="Vista previa" class="img-fluid rounded-3 shadow-sm" style="max-height: 220px; object-fit: cover; border: 1px solid #cbd5e1;">
                        </div>

                        <div id="nota_imagen_actual" class="mt-2 text-muted small d-none">
                            <i class="fas fa-check text-success me-1"></i> Ya cuenta con una imagen guardada. Si seleccionas otra, será reemplazada.
                        </div>
                    </div>

                    <div id="seccion_audio" class="seccion-multimedia p-3 rounded-4 bg-light border mb-3 d-none">
                        <label for="campo_archivo_audio" class="form-label fw-bold font-outfit text-dark small mb-1">
                            Seleccionar Archivo de Audio <span id="req_audio" class="text-danger">*</span>
                        </label>
                        <input type="file"
                               name="archivo_audio"
                               id="campo_archivo_audio"
                               class="form-control rounded-pill"
                               accept=".mp3,.wav,.ogg"
                               onchange="previsualizarAudio(event)">

                        <div class="form-text small">Formatos válidos: <strong>MP3, WAV u OGG</strong>. Tamaño máximo permitido: <strong>10 MB</strong>.</div>

                        <div id="contenedor_preview_audio" class="mt-3 p-3 bg-white rounded-3 border d-none">
                            <span class="d-block text-muted small mb-2 fw-semibold">
                                <i class="fas fa-headphones text-success me-1"></i> Vista previa del reproductor:
                            </span>
                            <audio id="audio_preview" controls class="w-100">
                                Tu navegador no soporta reproducción de audio.
                            </audio>
                        </div>

                        <div id="nota_audio_actual" class="mt-2 text-muted small d-none">
                            <i class="fas fa-check text-success me-1"></i> Ya cuenta con un audio guardado. Si cargas uno nuevo, reemplazará al anterior.
                        </div>
                    </div>

                    <div id="seccion_video" class="seccion-multimedia p-3 rounded-4 bg-light border mb-3 d-none">
                        <label for="campo_url_video" class="form-label fw-bold font-outfit text-dark small mb-1">
                            URL del Video (YouTube o Vimeo) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3">
                                <i class="fas fa-link text-muted"></i>
                            </span>
                            <input type="url"
                                   name="url_video"
                                   id="campo_url_video"
                                   class="form-control rounded-end-pill border-start-0 pe-3"
                                   placeholder="https://www.youtube.com/watch?v=... o https://vimeo.com/..."
                                   oninput="previsualizarVideo()">

                        </div>
                        <div class="form-text small">
                            <i class="fab fa-youtube text-danger me-1"></i> El video <strong>no se almacena en el sistema</strong>; se reproduce incrustado (embed).
                        </div>

                        <div id="contenedor_preview_video" class="mt-3 d-none">
                            <span class="d-block text-muted small mb-2 fw-semibold">
                                <i class="fas fa-video text-primary me-1"></i> Vista previa incrustada:
                            </span>
                            <div class="ratio ratio-16x9 rounded-3 overflow-hidden shadow-sm border bg-dark">
                                <iframe id="video_preview_iframe" src="" allowfullscreen></iframe>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" id="btnGuardarBloque" class="btn btn-ecp-primary px-4 d-flex align-items-center gap-2">
                        <i class="fas fa-save"></i>
                        <span id="btnGuardarBloqueText">Guardar Bloque</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const RUTA_BASE_HISTORIA = "{{ url('control-ecp/administrador/repositorio/historia') }}";
    /* =========================================================================
       2. MODAL AGREGAR / EDITAR BLOQUE
       ========================================================================= */
    const modalBloqueEl = document.getElementById('modalBloque');
    const modalBloqueInstancia = new bootstrap.Modal(modalBloqueEl);

    function abrirModalCrear() {
        document.getElementById('formBloque').reset();
        document.getElementById('formBloque').action = RUTA_BASE_HISTORIA;
        document.getElementById('formBloqueMethod').value = 'POST';
        document.getElementById('bloqueIdEditando').value = '';

        document.getElementById('modalBloqueTitle').textContent = 'Agregar Nuevo Bloque';
        document.getElementById('modalBloqueIcon').className = 'fas fa-plus-circle fs-5';
        document.getElementById('btnGuardarBloqueText').textContent = 'Guardar y Publicar';

        // Requerimientos en creación
        document.getElementById('req_imagen').classList.remove('d-none');
        document.getElementById('req_audio').classList.remove('d-none');
        document.getElementById('nota_imagen_actual').classList.add('d-none');
        document.getElementById('nota_audio_actual').classList.add('d-none');

        // Limpiar vistas previas
        limpiarPreviews();

        // Por defecto: tipo imagen
        document.getElementById('tipo_imagen').checked = true;
        cambiarTipoContenido('imagen');

        modalBloqueInstancia.show();
    }

    function abrirModalEditar(bloque) {
        document.getElementById('formBloque').reset();
        document.getElementById('formBloque').action = `${RUTA_BASE_HISTORIA}/${bloque.id}`;
        document.getElementById('formBloqueMethod').value = 'PUT';
        document.getElementById('bloqueIdEditando').value = bloque.id;

        document.getElementById('modalBloqueTitle').textContent = 'Editar Bloque de Historia';
        document.getElementById('modalBloqueIcon').className = 'fas fa-pen-to-square fs-5';
        document.getElementById('btnGuardarBloqueText').textContent = 'Actualizar Cambios';

        // Llenar campos
        document.getElementById('campo_titulo').value = bloque.titulo || '';
        document.getElementById('campo_texto').value = bloque.texto || '';

        // Limpiar previews
        limpiarPreviews();

        // En edición, los archivos son opcionales si ya existen
        document.getElementById('req_imagen').classList.add('d-none');
        document.getElementById('req_audio').classList.add('d-none');

        // Seleccionar tipo de contenido
        const tipo = bloque.tipo_contenido || 'imagen';
        const radio = document.getElementById('tipo_' + tipo);
        if (radio) radio.checked = true;
        cambiarTipoContenido(tipo);

        // Previsualizar según tipo existente
        if (tipo === 'imagen') {
            if (bloque.ruta_archivo) {
                document.getElementById('nota_imagen_actual').classList.remove('d-none');
                const urlImg = `/storage/${bloque.ruta_archivo}`;
                document.getElementById('img_preview').src = urlImg;
                document.getElementById('contenedor_preview_imagen').classList.remove('d-none');
            }
        } else if (tipo === 'audio') {
            if (bloque.ruta_archivo) {
                document.getElementById('nota_audio_actual').classList.remove('d-none');
                const urlAudio = `/storage/${bloque.ruta_archivo}`;
                const audioEl = document.getElementById('audio_preview');
                audioEl.src = urlAudio;
                document.getElementById('contenedor_preview_audio').classList.remove('d-none');
            }
        } else if (tipo === 'video') {
            document.getElementById('campo_url_video').value = bloque.url_video || '';
            previsualizarVideo();
        }

        modalBloqueInstancia.show();
    }

    // Cambiar la sección multimedia según el tipo
    function cambiarTipoContenido(tipo) {
        const secImg = document.getElementById('seccion_imagen');
        const secAud = document.getElementById('seccion_audio');
        const secVid = document.getElementById('seccion_video');

        secImg.classList.add('d-none');
        secAud.classList.add('d-none');
        secVid.classList.add('d-none');

        if (tipo === 'imagen') {
            secImg.classList.remove('d-none');
        } else if (tipo === 'audio') {
            secAud.classList.remove('d-none');
        } else if (tipo === 'video') {
            secVid.classList.remove('d-none');
            previsualizarVideo();
        }
    }

    function limpiarPreviews() {
        document.getElementById('contenedor_preview_imagen').classList.add('d-none');
        document.getElementById('img_preview').src = '';

        document.getElementById('contenedor_preview_audio').classList.add('d-none');
        const audioEl = document.getElementById('audio_preview');
        audioEl.pause();
        audioEl.src = '';

        document.getElementById('contenedor_preview_video').classList.add('d-none');
        document.getElementById('video_preview_iframe').src = '';
    }

    /* =========================================================================
       3. VISTAS PREVIAS INTERACTIVAS & VALIDACIONES FRONTEND
       ========================================================================= */
    function previsualizarImagen(event) {
        const file = event.target.files[0];
        if (!file) return;

        // Validar formato
        const ext = file.name.split('.').pop().toLowerCase();
        if (!['jpg', 'jpeg', 'png'].includes(ext)) {
            Swal.fire({
                icon: 'warning',
                title: 'Formato no permitido',
                text: 'Solo se permiten imágenes en formato JPG o PNG.',
                confirmButtonColor: 'var(--petroleo-ecp)'
            });
            event.target.value = '';
            document.getElementById('contenedor_preview_imagen').classList.add('d-none');
            return;
        }

        // Validar tamaño máx (5MB)
        if (file.size > 5 * 1024 * 1024) {
            Swal.fire({
                icon: 'warning',
                title: 'Imagen muy pesada',
                text: 'La imagen no debe superar los 5 MB.',
                confirmButtonColor: 'var(--petroleo-ecp)'
            });
            event.target.value = '';
            document.getElementById('contenedor_preview_imagen').classList.add('d-none');
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('img_preview').src = e.target.result;
            document.getElementById('contenedor_preview_imagen').classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    }

    function previsualizarAudio(event) {
        const file = event.target.files[0];
        if (!file) return;

        // Validar formato
        const ext = file.name.split('.').pop().toLowerCase();
        if (!['mp3', 'wav', 'ogg'].includes(ext)) {
            Swal.fire({
                icon: 'warning',
                title: 'Formato no permitido',
                text: 'El archivo de audio debe ser MP3, WAV u OGG.',
                confirmButtonColor: 'var(--petroleo-ecp)'
            });
            event.target.value = '';
            document.getElementById('contenedor_preview_audio').classList.add('d-none');
            return;
        }

        // Validar tamaño máximo 10 MB (10 * 1024 * 1024 = 10485760 bytes)
        if (file.size > 10 * 1024 * 1024) {
            Swal.fire({
                icon: 'error',
                title: 'Audio excede el tamaño máximo',
                text: `El archivo pesa ${(file.size / (1024 * 1024)).toFixed(2)} MB. El límite máximo permitido es de 10 MB.`,
                confirmButtonColor: 'var(--petroleo-ecp)'
            });
            event.target.value = '';
            document.getElementById('contenedor_preview_audio').classList.add('d-none');
            return;
        }

        const url = URL.createObjectURL(file);
        const audioEl = document.getElementById('audio_preview');
        audioEl.src = url;
        document.getElementById('contenedor_preview_audio').classList.remove('d-none');
    }

    function previsualizarVideo() {
        const urlInput = document.getElementById('campo_url_video').value.trim();
        const contenedor = document.getElementById('contenedor_preview_video');
        const iframe = document.getElementById('video_preview_iframe');

        if (!urlInput) {
            contenedor.classList.add('d-none');
            iframe.src = '';
            return;
        }

        const embedUrl = extraerEmbedVideo(urlInput);
        if (embedUrl) {
            iframe.src = embedUrl;
            contenedor.classList.remove('d-none');
        } else {
            contenedor.classList.add('d-none');
            iframe.src = '';
        }
    }

    function extraerEmbedVideo(url) {
        // YouTube
        const ytMatch = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=|shorts\/))([\w-]{11})/i);
        if (ytMatch && ytMatch[1]) {
            return `https://www.youtube-nocookie.com/embed/${ytMatch[1]}`;
        }
        // Vimeo
        const vmMatch = url.match(/(?:vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/[^\/]*\/videos\/|album\/\d+\/video\/|video\/|)(\d+))/i);
        if (vmMatch && vmMatch[1]) {
            return `https://player.vimeo.com/video/${vmMatch[1]}`;
        }
        return null;
    }

    // Validación antes de enviar el formulario
    document.getElementById('formBloque').addEventListener('submit', function (e) {
        const titulo = document.getElementById('campo_titulo').value.trim();
        const texto = document.getElementById('campo_texto').value.trim();
        const tipo = document.querySelector('input[name="tipo_contenido"]:checked')?.value || 'imagen';
        const isEditing = Boolean(document.getElementById('bloqueIdEditando').value);

        if (!titulo || !texto) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Campos obligatorios',
                text: 'El título y el texto del bloque no pueden estar vacíos.',
                confirmButtonColor: 'var(--petroleo-ecp)'
            });
            return;
        }

        if (tipo === 'imagen') {
            const file = document.getElementById('campo_archivo_imagen').files[0];
            const tieneActual = !document.getElementById('nota_imagen_actual').classList.contains('d-none');
            if (!file && !isEditing && !tieneActual) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Imagen requerida',
                    text: 'Debes seleccionar una imagen para el bloque.',
                    confirmButtonColor: 'var(--petroleo-ecp)'
                });
                return;
            }
        } else if (tipo === 'audio') {
            const file = document.getElementById('campo_archivo_audio').files[0];
            const tieneActual = !document.getElementById('nota_audio_actual').classList.contains('d-none');
            if (!file && !isEditing && !tieneActual) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Audio requerido',
                    text: 'Debes seleccionar un archivo de audio para el bloque.',
                    confirmButtonColor: 'var(--petroleo-ecp)'
                });
                return;
            }
        } else if (tipo === 'video') {
            const url = document.getElementById('campo_url_video').value.trim();
            if (!url) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Enlace requerido',
                    text: 'Debes ingresar el enlace de YouTube o Vimeo.',
                    confirmButtonColor: 'var(--petroleo-ecp)'
                });
                return;
            }
            if (!extraerEmbedVideo(url)) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Enlace no válido',
                    text: 'El enlace debe pertenecer a una URL válida de YouTube o Vimeo.',
                    confirmButtonColor: 'var(--petroleo-ecp)'
                });
                return;
            }
        }

        // Deshabilitar botón para evitar envíos dobles
        const btn = document.getElementById('btnGuardarBloque');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Guardando...';
    });

    /* =========================================================================
       5. UTILIDADES
       ========================================================================= */
    function mostrarToast(mensaje, tipo = 'success') {
        const toastEl = document.getElementById('toastAjax');
        const msgEl = document.getElementById('toastAjaxMsg');
        const iconEl = document.getElementById('toastAjaxIcon');

        if (!toastEl) return;

        msgEl.textContent = mensaje;
        if (tipo === 'success') {
            toastEl.style.backgroundColor = 'var(--verde-ecp)';
            iconEl.className = 'fas fa-check-circle fs-5';
        } else if (tipo === 'error') {
            toastEl.style.backgroundColor = '#dc2626';
            iconEl.className = 'fas fa-exclamation-triangle fs-5';
        } else {
            toastEl.style.backgroundColor = 'var(--petroleo-ecp-medio)';
            iconEl.className = 'fas fa-info-circle fs-5';
        }

        const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
        toast.show();
    }
</script>
@endpush
@endsection
