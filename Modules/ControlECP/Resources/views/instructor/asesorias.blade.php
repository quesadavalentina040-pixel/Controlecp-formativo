@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .asesoria-instructor-page {
        --verde: #39A900;
        --verde-claro: #d8f3dc;
        --petroleo: #001A29;
        --petroleo-medio: #00324D;
        --azul-claro: #d0e8f5;
        --borde: #e0ece4;
    }

    .asesoria-instructor-header {
        background: var(--petroleo);
        border-radius: 24px;
        padding: 1.25rem 1.5rem;
    }
    .asesoria-instructor-header h1 { color: #fff; }
    .asesoria-instructor-header p { color: #c7d4da; }
    .asesoria-instructor-kpi {
        background: #fff;
        border: 1px solid var(--borde);
        border-radius: 14px;
        padding: 1rem 1.25rem;
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .asesoria-instructor-kpi:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 26, 41, .1);
    }
    .asesoria-instructor-kpi-label { font-size: .95rem; color: #475569; font-weight: 500; }
    .asesoria-instructor-kpi-value { font-size: 1.7rem; font-weight: 800; line-height: 1; margin-top: .15rem; }
    .asesoria-instructor-card {
        background: #fff;
        border: 1px solid var(--borde);
        border-radius: 14px;
        box-shadow: 0 2px 10px rgba(0, 26, 41, .04);
    }
    .asesoria-instructor-card-header {
        border-bottom: 1px solid #eef2f5;
        padding: 1.1rem 1.25rem;
    }
    .asesoria-instructor-view-switch {
        display:inline-flex;
        align-items:center;
        gap:.2rem;
        padding:.25rem;
        border:1px solid #d9e3ea;
        border-radius:999px;
        background:#f8fafc;
    }
    .asesoria-instructor-view-switch a {
        display:inline-flex;
        align-items:center;
        gap:.45rem;
        padding:.55rem 1rem;
        border-radius:999px;
        color:#001A29;
        font-size:.86rem;
        font-weight:700;
        text-decoration:none;
        white-space:nowrap;
    }
    .asesoria-instructor-view-switch a.active { background:#39A900; color:#fff; }
    .asesoria-instructor-calendar { overflow:hidden; border:1px solid #dce8df; border-radius:16px; background:#fff; }
    .asesoria-instructor-calendar-toolbar {
        display:flex;
        min-height:58px;
        align-items:center;
        justify-content:space-between;
        gap:1rem;
        padding:.65rem 1rem;
        border-bottom:1px solid #e7edf0;
    }
    .asesoria-instructor-calendar-month { color:#001A29; font-size:.95rem; font-weight:800; }
    .asesoria-instructor-calendar-nav { display:flex; align-items:center; gap:.55rem; }
    .asesoria-instructor-calendar-arrow {
        display:grid;
        width:34px;
        height:34px;
        place-items:center;
        border:1px solid #cbd5e1;
        border-radius:50%;
        background:#fff;
        color:#526579;
        text-decoration:none;
    }
    .asesoria-instructor-calendar-arrow:hover { border-color:#39A900; color:#39A900; }
    .asesoria-instructor-calendar-today {
        padding:.35rem .75rem;
        border:1px solid #39A900;
        border-radius:999px;
        background:#fff;
        color:#318f00;
        font-size:.8rem;
        font-weight:700;
        text-decoration:none;
    }
    .asesoria-instructor-calendar-body { padding:.85rem; }
    .asesoria-instructor-calendar-weekdays,
    .asesoria-instructor-calendar-grid { display:grid; grid-template-columns:repeat(7,minmax(0,1fr)); }
    .asesoria-instructor-calendar-weekday {
        padding:.65rem .4rem;
        background:#001A29;
        color:#fff;
        font-size:.7rem;
        font-weight:800;
        text-align:center;
    }
    .asesoria-instructor-calendar-weekday:first-child { border-top-left-radius:8px; }
    .asesoria-instructor-calendar-weekday:last-child { border-top-right-radius:8px; }
    .asesoria-instructor-calendar-day {
        min-width:0;
        min-height:88px;
        padding:.55rem;
        border-right:1px solid #d9e3ea;
        border-bottom:1px solid #d9e3ea;
        background:#fff;
    }
    .asesoria-instructor-calendar-day:nth-child(7n+1) { border-left:1px solid #d9e3ea; }
    .asesoria-instructor-calendar-day.is-outside { background:#eaf1f5; color:#82909b; }
    .asesoria-instructor-calendar-day-number {
        display:inline-flex;
        width:22px;
        height:22px;
        align-items:center;
        justify-content:center;
        color:#334155;
        font-size:.8rem;
        font-weight:800;
    }
    .asesoria-instructor-calendar-day.is-outside .asesoria-instructor-calendar-day-number { color:#82909b; }
    .asesoria-instructor-calendar-day.is-today .asesoria-instructor-calendar-day-number {
        border-radius:50%;
        background:#39A900;
        color:#fff;
    }
    .asesoria-instructor-calendar-event {
        display:block;
        width:100%;
        overflow:hidden;
        margin-top:.3rem;
        padding:.25rem .4rem;
        border-top:0;
        border-right:0;
        border-bottom:0;
        border-left:3px solid #0284c7;
        border-radius:5px;
        background:#eaf5ff;
        color:#075985;
        font:inherit;
        font-size:.68rem;
        line-height:1.25;
        text-align:left;
        text-overflow:ellipsis;
        white-space:nowrap;
        cursor:pointer;
    }
    .asesoria-instructor-calendar-event:hover,
    .asesoria-instructor-calendar-event:focus-visible { background:#d8edff; outline:2px solid #0284c7; outline-offset:1px; }
    .asesoria-instructor-calendar-event.is-done { border-left-color:#39A900; background:#eef9e9; color:#287a13; }
    .asesoria-instructor-table thead th {
        background: #001A29;
        color: #fff;
        font-size: .76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .4px;
        padding: .85rem 1rem;
        border: 0;
        white-space: nowrap;
    }
    .asesoria-instructor-table tbody td {
        padding: .85rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .asesoria-pagination {
        display: flex;
        justify-content: center;
        padding: 1rem;
        border-top: 1px solid #edf1f5;
        background: #fff;
    }
    .asesoria-pagination .pagination { gap: 0; margin: 0; }
    .asesoria-pagination .page-link {
        display: flex;
        min-width: 42px;
        height: 42px;
        align-items: center;
        justify-content: center;
        border-color: #cfe5d4;
        background: #fff;
        color: #39a900;
        font-weight: 700;
        box-shadow: none;
    }
    .asesoria-pagination .page-item:first-child .page-link { border-radius: 7px 0 0 7px; }
    .asesoria-pagination .page-item:last-child .page-link { border-radius: 0 7px 7px 0; }
    .asesoria-pagination .page-item.active .page-link {
        border-color: #39a900;
        background: #39a900;
        color: #fff;
    }
    .asesoria-pagination .page-item.disabled .page-link { color: #8aa18d; background: #f7faf8; }
    .asesoria-instructor-filter {
        padding: 1rem 1.15rem;
        margin-bottom: 1.5rem;
        background: #001A29;
        border-radius: 18px;
        box-shadow: 0 5px 20px rgba(0, 26, 41, .08);
    }
    .asesoria-instructor-filter-form {
        display: grid;
        grid-template-columns: minmax(200px, 1.4fr) minmax(150px, 1fr) minmax(150px, 1fr) repeat(2, minmax(145px, 1fr)) 160px;
        align-items: end;
        gap: .75rem;
    }
    .asesoria-instructor-filter-field { min-width: 0; }
    .asesoria-instructor-filter-field label {
        display: block;
        margin: 0 0 .3rem;
        color: #fff;
        font-size: .75rem;
        font-weight: 700;
    }
    .asesoria-instructor-filter-field .form-control,
    .asesoria-instructor-filter-field .form-select {
        width: 100%;
        min-width: 0;
        height: 44px;
        padding: .55rem .8rem;
        border: 1px solid #fff;
        border-radius: 10px;
        background: #fff;
        color: #001A29;
        font-size: .88rem;
        box-shadow: none;
    }
    .asesoria-instructor-filter-actions { display: flex; gap: .55rem; min-width: 0; }
    .asesoria-instructor-filter-actions .btn-filter {
        flex: 1;
        min-width: 0;
        height: 44px;
        border: 0;
        border-radius: 10px;
        background: #39A900;
        color: #fff;
        font-weight: 700;
        white-space: nowrap;
    }
    .asesoria-instructor-filter-actions .btn-filter:hover { background: #318f00; color: #fff; }
    .asesoria-instructor-filter-actions .btn-reset {
        flex: 0 0 48px;
        height: 44px;
        display: grid;
        place-items: center;
        padding: 0;
        border: 1px solid #fff;
        border-radius: 10px;
        background: #fff;
        color: #001A29;
    }
    .asesoria-instructor-filter-actions .btn-reset:hover { background: #f1f5f9; color: #001A29; }
    .asesoria-instructor-actions { display: flex; justify-content: flex-end; flex-wrap: wrap; gap: .4rem; white-space: nowrap; }
    .asesoria-action {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border: 1px solid transparent;
        border-radius: 10px;
        transition: .15s ease;
    }
    .asesoria-action:hover { transform: translateY(-2px); box-shadow: 0 3px 8px rgba(0, 26, 41, .14); }
    .asesoria-action-view { background: #eaf5ff; border-color: #c9e6fb; color: #0876bd; }
    .asesoria-action-accept { background: #eaf8ef; border-color: #ccebd6; color: #278344; }
    .asesoria-action-reject { background: #fff0f0; border-color: #f7cccc; color: #d63939; }
    .asesoria-action-reschedule { background: #fff7e6; border-color: #f8e3b2; color: #c47a00; }
    .asesoria-action-cancel { background: #fff0f0; border-color: #f7cccc; color: #d63939; }
    .asesoria-action-followup { background: #eaf8ef; border-color: #ccebd6; color: #278344; }
    .asesoria-action-resource { background: #f3edff; border-color: #ddccff; color: #7541c8; }
    .asesoria-action-rating { background:#fff7e6; border-color:#f8e3b2; color:#e8a000; }
    .asesoria-action-rating i { color:#e8a000; }
    .asesoria-instructor-page .modal-content { border: 0; border-radius: 16px; overflow: hidden; }
    .asesoria-instructor-page .modal-header {
        color: #fff;
        background: var(--petroleo);
        border-bottom: 2px solid var(--verde);
    }
    .asesoria-instructor-page .modal-body { background: #f8fafc; }
    .asesoria-instructor-page .modal-footer { background: #fff; border-top: 0; }
    .asesoria-action-modal .modal-content { overflow:hidden; border:0; border-radius:22px; box-shadow:0 20px 55px rgba(0,26,41,.22); }
    .asesoria-action-modal .modal-header {
        display:flex;
        align-items:center;
        gap:.85rem;
        padding:1.25rem 1.5rem;
        border:0;
        border-bottom:2px solid #39A900;
        background:linear-gradient(135deg,#001A29 0%,#12394d 100%);
        color:#fff;
    }
    .asesoria-action-modal .modal-header .modal-title { color:#fff !important; }
    .asesoria-action-modal .modal-header .btn-close { filter:invert(1) grayscale(100%) brightness(200%); }
    .asesoria-action-modal-icon {
        display:flex;
        width:46px;
        height:46px;
        flex:0 0 46px;
        align-items:center;
        justify-content:center;
        border:1px solid rgba(255,255,255,.25);
        border-radius:15px;
        background:rgba(255,255,255,.1);
        color:#fff;
        font-size:1.1rem;
    }
    .asesoria-action-modal--view .asesoria-action-modal-icon { color:#7dd3fc; background:rgba(14,165,233,.16); border-color:rgba(125,211,252,.38); }
    .asesoria-action-modal--reject .asesoria-action-modal-icon,
    .asesoria-action-modal--cancel .asesoria-action-modal-icon { color:#fca5a5; background:rgba(239,68,68,.16); border-color:rgba(252,165,165,.38); }
    .asesoria-action-modal--reschedule .asesoria-action-modal-icon { color:#fcd34d; background:rgba(245,158,11,.16); border-color:rgba(252,211,77,.38); }
    .asesoria-action-modal--followup .asesoria-action-modal-icon { color:#86efac; background:rgba(34,197,94,.16); border-color:rgba(134,239,172,.38); }
    .asesoria-action-modal--resource .asesoria-action-modal-icon { color:#c4b5fd; background:rgba(139,92,246,.16); border-color:rgba(196,181,253,.38); }
    .asesoria-action-modal--rating .asesoria-action-modal-icon { color:#fcd34d; background:rgba(245,158,11,.16); border-color:rgba(252,211,77,.38); }
    .asesoria-rating-stars { display:flex; gap:.3rem; color:#f59e0b; font-size:1.75rem; }
    .asesoria-rating-stars .is-empty { color:#cbd5e1; }
    .asesoria-action-modal-subtitle { margin:.2rem 0 0; color:#c7d4da; font-size:.82rem; }
    .asesoria-action-modal .modal-body { padding:1.5rem; background:#f8fafc; }
    .asesoria-view-modal .modal-dialog { max-width:820px; }
    .asesoria-view-modal .modal-header { min-height:90px; }
    .asesoria-view-modal .modal-header-main { min-width:0; flex:1; }
    .asesoria-view-modal .modal-title-row { display:flex; flex-wrap:wrap; align-items:center; gap:.55rem; }
    .asesoria-view-modal .modal-title { font-size:1.2rem; }
    .asesoria-state-badge { display:inline-flex !important; align-items:center; gap:.3rem; padding:.28rem .8rem !important; border:1px solid transparent; border-radius:999px !important; font-size:.72rem; font-weight:800; line-height:1.2; white-space:nowrap; }
    .asesoria-state-badge--programada { background:#e0f2fe !important; border-color:#bae6fd !important; color:#0369a1 !important; }
    .asesoria-state-badge--solicitada,
    .asesoria-state-badge--pendiente,
    .asesoria-state-badge--pospuesta { background:#fef3c7 !important; border-color:#fde68a !important; color:#b45309 !important; }
    .asesoria-state-badge--realizada,
    .asesoria-state-badge--finalizada { background:#dcfce7 !important; border-color:#bbf7d0 !important; color:#15803d !important; }
    .asesoria-state-badge--cancelada,
    .asesoria-state-badge--rechazada { background:#fee2e2 !important; border-color:#fecaca !important; color:#c62828 !important; }
    .asesoria-view-modal .asesoria-view-card { border:1px solid #dcebe1; border-radius:16px; background:#fff; box-shadow:0 2px 5px rgba(0,26,41,.07); }
    .asesoria-view-modal .asesoria-view-data { padding:1rem; }
    .asesoria-view-modal .asesoria-view-people { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:1rem; padding-bottom:1rem; border-bottom:1px solid #d5dce2; }
    .asesoria-view-modal .asesoria-view-person { display:flex; min-width:0; align-items:flex-start; gap:.7rem; }
    .asesoria-view-modal .asesoria-view-person-icon { display:flex; width:40px; height:40px; flex:0 0 40px; align-items:center; justify-content:center; border-radius:50%; background:#f4f7f8; color:#39a900; }
    .asesoria-view-modal .asesoria-view-person-icon.is-instructor { color:#0787d1; }
    .asesoria-view-modal .asesoria-view-label { display:block; margin-bottom:.12rem; color:#69737c; font-size:.9rem; }
    .asesoria-view-modal .asesoria-view-value { display:block; color:#092536; font-weight:800; overflow-wrap:anywhere; }
    .asesoria-view-modal .asesoria-view-meta { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:1rem; padding-top:1rem; }
    .asesoria-view-modal .asesoria-view-section { margin-top:1rem; padding:1rem; }
    .asesoria-view-modal .asesoria-view-section-title { display:flex; align-items:center; gap:.45rem; margin:0 0 .6rem; color:#092536; font-size:.9rem; font-weight:800; }
    .asesoria-view-modal .asesoria-view-section-title i { color:#39a900; }
    .asesoria-view-modal .asesoria-view-reason { margin:0; padding:.65rem .75rem; border-radius:10px; background:#f5f7f8; color:#606b73; overflow-wrap:anywhere; }
    .asesoria-view-modal .asesoria-view-history-list { padding:.5rem; border-radius:12px; background:#f7f8f9; }
    .asesoria-view-modal .asesoria-view-history-item { margin-bottom:.5rem; padding:.9rem 1rem; border:1px solid #d8e0e6; border-radius:10px; background:#fff; box-shadow:0 2px 4px rgba(0,26,41,.06); }
    .asesoria-view-modal .asesoria-view-history-item:last-child { margin-bottom:0; }
    .asesoria-view-modal .asesoria-view-history-date { margin-bottom:.4rem; color:#68727b; font-size:.82rem; text-align:right; }
    .asesoria-view-modal .asesoria-view-history-actor { margin-top:.4rem; color:#68727b; }
    .asesoria-view-modal .asesoria-view-modal-footer { min-height:78px; padding:1rem 1.5rem; border-top:1px solid #e4e9ed; background:#fff; }
    .asesoria-view-modal .asesoria-view-modal-footer .btn { border:1px solid #092536; border-radius:999px; color:#092536; font-weight:800; }
    @media (max-width:575.98px) {
        .asesoria-view-modal .asesoria-view-people { grid-template-columns:1fr; }
        .asesoria-view-modal .asesoria-view-meta { grid-template-columns:1fr; gap:.75rem; }
        .asesoria-view-modal .modal-header { padding:1rem; }
        .asesoria-view-modal .modal-body { padding:1rem !important; }
    }
    .asesoria-action-modal .form-control,
    .asesoria-action-modal .form-select { min-height:46px; border:1px solid #dbe3eb; border-radius:12px; box-shadow:none; }
    .asesoria-action-modal textarea.form-control { min-height:112px; resize:vertical; }
    .asesoria-action-modal .form-control:focus,
    .asesoria-action-modal .form-select:focus { border-color:#39A900; box-shadow:0 0 0 .2rem rgba(57,169,0,.13); }
    .asesoria-action-modal .modal-footer { padding:1rem 1.5rem; border-top:1px solid #edf1f5; background:#fbfcfd; }
    .asesoria-action-modal .btn { min-height:40px; padding:.5rem 1rem; border-radius:999px; font-weight:700; }
    .asesoria-action-modal .modal-body .asesoria-modal-note {
        padding:.85rem 1rem;
        border:1px solid #dbeafe;
        border-radius:12px;
        background:#eff6ff;
        color:#334155;
    }
    .asesoria-instructor-page .btn-disponibilidad-guardar {
        border: 1px solid var(--verde);
        border-radius: 8px;
        background: var(--verde);
        color: #fff;
        font-weight: 700;
    }
    .asesoria-instructor-page .btn-disponibilidad-guardar:hover,
    .asesoria-instructor-page .btn-disponibilidad-guardar:focus-visible {
        border-color: #2e8b00;
        background: #2e8b00;
        color: #fff;
    }
    @media (max-width: 1100px) {
        .asesoria-instructor-filter-form { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .asesoria-instructor-filter-actions { grid-column: span 3; justify-content: flex-end; }
        .asesoria-instructor-filter-actions .btn-filter { flex: 0 0 150px; }
    }
    @media (max-width: 767px) {
        .asesoria-instructor-filter-form { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .asesoria-instructor-filter-actions { grid-column: span 2; }
        .asesoria-instructor-calendar-body { padding:.45rem; }
        .asesoria-instructor-calendar-day { min-height:72px; padding:.3rem; }
        .asesoria-instructor-calendar-event { font-size:.6rem; padding:.2rem; }
    }
    @media (max-width: 480px) {
        .asesoria-instructor-filter-form { grid-template-columns: minmax(0, 1fr); }
        .asesoria-instructor-filter-actions { grid-column: auto; }
        .asesoria-instructor-filter-actions .btn-filter { flex: 1; }
        .asesoria-instructor-calendar-toolbar { padding:.6rem; }
        .asesoria-instructor-calendar-body { padding:.25rem; }
        .asesoria-instructor-calendar-weekday { padding:.5rem .1rem; font-size:.58rem; }
        .asesoria-instructor-calendar-day { min-height:58px; padding:.2rem; }
        .asesoria-instructor-calendar-day-number { width:19px; height:19px; font-size:.7rem; }
        .asesoria-instructor-calendar-event { font-size:.52rem; }
        .asesoria-instructor-view-switch a { padding:.5rem .7rem; font-size:.76rem; }
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-3 py-3">
    <div class="asesoria-instructor-page">
    <div class="asesoria-instructor-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small fw-semibold mb-1" style="color:#39A900"><i class="fas fa-chart-line me-2"></i>Control ECP · Panel de instructor</div>
            <h1 class="h3 fw-bold mb-1"><i class="fas fa-comments me-2" style="color:#39A900"></i>Gestión de Asesorías</h1>
            <p class="mb-0 small">Gestiona las asesorías que atiendes, tu disponibilidad y los seguimientos.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-outline-light rounded-pill px-3" type="button" data-bs-toggle="modal" data-bs-target="#modalDisponibilidadInstructor">
                <i class="fas fa-clock me-2"></i>Mi disponibilidad
            </button>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="row g-3 mb-4">
        @foreach([['Programadas',$stats['programadas'],'#0284c7','#d0e8f5','fa-calendar-check'],['Realizadas',$stats['realizadas'],'#15803d','#d8f3dc','fa-circle-check'],['Canceladas',$stats['canceladas'],'#b91c1c','#fee2e2','fa-ban']] as [$label,$value,$color,$background,$icon])
            <div class="col-6 col-md-4">
                <div class="asesoria-instructor-kpi h-100" style="border-color:{{ $color }}55">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="asesoria-instructor-kpi-label">{{ $label }}</span>
                        <span class="rounded-circle d-flex align-items-center justify-content-center" style="width:38px;height:38px;background:{{ $background }};color:{{ $color }}"><i class="fas {{ $icon }}"></i></span>
                    </div>
                    <div class="asesoria-instructor-kpi-value" style="color:{{ $color }}">{{ $value }}</div>
                    <div class="small text-muted mt-1">{{ $label === 'Programadas' ? 'Con fecha y horario' : ($label === 'Realizadas' ? 'Con seguimiento final' : 'Anuladas con motivo') }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="modal fade" id="modalDisponibilidadInstructor" tabindex="-1" aria-labelledby="modalDisponibilidadInstructorLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:rgba(57,169,0,.2)">
                            <i class="fas fa-clock text-white" aria-hidden="true"></i>
                        </span>
                        <div>
                            <h2 class="modal-title h5 fw-bold mb-1" id="modalDisponibilidadInstructorLabel">Mi disponibilidad</h2>
                            <p class="small mb-0" style="color:#c7d4da">Define tus días y horarios disponibles para asesorías.</p>
                        </div>
                    </div>
                    <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    @if(session('modal_abrir') === 'modalDisponibilidadInstructor' && $errors->any())
                        <div class="alert alert-danger small rounded-3">
                            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                        </div>
                    @endif
                    <form method="POST" action="{{ route('controlecp.instructor.asesorias.disponibilidad.categoria') }}" class="card border-0 shadow-sm p-3 rounded-3 bg-white mb-4">
                        @csrf
                        <label class="form-label fw-bold mb-3" style="color:#001A29" for="categoriaDisponibilidadInstructor">
                            <i class="fas fa-tags me-1" style="color:#39A900" aria-hidden="true"></i>Selecciona categoría <span class="text-danger">*</span>
                        </label>
                        <select class="form-select form-select-sm rounded-pill" id="categoriaDisponibilidadInstructor" name="id_categoria_asesoria" required>
                            <option value="">Selecciona una categoría</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id_categoria_asesoria }}" @selected(old('id_categoria_asesoria', $categoriaDisponibilidadSeleccionada) == $categoria->id_categoria_asesoria)>{{ $categoria->nombre }}</option>
                            @endforeach
                        </select>
                        <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 mt-2">
                            <button class="btn btn-disponibilidad-guardar btn-sm px-4 shadow-sm" type="submit">
                                <i class="fas fa-save me-1" aria-hidden="true"></i>Guardar
                            </button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('controlecp.instructor.asesorias.disponibilidad.franjas.store') }}" class="card border-0 shadow-sm p-3 rounded-3 bg-white mb-4">
                        @csrf
                        <h3 class="h6 fw-bold mb-3" style="color:#001A29">
                            <i class="fas fa-calendar-plus me-1" style="color:#39A900" aria-hidden="true"></i>Agregar Franja Horaria
                        </h3>
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1" for="disponibilidadDiaInstructor">Día de la Semana <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm rounded-pill" id="disponibilidadDiaInstructor" name="dia_semana" required>
                                    @foreach([1=>'Lunes',2=>'Martes',3=>'Miércoles',4=>'Jueves',5=>'Viernes'] as $day=>$name)
                                        <option value="{{ $day }}" @selected((int) old('dia_semana', 1) === $day)>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold text-muted mb-1" for="disponibilidadHoraInicioInstructor">Hora Inicio <span class="text-danger">*</span></label>
                                <input class="form-control form-control-sm rounded-pill" id="disponibilidadHoraInicioInstructor" type="time" name="hora_inicio" value="{{ old('hora_inicio', '08:00') }}" min="08:00" max="14:59" step="60" required>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold text-muted mb-1" for="disponibilidadHoraFinInstructor">Hora Fin <span class="text-danger">*</span></label>
                                <input class="form-control form-control-sm rounded-pill" id="disponibilidadHoraFinInstructor" type="time" name="hora_fin" value="{{ old('hora_fin', '15:00') }}" min="08:00" max="15:00" step="60" required>
                            </div>
                            <div class="col-12"><small class="text-muted">Las franjas deben estar entre las 8:00 a. m. y las 3:00 p. m.</small></div>
                            <div class="col-12 text-end mt-2">
                                <button class="btn btn-success btn-disponibilidad-guardar btn-sm px-4 shadow-sm" type="submit">
                                    <i class="fas fa-save me-1" aria-hidden="true"></i>Guardar Franja
                                </button>
                            </div>
                        </div>
                    </form>

                    <h3 class="h6 fw-bold mb-2" style="color:#001A29">
                        <i class="fas fa-calendar-check me-1" style="color:#0284c7" aria-hidden="true"></i>Mis franjas registradas
                    </h3>
                    <div class="p-3 border rounded-3 bg-white shadow-sm">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered small mb-0 align-middle">
                                <thead class="table-light"><tr><th>Día</th><th>Horario</th><th>Categoría</th><th>Estado</th></tr></thead>
                                <tbody>
                                    @forelse($disponibilidadActual as $franja)
                                        <tr>
                                            <td>{{ [1=>'Lunes',2=>'Martes',3=>'Miércoles',4=>'Jueves',5=>'Viernes'][$franja->dia_semana] ?? '—' }}</td>
                                            <td>{{ str_replace(['am','pm'], ['a. m.','p. m.'], \Carbon\Carbon::parse($franja->hora_inicio)->format('g:i a')) }} - {{ str_replace(['am','pm'], ['a. m.','p. m.'], \Carbon\Carbon::parse($franja->hora_fin)->format('g:i a')) }}</td>
                                            <td>{{ $franja->categoria?->nombre ?? 'Sin categoría' }}</td>
                                            <td>
                                                <div class="d-flex align-items-center justify-content-between gap-2">
                                                    <span class="badge rounded-pill {{ $franja->habilitada ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">{{ $franja->habilitada ? 'Activa' : 'Inactiva' }}</span>
                                                    <button class="btn btn-sm btn-outline-primary rounded-circle px-2"
                                                            type="button"
                                                            title="Editar horario"
                                                            aria-label="Editar horario del {{ [1=>'lunes',2=>'martes',3=>'miércoles',4=>'jueves',5=>'viernes'][$franja->dia_semana] ?? 'día' }}"
                                                            data-editar-disponibilidad
                                                            data-id="{{ $franja->id_disponibilidad_instructor }}"
                                                            data-dia="{{ [1=>'Lunes',2=>'Martes',3=>'Miércoles',4=>'Jueves',5=>'Viernes'][$franja->dia_semana] ?? 'Día' }}"
                                                            data-inicio="{{ substr($franja->hora_inicio, 0, 5) }}"
                                                            data-fin="{{ substr($franja->hora_fin, 0, 5) }}">
                                                        <i class="fas fa-pen-to-square" aria-hidden="true"></i>
                                                    </button>
                                                    <form method="POST" action="{{ route('controlecp.instructor.asesorias.disponibilidad.franjas.estado', $franja->id_disponibilidad_instructor) }}"
                                                          onsubmit="return confirm('¿Seguro que deseas {{ $franja->habilitada ? 'desactivar' : 'activar' }} esta franja?');">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="habilitada" value="{{ $franja->habilitada ? 0 : 1 }}">
                                                        <button class="btn btn-sm rounded-circle px-2 {{ $franja->habilitada ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                                                type="submit"
                                                                title="{{ $franja->habilitada ? 'Desactivar franja' : 'Activar franja' }}"
                                                                aria-label="{{ $franja->habilitada ? 'Desactivar' : 'Activar' }} franja del {{ [1=>'lunes',2=>'martes',3=>'miércoles',4=>'jueves',5=>'viernes'][$franja->dia_semana] ?? 'día' }}">
                                                            <i class="fas {{ $franja->habilitada ? 'fa-ban' : 'fa-check' }}" aria-hidden="true"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td class="text-muted text-center py-3" colspan="4">Aún no tienes franjas de disponibilidad registradas.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-light rounded-pill px-3" type="button" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditarDisponibilidadInstructor" tabindex="-1" aria-labelledby="modalEditarDisponibilidadInstructorLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" id="formEditarDisponibilidadInstructor" action="">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h2 class="modal-title h5 fw-bold" id="modalEditarDisponibilidadInstructorLabel">Editar horario disponible</h2>
                        <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body p-4">
                        @if(session('modal_abrir') === 'modalEditarDisponibilidadInstructor' && $errors->any())
                            <div class="alert alert-danger small rounded-3">
                                @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                            </div>
                        @endif
                        <p class="small text-muted" id="editarDisponibilidadDiaInstructor"></p>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-muted" for="editarDisponibilidadHoraInicioInstructor">Hora Inicio</label>
                                <input class="form-control rounded-pill" id="editarDisponibilidadHoraInicioInstructor" type="time" name="hora_inicio" value="{{ old('hora_inicio') }}" min="08:00" max="14:59" step="60" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-muted" for="editarDisponibilidadHoraFinInstructor">Hora Fin</label>
                                <input class="form-control rounded-pill" id="editarDisponibilidadHoraFinInstructor" type="time" name="hora_fin" value="{{ old('hora_fin') }}" min="08:00" max="15:00" step="60" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-light rounded-pill px-3" type="button" data-bs-dismiss="modal">Cancelar</button>
                        <button class="btn btn-success btn-disponibilidad-guardar px-4" type="submit">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="asesoria-instructor-filter">
        <form method="GET" action="{{ route('controlecp.instructor.asesorias') }}" class="asesoria-instructor-filter-form">
            <input type="hidden" name="vista" value="{{ request('vista', 'calendario') }}">
            <input type="hidden" name="calendario_mes" value="{{ $calendarMonth->format('Y-m') }}">
            <div class="asesoria-instructor-filter-field">
                <label><i class="fas fa-search me-1"></i> Buscar Aprendiz</label>
                <input class="form-control" name="aprendiz" value="{{ $filters['aprendiz'] ?? '' }}" placeholder="Nombre, documento o correo">
            </div>
            <div class="asesoria-instructor-filter-field">
                <label>Categoría</label>
                <select class="form-select" name="categoria_id">
                    <option value="">Todas</option>
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria->id_categoria_asesoria }}" @selected(($filters['categoria_id'] ?? '') == $categoria->id_categoria_asesoria)>{{ $categoria->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="asesoria-instructor-filter-field">
                <label>Estado</label>
                <select class="form-select" name="estado">
                    <option value="">Todos</option>
                    @foreach(['Solicitada','Programada','Realizada','Cancelada','Rechazada'] as $state)
                        <option value="{{ $state }}" @selected(($filters['estado'] ?? '') === $state)>{{ $state }}</option>
                    @endforeach
                </select>
            </div>
            <div class="asesoria-instructor-filter-field">
                <label>Fecha desde</label>
                <input class="form-control" type="date" name="fecha_desde" value="{{ $filters['fecha_desde'] ?? '' }}">
            </div>
            <div class="asesoria-instructor-filter-field">
                <label>Fecha hasta</label>
                <input class="form-control" type="date" name="fecha_hasta" value="{{ $filters['fecha_hasta'] ?? '' }}">
            </div>
            <div class="asesoria-instructor-filter-actions">
                <button class="btn btn-filter" type="submit" title="Buscar asesorías"><i class="fas fa-magnifying-glass me-1"></i>Buscar</button>
                <a href="{{ route('controlecp.instructor.asesorias', ['vista' => request('vista', 'calendario'), 'calendario_mes' => $calendarMonth->format('Y-m')]) }}" class="btn btn-reset" title="Restablecer filtros" aria-label="Restablecer filtros"><i class="fas fa-rotate-left"></i></a>
            </div>
        </form>
    </div>

     <div class="asesoria-instructor-view-switch mb-3" role="navigation" aria-label="Vistas de asesorías">
        <a href="{{ route('controlecp.instructor.asesorias', array_merge(request()->query(), ['vista' => 'calendario', 'page' => null])) }}"
           class="{{ request('vista', 'calendario') === 'calendario' ? 'active' : '' }}"
           @if(request('vista', 'calendario') === 'calendario') aria-current="page" @endif>
            <i class="fas fa-calendar-days" aria-hidden="true"></i>Calendario
        </a>
        <a href="{{ route('controlecp.instructor.asesorias', array_merge(request()->query(), ['vista' => 'solicitudes', 'page' => null])) }}"
           class="{{ request('vista') === 'solicitudes' ? 'active' : '' }}"
           @if(request('vista') === 'solicitudes') aria-current="page" @endif>
            <i class="fas fa-list-ul" aria-hidden="true"></i>Mis Solicitudes
        </a>
    </div>

    @if(request('vista', 'calendario') === 'calendario')
        <section class="asesoria-instructor-calendar mb-4" aria-label="Calendario de asesorías">
            <div class="asesoria-instructor-calendar-toolbar">
                <div class="asesoria-instructor-calendar-nav">
                    <a class="asesoria-instructor-calendar-arrow"
                       href="{{ route('controlecp.instructor.asesorias', array_merge(request()->query(), ['vista' => 'calendario', 'calendario_mes' => $calendarPreviousMonth, 'page' => null])) }}"
                       aria-label="Mes anterior"><i class="fas fa-chevron-left" aria-hidden="true"></i></a>
                    <h2 class="asesoria-instructor-calendar-month mb-0">{{ $calendarTitle }}</h2>
                    <a class="asesoria-instructor-calendar-arrow"
                       href="{{ route('controlecp.instructor.asesorias', array_merge(request()->query(), ['vista' => 'calendario', 'calendario_mes' => $calendarNextMonth, 'page' => null])) }}"
                       aria-label="Mes siguiente"><i class="fas fa-chevron-right" aria-hidden="true"></i></a>
                </div>
                <a class="asesoria-instructor-calendar-today"
                   href="{{ route('controlecp.instructor.asesorias', array_merge(request()->query(), ['vista' => 'calendario', 'calendario_mes' => $calendarCurrentMonth, 'page' => null])) }}">
                    <i class="fas fa-calendar-day me-1" aria-hidden="true"></i>Hoy
                </a>
            </div>
            <div class="asesoria-instructor-calendar-body">
                <div class="asesoria-instructor-calendar-weekdays" aria-hidden="true">
                    @foreach(['LUN','MAR','MIÉ','JUE','VIE','SÁB','DOM'] as $weekday)
                        <div class="asesoria-instructor-calendar-weekday">{{ $weekday }}</div>
                    @endforeach
                </div>
                <div class="asesoria-instructor-calendar-grid">
                    @foreach($calendarDays as $calendarDay)
                        @php
                            $calendarDate = $calendarDay->format('Y-m-d');
                            $dayAppointments = $calendarAppointments->get($calendarDate, collect());
                            $dayAppointmentDetails = $dayAppointments->map(fn ($appointment) => substr($appointment->hora_inicio, 0, 5)
                                . ' · ' . ($appointment->categoria?->nombre ?? 'Asesoría')
                                . ' · ' . ($appointment->aprendiz?->person?->full_name ?? $appointment->aprendiz?->nickname ?? 'Aprendiz'))
                                ->implode(' | ');
                        @endphp
                        <div class="asesoria-instructor-calendar-day {{ $calendarDay->month !== $calendarMonth->month ? 'is-outside' : '' }} {{ $calendarDate === now()->toDateString() ? 'is-today' : '' }}" data-calendario-fecha="{{ $calendarDate }}">
                            <span class="asesoria-instructor-calendar-day-number">{{ $calendarDay->day }}</span>
                            @if($dayAppointments->isNotEmpty())
                                <button class="asesoria-instructor-calendar-event {{ $dayAppointments->every(fn ($appointment) => $appointment->estado === 'Realizada') ? 'is-done' : '' }}"
                                        type="button" data-bs-toggle="modal" data-bs-target="#asesoriasDia{{ $calendarDay->format('Ymd') }}"
                                        title="{{ $dayAppointmentDetails }}" aria-label="Ver asesorías del {{ $calendarDay->format('d/m/Y') }}">
                                    {{ $dayAppointments->count() }} {{ $dayAppointments->count() === 1 ? 'asesoría' : 'asesorías' }}
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        @foreach($calendarDays as $calendarDay)
            @php
                $calendarDate = $calendarDay->format('Y-m-d');
                $dayAppointments = $calendarAppointments->get($calendarDate, collect());
            @endphp
            @if($dayAppointments->isNotEmpty())
                <div class="modal fade asesoria-action-modal asesoria-action-modal--view" id="asesoriasDia{{ $calendarDay->format('Ymd') }}" tabindex="-1" aria-labelledby="asesoriasDiaTitulo{{ $calendarDay->format('Ymd') }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <span class="asesoria-action-modal-icon"><i class="fas fa-calendar-check" aria-hidden="true"></i></span>
                                <div>
                                    <h2 class="modal-title h5 fw-bold mb-0" id="asesoriasDiaTitulo{{ $calendarDay->format('Ymd') }}">Horario reservado</h2>
                                    <p class="asesoria-action-modal-subtitle">{{ $calendarDay->translatedFormat('d \d\e F \d\e Y') }}</p>
                                </div>
                                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                @foreach($dayAppointments as $calendarAppointment)
                                    <div class="border rounded-3 bg-white p-3 mb-2">
                                        <div class="fw-bold text-dark">
                                            {{ \Carbon\Carbon::parse($calendarAppointment->hora_inicio)->format('g:i a') }}
                                            – {{ \Carbon\Carbon::parse($calendarAppointment->hora_fin)->format('g:i a') }}
                                        </div>
                                        <div class="mt-1">Reservado con <strong>{{ $calendarAppointment->aprendiz?->person?->full_name ?? $calendarAppointment->aprendiz?->nickname ?? 'Aprendiz' }}</strong></div>
                                        <div class="small text-muted mt-1">{{ $calendarAppointment->categoria?->nombre ?? 'Asesoría' }} · {{ $calendarAppointment->estado }}</div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @endif
        @foreach($asesorias as $asesoria)
            @php
                $personaAprendiz = $asesoria->aprendiz?->person;
            @endphp
            <div class="modal fade asesoria-action-modal asesoria-action-modal--view asesoria-view-modal" id="detalle{{ $asesoria->id_asesoria }}" tabindex="-1" aria-labelledby="detalleTitulo{{ $asesoria->id_asesoria }}" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <span class="asesoria-action-modal-icon"><i class="fas fa-info-circle" aria-hidden="true"></i></span>
                            <div class="modal-header-main">
                                <div class="modal-title-row">
                                    <h2 class="modal-title fw-bold mb-0" id="detalleTitulo{{ $asesoria->id_asesoria }}">Ficha de Asesoría #{{ $asesoria->id_asesoria }}</h2>
                                    <span class="badge asesoria-state-badge asesoria-state-badge--{{ \Illuminate\Support\Str::slug($asesoria->estado) }}">
                                        @if($asesoria->estado === 'Realizada')
                                            <i class="fas fa-check-circle" aria-hidden="true"></i>
                                        @elseif(in_array($asesoria->estado, ['Cancelada', 'Rechazada'], true))
                                            <i class="fas fa-ban" aria-hidden="true"></i>
                                        @endif
                                        {{ $asesoria->estado }}
                                    </span>
                                </div>
                                <p class="asesoria-action-modal-subtitle">Trazabilidad, seguimientos y recursos vinculados</p>
                            </div>
                            <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="asesoria-view-card asesoria-view-data">
                                <div class="asesoria-view-people">
                                    <div class="asesoria-view-person">
                                        <span class="asesoria-view-person-icon"><i class="fas fa-user-graduate" aria-hidden="true"></i></span>
                                        <div class="min-w-0">
                                            <span class="asesoria-view-label">Aprendiz</span>
                                            <span class="asesoria-view-value">{{ $personaAprendiz?->full_name ?? $asesoria->aprendiz?->nickname ?? '—' }}</span>
                                            <span class="asesoria-view-label">{{ $asesoria->aprendiz?->email ?? $personaAprendiz?->personal_email ?? '—' }}</span>
                                        </div>
                                    </div>
                                    <div class="asesoria-view-person">
                                        <span class="asesoria-view-person-icon is-instructor"><i class="fas fa-chalkboard-teacher" aria-hidden="true"></i></span>
                                        <div class="min-w-0">
                                            <span class="asesoria-view-label">Instructor Asignado</span>
                                            <span class="asesoria-view-value">{{ $asesoria->instructor?->nickname ?? '—' }}</span>
                                            <span class="asesoria-view-label">{{ $asesoria->instructor?->email ?? '—' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="asesoria-view-meta">
                                    <div>
                                        <span class="asesoria-view-label">Categoría</span>
                                        <span class="badge rounded-pill text-bg-light border">{{ $asesoria->categoria?->nombre ?? '—' }}</span>
                                    </div>
                                    <div>
                                        <span class="asesoria-view-label">Fecha y Horario</span>
                                        <span class="asesoria-view-value">{{ $asesoria->fecha?->format('d/m/Y') ?? '—' }} | {{ \Carbon\Carbon::parse($asesoria->hora_inicio)->format('g:i a') }} – {{ \Carbon\Carbon::parse($asesoria->hora_fin)->format('g:i a') }}</span>
                                    </div>
                                    <div>
                                        <span class="asesoria-view-label">Fecha de Solicitud</span>
                                        <span>{{ $asesoria->fecha_solicitud?->copy()->timezone('America/Bogota')->format('d/m/Y g:i a') ?? '—' }}</span>
                                    </div>
                                </div>
                            </div>
                            <section class="asesoria-view-card asesoria-view-section">
                                <h3 class="asesoria-view-section-title"><i class="fas fa-align-left" aria-hidden="true"></i>Motivo de la Solicitud</h3>
                                <p class="asesoria-view-reason">{{ $asesoria->motivo ?: 'No se indicó un motivo.' }}</p>
                            </section>
                            @if($asesoria->historial->isNotEmpty())
                                <section class="asesoria-view-card asesoria-view-section">
                                    <h3 class="asesoria-view-section-title"><i class="fas fa-history" aria-hidden="true"></i>Historial ({{ $asesoria->historial->count() }})</h3>
                                    <div class="asesoria-view-history-list">
                                        @foreach($asesoria->historial as $evento)
                                            <article class="asesoria-view-history-item">
                                                <div class="asesoria-view-history-date">{{ $evento->fecha?->copy()->timezone('America/Bogota')->format('d/m/Y g:i a') ?? '—' }}</div>
                                                <div><strong>{{ $evento->accion }}:</strong> {{ $evento->motivo ?: 'Sin motivo adicional.' }}</div>
                                                <div class="asesoria-view-history-actor">Por: {{ $evento->usuario?->nickname ?? 'Sistema' }}</div>
                                            </article>
                                        @endforeach
                                    </div>
                                </section>
                            @endif
                            @if($asesoria->recursos->isNotEmpty())
                                <section class="asesoria-view-card asesoria-view-section">
                                    <h3 class="asesoria-view-section-title"><i class="fas fa-box" aria-hidden="true"></i>Recursos vinculados</h3>
                                    @foreach($asesoria->recursos as $recurso)
                                        <div>{{ $recurso->inventario?->nombre }} · Cantidad: {{ $recurso->cantidad }}</div>
                                    @endforeach
                                </section>
                            @endif
                            @if($asesoria->seguimientos->whereNull('id_asesoria_seguimiento_corrige')->isNotEmpty())
                                <section class="asesoria-view-card asesoria-view-section">
                                    <h3 class="asesoria-view-section-title"><i class="fas fa-clipboard-check" aria-hidden="true"></i>Seguimientos</h3>
                                @foreach($asesoria->seguimientos->whereNull('id_asesoria_seguimiento_corrige') as $seguimiento)
                                    <div class="border rounded-3 bg-white p-3 mb-2">
                                        <div class="small text-muted">{{ $seguimiento->fecha?->copy()->timezone('America/Bogota')->format('d/m/Y g:i A') ?? '—' }} · {{ $seguimiento->usuario?->nickname }}</div>
                                        <div><strong>Avances:</strong> {{ $seguimiento->avances }}</div>
                                        <div><strong>Compromisos:</strong> {{ $seguimiento->compromisos }}</div>
                                        @if($seguimiento->observaciones)<div><strong>Observaciones:</strong> {{ $seguimiento->observaciones }}</div>@endif
                                        @foreach($seguimiento->correcciones as $correccion)
                                            <div class="alert alert-warning py-2 mt-2 mb-0">
                                                <strong>Corrección {{ $correccion->fecha?->copy()->timezone('America/Bogota')->format('d/m/Y g:i A') ?? '—' }}:</strong> {{ $correccion->motivo_correccion }}
                                                <div>{{ $correccion->avances }} · {{ $correccion->compromisos }}</div>
                                            </div>
                                        @endforeach
                                        @if((int) $seguimiento->id_usuario === (int) auth()->id())
                                            <button class="btn btn-sm btn-outline-warning mt-2" type="button" data-bs-toggle="collapse" data-bs-target="#corregir{{ $seguimiento->id_asesoria_seguimiento }}">Corregir seguimiento</button>
                                            <form class="collapse mt-2" id="corregir{{ $seguimiento->id_asesoria_seguimiento }}" method="POST" action="{{ route('controlecp.instructor.asesorias.seguimiento.corregir', $seguimiento->id_asesoria_seguimiento) }}">
                                                @csrf @method('PUT')
                                                <div class="row g-2">
                                                    <div class="col-md-6"><textarea class="form-control" name="avances" required minlength="10" placeholder="Avances corregidos"></textarea></div>
                                                    <div class="col-md-6"><textarea class="form-control" name="compromisos" required minlength="10" placeholder="Compromisos corregidos"></textarea></div>
                                                    <div class="col-12"><textarea class="form-control" name="observaciones" placeholder="Observaciones"></textarea></div>
                                                    <div class="col-12"><input class="form-control" name="motivo_correccion" required placeholder="Motivo de corrección"></div>
                                                    <div class="col-12"><button class="btn btn-warning btn-sm">Guardar corrección</button></div>
                                                </div>
                                            </form>
                                        @endif
                                    </div>
                                @endforeach
                                </section>
                            @endif
                        </div>
                        <div class="modal-footer asesoria-view-modal-footer">
                            <button class="btn px-4" type="button" data-bs-dismiss="modal"><i class="fas fa-times me-2" aria-hidden="true"></i>Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade asesoria-action-modal asesoria-action-modal--rating" id="calificacion{{ $asesoria->id_asesoria }}" tabindex="-1" aria-labelledby="calificacionTitulo{{ $asesoria->id_asesoria }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <span class="asesoria-action-modal-icon"><i class="fas fa-star" aria-hidden="true"></i></span>
                                <div>
                                    <h2 class="modal-title h5 fw-bold mb-0" id="calificacionTitulo{{ $asesoria->id_asesoria }}">Calificación de la asesoría</h2>
                                    <p class="asesoria-action-modal-subtitle">{{ $asesoria->categoria?->nombre ?? 'Asesoría' }} · {{ $personaAprendiz?->full_name ?? $asesoria->aprendiz?->nickname ?? 'Aprendiz' }}</p>
                                </div>
                                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                @if($asesoria->calificacion)
                                    <div class="asesoria-view-card p-3">
                                        <div class="asesoria-rating-stars mb-2" role="img" aria-label="{{ $asesoria->calificacion->estrellas }} de 5 estrellas">
                                            @for($estrella = 1; $estrella <= 5; $estrella++)
                                                <i class="fas fa-star {{ $estrella <= $asesoria->calificacion->estrellas ? '' : 'is-empty' }}" aria-hidden="true"></i>
                                            @endfor
                                        </div>
                                        <div class="fw-bold text-dark">{{ $asesoria->calificacion->estrellas }} de 5 estrellas</div>
                                        <div class="small text-muted">Calificada por {{ $asesoria->calificacion->aprendiz?->nickname ?? $personaAprendiz?->full_name ?? 'el aprendiz' }} · {{ $asesoria->calificacion->created_at?->copy()->timezone('America/Bogota')->format('d/m/Y g:i a') ?? '—' }}</div>
                                        <hr>
                                        <h3 class="h6 fw-bold">Comentario</h3>
                                        <p class="mb-0 text-muted">{{ $asesoria->calificacion->comentario ?: 'El aprendiz no agregó un comentario.' }}</p>
                                    </div>
                                @else
                                    <div class="asesoria-view-card p-4 text-center">
                                        <div class="asesoria-rating-stars justify-content-center mb-3" aria-hidden="true">
                                            @for($estrella = 1; $estrella <= 5; $estrella++)
                                                <i class="fas fa-star is-empty"></i>
                                            @endfor
                                        </div>
                                        <h3 class="h6 fw-bold mb-1">Calificación pendiente</h3>
                                        <p class="text-muted mb-0">El aprendiz aún no ha calificado esta asesoría.</p>
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
        @endforeach

        @if(request('vista') === 'solicitudes')
    <section class="asesoria-instructor-card overflow-hidden">
        <div class="asesoria-instructor-card-header"><h2 class="h5 fw-bold mb-0">Mis asesorías</h2></div>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0 asesoria-instructor-table">
            <thead><tr><th class="ps-3 py-3">Aprendiz</th><th class="py-3">Categoría</th><th class="py-3">Motivo de solicitud</th><th class="py-3">Fecha y hora</th><th class="py-3 text-center">Estado</th><th class="text-end pe-3 py-3">Acciones</th></tr></thead>
            <tbody>
            @forelse($asesorias as $asesoria)
                @php
                    $personaAprendiz = $asesoria->aprendiz?->person;
                @endphp
                <tr>
                    <td class="ps-3 py-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0" style="width:34px;height:34px;background-color:#12394d;font-size:.75rem;">
                                {{ $asesoria->aprendiz?->initials ?? 'AP' }}
                            </div>
                            <div class="min-w-0">
                                <span class="d-block fw-semibold text-truncate" style="max-width:200px;color:#001A29;">
                                    {{ $personaAprendiz?->full_name ?? $asesoria->aprendiz?->nickname ?? 'Aprendiz' }}
                                </span>
                                <small class="text-muted d-block text-truncate" style="max-width:200px;font-size:.72rem;">
                                    {{ $asesoria->aprendiz?->email ?? '—' }}
                                </small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge rounded-pill bg-light text-dark border px-2 py-1 small">{{ $asesoria->categoria?->nombre ?? 'Sin categoría' }}</span>
                    </td>
                    <td class="small" style="min-width:260px;max-width:380px;white-space:normal;overflow-wrap:anywhere;">
                        {{ $asesoria->motivo ?: 'Sin motivo registrado' }}
                    </td>
                    <td>
                        <div class="small">
                            <span class="d-block fw-semibold" style="color:#001A29;">
                                <i class="fas fa-calendar-day me-1 text-muted" aria-hidden="true"></i>{{ $asesoria->fecha?->format('d/m/Y') ?? '—' }}
                            </span>
                            <span class="text-muted" style="font-size:.72rem;">
                                <i class="fas fa-clock me-1 text-muted" aria-hidden="true"></i>{{ str_replace(['am', 'pm'], ['a. m.', 'p. m.'], \Carbon\Carbon::parse($asesoria->hora_inicio)->format('g:i a')) }} - {{ str_replace(['am', 'pm'], ['a. m.', 'p. m.'], \Carbon\Carbon::parse($asesoria->hora_fin)->format('g:i a')) }}
                            </span>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="badge asesoria-state-badge asesoria-state-badge--{{ \Illuminate\Support\Str::slug($asesoria->estado) }}">{{ $asesoria->estado }}</span>
                    </td>
                    <td class="text-end pe-3 py-3"><div class="asesoria-instructor-actions">
                        <button class="asesoria-action asesoria-action-view" type="button" data-bs-toggle="modal" data-bs-target="#detalle{{ $asesoria->id_asesoria }}" title="Ver detalle" aria-label="Ver detalle"><i class="fas fa-eye" aria-hidden="true"></i></button>
                        @if($asesoria->estado !== 'Rechazada')
                            <button class="asesoria-action asesoria-action-rating" type="button" data-bs-toggle="modal" data-bs-target="#calificacion{{ $asesoria->id_asesoria }}" title="{{ $asesoria->calificacion ? 'Ver calificación' : 'Calificación pendiente' }}" aria-label="{{ $asesoria->calificacion ? 'Ver calificación' : 'Calificación pendiente' }}"><i class="fas fa-star" aria-hidden="true"></i></button>
                        @endif
                        @if($asesoria->estado === 'Solicitada')
                            <form method="POST" action="{{ route('controlecp.instructor.asesorias.aceptar', $asesoria->id_asesoria) }}" class="d-inline" onsubmit="return confirm('¿Confirmas que deseas aceptar esta solicitud?');">
                                @csrf @method('PUT')
                                <button class="asesoria-action asesoria-action-accept" type="submit" title="Aceptar solicitud" aria-label="Aceptar solicitud"><i class="fas fa-check"></i></button>
                            </form>
                            <button class="asesoria-action asesoria-action-reject" type="button" data-bs-toggle="modal" data-bs-target="#rechazar{{ $asesoria->id_asesoria }}" title="Rechazar solicitud" aria-label="Rechazar solicitud"><i class="fas fa-times"></i></button>
                        @endif
                        @if($asesoria->estado === 'Programada')
                            <button class="asesoria-action asesoria-action-reschedule" type="button" data-bs-toggle="modal" data-bs-target="#reprogramar{{ $asesoria->id_asesoria }}" title="Reprogramar" aria-label="Reprogramar"><i class="fas fa-calendar-alt" aria-hidden="true"></i></button>
                            <button class="asesoria-action asesoria-action-cancel" type="button" data-bs-toggle="modal" data-bs-target="#cancelar{{ $asesoria->id_asesoria }}" title="Cancelar" aria-label="Cancelar"><i class="fas fa-ban"></i></button>
                            <button class="asesoria-action asesoria-action-followup" type="button" data-bs-toggle="modal" data-bs-target="#seguimiento{{ $asesoria->id_asesoria }}" title="Registrar seguimiento" aria-label="Registrar seguimiento"><i class="fas fa-clipboard-check"></i></button>
                        @endif
                        @if(in_array($asesoria->estado, ['Programada','Realizada'], true))
                                    <button class="asesoria-action asesoria-action-resource" type="button" data-bs-toggle="modal" data-bs-target="#recurso{{ $asesoria->id_asesoria }}" title="Agregar recurso" aria-label="Agregar recurso"><i class="fas fa-box"></i></button>
                                @endif
                            </div></td>
                        </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-5">No tienes asesorías que coincidan con los filtros.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="asesoria-pagination">
            @if ($asesorias->hasPages())
                <nav aria-label="Paginación de asesorías">
                    <ul class="pagination justify-content-center">
                        @if ($asesorias->onFirstPage())
                            <li class="page-item disabled"><span class="page-link" aria-hidden="true">&lsaquo;</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $asesorias->previousPageUrl() }}" aria-label="Página anterior">&lsaquo;</a></li>
                        @endif
                        @php
                                $ultimaPaginaMostrada = 0;
                            @endphp
                        @for ($pagina = 1; $pagina <= $asesorias->lastPage(); $pagina++)
                            @if ($pagina === 1 || $pagina === $asesorias->lastPage() || abs($pagina - $asesorias->currentPage()) <= 1)
                                @if ($ultimaPaginaMostrada > 0 && $pagina > $ultimaPaginaMostrada + 1)
                                    <li class="page-item disabled"><span class="page-link">…</span></li>
                                @endif
                                <li class="page-item {{ $pagina === $asesorias->currentPage() ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $asesorias->url($pagina) }}" @if($pagina === $asesorias->currentPage()) aria-current="page" @endif>{{ $pagina }}</a>
                                </li>
                                @php
                                    $ultimaPaginaMostrada = $pagina;
                                @endphp
                            @endif
                        @endfor
                        @if ($asesorias->hasMorePages())
                            <li class="page-item"><a class="page-link" href="{{ $asesorias->nextPageUrl() }}" aria-label="Página siguiente">&rsaquo;</a></li>
                        @else
                            <li class="page-item disabled"><span class="page-link" aria-hidden="true">&rsaquo;</span></li>
                        @endif
                    </ul>
                </nav>
            @endif
        </div>
    </section>
    @endif

    <div class="modal fade" id="nuevaSolicitud" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content">
        <form method="POST" action="{{ route('controlecp.instructor.asesorias.store') }}" id="formNuevaSolicitud">
            @csrf
            <div class="modal-header"><h2 class="modal-title h5">Registrar solicitud en nombre del aprendiz</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Aprendiz *</label><select class="form-select" name="id_usuario_aprendiz" required><option value="">Selecciona un aprendiz</option>@foreach($aprendices as $aprendiz)<option value="{{ $aprendiz->id }}">{{ $aprendiz->person?->full_name ?? $aprendiz->nickname }} · {{ $aprendiz->person?->document_number ?? $aprendiz->email }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label class="form-label">Categoría *</label><select class="form-select disponibilidad-input" name="id_categoria_asesoria" required><option value="">Selecciona</option>@foreach($categorias as $categoria)<option value="{{ $categoria->id_categoria_asesoria }}">{{ $categoria->nombre }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label class="form-label">Fecha *</label><input class="form-control disponibilidad-input" type="date" name="fecha" min="{{ today()->toDateString() }}" required></div>
                    <div class="col-md-4"><label class="form-label">Desde *</label><input class="form-control disponibilidad-input" type="time" name="hora_inicio" min="08:00" max="14:59" step="60" required></div>
                    <div class="col-md-4"><label class="form-label">Hasta *</label><input class="form-control disponibilidad-input" type="time" name="hora_fin" min="08:00" max="15:00" step="60" required></div>
                    <div class="col-12"><label class="form-label">Motivo de la asesoría *</label><textarea class="form-control" name="motivo" rows="3" maxlength="2000" required></textarea></div>
                    <div class="col-12"><button class="btn btn-outline-primary" type="button" id="buscarInstructores">Buscar disponibilidad</button><span class="small text-muted ms-2" id="estadoDisponibilidad"></span></div>
                    <div class="col-12"><label class="form-label">Instructor disponible *</label><select class="form-select" name="id_usuario_instructor" id="instructorDisponible" required disabled><option value="">Primero busca disponibilidad</option></select></div>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-outline-secondary rounded-pill" type="button" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-success rounded-pill" type="submit" id="guardarSolicitud" disabled>Guardar y reservar</button></div>
        </form>
    </div></div></div>

    @foreach($asesorias as $asesoria)
        @if($asesoria->estado === 'Solicitada')
            <div class="modal fade asesoria-action-modal asesoria-action-modal--reject" id="rechazar{{ $asesoria->id_asesoria }}" tabindex="-1" aria-labelledby="rechazarTitulo{{ $asesoria->id_asesoria }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('controlecp.instructor.asesorias.rechazar', $asesoria->id_asesoria) }}">
                            @csrf @method('PUT')
                            <div class="modal-header">
                                <span class="asesoria-action-modal-icon"><i class="fas fa-ban" aria-hidden="true"></i></span>
                                <div>
                                    <h2 class="modal-title h5 fw-bold mb-0" id="rechazarTitulo{{ $asesoria->id_asesoria }}">Rechazar solicitud</h2>
                                    <p class="asesoria-action-modal-subtitle">Notificaremos al aprendiz y liberaremos el horario.</p>
                                </div>
                                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <label class="form-label" for="motivoRechazo{{ $asesoria->id_asesoria }}">Motivo *</label>
                                <textarea class="form-control" id="motivoRechazo{{ $asesoria->id_asesoria }}" name="motivo" minlength="5" maxlength="1000" placeholder="Explica el motivo del rechazo..." required></textarea>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Volver</button>
                                <button class="btn btn-danger" type="submit"><i class="fas fa-ban me-1" aria-hidden="true"></i>Rechazar solicitud</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
        @if($asesoria->estado === 'Programada')
            <div class="modal fade asesoria-action-modal asesoria-action-modal--reschedule" id="reprogramar{{ $asesoria->id_asesoria }}" tabindex="-1" aria-labelledby="reprogramarTitulo{{ $asesoria->id_asesoria }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('controlecp.instructor.asesorias.reprogramar', $asesoria->id_asesoria) }}">
                            @csrf @method('PUT')
                            <div class="modal-header">
                                <span class="asesoria-action-modal-icon"><i class="fas fa-calendar-alt" aria-hidden="true"></i></span>
                                <div>
                                    <h2 class="modal-title h5 fw-bold mb-0" id="reprogramarTitulo{{ $asesoria->id_asesoria }}">Reprogramar asesoría</h2>
                                    <p class="asesoria-action-modal-subtitle">Selecciona la nueva fecha y el horario de atención.</p>
                                </div>
                                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold" for="reprogramarFecha{{ $asesoria->id_asesoria }}">Nueva fecha *</label>
                                    <input class="form-control" id="reprogramarFecha{{ $asesoria->id_asesoria }}" type="date" name="fecha" min="{{ today()->toDateString() }}" required>
                                </div>
                                <div class="row g-3">
                                    <div class="col-6"><label class="form-label fw-semibold" for="reprogramarInicio{{ $asesoria->id_asesoria }}">Desde *</label><input class="form-control" id="reprogramarInicio{{ $asesoria->id_asesoria }}" type="time" name="hora_inicio" min="08:00" max="14:59" step="60" required></div>
                                    <div class="col-6"><label class="form-label fw-semibold" for="reprogramarFin{{ $asesoria->id_asesoria }}">Hasta *</label><input class="form-control" id="reprogramarFin{{ $asesoria->id_asesoria }}" type="time" name="hora_fin" min="08:00" max="15:00" step="60" required></div>
                                </div>
                                <div class="mt-3">
                                    <label class="form-label fw-semibold" for="reprogramarMotivo{{ $asesoria->id_asesoria }}">Motivo *</label>
                                    <textarea class="form-control" id="reprogramarMotivo{{ $asesoria->id_asesoria }}" name="motivo" maxlength="1000" placeholder="Indica por qué necesitas reprogramar..." required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                                <button class="btn btn-success" type="submit"><i class="fas fa-calendar-check me-1" aria-hidden="true"></i>Guardar cambio</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal fade asesoria-action-modal asesoria-action-modal--cancel" id="cancelar{{ $asesoria->id_asesoria }}" tabindex="-1" aria-labelledby="cancelarTitulo{{ $asesoria->id_asesoria }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('controlecp.instructor.asesorias.cancelar', $asesoria->id_asesoria) }}">
                            @csrf @method('PUT')
                            <div class="modal-header">
                                <span class="asesoria-action-modal-icon"><i class="fas fa-ban" aria-hidden="true"></i></span>
                                <div>
                                    <h2 class="modal-title h5 fw-bold mb-0" id="cancelarTitulo{{ $asesoria->id_asesoria }}">Cancelar asesoría</h2>
                                    <p class="asesoria-action-modal-subtitle">Se notificará al aprendiz y se liberará la franja.</p>
                                </div>
                                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <label class="form-label fw-semibold" for="cancelarMotivo{{ $asesoria->id_asesoria }}">Motivo *</label>
                                <textarea class="form-control" id="cancelarMotivo{{ $asesoria->id_asesoria }}" name="motivo" maxlength="1000" placeholder="Explica el motivo de la cancelación..." required></textarea>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Volver</button>
                                <button class="btn btn-danger" type="submit"><i class="fas fa-ban me-1" aria-hidden="true"></i>Confirmar cancelación</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal fade asesoria-action-modal asesoria-action-modal--followup" id="seguimiento{{ $asesoria->id_asesoria }}" tabindex="-1" aria-labelledby="seguimientoTitulo{{ $asesoria->id_asesoria }}" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('controlecp.instructor.asesorias.seguimiento', $asesoria->id_asesoria) }}">
                            @csrf
                            <div class="modal-header">
                                <span class="asesoria-action-modal-icon"><i class="fas fa-clipboard-check" aria-hidden="true"></i></span>
                                <div>
                                    <h2 class="modal-title h5 fw-bold mb-0" id="seguimientoTitulo{{ $asesoria->id_asesoria }}">Registrar seguimiento</h2>
                                    <p class="asesoria-action-modal-subtitle">Registra avances y compromisos para finalizar la asesoría.</p>
                                </div>
                                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold" for="seguimientoAvances{{ $asesoria->id_asesoria }}">Avances *</label>
                                    <textarea class="form-control" id="seguimientoAvances{{ $asesoria->id_asesoria }}" name="avances" minlength="10" maxlength="5000" placeholder="Describe los avances alcanzados..." required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold" for="seguimientoCompromisos{{ $asesoria->id_asesoria }}">Compromisos *</label>
                                    <textarea class="form-control" id="seguimientoCompromisos{{ $asesoria->id_asesoria }}" name="compromisos" minlength="10" maxlength="5000" placeholder="Escribe los compromisos acordados..." required></textarea>
                                </div>
                                <div>
                                    <label class="form-label fw-semibold" for="seguimientoObservaciones{{ $asesoria->id_asesoria }}">Observaciones</label>
                                    <textarea class="form-control" id="seguimientoObservaciones{{ $asesoria->id_asesoria }}" name="observaciones" maxlength="5000" placeholder="Observaciones adicionales (opcional)"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                                <button class="btn btn-success" type="submit"><i class="fas fa-check me-1" aria-hidden="true"></i>Guardar y finalizar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
        @if(in_array($asesoria->estado, ['Programada','Realizada'], true))
            <div class="modal fade asesoria-action-modal asesoria-action-modal--resource" id="recurso{{ $asesoria->id_asesoria }}" tabindex="-1" aria-labelledby="recursoTitulo{{ $asesoria->id_asesoria }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('controlecp.instructor.asesorias.recursos', $asesoria->id_asesoria) }}">
                            @csrf
                            <div class="modal-header">
                                <span class="asesoria-action-modal-icon"><i class="fas fa-box" aria-hidden="true"></i></span>
                                <div>
                                    <h2 class="modal-title h5 fw-bold mb-0" id="recursoTitulo{{ $asesoria->id_asesoria }}">Agregar recurso</h2>
                                    <p class="asesoria-action-modal-subtitle">Asigna un recurso utilizado durante la asesoría.</p>
                                </div>
                                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold" for="recursoInventario{{ $asesoria->id_asesoria }}">Recurso disponible *</label>
                                    <select class="form-select" id="recursoInventario{{ $asesoria->id_asesoria }}" name="id_inventario" required>
                                        <option value="">Selecciona un recurso</option>
                                        @foreach($recursos as $recurso)
                                            <option value="{{ $recurso->id_inventario }}">{{ $recurso->nombre }} · disponibles: {{ $recurso->cantidad_disponible }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label fw-semibold" for="recursoCantidad{{ $asesoria->id_asesoria }}">Cantidad *</label>
                                    <input class="form-control" id="recursoCantidad{{ $asesoria->id_asesoria }}" type="number" name="cantidad" min="1" value="1" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                                <button class="btn btn-success" type="submit"><i class="fas fa-box me-1" aria-hidden="true"></i>Agregar recurso</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
</div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const searchButton = document.getElementById('buscarInstructores');
    const requestForm = document.getElementById('formNuevaSolicitud');
    const instructorSelect = document.getElementById('instructorDisponible');
    const saveButton = document.getElementById('guardarSolicitud');
    const status = document.getElementById('estadoDisponibilidad');
    searchButton?.addEventListener('click', async () => {
        const data = new FormData(requestForm);
        if (!data.get('id_categoria_asesoria') || !data.get('fecha') || !data.get('hora_inicio') || !data.get('hora_fin') || data.get('hora_fin') <= data.get('hora_inicio')) {
            status.textContent = 'Completa categoría, fecha y un rango de horas válido.';
            instructorSelect.disabled = true;
            saveButton.disabled = true;
            return;
        }
        status.textContent = 'Consultando disponibilidad…';
        instructorSelect.disabled = true;
        saveButton.disabled = true;
        const url = new URL(@json(route('controlecp.instructor.asesorias.disponibles')), window.location.origin);
        ['id_categoria_asesoria','fecha','hora_inicio','hora_fin'].forEach(key => url.searchParams.set(key === 'id_categoria_asesoria' ? 'categoria_id' : key, data.get(key)));
        try {
            const response = await fetch(url, {headers: {'Accept': 'application/json'}});
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'No se pudo consultar la disponibilidad.');
            instructorSelect.replaceChildren(new Option(result.instructores.length ? 'Selecciona un instructor' : 'Sin disponibilidad', ''));
            result.instructores.forEach(instructor => instructorSelect.add(new Option(`${instructor.nombre} · ${instructor.email}`, instructor.id)));
            instructorSelect.disabled = result.instructores.length === 0;
            saveButton.disabled = result.instructores.length === 0;
            status.textContent = result.mensaje || `${result.instructores.length} instructor(es) disponible(s).`;
        } catch (error) {
            status.textContent = error.message;
        }
    });
    instructorSelect?.addEventListener('change', () => saveButton.disabled = !instructorSelect.value);
    requestForm?.querySelectorAll('.disponibilidad-input').forEach(input => input.addEventListener('change', () => {
        instructorSelect.disabled = true;
        saveButton.disabled = true;
        instructorSelect.replaceChildren(new Option('Vuelve a buscar disponibilidad', ''));
        status.textContent = '';
    }));

    const editAvailabilityModal = document.getElementById('modalEditarDisponibilidadInstructor');
    const editAvailabilityForm = document.getElementById('formEditarDisponibilidadInstructor');
    const updateAvailabilityUrl = @json(route('controlecp.instructor.asesorias.disponibilidad.franjas.update', ['id' => '__ID__']));
    const openEditAvailability = (id, day, start, end) => {
        editAvailabilityForm.action = updateAvailabilityUrl.replace('__ID__', encodeURIComponent(id));
        document.getElementById('editarDisponibilidadDiaInstructor').textContent = `Día: ${day}`;
        document.getElementById('editarDisponibilidadHoraInicioInstructor').value = start;
        document.getElementById('editarDisponibilidadHoraFinInstructor').value = end;
        bootstrap.Modal.getOrCreateInstance(editAvailabilityModal).show();
    };

    document.querySelectorAll('[data-editar-disponibilidad]').forEach(button => {
        button.addEventListener('click', () => openEditAvailability(
            button.dataset.id,
            button.dataset.dia,
            button.dataset.inicio,
            button.dataset.fin
        ));
    });
})();
@if(in_array(session('modal_abrir'), ['modalDisponibilidadInstructor', 'modalEditarDisponibilidadInstructor'], true))
document.addEventListener('DOMContentLoaded', () => {
    const availabilityModal = document.getElementById('modalDisponibilidadInstructor');
    if (!availabilityModal) return;
    availabilityModal.addEventListener('shown.bs.modal', () => {
        @if(session('modal_abrir') === 'modalEditarDisponibilidadInstructor' && session('modal_id'))
            const editButton = document.querySelector(`[data-editar-disponibilidad][data-id="{{ session('modal_id') }}"]`);
            if (editButton) {
                editButton.click();
                document.getElementById('editarDisponibilidadHoraInicioInstructor').value = @json(old('hora_inicio'));
                document.getElementById('editarDisponibilidadHoraFinInstructor').value = @json(old('hora_fin'));
            }
        @endif
    }, {once: true});
    bootstrap.Modal.getOrCreateInstance(availabilityModal).show();
});
@endif
</script>
@endpush
