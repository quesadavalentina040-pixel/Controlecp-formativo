<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Acompanamiento extends Model
{
    protected $table = 'controlecp_acompaniamientos';
    protected $primaryKey = 'id_acompanamiento';
    protected $fillable = ['id_instructor', 'id_aprendiz', 'categoria', 'motivo', 'fecha_inicio', 'intervalo_dias', 'total_sesiones', 'estado', 'resumen_final', 'objetivo_cumplido'];
    protected $casts = ['fecha_inicio' => 'date'];

    public function instructor() { return $this->belongsTo(User::class, 'id_instructor'); }
    public function aprendiz() { return $this->belongsTo(User::class, 'id_aprendiz'); }
    public function sesiones() { return $this->hasMany(AcompanamientoSesion::class, 'id_acompanamiento')->orderBy('fecha')->orderBy('hora_inicio'); }
    public function historial() { return $this->hasMany(AcompanamientoHistorial::class, 'id_acompanamiento')->latest(); }
}
