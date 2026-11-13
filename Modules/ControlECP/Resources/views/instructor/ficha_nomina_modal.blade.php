<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <div class="fw-bold">Ficha {{ $ficha->code ?? '—' }}</div>
        <div class="small text-muted">{{ $ficha->program?->name ?? 'Sin programa registrado' }}</div>
    </div>
    <span class="badge rounded-pill text-bg-success">{{ $ficha->apprentices_count }} aprendices</span>
</div>

@if($ficha->apprentices->isNotEmpty())
    <div class="table-responsive border rounded-3">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Documento</th>
                    <th>Aprendiz</th>
                    <th>Correo</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ficha->apprentices as $aprendiz)
                    @php
                        $persona = $aprendiz->person;
                        $tipoDocumento = match(mb_strtolower(trim((string) ($persona?->document_type ?? '')))) {
                            'cédula de ciudadanía', 'cedula de ciudadania', 'cc', 'c.c.' => 'CC',
                            'tarjeta de identidad', 'ti', 't.i.' => 'TI',
                            'cédula de extranjería', 'cedula de extranjeria', 'ce', 'c.e.' => 'CE',
                            'pasaporte' => 'Pasaporte',
                            default => $persona?->document_type ?: 'Documento',
                        };
                    @endphp
                    <tr>
                        <td class="ps-3">
                            @if($persona)
                                <span class="badge bg-light text-dark border me-1">{{ $tipoDocumento }}</span>{{ $persona->document_number }}
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $persona ? trim($persona->first_name . ' ' . $persona->first_last_name . ' ' . $persona->second_last_name) : 'Sin datos' }}</td>
                        <td>{{ $persona?->personal_email ?: ($persona?->misena_email ?? 'No registra') }}</td>
                        <td><span class="badge text-bg-light border">{{ $aprendiz->apprentice_status ?? 'EN FORMACIÓN' }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-center text-muted border rounded-3 py-4">
        <i class="fas fa-users-slash mb-2"></i>
        <div>Esta ficha aún no tiene aprendices registrados.</div>
    </div>
@endif
