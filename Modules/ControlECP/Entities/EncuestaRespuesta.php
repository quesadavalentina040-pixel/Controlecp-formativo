<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class EncuestaRespuesta extends Model
{
    protected $table = 'encuesta_respuestas';
    protected $primaryKey = 'id_encuesta_respuesta';
    public $timestamps = false;

    protected $fillable = [
        'id_cronograma',
        'id_usuario',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    public function detalles()
    {
        return $this->hasMany(EncuestaRespuestaDetalle::class, 'id_encuesta_respuesta', 'id_encuesta_respuesta');
    }
}
