<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;

class Pregunta extends Model
{
    protected $table = 'preguntas';
    protected $primaryKey = 'id_pregunta';
    public $timestamps = false;

    protected $fillable = [
        'id_encuesta',
        'texto',
        'tipo',
        'obligatoria',
        'orden',
    ];

    protected $casts = [
        'obligatoria' => 'boolean',
        'orden'       => 'integer',
    ];

    public function getIdAttribute()
    {
        return $this->attributes['id_pregunta'] ?? null;
    }

    public function encuesta()
    {
        return $this->belongsTo(Encuesta::class, 'id_encuesta', 'id_encuesta');
    }

    public function opciones()
    {
        return $this->hasMany(OpcionPregunta::class, 'id_pregunta', 'id_pregunta')
            ->orderBy('orden', 'asc')
            ->orderBy('id_opcion_pregunta', 'asc');
    }

    public function detalles()
    {
        return $this->hasMany(EncuestaRespuestaDetalle::class, 'id_pregunta', 'id_pregunta');
    }

    public function isOpcionMultiple(): bool
    {
        return $this->tipo === 'Opcion_multiple';
    }

    public function isEscala(): bool
    {
        return $this->tipo === 'Escala';
    }

    public function isTexto(): bool
    {
        return $this->tipo === 'Texto';
    }
}
