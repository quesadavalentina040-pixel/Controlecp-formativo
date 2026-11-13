<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\SICA\Entities\Course;

class AcompanamientoEvidencia extends Model
{
    protected $table = 'controlecp_acompanamiento_evidencias';

    protected $fillable = [
        'sesion_id',
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

    public function sesion(): BelongsTo
    {
        return $this->belongsTo(AcompanamientoSesion::class, 'sesion_id');
    }

    public function ficha(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getUrlAttribute(): ?string
    {
        return $this->tipo === 'foto' && $this->archivo
            ? asset('storage/' . $this->archivo)
            : $this->enlace;
    }
}
