<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsesoriaRecurso extends Model
{
    protected $table = 'asesoria_recursos';
    protected $primaryKey = 'id_asesoria_recurso';

    protected $fillable = [
        'id_asesoria',
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
