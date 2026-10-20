<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use Modules\SICA\Entities\Course;
use Modules\SICA\Entities\Apprentice;

/**
 * Modelo de registro de asistencia individual por aprendiz.
 * Soporta los 13 casos de uso del módulo de asistencia de Control ECP.
 *
 * Integración SICEFA:
 *  - fuente: 'api_central' si la lista de aprendices vino de la API REST de SICEFA
 *  - sicefa_id / sicefa_synced_at: para sincronización de vuelta al ERP
 */
class AsistenciaRegistro extends Model
{
    use HasFactory;

    protected $table = 'asistencia_registros';

    protected $fillable = [
        // Identificación de la sesión
        'course_id',
        'instructor_id',
        'actividad_id',
        'actividad_nombre',
        'momento_id',
        // Identificación del aprendiz
        'apprentice_id',
        'document_number',
        'full_name',
        // Estado de asistencia
        'estado',
        'observacion',
        'fecha',
        // Evidencia
        'evidencia_url',
        'evidencia_tipo',
        // Auditoría de edición
        'motivo_edicion',
        'editado_por',
        'editado_at',
        // Anulación
        'motivo_anulacion',
        'anulado_por',
        'anulado_at',
        // Historial de cambios (JSON)
        'historial_cambios',
        // Fuente y sincronización
        'fuente',
        'sicefa_id',
        'sicefa_synced_at',
    ];

    protected $casts = [
        'fecha'            => 'date',
        'editado_at'       => 'datetime',
        'anulado_at'       => 'datetime',
        'sicefa_synced_at' => 'datetime',
        'historial_cambios' => 'array',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    // ─── RELACIONES ───────────────────────────────────────────────────────────

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function apprentice()
    {
        return $this->belongsTo(Apprentice::class);
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'editado_por');
    }

    public function anulador()
    {
        return $this->belongsTo(User::class, 'anulado_por');
    }

    // ─── ACCESORES ────────────────────────────────────────────────────────────

    /** Badge Bootstrap según estado */
    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado) {
            'ASISTIO' => 'success',
            'FALTO'   => 'danger',
            'EXCUSA'  => 'info',
            'RETARDO' => 'warning',
            'ANULADA' => 'secondary',
            default   => 'light',
        };
    }

    /** Etiqueta legible del estado */
    public function getEstadoLabelAttribute(): string
    {
        return match ($this->estado) {
            'ASISTIO' => 'Asistió',
            'FALTO'   => 'Faltó',
            'EXCUSA'  => 'Excusa',
            'RETARDO' => 'Retardo',
            'ANULADA' => 'Anulada',
            default   => $this->estado,
        };
    }

    /** Icono FontAwesome según estado */
    public function getEstadoIconAttribute(): string
    {
        return match ($this->estado) {
            'ASISTIO' => 'fa-circle-check',
            'FALTO'   => 'fa-circle-xmark',
            'EXCUSA'  => 'fa-circle-info',
            'RETARDO' => 'fa-clock',
            'ANULADA' => 'fa-ban',
            default   => 'fa-circle',
        };
    }

    /** Nombre del momento ECP */
    public function getMomentoNombreAttribute(): string
    {
        return match ($this->momento_id) {
            1 => 'Momento 1: Autoconciencia',
            2 => 'Momento 2: Convivencia',
            3 => 'Momento 3: Resolución',
            default => 'Momento ' . $this->momento_id,
        };
    }

    /** Color del momento ECP */
    public function getMomentoColorAttribute(): string
    {
        return match ($this->momento_id) {
            1 => '#39A900',
            2 => '#0070B8',
            3 => '#6A1B9A',
            default => '#6c757d',
        };
    }

    /** Indica si tiene evidencia adjunta */
    public function getTieneEvidenciaAttribute(): bool
    {
        return !empty($this->evidencia_url);
    }

    /** Indica si fue sincronizado con SICEFA */
    public function getIsSyncedAttribute(): bool
    {
        return !is_null($this->sicefa_id) && !is_null($this->sicefa_synced_at);
    }

    /** Indica si fue anulado */
    public function getIsAnuladoAttribute(): bool
    {
        return $this->estado === 'ANULADA';
    }

    // ─── MÉTODOS DE NEGOCIO ───────────────────────────────────────────────────

    /**
     * Registra un cambio en el historial de auditoría.
     * Llamar antes de guardar cada modificación.
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
     * Payload preparado para enviar a la API REST de SICEFA.
     * Estructura estandarizada para la integración ERP.
     */
    public function toSicefaPayload(): array
    {
        return [
            'ficha_code'       => $this->course?->code,
            'actividad_id'     => $this->actividad_id,
            'actividad_nombre' => $this->actividad_nombre,
            'momento_id'       => $this->momento_id,
            'document_number'  => $this->document_number,
            'full_name'        => $this->full_name,
            'estado'           => $this->estado,
            'observacion'      => $this->observacion,
            'fecha'            => $this->fecha?->format('Y-m-d'),
            'evidencia_url'    => $this->evidencia_url,
            'instructor_doc'   => $this->instructor?->person?->document_number,
            'local_id'         => $this->id,
        ];
    }
}
