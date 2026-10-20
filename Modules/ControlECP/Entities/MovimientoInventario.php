<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';
    protected $primaryKey = 'id_movimiento_inventario';

    protected $fillable = [
        'id_inventario',
        'id_usuario',
        'id_movimiento_inventario_origen',
        'id_cronograma',
        'id_asesoria',
        'tipo',
        'cantidad',
        'estado_anterior',
        'estado_nuevo',
        'observacion',
        'foto',
        'fecha',
    ];

    protected $casts = [
        'fecha'    => 'datetime',
        'cantidad' => 'integer',
    ];

    /**
     * Relación: Pertenece a un recurso de inventario.
     */
    public function inventario(): BelongsTo
    {
        return $this->belongsTo(Inventario::class, 'id_inventario', 'id_inventario');
    }

    /**
     * Relación: Pertenece a un usuario que realizó el movimiento.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    /**
     * Relación: Movimiento de origen (ej. devolución que proviene de un préstamo).
     */
    public function movimientoOrigen(): BelongsTo
    {
        return $this->belongsTo(self::class, 'id_movimiento_inventario_origen', 'id_movimiento_inventario');
    }

    /**
     * Relación: Movimientos derivados de este.
     */
    public function movimientosHijos(): HasMany
    {
        return $this->hasMany(self::class, 'id_movimiento_inventario_origen', 'id_movimiento_inventario');
    }
}
