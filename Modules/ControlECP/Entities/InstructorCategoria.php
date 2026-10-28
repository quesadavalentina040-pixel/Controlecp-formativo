<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class InstructorCategoria extends Model
{
    // Tabla y llave primaria personalizada
    protected $table = 'instructor_categoria';
    protected $primaryKey = 'id_instructor_categoria';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_categoria_asesoria',
    ];

    // Usuario instructor
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    // Categoría asociada
    public function categoria()
    {
        return $this->belongsTo(CategoriaAsesoria::class, 'id_categoria_asesoria');
    }
}
