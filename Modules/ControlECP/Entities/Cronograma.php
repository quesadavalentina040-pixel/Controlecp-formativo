<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\SICA\Entities\Course;

class Cronograma extends Model
{
    protected $table = 'cronograma';
    protected $primaryKey = 'id_cronograma';

    protected $fillable = [
        'id_actividad',
        'id_ficha',
        'id_instructor',
        'id_usuario_creador',
        'id_usuario_finalizador',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'lugar',
        'estado',
        'fecha_original',
        'motivo_posposicion',
        'fecha_finalizacion',
    ];

    protected $casts = [
        'fecha'              => 'date:Y-m-d',
        'fecha_original'     => 'date:Y-m-d',
        'fecha_finalizacion' => 'datetime',
    ];

    // ── RELACIONES ───────────────────────────────────────────────

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(ActividadEcp::class, 'id_actividad', 'id');
    }

    public function ficha(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'id_ficha', 'id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_instructor', 'id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_creador', 'id');
    }

    public function finalizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_finalizador', 'id');
    }

    public function recursos(): HasMany
    {
        return $this->hasMany(CronogramaRecurso::class, 'id_cronograma', 'id_cronograma');
    }

    public function historial(): HasMany
    {
        return $this->hasMany(CronogramaHistorial::class, 'id_cronograma', 'id_cronograma')
                    ->orderBy('created_at', 'desc');
    }

    // ── ACCESORES / LÓGICA DE NEGOCIO (HU-006) ───────────────────

    /**
     * Criterio 6: El semáforo se aplica SOLO a las actividades pendientes.
     * - rojo: Hoy o muy próxima (<= 2 días o si ya venció)
     * - amarillo: Aproximadamente una semana (3 a 7 días)
     * - verde: Dos semanas o más (>= 8 días)
     * - Las finalizadas y pospuestas se muestran sin color (null).
     */
    public function getSemaforoAttribute(): ?string
    {
        if ($this->estado !== 'Pendiente') {
            return null; // Sin color para finalizadas, pospuestas, etc.
        }

        if (!$this->fecha) {
            return null;
        }

        $hoy = Carbon::today();
        $fechaSesion = Carbon::parse($this->fecha)->startOfDay();
        $diasDiferencia = $hoy->diffInDays($fechaSesion, false);

        if ($diasDiferencia <= 2) {
            return 'rojo';
        }

        if ($diasDiferencia <= 7) {
            return 'amarillo';
        }

        return 'verde';
    }

    /**
     * Etiqueta descriptiva del semáforo.
     */
    public function getSemaforoTextoAttribute(): string
    {
        $sem = $this->semaforo;
        if (!$sem) {
            return $this->estado === 'Finalizada' ? 'Finalizada' : ($this->estado === 'Pospuesta' ? 'Pospuesta' : $this->estado);
        }

        return match ($sem) {
            'rojo'     => 'Crítico / Muy Próximo',
            'amarillo' => 'Próximo (~1 Semana)',
            'verde'    => 'En Tiempo (>= 2 Semanas)',
            default    => 'Sin Semáforo',
        };
    }

    /**
     * Criterio 1: Nombre en calendario "Actividad X – Momento Y"
     */
    public function getTituloCalendarioAttribute(): string
    {
        $act = $this->actividad;
        $numAct = $act ? ($act->numero ? "Actividad {$act->numero}" : "Actividad {$act->id}") : 'Actividad';
        $nomMom = $act && $act->momento ? $act->momento->nombre : 'Sin Momento';

        return "{$numAct} – {$nomMom}";
    }

    /**
     * Días faltantes (positivos: días restantes; negativos: días de retraso).
     */
    public function getDiasRestantesAttribute(): int
    {
        if (!$this->fecha) return 0;
        return (int) Carbon::today()->diffInDays(Carbon::parse($this->fecha), false);
    }

    /**
     * Formato de horario 12 horas legible (ej: 08:00 AM - 10:00 AM)
     */
    public function getHorarioFormateadoAttribute(): string
    {
        $ini = $this->hora_inicio ? Carbon::createFromFormat('H:i:s', $this->hora_inicio)->format('h:i A') : '--';
        $fin = $this->hora_fin ? Carbon::createFromFormat('H:i:s', $this->hora_fin)->format('h:i A') : '--';
        return "{$ini} - {$fin}";
    }
}
