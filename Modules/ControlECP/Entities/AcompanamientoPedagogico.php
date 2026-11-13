<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcompanamientoPedagogico extends Model
{
    protected $table = 'controlecp_acompanamientos';

    protected $fillable = [
        'instructor_id',
        'aprendiz_id',
        'categoria',
        'motivo',
        'fecha_inicio',
        'intervalo_dias',
        'sesiones_planeadas',
        'estado',
        'resumen_final',
        'objetivo_cumplido',
        'cerrado_en',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'objetivo_cumplido' => 'boolean',
        'cerrado_en' => 'datetime',
    ];

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function aprendiz(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprendiz_id');
    }

    public function sesiones(): HasMany
    {
        return $this->hasMany(AcompanamientoSesion::class, 'acompanamiento_id')->orderBy('numero');
    }

    public function historial(): HasMany
    {
        return $this->hasMany(AcompanamientoHistorial::class, 'acompanamiento_id')->latest();
    }
}
