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
    $chartLabels = $resultado['filas']->take(8)->map(fn($fila, $i) => data_get($fila, 'estado') ?: 'Registro ' . ($i + 1))->values();
    $chartValues = $resultado['filas']->take(8)->map(fn() => 1)->values();
@endphp

<div class="reports-dashboard">
    <header class="reports-hero no-print">
        <div>
            <div class="reports-eyebrow"><i class="fas fa-chart-line me-2"></i>Control ECP · Centro de reportes</div>
            <h1>Reportes y analítica</h1>
            <p>Consulta el comportamiento de la Escuela Cultura de Paz desde un solo lugar.</p>
        </div>
        <button type="button" class="btn btn-light rounded-pill px-4 shadow-sm" onclick="window.print()">
            <i class="fas fa-file-pdf me-2"></i>Exportar PDF
        </button>
    </header>

    <div class="report-type-grid no-print">
        @foreach($tipos as $clave => $nombre)
            <a href="{{ route('controlecp.administrador.reportes', ['tipo' => $clave]) }}"
               class="report-type {{ $tipo === $clave ? 'selected' : '' }}"
               style="--report-color: {{ $icons[$clave][1] }}">
                <span class="report-type-icon"><i class="fas {{ $icons[$clave][0] }}"></i></span>
                <span><strong>{{ $nombre }}</strong><small>Ver reporte</small></span>
                <i class="fas fa-arrow-up-right-from-square report-type-arrow"></i>
            </a>
        @endforeach
    </div>

    <section class="report-toolbar no-print">
        <div class="toolbar-title"><span class="toolbar-icon"><i class="fas fa-sliders"></i></span><div><strong>Filtros del reporte</strong><small>{{ $tipos[$tipo] }} · Personaliza la consulta</small></div></div>
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="tipo" value="{{ $tipo }}">
            <div class="col-sm-6 col-lg-2"><label>Fecha inicial</label><input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"></div>
            <div class="col-sm-6 col-lg-2"><label>Fecha final</label><input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"></div>
            <div class="col-sm-6 col-lg-3"><label>Responsable</label><select name="usuario_id"><option value="">Todos los responsables</option>@foreach($usuarios as $usuario)<option value="{{ $usuario->id }}" @selected(request('usuario_id') == $usuario->id)>{{ $usuario->nickname ?: $usuario->email }}</option>@endforeach</select></div>
            <div class="col-sm-6 col-lg-2"><label>Estado</label><input name="estado" value="{{ request('estado') }}" placeholder="Todos"></div>
            <div class="col-lg-3 d-flex gap-2"><button class="btn btn-generate flex-grow-1"><i class="fas fa-magnifying-glass me-2"></i>Generar reporte</button><a class="btn btn-reset" href="{{ route('controlecp.administrador.reportes', ['tipo' => $tipo]) }}"><i class="fas fa-rotate-left"></i></a></div>
        </form>
    </section>

    @if($errores)
        <div class="alert alert-danger border-0 shadow-sm">@foreach($errores as $error)<div><i class="fas fa-circle-exclamation me-2"></i>{{ $error }}</div>@endforeach</div>
    @endif

    <section class="report-heading">
        <div><span class="section-kicker">REPORTE ACTUAL</span><h2>{{ $tipos[$tipo] }}</h2><p>Generado el {{ now()->format('d/m/Y \a \l\a\s H:i') }} · {{ Auth::user()->nickname ?? 'Administrador' }}</p></div>
        <span class="status-pill"><i class="fas fa-circle"></i> Actualizado</span>
    </section>

    <div class="kpi-grid">
        @forelse($resultado['indicadores'] as $label => $valor)
            <div class="kpi-card"><div class="kpi-card-top"><span>{{ $label }}</span><i class="fas {{ $icons[$tipo][0] }}" style="color:{{ $icons[$tipo][1] }}"></i></div><strong>{{ number_format($valor) }}</strong><small>Según filtros aplicados</small></div>
        @empty
            <div class="kpi-card"><div class="kpi-card-top"><span>Registros</span><i class="fas fa-database"></i></div><strong>0</strong><small>Sin información disponible</small></div>
        @endforelse
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-5">
            <div class="panel-card chart-panel"><div class="panel-header"><div><h3>Distribución del reporte</h3><small>Vista general de los registros</small></div><i class="fas fa-chart-pie"></i></div><div class="chart-wrap"><canvas id="reportChart"></canvas>@if(!$resultado['filas']->count())<div class="empty-chart">No hay datos para graficar</div>@endif</div></div>
        </div>
        <div class="col-lg-7">
            <div class="panel-card insight-panel"><div class="panel-header"><div><h3>Resumen ejecutivo</h3><small>Indicadores de {{ strtolower($tipos[$tipo]) }}</small></div><i class="fas fa-lightbulb"></i></div><div class="insight-list"><div><span class="insight-icon green"><i class="fas fa-database"></i></span><p><strong>{{ $resultado['filas']->count() }} registros encontrados</strong><small>La consulta respeta los filtros seleccionados.</small></p></div><div><span class="insight-icon blue"><i class="fas fa-user-shield"></i></span><p><strong>Trazabilidad disponible</strong><small>El reporte identifica el responsable cuando el registro lo contiene.</small></p></div><div><span class="insight-icon orange"><i class="fas fa-file-export"></i></span><p><strong>Listo para presentar</strong><small>Usa “Exportar PDF” para generar una versión imprimible.</small></p></div></div></div>
        </div>
    </div>

    <div class="panel-card table-panel">
        <div class="panel-header"><div><h3>Detalle de registros</h3><small>{{ $resultado['filas']->count() }} resultados encontrados</small></div><span class="table-badge"><i class="fas fa-table me-1"></i> Tabla detallada</span></div>
        @if($resultado['mensaje'])<div class="empty-state"><i class="fas fa-circle-info"></i><strong>{{ $resultado['mensaje'] }}</strong></div>
        @elseif(!$resultado['filas']->count())<div class="empty-state"><i class="fas fa-folder-open"></i><strong>No se encontraron registros</strong><span>Prueba con otros filtros para consultar información.</span></div>
        @else<div class="table-responsive"><table class="table report-table"><thead><tr>@foreach($resultado['columnas'] as $columna)<th>{{ str_replace('_',' ', ucfirst($columna)) }}</th>@endforeach</tr></thead><tbody>@foreach($resultado['filas'] as $fila)<tr>@foreach($resultado['columnas'] as $columna)<td>{{ is_scalar(data_get($fila, $columna)) ? data_get($fila, $columna) : '' }}</td>@endforeach</tr>@endforeach</tbody></table></div>@endif
    </div>

    <div class="print-footer d-none d-print-block">CONTROL ECP · SENA · Reporte de {{ $tipos[$tipo] }} · {{ now()->format('d/m/Y H:i') }} · {{ Auth::user()->nickname }}</div>
</div>
@endsection

@push('styles')
<style>
    .reports-dashboard{--navy:#001A29;--green:#39A900;color:var(--navy);min-width:0;width:100%}.reports-hero{background:linear-gradient(120deg,#001A29,#00324D);border-radius:24px;padding:2rem 2.25rem;color:#fff;display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem}.reports-eyebrow,.section-kicker{font-size:.7rem;letter-spacing:.12em;text-transform:uppercase;font-weight:700;color:#62E31D}.reports-hero h1{color:#fff;font-size:2rem;margin:.35rem 0}.reports-hero p{margin:0;color:#c7d4da}.report-type-grid{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:.65rem;margin-bottom:1.25rem}.report-type{min-width:0;background:#fff;border:1px solid #e6ebef;border-radius:15px;padding:.85rem .7rem;display:flex;align-items:center;gap:.55rem;color:var(--navy);text-decoration:none;position:relative;transition:.2s}.report-type:hover,.report-type.selected{border-color:var(--report-color);box-shadow:0 6px 18px rgba(0,26,41,.1);transform:translateY(-2px)}.report-type.selected{background:#f5fbf2}.report-type-icon{width:34px;height:34px;border-radius:10px;background:color-mix(in srgb,var(--report-color) 12%,white);color:var(--report-color);display:grid;place-items:center;flex:none}.report-type strong{display:block;font-size:.78rem}.report-type small{display:block;color:#86929b;font-size:.67rem}.report-type-arrow{margin-left:auto;font-size:.65rem;color:#adb7bd}.report-toolbar,.panel-card,.kpi-card{background:#fff;border:1px solid #e6ebef;border-radius:18px;box-shadow:0 5px 20px rgba(0,26,41,.05)}.report-toolbar{padding:1rem 1.15rem;margin-bottom:1.5rem}.toolbar-title{display:flex;align-items:center;gap:.7rem;margin-bottom:1rem}.toolbar-title small,.panel-header small,.kpi-card small{display:block;color:#87939a;font-size:.72rem}.toolbar-icon{width:34px;height:34px;border-radius:10px;background:#edf8e9;color:var(--green);display:grid;place-items:center}.report-toolbar label{font-size:.7rem;font-weight:700;color:#68757c;display:block;margin:0 0 .25rem}.report-toolbar input,.report-toolbar select{border:1px solid #dfe6e9;border-radius:9px;padding:.55rem .7rem;width:100%;font-size:.82rem;min-width:0}.btn-generate{background:var(--green);color:#fff;border:0;border-radius:9px;font-weight:700}.btn-reset{border:1px solid #dfe6e9;color:#68757c;border-radius:9px;padding:.55rem .8rem}.report-heading{display:flex;justify-content:space-between;align-items:end;margin-bottom:1rem}.report-heading h2{font-size:1.5rem;margin:.25rem 0}.report-heading p{margin:0;color:#87939a;font-size:.78rem}.status-pill,.table-badge{background:#edf8e9;color:#287900;border-radius:999px;padding:.4rem .75rem;font-size:.7rem;font-weight:700}.status-pill i{font-size:.45rem;margin-right:.35rem}.kpi-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1rem;margin-bottom:1.5rem}.kpi-card{min-width:0;padding:1.1rem 1.2rem}.kpi-card-top{display:flex;justify-content:space-between;color:#718087;font-size:.75rem;font-weight:600}.kpi-card>strong{display:block;font-size:1.8rem;margin:.45rem 0 .2rem}.panel-card{min-width:0;padding:1.2rem}.panel-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem}.panel-header h3{font-size:1rem;margin:0}.panel-header>i{color:var(--green);font-size:1.1rem}.chart-wrap{height:220px;position:relative}.empty-chart{position:absolute;inset:0;display:grid;place-items:center;color:#9aa6ab;font-size:.8rem}.insight-list>div{display:flex;gap:.75rem;align-items:center;padding:.65rem 0;border-bottom:1px solid #eef1f2}.insight-list>div:last-child{border:0}.insight-list p{margin:0}.insight-list strong,.insight-list small{display:block;font-size:.78rem}.insight-list small{color:#87939a;font-size:.7rem}.insight-icon{width:34px;height:34px;border-radius:10px;display:grid;place-items:center}.green{background:#edf8e9;color:#39a900}.blue{background:#edf6fb;color:#0070b8}.orange{background:#fff3e9;color:#e65100}.table-panel{padding:0;overflow:hidden}.table-panel .panel-header{padding:1.2rem 1.2rem .2rem}.report-table{font-size:.75rem;margin:0}.report-table th{background:#f7f9fa;color:#617078;text-transform:uppercase;font-size:.65rem;letter-spacing:.04em;white-space:nowrap}.report-table td,.report-table th{padding:.75rem 1rem;border-color:#eef1f2;white-space:nowrap}.empty-state{text-align:center;padding:3rem;color:#87939a}.empty-state i{display:block;font-size:2rem;color:#b8c2c6;margin-bottom:.7rem}.empty-state strong,.empty-state span{display:block}.print-footer{border-top:1px solid #ddd;margin-top:2rem;padding-top:1rem;text-align:center;font-size:.7rem}@media(max-width:1100px){.report-type-grid{grid-template-columns:repeat(4,minmax(0,1fr))}}@media(max-width:700px){.reports-hero{display:block;padding:1.4rem}.reports-hero button{margin-top:1rem}.report-type-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.kpi-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.report-heading{align-items:start;display:block}.status-pill{display:inline-block;margin-top:.7rem}}@media print{.no-print{display:none!important}.reports-hero{background:#fff;color:#001A29;border:1px solid #ddd}.reports-hero h1{color:#001A29}.reports-dashboard{width:100%}.panel-card,.kpi-card{box-shadow:none;border:1px solid #ddd}.table-panel{overflow:visible}.report-table{font-size:8pt}}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
const reportCanvas = document.getElementById('reportChart');
if (reportCanvas) {
    new Chart(reportCanvas, {
        type: 'doughnut',
        data: {
            labels: @json($chartLabels),
            datasets: [{ data: @json($chartValues), backgroundColor: ['#39A900','#0070B8','#6A1B9A','#E65100','#00838F','#C0392B','#2C3E50'], borderWidth: 0 }]
        },
        options: { responsive: true, maintainAspectRatio: false, cutout: '68%', plugins: { legend: { position: 'right', labels: { boxWidth: 12, font: { size: 10 } } } } }
    });
}
</script>
@endpush
