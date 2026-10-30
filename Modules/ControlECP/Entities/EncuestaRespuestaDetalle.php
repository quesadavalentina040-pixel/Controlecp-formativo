<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;

class EncuestaRespuestaDetalle extends Model
{
    protected $table = 'encuesta_respuesta_detalle';
    protected $primaryKey = 'id_encuesta_respuesta_detalle';
    public $timestamps = false;

    protected $fillable = [
        'id_encuesta_respuesta',
        'id_pregunta',
        'id_opcion_pregunta',
        'valor',
    ];

    public function respuesta()
    {
        return $this->belongsTo(EncuestaRespuesta::class, 'id_encuesta_respuesta', 'id_encuesta_respuesta');
    }

    public function pregunta()
    {
        return $this->belongsTo(Pregunta::class, 'id_pregunta', 'id_pregunta');
    }

    public function opcion()
    {
        return $this->belongsTo(OpcionPregunta::class, 'id_opcion_pregunta', 'id_opcion_pregunta');
    }
}
