<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcompanamientoSesion extends Model
{
    protected $table = 'controlecp_acompanamiento_sesiones';

    protected $fillable = [
        'acompanamiento_id',
        'numero',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'estado',
        'observaciones',
        'avances',
        'compromisos',
        'motivo',
        'registrada_en',
        'aviso_un_dia_enviado_en',
        'aviso_aprendiz_enviado_en',
        'aviso_sin_registro_enviado_en',
    ];

    protected $casts = [
        'fecha' => 'date',
        'registrada_en' => 'datetime',
        'aviso_un_dia_enviado_en' => 'datetime',
        'aviso_aprendiz_enviado_en' => 'datetime',
        'aviso_sin_registro_enviado_en' => 'datetime',
    ];

    public function acompanamiento(): BelongsTo
    {
        return $this->belongsTo(AcompanamientoPedagogico::class, 'acompanamiento_id');
    }

    public function historial(): HasMany
    {
        return $this->hasMany(AcompanamientoHistorial::class, 'sesion_id');
    }

    public function evidencias(): HasMany
    {
        return $this->hasMany(AcompanamientoEvidencia::class, 'sesion_id');
    }
}
