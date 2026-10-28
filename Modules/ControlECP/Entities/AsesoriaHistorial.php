<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AsesoriaHistorial extends Model
{
    // Tabla y llave primaria personalizada
    protected $table = 'asesoria_historial';
    protected $primaryKey = 'id_asesoria_historial';
    public $timestamps = false;

    protected $fillable = [
        'id_asesoria',
        'id_usuario',
        'accion',
        'fecha_anterior',
        'hora_anterior',
        'fecha_nueva',
        'hora_nueva',
        'motivo',
        'fecha',
    ];

    protected $casts = [
        'fecha_anterior' => 'date',
        'fecha_nueva'    => 'date',
        'fecha'          => 'datetime',
    ];

    // Asesoría vinculada
    public function asesoria()
    {
        return $this->belongsTo(Asesoria::class, 'id_asesoria');
    }

    // Usuario que realizó la acción
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
