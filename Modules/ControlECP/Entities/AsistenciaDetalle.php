<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\SICA\Entities\Apprentice;

class AsistenciaDetalle extends Model
{
    protected $table = 'controlecp_asistencia_detalles';
    protected $fillable = ['asistencia_id', 'aprendiz_id', 'presente', 'justificada', 'observacion', 'excusa_imagen'];
    protected $casts = [
        'presente' => 'boolean',
        'justificada' => 'boolean',
    ];

    public function aprendiz()
    {
        return $this->belongsTo(Apprentice::class, 'aprendiz_id');
    }
}
