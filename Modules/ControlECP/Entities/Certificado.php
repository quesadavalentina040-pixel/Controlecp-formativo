<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use Modules\SICA\Entities\Course;
use Modules\ControlECP\Entities\AsistenciaRegistro;

/**
 * Modelo de certificación de ficha por momento ECP.
 *
 * Lógica de negocio:
 *   - Un certificado por (course_id + momento_id)
 *   - Para certificar un momento, la ficha debe tener 100% de actividades
 *     del momento con al menos un registro de asistencia
 *   - Los 3 momentos certificados = ficha completamente certificada
 *
 * Integración SICEFA:
 *   - sicefa_id / sicefa_synced_at: sincronización con la API REST Central
 *   - emitido_sofia: false hasta conectar con SOFIA Plus (HU-010)
 *   - sicefa_token_verificacion: para QR de verificación
 */
class Certificado extends Model
{
    use HasFactory;

    protected $table = 'certificados';

    protected $fillable = [
        'course_id',
        'momento_id',
        'certificado_por',
        'estado',
        'fecha_certificacion',
        'observacion',
        'actividades_completadas',
        'porcentaje_asistencia',
        'total_aprendices',
        'historial_cambios',
        'notificacion_generada',
        'notificacion_at',
        'emitido_sofia',
        'sicefa_id',
        'sicefa_synced_at',
        'sicefa_token_verificacion',
        'motivo_anulacion',
        'anulado_por',
        'anulado_at',
    ];

    protected $casts = [
        'fecha_certificacion'      => 'date',
        'actividades_completadas'  => 'array',
        'historial_cambios'        => 'array',
        'notificacion_generada'    => 'boolean',
        'emitido_sofia'            => 'boolean',
        'notificacion_at'          => 'datetime',
        'sicefa_synced_at'         => 'datetime',
        'anulado_at'               => 'datetime',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    // ─── CONSTANTES ───────────────────────────────────────────────────────────

    /** Actividades por momento (de las 18 actividades ECP totales) */
    public const ACTIVIDADES_POR_MOMENTO = [1 => 10, 2 => 4, 3 => 4];

    /** Colores por momento */
    public const MOMENTO_COLORES = [1 => '#39A900', 2 => '#0070B8', 3 => '#6A1B9A'];

    /** Nombres de momentos */
    public const MOMENTO_NOMBRES = [
        1 => 'Momento 1: Autoconciencia',
        2 => 'Momento 2: Convivencia',
        3 => 'Momento 3: Resolución',
    ];

    /** IDs de actividades por momento */
    public const ACTIVIDADES_IDS = [
        1 => [1,2,3,4,5,6,7,8,9,10],
        2 => [11,12,13,14],
        3 => [15,16,17,18],
    ];

    // ─── RELACIONES ───────────────────────────────────────────────────────────

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'certificado_por');
    }

    public function anulador()
    {
        return $this->belongsTo(User::class, 'anulado_por');
    }

    // ─── ACCESORES ────────────────────────────────────────────────────────────

    public function getMomentoNombreAttribute(): string
    {
        return self::MOMENTO_NOMBRES[$this->momento_id] ?? 'Momento '.$this->momento_id;
    }

    public function getMomentoColorAttribute(): string
    {
        return self::MOMENTO_COLORES[$this->momento_id] ?? '#6c757d';
    }

    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado) {
            'CERTIFICADA' => 'success',
            'ANULADA'     => 'secondary',
            default       => 'light',
        };
    }

    public function getIsAnuladoAttribute(): bool
    {
        return $this->estado === 'ANULADA';
    }

    public function getIsSyncedAttribute(): bool
    {
        return !is_null($this->sicefa_id) && !is_null($this->sicefa_synced_at);
    }

    // ─── MÉTODOS DE NEGOCIO ───────────────────────────────────────────────────

    /**
     * Registra una entrada en el historial de cambios (auditoría HU-012).
     */
    public function registrarCambio(string $accion, array $datos, int $usuarioId): void
    {
        $historial = $this->historial_cambios ?? [];
        $historial[] = [
            'accion'     => $accion,
            'datos'      => $datos,
            'usuario_id' => $usuarioId,
            'timestamp'  => now()->toIso8601String(),
        ];
        $this->historial_cambios = $historial;
    }

    /**
     * Genera el token de verificación único para QR e integración SICEFA.
     */
    public static function generarTokenVerificacion(int $courseId, int $momentoId): string
    {
        return hash('sha256', "ECP-{$courseId}-{$momentoId}-" . now()->timestamp . '-' . config('app.key'));
    }

    /**
     * Payload preparado para enviar a la API REST Central de SICEFA.
     * Contrato de integración definido y listo para conectar.
     */
    public function toSicefaPayload(): array
    {
        return [
            'ficha_code'         => $this->course?->code,
            'momento_id'         => $this->momento_id,
            'momento_nombre'     => $this->momento_nombre,
            'estado'             => $this->estado,
            'fecha_certificacion' => $this->fecha_certificacion?->format('Y-m-d'),
            'porcentaje_asistencia' => $this->porcentaje_asistencia,
            'total_aprendices'   => $this->total_aprendices,
            'responsable_doc'    => $this->responsable?->person?->document_number,
            'token_verificacion' => $this->sicefa_token_verificacion,
            'local_id'           => $this->id,
        ];
    }

    // ─── MÉTODOS ESTÁTICOS DE ANÁLISIS ────────────────────────────────────────

    /**
     * HU-006: Verifica si una ficha cumple el 100% de actividades
     * de un momento para poder certificar.
     *
     * Regla: todas las actividades del momento deben tener al menos
     * 1 registro de asistencia (estado != ANULADA) en la ficha.
     */
    public static function puedeSerCertificada(int $courseId, int $momentoId): array
    {
        $actividadesDelMomento = self::ACTIVIDADES_IDS[$momentoId] ?? [];
        $actividadesConRegistro = AsistenciaRegistro::where('course_id', $courseId)
            ->where('momento_id', $momentoId)
            ->where('estado', '!=', 'ANULADA')
            ->distinct()
            ->pluck('actividad_id')
            ->toArray();

        $faltantes = array_diff($actividadesDelMomento, $actividadesConRegistro);
        $porcentaje = count($actividadesDelMomento) > 0
            ? round((count($actividadesConRegistro) / count($actividadesDelMomento)) * 100, 1)
            : 0;

        return [
            'puede'              => empty($faltantes),
            'porcentaje'         => $porcentaje,
            'completadas'        => count($actividadesConRegistro),
            'total'              => count($actividadesDelMomento),
            'faltantes_ids'      => array_values($faltantes),
        ];
    }

    /**
     * HU-003: Obtiene el progreso de una ficha en los 3 momentos.
     */
    public static function progresoPorMomento(int $courseId): array
    {
        $resultado = [];
        foreach ([1, 2, 3] as $m) {
            $analisis  = self::puedeSerCertificada($courseId, $m);
            $cert      = self::where('course_id', $courseId)
                ->where('momento_id', $m)
                ->where('estado', 'CERTIFICADA')
                ->first();

            $resultado[$m] = [
                'momento_id'   => $m,
                'nombre'       => self::MOMENTO_NOMBRES[$m],
                'color'        => self::MOMENTO_COLORES[$m],
                'completadas'  => $analisis['completadas'],
                'total'        => $analisis['total'],
                'porcentaje'   => $analisis['porcentaje'],
                'puede_cert'   => $analisis['puede'],
                'certificado'  => $cert !== null,
                'cert_fecha'   => $cert?->fecha_certificacion?->format('d/m/Y'),
                'cert_resp'    => $cert?->responsable?->full_name,
                'cert_estado'  => $cert?->estado,
            ];
        }
        return $resultado;
    }
}
