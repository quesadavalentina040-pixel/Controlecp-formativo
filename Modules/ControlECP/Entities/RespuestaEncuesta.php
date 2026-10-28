<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class RespuestaEncuesta extends Model
{
    protected $table = 'respuestas_encuesta';

    protected $fillable = [
        'encuesta_id',
        'pregunta_id',
        'actividad_id',
        'programacion_id',
        'usuario_id',
        'respuesta',
    ];

    public function encuesta()
    {
        return $this->belongsTo(Encuesta::class, 'encuesta_id');
    }

    public function pregunta()
    {
        return $this->belongsTo(PreguntaEncuesta::class, 'pregunta_id');
    }

    public function actividad()
    {
        return $this->belongsTo(Actividad::class, 'actividad_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
