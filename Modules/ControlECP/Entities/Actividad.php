<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Actividad extends Model
{
    use SoftDeletes;

    protected $table = 'controlecp_actividades';

    protected $fillable = [
        'momento_id',
        'numero',
        'nombre',
        'descripcion',
        'objetivo',
        'duracion',
        'duracion_min',
        'metodologia',
        'estado',
        'tiene_asistencia',
        'encuesta_id',
        'id_encuesta',
        'tiene_evidencia',
    ];

    /**
     * Relación: Una actividad puede tener asignada una encuesta institucional.
     */
    public function encuesta()
    {
        return $this->belongsTo(Encuesta::class, 'id_encuesta', 'id_encuesta');
    }
}
