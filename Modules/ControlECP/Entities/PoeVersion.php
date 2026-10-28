<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PoeVersion extends Model
{
    protected $table = 'poe_versiones';
    protected $primaryKey = 'id_poe_version';
    public $timestamps = false;

    protected $fillable = [
        'id_poe',
        'id_usuario',
        'id_poe_programacion',
        'numero_version',
        'cambio',
        'archivo',
        'formato',
        'es_actual',
        'fecha',
    ];

    protected $casts = [
        'es_actual' => 'boolean',
        'fecha'     => 'datetime',
    ];

    /**
     * POE al que pertenece esta versión.
     */
    public function poe()
    {
        return $this->belongsTo(Poe::class, 'id_poe');
    }

    /**
     * Usuario que subió esta versión.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    /**
     * Programación vinculada si esta versión cumplió una fecha programada.
     */
    public function programacion()
    {
        return $this->belongsTo(PoeProgramacion::class, 'id_poe_programacion');
    }
}
