<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CalificacionAsesoria extends Model
{
    protected $table = 'controlecp_calificaciones_asesorias';

    protected $fillable = [
        'id_asesoria',
        'user_id',
        'estrellas',
        'comentario',
    ];

    protected $casts = [
        'estrellas' => 'integer',
    ];

    public function asesoria()
    {
        return $this->belongsTo(Asesoria::class, 'id_asesoria', 'id_asesoria');
    }

    public function aprendiz()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
