<?php

namespace Modules\ControlECP\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventario extends Model
{
    use SoftDeletes;

    protected $table = 'inventario';
    protected $primaryKey = 'id_inventario';

    protected $fillable = [
        'id_categoria_inventario',
        'id_usuario',
        'nombre',
        'cantidad_total',
        'cantidad_disponible',
        'umbral_critico',
        'umbral_regular',
        'estado_fisico',
        'observaciones',
        'estado',
        'motivo_baja',
        'fecha_baja',
    ];

    protected $casts = [
        'fecha_baja'          => 'datetime',
        'cantidad_total'      => 'integer',
        'cantidad_disponible' => 'integer',
        'umbral_critico'      => 'integer',
        'umbral_regular'      => 'integer',
    ];

    public const ESTADOS_FISICOS = ['Bueno', 'Regular', 'Crítico'];
    public const ESTADOS = ['Disponible', 'Agotado', 'En Mantenimiento', 'Inactivo'];
    public const MOTIVOS_BAJA = ['Dañado', 'Perdido', 'Fuera de servicio'];

    // --- ACCESORES Y CÁLCULOS DEL SEMÁFORO ---

    /**
     * Calcula el nivel del semáforo:
     * - verde: Buen nivel de disponibilidad.
     * - amarillo: Nivel regular.
     * - rojo: Nivel crítico.
     */
    public function getSemaforoAttribute(): string
    {
        if ($this->estado === 'Inactivo' || $this->estado === 'Agotado' || $this->cantidad_disponible <= 0) {
            return 'rojo';
        }

        $umbralCritico = $this->umbral_critico > 0 ? $this->umbral_critico : 5;
        $umbralRegular = $this->umbral_regular > 0 ? $this->umbral_regular : 15;

        if ($this->cantidad_disponible <= $umbralCritico) {
            return 'rojo';
        }

        if ($this->cantidad_disponible <= $umbralRegular) {
            return 'amarillo';
        }

        return 'verde';
    }

    /**
     * Información visual del semáforo (colores, etiqueta, icono).
     */
    public function getSemaforoBadgeAttribute(): array
    {
        $semaforo = $this->semaforo;

        return match ($semaforo) {
            'rojo' => [
                'color'  => 'danger',
                'bg'     => '#fee2e2',
                'text'   => '#991b1b',
                'border' => '#fca5a5',
                'label'  => 'Crítico',
                'icon'   => 'fas fa-circle-exclamation',
            ],
            'amarillo' => [
                'color'  => 'warning',
                'bg'     => '#fef3c7',
                'text'   => '#92400e',
                'border' => '#fde68a',
                'label'  => 'Regular',
                'icon'   => 'fas fa-triangle-exclamation',
            ],
            default => [
                'color'  => 'success',
                'bg'     => '#dcfce7',
                'text'   => '#166534',
                'border' => '#86efac',
                'label'  => 'Buen Nivel',
                'icon'   => 'fas fa-circle-check',
            ],
        };
    }

    /**
     * Información visual del estado físico (bueno, regular, crítico).
     */
    public function getEstadoFisicoBadgeAttribute(): array
    {
        $estado = mb_strtolower($this->estado_fisico ?? 'bueno');

        return match ($estado) {
            'crítico', 'critico', 'malo' => [
                'color' => 'danger',
                'bg'    => '#fee2e2',
                'text'  => '#991b1b',
                'label' => 'Crítico',
                'icon'  => 'fas fa-wrench',
            ],
            'regular' => [
                'color' => 'warning',
                'bg'    => '#fef3c7',
                'text'  => '#92400e',
                'label' => 'Regular',
                'icon'  => 'fas fa-shield-halved',
            ],
            default => [
                'color' => 'success',
                'bg'    => '#dcfce7',
                'text'  => '#166534',
                'label' => 'Bueno',
                'icon'  => 'fas fa-circle-check',
            ],
        };
    }

    /**
     * Obtiene la foto principal del recurso.
     */
    public function getFotoPrincipalAttribute(): ?string
    {
        $foto = $this->fotos()->first();
        if ($foto && $foto->ruta) {
            return asset($foto->ruta);
        }
        return null;
    }

    /**
     * Tipo de recurso (Material, Equipo, Espacio) derivado de la categoría o nombre.
     */
    public function getTipoRecursoAttribute(): string
    {
        $catNombre = mb_strtolower($this->categoria ? $this->categoria->nombre : '');
        $recNombre = mb_strtolower($this->nombre);

        if (str_contains($catNombre, 'espacio') || str_contains($catNombre, 'ambiente') || str_contains($catNombre, 'mobiliario') || str_contains($recNombre, 'salón') || str_contains($recNombre, 'ambiente') || str_contains($recNombre, 'tablero')) {
            return 'Espacio';
        } elseif (str_contains($catNombre, 'equipo') || str_contains($catNombre, 'tecnol') || str_contains($catNombre, 'audio') || str_contains($recNombre, 'bafle') || str_contains($recNombre, 'videobeam') || str_contains($recNombre, 'grabadora') || str_contains($recNombre, 'micrófono')) {
            return 'Equipo';
        }
        return 'Material';
    }

    /**
     * Badge visual para el tipo de recurso (Material, Equipo, Espacio).
     */
    public function getTipoRecursoBadgeAttribute(): array
    {
        $tipo = $this->tipo_recurso;
        return match ($tipo) {
            'Espacio' => [
                'bg'     => '#E0F2FE',
                'text'   => '#0369A1',
                'border' => '#BAE6FD',
                'icon'   => 'fas fa-door-open',
            ],
            'Equipo' => [
                'bg'     => '#F3E8FF',
                'text'   => '#7E22CE',
                'border' => '#E9D5FF',
                'icon'   => 'fas fa-laptop',
            ],
            default => [
                'bg'     => '#ECFDF5',
                'text'   => '#047857',
                'border' => '#A7F3D0',
                'icon'   => 'fas fa-box-archive',
            ],
        };
    }

    // --- SCOPES DE CONSULTA ---

    public function scopeActivos($query)
    {
        return $query->where('estado', '!=', 'Inactivo');
    }

    public function scopePorCategoria($query, $categoriaId)
    {
        if ($categoriaId) {
            return $query->where('id_categoria_inventario', $categoriaId);
        }
        return $query;
    }

    public function scopePorEstadoFisico($query, $estadoFisico)
    {
        if ($estadoFisico) {
            return $query->where('estado_fisico', $estadoFisico);
        }
        return $query;
    }

    // --- RELACIONES ---

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaInventario::class, 'id_categoria_inventario', 'id_categoria_inventario');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(RecursoFoto::class, 'id_inventario', 'id_inventario');
    }

    public function novedades(): HasMany
    {
        return $this->hasMany(RecursoNovedad::class, 'id_inventario', 'id_inventario')->orderBy('fecha', 'desc');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'id_inventario', 'id_inventario')->orderBy('fecha', 'desc');
    }

    public function asesoriaRecursos(): HasMany
    {
        return $this->hasMany(AsesoriaRecurso::class, 'id_inventario', 'id_inventario');
    }

    public function cronogramaRecursos(): HasMany
    {
        return $this->hasMany(CronogramaRecurso::class, 'id_inventario', 'id_inventario');
    }
}
