<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    protected $table = 'controlecp_asistencias';
    protected $fillable = ['cronograma_id', 'registrado_por', 'estado', 'motivo_anulacion', 'registrada_en'];

    public function detalles()
    {
        return $this->hasMany(AsistenciaDetalle::class, 'asistencia_id');
    }

    public function registradoPor()
    {
        return $this->belongsTo(\App\Models\User::class, 'registrado_por');
    }
}
