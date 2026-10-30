<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Encuesta extends Model
{
    protected $table = 'encuestas';
    protected $primaryKey = 'id_encuesta';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'nombre',
        'descripcion',
        'estado',
        'fecha_creacion',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
    ];

    /**
     * Accesor para compatibilidad con id
     */
    public function getIdAttribute()
    {
        return $this->attributes['id_encuesta'] ?? null;
    }

    /**
     * Boot del modelo para asignar valores por defecto al crear
     */
    protected static function booted()
    {
        static::creating(function ($encuesta) {
            if (empty($encuesta->id_usuario)) {
                $encuesta->id_usuario = Auth::id() ?? 1;
            }
            if (empty($encuesta->estado)) {
                $encuesta->estado = 'Borrador';
            }
            if (empty($encuesta->fecha_creacion)) {
                $encuesta->fecha_creacion = now();
            }
        });
    }

    /**
     * Preguntas asociadas a la encuesta ordenadas por su posición
     */
    public function preguntas()
    {
        return $this->hasMany(Pregunta::class, 'id_encuesta', 'id_encuesta')
            ->orderBy('orden', 'asc')
            ->orderBy('id_pregunta', 'asc');
    }

    /**
     * Actividades formativas asociadas a esta encuesta (reutilización por id_encuesta)
     */
    public function actividades()
    {
        return $this->hasMany(Actividad::class, 'id_encuesta', 'id_encuesta');
    }

    /**
     * Usuario que creó la encuesta
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    /**
     * "Encuesta con respuestas" = existe algún registro en encuesta_respuestas
     * cuyo cronograma pertenezca a una actividad vinculada a esa encuesta (Sección 9).
     */
    public function tieneRespuestas(): bool
    {
        $actividadesIds = $this->actividades()->pluck('id');
        if ($actividadesIds->isEmpty()) {
            return false;
        }

        $cronogramasIds = DB::table('cronograma')
            ->whereIn('id_actividad', $actividadesIds)
            ->pluck('id_cronograma');

        if ($cronogramasIds->isEmpty()) {
            return false;
        }

        return DB::table('encuesta_respuestas')
            ->whereIn('id_cronograma', $cronogramasIds)
            ->exists();
    }

    /**
     * Número total de respuestas registradas por cronogramas de actividades asociadas
     */
    public function totalRespuestas(): int
    {
        $actividadesIds = $this->actividades()->pluck('id');
        if ($actividadesIds->isEmpty()) {
            return 0;
        }

        $cronogramasIds = DB::table('cronograma')
            ->whereIn('id_actividad', $actividadesIds)
            ->pluck('id_cronograma');

        if ($cronogramasIds->isEmpty()) {
            return 0;
        }

        return DB::table('encuesta_respuestas')
            ->whereIn('id_cronograma', $cronogramasIds)
            ->count();
    }

    /**
     * Indica si se pueden agregar, editar, reordenar o eliminar preguntas
     */
    public function puedeModificarPreguntas(): bool
    {
        return !$this->tieneRespuestas();
    }

    public function esBorrador(): bool
    {
        return $this->estado === 'Borrador';
    }

    public function estaActiva(): bool
    {
        return $this->estado === 'Activa';
    }

    public function estaInactiva(): bool
    {
        return $this->estado === 'Inactiva';
    }

    /**
     * "Programaciones pendientes" = cronogramas futuros o no ejecutados de las
     * actividades asociadas (revisa los campos de fecha/estado de cronograma) (Sección 9).
     */
    public function tieneProgramacionesPendientes(): bool
    {
        $actividadesIds = $this->actividades()->pluck('id');
        if ($actividadesIds->isEmpty()) {
            return false;
        }

        return DB::table('cronograma')
            ->whereIn('id_actividad', $actividadesIds)
            ->where(function ($q) {
                $q->whereIn('estado', ['Pendiente', 'En proceso'])
                  ->orWhere(function ($sub) {
                      $sub->whereDate('fecha', '>=', now()->toDateString())
                          ->whereNotIn('estado', ['Finalizada', 'Cancelada']);
                  });
            })
            ->exists();
    }

    /**
     * Nombres de las actividades asociadas que tienen programaciones pendientes
     */
    public function nombresActividadesConProgramacionesPendientes()
    {
        $actividadesIds = $this->actividades()->pluck('id');
        if ($actividadesIds->isEmpty()) {
            return collect();
        }

        $pendientesIds = DB::table('cronograma')
            ->whereIn('id_actividad', $actividadesIds)
            ->where(function ($q) {
                $q->whereIn('estado', ['Pendiente', 'En proceso'])
                  ->orWhere(function ($sub) {
                      $sub->whereDate('fecha', '>=', now()->toDateString())
                          ->whereNotIn('estado', ['Finalizada', 'Cancelada']);
                  });
            })
            ->pluck('id_actividad')
            ->unique();

        return $this->actividades()->whereIn('id', $pendientesIds)->pluck('nombre');
    }
}
