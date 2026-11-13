@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
@php
    $colores = [
        'asistencias' => '#39A900',
        'asesorias' => '#6A1B9A',
        'acompanamientos' => '#0070B8',
        'certificacion' => '#00838F',
        'encuestas' => '#0070B8',
        'inventario' => '#C0392B',
        'pqrs' => '#E65100',
    ];
    $descripciones = [
        'asistencias' => 'Consulta la asistencia registrada en las actividades de tus fichas.',
        'asesorias' => 'Revisa el estado y el historial de las asesorías que has atendido.',
        'acompanamientos' => 'Consulta sesiones, avances y faltas de tus acompañamientos.',
        'certificacion' => 'Revisa los momentos certificados en las fichas a tu cargo.',
        'encuestas' => 'Consulta resultados y respuestas de las encuestas de tus actividades.',
        'inventario' => 'Revisa los elementos e inventario que has utilizado.',
        'pqrs' => 'Consulta las PQRS que has atendido.',
    ];
    $color = $tipo ? $colores[$tipo] : '#39A900';
@endphp

<div class="instructor-reports px-3 py-3" style="--report-color: {{ $color }}">
    <header class="reports-hero">
        <div>
            <div class="reports-eyebrow"><i class="fas fa-chart-line me-2"></i>Control ECP · Centro de reportes</div>
            @if($tipo)
                <h1>{{ $tipos[$tipo] }}</h1>
                <p>{{ $descripciones[$tipo] }}</p>
            @else
                <h1>Reportes</h1>
                <p>Consulta y analiza la información de tu gestión como instructor.</p>
            @endif
        </div>
        @if($tipo)
            <div class="reports-hero-actions">
                <a class="btn btn-outline-light rounded-pill px-4" href="{{ route('controlecp.instructor.reportes') }}">
                    <i class="fas fa-arrow-left me-2" aria-hidden="true"></i>Volver a reportes
                </a>
                @if($resultado['filas']->isNotEmpty())
                    <a class="btn btn-light rounded-pill px-4 shadow-sm"
                       href="{{ route('controlecp.instructor.reportes.pdf', array_merge(request()->only(['fecha_desde', 'fecha_hasta', 'ficha_id']), ['tipo' => $tipo])) }}">
                        <i class="fas fa-file-pdf me-2"></i>Exportar PDF
                    </a>
                @endif
            </div>
        @endif
    </header>

    @if(!$tipo)
    <nav class="report-type-grid" aria-label="Tipos de reporte">
        @foreach($tipos as $clave => $nombre)
            <a href="{{ route('controlecp.instructor.reportes', ['tipo' => $clave]) }}"
               class="report-type {{ $tipo === $clave ? 'selected' : '' }}"
               style="--report-color: {{ $colores[$clave] }}">
                <span class="report-type-icon"><i class="fas {{ $iconos[$clave] }}" aria-hidden="true"></i></span>
                <span class="report-type-copy">
                    <strong>Reporte de {{ $nombre }}</strong>
                    <small>{{ $descripciones[$clave] }}</small>
                    <span class="report-type-link">Ver reporte <i class="fas fa-arrow-right ms-1" aria-hidden="true"></i></span>
                </span>
            </a>
        @endforeach
    </nav>
    @else
    <section class="report-toolbar">
        <form method="GET" action="{{ route('controlecp.instructor.reportes') }}" class="report-filter-form">
            <input type="hidden" name="tipo" value="{{ $tipo }}">
            <div class="report-filter-field">
                <label for="fechaDesde">Fecha inicial</label>
                <input id="fechaDesde" type="date" name="fecha_desde" value="{{ request('fecha_desde') }}">
            </div>
            <div class="report-filter-field">
                <label for="fechaHasta">Fecha final</label>
                <input id="fechaHasta" type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}">
            </div>
            <div class="report-filter-field">
                <label for="fichaId">Ficha</label>
                <select id="fichaId" name="ficha_id">
                    <option value="">Todas mis fichas</option>
                    @foreach($fichas as $ficha)
                        <option value="{{ $ficha->id }}" @selected(request('ficha_id') == $ficha->id)>
                            {{ $ficha->code }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="report-filter-actions">
                <button class="btn btn-generate" type="submit"><i class="fas fa-magnifying-glass me-2"></i>Buscar</button>
                <a class="btn btn-reset" href="{{ route('controlecp.instructor.reportes', ['tipo' => $tipo]) }}" title="Limpiar filtros" aria-label="Limpiar filtros">
                    <i class="fas fa-rotate-left"></i>
                </a>
            </div>
        </form>
        @if($errors->any())
            <div class="alert alert-danger mt-3 mb-0 py-2">
                @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning mt-3 mb-0 py-2">{{ session('warning') }}</div>
        @endif
    </section>

    <section class="report-heading">
        <div>
            <span class="section-kicker">REPORTE ACTUAL</span>
            <h2>{{ $tipos[$tipo] }}</h2>
            <p>Generado el {{ now('America/Bogota')->format('d/m/Y g:i A') }} · {{ Auth::user()->nickname ?: Auth::user()->name }}</p>
        </div>
        <span class="status-pill"><i class="fas fa-circle"></i> Solo tus registros</span>
    </section>

    @php
        $iconosKpi = ['fa-clipboard-list', 'fa-user-check', 'fa-user-xmark', 'fa-chart-pie'];
        $coloresKpi = [
            ['color' => '#0284C7', 'fondo' => '#E0F2FE', 'borde' => '#A5D8F3'],
            ['color' => '#16A34A', 'fondo' => '#DCFCE7', 'borde' => '#A7E4B8'],
            ['color' => '#DC2626', 'fondo' => '#FEE2E2', 'borde' => '#F7B1B1'],
            ['color' => '#7C3AED', 'fondo' => '#EDE9FE', 'borde' => '#CFC2FA'],
        ];
    @endphp
    @if(count($resultado['indicadores']))
        <section class="report-kpi-grid" aria-label="Indicadores del reporte">
            @foreach($resultado['indicadores'] as $etiqueta => $valor)
                @php
                    $valorKpi = is_numeric($valor)
                        ? number_format((float) $valor, floor((float) $valor) === (float) $valor ? 0 : 1)
                        : $valor;
                @endphp
                @php $colorKpi = $coloresKpi[$loop->index % count($coloresKpi)]; @endphp
                <article class="report-kpi-card" style="--kpi-color: {{ $colorKpi['color'] }}; --kpi-bg: {{ $colorKpi['fondo'] }}; --kpi-border: {{ $colorKpi['borde'] }}">
                    <div class="report-kpi-label">
                        <span>{{ $etiqueta }}</span>
                        <span class="report-kpi-icon"><i class="fas {{ $iconosKpi[$loop->index % count($iconosKpi)] }}" aria-hidden="true"></i></span>
                    </div>
                    <strong>{{ $valorKpi }}</strong>
                    <small>Según filtros aplicados</small>
                </article>
            @endforeach
        </section>
    @endif

    <section class="panel-card table-panel">
        <div class="panel-header">
            <div><h3>Detalle del reporte</h3><small>{{ $resultado['filas']->count() }} registros encontrados</small></div>
            <i class="fas {{ $iconos[$tipo] }}"></i>
        </div>
        @if($resultado['filas']->isEmpty())
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <strong>No se encontraron registros</strong>
                <span>No hay información que coincida con los filtros seleccionados.</span>
            </div>
        @else
            <div class="table-responsive">
                <table class="table report-table align-middle mb-0">
                    <thead><tr>@foreach($resultado['columnas'] as $etiqueta => $columna)<th>{{ $etiqueta }}</th>@endforeach</tr></thead>
                    <tbody>
                        @foreach($filasPaginadas as $fila)
                            <tr>
                                @foreach($resultado['columnas'] as $columna)
                                    <td>{{ $fila[$columna] !== null && $fila[$columna] !== '' ? $fila[$columna] : '—' }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($filasPaginadas->hasPages())
                <div class="d-flex justify-content-center p-3 border-top">
                    @if ($filasPaginadas->hasPages())
                        <nav aria-label="Paginación de reportes">
                            <ul class="pagination justify-content-center">
                                @if ($filasPaginadas->onFirstPage())
                                    <li class="page-item disabled"><span class="page-link" aria-hidden="true">&lsaquo;</span></li>
                                @else
                                    <li class="page-item"><a class="page-link" href="{{ $filasPaginadas->previousPageUrl() }}" aria-label="Página anterior">&lsaquo;</a></li>
                                @endif
                                @php
                                $ultimaPaginaMostrada = 0;
                            @endphp
                                @for ($pagina = 1; $pagina <= $filasPaginadas->lastPage(); $pagina++)
                                    @if ($pagina === 1 || $pagina === $filasPaginadas->lastPage() || abs($pagina - $filasPaginadas->currentPage()) <= 1)
                                        @if ($ultimaPaginaMostrada > 0 && $pagina > $ultimaPaginaMostrada + 1)
                                            <li class="page-item disabled"><span class="page-link">…</span></li>
                                        @endif
                                        <li class="page-item {{ $pagina === $filasPaginadas->currentPage() ? 'active' : '' }}">
                                            <a class="page-link" href="{{ $filasPaginadas->url($pagina) }}" @if($pagina === $filasPaginadas->currentPage()) aria-current="page" @endif>{{ $pagina }}</a>
                                        </li>
                                        @php
                                    $ultimaPaginaMostrada = $pagina;
                                @endphp
                                    @endif
                                @endfor
                                @if ($filasPaginadas->hasMorePages())
                                    <li class="page-item"><a class="page-link" href="{{ $filasPaginadas->nextPageUrl() }}" aria-label="Página siguiente">&rsaquo;</a></li>
                                @else
                                    <li class="page-item disabled"><span class="page-link" aria-hidden="true">&rsaquo;</span></li>
                                @endif
                            </ul>
                        </nav>
                    @endif
                </div>
            @endif
        @endif
    </section>
    @php
        $filasGrafica = $resultado['filas'];
        $tituloGrafica = '';
        $descripcionGrafica = '';
        $etiquetaDatosGrafica = 'Cantidad';
        $etiquetasGrafica = [];
        $valoresGrafica = [];
        $escalasGrafica = [
            'x' => ['beginAtZero' => true, 'grace' => '10%', 'title' => ['display' => true, 'text' => 'Cantidad'], 'ticks' => ['precision' => 0]],
            'y' => ['grid' => ['display' => false], 'ticks' => ['autoSkip' => false]],
        ];
        $coloresGrafica = ['#39A900', '#0284C7', '#F59E0B', '#DC2626', '#7C3AED', '#0891B2', '#EA580C'];

        if ($tipo === 'asistencias') {
            $tituloGrafica = 'Presentes y ausencias';
            $descripcionGrafica = 'Compara el total de aprendices presentes y ausentes en las asistencias filtradas.';
            $etiquetaDatosGrafica = 'Aprendices';
            $etiquetasGrafica = ['Presentes', 'Ausencias'];
            $valoresGrafica = [(int) $filasGrafica->sum('presentes'), (int) $filasGrafica->sum('ausencias')];
        } elseif ($tipo === 'asesorias') {
            $tituloGrafica = 'Asesorías por estado';
            $descripcionGrafica = 'Distribución de las asesorías según su estado actual.';
            $etiquetaDatosGrafica = 'Asesorías';
            $etiquetasGrafica = $distribucion->keys()->values()->all();
            $valoresGrafica = $distribucion->values()->map(fn ($valor) => (int) $valor)->all();
        } elseif ($tipo === 'acompanamientos') {
            $tituloGrafica = 'Sesiones realizadas y faltas';
            $descripcionGrafica = 'Compara las sesiones realizadas con las faltas registradas.';
            $etiquetaDatosGrafica = 'Sesiones';
            $etiquetasGrafica = ['Sesiones realizadas', 'Faltas'];
            $valoresGrafica = [(int) $filasGrafica->sum('realizadas'), (int) $filasGrafica->sum('faltas')];
        } elseif ($tipo === 'certificacion') {
            $tituloGrafica = 'Certificaciones por momento';
            $descripcionGrafica = 'Cantidad de certificaciones registradas en cada momento.';
            $etiquetaDatosGrafica = 'Certificaciones';
            $distribucionGrafica = $filasGrafica->countBy('momento');
            $etiquetasGrafica = $distribucionGrafica->keys()->values()->all();
            $valoresGrafica = $distribucionGrafica->values()->map(fn ($valor) => (int) $valor)->all();
        } elseif ($tipo === 'encuestas') {
            $tituloGrafica = 'Respuestas por encuesta';
            $descripcionGrafica = 'Se muestran hasta 10 encuestas, ordenadas desde la que recibió más respuestas.';
            $etiquetaDatosGrafica = 'Respuestas';
            $filasGrafica = $filasGrafica->sortByDesc('respuestas')->take(10)->values();
            $etiquetasGrafica = $filasGrafica->pluck('encuesta')->all();
            $valoresGrafica = $filasGrafica->map(fn ($fila) => (int) $fila['respuestas'])->all();
        } elseif ($tipo === 'inventario') {
            $tituloGrafica = 'Unidades por tipo de movimiento';
            $descripcionGrafica = 'Total de unidades movidas en cada tipo de movimiento de inventario.';
            $etiquetaDatosGrafica = 'Unidades';
            $distribucionGrafica = $filasGrafica->groupBy('movimiento')->map(
                fn ($movimientos) => (int) $movimientos->sum('cantidad')
            );
            $etiquetasGrafica = $distribucionGrafica->keys()->values()->all();
            $valoresGrafica = $distribucionGrafica->values()->all();
        } elseif ($tipo === 'pqrs') {
            $tituloGrafica = 'Gestiones de PQRS';
            $descripcionGrafica = 'Distribución de las gestiones realizadas entre cambios de estado y respuestas.';
            $etiquetaDatosGrafica = 'Gestiones';
            $distribucionGrafica = $filasGrafica->countBy('evento');
            $etiquetasGrafica = $distribucionGrafica->keys()->values()->all();
            $valoresGrafica = $distribucionGrafica->values()->map(fn ($valor) => (int) $valor)->all();
        }
        $coloresPorBarra = array_map(
            fn ($indice) => $coloresGrafica[$indice % count($coloresGrafica)],
            array_keys($etiquetasGrafica)
        );
    @endphp
    <section class="panel-card chart-panel">
        <div class="panel-header">
            <div><h3>{{ $tituloGrafica }}</h3><small>{{ $descripcionGrafica }}</small></div>
            <i class="fas fa-chart-column"></i>
        </div>
        @if($filasGrafica->isNotEmpty() && count($valoresGrafica))
            <div class="report-chart-wrap"><canvas id="instructorReportChart" role="img" aria-label="Gráfica de {{ strtolower($tipos[$tipo]) }}"></canvas></div>
            <div class="report-chart-legend" aria-label="Leyenda de la gráfica">
                @foreach($etiquetasGrafica as $indice => $etiqueta)
                    <span class="report-chart-legend-item">
                        <i style="--legend-color: {{ $coloresPorBarra[$indice] }}" aria-hidden="true"></i>
                        <span>{{ $etiqueta }}: <strong>{{ number_format($valoresGrafica[$indice]) }}</strong> {{ strtolower($etiquetaDatosGrafica) }}</span>
                    </span>
                @endforeach
            </div>
        @else
            <div class="empty-state py-4">
                <i class="fas fa-chart-simple"></i>
                <strong>No hay datos para graficar</strong>
                <span>Aplica otros filtros o selecciona otro tipo de reporte.</span>
            </div>
        @endif
    </section>
    @endif
</div>
@endsection

@push('scripts')
@if($tipo)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const canvas = document.getElementById('instructorReportChart');
        if (!canvas || typeof Chart === 'undefined') return;

        const labels = @json($etiquetasGrafica);
        const values = @json($valoresGrafica);
        const dataLabel = @json($etiquetaDatosGrafica);
        const valueLabelsPlugin = {
            id: 'reportValueLabels',
            afterDatasetsDraw(chart) {
                const context = chart.ctx;
                context.save();
                context.fillStyle = '#334155';
                context.font = '600 12px sans-serif';
                context.textAlign = 'left';
                context.textBaseline = 'middle';

                chart.getDatasetMeta(0).data.forEach((element, index) => {
                    const position = element.getProps(['x', 'y'], true);
                    context.fillText(values[index], position.x + 8, position.y);
                });
                context.restore();
            },
        };

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: dataLabel,
                    data: values,
                    backgroundColor: @json($coloresPorBarra),
                    borderColor: '#ffffff',
                    borderWidth: 2,
                    borderRadius: 7,
                    maxBarThickness: 54,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => ` ${context.label}: ${context.formattedValue} ${dataLabel.toLowerCase()}`,
                        },
                    },
                },
                scales: @json($escalasGrafica),
            },
            plugins: [valueLabelsPlugin],
        });
    });
</script>
@endif
@endpush

@push('styles')
<style>
    .instructor-reports{--navy:#001A29;--green:#39A900;color:var(--navy);min-width:0;width:100%}
    .reports-hero{background:linear-gradient(120deg,#001A29,#00324D);border-radius:24px;padding:2rem 2.25rem;color:#fff;display:flex;justify-content:space-between;align-items:center;gap:1rem;margin-bottom:1.25rem}
    .reports-eyebrow,.section-kicker{font-size:.7rem;letter-spacing:.12em;text-transform:uppercase;font-weight:700;color:#62E31D}
    .reports-hero h1{color:#fff;font-size:2rem;margin:.35rem 0}
    .reports-hero p{margin:0;color:#c7d4da}
    .reports-hero-actions{display:flex;flex-wrap:wrap;align-items:center;justify-content:flex-end;gap:.65rem}
    .report-type-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1rem;margin-bottom:1.5rem}
    .report-type{min-width:0;min-height:150px;background:#fff;border:1px solid #e6ebef;border-radius:17px;padding:1.35rem;display:flex;align-items:center;gap:1.1rem;color:var(--navy);text-decoration:none;transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease}
    .report-type:hover,.report-type.selected{border-color:color-mix(in srgb,var(--report-color) 42%,#e6ebef);box-shadow:0 8px 22px rgba(0,26,41,.09);transform:translateY(-2px)}
    .report-type.selected{background:linear-gradient(145deg,color-mix(in srgb,var(--report-color) 5%,#fff),#fff 72%)}
    .report-type-icon{width:56px;height:56px;border-radius:16px;background:color-mix(in srgb,var(--report-color) 12%,white);color:var(--report-color);display:grid;place-items:center;flex:none;font-size:1.25rem}
    .report-type-copy{display:block;min-width:0}
    .report-type strong{display:block;margin-bottom:.3rem;color:#111827;font-size:1rem;font-weight:800}
    .report-type small{display:block;color:#64748b;font-size:.82rem;line-height:1.45}
    .report-type-link{display:inline-block;margin-top:.45rem;color:var(--report-color);font-size:.76rem;font-weight:800}
    .report-toolbar,.panel-card{background:#fff;border:1px solid #e6ebef;border-radius:18px;box-shadow:0 5px 20px rgba(0,26,41,.05)}
    .report-toolbar{padding:1rem 1.15rem;margin-bottom:1.5rem;background:linear-gradient(135deg,#001a29 0%,#052b3d 100%)}
    .report-filter-form{display:grid;grid-template-columns:minmax(170px,1fr) minmax(170px,1fr) minmax(200px,1.3fr) 220px;align-items:end;gap:.75rem}
    .report-filter-field{min-width:0}.report-filter-field label{display:block;margin:0 0 .3rem;color:#fff;font-size:.75rem;font-weight:700}
    .report-filter-field input,.report-filter-field select{width:100%;min-width:0;height:44px;padding:.55rem .8rem;border:1px solid #fff;border-radius:10px;background:#fff;color:var(--navy);font-size:.88rem}
    .report-filter-actions{display:flex;gap:.55rem;min-width:0}.btn-generate{flex:1;background:#39A900;color:#fff;border:0;border-radius:10px;font-weight:700}.btn-generate:hover{background:#318f00;color:#fff}
    .btn-reset{flex:0 0 48px;height:44px;display:grid;place-items:center;background:#fff;border:1px solid #fff;color:#001A29;border-radius:10px}
    .report-heading{display:flex;justify-content:space-between;align-items:end;margin-bottom:1rem}.report-heading h2{font-size:1.5rem;margin:.25rem 0}.report-heading p{margin:0;color:#87939a;font-size:.78rem}
    .status-pill{background:#edf8e9;color:#287900;border-radius:999px;padding:.4rem .75rem;font-size:.7rem;font-weight:700;white-space:nowrap}.status-pill i{font-size:.45rem;margin-right:.35rem}
    .report-kpi-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1rem;margin-bottom:1.5rem}
    .report-kpi-card{min-width:0;min-height:132px;padding:1rem 1.1rem;background:#fff;border:2px solid var(--kpi-border);border-radius:16px;transition:transform .18s ease,box-shadow .18s ease,border-color .18s ease}
    .report-kpi-card:hover{transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,50,77,.09);border-color:var(--kpi-color)}
    .report-kpi-label{display:flex;align-items:center;justify-content:space-between;gap:.6rem;color:#334155;font-size:.82rem;font-weight:500}
    .report-kpi-icon{width:38px;height:38px;flex:none;display:grid;place-items:center;border-radius:50%;background:var(--kpi-bg);color:var(--kpi-color);font-size:.9rem}
    .report-kpi-card strong{display:block;margin-top:.05rem;color:var(--kpi-color);font-size:1.65rem;line-height:1.25}
    .report-kpi-card small{display:block;color:#64748b;font-size:.78rem}
    .panel-card{border:1px solid color-mix(in srgb,var(--report-color) 28%,#e6ebef);border-radius:20px;background:linear-gradient(145deg,color-mix(in srgb,var(--report-color) 5%,#fff),#fff 62%)}
    .panel-header>i{color:var(--report-color)}.panel-header small{display:block;color:#87939a;font-size:.72rem}
    .panel-card{padding:1.2rem;margin-bottom:1.5rem}.panel-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem}.panel-header h3{font-size:1rem;margin:0}
    .report-chart-wrap{position:relative;width:100%;height:340px}
    .report-chart-legend{display:flex;flex-wrap:wrap;gap:.65rem 1.25rem;margin-top:1rem;padding-top:1rem;border-top:1px solid #e8edef}
    .report-chart-legend-item{display:flex;align-items:center;gap:.45rem;color:#475569;font-size:.78rem}
    .report-chart-legend-item i{width:11px;height:11px;flex:none;border-radius:3px;background:var(--legend-color)}
    .report-chart-legend-item strong{color:var(--navy)}
    .table-panel{padding:0;overflow:hidden}.table-panel .panel-header{padding:1.2rem 1.2rem .2rem}.report-table{font-size:.78rem;margin:0}
    .report-table th{background:#f7f9fa;color:#617078;text-transform:uppercase;font-size:.66rem;letter-spacing:.04em;white-space:nowrap}
    .report-table td,.report-table th{padding:.75rem 1rem;border-color:#eef1f2}
    .empty-state{text-align:center;padding:3rem;color:#87939a}.empty-state i{display:block;font-size:2rem;color:#b8c2c6;margin-bottom:.7rem}.empty-state strong,.empty-state span{display:block}
    @media(max-width:1100px){.report-type-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:900px){.report-filter-form{grid-template-columns:repeat(2,minmax(0,1fr))}.report-filter-actions{grid-column:span 2;justify-content:flex-end}.report-filter-actions .btn-generate{flex:0 0 200px}}
    @media(max-width:700px){.reports-hero{display:block;padding:1.4rem}.reports-hero-actions{justify-content:flex-start;margin-top:1rem}.report-heading{align-items:start;display:block}.status-pill{display:inline-block;margin-top:.7rem}.report-kpi-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:520px){.report-filter-form{grid-template-columns:minmax(0,1fr)}.report-filter-actions{grid-column:auto}.report-filter-actions .btn-generate{flex:1}}
</style>
@endpush
