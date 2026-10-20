<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoPqrs extends Model
{
    protected $table = 'tipos_pqrs';
    protected $primaryKey = 'id_tipo_pqrs';

    protected $fillable = [
        'nombre',
    ];

    /**
     * Relación: Un tipo de PQR tiene muchas solicitudes PQR.
     */
    public function pqrs(): HasMany
    {
        return $this->hasMany(Pqrs::class, 'id_tipo_pqrs', 'id_tipo_pqrs');
    }
}
