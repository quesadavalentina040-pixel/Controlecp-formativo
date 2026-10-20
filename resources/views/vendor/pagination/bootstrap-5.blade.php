@if ($paginator->hasPages())
<nav aria-label="Paginación" class="controlecp-pagination">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">

        {{-- Info de resultados --}}
        <p class="small text-muted mb-0">
            <i class="fas fa-layer-group me-1" style="color:#39A900;"></i>
            Mostrando
            <strong style="color:#001A29;">{{ $paginator->firstItem() }}</strong>
            –
            <strong style="color:#001A29;">{{ $paginator->lastItem() }}</strong>
            de
            <strong style="color:#001A29;">{{ $paginator->total() }}</strong>
            resultados
        </p>

        {{-- Controles de paginación --}}
        <ul class="pagination pagination-sm mb-0" style="gap:4px;">

            {{-- Botón « Primera --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link rounded-pill border-0 text-muted px-3"
                          style="background:#f8f9fa;">
                        <i class="fas fa-angles-left"></i>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link rounded-pill border-0 px-3"
                       href="{{ $paginator->url(1) }}"
                       style="color:#39A900;background:#f0fbf4;"
                       title="Primera página">
                        <i class="fas fa-angles-left"></i>
                    </a>
                </li>
            @endif

            {{-- Botón ‹ Anterior --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link rounded-pill border-0 px-3"
                          style="background:#f8f9fa;color:#adb5bd;">
                        <i class="fas fa-angle-left me-1"></i> Anterior
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link rounded-pill border-0 px-3"
                       href="{{ $paginator->previousPageUrl() }}"
                       style="color:#39A900;background:#f0fbf4;">
                        <i class="fas fa-angle-left me-1"></i> Anterior
                    </a>
                </li>
            @endif

            {{-- Números de página --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled">
                        <span class="page-link rounded-pill border-0 px-3"
                              style="background:#f8f9fa;color:#adb5bd;">
                            {{ $element }}
                        </span>
                    </li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active">
                                <span class="page-link rounded-pill border-0 px-3 fw-semibold"
                                      style="background:#39A900;color:#fff;box-shadow:0 2px 8px #39A90040;">
                                    {{ $page }}
                                </span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link rounded-pill border-0 px-3"
                                   href="{{ $url }}"
                                   style="color:#001A29;background:#f8f9fa;">
                                    {{ $page }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Botón Siguiente › --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link rounded-pill border-0 px-3"
                       href="{{ $paginator->nextPageUrl() }}"
                       style="color:#39A900;background:#f0fbf4;">
                        Siguiente <i class="fas fa-angle-right ms-1"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link rounded-pill border-0 px-3"
                          style="background:#f8f9fa;color:#adb5bd;">
                        Siguiente <i class="fas fa-angle-right ms-1"></i>
                    </span>
                </li>
            @endif

            {{-- Botón Última » --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link rounded-pill border-0 px-3"
                       href="{{ $paginator->url($paginator->lastPage()) }}"
                       style="color:#39A900;background:#f0fbf4;"
                       title="Última página">
                        <i class="fas fa-angles-right"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link rounded-pill border-0 px-3"
                          style="background:#f8f9fa;color:#adb5bd;">
                        <i class="fas fa-angles-right"></i>
                    </span>
                </li>
            @endif

        </ul>
    </div>
</nav>

<style>
.controlecp-pagination .page-link {
    transition: background .18s, color .18s, box-shadow .18s;
    font-size: .82rem;
    line-height: 1.6;
}
.controlecp-pagination .page-link:hover {
    background: #d8f3dc !important;
    color: #001A29 !important;
    box-shadow: 0 2px 6px #39A90030;
}
.controlecp-pagination .page-item.active .page-link:hover {
    background: #2d8500 !important;
    color: #fff !important;
}
.controlecp-pagination .pagination {
    flex-wrap: wrap;
}
</style>
@endif
