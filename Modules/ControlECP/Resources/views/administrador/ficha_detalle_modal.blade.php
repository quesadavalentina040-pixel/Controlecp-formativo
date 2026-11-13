@php
    $isActive = $ficha->status === 'Activo' || empty($ficha->status);
    $program = $ficha->program;
@endphp

<div class="row g-4 ficha-detalle-layout">
    <div class="col-lg-4">
        <div class="card border-0 h-100 ficha-detalle-summary">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center bg-white text-success shadow-sm"
                          style="width:52px;height:52px;font-size:1.35rem;flex:0 0 52px;">
                        <i class="fas fa-graduation-cap"></i>
                    </span>
                    <div>
                        <div class="small text-muted">Ficha</div>
                        <h3 class="h5 fw-bold mb-1">{{ $ficha->code }}</h3>
                        <span class="badge rounded-pill text-bg-{{ $isActive ? 'success' : 'secondary' }}">
                            {{ $isActive ? 'Activa' : 'Inactiva' }}
                        </span>
                    </div>
                </div>
                <dl class="row small mb-0 ficha-detalle-dl">
                    <dt class="col-5">Programa</dt>
                    <dd class="col-7">{{ $program?->name ?? 'Sin programa' }}</dd>
                    <dt class="col-5">Tipo</dt>
                    <dd class="col-7">{{ $program?->program_type ?? '—' }}</dd>
                    <dt class="col-5">Modalidad</dt>
                    <dd class="col-7">{{ $program?->training_type ?: 'Presencial' }}</dd>
                    <dt class="col-5">Inicio lectiva</dt>
                    <dd class="col-7">{{ $ficha->start_date ?? '—' }}</dd>
                    <dt class="col-5">Fin lectiva</dt>
                    <dd class="col-7">{{ $ficha->end_date ?? '—' }}</dd>
                    @if($ficha->start_production_date)
                        <dt class="col-5">Inicio productiva</dt>
                        <dd class="col-7">{{ $ficha->start_production_date }}</dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="row g-2 mb-3">
            <div class="col-6 col-xl-3">
                <div class="border rounded-3 p-3 text-center h-100 ficha-detalle-stat">
                    <div class="h4 fw-bold mb-0">{{ $statsAprendices['total'] }}</div>
                    <div class="small text-muted">Aprendices</div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="border rounded-3 p-3 text-center h-100 ficha-detalle-stat ficha-detalle-stat--training">
                    <div class="h4 fw-bold text-primary mb-0">{{ $statsAprendices['en_formacion'] }}</div>
                    <div class="small text-muted">En formación</div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="border rounded-3 p-3 text-center h-100 ficha-detalle-stat ficha-detalle-stat--certified">
                    <div class="h4 fw-bold text-success mb-0">{{ $statsAprendices['certificados'] }}</div>
                    <div class="small text-muted">Certificados</div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="border rounded-3 p-3 text-center h-100 ficha-detalle-stat ficha-detalle-stat--retired">
                    <div class="h4 fw-bold text-danger mb-0">{{ $statsAprendices['retirados'] }}</div>
                    <div class="small text-muted">Retirados</div>
                </div>
            </div>
        </div>

        <h3 class="h6 fw-bold mb-2">
            <i class="fas fa-users-viewfinder me-2 text-success"></i>Nómina de aprendices
        </h3>
        @if($ficha->apprentices->isNotEmpty())
            <div class="table-responsive border rounded-3 ficha-detalle-roster-table">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th class="ps-3">Documento</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ficha->apprentices as $aprendiz)
                            @php
                                $persona = $aprendiz->person;
                            @endphp
                            <tr>
                                <td class="ps-3">
                                    @if($persona)
                                        <span class="badge bg-light text-dark border">{{ $persona->document_type }}</span>
                                        {{ $persona->document_number }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $persona ? trim($persona->first_name . ' ' . $persona->first_last_name . ' ' . $persona->second_last_name) : 'Sin datos' }}</td>
                                <td>
                                    {{ $persona?->misena_email ?: ($persona?->personal_email ?? 'No registra') }}
                                </td>
                                <td><span class="badge text-bg-light border">{{ $aprendiz->apprentice_status ?? 'EN FORMACIÓN' }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center text-muted border rounded-3 py-4 ficha-detalle-empty">
                <i class="fas fa-users-slash mb-2" aria-hidden="true"></i>
                <div>Esta ficha aún no tiene aprendices vinculados.</div>
            </div>
        @endif
    </div>
</div>
