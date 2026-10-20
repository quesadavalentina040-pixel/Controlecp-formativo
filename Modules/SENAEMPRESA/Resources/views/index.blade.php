@extends('senaempresa::components.layouts.master')

@section('content')

    @php
        $totalAsistencias = \Modules\SENAEMPRESA\Entities\Asistencia::count();
        $asistenciasActivas = \Modules\SENAEMPRESA\Entities\Asistencia::where('estado', 'ACTIVA')->count();
        $asistenciasCerradas = \Modules\SENAEMPRESA\Entities\Asistencia::where('estado', 'CERRADA')->count();
        $pendientesSicefa = \Modules\SENAEMPRESA\Entities\Asistencia::whereNull('sicefa_id')
            ->orWhereNull('sicefa_synced_at')
            ->where('estado', '!=', 'CANCELADA')
            ->count();
        $totalAprendices = \Modules\SICA\Entities\Apprentice::count();
    @endphp

    <div class="row">
        <div class="col-lg-3 col-sm-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalAsistencias }}</h3>
                    <p>Sesiones totales</p>
                </div>
                <div class="icon"><i class="fas fa-clipboard-list"></i></div>
                <a href="{{ route('senaempresa.asistencia.index') }}" class="small-box-footer">
                    Ver todas <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $asistenciasActivas }}</h3>
                    <p>Sesiones activas</p>
                </div>
                <div class="icon"><i class="fas fa-door-open"></i></div>
                <a href="{{ route('senaempresa.asistencia.create') }}" class="small-box-footer">
                    Crear nueva <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $pendientesSicefa }}</h3>
                    <p>Pendientes SICEFA</p>
                </div>
                <div class="icon"><i class="fas fa-cloud-upload-alt"></i></div>
                <a href="{{ route('senaempresa.asistencia.sync') }}" class="small-box-footer">
                    Sincronizar <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="small-box bg-orange">
                <div class="inner">
                    <h3>{{ $totalAprendices }}</h3>
                    <p>Aprendices registrados</p>
                </div>
                <div class="icon"><i class="fas fa-user-graduate"></i></div>
                <a href="{{ route('senaempresa.asistencia.sync') }}" class="small-box-footer">
                    Sincronizar aprendices <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card card-orange card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-industry"></i> Módulo SENA Empresa</h3>
                </div>
                <div class="card-body">
                    <p>Este módulo gestiona la asistencia de los aprendices del SENA Empresa e integra los datos con el ERP SICEFA a través de su API REST central.</p>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-box bg-light">
                                <div class="info-box-content">
                                    <span class="info-box-text text-center"><i class="fas fa-clipboard-check fa-2x text-primary"></i></span>
                                    <span class="info-box-number text-center mt-2">
                                        <a href="{{ route('senaempresa.asistencia.index') }}" class="btn btn-primary btn-block">Gestionar Asistencias</a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-light">
                                <div class="info-box-content">
                                    <span class="info-box-text text-center"><i class="fas fa-plus-circle fa-2x text-success"></i></span>
                                    <span class="info-box-number text-center mt-2">
                                        <a href="{{ route('senaempresa.asistencia.create') }}" class="btn btn-success btn-block">Crear Nueva Sesión</a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-light">
                                <div class="info-box-content">
                                    <span class="info-box-text text-center"><i class="fas fa-sync-alt fa-2x text-warning"></i></span>
                                    <span class="info-box-number text-center mt-2">
                                        <a href="{{ route('senaempresa.asistencia.sync') }}" class="btn btn-warning btn-block">Sincronizar con SICEFA</a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
