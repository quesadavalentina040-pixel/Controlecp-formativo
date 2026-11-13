<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de {{ $nombreReporte }}</title>
    @php
        $coloresGrafica = ['#39A900', '#0284C7', '#F59E0B', '#DC2626', '#7C3AED', '#0891B2', '#EA580C', '#475569'];
        $coloresKpi = [
            ['color' => '#0284C7', 'fondo' => '#F0F9FF', 'borde' => '#BAE6FD'],
            ['color' => '#16A34A', 'fondo' => '#F0FDF4', 'borde' => '#BBF7D0'],
            ['color' => '#DC2626', 'fondo' => '#FEF2F2', 'borde' => '#FECACA'],
            ['color' => '#7C3AED', 'fondo' => '#F5F3FF', 'borde' => '#DDD6FE'],
        ];
        $tieneEstado = in_array('estado', $resultado['columnas'], true);
        $distribucion = $tieneEstado
            ? $resultado['filas']->groupBy(fn ($fila) => data_get($fila, 'estado') ?: 'Sin estado')->map(fn ($filas) => $filas->count())->take(8)
            : collect($resultado['filas']->isNotEmpty() ? ['Registros totales' => $resultado['filas']->count()] : []);
        $maximoGrafica = max(1, (int) ($distribucion->max() ?? 0));
        $tituloGrafica = $tieneEstado ? 'Registros por estado' : 'Total de registros';
    @endphp
    <style>
        @page { margin: 28px 32px 38px; }
        body { font-family: DejaVu Sans, sans-serif; color: #001A29; font-size: 9px; }
        .header { text-align: center; padding: 4px 0 14px; margin-bottom: 12px; border-bottom: 2px solid #e6ecef; }
        .logo { display: block; width: 110px; height: 110px; margin: 0 auto 8px; }
        .brand { color: {{ $color }}; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 1.2px; }
        h1 { margin: 5px 0; font-size: 21px; color: #001A29; }
        .muted { color: #647782; font-size: 8px; }
        .metrics { width: 100%; border-collapse: separate; border-spacing: 6px; margin: 0 -6px 12px; }
        .metric { width: 25%; border: 1px solid #dfe7ea; border-radius: 9px; padding: 10px; background: #fff; }
        .metric-label { color: #647782; font-size: 8px; font-weight: bold; }
        .metric-value { font-size: 19px; font-weight: bold; margin: 6px 0 3px; }
        .metric-note { color: #82919a; font-size: 7px; }
        .section-title { margin: 14px 0 7px; font-size: 12px; color: #001A29; }
        .section-subtitle { margin: 0 0 8px; color: #718087; font-size: 8px; }
        table.data { width: 100%; border-collapse: collapse; }
        .data th { background: #001A29; color: #fff; text-align: left; font-size: 7px; text-transform: uppercase; }
        .data td,.data th { padding: 6px 5px; border: 1px solid #dfe7ea; vertical-align: top; }
        .data tr:nth-child(even) td { background: #f7fafb; }
        .chart-card { border: 1px solid #dfe7ea; border-radius: 12px; padding: 16px 18px; background: #fbfdfd; page-break-inside: avoid; }
        .chart-summary { margin-bottom: 12px; padding: 8px 11px; background: #f1f6f7; border-radius: 7px; color: #52616a; font-size: 8px; }
        .chart-summary strong { color: #001A29; }
        .chart-row { width: 100%; border-collapse: collapse; page-break-inside: avoid; }
        .chart-row tr { page-break-inside: avoid; }
        .chart-row td { padding: 8px 0; vertical-align: middle; border-bottom: 1px solid #e8eef0; }
        .chart-row tr:last-child td { border-bottom: 0; }
        .chart-label { width: 24%; padding-right: 16px !important; color: #263746; font-size: 10px; font-weight: bold; }
        .chart-track-cell { width: 64%; }
        .chart-value { width: 12%; padding-left: 12px !important; color: #001A29; font-weight: bold; font-size: 9px; text-align: right; white-space: nowrap; }
        .chart-track { width: 100%; height: 20px; background: #e8eef0; border-radius: 10px; }
        .chart-bar { height: 20px; border-radius: 10px; }
        .empty { color: #647782; text-align: center; padding: 20px; }
        .footer { position: fixed; bottom: -18px; left: 0; right: 0; padding-top: 5px; border-top: 1px solid #dfe7ea; color: #647782; text-align: center; font-size: 8px; }
    </style>
</head>
<body>
    <header class="header">
        <img class="logo" src="{{ $logo }}" alt="Logo Escuela Cultura de Paz">
        <div class="brand">Control ECP · SENA · Reporte administrativo</div>
        <h1>{{ $nombreReporte }}</h1>
        <div class="muted">Generado el {{ $generadoEn }} por {{ $generadoPor }}</div>
    </header>

    @if(count($resultado['indicadores']))
        <table class="metrics">
            @foreach(array_chunk($resultado['indicadores'], 4, true) as $fila)
                <tr>
                    @foreach($fila as $label => $valor)
                        @php
                            $valorKpi = is_numeric($valor)
                                ? number_format((float) $valor, floor((float) $valor) === (float) $valor ? 0 : 1)
                                : $valor;
                            $colorKpi = $coloresKpi[$loop->index % count($coloresKpi)];
                        @endphp
                        <td class="metric" style="border-color: {{ $colorKpi['borde'] }}; background-color: {{ $colorKpi['fondo'] }};">
                            <div class="metric-label">{{ $label }}</div>
                            <div class="metric-value" style="color: {{ $colorKpi['color'] }};">{{ $valorKpi }}</div>
                            <div class="metric-note">Según filtros aplicados</div>
                        </td>
                    @endforeach
                    @for($espacio = count($fila); $espacio < 4; $espacio++)
                        <td class="metric" style="border-color: transparent;"></td>
                    @endfor
                </tr>
            @endforeach
        </table>
    @endif

    <h2 class="section-title">Detalle del reporte</h2>
    <p class="section-subtitle">{{ $resultado['filas']->count() }} registros encontrados con los filtros seleccionados.</p>
    @if($resultado['filas']->isEmpty())
        <div class="empty">{{ $resultado['mensaje'] ?: 'No se encontraron registros para los filtros seleccionados.' }}</div>
    @else
        <table class="data">
            <thead><tr>@foreach($resultado['columnas'] as $columna)<th>{{ str_replace('_', ' ', $columna) }}</th>@endforeach</tr></thead>
            <tbody>
                @foreach($resultado['filas'] as $fila)
                    <tr>@foreach($resultado['columnas'] as $columna)<td>{{ data_get($fila, $columna) ?? '—' }}</td>@endforeach</tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2 class="section-title">{{ $tituloGrafica }}</h2>
    <p class="section-subtitle">Cada color identifica una categoría; la longitud y el valor de la barra indican la cantidad de registros.</p>
    @if($distribucion->isNotEmpty())
        <div class="chart-card">
            <div class="chart-summary"><strong>Cómo leer esta gráfica:</strong> compara los estados por longitud de barra. Escala: 0 a {{ number_format($maximoGrafica) }} registros.</div>
            <table class="chart-row">
                @foreach($distribucion as $estado => $cantidad)
                    @php
                        $porcentaje = max(2, (int) round($cantidad * 100 / $maximoGrafica));
                        $colorBarra = $coloresGrafica[$loop->index % count($coloresGrafica)];
                    @endphp
                    <tr>
                        <td class="chart-label"><span style="color: {{ $colorBarra }};">&#9632;</span> &nbsp;{{ $estado }}</td>
                        <td class="chart-track-cell"><div class="chart-track"><div class="chart-bar" style="width: {{ $porcentaje }}%; background-color: {{ $colorBarra }};"></div></div></td>
                        <td class="chart-value">{{ number_format($cantidad) }} registros</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @else
        <div class="empty">No hay datos suficientes para construir la gráfica.</div>
    @endif

    <footer class="footer">CONTROL ECP · SENA · {{ $nombreReporte }} · {{ $generadoEn }}</footer>
</body>
</html>
