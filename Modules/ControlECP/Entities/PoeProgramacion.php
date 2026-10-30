<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PoeProgramacion extends Model
{
    protected $table = 'poe_programaciones';
    protected $primaryKey = 'id_poe_programacion';
    public $timestamps = false;

    protected $fillable = [
        'id_poe',
        'id_usuario_lider_actual',
        'id_usuario_nuevo_lider',
        'fecha_limite',
        'fecha_original',
        'estado',
        'cancelada',
        'motivo_cambio',
        'fecha_cumplimiento',
    ];

    protected $casts = [
        'fecha_limite'       => 'date',
        'fecha_original'     => 'date',
        'fecha_cumplimiento' => 'datetime',
        'cancelada'          => 'boolean',
    ];

    /**
     * POE programado.
     */
    public function poe()
    {
        return $this->belongsTo(Poe::class, 'id_poe');
    }

    /**
     * Líder saliente / actual al momento de la programación.
     */
    public function liderActual()
    {
        return $this->belongsTo(User::class, 'id_usuario_lider_actual');
    }

    /**
     * Líder entrante asignado para subir la versión.
     */
    public function nuevoLider()
    {
        return $this->belongsTo(User::class, 'id_usuario_nuevo_lider');
    }

    /**
     * Versiones que cumplieron esta programación.
     */
    public function versiones()
    {
        return $this->hasMany(PoeVersion::class, 'id_poe_programacion');
    }

    /**
     * Notificaciones asociadas a esta programación.
     */
    public function notificaciones()
    {
        return $this->hasMany(Notification::class, 'id_poe_programacion');
    }
}
