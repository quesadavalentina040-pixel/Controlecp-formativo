<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcompanamientoHistorial extends Model
{
    protected $table = 'controlecp_acompanamiento_historial';

    protected $fillable = [
        'acompanamiento_id',
        'sesion_id',
        'usuario_id',
        'accion',
        'motivo',
        'datos',
    ];

    protected $casts = [
        'datos' => 'array',
    ];

    public function acompanamiento(): BelongsTo
    {
        return $this->belongsTo(AcompanamientoPedagogico::class, 'acompanamiento_id');
    }

    public function sesion(): BelongsTo
    {
        return $this->belongsTo(AcompanamientoSesion::class, 'sesion_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
