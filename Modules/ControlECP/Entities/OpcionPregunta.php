<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;

class OpcionPregunta extends Model
{
    protected $table = 'opciones_pregunta';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $appends = ['id_opcion_pregunta'];

    protected $fillable = [
        'id_pregunta',
        'texto',
        'orden',
    ];

    protected $casts = [
        'orden' => 'integer',
    ];

    public function getIdOpcionPreguntaAttribute(): ?int
    {
        return isset($this->attributes['id']) ? (int) $this->attributes['id'] : null;
    }

    public function pregunta()
    {
        return $this->belongsTo(Pregunta::class, 'id_pregunta', 'id_pregunta');
    }
}
