@extends('senaempresa::components.layouts.master')

@section('content')

    <div class="row justify-content-center">
        <div class="col-md-10">

            {{-- Estado de conexión --}}
            <div class="card card-outline {{ $connection['status'] ? 'card-success' : 'card-danger' }}">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-satellite-dish"></i> Estado de Conexión SICEFA</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p>
                                <strong>Estado:</strong>
                                @if ($connection['status'])
                                    <span class="badge badge-success"><i class="fas fa-check-circle"></i> Conectado</span>
                                @else
                                    <span class="badge badge-danger"><i class="fas fa-times-circle"></i> Sin conexión</span>
                                @endif
                            </p>
                            <p class="text-muted">{{ $connection['message'] }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>URL de la API:</strong></p>
                            <code>{{ config('senaempresa.sicefa_api.base_url') }}</code>
                            <p class="mt-2"><strong>Token configurado:</strong>
                                @if (config('senaempresa.sicefa_api.token'))
                                    <span class="text-success"><i class="fas fa-check"></i> Sí</span>
                                @else
                                    <span class="text-danger"><i class="fas fa-exclamation-triangle"></i> No configurado</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sincronizar aprendices --}}
            <div class="card card-outline card-info mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-graduate"></i> Sincronizar Aprendices</h3>
                </div>
                <div class="card-body">
                    <p>Descarga la lista de aprendices desde la API REST central de SICEFA y los actualiza en la base de datos local.</p>
                    <form action="{{ route('senaempresa.asistencia.sync.apprentices') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-info" onclick="this.disabled=true; this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Sincronizando...'; this.form.submit();">
                            <i class="fas fa-download"></i> Sincronizar Aprendices
                        </button>
                    </form>
                </div>
            </div>

            {{-- Enviar asistencias --}}
            <div class="card card-outline card-warning mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-cloud-upload-alt"></i> Enviar Asistencias Pendientes</h3>
                </div>
                <div class="card-body">
                    <p>Envía las sesiones de asistencia que aún no han sido sincronizadas con SICEFA.</p>
                    <p>
                        <strong>Sesiones pendientes:</strong>
                        <span class="badge badge-warning badge-pill">{{ $pendingCount }}</span>
                    </p>
                    <form action="{{ route('senaempresa.asistencia.sync.attendance') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-warning"
                                @if ($pendingCount == 0) disabled @endif
                                onclick="this.disabled=true; this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Enviando...'; this.form.submit();">
                            <i class="fas fa-upload"></i> Enviar a SICEFA
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

@endsection
