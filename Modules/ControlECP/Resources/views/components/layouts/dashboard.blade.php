{{-- Vista: Distribución común del panel, con menú, encabezado y contenido del perfil. --}}
@extends('controlecp::components.layouts.master')

{{-- Contenido principal que organiza la interfaz específica de esta vista. --}}
@section('content')

    @push('styles')
        @if(auth()->user()->hasRole('controlecp.admin'))
            <style>
                .admin-dashboard-content > .container-fluid > :first-child:not(.toast-container):not(.dash-welcome),
                .admin-dashboard-content > .container-fluid > .toast-container + div {
                    background: var(--sena-navy);
                    border-radius: 24px;
                    padding: 1.25rem 1.5rem;
                    border-bottom: 0 !important;
                }

                .admin-dashboard-content > .container-fluid > :first-child:not(.toast-container):not(.dash-welcome) h1,
                .admin-dashboard-content > .container-fluid > :first-child:not(.toast-container):not(.dash-welcome) h2,
                .admin-dashboard-content > .container-fluid > :first-child:not(.toast-container):not(.dash-welcome) h3,
                .admin-dashboard-content > .container-fluid > .toast-container + div h1,
                .admin-dashboard-content > .container-fluid > .toast-container + div h2,
                .admin-dashboard-content > .container-fluid > .toast-container + div h3 {
                    color: #fff !important;
                }

                .admin-dashboard-content > .container-fluid > :first-child:not(.toast-container):not(.dash-welcome) .text-muted,
                .admin-dashboard-content > .container-fluid > .toast-container + div .text-muted {
                    color: #c7d4da !important;
                }

                .admin-dashboard-content > .container-fluid > :first-child:not(.toast-container):not(.dash-welcome) .btn-outline-secondary,
                .admin-dashboard-content > .container-fluid > .toast-container + div .btn-outline-secondary {
                    color: #fff;
                    border-color: rgba(255, 255, 255, .65);
                }

                .admin-dashboard-content :is(.card, .ecp-card, .notif-card, .encuesta-card, .tabulacion-card, .poe-card):has(form[method="GET"]),
                .admin-dashboard-content .report-toolbar {
                    background: var(--sena-navy) !important;
                    border: 0 !important;
                    border-radius: 18px !important;
                    color: #fff;
                }

                .admin-dashboard-content :is(.card, .ecp-card, .notif-card, .encuesta-card, .tabulacion-card, .poe-card):has(form[method="GET"]) label,
                .admin-dashboard-content .report-toolbar label {
                    color: #fff !important;
                }

                .admin-dashboard-content :is(.card, .ecp-card, .notif-card, .encuesta-card, .tabulacion-card, .poe-card):has(form[method="GET"]) .form-control,
                .admin-dashboard-content :is(.card, .ecp-card, .notif-card, .encuesta-card, .tabulacion-card, .poe-card):has(form[method="GET"]) .form-select,
                .admin-dashboard-content :is(.card, .ecp-card, .notif-card, .encuesta-card, .tabulacion-card, .poe-card):has(form[method="GET"]) .input-group-text,
                .admin-dashboard-content .report-toolbar input,
                .admin-dashboard-content .report-toolbar select {
                    background-color: #fff !important;
                    border-color: #fff !important;
                    color: var(--sena-navy) !important;
                }

                .admin-dashboard-content :is(.card, .ecp-card, .notif-card, .encuesta-card, .tabulacion-card, .poe-card):has(form[method="GET"]) .form-control:focus,
                .admin-dashboard-content :is(.card, .ecp-card, .notif-card, .encuesta-card, .tabulacion-card, .poe-card):has(form[method="GET"]) .form-select:focus,
                .admin-dashboard-content .report-toolbar input:focus,
                .admin-dashboard-content .report-toolbar select:focus {
                    border-color: var(--sena-green) !important;
                    box-shadow: 0 0 0 0.2rem rgba(57, 169, 0, .25) !important;
                }

                .admin-dashboard-content :is(.card, .ecp-card, .notif-card, .encuesta-card, .tabulacion-card, .poe-card):has(form[method="GET"]) button[type="submit"],
                .admin-dashboard-content :is(.card, .ecp-card, .notif-card, .encuesta-card, .tabulacion-card, .poe-card):has(form[method="GET"]) .btn-search-ficha,
                .admin-dashboard-content .report-toolbar .btn-generate {
                    background-color: var(--sena-green) !important;
                    border-color: var(--sena-green) !important;
                    color: #fff !important;
                }

                .admin-dashboard-content :is(.card, .ecp-card, .notif-card, .encuesta-card, .tabulacion-card, .poe-card):has(form[method="GET"]) button[type="submit"]:hover,
                .admin-dashboard-content :is(.card, .ecp-card, .notif-card, .encuesta-card, .tabulacion-card, .poe-card):has(form[method="GET"]) .btn-search-ficha:hover,
                .admin-dashboard-content .report-toolbar .btn-generate:hover {
                    background-color: var(--sena-green-hover) !important;
                    border-color: var(--sena-green-hover) !important;
                }

                .admin-dashboard-content form[method="GET"] .form-control,
                .admin-dashboard-content form[method="GET"] .form-select,
                .admin-dashboard-content form[method="GET"] .input-group-text {
                    border-color: #fff;
                    color: var(--sena-navy);
                }

                .admin-dashboard-content form[method="GET"] button[type="submit"] {
                    background-color: var(--sena-green);
                    border-color: var(--sena-green);
                    color: #fff;
                }

                .admin-dashboard-content form[method="GET"] button[type="submit"]:hover {
                    background-color: var(--sena-green-hover);
                    border-color: var(--sena-green-hover);
                }
            </style>
        @endif
        @if(auth()->user()->hasRole('controlecp.admin') && !request()->routeIs('controlecp.administrador.asistencia*'))
            <style>
                .admin-dashboard-content .modal .modal-content {
                    overflow: hidden;
                    border: 1px solid #e3eaed !important;
                    border-radius: 20px !important;
                    background: #fff;
                    box-shadow: 0 24px 60px rgba(0, 26, 41, .2) !important;
                }

                .admin-dashboard-content .modal .modal-header {
                    align-items: flex-start;
                    gap: .75rem;
                    padding: 1.1rem 1.35rem !important;
                    border: 0 !important;
                    border-bottom: 3px solid var(--sena-green) !important;
                    background: linear-gradient(125deg, #001a29, #00324d) !important;
                    color: #fff !important;
                }

                .admin-dashboard-content .modal .modal-header .modal-title {
                    margin: 0;
                    font-size: 1.12rem;
                    line-height: 1.35;
                    letter-spacing: -.015em;
                }

                .admin-dashboard-content .modal .modal-header .modal-title,
                .admin-dashboard-content .modal .modal-header .text-dark,
                .admin-dashboard-content .modal .modal-header .text-white {
                    color: #fff !important;
                    font-weight: 800;
                }

                .admin-dashboard-content .modal .modal-header .rounded-circle {
                    display: inline-flex;
                    flex: 0 0 42px;
                    align-items: center;
                    justify-content: center;
                    width: 42px;
                    height: 42px;
                    border: 1px solid rgba(255, 255, 255, .24);
                    background: rgba(255, 255, 255, .12);
                    color: #fff;
                }

                .admin-dashboard-content .modal .modal-header p,
                .admin-dashboard-content .modal .modal-header .text-muted {
                    color: #c7d4da !important;
                }

                .admin-dashboard-content .modal .modal-header .btn-close {
                    flex: 0 0 auto;
                    padding: .65rem;
                    border-radius: 50%;
                    background-color: rgba(255, 255, 255, .14);
                    filter: invert(1) grayscale(100%) brightness(200%);
                    opacity: .9;
                }

                .admin-dashboard-content .modal .modal-body {
                    padding: 1.25rem 1.35rem !important;
                    background: #f7fafb;
                    color: #334155;
                    line-height: 1.55;
                }

                .admin-dashboard-content .modal .modal-body h1,
                .admin-dashboard-content .modal .modal-body h2,
                .admin-dashboard-content .modal .modal-body h3,
                .admin-dashboard-content .modal .modal-body h4,
                .admin-dashboard-content .modal .modal-body h5 {
                    color: #001a29;
                }

                .admin-dashboard-content .modal .modal-footer {
                    gap: .55rem;
                    padding: .9rem 1.35rem 1.15rem !important;
                    border-top: 1px solid #e8eef2 !important;
                    background: #fff !important;
                }

                .admin-dashboard-content .modal .form-label {
                    display: inline-block;
                    margin-bottom: .4rem;
                    color: #23465d;
                    font-size: .84rem;
                    font-weight: 700;
                }

                .admin-dashboard-content .modal .form-control,
                .admin-dashboard-content .modal .form-select {
                    min-height: 42px;
                    border: 1px solid #dce5eb;
                    border-radius: 10px;
                    background-color: #fff;
                    color: #1f2d3d;
                    box-shadow: none;
                    transition: border-color .16s ease, box-shadow .16s ease;
                }

                .admin-dashboard-content .modal .form-control:hover,
                .admin-dashboard-content .modal .form-select:hover {
                    border-color: #b7c9d2;
                }

                .admin-dashboard-content .modal textarea.form-control {
                    min-height: auto;
                }

                .admin-dashboard-content .modal .form-text,
                .admin-dashboard-content .modal .text-muted {
                    color: #64748b !important;
                }

                .admin-dashboard-content .modal .input-group-text {
                    border-color: #dce5eb;
                    background: #f1f5f7;
                    color: #475569;
                }

                .admin-dashboard-content .modal .form-control:focus,
                .admin-dashboard-content .modal .form-select:focus {
                    border-color: var(--sena-green);
                    box-shadow: 0 0 0 .2rem rgba(57, 169, 0, .14);
                }

                .admin-dashboard-content .modal .form-check-input:checked {
                    border-color: var(--sena-green);
                    background-color: var(--sena-green);
                }

                .admin-dashboard-content .modal .alert {
                    border: 1px solid rgba(0, 26, 41, .08);
                    border-radius: 12px;
                }

                .admin-dashboard-content .modal .table {
                    --bs-table-bg: #fff;
                    --bs-table-striped-bg: #f7fafb;
                    margin-bottom: 0;
                    vertical-align: middle;
                }

                .admin-dashboard-content .modal .table thead th {
                    border-bottom-color: #dce5eb;
                    background: #edf3f5;
                    color: #23465d;
                    font-size: .76rem;
                    font-weight: 800;
                    letter-spacing: .035em;
                    text-transform: uppercase;
                    white-space: nowrap;
                }

                .admin-dashboard-content .modal .table-responsive {
                    border: 1px solid #e3eaed;
                    border-radius: 12px;
                    background: #fff;
                }

                .admin-dashboard-content .modal .btn {
                    min-height: 40px;
                    border-radius: 999px;
                    font-weight: 700;
                    transition: transform .16s ease, box-shadow .16s ease, background-color .16s ease;
                }

                .admin-dashboard-content .modal .btn:not(:disabled):hover {
                    transform: translateY(-1px);
                    box-shadow: 0 5px 14px rgba(0, 26, 41, .11);
                }

                .admin-dashboard-content .modal .modal-footer .btn-primary,
                .admin-dashboard-content .modal .modal-footer .btn-success,
                .admin-dashboard-content .modal .modal-footer .btn-save,
                .admin-dashboard-content .modal .modal-footer .register-submit,
                .admin-dashboard-content .modal .modal-footer button[type="submit"]:not(.btn-danger):not(.btn-outline-danger):not(.btn-warning):not(.btn-outline-warning) {
                    border-color: var(--sena-green) !important;
                    background: var(--sena-green) !important;
                    color: #fff !important;
                }

                .admin-dashboard-content .modal .modal-footer .btn-primary:hover,
                .admin-dashboard-content .modal .modal-footer .btn-success:hover,
                .admin-dashboard-content .modal .modal-footer .btn-save:hover,
                .admin-dashboard-content .modal .modal-footer .register-submit:hover,
                .admin-dashboard-content .modal .modal-footer button[type="submit"]:not(.btn-danger):not(.btn-outline-danger):not(.btn-warning):not(.btn-outline-warning):hover {
                    border-color: var(--sena-green-hover) !important;
                    background: var(--sena-green-hover) !important;
                }

                .admin-dashboard-content .modal .btn-outline-success {
                    border-color: #d3e9c8 !important;
                    background: #eff8e9 !important;
                    color: #347d1c !important;
                }

                .admin-dashboard-content .modal .btn-outline-secondary,
                .admin-dashboard-content .modal .btn-secondary {
                    border-color: #e1e8e2 !important;
                    background: #f1f5f2 !important;
                    color: #536258 !important;
                }

                .admin-dashboard-content .modal .btn-outline-warning,
                .admin-dashboard-content .modal .btn-warning {
                    border-color: #f2dfad !important;
                    background: #fff6df !important;
                    color: #8a640d !important;
                }

                .admin-dashboard-content .modal .btn-outline-danger,
                .admin-dashboard-content .modal .btn-danger {
                    border-color: #f1d0d0 !important;
                    background: #fff0f0 !important;
                    color: #b83b3b !important;
                }

                .admin-dashboard-content .modal .btn-outline-success:hover {
                    background: #e6f3dd !important;
                }

                .admin-dashboard-content .modal .btn-outline-secondary:hover,
                .admin-dashboard-content .modal .btn-secondary:hover {
                    background: #e9efea !important;
                }

                .admin-dashboard-content .modal .btn-outline-warning:hover,
                .admin-dashboard-content .modal .btn-warning:hover {
                    background: #fff0c7 !important;
                }

                .admin-dashboard-content .modal .btn-outline-danger:hover,
                .admin-dashboard-content .modal .btn-danger:hover {
                    background: #ffe5e5 !important;
                }

                .admin-dashboard-content .modal .modal-dialog-scrollable .modal-body {
                    scrollbar-color: #b8c8cf #f7fafb;
                    scrollbar-width: thin;
                }

                @media (max-width: 575.98px) {
                    .admin-dashboard-content .modal .modal-header {
                        padding: .9rem 1rem !important;
                    }

                    .admin-dashboard-content .modal .modal-body {
                        padding: 1rem !important;
                    }

                    .admin-dashboard-content .modal .modal-footer {
                        padding: .8rem 1rem 1rem !important;
                        flex-wrap: wrap;
                    }

                    .admin-dashboard-content .modal .modal-title {
                        font-size: 1rem;
                    }

                    .admin-dashboard-content .modal .modal-footer .btn {
                        flex: 1 1 auto;
                    }
                }

                @media (prefers-reduced-motion: reduce) {
                    .admin-dashboard-content .modal .btn,
                    .admin-dashboard-content .modal .form-control,
                    .admin-dashboard-content .modal .form-select {
                        transition: none;
                    }

                    .admin-dashboard-content .modal .btn:not(:disabled):hover {
                        transform: none;
                    }
                }
            </style>
        @endif
        @if(auth()->user()->hasRole('controlecp.instructor'))
            <style>
                .instructor-dashboard-content .modal .modal-content {
                    overflow: hidden;
                    border: 1px solid #e3eaed !important;
                    border-radius: 20px !important;
                    background: #fff;
                    box-shadow: 0 24px 60px rgba(0, 26, 41, .2) !important;
                }

                .instructor-dashboard-content .modal .modal-header {
                    align-items: flex-start;
                    gap: .75rem;
                    padding: 1.1rem 1.35rem !important;
                    border: 0 !important;
                    border-bottom: 3px solid var(--sena-green) !important;
                    background: linear-gradient(125deg, #001a29, #00324d) !important;
                    color: #fff !important;
                }

                .instructor-dashboard-content .modal .modal-title {
                    margin: 0;
                    color: #fff !important;
                    font-size: 1.12rem;
                    font-weight: 800;
                    line-height: 1.35;
                    letter-spacing: -.015em;
                }

                .instructor-dashboard-content .modal .modal-header .text-dark,
                .instructor-dashboard-content .modal .modal-header .text-white {
                    color: #fff !important;
                    font-weight: 800;
                }

                .instructor-dashboard-content .modal .modal-header .rounded-circle {
                    display: inline-flex;
                    flex: 0 0 42px;
                    align-items: center;
                    justify-content: center;
                    width: 42px;
                    height: 42px;
                    border: 1px solid rgba(255, 255, 255, .24);
                    background: rgba(255, 255, 255, .12);
                    color: #fff;
                }

                .instructor-dashboard-content .modal .modal-header p,
                .instructor-dashboard-content .modal .modal-header .text-muted {
                    color: #c7d4da !important;
                }

                .instructor-dashboard-content .modal .modal-header .btn-close {
                    flex: 0 0 auto;
                    padding: .65rem;
                    border-radius: 50%;
                    background-color: rgba(255, 255, 255, .14);
                    filter: invert(1) grayscale(100%) brightness(200%);
                    opacity: .9;
                }

                .instructor-dashboard-content .modal .modal-body {
                    padding: 1.25rem 1.35rem !important;
                    background: #f7fafb;
                    color: #334155;
                    line-height: 1.55;
                }

                .instructor-dashboard-content .modal .modal-body h1,
                .instructor-dashboard-content .modal .modal-body h2,
                .instructor-dashboard-content .modal .modal-body h3,
                .instructor-dashboard-content .modal .modal-body h4,
                .instructor-dashboard-content .modal .modal-body h5 {
                    color: #001a29;
                }

                .instructor-dashboard-content .modal .modal-footer {
                    display: flex;
                    flex-wrap: wrap;
                    gap: .55rem;
                    padding: .9rem 1.35rem 1.15rem !important;
                    border-top: 1px solid #e8eef2 !important;
                    background: #fff !important;
                }

                .instructor-dashboard-content .modal .form-label {
                    display: inline-block;
                    margin-bottom: .4rem;
                    color: #23465d;
                    font-size: .84rem;
                    font-weight: 700;
                }

                .instructor-dashboard-content .modal .form-control,
                .instructor-dashboard-content .modal .form-select {
                    min-height: 42px;
                    border: 1px solid #dce5eb;
                    border-radius: 10px;
                    background-color: #fff;
                    color: #1f2d3d;
                    box-shadow: none;
                    transition: border-color .16s ease, box-shadow .16s ease;
                }

                .instructor-dashboard-content .modal textarea.form-control {
                    min-height: auto;
                }

                .instructor-dashboard-content .modal .form-control:hover,
                .instructor-dashboard-content .modal .form-select:hover {
                    border-color: #b7c9d2;
                }

                .instructor-dashboard-content .modal .form-control:focus,
                .instructor-dashboard-content .modal .form-select:focus {
                    border-color: var(--sena-green);
                    box-shadow: 0 0 0 .2rem rgba(57, 169, 0, .14);
                }

                .instructor-dashboard-content .modal .form-check-input:checked {
                    border-color: var(--sena-green);
                    background-color: var(--sena-green);
                }

                .instructor-dashboard-content .modal .form-text,
                .instructor-dashboard-content .modal .text-muted {
                    color: #64748b !important;
                }

                .instructor-dashboard-content .modal .input-group-text {
                    border-color: #dce5eb;
                    background: #f1f5f7;
                    color: #475569;
                }

                .instructor-dashboard-content .modal .alert {
                    border: 1px solid rgba(0, 26, 41, .08);
                    border-radius: 12px;
                }

                .instructor-dashboard-content .modal .table {
                    --bs-table-bg: #fff;
                    --bs-table-striped-bg: #f7fafb;
                    margin-bottom: 0;
                    vertical-align: middle;
                }

                .instructor-dashboard-content .modal .table thead th {
                    border-bottom-color: #dce5eb;
                    background: #edf3f5;
                    color: #23465d;
                    font-size: .76rem;
                    font-weight: 800;
                    letter-spacing: .035em;
                    text-transform: uppercase;
                    white-space: nowrap;
                }

                .instructor-dashboard-content .modal .table-responsive {
                    border: 1px solid #e3eaed;
                    border-radius: 12px;
                    background: #fff;
                }

                .instructor-dashboard-content .modal .btn {
                    min-height: 40px;
                    border-radius: 999px;
                    font-weight: 700;
                    transition: transform .16s ease, box-shadow .16s ease, background-color .16s ease;
                }

                .instructor-dashboard-content .modal .btn:not(:disabled):hover {
                    transform: translateY(-1px);
                    box-shadow: 0 5px 14px rgba(0, 26, 41, .11);
                }

                .instructor-dashboard-content .modal .modal-footer .btn-primary,
                .instructor-dashboard-content .modal .modal-footer .btn-success,
                .instructor-dashboard-content .modal .modal-footer .btn-save,
                .instructor-dashboard-content .modal .modal-footer button[type="submit"]:not(.btn-danger):not(.btn-outline-danger):not(.btn-warning):not(.btn-outline-warning) {
                    border-color: var(--sena-green) !important;
                    background: var(--sena-green) !important;
                    color: #fff !important;
                }

                .instructor-dashboard-content .modal .modal-footer .btn-primary:hover,
                .instructor-dashboard-content .modal .modal-footer .btn-success:hover,
                .instructor-dashboard-content .modal .modal-footer .btn-save:hover,
                .instructor-dashboard-content .modal .modal-footer button[type="submit"]:not(.btn-danger):not(.btn-outline-danger):not(.btn-warning):not(.btn-outline-warning):hover {
                    border-color: var(--sena-green-hover) !important;
                    background: var(--sena-green-hover) !important;
                }

                .instructor-dashboard-content .modal .btn-outline-success {
                    border-color: #d3e9c8 !important;
                    background: #eff8e9 !important;
                    color: #347d1c !important;
                }

                .instructor-dashboard-content .modal .btn-outline-secondary,
                .instructor-dashboard-content .modal .btn-secondary {
                    border-color: #e1e8e2 !important;
                    background: #f1f5f2 !important;
                    color: #536258 !important;
                }

                .instructor-dashboard-content .modal .btn-outline-warning,
                .instructor-dashboard-content .modal .btn-warning {
                    border-color: #f2dfad !important;
                    background: #fff6df !important;
                    color: #8a640d !important;
                }

                .instructor-dashboard-content .modal .btn-outline-danger,
                .instructor-dashboard-content .modal .btn-danger {
                    border-color: #f1d0d0 !important;
                    background: #fff0f0 !important;
                    color: #b83b3b !important;
                }

                .instructor-dashboard-content .modal .modal-dialog-scrollable .modal-body {
                    scrollbar-color: #b8c8cf #f7fafb;
                    scrollbar-width: thin;
                }

                @media (max-width: 575.98px) {
                    .instructor-dashboard-content .modal .modal-header {
                        padding: .9rem 1rem !important;
                    }

                    .instructor-dashboard-content .modal .modal-body {
                        padding: 1rem !important;
                    }

                    .instructor-dashboard-content .modal .modal-footer {
                        padding: .8rem 1rem 1rem !important;
                    }

                    .instructor-dashboard-content .modal .modal-title {
                        font-size: 1rem;
                    }

                    .instructor-dashboard-content .modal .modal-footer .btn {
                        flex: 1 1 auto;
                    }
                }

                @media (prefers-reduced-motion: reduce) {
                    .instructor-dashboard-content .modal .btn,
                    .instructor-dashboard-content .modal .form-control,
                    .instructor-dashboard-content .modal .form-select {
                        transition: none;
                    }

                    .instructor-dashboard-content .modal .btn:not(:disabled):hover {
                        transform: none;
                    }
                }
            </style>
        @endif
        @if(auth()->user()->hasRole('controlecp.apprentice'))
            <style>
                .apprentice-dashboard-content .modal .modal-content {
                    overflow: hidden;
                    border: 1px solid #e3eaed !important;
                    border-radius: 20px !important;
                    background: #fff;
                    box-shadow: 0 24px 60px rgba(0, 26, 41, .2) !important;
                }

                .apprentice-dashboard-content .modal .modal-header {
                    align-items: flex-start;
                    gap: .75rem;
                    padding: 1.1rem 1.35rem !important;
                    border: 0 !important;
                    border-bottom: 3px solid var(--sena-green) !important;
                    background: linear-gradient(125deg, #001a29, #00324d) !important;
                    color: #fff !important;
                }

                .apprentice-dashboard-content .modal .modal-title,
                .apprentice-dashboard-content .modal .modal-header .text-dark,
                .apprentice-dashboard-content .modal .modal-header .text-white {
                    color: #fff !important;
                    font-size: 1.12rem;
                    font-weight: 800;
                    line-height: 1.35;
                }

                .apprentice-dashboard-content .modal .modal-header .rounded-circle {
                    display: inline-flex;
                    flex: 0 0 42px;
                    align-items: center;
                    justify-content: center;
                    width: 42px;
                    height: 42px;
                    border: 1px solid rgba(255, 255, 255, .24);
                    background: rgba(255, 255, 255, .12);
                    color: #fff;
                }

                .apprentice-dashboard-content .modal .modal-header p,
                .apprentice-dashboard-content .modal .modal-header .text-muted {
                    color: #c7d4da !important;
                }

                .apprentice-dashboard-content .modal .modal-header .btn-close {
                    flex: 0 0 auto;
                    padding: .65rem;
                    border-radius: 50%;
                    background-color: rgba(255, 255, 255, .14);
                    filter: invert(1) grayscale(100%) brightness(200%);
                    opacity: .9;
                }

                .apprentice-dashboard-content .modal .modal-body {
                    padding: 1.25rem 1.35rem !important;
                    background: #f7fafb;
                    color: #334155;
                    line-height: 1.55;
                }

                .apprentice-dashboard-content .modal .modal-body h1,
                .apprentice-dashboard-content .modal .modal-body h2,
                .apprentice-dashboard-content .modal .modal-body h3,
                .apprentice-dashboard-content .modal .modal-body h4,
                .apprentice-dashboard-content .modal .modal-body h5 {
                    color: #001a29;
                }

                .apprentice-dashboard-content .modal .modal-footer {
                    display: flex;
                    flex-wrap: wrap;
                    gap: .55rem;
                    padding: .9rem 1.35rem 1.15rem !important;
                    border-top: 1px solid #e8eef2 !important;
                    background: #fff !important;
                }

                .apprentice-dashboard-content .modal .form-label {
                    display: inline-block;
                    margin-bottom: .4rem;
                    color: #23465d;
                    font-size: .84rem;
                    font-weight: 700;
                }

                .apprentice-dashboard-content .modal .form-control,
                .apprentice-dashboard-content .modal .form-select {
                    min-height: 42px;
                    border: 1px solid #dce5eb;
                    border-radius: 10px;
                    background-color: #fff;
                    color: #1f2d3d;
                    box-shadow: none;
                    transition: border-color .16s ease, box-shadow .16s ease;
                }

                .apprentice-dashboard-content .modal textarea.form-control {
                    min-height: auto;
                }

                .apprentice-dashboard-content .modal .form-control:hover,
                .apprentice-dashboard-content .modal .form-select:hover {
                    border-color: #b7c9d2;
                }

                .apprentice-dashboard-content .modal .form-control:focus,
                .apprentice-dashboard-content .modal .form-select:focus {
                    border-color: var(--sena-green);
                    box-shadow: 0 0 0 .2rem rgba(57, 169, 0, .14);
                }

                .apprentice-dashboard-content .modal .form-check-input:checked {
                    border-color: var(--sena-green);
                    background-color: var(--sena-green);
                }

                .apprentice-dashboard-content .modal .form-text,
                .apprentice-dashboard-content .modal .text-muted {
                    color: #64748b !important;
                }

                .apprentice-dashboard-content .modal .input-group-text {
                    border-color: #dce5eb;
                    background: #f1f5f7;
                    color: #475569;
                }

                .apprentice-dashboard-content .modal .alert {
                    border: 1px solid rgba(0, 26, 41, .08);
                    border-radius: 12px;
                }

                .apprentice-dashboard-content .modal .table {
                    --bs-table-bg: #fff;
                    --bs-table-striped-bg: #f7fafb;
                    margin-bottom: 0;
                    vertical-align: middle;
                }

                .apprentice-dashboard-content .modal .table thead th {
                    border-bottom-color: #dce5eb;
                    background: #edf3f5;
                    color: #23465d;
                    font-size: .76rem;
                    font-weight: 800;
                    letter-spacing: .035em;
                    text-transform: uppercase;
                    white-space: nowrap;
                }

                .apprentice-dashboard-content .modal .table-responsive {
                    border: 1px solid #e3eaed;
                    border-radius: 12px;
                    background: #fff;
                }

                .apprentice-dashboard-content .modal .btn {
                    min-height: 40px;
                    border-radius: 999px;
                    font-weight: 700;
                    transition: transform .16s ease, box-shadow .16s ease, background-color .16s ease;
                }

                .apprentice-dashboard-content .modal .btn:not(:disabled):hover {
                    transform: translateY(-1px);
                    box-shadow: 0 5px 14px rgba(0, 26, 41, .11);
                }

                .apprentice-dashboard-content .modal .modal-footer .btn-primary,
                .apprentice-dashboard-content .modal .modal-footer .btn-success,
                .apprentice-dashboard-content .modal .modal-footer button[type="submit"]:not(.btn-danger):not(.btn-outline-danger):not(.btn-warning):not(.btn-outline-warning) {
                    border-color: var(--sena-green) !important;
                    background: var(--sena-green) !important;
                    color: #fff !important;
                }

                .apprentice-dashboard-content .modal .modal-footer .btn-primary:hover,
                .apprentice-dashboard-content .modal .modal-footer .btn-success:hover,
                .apprentice-dashboard-content .modal .modal-footer button[type="submit"]:not(.btn-danger):not(.btn-outline-danger):not(.btn-warning):not(.btn-outline-warning):hover {
                    border-color: var(--sena-green-hover) !important;
                    background: var(--sena-green-hover) !important;
                }

                .apprentice-dashboard-content .modal .btn-outline-success {
                    border-color: #d3e9c8 !important;
                    background: #eff8e9 !important;
                    color: #347d1c !important;
                }

                .apprentice-dashboard-content .modal .btn-outline-secondary,
                .apprentice-dashboard-content .modal .btn-secondary {
                    border-color: #e1e8e2 !important;
                    background: #f1f5f2 !important;
                    color: #536258 !important;
                }

                .apprentice-dashboard-content .modal .btn-outline-danger,
                .apprentice-dashboard-content .modal .btn-danger {
                    border-color: #f1d0d0 !important;
                    background: #fff0f0 !important;
                    color: #b83b3b !important;
                }

                .apprentice-dashboard-content .modal .modal-dialog-scrollable .modal-body {
                    scrollbar-color: #b8c8cf #f7fafb;
                    scrollbar-width: thin;
                }

                @media (max-width: 575.98px) {
                    .apprentice-dashboard-content .modal .modal-header {
                        padding: .9rem 1rem !important;
                    }

                    .apprentice-dashboard-content .modal .modal-body {
                        padding: 1rem !important;
                    }

                    .apprentice-dashboard-content .modal .modal-footer {
                        padding: .8rem 1rem 1rem !important;
                    }

                    .apprentice-dashboard-content .modal .modal-title {
                        font-size: 1rem;
                    }

                    .apprentice-dashboard-content .modal .modal-footer .btn {
                        flex: 1 1 auto;
                    }
                }

                @media (prefers-reduced-motion: reduce) {
                    .apprentice-dashboard-content .modal .btn,
                    .apprentice-dashboard-content .modal .form-control,
                    .apprentice-dashboard-content .modal .form-select {
                        transition: none;
                    }

                    .apprentice-dashboard-content .modal .btn:not(:disabled):hover {
                        transform: none;
                    }
                }
            </style>
        @endif
    @endpush

    @if(auth()->user()->hasRole('controlecp.admin') || auth()->user()->hasRole('controlecp.instructor') || auth()->user()->hasRole('controlecp.apprentice'))
        @push('styles')
            <style>
                .admin-working-day-control {
                    position: relative;
                }

                .admin-working-day-control > input[type="text"] {
                    padding-right: 2.75rem !important;
                    cursor: pointer;
                }

                .admin-working-day-trigger {
                    position: absolute;
                    top: 50%;
                    right: .45rem;
                    z-index: 2;
                    display: grid;
                    width: 2rem;
                    height: 2rem;
                    place-items: center;
                    padding: 0;
                    transform: translateY(-50%);
                    border: 0;
                    border-radius: .5rem;
                    background: #e8eef3;
                    color: #536d82;
                }

                .admin-working-day-trigger:hover,
                .admin-working-day-trigger:focus-visible {
                    background: #d3dee7;
                    color: #354f64;
                    outline: 2px solid #8197aa;
                    outline-offset: 1px;
                }

                .admin-working-day-trigger:disabled {
                    cursor: not-allowed;
                    opacity: .55;
                }

                .colombian-date-picker {
                    position: fixed;
                    z-index: 1100;
                    width: 292px;
                    padding: .75rem;
                    border: 1px solid #c8d3dd;
                    border-radius: 14px;
                    background: #f8fafc;
                    box-shadow: 0 14px 36px rgba(29, 48, 65, .22);
                    color: #334b60;
                    font: inherit;
                }

                .colombian-date-picker[hidden] {
                    display: none;
                }

                .colombian-date-picker-header {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: .5rem;
                    margin-bottom: .6rem;
                    padding: .4rem .5rem;
                    border-radius: 9px;
                    background: #dce5ec;
                    color: #344e63;
                    font-weight: 700;
                    text-transform: capitalize;
                }

                .colombian-date-picker-nav {
                    width: 2rem;
                    height: 2rem;
                    border: 0;
                    border-radius: 7px;
                    background: transparent;
                    color: #435d72;
                    font-size: 1.15rem;
                    line-height: 1;
                }

                .colombian-date-picker-nav:hover {
                    background: #c5d2dd;
                }

                .colombian-date-picker-grid {
                    display: grid;
                    grid-template-columns: repeat(7, minmax(0, 1fr));
                    gap: .2rem;
                    text-align: center;
                }

                .colombian-date-picker-weekday {
                    padding: .3rem 0;
                    color: #748697;
                    font-size: .73rem;
                    font-weight: 700;
                }

                .colombian-date-picker-day {
                    min-height: 2.15rem;
                    border: 0;
                    border-radius: 8px;
                    background: transparent;
                    color: #3c566b;
                    font-size: .88rem;
                }

                .colombian-date-picker-day:not(:disabled):hover,
                .colombian-date-picker-day:focus-visible {
                    background: #d8e2ea;
                    outline: 2px solid #8197aa;
                }

                .colombian-date-picker-day.is-today {
                    box-shadow: inset 0 0 0 1px #8096a9;
                }

                .colombian-date-picker-day.is-selected {
                    background: #5e788e;
                    color: #fff;
                    font-weight: 700;
                }

                .colombian-date-picker-day:disabled {
                    background: #e9eef2;
                    color: #a1adb7;
                    cursor: not-allowed;
                    text-decoration: line-through;
                }

                .colombian-date-picker-day.is-empty {
                    visibility: hidden;
                }

                .calendar-day-cell.is-non-working-day,
                .acom-calendar-day.is-non-working-day,
                .asesoria-instructor-calendar-day.is-non-working-day,
                .apr-calendar-day.is-non-working-day {
                    background: #e8eef3 !important;
                    color: #718397 !important;
                }
            </style>
        @endpush

        @push('scripts')
            <script>
                (() => {
                    const weekdays = ['L', 'M', 'X', 'J', 'V', 'S', 'D'];
                    const message = 'Selecciona un día hábil que no sea festivo nacional de Colombia.';
                    let activePicker = null;
                    let pickerId = 0;
                    const pickers = new WeakMap();

                    const dateString = (date) => [
                        date.getUTCFullYear(),
                        String(date.getUTCMonth() + 1).padStart(2, '0'),
                        String(date.getUTCDate()).padStart(2, '0')
                    ].join('-');

                    const makeUtcDate = (year, month, day) => {
                        const date = new Date(0);
                        date.setUTCHours(0, 0, 0, 0);
                        date.setUTCFullYear(year, month, day);
                        return date;
                    };

                    const parseDate = (value) => {
                        if (!/^\d{4}-\d{2}-\d{2}$/.test(value || '')) return null;
                        const [year, month, day] = value.split('-').map(Number);
                        const date = makeUtcDate(year, month - 1, day);
                        return date.getUTCFullYear() === year && date.getUTCMonth() === month - 1 && date.getUTCDate() === day
                            ? date
                            : null;
                    };

                    const addDays = (date, amount) => makeUtcDate(
                        date.getUTCFullYear(),
                        date.getUTCMonth(),
                        date.getUTCDate() + amount
                    );

                    const easterSunday = (year) => {
                        const a = year % 19;
                        const b = Math.floor(year / 100);
                        const c = year % 100;
                        const d = Math.floor(b / 4);
                        const e = b % 4;
                        const f = Math.floor((b + 8) / 25);
                        const g = Math.floor((b - f + 1) / 3);
                        const h = (19 * a + b - d - g + 15) % 30;
                        const i = Math.floor(c / 4);
                        const k = c % 4;
                        const l = (32 + 2 * e + 2 * i - h - k) % 7;
                        const m = Math.floor((a + 11 * h + 22 * l) / 451);
                        const month = Math.floor((h + l - 7 * m + 114) / 31);
                        const day = ((h + l - 7 * m + 114) % 31) + 1;
                        return makeUtcDate(year, month - 1, day);
                    };

                    const holidaysForYear = (year) => {
                        const holidays = new Set([
                            `${year}-01-01`, `${year}-05-01`, `${year}-07-20`,
                            `${year}-08-07`, `${year}-12-08`, `${year}-12-25`
                        ]);
                        const moveToMonday = (date) => {
                            const day = date.getUTCDay();
                            return dateString(day === 1 ? date : addDays(date, (8 - day) % 7 || 7));
                        };

                        [[1, 6], [3, 19], [6, 29], [8, 15], [10, 12], [11, 1], [11, 11]].forEach(([month, day]) => {
                            holidays.add(moveToMonday(makeUtcDate(year, month - 1, day)));
                        });

                        const easter = easterSunday(year);
                        holidays.add(dateString(addDays(easter, -3)));
                        holidays.add(dateString(addDays(easter, -2)));
                        [39, 60, 68].forEach((days) => holidays.add(moveToMonday(addDays(easter, days))));

                        return holidays;
                    };

                    const isWorkingDay = (value) => {
                        const date = parseDate(value);
                        if (!date) return false;
                        const weekday = date.getUTCDay();
                        return weekday !== 0 && weekday !== 6 && !holidaysForYear(date.getUTCFullYear()).has(value);
                    };

                    document.querySelectorAll('[data-calendario-fecha]')
                        .forEach((cell) => {
                            const value = cell.dataset.calendarioFecha;
                            if (!isWorkingDay(value)) {
                                cell.classList.add('is-non-working-day');
                                cell.title = 'Fin de semana o festivo nacional de Colombia';
                            }
                        });

                    const closePicker = () => {
                        if (!activePicker) return;
                        activePicker.panel.hidden = true;
                        activePicker.trigger.setAttribute('aria-expanded', 'false');
                        activePicker = null;
                    };

                    const renderPicker = (picker) => {
                        const year = picker.month.getUTCFullYear();
                        const month = picker.month.getUTCMonth();
                        const firstDay = makeUtcDate(year, month, 1);
                        const daysInMonth = makeUtcDate(year, month + 1, 0).getUTCDate();
                        const title = new Intl.DateTimeFormat('es-CO', {
                            month: 'long',
                            year: 'numeric',
                            timeZone: 'UTC'
                        }).format(firstDay);
                        picker.title.textContent = title;
                        picker.grid.replaceChildren();

                        weekdays.forEach((day) => {
                            const heading = document.createElement('span');
                            heading.className = 'colombian-date-picker-weekday';
                            heading.textContent = day;
                            picker.grid.appendChild(heading);
                        });

                        const emptyCells = (firstDay.getUTCDay() + 6) % 7;
                        for (let index = 0; index < emptyCells; index += 1) {
                            const empty = document.createElement('span');
                            empty.className = 'colombian-date-picker-day is-empty';
                            picker.grid.appendChild(empty);
                        }

                        const today = dateString(new Date());
                        for (let day = 1; day <= daysInMonth; day += 1) {
                            const date = makeUtcDate(year, month, day);
                            const value = dateString(date);
                            const button = document.createElement('button');
                            const weekend = date.getUTCDay() === 0 || date.getUTCDay() === 6;
                            const holiday = holidaysForYear(year).has(value);
                            const minimum = picker.input.getAttribute('min') || '';
                            const tooEarly = minimum && value < minimum;
                            button.type = 'button';
                            button.className = 'colombian-date-picker-day';
                            button.textContent = String(day);
                            button.setAttribute('aria-label', `${day}/${month + 1}/${year}`);
                            button.disabled = weekend || holiday || tooEarly;
                            if (value === today) button.classList.add('is-today');
                            if (value === picker.input.value) button.classList.add('is-selected');
                            if (weekend) button.title = 'Fin de semana';
                            else if (holiday) button.title = 'Festivo nacional de Colombia';
                            else if (tooEarly) button.title = 'Fecha anterior al mínimo permitido';
                            button.addEventListener('click', () => {
                                picker.input.value = value;
                                picker.input.setCustomValidity('');
                                picker.input.dispatchEvent(new Event('input', { bubbles: true }));
                                picker.input.dispatchEvent(new Event('change', { bubbles: true }));
                                closePicker();
                            });
                            picker.grid.appendChild(button);
                        }
                    };

                    const openPicker = (picker) => {
                        closePicker();
                        const currentValue = parseDate(picker.input.value);
                        const now = new Date();
                        picker.month = makeUtcDate(
                            currentValue ? currentValue.getUTCFullYear() : now.getUTCFullYear(),
                            currentValue ? currentValue.getUTCMonth() : now.getUTCMonth(),
                            1
                        );
                        renderPicker(picker);
                        picker.panel.hidden = false;
                        picker.trigger.setAttribute('aria-expanded', 'true');
                        activePicker = picker;

                        const bounds = picker.input.getBoundingClientRect();
                        const panelBounds = picker.panel.getBoundingClientRect();
                        const left = Math.max(8, Math.min(bounds.left, window.innerWidth - panelBounds.width - 8));
                        const top = bounds.bottom + panelBounds.height + 8 <= window.innerHeight
                            ? bounds.bottom + 6
                            : Math.max(8, bounds.top - panelBounds.height - 6);
                        picker.panel.style.left = `${left}px`;
                        picker.panel.style.top = `${top}px`;
                    };

                    document.querySelectorAll('input[type="date"]').forEach((input) => {
                        const parent = input.parentElement;
                        const wrapper = document.createElement('div');
                        wrapper.className = 'admin-working-day-control';
                        parent.insertBefore(wrapper, input);
                        wrapper.appendChild(input);
                        input.type = 'text';
                        input.readOnly = true;
                        input.autocomplete = 'off';
                        input.classList.add('admin-working-day-input');

                        const trigger = document.createElement('button');
                        trigger.type = 'button';
                        trigger.className = 'admin-working-day-trigger';
                        trigger.setAttribute('aria-label', 'Abrir calendario de días hábiles');
                        trigger.setAttribute('aria-expanded', 'false');
                        trigger.setAttribute('aria-haspopup', 'dialog');
                        trigger.innerHTML = '<svg aria-hidden="true" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4M8 3v4M3 10h18"></path></svg>';
                        trigger.disabled = input.disabled;
                        wrapper.appendChild(trigger);

                        const panel = document.createElement('div');
                        panel.className = 'colombian-date-picker';
                        panel.id = `admin-working-day-calendar-${++pickerId}`;
                        trigger.setAttribute('aria-controls', panel.id);
                        panel.hidden = true;
                        panel.setAttribute('role', 'dialog');
                        panel.setAttribute('aria-label', 'Calendario de días hábiles');
                        const header = document.createElement('div');
                        header.className = 'colombian-date-picker-header';
                        const previous = document.createElement('button');
                        previous.type = 'button';
                        previous.className = 'colombian-date-picker-nav';
                        previous.setAttribute('aria-label', 'Mes anterior');
                        previous.textContent = '‹';
                        const title = document.createElement('span');
                        const next = document.createElement('button');
                        next.type = 'button';
                        next.className = 'colombian-date-picker-nav';
                        next.setAttribute('aria-label', 'Mes siguiente');
                        next.textContent = '›';
                        const grid = document.createElement('div');
                        grid.className = 'colombian-date-picker-grid';
                        header.append(previous, title, next);
                        panel.append(header, grid);
                        document.body.appendChild(panel);

                        const picker = { input, wrapper, trigger, panel, title, grid, month: new Date() };
                        pickers.set(input, picker);
                        previous.addEventListener('click', () => {
                            picker.month = makeUtcDate(picker.month.getUTCFullYear(), picker.month.getUTCMonth() - 1, 1);
                            renderPicker(picker);
                        });
                        next.addEventListener('click', () => {
                            picker.month = makeUtcDate(picker.month.getUTCFullYear(), picker.month.getUTCMonth() + 1, 1);
                            renderPicker(picker);
                        });
                        trigger.addEventListener('click', () => {
                            if (activePicker === picker) closePicker();
                            else openPicker(picker);
                        });
                        input.addEventListener('click', () => openPicker(picker));
                        input.addEventListener('keydown', (event) => {
                            if (event.key === 'Enter' || event.key === ' ') {
                                event.preventDefault();
                                openPicker(picker);
                            }
                        });
                    });

                    document.addEventListener('click', (event) => {
                        if (activePicker && !activePicker.wrapper.contains(event.target) && !activePicker.panel.contains(event.target)) {
                            closePicker();
                        }
                    });
                    document.addEventListener('keydown', (event) => {
                        if (event.key === 'Escape') closePicker();
                    });
                    document.addEventListener('submit', (event) => {
                        const inputs = event.target.querySelectorAll('.admin-working-day-input');
                        for (const input of inputs) {
                            if (input.disabled) continue;
                            const minimum = input.getAttribute('min') || '';
                            const invalid = input.value && (!isWorkingDay(input.value) || (minimum && input.value < minimum));
                            input.setCustomValidity(invalid ? message : '');
                            if (invalid) {
                                event.preventDefault();
                                event.stopImmediatePropagation();
                                input.reportValidity();
                                openPicker(pickers.get(input));
                                break;
                            }
                        }
                    }, true);
                    window.addEventListener('resize', closePicker);
                    document.addEventListener('scroll', closePicker, true);
                })();
            </script>
        @endpush
    @endif

    <div class="d-flex" style="min-height: 100vh;">

        <div style="position: sticky; top: 0; min-height: 100vh; align-self: stretch; flex-shrink: 0;">
            @if(auth()->user()->hasRole('controlecp.admin'))
                @include('controlecp::components.sidebar_administrador')
            @elseif(auth()->user()->hasRole('controlecp.instructor'))
                @include('controlecp::components.sidebar_instructor')
            @elseif(auth()->user()->hasRole('controlecp.apprentice'))
                @include('controlecp::components.sidebar_aprendiz')
            @endif
        </div>

        <div class="flex-grow-1 d-flex flex-column" style="min-width: 0;">

            @include('controlecp::components.header')

            <div class="flex-grow-1 p-4 {{ auth()->user()->hasRole('controlecp.admin') ? 'admin-dashboard-content' : (auth()->user()->hasRole('controlecp.instructor') ? 'instructor-dashboard-content' : (auth()->user()->hasRole('controlecp.apprentice') ? 'apprentice-dashboard-content' : '')) }}">
                @yield('dashboard-content')
            </div>

            @include('controlecp::components.footer')

        </div>

    </div>

@endsection