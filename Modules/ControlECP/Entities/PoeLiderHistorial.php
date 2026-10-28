<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PoeLiderHistorial extends Model
{
    protected $table = 'poe_lideres_historial';
    protected $primaryKey = 'id_poe_lider_historial';
    public $timestamps = false;

    protected $fillable = [
        'id_poe',
        'id_usuario',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];

    /**
     * POE al que pertenece este registro de liderazgo.
     */
    public function poe()
    {
        return $this->belongsTo(Poe::class, 'id_poe');
    }

    /**
     * Usuario líder en este periodo.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
