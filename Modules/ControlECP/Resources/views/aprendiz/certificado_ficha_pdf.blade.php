<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Constancia de ficha {{ $curso->code }}</title>
    <style>
        @page { margin: 34px 42px; }
        body { font-family: DejaVu Sans, sans-serif; color: #173042; font-size: 12px; }
        .topline { height: 8px; background: #39a900; margin-bottom: 28px; }
        .brand { color: #527080; font-size: 10px; font-weight: bold; letter-spacing: 2px; }
        h1 { color: #001a29; font-size: 26px; margin: 9px 0 5px; }
        .subtitle { color: #627887; font-size: 13px; margin: 0 0 24px; }
        .summary { background: #f0f7eb; border-left: 5px solid #39a900; padding: 14px 17px; margin-bottom: 22px; }
        .summary table { width: 100%; }
        .summary td { padding: 4px 8px 4px 0; vertical-align: top; }
        .label { color: #627887; font-size: 9px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; }
        .value { color: #001a29; font-size: 14px; font-weight: bold; padding-top: 3px; }
        .intro { font-size: 13px; line-height: 1.7; margin: 0 0 18px; }
        .moment { border: 1px solid #dce5e9; margin-bottom: 11px; }
        .moment-heading { background: #001a29; color: #fff; padding: 11px 14px; font-size: 13px; font-weight: bold; }
        .moment-body { padding: 11px 14px; }
        .record { width: 100%; border-collapse: collapse; }
        .record th { color: #627887; font-size: 9px; letter-spacing: .7px; text-align: left; text-transform: uppercase; padding: 5px 7px; border-bottom: 1px solid #dce5e9; }
        .record td { padding: 7px; border-bottom: 1px solid #edf1f3; }
        .record tr:last-child td { border-bottom: 0; }
        .seal { margin: 22px auto 0; border: 2px solid #39a900; color: #247500; width: 128px; padding: 9px; text-align: center; font-size: 10px; font-weight: bold; letter-spacing: 1px; }
        .footer { margin-top: 23px; padding-top: 11px; border-top: 1px solid #dce5e9; color: #627887; font-size: 9px; line-height: 1.6; text-align: center; }
    </style>
</head>
<body>
    <div class="topline"></div>
    <div class="brand">CONTROL ECP · CULTURA DE PAZ</div>
    <h1>Constancia de certificación</h1>
    <p class="subtitle">Proceso formativo por momentos</p>

    <div class="summary">
        <table>
            <tr>
                <td width="35%">
                    <div class="label">Ficha</div>
                    <div class="value">{{ $curso->code }}</div>
                </td>
                <td>
                    <div class="label">Programa de formación</div>
                    <div class="value">{{ $curso->program?->name ?: 'No especificado' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <p class="intro">
        Se deja constancia de que la ficha <strong>{{ $curso->code }}</strong> completó la certificación de
        todos los momentos del proceso formativo. A continuación se relacionan las fechas y las personas
        responsables registradas para cada momento.
    </p>

    @foreach($momentos as $momento)
        <div class="moment">
            <div class="moment-heading">Momento {{ $momento->orden }} · {{ $momento->nombre }}</div>
            <div class="moment-body">
                <table class="record">
                    <thead>
                        <tr><th>Fecha de certificación</th><th>Responsable</th><th>Rol</th></tr>
                    </thead>
                    <tbody>
                        @foreach($momento->certificaciones as $certificacion)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($certificacion->fecha_certificacion, 'UTC')->timezone('America/Bogota')->format('d/m/Y g:i A') }}</td>
                                <td>{{ $certificacion->nickname }}</td>
                                <td>{{ $certificacion->responsable_tipo === 'instructor' ? 'Instructor' : 'Administrador' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

    <div class="seal">FICHA<br>COMPLETADA</div>
    <div class="footer">
        Constancia generada el {{ now()->timezone('America/Bogota')->format('d/m/Y g:i A') }} por Control ECP.<br>
        Este documento acredita el seguimiento interno de los momentos; no reemplaza la certificación oficial expedida en SOFIA Plus.
    </div>
</body>
</html>
