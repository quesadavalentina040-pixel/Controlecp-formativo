<?php

namespace Modules\Direccion\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Politica extends Model
{
    use SoftDeletes;

    protected $table = 'politicas';

    protected $fillable = [
        'codigo',
        'titulo',
        'tipo',
        'descripcion',
        'vigencia',
        'estado',
        'responsable'
    ];

    protected $dates = ['deleted_at'];
}
