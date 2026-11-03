<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;

class AcompanamientoSesion extends Model
{
    protected $table = 'controlecp_acompanamiento_sesiones';
    protected $primaryKey = 'id_sesion';
    protected $fillable = ['id_acompanamiento', 'fecha', 'hora_inicio', 'hora_fin', 'estado', 'observaciones', 'avances', 'compromisos', 'motivo', 'registrada_en'];
    protected $casts = ['fecha' => 'date', 'registrada_en' => 'datetime'];
    public function acompanamiento() { return $this->belongsTo(Acompanamiento::class, 'id_acompanamiento'); }
}
