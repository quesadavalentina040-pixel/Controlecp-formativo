<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class DisponibilidadInstructor extends Model
{
    // Tabla y llave primaria personalizada
    protected $table = 'disponibilidades_instructor';
    protected $primaryKey = 'id_disponibilidad_instructor';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_categoria_asesoria',
        'anio',
        'trimestre',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'habilitada',
        'vigente',
        'fecha_registro',
    ];

    protected $casts = [
        'habilitada'     => 'boolean',
        'vigente'        => 'boolean',
        'fecha_registro' => 'datetime',
    ];

    // Usuario instructor propietario de la disponibilidad
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaAsesoria::class, 'id_categoria_asesoria', 'id_categoria_asesoria');
    }
}
