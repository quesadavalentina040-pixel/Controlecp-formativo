@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    /* ── Tarjetas métricas ── */
    .pqr-stat-card {
        background: #fff;
        border: 2px solid transparent;
        border-radius: 18px;
        padding: 1rem 1.25rem;
        transition: transform .2s, box-shadow .2s;
        box-shadow: 0 2px 10px rgba(0,26,41,.04);
        position: relative; overflow: hidden;
    }
    .pqr-stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,26,41,.1); }

    .pqr-stat-icon {
        width: 38px; height: 38px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; flex-shrink: 0;
    }
    .pqr-stat-label { font-size: .95rem; color: #475569; font-weight: 500; }
    .pqr-stat-value { font-size: 1.7rem; font-weight: 800; line-height: 1; margin-top: .15rem; }
    .pqr-stat-subtitle { font-size: .9rem; color: #64748b; }

    /* ── Botones de acción ── */
    .btn-tbl-action {
        width: 34px; height: 34px; padding: 0;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 9px; font-size: 0.82rem;
        border: 1px solid #E2E8F0; background: #FFFFFF;
        transition: all .15s ease; text-decoration: none; cursor: pointer;
    }
    .btn-tbl-action:hover { transform: scale(1.1); box-shadow: 0 3px 10px rgba(0,26,41,.12); }
    .btn-tbl-action-view {
        color:#4F46E5;
        background:#EEF2FF;
        border-color:#C7D2FE;
    }
    .btn-tbl-action-view:hover,
    .btn-tbl-action-view:focus-visible {
        color:#fff;
        background:#4F46E5;
        border-color:#4F46E5;
    }
    .btn-tbl-action-history {
        color:#7C3AED;
        background:#F5F3FF;
        border-color:#DDD6FE;
    }
    .btn-tbl-action-history:hover,
    .btn-tbl-action-history:focus-visible {
        color:#fff;
        background:#7C3AED;
        border-color:#7C3AED;
    }
    .btn-tbl-action-trace {
        color:#0891B2;
        background:#ECFEFF;
        border-color:#A5F3FC;
    }
    .btn-tbl-action-trace:hover,
    .btn-tbl-action-trace:focus-visible {
        color:#fff;
        background:#0891B2;
        border-color:#0891B2;
    }
    .btn-tbl-action-response {
        color:#16A34A;
        background:#F0FDF4;
        border-color:#BBF7D0;
    }
    .btn-tbl-action-response:hover,
    .btn-tbl-action-response:focus-visible {
        color:#fff;
        background:#16A34A;
        border-color:#16A34A;
    }
    .btn-tbl-action-trace {
        color:#0891B2;
        background:#ECFEFF;
        border-color:#A5F3FC;
    }
    .btn-tbl-action-trace:hover,
    .btn-tbl-action-trace:focus-visible {
        color:#fff;
        background:#0891B2;
        border-color:#0891B2;
    }
    .pqr-trace-modal .modal-content { overflow:hidden; border-radius:22px; }
    .pqr-trace-modal .modal-dialog { width:calc(100% - 2rem); max-width:860px; }
    .pqr-trace-modal .modal-header {
        padding:1.35rem 1.5rem;
        color:#fff;
        background:linear-gradient(135deg,#001A29 0%,#12394d 100%);
        border:0;
    }
    .pqr-trace-modal-icon {
        display:flex;
        width:46px;
        height:46px;
        flex:0 0 46px;
        align-items:center;
        justify-content:center;
        border:1px solid rgba(103,232,249,.45);
        border-radius:15px;
        background:rgba(8,145,178,.18);
        color:#67e8f9;
        font-size:1.15rem;
    }
    .pqr-trace-modal .modal-body { padding:1.5rem; overflow-x:hidden; }
    .pqr-trace-modal .modal-footer {
        padding:1rem 1.5rem;
        border-top:1px solid #edf1f5;
        background:#fbfcfd;
    }
    .pqr-trace-timeline { position:relative; padding-left:1.7rem; }
    .pqr-trace-timeline::before {
        position:absolute;
        top:.35rem;
        bottom:.35rem;
        left:.42rem;
        width:2px;
        background:#cffafe;
        content:"";
    }
    .pqr-trace-event { position:relative; padding:0 0 1.4rem; }
    .pqr-trace-event:last-child { padding-bottom:0; }
    .pqr-trace-event::before {
        position:absolute;
        top:.25rem;
        left:-1.64rem;
        width:13px;
        height:13px;
        border:3px solid #fff;
        border-radius:50%;
        background:#06b6d4;
        box-shadow:0 0 0 2px #a5f3fc;
        content:"";
    }
    .pqr-trace-event-heading {
        display:flex;
        flex-wrap:wrap;
        align-items:baseline;
        gap:.25rem .5rem;
        margin-bottom:.2rem;
    }
    .pqr-trace-event-title { color:#1e293b; font-weight:700; overflow-wrap:anywhere; }
    .pqr-trace-event-date { color:#64748b; font-size:.88rem; }
    .pqr-trace-event-detail { color:#475569; white-space:pre-wrap; overflow-wrap:anywhere; }
    .pqr-trace-empty {
        padding:1rem;
        border:1px dashed #a5f3fc;
        border-radius:14px;
        background:#f0fdff;
        color:#64748b;
        text-align:center;
    }
    .pqr-response-modal .modal-content { overflow:hidden; border-radius:22px; }
    .pqr-response-modal .modal-header {
        padding:1.35rem 1.5rem;
        color:#fff;
        background:linear-gradient(135deg,#001A29 0%,#12394d 100%);
        border:0;
    }
    .pqr-response-modal-icon {
        display:flex;
        width:46px;
        height:46px;
        flex:0 0 46px;
        align-items:center;
        justify-content:center;
        border:1px solid rgba(134,239,172,.45);
        border-radius:15px;
        background:rgba(22,163,74,.18);
        color:#86efac;
        font-size:1.15rem;
    }
    .pqr-response-modal .modal-body { padding:1.5rem; }
    .pqr-response-modal .modal-footer {
        padding:1rem 1.5rem;
        border-top:1px solid #edf1f5;
        background:#fbfcfd;
    }
    .pqr-response-empty {
        padding:2rem 1rem;
        color:#64748b;
        text-align:center;
    }
    .pqr-response-empty-icon { color:#64748b; font-size:2rem; }
    .pqr-response-item {
        padding:1rem;
        border:1px solid #dcfce7;
        border-left:4px solid #16a34a;
        border-radius:14px;
        background:#f7fdf5;
    }
    .pqr-response-text { color:#334155; white-space:pre-wrap; overflow-wrap:anywhere; }
    .pqr-instructor-detail-modal .modal-content { overflow:hidden; border-radius:22px; }
    .pqr-instructor-detail-modal .modal-header {
        padding:1.35rem 1.5rem;
        color:#fff;
        background:linear-gradient(135deg,#001A29 0%,#12394d 100%);
        border:0;
    }
    .pqr-instructor-detail-icon {
        display:flex;
        width:46px;
        height:46px;
        flex:0 0 46px;
        align-items:center;
        justify-content:center;
        border:1px solid rgba(147,197,253,.45);
        border-radius:15px;
        background:rgba(37,99,235,.18);
        color:#93c5fd;
        font-size:1.15rem;
    }
    .pqr-instructor-detail-modal .modal-body { padding:1.5rem; }
    .pqr-instructor-detail-meta {
        padding:1rem;
        border:1px solid #e8edf2;
        border-radius:15px;
        background:#f8fafc;
    }
    .pqr-instructor-detail-label {
        display:block;
        margin-bottom:.2rem;
        color:#64748b;
        font-size:.75rem;
        font-weight:700;
        letter-spacing:.03em;
        text-transform:uppercase;
    }
    .pqr-instructor-detail-description {
        padding:1rem;
        border:1px solid #e8edf2;
        border-radius:15px;
        background:#fff;
        color:#334155;
        line-height:1.65;
        white-space:pre-wrap;
        overflow-wrap:anywhere;
    }
    .pqr-instructor-detail-modal .modal-footer {
        padding:1rem 1.5rem;
        border-top:1px solid #edf1f5;
        background:#fbfcfd;
    }
    .instructor-pqr-table th { font-size:.84rem; }
    .instructor-pqr-table .btn-tbl-action {
        width:40px;
        height:40px;
        border-radius:11px;
        font-size:1rem;
    }
    .pqr-admin-table th { font-size:.84rem; }
    .pqr-manage-icon {
        display:inline-flex;
        width:38px;
        height:38px;
        align-items:center;
        justify-content:center;
        padding:0;
        border:1px solid #93c5fd;
        border-radius:11px;
        background:#eff6ff;
        color:#2563eb;
        transition:transform .18s ease,background-color .18s ease,color .18s ease,box-shadow .18s ease;
    }
    .pqr-manage-icon:hover,
    .pqr-manage-icon:focus-visible {
        transform:translateY(-2px);
        box-shadow:0 5px 14px rgba(37,99,235,.28);
    }
    .pqr-manage-icon-history { color:#7c3aed; background:#f5f3ff; border-color:#ddd6fe; }
    .pqr-manage-icon-history:hover,
    .pqr-manage-icon-history:focus-visible { border-color:#7c3aed; background:#7c3aed; color:#fff; }
    .pqr-manage-icon-status { color:#d97706; background:#fffbeb; border-color:#fde68a; }
    .pqr-manage-icon-status:hover,
    .pqr-manage-icon-status:focus-visible { border-color:#d97706; background:#d97706; color:#fff; }
    .pqr-manage-icon-reply { color:#16a34a; background:#f0fdf4; border-color:#bbf7d0; }
    .pqr-manage-icon-reply:hover,
    .pqr-manage-icon-reply:focus-visible { border-color:#16a34a; background:#16a34a; color:#fff; }
    .pqr-status-modal .modal-content,
    .pqr-reply-modal .modal-content,
    .pqr-history-modal .modal-content,
    .pqr-detail-modal .modal-content { overflow:hidden; border-radius:22px; }
    .pqr-status-modal .modal-header,
    .pqr-reply-modal .modal-header,
    .pqr-history-modal .modal-header,
    .pqr-detail-modal .modal-header {
        padding:1.35rem 1.5rem;
        color:#fff;
        background:linear-gradient(135deg,#001A29 0%,#12394d 100%);
        border:0;
    }
    .pqr-status-modal-icon,
    .pqr-reply-modal-icon,
    .pqr-history-modal-icon,
    .pqr-detail-modal-icon {
        display:flex;
        width:46px;
        height:46px;
        flex:0 0 46px;
        align-items:center;
        justify-content:center;
        border:1px solid rgba(147,197,253,.45);
        border-radius:15px;
        background:rgba(37,99,235,.18);
        color:#93c5fd;
        font-size:1.15rem;
    }
    .pqr-status-modal-icon { border-color:rgba(253,230,138,.45); background:rgba(245,158,11,.16); color:#fbbf24; }
    .pqr-reply-modal-icon { border-color:rgba(134,239,172,.45); background:rgba(22,163,74,.18); color:#86efac; }
    .pqr-history-modal-icon { border-color:rgba(196,181,253,.45); background:rgba(124,58,237,.18); color:#c4b5fd; }
    .pqr-status-modal .modal-body,
    .pqr-reply-modal .modal-body,
    .pqr-history-modal .modal-body,
    .pqr-detail-modal .modal-body { padding:1.5rem; }
    .pqr-status-modal .form-select,
    .pqr-status-modal .form-control,
    .pqr-reply-modal textarea.form-control {
        min-height:46px;
        border:1px solid #dbe3eb;
        border-radius:12px;
        box-shadow:none;
    }
    .pqr-status-modal textarea.form-control { min-height:112px; resize:vertical; }
    .pqr-reply-modal textarea.form-control { min-height:150px; resize:vertical; }
    .pqr-status-modal .form-select:focus,
    .pqr-status-modal .form-control:focus,
    .pqr-reply-modal textarea.form-control:focus {
        border-color:#39A900;
        box-shadow:0 0 0 .2rem rgba(57,169,0,.13);
    }
    .pqr-status-modal .modal-footer,
    .pqr-reply-modal .modal-footer,
    .pqr-history-modal .modal-footer,
    .pqr-detail-modal .modal-footer {
        padding:1rem 1.5rem;
        border-top:1px solid #edf1f5;
        background:#fbfcfd;
    }
    .pqr-status-save,
    .pqr-reply-send {
        min-height:42px;
        padding:.55rem 1.15rem;
        border:0;
        border-radius:999px;
        color:#fff;
        font-weight:700;
    }
    .pqr-status-save { background:#39A900; }
    .pqr-status-save:hover { background:#2f8f00; color:#fff; }
    .pqr-reply-send { background:#16a34a; }
    .pqr-reply-send:hover { background:#15803d; color:#fff; }
    .pqr-history-timeline { position:relative; padding-left:1.7rem; }
    .pqr-history-timeline::before {
        position:absolute;
        top:.35rem;
        bottom:.35rem;
        left:.42rem;
        width:2px;
        background:#e9d5ff;
        content:"";
    }
    .pqr-history-event { position:relative; padding:0 0 1.4rem; }
    .pqr-history-event:last-child { padding-bottom:0; }
    .pqr-history-event::before {
        position:absolute;
        top:.25rem;
        left:-1.64rem;
        width:13px;
        height:13px;
        border:3px solid #fff;
        border-radius:50%;
        background:#8b5cf6;
        box-shadow:0 0 0 2px #c4b5fd;
        content:"";
    }
    .pqr-history-event-heading { display:flex; flex-wrap:wrap; align-items:baseline; gap:.25rem .5rem; margin-bottom:.2rem; }
    .pqr-history-event-status { color:#1e293b; font-weight:700; }
    .pqr-history-event-date { color:#64748b; font-size:.88rem; }
    .pqr-history-event-note { color:#475569; white-space:pre-wrap; overflow-wrap:anywhere; }
    .pqr-history-empty {
        padding:1rem;
        border:1px dashed #c4b5fd;
        border-radius:14px;
        background:#faf8ff;
        color:#64748b;
        text-align:center;
    }
    .pqr-detail-meta { padding:1rem; border:1px solid #e8edf2; border-radius:15px; background:#f8fafc; }
    .pqr-detail-label {
        display:block;
        margin-bottom:.2rem;
        color:#64748b;
        font-size:.75rem;
        font-weight:700;
        letter-spacing:.03em;
        text-transform:uppercase;
    }
    .pqr-detail-description {
        padding:1rem;
        border:1px solid #e8edf2;
        border-radius:15px;
        background:#fff;
        color:#334155;
        line-height:1.65;
        white-space:pre-wrap;
        overflow-wrap:anywhere;
    }
    .pqr-detail-response { padding:1rem; border:1px solid #dcfce7; border-left:4px solid #39A900; border-radius:14px; background:#f7fdf5; }
    .pqr-pagination { padding:1rem; border-top:1px solid #eef2f5; background:#fff; }
    .pqr-pagination .pagination { margin:0; }
    .pqr-pagination .page-link {
        display:flex;
        min-width:42px;
        height:42px;
        align-items:center;
        justify-content:center;
        border:1px solid #d5e7db;
        border-right:0;
        background:#fff;
        color:#39A900;
        font-weight:700;
        text-decoration:none;
        box-shadow:none;
    }
    .pqr-pagination .page-item:first-child .page-link { border-radius:8px 0 0 8px; }
    .pqr-pagination .page-item:last-child .page-link { border-right:1px solid #d5e7db; border-radius:0 8px 8px 0; }
    .pqr-pagination .page-item.active .page-link { border-color:#39A900; background:#39A900; color:#fff; }
    .pqr-pagination .page-item:not(.active):not(.disabled) .page-link:hover { background:#f0fdf4; color:#318f00; }
    .pqr-pagination .page-item.disabled .page-link { background:#f8fafc; color:#94a3b8; }
    .btn-tbl-action:disabled, .btn-tbl-action[disabled] {
        opacity: .4; cursor: not-allowed; pointer-events: none;
    }

    /* ── Avatar ── */
    .av-badge {
        width: 38px; height: 38px; border-radius: 50%;
        background: #E2F5D7; color: #39A900;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: .88rem;
        border: 1.5px solid #A3E070; flex-shrink: 0;
    }

    /* ── Sección instructores (solo lectura) ── */
    .section-readonly {
        background: #FAFBFD;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        overflow: hidden;
    }
    .section-readonly .tbl-body-row td { color: #64748B; }

    /* ── Tabla ── */
    .tbl-head-row th {
        background: #F8FAFC; color: #475569;
        font-size: .76rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .4px;
        border-bottom: 2px solid #E2E8F0;
        padding: .85rem 1rem;
    }
    .tbl-head-row th:first-child { padding-left: 1.5rem; }
    .tbl-head-row th:last-child  { padding-right: 1.5rem; }
    .tbl-body-row td {
        padding: .85rem 1rem; border-bottom: 1px solid #F1F5F9;
        vertical-align: middle;
    }
    .tbl-body-row td:first-child { padding-left: 1.5rem; }
    .tbl-body-row td:last-child  { padding-right: 1.5rem; }
    .tbl-body-row:last-child td  { border-bottom: none; }
    .tbl-body-row:hover td       { background: #FAFBFF; }

    .pqr-filter-card {
        padding: 1rem 1.15rem;
        margin-bottom: 1.5rem;
        background: #001A29;
        border-radius: 18px;
        box-shadow: 0 5px 20px rgba(0,26,41,.08);
    }
    .pqr-filter-form {
        display: grid;
        grid-template-columns: minmax(200px, 1.4fr) minmax(150px, 1fr) minmax(160px, 1fr) minmax(220px, 1.4fr) 160px;
        align-items: end;
        gap: .75rem;
    }
    .pqr-filter-field { min-width: 0; }
    .pqr-filter-field label {
        display: block;
        margin: 0 0 .3rem;
        color: #fff;
        font-size: .75rem;
        font-weight: 700;
    }
    .pqr-filter-field .form-control,
    .pqr-filter-field .form-select {
        width: 100%;
        min-width: 0;
        height: 44px;
        padding: .55rem .8rem;
        border: 1px solid #fff;
        border-radius: 10px;
        background-color: #fff;
        color: #001A29;
        font-size: .88rem;
        box-shadow: none;
    }
    .pqr-filter-field .input-group {
        flex-wrap: nowrap;
    }
    .pqr-filter-field .input-group-text {
        height: 44px;
        padding: 0 .8rem;
        border: 1px solid #fff;
        border-right: 0;
        border-radius: 10px 0 0 10px;
        background: #fff;
        color: #001A29;
    }
    .pqr-filter-field .input-group .form-control {
        border-left: 0;
        border-radius: 0 10px 10px 0;
    }
    .pqr-filter-actions {
        display: flex;
        gap: .55rem;
        min-width: 0;
    }
    .pqr-filter-actions .btn-filter {
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
    .pqr-filter-actions .btn-filter:hover {
        background: #318f00;
        color: #fff;
    }
    .pqr-filter-actions .btn-reset {
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
    .pqr-filter-actions .btn-reset:hover {
        background: #f1f5f9;
        color: #001A29;
    }
    @media (max-width: 1100px) {
        .pqr-filter-form { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .pqr-filter-actions { grid-column: span 2; justify-content: flex-end; }
        .pqr-filter-actions .btn-filter { flex: 0 0 200px; }
    }
    @media (max-width: 520px) {
        .pqr-filter-form { grid-template-columns: minmax(0, 1fr); }
        .pqr-filter-actions { grid-column: auto; }
        .pqr-filter-actions .btn-filter { flex: 1; }
    }

    /* ── Badge solo lectura ── */
    .badge-readonly {
        background: #F1F5F9; color: #94A3B8;
        border: 1px dashed #CBD5E1;
        border-radius: 8px;
        font-size: .7rem; padding: .3rem .6rem;
        display: inline-flex; align-items: center; gap: .3rem;
    }

    /* ── Timeline ── */
    .timeline-item {
        position: relative; padding-left: 2rem; padding-bottom: 1.4rem;
        border-left: 2px solid #E2E8F0;
    }
    .timeline-item:last-child { border-left-color: transparent; padding-bottom: 0; }
    .timeline-dot {
        position: absolute; left: -7px; top: 3px;
        width: 12px; height: 12px; border-radius: 50%;
        border: 2px solid #fff; box-shadow: 0 0 0 2px #E2E8F0;
    }

    .pqr-header-card {
        background-color: #001A29;
        border-radius: 24px;
        padding: 1.25rem 1.5rem;
    }

    .pqr-header-card h2 {
        color: #ffffff !important;
    }

    .pqr-header-card p {
        color: #c7d4da !important;
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-3 py-3">

    {{-- ══ CABECERA ══ --}}
    <div class="pqr-header-card d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small fw-semibold mb-1" style="color:#39A900;">
                <i class="fas fa-comment-dots me-2"></i>Control ECP · Panel administrativo
            </div>
            <h2 class="h3 fw-bold mb-0" style="font-family:'Outfit',sans-serif;">
                <i class="fas fa-comment-dots me-2" style="color:#39A900;"></i>Gestión de PQRS
            </h2>
            <p class="text-muted small mb-0 mt-1">
                Supervisión y respuesta a solicitudes de la Escuela Cultura de Paz
            </p>
        </div>
    </div>

    {{-- ══ ALERTAS FLASH ══ --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4"
             style="background:#f0fbf4; color:#001A29;">
            <i class="fas fa-check-circle me-2" style="color:#39A900;"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ══ MÉTRICAS ══ --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="pqr-stat-card accent-blue h-100" style="border-color:rgba(2,136,209,.35);">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="pqr-stat-label">Total recibidas</span>
                    <div class="pqr-stat-icon" style="background:#E0F2FE; color:#0288D1;">
                        <i class="fas fa-inbox"></i>
                    </div>
                </div>
                <div class="pqr-stat-value" style="color:#0288D1;">{{ $total }}</div>
                <div class="pqr-stat-subtitle"><span class="text-success fw-semibold">{{ $pqrsAdmin->total() }}</span> admin · <span style="color:#4F46E5;" class="fw-semibold">{{ $pqrsInstructor->total() }}</span> instructores</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="pqr-stat-card accent-amber h-100" style="border-color:rgba(217,119,6,.35);">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="pqr-stat-label">Pendientes</span>
                    <div class="pqr-stat-icon" style="background:#FEF3C7; color:#D97706;">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="pqr-stat-value" style="color:#D97706;">{{ $pendientes }}</div>
                <div class="pqr-stat-subtitle">Sin respuesta aún</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="pqr-stat-card accent-indigo h-100" style="border-color:rgba(79,70,229,.35);">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="pqr-stat-label">En trámite</span>
                    <div class="pqr-stat-icon" style="background:#EEF2FF; color:#4F46E5;">
                        <i class="fas fa-spinner"></i>
                    </div>
                </div>
                <div class="pqr-stat-value" style="color:#4F46E5;">{{ $enTramite }}</div>
                <div class="pqr-stat-subtitle">En revisión activa</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="pqr-stat-card accent-green h-100" style="border-color:rgba(22,163,74,.35);">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="pqr-stat-label">Resueltos</span>
                    <div class="pqr-stat-icon" style="background:#DCFCE7; color:#16A34A;">
                        <i class="fas fa-circle-check"></i>
                    </div>
                </div>
                <div class="pqr-stat-value" style="color:#16A34A;">{{ $resueltas }}</div>
                <div class="pqr-stat-subtitle">Casos finalizados</div>
            </div>
        </div>
    </div>

    {{-- ══ FILTROS ══ --}}
    <div class="pqr-filter-card mb-5">
            <form action="{{ route($soloInstructores ? 'controlecp.administrador.pqr.instructores' : 'controlecp.administrador.pqr') }}" method="GET"
                  class="pqr-filter-form">
                <div class="pqr-filter-field">
                    <label for="pqr-buscar">Buscar</label>
                    <div class="input-group inv-search">
                        <span class="input-group-text text-muted"><i class="fas fa-magnifying-glass"></i></span>
                        <input id="pqr-buscar" type="text" name="buscar" value="{{ request('buscar') }}"
                               class="form-control" placeholder="Radicado, aprendiz, asunto…">
                    </div>
                </div>
                <div class="pqr-filter-field">
                    <label for="pqr-tipo">Tipo</label>
                    <select id="pqr-tipo" name="id_tipo_pqrs" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos los tipos</option>
                        @foreach($tipos as $t)
                            <option value="{{ $t->id_tipo_pqrs }}"
                                {{ request('id_tipo_pqrs') == $t->id_tipo_pqrs ? 'selected' : '' }}>
                                {{ $t->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="pqr-filter-field">
                    <label for="pqr-estado">Estado</label>
                    <select id="pqr-estado" name="estado" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos los estados</option>
                        <option value="Pendiente"  {{ request('estado')=='Pendiente'  ?'selected':'' }}> Pendiente</option>
                        <option value="En trámite" {{ request('estado')=='En trámite' ?'selected':'' }}> En trámite</option>
                        <option value="Resuelto"   {{ request('estado')=='Resuelto'   ?'selected':'' }}> Resuelto</option>
                    </select>
                </div>
                <div class="pqr-filter-field">
                    <label for="pqr-instructor">Instructor</label>
                    <select id="pqr-instructor" name="id_instructor" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos los instructores</option>
                        @foreach($instructores as $inst)
                            <option value="{{ $inst->id }}"
                                {{ request('id_instructor') == $inst->id ? 'selected' : '' }}>
                                 {{ $inst->nickname }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="pqr-filter-actions">
                    <button type="submit" class="btn btn-filter">
                        <i class="fas fa-search me-1"></i> Buscar
                    </button>
                    <a href="{{ route($soloInstructores ? 'controlecp.administrador.pqr.instructores' : 'controlecp.administrador.pqr') }}"
                       class="btn btn-reset" title="Restablecer filtros" aria-label="Restablecer filtros">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                </div>
            </form>
    </div>

    {{-- ══ TABS: ADMINISTRACIÓN / INSTRUCTORES ══ --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div class="d-flex gap-2">
            {{-- Tab Administración --}}
            <a href="{{ route('controlecp.administrador.pqr') }}"
               class="d-flex align-items-center gap-2 rounded-pill px-4 py-2 fw-bold shadow-sm text-decoration-none"
               style="background:{{ !$soloInstructores ? '#39A900' : '#F1F5F9' }}; color:{{ !$soloInstructores ? '#fff' : '#475569' }}; font-size:.88rem;">
                <i class="fas fa-building-columns"></i>
                <span>Administración</span>
            </a>
            {{-- Tab Instructores --}}
            <a href="{{ route('controlecp.administrador.pqr.instructores') }}"
               class="d-flex align-items-center gap-2 rounded-pill border px-4 py-2 fw-bold text-decoration-none"
               style="background:{{ $soloInstructores ? '#39A900' : '#F1F5F9' }}; color:{{ $soloInstructores ? '#fff' : '#475569' }}; font-size:.88rem;">
                <i class="fas fa-chalkboard-user"></i>
                <span>Instructores</span>
            </a>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         SECCIÓN 1 — PQRS DE ADMINISTRACIÓN (el admin puede responder)
         ══════════════════════════════════════════════════════════════ --}}
    @if(!$soloInstructores)
    <div class="mb-5">
        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="d-flex align-items-center justify-content-center rounded-circle"
                  style="width:10px;height:10px;background:#39A900;flex-shrink:0;"></span>
            <h6 class="fw-bold mb-0" style="color:#001A29;">
                PQRS de Administración
                <span class="text-muted fw-normal ms-1" style="font-size:.8rem;">— Puedes ver, responder y cambiar estado</span>
            </h6>
        </div>
        <div class="card border-0 shadow-sm" style="border-radius:16px; overflow:hidden;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 pqr-admin-table">
                        <thead>
                            <tr class="tbl-head-row">
                                <th>Radicado / Fecha</th>
                                <th>Aprendiz</th>
                                <th>Tipo</th>
                                <th>Asunto</th>
                                <th class="text-center">Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pqrsAdmin as $pqr)
                            @php $ei = $pqr->estado_badge; $ti = $pqr->tipo_badge; @endphp
                            <tr class="tbl-body-row">
                                <td>
                                    <div class="fw-bold font-monospace" style="font-size:.9rem; color:#001A29;">
                                        {{ $pqr->radicado }}
                                    </div>
                                    <small class="text-muted" style="font-size:.76rem;">
                                        <i class="fas fa-calendar-day me-1"></i>
                                        {{ $pqr->fecha_radicacion?->copy()->timezone('America/Bogota')->format('d/m/Y g:i A') ?? '—' }}
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="av-badge">
                                            {{ strtoupper(substr($pqr->aprendiz?->nickname ?? 'A', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark" style="font-size:.94rem;">
                                                {{ $pqr->aprendiz?->nickname ?? 'Aprendiz' }}
                                            </div>
                                            <small class="text-muted" style="font-size:.76rem;">
                                                {{ $pqr->aprendiz?->email ?? '' }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge rounded-pill px-2 py-1 border fw-semibold"
                                          style="background:{{ $ti['bg'] }};color:{{ $ti['text'] }};border-color:{{ $ti['border'] }} !important;font-size:.8rem;">
                                        <i class="{{ $ti['icon'] }} me-1"></i>{{ $pqr->tipo_nombre }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark text-truncate" style="max-width:220px;font-size:.94rem;">
                                        {{ $pqr->asunto }}
                                    </div>
                                    <div class="text-muted text-truncate" style="max-width:220px;font-size:.8rem;">
                                        {{ $pqr->descripcion }}
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill px-3 py-1 border fw-semibold"
                                          style="background:{{ $ei['bg'] }};color:{{ $ei['text'] }};border-color:{{ $ei['border'] }} !important;font-size:.82rem;">
                                        <i class="{{ $ei['icon'] }} me-1"></i>{{ $ei['label'] }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <button type="button" class="btn pqr-manage-icon" title="Ver detalle"
                                                aria-label="Ver detalle de {{ $pqr->radicado }}"
                                                data-bs-toggle="modal" data-bs-target="#detalleAdminPqr{{ $pqr->id_pqrs }}">
                                            <i class="fas fa-eye" aria-hidden="true"></i>
                                        </button>
                                        <button type="button" class="btn pqr-manage-icon pqr-manage-icon-history" title="Ver historial"
                                                aria-label="Ver historial de {{ $pqr->radicado }}"
                                                onclick="abrirModalHistorial({{ $pqr->id_pqrs }}, '{{ $pqr->radicado }}')">
                                            <i class="fas fa-clock-rotate-left" aria-hidden="true"></i>
                                        </button>
                                        <button type="button" class="btn pqr-manage-icon pqr-manage-icon-status" title="Cambiar estado"
                                                aria-label="Cambiar estado de {{ $pqr->radicado }}"
                                                data-bs-toggle="modal" data-bs-target="#estadoAdminPqr{{ $pqr->id_pqrs }}">
                                            <i class="fas fa-arrows-rotate" aria-hidden="true"></i>
                                        </button>
                                        <button type="button" class="btn pqr-manage-icon pqr-manage-icon-reply" title="Enviar respuesta"
                                                aria-label="Enviar respuesta para {{ $pqr->radicado }}"
                                                data-bs-toggle="modal" data-bs-target="#responderAdminPqr{{ $pqr->id_pqrs }}">
                                            <i class="fas fa-paper-plane" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block opacity-30"></i>
                                    <p class="fw-bold mb-1" style="color:#001A29;">No hay PQRS para administración</p>
                                    <small>Las solicitudes sin instructor asignado aparecerán aquí.</small>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @foreach($pqrsAdmin as $pqr)
                    @php
                        $estadoBadgeAdmin = $pqr->estado_badge;
                        $tipoBadgeAdmin = $pqr->tipo_badge;
                        $modalErroresAdminRespuesta = old('modal_pqr_id') == $pqr->id_pqrs && $errors->has('respuesta');
                        $modalErroresAdminEstado = old('modal_pqr_id') == $pqr->id_pqrs
                            && ($errors->has('estado') || $errors->has('observacion'));
                    @endphp
                    <div class="modal fade pqr-detail-modal" id="detalleAdminPqr{{ $pqr->id_pqrs }}" tabindex="-1"
                         aria-labelledby="detalleAdminPqrTitulo{{ $pqr->id_pqrs }}" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content border-0 shadow-lg">
                                <div class="modal-header">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="pqr-detail-modal-icon"><i class="fas fa-eye" aria-hidden="true"></i></span>
                                        <div>
                                            <div class="small fw-semibold mb-1" style="color:#93c5fd">{{ $pqr->radicado }} · {{ $pqr->tipo_nombre }}</div>
                                            <h2 class="modal-title h5 fw-bold mb-0" id="detalleAdminPqrTitulo{{ $pqr->id_pqrs }}">Detalle de la solicitud</h2>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <h3 class="h5 fw-bold mb-3" style="color:#001A29;">{{ $pqr->asunto }}</h3>
                                    <div class="pqr-detail-meta d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                                        <div>
                                            <span class="pqr-detail-label">Solicitante</span>
                                            <strong class="text-dark">{{ $pqr->aprendiz?->nickname ?? 'Aprendiz' }}</strong>
                                            <div class="small text-muted">{{ $pqr->aprendiz?->email ?? '' }}</div>
                                        </div>
                                        <div>
                                            <span class="pqr-detail-label">Fecha de radicación</span>
                                            <span class="small fw-semibold text-dark">{{ $pqr->fecha_radicacion?->copy()->timezone('America/Bogota')->format('d/m/Y g:i A') ?? '—' }}</span>
                                        </div>
                                        <span class="badge rounded-pill px-3 py-2 border fw-semibold"
                                              style="background:{{ $estadoBadgeAdmin['bg'] }};color:{{ $estadoBadgeAdmin['text'] }};border-color:{{ $estadoBadgeAdmin['border'] }} !important;">
                                            <i class="{{ $estadoBadgeAdmin['icon'] }} me-1" aria-hidden="true"></i>{{ $estadoBadgeAdmin['label'] }}
                                        </span>
                                        <span class="badge rounded-pill px-3 py-2 border fw-semibold"
                                              style="background:{{ $tipoBadgeAdmin['bg'] }};color:{{ $tipoBadgeAdmin['text'] }};border-color:{{ $tipoBadgeAdmin['border'] }} !important;">
                                            <i class="{{ $tipoBadgeAdmin['icon'] }} me-1" aria-hidden="true"></i>{{ $pqr->tipo_nombre }}
                                        </span>
                                    </div>
                                    <h3 class="h6 fw-bold mb-2">Descripción</h3>
                                    <div class="pqr-detail-description mb-4">{{ $pqr->descripcion }}</div>
                                    @if($pqr->respuestas->isNotEmpty())
                                        <h3 class="h6 fw-bold mb-3">Respuestas</h3>
                                        <div class="d-grid gap-2">
                                            @foreach($pqr->respuestas as $respuesta)
                                                <article class="pqr-detail-response">
                                                    <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                                                        <strong class="text-dark">{{ $respuesta->usuario?->nickname ?? 'Equipo de atención' }}</strong>
                                                        <time class="small text-muted">{{ $respuesta->fecha?->copy()->timezone('America/Bogota')->format('d/m/Y H:i') ?? '—' }}</time>
                                                    </div>
                                                    <div style="white-space:pre-wrap;overflow-wrap:anywhere;">{{ $respuesta->respuesta }}</div>
                                                </article>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="small text-muted"><i class="fas fa-comment-dots me-1" aria-hidden="true"></i>Aún no hay respuestas.</div>
                                    @endif
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade pqr-status-modal" id="estadoAdminPqr{{ $pqr->id_pqrs }}" tabindex="-1"
                         aria-labelledby="estadoAdminPqrTitulo{{ $pqr->id_pqrs }}" aria-hidden="true"
                         @if($modalErroresAdminEstado) data-open-after-validation="true" @endif>
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg">
                                <form method="POST" action="{{ route('controlecp.administrador.pqr.estado', $pqr->id_pqrs) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="modal_pqr_id" value="{{ $pqr->id_pqrs }}">
                                    <div class="modal-header">
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="pqr-status-modal-icon"><i class="fas fa-arrows-rotate" aria-hidden="true"></i></span>
                                            <div>
                                                <div class="small fw-semibold mb-1" style="color:#fbbf24">{{ $pqr->radicado }}</div>
                                                <h2 class="modal-title h5 fw-bold mb-0" id="estadoAdminPqrTitulo{{ $pqr->id_pqrs }}">Cambiar estado</h2>
                                            </div>
                                        </div>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                    </div>
                                    <div class="modal-body">
                                        @if($modalErroresAdminEstado)
                                            <div class="alert alert-danger rounded-3">
                                                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                                            </div>
                                        @endif
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold" for="estadoAdminSelect{{ $pqr->id_pqrs }}">Nuevo estado</label>
                                            <select class="form-select" id="estadoAdminSelect{{ $pqr->id_pqrs }}" name="estado" required>
                                                @foreach(['Pendiente', 'En trámite', 'Resuelto', 'Resuelta', 'Cerrado', 'Cerrada'] as $estado)
                                                    <option value="{{ $estado }}" @selected(($modalErroresAdminEstado ? old('estado', $pqr->estado) : $pqr->estado) === $estado)>{{ $estado }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="form-label fw-semibold" for="observacionAdmin{{ $pqr->id_pqrs }}">Observación</label>
                                            <textarea class="form-control" id="observacionAdmin{{ $pqr->id_pqrs }}" name="observacion" rows="4" minlength="10" maxlength="200" placeholder="Escribe el motivo o detalle del cambio (mínimo 10 caracteres)...">{{ $modalErroresAdminEstado ? old('observacion') : '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn pqr-status-save"><i class="fas fa-check me-1" aria-hidden="true"></i>Guardar cambio</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade pqr-reply-modal" id="responderAdminPqr{{ $pqr->id_pqrs }}" tabindex="-1"
                         aria-labelledby="responderAdminPqrTitulo{{ $pqr->id_pqrs }}" aria-hidden="true"
                         @if($modalErroresAdminRespuesta) data-open-after-validation="true" @endif>
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg">
                                <form method="POST" action="{{ route('controlecp.administrador.pqr.responder', $pqr->id_pqrs) }}">
                                    @csrf
                                    <input type="hidden" name="modal_pqr_id" value="{{ $pqr->id_pqrs }}">
                                    <div class="modal-header">
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="pqr-reply-modal-icon"><i class="fas fa-paper-plane" aria-hidden="true"></i></span>
                                            <div>
                                                <div class="small fw-semibold mb-1" style="color:#86efac">{{ $pqr->radicado }}</div>
                                                <h2 class="modal-title h5 fw-bold mb-0" id="responderAdminPqrTitulo{{ $pqr->id_pqrs }}">Enviar respuesta</h2>
                                            </div>
                                        </div>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                    </div>
                                    <div class="modal-body">
                                        @if($modalErroresAdminRespuesta)
                                            <div class="alert alert-danger rounded-3">
                                                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                                            </div>
                                        @endif
                                        <div class="mb-3">
                                            <div class="text-muted small">Asunto</div>
                                            <strong class="text-dark">{{ $pqr->asunto }}</strong>
                                        </div>
                                        <label class="form-label fw-semibold" for="respuestaAdmin{{ $pqr->id_pqrs }}">Respuesta formal para el aprendiz</label>
                                        <textarea class="form-control" id="respuestaAdmin{{ $pqr->id_pqrs }}" name="respuesta" rows="5" minlength="10" maxlength="200" placeholder="Escribe aquí tu respuesta (mínimo 10 caracteres)..." required>{{ $modalErroresAdminRespuesta ? old('respuesta') : '' }}</textarea>
                                        <div class="form-text text-end">Entre 50 y 5000 caracteres.</div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn pqr-reply-send"><i class="fas fa-paper-plane me-1" aria-hidden="true"></i>Enviar respuesta</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
                @if($pqrsAdmin->hasPages())
                <div class="pqr-pagination">
                    @if ($pqrsAdmin->hasPages())
                        <nav aria-label="Paginación de PQRS administrativas">
                            <ul class="pagination justify-content-center">
                                @if ($pqrsAdmin->onFirstPage())
                                    <li class="page-item disabled"><span class="page-link" aria-hidden="true">&lsaquo;</span></li>
                                @else
                                    <li class="page-item"><a class="page-link" href="{{ $pqrsAdmin->previousPageUrl() }}" aria-label="Página anterior">&lsaquo;</a></li>
                                @endif
                                @php
                                $ultimaPaginaMostrada = 0;
                            @endphp
                                @for ($pagina = 1; $pagina <= $pqrsAdmin->lastPage(); $pagina++)
                                    @if ($pagina === 1 || $pagina === $pqrsAdmin->lastPage() || abs($pagina - $pqrsAdmin->currentPage()) <= 1)
                                        @if ($ultimaPaginaMostrada > 0 && $pagina > $ultimaPaginaMostrada + 1)
                                            <li class="page-item disabled"><span class="page-link">…</span></li>
                                        @endif
                                        <li class="page-item {{ $pagina === $pqrsAdmin->currentPage() ? 'active' : '' }}">
                                            <a class="page-link" href="{{ $pqrsAdmin->url($pagina) }}" @if($pagina === $pqrsAdmin->currentPage()) aria-current="page" @endif>{{ $pagina }}</a>
                                        </li>
                                        @php
                                    $ultimaPaginaMostrada = $pagina;
                                @endphp
                                    @endif
                                @endfor
                                @if ($pqrsAdmin->hasMorePages())
                                    <li class="page-item"><a class="page-link" href="{{ $pqrsAdmin->nextPageUrl() }}" aria-label="Página siguiente">&rsaquo;</a></li>
                                @else
                                    <li class="page-item disabled"><span class="page-link" aria-hidden="true">&rsaquo;</span></li>
                                @endif
                            </ul>
                        </nav>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════
         SECCIÓN 2 — PQRS DE INSTRUCTORES (solo lectura para el admin)
         ══════════════════════════════════════════════════════════════ --}}
    @if($soloInstructores)
    <div class="mb-4">
        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="d-flex align-items-center justify-content-center rounded-circle"
                  style="width:10px;height:10px;background:#4F46E5;flex-shrink:0;"></span>
            <h6 class="fw-bold mb-0" style="color:#001A29;">
                PQRS de Instructores
                <span class="text-muted fw-normal ms-1" style="font-size:.8rem;">— Solo lectura para el administrador</span>
            </h6>
        </div>

        <div class="section-readonly">
            <div class="table-responsive">
                <table class="table table-hover mb-0 instructor-pqr-table">
                    <thead>
                        <tr class="tbl-head-row" style="background:#F1F5F9;">
                            <th>Radicado / Fecha</th>
                            <th>Aprendiz</th>
                            <th>Tipo</th>
                            <th>Asunto</th>
                            <th>Instructor asignado</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end">Ver</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pqrsInstructor as $pqr)
                        @php $ei = $pqr->estado_badge; $ti = $pqr->tipo_badge; @endphp
                        <tr class="tbl-body-row">
                            <td>
                                <div class="fw-semibold font-monospace" style="font-size:.9rem; color:#475569;">
                                    {{ $pqr->radicado }}
                                </div>
                                <small class="text-muted" style="font-size:.76rem;">
                                    {{ $pqr->fecha_radicacion?->copy()->timezone('America/Bogota')->format('d/m/Y g:i a') ?? '—' }}
                                </small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="av-badge" style="background:#F1F5F9;color:#94A3B8;border-color:#CBD5E1;">
                                        {{ strtoupper(substr($pqr->aprendiz?->nickname ?? 'A', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold" style="font-size:.94rem;color:#475569;">
                                            {{ $pqr->aprendiz?->nickname ?? 'Aprendiz' }}
                                        </div>
                                        <small class="text-muted" style="font-size:.76rem;">
                                            {{ $pqr->aprendiz?->email ?? '' }}
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill px-2 py-1 border"
                                      style="background:{{ $ti['bg'] }};color:{{ $ti['text'] }};border-color:{{ $ti['border'] }} !important;font-size:.8rem;opacity:.85;">
                                    <i class="{{ $ti['icon'] }} me-1"></i>{{ $pqr->tipo_nombre }}
                                </span>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width:200px;font-size:.94rem;color:#475569;">
                                    {{ $pqr->asunto }}
                                </div>
                                <div class="text-muted text-truncate" style="max-width:200px;font-size:.8rem;">
                                    {{ $pqr->descripcion }}
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-chalkboard-user" style="color:#4F46E5;font-size:.9rem;"></i>
                                    <span class="fw-semibold small" style="color:#4F46E5;">
                                        {{ $pqr->instructorDestino?->nickname ?? '—' }}
                                    </span>
                                </div>
                                @if($pqr->respuestas->isNotEmpty())
                                <small class="text-success d-block mt-1" style="font-size:.76rem;">
                                    <i class="fas fa-check-circle me-1"></i>
                                    {{ $pqr->respuestas->count() }} respuesta(s) del instructor
                                </small>
                                @else
                                <small class="text-muted d-block mt-1" style="font-size:.76rem;">
                                    Sin respuesta aún
                                </small>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill px-2 py-1 border"
                                      style="background:{{ $ei['bg'] }};color:{{ $ei['text'] }};border-color:{{ $ei['border'] }} !important;font-size:.82rem;">
                                    <i class="{{ $ei['icon'] }} me-1"></i>{{ $pqr->estado }}
                                </span>
                            </td>
                            <td class="text-end">
                                {{-- PQRS de instructor: solo consulta, sin gestionar estado ni responder --}}
                                <div class="d-inline-flex gap-2">
                                    <button type="button" class="btn-tbl-action btn-tbl-action-view"
                                            title="Ver detalle (solo lectura)"
                                            data-bs-toggle="modal"
                                            data-bs-target="#detallePqrsInstructor{{ $pqr->id_pqrs }}"
                                            aria-label="Ver detalle de {{ $pqr->radicado }}">
                                        <i class="fas fa-eye" aria-hidden="true"></i>
                                    </button>
                                    <button type="button" class="btn-tbl-action btn-tbl-action-history"
                                            title="Ver historial"
                                            onclick="abrirModalHistorial({{ $pqr->id_pqrs }}, '{{ $pqr->radicado }}')">
                                        <i class="fas fa-clock-rotate-left" aria-hidden="true"></i>
                                    </button>
                                    <button type="button" class="btn-tbl-action btn-tbl-action-trace"
                                            title="Ver trazabilidad"
                                            aria-label="Ver trazabilidad de {{ $pqr->radicado }}"
                                            onclick="abrirTrazabilidadPqrs({{ $pqr->id_pqrs }}, '{{ $pqr->radicado }}')">
                                        <i class="fas fa-route" aria-hidden="true"></i>
                                    </button>
                                    <button type="button" class="btn-tbl-action btn-tbl-action-response"
                                            title="Ver respuesta del instructor"
                                            aria-label="Ver respuesta del instructor para {{ $pqr->radicado }}"
                                            onclick="abrirRespuestasInstructorPqrs({{ $pqr->id_pqrs }}, '{{ $pqr->radicado }}')">
                                        <i class="fas fa-comment-dots" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-chalkboard-user fa-3x mb-3 d-block opacity-30"></i>
                                <p class="fw-bold mb-1" style="color:#001A29;">No hay PQRS asignadas a instructores</p>
                                <small>Las solicitudes dirigidas a instructores aparecerán aquí.</small>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @foreach($pqrsInstructor as $pqr)
                @php $badgeDetalleInstructor = $pqr->estado_badge; $tipoDetalleInstructor = $pqr->tipo_badge; @endphp
                <div class="modal fade pqr-instructor-detail-modal" id="detallePqrsInstructor{{ $pqr->id_pqrs }}"
                     tabindex="-1" aria-labelledby="detallePqrsInstructorTitulo{{ $pqr->id_pqrs }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="pqr-instructor-detail-icon"><i class="fas fa-eye" aria-hidden="true"></i></span>
                                    <div>
                                        <div class="small fw-semibold mb-1" style="color:#93c5fd">
                                            {{ $pqr->radicado }} · {{ $pqr->tipo_nombre }}
                                        </div>
                                        <h2 class="modal-title h5 fw-bold mb-0" id="detallePqrsInstructorTitulo{{ $pqr->id_pqrs }}">
                                            Detalle de la solicitud
                                        </h2>
                                    </div>
                                </div>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <h3 class="h5 fw-bold mb-3" style="color:#001A29;">{{ $pqr->asunto }}</h3>
                                <div class="pqr-instructor-detail-meta d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                                    <div>
                                        <span class="pqr-instructor-detail-label">Aprendiz</span>
                                        <strong class="text-dark">{{ $pqr->aprendiz?->nickname ?? 'Aprendiz' }}</strong>
                                        <div class="small text-muted">{{ $pqr->aprendiz?->email ?? '' }}</div>
                                    </div>
                                    <div>
                                        <span class="pqr-instructor-detail-label">Fecha de radicación</span>
                                        <span class="small fw-semibold text-dark">
                                            {{ $pqr->fecha_radicacion?->copy()->timezone('America/Bogota')->format('d/m/Y g:i A') ?? '—' }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="pqr-instructor-detail-label">Instructor asignado</span>
                                        <strong class="small text-dark">{{ $pqr->instructorDestino?->nickname ?? '—' }}</strong>
                                    </div>
                                    <span class="badge rounded-pill px-3 py-2 border fw-semibold"
                                          style="background:{{ $badgeDetalleInstructor['bg'] }};color:{{ $badgeDetalleInstructor['text'] }};border-color:{{ $badgeDetalleInstructor['border'] }} !important;">
                                        <i class="{{ $badgeDetalleInstructor['icon'] }} me-1" aria-hidden="true"></i>{{ $pqr->estado }}
                                    </span>
                                    <span class="badge rounded-pill px-3 py-2 border fw-semibold"
                                          style="background:{{ $tipoDetalleInstructor['bg'] }};color:{{ $tipoDetalleInstructor['text'] }};border-color:{{ $tipoDetalleInstructor['border'] }} !important;">
                                        <i class="{{ $tipoDetalleInstructor['icon'] }} me-1" aria-hidden="true"></i>{{ $pqr->tipo_nombre }}
                                    </span>
                                </div>
                                <h3 class="h6 fw-bold mb-2">Descripción</h3>
                                <div class="pqr-instructor-detail-description">{{ $pqr->descripcion }}</div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            @if($pqrsInstructor->hasPages())
            <div class="pqr-pagination">
                @if ($pqrsInstructor->hasPages())
                    <nav aria-label="Paginación de PQRS de instructores">
                        <ul class="pagination justify-content-center">
                            @if ($pqrsInstructor->onFirstPage())
                                <li class="page-item disabled"><span class="page-link" aria-hidden="true">&lsaquo;</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $pqrsInstructor->previousPageUrl() }}" aria-label="Página anterior">&lsaquo;</a></li>
                            @endif
                            @php
                                $ultimaPaginaMostrada = 0;
                            @endphp
                            @for ($pagina = 1; $pagina <= $pqrsInstructor->lastPage(); $pagina++)
                                @if ($pagina === 1 || $pagina === $pqrsInstructor->lastPage() || abs($pagina - $pqrsInstructor->currentPage()) <= 1)
                                    @if ($ultimaPaginaMostrada > 0 && $pagina > $ultimaPaginaMostrada + 1)
                                        <li class="page-item disabled"><span class="page-link">…</span></li>
                                    @endif
                                    <li class="page-item {{ $pagina === $pqrsInstructor->currentPage() ? 'active' : '' }}">
                                        <a class="page-link" href="{{ $pqrsInstructor->url($pagina) }}" @if($pagina === $pqrsInstructor->currentPage()) aria-current="page" @endif>{{ $pagina }}</a>
                                    </li>
                                    @php
                                    $ultimaPaginaMostrada = $pagina;
                                @endphp
                                @endif
                            @endfor
                            @if ($pqrsInstructor->hasMorePages())
                                <li class="page-item"><a class="page-link" href="{{ $pqrsInstructor->nextPageUrl() }}" aria-label="Página siguiente">&rsaquo;</a></li>
                            @else
                                <li class="page-item disabled"><span class="page-link" aria-hidden="true">&rsaquo;</span></li>
                            @endif
                        </ul>
                    </nav>
                @endif
            </div>
            @endif
        </div>
    </div>
    @endif
</div>

{{-- ════════════════════════════════════════════════════════
     MODAL 1 – DETALLE PQRS
     ════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalDetallePqrs" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header py-3 px-4 border-bottom" style="background:#F8FAFC;">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-dark font-monospace fs-6 px-3 py-1" id="detRadicado">…</span>
                    <span class="badge rounded-pill px-2 py-1" id="detTipoBadge">—</span>
                    <span class="badge rounded-pill px-2 py-1" id="detEstadoBadge">—</span>
                    {{-- Indicador solo lectura (visible solo para sección instructores) --}}
                    <span id="detReadonlyBadge" class="badge-readonly d-none">
                        <i class="fas fa-eye"></i> Solo lectura
                    </span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-12 col-lg-5">
                        <div class="card border-0 shadow-sm rounded-4 p-3 mb-3" style="background:#FAFBFD;">
                            <h6 class="fw-bold mb-3 pb-2 border-bottom text-dark">
                                <i class="fas fa-user me-1 text-success"></i>Solicitante
                            </h6>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="av-badge" style="width:46px;height:46px;font-size:1.1rem;" id="detAvatar">A</div>
                                <div>
                                    <div class="fw-bold text-dark" id="detAprendizNombre">—</div>
                                    <small class="text-muted d-block" id="detAprendizEmail">—</small>
                                </div>
                            </div>
                            <div class="small">
                                <div class="d-flex justify-content-between py-1 border-bottom">
                                    <span class="text-muted">Radicación:</span>
                                    <strong id="detFechaRad">—</strong>
                                </div>
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted">Asignado a:</span>
                                    <strong id="detInstructor">—</strong>
                                </div>
                            </div>
                        </div>
                        <div class="card border-0 shadow-sm rounded-4 p-3">
                            <h6 class="fw-bold text-dark mb-1" id="detAsunto">—</h6>
                            <div class="p-3 rounded-3 border small text-dark mt-2"
                                 style="background:#F8FAFC; white-space:pre-wrap; line-height:1.7; font-size:.88rem;"
                                 id="detDescripcion">Cargando…</div>
                            {{-- Botones de acción (ocultos en modo solo lectura) --}}
                            <div class="d-flex gap-2 mt-3 pt-3 border-top" id="detAccionesBar">
                                <button type="button" id="detBtnCambiarEstado"
                                        class="btn btn-sm btn-outline-warning rounded-pill px-3 flex-grow-1">
                                    <i class="fas fa-arrows-rotate me-1"></i>Estado
                                </button>
                                <button type="button" id="detBtnResponder"
                                        class="btn btn-sm text-white rounded-pill px-3 flex-grow-1 fw-semibold"
                                        style="background:#39A900;">
                                    <i class="fas fa-reply me-1"></i>Responder
                                </button>
                                <button type="button" id="detBtnHistorial"
                                        class="btn btn-sm btn-outline-secondary rounded-pill px-3 flex-grow-1"
                                        style="color:#7C3AED; border-color:#7C3AED;">
                                    <i class="fas fa-clock-rotate-left me-1"></i>Historial
                                </button>
                            </div>
                            {{-- Aviso solo lectura (visible en modo instructor) --}}
                            <div id="detReadonlyBar" class="d-none mt-3 pt-3 border-top">
                                <div class="alert mb-0 py-2 small border-0 d-flex align-items-center gap-2" data-system-alert-ignore
                                     style="background:#FFF7ED; border-radius:10px;">
                                    <i class="fas fa-lock" style="color:#D97706;"></i>
                                    <span class="text-dark">Esta PQRS es gestionada por el instructor asignado.
                                    El administrador solo puede supervisar.</span>
                                </div>
                                <button type="button" id="detBtnHistorialRO"
                                        class="btn btn-sm btn-outline-secondary rounded-pill px-3 w-100 mt-2"
                                        style="color:#7C3AED; border-color:#7C3AED;">
                                    <i class="fas fa-clock-rotate-left me-1"></i>Ver historial completo
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-7">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-header bg-white border-0 pt-3 px-4 pb-0">
                                <ul class="nav nav-tabs card-header-tabs">
                                    <li class="nav-item">
                                        <button class="nav-link active fw-semibold" data-bs-toggle="tab"
                                                data-bs-target="#pane-timeline-det">
                                            <i class="fas fa-clock-rotate-left me-1 text-primary"></i>Trazabilidad
                                        </button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link fw-semibold" data-bs-toggle="tab"
                                                data-bs-target="#pane-respuestas-det">
                                            <i class="fas fa-comment-dots me-1 text-success"></i>Respuestas
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body p-4 tab-content" style="min-height:280px;">
                                <div class="tab-pane fade show active" id="pane-timeline-det">
                                    <div id="detTimelineContainer">
                                        <div class="text-center py-5 text-muted">
                                            <i class="fas fa-spinner fa-spin me-2"></i>Cargando…
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="pane-respuestas-det">
                                    <div id="detRespuestasContainer">
                                        <div class="text-center py-5 text-muted">
                                            <i class="fas fa-spinner fa-spin me-2"></i>Cargando…
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════
     MODAL 2 – RESPONDER (solo para PQRS de administración)
     ════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalResponderPqrs" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <form id="formResponderPqrs" method="POST">
                @csrf
                <div class="modal-header py-3 px-4 border-bottom" style="background:#F8FAFC;">
                    <h5 class="modal-title fw-bold" style="color:#001A29;">
                        <i class="fas fa-reply me-2" style="color:#39A900;"></i>Escribir respuesta oficial
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 py-2 small mb-3 rounded-3" data-system-alert-ignore style="background:#EFF6FF;">
                        <i class="fas fa-circle-info me-1"></i>
                        Respondiendo a <strong id="respModalRadicado">—</strong>:
                        <span id="respModalAsunto" class="text-dark fw-semibold">—</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">
                            Respuesta formal <span class="text-danger">*</span>
                        </label>
                        <textarea name="respuesta" id="respTextarea" rows="6" minlength="10" maxlength="200"
                                  class="form-control rounded-3"
                                  placeholder="Redacte la respuesta clara y formal que recibirá el aprendiz…"
                                  required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                            data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white rounded-pill px-4 shadow-sm fw-semibold"
                            style="background:#39A900;">
                        <i class="fas fa-paper-plane me-1"></i>Enviar respuesta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════
     MODAL 3 – CAMBIAR ESTADO
     ════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalCambiarEstadoPqrs" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <form id="formCambiarEstadoPqrs" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header py-3 px-4 border-bottom" style="background:#F8FAFC;">
                    <h5 class="modal-title fw-bold" style="color:#001A29;">
                        <i class="fas fa-arrows-rotate me-2 text-warning"></i>Cambiar estado
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <div class="text-muted small">Solicitud:</div>
                        <div class="fw-bold font-monospace fs-6 text-dark" id="estadoModalRadicado">—</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Nuevo estado <span class="text-danger">*</span></label>
                        <select name="estado" id="nuevoEstadoSelect" class="form-select rounded-3" required>
                            <option value="Pendiente">🟡 Pendiente</option>
                            <option value="En trámite">🔵 En trámite</option>
                            <option value="Resuelto">🟢 Resuelto</option>
                            <option value="Cerrado">🔒 Cerrado</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label small fw-bold text-dark">Observación</label>
                        <textarea name="observacion" id="obsEstadoText" rows="3" minlength="10" maxlength="200"
                                  class="form-control rounded-3"
                                  placeholder="Motivo del cambio…"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                            data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white rounded-pill px-4 shadow-sm fw-semibold"
                            style="background:#39A900;">Guardar estado</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════
     MODAL 4 – HISTORIAL PERSONAL
     ════════════════════════════════════════════════════════ --}}
<div class="modal fade pqr-history-modal" id="modalHistorialPqrs" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-3">
                    <span class="pqr-history-modal-icon"><i class="fas fa-clock-rotate-left" aria-hidden="true"></i></span>
                    <div>
                        <div class="small fw-semibold mb-1" style="color:#c4b5fd">
                            <span id="historialModalRadicado">PQRS</span>
                        </div>
                        <h2 class="modal-title h5 fw-bold mb-0">Historial de cambios</h2>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="historialTimelineContainer">
                    <div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2" aria-hidden="true"></i>Cargando historial…</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade pqr-trace-modal" id="modalTrazabilidadPqrs" tabindex="-1"
     aria-labelledby="trazabilidadModalTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-3">
                    <span class="pqr-trace-modal-icon"><i class="fas fa-route" aria-hidden="true"></i></span>
                    <div>
                        <div class="small fw-semibold mb-1" style="color:#67e8f9">
                            <span id="trazabilidadModalRadicado">PQRS</span>
                        </div>
                        <h2 class="modal-title h5 fw-bold mb-0" id="trazabilidadModalTitulo">Trazabilidad</h2>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="trazabilidadTimelineContainer" aria-live="polite"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade pqr-response-modal" id="modalRespuestasInstructorPqrs" tabindex="-1"
     aria-labelledby="respuestasInstructorTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-3">
                    <span class="pqr-response-modal-icon"><i class="fas fa-comment-dots" aria-hidden="true"></i></span>
                    <div>
                        <div class="small fw-semibold mb-1" style="color:#86efac">
                            <span id="respuestasInstructorRadicado">PQRS</span>
                        </div>
                        <h2 class="modal-title h5 fw-bold mb-0" id="respuestasInstructorTitulo">Respuestas</h2>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="respuestasInstructorContainer" aria-live="polite"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════
     MODAL 5 – REPORTE ESTADÍSTICO
     ════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalReportePqrs" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header py-3 px-4 border-bottom" style="background:#F8FAFC;">
                <div>
                    <h5 class="modal-title fw-bold mb-0" style="color:#001A29;">
                        <i class="fas fa-chart-pie me-2 text-success"></i>Reporte estadístico de PQRS
                    </h5>
                    <small class="text-muted">Escuela Cultura de Paz</small>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <a href="{{ route('controlecp.administrador.pqr.reporte') }}"
                       class="btn btn-outline-secondary rounded-pill btn-sm px-3" target="_blank">
                        <i class="fas fa-print me-1"></i>Imprimir
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-12 col-lg-4">
                        <div class="border rounded-4 p-3 bg-white shadow-sm">
                            <h6 class="fw-bold text-dark mb-2">Por tipo</h6>
                            <div style="height:220px;"><canvas id="chartPqrsTipo"></canvas></div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="border rounded-4 p-3 bg-white shadow-sm">
                            <h6 class="fw-bold text-dark mb-2">Por estado</h6>
                            <div style="height:220px;"><canvas id="chartPqrsEstado"></canvas></div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="border rounded-4 p-3 bg-white shadow-sm">
                            <h6 class="fw-bold text-dark mb-2">Por responsable</h6>
                            <div style="height:220px;"><canvas id="chartPqrsInstructor"></canvas></div>
                        </div>
                    </div>
                </div>
                <div class="row g-2 mt-4 pt-3 border-top text-center">
                    <div class="col-3">
                        <div class="p-2 border rounded-3 bg-light">
                            <small class="text-muted d-block">Total</small>
                            <strong class="fs-5">{{ $total }}</strong>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 border rounded-3" style="background:#FEF3C7;">
                            <small class="text-warning-emphasis d-block">Pendientes</small>
                            <strong class="fs-5 text-warning-emphasis">{{ $pendientes }}</strong>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 border rounded-3" style="background:#EEF2FF;">
                            <small style="color:#4F46E5;" class="d-block">En trámite</small>
                            <strong class="fs-5" style="color:#4F46E5;">{{ $enTramite }}</strong>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 border rounded-3" style="background:#DEF7EC;">
                            <small class="text-success d-block">Resueltos</small>
                            <strong class="fs-5 text-success">{{ $resueltas }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// ── 1. MODAL DETALLE ──────────────────────────────────────────────────────
function abrirDetallePqrsModal(id, soloLectura = false, pestañaInicial = null) {
    const modal = new bootstrap.Modal(document.getElementById('modalDetallePqrs'));
    modal.show();

    // Mostrar/ocultar controles según modo
    const accBar  = document.getElementById('detAccionesBar');
    const roBar   = document.getElementById('detReadonlyBar');
    const roBadge = document.getElementById('detReadonlyBadge');
    if (soloLectura) {
        accBar.classList.add('d-none');
        roBar.classList.remove('d-none');
        roBadge.classList.remove('d-none');
    } else {
        accBar.classList.remove('d-none');
        roBar.classList.add('d-none');
        roBadge.classList.add('d-none');
    }

    // Reset
    document.getElementById('detRadicado').textContent    = 'Cargando…';
    document.getElementById('detAsunto').textContent      = '—';
    document.getElementById('detDescripcion').textContent = 'Cargando…';
    document.getElementById('detTimelineContainer').innerHTML =
        '<div class="text-center py-5 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Cargando…</div>';
    document.getElementById('detRespuestasContainer').innerHTML =
        '<div class="text-center py-5 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Cargando…</div>';

    fetch(`/control-ecp/administrador/pqr/${id}/detalle-json`)
        .then(r => r.json())
        .then(d => {
            if (pestañaInicial) {
                const botonPestana = document.querySelector(`[data-bs-target="#${pestañaInicial}"]`);
                if (botonPestana) {
                    bootstrap.Tab.getOrCreateInstance(botonPestana).show();
                }
            }

            document.getElementById('detRadicado').textContent      = d.radicado;
            document.getElementById('detAsunto').textContent         = d.asunto;
            document.getElementById('detDescripcion').textContent    = d.descripcion;
            document.getElementById('detAprendizNombre').textContent = d.aprendiz.nickname;
            document.getElementById('detAprendizEmail').textContent  = d.aprendiz.email;
            document.getElementById('detAvatar').textContent         = d.aprendiz.nickname.substring(0,1).toUpperCase();
            document.getElementById('detFechaRad').textContent       = d.fecha_radicacion;
            document.getElementById('detInstructor').textContent     = d.instructor.nickname;

            const tb = document.getElementById('detTipoBadge');
            tb.style.backgroundColor = d.tipo_badge.bg;
            tb.style.color           = d.tipo_badge.text;
            tb.textContent           = d.tipo_nombre;

            const eb = document.getElementById('detEstadoBadge');
            eb.style.backgroundColor = d.estado_badge.bg;
            eb.style.color           = d.estado_badge.text;
            eb.textContent           = d.estado;

            if (!soloLectura) {
                document.getElementById('detBtnResponder').onclick = () => {
                    bootstrap.Modal.getInstance(document.getElementById('modalDetallePqrs')).hide();
                    abrirModalResponder(d.id_pqrs, d.radicado, d.asunto);
                };
                document.getElementById('detBtnCambiarEstado').onclick = () => {
                    bootstrap.Modal.getInstance(document.getElementById('modalDetallePqrs')).hide();
                    abrirModalEstado(d.id_pqrs, d.radicado, d.estado);
                };
                document.getElementById('detBtnHistorial').onclick = () => {
                    bootstrap.Modal.getInstance(document.getElementById('modalDetallePqrs')).hide();
                    abrirModalHistorial(d.id_pqrs, d.radicado);
                };
            } else {
                document.getElementById('detBtnHistorialRO').onclick = () => {
                    bootstrap.Modal.getInstance(document.getElementById('modalDetallePqrs')).hide();
                    abrirModalHistorial(d.id_pqrs, d.radicado);
                };
            }

            // Timeline
            const tc = document.getElementById('detTimelineContainer');
            tc.innerHTML = '';
            if (!d.historial || d.historial.length === 0) {
                tc.innerHTML = '<div class="text-center py-3 text-muted small">Sin eventos adicionales.</div>';
            } else {
                d.historial.forEach(h => {
                    tc.innerHTML += `
                    <div class="timeline-item">
                        <span class="timeline-dot" style="background:#39A900;"></span>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-bold text-dark small">${h.estado_nuevo ? 'Estado: '+h.estado_nuevo : 'Actuación'}</span>
                            <small class="text-muted" style="font-size:.72rem;">${h.fecha}</small>
                        </div>
                        <div class="text-muted small">${h.observacion || 'Sin observaciones'}</div>
                        <div class="text-secondary mt-1" style="font-size:.71rem;">Por: <strong>${h.autor}</strong></div>
                    </div>`;
                });
            }

            // Respuestas
            const rc = document.getElementById('detRespuestasContainer');
            rc.innerHTML = '';
            if (!d.respuestas || d.respuestas.length === 0) {
                const msgBtn = soloLectura
                    ? '<div class="text-muted small mt-2">El instructor aún no ha respondido esta solicitud.</div>'
                    : `<button type="button" class="btn btn-sm text-white rounded-pill px-3 mt-2 d-block mx-auto"
                               style="background:#39A900;"
                               onclick="document.getElementById('detBtnResponder').click()">
                           <i class="fas fa-reply me-1"></i>Escribir primera respuesta
                       </button>`;
                rc.innerHTML = `
                <div class="text-center py-5 text-muted small">
                    <i class="fas fa-comment-slash fa-2x mb-2 d-block opacity-40"></i>
                    Esta solicitud aún no tiene respuestas.
                    ${msgBtn}
                </div>`;
            } else {
                d.respuestas.forEach(r => {
                    rc.innerHTML += `
                    <div class="p-3 mb-3 rounded-3 border-start border-4 border-success"
                         style="background:#F0FDF4;">
                        <div class="d-flex justify-content-between mb-1">
                            <strong class="text-dark small">
                                <i class="fas fa-user-shield text-success me-1"></i>${r.autor}
                            </strong>
                            <small class="text-muted" style="font-size:.72rem;">${r.fecha}</small>
                        </div>
                        <div class="text-dark small mt-2" style="white-space:pre-wrap;">${r.respuesta}</div>
                    </div>`;
                });
            }
        })
        .catch(() => window.showSystemAlert('Error al cargar el detalle de la PQRS.', 'error'));
}

// ── 2. MODAL RESPONDER ─────────────────────────────────────────────────────
function abrirModalResponder(id, radicado, asunto) {
    document.getElementById('respModalRadicado').textContent = radicado;
    document.getElementById('respModalAsunto').textContent   = asunto;
    document.getElementById('formResponderPqrs').action      = `/control-ecp/administrador/pqr/${id}/responder`;
    document.getElementById('respTextarea').value            = '';
    new bootstrap.Modal(document.getElementById('modalResponderPqrs')).show();
}

// ── 3. MODAL CAMBIAR ESTADO ────────────────────────────────────────────────
function abrirModalEstado(id, radicado, estadoActual) {
    document.getElementById('estadoModalRadicado').textContent = radicado;
    document.getElementById('nuevoEstadoSelect').value          = estadoActual;
    document.getElementById('obsEstadoText').value              = '';
    document.getElementById('formCambiarEstadoPqrs').action     = `/control-ecp/administrador/pqr/${id}/estado`;
    new bootstrap.Modal(document.getElementById('modalCambiarEstadoPqrs')).show();
}

// ── 4. MODAL HISTORIAL ─────────────────────────────────────────────────────
function abrirModalHistorial(id, radicado) {
    document.getElementById('historialModalRadicado').textContent = radicado;
    document.getElementById('historialTimelineContainer').innerHTML =
        '<div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2" aria-hidden="true"></i>Cargando historial…</div>';

    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalHistorialPqrs')).show();

    fetch(`/control-ecp/administrador/pqr/${id}/historial-json`)
        .then(response => {
            if (!response.ok) throw new Error('No se pudo cargar el historial.');
            return response.json();
        })
        .then(data => {
            const container = document.getElementById('historialTimelineContainer');
            container.replaceChildren();

            if (!data.eventos || data.eventos.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'pqr-history-empty';
                empty.textContent = 'Aún no hay cambios registrados.';
                container.appendChild(empty);
                return;
            }

            const summary = document.createElement('div');
            summary.className = 'd-flex flex-wrap gap-2 mb-4 p-3 rounded-3 border';
            summary.style.background = '#faf8ff';

            const radicadoBadge = document.createElement('span');
            radicadoBadge.className = 'badge bg-secondary font-monospace px-2 py-1';
            radicadoBadge.textContent = data.radicado || radicado;

            const subject = document.createElement('span');
            subject.className = 'fw-semibold text-dark small';
            subject.textContent = data.asunto || '';

            const state = document.createElement('span');
            state.className = 'badge rounded-pill ms-auto px-3';
            state.style.backgroundColor = data.estado_badge?.bg || '#F1F5F9';
            state.style.color = data.estado_badge?.text || '#475569';
            state.textContent = data.estado || '';
            summary.append(radicadoBadge, subject, state);

            const timeline = document.createElement('div');
            timeline.className = 'pqr-history-timeline';
            data.eventos.forEach(event => {
                const item = document.createElement('article');
                item.className = 'pqr-history-event';

                const heading = document.createElement('div');
                heading.className = 'pqr-history-event-heading';

                const title = document.createElement('span');
                title.className = 'pqr-history-event-status';
                title.textContent = event.titulo || 'Evento de PQRS';

                const date = document.createElement('time');
                date.className = 'pqr-history-event-date';
                date.textContent = event.fecha || '—';
                heading.append(title, date);

                const note = document.createElement('div');
                note.className = 'pqr-history-event-note';
                note.textContent = event.descripcion || 'Sin detalle adicional.';

                const author = document.createElement('div');
                author.className = 'small text-muted mt-1';
                author.textContent = `Por: ${event.autor || 'Sistema'}`;

                item.append(heading, note, author);
                timeline.appendChild(item);
            });
            container.append(summary, timeline);
        })
        .catch(() => {
            const container = document.getElementById('historialTimelineContainer');
            const error = document.createElement('div');
            error.className = 'alert alert-danger rounded-3 mb-0';
            error.textContent = 'No se pudo cargar el historial. Intenta nuevamente.';
            container.replaceChildren(error);
        });
}

function abrirTrazabilidadPqrs(id, radicado) {
    const contenedor = document.getElementById('trazabilidadTimelineContainer');
    document.getElementById('trazabilidadModalRadicado').textContent = radicado;
    contenedor.innerHTML =
        '<div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2" aria-hidden="true"></i>Cargando trazabilidad…</div>';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalTrazabilidadPqrs')).show();

    fetch(`/control-ecp/administrador/pqr/${id}/historial-json`)
        .then(response => {
            if (!response.ok) throw new Error('No se pudo cargar la trazabilidad.');
            return response.json();
        })
        .then(data => {
            contenedor.replaceChildren();
            if (!data.eventos || data.eventos.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'pqr-trace-empty';
                empty.textContent = 'No hay eventos de trazabilidad registrados.';
                contenedor.appendChild(empty);
                return;
            }

            const timeline = document.createElement('div');
            timeline.className = 'pqr-trace-timeline';
            data.eventos.forEach(evento => {
                const item = document.createElement('article');
                item.className = 'pqr-trace-event';

                const heading = document.createElement('div');
                heading.className = 'pqr-trace-event-heading';

                const title = document.createElement('span');
                title.className = 'pqr-trace-event-title';
                title.textContent = evento.titulo || 'Evento de PQRS';

                const date = document.createElement('time');
                date.className = 'pqr-trace-event-date';
                date.textContent = evento.fecha || '—';
                heading.append(title, date);

                const detail = document.createElement('div');
                detail.className = 'pqr-trace-event-detail';
                detail.textContent = evento.descripcion || 'Sin detalle adicional.';

                const author = document.createElement('div');
                author.className = 'small text-muted mt-1';
                const authorIcon = document.createElement('i');
                authorIcon.className = 'fas fa-user me-1';
                authorIcon.setAttribute('aria-hidden', 'true');
                author.append(authorIcon, document.createTextNode(`Por: ${evento.autor || 'Sistema'}`));

                item.append(heading, detail, author);
                timeline.appendChild(item);
            });
            contenedor.appendChild(timeline);
        })
        .catch(() => {
            contenedor.replaceChildren();
            const error = document.createElement('div');
            error.className = 'alert alert-danger rounded-3 mb-0';
            error.textContent = 'No se pudo cargar la trazabilidad. Intenta nuevamente.';
            contenedor.appendChild(error);
        });
}

function abrirRespuestasInstructorPqrs(id, radicado) {
    const contenedor = document.getElementById('respuestasInstructorContainer');
    document.getElementById('respuestasInstructorRadicado').textContent = radicado;
    contenedor.innerHTML =
        '<div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2" aria-hidden="true"></i>Cargando respuestas…</div>';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalRespuestasInstructorPqrs')).show();

    fetch(`/control-ecp/administrador/pqr/${id}/detalle-json`)
        .then(response => {
            if (!response.ok) throw new Error('No se pudieron cargar las respuestas.');
            return response.json();
        })
        .then(data => {
            contenedor.replaceChildren();
            if (!data.respuestas || data.respuestas.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'pqr-response-empty';
                const icon = document.createElement('i');
                icon.className = 'fas fa-comment-slash pqr-response-empty-icon d-block mb-2';
                icon.setAttribute('aria-hidden', 'true');
                const message = document.createElement('div');
                message.textContent = 'Esta solicitud aún no tiene respuestas.';
                const note = document.createElement('div');
                note.className = 'small mt-2';
                note.textContent = 'El instructor aún no ha respondido esta solicitud.';
                empty.append(icon, message, note);
                contenedor.appendChild(empty);
                return;
            }

            const list = document.createElement('div');
            list.className = 'd-grid gap-3';
            data.respuestas.forEach(respuesta => {
                const item = document.createElement('article');
                item.className = 'pqr-response-item';

                const heading = document.createElement('div');
                heading.className = 'd-flex justify-content-between flex-wrap gap-2 mb-2';
                const author = document.createElement('strong');
                author.className = 'text-dark';
                author.textContent = respuesta.autor || 'Instructor';
                const date = document.createElement('time');
                date.className = 'small text-muted';
                date.textContent = respuesta.fecha || '—';
                heading.append(author, date);

                const text = document.createElement('div');
                text.className = 'pqr-response-text';
                text.textContent = respuesta.respuesta || '';
                item.append(heading, text);
                list.appendChild(item);
            });
            contenedor.appendChild(list);
        })
        .catch(() => {
            contenedor.replaceChildren();
            const error = document.createElement('div');
            error.className = 'alert alert-danger rounded-3 mb-0';
            error.textContent = 'No se pudieron cargar las respuestas. Intenta nuevamente.';
            contenedor.appendChild(error);
        });
}

// ── 5. GRÁFICAS REPORTE ────────────────────────────────────────────────────
let graficasPqrsInicializadas = false;
function inicializarGraficasPqrs() {
    if (graficasPqrsInicializadas) return;
    setTimeout(() => {
        const ctxT = document.getElementById('chartPqrsTipo');
        if (ctxT) new Chart(ctxT, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($reporteTiposLabels) !!},
                datasets: [{ data: {!! json_encode($reporteTiposCounts) !!},
                    backgroundColor: ['#0284C7','#EF4444','#F97316','#8B5CF6','#10B981'],
                    borderWidth: 2, borderColor: '#fff' }]
            },
            options: { responsive:true, maintainAspectRatio:false,
                plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10, font:{ size:9 } } } } }
        });

        const ctxE = document.getElementById('chartPqrsEstado');
        if (ctxE) new Chart(ctxE, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($reporteEstadosLabels) !!},
                datasets: [{ data: {!! json_encode($reporteEstadosCounts) !!},
                    backgroundColor: ['#F59E0B','#4F46E5','#10B981'],
                    borderWidth: 2, borderColor: '#fff' }]
            },
            options: { responsive:true, maintainAspectRatio:false, cutout:'65%',
                plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10, font:{ size:9 } } } } }
        });

        const ctxI = document.getElementById('chartPqrsInstructor');
        if (ctxI) new Chart(ctxI, {
            type: 'bar',
            data: {
                labels: {!! json_encode($reporteInstructoresLabels) !!},
                datasets: [{ label:'PQRS Asignadas',
                    data: {!! json_encode($reporteInstructoresCounts) !!},
                    backgroundColor: '#39A900', borderRadius: 6 }]
            },
            options: { indexAxis:'y', responsive:true, maintainAspectRatio:false,
                plugins:{ legend:{ display:false } }, scales:{ x:{ beginAtZero:true, ticks:{ precision:0 } } } }
        });

        graficasPqrsInicializadas = true;
    }, 250);
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-open-after-validation="true"]').forEach(modalElement => {
        bootstrap.Modal.getOrCreateInstance(modalElement).show();
    });
});
</script>
@endpush
@endsection
