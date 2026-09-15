<?php

namespace Modules\ControlECP\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ControlECP\Entities\Material;

class MaterialSeeder extends Seeder
{
    /**
     * Materiales escolares de ejemplo para el inventario de Control ECP.
     */
    public function run(): void
    {
        $materiales = [
            ['codigo' => 'MAT-001', 'nombre' => 'Cuaderno cuadriculado 100 hojas', 'descripcion' => 'Cuaderno tamaño carta, cuadros de 5mm.', 'categoria' => 'Papelería',           'cantidad' => 120, 'unidad' => 'Unidad',  'stock_minimo' => 20, 'ubicacion' => 'Bodega 1 - Estante A', 'estado' => 'Disponible'],
            ['codigo' => 'MAT-002', 'nombre' => 'Lápiz de grafito HB',            'descripcion' => 'Caja x 12 lápices negros.',           'categoria' => 'Escritura',            'cantidad' => 45,  'unidad' => 'Caja',    'stock_minimo' => 10, 'ubicacion' => 'Bodega 1 - Estante B', 'estado' => 'Disponible'],
            ['codigo' => 'MAT-003', 'nombre' => 'Bolígrafo tinta negra',          'descripcion' => 'Paquete x 10 esferos.',               'categoria' => 'Escritura',            'cantidad' => 8,   'unidad' => 'Paquete', 'stock_minimo' => 10, 'ubicacion' => 'Bodega 1 - Estante B', 'estado' => 'Disponible'],
            ['codigo' => 'MAT-004', 'nombre' => 'Resma de papel bond carta',      'descripcion' => 'Papel blanco 75g, 500 hojas.',        'categoria' => 'Papelería',            'cantidad' => 30,  'unidad' => 'Resma',   'stock_minimo' => 5,  'ubicacion' => 'Bodega 2 - Estante C', 'estado' => 'Disponible'],
            ['codigo' => 'MAT-005', 'nombre' => 'Marcadores de colores',          'descripcion' => 'Set x 12 colores permanentes.',       'categoria' => 'Arte y Manualidades',  'cantidad' => 25,  'unidad' => 'Kit',     'stock_minimo' => 8,  'ubicacion' => 'Bodega 2 - Estante D', 'estado' => 'Disponible'],
            ['codigo' => 'MAT-006', 'nombre' => 'Tijeras escolares punta roma',   'descripcion' => 'Tijeras de seguridad para aprendices.','categoria' => 'Arte y Manualidades', 'cantidad' => 0,   'unidad' => 'Unidad',  'stock_minimo' => 15, 'ubicacion' => 'Bodega 2 - Estante D', 'estado' => 'Agotado'],
            ['codigo' => 'MAT-007', 'nombre' => 'Pegante en barra',               'descripcion' => 'Barra de pegamento 40g.',             'categoria' => 'Arte y Manualidades',  'cantidad' => 60,  'unidad' => 'Unidad',  'stock_minimo' => 20, 'ubicacion' => 'Bodega 2 - Estante D', 'estado' => 'Disponible'],
            ['codigo' => 'MAT-008', 'nombre' => 'Cartulina de colores',           'descripcion' => 'Pliego de cartulina surtida.',        'categoria' => 'Papelería',            'cantidad' => 200, 'unidad' => 'Unidad',  'stock_minimo' => 30, 'ubicacion' => 'Bodega 1 - Estante A', 'estado' => 'Disponible'],
            ['codigo' => 'MAT-009', 'nombre' => 'Tablero acrílico portátil',      'descripcion' => 'Tablero blanco 60x40cm.',             'categoria' => 'Mobiliario',           'cantidad' => 5,   'unidad' => 'Unidad',  'stock_minimo' => 2,  'ubicacion' => 'Bodega 3 - Zona mobiliario', 'estado' => 'Disponible'],
            ['codigo' => 'MAT-010', 'nombre' => 'Borrador de nata',               'descripcion' => 'Caja x 20 borradores blancos.',       'categoria' => 'Escritura',            'cantidad' => 3,   'unidad' => 'Caja',    'stock_minimo' => 5,  'ubicacion' => 'Bodega 1 - Estante B', 'estado' => 'Disponible'],
        ];

        foreach ($materiales as $material) {
            Material::updateOrCreate(
                ['codigo' => $material['codigo']],
                $material
            );
        }
    }
}
