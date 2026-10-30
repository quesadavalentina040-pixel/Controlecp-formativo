<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Poe extends Model
{
    protected $table = 'poes';
    protected $primaryKey = 'id_poe';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'codigo',
        'nombre',
    ];

    /**
     * Líder actual del POE.
     */
    public function liderActual()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    /**
     * Todas las versiones del POE.
     */
    public function versiones()
    {
        return $this->hasMany(PoeVersion::class, 'id_poe')->orderBy('id_poe_version', 'desc');
    }

    /**
     * Versión actual vigente del POE.
     */
    public function versionActual()
    {
        return $this->hasOne(PoeVersion::class, 'id_poe')->where('es_actual', true);
    }

    /**
     * Programaciones de entrega del POE.
     */
    public function programaciones()
    {
        return $this->hasMany(PoeProgramacion::class, 'id_poe')->orderBy('id_poe_programacion', 'desc');
    }

    /**
     * Programación vigente (Pendiente o Atrasada, no cancelada).
     */
    public function programacionVigente()
    {
        return $this->hasOne(PoeProgramacion::class, 'id_poe')
            ->where('cancelada', false)
            ->whereIn('estado', ['Pendiente', 'Atrasada']);
    }

    /**
     * Historial cronológico de líderes del POE.
     */
    public function lideresHistorial()
    {
        return $this->hasMany(PoeLiderHistorial::class, 'id_poe')->orderBy('fecha_inicio', 'desc');
    }
}
