@extends('controlecp::components.layouts.dashboard')

@section('dashboard-content')
@php
    $icons = [
        'asistencias' => ['fa-user-check', '#39A900'],
        'encuestas' => ['fa-clipboard-question', '#0070B8'],
        'asesorias' => ['fa-comments', '#6A1B9A'],
        'pqrs' => ['fa-comment-dots', '#E65100'],
        'certificacion' => ['fa-certificate', '#00838F'],
        'inventario' => ['fa-boxes-stacked', '#C0392B'],
        'poe' => ['fa-file-contract', '#2C3E50'],
    ];
    $descriptions = [
        'asistencias' => 'Consulta los registros de asistencia y sus estados.',
        'encuestas' => 'Revisa las encuestas registradas y su estado.',
        'asesorias' => 'Consulta las asesorías y su distribución por estado.',
        'pqrs' => 'Revisa las PQRS registradas y su estado actual.',
        'certificacion' => 'Consulta las sesiones relacionadas con certificación.',
        'inventario' => 'Revisa los elementos de inventario y su disponibilidad.',
        'poe' => 'Consulta los registros de programación POE.',
    ];
    $chartDistribution = $tipo
        ? $resultado['filas']->groupBy(fn($fila) => data_get($fila, 'estado') ?: 'Sin estado')->map(fn($filas) => $filas->count())->take(8)
        : collect();
    $chartTitle = $tipo && in_array('estado', $resultado['columnas'], true) ? 'Registros por estado' : 'Total de registros';
    $chartLabels = $chartDistribution->keys()->values();
    $chartValues = $chartDistribution->values();
    $chartColors = ['#39A900', '#0284C7', '#F59E0B', '#DC2626', '#7C3AED', '#0891B2', '#EA580C', '#475569'];
    $kpiColors = [
        ['color' => '#0284C7', 'background' => '#E0F2FE', 'border' => '#A5D8F3'],
        ['color' => '#16A34A', 'background' => '#DCFCE7', 'border' => '#A7E4B8'],
        ['color' => '#DC2626', 'background' => '#FEE2E2', 'border' => '#F7B1B1'],
        ['color' => '#7C3AED', 'background' => '#EDE9FE', 'border' => '#CFC2FA'],
    ];
    $kpiIcons = ['fa-clipboard-list', 'fa-chart-simple', 'fa-circle-check', 'fa-chart-pie'];
@endphp

<div class="reports-dashboard px-3 py-3" style="--report-color: {{ $tipo ? $icons[$tipo][1] : '#39A900' }}">
    <header class="reports-hero no-print">
        <div>
            <div class="reports-eyebrow"><i class="fas fa-chart-line me-2"></i>Control ECP · Centro de reportes</div>
            @if($tipo)
                <h1>Reporte de {{ $tipos[$tipo] }}</h1>
                <p>{{ $descriptions[$tipo] }}</p>
            @else
                <h1>Reportes y analítica</h1>
                <p>Consulta el comportamiento de la Escuela Cultura de Paz desde un solo lugar.</p>
            @endif
        </div>
        @if($tipo)
            <div class="reports-hero-actions">
                <a class="btn btn-outline-light rounded-pill px-4" href="{{ route('controlecp.administrador.reportes') }}">
                    <i class="fas fa-arrow-left me-2" aria-hidden="true"></i>Volver a reportes
                </a>
                @if($resultado['filas']->isNotEmpty())
                    <a class="btn btn-light rounded-pill px-4 shadow-sm"
                       href="{{ route('controlecp.administrador.reportes.pdf', array_merge(request()->only(['fecha_desde', 'fecha_hasta', 'usuario_id']), ['tipo' => $tipo])) }}">
                        <i class="fas fa-file-pdf me-2"></i>Descargar PDF
                    </a>
                @endif
            </div>
        @endif
    </header>

    @if(!$tipo)
    <nav class="report-type-grid no-print" aria-label="Tipos de reporte">
        @foreach($tipos as $clave => $nombre)
            <a href="{{ route('controlecp.administrador.reportes', ['tipo' => $clave]) }}"
               class="report-type"
               style="--report-color: {{ $icons[$clave][1] }}">
                <span class="report-type-icon"><i class="fas {{ $icons[$clave][0] }}"></i></span>
                <span class="report-type-copy"><strong>Reporte de {{ $nombre }}</strong><small>{{ $descriptions[$clave] }}</small><span class="report-type-link">Ver reporte <i class="fas fa-arrow-right ms-1"></i></span></span>
            </a>
        @endforeach
    </nav>
    @else

    <section class="report-toolbar no-print">
        <form method="GET" class="report-filter-form">
            <input type="hidden" name="tipo" value="{{ $tipo }}">
            <div class="report-filter-field"><label>Fecha inicial</label><input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"></div>
            <div class="report-filter-field"><label>Fecha final</label><input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"></div>
            <div class="report-filter-field"><label>Responsable</label><select name="usuario_id"><option value="">Todos los responsables</option>@foreach($usuarios as $usuario)<option value="{{ $usuario->id }}" @selected(request('usuario_id') == $usuario->id)>{{ $usuario->nickname ?: $usuario->email }}</option>@endforeach</select></div>
            <div class="report-filter-actions"><button class="btn btn-generate"><i class="fas fa-magnifying-glass me-2"></i>Buscar</button><a class="btn btn-reset" href="{{ route('controlecp.administrador.reportes', ['tipo' => $tipo]) }}" aria-label="Limpiar filtros"><i class="fas fa-rotate-left"></i></a></div>
        </form>
    </section>

    @if($errores)
        <div class="alert alert-danger border-0 shadow-sm">@foreach($errores as $error)<div><i class="fas fa-circle-exclamation me-2"></i>{{ $error }}</div>@endforeach</div>
    @endif

    <section class="report-heading">
        <div><span class="section-kicker">REPORTE ACTUAL</span><h2>{{ $tipos[$tipo] }}</h2><p>Generado el {{ now()->format('d/m/Y \a \l\a\s H:i') }} · {{ Auth::user()->nickname ?? 'Administrador' }}</p></div>
        <span class="status-pill"><i class="fas fa-circle"></i> Actualizado</span>
    </section>

    <section class="kpi-grid" aria-label="Indicadores del reporte">
        @forelse($resultado['indicadores'] as $label => $valor)
            @php
                $kpiColor = $kpiColors[$loop->index % count($kpiColors)];
                $kpiValue = is_numeric($valor) ? number_format((float) $valor, floor((float) $valor) === (float) $valor ? 0 : 1) : $valor;
            @endphp
            <article class="kpi-card" style="--kpi-color: {{ $kpiColor['color'] }}; --kpi-background: {{ $kpiColor['background'] }}; --kpi-border: {{ $kpiColor['border'] }}">
                <div class="kpi-card-top"><span>{{ $label }}</span><i class="fas {{ $kpiIcons[$loop->index % count($kpiIcons)] }}" aria-hidden="true"></i></div>
                <strong>{{ $kpiValue }}</strong>
                <small>Según filtros aplicados</small>
            </article>
        @empty
            <article class="kpi-card" style="--kpi-color: #0284C7; --kpi-background: #E0F2FE; --kpi-border: #A5D8F3">
                <div class="kpi-card-top"><span>Registros</span><i class="fas fa-database" aria-hidden="true"></i></div>
                <strong>0</strong><small>Sin información disponible</small>
            </article>
        @endforelse
    </section>

    <section class="panel-card table-panel">
        <div class="panel-header">
            <div><h3>Detalle del reporte</h3><small>{{ $resultado['filas']->count() }} registros encontrados</small></div>
            <i class="fas fa-table" aria-hidden="true"></i>
        </div>
        @if($resultado['filas']->isEmpty())
            <div class="empty-state"><i class="fas fa-folder-open"></i><strong>No se encontraron registros</strong><span>{{ $resultado['mensaje'] ?: 'No hay información que coincida con los filtros seleccionados.' }}</span></div>
        @else
            <div class="table-responsive">
                <table class="table report-table align-middle mb-0">
                    <thead><tr>@foreach($resultado['columnas'] as $columna)<th>{{ str_replace('_', ' ', $columna) }}</th>@endforeach</tr></thead>
                    <tbody>
                        @foreach($resultado['filas'] as $fila)
                            <tr>@foreach($resultado['columnas'] as $columna)<td>{{ data_get($fila, $columna) !== null && data_get($fila, $columna) !== '' ? data_get($fila, $columna) : '—' }}</td>@endforeach</tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="panel-card chart-panel">
        <div class="panel-header">
            <div><h3>{{ $chartTitle }}</h3><small>Cada color identifica una categoría; las barras comparan cuántos registros hay en cada una.</small></div>
            <i class="fas fa-chart-bar" aria-hidden="true"></i>
        </div>
        @if($chartLabels->isNotEmpty())
            <div class="chart-wrap"><canvas id="reportChart" role="img" aria-label="Gráfica de barras: {{ strtolower($chartTitle) }}"></canvas></div>
            <div class="chart-legend">
                @foreach($chartLabels as $index => $label)
                    <span><i style="--legend-color: {{ $chartColors[$index % count($chartColors)] }}"></i>{{ $label }}: <strong>{{ number_format($chartValues[$index]) }}</strong> registros</span>
                @endforeach
            </div>
        @else
            <div class="empty-state py-4"><i class="fas fa-chart-simple"></i><strong>No hay datos para graficar</strong><span>Aplica otros filtros y vuelve a consultar.</span></div>
        @endif
    </section>

    @endif
</div>
@endsection

@push('styles')
<style>
    .reports-dashboard{--navy:#001A29;--green:#39A900;color:var(--navy);min-width:0;width:100%}.reports-hero{background:linear-gradient(120deg,#001A29,#00324D);border-radius:24px;padding:2rem 2.25rem;color:#fff;display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem}.reports-eyebrow,.section-kicker{font-size:.7rem;letter-spacing:.12em;text-transform:uppercase;font-weight:700;color:#62E31D}.reports-hero h1{color:#fff;font-size:2rem;margin:.35rem 0}.reports-hero p{margin:0;color:#c7d4da}.report-type-grid{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:.65rem;margin-bottom:1.25rem}.report-type{min-width:0;background:#fff;border:1px solid #e6ebef;border-radius:15px;padding:.85rem .7rem;display:flex;align-items:center;gap:.55rem;color:var(--navy);text-decoration:none;position:relative;transition:.2s}.report-type:hover,.report-type.selected{border-color:var(--report-color);box-shadow:0 6px 18px rgba(0,26,41,.1);transform:translateY(-2px)}.report-type.selected{background:#f5fbf2}.report-type-icon{width:34px;height:34px;border-radius:10px;background:color-mix(in srgb,var(--report-color) 12%,white);color:var(--report-color);display:grid;place-items:center;flex:none}.report-type strong{display:block;font-size:.78rem}.report-type small{display:block;color:#86929b;font-size:.67rem}.report-type-arrow{margin-left:auto;font-size:.65rem;color:#adb7bd}    .report-toolbar,.panel-card,.kpi-card{background:#fff;border:1px solid #e6ebef;border-radius:18px;box-shadow:0 5px 20px rgba(0,26,41,.05)}.report-toolbar{padding:1rem 1.15rem;margin-bottom:1.5rem}.toolbar-title{display:flex;align-items:center;gap:.7rem;margin-bottom:1rem}.toolbar-title small,.panel-header small,.kpi-card small{display:block;color:#87939a;font-size:.72rem}.toolbar-icon{width:34px;height:34px;border-radius:10px;background:#edf8e9;color:var(--green);display:grid;place-items:center}.report-toolbar label{font-size:.7rem;font-weight:700;color:#68757c;display:block;margin:0 0 .25rem}.report-toolbar input,.report-toolbar select{border:1px solid #dfe6e9;border-radius:9px;padding:.55rem .7rem;width:100%;font-size:.82rem;min-width:0}.btn-generate{background:var(--green);color:#fff;border:0;border-radius:9px;font-weight:700}.btn-reset{background:#fff;border:1px solid #fff;color:#001A29;border-radius:9px;padding:.55rem .8rem}.btn-reset:hover{background:#f1f5f9;color:#001A29;border-color:#fff}.report-heading{display:flex;justify-content:space-between;align-items:end;margin-bottom:1rem}.report-heading h2{font-size:1.5rem;margin:.25rem 0}.report-heading p{margin:0;color:#87939a;font-size:.78rem}.status-pill,.table-badge{background:#edf8e9;color:#287900;border-radius:999px;padding:.4rem .75rem;font-size:.7rem;font-weight:700}.status-pill i{font-size:.45rem;margin-right:.35rem}.kpi-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1rem;margin-bottom:1.5rem}.kpi-card{min-width:0;padding:1.1rem 1.2rem}.kpi-card-top{display:flex;justify-content:space-between;color:#718087;font-size:.75rem;font-weight:600}.kpi-card>strong{display:block;font-size:1.8rem;margin:.45rem 0 .2rem}.panel-card{min-width:0;padding:1.2rem}.panel-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem}.panel-header h3{font-size:1rem;margin:0}.panel-header>i{color:var(--green);font-size:1.1rem}.chart-wrap{height:220px;position:relative}.empty-chart{position:absolute;inset:0;display:grid;place-items:center;color:#9aa6ab;font-size:.8rem}.insight-list>div{display:flex;gap:.75rem;align-items:center;padding:.65rem 0;border-bottom:1px solid #eef1f2}.insight-list>div:last-child{border:0}.insight-list p{margin:0}.insight-list strong,.insight-list small{display:block;font-size:.78rem}.insight-list small{color:#87939a;font-size:.7rem}.insight-icon{width:34px;height:34px;border-radius:10px;display:grid;place-items:center}.green{background:#edf8e9;color:#39a900}.blue{background:#edf6fb;color:#0070b8}.orange{background:#fff3e9;color:#e65100}.table-panel{padding:0;overflow:hidden}.table-panel .panel-header{padding:1.2rem 1.2rem .2rem}.report-table{font-size:.75rem;margin:0}.report-table th{background:#f7f9fa;color:#617078;text-transform:uppercase;font-size:.65rem;letter-spacing:.04em;white-space:nowrap}.report-table td,.report-table th{padding:.75rem 1rem;border-color:#eef1f2;white-space:nowrap}.empty-state{text-align:center;padding:3rem;color:#87939a}.empty-state i{display:block;font-size:2rem;color:#b8c2c6;margin-bottom:.7rem}.empty-state strong,.empty-state span{display:block}.print-footer{border-top:1px solid #ddd;margin-top:2rem;padding-top:1rem;text-align:center;font-size:.7rem}.print-filter-summary{font-size:.8rem;margin:0 0 1rem;color:#52616a}@media(max-width:1100px){.report-type-grid{grid-template-columns:repeat(4,minmax(0,1fr))}}@media(max-width:700px){.reports-hero{display:block;padding:1.4rem}.reports-hero button{margin-top:1rem}.report-type-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.kpi-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.report-heading{align-items:start;display:block}.status-pill{display:inline-block;margin-top:.7rem}}@media print{body *{visibility:hidden!important}.reports-dashboard,.reports-dashboard *{visibility:visible!important}.reports-dashboard{position:fixed;inset:0;z-index:99999;background:#fff;width:100vw!important;height:auto;overflow:visible;padding:0!important}.no-print{display:none!important}.panel-card,.kpi-card{box-shadow:none;border:1px solid #ddd}.print-filter-summary,.print-footer{display:block!important}.reports-hero{background:#fff;color:#001A29;border:0;padding:0 0 1rem}.reports-hero h1{color:#001A29}}
    .reports-dashboard .kpi-card {
        min-height: 132px;
        border: 2px solid var(--kpi-border);
        border-radius: 16px;
        background: #fff;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .reports-dashboard .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(0,50,77,.09);
        border-color: var(--kpi-color);
    }
    .reports-dashboard .kpi-card-top {
        align-items: center;
        color: #334155;
        font-size: .82rem;
        font-weight: 500;
    }
    .reports-dashboard .kpi-card-top i {
        width: 38px;
        height: 38px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        background: var(--kpi-background);
        color: var(--kpi-color) !important;
    }
    .reports-dashboard .kpi-card > strong { color: var(--kpi-color); }
    .reports-dashboard .panel-card {
        border: 1px solid #e6ebef;
        border-radius: 18px;
        background: #fff;
    }
    .reports-dashboard .panel-header > i { color: var(--report-color); }
    .reports-dashboard .table-panel { padding: 0; overflow: hidden; margin-bottom: 1.5rem; }
    .reports-dashboard .table-panel .panel-header { padding: 1.2rem 1.2rem .2rem; }
    .reports-dashboard .report-table { font-size: .75rem; margin: 0; }
    .reports-dashboard .report-table th {
        background: #f7f9fa;
        color: #617078;
        text-transform: uppercase;
        font-size: .65rem;
        letter-spacing: .04em;
        white-space: nowrap;
    }
    .reports-dashboard .report-table td,
    .reports-dashboard .report-table th { padding: .75rem 1rem; border-color: #eef1f2; white-space: nowrap; }
    .reports-dashboard .chart-wrap { height: 340px; position: relative; }
    .reports-dashboard .chart-legend { display: flex; flex-wrap: wrap; gap: .65rem 1.25rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e8edef; }
    .reports-dashboard .chart-legend span { display: flex; align-items: center; gap: .45rem; color: #475569; font-size: .78rem; }
    .reports-dashboard .chart-legend i { width: 11px; height: 11px; flex: none; border-radius: 3px; background: var(--legend-color); }
    .reports-dashboard .chart-legend strong { color: var(--navy); }
    .reports-dashboard .report-type-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .reports-dashboard .report-type { min-height: 150px; padding: 1.35rem; gap: 1.1rem; border-radius: 17px; }
    .reports-dashboard .report-type-icon { width: 56px; height: 56px; border-radius: 16px; font-size: 1.25rem; }
    .reports-dashboard .report-type-copy { display: block; min-width: 0; }
    .reports-dashboard .report-type strong { display: block; margin-bottom: .3rem; font-size: 1rem; font-weight: 800; }
    .reports-dashboard .report-type small { display: block; color: #64748b; font-size: .82rem; line-height: 1.45; }
    .reports-dashboard .report-type-link { display: inline-block; margin-top: .45rem; color: var(--report-color); font-size: .76rem; font-weight: 800; }
    .reports-dashboard .reports-hero-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: .65rem; }
    @media(max-width: 1100px) { .reports-dashboard .report-type-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media(max-width: 700px) { .reports-dashboard .reports-hero-actions { justify-content: flex-start; margin-top: 1rem; } .reports-dashboard .report-type-grid { grid-template-columns: minmax(0, 1fr); } }
    .report-filter-form {
        display: grid;
        grid-template-columns: minmax(170px, 1.1fr) minmax(170px, 1.1fr) minmax(250px, 1.55fr) 220px;
        align-items: end;
        gap: .75rem;
    }
    .report-filter-field { min-width: 0; }
    .report-filter-field label {
        display: block;
        margin: 0 0 .3rem;
        color: #fff;
        font-size: .75rem;
        font-weight: 700;
    }
    .report-filter-field input,
    .report-filter-field select {
        width: 100%;
        min-width: 0;
        height: 44px;
        padding: .55rem .8rem;
        border: 1px solid #fff;
        border-radius: 10px;
        background: #fff;
        color: var(--navy);
        font-size: .88rem;
    }
    .report-filter-actions { display: flex; gap: .55rem; min-width: 0; }
    .report-filter-actions .btn-generate { flex: 1; min-width: 0; height: 44px; white-space: nowrap; }
    .report-filter-actions .btn-reset { flex: 0 0 48px; height: 44px; display: grid; place-items: center; padding: 0; }
    @media(max-width: 900px) {
        .report-filter-form { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .report-filter-actions { grid-column: span 2; justify-content: flex-end; }
        .report-filter-actions .btn-generate { flex: 0 0 200px; }
    }
    @media(max-width: 520px) {
        .report-filter-form { grid-template-columns: minmax(0, 1fr); }
        .report-filter-actions { grid-column: auto; }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
const reportCanvas = document.getElementById('reportChart');
if (reportCanvas && typeof Chart !== 'undefined') {
    const labels = @json($chartLabels);
    const values = @json($chartValues);
    const colors = @json($chartColors);
    const valueLabelsPlugin = {
        id: 'adminReportValueLabels',
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
    new Chart(reportCanvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Registros',
                data: values,
                backgroundColor: labels.map((_, index) => colors[index % colors.length]),
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
                legend: { display: false },
                tooltip: { callbacks: { label: (context) => ` ${context.formattedValue} registros` } },
            },
            scales: {
                x: { beginAtZero: true, grace: '10%', title: { display: true, text: 'Cantidad de registros' }, ticks: { precision: 0 } },
                y: { grid: { display: false }, ticks: { autoSkip: false } },
            },
        },
        plugins: [valueLabelsPlugin],
    });
}
</script>
@endpush
