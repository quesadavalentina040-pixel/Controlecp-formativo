@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
    <h1>Hola Admin 👋</h1>
    <p>Estás dentro del panel de <strong>Administrador</strong> del módulo Control ECP.</p>
    <p>Usuario: {{ Auth::user()->full_name ?? Auth::user()->name }} ({{ Auth::user()->email }})</p>
    <a href="{{ route('logout', ['redirect' => route('login')]) }}" class="btn btn-outline-danger">Cerrar Sesión</a>
@endsection