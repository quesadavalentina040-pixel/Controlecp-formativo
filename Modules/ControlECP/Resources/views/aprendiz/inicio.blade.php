@extends('controlecp::components.layouts.master')

@section('content')
    <div class="container py-5">
        <h1>Hola Aprendiz 👋</h1>
        <p>Estás dentro del panel de <strong>Aprendiz</strong> del módulo Control ECP.</p>
        <p>Usuario: {{ Auth::user()->full_name ?? Auth::user()->name }} ({{ Auth::user()->email }})</p>
        <a href="{{ route('logout', ['redirect' => route('login')]) }}" class="btn btn-outline-danger">Cerrar Sesión</a>
    </div>
@endsection