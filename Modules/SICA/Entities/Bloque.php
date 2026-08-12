<?php

namespace Modules\SICA\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bloque extends Model
{
    use SoftDeletes;

    protected $table = 'bloques';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'order_index'
    ];

    protected $dates = ['deleted_at'];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    // Relación con las Aplicaciones / Submódulos que pertenecen a este bloque
    public function apps()
    {
        return $this->hasMany(App::class)->orderBy('name');
    }
}
