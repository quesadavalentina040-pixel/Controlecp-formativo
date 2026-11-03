<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\SICA\Entities\Course;

class CertificacionMomento extends Model
{
    protected $table = 'controlecp_certificaciones_momento';
    protected $primaryKey = 'id_certificacion';

    protected $fillable = [
        'id_ficha',
        'id_momento',
        'id_instructor',
        'fecha_certificacion',
        'estado',
    ];

    protected $casts = [
        'fecha_certificacion' => 'datetime',
    ];

    public function ficha()
    {
        return $this->belongsTo(Course::class, 'id_ficha');
    }

    public function momento()
    {
        return $this->belongsTo(Momento::class, 'id_momento');
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'id_instructor');
    }
}
