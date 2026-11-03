{{-- Consulta de momentos para el perfil instructor. --}}
@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .momento-consulta {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 6px 22px rgba(0, 26, 41, .08);
        overflow: hidden;
    }

    .momento-consulta-header {
        color: #fff;
        padding: 1.35rem 1.5rem;
    }

    .momento-consulta-toggle {
        cursor: pointer;
        transition: filter .2s ease;
    }

    .momento-consulta-toggle:hover,
    .momento-consulta-toggle:focus-visible {
        filter: brightness(1.08);
    }

    .momento-actividades {
        display: none;
        background: #f8faf9;
    }

    .momento-actividades.is-visible {
        display: block;
    }

    .actividad-consulta {
        border-bottom: 1px solid #e5ece8;
        padding: .75rem 1rem;
    }

    .actividad-consulta:last-child {
        border-bottom: 0;
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.instructor.inicio') }}" class="text-decoration-none" style="color:#39A900;">
                            <i class="fas fa-house me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item active fw-semibold" style="color:#001A29;">Momentos</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color:#001A29;">
                <i class="fas fa-star me-2" style="color:#39A900;"></i>Momentos del programa
            </h2>
            <p class="text-muted small mb-0">
                Consulta la organización de los momentos y sus actividades.
            </p>
        </div>
    </div>

    @if($momentos->isEmpty())
        <div class="alert alert-info border-0 shadow-sm rounded-4">
            No hay momentos registrados para consultar.
        </div>
    @else
        <div class="row g-4">
            @foreach($momentos as $momento)
                @php
                    $color = $momento->color ?: '#39A900';
                    $actividadesId = 'actividades-momento-' . $momento->id;
                @endphp
                <div class="col-12 col-md-6 col-xl-4">
                    <article class="card momento-consulta h-100">
                        <div class="momento-consulta-header momento-consulta-toggle"
                             style="background: linear-gradient(135deg, #001A29 0%, {{ $color }} 100%);"
                             role="button"
                             tabindex="0"
                             aria-expanded="false"
                             aria-controls="{{ $actividadesId }}"
                             data-target="{{ $actividadesId }}">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <span class="badge bg-white text-dark rounded-pill mb-2">
                                        Momento {{ str_pad($momento->orden, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                    <h4 class="fw-bold mb-1">{{ $momento->nombre }}</h4>
                                </div>
                                <i class="fas {{ $momento->icono ?: 'fa-star' }} fs-4"></i>
                            </div>
                            <p class="small mb-0 text-white-50">
                                {{ $momento->actividades_count }}
                                {{ $momento->actividades_count === 1 ? 'actividad' : 'actividades' }}
                                <i class="fas fa-chevron-down ms-1"></i>
                            </p>
                        </div>

                        <div class="card-body">
                            <p class="small text-muted mb-0">
                                {{ $momento->descripcion ?: 'Sin descripción disponible.' }}
                            </p>
                        </div>

                        <div id="{{ $actividadesId }}" class="momento-actividades" aria-hidden="true">
                            @forelse($momento->actividades as $actividad)
                                <div class="actividad-consulta d-flex align-items-center gap-2">
                                    <span class="badge rounded-pill text-bg-light border">{{ $actividad->numero }}</span>
                                    <span class="small fw-semibold text-dark">{{ $actividad->nombre }}</span>
                                </div>
                            @empty
                                <div class="p-3 text-muted small">Este momento no tiene actividades activas.</div>
                            @endforelse
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.momento-consulta-toggle').forEach((toggle) => {
        const alternar = () => {
            const panel = document.getElementById(toggle.dataset.target);
            if (!panel) return;

            const visible = panel.classList.toggle('is-visible');
            toggle.setAttribute('aria-expanded', visible ? 'true' : 'false');
            panel.setAttribute('aria-hidden', visible ? 'false' : 'true');
        };

        toggle.addEventListener('click', alternar);
        toggle.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                alternar();
            }
        });
    });
</script>
@endpush
