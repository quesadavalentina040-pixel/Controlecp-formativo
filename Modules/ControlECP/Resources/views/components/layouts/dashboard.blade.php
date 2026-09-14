@extends('controlecp::components.layouts.master')

@section('content')

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

        <div class="flex-grow-1 d-flex flex-column">

            @include('controlecp::components.header')

            <div class="flex-grow-1 p-4">
                @yield('dashboard-content')
            </div>

            @include('controlecp::components.footer')

        </div>

    </div>

@endsection