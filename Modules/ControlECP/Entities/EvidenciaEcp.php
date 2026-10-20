<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\SICA\Entities\Course;
use App\Models\User;

class EvidenciaEcp extends Model
{
    use SoftDeletes;

    protected $table = 'controlecp_evidencias';

    protected $fillable = [
        'actividad_id',
        'course_id',
        'user_id',
        'tipo',
        'archivo',
        'enlace',
        'descripcion',
        'fecha_registro',
    ];

    protected $casts = [
        'fecha_registro' => 'datetime',
    ];

    public function actividad()
    {
        return $this->belongsTo(ActividadEcp::class, 'actividad_id');
    }

    public function ficha()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Devuelve la URL pública del archivo o el enlace
    public function getUrlAttribute(): ?string
    {
        if ($this->tipo === 'foto' && $this->archivo) {
            return asset('storage/' . $this->archivo);
        }
        return $this->enlace;
    }
}
