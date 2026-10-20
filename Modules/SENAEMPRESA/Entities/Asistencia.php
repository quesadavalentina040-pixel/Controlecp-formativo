<?php

namespace Modules\SENAEMPRESA\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;
use Modules\SICA\Entities\Apprentice;

class Asistencia extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable,
        SoftDeletes,
        HasFactory;

    protected $table = 'asistencias';

    protected $fillable = [
        'nombre',
        'descripcion',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'estado',
        'tipo',
        'sicefa_id',
        'sicefa_synced_at',
    ];

    protected $dates = ['deleted_at', 'sicefa_synced_at'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'fecha' => 'date',
        'hora_inicio' => 'datetime:H:i',
        'hora_fin' => 'datetime:H:i',
        'sicefa_synced_at' => 'datetime',
    ];

    // MUTADORES
    public function setNombreAttribute($value): void
    {
        $this->attributes['nombre'] = mb_strtoupper($value);
    }

    // RELACIONES

    /**
     * Accede a todos los aprendices registrados en esta sesión de asistencia (PIVOTE).
     */
    public function apprentices()
    {
        return $this->belongsToMany(Apprentice::class, 'apprentice_asistencia')
            ->withPivot(['asistencia', 'observacion', 'sicefa_id', 'sicefa_synced_at'])
            ->withTimestamps();
    }

    // ACCESORES

    /**
     * Cantidad de aprendices que asistieron.
     */
    public function getTotalAsistioAttribute(): int
    {
        return $this->apprentices()->wherePivot('asistencia', 'ASISTIO')->count();
    }

    /**
     * Cantidad de aprendices que faltaron.
     */
    public function getTotalFaltoAttribute(): int
    {
        return $this->apprentices()->wherePivot('asistencia', 'FALTO')->count();
    }

    /**
     * Cantidad de aprendices con excusa.
     */
    public function getTotalExcusaAttribute(): int
    {
        return $this->apprentices()->wherePivot('asistencia', 'EXCUSA')->count();
    }

    /**
     * Cantidad de aprendices con retardo.
     */
    public function getTotalRetardoAttribute(): int
    {
        return $this->apprentices()->wherePivot('asistencia', 'RETARDO')->count();
    }

    /**
     * Total de aprendices registrados en la sesión.
     */
    public function getTotalApprenticesAttribute(): int
    {
        return $this->apprentices()->count();
    }

    /**
     * Indica si la sesión ya fue sincronizada con SICEFA.
     */
    public function getIsSyncedAttribute(): bool
    {
        return !is_null($this->sicefa_id) && !is_null($this->sicefa_synced_at);
    }
}
