@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
<div class="container-fluid px-2 px-md-3 py-2">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item">
                        <a href="{{ route('controlecp.administrador.inicio') }}" class="text-decoration-none" style="color: #2E7D32;">
                            <i class="fas fa-house me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page" style="color: #1B5E20; font-weight: 600;">
                        Repositorio &bull; Tabulación
                    </li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0" style="color: #001A29;">
                <i class="fas fa-table me-2" style="color: #39A900;"></i>Tabulación de Encuestas
            </h2>
            <p class="text-muted small mb-0">Control ECP &bull; Resultados y análisis de las respuestas recibidas</p>
        </div>

        <div style="min-width: 260px;">
            <select class="form-select form-select-sm" id="selectorEncuesta" onchange="cambiarEncuesta()">
                <option value="1">Percepción del taller de convivencia</option>
                <option value="2">Evaluación jornada de asesorías</option>
            </select>
        </div>
    </div>

    <div class="alert alert-warning small mb-4" style="border-radius: 12px;">
        <i class="fas fa-triangle-exclamation me-1"></i>
        Vista de interfaz con datos de ejemplo — falta conectar con las respuestas reales guardadas en base de datos.
    </div>

    {{-- ===================== RESUMEN GENERAL ===================== --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-3 text-center">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-2"
                         style="width: 46px; height: 46px; background: #E8F5E9; color: #2E7D32; font-size: 1.15rem;">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4 class="fw-bold mb-0" style="color:#001A29;">42</h4>
                    <p class="small text-muted mb-0">Respuestas recibidas</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-3 text-center">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-2"
                         style="width: 46px; height: 46px; background: #EBF5FB; color: #2980B9; font-size: 1.15rem;">
                        <i class="fas fa-list-ol"></i>
                    </div>
                    <h4 class="fw-bold mb-0" style="color:#001A29;">6</h4>
                    <p class="small text-muted mb-0">Preguntas evaluadas</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-3 text-center">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-2"
                         style="width: 46px; height: 46px; background: #FEF9E7; color: #D68910; font-size: 1.15rem;">
                        <i class="fas fa-percent"></i>
                    </div>
                    <h4 class="fw-bold mb-0" style="color:#001A29;">84%</h4>
                    <p class="small text-muted mb-0">Tasa de participación</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-3 text-center">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-2"
                         style="width: 46px; height: 46px; background: #F4ECF7; color: #8E44AD; font-size: 1.15rem;">
                        <i class="fas fa-star"></i>
                    </div>
                    <h4 class="fw-bold mb-0" style="color:#001A29;">4.3 / 5</h4>
                    <p class="small text-muted mb-0">Satisfacción promedio</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== PREGUNTA 1: OPCIÓN MÚLTIPLE ===================== --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
            <h5 class="fw-bold mb-1" style="color: #001A29;">1. ¿Cómo calificarías el taller en general?</h5>
            <span class="badge rounded-pill bg-light text-dark border small">Opción múltiple &bull; 42 respuestas</span>
        </div>
        <div class="card-body px-4 pb-4">
            @php
                $opciones = [
                    ['label' => 'Excelente', 'valor' => 60, 'color' => '#2E7D32'],
                    ['label' => 'Bueno', 'valor' => 28, 'color' => '#66BB6A'],
                    ['label' => 'Regular', 'valor' => 9, 'color' => '#FDD835'],
                    ['label' => 'Malo', 'valor' => 3, 'color' => '#E53935'],
                ];
            @endphp
            @foreach ($opciones as $op)
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="fw-semibold text-dark">{{ $op['label'] }}</span>
                        <span class="text-muted">{{ $op['valor'] }}%</span>
                    </div>
                    <div class="progress" style="height: 10px; border-radius: 10px; background-color: #F1F1F1;">
                        <div class="progress-bar" role="progressbar" style="width: {{ $op['valor'] }}%; background-color: {{ $op['color'] }}; border-radius: 10px;"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ===================== PREGUNTA 2: ESCALA ===================== --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
            <h5 class="fw-bold mb-1" style="color: #001A29;">2. ¿Qué tan útil fue el contenido para tu día a día?</h5>
            <span class="badge rounded-pill bg-light text-dark border small">Escala 1 a 5 &bull; 42 respuestas</span>
        </div>
        <div class="card-body px-4 pb-4">
            <div class="d-flex align-items-end gap-3" style="height: 140px;">
                @php $escala = [1 => 2, 2 => 4, 3 => 10, 4 => 15, 5 => 11]; @endphp
                @foreach ($escala as $valor => $cantidad)
                    @php $alturaPct = ($cantidad / 15) * 100; @endphp
                    <div class="d-flex flex-column align-items-center flex-grow-1">
                        <div style="height: {{ $alturaPct }}%; width: 100%; background-color: #39A900; border-radius: 8px 8px 0 0; min-height: 6px;"></div>
                        <span class="small text-muted mt-1">{{ $valor }} ★</span>
                        <span class="small fw-bold text-dark">{{ $cantidad }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ===================== PREGUNTA 3: SÍ / NO ===================== --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
            <h5 class="fw-bold mb-1" style="color: #001A29;">3. ¿Recomendarías esta actividad a otro compañero?</h5>
            <span class="badge rounded-pill bg-light text-dark border small">Sí / No &bull; 42 respuestas</span>
        </div>
        <div class="card-body px-4 pb-4">
            <div class="row g-3 text-center">
                <div class="col-6">
                    <div class="p-3 rounded-4" style="background-color: #E8F5E9;">
                        <h3 class="fw-bold mb-0" style="color:#2E7D32;">91%</h3>
                        <span class="small text-muted">Sí</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-4" style="background-color: #FDECEA;">
                        <h3 class="fw-bold mb-0" style="color:#C62828;">9%</h3>
                        <span class="small text-muted">No</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== PREGUNTA 4: TEXTO ABIERTO ===================== --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
            <h5 class="fw-bold mb-1" style="color: #001A29;">4. ¿Qué mejorarías del taller?</h5>
            <span class="badge rounded-pill bg-light text-dark border small">Texto corto &bull; Últimas respuestas</span>
        </div>
        <div class="card-body px-4 pb-4">
            <ul class="list-group list-group-flush">
                <li class="list-group-item px-0 small text-dark">"Más tiempo para las dinámicas grupales."</li>
                <li class="list-group-item px-0 small text-dark">"Estuvo muy bien, seguiría igual."</li>
                <li class="list-group-item px-0 small text-dark">"Contar con un espacio más amplio."</li>
                <li class="list-group-item px-0 small text-dark">"Incluir más ejemplos prácticos."</li>
            </ul>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="button" class="btn text-white rounded-pill px-4" style="background-color: #2E7D32;" onclick="alert('Falta conectar la exportación real a Excel/PDF.')">
            <i class="fas fa-file-export me-1"></i> Exportar resultados
        </button>
    </div>

</div>

<script>
    function cambiarEncuesta() {
        // Placeholder: aquí se hará la petición real (fetch/ajax) para traer
        // la tabulación de la encuesta seleccionada cuando exista el backend.
        alert('Falta conectar la carga dinámica de resultados según la encuesta seleccionada.');
    }
</script>
@endsection