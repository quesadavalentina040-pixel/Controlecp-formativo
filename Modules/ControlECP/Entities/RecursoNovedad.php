<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecursoNovedad extends Model
{
    protected $table = 'recurso_novedades';
    protected $primaryKey = 'id_recurso_novedad';

    protected $fillable = [
        'id_inventario',
        'id_usuario',
        'descripcion',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    /**
     * Relación: Pertenece a un recurso de inventario.
     */
    public function inventario(): BelongsTo
    {
        return $this->belongsTo(Inventario::class, 'id_inventario', 'id_inventario');
    }

    /**
     * Relación: Pertenece a un usuario que reportó la novedad.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }
}
