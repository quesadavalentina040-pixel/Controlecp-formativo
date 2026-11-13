@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .asistencia-hero {
        background: linear-gradient(120deg, #001A29, #00324D);
        border-radius: 24px;
        padding: 1.5rem 2rem;
        color: #fff;
    }
    .asistencia-hero .hero-eyebrow {
        color: #62E31D;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
    }
    .asistencia-hero h2 {
        color: #fff;
        font-family: 'Outfit', sans-serif;
        font-size: 2rem;
        font-weight: 800;
        margin: .35rem 0 .25rem;
    }
    .asistencia-hero p {
        color: #c7d4da;
    }
    .asistencia-hero a {
        color: #39A900;
    }
    .kpi-asistencia {
        border-radius: 16px;
        background: #fff;
        border: 1px solid rgba(0, 0, 0, .05);
        padding: 1rem 1.25rem;
        box-shadow: 0 4px 16px rgba(0, 26, 41, .06);
        transition: transform .18s;
    }
    .kpi-asistencia:hover {
        transform: translateY(-2px);
    }
    .asistencia-filters {
        display: grid;
        grid-template-columns: minmax(160px, 1fr) minmax(170px, 1.45fr) minmax(190px, 1.6fr) repeat(2, minmax(145px, 1fr)) 150px;
        gap: .65rem;
        align-items: end;
    }
    .asistencia-filters .form-label {
        display: block;
        margin: 0 0 .3rem;
        color: #fff;
        font-size: .75rem;
        font-weight: 700;
    }
    .asistencia-filters .form-select,
    .asistencia-filters .form-control {
        min-width: 0;
        width: 100%;
        height: 44px;
        padding: .55rem .8rem;
        border: 1px solid #fff;
        border-radius: 10px;
        background-color: #fff;
        color: #001A29;
        font-size: .88rem;
        box-shadow: none;
    }
    .asistencia-filter-card {
        padding: 1rem 1.15rem;
        margin-bottom: 1.5rem;
        background: #001A29;
        border-radius: 18px;
        box-shadow: 0 5px 20px rgba(0, 26, 41, .08);
    }
    .asistencia-filter-actions {
        display: flex;
        gap: .5rem;
        min-width: 0;
    }
    .asistencia-filter-actions .asistencia-action-button {
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
    .asistencia-filter-actions .asistencia-action-button:hover {
        background: #2e8b00;
        color: #fff;
    }
    .asistencia-filter-reset {
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
    .asistencia-filter-reset:hover {
        background: #f1f5f9;
        color: #001A29;
    }
    .ficha-search-results {
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
    .ficha-search-results.is-visible {
        display: block;
    }
    .ficha-search-option {
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
    .ficha-search-option:hover,
    .ficha-search-option:focus {
        background: #f0fbf4;
        outline: none;
    }
    .ficha-search-empty {
        padding: .4rem .55rem;
        color: #6c757d;
        font-size: .8rem;
    }
    .asistencia-hero {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
    }
    .asistencia-action-button {
        background-color: var(--sena-green);
        border-color: var(--sena-green);
        color: #fff;
    }
    .asistencia-action-button:hover,
    .asistencia-action-button:focus {
        background-color: var(--sena-green-hover);
        border-color: var(--sena-green-hover);
        color: #fff;
    }
    .asistencia-row-actions {
        display: flex;
        justify-content: flex-end;
        gap: .4rem;
        white-space: nowrap;
    }
    .asistencia-row-actions form {
        display: inline-flex;
    }
    .asistencia-row-action {
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
    .asistencia-row-action:hover,
    .asistencia-row-action:focus-visible {
        transform: translateY(-2px);
        box-shadow: 0 3px 8px rgba(0, 26, 41, .14);
    }
    .asistencia-row-action--view {
        background: #eaf5ff;
        border-color: #c9e6fb;
        color: #0876bd;
    }
    .asistencia-row-action--view:hover {
        background: #0876bd;
        border-color: #0876bd;
        color: #fff;
    }
    .asistencia-row-action--edit {
        background: #fff7e6;
        border-color: #ffe0a3;
        color: #b7791f;
    }
    .asistencia-row-action--edit:hover {
        background: #d99000;
        border-color: #d99000;
        color: #fff;
    }
    .asistencia-row-action--history {
        background: #f3edff;
        border-color: #ddccff;
        color: #7541c8;
    }
    .asistencia-row-action--history:hover {
        background: #7541c8;
        border-color: #7541c8;
        color: #fff;
    }
    .asistencia-row-action--finish {
        background: #eaf8ef;
        border-color: #ccebd6;
        color: #278344;
    }
    .asistencia-row-action--finish:hover {
        background: #278344;
        border-color: #278344;
        color: #fff;
    }
    .asistencia-row-action--register {
        width: auto;
        padding: 0 .85rem;
        background: #eaf8ef;
        border-color: #ccebd6;
        color: #278344;
        font-weight: 600;
    }
    .asistencia-row-action--register:hover {
        background: #278344;
        border-color: #278344;
        color: #fff;
    }
    .asistencia-modal-dialog {
        height: calc(100% - var(--bs-modal-margin) * 2);
    }
    .asistencia-modal-form {
        display: flex;
        flex: 1 1 auto;
        flex-direction: column;
        min-height: 0;
        overflow: hidden;
    }
    .asistencia-modal-form .modal-body {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
    }
    .asistencia-modal-form .modal-header,
    .asistencia-modal-form .modal-footer {
        flex-shrink: 0;
    }
    .asistencia-register-modal {
        overflow: hidden;
        border: 0;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 26, 41, .24);
    }
    .asistencia-register-modal .modal-header {
        padding: 1rem 1.35rem;
        border-bottom: 1px solid #e8eef2;
        background: #fff;
    }
    .asistencia-register-modal .modal-title {
        display: flex;
        align-items: center;
        gap: .75rem;
        color: #00324d;
    }
    .asistencia-register-modal .register-title-icon {
        display: inline-flex;
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        align-items: center;
        justify-content: center;
        border-radius: 13px;
        background: #eaf8ef;
        color: #278344;
    }
    .asistencia-register-modal .register-title-copy { min-width: 0; }
    .asistencia-register-modal .register-title-copy small {
        display: block;
        margin-top: .12rem;
        color: #718096;
        font-size: .78rem;
        font-weight: 500;
    }
    .asistencia-register-modal .modal-body { padding: 1.1rem 1.35rem; background: #f7fafb; }
    .asistencia-register-modal .register-hint {
        display: flex;
        align-items: flex-start;
        gap: .6rem;
        margin-bottom: 1rem;
        padding: .75rem .9rem;
        border: 1px solid #d5e9f4;
        border-radius: 12px;
        background: #f0f8fc;
        color: #36566a;
        font-size: .85rem;
        line-height: 1.45;
    }
    .asistencia-register-modal .register-hint i { margin-top: .1rem; color: #1683b5; }
    .asistencia-register-modal .register-roster-wrap {
        max-height: min(52vh, 500px);
        overflow: auto;
        border: 1px solid #e2eaf0;
        border-radius: 14px;
        background: #fff;
    }
    .asistencia-register-modal .register-roster-table { min-width: 700px; margin: 0; }
    .asistencia-register-modal .register-roster-table thead th {
        position: sticky;
        z-index: 1;
        top: 0;
        padding: .75rem .85rem;
        border-bottom: 1px solid #dfe7ed;
        background: #f3f7f9;
        color: #36566a;
        font-size: .75rem;
        font-weight: 750;
        letter-spacing: .025em;
    }
    .asistencia-register-modal .register-roster-table tbody td { padding: .55rem .85rem; border-color: #edf1f4; vertical-align: middle; }
    .asistencia-register-modal .form-control,
    .asistencia-register-modal .form-select { border-color: #dce5eb; border-radius: 9px; }
    .asistencia-register-modal .register-evidence {
        height: 100%;
        padding: .85rem;
        border: 1px solid #e2eaf0;
        border-radius: 12px;
        background: #fff;
    }
    .asistencia-register-modal .register-evidence .form-label { color: #36566a; font-size: .82rem; font-weight: 700; }
    .asistencia-register-modal .modal-footer { padding: .9rem 1.35rem 1.1rem; border-top: 1px solid #edf1f4; background: #fff; }
    .asistencia-register-modal .register-submit {
        padding: .6rem 1rem;
        border: 1px solid #278344;
        border-radius: 10px;
        background: #278344;
        color: #fff;
        font-weight: 700;
    }
    .asistencia-register-modal .register-submit:hover { border-color: #1d6e36; background: #1d6e36; }
    .asistencia-register-modal .register-submit:disabled { border-color: #d8e0e5; background: #f1f5f7; color: #8795a0; }
    .asistencia-register-modal .btn-close { padding: .65rem; border-radius: 50%; background-color: #f1f5f7; opacity: 1; }
    .asistencia-admin-edit-modal .modal-content {
        display: flex;
        height: 92vh;
        max-height: 92vh;
        overflow: hidden;
        flex-direction: column;
        border: 0;
        border-radius: 22px;
        box-shadow: 0 24px 60px rgba(0, 26, 41, .2);
    }
    .asistencia-admin-edit-modal .modal-header {
        flex: 0 0 auto;
        padding: 1rem 1.25rem;
        border-bottom: 0;
        background: linear-gradient(135deg, #001a29, #00324d);
        color: #fff;
    }
    .asistencia-admin-edit-modal .modal-title { margin: 0; color: #fff; font-size: 1.1rem; font-weight: 800; }
    .asistencia-admin-edit-modal .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }
    .asistencia-admin-edit-form { display: flex; min-height: 0; flex: 1 1 auto; flex-direction: column; }
    .asistencia-admin-edit-body {
        min-height: 0;
        flex: 1 1 auto;
        overflow-y: auto;
        padding: 1rem 1.25rem;
        background: #f4f9fb;
    }
    .asistencia-admin-edit-roster {
        max-height: min(58vh, 570px);
        overflow: auto;
        border: 1px solid #e2eaf0;
        border-radius: 14px;
        background: #fff;
    }
    .asistencia-admin-edit-table { width: 100%; min-width: 760px; margin: 0; background: #fff; }
    .asistencia-admin-edit-table thead th {
        position: sticky;
        z-index: 1;
        top: 0;
        padding: .9rem 1rem;
        border-bottom: 1px solid #dfeaf0;
        background: #eef7fa;
        color: #23465d;
        font-size: .74rem;
        font-weight: 800;
        letter-spacing: .045em;
        text-transform: uppercase;
    }
    .asistencia-admin-edit-table tbody td {
        padding: .9rem 1rem;
        border-bottom: 1px solid #edf2f5;
        vertical-align: middle;
        color: #1f2d3d;
        font-weight: 500;
    }
    .asistencia-admin-edit-table tbody tr:last-child td { border-bottom: 0; }
    .asistencia-admin-edit-table th:nth-child(1),
    .asistencia-admin-edit-table td:nth-child(1) { width: 14%; }
    .asistencia-admin-edit-table th:nth-child(2),
    .asistencia-admin-edit-table td:nth-child(2) { width: 35%; }
    .asistencia-admin-edit-table th:nth-child(3),
    .asistencia-admin-edit-table td:nth-child(3),
    .asistencia-admin-edit-table th:nth-child(4),
    .asistencia-admin-edit-table td:nth-child(4) { width: 11%; text-align: center; }
    .asistencia-admin-edit-table th:nth-child(5),
    .asistencia-admin-edit-table td:nth-child(5) { width: 29%; }
    .asistencia-admin-edit-table .form-check-input { display: inline-block; float: none; width: 1rem; height: 1rem; margin: 0; vertical-align: middle; }
    .asistencia-admin-edit-trigger {
        display: inline-flex;
        min-width: 145px;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        padding: .5rem .75rem;
        border: 1px solid #dfe7ed;
        border-radius: 10px;
        background: #fff;
        color: #1d2b3a;
        font-size: .82rem;
        font-weight: 650;
    }
    .asistencia-admin-edit-trigger:hover,
    .asistencia-admin-edit-trigger:focus-visible { border-color: #9ad7ff; background: #f4fbff; color: #0b4569; }
    .asistencia-admin-edit-trigger i { color: #0e9fd8; }
    .asistencia-admin-correction {
        margin-top: 1.1rem;
        padding: 1rem 1.1rem;
        border: 1px solid #e2eaf0;
        border-radius: 14px;
        background: #fff;
    }
    .asistencia-admin-correction .form-label { display: block; margin-bottom: .55rem; color: #23465d; }
    .asistencia-admin-correction .form-control { min-height: 88px; border-color: #d7e2e9; border-radius: 10px; resize: vertical; }
    .asistencia-admin-edit-footer {
        flex: 0 0 auto;
        justify-content: flex-end;
        gap: .75rem;
        margin: 0 .85rem .85rem !important;
        padding: .75rem 1.25rem 1rem !important;
        border: 0;
        background: #fff;
    }
    .asistencia-admin-edit-footer .btn { min-height: 44px; padding: .55rem 1rem; border-radius: 10px; }
    .asistencia-admin-edit-footer .btn-save {
        min-width: 150px;
        border: 1px solid #39a900;
        background: #39a900;
        color: #fff;
        font-weight: 700;
    }
    .asistencia-admin-edit-footer .btn-save:hover { border-color: #2e8b00; background: #2e8b00; color: #fff; }
    .asistencia-admin-excuse-modal .modal-content { overflow: hidden; border: 0; border-radius: 20px; box-shadow: 0 24px 60px rgba(0, 26, 41, .2); }
    .asistencia-admin-excuse-modal .modal-header { padding: 1rem 1.25rem; border: 0; background: linear-gradient(135deg, #001a29, #00324d); color: #fff; }
    .asistencia-admin-excuse-modal .modal-title { color: #fff; font-size: 1.05rem; font-weight: 800; }
    .asistencia-admin-excuse-modal .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }
    .asistencia-admin-excuse-modal .modal-body { padding: 1.25rem; background: #f6fbfc; }
    .asistencia-admin-excuse-modal .modal-footer { padding: .85rem 1.25rem; border-top: 1px solid #edf2f5; background: #fff; }
    .asistencia-admin-excuse-editor { display: flex; flex-direction: column; gap: .7rem; }
    .asistencia-admin-excuse-input-row { display: flex; align-items: center; gap: .65rem; }
    .asistencia-admin-excuse-input-row .form-control { min-width: 0; height: 42px; border-color: #dfe7ed; border-radius: 10px; }
    .asistencia-admin-upload {
        display: inline-flex;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: linear-gradient(135deg, #1ec7c4 0%, #2aa6d9 100%);
        box-shadow: 0 10px 22px rgba(28, 188, 188, .28);
        color: #fff;
        cursor: pointer;
    }
    .asistencia-admin-excuse-file { display: none; }
    .asistencia-admin-edit-modal .modal-dialog { max-width: min(1140px, calc(100% - 2rem)); }
    @media (max-width: 575.98px) {
        .asistencia-admin-edit-modal .modal-content { height: 94vh; max-height: 94vh; }
        .asistencia-admin-edit-modal .modal-header { padding: .9rem 1rem; }
        .asistencia-admin-edit-body { padding: .85rem; }
        .asistencia-admin-edit-footer { margin: 0 .45rem .45rem !important; padding: .65rem .65rem .8rem !important; }
        .asistencia-admin-edit-footer .btn { min-width: 0; flex: 1; }
    }
    .asistencia-detail-modal .modal-content {
        overflow: hidden;
        border: 0;
        border-radius: 22px;
        box-shadow: 0 24px 60px rgba(0, 26, 41, .2);
    }
    .asistencia-detail-modal .modal-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #edf2f5;
        background: linear-gradient(135deg, #001a29, #00324d);
        color: #fff;
    }
    .asistencia-detail-modal .modal-title { margin: 0; color: #fff; font-size: 1.1rem; font-weight: 800; }
    .asistencia-detail-modal .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }
    .asistencia-detail-modal .modal-body { padding: .35rem 1rem 1rem; background: #fff; }
    .asistencia-detail-modal .modal-footer { padding: .9rem 1.25rem; border-top: 1px solid #e8eef2; background: #fff; }
    .asistencia-detail-table { margin: 0; border-radius: 14px; background: #fff; overflow: hidden; }
    .asistencia-detail-table thead th {
        padding: .85rem 1rem;
        border-bottom: 1px solid #dfeaf0;
        background: #eef7fa;
        color: #23465d;
        font-size: .75rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .asistencia-detail-table tbody td { padding: .85rem 1rem; border-bottom: 1px solid #edf2f5; vertical-align: middle; color: #1f2d3d; }
    .asistencia-detail-table tbody tr:last-child td { border-bottom: 0; }
    [id^="detalleAsistencia"].asistencia-detail-modal .asistencia-detail-table { width: 100%; table-layout: fixed; }
    [id^="detalleAsistencia"].asistencia-detail-modal .asistencia-detail-table th:nth-child(1),
    [id^="detalleAsistencia"].asistencia-detail-modal .asistencia-detail-table td:nth-child(1) { width: 14%; }
    [id^="detalleAsistencia"].asistencia-detail-modal .asistencia-detail-table th:nth-child(2),
    [id^="detalleAsistencia"].asistencia-detail-modal .asistencia-detail-table td:nth-child(2) { width: 16%; }
    [id^="detalleAsistencia"].asistencia-detail-modal .asistencia-detail-table th:nth-child(3),
    [id^="detalleAsistencia"].asistencia-detail-modal .asistencia-detail-table td:nth-child(3) { width: 15%; }
    [id^="detalleAsistencia"].asistencia-detail-modal .asistencia-detail-table th:nth-child(4),
    [id^="detalleAsistencia"].asistencia-detail-modal .asistencia-detail-table td:nth-child(4) { width: 47%; overflow-wrap: anywhere; }
    [id^="detalleAsistencia"].asistencia-detail-modal .asistencia-detail-table th:nth-child(5),
    [id^="detalleAsistencia"].asistencia-detail-modal .asistencia-detail-table td:nth-child(5) { width: 8%; text-align: center; }
    [id^="detalleAsistencia"].asistencia-detail-modal .asistencia-detail-table .badge { white-space: nowrap; }
    [id^="detalleAsistencia"].asistencia-detail-modal .asistencia-support-link {
        display: inline-flex;
        width: 38px;
        height: 38px;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: linear-gradient(135deg, #1ec7c4 0%, #3aa9d8 100%);
        box-shadow: 0 8px 18px rgba(34, 190, 186, .28);
        color: #fff;
        text-decoration: none;
        transition: transform .15s ease, box-shadow .15s ease;
    }
    [id^="detalleAsistencia"].asistencia-detail-modal .asistencia-support-link:hover,
    [id^="detalleAsistencia"].asistencia-detail-modal .asistencia-support-link:focus-visible {
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 12px 20px rgba(34, 190, 186, .35);
    }
    [id^="detalleAsistencia"].asistencia-detail-modal .asistencia-support-link i { font-size: 1.1rem; }
    .asistencia-detail-modal .modal-dialog { max-height: 92vh; }
    .asistencia-detail-modal .modal-content { max-height: 92vh; }
    .asistencia-detail-modal .modal-body { overflow-y: auto; }
    .asistencia-detail-modal .asistencia-modal-button {
        min-width: 150px;
        min-height: 44px;
        padding: .55rem 1rem;
        border: 1px solid #39a900;
        border-radius: 10px;
        background: #39a900;
        color: #fff;
        font-weight: 700;
    }
    .asistencia-detail-modal .asistencia-modal-button:hover { border-color: #2e8b00; background: #2e8b00; color: #fff; }
    .asistencia-detail-modal .asistencia-modal-cancel { min-width: 120px; min-height: 44px; padding: .55rem 1rem; border-radius: 10px !important; }
    @media (max-width: 575.98px) {
        .asistencia-register-modal .modal-header { padding: .9rem 1rem; }
        .asistencia-register-modal .modal-body { padding: .85rem 1rem; }
        .asistencia-register-modal .modal-footer { padding: .8rem 1rem 1rem; }
        .asistencia-register-modal .register-submit { width: 100%; }
    }
    @media (max-width: 1199.98px) {
        .asistencia-filters {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .asistencia-filter-actions {
            grid-column: span 3;
            justify-content: flex-end;
        }
        .asistencia-filter-actions .asistencia-action-button {
            flex: 0 0 130px;
        }
    }
    @media (max-width: 767.98px) {
        .asistencia-filters {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .asistencia-filter-actions {
            grid-column: span 2;
        }
    }
    @media (max-width: 480px) {
        .asistencia-filters {
            grid-template-columns: minmax(0, 1fr);
        }
        .asistencia-filter-actions {
            grid-column: auto;
        }
        .asistencia-filter-actions .asistencia-action-button {
            flex: 1;
        }
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-3 py-3">
    <div class="asistencia-hero mb-4">
        <div>
            <div class="hero-eyebrow mt-3"><i class="fas fa-chart-line me-2"></i>Control ECP · Panel administrativo</div>
            <h2><i class="fas fa-user-check me-2" style="color: #39A900;"></i>Gestión de Asistencias</h2>
            <p class="small mb-0">Registra, controla y audita la asistencia de las actividades programadas.</p>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <div class="modal fade" id="nuevaAsistencia" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable asistencia-modal-dialog"><div class="modal-content asistencia-register-modal">
            <div class="modal-header">
                <h2 class="modal-title h5 fw-bold">
                    <span class="register-title-icon"><i class="fas fa-clipboard-check" aria-hidden="true"></i></span>
                    <span class="register-title-copy">Agregar asistencia<small>Selecciona la ficha y actividad para registrar la asistencia.</small></span>
                </h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="nuevaAsistenciaForm" class="asistencia-modal-form" method="POST" action="{{ route('controlecp.administrador.asistencia.store-manual') }}" enctype="multipart/form-data" data-aprendices-url="{{ url('/control-ecp/administrador/asistencia/fichas') }}" data-actividades-url="{{ url('/control-ecp/administrador/asistencia/fichas') }}">
                @csrf
                <div class="modal-body">
                    <div class="register-hint"><i class="fas fa-circle-info" aria-hidden="true"></i><span>Los aprendices se cargarán automáticamente al seleccionar una ficha. Todos aparecerán presentes por defecto; desmarca la asistencia para registrar una ausencia o excusa.</span></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nuevaFichaBuscar" class="form-label">Ficha *</label>
                            <div class="position-relative">
                                <input id="nuevaFichaBuscar" type="search" class="form-control" placeholder="Buscar ficha por número..." autocomplete="off" aria-autocomplete="list" aria-controls="nuevaFichaResultados" aria-expanded="false" required>
                                <input id="nuevaFicha" type="hidden" name="id_ficha">
                                <div id="nuevaFichaResultados" class="ficha-search-results" role="listbox"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="nuevaActividad" class="form-label">Actividad *</label>
                            <select id="nuevaActividad" name="cronograma_id" class="form-select" required disabled>
                                <option value="">Primero selecciona una ficha</option>
                            </select>
                        </div>
                    </div>
                    <p id="actividadesEstado" class="small text-muted mt-2 mb-0" aria-live="polite">Selecciona una ficha para ver sus actividades programadas.</p>

                    <hr class="my-4">
                    <h6 class="fw-bold mb-1">Aprendices de la ficha</h6>
                    <p id="aprendicesEstado" class="small text-muted mb-2" aria-live="polite">Selecciona una ficha para cargar sus aprendices.</p>
                    <div class="register-roster-wrap">
                        <table class="table table-hover align-middle register-roster-table">
                            <thead><tr><th>Documento</th><th>Aprendiz</th><th class="text-center">Presente</th><th class="text-center">Excusa</th><th>Motivo y soporte de excusa</th></tr></thead>
                            <tbody id="nuevosAprendices"></tbody>
                        </table>
                    </div>
                    <div class="row g-3 mt-2">
                        <div class="col-md-6"><div class="register-evidence"><label class="form-label">Foto opcional</label><input type="file" name="evidencia_foto" class="form-control form-control-sm" accept="image/jpeg,image/png"></div></div>
                        <div class="col-md-6"><div class="register-evidence"><label class="form-label">Enlace de video opcional</label><input type="url" name="evidencia_enlace" class="form-control form-control-sm" placeholder="https://..."></div></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button id="guardarNuevaAsistencia" type="submit" class="btn register-submit" disabled><i class="fas fa-floppy-disk me-2" aria-hidden="true"></i>Guardar asistencia</button>
                </div>
            </form>
        </div></div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="kpi-asistencia" style="border: 2px solid rgba(57,169,0,.35);">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted fw-semibold">Actividades</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background: #e8f8e8; color: #39A900;"><i class="fas fa-calendar-check"></i></div>
                </div>
                <h3 class="fw-bold mb-0" style="color: #001A29;">{{ number_format($stats['total']) }}</h3>
                <small class="text-muted">Programadas</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-asistencia" style="border: 2px solid rgba(30,112,0,.35);">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted fw-semibold">Registradas</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background: #e8f8e8; color: #1e7000;"><i class="fas fa-circle-check"></i></div>
                </div>
                <h3 class="fw-bold mb-0 text-success">{{ number_format($stats['registradas']) }}</h3>
                <small class="text-muted">Asistencias guardadas</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-asistencia" style="border: 2px solid rgba(192,57,43,.35);">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted fw-semibold">Pendientes</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background: #fdecea; color: #c0392b;"><i class="fas fa-clock"></i></div>
                </div>
                <h3 class="fw-bold mb-0 text-danger">{{ number_format($stats['pendientes']) }}</h3>
                <small class="text-muted">Sin registro</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-asistencia" style="border: 2px solid rgba(106,27,154,.35);">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-muted fw-semibold">Ausencias</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background: #f3e8f8; color: #6a1b9a;"><i class="fas fa-user-xmark"></i></div>
                </div>
                <h3 class="fw-bold mb-0" style="color: #6a1b9a;">{{ number_format($stats['ausencias']) }}</h3>
                <small class="text-muted">Aprendices ausentes</small>
            </div>
        </div>
    </div>

    <div class="asistencia-filter-card mb-4">
            <form method="GET" class="asistencia-filters">
                <div><label class="form-label small" for="filtroEstado">Estado</label><select id="filtroEstado" name="estado" class="form-select"><option value="">Todos</option>@foreach(['Registrada','Anulada','Pendiente','Sin asistencia registrada'] as $estado)<option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ $estado }}</option>@endforeach</select></div>
                <div><label class="form-label small">Instructor</label><select name="instructor_id" class="form-select"><option value="">Todos</option>@foreach($instructores as $instructor)<option value="{{ $instructor->id }}" @selected(request('instructor_id') == $instructor->id)>{{ $instructor->person?->full_name ?: $instructor->nickname }}</option>@endforeach</select></div>
                <div><label class="form-label small">Actividad</label><select name="actividad_id" class="form-select"><option value="">Todas</option>@foreach($actividades as $actividad)<option value="{{ $actividad->id }}" @selected(request('actividad_id') == $actividad->id)>{{ $actividad->numero }} - {{ $actividad->nombre }}</option>@endforeach</select></div>
                <div><label class="form-label small">Desde</label><input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="form-control"></div>
                <div><label class="form-label small">Hasta</label><input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="form-control"></div>
                <div class="asistencia-filter-actions">
                    <button type="submit" class="btn asistencia-action-button" title="Buscar asistencias"><i class="fas fa-magnifying-glass me-1"></i>Buscar</button>
                    <a href="{{ route('controlecp.administrador.asistencia') }}" class="btn asistencia-filter-reset" title="Restablecer filtros" aria-label="Restablecer filtros"><i class="fas fa-rotate-left"></i></a>
                </div>
            </form>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 pt-3"><h5 class="fw-bold mb-0">Actividades y asistencia</h5></div>
        <div class="table-responsive"><table class="table align-middle mb-0">
            <thead class="table-light"><tr><th>Fecha</th><th>Ficha</th><th>Actividad</th><th>Instructor</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
            @forelse($cronogramas as $sesion)
                @php $registro = $sesion->asistencia; $nombreInstructor = $sesion->instructor?->person?->full_name ?: $sesion->instructor?->nickname; $vencida = $sesion->fecha && $sesion->fecha->isPast(); @endphp
                <tr>
                    <td>{{ optional($sesion->fecha)->format('d/m/Y') }}</td><td>{{ $sesion->ficha?->code ?: 'Sin ficha' }}</td><td>{{ $sesion->actividad?->nombre ?: 'Actividad eliminada' }}</td><td>{{ $nombreInstructor ?: 'Sin instructor' }}</td>
                    <td>
                        @if($registro?->estado === 'Registrada')
                            <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                <i class="fas fa-check-circle me-1"></i>Asistencia registrada
                            </span>
                            @if($sesion->estado === 'Finalizada')
                                <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                    <i class="fas fa-check-circle me-1"></i>Actividad finalizada
                                </span>
                            @endif
                        @elseif($registro?->estado === 'Anulada')
                            <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">Anulada</span>
                        @elseif($vencida)
                            <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">Sin asistencia registrada</span>
                        @else
                            <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">Pendiente</span>
                        @endif
                    </td>
                    <td>
                        <div class="asistencia-row-actions">
                        @if($registro?->estado === 'Registrada')
                            <button type="button" class="btn asistencia-row-action asistencia-row-action--view" data-bs-toggle="modal" data-bs-target="#detalleAsistencia{{ $registro->id }}" title="Ver detalle" aria-label="Ver detalle"><i class="fas fa-eye"></i></button>
                            <button type="button" class="btn asistencia-row-action asistencia-row-action--edit" data-bs-toggle="modal" data-bs-target="#formAsistencia{{ $sesion->id_cronograma }}" title="Editar asistencia" aria-label="Editar asistencia"><i class="fas fa-pen"></i></button>
                            <button type="button" class="btn asistencia-row-action asistencia-row-action--history" data-bs-toggle="modal" data-bs-target="#historialAsistencia{{ $registro->id }}" title="Historial" aria-label="Historial"><i class="fas fa-clock-rotate-left"></i></button>
                            @if(in_array($sesion->estado, ['Pendiente', 'En proceso'], true))
                                <form method="POST" action="{{ route('controlecp.administrador.asistencia.finalizar-actividad', $sesion->id_cronograma) }}" onsubmit="return confirm('¿Finalizar esta actividad para la ficha {{ $sesion->ficha?->code }}? Su avance se actualizará en Certificados.')">
                                    @csrf<button type="submit" class="btn asistencia-row-action asistencia-row-action--finish" title="Finalizar actividad para esta ficha" aria-label="Finalizar actividad para esta ficha"><i class="fas fa-check" aria-hidden="true"></i></button>
                                </form>
                            @endif
                        @elseif($sesion->actividad)
                            <button type="button" class="btn asistencia-row-action asistencia-row-action--register" data-bs-toggle="modal" data-bs-target="#formAsistencia{{ $sesion->id_cronograma }}" title="Registrar asistencia"><i class="fas fa-clipboard-check me-1"></i>Registrar</button>
                        @endif
                        </div>
                    </td>
                </tr>
            @empty<tr><td colspan="6" class="text-center text-muted py-4">No hay actividades programadas.</td></tr>@endforelse
            </tbody>
        </table></div>
        <div class="card-footer bg-white border-0">
            @if ($cronogramas->hasPages())
                <nav aria-label="Paginación de asistencias">
                    <ul class="pagination justify-content-center">
                        @if ($cronogramas->onFirstPage())
                            <li class="page-item disabled"><span class="page-link" aria-hidden="true">&lsaquo;</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $cronogramas->previousPageUrl() }}" aria-label="Página anterior">&lsaquo;</a></li>
                        @endif
                        @php
                                $ultimaPaginaMostrada = 0;
                            @endphp
                        @for ($pagina = 1; $pagina <= $cronogramas->lastPage(); $pagina++)
                            @if ($pagina === 1 || $pagina === $cronogramas->lastPage() || abs($pagina - $cronogramas->currentPage()) <= 1)
                                @if ($ultimaPaginaMostrada > 0 && $pagina > $ultimaPaginaMostrada + 1)
                                    <li class="page-item disabled"><span class="page-link">…</span></li>
                                @endif
                                <li class="page-item {{ $pagina === $cronogramas->currentPage() ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $cronogramas->url($pagina) }}" @if($pagina === $cronogramas->currentPage()) aria-current="page" @endif>{{ $pagina }}</a>
                                </li>
                                @php
                                    $ultimaPaginaMostrada = $pagina;
                                @endphp
                            @endif
                        @endfor
                        @if ($cronogramas->hasMorePages())
                            <li class="page-item"><a class="page-link" href="{{ $cronogramas->nextPageUrl() }}" aria-label="Página siguiente">&rsaquo;</a></li>
                        @else
                            <li class="page-item disabled"><span class="page-link" aria-hidden="true">&rsaquo;</span></li>
                        @endif
                    </ul>
                </nav>
            @endif
        </div>
    </div>
</div>

@foreach($cronogramas as $sesion)
    @php
        $registro = $sesion->asistencia;
        $editando = $registro?->estado === 'Registrada';
        $detalles = $registro?->detalles?->keyBy('aprendiz_id') ?: collect();
        $aprendices = $aprendicesPorFicha->get($sesion->id_ficha, collect());
    @endphp
    @if($sesion->actividad)
        <div class="modal fade {{ $editando ? 'asistencia-admin-edit-modal' : '' }}" id="formAsistencia{{ $sesion->id_cronograma }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable asistencia-modal-dialog"><div class="modal-content {{ $editando ? '' : 'asistencia-register-modal' }}">
                @if($editando)
                    <div class="modal-header">
                        <h5 class="modal-title">Editar asistencia</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                @else
                    <div class="modal-header">
                        <h2 class="modal-title h5 fw-bold">
                            <span class="register-title-icon"><i class="fas fa-clipboard-check" aria-hidden="true"></i></span>
                            <span class="register-title-copy">Registrar asistencia · {{ $sesion->actividad->nombre }}<small>Ficha {{ $sesion->ficha?->code }} · {{ optional($sesion->fecha)->format('d/m/Y') }}</small></span>
                        </h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                @endif
                <form id="{{ $editando ? 'formEditarAdminAsistencia' . $registro->id : 'formRegistrarAdminAsistencia' . $sesion->id_cronograma }}" class="{{ $editando ? 'asistencia-admin-edit-form' : 'asistencia-modal-form' }}" method="POST" action="{{ $editando ? route('controlecp.administrador.asistencia.update', $registro->id) : route('controlecp.administrador.asistencia.store', $sesion->id_cronograma) }}" enctype="multipart/form-data">
                    @csrf
                    @if($editando)
                        @method('PUT')
                        <div class="modal-body asistencia-admin-edit-body">
                            <div class="asistencia-admin-edit-roster">
                                <table class="table table-hover align-middle asistencia-admin-edit-table">
                                    <thead><tr><th>Documento</th><th>Aprendiz</th><th>Presente</th><th>Excusa</th><th>Motivo y soporte</th></tr></thead>
                                    <tbody>
                                    @foreach($aprendices as $aprendiz)
                                        @php
                                            $detalle = $detalles->get($aprendiz->id);
                                            $presente = old('asistencia.' . $aprendiz->id . '.presente', $detalle?->presente ? '1' : '0');
                                            $justificada = old('asistencia.' . $aprendiz->id . '.justificada', $detalle?->justificada ? '1' : '0');
                                        @endphp
                                        <tr data-aprendiz-editar="{{ $aprendiz->id }}">
                                            <td class="font-monospace">{{ $aprendiz->person?->document_number ?? $aprendiz->person_id }}</td>
                                            <td>{{ $aprendiz->person?->full_name ?? 'Aprendiz' }}</td>
                                            <td class="text-center">
                                                <input type="hidden" name="asistencia[{{ $aprendiz->id }}][presente]" value="0">
                                                <input type="checkbox" class="form-check-input asistencia-presente-editar" name="asistencia[{{ $aprendiz->id }}][presente]" value="1" @checked((string) $presente === '1')>
                                            </td>
                                            <td class="text-center">
                                                <input type="hidden" name="asistencia[{{ $aprendiz->id }}][justificada]" value="0">
                                                <input type="checkbox" class="form-check-input asistencia-justificada-editar" name="asistencia[{{ $aprendiz->id }}][justificada]" value="1" @checked((string) $justificada === '1')>
                                            </td>
                                            <td>
                                                <div class="asistencia-excusa-detalles-editar {{ (string) $justificada === '1' ? '' : 'd-none' }}">
                                                    <button type="button" class="btn asistencia-admin-edit-trigger" data-excusa-modal="excusaModalAdminEditar{{ $registro->id }}_{{ $aprendiz->id }}" data-parent-modal="formAsistencia{{ $sesion->id_cronograma }}">
                                                        <i class="fas fa-folder-open" aria-hidden="true"></i>Motivo y soporte
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="asistencia-admin-correction">
                                <label class="form-label fw-semibold" for="motivoEdicionAdmin{{ $registro->id }}">Motivo de la corrección *</label>
                                <textarea class="form-control" id="motivoEdicionAdmin{{ $registro->id }}" name="motivo" maxlength="1000" required>{{ old('motivo') }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer asistencia-admin-edit-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-save"><i class="fas fa-floppy-disk me-2" aria-hidden="true"></i>Guardar cambios</button>
                        </div>
                    @else
                        <div class="modal-body">
                            <div class="register-hint"><i class="fas fa-circle-info" aria-hidden="true"></i><span>Todos los aprendices aparecen presentes por defecto. Desmarca la asistencia para registrar una ausencia y, si corresponde, agrega la excusa y su soporte.</span></div>
                            <div class="register-roster-wrap"><table class="table table-hover align-middle register-roster-table"><thead><tr><th>Documento</th><th>Aprendiz</th><th class="text-center">Presente</th><th class="text-center">Excusa</th><th>Motivo y soporte de excusa</th></tr></thead><tbody>
                            @forelse($aprendices as $aprendiz)
                                @php $detalle = $detalles->get($aprendiz->id); @endphp
                                <tr>
                                    <td class="font-monospace">{{ $aprendiz->person?->document_number }}</td>
                                    <td>{{ $aprendiz->person?->full_name }}</td>
                                    <td class="text-center">
                                        <input type="hidden" name="asistencia[{{ $aprendiz->id }}][presente]" value="0">
                                        <input type="checkbox" class="form-check-input asistencia-presente" name="asistencia[{{ $aprendiz->id }}][presente]" value="1" @checked($detalle?->presente ?? true)>
                                    </td>
                                    <td class="text-center">
                                        <input type="hidden" name="asistencia[{{ $aprendiz->id }}][justificada]" value="0">
                                        <input type="checkbox" class="form-check-input asistencia-justificada" name="asistencia[{{ $aprendiz->id }}][justificada]" value="1" @checked($detalle?->justificada ?? false) @disabled($detalle?->presente ?? true)>
                                    </td>
                                    <td>
                                        <div class="asistencia-excusa-detalles {{ $detalle?->justificada ? '' : 'd-none' }}">
                                            <input class="form-control form-control-sm asistencia-observacion mb-2" name="asistencia[{{ $aprendiz->id }}][observacion]" value="{{ $detalle?->observacion }}" maxlength="1000" placeholder="Escribe el motivo de la excusa">
                                            <input type="file" class="form-control form-control-sm asistencia-excusa-imagen" name="asistencia[{{ $aprendiz->id }}][excusa_imagen]" accept="image/jpeg,image/png" data-has-existing-image="{{ $detalle?->excusa_imagen ? 'true' : 'false' }}" @required($detalle?->justificada && !$detalle?->excusa_imagen)>
                                            @if($detalle?->excusa_imagen)
                                                <a class="small d-inline-block mt-1" href="{{ asset('storage/' . $detalle->excusa_imagen) }}" target="_blank" rel="noopener">Ver imagen de soporte</a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty<tr><td colspan="5" class="text-center text-muted">La ficha no tiene aprendices.</td></tr>@endforelse
                            </tbody></table></div>
                            <div class="row g-3 mt-2"><div class="col-md-6"><div class="register-evidence"><label class="form-label">Foto opcional</label><input type="file" name="evidencia_foto" class="form-control form-control-sm" accept="image/jpeg,image/png"></div></div><div class="col-md-6"><div class="register-evidence"><label class="form-label">Enlace de video opcional</label><input type="url" name="evidencia_enlace" class="form-control form-control-sm" placeholder="https://..."></div></div></div>
                        </div>
                        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button><button class="btn register-submit"><i class="fas fa-floppy-disk me-2" aria-hidden="true"></i>Guardar asistencia</button></div>
                    @endif
                </form>
            </div></div>
        </div>
        @if($editando)
            @foreach($aprendices as $aprendiz)
                @php
                    $detalle = $detalles->get($aprendiz->id);
                @endphp
                <div class="modal fade asistencia-admin-excuse-modal" id="excusaModalAdminEditar{{ $registro->id }}_{{ $aprendiz->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Motivo y soporte · {{ $aprendiz->person?->full_name ?? 'Aprendiz' }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <div class="asistencia-admin-excuse-editor">
                                    <label class="form-label fw-semibold" for="motivoExcusaAdmin{{ $registro->id }}_{{ $aprendiz->id }}">Motivo de la excusa</label>
                                    <div class="asistencia-admin-excuse-input-row">
                                        <input id="motivoExcusaAdmin{{ $registro->id }}_{{ $aprendiz->id }}" type="text" class="form-control" form="formEditarAdminAsistencia{{ $registro->id }}" name="asistencia[{{ $aprendiz->id }}][observacion]" maxlength="1000" value="{{ old('asistencia.' . $aprendiz->id . '.observacion', $detalle?->observacion ?? '') }}" placeholder="Escribe el motivo de la excusa">
                                        <label class="asistencia-admin-upload" for="excusaFileAdmin{{ $registro->id }}_{{ $aprendiz->id }}" title="Subir evidencia" aria-label="Subir evidencia"><i class="fas fa-folder-open" aria-hidden="true"></i></label>
                                        <input id="excusaFileAdmin{{ $registro->id }}_{{ $aprendiz->id }}" type="file" class="asistencia-admin-excuse-file" form="formEditarAdminAsistencia{{ $registro->id }}" name="asistencia[{{ $aprendiz->id }}][excusa_imagen]" accept="image/jpeg,image/png">
                                    </div>
                                    @if($detalle?->excusa_imagen)
                                        <small class="text-primary"><i class="fas fa-circle-check me-1" aria-hidden="true"></i>Ya hay una evidencia guardada; se conservará si mantienes la excusa.</small>
                                    @endif
                                    <small class="text-muted asistencia-admin-excuse-file-status" data-default-text="Adjunta una imagen JPG o PNG como evidencia de la excusa.">Adjunta una imagen JPG o PNG como evidencia de la excusa.</small>
                                </div>
                            </div>
                            <div class="modal-footer"><button type="button" class="btn btn-success rounded-pill px-4" data-bs-dismiss="modal">Listo</button></div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    @endif
    @if($registro)
        <div class="modal fade asistencia-detail-modal" id="detalleAsistencia{{ $registro->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Detalle de asistencia</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
                <div class="modal-body"><div class="table-responsive"><table class="table align-middle asistencia-detail-table"><thead><tr><th>Documento</th><th>Aprendiz</th><th>Estado</th><th>Observación</th><th>Soporte</th></tr></thead><tbody>
                @foreach($detalles as $aprendizId => $detalle)
                    @php $aprendiz = $aprendices->firstWhere('id', $aprendizId); @endphp
                    <tr>
                        <td class="font-monospace">{{ $aprendiz?->person?->document_number ?? $aprendizId }}</td>
                        <td>{{ $aprendiz?->person?->full_name ?? 'Aprendiz' }}</td>
                        <td><span class="badge rounded-pill {{ $detalle->presente ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $detalle->presente ? 'Presente' : 'Ausente' }}</span></td>
                        <td>{{ $detalle->observacion ?: '—' }}</td>
                        <td>
                            @if($detalle->excusa_imagen)
                                <a class="asistencia-support-link" href="{{ asset('storage/' . $detalle->excusa_imagen) }}" target="_blank" rel="noopener" title="Ver imagen de soporte" aria-label="Ver imagen de soporte"><i class="fas fa-folder-open" aria-hidden="true"></i></a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody></table></div></div>
            </div></div>
        </div>
        <div class="modal fade asistencia-detail-modal" id="historialAsistencia{{ $registro->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
                <div class="modal-header"><h5 class="modal-title"><i class="fas fa-clock-rotate-left me-2" aria-hidden="true"></i>Historial de cambios</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
                <div class="modal-body"><div class="table-responsive"><table class="table align-middle asistencia-detail-table"><thead><tr><th>Fecha</th><th>Acción</th><th>Usuario</th><th>Motivo</th></tr></thead><tbody>
                @forelse($historialPorAsistencia->get($registro->id, collect()) as $item)<tr><td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}</td><td>{{ $item->accion }}</td><td>{{ $item->nickname }}</td><td>{{ $item->motivo ?: '—' }}</td></tr>@empty<tr><td colspan="4" class="text-center text-muted">Sin historial.</td></tr>@endforelse
                </tbody></table></div></div>
            </div></div>
        </div>
    @endif
@endforeach
@endsection

@push('scripts')
<script>
(() => {
    const formNueva = document.getElementById('nuevaAsistenciaForm');
    const fichaBuscar = document.getElementById('nuevaFichaBuscar');
    const fichaNueva = document.getElementById('nuevaFicha');
    const fichaResultados = document.getElementById('nuevaFichaResultados');
    const aprendicesBody = document.getElementById('nuevosAprendices');
    const aprendicesEstado = document.getElementById('aprendicesEstado');
    const guardarNueva = document.getElementById('guardarNuevaAsistencia');
    const actividadNueva = document.getElementById('nuevaActividad');
    const actividadesEstado = document.getElementById('actividadesEstado');
    const fichasCatalogo = @json($fichas->map(fn ($ficha) => ['id' => $ficha->id, 'code' => $ficha->code])->values());
    let solicitudAprendices;
    let solicitudActividades;
    let aprendicesListos = false;

    const actualizarBotonGuardar = () => {
        guardarNueva.disabled = !aprendicesListos || !actividadNueva.value;
    };

    const ocultarFichas = () => {
        fichaResultados.classList.remove('is-visible');
        fichaBuscar.setAttribute('aria-expanded', 'false');
    };

    const cargarActividades = async fichaId => {
        solicitudActividades?.abort();
        actividadNueva.replaceChildren(new Option('Cargando actividades...', ''));
        actividadNueva.disabled = true;
        actividadesEstado.textContent = 'Buscando actividades programadas para esta ficha...';
        if (!fichaId) {
            actividadNueva.replaceChildren(new Option('Primero selecciona una ficha', ''));
            actividadesEstado.textContent = 'Selecciona una ficha para ver sus actividades programadas.';
            return;
        }

        const fichaIdSeleccionada = fichaId;
        solicitudActividades = new AbortController();
        try {
            const response = await fetch(`${formNueva.dataset.actividadesUrl}/${encodeURIComponent(fichaIdSeleccionada)}/actividades`, {
                headers: { Accept: 'application/json' },
                signal: solicitudActividades.signal,
            });
            if (!response.ok) throw new Error('No fue posible consultar las actividades programadas.');
            const resultado = await response.json();
            if (fichaNueva.value !== fichaIdSeleccionada) return;

            actividadNueva.replaceChildren(new Option('Selecciona una actividad programada', ''));
            if (!resultado.data.length) {
                actividadNueva.replaceChildren(new Option('No hay actividades disponibles', ''));
                actividadesEstado.textContent = 'Esta ficha no tiene actividades vencidas y pendientes de asistencia.';
                return;
            }

            resultado.data.forEach(actividad => {
                actividadNueva.add(new Option(`${actividad.nombre} · ${actividad.fecha}`, actividad.id));
            });
            actividadNueva.disabled = false;
            actividadesEstado.textContent = `${resultado.data.length} actividad(es) programadas disponibles.`;
        } catch (error) {
            if (error.name === 'AbortError') return;
            actividadNueva.replaceChildren(new Option('No se pudieron cargar las actividades', ''));
            actividadesEstado.textContent = 'No se pudieron cargar las actividades. Intenta nuevamente.';
        }
    };

    const cargarAprendices = async fichaId => {
        solicitudAprendices?.abort();
        aprendicesBody.replaceChildren();
        aprendicesListos = false;
        actualizarBotonGuardar();
        if (!fichaId) {
            aprendicesEstado.textContent = 'Selecciona una ficha para cargar sus aprendices.';
            return;
        }

        const fichaIdSeleccionada = fichaId;
        solicitudAprendices = new AbortController();
        aprendicesEstado.textContent = 'Cargando aprendices...';
        try {
            const response = await fetch(`${formNueva.dataset.aprendicesUrl}/${encodeURIComponent(fichaIdSeleccionada)}/aprendices`, {
                headers: { Accept: 'application/json' },
                signal: solicitudAprendices.signal,
            });
            if (!response.ok) throw new Error('No fue posible consultar los aprendices.');
            const resultado = await response.json();
            if (fichaNueva.value !== fichaIdSeleccionada) return;

            if (!resultado.data.length) {
                aprendicesEstado.textContent = 'La ficha seleccionada no tiene aprendices activos.';
                return;
            }

            resultado.data.forEach(aprendiz => {
                const fila = document.createElement('tr');
                agregarCelda(fila, aprendiz.documento, 'font-monospace');
                agregarCelda(fila, aprendiz.nombre);

                const presenteCell = document.createElement('td');
                presenteCell.className = 'text-center';
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = `asistencia[${aprendiz.id}][presente]`;
                hidden.value = '0';
                const presente = document.createElement('input');
                presente.type = 'checkbox';
                presente.className = 'form-check-input';
                presente.classList.add('asistencia-presente');
                presente.name = `asistencia[${aprendiz.id}][presente]`;
                presente.value = '1';
                presente.checked = true;
                presenteCell.append(hidden, presente);
                fila.append(presenteCell);

                const excusaCell = document.createElement('td');
                excusaCell.className = 'text-center';
                const excusaHidden = document.createElement('input');
                excusaHidden.type = 'hidden';
                excusaHidden.name = `asistencia[${aprendiz.id}][justificada]`;
                excusaHidden.value = '0';
                const excusa = document.createElement('input');
                excusa.type = 'checkbox';
                excusa.className = 'form-check-input asistencia-justificada';
                excusa.name = `asistencia[${aprendiz.id}][justificada]`;
                excusa.value = '1';
                excusa.disabled = presente.checked;
                excusaCell.append(excusaHidden, excusa);
                fila.append(excusaCell);

                const observacionCell = document.createElement('td');
                const detallesExcusa = document.createElement('div');
                detallesExcusa.className = 'asistencia-excusa-detalles d-none';
                const observacion = document.createElement('input');
                observacion.type = 'text';
                observacion.className = 'form-control asistencia-observacion';
                observacion.name = `asistencia[${aprendiz.id}][observacion]`;
                observacion.maxLength = 1000;
                observacion.placeholder = 'Escribe el motivo de la excusa';
                observacion.classList.add('mb-2');
                const imagen = document.createElement('input');
                imagen.type = 'file';
                imagen.className = 'form-control asistencia-excusa-imagen';
                imagen.name = `asistencia[${aprendiz.id}][excusa_imagen]`;
                imagen.accept = 'image/jpeg,image/png';
                detallesExcusa.append(observacion, imagen);
                observacionCell.append(detallesExcusa);
                fila.append(observacionCell);
                aprendicesBody.append(fila);
            });

            aprendicesBody.querySelectorAll('tr').forEach(actualizarCamposExcusa);
            aprendicesEstado.textContent = `${resultado.data.length} aprendiz(es) cargados.`;
            aprendicesListos = true;
            actualizarBotonGuardar();
        } catch (error) {
            if (error.name === 'AbortError') return;
            aprendicesEstado.textContent = 'No se pudieron cargar los aprendices. Intenta nuevamente.';
        }
    };

    const agregarCelda = (fila, contenido, clase = '') => {
        const celda = document.createElement('td');
        celda.textContent = contenido || '—';
        if (clase) celda.className = clase;
        fila.append(celda);
        return celda;
    };

    function actualizarCamposExcusa(fila) {
        const presente = fila.querySelector('.asistencia-presente');
        const excusa = fila.querySelector('.asistencia-justificada');
        const observacion = fila.querySelector('.asistencia-observacion');
        const detallesExcusa = fila.querySelector('.asistencia-excusa-detalles');
        const imagen = fila.querySelector('.asistencia-excusa-imagen');
        if (!presente || !excusa || !observacion || !detallesExcusa || !imagen) return;
        excusa.disabled = presente.checked;
        if (presente.checked) {
            excusa.checked = false;
        }
        detallesExcusa.classList.toggle('d-none', !excusa.checked);
        observacion.required = excusa.checked;
        imagen.required = excusa.checked && imagen.dataset.hasExistingImage !== 'true';
    }

    document.querySelectorAll('#nuevaAsistenciaForm tbody tr, [id^="formAsistencia"] tbody tr')
        .forEach(actualizarCamposExcusa);

    document.addEventListener('change', event => {
        if (event.target.matches('.asistencia-justificada')) {
            const excusa = event.target;
            if (excusa.checked && !window.confirm('¿Confirmas que esta ausencia tiene excusa? Deberás escribir el motivo y adjuntar una imagen de soporte.')) {
                excusa.checked = false;
            }
            actualizarCamposExcusa(excusa.closest('tr'));
        } else if (event.target.matches('.asistencia-presente')) {
            actualizarCamposExcusa(event.target.closest('tr'));
        }
    });

    const mostrarFichas = (limpiarSeleccion = true) => {
        const termino = fichaBuscar.value.trim().toLocaleLowerCase();
        fichaResultados.replaceChildren();
        if (limpiarSeleccion) {
            fichaNueva.value = '';
            fichaBuscar.setCustomValidity('Selecciona una ficha del catálogo.');
            cargarAprendices('');
            cargarActividades('');
        }

        if (!termino) {
            ocultarFichas();
            return;
        }

        const coincidencias = fichasCatalogo
            .filter(ficha => String(ficha.code).toLocaleLowerCase().includes(termino))
            .slice(0, 8);

        if (!coincidencias.length) {
            const vacio = document.createElement('div');
            vacio.className = 'ficha-search-empty';
            vacio.textContent = 'No hay fichas del módulo Fichas que coincidan.';
            fichaResultados.append(vacio);
        } else {
            coincidencias.forEach(ficha => {
                const opcion = document.createElement('button');
                opcion.type = 'button';
                opcion.className = 'ficha-search-option';
                opcion.setAttribute('role', 'option');
                opcion.textContent = ficha.code;
                opcion.addEventListener('click', () => {
                    fichaBuscar.value = ficha.code;
                    fichaNueva.value = ficha.id;
                    fichaBuscar.setCustomValidity('');
                    ocultarFichas();
                    cargarAprendices(String(ficha.id));
                    cargarActividades(String(ficha.id));
                });
                fichaResultados.append(opcion);
            });
        }

        fichaResultados.classList.add('is-visible');
        fichaBuscar.setAttribute('aria-expanded', 'true');
    };

    fichaBuscar.addEventListener('input', mostrarFichas);
    fichaBuscar.addEventListener('focus', () => mostrarFichas(false));
    fichaBuscar.addEventListener('keydown', event => {
        if (event.key === 'Escape') ocultarFichas();
        if (event.key === 'Enter' && fichaResultados.querySelector('.ficha-search-option')) {
            event.preventDefault();
            fichaResultados.querySelector('.ficha-search-option').click();
        }
    });
    document.addEventListener('click', event => {
        if (!fichaResultados.parentElement.contains(event.target)) ocultarFichas();
    });
    actividadNueva.addEventListener('change', actualizarBotonGuardar);

})();

function solicitarMotivo(form, titulo) {
    const motivo = window.prompt(titulo + '. Indica el motivo:');
    if (!motivo || !motivo.trim()) return false;
    form.querySelector('[name="motivo"]').value = motivo.trim();
    return true;
}

function actualizarFilaEdicionAdmin(fila) {
    const presente = fila.querySelector('.asistencia-presente-editar');
    const excusa = fila.querySelector('.asistencia-justificada-editar');
    const detalles = fila.querySelector('.asistencia-excusa-detalles-editar');
    if (!presente || !excusa || !detalles) return;
    detalles.classList.toggle('d-none', !excusa.checked);
}

document.querySelectorAll('tr[data-aprendiz-editar]').forEach(actualizarFilaEdicionAdmin);

document.addEventListener('change', event => {
    if (event.target.matches('.asistencia-presente-editar, .asistencia-justificada-editar')) {
        const fila = event.target.closest('tr[data-aprendiz-editar]');
        if (event.target.matches('.asistencia-presente-editar') && event.target.checked) {
            const excusa = fila?.querySelector('.asistencia-justificada-editar');
            if (excusa) excusa.checked = false;
        } else if (event.target.matches('.asistencia-justificada-editar') && event.target.checked) {
            const presente = fila?.querySelector('.asistencia-presente-editar');
            if (presente) presente.checked = false;
        }
        if (fila) actualizarFilaEdicionAdmin(fila);
    }

    if (event.target.matches('.asistencia-admin-excuse-file')) {
        const status = event.target.closest('.asistencia-admin-excuse-modal')?.querySelector('.asistencia-admin-excuse-file-status');
        if (status) {
            status.textContent = event.target.files?.[0]
                ? 'Evidencia seleccionada: ' + event.target.files[0].name
                : status.dataset.defaultText;
            status.classList.toggle('text-primary', Boolean(event.target.files?.[0]));
            status.classList.toggle('text-muted', !event.target.files?.[0]);
        }
    }
});

document.addEventListener('click', event => {
    const trigger = event.target.closest('.asistencia-admin-edit-trigger');
    if (!trigger || !window.bootstrap?.Modal) return;

    const parent = document.getElementById(trigger.dataset.parentModal);
    const excuseModal = document.getElementById(trigger.dataset.excusaModal);
    if (!parent || !excuseModal) return;

    excuseModal.dataset.parentModal = parent.id;
    parent.addEventListener('hidden.bs.modal', () => {
        window.bootstrap.Modal.getOrCreateInstance(excuseModal).show();
    }, { once: true });
    window.bootstrap.Modal.getOrCreateInstance(parent).hide();
});

document.addEventListener('hidden.bs.modal', event => {
    const excuseModal = event.target;
    if (!excuseModal.classList.contains('asistencia-admin-excuse-modal') || !excuseModal.dataset.parentModal) return;

    const parent = document.getElementById(excuseModal.dataset.parentModal);
    if (parent) window.bootstrap.Modal.getOrCreateInstance(parent).show();
});
</script>
@endpush
