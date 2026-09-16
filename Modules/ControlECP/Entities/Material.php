<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use SoftDeletes;

    protected $table = 'materiales';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'imagen',
        'categoria',
        'cantidad',
        'unidad',
        'stock_minimo',
        'ubicacion',
        'estado',
    ];

    protected $dates = [
        'deleted_at',
    ];

    /**
     * Categorías disponibles para materiales escolares.
     */
    public const CATEGORIAS = [
        'Papelería',
        'Escritura',
        'Arte y Manualidades',
        'Tecnología',
        'Mobiliario',
        'Aseo',
        'Otros',
    ];

    /**
     * Unidades de medida disponibles.
     */
    public const UNIDADES = [
        'Unidad',
        'Caja',
        'Paquete',
        'Resma',
        'Docena',
        'Kit',
    ];

    /**
     * Estados disponibles.
     */
    public const ESTADOS = ['Disponible', 'Agotado', 'Dañado'];

    /**
     * Indica si el material está por debajo del stock mínimo.
     */
    public function bajoStock(): bool
    {
        return $this->cantidad <= $this->stock_minimo;
    }

    /**
     * URL pública de la imagen del material (null si no tiene).
     */
    public function getImagenUrlAttribute(): ?string
    {
        if (empty($this->imagen)) {
            return null;
        }

        return asset('storage/' . $this->imagen);
    }
}
