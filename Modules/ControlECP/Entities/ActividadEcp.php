<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActividadEcp extends Model
{
    use SoftDeletes;

    protected $table = 'controlecp_actividades';

    protected $fillable = [
        'momento_id',
        'numero',
        'nombre',
        'descripcion',
        'objetivo',
        'duracion',
        'duracion_min',
        'metodologia',
        'estado',
        'tiene_asistencia',
        'encuesta_id',
        'tiene_evidencia',
    ];

    protected $casts = [
        'duracion_min'    => 'integer',
        'tiene_asistencia'=> 'boolean',
        'tiene_evidencia' => 'boolean',
    ];

    // ── Relaciones ────────────────────────────────────────────
    public function momento()
    {
        return $this->belongsTo(Momento::class, 'momento_id');
    }

    public function encuesta()
    {
        return $this->belongsTo(EncuestaEcp::class, 'encuesta_id');
    }

    public function evidencias()
    {
        return $this->hasMany(EvidenciaEcp::class, 'actividad_id');
    }

    public function calificaciones()
    {
        return $this->hasMany(CalificacionEcp::class, 'actividad_id');
    }

    // ── Scopes ────────────────────────────────────────────────
    public function scopeActivos($query)
    {
        return $query->where('estado', 'Activo');
    }

    public function scopeFinalizados($query)
    {
        return $query->where('estado', 'Finalizado');
    }

    // ── Accessors ─────────────────────────────────────────────
    public function getPromedioEstrellaAttribute(): float
    {
        return round($this->calificaciones()->avg('estrellas') ?? 0, 1);
    }
}
