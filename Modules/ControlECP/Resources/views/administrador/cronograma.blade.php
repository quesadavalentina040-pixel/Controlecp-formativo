@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .cronograma-header-card {
        background-color: #ffffff;
        border-radius: 24px;
        padding: 1.25rem 1.5rem;
    }
    .cronograma-header-card h2 {
        color: #001A29 !important;
    }
    .cronograma-header-card .text-muted {
        color: #64748b !important;
    }

    #pills-tab .nav-link {
        color: #001A29;
    }
    #pills-tab .nav-link.active,
    #pills-tab .show > .nav-link {
        background-color: #39A900;
        color: #fff;
    }
    #pills-tab .nav-link:not(.active):hover {
        color: #2e8800;
    }

    .cronograma-filter-card {
        border-radius: 18px;
        background: #001A29;
    }
    .cronograma-filter-grid {
        display: grid;
        grid-template-columns:
            minmax(150px, 1.8fr)
            minmax(120px, 1.35fr)
            minmax(100px, .9fr)
            minmax(130px, 1.3fr)
            minmax(120px, 1fr)
            minmax(120px, 1fr)
            auto;
        gap: .6rem;
        align-items: end;
    }
    .cronograma-filter-grid .form-label {
        display: block;
        margin-bottom: .35rem;
        color: #fff;
    }
    .cronograma-filter-grid .form-control,
    .cronograma-filter-grid .form-select,
    .cronograma-filter-grid .input-group-text {
        min-width: 0;
        min-height: 43px;
        border-color: #dce5eb;
        background-color: #fff;
        color: #001A29;
        box-shadow: none;
    }
    .cronograma-filter-grid .form-control:focus,
    .cronograma-filter-grid .form-select:focus {
        border-color: #39A900;
        box-shadow: 0 0 0 .2rem rgba(57, 169, 0, .25);
    }
    .cronograma-filter-grid .input-group-text {
        border-radius: 9px 0 0 9px;
    }
    .cronograma-filter-grid .search-field {
        border-radius: 0 9px 9px 0 !important;
    }
    .cronograma-filter-grid .ficha-search-results,
    .cronograma-ficha-search-results {
        position: absolute;
        top: calc(100% + .25rem);
        left: 0;
        right: 0;
        z-index: 20;
        display: none;
        max-height: 180px;
        overflow-y: auto;
        padding: .25rem;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: .65rem;
        box-shadow: 0 .35rem 1rem rgba(0, 26, 41, .12);
    }
    .cronograma-filter-grid .ficha-search-results.is-visible,
    .cronograma-ficha-search-results.is-visible {
        display: block;
    }
    .cronograma-filter-grid .ficha-search-option,
    .cronograma-ficha-search-results .ficha-search-option {
        display: block;
        width: 100%;
        padding: .4rem .55rem;
        border: 0;
        border-radius: .4rem;
        background: transparent;
        color: #001A29;
        text-align: left;
        font-size: .9rem;
    }
    .cronograma-filter-grid .ficha-search-option:hover,
    .cronograma-filter-grid .ficha-search-option:focus,
    .cronograma-ficha-search-results .ficha-search-option:hover,
    .cronograma-ficha-search-results .ficha-search-option:focus {
        background: #f0fbf4;
        outline: none;
    }
    .cronograma-filter-grid .ficha-search-empty,
    .cronograma-ficha-search-results .ficha-search-empty {
        padding: .4rem .55rem;
        color: #6c757d;
        font-size: .8rem;
    }
    .cronograma-filter-grid .form-select,
    .cronograma-filter-grid input[type="date"] {
        border-radius: 9px;
    }
    .cronograma-filter-grid .filter-actions {
        display: flex;
        gap: .5rem;
    }
    .cronograma-filter-grid .filter-submit,
    .cronograma-filter-grid .filter-reset {
        min-height: 43px;
        height: 43px;
        white-space: nowrap;
    }
    .cronograma-filter-grid .filter-submit {
        flex: 1;
        min-width: 112px;
        padding: 0 .85rem;
        background: #39A900;
        border-color: #39A900;
        color: #fff;
        font-weight: 700;
    }
    .cronograma-filter-grid .filter-submit:hover,
    .cronograma-filter-grid .filter-submit:focus {
        background: #2e8800;
        border-color: #2e8800;
        color: #fff;
    }
    .cronograma-filter-grid .filter-reset {
        flex: 0 0 48px;
        display: grid;
        place-items: center;
        padding: 0;
        border-color: #dce5eb;
        background: #fff;
        color: #001A29;
    }
    .cronograma-filter-grid .filter-reset:hover {
        background: #f1f5f9;
        color: #001A29;
    }
    @media (max-width: 1199.98px) {
        .cronograma-filter-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
        .cronograma-filter-grid .search-filter {
            grid-column: span 2;
        }
        .cronograma-filter-grid .filter-actions {
            grid-column: span 2;
            justify-content: flex-end;
        }
        .cronograma-filter-grid .filter-submit {
            flex: 0 0 112px;
        }
    }
    @media (max-width: 767.98px) {
        .cronograma-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .cronograma-filter-grid .search-filter,
        .cronograma-filter-grid .filter-actions {
            grid-column: 1 / -1;
        }
    }

    /* ── Estilos del Semáforo ── */
    .semaforo-indicator {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 8px rgba(0,0,0,0.15);
        flex-shrink: 0;
    }
    .semaforo-indicator-large {
        width: 20px;
        height: 20px;
    }
    .semaforo-verde {
        background-color: #39A900;
        box-shadow: 0 0 10px rgba(57, 169, 0, 0.6);
    }
    .semaforo-amarillo {
        background-color: #f57f17;
        box-shadow: 0 0 10px rgba(245, 127, 23, 0.6);
    }
    .semaforo-rojo {
        background-color: #d32f2f;
        box-shadow: 0 0 10px rgba(211, 47, 47, 0.6);
    }
    .semaforo-neutro {
        background-color: #94a3b8;
        box-shadow: none;
    }

    .card-semaforo-box {
        height: 100%;
        border: 2px solid transparent;
        border-radius: 14px;
        background: #fff;
        cursor: pointer;
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .card-semaforo-box .card-body {
        padding: 1rem !important;
    }
    .card-semaforo-rojo {
        border-color: rgba(220, 38, 38, .35);
    }
    .card-semaforo-amarillo {
        border-color: rgba(245, 127, 23, .4);
    }
    .card-semaforo-verde {
        border-color: rgba(22, 163, 74, .35);
    }
    .card-semaforo-neutro {
        border-color: rgba(100, 116, 139, .35);
    }
    .card-semaforo-icon {
        display: inline-flex;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: .95rem;
    }
    .card-semaforo-rojo .card-semaforo-icon {
        background: #fee2e2;
        color: #d32f2f;
    }
    .card-semaforo-amarillo .card-semaforo-icon {
        background: #ffedd5;
        color: #ea580c;
    }
    .card-semaforo-verde .card-semaforo-icon {
        background: #dcfce7;
        color: #16a34a;
    }
    .card-semaforo-neutro .card-semaforo-icon {
        background: #e2e8f0;
        color: #64748b;
    }
    .card-semaforo-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 26, 41, .08);
    }
    .card-semaforo-label {
        color: #475569;
        font-size: .95rem;
        font-weight: 500;
    }
    .card-semaforo-value {
        margin-top: .15rem;
        font-size: 1.7rem;
        font-weight: 800;
        line-height: 1;
    }
    .card-semaforo-subtitle {
        color: #64748b;
        font-size: .9rem;
    }

    /* ── Estilos del Calendario Mensual ── */
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 2px;
        background-color: #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
    }
    .calendar-day-header {
        background-color: #001A29;
        color: #ffffff;
        font-weight: 700;
        text-align: center;
        padding: 12px 6px;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .calendar-day-cell {
        background-color: #ffffff;
        min-height: 120px;
        padding: 8px;
        position: relative;
        transition: background-color 0.15s ease;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .calendar-day-cell.has-events {
        cursor: pointer;
    }
    .calendar-day-cell.has-events:hover {
        background-color: #f0fbf4;
    }
    .calendar-day-activity-indicator {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        margin-top: .35rem;
        padding: .3rem .5rem;
        border-radius: 999px;
        background: #e8f8e8;
        color: #267800;
        font-size: .72rem;
        font-weight: 700;
    }
    .calendar-day-activity-indicator .activity-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #39A900;
    }
    .calendar-day-activity-indicator.semaforo-rojo {
        background: #fee2e2;
        color: #b91c1c;
        animation: none;
        box-shadow: none;
    }
    .calendar-day-activity-indicator.semaforo-rojo .activity-dot {
        background: #dc2626;
    }
    .calendar-day-activity-indicator.semaforo-amarillo {
        background: #ffedd5;
        color: #c2410c;
        animation: none;
        box-shadow: none;
    }
    .calendar-day-activity-indicator.semaforo-amarillo .activity-dot {
        background: #f57f17;
    }
    .calendar-day-activity-indicator.semaforo-verde {
        background: #e8f8e8;
        color: #267800;
        animation: none;
        box-shadow: none;
    }
    .calendar-day-activity-indicator.semaforo-neutro {
        background: #e2e8f0;
        color: #475569;
        animation: none;
        box-shadow: none;
    }
    .calendar-day-activity-indicator.semaforo-neutro .activity-dot {
        background: #94a3b8;
    }
    .calendar-day-empty {
        margin-top: .4rem;
        color: #94a3b8;
        font-size: .72rem;
    }
    .calendar-day-activity {
        padding: .75rem;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #39A900;
        border-radius: 12px;
        background: #fff;
    }
    .calendar-day-activity + .calendar-day-activity {
        margin-top: .6rem;
    }
    .calendar-day-activity .activity-meta {
        color: #64748b;
        font-size: .85rem;
    }
    .calendar-day-cell:hover {
        background-color: #f8fafc;
    }
    .calendar-day-cell.non-working-day {
        background-color: #ffffff;
    }
    .calendar-day-cell.other-month {
        background-color: #eaf1f5;
    }
    .calendar-day-cell.other-month .calendar-day-number {
        color: #82909b;
    }
    .calendar-day-header-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 4px;
    }
    .calendar-day-number {
        font-weight: 700;
        font-size: 0.88rem;
        color: #001A29;
        display: inline-block;
        width: 26px;
        height: 26px;
        line-height: 26px;
        text-align: center;
        border-radius: 50%;
    }
    .calendar-day-cell.today .calendar-day-number {
        background-color: #39A900;
        color: #ffffff;
        box-shadow: 0 0 10px rgba(57, 169, 0, 0.4);
    }
    .calendar-day-events {
        display: flex;
        flex-direction: column;
        gap: 4px;
        overflow-y: auto;
        max-height: 110px;
    }

    .calendar-event-pill {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 4px 6px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        gap: 2px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        text-decoration: none;
        border-left: 3px solid #cbd5e1;
        background-color: #f8fafc;
        color: #0f172a;
    }
    .calendar-event-pill:hover {
        transform: scale(1.02);
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }
    .event-pill-verde {
        background-color: #f0fdf4;
        border-left-color: #39A900;
        color: #14532d;
    }
    .event-pill-amarillo {
        background-color: #fffbeb;
        border-left-color: #f59e0b;
        color: #78350f;
    }
    .event-pill-rojo {
        background-color: #fef2f2;
        border-left-color: #ef4444;
        color: #7f1d1d;
    }
    .event-pill-neutro {
        background-color: #f1f5f9;
        border-left-color: #94a3b8;
        color: #334155;
    }

    .badge-ficha {
        font-size: 0.68rem;
        background-color: rgba(0, 26, 41, 0.08);
        color: #001A29;
        font-family: monospace;
        padding: 1px 4px;
        border-radius: 4px;
        display: inline-block;
        width: fit-content;
    }

    .timeline-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: #39A900;
        border: 2px solid #ffffff;
        box-shadow: 0 0 0 2px #39A900;
    }
    .cronograma-row-actions {
        display: flex;
        justify-content: flex-end;
        gap: .4rem;
        white-space: nowrap;
    }
    .cronograma-row-action {
        display: inline-flex;
        width: 34px;
        height: 34px;
        align-items: center;
        justify-content: center;
        padding: 0;
        border: 1px solid transparent;
        border-radius: 10px !important;
        transition: background-color .15s ease, color .15s ease, transform .15s ease, box-shadow .15s ease;
    }
    .cronograma-row-action:hover,
    .cronograma-row-action:focus-visible {
        transform: translateY(-2px);
        box-shadow: 0 3px 8px rgba(0, 26, 41, .14);
    }
    .cronograma-row-action--view {
        background: #eaf5ff;
        border-color: #c9e6fb;
        color: #0876bd;
    }
    .cronograma-row-action--view:hover {
        background: #0876bd;
        border-color: #0876bd;
        color: #fff;
    }
    .cronograma-row-action--edit {
        background: #f1f5f9;
        border-color: #dbe3eb;
        color: #475569;
    }
    .cronograma-row-action--edit:hover {
        background: #475569;
        border-color: #475569;
        color: #fff;
    }
    .cronograma-row-action--reassign {
        background: #eaf8ef;
        border-color: #ccebd6;
        color: #278344;
    }
    .cronograma-row-action--reassign:hover {
        background: #278344;
        border-color: #278344;
        color: #fff;
    }
    .cronograma-row-action--postpone {
        background: #fff7e6;
        border-color: #f8e3b2;
        color: #c47a00;
    }
    .cronograma-row-action--postpone:hover {
        background: #c47a00;
        border-color: #c47a00;
        color: #fff;
    }
    .cronograma-row-action--deactivate {
        background: #fff0f0;
        border-color: #f7cccc;
        color: #d63939;
    }
    .cronograma-row-action--deactivate:hover {
        background: #d63939;
        border-color: #d63939;
        color: #fff;
    }
    .cronograma-today-btn {
        border-color: #39A900 !important;
        color: #278000 !important;
        background: #fff !important;
        font-weight: 700;
    }
    .cronograma-today-btn:hover,
    .cronograma-today-btn:focus {
        border-color: #2e8800 !important;
        color: #fff !important;
        background: #39A900 !important;
        box-shadow: 0 0 0 .2rem rgba(57, 169, 0, .18);
    }

    .cronograma-modal {
        --cronograma-modal-accent: #39A900;
        --cronograma-modal-tint: #f3faef;
    }
    .cronograma-modal .modal-dialog {
        margin-top: 1rem;
        margin-bottom: 1rem;
    }
    .cronograma-modal .modal-content {
        overflow: hidden;
        border: 1px solid #e5ece6 !important;
        border-top: 4px solid #39A900 !important;
        border-radius: 20px !important;
        background: #fff;
        box-shadow: 0 22px 60px rgba(0, 26, 41, .18) !important;
    }
    .cronograma-modal .modal-header {
        align-items: flex-start;
        padding: 1.35rem 1.5rem 1.15rem !important;
        border-bottom: 1px solid #e8eee8 !important;
        background: linear-gradient(125deg, #f1f9ec 0%, #fff 78%);
    }
    .cronograma-modal .modal-header .btn-close {
        margin-top: .2rem;
        padding: .65rem;
        border-radius: 50%;
        background-color: #edf4e9;
        box-shadow: none;
    }
    .cronograma-modal .modal-title {
        color: #001A29 !important;
        font-weight: 800;
        letter-spacing: -.02em;
    }
    .cronograma-modal .modal-header h5 i {
        display: inline-grid;
        width: 2.45rem;
        height: 2.45rem;
        place-items: center;
        margin-right: .55rem !important;
        border-radius: 12px;
        background: #e7f5de;
        color: #39A900 !important;
        vertical-align: middle;
    }
    .cronograma-modal .modal-body {
        padding: 1.4rem 1.5rem !important;
        background: #fff;
    }
    .cronograma-modal .modal-footer {
        gap: .55rem;
        padding: 1rem 1.5rem 1.35rem !important;
        border-top: 1px solid #e8eee8 !important;
        background: #fbfdfb;
    }
    .cronograma-modal .form-label {
        margin-bottom: .45rem;
        color: #001A29;
        font-weight: 700;
    }
    .cronograma-modal .form-control,
    .cronograma-modal .form-select {
        min-height: 46px;
        border: 1px solid #dce5dc;
        border-radius: 11px !important;
        color: #23313d;
        background-color: #fff;
        box-shadow: none;
        transition: border-color .16s ease, box-shadow .16s ease, background-color .16s ease;
    }
    .cronograma-modal .form-control::placeholder {
        color: #9aa79b;
    }
    .cronograma-modal textarea.form-control {
        min-height: auto;
        border-radius: 11px !important;
    }
    .cronograma-modal .form-control:focus,
    .cronograma-modal .form-select:focus {
        border-color: #39A900;
        box-shadow: 0 0 0 .22rem rgba(57, 169, 0, .14);
    }
    .cronograma-modal .btn {
        min-height: 41px;
        border-radius: 999px;
        font-weight: 700;
        transition: transform .16s ease, box-shadow .16s ease, background-color .16s ease, border-color .16s ease;
    }
    .cronograma-modal .btn:not(:disabled):hover {
        transform: translateY(-1px);
        box-shadow: 0 5px 14px rgba(0, 26, 41, .1);
    }
    .cronograma-modal .modal-footer .btn:not(.btn-outline-secondary):not(.btn-outline-success):not(.btn-outline-warning):not(.btn-outline-danger):not(.btn-warning):not(.btn-danger):not(.btn-secondary) {
        border-color: #39A900 !important;
        background-color: #39A900 !important;
        color: #fff !important;
    }
    .cronograma-modal .btn-outline-success {
        border-color: #d3e9c8 !important;
        background: #eff8e9 !important;
        color: #347d1c !important;
    }
    .cronograma-modal .btn-outline-success:hover {
        border-color: #c4e1b5 !important;
        background: #e6f3dd !important;
        color: #286a12 !important;
    }
    .cronograma-modal .btn-outline-secondary,
    .cronograma-modal .btn-secondary {
        border-color: #e1e8e2 !important;
        background: #f1f5f2 !important;
        color: #536258 !important;
    }
    .cronograma-modal .btn-outline-secondary:hover,
    .cronograma-modal .btn-secondary:hover {
        border-color: #d5dfd6 !important;
        background: #e9efea !important;
        color: #3e4b42 !important;
    }
    .cronograma-modal .btn-outline-warning,
    .cronograma-modal .btn-warning {
        border-color: #f2dfad !important;
        background: #fff6df !important;
        color: #8a640d !important;
    }
    .cronograma-modal .btn-outline-warning:hover,
    .cronograma-modal .btn-warning:hover {
        border-color: #ead18d !important;
        background: #fff0c7 !important;
        color: #75530a !important;
    }
    .cronograma-modal .btn-outline-danger,
    .cronograma-modal .btn-danger {
        border-color: #f1d0d0 !important;
        background: #fff0f0 !important;
        color: #b83b3b !important;
    }
    .cronograma-modal .btn-outline-danger:hover,
    .cronograma-modal .btn-danger:hover {
        border-color: #e9baba !important;
        background: #ffe5e5 !important;
        color: #a52e2e !important;
    }
    .cronograma-modal .modal-body .bg-light {
        border: 1px solid #e5ece4;
        background: #f5f9f3 !important;
    }
    .cronograma-modal .modal-body .border-top {
        border-color: #e5ece4 !important;
    }
    #modalActividadesDia .calendar-day-activity {
        margin-bottom: .75rem;
        padding: 1rem;
        border: 1px solid #e0eadc;
        border-left: 4px solid #39A900;
        border-radius: 12px;
        background: #f8fcf6;
        box-shadow: 0 3px 12px rgba(0, 26, 41, .045);
    }
    #modalDetalleSesion .modal-header {
        gap: 1rem;
    }
    #modalDetalleSesion .modal-header h5 {
        margin-top: .35rem;
    }
    #modalDetalleSesion .modal-body .row > [class*="col-"] > .p-3 {
        padding: 1.1rem !important;
        border: 1px solid #e2e9e5;
        border-radius: 14px !important;
        background: #f8fbf8 !important;
    }
    #modalDetalleSesion #detTimelineHistorial {
        max-height: 270px;
        overflow-y: auto;
        padding-right: .25rem;
    }
    @media (max-width: 575.98px) {
        .cronograma-modal .modal-header {
            padding: 1.1rem 1rem .9rem !important;
        }
        .cronograma-modal .modal-body {
            padding: 1rem !important;
        }
        .cronograma-modal .modal-footer {
            display: grid;
            grid-template-columns: 1fr;
            padding: .8rem 1rem 1rem !important;
        }
        .cronograma-modal .modal-footer .btn {
            width: 100%;
        }
        #modalDetalleSesion .modal-footer {
            display: flex;
            flex-direction: column;
            align-items: stretch;
        }
        #modalDetalleSesion .modal-footer > div {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .45rem;
        }
        #modalDetalleSesion .modal-footer > button {
            width: 100%;
        }
    }
    @media (prefers-reduced-motion: reduce) {
        .cronograma-modal .btn,
        .cronograma-modal .form-control,
        .cronograma-modal .form-select {
            transition: none;
        }
        .cronograma-modal .btn:not(:disabled):hover {
            transform: none;
        }
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-3 py-3">

    <!-- ── Breadcrumb & Cabecera Principal ── -->
    <div class="cronograma-header-card d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small fw-semibold mb-1" style="color: #39A900;">
                <i class="fas fa-calendar-days me-2"></i>Control ECP · Gestión de Cronograma
            </div>
            <h2 class="h3 fw-bold mb-0" style="font-family: 'Outfit', sans-serif;">
                <i class="fas fa-calendar-days me-2" style="color: #39A900;"></i>Cronograma de Actividades
            </h2>
            <p class="text-muted small mb-0">Programación, control temporal, semaforización de cercanía y asignación de recursos</p>
        </div>

        <div class="d-flex gap-2">
            <button class="btn text-white rounded-pill px-4 shadow-sm fw-semibold" style="background-color: #39A900; border-color: #39A900;" data-bs-toggle="modal" data-bs-target="#modalProgramarActividad">
                <i class="fas fa-plus-circle me-2"></i>Programar Actividad
            </button>
        </div>
    </div>

    <!-- ── Panel de Semáforos (Criterio 6) ── -->
    <div class="row g-3 mb-4">
        <!-- Rojo: Crítico / Muy próximo (<= 2 días o vencida pendiente) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-semaforo-box card-semaforo-rojo shadow-sm" onclick="filtrarPorSemaforoCard('rojo')">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="card-semaforo-label">Crítico / Muy próximo</span>
                        <span class="card-semaforo-icon"><i class="fas fa-triangle-exclamation"></i></span>
                    </div>
                    <div class="card-semaforo-value" style="color:#dc2626;">{{ $stats['rojo'] }}</div>
                    <div class="card-semaforo-subtitle">Hoy o en ≤ 2 días</div>
                </div>
            </div>
        </div>

        <!-- Amarillo: Aproximadamente una semana (3 a 7 días) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-semaforo-box card-semaforo-amarillo shadow-sm" onclick="filtrarPorSemaforoCard('amarillo')">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="card-semaforo-label">Próximo (~1 semana)</span>
                        <span class="card-semaforo-icon"><i class="fas fa-clock"></i></span>
                    </div>
                    <div class="card-semaforo-value" style="color:#ea580c;">{{ $stats['amarillo'] }}</div>
                    <div class="card-semaforo-subtitle">Entre 3 y 7 días</div>
                </div>
            </div>
        </div>

        <!-- Verde: Dos semanas o más (>= 8 días) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-semaforo-box card-semaforo-verde shadow-sm" onclick="filtrarPorSemaforoCard('verde')">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="card-semaforo-label">En tiempo (≥ 2 semanas)</span>
                        <span class="card-semaforo-icon"><i class="fas fa-circle-check"></i></span>
                    </div>
                    <div class="card-semaforo-value" style="color:#16a34a;">{{ $stats['verde'] }}</div>
                    <div class="card-semaforo-subtitle">≥ 8 días de anticipación</div>
                </div>
            </div>
        </div>

        <!-- Pospuestas y Finalizadas (Semáforo gris - Criterio 6) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-semaforo-box card-semaforo-neutro shadow-sm" onclick="filtrarPorSemaforoCard('sin_color')">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="card-semaforo-label d-inline-flex align-items-center gap-2">
                            <span class="semaforo-indicator semaforo-neutro" aria-hidden="true"></span>
                            Gris (otras)
                        </span>
                        <span class="card-semaforo-icon"><i class="fas fa-calendar-check"></i></span>
                    </div>
                    <div class="d-flex align-items-baseline flex-wrap gap-2">
                        <span class="card-semaforo-value text-dark">{{ $stats['pospuestas'] }}</span>
                        <span class="card-semaforo-subtitle">posp.</span>
                        <span class="text-muted">/</span>
                        <span class="card-semaforo-value text-dark">{{ $stats['finalizadas'] }}</span>
                        <span class="card-semaforo-subtitle">fin.</span>
                        <span class="text-muted">/</span>
                        <span class="card-semaforo-value text-dark">{{ $stats['canceladas'] }}</span>
                        <span class="card-semaforo-subtitle">des.</span>
                    </div>
                    <div class="card-semaforo-subtitle">Pospuestas, finalizadas y desactivadas</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Barra de Pestañas y Acciones de Vista (Criterio 5) ── -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <ul class="nav nav-pills p-1 bg-light rounded-pill border" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ request('vista', 'calendario') !== 'lista' ? 'active' : '' }} rounded-pill px-4 fw-bold" id="tab-calendario" data-bs-toggle="pill" data-bs-target="#content-calendario" type="button" role="tab">
                    <i class="fas fa-calendar-alt me-2"></i>Vista Calendario
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ request('vista') === 'lista' ? 'active' : '' }} rounded-pill px-4 fw-bold" id="tab-lista" data-bs-toggle="pill" data-bs-target="#content-lista" type="button" role="tab">
                    <i class="fas fa-list-ul me-2"></i>Vista Lista & Semáforo
                </button>
            </li>
        </ul>

    </div>

    <!-- ── Panel de Filtros Avanzados (Criterio 7) ── -->
    <div class="mb-4">
        <div class="card cronograma-filter-card border-0 shadow-sm">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('controlecp.administrador.cronograma') }}" id="formFiltrosCronograma">
                    <input type="hidden" name="vista" id="filtroVista" value="{{ request('vista', 'calendario') }}">
                    <div class="cronograma-filter-grid">
                        <!-- Filtro Buscar Texto -->
                        <div class="search-filter">
                            <label for="filtroSearch" class="form-label small fw-semibold">Buscar</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 ps-3">
                                    <i class="fas fa-magnifying-glass"></i>
                                </span>
                                <input type="text" name="search" id="filtroSearch" value="{{ request('search') }}" class="form-control border-start-0 search-field" placeholder="Actividad, ficha, lugar...">
                            </div>
                        </div>

                        <!-- Filtro Ficha -->
                        <div class="position-relative">
                            <label for="filtroFicha" class="form-label small fw-semibold">Ficha</label>
                            <input id="filtroFicha" type="search" name="ficha" class="form-control"
                                   value="{{ $fichas->firstWhere('id', request('ficha_id'))?->code ?? request('ficha') }}"
                                   placeholder="Buscar..." autocomplete="off" aria-autocomplete="list"
                                   aria-controls="cronogramaFichaResults" aria-expanded="false">
                            <input id="filtroFichaId" type="hidden" name="ficha_id" value="{{ request('ficha_id') }}">
                            <div id="cronogramaFichaResults" class="ficha-search-results" role="listbox"></div>
                        </div>

                        <!-- Filtro Estado -->
                        <div>
                            <label for="filtroEstado" class="form-label small fw-semibold">Estado</label>
                            <select name="estado" id="filtroEstado" class="form-select">
                                <option value="">Todos</option>
                                <option value="Pendiente" {{ request('estado') === 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="Pospuesta" {{ request('estado') === 'Pospuesta' ? 'selected' : '' }}>Pospuesta</option>
                                <option value="Finalizada" {{ request('estado') === 'Finalizada' ? 'selected' : '' }}>Finalizada</option>
                                <option value="Cancelada" {{ request('estado') === 'Cancelada' ? 'selected' : '' }}>Desactivada</option>
                            </select>
                        </div>

                        <!-- Filtro Semáforo -->
                        <div>
                            <label for="filtroSemaforo" class="form-label small fw-semibold">Semáforo</label>
                            <select name="semaforo" id="filtroSemaforo" class="form-select">
                                <option value="todos">Todos</option>
                                <option value="rojo" {{ request('semaforo') === 'rojo' ? 'selected' : '' }}>🔴 Rojo (≤ 2 días)</option>
                                <option value="amarillo" {{ request('semaforo') === 'amarillo' ? 'selected' : '' }}>🟡 Amarillo (~1 sem)</option>
                                <option value="verde" {{ request('semaforo') === 'verde' ? 'selected' : '' }}>🟢 Verde (≥ 2 sem)</option>
                                <option value="sin_color" {{ request('semaforo') === 'sin_color' ? 'selected' : '' }}>⚪ Gris (Posp/Fin)</option>
                            </select>
                        </div>
                        <div>
                            <label for="filtroFechaDesde" class="form-label small fw-semibold">Fecha desde</label>
                            <input type="date" id="filtroFechaDesde" name="fecha_desde" value="{{ request('fecha_desde') }}" class="form-control">
                        </div>
                        <div>
                            <label for="filtroFechaHasta" class="form-label small fw-semibold">Fecha hasta</label>
                            <input type="date" id="filtroFechaHasta" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="form-control">
                        </div>
                        <div class="filter-actions">
                            <button type="submit" class="btn filter-submit rounded-3" title="Buscar actividades">
                                <i class="fas fa-search me-1"></i>Buscar
                            </button>
                            <a href="{{ route('controlecp.administrador.cronograma', ['vista' => request('vista', 'calendario')]) }}" class="btn filter-reset rounded-3" title="Restablecer filtros" aria-label="Restablecer filtros">
                                <i class="fas fa-rotate-left"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ── Contenedor de Pestañas ── -->
    <div class="tab-content" id="pills-tabContent">

        <!-- ════════════════════════════════════════════════
             PESTAÑA 1: VISTA CALENDARIO MENSUAL (Criterio 1, 5, 6)
        ════════════════════════════════════════════════ -->
        <div class="tab-pane fade {{ request('vista', 'calendario') !== 'lista' ? 'show active' : '' }}" id="content-calendario" role="tabpanel">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-header bg-white p-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <button class="btn btn-sm btn-outline-secondary rounded-circle" id="prevMonthBtn" title="Mes Anterior" style="width: 34px; height: 34px;">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <h4 class="h5 fw-bold mb-0 text-dark" id="calendarMonthTitle" style="font-family: 'Outfit', sans-serif; min-width: 170px; text-align: center;">
                            --
                        </h4>
                        <button class="btn btn-sm btn-outline-secondary rounded-circle" id="nextMonthBtn" title="Mes Siguiente" style="width: 34px; height: 34px;">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm rounded-pill px-3 cronograma-today-btn" id="todayBtn">
                            <i class="fas fa-calendar-day me-1"></i>Hoy
                        </button>
                    </div>
                </div>

                <div class="card-body p-3">
                    <div class="calendar-grid" id="calendarGrid">
                        <!-- Cabecera de Días de la Semana -->
                        <div class="calendar-day-header">Lun</div>
                        <div class="calendar-day-header">Mar</div>
                        <div class="calendar-day-header">Mié</div>
                        <div class="calendar-day-header">Jue</div>
                        <div class="calendar-day-header">Vie</div>
                        <div class="calendar-day-header">Sáb</div>
                        <div class="calendar-day-header">Dom</div>
                        <!-- Celdas renderizadas dinámicamente con JavaScript -->
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════
             PESTAÑA 2: VISTA LISTA Y TABLA DE SEMÁFOROS (Criterio 5, 6, 7)
        ════════════════════════════════════════════════ -->
        <div class="tab-pane fade {{ request('vista') === 'lista' ? 'show active' : '' }}" id="content-lista" role="tabpanel">
            <div class="card border-0 shadow-sm" style="border-radius: 18px; overflow: hidden;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tablaCronograma">
                            <thead style="background-color: #001A29; color: #ffffff;" class="small fw-bold">
                                <tr>
                                    <th class="ps-4 py-3 text-center" style="width: 90px;">Semáforo</th>
                                    <th class="py-3">Fecha y Horario</th>
                                    <th class="py-3">Ficha / Programa</th>
                                    <th class="py-3">Actividad & Momento</th>
                                    <th class="py-3">Instructor</th>
                                    <th class="py-3">Lugar</th>
                                    <th class="py-3 text-center">Estado</th>
                                    <th class="text-end pe-4 py-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @forelse($todasSesiones as $sesion)
                                    @php
                                        $sem = $sesion->semaforo;
                                        $pInst = $sesion->instructor?->person;
                                        $nomInst = $pInst ? "{$pInst->first_name} {$pInst->first_last_name}" : ($sesion->instructor?->nickname ?? 'Sin asignar');
                                        $act = $sesion->actividad;
                                        $numAct = $act ? ($act->numero ? "Actividad {$act->numero}" : "Actividad {$act->id}") : 'Actividad';
                                        $nomMom = $act && $act->momento ? $act->momento->nombre : 'Sin Momento';
                                        $estadoSemaforoGris = [
                                            'Pospuesta' => 'Pospuesta',
                                            'Finalizada' => 'Finalizada',
                                            'Cancelada' => 'Cancelada',
                                        ][$sesion->estado] ?? 'Sin semáforo';
                                    @endphp
                                    <tr>
                                        <!-- Semáforo (Criterio 6: Solo a pendientes) -->
                                        <td class="ps-4 text-center">
                                            @if($sem === 'rojo')
                                                <span class="semaforo-indicator semaforo-rojo" title="Semáforo Rojo: Hoy o en menos de 2 días (Crítico)"></span>
                                                <div class="small text-danger fw-bold" style="font-size: 0.68rem;">
                                                    {{ $sesion->dias_restantes < 0 ? abs($sesion->dias_restantes).'d atrasada' : ($sesion->dias_restantes == 0 ? '¡Hoy!' : $sesion->dias_restantes.'d') }}
                                                </div>
                                            @elseif($sem === 'amarillo')
                                                <span class="semaforo-indicator semaforo-amarillo" title="Semáforo Amarillo: Aproximadamente 1 semana (3 a 7 días)"></span>
                                                <div class="small text-warning-emphasis fw-bold" style="font-size: 0.68rem;">{{ $sesion->dias_restantes }}d</div>
                                            @elseif($sem === 'verde')
                                                <span class="semaforo-indicator semaforo-verde" title="Semáforo Verde: En tiempo (2 semanas o más)"></span>
                                                <div class="small text-success fw-bold" style="font-size: 0.68rem;">{{ $sesion->dias_restantes }}d</div>
                                            @else
                                                <div class="d-flex flex-column align-items-center gap-1">
                                                    <span class="semaforo-indicator semaforo-neutro semaforo-indicator-large"
                                                          title="{{ $estadoSemaforoGris }}"
                                                          aria-label="{{ $estadoSemaforoGris }}"></span>
                                                    <span class="small text-muted fw-semibold" style="font-size: 0.68rem;">
                                                        {{ $estadoSemaforoGris }}
                                                    </span>
                                                </div>
                                            @endif
                                        </td>

                                        <!-- Fecha y Horario -->
                                        <td>
                                            <div class="fw-bold text-dark">
                                                <i class="far fa-calendar text-muted me-1"></i>
                                                {{ $sesion->fecha ? \Carbon\Carbon::parse($sesion->fecha)->translatedFormat('d M Y') : '--' }}
                                            </div>
                                            <div class="small text-muted">
                                                <i class="far fa-clock text-muted me-1"></i>
                                                {{ $sesion->horario_formateado }}
                                            </div>
                                            @if($sesion->fecha_original)
                                                <div class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size: 0.65rem;" title="Fecha antes de posponer">
                                                    Orig: {{ \Carbon\Carbon::parse($sesion->fecha_original)->format('d/m/Y') }}
                                                </div>
                                            @endif
                                        </td>

                                        <!-- Ficha / Programa -->
                                        <td>
                                            <span class="badge bg-dark-subtle text-dark border font-monospace">
                                                Ficha {{ $sesion->ficha?->code ?? '--' }}
                                            </span>
                                            <div class="small text-muted" style="max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $sesion->ficha?->program?->name }}">
                                                {{ $sesion->ficha?->program?->name ?? 'Programa no registrado' }}
                                            </div>
                                        </td>

                                        <!-- Momento & Actividad (Criterio 1: Actividad X – Momento Y) -->
                                        <td>
                                            <div class="fw-bold text-dark">
                                                <span class="text-primary">{{ $numAct }}</span> – {{ $nomMom }}
                                            </div>
                                            <div class="small text-muted" style="max-width: 240px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $act?->nombre }}">
                                                {{ $act?->nombre ?? 'Actividad sin nombre' }}
                                            </div>
                                            @if($sesion->recursos->count() > 0)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.68rem;">
                                                    <i class="fas fa-boxes-stacked me-1"></i>{{ $sesion->recursos->count() }} recurso(s)
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Instructor -->
                                        <td>
                                            <div>
                                                <div class="small fw-bold text-dark">{{ $nomInst }}</div>
                                                <div class="text-muted" style="font-size: 0.70rem;">Instructor Responsable</div>
                                            </div>
                                        </td>

                                        <!-- Lugar -->
                                        <td>
                                            <span class="small text-muted">
                                                <i class="fas fa-location-dot text-danger me-1"></i>{{ $sesion->lugar }}
                                            </span>
                                        </td>

                                        <!-- Estado -->
                                        <td class="text-center">
                                            @if($sesion->estado === 'Pendiente')
                                                <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                                    Pendiente
                                                </span>
                                            @elseif($sesion->estado === 'Pospuesta')
                                                <span class="badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle px-2 py-1" title="{{ $sesion->motivo_posposicion }}">
                                                    <i class="fas fa-clock-rotate-left me-1"></i>Pospuesta
                                                </span>
                                            @elseif($sesion->estado === 'Finalizada')
                                                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                    <i class="fas fa-check-circle me-1"></i>Finalizada
                                                </span>
                                            @elseif($sesion->estado === 'Cancelada')
                                                <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                                                    <i class="fas fa-ban me-1"></i>Desactivada
                                                </span>
                                            @else
                                                <span class="badge rounded-pill bg-secondary px-2 py-1">{{ $sesion->estado }}</span>
                                            @endif
                                        </td>

                                        <!-- Acciones (Criterios 8, 9, 10, 11) -->
                                        <td class="text-end pe-4">
                                            <div class="cronograma-row-actions">
                                                <button type="button" class="btn cronograma-row-action cronograma-row-action--view" title="Ver Detalle y Auditoría" aria-label="Ver detalle y auditoría" onclick="verDetalleSesion({{ $sesion->id_cronograma }})">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if($sesion->estado !== 'Cancelada')
                                                    <button type="button" class="btn cronograma-row-action cronograma-row-action--edit" title="Editar Programación" aria-label="Editar programación" onclick="abrirModalEditar({{ $sesion->id_cronograma }})">
                                                        <i class="fas fa-pen"></i>
                                                    </button>
                                                    <button type="button" class="btn cronograma-row-action cronograma-row-action--reassign" title="Reasignar Instructor" aria-label="Reasignar instructor" onclick="abrirModalReasignar({{ $sesion->id_cronograma }})">
                                                        <i class="fas fa-user-pen"></i>
                                                    </button>
                                                    <button type="button" class="btn cronograma-row-action cronograma-row-action--postpone" title="Posponer Actividad" aria-label="Posponer actividad" onclick="abrirModalPosponer({{ $sesion->id_cronograma }})">
                                                        <i class="fas fa-calendar-plus"></i>
                                                    </button>
                                                    @if($sesion->estado !== 'Finalizada')
                                                        <button type="button" class="btn cronograma-row-action cronograma-row-action--deactivate" title="Desactivar Programación" aria-label="Desactivar programación" onclick="confirmarDesactivarSesion({{ $sesion->id_cronograma }})">
                                                            <i class="fas fa-ban"></i>
                                                        </button>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="fas fa-calendar-xmark fa-3x mb-3 text-secondary opacity-50"></i>
                                            <h5 class="fw-bold">No se encontraron actividades programadas</h5>
                                            <p class="small mb-3">No hay sesiones que coincidan con los filtros aplicados.</p>
                                            <button class="btn btn-sm btn-outline-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalProgramarActividad">
                                                <i class="fas fa-plus me-1"></i>Programar Actividad Ahora
                                            </button>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- ════════════════════════════════════════════════
     MODAL 1: PROGRAMAR ACTIVIDAD (Criterios 1, 2, 3, 4, 12)
════════════════════════════════════════════════ -->
<div class="modal fade cronograma-modal" id="modalProgramarActividad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div>
                    <h5 class="modal-title fw-bold" style="color: #001A29;">
                        <i class="fas fa-calendar-plus me-2" style="color: #39A900;"></i>Programar Actividad en Cronograma
                    </h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formProgramarActividad">
                    @csrf
                    <div class="row g-3">
                        <!-- Actividad Pedagógica (Criterio 1 & 2) -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                Actividad Pedagógica <span class="text-danger">*</span>
                            </label>
                            <select name="id_actividad" id="progActividad" class="form-select rounded-pill" required>
                                <option value="">— Seleccionar Actividad —</option>
                                @foreach($momentos as $mom)
                                    <optgroup label="{{ $mom->nombre }}">
                                        @foreach($mom->actividades as $actItem)
                                            <option value="{{ $actItem->id }}" data-duracion="{{ $actItem->duracion_min ?? 120 }}">
                                                {{ $actItem->numero ? "Actividad {$actItem->numero}: " : '' }}{{ $actItem->nombre }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                        <!-- Ficha de Formación (Criterio 1 & 2) -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                Ficha de Formación SENA <span class="text-danger">*</span>
                            </label>
                            <div class="position-relative">
                                <input id="progFicha" type="search" class="form-control rounded-pill" placeholder="Buscar..." autocomplete="off"
                                       aria-autocomplete="list" aria-controls="progFichaResults" aria-expanded="false" required>
                                <input type="hidden" name="id_ficha" id="progFichaId">
                                <div id="progFichaResults" class="cronograma-ficha-search-results" role="listbox"></div>
                            </div>
                        </div>

                        <!-- Fecha Programada -->
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-dark">
                                Fecha de Ejecución <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="fecha" id="progFecha" class="form-control rounded-pill" min="{{ date('Y-m-d') }}" required onchange="verificarDisponibilidadEnVivo()">
                        </div>

                        <!-- Hora Inicio -->
                        <div class="col-6 col-md-4">
                            <label class="form-label small fw-bold text-dark">
                                Hora Inicio <span class="text-danger">*</span>
                            </label>
                            <input type="time" name="hora_inicio" id="progHoraInicio" class="form-control rounded-pill" value="08:00" min="08:00" max="14:59" step="60" required onchange="verificarDisponibilidadEnVivo()">
                        </div>

                        <!-- Hora Fin -->
                        <div class="col-6 col-md-4">
                            <label class="form-label small fw-bold text-dark">
                                Hora Fin <span class="text-danger">*</span>
                            </label>
                            <input type="time" name="hora_fin" id="progHoraFin" class="form-control rounded-pill" value="15:00" min="08:00" max="15:00" step="60" required onchange="verificarDisponibilidadEnVivo()">
                        </div>

                        <!-- Instructor Responsable (Criterio 3) -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                Instructor Responsable <span class="text-danger">*</span>
                            </label>
                            <select name="id_instructor" id="progInstructor" class="form-select rounded-pill" required onchange="verificarDisponibilidadEnVivo()">
                                <option value="">— Seleccionar Instructor —</option>
                                @foreach($instructores as $inst)
                                    @php $p = $inst->person; @endphp
                                    <option value="{{ $inst->id }}">
                                        {{ $p ? "{$p->first_name} {$p->first_last_name}" : $inst->nickname }}
                                    </option>
                                @endforeach
                            </select>
                            <!-- Feedback de disponibilidad del instructor -->
                            <div id="progInstructorFeedback" class="small mt-1 d-none"></div>
                        </div>

                        <!-- Lugar o Ambiente -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                Lugar / Ambiente <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="lugar" id="progLugar" class="form-control rounded-pill" placeholder="Ej: Aula 102, Auditorio Central, Cancha ECP" required>
                        </div>

                        <!-- ── Sección Asociar Recursos (Criterio 4) ── -->
                        <div class="col-12 mt-3 pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <label class="form-label small fw-bold text-dark mb-0">
                                        <i class="fas fa-boxes-stacked text-success me-1"></i>Asociar Recursos de Inventario (Opcional)
                                    </label>
                                    <div class="text-muted" style="font-size: 0.72rem;">Verifica disponibilidad de inventario para la sesión</div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="agregarFilaRecurso()">
                                    <i class="fas fa-plus me-1"></i>Agregar Recurso
                                </button>
                            </div>

                            <div id="contenedorRecursos" class="d-flex flex-column gap-2">
                                <!-- Filas de recursos añadidas dinámicamente -->
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn text-white rounded-pill px-4 fw-semibold shadow-sm" style="background-color: #39A900;" id="btnGuardarProgramacion" onclick="ejecutarGuardarProgramacion()">
                    <i class="fas fa-floppy-disk me-1"></i>Guardar Programación
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════
     MODAL 2: EDITAR PROGRAMACIÓN (Criterio 8)
════════════════════════════════════════════════ -->
<div class="modal fade cronograma-modal" id="modalEditarProgramacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div>
                    <h5 class="modal-title fw-bold" style="color: #001A29;">
                        <i class="fas fa-pen-to-square me-2" style="color: #001A29;"></i>Editar Programación
                    </h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formEditarProgramacion">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editCronogramaId" name="id_cronograma">

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">Actividad Pedagógica</label>
                            <select name="id_actividad" id="editActividad" class="form-select rounded-pill" required>
                                @foreach($momentos as $mom)
                                    <optgroup label="{{ $mom->nombre }}">
                                        @foreach($mom->actividades as $actItem)
                                            <option value="{{ $actItem->id }}">
                                                {{ $actItem->numero ? "Actividad {$actItem->numero}: " : '' }}{{ $actItem->nombre }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">Ficha de Formación</label>
                            <select name="id_ficha" id="editFicha" class="form-select rounded-pill" required>
                                @foreach($fichas as $f)
                                    <option value="{{ $f->id }}">
                                        Ficha {{ $f->code }} — {{ $f->program?->name ?? 'Sin programa' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-dark">Fecha de Ejecución</label>
                            <input type="date" name="fecha" id="editFecha" class="form-control rounded-pill" required>
                        </div>

                        <div class="col-6 col-md-4">
                            <label class="form-label small fw-bold text-dark">Hora Inicio</label>
                            <input type="time" name="hora_inicio" id="editHoraInicio" class="form-control rounded-pill" min="08:00" max="14:59" step="60" required>
                        </div>

                        <div class="col-6 col-md-4">
                            <label class="form-label small fw-bold text-dark">Hora Fin</label>
                            <input type="time" name="hora_fin" id="editHoraFin" class="form-control rounded-pill" value="15:00" min="08:00" max="15:00" step="60" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Lugar / Ambiente</label>
                            <input type="text" name="lugar" id="editLugar" class="form-control rounded-pill" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Motivo del Ajuste (Opcional)</label>
                            <input type="text" name="motivo_edicion" id="editMotivo" class="form-control rounded-pill" placeholder="Explica brevemente el motivo del cambio...">
                        </div>

                        <!-- Recursos en Edición -->
                        <div class="col-12 mt-3 pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label small fw-bold text-dark mb-0">
                                    <i class="fas fa-boxes-stacked text-success me-1"></i>Recursos de Inventario
                                </label>
                                <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="agregarFilaRecurso('contenedorRecursosEdit')">
                                    <i class="fas fa-plus me-1"></i>Agregar Recurso
                                </button>
                            </div>
                            <div id="contenedorRecursosEdit" class="d-flex flex-column gap-2"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn text-white rounded-pill px-4 fw-semibold" style="background-color: #001A29;" onclick="ejecutarActualizarProgramacion()">
                    <i class="fas fa-check me-1"></i>Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════
     MODAL 3: REASIGNAR INSTRUCTOR (Criterio 10)
════════════════════════════════════════════════ -->
<div class="modal fade cronograma-modal" id="modalReasignarInstructor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div>
                    <h5 class="modal-title fw-bold" style="color: #001A29;">
                        <i class="fas fa-user-pen me-2" style="color: #39A900;"></i>Reasignar Instructor
                    </h5>
                    <p class="text-muted small mb-0">Valida disponibilidad del nuevo instructor y notifica a ambos (Criterio 10)</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formReasignarInstructor">
                    <input type="hidden" id="reasignarCronogramaId">

                    <!-- Tarjeta con resumen de la sesión actual -->
                    <div class="p-3 mb-3 bg-light rounded-4 border">
                        <div class="small text-muted fw-semibold">Actividad Seleccionada:</div>
                        <div class="fw-bold text-dark" id="reasignarActividadNombre">—</div>
                        <div class="small text-muted mt-1" id="reasignarHorarioTexto">—</div>
                        <div class="small text-danger fw-semibold mt-1">
                            Instructor actual: <span id="reasignarInstructorActual">—</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">
                            Nuevo Instructor Responsable <span class="text-danger">*</span>
                        </label>
                        <select name="nuevo_instructor_id" id="nuevoInstructorId" class="form-select rounded-pill" required>
                            <option value="">— Seleccionar Nuevo Instructor —</option>
                            @foreach($instructores as $inst)
                                @php $p = $inst->person; @endphp
                                <option value="{{ $inst->id }}">
                                    {{ $p ? "{$p->first_name} {$p->first_last_name}" : $inst->nickname }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">
                            Motivo de la Reasignación <span class="text-danger">*</span>
                        </label>
                        <textarea name="motivo_reasignacion" id="motivoReasignacion" class="form-control" rows="3" placeholder="Explica la causa del cambio de instructor (cruce pedagógico, permiso, etc.)..." required style="border-radius: 14px;"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn text-white rounded-pill px-4 fw-semibold" style="background-color: #39A900;" onclick="ejecutarReasignarInstructor()">
                    <i class="fas fa-arrows-rotate me-1"></i>Reasignar y Notificar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════
     MODAL 4: POSPONER ACTIVIDAD (Criterio 11)
════════════════════════════════════════════════ -->
<div class="modal fade cronograma-modal" id="modalPosponerActividad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div>
                    <h5 class="modal-title fw-bold text-warning-emphasis">
                        <i class="fas fa-calendar-plus me-2 text-warning"></i>Posponer Actividad
                    </h5>
                    <p class="text-muted small mb-0">Conserva la fecha original, registra el motivo y la reubica (Criterio 11)</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formPosponerActividad">
                    <input type="hidden" id="posponerCronogramaId">

                    <!-- Resumen fecha actual -->
                    <div class="p-3 mb-3 bg-light rounded-4 border">
                        <div class="small text-muted fw-semibold">Sesión a Posponer:</div>
                        <div class="fw-bold text-dark" id="posponerActividadNombre">—</div>
                        <div class="small text-muted mt-1">
                            Fecha Programada Actual: <span class="fw-bold text-danger" id="posponerFechaActual">—</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">
                            Nueva Fecha de Ejecución <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="nueva_fecha" id="posponerNuevaFecha" class="form-control rounded-pill" min="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">Nueva Hora Inicio</label>
                            <input type="time" name="hora_inicio" id="posponerHoraInicio" class="form-control rounded-pill" min="08:00" max="14:59" step="60">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">Nueva Hora Fin</label>
                            <input type="time" name="hora_fin" id="posponerHoraFin" class="form-control rounded-pill" min="08:00" max="15:00" step="60">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">
                            Motivo de Posposición <span class="text-danger">*</span>
                        </label>
                        <textarea name="motivo" id="posponerMotivo" class="form-control" rows="3" placeholder="Explica obligatoriamente el motivo para posponer la sesión..." required style="border-radius: 14px;"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning text-dark fw-bold rounded-pill px-4" onclick="ejecutarPosponerActividad()">
                    <i class="fas fa-clock-rotate-left me-1"></i>Confirmar Posposición
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Actividades del día -->
<div class="modal fade cronograma-modal" id="modalActividadesDia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div>
                    <h5 class="modal-title fw-bold text-dark" id="actividadesDiaTitulo">Actividades del día</h5>
                    <p class="text-muted small mb-0" id="actividadesDiaResumen"></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4" id="actividadesDiaLista"></div>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════
     MODAL 5: VER DETALLE Y AUDITORÍA (Criterios 1, 5, 6, 8, 10, 11)
════════════════════════════════════════════════ -->
<div class="modal fade cronograma-modal" id="modalDetalleSesion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span id="detSemaforoDot" class="semaforo-indicator semaforo-verde"></span>
                        <span id="detEstadoBadge" class="badge rounded-pill bg-primary px-3">Pendiente</span>
                        <span id="detFichaBadge" class="badge bg-dark-subtle text-dark border font-monospace">Ficha 000000</span>
                    </div>
                    <h5 class="modal-title fw-bold text-dark mb-0" id="detTituloCalendario">
                        Actividad X – Momento Y
                    </h5>
                    <p class="text-muted small mb-0" id="detActividadNombre">—</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <!-- Datos Principales -->
                    <div class="col-12 col-md-6">
                        <div class="p-3 bg-light rounded-4 h-100">
                            <h6 class="fw-bold text-dark mb-2 small text-uppercase" style="letter-spacing: 0.5px;">
                                <i class="fas fa-circle-info text-success me-1"></i>Detalles de Sesión
                            </h6>
                            <div class="mb-2 small">
                                <span class="text-muted d-block">Fecha y Horario:</span>
                                <span class="fw-bold text-dark" id="detFechaHorario">—</span>
                            </div>
                            <div class="mb-2 small">
                                <span class="text-muted d-block">Lugar / Ambiente:</span>
                                <span class="fw-bold text-dark" id="detLugar">—</span>
                            </div>
                            <div class="mb-2 small">
                                <span class="text-muted d-block">Instructor a Cargo:</span>
                                <span class="fw-bold text-success" id="detInstructor">—</span>
                            </div>
                            <div class="small">
                                <span class="text-muted d-block">Programado por:</span>
                                <span class="fw-bold text-muted" id="detCreador">—</span>
                            </div>
                        </div>
                    </div>

                    <!-- Recursos Asociados (Criterio 4) -->
                    <div class="col-12 col-md-6">
                        <div class="p-3 bg-light rounded-4 h-100">
                            <h6 class="fw-bold text-dark mb-2 small text-uppercase" style="letter-spacing: 0.5px;">
                                <i class="fas fa-boxes-stacked text-primary me-1"></i>Recursos Asociados
                            </h6>
                            <div id="detListaRecursos" class="d-flex flex-column gap-2 small">
                                <span class="text-muted">Sin recursos asignados a esta sesión.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Trazabilidad / Historial de Cambios (Criterios 8, 10, 11) -->
                    <div class="col-12">
                        <div class="p-3 bg-white rounded-4 border">
                            <h6 class="fw-bold text-dark mb-3 small text-uppercase" style="letter-spacing: 0.5px;">
                                <i class="fas fa-timeline text-secondary me-1"></i>Historial de Auditoría & Trazabilidad
                            </h6>
                            <div id="detTimelineHistorial" class="d-flex flex-column gap-3 small">
                                <!-- Elementos del historial -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4 d-flex justify-content-between">
                <div>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 me-1" id="btnDetDesactivar">
                        <i class="fas fa-ban me-1"></i>Desactivar
                    </button>
                    <button type="button" class="btn btn-outline-warning btn-sm rounded-pill px-3 me-1" id="btnDetPosponer">
                        <i class="fas fa-clock-rotate-left me-1"></i>Posponer
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3" id="btnDetReasignar">
                        <i class="fas fa-user-pen me-1"></i>Reasignar
                    </button>
                </div>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // ── Variables Globales y Catálogo de Inventario ──
    const CATALOGO_INVENTARIO = @json($recursosInventario);
    let mesActual = new Date().getMonth() + 1; // 1-12
    let anioActual = new Date().getFullYear();
    let eventosCalendario = [];
    let diasNoLaboralesCalendario = [];
    let fechaUltimaActualizacionSemaforo = '';
    let temporizadorActualizacionSemaforo;

    function obtenerFechaLocalActual() {
        const hoy = new Date();
        return `${hoy.getFullYear()}-${String(hoy.getMonth() + 1).padStart(2, '0')}-${String(hoy.getDate()).padStart(2, '0')}`;
    }

    function programarActualizacionSemaforoDiaria() {
        clearTimeout(temporizadorActualizacionSemaforo);
        const ahora = new Date();
        const proximaMedianoche = new Date(ahora.getFullYear(), ahora.getMonth(), ahora.getDate() + 1);
        temporizadorActualizacionSemaforo = setTimeout(() => {
            fechaUltimaActualizacionSemaforo = obtenerFechaLocalActual();
            cargarEventosCalendario(anioActual, mesActual);
            programarActualizacionSemaforoDiaria();
        }, proximaMedianoche.getTime() - ahora.getTime() + 1000);
    }

    document.addEventListener('DOMContentLoaded', function () {
        const fichas = @json($fichas->map(fn ($ficha) => ['id' => $ficha->id, 'code' => $ficha->code])->values());

        const configurarBusquedaFicha = (inputId, hiddenId, resultsId) => {
            const fichaInput = document.getElementById(inputId);
            const fichaId = document.getElementById(hiddenId);
            const fichaResults = document.getElementById(resultsId);

            const hideResults = () => {
                fichaResults.classList.remove('is-visible');
                fichaInput.setAttribute('aria-expanded', 'false');
            };

            const showResults = () => {
                const term = fichaInput.value.trim().toLocaleLowerCase();
                fichaResults.replaceChildren();
                if (!term) {
                    hideResults();
                    return;
                }

                const matches = fichas
                    .filter(ficha => String(ficha.code).toLocaleLowerCase().includes(term))
                    .slice(0, 5);

                if (!matches.length) {
                    const empty = document.createElement('div');
                    empty.className = 'ficha-search-empty';
                    empty.textContent = 'Sin coincidencias';
                    fichaResults.append(empty);
                } else {
                    matches.forEach(ficha => {
                        const option = document.createElement('button');
                        option.type = 'button';
                        option.className = 'ficha-search-option';
                        option.setAttribute('role', 'option');
                        option.textContent = ficha.code;
                        option.addEventListener('click', () => {
                            fichaInput.value = ficha.code;
                            fichaId.value = ficha.id;
                            fichaInput.setCustomValidity('');
                            hideResults();
                            fichaInput.focus();
                        });
                        fichaResults.append(option);
                    });
                }

                fichaResults.classList.add('is-visible');
                fichaInput.setAttribute('aria-expanded', 'true');
            };

            fichaInput.addEventListener('input', () => {
                fichaId.value = '';
                fichaInput.setCustomValidity('');
                showResults();
            });
            fichaInput.addEventListener('focus', showResults);
            fichaInput.addEventListener('keydown', event => {
                if (event.key === 'Escape') hideResults();
                if (event.key === 'Enter' && fichaResults.querySelector('.ficha-search-option')) {
                    event.preventDefault();
                    fichaResults.querySelector('.ficha-search-option').click();
                }
            });
            document.addEventListener('click', event => {
                if (!fichaResults.parentElement.contains(event.target)) hideResults();
            });
        };

        configurarBusquedaFicha('filtroFicha', 'filtroFichaId', 'cronogramaFichaResults');
        configurarBusquedaFicha('progFicha', 'progFichaId', 'progFichaResults');

        const filtroVista = document.getElementById('filtroVista');
        document.querySelectorAll('#pills-tab [data-bs-toggle="pill"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', event => {
                filtroVista.value = event.target.id === 'tab-lista' ? 'lista' : 'calendario';
            });
        });

        fechaUltimaActualizacionSemaforo = obtenerFechaLocalActual();
        programarActualizacionSemaforoDiaria();
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible' && fechaUltimaActualizacionSemaforo !== obtenerFechaLocalActual()) {
                fechaUltimaActualizacionSemaforo = obtenerFechaLocalActual();
                cargarEventosCalendario(anioActual, mesActual);
                programarActualizacionSemaforoDiaria();
            }
        });

        cargarEventosCalendario(anioActual, mesActual);

        // Controles de navegación de mes
        document.getElementById('prevMonthBtn').addEventListener('click', function () {
            mesActual--;
            if (mesActual < 1) {
                mesActual = 12;
                anioActual--;
            }
            cargarEventosCalendario(anioActual, mesActual);
        });

        document.getElementById('nextMonthBtn').addEventListener('click', function () {
            mesActual++;
            if (mesActual > 12) {
                mesActual = 1;
                anioActual++;
            }
            cargarEventosCalendario(anioActual, mesActual);
        });

        document.getElementById('todayBtn').addEventListener('click', function () {
            const hoy = new Date();
            anioActual = hoy.getFullYear();
            mesActual = hoy.getMonth() + 1;
            cargarEventosCalendario(anioActual, mesActual);
        });
    });

    // ── 1. Cargar Eventos y Renderizar Calendario (Criterio 1, 5, 6) ──
    function cargarEventosCalendario(year, month) {
        const filtrosFicha = new URLSearchParams();
        const fichaId = document.getElementById('filtroFichaId')?.value;
        const ficha = document.getElementById('filtroFicha')?.value.trim();
        if (fichaId) {
            filtrosFicha.set('ficha_id', fichaId);
        } else if (ficha) {
            filtrosFicha.set('ficha', ficha);
        }
        const query = filtrosFicha.toString();
        const url = `{{ route('controlecp.administrador.cronograma.eventos') }}?year=${year}&month=${month}${query ? `&${query}` : ''}`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    eventosCalendario = data.data;
                    diasNoLaboralesCalendario = data.dias_no_laborales || [];
                    renderizarCalendario(year, month, eventosCalendario);
                }
            })
            .catch(err => console.error('Error cargando eventos:', err));
    }

    function renderizarCalendario(year, month, eventos) {
        const grid = document.getElementById('calendarGrid');
        const titleEl = document.getElementById('calendarMonthTitle');

        const mesesNombres = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];
        titleEl.textContent = `${mesesNombres[month - 1]} ${year}`;

        // Limpiar días previos (mantener los 7 encabezados)
        while (grid.children.length > 7) {
            grid.removeChild(grid.lastChild);
        }

        const primerDia = new Date(year, month - 1, 1);
        let diaSemanaInicio = primerDia.getDay() - 1; // Lunes = 0
        if (diaSemanaInicio === -1) diaSemanaInicio = 6; // Domingo = 6

        const diasEnMes = new Date(year, month, 0).getDate();
        const diasEnMesAnterior = new Date(year, month - 1, 0).getDate();

        const hoy = new Date();
        const esMismoMesYHoy = hoy.getFullYear() === year && (hoy.getMonth() + 1) === month;
        const diaHoyNumero = hoy.getDate();
        const diasNoLaborales = new Set(diasNoLaboralesCalendario);
        const fechaComoTexto = date =>
            `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;

        // 1. Días del mes anterior
        for (let i = diaSemanaInicio - 1; i >= 0; i--) {
            const numDia = diasEnMesAnterior - i;
            const fecha = new Date(year, month - 2, numDia);
            const celda = document.createElement('div');
            celda.className = 'calendar-day-cell other-month';
            if (diasNoLaborales.has(fechaComoTexto(fecha))) {
                celda.classList.add('non-working-day');
            }
            celda.innerHTML = `
                <div class="calendar-day-header-top">
                    <span class="calendar-day-number">${numDia}</span>
                </div>
            `;
            grid.appendChild(celda);
        }

        // 2. Días del mes actual
        for (let dia = 1; dia <= diasEnMes; dia++) {
            const fechaStr = `${year}-${String(month).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
            const esHoy = esMismoMesYHoy && dia === diaHoyNumero;

            const celda = document.createElement('div');
            celda.className = `calendar-day-cell ${esHoy ? 'today' : ''}`;
            if (diasNoLaborales.has(fechaStr)) {
                celda.classList.add('non-working-day');
            }

            // Filtrar eventos de este día
            const eventosDelDia = eventos.filter(e => e.fecha === fechaStr);
            if (eventosDelDia.length) {
                celda.classList.add('has-events');
            }
            const prioridadSemaforo = ['rojo', 'amarillo', 'verde', 'neutro'];
            const semaforoDia = prioridadSemaforo.find(semaforo =>
                eventosDelDia.some(evento =>
                    evento.estado === 'Pendiente'
                        ? evento.semaforo === semaforo
                        : semaforo === 'neutro'
                )
            ) || 'neutro';

            celda.innerHTML = `
                <div class="calendar-day-header-top">
                    <span class="calendar-day-number">${dia}</span>
                    ${esHoy ? '<span class="badge bg-success small" style="font-size: 0.6rem;">Hoy</span>' : ''}
                </div>
                ${eventosDelDia.length
                    ? `<span class="calendar-day-activity-indicator semaforo-${semaforoDia}"><span class="activity-dot"></span>${eventosDelDia.length} ${eventosDelDia.length === 1 ? 'actividad' : 'actividades'}</span>`
                    : ''}
            `;
            celda.setAttribute('role', 'button');
            celda.setAttribute('tabindex', '0');
            celda.setAttribute('aria-label', `${dia} de ${mesesNombres[month - 1]}: ${eventosDelDia.length} actividades`);
            celda.addEventListener('click', () => mostrarActividadesDia(fechaStr, eventosDelDia));
            celda.addEventListener('keydown', event => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    mostrarActividadesDia(fechaStr, eventosDelDia);
                }
            });
            grid.appendChild(celda);
        }

        // 3. Días del mes siguiente para completar la cuadrícula de 35 o 42 celdas
        const totalCeldasActuales = diaSemanaInicio + diasEnMes;
        const celdasRestantes = (totalCeldasActuales <= 35 ? 35 : 42) - totalCeldasActuales;
        for (let diaSig = 1; diaSig <= celdasRestantes; diaSig++) {
            const fecha = new Date(year, month, diaSig);
            const celda = document.createElement('div');
            celda.className = 'calendar-day-cell other-month';
            if (diasNoLaborales.has(fechaComoTexto(fecha))) {
                celda.classList.add('non-working-day');
            }
            celda.innerHTML = `
                <div class="calendar-day-header-top">
                    <span class="calendar-day-number">${diaSig}</span>
                </div>
            `;
            grid.appendChild(celda);
        }
    }

    function mostrarActividadesDia(fecha, eventos) {
        const [year, month, day] = fecha.split('-').map(Number);
        const fechaTexto = new Date(year, month - 1, day).toLocaleDateString('es-CO', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        });
        const titulo = document.getElementById('actividadesDiaTitulo');
        const resumen = document.getElementById('actividadesDiaResumen');
        const lista = document.getElementById('actividadesDiaLista');
        const modalElement = document.getElementById('modalActividadesDia');

        titulo.textContent = `Actividades del ${fechaTexto}`;
        resumen.textContent = eventos.length
            ? `${eventos.length} ${eventos.length === 1 ? 'actividad programada' : 'actividades programadas'}`
            : 'No hay actividades programadas para este día.';
        lista.replaceChildren();

        if (!eventos.length) {
            const vacio = document.createElement('div');
            vacio.className = 'text-center text-muted py-4';
            vacio.textContent = 'No hay actividades programadas para esta fecha.';
            lista.append(vacio);
        } else {
            eventos
                .slice()
                .sort((a, b) => String(a.hora_inicio || '').localeCompare(String(b.hora_inicio || '')))
                .forEach(evento => {
                    const tarjeta = document.createElement('div');
                    tarjeta.className = 'calendar-day-activity';

                    const encabezado = document.createElement('div');
                    encabezado.className = 'd-flex flex-wrap justify-content-between align-items-start gap-2';

                    const tituloActividad = document.createElement('h6');
                    tituloActividad.className = 'fw-bold mb-1';
                    tituloActividad.textContent = evento.titulo || 'Actividad programada';

                    const estado = document.createElement('span');
                    estado.className = 'badge rounded-pill bg-light text-dark border';
                    estado.textContent = evento.estado === 'Cancelada'
                        ? 'Desactivada'
                        : (evento.estado || 'Sin estado');

                    encabezado.append(tituloActividad, estado);

                    const detalles = document.createElement('div');
                    detalles.className = 'activity-meta';
                    detalles.textContent = `Ficha ${evento.ficha_codigo || '—'} · ${evento.horario || 'Horario sin definir'}`;

                    const ubicacion = document.createElement('div');
                    ubicacion.className = 'activity-meta';
                    ubicacion.textContent = `Instructor: ${evento.instructor_nombre || 'Sin asignar'} · Lugar: ${evento.lugar || 'Sin definir'}`;

                    const acciones = document.createElement('div');
                    acciones.className = 'text-end mt-2';
                    const botonDetalle = document.createElement('button');
                    botonDetalle.type = 'button';
                    botonDetalle.className = 'btn btn-sm btn-outline-success rounded-pill px-3';
                    botonDetalle.innerHTML = '<i class="fas fa-circle-info me-1"></i>Ver detalle';
                    botonDetalle.addEventListener('click', () => {
                        modalElement.addEventListener('hidden.bs.modal', () => verDetalleSesion(evento.id), { once: true });
                        bootstrap.Modal.getOrCreateInstance(modalElement).hide();
                    });
                    acciones.append(botonDetalle);

                    tarjeta.append(encabezado, detalles, ubicacion, acciones);
                    lista.append(tarjeta);
                });
        }

        bootstrap.Modal.getOrCreateInstance(modalElement).show();
    }

    // ── 2. Filtro Rápido desde las Tarjetas de Semáforo ──
    function filtrarPorSemaforoCard(tipo) {
        const selectSem = document.getElementById('filtroSemaforo');
        if (selectSem) {
            selectSem.value = tipo;
            document.getElementById('filtroVista').value = 'lista';
            document.getElementById('formFiltrosCronograma').submit();
        }
    }

    // ── 3. Manejo de Filas Dinámicas de Recursos (Criterio 4) ──
    function agregarFilaRecurso(contenedorId = 'contenedorRecursos') {
        const contenedor = document.getElementById(contenedorId);
        const index = contenedor.children.length;

        let opcionesRecursos = '<option value="">— Seleccionar Recurso de Inventario —</option>';
        CATALOGO_INVENTARIO.forEach(rec => {
            opcionesRecursos += `<option value="${rec.id_inventario}" data-disponible="${rec.cantidad_disponible}">
                ${rec.nombre} (Disponibles: ${rec.cantidad_disponible})
            </option>`;
        });

        const fila = document.createElement('div');
        fila.className = 'row g-2 align-items-center bg-white p-2 rounded-pill border shadow-xs';
        fila.innerHTML = `
            <div class="col-7">
                <select name="recursos[${index}][id_inventario]" class="form-select form-select-sm border-0 bg-transparent rounded-pill" required onchange="validarStockFila(this)">
                    ${opcionesRecursos}
                </select>
            </div>
            <div class="col-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent border-0 small text-muted">Cant:</span>
                    <input type="number" name="recursos[${index}][cantidad]" class="form-control form-control-sm rounded-pill" value="1" min="1" required oninput="validarStockFila(this)">
                </div>
            </div>
            <div class="col-2 text-end pe-2">
                <button type="button" class="btn btn-sm btn-outline-danger rounded-circle" style="width: 28px; height: 28px; padding: 0;" onclick="this.closest('.row').remove()" title="Quitar recurso">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        contenedor.appendChild(fila);
    }

    function validarStockFila(element) {
        const fila = element.closest('.row');
        const select = fila.querySelector('select');
        const inputCant = fila.querySelector('input[type="number"]');

        const selectedOption = select.options[select.selectedIndex];
        const disponible = parseInt(selectedOption?.getAttribute('data-disponible') || 0);
        const cantidad = parseInt(inputCant.value || 0);

        if (cantidad > disponible && disponible > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Disponibilidad insuficiente',
                text: `Solo hay ${disponible} unidades disponibles de este recurso.`,
                confirmButtonColor: '#39A900'
            });
            inputCant.value = disponible;
        }
    }

    // ── 4. Validación en Vivo de Disponibilidad de Instructor (Criterio 3) ──
    let timeoutDisponibilidad = null;
    function verificarDisponibilidadEnVivo() {
        clearTimeout(timeoutDisponibilidad);
        timeoutDisponibilidad = setTimeout(() => {
            const instructorId = document.getElementById('progInstructor').value;
            const fecha = document.getElementById('progFecha').value;
            const horaInicio = document.getElementById('progHoraInicio').value;
            const horaFin = document.getElementById('progHoraFin').value;
            const feedback = document.getElementById('progInstructorFeedback');

            if (!instructorId || !fecha || !horaInicio || !horaFin) {
                feedback.className = 'd-none';
                return;
            }

            fetch('{{ route('controlecp.administrador.cronograma.verificar_disponibilidad') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    id_instructor: instructorId,
                    fecha: fecha,
                    hora_inicio: horaInicio,
                    hora_fin: horaFin
                })
            })
            .then(res => res.json())
            .then(data => {
                feedback.classList.remove('d-none');
                if (data.disponible) {
                    feedback.className = 'small mt-1 text-success fw-bold';
                    feedback.innerHTML = '<i class="fas fa-check-circle me-1"></i>Instructor libre en este horario.';
                } else {
                    feedback.className = 'small mt-1 text-danger fw-bold';
                    feedback.innerHTML = `<i class="fas fa-circle-exclamation me-1"></i>${data.mensaje}`;
                }
            })
            .catch(err => console.error(err));
        }, 400);
    }

    // ── 5. Guardar Nueva Programación (Criterios 1, 2, 3, 4, 12) ──
    function ejecutarGuardarProgramacion() {
        const form = document.getElementById('formProgramarActividad');
        const fichaInput = document.getElementById('progFicha');
        const fichaId = document.getElementById('progFichaId');
        fichaInput.setCustomValidity(fichaId.value ? '' : 'Selecciona una ficha de la lista.');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const btn = document.getElementById('btnGuardarProgramacion');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Guardando...';

        const formData = new FormData(form);

        fetch('{{ route('controlecp.administrador.cronograma.store') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(({ status, body }) => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-floppy-disk me-1"></i>Guardar Programación';

            if (status === 200 && body.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalProgramarActividad')).hide();
                Swal.fire({
                    icon: 'success',
                    title: '¡Programada con éxito!',
                    text: body.message,
                    confirmButtonColor: '#39A900'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo guardar',
                    text: body.message || 'Por favor verifica los campos obligatorios o la disponibilidad.',
                    confirmButtonColor: '#001A29'
                });
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-floppy-disk me-1"></i>Guardar Programación';
            Swal.fire({
                icon: 'error',
                title: 'Error de servidor',
                text: 'Ocurrió un error inesperado al procesar la solicitud.'
            });
        });
    }

    // ── 6. Ver Detalle y Trazabilidad (Modal 5) ──
    function verDetalleSesion(idCronograma) {
        fetch(`{{ url('/control-ecp/administrador/cronograma') }}/${idCronograma}`)
            .then(res => res.json())
            .then(res => {
                if (!res.success) return;
                const d = res.data;

                // Cabecera modal
                document.getElementById('detTituloCalendario').textContent = `${d.actividad_numero ? 'Actividad ' + d.actividad_numero : 'Actividad'} – ${d.momento_nombre}`;
                document.getElementById('detActividadNombre').textContent = d.actividad_nombre;
                document.getElementById('detFichaBadge').textContent = `Ficha ${d.ficha_code} - ${d.ficha_programa || ''}`;

                const badgeEstado = document.getElementById('detEstadoBadge');
                badgeEstado.textContent = d.estado === 'Cancelada' ? 'Desactivada' : d.estado;
                badgeEstado.className = d.estado === 'Finalizada' ? 'badge rounded-pill bg-success px-3' :
                                       (d.estado === 'Pospuesta' ? 'badge rounded-pill bg-warning text-dark px-3' :
                                       (d.estado === 'Cancelada' ? 'badge rounded-pill bg-secondary px-3' : 'badge rounded-pill bg-primary px-3'));

                const dot = document.getElementById('detSemaforoDot');
                dot.className = 'semaforo-indicator ' + (d.semaforo === 'rojo' ? 'semaforo-rojo' : (d.semaforo === 'amarillo' ? 'semaforo-amarillo' : (d.semaforo === 'verde' ? 'semaforo-verde' : 'semaforo-neutro')));

                // Detalles
                document.getElementById('detFechaHorario').textContent = `${d.fecha_formateada} (${d.horario})`;
                document.getElementById('detLugar').textContent = d.lugar;
                document.getElementById('detInstructor').textContent = d.instructor_nombre;
                document.getElementById('detCreador').textContent = d.creador_nombre;

                // Recursos
                const contenedorRec = document.getElementById('detListaRecursos');
                if (d.recursos && d.recursos.length > 0) {
                    let htmlRec = '';
                    d.recursos.forEach(r => {
                        htmlRec += `
                            <div class="d-flex justify-content-between align-items-center bg-white p-2 rounded-3 border">
                                <span class="fw-semibold text-dark"><i class="fas fa-box text-success me-1"></i>${r.nombre}</span>
                                <span class="badge bg-success-subtle text-success border">Cant: ${r.cantidad}</span>
                            </div>
                        `;
                    });
                    contenedorRec.innerHTML = htmlRec;
                } else {
                    contenedorRec.innerHTML = '<span class="text-muted">Sin recursos asignados.</span>';
                }

                // Trazabilidad / Historial
                const contHist = document.getElementById('detTimelineHistorial');
                if (d.historial && d.historial.length > 0) {
                    let htmlH = '';
                    d.historial.forEach(h => {
                        htmlH += `
                            <div class="d-flex gap-2 align-items-start border-bottom pb-2">
                                <span class="timeline-dot mt-1"></span>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold text-dark text-capitalize">${h.accion}</span>
                                        <span class="text-muted" style="font-size: 0.7rem;">${h.fecha_registro}</span>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.75rem;">Por: <strong>${h.autor}</strong></div>
                                    ${h.motivo ? `<div class="p-1 px-2 mt-1 bg-light rounded text-dark" style="font-size: 0.72rem;">"${h.motivo}"</div>` : ''}
                                </div>
                            </div>
                        `;
                    });
                    contHist.innerHTML = htmlH;
                } else {
                    contHist.innerHTML = '<span class="text-muted">No hay registros de historial aún.</span>';
                }

                // Configurar botones de acción del modal detalle
                const btnDetDesactivar = document.getElementById('btnDetDesactivar');
                const btnDetPosponer = document.getElementById('btnDetPosponer');
                const btnDetReasignar = document.getElementById('btnDetReasignar');
                const estaDesactivada = d.estado === 'Cancelada';

                btnDetDesactivar.hidden = estaDesactivada || d.estado === 'Finalizada';
                btnDetPosponer.hidden = estaDesactivada;
                btnDetReasignar.hidden = estaDesactivada;

                btnDetDesactivar.onclick = () => {
                    bootstrap.Modal.getInstance(document.getElementById('modalDetalleSesion')).hide();
                    confirmarDesactivarSesion(d.id_cronograma);
                };
                btnDetPosponer.onclick = () => {
                    bootstrap.Modal.getInstance(document.getElementById('modalDetalleSesion')).hide();
                    abrirModalPosponer(d.id_cronograma);
                };
                btnDetReasignar.onclick = () => {
                    bootstrap.Modal.getInstance(document.getElementById('modalDetalleSesion')).hide();
                    abrirModalReasignar(d.id_cronograma);
                };

                new bootstrap.Modal(document.getElementById('modalDetalleSesion')).show();
            });
    }

    // ── 7. Abrir y Ejecutar Edición (Criterio 8) ──
    function abrirModalEditar(idCronograma) {
        fetch(`{{ url('/control-ecp/administrador/cronograma') }}/${idCronograma}`)
            .then(res => res.json())
            .then(res => {
                if (!res.success) return;
                const d = res.data;

                document.getElementById('editCronogramaId').value = d.id_cronograma;
                document.getElementById('editActividad').value = d.id_actividad;
                document.getElementById('editFicha').value = d.id_ficha;
                document.getElementById('editFecha').value = d.fecha;
                document.getElementById('editHoraInicio').value = d.hora_inicio;
                document.getElementById('editHoraFin').value = '15:00';
                document.getElementById('editLugar').value = d.lugar;
                document.getElementById('editMotivo').value = '';

                // Cargar recursos existentes
                const contEditRec = document.getElementById('contenedorRecursosEdit');
                contEditRec.innerHTML = '';
                if (d.recursos && d.recursos.length > 0) {
                    d.recursos.forEach(r => {
                        agregarFilaRecurso('contenedorRecursosEdit');
                        const ultFila = contEditRec.lastElementChild;
                        ultFila.querySelector('select').value = r.id_inventario;
                        ultFila.querySelector('input[type="number"]').value = r.cantidad;
                    });
                }

                new bootstrap.Modal(document.getElementById('modalEditarProgramacion')).show();
            });
    }

    function ejecutarActualizarProgramacion() {
        const id = document.getElementById('editCronogramaId').value;
        const form = document.getElementById('formEditarProgramacion');
        const formData = new FormData(form);

        fetch(`{{ url('/control-ecp/administrador/cronograma') }}/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalEditarProgramacion')).hide();
                Swal.fire({
                    icon: 'success',
                    title: '¡Actualizado!',
                    text: body.message,
                    confirmButtonColor: '#39A900'
                }).then(() => location.reload());
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error al actualizar',
                    text: body.message || 'Verifica los campos obligatorios.',
                    confirmButtonColor: '#001A29'
                });
            }
        });
    }

    // ── 8. Reasignar Instructor (Criterio 10) ──
    function abrirModalReasignar(idCronograma) {
        fetch(`{{ url('/control-ecp/administrador/cronograma') }}/${idCronograma}`)
            .then(res => res.json())
            .then(res => {
                if (!res.success) return;
                const d = res.data;
                document.getElementById('reasignarCronogramaId').value = d.id_cronograma;
                document.getElementById('reasignarActividadNombre').textContent = `${d.actividad_numero ? 'Actividad ' + d.actividad_numero + ': ' : ''}${d.actividad_nombre}`;
                document.getElementById('reasignarHorarioTexto').textContent = `${d.fecha_formateada} (${d.horario}) — Lugar: ${d.lugar}`;
                document.getElementById('reasignarInstructorActual').textContent = d.instructor_nombre;
                document.getElementById('nuevoInstructorId').value = '';
                document.getElementById('motivoReasignacion').value = '';

                new bootstrap.Modal(document.getElementById('modalReasignarInstructor')).show();
            });
    }

    function ejecutarReasignarInstructor() {
        const id = document.getElementById('reasignarCronogramaId').value;
        const nuevoInstructor = document.getElementById('nuevoInstructorId').value;
        const motivo = document.getElementById('motivoReasignacion').value.trim();

        if (!nuevoInstructor || !motivo) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos requeridos',
                text: 'Debes seleccionar el nuevo instructor e ingresar el motivo de la reasignación.'
            });
            return;
        }

        fetch(`{{ url('/control-ecp/administrador/cronograma') }}/${id}/reasignar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                nuevo_instructor_id: nuevoInstructor,
                motivo_reasignacion: motivo
            })
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalReasignarInstructor')).hide();
                Swal.fire({
                    icon: 'success',
                    title: '¡Instructor Reasignado!',
                    text: body.message,
                    confirmButtonColor: '#39A900'
                }).then(() => location.reload());
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Conflicto de Horario',
                    text: body.message,
                    confirmButtonColor: '#001A29'
                });
            }
        });
    }

    // ── 9. Posponer Actividad (Criterio 11) ──
    function abrirModalPosponer(idCronograma) {
        fetch(`{{ url('/control-ecp/administrador/cronograma') }}/${idCronograma}`)
            .then(res => res.json())
            .then(res => {
                if (!res.success) return;
                const d = res.data;
                document.getElementById('posponerCronogramaId').value = d.id_cronograma;
                document.getElementById('posponerActividadNombre').textContent = `${d.actividad_numero ? 'Actividad ' + d.actividad_numero + ': ' : ''}${d.actividad_nombre}`;
                document.getElementById('posponerFechaActual').textContent = `${d.fecha_formateada} (${d.horario})`;
                document.getElementById('posponerNuevaFecha').value = '';
                document.getElementById('posponerHoraInicio').value = d.hora_inicio;
                document.getElementById('posponerHoraFin').value = d.hora_fin;
                document.getElementById('posponerMotivo').value = '';

                new bootstrap.Modal(document.getElementById('modalPosponerActividad')).show();
            });
    }

    function ejecutarPosponerActividad() {
        const id = document.getElementById('posponerCronogramaId').value;
        const nuevaFecha = document.getElementById('posponerNuevaFecha').value;
        const motivo = document.getElementById('posponerMotivo').value.trim();
        const horaInicio = document.getElementById('posponerHoraInicio').value;
        const horaFin = document.getElementById('posponerHoraFin').value;

        if (!nuevaFecha || !motivo) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos obligatorios',
                text: 'La nueva fecha y el motivo de posposición son obligatorios.'
            });
            return;
        }

        fetch(`{{ url('/control-ecp/administrador/cronograma') }}/${id}/posponer`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                nueva_fecha: nuevaFecha,
                hora_inicio: horaInicio,
                hora_fin: horaFin,
                motivo: motivo
            })
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalPosponerActividad')).hide();
                Swal.fire({
                    icon: 'success',
                    title: '¡Actividad Pospuesta!',
                    text: body.message,
                    confirmButtonColor: '#39A900'
                }).then(() => location.reload());
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo posponer',
                    text: body.message,
                    confirmButtonColor: '#001A29'
                });
            }
        });
    }

    // ── 10. Desactivar Programación sin eliminar su historial ──
    function confirmarDesactivarSesion(idCronograma) {
        Swal.fire({
            title: '¿Desactivar programación?',
            text: 'La programación se conservará en el historial como desactivada y se liberarán los recursos. No se permite si ya tiene asistencia.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#39A900',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="fas fa-ban me-1"></i>Sí, desactivar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('/control-ecp/administrador/cronograma') }}/${idCronograma}/desactivar`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json().then(data => ({ status: res.status, body: data })))
                .then(({ status, body }) => {
                    if (status === 200 && body.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Programación desactivada!',
                            text: body.message,
                            confirmButtonColor: '#39A900'
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'No se puede desactivar',
                            text: body.message,
                            confirmButtonColor: '#001A29'
                        });
                    }
                });
            }
        });
    }
</script>
@endpush
@endsection
