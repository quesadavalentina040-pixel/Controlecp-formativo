
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tabulación - {{ $encuesta->nombre }}</title>

    <style>
        @page {
            margin: 1.2cm 1.2cm 1.5cm 1.2cm;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        /* Colores SENA / Control ECP */
        .color-petroleo { color: #001A29; }
        .color-verde { color: #39A900; }
        .bg-petroleo { background-color: #001A29; color: #ffffff; }
        .bg-verde { background-color: #39A900; color: #ffffff; }

        /* Encabezado */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #39A900;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .header-title {
            font-size: 16px;
            font-weight: bold;
            color: #001A29;
            margin: 0 0 4px 0;
            text-transform: uppercase;
        }
        .header-sub {
            font-size: 11px;
            color: #64748b;
            margin: 0;
        }

        /* Caja de Filtros Aplicados */
        .box-filtros {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 16px;
            font-size: 10px;
        }
        .badge-filtro {
            display: inline-block;
            background-color: #e2e8f0;
            color: #334155;
            padding: 2px 6px;
            border-radius: 4px;
            margin-right: 6px;
            font-weight: 600;
        }

        /* 4 Indicadores Principales */
        .tabla-indicadores {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: separate;
            border-spacing: 8px 0;
        }
        .card-indicador {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
        }
        .ind-valor {
            font-size: 16px;
            font-weight: bold;
            color: #001A29;
            margin-bottom: 2px;
        }
        .ind-etiqueta {
            font-size: 9px;
            text-transform: uppercase;
            font-weight: bold;
            color: #64748b;
            letter-spacing: 0.5px;
        }

        /* Preguntas */
        .card-pregunta {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            margin-bottom: 14px;
            padding: 12px 14px;
            page-break-inside: avoid;
        }
        .pregunta-header {
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        .pregunta-numero {
            display: inline-block;
            background-color: #001A29;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 4px;
            margin-right: 6px;
        }
        .pregunta-titulo {
            font-size: 12px;
            font-weight: bold;
            color: #001A29;
        }
        .pregunta-meta {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }

        /* Barras de distribución con HTML/CSS puro para Dompdf */
        .barra-fila {
            margin-bottom: 6px;
        }
        .barra-texto {
            font-size: 10px;
            margin-bottom: 2px;
        }
        .barra-contenedor {
            width: 100%;
            height: 14px;
            background-color: #f1f5f9;
            border-radius: 4px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .barra-progreso-verde {
            height: 14px;
            background-color: #39A900;
        }
        .barra-progreso-azul {
            height: 14px;
            background-color: #0ea5e9;
        }

        /* Respuestas de Texto */
        .item-texto {
            background-color: #f8fafc;
            border-left: 3px solid #39A900;
            padding: 6px 10px;
            margin-bottom: 6px;
            font-size: 10px;
        }

        /* Pie de página */
        .footer-pdf {
            position: fixed;
            bottom: -20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td style="vertical-align: middle;">
                <div class="header-title">INFORME DE TABULACIÓN DE ENCUESTA</div>
                <div class="header-sub">
                    <strong>Encuesta:</strong> {{ $encuesta->nombre }} &bull; Escuela Cultura de Paz (Control ECP)
                </div>
            </td>
            <td style="text-align: right; vertical-align: middle; width: 160px;">
                <div style="font-size: 9px; color: #64748b;">
                    <strong>Fecha Generación:</strong><br>{{ $fechaGeneracion }}
                </div>
            </td>
        </tr>
    </table>

    <div class="box-filtros">
        <strong>Filtros aplicados al informe:</strong>
        @if($nombreActividad)
            <span class="badge-filtro">{{ $nombreActividad }}</span>
        @else
            <span class="badge-filtro">Actividades: Todas (Consolidado)</span>
        @endif

        @if($codigoFicha)
            <span class="badge-filtro">{{ $codigoFicha }}</span>
        @endif

        @if(!empty($filtros['fecha_desde']) || !empty($filtros['fecha_hasta']))
            <span class="badge-filtro">
                Rango: {{ $filtros['fecha_desde'] ?? 'Inicio' }} al {{ $filtros['fecha_hasta'] ?? 'Hoy' }}
            </span>
        @else
            <span class="badge-filtro">Rango: Histórico Completo</span>
        @endif
    </div>

    @php // Inicia un bloque PHP para preparar datos que usará la vista.
        $consolidado = $resultados['consolidado'];
    @endphp
    <table class="tabla-indicadores">
        <tr>
            <td style="width: 25%;">
                <div class="card-indicador">
                    <div class="ind-valor color-petroleo">{{ $consolidado['total_respuestas'] }}</div>
                    <div class="ind-etiqueta">Respuestas Recibidas</div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="card-indicador">
                    <div class="ind-valor color-petroleo">{{ $consolidado['preguntas_evaluadas'] }}</div>
                    <div class="ind-etiqueta">Preguntas Evaluadas</div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="card-indicador">
                    <div class="ind-valor color-verde">{{ $consolidado['tasa_participacion'] }}</div>
                    <div class="ind-etiqueta">Tasa de Participación</div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="card-indicador">
                    <div class="ind-valor" style="color: #d97706;">{{ $consolidado['satisfaccion_promedio'] }}</div>
                    <div class="ind-etiqueta">Satisfacción Promedio</div>
                    @if($consolidado['satisfaccion_porcentaje'] !== null)
                        <div style="font-size: 9px; color: #64748b;">{{ $consolidado['satisfaccion_porcentaje'] }}%</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <div style="font-size: 13px; font-weight: bold; color: #001A29; margin-bottom: 12px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px;">
        RESULTADOS DETALLADOS POR PREGUNTA ({{ count($consolidado['preguntas']) }})
    </div>

    @forelse($consolidado['preguntas'] as $index => $preg)
        <div class="card-pregunta">
            <div class="pregunta-header">
                <table style="width: 100%;">
                    <tr>
                        <td>
                            <span class="pregunta-numero">P{{ $preg['orden'] ?? ($index + 1) }}</span>
                            <span class="pregunta-titulo">{{ $preg['texto'] }}</span>
                        </td>
                        <td style="text-align: right; width: 140px;">
                            <span style="font-size: 9px; background-color: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-weight: 600;">
                                {{ str_replace('_', ' ', $preg['tipo']) }}
                            </span>
                        </td>
                    </tr>
                </table>
                <div class="pregunta-meta">
                    Total respuestas registradas: <strong>{{ $preg['cantidad_respuestas'] }}</strong>
                    @if($preg['tipo'] === 'Escala' && $preg['promedio_escala'] !== null)
                        &bull; Promedio de la pregunta: <strong style="color: #39A900;">{{ number_format($preg['promedio_escala'], 1) }} / 5</strong>
                    @endif
                </div>
            </div>

            @if($preg['tipo'] === 'Opcion_multiple')
                @foreach($preg['opciones'] as $opc)
                    <div class="barra-fila">
                        <table style="width: 100%;">
                            <tr>
                                <td class="barra-texto" style="font-weight: 600;">{{ $opc['texto'] }}</td>
                                <td style="text-align: right; font-size: 9px; color: #64748b;">
                                    <strong>{{ $opc['total'] }}</strong> ({{ $opc['porcentaje'] }}%)
                                </td>
                            </tr>
                        </table>
                        <div class="barra-contenedor">
                            <div class="barra-progreso-verde" style="width: {{ $opc['porcentaje'] }}%;"></div>
                        </div>
                    </div>
                @endforeach

            @elseif($preg['tipo'] === 'Escala')
                @for($i = 1; $i <= 5; $i++)
                    @php $itemEscala = $preg['escala'][$i] ?? ['total' => 0, 'porcentaje' => 0]; @endphp

                    <div class="barra-fila">
                        <table style="width: 100%;">
                            <tr>
                                <td class="barra-texto">Nivel {{ $i }} ★</td>
                                <td style="text-align: right; font-size: 9px; color: #64748b;">
                                    <strong>{{ $itemEscala['total'] }}</strong> ({{ $itemEscala['porcentaje'] }}%)
                                </td>
                            </tr>
                        </table>
                        <div class="barra-contenedor">
                            <div class="barra-progreso-azul" style="width: {{ $itemEscala['porcentaje'] }}%;"></div>
                        </div>
                    </div>
                @endfor

            @elseif($preg['tipo'] === 'Texto')
                @if(count($preg['respuestas_texto']) > 0)
                    @foreach(array_slice($preg['respuestas_texto'], 0, 15) as $txt)
                        <div class="item-texto">
                            <em>"{{ $txt['texto'] }}"</em>
                            <div style="font-size: 8px; color: #64748b; margin-top: 2px;">
                                Por: {{ $txt['autor'] }} &bull; {{ $txt['fecha'] }}
                            </div>
                        </div>
                    @endforeach
                    @if(count($preg['respuestas_texto']) > 15)
                        <div style="font-size: 9px; color: #64748b; text-align: center; margin-top: 4px;">
                            (Mostrando 15 de {{ count($preg['respuestas_texto']) }} respuestas abiertas)
                        </div>
                    @endif
                @else
                    <div style="color: #94a3b8; font-style: italic; font-size: 9px;">Sin respuestas de texto registradas.</div>
                @endif
            @endif
        </div>
    @empty
        <div style="text-align: center; color: #94a3b8; padding: 20px;">
            No se encontraron preguntas en esta encuesta.
        </div>
    @endforelse

    @if(!empty($resultados['por_actividad']))
        @foreach($resultados['por_actividad'] as $item)
            @php // Inicia un bloque PHP para preparar datos que usará la vista.
                $actividad = $item['actividad'];
                $datosActividad = $item['datos'];
            @endphp
            <div style="page-break-before: always;">
                <div style="font-size: 15px; font-weight: bold; color: #001A29; margin-bottom: 12px; border-bottom: 2px solid #39A900; padding-bottom: 6px;">
                    RESULTADOS POR ACTIVIDAD: {{ $actividad->nombre }}
                </div>

                <table class="tabla-indicadores">
                    <tr>
                        <td style="width: 25%;">
                            <div class="card-indicador">
                                <div class="ind-valor color-petroleo">{{ $datosActividad['total_respuestas'] }}</div>
                                <div class="ind-etiqueta">Respuestas recibidas</div>
                            </div>
                        </td>
                        <td style="width: 25%;">
                            <div class="card-indicador">
                                <div class="ind-valor color-petroleo">{{ $datosActividad['preguntas_evaluadas'] }}</div>
                                <div class="ind-etiqueta">Preguntas evaluadas</div>
                            </div>
                        </td>
                        <td style="width: 25%;">
                            <div class="card-indicador">
                                <div class="ind-valor color-verde">{{ $datosActividad['tasa_participacion'] }}</div>
                                <div class="ind-etiqueta">Tasa de participación</div>
                            </div>
                        </td>
                        <td style="width: 25%;">
                            <div class="card-indicador">
                                <div class="ind-valor" style="color: #d97706;">{{ $datosActividad['satisfaccion_promedio'] }}</div>
                                <div class="ind-etiqueta">Satisfacción promedio</div>
                                @if($datosActividad['satisfaccion_porcentaje'] !== null)
                                    <div style="font-size: 9px; color: #64748b;">{{ $datosActividad['satisfaccion_porcentaje'] }}%</div>
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>

                @if($datosActividad['total_respuestas'] === 0)
                    <div style="text-align: center; color: #64748b; padding: 20px;">
                        Sin respuestas registradas para esta actividad con los filtros seleccionados.
                    </div>
                @else
                    @foreach($datosActividad['preguntas'] as $index => $preg)
                        <div class="card-pregunta">
                            <div class="pregunta-header">
                                <div>
                                    <span class="pregunta-numero">P{{ $preg['orden'] ?? ($index + 1) }}</span>
                                    <span class="pregunta-titulo">{{ $preg['texto'] }}</span>
                                </div>
                                <div class="pregunta-meta">
                                    {{ str_replace('_', ' ', $preg['tipo']) }} &bull;
                                    Total respuestas: <strong>{{ $preg['cantidad_respuestas'] }}</strong>
                                    @if($preg['tipo'] === 'Escala' && $preg['promedio_escala'] !== null)
                                        &bull; Promedio: <strong>{{ number_format($preg['promedio_escala'], 1) }} / 5</strong>
                                    @endif
                                </div>
                            </div>

                            @if($preg['tipo'] === 'Opcion_multiple')
                                @foreach($preg['opciones'] as $opc)
                                    <div class="barra-fila">
                                        <table style="width: 100%;">
                                            <tr>
                                                <td class="barra-texto" style="font-weight: 600;">{{ $opc['texto'] }}</td>
                                                <td style="text-align: right; font-size: 9px; color: #64748b;">
                                                    <strong>{{ $opc['total'] }}</strong> ({{ $opc['porcentaje'] }}%)
                                                </td>
                                            </tr>
                                        </table>
                                        <div class="barra-contenedor">
                                            <div class="barra-progreso-verde" style="width: {{ $opc['porcentaje'] }}%;"></div>
                                        </div>
                                    </div>
                                @endforeach
                            @elseif($preg['tipo'] === 'Escala')
                                @for($i = 1; $i <= 5; $i++)
                                    @php $itemEscala = $preg['escala'][$i] ?? ['total' => 0, 'porcentaje' => 0]; @endphp

                                    <div class="barra-fila">
                                        <table style="width: 100%;">
                                            <tr>
                                                <td class="barra-texto">Nivel {{ $i }}</td>
                                                <td style="text-align: right; font-size: 9px; color: #64748b;">
                                                    <strong>{{ $itemEscala['total'] }}</strong> ({{ $itemEscala['porcentaje'] }}%)
                                                </td>
                                            </tr>
                                        </table>
                                        <div class="barra-contenedor">
                                            <div class="barra-progreso-azul" style="width: {{ $itemEscala['porcentaje'] }}%;"></div>
                                        </div>
                                    </div>
                                @endfor
                            @elseif($preg['tipo'] === 'Texto')
                                @forelse(array_slice($preg['respuestas_texto'], 0, 15) as $txt)
                                    <div class="item-texto">{{ $txt['texto'] }}</div>
                                @empty
                                    <div style="color: #94a3b8; font-style: italic; font-size: 9px;">Sin respuestas de texto registradas.</div>
                                @endforelse
                                @if(count($preg['respuestas_texto']) > 15)
                                    <div style="font-size: 9px; color: #64748b; text-align: center;">
                                        (Mostrando 15 de {{ count($preg['respuestas_texto']) }} respuestas abiertas)
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        @endforeach
    @endif

    <div class="footer-pdf">
        Sistema de Gestión y Tabulación &bull; Control ECP (Escuela Cultura de Paz) &bull; Documento Generado Automáticamente
    </div>

</body>
</html>
