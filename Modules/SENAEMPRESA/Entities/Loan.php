<?php

namespace Modules\SENAEMPRESA\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Stub de la entidad Loan (préstamos de SENA Empresa).
 * Referenciada por Modules\SICA\Entities\Apprentice.
 */
class Loan extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable,
        SoftDeletes;

    protected $table = 'loans';

    protected $fillable = ['apprentice_id', 'estado', 'descripcion'];

    protected $hidden = ['created_at', 'updated_at'];

    public function apprentice()
    {
        return $this->belongsTo(\Modules\SICA\Entities\Apprentice::class);
    }
}
