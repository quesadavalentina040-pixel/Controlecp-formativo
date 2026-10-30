<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EncuestaEcp extends Model
{
    use SoftDeletes;

    protected $table = 'controlecp_encuestas';

    protected $fillable = [
        'nombre',
        'titulo',
        'descripcion',
        'estado',
    ];

    public function actividades()
    {
        return $this->hasMany(ActividadEcp::class, 'encuesta_id');
    }

    public function scopeActivas($query)
    {
        return $query->whereIn('estado', ['Activo', 'Activa']);
    }
}
