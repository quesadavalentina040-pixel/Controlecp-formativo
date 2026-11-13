<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CalificacionAsesoria extends Model
{
    protected $table = 'controlecp_calificaciones_asesoria';

    protected $fillable = [
        'asesoria_id',
        'user_id',
        'estrellas',
        'comentario',
    ];

    protected $casts = [
        'estrellas' => 'integer',
    ];

    public function asesoria()
    {
        return $this->belongsTo(Asesoria::class, 'asesoria_id', 'id_asesoria');
    }

    public function aprendiz()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
