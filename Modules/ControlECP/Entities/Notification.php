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
        $esInstructor = auth()->check() && auth()->user()->hasRole('controlecp.instructor');
        $esAprendiz = auth()->check() && auth()->user()->hasRole('controlecp.apprentice');
        $tipo = strtolower((string) $this->referencia_tipo);
        if ($esAprendiz) {
            return match ($tipo) {
                'asesoria' => route('controlecp.aprendiz.asesorias'),
                'pqr', 'pqrs' => route('controlecp.aprendiz.pqr'),
                'cronograma', 'actividad' => route('controlecp.aprendiz.cronograma'),
                'encuesta' => route('controlecp.aprendiz.encuesta'),
                default => route('controlecp.notificaciones.index'),
            };
        }
        $prefijo = $esInstructor ? 'controlecp.instructor.' : 'controlecp.administrador.';

        return match ($tipo) {
            'asesoria' => route($esInstructor ? 'controlecp.instructor.asesorias' : 'controlecp.administrador.asesorias.index'),
            'pqr', 'pqrs' => route($prefijo . 'pqr'),
            'poe' => route('controlecp.administrador.repositorio.poe.index'),
            'cronograma', 'actividad' => route($prefijo . 'cronograma'),
            'asistencia' => route($prefijo . 'asistencia'),
            'inventario' => route($prefijo . 'inventario'),
            'encuesta' => route($prefijo . 'repositorio.encuestas'),
            default => route('controlecp.notificaciones.index'),
        };
    }
}
