@extends('controlecp::components.layouts.dashboard')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --ecp-green:   #39A900;
        --ecp-green-d: #2e8800;
        --ecp-navy:    #001A29;
        --ecp-navy-l:  #00324D;
        --ecp-blue:    #0070B8;
        --ecp-purple:  #6A1B9A;
    }
    .act-page { font-family: 'Outfit', sans-serif; }

    /* KPI Strip */
    .kpi-strip {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 1rem;
        margin-bottom: 1.75rem;
    }
    .kpi-card {
        background: #fff;
        border-radius: 18px;
        padding: 1.25rem 1rem;
        box-shadow: 0 4px 18px rgba(0,26,41,.07);
        border: 1px solid rgba(0,0,0,.04);
        display: flex;
        flex-direction: column;
        gap: .35rem;
        transition: transform .2s, box-shadow .2s;
    }
    .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 8px 28px rgba(0,26,41,.12); }
    .kpi-icon-wrap {
        width: 42px; height: 42px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; margin-bottom: .25rem;
    }
    .kpi-value { font-size: 1.8rem; font-weight: 800; line-height: 1; color: var(--ecp-navy); }
    .kpi-label { font-size: .78rem; color: #7a8394; font-weight: 500; }

    /* Momento progress bars */
    .momento-progress-block { background: #fff; border-radius: 18px; box-shadow: 0 4px 18px rgba(0,26,41,.07); padding: 1.4rem; margin-bottom: 1.75rem; }
    .mp-bar { height: 9px; border-radius: 6px; background: #edf0f3; overflow: hidden; }
    .mp-fill { height: 100%; border-radius: 6px; transition: width 1s ease; }

    /* Filter bar */
    .filter-bar {
        background: #fff;
        border-radius: 16px;
        padding: .85rem 1.1rem;
        box-shadow: 0 3px 14px rgba(0,26,41,.06);
        display: flex;
        flex-wrap: wrap;
        gap: .65rem;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    .filter-search {
        flex: 1;
        min-width: 200px;
        border: 1.5px solid #e3e7ee;
        border-radius: 10px;
        padding: .55rem 1rem;
        font-size: .9rem;
        outline: none;
        transition: border-color .2s;
        font-family: 'Outfit', sans-serif;
    }
    .filter-search:focus { border-color: var(--ecp-green); }
    .pill-filter {
        padding: .42rem 1.1rem;
        border-radius: 50px;
        font-size: .8rem;
        font-weight: 600;
        border: 1.5px solid #dde1ea;
        background: #f5f7fb;
        color: #555;
        cursor: pointer;
        transition: all .18s;
    }
    .pill-filter.active, .pill-filter:hover { color: #fff; }
    .pill-filter.all.active, .pill-filter.all:hover   { background: var(--ecp-navy); border-color: var(--ecp-navy); }
    .pill-filter.m1.active,  .pill-filter.m1:hover    { background: var(--ecp-green); border-color: var(--ecp-green); }
    .pill-filter.m2.active,  .pill-filter.m2:hover    { background: var(--ecp-blue); border-color: var(--ecp-blue); }
    .pill-filter.m3.active,  .pill-filter.m3:hover    { background: var(--ecp-purple); border-color: var(--ecp-purple); }
    .results-count { font-size: .82rem; color: #888; font-weight: 500; margin-left: auto; white-space: nowrap; }

    /* Activity cards */
    .act-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 1.2rem;
    }
    .act-card {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 4px 18px rgba(0,26,41,.06);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform .22s, box-shadow .22s;
        border: 1px solid rgba(0,0,0,.04);
    }
    .act-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,26,41,.13); }
    .act-card-header {
        padding: .9rem 1.1rem .75rem;
        display: flex;
        align-items: center;
        gap: .75rem;
        border-bottom: 1px solid rgba(0,0,0,.05);
    }
    .act-num-badge {
        width: 38px; height: 38px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: .85rem; font-weight: 800; color: #fff; flex-shrink: 0;
    }
    .act-card-title { font-size: .98rem; font-weight: 700; color: var(--ecp-navy); line-height: 1.3; }
    .act-momento-badge {
        font-size: .7rem; font-weight: 600; padding: .2rem .6rem;
        border-radius: 50px; letter-spacing: .02em; display: inline-block; margin-top: .2rem;
    }
    .act-card-body { padding: .9rem 1.1rem; flex: 1; display: flex; flex-direction: column; gap: .6rem; }
    .act-obj-text { font-size: .83rem; color: #555; line-height: 1.5; }
    .act-tags { display: flex; flex-wrap: wrap; gap: .35rem; }
    .act-tag {
        font-size: .7rem; padding: .18rem .6rem; border-radius: 50px;
        background: #f0f3f8; color: #607080; font-weight: 500; border: 1px solid #e5e9f0;
    }
    .act-card-footer {
        padding: .75rem 1.1rem;
        border-top: 1px solid rgba(0,0,0,.05);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .act-dur-badge {
        font-size: .78rem; font-weight: 600; padding: .3rem .75rem;
        border-radius: 50px; background: #f0f3f8; color: #536070; border: 1px solid #e0e5ec;
        display: flex; align-items: center; gap: .35rem;
    }
    .btn-guia {
        font-size: .8rem; font-weight: 600; padding: .35rem .9rem;
        border-radius: 50px; color: #fff; border: none; cursor: pointer;
        transition: filter .15s, transform .15s;
        font-family: 'Outfit', sans-serif;
    }
    .btn-guia:hover { filter: brightness(1.12); transform: scale(1.03); }

    /* Empty state */
    .act-empty { display: none; text-align: center; padding: 3rem 2rem; color: #aaa; }
    .act-empty.show { display: block; }

    /* Modal */
    #modalGuiaPedagogica .modal-content { border-radius: 22px; border: 0; }
    .guia-header-band {
        border-radius: 22px 22px 0 0;
        padding: 1.5rem 1.75rem 1.25rem;
        color: #fff;
    }
    .guia-section {
        background: #f8fafc;
        border: 1px solid #edf0f5;
        border-radius: 12px;
        padding: 1rem 1.1rem;
        margin-bottom: .9rem;
    }
    .guia-section-title {
        font-size: .78rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .08em; margin-bottom: .5rem; display: flex; align-items: center; gap: .4rem;
    }
    .competencia-pill {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .75rem; padding: .28rem .7rem; border-radius: 50px;
        font-weight: 600; border: 1.5px solid;
    }
    .material-item {
        font-size: .82rem; color: #444;
        display: flex; align-items: flex-start; gap: .45rem;
        padding: .22rem 0;
    }

    /* API Banner */
    .api-banner {
        background: linear-gradient(135deg, #001A29 0%, #002D4A 100%);
        border-radius: 14px;
        padding: .8rem 1.2rem;
        display: flex;
        align-items: center;
        gap: .75rem;
        margin-bottom: 1.5rem;
        font-size: .82rem;
        color: rgba(255,255,255,.85);
    }
    .api-dot { width: 8px; height: 8px; border-radius: 50%; background: #f59e0b; animation: pulse-dot 2s infinite; flex-shrink: 0; }
    @keyframes pulse-dot {
        0%,100% { opacity: 1; transform: scale(1); }
        50%      { opacity: .5; transform: scale(1.3); }
    }

    @media (max-width: 576px) {
        .act-grid { grid-template-columns: 1fr; }
        .kpi-strip { grid-template-columns: repeat(2, 1fr); }
    }

    /* ── Paginación ── */
    .pag-wrap {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin-top: 1.5rem;
        padding: .9rem 1.2rem;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 3px 14px rgba(0,26,41,.06);
    }
    .pag-info { font-size: .82rem; color: #7a8394; font-weight: 500; }
    .pag-info strong { color: var(--ecp-navy); }
    .pag-controls { display: flex; align-items: center; gap: .35rem; flex-wrap: wrap; }
    .pag-btn {
        min-width: 34px; height: 34px;
        border-radius: 9px;
        border: 1.5px solid #e0e5ec;
        background: #f8fafc;
        color: #536070;
        font-size: .82rem;
        font-weight: 600;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: all .18s;
        font-family: 'Outfit', sans-serif;
        padding: 0 .55rem;
    }
    .pag-btn:hover:not(:disabled) { border-color: var(--ecp-green); color: var(--ecp-green); background: #f0fbf4; }
    .pag-btn.active { background: var(--ecp-green); border-color: var(--ecp-green); color: #fff; }
    .pag-btn:disabled { opacity: .4; cursor: default; }
    .pag-sep { font-size: .82rem; color: #bbb; padding: 0 .15rem; }
    .pag-per-page {
        font-size: .8rem; font-weight: 600; color: #536070;
        border: 1.5px solid #e0e5ec; border-radius: 9px;
        background: #f8fafc; padding: .3rem .6rem;
        cursor: pointer; outline: none; font-family: 'Outfit', sans-serif;
        transition: border-color .18s;
    }
    .pag-per-page:focus { border-color: var(--ecp-green); }
</style>
@endpush

@section('dashboard-content')
<div class="act-page container-fluid px-2 px-md-3 py-2">

    {{-- Breadcrumb & Cabecera --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.administrador.inicio') }}" class="text-decoration-none" style="color:var(--ecp-green);">
                            <i class="fas fa-house me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item active" style="color:var(--ecp-navy);font-weight:600;">Actividades</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color:var(--ecp-navy);">
                <i class="fas fa-list-check me-2" style="color:var(--ecp-green);"></i>Catálogo de Actividades
            </h2>
            <p class="text-muted small mb-0">
                Control ECP &bull;
                <strong>{{ $statsActividades['total'] }}</strong> actividades &bull;
                <strong>{{ $statsActividades['total_horas'] }} h</strong> formativas totales
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button id="btnExportCsv" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
                <i class="fas fa-file-csv me-1"></i> Exportar CSV
            </button>
            <a href="{{ route('controlecp.administrador.momentos') }}" class="btn text-white rounded-pill px-3 btn-sm" style="background:var(--ecp-green);">
                <i class="fas fa-star me-1"></i> Ver Momentos
            </a>
            <a href="{{ route('controlecp.administrador.inicio') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Inicio
            </a>
        </div>
    </div>

    {{-- API Banner --}}
    <div class="api-banner">
        <span class="api-dot"></span>
        <div>
            <strong style="color:#fff;">Listo para integración SICEFA:</strong>
            Las {{ $statsActividades['total'] }} actividades se gestionarán vía
            <code style="color:#62E31D;font-size:.8rem;">GET /api/v1/controlecp/actividades</code>
            &mdash; ERP SENA Empresa / SICEFA · Centro La Angostura
        </div>
    </div>

    {{-- KPI Strip --}}
    <div class="kpi-strip">
        <div class="kpi-card">
            <div class="kpi-icon-wrap" style="background:rgba(57,169,0,.12);">
                <i class="fas fa-list-check" style="color:var(--ecp-green);"></i>
            </div>
            <div class="kpi-value" style="color:var(--ecp-green);">{{ $statsActividades['total'] }}</div>
            <div class="kpi-label">Actividades Pedagógicas</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon-wrap" style="background:rgba(0,26,41,.08);">
                <i class="fas fa-clock" style="color:var(--ecp-navy);"></i>
            </div>
            <div class="kpi-value">{{ $statsActividades['total_horas'] }}<span style="font-size:1rem;font-weight:600;">h</span></div>
            <div class="kpi-label">Horas Formativas Totales</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon-wrap" style="background:rgba(0,112,184,.1);">
                <i class="fas fa-id-card-clip" style="color:var(--ecp-blue);"></i>
            </div>
            <div class="kpi-value" style="color:var(--ecp-blue);">{{ $statsActividades['fichas_activas'] }}</div>
            <div class="kpi-label">Fichas Activas en ECP</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon-wrap" style="background:rgba(106,27,154,.1);">
                <i class="fas fa-users" style="color:var(--ecp-purple);"></i>
            </div>
            <div class="kpi-value" style="color:var(--ecp-purple);">{{ number_format($statsActividades['aprendices']) }}</div>
            <div class="kpi-label">Aprendices Participando</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon-wrap" style="background:rgba(57,169,0,.08);">
                <i class="fas fa-star" style="color:var(--ecp-green);"></i>
            </div>
            <div class="kpi-value">{{ $statsActividades['total_momentos'] }}</div>
            <div class="kpi-label">Momentos Pedagógicos</div>
        </div>
    </div>

    {{-- Barra de progreso por Momento --}}
    <div class="momento-progress-block">
        <h6 class="fw-bold mb-3" style="color:var(--ecp-navy);font-size:.9rem;">
            <i class="fas fa-chart-bar me-2" style="color:var(--ecp-green);"></i>
            Distribución por Momento Pedagógico
        </h6>
        <div class="row g-3">
            @php
                $momentosInfo = [
                    ['id'=>1,'label'=>'Momento 1: Autoconciencia','color'=>'#39A900','icon'=>'fa-brain',
                     'count'=>$statsActividades['momento1_count'],'horas'=>$statsActividades['horas_m1']],
                    ['id'=>2,'label'=>'Momento 2: Convivencia','color'=>'#0070B8','icon'=>'fa-people-holding-hands',
                     'count'=>$statsActividades['momento2_count'],'horas'=>$statsActividades['horas_m2']],
                    ['id'=>3,'label'=>'Momento 3: Resolución','color'=>'#6A1B9A','icon'=>'fa-scale-balanced',
                     'count'=>$statsActividades['momento3_count'],'horas'=>$statsActividades['horas_m3']],
                ];
            @endphp
            @foreach($momentosInfo as $m)
            <div class="col-12 col-md-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold" style="color:{{ $m['color'] }};">
                        <i class="fas {{ $m['icon'] }} me-1"></i>{{ $m['label'] }}
                    </span>
                    <span class="small text-muted">{{ $m['count'] }} act &bull; {{ $m['horas'] }}h</span>
                </div>
                <div class="mp-bar">
                    <div class="mp-fill" data-width="{{ ($m['count'] / $statsActividades['total']) * 100 }}" style="width:0%;background:{{ $m['color'] }};"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Barra de Filtros --}}
    <div class="filter-bar">
        <i class="fas fa-magnifying-glass text-muted" style="font-size:.9rem;"></i>
        <input type="text" id="actSearch" class="filter-search" placeholder="Buscar actividad por nombre, objetivo, etiqueta...">
        <button class="pill-filter all active" data-filter="all">
            <i class="fas fa-th me-1"></i> Todas ({{ $statsActividades['total'] }})
        </button>
        <button class="pill-filter m1" data-filter="1">
            <i class="fas fa-brain me-1"></i> Autoconciencia ({{ $statsActividades['momento1_count'] }})
        </button>
        <button class="pill-filter m2" data-filter="2">
            <i class="fas fa-people-holding-hands me-1"></i> Convivencia ({{ $statsActividades['momento2_count'] }})
        </button>
        <button class="pill-filter m3" data-filter="3">
            <i class="fas fa-scale-balanced me-1"></i> Resolucion ({{ $statsActividades['momento3_count'] }})
        </button>
        <span class="results-count" id="resultsCount">{{ $statsActividades['total'] }} resultados</span>
    </div>

    {{-- Grilla de Tarjetas --}}
    <div class="act-grid" id="actGrid">
        @foreach($actividades as $act)
        @php
            $colorMap = [1=>'#39A900', 2=>'#0070B8', 3=>'#6A1B9A'];
            $bgMap    = [1=>'rgba(57,169,0,.1)', 2=>'rgba(0,112,184,.1)', 3=>'rgba(106,27,154,.1)'];
            $c        = $colorMap[$act['momento_id']];
            $bg       = $bgMap[$act['momento_id']];
        @endphp
        <div class="act-card"
             data-momento="{{ $act['momento_id'] }}"
             data-text="{{ strtolower($act['nombre'].' '.$act['objetivo'].' '.implode(' ',$act['tags'])) }}">
            <div class="act-card-header">
                <div class="act-num-badge" style="background:{{ $c }};">{{ $act['numero'] }}</div>
                <div style="min-width:0;">
                    <div class="act-card-title">{{ $act['nombre'] }}</div>
                    <span class="act-momento-badge" style="background:{{ $bg }};color:{{ $c }};">
                        <i class="fas {{ $act['momento_icon'] }} me-1"></i>{{ $act['momento'] }}
                    </span>
                </div>
            </div>
            <div class="act-card-body">
                <p class="act-obj-text mb-0">{{ $act['objetivo'] }}</p>
                <div class="act-tags">
                    @foreach($act['tags'] as $tag)
                    <span class="act-tag">#{{ $tag }}</span>
                    @endforeach
                    @foreach($act['competencias'] as $comp)
                    <span class="act-tag" style="background:{{ $bg }};border-color:{{ $c }}40;color:{{ $c }};">{{ $comp }}</span>
                    @endforeach
                </div>
            </div>
            <div class="act-card-footer">
                <div class="act-dur-badge">
                    <i class="far fa-clock" style="color:{{ $c }};"></i>
                    {{ $act['duracion'] }}
                </div>
                <button class="btn-guia" style="background:{{ $c }};" onclick="abrirGuia({{ $act['id'] }})">
                    <i class="fas fa-book-open me-1"></i> Ver Guia
                </button>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Estado vacio --}}
    <div class="act-empty" id="actEmpty">
        <i class="fas fa-magnifying-glass fa-3x mb-3" style="color:#d5dae2;"></i>
        <h6 class="fw-bold" style="color:#8a93a0;">Sin resultados para esa busqueda</h6>
        <p class="small text-muted">Intenta con otro termino o limpia los filtros.</p>
        <button class="btn btn-sm btn-outline-secondary rounded-pill px-4 mt-1" onclick="resetFiltros()">
            <i class="fas fa-xmark me-1"></i> Limpiar filtros
        </button>
    </div>

    {{-- Paginacion --}}
    <div class="pag-wrap" id="pagWrap" style="display:none;">
        <div class="pag-info" id="pagInfo">Mostrando <strong id="pagFrom">1</strong>–<strong id="pagTo">6</strong> de <strong id="pagTotal">18</strong> actividades</div>
        <div class="pag-controls" id="pagControls"></div>
        <div class="d-flex align-items-center gap-2">
            <span class="pag-info">Por pagina:</span>
            <select class="pag-per-page" id="pagPerPage">
                <option value="6">6</option>
                <option value="9">9</option>
                <option value="18" selected>18</option>
            </select>
        </div>
    </div>

</div>

{{-- ====================== MODAL GUIA PEDAGOGICA ====================== --}}
<div class="modal fade" id="modalGuiaPedagogica" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="guia-header-band" id="guiaHeaderBand">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span id="guiaMomentoBadge" class="badge rounded-pill px-3 py-1" style="background:rgba(255,255,255,.2);font-size:.78rem;">Momento</span>
                            <span id="guiaNumBadge" class="badge rounded-pill px-2 py-1" style="background:rgba(255,255,255,.15);font-size:.76rem;">Actividad #01</span>
                        </div>
                        <h5 class="fw-bold mb-1 fs-5" id="guiaTitulo">Nombre de la Actividad</h5>
                        <div style="opacity:.85;font-size:.83rem;">
                            <i class="far fa-clock me-1"></i><span id="guiaDuracion">2 Horas</span>
                            &nbsp;&bull;&nbsp;
                            <i class="fas fa-award me-1"></i>Escuela Cultura de Paz &middot; SENA
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white mt-1" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body p-4">
                <div class="guia-section">
                    <div class="guia-section-title" style="color:#001A29;">
                        <i class="fas fa-bullseye" style="color:#0070B8;"></i> Objetivo Pedagogico
                    </div>
                    <p class="mb-0 small" style="color:#444;" id="guiaObjetivo">...</p>
                </div>
                <div class="guia-section">
                    <div class="guia-section-title" style="color:#001A29;">
                        <i class="fas fa-chalkboard-user" style="color:#39A900;"></i> Metodologia Aplicada
                    </div>
                    <p class="mb-0 small" style="color:#444;" id="guiaMetodologia">...</p>
                </div>
                <div class="guia-section">
                    <div class="guia-section-title" style="color:#001A29;">
                        <i class="fas fa-graduation-cap" style="color:#6A1B9A;"></i> Competencias Transversales
                    </div>
                    <div id="guiaCompetencias" class="d-flex flex-wrap gap-2"></div>
                </div>
                <div class="guia-section">
                    <div class="guia-section-title" style="color:#001A29;">
                        <i class="fas fa-box-open" style="color:#f59e0b;"></i> Materiales Requeridos
                    </div>
                    <div id="guiaMateriales"></div>
                </div>
                <div class="d-flex align-items-center gap-2 p-3 rounded-3" style="background:#f0f9ff;border:1px solid #bae6fd;font-size:.78rem;color:#0c4a6e;">
                    <i class="fas fa-plug-circle-bolt" style="color:#0070B8;font-size:1rem;"></i>
                    <span>Guia disponible en SICEFA API como
                        <code style="background:#dbeafe;padding:.1rem .4rem;border-radius:4px;">GET /api/v1/controlecp/actividades/{id}/guia</code>
                    </span>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-2 gap-2">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 btn-sm" data-bs-dismiss="modal">
                    <i class="fas fa-xmark me-1"></i> Cerrar
                </button>
                <a href="{{ route('controlecp.administrador.cronograma') }}" class="btn text-white rounded-pill px-4 btn-sm" style="background:#39A900;">
                    <i class="fas fa-calendar-plus me-1"></i> Programar en Cronograma
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const ACTIVIDADES = @json($actividades);

const PALETTE = {
    1: { color: '#39A900', bg: 'linear-gradient(135deg,#001A29 0%,#39A900 100%)' },
    2: { color: '#0070B8', bg: 'linear-gradient(135deg,#002336 0%,#0070B8 100%)' },
    3: { color: '#6A1B9A', bg: 'linear-gradient(135deg,#1a0028 0%,#6A1B9A 100%)' },
};

function abrirGuia(actId) {
    const act = ACTIVIDADES.find(a => a.id === actId);
    if (!act) return;
    const pal = PALETTE[act.momento_id];

    document.getElementById('guiaHeaderBand').style.background = pal.bg;
    document.getElementById('guiaMomentoBadge').textContent = act.momento;
    document.getElementById('guiaNumBadge').textContent = 'Actividad #' + act.numero;
    document.getElementById('guiaTitulo').textContent = act.nombre;
    document.getElementById('guiaDuracion').textContent = act.duracion;
    document.getElementById('guiaObjetivo').textContent = act.objetivo;
    document.getElementById('guiaMetodologia').textContent = act.metodologia;

    document.getElementById('guiaCompetencias').innerHTML = act.competencias.map(c =>
        `<span class="competencia-pill" style="color:${pal.color};border-color:${pal.color}55;background:${pal.color}15;">
            <i class="fas fa-check-circle" style="font-size:.65rem;"></i> ${c}
        </span>`
    ).join('');

    document.getElementById('guiaMateriales').innerHTML = act.materiales.map(m =>
        `<div class="material-item">
            <i class="fas fa-circle-check" style="color:#f59e0b;font-size:.75rem;margin-top:.22rem;flex-shrink:0;"></i>
            <span>${m}</span>
        </div>`
    ).join('');

    new bootstrap.Modal(document.getElementById('modalGuiaPedagogica')).show();
}

// ── Estado global ────────────────────────────────────────────────────────────
let activeMomento = 'all';
let searchTerm    = '';
let currentPage   = 1;
let perPage       = 6;

// ── Motor de filtrado + paginacion ───────────────────────────────────────────
function applyFilters() {
    currentPage = 1;   // Siempre volver a la pagina 1 al filtrar
    renderPage();
}

function renderPage() {
    const allCards = Array.from(document.querySelectorAll('.act-card'));

    // 1. Filtrar
    const filtered = allCards.filter(card => {
        const mMatch = activeMomento === 'all' || card.dataset.momento === activeMomento;
        const tMatch = !searchTerm || card.dataset.text.includes(searchTerm.toLowerCase());
        return mMatch && tMatch;
    });

    const total     = filtered.length;
    const totalPags = Math.ceil(total / perPage) || 1;
    if (currentPage > totalPags) currentPage = totalPags;

    const start = (currentPage - 1) * perPage;
    const end   = start + perPage;

    // 2. Mostrar / ocultar tarjetas
    allCards.forEach(card => card.style.display = 'none');
    filtered.slice(start, end).forEach(card => card.style.display = '');

    // 3. Actualizar contador de resultados
    document.getElementById('resultsCount').textContent = total + ' resultado' + (total !== 1 ? 's' : '');

    // 4. Estado vacio
    const empty   = document.getElementById('actEmpty');
    const grid    = document.getElementById('actGrid');
    const pagWrap = document.getElementById('pagWrap');
    empty.classList.toggle('show', total === 0);
    grid.style.display    = total === 0 ? 'none' : '';
    pagWrap.style.display = total === 0 ? 'none' : 'flex';

    // 5. Info de pagina
    document.getElementById('pagFrom').textContent  = total === 0 ? 0 : start + 1;
    document.getElementById('pagTo').textContent    = Math.min(end, total);
    document.getElementById('pagTotal').textContent = total;

    // 6. Renderizar botones de pagina
    renderPagButtons(totalPags);
}

function renderPagButtons(totalPags) {
    const ctrl = document.getElementById('pagControls');
    ctrl.innerHTML = '';

    // Btn Anterior
    const btnPrev = document.createElement('button');
    btnPrev.className = 'pag-btn';
    btnPrev.innerHTML = '<i class="fas fa-chevron-left" style="font-size:.7rem;"></i>';
    btnPrev.disabled  = currentPage === 1;
    btnPrev.addEventListener('click', () => { currentPage--; renderPage(); scrollToGrid(); });
    ctrl.appendChild(btnPrev);

    // Botones numericos con ventana deslizante de 5
    const window_size = 5;
    let startP = Math.max(1, currentPage - Math.floor(window_size / 2));
    let endP   = Math.min(totalPags, startP + window_size - 1);
    if (endP - startP < window_size - 1) startP = Math.max(1, endP - window_size + 1);

    if (startP > 1) {
        ctrl.appendChild(mkPageBtn(1));
        if (startP > 2) ctrl.appendChild(mkSep());
    }
    for (let p = startP; p <= endP; p++) ctrl.appendChild(mkPageBtn(p));
    if (endP < totalPags) {
        if (endP < totalPags - 1) ctrl.appendChild(mkSep());
        ctrl.appendChild(mkPageBtn(totalPags));
    }

    // Btn Siguiente
    const btnNext = document.createElement('button');
    btnNext.className = 'pag-btn';
    btnNext.innerHTML = '<i class="fas fa-chevron-right" style="font-size:.7rem;"></i>';
    btnNext.disabled  = currentPage === totalPags;
    btnNext.addEventListener('click', () => { currentPage++; renderPage(); scrollToGrid(); });
    ctrl.appendChild(btnNext);
}

function mkPageBtn(p) {
    const btn = document.createElement('button');
    btn.className = 'pag-btn' + (p === currentPage ? ' active' : '');
    btn.textContent = p;
    btn.addEventListener('click', () => { currentPage = p; renderPage(); scrollToGrid(); });
    return btn;
}

function mkSep() {
    const s = document.createElement('span');
    s.className = 'pag-sep';
    s.textContent = '…';
    return s;
}

function scrollToGrid() {
    document.getElementById('actGrid').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ── Pills de Momento ─────────────────────────────────────────────────────────
document.querySelectorAll('.pill-filter').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.pill-filter').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        activeMomento = this.dataset.filter;
        applyFilters();
    });
});

// ── Busqueda ─────────────────────────────────────────────────────────────────
document.getElementById('actSearch').addEventListener('input', function () {
    searchTerm = this.value.trim();
    applyFilters();
});

// ── Selector de items por pagina ─────────────────────────────────────────────
document.getElementById('pagPerPage').addEventListener('change', function () {
    perPage = parseInt(this.value);
    currentPage = 1;
    renderPage();
});

// ── Reset filtros ────────────────────────────────────────────────────────────
function resetFiltros() {
    searchTerm    = '';
    activeMomento = 'all';
    document.getElementById('actSearch').value = '';
    document.querySelectorAll('.pill-filter').forEach(b => b.classList.remove('active'));
    document.querySelector('.pill-filter.all').classList.add('active');
    applyFilters();
}

// ── Exportar CSV (exporta TODOS los filtrados, no solo la pagina actual) ─────
document.getElementById('btnExportCsv').addEventListener('click', function () {
    const cols = ['ID','#','Momento','Nombre','Objetivo','Duracion (h)','Metodologia','Competencias','Materiales','Tags'];
    // Filtrar los mismos que estan visibles (sin limite de pagina)
    const filtered = ACTIVIDADES.filter(a => {
        const mMatch = activeMomento === 'all' || String(a.momento_id) === activeMomento;
        const text   = (a.nombre + ' ' + a.objetivo + ' ' + a.tags.join(' ')).toLowerCase();
        const tMatch = !searchTerm || text.includes(searchTerm.toLowerCase());
        return mMatch && tMatch;
    });
    const rows = filtered.map(a => [
        a.id, a.numero, '"' + a.momento + '"', '"' + a.nombre + '"', '"' + a.objetivo + '"',
        a.duracion_min / 60, '"' + a.metodologia + '"',
        '"' + a.competencias.join(' | ') + '"',
        '"' + a.materiales.join(' | ') + '"',
        '"' + a.tags.join(', ') + '"'
    ]);
    const csv  = [cols.join(','), ...rows.map(r => r.join(','))].join('\n');
    const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'catalogo_actividades_ecp.csv';
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(url);
});

// ── Inicializar al cargar ─────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    // Animacion barras de progreso
    document.querySelectorAll('.mp-fill').forEach(el => {
        const target = el.dataset.width + '%';
        setTimeout(() => { el.style.width = target; }, 300);
    });
    // Renderizar pagina inicial (por defecto 6 por pagina)
    perPage = parseInt(document.getElementById('pagPerPage').value);
    renderPage();
});
</script>
@endpush

