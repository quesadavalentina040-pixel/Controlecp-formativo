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
    @endpush

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

            <div class="flex-grow-1 p-4 {{ auth()->user()->hasRole('controlecp.admin') ? 'admin-dashboard-content' : '' }}">
                @yield('dashboard-content')
            </div>

            @include('controlecp::components.footer')

        </div>

    </div>

@endsection