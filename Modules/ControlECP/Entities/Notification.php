<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    // Configuración de tabla y clave primaria
    protected $table = 'notificaciones';
    protected $primaryKey = 'id_notificacion';
    public $timestamps = false;

    // Atributos asignables masivamente
    protected $fillable = [
        'id_usuario_receptor',
        'id_usuario_causante',
        'modulo',
        'titulo',
        'mensaje',
        'referencia_tipo',
        'referencia_id',
        'id_poe_programacion',
        'clave_evento',
        'clave_agrupacion',
        'cantidad',
        'leida',
        'fecha_generacion',
        'fecha_lectura',
    ];

    // Casteo de tipos de datos
    protected $casts = [
        'leida'            => 'boolean',
        'cantidad'         => 'integer',
        'fecha_generacion' => 'datetime',
        'fecha_lectura'    => 'datetime',
    ];

    // Relación con el usuario que recibe la notificación
    public function receptor()
    {
        return $this->belongsTo(User::class, 'id_usuario_receptor');
    }

    // Relación con el usuario causante del evento
    public function causante()
    {
        return $this->belongsTo(User::class, 'id_usuario_causante');
    }

    // Relación con la programación de POE (si aplica)
    public function programacionPoe()
    {
        return $this->belongsTo(PoeProgramacion::class, 'id_poe_programacion');
    }

    // Filtro para notificaciones no leídas
    public function scopeNoLeidas($query)
    {
        return $query->where('leida', false);
    }

    // Filtro para notificaciones del usuario autenticado
    public function scopeDelUsuario($query, $idUsuario)
    {
        return $query->where('id_usuario_receptor', $idUsuario);
    }

    // Genera la URL de destino según el tipo de referencia
    public function getUrlReferenciaAttribute(): string
    {
        return match (strtolower((string)$this->referencia_tipo)) {
            'asesoria' => route('controlecp.administrador.asesorias.index'),
            'pqr', 'pqrs' => route('controlecp.administrador.inicio'),
            'poe' => route('controlecp.administrador.repositorio.poe.index'),
            default => route('controlecp.notificaciones.index'),
        };
    }
}
