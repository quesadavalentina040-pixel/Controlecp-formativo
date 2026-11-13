<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacion extends Model
{
    protected $table = 'notificaciones';
    protected $primaryKey = 'id_notificacion';

    protected $fillable = [
        'id_usuario_receptor',
        'id_usuario_causante',
        'modulo',
        'titulo',
        'mensaje',
        'referencia_tipo',
        'referencia_id',
        'clave_evento',
        'clave_agrupacion',
        'cantidad',
        'leida',
        'fecha_generacion',
        'fecha_lectura',
    ];

    protected $casts = [
        'leida'            => 'boolean',
        'cantidad'         => 'integer',
        'fecha_generacion' => 'datetime',
        'fecha_lectura'    => 'datetime',
    ];

    public function receptor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_receptor', 'id');
    }

    public function causante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_causante', 'id');
    }

    /**
     * Helper estático para registrar una notificación rápidamente.
     */
    public static function crear(
        int $idReceptor,
        ?int $idCausante,
        string $titulo,
        string $mensaje,
        ?string $referenciaTipo = null,
        ?int $referenciaId = null,
        ?string $claveEvento = null
    ): self {
        $tituloNormalizado = mb_strtolower($titulo);
        $esResumenSesiones = str_contains($tituloNormalizado, 'sesiones de acompañamiento')
            || str_contains($tituloNormalizado, 'próxima sesión de acompañamiento');

        if (strtolower((string) $referenciaTipo) === 'acompanamiento' && !$esResumenSesiones) {
            $cancelada = str_contains(mb_strtolower($titulo), 'cancel');
            $titulo = $cancelada
                ? 'Sesión de acompañamiento cancelada'
                : 'Aviso de sesión de acompañamiento';

            preg_match('/\b(\d{2}\/\d{2}\/\d{4})\b/u', $mensaje, $fecha);
            preg_match_all('/\b([01]\d|2[0-3]):[0-5]\d\b/u', $mensaje, $horas);

            if (empty($fecha[1])) {
                $mensaje = 'Hay una novedad sobre tu sesión de acompañamiento.';
            } else {
                $mensaje = 'Fecha: ' . $fecha[1];
                if (count($horas[0]) >= 2) {
                    $mensaje .= ' · Hora: ' . $horas[0][0] . ' a ' . $horas[0][1];
                }
                $mensaje .= '.';
            }
        }

        return self::create([
            'id_usuario_receptor' => $idReceptor,
            'id_usuario_causante' => $idCausante,
            'modulo'              => 'ControlECP',
            'titulo'              => $titulo,
            'mensaje'             => $mensaje,
            'referencia_tipo'     => $referenciaTipo,
            'referencia_id'       => $referenciaId,
            'clave_evento'        => $claveEvento,
            'leida'               => false,
            'fecha_generacion'    => now(),
        ]);
    }
}
