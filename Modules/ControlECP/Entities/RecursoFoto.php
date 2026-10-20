<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecursoFoto extends Model
{
    protected $table = 'recurso_fotos';
    protected $primaryKey = 'id_recurso_foto';

    protected $fillable = [
        'id_inventario',
        'ruta',
    ];

    /**
     * Relación: Pertenece a un recurso de inventario.
     */
    public function inventario(): BelongsTo
    {
        return $this->belongsTo(Inventario::class, 'id_inventario', 'id_inventario');
    }
}
