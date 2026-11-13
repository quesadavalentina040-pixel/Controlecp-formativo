<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;

class AcompanamientoCategoria extends Model
{
    protected $table = 'controlecp_acompanamiento_categorias';

    protected $fillable = [
        'instructor_id',
        'nombre',
    ];
}
