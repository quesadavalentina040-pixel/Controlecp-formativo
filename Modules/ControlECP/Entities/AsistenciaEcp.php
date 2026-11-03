<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;

class AsistenciaEcp extends Model
{
    protected $table = 'controlecp_asistencias';
    protected $primaryKey = 'id_asistencia';
    protected $fillable = ['id_cronograma', 'id_instructor', 'id_ficha', 'estado', 'observacion', 'evidencia_tipo', 'evidencia_archivo', 'evidencia_enlace'];
    public function cronograma() { return $this->belongsTo(Cronograma::class, 'id_cronograma', 'id_cronograma'); }
    public function detalles() { return $this->hasMany(AsistenciaEcpDetalle::class, 'id_asistencia'); }
    public function historial() { return $this->hasMany(AsistenciaEcpHistorial::class, 'id_asistencia')->latest(); }
}
