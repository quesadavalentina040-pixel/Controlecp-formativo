<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CronogramaRecurso extends Model
{
    protected $table = 'cronograma_recursos';
    protected $primaryKey = 'id_cronograma_recurso';

    protected $fillable = [
        'id_cronograma',
        'id_inventario',
        'cantidad',
    ];

    protected $casts = [
        'cantidad' => 'integer',
    ];

    /**
     * Relación: Pertenece a un recurso de inventario.
     */
    public function inventario(): BelongsTo
    {
        return $this->belongsTo(Inventario::class, 'id_inventario', 'id_inventario');
    }
}
