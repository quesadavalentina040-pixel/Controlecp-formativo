<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CronogramaHistorial extends Model
{
    protected $table = 'cronograma_historial';
    protected $primaryKey = 'id_cronograma_historial';

    protected $fillable = [
        'id_cronograma',
        'id_usuario_autor',
        'id_usuario_instructor_anterior',
        'id_usuario_instructor_nuevo',
        'accion',
        'fecha_anterior',
        'fecha_nueva',
        'motivo',
        'fecha',
    ];

    protected $casts = [
        'fecha_anterior' => 'date:Y-m-d',
        'fecha_nueva'    => 'date:Y-m-d',
        'fecha'          => 'datetime',
    ];

    public function cronograma(): BelongsTo
    {
        return $this->belongsTo(Cronograma::class, 'id_cronograma', 'id_cronograma');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_autor', 'id');
    }

    public function instructorAnterior(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_instructor_anterior', 'id');
    }

    public function instructorNuevo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_instructor_nuevo', 'id');
    }
}
