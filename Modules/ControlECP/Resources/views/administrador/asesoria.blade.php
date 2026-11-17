
@extends('controlecp::components.layouts.dashboard')

@push('styles')

<style>
    :root {
        --verde: #39A900;
        --verde-claro: #d8f3dc;
        --petroleo: #001A29;
        --petroleo-medio: #00324D;
        --azul-claro: #d0e8f5;
        --gris-texto: #52796f;
        --gris-claro: #94a3b8;
        --borde: #e0ece4;
    }

    .font-outfit {
        font-family: 'Outfit', sans-serif;
    }

    .asesoria-header-card {
        background-color: var(--petroleo);
        border-radius: 24px;
        padding: 1.25rem 1.5rem;
    }

    .asesoria-header-card h1 {
        color: #ffffff !important;
    }

    .asesoria-header-card p {
        color: #c7d4da !important;
    }

    .asesoria-header-card .btn-ecp-outline {
        color: #ffffff;
        border-color: rgba(255, 255, 255, .65);
    }

    /* Tarjetas con elevación suave */
    .ecp-card {
        background: #ffffff;
        border: 1px solid var(--borde);
        border-radius: 14px;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .asesoria-kpi {
        padding: 1rem 1.25rem;
        border: 2px solid transparent;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .asesoria-kpi:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 26, 41, .1);
    }

    .asesoria-kpi-label {
        font-size: .95rem;
        color: #475569;
        font-weight: 500;
    }

    .asesoria-kpi-value {
        font-size: 1.7rem;
        font-weight: 800;
        line-height: 1;
        margin-top: .15rem;
    }

    .asesoria-kpi-subtitle {
        font-size: .9rem;
        color: #64748b;
    }
    .asesoria-view-toggle {
        display: inline-flex;
        gap: .2rem;
        padding: .25rem;
        border: 1px solid #dce5eb;
        border-radius: 999px;
        background: #f8fafc;
    }
    .asesoria-view-toggle button {
        min-height: 42px;
        padding: .55rem 1.15rem;
        border: 0;
        border-radius: 999px;
        background: transparent;
        color: var(--petroleo);
        font-weight: 750;
        white-space: nowrap;
    }
    .asesoria-view-toggle button.is-active {
        background: var(--verde);
        color: #fff;
    }
    .asesoria-calendar-card { overflow:hidden; border:1px solid #dce8df; border-radius:16px; background:#fff; }
    .asesoria-calendar-toolbar { display:flex; min-height:58px; align-items:center; justify-content:space-between; gap:1rem; padding:.65rem 1rem; border-bottom:1px solid #e7edf0; }
    .asesoria-calendar-toolbar button { display:grid; width:34px; height:34px; flex:0 0 34px; place-items:center; border:1px solid #cbd5e1; border-radius:50%; background:#fff; color:#526579; }
    .asesoria-calendar-toolbar button:hover { border-color:var(--verde); color:var(--verde); }
    .asesoria-calendar-toolbar .asesoria-calendar-today { width:auto; height:auto; flex-basis:auto; margin-left:0; padding:.35rem .75rem; border-color:var(--verde); color:#318f00; font-size:.8rem; font-weight:700; }
    .asesoria-calendar-month-nav { display:flex; align-items:center; gap:.55rem; }
    .asesoria-calendar-month { color:var(--petroleo); font-size:.95rem; font-weight:800; }
    .asesoria-calendar-body { padding:.85rem; }
    .asesoria-calendar-weekdays,
    .asesoria-calendar-grid { display:grid; grid-template-columns:repeat(7,minmax(0,1fr)); }
    .asesoria-calendar-weekday { padding:.65rem .4rem; background:var(--petroleo); color:#fff; font-size:.7rem; font-weight:800; text-align:center; }
    .asesoria-calendar-weekday:first-child { border-top-left-radius:8px; }
    .asesoria-calendar-weekday:last-child { border-top-right-radius:8px; }
    .asesoria-calendar-day { min-width:0; min-height:88px; padding:.55rem; border:0; border-right:1px solid #d9e3ea; border-bottom:1px solid #d9e3ea; background:#fff; color:#334155; text-align:left; vertical-align:top; }
    .asesoria-calendar-day:nth-child(7n+1) { border-left:1px solid #d9e3ea; }
    .asesoria-calendar-day:hover { background:#f6fff2; }
    .asesoria-calendar-day.is-today .asesoria-calendar-day-number { display:inline-flex; width:22px; height:22px; align-items:center; justify-content:center; border-radius:50%; background:var(--verde); color:#fff; }
    .asesoria-calendar-day.is-outside { background:#eaf1f5; color:#82909b; }
    .asesoria-calendar-day-heading { display:flex; flex-direction:column; align-items:flex-start; gap:.25rem; }
    .asesoria-calendar-day-number { display:inline-flex; min-height:22px; align-items:center; color:#334155; font-size:.8rem; font-weight:800; }
    .asesoria-calendar-day.is-outside .asesoria-calendar-day-number { color:#82909b; }
    .asesoria-calendar-day-count { display:block; width:100%; overflow:hidden; margin-top:.3rem; padding:.25rem .4rem; border-left:3px solid #0284c7; border-radius:5px; background:#eaf5ff; color:#075985; font-size:.68rem; font-weight:700; line-height:1.25; text-align:left; text-overflow:ellipsis; white-space:nowrap; }
    .asesoria-calendar-day.is-done .asesoria-calendar-day-count { border-left-color:var(--verde); background:#eef9e9; color:#287a13; }
    .asesoria-admin-calendar-item { padding:1rem; border:1px solid #dce5eb; border-radius:12px; background:#fff; }
    .asesoria-admin-calendar-item + .asesoria-admin-calendar-item { margin-top:.75rem; }
    .asesoria-admin-calendar-time { color:var(--petroleo); font-weight:800; }
    .asesoria-calendar-selection { min-height:80px; margin-top:1rem; padding:1rem; border:1px solid #e5edf2; border-radius:14px; background:#f8fafc; }
    .asesoria-calendar-event { padding:.8rem; border:1px solid #e1e9ee; border-left:4px solid #0284c7; border-radius:10px; background:#fff; }
    .asesoria-calendar-event + .asesoria-calendar-event { margin-top:.6rem; }
    .asesoria-calendar-event-title { color:var(--petroleo); font-size:.88rem; font-weight:800; }
    .asesoria-calendar-event-meta { margin-top:.25rem; color:#64748b; font-size:.78rem; }
    .asesoria-calendar-event-status { display:inline-flex; margin-top:.45rem; padding:.16rem .55rem; border-radius:999px; background:#e0f2fe; color:#0369a1; font-size:.7rem; font-weight:750; }
    .asesoria-calendar-event-status[data-state="Realizada"] { background:#e1f4e9; color:#16804a; }
    .asesoria-calendar-event-status[data-state="Rechazada"],
    .asesoria-calendar-event-status[data-state="Cancelada"] { background:#fff0f0; color:#d63939; }
    .asesoria-calendar-event-status[data-state="Solicitada"] { background:#fef3c7; color:#a16207; }
    .asesoria-detail-motive { border:1px solid #e0ece4 !important; border-radius:14px; background:#fff; }
    .asesoria-detail-motive-text { padding:.45rem; border-radius:6px; background:#f8f9fa; }
    .asesoria-detail-section { border:1px solid #e0ece4 !important; border-radius:14px; background:#fff; }
    @media (max-width:575px) {
        .asesoria-view-toggle { display:flex; width:100%; }
        .asesoria-view-toggle button { flex:1; padding:.55rem .65rem; font-size:.85rem; }
        .asesoria-calendar-toolbar { padding:.6rem; }
        .asesoria-calendar-body { padding:.55rem; }
        .asesoria-calendar-grid { gap:1px; }
        .asesoria-calendar-weekday { min-height:32px; font-size:.58rem; }
        .asesoria-calendar-day { min-height:68px; padding:.35rem; font-size:.78rem; }
        .asesoria-calendar-day-count { padding:.15rem .3rem; font-size:.55rem; }
    }

    .ecp-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 26, 41, 0.08);
    }

    /* Botones temáticos */
    .btn-ecp-primary {
        background-color: var(--verde);
        color: #ffffff;
        border: 1px solid var(--verde);
        font-weight: 600;
        border-radius: 50rem;
        transition: all .2s ease;
    }

    .btn-ecp-primary:hover {
        background-color: #2e8b00;
        border-color: #2e8b00;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(57, 169, 0, 0.3);
    }

    .btn-ecp-outline {
        background-color: transparent;
        color: var(--petroleo);
        border: 1px solid var(--petroleo);
        font-weight: 500;
        border-radius: 50rem;
        transition: all .2s ease;
    }

    .btn-ecp-outline:hover {
        background-color: var(--petroleo);
        color: #ffffff;
    }

    .ecp-card form .btn-outline-secondary {
        background-color: #ffffff;
        color: var(--petroleo);
        border-color: #ffffff;
    }

    .ecp-card form .btn-outline-secondary:hover {
        background-color: #f1f5f9;
        color: var(--petroleo);
        border-color: #ffffff;
    }

    .asesoria-filter-card {
        padding: 1rem 1.15rem;
        margin-bottom: 1.5rem;
        background: var(--petroleo);
        border-radius: 18px;
        box-shadow: 0 5px 20px rgba(0, 26, 41, .08);
    }

    .asesoria-filter-form {
        display: grid;
        grid-template-columns: minmax(220px, 1.5fr) repeat(2, minmax(145px, 1fr)) minmax(170px, 1fr) 160px;
        align-items: end;
        gap: .65rem;
    }

    .asesoria-filter-field { min-width: 0; }

    .asesoria-filter-field .form-label {
        display: block;
        margin: 0 0 .3rem;
        color: #fff !important;
        font-size: .75rem;
        font-weight: 700;
    }

    .asesoria-filter-field .form-control,
    .asesoria-filter-field .form-select {
        width: 100%;
        min-width: 0;
        height: 44px;
        padding: .55rem .8rem;
        border: 1px solid #fff;
        border-radius: 10px !important;
        background: #fff;
        color: var(--petroleo);
        font-size: .88rem;
        box-shadow: none;
    }

    .asesoria-filter-actions {
        display: flex;
        gap: .5rem;
        min-width: 0;
    }

    .asesoria-filter-actions .btn-filter {
        flex: 1;
        min-width: 0;
        height: 44px;
        border: 0;
        border-radius: 10px;
        background: var(--verde);
        color: #fff;
        font-weight: 700;
    }

    .asesoria-filter-actions .btn-filter:hover {
        background: #2e8b00;
        color: #fff;
    }

    .asesoria-filter-actions .btn-reset {
        flex: 0 0 48px;
        height: 44px;
        display: grid;
        place-items: center;
        padding: 0;
        border: 1px solid #fff;
        border-radius: 10px;
        background: #fff;
        color: var(--petroleo);
    }

    .asesoria-filter-actions .btn-reset:hover {
        background: #f1f5f9;
        color: var(--petroleo);
    }

    @media (max-width: 1100px) {
        .asesoria-filter-form { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .asesoria-filter-actions { grid-column: span 3; justify-content: flex-end; }
        .asesoria-filter-actions .btn-filter { flex: 0 0 130px; }
    }

    @media (max-width: 767px) {
        .asesoria-filter-form { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .asesoria-filter-actions { grid-column: span 2; }
    }

    @media (max-width: 480px) {
        .asesoria-filter-form { grid-template-columns: minmax(0, 1fr); }
        .asesoria-filter-actions { grid-column: auto; }
        .asesoria-filter-actions .btn-filter { flex: 1; }
    }

    /* Badges de estado */
    .badge-estado-solicitada {
        background-color: #fef3c7;
        color: #b45309;
        border: 1px solid #fde68a;
    }

    .badge-estado-programada {
        background-color: var(--azul-claro);
        color: #0369a1;
        border: 1px solid #bae6fd;
    }

    .badge-estado-realizada {
        background-color: var(--verde-claro);
        color: #15803d;
        border: 1px solid #bbf7d0;
    }

    .badge-estado-cancelada {
        background-color: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    .badge-estado-rechazada {
        background-color: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    .badge-estado-sin-asignar {
        background-color: #ffedd5;
        color: #c2410c;
        border: 1px solid #fed7aa;
    }

    .asesoria-row-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: .4rem;
        white-space: nowrap;
    }
    .asesoria-category-actions {
        display: flex;
        justify-content: flex-end;
        gap: .4rem;
        white-space: nowrap;
    }
    .asesoria-category-actions .asesoria-row-action {
        width: 34px;
        height: 34px;
        border-radius: 10px !important;
    }
    .asesoria-category-actions .asesoria-row-action--edit {
        background: #fff7e6;
        border-color: #f8e3b2;
        color: #c47a00;
    }
    .asesoria-category-actions .asesoria-row-action--edit:hover {
        background: #c47a00;
        border-color: #c47a00;
        color: #fff;
    }
    .asesoria-category-actions .asesoria-row-action--category-disable {
        background: #fff0f0;
        border-color: #f7cccc;
        color: #d63939;
    }
    .asesoria-category-actions .asesoria-row-action--category-disable:hover {
        background: #d63939;
        border-color: #d63939;
        color: #fff;
    }
    .asesoria-category-actions .asesoria-row-action--category-enable {
        background: #eaf8ef;
        border-color: #ccebd6;
        color: #278344;
    }
    .asesoria-category-actions .asesoria-row-action--category-enable:hover {
        background: #278344;
        border-color: #278344;
        color: #fff;
    }
    .asesoria-row-action {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border: 1px solid transparent;
        border-radius: 10px !important;
        transition: background-color .15s ease, color .15s ease, transform .15s ease, box-shadow .15s ease;
    }
    .asesoria-row-action:hover,
    .asesoria-row-action:focus-visible {
        transform: translateY(-2px);
        box-shadow: 0 3px 8px rgba(0, 26, 41, .14);
    }
    .asesoria-row-action--view {
        background: #eaf5ff;
        border-color: #c9e6fb;
        color: #0876bd;
    }
    .asesoria-row-action--view:hover {
        background: #0876bd;
        border-color: #0876bd;
        color: #fff;
    }
    .asesoria-row-action--edit {
        background: #f1f5f9;
        border-color: #dbe3eb;
        color: #475569;
    }
    .asesoria-row-action--edit:hover {
        background: #475569;
        border-color: #475569;
        color: #fff;
    }
    .asesoria-row-action--rating {
        background: #fff7e6;
        border-color: #f8e3b2;
        color: #e8a000;
    }
    .asesoria-row-action--rating:hover {
        background: #e8a000;
        border-color: #e8a000;
        color: #fff;
    }
    .asesoria-row-action--schedule,
    .asesoria-row-action--finish {
        background: #eaf8ef;
        border-color: #ccebd6;
        color: #278344;
    }
    .asesoria-row-action--schedule:hover,
    .asesoria-row-action--finish:hover {
        background: #278344;
        border-color: #278344;
        color: #fff;
    }
    .asesoria-row-action--resources {
        background: #f3edff;
        border-color: #ddccff;
        color: #7541c8;
    }
    .asesoria-row-action--resources:hover {
        background: #7541c8;
        border-color: #7541c8;
        color: #fff;
    }
    .asesoria-row-action--reschedule {
        background: #fff7e6;
        border-color: #f8e3b2;
        color: #c47a00;
    }
    .asesoria-row-action--reschedule:hover {
        background: #c47a00;
        border-color: #c47a00;
        color: #fff;
    }
    .asesoria-row-action--cancel {
        background: #fff0f0;
        border-color: #f7cccc;
        color: #d63939;
    }
    .asesoria-row-action--cancel:hover {
        background: #d63939;
        border-color: #d63939;
        color: #fff;
    }
    .asesoria-row-action--followup {
        background: #eaf8ef;
        border-color: #ccebd6;
        color: #278344;
    }
    .asesoria-row-action--followup:hover {
        background: #278344;
        border-color: #278344;
        color: #fff;
    }
    .asesoria-admin-modal .modal-content {
        overflow: hidden;
        border: 0;
        border-radius: 22px;
        box-shadow: 0 20px 55px rgba(0, 26, 41, .22);
    }
    .asesoria-admin-modal .modal-header {
        gap: .85rem;
        padding: 1.25rem 1.5rem;
        border: 0;
        border-bottom: 2px solid var(--verde);
        background: linear-gradient(135deg, #001A29 0%, #12394d 100%) !important;
        color: #fff;
    }
    .asesoria-admin-modal .modal-header .modal-title { color: #fff !important; }
    .asesoria-admin-modal .modal-header .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }
    .asesoria-admin-modal .modal-header .rounded-circle {
        width: 46px !important;
        height: 46px !important;
        flex: 0 0 46px;
        border: 1px solid rgba(255, 255, 255, .25);
        border-radius: 15px !important;
        background: rgba(255, 255, 255, .1) !important;
        color: #86efac;
        font-size: 1.1rem;
    }
    .asesoria-admin-modal#modalDetalle .modal-header .rounded-circle,
    .asesoria-admin-modal#modalSolicitudesCalendario .modal-header .rounded-circle { color: #7dd3fc; }
    .asesoria-admin-modal#modalReprogramar .modal-header .rounded-circle { color: #fcd34d; }
    .asesoria-admin-modal#modalCancelar .modal-header .rounded-circle,
    .asesoria-admin-modal#modalRechazarSolicitudAdmin .modal-header .rounded-circle { color: #fca5a5; }
    .asesoria-admin-modal#modalRecursos .modal-header .rounded-circle { color: #c4b5fd; }
    .asesoria-admin-modal#modalProgramar .modal-header .rounded-circle,
    .asesoria-admin-modal#modalReprogramar .modal-header .rounded-circle {
        background: rgba(245, 158, 11, .16) !important;
        border-color: rgba(252, 211, 77, .38);
        color: #fcd34d;
    }
    .asesoria-admin-modal#modalCancelar .modal-header .rounded-circle {
        background: rgba(239, 68, 68, .16) !important;
        border-color: rgba(252, 165, 165, .38);
        color: #fca5a5;
    }
    .asesoria-admin-modal#modalSeguimiento .modal-header .rounded-circle {
        background: rgba(34, 197, 94, .16) !important;
        border-color: rgba(134, 239, 172, .38);
        color: #86efac;
    }
    .asesoria-admin-modal#modalRecursos .modal-header .rounded-circle {
        background: rgba(139, 92, 246, .16) !important;
        border-color: rgba(196, 181, 253, .38);
        color: #c4b5fd;
    }
    .asesoria-admin-rating-modal .modal-header .rounded-circle {
        background: rgba(245, 158, 11, .16) !important;
        border-color: rgba(252, 211, 77, .38);
        color: #fcd34d;
    }
    .asesoria-admin-rating-stars { display: flex; gap: .3rem; color: #f59e0b; font-size: 1.75rem; }
    .asesoria-admin-rating-stars .is-empty { color: #cbd5e1; }
    .asesoria-admin-modal#modalSolicitudesCalendario .modal-dialog { max-width:560px; }
    .asesoria-admin-modal#modalSolicitudesCalendario .modal-header { min-height:90px; }
    .asesoria-admin-modal#modalSolicitudesCalendario .modal-header .rounded-circle {
        background:rgba(14,165,233,.16) !important;
        border-color:rgba(125,211,252,.38);
        color:#7dd3fc;
    }
    .asesoria-admin-modal-subtitle { margin:.2rem 0 0; color:#c7d4da; font-size:.82rem; }
    .asesoria-admin-modal .modal-body { background: #f8fafc; }
    .asesoria-admin-modal .modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #edf1f5;
        background: #fff;
    }
    .asesoria-admin-modal .modal-footer .btn { min-height: 40px; border-radius: 999px; font-weight: 700; }
    .asesoria-admin-modal .form-control,
    .asesoria-admin-modal .form-select {
        border-radius: 12px;
        box-shadow: none;
    }
    .asesoria-admin-modal .form-control:focus,
    .asesoria-admin-modal .form-select:focus {
        border-color: var(--verde);
        box-shadow: 0 0 0 .2rem rgba(57, 169, 0, .13);
    }
    .asesoria-admin-modal .modal-footer .btn-ecp-primary { background: #168a5b; border-color: #168a5b; color: #fff; }
    .asesoria-admin-modal .modal-footer .btn-ecp-primary:hover { background: #11734b; border-color: #11734b; color: #fff; }
    .asesoria-admin-modal .modal-footer .btn-danger { background: #e83245; border-color: #e83245; }
    .asesoria-admin-modal .modal-footer .btn-danger:hover { background: #cc293a; border-color: #cc293a; }
    .asesoria-admin-modal#modalProgramar .modal-dialog,
    .asesoria-admin-modal#modalReprogramar .modal-dialog,
    .asesoria-admin-modal#modalCancelar .modal-dialog,
    .asesoria-admin-modal#modalRecursos .modal-dialog { max-width: 520px; }
    .asesoria-admin-modal#modalSeguimiento .modal-dialog { max-width: 820px; }
    .asesoria-admin-modal#modalProgramar .form-control,
    .asesoria-admin-modal#modalProgramar .form-select,
    .asesoria-admin-modal#modalReprogramar .form-control,
    .asesoria-admin-modal#modalReprogramar .form-select,
    .asesoria-admin-modal#modalCancelar .form-control,
    .asesoria-admin-modal#modalCancelar .form-select,
    .asesoria-admin-modal#modalSeguimiento .form-control,
    .asesoria-admin-modal#modalSeguimiento .form-select,
    .asesoria-admin-modal#modalRecursos .form-control,
    .asesoria-admin-modal#modalRecursos .form-select { min-height: 46px; }
    .asesoria-admin-modal#modalProgramar textarea.form-control,
    .asesoria-admin-modal#modalReprogramar textarea.form-control,
    .asesoria-admin-modal#modalCancelar textarea.form-control,
    .asesoria-admin-modal#modalSeguimiento textarea.form-control { min-height: 112px; resize: vertical; }
    .asesoria-admin-modal#modalEditarAsesoria .modal-header .rounded-circle {
        background: rgba(255, 255, 255, .1) !important;
        border-color: rgba(255, 255, 255, .25);
        color: #e2e8f0;
    }
    .asesoria-admin-modal#modalEditarAsesoria .form-control,
    .asesoria-admin-modal#modalEditarAsesoria .form-select { min-height: 42px; }
    .asesoria-admin-modal#modalEditarAsesoria textarea.form-control { min-height: 72px; }
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
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-3 py-3 asesoria-admin-page">

    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
        @if(session('success'))
            <div class="toast align-items-center text-white border-0 show shadow" style="background-color: var(--verde);" role="alert" aria-live="assertive" aria-atomic="true">
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

        @if(session('info'))
            <div class="toast align-items-center text-white border-0 show shadow" style="background-color: var(--petroleo-medio);" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i class="fas fa-info-circle fs-5"></i>
                        <span>{{ session('info') }}</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
                </div>
            </div>
        @endif

        <div id="toastDinamico" class="toast align-items-center text-white border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true" data-system-alert-ignore style="display: none;">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i id="toastDinamicoIcon" class="fas fa-info-circle fs-5"></i>
                    <span id="toastDinamicoMensaje">Mensaje</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        </div>
    </div>

    <div class="asesoria-header-card d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small fw-semibold mb-1" style="color: var(--verde);">
                <i class="fas fa-chart-line me-2"></i>Control ECP · Panel administrativo
            </div>
            <h1 class="h3 fw-bold mb-1 font-outfit" style="color: var(--petroleo);">
                <i class="fas fa-comments me-2" style="color: var(--verde);"></i>Solicitudes de Asesoría
            </h1>
            <p class="mb-0 small" style="color: var(--gris-texto);">
                Revisa y gestiona las solicitudes de asesoría que te enviaron
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-ecp-outline px-3" data-bs-toggle="modal" data-bs-target="#modalDisponibilidad">
                <i class="fas fa-clock me-1"></i> Mi disponibilidad
            </button>
            <button type="button" class="btn btn-ecp-outline px-3" data-bs-toggle="modal" data-bs-target="#modalCategorias">
                <i class="fas fa-tags me-1"></i> Categorías
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">

        <div class="col-6 col-md-4 col-lg">
            <div class="ecp-card asesoria-kpi h-100" style="border-color:rgba(180,83,9,.35);">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="asesoria-kpi-label">Solicitadas</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: #fef3c7; color: #b45309;">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="asesoria-kpi-value" style="color:#b45309;">{{ $indicadores['solicitadas'] }}</div>
                <div class="asesoria-kpi-subtitle">Pendientes por programar</div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg">
            <div class="ecp-card asesoria-kpi h-100" style="border-color:rgba(2,132,199,.35);">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="asesoria-kpi-label">Programadas</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: var(--azul-claro); color: #0284c7;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
                <div class="asesoria-kpi-value" style="color:#0284c7;">{{ $indicadores['programadas'] }}</div>
                <div class="asesoria-kpi-subtitle">Con fecha e instructor</div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg">
            <div class="ecp-card asesoria-kpi h-100" style="border-color:rgba(21,128,61,.35);">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="asesoria-kpi-label">Realizadas</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: var(--verde-claro); color: #15803d;">
                        <i class="fas fa-circle-check"></i>
                    </div>
                </div>
                <div class="asesoria-kpi-value" style="color:#15803d;">{{ $indicadores['realizadas'] }}</div>
                <div class="asesoria-kpi-subtitle">Con seguimiento final</div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg">
            <div class="ecp-card asesoria-kpi h-100" style="border-color:rgba(194,65,12,.35);">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="asesoria-kpi-label">Rechazadas</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: #fee2e2; color: #b91c1c;">
                        <i class="fas fa-xmark"></i>
                    </div>
                </div>
                <div class="asesoria-kpi-value" style="color:#b91c1c;">{{ $indicadores['rechazadas'] }}</div>
                <div class="asesoria-kpi-subtitle">Solo consulta</div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg">
            <div class="ecp-card asesoria-kpi h-100" style="border-color:rgba(185,28,28,.35);">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="asesoria-kpi-label">Canceladas</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: #fee2e2; color: #b91c1c;">
                        <i class="fas fa-ban"></i>
                    </div>
                </div>
                <div class="asesoria-kpi-value" style="color:#b91c1c;">{{ $indicadores['canceladas'] }}</div>
                <div class="asesoria-kpi-subtitle">Anuladas con motivo</div>
            </div>
        </div>
    </div>

    <div class="asesoria-filter-card mb-4">
        <form method="GET" action="{{ route('controlecp.administrador.asesorias.index') }}" class="asesoria-filter-form">
            <input type="hidden" name="vista" value="solicitudes">
            <div class="asesoria-filter-field">
                <label class="form-label small fw-semibold mb-1"><i class="fas fa-search me-1"></i> Buscar Aprendiz</label>
                <input type="text" name="aprendiz" value="{{ request('aprendiz') }}" class="form-control" placeholder="Nombre, correo o documento...">
            </div>
            <div class="asesoria-filter-field">
                <label class="form-label small fw-semibold mb-1">Categoría</label>
                <select name="categoria_id" class="form-select">
                    <option value="">Todas</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id_categoria_asesoria }}" {{ request('categoria_id') == $cat->id_categoria_asesoria ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="asesoria-filter-field">
                <label class="form-label small fw-semibold mb-1">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    @foreach(['Solicitada', 'Programada', 'Realizada', 'Cancelada', 'Rechazada'] as $estado)
                        <option value="{{ $estado }}" {{ request('estado') === $estado ? 'selected' : '' }}>{{ $estado }}</option>
                    @endforeach
                </select>
            </div>
            <div class="asesoria-filter-field">
                <label class="form-label small fw-semibold mb-1">Fecha</label>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="form-control" title="Desde">
            </div>
            <div class="asesoria-filter-actions">
                <button type="submit" class="btn btn-filter" title="Buscar asesorías"><i class="fas fa-magnifying-glass me-1"></i>Buscar</button>
                <a href="{{ route('controlecp.administrador.asesorias.index', ['vista' => 'solicitudes']) }}" class="btn btn-reset" title="Restablecer filtros" aria-label="Restablecer filtros"><i class="fas fa-rotate-left"></i></a>
            </div>
        </form>
    </div>

    <div class="d-flex justify-content-start mb-4">
        <div class="asesoria-view-toggle" role="group" aria-label="Cambiar vista de asesorías">
            <button type="button" class="is-active" id="adminVistaCalendarioBtn" aria-pressed="true">
                <i class="fas fa-calendar-days me-2"></i>Vista Calendario
            </button>
            <button type="button" id="adminVistaSolicitudesBtn" aria-pressed="false">
                <i class="fas fa-list-ul me-2"></i>Vista Solicitudes
            </button>
        </div>
    </div>

    <section class="asesoria-calendar-card mb-4" id="adminVistaCalendario" aria-label="Calendario de solicitudes">
        <div class="asesoria-calendar-toolbar">
            <div class="asesoria-calendar-month-nav">
                <button type="button" id="adminCalendarioAnterior" aria-label="Mes anterior"><i class="fas fa-chevron-left"></i></button>
                <h2 class="asesoria-calendar-month text-capitalize mb-0" id="adminCalendarioMes"></h2>
                <button type="button" id="adminCalendarioSiguiente" aria-label="Mes siguiente"><i class="fas fa-chevron-right"></i></button>
            </div>
            <button type="button" class="asesoria-calendar-today" id="adminCalendarioHoy"><i class="fas fa-calendar-day me-1"></i>Hoy</button>
        </div>
        <div class="asesoria-calendar-body">
            <div class="asesoria-calendar-weekdays" aria-hidden="true">
                @foreach(['LUN','MAR','MIÉ','JUE','VIE','SÁB','DOM'] as $weekday)
                    <div class="asesoria-calendar-weekday">{{ $weekday }}</div>
                @endforeach
            </div>
            <div class="asesoria-calendar-grid" id="adminCalendarioGrid"></div>
        </div>
    </section>

    <div class="modal fade asesoria-admin-modal" id="modalSolicitudesCalendario" tabindex="-1" aria-labelledby="modalSolicitudesCalendarioLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius:16px; overflow:hidden;">
                <div class="modal-header text-white" style="background-color:var(--petroleo); border-bottom:2px solid var(--verde);">
                    <span class="rounded-circle d-flex align-items-center justify-content-center"><i class="fas fa-calendar-check" aria-hidden="true"></i></span>
                    <div>
                        <h2 class="modal-title fs-5 fw-bold mb-0" id="modalSolicitudesCalendarioLabel">Horario reservado</h2>
                        <p class="asesoria-admin-modal-subtitle" id="modalSolicitudesCalendarioFecha"></p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" id="modalSolicitudesCalendarioContenido"></div>
            </div>
        </div>
    </div>

    <div id="adminVistaSolicitudes" class="d-none">
    <div class="ecp-card mb-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background-color: var(--petroleo); color: #ffffff;">
                    <tr class="small text-uppercase" style="letter-spacing: 0.5px;">
                        <th class="ps-3 py-3">Aprendiz</th>
                        <th class="py-3">Categoría</th>
                        <th class="py-3">Motivo de solicitud</th>
                        <th class="py-3">Fecha y Hora</th>
                        <th class="py-3 text-center">Estado</th>
                        <th class="pe-3 py-3 text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($asesorias as $item)
                        @php // Inicia un bloque PHP para preparar datos que usará la vista.
                            $badgeClass = match($item->estado) {
                                'Solicitada' => 'badge-estado-solicitada',
                                'Programada' => 'badge-estado-programada',
                                'Realizada'  => 'badge-estado-realizada',
                                'Cancelada'  => 'badge-estado-cancelada',
                                'Rechazada' => 'badge-estado-rechazada',
                                default      => 'badge-estado-sin-asignar',
                            };
                            $sinAsignar = is_null($item->id_usuario_instructor) && !in_array($item->estado, ['Cancelada', 'Rechazada'], true);
                        @endphp
                        <tr>

                            <td class="ps-3 py-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width: 34px; height: 34px; background-color: var(--petroleo-medio); font-size: 0.75rem;">
                                        {{ $item->aprendiz ? $item->aprendiz->initials : 'AP' }}
                                    </div>
                                    <div>
                                        <span class="d-block fw-semibold text-truncate" style="max-width: 200px; color: var(--petroleo);">
                                            {{ $item->aprendiz ? $item->aprendiz->full_name : 'No registrado' }}
                                        </span>
                                        <small class="text-muted d-block" style="font-size: 0.72rem;">
                                            {{ $item->aprendiz ? $item->aprendiz->email : '' }}
                                        </small>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="badge rounded-pill bg-light text-dark border px-2 py-1 small">
                                    {{ $item->categoria ? $item->categoria->nombre : 'Sin categoría' }}
                                </span>
                            </td>

                            <td class="small" style="min-width: 260px; max-width: 380px; white-space: normal; overflow-wrap: anywhere;">
                                <span>
                                    {{ $item->motivo ?: 'Sin motivo registrado' }}
                                </span>
                            </td>

                            <td>
                                <div class="small">
                                    <span class="d-block fw-semibold" style="color: var(--petroleo);">
                                        <i class="fas fa-calendar-day me-1 text-muted"></i>{{ $item->fecha ? $item->fecha->format('d/m/Y') : '-' }}
                                    </span>
                                    <span class="text-muted" style="font-size: 0.72rem;">
                                        <i class="fas fa-clock me-1 text-muted"></i>{{ $item->hora_inicio_formateada }} - {{ $item->hora_fin_formateada }}
                                    </span>
                                </div>
                            </td>

                            <td class="text-center">
                                <span class="badge {{ $badgeClass }} rounded-pill px-3 py-1 small">
                                    {{ $item->estado }}
                                </span>
                            </td>

                            <td class="pe-3 py-3 text-end">
                                <div class="asesoria-row-actions">

                                    <button type="button" class="btn asesoria-row-action asesoria-row-action--view" title="Ver detalle completo" aria-label="Ver detalle completo" onclick="verDetalle({{ $item->id_asesoria }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if($item->estado !== 'Rechazada')
                                        <button type="button" class="btn asesoria-row-action asesoria-row-action--rating" title="{{ $item->calificacion ? 'Ver calificación' : 'Calificación pendiente' }}" aria-label="{{ $item->calificacion ? 'Ver calificación' : 'Calificación pendiente' }}" data-bs-toggle="modal" data-bs-target="#calificacion{{ $item->id_asesoria }}">
                                            <i class="fas fa-star" aria-hidden="true"></i>
                                        </button>
                                    @endif

                                    @if($item->estado === 'Programada')
                                        <button type="button" class="btn asesoria-row-action asesoria-row-action--reschedule" title="Reprogramar asesoría" aria-label="Reprogramar asesoría" onclick="abrirReprogramar({{ $item->id_asesoria }}, '{{ $item->fecha ? $item->fecha->format('Y-m-d') : '' }}', '{{ substr($item->hora_inicio, 0, 5) }}', '{{ substr($item->hora_fin, 0, 5) }}')">
                                            <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                                        </button>
                                    @elseif(!in_array($item->estado, ['Cancelada', 'Rechazada'], true) && !($item->estado === 'Solicitada' && $item->id_usuario_instructor))
                                        <button type="button" class="btn asesoria-row-action asesoria-row-action--edit" title="Editar asesoría" aria-label="Editar asesoría" onclick="abrirEditar({{ $item->id_asesoria }}, {{ $item->id_usuario_aprendiz }}, {{ $item->id_categoria_asesoria }}, '{{ $item->fecha ? $item->fecha->format('Y-m-d') : '' }}', '{{ substr($item->hora_inicio, 0, 5) }}', '{{ substr($item->hora_fin, 0, 5) }}', {{ $item->id_usuario_instructor ?? 'null' }}, '{{ addslashes(preg_replace('/\r|\n/', ' ', $item->motivo)) }}')">
                                            <i class="fas fa-edit" aria-hidden="true"></i>
                                        </button>
                                    @endif

                                    @if($item->estado === 'Solicitada' && !$item->id_usuario_instructor)
                                        <button type="button" class="btn asesoria-row-action asesoria-row-action--reschedule" title="Asignar y programar" aria-label="Asignar y programar" onclick="abrirProgramar({{ $item->id_asesoria }}, '{{ addslashes($item->aprendiz ? $item->aprendiz->full_name : '') }}', {{ $item->id_categoria_asesoria }}, '{{ $item->fecha ? $item->fecha->format('Y-m-d') : '' }}', '{{ substr($item->hora_inicio, 0, 5) }}', '{{ substr($item->hora_fin, 0, 5) }}', {{ $item->id_usuario_instructor ?? 'null' }}, '{{ addslashes($item->categoria ? $item->categoria->nombre : '') }}')">
                                            <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                                        </button>
                                    @endif

                                    @if($item->estado === 'Solicitada' && (int) $item->id_usuario_instructor === (int) auth()->id())
                                        <form method="POST" action="{{ route('controlecp.administrador.asesorias.solicitudes.aceptar', $item->id_asesoria) }}" class="d-inline" onsubmit="return confirm('¿Confirmas que deseas aceptar esta solicitud?');">
                                            @csrf @method('PUT')
                                            <button type="submit" class="btn asesoria-row-action asesoria-row-action--schedule" title="Aceptar solicitud" aria-label="Aceptar solicitud">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <button type="button" class="btn asesoria-row-action asesoria-row-action--cancel" title="Rechazar solicitud" aria-label="Rechazar solicitud"
                                            data-bs-toggle="modal" data-bs-target="#modalRechazarSolicitudAdmin"
                                            data-action="{{ route('controlecp.administrador.asesorias.solicitudes.rechazar', $item->id_asesoria) }}">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif

                                    @if($item->estado === 'Programada')
                                        <button type="button" class="btn asesoria-row-action asesoria-row-action--cancel" title="Cancelar asesoría" aria-label="Cancelar asesoría" onclick="abrirCancelar({{ $item->id_asesoria }})">
                                            <i class="fas fa-ban" aria-hidden="true"></i>
                                        </button>
                                    @endif

                                    @if($item->estado === 'Programada' || $item->estado === 'Realizada')
                                        <button type="button" class="btn asesoria-row-action asesoria-row-action--followup" title="Registrar seguimiento" aria-label="Registrar seguimiento" onclick="abrirSeguimiento({{ $item->id_asesoria }})">
                                            <i class="fas fa-clipboard-check" aria-hidden="true"></i>
                                        </button>
                                    @endif

                                    @if(in_array($item->estado, ['Programada', 'Realizada'], true))
                                        <button type="button" class="btn asesoria-row-action asesoria-row-action--resources" title="Vincular recursos" aria-label="Vincular recursos" onclick="abrirRecursos({{ $item->id_asesoria }})">
                                            <i class="fas fa-box" aria-hidden="true"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3 text-secondary opacity-50"></i>
                                    <h6 class="fw-bold text-secondary mb-1">No se encontraron asesorías</h6>
                                    <p class="small mb-0">No tienes solicitudes asignadas que coincidan con los filtros actuales.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($asesorias->hasPages())
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
        @endif
    </div>
    </div>
</div>

@foreach($asesorias as $item)
    @if($item->estado !== 'Rechazada')
    <div class="modal fade asesoria-admin-modal asesoria-admin-rating-modal" id="calificacion{{ $item->id_asesoria }}" tabindex="-1" aria-labelledby="calificacionTitulo{{ $item->id_asesoria }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="rounded-circle d-flex align-items-center justify-content-center"><i class="fas fa-star" aria-hidden="true"></i></span>
                    <div class="min-w-0">
                        <h2 class="modal-title h5 fw-bold mb-0" id="calificacionTitulo{{ $item->id_asesoria }}">Calificación de la asesoría</h2>
                        <p class="asesoria-admin-modal-subtitle">{{ $item->categoria?->nombre ?? 'Asesoría' }} · {{ $item->aprendiz?->full_name ?? 'Aprendiz' }}</p>
                    </div>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    @if($item->calificacion)
                        <div class="card border rounded-3 bg-white p-3">
                            <div class="asesoria-admin-rating-stars mb-2" role="img" aria-label="{{ $item->calificacion->estrellas }} de 5 estrellas">
                                @for($estrella = 1; $estrella <= 5; $estrella++)
                                    <i class="fas fa-star {{ $estrella <= $item->calificacion->estrellas ? '' : 'is-empty' }}" aria-hidden="true"></i>
                                @endfor
                            </div>
                            <div class="fw-bold text-dark">{{ $item->calificacion->estrellas }} de 5 estrellas</div>
                            <div class="small text-muted">Calificada por {{ $item->calificacion->aprendiz?->nickname ?? $item->aprendiz?->full_name ?? 'el aprendiz' }} · {{ $item->calificacion->created_at?->copy()->timezone('America/Bogota')->format('d/m/Y g:i a') ?? '—' }}</div>
                            <hr>
                            <h3 class="h6 fw-bold">Comentario</h3>
                            <p class="mb-0 text-muted">{{ $item->calificacion->comentario ?: 'El aprendiz no agregó un comentario.' }}</p>
                        </div>
                    @else
                        <div class="card border rounded-3 bg-white p-4 text-center">
                            <div class="asesoria-admin-rating-stars justify-content-center mb-3" aria-hidden="true">
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
    @endif
@endforeach

<div class="modal fade asesoria-admin-modal" id="modalRechazarSolicitudAdmin" tabindex="-1" aria-labelledby="modalRechazarSolicitudAdminLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius:16px; overflow:hidden;">
            <form method="POST" action="" id="formRechazarSolicitudAdmin">
                @csrf
                @method('PUT')
                <div class="modal-header text-white" style="background-color:var(--petroleo);">
                    <span class="rounded-circle d-flex align-items-center justify-content-center"><i class="fas fa-ban" aria-hidden="true"></i></span>
                    <h2 class="modal-title h5 fw-bold mb-0" id="modalRechazarSolicitudAdminLabel">Rechazar solicitud</h2>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-semibold" for="motivoRechazoAdmin">Motivo del rechazo</label>
                    <textarea class="form-control" id="motivoRechazoAdmin" name="motivo" rows="4" minlength="5" maxlength="1000" required placeholder="Explica brevemente por qué rechazas la solicitud."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-3">Confirmar rechazo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade asesoria-admin-modal" id="modalDetalle" tabindex="-1" aria-labelledby="modalDetalleLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--petroleo); border-bottom: 2px solid var(--verde);">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: rgba(57, 169, 0, 0.2);">
                        <i class="fas fa-info-circle text-white"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold mb-0 font-outfit" id="modalDetalleLabel">
                                Ficha de Asesoría #<span id="det-id">-</span>
                            </h5>
                            <span id="det-badge-estado" class="badge rounded-pill px-3 py-1">-</span>
                        </div>
                        <small class="text-white-50" style="font-size: 0.75rem;">Trazabilidad, seguimientos y recursos vinculados</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
            </div>

            <div class="modal-body p-4" style="background-color: #f8fafc;">
                <div id="det-cargando" class="text-center py-5">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="text-muted small mt-2">Consultando información...</p>
                </div>

                <div id="det-contenido" style="display: none;">

                    <div class="card border-0 shadow-sm mb-3" style="border-radius: 14px; border: 1px solid var(--borde) !important;">
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="rounded-circle bg-light p-2 text-muted">
                                            <i class="fas fa-user-graduate" style="color: var(--verde);"></i>
                                        </div>
                                        <div>
                                            <span class="text-muted small d-block">Aprendiz</span>
                                            <strong id="det-aprendiz-nombre" style="color: var(--petroleo);">-</strong>
                                            <span id="det-aprendiz-email" class="small text-muted d-block">-</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="rounded-circle bg-light p-2 text-muted">
                                            <i class="fas fa-chalkboard-user" style="color: #0284c7;"></i>
                                        </div>
                                        <div>
                                            <span class="text-muted small d-block">Instructor Asignado</span>
                                            <strong id="det-instructor-nombre" style="color: var(--petroleo);">-</strong>
                                            <span id="det-instructor-email" class="small text-muted d-block">-</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12"><hr class="my-1 text-muted opacity-25"></div>
                                <div class="col-md-4">
                                    <span class="text-muted small d-block">Categoría</span>
                                    <span id="det-categoria" class="badge rounded-pill bg-light text-dark border">-</span>
                                </div>
                                <div class="col-md-4">
                                    <span class="text-muted small d-block">Fecha y Horario</span>
                                    <strong id="det-fecha-hora" style="color: var(--petroleo);">-</strong>
                                </div>
                                <div class="col-md-4">
                                    <span class="text-muted small d-block">Fecha de Solicitud</span>
                                    <span id="det-fecha-solicitud" class="small text-muted">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card asesoria-detail-motive shadow-sm mb-3">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-1" style="color: var(--petroleo); font-size: 0.9rem;">
                                <i class="fas fa-align-left me-1" style="color: var(--verde);"></i> Motivo de la Solicitud
                            </h6>
                            <p id="det-motivo" class="asesoria-detail-motive-text text-muted small mb-0" style="white-space: pre-line;">-</p>
                        </div>
                    </div>

                    <div id="det-contenedor-cancelacion" class="card border-0 shadow-sm mb-3" style="display: none; border-radius: 14px; background-color: #fef2f2; border: 1px solid #fecaca !important;">
                        <div class="card-body p-3">
                            <h6 class="fw-bold text-danger mb-1" style="font-size: 0.9rem;">
                                <i class="fas fa-ban me-1"></i> Motivo de Cancelación
                            </h6>
                            <p id="det-motivo-cancelacion" class="text-danger small mb-1" style="white-space: pre-line;">-</p>
                            <small class="text-muted">Cancelado por: <span id="det-cancelado-por">-</span></small>
                        </div>
                    </div>

                    <div class="card asesoria-detail-section shadow-sm mb-3" id="panel-seg">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-1" style="color:var(--petroleo);font-size:.9rem;"><i class="fas fa-clipboard-check me-1" style="color:var(--verde);"></i>Seguimientos (<span id="det-count-seguimientos">0</span>)</h6>
                            <div class="asesoria-detail-motive-text">
                                <div id="det-lista-seguimientos" class="d-flex flex-column gap-2"></div>
                            </div>
                        </div>
                    </div>

                    <div class="card asesoria-detail-section shadow-sm mb-3" id="panel-hist">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-1" style="color:var(--petroleo);font-size:.9rem;"><i class="fas fa-history me-1" style="color:var(--verde);"></i>Historial (<span id="det-count-historial">0</span>)</h6>
                            <div class="asesoria-detail-motive-text">
                                <div id="det-lista-historial" class="d-flex flex-column gap-2"></div>
                            </div>
                        </div>
                    </div>

                    <div class="card asesoria-detail-section shadow-sm" id="panel-rec">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-1" style="color:var(--petroleo);font-size:.9rem;"><i class="fas fa-boxes-stacked me-1" style="color:var(--verde);"></i>Recursos (<span id="det-count-recursos">0</span>)</h6>
                            <div class="asesoria-detail-motive-text">
                                <div id="det-lista-recursos"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-white border-top-0 px-4 py-3 d-flex justify-content-between align-items-center">
                <div>
                    <button type="button" class="btn btn-sm btn-success px-3 shadow-sm" id="det-btn-finalizar" style="display: none; background-color: #16a34a; border-color: #16a34a;" onclick="finalizarDesdeDetalle()">
                        <i class="fas fa-circle-check me-1"></i> Finalizar Asesoría
                    </button>
                </div>
                <button type="button" class="btn btn-ecp-outline px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade asesoria-admin-modal" id="modalEditarAsesoria" tabindex="-1" aria-labelledby="modalEditarAsesoriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--petroleo); border-bottom: 2px solid var(--verde);">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: rgba(57, 169, 0, 0.2);">
                        <i class="fas fa-pen-to-square"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 font-outfit" id="modalEditarAsesoriaLabel">
                            Editar Asesoría #<span id="edit_asesoria_numero">-</span>
                        </h5>
                        <small class="text-white-50" style="font-size: 0.82rem;">Modifica los datos del requerimiento o reasigna instructor</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form method="POST" id="formEditarAsesoria" action="">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_asesoria_id" name="asesoria_id" value="">
                <input type="hidden" id="edit_categoria_id" value="">

                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    @if($errors->any() && session('modal_abrir') === 'modalEditarAsesoria')
                        <div class="alert alert-danger alert-dismissible fade show rounded-3 small p-3 mb-3 border-0 shadow-sm" role="alert">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Corrige los siguientes errores:</strong>
                            </div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    @endif

                    <div class="row g-3">

                        <div class="col-12 col-md-6">
                            <label for="edit_aprendiz" class="form-label fw-semibold text-dark mb-1">
                                <i class="fas fa-user-graduate me-1" style="color: var(--verde);"></i> Aprendiz <span class="text-danger">*</span>
                            </label>
                            <select name="id_usuario_aprendiz" id="edit_aprendiz" class="form-select" required>
                                <option value="">-- Selecciona el aprendiz --</option>
                                @foreach($aprendices as $ap)
                                    <option value="{{ $ap->id }}" {{ old('id_usuario_aprendiz') == $ap->id ? 'selected' : '' }}>
                                        {{ $ap->full_name }} ({{ $ap->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="edit_categoria" class="form-label fw-semibold text-dark mb-1">
                                <i class="fas fa-tag me-1" style="color: var(--verde);"></i> Categoría Temática <span class="text-danger">*</span>
                            </label>
                            <select name="id_categoria_asesoria" id="edit_categoria" class="form-select" required onchange="consultarInstructoresDisponibles('edit')">
                                <option value="">-- Selecciona la categoría --</option>
                                @foreach($categorias->where('estado', 'Activa') as $cat)
                                    <option value="{{ $cat->id_categoria_asesoria }}" {{ old('id_categoria_asesoria') == $cat->id_categoria_asesoria ? 'selected' : '' }}>
                                        {{ $cat->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="edit_fecha" class="form-label fw-semibold text-dark mb-1">
                                <i class="fas fa-calendar-day me-1" style="color: var(--verde);"></i> Fecha <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="fecha" id="edit_fecha" value="{{ old('fecha') }}" class="form-control" required onchange="consultarInstructoresDisponibles('edit')">
                        </div>

                        <div class="col-6 col-md-4">
                            <label for="edit_hora_inicio" class="form-label fw-semibold text-dark mb-1">
                                <i class="fas fa-clock me-1" style="color: var(--verde);"></i> Hora Inicio <span class="text-danger">*</span>
                            </label>
                            <input type="time" name="hora_inicio" id="edit_hora_inicio" value="{{ old('hora_inicio') }}" class="form-control" min="08:00" max="14:59" step="60" required onchange="consultarInstructoresDisponibles('edit')">
                        </div>

                        <div class="col-6 col-md-4">
                            <label for="edit_hora_fin" class="form-label fw-semibold text-dark mb-1">
                                <i class="fas fa-clock me-1" style="color: var(--verde);"></i> Hora Fin <span class="text-danger">*</span>
                            </label>
                            <input type="time" name="hora_fin" id="edit_hora_fin" value="{{ old('hora_fin') }}" class="form-control" min="08:00" max="15:00" step="60" required onchange="consultarInstructoresDisponibles('edit')">
                        </div>

                        <div class="col-12">
                            <div class="p-3 border rounded-3 bg-white shadow-sm">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label for="edit_instructor" class="form-label fw-semibold mb-0" style="color: var(--petroleo);">
                                        <i class="fas fa-chalkboard-user me-1" style="color: #0284c7;"></i> Instructor Disponible (Opcional)
                                    </label>
                                    <span id="edit_badge_disponibles" class="badge rounded-pill bg-light text-muted border small">
                                        Selecciona categoría y horario
                                    </span>
                                </div>
                                <select name="id_usuario_instructor" id="edit_instructor" class="form-select">
                                    <option value="">-- Dejar sin asignar (Solicitada) --</option>
                                </select>
                                <div id="edit_alerta_disponibilidad" class="small mt-2" style="display: none;"></div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="edit_motivo" class="form-label fw-semibold text-dark mb-1">
                                <i class="fas fa-align-left me-1" style="color: var(--verde);"></i> Motivo de la Solicitud <span class="text-danger">*</span>
                            </label>
                            <textarea name="motivo" id="edit_motivo" rows="3" class="form-control" placeholder="Describe la necesidad o temática a tratar..." required minlength="5">{{ old('motivo') }}</textarea>
                            <small class="text-muted" style="font-size: 0.72rem;">Mínimo 5 caracteres.</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-white border-top-0 px-4 py-3 gap-2">
                    <button type="button" class="btn btn-ecp-outline px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-ecp-primary px-4 shadow-sm">
                        <i class="fas fa-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade asesoria-admin-modal" id="modalProgramar" tabindex="-1" aria-labelledby="modalProgramarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--petroleo); border-bottom: 2px solid var(--verde);">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: rgba(57, 169, 0, 0.2);">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 font-outfit" id="modalProgramarLabel">Programar asesoría</h5>
                        <small class="text-white-50" style="font-size: 0.82rem;">Selecciona la nueva fecha y el horario de atención.</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form method="POST" id="formProgramar" action="">
                @csrf
                @method('PUT')
                <input type="hidden" id="prog_asesoria_id" name="asesoria_id" value="">
                <input type="hidden" id="prog_categoria_id" value="">

                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    @if($errors->any() && session('modal_abrir') === 'modalProgramar')
                        <div class="alert alert-danger alert-dismissible fade show rounded-3 small p-3 mb-3 border-0 shadow-sm" role="alert">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Error al programar:</strong>
                            </div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    @endif

                    <div class="p-3 border rounded-3 bg-white mb-3 shadow-sm">
                        <span class="small text-muted d-block">Aprendiz</span>
                        <strong id="prog_aprendiz_nombre" class="d-block mb-1" style="color: var(--petroleo);">-</strong>
                        <span class="small text-muted d-block">Categoría</span>
                        <span id="prog_categoria_nombre" class="badge rounded-pill bg-light text-dark border small">-</span>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-12">
                            <label for="prog_fecha" class="form-label fw-semibold text-dark mb-1">Nueva fecha <span class="text-danger">*</span></label>
                            <input type="date" name="fecha" id="prog_fecha" class="form-control" required onchange="consultarInstructoresDisponibles('prog')">
                        </div>
                        <div class="col-6">
                            <label for="prog_hora_inicio" class="form-label fw-semibold text-dark mb-1">Desde <span class="text-danger">*</span></label>
                            <input type="time" name="hora_inicio" id="prog_hora_inicio" class="form-control" min="08:00" max="14:59" step="60" required onchange="consultarInstructoresDisponibles('prog')">
                        </div>
                        <div class="col-6">
                            <label for="prog_hora_fin" class="form-label fw-semibold text-dark mb-1">Hasta <span class="text-danger">*</span></label>
                            <input type="time" name="hora_fin" id="prog_hora_fin" class="form-control" min="08:00" max="15:00" step="60" required onchange="consultarInstructoresDisponibles('prog')">
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="prog_instructor" class="form-label fw-semibold text-dark mb-0">
                                Instructor disponible <span class="text-danger">*</span>
                            </label>
                            <span id="prog_badge_disponibles" class="badge rounded-pill bg-light text-muted border small">Consultando...</span>
                        </div>
                        <select name="id_usuario_instructor" id="prog_instructor" class="form-select" required>
                            <option value="">-- Selecciona el instructor --</option>
                        </select>
                        <div id="prog_alerta_disponibilidad" class="small mt-2" style="display: none;"></div>
                    </div>
                </div>

                <div class="modal-footer bg-white border-top-0 px-4 py-3 gap-2">
                    <button type="button" class="btn btn-ecp-outline px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-ecp-primary px-4 shadow-sm">
                        <i class="fas fa-calendar-check me-1"></i> Confirmar y programar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade asesoria-admin-modal" id="modalCategorias" tabindex="-1" aria-labelledby="modalCategoriasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--petroleo); border-bottom: 2px solid var(--verde);">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: rgba(57, 169, 0, 0.2);">
                        <i class="fas fa-tags text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 font-outfit" id="modalCategoriasLabel">Categorías de Asesoría</h5>
                        <small class="text-white-50" style="font-size: 0.75rem;">Gestiona tus áreas de especialidad para asesorías</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-4" style="background-color: #f8fafc;">
                @if($errors->any() && session('modal_abrir') === 'modalCategorias')
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 small p-3 mb-3 border-0 shadow-sm" role="alert">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Error al guardar categoría:</strong>
                        </div>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('controlecp.administrador.asesorias.categorias.store') }}" id="formCrearCategoria" class="mb-4">
                    @csrf
                    <div class="card border-0 shadow-sm p-3 rounded-3 bg-white">
                        <h6 class="fw-bold mb-3" style="color: var(--petroleo);">
                            <i class="fas fa-plus-circle me-1" style="color: var(--verde);"></i> Nueva Categoría Temática
                        </h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="cat_nombre" class="form-label small fw-semibold text-muted mb-1">
                                    Nombre de la Categoría <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="nombre" id="cat_nombre" class="form-control form-control-sm rounded-pill" placeholder="Ej: Paz y Convivencia, Liderazgo..." required>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-ecp-primary btn-sm px-4 shadow-sm">
                                    <i class="fas fa-save me-1"></i> Guardar Categoría
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <h6 class="fw-bold mb-2 font-outfit" style="color: var(--petroleo);">
                    <i class="fas fa-list me-1" style="color: #0284c7;"></i> Categorías Registradas ({{ $categorias->count() }})
                </h6>
                <div class="table-responsive rounded-3 border bg-white shadow-sm">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead class="table-light small">
                            <tr>
                                <th class="ps-3 py-2">Categoría</th>
                                <th class="py-2 text-center">Estado</th>
                                <th class="pe-3 py-2 text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($categorias as $c)
                                <tr>
                                    <td class="ps-3 py-2 fw-semibold" style="color: var(--petroleo);">
                                        <i class="fas fa-tag me-1 text-muted"></i>{{ $c->nombre }}
                                    </td>
                                    <td class="py-2 text-center">
                                        <span class="badge {{ $c->estado === 'Activa' ? 'badge-estado-realizada' : 'badge-estado-cancelada' }} rounded-pill" style="font-size: 0.7rem;">
                                            {{ $c->estado }}
                                        </span>
                                    </td>
                                    <td class="pe-3 py-2 text-end">
                                        <div class="asesoria-category-actions">
                                            <button type="button" class="btn asesoria-row-action asesoria-row-action--edit" title="Editar categoría" aria-label="Editar categoría" onclick="editarCategoria({{ $c->id_categoria_asesoria }}, '{{ addslashes($c->nombre) }}')">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <form method="POST" action="{{ route('controlecp.administrador.asesorias.categorias.estado', $c->id_categoria_asesoria) }}" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn asesoria-row-action {{ $c->estado === 'Activa' ? 'asesoria-row-action--category-disable' : 'asesoria-row-action--category-enable' }}" title="{{ $c->estado === 'Activa' ? 'Desactivar categoría' : 'Activar categoría' }}" aria-label="{{ $c->estado === 'Activa' ? 'Desactivar categoría' : 'Activar categoría' }}">
                                                    <i class="fas {{ $c->estado === 'Activa' ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-3 text-muted">No hay categorías registradas aún.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer bg-white border-top-0 px-4 py-3">
                <button type="button" class="btn btn-ecp-outline px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade asesoria-admin-modal" id="modalDisponibilidad" tabindex="-1" aria-labelledby="modalDisponibilidadLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--petroleo); border-bottom: 2px solid var(--verde);">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: rgba(57, 169, 0, 0.2);">
                        <i class="fas fa-clock text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 font-outfit" id="modalDisponibilidadLabel">Mi disponibilidad</h5>
                        <small class="text-white-50" style="font-size: 0.75rem;">Define tus días y horarios disponibles para asesorías</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-4" style="background-color: #f8fafc;">
                @if($errors->any() && session('modal_abrir') === 'modalDisponibilidad')
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 small p-3 mb-3 border-0 shadow-sm" role="alert">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Error al guardar franja:</strong>
                        </div>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('controlecp.administrador.asesorias.mi-disponibilidad.categoria') }}" class="mb-4">
                    @csrf
                    <div class="card border-0 shadow-sm p-3 rounded-3 bg-white">
                        <label for="disp_categoria" class="form-label fw-bold mb-3" style="color:var(--petroleo)">
                            <i class="fas fa-tags me-1" style="color:var(--verde);"></i> Selecciona categoría <span class="text-danger">*</span>
                        </label>
                        <select name="id_categoria_asesoria" id="disp_categoria" class="form-select form-select-sm rounded-pill" required>
                            <option value="">Selecciona una categoría</option>
                            @foreach($categoriasDisponibilidad as $categoria)
                                <option value="{{ $categoria->id_categoria_asesoria }}" @selected(old('id_categoria_asesoria', $categoriaDisponibilidadSeleccionada) == $categoria->id_categoria_asesoria)>{{ $categoria->nombre }}</option>
                            @endforeach
                        </select>
                        <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 mt-2">
                            <button type="submit" class="btn btn-ecp-primary btn-sm px-4 shadow-sm">
                                <i class="fas fa-save me-1"></i> Guardar
                            </button>
                        </div>
                    </div>
                </form>

                <form method="POST" action="{{ route('controlecp.administrador.asesorias.mi-disponibilidad.guardar') }}" id="formGuardarDisponibilidad" class="mb-4">
                    @csrf
                    <input type="hidden" name="anio" value="{{ date('Y') }}">
                    <input type="hidden" name="trimestre" value="{{ (int) ceil(date('n') / 3) }}">
                    <div class="card border-0 shadow-sm p-3 rounded-3 bg-white">
                        <h6 class="fw-bold mb-3" style="color: var(--petroleo);">
                            <i class="fas fa-calendar-plus me-1" style="color: var(--verde);"></i> Agregar Franja Horaria
                        </h6>
                        <div class="row g-2">

                            <div class="col-12 col-md-6">
                                <label for="disp_dia" class="form-label small fw-semibold text-muted mb-1">Día de la Semana <span class="text-danger">*</span></label>
                                <select name="dia_semana" id="disp_dia" class="form-select form-select-sm rounded-pill" required>
                                    <option value="1">Lunes</option>
                                    <option value="2">Martes</option>
                                    <option value="3">Miércoles</option>
                                    <option value="4">Jueves</option>
                                    <option value="5">Viernes</option>
                                </select>
                            </div>

                            <div class="col-6 col-md-3">
                                <label for="disp_hora_inicio" class="form-label small fw-semibold text-muted mb-1">Hora Inicio <span class="text-danger">*</span></label>
                                <input type="time" name="hora_inicio" id="disp_hora_inicio" value="08:00" min="08:00" max="14:59" step="60" class="form-control form-control-sm rounded-pill" required>
                            </div>
                            <div class="col-6 col-md-3">
                                <label for="disp_hora_fin" class="form-label small fw-semibold text-muted mb-1">Hora Fin <span class="text-danger">*</span></label>
                                <input type="time" name="hora_fin" id="disp_hora_fin" value="15:00" min="08:00" max="15:00" step="60" class="form-control form-control-sm rounded-pill" required>
                            </div>
                            <div class="col-12">
                                <small class="text-muted">Las franjas deben estar entre las 8:00 a. m. y las 3:00 p. m.</small>
                            </div>

                            <div class="col-12 text-end mt-2">
                                <button type="submit" class="btn btn-ecp-primary btn-sm px-4 shadow-sm">
                                    <i class="fas fa-save me-1"></i> Guardar Franja
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <h6 class="fw-bold mb-2 font-outfit" style="color: var(--petroleo);">
                    <i class="fas fa-calendar-check me-1" style="color: #0284c7;"></i> Mis franjas registradas
                </h6>
                <div id="disp_lista_franjas" class="p-3 border rounded-3 bg-white shadow-sm">
                    <p class="text-muted small text-center my-2">Consultando tus franjas...</p>
                </div>
            </div>

            <div class="modal-footer bg-white border-top-0 px-4 py-3">
                <button type="button" class="btn btn-ecp-outline px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade asesoria-admin-modal" id="modalEditarDisponibilidad" tabindex="-1" aria-labelledby="modalEditarDisponibilidadLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--petroleo); border-bottom: 2px solid var(--verde);">
                <h5 class="modal-title fw-bold font-outfit" id="modalEditarDisponibilidadLabel">
                    <i class="fas fa-clock me-2"></i>Editar horario disponible
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form method="POST" id="formEditarDisponibilidad" action="">
                @csrf
                @method('PUT')
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    @if($errors->any() && session('modal_abrir') === 'modalEditarDisponibilidad')
                        <div class="alert alert-danger small rounded-3">
                            @foreach($errors->all() as $err)
                                <div>{{ $err }}</div>
                            @endforeach
                        </div>
                    @endif
                    <div class="row g-3">
                        <div class="col-6">
                            <label for="edit_disp_hora_inicio" class="form-label small fw-semibold text-muted">Hora Inicio</label>
                            <input type="time" name="hora_inicio" id="edit_disp_hora_inicio" min="08:00" max="14:59" step="60" class="form-control rounded-pill" value="{{ old('hora_inicio') }}" required>
                        </div>
                        <div class="col-6">
                            <label for="edit_disp_hora_fin" class="form-label small fw-semibold text-muted">Hora Fin</label>
                            <input type="time" name="hora_fin" id="edit_disp_hora_fin" min="08:00" max="15:00" step="60" class="form-control rounded-pill" value="{{ old('hora_fin') }}" required>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">El horario debe estar entre las 8:00 a. m. y las 3:00 p. m.</small>
                </div>
                <div class="modal-footer bg-white border-top-0 px-4 py-3">
                    <button type="button" class="btn btn-ecp-outline px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-ecp-primary px-4 shadow-sm">
                        <i class="fas fa-save me-1"></i>Guardar Horario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade asesoria-admin-modal" id="modalEditarCategoria" tabindex="-1" aria-labelledby="modalEditarCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--petroleo); border-bottom: 2px solid var(--verde);">
                <h5 class="modal-title fw-bold font-outfit" id="modalEditarCategoriaLabel">
                    <i class="fas fa-edit me-2"></i>Editar Categoría
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form method="POST" id="formEditarCategoria" action="">
                @csrf
                @method('PUT')
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    <label for="edit_cat_nombre" class="form-label small fw-semibold text-muted mb-1">
                        Nombre de la Categoría <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="nombre" id="edit_cat_nombre" class="form-control form-control-sm rounded-pill" required maxlength="100">
                </div>
                <div class="modal-footer bg-white border-top-0 px-4 py-3">
                    <button type="button" class="btn btn-ecp-outline px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-ecp-primary px-4 shadow-sm">
                        <i class="fas fa-save me-1"></i>Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade asesoria-admin-modal" id="modalReprogramar" tabindex="-1" aria-labelledby="modalReprogramarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--petroleo); border-bottom: 2px solid var(--verde);">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: rgba(57, 169, 0, 0.2);">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 font-outfit" id="modalReprogramarLabel">Reprogramar asesoría</h5>
                        <small class="text-white-50" style="font-size: 0.82rem;">Selecciona la nueva fecha y el horario de atención.</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form method="POST" id="formReprogramar" action="">
                @csrf
                @method('PUT')
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    @if($errors->any() && session('modal_abrir') === 'modalReprogramar')
                        <div class="alert alert-danger alert-dismissible fade show rounded-3 small p-3 mb-3 border-0 shadow-sm" role="alert">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Error al reprogramar:</strong>
                            </div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    @endif

                    <div class="row g-2 mb-3">
                        <div class="col-12">
                            <label for="reprog_fecha" class="form-label fw-semibold text-dark mb-1">Nueva fecha <span class="text-danger">*</span></label>
                            <input type="date" name="fecha" id="reprog_fecha" class="form-control" required min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-6">
                            <label for="reprog_hora_inicio" class="form-label fw-semibold text-dark mb-1">Desde <span class="text-danger">*</span></label>
                            <input type="time" name="hora_inicio" id="reprog_hora_inicio" class="form-control" min="08:00" max="14:59" step="60" required>
                        </div>
                        <div class="col-6">
                            <label for="reprog_hora_fin" class="form-label fw-semibold text-dark mb-1">Hasta <span class="text-danger">*</span></label>
                            <input type="time" name="hora_fin" id="reprog_hora_fin" class="form-control" min="08:00" max="15:00" step="60" required>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="reprog_motivo" class="form-label fw-semibold text-dark mb-1">Motivo <span class="text-danger">*</span></label>
                        <textarea name="motivo" id="reprog_motivo" rows="3" class="form-control" placeholder="Indica por qué necesitas reprogramar..." required minlength="5"></textarea>
                        <small class="text-muted" style="font-size:0.72rem;">Quedará guardado en el historial de trazabilidad.</small>
                    </div>
                </div>

                <div class="modal-footer bg-white border-top-0 px-4 py-3 gap-2">
                    <button type="button" class="btn btn-ecp-outline px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-ecp-primary px-4 shadow-sm">
                        <i class="fas fa-calendar-check me-1"></i> Guardar cambio
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade asesoria-admin-modal" id="modalCancelar" tabindex="-1" aria-labelledby="modalCancelarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--petroleo); border-bottom: 2px solid var(--verde);">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: rgba(255, 255, 255, 0.2);">
                        <i class="fas fa-ban"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 font-outfit" id="modalCancelarLabel">Cancelar asesoría</h5>
                        <small class="text-white-50" style="font-size: 0.82rem;">Se notificará al aprendiz y se liberará la franja.</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form method="POST" id="formCancelar" action="">
                @csrf
                @method('PUT')
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    @if($errors->any() && session('modal_abrir') === 'modalCancelar')
                        <div class="alert alert-danger alert-dismissible fade show rounded-3 small p-3 mb-3 border-0 shadow-sm" role="alert">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Error al cancelar:</strong>
                            </div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    @endif

                    <div class="mb-2">
                        <label for="canc_motivo" class="form-label fw-semibold text-dark mb-1">Motivo <span class="text-danger">*</span></label>
                        <textarea name="motivo" id="canc_motivo" rows="3" class="form-control" placeholder="Explica el motivo de la cancelación..." required minlength="5"></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-white border-top-0 px-4 py-3 gap-2">
                    <button type="button" class="btn btn-ecp-outline px-4" data-bs-dismiss="modal">Volver</button>
                    <button type="submit" class="btn btn-danger px-4 shadow-sm">
                        <i class="fas fa-ban me-1"></i> Confirmar cancelación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade asesoria-admin-modal" id="modalFinalizar" tabindex="-1" aria-labelledby="modalFinalizarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: #15803d; border-bottom: 2px solid #22c55e;">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: rgba(255, 255, 255, 0.2);">
                        <i class="fas fa-circle-check text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 font-outfit" id="modalFinalizarLabel">Finalizar Asesoría</h5>
                        <small class="text-white-50" style="font-size: 0.75rem;">Marcar la asesoría como completada / realizada</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form method="POST" id="formFinalizar" action="">
                @csrf
                @method('PUT')
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    @if($errors->any() && session('modal_abrir') === 'modalFinalizar')
                        <div class="alert alert-danger alert-dismissible fade show rounded-3 small p-3 mb-3 border-0 shadow-sm" role="alert">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Error al finalizar:</strong>
                            </div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    @endif

                    <div class="alert alert-success border-0 shadow-sm small p-3 mb-3 rounded-3" data-system-alert-ignore style="background-color: #dcfce7; color: #166534;">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="fas fa-circle-check fs-5"></i>
                            <span class="fw-bold">¿Confirmas que esta asesoría fue realizada y deseas finalizarla?</span>
                        </div>
                        <div class="small">
                            El estado pasará a <span class="badge badge-estado-realizada rounded-pill px-2 py-1">Realizada</span> y se notificará a los participantes correspondientes.
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm p-3 mb-3 rounded-3 bg-white">
                        <div class="row g-2 small">
                            <div class="col-6">
                                <span class="text-muted d-block" style="font-size: 0.72rem;">N° ASESORÍA:</span>
                                <span class="fw-bold text-dark" id="fin_asesoria_numero">#</span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block" style="font-size: 0.72rem;">FECHA Y HORARIO:</span>
                                <span class="fw-bold text-dark" id="fin_asesoria_horario">-</span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block" style="font-size: 0.72rem;">APRENDIZ:</span>
                                <span class="fw-semibold text-dark" id="fin_asesoria_aprendiz">-</span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block" style="font-size: 0.72rem;">INSTRUCTOR:</span>
                                <span class="fw-semibold text-dark" id="fin_asesoria_instructor">-</span>
                            </div>
                            <div class="col-12">
                                <span class="text-muted d-block" style="font-size: 0.72rem;">CATEGORÍA:</span>
                                <span class="badge bg-light text-dark border px-2 py-1" id="fin_asesoria_categoria">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="fin_observaciones" class="form-label small fw-semibold text-muted mb-1">
                            Observaciones o conclusiones de la sesión <span class="text-muted fw-normal">(opcional)</span>
                        </label>
                        <textarea name="observaciones" id="fin_observaciones" rows="3" class="form-control form-control-sm rounded-3" placeholder="Ingresa notas o conclusiones sobre el desarrollo de la asesoría..."></textarea>
                        <small class="text-muted" style="font-size: 0.72rem;">Si agregas observaciones, se guardarán en el seguimiento de la asesoría.</small>
                    </div>
                </div>

                <div class="modal-footer bg-white border-top-0 px-4 py-3 gap-2">
                    <button type="button" class="btn btn-ecp-outline px-4" data-bs-dismiss="modal">Volver</button>
                    <button type="submit" class="btn btn-success px-4 shadow-sm" style="background-color: #16a34a; border-color: #16a34a;">
                        <i class="fas fa-circle-check me-1"></i> Confirmar y Finalizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade asesoria-admin-modal" id="modalSeguimiento" tabindex="-1" aria-labelledby="modalSeguimientoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--petroleo); border-bottom: 2px solid var(--verde);">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: rgba(57, 169, 0, 0.2);">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 font-outfit" id="modalSeguimientoLabel">Registrar seguimiento</h5>
                        <small class="text-white-50" style="font-size: 0.82rem;">Registra avances y compromisos para finalizar la asesoría.</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form method="POST" id="formSeguimiento" action="">
                @csrf
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    @if($errors->any() && session('modal_abrir') === 'modalSeguimiento')
                        <div class="alert alert-danger alert-dismissible fade show rounded-3 small p-3 mb-3 border-0 shadow-sm" role="alert">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Error al registrar seguimiento:</strong>
                            </div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="seg_avances" class="form-label fw-semibold text-dark mb-1">Avances <span class="text-danger">*</span></label>
                        <textarea name="avances" id="seg_avances" rows="3" class="form-control" placeholder="Describe los avances alcanzados..." required minlength="5"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="seg_compromisos" class="form-label fw-semibold text-dark mb-1">Compromisos <span class="text-danger">*</span></label>
                        <textarea name="compromisos" id="seg_compromisos" rows="3" class="form-control" placeholder="Escribe los compromisos acordados..." required minlength="5"></textarea>
                    </div>

                    <div class="mb-2">
                        <label for="seg_observaciones" class="form-label fw-semibold text-dark mb-1">Observaciones</label>
                        <textarea name="observaciones" id="seg_observaciones" rows="2" class="form-control" placeholder="Observaciones adicionales (opcional)"></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-white border-top-0 px-4 py-3 gap-2">
                    <button type="button" class="btn btn-ecp-outline px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-ecp-primary px-4 shadow-sm">
                        <i class="fas fa-check me-1"></i> Guardar y finalizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade asesoria-admin-modal" id="modalCorregirSeguimiento" tabindex="-1" aria-labelledby="modalCorregirSeguimientoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--petroleo); border-bottom: 2px solid #eab308;">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: rgba(234, 179, 8, 0.2);">
                        <i class="fas fa-pen-to-square text-warning"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 font-outfit" id="modalCorregirSeguimientoLabel">Corregir Seguimiento</h5>
                        <small class="text-white-50" style="font-size: 0.75rem;">Crea una nueva versión enlazada conservando la auditoría original</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form method="POST" id="formCorregirSeguimiento" action="">
                @csrf
                @method('PUT')
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    @if($errors->any() && session('modal_abrir') === 'modalCorregirSeguimiento')
                        <div class="alert alert-danger alert-dismissible fade show rounded-3 small p-3 mb-3 border-0 shadow-sm" role="alert">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Error al corregir:</strong>
                            </div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    @endif

                    <div class="alert alert-info border-0 shadow-sm small p-3 mb-3 rounded-3" data-system-alert-ignore>
                        <i class="fas fa-info-circle me-1"></i>
                        Esta acción <strong>nunca borra ni sobrescribe</strong> el seguimiento anterior; genera un nuevo registro que apunta al original.
                    </div>

                    <div class="mb-3">
                        <label for="corr_motivo" class="form-label small fw-semibold text-danger mb-1">Motivo de la Corrección <span class="text-danger">*</span></label>
                        <input type="text" name="motivo_correccion" id="corr_motivo" class="form-control form-control-sm rounded-pill" placeholder="Ej: Ajuste en compromisos solicitados por el aprendiz..." required minlength="5">
                    </div>

                    <div class="mb-3">
                        <label for="corr_avances" class="form-label small fw-semibold text-muted mb-1">Avances Corregidos <span class="text-danger">*</span></label>
                        <textarea name="avances" id="corr_avances" rows="3" class="form-control form-control-sm rounded-3" required minlength="5"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="corr_compromisos" class="form-label small fw-semibold text-muted mb-1">Compromisos Corregidos <span class="text-danger">*</span></label>
                        <textarea name="compromisos" id="corr_compromisos" rows="3" class="form-control form-control-sm rounded-3" required minlength="5"></textarea>
                    </div>

                    <div class="mb-2">
                        <label for="corr_observaciones" class="form-label small fw-semibold text-muted mb-1">Observaciones</label>
                        <textarea name="observaciones" id="corr_observaciones" rows="2" class="form-control form-control-sm rounded-3"></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-white border-top-0 px-4 py-3 gap-2">
                    <button type="button" class="btn btn-ecp-outline px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning px-4 shadow-sm text-dark fw-bold">
                        <i class="fas fa-check-circle me-1"></i> Guardar Corrección
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade asesoria-admin-modal" id="modalRecursos" tabindex="-1" aria-labelledby="modalRecursosLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--petroleo); border-bottom: 2px solid var(--verde);">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: rgba(57, 169, 0, 0.2);">
                        <i class="fas fa-box"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 font-outfit" id="modalRecursosLabel">Agregar recurso</h5>
                        <small class="text-white-50" style="font-size: 0.82rem;">Asigna un recurso utilizado durante la asesoría.</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form method="POST" id="formRecursos" action="">
                @csrf
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    @if($errors->any() && session('modal_abrir') === 'modalRecursos')
                        <div class="alert alert-danger alert-dismissible fade show rounded-3 small p-3 mb-3 border-0 shadow-sm" role="alert">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Error al vincular recurso:</strong>
                            </div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="rec_inventario" class="form-label fw-semibold text-dark mb-1">Recurso disponible <span class="text-danger">*</span></label>
                        <select name="id_inventario" id="rec_inventario" class="form-select" required>
                            <option value="">Selecciona un recurso</option>
                            @foreach($inventarios as $inv)
                                <option value="{{ $inv->id }}">
                                    {{ $inv->element ? $inv->element->name : 'Elemento #'.$inv->id }} (Código: {{ $inv->inventory_code ?? 'S/C' }} | Stock: {{ $inv->stock }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-2">
                        <label for="rec_cantidad" class="form-label fw-semibold text-dark mb-1">Cantidad <span class="text-danger">*</span></label>
                        <input type="number" name="cantidad" id="rec_cantidad" value="1" min="1" class="form-control" required>
                        <small class="text-muted" style="font-size: 0.72rem;">Se descontará automáticamente del stock de inventario.</small>
                    </div>
                </div>

                <div class="modal-footer bg-white border-top-0 px-4 py-3 gap-2">
                    <button type="button" class="btn btn-ecp-outline px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-ecp-primary px-4 shadow-sm">
                        <i class="fas fa-box me-1"></i> Agregar recurso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade asesoria-admin-modal" id="modalReporte" tabindex="-1" aria-labelledby="modalReporteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background-color: var(--petroleo); border-bottom: 2px solid var(--verde);">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background-color: rgba(57, 169, 0, 0.2);">
                        <i class="fas fa-file-export text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 font-outfit" id="modalReporteLabel">Generar Reporte de Asesorías</h5>
                        <small class="text-white-50" style="font-size: 0.75rem;">Exporta a Excel (CSV) o genera vista de impresión para PDF</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form method="GET" action="{{ route('controlecp.administrador.asesorias.reporte') }}" id="formReporte">
                <input type="hidden" name="formato" id="rep_formato" value="csv">
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    <div class="card border-0 shadow-sm p-3 rounded-3 bg-white mb-3">
                        <h6 class="fw-bold mb-3" style="color: var(--petroleo);">
                            <i class="fas fa-filter me-1" style="color: var(--verde);"></i> Criterios del Reporte
                        </h6>
                        <div class="row g-2">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">Categoría Temática</label>
                                <select name="categoria_id" class="form-select form-select-sm rounded-pill">
                                    <option value="">Todas las categorías</option>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->id_categoria_asesoria }}">{{ $cat->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">Instructor</label>
                                <select name="instructor_id" class="form-select form-select-sm rounded-pill">
                                    <option value="">Todos los instructores</option>
                                    <option value="sin_asignar">-- Sin asignar --</option>
                                    @foreach($instructores as $inst)
                                        <option value="{{ $inst->id }}">{{ $inst->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold text-muted mb-1">Estado</label>
                                <select name="estado" class="form-select form-select-sm rounded-pill">
                                    <option value="">Todos los estados</option>
                                    <option value="Solicitada">Solicitada</option>
                                    <option value="Programada">Programada</option>
                                    <option value="Realizada">Realizada</option>
                                    <option value="Cancelada">Cancelada</option>
                                </select>
                            </div>

                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold text-muted mb-1">Trimestre</label>
                                <select name="trimestre" class="form-select form-select-sm rounded-pill">
                                    <option value="">Todo el año</option>
                                    <option value="1">T1 (Ene - Mar)</option>
                                    <option value="2">T2 (Abr - Jun)</option>
                                    <option value="3">T3 (Jul - Sep)</option>
                                    <option value="4">T4 (Oct - Dic)</option>
                                </select>
                            </div>

                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold text-muted mb-1">Fecha Desde</label>
                                <input type="date" name="fecha_desde" class="form-control form-control-sm rounded-pill">
                            </div>

                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold text-muted mb-1">Fecha Hasta</label>
                                <input type="date" name="fecha_hasta" class="form-control form-control-sm rounded-pill">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-white border-top-0 px-4 py-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-ecp-outline px-4" data-bs-dismiss="modal">Cerrar</button>
                    <div class="d-flex gap-2">
                        <button type="submit" onclick="document.getElementById('rep_formato').value='csv'; this.form.target='_self';" class="btn btn-ecp-primary px-3 shadow-sm">
                            <i class="fas fa-file-csv me-1"></i> Descargar CSV (Excel)
                        </button>
                        <button type="submit" onclick="document.getElementById('rep_formato').value='print'; this.form.target='_blank';" class="btn btn-outline-primary px-3">
                            <i class="fas fa-print me-1"></i> Imprimir / Guardar PDF
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')

<script>
    (() => {
        const modalDisponibilidad = document.getElementById('modalDisponibilidad');
        if (modalDisponibilidad) {
            modalDisponibilidad.addEventListener('show.bs.modal', () => cargarDisponibilidadesInstructor({{ auth()->id() }}));
        }

        const calendarSection = document.getElementById('adminVistaCalendario');
        const requestsSection = document.getElementById('adminVistaSolicitudes');
        const calendarButton = document.getElementById('adminVistaCalendarioBtn');
        const requestsButton = document.getElementById('adminVistaSolicitudesBtn');
        const monthLabel = document.getElementById('adminCalendarioMes');
        const calendarGrid = document.getElementById('adminCalendarioGrid');
        const calendarRequestsModal = document.getElementById('modalSolicitudesCalendario');
        const calendarRequestsContent = document.getElementById('modalSolicitudesCalendarioContenido');
        const calendarRequestsDate = document.getElementById('modalSolicitudesCalendarioFecha');
        const appointments = @json($calendarioAsesorias);
        const dateKey = date => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
        const todayKey = dateKey(new Date());
        let currentMonth = new Date();
        currentMonth = new Date(currentMonth.getFullYear(), currentMonth.getMonth(), 1);
        let selectedDate = todayKey;

        const dayAppointments = date => appointments.filter(appointment => appointment.fecha === date);
        const formatTime = time => {
            const [hour, minute] = time.split(':').map(Number);
            return `${hour % 12 || 12}:${String(minute).padStart(2, '0')} ${hour < 12 ? 'am' : 'pm'}`;
        };
        const openCalendarRequests = (items, date) => {
            const calendarDate = new Date(`${date}T00:00:00`);
            calendarRequestsDate.textContent = new Intl.DateTimeFormat('es-CO', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            }).format(calendarDate);
            calendarRequestsContent.replaceChildren();
            items.forEach(item => {
                const card = document.createElement('div');
                card.className = 'asesoria-admin-calendar-item';
                const row = document.createElement('div');
                row.className = 'd-flex align-items-start justify-content-between gap-3';
                const description = document.createElement('div');
                const heading = document.createElement('div');
                heading.className = 'asesoria-admin-calendar-time';
                heading.textContent = `${formatTime(item.inicio)} – ${formatTime(item.fin)}`;
                const reservedFor = document.createElement('div');
                reservedFor.className = 'mt-1';
                reservedFor.append('Reservado con ');
                const learner = document.createElement('strong');
                learner.textContent = item.aprendiz;
                reservedFor.append(learner);
                const meta = document.createElement('div');
                meta.className = 'small text-muted mt-1';
                meta.textContent = `${item.categoria} · ${item.estado}`;
                description.append(heading, reservedFor, meta);
                const detailButton = document.createElement('button');
                detailButton.type = 'button';
                detailButton.className = 'btn btn-sm btn-outline-primary rounded-pill text-nowrap';
                detailButton.textContent = 'Ver detalle';
                detailButton.addEventListener('click', () => {
                    const modal = bootstrap.Modal.getOrCreateInstance(calendarRequestsModal);
                    calendarRequestsModal.addEventListener('hidden.bs.modal', () => verDetalle(item.id, true), {once:true});
                    modal.hide();
                });
                row.append(description, detailButton);
                card.append(row);
                calendarRequestsContent.append(card);
            });
            bootstrap.Modal.getOrCreateInstance(calendarRequestsModal).show();
        };

        const renderCalendar = () => {
            const first = new Date(currentMonth.getFullYear(), currentMonth.getMonth(), 1);
            const monthName = new Intl.DateTimeFormat('es-CO', {month:'long', year:'numeric'});
            monthLabel.textContent = monthName.format(first);
            calendarGrid.replaceChildren();
            const gridStart = new Date(first);
            gridStart.setDate(first.getDate() - ((first.getDay() + 6) % 7));
            for (let index = 0; index < 42; index++) {
                const date = new Date(gridStart);
                date.setDate(gridStart.getDate() + index);
                const key = dateKey(date);
                const items = dayAppointments(key);
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'asesoria-calendar-day';
                if (date.getMonth() !== currentMonth.getMonth()) button.classList.add('is-outside');
                if (items.length) button.classList.add('has-events');
                if (items.length && items.every(item => item.estado === 'Realizada')) button.classList.add('is-done');
                if (key === selectedDate) button.classList.add('is-selected');
                if (key === todayKey) button.classList.add('is-today');
                const dateNumber = document.createElement('span');
                dateNumber.className = 'asesoria-calendar-day-number';
                dateNumber.textContent = String(date.getDate());
                const dayHeading = document.createElement('div');
                dayHeading.className = 'asesoria-calendar-day-heading';
                dayHeading.append(dateNumber);
                if (items.length) {
                    const count = document.createElement('span');
                    count.className = 'asesoria-calendar-day-count';
                    count.textContent = `${items.length} ${items.length === 1 ? 'asesoría' : 'asesorías'}`;
                    dayHeading.append(count);
                }
                button.append(dayHeading);
                button.setAttribute('aria-label', `${date.toLocaleDateString('es-CO', {day:'numeric', month:'long', year:'numeric'})}${items.length ? `, ${items.length} ${items.length === 1 ? 'asesoría' : 'asesorías'}` : ''}`);
                button.addEventListener('click', () => {
                    selectedDate = key;
                    if (date.getMonth() !== currentMonth.getMonth() || date.getFullYear() !== currentMonth.getFullYear()) {
                        currentMonth = new Date(date.getFullYear(), date.getMonth(), 1);
                    }
                    renderCalendar();
                    if (items.length) openCalendarRequests(items, key);
                });
                calendarGrid.append(button);
            }
        };

        const setView = showCalendar => {
            calendarSection.classList.toggle('d-none', !showCalendar);
            requestsSection.classList.toggle('d-none', showCalendar);
            calendarButton.classList.toggle('is-active', showCalendar);
            requestsButton.classList.toggle('is-active', !showCalendar);
            calendarButton.setAttribute('aria-pressed', String(showCalendar));
            requestsButton.setAttribute('aria-pressed', String(!showCalendar));
        };
        calendarButton.addEventListener('click', () => setView(true));
        requestsButton.addEventListener('click', () => setView(false));
        document.getElementById('adminCalendarioAnterior').addEventListener('click', () => {
            currentMonth = new Date(currentMonth.getFullYear(), currentMonth.getMonth() - 1, 1);
            selectedDate = dateKey(currentMonth);
            renderCalendar();
        });
        document.getElementById('adminCalendarioSiguiente').addEventListener('click', () => {
            currentMonth = new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1, 1);
            selectedDate = dateKey(currentMonth);
            renderCalendar();
        });
        document.getElementById('adminCalendarioHoy').addEventListener('click', () => {
            currentMonth = new Date(new Date().getFullYear(), new Date().getMonth(), 1);
            selectedDate = todayKey;
            renderCalendar();
        });
        renderCalendar();
        setView(@json(request('vista') !== 'solicitudes'));
    })();

    // Muestra toast dinámico flotante
    function mostrarToast(mensaje, tipo = 'info') {
        const toastEl = document.getElementById('toastDinamico');
        const iconEl = document.getElementById('toastDinamicoIcon');
        const msgEl = document.getElementById('toastDinamicoMensaje');

        if (!toastEl) return;

        toastEl.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info');

        if (tipo === 'success') {
            toastEl.style.backgroundColor = 'var(--verde)';
            iconEl.className = 'fas fa-check-circle fs-5';
        } else if (tipo === 'error') {
            toastEl.style.backgroundColor = '#dc3545';
            iconEl.className = 'fas fa-exclamation-triangle fs-5';
        } else if (tipo === 'warning') {
            toastEl.style.backgroundColor = '#d97706';
            iconEl.className = 'fas fa-triangle-exclamation fs-5';
        } else {
            toastEl.style.backgroundColor = 'var(--petroleo-medio)';
            iconEl.className = 'fas fa-info-circle fs-5';
        }

        msgEl.innerText = mensaje;
        toastEl.style.display = 'block';

        const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
        toast.show();
    }

    // Consulta y muestra el detalle de una asesoría en el modal
    function verDetalle(id, desdeCalendario = false) {
        const modalEl = document.getElementById('modalDetalle');
        const bsModal = new bootstrap.Modal(modalEl);

        document.getElementById('det-cargando').style.display = 'block';
        document.getElementById('det-contenido').style.display = 'none';
        document.getElementById('det-contenedor-cancelacion').style.display = 'none';

        bsModal.show();

        fetch(`{{ url('control-ecp/administrador/asesorias') }}/${id}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                mostrarToast('No se pudo cargar la información de la asesoría.', 'error');
                return;
            }

            const a = data.asesoria;
            document.getElementById('det-id').innerText = a.id_asesoria;
            document.getElementById('det-aprendiz-nombre').innerText = a.aprendiz.nombre;
            document.getElementById('det-aprendiz-email').innerText = a.aprendiz.email || '';
            document.getElementById('det-instructor-nombre').innerText = a.instructor.nombre;
            document.getElementById('det-instructor-email').innerText = a.instructor.email || '';
            document.getElementById('det-categoria').innerText = a.categoria;
            document.getElementById('det-fecha-hora').innerText = `${a.fecha_formateada || '-'} | ${a.horario_formateado}`;
            document.getElementById('det-fecha-solicitud').innerText = a.fecha_solicitud || '-';
            document.getElementById('det-motivo').innerText = a.motivo || 'Sin motivo especificado.';

            const badgeEl = document.getElementById('det-badge-estado');
            badgeEl.innerText = a.estado;
            badgeEl.className = 'badge rounded-pill px-3 py-1 ';
            if (a.estado === 'Solicitada') badgeEl.className += 'badge-estado-solicitada';
            else if (a.estado === 'Programada') badgeEl.className += 'badge-estado-programada';
            else if (a.estado === 'Realizada') badgeEl.className += 'badge-estado-realizada';
            else if (a.estado === 'Cancelada') badgeEl.className += 'badge-estado-cancelada';
            else if (a.estado === 'Rechazada') badgeEl.className += 'badge-estado-rechazada';
            else badgeEl.className += 'badge-estado-sin-asignar';

            // Cancelación
            if (a.estado === 'Cancelada' && a.motivo_cancelacion) {
                document.getElementById('det-contenedor-cancelacion').style.display = 'block';
                document.getElementById('det-motivo-cancelacion').innerText = a.motivo_cancelacion;
                document.getElementById('det-cancelado-por').innerText = a.cancelado_por || 'Administrador';
            }

            // Seguimientos
            document.getElementById('det-count-seguimientos').innerText = a.seguimientos.length;
            document.getElementById('panel-seg').style.display = a.seguimientos.length ? '' : 'none';
            const listaSeg = document.getElementById('det-lista-seguimientos');
            listaSeg.innerHTML = '';
            window._currentSeguimientos = {};
            if (a.seguimientos.length > 0) {
                a.seguimientos.forEach(s => {
                    window._currentSeguimientos[s.id_seguimiento] = s;
                    listaSeg.innerHTML += `
                        <div class="p-3 border rounded-3 bg-white mb-2 shadow-sm ${s.es_correccion ? 'border-warning' : ''}">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold small" style="color: var(--petroleo);">${s.usuario}</span>
                                <small class="text-muted">${s.fecha || ''}</small>
                            </div>
                            ${s.es_correccion ? '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle mb-2 small"><i class="fas fa-pen-to-square me-1"></i>Corrección de seguimiento previo</span>' : ''}
                            ${s.motivo_correccion ? `<p class="small text-danger mb-2"><strong>Motivo corrección:</strong> ${s.motivo_correccion}</p>` : ''}
                            <p class="small mb-1"><strong>Avances:</strong> ${s.avances}</p>
                            <p class="small mb-1"><strong>Compromisos:</strong> ${s.compromisos}</p>
                            ${s.observaciones ? `<p class="small text-muted mb-0"><strong>Observaciones:</strong> ${s.observaciones}</p>` : ''}
                            <div class="text-end mt-2 pt-2 border-top">
                                <button type="button" class="btn btn-outline-warning btn-sm text-dark py-0 px-2 rounded-pill shadow-xs" style="font-size: 0.72rem;" onclick="prepararCorreccionSeguimiento(${s.id_seguimiento})">
                                    <i class="fas fa-pen-to-square me-1"></i> Corregir seguimiento
                                </button>
                            </div>
                        </div>
                    `;
                });
            }

            // Historial
            document.getElementById('det-count-historial').innerText = a.historial.length;
            document.getElementById('panel-hist').style.display = a.historial.length ? '' : 'none';
            const listaHist = document.getElementById('det-lista-historial');
            listaHist.innerHTML = '';
            if (a.historial.length > 0) {
                a.historial.forEach(h => {
                    listaHist.innerHTML += `
                        <div class="p-3 border rounded-3 bg-white mb-2 shadow-sm">
                            <div class="text-end mb-1"><small class="text-muted">${h.fecha || ''}</small></div>
                            <p class="small mb-1"><strong>Motivo:</strong> ${h.motivo}</p>
                            ${h.fecha_anterior ? `<small class="text-muted d-block">Anterior: ${h.fecha_anterior} (${h.hora_anterior}) &rarr; Nueva: ${h.fecha_nueva} (${h.hora_nueva})</small>` : ''}
                            <small class="text-muted">Por: ${h.usuario}</small>
                        </div>
                    `;
                });
            }

            // Recursos
            document.getElementById('det-count-recursos').innerText = a.recursos.length;
            document.getElementById('panel-rec').style.display = a.recursos.length ? '' : 'none';
            const listaRec = document.getElementById('det-lista-recursos');
            listaRec.innerHTML = '';
            if (a.recursos.length > 0) {
                let htmlRec = `
                    <table class="table table-sm table-bordered small mb-0">
                        <thead class="table-light"><tr><th>Recurso de Inventario</th><th class="text-center" style="width: 100px;">Cantidad</th></tr></thead>
                        <tbody>
                `;
                a.recursos.forEach(r => {
                    htmlRec += `<tr><td>${r.nombre}</td><td class="text-center fw-bold">${r.cantidad}</td></tr>`;
                });
                htmlRec += `</tbody></table>`;
                listaRec.innerHTML = htmlRec;
            }

            // Botón finalizar desde el detalle
            const btnFin = document.getElementById('det-btn-finalizar');
            if (btnFin) {
                if (!desdeCalendario && a.estado === 'Programada') {
                    btnFin.style.display = 'inline-block';
                    window._currentDetalleAsesoria = a;
                } else {
                    btnFin.style.display = 'none';
                    window._currentDetalleAsesoria = null;
                }
            }

            document.getElementById('det-cargando').style.display = 'none';
            document.getElementById('det-contenido').style.display = 'block';
        })
        .catch(err => {
            document.getElementById('det-cargando').style.display = 'none';
            mostrarToast('Error de conexión al consultar el detalle.', 'error');
        });
    }

    // Consulta instructores disponibles vía AJAX para Solicitud o Programación
    function consultarInstructoresDisponibles(prefix, seleccionado = null) {
        const catEl = document.getElementById(`${prefix}_categoria_id`) || document.getElementById(`${prefix}_categoria`);
        const fechaEl = document.getElementById(`${prefix}_fecha`);
        const hInicioEl = document.getElementById(`${prefix}_hora_inicio`);
        const hFinEl = document.getElementById(`${prefix}_hora_fin`);
        const instSelect = document.getElementById(`${prefix}_instructor`);
        const badgeEl = document.getElementById(`${prefix}_badge_disponibles`);
        const alertaEl = document.getElementById(`${prefix}_alerta_disponibilidad`);
        const asesoriaIdEl = document.getElementById(`${prefix}_asesoria_id`);

        if (!catEl || !fechaEl || !hInicioEl || !hFinEl || !instSelect) return;

        const categoriaId = catEl.value;
        const fecha = fechaEl.value;
        const horaInicio = hInicioEl.value;
        const horaFin = hFinEl.value;
        const asesoriaId = asesoriaIdEl ? asesoriaIdEl.value : '';

        if (!categoriaId || !fecha || !horaInicio || !horaFin) {
            if (badgeEl) {
                badgeEl.className = 'badge rounded-pill bg-light text-muted border small';
                badgeEl.innerText = 'Selecciona categoría y horario';
            }
            if (alertaEl) alertaEl.style.display = 'none';
            return;
        }

        if (horaInicio >= horaFin) {
            if (badgeEl) {
                badgeEl.className = 'badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle small';
                badgeEl.innerText = 'Hora inicio debe ser menor a fin';
            }
            if (alertaEl) {
                alertaEl.className = 'alert alert-warning py-1 px-2 small mb-0 mt-2';
                alertaEl.innerText = 'La hora de inicio debe ser anterior a la hora de fin.';
                alertaEl.style.display = 'block';
            }
            return;
        }

        if (badgeEl) {
            badgeEl.className = 'badge rounded-pill bg-light text-primary border small';
            badgeEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Consultando...';
        }

        const params = new URLSearchParams({
            categoria_id: categoriaId,
            fecha: fecha,
            hora_inicio: horaInicio,
            hora_fin: horaFin,
            asesoria_id: asesoriaId
        });

        fetch(`{{ route('controlecp.administrador.asesorias.instructores-disponibles') }}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                if (badgeEl) {
                    badgeEl.className = 'badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle small';
                    badgeEl.innerText = data.mensaje || 'Error al consultar';
                }
                return;
            }

            // Reconstruir opciones del select
            instSelect.innerHTML = '';
            if (prefix === 'sol') {
                instSelect.innerHTML = '<option value="">-- Dejar sin asignar (Solicitada) --</option>';
            } else {
                instSelect.innerHTML = '<option value="">-- Selecciona el instructor --</option>';
            }

            if (data.instructores.length === 0) {
                if (badgeEl) {
                    badgeEl.className = 'badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle small';
                    badgeEl.innerText = '0 disponibles en esta franja';
                }
                if (alertaEl) {
                    alertaEl.className = 'alert alert-warning py-2 px-3 small mb-0 mt-2 rounded-3';
                    if (prefix === 'sol') {
                        alertaEl.innerHTML = '<i class="fas fa-circle-info me-1"></i> No hay instructores disponibles para esta temática y horario. Puedes guardar la solicitud como <strong>Solicitada</strong> para asignarla después, o definir una nueva disponibilidad.';
                    } else {
                        alertaEl.innerHTML = '<i class="fas fa-triangle-exclamation me-1"></i> Ningún instructor habilitado para esta temática tiene disponibilidad libre en este horario (o hay cruce con otra asesoría).';
                    }
                    alertaEl.style.display = 'block';
                }
            } else {
                if (badgeEl) {
                    badgeEl.className = 'badge rounded-pill bg-success-subtle text-success-emphasis border border-success-subtle small';
                    badgeEl.innerText = `${data.instructores.length} instructor(es) libre(s)`;
                }
                if (alertaEl) {
                    alertaEl.className = 'alert alert-success py-1 px-2 small mb-0 mt-2 rounded-3';
                    alertaEl.innerHTML = `<i class="fas fa-check-circle me-1"></i> Se encontraron ${data.instructores.length} instructor(es) disponibles sin cruces de horario.`;
                    alertaEl.style.display = 'block';
                }

                data.instructores.forEach(inst => {
                    const opt = document.createElement('option');
                    opt.value = inst.id;
                    opt.textContent = `${inst.nombre} (${inst.email})`;
                    if (seleccionado && String(seleccionado) === String(inst.id)) {
                        opt.selected = true;
                    }
                    instSelect.appendChild(opt);
                });
            }
        })
        .catch(err => {
            if (badgeEl) {
                badgeEl.className = 'badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle small';
                badgeEl.innerText = 'Error de conexión';
            }
        });
    }

    // Abre el modal para asignar y programar una asesoría
    function abrirProgramar(id, aprendiz, categoriaId, fecha, hInicio, hFin, instructorId, categoriaNombre) {
        const form = document.getElementById('formProgramar');
        form.action = `{{ url('control-ecp/administrador/asesorias') }}/${id}/programar`;

        document.getElementById('prog_asesoria_id').value = id;
        document.getElementById('prog_categoria_id').value = categoriaId;
        document.getElementById('prog_aprendiz_nombre').innerText = aprendiz || 'Aprendiz';
        document.getElementById('prog_categoria_nombre').innerText = categoriaNombre || 'Categoría';

        if (fecha) document.getElementById('prog_fecha').value = fecha;
        if (hInicio) document.getElementById('prog_hora_inicio').value = hInicio;
        if (hFin) document.getElementById('prog_hora_fin').value = hFin;

        // Ejecutar búsqueda AJAX de instructores disponibles
        consultarInstructoresDisponibles('prog', instructorId);

        const bsModal = new bootstrap.Modal(document.getElementById('modalProgramar'));
        bsModal.show();
    }

    // Consulta franjas registradas para el instructor en el modal de disponibilidad
    function cargarDisponibilidadesInstructor(usuarioId) {
        const cont = document.getElementById('disp_lista_franjas');
        if (!cont) return;
        if (!usuarioId) {
            cont.innerHTML = '<p class="text-muted small text-center my-2">No se pudo identificar al administrador.</p>';
            return;
        }
        cont.innerHTML = '<div class="text-center py-2"><span class="spinner-border spinner-border-sm text-success"></span> Consultando franjas...</div>';

        fetch(`{{ url('control-ecp/administrador/asesorias/disponibilidad') }}/${usuarioId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success || data.disponibilidades.length === 0) {
                cont.innerHTML = '<p class="text-muted small text-center my-2">Aún no tienes franjas de disponibilidad registradas.</p>';
                return;
            }
            let html = '<div class="table-responsive"><table class="table table-sm table-bordered small mb-0 align-middle"><thead class="table-light"><tr><th>Día</th><th>Horario</th><th>Categoría</th><th>Estado</th></tr></thead><tbody>';
            data.disponibilidades.forEach(d => {
                const categoria = String(d.categoria || 'Sin categoría').replace(/[&<>"']/g, caracter => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[caracter]));
                html += `<tr><td>${d.dia_nombre}</td><td>${d.hora_inicio} - ${d.hora_fin}</td><td>${categoria}</td><td><div class="d-flex align-items-center justify-content-between gap-2"><span class="badge ${d.habilitada ? 'badge-estado-realizada' : 'badge-estado-cancelada'} rounded-pill" style="font-size:0.7rem;">${d.habilitada ? 'Activa' : 'Inactiva'}</span><div class="d-flex gap-1"><button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" title="Editar franja" aria-label="Editar franja del ${d.dia_nombre}" onclick="editarDisponibilidad(${d.id}, '${d.hora_inicio_raw}', '${d.hora_fin_raw}')"><i class="fas fa-edit"></i></button><button type="button" class="btn btn-sm ${d.habilitada ? 'btn-outline-danger' : 'btn-outline-success'} py-0 px-2" title="${d.habilitada ? 'Desactivar franja' : 'Activar franja'}" aria-label="${d.habilitada ? 'Desactivar' : 'Activar'} franja del ${d.dia_nombre}" onclick="cambiarEstadoDisponibilidad(${d.id}, ${d.habilitada ? 0 : 1})"><i class="fas ${d.habilitada ? 'fa-ban' : 'fa-check'}"></i></button></div></div></td></tr>`;
            });
            html += '</tbody></table></div>';
            cont.innerHTML = html;
        })
        .catch(() => {
            cont.innerHTML = '<p class="text-danger small text-center my-2">No se pudieron consultar las franjas.</p>';
        });
    }

    function editarDisponibilidad(id, horaInicio, horaFin) {
        const form = document.getElementById('formEditarDisponibilidad');
        form.action = `{{ url('control-ecp/administrador/asesorias/mi-disponibilidad') }}/${id}`;
        document.getElementById('edit_disp_hora_inicio').value = horaInicio;
        document.getElementById('edit_disp_hora_fin').value = horaFin;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEditarDisponibilidad')).show();
    }

    function cambiarEstadoDisponibilidad(id, habilitada) {
        const accion = habilitada ? 'activar' : 'desactivar';
        if (!window.confirm(`¿Seguro que deseas ${accion} esta franja?`)) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('control-ecp/administrador/asesorias/disponibilidad') }}/${id}/estado`;
        form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="PUT"><input type="hidden" name="habilitada" value="${habilitada}">`;
        document.body.appendChild(form);
        form.submit();
    }

    // Reabrir modales automáticamente en caso de error de validación
    @if(session('modal_abrir'))
        document.addEventListener('DOMContentLoaded', function() {
            const modalId = "{{ session('modal_abrir') }}";
            const targetId = "{{ session('modal_id') ?? session('modal_programar_id') }}";
            const modalEl = document.getElementById(modalId);
            if (modalEl) {
                if (modalId === 'modalProgramar' && targetId) {
                    const formProg = document.getElementById('formProgramar');
                    if (formProg) formProg.action = `{{ url('control-ecp/administrador/asesorias') }}/${targetId}/programar`;
                } else if (modalId === 'modalReprogramar' && targetId) {
                    const formReprog = document.getElementById('formReprogramar');
                    if (formReprog) formReprog.action = `{{ url('control-ecp/administrador/asesorias') }}/${targetId}/reprogramar`;
                } else if (modalId === 'modalCancelar' && targetId) {
                    const formCanc = document.getElementById('formCancelar');
                    if (formCanc) formCanc.action = `{{ url('control-ecp/administrador/asesorias') }}/${targetId}/cancelar`;
                } else if (modalId === 'modalFinalizar' && targetId) {
                    const formFin = document.getElementById('formFinalizar');
                    if (formFin) formFin.action = `{{ url('control-ecp/administrador/asesorias') }}/${targetId}/finalizar`;
                } else if (modalId === 'modalSeguimiento' && targetId) {
                    const formSeg = document.getElementById('formSeguimiento');
                    if (formSeg) formSeg.action = `{{ url('control-ecp/administrador/asesorias') }}/${targetId}/seguimiento`;
                } else if (modalId === 'modalCorregirSeguimiento' && targetId) {
                    const formCorr = document.getElementById('formCorregirSeguimiento');
                    if (formCorr) formCorr.action = `{{ url('control-ecp/administrador/asesorias/seguimiento') }}/${targetId}`;
                } else if (modalId === 'modalEditarAsesoria' && targetId) {
                    const formEdit = document.getElementById('formEditarAsesoria');
                    if (formEdit) formEdit.action = `{{ url('control-ecp/administrador/asesorias') }}/${targetId}`;
                } else if (modalId === 'modalRecursos' && targetId) {
                    const formRec = document.getElementById('formRecursos');
                    if (formRec) formRec.action = `{{ url('control-ecp/administrador/asesorias') }}/${targetId}/recursos`;
                } else if (modalId === 'modalEditarDisponibilidad' && targetId) {
                    const formDisp = document.getElementById('formEditarDisponibilidad');
                    if (formDisp) formDisp.action = `{{ url('control-ecp/administrador/asesorias/mi-disponibilidad') }}/${targetId}`;
                    const modalDisponibilidad = document.getElementById('modalDisponibilidad');
                    modalDisponibilidad.addEventListener('shown.bs.modal', () => {
                        bootstrap.Modal.getOrCreateInstance(modalEl).show();
                    }, { once: true });
                    bootstrap.Modal.getOrCreateInstance(modalDisponibilidad).show();
                    return;
                }
                const bsModal = new bootstrap.Modal(modalEl);
                bsModal.show();
            }
        });
    @endif

    // Abre el modal para editar una asesoría existente
    function abrirEditar(id, aprendizId, categoriaId, fecha, hInicio, hFin, instructorId, motivo) {
        const form = document.getElementById('formEditarAsesoria');
        form.action = `{{ url('control-ecp/administrador/asesorias') }}/${id}`;

        document.getElementById('edit_asesoria_id').value = id;
        document.getElementById('edit_categoria_id').value = categoriaId || '';
        document.getElementById('edit_asesoria_numero').innerText = id;

        if (aprendizId) document.getElementById('edit_aprendiz').value = aprendizId;
        if (categoriaId) document.getElementById('edit_categoria').value = categoriaId;
        if (fecha) document.getElementById('edit_fecha').value = fecha;
        if (hInicio) document.getElementById('edit_hora_inicio').value = hInicio;
        if (hFin) document.getElementById('edit_hora_fin').value = hFin;
        if (motivo) document.getElementById('edit_motivo').value = motivo;

        // Consultar instructores disponibles para este horario y categoría
        consultarInstructoresDisponibles('edit', instructorId);

        const bsModal = new bootstrap.Modal(document.getElementById('modalEditarAsesoria'));
        bsModal.show();
    }

    // Prepara la corrección de un seguimiento desde la ficha de detalle
    function prepararCorreccionSeguimiento(id) {
        if (!window._currentSeguimientos || !window._currentSeguimientos[id]) return;
        const s = window._currentSeguimientos[id];
        abrirCorregirSeguimiento(s.id_seguimiento, s.avances, s.compromisos, s.observaciones);
    }

    // Modal de reprogramación
    function abrirReprogramar(id, fecha, hInicio, hFin) {
        const form = document.getElementById('formReprogramar');
        form.action = `{{ url('control-ecp/administrador/asesorias') }}/${id}/reprogramar`;
        if (fecha) document.getElementById('reprog_fecha').value = fecha;
        if (hInicio) document.getElementById('reprog_hora_inicio').value = hInicio;
        if (hFin) document.getElementById('reprog_hora_fin').value = hFin;
        document.getElementById('reprog_motivo').value = '';
        new bootstrap.Modal(document.getElementById('modalReprogramar')).show();
    }

    // Modal de cancelación
    function abrirCancelar(id) {
        const form = document.getElementById('formCancelar');
        form.action = `{{ url('control-ecp/administrador/asesorias') }}/${id}/cancelar`;
        document.getElementById('canc_motivo').value = '';
        new bootstrap.Modal(document.getElementById('modalCancelar')).show();
    }

    // Modal de finalización
    function abrirFinalizar(id, aprendiz, categoria, fecha, horario, instructor) {
        const form = document.getElementById('formFinalizar');
        form.action = `{{ url('control-ecp/administrador/asesorias') }}/${id}/finalizar`;
        document.getElementById('fin_asesoria_numero').innerText = '#' + id;
        document.getElementById('fin_asesoria_aprendiz').innerText = aprendiz || 'No especificado';
        document.getElementById('fin_asesoria_categoria').innerText = categoria || 'Sin categoría';
        document.getElementById('fin_asesoria_horario').innerText = (fecha || '-') + (horario ? ' (' + horario + ')' : '');
        document.getElementById('fin_asesoria_instructor').innerText = instructor || 'Sin asignar';
        document.getElementById('fin_observaciones').value = '';
        new bootstrap.Modal(document.getElementById('modalFinalizar')).show();
    }

    // Permite disparar la finalización directamente desde el modal de detalle
    function finalizarDesdeDetalle() {
        if (!window._currentDetalleAsesoria) return;
        const a = window._currentDetalleAsesoria;
        const mDet = bootstrap.Modal.getInstance(document.getElementById('modalDetalle'));
        if (mDet) mDet.hide();
        abrirFinalizar(
            a.id_asesoria,
            a.aprendiz ? a.aprendiz.nombre : '',
            a.categoria || '',
            a.fecha_formateada || '',
            `${a.hora_inicio || ''} - ${a.hora_fin || ''}`,
            a.instructor ? a.instructor.nombre : ''
        );
    }

    // Modal de seguimiento
    function abrirSeguimiento(id) {
        const form = document.getElementById('formSeguimiento');
        form.action = `{{ url('control-ecp/administrador/asesorias') }}/${id}/seguimiento`;
        document.getElementById('seg_avances').value = '';
        document.getElementById('seg_compromisos').value = '';
        document.getElementById('seg_observaciones').value = '';
        new bootstrap.Modal(document.getElementById('modalSeguimiento')).show();
    }

    // Modal de corregir seguimiento (conserva histórico creando nuevo registro enlazado)
    function abrirCorregirSeguimiento(idSeguimiento, avances, compromisos, observaciones) {
        const mDet = bootstrap.Modal.getInstance(document.getElementById('modalDetalle'));
        if (mDet) mDet.hide();

        const form = document.getElementById('formCorregirSeguimiento');
        form.action = `{{ url('control-ecp/administrador/asesorias/seguimiento') }}/${idSeguimiento}`;
        document.getElementById('corr_motivo').value = '';
        document.getElementById('corr_avances').value = avances || '';
        document.getElementById('corr_compromisos').value = compromisos || '';
        document.getElementById('corr_observaciones').value = observaciones || '';
        new bootstrap.Modal(document.getElementById('modalCorregirSeguimiento')).show();
    }

    // Modal de asignación de recursos desde inventario
    function abrirRecursos(id) {
        const form = document.getElementById('formRecursos');
        form.action = `{{ url('control-ecp/administrador/asesorias') }}/${id}/recursos`;
        document.getElementById('rec_inventario').value = '';
        document.getElementById('rec_cantidad').value = 1;
        new bootstrap.Modal(document.getElementById('modalRecursos')).show();
    }

    // Abre el modal para editar la categoría seleccionada.
    function editarCategoria(id, nombre) {
        const form = document.getElementById('formEditarCategoria');
        form.action = `{{ url('control-ecp/administrador/asesorias/categorias') }}/${id}`;
        document.getElementById('edit_cat_nombre').value = nombre;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEditarCategoria')).show();
    }

    document.getElementById('modalRechazarSolicitudAdmin')?.addEventListener('show.bs.modal', event => {
        const trigger = event.relatedTarget;
        const form = document.getElementById('formRechazarSolicitudAdmin');
        form.action = trigger.dataset.action;
        form.querySelector('textarea[name="motivo"]').value = '';
    });
</script>
@endpush
@endsection
