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
