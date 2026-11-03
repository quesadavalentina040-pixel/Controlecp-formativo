@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2"><div class="card border-0 shadow-sm"><div class="card-body">
    <h3 class="fw-bold">Registrar asistencia</h3><p class="text-muted">{{ $cronograma->actividad->nombre ?? 'Actividad' }} · Ficha {{ $cronograma->ficha->code }}</p>
    <form method="POST" action="{{ route('controlecp.instructor.asistencia.store',$cronograma->id_cronograma) }}" enctype="multipart/form-data">@csrf
        <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Aprendiz</th><th>Documento</th><th class="text-center">Presente</th></tr></thead><tbody>
        @foreach($cronograma->ficha->apprentices as $aprendiz)<tr><td>{{ $aprendiz->person->full_name ?? 'Sin nombre' }}</td><td>{{ $aprendiz->person->document_number ?? '-' }}</td><td class="text-center"><input type="checkbox" class="form-check-input" name="presentes[]" value="{{ $aprendiz->id }}" checked></td></tr>@endforeach
        </tbody></table></div>
        <textarea name="observacion" class="form-control mb-3" placeholder="Observación general opcional"></textarea>
        <div class="row g-2"><div class="col-md-6"><label class="small">Evidencia fotográfica opcional</label><input type="file" name="evidencia" class="form-control" accept=".jpg,.jpeg,.png"></div><div class="col-md-6"><label class="small">O enlace de video</label><input type="url" name="enlace_video" class="form-control"></div></div>
        <div class="mt-4"><button class="btn btn-success">Guardar asistencia</button><a href="{{ route('controlecp.instructor.asistencia') }}" class="btn btn-outline-secondary ms-2">Cancelar</a></div>
    </form>
</div></div></div>
@endsection
