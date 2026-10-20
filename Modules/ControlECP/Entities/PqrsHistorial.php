<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PqrsHistorial extends Model
{
    protected $table = 'controlecp_pqrs_historial';
    protected $primaryKey = 'id_pqrs_historial';

    protected $fillable = [
        'id_pqrs',
        'id_usuario',
        'estado_anterior',
        'estado_nuevo',
        'observacion',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    /**
     * Relación: Pertenece a una PQR.
     */
    public function pqrs(): BelongsTo
    {
        return $this->belongsTo(Pqrs::class, 'id_pqrs', 'id_pqrs');
    }

    /**
     * Relación: Pertenece al usuario que realizó el cambio de estado.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }
}
