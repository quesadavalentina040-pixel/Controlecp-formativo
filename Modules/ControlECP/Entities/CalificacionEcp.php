<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\SICA\Entities\Course;
use App\Models\User;

class CalificacionEcp extends Model
{
    protected $table = 'controlecp_calificaciones';

    protected $fillable = [
        'actividad_id',
        'course_id',
        'user_id',
        'estrellas',
        'comentario',
    ];

    protected $casts = [
        'estrellas' => 'integer',
    ];

    public function actividad()
    {
        return $this->belongsTo(ActividadEcp::class, 'actividad_id');
    }

    public function ficha()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function aprendiz()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
