@php
    $fichaActiva = $ficha->status === 'Activo' || empty($ficha->status);
@endphp

<div class="text-center mb-3">
    <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mb-2"
         style="width:58px;height:58px;font-size:1.4rem;">
        <i class="fas fa-graduation-cap"></i>
    </div>
    <div class="h4 fw-bold mb-1">{{ $ficha->code ?? '—' }}</div>
    <span class="badge rounded-pill text-bg-{{ $fichaActiva ? 'success' : 'secondary' }}">
        {{ $ficha->status ?: 'Activo' }}
    </span>
</div>

<dl class="row mb-0">
    <dt class="col-4 text-muted">Programa</dt>
    <dd class="col-8 fw-semibold">{{ $ficha->program?->name ?? 'Sin programa registrado' }}</dd>
    <dt class="col-4 text-muted">Tipo</dt>
    <dd class="col-8">{{ $ficha->program?->program_type ?? '—' }}</dd>
    <dt class="col-4 text-muted">Modalidad</dt>
    <dd class="col-8">{{ $ficha->program?->training_type ?: 'Presencial' }}</dd>
    <dt class="col-4 text-muted">Inicio</dt>
    <dd class="col-8">{{ $ficha->start_date ?? '—' }}</dd>
    <dt class="col-4 text-muted">Finalización</dt>
    <dd class="col-8">{{ $ficha->end_date ?? '—' }}</dd>
</dl>
