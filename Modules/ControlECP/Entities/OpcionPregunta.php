<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;

class OpcionPregunta extends Model
{
    protected $table = 'opciones_pregunta';
    protected $primaryKey = 'id_opcion_pregunta';
    public $timestamps = false;

    protected $fillable = [
        'id_pregunta',
        'texto',
        'orden',
    ];

    protected $casts = [
        'orden' => 'integer',
    ];

    public function getIdAttribute()
    {
        return $this->attributes['id_opcion_pregunta'] ?? null;
    }

    public function pregunta()
    {
        return $this->belongsTo(Pregunta::class, 'id_pregunta', 'id_pregunta');
    }
}
