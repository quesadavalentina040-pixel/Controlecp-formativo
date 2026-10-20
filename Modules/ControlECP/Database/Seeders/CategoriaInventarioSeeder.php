<?php

namespace Modules\ControlECP\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ControlECP\Entities\CategoriaInventario;

class CategoriaInventarioSeeder extends Seeder
{
    /**
     * Categorías iniciales de inventario para la Escuela Cultura de Paz.
     */
    public function run(): void
    {
        $categorias = [
            'Papelería y Útiles Escolares',
            'Materiales de Arte y Expresión',
            'Mobiliario Pedagógico',
            'Equipos Tecnológicos y Audiovisuales',
            'Juegos y Dinámicas de Paz',
            'Cartillas, Libros y Guías de Formación',
            'Insumos de Apoyo y Aseo',
        ];

        foreach ($categorias as $nombre) {
            CategoriaInventario::firstOrCreate(
                ['nombre' => $nombre],
                ['estado' => 'Activo']
            );
        }
    }
}
