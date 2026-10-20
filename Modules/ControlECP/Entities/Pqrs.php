<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pqrs extends Model
{
    use SoftDeletes;

    protected $table = 'controlecp_pqrs';
    protected $primaryKey = 'id_pqrs';

    protected $fillable = [
        'radicado',
        'id_usuario_aprendiz',
        'id_tipo_pqrs',
        'id_usuario_instructor_destino',
        'asunto',
        'descripcion',
        'estado',
        'fecha_radicacion',
    ];

    protected $casts = [
        'fecha_radicacion' => 'datetime',
    ];

    public const ESTADOS = ['Pendiente', 'En trámite', 'Resuelto', 'Cerrado'];

    /**
     * Relación: Tipo de PQR.
     */
    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoPqrs::class, 'id_tipo_pqrs', 'id_tipo_pqrs');
    }

    /**
     * Relación: Usuario (Aprendiz) que radicó la PQR.
     */
    public function aprendiz(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_aprendiz', 'id');
    }

    /**
     * Relación: Usuario (Instructor) de destino (opcional).
     */
    public function instructorDestino(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_instructor_destino', 'id');
    }

    /**
     * Relación: Hilo de respuestas asociadas a la PQR.
     */
    public function respuestas(): HasMany
    {
        return $this->hasMany(PqrsRespuesta::class, 'id_pqrs', 'id_pqrs');
    }

    /**
     * Relación: Historial de trazabilidad y cambios de estado.
     */
    public function historial(): HasMany
    {
        return $this->hasMany(PqrsHistorial::class, 'id_pqrs', 'id_pqrs');
    }

    /**
     * Generador de radicado único: ECP-PQR-2026-0001
     */
    public static function generarRadicado(): string
    {
        $anio    = date('Y');
        $prefijo = "ECP-PQR-{$anio}-";
        $ultimo  = static::withTrashed()
            ->where('radicado', 'like', $prefijo . '%')
            ->orderBy('id_pqrs', 'desc')
            ->first();

        if ($ultimo) {
            $partes     = explode('-', $ultimo->radicado);
            $consecutivo = (int) end($partes) + 1;
        } else {
            $consecutivo = 1;
        }

        return $prefijo . str_pad($consecutivo, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Accesor para estilo y color de badge según el estado.
     */
    public function getEstadoBadgeAttribute(): array
    {
        $estado = trim(strtolower($this->estado ?? 'pendiente'));

        return match ($estado) {
            'en trámite', 'en tramite' => [
                'bg'     => '#E0F2FE',
                'text'   => '#0369A1',
                'border' => '#BAE6FD',
                'icon'   => 'fas fa-spinner',
                'label'  => 'En trámite',
            ],
            'resuelta', 'resuelto' => [
                'bg'     => '#DEF7EC',
                'text'   => '#03543F',
                'border' => '#BCF0DA',
                'icon'   => 'fas fa-check-circle',
                'label'  => 'Resuelta',
            ],
            'cerrado', 'cerrada' => [
                'bg'     => '#F1F5F9',
                'text'   => '#475569',
                'border' => '#CBD5E1',
                'icon'   => 'fas fa-lock',
                'label'  => 'Cerrada',
            ],
            default => [
                'bg'     => '#FEF3C7',
                'text'   => '#92400E',
                'border' => '#FCD34D',
                'icon'   => 'fas fa-clock',
                'label'  => 'Pendiente',
            ],
        };
    }

    /**
     * Accesor para estilo y color de badge según el tipo de solicitud.
     */
    public function getTipoBadgeAttribute(): array
    {
        $nombreTipo = trim(strtolower($this->tipo ? $this->tipo->nombre : 'petición'));

        return match (true) {
            str_contains($nombreTipo, 'queja') => [
                'bg'     => '#FEE2E2',
                'text'   => '#991B1B',
                'border' => '#FCA5A5',
                'icon'   => 'fas fa-exclamation-circle',
            ],
            str_contains($nombreTipo, 'reclamo') => [
                'bg'     => '#FFEDD5',
                'text'   => '#C2410C',
                'border' => '#FDBA74',
                'icon'   => 'fas fa-triangle-exclamation',
            ],
            str_contains($nombreTipo, 'sugerencia') => [
                'bg'     => '#EDE9FE',
                'text'   => '#6D28D9',
                'border' => '#DDD6FE',
                'icon'   => 'fas fa-lightbulb',
            ],
            str_contains($nombreTipo, 'felicitación') || str_contains($nombreTipo, 'felicitacion') || str_contains($nombreTipo, 'comentario') => [
                'bg'     => '#DCFCE7',
                'text'   => '#15803D',
                'border' => '#BBF7D0',
                'icon'   => 'fas fa-heart',
            ],
            default => [
                'bg'     => '#E0F2FE',
                'text'   => '#0369A1',
                'border' => '#BAE6FD',
                'icon'   => 'fas fa-file-lines',
            ],
        };
    }
}
