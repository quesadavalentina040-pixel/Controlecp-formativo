<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;

class AsistenciaEcpDetalle extends Model
{
    protected $table = 'controlecp_asistencia_detalles';
    protected $fillable = ['id_asistencia', 'id_aprendiz', 'presente', 'observacion'];
    protected $casts = ['presente' => 'boolean'];
    public function aprendiz() { return $this->belongsTo(\Modules\SICA\Entities\Apprentice::class, 'id_aprendiz'); }
}
