<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\ControlECP\Entities\Encuesta;

class Momento extends Model
{
    use SoftDeletes;

    protected $table = 'controlecp_momentos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'icono',
        'color',
        'orden',
        'estado',
        'encuesta_id',
    ];

    protected $casts = [
        'orden' => 'integer',
    ];

    // ── Relaciones ────────────────────────────────────────────
    /**
     * Actividades pedagógicas de este momento.
     */
    public function actividades()
    {
        return $this->hasMany(ActividadEcp::class, 'momento_id')
                    ->orderBy('numero');
    }

    /**
     * Solo las actividades activas.
     */
    public function actividadesActivas()
    {
        return $this->hasMany(ActividadEcp::class, 'momento_id')
                    ->where('estado', 'Activo')
                    ->orderBy('numero');
    }

    public function encuesta()
    {
        return $this->belongsTo(Encuesta::class, 'encuesta_id', 'id_encuesta');
    }

    // ── Scopes ────────────────────────────────────────────────
    public function scopeActivos($query)
    {
        return $query->where('estado', 'Activo');
    }
}
