<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AsesoriaSeguimiento extends Model
{
    // Tabla y llave primaria personalizada
    protected $table = 'asesoria_seguimientos';
    protected $primaryKey = 'id_asesoria_seguimiento';
    public $timestamps = false;

    protected $fillable = [
        'id_asesoria',
        'id_usuario',
        'id_asesoria_seguimiento_corrige',
        'avances',
        'compromisos',
        'observaciones',
        'motivo_correccion',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    // Asesoría vinculada
    public function asesoria()
    {
        return $this->belongsTo(Asesoria::class, 'id_asesoria');
    }

    // Usuario instructor/admin que registra el seguimiento
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    // Seguimiento original al que este registro corrige
    public function seguimientoOriginal()
    {
        return $this->belongsTo(self::class, 'id_asesoria_seguimiento_corrige');
    }

    // Correcciones emitidas sobre este seguimiento
    public function correcciones()
    {
        return $this->hasMany(self::class, 'id_asesoria_seguimiento_corrige', 'id_asesoria_seguimiento');
    }
}
