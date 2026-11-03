<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;

class AcompanamientoHistorial extends Model
{
    protected $table = 'controlecp_acompanamiento_historial';
    protected $fillable = ['id_acompanamiento', 'id_sesion', 'id_usuario', 'accion', 'motivo', 'datos_anteriores'];
    protected $casts = ['datos_anteriores' => 'array'];
}
