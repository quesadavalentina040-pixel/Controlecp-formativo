<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\SICA\Entities\Inventory;

class AsesoriaRecurso extends Model
{
    // Tabla y llave primaria personalizada
    protected $table = 'asesoria_recursos';
    protected $primaryKey = 'id_asesoria_recurso';
    public $timestamps = false;

    protected $fillable = [
        'id_asesoria',
        'id_inventario',
        'cantidad',
    ];

    // Asesoría vinculada
    public function asesoria()
    {
        return $this->belongsTo(Asesoria::class, 'id_asesoria');
    }

    // Recurso de inventario (SICA)
    public function inventario()
    {
        return $this->belongsTo(Inventory::class, 'id_inventario');
    }
}
