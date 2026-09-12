{{--
    ============================================================
    Layout intermedio del dashboard de Control ECP.
    Decide qué sidebar mostrar según el rol del usuario logueado
    y arma la página completa: header + sidebar + contenido + footer.
    Las vistas de cada rol (administrador/instructor/aprendiz)
    extienden ESTE archivo, no el master directamente.
    ============================================================
--}}
@extends('controlecp::components.layouts.master')

@section('content')

    @include('controlecp::components.header')

    <div class="d-flex">

        @if(auth()->user()->hasRole('controlecp.admin'))
            @include('controlecp::components.sidebar_administrador')
        @elseif(auth()->user()->hasRole('controlecp.instructor'))
            @include('controlecp::components.sidebar_instructor')
        @elseif(auth()->user()->hasRole('controlecp.apprentice'))
            @include('controlecp::components.sidebar_aprendiz')
        @endif

        <div class="flex-grow-1 p-4">
            @yield('dashboard-content')
        </div>

    </div>

    @include('controlecp::components.footer')

@endsection