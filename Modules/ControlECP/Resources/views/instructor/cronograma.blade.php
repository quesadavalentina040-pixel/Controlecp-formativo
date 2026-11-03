@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div><h2 class="h3 fw-bold mb-1" style="color:#001A29"><i class="fas fa-calendar-days me-2 text-success"></i>Mi cronograma</h2><p class="text-muted small mb-0">Actividades asignadas, fechas, recursos y posposiciones.</p></div>
    </div>
    @if($errors->any()) <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div> @endif
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    <form class="card border-0 shadow-sm mb-4 p-3" method="GET">
        <div class="row g-2 align-items-end">
            <div class="col-md-2"><label class="small">Ficha</label><select name="ficha_id" class="form-select"><option value="">Todas</option>@foreach($fichas as $ficha)<option value="{{ $ficha->id }}" @selected(request('ficha_id') == $ficha->id)>{{ $ficha->code }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="small">Momento</label><select name="momento_id" class="form-select"><option value="">Todos</option>@foreach($momentos as $momento)<option value="{{ $momento->id }}" @selected(request('momento_id') == $momento->id)>{{ $momento->nombre }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="small">Estado</label><select name="estado" class="form-select"><option value="">Todos</option>@foreach(['Pendiente','Pospuesta','Finalizada','Cancelada'] as $estado)<option @selected(request('estado') === $estado)>{{ $estado }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="small">Desde</label><input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}"></div>
            <div class="col-md-2"><label class="small">Hasta</label><input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}"></div>
            <div class="col-md-2"><button class="btn btn-success w-100"><i class="fas fa-filter me-1"></i>Filtrar</button></div>
        </div>
    </form>
    <div class="d-flex flex-wrap gap-3 small mb-3"><span><i class="fas fa-circle text-danger me-1"></i>Hoy o muy próxima</span><span><i class="fas fa-circle text-warning me-1"></i>Próxima semana</span><span><i class="fas fa-circle text-success me-1"></i>Dos semanas o más</span></div>
    <div class="row g-3">
        @forelse($cronogramas as $cronograma)
            @php($color = ['rojo'=>'danger','amarillo'=>'warning','verde'=>'success'][$cronograma->semaforo] ?? 'secondary')
            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-{{ $color }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-2"><div><h5 class="fw-bold mb-1">Actividad {{ $cronograma->actividad->numero ?? $cronograma->id_actividad }} – {{ $cronograma->actividad->momento->nombre ?? 'Sin momento' }}</h5><span class="badge text-bg-light">{{ $cronograma->actividad->nombre ?? 'Actividad ECP' }}</span></div><span class="badge text-bg-{{ $cronograma->estado === 'Finalizada' ? 'success' : ($cronograma->estado === 'Pospuesta' ? 'secondary' : 'warning') }}">{{ $cronograma->estado }}</span></div>
                        <div class="row g-2 small mt-3"><div class="col-sm-6"><strong>Ficha:</strong> {{ $cronograma->ficha->code ?? 'Sin ficha' }}<br><strong>Programa:</strong> {{ $cronograma->ficha->program->name ?? 'Sin programa' }}</div><div class="col-sm-6"><strong>Fecha:</strong> {{ $cronograma->fecha->format('d/m/Y') }}<br><strong>Hora:</strong> {{ $cronograma->horario_formateado }}</div><div class="col-12"><strong>Lugar:</strong> {{ $cronograma->lugar ?: 'Sin lugar' }}</div></div>
                        @if($cronograma->recursos->isNotEmpty())<div class="small mt-2"><strong>Recursos:</strong> @foreach($cronograma->recursos as $recurso){{ $recurso->inventario->nombre ?? 'Recurso' }} ({{ $recurso->cantidad }})@if(!$loop->last), @endif @endforeach</div>@endif
                        @if($cronograma->estado !== 'Finalizada' && $cronograma->estado !== 'Cancelada')<button class="btn btn-outline-success btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#posponer-{{ $cronograma->id_cronograma }}"><i class="fas fa-calendar-plus me-1"></i>Posponer</button>@endif
                    </div>
                </div>
            </div>
            @if($cronograma->estado !== 'Finalizada' && $cronograma->estado !== 'Cancelada')
            <div class="modal fade" id="posponer-{{ $cronograma->id_cronograma }}" tabindex="-1"><div class="modal-dialog"><form method="POST" action="{{ route('controlecp.instructor.cronograma.posponer',$cronograma->id_cronograma) }}" class="modal-content">@csrf<div class="modal-header"><h5>Posponer actividad</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><label class="small">Nueva fecha</label><input type="date" name="nueva_fecha" class="form-control mb-2" min="{{ now()->format('Y-m-d') }}" value="{{ $cronograma->fecha->format('Y-m-d') }}" required><div class="row g-2"><div class="col"><label class="small">Inicio</label><input type="time" name="hora_inicio" class="form-control" value="{{ substr($cronograma->hora_inicio,0,5) }}" required></div><div class="col"><label class="small">Fin</label><input type="time" name="hora_fin" class="form-control" value="{{ substr($cronograma->hora_fin,0,5) }}" required></div></div><textarea name="motivo" class="form-control mt-2" minlength="5" required placeholder="Motivo de la posposición"></textarea></div><div class="modal-footer"><button class="btn btn-success">Guardar posposición</button></div></form></div></div>
            @endif
        @empty <div class="col-12"><div class="alert alert-info">No tienes actividades asignadas en este rango.</div></div> @endforelse
    </div>
</div>
@endsection
