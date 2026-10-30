<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CategoriaAsesoria extends Model
{
    // Tabla y llave primaria personalizada
    protected $table = 'categorias_asesoria';
    protected $primaryKey = 'id_categoria_asesoria';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'estado',
    ];

    // Instructores vinculados a esta categoría
    public function instructores()
    {
        return $this->belongsToMany(User::class, 'instructor_categoria', 'id_categoria_asesoria', 'id_usuario');
    }

    // Asesorías asociadas a esta categoría
    public function asesorias()
    {
        return $this->hasMany(Asesoria::class, 'id_categoria_asesoria', 'id_categoria_asesoria');
    }
}
