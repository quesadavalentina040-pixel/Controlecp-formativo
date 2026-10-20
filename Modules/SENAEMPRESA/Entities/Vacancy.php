<?php

namespace Modules\SENAEMPRESA\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Stub de la entidad Vacancy (vacantes de SENA Empresa).
 * Referenciada por Modules\SICA\Entities\Course.
 */
class Vacancy extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable,
        SoftDeletes;

    protected $table = 'vacancies';

    protected $fillable = ['course_id', 'estado', 'descripcion'];

    protected $hidden = ['created_at', 'updated_at'];

    public function courses()
    {
        return $this->belongsToMany(\Modules\SICA\Entities\Course::class);
    }
}
