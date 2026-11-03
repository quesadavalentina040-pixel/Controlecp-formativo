{{-- Consulta y gestión de componentes de actividades para el instructor. --}}
@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    :root {
        --ecp-green: #39A900;
        --ecp-navy: #001A29;
        --ecp-blue: #0070B8;
        --ecp-purple: #6A1B9A;
    }

    .act-page { font-family: 'Outfit', sans-serif; min-width: 0; }
    .kpi-card, .act-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,.04);
        box-shadow: 0 4px 18px rgba(0,26,41,.07);
    }
    .kpi-card {
        border-radius: 18px;
        padding: 1.1rem 1rem;
        transition: transform .2s, box-shadow .2s;
    }
    .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 8px 28px rgba(0,26,41,.12); }
    .kpi-icon-wrap {
        width: 38px; height: 38px; border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
    }
    .kpi-value { font-size: 1.7rem; font-weight: 800; line-height: 1; color: var(--ecp-navy); }
    .kpi-label { font-size: .77rem; color: #7a8394; font-weight: 500; }
    .momento-section {
        width: 100%; border-radius: 18px; overflow: hidden; margin-bottom: 2rem;
        box-shadow: 0 4px 16px rgba(0,26,41,.06);
    }
    .momento-header {
        padding: 1rem 1.25rem; color: #fff; display: flex;
        align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .5rem;
    }
    .act-grid {
        display: grid; grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .75rem; padding: .85rem 1rem; background: #f8faf9;
    }
    .act-card {
        border-radius: 16px; overflow: hidden;
        transition: transform .2s, box-shadow .2s;
    }
    .act-card:hover { transform: translateY(-3px); box-shadow: 0 10px 28px rgba(0,26,41,.11); }
    .comp-badge {
        font-size: .7rem; padding: .25rem .55rem; border-radius: 50px;
        font-weight: 600; border: 1.5px solid; display: inline-flex;
        align-items: center; gap: .25rem;
    }
    @media (max-width: 1200px) { .act-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 768px) { .act-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 520px) { .act-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('dashboard-content')
<div class="act-page container-fluid px-2 px-md-3 py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.instructor.inicio') }}" class="text-decoration-none" style="color:var(--ecp-green);">
                            <i class="fas fa-house me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item active" style="color:var(--ecp-navy);font-weight:600;">Actividades</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color:var(--ecp-navy);">
                <i class="fas fa-list-check me-2" style="color:var(--ecp-green);"></i>Gestión de Actividades
            </h2>
            <p class="text-muted small mb-0">
                Control ECP &bull; Consulta y gestión de componentes pedagógicos
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('controlecp.instructor.momentos') }}" class="btn btn-outline-success rounded-pill px-3 btn-sm">
                <i class="fas fa-star me-1"></i> Ver Momentos
            </a>
            <a href="{{ route('controlecp.instructor.inicio') }}" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Inicio
            </a>
        </div>
    </div>

    @php
        $totalActividades = $momentos->sum('actividades_count');
        $totalMomentos = $momentos->count();
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon-wrap mb-2" style="background:rgba(57,169,0,.12);">
                    <i class="fas fa-list-check" style="color:var(--ecp-green);"></i>
                </div>
                <div class="kpi-value" style="color:var(--ecp-green);">{{ $totalActividades }}</div>
                <div class="kpi-label">Actividades activas</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon-wrap mb-2" style="background:#e8f0fe;">
                    <i class="fas fa-star" style="color:#3949ab;"></i>
                </div>
                <div class="kpi-value" style="color:#3949ab;">{{ $totalMomentos }}</div>
                <div class="kpi-label">Momentos</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon-wrap mb-2" style="background:#e8f8e8;">
                    <i class="fas fa-clipboard-question" style="color:#0070B8;"></i>
                </div>
                <div class="kpi-value" style="color:#0070B8;">{{ $momentos->flatMap->actividades->whereNotNull('encuesta_id')->count() }}</div>
                <div class="kpi-label">Con encuesta</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon-wrap mb-2" style="background:#f3e5f5;">
                    <i class="fas fa-photo-film" style="color:var(--ecp-purple);"></i>
                </div>
                <div class="kpi-value" style="color:var(--ecp-purple);">{{ $momentos->flatMap->actividades->where('tiene_evidencia', true)->count() }}</div>
                <div class="kpi-label">Con evidencia</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4" style="border-radius:18px;">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-7">
                    <label for="buscarActividad" class="visually-hidden">Buscar actividad</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-start-pill"><i class="fas fa-search text-muted"></i></span>
                        <input id="buscarActividad" type="search" class="form-control border-start-0 rounded-end-pill" placeholder="Buscar por nombre u objetivo...">
                    </div>
                </div>
                <div class="col-md-5">
                    <label for="filtrarMomento" class="visually-hidden">Filtrar por momento</label>
                    <select id="filtrarMomento" class="form-select rounded-pill">
                        <option value="">Todos los momentos</option>
                        @foreach($momentos as $momento)
                            <option value="{{ $momento->id }}">{{ $momento->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    @forelse($momentos as $momento)
        <section class="card border-0 momento-section momento-bloque" data-momento-id="{{ $momento->id }}">
            <div class="momento-header" style="background:linear-gradient(135deg, {{ $momento->color ?: '#001A29' }} 0%, #001A29 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:44px;height:44px;background:rgba(255,255,255,.18);">
                        <i class="fas {{ $momento->icono ?: 'fa-star' }}"></i>
                    </div>
                    <div>
                        <div class="small" style="opacity:.8;">Momento {{ str_pad($momento->orden, 2, '0', STR_PAD_LEFT) }}</div>
                        <h5 class="fw-bold mb-0">{{ $momento->nombre }}</h5>
                        <div class="small" style="opacity:.8;">{{ $momento->descripcion }}</div>
                    </div>
                </div>
                <span class="badge rounded-pill px-3 py-2" style="background:rgba(255,255,255,.2);">
                    {{ $momento->actividades_count }} actividades
                </span>
            </div>

            @if($momento->actividades->count() > 0)
                <div class="act-grid">
                    @foreach($momento->actividades as $actividad)
                        <article class="act-card actividad-fila"
                                 data-nombre="{{ strtolower($actividad->nombre) }}"
                                 data-objetivo="{{ strtolower($actividad->objetivo) }}">
                            <div class="p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="fw-bold rounded-2 px-2 py-1 small"
                                          style="background:{{ $momento->color ?: '#39A900' }}22;color:{{ $momento->color ?: '#39A900' }};">
                                        #{{ $actividad->numero }}
                                    </span>
                                    <span class="badge bg-success-subtle text-success rounded-pill">
                                        <i class="fas fa-circle-check me-1"></i>{{ $actividad->estado }}
                                    </span>
                                </div>
                                <h6 class="fw-bold text-dark mb-1" style="font-size:.9rem;line-height:1.3;">{{ $actividad->nombre }}</h6>
                                <p class="text-muted mb-2" style="font-size:.8rem;line-height:1.4;">
                                    {{ Str::limit($actividad->objetivo, 90) }}
                                </p>
                                <div class="d-flex flex-wrap gap-1 mb-3">
                                    <span class="comp-badge" style="color:#39A900;border-color:#c2eec2;background:#f0fbf4;">
                                        <i class="fas fa-clipboard-list"></i> Asistencia
                                    </span>
                                    <span class="comp-badge" style="color:{{ $actividad->encuesta_id ? '#0070B8' : '#999' }};border-color:{{ $actividad->encuesta_id ? '#bce1f7' : '#ddd' }};background:#f8fbff;">
                                        <i class="fas fa-clipboard-question"></i> Encuesta
                                    </span>
                                    <span class="comp-badge" style="color:#6A1B9A;border-color:#e1bee7;background:#fdf7ff;">
                                        <i class="fas fa-photo-film"></i> Evidencia
                                    </span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between gap-2 border-top pt-2">
                                    <span class="small text-muted"><i class="far fa-clock me-1"></i>{{ $actividad->duracion }}</span>
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 ver-componentes"
                                            data-id="{{ $actividad->id }}" data-nombre="{{ $actividad->nombre }}">
                                        <i class="fas fa-puzzle-piece me-1"></i>Componentes
                                    </button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="sin-resultados text-muted small text-center py-3 d-none">No hay actividades que coincidan con la búsqueda.</div>
            @else
                <div class="p-4 text-muted small">No hay actividades activas en este momento.</div>
            @endif
        </section>
    @empty
        <div class="alert alert-info rounded-4 border-0">No hay actividades activas para consultar.</div>
    @endforelse
</div>

<div class="modal fade" id="modalComponentesInstructor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" style="color:#001A29;"><i class="fas fa-sliders me-2" style="color:#39A900;"></i><span id="componentesTitulo"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="componentesContenido"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const actividadesInstructorBase = @json(url('/control-ecp/instructor/actividades'));
    const modalComponentesInstructor = new bootstrap.Modal(document.getElementById('modalComponentesInstructor'));

    function filtrarActividades() {
        const termino = document.getElementById('buscarActividad').value.trim().toLowerCase();
        const momento = document.getElementById('filtrarMomento').value;
        document.querySelectorAll('.momento-bloque').forEach((bloque) => {
            const coincideMomento = !momento || bloque.dataset.momentoId === momento;
            let visibles = 0;
            bloque.querySelectorAll('.actividad-fila').forEach((fila) => {
                const coincideTexto = !termino || fila.dataset.nombre.includes(termino) || fila.dataset.objetivo.includes(termino);
                fila.classList.toggle('d-none', !(coincideMomento && coincideTexto));
                if (coincideMomento && coincideTexto) visibles++;
            });
            bloque.classList.toggle('d-none', !coincideMomento);
            const sinResultados = bloque.querySelector('.sin-resultados');
            if (sinResultados) sinResultados.classList.toggle('d-none', visibles > 0);
        });
    }

    document.getElementById('buscarActividad').addEventListener('input', filtrarActividades);
    document.getElementById('filtrarMomento').addEventListener('change', filtrarActividades);

    document.querySelectorAll('.ver-componentes').forEach((button) => {
        button.addEventListener('click', async () => {
            document.getElementById('componentesTitulo').textContent = button.dataset.nombre;
            document.getElementById('componentesContenido').innerHTML = '<div class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>';
            modalComponentesInstructor.show();
            try {
                const response = await fetch(`${actividadesInstructorBase}/${button.dataset.id}/componentes`, { headers: { Accept: 'application/json' } });
                const data = await response.json();
                if (!response.ok || !data.success) throw new Error(data.message || 'No se pudieron cargar los componentes.');
                const componentes = data.componentes;
                document.getElementById('componentesContenido').innerHTML = `
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-0"><i class="fas fa-user-check text-success me-2"></i><strong>Asistencia:</strong> ${componentes.asistencia.activo ? 'Activa' : 'Inactiva'}</div>
                        <div class="list-group-item px-0"><i class="fas fa-clipboard-question text-primary me-2"></i><strong>Encuesta:</strong> ${componentes.encuesta.encuesta || 'Sin encuesta asociada'}</div>
                        <div class="list-group-item px-0"><i class="fas fa-photo-film me-2" style="color:#6a1b9a;"></i><strong>Evidencia:</strong> ${componentes.evidencia.activo ? 'Foto JPG/PNG o enlace de video' : 'Inactiva'}</div>
                    </div>`;
            } catch (error) {
                document.getElementById('componentesContenido').innerHTML = `<div class="alert alert-danger mb-0">${error.message}</div>`;
            }
        });
    });
</script>
@endpush
