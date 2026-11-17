<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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

    public function getModuloMostradoAttribute(): string
    {
        $modulo = trim($this->repararTexto((string) ($this->attributes['modulo'] ?? '')));
        $moduloNormalizado = Str::lower(Str::ascii($modulo));

        if ($modulo !== '' && !in_array($moduloNormalizado, ['controlecp', 'general'], true)) {
            return $modulo;
        }

        $tipoReferencia = Str::lower(Str::ascii(trim(
            $this->repararTexto((string) ($this->attributes['referencia_tipo'] ?? ''))
        )));
        $moduloPorReferencia = match ($tipoReferencia) {
            'cronograma' => 'Cronograma',
            'actividad' => 'Actividades',
            'asesoria', 'asesorias' => 'Asesorías',
            'encuesta' => 'Encuestas',
            'pqr', 'pqrs' => 'PQRS',
            'certificacion', 'certificacion_pendiente' => 'Certificados',
            'inventario' => 'Inventario',
            'poe' => 'POE',
            'asistencia' => 'Asistencia',
            'ficha' => 'Fichas',
            'momento' => 'Momentos',
            'acompanamiento', 'acompaniamiento' => auth()->user()?->hasRole('controlecp.apprentice')
                ? null
                : 'Acompañamiento',
            default => null,
        };

        return $moduloPorReferencia ?? 'ControlECP';
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

    public function getTituloAttribute($value): string
    {
        if (strtolower((string) ($this->attributes['referencia_tipo'] ?? '')) === 'acompanamiento') {
            $titulo = $this->repararTexto((string) $value);
            if (str_contains(mb_strtolower($titulo), 'sesiones de acompañamiento')
                || str_contains(mb_strtolower($titulo), 'próxima sesión de acompañamiento')) {
                return $titulo;
            }

            return str_contains(mb_strtolower((string) $value), 'cancel')
                ? 'Sesión de acompañamiento cancelada'
                : 'Aviso de sesión de acompañamiento';
        }

        return $this->repararTexto((string) $value);
    }

    public function getMensajeAttribute($value): ?string
    {
        if (strtolower((string) ($this->attributes['referencia_tipo'] ?? '')) !== 'acompanamiento') {
            return $value === null ? null : $this->repararTexto((string) $value);
        }

        $tituloRaw = $this->repararTexto((string) ($this->attributes['titulo'] ?? ''));
        if (str_contains(mb_strtolower((string) $value), 'sesiones de acompañamiento')
            || str_contains(mb_strtolower($tituloRaw), 'próxima sesión de acompañamiento')
            || str_contains(mb_strtolower($tituloRaw), 'sesiones de acompañamiento')) {
            return $this->formatearHoras12h($this->repararTexto((string) $value));
        }

        preg_match('/\b(\d{2}\/\d{2}\/\d{4})\b/u', (string) $value, $fecha);
        preg_match_all('/\b([01]\d|2[0-3]):[0-5]\d\b/u', (string) $value, $horas);

        if (empty($fecha[1])) {
            return 'Hay una novedad sobre tu sesión de acompañamiento.';
        }

        $mensaje = 'Fecha: ' . $fecha[1];
        if (count($horas[0]) >= 2) {
            $mensaje .= ' · Hora: ' . $this->formatearHora12h($horas[0][0]) . ' a ' . $this->formatearHora12h($horas[0][1]);
        }

        return $mensaje . '.';
    }

    private function formatearHoras12h(string $texto): string
    {
        $texto = preg_replace_callback(
            '/\b([01]\d|2[0-3]):([0-5]\d)\b/',
            fn (array $hora) => $this->formatearHora12h($hora[0]),
            $texto
        );

        return preg_replace('/([ap]\. m\.)\.$/u', '$1', $texto);
    }

    private function formatearHora12h(string $hora): string
    {
        $fecha = \Carbon\Carbon::createFromFormat('!H:i', $hora);
        $periodo = $fecha->format('a') === 'am' ? 'a. m.' : 'p. m.';

        return $fecha->format('g:i') . ' ' . $periodo;
    }

    private function repararTexto(string $texto): string
    {
        if (str_contains($texto, 'Ã') || str_contains($texto, 'Â')) {
            return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
        }

        return $texto;
    }

    // Genera la URL de destino según el tipo de referencia
    public function getUrlReferenciaAttribute(): string
    {
        if (auth()->user()?->hasRole('controlecp.apprentice')) {
            return match (strtolower((string) $this->referencia_tipo)) {
                'asesoria' => route('controlecp.aprendiz.asesorias'),
                'pqr', 'pqrs' => route('controlecp.aprendiz.pqr'),
                'cronograma' => route('controlecp.aprendiz.cronograma'),
                default => route('controlecp.aprendiz.notificaciones'),
            };
        }

        if (auth()->user()?->hasRole('controlecp.instructor')
            && strtolower((string) $this->referencia_tipo) === 'asesoria') {
            return route('controlecp.instructor.asesorias');
        }

        return match (strtolower((string)$this->referencia_tipo)) {
            'asesoria' => route('controlecp.administrador.asesorias.index'),
            'pqr', 'pqrs' => route('controlecp.administrador.inicio'),
            'poe' => route('controlecp.administrador.repositorio.poe.index'),
            default => route('controlecp.notificaciones.index'),
        };
    }
}
