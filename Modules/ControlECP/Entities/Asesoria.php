<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Asesoria extends Model
{
    // Tabla y llave primaria personalizada
    protected $table = 'asesorias';
    protected $primaryKey = 'id_asesoria';
    public $timestamps = false;

    protected $fillable = [
        'id_categoria_asesoria',
        'id_usuario_aprendiz',
        'id_usuario_instructor',
        'id_usuario_registro',
        'id_usuario_cancelador',
        'motivo',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'estado',
        'motivo_cancelacion',
        'fecha_solicitud',
    ];

    protected $casts = [
        'fecha'           => 'date',
        'fecha_solicitud' => 'datetime',
    ];

    // Categoría de la asesoría
    public function categoria()
    {
        return $this->belongsTo(CategoriaAsesoria::class, 'id_categoria_asesoria');
    }

    // Usuario aprendiz solicitante
    public function aprendiz()
    {
        return $this->belongsTo(User::class, 'id_usuario_aprendiz');
    }

    // Usuario instructor asignado
    public function instructor()
    {
        return $this->belongsTo(User::class, 'id_usuario_instructor');
    }

    // Usuario que registró la asesoría
    public function usuarioRegistro()
    {
        return $this->belongsTo(User::class, 'id_usuario_registro');
    }

    // Usuario que canceló la asesoría
    public function usuarioCancelador()
    {
        return $this->belongsTo(User::class, 'id_usuario_cancelador');
    }

    // Historial de reprogramaciones y cancelaciones
    public function historial()
    {
        return $this->hasMany(AsesoriaHistorial::class, 'id_asesoria', 'id_asesoria')->orderBy('fecha', 'desc');
    }

    // Seguimientos registrados
    public function seguimientos()
    {
        return $this->hasMany(AsesoriaSeguimiento::class, 'id_asesoria', 'id_asesoria')->orderBy('fecha', 'desc');
    }

    // Recursos de inventario asociados
    public function recursos()
    {
        return $this->hasMany(AsesoriaRecurso::class, 'id_asesoria', 'id_asesoria');
    }
}
