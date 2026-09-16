<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pqr extends Model
{
    use SoftDeletes;

    protected $table = 'controlecp_pqrs';

    protected $fillable = [
        'radicado',
        'tipo',
        'nombre_solicitante',
        'documento',
        'email',
        'telefono',
        'perfil',
        'asunto',
        'mensaje',
        'anexo',
        'prioridad',
        'estado',
        'respuesta',
        'fecha_respuesta',
        'respondido_por',
    ];

    protected $casts = [
        'fecha_respuesta' => 'datetime',
    ];

    protected $dates = ['deleted_at'];

    public const TIPOS = [
        'Petición',
        'Queja',
        'Reclamo',
        'Sugerencia',
        'Felicitación',
    ];

    public const PERFILES  = ['Aprendiz', 'Instructor', 'Funcionario', 'Externo'];
    public const PRIORIDADES = ['Baja', 'Media', 'Alta'];
    public const ESTADOS   = ['Pendiente', 'En trámite', 'Resuelto', 'Cerrado'];

    /**
     * Usuario que respondió la PQR.
     */
    public function respondidoPor()
    {
        return $this->belongsTo(User::class, 'respondido_por');
    }

    /**
     * Genera un número de radicado único: ECP-PQR-2026-0001
     */
    public static function generarRadicado(): string
    {
        $anio      = date('Y');
        $prefijo   = "ECP-PQR-{$anio}-";
        $ultimo    = static::withTrashed()
            ->where('radicado', 'like', $prefijo . '%')
            ->orderBy('id', 'desc')
            ->first();

        $consecutivo = $ultimo
            ? ((int) substr($ultimo->radicado, strlen($prefijo))) + 1
            : 1;

        return $prefijo . str_pad($consecutivo, 4, '0', STR_PAD_LEFT);
    }

    /**
     * URL pública del anexo (null si no tiene).
     */
    public function getAnexoUrlAttribute(): ?string
    {
        return empty($this->anexo) ? null : asset('storage/' . $this->anexo);
    }

    /**
     * Indica si ya fue respondida.
     */
    public function estaRespondida(): bool
    {
        return !empty($this->respuesta);
    }

    /**
     * Días transcurridos desde la radicación.
     */
    public function diasTranscurridos(): int
    {
        return (int) $this->created_at->diffInDays(now());
    }

    /**
     * Clase de color Bootstrap según el estado.
     */
    public function colorEstado(): string
    {
        return match ($this->estado) {
            'Pendiente'  => 'bg-warning text-dark',
            'En trámite' => 'bg-info text-dark',
            'Resuelto'   => 'bg-success',
            'Cerrado'    => 'bg-secondary',
            default      => 'bg-light text-dark',
        };
    }

    /**
     * Ícono FontAwesome según el tipo.
     */
    public function iconoTipo(): string
    {
        return match ($this->tipo) {
            'Petición'     => 'fa-hand-holding-heart',
            'Queja'        => 'fa-face-frown',
            'Reclamo'      => 'fa-triangle-exclamation',
            'Sugerencia'   => 'fa-lightbulb',
            'Felicitación' => 'fa-star',
            default        => 'fa-comment-dots',
        };
    }
}
