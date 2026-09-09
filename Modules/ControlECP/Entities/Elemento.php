<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Elemento extends Model
{
    use SoftDeletes;

    protected $table = 'elementos';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'estado',
    ];

    protected $dates = [
        'deleted_at',
    ];
}