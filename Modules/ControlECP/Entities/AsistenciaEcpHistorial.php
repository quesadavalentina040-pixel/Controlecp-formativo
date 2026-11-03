<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;

class AsistenciaEcpHistorial extends Model
{
    protected $table = 'controlecp_asistencia_historial';
    protected $fillable = ['id_asistencia', 'id_usuario', 'accion', 'motivo', 'datos_anteriores'];
    protected $casts = ['datos_anteriores' => 'array'];
}
