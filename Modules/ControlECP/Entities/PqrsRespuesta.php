<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PqrsRespuesta extends Model
{
    protected $table = 'controlecp_pqrs_respuestas';
    protected $primaryKey = 'id_pqrs_respuesta';

    protected $fillable = [
        'id_pqrs',
        'id_usuario',
        'respuesta',
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
     * Relación: Pertenece al usuario que emitió la respuesta.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }
}
