<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaInventario extends Model
{
    protected $table = 'categorias_inventario';
    protected $primaryKey = 'id_categoria_inventario';

    protected $fillable = [
        'nombre',
        'estado',
    ];

    public const ESTADOS = ['Activo', 'Inactivo'];

    /**
     * Scope para consultar solo categorías activas.
     */
    public function scopeActivas($query)
    {
        return $query->where('estado', 'Activo');
    }

    /**
     * Relación: Una categoría tiene muchos recursos de inventario.
     */
    public function inventarios(): HasMany
    {
        return $this->hasMany(Inventario::class, 'id_categoria_inventario', 'id_categoria_inventario');
    }

    /**
     * Total de recursos activos en esta categoría.
     */
    public function getRecursosActivosCountAttribute(): int
    {
        return $this->inventarios()->where('estado', '!=', 'Inactivo')->count();
    }
}
