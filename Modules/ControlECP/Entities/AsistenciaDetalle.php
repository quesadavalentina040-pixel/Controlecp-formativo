<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;

class AsistenciaDetalle extends Model
{
    protected $table = 'controlecp_asistencia_detalles';
    protected $fillable = ['asistencia_id', 'aprendiz_id', 'presente', 'observacion'];
    protected $casts = ['presente' => 'boolean'];
}
