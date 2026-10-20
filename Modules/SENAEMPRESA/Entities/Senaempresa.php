<?php

namespace Modules\SENAEMPRESA\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Stub de la entidad Senaempresa (unidades productivas de SENA Empresa).
 * Referenciada por Modules\SICA\Entities\Course.
 */
class Senaempresa extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable,
        SoftDeletes;

    protected $table = 'senaempresas';

    protected $fillable = ['nombre', 'descripcion', 'estado'];

    protected $hidden = ['created_at', 'updated_at'];

    public function courses()
    {
        return $this->belongsToMany(\Modules\SICA\Entities\Course::class);
    }
}
